<?php

function vtm_default_chargen_settings() {
	global $wpdb;
	global $vtmglobal;

	$defaultgenid = $vtmglobal['config']->DEFAULT_GENERATION_ID;
	$defaultgenlvl = $wpdb->get_var($wpdb->prepare("SELECT NAME FROM " . VTM_TABLE_PREFIX . "GENERATION WHERE ID = %s", $defaultgenid));
	$limitgenlvl = $defaultgenlvl - 5;
	$limitgenid = $wpdb->get_var($wpdb->prepare("SELECT ID FROM " . VTM_TABLE_PREFIX . "GENERATION WHERE NAME = %s", $limitgenlvl));
	$limitgenid = $limitgenid ? $limitgenid : 1;

	return array(
		'attributes-method'    => "PST", 	// or 'point'
		'attributes-primary'   => 7,
		'attributes-secondary' => 5,
		'attributes-tertiary'  => 3,
		'attributes-points'    => 0,
		'abilities-primary'    => 13,
		'abilities-secondary'  => 9,
		'abilities-tertiary'   => 5,
		'abilities-max'        => 3,
		'disciplines-points'   => 3,
		'backgrounds-points'   => 5,
		'virtues-free-dots'    => 'humanityvirtues',  // 'yes', 'no', 'humanityonly', 'humanityvirtues'
		'virtues-points'       => 7,
		'road-multiplier'      => 1,
		'merits-max'           => 7,
		'flaws-max'            => 7,
		'freebies-points'      => 15,
		'rituals-method'       => 'point',  // 'discipline', 'accumulate', 'point' or 'none'
		'rituals-points'       => 1,
		'limit-sect-method'    => 'none', 	// 'none', 'only', 'exclude'
		'limit-sect-id'        => 1,
		'limit-road-method'    => 'none', 	// 'none', 'only', 'exclude'
		'limit-road-id'        => 1,
		'limit-generation-low' => $limitgenid,   		// generation ID
		'primarypath-select'   => 1,					// Allow non-default path to be select
		'primarypath-default'  => 'discipline',	        // 'discipline', 'clan'
	);

}

function vtm_chargen_flow_steps() {
	global $wpdb;
	global $vtmglobal;
	
	$spent   = vtm_get_chargen_xp_spent();
	$total   = vtm_get_total_xp(0, $vtmglobal['characterID']);
	$pending = vtm_get_pending_xp(0, $vtmglobal['characterID']);
	$xp      = $total - $pending + $spent;
	//echo "<p>Total: $total, Pending: $pending, Spent on this character: $spent</p>";

	$questions     = vtm_get_chargen_questions(true); // count
	$chargenstatus = $wpdb->get_var($wpdb->prepare("SELECT cgs.NAME FROM " . VTM_TABLE_PREFIX . "CHARACTER c, " . VTM_TABLE_PREFIX . "CHARGEN_STATUS cgs WHERE c.ID = %s AND c.CHARGEN_STATUS_ID = cgs.ID",$vtmglobal['characterID']));
	$feedback      = $wpdb->get_var( $wpdb->prepare("SELECT NOTE_FROM_ST FROM " . VTM_TABLE_PREFIX . "CHARACTER_GENERATION WHERE CHARACTER_ID = %s", $vtmglobal['characterID']));
	$rituals       = vtm_get_chargen_rituals(OBJECT, true); // count
	$paths         = vtm_get_chargen_paths();
		
	$buttons = array ();
	
	$lasttag  = 'select_template';
	$thistag  = 'select_template';
	$starttag = 'basic_info';
	$nexttag  = '';
	$endtag   = 'submit';
	
	$buttons[$thistag] = array(
		'title'      => "Select Template", 
		'function'   => 'vtm_render_choose_template',
		'validate'   => 'vtm_validate_template',
		'save'       => 'vtm_save_template',
		'order'		 => 0,
		'back'       => $lasttag,
		'display'    => 0
	);
	
	if (!empty($feedback)) {
		$lasttag = $thistag;
		$thistag = 'st_feedback';
		$buttons[$lasttag]['next'] = $thistag;
		$buttons[$thistag] = array(
			'title'      => "Storyteller Feedback", 
			'function'   => 'vtm_render_feedback',
			'validate'   => 'vtm_validate_dummy',
			'save'       => 'vtm_save_dummy',
			'order'		 => 1,
			'back'       => $lasttag,
			'display'    => 1
		);
		$starttag = $thistag;
	}
	
	$lasttag = $thistag;
	$thistag = 'basic_info';
	$buttons[$lasttag]['next'] = $thistag;
	$buttons[$thistag] = array(	
		'title'      => "Basic Information", 
		'function'   => 'vtm_render_basic_info',
		'validate'   => 'vtm_validate_basic_info',
		'save'       => 'vtm_save_basic_info',
		'order'		 => 2,
		'back'       => $lasttag,
		'display'    => 1
	);

	if ( ($vtmglobal['settings']['attributes-method'] == 'PST' && (
			$vtmglobal['settings']['attributes-tertiary'] > 0 || 
			$vtmglobal['settings']['attributes-secondary'] > 0 || 
			$vtmglobal['settings']['attributes-primary'] > 0)) ||
		 ($vtmglobal['settings']['attributes-method'] != 'PST' && $vtmglobal['settings']['attributes-points'] > 0)) {

		$lasttag = $thistag;
		$thistag = 'attributes';
		$buttons[$lasttag]['next'] = $thistag;
		$buttons[$thistag] = array(	
			'title'      => "Attributes", 
			'function'   => 'vtm_render_attributes',
			'validate'   => 'vtm_validate_attributes',
			'save'       => 'vtm_save_attributes',
			'order'		 => 3,
			'back'       => $lasttag,
			'display'    => 1
		); 
	}
	if ( $vtmglobal['settings']['abilities-tertiary'] > 0 || 
		 $vtmglobal['settings']['abilities-secondary'] > 0 || 
		 $vtmglobal['settings']['abilities-primary'] > 0) {
			 
		$lasttag = $thistag;
		$thistag = 'abilities';
		$buttons[$lasttag]['next'] = $thistag;
		$buttons[$thistag] = array(	
			'title'      => "Abilities", 
			'function'   => 'vtm_render_abilities',
			'validate'   => 'vtm_validate_abilities',
			'save'       => 'vtm_save_abilities',
			'order'		 => 4,
			'back'       => $lasttag,
			'display'    => 1
		);
	}
	if ($vtmglobal['settings']['disciplines-points'] > 0) {
		$lasttag = $thistag;
		$thistag = 'disciplines';
		$buttons[$lasttag]['next'] = $thistag;
		$buttons[$thistag] = array(
			'title' => "Disciplines", 
			'function'   => 'vtm_render_chargen_disciplines',
			'validate'   => 'vtm_validate_disciplines',
			'save'       => 'vtm_save_disciplines',
			'order'		 => 5,
			'back'       => $lasttag,
			'display'    => 1
		);
	}
	
	if ($vtmglobal['settings']['backgrounds-points'] > 0) {
		$lasttag = $thistag;
		$thistag = 'backgrounds';
		$buttons[$lasttag]['next'] = $thistag;
		$buttons[$thistag] = array(	
			'title' => "Backgrounds", 
			'function'   => 'vtm_render_chargen_backgrounds',
			'validate'   => 'vtm_validate_backgrounds',
			'save'       => 'vtm_save_backgrounds',
			'order'		 => 6,
			'back'       => $lasttag,
			'display'    => 1
		);
	}
	if ($vtmglobal['settings']['virtues-points'] > 0) {
		$lasttag = $thistag;
		$thistag = 'virtues';
		$buttons[$lasttag]['next'] = $thistag;
		$buttons[$thistag] = array(
			'title' => "Virtues", 
			'function'   => 'vtm_render_chargen_virtues',
			'validate'   => 'vtm_validate_virtues',
			'save'       => 'vtm_save_virtues',
			'order'		 => 7,
			'back'       => $lasttag,
			'display'    => 1
		);
	}
	// if thaum, etc has been selected
	if (vtm_has_submitted_disc_with_paths() || count($paths) > 0 ) {
		//print_r($paths);
		$lasttag = $thistag;
		$thistag = 'paths';
		$buttons[$lasttag]['next'] = $thistag;
		$buttons[$thistag] = array(
			'title' => "Paths", 
			'function'   => 'vtm_render_chargen_paths',
			'validate'   => 'vtm_validate_paths',
			'save'       => 'vtm_save_paths',
			'order'		 => 8,
			'back'       => $lasttag,
			'display'    => 1
		);
	}			
	if ($vtmglobal['settings']['freebies-points'] > 0) {
		$lasttag = $thistag;
		$thistag = 'freebies';
		$buttons[$lasttag]['next'] = $thistag;
		$buttons[$thistag] = array(	
			'title' => "Freebie Points", 
			'function'   => 'vtm_render_chargen_freebies',
			'validate'   => 'vtm_validate_freebies',
			'save'       => 'vtm_save_freebies',
			'order'		 => 9,
			'back'       => $lasttag,
			'display'    => 1
		);
	}

	if ($total > 0) {
		$lasttag = $thistag;
		$thistag = 'experience';
		$buttons[$lasttag]['next'] = $thistag;
		$buttons[$thistag] = array(
			'title'      => "Spend Experience", 
			'function'   => 'vtm_render_chargen_xp',
			'validate'   => 'vtm_validate_xp',
			'save'       => 'vtm_save_xp',
			'order'		 => 10,
			'back'       => $lasttag,
			'display'    => 1
		);
	}
	if ($vtmglobal['settings']['rituals-method'] != 'none' && count($rituals) > 0) {
		$lasttag = $thistag;
		$thistag = 'rituals';
		$buttons[$lasttag]['next'] = $thistag;
		$buttons[$thistag] = array(
			'title' => "Rituals", 
			'function'   => 'vtm_render_chargen_rituals',
			'validate'   => 'vtm_validate_rituals',
			'save'       => 'vtm_save_rituals',
			'order'		 => 11,
			'back'       => $lasttag,
			'display'    => 1
		);
	}			
	
	
	$lasttag = $thistag;
	$thistag = 'finish';
	$buttons[$lasttag]['next'] = $thistag;
	$buttons[$thistag] = array(
		'title'      => "Finishing Touches", 
		'function'   => 'vtm_render_finishing',
		'validate'   => 'vtm_validate_finishing',
		'save'       => 'vtm_save_finish',
		'order'		 => 12,
		'back'       => $lasttag,
		'display'    => 1
	);
	
	// Only display if there are any background questions
	if ($questions > 0) {
		$lasttag = $thistag;
		$thistag = 'extended';
		$buttons[$lasttag]['next'] = $thistag;
		$buttons[$thistag] = array(
			'title'      => "Extended Background", 
			'function'   => 'vtm_render_chargen_extbackgrounds',
			'validate'   => 'vtm_validate_history',
			'save'       => 'vtm_save_history',
			'order'		 => 13,
			'back'       => $lasttag,
			'display'    => 1
		);
	}
	
	$title = $chargenstatus == 'Submitted' ? 'Review' : 'Submit';
	$lasttag = $thistag;
	$thistag = 'submit';
	$buttons[$lasttag]['next'] = $thistag;
	$buttons[$thistag] = array(
		'title'      => $title, 
		'function'   => 'vtm_render_chargen_submit',
		'validate'   => 'vtm_validate_submit',
		'save'       => 'vtm_save_submit',
		'order'		 => 14,
		'back'       => $lasttag,
		'display'    => 1
	);

	$vtmglobal['start_flow'] = $starttag;			
	$vtmglobal['end_flow'] = $endtag;			
	
	return $buttons;
}

function vtm_chargen_content_filter($content) {

	if (is_page(vtm_get_stlink_page('viewCharGen'))) {
		$mustbeloggedin = get_option('vtm_chargen_mustbeloggedin', '0') ? true : false;
		if (get_option('vtm_chargen_mustbeloggedin', '0') == 0 || (is_user_logged_in() && $mustbeloggedin))
			$content .= vtm_report_max_input_vars(vtm_get_chargen_content());
		else
			$content .= "<p>You must be logged in to generate a character</p>\n";
	}
	return $content;
}

add_filter( 'the_content', 'vtm_chargen_content_filter' );


function vtm_get_chargen_content() {
	global $wpdb;
	global $vtmglobal;

	// Init global variables
	$vtmglobal['characterID'] = vtm_get_chargen_characterID();
	$vtmglobal['playerID']    = vtm_get_player_id_from_characterID();
	$vtmglobal['templateID']  = vtm_get_templateid();
	$vtmglobal['settings']    = vtm_get_chargen_settings();
	$vtmglobal['dots'] = array(
		'dot1full'  => VTM_PLUGIN_URL . '/images/dot1full.' . VTM_ICON_FORMAT,
		'dot1empty' => VTM_PLUGIN_URL . '/images/dot1empty.' . VTM_ICON_FORMAT,
		'dot3'      => VTM_PLUGIN_URL . '/images/dot3.' . VTM_ICON_FORMAT,
		'dot2'      => VTM_PLUGIN_URL . '/images/dot2.' . VTM_ICON_FORMAT,
		'spacer'    => VTM_PLUGIN_URL . '/images/spacer.' . VTM_ICON_FORMAT
	);
	$vtmglobal['charGenStatus'] = vtm_get_chargen_status();
	
	$vtmglobal['flow']        = vtm_chargen_flow_steps();
	$vtmglobal['genInfo']     = vtm_calculate_generation();
	
	$output = "";
	
	$laststep = isset($_POST['step']) ? $_POST['step'] : '';
	
	if (isset($_POST['chargen-step']))
		$chargenstep = array_keys($_POST['chargen-step']);
	elseif ($vtmglobal['characterID'] > 0)
		$chargenstep = array('basic_info');
	else
		$chargenstep = array('select_template');
	
	$thisstep = array_shift($chargenstep);
	
	$output .= "<form id='chargen_form' method='post' autocomplete='off'>\n";
	
	// validate & save data from last step
	$dataok = vtm_validate_chargen($laststep);
	if ($dataok) {
		$vtmglobal['characterID'] = vtm_save_progress($laststep);
	
	} 
	else {
		$thisstep = $laststep;
	}
	
	if (!isset($vtmglobal['flow'][$thisstep]['function'])) {
		$thisstep = 'basic_info';
	}
	
	// output flow buttons
	$output .= vtm_render_flow($thisstep);
	
	$output .= "<div id='chargen-main' class='gvplugin vtmpage_" . $vtmglobal['config']->WEB_PAGEWIDTH . "'>\n";
	
	// output form to be filled in
	$formoutput = call_user_func($vtmglobal['flow'][$thisstep]['function'], $thisstep);
	$output .= $formoutput;
	
	// 3 buttons: Back, Check & Next
	$output .= vtm_render_submit($thisstep);
	$output .= "</div></form>\n";
	
	return $output;
}

function vtm_render_submit($step) {
	global $wpdb;
	global $vtmglobal;

	$sql = "SELECT COUNT(ID) FROM " . VTM_TABLE_PREFIX . "CHARGEN_TEMPLATE WHERE VISIBLE = 'Y' ORDER BY NAME";
	$result = $wpdb->get_var($sql);
	if (!$result) return "";

	$output = "";
	
	if ($step != $vtmglobal['start_flow'] && $step != 'select_template')
		$output .= "<input type='submit' name='chargen-step[" . $vtmglobal['flow'][$step]['back'] . "]' class='button-chargen-step' value='< Back' />\n";
	if ($step != $vtmglobal['start_flow'] && $step != 'select_template' && $step != $vtmglobal['end_flow'] && $vtmglobal['charGenStatus'] != 'Submitted')
		$output .= "<input type='submit' name='chargen-step[" . $step . "]' class='button-chargen-step' value='Update' />\n";
	if ($step != $vtmglobal['end_flow'])
		$output .= "<input type='submit' name='chargen-step[" . $vtmglobal['flow'][$step]['next'] . "]' class='button-chargen-step' value='Next >' />\n";
	elseif ($vtmglobal['charGenStatus'] != 'Submitted')
		$output .= "<input type='submit' name='chargen-submit' class='button-chargen-step' value='Submit for Approval' />\n";

	return $output;
}


function vtm_get_step() {
	global $vtmglobal;

	$step = 0;
	
	// output step based on what button has been pressed
	if (isset($_POST['chargen-step'])) {
		$buttons = array_keys($_POST['chargen-step']);
		$step = $buttons[0];
	}
	elseif (isset($_POST['chargen-submit'])) {
		$step = count($vtmglobal['flow']);
	}
	elseif (vtm_isST() && $vtmglobal['characterID'] > 0) {
		$step = 1;
	}
	
	//echo "<li>Step $step ($vtmglobal['characterID'], $vtmglobal['templateID'])</li>\n";
	
	return $step;
}

function vtm_render_flow($step) {
	global $vtmglobal;

	$output = "";
		
	//$buttons = vtm_chargen_flow_steps();
	
	$order = array_combine(array_keys($vtmglobal['flow']),array_column($vtmglobal['flow'],'order'));
	asort($order);
	
	$output .= "<div id='vtm-chargen-flow'>\n";	
	$output .= "<input type='hidden' name='selected_template' value='{$vtmglobal['templateID']}' />\n";
	$output .= "<input type='hidden' name='characterID' value='{$vtmglobal['characterID']}' />\n";
	$output .= "<input type='hidden' name='step' value='$step' />\n";
	
	if ($vtmglobal['flow'][$step]['display'] == 1) {
		$output .= "<ul>\n";
		$i = 0;
		foreach ($order as $tag => $discard) {
			if ($vtmglobal['flow'][$tag]['display'] == 1) {
				$steptitle  = $vtmglobal['flow'][$tag]['title'];
				$stepno    = $i+1;
				if ($step == $tag) {
					$output .= "<li class='step-button step-selected'><span><strong>Step $stepno:</strong> $steptitle</span></li>\n";
				} 
				else {
					$output .= "<li class='step-button step-enable'><input type='submit' name='chargen-step[$tag]' class='button-chargen-step' value='Step $stepno: $steptitle' /></li>\n";
				}
				$i++;
			}
		}
		$output .= "</ul>\n";
	}
	$output .= "</div>\n";

	return $output;

}



function vtm_render_basic_info($step) {
	global $wpdb;
	global $vtmglobal;

	$output = "";
	$nodatafail = 0;
	
	$submitted = $vtmglobal['charGenStatus'] == 'Submitted';
	$clans    = vtm_get_clans();
	$natures  = vtm_get_natures();
	
	$vtmglobal['characterID'] = $vtmglobal['characterID'] ? $vtmglobal['characterID'] : (isset($_POST['characterID']) ? $_POST['characterID'] : -1);
	
	if ($vtmglobal['characterID'] > 0) {
	
		// get from database
		$sql = "SELECT characters.NAME as charactername, 
					characters.EMAIL, 
					characters.WORDPRESS_ID, 
					characters.PLAYER_ID, 
					players.NAME as player,
					characters.PUBLIC_CLAN_ID,
					characters.PRIVATE_CLAN_ID,
					characters.NATURE_ID,
					characters.DEMEANOUR_ID,
					characters.CONCEPT,
					characters.SECT_ID,
					chargen.EMAIL_CONFIRMED
				FROM
					" . VTM_TABLE_PREFIX . "CHARACTER characters
					LEFT JOIN (
						SELECT EMAIL_CONFIRMED, CHARACTER_ID
						FROM " . VTM_TABLE_PREFIX . "CHARACTER_GENERATION
						WHERE CHARACTER_ID = %s
					) chargen
					ON chargen.CHARACTER_ID = characters.ID,
					" . VTM_TABLE_PREFIX . "PLAYER players
				WHERE
					characters.PLAYER_ID = players.ID
					AND characters.ID = %s";
		$sql = $wpdb->prepare($sql, $vtmglobal['characterID'], $vtmglobal['characterID']);
		//echo "SQL: $sql<br />\n";
		$result = $wpdb->get_row($sql);
		//print_r($result);
		
		$email      = $result->EMAIL;
		$confirmed  = $result->EMAIL_CONFIRMED;
		$login      = $result->WORDPRESS_ID;
		$playerid   = $result->PLAYER_ID;
		$sectid     = $result->SECT_ID;
		$playername = $result->player;
		$shownew    = 'off';
		$character  = vtm_formatOutput($result->charactername);
		
		$pub_clan    = $result->PUBLIC_CLAN_ID;
		$priv_clan   = $result->PRIVATE_CLAN_ID;
		$natureid    = $result->NATURE_ID;
		$demeanourid = $result->DEMEANOUR_ID;
		$concept     = vtm_formatOutput($result->CONCEPT);
		$playerset   = 1;
		
	
	} else {
		$email      = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
		$login      = isset($_POST['wordpress_id']) ? $_POST['wordpress_id'] : '';
		$playerid   = isset($_POST['playerID']) ? $_POST['playerID'] : '';
		$playername = isset($_POST['player']) ? $_POST['player'] : '';
		$shownew    = isset($_POST['newplayer']) ? $_POST['newplayer'] : 'off';
		$character  = isset($_POST['character']) ? vtm_formatOutput($_POST['character']) : '';
		$concept    = isset($_POST['concept']) ? vtm_formatOutput($_POST['concept']) : '';
		
		$pub_clan    = isset($_POST['pub_clan'])  ? $_POST['pub_clan']  : 0;
		$priv_clan   = isset($_POST['priv_clan']) ? $_POST['priv_clan'] : 0;
		$natureid    = isset($_POST['nature'])    ? $_POST['nature']    : 0;
		$demeanourid = isset($_POST['demeanour']) ? $_POST['demeanour'] : 0;
		$playerset   = 0;
		$confirmed   = 'N';
		
		if (isset($_POST['sect']))
			$sectid = $_POST['sect'];
		elseif ($vtmglobal['settings']['limit-sect-method'] == 'only')
			$sectid = $vtmglobal['settings']['limit-sect-id'];
		else
			$sectid = $vtmglobal['config']->HOME_SECT_ID;
		
		if (is_user_logged_in()) {
			$current_user = wp_get_current_user();
			$userid = $current_user->ID;
			
			if (empty($email)) $email = $current_user->user_email;
			
			$sql = "SELECT ID FROM " . VTM_TABLE_PREFIX . "CHARACTER WHERE WORDPRESS_ID = %s";
			$sql = $wpdb->prepare($sql, $current_user->user_login);
			$check = $wpdb->get_results($sql);
			if (empty($login) && count($check) == 0) $login = $current_user->user_login;
			
			if (empty($playername)) {
				// find other accounts with that email to guess the player
				$otherlogins = get_users("search=$email&exclude=$userid");
				foreach ($otherlogins as $other) {
					//echo "<li>{$other->user_login}</li>\n";
					$player      = vtm_get_player_from_login($other->user_login);
					if (isset($player)) {
						$shownew    = 'off';
						$playername = $player->NAME;
						$playerid   = $player->ID;
					}
				}
			} else {
				$playerid = vtm_get_player_name($playername);
			}
		} 
	}
	$playername = vtm_formatOutput($playername);
	
	$output .= "<h3>Basic Information</h3>\n";
	$output .= "<input type='hidden' name='playerID' value='$playerid'>\n";
	$output .= "<table>
		<tr>
			<td class='vtmcol_key'>Character Name*:</td>
			<td>\n";
	if ($submitted)
		$output .= $character;
	else
		$output .= "<input type='text' name='character' value='$character'>\n";
	$output .= " (ID: {$vtmglobal['characterID']})</td>
		</tr>
		<tr>
			<td class='vtmcol_key'>Player Name*:</td>\n";
	if ($playerset) {
		$output .= "<td>$playername<input type='hidden' name='player' value='$playername'>\n";
	
	} else {
		$output .= "<td><input type='text' name='player' value='$playername'>\n";
		if ($shownew)
			$output .= "<input type='checkbox' name='newplayer' " . checked( 'on', $shownew, false) . "> : I am a new player";
	}
	$output .= "</td>
		</tr>
		<tr>
			<td class='vtmcol_key'>Actual Clan*:</td>
			<td>\n";
	if ($submitted) {
		$output .= $wpdb->get_var($wpdb->prepare("SELECT NAME FROM " . VTM_TABLE_PREFIX . "CLAN WHERE ID = %s", $priv_clan));
	} 
	elseif (count($clans) == 0) {
		$nodatafail = 1;
	}
	else {
		
		$output .= "<select name='priv_clan'>\n";
		foreach ($clans as $clan) {
			$output .= "<option value='{$clan->ID}' " . selected( $clan->ID, $priv_clan, false) . ">" . vtm_formatOutput($clan->NAME) . "</option>\n";
		}
		$output .= "</select>\n";
	}
	$output .= "</td>
		</tr>
		<tr>
			<td class='vtmcol_key'>Public Clan:</td>
			<td>\n";
	if ($submitted) {
		$output .= $wpdb->get_var($wpdb->prepare("SELECT NAME FROM " . VTM_TABLE_PREFIX . "CLAN WHERE ID = %s", $pub_clan));
	} else {
		$output .= "<select name='pub_clan'><option value='-1'>[Same as Actual]</option>\n";
		foreach ($clans as $clan) {
			$output .= "<option value='{$clan->ID}' " . selected( $clan->ID, $pub_clan, false) . ">" . vtm_formatOutput($clan->NAME) . "</option>\n";
		}
		$output .= "</select>\n";
	}
	$output .= "</td></tr><tr>
			<td class='vtmcol_key'>Sect:</td>
			<td>\n";
	if ($submitted) {
		$output .= $wpdb->get_var($wpdb->prepare("SELECT NAME FROM " . VTM_TABLE_PREFIX . "SECT WHERE ID = %s", $sectid));
	} 
	elseif ($vtmglobal['settings']['limit-sect-method'] == 'only') {
		$output .= "<input type='hidden' name='sect' value='{$vtmglobal['settings']['limit-sect-id']}' />\n";
		$output .= $wpdb->get_var($wpdb->prepare("SELECT NAME FROM " . VTM_TABLE_PREFIX . "SECT WHERE ID = %s", $sectid));
	}
	else {
		$output .= "<select name='sect'>\n";
		foreach (vtm_get_sects(true) as $sect) {
			if ($vtmglobal['settings']['limit-sect-method'] != 'exclude' ||
			    ($vtmglobal['settings']['limit-sect-method'] == 'exclude' && $vtmglobal['settings']['limit-sect-id'] != $sect->ID)) 
				$output .= "<option value='{$sect->ID}' " . selected( $sect->ID, $sectid, false) . ">" . vtm_formatOutput($sect->NAME) . "</option>\n";		
		}
		$output .= "</select>\n";
	}
	$output .= "</td></tr>\n";
	
	if ($vtmglobal['config']->USE_NATURE_DEMEANOUR == 'Y' && count($natures) > 0) {
		$output .= "<tr><td class='vtmcol_key'>Nature*:</td><td>";
		if ($submitted) {
			$output .= vtm_formatOutput($wpdb->get_var($wpdb->prepare("SELECT NAME FROM " . VTM_TABLE_PREFIX . "NATURE WHERE ID = %s", $natureid)));
		} else {
			$output .= "<select name='nature'>\n";
			foreach ($natures as $nature) {
				$output .= "<option value='" . $nature->ID . "' " . selected( $nature->ID, $natureid, false) . ">" . vtm_formatOutput($nature->NAME) . "</option>\n";
			}
			$output .= "</select>\n";
		}
		$output .= "</td></tr>
		<tr><td class='vtmcol_key'>Demeanour*:</td><td>\n";
		if ($submitted) {
			$output .= $wpdb->get_var($wpdb->prepare("SELECT NAME FROM " . VTM_TABLE_PREFIX . "NATURE WHERE ID = %s", $demeanourid));
		} else {
			$output .= "<select name='demeanour'>\n";
			foreach ($natures as $nature) {
				$output .= "<option value='" . $nature->ID . "' " . selected( $nature->ID, $demeanourid, false) . ">" . vtm_formatOutput($nature->NAME) . "</option>\n";
			}
			$output .= "</select>\n";
		}
		$output .= "</td></tr>\n";
	}	
	$output .= "<tr>
			<td class='vtmcol_key'>Character Login name*:</td>
			<td>\n";
	if ($submitted)
		$output .= vtm_formatOutput($login);
	else
		$output .= "<input type='text' name='wordpress_id' value='$login'>\n";
	$output .= "</td>
		</tr>
		<tr>
			<td class='vtmcol_key'>Email Address*:</td>
			<td>\n";
	if ($submitted)
		$output .= $email;
	else
		$output .= "<input type='text' name='email' value='$email'>\n";
	if ($confirmed == 'Y') {
		$output .= "(confirmed)";
	} 
	elseif ($vtmglobal['characterID'] > 0) {
		$output .= "<input type='submit' name='chargen-resend-email' class='' value='Resend confirmation email' />";
	}
	$output .= "</td></tr>
		<tr>
			<td class='vtmcol_key'>Concept* (200 characters):</td>
			<td>\n";
	if ($submitted)
		$output .= $concept;
	else
		$output .= "<textarea name='concept' rows='2' cols='50' maxlength='200'>$concept</textarea>\n";
	$output .= "</td></tr>
		</table>\n";
		
	if ($nodatafail) {
		$output = "Source data (e.g. clans) has not been setup. Character generation cannot continue";
	}

	return $output;
}

function vtm_render_feedback($step) {
	global $wpdb;
	global $vtmglobal;

	$output = "";
	
	$output .= "<h3>Storyteller Feedback</h3>\n";
	$submitted = $vtmglobal['charGenStatus'] == 'Submitted';
	$feedback = $wpdb->get_var( $wpdb->prepare("SELECT NOTE_FROM_ST FROM " . VTM_TABLE_PREFIX . "CHARACTER_GENERATION WHERE CHARACTER_ID = %s", $vtmglobal['characterID']));

	$output .= "<p>Please review the feedback from the Storytellers and make any
				appropriate changes before resubmitting.</p>\n";
	$output .= "<div class='vtmext_section'>" . wpautop(vtm_formatOutput($feedback)) . "</div>\n";

	return $output;
}

function vtm_render_freebie_section($items, $saved, $pendingfb, $pendingxp, $freebiecosts, 
		$postvariable, $showzeros, $issubmitted, $maxdots, $templatefree, $primarypaths) {
	
	global $vtmglobal;

	$columns     = $vtmglobal['config']->WEB_COLUMNS;
	$coloutput   = array();
	$colgroups   = array();
	$colindex    = 0;
	$rowoutput   = "";
	$output = "";
	
	// Get Posted data
	if (isset($_POST[$postvariable])) {
		$submitted = 1;
		$posted = $_POST[$postvariable];
	} else {
		$submitted = 0;
		$posted = array();
	}
	//print_r($pendingfb);

	$maxitems = count($items);
	$itemcount = 0;
	if ($maxitems > 0) {
		$id = 0;
		$grp = "";
		foreach ($items as $item) {
			
			if (is_array($maxdots)) {
				if (isset($maxdots[$item['ITEMTABLE_ID']])) {
					$max2display = $maxdots[$item['ITEMTABLE_ID']]->LEVEL;
				} else {
					$max2display = $maxdots['default'];
				}
			} 
			elseif (sanitize_key($item['ITEMNAME']) == 'willpower') {
				$max2display = 10;
			}
			elseif (sanitize_key($item['ITEMNAME']) == 'pathrating') {
				$max2display = 10;
			}
			else {
				$max2display = $maxdots;
			}

			$loop = $item['MULTIPLE'] == 'Y' ? 4 : 1;
			
			$colspan = $postvariable == 'freebie_merit' ? 1 : 2;
			$itemcount++;
			
			for ($j = 1 ; $j <= $loop ; $j++) {
				
				// What is the name and key of the item
				$name = sanitize_key($item['ITEMNAME']);
				if ($item['MULTIPLE'] == 'Y') {
					$key = $name . "_" . $j;
					if (isset($templatefree[$key])) {
						$loop++;
					}
				} else {
					$key = $name;
				}
				if ($postvariable == 'freebie_stat' && $name == 'pathrating') {
					$name = sanitize_key($item['GROUPING']);
				}
				
				// Work out the right key for the templatefree info
				// to cover the special case where there is only 1 free of a multiple skill so the key
				// was guessed wrongly by vtm_get_free_levels()
				if (isset($templatefree[$key]))
					$templatefreedata = $templatefree[$key]; 
				elseif (isset($templatefree[$name]->LEVEL) && $j == 1)
					$templatefreedata = $templatefree[$name]; 
				else
					$templatefreedata = array();
				
				// Base level from free dots from template
				$levelfrom = isset($templatefreedata->LEVEL) ? $templatefreedata->LEVEL : $levelfrom = 0;
				// Base level Over-ridden by level from main table in database
				$levelfrom = isset($saved[$key]->level_from) ? $saved[$key]->level_from : $levelfrom;
			
				// Current level set by freebie point spends saved
				$current = isset($pendingfb[$key]) ? $pendingfb[$key]->value : $levelfrom;
				// Over-ridden by freebie point spends submitted
				$current = $submitted ? (isset($posted[$key]) ? $posted[$key] : $levelfrom) : $current;

				// xp point spends saved
				$levelxp = isset($pendingxp[$key]) ? $pendingxp[$key]->value : 0;

		
				// Specialisation / Primary Path name
				if (isset($pendingfb[$key]) && $pendingfb[$key]->specialisation != '')
					$specialisation = $pendingfb[$key]->specialisation;
				elseif (isset($templatefreedata->SPECIALISATION)) 
					$specialisation = $templatefreedata->SPECIALISATION;
				elseif (isset($primarypaths[$item['ITEMTABLE_ID']]))
					$specialisation = 'Primary Path';
				else
					$specialisation = '';
				$specialisation = vtm_formatOutput($specialisation);
				
				// Paths only: max level of path
				$maxpathlevel = 5;
				foreach ($primarypaths as $pp) {
					if ($pp->discipline == $item['GROUPING']) {
						$maxpathlevel = $pp->discipline_level;
					}
				}
				if ($maxpathlevel < 5)
					$maxpathlevel = $maxpathlevel - 1;
				else
					$maxpathlevel = 5;

				// Pending Detail
				$detail = isset($pendingfb[$key]) ? vtm_formatOutput($pendingfb[$key]->pending_detail, 1) : '';

				//echo "<li>$key: name: $name, from: $levelfrom, current: $current, xp: $levelxp, spec: $specialisation, saved from: " .
				//(isset($saved[$key]->level_from) ? $saved[$key]->level_from : "not-set") . ", pendingfb: " .
				//(isset($pendingfb[$key]->value) ? $pendingfb[$key]->value : "not-set") . ", posted: " .
				//(isset($posted[$key]) ? $posted[$key] : "not-set") . ", submitted: $submitted</li>\n";

				if ($levelfrom > 0 || $showzeros) {
					// start column / new column
					if (isset($item['GROUPING'])) {
						if ($grp != $item['GROUPING']) {
							$colindex++;
							$grp = $item['GROUPING'];
							$colgroups[$colindex] = $item['GROUPING'];
							$coloutput[$colindex] = "";
						} 
					}
					
					// Hidden fields
					$coloutput[$colindex] .= "<tr style='display:none'><td colspan=$colspan>\n";
					$coloutput[$colindex] .= "<input type='hidden' name='{$postvariable}_spec[" . $key . "]' value='$specialisation' />\n";
					$coloutput[$colindex] .= "<input type='hidden' name='{$postvariable}_detail[" . $key . "]' value='$detail' />\n";
					$coloutput[$colindex] .= "<input type='hidden' name='{$postvariable}_itemid[" . $key . "]' value='{$item['ITEMTABLE_ID']}' />\n";
					$coloutput[$colindex] .= "</td></tr>\n";
				
					$namehtml = vtm_formatName($item['ITEMNAME'], $item['DESCRIPTION'], $specialisation);
					
					if ($postvariable == 'freebie_merit') {
						$cost = $freebiecosts[$name][0][1];
						$cbid = "cb_{$key}_{$j}";
						$namehtml = vtm_formatName($item['ITEMNAME'] . " ($cost)", $item['DESCRIPTION'], $specialisation);
						$coloutput[$colindex] .= "<tr><td class='mfdotselect'>";
						if ($issubmitted) {
							if ($current != 0) {
								$coloutput[$colindex] .= "<img src='{$vtmglobal['dots']['dot2']}' alt='X' /> ";
							} else {
								$coloutput[$colindex] .= "<img src='{$vtmglobal['dots']['dot1empty']}' alt='O' /> ";
							}
							$coloutput[$colindex] .=  "<div><label>" . $namehtml . "</label></div>";
						} else {
							$coloutput[$colindex] .= "<input type='checkbox' name='{$postvariable}[" . $key . "]' id='$cbid' value='$cost' ";
							$coloutput[$colindex] .= checked($current != 0, true, false);
							$coloutput[$colindex] .= "/>";
							$coloutput[$colindex] .= "<div><label for='$cbid'>" . $namehtml . "</label></div>";
						}
						$coloutput[$colindex] .= "</td></tr>\n";
					
					} else {
						//dots row
						$flag = 0;
						$coloutput[$colindex] .= "<tr><td class='vtmcol_key'>" . $namehtml . "</td><td class='vtmcol_dots vtmdot_" . ($max2display > 5 ? 10 : 5) . "'>\n";
						$coloutput[$colindex] .= "<fieldset class='dotselect'>\n";
						for ($i=$max2display;$i>=1;$i--) {
							$radioid = "dot_{$key}_{$i}_{$j}";
							
							if ($postvariable == 'freebie_path' && isset($primarypaths[$item['ITEMTABLE_ID']])) {
								// Lock if this is the primary path for disciplines
								if ($primarypaths[$item['ITEMTABLE_ID']]->path_level >= $i)
									$coloutput[$colindex] .= "<img src='{$vtmglobal['dots']['dot1full']}' alt='*' id='$radioid' />\n";
								else
									$coloutput[$colindex] .= "<img src='{$vtmglobal['dots']['dot1empty']}' alt='*' id='$radioid' />\n";
							}
							elseif ($levelfrom >= $i)
								// Base level from main table in database
								$coloutput[$colindex] .= "<img src='{$vtmglobal['dots']['dot1full']}' alt='*' id='$radioid' />\n";
							elseif ($postvariable == 'freebie_path' && $i > $maxpathlevel) {
								$coloutput[$colindex] .= "<img src='{$vtmglobal['dots']['spacer']}' alt='_' id='$radioid' />\n";
							}
							elseif ($issubmitted || (isset($pendingxp[$key]) && $pendingxp[$key]->value != 0) ) {
								// Lock if there are any xp spends for this item
								if ($current >= $i)
									$coloutput[$colindex] .= "<img src='{$vtmglobal['dots']['dot2']}' alt='*' id='$radioid' />\n";
								elseif ($levelxp >= $i)
									$coloutput[$colindex] .= "<img src='{$vtmglobal['dots']['dot3']}' alt='*' id='$radioid' />\n";
								else
									$coloutput[$colindex] .= "<img src='{$vtmglobal['dots']['dot1empty']}' alt='*' id='$radioid' />\n";
							} 
							else {
								// Display dot to buy, if it can be bought
								if (isset($freebiecosts[$name][$levelfrom][$i])) {
									$cost = $freebiecosts[$name][$levelfrom][$i];
									$coloutput[$colindex] .= "<input type='radio' id='$radioid' name='{$postvariable}[{$key}]' value='$i' ";
									$coloutput[$colindex] .= checked($current, $i, false);
									$coloutput[$colindex] .= " /><label for='$radioid' title='Level $i ($cost freebies)'";
									$coloutput[$colindex] .= ">&nbsp;</label>\n";
									$flag = 1;
								}
								else {
									$coloutput[$colindex] .= "<img src='{$vtmglobal['dots']['dot1empty']}' alt='X' id='$radioid' />\n";
								}
							}
						}
						if (!$issubmitted) {
							$radioid = "dot_{$key}_{$j}_clear";
							if (!isset($primarypaths[$item['ITEMTABLE_ID']])) {
								$coloutput[$colindex] .= "<input type='radio' id='$radioid' name='{$postvariable}[{$key}]' value='0' ";
								$coloutput[$colindex] .= " /><label for='$radioid' title='Clear' class='cleardot'>&nbsp;</label>\n";
							} else {
								$coloutput[$colindex] .= "<img src='{$vtmglobal['dots']['spacer']}' alt='_' id='$radioid' />\n";
							}
						}
						$coloutput[$colindex] .= "</fieldset></td></tr>\n";
						
						// Ensure that freebie spends don't get lost when an XP
						// spend has blocked the user from changing the level
						if (!$flag && $current > 0) {
						
							$coloutput[$colindex] .= "<tr style='display:none'><td colspan=$colspan>\n";
							$coloutput[$colindex] .= "<input type='hidden' name='{$postvariable}[{$key}]' value='$current' />\n";
							$coloutput[$colindex] .= "</td></tr>\n";
						
						}
					}
				}
			}
		}
	
	}
	
	/*
	if ($rowoutput != "") {
		$rowoutput .= "</table></td>";
		if ($col != $columns && $grpcount > $columns) {
			$rowoutput .= "<td class='vtmcg_col' colspan='" . ($columns - $col) . "'></td>";
		}
		$rowoutput .= "</tr>\n";
	}
	*/
	
	// Put each sub-table in the right column
	for ($col = 1 ; $col <= count($coloutput) ; $col++) {
		if ( $columns == 1 || ($col % $columns) == 1) { 
			$rowoutput .= "<tr>";
		}
		$rowoutput .= "<td class='vtmcg_col'><table><tr>";
		$rowoutput .= "<th colspan=$colspan>" . vtm_formatOutput($colgroups[$col]) . "</th></tr>";
		$rowoutput .= $coloutput[$col];
		$rowoutput .= "</table></td>";
		if ( $columns == 1 || ($col % $columns) == 0) 
			$rowoutput .= "</tr>";
		
	}
	
	return $rowoutput;
}


function vtm_render_xp_section($items, $saved, $xpcosts, $pendingfb, 
	$pendingxp, $postvariable, $showzeros, $issubmitted, $fbcosts,
	$maxdots, $templatefree, $primarypaths) {

	global $vtmglobal;
	
	$columns = $vtmglobal['config']->WEB_COLUMNS;
	$coloutput   = array();
	$colgroups   = array();
	$colindex    = 0;
	$rowoutput = "";
	$output = "";
	
	// Get Posted data
	if (isset($_POST[$postvariable])) {
		$submitted = 1;
		$posted = $_POST[$postvariable];
	} else {
		$submitted = 0;
		$posted = array();
	}
	
	$maxitems = count($items);
	if ($maxitems > 0) {
		$grp = "";
		foreach ($items as $item) {
			$loop = $item['MULTIPLE'] == 'Y' ? 4 : 1;
			$colspan = ($postvariable == 'xp_merit' || $postvariable == 'xp_ritual') ? 1 : 2;
			for ($j = 1 ; $j <= $loop ; $j++) {
				// What is the name and key of the item
				$name = sanitize_key($item['ITEMNAME']);
				if ($item['MULTIPLE'] == 'Y') {
					$key = $name . "_" . $j;
					if (isset($templatefree[$key])) {
						$loop++;
					}
				} else {
					$key = $name;
				}
				if ($postvariable == 'freebie_stat' && $name == 'pathrating') {
					$name = sanitize_key($item['GROUPING']);
				}
				
				// Work out the right key for the templatefree info
				// to cover the special case where there is only 1 free of a multiple skill so the key
				// was guessed wrongly by vtm_get_free_levels()
				if (isset($templatefree[$key]))
					$templatefreedata = $templatefree[$key]; 
				elseif (isset($templatefree[$name]->LEVEL) && $j == 1)
					$templatefreedata = $templatefree[$name]; 
				else
					$templatefreedata = array();
	
				// How many dots to display
				if (is_array($maxdots)) {
					if (isset($maxdots[$item['ITEMTABLE_ID']])) {
						$max2display = $maxdots[$item['ITEMTABLE_ID']]->LEVEL;
					} else {
						$max2display = $maxdots['default'];
					}
				} else {
					$max2display = $maxdots;
					switch ($key) {
						case 'willpower':   $max2display = 10; break;
						case 'pathrating':  $max2display = 10; break;
						case 'conscience':  $max2display = 5; break;
						case 'conviction':  $max2display = 5; break;
						case 'selfcontrol': $max2display = 5; break;
						case 'courage':     $max2display = 5; break;
						case 'instinct':    $max2display = 5; break;
					}
				}
				
				// Base level from free dots from template
				$levelfrom = isset($templatefreedata->LEVEL) ? $templatefreedata->LEVEL : $levelfrom = 0;
				// Base level Over-ridden by level from main table in database
				$levelfrom = isset($saved[$key]->level_from) ? $saved[$key]->level_from : $levelfrom;

				// level from freebie point spends saved
				$levelfb = isset($pendingfb[$key]) ? $pendingfb[$key]->value : $levelfrom;
				
				// level from xp point spends
				$current = isset($posted[$key]) ? $posted[$key] : 
						(isset($pendingxp[$key]) ? $pendingxp[$key]->value : 0);

				// Specialisation
				if (isset($pendingxp[$key]) && $pendingxp[$key]->specialisation != '')
					$specialisation = $pendingxp[$key]->specialisation;
				elseif (isset($pendingfb[$key]) && $pendingfb[$key]->specialisation != '')
					$specialisation = $pendingfb[$key]->specialisation;
				elseif (isset($templatefreedata->SPECIALISATION)) 
					$specialisation = $templatefreedata->SPECIALISATION;
				elseif (isset($primarypaths[$item['ITEMTABLE_ID']]))
					$specialisation = 'Primary Path';
				else
					$specialisation = '';
				$specialisation = vtm_formatOutput($specialisation);

				// Paths only: max level of path
				$maxpathlevel = 5;
				foreach ($primarypaths as $pp) {
					if ($pp->discipline == $item['GROUPING']) {
						$maxpathlevel = $pp->discipline_level;
					}
				}
				if ($maxpathlevel < 5)
					$maxpathlevel = $maxpathlevel - 1;
				else
					$maxpathlevel = 5;

				
				// echo "<li>$key: name: $name, from: $levelfrom, fb: $levelfb, xp: $current, spec: $specialisation, saved from: " .
				// (isset($saved[$key]->level_from) ? $saved[$key]->level_from : "not-set") . ", pendingfb: " .
				// (isset($pendingfb[$key]->value) ? $pendingfb[$key]->value : "not-set") . ", posted: " .
				// (isset($posted[$key]) ? $posted[$key] : "not-set") . ", submitted: $submitted</li>\n";

				// Merit stuff
				$meritcost  = $postvariable == 'xp_merit' ? $xpcosts[$name][0][1] : 0;
				$meritlevel = $postvariable == 'xp_merit' ? $fbcosts[$name][0][1] : 0;
				// Ritual stuff
				$ritualcost  = $postvariable == 'xp_ritual' ? $xpcosts[$name][0][1] : 0;
				$rituallevel = $postvariable == 'xp_ritual' ? $item['LEVEL'] : 0;

				// Work out if we are going to display this row
				if ($postvariable == 'xp_merit' && $meritcost > 0 && $meritlevel !== 0)
					$dodisplay = 1;
				elseif ($postvariable == 'xp_ritual' && $ritualcost > 0)
					$dodisplay = 1;
				elseif ($postvariable != 'xp_merit' && $postvariable != 'xp_ritual' && ($levelfrom > 0 || $showzeros))
					$dodisplay = 1;
				else
					$dodisplay = 0;
				
				if ($dodisplay) {
					// start column / new column
					if (isset($item['GROUPING'])) {
						if ($grp != $item['GROUPING']) {
							$colindex++;
							$grp = $item['GROUPING'];
							$colgroups[$colindex] = $item['GROUPING'];
							$coloutput[$colindex] = "";
						} 
					}
					
					// Hidden fields
					$coloutput[$colindex] .= "<tr style='display:none'><td colspan=$colspan>";
					$coloutput[$colindex] .= "<input type='hidden' name='{$postvariable}_comment[$key]' value='$specialisation' />";
					$coloutput[$colindex] .= "<input type='hidden' name='{$postvariable}_itemid[$key]' value='{$item['ITEMTABLE_ID']}' />\n";
					$coloutput[$colindex] .= "<input type='hidden' name='{$postvariable}_name[$key]' value='" . vtm_formatOutput($item['ITEMNAME']) . "' />\n";
					$coloutput[$colindex] .= "</td></tr>\n";
					
					$namehtml = vtm_formatName($item['ITEMNAME'], $item['DESCRIPTION'], $specialisation);
					
					if ($postvariable == 'xp_merit') {
						$namehtml = vtm_formatName($item['ITEMNAME'] . " ($meritlevel) - {$meritcost}xp", $item['DESCRIPTION'], $specialisation);
						$cbid = "cb_{$j}_{$key}";
						$coloutput[$colindex] .= "<tr><td class='mfdotselect'>\n";
						if ($issubmitted) {
							if ($current) {
								$coloutput[$colindex] .= "<img src='{$vtmglobal['dots']['dot2']}' alt='X' /> ";
							} else {
								$coloutput[$colindex] .= "<img src='{$vtmglobal['dots']['dot1empty']}' alt='O' /> ";
							}
							$coloutput[$colindex] .=  "<div><label>" . $namehtml . "</label></div>";
						} else {
							$coloutput[$colindex] .= "<input type='checkbox' name='{$postvariable}[" . $key . "]' id='$cbid' value='$meritlevel' ";
							if ($current) {
								$coloutput[$colindex] .= checked($current, $current, false);
							}
							$coloutput[$colindex] .= "/>\n";
							$coloutput[$colindex] .= "<div><label for='$cbid'>" . $namehtml . "</label></div>\n";
						}
						$coloutput[$colindex] .= "</td></tr>\n";
					}
					elseif ($postvariable == 'xp_ritual') {
						$namehtml = vtm_formatName($item['ITEMNAME']. " (level $rituallevel) - {$ritualcost}xp", $item['DESCRIPTION'], $specialisation);
						$cbid = "cb_{$j}_{$key}";
						$coloutput[$colindex] .= "<tr><td class='mfdotselect'>\n";
						if ($issubmitted) {
							if ($current) {
								$coloutput[$colindex] .= "<img src='{$vtmglobal['dots']['dot2']}' alt='X' /> ";
							} else {
								$coloutput[$colindex] .= "<img src='{$vtmglobal['dots']['dot1empty']}' alt='O' /> ";
							}
							$coloutput[$colindex] .=  "<div><label>" . $namehtml . "</label></div>";
						} 
						elseif (isset($saved[$key]->level_from) && $saved[$key]->level_from > 0) {
							$coloutput[$colindex] .= "<img src='{$vtmglobal['dots']['dot1full']}' alt='X' />\n";
							$coloutput[$colindex] .= "<div><label>" . $namehtml . "</label></div>\n";
						}
						else {
							$coloutput[$colindex] .= "<input type='checkbox' name='{$postvariable}[" . $key . "]' id='$cbid' value='$rituallevel' ";
							if ($current) {
								$coloutput[$colindex] .= checked($current, $current, false);
							}
							$coloutput[$colindex] .= "/>\n";
							$coloutput[$colindex] .= "<div><label for='$cbid'>" . $namehtml . " (level $rituallevel) - {$ritualcost}xp</label></div>\n";
						}
						$coloutput[$colindex] .= "</td></tr>\n";
						}
					else {
						//dots row
						$coloutput[$colindex] .= "<tr><td class='vtmcol_key'>" . $namehtml . "</td><td class='vtmcol_dots vtmdot_" . ($max2display > 5 ? 10 : 5) . "'>\n";
						$coloutput[$colindex] .= "<fieldset class='dotselect'>";
						for ($i=$max2display;$i>=1;$i--) {
							$radioid = "dot_{$key}_{$i}_{$j}";
							
							if ($postvariable == 'xp_path' && isset($primarypaths[$item['ITEMTABLE_ID']])) {
								// Lock if this is the primary path for disciplines
								if ($primarypaths[$item['ITEMTABLE_ID']]->path_level >= $i)
									$coloutput[$colindex] .= "<img src='{$vtmglobal['dots']['dot1full']}' alt='*' id='$radioid' />\n";
								else
									$coloutput[$colindex] .= "<img src='{$vtmglobal['dots']['dot1empty']}' alt='*' id='$radioid' />\n";
							}
							elseif ($levelfrom >= $i)
								$coloutput[$colindex] .= "<img src='{$vtmglobal['dots']['dot1full']}' alt='*' id='$radioid' />";
							elseif ($postvariable == 'xp_path' && $i > $maxpathlevel) {
								$coloutput[$colindex] .= "<img src='{$vtmglobal['dots']['spacer']}' alt='_' id='$radioid' />\n";
							}
							elseif (isset($pendingfb[$key]) && $levelfb >= $i)
								$coloutput[$colindex] .= "<img src='{$vtmglobal['dots']['dot3']}' alt='*' id='$radioid' />";
							elseif ($issubmitted) {
								if ($current >= $i)
									$coloutput[$colindex] .= "<img src='{$vtmglobal['dots']['dot2']}' alt='*' id='$radioid' />";
								else
									$coloutput[$colindex] .= "<img src='{$vtmglobal['dots']['dot1empty']}' alt='*' id='$radioid' />";
							}
							elseif (isset($xpcosts[$name][$levelfb][$i])) {
								$cost = $xpcosts[$name][$levelfb][$i];
								$coloutput[$colindex] .= "<input type='radio' id='$radioid' name='{$postvariable}[$key]' value='$i' ";
								$coloutput[$colindex] .= checked($current, $i, false);
								$coloutput[$colindex] .= " /><label for='$radioid' title='Level $i ($cost xp)'";
								$coloutput[$colindex] .= ">&nbsp;</label>";
							}
							else {
								$coloutput[$colindex] .= "<img src='{$vtmglobal['dots']['dot1empty']}' alt='X' id='$radioid' />\n";
							}
						}
						if (!$issubmitted) {
							$radioid = "dot_{$key}_{$j}_clear";
							if (!isset($primarypaths[$item['ITEMTABLE_ID']])) {
								$coloutput[$colindex] .= "<input type='radio' id='$radioid' name='{$postvariable}[$key]' value='0' ";
								$coloutput[$colindex] .= " /><label for='$radioid' title='Clear' class='cleardot'>&nbsp;</label>\n";
							} else {
								$coloutput[$colindex] .= "<img src='{$vtmglobal['dots']['spacer']}' alt='_' id='$radioid' />\n";
							}
						}
						$coloutput[$colindex] .= "</fieldset></td></tr>\n";
					}
				}
			}
		}
	}	
	
	// Put each sub-table in the right column
	for ($col = 1 ; $col <= count($coloutput) ; $col++) {
		if ( $columns == 1 || ($col % $columns) == 1) { 
			$rowoutput .= "<tr>";
		}
		$rowoutput .= "<td class='vtmcg_col'><table><tr>";
		$rowoutput .= "<th colspan=$colspan>" . vtm_formatOutput($colgroups[$col]) . "</th></tr>";
		$rowoutput .= $coloutput[$col];
		$rowoutput .= "</table></td>";
		if ( $columns == 1 || ($col % $columns) == 0) 
			$rowoutput .= "</tr>";
		
	}

	return $rowoutput;

}




function vtm_render_attributes($step) {
	global $vtmglobal;
	
	$output = "";
	$submitted = $vtmglobal['charGenStatus'] == 'Submitted';
	$items      = vtm_get_chargen_itemlist('STAT');
	$pendingfb  = vtm_get_pending_freebies('STAT');
	$pendingxp  = vtm_get_pending_chargen_xp('STAT');
	$saved      = vtm_get_chargen_saved('STAT');
	$posted     = isset($_POST['attribute_value']) ? $_POST['attribute_value'] : array();
	$free       = array();
	
	$output .= "<h3>Attributes</h3>\n";
	
	if ($vtmglobal['settings']['attributes-method'] == "PST") {
		// Primary, Secondary, Tertiary
		$output .= "<p>You have {$vtmglobal['settings']['attributes-primary']} dots to spend on your Primary attributes, {$vtmglobal['settings']['attributes-secondary']} to spend on Secondary and {$vtmglobal['settings']['attributes-tertiary']} to spend on Tertiary.</p>\n";
	} else {
		$output .= "<p>You have {$vtmglobal['settings']['attributes-points']} dots to spend on your attributes</p>\n";
	}
	// echo "<p>items:";
	// print_r($items); echo "</p><p>fb:";
	// print_r($pendingfb); echo "</p><p>xp:";
	// print_r($pendingxp); echo "</p><p>saved:";
	// print_r($saved); echo "</p>";
	
	$output .= vtm_render_chargen_section(
		$saved, 
		($vtmglobal['settings']['attributes-method'] == "PST"), 
		$vtmglobal['settings']['attributes-primary'], 
		$vtmglobal['settings']['attributes-secondary'], 
		$vtmglobal['settings']['attributes-tertiary'], 
		1, 
		$items, 
		$posted, 
		$pendingfb,
		$pendingxp, 
		'Attributes', 
		'attribute_value', 
		$submitted,
		$vtmglobal['genInfo']['MaxDot'],
		$free,
		array()
	);

	return $output;
}

function vtm_render_chargen_virtues($step) {
	global $wpdb;
	global $vtmglobal;

	$output = "";
	
	$submitted = $vtmglobal['charGenStatus'] == 'Submitted';
	$pendingfb  = vtm_get_pending_freebies('STAT');
	$pendingxp  = vtm_get_pending_chargen_xp('STAT');
	$saved      = vtm_get_chargen_saved('STAT');
	$posted     = isset($_POST['virtue_value']) ? $_POST['virtue_value'] : array();
	
	// Saved Path
	if ($vtmglobal['settings']['limit-road-method'] == 'only') {
		$savedpath = $vtmglobal['settings']['limit-road-id'];
	}
	else {
		$savedpath = $wpdb->get_var($wpdb->prepare("SELECT ROAD_OR_PATH_ID FROM " . VTM_TABLE_PREFIX . "CHARACTER WHERE ID = %s", $vtmglobal['characterID']));
	}
	
	// What path are we on
	if (isset($_POST['path'])) {
		$selectedpath = $_POST['path'];
	}
	else {
		$selectedpath = $savedpath;
	}
	
	// Get the list of items to display
	$pathitems = vtm_get_chargen_virtues($selectedpath);
	//print_r($pathitems);
	
	$output .= "<h3>Virtues</h3>\n";
	$output .= "<p>You have {$vtmglobal['settings']['virtues-points']} dots to spend on your virtues.</p>\n";
	
	// Display Path pull-down
	$pendingroad = vtm_get_pending_freebies('ROAD_OR_PATH');

	$output .= "<p><label><strong>Path of Enlightenment:</strong></label> ";
	$output .= "<input type='hidden' name='savedpath' value='$selectedpath' />";
	//$output .= "<input type='hidden' name='lastpath' value='" . (isset($_REQUEST['savedpath']) ? $_REQUEST['savedpath'] : $savedpath) . "' />";
	if ($submitted) {
		$pathname = $wpdb->get_var($wpdb->prepare("SELECT NAME FROM " . VTM_TABLE_PREFIX . "ROAD_OR_PATH WHERE ID = %s", $selectedpath));
		$output .= "<span>$pathname</span>\n";
	} 
	elseif ($vtmglobal['settings']['limit-road-method'] == 'only' || count($pendingroad) > 0) {
		$pathname = $wpdb->get_var($wpdb->prepare("SELECT NAME FROM " . VTM_TABLE_PREFIX . "ROAD_OR_PATH WHERE ID = %s", $selectedpath));
		$output .= "<input type='hidden' name='path' value='$selectedpath' />";
		$output .= "<span>$pathname</span>\n";
	}
	else {
		$output .= "<select name='path'>\n";
		foreach (vtm_get_chargen_roads() as $path) {
		//echo "<p>method: {$vtmglobal['settings']['limit-road-method']}, id: {$vtmglobal['settings']['limit-road-id']}, pathid: {$path->ID}</p>";
			if ($vtmglobal['settings']['limit-road-method'] != 'exclude' || 
					($vtmglobal['settings']['limit-road-method'] == 'exclude' && $vtmglobal['settings']['limit-road-id'] != $path->ID)) {
				$output .= "<option value='{$path->ID}' " . selected($path->ID, $selectedpath, false) . ">" . stripslashes($path->NAME) . "</option>\n";
			}
		}
		$output .= "</select>\n";
	}
	$output .= "</p>\n";
	
	// Messages for player
	if (count($pendingroad) > 0) {
		$submitted = 1;
		$output .= "<p>Please remove freebie point spends on your path if you want to alter your Virtues</p>";
	}
	if (isset($pendingfb['willpower']) || isset($pendingxp['willpower'])) {
		$output .= "<p>Please remove freebie or experience spends on Willpower if you want to alter Courage.</p>";
	}	

	$output .= vtm_render_chargen_section(
		$saved, 
		false, 0, 0, 0, 
		vtm_has_virtue_free_dot($selectedpath), 
		$pathitems, 
		$posted, 
		$pendingfb, 
		$pendingxp, 
		'Virtues', 
		'virtue_value',
		$submitted, 
		5,
		array(),
		array()
	);
		
	return $output;
}

function vtm_render_chargen_freebies($step) {
	global $vtmglobal;

	$output = "";
	
	// Work out how much points are currently available
	$spends = vtm_get_freebies_spent();
	$spent = $spends["spent"];
	$gained = $spends["gained"];
	$points = $vtmglobal['settings']['freebies-points'] + $gained;
	$remaining = $points - $spent;
	$submitted = $vtmglobal['charGenStatus'] == 'Submitted';
	
	$output .= "<h3>Freebie Points</h3>\n";
	$output .= "<p>\n";
	if ($vtmglobal['settings']['merits-max'] > 0)
		$output .= "You can have a maximum of {$vtmglobal['settings']['merits-max']} points of Merits. ";
	if ($vtmglobal['settings']['flaws-max'] > 0)
		$output .= "You can have a maximum of {$vtmglobal['settings']['flaws-max']} points of Flaws. ";
	$output .= "You have $points points available to spend on your character. $spent have been spent leaving 
	you $remaining points. Hover over the dot to show the freebie point cost.</p>\n";
	
	$sectiontitle   = array(
						'stat'       => "Attributes and Stats",
						'skill'      => "Abilities",
						'disc'       => "Disciplines",
						'path'       => "Paths",
						'background' => "Backgrounds",
						'merit'      => "Merits and Flaws",
					);
	$sectionorder   = array('stat', 'skill', 'background', 'disc', 'path', 'merit');
	
	$sectioncontent['stat']  = vtm_render_freebie_stats($submitted);
	$sectioncontent['skill'] = vtm_render_freebie_skills($submitted);
	$sectioncontent['disc']  = vtm_render_freebie_disciplines($submitted);
	$sectioncontent['path']  = vtm_render_freebie_paths($submitted);
	$sectioncontent['background'] = vtm_render_freebie_backgrounds($submitted);
	$sectioncontent['merit'] = vtm_render_freebie_merits($submitted);
	
	// DISPLAY TABLES 
	//-------------------------------
	$i = 0;
	$jumpto = array();
	foreach ($sectionorder as $section) {
		if (isset($sectioncontent[$section]) && $sectiontitle[$section] && $sectioncontent[$section]) {
			$jumpto[$i++] = "<a href='#gvid_fb_$section' class='gvfb_jump'>" . $sectiontitle[$section] . "</a>\n";
		}
	}
	$outputJump = "<p>Jump to section: " . implode(" | ", $jumpto) . "</p>\n";
	
	foreach ($sectionorder as $section) {
	
		if (isset($sectioncontent[$section]) && $sectioncontent[$section] != "" ) {
			$output .= "<h4 class='gvfb_head' id='gvid_fb_$section'>" . $sectiontitle[$section] . "</h4>\n";
			$output .= "$outputJump\n";
			$output .= $sectioncontent[$section];
		} 
		
	}

	return $output;
}

function vtm_render_chargen_xp($step) {
	global $vtmglobal;

	$output = "";

	$submitted = $vtmglobal['charGenStatus'] == 'Submitted';
	$spent   = vtm_get_chargen_xp_spent();
	$points  = vtm_get_available_xp($vtmglobal['playerID'], $vtmglobal['characterID']); 
	//$pending = vtm_get_pending_xp($vtmglobal['playerID'], $vtmglobal['characterID']);
	
	$remaining = $points - $spent;
	
	$output .= "<h3>Experience Points</h3>\n";
	$output .= "<p>\n";
	$output .= "You have $points points available to spend on your character. $spent have been spent leaving 
	you $remaining points. Hover over the dot to show the experience point cost.</p>\n";
	
	$sectiontitle   = array(
						'stat'       => "Attributes and Stats",
						'skill'      => "Abilities",
						'disc'       => "Disciplines",
						'path'       => "Paths",
						'merit'      => "Merits",
						'ritual'     => "Rituals",
					);
	$sectionorder   = array('stat', 'skill', 'disc', 'path', 'merit', 'ritual');
	
	$pendingSpends = array();
	$sectioncontent['stat']   = vtm_render_chargen_xp_stats($submitted);
	$sectioncontent['skill']  = vtm_render_chargen_xp_skills($submitted);
	$sectioncontent['disc']   = vtm_render_chargen_xp_disciplines($submitted);
	$sectioncontent['path']   = vtm_render_chargen_xp_paths($submitted);
	$sectioncontent['merit']  = vtm_render_chargen_xp_merits($submitted);
	$sectioncontent['ritual'] = vtm_render_chargen_xp_rituals($submitted);
	
	// DISPLAY TABLES 
	//-------------------------------
	$i = 0;
	$jumpto = array();
	foreach ($sectionorder as $section) {
		if (isset($sectioncontent[$section]) && $sectiontitle[$section] && $sectioncontent[$section]) {
			$jumpto[$i++] = "<a href='#gvid_xp_$section' class='gvxp_jump'>" . $sectiontitle[$section] . "</a>\n";
		}
	}
	$outputJump = "<p>Jump to section: " . implode(" | ", $jumpto) . "</p>\n";
	
	foreach ($sectionorder as $section) {
	
		if (isset($sectioncontent[$section]) && $sectioncontent[$section] != "" ) {
			$output .= "<h4 class='gvxp_head' id='gvid_xp_$section'>" . $sectiontitle[$section] . "</h4>\n";
			$output .= "$outputJump\n";
			$output .= $sectioncontent[$section];
		} 
		
	}

	return $output;
}

function vtm_render_finishing($step) {
	global $wpdb;
	global $vtmglobal;

	$output = "";

	$output .= "<h3>Finishing Touches</h3>\n";
	$output .= "<p>Please fill in more information on your character.</p>\n";
	$submitted = $vtmglobal['charGenStatus'] == 'Submitted';
	
	// Calculate Generation
	$generationInfo = vtm_calculate_generation();
	$generation   = $generationInfo['Gen'];
	$generationID = $generationInfo['ID'];

	// Calculate Path
	$pathid    = $wpdb->get_var($wpdb->prepare("SELECT ROAD_OR_PATH_ID FROM " . VTM_TABLE_PREFIX . "CHARACTER WHERE ID = %s", $vtmglobal['characterID']));
	$pathname  = $wpdb->get_var($wpdb->prepare("SELECT NAME FROM " . VTM_TABLE_PREFIX . "ROAD_OR_PATH WHERE ID = %s", $pathid));
	$pathfreeb = $wpdb->get_var("SELECT LEVEL_TO FROM " . VTM_TABLE_PREFIX . "PENDING_FREEBIE_SPEND WHERE ITEMTABLE = 'ROAD_OR_PATH'");

	if ($pathfreeb) {
		$pathrating = $pathfreeb * $vtmglobal['settings']['road-multiplier'];
	} else {
		$statid1   = $wpdb->get_var($wpdb->prepare("SELECT STAT1_ID FROM " . VTM_TABLE_PREFIX . "ROAD_OR_PATH WHERE ID = %s", $pathid));
		$statid2   = $wpdb->get_var($wpdb->prepare("SELECT STAT2_ID FROM " . VTM_TABLE_PREFIX . "ROAD_OR_PATH WHERE ID = %s", $pathid));
		
		$sql = "SELECT cs.LEVEL
				FROM " . VTM_TABLE_PREFIX . "CHARACTER_STAT cs
				WHERE STAT_ID = %s AND CHARACTER_ID = %s";
		$stat1      = $wpdb->get_var($wpdb->prepare($sql, $statid1, $vtmglobal['characterID']));
		$stat2      = $wpdb->get_var($wpdb->prepare($sql, $statid2, $vtmglobal['characterID']));
		$pathrating = ($stat1 + $stat2) * $vtmglobal['settings']['road-multiplier'];
	}

	// Date of Birth
	$dob = $wpdb->get_var($wpdb->prepare("SELECT DATE_OF_BIRTH FROM " . VTM_TABLE_PREFIX . "CHARACTER WHERE ID = %s", $vtmglobal['characterID']));
	$dob_array = explode('-',$dob);
	$dob_day   = isset($_POST['day_dob'])   ? $_POST['day_dob']   : (isset($dob) ? strftime("%d", strtotime($dob)) : '');
	$dob_month = isset($_POST['month_dob']) ? $_POST['month_dob'] : (isset($dob) ? strftime("%m", strtotime($dob)) : '');
	$dob_year  = isset($_POST['year_dob'])  ? $_POST['year_dob']  : (isset($dob) ? $dob_array[0] : '');
	
	// Date of Embrace
	$doe = $wpdb->get_var($wpdb->prepare("SELECT DATE_OF_EMBRACE FROM " . VTM_TABLE_PREFIX . "CHARACTER WHERE ID = %s", $vtmglobal['characterID']));
	$doe_array = explode('-',$doe);
	$doe_day   = isset($_POST['day_doe'])   ? $_POST['day_doe']   : (isset($doe) ? strftime("%d", strtotime($doe)) : '');
	$doe_month = isset($_POST['month_doe']) ? $_POST['month_doe'] : (isset($doe) ? strftime("%m", strtotime($doe)) : '');
	$doe_year  = isset($_POST['year_doe'])  ? $_POST['year_doe']  : (isset($doe) ? $doe_array[0] : '');
	
	// Sire
	$sire = $wpdb->get_var($wpdb->prepare("SELECT SIRE FROM " . VTM_TABLE_PREFIX . "CHARACTER WHERE ID = %s", $vtmglobal['characterID']));
	$sire = isset($_POST['sire']) ? $_POST['sire'] : $sire;
	// Pronouns
	$pronouns = $wpdb->get_var($wpdb->prepare("SELECT PRONOUNS FROM " . VTM_TABLE_PREFIX . "CHARACTER WHERE ID = %s", $vtmglobal['characterID']));
	$pronouns = isset($_POST['pronouns']) ? $_POST['pronouns'] : $pronouns;
	
	$output .= "<h4>Calculated Values</h4>\n";
	$output .= "<table>\n";
	$output .= "<tr><td>Generation:</td><td>$generation";
	$output .= "<input type='hidden' name='generationID' value='$generationID' />\n";
	$output .= "</td></tr>\n";
	if ($pathrating > 0) {
		$output .= "<tr><td>$pathname:</td><td>$pathrating";
		$output .= "<input type='hidden' name='pathrating' value='$pathrating' />\n";
		$output .= "</td></tr>\n";
	} 
	$output .= "</table>\n";

	$output .= "<h4>Important Dates</h4>\n";
	$output .= "<table>\n";
	$output .= "<tr><td>Date of Birth:</td><td>\n";
	$output .= vtm_render_date_entry("dob", $dob_day, $dob_month, $dob_year, $submitted);
	$output .= "</td></tr>\n";
	$output .= "<tr><td>Date of Embrace:</td><td>\n";
	$output .= vtm_render_date_entry("doe", $doe_day, $doe_month, $doe_year, $submitted);
	$output .= "</td></tr>\n";
	$output .= "</table>\n";

	// Notes to ST
	$stnotes = $wpdb->get_var($wpdb->prepare("SELECT NOTE_TO_ST FROM " . VTM_TABLE_PREFIX . "CHARACTER_GENERATION WHERE CHARACTER_ID = %s", $vtmglobal['characterID']));
	$stnotes = vtm_formatOutput(isset($_POST['noteforST']) ? $_POST['noteforST'] : $stnotes);
	
	// Specialities Data
	
	// Get the list of things needing specialities
	$specialities = vtm_get_chargen_specialties();
	
	// Output specialities
	$spec_output = "";
	$title = "";
	foreach ($specialities as $item) {
		if ($title != $item['title']) {
			$title = vtm_formatOutput($item['title']);
			$spec_output .= "<tr><th colspan=3>$title</th></tr>\n";
		}
		
		// have a hidden row with the tablename and tableid info
		$spec_output .= "<tr style='display:none'><td colspan=3>
					<input type='hidden' name='tablename[]' value='{$item['tablename']}' />
					<input type='hidden' name='tableid[]' value='{$item['tableid']}' />
					<input type='hidden' name='fullname[]' value='" . vtm_formatOutput($item['itemname']) . "' />
					</td></tr>\n";
					
		$spec_output .= "<tr><td>" . vtm_formatOutput($item['itemname']) . "</td>
					<td>{$item['level']}</td>
					<td>\n";
			
		// Only have an entry box for specialities that haven't been pre-set from the
		// character generation template
		if ($submitted)
			$spec_output .= vtm_formatOutput($item['spec']);
		elseif ($item['hasinput'] == 'Y') 
			$spec_output .= "<input type='text' name='comment[]' value='" . vtm_formatOutput($item['spec']) . "' maxlength='25' />\n";
		else
			$spec_output .= "{$item['spec']}<input type='hidden' name='comment[]' value='" . vtm_formatOutput($item['spec']) . "' />\n";
		
		$spec_output .= "</td></tr>\n";
											
	}

	if ($spec_output != '') {
		$output .= "<h4>Specialities</h4>\n";
		$output .= "<p>Please enter specialities for the indicated Attributes and Abilities and provide
					a note on what any Merits and Flaws refer to.</p>
					
					<p>An example speciality for Stamina is 'tough'. An example note for the Merit 'Acute Sense'
					might be 'sight' and for 'Clan Friendship' might be 'Ventrue'</p>\n";
		$output .= "<table>$spec_output\n";
		$output .= "</table>\n";
	}
	
	$output .= "<h4>Miscellaneous</h4>\n";
	$output .= "<table>\n";
	$output .= "<tr><td>Character Pronouns:</td><td>\n";
	if ($submitted)
		$output .= vtm_formatOutput($pronouns);
	else
		$output .= "<input type='text' name='pronouns' value='" . vtm_formatOutput($pronouns) . "' />\n";
	$output .= "</td></tr>\n";
	$output .= "<tr><td>Name of your Sire:</td><td>\n";
	if ($submitted)
		$output .= vtm_formatOutput($sire);
	else
		$output .= "<input type='text' name='sire' value='" . vtm_formatOutput($sire) . "' />\n";
	$output .= "</td></tr>\n";
	$output .= "<tr><td>Notes for Storyteller:</td><td>\n";
	if ($submitted)
		$output .= wpautop(vtm_formatOutput($stnotes, 1));
	else
		$output .= "<textarea name='noteforST' rows='5'>" . vtm_formatOutput($stnotes, 1) . "</textarea>\n"; // ADD COLUMN TO CHARACTER
	$output .= "</td></tr>\n";
	$output .= "</table>\n";

	return $output;
}
function vtm_render_chargen_extbackgrounds($step) {
	global $vtmglobal;
	
	$output = "";

	$output .= "<h3>History and Extended Backgrounds</h3>\n";
	$output .= "<p>Please fill in more information on your character.</p>\n";
	$submitted = $vtmglobal['charGenStatus'] == 'Submitted';
	
	// Merits
	$questions = vtm_get_chargen_merit_questions();
	$posted    = isset($_POST['meritquestion']) ? $_POST['meritquestion'] : array();
	foreach ($questions as $question) {
		$id = $question->ID;
		$title = $question->NAME;
		if (!empty($question->SPECIALISATION)) $title .= " - " . $question->SPECIALISATION;
		$title .= " (" . $question->VALUE . ")";
		
		$text = isset($posted[$id]) ? $posted[$id] : (isset($question->PENDING_DETAIL) ? $question->PENDING_DETAIL : '');
		
		$output .= "<h4>$title</h4><p class='gvext_ques'>{$question->BACKGROUND_QUESTION}</p>\n";
		$output .= "<input type='hidden' name='meritquestion_title[$id]' value='" . htmlspecialchars($title, ENT_QUOTES) . "' />\n";
		if ($submitted)
			$output .= "<div class='vtmext_section'>" . wpautop(vtm_formatOutput($text, 1)) . "</div>\n";
		else {
			$output .= "<p><textarea name='meritquestion[$id]' rows='4' cols='80'>" . vtm_formatOutput($text) . "</textarea></p>\n";
		}
	}

	// Backgrounds
	$questions = vtm_get_chargen_background_questions();
	$posted    = isset($_POST['bgquestion']) ? $_POST['bgquestion'] : array();
	foreach ($questions as $question) {
		$id    = $question->ID;
		$title = $question->NAME . " " . $question->LEVEL;
		$text = isset($posted[$id]) ? $posted[$id] : (isset($question->PENDING_DETAIL) ? $question->PENDING_DETAIL : '');

		if (!empty($question->COMMENT)) $title .= " (" . $question->COMMENT . ")";		
		
		$output .= "<h4>" . vtm_formatOutput($title) . "</h4><p class='gvext_ques'>" . vtm_formatOutput($question->BACKGROUND_QUESTION) . "</p>\n";
		$output .= "<input type='hidden' name='bgquestion_title[$id]' value='" . htmlspecialchars($title, ENT_QUOTES) . "' />\n";
		$output .= "<input type='hidden' name='bgquestion_source[$id]' value='" . htmlspecialchars($question->source, ENT_QUOTES) . "' />\n";
		if ($submitted)
			$output .= "<div class='vtmext_section'>" . wpautop(vtm_formatOutput($text, 1)) . "</div>\n";
		else
			$output .= "<p><textarea name='bgquestion[$id]' rows='4' cols='80'>" . vtm_formatOutput($text) . "</textarea></p>\n";
		
	}

	// Extended
	$questions = vtm_get_chargen_questions();
	$posted    = isset($_POST['question']) ? $_POST['question'] : array();
		
	foreach ($questions as $question) {
		$id = $question->questID;
		$text = isset($posted[$id]) ? $posted[$id] : (isset($question->PENDING_DETAIL) ? $question->PENDING_DETAIL : '');
	
		$output .= "<h4>{$question->TITLE}</h4><p class='gvext_ques'>{$question->BACKGROUND_QUESTION}</p>\n";
		$output .= "<input type='hidden' name='question_title[$id]' value='{$question->TITLE}' />\n";
		if ($submitted)
			$output .= "<div class='vtmext_section'>" . wpautop(vtm_formatOutput($text, 1)) . "</div>\n";
		else
			$output .= "<p><textarea name='question[$id]' rows='4' cols='80'>" . vtm_formatOutput($text) . "</textarea></p>\n";
	}

	return $output;
}
function vtm_render_chargen_submit($step) {
	global $vtmglobal;

	$output = "";

	$output .= "<h3>Summary and Submit</h3>\n";
	$output .= "<p>Below is a summary of the character generation status.</p>\n";
	$submitted = $vtmglobal['charGenStatus'] == 'Submitted';
	
	// Not suitable to use _POST as it is only updated if all steps have been
	// gone through this session
	//$flow = vtm_chargen_flow_steps();
	foreach ($vtmglobal['flow'] as $tag => $flowstep) {
		$progress[$tag] = call_user_func($flowstep['validate'], 0);
	}
	
	$output .= "<table>\n";
	$index = 0;
	$done = 0;
	$allsteps = 0;
	foreach ($progress as $tag => $result) {
		if ($vtmglobal['flow'][$tag]['display'] == 1 && $tag != 'submit') {
			$output .= "<tr>\n";
			if ($result[2]) $status = "Complete";
			elseif ($result[0]) $status = "In progress: <ul class='vtm_warn'>{$result[1]}</ul>";
			else $status = "Error";
			
			if ($vtmglobal['flow'][$tag]['title'] == 'Spend Experience' && $status != "Error") $status = "N/A";
			
			if ($status == "Error") $errinfo = "<ul class='vtm_error'>{$result[1]}</ul>"; else $errinfo = "";
			If ($status == "Complete" || $status == "N/A") $done++;
			
			$output .= "<td>Step " . ($index) .": {$vtmglobal['flow'][$tag]['title']}</td>\n";
			$output .= "<td>$status $errinfo</td>\n";
			$output .= "</tr>\n";
			
			$allsteps++;
		}
		$index++;
	}
	
	$output .= "</table>\n";
	
	$alldone = 0;
	if ($done == $allsteps) {
		$alldone = 1;
		if ($submitted)
			$output .= "<p><strong>Your character has been submitted!</strong></p>\n";
		else
			$output .= "<p><strong>Your character is ready to submit!</strong></p>\n";
	}
	$output .= "<input type='hidden' name='status' value='$alldone' />\n";
	
	$link = vtm_get_stlink_url('printCharSheet');
	$link = add_query_arg('characterID', $vtmglobal['characterID'], $link);
	$output .= "<br /><p>Click to <a href='$link' title='Print Character'>Print your character</a></p>\n";


	return $output;
}
function vtm_render_abilities($step) {
	global $wpdb;
	global $vtmglobal;

	$output     = "";
	$submitted = $vtmglobal['charGenStatus'] == 'Submitted';
	$showsecondaries = get_option('vtm_chargen_showsecondaries', '0') == '0' ? 'nosec' : '';
	$items  = vtm_get_chargen_itemlist('SKILL', $showsecondaries);
	
	//$items      = vtm_get_chargen_itemlist('SKILL');
	$pendingfb  = vtm_get_pending_freebies('SKILL');
	$pendingxp  = vtm_get_pending_chargen_xp('SKILL');
	$saved      = vtm_get_chargen_saved('SKILL');
	$posted     = isset($_POST['ability_value']) ? $_POST['ability_value'] : array();
	$free       = vtm_get_free_levels('SKILL');
	
	//echo "<p>items:";print_r($items); echo "</p>";
	//echo "<p>pendingfb";
	//print_r($pendingfb); echo "</p><p>pendingxp";
	//print_r($pendingxp); echo "</p>";
	//echo "<p>saved:";print_r($saved); echo "</p>";
	//echo "<p>posted:";print_r($posted); echo "</p>";
	//echo "<p>free";
	//print_r($free); echo "</p>";
	
	$output .= "<h3>Abilities</h3>\n";
	$output .= "<p>You have {$vtmglobal['settings']['abilities-primary']} dots to spend on your Primary abilities, 
		{$vtmglobal['settings']['abilities-secondary']} to spend on Secondary and {$vtmglobal['settings']['abilities-tertiary']} to 
		spend on Tertiary.";
	if ($vtmglobal['settings']['abilities-max'] > 0)
		$output .= " The maximum you can spend on any one Ability at this stage is {$vtmglobal['settings']['abilities-max']}.";
	$output .= "</p>\n";
	
	$output .= vtm_render_chargen_section($saved, 
		true, 
		$vtmglobal['settings']['abilities-primary'], 
		$vtmglobal['settings']['abilities-secondary'], 
		$vtmglobal['settings']['abilities-tertiary'], 
		0, 
		$items, 
		$posted, 
		$pendingfb, 
		$pendingxp, 
		'Abilities', 
		'ability_value', 
		$submitted,
		$vtmglobal['genInfo']['MaxDot'], 
		$free,
		array()
	);

	return $output;
}

function vtm_render_chargen_disciplines($step) {
	global $wpdb;
	global $vtmglobal;

	$output = "";
	$submitted = $vtmglobal['charGenStatus'] == 'Submitted';
	$items      = vtm_get_chargen_itemlist('DISCIPLINE');
	//echo "<p>items:";print_r($items); echo "</p>";
	$pendingfb  = vtm_get_pending_freebies('DISCIPLINE');
	$pendingxp  = vtm_get_pending_chargen_xp('DISCIPLINE');
	$saved      = vtm_get_chargen_saved('DISCIPLINE');
	//echo "<p>saved:";print_r($saved); echo "</p>";
	$posted     = isset($_POST['discipline_value']) ? $_POST['discipline_value'] : array();
	$free       = array();
	$primarypath = vtm_get_chargen_paths();
	
	$output .= "<h3>Disciplines</h3>\n";
	$output .= "<p>You have {$vtmglobal['settings']['disciplines-points']} dots to spend on your Disciplines</p>\n";
	
	$output .= vtm_render_chargen_section(
		$saved, 
		false, 0, 0, 0, 
		0, 
		$items, 
		$posted, 
		$pendingfb, 
		$pendingxp, 
		'Disciplines', 
		'discipline_value', 
		$submitted,
		$vtmglobal['genInfo']['MaxDisc'],
		$free,
		$primarypath
	);


	return $output;
}

function vtm_render_chargen_paths($step) {
	global $wpdb;
	global $vtmglobal;

	$output = "";
	$submitted = $vtmglobal['charGenStatus'] == 'Submitted';
	$items      = vtm_get_chargen_itemlist('PATH');
	//echo "<p>items:";print_r($items); echo "</p>";
	$pendingfb  = vtm_get_pending_freebies('PATH');
	$pendingxp  = vtm_get_pending_chargen_xp('PATH');
	$saved      = vtm_get_chargen_saved('PATH');	
	$charpaths  = vtm_get_chargen_paths();
	$paths      = vtm_listPaths('N');
	$max        = $vtmglobal['genInfo']['MaxDisc'] > 5 ? 10 : 5;
	
	$output .= "<h3>Paths</h3>\n";
	$output .= "<p>Select your primary path(s)</p>\n";
	
	$output .= "<table>";
	$output .= "<tr><th class='vtmcol_key'>Discipline</th><th class='vtmcol_dots'>Level</th><th>Primary Path</th></tr>\n";
	
	$disc_over5 = array();
	foreach ($charpaths as $entry) {
		$dkey = sanitize_key($entry->discipline);
		$output .= "<tr><td>" . vtm_formatOutput($entry->discipline) . "</td><td class='vtmdot_$max'>";
		$output .= vtm_numberToDots($max, $entry->discipline_level);
		$output .= "<input name='discipline_level[$dkey]' type='hidden' value='{$entry->discipline_level}'>";
		$output .= "<input name='discipline_name[$dkey]' type='hidden' value='" . vtm_formatOutput($entry->discipline) . "'>";
		$output .= "</td><td>";
		
		if (!$submitted && $vtmglobal['settings']['primarypath-select'] == 1) {
			$output .= "<select name='primarypaths[$dkey]'>\n";
			foreach ($paths as $path) {
				if ($dkey == sanitize_key($path->disname)) {
					$key = sanitize_key($path->name);
					$output .= "<option value='{$key}' " . selected($key, sanitize_key($entry->name) , false) . ">" . vtm_formatOutput($path->name) . "</option>\n";
				}
			}
			$output .= "</select>\n";
		} else {
			$output .= vtm_formatOutput($entry->name);
			$output .= "<input name='primarypaths[$dkey]' type='hidden' value='" . sanitize_key($entry->name) . "' \>";
		}
		
		$output .= "</td></tr>\n";
	}

	$output .= "</table>";
	
	foreach ($charpaths as $entry) {
		$primarypid = $entry->pathid;
		$discipline = $entry->discipline;
		$dkey = sanitize_key($discipline);
		
		if ($entry->discipline_level > 5) {
			$dots = $entry->discipline_level - 5;
			
			$output .= "<p>Assign $dots excess " . vtm_formatOutput($discipline) . " dots to additional paths</p>\n";
			$output .= "<table>";
			$output .= "<tr><th>Path</th><th class='vtmcol_dots'>Level</th></tr>\n";
			foreach ($items as $path) {
				if ($dkey == sanitize_key($path['GROUPING'])) {
					$key = sanitize_key($path['ITEMNAME']);
					$level_pp = $path['ITEMTABLE_ID'] == $primarypid ? 5 : 0;
					$level_pp = isset($saved[$key]->level_from) ? $saved[$key]->level_from : $level_pp;
					$level_fb = isset($pendingfb[$key]) ? $pendingfb[$key]->value : 0;
					$level_xp = isset($pendingxp[$key]) ? $pendingxp[$key]->value : 0;

					$output .= "<tr><td class='vtmcol_key'>{$path['ITEMNAME']}</td>";
					$output .= "<td class='vtmdot_5'>";
					$output .= "<input type='hidden' name='path_disc_id[$key]' value='$dkey'>";
					if ($path['ITEMTABLE_ID'] == $primarypid) {
						$output .= vtm_render_dot_select('path_value', $key, $level_pp, $level_pp, 0, 5, 1);
					} else {
						$level_pp = isset($_POST['path_value'][$key]) ? $_POST['path_value'][$key] : $level_pp;
						$output .= vtm_render_dot_select('path_value', $key, $level_pp, max($level_fb,$level_xp), 0, 5, $submitted);
					}
					$output .= "</td></tr>\n";
				}
				
			}
			$output .= "</table>";
			
		} else {
			$key = sanitize_key($entry->name);
			$dots = $entry->discipline_level;
			//$output .= "<p>" . vtm_formatOutput($discipline) . " has level $dots</p>\n";
			$output .= "<input type='hidden' name='path_value[$key]' value='$dots'>";		
			$output .= "<input type='hidden' name='path_disc_id[$key]' value='$dkey'>";		
		}
	}


	return $output;
}

function vtm_render_chargen_backgrounds($step) {
	global $wpdb;
	global $vtmglobal;

	$output = "";
	$submitted = $vtmglobal['charGenStatus'] == 'Submitted';
	$items      = vtm_get_chargen_itemlist('BACKGROUND');
	$pendingfb  = vtm_get_pending_freebies('BACKGROUND');
	$pendingxp  = vtm_get_pending_chargen_xp('BACKGROUND');
	$saved      = vtm_get_chargen_saved('BACKGROUND');
	$posted     = isset($_POST['background_value']) ? $_POST['background_value'] : array();
	$free       = vtm_get_free_levels('BACKGROUND');

	$output .= "<h3>Backgrounds</h3>\n";
	$output .= "<p>You have {$vtmglobal['settings']['backgrounds-points']} dots to spend on your Backgrounds</p>\n";

	// Work out how many dots we need
	$maxdots = $wpdb->get_var($wpdb->prepare("SELECT MAX_DISCIPLINE FROM " . VTM_TABLE_PREFIX . "GENERATION WHERE ID = %s", $vtmglobal['settings']['limit-generation-low']));
	$maxbgs = $wpdb->get_results($wpdb->prepare("SELECT ITEMTABLE_ID, LEVEL 
		FROM " . VTM_TABLE_PREFIX . "CHARGEN_TEMPLATE_MAXIMUM WHERE 
		ITEMTABLE = 'BACKGROUND' AND TEMPLATE_ID = %s", $vtmglobal['templateID']), OBJECT_K);
	if (count($maxbgs) > 0) {
		$maximums = $maxbgs;
		$maximums['default'] = $maxdots;
	} else {
		$maximums = $maxdots;
	}
	
	$output .= vtm_render_chargen_section(
		$saved, 
		false, 0, 0, 0, 
		0, 
		$items, 
		$posted, 
		$pendingfb, 
		$pendingxp, 
		'Backgrounds', 
		'background_value', 
		$submitted,
		$maximums, 
		$free,
		array()
	);

	return $output;
} 

function vtm_render_chargen_rituals($step) {
	global $wpdb;
	global $vtmglobal;
	
	$output = "";
	$submitted = $vtmglobal['charGenStatus'] == 'Submitted';
	$items    = vtm_get_chargen_rituals();
	$points   = vtm_get_chargen_ritual_points($items);
	$pendingxp  = vtm_get_pending_chargen_xp('RITUAL'); 
	$saved      = vtm_get_chargen_saved('RITUAL');
	$posted     = isset($_POST['ritual_value']) ? $_POST['ritual_value'] : array();
		
	$output .= "<h3>Rituals</h3>\n";
	foreach ($points as $discipline => $point) {
		$discipline = ucfirst($discipline);
		$output .= "<p>You have $point points to spend on your $discipline rituals.</p>\n";
	}

	$output .= vtm_render_chargen_section(
		$saved, 
		false, 0, 0, 0, 
	    0, 
		$items, 
		$posted, 
		array(), 
		$pendingxp, 
		'Ritual', 
		'ritual_value', 
		$submitted,
		1,
		array(),
		array()
	);
	
	return $output;
} 

function vtm_render_choose_template($tag) {
	global $wpdb;

	$output = "";
	
	$sql = "SELECT ID, NAME, DESCRIPTION FROM " . VTM_TABLE_PREFIX . "CHARGEN_TEMPLATE WHERE VISIBLE = 'Y' ORDER BY NAME";
	$result = $wpdb->get_results($sql);
	
	// Check that character generation can go ahead - that we have enough data
	if (count($result) == 0) {
		return "<div class='vtm_error'><p>No character generation templates have been defined.</p></div>";
	}
	if (count(vtm_get_clans()) == 0) {
		return "<div class='vtm_error'><p>No clans have been defined in the database</p></div>";
	}
	if (count(vtm_listRoadsOrPaths()) == 0) {
		return "<div class='vtm_error'><p>No Paths of Enlightenment have been defined in the database</p></div>";
	}
	if (count(vtm_listSkills("","")) == 0) {
		return "<div class='vtm_error'><p>No abilities have been defined in the database</p></div>";
	}
	if (count(vtm_get_backgrounds()) == 0) {
		return "<div class='vtm_error'><p>No backgrounds have been defined in the database</p></div>";
	}

	$output .= "<h3>Choose a template</h3>\n";
	
	$output .= "<table>";
	foreach ($result as $template) {
		$output .= "<tr><td><input type='radio' id='seltempl{{$template->ID}}' name='chargen_template' value='{$template->ID}' ";
		
		if (sizeof($result) == 1 || $template->ID == $result[0]->ID) {
			$output .= 'checked="checked"';
		}
		
		$output .= "/></td>";
		$output .= "<td><label for='seltempl{{$template->ID}}'>" . vtm_formatOutput($template->NAME) . "</label></td>";
		$output .= "<td>" . vtm_formatOutput($template->DESCRIPTION) . "</td></tr>";
	}
	$output .= "</table>";
	$ref = isset($_GET['reference']) ? $_GET['reference'] : '';
	
	$output .= "<p>Or, update a character: 
		<label>Reference:</label> <input type='text' name='chargen_reference' value='$ref' size=30 ></p>\n";

	return $output;
}

function vtm_validate_chargen($laststep) {
	global $wpdb;
	global $vtmglobal;
			
	if ($laststep == '') {
		//print_r($_REQUEST);
		/*
		if ( !isset($_REQUEST['chargen_template']) && 
			(isset($_REQUEST['chargen_reference']) && $_REQUEST['chargen_reference'] == '')) {
			$ok = 0;
			$errormessages = "<li>Please make a selection or enter a reference</li>";
		} else {
			$ok = 1;
			$errormessages = "";
		}
		*/
		$ok = 1;
		$errormessages = "";
	}
	elseif ($vtmglobal['charGenStatus'] == 'Submitted') {
		$ok = 1;
		$errormessages = "";
	}
	else {
		$status = call_user_func($vtmglobal['flow'][$laststep]['validate']);
		$ok = $status[0];
		$errormessages = $status[1];
	}
	//echo "<p>Do Validate ($laststep, {$vtmglobal['characterID']}, $ok, $errormessages)</p>";
		
	if ($errormessages != "") {
		echo "<div class='vtm_error'><ul>$errormessages</ul></div>\n";
	}
	
	return $ok;
}

function vtm_save_progress($laststep) {
	global $vtmglobal;
	
	if ($laststep != '' && $vtmglobal['charGenStatus'] != 'Submitted') {
		//print "<p>Saving $laststep</p>";
		$vtmglobal['characterID'] = call_user_func($vtmglobal['flow'][$laststep]['save']);
	}

	return $vtmglobal['characterID'];
}

function vtm_save_attributes() {
	global $wpdb;
	global $vtmglobal;
	
	$items  = vtm_get_chargen_itemlist('STAT');
	$saved  = vtm_get_chargen_saved('STAT');
	$posted = isset($_POST['attribute_value']) ? $_POST['attribute_value'] : array();

	foreach ($items as $attribute) {
		$key     = sanitize_key($attribute['ITEMNAME']);
		$value   = isset($posted[$key]) ? $posted[$key] : 0;
		$comment = isset($saved[$key]->comment) ? $saved[$key]->comment : '';

		$data = array(
			'CHARACTER_ID' => $vtmglobal['characterID'],
			'STAT_ID'      => $attribute['ITEMTABLE_ID'],
			'LEVEL'        => $value,
			'COMMENT'      => $comment
		);
		if (isset($saved[$key])) {
			// update
			$wpdb->update(VTM_TABLE_PREFIX . "CHARACTER_STAT",
				$data,
				array (
					'ID' => $saved[$key]->chartableid
				)
			);
		} else {
			// insert
			$wpdb->insert(VTM_TABLE_PREFIX . "CHARACTER_STAT",
						$data,
						array ('%d', '%d', '%d', '%s')
					);
		}
	}		
	
	// Delete appearance, if it's no longer needed
	if (isset($saved['appearance']) && !isset($posted['appearance'])) {
		// Delete
		$sql = "DELETE FROM " . VTM_TABLE_PREFIX . "CHARACTER_STAT
				WHERE CHARACTER_ID = %s AND STAT_ID = %s";
		$wpdb->get_results($wpdb->prepare($sql,$vtmglobal['characterID'],$saved['appearance']->itemid));
	}

	return $vtmglobal['characterID'];

}

function vtm_save_rituals() {
	global $wpdb;
	global $vtmglobal;
	
	$rituals = vtm_get_chargen_rituals();
	$saved  = vtm_get_chargen_saved('RITUAL');
	$new = isset($_POST['ritual_value']) ? $_POST['ritual_value'] : array();
	
	foreach ($rituals as $ritual) {
		$key     = sanitize_key($ritual['ITEMNAME']);
		$value   = isset($new[$key]) ? $new[$key] : 0;
	
		$data = array(
			'CHARACTER_ID' => $vtmglobal['characterID'],
			'RITUAL_ID'    => $ritual['ITEMTABLE_ID'],
			'LEVEL'        => $value
		);
		//print_r($data);
		if (isset($saved[$key])) {
			// update
			$wpdb->update(VTM_TABLE_PREFIX . "CHARACTER_RITUAL",
				$data,
				array (
					'ID' => $saved[$key]->chartableid
				)
			);
		} 
		elseif (isset($new[$key])) {
			// insert
			$wpdb->insert(VTM_TABLE_PREFIX . "CHARACTER_RITUAL",
						$data,
						array ('%d', '%d', '%d')
					);
		}
	}
	
	// Delete anything no longer needed
	foreach ($saved as $id => $value) {
		if (!isset($new[$id]) || $new[$id] <= 0) {
			// Delete
			$sql = "DELETE FROM " . VTM_TABLE_PREFIX . "CHARACTER_RITUAL
					WHERE CHARACTER_ID = %s AND RITUAL_ID = %s";
			$sql = $wpdb->prepare($sql,$vtmglobal['characterID'],$saved[$id]->itemid);
			//echo "<li>Delete $id ($sql)</li>\n";
			$wpdb->get_results($sql);
		}
	}

	return $vtmglobal['characterID'];
}

function vtm_save_freebies() {
	global $wpdb;
	global $vtmglobal;

	// Delete current pending spends
	$sql = "DELETE FROM " . VTM_TABLE_PREFIX . "PENDING_FREEBIE_SPEND
			WHERE CHARACTER_ID = %s";
	$sql = $wpdb->prepare($sql, $vtmglobal['characterID']);
	$result = $wpdb->get_results($sql);
	
	$freebiecosts['STAT']       = vtm_get_chargen_costs('STAT', 'FREEBIE_COST');
	$freebiecosts['SKILL']      = vtm_get_chargen_costs('SKILL', 'FREEBIE_COST');
	$freebiecosts['DISCIPLINE'] = vtm_get_chargen_costs('DISCIPLINE', 'FREEBIE_COST');
	$freebiecosts['BACKGROUND'] = vtm_get_chargen_costs('BACKGROUND', 'FREEBIE_COST');
	$freebiecosts['MERIT']      = vtm_get_chargen_costs('MERIT', 'FREEBIE_COST');
	$freebiecosts['PATH']       = vtm_get_chargen_costs('PATH', 'FREEBIE_COST');
	$freebiecosts['ROAD_OR_PATH'] = vtm_get_chargen_costs('ROAD_OR_PATH', 'FREEBIE_COST');
	$freebiecosts['STAT'] = array_merge($freebiecosts['STAT'], vtm_get_chargen_costs('ROAD_OR_PATH', 'FREEBIE_COST'));

	$templatefree['SKILL']      = vtm_get_free_levels('SKILL');
	
	$current['STAT']       = vtm_get_chargen_saved('STAT');
	$current['SKILL']      = vtm_get_chargen_saved('SKILL');
	$current['DISCIPLINE'] = vtm_get_chargen_saved('DISCIPLINE');
	$current['BACKGROUND'] = vtm_get_chargen_saved('BACKGROUND');
	$current['MERIT']      = vtm_get_chargen_saved('MERIT');
	$current['PATH']       = vtm_get_chargen_saved('PATH');
	$current['STAT'] = array_merge($current['STAT'], vtm_get_current_road());
			
	$bought['STAT']       = isset($_POST['freebie_stat']) ? $_POST['freebie_stat'] : array();
	$bought['SKILL']      = isset($_POST['freebie_skill']) ? $_POST['freebie_skill'] : array();
	$bought['DISCIPLINE'] = isset($_POST['freebie_discipline']) ? $_POST['freebie_discipline'] : array();
	$bought['BACKGROUND'] = isset($_POST['freebie_background']) ? $_POST['freebie_background'] : array();
	$bought['MERIT']      = isset($_POST['freebie_merit']) ? $_POST['freebie_merit'] : array();
	$bought['PATH']       = isset($_POST['freebie_path']) ? $_POST['freebie_path'] : array();

	$itemids['STAT']       = isset($_POST['freebie_stat_itemid']) ? $_POST['freebie_stat_itemid'] : array();
	$itemids['SKILL']      = isset($_POST['freebie_skill_itemid']) ? $_POST['freebie_skill_itemid'] : array();
	$itemids['DISCIPLINE'] = isset($_POST['freebie_discipline_itemid']) ? $_POST['freebie_discipline_itemid'] : array();
	$itemids['BACKGROUND'] = isset($_POST['freebie_background_itemid']) ? $_POST['freebie_background_itemid'] : array();
	$itemids['MERIT']      = isset($_POST['freebie_merit_itemid']) ? $_POST['freebie_merit_itemid'] : array();
	$itemids['PATH']       = isset($_POST['freebie_path_itemid']) ? $_POST['freebie_path_itemid'] : array();

	$specialisation['STAT']       = isset($_POST['freebie_stat_spec']) ? $_POST['freebie_stat_spec'] : array();
	$specialisation['SKILL']      = isset($_POST['freebie_skill_spec']) ? $_POST['freebie_skill_spec'] : array();
	$specialisation['DISCIPLINE'] = isset($_POST['freebie_discipline_spec']) ? $_POST['freebie_discipline_spec'] : array();
	$specialisation['BACKGROUND'] = isset($_POST['freebie_background_spec']) ? $_POST['freebie_background_spec'] : array();
	$specialisation['MERIT']      = isset($_POST['freebie_merit_spec']) ? $_POST['freebie_merit_spec'] : array();
	$specialisation['PATH']       = isset($_POST['freebie_path_spec']) ? $_POST['freebie_path_spec'] : array();

	$pending_detail['MERIT']      = isset($_POST['freebie_merit_detail']) ? $_POST['freebie_merit_detail'] : array();
	$pending_detail['BACKGROUND'] = isset($_POST['freebie_background_detail']) ? $_POST['freebie_background_detail'] : array();
	
	$pending_xp['DISCIPLINE'] = vtm_get_pending_chargen_xp('DISCIPLINE');
	
	
	//print_r($bought);
	// Add free skills to bought skills
	foreach ($templatefree as $type => $items) {
		foreach ($items as $key => $row) {
			if (isset($bought[$type][$key])) {
				if ($bought[$type][$key] < $row->LEVEL) {
					if (!isset($current[$type][$key]) || (isset($current[$type][$key]) && $current[$type][$key]->level_from < $row->LEVEL)) {
						$bought[$type][$key] = $row->LEVEL;
						//echo "<li>New bought level for $type $key is {$bought[$type][$key]}</li>";
					}
				} 
			} 
			elseif (!isset($current[$type][$key])) {
				//echo "<li>Adding $type $key to level {$row->LEVEL}</li>";
				$bought[$type][$key] = $row->LEVEL;
			}
		}
	}
	
	foreach ($bought as $type => $items) {
		foreach ($items as $key => $levelto) {
			if (isset($templatefree[$type][$key]->LEVEL))
				$freelevel = $templatefree[$type][$key]->LEVEL;
			else
				$freelevel = 0;
			$currlevel  = isset($current[$type][$key]->level_from)  ? $current[$type][$key]->level_from  : 0;
			$levelfrom  = max($currlevel, $freelevel);
			$costkey    = preg_replace("/_\d+$/", "", $key);
			
			if ($type == 'STAT' && $key == 'pathrating') {
				$costkey = sanitize_key($current[$type][$key]->grp);
			}
			if ($type == 'MERIT') {
				$levelfrom = 0;
				$levelto = 1;
			}
			$amount      = isset($freebiecosts[$type][$costkey][$levelfrom][$levelto]) ? $freebiecosts[$type][$costkey][$levelfrom][$levelto] : 0;
			$chartableid = isset($current[$type][$key]->chartableid) ? $current[$type][$key]->chartableid : 0;
			$detail      = isset($pending_detail[$type][$key]) ? $pending_detail[$type][$key] : '';
			$itemid = $itemids[$type][$key];

			if (isset($specialisation[$type][$key]) && $specialisation[$type][$key] != '')
				$spec = $specialisation[$type][$key];
			elseif (isset($current[$type][$key]->specialisation) && $current[$type][$key]->specialisation != '')
				$spec =  $current[$type][$key]->specialisation;
			elseif (isset($templatefree[$key][$key]->SPECIALISATION)) 
				$spec = $templatefree[$key][$key]->SPECIALISATION;
			else
				$spec = '';
			
			//echo "<li>key: $key, from level $levelfrom to $levelto, spec: $spec, cost: $amount, chartableid: $chartableid, detail: $detail</li>\n";
			
			if ($levelto > $levelfrom) {
				
				$data = array (
					'CHARACTER_ID'   => $vtmglobal['characterID'],
					'CHARTABLE'      => 'CHARACTER_' . $type,
					'CHARTABLE_ID'   => $chartableid,
					'LEVEL_FROM'     => $levelfrom,
					
					'LEVEL_TO'       => $type == 'MERIT' ? $amount : $levelto,
					'AMOUNT'         => $amount,
					'ITEMTABLE'      => $type,
					'ITEMNAME'       => $key,
					
					'ITEMTABLE_ID'   => $itemid,
					'SPECIALISATION' => $spec,
					'PENDING_DETAIL' => $detail
				);
				$wpdb->insert(VTM_TABLE_PREFIX . "PENDING_FREEBIE_SPEND",
							$data,
							array (
								'%d', '%s', '%d', '%d',
								'%d', '%d', '%s', '%s',
								'%d', '%s', '%s'
							)
						);
				if ($wpdb->insert_id == 0) {
					echo "<p style='color:red'><b>Error:</b> $key could not be inserted</p>\n";
				}		
				
				//print_r($data);
				
			}
			// Magik Disciplines need their primary paths updated
			if ($type == 'DISCIPLINE') {
				$level = $levelfrom > $levelto ? $levelfrom : $levelto; // levelto is 0 if spend is cleared
				$level = $levelto   > 5 ? 5 : $level;
				
				// Do this here, in case discipline levels just got updated
				$primarypaths = vtm_get_chargen_paths();
				$cgpp = vtm_get_chargen_primarypath();
				
				// Add primary path if it isn't there already
				if (isset($primarypaths[$itemid]) && !isset($cgpp[$primarypaths[$itemid]->pathid])) {
					
					$data = array(
						'CHARACTER_ID'  => $vtmglobal['characterID'],
						'PATH_ID'       => $primarypaths[$itemid]->pathid,
						'DISCIPLINE_ID' => $itemid
					);
					
					$wpdb->insert(VTM_TABLE_PREFIX . "CHARACTER_PRIMARY_PATH",
								$data,
								array ('%d', '%d', '%d')
							);
					//echo  "<li>Added Primary Path {$primarypaths[$itemid]->pathid} for $key ({$itemid})</li>\n";

					
					$data = array(
						'CHARACTER_ID' => $vtmglobal['characterID'],
						'PATH_ID'      => $primarypaths[$itemid]->pathid,
						'LEVEL'        => $level
					);
					$wpdb->insert(VTM_TABLE_PREFIX . "CHARACTER_PATH",
								$data,
								array ('%d', '%d', '%d')
							);
					//echo  "<li>Added Primary Path level {$primarypaths[$itemid]->pathid} to $level</li>\n";
				}
				
				// only update the path level if we haven't spent any XP on the discipline
				//print "<p>DEBUG: $itemid, {$primarypaths[$itemid]}, $type, $key, {$pending_xp[$type][$key]->value}</p>";
				elseif (isset($primarypaths[$itemid]) && !isset($pending_xp[$type][$key]->value)) {
					
					// Then update the primary path level
					//echo "<p>Freebies: Update primary path for $key to $level (max of 5)</p>";
					$result = $wpdb->update(VTM_TABLE_PREFIX . "CHARACTER_PATH",
						array('LEVEL' => $level),
						array(
							'CHARACTER_ID' => $vtmglobal['characterID'],
							'PATH_ID'      => $primarypaths[$itemid]->pathid
						)
					);
					if (!$result && $result !== 0) {
						echo "<p>ERROR: Unable to update primary path level</p>\n";
						$wpdb->print_error();
					} else {
						
						//echo  "<li>Updated Primary Path level {$primarypaths[$itemid]->pathid} to $level</li>\n";
					
					}

				}
				
			}
			
		}
	}

	return $vtmglobal['characterID'];
}

function vtm_save_history() {
	global $wpdb;
	global $vtmglobal;

	$sql = "SELECT questions.ID as questID, cq.ID as id, cq.PENDING_DETAIL as detail
			FROM 
				" . VTM_TABLE_PREFIX . "EXTENDED_BACKGROUND questions,
				" . VTM_TABLE_PREFIX . "CHARACTER_EXTENDED_BACKGROUND cq
			WHERE
				questions.ID = cq.QUESTION_ID
				AND cq.CHARACTER_ID = %s";
	$sql = $wpdb->prepare($sql, $vtmglobal['characterID']);
	$saved = $wpdb->get_results($sql, OBJECT_K);
	//echo "<p>SQL: $sql</p>\n";
	//print_r($saved);
	
	// Save Ext Background questions
	if (isset($_POST['question'])) {
		foreach ($_POST['question'] as $index => $text) {
		
			$data = array (
				'CHARACTER_ID'  	=> $vtmglobal['characterID'],
				'QUESTION_ID'		=> $index,
				'APPROVED_DETAIL'	=> '',
				'PENDING_DETAIL'	=> trim($text),
				'DENIED_DETAIL'		=> '',
			);
			//print_r($data);
			
			if (isset($saved[$index])) {
				//echo "<li>Updating id {$saved[$index]->id} for question $index</li>\n";
				$wpdb->update(VTM_TABLE_PREFIX . "CHARACTER_EXTENDED_BACKGROUND",
					$data,
					array ('ID' => $saved[$index]->id)
				);
			} else {
				//echo "<li>Adding question $index</li>\n";
				$wpdb->insert(VTM_TABLE_PREFIX . "CHARACTER_EXTENDED_BACKGROUND",
							$data,
							array ('%d', '%d', '%s', '%s', '%s')
				);
				
			}
		}
	}
	// Save Merit/Flaw questions
	if (isset($_POST['meritquestion'])) {
		foreach ($_POST['meritquestion'] as $index => $text) {
		
			$data = array (
				'PENDING_DETAIL'	=> trim($text)
			);
			
			$wpdb->update(VTM_TABLE_PREFIX . "PENDING_FREEBIE_SPEND",
				$data,
				array ('ID' => $index)
			);
		}
	}
	
	// Save Background questions
	if (isset($_POST['bgquestion'])) {
		foreach ($_POST['bgquestion'] as $index => $text) {
			$data = array (
				'PENDING_DETAIL'	=> trim($text)
			);
			
			$wpdb->update(VTM_TABLE_PREFIX . $_POST['bgquestion_source'][$index],
				$data,
				array ('ID' => $index)
			);
		}
	}

	return $vtmglobal['characterID'];
}

function vtm_save_finish() {
	global $wpdb;
	global $vtmglobal;

	// Save CHARACTER information
	$dob = $_POST['year_dob'] . '-' . $_POST['month_dob'] . '-' . $_POST['day_dob'];
	$doe = $_POST['year_doe'] . '-' . $_POST['month_doe'] . '-' . $_POST['day_doe'];
	
	$data = array (
		'SIRE'                => $_POST['sire'],
		'DATE_OF_BIRTH'       => $dob,
		'DATE_OF_EMBRACE'     => $doe,
		'GENERATION_ID'       => $_POST['generationID'],
		'PRONOUNS'            => $_POST['pronouns'],
		'ROAD_OR_PATH_RATING' => isset($_POST['pathrating']) ? $_POST['pathrating'] : 0,
	);
	
	$result = $wpdb->update(VTM_TABLE_PREFIX . "CHARACTER",
		$data,
		array (
			'ID' => $vtmglobal['characterID']
		),
		array('%s', '%s', '%s', '%d', '%s')
	);		
	if (!$result && $result !== 0) {
		echo "<p style='color:red'>Failed to save:</p>\n";
		$wpdb->show_errors();
		$wpdb->print_error();
		echo "<pre>";
		print_r($data);
		echo "</pre>";
	}
	
	// Save CHARACTER_GENERATION information
	$data = array (
		'NOTE_TO_ST'  => trim($_POST['noteforST']),
	);
	$result = $wpdb->update(VTM_TABLE_PREFIX . "CHARACTER_GENERATION",
		$data,
		array (
			'CHARACTER_ID' => $vtmglobal['characterID']
		),
		array('%s')
	);		
	//print_r($_POST);
	// Save Specialities
	if (isset($_POST['fullname'])) {
	
		// Remove anything with a speciality to ensure that skills haven't dropped
		// since the last time the specialities were saved
		$wpdb->update(VTM_TABLE_PREFIX . "CHARACTER_STAT",  array('COMMENT' => ''), array('CHARACTER_ID' => $vtmglobal['characterID']));
		$wpdb->update(VTM_TABLE_PREFIX . "CHARACTER_SKILL", array('COMMENT' => ''), array('CHARACTER_ID' => $vtmglobal['characterID']));
		$wpdb->update(VTM_TABLE_PREFIX . "CHARACTER_MERIT", array('COMMENT' => ''), array('CHARACTER_ID' => $vtmglobal['characterID']));
		$wpdb->update(VTM_TABLE_PREFIX . "CHARACTER_BACKGROUND",  array('COMMENT' => ''), array('CHARACTER_ID' => $vtmglobal['characterID']));
		$wpdb->update(VTM_TABLE_PREFIX . "PENDING_FREEBIE_SPEND", array('SPECIALISATION' => ''), array('CHARACTER_ID' => $vtmglobal['characterID']));
		$wpdb->update(VTM_TABLE_PREFIX . "PENDING_XP_SPEND",      array('SPECIALISATION' => ''), array('CHARACTER_ID' => $vtmglobal['characterID']));
		
		// Then re-add the ones we need
		foreach ($_POST['fullname'] as $index => $name) {
			$comment = $_POST['comment'][$index];
			$id      = $_POST['tableid'][$index];
			$table   = $_POST['tablename'][$index];
			
			// for one of these tables, it is a free spend so there is nowhere to save
			// the specialisation to
			if ($table == 'SKILL' || $table == 'BACKGROUND')
				continue;
			
			switch($table) {
				Case 'PENDING_FREEBIE_SPEND': $colname = 'SPECIALISATION'; break;
				Case 'PENDING_XP_SPEND':  	  $colname = 'SPECIALISATION'; break;
				default:                      $colname = 'COMMENT';
			}

			$data = array (
				$colname => $comment
			);
			$result = $wpdb->update(VTM_TABLE_PREFIX . $table, $data, array ('ID' => $id),array('%s'));		

			// if ($result) 			echo "<p style='color:green'>Updated $name speciality with $comment</p>\n";
			// else if ($result === 0) echo "<p style='color:orange'>No updates made to $name speciality</p>\n";
			// else {
				// $wpdb->show_errors();
				// $wpdb->print_error();
				// echo "<p style='color:red'>Could not update $name speciality</p>\n";
			// }
		}
	}
	return $vtmglobal['characterID'];
}

function vtm_save_xp() {
	global $wpdb;
	global $vtmglobal;

	// Delete current pending spends
	$sql = "DELETE FROM " . VTM_TABLE_PREFIX . "PENDING_XP_SPEND
			WHERE CHARACTER_ID = %s";
	$sql = $wpdb->prepare($sql, $vtmglobal['characterID']);
	$result = $wpdb->get_results($sql);
	
	$freebiecosts['STAT']       = vtm_get_chargen_costs('STAT', 'FREEBIE_COST');
	$freebiecosts['SKILL']      = vtm_get_chargen_costs('SKILL', 'FREEBIE_COST');
	$freebiecosts['DISCIPLINE'] = vtm_get_chargen_costs('DISCIPLINE', 'FREEBIE_COST');
	$freebiecosts['MERIT']      = vtm_get_chargen_costs('MERIT', 'FREEBIE_COST');
	$freebiecosts['PATH']       = vtm_get_chargen_costs('PATH', 'FREEBIE_COST');

	$templatefree['SKILL']      = vtm_get_free_levels('SKILL');

	$current['STAT']       = vtm_get_chargen_saved('STAT');
	$current['SKILL']      = vtm_get_chargen_saved('SKILL');
	$current['DISCIPLINE'] = vtm_get_chargen_saved('DISCIPLINE');
	$current['MERIT']      = vtm_get_chargen_saved('MERIT');
	$current['PATH']       = vtm_get_chargen_saved('PATH');
	$current['RITUAL']     = vtm_get_chargen_saved('RITUAL');
	
	$bought['STAT']       = isset($_POST['xp_stat']) ? $_POST['xp_stat'] : array();
	$bought['SKILL']      = isset($_POST['xp_skill']) ? $_POST['xp_skill'] : array();
	$bought['DISCIPLINE'] = isset($_POST['xp_discipline']) ? $_POST['xp_discipline'] : array();
	$bought['MERIT']      = isset($_POST['xp_merit']) ? $_POST['xp_merit'] : array();
	$bought['PATH']       = isset($_POST['xp_path']) ? $_POST['xp_path'] : array();
	$bought['RITUAL']     = isset($_POST['xp_ritual']) ? $_POST['xp_ritual'] : array();

	$comments['STAT']   = isset($_POST['xp_stat_comment'])   ? $_POST['xp_stat_comment'] : array();
	$comments['SKILL']  = isset($_POST['xp_skill_comment']) ? $_POST['xp_skill_comment'] : array();
	$comments['MERIT']  = isset($_POST['xp_merit_comment']) ? $_POST['xp_merit_comment'] : array();
	
	$freebies['STAT']       = vtm_get_pending_freebies('STAT');
	$freebies['SKILL']      = vtm_get_pending_freebies('SKILL');
	$freebies['DISCIPLINE'] = vtm_get_pending_freebies('DISCIPLINE');
	$freebies['MERIT']      = vtm_get_pending_freebies('MERIT');
	$freebies['PATH']       = vtm_get_pending_freebies('PATH');
	
	$xpcosts['STAT']       = vtm_get_chargen_costs('STAT', 'XP_COST');
	$xpcosts['SKILL']      = vtm_get_chargen_costs('SKILL', 'XP_COST');
	$xpcosts['DISCIPLINE'] = vtm_get_chargen_costs('DISCIPLINE', 'XP_COST');
	$xpcosts['MERIT']      = vtm_get_chargen_costs('MERIT', 'XP_COST');
	$xpcosts['PATH']       = vtm_get_chargen_costs('PATH', 'XP_COST');
	$xpcosts['RITUAL']     = vtm_get_chargen_costs('RITUAL', 'XP_COST');

	// $items['STAT']       = vtm_sanitize_array(vtm_get_chargen_stats(OBJECT_K));
	// $items['SKILL']      = vtm_sanitize_array(vtm_get_chargen_abilities(1, OBJECT_K));
	// $items['DISCIPLINE'] = vtm_sanitize_array(vtm_get_chargen_disciplines( OBJECT_K));
	// $items['MERIT']      = vtm_sanitize_array(vtm_get_chargen_merits(OBJECT_K));
	// $items['PATH']       = vtm_sanitize_array(vtm_get_chargen_paths(OBJECT_K));
	// $items['RITUAL']     = vtm_sanitize_array(vtm_get_chargen_rituals(OBJECT_K));
	$itemids['STAT']       = isset($_POST['xp_stat_itemid']) ? $_POST['xp_stat_itemid'] : array();
	$itemids['SKILL']      = isset($_POST['xp_skill_itemid']) ? $_POST['xp_skill_itemid'] : array();
	$itemids['DISCIPLINE'] = isset($_POST['xp_discipline_itemid']) ? $_POST['xp_discipline_itemid'] : array();
	$itemids['MERIT']      = isset($_POST['xp_merit_itemid']) ? $_POST['xp_merit_itemid'] : array();
	$itemids['PATH']       = isset($_POST['xp_path_itemid']) ? $_POST['xp_path_itemid'] : array();
	$itemids['RITUAL']     = isset($_POST['xp_ritual_itemid']) ? $_POST['xp_ritual_itemid'] : array();
	$names['STAT']       = isset($_POST['xp_stat_name']) ? $_POST['xp_stat_name'] : array();
	$names['SKILL']      = isset($_POST['xp_skill_name']) ? $_POST['xp_skill_name'] : array();
	$names['DISCIPLINE'] = isset($_POST['xp_discipline_name']) ? $_POST['xp_discipline_name'] : array();
	$names['MERIT']      = isset($_POST['xp_merit_name']) ? $_POST['xp_merit_name'] : array();
	$names['PATH']       = isset($_POST['xp_path_name']) ? $_POST['xp_path_name'] : array();
	$names['RITUAL']     = isset($_POST['xp_ritual_name']) ? $_POST['xp_ritual_name'] : array();

	// Add free skills to bought skills
	foreach ($templatefree as $type => $data) {
		foreach ($data as $key => $row) {
		
			//echo "<li>$key - {$row ->LEVEL} {$row->SPECIALISATION}</li>";
		
			// Ensure you have the free dot as a minimum value if 
			// addition spend on this item has been cancelled and it wasn't bought
			// with freebies
			if (isset($bought[$type][$key])) {
				//echo "<li>Bought level {$bought[$type][$key]} of $key</li>";
				if ($bought[$type][$key] < $row->LEVEL) {
					if (!isset($freebies[$type][$key])) {
						if (!isset($current[$type][$key]) || (isset($current[$type][$key]) && $current[$type][$key]->level_from < $row->LEVEL)) {
							$bought[$type][$key] = $row->LEVEL;
							//echo "<li>New bought level for $type $key is {$bought[$type][$key]}</li>";
						}
					} 
				}
			} 
			// Add the dot if you haven't already bought it
			elseif (!isset($current[$type][$key]) && !isset($freebies[$type][$key])) {
				//echo "<li>Adding $type $key to level {$row->LEVEL}</li>";
				$bought[$type][$key] = $row->LEVEL;
				$comments[$type][$key] = $row->SPECIALISATION;
			} 
			else {
				//echo "<li>Not bought $key</li>";
			}
		}
	}

	//echo "<pre>";
	//print_r($bought['DISCIPLINE']);
	//print_r($current['SKILL']);
	//print_r($freebies['SKILL']);
	//print_r($items);
	//print_r($templatefree);
	//echo "</pre>";
	
	foreach ($bought as $type => $row) {
		foreach ($row as $key => $value) {
			if (isset($templatefree[$type][$key]->LEVEL))
				$freelevel = $templatefree[$type][$key]->LEVEL;
			else
				$freelevel = 0;

			$currlevel   = isset($current[$type][$key]->level_from)  ? $current[$type][$key]->level_from  : 0;
			$levelfrom   = max($currlevel, $freelevel);
			$levelfrom   = isset($freebies[$type][$key]->value) ? $freebies[$type][$key]->value : $levelfrom;
			$costkey    = preg_replace("/_\d+$/", "", $key);

			if ($type == 'MERIT' || $type == 'RITUAL') {
				$levelfrom = 0;
				$value = 1;
			}
			$amount      = isset($xpcosts[$type][$key][$levelfrom][$value]) ? $xpcosts[$type][$key][$levelfrom][$value] : 0;
			$itemid      = $itemids[$type][$key];
			$name        = $names[$type][$key];
			$spec        = isset($comments[$type][$key]) ? $comments[$type][$key] : '';
			$chartable  = 'CHARACTER_' . $type;
			
			if (isset($current[$type][$key]->chartableid)) {
				$chartableid = $current[$type][$key]->chartableid;
			}
			elseif (isset($freebies[$type][$key]->id)) {
				$chartableid = $freebies[$type][$key]->id;
				$chartable = 'PENDING_FREEBIE_SPEND';
			} 
			else {
				$chartableid = 0;
			}
			
			if ($value > $levelfrom) {

				//echo "<li>$key/$name - type: $type, from: $levelfrom, value: $value, spec: $spec, cost: -$amount</li>\n";
				
				$data = array (
					'PLAYER_ID'       => $vtmglobal['playerID'],
					'CHARACTER_ID'    => $vtmglobal['characterID'],
					'CHARTABLE'       => $chartable,
					'CHARTABLE_ID'    => $chartableid,
					
					'CHARTABLE_LEVEL' => $value,
					'AWARDED'         => Date('Y-m-d'),
					'AMOUNT'          => -$amount,
					'COMMENT'         => "Character Generation: " . stripslashes($name) . " $levelfrom > $value",
					
					'SPECIALISATION'  => $spec,
					'TRAINING_NOTE'   => 'Character Generation',
					'ITEMTABLE'       => $type,
					'ITEMNAME'        => $key,
					
					'ITEMTABLE_ID'    => $itemid
				);
				//echo "<pre>\n";
				//print_r($data);
				//echo "</pre>\n";
				
				$wpdb->insert(VTM_TABLE_PREFIX . "PENDING_XP_SPEND",
							$data,
							array (
								'%d', '%d', '%s', '%d',
								'%d', '%s', '%d', '%s',
								'%s', '%s', '%s', '%s',
								'%d'
							)
						);
				if ($wpdb->insert_id == 0) {
					echo "<p style='color:red'><b>Error:</b> $name could not be inserted</p>\n";
				}
				

			}
			
			// Magik Disciplines need their primary paths updated
			if ($type == 'DISCIPLINE') {
				$level = $levelfrom > $value ? $levelfrom : $value; // value is 0 if spend is cleared
				$level = $value > 5 ? 5 : $level;
				
				// Do this here, in case discipline levels just got updated
				$primarypaths = vtm_get_chargen_paths();
				$cgpp = vtm_get_chargen_primarypath();
				
				if (isset($primarypaths[$itemid]) && !isset($cgpp[$primarypaths[$itemid]->pathid])) {
					$data = array(
						'CHARACTER_ID'  => $vtmglobal['characterID'],
						'PATH_ID'       => $primarypaths[$itemid]->pathid,
						'DISCIPLINE_ID' => $itemid
					);
					
					$wpdb->insert(VTM_TABLE_PREFIX . "CHARACTER_PRIMARY_PATH",
								$data,
								array ('%d', '%d', '%d')
							);
					//echo  "<li>Added Primary Path {$primarypaths[$itemid]->pathid} for $key ({$itemid})</li>\n";

					
					$data = array(
						'CHARACTER_ID' => $vtmglobal['characterID'],
						'PATH_ID'      => $primarypaths[$itemid]->pathid,
						'LEVEL'        => $level
					);
					$wpdb->insert(VTM_TABLE_PREFIX . "CHARACTER_PATH",
								$data,
								array ('%d', '%d', '%d')
							);
					//echo  "<li>Added Primary Path level {$primarypaths[$itemid]->pathid} to $level</li>\n";
					
				} 
				elseif(isset($primarypaths[$itemid]->pathid)) {
					//echo "<p>XP: Update primary path for $key to $level (max of 5)</p>";
					$result = $wpdb->update(VTM_TABLE_PREFIX . "CHARACTER_PATH",
						array('LEVEL' => $level),
						array(
							'CHARACTER_ID' => $vtmglobal['characterID'],
							'PATH_ID'      => $primarypaths[$itemid]->pathid
						)
					);
					if (!$result && $result !== 0) {
						echo "<p>ERROR: Unable to update primary path level</p>\n";
						$wpdb->print_error();
					}
				}
				
			}
		}
	}

	return $vtmglobal['characterID'];
}

function vtm_save_abilities() {
	global $wpdb;
	global $vtmglobal;

	$items  = vtm_get_chargen_itemlist('SKILL');
	$saved  = vtm_get_chargen_saved('SKILL');
	$posted = isset($_POST['ability_value']) ? $_POST['ability_value'] : array();
	$templatefree = vtm_get_free_levels('SKILL');

	foreach ($items as $ability) {
		$suffix = "";
		$i      = 1;
		if ($ability['MULTIPLE'] == 'Y') {
			$suffix = "_$i";
			$i++;
		}		
		$key    = sanitize_key($ability['ITEMNAME']) . $suffix;
		
		while ((isset($posted[$key]) || isset($saved[$key]->level_from)) && $i != 0) {
		
			$value   = isset($posted[$key]) ? $posted[$key] : 0;
			//echo "<li>Key: $key, Value: $value, i: $i, suffix: $suffix, multiple: {$ability['MULTIPLE']}</li>";
			if ($value > 0) {
				if (isset($saved[$key]->comment) && $saved[$key]->comment != '')
					$comment = $saved[$key]->comment ;
				elseif (isset($templatefree[$key]->SPECIALISATION))
					$comment = $templatefree[$key]->SPECIALISATION;
				else
					$comment = '';
				
				$data = array(
					'CHARACTER_ID'  => $vtmglobal['characterID'],
					'SKILL_ID'      => $ability['ITEMTABLE_ID'],
					'LEVEL'         => $value,
					'COMMENT'		=> $comment
				);
				//print_r($data);
				if (isset($saved[$key])) {
					if ($saved[$key]->level_from != $value) {
						//echo "<li>Updated $key at $value</li>\n";
						// update
						$wpdb->update(VTM_TABLE_PREFIX . "CHARACTER_SKILL",
							$data,
							array (
								'ID' => $saved[$key]->chartableid
							)
						);
					} //else {
					//	echo "<li>No need to update $key</li>\n";
					//}
				} else {
					//echo "<li>Added $key at $value</li>\n";
					// insert
					$wpdb->insert(VTM_TABLE_PREFIX . "CHARACTER_SKILL",
								$data,
								array ('%d', '%d', '%d', '%s')
							);
				}
			}
			if ($ability['MULTIPLE'] == 'Y') {
				$suffix = "_$i";
				$i++;
				$key    = sanitize_key($ability['ITEMNAME']) . $suffix;
				//echo "<li>loop? $suffix</li>";
			} else {
				$i = 0;
			}
		}
	}
		
	// Delete anything no longer needed
	foreach ($saved as $id => $value) {
	
		if (!isset($posted[$id]) || $posted[$id] <= 0) {
			// Delete
			$sql = "DELETE FROM " . VTM_TABLE_PREFIX . "CHARACTER_SKILL
					WHERE CHARACTER_ID = %s AND SKILL_ID = %s";
			$sql = $wpdb->prepare($sql,$vtmglobal['characterID'],$saved[$id]->itemid);
			//echo "<li>Delete $id ($sql)</li>\n";
			$wpdb->get_results($sql);
		}
	}

	return $vtmglobal['characterID'];
}

function vtm_save_disciplines() {
	global $wpdb;
	global $vtmglobal;

	$items  = vtm_get_chargen_itemlist('DISCIPLINE');
	$saved  = vtm_get_chargen_saved('DISCIPLINE');
	$posted = isset($_POST['discipline_value']) ? $_POST['discipline_value'] : array();
	$postpppid = isset($_POST['primarypathspid']) ? $_POST['primarypathspid'] : array();
	$postpptid = isset($_POST['primarypathstid']) ? $_POST['primarypathstid'] : array();
	$postpplvl = isset($_POST['primarypathslvl']) ? $_POST['primarypathslvl'] : array();
	$primarypaths = vtm_get_chargen_paths(0);

	foreach ($items as $discipline) {
		$key     = sanitize_key($discipline['ITEMNAME']);
		$value   = isset($posted[$key]) ? $posted[$key] : 0;
		if ($value > 0) {
			$data = array(
				'CHARACTER_ID'  => $vtmglobal['characterID'],
				'DISCIPLINE_ID' => $discipline['ITEMTABLE_ID'],
				'LEVEL'         => $value
			);
			if (isset($saved[$key])) {
				if ($saved[$key]->level_from != $value) {
					//echo "<li>Updated $key at $value</li>\n";
					// update
					$wpdb->update(VTM_TABLE_PREFIX . "CHARACTER_DISCIPLINE",
						$data,
						array (
							'ID' => $saved[$key]->chartableid
						)
					);
				} //else {
					//echo "<li>No need to update $key</li>\n";
				//}
			} else {
				//echo "<li>Added $key at $value</li>\n";
				// insert
				$wpdb->insert(VTM_TABLE_PREFIX . "CHARACTER_DISCIPLINE",
							$data,
							array ('%d', '%d', '%d')
						);
			}
		}
		
		if (isset($postpppid[$key])) {
			
			// Add default primary path to the table if it isn't
			// already there
			if (empty($postpptid[$key])) {
				$data = array(
					'CHARACTER_ID' => $vtmglobal['characterID'],
					'PATH_ID' => $postpppid[$key],
					'DISCIPLINE_ID' => $discipline['ITEMTABLE_ID']
				);
				
				$wpdb->insert(VTM_TABLE_PREFIX . "CHARACTER_PRIMARY_PATH",
							$data,
							array ('%d', '%d', '%d')
						);
				//echo  "<li>Added Primary Path {$postpppid[$key]} for $key ({$discipline['ITEMTABLE_ID']})</li>\n";
			}
			
			// Add/update the level of the primary path
			$disclvl = $primarypaths[$postpppid[$key]]->discipline_level;
			$pathlvl = $disclvl > 5 ? 5 : $disclvl;
			if (empty($postpplvl[$key])) {
				$data = array(
					'CHARACTER_ID' => $vtmglobal['characterID'],
					'PATH_ID'      => $postpppid[$key],
					'LEVEL'        => $pathlvl
				);

				// add
				$wpdb->insert(VTM_TABLE_PREFIX . "CHARACTER_PATH",
							$data,
							array ('%d', '%d', '%d')
						);
				//echo  "<li>Added Path {$postpppid[$key]}, level $pathlvl, for $key ({$discipline['ITEMTABLE_ID']}) to PATH</li>\n";
				
			} else {
				$data = array(
					'LEVEL'        => $pathlvl
				);
				// update
				$wpdb->update(VTM_TABLE_PREFIX . "CHARACTER_PATH",
					$data,
					array (
						'PATH_ID'      => $postpppid[$key],
						'CHARACTER_ID' => $vtmglobal['characterID']
					)
				);
				//echo  "<li>Updated Path {$postpppid[$key]}, level $pathlvl, for $key ({$discipline['ITEMTABLE_ID']}) to PATH</li>\n";
			}
			
		}
		
		
	}
		
	// Delete anything no longer needed
	foreach ($saved as $id => $row) {
		if (!isset($posted[$id]) || $posted[$id] == 0) {
			// Delete any selected rituals associated with a deleted discipline
			$sql = "SELECT crit.ID 
					FROM 
						" . VTM_TABLE_PREFIX . "CHARACTER_RITUAL crit,
						" . VTM_TABLE_PREFIX . "RITUAL rit
					WHERE 
						crit.CHARACTER_ID = %s 
						AND crit.RITUAL_ID = rit.ID
						AND rit.DISCIPLINE_ID = %s";
			$sql = $wpdb->prepare($sql,$vtmglobal['characterID'],$saved[$id]->itemid);
			//echo "<p>ritual SQL: $sql</p>";
			$rituals = $wpdb->get_col($sql);
			if (count($rituals) > 0) {
				foreach ($rituals as $rid) {
					$sql = "DELETE FROM " . VTM_TABLE_PREFIX . "CHARACTER_RITUAL
							WHERE ID = %s";
					$sql = $wpdb->prepare($sql,$rid);
					$wpdb->get_results($sql);
				}
				//echo "<li>Delete ritual $rid ($sql)</li>\n";
			}

			// Delete any primary paths associated with a deleted discipline
			$sql = "SELECT cpp.ID 
					FROM 
						" . VTM_TABLE_PREFIX . "CHARACTER_PRIMARY_PATH cpp
					WHERE 
						cpp.CHARACTER_ID = %s 
						AND cpp.DISCIPLINE_ID = %s";
			$sql = $wpdb->prepare($sql,$vtmglobal['characterID'],$saved[$id]->itemid);
			//echo "<p>path SQL: $sql</p>";
			$paths = $wpdb->get_col($sql);
			if (count($paths) > 0) {
				foreach ($paths as $pathid) {
					$sql = "DELETE FROM " . VTM_TABLE_PREFIX . "CHARACTER_PRIMARY_PATH
							WHERE ID = %s";
					$sql = $wpdb->prepare($sql,$pathid);
					$wpdb->get_results($sql);
				}
				//echo "<li>Delete ritual $rid ($sql)</li>\n";
			}

			
			// Delete
			$sql = "DELETE FROM " . VTM_TABLE_PREFIX . "CHARACTER_DISCIPLINE
					WHERE CHARACTER_ID = %s AND DISCIPLINE_ID = %s";
			$sql = $wpdb->prepare($sql,$vtmglobal['characterID'],$saved[$id]->itemid);
			//echo "<li>Delete $id ($sql)</li>\n";
			$wpdb->get_results($sql);
		}
	}

	return $vtmglobal['characterID'];

}

function vtm_save_paths() {
	global $wpdb;
	global $vtmglobal;

	$disc_items  = vtm_get_chargen_itemlist('DISCIPLINE');
	$path_items  = vtm_get_chargen_itemlist('PATH');
	//$primary    = vtm_get_chargen_primarypath();
	//print_r($path_items);
	// $saved  = vtm_get_chargen_saved('DISCIPLINE');
	// $posted = isset($_POST['discipline_value']) ? $_POST['discipline_value'] : array();
	// $postpppid = isset($_POST['primarypathspid']) ? $_POST['primarypathspid'] : array();
	// $postpptid = isset($_POST['primarypathstid']) ? $_POST['primarypathstid'] : array();
	// $postpplvl = isset($_POST['primarypathslvl']) ? $_POST['primarypathslvl'] : array();
	$charpaths = vtm_get_chargen_paths();
	
	// [path_name] = pathlevel
	$postvalues = isset($_POST['path_value']) ? $_POST['path_value'] : array();
	// [discipline_name] = path_name
	$postppaths = isset($_POST['primarypaths']) ? $_POST['primarypaths'] : array();
	// [discipline_name] = discipline_level
	$postdisclvl = isset($_POST['discipline_level']) ? $_POST['discipline_level'] : array();
	// [discipline_name] = full discipline name
	$postdiscname = isset($_POST['discipline_name']) ? $_POST['discipline_name'] : array();
	// [path_name] = discipline_name
	$postpathdisc = isset($_POST['path_disc_id']) ? $_POST['path_disc_id'] : array();
	//print_r($postvalues);

	// Save Primary Path
	// Delete entries so a clean slate for adding
	$sql = "DELETE FROM " . VTM_TABLE_PREFIX . "CHARACTER_PRIMARY_PATH WHERE CHARACTER_ID = '%s'";
	$sql = $wpdb->prepare($sql, $vtmglobal['characterID']);
	$result = $wpdb->get_results($sql);
	// Delete entries so a clean slate for adding
	$sql = "DELETE FROM " . VTM_TABLE_PREFIX . "CHARACTER_PATH WHERE CHARACTER_ID = '%s'";
	$sql = $wpdb->prepare($sql, $vtmglobal['characterID']);
	$result = $wpdb->get_results($sql);
	
	foreach ($disc_items as $discipline) {
		$dkey = sanitize_key($discipline['ITEMNAME']);
		
		if (isset($postppaths[$dkey])) {
			$discID = $discipline['ITEMTABLE_ID'];
			$pkey   = $postppaths[$dkey];
			
			$pathID = 0;
			$pathname = "";
			foreach ($path_items as $paths) {
				//echo "Compare $pkey with " . sanitize_key($paths['ITEMNAME']) . "<br \>";
				if ($pkey == sanitize_key($paths['ITEMNAME'])) {
					$pathID = $paths['ITEMTABLE_ID'];
					$pathname = $paths['ITEMNAME'];
				}
				
			}
			
			//echo "Compare $pathID with {$charpaths[$discID]->pathid}<br>";
			// re-add levels of primary path
			$sql = "DELETE FROM " . VTM_TABLE_PREFIX . "PENDING_FREEBIE_SPEND WHERE CHARACTER_ID = '%s' AND ITEMTABLE = 'PATH'";
			$result = $wpdb->get_results($wpdb->prepare($sql,$vtmglobal['characterID']));
			$sql = "DELETE FROM " . VTM_TABLE_PREFIX . "PENDING_XP_SPEND WHERE CHARACTER_ID = '%s' AND ITEMTABLE = 'PATH'";
			$result = $wpdb->get_results($wpdb->prepare($sql,$vtmglobal['characterID']));
			echo "<div class='vtm_error'><p>Any freebie or experience spends on paths have been cleared after changing the primary path for {$discipline['ITEMNAME']}</p></div>";
			
			$value = $postdisclvl[$dkey] > 5 ? 5 : $postdisclvl[$dkey];
			// Then add levels for the primary paths based on the discipline level
			//echo "<li>Add $pathname ($pathID) path level {$value} for discipline {$discipline['ITEMNAME']} ($discID)</li>";
			
			$data = array(
				'CHARACTER_ID' => $vtmglobal['characterID'],
				'LEVEL' => $value,
				'PATH_ID' => $pathID
			);
			$wpdb->insert(VTM_TABLE_PREFIX . "CHARACTER_PATH",
				$data,
				array ('%d', '%d', '%d')
			);
			if ($wpdb->insert_id == 0) {
				echo "<p style='color:red'><b>Error:</b>Path $pathname could not be updated</p>\n";
				$wpdb->print_error();
			}		
			
			$postvalues = array();
			
			
			//echo "<li>" . vtm_formatOutput($discipline['ITEMNAME']) . " has primary path " . vtm_formatOutput($pathname) . " ($pathID)</li>";
			$data = array(
				'CHARACTER_ID' => $vtmglobal['characterID'],
				'DISCIPLINE_ID' => $discID,
				'PATH_ID' => $pathID
			);
			$wpdb->insert(VTM_TABLE_PREFIX . "CHARACTER_PRIMARY_PATH",
				$data,
				array ('%d', '%d', '%d')
			);
			
		}

	}
	
	// Save Secondary Path levels
	
	foreach ($path_items as $item) {
		$pathname   = $item['ITEMNAME'];
		$pkey = sanitize_key($pathname);
		if (isset($postvalues[$pkey])) {
			$discipline = $item['GROUPING'];
			$dkey = sanitize_key($discipline);
			$value = $postvalues[$pkey];
			$pathID = $item['ITEMTABLE_ID'];
			
			if ($value > 0) {
				//echo "<li>Path " . vtm_formatOutput($pathname) . "($pathID) for " . vtm_formatOutput($discipline) . " has level $value</li>";	
				$data = array(
					'CHARACTER_ID'  => $vtmglobal['characterID'],
					'PATH_ID'       => $pathID,
					'LEVEL'         => $value
				);
				$wpdb->insert(VTM_TABLE_PREFIX . "CHARACTER_PATH",
					$data,
					array ('%d', '%d', '%d')
				);
			}
		}

	}		

	return $vtmglobal['characterID'];

}

function vtm_save_backgrounds() {
	global $wpdb;
	global $vtmglobal;

	$items  = vtm_get_chargen_itemlist('BACKGROUND');
	$saved  = vtm_get_chargen_saved('BACKGROUND');
	$posted = isset($_POST['background_value']) ? $_POST['background_value'] : array();
	$templatefree = vtm_get_free_levels('BACKGROUND');

	foreach ($items as $background) {
		$key     = sanitize_key($background['ITEMNAME']);
		$value   = isset($posted[$key]) ? $posted[$key] : 0;
		
		if ($value > 0) {
			if (isset($saved[$key]->comment) && $saved[$key]->comment != '')
				$comment = $saved[$key]->comment ;
			elseif (isset($templatefree[$key]->SPECIALISATION))
				$comment = $templatefree[$key]->SPECIALISATION;
			else
				$comment = '';
			
			$data = array(
				'CHARACTER_ID'  => $vtmglobal['characterID'],
				'BACKGROUND_ID' => $background['ITEMTABLE_ID'],
				'LEVEL'         => $value,
				'COMMENT'       => $comment
			);
			if (isset($saved[$key])) {
				if ($saved[$key]->level_from != $value) {
					//echo "<li>Updated $key at $value</li>\n";
					// update
					$wpdb->update(VTM_TABLE_PREFIX . "CHARACTER_BACKGROUND",
						$data,
						array (
							'ID' => $saved[$key]->chartableid
						)
					);
				} //else {
					//echo "<li>No need to update $key</li>\n";
				//
			} else {
				//echo "<li>Added $key at $value</li>\n";
				// insert
				$wpdb->insert(VTM_TABLE_PREFIX . "CHARACTER_BACKGROUND",
							$data,
							array ('%d', '%d', '%d', '%s')
						);
			}
		}
	}
		
	// Delete anything no longer needed
	foreach ($saved as $id => $value) {
	
		if (!isset($posted[$id]) || $posted[$id] == 0) {
			//echo "<li>Deleted $id</li>\n";
			// Delete
			$sql = "DELETE FROM " . VTM_TABLE_PREFIX . "CHARACTER_BACKGROUND
					WHERE ID = %s";
			$sql = $wpdb->prepare($sql,$saved[$id]->chartableid);
			//echo "<p>SQL: $sql</p>";
			$wpdb->get_results($sql);
		}
	}

	return $vtmglobal['characterID'];
	
}

function vtm_save_virtues() {
	global $wpdb;
	global $vtmglobal;
	
	$saved        = vtm_get_chargen_saved('STAT');
	$posted       = isset($_POST['virtue_value']) ? $_POST['virtue_value'] : array();
	$selectedpath = $_POST['path'];
	$virtues      = vtm_get_chargen_virtues($selectedpath);

	// Update CHARACTER with road/path ID
	$data = array (
		'ROAD_OR_PATH_ID'     => $selectedpath
	);
	$wpdb->update(VTM_TABLE_PREFIX . "CHARACTER",
		array ('ROAD_OR_PATH_ID' => $selectedpath),
		array ('ID' => $vtmglobal['characterID'])
	);
	
	// Update Willpower based on Courage
	$wpid   = $wpdb->get_var("SELECT ID FROM " . VTM_TABLE_PREFIX . "STAT WHERE NAME = 'Willpower'");
	$wpcsid = $wpdb->get_var($wpdb->prepare(
				"SELECT ID 
				FROM " . VTM_TABLE_PREFIX . "CHARACTER_STAT 
				WHERE CHARACTER_ID = %s and STAT_ID = %s", $vtmglobal['characterID'], $wpid));
	$data = array(
		'CHARACTER_ID' => $vtmglobal['characterID'],
		'STAT_ID'      => $wpid,
		'LEVEL'        => isset($posted['courage']) ? $posted['courage'] : 0
	);
	if (isset($wpcsid)) {
		// update
		$wpdb->update(VTM_TABLE_PREFIX . "CHARACTER_STAT",
			$data,
			array ('ID' => $wpcsid)
		);
	} 
	else {
		// insert
		$wpdb->insert(VTM_TABLE_PREFIX . "CHARACTER_STAT",
			$data,
			array ('%d', '%d', '%d')
		);
	}
	
	// Update CHARACTER_STAT with virtue ratings	
	foreach ($virtues as $attribute) {
		$key   = sanitize_key($attribute['ITEMNAME']);
		$value = isset($posted[$key]) ? $posted[$key] : 0;
		
		$data = array(
			'CHARACTER_ID' => $vtmglobal['characterID'],
			'STAT_ID'      => $attribute['ITEMTABLE_ID'],
			'LEVEL'        => $value
		);
		if (isset($saved[$key])) {
			//echo "<li>Updated $key at $value</li>\n";
			// update
			$wpdb->update(VTM_TABLE_PREFIX . "CHARACTER_STAT",
				$data,
				array (
					'ID' => $saved[$key]->chartableid
				)
			);
		} 
		else {
			//echo "<li>Added $key at $value</li>\n";
			// insert
			$wpdb->insert(VTM_TABLE_PREFIX . "CHARACTER_STAT",
				$data,
				array ('%d', '%d', '%d')
			);
		}
	}
	

	// Delete anything no longer needed
	foreach ($saved as $row) {
		$key = sanitize_key($row->name);
		if (!isset($posted[$key]) && $row->grp == 'Virtue') {
			// Delete
			$sql = "DELETE FROM " . VTM_TABLE_PREFIX . "CHARACTER_STAT
					WHERE CHARACTER_ID = %s AND STAT_ID = %s";
			$sql = $wpdb->prepare($sql,$vtmglobal['characterID'],$saved[$key]->itemid);
			//echo "<li>Delete $key ($sql)</li>\n";
			$wpdb->get_results($sql);
		}
	}

	return $vtmglobal['characterID'];
}

function vtm_save_basic_info() {
	global $wpdb;
	global $vtmglobal;
	
	// New Player?
	if (isset($_POST['newplayer']) && $_POST['newplayer'] == 'on') {
	
		$playertypeid   = $wpdb->get_var("SELECT ID FROM " . VTM_TABLE_PREFIX . "PLAYER_TYPE WHERE NAME = 'Player';");
		$playerstatusid = $wpdb->get_var("SELECT ID FROM " . VTM_TABLE_PREFIX . "PLAYER_STATUS WHERE NAME = 'Active';");
	
		$dataarray = array (
			'NAME' 				=> $_POST['player'],
			'PLAYER_TYPE_ID' 	=> $playertypeid,
			'PLAYER_STATUS_ID' 	=> $playerstatusid,
			'DELETED'      		=> 'N'
		);
		
		$wpdb->insert(VTM_TABLE_PREFIX . "PLAYER",
					$dataarray,
					array (
						'%s',
						'%s',
						'%s',
						'%s'
					)
				);
		
		$playerid = $wpdb->insert_id;
		if ($playerid == 0) {
			echo "<p style='color:red'><b>Error:</b> Player could not be added</p>\n";
			return 0;
		} 
	
	} else {
		$playerid = vtm_get_player_id(stripslashes($_POST['player']));
	}
	
	// Character Data
	
	$domain 		= $vtmglobal['config']->HOME_DOMAIN_ID;
	$chartype	= $wpdb->get_var("SELECT ID FROM " . VTM_TABLE_PREFIX . "CHARACTER_TYPE WHERE NAME = 'PC';");
	$charstatus	= $wpdb->get_var("SELECT ID FROM " . VTM_TABLE_PREFIX . "CHARACTER_STATUS WHERE NAME = 'Alive';");
	$genstatus	= $wpdb->get_var("SELECT ID FROM " . VTM_TABLE_PREFIX . "CHARGEN_STATUS WHERE NAME = 'In Progress';");
	$template	= $wpdb->get_var($wpdb->prepare("SELECT NAME FROM " . VTM_TABLE_PREFIX . "CHARGEN_TEMPLATE WHERE ID = %s;", $vtmglobal['templateID']));
	$priv_clan  = isset($_POST['priv_clan']) ? $_POST['priv_clan'] : 0;
	if (isset($_POST['pub_clan']) && $_POST['pub_clan'] > 0)
		$pub_clan = $_POST['pub_clan'];
	else
		$pub_clan = $priv_clan;
	
	// Set defaults for new characters or get current values
	if ($vtmglobal['characterID'] > 0) {
		$generationid = $wpdb->get_var($wpdb->prepare("SELECT GENERATION_ID FROM " . VTM_TABLE_PREFIX . "CHARACTER WHERE ID = %s", $vtmglobal['characterID']));
		$path = $wpdb->get_var($wpdb->prepare("SELECT ROAD_OR_PATH_ID FROM " . VTM_TABLE_PREFIX . "CHARACTER WHERE ID = %s", $vtmglobal['characterID']));
		$dob  = $wpdb->get_var($wpdb->prepare("SELECT DATE_OF_BIRTH FROM " . VTM_TABLE_PREFIX . "CHARACTER WHERE ID = %s", $vtmglobal['characterID']));
		$doe  = $wpdb->get_var($wpdb->prepare("SELECT DATE_OF_EMBRACE FROM " . VTM_TABLE_PREFIX . "CHARACTER WHERE ID = %s", $vtmglobal['characterID']));
		$sire = $wpdb->get_var($wpdb->prepare("SELECT SIRE FROM " . VTM_TABLE_PREFIX . "CHARACTER WHERE ID = %s", $vtmglobal['characterID']));
		$rating = $wpdb->get_var($wpdb->prepare("SELECT ROAD_OR_PATH_RATING FROM " . VTM_TABLE_PREFIX . "CHARACTER WHERE ID = %s", $vtmglobal['characterID']));
		$currentclanid = $wpdb->get_var($wpdb->prepare("SELECT PRIVATE_CLAN_ID FROM " . VTM_TABLE_PREFIX . "CHARACTER WHERE ID = %s", $vtmglobal['characterID']));
		$discspends = vtm_count($wpdb->get_var($wpdb->prepare("SELECT ID 
						FROM " . VTM_TABLE_PREFIX . "PENDING_FREEBIE_SPEND 
						WHERE CHARACTER_ID = %s AND (ITEMTABLE='DISCIPLINE' OR ITEMTABLE = 'PATH')", $vtmglobal['characterID'])));
		$discspends += vtm_count($wpdb->get_var($wpdb->prepare("SELECT ID 
						FROM " . VTM_TABLE_PREFIX . "PENDING_XP_SPEND 
						WHERE CHARACTER_ID = %s AND (ITEMTABLE='DISCIPLINE' OR ITEMTABLE = 'PATH')", $vtmglobal['characterID'])));
		$discspends += vtm_count($wpdb->get_var($wpdb->prepare("SELECT ID 
						FROM " . VTM_TABLE_PREFIX . "CHARACTER_DISCIPLINE 
						WHERE CHARACTER_ID = %s", $vtmglobal['characterID'])));
		$discspends += vtm_count($wpdb->get_var($wpdb->prepare("SELECT ID 
						FROM " . VTM_TABLE_PREFIX . "CHARACTER_PATH 
						WHERE CHARACTER_ID = %s", $vtmglobal['characterID'])));
	} else {
		$generationid = $vtmglobal['config']->DEFAULT_GENERATION_ID;
		$sql = $wpdb->prepare("SELECT ID FROM " . VTM_TABLE_PREFIX . "ROAD_OR_PATH WHERE ID = '%s';",get_option( 'vtm_chargen_humanity', '1' ));
		//echo "SQL: $sql";
		$path		  = $wpdb->get_var($sql);
		$dob = '';
		$doe = '';
		$sire = '';
		$rating = 0;
		$currentclanid = 0;
		$discspends = 0;
	}
	
	$dataarray = array (
		'NAME'						=> $_POST['character'],
		'PUBLIC_CLAN_ID'			=> $pub_clan,
		'PRIVATE_CLAN_ID'			=> $priv_clan,
		'GENERATION_ID'				=> $generationid,	// default from config, update later in backgrounds

		'DATE_OF_BIRTH'				=> $dob,				// Set later in ext backgrounds
		'DATE_OF_EMBRACE'			=> $doe,				// Set later in ext backgrounds
		'SIRE'						=> $sire,				// Set later in ext backgrounds
		'PLAYER_ID'					=> $playerid,

		'CHARACTER_TYPE_ID'			=> $chartype,		// player
		'CHARACTER_STATUS_ID'		=> $charstatus,		// active
		'CHARACTER_STATUS_COMMENT'	=> '',
		'ROAD_OR_PATH_ID'			=> $path,			// default from config

		'ROAD_OR_PATH_RATING'		=> $rating,				// Set later in virtues
		'DOMAIN_ID'					=> $domain,			// default from config, update later in ext backgrounds
		'WORDPRESS_ID'				=> isset($_POST['wordpress_id']) ? $_POST['wordpress_id'] : '',
		'SECT_ID'					=> $_POST['sect'],

		'NATURE_ID'					=> isset($_POST['nature']) ? $_POST['nature'] : 0,
		'DEMEANOUR_ID'				=> isset($_POST['demeanour']) ? $_POST['demeanour'] : 0,
		'CHARGEN_STATUS_ID'			=> $genstatus,		// in progress
		'CONCEPT'					=> $_POST['concept'],

		'EMAIL'						=> $_POST['email'],
		'LAST_UPDATED'				=> Date('Y-m-d'),	// Today
		'VISIBLE'					=> 'Y',
		'DELETED'					=> 'N'
	);
	//print_r($dataarray);
	
	// new character or update character?
	if ($vtmglobal['characterID'] > 0) {
		$result = $wpdb->update(VTM_TABLE_PREFIX . "CHARACTER",
					$dataarray,
					array (
						'ID' => $vtmglobal['characterID']
					)
				);
		
		if ($result) 
			echo "<p style='color:green'>Updated Character</p>\n";
		else if ($result !== 0) {
			$wpdb->print_error();
			echo "<p style='color:red'>Could not update character</p>\n";
		}

		// Fix if row in CHARACTER_GENERATION table is missing
		$sql = "SELECT COUNT(ID) FROM " . VTM_TABLE_PREFIX . "CHARACTER_GENERATION WHERE CHARACTER_ID = %s";
		$count = $wpdb->get_var($wpdb->prepare($sql, $vtmglobal['characterID']));
		if (!isset($count) || $count == 0) {
			echo "<p style='color:red'>Fixing missing CHARACTER_GENERATION row</p>\n";
			if (is_user_logged_in()) {
				$current_user = wp_get_current_user();
				$loggedin = $current_user->user_login;
			} else {
				$loggedin = '';
			}
			// Add character generation info
			$dataarray = array (
				'CHARACTER_ID'     => $vtmglobal['characterID'],
				'TEMPLATE_ID'      => $vtmglobal['templateID'],
				'NOTE_TO_ST'       => '',
				'NOTE_FROM_ST'	   => '',
				
				'WORDPRESS_ID'     => $loggedin,
				'DATE_OF_APPROVAL' => ''
			);
			$wpdb->insert(VTM_TABLE_PREFIX . "CHARACTER_GENERATION",
						$dataarray,
						array (
							'%d', 		'%d', 		'%s', 		'%s',
							'%s', 		'%s'
						)
					);
		
		}
		
	} 
	elseif ($pub_clan == 0 || $priv_clan == 0) {
		echo "<p style='color:red'><b>Error:</b>Character could not be added because no clans have been selected.</p>\n";
		return $vtmglobal['characterID'];
	}
	else {
		$wpdb->insert(VTM_TABLE_PREFIX . "CHARACTER",
					$dataarray,
					array (
						'%s', 		'%d', 		'%d', 		'%d',
						'%s', 		'%s', 		'%s', 		'%d', 
						'%d', 		'%d', 		'%s', 		'%d',
						'%d', 		'%d', 		'%s', 		'%d',
						'%d', 		'%d', 		'%d', 		'%s',
						'%s', 		'%s', 		'%s', 		'%s'
					)
				);
		$vtmglobal['characterID'] = $wpdb->insert_id;
		if ($vtmglobal['characterID'] == 0) {
			echo "<p style='color:red'><b>Error:</b> Character could not be added</p>\n";
		} else {
			if (is_user_logged_in()) {
				$current_user = wp_get_current_user();
				$loggedin = $current_user->user_login;
			} else {
				$loggedin = '';
			}
			// Add character generation info
			$dataarray = array (
				'CHARACTER_ID'     => $vtmglobal['characterID'],
				'TEMPLATE_ID'      => $vtmglobal['templateID'],
				'NOTE_TO_ST'       => '',
				'NOTE_FROM_ST'	   => '',
				
				'WORDPRESS_ID'     => $loggedin,
				'DATE_OF_APPROVAL' => ''
			);
			$wpdb->insert(VTM_TABLE_PREFIX . "CHARACTER_GENERATION",
						$dataarray,
						array (
							'%d', 		'%d', 		'%s', 		'%s',
							'%s', 		'%s'
						)
					);
			
			vtm_email_new_character($_POST['email'], $playerid, 
				$_POST['character'], $_POST['priv_clan'], $_POST['player'], $_POST['concept']);
		}
	}
	
	// Delete any spends on Disciplines and paths if the clan has changed
	if ($vtmglobal['characterID'] > 0 && $currentclanid != $_POST['priv_clan'] && $discspends > 0) {
		$sql = "DELETE FROM " . VTM_TABLE_PREFIX . "PENDING_FREEBIE_SPEND
				WHERE CHARACTER_ID = %s AND (ITEMTABLE = 'DISCIPLINE' OR ITEMTABLE = 'PATH')";
		$wpdb->get_results($wpdb->prepare($sql,$vtmglobal['characterID']));
		$sql = "DELETE FROM " . VTM_TABLE_PREFIX . "PENDING_XP_SPEND
				WHERE CHARACTER_ID = %s AND (ITEMTABLE = 'DISCIPLINE' OR ITEMTABLE = 'PATH')";
		$wpdb->get_results($wpdb->prepare($sql,$vtmglobal['characterID']));
		$sql = "DELETE FROM " . VTM_TABLE_PREFIX . "CHARACTER_DISCIPLINE
				WHERE CHARACTER_ID = %s";
		$wpdb->get_results($wpdb->prepare($sql,$vtmglobal['characterID']));
		$sql = "DELETE FROM " . VTM_TABLE_PREFIX . "CHARACTER_PATH
				WHERE CHARACTER_ID = %s";
		$wpdb->get_results($wpdb->prepare($sql,$vtmglobal['characterID']));
		
	}

	// Resend the character confirmation email if the button was pressed
	if (isset($_POST['chargen-resend-email'])) {
		vtm_email_new_character($_POST['email'], $playerid, 
				$_POST['character'], $_POST['priv_clan'], $_POST['player'], $_POST['concept']);
	}

	return $vtmglobal['characterID'];
} 

//------------------------------------------------------------
// FUNCTIONS 
//------------------------------------------------------------
function vtm_get_current_road() {
	global $wpdb;
	global $vtmglobal;

	$sql = "SELECT 
				'Path Rating'				as name, 
				stat1.LEVEL + stat2.LEVEL	as level_from,
				0	 						as chartableid, 
				'' 							as comment,
				road.ID 					as itemid, 
				road.name 	as grp
			FROM 
				" . VTM_TABLE_PREFIX . "CHARACTER cha
				,
				" . VTM_TABLE_PREFIX . "ROAD_OR_PATH road
				LEFT JOIN (
					SELECT STAT_ID, LEVEL
					FROM
						" . VTM_TABLE_PREFIX . "CHARACTER_STAT cstat
					WHERE
						CHARACTER_ID = %s
				) stat1
				ON 
					stat1.STAT_ID = road.STAT1_ID
				LEFT JOIN (
					SELECT STAT_ID, LEVEL
					FROM
						" . VTM_TABLE_PREFIX . "CHARACTER_STAT cstat
					WHERE
						CHARACTER_ID = %s
				) stat2
				ON 
					stat2.STAT_ID = road.STAT2_ID
			WHERE 
				cha.ROAD_OR_PATH_ID      = road.ID
				AND cha.ID = %s";
	$sql   = $wpdb->prepare($sql, $vtmglobal['characterID'], $vtmglobal['characterID'], $vtmglobal['characterID']);
	//echo "<p>SQL: $sql</p>\n";
	$items = vtm_sanitize_array($wpdb->get_results($sql, OBJECT_K));
	
	return $items;
}
function vtm_formatName($name, $description, $specialisation) {
	
	return "<span title='" . vtm_formatOutput($description . ($specialisation == '' ? '' : " ($specialisation)")) . "'>" . vtm_formatOutput($name) . "</span>";

}
function vtm_get_chargen_costs($type, $costtype) {
	global $wpdb;
	global $vtmglobal;

	$outdata = array();
	//	$cost['<statname>'] = array( '<from>' => array( '<to>' => <cost>))
	
	if ($type == 'DISCIPLINE') {
	
		// Get list of disciplines
		$sql = "SELECT ID, NAME, NOT(ISNULL(clandisc.DISCIPLINE_ID)) as ISCLAN					
				FROM 
					" . VTM_TABLE_PREFIX . "DISCIPLINE disc
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
						clandisc.DISCIPLINE_ID = disc.id";
		$sql = $wpdb->prepare($sql, $vtmglobal['characterID']);
		$items = $wpdb->get_results($sql, OBJECT_K);
		
		// Get clan and non-clan cost model IDs
		$sql = "SELECT CLAN_COST_MODEL_ID, NONCLAN_COST_MODEL_ID
				FROM
					" . VTM_TABLE_PREFIX . "CLAN clans,
					" . VTM_TABLE_PREFIX . "CHARACTER cha
				WHERE
					cha.PRIVATE_CLAN_ID = clans.ID
					AND cha.ID = %s";
		$costmodels = $wpdb->get_row($wpdb->prepare($sql, $vtmglobal['characterID']));

		// Get clan and non-clan costs
		$sql = "SELECT 
					steps.CURRENT_VALUE, steps.NEXT_VALUE, steps.$costtype
				FROM
					" . VTM_TABLE_PREFIX . "COST_MODEL_STEP steps,
					" . VTM_TABLE_PREFIX . "COST_MODEL models
				WHERE
					steps.COST_MODEL_ID = models.ID
					AND models.ID = %s
				ORDER BY
					steps.CURRENT_VALUE";
		$data    = $wpdb->get_results($wpdb->prepare($sql, $costmodels->CLAN_COST_MODEL_ID), ARRAY_A);
		$clancost = array();
		for ($i = 0 ; $i < 10 ; $i++) {
			$from = $data[$i]['CURRENT_VALUE'];
			$to   = $data[$i]['NEXT_VALUE'];
			$cost = 0;
			
			while ($from != $to && $to <= 10 && $to > 0) {
				if ($data[$from][$costtype] != 0) {
					$cost += $data[$from][$costtype];
					$clancost[$i][$to] = $cost;
				}
				$from = $to;
				$to   = $data[$from]['NEXT_VALUE'];
				
			}
		
		}
		$data = $wpdb->get_results($wpdb->prepare($sql, $costmodels->NONCLAN_COST_MODEL_ID), ARRAY_A);
		$nonclancost = array();
		for ($i = 0 ; $i < 10 ; $i++) {
			$from = $data[$i]['CURRENT_VALUE'];
			$to   = $data[$i]['NEXT_VALUE'];
			$cost = 0;
			
			while ($from != $to && $to <= 10 && $to > 0) {
				if ($data[$from][$costtype] != 0) {
					$cost += $data[$from][$costtype];
					$nonclancost[$i][$to] = $cost;
				}
				$from = $to;
				$to   = $data[$from]['NEXT_VALUE'];
				
			}
		}
		//print_r($data);
		//echo "<pre>\n";
		//print_r($clancost);
		//echo "</pre>\n";
		
		foreach ($items as $item) {
			$key = sanitize_key($item->NAME);
			if ($item->ISCLAN) {
				$outdata[$key] = $clancost;
			} else {
				$outdata[$key] = $nonclancost;
			}
		}
	} 
	elseif ($type == "MERIT") {
		
		$costcol = $costtype == 'FREEBIE_COST' ? 'COST' : $costtype;

		$sql = "SELECT ID, NAME, $costcol FROM " . VTM_TABLE_PREFIX . "MERIT ORDER BY ID";
		$items = $wpdb->get_results($sql);
		
		foreach ($items as $item) {
			$key = sanitize_key($item->NAME);
			$outdata[$key][0][1] = $item->$costcol;
		
		}

	}
	elseif ($type == "RITUAL") {
		$costcol ='COST';

		$sql = "SELECT ID, NAME, $costcol FROM " . VTM_TABLE_PREFIX . "RITUAL ORDER BY ID";
		$items = $wpdb->get_results($sql);
		
		foreach ($items as $item) {
			$key = sanitize_key($item->NAME);
			$outdata[$key][0][1] = $item->$costcol;
		}
	}
	else {
	
		$sql = "SELECT ID, NAME FROM " . VTM_TABLE_PREFIX . $type . " ORDER BY ID";
		$items = $wpdb->get_results($sql, OBJECT_K);
		
		foreach ($items as $item) {
			$key = sanitize_key($item->NAME);
		
			$sql = "SELECT 
						steps.CURRENT_VALUE, steps.NEXT_VALUE, steps.$costtype
					FROM
						" . VTM_TABLE_PREFIX . $type . " itemtable,
						" . VTM_TABLE_PREFIX . "COST_MODEL_STEP steps,
						" . VTM_TABLE_PREFIX . "COST_MODEL models
					WHERE
						itemtable.COST_MODEL_ID = models.ID
						AND steps.COST_MODEL_ID = models.ID
						AND itemtable.ID = %s
					ORDER BY
						itemtable.ID, steps.CURRENT_VALUE";
			$sql = $wpdb->prepare($sql, $item->ID);
			$data    = $wpdb->get_results($sql, ARRAY_A);
			
			if (count($data) > 0) {
				for ($i = 0 ; $i < 10 ; $i++) {
					$from = isset($data[$i]['CURRENT_VALUE']) ? $data[$i]['CURRENT_VALUE'] : 0;
					$to   = isset($data[$i]['NEXT_VALUE']) ? $data[$i]['NEXT_VALUE'] : 0;
					$cost = 0;
					
					while ($from != $to && $to <= 10 && $to > 0) {
						if ($data[$from][$costtype] != 0) {
							$cost += $data[$from][$costtype];
							$outdata[$key][$i][$to] = $cost;
						}
						$from = $to;
						$to   = $data[$from]['NEXT_VALUE'];
						
						//echo "<li>name:{$item->NAME}, key: $key, i: $i, from: $from, to: $to</li>\n";
					}
				
				}
			} else {
				echo "<li>ERROR: Issue with cost model for {$item->NAME}. Please ask the admin to check and resave the cost model</li>\n";
			}
		}
	
	}
	
	// if ($type == "MERIT") {
		// print_r($data);
		// echo "<p>($type / $vtmglobal['characterID']) SQL: $sql</p>\n";
		// echo "<pre>\n";
		// print_r($outdata);
		// echo "</pre>\n";
	// }

	return $outdata;
}

function vtm_get_freebies_spent() {
	global $wpdb;
	global $vtmglobal;

	$spent = 0;
	$gained = 0;
		
	if (isset($_POST['freebie_stat'])       || isset($_POST['freebie_skill']) ||
		isset($_POST['freebie_discipline']) || isset($_POST['freebie_background']) ||
		isset($_POST['freebie_merit'])      || isset($_POST['freebie_path'])) {
			
		$freebiecosts['STAT']       = vtm_get_chargen_costs('STAT', 'FREEBIE_COST');
		$freebiecosts['SKILL']      = vtm_get_chargen_costs('SKILL', 'FREEBIE_COST');
		$freebiecosts['DISCIPLINE'] = vtm_get_chargen_costs('DISCIPLINE', 'FREEBIE_COST');
		$freebiecosts['BACKGROUND'] = vtm_get_chargen_costs('BACKGROUND', 'FREEBIE_COST');
		$freebiecosts['MERIT']      = vtm_get_chargen_costs('MERIT', 'FREEBIE_COST');
		$freebiecosts['PATH']       = vtm_get_chargen_costs('PATH', 'FREEBIE_COST');
		$freebiecosts['STAT'] = array_merge($freebiecosts['STAT'], vtm_get_chargen_costs('ROAD_OR_PATH', 'FREEBIE_COST'));
		
		$current['STAT']       = vtm_get_chargen_saved('STAT');
		$current['SKILL']      = vtm_get_chargen_saved('SKILL');
		$current['DISCIPLINE'] = vtm_get_chargen_saved('DISCIPLINE');
		$current['BACKGROUND'] = vtm_get_chargen_saved('BACKGROUND');
		$current['MERIT']      = vtm_get_chargen_saved('MERIT');
		$current['PATH']       = vtm_get_chargen_saved('PATH');
		$current['STAT'] = array_merge($current['STAT'], vtm_get_current_road());
				
		$bought['STAT']       = isset($_POST['freebie_stat']) ? $_POST['freebie_stat'] : array();
		$bought['SKILL']      = isset($_POST['freebie_skill']) ? $_POST['freebie_skill'] : array();
		$bought['DISCIPLINE'] = isset($_POST['freebie_discipline']) ? $_POST['freebie_discipline'] : array();
		$bought['BACKGROUND'] = isset($_POST['freebie_background']) ? $_POST['freebie_background'] : array();
		$bought['MERIT']      = isset($_POST['freebie_merit']) ? $_POST['freebie_merit'] : array();
		$bought['PATH']       = isset($_POST['freebie_path']) ? $_POST['freebie_path'] : array();

		$templatefree['SKILL']      = vtm_get_free_levels('SKILL');
		
		foreach ($bought as $type => $items) {
			//print_r($current[$type]);
			foreach ($items as $key => $levelto) {
				if (isset($templatefree[$type][$key]->LEVEL))
					$freelevel = $templatefree[$type][$key]->LEVEL;
				else
					$freelevel = 0;
				$currlevel  = isset($current[$type][$key]->level_from)  ? $current[$type][$key]->level_from  : 0;
				$levelfrom  = max($currlevel, $freelevel);
				$costkey    = preg_replace("/_\d+$/", "", $key);
				
				if ($type == 'STAT' && $key == 'pathrating') {
					$costkey = sanitize_key($current[$type][$key]->grp);
				}
				if ($type == 'MERIT') {
					$levelfrom = 0;
					$levelto = 1;
				}
				
				// echo "<li>Key: $key, 	Cost key: $costkey,
				// Saved level: $currlevel, Free: $freelevel
				// Level to: $levelto</li>";

				$cost = isset($freebiecosts[$type][$costkey][$levelfrom][$levelto]) ? $freebiecosts[$type][$costkey][$levelfrom][$levelto] : 0;
				if ($cost >= 0)
					$spent += $cost;
				else
					$gained += -$cost;
				//echo "<li>Running total is $spent. Bought $key to $levelto ($cost)</li>\n";
				
			}
		
		}
		

	} else {
		$sql = "SELECT SUM(AMOUNT) FROM " . VTM_TABLE_PREFIX . "PENDING_FREEBIE_SPEND
				WHERE CHARACTER_ID = %s AND AMOUNT >= 0" ;
		$sql = $wpdb->prepare($sql, $vtmglobal['characterID']);
		$spent = $wpdb->get_var($sql) * 1;
		$sql = "SELECT SUM(AMOUNT) FROM " . VTM_TABLE_PREFIX . "PENDING_FREEBIE_SPEND
				WHERE CHARACTER_ID = %s AND AMOUNT < 0";
		$sql = $wpdb->prepare($sql, $vtmglobal['characterID']);
		$gained = $wpdb->get_var($sql) * -1;
		
	}

	return array("spent" => $spent, "gained" => $gained);
}
function vtm_get_chargen_virtues($selectedpath) {
	global $wpdb;
			
	$items    = vtm_get_chargen_itemlist('STAT', "Virtues");
	$statkey1 = vtm_get_virtue_statkey(1, $selectedpath);
	$statkey2 = vtm_get_virtue_statkey(2, $selectedpath);

	$results = array ();
	foreach ($items as $stat) {
		$name = sanitize_key($stat['ITEMNAME']);
		if ($name == $statkey1 || $name == $statkey2 || $name == 'courage') {
			$results[] = $stat;
		}
	}

	return $results;

}
function vtm_has_virtue_free_dot($selectedpath, $stat = '') {
	global $wpdb;
	global $vtmglobal;

	if ($vtmglobal['settings']['virtues-free-dots'] == 'yes')
		$freedot = 1;
	elseif ($vtmglobal['settings']['virtues-free-dots'] == 'no')
		$freedot = 0;
	elseif ($vtmglobal['settings']['virtues-free-dots'] == 'humanityvirtues') {
		if ($stat == 'courage') {
			$freedot = 1;
		}
		else {
			$humanityinfo = $wpdb->get_row($wpdb->prepare("SELECT ID, STAT1_ID, STAT2_ID FROM " . VTM_TABLE_PREFIX . "ROAD_OR_PATH WHERE ID = %s", get_option( 'vtm_chargen_humanity', '1' )));
			
			if ($stat == '') {
				$stat1 = sanitize_key($wpdb->get_var($wpdb->prepare("SELECT NAME FROM " . VTM_TABLE_PREFIX . "STAT WHERE ID = %s", $humanityinfo->STAT1_ID)));
				$stat2 = sanitize_key($wpdb->get_var($wpdb->prepare("SELECT NAME FROM " . VTM_TABLE_PREFIX . "STAT WHERE ID = %s", $humanityinfo->STAT2_ID)));
				$freedot = array(
					'courage' => 1,
					$stat1 => 1,
					$stat2 => 1
				);
			} else {
				$statinfo = $wpdb->get_results("SELECT NAME, ID FROM " . VTM_TABLE_PREFIX . "STAT", OBJECT_K);
				//print_r($statinfo);
				$statinfo = vtm_sanitize_array($statinfo);
				$statid = $statinfo[sanitize_key($stat)]->ID;
				if ($statid == $humanityinfo->STAT1_ID || $statid == $humanityinfo->STAT2_ID) {
					$freedot = 1;
				} else {
					$freedot = 0;
				}
				
			}
		}
		
	}
	else {
		$humanityid = $wpdb->get_var($wpdb->prepare("SELECT ID FROM " . VTM_TABLE_PREFIX . "ROAD_OR_PATH WHERE ID = %s", get_option( 'vtm_chargen_humanity', '1' )));
		if ($humanityid == $selectedpath)
			$freedot = 1;
		else
			$freedot = 0;
	}

	//echo "<li>setting: {$vtmglobal['settings']['virtues-free-dots']}, stat: $stat, pathid: $selectedpath, statid: $statid</li>";
	//print_r($freedot);
	return $freedot;
}

function vtm_get_chargen_roads() {
	global $wpdb;

	$sql = "SELECT ID, NAME
			FROM " . VTM_TABLE_PREFIX . "ROAD_OR_PATH
			WHERE VISIBLE = 'Y'
			ORDER BY NAME";

	$roadsOrPaths = $wpdb->get_results($sql);
	return $roadsOrPaths;
}
function vtm_get_virtue_statkey($statnum, $selectedpath) {
	global $wpdb;
	
	$statsql = "SELECT stat.NAME, stat.ID 
				FROM 
					" . VTM_TABLE_PREFIX . "ROAD_OR_PATH rop,
					" . VTM_TABLE_PREFIX . "STAT stat
				WHERE rop.ID = %s AND rop.STAT{$statnum}_ID = stat.ID";

	return sanitize_key($wpdb->get_var($wpdb->prepare($statsql, $selectedpath)));
	
}
function vtm_get_free_levels($table) {
	global $wpdb;
	global $vtmglobal;

	$duplicates = array();
	
	$sql = "SELECT item.NAME, item.ID, ctd.SPECIALISATION, ctd.LEVEL,
				ctd.ITEMTABLE, ctd.ITEMTABLE_ID,
				IFNULL(sector.ID,0) as SECTOR_ID, 
				IFNULL(sector.NAME,'') as SECTOR,
				ctd.MULTIPLE
			FROM 
				" . VTM_TABLE_PREFIX . "CHARGEN_TEMPLATE_DEFAULTS ctd
				LEFT JOIN (
					SELECT ID, NAME
					FROM " . VTM_TABLE_PREFIX . "SECTOR
				) sector
				ON sector.ID = ctd.SECTOR_ID,
				" . VTM_TABLE_PREFIX . $table . " item
			WHERE 
				ctd.TEMPLATE_ID = %s 
				AND ctd.ITEMTABLE_ID = item.ID
				AND ctd.ITEMTABLE = %s";
	$sql = $wpdb->prepare($sql, $vtmglobal['templateID'], $table);
	$results = $wpdb->get_results($sql);
	
	//print_r($results);
	
	$out = array();
	$indexes = array();
	foreach ($results as $row) {
		$key = sanitize_key($row->NAME);
		if ($row->MULTIPLE == 'Y') {
			if (isset($indexes[$key])) {
				$indexes[$key]++;
			} else {
				$indexes[$key] = 1;
			}
			$out[$key . "_" . $indexes[$key]] = $row;
		} else {
			$out[$key] = $row;
		}
		
	}
	
	return $out;
}
function vtm_render_dot_select($type, $itemid, $current, $pending, $free, $max, $submitted) {
	global $vtmglobal;

	$output = "";
	
	if ($pending || $submitted) {
		$output .= "<input type='hidden' name='" . $type . "[" . $itemid . "]' value='$current' />\n";
	}
	
	$output .= "<fieldset class='dotselect'>\n";
	
	// Ensure that anything with a free dot is selected initially at that level or 
	// it won't be saved to the database
	if ($free > 0 && $current == 0)
		$current = $free;
	
	for ($index = $max ; $index > 0 ; $index--) {
		$radioid = "dot_{$type}_{$itemid}_{$index}";
		//echo "<li>$radioid: current:$current / index:$index / free:$free (" . ($index - $free) . ")</li>\n";
		if ($pending || $submitted) {
			if ($index <= $free)
				$output .= "<img src='{$vtmglobal['dots']['dot1full']}' alt='*' id='$radioid' />";
			elseif ($index <= $current )
				$output .= "<img src='{$vtmglobal['dots']['dot2']}' alt='*' id='$radioid' />";
			elseif ($index <= $pending)
				$output .= "<img src='{$vtmglobal['dots']['dot3']}' alt='*' id='$radioid' />";
			else
				$output .= "<img src='{$vtmglobal['dots']['dot1empty']}' alt='O' id='$radioid' />";
		} else {
			$output .= "<input type='radio' id='$radioid' name='" . $type . "[" . $itemid . "]' value='$index' ";
			$output .= checked($current, $index, false);
			$output .= " /><label for='$radioid' title='$index'";
			
			if ($index <= $free)
				$output .= " class='freedot'";
			
			$output .= ">&nbsp;</label>";
		}
	}
	
	//if ($free == 0 && $pending == 0 && !$submitted) {
	if ($pending == 0 && !$submitted) {
		$radioid = "dot_{$type}_{$itemid}_clear";
		$output .= "<input type='radio' id='$radioid' name='" . $type . "[" . $itemid . "]' value='0' ";
		$output .= checked($current, 0, false);
		$output .= " /><label for='$radioid' title='Clear' class='cleardot'>&nbsp;</label>";
	} else {
		$output .= "<img src='{$vtmglobal['dots']['spacer']}' alt='' />";
	}
	
	$output .= "</fieldset>\n";
	
	
	return $output;

}

function vtm_render_pst_select($name, $info ) {

	$selected = isset($info['pst'][$name])     ? $info['pst'][$name]     : 0;
	$target   = isset($info['correct'][$name]) ? $info['correct'][$name] : 0;
	$actual   = isset($info['totals'][$name])  ? $info['totals'][$name]  : 0;

	$pst = "Primary/Secondary/Tertiary";
	switch ($selected) {
		case 1: 
			$pst = "Primary"; 
			break;
		case 2: 
			$pst = "Secondary"; 
			break;
		case 3: 
			$pst = "Tertiary"; 
			break;
		default:
			$selected = -1;
	}
	
	$spent = array();
	if ($selected > 0) {
		if ($actual > 0 && $actual != $target) {
			$pst .= " (spent $actual, target $target)";
		}
	} 
	elseif ($actual > 0) {
		$pst .= " (spent $actual)";
	}
	
	$output = "<strong>$pst</strong>\n";
	
	return $output;
}
function vtm_get_pst($saved, $posted, $items, $pdots, $sdots, $tdots, $freedot,
	$templatefree) {

	$grouptotals = array();
	$groupselected = array();
	$correct = array();
	
	// Get all the groups
	// "physical" => 1
	foreach ($items as $item) {
		$grouplist[sanitize_key($item['GROUPING'])] = 1;
	}
	//print_r($grouplist);
	
	// Work out how many dots have been spent in each group
	foreach ($items as $item) {
		$suffix = "";
		$i = 1;
		if ($item['MULTIPLE'] == 'Y') {
			$suffix = "_$i";
			$i++;
		}
		$key = sanitize_key($item['ITEMNAME']) . $suffix;
		
		while ((isset($posted[$key]) || isset($saved[$key]->level_from)) && $i != 0) {
			$grp = sanitize_key($item['GROUPING']);
			
			if (isset($posted[$key]))
				$level = $posted[$key];
			elseif (isset($saved[$key]->level_from))
				$level = $saved[$key]->level_from;
			else
				$level = 0;
				
			$freelevel = isset($templatefree[$key]->LEVEL) ? $templatefree[$key]->LEVEL : 0;
				
			//echo "<li>key: $key, grp: $grp, level: $level</li>";
			if ($level > 0  && isset($grouplist[$grp])) {
				if (isset($grouptotals[$grp]))
					$grouptotals[$grp] += max(0,$level - $freedot - $freelevel);
				elseif ($level > 0)
					$grouptotals[$grp] = max(0,$level - $freedot - $freelevel);
			}
			if ($item['MULTIPLE'] == 'Y') {
				$suffix = "_$i";
				$i++;
				$key = sanitize_key($item['ITEMNAME']) . $suffix;
			} else {
				$i = 0;
			}
		}
	}
	//print_r($grouptotals);
	
	// Work out which groups are Primary, Secondary or Tertiary
	$matches = array(null,0,0,0);
	foreach ($grouptotals as $grp => $total) {
		switch($total) {
			case $pdots: 
				$groupselected[$grp] = 1;
				$matches[1] = 1;
				$grouplist[$grp] = 0;
				break;
			case $sdots: 
				$groupselected[$grp] = 2;
				$matches[2] = 1;
				$grouplist[$grp] = 0;
				break;
			case $tdots: 
				$groupselected[$grp] = 3;
				$matches[3] = 1;
				$grouplist[$grp] = 0;
				break;
			default: $groupselected[$grp] = 0;
		}
	}

	// Work out the last group, if other 2 are found
	if (array_sum($matches) == 2) {
		for ($i = 1; $i <= 3 ; $i++) {
			if ($matches[$i] == 0) {
				foreach ($grouplist as $grp => $notfound) {
					if ($notfound)
						$groupselected[$grp] = $i;
				}
			}
		}
	}
	
	//print_r($groupselected);
	foreach ($groupselected as $grp => $pst) {
		if ($pst == 1) $correct[$grp] = $pdots;
		if ($pst == 2) $correct[$grp] = $sdots;
		if ($pst == 3) $correct[$grp] = $tdots;
	}
	
	$out['pst']     = $groupselected;
	$out['totals']  = $grouptotals;
	$out['correct'] = $correct;
	
	return $out;
}

function vtm_get_chargen_characterID() {
	global $wpdb;
	global $vtmglobal;
	
	// return -1: character reference is wrong
	// return 0: new character
	// return character ID

	// Returning to character generation via a reference?
	if (isset($_POST['chargen_reference']) && $_POST['chargen_reference'] != '') {
		$charref = $_POST['chargen_reference'];
		if (strpos($charref,'/') && strpos($charref,'/', strpos($charref,'/') + 1)) {
			$ref = explode('/',$charref);
			$id   = $ref[0] * 1;
			$pid  = $ref[1] * 1;
			$tid  = $ref[2] * 1;
			$wpid = $ref[3] * 1;
		
			// Check player ID is valid based on character ID
			$sql = "SELECT PLAYER_ID FROM " . VTM_TABLE_PREFIX . "CHARACTER WHERE ID = %s";
			$result = $wpdb->get_row($wpdb->prepare($sql, $id));
			if (!$result || $result->PLAYER_ID != $pid)
				$id = -1;
		
			// Check that wordpress ID is that of the user that created the character
			//		Or that the current user is an ST 
			//$mustbeloggedin = get_option('vtm_chargen_mustbeloggedin', '0');
			$correctlogin = vtm_get_chargenlogin($id);
			if (empty($correctlogin)) {
				$correctid = 0;
			} else {
				$bloguser = get_users('search=' . $correctlogin . '&number=1');
				$correctid = isset($bloguser[0]->ID) ? $bloguser[0]->ID : 0;
			}
			if (is_user_logged_in()) {
				$current_user = wp_get_current_user();
				$currentid = $current_user->ID;
			} else {
				$currentid = 0;
			}
			//echo "<li>CorrectLogin: $correctlogin, CorrectID: $correctid, current: $currentid, refid: $wpid</li>\n";
			
			if (!vtm_isST() && ($currentid != $wpid || $correctid != $currentid) )
				$id = -1; 
			
			// ensure character gen is in progress (and not approved)
			if ($id > 0) {
				$sql = "SELECT cgstat.NAME
						FROM
							" . VTM_TABLE_PREFIX . "CHARACTER cha,
							" . VTM_TABLE_PREFIX . "CHARGEN_STATUS cgstat
						WHERE
							cha.CHARGEN_STATUS_ID = cgstat.ID
							AND cha.ID = %s";
				$result = $wpdb->get_var($wpdb->prepare($sql, $id));
				
				if ($result == 'Approved')
					$id = -1;
			}
		} else {
			$id = -1;
		}
	}
	// Character generation in progress?
	elseif (isset($_POST['characterID']) && $_POST['characterID'] > 0) {
		$id = $_POST['characterID'];
	}
	// Is logged in user a Storyteller, getting to the page via a URL link?
	elseif (isset($_GET['characterID']) && $_GET['characterID'] > 0 && vtm_isST()) {
		$id = $_GET['characterID'];
	}
	// Logged in with an account that already has a character?
	elseif (isset($vtmglobal['characterID']) && $vtmglobal['characterID'] > 0) {
		//$id = $vtmglobal['characterID'];
		$id = 0;
	}
	// New character to be generated
	else {
		$id = 0;
	}

	//echo "<li>ID: $id ({$vtmglobal['characterID']})</li>\n";
	
	return $id;
}
function vtm_get_chargenlogin($id = false) {
	global $wpdb;
	global $vtmglobal;
	
	if ($id === false) {
		$characterID = $vtmglobal['characterID'];
	} else {
		$characterID = $id;
	}
	
	$sql = "SELECT WORDPRESS_ID 
		FROM 
			" . VTM_TABLE_PREFIX . "CHARACTER_GENERATION
		WHERE
			CHARACTER_ID = %s";
	$sql = $wpdb->prepare($sql, $characterID);
	return $wpdb->get_var($sql);

}
function vtm_get_templateid() {
	global $wpdb;
	global $vtmglobal;
	
	$sql = "SELECT TEMPLATE_ID FROM " . VTM_TABLE_PREFIX . "CHARACTER_GENERATION
		WHERE CHARACTER_ID = %s";
	$sql = $wpdb->prepare($sql, $vtmglobal['characterID']);
		
	if (isset($_POST['chargen_template'])) {
		if (isset($_POST['chargen_reference']) && $_POST['chargen_reference'] != "") {
			// look up what template the character was generated with
			$template = $wpdb->get_var($sql);
			//echo "Looked up template ID from character ({$vtmglobal['characterID']}) : $template<br />\n";
		} else {
			$template = $_POST['chargen_template'];
			//echo "Looked up template ID from Step 0 : $template<br />\n";
		}
	} 
	elseif (isset($_POST['selected_template']) && $_POST['selected_template'] != "") {
		$template = $_POST['selected_template'] ;
		//echo "Looked up template ID from last step : $template<br />\n";
	}
	else {
		$template = $wpdb->get_var($sql);
		//echo "Looked up template ID from character '{$vtmglobal['characterID']}': $template<br />\n";
	}
	
	return $template;
}
function vtm_get_chargen_settings() {
	global $wpdb;
	global $vtmglobal;
	
	$sql = "SELECT NAME, VALUE FROM " . VTM_TABLE_PREFIX . "CHARGEN_TEMPLATE_OPTIONS WHERE TEMPLATE_ID = %s";
	$sql = $wpdb->prepare($sql, $vtmglobal['templateID']);
	//echo "<p>SQL: $sql</p>\n";
	$result = $wpdb->get_results($sql);
	$settings = vtm_default_chargen_settings();
	
	if (count($result) == 0)
		return $settings;
	
	$keys = $wpdb->get_col($sql);
	$vals = $wpdb->get_col($sql,1);

	$settings = array_merge($settings, array_combine($keys, $vals));
	
	return $settings;
}
function vtm_get_chargen_reference() {
	global $vtmglobal;

	if (isset($vtmglobal['characterID'])) {
		$characterID = $vtmglobal['characterID'];
	}
	elseif (isset($_REQUEST['characterID']) && $_REQUEST['characterID'] > 0) {
		$characterID = $_REQUEST['characterID'];
	}
	elseif (isset($_REQUEST['chargen_reference'])) {
		$split = explode("/",$_POST['chargen_reference']);
		$characterID = $split[0];
	}
	$vtmglobal['characterID'] = $characterID;
	$vtmglobal['templateID']  = vtm_get_templateid();
	$vtmglobal['playerID']    = vtm_get_player_id_from_characterID();
	$vtmglobal['genInfo']     = vtm_calculate_generation();

	$cid = sprintf("%04d", $vtmglobal['characterID']);
	$pid = sprintf("%04d", $vtmglobal['playerID']);
	$tid = sprintf("%02d", $vtmglobal['templateID']);
	
	$login = vtm_get_chargenlogin();
	if (isset($login)) {
		//echo "<li>$login</li>\n";
		$bloguser = get_users('search=' . $login . '&number=1');
		//print_r($bloguser);
		$wpid = isset($bloguser[0]->ID) ? sprintf("%04d", $bloguser[0]->ID) : '0000';
	} else {
		$wpid = '0000';
	}
	
	$ref = "$cid/$pid/$tid/$wpid";
	//echo "<li>Reference: $ref</li>\n";
	return $ref;

}
function vtm_get_player_id_from_characterID() {
	global $wpdb;
	global $vtmglobal;
		
	if (isset($_REQUEST['page']) && isset($_REQUEST['character']) && $_REQUEST['page'] == 'vtmcharacter-chargen')
		$characterID = $_REQUEST['character'];
	else
		$characterID = $vtmglobal['characterID'];
	
	$sql = "SELECT players.ID 
		FROM 
			" . VTM_TABLE_PREFIX . "PLAYER players,
			" . VTM_TABLE_PREFIX . "CHARACTER charac
		WHERE
			players.ID = charac.PLAYER_ID
			AND charac.ID = %s";
	$sql = $wpdb->prepare($sql, $characterID);
	return $wpdb->get_var($sql);

}
function vtm_calculate_generation() {
	global $wpdb;
	global $vtmglobal;
			
	$defaultgen = $wpdb->get_var($wpdb->prepare("SELECT NAME FROM " . VTM_TABLE_PREFIX . "GENERATION WHERE ID = %s", $vtmglobal['config']->DEFAULT_GENERATION_ID));
	$sql = "SELECT charbg.LEVEL 
			FROM 
				" . VTM_TABLE_PREFIX . "CHARACTER_BACKGROUND charbg,
				" . VTM_TABLE_PREFIX . "BACKGROUND bg
			WHERE
				charbg.CHARACTER_ID = %s
				AND charbg.BACKGROUND_ID = bg.ID
				AND bg.NAME = 'Generation'";
	$genfromgb  = $wpdb->get_var($wpdb->prepare($sql, $vtmglobal['characterID']));
	$sql = "SELECT LEVEL_TO 
			FROM " . VTM_TABLE_PREFIX . "PENDING_FREEBIE_SPEND
			WHERE 
				ITEMTABLE = 'BACKGROUND'
				AND ITEMNAME = 'generation'
				AND CHARACTER_ID = %s";
	$genfromfreebie = $wpdb->get_var($wpdb->prepare($sql, $vtmglobal['characterID']));
	$generation     = $defaultgen - (isset($genfromfreebie) ? $genfromfreebie : $genfromgb);

	$results   = $wpdb->get_row($wpdb->prepare("SELECT ID, MAX_RATING, MAX_DISCIPLINE FROM " . VTM_TABLE_PREFIX . "GENERATION WHERE NAME = %s", $generation));

	$data = array(
		'ID' => $results->ID,
		'Gen' => $generation,
		'MaxDot' => $results->MAX_RATING,
		'MaxDisc' => $results->MAX_DISCIPLINE
	);
	
	//print "<li>Dot limit for {$generation}th generation is {$results->MAX_RATING}/{$results->MAX_DISCIPLINE}</li>";
	
	return $data;
	
}

function vtm_sanitize_array($array) {

	if (count($array) == 0) {
		return array();
	} else {
		$keys = array_keys($array);
		$values = array_values($array);
		
		return array_combine(array_map("vtm_sanitize_keys",$keys), $values);
	} 
}

function vtm_sanitize_keys($a) {
	return sanitize_key($a);
}

//------------------------------------
// RENDER SUB-SECTIONS
//------------------------------------
function vtm_render_chargen_section($saved, $isPST, $pdots, $sdots, $tdots, $freedot,
	$items, $posted, $pendingfb, $pendingxp, $title, $postvariable, $submitted,
	$maxdots, $templatefree, $primarypath) {
		
	global $vtmglobal;

	$output = "";
	$class = $postvariable == 'ritual_value' ? "ritrowselect mfdotselect" : "";
	
	// Make a guess from saved levels which is Primary/Secondary/Tertiary
	$info['pst']     = array();
	$info['totals']  = array();
	$info['correct'] = array();
	if (count($saved) > 0 || count($posted) > 0) {
		if ($isPST) {
			$info = vtm_get_pst($saved, $posted, $items, $pdots, $sdots, $tdots,
				$freedot, $templatefree);
			//print_r($info);
		}
	} 

	// Go through each item
	$group = "";
	foreach ($items as $item) {
		// How many dots to display
		if (is_array($maxdots)) {
			if (isset($maxdots[$item['ITEMTABLE_ID']])) {
				$maxdot = $maxdots[$item['ITEMTABLE_ID']]->LEVEL;
			} else {
				$maxdot = $maxdots['default'];
			}
		} else {
			$maxdot = $maxdots;
		}
		
		$loop = $item['MULTIPLE'] == 'Y' ? 4 : 1;
		
		for ($j = 1 ; $j <= $loop ; $j++) {
			$name = sanitize_key($item['ITEMNAME']);
			if ($item['MULTIPLE'] == 'Y') {
				$key = $name . "_" . $j;
				if (isset($templatefree[$key])) {
					$loop++;
				}
			} else {
				$key = $name;
			}
	
			// Heading and Primary/Secondary/Tertiary pull-down
			if (sanitize_key($item['GROUPING']) != $group) {
				if ($group != "")
					$output .= "</table>\n";
				$group = sanitize_key($item['GROUPING']);
				$output .= "<h4>{$item['GROUPING']}</h4><p>\n";
				if ($isPST) {
					$output .= vtm_render_pst_select($group, $info);
				}
				
				$output .= "</p><input type='hidden' name='group[]' value='$group' />";
				if ($postvariable == 'ritual_value')
					$output .= "<table><tr><th>$title</th><th>Description</th></tr>\n";
				else
					$output .= "<table><tr><th class='vtmcol_key'>$title</th><th class='vtmcol_dots'>Rating</th><th>Description</th></tr>\n";
			}
			
			// Display Data
			$level = isset($posted[$key]) ? $posted[$key] : (isset($saved[$key]->level_from) ? $saved[$key]->level_from : 0);  // currently selected or saved level
			if (isset($templatefree[$key]))
				$tpfree = $templatefree[$key]->LEVEL;
			elseif (is_array($freedot)) {
				if (isset($freedot[$key]))
					$tpfree = 1;
				else
					$tpfree = 0;
			} else
				$tpfree = $freedot;
			
			$itemname = $item['ITEMNAME'];
			if ($postvariable == 'ritual_value') {
				$id = "id$key";
				$output .= "<tr><td class='$class'>";
				if (isset($pendingxp[$key])) {
					$output .= "<img src='{$vtmglobal['dots']['dot3']}' alt='*' />";
				}
				elseif ($submitted) {
					if ($item['LEVEL'] == $level) {
						$output .= "<img src='{$vtmglobal['dots']['dot1full']}' alt='*' />";
					} else {
						$output .= "<img src='{$vtmglobal['dots']['dot1empty']}' alt='O' />";
					}
				}
				else
					$output .= "<input id='$id' name='ritual_value[$key]' type='checkbox' " . checked( $item['LEVEL'], $level, false) . " value='{$item['LEVEL']}'>";
				$output .= "<div><label " . ($submitted ? '' : "for='$id'") . ">Level {$item['LEVEL']} - " . vtm_formatOutput($itemname) . "</label></div>";
				//$output .= "</td>\n";
			} else {
				if (isset($saved[$key]->comment) && $saved[$key]->comment != "") {
					$itemname .= " (" . $saved[$key]->comment . ")";
				}
				elseif (isset($templatefree[$key]->SPECIALISATION) && $templatefree[$key]->SPECIALISATION != "") {
					$itemname .= " (" . $templatefree[$key]->SPECIALISATION . ")";
				}
				
				$output .= "<tr><td class='$class vtmcol_key'>" . vtm_formatOutput($itemname) . "</td>";
				$output .= "<td class='$class vtmcol_dots vtmdot_" . (max($maxdot,(isset($maxdots['default']) ? $maxdots['default'] : $maxdots)) > 5 ? 10 : 5) . "'>";
				
				$pending = isset($pendingfb[$key]->value) ? $pendingfb[$key]->value : 0 ;         // level bought with freebies
				$pending = isset($pendingxp[$key]->value) ? $pendingxp[$key]->value : $pending ;  // level bought with xp
				
				/*if ($postvariable == 'virtue_value' && $key == 'courage' 
					&& (isset($pendingfb['willpower']) || isset($pendingxp['willpower'])))
					$output .= vtm_render_dot_select($postvariable, $key, $level, $pending, $tpfree, $maxdot, 1);
				else */
					$output .= vtm_render_dot_select($postvariable, $key, $level, $pending, $tpfree, $maxdot, $submitted);
			}
			$output .= "</td><td class='$class'>\n";
			if ($postvariable == 'discipline_value' && isset($primarypath[$item['ITEMTABLE_ID']])) {
				$output .= "<input type='hidden' name='primarypathspid[$key]' value='{$primarypath[$item['ITEMTABLE_ID']]->pathid}' />";
				$output .= "<input type='hidden' name='primarypathstid[$key]' value='{$primarypath[$item['ITEMTABLE_ID']]->chppid}' />";
				$output .= "<input type='hidden' name='primarypathslvl[$key]' value='{$primarypath[$item['ITEMTABLE_ID']]->path_level}' />";
				$item['DESCRIPTION'] .= " ({$primarypath[$item['ITEMTABLE_ID']]->name})";
			}
			$output .= vtm_formatOutput($item['DESCRIPTION']);
			$output .= "</td></tr>\n";
		}
	}
	$output .= "</table>\n";

	return $output;
}

function vtm_get_chargen_paths($bydisc = 1) {
	global $wpdb;
	global $vtmglobal;
	
	//$defaults = vtm_get_chargen_path_defaults();
	$templateid = vtm_get_templateid();
	
	$discid = "disc.ID as discid";
	$pathid = "IFNULL(ch.PATH_ID,template.PATH_ID) as pathid";
	
	if ($bydisc) {
		$col_order = "$discid, $pathid";
	} else {
		$col_order = "$pathid, $discid";
	}

	// Save to the CHARACTER_PRIMARY_PATH table every time
	// we add a discipline to the character or buy one with freebies or xp 
	
	$sql = "SELECT
			$col_order,
			disc.NAME as discipline,
			IFNULL(ch.NAME,template.NAME) as name,
			ch.ID as chppid,
			GREATEST(
				IFNULL(chdisc.LEVEL,0), 
				IFNULL(chfb.LEVEL_TO,0), 
				IFNULL(chxp.CHARTABLE_LEVEL,0)
			) as discipline_level,
			ch.LEVEL as path_level
		FROM
			" . VTM_TABLE_PREFIX . "DISCIPLINE disc
			LEFT JOIN (
				SELECT 
					disc2.ID as DISCIPLINE_ID,
					pth2.ID as PATH_ID,
					pth2.NAME
				FROM
					" . VTM_TABLE_PREFIX . "CHARGEN_PRIMARY_PATH cpp,
					" . VTM_TABLE_PREFIX . "DISCIPLINE disc2,
					" . VTM_TABLE_PREFIX . "PATH pth2
				WHERE
					disc2.ID = pth2.DISCIPLINE_ID
					AND cpp.TEMPLATE_ID = '%s'
					AND cpp.PATH_ID = pth2.ID
					AND cpp.DISCIPLINE_ID = disc2.ID
			) template
			ON
				template.DISCIPLINE_ID = disc.ID
			LEFT JOIN (
				SELECT 
					chpp.ID as ID,
					disc3.ID as DISCIPLINE_ID,
					pth3.ID as PATH_ID,
					pth3.NAME as NAME,
					cp.LEVEL
				FROM
					" . VTM_TABLE_PREFIX . "CHARACTER_PRIMARY_PATH chpp
					LEFT JOIN (
						SELECT PATH_ID, LEVEL
						FROM " . VTM_TABLE_PREFIX . "CHARACTER_PATH
						WHERE CHARACTER_ID = '%s'
					) cp
					ON
					cp.PATH_ID = chpp.PATH_ID,
					" . VTM_TABLE_PREFIX . "DISCIPLINE disc3,
					" . VTM_TABLE_PREFIX . "PATH pth3
				WHERE
					disc3.ID = pth3.DISCIPLINE_ID
					AND chpp.CHARACTER_ID = '%s'
					AND chpp.PATH_ID  = pth3.ID
					AND pth3.VISIBLE = 'Y'
			) ch
			ON
				ch.DISCIPLINE_ID = disc.ID
			LEFT JOIN (
				SELECT 
					DISCIPLINE_ID,
					LEVEL
				FROM
					" . VTM_TABLE_PREFIX . "CHARACTER_DISCIPLINE
				WHERE
					CHARACTER_ID = '%s'
			) chdisc
			ON
				chdisc.DISCIPLINE_ID = disc.ID
			LEFT JOIN (
				SELECT 
					ITEMTABLE_ID,
					LEVEL_TO
				FROM
					" . VTM_TABLE_PREFIX . "PENDING_FREEBIE_SPEND
				WHERE
					CHARACTER_ID = '%s'
					AND ITEMTABLE = 'DISCIPLINE'
			) chfb
			ON
				chfb.ITEMTABLE_ID = disc.ID
			LEFT JOIN (
				SELECT 
					ITEMTABLE_ID,
					CHARTABLE_LEVEL
				FROM
					" . VTM_TABLE_PREFIX . "PENDING_XP_SPEND
				WHERE
					CHARACTER_ID = '%s'
					AND ITEMTABLE = 'DISCIPLINE'
			) chxp
			ON
				chxp.ITEMTABLE_ID = disc.ID
		WHERE
			(
				chdisc.LEVEL IS NOT NULL
				OR chfb.LEVEL_TO IS NOT NULL
				OR chxp.CHARTABLE_LEVEL IS NOT NULL
			)
			AND (
				template.PATH_ID IS NOT NULL
				OR ch.PATH_ID IS NOT NULL
			)
		";
	
	$sql = $wpdb->prepare($sql, $templateid, $vtmglobal['characterID'], 
		$vtmglobal['characterID'], $vtmglobal['characterID'], $vtmglobal['characterID'], 
		$vtmglobal['characterID']);
	$results = vtm_sanitize_array($wpdb->get_results($sql, OBJECT_K));
	
	//echo "<p>SQL: $sql</p>";
	//print_r($results);
	
	return $results;
}

function vtm_get_chargen_path_defaults() {
	global $wpdb;
	global $vtmglobal;
	
	$templateid = vtm_get_templateid();

	$sql = "SELECT 
			disc.ID as discid, 
			pth.ID as pathid,
			pth.NAME as name,
			chpp.ID as charppid
		FROM
			" . VTM_TABLE_PREFIX . "CHARGEN_PRIMARY_PATH cpp,
			" . VTM_TABLE_PREFIX . "DISCIPLINE disc,
			" . VTM_TABLE_PREFIX . "PATH pth
			LEFT JOIN (
				SELECT ID, PATH_ID
				FROM " . VTM_TABLE_PREFIX . "CHARACTER_PRIMARY_PATH
				WHERE CHARACTER_ID = '%s'
			) chpp
			ON
				chpp.PATH_ID = pth.ID
		WHERE
			cpp.DISCIPLINE_ID = disc.ID
			AND cpp.PATH_ID = pth.ID
			AND cpp.TEMPLATE_ID = '%d'
		";
	
	$sql = $wpdb->prepare($sql, $vtmglobal['characterID'], $templateid);
	$results = $wpdb->get_results($sql, OBJECT_K);
	
	// If results are zero, the primary paths might not be set in the template

	//echo "<p>SQL: $sql</p>";
	//print_r($results);
	
	return $results;
}

//-------------------------------------------
function vtm_get_chargen_itemlist($table, $args = "") {
	global $wpdb;
	global $vtmglobal;
	
	$wpdbprepare = 0;
	$arguments = array($vtmglobal['characterID']);
	
	// Define if/how this data table returns MULTIPLE, GROUPING, SPECIALISATION_AT, etc
	$multiple = "t.MULTIPLE";
	$grouping = "t.GROUPING";
	$specat   = "t.SPECIALISATION_AT";
	$name     = "t.NAME";
	$level    = "0";
	switch($table) {
		case 'STAT':
			$multiple = "'N'";
			break;
		case 'SKILL':
			if ($args == "subgroup" || $args == "nosec")
				$grouping = "type.NAME";
			else
				$grouping = "IFNULL(parent.NAME, type.NAME)";
			break;	
		case 'DISCIPLINE':
			$multiple = "'N'";
			$specat   = "0";
			$grouping = "IF(ISNULL(clandisc.DISCIPLINE_ID),'Non-Clan Discipline','Clan Discipline')";
			break;	
		case 'BACKGROUND':
			$multiple = "'N'";
			$specat   = "t.HAS_SPECIALISATION";
			break;
		case 'ROAD_OR_PATH':
			$multiple = "'N'";
			$specat   = "0";
			$grouping = "t.NAME";
			$name     = "'Path Rating'";
			break;
		case 'PATH':
			$multiple = "'N'";
			$specat   = "0";
			$grouping = "disc.NAME";
			break;
		case 'MERIT':
			$specat   = "t.HAS_SPECIALISATION";
			$level    = "t.VALUE";
			break;
	}
	
	// Define tables to query
	$tables = VTM_TABLE_PREFIX . $table . " as t";
	switch($table) {
		case 'SKILL':
			$tables .= ", ". VTM_TABLE_PREFIX . "SKILL_TYPE type
				LEFT JOIN ". VTM_TABLE_PREFIX . "SKILL_TYPE parent
				ON type.PARENT_ID = parent.ID";
			break;	
		case 'DISCIPLINE':
			$wpdbprepare = 1;
			$tables .= "
				LEFT JOIN (
					SELECT DISCIPLINE_ID, CLAN_ID
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
					clandisc.DISCIPLINE_ID = t.id";
			break;	
		case 'ROAD_OR_PATH':
			$wpdbprepare = 1;
			$tables .= ", ". VTM_TABLE_PREFIX . "CHARACTER cha";
			break;	
		case 'PATH':
			$wpdbprepare = 1;
			$tables .= ",
				" . VTM_TABLE_PREFIX . "DISCIPLINE disc
				LEFT JOIN (
					SELECT ID, LEVEL, DISCIPLINE_ID
					FROM
						" . VTM_TABLE_PREFIX . "CHARACTER_DISCIPLINE
					WHERE
						CHARACTER_ID = %s
				) as cp
				ON
					cp.DISCIPLINE_ID = disc.ID
				LEFT JOIN (
					SELECT ID, LEVEL_TO, ITEMTABLE_ID
					FROM
						" . VTM_TABLE_PREFIX . "PENDING_FREEBIE_SPEND
					WHERE
						CHARACTER_ID = %s
						AND ITEMTABLE = 'DISCIPLINE'
				) as fp
				ON
					fp.ITEMTABLE_ID = disc.ID
				LEFT JOIN (
					SELECT ID, CHARTABLE_LEVEL, ITEMTABLE_ID
					FROM
						" . VTM_TABLE_PREFIX . "PENDING_XP_SPEND
					WHERE
						CHARACTER_ID = %s
						AND ITEMTABLE = 'DISCIPLINE'
				) as xp
				ON
					xp.ITEMTABLE_ID = disc.ID";
			array_push($arguments, $vtmglobal['characterID'], $vtmglobal['characterID']);
			break;	
	}
	
	
	// Define any filtering
	$filter = "t.VISIBLE = 'Y'";
	switch($table) {
		case 'STAT':
			if ($args == 'Virtues') {
				$filter = "GROUPING = 'Virtue'";
			}
			else {
				if ($args == 'all') {
					$filter = "1";
				} 
				else {
					$filter = "GROUPING = 'Physical' OR GROUPING = 'Social' OR GROUPING = 'Mental'";
				}
				$sql = "SELECT clans.NAME
						FROM
							" . VTM_TABLE_PREFIX . "CHARACTER chara,
							" . VTM_TABLE_PREFIX . "CLAN clans
						WHERE
							chara.PRIVATE_CLAN_ID = clans.ID
							AND chara.ID = %s";
				$clan   = $wpdb->get_var($wpdb->prepare($sql, $vtmglobal['characterID']));
				if (isset($clan) && ($clan == 'Nosferatu' || $clan == 'Samedi'))
					$filter = "NAME != 'Appearance' AND ($filter)";
			}
			break;
		case 'SKILL':
			$filter .= " AND t.SKILL_TYPE_ID = type.ID";
			if ($args == 'nosec') {
				$filter .= " AND type.PARENT_ID = 0";
			}
			break;
		case 'DISCIPLINE':
			$filter .= " OR NOT(ISNULL(clandisc.DISCIPLINE_ID))";
			break;
		case 'ROAD_OR_PATH':
			$filter = "cha.ID = %s AND cha.ROAD_OR_PATH_ID = t.ID";
			break;
		case 'PATH':
			$filter .= "AND t.DISCIPLINE_ID = disc.ID AND (
				NOT(ISNULL(cp.LEVEL)) OR NOT(ISNULL(fp.LEVEL_TO)) 
				OR NOT(ISNULL(xp.CHARTABLE_LEVEL))
				)";
			break;
	}
	
	// Define ordering
	$ordering = "t.ORDERING";
	switch($table) {
		case 'SKILL':
			if ($args == "subgroup")
				$ordering = "type.ORDERING, t.NAME";
			else
				$ordering = "parent.ORDERING, type.ORDERING, t.NAME";
			break;	
		case 'DISCIPLINE':
			$ordering = "GROUPING, t.NAME";
			break;	
		case 'BACKGROUND':
			$ordering = "t.GROUPING, t.NAME";
			break;	
		case 'ROAD_OR_PATH':
			$ordering = "t.NAME";
			break;	
		case 'PATH':
			$ordering = "GROUPING, t.NAME";
			break;	
		case 'MERIT':
			$ordering = "t.GROUPING, t.VALUE DESC, t.NAME";
			break;	
	}
	
	// Main DB query
	$sql = "SELECT t.ID, $name as NAME, t.DESCRIPTION, $grouping as GROUPING, 
				$specat as SPECIALISATION_AT, $multiple as MULTIPLE, $level as LEVEL
			FROM $tables
			WHERE
				$filter
			ORDER BY $ordering";
	if ($wpdbprepare)
		$sql = $wpdb->prepare($sql, $arguments);
	
	//if ($table == 'PATH')
	//	echo "<p>$table itemlist SQL ($args): $sql</p>\n";
		
	$results = $wpdb->get_results($sql);
	
	// Prepare return array
	$out = array();
	$uniq = array();
	foreach($results as $row) {

		$data = array(
			'CHARTABLE'         => 'CHARACTER_' . $table,
			'ITEMTABLE'         => $table,
			'ITEMTABLE_ID'      => $row->ID,
			'SPECIALISATION_AT' => $row->SPECIALISATION_AT,
			'GROUPING'          => $row->GROUPING,
			'ITEMNAME'          => $row->NAME,
			'DESCRIPTION'       => $row->DESCRIPTION,
			'LEVEL'             => $row->LEVEL
		);
		$data['MULTIPLE'] = isset($row->MULTIPLE) ? $row->MULTIPLE : 'N';
		
		$out[] = $data;
	}
	
	//print_r($out);
	return $out;
}
function vtm_get_pending_freebies($table) {
	global $wpdb;
	global $vtmglobal;

	$sql = "SELECT freebie.ITEMNAME as name, freebie.LEVEL_TO as value,
			freebie.SPECIALISATION as specialisation, freebie.ID as id,
			freebie.PENDING_DETAIL as pending_detail, freebie.ITEMTABLE_ID as itemid
		FROM
			" . VTM_TABLE_PREFIX . "PENDING_FREEBIE_SPEND freebie
		WHERE
			freebie.CHARACTER_ID = %s
			AND freebie.ITEMTABLE = '$table'";
	$sql = $wpdb->prepare($sql, $vtmglobal['characterID']);
	//echo "SQL: $sql</p>\n";
	
	$pending = $wpdb->get_results($sql, OBJECT_K);
	$pending = vtm_sanitize_array($pending);

	return $pending;
}
function vtm_get_pending_chargen_xp($table) {
	global $wpdb;
	global $vtmglobal;
	
	$sql = "SELECT ITEMNAME as name, CHARTABLE_LEVEL as value, 
			SPECIALISATION as specialisation,
			ITEMTABLE_ID as itemid, ID as id
		FROM
			" . VTM_TABLE_PREFIX . "PENDING_XP_SPEND
		WHERE
			CHARACTER_ID = %s
			AND ITEMTABLE = '$table'";
	$sql = $wpdb->prepare($sql, $vtmglobal['characterID']);
	//echo "SQL: $sql</p>\n";
	
	$pending = $wpdb->get_results($sql, OBJECT_K);
	$pending = vtm_sanitize_array($pending);
	
	return $pending;
}
function vtm_get_chargen_primarypath() {
	global $wpdb;
	global $vtmglobal;

	$sql = "SELECT 
				cpp.PATH_ID,
				path.NAME,
				cpp.ID
		FROM 
			" . VTM_TABLE_PREFIX . "CHARACTER_PRIMARY_PATH cpp,
			" . VTM_TABLE_PREFIX . "PATH path
		WHERE 
			CHARACTER_ID = '%s'
			AND path.ID = cpp.PATH_ID";
	$sql = $wpdb->prepare($sql, $vtmglobal['characterID']);
	$results = $wpdb->get_results($sql, OBJECT_K); 
	
	//print_r($results);
	
	return $results;
}

function vtm_get_chargen_saved($table) {
	global $wpdb;
	global $vtmglobal;
	
	$name        = "items.NAME";
	$level       = "ci.LEVEL";
	$multiple    = "items.MULTIPLE";
	$grouping    = "items.GROUPING";
	$comment     = "ci.COMMENT";
	$extratables = "";
	$filter      = "ci.{$table}_ID = items.ID AND ci.CHARACTER_ID = %s";
	$ordering    = "items.NAME, ci.ID";
	$arguments   = array($vtmglobal['characterID']);
	switch($table) {
		case 'STAT':
			$multiple = "'N'";
			$ordering = "items.ORDERING";
			break;
		case 'SKILL':
			$grouping = "types.NAME";
			$extratables = ", " . VTM_TABLE_PREFIX . "SKILL_TYPE types";
			$filter   .= "AND types.ID = items.SKILL_TYPE_ID";
			$ordering = "types.ORDERING, items.NAME, ci.ID";
			break;
		case 'PATH':
			$multiple = "'N'";
			$grouping = "disc.NAME";
			$comment  = "''";
			$extratables = ",
				" . VTM_TABLE_PREFIX . "DISCIPLINE disc
				LEFT JOIN (
					SELECT ID, LEVEL, DISCIPLINE_ID
					FROM
						" . VTM_TABLE_PREFIX . "CHARACTER_DISCIPLINE
					WHERE
						CHARACTER_ID = %s
				) as cp
				ON
					cp.DISCIPLINE_ID = disc.ID";
			$filter   .= " AND NOT(ISNULL(cp.LEVEL))";
			$ordering = "disc.NAME, items.NAME";
			array_push($arguments, $vtmglobal['characterID']);
			break;
		case 'DISCIPLINE':
			$multiple = "'N'";
			$grouping = "'DISCIPLINE'";
			break;
		case 'BACKGROUND':
			$multiple = "'N'";
			break;
		case 'RITUAL':
			//$level = "0";
			$multiple = "'N'";
			$grouping = "disc.NAME";
			$comment  = "''";
			$extratables = ", " . VTM_TABLE_PREFIX . "DISCIPLINE disc";
			$filter .= "AND items.DISCIPLINE_ID = disc.ID";
			$ordering = "disc.NAME, items.LEVEL, items.name";
			break;
		case 'MERIT':
			$level = "items.VALUE";
			$ordering = "items.NAME, items.VALUE";
			break;
	}
	
	$sql = "SELECT 
				$name      as name, 
				ci.ID      as chartableid,
				items.ID   as itemid, 
				$level     as level_from, 
				$comment   as comment,
				$grouping  as grp,
				$multiple  as multiple
			FROM 
				" . VTM_TABLE_PREFIX . "CHARACTER_{$table} ci,
				" . VTM_TABLE_PREFIX . $table . " items
				$extratables
			WHERE 
				$filter 
			ORDER BY
				$ordering";
				
	$sql = $wpdb->prepare($sql, $arguments);
	//echo "<p>$table saved SQL: $sql</p>";
	$results = $wpdb->get_results($sql); 
	
	// prepare output
	$saved = array();
	$counts = array();
	foreach ($results as $row) {
		
		if (isset($counts[$row->name])) {
			$counts[$row->name]++;
		} else {
			$counts[$row->name] = 1;
		}
		
		if ($row->multiple == 'Y') {
			$key = sanitize_key($row->name) . "_" . $counts[$row->name];
		} else {
			$key = sanitize_key($row->name);
		}
		
		//echo "<li>Save $key:";
		//print_r($row);
		//echo "</li>";
		$saved[$key] = $row;
	}
	
	return $saved;
}
function vtm_get_player_from_login($login) {
	global $wpdb;
	
	$sql = "SELECT players.ID, players.NAME 
			FROM 
				" . VTM_TABLE_PREFIX . "CHARACTER characters,
				" . VTM_TABLE_PREFIX . "PLAYER players
			WHERE
				characters.PLAYER_ID = players.ID
				AND characters.wordpress_id = %s";
	$sql = $wpdb->prepare($sql, $login);
	$player = $wpdb->get_row($sql);
	
	return $player;
}

function vtm_get_player_id($playername, $guess = false) {
	global $wpdb;
	
	if (empty($playername))
		return;
	
	$playername = esc_sql($playername);
	
	if ($guess) {
		$playername = "%$playername%";
		$test = 'LIKE';
	} else {
		$test = '=';
	}
	
	$sql = "SELECT ID FROM " . VTM_TABLE_PREFIX . "PLAYER WHERE NAME $test %s AND DELETED = 'N'";
	$sql = $wpdb->prepare($sql, $playername);
	//echo "<p>SQL: $sql</p>\n";
	
	return $wpdb->get_var($sql);

}

function vtm_get_template_from_templateID() {
	global $wpdb;
	global $vtmglobal;
	
	$sql = "SELECT NAME FROM " . VTM_TABLE_PREFIX . "CHARGEN_TEMPLATE 
		WHERE ID = %s";
	$sql = $wpdb->prepare($sql, $vtmglobal['templateID']);
	return $wpdb->get_var($sql);

}

function vtm_get_player_name($playerid) {
	global $wpdb;
		
	$sql = "SELECT NAME FROM " . VTM_TABLE_PREFIX . "PLAYER WHERE ID = %s";
	$sql = $wpdb->prepare($sql, $playerid);
	return $wpdb->get_var($sql);

}

function vtm_get_clan_name($clanid) {
	global $wpdb;
		
	$sql = "SELECT NAME FROM " . VTM_TABLE_PREFIX . "CLAN WHERE ID = %s";
	$sql = $wpdb->prepare($sql, $clanid);
	return $wpdb->get_var($sql);

}

function vtm_email_new_character($email, $playerid, $name, $clanid, $player, $concept) {

	$ref = vtm_get_chargen_reference();
	$clan = vtm_get_clan_name($clanid);
	$url = add_query_arg('reference', $ref, vtm_get_stlink_url('viewCharGen', true));
	$url = add_query_arg('confirm', true, $url);
		
	$userbody = "<p>Hello $player,</p>
	
	<p>Your new character has been created:</p>
	<ul>
	<li><strong>Reference</strong>: $ref</li>
	<li><strong>Character Name</strong>: $name</li>
	<li><strong>Clan</strong>: $clan</li>
	<li><strong>Template</strong>: " . vtm_get_template_from_templateID() . "</li>
	</ul>

	<p><strong>Concept</strong>: <br>
	" . stripslashes($concept) . "</p>
	
	<p>Click this link to confirm your email address and to return to character generation: <a href='$url'>$url</a></p>";
	
	//echo "<pre>$userbody</pre>\n";
	
	$result = vtm_send_email($email, "New Character Created: $name", $userbody);
	
	if (!$result)
		echo "<p>Failed to send email. Character Ref: $ref</p>\n";
	
}

function vtm_get_chargen_rituals() {
	global $wpdb;
	global $vtmglobal;
	
	// get rituals for disciplines bought with discipline points (and possibly
	// raised with freebie points and XP) and ones only bought with
	// freebies and/or XP
	
	$sql = "(SELECT item.NAME as name, item.ID, item.DESCRIPTION as description, item.LEVEL as level, 
					disc.NAME as grp, 
					IFNULL(xp.CHARTABLE_LEVEL, IFNULL(fb.LEVEL_TO,cdisc.LEVEL)) as discipline_level
			FROM 
				" . VTM_TABLE_PREFIX . "RITUAL item,
				" . VTM_TABLE_PREFIX . "CHARACTER_DISCIPLINE cdisc
				LEFT JOIN (
					SELECT ID, CHARTABLE_ID, LEVEL_TO
					FROM " . VTM_TABLE_PREFIX . "PENDING_FREEBIE_SPEND
					WHERE
						CHARACTER_ID = %s 
						AND CHARTABLE = 'CHARACTER_DISCIPLINE'
				) fb
				ON
					fb.CHARTABLE_ID = cdisc.ID
				LEFT JOIN (
					SELECT ID, CHARTABLE_ID, CHARTABLE_LEVEL
					FROM " . VTM_TABLE_PREFIX . "PENDING_XP_SPEND
					WHERE
						CHARACTER_ID = %s 
						AND CHARTABLE = 'CHARACTER_DISCIPLINE'
				) xp
				ON
					xp.CHARTABLE_ID = cdisc.ID
				,
				" . VTM_TABLE_PREFIX . "DISCIPLINE disc
			WHERE
				item.VISIBLE = 'Y'
				AND item.DISCIPLINE_ID = cdisc.DISCIPLINE_ID
				AND item.DISCIPLINE_ID = disc.ID
				AND cdisc.CHARACTER_ID = %s
				AND item.LEVEL <= IFNULL(xp.CHARTABLE_LEVEL, IFNULL(fb.LEVEL_TO,cdisc.LEVEL))
			) UNION (
			SELECT item.NAME as name, item.ID, item.DESCRIPTION as description, item.LEVEL as level, 
					disc.NAME as grp, 
					IFNULL(xp.CHARTABLE_LEVEL, fb.LEVEL_TO) as discipline_level
			FROM 
				" . VTM_TABLE_PREFIX . "RITUAL item,
				" . VTM_TABLE_PREFIX . "PENDING_FREEBIE_SPEND fb
				LEFT JOIN (
					SELECT ID, ITEMTABLE_ID, CHARTABLE_LEVEL
					FROM " . VTM_TABLE_PREFIX . "PENDING_XP_SPEND
					WHERE
						CHARACTER_ID = %s 
						AND ITEMTABLE = 'DISCIPLINE'
				) xp
				ON
					xp.ITEMTABLE_ID = fb.ITEMTABLE_ID
				,
				" . VTM_TABLE_PREFIX . "DISCIPLINE disc
			WHERE
				item.VISIBLE = 'Y'
				AND item.DISCIPLINE_ID = disc.ID
				AND fb.ITEMTABLE_ID = disc.ID
				AND fb.ITEMTABLE = 'DISCIPLINE'
				AND fb.CHARACTER_ID = %s 
				AND item.LEVEL <= IFNULL(xp.CHARTABLE_LEVEL, fb.LEVEL_TO)
			) UNION (
			SELECT item.NAME as name, item.ID, item.DESCRIPTION as description, item.LEVEL as level, 
					disc.NAME as grp, 
					xp.CHARTABLE_LEVEL as discipline_level
			FROM 
				" . VTM_TABLE_PREFIX . "RITUAL item,
				" . VTM_TABLE_PREFIX . "PENDING_XP_SPEND xp,
				" . VTM_TABLE_PREFIX . "DISCIPLINE disc
			WHERE
				item.VISIBLE = 'Y'
				AND item.DISCIPLINE_ID = disc.ID
				AND xp.ITEMTABLE_ID = disc.ID
				AND xp.ITEMTABLE = 'DISCIPLINE'
				AND xp.CHARACTER_ID = %s 
				AND item.LEVEL <= xp.CHARTABLE_LEVEL
			)
			ORDER BY grp, LEVEL, NAME";
			
	$sql = $wpdb->prepare($sql, $vtmglobal['characterID'], $vtmglobal['characterID'], $vtmglobal['characterID'], $vtmglobal['characterID'], $vtmglobal['characterID'], $vtmglobal['characterID']);
	$results = vtm_sanitize_array($wpdb->get_results($sql));
	//echo "<p>SQL: $sql</p>\n";
	//print_r($results);
	
	// Prepare return array
	$out = array();
	$uniq = array();
	foreach($results as $row) {

		$data = array(
			'CHARTABLE'         => 'CHARACTER_RITUAL',
			'ITEMTABLE'         => 'RITUAL',
			'ITEMTABLE_ID'      => $row->ID,
			'SPECIALISATION_AT' => 0,
			'GROUPING'          => $row->grp,
			'ITEMNAME'          => $row->name,
			'DESCRIPTION'       => $row->description,
			'MULTIPLE'			=> 'N',
			'LEVEL'			    => $row->level
		);
		
		$out[] = $data;
	}
	
	return $out;
}

function vtm_render_freebie_stats($submitted) {
	global $vtmglobal;
	
	$output      = "";

	// COSTS OF STATS - if entry doesn't exist then you can't buy it
	//	$cost['<statname>'] = array( '<from>' => array( '<to>' => <cost>))
	$freebiecosts = vtm_get_chargen_costs('STAT', 'FREEBIE_COST');
	$freebiecosts = array_merge($freebiecosts, vtm_get_chargen_costs('ROAD_OR_PATH', 'FREEBIE_COST'));

	// display stats to buy
	//$items = vtm_get_chargen_stats();
	$items = vtm_get_chargen_itemlist('STAT', "all");
	//$items = array_merge($items, vtm_get_chargen_road());
	$roads = vtm_get_chargen_itemlist('ROAD_OR_PATH');
	$items = array_merge($items, $roads);
	
	// Current stats saved into db
	$saved = vtm_get_chargen_saved('STAT');
	//$saved = array_merge($saved, vtm_get_current_road());
	$sroads = vtm_get_current_road();
	$saved = array_merge($saved, $sroads);
	
	// Current freebies saved into database
	$pendingfb = vtm_get_pending_freebies('STAT');
	$pendingfb = array_merge($pendingfb, vtm_get_pending_freebies('ROAD_OR_PATH'));

	// Current bought with XP
	$pendingxp  = vtm_get_pending_chargen_xp('STAT');  // name => value
	
	//echo "<p>items:";print_r($items); echo "</p>";
	//echo "<p>saved:";print_r($saved); echo "</p>";
	//echo "<p>pendingfb:";print_r($pendingfb); echo "</p>";
	//echo "<p>pendingxp:";print_r($pendingxp); echo "</p>";
	//echo "<p>roads:";print_r($roads); echo "</p>";
	//echo "<p>sroads:";print_r($sroads); echo "</p>";

	$rowoutput = vtm_render_freebie_section(
		$items, 
		$saved, 
		$pendingfb, 
		$pendingxp,
		$freebiecosts, 
		'freebie_stat', 
		0, 
		$submitted, 
		$vtmglobal['genInfo']['MaxDot'],
		array(),
		array()
	);
	
	if ($rowoutput != "")
		$output .= "<table>$rowoutput</table>\n";

	return $output;

}

function vtm_render_freebie_skills($submitted) {
	global $vtmglobal;
	
	$output  = "";
	
	// COSTS OF skills - if entry doesn't exist then you can't buy it
	//	$cost['<statname>'] = array( '<from>' => array( '<to>' => <cost>))
	$freebiecosts = vtm_get_chargen_costs('SKILL', 'FREEBIE_COST');

	// display skills to buy
	//$items = vtm_get_chargen_abilities();
	$items = vtm_get_chargen_itemlist('SKILL', "subgroup");

	// Current skills saved into db
	//$saved = vtm_get_current_skills();
	$saved = vtm_get_chargen_saved('SKILL');
	
	// Current spent
	$pendingfb = vtm_get_pending_freebies('SKILL');
	
	// Current bought with XP
	$pendingxp  = vtm_get_pending_chargen_xp('SKILL');  // name => value

	// Get free stuff from template to get specialities
	$templatefree = vtm_get_free_levels('SKILL');

	$rowoutput = vtm_render_freebie_section(
		$items, 
		$saved, 
		$pendingfb, 
		$pendingxp,
		$freebiecosts, 
		'freebie_skill', 
		1, 
		$submitted, 
		$vtmglobal['genInfo']['MaxDot'],
		$templatefree,
		array()
	);
	
	if ($rowoutput != "")
		$output .= "<table>$rowoutput</table>\n";

	return $output;

}

function vtm_render_freebie_disciplines($submitted) {	
	global $vtmglobal;
	
	$output      = "";
	
	// COSTS OF STATS - if entry doesn't exist then you can't buy it
	//	$cost['<statname>'] = array( '<from>' => array( '<to>' => <cost>))
	$freebiecosts = vtm_get_chargen_costs('DISCIPLINE', 'FREEBIE_COST');

	//$items = vtm_get_chargen_disciplines();
	$items = vtm_get_chargen_itemlist('DISCIPLINE');

	// display stats to buy
	//	hover over radiobutton to show the cost
	//$saved = vtm_get_current_disciplines();
	$saved = vtm_get_chargen_saved('DISCIPLINE');
	
	// Current spent
	$pendingfb = vtm_get_pending_freebies('DISCIPLINE');
	
	// Current bought with XP
	$pendingxp  = vtm_get_pending_chargen_xp('DISCIPLINE');  // name => value
	
	$rowoutput = vtm_render_freebie_section(
		$items,
		$saved, 
		$pendingfb, 
		$pendingxp,
		$freebiecosts, 
		'freebie_discipline', 
		1, 
		$submitted, 
		$vtmglobal['genInfo']['MaxDisc'],
		array(),
		array()
	);
	
	if ($rowoutput != "")
		$output .= "<table>$rowoutput</table>\n";

	return $output;

}

function vtm_render_chargen_xp_disciplines($submitted) {
	global $vtmglobal;
	
	$output  = "";

	$xpcosts   = vtm_get_chargen_costs('DISCIPLINE', 'XP_COST');
	$items     = vtm_get_chargen_itemlist('DISCIPLINE');
	$saved     = vtm_get_chargen_saved('DISCIPLINE');
	$pendingfb = vtm_get_pending_freebies('DISCIPLINE');
	$pendingxp = vtm_get_pending_chargen_xp('DISCIPLINE');
	
	//print_r($xpcosts);
	
	$rowoutput = vtm_render_xp_section(
		$items, 
		$saved, 
		$xpcosts, 
		$pendingfb, 
		$pendingxp, 
		'xp_discipline', 
		1, 
		$submitted,
		array(), 
		$vtmglobal['genInfo']['MaxDisc'],
		array(),
		array()
	);
	
	if ($rowoutput != "")
		$output .= "<table>$rowoutput</table>\n";

	return $output;

}

function vtm_render_freebie_paths($submitted) {
	
	$output      = "";

	$freebiecosts = vtm_get_chargen_costs('PATH', 'FREEBIE_COST');
	$items = vtm_get_chargen_itemlist('PATH');
	$saved = vtm_get_chargen_saved('PATH');
	$pendingfb = vtm_get_pending_freebies('PATH');
	$pendingxp = vtm_get_pending_chargen_xp('PATH');
	// Primary paths
	$primarypaths = vtm_get_chargen_paths(0);

	//print_r($saved);
	//print_r($primarypaths);
	
	$rowoutput = vtm_render_freebie_section(
		$items, 
		$saved, 
		$pendingfb, 
		$pendingxp,
		$freebiecosts, 
		'freebie_path', 
		1, 
		$submitted, 
		5,
		array(),
		$primarypaths
	);

	if ($rowoutput != "")
		$output .= "<table>$rowoutput</table>\n";

	return $output;

} 

function vtm_render_freebie_backgrounds($submitted) {
	global $wpdb;
	global $vtmglobal;
	
	$output      = "";
	
	$freebiecosts = vtm_get_chargen_costs('BACKGROUND', 'FREEBIE_COST');
	//$items = vtm_get_chargen_backgrounds();
	//$saved = vtm_get_current_backgrounds();
	$items = vtm_get_chargen_itemlist('BACKGROUND');
	$saved = vtm_get_chargen_saved('BACKGROUND');
	$pendingfb = vtm_get_pending_freebies('BACKGROUND');
	$pendingxp  = vtm_get_pending_chargen_xp('DISCIPLINE');  // name => value
	$free       = vtm_get_free_levels('BACKGROUND');
	
	$columns     = 3;
	$dotstobuy   = 0;
	
	$max2display = $wpdb->get_var($wpdb->prepare("SELECT MAX_DISCIPLINE FROM " . VTM_TABLE_PREFIX . "GENERATION WHERE ID = %s", $vtmglobal['settings']['limit-generation-low']));
	$maxbgs = $wpdb->get_results($wpdb->prepare("SELECT ITEMTABLE_ID, LEVEL 
		FROM " . VTM_TABLE_PREFIX . "CHARGEN_TEMPLATE_MAXIMUM WHERE 
		ITEMTABLE = 'BACKGROUND' AND TEMPLATE_ID = %s", $vtmglobal['templateID']), OBJECT_K);
	if (count($maxbgs) > 0) {
		$maximums = $maxbgs;
		$maximums['default'] = $max2display;
	} else {
		$maximums = $max2display;
	}

		
	$rowoutput = vtm_render_freebie_section(
		$items, 
		$saved, 
		$pendingfb, 
		$pendingxp,
		$freebiecosts, 
		'freebie_background', 
		1, 
		$submitted, 
		$maximums,
		$free,
		array()
	);

	if ($rowoutput != "")
		$output .= "<table>$rowoutput</table>\n";


	return $output;

}

function vtm_render_freebie_merits($submitted) {
	global $wpdb;
	global $vtmglobal;
	
	$output      = "";

	$freebiecosts = vtm_get_chargen_costs('MERIT', 'FREEBIE_COST');
	//$items     = vtm_get_chargen_merits();
	//$saved     = vtm_get_current_merits();
	$items = vtm_get_chargen_itemlist('MERIT');
	$saved = vtm_get_chargen_saved('MERIT');
	$pendingfb = vtm_get_pending_freebies('MERIT');
	$pendingxp  = vtm_get_pending_chargen_xp('MERIT');  // name => value

	//print_r($saved);
	
	$rowoutput = vtm_render_freebie_section(
		$items, 
		$saved, 
		$pendingfb, 
		$pendingxp,
		$freebiecosts, 
		'freebie_merit', 
		1, 
		$submitted,
		1,
		array(),
		array()
	);
	
	if ($rowoutput != "")
		$output .= "<table id='merit_freebie_table'>$rowoutput</table>\n";

	return $output;

}

function vtm_get_chargen_xp_spent() {
	global $wpdb;
	global $vtmglobal;

	$spent = 0;

	if (isset($_POST['xp_stat'])       || isset($_POST['xp_skill']) ||
		isset($_POST['xp_discipline']) || isset($_POST['xp_background']) ||
		isset($_POST['xp_merit'])      || isset($_POST['xp_path'])) {
	
		$xpcosts['STAT']       = vtm_get_chargen_costs('STAT', 'XP_COST');
		$xpcosts['SKILL']      = vtm_get_chargen_costs('SKILL', 'XP_COST');
		$xpcosts['DISCIPLINE'] = vtm_get_chargen_costs('DISCIPLINE', 'XP_COST');
		$xpcosts['MERIT']      = vtm_get_chargen_costs('MERIT', 'XP_COST');
		$xpcosts['PATH']       = vtm_get_chargen_costs('PATH', 'XP_COST');
		
		$current['STAT']       = vtm_get_chargen_saved('STAT');
		$current['SKILL']      = vtm_get_chargen_saved('SKILL');
		$current['DISCIPLINE'] = vtm_get_chargen_saved('DISCIPLINE');
		$current['MERIT']      = vtm_get_chargen_saved('MERIT');
		$current['PATH']       = vtm_get_chargen_saved('PATH');

		$pendingfb['STAT']       = vtm_get_pending_freebies('STAT');
		$pendingfb['SKILL']      = vtm_get_pending_freebies('SKILL');
		$pendingfb['DISCIPLINE'] = vtm_get_pending_freebies('DISCIPLINE');
		$pendingfb['MERIT']      = vtm_get_pending_freebies('MERIT');
		$pendingfb['PATH']       = vtm_get_pending_freebies('PATH');
		
		$bought['STAT']       = isset($_POST['xp_stat']) ? $_POST['xp_stat'] : array();
		$bought['SKILL']      = isset($_POST['xp_skill']) ? $_POST['xp_skill'] : array();
		$bought['DISCIPLINE'] = isset($_POST['xp_discipline']) ? $_POST['xp_discipline'] : array();
		$bought['MERIT']      = isset($_POST['xp_merit']) ? $_POST['xp_merit'] : array();
		$bought['PATH']       = isset($_POST['xp_path']) ? $_POST['xp_path'] : array();

		$templatefree['SKILL']      = vtm_get_free_levels('SKILL');

		foreach ($bought as $type => $items) {
			foreach ($items as $key => $levelto) {
			
				if (isset($templatefree[$type][$key]->LEVEL))
					$freelevel = $templatefree[$type][$key]->LEVEL;
				else
					$freelevel = 0;
				$currlevel = isset($current[$type][$key]->level_from)  ? $current[$type][$key]->level_from  : 0;
				$levelfrom = max($currlevel, $freelevel);
				$levelfrom = isset($pendingfb[$type][$key]->value) ? $pendingfb[$type][$key]->value : $levelfrom;
				$costkey   = preg_replace("/_\d+$/", "", $key);
				
				if ($type == 'MERIT') {
					$levelfrom = 0;
					$levelto = 1;
				}
				
				$cost = isset($xpcosts[$type][$costkey][$levelfrom][$levelto]) ? $xpcosts[$type][$costkey][$levelfrom][$levelto] : 0;
				$spent += $cost;
				//echo "<li>Running total is $spent. Bought $key to $levelto ($cost)</li>\n";
				
				// if ($level_to != 0) {
					// $actualkey = preg_replace("/_\d+$/", "", $key);
					
					// if ($type == 'MERIT') {
						// if (!isset($current[$type][$key])) {
							// if (isset($current[$type][$actualkey]->multiple) && $current[$type][$actualkey]->multiple == 'Y') {
								// $spent += isset($xpcosts[$type][$actualkey][0][1]) ? $xpcosts[$type][$actualkey][0][1] : 0;
								// //echo "<li>$key / $actualkey, cost: {$xpcosts[$type][$actualkey][0][1]}</li>\n";
							// }
						// } else {
							// //echo "<li>$key - from:$levelfrom, to:$level_to, cost: {$xpcosts[$type][$key][0][1]}</li>\n";
							// $spent += isset($xpcosts[$type][$key][0][1]) ? $xpcosts[$type][$key][0][1] : 0;
						// }
					// } else {
						// $spent += isset($xpcosts[$type][$actualkey][$levelfrom][$level_to]) ? $xpcosts[$type][$actualkey][$levelfrom][$level_to] : 0;
						// //echo "<li>$key - $type, from $levelfrom to $level_to, cost: {$xpcosts[$type][$actualkey][$levelfrom][$level_to]}, running total: $spent</li>\n";
					// }
				// }
			}
		}
	} else {
	
		$sql = "SELECT SUM(AMOUNT) FROM " . VTM_TABLE_PREFIX . "PENDING_XP_SPEND
				WHERE CHARACTER_ID = %s";
		$sql = $wpdb->prepare($sql, $vtmglobal['characterID']);
		$spent = -$wpdb->get_var($sql);
	
	}
	
	return $spent;
} 

function vtm_render_chargen_xp_stats($submitted) {
	global $vtmglobal;

	$output = "";
	$geninfo   = vtm_calculate_generation();

	// Get costs
	$xpcosts = vtm_get_chargen_costs('STAT', 'XP_COST');
	// display stats to buy
	$items = vtm_get_chargen_itemlist('STAT', "all");
	// Get current stats in database
	$saved = vtm_get_chargen_saved('STAT');
	// Get Freebie points spent on stats
	$pendingfb = vtm_get_pending_freebies('STAT');
	// Currently bought with XP
	$pendingxp  = vtm_get_pending_chargen_xp('STAT');  // name => value
	
	$rowoutput = vtm_render_xp_section(
		$items, 
		$saved, 
		$xpcosts, 
		$pendingfb, 
		$pendingxp, 
		'xp_stat', 
		0, 
		$submitted,
		array(),
		$vtmglobal['genInfo']['MaxDot'],
		array(),
		array()
	);

	if ($rowoutput != "")
		$output .= "<table>$rowoutput</table>\n";

	return $output;
}

function vtm_render_chargen_xp_paths($submitted) {
	global $vtmglobal;

	$output = "";

	$xpcosts   = vtm_get_chargen_costs('PATH', 'XP_COST');
	$items = vtm_get_chargen_itemlist('PATH');
	$saved = vtm_get_chargen_saved('PATH');
	$pendingfb = vtm_get_pending_freebies('PATH');
	$pendingxp = vtm_get_pending_chargen_xp('PATH');
	//print_r($current_path);
	$primarypaths = vtm_get_chargen_paths(0);
	
	$rowoutput = vtm_render_xp_section(
		$items, 
		$saved, 
		$xpcosts, 
		$pendingfb, 
		$pendingxp, 
		'xp_path', 
		1, 
		$submitted,
		array(),
		5,
		array(),
		$primarypaths
	);
	
	if ($rowoutput != "")
		$output .= "<table>$rowoutput</table>\n";

	return $output;
}

function vtm_render_chargen_xp_skills($submitted) {
	global $vtmglobal;
	
	$output = "";

	// Get costs
	$xpcosts      = vtm_get_chargen_costs('SKILL', 'XP_COST');
	$items        = vtm_get_chargen_itemlist('SKILL', "subgroup");
	$saved        = vtm_get_chargen_saved('SKILL');
	$pendingfb    = vtm_get_pending_freebies('SKILL');
	$pendingxp    = vtm_get_pending_chargen_xp('SKILL');
	$templatefree = vtm_get_free_levels('SKILL');
	
	$rowoutput = vtm_render_xp_section(
		$items, 
		$saved, 
		$xpcosts, 
		$pendingfb,
		$pendingxp, 
		'xp_skill', 
		1, 
		$submitted,
		array(), 
		$vtmglobal['genInfo']['MaxDot'], 
		$templatefree,
		array()
	);
	
	if ($rowoutput != "")
		$output .= "<table>$rowoutput</table>\n";

	return $output;

}

function vtm_render_chargen_xp_merits($submitted) {
	global $wpdb;
	
	$output = "";

	// Get costs
	$xpcosts   = vtm_get_chargen_costs('MERIT', 'XP_COST');
	$items = vtm_get_chargen_itemlist('MERIT');
	$saved = vtm_get_chargen_saved('MERIT');
	$fbcosts   = vtm_get_chargen_costs('MERIT', 'FREEBIE_COST');
	$pendingfb = vtm_get_pending_freebies('MERIT');
	$pendingxp = vtm_get_pending_chargen_xp('MERIT');

	$rowoutput = vtm_render_xp_section(
		$items, 
		$saved, 
		$xpcosts, 
		$pendingfb, 
		$pendingxp, 
		'xp_merit', 
		1, 
		$submitted, 
		$fbcosts,
		1,
		array(),
		array()
	);
	
	if ($rowoutput != "")
		$output .= "<table id='merit_xp_table'>$rowoutput</table>\n";

	return $output;

}
function vtm_render_chargen_xp_rituals($submitted) {
	
	$output = "";

	// Get costs
	$xpcosts   = vtm_get_chargen_costs('RITUAL', 'XP_COST');
	$items     = vtm_get_chargen_rituals();
	//$saved     = vtm_get_current_rituals();
	$saved     = vtm_get_chargen_saved('RITUAL');
	$pendingxp = vtm_get_pending_chargen_xp('RITUAL');
	
	//print_r($saved);
	
	$rowoutput = vtm_render_xp_section(
		$items, 
		$saved, 
		$xpcosts, 
		array(), 
		$pendingxp, 
		'xp_ritual', 
		1, 
		$submitted, 
		array(),
		1,
		array(),
		array()
	);
	
	if ($rowoutput != "")
		$output .= "<table id='ritual_xp_table'>$rowoutput</table>\n";

	return $output;

}

//--------------------------------------------------------------
// VALIDATE
//--------------------------------------------------------------
function vtm_validate_basic_info($usepost = 1) {
	global $wpdb;
	global $vtmglobal;

	$ok = 1;
	$errormessages = "";
	$complete = 1;

	$wpdb->show_errors();

	// VALIDATE BASIC INFO
	//		- error: character name is not blank
	//		- error: new player? player name is not duplicated
	//		- error: old player? player name is found
	//		- error: login name doesn't already exist (except if it's the currently logged in acct)
	//		- error: email address is not blank and looks valid
	//		- error: concept is not blank
	//		- error: email address not confirmed
	
	if (!$usepost) {
	
		$sql = "SELECT ch.NAME, ch.PLAYER_ID, ch.WORDPRESS_ID, ch.EMAIL, ch.CONCEPT,
					pl.NAME as player, ch.PRIVATE_CLAN_ID
				FROM
					" . VTM_TABLE_PREFIX . "CHARACTER ch,
					" . VTM_TABLE_PREFIX . "PLAYER pl
				WHERE
					ch.PLAYER_ID = pl.id
					AND ch.ID = %s";
		$sql = $wpdb->prepare($sql, $vtmglobal['characterID']);
		$row = $wpdb->get_row($sql);
		//echo "<p>SQL: $sql</p>\n";
		//print_r($row);
	
		$dbcharacter = vtm_formatOutput($row->NAME);
		$dbplayer = stripslashes($row->player);
		$dbplayerID = $row->PLAYER_ID;
		$dbnewplayer = 'off';
		$dbwordpressID = $row->WORDPRESS_ID;
		$dbemail = $row->EMAIL;
		$dbconcept = vtm_formatOutput($row->CONCEPT);
		$dbclanid = vtm_formatOutput($row->CONCEPT);
	}
	
	$postcharacter  = $usepost ? (isset($_POST['character'])    ? $_POST['character']    : '') : $dbcharacter;
	$playername     = $usepost ? (isset($_POST['player'])       ? stripslashes($_POST['player'])       : '') : $dbplayer;
	$playeridguess  = $usepost ? (isset($_POST['playerID'])     ? $_POST['playerID']      : -1) : $dbplayerID;
	$postnewplayer  = $usepost ? (isset($_POST['newplayer'])    ? $_POST['newplayer']    : 'off') : $dbnewplayer;
	$login          = $usepost ? (isset($_POST['wordpress_id']) ? $_POST['wordpress_id'] : '') : $dbwordpressID;
	$email          = $usepost ? (isset($_POST['email'])        ? $_POST['email']        : '') : $dbemail;
	$postconcept    = $usepost ? (isset($_POST['concept'])      ? $_POST['concept']      : '') : $dbconcept;
	$postclanid     = $usepost ? (isset($_POST['priv_clan'])    ? $_POST['priv_clan']    : 0) : $dbclanid;
		
	if (empty($postcharacter)) {
		$errormessages .= "<li>ERROR: Please enter a character name</li>\n";
		$ok = 0;
		$complete = 0;
	}
	
	if (empty($playername)) {
		$errormessages .= "<li>ERROR: Please enter a player name</li>\n";
		$ok = 0;
		$complete = 0;
	} else {
		$playerid = vtm_get_player_id($playername);
		
		if ($postnewplayer == 'off') {
			// old player
			if (!isset($playerid)) {
				$ok = 0;
				$complete = 0;
				// can't find playername.  make a guess
				$playerid = vtm_get_player_id($playername, true);
				if (isset($playerid)) {
					$errormessages .= "<li>ERROR: Could not find a player with the name '" . vtm_formatOutput($playername) . "'. Did you mean '" . vtm_formatOutput(vtm_get_player_name($playerid)) . "'?</li>\n";
				}
				else
					$errormessages .= "<li>ERROR: Could not find a player with the name '" . vtm_formatOutput($playername) . "'. Are you a new player?</li>\n";
			}
		} else {
			// new player
			if (isset($playerid)) {
				$ok = 0;
				$complete = 0;
				$errormessages .= "<li>ERROR: A player already exists with the name '" . vtm_formatOutput($playername) . "'. Are you a returning player?</li>\n";
			}
		}
	}
	
	if (empty($login)) {
		$errormessages .= "<li>ERROR: Please enter a login name</li>\n";
		$ok = 0;
		$complete = 0;
	}
	else {
		$current_user = wp_get_current_user();
		if (username_exists( $login ) && $login != $current_user->user_login) {
			$ok = 0;
			$complete = 0;
			$errormessages .= "<li>ERROR: An account already exists with the login name '$login'. Please choose another.</li>\n";
		}
		elseif (!validate_username( $login )) {
			$ok = 0;
			$complete = 0;
			$errormessages .= "<li>ERROR: Login name '" . vtm_formatOutput($login) . "' is invalid. Please choose another.</li>\n";
		}
		else {
			if ($vtmglobal['characterID'] > 0) {
				$sql = "SELECT NAME FROM " . VTM_TABLE_PREFIX . "CHARACTER WHERE WORDPRESS_ID = %s AND ID != %s";
				$sql = $wpdb->prepare($sql, $login, $vtmglobal['characterID']);
			} else {
				$sql = "SELECT NAME FROM " . VTM_TABLE_PREFIX . "CHARACTER WHERE WORDPRESS_ID = %s";
				$sql = $wpdb->prepare($sql, $login);
			}
			$names = $wpdb->get_col($sql);
			if (count($names) > 0) {
				$ok = 0;
				$complete = 0;
				$errormessages .= "<li>ERROR: Login name '" . vtm_formatOutput($login) . "' has already been chosen for another character.</li>\n";
			}
		}
	}
	
	if (empty($email)) {
			$ok = 0;
			$complete = 0;
			$errormessages .= "<li>ERROR: Email address is missing.</li>\n";
	} else {
		if (!is_email($email)) {
			$ok = 0;
			$complete = 0;
			$errormessages .= "<li>ERROR: Email address '$email' does not seem to be a valid email address.</li>\n";
		}
	}
	
	if (empty($postconcept)) {
		$errormessages .= "<li>ERROR: Please enter your character concept.</li>\n";
		$ok = 0;
		$complete = 0;
	}
	
	$currentclanid = $wpdb->get_var($wpdb->prepare("SELECT PRIVATE_CLAN_ID FROM " . VTM_TABLE_PREFIX . "CHARACTER WHERE ID = %s", $vtmglobal['characterID']));
	$discspends = vtm_count($wpdb->get_var($wpdb->prepare("SELECT ID 
						FROM " . VTM_TABLE_PREFIX . "PENDING_FREEBIE_SPEND 
						WHERE CHARACTER_ID = %s AND (ITEMTABLE='DISCIPLINE' OR ITEMTABLE = 'PATH')", $vtmglobal['characterID'])));
	$discspends += vtm_count($wpdb->get_var($wpdb->prepare("SELECT ID 
						FROM " . VTM_TABLE_PREFIX . "PENDING_XP_SPEND 
						WHERE CHARACTER_ID = %s AND (ITEMTABLE='DISCIPLINE' OR ITEMTABLE = 'PATH')", $vtmglobal['characterID'])));
	$discspends += vtm_count($wpdb->get_var($wpdb->prepare("SELECT ID 
						FROM " . VTM_TABLE_PREFIX . "CHARACTER_DISCIPLINE 
						WHERE CHARACTER_ID = %s", $vtmglobal['characterID'])));
	$discspends += vtm_count($wpdb->get_var($wpdb->prepare("SELECT ID 
						FROM " . VTM_TABLE_PREFIX . "CHARACTER_PATH 
						WHERE CHARACTER_ID = %s", $vtmglobal['characterID'])));

	if ($currentclanid != $postclanid && $postclanid != 0 && $discspends > 0) {
		$errormessages .= "<li>WARNING: All spends on Disciplines will be deleted due to the change in Clan</li>\n";
	}
	
	// Email address must be confirmed
	$confirm = $wpdb->get_var($wpdb->prepare("SELECT EMAIL_CONFIRMED FROM " . VTM_TABLE_PREFIX . "CHARACTER_GENERATION
		WHERE CHARACTER_ID = %s", $vtmglobal['characterID']));
	if ($vtmglobal['characterID'] > 0 && $confirm !== 'Y') {
		$complete = 0;
		$errormessages .= "<li>WARNING: You must confirm your email address by clicking the link that was emailed to you before
							your character can be submitted</li>";
	}
	
	// Check potential issues with the  default Path of Enlightenment
	$sql = $wpdb->prepare("SELECT ID FROM " . VTM_TABLE_PREFIX . "ROAD_OR_PATH WHERE ID = '%s';",get_option( 'vtm_chargen_humanity', '1' ));
	$path = $wpdb->get_var($sql);
	if (empty($path)) {
		$ok = 0;
		$complete = 0;
		$errormessages .= "<li>ERROR: There is an issue with the default Path of Enlightenment. Please ask your site admin to check the Configuration.</li>\n";
	}
	
	// Check for missing primary paths
	// TEST THIS - NOT SURE ITS WORKING
	//$result = $wpdb->get_results("SELECT * FROM " . VTM_TABLE_PREFIX . "CHARGEN_PRIMARY_PATH");
	//print_r($result);
	$result = $wpdb->get_results($wpdb->prepare("SELECT ID FROM " . VTM_TABLE_PREFIX . "CHARGEN_PRIMARY_PATH WHERE TEMPLATE_ID = '%s'", $vtmglobal['templateID']));
	//print_r($result);
	if (count(vtm_get_magic_disciplines()) > 0 && count($result) == 0) {
		$errormessages .= "<li>WARNING: '{$vtmglobal['templateID']}'No Primary Paths for Disciplines have been defined in the character generation template. Please ask your site admin to check and update the template data table settings.</li>\n";
	}

	return array($ok, $errormessages, $complete);
}

function vtm_validate_abilities($usepost = 1) {
	global $vtmglobal;

	$ok = 1;
	$errormessages = "";
	$complete = 1;
	
	$templatefree = vtm_get_free_levels('SKILL');
	$items        = vtm_get_chargen_itemlist('SKILL');
		
	// VALIDATE ABILITIES
	// P/S/T
	//		- WARN/ERROR: correct number of points spent in each group
	// 		- ERROR: check that nothing is over the max
	
	if (!$usepost) {
		$saved = vtm_get_chargen_saved('SKILL');
		$dbvalues = array();
		foreach($saved as $key => $row) {
			$dbvalues[$key] = $row->level_from;
		}
		//print_r($dbvalues);
	}
	
	$posted = isset($_POST['ability_value']) ? $_POST['ability_value'] : array();
	$postvalues = $usepost ? $posted : $dbvalues;
	
	if (count($postvalues) > 0) {
		
		$target = $vtmglobal['settings']['abilities-primary'] + $vtmglobal['settings']['abilities-secondary'] + $vtmglobal['settings']['abilities-tertiary'];
		$check = 0;
		
		$total = 0;
		
		foreach ($items as $item) {
			$loop = $item['MULTIPLE'] == 'Y' ? 4 : 1;
			$name = sanitize_key($item['ITEMNAME']);
			
			for ($j = 1 ; $j <= $loop ; $j++) {
				if ($item['MULTIPLE'] == 'Y') {
					$key = $name . "_" . $j;
					if (isset($templatefree[$key])) {
						$loop++;
					}
				} else {
					$key = $name;
				}
				
				if (isset($postvalues[$key])) {
					$free = isset($templatefree[$key]->LEVEL) ? $templatefree[$key]->LEVEL : 0;
					$total += max(0,$postvalues[$key] - $free);
					
					if ($postvalues[$key] > $vtmglobal['settings']['abilities-max']) {
						$ok = 0;
						$complete = 0;
						$errormessages .= "<li>ERROR: Ability '$name' is greater than the maximum of {$vtmglobal['settings']['abilities-max']}</li>\n";
					}
				}
			}
		}
		
		if ($total > $target) {
			$errormessages .= "<li>ERROR: You have spent too many points</li>\n";
			$ok = 0;
			$complete = 0;
		}
		elseif ($total < $target)  {
			$errormessages .= "<li>WARNING: You haven't spent enough points</li>\n";
			$complete = 0;
		}
		
		
		$info = vtm_get_pst(array(), 
			$postvalues,
			vtm_get_chargen_itemlist('SKILL'),
			$vtmglobal['settings']['abilities-primary'], 
			$vtmglobal['settings']['abilities-secondary'], 
			$vtmglobal['settings']['abilities-tertiary'], 
			0, $templatefree
		);
		foreach ($info['totals'] as $group => $count) {
			if (!isset($info['correct'][$group]) || $count != $info['correct'][$group]) {
				$errormessages .= "<li>WARNING: Wrong number of dots spent in $group section</li>\n";
				$complete = 0;
			}
		}
			
	} else {
		$errormessages .= "<li>WARNING: You have not spent any dots</li>\n";
		$complete = 0;
	}

	return array($ok, $errormessages, $complete);
}

function vtm_validate_attributes($usepost = 1) {
	global $vtmglobal;

	$ok = 1;
	$errormessages = "";
	$complete = 1;

	if (!$usepost) {
		$saved = vtm_get_chargen_saved('STAT');
		$dbvalues = array();
		//print_r($saved);
		foreach($saved as $key => $row) {
			if ($row->grp == 'Physical' || $row->grp == 'Mental' || $row->grp == 'Social')
				$dbvalues[sanitize_key($row->name)] = $row->level_from;
		}
		//print_r($dbvalues);
	}
		
	$posted = isset($_POST['attribute_value']) ? $_POST['attribute_value'] : array();
	$postvalues = $usepost ? $posted : $dbvalues;
	
	// VALIDATE ATTRIBUTES
	// P/S/T
	//		- WARN/ERROR: correct number of points spent in each group
	// Point Spent
	//		- WARN/ERROR: point total correct
	if (count($postvalues) > 0) {

		if ($vtmglobal['settings']['attributes-method'] == 'PST') {
			$target = $vtmglobal['settings']['attributes-primary'] + $vtmglobal['settings']['attributes-secondary'] + $vtmglobal['settings']['attributes-tertiary'];
		} else {
			$target = $vtmglobal['settings']['attributes-points'];
		}
		
		$total = 0;
		foreach ($postvalues as $att => $val)
			$total += max(0,$val - 1);
		
		if ($total > $target) {
			$errormessages .= "<li>ERROR: You have spent too many points</li>\n";
			$ok = 0;
			$complete = 0;
		}
		elseif ($total < $target)  {
			$errormessages .= "<li>WARNING: You haven't spent enough points</li>\n";
			$complete = 0;
		}
	} else {
		$errormessages .= "<li>WARNING: You have not spent any dots</li>\n";
		$complete = 0;
	}
	
	return array($ok, $errormessages, $complete);
}

function vtm_validate_disciplines($usepost = 1) {
	global $vtmglobal;

	$ok = 1;
	$errormessages = "";
	$complete = 1;

	if (!$usepost) {
		$disciplines = vtm_get_chargen_saved('DISCIPLINE');
		$dbvalues = array();
		foreach ($disciplines as $disc) {
			$dbvalues[sanitize_key($disc->name)] = $disc->level_from;
		}
	}
	
	$postvalues = $usepost ? 
				(isset($_POST['discipline_value']) ? $_POST['discipline_value'] : array()) :
				$dbvalues;

	// VALIDATE DISCIPLINES
	//		- spend the right amount of points
	if (count($postvalues) > 0) {
		$values = $postvalues;
		
		$total = 0;
		foreach  ($values as $id => $val) {
			$total += max(0,$val);
		}
		
		if ($total > $vtmglobal['settings']['disciplines-points']) {
			$errormessages .= "<li>ERROR: You have spent too many dots</li>\n";
			$ok = 0;
			$complete = 0;
		}
		elseif ($total < $vtmglobal['settings']['disciplines-points'])  {
			$errormessages .= "<li>WARNING: You haven't spent enough dots</li>\n";
			$complete = 0;
		}
			
	} else {
		$errormessages .= "<li>WARNING: You have not spent any dots</li>\n";
		$complete = 0;
	}

	return array($ok, $errormessages, $complete);
}

function vtm_validate_paths($usepost = 1) {
	global $vtmglobal;

	$ok = 1;
	$errormessages = "";
	$complete = 1;

	if (!$usepost) {
		$paths = vtm_get_chargen_saved('PATH');
		$dbvalues = array();
		foreach ($paths as $path) {
			$dbvalues[sanitize_key($path->name)] = $path->level_from;
		}
		$discplines = vtm_get_chargen_paths(1);
		$dbdisclvl = array();
		$dbdiscname = array();
		foreach ($discplines as $disc) {
			$dbdisclvl[sanitize_key($disc->discipline)] = $disc->discipline_level;
			$dbdiscname[sanitize_key($disc->discipline)] = $disc->discipline;
		}
		
		$dbpathdisc = array();
		$items      = vtm_get_chargen_itemlist('PATH');
		foreach ($items as $path) {
			$dbpathdisc[sanitize_key($path['ITEMNAME'])] = sanitize_key($path['GROUPING']);
			$dbppaths[sanitize_key($path['GROUPING'])] = sanitize_key($path['ITEMNAME']);
		}
		
		//echo "\n<li>dbvalues:\n";
		//print_r($dbvalues);
		//echo "\n</li><li>dbpathdisc:\n";
		//print_r($dbpathdisc);
		//echo "\n</li>";
	}
	
	// [path_name] = pathlevel
	$postvalues = $usepost ? 
				(isset($_POST['path_value']) ? $_POST['path_value'] : array()) :
				$dbvalues;
	// [discipline_name] = path_name
	$postppaths = $usepost ? 
				(isset($_POST['primarypaths']) ? $_POST['primarypaths'] : array()) :
				$dbppaths;
	// [discipline_name] = discipline_level
	$postdisclvl = $usepost ? 
				(isset($_POST['discipline_level']) ? $_POST['discipline_level'] : array()) :
				$dbdisclvl;
	// [discipline_name] = full discipline name
	$postdiscname = $usepost ? 
				(isset($_POST['discipline_name']) ? $_POST['discipline_name'] : array()) :
				$dbdiscname;
	// [path_name] = discipline_name
	$postpathdisc = $usepost ? 
				(isset($_POST['path_disc_id']) ? $_POST['path_disc_id'] : array()) :
				$dbpathdisc;
				
	//print_r($_POST['discipline_name']);

	// VALIDATE PATHS
	//		- spend the right amount of points
	if (count($postvalues) > 0) {
		foreach ($postdisclvl as $dkey => $discipline_level) {
			//echo "<li>Checking $dkey with level $discipline_level, primary path is {$postppaths[$dkey]}</li>";
			
			
			// Add up how many points spent for this discipline on paths
			$total = 0;
			foreach ($postpathdisc as $pkey => $test_dkey) {
				//echo "<li>$test_dkey == $dkey?</li>";
				if (isset($postvalues[$pkey]) && $test_dkey == $dkey) {
					$total += $postvalues[$pkey];
				}
			}
			//echo "<li>Total points spent on " . vtm_formatOutput($postdiscname[$dkey]) . " paths is: $total</li>";
						
			// Check total points spent
			if ($total > $discipline_level) {
				$errormessages .= "<li>ERROR: You have spent too many dots on {$postdiscname[$dkey]} paths</li>\n";
				$ok = 1;
				$complete = 0;
			}
			elseif ($total < $discipline_level) {
				$errormessages .= "<li>ERROR: You haven't spent enough dots on {$postdiscname[$dkey]} paths</li>\n";
				$ok = 1;
				$complete = 0;
			}
			elseif ($total == 0) {
				$errormessages .= "<li>ERROR: You haven't spent any dots on {$postdiscname[$dkey]} paths</li>\n";
				$ok = 1;
				$complete = 0;
			}
			
			
		}
		
	}
	
	return array($ok, $errormessages, $complete);
}

function vtm_validate_backgrounds($usepost = 1) {
	global $wpdb;
	global $vtmglobal;

	$ok = 1;
	$errormessages = "";
	$complete = 1;

	$templatefree = vtm_get_free_levels('BACKGROUND');
	
	if (!$usepost) {
		$saved = vtm_get_chargen_saved('BACKGROUND');
		$dbvalues = array();
		foreach($saved as $row) {
			$dbvalues[sanitize_key($row->name)] = $row->level_from;
		}
	}
	
	$postvalues = $usepost ? 
				(isset($_POST['background_value']) ? $_POST['background_value'] : array()) :
				$dbvalues;
				
	// VALIDATE BACKGROUNDS
	//		- all points spent
	if (isset($postvalues)) {
		$values = $postvalues;
						
		$total = 0;
		foreach ($values as $att => $val) {
			$free = isset($templatefree[$att]->LEVEL) ? $templatefree[$att]->LEVEL : 0;
			$total += max(0,$val - $free);
		}
		
		if ($total > $vtmglobal['settings']['backgrounds-points']) {
			$errormessages .= "<li>ERROR: You have spent too many dots</li>\n";
			$ok = 0;
			$complete = 0;
		}
		elseif ($total < $vtmglobal['settings']['backgrounds-points'])  {
			$errormessages .= "<li>WARNING: You haven't spent enough dots</li>\n";
			$complete = 0;
		}
							
	} else {
		$errormessages .= "<li>WARNING: You have not spent any dots</li>\n";
		$complete = 0;
	}

	return array($ok, $errormessages, $complete);
}

function vtm_validate_virtues($usepost = 1) {
	global $wpdb;
	global $vtmglobal;
	
	$ok = 1;
	$errormessages = "";
	$complete = 1;
	$pendingfb  = vtm_get_pending_freebies('STAT');
	$pendingxp  = vtm_get_pending_chargen_xp('STAT');

	if (!$usepost) {
		$stats = vtm_get_chargen_saved('STAT');
		$dbvalues = array();
		foreach ($stats as $stat) {
			if ($stat->grp == 'Virtue')
				$dbvalues[sanitize_key($stat->name)] = $stat->level_from;
		}
		
		$dbpath = $wpdb->get_var($wpdb->prepare("SELECT ROAD_OR_PATH_ID FROM " . VTM_TABLE_PREFIX . "CHARACTER WHERE ID = %s", $vtmglobal['characterID']));
	}
	
	$postvalues = $usepost ? 
				(isset($_POST['virtue_value']) ? $_POST['virtue_value'] : array()) :
				$dbvalues;
	$postpath = $usepost ? 
				(isset($_POST['path']) ? $_POST['path'] : 0) :
				$dbpath;
	$lastpath = isset($_POST['savedpath']) ? $_POST['savedpath'] : $postpath;
	
	//print_r($postvalues);
	
	// VALIDATE VIRTUES
	//		- all points spent
	//		- point spent on the correct virtues
	if ($lastpath != $postpath) {
		$ok = 0;
		$complete = 0;
		$errormessages .= "<li>WARNING: Path has changed. Please check/update Virtue dot spends.</li>\n";
	}
	elseif (count($postvalues) > 0) {
		$values = $postvalues;
		
		$selectedpath = $postpath;
		$statkey1 = vtm_get_virtue_statkey(1, $selectedpath);
		$statkey2 = vtm_get_virtue_statkey(2, $selectedpath);
		
		$total = 0;
		$statfail = 0;
		foreach  ($values as $key => $val) {
			$level = $val - vtm_has_virtue_free_dot($selectedpath, $key);
			$total += $level;
			
			if ($key != $statkey1 && $key != $statkey2 && $key != 'courage') {
				$statfail = 1;
			} 
			elseif ($level == 0 && !isset($pendingfb[$key]) 
				&& !isset($pendingxp[$key]) && vtm_has_virtue_free_dot($selectedpath, $key) == 0) {
				$errormessages .= "<li>WARNING: Virtues must each have at least 1 dot</li>\n";
				$complete = 0;
			}
			
		}
		
		if ($total > $vtmglobal['settings']['virtues-points']) {
			$errormessages .= "<li>ERROR: You have spent too many dots</li>\n";
			$ok = 0;
			$complete = 0;
		}
		elseif ($total < $vtmglobal['settings']['virtues-points'])  {
			$errormessages .= "<li>WARNING: You haven't spent enough dots</li>\n";
			$complete = 0;
		}
		if ($statfail) {
			$errormessages .= "<li>ERROR: Please update Virtues for the selected path</li>\n";
			$ok = 0;
			$complete = 0;
		}
		
							
	} else {
		$errormessages .= "<li>WARNING: You have not spent any dots</li>\n";
		$complete = 0;
	}

	return array($ok, $errormessages, $complete);
}

function vtm_validate_freebies($usepost = 1) {
	global $vtmglobal;
	
	$ok = 1;
	$errormessages = "";
	$complete = 1;

	if (!$usepost) {
		$dbmerit = array();
		$dbpath = array();
		$dbdisc = array();
		$items = vtm_get_pending_freebies('MERIT');
		foreach ($items as $item) {
			$dbmerit[sanitize_key($item->name)] = $item->value;
		}
		$items = vtm_get_pending_freebies('PATH');
		foreach ($items as $item) {
			$dbpath[sanitize_key($item->name)] = $item->value;
		}
		$items = vtm_get_pending_freebies('DISCIPLINE');
		foreach ($items as $item) {
			$dbdisc[sanitize_key($item->name)] = $item->value;
		}
	}
	$postmerit = $usepost ? 
				(isset($_POST['freebie_merit']) ? $_POST['freebie_merit'] : array()) :
				$dbmerit;
	$postpath = $usepost ? 
				(isset($_POST['freebie_path']) ? $_POST['freebie_path'] : array()) :
				$dbpath;
	$postdisc = $usepost ? 
				(isset($_POST['freebie_discipline']) ? $_POST['freebie_discipline'] : array()) :
				$dbdisc;
	
	// VALIDATE FREEBIE POINTS
	//		Right number of points spent
	//		Not too many merits bought
	//		Not too many flaws bought
	//		Level of paths bought do not exceed level of discipline
	$meritsspent = 0;
	$flawsgained = 0;
	if (count($postmerit) > 0) {
		$bought = $postmerit;
		foreach ($bought as $name => $level_to) {
			if ($level_to > 0)
				$meritsspent += $level_to;
			else
				$flawsgained += -$level_to;
		}
		if ($vtmglobal['settings']['merits-max'] > 0 && $meritsspent > $vtmglobal['settings']['merits-max']) {
			$errormessages .= "<li>ERROR: You have bought too many points of Merits</li>\n";
			$ok = 0;
			$complete = 0;
		}
		if ($vtmglobal['settings']['flaws-max'] > 0 && $flawsgained > $vtmglobal['settings']['flaws-max']) {
			$errormessages .= "<li>ERROR: You have gained too many points from Flaws</li>\n";
			$ok = 0;
			$complete = 0;
		}
	}
	
	$points = $vtmglobal['settings']['freebies-points'];
	
	$spends = vtm_get_freebies_spent();
	$spent = $spends["spent"];
	$gained = $spends["gained"];
	
	if ($spent == 0 && $gained == 0) {
		$errormessages .= "<li>WARNING: You have not spent any points</li>\n";
		$complete = 0;
	}
	elseif ($spent > $points + $gained) {
		$errormessages .= "<li>ERROR: You have spent too many points</li>\n";
		$ok = 0;
		$complete = 0;
	}
	elseif ($spent < $points + $gained) {
		$errormessages .= "<li>WARNING: You haven't spent enough points</li>\n";
		$complete = 0;
	}
	
	// IMPLEMENT THIS CHECK BEFORE SUBMITTING
	// CHECK WILL ALWAYS PASS WHEN SPENDING FREEBIES
	// AS YOU LITERALLY CANNOT SELECT TOO HIGH A PATH RATING
	
	// if (count($postpath) > 0) {
		// $results = vtm_get_chargen_itemlist('PATH');
		// $primarypaths = vtm_get_chargen_paths(0);
		// print_r($primarypaths);
		// $pathinfo = array();
		// foreach ($results as $path) {
			// $pathinfo[sanitize_key($path['ITEMNAME'])] = $path;
		// }
		// $discinfo = vtm_get_chargen_saved('DISCIPLINE');
		// //print_r($pathinfo);
		
		// $bought = $postpath;
		// foreach ($bought as $path => $level) {
			// $disciplinekey = sanitize_key($pathinfo[$path]['GROUPING']);
			// $pathid = $pathinfo[$path]['ITEMTABLE_ID'];
			// $primarypathid = 0;
			// $disciplineid = 0;
			// $primarypathlevel = 0;
			// $primarypathname = "";
			// foreach ($primarypaths as $ppath) {
				// if ($disciplinekey == sanitize_key($ppath->discipline)) {
					// $primarypathid = $ppath->pathid;
					// $disciplineid = $ppath->discid;
					// $primarypathlevel = $ppath->path_level;
					// $primarypathname = $ppath->name;
				// }
			// }
			// //if (isset($postdisc[$disciplinekey]) && $postdisc[$disciplinekey] > $primarypathlevel)
			// //	$primarypathlevel = $postdisc[$disciplinekey];
			
			// if ($pathid == $primarypathid) {
				// echo "<li>Primary {$pathinfo[$path]['GROUPING']} path {$pathinfo[$path]['ITEMNAME']} - no checks</li>";
			// } else {
				// // Check that the primary path must be at least
				// // 1 higher than the selected path... unless the
				// // primary path is 5
				// echo "<li>Check level $level of {$pathinfo[$path]['GROUPING']} path {$pathinfo[$path]['ITEMNAME']} against primary path $primarypathname level $primarypathlevel</li>";
			// }
			
			// // MAX level you can buy is the level of the discipline
			// // which you might have also bought up with freebie points

			// // if (isset($discinfo[$disciplinekey]->level_from))
				// // $max = $discinfo[$disciplinekey]->level_from;
			// // else
				// // $max = 0;
			// // if (isset($postdisc[$disciplinekey]) && $postdisc[$disciplinekey] > $max)
				// // $max = $postdisc[$disciplinekey];
		
			// // if ($level > $max) {
				// // $errormessages .= "<li>ERROR: The level in " . vtm_formatOutput($pathinfo[$path]['ITEMNAME']) . " ($level) cannot be greater than the {$pathinfo[$path]['GROUPING']} rating ($max)</li>\n";
				// // $ok = 0;
				// // $complete = 0;
			// // }
		// }
	// }

	return array($ok, $errormessages, $complete);
}

function vtm_validate_rituals($usepost = 1) {
	global $vtmglobal;

	$ok = 1;
	$errormessages = "";
	$complete = 1;

	$rituals = vtm_get_chargen_rituals();
	$target = vtm_get_chargen_ritual_points($rituals);
	//print_r($target);
	
	if (!$usepost) {
		$dbvalues = array();
		$dbgroups = array();
		$dball = array();
		
		$items = vtm_get_chargen_saved('RITUAL');
		$discipline = "";
		foreach ($items as $key => $item) {
			$dbvalues[$key] = $item->level_from;
			
			if ($discipline != $item->grp) {
				$dbgroups[] = sanitize_key($item->grp);
				$discipline = $item->grp;
			}
		}
		//print_r($dbvalues);
		//print_r($dbgroups);
	}
	
	$postvalues = $usepost ? 
				(isset($_POST['ritual_value']) ? $_POST['ritual_value'] : array()) :
				$dbvalues;
	$postgroups = $usepost ? 
				(isset($_POST['group']) ? $_POST['group'] : array()) :
				$dbgroups;
	$postall    = $usepost ? 
				(isset($_POST) ? $_POST : array()) :
				$dball;
	
	if (count($postvalues) > 0) {
		$values = $postvalues;
		$groups = $postgroups;
		$check = 0;
		
		foreach ($groups as $group) {
			$disctotal = 0;
			$groupname = "";
			foreach ($rituals as $ritual) {
				$key = sanitize_key($ritual['ITEMNAME']);
				if (sanitize_key($ritual['GROUPING']) == $group) {
					$disctotal += isset($values[$key]) ? max(0,$values[$key]) : 0;
					$groupname = $ritual['GROUPING'];
				}
			}
			
			if (isset($target[$group])) {
				if ($disctotal > $target[$group]) {
					$errormessages .= "<li>ERROR: You have spent too many points on $groupname Rituals</li>\n";
					$ok = 0;
					$complete = 0;
				}
				elseif ($disctotal < $target[$group])  {
					$errormessages .= "<li>WARNING: You haven't spent enough points on $groupname Rituals</li>\n";
					$complete = 0;
				}
			}
			elseif ($disctotal > 0) {
					$errormessages .= "<li>ERROR: You have spent points on $groupname Rituals but you don't have the discipline</li>\n";
					$ok = 0;
					$complete = 0;
			}
		}
	
	} else {
		$errormessages .= "<li>WARNING: You have not spent any points of rituals</li>\n";
		$complete = 0;
	}

	return array($ok, $errormessages, $complete);
}

function vtm_validate_finishing($usepost = 1) {
	global $wpdb;
	global $vtmglobal;

	$ok = 1;
	$errormessages = "";
	$complete = 1;
	
	if (!$usepost) {
		$dbvalues = array();
		$dbcomments = array();
		
		$specialities = vtm_get_chargen_specialties();
		foreach ($specialities as $spec) {
			$dbvalues[]   = $spec['itemname'];
			$dbcomments[] = $spec['spec'];
		}
		
		$dob = $wpdb->get_var($wpdb->prepare("SELECT DATE_OF_BIRTH FROM " . VTM_TABLE_PREFIX . "CHARACTER WHERE ID = %s", $vtmglobal['characterID']));
		$dob_array = explode('-',$dob);
		$dbday_dob   = isset($_POST['day_dob'])   ? $_POST['day_dob']   : (isset($dob) ? strftime("%d", strtotime($dob)) : '');
		$dbmonth_dob = isset($_POST['month_dob']) ? $_POST['month_dob'] : (isset($dob) ? strftime("%m", strtotime($dob)) : '');
		$dbyear_dob  = isset($_POST['year_dob'])  ? $_POST['year_dob']  : (isset($dob) ? $dob_array[0] : '0000');
		
		$doe = $wpdb->get_var($wpdb->prepare("SELECT DATE_OF_EMBRACE FROM " . VTM_TABLE_PREFIX . "CHARACTER WHERE ID = %s", $vtmglobal['characterID']));
		$doe_array = explode('-',$doe);
		$dbday_doe   = isset($_POST['day_doe'])   ? $_POST['day_doe']   : (isset($doe) ? strftime("%d", strtotime($doe)) : '');
		$dbmonth_doe = isset($_POST['month_doe']) ? $_POST['month_doe'] : (isset($doe) ? strftime("%m", strtotime($doe)) : '');
		$dbyear_doe  = isset($_POST['year_doe'])  ? $_POST['year_doe']  : (isset($dob) ? $doe_array[0] : '0000');
		
		$dbsire = $wpdb->get_var($wpdb->prepare("SELECT SIRE FROM " . VTM_TABLE_PREFIX . "CHARACTER WHERE ID = %s", $vtmglobal['characterID']));
	}
	
	//print_r($_POST);
	
	$postvalues = $usepost ? 
				(isset($_POST['fullname']) ? $_POST['fullname'] : array()) :
				$dbvalues;
	$postcomments = $usepost ? 
				(isset($_POST['comment']) ? $_POST['comment'] : array()) :
				$dbcomments;
	$postsire      = $usepost ? (isset($_POST['sire'])      ? $_POST['sire']      : '')     : $dbsire;
	$postday_dob   = $usepost ? (isset($_POST['day_dob'])   ? $_POST['day_dob']   : '')     : $dbday_dob;
	$postmonth_dob = $usepost ? (isset($_POST['month_dob']) ? $_POST['month_dob'] : '')     : $dbmonth_dob;
	$postyear_dob  = $usepost ? (isset($_POST['year_dob'])  ? $_POST['year_dob']  : '0000') : $dbyear_dob;
	$postday_doe   = $usepost ? (isset($_POST['day_doe'])   ? $_POST['day_doe']   : '')     : $dbday_doe;
	$postmonth_doe = $usepost ? (isset($_POST['month_doe']) ? $_POST['month_doe'] : '')     : $dbmonth_doe;
	$postyear_doe  = $usepost ? (isset($_POST['year_doe'])  ? $_POST['year_doe']  : '0000') : $dbyear_doe;

	// All specialities are entered
	// Sire name is entered
	// Dates are not the default dates
	
	if (count($postvalues) > 0) {
		foreach ($postvalues as $index => $name) {
		//print "<li>Speciality for $index/$name is $postcomments[$index]</li>";
			if (!isset($postcomments[$index]) || $postcomments[$index] == '') {
				$errormessages .= "<li>WARNING: Please specify a speciality for $name</li>\n";
				$complete = 0;
			}
		}
	}
	if ($postsire == '') {
		$errormessages .= "<li>WARNING: Please enter the name of your sire, or enter 'unknown' if your character does not know.</li>\n";
		$complete = 0;
}
	if ($postday_dob == 0 || $postmonth_dob == 0 || $postyear_dob == '0000') {
		$errormessages .= "<li>WARNING: Please enter your character's Date of Birth.</li>\n";
		$complete = 0;
	}
	if ($postday_doe == 0 || $postmonth_doe == 0 || $postyear_doe == '0000') {
		$errormessages .= "<li>WARNING: Please enter your character's Date of Embrace.</li>\n";
		$complete = 0;
	}
	if ($postyear_dob > date("Y") * 1) {
		$errormessages .= "<li>ERROR: Your character's Date of Birth cannot be in the future.</li>\n";
		$ok = 0;
		$complete = 0;
	}
	if ($postyear_doe > date("Y") * 1) {
		$errormessages .= "<li>ERROR: Your character's Date of Embrace cannot be in the future.</li>\n";
		$ok = 0;
		$complete = 0;
	}
	if ($postyear_dob == '') {
		$errormessages .= "<li>ERROR: Please enter your character's Date of Birth.</li>\n";
		$ok = 0;
		$complete = 0;
	}
	if ($postyear_doe == '') {
		$errormessages .= "<li>ERROR: Please enter your character's Date of Embrace.</li>\n";
		$ok = 0;
		$complete = 0;
	}
	if ($postyear_dob != floor($postyear_dob)) {
		$errormessages .= "<li>ERROR: Your character's Date of Birth cannot be a decimal number.</li>\n";
		$ok = 0;
		$complete = 0;
	}
	if ($postyear_doe != floor($postyear_doe)) {
		$errormessages .= "<li>ERROR: Your character's Date of Embrace cannot be a decimal number.</li>\n";
		$ok = 0;
		$complete = 0;
	}
	if ($postyear_doe < $postyear_dob) {
		$errormessages .= "<li>ERROR: Your character's Date of Embrace cannot be before their Date of Birth.</li>\n";
		$ok = 0;
		$complete = 0;
	}
	
	return array($ok, $errormessages, $complete);
}

function vtm_validate_history($usepost = 1) {
	global $wpdb;
	global $vtmglobal;

	$ok = 1;
	$errormessages = "";
	$complete = 1;
	
	if (!$usepost) {
		$dbvalues = array();
		$dbtitles = array();
		$dbquestions = vtm_get_chargen_questions();
		foreach ($dbquestions as $question) {
			$dbvalues[] = $question->PENDING_DETAIL;
			$dbtitles[] = $question->TITLE;
		}
		$meritdbvalues = array();
		$meritdbtitles = array();
		$meritquestions = vtm_get_chargen_merit_questions();
		foreach ($meritquestions as $question) {
			$meritdbvalues[] = $question->PENDING_DETAIL;
			
			$title = $question->NAME;
			if (!empty($question->SPECIALISATION)) $title .= $question->SPECIALISATION;
			$title .= " (" . $question->VALUE . ")";
			$meritdbtitles[] = $title;
		}
		$bgdbvalues = array();
		$bgdbtitles = array();
		$bgquestions = vtm_get_chargen_background_questions();
		foreach ($bgquestions as $question) {
			$bgdbvalues[] = $question->PENDING_DETAIL;
			
			$title = $question->NAME . " " . $question->LEVEL;
			if (!empty($question->COMMENT)) $title .= " (" . $question->COMMENT . ")";	

			$bgdbtitles[] = $title;
		}
	} else {
		$dbquestions = array();
		$meritquestions = array();
		$bgquestions = array();
	}
	
	$postvalues = $usepost ? 
				(isset($_POST['question']) ? $_POST['question'] : array()) :
				$dbvalues;
	$posttitles = $usepost ? 
				(isset($_POST['question_title']) ? $_POST['question_title'] : array()) :
				$dbtitles;
	$meritpostvalues = $usepost ? 
				(isset($_POST['meritquestion']) ? $_POST['meritquestion'] : array()) :
				$meritdbvalues;
	$meritposttitles = $usepost ? 
				(isset($_POST['meritquestion_title']) ? $_POST['meritquestion_title'] : array()) :
				$meritdbtitles;
	$bgpostvalues = $usepost ? 
				(isset($_POST['bgquestion']) ? $_POST['bgquestion'] : array()) :
				$bgdbvalues;
	$bgposttitles = $usepost ? 
				(isset($_POST['bgquestion_title']) ? $_POST['bgquestion_title'] : array()) :
				$bgdbtitles;
	// All questions are entered
	
	if (count($postvalues) > 0) {
		foreach ($postvalues as $index => $text) {
			if (!isset($postvalues[$index]) || $postvalues[$index] == '') {
				$errormessages .= "<li>WARNING: Please fill in the '" . vtm_formatOutput($posttitles[$index]) . "' question.</li>\n";
				$complete = 0;
			}
		}
	} elseif (count($dbquestions) > 0) {
		$errormessages .= "<li>WARNING: Please fill in the background questions.</li>\n";
		$complete = 0;
	}
	if (count($meritpostvalues) > 0) {
		foreach ($meritpostvalues as $index => $text) {
			if (!isset($meritpostvalues[$index]) || $meritpostvalues[$index] == '') {
				$errormessages .= "<li>WARNING: Please fill in the '" . vtm_formatOutput($meritposttitles[$index]) . "' Merit/Flaw question.</li>\n";
				$complete = 0;
			}
		}
	} elseif (count($meritquestions) > 0) {
		$errormessages .= "<li>WARNING: Please fill in the Merit/Flaw questions.</li>\n";
		$complete = 0;
	}
	if (count($bgpostvalues) > 0) {
		foreach ($bgpostvalues as $index => $text) {
			if (!isset($bgpostvalues[$index]) || $bgpostvalues[$index] == '') {
				$errormessages .= "<li>WARNING: Please fill in the '" . vtm_formatOutput($bgposttitles[$index]) . "' Background question.</li>\n";
				$complete = 0;
			}
		}
	} elseif (count($bgquestions) > 0) {
		$errormessages .= "<li>WARNING: Please fill in the Background questions.</li>\n";
		$complete = 0;
	}

	return array($ok, $errormessages, $complete);
}

function vtm_validate_xp($usepost = 1) {
	global $vtmglobal;

	$ok = 1;
	$errormessages = "";
	$complete = 1;
	
	if (!$usepost) {
		$dbpath = array();
		$dbdisc = array();
		$items = vtm_get_pending_chargen_xp('PATH');
		foreach ($items as $item) {
			$dbpath[sanitize_key($item->name)] = $item->value;
		}
		$items = vtm_get_pending_chargen_xp('DISCIPLINE');
		foreach ($items as $item) {
			$dbdisc[sanitize_key($item->name)] = $item->value;
		}
	}
	
	$postpath = $usepost ? 
				(isset($_POST['xp_path']) ? $_POST['xp_path'] : array()) :
				$dbpath;
	$postdisc = $usepost ? 
				(isset($_POST['xp_discipline']) ? $_POST['xp_discipline'] : array()) :
				$dbdisc;
	
	// VALIDATE XP POINTS
	//		Not too many points spent
	//		Level of paths bought do not exceed level of discipline
	$spent   = vtm_get_chargen_xp_spent();
	$points  = vtm_get_available_xp($vtmglobal['playerID'], $vtmglobal['characterID']); 
	$remaining = $points - $spent;
	
	if ($remaining < 0) {
		$errormessages .= "<li>ERROR: You have spent too many dots</li>\n";
		$ok = 0;
		$complete = 0;
	}

	if (count($postpath) > 0) {
		$results = vtm_get_chargen_itemlist('PATH');
		$pathinfo = array();
		foreach ($results as $path) {
			$pathinfo[sanitize_key($path['ITEMNAME'])] = $path;
		}
		$discinfo = vtm_get_chargen_saved('DISCIPLINE');
		$pendingfb = vtm_get_pending_freebies('DISCIPLINE');
		
		$bought = $postpath;
		foreach ($bought as $path => $level) {
			$disciplinekey = sanitize_key($pathinfo[$path]['GROUPING']);
			
			// MAX level you can buy is the level of the discipline
			// which you might have also bought up with xp or  points

			$max = 0;
			if (isset($discinfo[$disciplinekey]->level_from))
				$max = $discinfo[$disciplinekey]->level_from;
			if (isset($pendingfb[$disciplinekey]->value) && $pendingfb[$disciplinekey]->value > $max)
				$max = $pendingfb[$disciplinekey]->value;
			if (isset($postdisc[$disciplinekey]) && $postdisc[$disciplinekey] > $max)
				$max = $postdisc[$disciplinekey];
		
			if ($level > $max) {
				$errormessages .= "<li>ERROR: The level in " . vtm_formatOutput($pathinfo[$path]['ITEMNAME']) . " ($level) cannot be greater than the {$pathinfo[$path]['GROUPING']} rating ($max)</li>\n";
				$ok = 0;
				$complete = 0;
			}
		}
	}
	return array($ok, $errormessages, $complete);
}

function vtm_render_date_entry($fieldname, $day, $month, $year, $submitted) {

	if ($submitted) {
		$output = date_i18n(get_option('date_format'),strtotime("$year-$month-$day"));
	} else {
		$output ="
		<fieldset>
		<label>Month</label>
		<select id='month_$fieldname' name='month_$fieldname' >
			<option value='0'>[Select]</option>      
			<option value='01' " . selected('01', $month, false) . ">January</option>      
			<option value='02' " . selected('02', $month, false) . ">February</option>      
			<option value='03' " . selected('03', $month, false) . ">March</option>      
			<option value='04' " . selected('04', $month, false) . ">April</option>      
			<option value='05' " . selected('05', $month, false) . ">May</option>      
			<option value='06' " . selected('06', $month, false) . ">June</option>      
			<option value='07' " . selected('07', $month, false) . ">July</option>      
			<option value='08' " . selected('08', $month, false) . ">August</option>      
			<option value='09' " . selected('09', $month, false) . ">September</option>      
			<option value='10' " . selected('10', $month, false) . ">October</option>      
			<option value='11' " . selected('11', $month, false) . ">November</option>      
			<option value='12' " . selected('12', $month, false) . ">December</option>      
		</select> -
		<label>Day</label>
		<select id='day_$fieldname'  name='day_$fieldname' >
			<option value='0'>[Select]</option>\n";
		for ($i = 1; $i <= 31 ; $i++) {
			$val = sprintf("%02d", $i);
			$output .= "<option value='$val' " . selected($val, $day, false) . ">$i</option>\n";
		}
	  
		$output .= "</select> -
		<label>Year</label>
		<input type='text' name='year_$fieldname' size=5 value='$year' />
		</fieldset>\n";
	}

	return $output;
}
function vtm_get_chargen_specialties() {
	global $wpdb;
	global $vtmglobal;

	// array ( 0 => array (
	//			'updatetable'  => 'CHARACTER_STAT|PENDING_XP_SPEND|PENDING_FREEBIE_SPEND',
	//			'tableid'      => <id of entry of table>
	//			'name'         => <name of stat>
	//		)
	// )
	$specialities = array();
	
	// STATS (from table, freebies or xp)
	$items     = vtm_get_chargen_itemlist('STAT');
	$saved     = vtm_get_chargen_saved('STAT');
	$pendingfb = vtm_get_pending_freebies('STAT');
	$pendingxp = vtm_get_pending_chargen_xp('STAT');  // name => value
	foreach ($items as $item) {
		$key = sanitize_key($item['ITEMNAME']);
		
		$init = array (
				'title'     => 'Attributes',
				'itemname'  => $item['ITEMNAME'],
				'itemid'    => $item['ITEMTABLE_ID'],
				'hasinput'  => 'Y'
		);
		
		if (isset($pendingxp[$key]->specialisation) && $pendingxp[$key]->value >= $item['SPECIALISATION_AT']) {
			$specialities[$key] = $init;
			$specialities[$key]['tablename'] = 'PENDING_XP_SPEND';
			$specialities[$key]['tableid']   = $pendingxp[$key]->id;
			$specialities[$key]['spec']      = $pendingxp[$key]->specialisation;
			$specialities[$key]['level']     = $pendingxp[$key]->value;
		}
		elseif (isset($pendingfb[$key]->specialisation) && $pendingfb[$key]->value >= $item['SPECIALISATION_AT']) {
			$specialities[$key] = $init;
			$specialities[$key]['tablename'] = 'PENDING_FREEBIE_SPEND';
			$specialities[$key]['tableid']   = $pendingfb[$key]->id;
			$specialities[$key]['spec']      = $pendingfb[$key]->specialisation;
			$specialities[$key]['level']     = $pendingfb[$key]->value;
		}
		elseif (isset($saved[$key]->comment) && $saved[$key]->level_from >= $item['SPECIALISATION_AT']) {
			$specialities[$key] = $init;
			$specialities[$key]['tablename'] = 'CHARACTER_STAT';
			$specialities[$key]['tableid']   = $saved[$key]->chartableid;
			$specialities[$key]['spec']      = $saved[$key]->comment;
			$specialities[$key]['level']     = $saved[$key]->level_from;
		}
	}
	
	// SKILL (from table, freebies, xp or free levels)
	$items     = vtm_get_chargen_itemlist('SKILL', "subgroup");
	$saved     = vtm_get_chargen_saved('SKILL');
	$pendingfb = vtm_get_pending_freebies('SKILL');
	$pendingxp = vtm_get_pending_chargen_xp('SKILL');  // name => value
	$free      = vtm_get_free_levels('SKILL');
	foreach ($items as $item) {
		$loop = $item['MULTIPLE'] == 'Y' ? 4 : 1;
		for ($j = 1 ; $j <= $loop ; $j++) {
			$name = sanitize_key($item['ITEMNAME']);
			if ($item['MULTIPLE'] == 'Y') {
				$key = $name . "_" . $j;
				if (isset($free[$key])) {
					$loop++;
				}
			} else {
				$key = $name;
			}
			
			if ($item['SPECIALISATION_AT'] > 0) {
				$init = array (
						'title'     => 'Abilities',
						'itemname'  => $item['ITEMNAME'],
						'itemid'    => $item['ITEMTABLE_ID'],
						'spec'      => '',
						'hasinput'  => 'Y'
				);
			
				if (isset($pendingxp[$key]) && $pendingxp[$key]->value >= $item['SPECIALISATION_AT']) {
					$specialities[$key] = $init;
					$specialities[$key]['tablename'] = 'PENDING_XP_SPEND';
					$specialities[$key]['tableid']   = $pendingxp[$key]->id;
					$specialities[$key]['spec']      = $pendingxp[$key]->specialisation;
					$specialities[$key]['level']     = $pendingxp[$key]->value;
				}
				elseif (isset($pendingfb[$key]) && $pendingfb[$key]->value >= $item['SPECIALISATION_AT']) {
					$specialities[$key] = $init;
					$specialities[$key]['tablename'] = 'PENDING_FREEBIE_SPEND';
					$specialities[$key]['tableid']   = $pendingfb[$key]->id;
					$specialities[$key]['spec']      = $pendingfb[$key]->specialisation;
					$specialities[$key]['level']     = $pendingfb[$key]->value;
				}
				elseif (isset($saved[$key]) && $saved[$key]->level_from >= $item['SPECIALISATION_AT']) {
					$specialities[$key] = $init;
					$specialities[$key]['tablename'] = 'CHARACTER_SKILL';
					$specialities[$key]['tableid']   = $saved[$key]->chartableid;
					$specialities[$key]['spec']      = $saved[$key]->comment;
					$specialities[$key]['level']     = $saved[$key]->level_from;
				}
				elseif (isset($free[$key]) && $item['SPECIALISATION_AT'] != 0) {
					$specialities[$key] = $init;
					$specialities[$key]['tablename'] = $free[$key]->ITEMTABLE;
					$specialities[$key]['tableid']   = $free[$key]->ITEMTABLE_ID;
					$specialities[$key]['spec']      = $free[$key]->SPECIALISATION;
					$specialities[$key]['level']     = $free[$key]->LEVEL;
				}

				if (isset($specialities[$key]) && isset($free[$key]->SPECIALISATION) && $free[$key]->SPECIALISATION != '') {
					$specialities[$key]['spec']      = $free[$key]->SPECIALISATION;
					$specialities[$key]['hasinput']  = 'N';
				}
			}
			
		}
	}

	// BACKGROUND (from table, freebies, xp or free levels)
	$items     = vtm_get_chargen_itemlist('BACKGROUND');
	$saved     = vtm_get_chargen_saved('BACKGROUND');
	$pendingfb = vtm_get_pending_freebies('BACKGROUND');
	$free      = vtm_get_free_levels('BACKGROUND');
	foreach ($items as $item) {
		$key = sanitize_key($item['ITEMNAME']);
		if ($item['SPECIALISATION_AT'] == 'Y' || isset($free[$key])) {
			$init = array (
					'title'     => 'Backgrounds',
					'itemname'  => $item['ITEMNAME'],
					'itemid'    => $item['ITEMTABLE_ID'],
					'spec'      => '',
					'hasinput'  => 'Y'
			);
			
			if (isset($pendingfb[$key]) && $item['SPECIALISATION_AT'] == 'Y') {
				$specialities[$key] = $init;
				$specialities[$key]['tablename'] = 'PENDING_FREEBIE_SPEND';
				$specialities[$key]['tableid']   = $pendingfb[$key]->id;
				$specialities[$key]['spec']      = $pendingfb[$key]->specialisation;
				$specialities[$key]['level']     = $pendingfb[$key]->value;
			}
			elseif (isset($saved[$key]) && $item['SPECIALISATION_AT'] == 'Y') {
				$specialities[$key] = $init;
				$specialities[$key]['tablename'] = 'CHARACTER_BACKGROUND';
				$specialities[$key]['tableid']   = $saved[$key]->chartableid;
				$specialities[$key]['spec']      = $saved[$key]->comment;
				$specialities[$key]['level']     = $saved[$key]->level_from;
			}
			elseif (isset($free[$key]) && $item['SPECIALISATION_AT'] == 'Y') {
				$specialities[$key] = $init;
				$specialities[$key]['tablename'] = $free[$key]->ITEMTABLE;
				$specialities[$key]['tableid']   = $free[$key]->ITEMTABLE_ID;
				$specialities[$key]['spec']      = $free[$key]->SPECIALISATION;
				$specialities[$key]['level']     = $free[$key]->LEVEL;
			}

			if (isset($specialities[$key]) && isset($free[$key]->SPECIALISATION) && $free[$key]->SPECIALISATION != '') {
				$specialities[$key]['spec']      = $free[$key]->SPECIALISATION;
				$specialities[$key]['hasinput']  = 'N';
			}
		}
	}

	// MERITS
	$items     = vtm_get_chargen_itemlist('MERIT');
	$saved     = vtm_get_chargen_saved('MERIT');
	$pendingfb = vtm_get_pending_freebies('MERIT');
	$pendingxp = vtm_get_pending_chargen_xp('MERIT');  // name => value
	foreach ($items as $item) {
		$loop = $item['MULTIPLE'] == 'Y' ? 4 : 1;
		for ($j = 1 ; $j <= $loop ; $j++) {
			$name = sanitize_key($item['ITEMNAME']);
			if ($item['MULTIPLE'] == 'Y') {
				$key = $name . "_" . $j;
				if (isset($free[$key])) {
					$loop++;
				}
			} else {
				$key = $name;
			}
			
			if ($item['SPECIALISATION_AT'] == 'Y') {
				$init = array (
						'title'     => 'Merits/Flaws',
						'itemname'  => $item['ITEMNAME'],
						'itemid'    => $item['ITEMTABLE_ID'],
						'spec'      => '',
						'level'     => $item['LEVEL'],
						'hasinput'  => 'Y'
				);
			
				if (isset($pendingxp[$key])) {
					$specialities[$key] = $init;
					$specialities[$key]['tablename'] = 'PENDING_XP_SPEND';
					$specialities[$key]['tableid']   = $pendingxp[$key]->id;
					$specialities[$key]['spec']      = $pendingxp[$key]->specialisation;
				}
				elseif (isset($pendingfb[$key])) {
					$specialities[$key] = $init;
					$specialities[$key]['tablename'] = 'PENDING_FREEBIE_SPEND';
					$specialities[$key]['tableid']   = $pendingfb[$key]->id;
					$specialities[$key]['spec']      = $pendingfb[$key]->specialisation;
				}
				elseif (isset($saved[$key])) {
					$specialities[$key] = $init;
					$specialities[$key]['tablename'] = 'CHARACTER_MERIT';
					$specialities[$key]['tableid']   = $saved[$key]->chartableid;
					$specialities[$key]['spec']      = $saved[$key]->comment;
				}
			}
		}
	}

	return $specialities;
} 
function vtm_get_chargen_questions($returncount = false) {
	global $wpdb;
	global $vtmglobal;

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
			WHERE characters.ID = %d
				AND questions.VISIBLE = 'Y'
				AND questions.REQD_AT_CHARGEN = 'Y'
			ORDER BY questions.ORDERING ASC";
			
	//$content = "<p>SQL: $sql</p>\n";
	
	$questions = $wpdb->get_results($wpdb->prepare($sql, $vtmglobal['characterID'], $vtmglobal['characterID']));
	
	return $returncount ? $wpdb->num_rows : $questions;

	return 0;

}

function vtm_get_chargen_merit_questions() {
	global $wpdb;
	global $vtmglobal;
	
	$sql = "SELECT fb.ID,
				merits.NAME, merits.BACKGROUND_QUESTION, fb.SPECIALISATION,
				fb.PENDING_DETAIL, merits.VALUE
			FROM
				" . VTM_TABLE_PREFIX . "PENDING_FREEBIE_SPEND fb,
				" . VTM_TABLE_PREFIX . "MERIT merits
			WHERE
				fb.CHARACTER_ID = %s
				AND fb.ITEMTABLE = 'MERIT'
				AND fb.ITEMTABLE_ID = merits.ID
				AND merits.BACKGROUND_QUESTION != ''";
	$sql = $wpdb->prepare($sql, $vtmglobal['characterID']);
	$questions = $wpdb->get_results($sql);
	
	return $questions;
}
function vtm_get_chargen_background_questions() {
	global $wpdb;
	global $vtmglobal;	
	
	$sql = "(SELECT cbg.ID, 'CHARACTER_BACKGROUND' as source,
				bg.NAME, bg.BACKGROUND_QUESTION, cbg.COMMENT,
				cbg.PENDING_DETAIL, 
				cbg.LEVEL
			FROM
				" . VTM_TABLE_PREFIX . "CHARACTER_BACKGROUND cbg
				LEFT JOIN (
					SELECT ID, CHARTABLE_ID, LEVEL_TO
					FROM " . VTM_TABLE_PREFIX . "PENDING_FREEBIE_SPEND
					WHERE CHARACTER_ID = %s 
						AND CHARTABLE = 'CHARACTER_BACKGROUND'
				) fb
				ON
					fb.CHARTABLE_ID = cbg.ID,
				" . VTM_TABLE_PREFIX . "BACKGROUND bg
			WHERE
				cbg.CHARACTER_ID = %s
				AND cbg.BACKGROUND_ID = bg.ID
				AND bg.BACKGROUND_QUESTION != ''
				AND ISNULL(fb.ID))
			UNION
			(SELECT fb.ID, 'PENDING_FREEBIE_SPEND' as source,
				bg.NAME, bg.BACKGROUND_QUESTION, fb.SPECIALISATION as COMMENT,
				fb.PENDING_DETAIL, fb.LEVEL_TO as LEVEL
			FROM
				" . VTM_TABLE_PREFIX . "PENDING_FREEBIE_SPEND fb,
				" . VTM_TABLE_PREFIX . "BACKGROUND bg
			WHERE
				fb.CHARACTER_ID = %s
				AND fb.ITEMTABLE_ID = bg.ID
				AND fb.ITEMTABLE = 'BACKGROUND'
				AND bg.BACKGROUND_QUESTION != ''
			)";
	$sql = $wpdb->prepare($sql, $vtmglobal['characterID'], $vtmglobal['characterID'], $vtmglobal['characterID']);
	$questions = $wpdb->get_results($sql);
	
	//echo "<p>SQL: $sql</p>\n";
	//print_r($questions);
	
	return $questions;
}

function vtm_validate_submit($usepost = 1) {

	if (isset($_POST['chargen-submit'])) {
		if ($_POST['status'] == 1)
			
			return array(1, "Character has been submitted", 1);
		else
			return array(0, "<LI>ERROR: Complete your character before submitting</li>", 0);
	} else {
		return array(1, "", 0);
	}
	
}
function vtm_save_submit() {
	global $wpdb;
	global $vtmglobal;

	$wpdb->show_errors();
	
	// Exit if we aren't actually submitting the character
	if (!isset($_POST['chargen-submit']) || !isset($_POST['status']) || $_POST['status'] != 1) {
		return $vtmglobal['characterID'];
	}

	// Update Character Generation Status
	$submittedid = $wpdb->get_var("SELECT ID FROM " . VTM_TABLE_PREFIX . "CHARGEN_STATUS WHERE NAME = 'Submitted'");
	
	$result = $wpdb->update(VTM_TABLE_PREFIX . "CHARACTER",
				array ('CHARGEN_STATUS_ID' => $submittedid),
				array ('ID' => $vtmglobal['characterID'])
			);
	
	// Send Email to storytellers
	if (!$result && $result !== 0) {
		echo "<p>ERROR: Submission of character failed. Contact the webadmin with your character name</p>\n";
	} else {
		
		$result = $wpdb->update(VTM_TABLE_PREFIX . "CHARACTER_GENERATION",
				array ('NOTE_FROM_ST' => ''),
				array ('ID' => $vtmglobal['characterID'])
		);
	
		if (is_user_logged_in()) {
			$current_user = wp_get_current_user();
			$userid       = $current_user->ID;
		} else {
			$userid = 0;
		}
		
		$sql = "SELECT c.NAME as name, c.EMAIL as email, c.CONCEPT as concept,
					p.NAME as player, p.ID as playerID, c.PRIVATE_CLAN_ID as clanid
				FROM " . VTM_TABLE_PREFIX . "CHARACTER c,
					" . VTM_TABLE_PREFIX . "PLAYER p
				WHERE c.ID = %s
					AND c.PLAYER_ID = p.ID";
		$results = $wpdb->get_row($wpdb->prepare($sql, $vtmglobal['characterID']));
		
		$playerid = $results->playerID;
		$ref = vtm_get_chargen_reference();
		$toaddr = get_option( 'vtm_replyto_address', get_option( 'vtm_chargen_email_from_address', get_bloginfo('admin_email') ) );
		$character = stripslashes($results->name);
		$concept = $results->concept;
		$clan = stripslashes(vtm_get_clan_name($results->clanid));
		$url    = add_query_arg('reference', $ref, vtm_get_stlink_url('viewCharGen', true));
		
		
		$body = "<p>Hello Storytellers,</p>
		
		<p>A new character has been submitted:</p>
		<ul>
		<li><strong>Reference</strong>: $ref</li>
		<li><strong>Character Name</strong>: $character</li>
		<li><strong>Player</strong>: " . stripslashes(vtm_get_player_name($playerid)) . "</li>
		<li><strong>Clan</strong>: $clan</li>
		</ul>
	
		<p><strong>Concept</strong>: <br>
	
		<p>" . stripslashes($concept) . "</p>
	
		<p>You can view this character by following this link: $url</p>";
	
		//echo "<pre>$body</pre>\n";

		$result = vtm_send_email($toaddr, "Character Submitted", $body);
		
		if (!$result)
			echo "<p>Failed to send email. Character Ref: $ref</p>\n";

	}

	// Update character gen status global variable
	$vtmglobal['charGenStatus'] = vtm_get_chargen_status();
	
	return $vtmglobal['characterID'];
}
function vtm_validate_dummy($usepost = 1) {
	return array(1, "", 1);

}
function vtm_save_dummy() {
	global $vtmglobal;
	return $vtmglobal['characterID'];
}


function vtm_get_chargen_ritual_points($items) {
	global $wpdb;
	global $vtmglobal;
	
	$points = array();
	
	foreach ($items as $ritual) {
	
		$key = sanitize_key($ritual['GROUPING']);
	
		if ($vtmglobal['settings']['rituals-method'] == 'none') {
			$points[$key] = 0;
		}
		elseif ($vtmglobal['settings']['rituals-method'] == 'point') {
			//echo "<li>point - {$vtmglobal['settings']['rituals-points']}</li>";
			$points[$key] = $vtmglobal['settings']['rituals-points'];
		}
		elseif ($vtmglobal['settings']['rituals-method'] == 'discipline') {
			//echo "<li>discipline - {$ritual->discipline_level}</li>";
			$points[$key] = $ritual['LEVEL'];
		}
		else {
			//echo "<li>accumulate</li>";
			$points[$key] = 0;
			for ($i = $ritual['LEVEL'] ; $i >= 1 ; $i--)
				$points[$key] += $i;
		}
	}
	
	return $points;
}

function vtm_get_available_xp($playerID, $characterID) {
	global $wpdb;
	global $vtmglobal;
		
	// total from PLAYER_XP table
	$total = vtm_get_total_xp($vtmglobal['playerID'], $characterID);
	
	// Pending on all other characters
	if ($vtmglobal['config']->ASSIGN_XP_BY_PLAYER == 'Y') {
		$sql = "SELECT SUM(AMOUNT)
			FROM
				" . VTM_TABLE_PREFIX . "PENDING_XP_SPEND
			WHERE
				CHARACTER_ID != %s
				AND PLAYER_ID = %s";
		$sql = $wpdb->prepare($sql, $characterID, $playerID);
		$pending = $wpdb->get_var($sql) * -1;
	
	} else {
		$pending = 0;
	}
	return $total - $pending;
}

function vtm_has_submitted_disc_with_paths() {
	
	$disciplines = vtm_get_magic_disciplines(1,1);
	$result = 0;
	
	//print_r($disciplines);
	
	if (isset($_REQUEST['discipline_value'])) {
		foreach ($_REQUEST['discipline_value'] as $name => $level) {
			if (isset($disciplines[sanitize_key($name)]) && $level > 0) {
				$result = 1;
			}
			
		}
	}
	
	if (isset($_REQUEST['freebie_discipline'])) {
		foreach ($_REQUEST['freebie_discipline'] as $name => $level) {
			if (isset($disciplines[sanitize_key($name)]) && $level > 0) {
				$result = 1;
			}
		}
	}
	
	if (isset($_REQUEST['xp_discipline'])) {
		foreach ($_REQUEST['xp_discipline'] as $name => $level) {
			if (isset($disciplines[sanitize_key($name)]) && $level > 0) {
				$result = 1;
			}
		}
	}
		
	return $result;
	
}

function vtm_get_chargen_status() {
	global $vtmglobal;
	global $wpdb;
	
	$sql = $wpdb->prepare("SELECT cgs.NAME FROM " . VTM_TABLE_PREFIX . "CHARACTER c, " . VTM_TABLE_PREFIX . "CHARGEN_STATUS cgs WHERE c.ID = %s AND c.CHARGEN_STATUS_ID = cgs.ID",$vtmglobal['characterID']);
	return $wpdb->get_var($sql);
	
}

function vtm_validate_template($usepost = 1) {
	global $vtmglobal;
	
	$ok = 1;
	$errormessages = "";
	$complete = 1;
	
	if (!$usepost) {
		$template_values = '';
		$ref_values = '';
		$email_values = false;
	}
	
	$template     = $usepost ? (isset($_POST['chargen_template'])  ? $_POST['chargen_template'] : '') : $template_values;	
	$reference    = $usepost ? (isset($_POST['chargen_reference']) ? $_POST['chargen_reference'] : '') : $ref_values;	
	$emailconfirm = $usepost ? isset($_GET['confirm']) : $email_values;
		
	if (empty($template) && empty($reference)) {
		$errormessages .= "<p>Select a template or enter a character generation reference number.</p>";
	}
	
	if ($vtmglobal['characterID'] == -1) {
		$errormessages .= "<div class='vtm_error'><p>Invalid Reference</p>";
		if ($reference != '') {
			$split = explode("/",$$reference);
			if ($split[3] != '0000') {
				$errormessages .= "<p>Check that you are logged
				in under the same account that you originally created the character under.</p>";
			}
		}
		$errormessages .= "</div>\n";
		$ok = 0;
		$complete=0;
	} 
	
	
	return array($ok, $errormessages, $complete);
	
}

function vtm_save_template() {
	global $wpdb;
	global $vtmglobal;
	
	$emailconfirm = isset($_GET['confirm']);
	
	if ($emailconfirm) {
		$split = explode("/",$_GET['reference']);
		$chid = $split[0] * 1;
		$result = $wpdb->update(VTM_TABLE_PREFIX . "CHARACTER_GENERATION",
				array('EMAIL_CONFIRMED' => 'Y'),
				array('CHARACTER_ID' => $chid)
			);
	
		if ($result) 
			echo "<p style='color:green'>Email address confirmed</p>\n";
		else if ($result !== 0) {
			$wpdb->print_error();
			echo "<p style='color:red'>Could not confirm email address</p>\n";
		}
	}
	
	return $vtmglobal['characterID'];
	
}

?>