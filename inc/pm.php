<?php 

// CODE:
//	* No spaces
//	* Alphanumeric characters
//	* Uppercase

// TO DO:
//	* Change Publish to Send Message?

// Register Custom Post Type
function vtm_PM_post_type() {

	$labels = array(
		'name'                => _x( 'Character messages', 'Post Type General Name', 'text_domain' ),
		'singular_name'       => _x( 'Character message', 'Post Type Singular Name', 'text_domain' ),
		'menu_name'           => __( 'Character Mail', 'text_domain' ),
		'parent_item_colon'   => __( 'Parent Message:', 'text_domain' ),
		'all_items'           => __( 'All Messages', 'text_domain' ),
		'view_item'           => __( 'View Message', 'text_domain' ),
		'add_new_item'        => __( 'Send New Message', 'text_domain' ),
		'add_new'             => __( 'Send New', 'text_domain' ),
		'edit_item'           => __( 'Edit Message', 'text_domain' ),
		'update_item'         => __( 'Update Message', 'text_domain' ),
		'search_items'        => __( 'Search Message', 'text_domain' ),
		'not_found'           => __( 'Not found', 'text_domain' ),
		'not_found_in_trash'  => __( 'Not found in Trash', 'text_domain' ),
	);
	$args = array(
		'label'               => __( 'inbox', 'text_domain' ),
		'description'         => __( 'Private Message', 'text_domain' ),
		'labels'              => $labels,
		'supports'            => array( ),
		'taxonomies'          => array( ),
		'hierarchical'        => false,
		'public'              => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_nav_menus'   => true,
		'show_in_admin_bar'   => true,
		'menu_position'       => 6,
		'can_export'          => true,
		'has_archive'         => true,
		'exclude_from_search' => true,
		'publicly_queryable'  => true,
		'capability_type'     => 'post',
		'delete_with_user'    => true,
		// 'map_meta_cap'        => false,
        'capabilities' => array(
            // meta caps (don't assign these to roles)
            'edit_post'              => 'read',
            'read_post'              => 'read',
            'delete_post'            => 'read',

            // primitive/meta caps
            'create_posts'           => 'read',

            // primitive caps used outside of map_meta_cap()
            'edit_posts'             => 'read',
            'edit_others_posts'      => 'read',
            'publish_posts'          => 'read',
            'read_private_posts'     => 'read',

            // primitive caps used inside of map_meta_cap()
            'read'                   => 'read',
            'delete_posts'           => 'read',
            'delete_private_posts'   => 'read',
            'delete_published_posts' => 'read',
            'delete_others_posts'    => 'read',
            'edit_private_posts'     => 'read',
            'edit_published_posts'   => 'read'
        ),
		//'rewrite' => array('slug' => 'vtmpmxxx'),
	);
	register_post_type( 'vtmpm', $args );

}
function vtm_pm_rewrite_flush() {
    // First, we "add" the custom post type via the above written function.
    // Note: "add" is written with quotes, as CPTs don't get added to the DB,
    // They are only referenced in the post_type column with a post entry, 
    // when you add a post of this CPT.
    vtm_PM_post_type();

    // ATTENTION: This is *only* done during plugin activation hook in this example!
    // You should *NEVER EVER* do this on every page load!!
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'vtm_pm_rewrite_flush' );


if (get_option( 'vtm_feature_pm', '0' ) == '1') {
		
	// Hook the custom post type into the 'init' action
	// --------------------------------------------
	add_action( 'init', 'vtm_PM_post_type', 0 );
	
	// Add extra columns for the List Messages screen
	// --------------------------------------------
	function vtm_pm_change_columns( $cols ) {
		$cols['vtmpmstatus'] =  __( 'Send Status', 'trans' );
		$cols['vtmfrom']     =  __( 'From', 'trans' );
		$cols['vtmto']       =  __( 'To', 'trans' );
		
		if (vtm_isST())
			$cols['author'] =  __( 'Actually From', 'trans' );
	  return $cols;
	}
	add_filter( "manage_vtmpm_posts_columns", "vtm_pm_change_columns" );
	
	// Display extra columns on the List Messages screen
	// --------------------------------------------
	function vtm_pm_custom_columns( $column, $post_id ) {
		global $vtmglobal;

		$info = vtm_pm_getpostmeta($post_id);
		
		switch ( $column ) {
	    case "vtmpmstatus":
			switch (get_post_status( $post_id )) {
				case 'publish': 
					if ($info['IsPMOwner']) {
						$status = "Delivered"; 
					} else {
						$status = ucwords(get_post_meta( $post_id, '_vtmpm_to_status', true ));
					}
					break;
				case 'trash':   $status = "Deleted"; break;
				default: $status = "Drafted";
			}
			//echo "$status (" . get_post_meta( $post_id, '_vtmpm_to_status', true ) . " / " . get_post_meta( $post_id, '_vtmpm_from_status', true ) . ")";
			echo "$status";
			break;
	    case "vtmfrom":
			echo vtm_formatOutput($info['FromFull'], 1);
			break;
	    case "vtmto":
			echo vtm_formatOutput($info['ToFull']);
			break;
		}
	}
	add_action( "manage_vtmpm_posts_custom_column", "vtm_pm_custom_columns", 10, 2 );

	function vtm_pm_remove_others_columns($columns) {

		$expected = array(
			'subject', 'date', 'vtmfrom', 'vtmto', 'vtmpmstatus',
			'author'
		);
	
		foreach($columns as $key => $title) {
			if (!in_array($key,$expected))
				unset($columns[$key]);
		}
		return $columns;
	}
	add_filter('manage_vtmpm_posts_columns', 'vtm_pm_remove_others_columns', 100);

	function vtm_pm_replace_title_column($columns) {

		$new = array();

		foreach($columns as $key => $title) {
			if ($key=='title') 
			$new['subject'] = 'Message Subject'; // Our New Column Name
			$new[$key] = $title;
		}

		unset($new['title']); 
		return $new;
	}

	// Replace the title with your custom title
	function vtm_pm_replace_title_row($column_name, $post_ID) {

		if ($column_name == 'subject') {
			
			$post    = get_post( $post_ID );
			$subject = esc_attr(get_the_title());
			$subject = empty($subject) ? '(no title)' : $subject;
			
			if ($post->post_status == 'publish') {
				$link = get_permalink($post_ID);
			} else {
				$link = get_edit_post_link($post_ID);
			}
			
			if (get_post_status( $post_ID ) == 'publish') {
				$current_user = wp_get_current_user();
				if (get_post_field( 'post_author', $post_ID ) == $current_user->ID) {
					$readclass = "read";
				} else {
					$readclass = get_post_meta( $post_ID, '_vtmpm_to_status', true );
				}
			} else {
				$readclass = "unread";
			}
			
			$pid = " <span style='color:silver'>(ID: $post_ID)</span>";
			$title ="<span class='sub-title vtmpm_title $readclass'><a href='$link'>$subject</a></span>$pid";
			
			// add in row actions
			//$title .= vtm_pm_render_row_actions($post);
			
			echo $title;
		}
	}
	add_filter('manage_vtmpm_posts_columns', 'vtm_pm_replace_title_column');
	add_action('manage_vtmpm_posts_custom_column', 'vtm_pm_replace_title_row', 10, 2);
	
	function vtm_pm_render_row_actions($post) {
		$can_edit_post = current_user_can( 'edit_post', $post->ID );
		$post_type_object = get_post_type_object( $post->post_type );
		$title = get_the_title();
		
		$actions = array();
		if ( $can_edit_post && 'trash' != $post->post_status ) {
			$actions['edit'] = '<a href="' . get_edit_post_link( $post->ID ) . '" title="' . esc_attr__( 'Edit this item' ) . '">' . __( 'Edit' ) . '</a>';
			$actions['inline hide-if-no-js'] = '<a href="#" class="editinline" title="' . esc_attr__( 'Edit this item inline' ) . '">' . __( 'Quick&nbsp;Edit' ) . '</a>';
		}
		if ( current_user_can( 'delete_post', $post->ID ) ) {
			if ( 'trash' == $post->post_status )
				$actions['untrash'] = "<a title='" . esc_attr__( 'Restore this item from the Trash' ) . "' href='" . wp_nonce_url( admin_url( sprintf( $post_type_object->_edit_link . '&amp;action=untrash', $post->ID ) ), 'untrash-post_' . $post->ID ) . "'>" . __( 'Restore' ) . "</a>";
			elseif ( EMPTY_TRASH_DAYS )
				$actions['trash'] = "<a class='submitdelete' title='" . esc_attr__( 'Move this item to the Trash' ) . "' href='" . get_delete_post_link( $post->ID ) . "'>" . __( 'Trash' ) . "</a>";
			if ( 'trash' == $post->post_status || !EMPTY_TRASH_DAYS )
				$actions['delete'] = "<a class='submitdelete' title='" . esc_attr__( 'Delete this item permanently' ) . "' href='" . get_delete_post_link( $post->ID, '', true ) . "'>" . __( 'Delete Permanently' ) . "</a>";
		}
		if ( $post_type_object->public ) {
			if ( in_array( $post->post_status, array( 'pending', 'draft', 'future' ) ) ) {
				if ( $can_edit_post ) {
					$preview_link = set_url_scheme( get_permalink( $post->ID ) );
					/** This filter is documented in wp-admin/includes/meta-boxes.php */
					$preview_link = apply_filters( 'preview_post_link', add_query_arg( 'preview', 'true', $preview_link ), $post );
					$actions['view'] = '<a href="' . esc_url( $preview_link ) . '" title="' . esc_attr( sprintf( __( 'Preview &#8220;%s&#8221;' ), $title ) ) . '" rel="permalink">' . __( 'Preview' ) . '</a>';
				}
			} elseif ( 'trash' != $post->post_status ) {
				$actions['view'] = '<a href="' . get_permalink( $post->ID ) . '" title="' . esc_attr( sprintf( __( 'View &#8220;%s&#8221;' ), $title ) ) . '" rel="permalink">' . __( 'View' ) . '</a>';
			}
		}

		$actions = apply_filters( 'post_row_actions', $actions, $post );	
			
		$table = new vtmclass_pm_WP_Posts_List_Table;
		$rowactions = $table->row_actions($actions, true );

		return $rowactions;
	}
	
	// New column is sortable 
	function vtm_pm_sortable_columns( $columns ) {
		$columns['subject'] = 'subject';
		return $columns;
	}
	add_filter( 'manage_edit-vtmpm_sortable_columns', 'vtm_pm_sortable_columns' );
	
	// replyto title 
	function vtm_pm_set_replyto_title( $title ) {
		if (isset($_GET['replyto'])) {
			$title = get_post_field( 'post_title', $_GET['replyto'] );
			if (!strstr($title,"RE: ")) {
				$title = "RE: $title"; 
			}
		}
		return $title;
	}
	add_filter( 'default_title', 'vtm_pm_set_replyto_title' );
	
	// Setup Meta box to the Edit Post page
	// --------------------------------------------
	function vtm_pm_metabox($post_type) {
			global $wp_meta_boxes;
			
			// remove extra metaboxes
			//$expected = array('submitdiv', 'slugdiv');
			$expected = array('slugdiv');
			if (isset($wp_meta_boxes['vtmpm'])) {
				$postboxes = $wp_meta_boxes['vtmpm'];
				foreach ($postboxes as $boxcontext => $boxinfo) {
					foreach ($boxinfo as $boxpriority => $box) {
						foreach ($box as $boxname => $boxdata) {
							if (!in_array($boxdata['id'],$expected))
								remove_meta_box( $boxdata['id'], 'vtmpm', $boxcontext );
						}
					}
				}
			}
			
			// add our metabox
			add_meta_box(
				'vtm_pm_metabox',
				'V:tM Messages',
				'vtm_pm_metabox_callback',
				'vtmpm',
				'special',
				'high'
			);
			
			// add our Send metabox
			add_meta_box(
				'submitdiv',
				'V:tM Messages',
				'vtm_pm_metabox_send_callback',
				'vtmpm',
				'side',
				'high'
			);
			
	}
	add_action( 'add_meta_boxes', 'vtm_pm_metabox', 100 );
	
	// Move the Meta box to the top of the page
	// --------------------------------------------
 	function vtm_pm_move_metabox() {
			# Get the globals:
			global $post, $wp_meta_boxes;

			# Output the "advanced" meta boxes:
			do_meta_boxes( get_current_screen(), 'special', $post );

			# Remove the initial "advanced" meta boxes:
			unset($wp_meta_boxes[get_post_type($post)]['special']);
	}
	add_action('edit_form_after_title', 'vtm_pm_move_metabox');

	// Display the Meta Box
	// --------------------------------------------
	function vtm_pm_metabox_callback($post) {
		global $vtmglobal;
		wp_nonce_field( 'vtm_pm_metabox', 'vtm_pm_metabox_nonce' );
		
		$addressbook = vtm_get_pm_addressbook();
		$myaddresses = vtm_get_pm_addresses();
				
		$characterID = isset($vtmglobal['characterID']) ? $vtmglobal['characterID'] : 0;

		$tochid   = get_post_meta( $post->ID, '_vtmpm_to_characterID', true );
		$tocode   = get_post_meta( $post->ID, '_vtmpm_to_code', true );
		$totype   = get_post_meta( $post->ID, '_vtmpm_to_type', true );
		$fromchid = get_post_meta( $post->ID, '_vtmpm_from_characterID', true );
		$fromcode = get_post_meta( $post->ID, '_vtmpm_from_code', true );
		$fromtype = get_post_meta( $post->ID, '_vtmpm_from_type', true );
		
		$tocode   = $totype == 0   ? 'postoffice' : $tocode;
		$fromcode = $fromtype == 0 ? 'postoffice' : $fromcode;
		$fromchid = empty($fromchid) ? $characterID : $fromchid;
		
		$to   = esc_attr($tochid . ":" . $tocode . ":" . $totype);
		
		// Set who post is from
		$poststatus = get_post_field( 'post_status', $post->ID );
		if ($poststatus == 'new' || $poststatus == 'auto-draft') {
			$from = "";
			if (!vtm_isST()) {
				$defaultaddr = vtm_get_default_address($vtmglobal['characterID']);
				if (isset($defaultaddr)) {
					$from = esc_attr($vtmglobal['characterID'] . ":" . 
						$defaultaddr->PM_CODE . ":" . $defaultaddr->PM_TYPE_ID);
				} 
			}
		} else {
			$from = esc_attr($fromchid . ":" . $fromcode . ":" . $fromtype);
		}
		
		
		// Set replyto post ID
		$extra = array();
		if (isset($_GET['replyto'])) {
			$replytopost = $_GET['replyto'];
			echo "Replying to post $replytopost";
			update_post_meta( $post->ID, '_vtmpm_replyto_postid', sanitize_text_field($replytopost) );
			$replytolink = get_permalink($replytopost);
			$replytotitle = get_the_title($replytopost);
			
			$info = vtm_pm_getpostmeta($replytopost);
			$to   = esc_attr($info['FromChID'] . ":" . $info['FromCode'] . ":" . $info['FromType']);
			$from = esc_attr($info['ToChID'] . ":" . $info['ToCode'] . ":" . $info['ToType']);
			
			// If return address, auto-add a new contact to the To list
			if ($info['FromChID'] != 0 && !($info['FromCode'] == 'postoffice' && get_option( 'vtm_pm_ic_postoffice_enabled', '0' ) == 0)) {
				if (!vtm_pm_isinaddrbook($info['FromCode'], $vtmglobal['characterID'])) {
					$extra[$info['FromFull']] = $to;
				}
				
			}
		} 
		// Or set who post is To
		elseif (isset($_GET['characterID']) ) {
			$tocode = isset($_GET['code']) ? $_GET['code'] : 'postoffice';
			$totype = isset($_GET['type']) ? $_GET['type'] : 0;
			$to = esc_attr($_GET['characterID'] . ":$tocode:$totype");
		}
		// Or check the To and From settings
		else {
			$notify = vtm_pm_validate_metabox($post);
			if (!empty($notify)) {
				echo "<ul style='color:red;border:1px solid red'>$notify</ul>";
			}
		}
		
		//echo "<p>To: $to</p>";
		//echo "<p>From: $from</p>";
		
				
		echo "<p>";
		//print_r($addressbook);
		echo "<label><strong>To:</strong> </label><select name='vtm_pm_to'>";
		echo "<option value='0:0:0'>[Select recipient]</option>";
		$addrcount = 0;
		foreach ($addressbook as $address) {
			if ($characterID != $address->CHARACTER_ID) {
				$title = $address->charactername;
				if ($address->charactername != $address->NAME)
					$title .= ": " . $address->NAME;
				if ($address->ADDRESSBOOK == 'Post Office')
					$title .= " (" . get_option( 'vtm_pm_ic_postoffice_location', 'Post Office') . ")";
				else
					$title .= " (" . $address->PM_CODE . ")";
				
				$code = $address->PM_TYPE_ID == 0 ? 'postoffice' : $address->PM_CODE;
				$value = esc_attr(implode(":", array($address->CHARACTER_ID, 
					$code, $address->PM_TYPE_ID)));
				
				echo "<option value='$value' " . selected($value, $to, false) . ">" . 
				vtm_formatOutput($title) . "</option>";
				
				$addrcount++;
			}
		}
		if (count($extra) > 0) {
			foreach ($extra as $title => $value) {
				echo "<option value='$value' " . selected($value, $to, false) . ">" . 
				vtm_formatOutput($title) . "</option>";
			}
		}
		echo "</select>";
		$link = admin_url('edit.php?post_type=vtmpm&amp;page=vtmpm_addresses');
		if (($addrcount + count($extra)) == 0) {
			echo "There is no one in your <a href='$link'>Addressbook</a>. Please add someone to select them as a recipient.";
		} else {
			echo "Select a message recipient from your <a href='$link'>Addressbook</a>";
		}
		echo "<br />";
		echo "<label><strong>From:</strong> </label><select name='vtm_pm_from'>";
		echo "<option value='$fromchid:postoffice:0'>Yourself with no return address</option>";
		echo "<option value='anonymous:postoffice:0'>Anonymous with no return address</option>";
		
		foreach ($myaddresses as $address) {
			$title = "";
			if (vtm_isST()) {
				$title .= vtm_pm_getchfromid($address->CHARACTER_ID) . ": ";
			}
			
			$title .= $address->NAME . " (" . $address->PM_CODE . ")";
			
			$code = $address->PM_TYPE_ID == 0 ? 'postoffice' : $address->PM_CODE;
			$value = esc_attr(implode(":", array($address->CHARACTER_ID, 
				$code, $address->PM_TYPE_ID)));

			echo "<option value='$value' " . selected($value, $from, false) .
				">" . vtm_formatOutput($title) . "</option>";
		}
		echo "</select>";
		echo "Select how you are contacting the recipient. ";
		if (count($myaddresses) == 0) {
			$link = admin_url('edit.php?post_type=vtmpm&amp;page=vtmpm_mydetails');
			echo "You will need to add your <a href='$link'>Contact Details</a> if you want additional options.";
		}
		echo "</p>";
		if (isset($replytolink)) {
			echo "<p><label><strong>Replying to:</strong> </label><a href='$replytolink'>$replytotitle</a></p>";
		}
		
		//print_r($addressbook);
	}
	// Display the Meta Box
	// --------------------------------------------
	function vtm_pm_metabox_send_callback($post, $args = array()) {
		global $vtmglobal;
		
		$post_type = $post->post_type;
		$post_type_object = get_post_type_object($post_type);
		$can_publish = current_user_can($post_type_object->cap->publish_posts);

		echo "<p>A copy of the message will be sent to the Storytellers.</p>";
		
?>
<div class="submitbox" id="submitpost">

<div class="misc-pub-section misc-pub-post-status">
<?php _e( 'Message Status:' ) ?> <span id="post-status-display"><?php

		switch ( $post->post_status ) {
			case 'private':
				_e('Privately Published');
				break;
			case 'publish':
				_e('Published');
				break;
			case 'future':
				_e('Scheduled');
				break;
			case 'pending':
				_e('Pending Review');
				break;
			case 'draft':
			case 'auto-draft':
				_e('Draft');
				break;
		}
?>
</span> (<?php echo get_post_meta( $post->ID, 'vtmpm_status', true );?>)
</div><!-- .misc-pub-section -->

<div id="minor-publishing">

<?php // Hidden submit button early on so that the browser chooses the right button when form is submitted with Return key ?>
<div style="display:none;">
<?php submit_button( __( 'Save' ), '', 'save' ); ?>
</div>
		

<div id="minor-publishing-actions">
<div id="save-action">
<?php if ( 'publish' != $post->post_status && 
	'future' != $post->post_status && 
	'pending' != $post->post_status ) { ?>
<input <?php if ( 'private' == $post->post_status ) { ?>style="display:none"<?php } ?> type="submit" name="save" id="save-post" value="<?php esc_attr_e('Save Draft'); ?>" class="button" />
<span class="spinner"></span>
<?php } elseif ( 'pending' == $post->post_status && $can_publish ) { ?>
<input type="submit" name="save" id="save-post" value="<?php esc_attr_e('Save as Pending'); ?>" class="button" />
<span class="spinner"></span>
<?php } ?>
</div>
<?php 
// Preview
if ( is_post_type_viewable( $post_type_object ) ) : ?>
<div id="preview-action">
<?php
$preview_link = esc_url( get_preview_post_link( $post ) );
if ( 'publish' == $post->post_status ) {
	$preview_button = __( 'Preview Changes' );
} else {
	$preview_button = __( 'Preview' );
}
?>
<a class="preview button" href="<?php echo $preview_link; ?>" target="wp-preview-<?php echo (int) $post->ID; ?>" id="post-preview"><?php echo $preview_button; ?></a>
<input type="hidden" name="wp-preview" id="wp-preview" value="" />
</div>
<?php endif; // public post type ?>
<?php
/**
 * Fires before the post time/date setting in the Publish meta box.
 *
 * @since 4.4.0
 *
 * @param WP_Post $post WP_Post object for the current post.
 */
		do_action( 'post_submitbox_minor_actions', $post );
?>
<div class="clear"></div>
</div><!-- #minor-publishing-actions -->

<div id="misc-publishing-actions">

<?php

/**
 * Fires after the post time/date setting in the Publish meta box.
 *
 * @since 2.9.0
 * @since 4.4.0 Added the `$post` parameter.
 *
 * @param WP_Post $post WP_Post object for the current post.
 */
do_action( 'post_submitbox_misc_actions', $post );
?>
</div>
<div class="clear"></div>
</div>

<div id="major-publishing-actions">
<?php
/**
 * Fires at the beginning of the publishing actions section of the Publish meta box.
 *
 * @since 2.7.0
 */
do_action( 'post_submitbox_start' );
?>
<div id="delete-action">
<?php
if ( current_user_can( "delete_post", $post->ID ) ) {
	if ( !EMPTY_TRASH_DAYS )
		$delete_text = __('Delete Permanently');
	else
		$delete_text = __('Move to Trash');
	?>
<a class="submitdelete deletion" href="<?php echo get_delete_post_link($post->ID); ?>"><?php echo $delete_text; ?></a><?php
} ?>
</div>

<div id="publishing-action">
<span class="spinner"></span>
<?php
if ( !in_array( $post->post_status, array('publish', 'future', 'private') ) || 0 == $post->ID ) {
	?>
	<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e('Publish') ?>" />
	<?php submit_button( __( 'Send' ), 'primary large', 'publish', false ); ?>
<?php
} else { ?>
		<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e('Update') ?>" />
		<input name="save" type="submit" class="button button-primary button-large" id="publish" value="<?php esc_attr_e( 'Update' ) ?>" />
<?php
} ?>
</div>
<div class="clear"></div>
</div>
</div>
		
<?php
	}
	
	// Add the extra page/menu items
	// --------------------------------------------
	function vtmpm_submenus() {
		
		// STs don't need an addressbook to see addresses
		if (!vtm_isST()) {
			add_submenu_page( 'edit.php?post_type=vtmpm', "Address Book", 
				"Address Book", "read", 'vtmpm_addresses',
				"vtmpm_render_address_book" );
		}
		add_submenu_page( 'edit.php?post_type=vtmpm', "My Addresses", 
			"Contact Details", "read", 'vtmpm_mydetails',
			"vtmpm_render_my_details" );
	}
	add_action('admin_menu' , 'vtmpm_submenus'); 

	// Display the address book
	// --------------------------------------------
	function vtmpm_render_address_book() {
		global $vtmglobal;
		$current_user = wp_get_current_user();
		$vtmglobal['characterID'] = vtm_establishCharacterID($current_user->user_login);

		echo "<h3>Addressbook</h3>";
		
		?><p>Your addressbook lists all the characters available for you to send a message to.</p>
		<p>Contact details that have been made public by other characters are automatically listed.  You can
		add additional contact details where the character has provided you their 'code'.  This code represents
		the character's phone number or address and you will need it to be able to sent them a message.</p>
		<?php
		
		if ($vtmglobal['characterID'] > 0) {
			$testListTable = new vtmclass_pm_addressbook_table();
			$doaction = vtm_pm_addressbook_input_validation('address');
			
			if ($doaction == "add-address") {
				$testListTable->add();
			}
			if ($doaction == "save-address") {
				$testListTable->edit();				
			}
			
			vtm_render_pm_addressbook_add_form('address', $doaction);
			
			$testListTable->prepare_items();
			$current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
			$current_url = remove_query_arg( 'action', $current_url );
			?>
			<form id="address-filter" method="get" action='<?php print htmlentities($current_url); ?>'>
				<input type="hidden" name="page" value="<?php print $_REQUEST['page'] ?>" />
				<input type="hidden" name="post_type" value="<?php print $_REQUEST['post_type'] ?>" />
				<?php $testListTable->display() ?>
			</form>	
			<?php
		} else {
			echo "<p>You do not have a character associated with this Wordpress account.</p>";
		}		
	}

	// Display the My Addresses page
	// --------------------------------------------
	function vtmpm_render_my_details (){
		global $vtmglobal;
		$current_user = wp_get_current_user();
		$vtmglobal['characterID'] = vtm_establishCharacterID($current_user->user_login);

		echo "<h3>My Contact Details</h3>";
		
		?><p>In this section, you should enter all the methods of communication that other characters
		might use to contact you.  It includes any mobile phone, dead drop locations, places of business, etc.</p>
		<p>If you wish, you can set the address/number to be public.  That will allow anyone to contact you through
		that method.  If you keep it private then you will have to give them the 'code' yourself so that they can
		add it to their Addressbook.</p>
		<p>These contact details are also needed for sending messages.  For example, if you are phoning another
		character, you will need to call them from another phone number to do so.</p>
		<?php	
		if ($vtmglobal['characterID'] > 0 || vtm_isST()) {
			$testListTable = new vtmclass_pm_address_table();
			$doaction = vtm_pm_address_input_validation('address');
			
			if ($doaction == "add-address") {
				$testListTable->add();
			}
			if ($doaction == "save-address") {
				$testListTable->edit();				
			}
			
			vtm_render_pm_address_add_form('address', $doaction);
			
			$testListTable->prepare_items();
			$current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
			$current_url = remove_query_arg( 'action', $current_url );
			?>
			<form id="address-filter" method="get" action='<?php print htmlentities($current_url); ?>'>
				<input type="hidden" name="page" value="<?php print $_REQUEST['page'] ?>" />
				<input type="hidden" name="post_type" value="<?php print $_REQUEST['post_type'] ?>" />
				<?php $testListTable->display() ?>
			</form>	
			<?php
		} 
		else {
			echo "<p>You do not have a character associated with this Wordpress account.</p>";
		}
		
	}
	
	// Display the form for adding My Addresses
	// --------------------------------------------
	function vtm_render_pm_address_add_form($type, $addaction) {
		global $vtmglobal;
		global $wpdb;
		
		$id   = isset($_REQUEST[$type]) ? $_REQUEST[$type] : '';
		
		if ('fix-' . $type == $addaction) {
			$name    = $_REQUEST[$type . "_name"];
			$desc    = $_REQUEST[$type . "_desc"];
			$visible = $_REQUEST[$type . '_visible'];
			$code    = $_REQUEST[$type . '_code'];
			$pm_type_id = $_REQUEST[$type . '_pmtype'];
			$default = $_REQUEST[$type . '_default'];
			$characterID = $_REQUEST[$type . "_charid"];
			
			$nextaction = $_REQUEST['action'];

		} elseif ('edit-' . $type == $addaction) {
			$sql = "SELECT * FROM " . VTM_TABLE_PREFIX . "CHARACTER_PM_ADDRESS WHERE ID = %s";
			$sql = $wpdb->prepare($sql, $id);
			$data =$wpdb->get_row($sql);
			
			$name       = $data->NAME;
			$desc       = $data->DESCRIPTION;
			$visible    = $data->VISIBLE;
			$code       = $data->PM_CODE;
			$pm_type_id = $data->PM_TYPE_ID;
			$default    = $data->ISDEFAULT;
			$characterID = $data->CHARACTER_ID;
			
			$nextaction = "save";

		} else {
			$name = "";
			$desc = "";
			$code = "";
			$visible= "N";
			$pm_type_id = 1;
			$default = 'N';
			$characterID = 0;
			
			$nextaction = "add";
		}

		// override character ID if this is a logged in character
		if (!vtm_isST()) {
			$characterID = $vtmglobal['characterID'];
		}
		
		$current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
		$current_url = remove_query_arg( 'action', $current_url );
		?>
		<form id="new-<?php print $type; ?>" method="post" action='<?php print htmlentities($current_url); ?>'>
			<input type="hidden" name="<?php print $type; ?>_id" value="<?php print $id; ?>"/>
			<input type="hidden" name="action" value="<?php print $nextaction; ?>" />
			<input type="hidden" name="characterID" value="<?php print $characterID; ?>" />
			<table>
			<?php
			if (vtm_isST()) {
			?><tr>
				<td>Character Name:</td>
				<td colspan=3>
					<select name="<?php print $type; ?>_charid">
						<?php
							foreach (vtm_get_characters() as $ch) {
								print "<option value='{$ch->ID}' ";
								($ch->ID == $characterID) ? print "selected" : print "";
								echo ">" . vtm_formatOutput($ch->NAME) . "</option>";
							}
						?>
					</select>
				</td>
			</tr><?php
			} else {
				?>
				<input type="hidden" name="<?php print $type; ?>_charid" value="<?php print $characterID; ?>" />
				<?php
			}
			?><tr>
				<td>Public Name:</td>
				<td><input type="text" name="<?php print $type; ?>_name" value="<?php print vtm_formatOutput($name); ?>" size=20 /></td>
				<td>Type:</td>
				<td>
					<select name="<?php print $type; ?>_pmtype">
						<?php
							foreach (vtm_get_pm_types() as $pmtype) {
								print "<option value='{$pmtype->ID}' ";
								($pmtype->ID == $pm_type_id) ? print "selected" : print "";
								echo ">" . vtm_formatOutput($pmtype->NAME) . "</option>";
							}
						?>
					</select>
				</td>
			</tr>
			<tr>
				<td>Code/Number:</td>
				<td><input type="text" name="<?php print $type; ?>_code" value="<?php print vtm_formatOutput($code); ?>" size=20 /></td>
				<td>Show on public addressbook:</td>
				<td>
					<select name="<?php print $type; ?>_visible">
						<option value="N" <?php selected($visible, "N"); ?>>No</option>
						<option value="Y" <?php selected($visible, "Y"); ?>>Yes</option>
					</select>
				</td>
			</tr>
			<tr>
				<td>Description:</td>
				<td><textarea name="<?php print $type; ?>_desc"><?php print vtm_formatOutput($desc); ?></textarea></td> 
				<td>Default for sending messages:</td>
				<td>
					<select name="<?php print $type; ?>_default">
						<option value="N" <?php selected($default, "N"); ?>>No</option>
						<option value="Y" <?php selected($default, "Y"); ?>>Yes</option>
					</select>
				</td>
			</tr>
			</table>
			<input type="submit" name="save_<?php print $type; ?>" class="button-primary" value="<?php echo ucfirst($nextaction); ?>" />
		</form>
		<?php
		
	}
	
	// Display the form for adding to your addressbook
	// --------------------------------------------
	function vtm_render_pm_addressbook_add_form($type, $addaction) {
		global $vtmglobal;
		global $wpdb;
		
		$id   = isset($_REQUEST[$type]) ? $_REQUEST[$type] : '';
		$characterID = $vtmglobal['characterID'];
		
		if ('fix-' . $type == $addaction) {
			$name    = $_REQUEST[$type . "_name"];
			$desc    = $_REQUEST[$type . "_desc"];
			$code    = vtm_sanitize_pm_code($_REQUEST[$type . '_code']);
			$tableID = $_REQUEST[$type . "_id"];
			
			$nextaction = $_REQUEST['action'];

		} elseif ('edit-' . $type == $addaction) {
			$sql = "SELECT * FROM " . VTM_TABLE_PREFIX . "CHARACTER_PM_ADDRESSBOOK WHERE ID = %s";
			$sql = $wpdb->prepare($sql, $id);
			$data =$wpdb->get_row($sql);
			
			$name       = $data->NAME;
			$desc       = $data->DESCRIPTION;
			$code       = $data->PM_CODE;
			$tableID 	= $data->ID;
			
			$nextaction = "save";

		} 
		elseif (isset($_REQUEST['code'])) {
			if ($_REQUEST['from'] > 0) {
				$ch = vtm_pm_getchfromid($_REQUEST['from']);
				$pmtype = vtm_get_pm_typefromid($_REQUEST['type']);
				$name = "$ch's " . ucfirst($pmtype);
			} else {
				$name = "";
			}
			$desc = "";
			$code = $_REQUEST['code'];
			$tableID = 0;
			
			$nextaction = "add";
		}
		else {
			$name = "";
			$desc = "";
			$code = "";
			$tableID = 0;
			
			$nextaction = "add";
		}
		
		$current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
		$current_url = remove_query_arg( 'action', $current_url );
		$current_url = remove_query_arg( 'code', $current_url );
		$current_url = remove_query_arg( 'type', $current_url );
		$current_url = remove_query_arg( 'from', $current_url );
		?>
		<form id="new-<?php print $type; ?>" method="post" action='<?php print htmlentities($current_url); ?>'>
			<input type="hidden" name="<?php print $type; ?>_id" value="<?php print $id; ?>"/>
			<input type="hidden" name="action" value="<?php print $nextaction; ?>" />
			<input type="hidden" name="characterID" value="<?php print $characterID; ?>" />
			<input type="hidden" name="<?php print $type; ?>_id" value="<?php print $tableID; ?>" />
			<table>
			<tr>
				<td>Name:</td>
				<td><input type="text" name="<?php print $type; ?>_name" value="<?php print vtm_formatOutput($name); ?>" size=20 /></td>
				<td>Code/Number:</td>
				<td><input type="text" name="<?php print $type; ?>_code" value="<?php print vtm_formatOutput($code); ?>" size=20 /></td>
			</tr>
			<tr>
				<td>Description:</td>
				<td colspan=3><textarea name="<?php print $type; ?>_desc"><?php print vtm_formatOutput($desc); ?></textarea></td> 
			</tr>
			</table>
			<input type="submit" name="save_<?php print $type; ?>" class="button-primary" value="<?php echo ucfirst($nextaction); ?>" />
		</form>
		<?php
		
	}

	// Validate My Addresses
	// --------------------------------------------
	function vtm_pm_address_input_validation($type) {
		global $wpdb;
		global $vtmglobal;
		
		$doaction = '';
		
		if (!empty($_REQUEST['action']) && $_REQUEST['action'] == 'edit')
			$doaction = "edit-$type";

		if (!empty($_REQUEST[$type . '_name'])){
		
			$doaction = $_REQUEST['action'] . "-" . $type;
			
			if (empty($_REQUEST[$type . '_desc']) || $_REQUEST[$type . '_desc'] == "") {
				$doaction = "fix-$type";
				echo "<p style='color:red'>ERROR: Description is missing</p>";
			}
			if (empty($_REQUEST[$type . '_code']) || $_REQUEST[$type . '_code'] == "") {
				$doaction = "fix-$type";
				echo "<p style='color:red'>ERROR: Phone number/postcode/zipcode is missing</p>";
			} else {
				$code = vtm_sanitize_pm_code($_REQUEST[$type . '_code']);
				
				// CODE MUST BE UNIQUE
				
				if ($_REQUEST['action'] == 'save') {
					// exclude current entry from check
					$sql = "SELECT COUNT(ID) 
						FROM " . VTM_TABLE_PREFIX . "CHARACTER_PM_ADDRESS
						WHERE PM_CODE = %s AND ID != %s";
					$sql = $wpdb->prepare($sql, $code, $_REQUEST[$type . '_id']);
				} else {
					$sql = "SELECT COUNT(ID) 
						FROM " . VTM_TABLE_PREFIX . "CHARACTER_PM_ADDRESS
						WHERE PM_CODE = %s";
					$sql = $wpdb->prepare($sql, $code);
				}
				
				//echo "action: {$_REQUEST['action']}, SQL: $sql";
				if ($wpdb->get_var($sql) > 0) {
					$doaction = "fix-$type";
					echo "<p style='color:red'>ERROR: Phone number/postcode/zipcode already in use. Please select another.</p>";
				}
			}
				
		}
		
		return $doaction;

	}

	// Validate Addressbook entry
	// --------------------------------------------
	function vtm_pm_addressbook_input_validation($type) {
		global $wpdb;
		global $vtmglobal;
		
		$doaction = '';
		
		if (!empty($_REQUEST['action']) && $_REQUEST['action'] == 'edit')
			$doaction = "edit-$type";

		if (!empty($_REQUEST[$type . '_name'])){
		
			$doaction = $_REQUEST['action'] . "-" . $type;
			
			if (empty($_REQUEST[$type . '_desc']) || $_REQUEST[$type . '_desc'] == "") {
				$doaction = "fix-$type";
				echo "<p style='color:red'>ERROR: Description is missing</p>";
			}
			if (empty($_REQUEST[$type . '_code']) || $_REQUEST[$type . '_code'] == "") {
				$doaction = "fix-$type";
				echo "<p style='color:red'>ERROR: Code is missing</p>";
			} else {
				$code = vtm_sanitize_pm_code($_REQUEST[$type . '_code']);
				// Can we match the code up?
				$sql = "SELECT COUNT(ID) 
					FROM " . VTM_TABLE_PREFIX . "CHARACTER_PM_ADDRESS
					WHERE PM_CODE = %s AND DELETED = 'N'";
				$sql = $wpdb->prepare($sql, $code);
				if ($wpdb->get_var($sql) == 0) {
					$doaction = "fix-$type";
					echo "<p style='color:red'>ERROR: That code does not exist or has been removed</p>";
				}
				else {
					// Is it already visible?
					$sql = "SELECT COUNT(ID) 
						FROM " . VTM_TABLE_PREFIX . "CHARACTER_PM_ADDRESS
						WHERE PM_CODE = %s AND VISIBLE = 'Y' AND DELETED = 'N'";
					$sql = $wpdb->prepare($sql, $code);
					if ($wpdb->get_var($sql) > 0) {
						$doaction = "fix-$type";
						echo "<p style='color:red'>ERROR: That code is a public address and already listed</p>";
					}
					
					// Is it one of your own?
					$sql = "SELECT COUNT(ID) 
						FROM " . VTM_TABLE_PREFIX . "CHARACTER_PM_ADDRESS
						WHERE PM_CODE = %s AND CHARACTER_ID = %s AND DELETED = 'N'";
					$sql = $wpdb->prepare($sql, $code, $vtmglobal['characterID']);
					if ($wpdb->get_var($sql) > 0) {
						$doaction = "fix-$type";
						echo "<p style='color:red'>ERROR: That is one of your own codes</p>";
					}
				}
				
				
			}
		}
		
		return $doaction;

	}

	// Report issues on post meta data
	// --------------------------------------------
	function vtm_pm_validate_metabox($post) {
		global $wpdb;

		$tochid   = get_post_meta( $post->ID, '_vtmpm_to_characterID', true );
		$tocode   = get_post_meta( $post->ID, '_vtmpm_to_code', true );
		$totype   = get_post_meta( $post->ID, '_vtmpm_to_type', true );
		$fromchid = get_post_meta( $post->ID, '_vtmpm_from_characterID', true );
		$fromcode = get_post_meta( $post->ID, '_vtmpm_from_code', true );
		$fromtype = get_post_meta( $post->ID, '_vtmpm_from_type', true );
		
		$tocode   = $totype == 0   ? 'postoffice' : $tocode;
		$fromcode = $fromtype == 0 ? 'postoffice' : $fromcode;
		
		$to   = esc_attr($tochid . ":" . $tocode . ":" . $totype);
		$from = esc_attr($fromchid . ":" . $fromcode . ":" . $fromtype);
			
		$output = "";
		
		// must select a To option 
		if ($totype == 0 && $tochid == 0 && $tocode == 0) {
			$output = "<li>Please select a recipient</li>";
		}
		// anytype/one can sent TO the postoffice
		elseif ($totype == 0) {
			$output = "";
		}
		// sending anonymously? (unless you are an ST)
		elseif ($fromtype == 0 && !vtm_isST()) {
			// Yes
			$allow = $wpdb->get_var($wpdb->prepare(
				"SELECT ISANONYMOUS FROM " . VTM_TABLE_PREFIX . "PM_TYPE
				WHERE ID = %s", $totype));
			
			if ($allow == 'N') {
			$to = $wpdb->get_var($wpdb->prepare("SELECT NAME FROM " . VTM_TABLE_PREFIX . "PM_TYPE WHERE ID = %s", $totype));
			$to_ana = strtoupper(substr($to, 0, 1)) == 'A' ? 'an' : 'a';
				$output .= "<li>You cannot send to $to_ana $to without providing a return address</li>";
			}
		} 
		// No - to and from must be same type/method 
		elseif ($fromtype != $totype && $fromtype != 0) {
			$from = $wpdb->get_var($wpdb->prepare("SELECT NAME FROM " . VTM_TABLE_PREFIX . "PM_TYPE WHERE ID = %s", $fromtype));
			$to = $wpdb->get_var($wpdb->prepare("SELECT NAME FROM " . VTM_TABLE_PREFIX . "PM_TYPE WHERE ID = %s", $totype));
			
			$from_ana = strtoupper(substr($from, 0, 1)) == 'A' ? 'an' : 'a';
			$to_ana = strtoupper(substr($to, 0, 1)) == 'A' ? 'an' : 'a';
			
			$output .= "<li>You cannot send a message from $from_ana $from to $to_ana $to</li>";
		}
		
		return $output;
	}

	// Save meta data and check the post status transition
	// Restore to Draft any posts with fails on publish_post/publish_vtmpm
	// --------------------------------------------
	function vtm_pm_check_post_transition( $new_status, $old_status, $post ) {
		
		if ($post->post_type != 'vtmpm')
			return;
		
		// Sanitize user input.
		if (isset($_POST['vtm_pm_to'])) {
			$to   = explode(":",sanitize_text_field( $_POST['vtm_pm_to'] ));
			$from = explode(":",sanitize_text_field( $_POST['vtm_pm_from'] ));

			update_post_meta( $post->ID, '_vtmpm_to_characterID', $to[0] );
			update_post_meta( $post->ID, '_vtmpm_to_code', $to[1] );
			update_post_meta( $post->ID, '_vtmpm_to_type', $to[2] );
			update_post_meta( $post->ID, '_vtmpm_from_characterID', $from[0] );
			update_post_meta( $post->ID, '_vtmpm_from_code', $from[1] );
			update_post_meta( $post->ID, '_vtmpm_from_type', $from[2] );
		} else {
			$to = array(0,0,0);
			$from = array(0,0,0);
		}
		
		$msg = "";
		
		// Update the meta field in the database.
		update_post_meta( $post->ID, '_vtmpm_from_actual_authorID', $post->post_author );

		if ( $new_status == 'publish') {
			$notify = vtm_pm_validate_metabox($post);
			
			if ($old_status != 'publish' && $old_status != 'trash') {
				update_post_meta( $post->ID, '_vtmpm_to_status', 'unread' );
			}
			
			if (!empty($notify)) {
				$msg = "Publish failed";
				vtm_change_post_status($post->ID, 'draft');
				update_post_meta( $post->ID, '_vtmpm_from_status', 'draft' );
			} 
			else {
				$msg = "Message sent";
				update_post_meta( $post->ID, '_vtmpm_from_status', 'sent' );

				if ($old_status != 'trash') {
					$viewlink    = get_permalink( $post->ID );
					
					$meta = vtm_pm_getpostmeta($post->ID);
					$from      = $meta['From'];
					$recipientID = $meta['ToChID'];
					$recipient   = vtm_pm_getchfromid($recipientID);
					
					// Send email to recipient
					$subject = "You have a new message: " . $post->post_title;
					$email   = vtm_get_character_email($recipientID);
					$body    = "<p>Hello $recipient,</p>
					<p>You have a new message from $from: <a href='$viewlink'>$viewlink</a></p>";
				
					vtm_send_email($email, $subject, $body);
					
					// Send email (with contents) to STs
					$subject = "Message sent from $from ({$meta['Author']}) to $recipient";
					$email = get_option( 'vtm_replyto_address', get_option( 'vtm_chargen_email_from_address', get_bloginfo('admin_email') ) );
					$body = "<p><strong>Subject</strong>: " . $post->post_title . "</p>
					" . apply_filters('the_content', $post->post_content) . "
					<p>View message on the site: <a href='$viewlink'>$viewlink</a></p>";
				
					vtm_send_email($email, $subject, $body);
				
				}
			}
		} 
		elseif ( $new_status == 'trash' ) {
			
			// Actually trash the message if user is an ST 
			// or it was a draft post
			if (!vtm_isST() && $old_status != 'draft') {
				$current_user = wp_get_current_user();
				$msg = "Trashed!?";
				// was this a message the logged in user sent?
				if ($post->post_author == $current_user->ID) {
					//update_post_meta( $post->ID, '_vtmpm_from_status', 'trash' );
				} else {
					update_post_meta( $post->ID, '_vtmpm_to_status', 'trash' );
				}
				
				// don't actually trash it
				vtm_change_post_status($post->ID, $old_status);
			}
		}
		else {
			$msg = "Message updated $old_status to $new_status";
			//update_post_meta( $post->ID, '_vtmpm_to_status', 'draft' );
		}
		update_post_meta( $post->ID, 'vtmpm_status', $msg);
	}
	add_action( 'transition_post_status', 'vtm_pm_check_post_transition', 15, 3 );

	// redirect to the view post after publishing/sending
	function vtm_pm_post_redirect($location) {
		if (isset($_POST['save']) || isset($_POST['publish'])) {
			global $post;
			if (get_post_meta($post->ID, '_vtmpm_from_status', true ) == 'sent') {
				$location = get_permalink($post->ID);
			}
		}
		elseif ($_POST['save']) {
			
		}
		return $location;
	}
	add_filter('redirect_post_location', 'vtm_pm_post_redirect');
	
	//function <function>( $post_id, $post, $update ) {
	//$post_id - The ID of the post you'd like to change.
	//$status -  The post status publish|pending|draft|private|static|object|attachment|inherit|future|trash.
	function vtm_change_post_status($post_id,$status){
		$current_post = get_post( $post_id, 'ARRAY_A' );
		$current_post['post_status'] = $status;
		wp_update_post($current_post, true);
		if (is_wp_error($post_id)) {
			$errors = $post_id->get_error_messages();
			foreach ($errors as $error) {
				echo $error;
			}
		}
	}

	// Don;t show trash undo message for players as it won't work
	// because we don't really trash the message
	function vtm_pm_style() {
		if (!vtm_isST() && is_admin() && get_query_var('post_type') == 'vtmpm') {
			wp_enqueue_style('vtmpm-style', plugins_url('css/style-hidemessage.css',dirname(__FILE__)));
		}
	}
	add_action('admin_enqueue_scripts', 'vtm_pm_style');
	
	// Filter functions
	// --------------------------------------------
	// Get the basic query
	function vtm_pm_search_filter($query) {
		if (isset($_GET['post_type'])) {
			$type = $_GET['post_type'];
		} else {
			$type = 'post';
		}
		if ( 'vtmpm' == $type && !vtm_isST() && $query->is_main_query() ) {
			$current_user = wp_get_current_user();
			$chid = vtm_pm_getchidfromauthid($current_user->ID);
			
			$tostatus   = get_query_var('tostatus');
			$fromstatus = get_query_var('fromstatus');
			$poststatus = get_query_var('post_status');
			
			if (empty($tostatus) && empty($fromstatus) && empty($poststatus)) {
				$tostatus = 'unread';
			}
			
			if (!empty($fromstatus) && $fromstatus == 'sent') {
				// Viewing sent messages
				$query->set('post_status','publish');
				$query->set('author',$current_user->ID);
			}
			elseif (!empty($tostatus)) {
				// Viewing read/unread messages
				$query->set('post_status','publish');
				
				$meta_query = array(
						'relation' => 'AND',
						array(
							'key'=>'_vtmpm_to_characterID',
							'value'=> "$chid",
							'compare'=>'==',
						),
						array(
							'key'=>'_vtmpm_to_status',
							'value'=> "$tostatus",
							'compare'=>'==',
						),
					);

				$query->set('meta_query',$meta_query);

			}
			elseif (!empty($poststatus) && $poststatus == 'draft') {
				// Viewing draft messages
				$query->set('post_status','draft');
				$query->set('author',$current_user->ID);
			}
			else {
				// All messages
				//$query->set('meta_query',vtm_pm_get_basic_metaquery());
			}
		}
	}
	add_action('pre_get_posts','vtm_pm_search_filter');
	
/* 	function vtm_pm_get_basic_metaquery() {
		$current_user = wp_get_current_user();
		$chid = vtm_pm_getchidfromauthid($current_user->ID);
		
		return array(
			'relation' => 'OR',
			array(
				'key'=>'_vtmpm_to_characterID',
				'value'=> "$chid",
				'compare'=>'==',
			),
			array(
				'key'=>'_vtmpm_from_actual_authorID',
				'value'=> $current_user->ID,
				'compare'=>'==',
			),
		);

	} */
	
/* 	function vtm_pm_search_filter_where( $where ) {
		global $pagenow;
		if (isset($_GET['post_type'])) {
			$type = $_GET['post_type'];
		} else {
			$type = 'post';
		}
		if ( 'vtmpm' == $type && is_admin() && is_main_query()) {
			global $wpdb;	

			$tostatus   = get_query_var('tostatus');
			$fromstatus = get_query_var('fromstatus');
						
			if (!empty($tostatus)) {
				$where .= " AND (";
				$where .= "({$wpdb->postmeta}.meta_key = '_vtmpm_to_status' AND
							{$wpdb->postmeta}.meta_value = '$tostatus')";
				$where .= ")";
			}
			if (!empty($fromstatus)) {
				$where .= " AND (";
				$where .= "({$wpdb->postmeta}.meta_key = '_vtmpm_from_status' AND
							{$wpdb->postmeta}.meta_value = '$fromstatus')";
				$where .= ")";
			}
			
			//echo "<p>WHERE: $where</p>";
		}
		return $where;
	}
 */	//add_filter( 'posts_where' , 'vtm_pm_search_filter_where' );

	// And set the counts/categories at the top
	function vtm_pm_get_posts_count( $views ) {
		
		if (vtm_isST())
			return $views;
		
		$current_user = wp_get_current_user();
		$chid = vtm_pm_getchidfromauthid($current_user->ID);

		$post_type = get_query_var('post_type');

		//print_r($views);
		unset($views['publish']);
		unset($views['draft']);
		unset($views['trash']);
		unset($views['mine']);
		unset($views['all']);
		$startview = 'unread';

		$new_views = array(
				//'all'    => __('ZAll'),
				'unread' => __('Unread'),
				'read'   => __('Read'),
				'draft'  => __('Draft'),
				'sent'   => __('Sent'),
				'trash'  => __('Trash')
				);

		foreach( $new_views as $view => $name ) {

			$query = array(
				'post_type'   => $post_type
			);
			
			switch ($view) {
				case 'all':
					$query['all_posts'] = 1;
					$class = ( get_query_var('all_posts') == 1 || 
						($startview == 'all' &&
						 get_query_var('poststatus') == '' &&
						 get_query_var('tostatus') == '' &&
						 get_query_var('fromstatus') == ''
						))
						? ' class="current"' : '';
					$url_query_var = 'all_posts=1';
					break;
				case 'read':
					$query['meta_query'] = array(
						'relation' => 'AND',
						array(
							'key'=>'_vtmpm_to_characterID',
							'value'=> "$chid",
							'compare'=>'==',
						),
						array(
							'key'=>'_vtmpm_to_status',
							'value'=> "read",
							'compare'=>'==',
						),
					);
					$query['post_status'] = 'publish';
					$class = ( get_query_var('tostatus') == 'read' ||
						($startview == 'read' &&
						 get_query_var('poststatus') == '' &&
						 get_query_var('tostatus') == '' &&
						 get_query_var('fromstatus') == ''
						)) ? ' class="current"' : '';
					$url_query_var = 'tostatus=read';
					break;
				case 'unread':
					$query['meta_query'] = array(
						'relation' => 'AND',
						array(
							'key'=>'_vtmpm_to_characterID',
							'value'=> "$chid",
							'compare'=>'==',
						),
						array(
							'key'=>'_vtmpm_to_status',
							'value'=> "unread",
							'compare'=>'==',
						),
					);
					$query['post_status'] = 'publish';
					$class = ( get_query_var('tostatus') == 'unread' ||
						($startview == 'unread' &&
						 get_query_var('poststatus') == '' &&
						 get_query_var('tostatus') == '' &&
						 get_query_var('fromstatus') == ''
						)) ? ' class="current"' : '';
					$url_query_var = 'tostatus=unread';
					break;
				case 'sent':
					$query['post_status'] = 'publish';
					$query['author'] = $current_user->ID;
					$class = ( get_query_var('fromstatus') == 'sent' ||
						($startview == 'sent' &&
						 get_query_var('poststatus') == '' &&
						 get_query_var('tostatus') == '' &&
						 get_query_var('fromstatus') == ''
						)) ? ' class="current"' : '';
					$url_query_var = 'fromstatus=sent';
					break;
				case 'draft':
					$query['post_status'] = 'draft';
					$query['author'] = $current_user->ID;
					$class = ( get_query_var('post_status') == $view ||
						($startview == $view &&
						 get_query_var('poststatus') == '' &&
						 get_query_var('tostatus') == '' &&
						 get_query_var('fromstatus') == ''
						) ) ? ' class="current"' : '';
					$url_query_var = 'post_status='.$view;
					break;
				case 'trash':
					$query['meta_query'] = array(
						'relation' => 'AND',
						array(
							'key'=>'_vtmpm_to_characterID',
							'value'=> "$chid",
							'compare'=>'==',
						),
						array(
							'key'=>'_vtmpm_to_status',
							'value'=> "trash",
							'compare'=>'==',
						),
					);
					$class = ( get_query_var('tostatus') == 'trash' ||
						($startview == 'trash' &&
						 get_query_var('poststatus') == '' &&
						 get_query_var('tostatus') == '' &&
						 get_query_var('fromstatus') == ''
						)) ? ' class="current"' : '';
					$url_query_var = 'tostatus=trash';
					break;
				default:
					$query['post_status'] = $view;
					$class = ( get_query_var('post_status') == $view  ||
						($startview == $view &&
						 get_query_var('poststatus') == '' &&
						 get_query_var('tostatus') == '' &&
						 get_query_var('fromstatus') == ''
						)) ? ' class="current"' : '';
					$url_query_var = 'post_status='.$view;
			}
			
			
			$result = new WP_Query($query);
			
			// if ($view == 'trash') {
				// echo "XXX: $view<br />";
				// print_r($query);
				// print_r($result);
			// }

			if($result->found_posts > 0 || $view == 'unread') {

				$views[$view] = sprintf(
					'<a href="%s"'. $class .'>'.__($name).' <span class="count">(%d)</span></a>',
					admin_url('edit.php?'.$url_query_var.'&post_type='.$post_type),
					$result->found_posts
				);

			} else {

				unset($views[$view]);

			}

		}

		return $views;
	}	
	function vtm_pm_get_posts_count_filter( $query ) {
		//Note that current_user_can('edit_others_posts') check for
		//capability_type like posts, custom capabilities may be defined for custom posts
		if( is_admin() && $query->is_main_query() ) {
			//print_r($query);
			//For standard posts
			add_filter('views_edit-vtmpm', 'vtm_pm_get_posts_count' );
		}
	}
	add_action( 'pre_get_posts', 'vtm_pm_get_posts_count_filter' );
	function vtm_pm_add_query_vars_filter( $vars ){
		$vars[] = "tostatus";
		$vars[] = "fromstatus";
		return $vars;
	}
	add_filter( 'query_vars', 'vtm_pm_add_query_vars_filter' );
	

	function vtm_pm_update_bulk_messages( $bulk_messages, $bulk_counts ) {

		//print_r($bulk_messages);
		$bulk_messages['vtmpm'] = array(
			'updated'   => _n( '%s message updated.', '%s messages updated.', $bulk_counts['updated'] ),
			'locked'    => _n( '%s message not updated, somebody is editing it.', '%s messages not updated, somebody is editing them.', $bulk_counts['locked'] ),
			'deleted'   => _n( '%s message permanently deleted.', '%s messages permanently deleted.', $bulk_counts['deleted'] ),
			'trashed'   => _n( '%s message moved to the Trash.', '%s messages moved to the Trash.', $bulk_counts['trashed'] ),
			'untrashed' => _n( '%s message restored from the Trash.', '%s messages restored from the Trash.', $bulk_counts['untrashed'] ),
		);

		return $bulk_messages;

	}
	add_filter( 'bulk_post_updated_messages', 'vtm_pm_update_bulk_messages', 10, 2 );	

	// General functions
	// --------------------------------------------
	
	function vtm_pm_getaddrfromcode($code) {
		
		if ($code == 'postoffice')
			return get_option( 'vtm_pm_ic_postoffice_location' );
		
		global $wpdb;
		$sql = "SELECT NAME
				FROM " . VTM_TABLE_PREFIX . "CHARACTER_PM_ADDRESS
				WHERE PM_CODE = %s AND DELETED = 'N'";
		$sql = $wpdb->prepare($sql, $code);
		$result = $wpdb->get_var($sql);
		
		return $result;
	}
	function vtm_pm_isinaddrbook($code, $characterID) {
		
		if ($code == 'postoffice')
			return false;
		
		global $wpdb;
		
		// Addresses you have saved
		$sql = "SELECT COUNT(ID)
				FROM " . VTM_TABLE_PREFIX . "CHARACTER_PM_ADDRESSBOOK
				WHERE CHARACTER_ID = %s AND PM_CODE = %s ";
		$sql = $wpdb->prepare($sql, $characterID, $code);
		$saved = $wpdb->get_var($sql);
		
		// Public addresses
		$sql = "SELECT COUNT(ID)
				FROM " . VTM_TABLE_PREFIX . "CHARACTER_PM_ADDRESS
				WHERE PM_CODE = %s 
					AND VISIBLE = 'Y' AND DELETED = 'N'";
		$sql = $wpdb->prepare($sql, $code);
		$public = $wpdb->get_var($sql);
		
		$result = $saved + $public;
		//echo "Result1: $saved + $public = $result ($sql)";
		return ($result > 0);
	}
	function vtm_pm_iscoderemoved($code) {
		
		if ($code == 'postoffice')
			return false;
		
		global $wpdb;
		$sql = "SELECT COUNT(ID)
				FROM " . VTM_TABLE_PREFIX . "CHARACTER_PM_ADDRESS
				WHERE PM_CODE = %s AND DELETED = 'Y'";
		$sql = $wpdb->prepare($sql, $code);
		$result = $wpdb->get_var($sql);
		
		//echo "Result2: $result, SQL: $sql";
		return ($result > 0);
	}
	function vtm_pm_getchfromid($characterID) {
		global $wpdb;
		$sql = "SELECT NAME
				FROM " . VTM_TABLE_PREFIX . "CHARACTER
				WHERE ID = %s";
		$sql = $wpdb->prepare($sql, $characterID);
		
		$name = $wpdb->get_var($sql);
		
		if (!isset($name)) 
			$name = "Anonymous";
		
		return $name;
	}
	function vtm_pm_getchfromauthid($authorID) {
		global $wpdb;
		
		$wordpressid = get_the_author_meta( 'user_login', $authorID );
		
		$sql = "SELECT NAME
				FROM " . VTM_TABLE_PREFIX . "CHARACTER
				WHERE WORDPRESS_ID = %s";
		$sql = $wpdb->prepare($sql, $wordpressid);
		
		$name = $wpdb->get_var($sql);
		
		if (!isset($name)) 
			$name = $wordpressid;
		
		return $name;
	}
	function vtm_pm_getchidfromauthid($authorID) {
		global $wpdb;
		
		$wordpressid = get_the_author_meta( 'user_login', $authorID );
		
		$sql = "SELECT ID
				FROM " . VTM_TABLE_PREFIX . "CHARACTER
				WHERE WORDPRESS_ID = %s";
		$sql = $wpdb->prepare($sql, $wordpressid);
		
		$characterID = $wpdb->get_var($sql);
		
		return $characterID;
	}
	function vtm_pm_render_pmhead($postID, $subjecthtag) {
		$info = vtm_pm_getpostmeta($postID);
		//print_r($info);
		?>
					<header class="entry-header">
					<<?php echo $subjecthtag; ?> class="entry-title"><?php echo get_the_title($postID); ?></<?php echo $subjecthtag; ?>>
					<div class="vtm_pmhead">
						<span class="vtm_pmhead_to">To: <?php echo vtm_formatOutput($info['ToFull']); ?></span>
						<span class="vtm_pmhead_from">From: <?php echo vtm_formatOutput($info['FromFull']); ?></span>
						<span class="vtm_pmhead_sent">Sent: <?php echo get_the_time( get_option( 'date_format' ) ); ?></span>
						<span class="vtm_pmhead_subject">Subject: <?php echo get_the_title($postID); ?></span>
					</div>
					</header>
		<?php
	}
	function vtm_pm_render_pmfoot($postID) {
		global $vtmglobal;
		
		$dellink = get_delete_post_link(get_the_ID());
		$replylink = admin_url('post-new.php?replyto=' . get_the_ID());
		$replylink = add_query_arg('post_type','vtmpm',$replylink );
		$inboxlink = admin_url('edit.php?post_type=vtmpm');
		
		$current_user = wp_get_current_user();
		$meta = vtm_pm_getpostmeta($postID);
		$post = get_post( $postID );
		
		$links = array("<a href='$inboxlink'>Inbox</a>");
		// Add reply link if you weren't the one that sent it
		// and it isn't an anonymous post (who would you reply to?)
		if ($post->post_author != $current_user->ID && 
			$meta['FromChID'] != 'anonymous' &&
			!($meta['FromCode'] == 'postoffice' && get_option( 'vtm_pm_ic_postoffice_enabled', '0' ) == 0)) {
			$links[] = "<a href='$replylink'>Reply</a>";
		}
		// Add trash link if it was sent to you
		if ($vtmglobal['characterID'] == $meta['ToChID']) {
			$links[] = "<a href='$dellink'>Trash</a>";
		}
		
		?>
					<footer class="entry-meta">
						<div class="vtm_pmfoot">
							<?php echo implode(' | ', $links); ?>
						</div>
						<div class="vtm_pmhistory">
						<?php 
							$pid = get_post_meta( get_the_ID(), '_vtmpm_replyto_postid', true );;

							while (isset($pid) && !empty($pid)) {
								$tochid = get_post_meta( $pid, '_vtmpm_to_characterID', true );
								$fromchid = get_post_meta( $pid, '_vtmpm_from_characterID', true );

								if ($vtmglobal['characterID'] == $tochid)
									$pmclass = "vtm_pm_tome";
								elseif ($vtmglobal['characterID'] == $fromchid)
									$pmclass = "vtm_pm_fromme";
								else
									$pmclass = "";
								print "<div class='$pmclass'>"; 
								vtm_pm_render_pmhead($pid, 'h2');
								vtm_pm_render_pmcontent($pid);
								$pid =  get_post_meta( $pid, '_vtmpm_replyto_postid', true );
								print "</div>";
							}
						
						?>
						</div>
					</footer>
		<?php
	}
	function vtm_pm_render_pmmsg() {
		global $wpdb;
		
		$current_user = wp_get_current_user();
		$postID = get_the_ID();
		$chid = vtm_pm_getchidfromauthid($current_user->ID);
		
		// check if user is allowed to read the message 
		//		- either the user sent the message, or 
		//		- it was sent to the user's character
		$readok = 0;
		$post = get_post($postID);
		if ($post->post_author == $current_user->ID) {
			$readok = 1;
		}
		elseif ($chid == get_post_meta( $postID, '_vtmpm_to_characterID', true )) {
			$readok = 1;
		}
		
		if ($readok || vtm_isST()) {
			
			if ($chid == get_post_meta( $postID, '_vtmpm_to_characterID', true )) {
				// mark post as read 
				update_post_meta($postID, '_vtmpm_to_status', 'read' );
			}
			$totypeid   = get_post_meta( $postID, '_vtmpm_to_type', true );
			$fromtypeid = get_post_meta( $postID, '_vtmpm_from_type', true );
			if ($totypeid)
				$pmclass = "vtm_pm_type_" . sanitize_key($wpdb->get_var($wpdb->prepare("SELECT NAME FROM " . VTM_TABLE_PREFIX . "PM_TYPE WHERE ID = '%s'", $totypeid)));
			elseif ($fromtypeid)
				$pmclass = "vtm_pm_type_" . sanitize_key($wpdb->get_var($wpdb->prepare("SELECT NAME FROM " . VTM_TABLE_PREFIX . "PM_TYPE WHERE ID = '%s'", $fromtypeid)));
			else
				$pmclass = "";
			$tochid = get_post_meta( $postID, '_vtmpm_to_characterID', true );
			$fromchid = get_post_meta( $postID, '_vtmpm_from_characterID', true );
			if ($tochid == $chid)
				$pmclass .= " vtm_pm_tome";
			elseif ($fromchid == $chid)	
				$pmclass .= " vtm_pm_fromme";
			
			
			?>
				<div class="vtm_pmmsg <?php echo $pmclass;?>">
			<?php
			vtm_pm_render_pmhead($postID, 'h1');
			vtm_pm_render_pmcontent($postID);
			vtm_pm_render_pmfoot($postID);
			?>
				</div>
			<?php
		} else {
			echo "<p>You do not have permission to read this message</p>";
		}
	}
	function vtm_pm_render_pmcontent($postID) {
		?>
					<div class="entry-content">
						<?php 
						if ($postID == get_the_ID()) {
							the_content();
						} else {
							$post = get_post($postID);
							echo $post->post_content;
						}
						?>
					</div><!-- .entry-content -->
		<?php 			
	}	
	function vtm_pm_getpostmeta($postID) {
		global $vtmglobal;
		
		$current_user = wp_get_current_user();
		if (!isset($vtmglobal['characterID'])) {
			$vtmglobal['characterID'] = vtm_establishCharacterID($current_user->user_login);
		}
		$characterID = $vtmglobal['characterID'];

		$tochid   = get_post_meta( $postID, '_vtmpm_to_characterID', true );
		$fromchid = get_post_meta( $postID, '_vtmpm_from_characterID', true );
		$authorid = get_post_field( 'post_author', $postID );
		$fromtype = get_post_meta( $postID, '_vtmpm_from_type', true );
		$totype   = get_post_meta( $postID, '_vtmpm_to_type', true );
		$replyto  = get_post_meta( $postID, '_vtmpm_replyto_postid', true );

		$toch     = vtm_pm_getchfromid($tochid);
		$fromch   = vtm_pm_getchfromid($fromchid);
		$authorch = vtm_pm_getchfromauthid($authorid);
		$tocode   = get_post_meta( $postID, '_vtmpm_to_code', true );
		$fromcode = get_post_meta( $postID, '_vtmpm_from_code', true );
		$toaddr   = vtm_pm_getaddrfromcode($tocode);
		$fromaddr = vtm_pm_getaddrfromcode($fromcode);
		
		$toaddr   = empty($toaddr) ? $tocode . 'unavailable' : $toaddr;
		$fromaddr = empty($fromaddr) ? $fromcode . 'unavailable' : $fromaddr;

		// User has from address in their addressbook?
		// If not, give them a link to add it 
		if ($fromcode != 'postoffice' &&
			!vtm_pm_isinaddrbook($fromcode, $characterID) &&
			!vtm_pm_iscoderemoved($fromcode)) {
			$addlink = admin_url('edit.php?post_type=vtmpm&amp;page=vtmpm_addresses&amp;code='.$fromcode.
				"&amp;from=$fromchid&amp;type=$fromtype");
			$fromaddrlink = "<a href='$addlink' title='Add to address book'>$fromaddr</a>";
		} 
		elseif ($fromcode == 'postoffice' && get_option( 'vtm_pm_ic_postoffice_enabled', '0' ) == 0) {
			$fromaddrlink = "No return address";
		}
		else {
			$fromaddrlink = $fromaddr;
		}
		
		// anonymous
		$ispmowner = ($authorid == $current_user->ID);
		
		if ($fromchid == 'anonymous') {
			// STs get full information
			if (vtm_isST()) {
				$fromfull = "Anonymous ($authorch) ";
			}
			// if you sent it, you also get full information
			elseif ($ispmowner) {
				$fromfull = "Anonymous ($authorch)";
			}
			// Otherwise
			else {
				$fromfull = "Anonymous";
			}
		}
		// from character deleted - then use post author name
		elseif ($fromchid > 0 && $fromch == 'Anonymous') {
			$wordpressid = get_the_author_meta( 'user_login', $authorid );
			$fromfull = "$wordpressid ($fromaddr)";
		}
		// don't have an address link for your own posts
		elseif ($ispmowner || vtm_isST()) {
			$fromfull = "$fromch ($fromaddr)";
		}
		// otherwise
		else {
			$fromfull = "$fromch ($fromaddrlink)";
		}
		
		// Show if TO character is deleted
		if ($tochid > 0 && $toch == 'Anonymous') {
			$toch = 'Unknown/Deleted';
		}
		
		//echo "ReplyTo: $replyto";
		
		return array(
			'ToChID'   => $tochid,
			'FromChID' => $fromchid,
			'AuthorID' => $authorid,
			'To'       => $toch,
			'From'     => $fromch,
			'Author'   => $authorch,
			'FromCode' => $fromcode,
			'ToCode'   => $tocode,
			'FromFull' => $fromfull,
			'ToFull'   => "$toch ($toaddr)",
			'IsPMOwner'  => $ispmowner,
			'FromType' => $fromtype,
			'ToType'   => $totype,
			'ReplyToPost' => $replyto
		);
	}
	
	// LIST MESSAGES ACTIONS
	// --------------------------------------------
	// Remove edit and quick edit for published pms from row
	function vtm_pm_update_row_actions( $actions, $post ) {
		global $current_screen;
		if( $current_screen->post_type == 'vtmpm' && get_post_status( $post->ID ) == 'publish') {
			unset( $actions['mine'] );
			unset( $actions['inline hide-if-no-js'] );
			
			if (!vtm_isST()) {
				unset( $actions['edit'] );
			
				$current_user = wp_get_current_user();
				if ($post->post_author == $current_user->ID) {
					// i.e. logged in user sent the message 
					unset( $actions['trash'] );
					
					// $read = get_post_meta( $post->ID, '_vtmpm_readstatus', true );
					// $action = $read == 'read' ? 'unread' : 'read' ;
					// $actiontxt = $read == 'read' ? 'Mark Unread' : 'Mark Read' ;

					// $link = add_query_arg('action',$action);
					// $link = add_query_arg('post_type','vtmpm', $link);
					// $link = add_query_arg('post',$post->ID, $link);
					// $link = wp_nonce_url($link, 'vtmpm_readunread');

					// //$link = admin_url("post.php?post={$post->ID}&amp;action=$action&amp;post_type=vtmpm");
					// $actions['read'] = "<a href='$link'>$actiontxt</a>" ;
				} 
				elseif (get_post_meta( $post->ID, '_vtmpm_to_status', true ) == 'trash') {
					$actions['view'] = str_replace(__( 'View' ),__( 'View/Untrash' ),$actions['view']) ;
					unset( $actions['trash'] );
				}
			}
		}
		return $actions;
	}
	add_filter( 'post_row_actions', 'vtm_pm_update_row_actions', 10, 2 );

	// Remove edit from bulk actions
    function vtm_pm_bulk_actions( $actions ){
 		global $current_screen;
		if( $current_screen->post_type == 'vtmpm') {
			unset( $actions[ 'edit' ] );
		}
        return $actions;
    }	
	add_filter( 'bulk_actions-edit-vtmpm', 'vtm_pm_bulk_actions' );

	 // * author_cap_filter()
	 // *
	 // * Filter on the current_user_can() function.
	 // * This function is used to explicitly allow authors to edit contributors and other
	 // * authors posts if they are published or pending.
	 // *
	 // * @param array $allcaps All the capabilities of the user
	 // * @param array $cap     [0] Required capability
	 // * @param array $args    [0] Requested capability
	 // *                       [1] User ID
	 // *                       [2] Associated object ID
	function vtm_pm_author_cap_filter( $allcaps, $cap, $args ) {
		
		// Bail out if we're not asking about a post:
		if ( 'edit_post' != $args[0] )
			return $allcaps;
		
		// Load the post data:
		$post = get_post( $args[2] );
		
		// if (isset($args[2])) {
			// echo "ALL: {$post->post_type} {$post->post_author}<br>";
			// print_r($allcaps);
			// echo "cap<br>";
			// print_r($cap);
			// echo "args<br>";
			// print_r($args);
		// }
		
		// Bail out if it isn't our custom post type
		if ($post->post_type != 'vtmpm')
			return $allcaps;
		
		// Bail out if the user is the post author and hasn't already
		// sent the message:
		if ( $args[1] == $post->post_author && $post->post_status != 'publish')
			return $allcaps;
		
		$allcaps[$cap[0]] = false;
		
		return $allcaps;
	}
	add_filter( 'user_has_cap', 'vtm_pm_author_cap_filter', 10, 3 );



	// function vtm_pm_publish_button_text( $translation, $text ) {
		// if ( $text == 'Publish') {
			// return 'Send Message';
		// }
		// return $translation;
	// }
	// add_filter( 'gettext', 'vtm_pm_publish_button_text', 10, 2 );
	
	// MESSAGE TEMPLATE
	// --------------------------------------------
	function vtm_pm_post_template($single_template) {
		global $post;
		if ($post->post_type == 'vtmpm') {
			$path = locate_template("vtmpm.php");
			if (file_exists($path)) {
				$single_template = $path;
			} else {
				$path = VTM_CHARACTER_URL . '/templates/vtmpm.php';
				if (file_exists($path)) {
					$single_template = $path;
				}
			}
		}
		return $single_template;
	}
	add_filter( 'single_template', 'vtm_pm_post_template' );

	// remove links/menus from the admin bar
    // my-account – link to your account (avatars disabled)
    // my-account-with-avatar – link to your account (avatars enabled)
    // my-blogs – the "My Sites" menu if the user has more than one site
    // get-shortlink – provides a Shortlink to that page
    // edit – link to the Edit/Write-Post page
    // new-content – link to the "Add New" dropdown list
    // comments – link to the "Comments" dropdown
    // appearance – link to the "Appearance" dropdown
    // updates – the "Updates" dropdown
	function vtm_pm_admin_bar() {
		global $wp_admin_bar;
		$wp_admin_bar->remove_menu('edit');
	}
	add_action( 'wp_before_admin_bar_render', 'vtm_pm_admin_bar' );	
	
}

//---------------------------------------------------
// CLASSES
//---------------------------------------------------

class vtmclass_pm_address_table extends vtmclass_MultiPage_ListTable {
   
    function __construct(){
        global $status, $page;
                
        parent::__construct( array(
            'singular'  => 'address',     
            'plural'    => 'addresses',    
            'ajax'      => false        
        ) );
    }
 	function add() {
		global $wpdb;
		$wpdb->show_errors();
		
		if (vtm_isST()) {
			$characterID = $_REQUEST['address_charid'];
		} else {
			$characterID = $_REQUEST['characterID'];
		}
		
		// set all other addresses to default = N if this one
		// is going to be the default 
		if ($_REQUEST['address_default'] == 'Y') {
			$result = $wpdb->update(VTM_TABLE_PREFIX . "CHARACTER_PM_ADDRESS",
				array ('ISDEFAULT' => 'N'),
				array ('CHARACTER_ID' => $characterID)
			);			
		}
		
		$dataarray = array(
						'NAME'         => $_REQUEST['address_name'],
						'CHARACTER_ID' => $characterID,
						'PM_TYPE_ID'   => $_REQUEST['address_pmtype'],
						'PM_CODE'      => vtm_sanitize_pm_code($_REQUEST['address_code']),
						'DESCRIPTION'  => $_REQUEST['address_desc'],
						'VISIBLE'      => $_REQUEST['address_visible'],
						'ISDEFAULT'    => $_REQUEST['address_default'],
						'DELETED'      => 'N',
					);
		
		/* print_r($dataarray); */
		
		$wpdb->insert(VTM_TABLE_PREFIX . "CHARACTER_PM_ADDRESS",
					$dataarray,
					array (
						'%s',
						'%d',
						'%d',
						'%s',
						'%s',
						'%s',
						'%s'
					)
				);
		
		if ($wpdb->insert_id == 0) {
			echo "<p style='color:red'><b>Error:</b> " . vtm_formatOutput($_REQUEST['address_name']) . " could not be inserted (";
			$wpdb->print_error();
			echo ")</p>";
		} else {
			echo "<p style='color:green'>Added '" . vtm_formatOutput($_REQUEST['address_name']) . "' (ID: {$wpdb->insert_id})</p>";
		}
	}
 	function edit() {
		global $wpdb;
		$wpdb->show_errors();
		
		if (vtm_isST()) {
			$characterID = $_REQUEST['address_charid'];
		} else {
			$characterID = $_REQUEST['characterID'];
		}
		
		// set all other addresses to default = N if this one
		// is going to be the default 
		if ($_REQUEST['address_default'] == 'Y') {
			$result = $wpdb->update(VTM_TABLE_PREFIX . "CHARACTER_PM_ADDRESS",
				array ('ISDEFAULT' => 'N'),
				array ('CHARACTER_ID' => $characterID)
			);
			/*			
			if ($result) 
				echo "<p style='color:green'>Removed default from other addresses</p>";
			else if ($result === 0) 
				echo "<p style='color:orange'>No updates made for default</p>";
			else {
				$wpdb->print_error();
				echo "<p style='color:red'>Could not update default</p>";
			}
			*/
		}
		
		$dataarray = array(
						'NAME'         => $_REQUEST['address_name'],
						'CHARACTER_ID' => $characterID,
						'PM_TYPE_ID'   => $_REQUEST['address_pmtype'],
						'PM_CODE'      => vtm_sanitize_pm_code($_REQUEST['address_code']),
						'DESCRIPTION'  => $_REQUEST['address_desc'],
						'VISIBLE'      => $_REQUEST['address_visible'],
						'ISDEFAULT'    => $_REQUEST['address_default'],
					);
		
		$result = $wpdb->update(VTM_TABLE_PREFIX . "CHARACTER_PM_ADDRESS",
					$dataarray,
					array (
						'ID' => $_REQUEST['address']
					)
				);
		
		if ($result) 
			echo "<p style='color:green'>Updated address " . vtm_formatOutput($_REQUEST['address_name']) . "</p>";
		else if ($result === 0) 
			echo "<p style='color:orange'>No updates made to " . vtm_formatOutput($_REQUEST['address_name']) . "</p>";
		else {
			$wpdb->print_error();
			echo "<p style='color:red'>Could not update address " . vtm_formatOutput($_REQUEST['address_name']) . "</p>";
		}
		 
	}
 	function delete($selectedID) {
		global $wpdb;
		
		//$sql = "delete from " . VTM_TABLE_PREFIX . "CHARACTER_PM_ADDRESS where ID = %d;";
		//$result = $wpdb->get_results($wpdb->prepare($sql, $selectedID));
		//echo "<p style='color:green'>Deleted address $selectedID</p>";
		
		$result = $wpdb->update(VTM_TABLE_PREFIX . "CHARACTER_PM_ADDRESS",
					array ('DELETED' => 'Y'),
					array ('ID' => $selectedID)
				);
		
		if ($result) 
			echo "<p style='color:green'>Deleted address</p>";
		else if ($result === 0) 
			echo "<p style='color:orange'>No changes made</p>";
		else {
			$wpdb->print_error();
			echo "<p style='color:red'>Could not delete address</p>";
		}
	}


    function column_default($item, $column_name){
        switch($column_name){
            case 'ISDEFAULT':
                return vtm_formatOutput($item->$column_name);
            case 'DESCRIPTION':
                return vtm_formatOutput($item->$column_name);
            default:
                return print_r($item,true); 
        }
    }
	

   function column_name($item){
        
		if (vtm_isST()) {
			return vtm_formatOutput($item->NAME);
		} else {
			$actions = array(
				'edit'      => sprintf('<a href="?post_type=%s&amp;page=%s&amp;action=%s&amp;address=%s">Edit</a>','vtmpm', $_REQUEST['page'],'edit',$item->ID),
				'delete'    => sprintf('<a href="?post_type=%s&amp;page=%s&amp;action=%s&amp;address=%s">Delete</a>','vtmpm', $_REQUEST['page'],'delete',$item->ID),
		   );
			
			return sprintf('%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
				vtm_formatOutput($item->NAME),
				$item->ID,
				$this->row_actions($actions)
			);
		}
    }
    function column_pm_code($item){
        return vtm_formatOutput($item->PM_CODE);
    }
    function column_charactername($item){
		$actions = array(
			'edit'      => sprintf('<a href="?post_type=%s&amp;page=%s&amp;action=%s&amp;address=%s">Edit</a>','vtmpm', $_REQUEST['page'],'edit',$item->ID),
			'delete'    => sprintf('<a href="?post_type=%s&amp;page=%s&amp;action=%s&amp;address=%s">Delete</a>','vtmpm', $_REQUEST['page'],'delete',$item->ID),
		);

		return sprintf('%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
			vtm_formatOutput(vtm_pm_getchfromid($item->CHARACTER_ID)),
			$item->ID,
			$this->row_actions($actions)
		);
    }
    function column_pm_type($item){
		global $wpdb;
        return vtm_formatOutput($wpdb->get_var($wpdb->prepare("SELECT NAME FROM " . VTM_TABLE_PREFIX . "PM_TYPE WHERE ID = %s", $item->PM_TYPE_ID)));
    }

    function get_columns(){
		$columns['cb']          = '<input type="checkbox" />';
		if (vtm_isST()) {
			$columns['CHARACTERNAME'] = 'Character';
		}
		$columns['NAME']        = 'Name';
		$columns['PM_TYPE']     = 'Type';
		$columns['PM_CODE']     = 'Code';
		$columns['DESCRIPTION'] = 'Private Description';
		$columns['VISIBLE']     = 'Visible to the public';
		$columns['ISDEFAULT']   = 'Default for sending';
        return $columns;
		
    }
    
    function get_sortable_columns() {
        $sortable_columns = array(
            'NAME'   => array('NAME',true),
        );
        return $sortable_columns;
    }
	
    
    function get_bulk_actions() {
        $actions = array(
            'delete'    => 'Delete'
       );
        return $actions;
    }
    
    function process_bulk_action() {
        if( 'delete'===$this->current_action()) {
			if ('string' == gettype($_REQUEST['address'])) {
				$this->delete($_REQUEST['address']);
			} else {
				foreach ($_REQUEST['address'] as $address) {
					$this->delete($address);
				}
			}
        }
     }

        
    function prepare_items() {
        global $wpdb; 
		global $vtmglobal;
        
		$characterID = $vtmglobal['characterID'];
		
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
		        			
		$this->_column_headers = array($columns, $hidden, $sortable);
 		$this->type = 'address';
               
        $this->process_bulk_action();
		
		$data = vtm_get_pm_addresses($characterID);
        
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
class vtmclass_pm_addressbook_table extends vtmclass_MultiPage_ListTable {
   
    function __construct(){
        global $status, $page;
                
        parent::__construct( array(
            'singular'  => 'address',     
            'plural'    => 'addresses',    
            'ajax'      => false        
        ) );
    }
 	function add() {
		global $wpdb;
		$wpdb->show_errors();
		
		$dataarray = array(
						'CHARACTER_ID' => $_REQUEST['characterID'],
						'NAME'         => $_REQUEST['address_name'],
						'PM_CODE'      => vtm_sanitize_pm_code($_REQUEST['address_code']),
						'DESCRIPTION'  => $_REQUEST['address_desc'],
					);
		
		/* print_r($dataarray); */
		
		$wpdb->insert(VTM_TABLE_PREFIX . "CHARACTER_PM_ADDRESSBOOK",
					$dataarray,
					array (
						'%d',
						'%s',
						'%s',
						'%s'
					)
				);
		
		if ($wpdb->insert_id == 0) {
			echo "<p style='color:red'><b>Error:</b> " . vtm_formatOutput($_REQUEST['address_name']) . " could not be inserted (";
			$wpdb->print_error();
			echo ")</p>";
		} else {
			echo "<p style='color:green'>Added " . vtm_formatOutput($_REQUEST['address_name']) . "' (ID: {$wpdb->insert_id})</p>";
		}
	}
 	function edit() {
		global $wpdb;
		$wpdb->show_errors();
		
		$dataarray = array(
						'CHARACTER_ID' => $_REQUEST['characterID'],
						'NAME'         => $_REQUEST['address_name'],
						'PM_CODE'      => vtm_sanitize_pm_code($_REQUEST['address_code']),
						'DESCRIPTION'  => $_REQUEST['address_desc'],
					);
		
		$result = $wpdb->update(VTM_TABLE_PREFIX . "CHARACTER_PM_ADDRESSBOOK",
					$dataarray,
					array (
						'ID' => $_REQUEST['address']
					)
				);
		
		if ($result) 
			echo "<p style='color:green'>Updated address {$_REQUEST['address_name']}</p>";
		else if ($result === 0) 
			echo "<p style='color:orange'>No updates made to {$_REQUEST['address_name']}</p>";
		else {
			$wpdb->print_error();
			echo "<p style='color:red'>Could not update address {$_REQUEST['address_name']}</p>";
		}
		 
	}
 	function delete($selectedID) {
		global $wpdb;
		
		$sql = "delete from " . VTM_TABLE_PREFIX . "CHARACTER_PM_ADDRESSBOOK where ID = %d;";
			
		$result = $wpdb->get_results($wpdb->prepare($sql, $selectedID));
		
		echo "<p style='color:green'>Deleted address $selectedID</p>";
	}


    function column_default($item, $column_name){
        switch($column_name){
            case 'DESCRIPTION':
                return vtm_formatOutput($item->$column_name);
            default:
                return print_r($item,true); 
        }
    }
	
    function column_cb($item){
		if ($item->ADDRESSBOOK == 'Private') {
			return sprintf(
				'<input type="checkbox" name="%1$s[]" value="%2$s" />',
				$this->_args['singular'],  
				$item->ID               
			);
		} else {
			return "";
		}
    }
   function column_name($item){
	   global $vtmglobal;
		
		// actions only available for own addressbook entries
		if ($item->ADDRESSBOOK == 'Private') {
			$actions = array(
				'edit'      => sprintf('<a href="?post_type=%s&amp;page=%s&amp;action=%s&amp;address=%s&amp">Edit</a>','vtmpm', $_REQUEST['page'],'edit',$item->tableID),
				'delete'    => sprintf('<a href="?post_type=%s&amp;page=%s&amp;action=%s&amp;address=%s&amp">Delete</a>','vtmpm', $_REQUEST['page'],'delete',$item->tableID),
			);
		} else {
			$actions = array();
		}
		
		if ($item->ADDRESSBOOK == 'Public' || $item->ADDRESSBOOK == 'Private') {
			$name = $item->charactername;
		} else {
			$name = $item->NAME;
		}

		if (!(isset($vtmglobal['characterID']) && $item->CHARACTER_ID == $vtmglobal['characterID'] )) {
			$type = vtm_get_pm_typeidfromcode($item->PM_CODE);
			$linkurl = admin_url('post-new.php');
			$linkurl = add_query_arg('post_type','vtmpm',$linkurl );
			$linkurl = add_query_arg('characterID',$item->CHARACTER_ID,$linkurl);
			$linkurl = add_query_arg('type',$type,$linkurl);
			if ($item->PM_CODE != '') {
				$linkurl = add_query_arg('code',$item->PM_CODE,$linkurl);
			}
			$actions['message'] = sprintf('<a href="%s">Send Message</a>',vtm_formatOutput($linkurl));
		}

		
        return sprintf('%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
            vtm_formatOutput($name),
            $item->ID,
            $this->row_actions($actions)
        );
    }
   function column_addressbook($item){
		// actions only available for own addressbook entries
		if ($item->ADDRESSBOOK == 'Post Office') {
			return vtm_formatOutput(get_option( 'vtm_pm_ic_postoffice_location'));
		} else {
			return vtm_formatOutput($item->ADDRESSBOOK);
		}
    }
   function column_description($item){
		if ($item->ADDRESSBOOK == 'Public') {
			return vtm_formatOutput($item->NAME);
		} 
		elseif ($item->ADDRESSBOOK == 'Private') {
			return vtm_formatOutput($item->NAME . " : " . $item->DESCRIPTION);
		} 
		else {
			return vtm_formatOutput($item->DESCRIPTION);
		}
    }
    function column_pm_code($item){
        return vtm_formatOutput($item->PM_CODE);
    }
    function column_pm_type($item){
		global $wpdb;
		
		if ($item->PM_TYPE_ID == 0) {
			$type = "Post Office";
		} else {
			$type = vtm_formatOutput($wpdb->get_var($wpdb->prepare("SELECT NAME FROM " . VTM_TABLE_PREFIX . "PM_TYPE WHERE ID = %s", $item->PM_TYPE_ID)));
		}
		
        return $type;
    }

    function get_columns(){
        $columns = array(
            'cb'          => '<input type="checkbox" />', 
            'NAME'        => 'Name',
            'ADDRESSBOOK' => 'Addressbook', // public/private
            'PM_TYPE'     => 'Type',
            'PM_CODE'     => 'Code',
            'DESCRIPTION' => 'Description',
         );
        return $columns;
		
    }
    
    function get_sortable_columns() {
        $sortable_columns = array(
            'NAME'   => array('NAME',true),
        );
        return $sortable_columns;
    }
	
	function load_filters() {
		global $wpdb;
		
		/* get defaults */		
		$default_addressbook  = 'all';
		$default_address_type = 'all';
		
		/* get filter options */
		$this->filter_addressbook = array (
			'all'         => 'All',
			'public'      => 'Public',
			'private'     => 'Private',
			'postoffice'  => get_option( 'vtm_pm_ic_postoffice_location','Post Office')
		);
		
		$sql = "SELECT ID, NAME FROM " . VTM_TABLE_PREFIX. "PM_TYPE";
		$this->filter_address_type = vtm_make_filter($wpdb->get_results($sql));
		$this->filter_address_type['0'] = 'Post Office';
		
		/* set active filters */
		if ( isset( $_REQUEST['addressbook'] ) && array_key_exists( $_REQUEST['addressbook'], $this->filter_addressbook ) ) {
			$this->active_filter_addressbook = sanitize_key( $_REQUEST['addressbook'] );
		} else {
			$this->active_filter_addressbook = $default_addressbook;
		}
		if ( isset( $_REQUEST['address_type'] ) && array_key_exists( $_REQUEST['address_type'], $this->filter_address_type ) ) {
			$this->active_filter_address_type = sanitize_key( $_REQUEST['address_type'] );
		} else {
			$this->active_filter_address_type = $default_address_type;
		}
	
	}
	function get_filter_sql() {
	
		$sql = "";
		$args = "";
				
		if ( "all" !== $this->active_filter_address_type) {
			$sql .= " AND pm.PM_TYPE_ID = %s";
			$args = $this->active_filter_address_type;
		}
		
		return array($sql, $args);
	
	}
	function extra_tablenav($which) {
		if ($which == 'top')  {
			echo "<div class='vtmfilter'>";
			
			echo "<label>Addressbook: </label>";
			if ( !empty( $this->filter_addressbook ) ) {
				echo "<select name='addressbook'>";
				foreach( $this->filter_addressbook as $key => $value ) {
					echo '<option value="' . esc_attr( $key ) . '" ';
					selected( $this->active_filter_addressbook, $key );
					echo '>' . esc_attr( $value ) . '</option>';
				}
				echo '</select>';
			}
			
			echo "<label>Address Type: </label>";
			if ( !empty( $this->filter_address_type ) ) {
				echo "<select name='address_type'>";
				foreach( $this->filter_address_type as $key => $value ) {
					echo '<option value="' . esc_attr( $key ) . '" ';
					selected( $this->active_filter_address_type, $key );
					echo '>' . esc_attr( $value ) . '</option>';
				}
				echo '</select>';
			}
			submit_button( 'Filter', 'secondary', 'do_filter_tablenav', false );
		
			echo "</div>";
		}
	}	
    
    function get_bulk_actions() {
        $actions = array(
            'delete'    => 'Delete'
       );
        return $actions;
    }
    
    function process_bulk_action() {
        if( 'delete'===$this->current_action()) {
			if ('string' == gettype($_REQUEST['address'])) {
				$this->delete($_REQUEST['address']);
			} else {
				foreach ($_REQUEST['address'] as $address) {
					$this->delete($address);
				}
			}
        }
        		
     }

        
    function prepare_items() {
        global $wpdb; 
		global $vtmglobal;
		
		$characterID = $vtmglobal['characterID'];
        
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
		
		/* filters */
		$this->load_filters();
		//$filterinfo = $this->get_filter_sql();
	        			
		$this->_column_headers = array($columns, $hidden, $sortable);
 		$this->type = 'address';
               
        $this->process_bulk_action();
		
		$data = vtm_get_pm_addressbook($characterID, 
			$this->active_filter_address_type,
			$this->active_filter_addressbook);
        
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

if(!class_exists('WP_List_Table')) {
	require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

if(!class_exists('WP_Posts_List_Table')) {
	require_once(ABSPATH . 'wp-admin/includes/class-wp-posts-list-table.php');
}
class vtmclass_pm_WP_Posts_List_Table extends WP_Posts_List_Table {
	function row_actions( $actions, $always_visible = false ) {
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

}
?>