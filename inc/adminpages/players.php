<?php
function vtm_character_players() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( 'You do not have sufficient permissions to access this page.' );
	}
	?>
	<div class="wrap">
		<h2>Players</h2>
		<div class="gvadmin_content">
			<?php vtm_render_player_data(); ?>
		</div>

	</div>
	
	<?php
}

function vtm_render_player_data(){

    $testListTable = new vtmclass_admin_players_table();
	$doaction = vtm_player_input_validation();
	
 	if ($doaction == "add-player") {
		$testListTable->add($_REQUEST['player_name'], $_REQUEST['player_type'], $_REQUEST['player_active']);
	}
	if ($doaction == "save-player") { 
		$testListTable->edit($_REQUEST['player_id'], $_REQUEST['player_name'], $_REQUEST['player_type'], $_REQUEST['player_active']);
	}
	if ($doaction == "confirm-delete-player") { 
		vtm_player_delete($_REQUEST['player_id']);
	}
	if ($doaction == "delete-player") { 
		$testListTable->deleteplayer($_REQUEST['player']);
	} else {
		vtm_render_player_add_form($doaction); 
	}
	
	$testListTable->prepare_items();
 	$current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
	$current_url = remove_query_arg( 'action', $current_url );
  ?>	
	<br /><hr />
  
	<!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
	<form id="player-filter" method="get" action='<?php print esc_url($current_url); ?>'>
		<input type="hidden" name="page" value="<?php print esc_html($_REQUEST['page']) ?>" />
		<input type="hidden" name="tab" value="player" />
		<?php $testListTable->display() ?>
	</form>

    <?php
}

function vtm_render_player_add_form($addaction) {

	global $wpdb;
	
	$type = "player";
	
	/* echo "<p>Creating player form based on action $addaction</p>"; */

	if ('fix-' . $type == $addaction) {
		$id         = $_REQUEST['player'];
		$name       = $_REQUEST[$type . '_name'];
		$activeid   = $_REQUEST[$type . '_active'];
		$typeid     = $_REQUEST[$type . '_type'];
		$nextaction = $_REQUEST['action'];
		
	} else if ('edit-' . $type == $addaction) {
		/* Get values from database */
		$id   = $_REQUEST['player'];
		
		$sql = "select *
				from " . VTM_TABLE_PREFIX . "PLAYER 
				where ID = %d;";
		
		/* echo "<p>$sql</p>"; */
		
		$data =$wpdb->get_results($wpdb->prepare("$sql", $id));
		
		$name     = $data[0]->NAME;
		$activeid = $data[0]->PLAYER_STATUS_ID;
		$typeid   = $data[0]->PLAYER_TYPE_ID;
		
		$nextaction = "save";
		
	} else {
	
		/* defaults */
		$id   = "";
		$name = "";
		$activeid = "";
		$typeid = "";
		
		$nextaction = "add";
	} 
	$current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
	$current_url = remove_query_arg( 'action', $current_url );
	
	$savetext = $nextaction == "save" ? "Save Player" : "New Player";

	?>
	<form id="new-<?php print esc_html($type); ?>" method="post" action='<?php print esc_url($current_url); ?>'>
		<input type="hidden" name="<?php print esc_html($type); ?>_id" value="<?php print esc_html($id); ?>"/>
		<input type="hidden" name="tab" value="<?php print esc_html($type); ?>" />
		<input type="hidden" name="action" value="<?php print esc_html($nextaction); ?>" />
		<table style='width:500px'>
		<tr>
			<td>Name:  </td>
			<td><input type="text" name="<?php print esc_html($type); ?>_name" value="<?php print esc_html($name); ?>" size=20 /></td>
			<td>Type:  </td>
			<td>
				<select name='player_type'>
					<?php 
						foreach( vtm_get_player_type() as $pltype ) {
							echo '<option value="' . esc_html($pltype->ID) . '" ';
							selected( $typeid, $pltype->ID );
							echo '>' . esc_html( $pltype->NAME ) . '</option>';
						}
					?>
				</select>
			</td>
			<td>Active Status:  </td>
			<td>
				<select name='player_active'>
					<?php 
						foreach( vtm_get_player_status() as $plstat ) {
							echo '<option value="' . esc_html($plstat->ID) . '" ';
							selected( $activeid, $plstat->ID );
							echo '>' . esc_html( $plstat->NAME ) . '</option>';
						}
					?>
				</select>
			</td>
		</tr>
		</table>
		<input type="submit" name="do_add_<?php print esc_html($type); ?>" class="button-primary" value="<?php print esc_html($savetext); ?>" />
	</form>
	
	<?php
}

function vtm_player_input_validation() {
	global $wpdb;
	
	$type = "player";
	
	//echo "<p>Requested action: " . $_REQUEST['action'] . ", " . $type . "_name: " . $_REQUEST[$type . '_name']; 

	$doaction = "";
	
	if (!empty($_REQUEST['action']) && $_REQUEST['action'] == 'edit')
		$doaction = "edit-$type";
	if (!empty($_REQUEST['action']) && $_REQUEST['action'] == 'delete')
		$doaction = "delete-$type";
	if (!empty($_REQUEST['action']) && $_REQUEST['action'] == 'confirm-delete')
		$doaction = "confirm-delete-$type";
		
	
	if (!empty($_REQUEST['action']) && !empty($_REQUEST[$type . '_name']) ){
		$doaction = $_REQUEST['action'] . "-" . $type;
	}
	
	if ($doaction == "add-$type") {
		$sql = 'SELECT NAME FROM ' . VTM_TABLE_PREFIX . 'PLAYER WHERE NAME = %s AND DELETED = "N"';
		$result = $wpdb->get_col($wpdb->prepare("$sql",$_REQUEST[$type . '_name'] ));
		//print_r($result);
		$countmatch = count($result);
		if ($countmatch > 0) {
			echo "<p style='color:red'>ERROR: Player name already exists</p>";
			$doaction = "fix-$type";
		}
	}
	
	//echo "<p>Doing action $doaction</p>";

	return $doaction;
}

/* 
-----------------------------------------------
PLAYERS TABLE
------------------------------------------------ */


class vtmclass_admin_players_table extends vtmclass_MultiPage_ListTable {
   
    function __construct(){
        global $status, $page;
                
        parent::__construct( array(
            'singular'  => 'player',     
            'plural'    => 'players',    
            'ajax'      => false        
        ) );
    }
	

	
 	function add($name, $type, $status) {
		global $wpdb;
		
		$wpdb->show_errors();
		
		$dataarray = array(
						'NAME'          => $name,
						'PLAYER_TYPE_ID'    => $type,
						'PLAYER_STATUS_ID'  => $status
					);
		
		/* print_r($dataarray); */
		
		$wpdb->insert(VTM_TABLE_PREFIX . "PLAYER",
					$dataarray,
					array (
						'%s',
						'%s',
						'%s',
					)
				);
		
		if ($wpdb->insert_id == 0) {
			echo "<p style='color:red'><b>Error:</b>" . esc_html($name) . " could not be inserted (";
			$wpdb->print_error();
			echo ")</p>";
		} else {
			echo "<p style='color:green'>Added player '" . esc_html($name) . "' (ID: " . esc_html($wpdb->insert_id) . ")</p>";
		}
	}
 	function edit($id, $name, $type, $status) {
		global $wpdb;
		
		$wpdb->show_errors();
		
		$dataarray = array(
						'NAME'          => $name,
						'PLAYER_TYPE_ID'    => $type,
						'PLAYER_STATUS_ID'  => $status
					);
		
		/* print_r($dataarray); */
		
		$result = $wpdb->update(VTM_TABLE_PREFIX . "PLAYER",
					$dataarray,
					array (
						'ID' => $id
					)
				);
		
		if ($result) 
			echo "<p style='color:green'>Updated " . esc_html($name) . "</p>";
		else if ($result === 0) 
			echo "<p style='color:orange'>No updates made to " . esc_html($name) . "</p>";
		else {
			$wpdb->print_error();
			echo "<p style='color:red'>Could not update " . esc_html($name) . " (" . esc_html($id) . ")</p>";
		}
	}
 	function deleteplayer($id) {
		global $wpdb;
		
		$wpdb->show_errors();
		
		$type = "player";
		$name = $wpdb->get_var($wpdb->prepare("SELECT NAME FROM %i WHERE ID = %s",VTM_TABLE_PREFIX . "PLAYER", $id));
		$current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
		$current_url = remove_query_arg( 'action', $current_url );
				
		echo "<p>Confirm that you want to delete player '" . esc_html($name) . "' and all their characters.</p>";
		$list = vtm_listCharactersForPlayer($id);
		if (vtm_count($list) > 0) {
			echo "<ul>";
			foreach ($list as $char) {
				echo "<li>" . esc_html($char->NAME) . "</li>";
			}
			echo "</ul>";
		}
		?>
		<form id="delete-<?php print esc_html($type); ?>" method="post" action='<?php print esc_url($current_url); ?>'>
			<input type="hidden" name="<?php print esc_html($type); ?>_id" value="<?php print esc_html($id); ?>"/>
			<input type="hidden" name="tab" value="<?php print esc_html($type); ?>" />
			<input type="hidden" name="action" value="confirm-delete" />
			
			<input type='submit' name='confirm-delete' class='button-primary' value='Confirm' />
		</form>
		<?php
	}

 	function vtm_doactivate($selectedID, $activate) {
		global $wpdb;
				
		$wpdb->show_errors();
		
		//echo "<p>New player_status for $selectedID is $activate</p>";
		
		$result = $wpdb->update( VTM_TABLE_PREFIX . "PLAYER", 
			array (
				'PLAYER_STATUS_ID' => $activate
			), 
			array (
				'ID' => $selectedID
			)
		);
		
		if ($result) 
			echo "<p style='color:green'>Item " . esc_html($selectedID) . " update successful</p>";
		else if ($result === 0)
			echo "<p style='color:orange'>Item " . esc_html($selectedID) . " has not been changed</p>";
		else {
			$wpdb->print_error();
			echo "<p style='color:red'>Item " . esc_html($selectedID) . " could not be updated</p>";
		}
	}

    function column_default($item, $column_name){
        switch($column_name){
          case 'PLAYERTYPE':
                return esc_html($item->$column_name);
          case 'PLAYERSTATUS':
                return esc_html($item->$column_name);
          case 'CHARACTERLIST':
			$list = vtm_listCharactersForPlayer($item->ID);
			$a = array();
			foreach ($list as $char) {
				$name = "<a href='" . vtm_get_stlink_url('editCharSheet') . "?characterID={$char->ID}'>{$char->NAME}</a>";
				if ($char->VISIBLE == 'N')
					$name =  $name . " (hidden)";
				
				array_push($a,$name);
			}
			return join(", ",$a);
		  
          default:
                return print_r($item,true); 
        }
    }
 
    function column_name($item){
        
        $actions = array(
            'edit'      => sprintf('<a href="?page=%s&amp;action=%s&amp;player=%s">Edit</a>',$_REQUEST['page'],'edit',$item->ID),
            'delete'    => sprintf('<a href="?page=%s&amp;action=%s&amp;player=%s">Delete</a>',$_REQUEST['page'],'delete',$item->ID),
       );
        
        
        return sprintf('%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
            esc_html($item->NAME),
            $item->ID,
            $this->row_actions($actions)
        );
    }
   

    function get_columns(){
        $columns = array(
            'cb'           => '<input type="checkbox" />', 
            'NAME'         => 'Name',
            'PLAYERTYPE'   => 'Player Type',
            'PLAYERSTATUS' => 'Player Status',
            'CHARACTERLIST' => 'Characters'
        );
        return $columns;
		
    }
    
    function get_sortable_columns() {
        $sortable_columns = array(
            'NAME'         => array('NAME',true),
            'PLAYERTYPE'   => array('PLAYERTYPE',false),
            'PLAYERSTATUS' => array('PLAYERSTATUS',false),
       );
        return $sortable_columns;
    }
	
    function get_bulk_actions() {
		global $wpdb;
		$activeid   = $wpdb->get_var($wpdb->prepare("SELECT ID FROM %i WHERE NAME = %s",VTM_TABLE_PREFIX. "PLAYER_STATUS", 'Active'));
		$inactiveid = $wpdb->get_var($wpdb->prepare("SELECT ID FROM %i WHERE NAME = %s",VTM_TABLE_PREFIX. "PLAYER_STATUS", 'Inactive'));
	
        $actions = array(
            $activeid    => 'Activate',
			$inactiveid  => 'Deactivate'
       );
        return $actions;
    }
    function process_bulk_action() {
 		global $wpdb;
		$activeid   = $wpdb->get_var($wpdb->prepare("SELECT ID FROM %i WHERE NAME = %s",VTM_TABLE_PREFIX. "PLAYER_STATUS", 'Active'));
		$inactiveid = $wpdb->get_var($wpdb->prepare("SELECT ID FROM %i WHERE NAME = %s",VTM_TABLE_PREFIX. "PLAYER_STATUS", 'Inactive'));
       
		//echo "<p>Bulk action " . $this->current_action() . "</p>"; 
				
        if( $activeid === $this->current_action() && isset($_REQUEST['player']) ) {
			if ('string' == gettype($_REQUEST['player'])) {
				$this->doactivate($_REQUEST['player'], $this->current_action());
			} else {
				foreach ($_REQUEST['player'] as $player) {
					$this->doactivate($player, $this->current_action());
				}
			}
        }
        if( $inactiveid === $this->current_action() && isset($_REQUEST['player']) ) {
			if ('string' == gettype($_REQUEST['player'])) {
				$this->doactivate($_REQUEST['player'], $this->current_action());
			} else {
				foreach ($_REQUEST['player'] as $player) {
					$this->doactivate($player, $this->current_action());
				}
			}
        }
    }
	
	function extra_tablenav($which) {
		if ($which == 'top') {

			echo "<div class='gvfilter'>";
			/* Select player type */
			echo "<label>Player Type: </label>";
			if ( !empty( $this->filter_type ) ) {
				echo "<select name='playertype_filter'>";
				foreach( $this->filter_type as $key => $value ) {
					echo '<option value="' . esc_attr( $key ) . '" ';
					selected( $this->active_playertype, $key );
					echo '>' . esc_html( $value ) . '</option>';
				}
				echo '</select>';
			}
			
			/* Select player status */
			echo "<label>Player Status:</label>";
			if ( !empty( $this->filter_status ) ) {
				echo "<select name='playerstatus_filter'>";
				foreach( $this->filter_status as $key => $value ) {
					echo '<option value="' . esc_attr( $key ) . '" ';
					selected( $this->active_playerstatus, $key );
					echo '>' . esc_html( $value ) . '</option>';
				}
				echo '</select>';
			}
			
			
			submit_button( 'Filter', 'secondary', 'do_filter_player', false );
			echo "</div>";
		}
	}
	
    function prepare_items() {
        global $wpdb; 
        
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
		
		$type = "player";

		$this->filter_type = vtm_make_filter(vtm_get_player_type());
		$this->filter_status = vtm_make_filter(vtm_get_player_status());
		
		$this->default_playerstatus = $wpdb->get_var($wpdb->prepare("SELECT ID FROM %i WHERE NAME = %s", VTM_TABLE_PREFIX. "PLAYER_STATUS", 'Active'));
		$this->default_playertype   = $wpdb->get_var($wpdb->prepare("SELECT ID FROM %i WHERE NAME = %s",VTM_TABLE_PREFIX. "PLAYER_TYPE", 'Player'));

		if ( isset( $_REQUEST['playertype_filter'] ) && array_key_exists( $_REQUEST['playertype_filter'], $this->filter_type ) ) {
			$this->active_playertype = sanitize_key( $_REQUEST['playertype_filter'] );
		} else {
			$this->active_playertype = $this->default_playertype;
		}
		if ( isset( $_REQUEST['playerstatus_filter'] ) && array_key_exists( $_REQUEST['playerstatus_filter'], $this->filter_status ) ) {
			$this->active_playerstatus = sanitize_key( $_REQUEST['playerstatus_filter'] );
		} else {
			$this->active_playerstatus = $this->default_playerstatus;
		}

		
		$this->_column_headers = array($columns, $hidden, $sortable);
        
		$this->type = $type;
        		
        $this->process_bulk_action();
		
		
		/* Get the data from the database */
		$sql = "SELECT players.ID, players.NAME, types.NAME as PLAYERTYPE, status.NAME as PLAYERSTATUS
				FROM 
					" . VTM_TABLE_PREFIX . "PLAYER players,
					" . VTM_TABLE_PREFIX . "PLAYER_TYPE types,
					" . VTM_TABLE_PREFIX . "PLAYER_STATUS status
				WHERE	
					types.ID = players.PLAYER_TYPE_ID
					AND status.ID = players.PLAYER_STATUS_ID
					AND players.DELETED = 'N'";
				
		if ( "all" !== $this->active_playertype)
			$sql .= " AND types.ID = '" . $this->active_playertype . "'";
		if ( "all" !== $this->active_playerstatus)			
			$sql .= " AND status.ID = '" . $this->active_playerstatus . "'";

			/* order the data according to sort columns */
		if (!empty($_REQUEST['orderby']) && !empty($_REQUEST['order']))
			$sql .= " ORDER BY {$_REQUEST['orderby']} {$_REQUEST['order']}, NAME ASC";
		else
			$sql .= " ORDER BY NAME ASC";
			
		$sql .= ";";
		
		/* echo "<p>SQL: $sql</p>"; */
		
		$data =$wpdb->get_results("$sql");
        
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

function vtm_player_delete($id) {
	global $wpdb;
	
	$name = $wpdb->get_var($wpdb->prepare("SELECT NAME FROM %i WHERE ID = %s", VTM_TABLE_PREFIX . "PLAYER", $id));
	$list = vtm_listCharactersForPlayer($id);
	if (vtm_count($list) == 0) {
		echo "<ul><li>No characters to delete</li></ul>";
	} else {
		echo "<ul>";
		foreach ($list as $char) {
			echo "<li>";
			echo esc_html(vtm_deleteCharacter($char->ID));
			echo "</li>";
		}
		echo "</ul>";
	}
	
	$result = $wpdb->update(VTM_TABLE_PREFIX . "PLAYER",
		array("DELETED" => 'Y'),
		array ('ID' => $id)	
	);
	if ($result) 
		echo "<p style='color:green'>Deleted player " . esc_html($name) . "</p>";
	else if ($result === 0) 
		echo "<p style='color:orange'>Character " . esc_html($name) . " already deleted - no changes made</p>";
	else {
		$wpdb->print_error();
		echo "<p style='color:red'>Could not delete " . esc_html($name) . " (" . esc_html($id) . ")</p>";
	}
}
?>