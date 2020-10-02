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
				vtm_setSwitchState('contact', tab == 'contact');
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
			<li>" . vtm_get_tabanchor('misc', 'Miscellaneous') . "</li>";
	if (get_option('vtm_feature_pm',0)) {
		$content .= "<li>" . vtm_get_tabanchor('contact', 'Contact Details') . "</li>";
	}
	$content .= "		</ul>
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
				
			</div>";
	if (get_option('vtm_feature_pm',0)) {
		$content .= "<div id='gv-contact' " . vtm_get_tabdisplay('contact') . ">
				" . vtm_get_editcontact_tab($characterID) . "
			</div>";
	}
	$content .= "</div>
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
				'PENDING_DETAIL' => isset($pendingbg[$id]) ? $pendingbg[$id] : "",
				'DENIED_DETAIL'  => '',
				'COMMENT'        => isset($comments[$id]) ? $comments[$id] : ""
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

function vtm_get_editcontact_tab($characterID) {
	global $wpdb;

	$character = vtm_establishCharacter("");
	$characterID = vtm_establishCharacterID($character);
	$wpdb->show_errors();
	
	if (empty($characterID)) {
		return "<p>A character is required</p>";
	}

	$content = "";
	
	if (isset($_REQUEST["addcontact"])) {
		$phone_pm_type_id = $wpdb->get_var("SELECT ID FROM " . VTM_TABLE_PREFIX . "PM_TYPE WHERE NAME = 'Telephone'");
		if ($_REQUEST["addcontact"] == 'mobile') {
			$number = vtm_generate_phone($characterID, get_option('vtm_pm_mobile_prefix',''));			
			
			$dataarray = array(
							'NAME'         => "Mobile number",
							'CHARACTER_ID' => $characterID,
							'PM_TYPE_ID'   => $phone_pm_type_id,
							'PM_CODE'      => $number,
							'DESCRIPTION'  => "Auto-generated number",
							'VISIBLE'      => 'Y',
							'ISDEFAULT'    => 'N',
							'DELETED'      => 'N',
						);
			
			
		}
		elseif($_REQUEST["addcontact"] == 'landline') {
			$number = vtm_generate_phone($characterID, get_option('vtm_pm_landline_prefix',''));			
				$dataarray = array(
							'NAME'         => "Phone number",
							'CHARACTER_ID' => $characterID,
							'PM_TYPE_ID'   => $phone_pm_type_id,
							'PM_CODE'      => $number,
							'DESCRIPTION'  => "Auto-generated number",
							'VISIBLE'      => 'Y',
							'ISDEFAULT'    => 'N',
							'DELETED'      => 'N',
						);
		}
		$wpdb->insert(VTM_TABLE_PREFIX . "CHARACTER_PM_ADDRESS",
					$dataarray,
					array (
						'%s',
						'%d',
						'%d',
						'%s',
						'%s',
						'%s',
						'%s'
					)
				);
		
		if ($wpdb->insert_id == 0) {
			echo "<p style='color:red'><b>Error:</b>number could not be inserted (";
			$wpdb->print_error();
			echo ")</p>";
		} 
		
	}
	elseif (isset($_REQUEST["delcontact"])) {
		$result = $wpdb->update(VTM_TABLE_PREFIX . "CHARACTER_PM_ADDRESS",
					array ('DELETED' => 'Y'),
					array ('ID' => $_REQUEST["delcontact"])
				);
		
		if ($result) 
			echo "<p style='color:green'>Deleted phone number</p>";
		else if ($result === 0) 
			echo "<p style='color:orange'>No changes made</p>";
		else {
			$wpdb->print_error();
			echo "<p style='color:red'>Could not delete phone number</p>";
		}
	}
	
	$contactdetails = vtm_get_extcontact_numbers($characterID);
	
	$content .= "<p>For advanced settings, click <a href=" . admin_url('edit.php?post_type=vtmpm&amp;page=vtmpm_mydetails') . ">here</a>.</p>";
	
	// Mobile phone numbers
	$content .= "<p class='vtmext_name'>My Mobile Numbers</p>
			<div class='vtmext_section'>\n";
	$content .= "<ul>";
	foreach ($contactdetails["mobile"] as $phonenum) {
		$content .= "<li>";
		$content .= vtm_formatOutput($phonenum->NAME);
		if ($phonenum->ISDEFAULT == 'Y')
			$content .= " (default)";
		$content .= " - ";
		$content .= vtm_formatOutput($phonenum->PM_CODE);
		$content .= " <a href='?tab=contact&amp;delcontact={$phonenum->ID}&amp;characterID=$characterID'>[X]</a></li>\n";
	}
	$content .= "<li><a href='?tab=contact&amp;addcontact=mobile&amp;characterID=$characterID'>Add new mobile number</a></li>";
	$content .= "</ul>";
	$content .= "</div>\n";

	// Landline phone numbers
	$content .= "<p class='vtmext_name'>Phone Numbers</p>
			<div class='vtmext_section'>\n";
	$content .= "<ul>";
	foreach ($contactdetails["landline"] as $phonenum) {
		$content .= "<li>";
		$content .= vtm_formatOutput($phonenum->NAME);
		if ($phonenum->ISDEFAULT == 'Y')
			$content .= " (default)";
		$content .= " - ";
		$content .= vtm_formatOutput($phonenum->PM_CODE);
		$content .= " <a href='?tab=contact&amp;delcontact={$phonenum->ID}&amp;characterID=$characterID'>[X]</a></li>\n";
	}
	foreach ($contactdetails["other"] as $phonenum) {
		$content .= "<li>";
		$content .= vtm_formatOutput($phonenum->NAME);
		if ($phonenum->ISDEFAULT == 'Y')
			$content .= " (default)";
		$content .= " - ";
		$content .= vtm_formatOutput($phonenum->PM_CODE);
		$content .= " <a href='?tab=contact&amp;delcontact={$phonenum->ID}&amp;characterID=$characterID'>[X]</a></li>\n";
	}
	$content .= "<li><a href='?tab=contact&amp;addcontact=landline&amp;characterID=$characterID'>Add new phone number</a></li>";
	$content .= "</ul>";
	$content .= "</div>\n";

	
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
function vtm_get_extcontact_numbers($characterID) {
	global $wpdb;
	

	$sql = "SELECT cpmad.ID, 
				cpmad.NAME, 
				cpmad.DESCRIPTION, 
				cpmad.VISIBLE, 
				cpmad.PM_CODE, 
				cpmad.ISDEFAULT,
				pmtype.NAME as PM_TYPE
			FROM 
				" . VTM_TABLE_PREFIX . "CHARACTER_PM_ADDRESS cpmad,
				" . VTM_TABLE_PREFIX . "PM_TYPE pmtype
			WHERE
				cpmad.DELETED = 'N'
				AND cpmad.CHARACTER_ID = '%s'
				AND pmtype.ID = cpmad.PM_TYPE_ID
				AND pmtype.NAME = 'Telephone'";
			
	$sql = $wpdb->prepare($sql, $characterID);
	//echo "<p>SQL: $sql</p>";
	$data = $wpdb->get_results($sql);
	
	$mobile   = array();
	$landline = array();
	$other    = array();
	$address  = array();
	
	$mobileprefix   = get_option('vtm_pm_mobile_prefix', '');
	$landlineprefix = get_option('vtm_pm_landline_prefix', '');
	
	foreach ($data as $entry) {
		if ($entry->PM_TYPE = "Telephone" && !empty($mobileprefix) && preg_match("/^$mobileprefix/", $entry->PM_CODE)) {
			$mobile[] = $entry;
		}
		elseif ($entry->PM_TYPE = "Telephone" && !empty($landlineprefix) && preg_match("/^$landlineprefix/", $entry->PM_CODE)) {
			$landline[] = $entry;
		}
		elseif ($entry->PM_TYPE = "Telephone") {
			$other[] = $entry;
		}
		else {
			$address[] = $entry;
		}
	}
	
	return array("mobile" => $mobile, 
				"landline" => $landline, 
				"other" => $other,
				"address" => $address);
}

function vtm_generate_phone($characterID, $prefix) {
	global $wpdb;
	
	// Guess a number
	// <prefix><characterID><number>
	// where <number> = <epoch-reversed>
	$epoch = time();
	$revepoch = strrev($epoch);
	
	$length = get_option('vtm_pm_telephone_digits',11) - strlen($prefix);
	
	$number = "$characterID$revepoch";
		
	// truncate/expand <number> to required number of digits
	if (strlen($number) > $length) {
		$number = substr($number, 0, $length);
	}
	elseif (strlen($number) < $length) {
		for ($i = strlen($number) ; $i < $length ; $i++) {
			$number .= rand(0,9);
		}
	}
	
	// Double-check phone number is unique
	// If not, increment <number> and try again
	// Exit if you can't find a unique number within x tries
	$sql = "SELECT COUNT(ID) 
		FROM " . VTM_TABLE_PREFIX . "CHARACTER_PM_ADDRESS
		WHERE PM_CODE = %s";
	$prepsql = $wpdb->prepare($sql, "$prefix$number");
	$ismatch = $wpdb->get_var($prepsql);
	if ($ismatch) {
		$loopcount = 0;
		do {
			$number++;
			$prepsql = $wpdb->prepare($sql, "$prefix$number");
			$ismatch = $wpdb->get_var($prepsql);
			$loopcount++;
			
		} while ($ismatch && $loopcount < 10);
		
	}

	$number = "$prefix$number";
	return $number;
}
?>