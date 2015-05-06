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
	
	
	switch ($report) {
		case 'meritflaw_report':
			vtm_render_report(new vtmclass_report_flaws());
			break;
		case 'quotes_report':
			vtm_render_report(new vtmclass_report_quotes());
			break;
		case 'prestige_report':
			vtm_render_report(new vtmclass_report_prestige());
			break;
		case 'signin_report':
			vtm_render_report(new vtmclass_report_signin());
			break;
		case 'sect_report':
			vtm_render_report(new vtmclass_report_sect());
			break;
		case 'activity_report':
			vtm_render_report(new vtmclass_report_activity());
			break;
		case 'sector_report':
			vtm_render_report(new vtmclass_report_sector());
			break;
	
	}
	
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
	echo ">Sect List</option>\n";

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

?>