<?php 

// CODE:
//	* No spaces
//	* Alphanumeric characters
//	* Uppercase
//
// TO DO
//	* remove anyone elses custom meta boxes
//	* remove anyone elses custom columns
//	* Cannot edit sent messages
//	* reply to message
//	* View messages sent to you
//	* Copy of message to storytellers
//	* Include previous messages in body of message
//	* Meta box 
//		- Validate that only ANONYMOUS codes have have no return address
//		- Validate that non-anonymous must have same PM_TYPE (i.e. phone -> phone)
//			- Anything can send to Post Office
//	* Inbox link on widget

// Register Custom Post Type
function vtm_PM_post_type() {

	$labels = array(
		'name'                => _x( 'VtM PMs', 'Post Type General Name', 'text_domain' ),
		'singular_name'       => _x( 'VtM PM', 'Post Type Singular Name', 'text_domain' ),
		'menu_name'           => __( 'V:tM Message', 'text_domain' ),
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
		'label'               => __( 'vtmpm', 'text_domain' ),
		'description'         => __( 'Private Message', 'text_domain' ),
		'labels'              => $labels,
		'supports'            => array( ),
		'taxonomies'          => array( ),
		'hierarchical'        => false,
		'public'              => false,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_nav_menus'   => true,
		'show_in_admin_bar'   => true,
		'menu_position'       => 6,
		'can_export'          => true,
		'has_archive'         => false,
		'exclude_from_search' => true,
		'publicly_queryable'  => false,
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
	);
	register_post_type( 'vtmpm', $args );

}


// Hook into the 'init' action
if (get_option( 'vtm_feature_pm', '0' ) == '1') {
		
	add_action( 'init', 'vtm_PM_post_type', 0 );
	
	// Change the columns for the edit CPT screen
	function vtm_pm_change_columns( $cols ) {
		$cols['vtmpmstatus']  =  __( 'Send Status', 'trans' );
		$cols['vtmfrom']    =  __( 'From', 'trans' );
		$cols['vtmto']      =  __( 'To', 'trans' );
		$cols['vtmaddress'] =  __( 'Address', 'trans' );
	  return $cols;
	}
	add_filter( "manage_vtmpm_posts_columns", "vtm_pm_change_columns" );
	
	function vtm_pm_custom_columns( $column, $post_id ) {
		switch ( $column ) {
	    case "vtmpmstatus":
			echo get_post_status( $post_id );
			break;
	    case "vtmfrom":
			echo "TBD";
			break;
	    case "vtmto":
			echo "TBD";
			break;
	    case "vtmaddress":
			echo "TBD";
			break;
		}
	}
	add_action( "manage_posts_custom_column", "vtm_pm_custom_columns", 10, 2 );


	// Meta box
	function vtm_pm_metabox($post_type) {
		//echo "<li>type: $post_type</li>";
		//if ( in_array( $post_type, array( 'post', 'page' ) ) ) {
			add_meta_box(
				'vtm_pm_metabox',
				'V:tM Messages',
				'vtm_pm_metabox_callback',
				'vtmpm',
				//'advanced',
				//'default'
				'special',
				'high'
			);
		//}
	}
	add_action( 'add_meta_boxes', 'vtm_pm_metabox' );
 	function vtm_pm_move_metabox() {
			# Get the globals:
			global $post, $wp_meta_boxes;

			# Output the "advanced" meta boxes:
			do_meta_boxes( get_current_screen(), 'special', $post );

			# Remove the initial "advanced" meta boxes:
			unset($wp_meta_boxes[get_post_type($post)]['special']);
	}
	add_action('edit_form_after_title', 'vtm_pm_move_metabox');

	function vtm_pm_metabox_callback($post) {
		global $vtmglobal;
		wp_nonce_field( 'vtm_pm_metabox', 'vtm_pm_metabox_nonce' );
		
		$addressbook = vtm_get_pm_addressbook();
		$myaddresses = vtm_get_pm_addresses();
		
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
		
		echo vtm_validate_vtmpm_metabox($tochid, $tocode, $totype,
			$fromchid, $fromcode, $fromtype);
				
		echo "<p>";
		echo "<label>To: </label><select name='vtm_pm_to'>";
		foreach ($addressbook as $address) {
			if ($vtmglobal['characterID'] != $address->CHARACTER_ID) {
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
			}
		}
		echo "</select><br />";
		echo "<label>From: </label><select name='vtm_pm_from'>";
		echo "<option value='anonymous:postoffice:0'>No return address / Anonymous</option>";
		
		foreach ($myaddresses as $address) {
			$title = $address->NAME . " (" . $address->PM_CODE . ")";
			
			$code = $address->PM_TYPE_ID == 0 ? 'postoffice' : $address->PM_CODE;
			$value = esc_attr(implode(":", array($address->CHARACTER_ID, 
				$code, $address->PM_TYPE_ID)));

			echo "<option value='$value' " . selected($value, $from, false) .
				">" . vtm_formatOutput($title) . "</option>";
		}
		echo "</select>";
		echo "</p>";
		
		//print_r($addressbook);
	}
	
	// Add addressbook page 
	function vtmpm_submenus() {
		add_submenu_page( 'edit.php?post_type=vtmpm', "Address Book", 
			"Address Book", "read", 'vtmpm_addresses',
			"vtmpm_render_address_book" );
		add_submenu_page( 'edit.php?post_type=vtmpm', "My Addresses", 
			"My Addresses", "read", 'vtmpm_mydetails',
			"vtmpm_render_my_details" );
	}
	add_action('admin_menu' , 'vtmpm_submenus'); 

	function vtmpm_render_address_book (){
		global $current_user;
		global $vtmglobal;
		get_currentuserinfo();
		$vtmglobal['characterID'] = vtm_establishCharacterID($current_user->user_login);

		echo "<h3>Addressbook</h3>";
		
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
			
			$testListTable->prepare_items($vtmglobal['characterID']);
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
	function vtmpm_render_my_details (){
		global $current_user;
		global $vtmglobal;
		get_currentuserinfo();
		$vtmglobal['characterID'] = vtm_establishCharacterID($current_user->user_login);

		echo "<h3>My Details</h3>";
		
		if ($vtmglobal['characterID'] > 0) {
			$testListTable = new vtmclass_pm_address_table();
			$doaction = vtm_pm_address_input_validation('address');
			
			if ($doaction == "add-address") {
				$testListTable->add();
			}
			if ($doaction == "save-address") {
				$testListTable->edit();				
			}
			
			vtm_render_pm_address_add_form('address', $doaction);
			
			$testListTable->prepare_items($vtmglobal['characterID']);
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
	
	function vtm_pm_save_meta_box_data( $post_id ) {

		//
		// We need to verify this came from our screen and with proper authorization,
		// because the save_post action can be triggered at other times.
		//

		// Check if our nonce is set.
		if ( ! isset( $_POST['vtm_pm_metabox_nonce'] ) ) {
			return;
		}
		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $_POST['vtm_pm_metabox_nonce'], 'vtm_pm_metabox' ) ) {
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

		//OK, it's safe for us to save the data now. 
		
		// Make sure that it is set.
		if ( ! isset( $_POST['vtm_pm_to'] ) ) {
			return;
		}

		// Sanitize user input.
		$to   = explode(":",sanitize_text_field( $_POST['vtm_pm_to'] ));
		$from = explode(":",sanitize_text_field( $_POST['vtm_pm_from'] ));
		
		// Update the meta field in the database.
		update_post_meta( $post_id, '_vtmpm_to_characterID', $to[0] );
		update_post_meta( $post_id, '_vtmpm_to_code', $to[1] );
		update_post_meta( $post_id, '_vtmpm_to_type', $to[2] );
		update_post_meta( $post_id, '_vtmpm_from_characterID', $from[0] );
		update_post_meta( $post_id, '_vtmpm_from_code', $from[1] );
		update_post_meta( $post_id, '_vtmpm_from_type', $from[2] );
		
	}	
	add_action( 'save_post', 'vtm_pm_save_meta_box_data' );
	
}


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
		
		// set all other addresses to default = N if this one
		// is going to be the default 
		if ($_REQUEST['address_default'] == 'Y') {
			$result = $wpdb->update(VTM_TABLE_PREFIX . "CHARACTER_PM_ADDRESS",
				array ('ISDEFAULT' => 'N'),
				array ('CHARACTER_ID' => $_REQUEST['characterID'])
			);			
		}
		
		$dataarray = array(
						'NAME'         => $_REQUEST['address_name'],
						'CHARACTER_ID' => $_REQUEST['characterID'],
						'PM_TYPE_ID'   => $_REQUEST['address_pmtype'],
						'PM_CODE'      => vtm_sanitize_pm_code($_REQUEST['address_code']),
						'DESCRIPTION'  => $_REQUEST['address_desc'],
						'VISIBLE'      => $_REQUEST['address_visible'],
						'ISDEFAULT'    => $_REQUEST['address_default'],
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
		
		// set all other addresses to default = N if this one
		// is going to be the default 
		if ($_REQUEST['address_default'] == 'Y') {
			$result = $wpdb->update(VTM_TABLE_PREFIX . "CHARACTER_PM_ADDRESS",
				array ('ISDEFAULT' => 'N'),
				array ('CHARACTER_ID' => $_REQUEST['characterID'])
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
						'CHARACTER_ID' => $_REQUEST['characterID'],
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
		
		$sql = "delete from " . VTM_TABLE_PREFIX . "CHARACTER_PM_ADDRESS where ID = %d;";
			
		$result = $wpdb->get_results($wpdb->prepare($sql, $selectedID));
		
		echo "<p style='color:green'>Deleted address $selectedID</p>";
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
        
        $actions = array(
            'edit'      => sprintf('<a href="?post_type=%s&amp;page=%s&amp;action=%s&amp;address=%s&amp">Edit</a>','vtmpm', $_REQUEST['page'],'edit',$item->ID),
            'delete'    => sprintf('<a href="?post_type=%s&amp;page=%s&amp;action=%s&amp;address=%s&amp">Delete</a>','vtmpm', $_REQUEST['page'],'delete',$item->ID),
       );
        
        return sprintf('%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
            vtm_formatOutput($item->NAME),
            $item->ID,
            $this->row_actions($actions)
        );
    }
    function column_pm_code($item){
        return vtm_formatOutput($item->PM_CODE);
    }
    function column_pm_type($item){
		global $wpdb;
        return vtm_formatOutput($wpdb->get_var($wpdb->prepare("SELECT NAME FROM " . VTM_TABLE_PREFIX . "PM_TYPE WHERE ID = %s", $item->PM_TYPE_ID)));
    }

    function get_columns(){
        $columns = array(
            'cb'          => '<input type="checkbox" />', 
            'NAME'        => 'Name',
            'PM_TYPE'     => 'Type',
            'PM_CODE'     => 'Code',
            'DESCRIPTION' => 'Private Description',
            'VISIBLE'     => 'Visible to the public',
            'ISDEFAULT'   => 'Default for sending',
        );
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

        
    function prepare_items($characterID) {
        global $wpdb; 
        
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

        
    function prepare_items($characterID) {
        global $wpdb; 
        
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
function vtm_render_pm_address_add_form($type, $addaction) {
	global $vtmglobal;
	global $wpdb;
	
	$id   = isset($_REQUEST[$type]) ? $_REQUEST[$type] : '';
	$characterID = $vtmglobal['characterID'];
	
	if ('fix-' . $type == $addaction) {
		$name    = $_REQUEST[$type . "_name"];
		$desc    = $_REQUEST[$type . "_desc"];
		$visible = $_REQUEST[$type . '_visible'];
		$code    = $_REQUEST[$type . '_code'];
		$pm_type_id = $_REQUEST[$type . '_pmtype'];
		$default = $_REQUEST[$type . '_default'];
		
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
		
		$nextaction = "save";

	} else {
		$name = "";
		$desc = "";
		$code = "";
		$visible= "N";
		$pm_type_id = 1;
		$default = 'N';
		
		$nextaction = "add";
	}
	
	$current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
	$current_url = remove_query_arg( 'action', $current_url );
	?>
	<form id="new-<?php print $type; ?>" method="post" action='<?php print htmlentities($current_url); ?>'>
		<input type="hidden" name="<?php print $type; ?>_id" value="<?php print $id; ?>"/>
		<input type="hidden" name="action" value="<?php print $nextaction; ?>" />
		<input type="hidden" name="characterID" value="<?php print $characterID; ?>" />
		<table>
		<tr>
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

	} else {
		$name = "";
		$desc = "";
		$code = "";
		$tableID = 0;
		
		$nextaction = "add";
	}
	
	$current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
	$current_url = remove_query_arg( 'action', $current_url );
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
			// Other character using it?
			// Own character using it?
			$sql = "SELECT COUNT(ID) 
				FROM " . VTM_TABLE_PREFIX . "CHARACTER_PM_ADDRESS
				WHERE CHARACTER_ID != %s AND PM_CODE = %s";
			$sql = $wpdb->prepare($sql, $vtmglobal['characterID'], $code);
			if ($wpdb->get_var($sql) > 0) {
				$doaction = "fix-$type";
				echo "<p style='color:red'>ERROR: Phone number/postcode/zipcode already in use by another character</p>";
			}
			
			if ($_REQUEST['action'] == 'add') {
				$sql = "SELECT COUNT(ID) 
					FROM " . VTM_TABLE_PREFIX . "CHARACTER_PM_ADDRESS
					WHERE CHARACTER_ID = %s AND PM_CODE = %s";
				$sql = $wpdb->prepare($sql, $vtmglobal['characterID'], $code);
				if ($wpdb->get_var($sql) > 0) {
					$doaction = "fix-$type";
					echo "<p style='color:red'>ERROR: You have already used Phone number/postcode/zipcode already</p>";
				}
			}
			
		}
			
	}
	
	return $doaction;

}
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
				WHERE PM_CODE = %s";
			$sql = $wpdb->prepare($sql, $code);
			if ($wpdb->get_var($sql) == 0) {
				$doaction = "fix-$type";
				echo "<p style='color:red'>ERROR: That code does not exist</p>";
			}
			else {
				// Is it already visible?
				$sql = "SELECT COUNT(ID) 
					FROM " . VTM_TABLE_PREFIX . "CHARACTER_PM_ADDRESS
					WHERE PM_CODE = %s AND VISIBLE = 'Y'";
				$sql = $wpdb->prepare($sql, $code);
				if ($wpdb->get_var($sql) > 0) {
					$doaction = "fix-$type";
					echo "<p style='color:red'>ERROR: That code is a public address and already listed</p>";
				}
				
				// Is it one of your own?
				$sql = "SELECT COUNT(ID) 
					FROM " . VTM_TABLE_PREFIX . "CHARACTER_PM_ADDRESS
					WHERE PM_CODE = %s AND CHARACTER_ID = %s";
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

// Report issues on save_post/save_post_vtmpm
// Restore to Draft any posts with fails on publish_post/publish_vtmpm
// Ensure posts stay 'private' (but trashed ok) 

 // * Save post metadata when a post is saved.
 // *
 // * @param int $post_id The post ID.
 // * @param post $post The post object.
 // * @param bool $update Whether this is an existing post being updated or not.
function vtm_validate_vtmpm_metabox($tochid, $tocode, $totype,
			$fromchid, $fromcode, $fromtype) {
	global $wpdb;
	
	$output = "";
	
	// anytype/one can sent TO the postoffice 
	if ($totype == 0) {
		$output = "";
	}
	// sending anonymously?
	elseif ($fromtype == 0) {
		// Yes
		$allow = $wpdb->get_var($wpdb->prepare(
			"SELECT ISANONYMOUS FROM " . VTM_TABLE_PREFIX . "PM_TYPE
			WHERE ID = %s", $totype));
		
		if ($allow == 'N')
			$output .= "<li>anonymous FAIL</li>";
	} 
	// No - to and from must be same type/method 
	elseif ($fromtype != $totype) {
		$output .= "<li>type mismatch</li>";
	}
	
	return $output;
}

 //function <function>( $post_id, $post, $update ) {
/*
$post_id - The ID of the post you'd like to change.
$status -  The post status publish|pending|draft|private|static|object|attachment|inherit|future|trash.
*/
/*
function change_post_status($post_id,$status){
    $current_post = get_post( $post_id, 'ARRAY_A' );
    $current_post['post_status'] = $status;
    wp_update_post($current_post);
}
*/


?>