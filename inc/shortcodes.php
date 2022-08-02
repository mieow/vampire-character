<?php


function vtm_get_shortcode_id($base) {
	static $shortcode_id;
	$shortcode_id++;
	return $base . "_" . $shortcode_id;
}

function vtm_get_homedomain() {
	global $vtmglobal;
	global $wpdb;
	
	$sql = "SELECT ID, NAME FROM " . VTM_TABLE_PREFIX . "DOMAIN
			WHERE ID = %s;";
	$list = $wpdb->get_row($wpdb->prepare($sql, $vtmglobal['config']->HOME_DOMAIN_ID));
	
	//echo "<li>Home domain: ({$list->ID}) {$list->NAME}</li>";
	
	return $list->NAME;
}
function vtm_get_loggedinclan($characterID) {
	global $wpdb;

	$sql = "SELECT pubclan.name as pub, privclan.name as priv
			FROM 
				" . VTM_TABLE_PREFIX . "CHARACTER chara,
				" . VTM_TABLE_PREFIX . "CLAN pubclan,
				" . VTM_TABLE_PREFIX . "CLAN privclan
			WHERE 
				chara.ID = %s
				AND chara.PUBLIC_CLAN_ID = pubclan.ID
				AND chara.PRIVATE_CLAN_ID = privclan.ID";
	$result = $wpdb->get_results($wpdb->prepare($sql, $characterID));
		
	if (vtm_count($result) == 0) {
		$result[0] = new stdClass();
		$result[0]->priv = '';
		$result[0]->pub = '';
	}
	
	return $result;
}
function vtm_get_loggedinsect($characterID) {
	global $wpdb;

	$sql = "SELECT sect.name
			FROM 
				" . VTM_TABLE_PREFIX . "CHARACTER chara,
				" . VTM_TABLE_PREFIX . "SECT sect
			WHERE 
				chara.ID = %s
				AND chara.SECT_ID = sect.ID";
	$sql = $wpdb->prepare($sql, $characterID);
	//echo "<p>SQL: $sql</p>";
	$result = $wpdb->get_var($sql);
	
	return $result;
}
function vtm_get_loggedindomain($characterID) {
	global $wpdb;

	$sql = "SELECT domains.name as domain
			FROM 
				" . VTM_TABLE_PREFIX . "CHARACTER chara,
				" . VTM_TABLE_PREFIX . "DOMAIN domains
			WHERE 
				chara.ID = %s
				AND domains.ID = chara.DOMAIN_ID";
	$result = $wpdb->get_results($wpdb->prepare($sql, $characterID));
	
	return $result[0]->domain;
}

function vtm_get_profilelink($wordpressid, $character) {
	$markup = '<a href="@PROFILELINK@?CHARACTER=@WORDPRESS@" @EXTRA@>@NAME@</a>';
	$link = str_replace(
		Array('@PROFILELINK@','@WORDPRESS@','@EXTRA@','@NAME@'),
			Array(vtm_get_stlink_url('viewProfile'), urlencode($wordpressid), "",vtm_formatOutput($character)),
			$markup
		);
	$link = vtm_pm_link($link, array('character' => $wordpressid));
	return $link;
}

function vtm_print_background_shortcode($atts, $content = null) {
	global $wpdb;
	
	$output = "";

	extract(shortcode_atts(array (
		"character" => "null",
		"background" => "Status",
		"matchtype"  => "",
		"match"      => "",
		"domain"     => "home",
		"liststatus" => "Alive",
		"level"      => "all",
		"columns"    => "level,character,player,clan,domain,background,sector,office,comment,level",  // also sect
		"heading"    => 1,
		"charactertype" => 'all',
		"pronouns"   => 1,
		), $atts)
	);
	
	/* Match comment in background to: 
		sector					- matchtype = sector
		comment (e.g. sector)	- matchtype = comment
		sect                    - matchtype = characteristic or comment
		
		match = <value> or loggedinclan or loggedinsect
		
		domain = "" or <value> or loggedin or home
		
		level = "all" or "displayzeros" or <number>
	*/

	$character = vtm_establishCharacter($character);
	$characterID = vtm_establishCharacterID($character);
		
	$sqlmain = "SELECT chara.id,
				chara.wordpress_id,
				chara.name as charname,
				pubclan.name as publicclan,
				privclan.name as privateclan,
				player.name as playername,
				pstatus.name as pstat,
				cstatus.name as cstat,
				background.name as bgname,
				char_bg.level as level,
				char_bg.comment as comment,
				domains.name as domain,
				sector.name as sectorname,
				cgstatus.name as chargenstat,
				sects.name as sect,
				ctype.name as charactertype,
				chara.pronouns as pronouns
			FROM
				" . VTM_TABLE_PREFIX . "CHARACTER chara,
				" . VTM_TABLE_PREFIX . "PLAYER player,
				" . VTM_TABLE_PREFIX . "PLAYER_STATUS pstatus,
				" . VTM_TABLE_PREFIX . "CHARACTER_STATUS cstatus,
				" . VTM_TABLE_PREFIX . "CHARACTER_TYPE ctype,
				" . VTM_TABLE_PREFIX . "CLAN pubclan,
				" . VTM_TABLE_PREFIX . "CLAN privclan,
				" . VTM_TABLE_PREFIX . "BACKGROUND background,
				" . VTM_TABLE_PREFIX . "CHARGEN_STATUS cgstatus,
				" . VTM_TABLE_PREFIX . "SECT sects,
				" . VTM_TABLE_PREFIX . "CHARACTER_BACKGROUND char_bg
				LEFT JOIN
					" . VTM_TABLE_PREFIX . "SECTOR sector
				ON
					char_bg.SECTOR_ID = sector.ID
				,
				" . VTM_TABLE_PREFIX . "DOMAIN domains
			WHERE
				chara.PLAYER_ID = player.ID
				AND chara.ID = char_bg.CHARACTER_ID
                AND player.PLAYER_STATUS_ID = pstatus.ID
				AND pstatus.NAME = 'Active'
				AND chara.CHARACTER_STATUS_ID = cstatus.ID
				AND chara.CHARACTER_TYPE_ID = ctype.ID
				AND chara.PUBLIC_CLAN_ID = pubclan.ID
				AND chara.PRIVATE_CLAN_ID = privclan.ID
				AND background.ID = char_bg.BACKGROUND_ID
				AND domains.ID = chara.DOMAIN_ID
				AND cgstatus.ID = chara.CHARGEN_STATUS_ID
				AND chara.SECT_ID = sects.ID
				AND chara.VISIBLE = 'Y'
				AND chara.DELETED = 'N'
				AND cgstatus.NAME = 'Approved'
				AND background.name = %s";
	$sqlmainargs = array($background);
	
	$sqlzero = "SELECT chara.id,
				chara.wordpress_id,
				chara.name as charname,
				pubclan.name as publicclan,
				privclan.name as privateclan,
				player.name as playername,
				pstatus.name as pstat,
				cstatus.name as cstat,
				%s as bgname,
				0 as level,
				\"\" as comment,
				domains.name as domain,
				\"\" as sectorname,
				cgstatus.name as chargenstat,
				sects.name as sect,
				ctype.name as charactertype,
				chara.pronouns as pronouns
			FROM
				" . VTM_TABLE_PREFIX . "CHARACTER chara
				LEFT JOIN
					(SELECT char_bgs.ID, background.NAME, char_bgs.CHARACTER_ID
					FROM
						" . VTM_TABLE_PREFIX . "CHARACTER_BACKGROUND char_bgs,
						" . VTM_TABLE_PREFIX . "BACKGROUND background
					WHERE
						background.ID = char_bgs.BACKGROUND_ID
						AND background.name = %s
					) char_bg
				ON
					char_bg.CHARACTER_ID = chara.ID
				,
				" . VTM_TABLE_PREFIX . "PLAYER player,
				" . VTM_TABLE_PREFIX . "PLAYER_STATUS pstatus,
				" . VTM_TABLE_PREFIX . "CHARACTER_STATUS cstatus,
				" . VTM_TABLE_PREFIX . "CHARACTER_TYPE ctype,
				" . VTM_TABLE_PREFIX . "CHARGEN_STATUS cgstatus,
				" . VTM_TABLE_PREFIX . "CLAN pubclan,
				" . VTM_TABLE_PREFIX . "CLAN privclan,
				" . VTM_TABLE_PREFIX . "DOMAIN domains,
				" . VTM_TABLE_PREFIX . "SECT sects
			WHERE
				chara.PLAYER_ID = player.ID
                AND player.PLAYER_STATUS_ID = pstatus.ID
				AND pstatus.NAME = 'Active'
				AND chara.CHARACTER_STATUS_ID = cstatus.ID
				AND chara.CHARACTER_TYPE_ID = ctype.ID
				AND chara.PUBLIC_CLAN_ID = pubclan.ID
				AND chara.PRIVATE_CLAN_ID = privclan.ID
				AND domains.ID = chara.DOMAIN_ID
				AND cgstatus.ID = chara.CHARGEN_STATUS_ID
				AND chara.SECT_ID = sects.ID
				AND chara.VISIBLE = 'Y'
				AND chara.DELETED = 'N'
				AND cgstatus.NAME = 'Approved'
				AND ISNULL(char_bg.ID)
				";
	$sqlzeroargs = array($background, $background);
	
	if ($liststatus) {
		$list = explode(',',$liststatus);
		$sqlfilter = " AND ( cstatus.name = '";
		$sqlfilter .= implode("' OR cstatus.name = '",$list);
		$sqlfilter .= "')";
		
		$sqlmain .= $sqlfilter;
		$sqlzero .= $sqlfilter;
	}
	if ($charactertype != 'all') {
		$sqlmain .= " AND ctype.name = %s";
		$sqlzero .= " AND ctype.name = %s";
		array_push($sqlmainargs, $charactertype);
		array_push($sqlzeroargs, $charactertype);
	}
	
	if ($matchtype == 'comment') {
		if ($match == 'loggedinclan') {
			$clans = vtm_get_loggedinclan($characterID);
			$sqlmain .= " AND (char_bg.comment = %s OR char_bg.comment = %s)";
			array_push($sqlmainargs, $clans[0]->priv, $clans[0]->pub);
		} 
		elseif ($match == 'loggedinsect') {
			$sect = vtm_get_loggedinsect($characterID);
			$sqlmain .= " AND char_bg.comment = %s";
			array_push($sqlmainargs, $sect);
		}
		else {
			$sqlmain .= " AND char_bg.comment = %s";
			array_push($sqlmainargs, $match);
		}
	}
	elseif ($matchtype == 'characteristic') {
		if ($match == 'loggedinsect') {
			$sect = vtm_get_loggedinsect($characterID);
			$sqlmain .= " AND sects.name = %s";
			$sqlzero .= " AND sects.name = %s";
			array_push($sqlmainargs, $sect);
			array_push($sqlzeroargs, $sect);
		}
	}
	elseif ($matchtype == 'sector') {
		$sqlmain .= " AND sector.NAME = %s";
		array_push($sqlmainargs, $match);
	}
	
	if ($domain) {
		$sqlfilter = " AND domains.name = %s";
		$sqlmain .= $sqlfilter;
		$sqlzero .= $sqlfilter;
		
		if ($domain == 'loggedin')
			$domain = vtm_get_loggedindomain($characterID);
		if ($domain == 'home')
			$domain = vtm_get_homedomain();
		
		array_push($sqlmainargs, $domain);
		array_push($sqlzeroargs, $domain);
	}
	
	if ($level != "all" && $level != 'displayzeros') {
		$sqlmain .= " AND char_bg.level = %s";
		array_push($sqlmainargs, $level);
	}
	
	$sql = $sqlmain;
	$sqlargs = $sqlmainargs;
	if ($level == 'displayzeros') {
		$sql .= "UNION $sqlzero";
		$sqlargs = array_merge($sqlmainargs, $sqlzeroargs);
	}
	elseif (!$level) {
		$sql = $sqlzero;
		$sqlargs = $sqlzeroargs;
	}
	
	$sql .= " ORDER BY level DESC, charname";

	$sql = $wpdb->prepare($sql, $sqlargs);
	$result = $wpdb->get_results($sql);
	
	//echo "<p>SQL: $sql<p>";
	//print_r($result);
	
	$sqloffices = "SELECT co.CHARACTER_ID, office.NAME as NAME, 
			domain.NAME as DOMAIN
		FROM 
			" . VTM_TABLE_PREFIX . "OFFICE office,
			" . VTM_TABLE_PREFIX . "DOMAIN domain,
			" . VTM_TABLE_PREFIX . "CHARACTER_OFFICE co
		WHERE
			co.OFFICE_ID = office.ID
			AND co.DOMAIN_ID = domain.ID
			AND office.VISIBLE = 'Y'
			AND domain.VISIBLE = 'Y'
		ORDER BY co.CHARACTER_ID, office.ORDERING";
	$temp = $wpdb->get_results($sqloffices);
	//echo "<p>SQL: $sqloffices<p>";
	//print_r($temp);
	$offices = array();
	if (count($temp)) {
		$homedomain = vtm_get_homedomain();
		foreach ($temp as $row) {
			$text = $row->NAME;
			//echo "<li>{$row->CHARACTER_ID}, home:$homedomain, domain:{$row->DOMAIN}</li>";
			$text .= $row->DOMAIN == $homedomain ? "" : " ({$row->DOMAIN})";
			if (array_key_exists($row->CHARACTER_ID, $offices)) {
				$offices[$row->CHARACTER_ID] .= ", $text";
			} else {
				$offices[$row->CHARACTER_ID] = $text;
			}
		
		}
	}
	//print_r($offices);
	
	if (count($result)) {
		$columnlist = explode(',',$columns);
		$output = "<table class='gvplugin' id=\"" . vtm_get_shortcode_id("gvid_blb") . "\">\n";
		if ($heading) {
			$output .= "<tr>";
			foreach ($columnlist as $name) {
				if ($name == 'character') $output .= "<th>Character</th>";
				if ($name == 'player') $output .= "<th>Player</th>";
				if ($name == 'clan')   $output .= "<th>Clan</th>";
				if ($name == 'status') $output .= "<th>Character Status</th>";
				if ($name == 'domain')  $output .= "<th>Location</th>";
				if ($name == 'background')   $output .= "<th>Background</th>";
				if ($name == 'comment')   $output .= "<th>Comment</th>";
				if ($name == 'sector')   $output .= "<th>Sector</th>";
				if ($name == 'level')  $output .= "<th>Level</th>";
				if ($name == 'office')   $output .= "<th>Office</th>";
				if ($name == 'sect')   $output .= "<th>Affiliation</th>";
				if ($name == 'pronouns')   $output .= "<th>Pronouns</th>";
			}
			$output .= "</tr>\n";
		}
		
		foreach ($result as $tablerow) {
			$col = 1;
			$output .= "<tr>";
			foreach ($columnlist as $name) {
				if ($name == 'character') $output .= "<td class='gvcol_$col gvcol_key'>" . vtm_get_profilelink($tablerow->wordpress_id, $tablerow->charname) . "</td>";
				if ($name == 'player') $output .= "<td class='gvcol_$col gvcol_val'>" . vtm_formatOutput($tablerow->playername) . "</td>";
				if ($name == 'clan')   $output .= "<td class='gvcol_$col gvcol_val'>" . vtm_formatOutput($tablerow->publicclan) . "</td>";
				if ($name == 'status') $output .= "<td class='gvcol_$col gvcol_val'>" . vtm_formatOutput($tablerow->cstat) . "</td>";
				if ($name == 'domain')  $output .= "<td class='gvcol_$col gvcol_val'>" . vtm_formatOutput($tablerow->domain) . "</td>";
				if ($name == 'background') $output .= "<td class='gvcol_$col gvcol_val'>" . vtm_formatOutput($background) . "</td>";
				if ($name == 'comment') $output .= "<td class='gvcol_$col gvcol_val'>" . vtm_formatOutput($tablerow->comment) . "</td>";
				if ($name == 'sector') $output .= "<td class='gvcol_$col gvcol_val'>" . vtm_formatOutput($tablerow->sectorname) . "</td>";
				if ($name == 'level')  $output .= "<td class='gvcol_$col gvcol_val'>{$tablerow->level}</td>";
				if ($name == 'office') {
					$text = isset($offices[$tablerow->id]) ? vtm_formatOutput($offices[$tablerow->id]) : "";
					$output .= "<td class='gvcol_$col gvcol_val'>$text</td>";
				}
				if ($name == 'sect')  $output .= "<td class='gvcol_$col gvcol_val'>" . vtm_formatOutput($tablerow->sect) . "</td>";
				if ($name == 'pronouns')  $output .= "<td class='gvcol_$col gvcol_val'>" . vtm_formatOutput($tablerow->pronouns) . "</td>";
				$col++;
			}
			$output .= "</tr>\n";
		}
		
		$output .= "</table>";
	} else {
		$output = "<p>No characters with the matching $background background</p>";
	}
	
	return $output;
}
add_shortcode('background_table', 'vtm_print_background_shortcode');


add_shortcode('integration_alo_easymail', 'vtm_get_character_from_email');
function vtm_get_character_from_email ($email, $setting = 'name') {
	global $wpdb;

	$sqlCharID = "SELECT chara.id, chara.name, paths.name as pathname, chara.player_id
			FROM	
				" . $wpdb->prefix . "VTM_CHARACTER chara,
				" . $wpdb->prefix . "users wpusers,
				" . $wpdb->prefix . "GVLARP_ROAD_OR_PATH paths
			WHERE
				wpusers.user_email = %s
				AND chara.WORDPRESS_ID = wpusers.user_login
				AND chara.DELETED = 'N'
				AND chara.VISIBLE = 'Y'
				AND paths.ID = chara.ROAD_OR_PATH_ID
			ORDER BY chara.CHARACTER_STATUS_ID ASC, chara.LAST_UPDATED DESC, chara.name
			LIMIT 1";
	$result = $wpdb->get_results($wpdb->prepare($sqlCharID, $email));
	$id   = $result[0]->id;
	$name = $result[0]->name;
	$path = $result[0]->pathname;
	$playerid = $result[0]->player_id;
		
	if ($setting == 'xptotal') {
		$xp = vtm_get_total_xp($this->player_id, $characterID);
	}
	
	if ($setting == 'rating') {

		$sql = "SELECT
					SUM(cha_path.AMOUNT) as rating
				FROM
					" . $wpdb->prefix . "VTM_CHARACTER_ROAD_OR_PATH cha_path
				WHERE
					cha_path.CHARACTER_ID = %s";
		$result = $wpdb->get_results($wpdb->prepare($sql, $id));
		$rating = $result[0]->rating;
		
	}

	switch ($setting) {
		case 'name':    $output = $name; break;
		case 'path':    $output = $path; break;
		case 'rating':  $output = $rating; break;
		case 'xptotal': $output = $xp; break;
		default:
			$output = $setting;
	}
		
	return $output;

}

function vtm_print_merit_shortcode($atts, $content = null) {
	global $wpdb;
	
	$output = "";

	extract(shortcode_atts(array (
		"character" => "null",
		"merit"      => "Clan Friendship",
		"match"      => "",
		"domain"     => "home",
		"liststatus" => "Alive",
		"columns"    => "character,player,clan,domain,merit,comment,level",
		"heading"    => 1,
		"charactertype" => 'all'
		), $atts)
	);
	
	/* 
		match = <value>, loggedinclan or loggedinsect
		
		domain = "" or <value> or loggedin or home
		
		level = "all" or "displayzeros" or <number>
		
		charactertype = all, PC, NPC
	*/

	$character = vtm_establishCharacter($character);
	$characterID = vtm_establishCharacterID($character);
		
	$sqlmain = "SELECT chara.id,
				chara.wordpress_id,
				chara.name as charname,
				pubclan.name as publicclan,
				privclan.name as privateclan,
				player.name as playername,
				pstatus.name as pstat,
				cstatus.name as cstat,
				merit.name as meritname,
				char_merit.level as level,
				char_merit.comment as comment,
				domains.name as domain,
				sects.name as sect,
				cgstatus.name as chargenstat,
				ctype.name as charactertype
			FROM
				" . VTM_TABLE_PREFIX . "CHARACTER chara,
				" . VTM_TABLE_PREFIX . "PLAYER player,
				" . VTM_TABLE_PREFIX . "PLAYER_STATUS pstatus,
				" . VTM_TABLE_PREFIX . "CHARACTER_STATUS cstatus,
				" . VTM_TABLE_PREFIX . "CHARACTER_TYPE ctype,
				" . VTM_TABLE_PREFIX . "CLAN pubclan,
				" . VTM_TABLE_PREFIX . "CLAN privclan,
				" . VTM_TABLE_PREFIX . "MERIT merit,
				" . VTM_TABLE_PREFIX . "CHARACTER_MERIT char_merit,
				" . VTM_TABLE_PREFIX . "DOMAIN domains,
				" . VTM_TABLE_PREFIX . "CHARGEN_STATUS cgstatus,
				" . VTM_TABLE_PREFIX . "SECT sects
			WHERE
				chara.PLAYER_ID = player.ID
				AND chara.ID = char_merit.CHARACTER_ID
                AND player.PLAYER_STATUS_ID = pstatus.ID
				AND pstatus.NAME = 'Active'
				AND chara.CHARACTER_STATUS_ID = cstatus.ID
				AND chara.CHARACTER_TYPE_ID = ctype.ID
				AND chara.PUBLIC_CLAN_ID = pubclan.ID
				AND chara.PRIVATE_CLAN_ID = privclan.ID
				AND cgstatus.ID = chara.CHARGEN_STATUS_ID
				AND merit.ID = char_merit.MERIT_ID
				AND domains.ID = chara.DOMAIN_ID
				AND sects.ID = chara.SECT_ID
				AND chara.VISIBLE = 'Y'
				AND chara.DELETED = 'N'
				AND cgstatus.NAME = 'Approved'
				AND merit.name = %s";
	$sqlmainargs = array($merit);
	
	if ($liststatus) {
		$list = explode(',',$liststatus);
		$sqlfilter = " AND ( cstatus.name = '";
		$sqlfilter .= implode("' OR cstatus.name = '",$list);
		$sqlfilter .= "')";
		
		$sqlmain .= $sqlfilter;
	}
	if ($charactertype != 'all') {
		$sqlmain .= " AND ctype.name = %s";
		array_push($sqlmainargs, $charactertype);
	}
	
	if ($match) {
		if ($match == 'loggedinclan') {
			$clans = vtm_get_loggedinclan($characterID);
			$sqlmain .= " AND (char_merit.comment = %s OR char_merit.comment = %s)";
			array_push($sqlmainargs, $clans[0]->priv, $clans[0]->pub);
		} 
		elseif ($match == 'loggedinsect') {
			$sect = vtm_get_loggedinsect($characterID);
			$sqlmain .= " AND char_merit.comment = %s";
			array_push($sqlmainargs, $sect);
		}
		else {
			$sqlmain .= " AND char_merit.comment = %s";
			array_push($sqlmainargs, $match);
		}
	}
	
	if ($domain) {
		$sqlfilter = " AND domains.name = %s";
		$sqlmain .= $sqlfilter;
		
		if ($domain == 'loggedin')
			$domain = vtm_get_loggedindomain($characterID);
		if ($domain == 'home')
			$domain = vtm_get_homedomain();
		
		array_push($sqlmainargs, $domain);
	}
		
	$sql = $sqlmain;
	$sqlargs = $sqlmainargs;
	
	$sql .= " ORDER BY level DESC, charname";

	$sql = $wpdb->prepare($sql, $sqlargs);
	$result = $wpdb->get_results($sql);
	
	//echo "<p>SQL: $sql<p>";
	//print_r($result);
	
	if (count($result)) {
		$columnlist = explode(',',$columns);
		$output = "<table class='gvplugin' id=\"" . vtm_get_shortcode_id("gvid_mlb") . "\">\n";
		if ($heading) {
			$output .= "<tr>";
			foreach ($columnlist as $name) {
				if ($name == 'character') $output .= "<th>Character</th>";
				if ($name == 'player') $output .= "<th>Player</th>";
				if ($name == 'clan')   $output .= "<th>Clan</th>";
				if ($name == 'status') $output .= "<th>Character Status</th>";
				if ($name == 'domain')  $output .= "<th>Location</th>";
				if ($name == 'merit')   $output .= "<th>Merit</th>";
				if ($name == 'comment')   $output .= "<th>Comment</th>";
				if ($name == 'level')  $output .= "<th>Level</th>";
				if ($name == 'sect')  $output .= "<th>Affiliation</th>";
			}
			$output .= "</tr>\n";
		}
		
		//$vtmglobal['config'] = getConfig();

		foreach ($result as $tablerow) {
			$col = 1;
			$output .= "<tr>";
			foreach ($columnlist as $name) {
				if ($name == 'character') $output .= "<td class='gvcol_$col gvcol_key'>" . vtm_get_profilelink($tablerow->wordpress_id, $tablerow->charname) . "</td>";
				if ($name == 'player') $output .= "<td class='gvcol_$col gvcol_val'>" . vtm_formatOutput($tablerow->playername) . "</td>";
				if ($name == 'clan')   $output .= "<td class='gvcol_$col gvcol_val'>" . vtm_formatOutput($tablerow->publicclan) . "</td>";
				if ($name == 'status') $output .= "<td class='gvcol_$col gvcol_val'>" . vtm_formatOutput($tablerow->cstat) . "</td>";
				if ($name == 'domain')  $output .= "<td class='gvcol_$col gvcol_val'>" . vtm_formatOutput($tablerow->domain) . "</td>";
				if ($name == 'merit') $output .= "<td class='gvcol_$col gvcol_val'>" . vtm_formatOutput($merit) . "</td>";
				if ($name == 'comment') $output .= "<td class='gvcol_$col gvcol_val'>" . vtm_formatOutput($tablerow->comment) . "</td>";
				if ($name == 'level')  $output .= "<td class='gvcol_$col gvcol_val'>{$tablerow->level}</td>";
				if ($name == 'sect')  $output .= "<td class='gvcol_$col gvcol_val'>" . vtm_formatOutput($tablerow->sect) . "</td>";
				$col++;
			}
			$output .= "</tr>\n";
		}
		
		$output .= "</table>";
	} else {
		$output = "<p>No characters with the matching merit '" . vtm_formatOutput($merit) . "'</p>";
	}
	
	return $output;
}
add_shortcode('merit_table', 'vtm_print_merit_shortcode');

function vtm_print_character_xp_table($atts, $content=null) {
	extract(shortcode_atts(array ("character" => "null", "group" => "", "maxrecords" => "20"), $atts));
	global $vtmglobal;
	global $wpdb;
	
	if (!is_user_logged_in()) {
		return "You must be logged in to view this content";
	}
	
	$character   = vtm_establishCharacter($character);
	$characterID = vtm_establishCharacterID($character);
	$playerID    = vtm_establishPlayerID($character);

	$table_prefix = VTM_TABLE_PREFIX;
		
	$xp_total = vtm_get_total_xp($playerID, $characterID);

	if ($group != "total" && $group != "TOTAL") {
		$character_xp = vtm_get_xp_table($playerID, $characterID, (int) $maxrecords);

		$output = "<table class='gvplugin' id=\"" . vtm_get_shortcode_id("cxpt") . "\">
						   <tr><th class=\"gvthead gvcol_1\">Character</th>
							   <th class=\"gvthead gvcol_2\">XP Reason</th>
							   <th class=\"gvthead gvcol_3\">XP Amount</th>
							   <th class=\"gvthead gvcol_4\">Comment</th>
							   <th class=\"gvthead gvcol_5\">Date of award</th></tr>\n";

		//$arr = array();
		//$i = 0;
		foreach ($character_xp as $current_xp) {
			$output .= "<tr><td class=\"gvcol_1 gvcol_key\">" . vtm_formatOutput($current_xp->char_name)   . "</td><td class=\"gvcol_2 gvcol_val\">"
				. vtm_formatOutput($current_xp->reason_name) . "</td><td class=\"gvcol_3 gvcol_val\">"
				. $current_xp->amount      . "</td><td class='gvcol_4 gvcol_val'>"
				. vtm_formatOutput($current_xp->comment)     . "</td><td class='gvcol_5 gvcol_val'>"
				. $current_xp->awarded     . "</td></tr>\n";
			//$i++;
		}

		/*
		$pageSize = 20;
		if ((int) $maxrecords > 0) {
			$pageSize = (int) $maxrecords;
		}
		$j = 0;
		if ($i > $pageSize) {
			$j = $i - $pageSize;
		}

		while ($j < $i) {
			$output .= $arr[$j];
			$j++;
		}*/

		$output .= "<tr><td colspan=3 class=\"gvsummary\">Total amount of XP to spend</td>
							<td colspan=2 class=\"gvsummary\">" . $xp_total . "</td></tr>\n";

		$output .= "</table>";
		
	}
	else {

		$output = $xp_total;

	}

	return $output;
}
add_shortcode('character_xp_table', 'vtm_print_character_xp_table');


function vtm_print_map($atts, $content = null) {
	global $wpdb;

	$output = "";
	
	if (get_option( 'vtm_feature_maps', '0' ) == 0) return "<p>Map Feature disabled</p>";

	/* Attributes:
	//		map size
	//		show/hide map key */
	extract(shortcode_atts(array (
		"showmapkey"    => 1,
		"height"        => 250,
		"width"         => 400
		), $atts)
	);

	$apikey = get_option('feedingmap_google_api');
	$lat    = get_option('feedingmap_centre_lat', '55.862982');
	$long   = get_option('feedingmap_centre_long', '-4.242325');
	$zoom   = get_option('feedingmap_zoom', '8');
	$type   = get_option('feedingmap_map_type', 'ROADMAP');

	/* Get Domains */
	$sql = "SELECT domains.*, owners.FILL_COLOUR, owners.VISIBLE as SHOWOWNER
			FROM 
				" . VTM_TABLE_PREFIX . "MAPDOMAIN domains,
				" . VTM_TABLE_PREFIX . "MAPOWNER owners
			WHERE 
				owners.ID = domains.OWNER_ID
				AND domains.VISIBLE = 'Y'";
	$domains = $wpdb->get_results($sql);
	
	
	/* Define the LatLng coordinates for the polygon's path. */
	$output .= "<script type='text/javascript'><!--
	var infoWindow;
	function vtm_loadDomains(map) {
		infoWindow = new google.maps.InfoWindow({maxWidth: 200});

		var domains = {\n";
	$initial = '';
	foreach ($domains as $domain) {
		// d#:{id:#, fill:'#xxxxxx', coords:[...]}, ...
		$output .= sprintf('%1$sd%2$d:{id:%2$d,', $initial."\t\t\t", $domain->ID);
		if (empty($initial))
			$initial = ",\n";

		// Domain name & description
		$output .= sprintf("name:'%s',desc:'%s',", vtm_formatOutput($domain->NAME), vtm_formatOutput($domain->DESCRIPTION));

		// Polygon fill colour
		$output .= sprintf("fill:'%s',", $domain->SHOWOWNER == 'Y' ? vtm_formatOutput($domain->FILL_COLOUR) : '#FFFFFF');

		// Coordinate list
		$output .= 'coords:[';
		$coordlist = explode("\n", $domain->COORDINATES);
		foreach($coordlist as $key => $coord) {
			$latlong = explode(',', preg_replace('/\s+/','',$coord));
			if (is_numeric($latlong[0]) && is_numeric($latlong[1]))
				$coordlist[$key] = sprintf('[%s,%s]', $latlong[0], $latlong[1]);
			else
				unset($coordlist[$key]);
		}
		$output .= implode(',', $coordlist);
		$output .= "]";

		// d#:{ ... }
		$output .= '}';
	}
	$output .= "
		};
		for (var tag in domains) {
			var domain = domains[tag];

			// Convert the lat/long pairs to objects
			// Calculate the bounding box of the domain
			domain.bounds = null;
			for (var i = 0; i < domain.coords.length; i++) {
				var pair = domain.coords[i];
				domain.coords[i] = new google.maps.LatLng(pair[0], pair[1]);
				if (domain.bounds)
					domain.bounds.extend(domain.coords[i]);
				else
					domain.bounds = new google.maps.LatLngBounds(domain.coords[i]);
			}

			// Create and add the domain polygon to the map
			var poly = new google.maps.Polygon({
				paths: domain.coords,
				strokeColor: '#000000',
				strokeOpacity: 0.8,
				strokeWeight: 1,
				fillColor: domain.fill,
				fillOpacity: 0.35
			});
			poly.domain = domain;
			poly.setMap(map);
			domains[tag].polygon = poly;

			// Add a listener for the click event.
			domain.listener = google.maps.event.addListener(poly, 'click', function(event) {
				infoWindow.setPosition(this.domain.bounds.getCenter());
				infoWindow.setContent('<div><b>'+this.domain.name+'</b><br>'+this.domain.desc+'</div>');
				infoWindow.open(map);
			});
		}
	}
	--></script>\n";

	$output .= "<input type='hidden' name='feedingmap_apikey' id='feedingmap_apikeyID' value=\"$apikey\">\n";
	$output .= "<input type='hidden' name='feedingmap_clat'   id='feedingmap_clatID'   value=\"$lat\">\n";
	$output .= "<input type='hidden' name='feedingmap_clong'  id='feedingmap_clongID'  value=\"$long\">\n";
	$output .= "<input type='hidden' name='feedingmap_zoom'   id='feedingmap_zoomID'  value=\"$zoom\">\n";
	$output .= "<input type='hidden' name='feedingmap_type'   id='feedingmap_typeID'  value=\"$type\">\n";
	$output .= "<input type='button' name='Reload' value='Refresh' onclick=\"vtm_initialize()\">\n";
	$output .= "<p id=\"feedingmap_status\">Start</p>\n";
	$output .= "<div id=\"feedingmap\" style=\"height:{$height}px; width:{$width}px\">\n";
	$output .= "<div id=\"map-canvas\" style=\"width: 100%; height: 100%\"></div>\n";
	$output .= "</div>\n";

	/* Map Key */
	$sql = "SELECT * FROM " . VTM_TABLE_PREFIX . "MAPOWNER WHERE VISIBLE = 'Y'";
	$owners = $wpdb->get_results($sql);
    $output .= "<table class=\"feedingmapkey\">\n";
	$output .= "<tr><th colspan=2>Map Key</th></tr>\n";
	foreach ($owners as $owner) {
		$output .= "<tr><td>". vtm_formatOutput($owner->NAME) . "</td>";
		$output .= "<td style='background-color:" . $owner->FILL_COLOUR . ";width:10px'>&nbsp;</td></tr>\n";
	}
	$output .= "</table>\n\n";

	return $output;
}
add_shortcode('feeding_map', 'vtm_print_map');

function vtm_print_character_road_or_path_table($atts, $content=null) {
	extract(shortcode_atts(array ("character" => "null", "group" => "", "maxrecords" => "20"), $atts));
	
	if (!is_user_logged_in()) {
		return "You must be logged in to view this content";
	}
	
	$character = vtm_establishCharacter($character);
	
	$output = "";

	global $wpdb;
	$table_prefix = VTM_TABLE_PREFIX;

	$sql = "SELECT chara.name char_name, preason.name reason_name, cpath.amount, cpath.comment, cpath.awarded, total_path
					FROM " . $table_prefix . "CHARACTER chara,
						 " . $table_prefix . "CHARACTER_ROAD_OR_PATH cpath,
						 " . $table_prefix . "PATH_REASON preason,
						 (SELECT character_path.character_id, SUM(character_path.amount) total_path
						  FROM " . $table_prefix . "CHARACTER_ROAD_OR_PATH character_path
						  GROUP BY character_path.character_id) path_totals
					WHERE path_totals.character_id = chara.id
					  AND chara.DELETED != 'Y'
					  AND cpath.path_reason_id = preason.id
					  AND cpath.character_id = chara.ID
					  AND chara.WORDPRESS_ID = %s
					ORDER BY cpath.awarded, cpath.id";

	$character_path = $wpdb->get_results($wpdb->prepare($sql, $character));

	if ($group != "total" && $group != "TOTAL") {
		$output .= "<table class='gvplugin' id=\"" . vtm_get_shortcode_id("gvid_crpt") . "\">
						   <tr><th class=\"gvthead gvcol_1\">Path Reason</th>
							   <th class=\"gvthead gvcol_2\">Path Amount</th>
							   <th class=\"gvthead gvcol_3\">Comment</th>
							   <th class=\"gvthead gvcol_4\">Date of award</th></tr>";

		$arr = array();
		$i = 0;
		$path_total = 0;
		foreach ($character_path as $current_path) {
			$arr[$i] = "<tr><td class=\"gvcol_1 gvcol_val\">" . vtm_formatOutput($current_path->reason_name) . "</td><td class=\"gvcol_2 gvcol_val\">"
				. $current_path->amount      . "</td><td class=\"gvcol_3 gvcol_val\">"
				. vtm_formatOutput($current_path->comment)     . "</td><td class='gvcol_4 gvcol_val'>"
				. $current_path->awarded     . "</td></tr>";
			$path_total = (int) $current_path->total_path;
			$i++;
		}

		$pageSize = 20;
		if ((int) $maxrecords > 0) {
			$pageSize = (int) $maxrecords;
		}
		$j = 0;
		if ($i > $pageSize) {
			$j = $i - $pageSize;
		}

		while ($j < $i) {
			$output .= $arr[$j];
			$j++;
		}

		$output .= "<tr><td colspan=2>Total </td>
								<td class=\"gvsummary\" colspan=2>" . $path_total . "</td></tr>";

		$output .= "</table>";
	}
	else {
		$total_path = 0;
		foreach ($character_path as $current_path) {
			$total_path = (int) $current_path->total_path;
		}

		$output = $total_path;
	}

	return $output;
}
add_shortcode('character_road_or_path_table', 'vtm_print_character_road_or_path_table');

function vtm_print_character_details($atts, $content=null) {
	extract(shortcode_atts(array ("character" => "null", "group" => ""), $atts));
	
	if (!is_user_logged_in()) {
		return "You must be logged in to view this content";
	}

	$character = vtm_establishCharacter($character);

	global $vtmglobal;
	global $wpdb;
	$table_prefix = VTM_TABLE_PREFIX;
	$output    = "";

	$sql = "SELECT chara.name char_name,
						   pub_clan.name pub_clan,
						   priv_clan.name priv_clan,
						   chara.date_of_birth,
						   chara.date_of_embrace,
						   IFNULL(cgen.APPR_DATE,playxp.XP_AWARDED) approval_date,
						   gen.name gen,
						   gen.bloodpool,
						   gen.blood_per_round,
						   chara.sire,
						   status.name status,
						   chara.character_status_comment status_comment,
						   domains.name domain,
						   path.name path_name,
						   path_totals.path_value,
						   chara.ID,
						   chara.last_updated
					FROM " . $table_prefix . "CHARACTER chara
						LEFT JOIN (
							SELECT CHARACTER_ID, MIN(DATE_OF_APPROVAL) AS APPR_DATE
							FROM " . $table_prefix . "CHARACTER_GENERATION
							GROUP BY CHARACTER_ID
						) cgen
						ON cgen.CHARACTER_ID = chara.ID
						LEFT JOIN (
							SELECT CHARACTER_ID, MIN(AWARDED) AS XP_AWARDED
							FROM " . $table_prefix . "PLAYER_XP
							GROUP BY CHARACTER_ID
						) playxp
						ON playxp.CHARACTER_ID = chara.ID
						,
						 " . $table_prefix . "CLAN pub_clan,
						 " . $table_prefix . "CLAN priv_clan,
						 " . $table_prefix . "GENERATION gen,
						 " . $table_prefix . "CHARACTER_STATUS status,
						 " . $table_prefix . "DOMAIN domains,
						 " . $table_prefix . "ROAD_OR_PATH path,
						 (SELECT character_path.character_id, SUM(character_path.amount) path_value
						  FROM " . $table_prefix . "CHARACTER_ROAD_OR_PATH character_path
						  GROUP BY character_path.character_id) path_totals
					WHERE chara.WORDPRESS_ID = %s
					  AND chara.public_clan_id      = pub_clan.id
					  AND chara.private_clan_id     = priv_clan.id
					  AND chara.generation_id       = gen.id
					  AND chara.DELETED != 'Y'
					  AND chara.character_status_id = status.id
					  AND chara.domain_id           = domains.id
					  AND chara.road_or_path_id     = path.id
					  AND chara.id                  = path_totals.character_id";

	//print $wpdb->prepare($sql, $character);
	$character_details = $wpdb->get_row($wpdb->prepare($sql, $character));
	
	if ($vtmglobal['config']->USE_NATURE_DEMEANOUR == 'Y' && vtm_count($character_details) > 0) {
			
		$sql = "SELECT 
					natures.name as nature,
					demeanours.name as demeanour
				FROM
					" . VTM_TABLE_PREFIX . "CHARACTER chara,
					" . VTM_TABLE_PREFIX . "NATURE natures,
					" . VTM_TABLE_PREFIX . "NATURE demeanours
				WHERE
					chara.NATURE_ID = natures.ID
					AND chara.DEMEANOUR_ID = demeanours.ID
					AND chara.ID = %s";
		$result = $wpdb->get_row($wpdb->prepare($sql, $character_details->ID));
	
		$character_details->nature    = isset($result->nature) ? $result->nature : '';
		$character_details->demeanour = isset($result->demeanour) ? $result->demeanour : '';
		
	}

	if (vtm_count($character_details) > 0) {
		if ($group == "") {
			$output  = "<table class='gvplugin' id=\"" . vtm_get_shortcode_id("gvid_cdb") . "\"><tr><td class=\"gvcol_1 gvcol_key\">Character Name</td><td class=\"gvcol_2 gvcol_val\">" . vtm_formatOutput($character_details->char_name) . "</td></tr>";
			$output .= "<tr><td class=\"gvcol_1 gvcol_key\">Public Clan</td><td class=\"gvcol_2 gvcol_val\">"           . vtm_formatOutput($character_details->pub_clan)        . "</td></tr>";
			$output .= "<tr><td class=\"gvcol_1 gvcol_key\">Private Clan</td><td class=\"gvcol_2 gvcol_val\">"          . vtm_formatOutput($character_details->priv_clan)       . "</td></tr>";
			$output .= "<tr><td class=\"gvcol_1 gvcol_key\">Date of Birth</td><td class=\"gvcol_2 gvcol_val\">"         . $character_details->date_of_birth   . "</td></tr>";
			$output .= "<tr><td class=\"gvcol_1 gvcol_key\">Date of Embrace</td><td class=\"gvcol_2 gvcol_val\">"       . $character_details->date_of_embrace . "</td></tr>";
			$output .= "<tr><td class=\"gvcol_1 gvcol_key\">Generation</td><td class=\"gvcol_2 gvcol_val\">"            . $character_details->gen             . "th</td></tr>";
			$output .= "<tr><td class=\"gvcol_1 gvcol_key\">Max Bloodpool</td><td class=\"gvcol_2 gvcol_val\">"         . $character_details->bloodpool       . "</td></tr>";
			$output .= "<tr><td class=\"gvcol_1 gvcol_key\">Max blood per round</td><td class=\"gvcol_2 gvcol_val\">"   . $character_details->blood_per_round . "</td></tr>";
			$output .= "<tr><td class=\"gvcol_1 gvcol_key\">Sire's Name</td><td class=\"gvcol_2 gvcol_val\">"           . vtm_formatOutput($character_details->sire)            . "</td></tr>";
			$output .= "<tr><td class=\"gvcol_1 gvcol_key\">Character Status</td><td class=\"gvcol_2 gvcol_val\">"      . vtm_formatOutput($character_details->status)          . "</td></tr>";
			$output .= "<tr><td class=\"gvcol_1 gvcol_key\">Status Comment</td><td class=\"gvcol_2 gvcol_val\">"        . vtm_formatOutput($character_details->status_comment)  . "</td></tr>";
			$output .= "<tr><td class=\"gvcol_1 gvcol_key\">Current Domain</td><td class=\"gvcol_2 gvcol_val\">"        . vtm_formatOutput($character_details->domain)          . "</td></tr>";
			$output .= "<tr><td class=\"gvcol_1 gvcol_key\">Road or Path name</td><td class=\"gvcol_2 gvcol_val\">"     . vtm_formatOutput($character_details->path_name)       . "</td></tr>";
			$output .= "<tr><td class=\"gvcol_1 gvcol_key\">Road or Path rating</td><td class=\"gvcol_2 gvcol_val\">"   . $character_details->path_value      . "</td></tr>";
			$output .= "<tr><td class=\"gvcol_1 gvcol_key\">Last Updated</td><td class=\"gvcol_2 gvcol_val\">"          . $character_details->last_updated    . "</td></tr>";
			$output .= "<tr><td class=\"gvcol_1 gvcol_key\">Character Creation Date</td><td class=\"gvcol_2 gvcol_val\">"       . $character_details->approval_date . "</td></tr>";
			
			if ($vtmglobal['config']->USE_NATURE_DEMEANOUR == 'Y') {
				
				$output .= "<tr><td class=\"gvcol_1 gvcol_key\">Nature</td><td class=\"gvcol_2 gvcol_val\">" . vtm_formatOutput($character_details->nature)      . "</td></tr>";
				$output .= "<tr><td class=\"gvcol_1 gvcol_key\">Demeanour</td><td class=\"gvcol_2 gvcol_val\">" . vtm_formatOutput($character_details->demeanour)      . "</td></tr>";
			
			}
			
			$output .= "</table>";
		}
		else {
			$output = "<span class=\"gvcol_val\" id=\"gvid_cdeb_" . $group . "\">" . vtm_formatOutput($character_details->$group) . "</span>";
		}
	}

	return $output;
}
add_shortcode('character_detail_block', 'vtm_print_character_details');

function vtm_print_character_offices($atts, $content=null) {
	extract(shortcode_atts(array ("character" => "null", "group" => ""), $atts));
	$character = vtm_establishCharacter($character);
	
	if (!is_user_logged_in()) {
		return "You must be logged in to view this content";
	}

	global $wpdb;
	$table_prefix = VTM_TABLE_PREFIX;
	$output    = "";
	$sqlOutput = "";
	$sql = "SELECT office.name office_name, domain.name domain_name, coffice.comment
					FROM " . $table_prefix . "CHARACTER_OFFICE coffice,
						 " . $table_prefix . "OFFICE office,
						 " . $table_prefix . "DOMAIN domain,
						 " . $table_prefix . "CHARACTER chara
					WHERE coffice.OFFICE_ID    = office.ID
					  AND coffice.CHARACTER_ID = chara.ID
					  AND coffice.DOMAIN_ID     = domain.ID
					  AND chara.DELETED != 'Y'
					  AND chara.WORDPRESS_ID = %s
				   ORDER BY office.ordering, office.name, domain.name";

	$character_offices = $wpdb->get_results($wpdb->prepare($sql, $character));

	foreach ($character_offices as $current_office) {
		$sqlOutput .="<tr><td class=\"gvcol_1 gvcol_key\">"  . vtm_formatOutput($current_office->office_name) . "</td>
								  <td class=\"gvcol_2 gvcol_val\">"  . vtm_formatOutput($current_office->domain_name)  . "</td>
								  <td class=\"gvcol_3 gvcol_spec\">" . vtm_formatOutput($current_office->comment)     . "</td></tr>";
	}

	if ($sqlOutput != "") {
		$output = "<table class='gvplugin' id=\"" . vtm_get_shortcode_id("cxpt") . "\" >" . $sqlOutput . "</table>";
	}
	else {
		$output = "";
	}

	return $output;
}
add_shortcode('character_offices_block', 'vtm_print_character_offices');

function vtm_print_character_temp_stats($atts, $content=null) {

	if (get_option( 'vtm_feature_temp_stats', '0' ) == 0) return "<p>Temporary stat tracking feature disabled</p>";

	extract(shortcode_atts(
		array ("character" => "null", "stat" => "Willpower", "showtable" => "0", "limit" => "5")
		, $atts)
	);
	$character = vtm_establishCharacter($character);
	$characterID = vtm_establishCharacterID($character);

	if (!is_user_logged_in()) {
		return "You must be logged in to view this content";
	}
	if (!isset($characterID) || $characterID == "") {
		return "Please select a character to view";
	}
	
	global $wpdb;
	$table_prefix = VTM_TABLE_PREFIX;

	$sqlOutput = "";
	$sql = "SELECT SUM(char_temp_stat.amount)
			FROM " . $table_prefix . "CHARACTER_TEMPORARY_STAT char_temp_stat,
				 " . $table_prefix . "CHARACTER chara,
				 " . $table_prefix . "TEMPORARY_STAT tstat
			WHERE char_temp_stat.character_id      = chara.id
			  AND char_temp_stat.temporary_stat_id = tstat.id
			  AND tstat.name         = %s
			  AND chara.WORDPRESS_ID = %s
			GROUP BY char_temp_stat.character_id, char_temp_stat.temporary_stat_id";

	$totalstat = $wpdb->get_var($wpdb->prepare($sql, $stat, $character));

	$output = "<div id=\"" . vtm_get_shortcode_id("gvid_ctw_" . esc_attr($stat)) . "\">";
	if ($showtable) {
		$sql = "SELECT 
					chartemp.AMOUNT,
					reasons.NAME,
					chartemp.AWARDED,
					chartemp.COMMENT
				FROM
					" . $table_prefix . "CHARACTER_TEMPORARY_STAT chartemp,
					" . $table_prefix . "TEMPORARY_STAT tempstat,
					" . $table_prefix . "TEMPORARY_STAT_REASON reasons
				WHERE
					chartemp.TEMPORARY_STAT_ID = tempstat.ID
					AND chartemp.TEMPORARY_STAT_REASON_ID = reasons.ID
					AND chartemp.CHARACTER_ID = %s
					AND tempstat.NAME = %s
				ORDER BY chartemp.AWARDED DESC, chartemp.ID DESC
				LIMIT 0 , %d";
		$sql = $wpdb->prepare($sql, $characterID, $stat, $limit);
		$result = $wpdb->get_results($sql);
		//echo "<p>$character - SQL: $sql</p>";
		//print_r($result);
		
		$output .= "<table>
		<tr>
			<th class=\"gvthead gvcol_1\">Reason for Change</th>
			<th class=\"gvthead gvcol_2\">Amount</th>
			<th class=\"gvthead gvcol_3\">Date</th>
			<th class=\"gvthead gvcol_4\">Comment</th>
		</tr>";
		
		foreach ($result as $row) {
			$output .= sprintf("<tr><td class=\"gvcol_1 gvcol_key\">%s</td>
									<td class=\"gvcol_2 gvcol_val\">%d</td>
									<td class=\"gvcol_3 gvcol_val\">%s</td>
									<td class=\"gvcol_4 gvcol_val\">%s</td>
								</tr>\n",
						vtm_formatOutput($row->NAME), $row->AMOUNT, $row->AWARDED, vtm_formatOutput($row->COMMENT));
		}
		$output .= "<tr><td colspan=2 class=\"gvsummary\">Current $stat</td>
							<td colspan=2 class=\"gvsummary\">$totalstat</td></tr>\n";
		$output .= "</table>";
		
	} else {
		$output .= $totalstat;
	}
	
	
	$output .= "</div>";

	return $output;
}
add_shortcode('character_temp_stats', 'vtm_print_character_temp_stats');

function vtm_print_office_block($atts, $content=null) {
	
	extract(shortcode_atts(array ("domain" => "home", "office" => ""), $atts));

	global $wpdb;
	$table_prefix = VTM_TABLE_PREFIX;
	$output    = "";
	$sqlOutput = "";

	$sql = "SELECT chara.name charname, office.name oname, domain.name domainname, 
				office.ordering, coffice.comment, chara.ID characterID
			FROM " . $table_prefix . "CHARACTER chara,
				 " . $table_prefix . "CHARACTER_OFFICE coffice,
				 " . $table_prefix . "OFFICE office,
				 " . $table_prefix . "DOMAIN domain
			WHERE coffice.character_id = chara.id
			  AND coffice.office_id    = office.id
			  AND coffice.domain_id     = domain.id
			  AND chara.deleted        = 'N'
			  AND domain.name = %s ";
	if (!vtm_isSt()) {
		$sql .= " AND office.visible = 'Y' AND chara.visible = 'Y' ";
	}
	if ($office != null && $office != "") {
		$sql .= " AND office.name = %s ";
	}
	$sql .= "ORDER BY domainname, office.ordering, charname";

	if (isset($domain) && $domain == 'home') {
		$domain = vtm_get_homedomain();
	}
	
	if ($office != null && $office != "") {
		$characterOffices = $wpdb->get_results($wpdb->prepare($sql, $domain, $office));
	}
	else {
		$characterOffices = $wpdb->get_results($wpdb->prepare($sql, $domain));
	}

	if ($office == null || $office == "") {
		$currentOffice = "";
		$lastOffice    = "";

		foreach ($characterOffices as $characterOffice) {
			$currentOffice = $characterOffice->oname;
			if ($currentOffice != $lastOffice) {
				$sqlOutput .= "<tr><td class=\"gvcol_1 gvcol_key\">" . vtm_formatOutput($characterOffice->oname) . "</td>";
				$lastOffice = $currentOffice;
			}
			else {
				$sqlOutput .= "<tr><td class=\"gvcol_1 gvcol_key\">&nbsp;</td>";
			}
			$sqlOutput .= "<td class=\"gvcol_2 gvcol_val\">" . vtm_pm_link(vtm_formatOutput($characterOffice->charname), array('characterID' => $characterOffice->characterID)) . "</td><td class=\"gvcol_3 gvcol_val\">" . stripslashes($characterOffice->comment) . "</td></tr>";
		}

		if ($sqlOutput != "") {
			$output = "<table class='gvplugin' id=\"" . vtm_get_shortcode_id("gvid_cob") . "\">" . $sqlOutput . "</table>";
		}
		else {
			$output = "No office holders found for " . vtm_formatOutput($domain);
		}
	}
	else {
		foreach ($characterOffices as $characterOffice) {
			if ($output != "") {
				$output .= ", ";
			}
			$output .= $characterOffice->charname;
		}
		if ($output == "") {
			$output = "No current holder of " . vtm_formatOutput($office) . " in " . vtm_formatOutput($domain) . " found.";
		}
	}
	return $output;
}
add_shortcode('office_block', 'vtm_print_office_block');

function vtm_print_spend_button($atts, $content=null) {
	global $wpdb;
	$wpdb->show_errors();

	if (get_option( 'vtm_feature_temp_stats', '0' ) == 0) return "<p>Temporary stat tracking feature disabled</p>";
	if (!is_user_logged_in()) return "You must be logged in to view this content";

	extract(shortcode_atts(array ("character" => "null", "stat" => "Willpower"), $atts));

	$character = vtm_establishCharacter($character);
	$characterID = vtm_establishCharacterID($character);

	if (!isset($characterID) || $characterID == "") {
		return "";
	}
	$buttonID  = vtm_get_shortcode_id("gv" . esc_attr($stat) . "sbut");
	$stagename = 'stage_' . $buttonID;
	
	if (isset($_REQUEST[$stagename]))
		$stage = $_REQUEST[$stagename];
	else
		$stage = '';
	$amount    = 1;
	$comment   = '';
	
	$output = "<div id=\"$buttonID\" class=\"gvspendbutton\">";
	$output .= "<form method='post' id='form_$buttonID'>";
	
	$sql = "SELECT SUM(char_temp_stat.amount)
		FROM " . VTM_TABLE_PREFIX . "CHARACTER_TEMPORARY_STAT char_temp_stat,
			 " . VTM_TABLE_PREFIX . "TEMPORARY_STAT tstat
		WHERE 
			char_temp_stat.temporary_stat_id = tstat.id
			AND tstat.name  = %s
			AND char_temp_stat.character_id    = %s";
	$sql = $wpdb->prepare($sql, $stat, $characterID);
	$currentstat = $wpdb->get_var($sql);
	
	switch($stage) {
		case "validate":
			$amount  = $_REQUEST["amount_$buttonID"];
			$comment = $_REQUEST["comment_$buttonID"];
			
			// amount must be a number > 0 and comment cannot be blank
			$spendok = 1;
			if (!is_numeric($amount) || $amount <= 0) {
				$spendok = 0;
				$output .= "<p>Change in $stat should be a number greater than zero</p>";
			}
			elseif (empty($comment)) {
				$spendok = 0;
				$output .= "<p>Please enter a comment on what you are spending your $stat on</p>";
			}
			// Can't spend all the points
			elseif ($currentstat - $amount <= 0) {
				$spendok = 0;
				$output .= "<p>You don't have enough $stat points to spend that much</p>";
			}
			
			if ($spendok) {
				
				$sql = "SELECT ID FROM " . VTM_TABLE_PREFIX . "TEMPORARY_STAT WHERE NAME = %s";
				$statID = $wpdb->get_var($wpdb->prepare($sql, $stat));
				$sql = "SELECT ID FROM " . VTM_TABLE_PREFIX . "TEMPORARY_STAT_REASON WHERE NAME = %s";
				$reasonID = $wpdb->get_var($wpdb->prepare($sql, 'Game spend'));
				
				// update database
				$data = array (
					'CHARACTER_ID'             => $characterID,
					'TEMPORARY_STAT_ID'        => $statID,
					'TEMPORARY_STAT_REASON_ID' => $reasonID,
					'AWARDED'                  => Date('Y-m-d'),
					'AMOUNT'                   => $amount * -1,
					'COMMENT'                  => $comment
				);
				$wpdb->insert(VTM_TABLE_PREFIX . "CHARACTER_TEMPORARY_STAT",
					$data,
					array ('%d', '%d', '%d', '%s', '%d', '%s')
				);
				
				// report done
				if ($wpdb->insert_id == 0) {
					$output .= "<p style='color:red'><b>Error:</b>Could not update $stat</p>";
				} else {
					vtm_touch_last_updated($characterID);
					$output .= "<p style='color:green'>Updated $stat</p>";
				}
				// spend again button
				$output .= "<input type='hidden' name='$stagename' value='detail'>";
				$output .= "<input type='submit' name='dospend_$buttonID' value='Spend more $stat' class='gvxp_submit'>";
				
				break;
			}
		case "detail":
			$output .= "<input type='hidden' name='$stagename' value='validate'>";
			$output .= "<label>I am spending</label><input type='text' name='amount_$buttonID' value='$amount' size=5 >
						<label> of my $currentstat $stat points on:</label><input type='text' name='comment_$buttonID' value='$comment' size=30 >";
			$output .= "<input type='submit' name='confirm_$buttonID' value='Confirm $stat spend' class='gvxp_submit'>";
			break;
		default:
			$output .= "<input type='hidden' name='$stagename' value='detail'>";
			$output .= "<input type='submit' name='dospend_$buttonID' value='Spend $stat' class='gvxp_submit'>";
		
	}
	

	$output .= "</form></div>";
	return $output;
}
add_shortcode('spend_button', 'vtm_print_spend_button');

function vtm_print_inbox_summary($atts, $content=null) {
	$character = vtm_establishCharacter('');
	$characterID = vtm_establishCharacterID($character);
	
	$output = "";
	
	if (get_option( 'vtm_feature_pm', '0' ) == 0) return "<p>Private nessaging feature disabled</p>";
	if (!is_user_logged_in()) return "<p>You must be logged in to view this content</p>";
	
	//get_posts(...)?
	//https://codex.wordpress.org/Template_Tags/get_posts
	
	// Option to list latest x posts
	extract(shortcode_atts(array (
		"list" => 5,		// <number of posts>
		), $atts)
	);
	
	if (!isset($_REQUEST["vtmpmtab"]) || (isset($_REQUEST["vtmpmtab"]) && $_REQUEST["vtmpmtab"] == "unread"))
		$vtmtab = "unread";
	elseif ($_REQUEST["vtmpmtab"] == "sent") {
		$vtmtab = "sent";
	}
	else {
		$vtmtab = "all";
	}
	
	
	$newmsgurl = admin_url('post-new.php?post_type=vtmpm');
	$inboxurl  = admin_url('edit.php?post_type=vtmpm');
	$thispage = get_page_link();
	$taboutput = "<p>
		<a href='$thispage?vtmpmtab=unread'>Unread</a> | 
		<a href='$thispage?vtmpmtab=read'>Read</a> | 
		<a href='$thispage?vtmpmtab=sent'>Sent</a> | 
		<a href='$newmsgurl'>Write New Message</a>
		</p>";
		
	if (vtm_isST() && empty($characterID)) {
		$meta_query = array();
	} 
	elseif ($vtmtab == "sent") {
		$output .= $taboutput;
		$meta_query = array(
				array(
					'key'=>'_vtmpm_from_characterID',
					'value'=> "$characterID",
					'compare'=>'==',
				),
		);
	}
	else {
		$output .= $taboutput;
		$meta_query = array(
				array(
					'key'=>'_vtmpm_to_characterID',
					'value'=> "$characterID",
					'compare'=>'==',
				),
		);
		if ($vtmtab == "unread") {
			$meta_query[] = array(
					'key'=>'_vtmpm_to_status',
					'value'=> "unread",
					'compare'=>'==',
				);
		} else {
			$meta_query[] = array(
					'key'=>'_vtmpm_to_status',
					'value'=> "read",
					'compare'=>'==',
				);
		}
	}
	
	$args = array(
		'posts_per_page'   => $list + 1, // need the extra +1 to generate the "More.." link
		'orderby'          => 'date',
		'order'            => 'DESC',
		'post_type'        => 'vtmpm',
		'post_status'      => 'publish',
		'meta_query'       => $meta_query
	);
	$posts_array = get_posts( $args );
	
	
	$allpm = array();
	foreach ($posts_array as $post) {
		
		$postID = $post->ID;
		$status = get_post_meta( $postID, '_vtmpm_to_status', true );
				
		$title = get_the_title($post);
		if (empty($title)) {$title = "[No Subject]";}
		
		$fromid = get_post_meta( $postID, '_vtmpm_from_characterID', true );
		$authorid = get_post_field( 'post_author', $postID );
		
		$pm = array(
			"title" => $title,
			"from" => vtm_formatOutput(vtm_pm_getchfromid($fromid)),
			"to"   => vtm_formatOutput(vtm_pm_getchfromid(get_post_meta( $postID, '_vtmpm_to_characterID', true ))),
			"authorch" => vtm_formatOutput(vtm_pm_getchfromauthid($authorid)),
			"status" => $status,
			"class" => $status == 'unread' ? "vtm_pmunread" : "",
			"permalink" => get_the_permalink($post),
			"date" => get_the_date('', $postID)
		);
		
		$allpm[] = $pm;

	}
	


	$heading = "<tr><th>Title</th>";
	$heading .= "<th>From</th>";
	if (vtm_isST() || $vtmtab == "sent") {
		$heading .= "<th>To</th>";
		$cols = 6;
	} else {
		$cols = 5;
	}
	$heading .= "<th>Date</th><th>Status</th></tr>\n"; 
	
	$output .= "<div><table>";
	$output .= $heading;
	if (count($allpm) == 0) {
		$output .= "<tr><td colspan='$cols'>No messages to display</td></tr>";
	} else {
		$count = 0;
		foreach ($allpm as $pm) {
			$output .= "<tr>";
			$output .= "<td><a href='{$pm["permalink"]}'>{$pm["title"]}</a></td>";
			$output .= "<td>{$pm["from"]}</td>";
			if (vtm_isST() || $vtmtab == "sent") {
				$output .= "<td>{$pm["to"]}</td>";
			}
			$output .= "<td>{$pm["date"]}</td>";
			$output .= "<td>{$pm["status"]}</td>";
			$output .= "</tr>\n";
			
			$count++;
			if ($count == $list) {
				if (count($allpm) > $list)
					$output .= "<tr><td colspan='$cols'><a href='" . admin_url('edit.php?post_type=vtmpm') . "'>More...</a></td></tr>";
				break;
			}
		}
	}
	$output .= "</table>";
	$output .= "</div>\n";
	
	
	// TAB - NEW MESSAGES
	$output .= "";

	
	
	return $output;
}
add_shortcode('inbox_summary', 'vtm_print_inbox_summary');

?>