<?php

require_once VTM_CHARACTER_URL . 'inc/adminpages/reports.php';
require_once VTM_CHARACTER_URL . 'inc/adminpages/reportclasses.php';
require_once VTM_CHARACTER_URL . 'inc/adminpages/backgrounds.php';
require_once VTM_CHARACTER_URL . 'inc/adminpages/clans.php';
require_once VTM_CHARACTER_URL . 'inc/adminpages/data.php';
require_once VTM_CHARACTER_URL . 'inc/adminpages/moredata.php';
require_once VTM_CHARACTER_URL . 'inc/adminpages/config.php';
require_once VTM_CHARACTER_URL . 'inc/adminpages/experience.php';
require_once VTM_CHARACTER_URL . 'inc/adminpages/enlightenment.php';
require_once VTM_CHARACTER_URL . 'inc/adminpages/paths.php';
require_once VTM_CHARACTER_URL . 'inc/adminpages/nature.php';
require_once VTM_CHARACTER_URL . 'inc/adminpages/domains.php';
require_once VTM_CHARACTER_URL . 'inc/adminpages/offices.php';
require_once VTM_CHARACTER_URL . 'inc/adminpages/combodisciplines.php';
require_once VTM_CHARACTER_URL . 'inc/adminpages/feedingmap.php';
require_once VTM_CHARACTER_URL . 'inc/adminpages/players.php';
require_once VTM_CHARACTER_URL . 'inc/adminpages/masterpath.php';
require_once VTM_CHARACTER_URL . 'inc/adminpages/generation.php';
require_once VTM_CHARACTER_URL . 'inc/adminpages/tempstats.php';
require_once VTM_CHARACTER_URL . 'inc/adminpages/chargentemplates.php';
require_once VTM_CHARACTER_URL . 'inc/adminpages/sects.php';
require_once VTM_CHARACTER_URL . 'inc/adminpages/skill_types.php';

if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
if ( is_admin() ){ // admin actions
	add_action( 'admin_menu', 'vtm_register_character_menu' );
	add_action( 'admin_init', 'vtm_register_character_settings' );
} else {
	// non-admin enqueues, actions, and filters
}


function vtm_admin_css() { 
	wp_enqueue_style('my-admin-style', plugins_url('css/style-admin.css',dirname(__FILE__)));
}
add_action('admin_enqueue_scripts', 'vtm_admin_css');


/* OPTIONS SETTINGS 
----------------------------------------------------------------- */
function vtm_register_character_settings() {
	global $wp_roles;

	register_setting( 'vtm_options_group', 'vtm_pdf_title' );
	register_setting( 'vtm_options_group', 'vtm_pdf_footer' );
	register_setting( 'vtm_options_group', 'vtm_pdf_titlefont' );
	register_setting( 'vtm_options_group', 'vtm_pdf_titlecolour' );
	register_setting( 'vtm_options_group', 'vtm_pdf_divcolour' );
	register_setting( 'vtm_options_group', 'vtm_pdf_divtextcolour' );
	register_setting( 'vtm_options_group', 'vtm_pdf_divlinewidth' );
	register_setting( 'vtm_options_group', 'vtm_pdf_dotcolour' );
	register_setting( 'vtm_options_group', 'vtm_pdf_dotlinewidth' );

	register_setting( 'vtm_options_group', 'vtm_view_bgcolour' );
	register_setting( 'vtm_options_group', 'vtm_view_dotlinewidth' );
	register_setting( 'vtm_options_group', 'vtm_dot1colour' );
	register_setting( 'vtm_options_group', 'vtm_dot2colour' );
	register_setting( 'vtm_options_group', 'vtm_dot3colour' );
	register_setting( 'vtm_options_group', 'vtm_dot4colour' );
	register_setting( 'vtm_options_group', 'vtm_view_dotcolour' ); // depreciated
	register_setting( 'vtm_options_group', 'vtm_pend_dotcolour' ); // depreciated
	register_setting( 'vtm_options_group', 'vtm_xp_dotcolour' ); // depreciated
	register_setting( 'vtm_options_group', 'vtm_chargen_freebie' ); // depreciated
	//register_setting( 'vtm_options_group', 'vtm_pend_bgcolour' );
	//register_setting( 'vtm_options_group', 'vtm_pend_dotlinewidth' );
	//register_setting( 'vtm_options_group', 'vtm_xp_bgcolour' );
	//register_setting( 'vtm_options_group', 'vtm_xp_dotlinewidth' );
	//register_setting( 'vtm_options_group', 'vtm_chargen_dotlinewidth' );
	//register_setting( 'vtm_options_group', 'vtm_chargen_bgcolour' );
	//register_setting( 'vtm_options_group', 'vtm_chargen_freedot' );
	//register_setting( 'vtm_options_group', 'vtm_chargen_selectdot' );
	//register_setting( 'vtm_options_group', 'vtm_chargen_empty' );
	
	register_setting( 'vtm_options_group', 'vtm_signin_columns' );
	//register_setting( 'vtm_options_group', 'vtm_web_columns' );
	register_setting( 'vtm_options_group', 'vtm_web_pagewidth' );
	register_setting( 'vtm_options_group', 'vtm_news_blogroll' );

	register_setting( 'vtm_features_group', 'vtm_feature_temp_stats' );
	register_setting( 'vtm_features_group', 'vtm_feature_maps' );
	register_setting( 'vtm_features_group', 'vtm_feature_reports' );
	register_setting( 'vtm_features_group', 'vtm_feature_email' );
	register_setting( 'vtm_features_group', 'vtm_feature_news' );
	register_setting( 'vtm_features_group', 'vtm_feature_pm' );
	add_settings_section(
		'vtmfeatures',
		"Plugin Features",
		"vtm_render_config_features",
		'vtm_features_group',
	);
	add_settings_field(
		'vtm_feature_temp_stats',
		'Track Temporary Stats',
		'vtm_checkbox_cb', 'vtm_features_group', 'vtmfeatures',
		array(
			'label_for'         => 'vtm_feature_temp_stats',
			'description'		=> 'Track Willpower and Blood pool spends'
		)
	);	
	add_settings_field(
		'vtm_feature_maps',
		'Maps',
		'vtm_checkbox_cb', 'vtm_features_group', 'vtmfeatures',
		array(
			'label_for'         => 'vtm_feature_maps',
			'description'		=> 'Show hunting/city maps'
		)
	);	
	add_settings_field(
		'vtm_feature_reports',
		'Reports',
		'vtm_checkbox_cb', 'vtm_features_group', 'vtmfeatures',
		array(
			'label_for'         => 'vtm_feature_reports',
			'description'		=> 'Show administrator reports, including sign-in sheet'
		)
	);	
	add_settings_field(
		'vtm_feature_email',
		'Email configuration',
		'vtm_checkbox_cb', 'vtm_features_group', 'vtmfeatures',
		array(
			'label_for'         => 'vtm_feature_email',
			'description'		=> 'Advanced options for configuring sending email'
		)
	);	
	add_settings_field(
		'vtm_feature_news',
		'Newsletter',
		'vtm_checkbox_cb', 'vtm_features_group', 'vtmfeatures',
		array(
			'label_for'         => 'vtm_feature_news',
			'description'		=> 'Email out news and Experience Point totals to active character accounts'
		)
	);	
	add_settings_field(
		'vtm_feature_pm',
		'Private Messaging',
		'vtm_checkbox_cb', 'vtm_features_group', 'vtmfeatures',
		array(
			'label_for'         => 'vtm_feature_pm',
			'description'		=> 'Enable inter-character private communication'
		)
	);	
	
	register_setting( 'vtm_chargen_options_group', 'vtm_chargen_mustbeloggedin' );
	register_setting( 'vtm_chargen_options_group', 'vtm_chargen_showsecondaries' );
	register_setting( 'vtm_chargen_options_group', 'vtm_chargen_humanity' );
	register_setting( 'vtm_chargen_options_group', 'vtm_chargen_emailtag' ); 			// depreciated
	register_setting( 'vtm_chargen_options_group', 'vtm_chargen_email_from_name' ); 	// depreciated
	register_setting( 'vtm_chargen_options_group', 'vtm_chargen_email_from_address' ); 	// depreciated
	
	register_setting( 'vtm_pm_options_group', 'vtm_pm_mobile_prefix' );
	register_setting( 'vtm_pm_options_group', 'vtm_pm_landline_prefix' );
	register_setting( 'vtm_pm_options_group', 'vtm_pm_telephone_digits' );
	register_setting( 'vtm_pm_options_group', 'vtm_pm_postcode_prefix' );
	register_setting( 'vtm_pm_options_group', 'vtm_pm_ic_postoffice_location' );
	register_setting( 'vtm_pm_options_group', 'vtm_pm_ic_postoffice_enabled' );
	register_setting( 'vtm_pm_options_group', 'vtm_pm_send_to_dead_characters' );
	
	register_setting( 'vtm_email_options_group', 'vtm_emailtag' );
	register_setting( 'vtm_email_options_group', 'vtm_email_debug' );
	register_setting( 'vtm_email_options_group', 'vtm_replyto_name' );
	register_setting( 'vtm_email_options_group', 'vtm_replyto_address' );
	register_setting( 'vtm_email_options_group', 'vtm_method' );
	register_setting( 'vtm_email_options_group', 'vtm_smtp_host' );
	register_setting( 'vtm_email_options_group', 'vtm_smtp_port' );
	register_setting( 'vtm_email_options_group', 'vtm_smtp_username' );
	register_setting( 'vtm_email_options_group', 'vtm_smtp_pw' );
	register_setting( 'vtm_email_options_group', 'vtm_smtp_auth' );
	register_setting( 'vtm_email_options_group', 'vtm_smtp_secure' );
	register_setting( 'vtm_email_options_group', 'vtm_email_signature' );
	register_setting( 'vtm_email_options_group', 'vtm_email_font' );
	register_setting( 'vtm_email_options_group', 'vtm_email_background' );
	register_setting( 'vtm_email_options_group', 'vtm_email_textcolor' );
	register_setting( 'vtm_email_options_group', 'vtm_email_linecolor' );

	register_setting( 'vtm_profile_options_group', 'vtm_max_width' );
	register_setting( 'vtm_profile_options_group', 'vtm_max_height' );
	register_setting( 'vtm_profile_options_group', 'vtm_max_size' );
	register_setting( 'vtm_profile_options_group', 'vtm_user_set_image' );
	register_setting( 'vtm_profile_options_group', 'vtm_user_upload_image' );
	register_setting( 'vtm_profile_options_group', 'vtm_image_effect' );
	register_setting( 'vtm_profile_options_group', 'vtm_user_set_quote' );
	
	register_setting( 'feedingmap_options_group', 'feedingmap_google_api' );  // google api key
	register_setting( 'feedingmap_options_group', 'feedingmap_centre_lat' );  // centre point, latitude
	register_setting( 'feedingmap_options_group', 'feedingmap_centre_long' ); // centre point, latitude
	register_setting( 'feedingmap_options_group', 'feedingmap_zoom' );        // zoom
	register_setting( 'feedingmap_options_group', 'feedingmap_map_type' );    // map type

	// PAGE LINKS
	
	register_setting( 'vtm_links_group', 'vtm_link_editCharSheet' );
	register_setting( 'vtm_links_group', 'vtm_link_viewCharSheet' );
	register_setting( 'vtm_links_group', 'vtm_link_printCharSheet' );
	register_setting( 'vtm_links_group', 'vtm_link_viewCustom' );
	register_setting( 'vtm_links_group', 'vtm_link_viewProfile' );
	register_setting( 'vtm_links_group', 'vtm_link_viewXPSpend' );
	register_setting( 'vtm_links_group', 'vtm_link_viewExtBackgrnd' );
	register_setting( 'vtm_links_group', 'vtm_link_viewCharGen' );

	add_settings_section(
		'vtmpagelinks',
		"Page Links",
		"vtm_render_config_pagelinks",
		'vtm_links_group',
	);
	add_settings_field(
		'vtm_link_editCharSheet',
		'Edit Character Sheet',
		'vtm_link_cb', 'vtm_links_group', 'vtmpagelinks',
		array(
			'label_for'         => 'vtm_link_editCharSheet',
			'newpagename'       => 'New/Edit Character'
		)
	);	
	add_settings_field(
		'vtm_link_viewCharSheet',
		'View Character Sheet',
		'vtm_link_cb', 'vtm_links_group', 'vtmpagelinks',
		array(
			'label_for'         => 'vtm_link_viewCharSheet',
			'newpagename'       => 'View Character'
		)
	);	
	add_settings_field(
		'vtm_link_printCharSheet',
		'Print Character Sheet',
		'vtm_link_cb', 'vtm_links_group', 'vtmpagelinks',
		array(
			'label_for'         => 'vtm_link_printCharSheet',
			'newpagename'       => 'Print Character'
		)
	);	
	add_settings_field(
		'vtm_link_viewProfile',
		'Character Profile',
		'vtm_link_cb', 'vtm_links_group', 'vtmpagelinks',
		array(
			'label_for'         => 'vtm_link_viewProfile',
			'newpagename'       => 'Character Profile'
		)
	);	
	add_settings_field(
		'vtm_link_viewXPSpend',
		'Spend Experience',
		'vtm_link_cb', 'vtm_links_group', 'vtmpagelinks',
		array(
			'label_for'         => 'vtm_link_viewXPSpend',
			'newpagename'       => 'Spend Experience'
		)
	);	
	add_settings_field(
		'vtm_link_viewExtBackgrnd',
		'Extended Background',
		'vtm_link_cb', 'vtm_links_group', 'vtmpagelinks',
		array(
			'label_for'         => 'vtm_link_viewExtBackgrnd',
			'newpagename'       => 'Extended Background'
		)
	);	
	add_settings_field(
		'vtm_link_viewCharGen',
		'Character Generation',
		'vtm_link_cb', 'vtm_links_group', 'vtmpagelinks',
		array(
			'label_for'         => 'vtm_link_viewCharGen',
			'newpagename'       => 'Character Generation'
		)
	);	
	add_settings_field(
		'vtm_link_viewCustom',
		'View Custom Page',
		'vtm_link_cb', 'vtm_links_group', 'vtmpagelinks',
		array(
			'label_for'         => 'vtm_link_viewCustom',
			'newpagename'       => 'My Character'
		)
	);	
	
}
add_action( 'admin_init', 'vtm_register_character_settings' );
#add_action( 'updated_option', 'update_vtm_link_cb', 10, 3);
add_filter( 'pre_update_option_vtm_link_editCharSheet',   'pre_update_vtm_link_cb', 10, 3);
add_filter( 'pre_update_option_vtm_link_viewCustom',      'pre_update_vtm_link_cb', 10, 3);
add_filter( 'pre_update_option_vtm_link_viewCharGen',     'pre_update_vtm_link_cb', 10, 3);
add_filter( 'pre_update_option_vtm_link_viewExtBackgrnd', 'pre_update_vtm_link_cb', 10, 3);
add_filter( 'pre_update_option_vtm_link_viewXPSpend',     'pre_update_vtm_link_cb', 10, 3);
add_filter( 'pre_update_option_vtm_link_viewProfile',     'pre_update_vtm_link_cb', 10, 3);
add_filter( 'pre_update_option_vtm_link_printCharSheet',  'pre_update_vtm_link_cb', 10, 3);
add_filter( 'pre_update_option_vtm_link_viewCharSheet',   'pre_update_vtm_link_cb', 10, 3);

/* function vtm_gvcharacter_options_validate($input) {

	global $wp_roles;

	$options = get_option('vtm_plugin_options');
	
	$options['title'] = trim($input['title']);
	
	
	return $options;
}
*/

/* ADMIN MENUS
----------------------------------------------------------------- */

function vtm_register_character_menu() {
	add_menu_page( "Character Plugin Options", "Characters", "manage_options", "character-plugin", "vtm_character_options");
	add_submenu_page( "character-plugin", "Character Admin",     "Character Admin",     "manage_options", "character-plugin",    "vtm_character_options" );  
	add_submenu_page( "character-plugin", "Character Approval",  "Character Approval",  "manage_options", "vtmcharacter-chargen","vtm_character_chargen_approval" );  
	add_submenu_page( "character-plugin", "Player Admin",        "Player Admin",        "manage_options", "vtmcharacter-player", "vtm_character_players" );  
	add_submenu_page( "character-plugin", "Assign XP",           "Assign XP",           "manage_options", "vtmcharacter-xpassign",  "vtm_character_xp_assign" );  
	add_submenu_page( "character-plugin", "XP Approval",         "XP Approval",         "manage_options", "vtmcharacter-xp",     "vtm_character_experience" );  
	add_submenu_page( "character-plugin", "Backgrounds",         "Backgrounds",         "manage_options", "vtmcharacter-bg",     "vtm_character_backgrounds" );  
	add_submenu_page( "character-plugin", "Path Changes",        "Path Changes",        "manage_options", "vtmcharacter-paths",  "vtm_character_master_path" );  
	if (get_option( 'vtm_feature_temp_stats', '0' ) == 1)
		add_submenu_page( "character-plugin", "Stat Changes",        "Stat Changes",        "manage_options", "vtmcharacter-stats",  "vtm_character_temp_stats" );  
	if (get_option( 'vtm_feature_reports', '0' ) == 1)
		add_submenu_page( "character-plugin", "Reports",             "Reports",             "manage_options", "vtmcharacter-report", "vtm_character_reports" );  
	add_submenu_page( "character-plugin", "Database Tables",     "Data Tables",         "manage_options", "vtmcharacter-data",   "vtm_character_datatables" );  
	add_submenu_page(
		"character-plugin",
		"Configuration",
		"Configuration",
		"manage_options",
		"vtmcharacter-config",
		"vtm_character_config"
	);  

	add_options_page(
		"Configuration",
		"Character Options",
		'manage_options',
		'vtmoptions',
		'vtm_render_config_options'
	);
	

}


function vtm_tabdisplay($tab, $default="merit") {

	$display = "style='display:none'";

	if (isset($_REQUEST['tab'])) {
		if ($_REQUEST['tab'] == $tab)
			$display = "";
	} else if ($tab == $default) {
		$display = "class=default";
	}
		
	print esc_html($display);
		
}

function vtm_make_filter($sqlresult) {
	
	$keys = array('all');
	$vals = array('All');

	foreach ($sqlresult as $item) {
		if (isset($item->ID) && isset($item->NAME) ) {
			array_push($keys, $item->ID);
			array_push($vals, $item->NAME);
		} 
		else {
			$keylist = array_keys(get_object_vars($item));
			if (count($keylist) == 1) {
				array_push($keys, sanitize_key($item->$keylist[0]));
				array_push($vals, $item->$keylist[0]);
			}
		}
	}
	$outarray = array_combine($keys,$vals);

	return $outarray;
}

function vtm_get_tabhighlight($tab, $default){
	if ((isset($_REQUEST['tab']) && $_REQUEST['tab'] == $tab) || (!isset($_REQUEST['tab']) && $tab == $default))
		return "class='nav-tab shown nav-tab-active'";
	return "class='nav-tab'";
}

function vtm_get_tablink($tab, $text, $default = ""){
	$current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
	$current_url = remove_query_arg( 'tab', $current_url );
	$current_url = remove_query_arg( 'action', $current_url );
	$current_url = add_query_arg('tab', $tab, $current_url);
	$markup = '<a id="gvm-@TAB@" href="@HREF@" @SHOWN@>@TEXT@</a>';
	return str_replace(
		Array('@TAB@','@TEXT@','@SHOWN@', '@HREF@'),
			Array($tab, $text, vtm_get_tabhighlight($tab, $default),htmlentities($current_url)),
			$markup
		);
}
function vtm_get_option_tablink($tab, $text, $default = ""){

	$active_tab = $default;
	if( isset( $_GET[ 'tab' ] ) ) {
		$active_tab = $_GET[ 'tab' ];
	} // end if

	$current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
	$current_url = remove_query_arg( 'tab', $current_url );
	$current_url = remove_query_arg( 'action', $current_url );
	$current_url = add_query_arg('tab', $tab, $current_url);
	$markup = '<a id="gvm-@TAB@" href="@HREF@" class="nav-tab @SHOWN@">@TEXT@</a>';
	return str_replace(
		Array('@TAB@','@TEXT@','@SHOWN@', '@HREF@'),
			Array($tab, $text, ($active_tab == $tab ? 'nav-tab-active' : ''),htmlentities($current_url)),
			$markup
		);
}

/* DISPLAY TABS
-------------------------------------------------- */
function vtm_character_datatables() {
	global $vtmglobal;
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( 'You do not have sufficient permissions to access this page.' );
	}
	?>
	<div class="wrap">
		<h2>Database Tables</h2>
		<h2 class="nav-tab-wrapper">
			<?php echo wp_kses(vtm_get_tablink('stat',        'Attributes and Stats', 'stat'), vtm_tablink_allowedhtml()); ?>
			<?php echo wp_kses(vtm_get_tablink('skill',       'Abilities'), vtm_tablink_allowedhtml()); ?>
			<?php echo wp_kses(vtm_get_tablink('skill_type',  'Ability Categories'), vtm_tablink_allowedhtml()); ?>
			<?php echo wp_kses(vtm_get_tablink('clans',       'Clans'), vtm_tablink_allowedhtml()); ?>
			<?php echo wp_kses(vtm_get_tablink('disc',        'Disciplines'), vtm_tablink_allowedhtml()); ?>
			<?php echo wp_kses(vtm_get_tablink('bgdata',      'Backgrounds'), vtm_tablink_allowedhtml()); ?>
			<?php if (isset($vtmglobal['config']->USE_NATURE_DEMEANOUR) && $vtmglobal['config']->USE_NATURE_DEMEANOUR == 'Y') echo wp_kses(vtm_get_tablink('nature',  'Nature/Demeanour'), vtm_tablink_allowedhtml()); ?>
			<?php echo wp_kses(vtm_get_tablink('merit',       'Merits'), vtm_tablink_allowedhtml()); ?>
			<?php echo wp_kses(vtm_get_tablink('flaw',        'Flaws'), vtm_tablink_allowedhtml()); ?>
			<?php echo wp_kses(vtm_get_tablink('ritual',      'Rituals'), vtm_tablink_allowedhtml()); ?>
			<?php echo wp_kses(vtm_get_tablink('enlighten',   'Paths of Enlightenment'), vtm_tablink_allowedhtml()); ?>
			<?php echo wp_kses(vtm_get_tablink('path',        'Paths of Magik'), vtm_tablink_allowedhtml()); ?>
			<?php echo wp_kses(vtm_get_tablink('costmodel',   'Cost Models'), vtm_tablink_allowedhtml()); ?>
			<?php echo wp_kses(vtm_get_tablink('book',        'Sourcebooks'), vtm_tablink_allowedhtml()); ?>
			<?php echo wp_kses(vtm_get_tablink('question',    'Background Questions'), vtm_tablink_allowedhtml()); ?>
			<?php echo wp_kses(vtm_get_tablink('sector',      'Sectors'), vtm_tablink_allowedhtml()); ?>
			<?php echo wp_kses(vtm_get_tablink('domain',      'Cities/Locations'), vtm_tablink_allowedhtml()); ?>
			<?php echo wp_kses(vtm_get_tablink('sect',        'Affiliations'), vtm_tablink_allowedhtml()); ?>
			<?php echo wp_kses(vtm_get_tablink('office',      'Offices'), vtm_tablink_allowedhtml()); ?>
			<?php echo wp_kses(vtm_get_tablink('combo',       'Combination Disciplines'), vtm_tablink_allowedhtml()); ?>
			<?php echo wp_kses(vtm_get_tablink('generation',  'Generation'), vtm_tablink_allowedhtml()); ?>
			<?php echo wp_kses(vtm_get_tablink('template',    'Character Templates'), vtm_tablink_allowedhtml()); ?>
			<?php if (get_option( 'vtm_feature_maps', '0' ) == 1) echo wp_kses(vtm_get_tablink('mapowner', 'Map Owners'), vtm_tablink_allowedhtml()); ?>
			<?php if (get_option( 'vtm_feature_maps', '0' ) == 1) echo wp_kses(vtm_get_tablink('mapdomain','Map Locations'), vtm_tablink_allowedhtml()); ?>
		</h2>
		<div class="gvadmin_content">
		<?php
		
		$tabselect = isset($_REQUEST['tab']) ? $_REQUEST['tab'] : '';
		
		switch ($tabselect) {
			case 'stat':
				vtm_render_stat_page("stat");
				break;
			case 'skill':
				vtm_render_skill_page("skill");
				break;
			case 'skill_type':
				vtm_render_skill_types_page("skill_type");
				break;
			case 'merit':
				vtm_render_meritflaw_page("merit");
				break;
			case 'flaw':
				vtm_render_meritflaw_page("flaw");
				break;
			case 'ritual':
				vtm_render_rituals_page(); 
				break;
			case 'book':
				vtm_render_sourcebook_page();
				break;
			case 'clans':
				vtm_render_clan_page(); 
				break;
			case 'disc':
				vtm_render_discipline_page();
				break;
			case 'bgdata':
				vtm_render_background_data();
				break;
			case 'sector':
				vtm_render_sector_data();
				break;
			case 'question':
				vtm_render_question_data();
				break;
			case 'costmodel':
				vtm_render_costmodel_page("costmodel");
				break;
			case 'enlighten':
				vtm_render_enlightenment_page();
				break;
			case 'path':
				vtm_render_paths_page();
				break;
			case 'nature':
				vtm_render_nature_page();
				break;
			case 'domain':
				vtm_render_domain_page();
				break;
			case 'office':
				vtm_render_office_page();
				break;
			case 'combo':
				vtm_render_combo_page();
				break;
			case 'mapowner':
				vtm_render_owner_data();
				break;
			case 'mapdomain':
				vtm_render_domain_data();
				break;
			case 'generation':
				vtm_render_generation_data();
				break;
			case 'template':
				vtm_render_template_data();
				break;
			case 'sect':
				vtm_render_sect_page();
				break;
			default:
				vtm_render_stat_page("stat");
		}
		
		?>
		</div>
	</div>
	
	<?php
}
?>