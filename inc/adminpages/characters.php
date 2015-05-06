<?php


function vtm_character_options() {
	global $wpdb;

	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	
	$iconurl = plugins_url('adminpages/icons/',dirname(__FILE__));
	
	// setup filter options
	$options_player_status    = vtm_make_filter($wpdb->get_results("SELECT ID, NAME FROM " . VTM_TABLE_PREFIX. "PLAYER_STATUS"));
	$options_character_status = vtm_make_filter($wpdb->get_results("SELECT ID, NAME FROM " . VTM_TABLE_PREFIX. "CHARACTER_STATUS"));
	$options_character_type   = vtm_make_filter($wpdb->get_results("SELECT ID, NAME FROM " . VTM_TABLE_PREFIX. "CHARACTER_TYPE"));
	$options_chargen_status   = vtm_make_filter($wpdb->get_results("SELECT ID, NAME FROM " . VTM_TABLE_PREFIX. "CHARGEN_STATUS"));
	
	// Set up default filter values
	$default_player_status     = $wpdb->get_var($wpdb->prepare("SELECT ID FROM " . VTM_TABLE_PREFIX. "PLAYER_STATUS     WHERE NAME = %s",'Active'));
	$default_character_status  = $wpdb->get_var($wpdb->prepare("SELECT ID FROM " . VTM_TABLE_PREFIX. "CHARACTER_STATUS  WHERE NAME = %s",'Alive'));
	$default_character_type    = $wpdb->get_var($wpdb->prepare("SELECT ID FROM " . VTM_TABLE_PREFIX. "CHARACTER_TYPE    WHERE NAME = %s",'PC'));
	$default_chargen_status    = $wpdb->get_var($wpdb->prepare("SELECT ID FROM " . VTM_TABLE_PREFIX. "CHARGEN_STATUS    WHERE NAME = %s",'Approved'));
	$default_character_visible = "y";
	
	// set active filter
	if ( isset( $_REQUEST['player_status'] ) && array_key_exists( $_REQUEST['player_status'], $options_player_status ) )
		$active_player_status = sanitize_key( $_REQUEST['player_status'] );
	else $active_player_status = $default_player_status;
	if ( isset( $_REQUEST['character_status'] ) && array_key_exists( $_REQUEST['character_status'], $options_character_status ) )
		$active_character_status = sanitize_key( $_REQUEST['character_status'] );
	else $active_character_status = $default_character_status;
	if ( isset( $_REQUEST['character_type'] ) && array_key_exists( $_REQUEST['character_type'], $options_character_type ) )
		$active_character_type = sanitize_key( $_REQUEST['character_type'] );
	else $active_character_type = $default_character_type;
	if ( isset( $_REQUEST['chargen_status'] ) && array_key_exists( $_REQUEST['chargen_status'], $options_character_status ) )
		$active_chargen_status = sanitize_key( $_REQUEST['chargen_status'] );
	else $active_chargen_status = $default_chargen_status;
	if ( isset( $_REQUEST['character_visible'] ) ) $active_character_visible = sanitize_key( $_REQUEST['character_visible'] );
	else $active_character_visible = $default_character_visible;
	
	// Get web pages
	$stlinks = $wpdb->get_results("SELECT VALUE, WP_PAGE_ID FROM " . VTM_TABLE_PREFIX. "ST_LINK ORDER BY ORDERING", OBJECT_K);
	//print_r($stlinks);
	
	$current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
	$noclan_url = remove_query_arg( 'clan', $current_url );
	?>
	<div class="wrap">
		<h2>Characters <a class="add-new-h2" href="<?php echo get_page_link($stlinks['editCharSheet']->WP_PAGE_ID) ; ?>">Add New</a></h2>

		<?php 
		
		if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'delete' && $_REQUEST['characterID'] != 0) {
		
			?>
			<p>Confirm deletion of character <?php echo vtm_formatOutput($_REQUEST['characterName']); ?></p>
			<div class="char_delete">
				
				<form id="character-delete" method="get" action='<?php print $current_url; ?>'>
				<input type="hidden" name="page" value="<?php print $_REQUEST['page'] ?>" />
				<input type="hidden" name="characterID" value="<?php print $_REQUEST['characterID'] ?>" />
				<input type='submit' name="cConfirm" value="Confirm Delete" />
				<input type='submit' name="cCancel" value="Cancel" />
				</form>
			
			</div>
		
		<?php
		} else {
		
			if (isset($_REQUEST['cConfirm'])) {
				echo vtm_deleteCharacter($_REQUEST['characterID']);
			} 

		?>
		
		<div class="char_clan_menu">
		<?php
			$arr = array('<a href="' . htmlentities($noclan_url) . '" class="nav_clan">All</a>');
			foreach (vtm_get_clans() as $clan) {
				$clanurl = add_query_arg('clan', $clan->ID);
				array_push($arr, '<a href="' . htmlentities($clanurl) . '" class="nav_clan">' . vtm_formatOutput($clan->NAME) . '</a>');
			}
			$text = implode(' | ', $arr);
			echo $text;
		?>
		</div>
		<div class="char_filters">
			
			<form id="character-filter" method="get" action='<?php print htmlentities($current_url); ?>'>
				<input type="hidden" name="page" value="<?php print $_REQUEST['page'] ?>" />
				<label>Player Status: </label>
				<select name='player_status'>
					<?php foreach( $options_player_status as $key => $value ) {
							echo '<option value="' . esc_attr( $key ) . '" ';
							selected( $active_player_status, $key );
							echo '>' . vtm_formatOutput( $value ) . '</option>';
						}
					?>
				</select>
				<label>Character Type: </label>
				<select name='character_type'>
					<?php foreach( $options_character_type as $key => $value ) {
							echo '<option value="' . esc_attr( $key ) . '" ';
							selected( $active_character_type, $key );
							echo '>' . vtm_formatOutput( $value ) . '</option>';
						}
					?>
				</select>
				<label>Character Status: </label>
				<select name='character_status'>
					<?php foreach( $options_character_status as $key => $value ) {
							echo '<option value="' . esc_attr( $key ) . '" ';
							selected( $active_character_status, $key );
							echo '>' . vtm_formatOutput( $value ) . '</option>';
						}
					?>
				</select>
				<label>Character Visibility: </label>
				<select name='character_visible'>
					<?php
					echo '<option value="all" ';
					selected( $active_character_visible, 'all' );
					echo '>All</option>';
					echo '<option value="Y" ';
					selected( $active_character_visible, 'y' );
					echo '>Yes</option>';
					echo '<option value="N" ';
					selected( $active_character_visible, 'n' );
					echo '>No</option>';
					?>
				</select>
				<label>Character Gen Status: </label>
				<select name='chargen_status'>
					<?php foreach( $options_chargen_status as $key => $value ) {
							echo '<option value="' . esc_attr( $key ) . '" ';
							selected( $active_chargen_status, $key );
							echo '>' . vtm_formatOutput( $value ) . '</option>';
						}
					?>
				</select>
				
				<?php submit_button( 'Filter', 'secondary', 'do_filter_character', false); ?>
			</form>
		
		</div>
		<div>
			<table class="wp-list-table widefat">
			<tr>
				<th>Character Name</th>
				<th>Actions</th>
				<th>Clan</th>
				<th>Player Name</th>
				<th>Player Status</th>
				<th>Character Type</th>
				<th>Character Status</th>
				<th>Character Visible</th>
			</tr>
			<?php
			// Character Name / Clan / Player Name / Player Status / Character Type / Character Status / Character Visible
			
			
			$sql = "SELECT
						chara.ID,
						chara.name as charactername,
						clans.name as clan,
						players.name as player,
						pstatus.name as player_status,
						ctypes.name as character_type,
						cstatus.name as character_status,
						chara.visible,
						chara.wordpress_id,
						cgstat.name as chargen_status, 
						tinfo.name as template
					FROM
						" . VTM_TABLE_PREFIX. "CHARACTER chara
						LEFT JOIN (
							SELECT cgt.name, cg.CHARACTER_ID
							FROM
								" . VTM_TABLE_PREFIX. "CHARACTER_GENERATION cg,
								" . VTM_TABLE_PREFIX. "CHARGEN_TEMPLATE cgt
							WHERE
								cg.TEMPLATE_ID = cgt.ID
						) as tinfo
						ON
							tinfo.CHARACTER_ID = chara.ID,
						" . VTM_TABLE_PREFIX. "CLAN clans,
						" . VTM_TABLE_PREFIX. "PLAYER players,
						" . VTM_TABLE_PREFIX. "PLAYER_STATUS pstatus,
						" . VTM_TABLE_PREFIX. "CHARACTER_TYPE ctypes,
						" . VTM_TABLE_PREFIX. "CHARACTER_STATUS cstatus,
						" . VTM_TABLE_PREFIX. "CHARGEN_STATUS cgstat
					WHERE
						clans.ID = chara.PRIVATE_CLAN_ID
						AND players.ID = chara.PLAYER_ID
						AND pstatus.ID = players.PLAYER_STATUS_ID
						AND ctypes.ID = chara.CHARACTER_TYPE_ID
						AND cstatus.ID = chara.CHARACTER_STATUS_ID
						AND cgstat.ID  = chara.CHARGEN_STATUS_ID
						AND chara.DELETED != 'Y'";
						
			$args = array();
					
			if ( "all" !== $active_player_status) {
				$sql .= " AND players.PLAYER_STATUS_ID = %s";
				array_push($args, $active_player_status);
			}
			if ( "all" !== $active_character_type) {
				$sql .= " AND chara.CHARACTER_TYPE_ID = %s";
				array_push($args, $active_character_type);
			}
			if ( "all" !== $active_character_status) {
				$sql .= " AND chara.CHARACTER_STATUS_ID = %s";
				array_push($args, $active_character_status);
			}
			if ( "all" !== $active_character_visible) {
				$sql .= " AND chara.VISIBLE = %s";
				array_push($args, $active_character_visible);
			}
			if ( "all" !== $active_chargen_status) {
				$sql .= " AND chara.CHARGEN_STATUS_ID = %s";
				array_push($args, $active_chargen_status);
			}
			if ( isset($_REQUEST['clan']) ) {
				$sql .= " AND clans.ID = %s";
				array_push($args, $_REQUEST['clan']);
			}
						
			$sql .= " 	ORDER BY charactername, visible, character_type, character_status";
			if (count($args) > 0)
				$sql = $wpdb->prepare($sql,$args);
			$result = $wpdb->get_results($sql);
			//echo "<p>SQL: $sql</p>";
			//print_r($result);
		
			$i = 0;
			foreach ($result as $character) {
				$name = vtm_formatOutput($character->charactername);
			
				echo "<tr";
				if ($i % 2) echo " class='alternate'";
				echo ">\n";
				echo "<th>";
				
				if ($character->chargen_status != 'Approved')
					echo $name . " [" . vtm_formatOutput($character->template) . "]";
				elseif (!empty($character->wordpress_id))
					echo '<a href="' . get_page_link($stlinks['viewCharSheet']->WP_PAGE_ID) . '?CHARACTER='. urlencode($character->wordpress_id) . '">' . $name . '</a>';
				else
					echo '<a href="' . get_page_link($stlinks['viewCharSheet']->WP_PAGE_ID) . '?characterID='. urlencode($character->ID) . '">' . $name . '</a>';
				
				echo "</th><td>";
				echo '<div>';
				if ($character->chargen_status == 'Approved')
					echo '&nbsp;<a href="' . get_page_link($stlinks['editCharSheet']->WP_PAGE_ID) . '?characterID=' . urlencode($character->ID) . '"><img src="' . $iconurl . 'edit.png" alt="Edit" title="Edit Character" /></a>';
				else
					echo '&nbsp;<a href="' . get_page_link($stlinks['viewCharGen']->WP_PAGE_ID) . '?characterID=' . urlencode($character->ID) . '"><img src="' . $iconurl . 'edit.png" alt="Edit" title="Edit Character" /></a>';

				$delete_url = add_query_arg('action', 'delete', $current_url);
				$delete_url = add_query_arg('characterID', $character->ID, $delete_url);
				$delete_url = add_query_arg('characterName', urlencode($character->wordpress_id), $delete_url);
				echo '&nbsp;<a href="' . htmlentities($delete_url) . '"><img src="' . $iconurl . 'delete.png" alt="Delete" title="Delete Character" /></a>';
				echo '&nbsp;<a href="' . get_page_link($stlinks['printCharSheet']->WP_PAGE_ID)  . '?characterID=' . urlencode($character->ID) . '"><img src="' . $iconurl . 'print.png" alt="Print" title="Print Character" /></a>';
				
				if (!empty($character->wordpress_id) && $character->chargen_status == 'Approved') {
					echo '&nbsp;<a href="' . get_page_link($stlinks['viewProfile']->WP_PAGE_ID)     . '?CHARACTER='. urlencode($character->wordpress_id) . '"><img src="' . $iconurl . 'profile.png" alt="Profile" title="View Profile" /></a>';
					echo '&nbsp;<a href="' . get_page_link($stlinks['viewXPSpend']->WP_PAGE_ID)     . '?CHARACTER='. urlencode($character->wordpress_id) . '"><img src="' . $iconurl . 'spendxp.png" alt="XP Spend" title="Spend Experience" /></a>';
					echo '&nbsp;<a href="' . get_page_link($stlinks['viewExtBackgrnd']->WP_PAGE_ID) . '?CHARACTER='. urlencode($character->wordpress_id) . '"><img src="' . $iconurl . 'background.png" alt="Background" title="Extended Background" /></a>';
					echo '&nbsp;<a href="' . get_page_link($stlinks['viewCustom']->WP_PAGE_ID)      . '?CHARACTER='. urlencode($character->wordpress_id) . '"><img src="' . $iconurl . 'custom.png" alt="Custom" title="View Custom Page as Character" /></a>';
				}
				echo "</div></td>";
				echo "<td>" . vtm_formatOutput($character->clan) . "</td>";
				echo "<td>" . vtm_formatOutput($character->player) . "</td>";
				echo "<td>" . vtm_formatOutput($character->player_status) . "</td>";
				echo "<td>" . vtm_formatOutput($character->character_type) . "</td>";
				echo "<td>" . vtm_formatOutput($character->character_status) . "</td>";
				echo "<td>{$character->visible}</td>";
				echo "</tr>\n";
				$i++;
			}
		
			?>
			</table>
		</div>
		
		<?php } ?>
	</div>
	<?php
}

/* CREATE/EDIT CHARACTER PAGE
-------------------------------------------------------------- */

function vtm_edit_character_content_filter($content) {

  if (is_page(vtm_get_stlink_page('editCharSheet')))
		if (is_user_logged_in()) {
			$content .= vtm_get_edit_character_content();
		} else {
			$content .= "<p>You must be logged in to view this content.</p>";
		}
  // otherwise returns the database content
  return $content;
}

add_filter( 'the_content', 'vtm_edit_character_content_filter' );


function vtm_get_edit_character_content() {

	$output = "";
	$submitted = 0;

	if (isset($_REQUEST['characterID']))
		$characterID = $_REQUEST['characterID'];
	else
		$characterID = 0;

	if (isset($_REQUEST['cSubmit']) && $_REQUEST['cSubmit'] == "Submit character changes") {
		$characterID = vtm_processCharacterUpdate($characterID);
		$submitted = 1;
	}
	$output .= vtm_displayUpdateCharacter($characterID, $submitted);
	
	return $output;
}


function vtm_displayUpdateCharacter($characterID, $submitted) {
	global $wpdb;
	global $vtmglobal;
	$table_prefix = VTM_TABLE_PREFIX;
	$output = "";

	if ($characterID == "0" || (int) ($characterID) > 0) {
		$players           = vtm_listPlayers("Active", "");       // ID, name
		$clans             = vtm_listClans();               // ID, name
		$generations       = vtm_listGenerations();         // ID, name
		$domains           = vtm_listDomains();             // ID, name
		$sects             = vtm_get_Sects();               // ID, name
		$characterTypes    = vtm_listCharacterTypes();      // ID, name
		$characterStatuses = vtm_listCharacterStatuses();   // ID, name
		$roadsOrPaths      = vtm_listRoadsOrPaths();        // ID, name

		$vtmglobal['config'] = vtm_getConfig();
		
		if ($submitted) {
			$characterName             = $_POST['charName'];
			$characterPublicClanId     = $_POST['charPubClan'];
			$characterPrivateClanId    = $_POST['charPrivClan'];
			$characterGenerationId     = $_POST['charGen'];
			$characterDateOfBirth      = $_POST['charDoB'];
			$characterDateOfEmbrace    = $_POST['charDoE'];
			$characterSire             = $_POST['charSire'];
			$characterPlayerId         = $_POST['charPlayer'];
			$characterTypeId           = $_POST['charType'];
			$characterStatusId         = $_POST['charStatus'];
			$characterStatusComment    = $_POST['charStatusComment'];
			$characterRoadOrPathId     = $_POST['charRoadOrPath'];
			$characterRoadOrPathRating = $_POST['charRoadOrPathRating'];
			$characterDomainId         = $_POST['charDomain'];
			$characterSectId           = $_POST['charSect'];
			$characterWordpressName    = $_POST['charWordPress'];
			$characterVisible          = $_POST['charVisible'];
			$characterNatureId         = $_POST['charNature'];
			$characterDemeanourId      = $_POST['charDemeanour'];

			$characterHarpyQuote       = $_POST['charHarpyQuote'];
			$characterPortraitURL      = $_POST['charPortraitURL'];
		} else {
			$characterName             = "New Name";
			$characterPublicClanId     = "";
			$characterPrivateClanId    = "";
			$characterGenerationId     = $vtmglobal['config']->DEFAULT_GENERATION_ID;
			$characterDateOfBirth      = "";
			$characterDateOfEmbrace    = "";
			$characterSire             = "";
			$characterPlayerId         = "";
			$characterTypeId           = "";
			$characterStatusId         = "";
			$characterStatusComment    = "";
			$characterRoadOrPathId     = "";
			$characterRoadOrPathRating = "";
			$characterDomainId         = $vtmglobal['config']->HOME_DOMAIN_ID;
			$characterSectId           = $vtmglobal['config']->HOME_SECT_ID;
			$characterWordpressName    = "";
			$characterVisible          = "Y";
			$characterNatureId         = "";
			$characterDemeanourId      = "";

			$characterHarpyQuote       = "";
			$characterPortraitURL      = "";
		}

		if ((int) ($characterID) > 0) {

			$sql = "SELECT NAME,
								   PUBLIC_CLAN_ID,
								   PRIVATE_CLAN_ID,
								   GENERATION_ID,
								   DATE_OF_BIRTH,
								   DATE_OF_EMBRACE,
								   SIRE,
								   PLAYER_ID,
								   CHARACTER_TYPE_ID,
								   CHARACTER_STATUS_ID,
								   CHARACTER_STATUS_COMMENT,
								   ROAD_OR_PATH_ID,
								   ROAD_OR_PATH_RATING,
								   DOMAIN_ID,
								   SECT_ID,
								   WORDPRESS_ID,
								   CHARGEN_STATUS_ID,
								   VISIBLE
							FROM " . $table_prefix . "CHARACTER
							WHERE ID = %d";

			$characterDetails = $wpdb->get_results($wpdb->prepare($sql, $characterID));

			foreach ($characterDetails as $characterDetail) {
				$characterName             = vtm_formatOutput($characterDetail->NAME);
				$characterPublicClanId     = $characterDetail->PUBLIC_CLAN_ID;
				$characterPrivateClanId    = $characterDetail->PRIVATE_CLAN_ID;
				$characterGenerationId     = $characterDetail->GENERATION_ID;
				$characterDateOfBirth      = $characterDetail->DATE_OF_BIRTH;
				$characterDateOfEmbrace    = $characterDetail->DATE_OF_EMBRACE;
				$characterSire             = vtm_formatOutput($characterDetail->SIRE);
				$characterPlayerId         = $characterDetail->PLAYER_ID;
				$characterTypeId           = $characterDetail->CHARACTER_TYPE_ID;
				$characterStatusId         = $characterDetail->CHARACTER_STATUS_ID;
				$characterStatusComment    = vtm_formatOutput($characterDetail->CHARACTER_STATUS_COMMENT);
				$characterRoadOrPathId     = $characterDetail->ROAD_OR_PATH_ID;
				$characterRoadOrPathRating = $characterDetail->ROAD_OR_PATH_RATING;
				$characterDomainId         = $characterDetail->DOMAIN_ID;
				$characterSectId           = $characterDetail->SECT_ID;
				$characterWordpressName    = $characterDetail->WORDPRESS_ID;
				$characterVisible          = $characterDetail->VISIBLE;
				$chargenStatus             = $characterDetail->CHARGEN_STATUS_ID;
			}
			
			$cgstatus = $wpdb->get_var($wpdb->prepare("SELECT NAME FROM " . $table_prefix . "CHARGEN_STATUS WHERE ID = %s", $chargenStatus));
			if ($cgstatus != 'Approved') {
				return 'Characters cannot be edited while in the middle of character generation';
			}

			$sql = "SELECT QUOTE, PORTRAIT
							FROM " . $table_prefix . "CHARACTER_PROFILE
							WHERE CHARACTER_ID = %d";

			$characterProfiles = $wpdb->get_results($wpdb->prepare($sql, $characterID));

			foreach ($characterProfiles as $characterProfile) {
				$characterHarpyQuote  = vtm_formatOutput($characterProfile->QUOTE);
				$characterPortraitURL = $characterProfile->PORTRAIT;
			}
			
			if ($vtmglobal['config']->USE_NATURE_DEMEANOUR == 'Y') {
				$sql = "SELECT
							NATURE_ID,
							DEMEANOUR_ID
						FROM " . $table_prefix . "CHARACTER
						WHERE ID = %d";
				$characterND = $wpdb->get_row($wpdb->prepare($sql, $characterID));
				
				$characterNatureId    = $characterND->NATURE_ID;
				$characterDemeanourId = $characterND->DEMEANOUR_ID;
			
			}
		}

		$jumpto = "<span><a href='#gvid_ucti'>top</a> | 
			<a href='#gvid_uctsto'>attributes</a> |
			<a href='#gvid_uctskg'>abilities</a> |
			<a href='#gvid_uctskn'>new abilities</a> |
			<a href='#gvid_uctdi'>disciplines</a> |
			<a href='#gvid_uctba'>backgrounds</a> |
			<a href='#gvid_uctme'>merits</a> |
			<a href='#gvid_uctcd'>combo disciplines</a> |
			<a href='#gvid_uctpa'>paths</a> |
			<a href='#gvid_uctri'>rituals</a> |
			<a href='#gvid_uctof'>offices</a>
		</span>";
		
		$output  = "<div class='gvplugin' id='vtmeditsheet'><form name='CHARACTER_UPDATE_FORM' method='post' action='" . $_SERVER['REQUEST_URI'] . "'>
					<input type='HIDDEN' name='VTM_FORM' value='displayUpdateCharacter' />
					<table id='gvid_ucti'>
					<tr><td><input type='submit' name='cSubmit' value='Submit character changes' /></td></tr></table>";
		if ((int) ($characterID) > 0) { $output .= "<input type='HIDDEN' name='characterID' value='" . $characterID . "' />"; }
		
		
		$output .= $jumpto;
		$output .= "<table id='gvid_uctu'>\n";
		
		$output .= "<tr><td>Character Name*</td><td><input type='text' maxlength=60 name='charName' value='" . $characterName . "'></td></tr>\n";
		$output .= "<tr><td>Player Name</td><td><select name='charPlayer'>\n";
		foreach ($players as $player) {
			$output .= "<option value='" . $player->ID . "' ";
			if ($player->ID == $characterPlayerId) {
				$output .= "SELECTED";
			}
			$output .= ">" . vtm_formatOutput($player->name) . "</option>";
		}
		$output .= "</select></td></tr>";
		$output .= "<tr><td>WordPress Account</td>
			<td><input type='text' maxlength=30 name='charWordPress' value='" . $characterWordpressName . "' /></td></tr>";
		$output .= "<tr><td>Public Clan</td><td><select name='charPubClan'>";
		foreach ($clans as $clan) {
			$output .= "<option value='" . $clan->ID . "' ";
			if ($clan->ID == $characterPublicClanId) {
				$output .= "SELECTED";
			}
			$output .= ">" . vtm_formatOutput($clan->name) . "</option>";
		}
		$output .= "</select></td></tr>\n";	
		$output .= "<tr><td>Private Clan</td><td><select name='charPrivClan'>";
		foreach ($clans as $clan) {
			$output .= "<option value='" . $clan->ID . "' ";
			if ($clan->ID == $characterPrivateClanId) {
				$output .= "SELECTED";
			}
			$output .= ">" . vtm_formatOutput($clan->name) . "</option>";
		}
		$output .= "</select></td></tr>\n";
		$output .= "<tr><td>Generation</td><td><select name='charGen'>";
		foreach ($generations as $generation) {
			$output .= "<option value='" . $generation->ID . "' ";
			if ($generation->ID == $characterGenerationId) {
				$output .= "SELECTED";
			}
			$output .= ">" . vtm_formatOutput($generation->name) . "th</option>";
		}
		$output .= "</select></td></tr>\n";	
		$output .= "<tr><td>Character Type</td><td><select name='charType'>";
		foreach ($characterTypes as $characterType) {
			$output .= "<option value='" . $characterType->ID . "' ";
			if ($characterType->ID == $characterTypeId || ($characterID == 0 && $characterType->name == 'PC')) {
				$output .= "SELECTED";
			}
			$output .= ">" . vtm_formatOutput($characterType->name) . "</option>";
		}
		$output .= "</select></td></tr>\n";	
		$output .= "<tr><td>Character Status</td><td><select name='charStatus'>";
		foreach ($characterStatuses as $characterStatus) {
			$output .= "<option value='" . $characterStatus->ID . "' ";
			if ($characterStatus->ID == $characterStatusId) {
				$output .= "SELECTED";
			}
			$output .= ">" . vtm_formatOutput($characterStatus->name) . "</option>";
		}
		$output .= "</select></td></tr>\n";
		$output .= "<tr><td>Character Status Comment</td><td><input type='text' maxlength=30 name='charStatusComment' value='" . $characterStatusComment . "' /></td></tr>\n";
		$output .= "<tr><td>Visible</td><td><select name='charVisible'><option value='Y' ";
		if ($characterVisible == "Y" ) $output .= "SELECTED";
		$output .= ">Yes</option><option value='N' ";
		if ($characterVisible != "Y") $output .= "SELECTED";
		$output .= ">No</option></select></td></tr>";
		
		$output .= "<tr><td>Road or Path*</td><td><select name='charRoadOrPath'>";
		foreach ($roadsOrPaths as $roadOrPath) {
			$output .= "<option value='" . $roadOrPath->ID . "' ";
			if ($roadOrPath->ID == $characterRoadOrPathId || ($characterID == 0 && $roadOrPath->name == 'Humanity')) {
				$output .= "SELECTED";
			}
			$output .= ">" . vtm_formatOutput($roadOrPath->name) . "</option>";
		}
		$output .= "</select>";
		$output .= "</td></tr>\n";
		
		$output .= "<tr><td>Road or Path Rating</td><td>";
		$sql = "SELECT SUM(AMOUNT) FROM " . VTM_TABLE_PREFIX . "CHARACTER_ROAD_OR_PATH WHERE CHARACTER_ID = %d";
		$sql = $wpdb->prepare($sql, $characterID);
		$result = $wpdb->get_var($sql);
		if ($result > 0) {
			$sql = "SELECT NAME FROM " . VTM_TABLE_PREFIX . "ROAD_OR_PATH WHERE ID = %s";
			$pathname = $wpdb->get_var($wpdb->prepare($sql, $characterRoadOrPathId));
			$output .= "<input type='hidden' name='charRoadOrPathRating' value='" . $result . "' />";
			$output .= "<span>$result</span>";
		} else {
			$output .= "<input type='text' maxlength=3 name='charRoadOrPathRating' value='" . $characterRoadOrPathRating . "' />";
		}
		
		$output .= "</td></tr>\n";		
		if ($vtmglobal['config']->USE_NATURE_DEMEANOUR == 'Y') {
			$output .= "<tr><td>Nature</td><td>";
			$output .= "<select name = 'charNature'>";
			$output .= "<option value='0'>[Select]</option>";
			foreach (vtm_get_natures() as $nature) {
				$output .= "<option value='" . $nature->ID . "' ";
				if ($nature->ID == $characterNatureId) {
					$output .= "SELECTED";
				}
				$output .= ">" . vtm_formatOutput($nature->NAME) . "</option>";
			}
			$output .= "</select></td></tr><tr><td>Demeanour</td><td>";
			$output .= "<select name = 'charDemeanour'>";
			$output .= "<option value='0'>[Select]</option>";
			foreach (vtm_get_natures() as $nature) {
				$output .= "<option value='" . $nature->ID . "' ";
				if ($nature->ID == $characterDemeanourId) {
					$output .= "SELECTED";
				}
				$output .= ">" . vtm_formatOutput($nature->NAME) . "</option>";
			}
			$output .= "</select></td></tr>\n";
		}
		$output .= "<tr><td>Sect</td><td>";
		$output .= "<select name = 'charSect'>";
		foreach ($sects as $sect) {
			$output .= "<option value='" . $sect->ID . "' ";
			if ($sect->ID == $characterSectId || ($characterID == 0 && $sect->NAME == 'Camarilla')) {
				$output .= "SELECTED";
			}
			$output .= ">" . vtm_formatOutput($sect->NAME) . "</option>";
		}
		$output .= "</select></td></tr>";		
		$output .= "<tr><td>Domain</td><td><select name='charDomain'>";
		foreach ($domains as $domain) {
			$output .= "<option value='" . $domain->ID . "' ";
			if ($domain->ID == $characterDomainId) {
				$output .= "SELECTED";
			}
			$output .= ">" . vtm_formatOutput($domain->name) . "</option>";
		}
		$output .= "</select></td></tr>";
		$output .= "<tr><td>Sire</td><td><input type='text' maxlength=60 name='charSire' value='" . $characterSire . "' /></td></tr>";
		$output .= "<tr><td>Date of birth</td>
						<td><input type='text' maxlength=10 name='charDoB' value='" . $characterDateOfBirth . "' /> YYYY-MM-DD</td></tr>
					<tr><td>Date of Embrace</td>
						<td><input type='text' maxlength=10 name='charDoE' value='" . $characterDateOfEmbrace . "' /> YYYY-MM-DD</td></tr>";
		$output .= "<tr><td>Portrait URL</td><td><input type='text' maxlength=250 size=50 name='charPortraitURL' value='" . $characterPortraitURL . "' /></td></tr>";
		$output .= "<tr><td>Harpy Quote</td><td><textarea name='charHarpyQuote' rows='5' cols='50'>" . $characterHarpyQuote . "</textarea></td></tr>";
		$output .= "</table>";


		// Initialise Stat information for new characters
		$sql = "SELECT name, grouping, id FROM " . $table_prefix . "STAT";
		$result =  $wpdb->get_results($sql);
		$arr = array();
		foreach ($result as $statinfo) {
			$arr[$statinfo->name] = $statinfo;
			$arr[$statinfo->name]->level   = 0;
			$arr[$statinfo->name]->comment = '';
			$arr[$statinfo->name]->cstatid = 0;
		}
		
		$sql = "SELECT stat.name,
							   stat.grouping,
							   stat.id statid,
							   cstat.level,
							   cstat.comment,
							   cstat.id cstatid
						FROM " . $table_prefix . "CHARACTER_STAT cstat,
							 " . $table_prefix . "STAT stat
						WHERE cstat.stat_id = stat.id
						  AND character_id = %d
						ORDER BY stat.ordering";

		$characterStats = $wpdb->get_results($wpdb->prepare($sql, $characterID));

		foreach ($characterStats as $characterStat) {
			$arr[$characterStat->name] = $characterStat;
		}
		$stats = vtm_listStats();

		$head = "<tr><th>Name</th><th>Value</th><th>Comment</th><th>Delete</th></tr>";

		$output .= "<hr />$jumpto<table id='gvid_uctsto'>";
		$lastgroup = "";
		$thisgroup = "";
		$col = 0;
		foreach ($stats as $stat) {
			$thisgroup = $stat->grouping;
			
			if ($thisgroup != $lastgroup) {
				$output .= "<tr><td colspan=4><h4>" . vtm_formatOutput($thisgroup) . "</h4></td></tr>$head";
			}

			$statName = $stat->name;
			$currentStat = $arr[$statName];
			$output .= "<tr><td>" . vtm_formatOutput($stat->name);
			switch($stat->name) {
				case 'Willpower': $output .= "*"; break;
			}
			
			$output .= "</td>"
				. "<td>" . vtm_printSelectCounter($statName, $currentStat->level, 1, 10) . "</td>"
				. "<td><input type='text' name='" . $statName . "Comment' value='" . vtm_formatOutput($currentStat->comment) . "' /></td>"
				. "<td>";

			if ($currentStat->grouping == "Virtue"  && $statName != "Courage") {
				$output .= "<input type='checkbox' name='" . $statName . "Delete' value='" . $currentStat->cstatid . "' />";
			}

			$output .= "<input type='HIDDEN' name='" . $statName . "ID' value='" . $currentStat->cstatid . "' />"
				. "</td></tr>";
			$lastgroup = $thisgroup;
		}
		$output .= "</table>";

		$sql = "SELECT skill.name,
							   skilltype.name as grouping,
							   skill.id skillid,
							   cskill.level,
							   cskill.comment,
							   cskill.id cskillid
						FROM " . $table_prefix . "CHARACTER_SKILL cskill,
							 " . $table_prefix . "SKILL skill,
							 " . $table_prefix . "SKILL_TYPE skilltype
						WHERE cskill.skill_id = skill.id
						  AND character_id = %d
						  AND skilltype.ID = skill.skill_type_id
						ORDER BY skilltype.ordering, skill.name";

		$characterSkills = $wpdb->get_results($wpdb->prepare($sql, $characterID));

		$lastgroup = "";
		$thisgroup = "";
		$output .= "<hr />$jumpto<table id='gvid_uctskg'>";

		$skillCount = 0;
		$arr = array();
		foreach($characterSkills as $characterSkill) {
			$thisgroup = $characterSkill->grouping;
			if ($thisgroup != $lastgroup) {
				$output .= "<tr><td colspan=4><h4>" . vtm_formatOutput($thisgroup) . "</h4></td></tr>$head";
			}

			$skillName = "skill" . $skillCount;
			$output .= "<tr><td>" . vtm_formatOutput($characterSkill->name) . "</td>"
				. "<td>" . vtm_printSelectCounter($skillName, $characterSkill->level, 1, 10) . "</td>"
				. "<td><input type='text' name='"     . $skillName . "Comment' value='" . vtm_formatOutput($characterSkill->comment) . "' /></td>"
				. "<td><input type='checkbox' name='" . $skillName . "Delete' value='"  . $characterSkill->cskillid . "' />"
				.     "<input type='HIDDEN' name='"   . $skillName . "ID' value='"      . $characterSkill->cskillid . "' /></td></tr>";

			$skillCount++;
			$lastgroup = $thisgroup;
		}
		$output .= "</table>\n";
		$output .= "<input type='HIDDEN' name='maxOldSkillCount' value='" . $skillCount . "' />";

		$output .= "<table id='gvid_uctskn'><tr><td colspan=4><h4>New Abilities</h4></td></tr>$head";

		$skillBlock = "";
		$skills = vtm_listSkills("", "Y");
		foreach ($skills as $skill) {
			$skillBlock .= "<option value='" . $skill->id . "'>" . vtm_formatOutput($skill->name) . "</option>";
		}

		for ($i = 0; $i < 20; ) {
			$skillName = "skill" . $skillCount;
			$output .= "<tr><td><select name='" . $skillName . "SID'>" . $skillBlock . "</select></td>"
				. "<td>" . vtm_printSelectCounter($skillName, "", 1, 10) . "</td>"
				. "<td><input type='text' name='" . $skillName . "Comment' /></td>"
				. "<td></td></tr>";

			$i++;
			$skillCount++;
		}
		$output .= "</table><input type='HIDDEN' name='maxNewSkillCount' value='" . $skillCount . "' /><hr />$jumpto";

		/*******************************************************************************************/
		/*******************************************************************************************/

		$sql = "SELECT discipline.name,
							   discipline.id disid,
							   cdiscipline.level,
							   cdiscipline.comment,
							   cdiscipline.id cdisciplineid
						FROM " . $table_prefix . "CHARACTER_DISCIPLINE cdiscipline,
							 " . $table_prefix . "DISCIPLINE discipline
						WHERE cdiscipline.discipline_id = discipline.id
						  AND character_id = %d
						ORDER BY discipline.name";

		$characterDisciplines = $wpdb->get_results($wpdb->prepare($sql, $characterID));

		$output .= "<table id='gvid_uctdi'>$head";
		$colOffset = 0;
		$i = 0;
		$disciplineCount = 0;
		$arr = array();
		foreach($characterDisciplines as $characterDiscipline) {
			$output .= "<tr>";

			$disciplineName = "discipline" . $disciplineCount;
			$output .= "<td class='vtmcol_key'>" . vtm_formatOutput($characterDiscipline->name) . "</td>"
				. "<td>" . vtm_printSelectCounter($disciplineName, $characterDiscipline->level, 1, 10) . "</td>"
				. "<td><input type='text' name='"     . $disciplineName . "Comment' value='" . vtm_formatOutput($characterDiscipline->comment)  . "' /></td>"
				. "<td><input type='checkbox' name='" . $disciplineName . "Delete' value='"  . $characterDiscipline->cdisciplineid . "' />"
				.     "<input type='HIDDEN' name='"   . $disciplineName . "ID' value='"      . $characterDiscipline->cdisciplineid . "' /></td>";

			$i++;
			$disciplineCount++;
			$output .= "</tr>";
		}

		$output .= "<tr style='display:none'><td colspan=4><input type='HIDDEN' name='maxOldDisciplineCount' value='" . $disciplineCount . "' /></td></tr>";

		$disciplineBlock = "";
		$disciplines = vtm_listDisciplines("Y");
		foreach ($disciplines as $discipline) {
			$disciplineBlock .= "<option value='" . $discipline->id . "'>" . vtm_formatOutput($discipline->name) . "</option>";
		}

		for ($i = 0; $i < 4; ) {
			$output .= "<tr>";
			$disciplineName = "discipline" . $disciplineCount;
			$output .= "<td><select name='" . $disciplineName . "SID'>" . $disciplineBlock . "</select></td>"
				. "<td>" . vtm_printSelectCounter($disciplineName, "", 1, 10) . "</td>"
				. "<td><input type='text' name='"     . $disciplineName . "Comment' /></td>"
				. "<td></td>";

			$i++;
			$disciplineCount++;
			$output .= "</tr>";
		}
		$output .= "<tr style='display:none'><td colspan=4><input type='HIDDEN' name='maxNewDisciplineCount' value='" . $disciplineCount . "' /></td></tr>";
		$output .= "</table><hr />$jumpto";

		/*******************************************************************************************/
		/*******************************************************************************************/

		$sql = "SELECT background.name,
							   background.grouping,
							   background.id statid,
							   cbackground.level,
							   cbackground.comment,
							   cbackground.id cbackgroundid
						FROM " . $table_prefix . "CHARACTER_BACKGROUND cbackground,
							 " . $table_prefix . "BACKGROUND background
						WHERE cbackground.background_id = background.id
						  AND character_id = %d
						ORDER BY background.name";

		$characterBackgrounds = $wpdb->get_results($wpdb->prepare($sql, $characterID));

		$output .= "<table id='gvid_uctba'>$head";
		$i = 0;
		$backgroundCount = 0;
		$arr = array();
		foreach($characterBackgrounds as $characterBackground) {
			$output .= "<tr>";

			$backgroundName = "background" . $backgroundCount;
			$output .= "<td>" . vtm_formatOutput($characterBackground->name) . "</td>"
				. "<td>" . vtm_printSelectCounter($backgroundName, $characterBackground->level, 1, 10) . "</td>"
				. "<td><input type='text' name='"     . $backgroundName . "Comment' value='" . vtm_formatOutput($characterBackground->comment)  . "' /></td>"
				. "<td><input type='checkbox' name='" . $backgroundName . "Delete' value='"  . $characterBackground->cbackgroundid . "' />"
				.     "<input type='HIDDEN' name='"   . $backgroundName . "ID' value='"      . $characterBackground->cbackgroundid . "' /></td>";

			$i++;
			$backgroundCount++;
			$output .= "</tr>";
		}

		$output .= "<tr style='display:none'><td colspan=4><input type='HIDDEN' name='maxOldBackgroundCount' value='" . $backgroundCount . "' /></td></tr>";

		$backgroundBlock = "";
		$backgrounds = vtm_listBackgrounds("", "Y");
		foreach ($backgrounds as $background) {
			$backgroundBlock .= "<option value='" . $background->id . "'>" . vtm_formatOutput($background->name) . "</option>";
		}

		for ($i = 0; $i < 6; ) {
			$output .= "<tr>";
			$backgroundName = "background" . $backgroundCount;
			$output .= "<td><select name='" . $backgroundName . "SID'>" . $backgroundBlock . "</select></td>"
				. "<td>" . vtm_printSelectCounter($backgroundName, "", 1, 10) . "</td>"
				. "<td><input type='text' name='"     . $backgroundName . "Comment' /></td>"
				. "<td></td>";

			$i++;
			$backgroundCount++;
			$output .= "</tr>";
		}
		$output .= "<tr style='display:none'><td colspan=4><input type='HIDDEN' name='maxNewBackgroundCount' value='" . $backgroundCount . "' /></td></tr>";
		$output .= "</table><hr />$jumpto";

		/*******************************************************************************************/

		$sql = "SELECT merit.name,
							   merit.grouping,
							   merit.id statid,
							   merit.value,
							   cmerit.level,
							   cmerit.comment,
							   cmerit.id cmeritid
						FROM " . $table_prefix . "CHARACTER_MERIT cmerit,
							 " . $table_prefix . "MERIT merit
						WHERE cmerit.merit_id = merit.id
						  AND character_id = %d
						ORDER BY merit.name";

		$characterMerits = $wpdb->get_results($wpdb->prepare($sql, $characterID));

		$output .= "<table id='gvid_uctme'>$head";
		$meritCount = 0;
		$arr = array();
		foreach($characterMerits as $characterMerit) {
			$meritName = "merit" . $meritCount;
			$output .= "<tr><td>" . vtm_formatOutput($characterMerit->name) . " (" . $characterMerit->value . ")</td>"
				. "<td>" . vtm_printSelectCounter($meritName, $characterMerit->level, -7, 7) . "</td>"
				. "<td><input type='text' name='"     . $meritName . "Comment' value='" . vtm_formatOutput($characterMerit->comment)  . "' /></td>"
				. "<td><input type='checkbox' name='" . $meritName . "Delete' value='"  . $characterMerit->cmeritid . "' />"
				.     "<input type='HIDDEN' name='"   . $meritName . "ID' value='"      . $characterMerit->cmeritid . "' /></td></tr>";

			$i++;
			$meritCount++;
		}

		$output .= "<tr style='display:none'><td colspan=4><input type='HIDDEN' name='maxOldMeritCount' value='" . $meritCount . "' /></td></tr>";

		$meritBlock = "";
		$merits = vtm_listMerits("", "Y");
		foreach ($merits as $merit) {
			$meritBlock .= "<option value='" . $merit->id . "'>" . vtm_formatOutput($merit->name) . " (" . $merit->value . ")</option>";
		}

		for ($i = 0; $i < 6; $i++) {
			$meritName = "merit" . $meritCount;
			$output .= "<tr><td><select name='" . $meritName . "SID'>" . $meritBlock . "</select></td>"
				. "<td>" . vtm_printSelectCounter($meritName, "", -7, 7) . "</td>"
				. "<td><input type='text' name='"     . $meritName . "Comment' /></td>"
				. "<td></td></tr>";

			$meritCount++;
		}
		$output .= "<tr style='display:none'><td colspan=4><input type='HIDDEN' name='maxNewMeritCount' value='" . $meritCount . "' /></td></tr>";
		$output .= "</table><hr />$jumpto";

		/*******************************************************************************************/


		$sql = "SELECT combo_discipline.name,
							   combo_discipline.id disid,
							   ccombo_discipline.comment,
							   ccombo_discipline.id ccombo_disciplineid
						FROM " . $table_prefix . "CHARACTER_COMBO_DISCIPLINE ccombo_discipline,
							 " . $table_prefix . "COMBO_DISCIPLINE combo_discipline
						WHERE ccombo_discipline.combo_discipline_id = combo_discipline.id
						  AND character_id = %d
						ORDER BY combo_discipline.name";

		$characterComboDisciplines = $wpdb->get_results($wpdb->prepare($sql, $characterID));

		$output .= "<table id='gvid_uctcd'>$head";

		$comboDisciplineCount = 0;
		$arr = array();
		foreach($characterComboDisciplines as $characterComboDiscipline) {
			$comboDisciplineName = "comboDiscipline" . $comboDisciplineCount;
			$output .= "<tr><td>" . vtm_formatOutput($characterComboDiscipline->name) . "</td>"
				. "<td>Learned<input type='HIDDEN' name='" . $comboDisciplineName . "' value='0' /></td>"
				. "<td><input type='text' name='"     . $comboDisciplineName . "Comment' value='" . vtm_formatOutput($characterComboDiscipline->comment)  . "' /></td>"
				. "<td><input type='checkbox' name='" . $comboDisciplineName . "Delete' value='"  . $characterComboDiscipline->ccombo_disciplineid . "' />"
				.     "<input type='HIDDEN' name='"   . $comboDisciplineName . "ID' value='"      . $characterComboDiscipline->ccombo_disciplineid . "' /></td></tr>";

			$comboDisciplineCount++;
		}
		$output .= "<tr style='display:none'><td colspan=4><input type='HIDDEN' name='maxOldComboDisciplineCount' value='" . $comboDisciplineCount . "' /></td></tr>";

		$comboDisciplineBlock = "";
		$comboDisciplines = vtm_listComboDisciplines("Y");
		foreach ($comboDisciplines as $comboDiscipline) {
			$comboDisciplineBlock .= "<option value='" . $comboDiscipline->id . "'>" .vtm_formatOutput( $comboDiscipline->name) . "</option>";
		}

		$comboDisciplineName = "comboDiscipline" . $comboDisciplineCount;
		$output .= "<tr><td><select name='" . $comboDisciplineName . "SID'>" . $comboDisciplineBlock . "</select></td>"
			. "<td><select name='" . $comboDisciplineName . "'><option value='-100'>Not Learned</option><option value='1'>Learned</option></select></td>"
			. "<td><input type='text' name='" . vtm_formatOutput($comboDisciplineName) . "Comment' /></td>"
			. "<td></td></tr>";
		$comboDisciplineCount++;

		$output .= "<tr style='display:none'><td colspan=4><input type='HIDDEN' name='maxNewComboDisciplineCount' value='" . $comboDisciplineCount . "' /></td></tr>";
		$output .= "</table><hr />$jumpto";

		/*******************************************************************************************/
		/*******************************************************************************************/

		$sql = "SELECT path.name,
							   path.id disid,
							   dis.name disname,
							   cpath.level,
							   cpath.comment,
							   cpath.id cpathid
						FROM " . $table_prefix . "CHARACTER_PATH cpath,
							 " . $table_prefix . "PATH path,
							 " . $table_prefix . "DISCIPLINE dis
						WHERE cpath.path_id = path.id
						  AND path.discipline_id = dis.id
						  AND character_id = %d
						ORDER BY disname, path.name";

		$characterPaths = $wpdb->get_results($wpdb->prepare($sql, $characterID));

		$output .= "<table id='gvid_uctpa'>$head";

		$pathCount = 0;
		$arr = array();
		foreach($characterPaths as $characterPath) {
			$pathName = "path" . $pathCount;
			$output .= "<tr><td>" . vtm_formatOutput($characterPath->name) . " (" . vtm_formatOutput(substr($characterPath->disname, 0, 5))  .")</td>"
				. "<td>" . vtm_printSelectCounter($pathName, $characterPath->level, 0, 10) . "</td>"
				. "<td><input type='text' name='"     . $pathName . "Comment' value='" . vtm_formatOutput($characterPath->comment)  . "' /></td>"
				. "<td><input type='checkbox' name='" . $pathName . "Delete' value='"  . $characterPath->cpathid . "' />"
				.     "<input type='HIDDEN' name='"   . $pathName . "ID' value='"      . $characterPath->cpathid . "' /></td></tr>";

			$i++;
			$pathCount++;
		}

		$output .= "<tr style='display:none'><td colspan=4><input type='HIDDEN' name='maxOldPathCount' value='" . $pathCount . "' /></td></tr>";

		$pathBlock = "";
		$paths = vtm_listPaths("Y");
		foreach ($paths as $path) {
			$pathBlock .= "<option value='" . $path->id . "'>" . vtm_formatOutput($path->name) . " (" . vtm_formatOutput(substr($path->disname, 0, 5))  .")</option>";
		}

		for ($i = 0; $i < 2; $i++) {
			$pathName = "path" . $pathCount;
			$output .= "<tr><td><select name='" . $pathName . "SID'>" . $pathBlock . "</select></td>"
				. "<td>" . vtm_printSelectCounter($pathName, "", 0, 10) . "</td>"
				. "<td><input type='text' name='"     . $pathName . "Comment' /></td>"
				. "<td></td></tr>";
			$pathCount++;
		}
		$output .= "<tr style='display:none'><td colspan=4><input type='HIDDEN' name='maxNewPathCount' value='" . $pathCount . "' /></td></tr>";
		$output .= "</table><hr />$jumpto";

		/*******************************************************************************************/
		/*******************************************************************************************/

		$sql = "SELECT ritual.name,
							   ritual.id disid,
							   ritual.level ritlevel,
							   dis.name disname,
							   critual.level,
							   critual.comment,
							   critual.id critualid
						FROM " . $table_prefix . "CHARACTER_RITUAL critual,
							 " . $table_prefix . "RITUAL ritual,
							 " . $table_prefix . "DISCIPLINE dis
						WHERE critual.ritual_id = ritual.id
						  AND ritual.discipline_id = dis.id
						  AND character_id = %d
						ORDER BY disname, level, ritual.name";

		$characterRituals = $wpdb->get_results($wpdb->prepare($sql, $characterID));

		$output .= "<table id='gvid_uctri'>$head";

		$ritualCount = 0;
		$arr = array();
		foreach($characterRituals as $characterRitual) {
			$ritualName = "ritual" . $ritualCount;
			$output .= "<tr><td>" . vtm_formatOutput($characterRitual->name) . " (" . vtm_formatOutput(substr($characterRitual->disname, 0, 5))  . " " . $characterRitual->ritlevel .")</td>"
				. "<td>Learned<input type='HIDDEN' name='" . $ritualName . "' value='0' /></td>"
				. "<td><input type='text' name='"     . $ritualName . "Comment' value='" . vtm_formatOutput($characterRitual->comment)  . "' /></td>"
				. "<td><input type='checkbox' name='" . $ritualName . "Delete' value='"  . $characterRitual->critualid . "' />"
				.     "<input type='HIDDEN' name='"   . $ritualName . "ID' value='"      . $characterRitual->critualid . "' /></td></tr>";

			$i++;
			$ritualCount++;
		}

		$output .= "<tr style='display:none'><td colspan=4><input type='HIDDEN' name='maxOldRitualCount' value='" . $ritualCount . "' /></td></tr>";

		$ritualBlock = "";
		$rituals = vtm_listRituals("Y");
		foreach ($rituals as $ritual) {
			$ritualBlock .= "<option value='" . $ritual->id . "'>" . vtm_formatOutput($ritual->name) . " (" . vtm_formatOutput(substr($ritual->disname, 0, 5))  . " " . $ritual->level . ")</option>";
		}

		for ($i = 0; $i < 5; $i++) {
			$ritualName = "ritual" . $ritualCount;
			$output .= "<tr><td><select name='" . $ritualName . "SID'>" . $ritualBlock . "</select></td>"
				. "<td><select name='" . $ritualName . "'><option value='-100'>Not Learned</option><option value='1'>Learned</option></select></td>"
				. "<td><input type='text' name='"     . $ritualName . "Comment' /></td>"
				. "<td></td></tr>";
			$ritualCount++;
		}
		$output .= "<tr style='display:none'><td colspan=4><input type='HIDDEN' name='maxNewRitualCount' value='" . $ritualCount . "' /></td></tr>";
		$output .= "</table><hr />$jumpto";

		/*******************************************************************************************/
		/*******************************************************************************************/

		$sql = "SELECT office.name,
							   office.id disid,
							   domain.name domainname,
							   coffice.comment,
							   coffice.id cofficeid
						FROM " . $table_prefix . "CHARACTER_OFFICE coffice,
							 " . $table_prefix . "OFFICE office,
							 " . $table_prefix . "DOMAIN domain
						WHERE coffice.office_id = office.id
						  AND coffice.domain_id  = domain.id
						  AND character_id = %d
						ORDER BY office.ordering, office.name, domain.name";

		$characterOffices = $wpdb->get_results($wpdb->prepare($sql, $characterID));

		$output .= "<table id='gvid_uctof'><tr><th>Office name</th>
												<th>Domain</th>
												<th>Status</th>
												<th>Comment</th>
												<th>Delete</th></tr>";

		$officeCount = 0;
		$arr = array();
		foreach($characterOffices as $characterOffice) {
			$officeName = "office" . $officeCount;
			$output .= "<tr><td>" . vtm_formatOutput($characterOffice->name) . "</td>"
				. "<td>" . vtm_formatOutput($characterOffice->domainname) . "</td>"
				. "<td>In office<input type='HIDDEN' name='" . $officeName . "' value='0' /></td>"
				. "<td><input type='text' name='"     . $officeName . "Comment' value='" . vtm_formatOutput($characterOffice->comment)  . "' /></td>"
				. "<td><input type='checkbox' name='" . $officeName . "Delete' value='"  . $characterOffice->cofficeid . "' />"
				.     "<input type='HIDDEN' name='"   . $officeName . "ID' value='"      . $characterOffice->cofficeid . "' /></td></tr>";
			$i++;
			$officeCount++;
		}

		$output .= "<tr style='display:none'><td colspan=5><input type='HIDDEN' name='maxOldOfficeCount' value='" . $officeCount . "' /></td></tr>";

		$officeBlock = "";
		$offices = vtm_listOffices("Y");
		foreach ($offices as $office) {
			$officeBlock .= "<option value='" . $office->ID . "'>" . vtm_formatOutput($office->name) . "</option>";
		}

		$domainBlock = "";
		$domains = vtm_listDomains();
		foreach ($domains as $domain) {
			$domainBlock .= "<option value='" . $domain->ID ."'>" . vtm_formatOutput($domain->name) . "</option>";
		}

		for ($i = 0; $i < 2; $i++) {
			$officeName = "office" . $officeCount;
			$output .= "<tr><td><select name='" . $officeName . "OID'>" . $officeBlock . "</select></td>"
				. "<td><select name='" . $officeName . "CID'>" . $domainBlock . "</select></td>"
				. "<td><select name='" . $officeName . "'><option value='-100'>Not in office</option><option value='1'>In office</option></select></td>"
				. "<td><input type='text' name='"     . $officeName . "Comment' /></td>"
				. "<td></td></tr>";
			$officeCount++;
		}
		$output .= "<tr style='display:none'><td colspan=5><input type='HIDDEN' name='maxNewOfficeCount' value='" . $officeCount . "' /></td></tr>";
		$output .= "</table><hr />$jumpto";

		/*******************************************************************************************/
		/*******************************************************************************************/

		$output .= "<table id='gvid_scc'><tr><td>
					<input type='submit' name='cSubmit' value='Submit character changes' /></td>
					</tr></table>";
		$output .= "</form></div>";
	}
	else {
		$output .= "We encountered an illegal Character ID (". $characterID . ")";
	}
	return $output;
}


function vtm_processCharacterUpdate($characterID) {
	global $wpdb;
	global $vtmglobal;
	$table_prefix = VTM_TABLE_PREFIX;
	
	$wpdb->show_errors();

	$characterName             = $_POST['charName'];
	$characterPlayer           = $_POST['charPlayer'];
	$characterPublicClan       = $_POST['charPubClan'];
	$characterPrivateClan      = $_POST['charPrivClan'];
	$characterGeneration       = $_POST['charGen'];
	$characterSire             = $_POST['charSire'];
	$characterDateOfBirth      = $_POST['charDoB'];
	$characterDateOfEmbrace    = $_POST['charDoE'];
	$characterRoadOrPath       = $_POST['charRoadOrPath'];
	$characterRoadOrPathRating = $_POST['charRoadOrPathRating'];
	$characterDomain           = $_POST['charDomain'];
	$characterSect             = $_POST['charSect'];
	$characterType             = $_POST['charType'];
	$characterStatus           = $_POST['charStatus'];
	$characterStatusComment    = $_POST['charStatusComment'];
	$characterVisible          = $_POST['charVisible'];
	$characterWordPress        = $_POST['charWordPress'];
	$characterNature           = $_POST['charNature'];
	$characterDemeanour        = $_POST['charDemeanour'];
			
	if (get_magic_quotes_gpc()) {
		$characterHarpyQuote = stripslashes($_POST['charHarpyQuote']);
	}
	else {
		$characterHarpyQuote = $_POST['charHarpyQuote'];
	}
	$characterPortraitURL      = $_POST['charPortraitURL'];
	
	// Input Validation
	//	* Check that the wordpress ID exists
	//	* Check that no other characters have the wordpress ID
	//	* Check that no other characters have the same character name
	//	* New characters - Check that a path rating has been entered (required)
	//	* New characters - Check that a willpower rating has been entered (required)
	
	if (isset($characterWordPress) && $characterWordPress != "") {
		if (!username_exists( $characterWordPress )) {
			echo "<p class='vtm_warn'>Warning: Wordpress username $characterWordPress does not exist and will need to be created</p>";
		}
		if (vtm_wordpressid_used($characterWordPress, $characterID)) {
			echo "<p class='vtm_error'>Error: Wordpress username $characterWordPress is used for another character</p>";
			$characterWordPress = "";
		}
	} else {
			echo "<p class='vtm_warn'>Warning: No Wordpress username has been specified</p>";
	}
	if (vtm_charactername_used($characterName, $characterID)) {
			echo "<p class='vtm_error'>Error: Character name " . vtm_formatOutput($characterName) . " already exists</p>";
			$characterName .= "(duplicate)";
	}

	if ((int) $characterID > 0) {
		
		$result = $wpdb->update($table_prefix . "CHARACTER",
				array (
					'NAME' => $characterName, 								'PUBLIC_CLAN_ID' => $characterPublicClan,
					'PRIVATE_CLAN_ID' => $characterPrivateClan, 			'GENERATION_ID' => $characterGeneration,
					'DATE_OF_BIRTH' => $characterDateOfBirth, 				'DATE_OF_EMBRACE' => $characterDateOfEmbrace,
					'SIRE' => $characterSire,								'PLAYER_ID' => $characterPlayer,
					'CHARACTER_TYPE_ID' => $characterType,					'CHARACTER_STATUS_ID' => $characterStatus,
					'CHARACTER_STATUS_COMMENT' =>  $characterStatusComment,	'ROAD_OR_PATH_ID' => $characterRoadOrPath,
					'ROAD_OR_PATH_RATING' => $characterRoadOrPathRating,	'DOMAIN_ID' => $characterDomain,
					'SECT_ID' => $characterSect,							'WORDPRESS_ID' => $characterWordPress,
					'VISIBLE' => $characterVisible
				),
				array (
					'ID' => $characterID
				)
		);
		if (!$result && $result !== 0){
			$wpdb->print_error();
			echo "<p style='color:red'>Could not update $characterName ($characterID)</p>";
			return $characterID;
		}
		
	}
	else {
		$fail = 0;
		if (!isset($characterName) || $characterName == "New Name") {
			echo "<p class='vtm_error'>Error: You must specify a name for the character</p>";
			$fail = 1;
		}
		if (!isset($characterRoadOrPathRating) || $characterRoadOrPathRating == "") {
			$sql = "SELECT NAME FROM " . VTM_TABLE_PREFIX . "ROAD_OR_PATH WHERE ID = %s";
			$pathname = $wpdb->get_var($wpdb->prepare($sql, $characterRoadOrPath));
			
			echo "<p class='vtm_error'>Error: You must enter a " . vtm_formatOutput($pathname) . " rating for the character</p>";
			$fail = 1;
		}
		if (!isset($_POST['Willpower']) || $_POST['Willpower'] == ""  || $_POST['Willpower'] == -100) {
			echo "<p class='vtm_error'>Error: You must enter a Willpower rating for the character</p>";
			$fail = 1;
		}
		if ($fail)
			return $characterID;

		$genstatus	= $wpdb->get_var("SELECT ID FROM " . VTM_TABLE_PREFIX . "CHARGEN_STATUS WHERE NAME = 'Approved';");
		
		$wpdb->show_errors();
		$wpdb->insert($table_prefix . "CHARACTER",
				array (
					'NAME' => $characterName, 								'PUBLIC_CLAN_ID' => $characterPublicClan,
					'PRIVATE_CLAN_ID' => $characterPrivateClan, 			'GENERATION_ID' => $characterGeneration,
					'DATE_OF_BIRTH' => $characterDateOfBirth, 				'DATE_OF_EMBRACE' => $characterDateOfEmbrace,
					'SIRE' => $characterSire,								'PLAYER_ID' => $characterPlayer,
					'CHARACTER_TYPE_ID' => $characterType,					'CHARACTER_STATUS_ID' => $characterStatus,
					'CHARACTER_STATUS_COMMENT' =>  $characterStatusComment,	'ROAD_OR_PATH_ID' => $characterRoadOrPath,
					'ROAD_OR_PATH_RATING' => $characterRoadOrPathRating,	'DOMAIN_ID' => $characterDomain,
					'SECT_ID' => $characterSect,							'WORDPRESS_ID' => $characterWordPress,
					'VISIBLE' => $characterVisible,							'DELETED' => 'N',
					'CHARGEN_STATUS_ID' => $genstatus
				),
				array (
					'%s', '%d', '%d', '%d', '%s', '%s', '%s', '%d', '%d', '%d', '%s', '%d', '%d', '%d', '%d', '%s', '%s', '%s', '%d'
				)
		);
		$wpdb->hide_errors();
		$characterID = $wpdb->insert_id;
		if ($wpdb->insert_id == 0) {
			echo "<p style='color:red'><b>Error:</b> Character " . vtm_formatOutput($characterName) . " could not be added</p>";
			$wpdb->print_error();
			return $characterID;
		} 
		
	}
	
	// Put an initial value into data tables, if they don't already exist
	// Returns the IDs for the relevant rows in each table
	$tableIDs = vtm_setupInitialCharTables($characterID, $characterPlayer, $characterRoadOrPathRating,
		array('Blood' => 10, 'Willpower' => $_POST['Willpower']));
	
	// Update Profile
	$result = $wpdb->update($table_prefix . "CHARACTER_PROFILE",
				array ('QUOTE' => $characterHarpyQuote, 'PORTRAIT' => $characterPortraitURL),
				array ('ID' => $tableIDs['profile']));
	if (!$result && $result !== 0) {
		$wpdb->print_error();
		echo "<p style='color:red'>Could not update profile for " . vtm_formatOutput($characterName) . " ({$tableIDs['profile']})</p>";
		return $characterID;
	}

	$stats = vtm_listStats();
	foreach ($stats as $stat) {
		$currentStat = str_replace(" ", "_", $stat->name);
		if ($_POST[$currentStat] != "" && $_POST[$currentStat] != "-100") {
			if (isset($_POST[$currentStat . "Delete"]) && (int) $_POST[$currentStat . "Delete"] > 0) {
				$sql = "DELETE FROM " . $table_prefix . "CHARACTER_STAT WHERE id = %d";
				$sql = $wpdb->prepare($sql, $_POST[$currentStat . "Delete"]);
			}
			elseif (isset($_POST[$currentStat . "ID"]) && (int) $_POST[$currentStat . "ID"] > 0) {
				$sql = "UPDATE " . $table_prefix . "CHARACTER_STAT
								SET level   =  %d,
									comment =  %s
								WHERE id = %d";
				$sql = $wpdb->prepare($sql, $_POST[$currentStat], $_POST[$currentStat . "Comment"], $_POST[$currentStat . "ID"]);
			}
			else {

				$sql = "INSERT INTO " . $table_prefix . "CHARACTER_STAT (character_id, stat_id, level, comment)
								VALUES (%d, %d, %d, %s)";
				$sql = $wpdb->prepare($sql, $characterID, $stat->id, $_POST[$currentStat], $_POST[$currentStat . "Comment"]);
			}
			$wpdb->query($sql);
		}
	}

	$maxOldSkillCount = $_POST['maxOldSkillCount'];
	$maxSkillCount    = $_POST['maxNewSkillCount'];
	$skillCounter = 0;
	$currentSkill = "";

	while ($skillCounter < $maxSkillCount) {
		$currentSkill = "skill" . $skillCounter;
		if ($_POST[$currentSkill] != "" && $_POST[$currentSkill] != "-100") {
			if ($skillCounter < $maxOldSkillCount) {
				if (isset($_POST[$currentSkill . "Delete"]) && (int) $_POST[$currentSkill . "Delete"] > 0) {
					$sql = "DELETE FROM " . $table_prefix . "CHARACTER_SKILL WHERE id = %d";
					$sql = $wpdb->prepare($sql, $_POST[$currentSkill . "Delete"]);
				}
				elseif (isset($_POST[$currentSkill . "ID"]) && (int) $_POST[$currentSkill . "ID"] > 0) {
					$sql = "UPDATE " . $table_prefix . "CHARACTER_SKILL
									SET level   = %d,
										comment = %s
									WHERE id = %d";
					$sql = $wpdb->prepare($sql, $_POST[$currentSkill], $_POST[$currentSkill . "Comment"], $_POST[$currentSkill . "ID"]);
				}
			}
			else {
				$sql = "INSERT INTO " . $table_prefix . "CHARACTER_SKILL (character_id, skill_id, level, comment)
								VALUES (%d, %d, %d, %s)";
				$sql = $wpdb->prepare($sql, $characterID, $_POST[$currentSkill . "SID"], $_POST[$currentSkill], $_POST[$currentSkill . "Comment"]);
			}
			$wpdb->query($sql);
		}
		$skillCounter++;
	}

	$maxOldDisciplineCount = $_POST['maxOldDisciplineCount'];
	$maxDisciplineCount    = $_POST['maxNewDisciplineCount'];
	$disciplineCounter = 0;
	$currentDiscipline = "";

	while ($disciplineCounter < $maxDisciplineCount) {
		$currentDiscipline = "discipline" . $disciplineCounter;
		if (isset($_POST[$currentDiscipline]) && $_POST[$currentDiscipline] != "" && $_POST[$currentDiscipline] != "-100") {
			if ($disciplineCounter < $maxOldDisciplineCount) {
				if (isset($_POST[$currentDiscipline . "Delete"]) && (int) $_POST[$currentDiscipline . "Delete"] > 0) {
					$sql = "DELETE FROM " . $table_prefix . "CHARACTER_DISCIPLINE WHERE id = %d";
					$sql = $wpdb->prepare($sql, $_POST[$currentDiscipline . "Delete"]);
				}
				elseif (isset($_POST[$currentDiscipline . "ID"]) && (int) $_POST[$currentDiscipline . "ID"] > 0) {
					$sql = "UPDATE " . $table_prefix . "CHARACTER_DISCIPLINE
									SET level   = %d,
										comment = %s
									WHERE id = %d";
					$sql = $wpdb->prepare($sql, $_POST[$currentDiscipline], $_POST[$currentDiscipline . "Comment"], $_POST[$currentDiscipline . "ID"]);
				}
			}
			else {
				$sql = "INSERT INTO " . $table_prefix . "CHARACTER_DISCIPLINE (character_id, discipline_id, level, comment)
								VALUES (%d, %d, %d, %s)";
				$sql = $wpdb->prepare($sql, $characterID, $_POST[$currentDiscipline . "SID"], $_POST[$currentDiscipline], $_POST[$currentDiscipline . "Comment"]);
			}
			$wpdb->query($sql);
		}
		$disciplineCounter++;
	}

	$maxOldComboDisciplineCount = $_POST['maxOldComboDisciplineCount'];
	$maxComboDisciplineCount    = $_POST['maxNewComboDisciplineCount'];
	$comboDisciplineCounter = 0;
	$currentComboDiscipline = "";
	while ($comboDisciplineCounter < $maxComboDisciplineCount) {
		$currentComboDiscipline = "comboDiscipline" . $comboDisciplineCounter;
		if (isset($_POST[$currentComboDiscipline]) && $_POST[$currentComboDiscipline] != "" && $_POST[$currentComboDiscipline] != "-100") {
			if ($comboDisciplineCounter < $maxOldComboDisciplineCount) {
				if (isset($_POST[$currentComboDiscipline . "Delete"]) && (int) $_POST[$currentComboDiscipline . "Delete"] > 0) {
					$sql = "DELETE FROM " . $table_prefix . "CHARACTER_COMBO_DISCIPLINE WHERE id = %d";
					$sql = $wpdb->prepare($sql, $_POST[$currentComboDiscipline . "Delete"]);
				}
				elseif (isset($_POST[$currentComboDiscipline . "ID"]) && (int) $_POST[$currentComboDiscipline . "ID"] > 0) {
					$sql = "UPDATE " . $table_prefix . "CHARACTER_COMBO_DISCIPLINE
									SET comment = %s
									WHERE id = %d";
					$sql = $wpdb->prepare($sql, $_POST[$currentComboDiscipline . "Comment"], $_POST[$currentComboDiscipline . "ID"]);
				}
			}
			else {
				$sql = "INSERT INTO " . $table_prefix . "CHARACTER_COMBO_DISCIPLINE (character_id, combo_discipline_id, comment)
								VALUES (%d, %d, %s)";
				$sql = $wpdb->prepare($sql, $characterID, $_POST[$currentComboDiscipline . "SID"], $_POST[$currentComboDiscipline . "Comment"]);
			}
			$wpdb->query($sql);
			$sql = "";
		}
		$comboDisciplineCounter++;
	}

	$maxOldPathCount = $_POST['maxOldPathCount'];
	$maxPathCount    = $_POST['maxNewPathCount'];
	$pathCounter = 0;
	$currentPath = "";

	while ($pathCounter < $maxPathCount) {
		$currentPath = "path" . $pathCounter;
		if (isset($_POST[$currentPath]) && $_POST[$currentPath] != "" && $_POST[$currentPath] != "-100") {
			if ($pathCounter < $maxOldPathCount) {
				if (isset($_POST[$currentPath . "Delete"]) && (int) $_POST[$currentPath . "Delete"] > 0) {
					$sql = "DELETE FROM " . $table_prefix . "CHARACTER_PATH WHERE id = %d";
					$sql = $wpdb->prepare($sql, $_POST[$currentPath . "Delete"]);
				}
				elseif (isset( $_POST[$currentPath . "ID"]) && (int) $_POST[$currentPath . "ID"] > 0) {
					$sql = "UPDATE " . $table_prefix . "CHARACTER_PATH
									SET level   = %d,
										comment = %s
									WHERE id = %d";
					$sql = $wpdb->prepare($sql, $_POST[$currentPath], $_POST[$currentPath . "Comment"], $_POST[$currentPath . "ID"]);
				}
			}
			else {
				$sql = "INSERT INTO " . $table_prefix . "CHARACTER_PATH (character_id,
																				 path_id,
																				 level,
																				 comment)
								VALUES (%d, %d, %d, %s)";
				$sql = $wpdb->prepare($sql, $characterID, $_POST[$currentPath . "SID"], $_POST[$currentPath], $_POST[$currentPath . "Comment"]);
			}
			$wpdb->query($sql);
		}
		$pathCounter++;
	}

	$maxOldRitualCount = $_POST['maxOldRitualCount'];
	$maxRitualCount    = $_POST['maxNewRitualCount'];
	$ritualCounter = 0;
	$currentRitual = "";

	while ($ritualCounter < $maxRitualCount) {
		$currentRitual = "ritual" . $ritualCounter;
		if (isset($_POST[$currentRitual]) && $_POST[$currentRitual] != "" && $_POST[$currentRitual] != "-100") {
			if ($ritualCounter < $maxOldRitualCount) {
				if (isset($_POST[$currentRitual . "Delete"]) && (int) $_POST[$currentRitual . "Delete"] > 0) {
					$sql = "DELETE FROM " . $table_prefix . "CHARACTER_RITUAL WHERE id = %d";
					$sql = $wpdb->prepare($sql, $_POST[$currentRitual . "Delete"]);
				}
				elseif (isset($_POST[$currentRitual . "ID"]) && (int) $_POST[$currentRitual . "ID"] > 0) {
					$sql = "UPDATE " . $table_prefix . "CHARACTER_RITUAL
									SET level   = %d,
										comment = %s
									WHERE id = %d";
					$sql = $wpdb->prepare($sql, $_POST[$currentRitual], $_POST[$currentRitual . "Comment"], $_POST[$currentRitual . "ID"]);
				}
			}
			else {
				$sql = "INSERT INTO " . $table_prefix . "CHARACTER_RITUAL (character_id, ritual_id, level, comment)
								VALUES (%d, %d, %d, %s)";
				$sql = $wpdb->prepare($sql, $characterID, $_POST[$currentRitual . "SID"], $_POST[$currentRitual], $_POST[$currentRitual . "Comment"]);
			}
			$wpdb->query($sql);
		}
		$ritualCounter++;
	}

	$maxOldBackgroundCount = $_POST['maxOldBackgroundCount'];
	$maxBackgroundCount    = $_POST['maxNewBackgroundCount'];
	$backgroundCounter = 0;
	$currentBackground = "";

	while ($backgroundCounter < $maxBackgroundCount) {
		$currentBackground = "background" . $backgroundCounter;
		if (isset($_POST[$currentBackground]) && $_POST[$currentBackground] != "" && $_POST[$currentBackground] != "-100") {
			if ($backgroundCounter < $maxOldBackgroundCount) {
				if (isset($_POST[$currentBackground . "Delete"]) && (int) $_POST[$currentBackground . "Delete"] > 0) {
					$sql = "DELETE FROM " . $table_prefix . "CHARACTER_BACKGROUND WHERE id = %d";
					$sql = $wpdb->prepare($sql, $_POST[$currentBackground . "Delete"]);
				}
				elseif (isset($_POST[$currentBackground . "ID"]) && (int) $_POST[$currentBackground . "ID"] > 0) {
					$sql = "UPDATE " . $table_prefix . "CHARACTER_BACKGROUND
									SET level   = %d,
										comment = %s
									WHERE id = %d";
					$sql = $wpdb->prepare($sql, $_POST[$currentBackground], $_POST[$currentBackground . "Comment"], $_POST[$currentBackground . "ID"]);
				}
			}
			else {
				$sql = "INSERT INTO " . $table_prefix . "CHARACTER_BACKGROUND (character_id, background_id, level, comment)
								VALUES (%d, %d, %d, %s)";
				$sql = $wpdb->prepare($sql, $characterID, $_POST[$currentBackground . "SID"], $_POST[$currentBackground], $_POST[$currentBackground . "Comment"]);
			}
			$wpdb->query($sql);
		}
		$backgroundCounter++;
	}

	$maxOldMeritCount = $_POST['maxOldMeritCount'];
	$maxMeritCount    = $_POST['maxNewMeritCount'];
	$meritCounter = 0;
	$currentMerit = "";

	while ($meritCounter < $maxMeritCount) {
		$currentMerit = "merit" . $meritCounter;
		if (isset($_POST[$currentMerit]) && $_POST[$currentMerit] != "" && $_POST[$currentMerit] != "-100") {
			if ($meritCounter < $maxOldMeritCount) {
				if (isset( $_POST[$currentMerit . "Delete"]) && (int) $_POST[$currentMerit . "Delete"] > 0) {
					$sql = "DELETE FROM " . $table_prefix . "CHARACTER_MERIT WHERE id =  %d";
					$sql = $wpdb->prepare($sql, $_POST[$currentMerit . "Delete"]);
				}
				elseif (isset($_POST[$currentMerit . "ID"]) && (int) $_POST[$currentMerit . "ID"] > 0) {
					$sql = "UPDATE " . $table_prefix . "CHARACTER_MERIT
									SET level   = %d,
										comment = %s
									WHERE id = %d";
					$sql = $wpdb->prepare($sql, $_POST[$currentMerit], $_POST[$currentMerit . "Comment"], $_POST[$currentMerit . "ID"]);
				}
			}
			else {
				$sql = "INSERT INTO " . $table_prefix . "CHARACTER_MERIT (character_id, merit_id, level, comment)
								VALUES (%d, %d, %d, %s)";
				$sql = $wpdb->prepare($sql, $characterID, $_POST[$currentMerit . "SID"], $_POST[$currentMerit], $_POST[$currentMerit . "Comment"]);
			}
			$wpdb->query($sql);
		}
		$meritCounter++;
	}

	$maxOldOfficeCount = $_POST['maxOldOfficeCount'];
	$maxOfficeCount    = $_POST['maxNewOfficeCount'];
	$officeCounter = 0;
	$currentOffice = "";

	while ($officeCounter < $maxOfficeCount) {
		$currentOffice = "office" . $officeCounter;
		if (isset($_POST[$currentOffice]) && $_POST[$currentOffice] != "" && $_POST[$currentOffice] != "-100") {
			if ($officeCounter < $maxOldOfficeCount) {
				if (isset( $_POST[$currentOffice . "Delete"]) && (int) $_POST[$currentOffice . "Delete"] > 0) {
					$sql = "DELETE FROM " . $table_prefix . "CHARACTER_OFFICE WHERE id = " . $_POST[$currentOffice . "Delete"];
				}
				elseif (isset($_POST[$currentOffice . "ID"]) && (int) $_POST[$currentOffice . "ID"] > 0) {
					$sql = "UPDATE " . $table_prefix . "CHARACTER_OFFICE
									SET comment = '" . $_POST[$currentOffice . "Comment"]  . "'
									WHERE id = " . $_POST[$currentOffice . "ID"];
				}
			}
			else {
				$sql = "INSERT INTO " . $table_prefix . "CHARACTER_OFFICE (character_id, office_id, domain_id, comment)
								VALUES (%d, %d, %d, %s)";
				$sql = $wpdb->prepare($sql, $characterID, $_POST[$currentOffice . "OID"], $_POST[$currentOffice . "CID"], $_POST[$currentOffice . "Comment"]);
			}
			$wpdb->query($sql);
		}
		$officeCounter++;
	}
	
	if ($vtmglobal['config']->USE_NATURE_DEMEANOUR == 'Y') {
		$dataarray = array(
			'NATURE_ID'    => $characterNature,
			'DEMEANOUR_ID' => $characterDemeanour,
		);
		$result = $wpdb->update(VTM_TABLE_PREFIX . "CHARACTER",
					$dataarray,
					array ('ID' => $characterID)
				);
	
	
	}
	
	vtm_touch_last_updated($characterID);
	
	echo "<p><center><strong>Update successful</strong></center></p>";

	return $characterID;
}


function vtm_deleteCharacter($characterID) {
	global $wpdb;
	$table_prefix = VTM_TABLE_PREFIX;

	$sql = "UPDATE " . $table_prefix . "CHARACTER
					SET DELETED = 'Y'
					WHERE ID = %d";

	$sql = $wpdb->prepare($sql, $characterID);
	$wpdb->query($sql);

	//echo "<p>SQL del: $sql</p>";
	$output = "Problem with delete, contact webmaster";

	$sql = "SELECT name
			FROM " . $table_prefix . "CHARACTER
			WHERE 
				ID = %d
				AND DELETED = 'Y'";

	$sql = $wpdb->prepare($sql, $characterID);
	//echo "<p>SQL check: $sql</p>";
	$characterNames = $wpdb->get_results($sql);
	//print_r($characterNames);
	$sqlOutput = "";

	foreach ($characterNames as $characterName) {
		$sqlOutput .= vtm_formatOutput($characterName->name) . " ";
	}

	if ($sqlOutput != "") {
		$output = "Deleted character " . $sqlOutput;
	}
	
	vtm_touch_last_updated($characterID);
	
	return $output;
}

function vtm_setupInitialCharTables($characterID, $playerID, $characterRoadOrPathRating,
	$tempStatRating = array ('Blood' => 10, 'Willpower' => 10)) {
	global $wpdb;
	
	$outIDs = array();

	// CHARACTER PROFILE
	$sql = "SELECT ID FROM " . VTM_TABLE_PREFIX . "CHARACTER_PROFILE WHERE CHARACTER_ID = %s";
	$result = $wpdb->get_var($wpdb->prepare($sql, $characterID));
	
	if (!$result) {
		$wpdb->insert(VTM_TABLE_PREFIX . "CHARACTER_PROFILE",
			array (
				'CHARACTER_ID' => $characterID,
				'QUOTE' => '',
				'PORTRAIT' => ''
			),
			array ('%s', '%s')
		);
		$outIDs['profile'] = $wpdb->insert_id;
	} else {
		$outIDs['profile'] = $result;
	}
	
	// INITIAL XP
	$sql = "SELECT ID FROM " . VTM_TABLE_PREFIX . "PLAYER_XP WHERE CHARACTER_ID = %s";
	$result = $wpdb->get_var($wpdb->prepare($sql, $characterID));

	if (!$result) {
		$xpReasonID = vtm_establishXPReasonID('Initial XP');
		$wpdb->insert(VTM_TABLE_PREFIX . "PLAYER_XP",
			array (
				'PLAYER_ID' => $playerID,
				'CHARACTER_ID' => $characterID,
				'XP_REASON_ID' => $xpReasonID,
				'AWARDED' => Date('Y-m-d'),
				'AMOUNT'  => 0,
				'COMMENT' => "Initial Experience"
			),
			array ('%d', '%d', '%d', '%s', '%d', '%s')
		);
		$outIDs['xp'] = $wpdb->insert_id;
	} else {
		$outIDs['xp'] = $result;
	}

	// INITIAL PATH
	$sql = "SELECT ID FROM " . VTM_TABLE_PREFIX . "CHARACTER_ROAD_OR_PATH WHERE CHARACTER_ID = %s";
	$result = $wpdb->get_var($wpdb->prepare($sql, $characterID));
	
	if (!$result) {
		$pathReasonID = vtm_establishPathReasonID('Initial');
		$wpdb->insert(VTM_TABLE_PREFIX . "CHARACTER_ROAD_OR_PATH",
			array (
				'CHARACTER_ID' => $characterID,
				'PATH_REASON_ID' => $pathReasonID,
				'AWARDED' => Date('Y-m-d'),
				'AMOUNT'  => $characterRoadOrPathRating,
				'COMMENT' => "Initial Path of Enlightenment"
			),
			array ('%d', '%d', '%s', '%d', '%s')
		);
		$outIDs['path'] = $wpdb->insert_id;
	} else {
		$outIDs['path'] = $result;
	}

	// INITIAL TEMP STATS
	$tempstatIDs = array (
		'Blood'     => vtm_establishTempStatID('Blood'), 
		'Willpower' => vtm_establishTempStatID('Willpower')
	);
	$tempStatReasonID = vtm_establishTempStatReasonID('Initial');
	foreach ($tempstatIDs as $tempstatName => $tempstatID) {
		$sql = "SELECT ID
				FROM " . VTM_TABLE_PREFIX . "CHARACTER_TEMPORARY_STAT 
				WHERE CHARACTER_ID = %s AND TEMPORARY_STAT_ID = %d";
		$result = $wpdb->get_var($wpdb->prepare($sql, $characterID, $tempstatID));
	
		if (!$result) {
			$wpdb->insert(VTM_TABLE_PREFIX . "CHARACTER_TEMPORARY_STAT",
				array (
					'CHARACTER_ID' => $characterID,
					'TEMPORARY_STAT_ID' => $tempstatID,
					'TEMPORARY_STAT_REASON_ID' => $tempStatReasonID,
					'AWARDED' => Date('Y-m-d'),
					'AMOUNT'  => $tempStatRating[$tempstatName],
					'COMMENT' => "Initial Temporary Stat Level"
				),
				array ('%d', '%d', '%d', '%s', '%d', '%s')
			);
			$outIDs['stat' . $tempstatID] = $wpdb->insert_id;
		} else {
			$outIDs['stat' . $tempstatID] = $result;
		}
	}
	
	return $outIDs;
}

function vtm_wordpressid_used($wordpressid, $characterID = "") {
	global $wpdb;
		
	$sql = "SELECT ID FROM " . VTM_TABLE_PREFIX . "CHARACTER WHERE WORDPRESS_ID = %s";
	$sql = $wpdb->prepare($sql, $wordpressid);
	$result = $wpdb->get_col($sql);
	
	//print_r($result);
	
	// no matches => not used anywhere
	if ($wpdb->num_rows == 0) {
		return 0;
	// one match, but it is for this character
	} elseif ($wpdb->num_rows == 1 && $characterID == $result[0] && $characterID != "") {
		return 0;
	} else {
		return 1;
	}
}

function vtm_charactername_used($name, $characterID = "") {
	global $wpdb;
	
	$sql = "SELECT ID FROM " . VTM_TABLE_PREFIX . "CHARACTER WHERE NAME = %s AND DELETED = 'N'";
	$sql = $wpdb->prepare($sql, $name);
	$result = $wpdb->get_col($sql);
	
	if ($wpdb->num_rows == 0) {
		return 0;
	// one match, but it is for this character
	} elseif ($wpdb->num_rows == 1 && $characterID == $result[0] && $characterID != "") {
		return 0;
	} else {
		return 1;
	}

}

function vtm_character_chargen_approval() {
	global $wpdb;

	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	
    $testListTable = new vtmclass_admin_charapproval_table();
	
	$showform = 0;
	if (isset($_REQUEST['do_deny'])) {
		// deny
		if (empty($_REQUEST['chargen_denied'])) {
			echo "<p>Please enter why the character has been denied</p>";
			$showform = 1;
		} else {
			$testListTable->deny($_REQUEST['characterID'], $_REQUEST['chargen_denied']);
		}
	}
	elseif (isset($_REQUEST['action']) && 'string' == gettype($_REQUEST['character']) && $_REQUEST['action'] == 'denyit') {
		// prompt for deny message
		$showform = 1;
	}
	elseif (isset($_REQUEST['action']) && 'string' == gettype($_REQUEST['character']) && $_REQUEST['action'] == 'approveit') {
		$testListTable->approve($_REQUEST['character']);
	}
	
	$iconurl = plugins_url('adminpages/icons/',dirname(__FILE__));
	$testListTable->prepare_items();
	$current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
	$noclan_url  = remove_query_arg( 'clan', $current_url );
	?>
	<div class="wrap">
		<h2>Character Approval</h2>
		
		<?php vtm_render_chargen_approve_form($showform, isset($_REQUEST['character']) ? $_REQUEST['character'] : 0); ?>

		<form id="chargen-filter" method="get" action='<?php print htmlentities($current_url); ?>'>
			<input type="hidden" name="page" value="<?php print $_REQUEST['page'] ?>" />
			<?php $testListTable->display() ?>
		</form>
	
	</div>
	<?php
}

function vtm_render_chargen_approve_form($showform, $characterID) {
	global $wpdb;
	
	if ($characterID > 0)
		$character = $wpdb->get_var($wpdb->prepare("SELECT NAME FROM " . VTM_TABLE_PREFIX . "CHARACTER WHERE ID = %s", $characterID));
	else
		$character = "";

	$current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
	$current_url = remove_query_arg( 'action', $current_url );
	
	if ($showform) {
	?>
	<form id="new-<?php print $type; ?>" method="post" action='<?php print htmlentities($current_url); ?>'>
		<input type="hidden" name="characterID" value="<?php print $characterID; ?>" />
		<table style='width:500px'>
		<tr>
			<td>Character: </td><td><?php print vtm_formatOutput($character); ?></td>
		</tr>
		<tr>
			<td>Denied Reason:  </td>
			<td><textarea name="chargen_denied" cols=50></textarea></td>
		</tr>
		</table>
		<input type="submit" name="do_deny" class="button-primary" value="Deny" />
	</form>
	
	<?php
	}
}

class vtmclass_admin_charapproval_table extends vtmclass_MultiPage_ListTable {
   
    function __construct(){
        global $status, $page;
                
        parent::__construct( array(
            'singular'  => 'character',     
            'plural'    => 'characters',    
            'ajax'      => false        
        ) );
    }
	
	function approve($characterID) {
		global $wpdb;
		$wpdb->show_errors();
		
		$playerID = vtm_get_player_id_from_characterID($characterID);
		
		// Transfer XP to character tables (including freebie table)
		$sql = "SELECT * FROM " . VTM_TABLE_PREFIX . "PENDING_XP_SPEND WHERE CHARACTER_ID = %s";
		$sql = $wpdb->prepare($sql, $characterID);
		$results = $wpdb->get_results($sql);
		$failed = 0;
		foreach ($results as $row) {
			$levelcol   = $row->CHARTABLE == 'PENDING_FREEBIE_SPEND' ? 'LEVEL_TO' : 'LEVEL';
			$commentcol = $row->CHARTABLE == 'PENDING_FREEBIE_SPEND' ? 'SPECIALISATION' : 'COMMENT';
			
			$propername = $wpdb->get_var($wpdb->prepare("SELECT NAME FROM " . VTM_TABLE_PREFIX . $row->ITEMTABLE . " WHERE ID = %s", $row->ITEMTABLE_ID ));
		
			if ($row->CHARTABLE_ID > 0) {
				// Update table
				$result = $wpdb->update( VTM_TABLE_PREFIX . $row->CHARTABLE,
					array (
						$levelcol => $row->CHARTABLE_LEVEL,
						$commentcol => $row->SPECIALISATION
					),
					array ('ID' => $row->CHARTABLE_ID)
				);
				if ($result || $result === 0) {
					//echo "<p style='color:green'>Updated XP spend {$row->ITEMTABLE} $propername</p>";
					$sql = "DELETE FROM " . VTM_TABLE_PREFIX . "PENDING_XP_SPEND WHERE ID = %d;";
					$result = $wpdb->get_results($wpdb->prepare($sql, $row->ID));
					
					$reason = $wpdb->get_var("SELECT ID FROM " . VTM_TABLE_PREFIX . "XP_REASON WHERE NAME = 'XP Spend'");
					$data = array (
						'PLAYER_ID'    => $playerID,
						'CHARACTER_ID' => $characterID,
						'XP_REASON_ID' => $reason,
						'AWARDED'      => $row->AWARDED,
						'AMOUNT'       => $row->AMOUNT,
						'COMMENT'	   => "Character Generation: $propername to {$row->CHARTABLE_LEVEL}"
					);
					$wpdb->insert(VTM_TABLE_PREFIX . "PLAYER_XP",
									$data,
									array (
										'%d',
										'%d',
										'%d',
										'%s',
										'%d',
										'%s'
									)
								);
					if ($wpdb->insert_id  == 0) {
						echo "<p style='color:red'><b>Error:</b> XP spend not added to spent XP table for " . vtm_formatOutput($propername);
						$failed = 1;
					} 
			
				}
				else {
					$wpdb->print_error();
					echo "<p style='color:red'>Could not update XP spend {$row->ITEMTABLE} " . vtm_formatOutput($propername) . " ({$row->CHARTABLE_ID})</p>";
					$failed = 1;
				}
			} else {
				$wpdb->insert(VTM_TABLE_PREFIX . $row->CHARTABLE,
					array (
						$levelcol      => $row->CHARTABLE_LEVEL,
						$commentcol    => $row->SPECIALISATION,
						'CHARACTER_ID' => $characterID,
						$row->ITEMTABLE . "_ID" => $row->ITEMTABLE_ID
					),
					array ('%d', '%s', '%d', '%d')
				);
				
				$id = $wpdb->insert_id;
				if ($id == 0) {
					echo "<p style='color:red'><b>Error XP spend:</b> {$row->ITEMTABLE} {$row->ITEMNAME} could not be inserted</p>";
				} else {
					//echo "<p style='color:green'>Added XP spend {$row->ITEMTABLE} $propername (ID: {$wpdb->insert_id})</p>";
					$sql = "DELETE FROM " . VTM_TABLE_PREFIX . "PENDING_XP_SPEND WHERE ID = %d;";
					$result = $wpdb->get_results($wpdb->prepare($sql, $row->ID));
					
					$reason = $wpdb->get_var("SELECT ID FROM " . VTM_TABLE_PREFIX . "XP_REASON WHERE NAME = 'XP Spend'");
					$data = array (
						'PLAYER_ID'    => $playerID,
						'CHARACTER_ID' => $characterID,
						'XP_REASON_ID' => $reason,
						'AWARDED'      => $row->AWARDED,
						'AMOUNT'       => $row->AMOUNT,
						'COMMENT'	   => "Character Generation: $propername to {$row->CHARTABLE_LEVEL}"
					);
					$wpdb->insert(VTM_TABLE_PREFIX . "PLAYER_XP",
									$data,
									array (
										'%d',
										'%d',
										'%d',
										'%s',
										'%d',
										'%s'
									)
								);
					if ($wpdb->insert_id  == 0) {
						echo "<p style='color:red'><b>Error:</b> XP spend not added to spent XP table for {$row->ITEMNAME}";
						$failed = 1;
					} 
				}
			}
		}
		if ($failed) {
			echo "<p style='color:red'>Failed when trying to add the experience point spends to the character</p>";
			return;
		}

		// Transfer freebies to character tables
		$sql = "SELECT * FROM " . VTM_TABLE_PREFIX . "PENDING_FREEBIE_SPEND WHERE CHARACTER_ID = %s";
		$sql = $wpdb->prepare($sql, $characterID);
		$results = $wpdb->get_results($sql);
		$failed = 0;
		foreach ($results as $row) {
			
			if ($row->ITEMTABLE == 'ROAD_OR_PATH') {
				// Path rating already saved as part of finishing step
				$sql = "DELETE FROM " . VTM_TABLE_PREFIX . "PENDING_FREEBIE_SPEND WHERE ID = %d;";
				$result = $wpdb->get_results($wpdb->prepare($sql, $row->ID));
			}
			elseif ($row->CHARTABLE_ID > 0) {
				// Update table
				$result = $wpdb->update( VTM_TABLE_PREFIX . $row->CHARTABLE,
					array (
						'LEVEL'   => $row->LEVEL_TO,
						'COMMENT' => $row->SPECIALISATION
					),
					array ('ID' => $row->CHARTABLE_ID)
				);
				if ($result || $result === 0) {
					//echo "<p style='color:green'>Updated freebie spend {$row->ITEMTABLE} {$row->ITEMNAME}</p>";
					$sql = "DELETE FROM " . VTM_TABLE_PREFIX . "PENDING_FREEBIE_SPEND WHERE ID = %d;";
					$result = $wpdb->get_results($wpdb->prepare($sql, $row->ID));
					// Update pending detail
					if (!empty($row->PENDING_DETAIL)) {
						$wpdb->update( VTM_TABLE_PREFIX . $row->CHARTABLE,
							array ('APPROVED_DETAIL'   => $row->PENDING_DETAIL),
							array ('ID' => $row->CHARTABLE_ID)
						);
					}
				}
				else {
					$wpdb->print_error();
					echo "<p style='color:red'>Could not update freebie spend {$row->ITEMTABLE} {$row->ITEMNAME} ({$row->CHARTABLE_ID})</p>";
					$failed = 1;
				}
			} else {
				$wpdb->insert(VTM_TABLE_PREFIX . $row->CHARTABLE,
					array (
						'LEVEL'        => $row->LEVEL_TO,
						'COMMENT'      => $row->SPECIALISATION,
						'CHARACTER_ID' => $characterID,
						$row->ITEMTABLE . "_ID" => $row->ITEMTABLE_ID
					),
					array ('%d', '%s', '%d', '%d')
				);
				
				$id = $wpdb->insert_id;
				if ($id == 0) {
					echo "<p style='color:red'><b>Error on freebie spend :</b> {$row->ITEMTABLE} {$row->ITEMNAME} could not be inserted</p>";
				} else {
					//echo "<p style='color:green'>Added freebie spend {$row->ITEMTABLE} {$row->ITEMNAME} (ID: {$wpdb->insert_id})</p>";
					$sql = "DELETE FROM " . VTM_TABLE_PREFIX . "PENDING_FREEBIE_SPEND WHERE ID = %d;";
					$result = $wpdb->get_results($wpdb->prepare($sql, $row->ID));
					if (!empty($row->PENDING_DETAIL)) {
						$wpdb->update( VTM_TABLE_PREFIX . $row->CHARTABLE,
							array ('APPROVED_DETAIL'   => $row->PENDING_DETAIL),
							array ('ID' => $id)
						);
					}
				}
			}
		}
		if ($failed) {
			echo "<p style='color:red'>Failed when trying to add the freebie point spends to the character</p>";
			return;
		}
		
		// Approve Pending Detail for extended backgrounds
		//		Questions, Backgrounds, Merits and Flaws
		$tables = array("CHARACTER_EXTENDED_BACKGROUND", "CHARACTER_BACKGROUND", "CHARACTER_MERIT");
		foreach ($tables as $table) {
			$sql = "SELECT ID, PENDING_DETAIL FROM " . VTM_TABLE_PREFIX . $table .
					" WHERE CHARACTER_ID = %s AND PENDING_DETAIL != ''";
			$results = $wpdb->get_results($wpdb->prepare($sql, $characterID));
			foreach ($results as $row) {
				$wpdb->update(VTM_TABLE_PREFIX . $table,
					array('PENDING_DETAIL'  => '', 'APPROVED_DETAIL' => $row->PENDING_DETAIL), 
					array ('ID' => $row->ID)
				);
			}
		}
		
		// Create initial tables for WP, Path, etc
		$RoadOrPathRating = $wpdb->get_var($wpdb->prepare("SELECT ROAD_OR_PATH_RATING FROM " . VTM_TABLE_PREFIX . "CHARACTER WHERE ID = %s", $characterID));
		$willpower = $wpdb->get_var($wpdb->prepare("SELECT LEVEL FROM 
				" . VTM_TABLE_PREFIX . "CHARACTER_STAT cs,
				" . VTM_TABLE_PREFIX . "STAT stat
				WHERE stat.ID = cs.STAT_ID AND CHARACTER_ID = %s
					AND stat.NAME = 'Willpower'", $characterID));
		vtm_setupInitialCharTables($characterID, $playerID, $RoadOrPathRating,
			array ('Blood' => 10, 'Willpower' => $willpower));
		
		
		// Create Wordpress Account with correct role
		// or update the account if it already exists
		// wp_generate_password
		$sql = "SELECT ch.NAME, ch.EMAIL, ch.WORDPRESS_ID, clans.WORDPRESS_ROLE
				FROM " . VTM_TABLE_PREFIX . "CLAN clans,
					" . VTM_TABLE_PREFIX . "CHARACTER ch
				WHERE
					ch.PRIVATE_CLAN_ID = clans.ID 
					AND ch.ID = %s";
		$sql = $wpdb->prepare($sql, $characterID);
		$result = $wpdb->get_row($sql);
		
		$login       = $result->WORDPRESS_ID;
		$email       = $result->EMAIL;
		$displayname = $result->NAME;
		$role        = $result->WORDPRESS_ROLE;
		
		$searchusers = get_users("search=$login");
		//print_r($searchusers);
		// loop through returned to ensure search didn't just return a partial match
		$wpid = 0;
		foreach ($searchusers as $searchuser) {
			if ($searchuser->user_login == $login) {
				foreach ($searchuser->roles as $checkrole) {
					if ($checkrole == 'administrator') {
						$wpid = -1;
					} else {
						$wpid = $searchuser->ID;
					}
				}
			}
		}
		
		if ($wpid == -1) {
			echo "<p>No changes made to administrator account '$login'</p>";
		}
		elseif ($wpid == 0) {
			$pass        = wp_generate_password();
			$userdata = array (
				'user_pass'    => $pass,
				'user_login'   => $login,
				'user_email'   => $email,
				'display_name' => $displayname,
				'role'         => $role
			);
			$user_id = wp_insert_user( $userdata ) ;
			if( is_wp_error($user_id) ) {
				$failed = 1;
				echo "<p style='color:red'>Failed to create new user</p>";
				print_r($userdata);
				return;
			} else {
				echo "<p style='color:green'>User created : $login (ID: $user_id) with '$role' role </p>";
				//print_r($userdata);
			}
			
		}
		else {
			$pass = "";
			$userdata = array (
				'ID'           => $wpid,
				'user_email'   => $email,
				'display_name' => $displayname,
				'role'         => $role
			);

			$result = wp_update_user( $userdata );

			if ( is_wp_error( $result ) ) {
				// There was an error, probably that user doesn't exist.
				$failed = 1;
				echo "<p style='color:red'>Failed to updated user '$login'</p>";
				print_r($userdata);
				return;
			} else {
				echo "<p style='color:green'>User updated : $login (ID: $wpid) with '$role' role </p>";
			}	
		}
		
		if (!$failed) {
			// Update Status and save approval date
			$approvedid = $wpdb->get_var("SELECT ID FROM " . VTM_TABLE_PREFIX . "CHARGEN_STATUS WHERE NAME = 'Approved'");
			$result = $wpdb->update(VTM_TABLE_PREFIX . "CHARACTER",
						array('CHARGEN_STATUS_ID' => $approvedid),
						array('ID' => $characterID)
			);
			$result = $wpdb->update(VTM_TABLE_PREFIX . "CHARACTER_GENERATION",
						array('DATE_OF_APPROVAL' => Date('Y-m-d')),
						array('CHARACTER_ID' => $characterID)
		);

		// Email user with the details
		vtm_email_chargen_approved($characterID, $wpid, $pass);

		}
}
		
	
	function deny($characterID, $denyMessage) {
		global $wpdb;
		
		$statusid = $wpdb->get_var("SELECT ID FROM " . VTM_TABLE_PREFIX . "CHARGEN_STATUS WHERE NAME = 'In Progress'");
		
		// Update Status and ST notes
		$data = array(
			'CHARGEN_STATUS_ID'     => $statusid
		);
		$result = $wpdb->update(VTM_TABLE_PREFIX . "CHARACTER",
			$data,
			array (
				'ID' => $characterID
			)
		);
		
		if ($result) {
			$data = array(
				'NOTE_FROM_ST'  => $denyMessage
			);
			$result = $wpdb->update(VTM_TABLE_PREFIX . "CHARACTER_GENERATION",
				$data,
				array (
					'CHARACTER_ID' => $characterID
				)
			);
			// Email user with the details
			$result = vtm_email_chargen_denied($characterID, $denyMessage);
			
			echo "<p style='color:green'>Denied message saved</p>";
		} else {
			$wpdb->print_error();
			echo "<p style='color:red'>Could not deny character</p>";
		}
		
	}
	
    function process_bulk_action() {
 		global $wpdb;

	}


    function column_default($item, $column_name){
        switch($column_name){
          case 'CLAN':
                return vtm_formatOutput($item->$column_name);
          case 'PLAYER':
                return vtm_formatOutput($item->$column_name);
          case 'WORDPRESS_ID':
                return vtm_formatOutput($item->$column_name);
         case 'TEMPLATE':
                return vtm_formatOutput($item->$column_name);
         case 'CONCEPT':
                return vtm_formatOutput($item->$column_name);
         case 'NOTE_TO_ST':
                return vtm_formatOutput($item->$column_name);
         default:
                return print_r($item,true); 
        }
    }
 
    function column_name($item){
        
        $actions = array(
            'view'      => sprintf('<a href="%s?characterID=%s">View</a>',get_page_link($this->stlinks['viewCharGen']->WP_PAGE_ID),$item->ID),
            'print'     => sprintf('<a href="%s?characterID=%s">Print</a>',get_page_link($this->stlinks['printCharSheet']->WP_PAGE_ID),$item->ID),
            'approveit' => sprintf('<a href="?page=%s&amp;action=%s&amp;character=%s">Approve</a>',$_REQUEST['page'],'approveit',$item->ID),
            'denyit'    => sprintf('<a href="?page=%s&amp;action=%s&amp;character=%s">Deny</a>',$_REQUEST['page'],'denyit',$item->ID),
        );
        
        return sprintf('%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
            stripslashes($item->NAME),
            $item->ID,
            $this->row_actions($actions)
        );
    }
   

    function get_columns(){
        $columns = array(
            'cb'         => '<input type="checkbox" />', 
            'NAME'       => 'Name',
            'CLAN' 		 => 'Clan',
            'PLAYER'     => 'Player',
            'WORDPRESS_ID' => 'Login Name',
            'TEMPLATE'   => 'Template',
            'CONCEPT'    => 'Character Concept',
            'NOTE_TO_ST' => 'Note to Storytellers'
       );
        return $columns;
		
    }
    
    function get_sortable_columns() {
        $sortable_columns = array(
            'NAME'   => array('NAME',true),
            'CLAN'   => array('CLAN',false),
            'PLAYER' => array('PLAYER',false)
       );
        return $sortable_columns;
    }
	
    function prepare_items() {
        global $wpdb; 
        
        $this->type    = "chargen";
		$this->stlinks = $wpdb->get_results("SELECT VALUE, WP_PAGE_ID FROM " . VTM_TABLE_PREFIX. "ST_LINK ORDER BY ORDERING", OBJECT_K);

        $columns  = $this->get_columns();
        $hidden   = array();
        $sortable = $this->get_sortable_columns();
		
		$this->_column_headers = array($columns, $hidden, $sortable);
		
        $this->process_bulk_action();
		
		/* Get the data from the database */
		$sql = "SELECT ch.ID, ch.NAME, pl.NAME as PLAYER, clan.NAME as CLAN,
					ch.CONCEPT, cg.NOTE_TO_ST, cgt.NAME as TEMPLATE, ch.WORDPRESS_ID
				FROM
					" . VTM_TABLE_PREFIX . "PLAYER pl,
					" . VTM_TABLE_PREFIX . "CHARACTER ch,
					" . VTM_TABLE_PREFIX . "CLAN clan,
					" . VTM_TABLE_PREFIX . "CHARGEN_STATUS cgs,
					" . VTM_TABLE_PREFIX . "CHARGEN_TEMPLATE cgt,
					" . VTM_TABLE_PREFIX ."CHARACTER_GENERATION cg
				WHERE
					ch.PLAYER_ID = pl.id
					AND ch.PRIVATE_CLAN_ID = clan.id
					AND ch.CHARGEN_STATUS_ID = cgs.ID
					AND cg.TEMPLATE_ID = cgt.ID
					AND cg.CHARACTER_ID = ch.ID 
					AND cgs.NAME = 'Submitted'
					AND ch.DELETED = 'N'";
				
			/* order the data according to sort columns */
		if (!empty($_REQUEST['orderby']) && !empty($_REQUEST['order']))
			$sql .= " ORDER BY {$_REQUEST['orderby']} {$_REQUEST['order']}, NAME ASC";
		else
			$sql .= " ORDER BY NAME ASC";
					
		//echo "<p>SQL: $sql</p>";
		
		$data =$wpdb->get_results($sql);
        
        $current_page = $this->get_pagenum();
        $total_items = count($data);
        
        $this->items = $data;
        
        $this->set_pagination_args( array(
            'total_items' => $total_items,                  
            'per_page'    => $total_items,                  
            'total_pages' => 1
        ) );
    }

}

function vtm_email_chargen_denied($characterID, $denyMessage) {
	global $current_user;
	global $wpdb;
	
	$sql = "SELECT ch.NAME as name, pl.NAME as player, ch.EMAIL as email
			FROM " . VTM_TABLE_PREFIX . "CHARACTER ch,
				" . VTM_TABLE_PREFIX . "PLAYER pl
			WHERE
				ch.PLAYER_ID = pl.ID
				AND ch.ID = %s";
	$results = $wpdb->get_row($wpdb->prepare($sql, $characterID));

	$name   = stripslashes($results->name);
	$player = stripslashes($results->player);
	$email  = $results->email;
	$ref = vtm_get_chargen_reference($characterID);
	$url = add_query_arg('reference', $ref, vtm_get_stlink_url('viewCharGen', true));	
	
	$userbody = "Hello $player,
	
The Storytellers have provided feedback on $name. Please review the comments and resubmit your character once any issues have been resolved.
	
'" . stripslashes($denyMessage) . "'
	
You can return to character generation by following this link: $url";
	
	$result = vtm_send_email($email, "Review Character Generation: $name", $userbody);
	
	if (!$result)
		echo "<p>Failed to send email. Character Ref: $ref</p>";
		
	return $result;
}

function vtm_email_chargen_approved($characterID, $wpid, $password) {
	global $current_user;
	global $wpdb;
	
	$sql = "SELECT ch.NAME as name, pl.NAME as player, ch.EMAIL as email,
				ch.WORDPRESS_ID as username
			FROM " . VTM_TABLE_PREFIX . "CHARACTER ch,
				" . VTM_TABLE_PREFIX . "PLAYER pl
			WHERE
				ch.PLAYER_ID = pl.ID
				AND ch.ID = %s";
	$results = $wpdb->get_row($wpdb->prepare($sql, $characterID));

	$name     = $results->name;
	$player   = $results->player;
	$email    = $results->email;
	$username = $results->username;
	$website  = site_url();
		
	$url1 = vtm_get_stlink_url('viewProfile', true);
	$url2 = vtm_get_stlink_url('viewCharSheet', true);
	$url3 = vtm_get_stlink_url('printCharSheet', true);
	$url4 = vtm_get_stlink_url('viewXPSpend', true);
	
	$userbody = "Hello $player,\n\nThe Storytellers have approved your character.  ";
	
	if ($wpid == 0) {
		// New Wordpress Account
		$userbody .= "Please log into the website with the below details:\n\n";
		$userbody .= "\tLogin name: $username\n";
		$userbody .= "\tPassword:   $password\n";
		$userbody .= "\tWebsite:    $website\n\n";
	}
	else {
		// Already existing WP account
		$userbody .= "Please log into the website with login details already created:\n\n";
		$userbody .= "\tLogin name: $username\n";
		$userbody .= "\tWebsite:    $website\n\n";
	}
	
	$userbody .= "Here are some useful direct links:\n\n";
	$userbody .= "\tChange your password:  $url1\n";
	$userbody .= "\tView your character:   $url2\n";
	$userbody .= "\tPrint your character:  $url3\n";
	$userbody .= "\tSpend Experience:      $url4\n";
	
	$result = vtm_send_email($email, "Character Approved: $name", $userbody);
	
	if (!$result)
		echo "<p>Failed to send email. Character: $name, Player: $player</p>";
		
	return $result;
}

function vtm_purge_character($characterID, $name) {

	$tables = array(
		'CHARACTER_OFFICE',
		'CHARACTER_ROAD_OR_PATH',
		'CHARACTER_TEMPORARY_STAT',
		'CHARACTER_STAT',
		'CHARACTER_RITUAL',
		'CHARACTER_DISCIPLINE',
		'CHARACTER_PATH',
		'CHARACTER_MERIT',
		'CHARACTER_SKILL',
		'CHARACTER_BACKGROUND',
		'CHARACTER_COMBO_DISCIPLINE',
		'CHARACTER_PROFILE',
		'CHARACTER_EXTENDED_BACKGROUND',
		'CHARACTER_GENERATION',
		'PLAYER_XP',
		'MAIL_QUEUE',
		'PENDING_XP_SPEND',
		'PENDING_FREEBIE_SPEND',
		'CHARACTER',
	);
	
	$done = 0;
	$ok = 1;
	while(!$done && $ok) {
	
		if (count($tables) == 0) {
			$done = 1;
		} else {
			$table = array_shift($tables);
			$column = $table == 'CHARACTER' ? 'ID' : 'CHARACTER_ID';
			$ok = vtm_purge_table($characterID, $table, $column);
		}
	}
	
	if ($ok)
		$result = "<li>Deleted " . vtm_formatOutput($name) . ", ID: $characterID</li>";
	else
		$result = "<li style='color:red;'>Failed to delete " . vtm_formatOutput($name) . ", ID: $characterID</li>";
		
	return $result;
}
function vtm_purge_table($characterID, $table, $column) {
	global $wpdb;
	$ok = 1;
	
	$sql = "SELECT COUNT(ID) FROM " . VTM_TABLE_PREFIX . $table . " WHERE $column = %s";
	$count = $wpdb->get_var($wpdb->prepare($sql, $characterID));
	
	if ($count > 0) {
		$result = $wpdb->delete( VTM_TABLE_PREFIX . $table, 
			array ($column => $characterID), 
			array ('%d')
		);
		if ($result) {
			//echo "<li>Delete OK for $table for ID $characterID</li>";
		}
		elseif ($result === 0) {
			echo "<li>Delete failed (0) for $table for ID $characterID</li>";
			$ok = 0;
			$wpdb->show_errors();
			$wpdb->print_error();
		}
		else {
			echo "<li>Delete failed for $table for ID $characterID</li>";
			$ok = 0;
		}
	} //else {
	//	echo "<li>No rows in $table for ID $characterID</li>";
	//}
	

	return $ok;
}
?>