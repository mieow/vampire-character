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
		$delete      = isset($_REQUEST['delete']) ? $_REQUEST['delete'] : array();
			
		$wpdb->show_errors();
		
		for ($i=0;$i<count($generations);$i++) {
			
			if (!empty($names[$i])) {
				//DATA VALIDATION
				$err = 0;
				if (!is_numeric($bloodpools[$i]) || $bloodpools[$i] <= 0) {
					$err = 1;
					echo "<p style='color:red'>Bloodpool for {$names[$i]}th generation should be a number greater than zero</p>";
				}
				if (!is_numeric($perround[$i]) || $perround[$i] <= 0) {
					$err = 1;
					echo "<p style='color:red'>Blood per Round for {$names[$i]}th generation should be a number greater than zero</p>";
				}
				if (!is_numeric($maxrating[$i]) || $maxrating[$i] <= 0) {
					$err = 1;
					echo "<p style='color:red'>The maximum rating for {$names[$i]}th generation should be a number greater than zero</p>";
				}
				if (!is_numeric($maxdisc[$i]) || $maxdisc[$i] <= 0) {
					$err = 1;
					echo "<p style='color:red'>The maximum Discipline rating for {$names[$i]}th generation should be a number greater than zero</p>";
				}
			
				// Update/Add
				if (!$err) {
					if ($generations[$i] == 0) {
						// add
						$data = array (
							'NAME' => $names[$i],
							'BLOODPOOL' => $bloodpools[$i],
							'BLOOD_PER_ROUND' => $perround[$i],
							'MAX_RATING' => $maxrating[$i],
							'MAX_DISCIPLINE' => $maxdisc[$i]
						);
						$wpdb->insert(VTM_TABLE_PREFIX . "GENERATION",
							$data,
							array (
								'%s',
								'%d',
								'%d',
								'%d',
								'%d',
							)
						);
						$id = $wpdb->insert_id;
						if ($id == 0) {
							echo "<p style='color:red'>Could not add {$names[$i]}th generation ({$generations[$i]})</p>";
						}
					} else {
						// update
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
		}
		
		// Delete any rows specified
		foreach ($delete as $id => $result) {
			if ($result == 'on') {
				$sql = "DELETE FROM " . VTM_TABLE_PREFIX . "GENERATION WHERE ID = %s";
				$wpdb->get_results($wpdb->prepare($sql, $id));
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
		<th class="manage-column">Delete</th>
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
					<input type="number" name="bloodpool[]" value="<?php echo $data->BLOODPOOL; ?>" size=4>
				</td>
				<td <?php echo $class; ?>>
					<input type="number" name="bloodperrnd[]" value="<?php echo $data->BLOOD_PER_ROUND; ?>" size=4>
				</td>
				<td <?php echo $class; ?>>
					<input type="number" name="maxrating[]" value="<?php echo $data->MAX_RATING; ?>" size=4>
				</td>
				<td <?php echo $class; ?>>
					<input type="number" name="maxdisc[]" value="<?php echo $data->MAX_DISCIPLINE; ?>" size=4>
				</td>
				<td <?php echo $class; ?>>
					<?php if ($data->ID != $vtmglobal['config']->DEFAULT_GENERATION_ID) { ?>
					<input type="checkbox" name="delete[<?php echo $data->ID; ?>]">
					<?php } ?>
				</td>
			</tr>
			<?php
		}
	?>
			<tr>
				<td <?php echo $class; ?>>
					<input type="hidden" name="genID[]" value="0" size=4>
					<input type="text" name="genName[]" value="" size=4>th
				</td>
				<td <?php echo $class; ?>>
					<input type="number" name="bloodpool[]" value="" size=4>
				</td>
				<td <?php echo $class; ?>>
					<input type="number" name="bloodperrnd[]" value="" size=4>
				</td>
				<td <?php echo $class; ?>>
					<input type="number" name="maxrating[]" value="" size=4>
				</td>
				<td <?php echo $class; ?>>
					<input type="number" name="maxdisc[]" value="" size=4>
				</td>
				<td <?php echo $class; ?>>
				</td>
			</tr>
	</table>
	<input type="submit" name="save_generation" class="button-primary" value="Save" />
	</form>
	
	</div>
	<?php
	
}




?>