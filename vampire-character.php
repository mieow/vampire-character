<?php
    /*  Plugin Name: Vampire Character Manager
        Plugin URI: http://plugin.gvlarp.com
        Description: Management of Characters and Players
        Author: Jane Houston
        Version: 2.13
        Author URI: http://www.mieow.co.uk
    */

    /*  Copyright 2023 Jane Houston

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
define( 'VTM_PLUGIN_URL',  plugins_url('vampire-character'));
define( 'VTM_ICON_FORMAT',  'jpg');

require_once VTM_CHARACTER_URL . 'inc/functions.php';
require_once VTM_CHARACTER_URL . 'inc/printable.php';
require_once VTM_CHARACTER_URL . 'inc/extendedbackground.php';
require_once VTM_CHARACTER_URL . 'inc/widgets.php';
require_once VTM_CHARACTER_URL . 'inc/xpfunctions.php';
require_once VTM_CHARACTER_URL . 'inc/shortcodes.php';
require_once VTM_CHARACTER_URL . 'inc/viewcharacter.php';
require_once VTM_CHARACTER_URL . 'inc/profile.php';
require_once VTM_CHARACTER_URL . 'inc/chargen.php';
require_once VTM_CHARACTER_URL . 'inc/install.php';
require_once VTM_CHARACTER_URL . 'inc/email.php';
require_once VTM_CHARACTER_URL . 'inc/stnews.php';
require_once VTM_CHARACTER_URL . 'inc/pm.php';
require_once VTM_CHARACTER_URL . 'inc/portrait_image.php';

// Only load code for admin pages if we are trying to look at them
if (is_admin()) {
	require_once VTM_CHARACTER_URL . 'inc/adminpages.php';
	
	// This external link is for optionally installing pre-defined
	// character sheet data.  It is accessed by an admin user from 
	// the Database tab on the Configuration page where they can 
	// choose to download the below zip file into the plugin init/ 
	// folder and load the data into the plugin database tables.
	// The plugin works without loading in the data, although they 
	// will then have to manually enter skills, etc.
	define( 'VTM_DATA_NAME',    'vampire-data');
	define( 'VTM_DATA_VERSION', 'vtm2.6-v3.1');
	define( 'VTM_DATA_FILE',  'https://github.com/mieow/vampire-data/archive/' . VTM_DATA_VERSION . '.zip');
}

// Admin includes that are used on the front of the site
require_once VTM_CHARACTER_URL . 'inc/adminpages/toolbar.php';
require_once VTM_CHARACTER_URL . 'inc/adminpages/characters.php';

$title = "Vampire Character Management";
vtm_getConfig();

/* STYLESHEETS
------------------------------------------------------ */

function vtm_plugin_style()  
{ 
  wp_register_style( 'plugin-style', VTM_PLUGIN_URL . '/css/style-plugin.css' );
  wp_enqueue_style( 'plugin-style' );
}
add_action('wp_enqueue_scripts', 'vtm_plugin_style');

/* REST API
------------------------------------------------------ */
add_action( 'rest_api_init', function () {
	
	require_once VTM_CHARACTER_URL . 'inc/classes.php';
	$items=new vtmclass_restapi();
	$items->register_routes();
	
} );

/* JAVASCRIPT
----------------------------------------------------------------- */
function vtm_feedingmap_scripts() {
	wp_enqueue_script( 'feedingmap-setup-api', VTM_PLUGIN_URL . '/js/googleapi.js');
}

add_action( 'wp_enqueue_scripts', 'vtm_feedingmap_scripts' );
add_action('admin_enqueue_scripts', 'vtm_feedingmap_scripts');

/* FUNCTIONS
------------------------------------------------------ */
function vtm_isST() {
	$current_user = wp_get_current_user();
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
	global $vtmglobal;
	$current_user = wp_get_current_user();
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
		$vtmglobal['character'] = $character;
	}
	
	return $character;
}





?>