<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require_once VTM_CHARACTER_URL . 'inc/adminpages/reportclasses.php';

function vtm_character_reports () {

	if ( !current_user_can( 'vtm_reports' ) )  {
		wp_die( esc_html(__( 'You do not have sufficient permissions to access this page.', 'vampire-character' ) ));
	}

	?>
	<div class="wrap">
		<h2>Reports</h2>
	<?php
	$report = '';
	if (isset($_POST['submit_report'])) {
		check_admin_referer('vtm_select_report');
		if (isset($_POST['report']) && is_string($_POST['report'])) {
			$report = sanitize_key(wp_unslash($_POST['report']));
		}
	} elseif (isset($_GET['report'])) {
		check_admin_referer('vtm_run_report');
		if (is_string($_GET['report'])) {
			$report = sanitize_key(wp_unslash($_GET['report']));
		}
	}
	
	vtm_render_select_report($report);
	vtm_render_report(vtm_report_switch($report));
	
	?>
	</div>

	<?php
}


function vtm_render_select_report($report) {

	echo "<h3>Select Report</h3>";
	echo "<form id='select_report_form' method='post'>\n";
	wp_nonce_field('vtm_select_report');
	echo "<select name='report'>\n";
	echo "<option value='0'>[Select Report]</option>\n";

	if (current_user_can('vtm_view_player')) {
		echo "<option value='signin_report' ";
		selected($report,'signin_report');
		echo ">Signin Sheet</option>\n";

		echo "<option value='activity_report' ";
		selected($report,'activity_report');
		echo ">Character Activity</option>\n";

		echo "<option value='xp_report' ";
		selected($report,'xp_report');
		echo ">XP Activity</option>\n";

		echo "<option value='consent_report' ";
		selected($report,'consent_report');
		echo ">Consent</option>\n";

	}

	if (current_user_can('vtm_view_character')) {
		echo "<option value='meritflaw_report' ";
		selected($report,'meritflaw_report');
		echo ">Merits and Flaws</option>\n";

		echo "<option value='quotes_report' ";
		selected($report,'quotes_report');
		echo ">Profile Quotes</option>\n";
		
		echo "<option value='prestige_report' ";
		selected($report,'prestige_report');
		echo ">Clan Prestige</option>\n";

		echo "<option value='sect_report' ";
		selected($report,'sect_report');
		echo ">Affiliation List</option>\n";

		echo "<option value='sector_report' ";
		selected($report,'sector_report');
		echo ">Sectors & Backgrounds</option>\n";
	}
	
	echo "</select>\n";
	echo "<input type='submit' name='submit_report' class='button-primary' value='Display Report' />\n";
	echo "</form>\n";

}


function vtm_render_report($reporttable) {
	
	if ($reporttable == '')
		return ;
	
	$reporttable->prepare_items(); 
	$current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
	$current_url = remove_query_arg('_wpnonce', $current_url);
	?>
	<form id="<?php print esc_html($_REQUEST['report']) ?>" method="get" action='<?php print esc_url($current_url); ?>'>
		<input type="hidden" name="page"   value="<?php print esc_html($_REQUEST['page']) ?>" />
		<input type="hidden" name="report" value="<?php print esc_html($_REQUEST['report']) ?>" />
		<?php wp_nonce_field('vtm_run_report'); ?>
		<?php $reporttable->display(); ?>
	</form>
	
	<?php
}

function vtm_report_redirect()
{
    if (isset($_REQUEST['report']) && isset($_REQUEST['format'])) {
		if (!current_user_can('vtm_reports')) {
			wp_die(esc_html(__('You do not have sufficient permissions to access this page.', 'vampire-character')));
		}
		check_admin_referer('vtm_run_report');
		if (!is_string($_REQUEST['report'])) {
			wp_die(esc_html(__('Invalid report.', 'vampire-character')));
		}
		$report = sanitize_key(wp_unslash($_REQUEST['report']));
		
		$reporttable = vtm_report_switch($report);
		if (!is_object($reporttable)) {
			wp_die(esc_html(__('Invalid report.', 'vampire-character')));
		}
		$reporttable->prepare_items(); 
		
		switch($_REQUEST['format']) {
			case 'pdf':
				$reporttable->output_report();
				break;
			case 'csv':
				$reporttable->output_csv();
				break;
		}
		
		exit;
    }
}
add_action( 'admin_init', 'vtm_report_redirect', 1 );


function vtm_report_switch($report) {
	$reportclass = "";
	
	switch ($report) {
		case 'meritflaw_report':
			$reportclass = new vtmclass_report_flaws();
			$reportclass->pdftitle = "Merits and Flaws Report";
			$reportclass->pdforientation = 'L';
			break;
		case 'quotes_report':
			$reportclass = new vtmclass_report_quotes();
			$reportclass->pdftitle = "Profile Quotes Report";
			$reportclass->pdforientation = 'L';
			break;
		case 'prestige_report':
			$reportclass = new vtmclass_report_prestige();
			$reportclass->pdftitle = "Clan Prestige Report";
			$reportclass->pdforientation = 'L';
			break;
		case 'signin_report':
			$reportclass = new vtmclass_report_signin();
			$reportclass->pdftitle = "Signin Sheet " . gmdate('F Y');
			$reportclass->pdforientation = 'P';
			break;
		case 'sect_report':
			$reportclass = new vtmclass_report_sect();
			$reportclass->pdftitle = "Character Sects List";
			$reportclass->pdforientation = 'P';
			break;
		case 'activity_report':
			$reportclass = new vtmclass_report_activity();
			$reportclass->pdftitle = "Character Activity";
			$reportclass->pdforientation = 'P';
			break;
		case 'sector_report':
			$reportclass = new vtmclass_report_sector();
			$reportclass->pdftitle = "Sector and Background";
			$reportclass->pdforientation = 'P';
			break;
		case 'xp_report':
			$reportclass = new vtmclass_report_xp();
			$reportclass->pdftitle = "Experience (1 year)";
			$reportclass->pdforientation = 'P';
			break;
		case 'consent_report':
			$reportclass = new vtmclass_report_consent();
			$reportclass->pdftitle = "Consent Form Information";
			$reportclass->pdforientation = 'P';
			break;
	
	}

	return $reportclass;
}

?>