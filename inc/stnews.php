<?php 

$vtmglobal['cronbatch'] = 5; // emails per batch

// Register Custom Post Type
function vtm_news_post_type() {

	$labels = array(
		'name'                => _x( 'Storyteller Posts', 'Post Type General Name', 'text_domain' ),
		'singular_name'       => _x( 'Storyteller Post', 'Post Type Singular Name', 'text_domain' ),
		'menu_name'           => __( 'Storyteller News', 'text_domain' ),
		'parent_item_colon'   => __( 'Parent Item:', 'text_domain' ),
		'all_items'           => __( 'All Items', 'text_domain' ),
		'view_item'           => __( 'View Item', 'text_domain' ),
		'add_new_item'        => __( 'Add New Item', 'text_domain' ),
		'add_new'             => __( 'Add New', 'text_domain' ),
		'edit_item'           => __( 'Edit Item', 'text_domain' ),
		'update_item'         => __( 'Update Item', 'text_domain' ),
		'search_items'        => __( 'Search Item', 'text_domain' ),
		'not_found'           => __( 'Not found', 'text_domain' ),
		'not_found_in_trash'  => __( 'Not found in Trash', 'text_domain' ),
	);
	$args = array(
		'label'               => __( 'vtmpost', 'text_domain' ),
		'description'         => __( 'Game News and Blog Posts', 'text_domain' ),
		'labels'              => $labels,
		'supports'            => array( ),
		'taxonomies'          => array( ),
		'hierarchical'        => false,
		'public'              => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_nav_menus'   => true,
		'show_in_admin_bar'   => true,
		'menu_position'       => 5,
		'can_export'          => true,
		'has_archive'         => true,
		'exclude_from_search' => false,
		'publicly_queryable'  => true,
		'capability_type'     => 'post'
	);
	register_post_type( 'vtmpost', $args );

}

// Hook into the 'init' action
if (get_option( 'vtm_feature_news', '0' ) == '1') {
	add_action( 'init', 'vtm_news_post_type', 0 );
	
	// Change the columns for the edit CPT screen
	function vtm_news_change_columns( $cols ) {
	  $cols['vtmstatus'] =  __( 'Send Status', 'trans' );
	  return $cols;
	}
	add_filter( "manage_vtmpost_posts_columns", "vtm_news_change_columns" );
	
	function vtm_news_custom_columns( $column, $post_id ) {
		switch ( $column ) {
	    case "vtmstatus":
			if (get_post_status( $post_id ) == 'publish') {
				$all  = count(vtm_get_queued_mail_recipients($post_id));
				$sent = count(vtm_get_queued_mail_recipients($post_id, 'Sent'));
				$fails = count(vtm_get_queued_mail_recipients($post_id, 'Error'));
				if ($all == 0) {
					echo "No recipients queued";
				} 
				elseif ($all == $sent) {
					echo "Send successful. " . $sent . " emails sent";
				}
				else {
					echo $sent . " of " . $all . " sent (" . $fails . " failed)";
				}
			} else {
				echo "Publish required";
			}
			break;
		}
	}
	add_action( "manage_posts_custom_column", "vtm_news_custom_columns", 10, 2 );

	// Show posts on homepage
	function add_vtmnews_to_query( $query ) {
	  if ( is_home() && $query->is_main_query() && get_option( 'vtm_news_blogroll', '0' ) == '1') {
		$query->set( 'post_type', array('vtmpost', 'post') );
	  }
	  return $query;
	}	
	add_action( 'pre_get_posts', 'add_vtmnews_to_query' );
	
	// Meta box
	function vtm_news_metabox() {
		add_meta_box(
			'vtm_news_metabox',
			'V:tM News',
			'vtm_news_metabox_callback',
			'vtmpost',
			'side',
			'default'
		);
	}
	add_action( 'add_meta_boxes', 'vtm_news_metabox' );
	
	function vtm_news_metabox_callback ($post) {
		wp_nonce_field( 'vtm_news_metabox', 'vtm_news_metabox_nonce' );
		
		if (get_post_status( $post->ID ) == 'publish') {
			$recipients = vtm_get_queued_mail_recipients($post->ID, 'Queue');
			
			?><strong>Select action on save</strong><br />
			<input type='radio' id='mail_none' name='vtm_mail_action' value='none' checked /><label for='mail_none'>None</label><br />
			<input type='radio' id='mail_queue' name='vtm_mail_action' value='queue' /><label for='mail_queue'>Queue recipients</label><br />
			<?php if (count($recipients) > 0) {
				?><input type='radio' id='mail_test' name='vtm_mail_action' value='test' /><label for='mail_test'>Send test email</label><br />
				<input type='radio' id='mail_send' name='vtm_mail_action' value='send' /><label for='mail_send'>Start sending emails</label><br />
				<input type='radio' id='mail_clear' name='vtm_mail_action' value='clear' /><label for='mail_clear'>Clear queue/stop sending</label><br />
				<?php
			}
			
			echo "<br /><strong>Recipients</strong><br />";
			$recipients = vtm_get_queued_mail_recipients($post->ID, 'all');
			if (count($recipients) > 0) {
				echo "<ul>";
				foreach ($recipients as $rec) {
					echo "<li>" . vtm_formatOutput($rec->name) . " (" . vtm_formatOutput($rec->status) . ")</li>";
				}
				echo "</ul>";
			} else {
				echo "<p>No recipients queued</p>";
			}
		} else {
			echo "Publish post required";
		}
	}
	
	function vtm_get_queued_mail_recipients($postid, $status = "all") {
		global $wpdb;
		
		$args = array($postid);
		$sql = "SELECT c.NAME as name, s.NAME as status, c.EMAIL as email
				FROM 
					" . VTM_TABLE_PREFIX . "CHARACTER c,
					" . VTM_TABLE_PREFIX . "MAIL_QUEUE q,
					" . VTM_TABLE_PREFIX . "MAIL_STATUS s
				WHERE 
					c.ID = q.CHARACTER_ID
					AND q.MAIL_STATUS_ID = s.ID
					AND q.WP_POST_ID = %s";
		if ($status != "all") {
			$sql .= " AND s.NAME = %s";
			array_push($args, $status);
		}
		$sql = $wpdb->prepare($sql, $args);
		//echo "<p>SQL: $sql</p>";
		return $wpdb->get_results($sql);
	}
	
	
	function vtm_mail_save_meta_box_data( $post_id ) {

		/*
		 * We need to verify this came from our screen and with proper authorization,
		 * because the save_post action can be triggered at other times.
		 */

		// Check if our nonce is set.
		if ( ! isset( $_POST['vtm_news_metabox_nonce'] ) ) {
			return;
		}
		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $_POST['vtm_news_metabox_nonce'], 'vtm_news_metabox' ) ) {
			return;
		}
		// If this is an autosave, our form has not been submitted, so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check the user's permissions.
		if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {
			if ( ! current_user_can( 'edit_page', $post_id ) ) {
				return;
			}
		} else {
			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return;
			}
		}

		/* OK, it's safe for us to save the data now. */
		
		// Make sure that it is set.
		if ( ! isset( $_POST['vtm_mail_action'] ) ) {
			return;
		}

		// Sanitize user input.
		$action = sanitize_text_field( $_POST['vtm_mail_action'] );

		// Update the meta field in the database.
		if ($action == 'none') {
			return;
		}
		elseif ($action == 'queue') {
			//echo "<li>Here</li>";
			vtm_queue_recipients($post_id);
		}
		elseif ($action == 'clear') {
			vtm_clear_queue($post_id);
		}
		elseif ($action == 'test') {
			$recipients = vtm_get_recipients($post_id);
			vtm_send_news_email($recipients[0]->ID, $post_id, true);
		}
		elseif ($action == 'send') {
			// add crontab entry to send emails every 5 mins, starting now
			vtm_schedule_news_cron($post_id);
		}
		
	}	
	add_action( 'save_post', 'vtm_mail_save_meta_box_data' );
	
	function vtm_get_recipients() {
		global $wpdb;
		
		$queueid = $wpdb->get_var(
			$wpdb->prepare("SELECT ID FROM " . VTM_TABLE_PREFIX . "MAIL_STATUS WHERE NAME = %s", 'Queue')
		);
		
		$sql = "SELECT c.ID, $queueid as MAIL_STATUS_ID
			FROM
				" . VTM_TABLE_PREFIX . "CHARACTER c,
				" . VTM_TABLE_PREFIX. "PLAYER p,
				" . VTM_TABLE_PREFIX. "PLAYER_STATUS ps,
				" . VTM_TABLE_PREFIX. "CHARACTER_TYPE ct,
				" . VTM_TABLE_PREFIX. "CHARACTER_STATUS cs,
				" . VTM_TABLE_PREFIX. "CHARGEN_STATUS cgs
				
			WHERE
				c.EMAIL != ''
				AND p.ID = c.PLAYER_ID
				AND ps.ID = p.PLAYER_STATUS_ID
				AND ct.ID = c.CHARACTER_TYPE_ID
				AND cs.ID = c.CHARACTER_STATUS_ID
				AND cgs.ID  = c.CHARGEN_STATUS_ID
				AND c.DELETED != 'Y'
				AND ps.NAME = 'Active'
				AND ct.NAME = 'PC'
				AND cs.NAME = 'Alive'
				AND cgs.NAME = 'Approved'
			";
		//echo "<p>SQL: $sql</p>";
		return $wpdb->get_results($sql);
	}
	
	function vtm_queue_recipients($postid) {
		global $wpdb;
		
		$list = vtm_get_recipients();
		//print_r($list);
		if (count($list) > 0) {
			// clear recipients table for this post 
			vtm_clear_queue($postid);		
			
			// add new recipients
			foreach ($list as $item) {
				$wpdb->insert(VTM_TABLE_PREFIX . "MAIL_QUEUE",
					array (
						'CHARACTER_ID' => $item->ID,
						'MAIL_STATUS_ID' => $item->MAIL_STATUS_ID,
						'WP_POST_ID' => $postid
					),
					array ('%d', '%d', '%d')
				);
			}

		}
	}
	
	function vtm_clear_queue($postid) {
		global $wpdb;
		$wpdb->delete( VTM_TABLE_PREFIX . "MAIL_QUEUE", 
			array ('WP_POST_ID' => $postid), 
			array ('%d')
		);			
		
	}
	
	function vtm_send_news_email($characterID, $postid, $test = 0) {
		
		$mycharacter = new vtmclass_character();
		$mycharacter->load($characterID);
		$name = $mycharacter->name;
		
		$replyname = get_option( 'vtm_replyto_name',    get_option( 'vtm_chargen_email_from_name', 'The Storytellers'));
		$email = $test 
			? get_option( 'vtm_replyto_address', get_option( 'vtm_chargen_email_from_address', get_bloginfo('admin_email') ) ) 
			: $mycharacter->email;
			
		$postinfo = get_post($postid);
		$subject = stripslashes($postinfo->post_title);
		
		$body = "<p>Hello $name,</p>";
		$body .= "<p>You have <strong>{$mycharacter->current_experience}</strong> experience available to spend. ";
		if (get_option('vtm_feature_temp_stats', '0') == '1')
			$body .= "You have <strong>{$mycharacter->current_willpower}</strong> Willpower out of {$mycharacter->willpower}. ";
		$body .= "You are at <strong>{$mycharacter->path_rating}</strong> on {$mycharacter->path_of_enlightenment}. ";
		$body .= "Your background is <strong>" . 
			sprintf ("%.0f%%", $mycharacter->backgrounds_done * 100 / $mycharacter->backgrounds_total) .
			"</strong> complete.</p>";
			
		$xpdata = vtm_get_xp_table($mycharacter->player_id, $characterID, 5);
		if (count($xpdata) > 0) {
			$body .= "<ul>\n";
			foreach ($xpdata as $row) {
				$body .= "<li>{$row->awarded} " . stripslashes($row->char_name) . " : " . stripslashes($row->reason_name) . " " . stripslashes($row->comment) . " : {$row->amount} experience</li>";
			}
			$body .= "</ul>";
		}
			
		$body .=  apply_filters('the_content', $postinfo->post_content);
		$body .= "<p>Best regards,<br>$replyname</p>";
		
		$result = vtm_send_email($email, $subject, $body);
		
		return $result;
	}
	
	function vtm_get_next_queued_recipient($postid) {
		global $wpdb;
		
		$sql = "SELECT mq.CHARACTER_ID
				FROM 
					" . VTM_TABLE_PREFIX . "MAIL_QUEUE mq,
					" . VTM_TABLE_PREFIX . "MAIL_STATUS ms
				WHERE 
					mq.WP_POST_ID = %s
					AND mq.MAIL_STATUS_ID = ms.ID 
					AND ms.NAME = 'Queue'
				ORDER BY mq.ID
				LIMIT 1";
		$sql = $wpdb->prepare($sql, $postid);
		return $wpdb->get_var($sql);
	}
	
	
	// crontab functions
	// - schedule crontab
	function vtm_schedule_news_cron($postid) {
		wp_schedule_event( time(), 'vtm_cron_news', 'vtm_news_cron_hook', array($postid) );
	}
	add_action( 'vtm_news_cron_hook', 'vtm_news_do_cron', 10, 1);	
	
	// - send next x recipients
	function vtm_news_do_cron($postid) {
		global $vtmglobal;
		
		//echo "<p>Running cron with post $postid</p>";
		vtm_send_nextx_queued_recipients($postid,$vtmglobal['cronbatch']);
		
		// - cancel crontab when all recipients sent
		$more2send = vtm_get_next_queued_recipient($postid);
		if (!$more2send) {
			$next = wp_next_scheduled( 'vtm_news_cron_hook', array($postid) );
			wp_unschedule_event( $next, 'vtm_news_cron_hook', array($postid) );
		}
	}

	
	add_filter( 'cron_schedules', 'vtm_add_cron_schedule' ); 
	function vtm_add_cron_schedule( $schedules ) {
		$schedules['vtm_cron_news'] = array(
			'interval' => 60 * 5, // 60 seconds * 5 mins
			'display' => 'V:tM Email Cron - 5 minutes' 
		);
		return $schedules;
	}
	
	// repeat x times:
	//		get next recipient
	//		send their email
	//		update send status
	function vtm_send_nextx_queued_recipients($postid, $x = 5) {
		
		$debug = get_option( 'vtm_email_debug', 'false' ) == 'true' ? true : false;
		
		for ($i = 0 ; $i < $x ; $i++) {
			$characterID = vtm_get_next_queued_recipient($postid);
			if ($characterID) {
				//echo "<p>Sending $postid to $characterID</p>";
				$result = vtm_send_news_email($characterID, $postid, $debug);
				vtm_update_send_status($characterID, $postid, $result);
			}
		}
	}
	
	function vtm_update_send_status($characterID, $postid, $result) {
		global $wpdb;
		
		$pass = $wpdb->get_var("SELECT ID FROM " . VTM_TABLE_PREFIX . "MAIL_STATUS WHERE NAME = 'Sent'");
		$fail = $wpdb->get_var("SELECT ID FROM " . VTM_TABLE_PREFIX . "MAIL_STATUS WHERE NAME = 'Error'");
		
		$id = $result ? $pass : $fail;
				
		$wpdb->update(VTM_TABLE_PREFIX . "MAIL_QUEUE",
			array('MAIL_STATUS_ID' => $id),
			array (
				'CHARACTER_ID' => $characterID,
				'WP_POST_ID'   => $postid
			)
		);
		
	}
	
}

?>