<?php
require_once VTM_CHARACTER_URL . 'lib/fpdf.php';
if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class vtmclass_character {

	var $name;
	var $display_name;
	var $sect;
	var $clan;
	var $private_clan;
	var $private_icon;
	var $public_icon;
	var $domain;
	var $player;
	var $player_id;
	var $wordpress_id;
	var $generation;
	var $bloodpool;
	var $blood_per_round;
	var $willpower;
	var $pending_willpower;
	var $current_willpower;
	var $path_of_enlightenment;
	var $path_rating;
	var $rituals;
	var $max_rating;
	var $date_of_birth;
	var $date_of_embrace;
	var $sire;
	var $combo_disciplines;
	var $current_experience;
	var $pending_experience;
	var $spent_experience;
	var $nature;
	var $demeanour;
	var $clan_flaw;
	var $quote;
	var $portrait;
	var $char_status_comment;
	var $char_status;
	var $offices;
	var $history;
	var $last_updated;
	var $concept;
	var $email;
	var $newsletter;
	var $backgrounds_done;
	var $backgrounds_total;
	
	function load ($characterID){
		global $wpdb;
		global $vtmglobal;
		
		$wpdb->show_errors();
				
		/* Basic Character Info */
		$sql = "SELECT chara.name                      cname,
					   chara.character_status_comment  cstat_comment,
					   cstatus.name                    cstat,
					   chara.wordpress_id              wpid,
					   chara.last_updated			   last_updated,
					   player.name                     pname,
					   player.id                       player_id,
					   domains.name                    domain,
					   pub_clan.name                   public_clan,
					   priv_clan.name                  private_clan,
					   paths.name					   path,
					   gen.name						   generation,
                       gen.bloodpool,
                       gen.blood_per_round,
					   gen.max_rating,
					   chara.date_of_birth,
					   chara.date_of_embrace,
					   chara.sire,
					   priv_clan.clan_flaw,
					   sects.name                      sect,
					   pub_clan.icon_link			   public_icon,
					   priv_clan.icon_link			   private_icon,
					   chara.concept				   concept,
					   chara.email,
					   chara.get_newsletter			   newsletter
                    FROM " . VTM_TABLE_PREFIX . "CHARACTER chara,
                         " . VTM_TABLE_PREFIX . "PLAYER player,
                         " . VTM_TABLE_PREFIX . "DOMAIN domains,
                         " . VTM_TABLE_PREFIX . "CLAN pub_clan,
                         " . VTM_TABLE_PREFIX . "CLAN priv_clan,
						 " . VTM_TABLE_PREFIX . "GENERATION gen,
						 " . VTM_TABLE_PREFIX . "ROAD_OR_PATH paths,
						 " . VTM_TABLE_PREFIX . "SECT sects,
						 " . VTM_TABLE_PREFIX . "CHARACTER_STATUS cstatus
                    WHERE chara.PUBLIC_CLAN_ID = pub_clan.ID
                      AND chara.PRIVATE_CLAN_ID = priv_clan.ID
                      AND chara.DOMAIN_ID = domains.ID
                      AND chara.PLAYER_ID = player.ID
					  AND chara.GENERATION_ID = gen.ID
					  AND chara.ROAD_OR_PATH_ID = paths.ID
					  AND chara.SECT_ID = sects.ID
					  AND chara.CHARACTER_STATUS_ID = cstatus.ID
                      AND chara.ID = '%s';";
		$sql = $wpdb->prepare($sql, $characterID);
		//echo "<p>SQL: ($characterID) $sql</p>";
		
		$result = $wpdb->get_results($sql);
		//print_r($result);
		
		if (count($result) > 0) {
			$this->name         = stripslashes($result[0]->cname);
			$this->clan         = $result[0]->public_clan;
			$this->private_clan = $result[0]->private_clan;
			$this->public_icon  = $result[0]->public_icon;
			$this->private_icon = $result[0]->private_icon;
			$this->domain       = $result[0]->domain;
			$this->player       = stripslashes($result[0]->pname);
			$this->wordpress_id = $result[0]->wpid;
			$this->generation   = $result[0]->generation;
			$this->max_rating   = $result[0]->max_rating;
			$this->player_id    = $result[0]->player_id;
			$this->clan_flaw    = stripslashes($result[0]->clan_flaw);
			$this->sect         = $result[0]->sect;
			$this->bloodpool    = $result[0]->bloodpool;
			$this->sire         = stripslashes($result[0]->sire);
			$this->char_status  = $result[0]->cstat;
			$this->last_updated = $result[0]->last_updated;
			$this->concept      = stripslashes($result[0]->concept);
			$this->blood_per_round = $result[0]->blood_per_round;
			$this->date_of_birth   = $result[0]->date_of_birth;
			$this->date_of_embrace = $result[0]->date_of_embrace;
			$this->char_status_comment   = stripslashes($result[0]->cstat_comment);
			$this->path_of_enlightenment = stripslashes($result[0]->path);
			$this->email        = $result[0]->email;
			$this->newsletter   = $result[0]->newsletter;
		} else {
			$this->name         = 'No character selected';
			$this->clan         = '';
			$this->private_clan = '';
			$this->public_icon  = '';
			$this->private_icon = '';
			$this->domain       = '';
			$this->player       = 'No player selected';
			$this->wordpress_id = '';
			$this->generation   = '';
			$this->max_rating   = 5;
			$this->player_id    = 0;
			$this->clan_flaw    = '';
			$this->sect         = '';
			$this->bloodpool    = 10;
			$this->sire         = '';
			$this->char_status  = '';
			$this->last_updated = '';
			$this->blood_per_round = 1;
			$this->date_of_birth   = '';
			$this->date_of_embrace = '';
			$this->char_status_comment   = '';
			$this->path_of_enlightenment = '';
			$this->concept      = '';
			$this->email        = '';
			$this->newsletter   = 'N';
		}
		
        $user = get_user_by('login',$this->name);
        $this->display_name = isset($user->display_name) ? $user->display_name : 'No character selected';
		
		// Profile
		$sql = "SELECT QUOTE, PORTRAIT
				FROM 
					" . VTM_TABLE_PREFIX . "CHARACTER_PROFILE
				WHERE
					CHARACTER_ID = %s";
		$result = $wpdb->get_row($wpdb->prepare($sql, $characterID));
		$this->quote    = isset($result->QUOTE) ? $result->QUOTE : '';
		if (empty($result->PORTRAIT))
			$this->portrait = $vtmglobal['config']->PLACEHOLDER_IMAGE;
		else
			$this->portrait = $result->PORTRAIT;
		
		/* Nature / Demeanour, if used */
		if ($vtmglobal['config']->USE_NATURE_DEMEANOUR == 'Y') {
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
			$result = $wpdb->get_row($wpdb->prepare($sql, $characterID));
			
			$this->nature    = isset($result->nature) ? $result->nature : '';
			$this->demeanour = isset($result->demeanour) ? $result->demeanour : '';
		}
		
		/* Attributes */
		$sql = "SELECT stat.name		name,
					stat.grouping		grouping,
					stat.ordering		ordering,
					IFNULL(freebie.SPECIALISATION,charstat.comment)	specialty,
					IFNULL(freebie.LEVEL_TO,charstat.level) level,
					xp.CHARTABLE_LEVEL  pending
				FROM
					" . VTM_TABLE_PREFIX . "STAT stat
					LEFT JOIN (
						SELECT ITEMTABLE_ID, LEVEL_TO, SPECIALISATION
						FROM " . VTM_TABLE_PREFIX . "PENDING_FREEBIE_SPEND
						WHERE
							ITEMTABLE = 'STAT'
							AND CHARACTER_ID = %s
					) freebie
					ON
						freebie.ITEMTABLE_ID = stat.ID
					LEFT JOIN (
						SELECT ITEMTABLE_ID, CHARTABLE_LEVEL
						FROM " . VTM_TABLE_PREFIX . "PENDING_XP_SPEND
						WHERE
							ITEMTABLE = 'STAT'
							AND CHARACTER_ID = %s
					) xp
					ON
						xp.ITEMTABLE_ID = stat.ID,
					" . VTM_TABLE_PREFIX . "CHARACTER_STAT charstat,
					" . VTM_TABLE_PREFIX . "CHARACTER chara
				WHERE
					charstat.CHARACTER_ID = chara.ID
					AND charstat.STAT_ID = stat.ID
					AND chara.id = '%s'
				ORDER BY stat.grouping, stat.ordering;";
		$sql = $wpdb->prepare($sql, $characterID, $characterID, $characterID);
		$result = $wpdb->get_results($sql);
		//echo "<p>SQL: $sql</p>";
		//print_r($result);
		
		$this->attributes = $result;
		$this->attributegroups = array();
		for ($i=0;$i<count($result);$i++)
			if (array_key_exists($result[$i]->grouping, $this->attributegroups))
				array_push($this->attributegroups[$result[$i]->grouping], $this->attributes[$i]);
			else {
				$this->attributegroups[$result[$i]->grouping] = array($this->attributes[$i]);
			}
		
		/* Abilities */
		// Abilities from skill table + freebie points with pending XP
		$sql = "SELECT skill.name		skillname,
					skilltype.name		grouping,
					IFNULL(freebie.SPECIALISATION,charskill.comment)	specialty,
					IFNULL(freebie.LEVEL_TO,charskill.level) level,
					xp.CHARTABLE_LEVEL  pending,
					skill.multiple      multiple
				FROM
					" . VTM_TABLE_PREFIX . "SKILL skill,
					" . VTM_TABLE_PREFIX . "SKILL_TYPE skilltype,
					" . VTM_TABLE_PREFIX . "CHARACTER_SKILL charskill
					LEFT JOIN (
						SELECT CHARTABLE_ID, LEVEL_TO, SPECIALISATION
						FROM " . VTM_TABLE_PREFIX . "PENDING_FREEBIE_SPEND
						WHERE
							ITEMTABLE = 'SKILL'
							AND CHARACTER_ID = %s
					) freebie
					ON
						freebie.CHARTABLE_ID = charskill.ID
					LEFT JOIN (
						SELECT CHARTABLE_ID, CHARTABLE_LEVEL, SPECIALISATION
						FROM " . VTM_TABLE_PREFIX . "PENDING_XP_SPEND
						WHERE
							CHARTABLE = 'CHARACTER_SKILL'
							AND CHARACTER_ID = %s
					) xp
					ON
						xp.CHARTABLE_ID = charskill.ID,
					" . VTM_TABLE_PREFIX . "CHARACTER chara
				WHERE
					charskill.CHARACTER_ID = chara.ID
					AND charskill.SKILL_ID = skill.ID
					AND skilltype.ID = skill.SKILL_TYPE_ID
					AND chara.id = '%s'
				ORDER BY skill.name ASC;";
		$sql = $wpdb->prepare($sql, $characterID, $characterID, $characterID);
		$result = $wpdb->get_results($sql);
		//echo "<p>SQL: $sql</p>";
		//print_r($result);
		
		// freebie points spend with pending xp
		$sql = "SELECT skill.NAME		skillname,
					skilltype.name		grouping,
					freebie.SPECIALISATION	specialty,
					freebie.LEVEL_TO 		level,
					xp.CHARTABLE_LEVEL      pending,
					skill.multiple      	multiple,
					freebie.CHARTABLE_ID	chartableid
			FROM
				" . VTM_TABLE_PREFIX . "PENDING_FREEBIE_SPEND freebie
					LEFT JOIN (
						SELECT CHARTABLE_ID, CHARTABLE_LEVEL, SPECIALISATION
						FROM " . VTM_TABLE_PREFIX . "PENDING_XP_SPEND
						WHERE
							CHARTABLE = 'PENDING_FREEBIE_SPEND'
							AND ITEMTABLE = 'SKILL'
							AND CHARACTER_ID = %s
					) xp
					ON
						xp.CHARTABLE_ID = freebie.ID,
				" . VTM_TABLE_PREFIX . "SKILL skill,
				" . VTM_TABLE_PREFIX . "SKILL_TYPE skilltype
			WHERE
				freebie.CHARACTER_ID = %s
				AND skill.ID = freebie.ITEMTABLE_ID
				AND skilltype.ID = skill.SKILL_TYPE_ID
				AND freebie.ITEMTABLE = 'SKILL'
				AND freebie.CHARTABLE_ID = 0";
		$sql = $wpdb->prepare($sql, $characterID, $characterID);
		$freebies = $wpdb->get_results($sql);
		//echo "SQL: $sql</p>";
		//print_r($freebies);
		
		// pending xp for new skills
		$sql = "SELECT skill.NAME			skillname,
					skilltype.name		grouping,
					xp.SPECIALISATION		specialty,
					0 						level,
					xp.CHARTABLE_LEVEL      pending,
					skill.multiple      	multiple,
					0						chartableid
			FROM
				" . VTM_TABLE_PREFIX . "PENDING_XP_SPEND xp,
				" . VTM_TABLE_PREFIX . "SKILL skill,
				" . VTM_TABLE_PREFIX . "SKILL_TYPE skilltype
			WHERE
				xp.CHARACTER_ID = %s
				AND skill.ID = xp.ITEMTABLE_ID
				AND skilltype.ID = skill.SKILL_TYPE_ID
				AND xp.ITEMTABLE = 'SKILL'
				AND xp.CHARTABLE_ID = 0";
		$sql = $wpdb->prepare($sql, $characterID);
		$xp = $wpdb->get_results($sql);
		//echo "SQL: $sql</p>";
		//print_r($xp);

		$result = array_merge($result, $freebies, $xp);
		$this->abilities = $result;
		$this->abilitygroups = array();
		for ($i=0;$i<count($result);$i++) {
			if (array_key_exists($result[$i]->grouping, $this->abilitygroups))
				array_push($this->abilitygroups[$result[$i]->grouping], $this->abilities[$i]);
			else {
				$this->abilitygroups[$result[$i]->grouping] = array($this->abilities[$i]);
			}
			
		}
		
		/* Backgrounds */
		$sql = "SELECT bground.name		     background,
					sectors.name		     sector,
					charbgnd.comment	     comment,
					IFNULL(freebie.LEVEL_TO,charbgnd.level) level,
					IFNULL(charbgnd.approved_detail,charbgnd.pending_detail) detail
				FROM
					" . VTM_TABLE_PREFIX . "BACKGROUND bground,
					" . VTM_TABLE_PREFIX . "CHARACTER chara,
					" . VTM_TABLE_PREFIX . "CHARACTER_BACKGROUND charbgnd
					LEFT JOIN (
						SELECT CHARTABLE_ID, LEVEL_TO
						FROM " . VTM_TABLE_PREFIX . "PENDING_FREEBIE_SPEND
						WHERE
							ITEMTABLE = 'BACKGROUND'
							AND CHARACTER_ID = %s
					) freebie
					ON
						freebie.CHARTABLE_ID = charbgnd.ID
					LEFT JOIN 
						" . VTM_TABLE_PREFIX . "SECTOR sectors
					ON charbgnd.SECTOR_ID = sectors.ID
				WHERE
					charbgnd.CHARACTER_ID = chara.ID
					AND charbgnd.BACKGROUND_ID = bground.ID
					AND chara.id = '%s'
				ORDER BY bground.name ASC;";
		$sql = $wpdb->prepare($sql, $characterID, $characterID);
		$result = $wpdb->get_results($sql);
		$sql = "SELECT bground.NAME			background,
					''						sector,
					freebie.SPECIALISATION	comment,
					freebie.LEVEL_TO 		level,
					freebie.PENDING_DETAIL  detail
			FROM
				" . VTM_TABLE_PREFIX . "PENDING_FREEBIE_SPEND freebie,
				" . VTM_TABLE_PREFIX . "BACKGROUND bground
			WHERE
				freebie.CHARACTER_ID = %s
				AND bground.ID = freebie.ITEMTABLE_ID
				AND freebie.ITEMTABLE = 'BACKGROUND'
				AND freebie.CHARTABLE_ID = ''";
		$sql = $wpdb->prepare($sql, $characterID);
		//echo "<p>SQL: $sql</p>";
		$freebies = $wpdb->get_results($sql);
		
		$this->backgrounds = array_merge($result, $freebies);
		
		/* Disciplines */
		// Disciplines from table with freebie points and pending xp
		$sql = "SELECT disciplines.NAME		name,
					IFNULL(freebie.LEVEL_TO,chardisc.level) level,
					xp.CHARTABLE_LEVEL      pending
				FROM
					" . VTM_TABLE_PREFIX . "DISCIPLINE disciplines,
					" . VTM_TABLE_PREFIX . "CHARACTER_DISCIPLINE chardisc
					LEFT JOIN (
						SELECT CHARTABLE_ID, LEVEL_TO
						FROM " . VTM_TABLE_PREFIX . "PENDING_FREEBIE_SPEND
						WHERE
							ITEMTABLE = 'DISCIPLINE'
							AND CHARACTER_ID = %s
					) freebie
					ON
						freebie.CHARTABLE_ID = chardisc.ID
					LEFT JOIN (
						SELECT CHARTABLE_ID, CHARTABLE_LEVEL
						FROM " . VTM_TABLE_PREFIX . "PENDING_XP_SPEND
						WHERE
							ITEMTABLE = 'DISCIPLINE'
							AND CHARTABLE = 'CHARACTER_DISCIPLINE'
							AND CHARACTER_ID = %s
					) xp
					ON
						xp.CHARTABLE_ID = chardisc.ID,
					" . VTM_TABLE_PREFIX . "CHARACTER chara
				WHERE
					chardisc.DISCIPLINE_ID = disciplines.ID
					AND chardisc.CHARACTER_ID = chara.ID
					AND chara.id = '%s'
				ORDER BY disciplines.name ASC;";
		$sql = $wpdb->prepare($sql, $characterID, $characterID, $characterID);
		$result = $wpdb->get_results($sql);
		// Disciplines from freebie points with pending xp
		$sql = "SELECT disciplines.NAME		name,
					freebie.LEVEL_TO 		level,
					xp.CHARTABLE_LEVEL      pending
			FROM
				" . VTM_TABLE_PREFIX . "PENDING_FREEBIE_SPEND freebie
					LEFT JOIN (
						SELECT CHARTABLE_ID, CHARTABLE_LEVEL, SPECIALISATION
						FROM " . VTM_TABLE_PREFIX . "PENDING_XP_SPEND
						WHERE
							CHARTABLE = 'PENDING_FREEBIE_SPEND'
							AND ITEMTABLE = 'DISCIPLINE'
							AND CHARACTER_ID = %s
					) xp
					ON
						xp.CHARTABLE_ID = freebie.ID,
				" . VTM_TABLE_PREFIX . "DISCIPLINE disciplines
			WHERE
				freebie.CHARACTER_ID = %s
				AND disciplines.ID = freebie.ITEMTABLE_ID
				AND freebie.ITEMTABLE = 'DISCIPLINE'
				AND freebie.CHARTABLE_ID = ''";
		$sql = $wpdb->prepare($sql, $characterID, $characterID);
		$freebies = $wpdb->get_results($sql);
		// pending xp for new
		$sql = "SELECT disciplines.NAME		name,
					0 						level,
					xp.CHARTABLE_LEVEL      pending
			FROM
				" . VTM_TABLE_PREFIX . "PENDING_XP_SPEND xp,
				" . VTM_TABLE_PREFIX . "DISCIPLINE disciplines
			WHERE
				xp.CHARACTER_ID = %s
				AND disciplines.ID = xp.ITEMTABLE_ID
				AND xp.ITEMTABLE = 'DISCIPLINE'
				AND xp.CHARTABLE_ID = 0";
		$sql = $wpdb->prepare($sql, $characterID);
		$xp = $wpdb->get_results($sql);

		$this->disciplines = array_merge($result, $freebies, $xp);

		/* Majik Paths */
		// Paths from tabel with freebie points and pending xp
		$sql = "SELECT paths.NAME           name,
					disciplines.NAME		discipline,
					IFNULL(freebie.LEVEL_TO,charpath.level)		level,
					xp.CHARTABLE_LEVEL      pending
				FROM
					" . VTM_TABLE_PREFIX . "DISCIPLINE disciplines,
					" . VTM_TABLE_PREFIX . "CHARACTER_PATH charpath
					LEFT JOIN (
						SELECT CHARTABLE_ID, LEVEL_TO
						FROM " . VTM_TABLE_PREFIX . "PENDING_FREEBIE_SPEND
						WHERE
							CHARTABLE = 'CHARACTER_PATH'
							AND CHARACTER_ID = %s
					) freebie
					ON
						freebie.CHARTABLE_ID = charpath.ID
					LEFT JOIN (
						SELECT CHARTABLE_ID, CHARTABLE_LEVEL
						FROM " . VTM_TABLE_PREFIX . "PENDING_XP_SPEND
						WHERE
							ITEMTABLE = 'PATH'
							AND CHARTABLE = 'CHARACTER_PATH'
							AND CHARACTER_ID = %s
					) xp
					ON
						xp.CHARTABLE_ID = charpath.ID,
					" . VTM_TABLE_PREFIX . "PATH paths,
					" . VTM_TABLE_PREFIX . "CHARACTER chara
				WHERE
					charpath.PATH_ID = paths.ID
					AND paths.DISCIPLINE_ID = disciplines.ID
					AND charpath.CHARACTER_ID = chara.ID
					AND chara.id = '%s'
				ORDER BY disciplines.name ASC, paths.NAME;";
		$sql = $wpdb->prepare($sql, $characterID, $characterID, $characterID);
		$result = $wpdb->get_results($sql);
		//echo "<p>SQL: $sql</p>";
		//print_r($result);
		// Disciplines from freebie points with pending xp
		$sql = "SELECT paths.NAME		name,
					disciplines.NAME	discipline,
					freebie.LEVEL_TO 	level,
					xp.CHARTABLE_LEVEL  pending
			FROM
				" . VTM_TABLE_PREFIX . "PENDING_FREEBIE_SPEND freebie
					LEFT JOIN (
						SELECT CHARTABLE_ID, CHARTABLE_LEVEL, SPECIALISATION
						FROM " . VTM_TABLE_PREFIX . "PENDING_XP_SPEND
						WHERE
							CHARTABLE = 'PENDING_FREEBIE_SPEND'
							AND ITEMTABLE = 'PATH'
							AND CHARACTER_ID = %s
					) xp
					ON
						xp.CHARTABLE_ID = freebie.ID,
				" . VTM_TABLE_PREFIX . "DISCIPLINE disciplines,
				" . VTM_TABLE_PREFIX . "PATH paths
			WHERE
				freebie.CHARACTER_ID = %s
				AND paths.DISCIPLINE_ID = disciplines.ID
				AND paths.ID = freebie.ITEMTABLE_ID
				AND freebie.ITEMTABLE = 'PATH'
				AND freebie.CHARTABLE_ID = ''";
		$sql = $wpdb->prepare($sql, $characterID, $characterID);
		$freebies = $wpdb->get_results($sql);
		//echo "<p>SQL: $sql</p>";
		//print_r($freebies);
		// pending xp for new
		$sql = "SELECT paths.NAME		name,
					disciplines.NAME	discipline,
					0 						level,
					xp.CHARTABLE_LEVEL      pending
			FROM
				" . VTM_TABLE_PREFIX . "PENDING_XP_SPEND xp,
				" . VTM_TABLE_PREFIX . "PATH paths,
				" . VTM_TABLE_PREFIX . "DISCIPLINE disciplines
			WHERE
				xp.CHARACTER_ID = %s
				AND paths.ID = xp.ITEMTABLE_ID
				AND paths.DISCIPLINE_ID = disciplines.ID
				AND xp.ITEMTABLE = 'PATH'
				AND xp.CHARTABLE_ID = 0";
		$sql = $wpdb->prepare($sql, $characterID);
		$xp = $wpdb->get_results($sql);
		
		$merged = array_merge($result, $freebies, $xp);
		
		// Reformat:
		//	[discipline] = ( [name] = (level, pending) )
		$this->paths = array();
		foreach ($merged as $majikpath) {
			$this->paths[$majikpath->discipline][$majikpath->name] = array($majikpath->level, $majikpath->pending);
		}
		//print_r($this->paths);
		
		/* Merits and Flaws */
		$sql = "(SELECT merits.NAME		      name,
					charmerit.comment	      comment,
					charmerit.level		      level,
					0						  pending,
					IFNULL(charmerit.approved_detail,charmerit.pending_detail) detail
				FROM
					" . VTM_TABLE_PREFIX . "MERIT merits,
					" . VTM_TABLE_PREFIX . "CHARACTER_MERIT charmerit,
					" . VTM_TABLE_PREFIX . "CHARACTER chara
				WHERE
					charmerit.MERIT_ID = merits.ID
					AND charmerit.CHARACTER_ID = chara.ID
					AND chara.id = '%s'
				ORDER BY merits.name ASC)
				UNION
				(SELECT merits.NAME			name,
					freebie.SPECIALISATION	comment,
					freebie.LEVEL_TO		level,
					0						pending,
					freebie.PENDING_DETAIL	detail
				FROM
					" . VTM_TABLE_PREFIX . "MERIT merits,
					" . VTM_TABLE_PREFIX . "PENDING_FREEBIE_SPEND freebie
				WHERE
					freebie.CHARACTER_ID = %s
					AND freebie.ITEMTABLE = 'MERIT'
					AND freebie.ITEMTABLE_ID = merits.ID)
				UNION
				(SELECT merits.NAME			name,
					xp.SPECIALISATION	    comment,
					xp.CHARTABLE_LEVEL		level,
					xp.CHARTABLE_LEVEL		pending,
					''						detail
				FROM
					" . VTM_TABLE_PREFIX . "MERIT merits,
					" . VTM_TABLE_PREFIX . "PENDING_XP_SPEND xp
				WHERE
					xp.CHARACTER_ID = %s
					AND xp.ITEMTABLE = 'MERIT'
					AND xp.ITEMTABLE_ID = merits.ID)";
		$sql = $wpdb->prepare($sql, $characterID, $characterID, $characterID);
		$result = $wpdb->get_results($sql);
		$this->meritsandflaws = $result;

		/* Full Willpower */
		$sql = "SELECT 
					IFNULL(freebie.LEVEL_TO,charstat.level) as level,
					xp.CHARTABLE_LEVEL as pending
				FROM " . VTM_TABLE_PREFIX . "CHARACTER_STAT charstat
					LEFT JOIN (
						SELECT CHARTABLE_ID, LEVEL_TO
						FROM " . VTM_TABLE_PREFIX . "PENDING_FREEBIE_SPEND
						WHERE
							ITEMTABLE = 'STAT'
							AND CHARACTER_ID = %s
					) freebie
					ON
						freebie.CHARTABLE_ID = charstat.ID
					LEFT JOIN (
						SELECT CHARTABLE_ID, CHARTABLE_LEVEL
						FROM " . VTM_TABLE_PREFIX . "PENDING_XP_SPEND
						WHERE
							ITEMTABLE = 'STAT'
							AND CHARTABLE = 'CHARACTER_STAT'
							AND CHARACTER_ID = %s
					) xp
					ON
						xp.CHARTABLE_ID = charstat.ID,
					" . VTM_TABLE_PREFIX . "STAT stat
				WHERE 
					charstat.CHARACTER_ID = '%s' 
					AND charstat.STAT_ID = stat.ID
					AND stat.name = 'Willpower';";
		$sql = $wpdb->prepare($sql, $characterID, $characterID, $characterID);
		//echo "<p>SQL: $sql</p>";
		$result = $wpdb->get_row($sql);
		$this->willpower         = isset($result->level) ? $result->level : 0;
		$this->pending_willpower = isset($result->pending) ? $result->pending : 0;
		
		/* Current Willpower */
        $sql = "SELECT SUM(char_temp_stat.amount) currentwp
                FROM " . VTM_TABLE_PREFIX . "CHARACTER_TEMPORARY_STAT char_temp_stat,
                     " . VTM_TABLE_PREFIX . "TEMPORARY_STAT tstat
                WHERE char_temp_stat.character_id = '%s'
					AND char_temp_stat.temporary_stat_id = tstat.id
					AND tstat.name = 'Willpower';";
		$sql = $wpdb->prepare($sql, $characterID);
		$result = $wpdb->get_var($sql);
		$this->current_willpower = isset($result) ? $result : 0;
		
		/* Humanity */
		$sql = "SELECT SUM(cpath.AMOUNT) path_rating
				FROM " . VTM_TABLE_PREFIX . "CHARACTER_ROAD_OR_PATH cpath
				WHERE cpath.CHARACTER_ID = %s;";	
		$sql = $wpdb->prepare($sql, $characterID);
		$result = $wpdb->get_var($sql);
		$sql = "SELECT ROAD_OR_PATH_RATING FROM " . VTM_TABLE_PREFIX . "CHARACTER WHERE ID = %s";
		$sql = $wpdb->prepare($sql, $characterID);
		$default = $wpdb->get_var($sql);
		//echo "<p>SQL: $sql ($result / $default)</p>";
		$this->path_rating = isset($result) && $result > 0 ? $result : $default;
		
		/* Rituals */
		$sql = "(SELECT disciplines.name as discname, rituals.name as ritualname, rituals.level,
					rituals.description, rituals.dice_pool, rituals.difficulty,
					0 as pending
				FROM " . VTM_TABLE_PREFIX . "DISCIPLINE disciplines,
                    " . VTM_TABLE_PREFIX . "CHARACTER_RITUAL char_rit,
                    " . VTM_TABLE_PREFIX . "RITUAL rituals
				WHERE
					char_rit.CHARACTER_ID = '%s'
					AND char_rit.RITUAL_ID = rituals.ID
					AND rituals.DISCIPLINE_ID = disciplines.ID
				ORDER BY disciplines.name, rituals.level, rituals.name)
				UNION
				(SELECT disciplines.name as discname, rituals.name as ritualname, rituals.level,
					rituals.description, rituals.dice_pool, rituals.difficulty,
					rituals.level as pending
				FROM " . VTM_TABLE_PREFIX . "DISCIPLINE disciplines,
                    " . VTM_TABLE_PREFIX . "PENDING_XP_SPEND xp,
                    " . VTM_TABLE_PREFIX . "RITUAL rituals
				WHERE
					xp.CHARACTER_ID = %s
					AND xp.ITEMTABLE = 'RITUAL'
					AND xp.ITEMTABLE_ID = rituals.id
					AND rituals.DISCIPLINE_ID = disciplines.ID)";
		$sql = $wpdb->prepare($sql, $characterID, $characterID);
		$result = $wpdb->get_results($sql);
		$i = 0;
		foreach ($result as $ritual) {
			$this->rituals[$ritual->discname][$i] = array(
				'name' => $ritual->ritualname, 
				'level' => $ritual->level,
				'roll'  => $ritual->dice_pool . ", diff " . $ritual->difficulty,
				'description' => $ritual->description,
				'pending' => $ritual->pending
			);
			$i++;
		}
		
		/* Combo disciplines */
		$sql = "(SELECT combo.name, 0 as pending
				FROM
					" . VTM_TABLE_PREFIX . "CHARACTER_COMBO_DISCIPLINE charcombo,
					" . VTM_TABLE_PREFIX . "COMBO_DISCIPLINE combo
				WHERE
					charcombo.COMBO_DISCIPLINE_ID = combo.ID
					AND charcombo.CHARACTER_ID = '%s'
				ORDER BY combo.name)
				UNION
				(SELECT combo.name, 1 as pending
				FROM
					" . VTM_TABLE_PREFIX . "PENDING_XP_SPEND xp,
					" . VTM_TABLE_PREFIX . "COMBO_DISCIPLINE combo
				WHERE
					xp.CHARACTER_ID = '%s'
					AND xp.ITEMTABLE = 'COMBO_DISCIPLINE'
					AND xp.ITEMTABLE_ID = combo.id
				ORDER BY combo.name)";
		$sql = $wpdb->prepare($sql, $characterID, $characterID);
		$result = $wpdb->get_results($sql);
		//print_r($result);
		//echo "<p>SQL: $sql</p>";
		$this->combo_disciplines = array();
		for ($i=0;$i<count($result);$i++) {	
			$name = $result[$i]->pending ? $result[$i]->name . " - PENDING" : $result[$i]->name;
			$this->combo_disciplines[$i] = $name;
		}
		
		/* Current Experience */
		$this->current_experience = vtm_get_total_xp($this->player_id, $characterID);
		$this->pending_experience = vtm_get_pending_xp($this->player_id, $characterID);
		$this->spent_experience  = $wpdb->get_var($wpdb->prepare("SELECT SUM(amount) FROM " . VTM_TABLE_PREFIX . "PLAYER_XP WHERE CHARACTER_ID = '%s' AND amount < 0", $characterID)) * -1;
		$this->spent_experience += $wpdb->get_var($wpdb->prepare("SELECT SUM(amount) FROM " . VTM_TABLE_PREFIX . "PENDING_XP_SPEND WHERE CHARACTER_ID = '%s'", $characterID)) * -1;
		
		// Offices / Positions
		$sql = "SELECT offices.name, offices.visible, domains.name as domain
				FROM
					" . VTM_TABLE_PREFIX . "CHARACTER_OFFICE charoffice,
					" . VTM_TABLE_PREFIX . "OFFICE offices,
					" . VTM_TABLE_PREFIX . "DOMAIN domains
				WHERE	
					charoffice.OFFICE_ID = offices.ID
					AND charoffice.DOMAIN_ID = domains.ID
					AND charoffice.CHARACTER_ID = '%s'
				ORDER BY offices.ORDERING";
		$sql = $wpdb->prepare($sql, $characterID);
		$this->offices = $wpdb->get_results($sql);
		
		// History
		$sql = "SELECT 
					eb.title				as title,
					eb.BACKGROUND_QUESTION	as question,
					IF(ceb.APPROVED_DETAIL = '',ceb.PENDING_DETAIL, ceb.APPROVED_DETAIL) as detail
				FROM
					" . VTM_TABLE_PREFIX . "CHARACTER_EXTENDED_BACKGROUND ceb,
					" . VTM_TABLE_PREFIX . "EXTENDED_BACKGROUND eb
				WHERE
					CHARACTER_ID = %s
					AND eb.ID = ceb.QUESTION_ID
					AND eb.VISIBLE = 'Y'
				ORDER BY eb.ORDERING";
		$sql = $wpdb->prepare($sql, $characterID);
		$this->history = $wpdb->get_results($sql);
		
		// Background questions complete
		$this->backgrounds_done = 0;
		$this->backgrounds_total = 0;
		
		// backgrounds
		$sql = "select count(backgrounds.BACKGROUND_QUESTION) as total2do, count(charbgs.APPROVED_DETAIL) as totaldone
				from	" . VTM_TABLE_PREFIX . "BACKGROUND backgrounds,
						" . VTM_TABLE_PREFIX . "CHARACTER_BACKGROUND charbgs,
						" . VTM_TABLE_PREFIX . "CHARACTER characters
				where	
					backgrounds.ID = charbgs.BACKGROUND_ID
					and	characters.ID = %d
					and characters.ID = charbgs.CHARACTER_ID
					and	(backgrounds.BACKGROUND_QUESTION != '' OR charbgs.SECTOR_ID > 0);";
		$sql = $wpdb->prepare($sql, $characterID);
		$result1 = $wpdb->get_row($sql);
		$this->backgrounds_done += $result1->totaldone;
		$this->backgrounds_total += $result1->total2do;
		
		// Merits and Flaws
		$sql = "select count(charmerits.APPROVED_DETAIL) as totaldone, count(merits.BACKGROUND_QUESTION) as total2do
				from	" . VTM_TABLE_PREFIX . "MERIT merits,
						" . VTM_TABLE_PREFIX . "CHARACTER_MERIT charmerits,
						" . VTM_TABLE_PREFIX . "CHARACTER characters
				where	merits.ID = charmerits.MERIT_ID
					and	characters.ID = %d
					and characters.ID = charmerits.CHARACTER_ID
					and	merits.BACKGROUND_QUESTION != '';";
		$sql = $wpdb->prepare($sql, $characterID);
		$result2 = $wpdb->get_row($sql);
		$this->backgrounds_done += $result2->totaldone;
		$this->backgrounds_total += $result2->total2do;
		
		// Misc questions
		$sql = "SELECT COUNT(ID) as total2do FROM " . VTM_TABLE_PREFIX . "EXTENDED_BACKGROUND WHERE VISIBLE = 'Y'";
		$this->backgrounds_total += $wpdb->get_var($sql);
		
		$sql = "SELECT COUNT(questions.ID) AS totaldone
				FROM
					" . VTM_TABLE_PREFIX . "CHARACTER_EXTENDED_BACKGROUND as charquest,
					" . VTM_TABLE_PREFIX . "EXTENDED_BACKGROUND as questions
				WHERE
					charquest.CHARACTER_ID = %s
					AND charquest.QUESTION_ID = questions.ID
					AND questions.VISIBLE = 'Y'
					AND charquest.APPROVED_DETAIL != ''";
		$sql = $wpdb->prepare($sql, $characterID);
		$this->backgrounds_done += $wpdb->get_var($sql);

		
	}
	function getAttributes($group = "") {
		$result = array();
		if ($group == "")
			return $this->attributes;
		elseif (isset($this->attributegroups[$group]))
			return $this->attributegroups[$group];
		else
			return array();
	}
	function getAbilities($group = "") {
		$result = array();
		if ($group == "")
			return $this->abilities;
		elseif (isset($this->abilitygroups[$group]))
			return $this->abilitygroups[$group];
		else
			return array();
	}
	function getBackgrounds() {
		return $this->backgrounds;
	}
	function getDisciplines() {
		return $this->disciplines;
	}

}


/* 
-----------------------------------------------
MULTI-PAGE LIST TABLE
------------------------------------------------ */
/**
 * Base class for displaying a list of items in an ajaxified HTML table.
 *
 * @since 3.1.0
 * @access private
 *
 * @package WordPress
 * @subpackage List_Table
 */
class vtmclass_WP_List_Table {

	/**
	 * The current list of items
	 *
	 * @since 3.1.0
	 * @var array
	 * @access public
	 */
	public $items;

	/**
	 * Various information about the current table
	 *
	 * @since 3.1.0
	 * @var array
	 * @access private
	 */
	private $_args;

	/**
	 * Various information needed for displaying the pagination
	 *
	 * @since 3.1.0
	 * @var array
	 * @access private
	 */
	private $_pagination_args = array();

	/**
	 * The current screen
	 *
	 * @since 3.1.0
	 * @var object
	 * @access protected
	 */
	protected $screen;

	/**
	 * Cached bulk actions
	 *
	 * @since 3.1.0
	 * @var array
	 * @access private
	 */
	private $_actions;

	/**
	 * Cached pagination output
	 *
	 * @since 3.1.0
	 * @var string
	 * @access private
	 */
	private $_pagination;

	/**
	 * The view switcher modes.
	 *
	 * @since 4.1.0
	 * @var array
	 * @access protected
	 */
	protected $modes = array();

	/**
	 * Constructor.
	 *
	 * The child class should call this constructor from its own constructor to override
	 * the default $args.
	 *
	 * @since 3.1.0
	 * @access public
	 *
	 * @param array|string $args {
	 *     Array or string of arguments.
	 *
	 *     @type string $plural   Plural value used for labels and the objects being listed.
	 *                            This affects things such as CSS class-names and nonces used
	 *                            in the list table, e.g. 'posts'. Default empty.
	 *     @type string $singular Singular label for an object being listed, e.g. 'post'.
	 *                            Default empty
	 *     @type bool   $ajax     Whether the list table supports AJAX. This includes loading
	 *                            and sorting data, for example. If true, the class will call
	 *                            the {@see _js_vars()} method in the footer to provide variables
	 *                            to any scripts handling AJAX events. Default false.
	 *     @type string $screen   String containing the hook name used to determine the current
	 *                            screen. If left null, the current screen will be automatically set.
	 *                            Default null.
	 * }
	 */
	public function __construct( $args = array() ) {
		$args = wp_parse_args( $args, array(
			'plural' => '',
			'singular' => '',
			'ajax' => false,
			'screen' => null,
		) );

		$this->screen = convert_to_screen( $args['screen'] );

		add_filter( "manage_{$this->screen->id}_columns", array( $this, 'get_columns' ), 0 );

		if ( !$args['plural'] )
			$args['plural'] = $this->screen->base;

		$args['plural'] = sanitize_key( $args['plural'] );
		$args['singular'] = sanitize_key( $args['singular'] );

		$this->_args = $args;

		if ( $args['ajax'] ) {
			// wp_enqueue_script( 'list-table' );
			add_action( 'admin_footer', array( $this, '_js_vars' ) );
		}

		if ( empty( $this->modes ) ) {
			$this->modes = array(
				'list'    => __( 'List View' ),
				'excerpt' => __( 'Excerpt View' )
			);
		}
	}

	/**
	 * Make private properties readable for backwards compatibility.
	 *
	 * @since 4.0.0
	 * @access public
	 *
	 * @param string $name Property to get.
	 * @return mixed Property.
	 */
	public function __get( $name ) {
		return $this->$name;
	}

	/**
	 * Make private properties settable for backwards compatibility.
	 *
	 * @since 4.0.0
	 * @access public
	 *
	 * @param string $name  Property to set.
	 * @param mixed  $value Property value.
	 * @return mixed Newly-set property.
	 */
	public function __set( $name, $value ) {
		return $this->$name = $value;
	}

	/**
	 * Make private properties checkable for backwards compatibility.
	 *
	 * @since 4.0.0
	 * @access public
	 *
	 * @param string $name Property to check if set.
	 * @return bool Whether the property is set.
	 */
	public function __isset( $name ) {
		return isset( $this->$name );
	}

	/**
	 * Make private properties un-settable for backwards compatibility.
	 *
	 * @since 4.0.0
	 * @access public
	 *
	 * @param string $name Property to unset.
	 */
	public function __unset( $name ) {
		unset( $this->$name );
	}

	/**
	 * Make private/protected methods readable for backwards compatibility.
	 *
	 * @since 4.0.0
	 * @access public
	 *
	 * @param callable $name      Method to call.
	 * @param array    $arguments Arguments to pass when calling.
	 * @return mixed|bool Return value of the callback, false otherwise.
	 */
	public function __call( $name, $arguments ) {
		return call_user_func_array( array( $this, $name ), $arguments );
	}

	/**
	 * Checks the current user's permissions
	 *
	 * @since 3.1.0
	 * @access public
	 * @abstract
	 */
	public function ajax_user_can() {
		die( 'function WP_List_Table::ajax_user_can() must be over-ridden in a sub-class.' );
	}

	/**
	 * Prepares the list of items for displaying.
	 * @uses WP_List_Table::set_pagination_args()
	 *
	 * @since 3.1.0
	 * @access public
	 * @abstract
	 */
	public function prepare_items() {
		die( 'function WP_List_Table::prepare_items() must be over-ridden in a sub-class.' );
	}

	/**
	 * An internal method that sets all the necessary pagination arguments
	 *
	 * @param array $args An associative array with information about the pagination
	 * @access protected
	 */
	protected function set_pagination_args( $args ) {
		$args = wp_parse_args( $args, array(
			'total_items' => 0,
			'total_pages' => 0,
			'per_page' => 0,
		) );

		if ( !$args['total_pages'] && $args['per_page'] > 0 )
			$args['total_pages'] = ceil( $args['total_items'] / $args['per_page'] );

		// Redirect if page number is invalid and headers are not already sent.
		if ( ! headers_sent() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) && $args['total_pages'] > 0 && $this->get_pagenum() > $args['total_pages'] ) {
			wp_redirect( add_query_arg( 'paged', $args['total_pages'] ) );
			exit;
		}

		$this->_pagination_args = $args;
	}

	/**
	 * Access the pagination args.
	 *
	 * @since 3.1.0
	 * @access public
	 *
	 * @param string $key Pagination argument to retrieve. Common values include 'total_items',
	 *                    'total_pages', 'per_page', or 'infinite_scroll'.
	 * @return int Number of items that correspond to the given pagination argument.
	 */
	public function get_pagination_arg( $key ) {
		if ( 'page' == $key )
			return $this->get_pagenum();

		if ( isset( $this->_pagination_args[$key] ) )
			return $this->_pagination_args[$key];
	}

	/**
	 * Whether the table has items to display or not
	 *
	 * @since 3.1.0
	 * @access public
	 *
	 * @return bool
	 */
	public function has_items() {
		return !empty( $this->items );
	}

	/**
	 * Message to be displayed when there are no items
	 *
	 * @since 3.1.0
	 * @access public
	 */
	public function no_items() {
		_e( 'No items found.' );
	}

	/**
	 * Display the search box.
	 *
	 * @since 3.1.0
	 * @access public
	 *
	 * @param string $text The search button text
	 * @param string $input_id The search input id
	 */
	public function search_box( $text, $input_id ) {
		if ( empty( $_REQUEST['s'] ) && !$this->has_items() )
			return;

		$input_id = $input_id . '-search-input';

		if ( ! empty( $_REQUEST['orderby'] ) )
			echo '<input type="hidden" name="orderby" value="' . esc_attr( $_REQUEST['orderby'] ) . '" />';
		if ( ! empty( $_REQUEST['order'] ) )
			echo '<input type="hidden" name="order" value="' . esc_attr( $_REQUEST['order'] ) . '" />';
		if ( ! empty( $_REQUEST['post_mime_type'] ) )
			echo '<input type="hidden" name="post_mime_type" value="' . esc_attr( $_REQUEST['post_mime_type'] ) . '" />';
		if ( ! empty( $_REQUEST['detached'] ) )
			echo '<input type="hidden" name="detached" value="' . esc_attr( $_REQUEST['detached'] ) . '" />';
?>
<p class="search-box">
	<label class="screen-reader-text" for="<?php echo $input_id ?>"><?php echo $text; ?>:</label>
	<input type="search" id="<?php echo $input_id ?>" name="s" value="<?php _admin_search_query(); ?>" />
	<?php submit_button( $text, 'button', false, false, array('id' => 'search-submit') ); ?>
</p>
<?php
	}

	/**
	 * Get an associative array ( id => link ) with the list
	 * of views available on this table.
	 *
	 * @since 3.1.0
	 * @access protected
	 *
	 * @return array
	 */
	protected function get_views() {
		return array();
	}

	/**
	 * Display the list of views available on this table.
	 *
	 * @since 3.1.0
	 * @access public
	 */
	public function views() {
		$views = $this->get_views();
		/**
		 * Filter the list of available list table views.
		 *
		 * The dynamic portion of the hook name, `$this->screen->id`, refers
		 * to the ID of the current screen, usually a string.
		 *
		 * @since 3.5.0
		 *
		 * @param array $views An array of available list table views.
		 */
		$views = apply_filters( "views_{$this->screen->id}", $views );

		if ( empty( $views ) )
			return;

		echo "<ul class='subsubsub'>\n";
		foreach ( $views as $class => $view ) {
			$views[ $class ] = "\t<li class='$class'>$view";
		}
		echo implode( " |</li>\n", $views ) . "</li>\n";
		echo "</ul>";
	}

	/**
	 * Get an associative array ( option_name => option_title ) with the list
	 * of bulk actions available on this table.
	 *
	 * @since 3.1.0
	 * @access protected
	 *
	 * @return array
	 */
	protected function get_bulk_actions() {
		return array();
	}

	/**
	 * Display the bulk actions dropdown.
	 *
	 * @since 3.1.0
	 * @access protected
	 *
	 * @param string $which The location of the bulk actions: 'top' or 'bottom'.
	 *                      This is designated as optional for backwards-compatibility.
	 */
	protected function bulk_actions( $which = '' ) {
		if ( is_null( $this->_actions ) ) {
			$no_new_actions = $this->_actions = $this->get_bulk_actions();
			/**
			 * Filter the list table Bulk Actions drop-down.
			 *
			 * The dynamic portion of the hook name, `$this->screen->id`, refers
			 * to the ID of the current screen, usually a string.
			 *
			 * This filter can currently only be used to remove bulk actions.
			 *
			 * @since 3.5.0
			 *
			 * @param array $actions An array of the available bulk actions.
			 */
			$this->_actions = apply_filters( "bulk_actions-{$this->screen->id}", $this->_actions );
			$this->_actions = array_intersect_assoc( $this->_actions, $no_new_actions );
			$two = '';
		} else {
			$two = '2';
		}

		if ( empty( $this->_actions ) )
			return;

		echo "<label for='bulk-action-selector-" . esc_attr( $which ) . "' class='screen-reader-text'>" . __( 'Select bulk action' ) . "</label>";
		echo "<select name='action$two' id='bulk-action-selector-" . esc_attr( $which ) . "'>\n";
		echo "<option value='-1' selected='selected'>" . __( 'Bulk Actions' ) . "</option>\n";

		foreach ( $this->_actions as $name => $title ) {
			$class = 'edit' == $name ? ' class="hide-if-no-js"' : '';

			echo "\t<option value='$name'$class>$title</option>\n";
		}

		echo "</select>\n";

		submit_button( __( 'Apply' ), 'action', false, false, array( 'id' => "doaction$two" ) );
		echo "\n";
	}

	/**
	 * Get the current action selected from the bulk actions dropdown.
	 *
	 * @since 3.1.0
	 * @access public
	 *
	 * @return string|bool The action name or False if no action was selected
	 */
	public function current_action() {
		if ( isset( $_REQUEST['filter_action'] ) && ! empty( $_REQUEST['filter_action'] ) )
			return false;

		if ( isset( $_REQUEST['action'] ) && -1 != $_REQUEST['action'] )
			return $_REQUEST['action'];

		if ( isset( $_REQUEST['action2'] ) && -1 != $_REQUEST['action2'] )
			return $_REQUEST['action2'];

		return false;
	}

	/**
	 * Generate row actions div
	 *
	 * @since 3.1.0
	 * @access protected
	 *
	 * @param array $actions The list of actions
	 * @param bool $always_visible Whether the actions should be always visible
	 * @return string
	 */
	protected function row_actions( $actions, $always_visible = false ) {
		$action_count = count( $actions );
		$i = 0;

		if ( !$action_count )
			return '';

		$out = '<div class="' . ( $always_visible ? 'row-actions visible' : 'row-actions' ) . '">';
		foreach ( $actions as $action => $link ) {
			++$i;
			( $i == $action_count ) ? $sep = '' : $sep = ' | ';
			$out .= "<span class='$action'>$link$sep</span>";
		}
		$out .= '</div>';

		return $out;
	}

	/**
	 * Display a monthly dropdown for filtering items
	 *
	 * @since 3.1.0
	 * @access protected
	 *
	 * @param string $post_type
	 */
	protected function months_dropdown( $post_type ) {
		global $wpdb, $wp_locale;

		$months = $wpdb->get_results( $wpdb->prepare( "
			SELECT DISTINCT YEAR( post_date ) AS year, MONTH( post_date ) AS month
			FROM $wpdb->posts
			WHERE post_type = %s
			ORDER BY post_date DESC
		", $post_type ) );

		/**
		 * Filter the 'Months' drop-down results.
		 *
		 * @since 3.7.0
		 *
		 * @param object $months    The months drop-down query results.
		 * @param string $post_type The post type.
		 */
		$months = apply_filters( 'months_dropdown_results', $months, $post_type );

		$month_count = count( $months );

		if ( !$month_count || ( 1 == $month_count && 0 == $months[0]->month ) )
			return;

		$m = isset( $_GET['m'] ) ? (int) $_GET['m'] : 0;
?>
		<label for="filter-by-date" class="screen-reader-text"><?php _e( 'Filter by date' ); ?></label>
		<select name="m" id="filter-by-date">
			<option<?php selected( $m, 0 ); ?> value="0"><?php _e( 'All dates' ); ?></option>
<?php
		foreach ( $months as $arc_row ) {
			if ( 0 == $arc_row->year )
				continue;

			$month = zeroise( $arc_row->month, 2 );
			$year = $arc_row->year;

			printf( "<option %s value='%s'>%s</option>\n",
				selected( $m, $year . $month, false ),
				esc_attr( $arc_row->year . $month ),
				/* translators: 1: month name, 2: 4-digit year */
				sprintf( __( '%1$s %2$d' ), $wp_locale->get_month( $month ), $year )
			);
		}
?>
		</select>
<?php
	}

	/**
	 * Display a view switcher
	 *
	 * @since 3.1.0
	 * @access protected
	 *
	 * @param string $current_mode
	 */
	protected function view_switcher( $current_mode ) {
?>
		<input type="hidden" name="mode" value="<?php echo esc_attr( $current_mode ); ?>" />
		<div class="view-switch">
<?php
			foreach ( $this->modes as $mode => $title ) {
				$classes = array( 'view-' . $mode );
				if ( $current_mode == $mode )
					$classes[] = 'current';
				printf(
					"<a href='%s' class='%s' id='view-switch-$mode'><span class='screen-reader-text'>%s</span></a>\n",
					esc_url( add_query_arg( 'mode', $mode ) ),
					implode( ' ', $classes ),
					$title
				);
			}
		?>
		</div>
<?php
	}

	/**
	 * Display a comment count bubble
	 *
	 * @since 3.1.0
	 * @access protected
	 *
	 * @param int $post_id          The post ID.
	 * @param int $pending_comments Number of pending comments.
	 */
	protected function comments_bubble( $post_id, $pending_comments ) {
		$pending_phrase = sprintf( __( '%s pending' ), number_format( $pending_comments ) );

		if ( $pending_comments )
			echo '<strong>';

		echo "<a href='" . esc_url( add_query_arg( 'p', $post_id, admin_url( 'edit-comments.php' ) ) ) . "' title='" . esc_attr( $pending_phrase ) . "' class='post-com-count'><span class='comment-count'>" . number_format_i18n( get_comments_number() ) . "</span></a>";

		if ( $pending_comments )
			echo '</strong>';
	}

	/**
	 * Get the current page number
	 *
	 * @since 3.1.0
	 * @access public
	 *
	 * @return int
	 */
	public function get_pagenum() {
		$pagenum = isset( $_REQUEST['paged'] ) ? absint( $_REQUEST['paged'] ) : 0;

		if( isset( $this->_pagination_args['total_pages'] ) && $pagenum > $this->_pagination_args['total_pages'] )
			$pagenum = $this->_pagination_args['total_pages'];

		return max( 1, $pagenum );
	}

	/**
	 * Get number of items to display on a single page
	 *
	 * @since 3.1.0
	 * @access protected
	 *
	 * @param string $option
	 * @param int    $default
	 * @return int
	 */
	protected function get_items_per_page( $option, $default = 20 ) {
		$per_page = (int) get_user_option( $option );
		if ( empty( $per_page ) || $per_page < 1 )
			$per_page = $default;

		/**
		 * Filter the number of items to be displayed on each page of the list table.
		 *
		 * The dynamic hook name, $option, refers to the `per_page` option depending
		 * on the type of list table in use. Possible values include: 'edit_comments_per_page',
		 * 'sites_network_per_page', 'site_themes_network_per_page', 'themes_network_per_page',
		 * 'users_network_per_page', 'edit_post_per_page', 'edit_page_per_page',
		 * 'edit_{$post_type}_per_page', etc.
		 *
		 * @since 2.9.0
		 *
		 * @param int $per_page Number of items to be displayed. Default 20.
		 */
		return (int) apply_filters( $option, $per_page );
	}

	/**
	 * Display the pagination.
	 *
	 * @since 3.1.0
	 * @access protected
	 *
	 * @param string $which
	 */
	protected function pagination( $which ) {
		if ( empty( $this->_pagination_args ) ) {
			return;
		}

		$total_items = $this->_pagination_args['total_items'];
		$total_pages = $this->_pagination_args['total_pages'];
		$infinite_scroll = false;
		if ( isset( $this->_pagination_args['infinite_scroll'] ) ) {
			$infinite_scroll = $this->_pagination_args['infinite_scroll'];
		}

		$output = '<span class="displaying-num">' . sprintf( _n( '1 item', '%s items', $total_items ), number_format_i18n( $total_items ) ) . '</span>';

		$current = $this->get_pagenum();

		$current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );

		$current_url = remove_query_arg( array( 'hotkeys_highlight_last', 'hotkeys_highlight_first' ), $current_url );

		$page_links = array();

		$disable_first = $disable_last = '';
		if ( $current == 1 ) {
			$disable_first = ' disabled';
		}
		if ( $current == $total_pages ) {
			$disable_last = ' disabled';
		}
		$page_links[] = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>",
			'first-page' . $disable_first,
			esc_attr__( 'Go to the first page' ),
			esc_url( remove_query_arg( 'paged', $current_url ) ),
			'&laquo;'
		);

		$page_links[] = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>",
			'prev-page' . $disable_first,
			esc_attr__( 'Go to the previous page' ),
			esc_url( add_query_arg( 'paged', max( 1, $current-1 ), $current_url ) ),
			'&lsaquo;'
		);

		if ( 'bottom' == $which ) {
			$html_current_page = $current;
		} else {
			$html_current_page = sprintf( "%s<input class='current-page' id='current-page-selector' title='%s' type='text' name='paged' value='%s' size='%d' />",
				'<label for="current-page-selector" class="screen-reader-text">' . __( 'Select Page' ) . '</label>',
				esc_attr__( 'Current page' ),
				$current,
				strlen( $total_pages )
			);
		}
		$html_total_pages = sprintf( "<span class='total-pages'>%s</span>", number_format_i18n( $total_pages ) );
		$page_links[] = '<span class="paging-input">' . sprintf( _x( '%1$s of %2$s', 'paging' ), $html_current_page, $html_total_pages ) . '</span>';

		$page_links[] = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>",
			'next-page' . $disable_last,
			esc_attr__( 'Go to the next page' ),
			esc_url( add_query_arg( 'paged', min( $total_pages, $current+1 ), $current_url ) ),
			'&rsaquo;'
		);

		$page_links[] = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>",
			'last-page' . $disable_last,
			esc_attr__( 'Go to the last page' ),
			esc_url( add_query_arg( 'paged', $total_pages, $current_url ) ),
			'&raquo;'
		);

		$pagination_links_class = 'pagination-links';
		if ( ! empty( $infinite_scroll ) ) {
			$pagination_links_class = ' hide-if-js';
		}
		$output .= "\n<span class='$pagination_links_class'>" . join( "\n", $page_links ) . '</span>';

		if ( $total_pages ) {
			$page_class = $total_pages < 2 ? ' one-page' : '';
		} else {
			$page_class = ' no-pages';
		}
		$this->_pagination = "<div class='tablenav-pages{$page_class}'>$output</div>";

		echo $this->_pagination;
	}

	/**
	 * Get a list of columns. The format is:
	 * 'internal-name' => 'Title'
	 *
	 * @since 3.1.0
	 * @access public
	 * @abstract
	 *
	 * @return array
	 */
	public function get_columns() {
		die( 'function WP_List_Table::get_columns() must be over-ridden in a sub-class.' );
	}

	/**
	 * Get a list of sortable columns. The format is:
	 * 'internal-name' => 'orderby'
	 * or
	 * 'internal-name' => array( 'orderby', true )
	 *
	 * The second format will make the initial sorting order be descending
	 *
	 * @since 3.1.0
	 * @access protected
	 *
	 * @return array
	 */
	protected function get_sortable_columns() {
		return array();
	}

	/**
	 * Get a list of all, hidden and sortable columns, with filter applied
	 *
	 * @since 3.1.0
	 * @access protected
	 *
	 * @return array
	 */
	protected function get_column_info() {
		if ( isset( $this->_column_headers ) )
			return $this->_column_headers;

		$columns = get_column_headers( $this->screen );
		$hidden = get_hidden_columns( $this->screen );

		$sortable_columns = $this->get_sortable_columns();
		/**
		 * Filter the list table sortable columns for a specific screen.
		 *
		 * The dynamic portion of the hook name, `$this->screen->id`, refers
		 * to the ID of the current screen, usually a string.
		 *
		 * @since 3.5.0
		 *
		 * @param array $sortable_columns An array of sortable columns.
		 */
		$_sortable = apply_filters( "manage_{$this->screen->id}_sortable_columns", $sortable_columns );

		$sortable = array();
		foreach ( $_sortable as $id => $data ) {
			if ( empty( $data ) )
				continue;

			$data = (array) $data;
			if ( !isset( $data[1] ) )
				$data[1] = false;

			$sortable[$id] = $data;
		}

		$this->_column_headers = array( $columns, $hidden, $sortable );

		return $this->_column_headers;
	}

	/**
	 * Return number of visible columns
	 *
	 * @since 3.1.0
	 * @access public
	 *
	 * @return int
	 */
	public function get_column_count() {
		list ( $columns, $hidden ) = $this->get_column_info();
		$hidden = array_intersect( array_keys( $columns ), array_filter( $hidden ) );
		return count( $columns ) - count( $hidden );
	}

	/**
	 * Print column headers, accounting for hidden and sortable columns.
	 *
	 * @since 3.1.0
	 * @access public
	 *
	 * @param bool $with_id Whether to set the id attribute or not
	 */
	public function print_column_headers( $with_id = true ) {
		list( $columns, $hidden, $sortable ) = $this->get_column_info();

		$current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
		$current_url = remove_query_arg( 'paged', $current_url );

		if ( isset( $_GET['orderby'] ) )
			$current_orderby = $_GET['orderby'];
		else
			$current_orderby = '';

		if ( isset( $_GET['order'] ) && 'desc' == $_GET['order'] )
			$current_order = 'desc';
		else
			$current_order = 'asc';

		if ( ! empty( $columns['cb'] ) ) {
			static $cb_counter = 1;
			$columns['cb'] = '<label class="screen-reader-text" for="cb-select-all-' . $cb_counter . '">' . __( 'Select All' ) . '</label>'
				. '<input id="cb-select-all-' . $cb_counter . '" type="checkbox" />';
			$cb_counter++;
		}

		foreach ( $columns as $column_key => $column_display_name ) {
			$class = array( 'manage-column', "column-$column_key" );

			$style = '';
			if ( in_array( $column_key, $hidden ) )
				$style = 'display:none;';

			$style = ' style="' . $style . '"';

			if ( 'cb' == $column_key )
				$class[] = 'check-column';
			elseif ( in_array( $column_key, array( 'posts', 'comments', 'links' ) ) )
				$class[] = 'num';

			if ( isset( $sortable[$column_key] ) ) {
				list( $orderby, $desc_first ) = $sortable[$column_key];

				if ( $current_orderby == $orderby ) {
					$order = 'asc' == $current_order ? 'desc' : 'asc';
					$class[] = 'sorted';
					$class[] = $current_order;
				} else {
					$order = $desc_first ? 'desc' : 'asc';
					$class[] = 'sortable';
					$class[] = $desc_first ? 'asc' : 'desc';
				}

				$column_display_name = '<a href="' . esc_url( add_query_arg( compact( 'orderby', 'order' ), $current_url ) ) . '"><span>' . $column_display_name . '</span><span class="sorting-indicator"></span></a>';
			}

			$id = $with_id ? "id='$column_key'" : '';

			if ( !empty( $class ) )
				$class = "class='" . join( ' ', $class ) . "'";

			echo "<th scope='col' $id $class $style>$column_display_name</th>";
		}
	}

	/**
	 * Display the table
	 *
	 * @since 3.1.0
	 * @access public
	 */
	public function display() {
		$singular = $this->_args['singular'];

		$this->display_tablenav( 'top' );

?>
<table class="wp-list-table <?php echo implode( ' ', $this->get_table_classes() ); ?>">
	<thead>
	<tr>
		<?php $this->print_column_headers(); ?>
	</tr>
	</thead>

	<tfoot>
	<tr>
		<?php $this->print_column_headers( false ); ?>
	</tr>
	</tfoot>

	<tbody id="the-list"<?php
		if ( $singular ) {
			echo " data-wp-lists='list:$singular'";
		} ?>>
		<?php $this->display_rows_or_placeholder(); ?>
	</tbody>
</table>
<?php
		$this->display_tablenav( 'bottom' );
	}

	/**
	 * Get a list of CSS classes for the list table table tag.
	 *
	 * @since 3.1.0
	 * @access protected
	 *
	 * @return array List of CSS classes for the table tag.
	 */
	protected function get_table_classes() {
		return array( 'widefat', 'fixed', $this->_args['plural'] );
	}

	/**
	 * Generate the table navigation above or below the table
	 *
	 * @since 3.1.0
	 * @access protected
	 * @param string $which
	 */
	protected function display_tablenav( $which ) {
		if ( 'top' == $which )
			wp_nonce_field( 'bulk-' . $this->_args['plural'] );
?>
	<div class="tablenav <?php echo esc_attr( $which ); ?>">

		<div class="alignleft actions bulkactions">
			<?php $this->bulk_actions( $which ); ?>
		</div>
<?php
		$this->extra_tablenav( $which );
		$this->pagination( $which );
?>

		<br class="clear" />
	</div>
<?php
	}

	/**
	 * Extra controls to be displayed between bulk actions and pagination
	 *
	 * @since 3.1.0
	 * @access protected
	 *
	 * @param string $which
	 */
	protected function extra_tablenav( $which ) {}

	/**
	 * Generate the tbody element for the list table.
	 *
	 * @since 3.1.0
	 * @access public
	 */
	public function display_rows_or_placeholder() {
		if ( $this->has_items() ) {
			$this->display_rows();
		} else {
			echo '<tr class="no-items"><td class="colspanchange" colspan="' . $this->get_column_count() . '">';
			$this->no_items();
			echo '</td></tr>';
		}
	}

	/**
	 * Generate the table rows
	 *
	 * @since 3.1.0
	 * @access public
	 */
	public function display_rows() {
		foreach ( $this->items as $item )
			$this->single_row( $item );
	}

	/**
	 * Generates content for a single row of the table
	 *
	 * @since 3.1.0
	 * @access public
	 *
	 * @param object $item The current item
	 */
	public function single_row( $item ) {
		static $row_class = '';
		$row_class = ( $row_class == '' ? ' class="alternate"' : '' );

		echo '<tr' . $row_class . '>';
		$this->single_row_columns( $item );
		echo '</tr>';
	}

	/**
	 * Generates the columns for a single row of the table
	 *
	 * @since 3.1.0
	 * @access protected
	 *
	 * @param object $item The current item
	 */
	protected function single_row_columns( $item ) {
		list( $columns, $hidden ) = $this->get_column_info();

		foreach ( $columns as $column_name => $column_display_name ) {
			$class = "class='$column_name column-$column_name'";

			$style = '';
			if ( in_array( $column_name, $hidden ) )
				$style = ' style="display:none;"';

			$attributes = "$class$style";

			if ( 'cb' == $column_name ) {
				echo '<th scope="row" class="check-column">';
				echo $this->column_cb( $item );
				echo '</th>';
			}
			elseif ( method_exists( $this, 'column_' . $column_name ) ) {
				echo "<td $attributes>";
				echo call_user_func( array( $this, 'column_' . $column_name ), $item );
				echo "</td>";
			}
			else {
				echo "<td $attributes>";
				echo $this->column_default( $item, $column_name );
				echo "</td>";
			}
		}
	}

	/**
	 * Handle an incoming ajax request (called from admin-ajax.php)
	 *
	 * @since 3.1.0
	 * @access public
	 */
	public function ajax_response() {
		$this->prepare_items();

		ob_start();
		if ( ! empty( $_REQUEST['no_placeholder'] ) ) {
			$this->display_rows();
		} else {
			$this->display_rows_or_placeholder();
		}

		$rows = ob_get_clean();

		$response = array( 'rows' => $rows );

		if ( isset( $this->_pagination_args['total_items'] ) ) {
			$response['total_items_i18n'] = sprintf(
				_n( '1 item', '%s items', $this->_pagination_args['total_items'] ),
				number_format_i18n( $this->_pagination_args['total_items'] )
			);
		}
		if ( isset( $this->_pagination_args['total_pages'] ) ) {
			$response['total_pages'] = $this->_pagination_args['total_pages'];
			$response['total_pages_i18n'] = number_format_i18n( $this->_pagination_args['total_pages'] );
		}

		die( wp_json_encode( $response ) );
	}

	/**
	 * Send required variables to JavaScript land
	 *
	 * @access public
	 */
	public function _js_vars() {
		$args = array(
			'class'  => get_class( $this ),
			'screen' => array(
				'id'   => $this->screen->id,
				'base' => $this->screen->base,
			)
		);

		printf( "<script type='text/javascript'>list_args = %s;</script>\n", wp_json_encode( $args ) );
	}
}

class vtmclass_MultiPage_ListTable extends vtmclass_WP_List_Table {
      
    function column_cb($item){
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            $this->_args['singular'],  
            $item->ID               
        );
    }

    function column_visible($item){
		return ($item->VISIBLE == "Y") ? "Yes" : "No";
    }
   
	/* Need own version of this function vtm_to deal with tabs */
	function print_column_headers( $with_id = true ) {
		list( $columns, $hidden, $sortable ) = $this->get_column_info();

		$current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
		$current_url = remove_query_arg( 'paged', $current_url );
		$current_url = remove_query_arg( 'action', $current_url );
		$current_url = add_query_arg('tab', $this->type, $current_url);

		if ( isset( $_GET['orderby'] ) && (!isset($_GET['tab']) || (isset($_GET['tab']) && $_GET['tab'] == $this->type ) ) )
			$current_orderby = $_GET['orderby'];
		else
			$current_orderby = '';

		if ( isset( $_GET['order'] ) && 'desc' == $_GET['order'] && (!isset($_GET['tab']) || (isset($_GET['tab']) && $_GET['tab'] == $this->type ) )  )
			$current_order = 'desc';
		else
			$current_order = 'asc';

		if ( ! empty( $columns['cb'] ) ) {
			static $cb_counter = 1;
			$columns['cb'] = '<label class="screen-reader-text" for="cb-select-all-' . $cb_counter . '">' . __( 'Select All' ) . '</label>'
				. '<input id="cb-select-all-' . $cb_counter . '" type="checkbox" />';
			$cb_counter++;
		}

		foreach ( $columns as $column_key => $column_display_name ) {
			$class = array( 'manage-column', "column-$column_key" );

			$style = '';
			if ( in_array( $column_key, $hidden ) )
				$style = 'display:none;';

			$style = ' style="' . $style . '"';

			if ( 'cb' == $column_key )
				$class[] = 'check-column';
			elseif ( in_array( $column_key, array( 'posts', 'comments', 'links' ) ) )
				$class[] = 'num';

			if ( isset( $sortable[$column_key] ) ) {
				list( $orderby, $desc_first ) = $sortable[$column_key];

				if ( $current_orderby == $orderby ) {
					$order = 'asc' == $current_order ? 'desc' : 'asc';
					$class[] = 'sorted';
					$class[] = $current_order;
				} else {
					$order = $desc_first ? 'desc' : 'asc';
					$class[] = 'sortable';
					$class[] = $desc_first ? 'asc' : 'desc';
				}

				$column_display_name = '<a href="' . esc_url( add_query_arg( compact( 'orderby', 'order' ), $current_url ) ) . '"><span>' . $column_display_name . '</span><span class="sorting-indicator"></span></a>';
			}

			$id = $with_id ? "id='$column_key'" : '';

			if ( !empty( $class ) )
				$class = "class='" . join( ' ', $class ) . "'";

			echo "<th scope='col' $id $class $style>$column_display_name</th>";
		}
	}


}

class vtmclass_Report_ListTable extends vtmclass_WP_List_Table {

	var $pagewidth;
	var $lineheight;
	var $columnstartX;		// array of X values where table columns start
	var $dotable = false;	// outputting table?
	var $ytop_page;
	var $ytop_cell;
	var $ytop_data;
	var $ybottom_page;
	var $row = 0;
      
    function __construct(){
        global $status, $page;
                
        parent::__construct( array(
            'singular'  => 'row',     
            'plural'    => 'rows',    
            'ajax'      => false        
        ) );
    }
	
    function column_visible($item){
		return ($item->VISIBLE == "Y") ? "Yes" : "No";
    }
   
    function get_bulk_actions() {
        $actions = array();
        return $actions;
    }
    function process_bulk_action() {
        		
        
    }
	
	function load_filters() {
		global $wpdb;
		
		/* get defaults */
		$default_character_visible = "Y";
		
		$sql = "SELECT ID FROM " . VTM_TABLE_PREFIX. "PLAYER_STATUS WHERE NAME = %s";
		$result = $wpdb->get_results($wpdb->prepare($sql,'Active'));
		$default_player_status = $result[0]->ID;
		
		$sql = "SELECT ID FROM " . VTM_TABLE_PREFIX. "CHARACTER_TYPE WHERE NAME = %s";
		$result = $wpdb->get_results($wpdb->prepare($sql,'PC'));
		$default_character_type    = $result[0]->ID;
		
		$sql = "SELECT ID FROM " . VTM_TABLE_PREFIX. "CHARACTER_STATUS WHERE NAME = %s";
		$result = $wpdb->get_results($wpdb->prepare($sql,'Alive'));
		$default_character_status  = $result[0]->ID;
		
		/* get filter options */
		$sql = "SELECT ID, NAME FROM " . VTM_TABLE_PREFIX. "PLAYER_STATUS";
		$this->filter_player_status = vtm_make_filter($wpdb->get_results($sql));
		
		$sql = "SELECT ID, NAME FROM " . VTM_TABLE_PREFIX. "CHARACTER_TYPE";
		$this->filter_character_type = vtm_make_filter($wpdb->get_results($sql));
		
		$sql = "SELECT ID, NAME FROM " . VTM_TABLE_PREFIX. "CHARACTER_STATUS";
		$this->filter_character_status = vtm_make_filter($wpdb->get_results($sql));		
		
		/* set active filters */
		if ( isset( $_REQUEST['player_status'] ) && array_key_exists( $_REQUEST['player_status'], $this->filter_player_status ) ) {
			$this->active_filter_player_status = sanitize_key( $_REQUEST['player_status'] );
		} else {
			$this->active_filter_player_status = $default_player_status;
		}
		if ( isset( $_REQUEST['character_type'] ) && array_key_exists( $_REQUEST['character_type'], $this->filter_character_type ) ) {
			$this->active_filter_character_type = sanitize_key( $_REQUEST['character_type'] );
		} else {
			$this->active_filter_character_type = $default_character_type;
		}
		if ( isset( $_REQUEST['character_status'] ) && array_key_exists( $_REQUEST['character_status'], $this->filter_character_status ) ) {
			$this->active_filter_character_status = sanitize_key( $_REQUEST['character_status'] );
		} else {
			$this->active_filter_character_status = $default_character_status;
		}
		if ( isset( $_REQUEST['character_visible'] )) {
			$this->active_filter_character_visible = strtoupper(sanitize_key( $_REQUEST['character_visible'] ));
		} else {
			$this->active_filter_character_visible = $default_character_visible;
		}
		
	
	}
	
	function get_filter_sql() {
	
		$sql = "";
		$args = array();
				
		if ( "all" !== $this->active_filter_player_status) {
			$sql .= " AND players.PLAYER_STATUS_ID = %s";
			array_push($args, $this->active_filter_player_status);
		}
		if ( "all" !== $this->active_filter_character_type) {
			$sql .= " AND characters.CHARACTER_TYPE_ID = %s";
			array_push($args, $this->active_filter_character_type);
		}
		if ( "all" !== $this->active_filter_character_status) {
			$sql .= " AND characters.CHARACTER_STATUS_ID = %s";
			array_push($args, $this->active_filter_character_status);
		}
		if ( "ALL" !== $this->active_filter_character_visible) {
			$sql .= " AND characters.VISIBLE = %s";
			array_push($args, $this->active_filter_character_visible);
		}
		
		return array($sql, $args);
	
	}

	function filter_tablenav() {
			echo "<label>Player Status: </label>";
			if ( !empty( $this->filter_player_status ) ) {
				echo "<select name='player_status'>";
				foreach( $this->filter_player_status as $key => $value ) {
					echo '<option value="' . esc_attr( $key ) . '" ';
					selected( $this->active_filter_player_status, $key );
					echo '>' . esc_attr( $value ) . '</option>';
				}
				echo '</select>';
			}
			
			echo "<label>Character Type: </label>";
			if ( !empty( $this->filter_character_type ) ) {
				echo "<select name='character_type'>";
				foreach( $this->filter_character_type as $key => $value ) {
					echo '<option value="' . esc_attr( $key ) . '" ';
					selected( $this->active_filter_character_type, $key );
					echo '>' . esc_attr( $value ) . '</option>';
				}
				echo '</select>';
			}
			
			echo "<label>Character Status: </label>";
			if ( !empty( $this->filter_character_status ) ) {
				echo "<select name='character_status'>";
				foreach( $this->filter_character_status as $key => $value ) {
					echo '<option value="' . esc_attr( $key ) . '" ';
					selected( $this->active_filter_character_status, $key );
					echo '>' . esc_attr( $value ) . '</option>';
				}
				echo '</select>';
			}
			
			echo "<label>Character Visibility: </label>";
			echo "<select name='character_visible'>";
			echo '<option value="all" ';
					selected( $this->active_filter_character_visible, 'all' );
					echo '>All</option>';
			echo '<option value="Y" ';
					selected( $this->active_filter_character_visible, 'Y' );
					echo '>Yes</option>';
			echo '<option value="N" ';
					selected( $this->active_filter_character_visible, 'N' );
					echo '>No</option>';
			echo '</select>';
			
			submit_button( 'Filter', 'secondary', 'do_filter_tablenav', false );
			echo "<label>Download: </label>";
			echo "<a class='button-primary' href='" . plugins_url( 'vtm-character/tmp/report.pdf') . "'>PDF</a>";
			echo "<a class='button-primary' href='" . plugins_url( 'vtm-character/tmp/report.csv') . "'>CSV</a>";
	}
	 
	function extra_tablenav($which) {
		if ($which == 'top')  {
			echo "<div class='gvfilter'>";
			$this->filter_tablenav();
		
			echo "</div>";
		}
	}
	
	/* Add Headings function vtm_to add report name to sort url */
       function print_column_headers( $with_id = true ) {	
			list( $columns, $hidden, $sortable ) = $this->get_column_info();

			$current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
			$current_url = remove_query_arg( 'paged', $current_url );
			if (isset($_REQUEST['report']))
				$current_url = add_query_arg('report', $_REQUEST['report']);

			if ( isset( $_GET['orderby'] ) )
					$current_orderby = $_GET['orderby'];
			else
					$current_orderby = '';

			if ( isset( $_GET['order'] ) && 'desc' == $_GET['order'] )
					$current_order = 'desc';
			else
					$current_order = 'asc';

			if ( ! empty( $columns['cb'] ) ) {
					static $cb_counter = 1;
					$columns['cb'] = '<label class="screen-reader-text" for="cb-select-all-' . $cb_counter . '">' . __( 'Select All' ) . '</label>'
							. '<input id="cb-select-all-' . $cb_counter . '" type="checkbox" />';
					$cb_counter++;
			}

			foreach ( $columns as $column_key => $column_display_name ) {
					$class = array( 'manage-column', "column-$column_key" );

					$style = '';
					if ( in_array( $column_key, $hidden ) )
							$style = 'display:none;';

					$style = ' style="' . $style . '"';

					if ( 'cb' == $column_key )
							$class[] = 'check-column';
					elseif ( in_array( $column_key, array( 'posts', 'comments', 'links' ) ) )
							$class[] = 'num';

					if ( isset( $sortable[$column_key] ) ) {
							list( $orderby, $desc_first ) = $sortable[$column_key];

							if ( $current_orderby == $orderby ) {
									$order = 'asc' == $current_order ? 'desc' : 'asc';
									$class[] = 'sorted';
									$class[] = $current_order;
							} else {
									$order = $desc_first ? 'desc' : 'asc';
									$class[] = 'sortable';
									$class[] = $desc_first ? 'asc' : 'desc';
							}

							$column_display_name = '<a href="' . esc_url( add_query_arg( compact( 'orderby', 'order' ), $current_url ) ) . '"><span>' . $column_display_name . '</span><span class="sorting-indicator"></span></a>';
					}

					$id = $with_id ? "id='$column_key'" : '';

					if ( !empty( $class ) )
							$class = "class='" . join( ' ', $class ) . "'";

					echo "<th scope='col' $id $class $style>$column_display_name</th>";
			}
	}
	
	function set_column_widths($columns = "") {
		$colwidths = array();
		$count = count($columns);
		if (!empty($columns))
			foreach ($columns as $column => $coldesc) {
				$colwidths[$column] = ($this->pagewidth - 10) / $count;
			}
		return $colwidths;
	}
	function get_column_names($columns = "") {
		$colnames = array();
		$col = 0;
		if (!empty($columns))
			foreach ($columns as $column => $coldesc) {
				$colnames[$col] = $column;
				$col++;
			}
		return $colnames;
	}
	function set_column_alignment($columns = "") {
		$colwidths = array();
		$count = count($columns);
		if (!empty($columns))
			foreach ($columns as $column => $coldesc) {
				$colwidths[$column] = 'L';
			}
		return $colwidths;
	}
 
	
	function output_report ($title, $orientation = 'L') {
		
		$pdf = new vtmclass_PDFreport($orientation,'mm','A4');
		
		if ($orientation == 'L') $pdf->pagewidth = 297;
		if ($orientation == 'P') $pdf->pagewidth = 210;
		
		$pdf->title = $title;
		$pdf->SetTitle($title);
		$pdf->AliasNbPages();
		$pdf->SetMargins(5, 5, 5);
		$pdf->AddPage();
		
		$this->ytop_page = $pdf->GetY();
		$pdf->SetY(-15);
		$this->ybottom_page = $pdf->GetY();
		$pdf->SetY($this->ytop_page);
		
		$columns = $this->get_columns();
		$this->pagewidth = $pdf->pagewidth;
		$colwidths  = $this->set_column_widths($columns);
		$colalign   = $this->set_column_alignment($columns);
		$colnames   = $this->get_column_names($columns);
		$lineheight = isset($this->lineheight) ? $this->lineheight : 5;
		
		$pdf->SetFont('Arial','B',9);
		$pdf->SetTextColor(255,255,255);
		$pdf->SetFillColor(255,0,0);
		
		$pdf->autobreak = false;
		$col = 0;
		foreach ($columns as $columnname => $columndesc) {
			$this->columnstartX[$col] = $pdf->GetX();
			$pdf->Cell($colwidths[$columnname],$lineheight,$columndesc,1,0,'C',1);
			$col++;
		}
		$pdf->Ln();
		
		$pdf->SetTextColor(0,0,0);
		$pdf->SetFillColor(200);
		$pdf->SetFont('Arial','',9);
		$row = 0;
		
		if (count($this->items) > 0) {
				
			// get row heights
			$rowheights = array();
			$row = 0;
			foreach ($this->items as $datarow) {
				$rowheight = 0;
				foreach ($columns as $columnname => $columndesc) {
					$raw = isset($datarow->$columnname) ? $datarow->$columnname : '';
					$text = $pdf->PrepareText($raw);
					$cellheight = $pdf->GetCellHeight($text, $colwidths[$columnname], $lineheight);
					if ($cellheight > $rowheight) $rowheight = $cellheight;
				}
				$rowheights[$row] = $rowheight;
				$row++;
			}
			
			// Print page-by-page
			//		And column-by-column
			//			And row-by-row
			$pagestartrow = 0;
			$pageendrow   = count($this->items) - 1;
			$tableendrow  = count($this->items) - 1;
			$this->row = $pagestartrow;
			$pdf->col = 0;
			$pdf->tablecols = count($columns);
			$this->ytop_data = $pdf->GetY();
			
			$ybottomcell = 0;
		
			
			while ($this->row <= $tableendrow) {
				$datarow = $this->items[$this->row];
				$columnname = $colnames[$pdf->col];
				$raw = isset($datarow->$columnname) ? $datarow->$columnname : '';
				$text    = $pdf->PrepareText($raw);
				
				if ($pdf->col == 0)
					$this->ytop_cell = $pdf->GetY();
				
				$cellheight = $pdf->GetCellHeight($text, $colwidths[$columnname], $lineheight);
				$rowheight = $rowheights[$this->row];
				
				if ($cellheight == $rowheight)
					$h = $lineheight;
				elseif ($cellheight = $lineheight)
					$h = $rowheight;
				else
					$h = $rowheight / $cellheight;
				
				//$text .= $pdf->tablecols;
				if ( ($this->ytop_cell + $rowheight) > $this->ybottom_page && $pdf->col == 0) {
					$this->ytop_cell = $this->ytop_page;
					$pdf->AddPage();
				}
				
				$pdf->SetX($this->columnstartX[$pdf->col]);
				$pdf->MultiCell($colwidths[$columnname],$h,$text,1,$colalign[$columnname], $this->row % 2);
				$ybottomcell =  $pdf->GetY();
				
				if ($pdf->col < $pdf->tablecols - 1) {
					//if ($pdf->col == 1) {$this->row = $tableendrow+1;}
					$pdf->col = $pdf->col+1;
					$pdf->SetY($this->ytop_cell);
				} else {
					$pdf->col = 0;
					$pdf->SetX($this->columnstartX[$pdf->col]);
					$this->row++;
					
				}
				
			}
			
		
			/* $this->ytop_data = $pdf->GetY();
			// output table, column by column
			foreach ($columns as $columnname => $columndesc) {
				$row = 0;
				foreach ($this->items as $datarow) {
				
					$text = $pdf->PrepareText($datarow->$columnname);
					
					$cellheight = $rowheights[$row];
					if ($cellheight == $rowheight)
						$h = $lineheight;
					elseif ($cellheight = $lineheight)
						$h = $rowheight;
					else
						$h = $rowheight / $cellheight;
					
					$pdf->MultiCell($colwidths[$columnname],$h,$text,1,$colalign[$columnname], $row % 2);
					$row++;
				}
				
				$pdf->SetY($this->ytop_data);
			}
			*/
		
		} 
		$pdf->autobreak = false;
		
		$pdf->Output(VTM_CHARACTER_URL . 'tmp/report.pdf', 'F');
		
	}
	
	function output_csv() {
		
		/* open file */
		$file = fopen(VTM_CHARACTER_URL . "tmp/report.csv","w");
		
		/* write headings */
		$columns = $this->get_columns();
		fputcsv($file, array_values($columns));
		
		/* write data */
		if (count($this->items) > 0) {
			foreach ($this->items as $datarow) {
				$data = array();
				foreach ($columns as $columnname => $columndesc) {
					$raw = isset($datarow->$columnname) ? $datarow->$columnname : '';
					array_push($data, $this->PrepareCSVText($raw));
				}
				fputcsv($file, $data);
			}
		}
		
		/* close file */
		fclose($file);
	}
	
	function PrepareCSVText($text) {
		
		$text = stripslashes($text);
		$text = str_ireplace("\r", "", $text);
		
		/* remove extra whitespace and trailing newlines */
		$text = trim($text);
	
		return $text;
	}

}

/* 
-----------------------------------------------
PRINT REPORT
------------------------------------------------ */
class vtmclass_PDFreport extends FPDF {

	var $title;
	var $pagewidth = 297;
	var $col = 0;
	var $tablecols = 0;
	var $autobreak = true;

	function Header()
	{

		$this->SetFont('Arial','B',16);
		$this->SetTextColor(0,0,0);
		$this->Cell(0,10,$this->title,0,1,'C');
		$this->Ln(2);
	}

	function Footer()
	{		
		$footerdate = date_i18n(get_option('date_format'));
	
		$this->SetY(-15);
		$this->SetFont('Arial','I',8);
		$this->SetLineWidth(0.3);
		
		$this->Cell(0,10,'Report | Page ' . $this->PageNo().' of {nb} | Generated on ' . $footerdate,'T',0,'C');
	}
	
	function GetCellHeight($text, $cellwidth, $lineheight) {
		
		$lines = ceil( $this->GetStringWidth($text) / ($cellwidth - 1) );
		
		$height = ceil($lineheight * $lines);
		
		/* plus anything from extra newlines */
		$height = $height + ($lineheight * substr_count($text, "\n"));
		
		return $height;
	}

	function PrepareText($text) {
		
		$text = stripslashes($text);
		$text = str_ireplace("<br>", "\n", $text);
		$text = str_ireplace("<br />", "\n", $text);
		$text = str_ireplace("<i>", "", $text);
		$text = str_ireplace("</i>", "", $text);
		$text = str_ireplace("<b>", "", $text);
		$text = str_ireplace("</b>", "", $text);
		
		/* remove extra whitespace and trailing newlines */
		$text = trim($text);
	
		return $text;
	}
	
	function AcceptPageBreak()
	{
		if ($this->autobreak)
			return true;
			
		// Method accepting or not automatic page break
		if($this->col < $this->tablecols)
		{
			// Keep on page
			return false;
		}
		else
		{
			return true;
		}
	} 

}



?>