<?php

function vtm_xp_spend_content_filter($content) {

  if (is_page(vtm_get_stlink_page('viewXPSpend')) && is_user_logged_in()) {
    $content .= vtm_print_xp_spend_table();
  }
  // otherwise returns the database content
  return $content;
}
add_filter( 'the_content', 'vtm_xp_spend_content_filter' );


function vtm_doPendingXPSpend($character) {
	global $wpdb;
	$characterID = vtm_establishCharacterID($character);
	$playerID    = vtm_establishPlayerID($character);
		
	$count = 0;
	$requestspends = array();
	foreach ($_REQUEST as $spend => $details) {
		$data = explode(":",$spend);
		if (count($data) > 1) {
			
			$data        = explode(":",$spend);
			$count++;
			vtm_save_to_pending($data, $details, $playerID, $characterID
			);
		}
	}	
	
	
	
	if ($count > 0) {
		$email = get_option( 'vtm_replyto_address', get_option( 'vtm_chargen_email_from_address', get_bloginfo('admin_email') ) );
		$body = "<p>A user has submitted experience spends.</p><p>View the spends here: " .
			admin_url('admin.php?page=vtmcharacter-xp') . "</p>";
		vtm_send_email($email, "Experience spends have been submitted", $body);
	}
}


function vtm_print_xp_spend_table() {
	global $vtmglobal;
	
	$output = "";
		
	//echo "<!--";
	//print_r($_POST);
	//echo "-->";
	
	$character   = vtm_establishCharacter('');
	$characterID = vtm_establishCharacterID($character);
	$playerID    = vtm_establishPlayerID($character);
	
	$outputError = "";
	$step = isset($_REQUEST['step']) ? $_REQUEST['step'] : '';
	
		
	// Cancel Spends

	$docancel = isset($_REQUEST['cancel']);
	if ($docancel) {
		vtm_cancel_pending($_REQUEST['cancel']);
	}
	

	// Back button
	if (isset($_REQUEST['xCancel']) || $docancel) $step = "";
		
	//VALIDATE SPENDS
	switch ($step) {
		case 'supply_details':
			$outputError .= vtm_validate_spends($playerID, $characterID, $docancel);
			if (!empty($outputError)) {
				$output .= "<div class='vtm_error'>$outputError</div>";
				$step = "";
			}
			break;
		case 'submit_spend':
			$outputError .= vtm_validate_details($characterID);
			if (!empty($outputError)) {
				$output .= "<div class='vtm_error'>$outputError</div>";
				$step = "supply_details";
			}
			break;
	}
	// DISPLAY REQUIRED PAGE
	switch ($step) {
		case 'supply_details':
			$output .= vtm_render_supply_details($character);
			break;
		case 'submit_spend':
			vtm_doPendingXPSpend($character);
		default:
			$output .= vtm_render_select_spends($character);
			break;
	
	}
	
	$content = "<div class='gvplugin vtmpage_" . $vtmglobal['config']->WEB_PAGEWIDTH . "' >";
	$content .= vtm_report_max_input_vars($output);
	$content .= "</div>";
	return $content;
}

function vtm_render_supply_details($character) {

	$output = "";
	$character   = vtm_establishCharacter($character);
	$characterID = vtm_establishCharacterID($character);

	$spent = 0;
	foreach ($_REQUEST as $spend => $details) {
		$data = explode(":",$spend);
		if (count($data) > 1) {
			$spent += $data[5];
		}
	}

	$output .= "<p>Spending $spent experience points.</p>\n";
	$output .= "<p>Please enter specialisations, if available, and enter a description of your learning method</p>";
	$output .= "<div class='gvplugin' id='vtmid_xpst'>\n";
	$output .= "<form name='SPEND_XP_FORM' method='post' action='" . $_SERVER['REQUEST_URI'] . "'>\n";
	
	$output .= vtm_render_details_section($characterID);

	$output .= "<input class='vtmxp_submit' type='submit' name='xSubmit' value='Spend XP'>\n";
	$output .= "<input class='vtmxp_submit' type='submit' name='xCancel' value='Back'>\n";

	if (isset($_POST['VTM_CHARACTER']) && $_POST['VTM_CHARACTER'] != "")
		$output .= "<input type='HIDDEN' name='VTM_CHARACTER' value='" . vtm_formatOutput($_POST['VTM_CHARACTER']) . "' />\n";
	$output .= "<input type='HIDDEN' name='character' value='" . vtm_formatOutput($character) . "'>\n";
	$output .= "<input type='HIDDEN' name='step' value='submit_spend'>\n";
	$output .= "<input type='HIDDEN' name='VTM_FORM' value='applyXPSpend' />\n";
	$output .= "</form></div>\n";

	return $output;
}

function vtm_render_select_spends($character) {
	global $vtmglobal;

	$character   = vtm_establishCharacter($character);
	$characterID = vtm_establishCharacterID($character);
	$playerID    = vtm_establishPlayerID($character);
	
	$xp_total      = vtm_get_total_xp($playerID, $characterID);
	$xp_pending    = vtm_get_pending_xp($playerID, $characterID);
	$xp_avail      = $xp_total - $xp_pending;
	$fulldoturl    = VTM_PLUGIN_URL . '/images/dot1full.' . VTM_ICON_FORMAT;
	$emptydoturl   = VTM_PLUGIN_URL . '/images/dot1empty.' . VTM_ICON_FORMAT;
	$pendingdoturl = VTM_PLUGIN_URL . '/images/dot2.' . VTM_ICON_FORMAT;
	
	$sectioncontent = array();
	$sectionheading = array();
	$sectiontitle   = array(
						'stat'  => "Attributes",
						'skill' => "Abilities",
						'disc'  => "Disciplines",
						'combo' => "Combo Disciplines",
						'path'  => "Paths",
						'ritual' => "Rituals",
						'merit'  => "Merits and Flaws"
					);
	$sectioncols    = array();
	$sectionorder   = array('stat', 'skill', 'disc', 'combo', 'path',
							'ritual', 'merit');
	$output = "<p>You have $xp_total experience in total, $xp_pending points currently pending and " . ($xp_total - $xp_pending) . " available to spend</p>";

	// work out the maximum ratings for this character based on generation
	$ratings = vtm_get_character_maximums($characterID);
	$maxRating = $ratings[0];
	$maxDiscipline = $ratings[1];
	
	// get the current pending spends for this character
	$pendingSpends = vtm_get_pending($characterID);
	
	$sectioncontent['stat']   = vtm_render_spend_table('stat', 'vtm_get_sql_stats', $characterID, $maxRating, $xp_avail);
	$sectioncontent['skill']  = vtm_render_spend_table('skill', 'vtm_get_sql_skills', $characterID, $maxRating, $xp_avail);
	$sectioncontent['disc']   = vtm_render_spend_table('disc', 'vtm_get_sql_disc', $characterID, $maxDiscipline, $xp_avail);
	$sectioncontent['combo']  = vtm_render_spend_table('combo', 'vtm_get_sql_combo', $characterID, $maxRating, $xp_avail);
	$sectioncontent['path']   = vtm_render_spend_table('path', 'vtm_get_sql_path', $characterID, $maxRating, $xp_avail);
	$sectioncontent['ritual'] = vtm_render_spend_table('ritual', 'vtm_get_sql_ritual', $characterID, 5, $xp_avail); //vtm_render_rituals($characterID, 5, $pendingSpends, $xp_avail);
	$sectioncontent['merit']  = vtm_render_spend_table('merit', 'vtm_get_sql_merit', $characterID, 100, $xp_avail); //vtm_render_merits($characterID, $pendingSpends, $xp_avail);
	
	
	// DISPLAY TABLES 
	//-------------------------------
	$output .= "<div class='gvplugin' id='vtmid_xpst'>\n";
	$output .= "<form name='SPEND_XP_FORM' method='post' action='" . $_SERVER['REQUEST_URI'] . "'>\n";
	$output .= "<p>Hover over items with <span class='vtmxp_spec'>this formatting</span> to show more information on the item.</p>\n";


	$jumpto = array();
	$i = 0;
	foreach ($sectionorder as $section) {
		if ($sectiontitle[$section] && $sectioncontent[$section]) {
			$jumpto[$i++] = "<a href='#gvid_xpst_$section' class='gvxp_jump'>" . vtm_formatOutput($sectiontitle[$section]) . "</a>";
		}
	}
	$outputJump = "<p>Jump to section: " . implode(" | ", $jumpto) . "</p>";
	
	foreach ($sectionorder as $section) {
	
		if ($sectioncontent[$section]) {
			$output .= "<h4 class='gvxp_head' id='gvid_xpst_$section'>" . vtm_formatOutput($sectiontitle[$section]) . "</h4>\n";
			$output .= "$outputJump\n";
			//$output .= $sectionheading[$section];
			$output .= $sectioncontent[$section];
			$output .= "<input class='vtmxp_submit' type='submit' name='xSubmit' value='Next >'>\n";
		} 
		
	}


	if (isset($_POST['VTM_CHARACTER']) && $_POST['VTM_CHARACTER'] != "") {
		$output .= "<input type='HIDDEN' name='VTM_CHARACTER' value='" . vtm_formatOutput($_POST['VTM_CHARACTER']) . "' />\n";
	}

	if (isset($_POST['VTM_CHARACTER']) && $_POST['VTM_CHARACTER'] != "")
		$output .= "<input type='HIDDEN' name='VTM_CHARACTER' value='" . vtm_formatOutput($_POST['VTM_CHARACTER']) . "' />\n";
	$output .= "<input type='HIDDEN' name='character' value='" . vtm_formatOutput($character) . "'>\n";
	$output .= "<input type='HIDDEN' name='step' value='supply_details'>\n";
	$output .= "<input type='HIDDEN' name='VTM_FORM' value='applyXPSpend' />\n";
	$output .= "</form></div>\n";

	return $output;
	
}

function vtm_get_xp_cost($dbdata, $current, $new) {

	$cost = 0;
	
	$selected = $current;
	$row = 0;
	while ($selected < $new && $row < count($dbdata)) {
		if ($selected == $dbdata[$row]->CURRENT_VALUE) {
			if ($dbdata[$row]->CURRENT_VALUE == $dbdata[$row]->NEXT_VALUE ||
				$dbdata[$row]->XP_COST == 0 ||
				$dbdata[$row]->NEXT_VALUE > $new) {
				$cost = 0;
				break;
			} else {
				$cost += $dbdata[$row]->XP_COST;
				$selected = $dbdata[$row]->NEXT_VALUE;
			}
		}
		$row++;
	}
	
	return $cost;
}
function vtm_get_pending($characterID) {
	global $wpdb;

	$sql = "SELECT * FROM " . VTM_TABLE_PREFIX . "PENDING_XP_SPEND WHERE CHARACTER_ID = %s";
	$sql = $wpdb->prepare($sql, $characterID);
	
	$result = $wpdb->get_results($sql);

	return $result;
}
function vtm_get_xp_costs_per_level($table, $tableid, $level) {
	global $wpdb;

	$sql = "SELECT steps.CURRENT_VALUE, steps.NEXT_VALUE, steps.XP_COST
		FROM
			" . VTM_TABLE_PREFIX . "COST_MODEL_STEP steps,
			" . VTM_TABLE_PREFIX . "COST_MODEL models,
			" . VTM_TABLE_PREFIX . $table . " mytable
		WHERE
			steps.COST_MODEL_ID = models.ID
			AND mytable.COST_MODEL_ID = models.ID
			AND mytable.ID = %s
			AND steps.NEXT_VALUE > %s
		ORDER BY steps.CURRENT_VALUE ASC";

	$sql = $wpdb->prepare($sql, $tableid, $level);
	
	return $wpdb->get_results($sql);

}
function vtm_get_discipline_xp_costs_per_level($disciplineid, $level, $clanid) {
	global $wpdb;

	// clan cost model 
	$clansql = "SELECT steps.CURRENT_VALUE, steps.NEXT_VALUE, steps.XP_COST
				FROM 
					" . VTM_TABLE_PREFIX . "COST_MODEL_STEP steps,
					" . VTM_TABLE_PREFIX . "COST_MODEL cmodels,
					" . VTM_TABLE_PREFIX . "CLAN cclans,
					" . VTM_TABLE_PREFIX . "CLAN_DISCIPLINE cclandisciplines,
					" . VTM_TABLE_PREFIX . "DISCIPLINE cdisciplines
				WHERE
					cclans.CLAN_COST_MODEL_ID = cmodels.ID
					AND steps.COST_MODEL_ID = cmodels.ID
					AND cclans.ID = %s	
					AND cclans.ID = cclandisciplines.CLAN_ID
					AND cdisciplines.ID = cclandisciplines.DISCIPLINE_ID
					AND cdisciplines.ID = %s
					AND steps.NEXT_VALUE > %s";
	$clansql = $wpdb->prepare($clansql, $clanid, $disciplineid, $level);
	$result = $wpdb->get_results($clansql);

	
	// non-clan cost model
	if (!$result) {
		$nonsql = "SELECT steps.CURRENT_VALUE, steps.NEXT_VALUE, steps.XP_COST
					FROM 
						" . VTM_TABLE_PREFIX . "COST_MODEL_STEP steps,
						" . VTM_TABLE_PREFIX . "COST_MODEL ncmodels,
						" . VTM_TABLE_PREFIX . "CLAN ncclans
					WHERE
						ncclans.NONCLAN_COST_MODEL_ID = ncmodels.ID
						AND steps.COST_MODEL_ID = ncmodels.ID
						AND ncclans.ID = %s
						AND steps.NEXT_VALUE > %s";
		$nonsql = $wpdb->prepare($nonsql, $clanid, $level);
		$result = $wpdb->get_results($nonsql);
	}			
	
	return $result;

}
function vtm_get_character_maximums($characterID) {
	global $wpdb;
	
	$maxRating     = 5;
	$maxDiscipline = 5;

	$sql = "SELECT gen.max_rating, gen.max_discipline
				FROM " . VTM_TABLE_PREFIX . "CHARACTER chara,
					 " . VTM_TABLE_PREFIX . "GENERATION gen
				WHERE chara.generation_id = gen.id
				  AND chara.ID = %s";
	$sql = $wpdb->prepare($sql, $characterID);
	$characterMaximums = $wpdb->get_results($sql);

	foreach ($characterMaximums as $charMax) {
		$maxRating = $charMax->max_rating;
		$maxDiscipline = $charMax->max_discipline;
	}
	
	return array($maxRating, $maxDiscipline);
}

function vtm_render_details_section($characterID) {
	global $wpdb;
	
	$output = "";
	$rowoutput = "";

	// Extract the spends and what tables we need to query from
	// the $_REQUEST
	$requestItemTables = array();
	$requestChTables = array();
	$requestspends = array();
	foreach ($_REQUEST as $spend => $details) {
		$data = explode(":",$spend);
		if (count($data) > 1) {
			$requestspends[$spend] = $details;
			$requestItemTables[$data[1]] = 1;
			if ($data[4] != 0) {
				$requestChTables[$data[3]] = 1;
			}
		}
	}	
	ksort($requestspends);
	
	// print "<br>";
	// print_r($requestspends);
	// print "<br>";
	
	// Query the Item Table information
	$itemInfo = array();
	foreach ($requestItemTables as $itemtable => $discard) {
		//Table has SPECIALISATION_AT column?
		$columns = "";
		$existing_columns = $wpdb->get_col("DESC " . VTM_TABLE_PREFIX . $itemtable, 0);

		$match_columns = array_intersect(array("SPECIALISATION_AT"), $existing_columns);
		$columns = empty($match_columns) ? "" : ",SPECIALISATION_AT";
		$match_columns = array_intersect(array("HAS_SPECIALISATION"), $existing_columns);
		$columns .= empty($match_columns) ? "" : ",HAS_SPECIALISATION";

		$itemInfo[$itemtable] = $wpdb->get_results("SELECT ID,NAME $columns FROM " . VTM_TABLE_PREFIX . $itemtable, OBJECT_K);
		
		
	}
	// Query the character table
	$charTableInfo = array();
	foreach ($requestChTables as $chartable => $discard) {
		$sql = $wpdb->prepare("SELECT ID,COMMENT,LEVEL FROM " . VTM_TABLE_PREFIX . $chartable . " WHERE CHARACTER_ID = '%s'",$characterID);
		//print "SQL: $sql<br>";
		$charTableInfo[$chartable] = $wpdb->get_results($sql, OBJECT_K);
	}
	// print "<br>";
	// print_r($charTableInfo);
	// print "<br>";
	
	// Display the table
	foreach ($requestspends as $spend => $details) {
		$level = $details["level"];
		//print_r($details);
		//print "$spend = level: $level<br>";

		if ($level != 0) {
			$data        = explode(":",$spend);
			$index       = $data[0];
			$itemtable   = $data[1];
			$itemid      = $data[2];
			$chartable   = $data[3];
			$chartableid = $data[4];
			$xpcost      = $data[5];
			
			$hasspec      = isset($itemInfo[$itemtable][$itemid]->HAS_SPECIALISATION) ? $itemInfo[$itemtable][$itemid]->HAS_SPECIALISATION : '';
			$specat       = isset($itemInfo[$itemtable][$itemid]->SPECIALISATION_AT) ? $itemInfo[$itemtable][$itemid]->SPECIALISATION_AT : '';
			$comment      = isset($charTableInfo[$chartable][$chartableid]->COMMENT) ? $charTableInfo[$chartable][$chartableid]->COMMENT : '';
			//$spendcomment = isset($charTableInfo[$chartable][$chartableid]->LEVEL)  ? $charTableInfo[$chartable][$chartableid]->LEVEL : '0';
			if ($itemtable == "RITUAL")
				$spendcomment = "Ritual: ";
			elseif ($itemtable == "MERIT")
				if ($level > 0)
					$spendcomment = "Buy Merit: ";
				else
					$spendcomment = "Remove Flaw: ";
			else
				$spendcomment = "";
			$spendcomment .= $itemInfo[$itemtable][$itemid]->NAME;
			if (!empty($comment)) $spendcomment .= " ($comment)";
			$spendcomment .= " ";
			if ($itemtable == "RITUAL")
				$spendcomment .= " (Level $level)";
			elseif ($itemtable == "MERIT")
				$spendcomment .= " (Level $level)";
			else {
				$spendcomment .= isset($charTableInfo[$chartable][$chartableid]->LEVEL) ? $charTableInfo[$chartable][$chartableid]->LEVEL : '0';
				$spendcomment .= " > $level";
			}
			$train = isset($details['training'])? $details['training'] : '';
			
			// print $itemInfo[$itemtable][$itemid]->NAME . ", $chartable, $chartableid: ";
			// print_r($charTableInfo[$chartable][$chartableid]);
			// print "<br>\n";
			
			$rowoutput .= "<tr><td class='vtmcol_key'>".vtm_formatOutput($itemInfo[$itemtable][$itemid]->NAME);
			$rowoutput .= "<input type='hidden' name='{$spend}[level]' value='{$level}'>";
			$rowoutput .= "<input type='hidden' name='{$spend}[name]' value='{$itemInfo[$itemtable][$itemid]->NAME}'>";
			$rowoutput .= "</td>";
						
			// specialisation
			if (empty($comment)) {
				if ($hasspec == 'Y' || ($specat > 0 && $specat <= $level)) {
					$spec = empty($details['spec']) ? "" : $details['spec']; 
					$rowoutput .= "<td><input type='text' name='{$spend}[spec]' value='{$spec}' size=15 maxlength=60></td>";
				}
				else {
					$rowoutput .= "<td>&nbsp;</td>";
				}
			}
			else {
				$rowoutput .= "<td>".vtm_formatOutput($comment)."<input type='hidden' name='{$spend}[spec]' value='{$comment}'></td>";
			}
			
			// Spend information
			$rowoutput .= "<td>".vtm_formatOutput($spendcomment)."<input type='hidden' name='{$spend}[detail]' value='{$spendcomment}'></td>";
			
			// cost
			$rowoutput .= "<td>".vtm_formatOutput($xpcost)."</td>";
			
			// Training
			$rowoutput .= "<td><input type='text'  name='{$spend}[training]' value='$train' size=30 maxlength=160 /></td>";
			$rowoutput .= "</tr>";
			
		}
	}
	
	/*
	$ids      = isset($_REQUEST[$type . '_id'])      ? $_REQUEST[$type . '_id'] : array();
	$levels   = isset($_REQUEST[$type . '_level'])   ? $_REQUEST[$type . '_level'] : array();
	$names    = isset($_REQUEST[$type . '_name'])    ? $_REQUEST[$type . '_name'] : array();
	$specats  = isset($_REQUEST[$type . '_spec_at']) ? $_REQUEST[$type . '_spec_at'] : array();
	$specs    = isset($_REQUEST[$type . '_spec'])    ? $_REQUEST[$type . '_spec'] : array();
	$itemids  = isset($_REQUEST[$type . '_itemid'])  ? $_REQUEST[$type . '_itemid'] : array();
	$trains   = isset($_REQUEST[$type . '_training']) ? $_REQUEST[$type . '_training'] : array();
	$xpcosts  = isset($_REQUEST[$type . '_cost'])    ? $_REQUEST[$type . '_cost'] : array();
	$comments = isset($_REQUEST[$type . '_comment']) ? $_REQUEST[$type . '_comment'] : array();
	
	//print_r($specs);
	//print_r($levels);
	
	foreach ($levels as $index => $level ) {
	
		if ($level != 0) {
			$specat = isset($specats[$index]) ? $specats[$index] : '';
			$name    = vtm_formatOutput($names[$index]);
			$comment = vtm_formatOutput($comments[$index]);
			$spec    = vtm_formatOutput($specs[$index]);
			
			// Hidden fields
			$rowoutput .= "<tr style='display:none'><td colspan=5>";
			$rowoutput .= "<input type='hidden' name='{$type}_level[" . $index . "]' value='$level' >\n";
			$rowoutput .= "<input type='hidden' name='{$type}_name[" . $index . "]' value='$name' >\n";
			$rowoutput .= "<input type='hidden' name='{$type}_id[" . $index . "]' value='{$ids[$index]}' >\n";
			$rowoutput .= "<input type='hidden' name='{$type}_cost[" . $index . "]' value='{$xpcosts[$index]}' >\n";
			$rowoutput .= "<input type='hidden' name='{$type}_comment[" . $index . "]' value='$comment' >\n";
			$rowoutput .= "<input type='hidden' name='{$type}_itemid[" . $index . "]' value='{$itemids[$index]}' >\n";
			$rowoutput .= "<input type='hidden' name='{$type}_spec_at[" . $index . "]' value='$specat' >\n";
			
			$rowoutput .= "</td></tr>";
		
			// name
			$rowoutput .= "<tr><td class='vtmcol_key'>$name</td>";
			
			// specialisation
			if ($specat == 'Y') {
				if (empty($specs[$index]))
					$rowoutput .= "<td><input type='text' name='{$type}_spec[" . $index . "]' value='' size=15 maxlength=60></td>";
				else
					$rowoutput .= "<td>{$spec}<input type='hidden' name='{$type}_spec[" . $index . "]' value='{$specs[$index]}'></td>";
			} elseif ($specat > 0) {
				if (empty($specs[$index]) && $specats[$index] <= $level)
					$rowoutput .= "<td><input type='text' name='{$type}_spec[" . $index . "]' value='' size=15 maxlength=60></td>";
				else
					$rowoutput .= "<td>{$spec}<input type='hidden' name='{$type}_spec[" . $index . "]' value='{$specs[$index]}'></td>";
			} else {
				$rowoutput .= "<td>&nbsp;</td>";
			}
			
			// Spend information
			$rowoutput .= "<td>$comment</td>";
			
			// cost
			$rowoutput .= "<td>{$xpcosts[$index]}</td>";
			
			// Training
			$train = isset($trains[$index])? $trains[$index] : '';
			$rowoutput .= "<td><input type='text'  name='{$type}_training[$index]' value='$train' size=30 maxlength=160 /></td></tr>";
		}
	}
	*/

	if (!empty($rowoutput)) {
		$output .= "<table>\n";
		$output .= "<tr><th class='gvthead'>Name</th><th class='gvthead'>Specialisation</th><th class='gvthead'>Experience Spend</th><th class='gvthead'>XP Cost</th><th class='gvthead'>Training Note/Learning Method</th></tr>";
		$output .= "$rowoutput\n";
		$output .= "</table>\n";
	} 
	
	return $output;
}
//function vtm_render_stats($characterID, $maxRating, $pendingSpends, $xp_avail) {
function vtm_get_sql_stats($characterID) {
	global $wpdb;
		
	$sql = "SELECT 
				item.ID 						as item_id, 
				item.name						as item_name, 
				item.specialisation_at			as spec_at,
				item.GROUPING 					as grp,
				cha_item.level					as curr_level,
				cha_item.comment				as comment,
				cha_item.id						as cha_item_id, 
				steps.XP_COST					as xp_cost,
				steps.NEXT_VALUE				as next_level,
				pendingspend.CHARTABLE_LEVEL	as pending_level,
				NOT(ISNULL(CHARTABLE_LEVEL)) 	as has_pending, 
				pendingspend.ID 				as pending_id
			FROM " . VTM_TABLE_PREFIX . "CHARACTER_STAT cha_item
				LEFT JOIN 
					(SELECT ID, CHARTABLE_LEVEL, CHARTABLE_ID
					FROM
						" . VTM_TABLE_PREFIX . "PENDING_XP_SPEND pending
					WHERE 
						pending.CHARACTER_ID = %s
						AND pending.CHARTABLE = 'CHARACTER_STAT'
					) as pendingspend
				ON
					pendingspend.CHARTABLE_ID = cha_item.id,
				 " . VTM_TABLE_PREFIX . "STAT item,
				 " . VTM_TABLE_PREFIX . "COST_MODEL_STEP steps,
				 " . VTM_TABLE_PREFIX . "COST_MODEL models
			WHERE 
				cha_item.STAT_ID      = item.ID
				AND steps.COST_MODEL_ID = models.ID
				AND item.COST_MODEL_ID = models.ID
				AND steps.CURRENT_VALUE = cha_item.level
				AND cha_item.CHARACTER_ID = %s
		   ORDER BY item.ordering";
	$sql = $wpdb->prepare($sql, $characterID,$characterID);
	//echo "<p>SQL: $sql</p>";
	//$character_stats_xp = $wpdb->get_results($sql);
		
	//$rowoutput = vtm_render_spend_table('stat', $character_stats_xp, $maxRating, $vtmglobal['config']->WEB_COLUMNS, $xp_avail);
	
	if (!empty($rowoutput)) {
		$output .= "<table>\n";
		$output .= "$rowoutput\n";
		$output .= "</table>\n";
	} 

	return $sql;

}
function vtm_get_sql_skills($characterID) {
	global $wpdb;
	
	$sqlsingle = "SELECT
				item.id 					as item_id,
				item.name 					as item_name, 
				item.specialisation_at 		as spec_at, 
				skilltype.name 				as grp,
				IFNULL(cha_item.level,0)	as curr_level, 
				cha_item.comment 			as comment, 
				cha_item.id					as cha_item_id,
				steps.XP_COST				as xp_cost,
				steps.NEXT_VALUE			as next_level, 
				pending.CHARTABLE_LEVEL		as pending_level,
				NOT(ISNULL(pending.CHARTABLE_LEVEL)) as has_pending, 
				pending.ID 					as pending_id,
				item.MULTIPLE				as multiple,
				skilltype.ordering 			as ordering
			FROM
				" . VTM_TABLE_PREFIX . "SKILL item
				LEFT JOIN 
					(SELECT *
					FROM " . VTM_TABLE_PREFIX . "CHARACTER_SKILL
					WHERE 
						CHARACTER_ID = '%s'
					) as cha_item
				ON
					cha_item.SKILL_ID = item.ID
				LEFT JOIN
					(SELECT ID, ITEMTABLE_ID, CHARTABLE_LEVEL
					FROM
						" . VTM_TABLE_PREFIX . "PENDING_XP_SPEND
					WHERE
						CHARACTER_ID = %s
						AND ITEMTABLE = 'SKILL'
					) as pending
				ON
					pending.ITEMTABLE_ID = item.ID,
				" . VTM_TABLE_PREFIX . "SKILL_TYPE skilltype,
				" . VTM_TABLE_PREFIX . "COST_MODEL_STEP steps,
				" . VTM_TABLE_PREFIX . "COST_MODEL models
			WHERE
				steps.COST_MODEL_ID = models.ID
				AND item.COST_MODEL_ID = models.ID
				AND item.SKILL_TYPE_ID = skilltype.ID
				AND 
					(
						(NOT(ISNULL(cha_item.level)) AND steps.CURRENT_VALUE = cha_item.level)
						OR
						(ISNULL(cha_item.level) AND steps.CURRENT_VALUE = 0)
					)
				AND item.MULTIPLE = 'N'
				AND (item.VISIBLE = 'Y' OR pending.CHARTABLE_LEVEL > 0)
				";
	
	$sql_mult_new = "SELECT
				item.id 					as item_id,
				item.name 					as item_name, 
				item.specialisation_at 		as spec_at, 
				skilltype.name 				as grp,
				0							as curr_level, 
				'' 							as comment, 
				0							as cha_item_id,
				steps.XP_COST				as xp_cost,
				steps.NEXT_VALUE			as next_level, 
				0							as pending_level,
				0 							as has_pending, 
				0 							as pending_id,
				item.MULTIPLE				as multiple,
				skilltype.ordering 			as ordering
			FROM
				" . VTM_TABLE_PREFIX . "SKILL item,
				" . VTM_TABLE_PREFIX . "SKILL_TYPE skilltype,
				" . VTM_TABLE_PREFIX . "COST_MODEL_STEP steps,
				" . VTM_TABLE_PREFIX . "COST_MODEL models
			WHERE
				steps.CURRENT_VALUE = 0
				AND steps.COST_MODEL_ID = models.ID
				AND item.COST_MODEL_ID  = models.ID
				AND item.SKILL_TYPE_ID = skilltype.ID
				AND item.MULTIPLE = 'Y'";
	
	$sql_mult_ch_pend = "SELECT
				item.id 					as item_id,
				item.name 					as item_name, 
				item.specialisation_at 		as spec_at, 
				skilltype.name 				as grp,
				IFNULL(cha_item.level,0)	as curr_level, 
				cha_item.comment 			as comment, 
				cha_item.id					as cha_item_id,
				steps.XP_COST				as xp_cost,
				steps.NEXT_VALUE			as next_level, 
				pending.CHARTABLE_LEVEL		as pending_level,
				NOT(ISNULL(pending.CHARTABLE_LEVEL)) as has_pending, 
				pending.ID 					as pending_id,
				item.MULTIPLE				as multiple,
				skilltype.ordering 			as ordering
			FROM
				" . VTM_TABLE_PREFIX . "CHARACTER_SKILL cha_item
				LEFT JOIN
						(SELECT ID, CHARTABLE_ID, CHARTABLE_LEVEL
						FROM
							" . VTM_TABLE_PREFIX . "PENDING_XP_SPEND
						WHERE
							CHARACTER_ID = %s
							AND CHARTABLE = 'CHARACTER_SKILL'
						) as pending
				ON
					pending.CHARTABLE_ID = cha_item.ID,
				" . VTM_TABLE_PREFIX . "SKILL item,
				" . VTM_TABLE_PREFIX . "SKILL_TYPE skilltype,
				" . VTM_TABLE_PREFIX . "COST_MODEL_STEP steps,
				" . VTM_TABLE_PREFIX . "COST_MODEL models
			WHERE
				cha_item.CHARACTER_ID = %s
				AND cha_item.SKILL_ID = item.ID
				AND steps.COST_MODEL_ID = models.ID
				AND item.COST_MODEL_ID = models.ID
				AND item.SKILL_TYPE_ID = skilltype.ID
				AND steps.CURRENT_VALUE = cha_item.level
				AND item.MULTIPLE = 'Y'
			";
	
	$sql_mult_new_pend = "SELECT
				item.id 					as item_id,
				item.name 					as item_name, 
				item.specialisation_at 		as spec_at, 
				skilltype.name 				as grp,
				0							as curr_level, 
				pending.SPECIALISATION		as comment, 
				0							as cha_item_id,
				steps.XP_COST				as xp_cost,
				steps.NEXT_VALUE			as next_level, 
				pending.CHARTABLE_LEVEL		as pending_level,
				NOT(ISNULL(pending.CHARTABLE_LEVEL)) as has_pending, 
				pending.ID 					as pending_id,
				item.MULTIPLE				as multiple,
				skilltype.ordering 			as ordering
			FROM
				" . VTM_TABLE_PREFIX . "PENDING_XP_SPEND as pending,
				" . VTM_TABLE_PREFIX . "SKILL item,
				" . VTM_TABLE_PREFIX . "SKILL_TYPE skilltype,
				" . VTM_TABLE_PREFIX . "COST_MODEL_STEP steps,
				" . VTM_TABLE_PREFIX . "COST_MODEL models
			WHERE
				steps.COST_MODEL_ID = models.ID
				AND item.COST_MODEL_ID = models.ID
				AND item.SKILL_TYPE_ID = skilltype.ID
				AND steps.CURRENT_VALUE = 0
				AND item.MULTIPLE = 'Y'	
				AND pending.CHARACTER_ID = '%s'
				AND pending.ITEMTABLE = 'SKILL'
				AND pending.ITEMTABLE_ID = item.ID
				AND pending.CHARTABLE_ID = 0
	";
	
	$sql = "($sqlsingle)
			UNION
			($sql_mult_new)
			UNION
			($sql_mult_ch_pend)
			UNION
			($sql_mult_new_pend)
			ORDER BY ordering, grp, item_name, cha_item_id";

	//$sql = $sql_mult_ch_pend;
	$sql = $wpdb->prepare($sql, $characterID, $characterID, $characterID, $characterID, $characterID);
	//print "SQL: $sql";
	
	return $sql;
}

function vtm_render_skills($characterID, $maxRating, $pendingSpends, $xp_avail) {
	global $wpdb;
	global $vtmglobal;
	
	$output = "";
	
	// All the skills currently had, with pending
		// plus all the pending skills not already had
		
		// Then list all the available skills to buy, current level, pending and new level
	
	
	$sqlCharacterSkill = "SELECT
					skill.name as name, 
					cha_skill.level as level, 
					cha_skill.comment as comment, 
					cha_skill.id as id,
					skill.specialisation_at as spec_at, 
					skill.id as item_id,
					skilltype.name as grp,
					skilltype.ordering ordering,
					pending.CHARTABLE_LEVEL,
					steps.XP_COST,
					steps.NEXT_VALUE, 
					skill.COST_MODEL_ID as COST_MODEL_ID,
					NOT(ISNULL(pending.CHARTABLE_LEVEL)) as has_pending, 
					pending.ID as pending_id
				FROM
					" . VTM_TABLE_PREFIX . "CHARACTER_SKILL cha_skill
					LEFT JOIN
						(SELECT ID, CHARTABLE_ID, CHARTABLE_LEVEL
						FROM
							" . VTM_TABLE_PREFIX . "PENDING_XP_SPEND
						WHERE
							CHARACTER_ID = %s
							AND CHARTABLE = 'CHARACTER_SKILL'
						) as pending
					ON
						pending.CHARTABLE_ID = cha_skill.ID,
					" . VTM_TABLE_PREFIX . "SKILL skill,
					" . VTM_TABLE_PREFIX . "SKILL_TYPE skilltype,
					" . VTM_TABLE_PREFIX . "COST_MODEL_STEP steps,
					" . VTM_TABLE_PREFIX . "COST_MODEL models
				WHERE
					cha_skill.CHARACTER_ID = %s
					AND cha_skill.SKILL_ID = skill.ID
					AND steps.COST_MODEL_ID = models.ID
					AND skill.COST_MODEL_ID = models.ID
					AND skill.SKILL_TYPE_ID = skilltype.ID
					AND steps.CURRENT_VALUE = cha_skill.level";
	$sqlPending = "SELECT
					skill.name as name, 
					0 as level, 
					pending.SPECIALISATION as comment, 
					0 as id,
					skill.specialisation_at as spec_at, 
					skill.id as item_id,
					skilltype.name as grp,
					skilltype.ordering ordering,
					pending.CHARTABLE_LEVEL,
					steps.XP_COST,
					steps.NEXT_VALUE, 
					skill.COST_MODEL_ID as COST_MODEL_ID,
					1 as has_pending, 
					pending.ID as pending_id
				FROM
					" . VTM_TABLE_PREFIX . "SKILL skill,
					" . VTM_TABLE_PREFIX . "SKILL_TYPE skilltype,
					" . VTM_TABLE_PREFIX . "COST_MODEL_STEP steps,
					" . VTM_TABLE_PREFIX . "COST_MODEL models,
					" . VTM_TABLE_PREFIX . "PENDING_XP_SPEND pending
				WHERE
					pending.CHARACTER_ID = %s
					AND pending.CHARTABLE = 'CHARACTER_SKILL'
					AND pending.ITEMTABLE_ID = skill.ID
					AND pending.CHARTABLE_ID = 0
					AND steps.COST_MODEL_ID = models.ID
					AND skill.COST_MODEL_ID  = models.ID
					AND skill.SKILL_TYPE_ID = skilltype.ID
					AND steps.CURRENT_VALUE = 0";					
	
	$sql = "$sqlCharacterSkill
			UNION
			$sqlPending
			ORDER BY ordering, grp, name, level DESC, comment";
	
	
	$sql = $wpdb->prepare($sql, $characterID, $characterID, $characterID, $characterID, $characterID);
    //echo "<p>SQL: $sql</p>";
	$character_skills_xp = vtm_reformat_skills_xp($wpdb->get_results($sql));
	
	$sql = "SELECT
				skill.name as name, 
				0 as level, 
				'' as comment, 
				0 as id,
				skill.specialisation_at as spec_at, 
				skill.id as item_id,
				skilltype.name as grp,
				skilltype.ordering ordering,
				0 as CHARTABLE_LEVEL,
				steps.XP_COST,
				steps.NEXT_VALUE, 
				skill.COST_MODEL_ID as COST_MODEL_ID,
				0 as has_pending, 
				0 as pending_id,
				skill.VISIBLE,
				skill.MULTIPLE
			FROM
				" . VTM_TABLE_PREFIX . "SKILL skill,
				" . VTM_TABLE_PREFIX . "SKILL_TYPE skilltype,
				" . VTM_TABLE_PREFIX . "COST_MODEL_STEP steps,
				" . VTM_TABLE_PREFIX . "COST_MODEL models
			WHERE
				steps.CURRENT_VALUE = 0
				AND steps.COST_MODEL_ID = models.ID
				AND skill.COST_MODEL_ID  = models.ID
				AND skill.SKILL_TYPE_ID = skilltype.ID
			ORDER BY ordering, grp, name";
	$skills_list = $wpdb->get_results($sql);
	
    //echo "<p>SQL: $sql</p>";
	//print_r($skills_list);
	
	$rowoutput = vtm_render_skill_spend_table('skill', $skills_list, $character_skills_xp, 
						$maxRating, $vtmglobal['config']->WEB_COLUMNS, $xp_avail);
	
	if (!empty($rowoutput)) {
		$output .= "<table>\n";
		$output .= "$rowoutput\n";
		$output .= "</table>\n";
	} 

	return $output;

}

function vtm_render_skills_row($type, $rownum, $max2display, $maxRating, $datarow, $levelsdata, $xp_avail) {

	$fulldoturl    = VTM_PLUGIN_URL . '/images/dot1full.' . VTM_ICON_FORMAT;
	$emptydoturl   = VTM_PLUGIN_URL . '/images/dot1empty.' . VTM_ICON_FORMAT;
	$pendingdoturl = VTM_PLUGIN_URL . '/images/dot2.' . VTM_ICON_FORMAT;

	$datarow->comment = vtm_formatOutput($datarow->comment);
	$datarow->name    = vtm_formatOutput($datarow->name);
	
	$rowoutput = "";
		// Hidden fields
	$rowoutput .= "<tr style='display:none'><td colspan=3>\n";
	$rowoutput .= "<input type='hidden' name='{$type}_spec_at[" . $rownum . "]' value='" . $datarow->spec_at . "' >";
	$rowoutput .= "<input type='hidden' name='{$type}_spec[" . $rownum . "]'    value='" . $datarow->comment . "' >";
	$rowoutput .= "<input type='hidden' name='{$type}_curr[" . $rownum . "]'    value='" . $datarow->level . "' >\n";
	$rowoutput .= "<input type='hidden' name='{$type}_itemid[" . $rownum . "]'  value='" . $datarow->item_id . "' >\n";
	$rowoutput .= "<input type='hidden' name='{$type}_id[" . $rownum . "]'      value='" . $datarow->id . "' >\n";
	$rowoutput .= "<input type='hidden' name='{$type}_name[" . $rownum . "]'    value='" . $datarow->name . "' >\n";
	$rowoutput .= "</td></tr>\n";
	

	//dots row
	$xpcost = 0;
	$rowoutput .= "<tr><td class='vtmcol_key'><span";
	if ($datarow->comment)
		$rowoutput .= " title='{$datarow->comment}' class='vtmxp_spec' ";
	$rowoutput .= ">{$datarow->name}</span></td>";
		$rowoutput .= "<td class='vtmdots vtmdot_$max2display'>";
	for ($i=1;$i<=$max2display;$i++) {
	
		if ($datarow->level >= $i)
			$rowoutput .= "<img alt='*' src='$fulldoturl'>";
		elseif ($maxRating < $i)
			$rowoutput .= "<img alt='O' src='$emptydoturl'>";
		elseif ($datarow->CHARTABLE_LEVEL)
			if ($datarow->CHARTABLE_LEVEL >= $i)
				$rowoutput .= "<img alt='X' src='$pendingdoturl'>";
			else
				$rowoutput .= "<img alt='O' src='$emptydoturl'>";
		else
			if ($datarow->NEXT_VALUE == $i) {
			
				if ($datarow->NEXT_VALUE > $datarow->level)
					$xpcost = $datarow->XP_COST;
				
				if ($xp_avail >= $xpcost) {
					$comment    = $datarow->name . " " . $datarow->level . " > " . $i;
				
					$rowoutput .= "<input type='hidden'   name='{$type}_cost[" . $rownum . "]'    value='" . $xpcost . "' >";
					$rowoutput .= "<input type='hidden'   name='{$type}_comment[" . $rownum . "]' value='$comment' >";
					$rowoutput .= "<input type='CHECKBOX' name='{$type}_level[" . $rownum . "]'   value='$i' id='vtmskcb_$rownum'";
					if (isset($levelsdata[$rownum]) && $i == $levelsdata[$rownum])
						$rowoutput .= "checked";
					$rowoutput .= "><label for='vtmskcb_$rownum' title='[ ]'>&nbsp;</label>";
				}
				else
					$rowoutput .= "<img alt='O' src='$emptydoturl'>";
			}
			else
				$rowoutput .= "<img alt='O' src='$emptydoturl'>";
				
	}
	
		
	//$xpcost = ($datarow->NEXT_VALUE <= $maxRating) ? "(" . $datarow->XP_COST . " XP)" : "";
	$xpcost = ($datarow->NEXT_VALUE <= $maxRating) ? $datarow->XP_COST . "xp" : "";
	if ($datarow->has_pending)
		$rowoutput .= "<td class='vtmxp_cost'><input class='vtmxp_clear' type='submit' name='{$type}_cancel[{$datarow->pending_id}]' value='Del'></td>";
	else
		$rowoutput .= "<td class='vtmxp_cost'>$xpcost</td>";
	$rowoutput .= "</tr>\n";

	return $rowoutput;

}

function vtm_reformat_skills_xp($input) {

	$arrayout = array();
	
	foreach ($input as $row) {
		if (array_key_exists($row->item_id, $arrayout)) {
			array_push($arrayout[$row->item_id],$row);
		} else {
			$arrayout[$row->item_id] = array($row);
		}
	
	}
	
	//print_r($arrayout);
	
	return $arrayout;

}
function vtm_get_sql_combo($characterID) {
	global $wpdb;
	
	$sql = "SELECT 
				combo.id 								as item_id,
				combo.NAME 								as item_name, 
				0										as spec_at,
				''										as grp,
				IF(ISNULL(charcombo.ID),0,1) 			as curr_level,
				''										as comment,
				charcombo.ID 							as cha_item_id,
				combo.COST 								as xp_cost,
				1 										as next_level,
				NOT(ISNULL(pending.CHARTABLE_LEVEL)) 	as pending_level,
				NOT(ISNULL(pending.CHARTABLE_LEVEL)) 	as has_pending,
				pending.ID 								as pending_id,
				combo.VISIBLE							as visible,
				IF(SUM(IF(prereq.DISCIPLINE_LEVEL <= chardisc.LEVEL,1,0)) = COUNT(prereq.DISCIPLINE_LEVEL),'Y','N') as meets_prereq
			FROM
				" . VTM_TABLE_PREFIX . "DISCIPLINE disciplines,
				" . VTM_TABLE_PREFIX . "COMBO_DISCIPLINE combo
				LEFT JOIN
					(SELECT ID, COMBO_DISCIPLINE_ID
					FROM " . VTM_TABLE_PREFIX . "CHARACTER_COMBO_DISCIPLINE 
					WHERE CHARACTER_ID = %s
					) as charcombo
				ON
					charcombo.COMBO_DISCIPLINE_ID = combo.ID
				LEFT JOIN
					(SELECT ID, CHARTABLE_LEVEL, CHARTABLE_ID, ITEMTABLE_ID
					FROM
						" . VTM_TABLE_PREFIX . "PENDING_XP_SPEND
					WHERE 
						CHARACTER_ID = %s
						AND CHARTABLE = 'CHARACTER_COMBO_DISCIPLINE'
					) as pending
				ON
					pending.ITEMTABLE_ID = combo.ID
					,
				" . VTM_TABLE_PREFIX . "COMBO_DISCIPLINE_PREREQUISITE prereq
				LEFT JOIN
					(SELECT DISCIPLINE_ID as ID, LEVEL 
					FROM " . VTM_TABLE_PREFIX . "CHARACTER_DISCIPLINE
					WHERE CHARACTER_ID = %s
					) as chardisc
				ON
					prereq.DISCIPLINE_ID = chardisc.ID
			WHERE	
				prereq.COMBO_DISCIPLINE_ID = combo.ID
				AND prereq.DISCIPLINE_ID = disciplines.ID
			GROUP BY combo.NAME";
	$sql = $wpdb->prepare($sql, $characterID,$characterID,$characterID);
	
	return $sql;
}
	

function vtm_get_sql_disc($characterID) {
	global $wpdb;
	
	$sql = "SELECT
				item.ID 							as item_id,
				item.name							as item_name,
				0									as spec_at,
				IF(ISNULL(clandisc.DISCIPLINE_ID),'Non-Clan Discipline','Clan Discipline') as grp,
				cha_item.level 						as curr_level,
				''									as comment,
				cha_item.ID 						as cha_item_id,
				IF(ISNULL(clandisc.DISCIPLINE_ID),nonclansteps.XP_COST,clansteps.XP_COST) 		as xp_cost,
				IF(ISNULL(clandisc.DISCIPLINE_ID),nonclansteps.NEXT_VALUE,clansteps.NEXT_VALUE) as next_level,
				pendingspend.CHARTABLE_LEVEL		as pending_level,
				NOT(ISNULL(CHARTABLE_LEVEL)) 		as has_pending,
				pendingspend.ID 					as pending_id
			FROM
				" . VTM_TABLE_PREFIX . "DISCIPLINE item
				LEFT JOIN
					(SELECT ID, LEVEL, CHARACTER_ID, DISCIPLINE_ID
					FROM
						" . VTM_TABLE_PREFIX . "CHARACTER_DISCIPLINE
					WHERE
						CHARACTER_ID = %s
					) cha_item
				ON
					cha_item.DISCIPLINE_ID = item.ID
				LEFT JOIN
					(SELECT ID, CHARTABLE_LEVEL, CHARTABLE_ID, ITEMTABLE_ID
					FROM
						" . VTM_TABLE_PREFIX . "PENDING_XP_SPEND pending
					WHERE	
						pending.CHARACTER_ID = %s
						AND pending.CHARTABLE = 'CHARACTER_DISCIPLINE'
					) as pendingspend
				ON
					pendingspend.ITEMTABLE_ID = item.id
				LEFT JOIN
					(SELECT DISCIPLINE_ID, CLAN_ID
					FROM
						" . VTM_TABLE_PREFIX . "CLAN clans,
						" . VTM_TABLE_PREFIX . "CLAN_DISCIPLINE cd,
						" . VTM_TABLE_PREFIX . "CHARACTER chars
					WHERE
						chars.ID = %s
						AND chars.PRIVATE_CLAN_ID = clans.ID
						AND cd.CLAN_ID = clans.ID
					) as clandisc
				ON
					clandisc.DISCIPLINE_ID = item.id
				,
				" . VTM_TABLE_PREFIX . "CHARACTER chars,
				" . VTM_TABLE_PREFIX . "CLAN clans,
				" . VTM_TABLE_PREFIX . "COST_MODEL_STEP clansteps,
				" . VTM_TABLE_PREFIX . "COST_MODEL clanmodels,
				" . VTM_TABLE_PREFIX . "COST_MODEL_STEP nonclansteps,
				" . VTM_TABLE_PREFIX . "COST_MODEL nonclanmodels
			WHERE
				clansteps.COST_MODEL_ID = clanmodels.ID
				AND clans.CLAN_COST_MODEL_ID = clanmodels.ID
				AND nonclansteps.COST_MODEL_ID = nonclanmodels.ID
				AND clans.NONCLAN_COST_MODEL_ID = nonclanmodels.ID
				AND chars.PRIVATE_CLAN_ID = clans.ID
				AND chars.ID = %s
				AND (
					NOT(ISNULL(clandisc.DISCIPLINE_ID)) 
					OR item.VISIBLE = 'Y' 
					OR NOT(ISNULL(cha_item.level)))
				AND (
					(ISNULL(cha_item.LEVEL) AND clansteps.CURRENT_VALUE = 0)
					OR clansteps.CURRENT_VALUE = cha_item.level
				)
				AND (
					(ISNULL(cha_item.LEVEL) AND nonclansteps.CURRENT_VALUE = 0)
					OR nonclansteps.CURRENT_VALUE = cha_item.level
				)
			ORDER BY grp, item.name";
	$sql = $wpdb->prepare($sql, $characterID,$characterID,$characterID,$characterID);

	return $sql;

}
function vtm_get_sql_path($characterID) {
	global $wpdb;
	
	$sql = "SELECT
				item.ID 						as item_id,
				item.name						as item_name,
				0								as spec_at,
				disc.name 						as grp,
				cha_item.level 					as curr_level,
				cha_item.ID 					as cha_item_id,
				steps.XP_COST 					as xp_cost,
				steps.NEXT_VALUE 				as next_level,
				pendingspend.CHARTABLE_LEVEL	as pending_level,
				NOT(ISNULL(CHARTABLE_LEVEL)) 	as has_pending,
				pendingspend.ID 				as pending_id,
				char_disc.level 				as disclevel,
				primarypath.PATH_ID 			as primary_path_id
			FROM
				" . VTM_TABLE_PREFIX . "DISCIPLINE disc
				LEFT JOIN (
					SELECT PATH_ID, DISCIPLINE_ID
					FROM " . VTM_TABLE_PREFIX . "CHARACTER_PRIMARY_PATH
					WHERE CHARACTER_ID = '%s'
				) primarypath
				ON
					primarypath.DISCIPLINE_ID = disc.ID,
				" . VTM_TABLE_PREFIX . "PATH item
				LEFT JOIN
					(SELECT ID, LEVEL, COMMENT, CHARACTER_ID, PATH_ID
					FROM
						" . VTM_TABLE_PREFIX . "CHARACTER_PATH
					WHERE
						CHARACTER_ID = %s
					) cha_item
				ON
					cha_item.PATH_ID = item.ID
				LEFT JOIN 
					(SELECT ID, CHARTABLE_LEVEL, CHARTABLE_ID, ITEMTABLE_ID
					FROM
						" . VTM_TABLE_PREFIX . "PENDING_XP_SPEND pending
					WHERE 
						pending.CHARACTER_ID = %s
						AND pending.CHARTABLE = 'CHARACTER_PATH'
					) as pendingspend
				ON
					pendingspend.ITEMTABLE_ID = item.id
				LEFT JOIN
					(SELECT *
					FROM
						" . VTM_TABLE_PREFIX . "CHARACTER_DISCIPLINE 
					WHERE
						CHARACTER_ID = %s
					) as char_disc
				ON
					char_disc.DISCIPLINE_ID = item.DISCIPLINE_ID
				,
				 " . VTM_TABLE_PREFIX . "COST_MODEL_STEP steps,
				 " . VTM_TABLE_PREFIX . "COST_MODEL models
			WHERE 
				steps.COST_MODEL_ID = models.ID
				AND item.COST_MODEL_ID = models.ID
				AND disc.ID = item.DISCIPLINE_ID
				AND char_disc.DISCIPLINE_ID = disc.ID
				AND 
					(char_disc.level >= cha_item.level
					OR ISNULL(cha_item.level)
					)
				AND (
					item.VISIBLE = 'Y'
					OR (NOT(ISNULL(cha_item.LEVEL)) AND steps.CURRENT_VALUE > 0)
				)
				AND (
					(ISNULL(cha_item.LEVEL) AND steps.CURRENT_VALUE = 0)
					OR steps.CURRENT_VALUE = cha_item.level
				)
				AND (
					steps.XP_COST > 0
					OR steps.CURRENT_VALUE > 0
				)
		   ORDER BY grp, item.name";
	$sql = $wpdb->prepare($sql, $characterID,$characterID,$characterID,$characterID);
	
	return $sql;

}
function vtm_get_sql_merit($characterID) {
	global $wpdb;

	$sql = "SELECT 
				merit.ID 						as item_id, 
				merit.name						as item_name, 
				IF(cha_merit.level < 0,'Remove Flaws','Buy Merits') as grp,
				cha_merit.level					as curr_level,
				cha_merit.comment				as comment,
				cha_merit.id					as cha_item_id,
				merit.XP_COST					as xp_cost,
				merit.value						as next_level,
				pendingspend.CHARTABLE_LEVEL 	as pending_level,
				NOT(ISNULL(pendingspend.ID)) 	as has_pending,
				pendingspend.ID 				as pending_id,
				merit.has_specialisation		as has_specialisation,
				merit.VISIBLE					as visible,
				merit.MULTIPLE					as multiple
			FROM
				" . VTM_TABLE_PREFIX . "MERIT merit,
				" . VTM_TABLE_PREFIX . "CHARACTER_MERIT cha_merit
				LEFT JOIN 
					(SELECT ID, CHARTABLE_LEVEL, CHARTABLE_ID, ITEMTABLE_ID, SPECIALISATION
					FROM
						" . VTM_TABLE_PREFIX . "PENDING_XP_SPEND pending
					WHERE 
						pending.CHARACTER_ID = %s
						AND pending.CHARTABLE = 'CHARACTER_MERIT'
					) as pendingspend
				ON
					pendingspend.CHARTABLE_ID = cha_merit.ID
			WHERE	
				cha_merit.MERIT_ID = merit.ID
				AND cha_merit.CHARACTER_ID = %s
			UNION
			SELECT
				merit.ID 						as item_id, 
				merit.name						as item_name, 
				IF(merit.value < 0,'Remove Flaws','Buy Merits') as grp,
				0								as curr_level,
				pendingspend.SPECIALISATION 	as comment,
				0 								as cha_item_id, 
				merit.XP_COST					as xp_cost,
				merit.value 					as next_level,
				pendingspend.CHARTABLE_LEVEL	as pending_level,
				1 								as has_pending, 
				pendingspend.ID 				as pending_id,
				merit.has_specialisation		as has_specialisation,
				merit.VISIBLE					as visible,
				merit.MULTIPLE					as multiple
			FROM
				" . VTM_TABLE_PREFIX . "MERIT merit,
				" . VTM_TABLE_PREFIX . "PENDING_XP_SPEND pendingspend
			WHERE
				merit.ID = pendingspend.ITEMTABLE_ID
				AND pendingspend.CHARACTER_ID = %s
				AND pendingspend.CHARTABLE = 'CHARACTER_MERIT'
				AND merit.value >= 0
			UNION
			SELECT
				merit.ID 						as item_id, 
				merit.name						as item_name, 
				IF(merit.value < 0,'Remove Flaws','Buy Merits') as grp,
				0								as curr_level,
				''								as comment,
				0								as cha_item_id,
				merit.XP_COST					as xp_cost,
				merit.value 					as next_level,
				0								as pending_level,
				0								as has_pending,
				0								as pending_id,
				merit.has_specialisation		as has_specialisation,
				merit.VISIBLE					as visible,
				merit.MULTIPLE					as multiple
			FROM
				" . VTM_TABLE_PREFIX . "MERIT merit
				LEFT JOIN
					(SELECT * 
					FROM " . VTM_TABLE_PREFIX . "CHARACTER_MERIT) as cm
				ON
					merit.ID = cm.MERIT_ID
			WHERE
				(merit.MULTIPLE = 'Y'
				OR (merit.MULTIPLE = 'N' AND ISNULL(cm.ID)))
				AND merit.XP_COST > 0
				AND merit.VISIBLE = 'Y'
			ORDER BY grp DESC, curr_level DESC, item_name";
			
	$sql = $wpdb->prepare($sql, $characterID,$characterID,$characterID);
 	//print "SQL: $sql";
	return $sql;
}
function vtm_get_sql_ritual($characterID) {
	global $wpdb;
	
	$sql = "SELECT
				ritual.ID 						as item_id,
				ritual.name						as item_name,
				0								as spec_at,
				disc.name 						as grp,		
				NOT(ISNULL(cha_ritual.level)) 	as curr_level,
				cha_ritual.ID					as cha_item_id,
				ritual.COST 					as xp_cost,
				1 								as next_level,
				pendingspend.CHARTABLE_LEVEL	as pending_level,
				NOT(ISNULL(CHARTABLE_LEVEL)) 	as has_pending,
				pendingspend.ID 				as pending_id,
				char_disc.level 				as disclevel,
				ritual.level 					as rituallevel
			FROM
				" . VTM_TABLE_PREFIX . "DISCIPLINE disc,
				" . VTM_TABLE_PREFIX . "RITUAL ritual
				LEFT JOIN
					(SELECT ID, LEVEL, COMMENT, CHARACTER_ID, RITUAL_ID
					FROM
						" . VTM_TABLE_PREFIX . "CHARACTER_RITUAL
					WHERE
						CHARACTER_ID = %s
					) cha_ritual
				ON
					cha_ritual.RITUAL_ID = ritual.ID
				LEFT JOIN 
					(SELECT ID, CHARTABLE_LEVEL, CHARTABLE_ID, ITEMTABLE_ID
					FROM
						" . VTM_TABLE_PREFIX . "PENDING_XP_SPEND pending
					WHERE 
						pending.CHARACTER_ID = %s
						AND pending.CHARTABLE = 'CHARACTER_RITUAL'
					) as pendingspend
				ON
					pendingspend.ITEMTABLE_ID = ritual.id
				LEFT JOIN
					(SELECT *
					FROM
						" . VTM_TABLE_PREFIX . "CHARACTER_DISCIPLINE 
					WHERE
						CHARACTER_ID = %s
					) as char_disc
				ON
					char_disc.DISCIPLINE_ID = ritual.DISCIPLINE_ID
			WHERE 
				disc.ID = ritual.DISCIPLINE_ID
				AND char_disc.level >= ritual.level
				AND ritual.VISIBLE = 'Y'
				AND (NOT(ISNULL(cha_ritual.level)) OR ritual.COST > 0)
		   ORDER BY grp, ritual.level, ritual.name";
	$sql = $wpdb->prepare($sql, $characterID,$characterID,$characterID);
	
	return $sql;
}

function vtm_render_rituals($characterID, $maxRating, $pendingSpends, $xp_avail) {
	global $wpdb;
	global $vtmglobal;
	
	$output = "";
	
	$sql = "SELECT
				ritual.name,
				ritual.level as rituallevel,
				disc.name as grp,		
				NOT(ISNULL(cha_ritual.level)) as level,
				ritual.ID as item_id,
				pendingspend.CHARTABLE_LEVEL,
				ritual.COST as XP_COST,
				1 as NEXT_VALUE,
				NOT(ISNULL(CHARTABLE_LEVEL)) as has_pending,
				pendingspend.ID as pending_id
			FROM
				" . VTM_TABLE_PREFIX . "DISCIPLINE disc,
				" . VTM_TABLE_PREFIX . "RITUAL ritual
				LEFT JOIN
					(SELECT ID, LEVEL, COMMENT, CHARACTER_ID, RITUAL_ID
					FROM
						" . VTM_TABLE_PREFIX . "CHARACTER_RITUAL
					WHERE
						CHARACTER_ID = %s
					) cha_ritual
				ON
					cha_ritual.RITUAL_ID = ritual.ID
				LEFT JOIN 
					(SELECT ID, CHARTABLE_LEVEL, CHARTABLE_ID, ITEMTABLE_ID
					FROM
						" . VTM_TABLE_PREFIX . "PENDING_XP_SPEND pending
					WHERE 
						pending.CHARACTER_ID = %s
						AND pending.CHARTABLE = 'CHARACTER_RITUAL'
					) as pendingspend
				ON
					pendingspend.ITEMTABLE_ID = ritual.id
				LEFT JOIN
					(SELECT *
					FROM
						" . VTM_TABLE_PREFIX . "CHARACTER_DISCIPLINE 
					WHERE
						CHARACTER_ID = %s
					) as char_disc
				ON
					char_disc.DISCIPLINE_ID = ritual.DISCIPLINE_ID
			WHERE 
				disc.ID = ritual.DISCIPLINE_ID
				AND char_disc.level >= ritual.level
				AND ritual.VISIBLE = 'Y'
				AND (NOT(ISNULL(cha_ritual.level)) OR ritual.COST > 0)
		   ORDER BY grp, ritual.level, ritual.name";
	$sql = $wpdb->prepare($sql, $characterID,$characterID,$characterID,$characterID);
    //echo "<p>SQL: $sql</p>";
	$character_data = $wpdb->get_results($sql);
	
	$columns = min(2, $vtmglobal['config']->WEB_COLUMNS);
	
	$rowoutput = vtm_render_ritual_spend_table('ritual', $character_data, $columns, $xp_avail);
	$colclass = 'vtm_colfull';
	
	if (!empty($rowoutput)) {
		$output .= "<table><tr><td class='$colclass'><table>\n";
		$output .= "$rowoutput\n";
		$output .= "</table></td></tr></table>\n";
	} 
	
	return $output;

}

function vtm_render_combo($characterID, $pendingSpends, $xp_avail) {
	global $wpdb;
	
	$output = "";
	
//				" . VTM_TABLE_PREFIX . "CHARACTER_COMBO_DISCIPLINE charcombo
//				IF(prereq.DISCIPLINE_LEVEL <= chardisc.LEVEL, 1,0) as meets_prereq,
//				disciplines.NAME as prerequisite_discipline, 
//				prereq.DISCIPLINE_LEVEL as prerequisite_level,
//				chardisc.LEVEL as actual_discipline_level,
//				SUM(IF(prereq.DISCIPLINE_LEVEL <= chardisc.LEVEL,1,0)) as count_met,
//				COUNT(prereq.DISCIPLINE_LEVEL) as count_to_meet
	$sql = "SELECT 
				combo.NAME as name, 
				combo.id as item_id,
				IF(SUM(IF(prereq.DISCIPLINE_LEVEL <= chardisc.LEVEL,1,0)) = COUNT(prereq.DISCIPLINE_LEVEL),'Y','N') as meets_prereq,
				IF(ISNULL(charcombo.ID),0,1) as level,
				NOT(ISNULL(pending.CHARTABLE_LEVEL)) as CHARTABLE_LEVEL,
				combo.COST as XP_COST,
				NOT(ISNULL(pending.CHARTABLE_LEVEL)) as has_pending,
				pending.ID as pending_id,
				combo.VISIBLE
			FROM
				" . VTM_TABLE_PREFIX . "DISCIPLINE disciplines,
				" . VTM_TABLE_PREFIX . "COMBO_DISCIPLINE combo
				LEFT JOIN
					(SELECT ID, COMBO_DISCIPLINE_ID
					FROM " . VTM_TABLE_PREFIX . "CHARACTER_COMBO_DISCIPLINE 
					WHERE CHARACTER_ID = %s
					) as charcombo
				ON
					charcombo.COMBO_DISCIPLINE_ID = combo.ID
				LEFT JOIN
					(SELECT ID, CHARTABLE_LEVEL, CHARTABLE_ID, ITEMTABLE_ID
					FROM
						" . VTM_TABLE_PREFIX . "PENDING_XP_SPEND
					WHERE 
						CHARACTER_ID = %s
						AND CHARTABLE = 'CHARACTER_COMBO_DISCIPLINE'
					) as pending
				ON
					pending.ITEMTABLE_ID = combo.ID
					,
				" . VTM_TABLE_PREFIX . "COMBO_DISCIPLINE_PREREQUISITE prereq
				LEFT JOIN
					(SELECT DISCIPLINE_ID as ID, LEVEL 
					FROM " . VTM_TABLE_PREFIX . "CHARACTER_DISCIPLINE
					WHERE CHARACTER_ID = %s
					) as chardisc
				ON
					prereq.DISCIPLINE_ID = chardisc.ID
			WHERE	
				prereq.COMBO_DISCIPLINE_ID = combo.ID
				AND prereq.DISCIPLINE_ID = disciplines.ID
			GROUP BY combo.NAME";
	$sql = $wpdb->prepare($sql, $characterID,$characterID,$characterID,$characterID);
    //echo "<p>SQL: $sql</p>";
	$character_data = $wpdb->get_results($sql);
	
	//print_r($character_data);
	
	$rowoutput = vtm_render_combo_spend_table('combo', $character_data, $xp_avail);
	
	if (!empty($rowoutput)) {
		$colclass = 'vtm_colfull';
		$output .= "<table><tr><td class='$colclass'><table>\n";
		$output .= "$rowoutput\n";
		$output .= "</table></td></tr></table>\n";
	} 
	
	return $output;

}

function vtm_render_merits($characterID, $pendingSpends, $xp_avail) {
	global $wpdb;
	global $vtmglobal;
	
	$output = "";
	
	$sql = "SELECT 
				merit.name, 
				cha_merit.level,
				cha_merit.comment,
				cha_merit.id,
				merit.has_specialisation,
				merit.ID as item_id, 
				IF(cha_merit.level < 0,'Remove Flaws','Buy Merits') as grp,
				pendingspend.CHARTABLE_LEVEL as CHARTABLE_LEVEL,
				merit.XP_COST,
				NOT(ISNULL(pendingspend.ID)) as has_pending,
				pendingspend.ID as pending_id,
				merit.VISIBLE
			FROM
				" . VTM_TABLE_PREFIX . "MERIT merit,
				" . VTM_TABLE_PREFIX . "CHARACTER_MERIT cha_merit
				LEFT JOIN 
					(SELECT ID, CHARTABLE_LEVEL, CHARTABLE_ID, ITEMTABLE_ID, SPECIALISATION
					FROM
						" . VTM_TABLE_PREFIX . "PENDING_XP_SPEND pending
					WHERE 
						pending.CHARACTER_ID = %s
						AND pending.CHARTABLE = 'CHARACTER_MERIT'
					) as pendingspend
				ON
					pendingspend.CHARTABLE_ID = cha_merit.ID
			WHERE	
				cha_merit.MERIT_ID = merit.ID
				AND cha_merit.CHARACTER_ID = %s
			UNION
			SELECT
				merit.name, 
				merit.value as level,
				pendingspend.SPECIALISATION as comment,
				0 as id, 
				merit.has_specialisation,
				merit.ID as item_id, 
				IF(merit.value < 0,'Remove Flaws','Buy Merits') as grp,
				pendingspend.CHARTABLE_LEVEL,
				merit.XP_COST,
				1 as has_pending, 
				pendingspend.ID as pending_id,
				merit.VISIBLE
			FROM
				" . VTM_TABLE_PREFIX . "MERIT merit,
				" . VTM_TABLE_PREFIX . "PENDING_XP_SPEND pendingspend
			WHERE
				merit.ID = pendingspend.ITEMTABLE_ID
				AND pendingspend.CHARACTER_ID = %s
				AND pendingspend.CHARTABLE = 'CHARACTER_MERIT'
				AND merit.value >= 0
			ORDER BY grp DESC, level DESC, name";
			
	$sql = $wpdb->prepare($sql, $characterID,$characterID,$characterID);
    //echo "<p>SQL: $sql</p>";
	$character_merit_xp = vtm_reformat_skills_xp($wpdb->get_results($sql));
	
	$sql = "SELECT
				merit.ID as item_id,
				merit.name,
				merit.value as level,
				'' as comment,
				merit.has_specialisation,
				IF(merit.value < 0,'Remove Flaws','Buy Merits') as grp,
				merit.XP_COST,
				merit.VISIBLE,
				merit.MULTIPLE
			FROM
				" . VTM_TABLE_PREFIX . "MERIT merit
			ORDER BY grp DESC, level DESC, name";
	$merits_list = $wpdb->get_results($sql);
    //echo "<p>SQL: $sql</p>";
	$columns = min(2,$vtmglobal['config']->WEB_COLUMNS);
	
	$rowoutput = vtm_render_merit_spend_table('merit', $merits_list, $character_merit_xp, $columns, $xp_avail);
	
	if (!empty($rowoutput)) {
		$output .= "<table>\n";
		$output .= "$rowoutput\n";
		$output .= "</table>\n";
	} 

	return $output;

}

function vtm_render_spend_table($type, $sqlfunction, $characterID, $maxRating, $xp_avail) {
	global $vtmglobal;
	global $wpdb;
	
	$fulldoturl    = VTM_PLUGIN_URL . '/images/dot1full.' . VTM_ICON_FORMAT;
	$emptydoturl   = VTM_PLUGIN_URL . '/images/dot1empty.' . VTM_ICON_FORMAT;
	$pendingdoturl = VTM_PLUGIN_URL . '/images/dot2.' . VTM_ICON_FORMAT;
	$levelsdata    = isset($_REQUEST[$type . '_level']) ? $_REQUEST[$type . '_level'] : array();

	/*if ($type == 'path' || $type == 'ritual' || $type == 'merit') {
		$columns = min(2, $vtmglobal['config']->WEB_COLUMNS);
	} else {
		$columns = $vtmglobal['config']->WEB_COLUMNS;
	}
	switch ($columns) {
		case 1: $colclass = 'vtm_colfull'; break;
		case 2: $colclass = 'vtm_colwide'; break;
		case 3: $colclass = 'vtm_colnarrow'; break;
	}*/
	if ($type == 'path' || $type == 'combo' || $type == 'ritual' || $type == 'merit') {
		$colclass = 'vtmsubsection_wide';
	} else {
		$colclass = 'vtmsubsection';
	}
	
	$allxpdata = $wpdb->get_results(call_user_func($sqlfunction, $characterID));
	//if ($type == 'merit') print_r($allxpdata);
	
	$max2display = vtm_get_max_dots($allxpdata, $maxRating);
	//$colspan = 2 + $max2display;
	$grp      = "";
	$grpcount = 0;
	$extracols = 0;
	$col = 0;
	$rowoutput = array();   // rowoutput[group][title], rowoutput[group][rows] = 
	if (count($allxpdata)>0) {
		$id = 0;
		foreach ($allxpdata as $xpdata) {
			//$id = $xpdata->id;
			
			if ($type == "combo") {
				// don't display combo-disciplines if you don't have them and you
				// don't meet the pre-requisites
				if ($xpdata->meets_prereq == 'N' && $xpdata->curr_level == 0)
					continue;
				// don't display if you don't have them and it isn't set to be visible
				if ($xpdata->visible == 'N' && $xpdata->curr_level == 0)
					continue;
				// don't display if they don't have an xp cost
				if ($xpdata->xp_cost == 0)
					continue;
				
			}
			
			$tmp_max2display = $max2display;
			$checkboxname = "$id:";
			switch($type) {
				case 'stat':
					$checkboxname .= "STAT:{$xpdata->item_id}:CHARACTER_STAT:{$xpdata->cha_item_id}";
					switch ($xpdata->item_name) {
						case 'Willpower':    
							$tmp_max2display = 10;
							$maxRating = 10;
							break;
						case 'Conscience':   
							$tmp_max2display = 5;
							$maxRating = 5;
							break;
						case 'Conviction':
							$tmp_max2display = 5;
							$maxRating = 5;
							break;
						case 'Self Control': 
							$tmp_max2display = 5;
							$maxRating = 5;
							break;
						case 'Courage':      
							$tmp_max2display = 5;
							$maxRating = 5;
							break;
						case 'Instinct':     
							$tmp_max2display = 5;
							$maxRating = 5;
							break;
						default:
							$tmp_max2display = $maxRating > 5 ? 10 : 5;
					}
					break;
				case 'disc':
					$checkboxname .= "DISCIPLINE:{$xpdata->item_id}:CHARACTER_DISCIPLINE:{$xpdata->cha_item_id}";
					break;
				case 'combo':
					$checkboxname .= "COMBO_DISCIPLINE:{$xpdata->item_id}:CHARACTER_COMBO_DISCIPLINE:{$xpdata->cha_item_id}";
					$tmp_max2display = 1;
					break;
				case 'path':
					$checkboxname .= "PATH:{$xpdata->item_id}:CHARACTER_PATH:{$xpdata->cha_item_id}";
					$tmp_max2display = 5;
					$maxRating = min(5,$xpdata->disclevel);
					break;
				case 'skill':
					$checkboxname .= "SKILL:{$xpdata->item_id}:CHARACTER_SKILL:{$xpdata->cha_item_id}";
					break;
				case 'ritual':
					$checkboxname .= "RITUAL:{$xpdata->item_id}:CHARACTER_RITUAL:{$xpdata->cha_item_id}";
					$tmp_max2display = 1;
					break;
				case 'merit':
					$checkboxname .= "MERIT:{$xpdata->item_id}:CHARACTER_MERIT:{$xpdata->cha_item_id}";
					$tmp_max2display = 1;
					break;
			}
			
			
			// start column / new column
			if ($xpdata->grp == '') {
				$rowoutput[$xpdata->grp]['title'] = "<table>\n";
			}
			if (isset($xpdata->grp)) {
				if ($grp != $xpdata->grp) {
					$grpcount++;
					$rowoutput[$xpdata->grp]['title'] = "<table>\n<tr><th colspan=3>{$xpdata->grp}</th></tr>\n";
										
					/*if (empty($grp)) {
						$rowoutput .= "<div class='$colclass'>\n<table>\n<tr><th colspan=3>{$xpdata->grp}</th></tr>\n";
						$col++;
					} 
					elseif ($col == $columns) {
						$rowoutput .= "</table>\n</td></tr>\n<tr><td class='$colclass'>\n<table>\n<tr><th class='$colclass' colspan=3>{$xpdata->grp}</th></tr>\n";
						$col = 1;
					}
					else {
						$rowoutput .= "</table></div>\n<div class='$colclass'>\n<table>\n<tr><th class='$colclass' colspan=3>{$xpdata->grp}</th></tr>\n";
						$col++;
					}*/
					$grp = $xpdata->grp;
				}
			}
			
			$spec_at   = isset($xpdata->spec_at) ?  $xpdata->spec_at : 0;
			$xpcomment = isset($xpdata->comment) ?  vtm_formatOutput($xpdata->comment) : '';
			$xpid      = isset($xpdata->ch_item_id)      ?  $xpdata->ch_item_id : '';
			$name      = $xpdata->item_name;
			if ($type == 'path' && $xpdata->item_id == $xpdata->primary_path_id) {
				$name .= " (P)";
			}
			if ($type == 'ritual') {
				$name = "(Level $xpdata->rituallevel) $name";
			}
			if ($type == 'merit') {
				$name = "(Level $xpdata->next_level) $name";
			}
			//if ($type == 'path') $name .= " (" . $xpdata->disclevel . "/$maxRating)";
			$name      = vtm_formatOutput($name);
			
			// Hidden fields
			//$rowoutput .= "<tr style='display:none'><td colspan=3>\n";
			//$rowoutput .= "<input type='hidden' name='{$type}_spec_at[" . $id . "]' value='" . $spec_at . "' >";
			//$rowoutput .= "<input type='hidden' name='{$type}_spec[" . $id . "]'    value='" . $xpcomment . "' >";
			//$rowoutput .= "<input type='hidden' name='{$type}_curr[" . $id . "]'    value='" . $xpdata->level . "' >\n";
			//$rowoutput .= "<input type='hidden' name='{$type}_itemid[" . $id . "]'  value='" . $xpdata->item_id . "' >\n";
			//$rowoutput .= "<input type='hidden' name='{$type}_id[" . $id . "]'      value='" . $xpid . "' >\n";
			//$rowoutput .= "<input type='hidden' name='{$type}_name[" . $id . "]'    value='" . $name . "' >\n";
			//$rowoutput .= "</td></tr>\n";
			
			
			//dots row
			$xpcost = 0;
			
			$rowoutput[$grp]['rows'][$id] = "<tr><td class='vtmcol_key'><span";
			if ($xpcomment)
				$rowoutput[$grp]['rows'][$id] .= " title='$xpcomment' class='vtmxp_spec' ";
			$rowoutput[$grp]['rows'][$id] .= ">$name</span></td>\n";
			$rowoutput[$grp]['rows'][$id] .= "<td class='vtmdot_$tmp_max2display vtmdots'>";
			for ($i=1;$i<=$tmp_max2display;$i++) {
			
				if ($xpdata->curr_level >= $i)
					$rowoutput[$grp]['rows'][$id] .= "<img alt='*' src='$fulldoturl'>";
				elseif ($maxRating < $i)
					$rowoutput[$grp]['rows'][$id] .= "<img alt='O' src='$emptydoturl'>";
				elseif ($xpdata->pending_level)
					if ($xpdata->pending_level >= $i)
						$rowoutput[$grp]['rows'][$id] .= "<img alt='X' src='$pendingdoturl'>";
					elseif ($type == 'merit' && $xpdata->next_level < 0 && $xpdata->xp_cost)
						$rowoutput[$grp]['rows'][$id] .= "<img alt='X' src='$pendingdoturl'>";
					else
						$rowoutput[$grp]['rows'][$id] .= "<img alt='O' src='$emptydoturl'>";
				elseif ($type == 'path' && $xpdata->item_id == $xpdata->primary_path_id) {
					// no spending xp on primary paths
					$rowoutput[$grp]['rows'][$id] .= "<img alt='O' src='$emptydoturl'>";
				}
				elseif ($type == 'path' && $maxRating < 5 && $maxRating == $i) {
					// no spending xp on secondary paths if the primary path
					// is less than max and if the new rating is equal to the
					// primary path rating
					$rowoutput[$grp]['rows'][$id] .= "<img alt='O' src='$emptydoturl'>";
				}
				elseif ($type == 'combo' || $type == 'ritual') {
					$xpcost = $xpdata->xp_cost;
				
					if ($xp_avail >= $xpcost) {
					
						$rowoutput[$grp]['rows'][$id] .= "<input type='CHECKBOX' name='$checkboxname:{$xpcost}[level]'   value='{$xpdata->next_level}' id='vtmcb_{$type}_$id' ";
						if (isset($levelsdata[$id]))
							$rowoutput[$grp]['rows'][$id] .= "checked";
						$rowoutput[$grp]['rows'][$id] .= "><label for='vtmcb_{$type}_$id' title='[ ]'>&nbsp;</label>";
					} else
						$rowoutput[$grp]['rows'][$id] .= "<img alt='O' src='$emptydoturl'>";
									
				}
				elseif ($type == 'merit') {
					$xpcost = $xpdata->xp_cost;
					if ($xpdata->next_level < 0) { // Flaw
						if($xpcost) {
							if ($xp_avail >= $xpcost) {
								$rowoutput[$grp]['rows'][$id] .= "<input type='CHECKBOX' name='$checkboxname:{$xpcost}[level]'   value='{$xpdata->next_level}' id='vtmcb_{$type}_$id' ";
								if (isset($levelsdata[$id]))
									$rowoutput[$grp]['rows'][$id] .= "checked";
								$rowoutput[$grp]['rows'][$id] .= "><label for='vtmcb_{$type}_$id' title='[ ]'>&nbsp;</label>";
							} else
								$rowoutput[$grp]['rows'][$id] .= "<img alt='O' src='$emptydoturl'>";

						} else
								$rowoutput[$grp]['rows'][$id] .= "<img alt='O' src='$fulldoturl'>";
					} else {
						if ($xp_avail >= $xpcost) {
							$rowoutput[$grp]['rows'][$id] .= "<input type='CHECKBOX' name='$checkboxname:{$xpcost}[level]'   value='{$xpdata->next_level}' id='vtmcb_{$type}_$id' ";
							if (isset($levelsdata[$id]))
								$rowoutput[$grp]['rows'][$id] .= "checked";
							$rowoutput[$grp]['rows'][$id] .= "><label for='vtmcb_{$type}_$id' title='[ ]'>&nbsp;</label>";
						} else
							$rowoutput[$grp]['rows'][$id] .= "<img alt='O' src='$emptydoturl'>";
					}
				}
				else {
					if ($xpdata->next_level == $i) {
						
						if ($xpdata->next_level > $xpdata->curr_level)
							$xpcost = $xpdata->xp_cost;
							
						if ($xpcost == 0) {
							$rowoutput[$grp]['rows'][$id] .= "<img alt='O' src='$emptydoturl'>";
						}
						elseif ($xp_avail >= $xpcost) {
								
							$comment    = $name . " " . $xpdata->curr_level . " > " . $i;
						
							//$rowoutput .= "<input type='hidden'   name='{$type}_cost[" . $id . "]'    value='" . $xpcost . "' >";
							//$rowoutput .= "<input type='hidden'   name='{$type}_comment[" . $id . "]' value='$comment' >";
							$rowoutput[$grp]['rows'][$id] .= "<input type='CHECKBOX' name='$checkboxname:{$xpcost}[level]'   value='$i' id='vtmcb_{$type}_$id' ";
							if (isset($levelsdata[$id]) && $i == $levelsdata[$id])
								$rowoutput[$grp]['rows'][$id] .= "checked";
							$rowoutput[$grp]['rows'][$id] .= "><label for='vtmcb_{$type}_$id' title='[ ]'>&nbsp;</label>";
						}
						else
							$rowoutput[$grp]['rows'][$id] .= "<img alt='O' src='$emptydoturl'>";
					}
					else
						$rowoutput[$grp]['rows'][$id] .= "<img alt='O' src='$emptydoturl'>";
				}	
			}
			
				
			//$xpcost = ($xpdata->next_level <= $maxRating) ? "(" . $xpdata->XP_COST . " XP)" : "";
			$xpcost = ($xpdata->next_level <= $maxRating) ? $xpdata->xp_cost . "xp" : "";
			if ($xpdata->has_pending)
				$rowoutput[$grp]['rows'][$id] .= "<td class='vtmxp_cost'><input class='vtmxp_clear' type='submit' name='cancel[{$xpdata->pending_id}]' value='Del'></td>";
			elseif ($xpdata->xp_cost == 0) {
				$rowoutput[$grp]['rows'][$id] .= "<td class='vtmxp_cost'>&nbsp;</td>";
			}
			elseif ($type == 'path' && $xpdata->item_id == $xpdata->primary_path_id) {
				$rowoutput[$grp]['rows'][$id] .= "<td class='vtmxp_cost'>&nbsp;</td>";
			}
			else
				$rowoutput[$grp]['rows'][$id] .= "<td class='vtmxp_cost'>$xpcost</td>";
				
			/*if ($id == count($allxpdata)) {
				$remaining = $grpcount % $columns;
				if ($remaining) {
					$extracols = $columns - $remaining;
				}
			}*/
			
			$rowoutput[$grp]['rows'][$id] .= "</tr>\n";
			$id++;
			
		}
	}
	
	/*if ($extracols > 0) {
		$rowoutput .= "</table></td>";
		for ($i = 1 ; $i <= $extracols ; $i++) {
			$rowoutput .= "<td class='$colclass'>&nbsp;</td>";
		}
		$rowoutput .= "</tr>";
	} 
	elseif ($rowoutput != "")
		$rowoutput .= "</table></td></tr>\n";*/

	if (vtm_count($rowoutput) > 0) {
		$output = "<div>";
		foreach ($rowoutput as $grp => $section) {
			$output .= "<div class='$colclass'>\n";
			$output .= $section['title'];
			foreach ($section['rows'] as $id => $row) {
				$output .= $row;
			}
			$output .= "</table>\n";
			$output .= "</div>\n";
		}
		$output .= "</div>";
	} else {
		$output = "";
	}
		
	return $output;
}


function vtm_render_skill_spend_table($type, $list, $allxpdata, $maxRating, $columns, $xp_avail) {

	$levelsdata    = isset($_REQUEST[$type . '_level']) ? $_REQUEST[$type . '_level'] : array();

	$colclass = $columns == 3 ? 'vtm_colnarrow' : 'vtm_colfull';
	$max2display = vtm_get_max_dots($allxpdata, $maxRating);
	//$colspan = 2 + $max2display;
	$multipleonce = array();
	$grp = "";
	$grpcount = 0;
	$col = 0;
	$rowoutput = "";
	//print_r($allxpdata);
	if (count($list)>0) {
		$id = 0;
		$count = 0;
		foreach ($list as $skill) {
		
			if (isset($skill->grp)) {
				if ($grp != $skill->grp) {
					$grpcount++;
					if (empty($grp)) {
						$rowoutput .= "<tr><td class='$colclass'><table><tr><th class='$colclass' colspan=3>" . vtm_formatOutput($skill->grp) . "</th></tr>";
						$col++;
					} 
					elseif ($col == $columns) {
						$rowoutput .= "</table></td></tr><tr><td class='$colclass'><table><tr><th class='$colclass' colspan=3>" . vtm_formatOutput($skill->grp) . "</th></tr>";
						$col = 1;
					}
					else {
						$rowoutput .= "</table></td><td class='$colclass'><table><tr><th class='$colclass' colspan=3>" . vtm_formatOutput($skill->grp) . "</th></tr>";
						$col++;
					}
					$grp = $skill->grp;
				}
			}		
			
			if (array_key_exists($skill->item_id, $allxpdata)) {
				//loop through array
				foreach ($allxpdata[$skill->item_id] as $xpdatarow) {
					//echo "<li>$id : {$xpdatarow->name} : {$xpdatarow->comment}</li>";
					$rowoutput .= vtm_render_skills_row($type, $id, $max2display, $maxRating, $xpdatarow, $levelsdata, $xp_avail);
					$id++;
					if ($skill->MULTIPLE == 'Y' && !array_key_exists($skill->item_id,$multipleonce)) {
						$multipleonce[$skill->item_id] = 1;
						$rowoutput .= vtm_render_skills_row($type, $id, $max2display, $maxRating, $skill, $levelsdata, $xp_avail);
						$id++;
					}
				}
			} else {
				if ($skill->VISIBLE == 'Y') {
					$rowoutput .= vtm_render_skills_row($type, $id, $max2display, $maxRating, $skill, $levelsdata, $xp_avail);
					$id++;
				}
			}
			
			$count++;
			
			if ($count == count($list)) {
				$remaining = $grpcount % $columns;
				if ($remaining)
					$extracols = $columns - $remaining;
			}
			
			
		}
	}
	$rowoutput .= "</table></td>";
	//if ($extracols)
	//	$rowoutput .= "<td colspan=$extracols>&nbsp;</td>\n";	
	$rowoutput .= "</tr>\n";

	return $rowoutput;
}

function vtm_render_ritual_spend_table($type, $allxpdata, $columns, $xp_avail) {

	$fulldoturl    = VTM_PLUGIN_URL . '/images/dot1full.' . VTM_ICON_FORMAT;
	$emptydoturl   = VTM_PLUGIN_URL . '/images/dot1empty.' . VTM_ICON_FORMAT;
	$pendingdoturl = VTM_PLUGIN_URL . '/images/dot2.' . VTM_ICON_FORMAT;
	$levelsdata    = isset($_REQUEST[$type . '_level']) ? $_REQUEST[$type . '_level'] : array();
	
	//$colclass = $columns == 3 ? 'vtm_colnarrow' : 'vtm_colfull';
	$colclass = 'vtm_colwide';

	$max2display = vtm_get_max_dots($allxpdata, 5);
	//$colspan = 3;
	$grp = "";
	$grpcount = 0;
	$extracols = 0;
	$col = 0;
	$rowoutput = "";
	if (count($allxpdata)>0) {
		$id = 0;
		foreach ($allxpdata as $xpdata) {
			//$id = $xpdata->id;
			
			// start column / new column
			if (isset($xpdata->grp)) {
				if ($grp != $xpdata->grp) {
					$grpcount++;
					if (empty($grp)) {
						$rowoutput .= "<tr><td class='$colclass'><table><tr><th class='$colclass' colspan=3>" . vtm_formatOutput($xpdata->grp) . "</th></tr>";
						$col++;
					} 
					elseif ($col == $columns) {
						$rowoutput .= "</table></td></tr><tr><td class='$colclass'><table><tr><th class='$colclass' colspan=3>" . vtm_formatOutput($xpdata->grp) . "</th></tr>";
						$col = 1;
					}
					else {
						$rowoutput .= "</table></td><td class='$colclass'><table><tr><th class='$colclass' colspan=3>" . vtm_formatOutput($xpdata->grp) . "</th></tr>";
						$col++;
					}
					$grp = $xpdata->grp;
				}
			}
			
			// Hidden fields
			$xpid = isset($xpdata->id) ? $xpdata->id : '';
			
			$rowoutput .= "<tr style='display:none'><td colspan=3>\n";
			$rowoutput .= "<input type='hidden' name='{$type}_curr[" . $id . "]'    value='" . $xpdata->level . "' >\n";
			$rowoutput .= "<input type='hidden' name='{$type}_itemid[" . $id . "]'  value='" . $xpdata->item_id . "' >\n";
			$rowoutput .= "<input type='hidden' name='{$type}_id[" . $id . "]'      value='" . $xpid . "' >\n";
			$rowoutput .= "<input type='hidden' name='{$type}_name[" . $id . "]'    value='" . vtm_formatOutput($xpdata->name) . "' >\n";
			$rowoutput .= "</td></tr>\n";
			
			
			//dots row
			$xpcost = 0;
			$rowoutput .= "<tr><td class='vtmcol_key_wide'><span>(Level {$xpdata->rituallevel}) " . vtm_formatOutput($xpdata->name) . "</span></td>";
			$rowoutput .= "<td class='vtmdot_1 vtmdots'>";
			if ($xpdata->level || $xpdata->level === 0)
				$rowoutput .= "<img alt='*' src='$fulldoturl'>";
			elseif ($xpdata->CHARTABLE_LEVEL)
				$rowoutput .= "<img alt='X' src='$pendingdoturl'>";
			else {
				$xpcost = $xpdata->XP_COST;
				
				if ($xp_avail >= $xpcost) {
					$comment    = vtm_formatOutput("Learn Level {$xpdata->rituallevel} {$xpdata->grp} ritual {$xpdata->name}");
				
					$rowoutput .= "<input type='hidden'   name='{$type}_cost[" . $id . "]'    value='" . $xpcost . "' >";
					$rowoutput .= "<input type='hidden'   name='{$type}_comment[" . $id . "]' value='$comment' >";
					$rowoutput .= "<input type='CHECKBOX' name='{$type}_level[" . $id . "]'   value='{$xpdata->rituallevel}' id='vtmritcb_$id' ";
					if (isset($levelsdata[$id]))
						$rowoutput .= "checked";
					$rowoutput .= "><label for='vtmritcb_$id' title='[ ]'>&nbsp;</label>";
				} else
					$rowoutput .= "<img alt='O' src='$emptydoturl'>";
				
			}
			$rowoutput .= "</td>";
			
				
			$xpcost = ($xpcost) ? $xpcost . "xp" : "";
			if ($xpdata->has_pending)
				$rowoutput .= "<td class='vtmxp_cost'><input class='vtmxp_clear' type='submit' name='{$type}_cancel[{$xpdata->pending_id}]' value='Del'></td>";
			else
				$rowoutput .= "<td class='vtmxp_cost'>$xpcost</td>\n";
			$rowoutput .= "</tr>\n";
			
			$id++;
			
			if ($id == count($allxpdata)) {
				$remaining = $grpcount % $columns;
				if ($remaining)
					$extracols = $columns - $remaining;
			}
		}
	}
	if ($rowoutput != "") {
		$rowoutput .= "</table></td>\n";
		if ($extracols)
			$rowoutput .= "<td class='$colclass' colspan=$extracols>&nbsp;</td>\n";	
		$rowoutput .= "</tr>\n";
	}

	return $rowoutput;
}
function vtm_render_combo_spend_table($type, $allxpdata, $xp_avail) {

	$fulldoturl    = VTM_PLUGIN_URL . '/images/dot1full.' . VTM_ICON_FORMAT;
	$emptydoturl   = VTM_PLUGIN_URL . '/images/dot1empty.' . VTM_ICON_FORMAT;
	$pendingdoturl = VTM_PLUGIN_URL . '/images/dot2.' . VTM_ICON_FORMAT;
	$levelsdata    = isset($_REQUEST[$type . '_level']) ? $_REQUEST[$type . '_level'] : array();

	//$colclass = $columns == 3 ? 'vtm_colnarrow' : 'vtm_colfull';
	//$colclass = 'vtm_colfull';
	$max2display = 1;
	//$colspan = 2 + $max2display;
	$grp = "";
	$col = 0;
	$rowoutput = "";
	if (count($allxpdata)>0) {
		$id = 0;
		foreach ($allxpdata as $xpdata) {
			//$id = $xpdata->id;
			
			// don't display combo-disciplines if you don't have them and you
			// don't meet the pre-requisites
			if ($xpdata->meets_prereq == 'N' && $xpdata->level == 0)
				continue;
			// don't display if you don't have them and it isn't set to be visible
			if ($xpdata->VISIBLE == 'N' && $xpdata->level == 0)
				continue;
			// don't display if they don't have an xp cost
			if ($xpdata->XP_COST == 0)
				continue;
			
			// Hidden fields
			$rowoutput .= "<tr style='display:none'><td colspan=3>\n";
			$rowoutput .= "<input type='hidden' name='{$type}_curr[" . $id . "]'    value='" . $xpdata->level . "' >\n";
			$rowoutput .= "<input type='hidden' name='{$type}_itemid[" . $id . "]'  value='" . $xpdata->item_id . "' >\n";
			$rowoutput .= "<input type='hidden' name='{$type}_id[" . $id . "]'      value='0' >\n";
			$rowoutput .= "<input type='hidden' name='{$type}_name[" . $id . "]'    value='" . vtm_formatOutput($xpdata->name) . "' >\n";
			$rowoutput .= "</td></tr>\n";
						
			//dots row
			$xpcost = 0;
			$rowoutput .= "<tr><td class='vtmcol_key'><span>" . vtm_formatOutput($xpdata->name) . "</span></td>";
			$rowoutput .= "<td class='vtmdot_1 vtmdots'>";
			if ($xpdata->level)
				$rowoutput .= "<img alt='*' src='$fulldoturl'>";
			elseif ($xpdata->CHARTABLE_LEVEL)
				$rowoutput .= "<img alt='X' src='$pendingdoturl'>";
			else {
				$xpcost = $xpdata->XP_COST;
				
				if ($xp_avail >= $xpcost) {
					$comment    = "Learn Combo-Discipline " . vtm_formatOutput($xpdata->name);
				
					$rowoutput .= "<input type='hidden'   name='{$type}_cost[" . $id . "]'    value='" . $xpcost . "' >";
					$rowoutput .= "<input type='hidden'   name='{$type}_comment[" . $id . "]' value='$comment' >";
					$rowoutput .= "<input type='CHECKBOX' name='{$type}_level[" . $id . "]'   value='1' id='vtmcocb_$id' ";
					if (isset($levelsdata[$id]))
						$rowoutput .= "checked";
					$rowoutput .= "><label for='vtmcocb_$id' title='[ ]'>&nbsp;</label>";
				} else
					$rowoutput .= "<img alt='O' src='$emptydoturl'>";
				
			}
			$rowoutput .= "</td>";
	
			$xpcost = ($xpcost) ? $xpcost . "xp" : "";
			if ($xpdata->has_pending)
				$rowoutput .= "<td class='vtmxp_cost'><input class='vtmxp_clear' type='submit' name='{$type}_cancel[{$xpdata->pending_id}]' value='Del'></td>";
			else
				$rowoutput .= "<td class='vtmxp_cost'>$xpcost</td>";
			$rowoutput .= "</tr>\n";
			
			$id++;
		}
	}
	//if ($rowoutput != "")
	//	$rowoutput .= "</table>\n";

	return $rowoutput;
}
function vtm_render_merit_spend_table($type, $list, $allxpdata, $columns, $xp_avail) {

	$levelsdata    = isset($_REQUEST[$type . '_level']) ? $_REQUEST[$type . '_level'] : array();
	$colclass = $columns == 1 ? 'vtm_colfull' : 'vtm_colwide';

	
	$multipleonce = array();
	$colspan = 3;
	$grp = "";
	$col = 0;
	$rowoutput = "";
	$id = 0;
	if (count($list)>0) {
		foreach ($list as $merit) {
		
			// start column / new column
			if (isset($merit->grp)) {
				if ($grp != $merit->grp) {
					if (empty($grp)) {
						$rowoutput .= "<tr><td class='$colclass'><table><tr><th class='$colclass' colspan=$colspan>" . vtm_formatOutput($merit->grp) . "</th></tr>\n";
						$col++;
					} 
					elseif ($col == $columns) {
						$rowoutput .= "</table></td></tr>\n<tr><td class='$colclass'>\n<table>\n<tr><th class='$colclass' colspan=$colspan>" . vtm_formatOutput($merit->grp) . "</th></tr>\n";
						$col = 1;
					}
					else {
						$rowoutput .= "</table>\n</td><td class='$colclass'>\n<table>\n<tr><th class='$colclass' colspan=$colspan>" . vtm_formatOutput($merit->grp) . "</th></tr>\n";
						$col++;
					}
					$grp = $merit->grp;
				}
			}
		
			if (array_key_exists($merit->item_id, $allxpdata)) {
				//loop through array
				foreach ($allxpdata[$merit->item_id] as $xpdatarow) {
					//echo "<li>$id : {$xpdatarow->name} : {$xpdatarow->comment}</li>";
					$rowoutput .= vtm_render_merits_row($type, $id, $xpdatarow, $levelsdata, $xp_avail);
					$id++;
					if ($merit->MULTIPLE == 'Y' 
						&& !array_key_exists($merit->item_id,$multipleonce)
						&& $merit->level >= 0 
						&& $merit->XP_COST > 0
						) {
						//echo "<li>$id : {$merit->name} : {$merit->comment}</li>";
						$multipleonce[$merit->item_id] = 1;
						$rowoutput .= vtm_render_merits_row($type, $id, $merit, $levelsdata, $xp_avail);
						$id++;
					}
				}
			} else {
				//if ($merit->XP_COST > 0)
					//$rowoutput .= "<tr><td><li>$id : {$merit->name} : ({$merit->VISIBLE} / lvl {$merit->level}) {$merit->comment}</li></td><tr>";
				if ($merit->VISIBLE == 'Y' && $merit->level >= 0 && $merit->XP_COST > 0 ) {
					$rowoutput .= vtm_render_merits_row($type, $id, $merit, $levelsdata, $xp_avail);
					$id++;
				}
			}
			
		}
	}
	if ($id == 0)
		$rowoutput = "";
	else
		$rowoutput .= "</table>\n</td></tr>\n";

	return $rowoutput;
}

function vtm_render_merits_row($type, $id, $xpdata, $levelsdata, $xp_avail) {

	$fulldoturl    = VTM_PLUGIN_URL . '/images/dot1full.' . VTM_ICON_FORMAT;
	$emptydoturl   = VTM_PLUGIN_URL . '/images/dot1empty.' . VTM_ICON_FORMAT;
	$pendingdoturl = VTM_PLUGIN_URL . '/images/dot2.' . VTM_ICON_FORMAT;

	$rowoutput = "";
	
	$cha_id = isset($xpdata->id) ? $xpdata->id : '';

	// Hidden fields
	$rowoutput .= "<tr style='display:none'><td colspan=3>\n";
	$rowoutput .= "<input type='hidden' name='{$type}_curr[" . $id . "]'    value='" . $xpdata->level . "' >\n";
	$rowoutput .= "<input type='hidden' name='{$type}_itemid[" . $id . "]'  value='" . $xpdata->item_id . "' >\n";
	$rowoutput .= "<input type='hidden' name='{$type}_id[" . $id . "]'      value='" . $cha_id . "' >\n";
	$rowoutput .= "<input type='hidden' name='{$type}_name[" . $id . "]'    value='" . vtm_formatOutput($xpdata->name) . "' >\n";
	$rowoutput .= "<input type='hidden' name='{$type}_spec_at[" . $id . "]' value='" . $xpdata->has_specialisation . "' >\n";
	$rowoutput .= "<input type='hidden' name='{$type}_spec[" . $id . "]'    value='" . vtm_formatOutput($xpdata->comment) . "' >\n";
	$rowoutput .= "</td></tr>\n";
	
	
	//dots row
	
	$xpcost = $xpdata->XP_COST;
	$rowoutput .= "<tr><td class='vtmcol_key_wide'><span>(Level {$xpdata->level}) " . vtm_formatOutput($xpdata->name);
	if ($xpdata->comment)
		$rowoutput .= " - " . vtm_formatOutput($xpdata->comment);
	$rowoutput .= "</span></td>\n";
	
	$rowoutput .= "<td class='vtmdot_1 vtmdots'>";
	if (isset($xpdata->has_pending) && $xpdata->has_pending)
		$rowoutput .= "<img alt='X' src='$pendingdoturl'>";
	elseif ($xpdata->level < 0) {  // flaw
		if($xpcost) {
			if ($xp_avail >= $xpcost) {
				$comment    = "Buy off level " . ($xpdata->level * -1) . " Flaw {$xpdata->name}";
				$rowoutput .= "<input type='hidden'   name='{$type}_cost[" . $id . "]'    value='" . $xpcost . "' >\n";
				$rowoutput .= "<input type='hidden'   name='{$type}_comment[" . $id . "]' value='$comment' >\n";
				$rowoutput .= "<input type='CHECKBOX' name='{$type}_level[" . $id . "]'   value='{$xpdata->level}' id='vtmmfcb_$id' ";
				if (isset($levelsdata[$id]))
					$rowoutput .= "checked";
				$rowoutput .= "><label for='vtmmfcb_$id' title='[ ]'>&nbsp;</label>";
			}
			else
				$rowoutput .= "<img alt='O' src='$emptydoturl'>";
		} 
	}
	else {
		if ($cha_id) {
			$rowoutput .= "";
		} else {
			if ($xp_avail >= $xpcost) {
				$comment    = "Buy level {$xpdata->level} Merit " . vtm_formatOutput($xpdata->name);
				$rowoutput .= "<input type='hidden'   name='{$type}_cost[" . $id . "]'    value='" . $xpcost . "' >\n";
				$rowoutput .= "<input type='hidden'   name='{$type}_comment[" . $id . "]' value='$comment' >\n";
				$rowoutput .= "<input type='CHECKBOX' name='{$type}_level[" . $id . "]'   value='{$xpdata->level}' id='vtmmfcb_$id' ";
				if (isset($levelsdata[$id]))
					$rowoutput .= "checked";
				$rowoutput .= "><label for='vtmmfcb_$id' title='[ ]'>&nbsp;</label>";
			}
			else
				$rowoutput .= "<img alt='O' src='$emptydoturl'>";
		} 
	}
	$rowoutput .= "</td>";
	$xpcost = ($xpdata->XP_COST) ? $xpdata->XP_COST . "xp" : "";
	if (isset($xpdata->has_pending) && $xpdata->has_pending)
		$rowoutput .= "<td class='vtmxp_cost'><input class='vtmxp_clear' type='submit' name='{$type}_cancel[{$xpdata->pending_id}]' value='Del'></td>\n";
	elseif ($cha_id && $xpdata->level >= 0)
		$rowoutput .= "<td class='vtmxp_cost'></td>\n";
	else
		$rowoutput .= "<td class='vtmxp_cost'>$xpcost</td>\n";
	$rowoutput .= "</tr>\n";

	return $rowoutput;
}

function vtm_get_max_dots($data, $maxRating) {
	$max2display = 5;
	if ($maxRating > 5)
		$max2display = 10;
	else {
		//check what the character has, in case they have the merit that increases
		//something above max 
		if (count($data)) {
			foreach ($data as $row) {
				if (gettype($row) == "array") {
					foreach ($row as $item) {
						if ($item->curr_level > $max2display)
							$max2display = 10;
					}
				} else {
					if ($row->curr_level > $max2display)
						$max2display = 10;
				}
			}
		}
	}
	return $max2display;
}

function vtm_pending_level ($pendingdata, $chartableid, $itemid) {

	$result = 0;
		
	if ($chartableid != 0) {
		foreach ($pendingdata as $row)
			if ($row->CHARTABLE_ID == $chartableid) {
				$result = $row->CHARTABLE_LEVEL;
				break;
			}
	} else {
		foreach ($pendingdata as $row)
			if ($row->ITEMTABLE_ID == $itemid && $row->CHARTABLE_ID == 0) {
				$result = $row->CHARTABLE_LEVEL;
				break;
			}
	}
	
	return $result;

}
function vtm_pending_id ($pendingdata, $chartableid, $itemid) {

	$result = 0;
		
	if ($chartableid != 0) {
		foreach ($pendingdata as $row)
			if ($row->CHARTABLE_ID == $chartableid) {
				$result = $row->ID;
				break;
			}
	} else {
		foreach ($pendingdata as $row)
			if ($row->ITEMTABLE_ID == $itemid && $row->CHARTABLE_ID == 0) {
				$result = $row->ID;
				break;
			}
	}
	
	return $result;

}

function vtm_pending_training ($pendingdata, $chartableid, $itemid) {

	$result = 0;
		
	if ($chartableid != 0) {
		foreach ($pendingdata as $row)
			if ($row->CHARTABLE_ID == $chartableid) {
				$result = $row->TRAINING_NOTE;
				break;
			}
	} else {
		foreach ($pendingdata as $row)
			if ($row->ITEMTABLE_ID == $itemid && $row->CHARTABLE_ID == 0) {
				$result = $row->TRAINING_NOTE;
				break;
			}
	}
	
	return $result;

}

function vtm_calc_submitted_spend($type) {
	$spend = 0;

	$costs =  $_REQUEST[$type . '_cost'];

	foreach ($_REQUEST[$type . '_level'] as $id => $level) {
		if (!empty($level))
			$spend += $costs[$id];
	}
	//print_r($costs);
	//print_r($_REQUEST[$type . '_level']);
	
	return $spend;
	
}

function vtm_render_ritual_row($xpdata, $pending, $levelsdata,
						$training, $trainerrors, $traindefault, $max2display,
						$pendingdoturl) {

	$id = $xpdata->item_id;

	$output = "";
    $output .= "<tr style='display:none'><td>\n";
	$output .= "<input type='hidden' name='ritual_curr[" . $id . "]' value='" . $xpdata->level . "' >\n";
	$output .= "<input type='hidden' name='ritual_itemid[" . $id . "]' value='" . $xpdata->item_id . "' >\n";
	$output .= "<input type='hidden' name='ritual_new[" . $id . "]' value='" . ($xpdata->id == 0) . "' >\n";
    $output .= "</td></tr>\n";
    $output .= "<tr>\n";
	// Name
	$output .= "<td colspan=2 class='vtmcol_key'>" . vtm_formatOutput($xpdata->name) . "\n";
	$output .= " </th>\n";

	$pendinglvl = vtm_pending_level($pending, $xpdata->id, $xpdata->item_id);

	$radiogroup = "ritual_level[" . $id . "]";
	$radiovalue = $xpdata->level;

	for ($i=1;$i<=$max2display;$i++){
		if ($i == $xpdata->level) {
			if ($pendinglvl) {
				$output .= "<td class='gvxp_dot'><img alt='X' src='$pendingdoturl'></td><td>&nbsp;</td>";
			} else {
				$output .= "<td class='gvxp_radio'><input type='RADIO' name='$radiogroup' value='$radiovalue' ";
				if (isset($levelsdata[$id]))
					$output .= "checked";
				$output .= ">";
				
				$output .= "<input type='hidden' name='ritual_cost_" . $i . "[" . $id . "]' value='" . $xpdata->cost . "' >";
				$output .= "<input type='hidden' name='ritual_comment_" . $i . "[" . $id . "]' value='Learn Ritual " . vtm_formatOutput($xpdata->name) . "' ></td>";
				$output .= "</td>";

				$output .= "<td>" . $xpdata->cost . "</td>";
			}
		} else {
			$output .= "<td>&nbsp;</td><td>&nbsp;</td>";
		}
	}

	if ($pendinglvl)
		$output .= "<td class='gvxp_radio'>&nbsp;</td>"; // no change dot
	else
		$output .= "<td class='gvxp_radio'><input type='RADIO' name='ritual_level[{$id}]' value='0' selected='selected'></td>"; // no change dot 
	
	// no training note if you cannot buy
	$trainingString = isset($training[$id]) ? $training[$id] : $traindefault;
	$output .= "<td";
	if ($trainerrors[$id])
		$output .= " class='vtmcol_error'";
	if ($pendinglvl)
		$output .= ">" . vtm_pending_training($pending, $xpdata->id, $xpdata->item_id) . "</td></tr></td>";
	else
		$output .= "><input type='text'  name='ritual_training[" . $id . "]' value='" . $trainingString ."' size=30 maxlength=160 /></td></tr></td>";


	
	return $output;
}

function vtm_save_to_pending($data, $details, $playerID, $characterID) {
	global $wpdb;

	$newid = "";
	
	$index       = $data[0];
	$itemtable   = $data[1];
	$itemid      = $data[2];
	$chartable   = $data[3];
	$chartableid = $data[4];
	$xpcost      = $data[5];
	$spec        = isset($details['spec']) ? $details['spec'] : '';
	$level       = isset($details['level']) ? $details['level'] : 0;
	$training    = isset($details['training']) ? $details['training'] : '';
	$itemidname  = isset($details['name']) ? $details['name'] : '';
	$comment     = isset($details['detail']) ? $details['detail'] : '';

	// print "<p>$index: <br>";
	// print_r($data);
	// print "<br>";
	// print_r($details);
	// print "<p>";
			
	//if ($level) {
		$dataarray = array (
			'PLAYER_ID'       => $playerID,
			'CHARACTER_ID'    => $characterID,
			'CHARTABLE'       => $chartable,
			'CHARTABLE_ID'    => $chartableid,
			'CHARTABLE_LEVEL' => $level,
			'AWARDED'         => Date('Y-m-d'),
			'AMOUNT'          => $xpcost * -1,
			'COMMENT'         => trim($comment),
			'SPECIALISATION'  => trim($spec),
			'TRAINING_NOTE'   => trim($training),
			'ITEMTABLE'       => $itemtable,
			'ITEMNAME'        => $itemidname,
			'ITEMTABLE_ID'    => $itemid
		);
		
		//print_r($dataarray);
		
		$wpdb->insert(VTM_TABLE_PREFIX . "PENDING_XP_SPEND",
					$dataarray,
					array (
						'%d',
						'%d',
						'%s',
						'%d',
						'%d',
						'%s',
						'%d',
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
						'%d'
					)
				);
		
		$newid = $wpdb->insert_id;
		if ($newid  == 0) {
			echo "<p style='color:red'><b>Error:</b> XP Spend failed for data (";
			print_r($dataarray);
			print $wpdb->last_error;
			echo ")</p>";
		} 
	//}
	
	
	return $newid;
							
}

function vtm_save_merit_to_pending ($type, $table, $itemtable, $itemidname, $playerID, $characterID) {
	global $wpdb;
	
	$wpdb->show_errors(); 

	$newid = "";

	$ids             = $_REQUEST[$type . '_id'];
	$levels          = $_REQUEST[$type . '_level'];
	$specialisations = $_REQUEST[$type . '_spec'];
	$training        = $_REQUEST[$type . '_training'];
	$itemid          = $_REQUEST[$type . '_itemid'];
	$costlvls        = $_REQUEST[$type . '_cost'];
	$comments        = $_REQUEST[$type . '_comment'];
	
	//print_r($_REQUEST);
	//echo "<pre>";
	//print_r($itemid);
	//echo "</pre>";
	
	foreach ($levels as $index => $level) {
		
		if ($level) {
			$dataarray = array (
				'PLAYER_ID'       => $playerID,
				'CHARACTER_ID'    => $characterID,
				'CHARTABLE'       => $table,
				'CHARTABLE_ID'    => $ids[$index],
				'CHARTABLE_LEVEL' => $level,
				'AWARDED'         => Date('Y-m-d'),
				'AMOUNT'          => $costlvls[$index] * -1,
				'COMMENT'         => $comments[$index],
				'SPECIALISATION'  => $specialisations[$index],
				'TRAINING_NOTE'   => $training[$index],
				'ITEMTABLE'       => $itemtable,
				'ITEMNAME'        => $itemidname,
				'ITEMTABLE_ID'    => $itemid[$index]
			);
			
			$wpdb->insert(VTM_TABLE_PREFIX . "PENDING_XP_SPEND",
						$dataarray,
						array (
							'%d',
							'%d',
							'%s',
							'%d',
							'%d',
							'%s',
							'%d',
							'%s',
							'%s',
							'%s',
							'%s',
							'%s',
							'%d'
						)
					);
			$wpdb->print_error();
			$newid = $wpdb->insert_id;
			if ($newid  == 0) {
				echo "<p style='color:red'><b>Error:</b> XP Spend failed for data (";
				print_r($dataarray);
				$wpdb->print_error() .
				$wpdb->last_error .
				$wpdb->last_query;
			} 
		}
	
	}	
	
	return $newid;
							
}

function vtm_get_pending_xp($playerID = 0, $characterID = 0) {
	global $wpdb;
	global $vtmglobal;
		
	if ($vtmglobal['config']->ASSIGN_XP_BY_PLAYER == 'Y' && $playerID == 0 & $characterID != 0) {
		$sql = $wpdb->prepare("SELECT PLAYER_ID FROM " . VTM_TABLE_PREFIX . "CHARACTER WHERE ID = %s", $characterID);
		$playerID = $wpdb->get_var($sql);
	}

	if ($vtmglobal['config']->ASSIGN_XP_BY_PLAYER == 'N') {
		$sql = "SELECT SUM(AMOUNT) as COST FROM " . VTM_TABLE_PREFIX . "PENDING_XP_SPEND
				WHERE CHARACTER_ID = %s";
		$sql = $wpdb->prepare($sql, $characterID);
		$result = $wpdb->get_results($sql);
		//echo "<p>SQL: $sql</p>";
		//print_r($result);
	} else {
		$sql = "SELECT SUM(pending.AMOUNT) as COST
				FROM 
					" . VTM_TABLE_PREFIX . "PENDING_XP_SPEND pending,
					" . VTM_TABLE_PREFIX . "CHARACTER ch
				WHERE
					ch.ID = pending.CHARACTER_ID
					AND ch.PLAYER_ID = %s";
		$sql = $wpdb->prepare($sql, $playerID);
		//echo "<p>SQL: $sql</p>";
		$result = $wpdb->get_results($sql);
	}
	
	$xp_pending = $result[0]->COST * -1;
	
	return $xp_pending;
}

function vtm_establishPrivateClanID($characterID) {
	global $wpdb;
	
	$sql = "SELECT PRIVATE_CLAN_ID FROM " . VTM_TABLE_PREFIX . "CHARACTER WHERE ID = %s";
	$sql = $wpdb->prepare($sql, $characterID);
	$result = $wpdb->get_results($sql);
	
	return $result[0]->PRIVATE_CLAN_ID;
}

function vtm_cancel_pending($data) {
	global $wpdb;
	
	foreach ($data as $pendingid => $button) {
		$sql = "DELETE FROM " . VTM_TABLE_PREFIX . "PENDING_XP_SPEND
				WHERE ID = %d";
		$sql = $wpdb->prepare($sql, $pendingid);
		$result = $wpdb->get_results($sql);
	}
}

function vtm_validate_spends($playerID, $characterID, $docancel) {

	$xp_total   = vtm_get_total_xp($playerID, $characterID);
	$xp_pending = vtm_get_pending_xp($playerID, $characterID);
	$xp_spent    = 0;
	$outputError = "";
	
	foreach ($_REQUEST as $spend => $level) {
		$data = explode(":",$spend);
		if (count($data) > 1) {
			$xp_spent += $data[5];
		}
	}
	
	/*
	if (isset($_REQUEST['stat_level'])) $xp_spent += vtm_calc_submitted_spend('stat');
	if (isset($_REQUEST['skill_level'])) $xp_spent += vtm_calc_submitted_spend('skill');
	if (isset($_REQUEST['disc_level'])) $xp_spent += vtm_calc_submitted_spend('disc');
	if (isset($_REQUEST['combo_level'])) $xp_spent += vtm_calc_submitted_spend('combo');
	if (isset($_REQUEST['path_level'])) $xp_spent += vtm_calc_submitted_spend('path');
	if (isset($_REQUEST['ritual_level'])) $xp_spent += vtm_calc_submitted_spend('ritual');
	if (isset($_REQUEST['merit_level'])) $xp_spent += vtm_calc_submitted_spend('merit');
	*/
	if ($xp_spent > ($xp_total - $xp_pending)) {
		$outputError .= "<p>You don't have enough experience left</p>";
	}
	
	if (!$xp_spent)
		if ($docancel)
			$outputError .= "<p>Experience spend has been cleared</p>";
		else
			$outputError .= "<p>You have not spent any experience</p>";
	

	return $outputError;
	
}

function vtm_validate_details($characterID) {
	global $wpdb;

	
	// Extract the spends and what tables we need to query from
	// the $_REQUEST
	$requestItemTables = array();
	$requestChTables = array();
	$requestspends = array();
	foreach ($_REQUEST as $spend => $details) {
		$data = explode(":",$spend);
		if (count($data) > 1) {
			$requestspends[$spend] = $details;
			$requestItemTables[$data[1]] = 1;
			if ($data[4] != 0) {
				$requestChTables[$data[3]] = 1;
			}
		}
	}	
	//print "<br>";
	//print_r($requestspends);
	//print "<br>";
	
	
	// Query the Item Table information
	$itemInfo = array();
	foreach ($requestItemTables as $itemtable => $discard) {
		//Table has SPECIALISATION_AT column?
		$columns = "";
		$existing_columns = $wpdb->get_col("DESC " . VTM_TABLE_PREFIX . $itemtable, 0);

		$match_columns = array_intersect(array("SPECIALISATION_AT"), $existing_columns);
		$columns = empty($match_columns) ? "" : ",SPECIALISATION_AT";
		$match_columns = array_intersect(array("HAS_SPECIALISATION"), $existing_columns);
		$columns .= empty($match_columns) ? "" : ",HAS_SPECIALISATION";

		$itemInfo[$itemtable] = $wpdb->get_results("SELECT ID,NAME $columns FROM " . VTM_TABLE_PREFIX . $itemtable, OBJECT_K);
		
	}

	// Query the character table
	$charTableInfo = array();
	foreach ($requestChTables as $chartable => $discard) {
		$sql = $wpdb->prepare("SELECT ID,COMMENT,LEVEL FROM " . VTM_TABLE_PREFIX . $chartable . " WHERE CHARACTER_ID = '%s'",$characterID);
		//print "SQL: $sql<br>";
		$charTableInfo[$chartable] = $wpdb->get_results($sql, OBJECT_K);
	}

	$outputError = "";
	foreach ($requestspends as $key => $spend) {

			$data        = explode(":",$key);
			$index       = $data[0];
			$itemtable   = $data[1];
			$itemid      = $data[2];
			$chartable   = $data[3];
			$chartableid = $data[4];
			$xp_cost     = $data[5];
			
			if (isset($spend['spec']) && empty($spend['spec'])) {
				$outputError .= "<li>Missing specialisation for {$itemInfo[$itemtable][$itemid]->NAME}</li>";
			}
			if (isset($spend['training']) && empty(($spend['training']))) {
				$outputError .= "<li>Missing training information for {$itemInfo[$itemtable][$itemid]->NAME}";
				if (isset($spend['spec']) && !empty($spend['spec'])) {
					$outputError .= " ({$spend['spec']})";
				}
				$outputError .= "</li>";
			}
	}
	if (!empty($outputError)) {
		$outputError = "<ul>$outputError</ul>";
	}
	
	/*
	$defaultTrainingString = "";
	$defaultSpecialisation = "";
	
	if (isset($_REQUEST['stat_level'])) {
		$spec_at      = $_REQUEST['stat_spec_at'];
		$statlevels   = $_REQUEST['stat_level'];
		$stattraining = $_REQUEST['stat_training'];
		$stat_spec_error = array();
		$stat_train_error = array();
		
		if (isset($_REQUEST['stat_spec'])) {
			$stat_specialisations = $_REQUEST['stat_spec'];
			foreach ($stat_specialisations as $id => $specialisation) {
				if ($spec_at[$id] <= $statlevels[$id] &&
					($specialisation == "" || $specialisation == $defaultSpecialisation)) {
					$stat_spec_error[$id] = 1;
				}
			}
		}
		
		if (count($stat_spec_error))
			$outputError .= "<p>Please fix missing or invalid <a href='#gvid_xpst_stat'>Attribute</a> specialisations</p>";
			
		foreach ($stattraining as $id => $trainingnote) {
			if ($statlevels[$id] && ($trainingnote == "" || $trainingnote == $defaultTrainingString)) {
				$stat_train_error[$id] = 1;
			}
		}
		if (count($stat_train_error))
			$outputError .= "<p>Please fix missing <a href='#gvid_xpst_stat'>Attribute</a> training notes</p>";

	}

	if (isset($_REQUEST['skill_level'])) {
		$spec_at      = $_REQUEST['skill_spec_at'];
		$skilllevels   = $_REQUEST['skill_level'];
		$skilltraining = $_REQUEST['skill_training'];
		$skill_specialisations = $_REQUEST['skill_spec'];
		$skill_spec_error = array();
		$skill_train_error = array();
		
		if (isset($skill_specialisations))
			foreach ($skill_specialisations as $id => $specialisation) {
				if ($spec_at[$id] <= $skilllevels[$id] &&
					($specialisation == "" || $specialisation == $defaultSpecialisation)) {
					$skill_spec_error[$id] = 1;
				}
			}
		
		if (count($skill_spec_error))
			$outputError .= "<p>Please fix missing or invalid <a href='#gvid_xpst_skill'>Ability</a> specialisations</p>";
			
		foreach ($skilltraining as $id => $trainingnote) {
			if ($skilllevels[$id] &&
				($trainingnote == "")) {
				$skill_train_error[$id] = 1;
			}
		}
		if (count($skill_train_error))
			$outputError .= "<p>Please fix missing <a href='#gvid_xpst_skill'>Ability</a> training notes</p>";

	}
	if (isset($_REQUEST['disc_level'])) {
		$levels   = $_REQUEST['disc_level'];
		$training = $_REQUEST['disc_training'];
		$disc_train_error = array();
					
		foreach ($training as $id => $trainingnote) {
			if ($levels[$id] &&
				($trainingnote == "")) {
				$disc_train_error[$id] = 1;
			}
		}
		if (count($disc_train_error))
			$outputError .= "<p>Please fix missing <a href='#gvid_xpst_disc'>Discipline</a> training notes</p>";

	}
	if (isset($_REQUEST['path_level'])) {
		$levels   = $_REQUEST['path_level'];
		$training = $_REQUEST['path_training'];
		$path_train_error = array();
					
		foreach ($training as $id => $trainingnote) {
			if ($levels[$id] &&
				($trainingnote == "")) {
				$path_train_error[$id] = 1;
			}
		}
		if (count($path_train_error))
			$outputError .= "<p>Please fix missing <a href='#gvid_xpst_path'>Path</a> training notes</p>";

	}
	if (isset($_REQUEST['ritual_level'])) {
		$levels   = $_REQUEST['ritual_level'];
		$training = $_REQUEST['ritual_training'];
		$ritual_train_error = array();
					
		foreach ($training as $id => $trainingnote) {
			if ($levels[$id] &&
				($trainingnote == "")) {
				$ritual_train_error[$id] = 1;
			}
		}
		if (count($ritual_train_error))
			$outputError .= "<p>Please fix missing <a href='#gvid_xpst_ritual'>Ritual</a> training notes</p>";

	}
	if (isset($_REQUEST['merit_level'])) {
		$levels   = $_REQUEST['merit_level'];
		$training = $_REQUEST['merit_training'];
		$has_spec = $_REQUEST['merit_spec_at'];
		$specialisations = isset($_REQUEST['merit_spec']) ? $_REQUEST['merit_spec'] : array();
		$merit_spec_error = array();
		$merit_train_error = array();
					
		if (isset($specialisations))
			foreach ($specialisations as $id => $specialisation) {
				if ($has_spec[$id] &&
					($specialisation == "")) {
					$merit_spec_error[$id] = 1;
				}
			}
		
		if (count($merit_spec_error))
			$outputError .= "<p>Please fix missing or invalid <a href='#gvid_xpst_merit'>Merit of Flaw</a> specialisations</p>";

		foreach ($training as $id => $trainingnote) {
			if ($levels[$id] &&
				($trainingnote == "")) {
				$merit_train_error[$id] = 1;
			}
		}
		if (count($merit_train_error))
			$outputError .= "<p>Please fix missing <a href='#gvid_xpst_ritual'>Merit or Flaw</a> training notes</p>";

	}

	*/
	return $outputError;
	
}

?>