<?php
    /*  Plugin Name: Vampire:the Masquerade Character Manager
        Plugin URI: http://plugin.gvlarp.com
        Description: Management of Characters and Players for Vampire:the Masquerade
        Author: Jane Houston
        Version: 2.1
        Author URI: http://www.mieow.co.uk
    */

    /*  Copyright 2015 Jane Houston

        This program is free software; you can redistribute it and/or modify
        it under the terms of the GNU General Public License, version 2, as
        published by the Free Software Foundation.

        This program is distributed in the hope that it will be useful,
        but WITHOUT ANY WARRANTY; without even the implied warranty of
        MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
        GNU General Public License for more details.

        You should have received a copy of the GNU General Public License
        along with this program; if not, write to the Free Software
        Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
    */

global $wpdb;

define( 'VTM_CHARACTER_URL', plugin_dir_path(__FILE__) );
define( 'VTM_TABLE_PREFIX', $wpdb->prefix . "vtm_" );
require_once VTM_CHARACTER_URL . 'inc/functions.php';
require_once VTM_CHARACTER_URL . 'inc/printable.php';
require_once VTM_CHARACTER_URL . 'inc/extendedbackground.php';
require_once VTM_CHARACTER_URL . 'inc/widgets.php';
require_once VTM_CHARACTER_URL . 'inc/android.php';
require_once VTM_CHARACTER_URL . 'inc/xpfunctions.php';
require_once VTM_CHARACTER_URL . 'inc/shortcodes.php';
require_once VTM_CHARACTER_URL . 'inc/viewcharacter.php';
require_once VTM_CHARACTER_URL . 'inc/profile.php';
require_once VTM_CHARACTER_URL . 'inc/chargen.php';
require_once VTM_CHARACTER_URL . 'inc/install.php';
require_once VTM_CHARACTER_URL . 'inc/email.php';
require_once VTM_CHARACTER_URL . 'inc/stnews.php';

// Only load code for admin pages if we are trying to look at them
if (is_admin()) {
	require_once VTM_CHARACTER_URL . 'inc/adminpages.php';
}

// Admin includes that are used on the front of the site
require_once VTM_CHARACTER_URL . 'inc/adminpages/toolbar.php';
require_once VTM_CHARACTER_URL . 'inc/adminpages/characters.php';

$title = "V:tM Character Management";
$vtmglobal['config'] = vtm_getConfig();

/* STYLESHEETS
------------------------------------------------------ */

function vtm_plugin_style()  
{ 
  wp_register_style( 'plugin-style', plugins_url( 'vtm-character/css/style-plugin.css' ) );
  wp_enqueue_style( 'plugin-style' );
}
add_action('wp_enqueue_scripts', 'vtm_plugin_style');

/* JAVASCRIPT
----------------------------------------------------------------- */
function vtm_feedingmap_scripts() {
	wp_enqueue_script( 'feedingmap-setup-api', plugins_url('vtm-character/js/googleapi.js'));
}

add_action( 'wp_enqueue_scripts', 'vtm_feedingmap_scripts' );
add_action('admin_enqueue_scripts', 'vtm_feedingmap_scripts');

/* FUNCTIONS
------------------------------------------------------ */
function vtm_isST() {
	global $current_user;
	
	get_currentuserinfo();
	$result = false;
	$roles = $current_user->roles;

	foreach ($roles as $current_role) {
		if ($current_role == "administrator"
			|| $current_role == "storyteller") {
			$result = true;
		}
	}
	return $result;
}

function vtm_establishCharacter($character) {
	global $current_user;
	get_currentuserinfo();
	if (vtm_isST()) {
		if (isset($_POST['VTM_CHARACTER'])) {
			$character = $_POST['VTM_CHARACTER'];
		}
		elseif (isset($_GET['CHARACTER'])) {
			$character = $_GET['CHARACTER'];
		}
		elseif ($character == null || $character == "null" || $character == "") {
			$character = $current_user->user_login;
		}
	}
	else {
		$character = $current_user->user_login;
	}
	return $character;
}



?>