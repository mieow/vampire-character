<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

//https://docs.google.com/document/d/1aDxOsXW4sSerla6n0FakxMsbVhneGLrmy0ip1jHz9VE/edit?tab=t.0#heading=h.qcsspfln56cs

/* ---
    settings:
    * URL to [more info]
    * Game includes PvP
    * Game includes adult themes

*/
function vtm_consent_content_filter($content) {

	if (get_option( 'vtm_feature_consent', '0' ) == 1 && is_page(vtm_get_stlink_page('editConsent'))) {
		if (is_user_logged_in())
			$content .= vtm_get_consent_content();
		else
			$content .= "<p>You must be logged in to view the consent form</p>\n";
	}
	return $content;
}

add_filter( 'the_content', 'vtm_consent_content_filter' );

function vtm_get_consent_content() {
	global $wpdb;
	global $vtmglobal;

	$content = "";

    if (get_option( 'vtm_feature_consent', '0' ) == 0)
        return "Consent form is disabled";

	# does the logged in user have permission
	# to view any info here?
	# st, player admin and the person whose form it is

	$character = vtm_establishCharacter('');
	$characterID = vtm_establishCharacterID($character);
	$playerID = vtm_establishPlayerID($character);

	$cando = vtm_canDo($character, 'vtm_edit_character');
	if (!$cando && !current_user_can('vtm_edit_player')) 
		return "You do not have permission to view this page\n";

	$nonce = $_POST['vtm_consent_nonce'] ?? null;

	$message = '';
	if (isset($_POST['save_consent'])) {
		if (
			is_string($nonce)
			&& wp_verify_nonce(sanitize_text_field(wp_unslash($nonce)), 'vtm_consent')
		) {
			$consentData = array(
				'PVPOK' => is_string($_POST['vtmcbpvpok'] ?? null) && sanitize_text_field(wp_unslash($_POST['vtmcbpvpok'])) === 'on' ? 'Y' : 'N',
				'PVPMOREINFO' => is_string($_POST['vtmcbpvpmore'] ?? null) && sanitize_text_field(wp_unslash($_POST['vtmcbpvpmore'])) === 'on' ? 'Y' : 'N',
				'THEMEOK' => is_string($_POST['vtmcbtheme'] ?? null) && sanitize_text_field(wp_unslash($_POST['vtmcbtheme'])) === 'on' ? 'Y' : 'N',
				'CONSENTOK' => is_string($_POST['vtmcbconseq'] ?? null) && sanitize_text_field(wp_unslash($_POST['vtmcbconseq'])) === 'on' ? 'Y' : 'N',
				'BOUNDOK' => is_string($_POST['vtmcbbound'] ?? null) && sanitize_text_field(wp_unslash($_POST['vtmcbbound'])) === 'on' ? 'Y' : 'N',
				'REDFLAG' => is_string($_POST['vtmredflag'] ?? null) ? sanitize_textarea_field(wp_unslash($_POST['vtmredflag'])) : '',
				'ORANGEFLAG' => is_string($_POST['vtmorangeflag'] ?? null) ? sanitize_textarea_field(wp_unslash($_POST['vtmorangeflag'])) : '',
			);
			if (!$playerID) {
				$message = '<p>Unable to save the consent form. No player is associated with this character.</p>';
			} else {
				$consentTable = VTM_TABLE_PREFIX . 'PLAYER_CONSENT';
				$consentID = $wpdb->get_var($wpdb->prepare(
					"SELECT ID FROM %i WHERE PLAYER_ID = %d",
					$consentTable,
					$playerID
				));

				if ($consentID) {
					$saveResult = $wpdb->update($consentTable, $consentData, array('ID' => (int) $consentID));
				} else {
					$consentData['PLAYER_ID'] = $playerID;
					$saveResult = $wpdb->insert($consentTable, $consentData);
				}

				$message = $saveResult === false
					? '<p>Unable to save the consent form. Please try again.</p>'
					: '<p>Consent form saved.</p>';
			}
		} else {
			$message = '<p>Consent form validation failed.</p>';
		}
	}

	$data = $wpdb->get_row($wpdb->prepare(
		"SELECT PVPOK, PVPMOREINFO, THEMEOK, CONSENTOK, BOUNDOK, REDFLAG, ORANGEFLAG FROM %i WHERE PLAYER_ID = %d",
		VTM_TABLE_PREFIX . 'PLAYER_CONSENT',
		$playerID
	));

	$content .= "<div class='vtm_consent'><form name='consentform' method='post'>\n";
	$content .= wp_nonce_field( 'vtm_consent', 'vtm_consent_nonce', true, false );
	$content .= $message;
	$content .= "<h2>Consent form</h2>\n";
	if (get_option( 'vtm_consent_url', '' ) != '') {
		$content .= "<p>Additional information can be found ";
		$url = esc_url(get_option( 'vtm_consent_url', '' ));
		$content .= "<a href='$url'>here</a>.";
		$content .= "</p>";
	}

	// Player character conflict
	// Choose one:
	// 		o I accept the normal scope of player character conflict.
	// 		o I want to discuss restrictions on player character conflict with the Admin and Storyteller teams.
	if (get_option( 'vtm_consent_pvp', '' ) != '') {
		$content .= "<h2>Player character conflict</h2>";
		$content .= "<p>Please confirm:</p>\n<p>";
		$content .= "<input type='checkbox' name='vtmcbpvpok' value='on' id='vtmcbpvpok' " . checked($data && $data->PVPOK === 'Y', true, false) . "> <label for='vtmcbpvpok'>I accept the normal scope of player character conflict</label><br />\n";
		$content .= "<input type='checkbox' name='vtmcbpvpmore' value='on' id='vtmcbpvpmore' " . checked($data && $data->PVPMOREINFO === 'Y', true, false) . "> <label for='vtmcbpvpmore'>I want to discuss restrictions on player character conflict with the Admin and Storyteller teams</label><br />\n";
		$content .= "</p>";
	}

	// Personal content boundaries
	// Do not include
	// List any specific subjects that must not be included in plots, order responses or scenes directed at you.
	// Handle carefully
	// List any subjects that may be included but should be handled without graphic detail, off-screen or with advance warning.
	//
	$content .= "<h2>Consent and Acknowledgement</h2>";
	$content .= "<p>Please confirm:</p>\n<p>";
	$content .= "<input type='checkbox' name='vtmcbtheme' value='on' id='vtmcbtheme' " . checked($data && $data->THEMEOK === 'Y', true, false) . "> <label for='vtmcbtheme'>I understand and accept the general themes and content of this game</label><br />\n";
	$content .= "<input type='checkbox' name='vtmcbconseq' value='on' id='vtmcbconseq' " . checked($data && $data->CONSENTOK === 'Y', true, false) . "> <label for='vtmcbconseq'>I understand that my character may face serious in-character consequences</label><br />\n";
	$content .= "<input type='checkbox' name='vtmcbbound' value='on' id='vtmcbbound' " . checked($data && $data->BOUNDOK === 'Y', true, false) . "> <label for='vtmcbbound'>I understand that this form records additional personal boundaries and does not change the general nature of the game (see below)</label><br />\n";
	$content .= "</p>";
	$content .= "<h2>Personal content boundaries</h2>\n";
	$content .= "<h3>Do Not Include</h3>\n";
	$content .= "<label for='vtmredflag'>List any specific subjects that must not be included in plots, order responses or scenes directed at you.</label><br />\n";
	$content .= "<textarea id='vtmredflag' name='vtmredflag' rows='5' cols='100' maxlength='65535'>";
	$content .= esc_textarea($data->REDFLAG ?? '') . "</textarea>";
	$content .= "<h3>Handle Carefully</h3>\n";
	$content .= "<label for='vtmorangeflag'>List any subjects that may be included but should be handled without graphic detail, off-screen or with advance warning.</label><br />\n";
	$content .= "<textarea id='vtmorangeflag' name='vtmorangeflag' rows='5' cols='100' maxlength='65535'>";
	$content .= esc_textarea($data->ORANGEFLAG ?? '') . "</textarea>";

	$content .= "<input type='submit' name='save_consent' value='Submit' />\n";
	$content .= "</form></div>";
	return $content;

}
?>