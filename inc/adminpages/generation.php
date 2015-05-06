<?php

function vtm_render_generation_data() {
	global $wpdb;
	global $vtmglobal;

	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	
	if (isset($_REQUEST['save_generation'])) {
		$generations = $_REQUEST['genID'];
		$names       = $_REQUEST['genName'];
		$bloodpools  = $_REQUEST['bloodpool'];
		$perround    = $_REQUEST['bloodperrnd'];
		$maxrating   = $_REQUEST['maxrating'];
		$maxdisc     = $_REQUEST['maxdisc'];
	
		$wpdb->show_errors();
		
		for ($i=0;$i<count($generations);$i++) {
			//DATA VALIDATION
			
			$err = 0;
			if (!is_numeric($bloodpools[$i]) || $bloodpools[$i] <= 0) {
				$err = 1;
				echo "<p style='color:red'>Bloodpool for {$generations[$i]}th generation should be a number greater than zero</p>";
			}
			if (!is_numeric($perround[$i]) || $perround[$i] <= 0) {
				$err = 1;
				echo "<p style='color:red'>Blood per Round for {$generations[$i]}th generation should be a number greater than zero</p>";
			}
			if (!is_numeric($maxrating[$i]) || $maxrating[$i] <= 0) {
				$err = 1;
				echo "<p style='color:red'>The maximum rating for {$generations[$i]}th generation should be a number greater than zero</p>";
			}
			if (!is_numeric($maxdisc[$i]) || $maxdisc[$i] <= 0) {
				$err = 1;
				echo "<p style='color:red'>The maximum Discipline rating for {$generations[$i]}th generation should be a number greater than zero</p>";
			}
		
			if (!$err) {
				$data = array (
					'BLOODPOOL' => $bloodpools[$i],
					'BLOOD_PER_ROUND' => $perround[$i],
					'MAX_RATING' => $maxrating[$i],
					'MAX_DISCIPLINE' => $maxdisc[$i]
				);
				$result = $wpdb->update(VTM_TABLE_PREFIX . "GENERATION",
							$data,
							array (
								'ID' => $generations[$i]
							)
				);
				if ($result) 
					echo "<p style='color:green'>Updated {$names[$i]}th generation</p>";
				else if ($result === 0) 
					echo "";
				else {
					$wpdb->print_error();
					echo "<p style='color:red'>Could not update {$names[$i]}th generation ({$generations[$i]})</p>";
				}
			}
		}
	}

	$sql = "SELECT * 
			FROM 
				" . VTM_TABLE_PREFIX. "GENERATION
			ORDER BY
				BLOODPOOL DESC, MAX_DISCIPLINE DESC";
	$result = $wpdb->get_results($sql);
		
	?>
	
	<div class="wrap">
	
	<form id='generation_form' method='post'>
	<table class="wp-list-table widefat">
	<tr>
		<th class="manage-column">Generation</th>
		<th class="manage-column">Bloodpool</th>
		<th class="manage-column">Blood per Round</th>
		<th class="manage-column">Max Rating</th>
		<th class="manage-column">Max Discipline</th>
	</tr>
	<?php
		foreach ($result as $data) {
			$class = $vtmglobal['config']->DEFAULT_GENERATION_ID == $data->ID ? "class='defaultgen'" : "";
			?>
			<tr>
				<td <?php echo $class; ?>>
					<input type="hidden" name="genID[]" value="<?php echo $data->ID; ?>" size=4>
					<input type="hidden" name="genName[]" value="<?php echo vtm_formatOutput($data->NAME); ?>" size=4>
					<?php echo $data->NAME; ?>th
				</td>
				<td <?php echo $class; ?>>
					<input type="text" name="bloodpool[]" value="<?php echo $data->BLOODPOOL; ?>" size=4>
				</td>
				<td <?php echo $class; ?>>
					<input type="text" name="bloodperrnd[]" value="<?php echo $data->BLOOD_PER_ROUND; ?>" size=4>
				</td>
				<td <?php echo $class; ?>>
					<input type="text" name="maxrating[]" value="<?php echo $data->MAX_RATING; ?>" size=4>
				</td>
				<td <?php echo $class; ?>>
					<input type="text" name="maxdisc[]" value="<?php echo $data->MAX_DISCIPLINE; ?>" size=4>
				</td>
			</tr>
			<?php
		}
	?>
	</table>
	<input type="submit" name="save_generation" class="button-primary" value="Save" />
	</form>
	
	</div>
	<?php
	
}




?>