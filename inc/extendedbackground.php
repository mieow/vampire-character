<?php

function vtm_extended_background_content_filter($content) {

  if (is_page(vtm_get_stlink_page('viewExtBackgrnd')))
		if (is_user_logged_in()) {
			$content .= vtm_get_extbackgrounds_content();
		} else {
			$content .= "<p>You must be logged in to view this content.</p>";
		}
  // otherwise returns the database content
  return $content;
}

add_filter( 'the_content', 'vtm_extended_background_content_filter' );


function vtm_get_extbackgrounds_content() {

	$character = vtm_establishCharacter('');
	$characterID = vtm_establishCharacterID($character);
		
	$content = "<div class='wrap'>
		<script type='text/javascript'>
			function vtm_tabSwitch(tab) {
				vtm_setSwitchState('backgrounds', tab == 'backgrounds');
				vtm_setSwitchState('meritflaw', tab == 'meritflaw');
				vtm_setSwitchState('misc', tab == 'misc');
				return false;
			}
			function vtm_setSwitchState(tab, show) {
				document.getElementById('gv-'+tab).style.display = show ? 'block' : 'none';
				document.getElementById('gvm-'+tab).className = show ? 'shown' : '';
			}
		</script>
		<div class='vtmbgmenu'>
			<ul>
			<li>" . vtm_get_tabanchor('backgrounds', 'Backgrounds') . "</li>
			<li>" . vtm_get_tabanchor('meritflaw', 'Merits and Flaws') . "</li>
			<li>" . vtm_get_tabanchor('misc', 'Miscellaneous') . "</li>
			</ul>
		</div>
		<div class='vtmbgmain'>
			<div id='gv-backgrounds' " . vtm_get_tabdisplay('backgrounds') . ">
				" . vtm_get_editbackgrounds_tab($characterID) . "
				
			</div>
			<div id='gv-meritflaw' " . vtm_get_tabdisplay('meritflaw') . ">
				" . vtm_get_editmerits_tab($characterID) . "	
				
			</div>
			<div id='gv-misc' " . vtm_get_tabdisplay('misc') . ">
				" . vtm_get_editmisc_tab($characterID) . "
				
			</div>
		</div>
	</div>";
	
	return $content;
}
function vtm_get_tabanchor($tab, $text, $default = "backgrounds"){
	$markup = '<a id="gvm-@TAB@" href="javascript:void(0);" onclick="vtm_tabSwitch(\'@TAB@\');"@SHOWN@>@TEXT@</a>';
	return str_replace(
		Array('@TAB@','@TEXT@','@SHOWN@'),
			Array($tab, vtm_formatOutput($text), vtm_get_highlight($tab, $default)),
			$markup
		);
}
function vtm_get_highlight($tab, $default="backgrounds"){
	if ((isset($_REQUEST['tab']) && $_REQUEST['tab'] == $tab) || ($tab == $default))
		return " class='shown'";
	return "";
}
function vtm_get_tabdisplay($tab, $default="backgrounds") {

	$display = "style='display:none'";
	
	/* echo "<p>tab: $tab, request tab: {$_REQUEST['tab']}.</p>"; */

	if (isset($_REQUEST['tab'])) {
		if ($_REQUEST['tab'] == $tab)
			$display = "class='".$tab."'";
	} else if ($tab == $default) {
		$display = "class='default'";
	}

	return $display;
}

function vtm_get_editbackgrounds_tab($characterID) {
	global $wpdb;

	$character = vtm_establishCharacter("");
	$characterID = vtm_establishCharacterID($character);
	
	$content = "";
	
	/* Save backgrounds */
	if (isset($_REQUEST['save_bgform'])) {
	
		$bgids     = $_REQUEST['charbgID'];
		$sectors   = $_REQUEST['sectorid'];
		$pendingbg = $_REQUEST['pendingbg'];
		$namesbg   = $_REQUEST['charbgName'];
		$comments  = $_REQUEST['charbgComment'];
		
		foreach ($_REQUEST['save_bgform'] as $id => $buttontitle) {
			$sector = isset($sectors[$id]) ? $sectors[$id] : 0;

			$data = array (
				'SECTOR_ID'      => $sector,
				'PENDING_DETAIL' => $pendingbg[$id],
				'DENIED_DETAIL'  => '',
				'COMMENT'        => $comments[$id]
			);
			$wpdb->show_errors();
			$result = $wpdb->update(VTM_TABLE_PREFIX . "CHARACTER_BACKGROUND",
				$data,
				array (
					'ID' => $bgids[$id]
				)
			);
			
			if ($result) 			echo "<p style='color:green'>Updated {$namesbg[$id]} background</p>";
			else if ($result === 0) echo "<p style='color:orange'>No updates made to {$namesbg[$id]} background</p>";
			else {
				$wpdb->print_error();
				echo "<p style='color:red'>Could not update {$namesbg[$id]} background</p>";
			}
			
			}
			
	}

	/* get all the backgrounds for this character that need extra detail */
	$backgrounds = vtm_get_extbackgrounds_questions($characterID);
	$i = 0;
	
	$content .= "<form name='extbgform' method='post'>\n";
	$content .= "<input type='hidden' name='charID' value='$characterID' />";
	
	foreach ($backgrounds as $background) {
		$content .= "<p class='vtmext_name'>" . vtm_formatOutput($background->NAME) . ": " . $background->LEVEL;
		//$content .= ($background->COMMENT) ? " (" . vtm_formatOutput($background->COMMENT) . ")" : "";
		$content .= "</p>\n";
		if (!empty($background->BACKGROUND_QUESTION))
			$content .= "<div class='vtmext_ques'>" . wpautop(vtm_formatOutput($background->BACKGROUND_QUESTION)) . "</div>\n";
		$content .= "<div class='vtmext_section'>";
		$content .= "<input type='hidden' name='charbgID[$i]' value='{$background->charbgsID}' />\n";
		$content .= "<input type='hidden' name='charbgName[$i]' value='{$background->NAME}' />\n";
		
		$content .= "<table>";
		if ($background->HAS_SPECIALISATION == 'Y') {
			$content .= "<tr><th>Specialisation:</th></tr>";
			$content .= "<tr><td>";
			$content .= "<input type='text' name='charbgComment[$i]' value='{$background->COMMENT}' />";
			$content .= "</td></tr>\n";
		}
		if ($background->HAS_SECTOR == 'Y') {
			$content .= "<tr><th>Sector:</th></tr>";
			$content .= "<tr><td><select name='sectorid[$i]'>";
			$content .= "<option value='0' ";
			if ($background->SECTOR_ID == 0)
				$content .= "selected='selected'";
			$content .= ">[Select]</option>";
			$found = 0;
			foreach (vtm_get_sectors(vtm_isST()) as $sector) {
				$content .= "<option value='{$sector->ID}' ";
				if ($background->SECTOR_ID == $sector->ID) {
					$content .= "selected='selected'";
					$found = 1;
				}
				$content .= ">" . vtm_formatOutput($sector->NAME) . "</option>";
			}
			if (!$found && !empty($background->SECTOR_ID)) {
				foreach (vtm_get_sectors(true) as $sector) {
					if ($background->SECTOR_ID == $sector->ID) {
						$content .= "<option value='{$sector->ID}' selected='selected' >" . vtm_formatOutput($sector->NAME) . "</option>";
					}
				}
			}
				
			$content .= "</select></td></tr>\n";
		}
		
		if ($background->BACKGROUND_QUESTION != '') {
			if (!empty($background->APPROVED_DETAIL))
				$content .= "<tr><th>Approved Description</th></tr><tr><td class='vtmext_approved'>" . wpautop(vtm_formatOutput($background->APPROVED_DETAIL, 1)) . "</td></tr>";
			if ($background->DENIED_DETAIL != "") {
				$content .= "<tr><th>Description Denied</th></tr><tr><td class='vtmext_denied'>" . wpautop(vtm_formatOutput($background->DENIED_DETAIL, 1)) . "</td></tr>\n";
			}
			$content .= "<tr><th>Update Description";
			if ($background->DENIED_DETAIL != "")
				$content .= " (denied, please update)";
			else if ($background->PENDING_DETAIL != "")
				$content .= " (saved, awaiting approval)";
			$content .= "</th></tr>";
			$content .= "<tr><td><textarea name='pendingbg[$i]' rows='5' cols='100'>";
			if (isset($pendingbg[$i]))
				$content .= vtm_formatOutput($pendingbg[$i]);
			else
				if ($background->PENDING_DETAIL == "")
					$content .= vtm_formatOutput($background->APPROVED_DETAIL, 1);
				else
					$content .= vtm_formatOutput($background->PENDING_DETAIL, 1);
			$content .= "</textarea></td></tr>";
		}
		
		$content .= "<tr><td><input type='submit' name='save_bgform[$i]' value='Save " . vtm_formatOutput($background->NAME) . "' /></td></tr>\n";
		$content .= "</table></div>\n";
		$i++;
	}
	$content .= "</form>\n";
	if (count($backgrounds) == 0) {
		$content .= "<p>You have no backgrounds requiring explanation</p>";
	}
	
	return $content;
}

function vtm_get_editmerits_tab($characterID) {
	global $wpdb;

	$character = vtm_establishCharacter("");
	$characterID = vtm_establishCharacterID($character);
	
	$content = "";
	
	/* Save Merits and Flaws */
	if (isset($_REQUEST['save_meritform'])) {
	
		$meritids = $_REQUEST['meritID'];
		$pendingmerit = $_REQUEST['pendingmerit'];
		$namesmerit    = $_REQUEST['charmeritName'];
		
		foreach ($_REQUEST['save_meritform'] as $id => $buttontitle) {
			$data = array (
				'PENDING_DETAIL' => $pendingmerit[$id],
				'DENIED_DETAIL'  => ''
			);
			$wpdb->show_errors();
			$result = $wpdb->update(VTM_TABLE_PREFIX . "CHARACTER_MERIT",
				$data,
				array (
					'ID' => $meritids[$id]
				)
			);
			
			if ($result) 			echo "<p style='color:green'>Updated {$namesmerit[$id]}</p>";
			else if ($result === 0) echo "<p style='color:orange'>No updates made to {$namesmerit[$id]} background</p>";
			else {
				$wpdb->print_error();
				echo "<p style='color:red'>Could not update {$namesmerit[$id]}</p>";
			}
		}
		
	} 

	/* get all the merits and flaws for this character that need extra detail */
	$merits = vtm_get_extmerits_questions($characterID);
	
	$i = 0;
	$content .= "<form name='extmeritform[$i]' method='post'>\n";
	$content .= "<input type='hidden' name='charID' value='$characterID' />";
	$content .= "<input type='hidden' name='tab' value='meritflaw' />\n";
	
	foreach ($merits as $merit) {
	
		$content .= "<p class='vtmext_name'>" . vtm_formatOutput($merit->NAME);
		$content .= ($merit->COMMENT) ? " (" . vtm_formatOutput($merit->COMMENT) . ")" : "";
		$content .= "</p>\n<div class='vtmext_ques'>" . wpautop(vtm_formatOutput($merit->BACKGROUND_QUESTION)) . "</div>\n";
		$content .= "<div class='vtmext_section'>";
		$content .= "<input type='hidden' name='meritID[$i]' value='{$merit->meritID}' />\n";
		$content .= "<input type='hidden' name='charmeritName[$i]' value='" . vtm_formatOutput($merit->COMMENT) . "' />\n";
		$content .= "<table>";

		if (!empty($merit->APPROVED_DETAIL))
			$content .= "<tr><th>Approved Description</th></tr><tr><td class='vtmext_approved'>" . wpautop(vtm_formatOutput($merit->APPROVED_DETAIL, 1)) . "</td></tr>";
		if ($merit->DENIED_DETAIL != "") {
			$content .= "<tr><th>Description Denied</th></tr><tr><td class='vtmext_denied'>" . wpautop(vtm_formatOutput($merit->DENIED_DETAIL, 1)) . "</td></tr>\n";
		}
		$content .= "<tr><th>Update Description";
		if ($merit->DENIED_DETAIL != "")
			$content .= " (denied, please update)";
		else if ($merit->PENDING_DETAIL != "")
			$content .= " (saved, awaiting approval)";
		$content .= "</th></tr>";
		$content .= "<tr><td><textarea name='pendingmerit[$i]' rows='5' cols='100'>";
		if (isset($pendingmerit[$i]))
			$content .= vtm_formatOutput($pendingmerit[$i]);
		else
			if ($merit->PENDING_DETAIL == "")
				$content .= vtm_formatOutput($merit->APPROVED_DETAIL, 1);
			else
				$content .= vtm_formatOutput($merit->PENDING_DETAIL, 1);
		$content .= "</textarea></td></tr>";

		
		$content .= "<tr><td><input type='submit' name='save_meritform[$i]' value='Save " . vtm_formatOutput($merit->NAME) . "' /></td></tr>\n";
		$content .= "</table></div>\n";
		$i++;
	}
	$content .= "</form>\n";
	
	if (count($merits) == 0) {
		$content .= "<p>You have no merits or flaws requiring explanation</p>";
	}
	
	
	return $content;
}

function vtm_get_editmisc_tab($characterID) {
	global $wpdb;

	$character = vtm_establishCharacter("");
	$characterID = vtm_establishCharacterID($character);
	$wpdb->show_errors();
	
	$content = "";
	
	$miscids      = isset($_REQUEST['miscID'])        ? $_REQUEST['miscID'] : array();
	$questids     = isset($_REQUEST['questID'])       ? $_REQUEST['questID'] : array();
	$pendingmisc  = isset($_REQUEST['pendingmisc'])   ? $_REQUEST['pendingmisc'] : array();
	$namesmisc    = isset($_REQUEST['charmiscTitle']) ? $_REQUEST['charmiscTitle'] : array();
	
	/* Save Misc Extended Background Answers */
	if (isset($_REQUEST['miscID'])) {
	
		foreach ($_REQUEST['save_miscform'] as $id => $buttontext) {
			if ($miscids[$id] == "") {
				/* new answer */
				$data = array (
					'CHARACTER_ID'    => $_REQUEST['charID'],
					'QUESTION_ID'     => $questids[$id],
					'APPROVED_DETAIL' => '',
					'PENDING_DETAIL'  => $pendingmisc[$id],
					'DENIED_DETAIL'   => ''
				);
				$wpdb->insert(VTM_TABLE_PREFIX . "CHARACTER_EXTENDED_BACKGROUND", $data,
					array (
						'%d',
						'%d',
						'%s',
						'%s',
						'%s'
					)
				);
				if ($wpdb->insert_id == 0) {
					echo "<p style='color:red'><b>Error:</b> {$namesmisc[$id]} could not be saved (";
					$wpdb->print_error();
					echo ")</p>";
				} else {
					echo "<p style='color:green'>Saved answer '{$namesmisc[$id]}' for approval</p>";
				}

			} else {
		
				/* update answer */
				$data = array (
					'PENDING_DETAIL' => $pendingmisc[$id],
					'DENIED_DETAIL'  => ''
				);
				
				//print "<p>Info: $id, {$miscids[$id]}, {$_REQUEST['miscID']}</p><pre>";
				//print_r($data);
				//print "</pre>";
				
				$result = $wpdb->update(VTM_TABLE_PREFIX . "CHARACTER_EXTENDED_BACKGROUND",
					$data,
					array (
						'ID' => $miscids[$id]
					)
				);
				
				if ($result) 			echo "<p style='color:green'>Updated {$namesmisc[$id]}</p>";
				else if ($result === 0) echo "<p style='color:orange'>No updates made to {$namesmisc[$id]} answer</p>";
				else {
					$wpdb->print_error();
					echo "<p style='color:red'>Could not update {$namesmisc[$id]}</p>";
				}
			}
		}
	} 
	
	/* get all the background questions that need extra detail */
	$questions = vtm_get_extmisc_questions($characterID);
	$i = 0;
	$content .= "<form name='extmiscform' method='post'>\n";
	$content .= "<input type='hidden' name='tab' value='misc' />\n";
	$content .= "<input type='hidden' name='charID' value='$characterID' />";
	
	foreach ($questions as $question) {
	
		$content .= "<p class='vtmext_name'>" . vtm_formatOutput($question->TITLE) . "</p>\n";
		$content .= "<div class='vtmext_ques'>" . wpautop(vtm_formatOutput($question->BACKGROUND_QUESTION)) . "</div>\n";
		$content .= "<div class='vtmext_section'>";
		$content .= "<input type='hidden' name='miscID[$i]' value='{$question->miscID}' />\n";
		$content .= "<input type='hidden' name='miscformID[$i]' value='{$i}' />\n";
		$content .= "<input type='hidden' name='questID[$i]' value='{$question->questID}' />\n";
		$content .= "<input type='hidden' name='charmiscTitle[$i]' value='" . vtm_formatOutput($question->TITLE) . "' />\n";
		$content .= "<table>";

		if (!empty($question->APPROVED_DETAIL))
			$content .= "<tr><th>Approved Description</th></tr><tr><td class='vtmext_approved'>" . wpautop(vtm_formatOutput($question->APPROVED_DETAIL, 1)) . "</td></tr>";
		if ($question->DENIED_DETAIL != "") {
			$content .= "<tr><th>Description Denied</th></tr><tr><td class='vtmext_denied'>" . wpautop(vtm_formatOutput($question->DENIED_DETAIL, 1)) . "</td></tr>\n";
		}
		$content .= "<tr><th>Update Description";
		if ($question->DENIED_DETAIL != "")
			$content .= " (denied, please update)";
		else if ($question->PENDING_DETAIL != "")
			$content .= " (saved, awaiting approval)";
		$content .= "</th></tr>";
		$content .= "<tr><td><textarea name='pendingmisc[$i]' rows='5' cols='100'>";
		if (isset($pendingmisc[$i]))
			$content .= vtm_formatOutput($pendingmisc[$i]);
		else
			if ($question->PENDING_DETAIL == "")
				$content .= vtm_formatOutput($question->APPROVED_DETAIL, 1);
			else
				$content .= vtm_formatOutput($question->PENDING_DETAIL, 1);
		$content .= "</textarea></td></tr>";

		
		$content .= "<tr><td><input type='submit' name='save_miscform[$i]' value='Save " . vtm_formatOutput($question->TITLE) . "' /></td></tr>\n";
		$content .= "</table></div>\n";
		$i++;
	}
	$content .= "</form>\n";
	if (count($questions) == 0) {
		$content .= "<p>There are no extended background questions to answer.</p>";
	}
	
	
	return $content;
}

function vtm_get_extbackgrounds_questions($characterID) {
	global $wpdb;
	
	$sql = "select backgrounds.NAME, charbgs.LEVEL, backgrounds.BACKGROUND_QUESTION,
				charbgs.SECTOR_ID, charbgs.APPROVED_DETAIL, charbgs.PENDING_DETAIL,
				charbgs.DENIED_DETAIL, charbgs.ID as charbgsID, backgrounds.HAS_SECTOR,
				charbgs.COMMENT, backgrounds.HAS_SPECIALISATION
			from	" . VTM_TABLE_PREFIX . "BACKGROUND backgrounds,
					" . VTM_TABLE_PREFIX . "CHARACTER_BACKGROUND charbgs,
					" . VTM_TABLE_PREFIX . "CHARACTER characters
			where	backgrounds.ID = charbgs.BACKGROUND_ID
				and	characters.ID = %d
				and characters.ID = charbgs.CHARACTER_ID
				and	(backgrounds.BACKGROUND_QUESTION != '' 
					OR backgrounds.HAS_SECTOR = 'Y'
					OR backgrounds.HAS_SPECIALISATION = 'Y');";
	/* $content = "<p>SQL: $sql</p>";  */
	
	$backgrounds = $wpdb->get_results($wpdb->prepare($sql, $characterID));

	return $backgrounds;
}

function vtm_get_extmerits_questions($characterID) {
	global $wpdb;

	$sql = "select merits.NAME, charmerits.APPROVED_DETAIL, charmerits.PENDING_DETAIL,
				charmerits.DENIED_DETAIL, charmerits.ID as meritID, merits.BACKGROUND_QUESTION,
				charmerits.COMMENT
			from	" . VTM_TABLE_PREFIX . "MERIT merits,
					" . VTM_TABLE_PREFIX . "CHARACTER_MERIT charmerits,
					" . VTM_TABLE_PREFIX . "CHARACTER characters
			where	merits.ID = charmerits.MERIT_ID
				and	characters.ID = %d
				and characters.ID = charmerits.CHARACTER_ID
				and	merits.BACKGROUND_QUESTION != '';";
	/* $content = "<p>SQL: $sql</p>"; */
	
	$merits = $wpdb->get_results($wpdb->prepare($sql, $characterID));
	
	return $merits;
}

function vtm_get_extmisc_questions($characterID) {
	global $wpdb;

	$sql = "SELECT questions.TITLE, questions.ORDERING, questions.GROUPING, questions.BACKGROUND_QUESTION, 
				tempcharmisc.APPROVED_DETAIL, tempcharmisc.PENDING_DETAIL, tempcharmisc.DENIED_DETAIL, 
				tempcharmisc.ID AS miscID, questions.ID as questID
			FROM " . VTM_TABLE_PREFIX . "CHARACTER characters, 
				 " . VTM_TABLE_PREFIX . "EXTENDED_BACKGROUND questions
				LEFT JOIN (
					SELECT charmisc.APPROVED_DETAIL, charmisc.PENDING_DETAIL, charmisc.DENIED_DETAIL, 
						charmisc.ID AS ID, charmisc.QUESTION_ID, characters.ID as charID
					FROM " . VTM_TABLE_PREFIX . "CHARACTER_EXTENDED_BACKGROUND charmisc, 
						 " . VTM_TABLE_PREFIX . "CHARACTER characters
					WHERE characters.ID = charmisc.CHARACTER_ID
				) tempcharmisc 
				ON questions.ID = tempcharmisc.QUESTION_ID AND tempcharmisc.charID = %d
			WHERE characters.ID = %s
				AND questions.VISIBLE = 'Y'
			ORDER BY questions.ORDERING ASC";
			
	$sql = $wpdb->prepare($sql, $characterID, $characterID);
	//echo "<p>SQL: $sql</p>";
	$questions = $wpdb->get_results($sql);
	return $questions;
}
?>