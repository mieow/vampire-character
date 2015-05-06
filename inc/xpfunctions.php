<?php

function vtm_xp_spend_content_filter($content) {

  if (is_page(vtm_get_stlink_page('viewXPSpend')) && is_user_logged_in()) {
    $content .= vtm_print_xp_spend_table();
  }
  // otherwise returns the database content
  return $content;
}
add_filter( 'the_content', 'vtm_xp_spend_content_filter' );

/*
	Called by print_xp_spend_table
*/
function vtm_doPendingXPSpend($character) {
	global $wpdb;
	$characterID = vtm_establishCharacterID($character);
	$playerID    = vtm_establishPlayerID($character);
	
	$submitted = array();
	if (isset($_REQUEST['stat_level'])) {
		$submitted[] = "Statistics";
		$newid = vtm_save_to_pending('stat', 'CHARACTER_STAT', 'STAT', 'STAT_ID', $playerID, $characterID);
	}
	if (isset($_REQUEST['skill_level'])) {
		$submitted[] = "Abilities";
		$newid = vtm_save_to_pending('skill', 'CHARACTER_SKILL', 'SKILL', 'SKILL_ID', $playerID, $characterID);
	}
	if (isset($_REQUEST['disc_level'])) {
		$submitted[] = "Disciplines";
		$newid = vtm_save_to_pending('disc', 'CHARACTER_DISCIPLINE', 'DISCIPLINE', 'DISCIPLINE_ID', $playerID, $characterID);
	}
	if (isset($_REQUEST['path_level'])) {
		$submitted[] = "Paths";
		$newid = vtm_save_to_pending('path', 'CHARACTER_PATH', 'PATH', 'PATH_ID', $playerID, $characterID);
	}
	if (isset($_REQUEST['ritual_level'])) {
		$submitted[] = "Rituals";
		$newid = vtm_save_to_pending('ritual', 'CHARACTER_RITUAL', 'RITUAL', 'RITUAL_ID', $playerID, $characterID);
	}
	if (isset($_REQUEST['merit_level'])) {
		$submitted[] = "Merits";
		$newid = vtm_save_to_pending('merit', 'CHARACTER_MERIT', 'MERIT', 'MERIT_ID', $playerID, $characterID);
	}
	if (isset($_REQUEST['combo_level'])) {
		$submitted[] = "Combination Disciplines";
		$newid = vtm_save_to_pending('combo', 'CHARACTER_COMBO_DISCIPLINE', 'COMBO_DISCIPLINE', 'COMBO_DISCIPLINE_ID', $playerID, $characterID);
	}
	
	if (count($submitted) > 0) {
		$email = get_option( 'vtm_replyto_address', get_option( 'vtm_chargen_email_from_address', get_bloginfo('admin_email') ) );
		$body = "A user has submitted experience spends.\n\nView the spends here: " .
			admin_url('admin.php?page=vtmcharacter-xp');
		vtm_send_email($email, "Experience spends have been submitted", $body);
	}
}
	
/*
	master_xp_update VTM_FORM
*/
function vtm_handleMasterXP() {
	$counter = 1;
	while (isset($_POST['counter_' . $counter])) {
		$current_player_id = $_POST['counter_' . $counter];
		$current_xp_value  = $_POST[$current_player_id . '_xp_value'];
		if (is_numeric($current_xp_value) && ((int) $current_xp_value != 0)) {
			vtm_addPlayerXP($current_player_id,
				$_POST[$current_player_id . '_character'],
				$_POST[$current_player_id . '_xp_reason'],
				$current_xp_value,
				$_POST[$current_player_id . '_xp_comment']);
		}
		$counter++;
	}
}
	
/* Add XP to the database
	- called by handleMasterXP
	- and handleGVLarpForm
 */

/* shortcode */

function vtm_print_xp_spend_table() {
	global $vtmglobal;
	
	$character   = vtm_establishCharacter('');
	$characterID = vtm_establishCharacterID($character);
	$playerID    = vtm_establishPlayerID($character);
	
	$output = "<div class='gvplugin vtmpage_" . $vtmglobal['config']->WEB_PAGEWIDTH . "' >";
	$outputError = "";
	$step = isset($_REQUEST['step']) ? $_REQUEST['step'] : '';
	
	// Cancel Spends
	$docancel = (isset($_REQUEST['stat_cancel']) 
				|| isset($_REQUEST['skill_cancel'])
				|| isset($_REQUEST['disc_cancel'])
				|| isset($_REQUEST['combo_cancel'])
				|| isset($_REQUEST['path_cancel'])
				|| isset($_REQUEST['ritual_cancel'])
				|| isset($_REQUEST['merit_cancel'])
				);
	if (isset($_REQUEST['stat_cancel']))    vtm_cancel_pending($_REQUEST['stat_cancel']);
	if (isset($_REQUEST['skill_cancel']))   vtm_cancel_pending($_REQUEST['skill_cancel']);
	if (isset($_REQUEST['disc_cancel']))    vtm_cancel_pending($_REQUEST['disc_cancel']);
	if (isset($_REQUEST['combo_cancel']))   vtm_cancel_pending($_REQUEST['combo_cancel']);
	if (isset($_REQUEST['path_cancel']))    vtm_cancel_pending($_REQUEST['path_cancel']);
	if (isset($_REQUEST['ritual_cancel']))  vtm_cancel_pending($_REQUEST['ritual_cancel']);
	if (isset($_REQUEST['merit_cancel']))   vtm_cancel_pending($_REQUEST['merit_cancel']);
	
	// Back button
	if (isset($_REQUEST['xCancel']) || $docancel) $step = "";
		
	/* VALIDATE SPENDS */
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

	$output .= "</div>";
	return $output;
}

function vtm_render_supply_details($character) {

	$output = "";
	$character   = vtm_establishCharacter($character);
	$characterID = vtm_establishCharacterID($character);

	$spent = 0;
	if (isset($_REQUEST['stat_level']))   $spent += vtm_calc_submitted_spend('stat');
	if (isset($_REQUEST['skill_level']))  $spent += vtm_calc_submitted_spend('skill');
	if (isset($_REQUEST['disc_level']))   $spent += vtm_calc_submitted_spend('disc');
	if (isset($_REQUEST['combo_level']))  $spent += vtm_calc_submitted_spend('combo');
	if (isset($_REQUEST['path_level']))   $spent += vtm_calc_submitted_spend('path');
	if (isset($_REQUEST['ritual_level'])) $spent += vtm_calc_submitted_spend('ritual');
	if (isset($_REQUEST['merit_level']))  $spent += vtm_calc_submitted_spend('merit');
	
	$output .= "<p>Spending $spent experience points.</p>\n";
	$output .= "<p>Please enter specialisations, if available, and enter a description of your learning method</p>";
	
	$output .= "<div class='gvplugin' id='vtmid_xpst'>\n";
	$output .= "<form name='SPEND_XP_FORM' method='post' action='" . $_SERVER['REQUEST_URI'] . "'>\n";
	
	if (isset($_REQUEST['stat_level'])) {
		$output .= vtm_render_details_section('stat');
	}
	if (isset($_REQUEST['skill_level'])) {
		$output .= vtm_render_details_section('skill');
	}
	if (isset($_REQUEST['disc_level'])) {
		$output .= vtm_render_details_section('disc');
	}
	if (isset($_REQUEST['combo_level'])) {
		$output .= vtm_render_details_section('combo');
	}
	if (isset($_REQUEST['path_level'])) {
		$output .= vtm_render_details_section('path');
	}
	if (isset($_REQUEST['ritual_level'])) {
		$output .= vtm_render_details_section('ritual');
	}
	if (isset($_REQUEST['merit_level'])) {
		$output .= vtm_render_details_section('merit');
	}
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

	$character   = vtm_establishCharacter($character);
	$characterID = vtm_establishCharacterID($character);
	$playerID    = vtm_establishPlayerID($character);
	
	$xp_total      = vtm_get_total_xp($playerID, $characterID);
	$xp_pending    = vtm_get_pending_xp($playerID, $characterID);
	$xp_avail      = $xp_total - $xp_pending;
	$fulldoturl    = plugins_url( 'vtm-character/images/dot1full.jpg' );
	$emptydoturl   = plugins_url( 'vtm-character/images/dot1empty.jpg' );
	$pendingdoturl = plugins_url( 'vtm-character/images/dot2.jpg' );
	
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

	/* work out the maximum ratings for this character based on generation */
	$ratings = vtm_get_character_maximums($characterID);
	$maxRating = $ratings[0];
	$maxDiscipline = $ratings[1];
	
	/* get the current pending spends for this character */
	$pendingSpends = vtm_get_pending($characterID);
	
	$sectioncontent['stat']   = vtm_render_stats($characterID, $maxRating, $pendingSpends, $xp_avail);
	$sectioncontent['skill']  = vtm_render_skills($characterID, $maxRating, $pendingSpends, $xp_avail);
	$sectioncontent['disc']   = vtm_render_disciplines($characterID, $maxDiscipline, $pendingSpends, $xp_avail);
	$sectioncontent['combo']  = vtm_render_combo($characterID, $pendingSpends, $xp_avail);
	$sectioncontent['path']   = vtm_render_paths($characterID, 5, $pendingSpends, $xp_avail);
	$sectioncontent['ritual'] = vtm_render_rituals($characterID, 5, $pendingSpends, $xp_avail);
	$sectioncontent['merit']  = vtm_render_merits($characterID, $pendingSpends, $xp_avail);
	
	
	/* DISPLAY TABLES 
	-------------------------------*/
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

	/* clan cost model */
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
	/* echo "<pre>\nSQL2: $clansql\n";
	print_r($result);
	echo "</pre>"; */
	
	/* non-clan cost model */
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
		/* echo "<pre>SQL1: $nonsql\n";
		print_r($result);
		echo "</pre>";  */
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

function vtm_render_details_section($type) {
	
	$output = "";
	$rowoutput = "";
	
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

	if (!empty($rowoutput)) {
		$output .= "<table>\n";
		$output .= "<tr><th class='gvthead'>Name</th><th class='gvthead'>Specialisation</th><th class='gvthead'>Experience Spend</th><th class='gvthead'>XP Cost</th><th class='gvthead'>Training Note/Learning Method</th></tr>";
		$output .= "$rowoutput\n";
		$output .= "</table>\n";
	} 
	
	return $output;
}
function vtm_render_stats($characterID, $maxRating, $pendingSpends, $xp_avail) {
	global $wpdb;
	global $vtmglobal;
	
	$output = "";
	
	$sql = "SELECT 
				stat.name, 
				cha_stat.level,
				cha_stat.comment,
				cha_stat.id, 
				stat.specialisation_at spec_at,
				stat.ID as item_id, 
				stat.GROUPING as grp,
				pendingspend.CHARTABLE_LEVEL,
				steps.XP_COST,
				steps.NEXT_VALUE,
				NOT(ISNULL(CHARTABLE_LEVEL)) as has_pending, 
				pendingspend.ID as pending_id
			FROM " . VTM_TABLE_PREFIX . "CHARACTER_STAT cha_stat
				LEFT JOIN 
					(SELECT ID, CHARTABLE_LEVEL, CHARTABLE_ID
					FROM
						" . VTM_TABLE_PREFIX . "PENDING_XP_SPEND pending
					WHERE 
						pending.CHARACTER_ID = %s
						AND pending.CHARTABLE = 'CHARACTER_STAT'
					) as pendingspend
				ON
					pendingspend.CHARTABLE_ID = cha_stat.id,
				 " . VTM_TABLE_PREFIX . "STAT stat,
				 " . VTM_TABLE_PREFIX . "COST_MODEL_STEP steps,
				 " . VTM_TABLE_PREFIX . "COST_MODEL models
			WHERE 
				cha_stat.STAT_ID      = stat.ID
				AND steps.COST_MODEL_ID = models.ID
				AND stat.COST_MODEL_ID = models.ID
				AND steps.CURRENT_VALUE = cha_stat.level
				AND cha_stat.CHARACTER_ID = %s
		   ORDER BY stat.ordering";
	$sql = $wpdb->prepare($sql, $characterID,$characterID);
	//echo "<p>SQL: $sql</p>";
	$character_stats_xp = $wpdb->get_results($sql);
		
	$rowoutput = vtm_render_spend_table('stat', $character_stats_xp, $maxRating, $vtmglobal['config']->WEB_COLUMNS, $xp_avail);
	
	if (!empty($rowoutput)) {
		$output .= "<table>\n";
		$output .= "$rowoutput\n";
		$output .= "</table>\n";
	} 

	return $output;

}
function vtm_render_skills($characterID, $maxRating, $pendingSpends, $xp_avail) {
	global $wpdb;
	global $vtmglobal;
	
	$output = "";
	
	/* All the skills currently had, with pending
		plus all the pending skills not already had
		
		Then list all the available skills to buy, current level, pending and new level
	*/
	
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

	$fulldoturl    = plugins_url( 'vtm-character/images/dot1full.jpg' );
	$emptydoturl   = plugins_url( 'vtm-character/images/dot1empty.jpg' );
	$pendingdoturl = plugins_url( 'vtm-character/images/dot2.jpg' );

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

function vtm_reformat_skills_xp ($input) {

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


function vtm_render_disciplines($characterID, $maxRating, $pendingSpends, $xp_avail) {
	global $wpdb;
	global $vtmglobal;
	
	$output = "";
	
	$sql = "SELECT
				disc.name,
				clans.name as clanname,
				NOT(ISNULL(clandisc.DISCIPLINE_ID)) as isclan,
				IF(ISNULL(clandisc.DISCIPLINE_ID),'Non-Clan Discipline','Clan Discipline') as grp,
				cha_disc.level,
				cha_disc.ID as id,
				disc.ID as item_id,
				pendingspend.CHARTABLE_LEVEL,
				IF(ISNULL(clandisc.DISCIPLINE_ID),nonclansteps.XP_COST,clansteps.XP_COST) as XP_COST,
				IF(ISNULL(clandisc.DISCIPLINE_ID),nonclansteps.NEXT_VALUE,clansteps.NEXT_VALUE) as NEXT_VALUE,
				NOT(ISNULL(CHARTABLE_LEVEL)) as has_pending,
				pendingspend.ID as pending_id
			FROM
				" . VTM_TABLE_PREFIX . "DISCIPLINE disc
				LEFT JOIN
					(SELECT ID, LEVEL, CHARACTER_ID, DISCIPLINE_ID
					FROM
						" . VTM_TABLE_PREFIX . "CHARACTER_DISCIPLINE
					WHERE
						CHARACTER_ID = %s
					) cha_disc
				ON
					cha_disc.DISCIPLINE_ID = disc.ID
				LEFT JOIN
					(SELECT ID, CHARTABLE_LEVEL, CHARTABLE_ID, ITEMTABLE_ID
					FROM
						" . VTM_TABLE_PREFIX . "PENDING_XP_SPEND pending
					WHERE	
						pending.CHARACTER_ID = %s
						AND pending.CHARTABLE = 'CHARACTER_DISCIPLINE'
					) as pendingspend
				ON
					pendingspend.ITEMTABLE_ID = disc.id
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
					clandisc.DISCIPLINE_ID = disc.id
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
					OR disc.VISIBLE = 'Y' 
					OR NOT(ISNULL(cha_disc.level)))
				AND (
					(ISNULL(cha_disc.LEVEL) AND clansteps.CURRENT_VALUE = 0)
					OR clansteps.CURRENT_VALUE = cha_disc.level
				)
				AND (
					(ISNULL(cha_disc.LEVEL) AND nonclansteps.CURRENT_VALUE = 0)
					OR nonclansteps.CURRENT_VALUE = cha_disc.level
				)
			ORDER BY grp, disc.name";
	$sql = $wpdb->prepare($sql, $characterID,$characterID,$characterID,$characterID);
    //echo "<p>SQL: $sql</p>";
	$character_data = $wpdb->get_results($sql);
		
	$rowoutput = vtm_render_spend_table('disc', $character_data, $maxRating, $vtmglobal['config']->WEB_COLUMNS, $xp_avail);
	
	if (!empty($rowoutput)) {
		$output .= "<table>\n";
		$output .= "$rowoutput\n";
		$output .= "</table>\n";
	} 

	return $output;

}
function vtm_render_paths($characterID, $maxRating, $pendingSpends, $xp_avail) {
	global $wpdb;
	global $vtmglobal;
	
	$output = "";
	
	$sql = "SELECT
				path.name,
				disc.name as grp,
				char_disc.level as disclevel,
				cha_path.level,
				cha_path.ID as id,
				path.ID as item_id,
				pendingspend.CHARTABLE_LEVEL,
				steps.XP_COST as XP_COST,
				steps.NEXT_VALUE as NEXT_VALUE,
				NOT(ISNULL(CHARTABLE_LEVEL)) as has_pending,
				pendingspend.ID as pending_id
			FROM
				" . VTM_TABLE_PREFIX . "DISCIPLINE disc,
				" . VTM_TABLE_PREFIX . "PATH path
				LEFT JOIN
					(SELECT ID, LEVEL, COMMENT, CHARACTER_ID, PATH_ID
					FROM
						" . VTM_TABLE_PREFIX . "CHARACTER_PATH
					WHERE
						CHARACTER_ID = %s
					) cha_path
				ON
					cha_path.PATH_ID = path.ID
				LEFT JOIN 
					(SELECT ID, CHARTABLE_LEVEL, CHARTABLE_ID, ITEMTABLE_ID
					FROM
						" . VTM_TABLE_PREFIX . "PENDING_XP_SPEND pending
					WHERE 
						pending.CHARACTER_ID = %s
						AND pending.CHARTABLE = 'CHARACTER_PATH'
					) as pendingspend
				ON
					pendingspend.ITEMTABLE_ID = path.id
				LEFT JOIN
					(SELECT *
					FROM
						" . VTM_TABLE_PREFIX . "CHARACTER_DISCIPLINE 
					WHERE
						CHARACTER_ID = %s
					) as char_disc
				ON
					char_disc.DISCIPLINE_ID = path.DISCIPLINE_ID
				,
				 " . VTM_TABLE_PREFIX . "COST_MODEL_STEP steps,
				 " . VTM_TABLE_PREFIX . "COST_MODEL models
			WHERE 
				steps.COST_MODEL_ID = models.ID
				AND path.COST_MODEL_ID = models.ID
				AND disc.ID = path.DISCIPLINE_ID
				AND char_disc.DISCIPLINE_ID = disc.ID
				AND 
					(char_disc.level >= cha_path.level
					OR ISNULL(cha_path.level)
					)
				AND (
					path.VISIBLE = 'Y'
					OR (NOT(ISNULL(cha_path.LEVEL)) AND steps.CURRENT_VALUE > 0)
				)
				AND (
					(ISNULL(cha_path.LEVEL) AND steps.CURRENT_VALUE = 0)
					OR steps.CURRENT_VALUE = cha_path.level
				)
				AND (
					steps.XP_COST > 0
					OR steps.CURRENT_VALUE > 0
				)
		   ORDER BY grp, path.name";
	$sql = $wpdb->prepare($sql, $characterID,$characterID,$characterID,$characterID);
    //echo "<p>SQL: $sql</p>";
	$character_data = $wpdb->get_results($sql);
	$columns = min(2, $vtmglobal['config']->WEB_COLUMNS);
	
	$rowoutput = vtm_render_spend_table('path', $character_data, $maxRating, $columns, $xp_avail);
	//$rowoutput = vtm_render_spend_table('path', $character_data, $maxRating, 1, $xp_avail);
	
	if (!empty($rowoutput)) {
		$output .= "<table>\n";
		$output .= "$rowoutput\n";
		$output .= "</table>\n";
	} 

	return $output;

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

function vtm_render_spend_table($type, $allxpdata, $maxRating, $columns, $xp_avail) {
	global $vtmglobal;
	
	$fulldoturl    = plugins_url( 'vtm-character/images/dot1full.jpg' );
	$emptydoturl   = plugins_url( 'vtm-character/images/dot1empty.jpg' );
	$pendingdoturl = plugins_url( 'vtm-character/images/dot2.jpg' );
	$levelsdata    = isset($_REQUEST[$type . '_level']) ? $_REQUEST[$type . '_level'] : array();

	switch ($columns) {
		case 1: $colclass = 'vtm_colfull'; break;
		case 2: $colclass = 'vtm_colwide'; break;
		case 3: $colclass = 'vtm_colnarrow'; break;
	}
	
	$max2display = vtm_get_max_dots($allxpdata, $maxRating);
	//$colspan = 2 + $max2display;
	$grp      = "";
	$grpcount = 0;
	$extracols = 0;
	$col = 0;
	$rowoutput = "";
	if (count($allxpdata)>0) {
		$id = 0;
		foreach ($allxpdata as $xpdata) {
			//$id = $xpdata->id;
			
			$tmp_max2display = $max2display;
			if ($type == 'stat') {
				switch ($xpdata->name) {
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
				}
			}
			//$colspan = 2 + $tmp_max2display;
			
			// start column / new column
			if (isset($xpdata->grp)) {
				if ($grp != $xpdata->grp) {
					$grpcount++;
					if (empty($grp)) {
						$rowoutput .= "<tr><td class='$colclass'>\n<table>\n<tr><th class='$colclass' colspan=3>{$xpdata->grp}</th></tr>\n";
						$col++;
					} 
					elseif ($col == $columns) {
						$rowoutput .= "</table>\n</td></tr>\n<tr><td class='$colclass'>\n<table>\n<tr><th class='$colclass' colspan=3>{$xpdata->grp}</th></tr>\n";
						$col = 1;
					}
					else {
						$rowoutput .= "</table>\n</td><td class='$colclass'>\n<table>\n<tr><th class='$colclass' colspan=3>{$xpdata->grp}</th></tr>\n";
						$col++;
					}
					$grp = $xpdata->grp;
				}
			}
			
			$spec_at   = isset($xpdata->spec_at) ?  $xpdata->spec_at : 0;
			$xpcomment = isset($xpdata->comment) ?  vtm_formatOutput($xpdata->comment) : '';
			$xpid      = isset($xpdata->id)      ?  $xpdata->id : '';
			$name      = vtm_formatOutput($xpdata->name);
			
			// Hidden fields
			$rowoutput .= "<tr style='display:none'><td colspan=3>\n";
			$rowoutput .= "<input type='hidden' name='{$type}_spec_at[" . $id . "]' value='" . $spec_at . "' >";
			$rowoutput .= "<input type='hidden' name='{$type}_spec[" . $id . "]'    value='" . $xpcomment . "' >";
			$rowoutput .= "<input type='hidden' name='{$type}_curr[" . $id . "]'    value='" . $xpdata->level . "' >\n";
			$rowoutput .= "<input type='hidden' name='{$type}_itemid[" . $id . "]'  value='" . $xpdata->item_id . "' >\n";
			$rowoutput .= "<input type='hidden' name='{$type}_id[" . $id . "]'      value='" . $xpid . "' >\n";
			$rowoutput .= "<input type='hidden' name='{$type}_name[" . $id . "]'    value='" . $name . "' >\n";
			$rowoutput .= "</td></tr>\n";
			
			
			//dots row
			$xpcost = 0;
			$rowoutput .= "<tr><td class='vtmcol_key'><span";
			if ($xpcomment)
				$rowoutput .= " title='$xpcomment' class='vtmxp_spec' ";
			$rowoutput .= ">$name</span></td>\n";
			$rowoutput .= "<td class='vtmdot_$tmp_max2display vtmdots'>";
			for ($i=1;$i<=$tmp_max2display;$i++) {
			
				if ($xpdata->level >= $i)
					$rowoutput .= "<img alt='*' src='$fulldoturl'>";
				elseif ($maxRating < $i)
					$rowoutput .= "<img alt='O' src='$emptydoturl'>";
				elseif ($xpdata->CHARTABLE_LEVEL)
					if ($xpdata->CHARTABLE_LEVEL >= $i)
						$rowoutput .= "<img alt='X' src='$pendingdoturl'>";
					else
						$rowoutput .= "<img alt='O' src='$emptydoturl'>";
				else
					if ($xpdata->NEXT_VALUE == $i) {
						
						if ($xpdata->NEXT_VALUE > $xpdata->level)
							$xpcost = $xpdata->XP_COST;
							
						if ($xpcost == 0) {
							$rowoutput .= "<img alt='O' src='$emptydoturl'>";
						}
						elseif ($xp_avail >= $xpcost) {
								
							$comment    = $name . " " . $xpdata->level . " > " . $i;
						
							$rowoutput .= "<input type='hidden'   name='{$type}_cost[" . $id . "]'    value='" . $xpcost . "' >";
							$rowoutput .= "<input type='hidden'   name='{$type}_comment[" . $id . "]' value='$comment' >";
							$rowoutput .= "<input type='CHECKBOX' name='{$type}_level[" . $id . "]'   value='$i' id='vtmcb_{$type}_$id' ";
							if (isset($levelsdata[$id]) && $i == $levelsdata[$id])
								$rowoutput .= "checked";
							$rowoutput .= "><label for='vtmcb_{$type}_$id' title='[ ]'>&nbsp;</label>";
						}
						else
							$rowoutput .= "<img alt='O' src='$emptydoturl'>";
					}
					else
						$rowoutput .= "<img alt='O' src='$emptydoturl'>";
						
			}
			
				
			//$xpcost = ($xpdata->NEXT_VALUE <= $maxRating) ? "(" . $xpdata->XP_COST . " XP)" : "";
			$xpcost = ($xpdata->NEXT_VALUE <= $maxRating) ? $xpdata->XP_COST . "xp" : "";
			if ($xpdata->has_pending)
				$rowoutput .= "<td class='vtmxp_cost'><input class='vtmxp_clear' type='submit' name='{$type}_cancel[{$xpdata->pending_id}]' value='Del'></td>";
			elseif ($xpdata->XP_COST == 0) {
				$rowoutput .= "<td class='vtmxp_cost'>&nbsp;</td>";
			}
			else
				$rowoutput .= "<td class='vtmxp_cost'>$xpcost</td>";
				
			$id++;
			if ($id == count($allxpdata)) {
				$remaining = $grpcount % $columns;
				if ($remaining) {
					$extracols = $columns - $remaining;
				}
			}
			
			$rowoutput .= "</tr>\n";
			
		}
	}
	
	if ($extracols > 0) {
		$rowoutput .= "</table></td>";
		for ($i = 1 ; $i <= $extracols ; $i++) {
			$rowoutput .= "<td class='$colclass'>&nbsp;</td>";
		}
		$rowoutput .= "</tr>";
	} 
	elseif ($rowoutput != "")
		$rowoutput .= "</table></td></tr>\n";

	return $rowoutput;
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

	$fulldoturl    = plugins_url( 'vtm-character/images/dot1full.jpg' );
	$emptydoturl   = plugins_url( 'vtm-character/images/dot1empty.jpg' );
	$pendingdoturl = plugins_url( 'vtm-character/images/dot2.jpg' );
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

	$fulldoturl    = plugins_url( 'vtm-character/images/dot1full.jpg' );
	$emptydoturl   = plugins_url( 'vtm-character/images/dot1empty.jpg' );
	$pendingdoturl = plugins_url( 'vtm-character/images/dot2.jpg' );
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
	if (count($list)>0) {
		$id = 0;
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

	$fulldoturl    = plugins_url( 'vtm-character/images/dot1full.jpg' );
	$emptydoturl   = plugins_url( 'vtm-character/images/dot1empty.jpg' );
	$pendingdoturl = plugins_url( 'vtm-character/images/dot2.jpg' );

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
		/* check what the character has, in case they have the merit that increases
		something above max */
		if (count($data)) 
			foreach ($data as $row) {
				if (gettype($row) == "array") {
					foreach ($row as $item) {
						if ($item->level > $max2display)
							$max2display = 10;
					}
				} else {
					if ($row->level > $max2display)
						$max2display = 10;
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
	
	/* echo "<p>charid: $chartableid, itemid: $itemid, result: $result</p>"; */
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
	
	/* echo "<p>charid: $chartableid, itemid: $itemid, result: $result</p>"; */
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
	
	/* echo "<p>charid: $chartableid, itemid: $itemid, result: $result</p>"; */
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
	/* Name */
	$output .= "<td colspan=2 class='vtmcol_key'>" . vtm_formatOutput($xpdata->name) . "\n";
	$output .= " </th>\n";

	$pendinglvl = vtm_pending_level($pending, $xpdata->id, $xpdata->item_id);

	$radiogroup = "ritual_level[" . $id . "]";
	$radiovalue = $xpdata->level;
	
	/* echo "<p>id: $id<p>";
	print_r($levelsdata); */

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
		$output .= "<td class='gvxp_radio'>&nbsp;</td>"; /* no change dot */
	else
		$output .= "<td class='gvxp_radio'><input type='RADIO' name='ritual_level[{$id}]' value='0' selected='selected'></td>"; /* no change dot */
	
	/* no training note if you cannot buy */
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

function vtm_save_to_pending ($type, $table, $itemtable, $itemidname, $playerID, $characterID) {
	global $wpdb;

	$newid = "";

	$ids             = isset($_REQUEST[$type . '_id'])       ? $_REQUEST[$type . '_id'] : array();
	$levels          = isset($_REQUEST[$type . '_level'])    ? $_REQUEST[$type . '_level'] : array();
	$specialisations = isset($_REQUEST[$type . '_spec'])     ? $_REQUEST[$type . '_spec'] : array();
	$training        = isset($_REQUEST[$type . '_training']) ? $_REQUEST[$type . '_training'] : array();
	$itemid          = isset($_REQUEST[$type . '_itemid'])   ? $_REQUEST[$type . '_itemid'] : array();
	$costlvls        = isset($_REQUEST[$type . '_cost'])     ? $_REQUEST[$type . '_cost'] : array();
	$comments        = isset($_REQUEST[$type . '_comment'])  ? $_REQUEST[$type . '_comment'] : array();
	
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
				'COMMENT'         => isset($comments[$index]) ? $comments[$index] : '',
				'SPECIALISATION'  => isset($specialisations[$index]) ? $specialisations[$index] : '',
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
			
			$newid = $wpdb->insert_id;
			if ($newid  == 0) {
				echo "<p style='color:red'><b>Error:</b> XP Spend failed for data (";
				print_r($dataarray);
				$wpdb->print_error();
				echo ")</p>";
			} 
		}
	
	}	
	
	return $newid;
							
}

function vtm_save_merit_to_pending ($type, $table, $itemtable, $itemidname, $playerID, $characterID) {
	global $wpdb;

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
			
			$newid = $wpdb->insert_id;
			if ($newid  == 0) {
				echo "<p style='color:red'><b>Error:</b> XP Spend failed for data (";
				print_r($dataarray);
				$wpdb->print_error();
				echo ")</p>";
			} 
		}
	
	}	
	
	return $newid;
							
}


/* function vtm_get_total_xp($characterID) {
	global $wpdb;
	
	$sql = "SELECT SUM(AMOUNT) as COST FROM " . VTM_TABLE_PREFIX . "PLAYER_XP WHERE CHARACTER_ID = %s";

	$sql = $wpdb->prepare($sql, $characterID);
	$result = $wpdb->get_results($sql);
	$xptotal = $result[0]->COST;
	
	return $xptotal;
}
 */
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
	/* echo "<pre>$sql</pre>"; */
	$result = $wpdb->get_results($sql);
	
	return $result[0]->PRIVATE_CLAN_ID;
}

function vtm_cancel_pending($data) {
	global $wpdb;
	
	foreach ($data as $pendingid => $button) {
		$sql = "DELETE FROM " . VTM_TABLE_PREFIX . "PENDING_XP_SPEND
				WHERE ID = %d";
		$sql = $wpdb->prepare($sql, $pendingid);
		/* echo "<p>SQL: $sql</p>"; */
		$result = $wpdb->get_results($sql);
	}
}

function vtm_validate_spends($playerID, $characterID, $docancel) {

	$xp_total   = vtm_get_total_xp($playerID, $characterID);
	$xp_pending = vtm_get_pending_xp($playerID, $characterID);
	$xp_spent    = 0;
	$outputError = "";
	
	if (isset($_REQUEST['stat_level'])) $xp_spent += vtm_calc_submitted_spend('stat');
	if (isset($_REQUEST['skill_level'])) $xp_spent += vtm_calc_submitted_spend('skill');
	if (isset($_REQUEST['disc_level'])) $xp_spent += vtm_calc_submitted_spend('disc');
	if (isset($_REQUEST['combo_level'])) $xp_spent += vtm_calc_submitted_spend('combo');
	if (isset($_REQUEST['path_level'])) $xp_spent += vtm_calc_submitted_spend('path');
	if (isset($_REQUEST['ritual_level'])) $xp_spent += vtm_calc_submitted_spend('ritual');
	if (isset($_REQUEST['merit_level'])) $xp_spent += vtm_calc_submitted_spend('merit');
	
	if ($xp_spent > ($xp_total - $xp_pending)) {
		$outputError .= "<p>You don't have enough experience left</p>";
	}
	
	if (!$xp_spent)
		if ($docancel)
			$outputError .= "<p>Experience spend has been cleared</p>";
		else
			$outputError .= "<p>You have not spent any experience</p>";
	
	/* echo "<p>Spent $xp_spent, Total: $xp_total, Pending: $xp_pending</p>"; */
	
	return $outputError;
	
}
function vtm_validate_details($characterID) {

	$outputError = "";
	
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

	
	return $outputError;
	
}
?>