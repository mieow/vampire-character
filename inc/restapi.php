<?php
/* ---------------------------------------------------------------
https://developer.wordpress.org/rest-api/extending-the-rest-api/adding-custom-endpoints/
------------------------------------------------------------------ */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require_once VTM_CHARACTER_URL . 'inc/classes.php';



function vtm_api_get_my_character (WP_REST_Request $request) {
	global $wpdb;

 	$current_user = wp_get_current_user();
	$character = $current_user->user_login;
	$characterID = vtm_establishCharacterID($character);
	$mycharacter = new vtmclass_character();
	
	

   // if( $_SERVER['REQUEST_URI'] == vtm_get_config('ANDROID_LINK') && is_user_logged_in() ) {
		// $character = vtm_establishCharacter();
		// $characterID = vtm_establishCharacterID($character);
		// $mycharacter = new vtmclass_character();
		// $mycharacter->load($characterID);

		// header('Content-Type: application/json; charset=utf-8');
		// echo json_encode($mycharacter);
		
		// exit;
	// } 

  // if ( empty( $posts ) ) {
    // return new WP_Error( 'no_author', 'Invalid author', array( 'status' => 404 ) );
  // }

	 return $mycharacter;
	 #return new WP_Error( 'vtmerror', 'Character error', array( 'status' => 404 ) );;
}
function vtm_api_get_character (WP_REST_Request $request) {
	// $nonce = $request->get_header('X-WP-Nonce');
	// if (!is_string($nonce) || $nonce === '') {
	// 	$nonce = $request->get_param('_wpnonce');
	// }
	// if (
	// 	!is_string($nonce)
	// 	|| !wp_verify_nonce(sanitize_text_field(wp_unslash($nonce)), 'wp_rest')
	// ) {
	// 	return new WP_Error(
	// 		'rest_forbidden',
	// 		'Invalid or missing REST API nonce.',
	// 		array('status' => 403)
	// 	);
	// }

	$current_user = wp_get_current_user();
	$character = $current_user->user_login;
	$characterID = vtm_establishCharacterID($character);
	$mycharacter = new vtmclass_character();

    // if( $_SERVER['REQUEST_URI'] == vtm_get_config('ANDROID_LINK') && is_user_logged_in() ) {
		// $character = vtm_establishCharacter();
		// $characterID = vtm_establishCharacterID($character);
		// $mycharacter = new vtmclass_character();
		// $mycharacter->load($characterID);

		// header('Content-Type: application/json; charset=utf-8');
		// echo json_encode($mycharacter);
		
		// exit;
	// } 

  // if ( empty( $posts ) ) {
    // return new WP_Error( 'no_author', 'Invalid author', array( 'status' => 404 ) );
  // }

	 #return new WP_Error( 'vtmerror', 'Character error', array( 'status' => 404 ) );;
	 return $_REQUEST;
}

function vtm_get_config ($field) {

        global $wpdb;
        $configs = $wpdb->get_results($wpdb->prepare("SELECT %i FROM %i", $field, VTM_TABLE_PREFIX . "CONFIG"));

        return $configs[0]->$field;

}
?>