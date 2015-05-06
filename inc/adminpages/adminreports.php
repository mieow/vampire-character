<?php

function vtm_character_reports () {

	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}

	?>
	<div class="wrap">
		<h2>Reports</h2>
	<?php
	vtm_render_select_report();
	
	switch ($_REQUEST['report']) {
		case 'meritflaw_report':
			vtm_render_meritflaw_report();
			break;
	
	}
	
	?>
	</div>

	<?php
}


function vtm_render_select_report() {

	echo "<h3>Select Report</h3>";
	echo "<form id='select_report_form' method='post'>\n";
	echo "<select name='report'>\n";
	echo "<option value='0'>[Select Report]</option>\n";
	
	echo "<option value='meritflaw_report' ";
	selected($_REQUEST['report'],'meritflaw_report');
	echo ">Merits and Flaws</option>\n";
	
	echo "</select>\n";
	echo "<input type='submit' name='submit_report' class='button-primary' value='Display Report' />\n";
	echo "</form>\n";

}


function vtm_render_meritflaw_report() {
	
	$reporttable = new vtmclass_report_flaws();
	$reporttable->prepare_items(); ?>
	
	<form id="meritflaw_report" method="get" action=''>
		<input type="hidden" name="page"   value="<?php print $_REQUEST['page'] ?>" />
		<input type="hidden" name="report" value="<?php print $_REQUEST['report'] ?>" />
		<?php $reporttable->display(); ?>
	</form>
	
	<?php
}

?>