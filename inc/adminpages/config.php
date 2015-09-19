<?php


function vtm_character_config() {
	global $wpdb;

	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
			
	?>
	<div class="wrap">
		<h2>Configuration</h2>
		<div class="gvadmin_nav">
			<ul>
				<li><?php echo vtm_get_tablink('general',   'General'); ?></li>
				<li><?php echo vtm_get_tablink('pagelinks', 'Page Links'); ?></li>
				<li><?php echo vtm_get_tablink('profile',   'Profile'); ?></li>
				<li><?php if (get_option( 'vtm_feature_maps', '0' ) == 1)  echo vtm_get_tablink('maps', 'Map Options'); ?></li>
				<li><?php echo vtm_get_tablink('chargen',   'Character Generation'); ?></li>
				<li><?php echo vtm_get_tablink('skinning',  'Skinning'); ?></li>
				<li><?php if (get_option( 'vtm_feature_email', '0' ) == 1) echo vtm_get_tablink('email', 'Email Options'); ?></li>
				<li><?php if (get_option( 'vtm_feature_pm', '0' ) == 1)    echo vtm_get_tablink('pm', 'Messaging'); ?></li>
				<li><?php echo vtm_get_tablink('database',  'Database'); ?></li>
				<li><?php echo vtm_get_tablink('features',  'Features'); ?></li>
			</ul>
		</div>
		<div class="gvadmin_content">
		<?php
		
		$tabselect = isset($_REQUEST['tab']) ? $_REQUEST['tab'] : '';
		
		switch ($tabselect) {
			case 'general':
				vtm_render_config_general();
				break;
			case 'pagelinks':
				vtm_render_config_pagelinks();
				break;
			case 'maps':
				vtm_render_config_maps();
				break;
			case 'chargen':
				vtm_render_config_chargen();
				break;
			case 'skinning':
				vtm_render_config_skinning();
				break;
			case 'features':
				vtm_render_config_features();
				break;
			case 'email':
				vtm_render_config_email();
				break;
			case 'pm':
				vtm_render_config_pm();
				break;
			case 'profile':
				vtm_render_config_profile();
				break;
			case 'database':
				vtm_render_config_database();
				break;
			default:
				vtm_render_config_general();
		}
		
		?>
		</div>
	</div>
	<?php
}

function vtm_render_config_general() {	
	global $wpdb;
	
		?>
		<h3>General Options</h3>
		<?php 
		
			if (isset($_REQUEST['save_options'])) {
			
				$sql = "SELECT ID FROM " . VTM_TABLE_PREFIX . "CONFIG ORDER BY ID";
				$configid = $wpdb->get_var($sql);
			
				$wpdb->show_errors();
				$dataarray = array (
					'PLACEHOLDER_IMAGE' => $_REQUEST['placeholder'],
					//'ANDROID_LINK' => $_REQUEST['androidlink'],
					'HOME_DOMAIN_ID' => $_REQUEST['homedomain'],
					'HOME_SECT_ID'   => $_REQUEST['homesect'],
					'ASSIGN_XP_BY_PLAYER' => $_REQUEST['assignxp'],
					'USE_NATURE_DEMEANOUR' => $_REQUEST['usenature'],
					'DISPLAY_BACKGROUND_IN_PROFILE' => $_REQUEST['displaybg'],
					'DEFAULT_GENERATION_ID' => $_REQUEST['generation'],
				);
				
				$result = $wpdb->update(VTM_TABLE_PREFIX . "CONFIG",
					$dataarray,
					array (
						'ID' => $configid
					),
					array('%s', '%s', '%d', '%d', '%s', '%s', '%d', '%d')
				);		
				
				if ($result) 
					echo "<p style='color:green'>Updated configuration options</p>";
				else if ($result === 0) 
					echo "<p style='color:orange'>No updates made to options</p>";
				else {
					$wpdb->print_error();
					echo "<p style='color:red'>Could not update options</p>";
				}
				
			}

			else {
			
			$sql = "select * from " . VTM_TABLE_PREFIX . "CONFIG;";
			$options = $wpdb->get_results($sql);
		?>

		<form id='options_form' method='post'>
			<table>
			<!---
			<tr>
				<td>URL to Android XML Output</td>
				<td><input type="text" name="androidlink" value="<?php print $options[0]->ANDROID_LINK; ?>" size=60 /></td>
				<td>Page where android app connects to for character sheet output.</td>
			</tr>
			--->
			<tr>
				<td>URL to Profile Placeholder image</td>
				<td><input type="text" name="placeholder" value="<?php print $options[0]->PLACEHOLDER_IMAGE; ?>" size=60 /></td>
				<td>This image is used in place of a character portrait on the profile page.</td>
			</tr><tr>
				<td>Home City</td>
				<td>
				<select name="homedomain">
					<?php
					foreach (vtm_get_domains() as $domain) {
						echo '<option value="' . $domain->ID . '" ';
						selected( $options[0]->HOME_DOMAIN_ID, $domain->ID );
						echo '>' . vtm_formatOutput($domain->NAME) . '</option>';
					}
					?>
				</select>
				</td>
				<td>Select which in-character city or location your game is based in</td>
			</tr><tr>
				<td>Default Affiliation</td>
				<td>
				<select name="homesect">
					<?php
					foreach (vtm_get_sects() as $sect) {
						echo '<option value="' . $sect->ID . '" ';
						selected( $options[0]->HOME_SECT_ID, $sect->ID );
						echo '>' . vtm_formatOutput($sect->NAME) . '</option>';
					}
					?>
				</select>
				</td>
				<td>Select what is the default affiliation for new characters</td>
			</tr><tr>
				<td>Assign XP By</td>
				<td>
				<input type="radio" name="assignxp" value="Y" <?php if ($options[0]->ASSIGN_XP_BY_PLAYER == 'Y') print "checked"; ?>>Player
				<input type="radio" name="assignxp" value="N" <?php if ($options[0]->ASSIGN_XP_BY_PLAYER == 'N') print "checked"; ?>>Character	
				<td>Experience can be assigned to players or to characters</td>
			</tr><tr>
				<td>Use Nature/Demeanour</td>
				<td>
				<input type="radio" name="usenature" value="Y" <?php if ($options[0]->USE_NATURE_DEMEANOUR == 'Y') print "checked"; ?>>Yes
				<input type="radio" name="usenature" value="N" <?php if ($options[0]->USE_NATURE_DEMEANOUR == 'N') print "checked"; ?>>No	
				<td>Enter and Display Nature and Demeanours for characters.</td>
			</tr><tr>
				<td>Display a Character Background on the Character Profile</td>
				<td>
				<select name="displaybg">
					<option value="0">Not displayed</option>
					<?php
					foreach (vtm_get_backgrounds() as $bg) {
						echo '<option value="' . $bg->ID . '" ';
						selected( $options[0]->DISPLAY_BACKGROUND_IN_PROFILE, $bg->ID );
						echo '>' . vtm_formatOutput($bg->NAME) , '</option>';
					}
					?>
				</select>
				<td>Specify if a background (e.g. Status) is displayed on the character profile.</td>
			</tr><tr>
				<td>Default Character Generation</td>
				<td>
				<select name="generation">
					<?php
					foreach (vtm_get_generations() as $gen) {
						echo '<option value="' . $gen->ID . '" ';
						selected( $options[0]->DEFAULT_GENERATION_ID, $gen->ID );
						echo '>' . vtm_formatOutput($gen->NAME) . '</option>';
					}
					?>
				</select>
				<td>What is the base generation for new characters.</td>
			</tr>
			</table>
			<input type="submit" name="save_options" class="button-primary" value="Save Options" />
			
		</form>
		
	<?php 
	}
}
function vtm_render_config_pagelinks() {	
	global $wpdb;

		?><h3>Page Links</h3>
		<?php 
			if (isset($_REQUEST['save_st_links'])) {
				for ($i=0; $i<$_REQUEST['linecount']; $i++) {
					if ($_REQUEST['selectpage' . $i] == "vtmnewpage") {
					
						//check if page with name $_REQUEST['value' . $i] exists 
						if (isset($_REQUEST['link' . $i]) && $_REQUEST['link' . $i] != "") {
							$my_page = array(
								  'post_status'           => 'publish', 
								  'post_type'             => 'page',
								  'comment_status'		  => 'closed',
								  'post_name'			  => $_REQUEST['value' . $i],
								  'post_title'			  => $_REQUEST['link' . $i]
							);

							// Insert the post into the database
							$pageid = wp_insert_post( $my_page );
						} else {
							$pageid = 0;
						}
					}
					else
						$pageid = $_REQUEST['selectpage' . $i];
						
					if ($pageid > 0) {
						$dataarray = array (
							'ORDERING' => $_REQUEST['order' . $i],
							'WP_PAGE_ID' => $pageid
						);
						//print_r($dataarray);
						
						$result = $wpdb->update(VTM_TABLE_PREFIX . "ST_LINK",
							$dataarray,
							array (
								'ID' => $_REQUEST['id' . $i]
							)
						);
						
						if ($result) 
							echo "<p style='color:green'>Updated {$_REQUEST['value' . $i]}</p>";
						else if ($result === 0) 
							echo "<p style='color:orange'>No updates made to {$_REQUEST['value' . $i]}</p>";
						else {
							$wpdb->print_error();
							echo "<p style='color:red'>Could not update {$_REQUEST['value' . $i]} ({$_REQUEST['id' . $i]})</p>";
						}
					}
			
				}
			}
			$sql = "select * from " . VTM_TABLE_PREFIX . "ST_LINK;";
			$stlinks = $wpdb->get_results($sql);
			
			$args = array(
				'sort_order' => 'ASC',
				'sort_column' => 'post_title',
				'hierarchical' => 0,
				'exclude' => '',
				'include' => '',
				'meta_key' => '',
				'meta_value' => '',
				'authors' => '',
				'child_of' => 0,
				'parent' => -1,
				'exclude_tree' => '',
				'number' => '',
				'offset' => 0,
				'post_type' => 'page',
				'post_status' => 'publish'
			); 
			$pages = get_pages($args);
			$pagetitles = array();
			foreach ( $pages as $page ) {
				$pagetitles[$page->ID] = $page->post_title;
			}							
		?>
		
		<form id='ST_Links_form' method='post'>
			<input type="hidden" name="linecount" value="<?php print count($stlinks); ?>" />
			<table>
				<tr><th>List Order</th><th>Name</th><th>Description</th><th>Select Page</th><th>New Page name</th></tr>
			<?php
				$i = 0;
				foreach ($stlinks as $stlink) {
					
					?>
					<tr>
						<td><input type="hidden" name="id<?php print $i ?>" value="<?php print $stlink->ID; ?>" />
							<input type="text" name="order<?php print $i; ?>" value="<?php print $stlink->ORDERING; ?>" size=5 /></td>
						<td><input type="hidden" name="value<?php print $i ?>" value="<?php print $stlink->VALUE; ?>" />
							<?php print $stlink->VALUE; ?></td>
						<td><input type="hidden" name="desc<?php print $i ?>" value="<?php print vtm_formatOutput($stlink->DESCRIPTION); ?>" />
							<?php print vtm_formatOutput($stlink->DESCRIPTION); ?></td>
						<td>
							<select name="selectpage<?php print $i; ?>">
							<option value='vtmnewpage'>[New Page]</option>
							<?php
								$match = 0;
								foreach ( $pagetitles as $pageid => $pagetitle ) {
									echo "<option value='$pageid' ";
									selected($pageid, $stlink->WP_PAGE_ID);
									echo ">" . vtm_formatOutput($pagetitle) . "</option>";
								}								
							?>
							</select>
						</td>
						<td><input type="text" name="link<?php print $i; ?>" value="" /></td>
					</tr>
					<?php
					$i++;
				}
			?>
			</table>
			<input type="submit" name="save_st_links" class="button-primary" value="Save Links" />
		</form>

	<?php 
}
function vtm_render_config_maps() {	
	global $wpdb;

		?><h3>Feeding Map Options</h3>
		<form method="post" action="options.php">
			<?php
			settings_fields( 'feedingmap_options_group' );
			do_settings_sections('feedingmap_options_group');
			?>	
			
			<table>
			<tr>
				<td><label>Google Maps API Key:</label></td>
				<td><input type="text" name="feedingmap_google_api" value="<?php echo get_option('feedingmap_google_api'); ?>" size=60 /></td>
			</tr>
			<tr>
				<td><label>Centre Point, Latitude:</label></td>
				<td><input type="text" name="feedingmap_centre_lat" value="<?php echo get_option('feedingmap_centre_lat'); ?>" style="width:120px;" /></td>
			</tr>
			<tr>
				<td><label>Centre Point, Longitude:</label></td>
				<td><input type="text" name="feedingmap_centre_long" value="<?php echo get_option('feedingmap_centre_long'); ?>" style="width:120px;" /></td>
			</tr>
			<tr>
				<td><label>Map Zoom:</label></td>
				<td><input type="number" name="feedingmap_zoom" value="<?php echo get_option('feedingmap_zoom'); ?>" style="width:50px;" /></td>
			</tr>
			<tr>
				<td><label>Map Type:</label></td>
				<td>
					<select name="feedingmap_map_type">
						<option value="ROADMAP" <?php selected(get_option('feedingmap_map_type'),"ROADMAP"); ?>>Roadmap</option>
						<option value="SATELLITE" <?php selected(get_option('feedingmap_map_type'),"SATELLITE"); ?>>Satellite</option>
						<option value="HYBRID" <?php selected(get_option('feedingmap_map_type'),"HYBRID"); ?>>Hybrid</option>
						<option value="TERRAIN" <?php selected(get_option('feedingmap_map_type'),"TERRAIN"); ?>>Terrain</option>
					</select>
				</td>
			</tr>
			</table>
			<?php submit_button("Save Map Options", "primary", "save_map_button"); ?>
		
		</form>

		
	<?php 
}
function vtm_render_config_chargen() {	
	global $wpdb;

		?><h3>Character Generation Options</h3>
		<form method="post" action="options.php">
		<?php
		
		settings_fields( 'vtm_chargen_options_group' );
		do_settings_sections('vtm_chargen_options_group');
		?>

		<table>
		<tr>
			<td><input type="checkbox" name="vtm_chargen_mustbeloggedin" value="1" <?php checked( '1', get_option( 'vtm_chargen_mustbeloggedin', '0' ) ); ?> /></td>
			<td><label>User must be logged in</label></td>
		</tr>
		<tr>
			<td><input type="checkbox" name="vtm_chargen_showsecondaries" value="1" <?php checked( '1', get_option( 'vtm_chargen_showsecondaries', '0' ) ); ?> /></td>
			<td><label>Show secondary Abilities in Abilities Character Generation Step</label></td>
		</tr>
		</table>
		<?php submit_button("Save Character Generation Options", "primary", "save_chargen_button"); ?>
		</form>
		
	<?php 
}
function vtm_render_config_email() {	
	global $wpdb;

	if (isset($_REQUEST['send_email_button'])) {
		//print_r($_REQUEST);
		vtm_test_email($_REQUEST['vtm_test_address']);
	}
	
	?><h3>Email Options</h3>
	<form method="post" action="options.php">
	<?php
	
	settings_fields( 'vtm_email_options_group' );
	do_settings_sections('vtm_email_options_group');
	
	$emailtag  = get_option( 'vtm_emailtag',        get_option( 'vtm_chargen_emailtag' ) );
	$replyname = get_option( 'vtm_replyto_name',    get_option( 'vtm_chargen_email_from_name', 'The Storytellers'));
	$replyto   = get_option( 'vtm_replyto_address', get_option( 'vtm_chargen_email_from_address', get_bloginfo('admin_email') ) );
	$method    = get_option( 'vtm_method',          'mail' );
	$debug     = get_option( 'vtm_email_debug',     'false' );

	$smtphost   = get_option( 'vtm_smtp_host',     '' );
	$smtpport   = get_option( 'vtm_smtp_port',     '25' );
	$smtpuser   = get_option( 'vtm_smtp_username', '' );
	$smtpauth   = get_option( 'vtm_smtp_auth',     'true' );
	$smtpsecure = get_option( 'vtm_smtp_secure',   'ssl' );
	$smtppw     = get_option( 'vtm_smtp_pw',       '' );
	?>

	<table>
	<tr>
		<td><label>Tag to add to the start of notification email subject: </label></td>
		<td><input type="text" name="vtm_emailtag" value="<?php echo $emailtag; ?>" /></td>
	</tr>
	<tr>
		<td><label>From name of notification emails: </label></td>
		<td><input type="text" name="vtm_replyto_name" value="<?php echo $replyname; ?>" /></td>
	</tr>
	<tr>
		<td><label>Reply-to address of notification emails: </label></td>
		<td><input type="text" name="vtm_replyto_address" value="<?php echo $replyto; ?>" /></td>
	</tr>
	<tr>
		<td><label>Email Debug: </label></td>
		<td>
			<input type='radio' id='debug_true' name='vtm_email_debug' value='true' <?php echo checked($debug, 'true', false); ?> /><label for='debug_true'>Yes</label>
			<input type='radio' id='debug_false' name='vtm_email_debug' value='false' <?php echo checked($debug, 'false', false); ?> /><label for='debug_false'>No</label>
		</td>
	</tr>
	<tr>
		<td><label>Email method: </label></td>
		<td>
			<input type='radio' id='method_mail' name='vtm_method' value='mail' <?php echo checked($method, 'mail', false); ?> /><label for='method_mail'>Mail (default)</label>
			<input type='radio' id='method_smpt' name='vtm_method' value='smtp' <?php echo checked($method, 'smtp', false); ?> /><label for='method_smpt'>SMTP</label>
		</td>
	</tr>
	</table>
	<?php 
	if ($method == 'smtp') {		
		?>
	<h3>SMTP Settings</h3>
	<p>Using SMTP is useful if emails from the site are being spam-filtered or are not
	reaching the end user.</p>
	<p>Note 1: These setting will affect all emails sent by Wordpress.  SMTP support
	is basic so if you have any issues then we recommend installing a stand-alone SMTP
	plugin instead.</p>
	<p>Note 2: The password is stored as plain text in the Wordpress database so be aware
	that this is a risk to security.  We recommend creating a separate email address for
	the purposes of sending notification emails so that your private email accounts
	won't be compromised.</p>
	<table>
	<tr>
		<td><label>SMTP Host: </label></td>
		<td><input type="text" name="vtm_smtp_host" value="<?php echo $smtphost; ?>" /></td>
	</tr>
	<tr>
		<td><label>SMTP port: </label></td>
		<td><input type="text" name="vtm_smtp_port" value="<?php echo $smtpport; ?>" /></td>
	</tr>
	<tr>
		<td><label>Email username: </label></td>
		<td><input type="text" name="vtm_smtp_username" value="<?php echo $smtpuser; ?>" /></td>
	</tr>
	<tr>
		<td><label>Email password: </label></td>
		<td><input type="password" name="vtm_smtp_pw" value="<?php echo $smtppw; ?>" /></td>
	</tr>
	<tr>
		<td><label>Email method: </label></td>
		<td>
			<input type='radio' id='secure1' name='vtm_smtp_secure' value='ssl' <?php echo checked($smtpsecure, 'ssl', false); ?> /><label for='secure1'>SSL (default)</label>
			<input type='radio' id='secure2' name='vtm_smtp_secure' value='tls' <?php echo checked($smtpsecure, 'tls', false); ?> /><label for='secure2'>TLS</label>
			<input type='radio' id='secure3' name='vtm_smtp_secure' value='none' <?php echo checked($smtpsecure, 'none', false); ?> /><label for='secure3'>None</label>
		</td>
	</tr>
	<tr>
		<td><label>SMTP Authentication: </label></td>
		<td>
			<input type='radio' id='auth1' name='vtm_smtp_auth' value='true' <?php echo checked($smtpauth, 'true', false); ?> />
			<label for='auth1'>Yes</label>
			<input type='radio' id='auth2' name='vtm_smtp_auth' value='false' <?php echo checked($smtpauth, 'false', false); ?> />
			<label for='auth2'>No</label>
		</td>
	</tr>
	</table>
		<?php
	}
	?>
	<?php submit_button("Save Email Options", "primary", "save_email_button"); ?>
	</form>
	
	<form id='options_form' method='post'>
	<input type="text" name="vtm_test_address" value="<?php echo $replyto; ?>" />
	<?php submit_button("Send test email", "primary", "send_email_button"); ?>
	</form>
			
	<?php 
}
function vtm_render_config_skinning() {	
	global $wpdb;
	
		?><h3>Skinning</h3>
		<form method="post" action="options.php">
		<?php
		
		settings_fields( 'vtm_options_group' );
		do_settings_sections('vtm_options_group');
		
		if (get_option( 'vtm_feature_reports', '0' ) == 1) {
		?>
		<h4>Report Options</h4>
		<table>
			<tr>
				<td>Extra columns for sign-in report (comma-separated):</td>
				<td><input type="text" name="vtm_signin_columns" value="<?php echo get_option('vtm_signin_columns'); ?>" /></td>
			</tr>
		</table>
		<?php } 
		
		if (get_option( 'vtm_feature_news', '0' ) == 1) {
		?>
		<h4>Newsletter Options</h4>
		<table>
			<tr>
				<td>Add Newsletter posts to Wordpress blogroll:</td>
				<td><input type="checkbox" name="vtm_news_blogroll" value="1" <?php checked( '1', get_option( 'vtm_news_blogroll', '0' ) ); ?> /></td>
			</tr>
		</table>
		<?php } ?>
		
		<h4>Web Page Layout</h4>
		<table>
			<tr>
				<!---
				<td>Number of columns:</td>
				<td>
					<input type="radio" name="vtm_web_columns" value="1" <?php if (get_option('vtm_web_columns', 3) == 1) print "checked"; ?>>1 Column
					<input type="radio" name="vtm_web_columns" value="3" <?php if (get_option('vtm_web_columns', 3) == 3) print "checked"; ?>>3 Columns	
				</td>
				--->
				<td>Page width:</td>
				<td>
					<input type="radio" name="vtm_web_pagewidth" value="narrow" <?php if (get_option('vtm_web_pagewidth', 'wide') == 'narrow') print "checked"; ?> />
					narrow (character sheet has 1 column and normal dots)<br />
					<input type="radio" name="vtm_web_pagewidth" value="medium" <?php if (get_option('vtm_web_pagewidth', 'wide') == 'medium') print "checked"; ?>>
					medium (character sheet has 3 columns and small dots)<br />
					<input type="radio" name="vtm_web_pagewidth" value="wide"   <?php if (get_option('vtm_web_pagewidth', 'wide') == 'wide') print "checked"; ?>>
					wide (character sheet has 3 columns and normal dots)<br />
				</td>
			</tr>
		</table>
		
		<h4>Web Page Graphics</h4>
		<?php 
			$drawbgcolour = get_option('vtm_view_bgcolour', '#000000');
			$drawborder   = get_option('vtm_view_dotlinewidth', '2');
			$dot1colour   = get_option('vtm_dot1colour', get_option('vtm_view_dotcolour', '#FFFFFF'));
			$dot2colour   = get_option('vtm_dot2colour', get_option('vtm_xp_dotcolour',   '#FF0000'));
			$dot3colour   = get_option('vtm_dot3colour', get_option('vtm_pend_dotcolour', '#00FF00'));
			$dot4colour   = get_option('vtm_dot4colour', get_option('vtm_chargen_freebie', '#0000FF'));
		?>
		
		<table>
			<tr>
				<td>Background Colour (#RRGGBB)</td><td><input type="color" name="vtm_view_bgcolour" value="<?php echo $drawbgcolour; ?>" /></td>
				<td>Dot/Box Line Width (mm)</td><td><input type="text" name="vtm_view_dotlinewidth" value="<?php echo $drawborder; ?>" size=4 /></td>
			</tr><tr>
				<td>Dot1 colour (#RRGGBB)</td><td><input type="color" name="vtm_dot1colour" value="<?php echo $dot1colour; ?>" /></td>
				<td>Dot2 Colour (#RRGGBB)</td><td><input type="color" name="vtm_dot2colour" value="<?php echo $dot2colour; ?>" /></td>
			</tr><tr>
				<td>Dot3 Colour (#RRGGBB)</td><td><input type="color" name="vtm_dot3colour" value="<?php echo $dot3colour; ?>" /></td>
				<td>Dot4 Colour (#RRGGBB)</td><td><input type="color" name="vtm_dot4colour" value="<?php echo $dot4colour; ?>" /></td>
			</tr>
		</table>
		<table>
		<tr>
		<td><img alt="empty dot1" width=16 src='<?php echo VTM_PLUGIN_URL . '/images/dot1empty.jpg'; ?>'></td>
		<td><img alt="full dot1"  width=16 src='<?php echo VTM_PLUGIN_URL . '/images/dot1full.jpg'; ?>'></td>
		<td><img alt="dot2"       width=16 src='<?php echo VTM_PLUGIN_URL . '/images/dot2.jpg'; ?>'></td>
		<td><img alt="dot3"       width=16 src='<?php echo VTM_PLUGIN_URL . '/images/dot3.jpg'; ?>'></td>
		<td><img alt="dot4"       width=16 src='<?php echo VTM_PLUGIN_URL . '/images/dot4.jpg'; ?>'></td>
		<td><img alt="crossclear" width=16 src='<?php echo VTM_PLUGIN_URL . '/images/crossclear.jpg'; ?>'></td>
		<td><img alt="box"        width=16 src='<?php echo VTM_PLUGIN_URL . '/images/webbox.jpg'; ?>'></td>
		<td><img alt="checked"    width=16 src='<?php echo VTM_PLUGIN_URL . '/images/check.jpg'; ?>'></td>
		<td><img alt="spacer"     width=16 src='<?php echo VTM_PLUGIN_URL . '/images/spacer.jpg'; ?>'></td>
		<td><img alt="fill"       width=16 src='<?php echo VTM_PLUGIN_URL . '/images/fill.jpg'; ?>'></td>
		<td><img alt="arrow"      width=16 src='<?php echo VTM_PLUGIN_URL . '/images/arrowright.jpg'; ?>'></td>
		<td><img alt="mail"       width=16 src='<?php echo VTM_PLUGIN_URL . '/images/mail.jpg'; ?>'></td>
		</tr>
		</table>

		<h4>PDF Character Sheet Options</h4>
		<table>
			<tr>
				<td>Character Sheet Title</td><td><input type="text" name="vtm_pdf_title" value="<?php echo get_option('vtm_pdf_title', 'Character Sheet'); ?>" size=30 /></td>
				<td>Title Font</td><td><select name="vtm_pdf_titlefont">
					<option value="Arial"     <?php if ('Arial'     == get_option('vtm_pdf_titlefont')) echo "selected='selected'"; ?>>Arial</option>
					<option value="Courier"   <?php if ('Courier'   == get_option('vtm_pdf_titlefont')) echo "selected='selected'"; ?>>Courier</option>
					<option value="Helvetica" <?php if ('Helvetica' == get_option('vtm_pdf_titlefont')) echo "selected='selected'"; ?>>Helvetica</option>
					<option value="Times"     <?php if ('Times'     == get_option('vtm_pdf_titlefont')) echo "selected='selected'"; ?>>Times New Roman</option>
					</select>
				</td>
				<td>Title Text Colour (#RRGGBB)</td><td><input type="color" name="vtm_pdf_titlecolour" value="<?php echo get_option('vtm_pdf_titlecolour', '#000000'); ?>" /></td>
			</tr>
			<tr>
				<td>Divider Line Colour (#RRGGBB)</td><td><input type="color" name="vtm_pdf_divcolour" value="<?php echo get_option('vtm_pdf_divcolour', '#000000'); ?>" /></td>
				<td>Divider Text Colour (#RRGGBB)</td><td><input type="color" name="vtm_pdf_divtextcolour" value="<?php echo get_option('vtm_pdf_divtextcolour', '#000000'); ?>" /></td>
				<td>Divider Line Width (mm)</td><td><input type="text" name="vtm_pdf_divlinewidth" value="<?php echo get_option('vtm_pdf_divlinewidth', '1'); ?>" size=4 /></td>
			</tr>
			<tr>
				<td>Character Sheet Footer</td><td><input type="text" name="vtm_pdf_footer" value="<?php echo get_option('vtm_pdf_footer'); ?>" size=30 /></td>
				<td>Dot/Box Colour (#RRGGBB)</td><td><input type="color" name="vtm_pdf_dotcolour" value="<?php echo get_option('vtm_pdf_dotcolour', '#000000'); ?>" /></td>
				<td>Dot/Box Line Width (mm)</td><td><input type="text" name="vtm_pdf_dotlinewidth" value="<?php echo get_option('vtm_pdf_dotlinewidth', '1'); ?>" size=4 /></td>
			</tr>
		</table>
		<table>
		<tr>
		<td><img alt="empty dot"  width=16 src='<?php echo VTM_PLUGIN_URL . '/images/emptydot.jpg'; ?>'></td>
		<td><img alt="full dot"  width=16 src='<?php echo VTM_PLUGIN_URL . '/images/fulldot.jpg'; ?>'></td>
		<td><img alt="xp dot"  width=16 src='<?php echo VTM_PLUGIN_URL . '/images/pdfxpdot.jpg'; ?>'></td>
		<td><img alt="box dot"  width=16 src='<?php echo VTM_PLUGIN_URL . '/images/box.jpg'; ?>'></td>
		<td><img alt="box2 dot" width=16 src='<?php echo VTM_PLUGIN_URL . '/images/boxcross1.jpg'; ?>'></td>
		<td><img alt="box3 dot" width=16 src='<?php echo VTM_PLUGIN_URL . '/images/boxcross2.jpg'; ?>'></td>
		<td><img alt="box4 dot" width=16 src='<?php echo VTM_PLUGIN_URL . '/images/boxcross3.jpg'; ?>'></td>
		</tr>
		</table>
		
		<?php submit_button("Save General Options", "primary", "save_general_button"); ?>
		</form>
		
		<?php
		
		// Webpage dots
		vtm_draw_dot("dot1empty", $dot1colour, $drawbgcolour, $drawborder, 0);
		vtm_draw_dot("dot1full",  $dot1colour, $drawbgcolour, $drawborder, 1);
		vtm_draw_box("crossclear", $dot1colour, $drawbgcolour, $drawborder, 2);
		vtm_draw_box("webbox", $dot1colour, $drawbgcolour, $drawborder, 0);
		vtm_draw_box("spacer", $drawbgcolour, $drawbgcolour, $drawborder, 0);
		vtm_draw_box("fill",   $dot2colour, $dot2colour, $drawborder, 0);
		vtm_draw_dot("dot2",   $dot2colour, $drawbgcolour, $drawborder, 1);
		vtm_draw_dot("dot3",   $dot3colour, $drawbgcolour, $drawborder, 1);
		vtm_draw_dot("dot4",   $dot4colour, $drawbgcolour, $drawborder, 1);
		vtm_draw_check("check", $dot1colour, $drawbgcolour, $drawborder);
		vtm_draw_arrow("arrowright", $dot1colour, $drawbgcolour, $drawborder * 2);
		//vtm_draw_mail("mail", $dot1colour, $drawbgcolour, $drawborder, 0);
		vtm_draw_mail("mail", $dot1colour, $drawbgcolour, 1, 0);
		
		// PDF dots
		$drawborder   = get_option('vtm_pdf_dotlinewidth', '3');
		$drawcolour   = get_option('vtm_pdf_dotcolour', '#000000');
		$drawbgcolour = '#FFFFFF';
		vtm_draw_dot("emptydot", $drawcolour, $drawbgcolour, $drawborder, 0);
		vtm_draw_dot("fulldot",  $drawcolour, $drawbgcolour, $drawborder, 1);
		vtm_draw_dot("pdfxpdot", $drawcolour, $drawbgcolour, $drawborder, 0, 1);
		
		vtm_draw_box("box", $drawcolour, $drawbgcolour, $drawborder, 0);
		vtm_draw_box("boxcross1", $drawcolour, $drawbgcolour, $drawborder, 1);
		vtm_draw_box("boxcross2", $drawcolour, $drawbgcolour, $drawborder, 2);
		vtm_draw_box("boxcross3", $drawcolour, $drawbgcolour, $drawborder, 3);
		
}

function vtm_draw_dot($name, $drawcolour, $drawbgcolour, $drawborder, $fill = 1, $filldot = 0) {

	if (class_exists('Imagick')) {
		$drawwidth    = 32;
		$drawheight   = 32;
		$drawmargin   = 1;
		$imagetype    = 'jpg';

		$image = new Imagick();

		$image->newImage($drawwidth, $drawheight, new ImagickPixel($drawbgcolour), $imagetype);
		$draw = new ImagickDraw();
		$draw->setStrokeColor($drawcolour);
		$draw->setStrokeWidth($drawborder);
		if ($fill)
			$draw->setFillColor($drawcolour);
		else
			$draw->setFillColor($drawbgcolour);
		$draw->circle( ceil($drawwidth / 2), ceil($drawheight / 2), ceil($drawwidth / 2), $drawborder + $drawmargin);
		
		if ($filldot) {
			$draw->setFillColor($drawcolour);
			$draw->circle( ceil($drawwidth / 2), ceil($drawheight / 2), ceil($drawwidth / 2), ceil($drawwidth / 4));
		}
		
		$image->drawImage($draw);
		$image->writeImage(VTM_CHARACTER_URL . "images/{$name}." . $imagetype);

		$image = "";
	}
}
function vtm_draw_box($name, $drawcolour, $drawbgcolour, $drawborder, $crosses) {
	if (class_exists('Imagick')) {
		$drawwidth    = 32;
		$drawheight   = 32;
		$drawmargin   = 1;
		$imagetype    = 'jpg';

		$image = new Imagick();
		
		$image->newImage($drawwidth, $drawheight, new ImagickPixel($drawbgcolour), $imagetype);
		$draw = new ImagickDraw();
		$draw->setStrokeColor($drawcolour);
		$draw->setStrokeWidth($drawborder);
		$draw->setFillColor($drawbgcolour);
		$draw->rectangle( $drawborder, $drawborder, $drawwidth - $drawborder - 1, $drawheight - $drawborder - 1);
		
		if ($crosses >= 1)
			$draw->line( $drawborder, $drawborder, $drawwidth - $drawborder - 1, $drawheight - $drawborder - 1);
		if ($crosses >= 2)
			$draw->line( $drawborder, $drawheight - $drawborder - 1, $drawwidth - $drawborder - 1, $drawborder);
		if ($crosses >= 3)
			$draw->line( $drawborder, $drawheight/2, $drawwidth - $drawborder - 1, $drawheight / 2);
		
		$image->drawImage($draw);
		$image->writeImage(VTM_CHARACTER_URL . "images/{$name}." . $imagetype);

		$image = "";
	}
	
}
function vtm_draw_mail($name, $drawcolour, $drawbgcolour, $drawborder, $crosses) {
	if (class_exists('Imagick')) {
		$drawwidth    = 15;
		$drawheight   = 10;
		$drawmargin   = 1;
		$imagetype    = 'jpg';

		$image = new Imagick();
		
		$image->newImage($drawwidth, $drawheight, new ImagickPixel($drawbgcolour), $imagetype);
		$draw = new ImagickDraw();
		$draw->setStrokeColor($drawcolour);
		$draw->setStrokeWidth($drawborder);
		$draw->setFillColor($drawbgcolour);
		$draw->rectangle( $drawborder, $drawborder, $drawwidth - $drawborder - 1, $drawheight - $drawborder - 1);
		
		$draw->line(	$drawborder,					$drawborder, 
						$drawwidth / 2,					$drawheight / 2);
		$draw->line( 	$drawwidth / 2,					$drawheight / 2, 
						$drawwidth - $drawborder - 1,	$drawborder);
		
		$image->drawImage($draw);
		$image->writeImage(VTM_CHARACTER_URL . "images/{$name}." . $imagetype);

		$image = "";
	}
	
}
function vtm_draw_check($name, $drawcolour, $drawbgcolour, $drawborder) {
	if (class_exists('Imagick')) {
		$drawwidth    = 32;
		$drawheight   = 32;
		$drawmargin   = 1;
		$imagetype    = 'jpg';

		$image = new Imagick();
		
		$image->newImage($drawwidth, $drawheight, new ImagickPixel($drawbgcolour), $imagetype);
		$draw = new ImagickDraw();
		$draw->setStrokeColor($drawcolour);
		$draw->setStrokeWidth($drawborder);
		$draw->setFillColor($drawbgcolour);
		$draw->rectangle( $drawborder, $drawborder, $drawwidth - $drawborder - 1, $drawheight - $drawborder - 1);
		
		$Lx = $drawborder;
		$Ty = $drawborder;
		$Rx = $drawheight - $drawborder - $drawmargin;
		$By = $drawwidth - $drawborder - $drawmargin;
		$MIDx = $drawwidth/2;
		$MIDy = $drawheight/2;
		$gap = $drawborder * 2;
		
		$draw->setStrokeWidth($drawborder * 2);
		$draw->line($Lx + $gap, $MIDy, 		$MIDx, $By - $gap);
		$draw->line($MIDx, $By - $gap,		$Rx - $gap, $Ty + $gap);
		
		$image->drawImage($draw);
		$image->writeImage(VTM_CHARACTER_URL . "images/{$name}." . $imagetype);

		$image = "";
	}
}
function vtm_draw_arrow($name, $drawcolour, $drawbgcolour, $drawborder) {
	if (class_exists('Imagick')) {
		$drawwidth    = 32;
		$drawheight   = 32;
		$drawmargin   = 1;
		$imagetype    = 'jpg';

		$image = new Imagick();
		
		$image->newImage($drawwidth, $drawheight, new ImagickPixel($drawbgcolour), $imagetype);
		$draw = new ImagickDraw();
		$draw->setStrokeColor($drawcolour);
		$draw->setStrokeWidth($drawborder);
				
		$Lx = $drawborder;
		$Ty = $drawborder;
		$Rx = $drawheight - $drawborder - $drawmargin;
		$By = $drawwidth - $drawborder - $drawmargin;
		$MIDx = $drawwidth/2;
		$MIDy = $drawheight/2;
		
		$draw->line($Lx, $MIDy, $Rx, $MIDy);
		$draw->line($MIDx, $Ty, $Rx, $MIDy);
		$draw->line($MIDx, $By, $Rx, $MIDy);
		
		$image->drawImage($draw);
		$image->writeImage(VTM_CHARACTER_URL . "images/{$name}." . $imagetype);

		$image = "";
	}
}


function vtm_render_config_features() {	
	global $wpdb;
	
		?>
		<h3>Enable/Disable Plugin Features</h3>
		<p>You can hide or show the various plugin features.</p>
		<form method="post" action="options.php">
		<?php
		
		settings_fields( 'vtm_features_group' );
		do_settings_sections('vtm_features_group');
		?>

		<table>
		<tr>
			<td><label>Track Temporary Stats: </label></td>
			<td><input type="checkbox" name="vtm_feature_temp_stats" value="1" <?php checked( '1', get_option( 'vtm_feature_temp_stats', '0' ) ); ?> /></td>
			<td>Track Willpower and Blood pool spends</td>
		</tr>
		<tr>
			<td><label>Maps: </label></td>
			<td><input type="checkbox" name="vtm_feature_maps" value="1" <?php checked( '1', get_option( 'vtm_feature_maps', '0' ) ); ?> /></td>
			<td>Show hunting/city maps</td>
		</tr>
		<tr>
			<td><label>Reports: </label></td>
			<td><input type="checkbox" name="vtm_feature_reports" value="1" <?php checked( '1', get_option( 'vtm_feature_reports', '0' ) ); ?> /></td>
			<td>Show administrator reports, including sign-in sheet</td>
		</tr>
		<tr>
			<td><label>Email configuration: </label></td>
			<td><input type="checkbox" name="vtm_feature_email" value="1" <?php checked( '1', get_option( 'vtm_feature_email', '0' ) ); ?> /></td>
			<td>Advanced options for configuring sending email</td>
		</tr>
		<tr>
			<td><label>Newsletter: </label></td>
			<td><input type="checkbox" name="vtm_feature_news" value="1" <?php checked( '1', get_option( 'vtm_feature_news', '0' ) ); ?> /></td>
			<td>Email out news and Experience Point totals to active character accounts</td>
		</tr>
		<tr>
			<td><label>Private Messaging: </label></td>
			<td><input type="checkbox" name="vtm_feature_pm" value="1" <?php checked( '1', get_option( 'vtm_feature_pm', '0' ) ); ?> /></td>
			<td>Enable inter-character private communication</td>
		</tr>
		</table>
		<?php submit_button("Save Changes", "primary", "save_features_button"); ?>
		</form>
		
	<?php 
}

function vtm_render_config_pm() {	
	global $wpdb;
	
		?>
		<h3>Private Messaging Options</h3>
		<form method="post" action="options.php">
		<?php
		settings_fields( 'vtm_pm_options_group' );
		do_settings_sections('vtm_pm_options_group');
		?>

		<table>
		<tr>
			<td><label>Enable In-Character postoffice: </label></td>
			<td><input type="checkbox" name="vtm_pm_ic_postoffice_enabled" value="1" <?php checked( '1', get_option( 'vtm_pm_ic_postoffice_enabled', '0' ) ); ?> /></td>
			<td>Allow messages to be posted to all active characters.</td>
		</tr>
		<tr>
			<td><label>In-Character Post Office location: </label></td>
			<td><input type="text" name="vtm_pm_ic_postoffice_location" value="<?php echo get_option( 'vtm_pm_ic_postoffice_location' ); ?>" /></td>
			<td>For example, characters might be able to leave messages for each other at a central location such as a nightclub</td>
		</tr>
		</table>
		<?php submit_button("Save Changes", "primary", "save_pm_button"); ?>
		</form>
		
	<?php 
}

function vtm_render_config_profile() {	
	global $wpdb;

		?><h3>Character Profile Options</h3>
		<form method="post" action="options.php">
			<?php
			settings_fields( 'vtm_profile_options_group' );
			do_settings_sections('vtm_profile_options_group');
			?>	
			
			<table>
			<tr>
				<td><label>Players can set their profile picture:</label></td>
				<td><input type="checkbox" name="vtm_user_set_image" value="1" <?php checked( '1', get_option( 'vtm_user_set_image', '1' ) ); ?> /></td>
			</tr>
			<tr>
				<td><label>Player can upload a profile picture to the Media Gallery:</label></td>
				<td><input type="checkbox" name="vtm_user_upload_image" value="1" <?php checked( '1', get_option( 'vtm_user_upload_image', '0' ) ); ?> /></td>
			</tr>
			<tr>
				<td><label>Maximum picture width for uploaded pictures (pixels)</label></td>
				<td><input type="text" name="vtm_max_width" value="<?php echo get_option('vtm_max_width', '0'); ?>" /> (set to 0 for no limit)</td>
			</tr>
			<tr>
				<td><label>Maximum picture height for uploaded pictures (pixels)</label></td>
				<td><input type="text" name="vtm_max_height" value="<?php echo get_option('vtm_max_height', '0'); ?>"  /> (set to 0 for no limit)</td>
			</tr>
			<tr>
				<td><label>Maximum picture filesize for uploaded pictures (bytes)</label></td>
				<td><input type="text" name="vtm_max_size" value="<?php echo get_option('vtm_max_size', '0'); ?>" /> (set to 0 for no limit)</td>
			</tr>
			<tr>
				<td>Image effect</td>
				<td>
				<select name="vtm_image_effect">
					<option value="none" <?php echo selected( 'none', get_option('vtm_image_effect'), false );?>>None</option>
					<option value="bw" <?php echo selected( 'bw', get_option('vtm_image_effect'), false );?>>Black and White</option>
					<option value="sepia" <?php echo selected( 'sepia', get_option('vtm_image_effect'), false );?>>Sepia</option>
					<option value="painting" <?php echo selected( 'painting', get_option('vtm_image_effect'), false );?>>Painting</option>
				</select>
				</td>
			</tr>
			</table>
			<?php submit_button("Save Profile Options", "primary", "save_profile_button"); ?>
		
		</form>

		
	<?php 
}

function vtm_render_config_database() {	
	global $wpdb;
	
		?>
		<h3>Plugin Database Options</h3>
		<?php 
		
			if (isset($_REQUEST['purge_deleted'])) {
				?>
				<form id='options_form' method='post'>
				<table>
				<tr><th>ID</th><th>Character</th><th>Player</th><th>Deleted on</th><th>Select</th></tr>
				<?php 
				$list = vtm_listDeletedCharacters();
				foreach ($list as $chID => $row) {
					echo "<tr><td>$chID</td><td>" . vtm_formatOutput($row->NAME) . "</td>";
					echo "<td>" . vtm_formatOutput($row->PLAYER) . "</td><td>{$row->LAST_UPDATED}</td><td>";
					echo "<input type='checkbox' name='characters[{$chID}]' " . checked( 1, 1, 0) . ">";
					echo "<input type='hidden'   name='names[{$chID}]' value='" . vtm_formatOutput($row->NAME) . "'>";
					echo "</td></tr>";
				}
				?>
				</table>
				<input type="submit" name="comfirm_purge" class="button-primary" value="Confirm" />
				<input type="submit" name="cancel_purge" class="button-primary" value="Cancel" />
				</form>
				<?php
			}
			elseif (isset($_REQUEST['comfirm_purge'])) {
				if (isset($_REQUEST['characters'])) {
					echo "<ul>";
					foreach ($_REQUEST['characters'] as $chID => $selected) {
						if ($selected) {
							echo vtm_purge_character($chID, $_REQUEST['names'][$chID]);
						}
					}
					?>
					</ul>
					<form id='options_form' method='post'>
					<input type="submit" name="return_purge" class="button-primary" value="Done" />
					</form>
					<?php
				} else {
					?>
					<p style='color:orange;'>No characters selected to purge</p>
					<form id='options_form' method='post'>
					<input type="submit" name="cancel_purge" class="button-primary" value="Return" />
					</form>
					<?php
				}
			}
			elseif (isset($_REQUEST['factory_defaults'])) {
				?>
				<form id='options_form' method='post'>
				<p>ALL data, including characters, will be removed and the default
				data set re-added. </p>
				<input type="submit" name="confirm_factory_defaults" class="button-primary" value="Confirm" />
				<input type="submit" name="cancel_factory_defaults" class="button-primary" value="Cancel" />
				</form>
				<?php
			}
			elseif (isset($_REQUEST['confirm_factory_defaults'])) {
				vtm_factory_defaults();
				vtm_character_install_data(VTM_CHARACTER_URL . "init");
				?>
				<form id='options_form' method='post'>
				<input type="submit" name="return_factory_defaults" class="button-primary" value="Done" />
				</form>
				<?php
			}
			elseif (isset($_REQUEST['load_data'])) {
				?>
				<form id='options_form' method='post'>
				<p>ALL data, including characters, will be removed and the new
				data loaded in. </p>
				<?php wp_nonce_field( 'vtm_load_data', 'vtm_load_data_nonce' ); ?>
				<input type="submit" name="confirm_load_data" class="button-primary" value="Confirm" />
				<input type="submit" name="cancel_load_data" class="button-primary" value="Cancel" />
				</form>
				<?php
			}
			elseif (isset($_REQUEST['confirm_load_data']) &&
				wp_verify_nonce( $_POST['vtm_load_data_nonce'], 'vtm_load_data' )
				) {
					
				$from = VTM_DATA_FILE;
				$upload = wp_upload_dir();
				$uploadto = $upload['path'] . "/init.zip";
				$unzipto = $upload['path'] . "/" . VTM_DATA_NAME . "-" . VTM_DATA_VERSION;
				//echo "<p>Upload to: $uploadto</p>";
				//echo "<p>Unzip to: $unzipto</p>";

				$creds = request_filesystem_credentials(site_url() . '/wp-admin/', '', false, false, array());

				/* initialize the API */
				if ( ! WP_Filesystem($creds) ) {
					/* any problems and we exit */
					return false;
				}	

				global $wp_filesystem;
				
				// fopen to get file from external URL
				// and save it off in chunks (256b?)
				echo '<p>Downloading data zip file</p>';
				$file = fopen ($from, "rb");
				if ($file) {
					$newf = fopen ($uploadto, "wb");

					if ($newf) {
						while(!feof($file)) {
							fwrite($newf, fread($file, 1024 * 8 ), 1024 * 8 );
						}
					} else {
						echo "<p style='color:red'>Cannot binary write $uploadto</p>";
					}
				} else {
					echo "<p style='color:red'>Cannot binary read $from</p>";
				}
				
				// extract to init
				echo '<p>Unzipping the data</p>';
				$unzipfile = unzip_file($uploadto, $upload['path']);
				if ( $unzipfile ) {
					// install
					echo '<p>Clearing all data from data tables</p>';
					vtm_factory_defaults();
					echo '<p>Installing ' . VTM_DATA_VERSION . ' data</p>';
					vtm_character_install_data($unzipto);
				} else {
					echo 'There was an error unzipping the file.';       
				}
				?>
				<form id='options_form' method='post'>
				<input type="submit" name="return_load_data" class="button-primary" value="Done" />
				</form>
				<?php
				
			}
			elseif (isset($_REQUEST['export_data'])) {
				global $vtm_character_version;
				$upload = wp_upload_dir();
				//print_r($upload);
				$link = vtm_export_data($upload['path'], "vtm-export-$vtm_character_version");
				// provide link
				$url = $upload['url'] . "/$link";
				echo "<p>Download exported data: <a class='button-primary' href='$url'>$link</a>";
				?>
				<form id='options_form' method='post'>
				<input type="submit" name="return_export_data" class="button-primary" value="Done" />
				</form>
				<?php
			}
			elseif (isset($_REQUEST['import_data'])) {
				?>
				<form id='options_form' name='import_data_form' method='post' enctype="multipart/form-data">
				<input type='file' name='vtm_import' id='vtm_import'  multiple='false' />
				<?php echo wp_nonce_field( 'vtm_import', 'vtm_import_nonce' ); ?>
				<input type="submit" name="select_import_data" class="button-primary" value="Import" />
				</form>
				<?php
			}
			elseif (isset($_REQUEST['select_import_data'])
				&& isset( $_POST['vtm_import_nonce'])
				&& wp_verify_nonce( $_POST['vtm_import_nonce'], 'vtm_import' )) {

				//print_r($_REQUEST);
				//print_r($_FILES);
				// check file type
				if ($_FILES['vtm_import']['type'] !== 'application/zip') {
					echo "<p style='color:red'>Uploaded file must be a zip file</p>";
				}
				else {
					// put in upload directory
					$uploadedfile = $_FILES['vtm_import'];
					$upload_overrides = array( 'test_form' => false );
					$movefile = wp_handle_upload( $uploadedfile, $upload_overrides );
					
					if ( $movefile && !isset( $movefile['error'] ) ) {
						echo "<p>File has been uploaded</p>";
						//var_dump( $movefile);
					} else {
						echo $movefile['error'];
					}
					
					$creds = request_filesystem_credentials(site_url() . '/wp-admin/', '', false, false, array());
					if ( ! WP_Filesystem($creds) ) {
						return false;
					}	
					global $wp_filesystem;
					
					// create folder to unzip to 
					$upload = wp_upload_dir();
					$unzipto = $upload['path'] . "/" . basename($movefile['file'], ".zip");
					if(!$wp_filesystem->is_dir($unzipto)) {
						$wp_filesystem->mkdir($unzipto);
					}
					
					//unzip 
					$unzipfile = unzip_file($movefile['file'], $unzipto);
					if ( $unzipfile && !is_wp_error( $unzipfile )) {
						echo "<p>File has been uncompressed</p>";
						
						//check database format/version
						$subfolder = glob("$unzipto/*");
						$subfolder = $subfolder[0];
						//print_r($subfolder);
						
						if (vtm_is_valid_import_version(basename($subfolder))) {
							// install
							echo '<p>Clearing all data from data tables</p>';
							vtm_factory_defaults();
							echo "<p>Installing data</p>";
							vtm_character_install_data("$subfolder");
								
						} else {
							echo "<p style='color:red'>Cannot import data as it was created with a different database version.</p>";       
						}
					
					} else {
						print_r($unzipfile);
						echo "<p style='color:red'>There was an error unzipping the file to $unzipto.</p>";       
					}
					
				}
				

				
				?>
				<form id='options_form' method='post'>
				<input type="submit" name="return_load_data" class="button-primary" value="Done" />
				</form>
				<?php
				// check database format/version 
				
				// install data
			}
			else {
				$access_type = get_filesystem_method();			
			
		?>

		<form id='database_form' method='post'>
		<?php
			if ($access_type == 'direct') {
		?>
			<h3>Export database data</h3>
			<p>Click this button to download a copy of all the character and other data.</p>
			<input type="submit" name="export_data" class="button-primary" value="Export" />
			<h3>Import database data</h3>
			<p>Click this button to import previously exported character and other data.</p>
			<input type="submit" name="import_data" class="button-primary" value="Import" />
		<?php } ?>

			<h3>Purge deleted characters</h3>
			<p>Click this button to completely remove all deleted characters from the database.</p>
			<input type="submit" name="purge_deleted" class="button-primary" value="Purge" />
		<?php
			$count = $wpdb->get_var("SELECT COUNT(ID) FROM " . VTM_TABLE_PREFIX . "CHARACTER");
			if ($count == 0 && $access_type == 'direct') {
		?>
			<h3>Load pre-defined data</h3>
			<p>You can optionally download and add pre-defined data for the Database tables.</p>
			<p>Version: <?php echo VTM_DATA_VERSION;?></p>
			<input type="submit" name="load_data" class="button-primary" value="Download and install data" />
		<?php } ?>
			<h3>Reset Database tables</h3>
			<p>Click this button to completely remove and re-create all the database tables.</p>
			<p>THIS WILL REMOVE ALL CHARACTERS, EXPERIENCE AND ANY DATA YOU HAVE ADDED TO THE DATA TABLES.</p>
			<input type="submit" name="factory_defaults" class="button-primary" value="Reset to factory defaults" />
		</form>
		
	<?php 
	}
}

function vtm_listDeletedCharacters() {
	global $wpdb;

	$sql = "SELECT ch.ID, ch.NAME as NAME, pl.NAME as PLAYER, ch.LAST_UPDATED
		FROM 
			" . VTM_TABLE_PREFIX . "CHARACTER ch,
			" . VTM_TABLE_PREFIX . "PLAYER pl
		WHERE 
			ch.PLAYER_ID = pl.ID
			AND ch.DELETED = 'Y'";
	$results =  $wpdb->get_results($sql, OBJECT_K);
	
	return $results;
	
}
?>