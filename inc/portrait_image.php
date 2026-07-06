<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Validate image data by checking magic bytes
 * 
 * @param string $file_contents The binary file contents to validate
 * @return array Array with 'valid' (bool) and 'type' (string) keys
 */
function vtm_validate_image_data($file_contents) {
	$result = array(
		'valid' => false,
		'type'  => 'application/octet-stream'
	);
	
	if (strlen($file_contents) < 2) {
		return $result;
	}
	
	$header = substr($file_contents, 0, 4);
	
	// JPEG: FF D8 FF
	if (substr($header, 0, 3) === "\xFF\xD8\xFF") {
		$result['valid'] = true;
		$result['type'] = 'image/jpeg';
	}
	// PNG: 89 50 4E 47
	elseif (substr($header, 0, 4) === "\x89PNG") {
		$result['valid'] = true;
		$result['type'] = 'image/png';
	}
	// GIF: 47 49 46 38
	elseif (substr($header, 0, 3) === "GIF") {
		$result['valid'] = true;
		$result['type'] = 'image/gif';
	}
	// BMP: 42 4D
	elseif (substr($header, 0, 2) === "BM") {
		$result['valid'] = true;
		$result['type'] = 'image/bmp';
	}
	// WebP: RIFF ... WEBP
	elseif (substr($header, 0, 4) === "RIFF" && strlen($file_contents) >= 12 && substr($file_contents, 8, 4) === "WEBP") {
		$result['valid'] = true;
		$result['type'] = 'image/webp';
	}
	
	return $result;
}

function vtm_portrait_image_redirect()
{
    if( isset($_GET['vtm_get_portrait']) )
    {
		global $wpdb, $wp_filesystem;
		global $vtmglobal;
		
        // Initialize WP_Filesystem
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        WP_Filesystem();
 		
		$characterID = $_GET['vtm_get_portrait'];
		$file = $wpdb->get_var($wpdb->prepare("SELECT PORTRAIT FROM " . $wpdb->prefix . "vtm_CHARACTER_PROFILE WHERE CHARACTER_ID = %d", $characterID));
		
		if ($file == '') {
			vtm_getConfig();
			$placeholder = $vtmglobal['config']->PLACEHOLDER_IMAGE;
			// Check if placeholder is already a URL
			if (filter_var($placeholder, FILTER_VALIDATE_URL) !== FALSE) {
				$file = $placeholder;
			} else {
				$file = VTM_PLUGIN_URL . "/" . $placeholder;
			}
		}
		elseif (filter_var($file, FILTER_VALIDATE_URL) === FALSE) {
            if(!$wp_filesystem->is_file($file)) {
                $file2 = VTM_PLUGIN_URL . "/$file";
                if(!$wp_filesystem->is_file($file2) && filter_var($file2, FILTER_VALIDATE_URL) === FALSE) {
                    echo "<p>Problem finding file: " . esc_html("$file / $file2") . "</p>";
                    return;
                } else {
                    $file = $file2;
                }
            }
		}
		
		// Get file contents - handle both URLs and local paths
		$file_contents = '';
		if (filter_var($file, FILTER_VALIDATE_URL) !== FALSE) {
			// It's a URL - use wp_remote_get
			$response = wp_remote_get($file, array(
				'timeout' => 10,
				'sslverify' => false,
			));
			if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
				$file_contents = wp_remote_retrieve_body($response);
			} else {
				$error = is_wp_error($response) ? $response->get_error_message() : 'HTTP error ' . wp_remote_retrieve_response_code($response);
				echo "<p>Could not fetch image from " . esc_html($file) . ": " . esc_html($error) . "</p>";
				return;
			}
		} else {
			// It's a local path - use WP_Filesystem
			if ($wp_filesystem->is_file($file)) {
				$file_contents = $wp_filesystem->get_contents($file);
			} else {
				echo "<p>File not found: " . esc_html($file) . "</p>";
				return;
			}
		}
		
		// Validate we have content
		if (empty($file_contents)) {
			echo "<p>Image file is empty: " . esc_html($file) . "</p>";
			return;
		}
		
		if (class_exists('Imagick')) { 
			$img = new Imagick();
            if ($img->readImageBlob($file_contents)) {
			
				switch (get_option('vtm_image_effect')) {
					case 'sepia':
						$img->sepiaToneImage(80);
						break;
					case 'bw':
						$img->modulateImage(100,0,100);
						break;
					case 'painting':
						$img->oilPaintImage(3);
						break;
					
				}
			
				// Get image blob and validate before output
				$image_blob = $img->getImageBlob();
				$image_validation = vtm_validate_image_data($image_blob);
				
				if ($image_validation['valid']) {
					header('Content-Type: ' . $image_validation['type']);
					// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- Binary image data verified by magic bytes, safe to output directly
					echo $image_blob;
					// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
				} else {
					echo "<p>Invalid image data after processing</p>";
				}
			} else {
				echo "<p>Could not read portait image " . esc_html($file) . "</p>";
			}
		} 
		elseif (extension_loaded('gd')) {
			// Use imagecreatefromstring for blob data
			$img = @imagecreatefromstring($file_contents);
			if ($img) {
				switch (get_option('vtm_image_effect')) {
					case 'sepia':
						imagefilter($img, IMG_FILTER_GRAYSCALE);
						imagefilter($img,IMG_FILTER_COLORIZE,100,50,0);
						break;
					case 'bw':
						imagefilter($img, IMG_FILTER_GRAYSCALE);
						break;
				}
				
				// Capture output, validate, then send
				ob_start();
				switch (VTM_ICON_FORMAT) {
					case 'jpg': imagejpeg($img); break;
					case 'gif': imagegif($img); break;
				}
				$image_data = ob_get_clean();
				imagedestroy($img);
				
				// Validate image data before output
				$image_validation = vtm_validate_image_data($image_data);
				if ($image_validation['valid']) {
					header('Content-Type: ' . $image_validation['type']);
					// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- Binary image data verified by magic bytes, safe to output directly
					echo $image_data;
					// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
				} else {
					echo "<p>Invalid image data after processing</p>";
				}
			} else {
				echo "<p>Could not create image from data</p>";
			}
		}
		else {
			// Fallback: output raw binary when no image processing available
			// Verify it's actually image data by checking magic bytes
			$image_validation = vtm_validate_image_data($file_contents);
			
			if ($image_validation['valid']) {
				header('Content-Type: ' . $image_validation['type']);
				// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- Binary image data verified by magic bytes, safe to output directly
				echo $file_contents;
				// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
			} else {
				echo "<p>Invalid image data: File does not appear to be a valid image</p>";
			}
		}
    }
}
add_action( 'template_redirect', 'vtm_portrait_image_redirect' );



?>