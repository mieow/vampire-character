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

	/*$sql = "select DESCRIPTION, LINK from " . VTM_TABLE_PREFIX . "ST_LINK where VALUE = %s;";
	$results = $wpdb->get_results($wpdb->prepare($sql, $stlinkvalue));
	
	$pageid   = 0;
	$pagename = "Page not matched";
	if (count($results) == 1) {
		$pages = get_pages();
		foreach ( $pages as $page ) {
			if ('/' . get_page_uri( $page->ID ) == $results[0]->LINK) {
				$pageid = $page->ID;
				$pagename = $page->post_title;
			}
		}		
	}*/
	$sql = "select DESCRIPTION, WP_PAGE_ID from " . VTM_TABLE_PREFIX . "ST_LINK where VALUE = %s;";
	$results = $wpdb->get_row($wpdb->prepare($sql, $stlinkvalue));
	
	$pageid = "Page not matched";
	if (count($results) == 1) {
		$pages = get_pages();
		foreach ( $pages as $page ) {
			if ($page->ID == $results->WP_PAGE_ID) {
				$pageid = $page->post_title;
			}
		}		
	}
	return $pageid;

}
function vtm_get_stlink_url($stlinkvalue, $fullurl = false) {
	global $wpdb;

	$sql = "select DESCRIPTION, WP_PAGE_ID from " . VTM_TABLE_PREFIX . "ST_LINK where VALUE = %s;";
	$results = $wpdb->get_row($wpdb->prepare($sql, $stlinkvalue));
	
	$url = "Page not matched";
	if (count($results) == 1) {
		$url = get_page_link($results->WP_PAGE_ID);
	}
	
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
function vtm_get_generations() {

	global $wpdb;

	$sql = "SELECT ID, NAME FROM " . VTM_TABLE_PREFIX . "GENERATION ORDER BY BLOODPOOL, MAX_DISCIPLINE;";
	$list = $wpdb->get_results($sql);
	
	//echo "<p>SQL: $sql</p>";
	//print_r($list);
	
	return $list;
}
function vtm_get_sects() {

	global $wpdb;

	$sql = "SELECT ID, NAME FROM " . VTM_TABLE_PREFIX . "SECT;";
	$list = $wpdb->get_results($sql);
	
	return $list;
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
	
    function vtm_listPlayers($playerStatus, $playerType) {
        global $wpdb;
        $table_prefix = VTM_TABLE_PREFIX;

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
                          " . $statusClause . $typeClause . "
                        ORDER BY name";

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
        $visible_sector  = " AND VISIBLE = 'Y' ";

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

        $merits = $wpdb->get_results($sql);
        return $merits;
    }


    function vtm_establishCharacterID($character = '') {
        global $wpdb;

		$sql = "SELECT id
				FROM " . VTM_TABLE_PREFIX . "CHARACTER
				WHERE WORDPRESS_ID = %s";
		$cid = $wpdb->get_var($wpdb->prepare($sql, $character));
		
		if (empty($cid) && isset($_REQUEST['characterID'])) {
			$cid = $_REQUEST['characterID'];
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

        $sql = "SELECT * FROM " . VTM_TABLE_PREFIX . "CONFIG";
        $vtmglobal['config'] = $wpdb->get_row($sql);
				
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
	$full  = plugins_url( 'vtm-character/images/dot1full.jpg' );
	$empty = plugins_url( 'vtm-character/images/dot1empty.jpg' );
	
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
	$full  = plugins_url( 'vtm-character/images/crossclear.jpg' );
	$empty = plugins_url( 'vtm-character/images/webbox.jpg' );
	
	$output = "";
	
	for ($i = 1 ; $i <= $base ; $i++) {
		if ($i <= $input)
			$output .= "<img alt='$i' src='$full' />";
		else
			$output .= "<img alt='$i' src='$empty' />";
			
		if ($i == 10)
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
				xp_spent.amount,
				xp_spent.comment,
				xp_spent.awarded
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
				pending.amount,
				pending.comment,
				pending.awarded
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
	
	$sql = "$sqlSpent
			UNION
			$sqlPending
			ORDER BY awarded DESC, comment
			$sqlLimit";
	$sql = $wpdb->prepare($sql, $filterid, $filterid);	
	
	//print "<p>SQL: $sql</p>";
	
	return $wpdb->get_results($sql);
	
}

?>