<?php

function vtm_viewcharacter_content_filter($content) {

  if (is_page(vtm_get_stlink_page('viewCharSheet'))) {
		if (is_user_logged_in()) {
			$content .= vtm_get_viewcharacter_content();
		} else {
			$content .= "<p>You must be logged in to view this content.</p>";
		}
  }
  // otherwise returns the database content
  return $content;
}

add_filter( 'the_content', 'vtm_viewcharacter_content_filter' );


function vtm_get_viewcharacter_content() {
	global $wpdb;
	global $vtmglobal;
	$character   = vtm_establishCharacter('');
	$characterID = vtm_establishCharacterID($character);
	
	$mycharacter = new vtmclass_character();
	$mycharacter->load($characterID);

	$maxrating = $mycharacter->max_rating > 5 ? 10 : 5;

	/*
	if ($vtmglobal['config']->WEB_COLUMNS == 1)
		$divder = "<tr><td class='vtmhr'><hr /></td></tr>"; // divider
	else
		$divder = "<tr><td class='vtmhr' colspan=" . $vtmglobal['config']->WEB_COLUMNS . "><hr /></td></tr>"; // divider
	*/
	$divder = "<hr />";
	
	$content = "<div class='gvplugin vtmpage_" . $vtmglobal['config']->WEB_PAGEWIDTH . "' id=\"csheet\">";
	
	//---- TOP CHARACTER INFO ----
	$c_tableleft = "<div class='vtmsubsection'><table><tbody>
		<tr><td class='vtmcol_key'>Character</td><td>". vtm_formatOutput($mycharacter->name) . "</td></tr>
		<tr><td class='vtmcol_key'>Domain</td><td>"   . vtm_formatOutput($mycharacter->domain) . "</td></tr>
		<tr><td class='vtmcol_key'>Sect</td><td>"     . vtm_formatOutput($mycharacter->sect) . "</td></tr>
		</tbody></table></div>";
	$c_tablemid = "<div class='vtmsubsection'><table><tbody>
		<tr><td class='vtmcol_key'>Clan</td><td>"        . vtm_formatOutput($mycharacter->private_clan) . "</td></tr>
		<tr><td class='vtmcol_key'>Public Clan</td><td>" . vtm_formatOutput($mycharacter->clan) . "</td></tr>
		<tr><td class='vtmcol_key'>Sire</td><td>"        . vtm_formatOutput($mycharacter->sire) . "</td></tr>
		</tbody></table></div>";
	if ($vtmglobal['config']->USE_NATURE_DEMEANOUR == 'Y') {
		$c_tableright = "<div class='vtmsubsection'><table><tbody>
			<tr><td class='vtmcol_key'>Generation</td><td>" . vtm_formatOutput($mycharacter->generation) . "</td></tr>
			<tr><td class='vtmcol_key'>Nature</td><td>"     . vtm_formatOutput($mycharacter->nature) . "</td></tr>
			<tr><td class='vtmcol_key'>Demeanour</td><td>"  . vtm_formatOutput($mycharacter->demeanour) . "</td></tr>
			</tbody></table></div>";
	} else {
		$c_tableright = "<div class='vtmsubsection'><table><tbody>
			<tr><td class='vtmcol_key'>Generation</td><td>" . vtm_formatOutput($mycharacter->generation) . "</td></tr>
			<tr><td class='vtmcol_key'>Date of Birth</td><td>" . vtm_formatOutput($mycharacter->date_of_birth) . "</td></tr>
			<tr><td class='vtmcol_key'>Date of Embrace</td><td>" . vtm_formatOutput($mycharacter->date_of_embrace) . "</td></tr>
			</tbody></table></div>";
	}
	
	$content .= "<div class='vtmsection'>\n";
	$content .= "$c_tableleft\n$c_tablemid\n$c_tableright\n";
	$content .= "</div><!-- end section -->\n";
	
	/*if ($vtmglobal['config']->WEB_COLUMNS == 3) {
		$content .= "<tr>
			<td class='vtm_colnarrow'>$c_tableleft</td>
			<td class='vtm_colnarrow'>$c_tablemid</td>
			<td class='vtm_colnarrow'>$c_tableright</td>
			</tr>";
	} else {
		$content .= "
			<tr><td class='vtm_colfull'>$c_tableleft</td></tr>
			<tr><td class='vtm_colfull'>$c_tablemid</td></tr>
			<tr><td class='vtm_colfull'>$c_tableright</td></tr>";
	}*/
	$content .= $divder;
	
	//---- ATTRIBUTES ----
	$physical = $mycharacter->getAttributes("Physical");
	$social   = $mycharacter->getAttributes("Social");
	$mental   = $mycharacter->getAttributes("Mental");
	
	$c_tableleft = "<div class='vtmsubsection'><table><tr><td colspan=3><h4>Physical</h4></td></tr>";
	for ($i=0;$i<3;$i++) {
		$statname = isset($physical[$i]->name)      ? vtm_formatOutput($physical[$i]->name) : '';
		$statspec = isset($physical[$i]->specialty) ? vtm_formatOutput($physical[$i]->specialty) : '';
		$statlvl  = isset($physical[$i]->level)     ? $physical[$i]->level : '';
		$max = max($statlvl, $maxrating);
		$c_tableleft .= "<tr>
				<td class='vtmcol_key'>$statname</td>
				<td class='vtmcol_spec'>$statspec </td>
				<td class='vtmdot_{$maxrating}'>" . vtm_numberToDots($max, $statlvl) . "</td>
			</tr>";
	}
	$c_tableleft .= "</table></div>\n";
	$c_tablemid = "<div class='vtmsubsection'><table><tr><td colspan=3><h4>Social</h4></td></tr>";
	for ($i=0;$i<3;$i++) {
		$statname = isset($social[$i]->name)      ? vtm_formatOutput($social[$i]->name) : '';
		$statspec = isset($social[$i]->specialty) ? vtm_formatOutput($social[$i]->specialty) : '';
		$statlvl  = isset($social[$i]->level)     ? $social[$i]->level : '';
		$max = max($statlvl, $maxrating);
		$c_tablemid .= "<tr>
				<td class='vtmcol_key'>$statname</td>
				<td class='vtmcol_spec'>$statspec </td>
				<td class='vtmdot_{$maxrating}'>" . vtm_numberToDots($max, $statlvl) . "</td>
			</tr>";
	}
	$c_tablemid .= "</table></div>\n";
	$c_tableright = "<div class='vtmsubsection'><table><tr><td colspan=3><h4>Mental</h4></td></tr>";
	for ($i=0;$i<3;$i++) {
		$statname = isset($mental[$i]->name)      ? vtm_formatOutput($mental[$i]->name) : '';
		$statspec = isset($mental[$i]->specialty) ? vtm_formatOutput($mental[$i]->specialty) : '';
		$statlvl  = isset($mental[$i]->level)     ? $mental[$i]->level : '';
		$max = max($statlvl, $maxrating);
		$c_tableright .= "<tr>
				<td class='vtmcol_key'>$statname</td>
				<td class='vtmcol_spec'>$statspec </td>
				<td class='vtmdot_{$maxrating}'>" . vtm_numberToDots($max, $statlvl) . "</td>
			</tr>";
	}
	$c_tableright .= "</table></div>\n";

	$content .= "<div class='vtmsection'>\n";
	$content .= "$c_tableleft\n$c_tablemid\n$c_tableright\n";
	$content .= "</div><!-- end section -->\n";

	/*if ($vtmglobal['config']->WEB_COLUMNS == 3) {
		$content .= "
			<tr><td colspan=3><h3>Attributes</h3></td></tr>
			<tr>
			<td class='vtm_colnarrow'>$c_tableleft</td>
			<td class='vtm_colnarrow'>$c_tablemid</td>
			<td class='vtm_colnarrow'>$c_tableright</td>
			</tr>";
	} else {
		$content .= "
			<tr><td><h3>Attributes</h3></td></tr>
			<tr><td class='vtm_colfull'>$c_tableleft</td></tr>
			<tr><td class='vtm_colfull'>$c_tablemid</td></tr>
			<tr><td class='vtm_colfull'>$c_tableright</td></tr>";
	}*/

	//---- ABILITIES ----
	$content .= $divder;

	$talent    = $mycharacter->getAbilities("Talents");
	$skill     = $mycharacter->getAbilities("Skills");
	$knowledge = $mycharacter->getAbilities("Knowledges");
	
	$c_tableleft = "<div class='vtmsubsection'><table><tr><td colspan=3><h4>Talents</h4></td></tr>";
	for ($i=0;$i<count($talent);$i++) {
		$max = max($talent[$i]->level, $maxrating);
		if ($talent[$i]->level > 0)
			$c_tableleft .= "<tr>
					<td class='vtmcol_key'>" . vtm_formatOutput($talent[$i]->skillname) . "</td>
					<td class='vtmcol_spec'>" . vtm_formatOutput($talent[$i]->specialty) . "</td>
					<td class='vtmdot_{$maxrating}'>" . vtm_numberToDots($max, $talent[$i]->level) . "</td>
				</tr>";
	}
	$c_tableleft .= "</table></div>\n";
	$c_tablemid = "<div class='vtmsubsection'><table><tr><td colspan=3><h4>Skills</h4></td></tr>";
	for ($i=0;$i<count($skill);$i++) {
		$max = max($skill[$i]->level, $maxrating);
		if ($skill[$i]->level > 0)
			$c_tablemid .= "<tr>
				<td class='vtmcol_key'>" . vtm_formatOutput($skill[$i]->skillname) . "</td>
				<td class='vtmcol_spec'>" . vtm_formatOutput($skill[$i]->specialty) . "</td>
				<td class='vtmdot_{$maxrating}'>" . vtm_numberToDots($max, $skill[$i]->level) . "</td>
			</tr>";
	}
	$c_tablemid .= "</table></div>\n";
	$c_tableright = "<div class='vtmsubsection'><table><tr><td colspan=3><h4>Knowledges</h4></td></tr>";
	for ($i=0;$i<count($knowledge);$i++) {
		$max = max($knowledge[$i]->level, $maxrating);
		if ($knowledge[$i]->level > 0)
			$c_tableright .= "<tr>
				<td class='vtmcol_key'>" . vtm_formatOutput($knowledge[$i]->skillname) . "</td>
				<td class='vtmcol_spec'>" . vtm_formatOutput($knowledge[$i]->specialty) . "</td>
				<td class='vtmdot_{$maxrating}'>" . vtm_numberToDots($max, $knowledge[$i]->level) . "</td>
			</tr>";
	}
	$c_tableright .= "</table></div>\n";
	
	/*if ($vtmglobal['config']->WEB_COLUMNS == 3) {
		$content .= "
			<tr><td colspan=3><h3>Abilities</h3></td></tr>
			<tr>
			<td class='vtm_colnarrow'>$c_tableleft</td>
			<td class='vtm_colnarrow'>$c_tablemid</td>
			<td class='vtm_colnarrow'>$c_tableright</td>
			</tr>";
	} else {
		$content .= "
			<tr><td><h3>Abilities</h3></td></tr>
			<tr><td class='vtm_colfull'>$c_tableleft</td></tr>
			<tr><td class='vtm_colfull'>$c_tablemid</td></tr>
			<tr><td class='vtm_colfull'>$c_tableright</td></tr>";
	}*/
	$content .= "<div class='vtmsection'>\n";
	$content .= "$c_tableleft\n$c_tablemid\n$c_tableright\n";
	$content .= "</div><!-- end section -->\n";
	
	
	//---- BACKGROUND, DISCIPLINES AND OTHER TRAITS ----
	
	$content .= $divder;
	$backgrounds = $mycharacter->getBackgrounds();
	$disciplines = $mycharacter->getDisciplines();
	
	$sql = "SELECT NAME, PARENT_ID FROM " . VTM_TABLE_PREFIX . "SKILL_TYPE;";
	$allgroups = $wpdb->get_results($sql);	
	
	$secondarygroups = array();
	foreach ($allgroups as $group) {
		if ($group->PARENT_ID > 0)
			array_push($secondarygroups, $group->NAME);
	}	

	$secondary = array();
	foreach ($secondarygroups as $group)
			$secondary = array_merge($mycharacter->getAbilities($group), $secondary);	
	
	$c_tableleft = "<div class='vtmsubsection'><table><tr><td colspan=3><h4>Backgrounds</h4></td></tr>";
	for ($i=0;$i<count($backgrounds);$i++) {
		$max = max($backgrounds[$i]->level, $maxrating);
		$c_tableleft .= "<tr>
				<td class='vtmcol_key'>" . vtm_formatOutput($backgrounds[$i]->background) . "</td>
				<td class='vtmcol_spec'>" . (!empty($backgrounds[$i]->sector) ?  vtm_formatOutput($backgrounds[$i]->sector) : vtm_formatOutput($backgrounds[$i]->comment)) . "</td>
				<td class='vtmdot_{$maxrating}'>" . vtm_numberToDots($max, $backgrounds[$i]->level) . "</td>
			</tr>";
	}
	$c_tableleft .= "</table></div>\n";
	$c_tablemid = "<div class='vtmsubsection'><table><tr><td colspan=3><h4>Disciplines</h4></td></tr>";
	for ($i=0;$i<count($disciplines);$i++) {
		$max = max($disciplines[$i]->level, $maxrating);
		if ($disciplines[$i]->level > 0)
			$c_tablemid .= "<tr>
				<td class='vtmcol_key'>" . vtm_formatOutput($disciplines[$i]->name) . "</td>
				<td class='vtmcol_spec'>&nbsp;</td>
				<td class='vtmdot_{$maxrating}'>" . vtm_numberToDots($max, $disciplines[$i]->level) . "</td>
			</tr>";
	}
	// COMBO DISCIPLINES
	$combo = $mycharacter->combo_disciplines;
	foreach ($combo as $id => $disc) {
		if (!strstr($disc,"PENDING"))
			$c_tablemid .= "<tr><td colspan=3>" . vtm_formatOutput($disc) . "</td></tr>";
	}
	$c_tablemid .= "</table></div>\n";
	$c_tableright = "<div class='vtmsubsection'><table><tr><td colspan=3><h4>Other Traits</h4></td></tr>";
	for ($i=0;$i<count($secondary);$i++) {
		$max = max($secondary[$i]->level, $maxrating);
		if ($secondary[$i]->level > 0)
			$c_tableright .= "<tr>
				<td class='vtmcol_key'>" . vtm_formatOutput($secondary[$i]->skillname) . "</td>
				<td class='vtmcol_spec'>" . vtm_formatOutput($secondary[$i]->specialty) . "</td>
				<td class='vtmdot_{$maxrating}'>" . vtm_numberToDots($max, $secondary[$i]->level) . "</td>
			</tr>";
	}
	$c_tableright .= "</table></div>\n";
	
	/*if ($vtmglobal['config']->WEB_COLUMNS == 3) {
		$content .= "
			<tr>
			<td class='vtm_colnarrow'>$c_tableleft</td>
			<td class='vtm_colnarrow'>$c_tablemid</td>
			<td class='vtm_colnarrow'>$c_tableright</td>
			</tr>";
	} else {
		$content .= "
			<tr><td class='vtm_colfull'>$c_tableleft</td></tr>$divder
			<tr><td class='vtm_colfull'>$c_tablemid</td></tr>$divder
			<tr><td class='vtm_colfull'>$c_tableright</td></tr>";
	}*/
	$content .= "<div class='vtmsection'>\n";
	$content .= "$c_tableleft\n$c_tablemid\n$c_tableright\n";
	$content .= "</div><!-- end section -->\n";

	
	//---- MERITS, FLAWS, VIRTUES, WILLPOWER, PATH AND BLOOD ----
	$content .= $divder;

	$merits = $mycharacter->meritsandflaws;
	$virtues = $mycharacter->getAttributes("Virtue");

	$c_tableleft = "<div class='vtmsubsection_wide'><table><tr><td><h4>Merits and Flaws</h4></td></tr>";
	$c_tableleft .= "<tr>";
	if (count($merits) > 0) {
		$c_tableleft .= "<td><table>";
		foreach ($merits as $merit) {
			if ($merit->pending == 0) {
				$c_tableleft .= "<tr><td class='vtmcol_key'>" . vtm_formatOutput($merit->name) . "</td>";
				$c_tableleft .= "<td class='vtmcol_spec'>" . (empty($merit->comment) ? "&nbsp;" : vtm_formatOutput($merit->comment)) . "</td>";
				$c_tableleft .= "<td>" . $merit->level . "</td></tr>\n";
			}
		}
		$c_tableleft .= "</table></td>";
	} else {
		$c_tableleft .= "<td>&nbsp;</td>";
	}
	$c_tableleft .= "</tr></table></div>\n";
	$c_tableright = "<div class='vtmsubsection'><table><tr><td colspan=3><h4>Virtues</h4></td></tr>";
	for ($i=0;$i<3;$i++) {
		$statname = isset($virtues[$i]->name)      ? vtm_formatOutput($virtues[$i]->name) : '';
		$statlvl  = isset($virtues[$i]->level)     ? $virtues[$i]->level : '';
		$c_tableright .= "<tr>
				<td class='vtmcol_key'>" . $statname . "</td>
				<td class='vtmcol_spec'>&nbsp;</td>
				<td class='vtmdot_5'>" . vtm_numberToDots(5, $statlvl) . "</td>
			</tr>\n";
	}
	$c_tableright .= "<tr><td colspan=3><hr /></td></tr>\n";
	$c_tableright .= "<tr><td colspan=3><h4>Willpower</h4></td></tr>";
	$c_tableright .= "<tr><td colspan=3 class='vtmdot_10 vtmdotwide'>" . vtm_numberToDots(10, $mycharacter->willpower) . "</td></tr>\n";
	$c_tableright .= "<tr><td colspan=3 class='vtmdot_10 vtmdotwide'>" . vtm_numberToBoxes(10, $mycharacter->willpower - $mycharacter->current_willpower) . "</td></tr>\n";
	$c_tableright .= "<tr><td colspan=3><hr /></td></tr>\n";
	$c_tableright .= "<tr><td colspan=2><h4>" . vtm_formatOutput($mycharacter->path_of_enlightenment) . "</h4></td><td><h4>" . $mycharacter->path_rating . "</h4></td></tr>\n";
	$c_tableright .= "<tr><td colspan=3><hr /></td></tr>\n";
	$c_tableright .= "<tr><td colspan=3><h4>Bloodpool</h4></td></tr>";
	$c_tableright .= "<tr><td colspan=3 class='vtmdot_10 vtmdotwide'>" . vtm_numberToBoxes($mycharacter->bloodpool,0) . "</td></tr>\n";
	$c_tableright .= "</table></div>\n";

	/*if ($vtmglobal['config']->WEB_COLUMNS == 3) {
		$content .= "
			<tr>
			<td class='vtm_colwide' colspan = 2>$c_tableleft</td>
			<td class='vtm_colnarrow'>$c_tableright</td>
			</tr>";
	} else {
		$content .= "
			<tr><td class='vtm_colfull'>$c_tableleft</td></tr>
			<tr><td class='vtm_colfull'>$c_tableright</td></tr>";
	}*/
	$content .= "<div class='vtmsection'>\n";
	$content .= "$c_tableleft\n$c_tableright\n";
	$content .= "</div><!-- end section -->\n";

	
	//---- MAGIK ----
	$content .= $divder;

	$rituals = $mycharacter->rituals;
	$majikpaths_primary  = $mycharacter->primary_paths;
	$majikpaths_secondary  = $mycharacter->secondary_paths;

	$c_tableleft = "<div class='vtmsubsection_wide'><table><tr><td><h4>Rituals</h4></td></tr>";
	$c_tableleft .= "<tr>";
	if (count($rituals) > 0) {
		$c_tableleft .= "<td><table>";
		foreach ($rituals as $majikdiscipline => $rituallist) {
			$c_tableleft .= "<tr><td colspan=2><strong>" . vtm_formatOutput($majikdiscipline) . " Rituals</strong></td></tr>\n";
			foreach ($rituallist as $ritual) {
				if ($ritual['pending'] == 0)
					$c_tableleft .= "<tr><td class='vtmcol_key'>Level " . $ritual['level'] . "</td><td>" . vtm_formatOutput($ritual['name']) . "</td></tr>\n";
			} 
		}
		$c_tableleft .= "</table></td>";
	} else {
		$c_tableleft .= "<td>&nbsp;</td>";
	}
	$c_tableleft .= "</tr></table></div>\n";
	$c_tableright = "<div class='vtmsubsection'><table><tr><td><h4>Paths</h4></td></tr>";
	$c_tableright .= "<tr>";
	
	if (count($majikpaths_primary)>0) {
		$c_tableright .= "<td><table>\n";
		for ($i=0;$i<count($disciplines);$i++) {
			$discipline = $disciplines[$i]->name;
			// Does this discipline have paths associated with it?
			if (isset($majikpaths_primary[$discipline])) {
				$c_tableright .= "<tr><td colspan=2><strong>" . vtm_formatOutput($discipline) . "</strong></td></tr>\n";
				foreach ($majikpaths_primary[$discipline] as $path => $info) {
					$c_tableright .= "<tr><td class='vtmcol_key_wide'>" . vtm_formatOutput($path) . " (Primary)</td><td class='vtmdot_5'>" . vtm_numberToDots(5, $info[0]) . "</td></tr>";
				}
				if (isset($majikpaths_secondary[$discipline])) {
					foreach ($majikpaths_secondary[$discipline] as $path => $info) {
						$c_tableright .= "<tr><td class='vtmcol_key_wide'>" . vtm_formatOutput($path) . "</td><td class='vtmdot_5'>" . vtm_numberToDots(5, $info[0]) . "</td></tr>";
					}
				}
			}
		}
		$c_tableright .= "</table></td>";

	} else {
				$c_tableright .= "&nbsp;";
	}
	$c_tableright .= "</tr></table></div>\n";
	

	/*if ($vtmglobal['config']->WEB_COLUMNS == 3) {
		$content .= "
			<tr>
			<td class='vtm_colwide' colspan = 2>$c_tableleft</td>
			<td class='vtm_colnarrow'>$c_tableright</td>
			</tr>";
	} else {
		$content .= "
			<tr><td class='vtm_colfull'>$c_tableleft</td></tr>$divder
			<tr><td class='vtm_colfull'>$c_tableright</td></tr>";
	}*/
	$content .= "<div class='vtmsection'>\n";
	$content .= "$c_tableleft\n$c_tableright\n";
	$content .= "</div><!-- end section -->\n";

	//$content .= "</table>"; */
	$content .= "</div>\n";
	
	return $content;
}


?>