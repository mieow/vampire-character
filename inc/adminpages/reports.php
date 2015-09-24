<?php

function vtm_character_reports () {

	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}

	?>
	<div class="wrap">
		<h2>Reports</h2>
	<?php
	$report = isset($_REQUEST['report']) ? $_REQUEST['report'] : '';
	
	vtm_render_select_report($report);
	vtm_render_report(vtm_report_switch($report));
	
	?>
	</div>

	<?php
}


function vtm_render_select_report($report) {

	echo "<h3>Select Report</h3>";
	echo "<form id='select_report_form' method='post'>\n";
	echo "<select name='report'>\n";
	echo "<option value='0'>[Select Report]</option>\n";
	
	echo "<option value='meritflaw_report' ";
	selected($report,'meritflaw_report');
	echo ">Merits and Flaws</option>\n";
	
	echo "<option value='quotes_report' ";
	selected($report,'quotes_report');
	echo ">Profile Quotes</option>\n";
	
	echo "<option value='prestige_report' ";
	selected($report,'prestige_report');
	echo ">Clan Prestige</option>\n";
	
	echo "<option value='signin_report' ";
	selected($report,'signin_report');
	echo ">Signin Sheet</option>\n";
	
	echo "<option value='sect_report' ";
	selected($report,'sect_report');
	echo ">Affiliation List</option>\n";

	echo "<option value='sector_report' ";
	selected($report,'sector_report');
	echo ">Sectors & Backgrounds</option>\n";

	echo "<option value='activity_report' ";
	selected($report,'activity_report');
	echo ">Character Activity</option>\n";

	echo "</select>\n";
	echo "<input type='submit' name='submit_report' class='button-primary' value='Display Report' />\n";
	echo "</form>\n";

}


function vtm_render_report($reporttable) {
	
	if ($reporttable == '')
		return ;
	
	$reporttable->prepare_items(); 
	$current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
	?>
	<form id="<?php print $_REQUEST['report'] ?>" method="get" action='<?php print htmlentities($current_url); ?>'>
		<input type="hidden" name="page"   value="<?php print $_REQUEST['page'] ?>" />
		<input type="hidden" name="report" value="<?php print $_REQUEST['report'] ?>" />
		<?php $reporttable->display(); ?>
	</form>
	
	<?php
}

function vtm_report_redirect()
{
    if (isset($_REQUEST['report']) && isset($_REQUEST['format'])) {
		
		$reporttable = vtm_report_switch($_REQUEST['report']);
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
			$reportclass->pdftitle = "Signin Sheet " . Date('F Y');
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
	
	}

	return $reportclass;
}

?>