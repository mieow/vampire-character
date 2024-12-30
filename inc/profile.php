<?php

function vtm_profile_content_filter($content) {

	if (is_page(vtm_get_stlink_page('viewProfile'))) {
		$content .= vtm_get_profile_content();
	}
  // otherwise returns the database content
  return $content;
}

add_filter( 'the_content', 'vtm_profile_content_filter' );


function vtm_get_profile_content() {
	global $wpdb;
	global $vtmglobal;

	// Work out current character and what character profile is requested
	$currentUser      = wp_get_current_user();
	$currentCharacter = $currentUser->user_login;
	$vtmglobal['character'] = $currentCharacter;
	$currentCharacterID = vtm_establishCharacterID($currentCharacter);
	
	if (isset($_REQUEST['CHARACTER']))
		$character = $_REQUEST['CHARACTER'];
	elseif (!empty($currentCharacter))
		$character = $currentCharacter;
	else
		return "<p>You need to specify a character or be logged in to view the profiles.</p>";

	$output       = "";
	$clanPrestige = 0;
	$showAll = false;
	
	$sql = "SELECT pub.NAME as pubclan, priv.NAME as privclan
			FROM 
				" . $wpdb->prefix . "vtm_CHARACTER chars,
				" . $wpdb->prefix . "vtm_CLAN pub,
				" . $wpdb->prefix . "vtm_CLAN priv
			WHERE
				chars.PUBLIC_CLAN_ID = pub.ID
				AND chars.PRIVATE_CLAN_ID = priv.ID
				AND chars.ID = %s";
	$result = $wpdb->get_row($wpdb->prepare("$sql", $currentCharacterID));
	
	$observerClanPub  = isset($result->pubclan) ? $result->pubclan : '';
	$observerClanPriv = isset($result->privclan) ? $result->privclan : '';
	
	// Show full character details to STs and if you are viewing your own profile
	if (vtm_isST() || $character == $currentCharacter)
		$showAll = true;

	$sql = "SELECT ID 
			FROM " . $wpdb->prefix . "vtm_CHARACTER 
			WHERE WORDPRESS_ID = %s
			AND DELETED = 'N'";
	$sql = $wpdb->prepare("$sql", $character);
	$characterID = $wpdb->get_var("$sql");
	
	if (empty($characterID))
		return "<p>No information found for $character</p>";
	
	$mycharacter = new vtmclass_character();
	$mycharacter->load($characterID);
	$emailAddress = $mycharacter->email;

	if (vtm_isST() || $currentCharacter == $character) {
		
		// Update display name
		$user = get_user_by('login', $character);
		$displayName = isset($user->display_name) ? $user->display_name : $mycharacter->name;
		$userID = isset($user->ID) ? $user->ID : 0;
	
		if (isset($_POST['VTM_FORM']) && $_POST['VTM_FORM'] == 'updateDisplayName' && isset($_POST['displayName']) 
			&& !empty($_POST['displayName']) && $_POST['displayName'] != $displayName && $userID > 0) {
			
			$newDisplayName = vtm_formatOutput($_POST['displayName']);
			
			$output .= "<p>Changed display name to <i>$newDisplayName</i></p>";
			vtm_changeDisplayNameByID ($userID, $newDisplayName);
			$displayName = $newDisplayName;
		}
	
		// Update email address
		if (isset($_POST['VTM_FORM']) && $_POST['VTM_FORM'] == 'updateEmail' && isset($_POST['emailAddress']) 
			&& !empty($_POST['emailAddress']) && $_POST['emailAddress'] != $emailAddress) {
			
			$newemailAddress = sanitize_email($_POST['emailAddress']);
			
			// SAVE email to database and wordpress account
			if (is_email($newemailAddress)) {
			
				$result = $wpdb->update($wpdb->prefix . "vtm_CHARACTER",
					array('EMAIL' => $newemailAddress),
					array('ID'    => $characterID)
				);
				if (!$result && $result !== 0){
					echo "<p style='color:red'>Could not save character email " . esc_html("$newemailAddress ($characterID)") . "</p>";
				} else {
					vtm_changeEmailByID($userID, $newemailAddress);
					
					$output .= "<p>Changed email <i>$newemailAddress</i></p>";
				}
			} else {
				$output .= "<p>Email address <i>$newemailAddress</i> is invalid</p>";
			}
				$emailAddress = $newemailAddress;
		}
	
		// Update password
		if (isset($_POST['newPassword1'])) {
			$user = get_user_by('login', $character);
			$userID = $user->ID;
		
			$newPassword1 = $_POST['newPassword1'];
			$newPassword2 = $_POST['newPassword2'];
			
			if ($_POST['VTM_FORM'] == 'updatePassword' 
				&& isset($newPassword1) && !empty($newPassword1) 
				&& isset($newPassword2) && !empty($newPassword2) 
				) {
				
				if ($newPassword1 !== $newPassword2) {
					$output .= "<p>Passwords don't match</p>";
				} 
				elseif (vtm_changePasswordByID($userID, $newPassword1, $newPassword2)) {
					$output .= "<p>Successfully changed password</p>";
				}
				else {
					$output .= "<p>Failed to change password</p>";
				}
			}
		}
		
		// Update Email Newsletter settings
		if (isset($_POST['newsletterUpdate'])) {
			$result = $wpdb->update($wpdb->prefix . "vtm_CHARACTER",
				array('GET_NEWSLETTER' => $_POST['vtm_news_optin']),
				array('ID' => $characterID)
			);
			if (!$result && $result !== 0){
				echo "<p style='color:red'>Could not save newsletter setting</p>";
			} else {
				$output .= "<p>Changed newsletter setting</p>";
			}
			$mycharacter->newsletter = $_POST['vtm_news_optin'];
		}

		// Set Portrait settings
		if (isset($_POST['set_vtm_portrait'])) {
			$result = $wpdb->update($wpdb->prefix . "vtm_CHARACTER_PROFILE",
				array('PORTRAIT' => $_POST['vtm_portrait_set']),
				array('CHARACTER_ID' => $characterID)
			);
			if (!$result && $result !== 0){
				echo "<p style='color:red'>Could not save profile image location</p>";
			} else {
				$output .= "<p>Changed profile image location</p>";
			}
			$mycharacter->portrait = $_POST['vtm_portrait_set'];
		}
		if (isset($_POST['clear_vtm_portrait'])) {
			$result = $wpdb->update($wpdb->prefix . "vtm_CHARACTER_PROFILE",
				array('PORTRAIT' => ''),
				array('CHARACTER_ID' => $characterID)
			);
			$mycharacter->portrait = '';
		}

		
		// Upload Portrait
		// Check that the nonce is valid, and the user can edit this post.
		if ( 
			isset( $_POST['vtm_portrait_nonce']) 
			&& wp_verify_nonce( $_POST['vtm_portrait_nonce'], 'vtm_portrait' )
			&& get_option('vtm_user_upload_image', '0') == '1'
		) {
			//print_r($_FILES);
			// check file type 
			if (strpos($_FILES['vtm_portrait']['type'], "image") !== 0) {
				echo "<p style='color:red'>Uploaded file is not an image</p>";
			}
			elseif (get_option('vtm_max_size', '0') != 0 
				&& $_FILES['vtm_portrait']['size'] > get_option('vtm_max_size', '0')) {
				echo "<p style='color:red'>Uploaded file is greater than the maximum size of " . esc_html(get_option('vtm_max_size', '0')) ." bytes</p>";
			}
			else {
				if (class_exists('Imagick')) {
					$image = new imagick($_FILES['vtm_portrait']['tmp_name']); 
					$geo = $image->getImageGeometry();
					$sizex=$geo['width'];
					$sizey=$geo['height']; 
				}
				elseif (extension_loaded('gd')) {
					list($sizex, $sizey, $type, $attr) = getimagesize($_FILES['vtm_portrait']['tmp_name']);
				}
				
				if (get_option('vtm_max_width', '0') != 0 &&
					$sizex > get_option('vtm_max_width', '0') ){
					echo "<p style='color:red'>Uploaded image is wider than the maximum width of " . esc_html(get_option('vtm_max_width', '0')) ." pixels</p>";
				}
				elseif (get_option('vtm_max_height', '0') != 0 &&
					$sizey > get_option('vtm_max_height', '0') ){
					echo "<p style='color:red'>Uploaded image is taller than the maximum height of " . esc_html(get_option('vtm_max_height', '0')) ." pixels</p>";
				} else {
					
					require_once( ABSPATH . 'wp-admin/includes/image.php' );
					require_once( ABSPATH . 'wp-admin/includes/file.php' );
					require_once( ABSPATH . 'wp-admin/includes/media.php' );
					
					// Let WordPress handle the upload.
					// Remember, 'my_image_upload' is the name of our file input in our form above.
					$attachment_id = media_handle_upload( 'vtm_portrait', 0 );
					
					if ( is_wp_error( $attachment_id ) ) {
						// There was an error uploading the image.
						echo "<p style='color:red'>Could not upload portrait</p>";
					} else {
						// The image was uploaded successfully!
						
						// Check attributes
						$upload_dir = wp_upload_dir();
						$metadata = wp_get_attachment_metadata( $attachment_id ); // returns an array
						//print_r($metadata);
						
						// Save path to character
						$portaitpath = $upload_dir['baseurl']  . "/" . $metadata['file'];
						
						$wpdb->update($wpdb->prefix . "vtm_CHARACTER_PROFILE",
							array ('PORTRAIT' => $portaitpath),
							array ('CHARACTER_ID' => $characterID)
						);
						$mycharacter->portrait = $portaitpath;
						
						$output .= "<p>Uploaded portrait</p>";
					}

				}
			}
			
			
		} else {

			// The security check failed, maybe show the user an error.
		}
		
		// Update quote
		if (isset($_POST['charHarpyQuote']) && get_option('vtm_user_set_quote', '0') == '1') {
			$result = $wpdb->update($wpdb->prefix . "vtm_CHARACTER_PROFILE",
				array('QUOTE' => $_POST['charHarpyQuote']),
				array('CHARACTER_ID' => $characterID)
			);
			if (!$result && $result !== 0){
				echo "<p style='color:red'>Could not save quote</p>";
			} else {
				$output .= "<p>Changed quote</p>";
			}
			$mycharacter->quote = $_POST['charHarpyQuote'];
				
		}
		
		// Update Pronouns
		if (isset($_POST['Pronouns'])) {
			$result = $wpdb->update($wpdb->prefix . "vtm_CHARACTER",
				array('PRONOUNS' => $_POST['Pronouns']),
				array('ID' => $characterID)
			);
			if (!$result && $result !== 0){
				echo "<p style='color:red'>Could not save pronouns</p>";
			} else {
				$output .= "<p>Changed pronouns</p>";
			}
			$mycharacter->pronouns = $_POST['Pronouns'];
				
		}
	} else {
		$displayName = $mycharacter->name;
	}
	
	if ($showAll) {
		$clanIcon = $mycharacter->private_icon;  // private clan
	} else {
		$clanIcon = $mycharacter->public_icon;   // public clan
	}
	
	// Title, with link to view character for STs
	$characterDisplayName = vtm_isST() ? 
							"<a href='" . vtm_get_stlink_url('viewCharSheet') . "?CHARACTER=" . urlencode($character) . "'>" . esc_html($displayName) . "</a>" 
							: esc_html($displayName);
	$output .= "<h1>" . $characterDisplayName . "</h1>";
	
	// Profile info
	$output .= "<table class='gvplugin vtmprofile' id=\"gvid_prof_out\">\n";
	$output .= "<tr><td class=\"gvcol_1 gvcol_val\">\n";
	// Character Info
	$output .= "<p><img alt='[Clan Icon]' src='$clanIcon' />" .wp_kses(stripslashes($mycharacter->quote), "post"). "</p>\n";
	$output .= "<table class='gvplugin vtmprofile' id=\"gvid_prof_in\">\n";
    $output .= "<tr><td class=\"gvcol_1 gvcol_key\">Player:</td><td class=\"gvcol_2 gvcol_val\">" . vtm_formatOutput($mycharacter->player) . "</td></tr>";
	$output .= "<tr><td class=\"gvcol_1 gvcol_key\">Clan:</td><td class=\"gvcol_2 gvcol_val\">" . vtm_formatOutput($mycharacter->clan);
	if ($showAll && $mycharacter->clan != $mycharacter->private_clan)
		$output .= " (" . vtm_formatOutput($mycharacter->private_clan) . ")";
	$output .= "</td></tr>";
    $output .= "<tr><td class=\"gvcol_1 gvcol_key\">Resides:</td><td class=\"gvcol_2 gvcol_val\">" . vtm_formatOutput($mycharacter->domain) . "</td></tr>";
	
	// Background - Status
	if ($vtmglobal['config']->DISPLAY_BACKGROUND_IN_PROFILE) {
		$sql = "SELECT NAME FROM " . $wpdb->prefix . "vtm_BACKGROUND
				WHERE ID = %d";
		$background = $wpdb->get_var($wpdb->prepare("$sql", $vtmglobal['config']->DISPLAY_BACKGROUND_IN_PROFILE));	
	
		$level = 0;
		foreach ($mycharacter->backgrounds as $row) {
			if ($row->background == $background)
				$level = $row->level;
		}
        $output .= "<tr><td class=\"gvcol_1 gvcol_key\">" . vtm_formatOutput($background) . ":</td><td class=\"gvcol_2 gvcol_val\">" . $level . "</td></tr>";
	}
	
	// Condition
	$output .= "<tr><td class=\"gvcol_1 gvcol_key\">Condition:</td><td class=\"gvcol_2 gvcol_val\">" . vtm_formatOutput($mycharacter->char_status);
	if ($mycharacter->char_status_comment != "") {
		$output .= " (" . vtm_formatOutput($mycharacter->char_status_comment) . ")";
	}
	$output .= "</td></tr>";
	
	// Pronouns
	$output .= "<tr><td class=\"gvcol_1 gvcol_key\">Character Pronouns:</td><td class=\"gvcol_2 gvcol_val\">" . vtm_formatOutput($mycharacter->pronouns);
	$output .= "</td></tr>";
	
	// Clan Prestige
	foreach ($mycharacter->backgrounds as $row) {
		$testClan = empty($row->comment) ? $mycharacter->clan : $row->comment;
	
		if ($row->background == "Clan Prestige") {
			if ($showAll || $observerClanPub  == $testClan || $observerClanPriv == $testClan)
				$output .= "<tr><td class=\"gvcol_1 gvcol_key\">Clan Prestige (" . vtm_formatOutput($testClan) . "):</td><td class=\"gvcol_2 gvcol_val\">" . $row->level . "</td></tr>";
		}
	}
	
	// Merits/Flaws - Clan Friendship/Enmity
	$sql = "SELECT merits.NAME
			FROM 
				" . $wpdb->prefix . "vtm_MERIT merits,
				" . $wpdb->prefix . "vtm_PROFILE_DISPLAY disp
			WHERE
				merits.PROFILE_DISPLAY_ID = disp.ID
				AND disp.NAME = 'If Clan Matches'
			ORDER BY merits.NAME";
	$displayMerits = $wpdb->get_col("$sql");
	foreach ($displayMerits as $displaymerit) {
		foreach ($mycharacter->meritsandflaws as $charmerit) {
			if ($displaymerit != $charmerit->name)
				continue;
			
			if ($showAll || $observerClanPub  == $charmerit->comment 
				|| $observerClanPriv == $charmerit->comment) {
				$output .= "<tr><td class=\"gvcol_1 gvcol_key\">" . vtm_formatOutput($displaymerit) . ":</td><td class=\"gvcol_2 gvcol_val\">" . vtm_formatOutput($charmerit->comment) . "</td></tr>";
			
			}
		}
	}
	
	// Positions (add offices to mycharacter class)
	if (count($mycharacter->offices) > 0) {
		$output .= "<tr><td class=\"gvcol_1 gvcol_key\">Positions:</td><td class=\"gvcol_2 gvcol_val\">";
		$offices = array();
		foreach ($mycharacter->offices as $office) {
			if ($office->visible == 'Y') {
				if ($office->domain == $mycharacter->domain)
					array_push($offices, vtm_formatOutput($office->name));
				else
					array_push($offices, vtm_formatOutput($office->name) . " (" . vtm_formatOutput($office->domain) . ")");
			}	
		}
		$output .= implode("<br />", $offices);
		$output .= "</td></tr>";
	}
	
	// Public addresses
	$addr2display = array();
	if (get_option('vtm_feature_pm', '0')) {
		if (count($mycharacter->addresses) > 0) {
			foreach ($mycharacter->addresses as $address) {
				if ($address->VISIBLE == 'Y' || vtm_isST() || $currentCharacter == $character ) {
					$addr2display[] = $address;
				}
			}
		}
	}
	if (count($addr2display) > 0) {
		$output .= "<tr><td class=\"gvcol_1 gvcol_key\">Addresses:</td>
			<td class=\"gvcol_2 gvcol_val\">";
		foreach ($addr2display as $address) {
			$output .= "<strong>" . vtm_formatOutput($address->NAME) . 
				"</strong>: " . vtm_pm_link(
					vtm_formatOutput($address->PM_CODE), 
					array('code' => $address->PM_CODE, 'characterID' => $characterID));
			if ($address->VISIBLE == 'N') {
				$output .= " (private)";
			}
			$output .= "<br />";
		}
		$output .= "<br />";
	}
	// and post office, if enabled
	if (get_option( 'vtm_feature_pm', '0' ) == '1' && is_user_logged_in()) {
		$output .= "<tr><td class=\"gvcol_1 gvcol_key\">Contact:</td>
			<td class=\"gvcol_2 gvcol_val\">";
		$output .= "<strong>" . vtm_pm_link(vtm_formatOutput(get_option( 'vtm_pm_ic_postoffice_location' )), array('code' => '', 'characterID' => $characterID)) . 
			"</strong>";
		$output .= "<br />";
		$output .= "</td></tr>";
	}
	
	$output .= "</table></td><td class=\"gvcol_2 gvcol_img\">\n";
	// Portrait

	$output .= "<img alt='[Profile Image]' src='?vtm_get_portrait=$characterID'>";
	
	$output .= "</td></tr>";
	$output .= "</table>";
	
	
	// change password and display name form
	if (vtm_isST() || $currentCharacter == $character) {
		$user = get_user_by('login', $character);
		$displayName = isset($user->display_name) ? $user->display_name : $mycharacter->name;
		$userID = isset($user->ID) ? $user->ID : 0;
		
		$output .= "<div class='vtmext_section vtmprofile'>";
		$output .= "<h4>Update Display Name:</h4>";
		$output .= "<form name=\"DISPLAY_NAME_UPDATE_FORM\" method='post'>";

		$output .= "<input type='HIDDEN' name=\"VTM_FORM\" value=\"updateDisplayName\" />";
		$output .= "<input type='HIDDEN' name=\"USER_ID\" value=\"" . $userID . "\" />";
		
		$output .= "<table>\n";
		$output .= "<tr><td class=\"gvcol_1 gvcol_key\">Display Name:</td><td class=\"gvcol_2 gvcol_val\">";
		$output .= "<input type='text' size=50 maxlength=50 name=\"displayName\" value=\"" . wp_kses($displayName,"data") . "\">";
		$output .= "</td>\n";
		$output .= "<td><input type='submit' name=\"displayNameUpdate\" value=\"Update\"></td>";
		$output .= "</tr>";
		$output .= "</table></form>\n";

		$output .= "</div><div class='vtmext_section vtmprofile'>";
		$output .= "<h4>Update Email Address:</h4>";
		$output .= "<form name=\"EMAIL_UPDATE_FORM\" method='post'>";

		$output .= "<input type='HIDDEN' name=\"VTM_FORM\" value=\"updateEmail\" />";
		$output .= "<input type='HIDDEN' name=\"USER_ID\" value=\"" . $userID . "\" />";
		
		$output .= "<table>\n";
		$output .= "<tr><td class=\"gvcol_1 gvcol_key\">Email address:</td><td class=\"gvcol_2 gvcol_val\">";
		$output .= "<input type='text' size=60 maxlength=60 name=\"emailAddress\" value=\"" . $emailAddress . "\">";
		$output .= "</td>\n";
		$output .= "<td><input type='submit' name=\"emailAddressUpdate\" value=\"Update\"></td>";
		$output .= "</tr>";
		$output .= "</table></form>\n";

		$output .= "</div><div class='vtmext_section vtmprofile'>";
		$output .= "<h4>Update Password:</h4>";
		$output .= "<form name=\"PASSWORD_UPDATE_FORM\" method='post'>";

		$output .= "<input type='HIDDEN' name=\"VTM_FORM\" value=\"updatePassword\" />";
		$output .= "<input type='HIDDEN' name=\"USER_ID\" value=\"" . $userID . "\" />";
		
		$output .= "<table>\n";
		$output .= "<tr><td class=\"gvcol_1 gvcol_key\">New Password:</td><td class=\"gvcol_2 gvcol_val\">";
		$output .= "<input type=\"password\" name=\"newPassword1\">";
		$output .= "</td></tr>";
		$output .= "<tr><td class=\"gvcol_1 gvcol_key\">Confirm New Password:</td><td class=\"gvcol_2 gvcol_val\">";
		$output .= "<input type=\"password\" name=\"newPassword2\">";
		$output .= "</td></tr>";
		
		$output .= "<tr><td colspan=2 class=\"gvcol_1 gvcol_submit\">";
		$output .= "<input type='submit' name=\"passwordUpdate\" value=\"Update Password\">";
		$output .= "</td></tr>";

		$output .= "</table></form>";
		if (get_option( 'vtm_user_set_image', '1'  == '1')) {
			$output .= "</div><div class='vtmext_section vtmprofile'>";
			$output .= "<h4>Upload a Portrait image</h4>";
			$output .= "Enter a web address to a profile image: <form id='portrait_set' method='post'>";
			$output .= "	<input type='text' name='vtm_portrait_set' value='" . wp_kses($mycharacter->portrait, "data") . "' size=60 />";
			$output .= "	<input id='set_vtm_portrait' name='set_vtm_portrait' type='submit' value='Save' />";
			$output .= "</form>";
			if (get_option( 'vtm_user_upload_image', '0'  == '1')) {
				$output .= "<p>OR</p>";
				$output .= "Upload an profile image: <form id='portrait_upload' method='post' action='#' enctype='multipart/form-data'>";
				$output .= "	<input type='file' name='vtm_portrait' id='vtm_portrait' />";
				$output .= wp_nonce_field( 'vtm_portrait', 'vtm_portrait_nonce' );
				$output .= "	<input id='submit_vtm_portrait' name='submit_vtm_portrait' type='submit' value='Upload' />";
				$output .= "</form>";
			}
			$output .= "<p>OR</p>";
			$output .= "<form id='portrait_clear' method='post'>";
			$output .= "	<input id='clear_vtm_portrait' name='clear_vtm_portrait' type='submit' value='Clear image' />";
			$output .= "</form>";
		}
		$output .= "</div><div class='vtmext_section vtmprofile'>";
		$output .= "<h4>Update Newsletter Settings:</h4>";
		$output .= "<form name='NEWLETTER_UPDATE_FORM' method='post'>";
		$output .= "<input type='radio' id='news_true' name='vtm_news_optin' value='Y' " .
			checked($mycharacter->newsletter, 'Y', false) . "/><label for='news_true'>I want the email newsletter</label><br />";
		$output .= "<input type='radio' id='news_false' name='vtm_news_optin' value='N' " .
			checked($mycharacter->newsletter, 'N', false) . "/><label for='news_false'>I do not want the email newsletter</label><br />";
		$output .= "<input type='submit' name=\"newsletterUpdate\" value=\"Update Newsletter\">";
		$output .= "</form>";
		
		// Update Quote
		if (get_option('vtm_user_set_quote', '0') == '1') {
			$output .= "</div><div class='vtmext_section vtmprofile'>";
			$output .= "<h4>Set a Character Quote</h4>\n";
			$output .= "<form id='quote_set' method='post'>";
			$output .= "    <textarea name='charHarpyQuote' rows='5' cols='50'>" . vtm_formatOutput($mycharacter->quote, 1) . "</textarea>";
			$output .= "	<input id='set_vtm_quote' name='set_vtm_quote' type='submit' value='Save' />";
			$output .= "</form>";
		}
		
		// Pronouna
		$output .= "</div><div class='vtmext_section vtmprofile'>";
		$output .= "<h4>Set Character Pronouns</h4>\n";
		$output .= "<form id='set_pronouns' method='post'>";
		$output .= "<input type='text' size=20 maxlength=20 name=\"Pronouns\" value=\"" . vtm_formatOutput($mycharacter->pronouns) . "\">";
		$output .= "	<input id='set_vtm_pronouns' name='set_vtm_pronouns' type='submit' value='Save' />";
		$output .= "</form>";


	}
	
	return $output;
}

?>