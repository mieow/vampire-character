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

	register_setting( 'vtm_features_group', 'vtm_feature_temp_stats' );
	register_setting( 'vtm_features_group', 'vtm_feature_maps' );
	register_setting( 'vtm_features_group', 'vtm_feature_reports' );
	register_setting( 'vtm_features_group', 'vtm_feature_email' );
	register_setting( 'vtm_features_group', 'vtm_feature_news' );
	
	register_setting( 'vtm_chargen_options_group', 'vtm_chargen_mustbeloggedin' );
	register_setting( 'vtm_chargen_options_group', 'vtm_chargen_showsecondaries' );
	register_setting( 'vtm_chargen_options_group', 'vtm_chargen_emailtag' ); 			// depreciated
	register_setting( 'vtm_chargen_options_group', 'vtm_chargen_email_from_name' ); 	// depreciated
	register_setting( 'vtm_chargen_options_group', 'vtm_chargen_email_from_address' ); 	// depreciated
	
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
	
	register_setting( 'feedingmap_options_group', 'feedingmap_google_api' );  // google api key
	register_setting( 'feedingmap_options_group', 'feedingmap_centre_lat' );  // centre point, latitude
	register_setting( 'feedingmap_options_group', 'feedingmap_centre_long' ); // centre point, latitude
	register_setting( 'feedingmap_options_group', 'feedingmap_zoom' );        // zoom
	register_setting( 'feedingmap_options_group', 'feedingmap_map_type' );    // map type

}
add_action( 'admin_menu', 'vtm_register_character_settings' );


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
	add_menu_page( "Character Plugin Options", "V:tM Characters", "manage_options", "character-plugin", "vtm_character_options");
	add_submenu_page( "character-plugin", "Character Admin",     "Character Admin",     "manage_options", "character-plugin",    "vtm_character_options" );  
	add_submenu_page( "character-plugin", "Character Approval",  "Character Approval",  "manage_options", "vtmcharacter-chargen","vtm_character_chargen_approval" );  
	add_submenu_page( "character-plugin", "Player Admin",        "Player Admin",        "manage_options", "vtmcharacter-player", "vtm_character_players" );  
	add_submenu_page( "character-plugin", "Assign XP",           "Assign XP",           "manage_options", "vtmcharacter-xpassign",  "vtm_character_xp_assign" );  
	add_submenu_page( "character-plugin", "XP Approval",         "XP Approval",         "manage_options", "vtmcharacter-xp",     "vtm_character_experience" );  
	add_submenu_page( "character-plugin", "Backgrounds",         "Backgrounds",         "manage_options", "vtmcharacter-bg",     "vtm_character_backgrounds" );  
	add_submenu_page( "character-plugin", "Path Changes",        "Path Changes",        "manage_options", "vtmcharacter-paths",  "vtm_character_master_path" );  
	if (get_option( 'vtm_feature_maps', '0' ) == 1)
		add_submenu_page( "character-plugin", "Stat Changes",        "Stat Changes",        "manage_options", "vtmcharacter-stats",  "vtm_character_temp_stats" );  
	if (get_option( 'vtm_feature_reports', '0' ) == 1)
		add_submenu_page( "character-plugin", "Reports",             "Reports",             "manage_options", "vtmcharacter-report", "vtm_character_reports" );  
	add_submenu_page( "character-plugin", "Database Tables",     "Data Tables",         "manage_options", "vtmcharacter-data",   "vtm_character_datatables" );  
	add_submenu_page( "character-plugin", "Configuration",       "Configuration",       "manage_options", "vtmcharacter-config", "vtm_character_config" );  
}


function vtm_tabdisplay($tab, $default="merit") {

	$display = "style='display:none'";

	if (isset($_REQUEST['tab'])) {
		if ($_REQUEST['tab'] == $tab)
			$display = "";
	} else if ($tab == $default) {
		$display = "class=default";
	}
		
	print $display;
		
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

function vtm_get_tabhighlight($tab){
	if ((isset($_REQUEST['tab']) && $_REQUEST['tab'] == $tab))
		return "class='shown'";
	return "";
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

/* DISPLAY TABS
-------------------------------------------------- */
function vtm_character_datatables() {
	global $vtmglobal;
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	?>
	<div class="wrap">
		<h2>Database Tables</h2>
		<div class="gvadmin_nav">
			<ul>
				<li><?php echo vtm_get_tablink('stat',   'Attributes and Stats'); ?></li>
				<li><?php echo vtm_get_tablink('skill',  'Abilities'); ?></li>
				<li><?php echo vtm_get_tablink('merit',  'Merits'); ?></li>
				<li><?php echo vtm_get_tablink('flaw',   'Flaws'); ?></li>
				<li><?php echo vtm_get_tablink('ritual', 'Rituals'); ?></li>
				<li><?php echo vtm_get_tablink('book',   'Sourcebooks'); ?></li>
				<li><?php echo vtm_get_tablink('clans',  'Clans'); ?></li>
				<li><?php echo vtm_get_tablink('disc',   'Disciplines'); ?></li>
				<li><?php echo vtm_get_tablink('bgdata', 'Backgrounds'); ?></li>
				<li><?php echo vtm_get_tablink('sector', 'Sectors'); ?></li>
				<li><?php echo vtm_get_tablink('question', 'Background Questions'); ?></li>
				<li><?php echo vtm_get_tablink('costmodel', 'Cost Models'); ?></li>
				<li><?php echo vtm_get_tablink('enlighten', 'Paths of Enlightenment'); ?></li>
				<li><?php echo vtm_get_tablink('path',    'Paths of Magik'); ?></li>
				<li><?php if (isset($vtmglobal['config']->USE_NATURE_DEMEANOUR) && $vtmglobal['config']->USE_NATURE_DEMEANOUR == 'Y') echo vtm_get_tablink('nature',  'Nature/Demeanour'); ?></li>
				<li><?php echo vtm_get_tablink('domain',  'Domains'); ?></li>
				<li><?php echo vtm_get_tablink('office',  'Offices'); ?></li>
				<li><?php echo vtm_get_tablink('combo',   'Combination Disciplines'); ?></li>
				<li><?php echo vtm_get_tablink('generation', 'Generation'); ?></li>
				<li><?php if (get_option( 'vtm_feature_maps', '0' ) == 1) echo vtm_get_tablink('mapowner', 'Map Domain Owners'); ?></li>
				<li><?php if (get_option( 'vtm_feature_maps', '0' ) == 1) echo vtm_get_tablink('mapdomain','Map Domains'); ?></li>
				<li><?php echo vtm_get_tablink('template', 'Character Templates'); ?></li>
			</ul>
		</div>
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
			default:
				vtm_render_stat_page("stat");
		}
		
		?>
		</div>
	</div>
	
	<?php
}
?>