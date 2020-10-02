<?php



function vtm_render_template_data(){
	global $wpdb;
	
	$id = "";
	$type = "template";
	
	//Default template options
	$settings = vtm_default_chargen_settings();

	$current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
	$current_url = remove_query_arg( 'action', $current_url );
	$wpdb->show_errors();
	
	$roads = vtm_listRoadsOrPaths();
	$sects = vtm_get_sects();
	
	$thisaction = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
	
	
	$id = 0;
	switch ($thisaction) {
		case "loadtemplate":
			$id = $_REQUEST['template'];			
			break;
		case "save":
		
			if (isset($_REQUEST['do_new_' . $type]) || (isset($_REQUEST['do_save_' . $type]) && $_REQUEST['template'] == 0) ) {
				if (empty($_REQUEST["template_name"])) {
					echo "<p style='color:red'><b>Error: </b>Enter a template name</p>";
					break;
				}
				/* insert */
				$dataarray = array (
					'NAME'        => $_REQUEST["template_name"],
					'DESCRIPTION' => $_REQUEST["template_desc"],
					'VISIBLE'     => $_REQUEST["template_visible"],
				);
				$wpdb->insert(VTM_TABLE_PREFIX . "CHARGEN_TEMPLATE",
							$dataarray,
							array (
								'%s',
								'%s',
							)
						);
				
				$id = $wpdb->insert_id;
				if ($id == 0) {
					echo "<p style='color:red'><b>Error:</b>Character Template could not be inserted (";
					echo ")</p>";
				} else {
				
					// save template options
					foreach ($settings as $option => $val) {
						$wpdb->insert(VTM_TABLE_PREFIX . "CHARGEN_TEMPLATE_OPTIONS",
							array(
								'NAME' => $option,
								'VALUE' => $_REQUEST[$option],
								'TEMPLATE_ID' => $id
							),
							array('%s', '%s', '%d')
						);
					}

					// save template defaults
					$tables    = $_REQUEST['table'];
					$items     = $_REQUEST['item'];
					$sectors   = $_REQUEST['item_sector'];
					$comments  = $_REQUEST['item_spec'];
					$levels    = $_REQUEST['item_level'];
					$multiples = isset($_REQUEST['multiple']) ? $_REQUEST['multiple'] : array();
					for ($i = 0 ; $i < count($items) ; $i++) {
						if ($levels[$i] > 0 && $items[$i] != 0) {
						
							if (!isset($multiples[$i])) {
								if ($tables[$i] != 'BACKGROUND') {
									$sql = "SELECT MULTIPLE FROM " . VTM_TABLE_PREFIX . $tables[$i] . " WHERE ID = %s";
									$multiples[$i] = $wpdb->get_var($wpdb->prepare($sql, $items[$i]));
								} else {
									$multiples[$i] = 'N';
								}
							}
							
							$data = array(
									'TEMPLATE_ID'  => $id,
									'CHARTABLE'    => 'CHARACTER_' . $tables[$i],
									'ITEMTABLE'    => $tables[$i],
									'ITEMTABLE_ID' => $items[$i],
									'SECTOR_ID'      => isset($sectors[$i]) ? $sectors[$i] : 0,
									'SPECIALISATION' => isset($comments[$i]) ? $comments[$i] : '',
									'LEVEL'          => $levels[$i],
									'MULTIPLE'       => $multiples[$i]
								);
						
							//print_r($data);
							$wpdb->insert(VTM_TABLE_PREFIX . "CHARGEN_TEMPLATE_DEFAULTS",
								$data,
								array('%d', '%s', '%s', '%d', '%d', '%s', '%d', '%s')
							);
						}
					}
					
					// save template maximums
					$tables    = $_REQUEST['max_table'];
					$items     = $_REQUEST['max_item'];
					$levels    = $_REQUEST['max_level'];
					for ($i = 0 ; $i < count($items) ; $i++) {
						if ($levels[$i] > 0 && $items[$i] != 0) {
						
							$data = array(
									'TEMPLATE_ID'  => $id,
									'ITEMTABLE'    => $tables[$i],
									'ITEMTABLE_ID' => $items[$i],
									'LEVEL'          => $levels[$i],
								);
						
							//print_r($data);
							$wpdb->insert(VTM_TABLE_PREFIX . "CHARGEN_TEMPLATE_MAXIMUM",
								$data,
								array('%d', '%s', '%d', '%d')
							);
						}
					}
					
					// save primary path defaults
				
					$clans       = $_REQUEST['ppclanid'];
					$disciplines = $_REQUEST['ppdiscid'];
					$paths       = $_REQUEST['pppathid'];

					for ($i = 0 ; $i < count($clans) ; $i++) {
						$data = array(
								'TEMPLATE_ID' => $id,
								'PATH_ID'     => $paths[$i],
								'CLAN_ID'     => $clans[$i],
								'DISCIPLINE_ID' => $disciplines[$i],
							);
					
						//print_r($data);
						$wpdb->insert(VTM_TABLE_PREFIX . "CHARGEN_PRIMARY_PATH",
							$data,
							array('%d', '%d', '%d', '%d')
						);
					}

				}
			} 
			elseif (isset($_REQUEST['do_delete_' . $type])) {
				if ($_REQUEST['template'] == 0) {
					echo "<p style='color:red'>Select template before deleting</p>";
				} else {
					$id = $_REQUEST['template'];
					/* delete */
					
					/* Check if model in use */
					//$ok = 1;
					$sql = "select 
						ch.ID, ch.NAME
					from 
						" . VTM_TABLE_PREFIX . "CHARACTER ch,
						" . VTM_TABLE_PREFIX . "CHARACTER_GENERATION cg,
						" . VTM_TABLE_PREFIX . "CHARGEN_STATUS cgs
					where
						ch.CHARGEN_STATUS_ID = cgs.ID
						and ch.ID = cg.CHARACTER_ID
						AND cg.TEMPLATE_ID = %d;";
					$sql = $wpdb->prepare($sql, $id);
					$result = $wpdb->get_results($sql);
					//echo "SQL: $sql ($result)";
					
					if (count($result) == 0) {
						/* delete options */
						$sql = "delete from " . VTM_TABLE_PREFIX . "CHARGEN_TEMPLATE_OPTIONS where TEMPLATE_ID = %d;";
						$result = $wpdb->get_results($wpdb->prepare($sql, $id));
						/* delete defaults */
						$sql = "delete from " . VTM_TABLE_PREFIX . "CHARGEN_TEMPLATE_DEFAULTS where TEMPLATE_ID = %d;";
						$result = $wpdb->get_results($wpdb->prepare($sql, $id));
						/* delete maximums */
						$sql = "delete from " . VTM_TABLE_PREFIX . "CHARGEN_TEMPLATE_MAXIMUM where TEMPLATE_ID = %d;";
						$result = $wpdb->get_results($wpdb->prepare($sql, $id));
						/* delete template */
						$sql = "delete from " . VTM_TABLE_PREFIX . "CHARGEN_TEMPLATE where ID = %d;";
						$result = $wpdb->get_results($wpdb->prepare($sql, $id));
						echo "<p style='color:green'>Deleted template {$_REQUEST['template_name']}</p>";
					} else {
						echo "<p style='color:red'><b>Error: </b>Cannot delete as this template has been used in these characters:<ul>";
						foreach ($result as $character)
							echo "<li style='color:red'>" . stripslashes($character->NAME) . "</li>";
						echo "</ul></p>";
						
					}
					
					
					$id = 0;
				}
				
			}
			else {
				/* update */
				$id = $_REQUEST['template'];
				
				//print_r($_REQUEST);
				
				if (empty($_REQUEST["template_name"])) {
					echo "<p style='color:red'><b>Error: </b>Enter a template name before saving</p>";
					break;
				}
				
				$updates = 0;
				$fail    = 0;
				
				// update options
				
				$dataarray = array (
					'NAME'        => $_REQUEST["template_name"],
					'DESCRIPTION' => $_REQUEST["template_desc"],
					'VISIBLE' => $_REQUEST["template_visible"]
				);
				
				$result = $wpdb->update(VTM_TABLE_PREFIX . "CHARGEN_TEMPLATE",
					$dataarray,
					array (
						'ID' => $id
					)
				);
				
				$sql = "SELECT NAME, VALUE, ID FROM " . VTM_TABLE_PREFIX . "CHARGEN_TEMPLATE_OPTIONS WHERE TEMPLATE_ID = %s";
				$sql = $wpdb->prepare($sql, $id);
				$results = $wpdb->get_results($sql, OBJECT_K);
				
				// save template options
				foreach ($settings as $option => $val) {
					$data = array(
								'NAME' => $option,
								'VALUE' => isset($_REQUEST[$option]) ? $_REQUEST[$option] : $val,
								'TEMPLATE_ID' => $id
							);
					if (isset($results[$option])) {
						$result = $wpdb->update(VTM_TABLE_PREFIX . "CHARGEN_TEMPLATE_OPTIONS",
							$data,
							array ('ID' => $results[$option]->ID)
						);
						if (!$result && $result !== 0) {
							$wpdb->print_error();
							echo "<p style='color:red'>Could not update $option</p>";
						}
					} else {
						$wpdb->insert(VTM_TABLE_PREFIX . "CHARGEN_TEMPLATE_OPTIONS",
							$data,
							array('%s', '%s', '%d')
						);
						if ($wpdb->insert_id == 0) {
							echo "<p style='color:red'><b>Error:</b> $option could not be inserted</p>";
						}
					}
				}
				
				// save template defaults
				
				//delete defaults 
				$sql = "delete from " . VTM_TABLE_PREFIX . "CHARGEN_TEMPLATE_DEFAULTS where TEMPLATE_ID = %d;";
				$result = $wpdb->get_results($wpdb->prepare($sql, $id));

				// then re-add
				$tables    = $_REQUEST['table'];
				$items     = $_REQUEST['item'];
				$sectors   = $_REQUEST['item_sector'];
				$comments  = $_REQUEST['item_spec'];
				$levels    = $_REQUEST['item_level'];
				$multiples = isset($_REQUEST['multiple']) ? $_REQUEST['multiple'] : array();
				$delete    = $_REQUEST['item_delete'];
				//print_r($_REQUEST);
				for ($i = 0 ; $i < count($items) ; $i++) {
					//echo "<li>Add $i {$tables[$i]} {$comments[$i]}, {$items[$i]} at {$levels[$i]}?</li>";
					if ($levels[$i] > 0 && $items[$i] != 0 && (!isset($delete[$i]) || (isset($delete[$i]) && $delete[$i] != 'on'))) {
						
						if (!isset($multiples[$i])) {
							if ($tables[$i] != 'BACKGROUND') {
								$sql = "SELECT MULTIPLE FROM " . VTM_TABLE_PREFIX . $tables[$i] . " WHERE ID = %s";
								$multiples[$i] = $wpdb->get_var($wpdb->prepare($sql, $items[$i]));
							} else {
								$multiples[$i] = 'N';
							}
						}
						
						$data = array(
								'TEMPLATE_ID'  => $id,
								'CHARTABLE'    => 'CHARACTER_' . $tables[$i],
								'ITEMTABLE'    => $tables[$i],
								'ITEMTABLE_ID' => $items[$i],
								'SECTOR_ID'      => isset($sectors[$i]) ? $sectors[$i] : 0,
								'SPECIALISATION' => isset($comments[$i]) ? $comments[$i] : '',
								'LEVEL'          => $levels[$i],
								'MULTIPLE'       => $multiples[$i]
							);
					
						//print_r($data);
						$wpdb->insert(VTM_TABLE_PREFIX . "CHARGEN_TEMPLATE_DEFAULTS",
							$data,
							array('%d', '%s', '%s', '%d', '%d', '%s', '%d', '%s')
						);
					}
				}
				
				// save template maximums
				
				//delete maximums 
				$sql = "delete from " . VTM_TABLE_PREFIX . "CHARGEN_TEMPLATE_MAXIMUM where TEMPLATE_ID = %d;";
				$result = $wpdb->get_results($wpdb->prepare($sql, $id));

				// then re-add
				$tables    = $_REQUEST['max_table'];
				$items     = $_REQUEST['max_item'];
				$levels    = $_REQUEST['max_level'];
				$delete    = $_REQUEST['max_delete'];

				for ($i = 0 ; $i < count($items) ; $i++) {
					if ($levels[$i] > 0 && $items[$i] != 0) {
						//echo "<li>$i - level: {$levels[$i]}, item: {$items[$i]}, delete: {$delete[$i]}</li>";
						if (isset($delete[$i]) && $delete[$i] == 'on') {
							$doadd = 0;
						} else {
							$doadd = 1;
						}
						
						if ($doadd) {
							$data = array(
									'TEMPLATE_ID'  => $id,
									'ITEMTABLE'    => $tables[$i],
									'ITEMTABLE_ID' => $items[$i],
									'LEVEL'        => $levels[$i],
								);
						
							//print_r($data);
							$wpdb->insert(VTM_TABLE_PREFIX . "CHARGEN_TEMPLATE_MAXIMUM",
								$data,
								array('%d', '%s', '%d', '%d')
							);
						}
					}
				}
				
				// save primary path defaults
				
				//delete primary path defaults 
				$sql = "delete from " . VTM_TABLE_PREFIX . "CHARGEN_PRIMARY_PATH where TEMPLATE_ID = %d;";
				$result = $wpdb->get_results($wpdb->prepare($sql, $id));

				// then re-add
				$clans       = $_REQUEST['ppclanid'];
				$disciplines = $_REQUEST['ppdiscid'];
				$paths       = $_REQUEST['pppathid'];

				for ($i = 0 ; $i < count($clans) ; $i++) {
					$data = array(
							'TEMPLATE_ID' => $id,
							'PATH_ID'     => $paths[$i],
							'CLAN_ID'     => $clans[$i],
							'DISCIPLINE_ID' => $disciplines[$i],
						);
				
					//print_r($data);
					$wpdb->insert(VTM_TABLE_PREFIX . "CHARGEN_PRIMARY_PATH",
						$data,
						array('%d', '%d', '%d', '%d')
					);
				}
			}
			break;		
	}
	
	if ($id > 0) {
		
		$sql = "SELECT NAME, DESCRIPTION, VISIBLE FROM " . VTM_TABLE_PREFIX . "CHARGEN_TEMPLATE WHERE ID = %s";
		$sql = $wpdb->prepare($sql, $id);
		$result = $wpdb->get_row($sql);
		$name        = $result->NAME;
		$description = $result->DESCRIPTION;
		$visible     = $result->VISIBLE;
		
		$sql = "SELECT NAME, VALUE FROM " . VTM_TABLE_PREFIX . "CHARGEN_TEMPLATE_OPTIONS WHERE TEMPLATE_ID = %s";
		$sql = $wpdb->prepare($sql, $id);
		$results = $wpdb->get_results($sql, OBJECT_K);
				
		$settings['attributes-method']    = isset($results['attributes-method']->VALUE) ? $results['attributes-method']->VALUE : $settings['attributes-method'];
		$settings['attributes-primary']   = isset($results['attributes-primary']->VALUE) ? $results['attributes-primary']->VALUE : $settings['attributes-primary'];
		$settings['attributes-secondary'] = isset($results['attributes-secondary']->VALUE) ? $results['attributes-secondary']->VALUE : $settings['attributes-secondary'];
		$settings['attributes-tertiary']  = isset($results['attributes-tertiary']->VALUE) ? $results['attributes-tertiary']->VALUE : $settings['attributes-tertiary'];
		$settings['attributes-points']    = isset($results['attributes-points']->VALUE) ? $results['attributes-points']->VALUE : $settings['attributes-points'];
		$settings['abilities-primary']    = isset($results['abilities-primary']->VALUE) ? $results['abilities-primary']->VALUE : $settings['abilities-primary'];
		$settings['abilities-secondary']  = isset($results['abilities-secondary']->VALUE) ? $results['abilities-secondary']->VALUE : $settings['abilities-secondary'];
		$settings['abilities-tertiary']   = isset($results['abilities-tertiary']->VALUE) ? $results['abilities-tertiary']->VALUE : $settings['abilities-tertiary'];
		$settings['abilities-max']        = isset($results['abilities-max']->VALUE) ? $results['abilities-max']->VALUE : $settings['abilities-max'];
		$settings['disciplines-points']   = isset($results['disciplines-points']->VALUE) ? $results['disciplines-points']->VALUE : $settings['disciplines-points'];
		$settings['virtues-points']       = isset($results['virtues-points']->VALUE) ? $results['virtues-points']->VALUE : $settings['virtues-points'];
		$settings['backgrounds-points']   = isset($results['backgrounds-points']->VALUE) ? $results['backgrounds-points']->VALUE : $settings['backgrounds-points'];
		$settings['road-multiplier']      = isset($results['road-multiplier']->VALUE) ? $results['road-multiplier']->VALUE : $settings['road-multiplier'];
		$settings['merits-max']           = isset($results['merits-max']->VALUE) ? $results['merits-max']->VALUE : $settings['merits-max'];
		$settings['flaws-max']            = isset($results['flaws-max']->VALUE) ? $results['flaws-max']->VALUE : $settings['flaws-max'];
		$settings['freebies-points']      = isset($results['freebies-points']->VALUE) ? $results['freebies-points']->VALUE : $settings['freebies-points'];
		$settings['rituals-method']       = isset($results['rituals-method']->VALUE) ? $results['rituals-method']->VALUE : $settings['rituals-method'];
		$settings['rituals-points']       = isset($results['rituals-points']->VALUE) ? $results['rituals-points']->VALUE : $settings['rituals-points'];
		$settings['limit-road-method']    = isset($results['limit-road-method']->VALUE) ? $results['limit-road-method']->VALUE : $settings['limit-road-method'];
		$settings['limit-road-id']        = isset($results['limit-road-id']->VALUE) ? $results['limit-road-id']->VALUE : $settings['limit-road-id'];
		$settings['limit-sect-method']    = isset($results['limit-sect-method']->VALUE) ? $results['limit-sect-method']->VALUE : $settings['limit-sect-method'];
		$settings['limit-sect-id']        = isset($results['limit-sect-id']->VALUE) ? $results['limit-sect-id']->VALUE : $settings['limit-sect-id'];
		$settings['virtues-free-dots']    = isset($results['virtues-free-dots']->VALUE) ? $results['virtues-free-dots']->VALUE : $settings['virtues-free-dots'];
		$settings['limit-generation-low'] = isset($results['limit-generation-low']->VALUE) ? $results['limit-generation-low']->VALUE : $settings['limit-generation-low'];
		$settings['primarypath-select']   = isset($results['primarypath-select']->VALUE) ? $results['primarypath-select']->VALUE : $settings['primarypath-select'];
		$settings['primarypath-default']  = isset($results['primarypath-default']->VALUE) ? $results['primarypath-default']->VALUE : $settings['primarypath-default'];
		//$settings['primarypath-2arylvl']  = isset($results['primarypath-2arylvl']->VALUE) ? $results['primarypath-2arylvl']->VALUE : $settings['primarypath-2arylvl'];
			
	} else {
		$name   = "";
		$description = "";
		$visible = "Y";
	}
	
	?>
	<p>Select a template to edit or create a new template.</p>
	
	<?php vtm_render_select_template();
	
	
?>
	<h3>Add/Edit Character Generation Template</h3>

	<form id="new-<?php print $type; ?>" method="post" action='<?php print htmlentities($current_url); ?>'>

	<h4>Character Generation Template Information</h4>
	
	<input type="hidden" name="tab" value="<?php print $type; ?>" />
	<input type="hidden" name="template" value="<?php print $id; ?>" />
	<input type="hidden" name="action" value="save" />
	<div class="datatables_info">
	<p>Template Name:
	<input type="text"   name="template_name" value="<?php print vtm_formatOutput($name); ?>" size=30 /></p>
	<p>Description:
	<input type="text"   name="template_desc" value="<?php print vtm_formatOutput($description); ?>" size=70 /></p>
	<p>Visible:
		<select name="template_visible">
			<option value="N" <?php selected($visible, "N"); ?>>No</option>
			<option value="Y" <?php selected($visible, "Y"); ?>>Yes</option>
		</select>
	</p>
	</div>

	<h4>Character Generation Template Options</h4>
	<div class="datatables_detail">
	<table>
	<tr class="template_option_row">
		<td rowspan=1>Assigning Attributes</td>
		<td><input type="radio" name="attributes-method" value="PST" <?php checked( 'PST', $settings['attributes-method']); ?>>Primary/Secondary/Tertiary
			<table>
			<tr><th>Primary Dots</th>  <td><input type="text" name="attributes-primary"   value="<?php print $settings['attributes-primary']; ?>"></td></tr>
			<tr><th>Secondary Dots</th><td><input type="text" name="attributes-secondary" value="<?php print $settings['attributes-secondary']; ?>"></td></tr>
			<tr><th>Tertiary Dots</th> <td><input type="text" name="attributes-tertiary"  value="<?php print $settings['attributes-tertiary']; ?>"></td></tr>
			</table>
		</td>
		<td><input type="radio" name="attributes-method" value="point" <?php checked( 'point', $settings['attributes-method']); ?>>Point Spend
			<table>
			<tr><th>Dots</th><td><input type="text" name="attributes-points"  value="<?php print $settings['attributes-points']; ?>"></td></tr>
			</table>
		</td>
	</tr>
	<tr class="template_option_row">
		<td rowspan=1>Assigning Abilities</td>
		<td colspan=2>
			<table>
			<tr>
				<th>Maximum in any one Ability at Abilities Step</th>
				<td><input type="text" name="abilities-max"   value="<?php print $settings['abilities-max']; ?>"></td>
			</tr>
			<tr><th>Primary Dots</th>  <td><input type="text" name="abilities-primary"   value="<?php print $settings['abilities-primary']; ?>"></td></tr>
			<tr><th>Secondary Dots</th><td><input type="text" name="abilities-secondary" value="<?php print $settings['abilities-secondary']; ?>"></td></tr>
			<tr><th>Tertiary Dots</th> <td><input type="text" name="abilities-tertiary"  value="<?php print $settings['abilities-tertiary']; ?>"></td></tr>
			</table>
		</td>
	</tr>
	<tr class="template_option_row">
		<td rowspan=1>Assigning Disciplines</td>
		<td colspan=2>
			<table>
			<tr><th>Number of Discipline Dots</th> <td><input type="text" name="disciplines-points"  value="<?php print $settings['disciplines-points']; ?>"></td></tr>
			</table>
		</td>
	</tr>
	<tr class="template_option_row">
		<td rowspan=1>Primary Paths of Magik</td>
		<td colspan=2>
			<table>
			<tr><th>Can non-default Paths be selected?</th> <td>
				<input type="radio" name="primarypath-select" value="1" <?php checked( '1', $settings['primarypath-select']); ?>>Yes, or 
				<input type="radio" name="primarypath-select" value="0" <?php checked( '0', $settings['primarypath-select']); ?>>No</td></tr>
			<tr><th>Primary path default based on</th>
				<td>
				<input type="radio" name="primarypath-default" value="clan" <?php checked( 'clan', $settings['primarypath-default']); ?>>Clan, or 
				<input type="radio" name="primarypath-default" value="discipline" <?php checked( 'discipline', $settings['primarypath-default']); ?>>Discipline</td></tr>
				</td></tr>
			</table>
		</td>
	</tr>
	<tr class="template_option_row">
		<td rowspan=1>Assigning Backgrounds</td>
		<td colspan=2>
			<table>
			<tr><th>Number of Background Dots</th> <td><input type="text" name="backgrounds-points"  value="<?php print $settings['backgrounds-points']; ?>"></td></tr>
			</table>
		</td>
	</tr>
	<tr class="template_option_row">
		<td rowspan=1>Assigning Virtues</td>
		<td colspan=2>
			<table>
			<tr><th>Number of Virtue Dots to spend</th> <td><input type="text" name="virtues-points"  value="<?php print $settings['virtues-points']; ?>"></td></tr>
			<tr>
				<th>Free Virtue Dots</th>
				<td>
					<select name='virtues-free-dots'>
						<option value="yes" <?php selected($settings['virtues-free-dots'], "yes"); ?>>Each virtue gets an initial free dot</option>
						<option value="no" <?php selected($settings['virtues-free-dots'], "no"); ?>>Virtues do not get an initial free dot</option>
						<option value="humanityonly" <?php selected($settings['virtues-free-dots'], "humanityonly"); ?>>Only the path of Humanity get a free dot</option>
						<option value="humanityvirtues" <?php selected($settings['virtues-free-dots'], "humanityvirtues"); ?>>Virtues from the path of Humanity get a free dot</option>
					</select>
				</td>
			</tr>
			</table>
		</td>
	</tr>
	<tr class="template_option_row">
		<td rowspan=1>Paths of Enlightenment</td>
		<td colspan=2>
			<table>
			<tr><th>Rating is Conscience + Self-Control multiplied by</th><td><input type="text" name="road-multiplier"  value="<?php print $settings['road-multiplier']; ?>" size=5 ></td></tr>
			<tr>
				<th>Limiting Paths</th>
				<td>
					<select name="limit-road-method">
						<option value="none" <?php selected($settings['limit-road-method'], "none"); ?>>No Limit</option>
						<option value="only" <?php selected($settings['limit-road-method'], "only"); ?>>Limit to a specific path</option>
						<option value="exclude" <?php selected($settings['limit-road-method'], "exclude"); ?>>Exclude a specific path</option>
					</select>
				</td>
			</tr>
			<tr>
				<th>Select Path for limit</th>
				<td>
					<select name="limit-road-id">
					<?php 
						foreach ($roads as $road) {
							print "<option value='{$road->ID}' " . selected($settings['limit-road-id'],$road->ID, false) . ">" . vtm_formatOutput($road->name) . "</option>\n";
						}
					?>
					</select>
				</td>
			</tr>
			</table>
		</td>
	</tr>
	<tr class="template_option_row">
		<td rowspan=1>Merits and Flaws</td>
		<td colspan=2>
			<table>
			<tr><th>Maximum points spent in Merits (0 for no limit)</th> <td><input type="text" name="merits-max"  value="<?php print $settings['merits-max']; ?>" size=5 ></td></tr>
			<tr><th>Maximum points spent in Flaws (0 for no limit)</th> <td><input type="text" name="flaws-max"  value="<?php print $settings['flaws-max']; ?>" size=5 ></td></tr>
			</table>
		</td>
	</tr>
	<tr class="template_option_row">
		<td rowspan=1>Freebie Points</td>
		<td colspan=2>
			<table>
			<tr><th>Number of Freebie Points</th> <td><input type="text" name="freebies-points"  value="<?php print $settings['freebies-points']; ?>" size=5 ></td></tr>
			</table>
		</td>
	</tr>
	<tr class="template_option_row">
		<td rowspan=1>Assigning Rituals</td>
		<td colspan=2>
			<table>
			<tr><td><input type="radio" name="rituals-method" value="none" <?php checked( 'none', $settings['rituals-method']); ?>>Don't assign rituals during character generation</td></tr>
			<tr>
				<td>
					<input type="radio" name="rituals-method" value="point" <?php checked( 'point', $settings['rituals-method']); ?>>Get a set number of points to spend 
					<input type="text" name="rituals-points" value="<?php print $settings['rituals-points']; ?>">
				</td>
			</tr>
			<tr><td><input type="radio" name="rituals-method" value="discipline" <?php checked( 'discipline', $settings['rituals-method']); ?>>Points equal Thaumaturgy level (Thaum 5 gives 5 levels of disciplines)</td></tr>
			<tr><td><input type="radio" name="rituals-method" value="accumulate" <?php checked( 'accumulate', $settings['rituals-method']); ?>>Points equal to accumulated Thaum level (Thaum 5 gives 1+2+3+4+5=15 levels)</td></tr>
			</table>
		</td>
	</tr>
	<tr class="template_option_row">
		<td rowspan=1>Controlling Affiliations</td>
		<td colspan=2>
			<table>
			<tr>
				<th>Limiting Affiliations</th>
				<td>
					<select name="limit-sect-method">
						<option value="none" <?php selected($settings['limit-sect-method'], "none"); ?>>No Limit</option>
						<option value="only" <?php selected($settings['limit-sect-method'], "only"); ?>>Limit to a specific Affiliation</option>
						<option value="exclude" <?php selected($settings['limit-sect-method'], "exclude"); ?>>Exclude a specific Affiliation</option>
					</select>
				</td>
			</tr>
			<tr>
				<th>Select Affiliation for limit</th>
				<td>
					<select name="limit-sect-id">
					<?php 
						foreach ($sects as $sect) {
							print "<option value='{$sect->ID}' " . selected($settings['limit-sect-id'],$sect->ID, false) . ">" . vtm_formatOutput($sect->NAME) . "</option>\n";
						}
					?>
					</select>
				</td>
			</tr>
			</table>
		</td>
	</tr>
	<tr class="template_option_row">
		<td rowspan=1>Generation Settings</td>
		<td colspan=2>
			<table>
			<tr>
				<th>Lowest Generation available:</th>
				<td>
					<select name="limit-generation-low">
					<?php
						
						foreach (vtm_get_generations() as $gen) {
							print "<option value='{$gen->ID}' " . selected($settings['limit-generation-low'],$gen->ID, false) . ">{$gen->NAME}th</option>";
						}
					?>
					</select>
				</td>
			</tr>
			</table>
		</td>
	</tr>
	</table>
	</div>

	<h4>Primary Magik Path Defaults</h4>
	
	<?php 
	$sql = "SELECT cpp.PATH_ID, cpp.CLAN_ID, disc.ID as DISCIPLINE_ID
		FROM 
			" . VTM_TABLE_PREFIX . "CHARGEN_PRIMARY_PATH cpp,
			" . VTM_TABLE_PREFIX . "DISCIPLINE disc,
			" . VTM_TABLE_PREFIX . "PATH path
		WHERE 
			cpp.TEMPLATE_ID = '%s'
			AND disc.ID = path.DISCIPLINE_ID
			AND path.ID = cpp.PATH_ID";
	$sql = $wpdb->prepare($sql, $id);
	$results = $wpdb->get_results($sql);
	
	$disciplines = vtm_get_magic_disciplines();
	$paths = vtm_listPaths("Y");
	
	if ($settings['primarypath-default'] == 'clan') { 
		$clans = vtm_get_clans();
		//print_r($results);
		
		foreach ($results as $entry) {
			if ($entry->CLAN_ID == 0) {
				foreach ($clans as $clan) {
					$primarypaths[$clan->ID][$entry->DISCIPLINE_ID] = $entry->PATH_ID;
				}
			} else {
				$primarypaths[$entry->CLAN_ID][$entry->DISCIPLINE_ID] = $entry->PATH_ID;
			}
		}
		//print_r($primarypaths);
		
	?>
	<p>Select which path is the default primary path for each clan</p>
	<div class="primarypath_defaults">
	<table>
	<tr class="template_default_row"><th>Clan</th><th>Discipline</th><th>Default Path</th></tr>
	<?php
		$offset = 0;
		foreach ($clans as $clan) {
			$selected = isset($primarypaths[$clan->ID][$discipline->ID]) ? $primarypaths[$clan->ID][$discipline->ID] : 0;
			foreach ($disciplines as $discipline) {
				print "<tr><td>
					<input type='hidden' name='ppclanid[$offset]' value='$clan->ID'>
					<input type='hidden' name='ppdiscid[$offset]' value='$discipline->ID'>
					{$clan->NAME}</td><td>$discipline->NAME</td><td>";
				print "<select name='pppathid[$offset]' >";
				foreach ($paths as $path) {
					if ($path->disname == $discipline->NAME) {
						print "<option value='" . $path->id . "' " . selected($selected,$path->id, false) . ">" . vtm_formatOutput($path->name) . "</option>";
					}
				}
				print "</select>";
				print "</td></tr>";
				
				$offset++;
			}
			
		}
	?>
	</table>
	</div>
	<?php } else { 
		foreach ($results as $entry) {
			$primarypaths[$entry->DISCIPLINE_ID] = $entry->PATH_ID;
		}
	
	?>
	<p>Select which path is the default primary path for each discipline</p>
	<div class="primarypath_defaults">
	<table>
	<tr class="template_default_row"><th>Discipline</th><th>Default Path</th></tr>
	<?php
		$offset = 0;
		foreach ($disciplines as $discipline) {
			$selected = isset($primarypaths[$discipline->ID]) ? $primarypaths[$discipline->ID] : 0;
			print "<tr><td>
				<input type='hidden' name='ppclanid[$offset]' value='0'>
				<input type='hidden' name='ppdiscid[$offset]' value='$discipline->ID'>
				$discipline->NAME</td><td>";
			print "<select name='pppathid[$offset]'>";
			foreach ($paths as $path) {
				if ($path->disname == $discipline->NAME) {
					print "<option value='" . $path->id . "' " . selected($selected,$path->id, false) . ">" . vtm_formatOutput($path->name) . "</option>";
				}
			}
			print "</select>";
			print "</td></tr>";
			
			$offset++;
		}
	?>		
	
	</table>
	</div>
	
	<?php } ?>
	
	<h4>Character Generation Template Defaults</h4>
	<p>Select any items which will be automatically added on to the character.</p>
	<div class="datatables_defaults">
	
	<h5>Backgrounds</h5>
	<table>
	<tr class="template_default_row">
		<th>Background</th><th>Sector</th><th>Comment</th><th>Level</th><th>Delete</th>
	</tr>
	<?php 
		$backgrounds = vtm_get_backgrounds();
		$sectors = vtm_get_sectors(true);
				
		if ($id > 0) {
			$sql = "SELECT bg.NAME, bg.ID, ctd.SPECIALISATION, ctd.LEVEL,
						IFNULL(sector.ID,0) as SECTOR_ID, 
						IFNULL(sector.NAME,'') as SECTOR, 'N' as MULTIPLE
					FROM 
						" . VTM_TABLE_PREFIX . "CHARGEN_TEMPLATE_DEFAULTS ctd
						LEFT JOIN (
							SELECT ID, NAME
							FROM " . VTM_TABLE_PREFIX . "SECTOR
						) sector
						ON sector.ID = ctd.SECTOR_ID,
						" . VTM_TABLE_PREFIX . "BACKGROUND bg
					WHERE 
						ctd.TEMPLATE_ID = %s 
						AND ctd.ITEMTABLE_ID = bg.ID
						AND ctd.ITEMTABLE = 'BACKGROUND'";
			$sql = $wpdb->prepare($sql, $id);
			$bought = $wpdb->get_results($sql);
		} else {
			$bought = array();
		}
		
		$offset = 0;
		foreach ($bought as $item) {

			print "<tr class='template_default_row'>\n";
			print "<td>
				<input type='hidden' name='table[$offset]' value='BACKGROUND'>
				<input type='hidden' name='item[$offset]' value='{$item->ID}'>
				<input type='hidden' name='multiple[$offset]' value='{$item->MULTIPLE}'>
				" . vtm_formatOutput($item->NAME) . "</td>\n";
			print "<td>
				<input type='hidden' name='item_sector[$offset]' value='{$item->SECTOR_ID}'>
				" . vtm_formatOutput($item->SECTOR) . "</td>\n";
			print "<td><input type='hidden' name='item_spec[$offset]' value='" . vtm_formatOutput($item->SPECIALISATION) . "'>
				" . vtm_formatOutput($item->SPECIALISATION) . "</td>\n";
			print "<td>
				<input type='hidden' name='item_level[$offset]' value='{$item->LEVEL}'>
				{$item->LEVEL}</td>\n";
			print "<td><input type='checkbox' name='item_delete[$offset]'></td>\n";
			print "</tr>\n";
			
			$offset++;
		}
		
		for ($i = $offset ; $i < ($offset + 4) ; $i++) {
			print "<tr class='template_default_row'>\n";
			print "<td>
				<input type='hidden' name='table[$i]' value='BACKGROUND'>
				<select name='item[$i]'>\n";
			print "<option value='0'>[Select]</option>\n";
			foreach ($backgrounds as $item) {
				print "<option value='{$item->ID}'>" . vtm_formatOutput($item->NAME) . "</option>\n";
			}
			print "</select></td>\n";
			print "<td><select name='item_sector[$i]'>\n";
			print "<option value='0'>[None]</option>\n";
			foreach ($sectors as $item) {
				print "<option value='{$item->ID}'>" . vtm_formatOutput($item->NAME) . "</option>\n";
			}
			print "</select></td>\n";
			print "<td><input type='text' name='item_spec[$i]' value=''></td>\n";
			print "<td><select name='item_level[$i]'>\n";
			for ($j = 0 ; $j <= 5 ; $j++) {
				print "<option value='$j'>$j</option>\n";
			}
			print "</select></td>\n";
			print "<td><input type='hidden' name='item_delete[$i]' value='off'></td>\n";
			print "</tr>\n";
		}
		$offset = $i;
	?>
	</table>
	
	<h5>Abilities</h5>
	<table>
	<tr class="template_default_row">
		<th>Ability</th><th>Speciality</th><th>Level</th><th>Delete</th>
	</tr>
	<?php 
		$skills = vtm_listSkills("", "Y");
		
		if ($id > 0) {
			$sql = "SELECT skill.NAME, skill.ID, ctd.SPECIALISATION, ctd.LEVEL, skill.MULTIPLE
					FROM 
						" . VTM_TABLE_PREFIX . "CHARGEN_TEMPLATE_DEFAULTS ctd,
						" . VTM_TABLE_PREFIX . "SKILL skill
					WHERE 
						ctd.TEMPLATE_ID = %s 
						AND ctd.ITEMTABLE_ID = skill.ID
						AND ctd.ITEMTABLE = 'SKILL'";
			$sql = $wpdb->prepare($sql, $id);
			$bought = $wpdb->get_results($sql);
		} else {
			$bought = array();
		}
		
		foreach ($bought as $item) {

			print "<tr class='template_default_row'>\n";
			print "<td>
				<input type='hidden' name='table[$offset]' value='SKILL'>
				<input type='hidden' name='item[$offset]' value='{$item->ID}'>
				<input type='hidden' name='multiple[$offset]' value='{$item->MULTIPLE}'>
				" . vtm_formatOutput($item->NAME) . "</td>\n";
			print "<td><input type='hidden' name='item_spec[$offset]' value='" . vtm_formatOutput($item->SPECIALISATION) . "'>
				" . vtm_formatOutput($item->SPECIALISATION) . "</td>\n";
			print "<td>
				<input type='hidden' name='item_level[$offset]' value='{$item->LEVEL}'>
				{$item->LEVEL}</td>\n";
			print "<td><input type='checkbox' name='item_delete[$offset]'></td>\n";
			print "</tr>\n";
			
			$offset++;
		}
		
		for ($i = $offset ; $i < ($offset + 4) ; $i++) {
			print "<tr class='template_default_row'>\n";
			print "<td>
				<input type='hidden' name='table[$i]' value='SKILL'>
				<select name='item[$i]'>\n";
			print "<option value='0'>[Select]</option>\n";
			foreach ($skills as $item) {
				print "<option value='{$item->id}'>" . vtm_formatOutput($item->name) . "</option>\n";
			}
			print "</select></td>\n";
			print "<td><input type='text' name='item_spec[$i]' value=''></td>\n";
			print "<td><select name='item_level[$i]'>\n";
			for ($j = 0 ; $j <= 5 ; $j++) {
				print "<option value='$j'>$j</option>\n";
			}
			print "</select></td>\n";
			print "<td><input type='hidden' name='item_delete[$i]' value='off'>
				<input type='hidden' name='item_sector[$i]' value='0'></td>\n";
			print "</tr>\n";
		}
		$offset = $i;
	?>
	</table>
	</div>
	
	<h4>Character Generation Template Maximums</h4>
	<p>Configure the maximum level allowed at character generation.</p>
	<div class="datatables_defaults">
	
	<h5>Backgrounds</h5>
	<table>
	<tr class="template_default_row">
		<th>Background</th><th>Maximum</th><th>Delete</th>
	</tr>
	<?php 
		$backgrounds = vtm_get_backgrounds();
				
		if ($id > 0) {
			$sql = "SELECT bg.ID, bg.NAME, ctm.LEVEL
					FROM 
						" . VTM_TABLE_PREFIX . "CHARGEN_TEMPLATE_MAXIMUM ctm,
						" . VTM_TABLE_PREFIX . "BACKGROUND bg
					WHERE 
						ctm.TEMPLATE_ID = %s 
						AND ctm.ITEMTABLE_ID = bg.ID
						AND ctm.ITEMTABLE = 'BACKGROUND'";
			$sql = $wpdb->prepare($sql, $id);
			$results = $wpdb->get_results($sql);
		} else {
			$results = array();
		}
		
		$offset = 0;
		if (count($results) > 0) {
			foreach ($results as $item) {

				print "<tr class='template_default_row'>\n";
				print "<td>
					<input type='hidden' name='max_table[$offset]' value='BACKGROUND'>
					<input type='hidden' name='max_item[$offset]' value='{$item->ID}'>
					" . vtm_formatOutput($item->NAME) . "</td>\n";
				print "<td><select name='max_level[$offset]'>\n";
				for ($j = 1 ; $j <= 5 ; $j++) {
					print "<option value='$j' " . selected($j,$item->LEVEL, false) . ">$j</option>\n";
				}
				print "</select></td>\n";
				print "<td><input type='checkbox' name='max_delete[$offset]' value='on'></td>\n";
				print "</tr>\n";
				
				$offset++;
			}
		}
		
		// blank rows
		for ($i = $offset ; $i < ($offset + 4) ; $i++) {
			print "<tr class='template_default_row'>\n";
			print "<td>
				<input type='hidden' name='max_table[$i]' value='BACKGROUND'>
				<select name='max_item[$i]'>\n";
			print "<option value='0'>[Select]</option>\n";
			foreach ($backgrounds as $item) {
				print "<option value='{$item->ID}'>" . vtm_formatOutput($item->NAME) . "</option>\n";
			}
			print "</select></td>\n";
			print "<td><select name='max_level[$i]'>\n";
			for ($j = 0 ; $j <= 5 ; $j++) {
				print "<option value='$j'>$j</option>\n";
			}
			print "</select></td>\n";
			print "<td><input type='hidden' name='max_delete[$i]' value='off'></td>\n";
			print "</tr>\n";
		}
		$offset = $i;
	?>
	</table>
	
	</div>	
	
	<br />	
	<input type="submit" name="do_save_<?php print $type; ?>" class="button-primary" value="Save" />
	<input type="submit" name="do_new_<?php print $type; ?>" class="button-primary" value="New" />
	<input type="submit" name="do_delete_<?php print $type; ?>" class="button-primary" value="Delete" />
	</form>


<?php
}


function vtm_render_select_template() {

	$selected = isset($_REQUEST['template']) ? $_REQUEST['template'] : '';

	echo "<h3>Select Template</h3>";
	echo "<form id='select_template_form' method='post'>\n";
	echo "<input type='hidden' name='tab'   value='template' />\n";
	echo "<input type='hidden' name='action' value='loadtemplate' />\n";
	echo "<select name='template'>\n";
	echo "<option value='0'>[Select/New]</option>\n";
	
	foreach (vtm_get_templates() as $template) {
		echo "<option value='{$template->ID}' ";
		selected($selected,$template->ID);
		echo ">" . vtm_formatOutput($template->NAME) . "</option>\n";
	}
	
	echo "</select>\n";
	echo "<input type='submit' name='submit_model' class='button-primary' value='Go' />\n";
	echo "</form>\n";
	

}

?>