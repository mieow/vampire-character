<?php

function vtm_portrait_image_redirect()
{
    if( isset($_GET['vtm_get_portrait']) )
    {
		global $wpdb;
		global $vtmglobal;
		
		$characterID = $_GET['vtm_get_portrait'];
		$file = $wpdb->get_var($wpdb->prepare("SELECT PORTRAIT FROM " . VTM_TABLE_PREFIX . "CHARACTER_PROFILE WHERE CHARACTER_ID = %d", $characterID));
		
		if ($file == '') {
			vtm_getConfig();
			$file = VTM_PLUGIN_URL . "/" . $vtmglobal['config']->PLACEHOLDER_IMAGE;
		}
		elseif (filter_var($file, FILTER_VALIDATE_URL) === FALSE) {
			if(!file_exists($file)) {
				$file2 = VTM_PLUGIN_URL . "/$file";
				if(!file_exists($file2) && filter_var($file2, FILTER_VALIDATE_URL) === FALSE) {
					echo "<p>Problem finding file: $file / $file2</p>";
				} else {
					$file = $file2;
				}
				
			}
		}
		//echo "file: $file";
		
		$handle = fopen($file, 'rb');
		
		if (class_exists('Imagick')) { 
			$img = new Imagick();
			if ($img->readImageFile($handle)) {
			
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
			
				header('Content-Type: image/'.$img->getImageFormat());
				echo $img;
			} else {
				echo "<p>Could not read portait image $file</p>";
			}
		} 
		elseif (extension_loaded('gd')) {
			switch (VTM_ICON_FORMAT) {
				case 'jpg': $img = imagecreatefromjpeg($file); break;
				case 'gif': $img = imagecreatefromgif($file); break;
			}
			switch (get_option('vtm_image_effect')) {
				case 'sepia':
					imagefilter($img, IMG_FILTER_GRAYSCALE);
					imagefilter($img,IMG_FILTER_COLORIZE,100,50,0);
					break;
				case 'bw':
					imagefilter($img, IMG_FILTER_GRAYSCALE);
					break;
				
			}
			header('Content-Type: image/'.VTM_ICON_FORMAT);
			switch (VTM_ICON_FORMAT) {
				case 'jpg': imagejpeg($img); break;
				case 'gif': imagegif($img); break;
			}
			imagedestroy($img);
		}
		else {
			fpassthru($handle);
			fclose($handle);
		}
		//echo "<p>file: $file</p>";
    }
}
add_action( 'template_redirect', 'vtm_portrait_image_redirect' );



?>