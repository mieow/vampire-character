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
			$file = site_url() . $vtmglobal['config']->PLACEHOLDER_IMAGE;
		}
		//echo "file: $file";
		
		$handle = fopen($file, 'rb');
		$img = new Imagick();
		$img->readImageFile($handle);
		
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
		//echo "<p>file: $file</p>";
    }
}
add_action( 'template_redirect', 'vtm_portrait_image_redirect' );



?>