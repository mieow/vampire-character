<?php
/* ---------------------------------------------------------------
<CHARACTER>
	<NAME>Character Name</NAME>
	<PLAYER>Player Name</PLAYER>
	<GENERATION>Generation</GENERATION>

</CHARACTER>
------------------------------------------------------------------ */

require_once VTM_CHARACTER_URL . 'inc/classes.php';

add_action( 'template_redirect', 'vtm_android_redirect' );

function vtm_android_redirect () {
	global $wpdb;

    if( $_SERVER['REQUEST_URI'] == vtm_get_config('ANDROID_LINK') && is_user_logged_in() ) {
		$character = vtm_establishCharacter('Ugly Duckling');
		$characterID = vtm_establishCharacterID($character);
		$mycharacter = new vtmclass_character();
		$mycharacter->load($characterID);

		header("Content-type: text/xml");
		echo "<?xml version='1.0' encoding='ISO-8859-1'?>\n";
		
		echo "<CHARACTER>\n";

		/* Character Info */
		echo vtm_output_xlmtag("NAME",         $mycharacter->name);
		echo vtm_output_xlmtag("PLAYER",       $mycharacter->player);
		echo vtm_output_xlmtag("GENERATION",   $mycharacter->generation);
		echo vtm_output_xlmtag("PUBLIC_CLAN",  $mycharacter->clan);
		echo vtm_output_xlmtag("PRIVATE_CLAN", $mycharacter->private_clan);
		echo vtm_output_xlmtag("CURRENT_XP",   $mycharacter->current_experience);
		echo vtm_output_xlmtag("PENDING_XP",   $mycharacter->pending_experience);
		echo vtm_output_xlmtag("CURRENT_WP",   $mycharacter->current_willpower);
		echo vtm_output_xlmtag("WILLPOWER",    $mycharacter->willpower);
		echo vtm_output_xlmtag("BLOODPOOL",    $mycharacter->bloodpool);
		echo vtm_output_xlmtag("BLOOD_PER_ROUND",       $mycharacter->blood_per_round);
		echo vtm_output_xlmtag("PATH_OF_ENLIGHTENMENT", $mycharacter->path_of_enlightenment);
		echo vtm_output_xlmtag("PATH_LEVEL",   $mycharacter->path_rating);
		echo vtm_output_xlmtag("DOB",          $mycharacter->date_of_birth);
		echo vtm_output_xlmtag("DOE",          $mycharacter->date_of_embrace);
		echo vtm_output_xlmtag("SIRE",         $mycharacter->sire);
		echo vtm_output_xlmtag("CONCEPT",      $mycharacter->concept);
		echo vtm_output_xlmtag("CLAN_FLAW",    $mycharacter->clan_flaw);
		
		if (vtm_get_config('USE_NATURE_DEMEANOUR') == 'Y') {
			echo vtm_output_xlmtag("NATURE",    $mycharacter->nature);
			echo vtm_output_xlmtag("DEMEANOUR", $mycharacter->demeanour);
		}
		
		/* Attributes */
		echo "\t<ATTRIBUTES>\n";
		echo "\t\t<PHYSICAL>\n";
		foreach ($mycharacter->getAttributes("Physical") as $attribute) {
			echo "\t\t\t<ATTRIBUTE>\n";
			echo vtm_output_xlmtag("NAME",       $attribute->name);
			echo vtm_output_xlmtag("LEVEL",      $attribute->level);
			echo vtm_output_xlmtag("PENDINGXP",  $attribute->pending);
			echo vtm_output_xlmtag("ORDER",      $attribute->ordering);
			echo vtm_output_xlmtag("SPECIALITY",  $attribute->specialty);
			echo "\t\t\t</ATTRIBUTE>\n";
		}
		echo "\t\t</PHYSICAL>\n"; 
		echo "\t\t<SOCIAL>\n";
		foreach ($mycharacter->getAttributes("Social") as $attribute) {
			echo "\t\t\t<ATTRIBUTE>\n";
			echo vtm_output_xlmtag("NAME",       $attribute->name);
			echo vtm_output_xlmtag("LEVEL",      $attribute->level);
			echo vtm_output_xlmtag("PENDINGXP",  $attribute->pending);
			echo vtm_output_xlmtag("ORDER",      $attribute->ordering);
			echo vtm_output_xlmtag("SPECIALITY",  $attribute->specialty);
			echo "\t\t\t</ATTRIBUTE>\n";
		}
		echo "\t\t</SOCIAL>\n"; 
		echo "\t\t<MENTAL>\n";
		foreach ($mycharacter->getAttributes("Mental") as $attribute) {
			echo "\t\t\t<ATTRIBUTE>\n";
			echo vtm_output_xlmtag("NAME",       $attribute->name);
			echo vtm_output_xlmtag("LEVEL",      $attribute->level);
			echo vtm_output_xlmtag("PENDINGXP",  $attribute->pending);
			echo vtm_output_xlmtag("ORDER",      $attribute->ordering);
			echo vtm_output_xlmtag("SPECIALITY",  $attribute->specialty);
			echo "\t\t\t</ATTRIBUTE>\n";
		}
		echo "\t\t</MENTAL>\n"; 
		echo "\t</ATTRIBUTES>\n";
		
		/* Abilities */
		$abilities = $mycharacter->getAbilities();
		echo "\t<ABILITIES>\n";
		foreach ($abilities as $ability) {
			echo "\t\t<ABILITY>\n";
			echo vtm_output_xlmtag("NAME",       $ability->skillname);
			echo vtm_output_xlmtag("LEVEL",      $ability->level);
			echo vtm_output_xlmtag("PENDINGXP",  $ability->pending);
			echo vtm_output_xlmtag("GROUPING",   $ability->grouping);
			echo vtm_output_xlmtag("SPECIALITY", $ability->specialty);
			echo "\t\t</ABILITY>\n";
		}
		echo "\t</ABILITIES>\n";
		
		/* Backgrounds */
		$backgrounds =  $mycharacter->getBackgrounds();
		echo "\t<BACKGROUNDS>\n";
		foreach ($backgrounds as $background) {
			echo "\t\t<BACKGROUND>\n";
			echo vtm_output_xlmtag("NAME",       $background->background);
			echo vtm_output_xlmtag("LEVEL",      $background->level);
			echo vtm_output_xlmtag("SECTOR",     $background->sector);
			echo vtm_output_xlmtag("COMMENT",    $background->comment);
			echo "\t\t</BACKGROUND>\n";
		}
		echo "\t</BACKGROUNDS>\n";
		
		/* Disciplines */
		$disciplines =  $mycharacter->getDisciplines();
		echo "\t<DISCIPLINES>\n";
		foreach ($disciplines as $discipline) {
			echo "\t\t<DISCIPLINE>\n";
			echo vtm_output_xlmtag("NAME",       $discipline->name);
			echo vtm_output_xlmtag("LEVEL",      $discipline->level);
			echo vtm_output_xlmtag("PENDINGXP",  $discipline->pending);
			echo "\t\t</DISCIPLINE>\n";
		}
		echo "\t</DISCIPLINES>\n";

		/* Merits and Flaws */
		$merits =  $mycharacter->meritsandflaws;
		echo "\t<MERITSANDFLAWS>\n";
		foreach ($merits as $merit) {
			echo "\t\t<MERITFLAW>\n";
			echo vtm_output_xlmtag("NAME",       $merit->name);
			echo vtm_output_xlmtag("LEVEL",      $merit->level);
			echo vtm_output_xlmtag("PENDINGXP",  $merit->pending);
			echo vtm_output_xlmtag("COMMENT",    $merit->comment);
			echo "\t\t</MERITFLAW>\n";
		}
		echo "\t</MERITSANDFLAWS>\n";

		/* Virtues */
		$virtues =  $mycharacter->getAttributes("Virtue");
		echo "\t<VIRTUES>\n";
		foreach ($virtues as $virtue) {
			echo "\t\t<VIRTUE>\n";
			echo vtm_output_xlmtag("NAME",       $virtue->name);
			echo vtm_output_xlmtag("LEVEL",      $virtue->level);
			echo vtm_output_xlmtag("PENDINGXP",  $virtue->pending);
			echo vtm_output_xlmtag("ORDER",      $virtue->ordering);
			echo "\t\t</VIRTUE>\n";
		}
		echo "\t</VIRTUES>\n";
		
		/* Rituals */
		$rituals = $mycharacter->rituals;
		echo "\t<RITUALS>\n";
		foreach ($rituals as $majikdiscipline => $rituallist) {
			foreach ($rituallist as $ritual) {
				echo "\t\t<RITUAL>\n";
				echo vtm_output_xlmtag("NAME",       $ritual['name']);
				echo vtm_output_xlmtag("LEVEL",      $ritual['level']);
				echo vtm_output_xlmtag("PENDINGXP",  $ritual['pending']);
				echo vtm_output_xlmtag("DISCIPLINE", $majikdiscipline);
				echo "\t\t</RITUAL>\n";
			} 
		}
		echo "\t</RITUALS>\n";
		
		
		/* Combo Disciplines */
		$combodisciplines = $mycharacter->combo_disciplines;
		echo "\t<COMBODISCIPLINES>\n";
		if (count($combodisciplines) > 0) {
			foreach ($combodisciplines as $discipline) {
				echo vtm_output_xlmtag("DISCIPLINE", $discipline);
			}
		}
		echo "\t</COMBODISCIPLINES>\n";
		
		/* NEED TO ADD PATHS */
		
		echo "</CHARACTER>\n";
		
		exit;
	} 
}

function vtm_output_xlmtag ($tagname, $value) {
	return (empty($value) ? "" : "\t\t\t\t<$tagname>$value</$tagname>\n");
}

function vtm_get_config ($field) {

        global $wpdb;
        $sql = "SELECT $field FROM " . VTM_TABLE_PREFIX . "CONFIG";
        $configs = $wpdb->get_results($sql);

        return $configs[0]->$field;

}
?>