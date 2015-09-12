<?php


function vtm_render_paths_page(){


    $testListTable["path"] = new vtmclass_admin_path_table();
	$doaction = vtm_path_input_validation("path");
	
	/* echo "<p>action: $doaction</p>"; */
	
	if ($doaction == "add-path") {
		$testListTable["path"]->add();		
	}
	if ($doaction == "save-path") {
		$testListTable["path"]->edit();				
	}

	vtm_render_path_add_form("path", $doaction);
	$testListTable["path"]->prepare_items();
	$current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
	$current_url = remove_query_arg( 'action', $current_url );
	?>	

	<form id="path-filter" method="get" action='<?php print htmlentities($current_url); ?>'>
		<input type="hidden" name="page" value="<?php print $_REQUEST['page'] ?>" />
		<input type="hidden" name="tab" value="path" />
 		<?php $testListTable["path"]->display() ?>
	</form>

    <?php 
}

function vtm_render_path_add_form($type, $addaction) {
	global $wpdb;

	$id   = isset($_REQUEST['path']) ? $_REQUEST['path'] : '';
		
	if ('fix-' . $type == $addaction) {
		$name          = $_REQUEST[$type . "_name"];
		$desc          = $_REQUEST[$type . "_desc"];
		$discipline_id = isset($_REQUEST[$type . "_discipline"]) ? $_REQUEST[$type . "_discipline"] : 0;
		$cost_model_id = $_REQUEST[$type . "_costmodel"];
		$sourcebook_id = $_REQUEST[$type . "_sourcebook"];
		$pagenum       = $_REQUEST[$type . "_pagenum"];
		$visible       = $_REQUEST[$type . "_visible"];
		
		$nextaction = $_REQUEST['action'];

	} elseif ('edit-' . $type == $addaction) {
		$sql = "SELECT * FROM " . VTM_TABLE_PREFIX . "PATH WHERE ID = %s";
		$sql = $wpdb->prepare($sql, $id);
		$data =$wpdb->get_row($sql);
		/* echo "<p>SQL: $sql</p>";
		print_r($data); */
		
		$name          = $data->NAME;
		$desc          = $data->DESCRIPTION;
		$discipline_id = $data->DISCIPLINE_ID;
		$cost_model_id = $data->COST_MODEL_ID;
		$sourcebook_id = $data->SOURCE_BOOK_ID;
		$pagenum       = $data->PAGE_NUMBER;
		$visible       = $data->VISIBLE;
		
		$nextaction = "save";

	} else {
	
		$name = "";
		$desc = "";
		$discipline_id = 0;
		$cost_model_id = 0;
		$sourcebook_id = 4;
		$pagenum = "";
		$visible = 'Y';
		
		$nextaction = "add";
		
	}
		
	$current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
	$current_url = remove_query_arg( 'action', $current_url );
	?>
	<form id="new-<?php print $type; ?>" method="post" action='<?php print htmlentities($current_url); ?>'>
		<input type="hidden" name="<?php print $type; ?>_id" value="<?php print $id; ?>"/>
		<input type="hidden" name="tab" value="<?php print $type; ?>" />
		<input type="hidden" name="action" value="<?php print $nextaction; ?>" />
		<table>
		<tr>
			<td>Name:</td>
			<td><input type="text" name="<?php print $type; ?>_name" value="<?php print vtm_formatOutput($name); ?>" size=30 /></td>
			<td>Discipline:</td>
			<td>
				<?php $disciplines = vtm_get_disciplines();
				if (count($disciplines) > 0) { ?>
				<select name="<?php print $type; ?>_discipline">
					<?php
						foreach ($disciplines as $discipline) {
							print "<option value='{$discipline->ID}' ";
							selected($discipline->ID, $discipline_id);
							echo ">" . vtm_formatOutput($discipline->NAME) . "</option>";
						}
					?>
				</select>
				<?php } else {
					echo "Please add disciplines to the database";
				} ?>
			</td>
			<td>Cost Model:  </td>
			<td><select name="<?php print $type; ?>_costmodel">
					<?php
						foreach (vtm_get_costmodels() as $costmodel) {
							print "<option value='{$costmodel->ID}' ";
							selected($costmodel->ID, $cost_model_id);
							echo ">" . vtm_formatOutput($costmodel->NAME) . "</option>";
						}
					?>
				</select>
			</td>
		</tr>
		<tr>
			<td>Sourcebook:  </td>
			<td>
			<select name="<?php print $type; ?>_sourcebook">
					<?php
						foreach (vtm_get_booknames() as $book) {
							print "<option value='{$book->ID}' ";
							($book->ID == $sourcebook_id) ? print "selected" : print "";
							echo ">" . vtm_formatOutput($book->NAME) . "</option>";
						}
					?>
				</select>
			</td>
			<td>Page number:  </td>
			<td><input type="number" name="<?php print $type; ?>_pagenum" value="<?php print $pagenum; ?>" /></td>
			<td>Visible to Players:</td>
			<td>
				<select name="<?php print $type; ?>_visible">
					<option value="N" <?php selected($visible, "N"); ?>>No</option>
					<option value="Y" <?php selected($visible, "Y"); ?>>Yes</option>
				</select>
			</td>
		</tr>
		<tr>
			<td>Description:  </td>
			<td colspan=5><input type="text" name="<?php print $type; ?>_desc" value="<?php print vtm_formatOutput($desc); ?>" size=90 /></td> 
		</tr>
		</table>
		<input type="submit" name="save_<?php print $type; ?>" class="button-primary" value="Save" />
	</form>
	
	<?php

}

function vtm_path_input_validation($type) {
	$doaction = '';
	
	
	if (!empty($_REQUEST['action']) && $_REQUEST['action'] == 'edit' && $_REQUEST['tab'] == $type)
		$doaction = "edit-$type";

	if (!empty($_REQUEST[$type . '_name'])){
	
		$doaction = $_REQUEST['action'] . "-" . $type;
		
		if (empty($_REQUEST[$type . '_desc']) || $_REQUEST[$type . '_desc'] == "") {
			$doaction = "fix-$type";
			echo "<p style='color:red'>ERROR: Description is missing</p>";
		}
		
		/* Page number is a number */
		if (empty($_REQUEST[$type . '_pagenum']) || $_REQUEST[$type . '_pagenum'] == "") {
			$doaction = "fix-$type";
			echo "<p style='color:red'>ERROR: sourcebook page number is missing</p>";
		} else if ($_REQUEST[$type . '_pagenum'] <= 0) {
			$doaction = "fix-$type";
			echo "<p style='color:red'>ERROR: Invalid sourcebook page number</p>";
		} 
		
		// discipline is available in database
		if (!isset($_REQUEST[$type . '_discipline'])) {
			$doaction = "fix-$type";
			echo "<p style='color:red'>ERROR: Associated discipline must be added to the database</p>";
		}
	
	}
	
	return $doaction;

}


/* 
-----------------------------------------------
ROAD/PATHS TABLE
------------------------------------------------ */


class vtmclass_admin_path_table extends vtmclass_MultiPage_ListTable {
   
    function __construct(){
        global $status, $page;
                
        parent::__construct( array(
            'singular'  => 'path',     
            'plural'    => 'paths',    
            'ajax'      => false        
        ) );
    }
 	function add() {
		global $wpdb;
		
		$wpdb->show_errors();
		
		$dataarray = array(
						'NAME'           => $_REQUEST['path_name'],
						'DESCRIPTION'    => $_REQUEST['path_desc'],
						'DISCIPLINE_ID'  => $_REQUEST['path_discipline'],
						'COST_MODEL_ID'  => $_REQUEST['path_costmodel'],
						'SOURCE_BOOK_ID' => $_REQUEST['path_sourcebook'],
						'PAGE_NUMBER'    => $_REQUEST['path_pagenum'],
						'VISIBLE'        => $_REQUEST['path_visible']
					);
		
		/* print_r($dataarray); */
		
		$wpdb->insert(VTM_TABLE_PREFIX . "PATH",
					$dataarray,
					array (
						'%s',
						'%s',
						'%d',
						'%d',
						'%d',
						'%d',
						'%s'
					)
				);
		
		if ($wpdb->insert_id == 0) {
			echo "<p style='color:red'><b>Error:</b> " . vtm_formatOutput($_REQUEST['path_name']) . " could not be inserted (";
			$wpdb->print_error();
			echo ")</p>";
		} else {
			echo "<p style='color:green'>Added " . vtm_formatOutput($_REQUEST['path_name']) . "' (ID: {$wpdb->insert_id})</p>";
		}
	}

 	function edit() {
		global $wpdb;
		
		$wpdb->show_errors();
		
		$dataarray = array(
						'NAME'           => $_REQUEST['path_name'],
						'DESCRIPTION'    => $_REQUEST['path_desc'],
						'DISCIPLINE_ID'  => $_REQUEST['path_discipline'],
						'COST_MODEL_ID'  => $_REQUEST['path_costmodel'],
						'SOURCE_BOOK_ID' => $_REQUEST['path_sourcebook'],
						'PAGE_NUMBER'    => $_REQUEST['path_pagenum'],
						'VISIBLE'        => $_REQUEST['path_visible']
					);
		
		$result = $wpdb->update(VTM_TABLE_PREFIX . "PATH",
					$dataarray,
					array (
						'ID' => $_REQUEST['path']
					)
				);
		
		if ($result) 
			echo "<p style='color:green'>Updated Path</p>";
		else if ($result === 0) 
			echo "<p style='color:orange'>No updates made</p>";
		else {
			$wpdb->print_error();
			echo "<p style='color:red'>Could not update Path ({$_REQUEST['path']})</p>";
		}
		 
	}
	
 	function delete($selectedID) {
		global $wpdb;
		
		/* Check if question in use */
		$sql = "select characters.NAME
				from 
					" . VTM_TABLE_PREFIX . "CHARACTER characters,
					" . VTM_TABLE_PREFIX . "CHARACTER_PATH charpaths,
					" . VTM_TABLE_PREFIX . "PATH paths
				where 
					characters.ID = charpaths.CHARACTER_ID 
					and paths.ID = charpaths.PATH_ID
					and paths.ID = %d;";
					
		$isused = $wpdb->get_results($wpdb->prepare($sql, $selectedID));
		if ($isused) {
			echo "<p style='color:red'>Cannot delete as this road/path has been use for the following characters:";
			echo "<ul>";
			foreach ($isused as $item)
				echo "<li style='color:red'>" . vtm_formatOutput($item->NAME) . "</li>";
			echo "</ul></p>";
			return;
			
		} else {
		
			$sql = "delete from " . VTM_TABLE_PREFIX . "PATH where ID = %d;";
			
			$result = $wpdb->get_results($wpdb->prepare($sql, $selectedID));
		
			echo "<p style='color:green'>Deleted path $selectedID</p>";
		}
	}
  
    function column_default($item, $column_name){
        switch($column_name){
            case 'DESCRIPTION':
                return vtm_formatOutput($item->$column_name);
            case 'COST_MODEL':
                return vtm_formatOutput($item->$column_name);
            case 'DISCIPLINE':
                return vtm_formatOutput($item->$column_name);
            default:
                return print_r($item,true); 
        }
    }
	
	function column_sourcebook($item) {
		return vtm_formatOutput($item->BOOKNAME) . ", " . $item->PAGE_NUMBER;
	}

   function column_name($item){
        
        $actions = array(
            'edit'      => sprintf('<a href="?page=%s&amp;action=%s&amp;path=%s&amp;tab=%s">Edit</a>',$_REQUEST['page'],'edit',$item->ID, $this->type),
            'delete'    => sprintf('<a href="?page=%s&amp;action=%s&amp;path=%s&amp;tab=%s">Delete</a>',$_REQUEST['page'],'delete',$item->ID, $this->type),
       );
        
        
        return sprintf('%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
            vtm_formatOutput($item->NAME),
            $item->ID,
            $this->row_actions($actions)
        );
    }
   

    function get_columns(){
        $columns = array(
            'cb'          => '<input type="checkbox" />', 
            'NAME'        => 'Name',
            'DESCRIPTION' => 'Description',
            'COST_MODEL'  => 'Cost Model',
            'DISCIPLINE'  => 'Associated Discipline',
            'SOURCEBOOK'  => 'Source book',
            'VISIBLE'     => 'Visible to Players',
         );
        return $columns;
		
    }
    
    function get_sortable_columns() {
        $sortable_columns = array(
            'NAME'       => array('NAME',true),
            'DISCIPLINE' => array('DISCIPLINE',false),
            'VISIBLE'    => array('VISIBLE',false)
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
        if( 'delete'===$this->current_action() && $_REQUEST['tab'] == $this->type && isset($_REQUEST['path'])) {
			if ('string' == gettype($_REQUEST['path'])) {
				$this->delete($_REQUEST['path']);
			} else {
				foreach ($_REQUEST['path'] as $path) {
					$this->delete($path);
				}
			}
        }
        		
     }
	 
	function extra_tablenav($which) {
		if ($which == 'top') {

			echo "<div class='gvfilter'>";
			
			/* Select Discipline */
			echo "<label>Discipline: </label>";
			if ( !empty( $this->filter_discipline ) ) {
				echo "<select name='{$this->type}_filter'>";
				foreach( $this->filter_discipline as $key => $value ) {
					echo '<option value="' . esc_attr( $key ) . '" ';
					selected( $this->active_filter_discipline, $key );
					echo '>' . vtm_formatOutput( $value ) . '</option>';
				}
				echo '</select>';
			}
						
			submit_button( 'Filter', 'secondary', 'do_filter_roads', false);
			echo "</div>";
		}
	}
 
        
    function prepare_items() {
        global $wpdb; 
        
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
		
		$type = "path";
        			
		$this->_column_headers = array($columns, $hidden, $sortable);
        
		$this->type = $type;
        
        $this->process_bulk_action();
		
		// FILTER ON DISCIPLINE
		$this->filter_discipline = vtm_make_filter(vtm_get_disciplines());
		if ( isset( $_REQUEST[$type . '_filter'] ) && array_key_exists( $_REQUEST[$type . '_filter'], $this->filter_discipline ) ) {
			$this->active_filter_discipline = sanitize_key( $_REQUEST[$type . '_filter'] );
		} else {
			$this->active_filter_discipline = 'all';
		}
		
		/* Get the data from the database */
		$sql = "SELECT
					paths.ID,
					paths.NAME,
					paths.DESCRIPTION,
					models.NAME as COST_MODEL,
					disciplines.NAME as DISCIPLINE,
					books.NAME as BOOKNAME,
					paths.PAGE_NUMBER,
					paths.VISIBLE
				FROM
					" . VTM_TABLE_PREFIX . "PATH paths,
					" . VTM_TABLE_PREFIX . "COST_MODEL models,
					" . VTM_TABLE_PREFIX . "DISCIPLINE disciplines,
					" . VTM_TABLE_PREFIX . "SOURCE_BOOK books
				WHERE
					paths.COST_MODEL_ID = models.ID
					AND paths.DISCIPLINE_ID = disciplines.ID
					AND paths.SOURCE_BOOK_ID = books.ID";
				
		if ( "all" !== $this->active_filter_discipline)
			$sql .= " AND disciplines.ID = %s";
			
		/* order the data according to sort columns */
		if (!empty($_REQUEST['orderby']) && !empty($_REQUEST['order']))
			$sql .= " ORDER BY {$_REQUEST['orderby']} {$_REQUEST['order']}";
		
		if ( "all" !== $this->active_filter_discipline)
			$sql = $wpdb->prepare($sql,$this->active_filter_discipline);
		
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
?>