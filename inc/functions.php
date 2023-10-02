<?php


/* FUNCTIONS
----------------------------------------------------------------- */

function vtm_get_stat_info() {
	global $wpdb;

	$sql = "SELECT NAME, ID FROM " . VTM_TABLE_PREFIX . "STAT;";
	$statinfo = $wpdb->get_results($sql, OBJECT_K);
	
	return $statinfo;
}
function vtm_get_booknames() {

	global $wpdb;

	$sql = "SELECT ID, NAME FROM " . VTM_TABLE_PREFIX . "SOURCE_BOOK;";
	$booklist = $wpdb->get_results($sql);
	
	return $booklist;
}
function vtm_get_disciplines() {

	global $wpdb;

	$sql = "SELECT ID, NAME FROM " . VTM_TABLE_PREFIX . "DISCIPLINE;";
	$list = $wpdb->get_results($sql);
	
	return $list;
}
function vtm_get_costmodels() {

	global $wpdb;

	$sql = "SELECT ID, NAME FROM " . VTM_TABLE_PREFIX . "COST_MODEL;";
	$list = $wpdb->get_results($sql);
	
	return $list;
}
function vtm_get_skilltypes() {

	global $wpdb;

	$sql = "SELECT ID, NAME FROM " . VTM_TABLE_PREFIX . "SKILL_TYPE;";
	$list = $wpdb->get_results($sql);
	
	return $list;
}
function vtm_get_templates() {

	global $wpdb;

	$sql = "SELECT ID, NAME FROM " . VTM_TABLE_PREFIX . "CHARGEN_TEMPLATE;";
	$list = $wpdb->get_results($sql);
	
	return $list;
}
function vtm_get_natures() {

	global $wpdb;

	$sql = "SELECT ID, NAME FROM " . VTM_TABLE_PREFIX . "NATURE;";
	$list = $wpdb->get_results($sql);
	
	return $list;
}
function vtm_get_backgrounds() {

	global $wpdb;

	$sql = "SELECT ID, NAME FROM " . VTM_TABLE_PREFIX . "BACKGROUND WHERE VISIBLE = 'Y';";
	$list = $wpdb->get_results($sql);
	
	return $list;
}

function vtm_get_magic_disciplines($objectk = 0, $by_discname = 0) {
	global $wpdb;

	if ($by_discname)
		$txt = "disc.NAME, disc.ID";
	else
		$txt = "disc.ID, disc.NAME";
	
	$sql = "SELECT $txt, path.cnt FROM 
				" . VTM_TABLE_PREFIX . "DISCIPLINE disc
				LEFT JOIN (
					SELECT COUNT(ID) as cnt, DISCIPLINE_ID 
					FROM " . VTM_TABLE_PREFIX . "PATH
					GROUP BY DISCIPLINE_ID
				) path
				ON path.DISCIPLINE_ID = disc.ID
			WHERE NOT(ISNULL(path.cnt))";
	
	if ($objectk) {
		$list = $wpdb->get_results($sql, OBJECT_K);
	} else {
		$list = $wpdb->get_results($sql);
	}
	
	if ($by_discname && $objectk) {
		$tmp = $list;
		foreach ($list as $key => $data) {
			$tmp[sanitize_key($key)] = $data;
		}
		$list = $tmp;
	}
	
	//print "SQL: $sql";
	//print_r($list);
	return $list;
	
}

function vtm_get_profile_display() {

	global $wpdb;

	$sql = "SELECT ID, NAME FROM " . VTM_TABLE_PREFIX . "PROFILE_DISPLAY;";
	$list = $wpdb->get_results($sql);
	
	return $list;
}
function vtm_get_sectors($showhidden = false) {

	global $wpdb;

	$sql = "SELECT ID, NAME FROM " . VTM_TABLE_PREFIX . "SECTOR";
	if (!$showhidden)
		$sql .= " WHERE VISIBLE = 'Y'";
	$list = $wpdb->get_results($sql);
	
	return $list;
}
function vtm_get_stlink_page($stlinkvalue) {
	global $wpdb;

	$linkname = "vtm_link_" . $stlinkvalue;
	$option = get_option( $linkname,'0' );
	$pageid = $option[$linkname];
	
	return $pageid;

}
function vtm_get_stlink_url($stlinkvalue, $fullurl = false) {
	global $wpdb;

	$pageid = vtm_get_stlink_page($stlinkvalue);
	$url = get_page_link($pageid);
	
	return $url;

}
function vtm_get_total_xp($playerID = 0, $characterID = 0) {
	global $wpdb;
	global $vtmglobal;
		
	if ($vtmglobal['config']->ASSIGN_XP_BY_PLAYER == 'Y' && $playerID == 0 & $characterID != 0) {
		$sql = $wpdb->prepare("SELECT PLAYER_ID FROM " . VTM_TABLE_PREFIX . "CHARACTER WHERE ID = %s", $characterID);
		$playerID = $wpdb->get_var($sql);
		//echo "<li>Working out playerID = $playerID</li>";
	}
	
	$filteron = $vtmglobal['config']->ASSIGN_XP_BY_PLAYER == 'Y' ? "PLAYER_ID" : "CHARACTER_ID";
	$filterid = $vtmglobal['config']->ASSIGN_XP_BY_PLAYER == 'Y' ? $playerID   : $characterID;
	
	$sql = "SELECT SUM(xpspends.amount) as total
			FROM
				" . VTM_TABLE_PREFIX . "PLAYER_XP as xpspends
			WHERE
				xpspends.$filteron = '%s'";
	$sql = $wpdb->prepare($sql, $filterid);
	
	//echo "<p>SQL: $sql</p>";
	$result = $wpdb->get_var($sql);
	
	return $result;

}
function vtm_get_clans() {

	global $wpdb;

	$sql = "SELECT ID, NAME FROM " . VTM_TABLE_PREFIX . "CLAN ORDER BY NAME;";
	$list = $wpdb->get_results($sql);
	
	return $list;
}
function vtm_get_domains() {

	global $wpdb;

	$sql = "SELECT ID, NAME FROM " . VTM_TABLE_PREFIX . "DOMAIN;";
	$list = $wpdb->get_results($sql);
	
	return $list;
}
function vtm_get_player_status() {

	global $wpdb;

	$sql = "SELECT ID, NAME FROM " . VTM_TABLE_PREFIX . "PLAYER_STATUS;";
	$list = $wpdb->get_results($sql);
	
	return $list;
}
function vtm_get_player_type() {

	global $wpdb;

	$sql = "SELECT ID, NAME FROM " . VTM_TABLE_PREFIX . "PLAYER_TYPE ORDER BY NAME;";
	$list = $wpdb->get_results($sql);
	
	//print_r($list);
	
	return $list;
}
function vtm_get_pm_typefromid($typeID) {

	global $wpdb;

	$sql = "SELECT NAME FROM " . VTM_TABLE_PREFIX . "PM_TYPE
		WHERE ID = %s;";
	$list = $wpdb->get_var($wpdb->prepare($sql, $typeID));
	
	//print_r($list);
	
	return $list;
}
function vtm_get_pm_typeidfromcode($code) {

	global $wpdb;

	$sql = "SELECT PM_TYPE_ID FROM " . VTM_TABLE_PREFIX . "CHARACTER_PM_ADDRESS
		WHERE PM_CODE = %s;";
	$list = $wpdb->get_var($wpdb->prepare($sql, $code));
	
	//print_r($list);
	
	return $list;
}
function vtm_get_generations() {

	global $wpdb;

	$sql = "SELECT ID, NAME FROM " . VTM_TABLE_PREFIX . "GENERATION ORDER BY BLOODPOOL, MAX_DISCIPLINE;";
	$list = $wpdb->get_results($sql);
	
	//echo "<p>SQL: $sql</p>";
	//print_r($list);
	
	return $list;
}
function vtm_get_sects($visible_only = false) {

	global $wpdb;

	$where = $visible_only ? "WHERE VISIBLE = 'Y'" : "";

	$sql = "SELECT ID, NAME FROM " . VTM_TABLE_PREFIX . "SECT $where;";
	$list = $wpdb->get_results($sql);
	
	return $list;
}
function vtm_get_characters() {

	global $wpdb;

	$sql = "SELECT ch.ID, ch.NAME 
			FROM 
				" . VTM_TABLE_PREFIX . "CHARACTER ch,
				" . VTM_TABLE_PREFIX . "PLAYER pl,
				" . VTM_TABLE_PREFIX . "PLAYER_STATUS ps,
				" . VTM_TABLE_PREFIX . "CHARACTER_STATUS cs,
				" . VTM_TABLE_PREFIX . "CHARGEN_STATUS cgs
			WHERE 
				ch.PLAYER_ID = pl.ID
                AND pl.PLAYER_STATUS_ID = ps.ID
				AND ch.CHARACTER_STATUS_ID = cs.ID
				AND cgs.ID = ch.CHARGEN_STATUS_ID
				AND ch.VISIBLE = 'Y'
				AND ch.DELETED = 'N'
				AND cgs.NAME = 'Approved'
				AND ps.NAME = 'Active';";
	$list = $wpdb->get_results($sql);
	
	return $list;
}

function vtm_get_characters_wide() {

	global $wpdb;

	$sql = "SELECT
				ch.ID as characterID, 
				ch.NAME characterName,
				ch.WORDPRESS_ID wordpress_id, 
				pl.NAME player, 
				cs.NAME char_status,
				clans.NAME as clan
			FROM 
				" . VTM_TABLE_PREFIX . "CHARACTER ch,
				" . VTM_TABLE_PREFIX . "PLAYER pl,
				" . VTM_TABLE_PREFIX . "PLAYER_STATUS ps,
				" . VTM_TABLE_PREFIX . "CHARACTER_STATUS cs,
				" . VTM_TABLE_PREFIX . "CHARGEN_STATUS cgs,
				" . VTM_TABLE_PREFIX . "CLAN clans
			WHERE 
				ch.PLAYER_ID = pl.ID
                AND pl.PLAYER_STATUS_ID = ps.ID
				AND ch.CHARACTER_STATUS_ID = cs.ID
				AND cgs.ID = ch.CHARGEN_STATUS_ID
				AND clans.ID = ch.PUBLIC_CLAN_ID
				AND ch.VISIBLE = 'Y'
				AND ch.DELETED = 'N'
				AND cgs.NAME = 'Approved'
				AND ps.NAME = 'Active';";
	$list = $wpdb->get_results($sql);
	
	return $list;
}

function vtm_get_character_templateid($characterID) {
	global $wpdb;
	
	$sql = "SELECT TEMPLATE_ID FROM " . VTM_TABLE_PREFIX . "CHARACTER_GENERATION
		WHERE CHARACTER_ID = %s";
	$sql = $wpdb->prepare($sql, $characterID);
	$templateID = $wpdb->get_var($sql);
	if (empty($templateID)) {
		$sql = "SELECT MIN(TEMPLATE_ID) FROM " . VTM_TABLE_PREFIX . "CHARACTER_GENERATION";
		$templateID = $wpdb->get_var($sql);
	}
	return $templateID;
	
}
function vtm_get_character_primarypath($characterID, $disciplineID) {
	global $wpdb;

	$sql = "SELECT 
				cpp.PATH_ID,
				path.NAME
		FROM 
			" . VTM_TABLE_PREFIX . "CHARACTER_PRIMARY_PATH cpp,
			" . VTM_TABLE_PREFIX . "PATH path
		WHERE 
			CHARACTER_ID = '%s'
			AND path.ID = cpp.PATH_ID
			AND cpp.DISCIPLINE_ID = '%s'";
	$sql = $wpdb->prepare($sql, $characterID, $disciplineID);
	$results = $wpdb->get_row($sql);
	
	//print_r($results);
	
	return $results;
}
function vtm_get_primarypath_default($templateID, $disciplineID, $clanID) {
	global $wpdb;
	
	$sql = "SELECT 
			disc.ID as discid, 
			pth.ID as pathid,
			pth.NAME as name
		FROM
			" . VTM_TABLE_PREFIX . "CHARGEN_PRIMARY_PATH cpp,
			" . VTM_TABLE_PREFIX . "DISCIPLINE disc,
			" . VTM_TABLE_PREFIX . "PATH pth
		WHERE
			cpp.DISCIPLINE_ID = disc.ID
			AND cpp.PATH_ID = pth.ID
			AND cpp.TEMPLATE_ID = '%d'
			AND disc.ID = '%d'
			AND (cpp.CLAN_ID = '%d' OR cpp.CLAN_ID = '')
		";
		
		
	
	$sql = $wpdb->prepare($sql, $templateID, $disciplineID, $clanID);
	
	//print "SQL: $sql";
	$results = $wpdb->get_results($sql, OBJECT_K);
	//print_r($results);

	return $results;
}

function vtm_get_character_email($characterID) {

	global $wpdb;

	$sql = "SELECT EMAIL 
		FROM " . VTM_TABLE_PREFIX . "CHARACTER
		WHERE ID = %s;";
	$email = $wpdb->get_var($wpdb->prepare($sql, $characterID));
	
	return $email;
}

    function vtm_print_name_value_pairs($atts, $content=null) {
        $output = "";
        if (isST()) {
            $output .= "<table>";
            foreach($_POST as $key=>$value) {
				$output .= "<tr><td>" . $key . "</td><td>";
				if (is_array($value))
					foreach($value as $key2 => $val2) {
						$output .= "$key2 = $val2,";
					}
				$output .= "</td></tr>";
            }
            $output .= "</table>";
        }
        return $output;
    }
    add_shortcode('debug_name_value_pairs', 'vtm_print_name_value_pairs');

    function vtm_printSelectCounter($name, $selectedValue, $lowerValue, $upperValue) {
	
		switch ($name) {
			case 'Conscience'  : $upperValue = 5; break;
			case 'Conviction'  : $upperValue = 5; break;
			case 'Self Control': $upperValue = 5; break;
			case 'Courage'     : $upperValue = 5; break;
			case 'Instinct'    : $upperValue = 5; break;
		}
	
        $output = "<select name=\"" . $name . "\">";
        if ($selectedValue == "") {
            $selectedValue = "-100";
            $output .= "<option value=\"-100\">No Value</option>";
        }
        for ($i = $lowerValue; $i <= $upperValue; $i++) {
            $output .= "<option";
            if ((int) $selectedValue == $i) {
                $output .= " selected";
            }
            $output .= ">" . $i . "</option>";
        }
        $output .= "</select>";
        return $output;
    }

    function vtm_listPlayerType() {
        global $wpdb;
        $table_prefix = VTM_TABLE_PREFIX;
        $sql = "SELECT ID, name, description
                        FROM " . $table_prefix . "PLAYER_TYPE ptype
                        ORDER BY description";

        $playerTypes = $wpdb->get_results($sql);
        return $playerTypes;
    }

    function vtm_listPlayerStatus() {
        global $wpdb;
        $table_prefix = VTM_TABLE_PREFIX;
        $sql = "SELECT ID, name, description
                        FROM " . $table_prefix . "PLAYER_STATUS status
                        ORDER BY description";

        $playerTypes = $wpdb->get_results($sql);
        return $playerTypes;
    }
	
	/*
    function vtm_listSTLinks() {
        global $wpdb;
        $table_prefix = VTM_TABLE_PREFIX;
        $sql = "SELECT ID, value, description, link
                        FROM " . $table_prefix . "ST_LINK stlinks
                        ORDER BY ordering";

        $stLinks = $wpdb->get_results($sql);
        return $stLinks;
    }
	*/
	
    function vtm_listPlayers($playerStatus, $playerType, $options = array()) {
        global $wpdb;
        $table_prefix = VTM_TABLE_PREFIX;
		
		if (isset($options["show-inactive"]) && $options["show-inactive"] == "last") {
			$sortby = "statusname, name";
		} else {
			$sortby = "name";
		}

        $statusClause = "";
		$playerStatusID = "";
        if ($playerStatus != null && $playerStatus != "") {
			$playerStatusID = $wpdb->get_var($wpdb->prepare("SELECT ID FROM " . $table_prefix . "PLAYER_STATUS WHERE NAME = %s", $playerStatus));
            $statusClause = " AND player_status_id = %d ";
        }

        $typeClause = "";
        if ($playerType != null && $playerType != "") {
            $typeClause = " AND player_type_id = %d ";
        }

        $sql = "SELECT player.ID, player.name, pstatus.name statusname, ptype.name typename
                        FROM " . $table_prefix . "PLAYER player,
                             " . $table_prefix . "PLAYER_STATUS pstatus,
                             " . $table_prefix . "PLAYER_TYPE ptype
                        WHERE player.player_status_id = pstatus.id
                          AND player.player_type_id   = ptype.id
						  AND player.deleted = 'N'
                          " . $statusClause . $typeClause . "
                        ORDER BY {$sortby}";

        if ($playerStatusID != null && $playerStatusID != "" && $playerType != null && $playerType != "") {
            $sql = $wpdb->prepare($sql, $playerStatusID, $playerType);
        }
        else if ($playerStatusID != null && $playerStatusID != "") {
            $sql = $wpdb->prepare($sql, $playerStatusID);
        }
        else if ($playerType != null && $playerType != "") {
            $sql = $wpdb->prepare($sql, $playerType);
        }

        $players = $wpdb->get_results($sql);
        return $players;
    }

    function vtm_listClans() {
        global $wpdb;
        $table_prefix = VTM_TABLE_PREFIX;
        $sql = "SELECT ID, name
                        FROM " . $table_prefix . "CLAN
                        ORDER BY name";

        $clans = $wpdb->get_results($sql);
        return $clans;
    }

    function vtm_listGenerations() {
        global $wpdb;
        $table_prefix = VTM_TABLE_PREFIX;
        $sql = "SELECT ID, name
                FROM " . $table_prefix . "GENERATION
                ORDER BY BLOODPOOL, MAX_DISCIPLINE";

        $generations = $wpdb->get_results($sql);
        return $generations;
    }

    function vtm_listCharacterStatuses() {
        global $wpdb;
        $table_prefix = VTM_TABLE_PREFIX;
        $sql = "SELECT ID, name
                        FROM " . $table_prefix . "CHARACTER_STATUS
                        ORDER BY name";

        $characterStatuses = $wpdb->get_results($sql);
        return $characterStatuses;
    }

    function vtm_listCharacterTypes() {
        global $wpdb;
        $table_prefix = VTM_TABLE_PREFIX;
        $sql = "SELECT ID, name
                        FROM " . $table_prefix . "CHARACTER_TYPE
                        ORDER BY name";

        $characterTypes = $wpdb->get_results($sql);
        return $characterTypes;
    }

    function vtm_listRoadsOrPaths() {
        global $wpdb;
        $table_prefix = VTM_TABLE_PREFIX;
        $sql = "SELECT ID, name
                        FROM " . $table_prefix . "ROAD_OR_PATH
                        ORDER BY name";

        $roadsOrPaths = $wpdb->get_results($sql);
        return $roadsOrPaths;
    }

    function vtm_listDomains() {
        global $wpdb;
        $table_prefix = VTM_TABLE_PREFIX;
        $sql = "SELECT ID, name
                        FROM " . $table_prefix . "DOMAIN
                        ORDER BY name";

        $domains = $wpdb->get_results($sql);
        return $domains;
    }

    function vtm_listOffices($showNotVisible) {
        global $wpdb;
        $table_prefix = VTM_TABLE_PREFIX;

        $visible_sector  = " VISIBLE = 'Y' ";
        if ($showNotVisible == "Y") {
            $visible_sector = "";
        }
        $sql = "SELECT ID, name
                        FROM " . $table_prefix . "OFFICE ";
        if ($visible_sector != "") {
            $sql .= "WHERE " . $visible_sector;
        }
        $sql .= " ORDER BY ordering, name";

        $offices = $wpdb->get_results($sql);
        return $offices;
    }

	function vtm_listCharactersForPlayer($playerID){
       global $wpdb;
        $table_prefix = VTM_TABLE_PREFIX;
		$table = $table_prefix . "CHARACTER";
		
		$sql = "SELECT ID,NAME,VISIBLE FROM $table WHERE PLAYER_ID = %s AND DELETED = 'N'";
        $results = $wpdb->get_results($wpdb->prepare($sql, $playerID));
		
		return $results;
	}

    function vtm_listCharacters($group, $activeCharacter, $playerName, $activePlayer, $showNotVisible) {
        global $wpdb;
        $table_prefix = VTM_TABLE_PREFIX;
        $grouping_sector = "";
        $activeCharacter_sector = "";
        $activePlayer_sector = "";
        $playerName_sector = "";
        $visible_sector  = " AND chara.VISIBLE = 'Y' ";

        if ($group != "") {
            $grouping_sector = "AND ctype.name = %s ";
        }
        if ($activeCharacter != "") {
            $activeCharacter_sector = "AND cstatus.name = %s ";
        }
        if ($activePlayer != "") {
            $activePlayer_sector = "AND pstatus.name = %s ";
        }
        if ($playerName != "") {
            $playerName_sector = "AND player.name = %s ";
        }
        if ($showNotVisible == "Y") {
            $visible_sector = "";
        }

        $sql = "SELECT chara.id,
                               chara.name cname,
                               ctype.name typename,
                               cstatus.name cstatusname,
                               chara.visible,
                               player.name pname,
                               pstatus.name pstatusname,
                               chara.wordpress_id wid
                        FROM " . $table_prefix . "CHARACTER chara,
                             " . $table_prefix . "CHARACTER_TYPE ctype,
                             " . $table_prefix . "CHARACTER_STATUS cstatus,
                             " . $table_prefix . "PLAYER player,
                             " . $table_prefix . "PLAYER_STATUS pstatus
                       WHERE chara.character_type_id = ctype.id
                         AND chara.character_status_id = cstatus.id
                         AND chara.player_id = player.id
                         AND player.player_status_id = pstatus.id
                         AND chara.DELETED != 'Y' "
            . $grouping_sector
            . $visible_sector
            . $activeCharacter_sector
            . $activePlayer_sector
            . $playerName_sector . "
                       ORDER BY cstatus.id, ctype.id, chara.name";

        if ($group != "" && $activeCharacter != "" && $activePlayer != "" && $playerName != "") {
            $sql = $wpdb->prepare($sql, $group, $activeCharacter, $activePlayer, $playerName);
        }
        else if ($group != "" && $activeCharacter != "" && $activePlayer != "") {
            $sql = $wpdb->prepare($sql, $group, $activeCharacter, $activePlayer);
        }
        else if ($group != "" && $activeCharacter != "" && $playerName != "") {
            $sql = $wpdb->prepare($sql, $group, $activeCharacter, $playerName);
        }
        else if ($group != "" && $activePlayer != "" && $playerName != "") {
            $sql = $wpdb->prepare($sql, $group, $activePlayer, $playerName);
        }
        else if ($activeCharacter != "" && $activePlayer != "" && $playerName != "") {
            $sql = $wpdb->prepare($sql, $activeCharacter, $activePlayer, $playerName);
        }
        else if ($group != "" && $activeCharacter != "") {
            $sql = $wpdb->prepare($sql, $group, $activeCharacter);
        }
        else if ($group != "" && $activePlayer != "") {
            $sql = $wpdb->prepare($sql, $group, $activePlayer);
        }
        else if ($group != "" && $playerName != "") {
            $sql = $wpdb->prepare($sql, $group, $playerName);
        }
        else if ($activeCharacter != "" && $activePlayer != "") {
            $sql = $wpdb->prepare($sql, $activeCharacter, $activePlayer);
        }
        else if ($activeCharacter != "" && $playerName != "") {
            $sql = $wpdb->prepare($sql, $activeCharacter, $playerName);
        }
        else if ($activePlayer != "" && $playerName != "") {
            $sql = $wpdb->prepare($sql, $activePlayer, $playerName);
        }
        else if ($group != "") {
            $sql = $wpdb->prepare($sql, $group);
        }
        else if ($activeCharacter != "") {
            $sql = $wpdb->prepare($sql, $activeCharacter);
        }
        else if ($activePlayer != "") {
            $sql = $wpdb->prepare($sql, $activePlayer);
        }
        else if ($playerName != "") {
            $sql = $wpdb->prepare($sql, $playerName);
        }

        $characters = $wpdb->get_results($sql);
        return $characters;
    }

    function vtm_listStats() {
        global $wpdb;
        $table_prefix = VTM_TABLE_PREFIX;
        $sql = "SELECT id, name, grouping
                        FROM " . $table_prefix . "STAT
                        ORDER BY ordering";

        return $wpdb->get_results($sql);
    }

    function vtm_listXpReasons() {
        global $wpdb;
        $table_prefix = VTM_TABLE_PREFIX;
        $sql = "SELECT id, name
                        FROM " . $table_prefix . "XP_REASON
                        ORDER BY id";

        return $wpdb->get_results($sql);
    }

    function vtm_listPathReasons() {
        global $wpdb;
        $table_prefix = VTM_TABLE_PREFIX;
        $sql = "SELECT id, name
                    FROM " . $table_prefix . "PATH_REASON
                    ORDER BY id";

        return $wpdb->get_results($sql);
    }

    function vtm_listTemporaryStatReasons() {
        global $wpdb;
        $table_prefix = VTM_TABLE_PREFIX;
        $sql = "SELECT id, name
                    FROM " . $table_prefix . "TEMPORARY_STAT_REASON
                    ORDER BY id";

        return $wpdb->get_results($sql);

    }

    function vtm_listSkills($group, $showNotVisible) {
        global $wpdb;
        $table_prefix = VTM_TABLE_PREFIX;
        $grouping_sector = "";
        $visible_sector  = " AND skill.VISIBLE = 'Y' ";

        if ($group != "") {
            $grouping_sector = " skilltype.name = %s ";
        }
        if ($showNotVisible == "Y") {
            $visible_sector = "";
        }

        $sql = "SELECT skill.id, skill.name, skill.description, skilltype.name as grouping, skill.visible
                        FROM  " . $table_prefix . "SKILL skill,
							 " . $table_prefix . "SKILL_TYPE skilltype";
        $sql .= " WHERE skill.skill_type_id = skilltype.id";
		if ($grouping_sector != "" && $visible_sector != "") {
			$sql .= " AND " . $grouping_sector . " AND " . $visible_sector;
		}
		elseif ($grouping_sector != "") {
			$sql .= " AND " . $grouping_sector;
		}
		else {
			$sql .= $visible_sector;
		}
        $sql .= " ORDER BY skill.name";

        if ($grouping_sector != "") {
            $sql = $wpdb->prepare($sql, $group);
        }
		
		//echo "<p>SQL: $sql</p>";
        $skills = $wpdb->get_results($sql);
        return $skills;
    }

    function vtm_listDisciplines($showNotVisible) {
        global $wpdb;
        $table_prefix = VTM_TABLE_PREFIX;
        $visible_sector  = " VISIBLE = 'Y' ";

        if ($showNotVisible == "Y") {
            $visible_sector = "";
        }

        $sql = "SELECT id, name, description, visible
                        FROM  " . $table_prefix . "DISCIPLINE ";
        if ($visible_sector != "") {
            $sql .= "WHERE " . $visible_sector;
        }
        $sql .= " ORDER BY name";

        $disciplines = $wpdb->get_results($sql);
        return $disciplines;
    }

    function vtm_listComboDisciplines($showNotVisible) {
        global $wpdb;
        $table_prefix = VTM_TABLE_PREFIX;
        $visible_sector  = " VISIBLE = 'Y' ";

        if ($showNotVisible == "Y") {
            $visible_sector = "";
        }

        $sql = "SELECT id, name, description, visible
                        FROM  " . $table_prefix . "COMBO_DISCIPLINE ";
        if ($visible_sector != "") {
            $sql .= "WHERE " . $visible_sector;
        }
        $sql .= " ORDER BY name";

        $combo_disciplines = $wpdb->get_results($sql);
        return $combo_disciplines;
    }

    function vtm_listPaths($showNotVisible) {
        global $wpdb;
        $table_prefix = VTM_TABLE_PREFIX;
        $visible_sector  = " AND path.VISIBLE = 'Y' ";

        if ($showNotVisible == "Y") {
            $visible_sector = "";
        }

        $sql = "SELECT path.id, path.name, path.description, path.visible, discipline.name disname
                        FROM  " . $table_prefix . "PATH path, "
            . $table_prefix . "DISCIPLINE discipline
                        WHERE path.discipline_id = discipline.id  " . $visible_sector .
            " ORDER BY disname, path.name";

        $paths = $wpdb->get_results($sql);
        return $paths;
    }

    function vtm_listRituals($showNotVisible) {
        global $wpdb;
        $table_prefix = VTM_TABLE_PREFIX;
        $visible_sector  = " AND VISIBLE = 'Y' ";

        if ($showNotVisible == "Y") {
            $visible_sector = "";
        }

        $sql = "SELECT ritual.id, ritual.name, ritual.description, ritual.level, ritual.visible, discipline.name disname
                        FROM  " . $table_prefix . "RITUAL ritual, "
            . $table_prefix . "DISCIPLINE discipline
                        WHERE ritual.discipline_id = discipline.id  " . $visible_sector .
            " ORDER BY disname, ritual.level, ritual.name";

        $rituals = $wpdb->get_results($sql);
        return $rituals;
    }

    function vtm_listBackgrounds($group, $showNotVisible) {
        global $wpdb;
        $table_prefix = VTM_TABLE_PREFIX;
        $grouping_sector = "";
        $visible_sector  = " VISIBLE = 'Y' ";

        if ($group != "") {
            $grouping_sector = " grouping = %s ";
        }
        if ($showNotVisible == "Y") {
            $visible_sector = "";
        }

        $sql = "SELECT id, name, description, grouping, visible
                        FROM  " . $table_prefix . "BACKGROUND ";
        if ($grouping_sector != "" || $visible_sector != "") {
            $sql .= "WHERE ";
            if ($grouping_sector != "" && $visible_sector != "") {
                $sql .= $grouping_sector . " AND " . $visible_sector;
            }
            elseif ($grouping_sector != "") {
                $sql .= $grouping_sector;
            }
            else {
                $sql .= $visible_sector;
            }
        }
        $sql .= " ORDER BY name";

        if ($grouping_sector != "") {
            $sql = $wpdb->prepare($sql, $group);
        }

        $backgrounds = $wpdb->get_results($sql);
        return $backgrounds;
    }

    function vtm_listMerits($group, $showNotVisible) {
        global $wpdb;
        $table_prefix = VTM_TABLE_PREFIX;
        $grouping_sector = "";
        $visible_sector  = " VISIBLE = 'Y' ";

        if ($group != "") {
            $grouping_sector = " grouping = %s ";
        }
        if ($showNotVisible == "Y") {
            $visible_sector = "";
        }

        $sql = "SELECT id, name, description, grouping, visible, value
                        FROM  " . $table_prefix . "MERIT ";
        if ($grouping_sector != "" || $visible_sector != "") {
            $sql .= "WHERE ";
            if ($grouping_sector != "" && $visible_sector != "") {
                $sql .= $grouping_sector . " AND " . $visible_sector;
            }
            elseif ($grouping_sector != "") {
                $sql .= $grouping_sector;
            }
            else {
                $sql .= $visible_sector;
            }
        }
        $sql .= " ORDER BY name";

        if ($grouping_sector != "")  {
            $sql = $wpdb->prepare($sql, $group);
        }
		
		//echo "<p>SQL: $sql</p>";

        $merits = $wpdb->get_results($sql);
        return $merits;
    }


    function vtm_establishCharacterID($character = '') {
        global $wpdb;
		global $vtmglobal;

		if (!empty($character)) {
			$sql = "SELECT id
					FROM " . VTM_TABLE_PREFIX . "CHARACTER
					WHERE WORDPRESS_ID = %s";
			$cid = $wpdb->get_var($wpdb->prepare($sql, $character));
		} else {
			$cid = "";
		}
		
		if (empty($cid) && isset($_REQUEST['characterID'])) {
			$cid = $_REQUEST['characterID'];
		}
		
		if ($character == '' || 
			(isset($vtmglobal['character']) && $character == $vtmglobal['character']) ) {
			$vtmglobal['characterID'] = $cid;
		}
		
        return $cid;
    }

    function vtm_establishPlayerID($character) {
        global $wpdb;
        $table_prefix = VTM_TABLE_PREFIX;
        $sql = "SELECT player_id
                        FROM " . $table_prefix . "CHARACTER
                        WHERE WORDPRESS_ID = %s";
        $playerIDs = $wpdb->get_results($wpdb->prepare($sql, $character));
        $pid = null;
        foreach ($playerIDs as $playerID) {
            $pid = $playerID->player_id;
        }
        return $pid;
    }

    function vtm_establishXPReasonID($xpReasonString) {
        global $wpdb;
        $table_prefix = VTM_TABLE_PREFIX;
        $sql = "SELECT id
                    FROM " . $table_prefix . "XP_REASON
                    WHERE NAME = %s";
        $reasonIDs = $wpdb->get_results($wpdb->prepare($sql, $xpReasonString));
        $rid = null;
        foreach ($reasonIDs as $reasonID) {
            $rid = $reasonID->id;
        }
        return $rid;
    }

    function vtm_establishPathReasonID($pathReasonString) {
        global $wpdb;
        $table_prefix = VTM_TABLE_PREFIX;
        $sql = "SELECT id
                        FROM " . $table_prefix . "PATH_REASON
                        WHERE NAME = %s";
        $reasonIDs = $wpdb->get_results($wpdb->prepare($sql, $pathReasonString));
        $rid = null;
        foreach ($reasonIDs as $reasonID) {
            $rid = $reasonID->id;
        }
        return $rid;
    }

    function vtm_establishTempStatID($tempStatString) {
        global $wpdb;
        $table_prefix = VTM_TABLE_PREFIX;
        $sql = "SELECT id
                FROM " . $table_prefix . "TEMPORARY_STAT
                WHERE NAME = %s";
        $reasonIDs = $wpdb->get_results($wpdb->prepare($sql, $tempStatString));
        $rid = null;
        foreach ($reasonIDs as $reasonID) {
            $rid = $reasonID->id;
        }
        return $rid;
    }

    function vtm_establishTempStatReasonID($tempStatReasonString) {
        global $wpdb;
        $table_prefix = VTM_TABLE_PREFIX;
        $sql = "SELECT id
                FROM " . $table_prefix . "TEMPORARY_STAT_REASON
                WHERE NAME = %s";
        $reasonIDs = $wpdb->get_results($wpdb->prepare($sql, $tempStatReasonString));
        $rid = null;
        foreach ($reasonIDs as $reasonID) {
            $rid = $reasonID->id;
        }
        return $rid;
    }

    function vtm_getConfig() {
        global $wpdb;
		global $vtmglobal;

		if (!isset($vtmglobal['config'])) {
			$vtmglobal['config'] = new stdClass();
			$vtmglobal['config']->PLACEHOLDER_IMAGE = '';
			$vtmglobal['config']->ANDROID_LINK = '';
			$vtmglobal['config']->HOME_DOMAIN_ID = 1;
			$vtmglobal['config']->HOME_SECT_ID = 1;
			$vtmglobal['config']->DEFAULT_GENERATION_ID = 1;
			$vtmglobal['config']->ASSIGN_XP_BY_PLAYER = 'Y';
			$vtmglobal['config']->USE_NATURE_DEMEANOUR = 'Y';
			$vtmglobal['config']->DISPLAY_BACKGROUND_IN_PROFILE = 0;
			$vtmglobal['config']->WEB_COLUMNS = 1;
		}
		
		if (vtm_table_exists('CONFIG')) {
			// Merge with default settings
			$sql = "SELECT * FROM " . VTM_TABLE_PREFIX . "CONFIG";
			$row = $wpdb->get_row($sql);
			
			if (vtm_count($row) > 0) {
				foreach($row as $property => $value) { 
					$vtmglobal['config']->$property = $value; 
				} 
				
			}
			
		 } //else {

			// $vtmglobal['config']->PLACEHOLDER_IMAGE = '';
			// $vtmglobal['config']->ANDROID_LINK = '';
			// $vtmglobal['config']->HOME_DOMAIN_ID = 1;
			// $vtmglobal['config']->HOME_SECT_ID = 1;
			// $vtmglobal['config']->DEFAULT_GENERATION_ID = 1;
			// $vtmglobal['config']->ASSIGN_XP_BY_PLAYER = 'Y';
			// $vtmglobal['config']->USE_NATURE_DEMEANOUR = 'Y';
			// $vtmglobal['config']->DISPLAY_BACKGROUND_IN_PROFILE = 0;

		// }
		//print_r($vtmglobal);
				
		switch(get_option('vtm_web_pagewidth', 'wide')) {
			case 'wide'  : $vtmglobal['config']->WEB_COLUMNS = 3; break;
			case 'medium': $vtmglobal['config']->WEB_COLUMNS = 3; break;
			case 'narrow': $vtmglobal['config']->WEB_COLUMNS = 1; break;
		}
        $vtmglobal['config']->WEB_PAGEWIDTH = get_option('vtm_web_pagewidth', 'wide');
		
		return $vtmglobal['config'];
    }

    function vtm_changeDisplayNameByID($userID, $newDisplayName) {
        $args = array ('ID' => $userID, 'display_name' => $newDisplayName);
        wp_update_user($args);
        return true;
    }
    function vtm_changeEmailByID($userID, $newEmail) {
        $args = array ('ID' => $userID, 'user_email ' => $newEmail);
        wp_update_user($args);
        return true;
    }

    function vtm_changePasswordByID($userID, $newPassword1, $newPassword2) {

        if ($newPassword1 == $newPassword2) {
            wp_set_password($newPassword1, $userID);
            return true;
        }
        else {
            return false;
        }
    }

	function vtm_touch_last_updated($characterID) {
		global $wpdb;

		$result = $wpdb->update(VTM_TABLE_PREFIX . "CHARACTER",
				array ('LAST_UPDATED' => Date('Y-m-d')),
				array ('ID' => $characterID)
			);
	}
	
    function vtm_handleGVLarpForm() {
        switch($_POST['VTM_FORM']) {
            case "new_player":
                vtm_addNewPlayer($_POST['player_name'], $_POST['player_type'], $_POST['player_status']);
                break;
            case "player_xp":
                vtm_addPlayerXP($_POST['player'], $_POST['character'], $_POST['xp_type'], $_POST['xp_value'], $_POST['comment']);
                break;
            case "master_xp_update":
               vtm_handleMasterXP();
                break;
        }
    }

    if (isset($_POST['VTM_FORM'])) {
        vtm_handleGVLarpForm();
    }

function vtm_numberToDots($base, $input) {
	$number = (int) $input;
	$full  = VTM_PLUGIN_URL . '/images/dot1full.' . VTM_ICON_FORMAT;
	$empty = VTM_PLUGIN_URL . '/images/dot1empty.' . VTM_ICON_FORMAT;
	
	$output = "";
	
	for ($i = 1 ; $i <= $base ; $i++) {
		if ($i <= $input)
			$output .= "<img alt='$i' src='$full' />";
		else
			$output .= "<img alt='$i' src='$empty' />";
	}
	
	return $output;
}
function vtm_numberToBoxes($base, $input) {
	$number = (int) $input;
	$full  = VTM_PLUGIN_URL . '/images/crossclear.' . VTM_ICON_FORMAT;
	$empty = VTM_PLUGIN_URL . '/images/webbox.' . VTM_ICON_FORMAT;
	
	$output = "";
	
	for ($i = 1 ; $i <= $base ; $i++) {
		if ($i <= $input)
			$output .= "<img alt='$i' src='$full' />";
		else
			$output .= "<img alt='$i' src='$empty' />";
			
		if ($i % 10 == 0)
			$output .= "<br />"; 
	}
	
	return $output;
}

function vtm_formatOutput($string, $allowhtml = 0) {
	$string = stripslashes($string);
	//$string = $allowhtml ? $string : htmlspecialchars($string, ENT_QUOTES);
	$string = $allowhtml ? $string : htmlentities($string, ENT_QUOTES);
	return $string;
}

function vtm_get_xp_table($playerID, $characterID, $limit = 0) {
	global $wpdb;
	global $vtmglobal;
	
	$filteron = $vtmglobal['config']->ASSIGN_XP_BY_PLAYER == 'Y' ? "PLAYER_ID" : "CHARACTER_ID";
	$filterid = $vtmglobal['config']->ASSIGN_XP_BY_PLAYER == 'Y' ? $playerID   : $characterID;

	$sqlLimit = $limit == 0 ? '' : "LIMIT $limit";
	
	$sqlSpent = "SELECT 
				player.name as player_name,
				chara.name as char_name,
				xp_reason.name as reason_name,
				xp_spent.amount as amount,
				xp_spent.comment as comment,
				xp_spent.awarded as awarded
			FROM
				" . VTM_TABLE_PREFIX . "CHARACTER chara,
				" . VTM_TABLE_PREFIX . "XP_REASON xp_reason,
				" . VTM_TABLE_PREFIX . "PLAYER_XP xp_spent,
				" . VTM_TABLE_PREFIX . "PLAYER player
			WHERE
				chara.ID = xp_spent.CHARACTER_ID
				AND player.ID = chara.PLAYER_ID
				AND xp_reason.ID = xp_spent.XP_REASON_ID
				AND chara.DELETED != 'Y'
				AND xp_spent.$filteron = %s";
				
	$sqlPending = "SELECT 
				player.name as player_name,
				chara.name as char_name,
				\"Pending\" as reason_name,
				pending.amount as amount,
				CONCAT(pending.comment, IF(ISNULL(pending.specialisation),'',CONCAT(' (', pending.specialisation, ')'))) as comment,
				pending.awarded as awarded
			FROM
				" . VTM_TABLE_PREFIX . "CHARACTER chara,
				" . VTM_TABLE_PREFIX . "PENDING_XP_SPEND pending,
				" . VTM_TABLE_PREFIX . "PLAYER player
			WHERE
				player.ID = chara.PLAYER_ID
				AND pending.CHARACTER_ID = chara.ID
				AND pending.PLAYER_ID = player.ID
				AND chara.DELETED != 'Y'
				AND pending.$filteron = %s";
	
	$sql = "($sqlSpent)
			UNION ALL
			($sqlPending)
			ORDER BY awarded DESC, comment
			$sqlLimit";
	$sql = $wpdb->prepare($sql, $filterid, $filterid);	
	
	//print "<p>SQL: $sql</p>";
	
	return $wpdb->get_results($sql);
	
}

function vtm_get_pm_addresses($characterID = 0) {
	global $wpdb;
	global $vtmglobal;

	if (!vtm_isST() && $characterID == 0){
		if (!isset($vtmglobal['characterID'])) {
			$current_user = wp_get_current_user();
			$vtmglobal['characterID'] = vtm_establishCharacterID($current_user->user_login);
		}
		$characterID = $vtmglobal['characterID'];
	}
	
	if (vtm_isST()) {
		$sql = "SELECT *
				FROM " . VTM_TABLE_PREFIX . "CHARACTER_PM_ADDRESS
				WHERE DELETED = 'N'
				ORDER BY CHARACTER_ID, NAME";
	} else {
		$sql = "SELECT *
				FROM " . VTM_TABLE_PREFIX . "CHARACTER_PM_ADDRESS
				WHERE CHARACTER_ID = %s AND DELETED = 'N'
				ORDER BY CHARACTER_ID, NAME";
		$sql = $wpdb->prepare($sql, $characterID);
	}
	
	return $wpdb->get_results($sql);
}

function vtm_get_default_address($characterID) {
	global $wpdb;
	
	$sql = "SELECT *
			FROM " . VTM_TABLE_PREFIX . "CHARACTER_PM_ADDRESS
			WHERE CHARACTER_ID = %s AND ISDEFAULT = 'Y'
			AND DELETED = 'N'";
	$sql = $wpdb->prepare($sql, $characterID);
	$address = $wpdb->get_row($sql);
	
	// Use the post office address if it's enable and we don't
	// have any other choices
	if ($wpdb->num_rows == 0 && get_option( 'vtm_pm_ic_postoffice_enabled', '0' ) == '1') {		
		$address = new stdClass();
		$address->ID = 0;
		$address->NAME = $wpdb->get_var($wpdb->prepare("SELECT NAME FROM " . VTM_TABLE_PREFIX . "CHARACTER WHERE ID = '%s'", $characterID));
		$address->CHARACTER_ID = $characterID;
		$address->PM_TYPE_ID = 0;
		$address->PM_CODE = '';
		$address->DESCRIPTION = 'Addressed message left in a secure location';
		$address->VISIBLE = 'Y';
		$address->ISDEFAULT = 'Y';
		$address->DELETED = 'N';
	} 
	
	return $address;
}

function vtm_get_pm_types() {
	global $wpdb;

	$sql = "SELECT ID, NAME, DESCRIPTION FROM " . VTM_TABLE_PREFIX . "PM_TYPE;";
	
	return $wpdb->get_results($sql);
}

function vtm_sanitize_pm_code($code) {
	$code = strtoupper($code);
	$code = preg_replace('/\s/','',$code); // remove whitespace
	$code = preg_replace('/[^\w-]/','',$code); // non-alpha characters
	
	return $code;
}

function vtm_get_pm_addressbook($characterID = 0, 
	$filter_address_type = 'all',
	$filter_addressbook = 'all') {
	global $wpdb;
	global $vtmglobal;
	if ($characterID == 0){
		if (!isset($vtmglobal['characterID']) && !vtm_isST()) {
			$current_user = wp_get_current_user();
			$vtmglobal['characterID'] = vtm_establishCharacterID($current_user->user_login);
			$characterID = $vtmglobal['characterID'];
		}
	}
	
	$sqlarray = array();
	$sqlargs  = array();
	$filtersql  = "";
	$filterargs = "";
	
	if (!get_option( 'vtm_pm_send_to_dead_characters', '0' )) {
		$filtersql .= " AND chstatus.NAME != 'Dead'";
		
	}
	
	if ( "all" !== $filter_address_type) {
		$filtersql .= " AND pm.PM_TYPE_ID = %s";
		$filterargs = $filter_address_type;
	}
	
	if (vtm_isST()) {
		$stfiltersql = "";
	} else {
		$stfiltersql = " AND pm.VISIBLE = 'Y'";
	}
	
	// all visible addresses entered by characters
	$public = "SELECT pm.NAME as NAME,
			'Public' as ADDRESSBOOK,
			pm.PM_TYPE_ID as PM_TYPE_ID,
			pm.PM_CODE,
			pm.DESCRIPTION,
			pm.ID as tableID,
			ch.NAME as charactername,
			ch.ID as CHARACTER_ID,
			chstatus.NAME as characterstatus
		FROM
			" . VTM_TABLE_PREFIX . "CHARACTER_PM_ADDRESS pm,
			" . VTM_TABLE_PREFIX . "CHARACTER ch,
			" . VTM_TABLE_PREFIX . "PLAYER player,
			" . VTM_TABLE_PREFIX . "PLAYER_STATUS pstatus,
			" . VTM_TABLE_PREFIX . "CHARGEN_STATUS cgstatus,
			" . VTM_TABLE_PREFIX . "CHARACTER_STATUS chstatus
		WHERE
			pm.CHARACTER_ID = ch.ID
			AND ch.PLAYER_ID = player.ID
			AND player.PLAYER_STATUS_ID = pstatus.ID
			AND cgstatus.ID = ch.CHARGEN_STATUS_ID
			AND chstatus.ID = ch.CHARACTER_STATUS_ID
			AND cgstatus.NAME = 'Approved'
			AND ch.DELETED = 'N'
			AND ch.VISIBLE = 'Y'
			AND pstatus.NAME = 'Active'
			AND pm.DELETED = 'N' " . 
			$stfiltersql . " " . $filtersql ;
		
	if ($filter_addressbook == 'all' ||
		$filter_addressbook == 'public') {
		array_push($sqlarray, $public);
		if ($filtersql !== '' && $filterargs !== '') {
			array_push($sqlargs, $filterargs);
		}
	}
		
	// all addressbook entries added by character
	if (vtm_isST()) {
		$stfiltersql = "";
	} else {
		$stfiltersql = "AND ab.CHARACTER_ID = %s";
	}
	
	$addressbook = "SELECT ab.NAME as NAME,
			'Private' as ADDRESSBOOK,
			pm.PM_TYPE_ID as PM_TYPE_ID,
			ab.PM_CODE,
			ab.DESCRIPTION,
			ab.ID as tableID,
			ch.NAME as charactername,
			ch.ID as CHARACTER_ID,
			chstatus.NAME as characterstatus
		FROM
			" . VTM_TABLE_PREFIX . "CHARACTER_PM_ADDRESSBOOK ab,
			" . VTM_TABLE_PREFIX . "CHARACTER_PM_ADDRESS pm,
			" . VTM_TABLE_PREFIX . "CHARACTER ch,
			" . VTM_TABLE_PREFIX . "PLAYER player,
			" . VTM_TABLE_PREFIX . "PLAYER_STATUS pstatus,
			" . VTM_TABLE_PREFIX . "CHARGEN_STATUS cgstatus,
			" . VTM_TABLE_PREFIX . "CHARACTER_STATUS chstatus
		WHERE
			pm.CHARACTER_ID = ch.ID
			AND ch.PLAYER_ID = player.ID
			AND player.PLAYER_STATUS_ID = pstatus.ID
			AND cgstatus.ID = ch.CHARGEN_STATUS_ID
			AND chstatus.ID = ch.CHARACTER_STATUS_ID
			AND cgstatus.NAME = 'Approved'
			AND ch.DELETED = 'N'
			AND ch.VISIBLE = 'Y'
			AND pstatus.NAME = 'Active'
			AND ab.PM_CODE = pm.PM_CODE
			AND pm.DELETED = 'N' " .
			$stfiltersql . " " . $filtersql;

	if ($filter_addressbook == 'all' ||
		$filter_addressbook == 'private') {
		array_push($sqlarray, $addressbook);
		if ($stfiltersql !== '')
			array_push($sqlargs, $characterID);
		if ($filterargs !== '')
			array_push($sqlargs, $filterargs);
		

	}
				
	// all addresses from post office (if enabled) of visible/active/undeleted
	if (get_option( 'vtm_pm_ic_postoffice_enabled', '0' ) == 1) {
		$location = get_option( 'vtm_pm_ic_postoffice_location', 'Post Office' );
		
		$postoffice = "SELECT ch.NAME as NAME,
				'Post Office' as ADDRESSBOOK,
				0 as PM_TYPE_ID,
				'' as PM_CODE,
				'Addressed message left in a secure location' as DESCRIPTION,
				ch.ID as tableID,
				ch.NAME as charactername,
				ch.ID as CHARACTER_ID,
				chstatus.NAME as characterstatus
			FROM 
				" . VTM_TABLE_PREFIX . "CHARACTER ch,
				" . VTM_TABLE_PREFIX . "PLAYER player,
				" . VTM_TABLE_PREFIX . "PLAYER_STATUS pstatus,
				" . VTM_TABLE_PREFIX . "CHARGEN_STATUS cgstatus,
				" . VTM_TABLE_PREFIX . "CHARACTER_STATUS chstatus
			WHERE
				ch.PLAYER_ID = player.ID
				AND player.PLAYER_STATUS_ID = pstatus.ID
				AND cgstatus.ID = ch.CHARGEN_STATUS_ID
				AND chstatus.ID = ch.CHARACTER_STATUS_ID
				AND pstatus.NAME = 'Active'
				AND ch.VISIBLE = 'Y'
				AND ch.DELETED = 'N'
				AND cgstatus.NAME = 'Approved' " .
				$filtersql;

		if ( ($filter_address_type == 'all' || $filter_address_type == 0) &&
			 ($filter_addressbook == 'all' || $filter_addressbook == 'postoffice') ) {
			array_push($sqlarray, $postoffice);
			if ($filtersql !== '' && $filterargs !== '') {
				array_push($sqlargs, $filterargs);
			}
		}
			
	}
	$sql = "(" . implode(") UNION (", $sqlarray) . ")";
	$sql .= " ORDER BY charactername, NAME, tableID";
		
	//print_r($sqlargs);
	//echo "<p>SQL: $sql</p>";
	if (count($sqlargs) > 0) {
		$sql  = $wpdb->prepare($sql, $sqlargs);
	}
	//echo "<p>SQL prepare: $sql</p>";
	
	$data = $wpdb->get_results($sql);
	
	if (vtm_count($data) > 0) {
		$i = 1;
		foreach ($data as $row) {
			$row->ID = $i++;
		}
	}
	
	//print_r($data);
	return $data;
}

// args = 	array('character' => <wordpress_id>), or
//			array('characterID' => <characterID>), and/or 
//			array('code' => <address code>)
function vtm_pm_link($linktext, $args) {
	global $vtmglobal;
	
	
	if (get_option( 'vtm_feature_pm', '0' ) == '1' && is_user_logged_in()) {
		$linkurl = admin_url('post-new.php');
		$linkurl = add_query_arg('post_type','vtmpm',$linkurl );
		
		// work out the character
		if (isset($args['characterID'])) {
			$characterID = $args['characterID'];
		} 
		elseif (isset($args['character'])) {
			$characterID = vtm_establishCharacterID($args['character']);
		} 
		else {
			$characterID = vtm_get_characterID_from_pm_code($args['code']);
		}
		
		if (isset($vtmglobal['characterID']) && $characterID ==	$vtmglobal['characterID'] ) {
			// don't add a contact link for your own character
			return vtm_formatOutput($linktext, 1);
		}
		
		// work out the code
		if (isset($args['code'])) {
			$code = $args['code'];
			$type = vtm_get_pm_typeidfromcode($code);
		} else {
			$address = vtm_get_default_address($characterID);
			
			if (!isset($address->PM_CODE))
				return vtm_formatOutput($linktext, 1);
		
			$code = $address->PM_CODE;
			$type = $address->PM_TYPE_ID;
		}
		
		// work out the url to create a new PM
		if ($code != '') {
			$linkurl = add_query_arg('code',$code,$linkurl);
		}
		$linkurl = add_query_arg('characterID',$characterID,$linkurl);
		$linkurl = add_query_arg('type',$type,$linkurl);
		
		$imgurl = VTM_PLUGIN_URL . '/images/mail.' . VTM_ICON_FORMAT;
		
		// output link
		$linktext .= " <a href='$linkurl'><img class='vtmpm_icon' src='$imgurl' alt='(contact)'></a>";
		
	}
	
	return vtm_formatOutput($linktext, 1);
}

function  vtm_get_characterID_from_pm_code($code) {
	global $wpdb;
	
	return $wpdb->get_var($wpdb->prepare("SELECT CHARACTER_ID FROM " . VTM_TABLE_PREFIX . "CHARACTER_PM_ADDRESS WHERE PM_CODE = '%s'", $code));
	
}

function vtm_hex2rgb($hex) {
   $hex = str_replace("#", "", $hex);

   if(strlen($hex) == 3) {
      $r = hexdec(substr($hex,0,1).substr($hex,0,1));
      $g = hexdec(substr($hex,1,1).substr($hex,1,1));
      $b = hexdec(substr($hex,2,1).substr($hex,2,1));
   } else {
      $r = hexdec(substr($hex,0,2));
      $g = hexdec(substr($hex,2,2));
      $b = hexdec(substr($hex,4,2));
   }
   $rgb = array($r, $g, $b);
   //return implode(",", $rgb); // returns the rgb values separated by commas
   return $rgb; // returns an array with the rgb values
}

function vtm_is_page($id) {
	$pages = get_pages();
	$match = 0;
	//print_r($pages);
	foreach ($pages as $page) { 
		if ($page->ID == $id) {
			$match = 1;
		} 
	}
	
	return $match;
}

function vtm_get_page_link($id, $characterID, $idname, $name) {
	
	$text = "";
	
	if (!vtm_is_page($id)) {
		return "(Error: check configuration for page settings) " . $name;
	} else {
		$link = get_page_link($id);
		return sprintf('<a href="%s?%s=%s">%s</a>',$link,$idname,urlencode($characterID),vtm_formatOutput($name));
	}
	
}
function vtm_get_page_icon($id, $characterID, $idname, $icon, $title, $alt) {
	
	$text = "";
	
	if (!vtm_is_page($id)) return "";
	
	$iconurl = plugins_url('inc/adminpages/icons/',dirname(__FILE__)) . $icon;
	$iconurl = sprintf('<img src="%s" alt="%s" title="%s" />',$iconurl,$alt,$title);
	$link = get_page_link($id);
	
	return sprintf('&nbsp;<a href="%s?%s=%s">%s</a>',$link,$idname,urlencode($characterID),$iconurl);
	
}

function vtm_report_max_input_vars($content) {
	
	$count = 0;
	$start = strpos($content, "<input", 0);
	while ($start !== false) {
		$count++;
		$start = strpos($content, "<input", $start+1);
	}
	
	if ($count > ini_get('max_input_vars')) {
		$report = "<div class='vtm_error'><p><b>Error:</b>PHP ini setting 'max_input_vars' is insufficient for the number of inputs on this page.  Either decrease the number of things available to buy with experience or ask your website administrator to increase the setting.</div>";
		$report .= "<!-- PHP Version: " . phpversion() . " -->\n";
		$report .= "<!-- max input vars: " . ini_get('max_input_vars') . " -->\n";
		$report .= "<!-- actual input vars: $count -->\n";
	} else {
		$report = "";
	}
	
	return $report . $content;
}

function vtm_count($input) {
	
	if (is_countable($input)) {
		return count($input);
	}
	elseif (is_object($input)) {
		//print_r($input);
		$count = 0;
		foreach ($input as $item) {
			$count++;
		}
		//print "count is $count";
		return $count;
	}
	
	return 0;
}
?>