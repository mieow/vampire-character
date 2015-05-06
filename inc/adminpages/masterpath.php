<?php

function vtm_character_master_path() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	?>
	<div class="wrap">
		<h2>Path Changes</h2>
		<?php vtm_render_master_path_page(); ?>
	</div>
	
	<?php
}



function vtm_render_master_path_page(){
	global $wpdb;

	$type = "masterpath";
	
	if (isset($_REQUEST['do_update']) && $_REQUEST['do_update']) {
		//echo "<p>Saving...</p>";
		//print_r($_REQUEST['masterpath_change']);
		//print_r($_REQUEST['comment']);
		
		$reasons  = $_REQUEST['path_reason'];
		$comments = $_REQUEST['comment'];
		
		foreach( $_REQUEST['masterpath_change'] as $characterID => $change) {
			if (!empty($change) && is_numeric($change)) {
				
				$dataarray = array (
					'CHARACTER_ID'    => $characterID,
					'PATH_REASON_ID'  => $reasons[$characterID],
					'AWARDED'         => Date('Y-m-d'),
					'AMOUNT'          => $change,
					'COMMENT'         => $comments[$characterID]
				);
				
				$wpdb->insert(VTM_TABLE_PREFIX . "CHARACTER_ROAD_OR_PATH",
							$dataarray,
							array (
								'%d',
								'%d',
								'%s',
								'%d',
								'%s'
							)
						);
				
				$newid = $wpdb->insert_id;
				if ($newid  == 0) {
					echo "<p style='color:red'><b>Error:</b> Path change failed for data (";
					print_r($dataarray);
					$wpdb->print_error();
					echo ")</p>";
				} else {
					echo "<p style='color:green'>Path change made for character $characterID</p>";
					vtm_touch_last_updated($characterID);
				}
				

			}
		
		}
		
	}
	
	$datatable = new vtmclass_master_path();
	$datatable->prepare_items(); 
	$current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
	$current_url = remove_query_arg( 'action', $current_url );
  ?>	

	<!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
	<form id="<?php print $type ?>-filter" method="post" action='<?php print htmlentities($current_url); ?>'>
		<input type="hidden" name="page" value="<?php print $_REQUEST['page'] ?>" />
		<input type="hidden" name="tab" value="<?php print $type ?>" />
		
		<?php $datatable->display(); ?>
		
	</form>

    <?php

}

class vtmclass_master_path extends vtmclass_Report_ListTable {

   function column_default($item, $column_name){
        switch($column_name){
            case 'PATHNAME':
                return vtm_formatOutput($item->$column_name);
            case 'CHARACTERNAME':
                return vtm_formatOutput($item->$column_name);
            case 'LEVEL':
                return $item->$column_name;
           default:
                return print_r($item,true); 
        }
    }
	
	function column_reason($item) {
		$output = "<select name='path_reason[{$item->ID}]'>";
		foreach ($this->pathreasons as $reason) {
			$output .= "<option value='{$reason->id}' " . selected($reason->id, $this->defaultreason,false) . ">" . vtm_formatOutput($reason->name) . "</option>\n";
		}		
		$output .= "</select>";
	
		return "$output";
	}
	
	function column_change($item) {
		return "<input name='masterpath_change[{$item->ID}]' value=\"\" type=\"text\" size=4 />";
	}
	
	function column_comment($item) {	
		return "<input name='comment[{$item->ID}]' value=\"\" type=\"text\" size=30 />";
	}

    function get_columns(){
        $columns = array(
            'CHARACTERNAME' => 'Character',
            'PATHNAME'   => 'Path Name',
            'LEVEL'      => 'Current Level',
			'REASON'     => 'Reason',
			'CHANGE'     => 'Path Change',
            'COMMENT'    => 'Comment',
        );
        return $columns;
	}
		
   function get_sortable_columns() {
        $sortable_columns = array();
       return $sortable_columns;
	}

	function print_update_button() {
		echo "<input type='submit' name='do_update' class='button-primary' value='Update' />";
	}
	
	function filter_tablenav() {
			echo "<label>Player Status: </label>";
			if ( !empty( $this->filter_player_status ) ) {
				echo "<select name='player_status'>";
				foreach( $this->filter_player_status as $key => $value ) {
					echo '<option value="' . esc_attr( $key ) . '" ';
					selected( $this->active_filter_player_status, $key );
					echo '>' . vtm_formatOutput( $value ) . '</option>';
				}
				echo '</select>';
			}
			
			echo "<label>Character Type: </label>";
			if ( !empty( $this->filter_character_type ) ) {
				echo "<select name='character_type'>";
				foreach( $this->filter_character_type as $key => $value ) {
					echo '<option value="' . esc_attr( $key ) . '" ';
					selected( $this->active_filter_character_type, $key );
					echo '>' . vtm_formatOutput( $value ) . '</option>';
				}
				echo '</select>';
			}
			
			echo "<label>Character Status: </label>";
			if ( !empty( $this->filter_character_status ) ) {
				echo "<select name='character_status'>";
				foreach( $this->filter_character_status as $key => $value ) {
					echo '<option value="' . esc_attr( $key ) . '" ';
					selected( $this->active_filter_character_status, $key );
					echo '>' . vtm_formatOutput( $value ) . '</option>';
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
	}

	function display() {
		$singular = $this->_args['singular'];
		$this->display_tablenav( 'top' );
		$this->print_update_button();
		?>
		<table class="wp-list-table">
			<thead><tr><?php $this->print_column_headers(); ?></tr></thead>
			<tfoot><tr><?php $this->print_column_headers( false ); ?></tr></tfoot>
			<tbody id="the-list"<?php
				if ( $singular ) {
					echo " data-wp-lists='list:$singular'";
				} ?>>
				<?php $this->display_rows_or_placeholder(); ?>
			</tbody>
		</table>
		<?php
		$this->display_tablenav( 'bottom' );
		$this->print_update_button();
	}
	
	function prepare_items() {
        global $wpdb; 
        
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
		
		$sql = "SELECT ID FROM " . VTM_TABLE_PREFIX . "PATH_REASON WHERE NAME = 'Path Change'";
		$this->defaultreason = $wpdb->get_var($sql);
		$this->pathreasons = vtm_listPathReasons();

		/* filters */
		$this->load_filters();

		$sql = "SELECT
					characters.ID as ID,
					characters.name as CHARACTERNAME,
					paths.name as PATHNAME,
					SUM(charpaths.AMOUNT) as LEVEL
				FROM
					" . VTM_TABLE_PREFIX . "CHARACTER characters,
					" . VTM_TABLE_PREFIX . "PLAYER players,
					" . VTM_TABLE_PREFIX . "CHARACTER_ROAD_OR_PATH charpaths,
					" . VTM_TABLE_PREFIX . "ROAD_OR_PATH paths
				WHERE
					characters.PLAYER_ID = players.ID
					AND charpaths.CHARACTER_ID = characters.ID
					AND paths.ID = characters.ROAD_OR_PATH_ID
					AND characters.DELETED != 'Y'";
		
		$filterinfo = $this->get_filter_sql();
		$sql .= $filterinfo[0];
		
		$sql .= " GROUP BY characters.ID
				ORDER BY charactername";
		
		$this->_column_headers = array($columns, $hidden, $sortable);
        $this->process_bulk_action();
		$data = $wpdb->get_results($wpdb->prepare($sql,$filterinfo[1]));
		
 		
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

?>