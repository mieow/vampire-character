<?php


function vtm_render_enlightenment_page(){


    $testListTable["enlighten"] = new vtmclass_admin_enlighten_table();
	$doaction = vtm_enlighten_input_validation("enlighten");
	
	/* echo "<p>action: $doaction</p>"; */
	
	if ($doaction == "add-enlighten") {
		$testListTable["enlighten"]->add();		
	}
	if ($doaction == "save-enlighten") {
		$testListTable["enlighten"]->edit();				
	}

	vtm_render_enlighten_add_form("enlighten", $doaction);
	$testListTable["enlighten"]->prepare_items();
	$current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
	$current_url = remove_query_arg( 'action', $current_url );
	?>	

	<form id="enlighten-filter" method="get" action='<?php print htmlentities($current_url); ?>'>
		<input type="hidden" name="page" value="<?php print $_REQUEST['page'] ?>" />
		<input type="hidden" name="tab" value="enlighten" />
 		<?php $testListTable["enlighten"]->display() ?>
	</form>

    <?php 
}

function vtm_render_enlighten_add_form($type, $addaction) {
	global $wpdb;

	$id   = isset($_REQUEST['road']) ? $_REQUEST['road'] : '';
		
	if ('fix-' . $type == $addaction) {
		$name     = $_REQUEST[$type . "_name"];
		$desc     = $_REQUEST[$type . "_desc"];
		$stat1_id = $_REQUEST[$type . "_stat1"];
		$stat2_id = $_REQUEST[$type . "_stat2"];
		$sourcebook_id = $_REQUEST[$type . "_sourcebook"];
		$pagenum  = $_REQUEST[$type . "_pagenum"];
		$visible  = $_REQUEST[$type . "_visible"];
		$costmodel_id  = $_REQUEST[$type . "_costmodel"];
		
		$nextaction = $_REQUEST['action'];

	} elseif ('edit-' . $type == $addaction) {
		$sql = "SELECT * FROM " . VTM_TABLE_PREFIX . "ROAD_OR_PATH WHERE ID = %s";
		$sql = $wpdb->prepare($sql, $id);
		$data =$wpdb->get_results($sql);
		/* echo "<p>SQL: $sql</p>";
		print_r($data); */
		
		$name     = $data[0]->NAME;
		$desc     = $data[0]->DESCRIPTION;
		$stat1_id = $data[0]->STAT1_ID;
		$stat2_id = $data[0]->STAT2_ID;
		$sourcebook_id = $data[0]->SOURCE_BOOK_ID;
		$pagenum  = $data[0]->PAGE_NUMBER;
		$visible  = $data[0]->VISIBLE;
		$costmodel_id  = $data[0]->COST_MODEL_ID;
		
		$nextaction = "save";

	} else {
	
		$name = "";
		$desc = "";
		$stat1_id = 0;
		$stat2_id = 0;
		$sourcebook_id = 4;
		$pagenum = "";
		$visible = 'Y';
		$costmodel_id = 1;
		
		$nextaction = "add";
		
	}
	
	$statinfo = vtm_get_stat_info();
	
	$conscience  = $statinfo['Conscience']->ID;  // should be stat1
	$conviction  = $statinfo['Conviction']->ID;  // should be stat1
	$selfcontrol = $statinfo['Self Control']->ID;  // should be stat2
	$instinct    = $statinfo['Instinct']->ID;  // should be stat2
	
	// swap if needed
	if ($stat1_id == $selfcontrol || $stat1_id == $instinct) {
		$tmp = $stat1_id;
		$stat1_id = $stat2_id;
		$stat2_id = $tmp;
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
			<td><input type="text" name="<?php print $type; ?>_name" value="<?php print vtm_formatOutput($name); ?>" size=20 /></td>
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
		</tr>
		<tr>
			<td>Stat 1:</td>
			<td>
				<input type="radio" name="<?php print $type; ?>_stat1" value="<?php echo $conscience; ?>" <?php if ($stat1_id == $conscience || $stat1_id == 0) print "checked"; ?>>Conscience
				<input type="radio" name="<?php print $type; ?>_stat1" value="<?php echo $conviction; ?>" <?php if ($stat1_id == $conviction) print "checked"; ?>>Conviction	
			</td>
			<td>Stat 2:  </td>
			<td>
				<input type="radio" name="<?php print $type; ?>_stat2" value="<?php echo $selfcontrol; ?>" <?php if ($stat2_id == $selfcontrol || $stat2_id == 0) print "checked"; ?>>Self Control
				<input type="radio" name="<?php print $type; ?>_stat2" value="<?php echo $instinct; ?>" <?php if ($stat2_id == $instinct) print "checked"; ?>>Instinct	
			</td>
			<td>Cost Model:  </td>
			<td colspan=3>
				<select name="<?php print $type; ?>_costmodel">
					<?php
						foreach (vtm_get_costmodels() as $costmodel) {
							print "<option value='{$costmodel->ID}' ";
							selected($costmodel->ID, $costmodel_id);
							echo ">" . vtm_formatOutput($costmodel->NAME) . "</option>";
						}
					?>
				</select>
			</td>
		</tr>
		<tr>
			<td>Description:  </td>
			<td colspan=3><input type="text" name="<?php print $type; ?>_desc" value="<?php print vtm_formatOutput($desc); ?>" size=90 /></td> 
			<td>Visible to Players:</td>
			<td>
				<select name="<?php print $type; ?>_visible">
					<option value="N" <?php selected($visible, "N"); ?>>No</option>
					<option value="Y" <?php selected($visible, "Y"); ?>>Yes</option>
				</select>
			</td>
		</tr>
		</table>
		<input type="submit" name="save_<?php print $type; ?>" class="button-primary" value="Save" />
	</form>
	
	<?php

}

function vtm_enlighten_input_validation($type) {
	
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
	
	}
	
	return $doaction;

}


/* 
-----------------------------------------------
ROAD/PATHS TABLE
------------------------------------------------ */


class vtmclass_admin_enlighten_table extends vtmclass_MultiPage_ListTable {
   
    function __construct(){
        global $status, $page;
                
        parent::__construct( array(
            'singular'  => 'road',     
            'plural'    => 'roads',    
            'ajax'      => false        
        ) );
    }
 	function add() {
		global $wpdb;
		
		$wpdb->show_errors();
		
		$dataarray = array(
						'NAME'        => $_REQUEST['enlighten_name'],
						'DESCRIPTION' => $_REQUEST['enlighten_desc'],
						'STAT1_ID'    => $_REQUEST['enlighten_stat1'],
						'STAT2_ID'    => $_REQUEST['enlighten_stat2'],
						'SOURCE_BOOK_ID'  => $_REQUEST['enlighten_sourcebook'],
						'PAGE_NUMBER' => $_REQUEST['enlighten_pagenum'],
						'VISIBLE'     => $_REQUEST['enlighten_visible'],
						'COST_MODEL_ID' => $_REQUEST['enlighten_costmodel']
					);
		
		/* print_r($dataarray); */
		
		$wpdb->insert(VTM_TABLE_PREFIX . "ROAD_OR_PATH",
					$dataarray,
					array (
						'%s',
						'%s',
						'%d',
						'%d',
						'%d',
						'%d',
						'%s',
						'%d'
					)
				);
		
		if ($wpdb->insert_id == 0) {
			echo "<p style='color:red'><b>Error:</b> " . vtm_formatOutput($_REQUEST['enlighten_name']) . " could not be inserted (";
			$wpdb->print_error();
			echo ")</p>";
		} else {
			echo "<p style='color:green'>Added " . vtm_formatOutput($_REQUEST['enlighten_name']) . "' (ID: {$wpdb->insert_id})</p>";
		}
	}

 	function edit() {
		global $wpdb;
		
		$wpdb->show_errors();
		
		$dataarray = array(
						'NAME'        => $_REQUEST['enlighten_name'],
						'DESCRIPTION' => $_REQUEST['enlighten_desc'],
						'STAT1_ID'    => $_REQUEST['enlighten_stat1'],
						'STAT2_ID'    => $_REQUEST['enlighten_stat2'],
						'SOURCE_BOOK_ID'    => $_REQUEST['enlighten_sourcebook'],
						'PAGE_NUMBER' => $_REQUEST['enlighten_pagenum'],
						'VISIBLE'     => $_REQUEST['enlighten_visible'],
						'COST_MODEL_ID' => $_REQUEST['enlighten_costmodel']
					);
		
		$result = $wpdb->update(VTM_TABLE_PREFIX . "ROAD_OR_PATH",
					$dataarray,
					array (
						'ID' => $_REQUEST['enlighten_id']
					)
				);
		
		if ($result) 
			echo "<p style='color:green'>Updated Road/Path</p>";
		else if ($result === 0) 
			echo "<p style='color:orange'>No updates made</p>";
		else {
			$wpdb->print_error();
			echo "<p style='color:red'>Could not update Road/Path ({$_REQUEST[$type . '_id']})</p>";
		}
		 
	}
	
 	function delete($selectedID) {
		global $wpdb;
		
		/* Check if question in use */
		$sql = "select characters.NAME
				from 
					" . VTM_TABLE_PREFIX . "CHARACTER characters,
					" . VTM_TABLE_PREFIX . "ROAD_OR_PATH paths
				where characters.ROAD_OR_PATH_ID = paths.ID 
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
		
			$sql = "delete from " . VTM_TABLE_PREFIX . "ROAD_OR_PATH where ID = %d;";
			
			$result = $wpdb->get_results($wpdb->prepare($sql, $selectedID));
		
			echo "<p style='color:green'>Deleted road/path $selectedID</p>";
		}
	}
  
    function column_default($item, $column_name){
        switch($column_name){
            case 'DESCRIPTION':
                return vtm_formatOutput($item->$column_name);
            case 'STAT1':
                return $item->$column_name;
            case 'STAT2':
                return $item->$column_name;
            case 'COSTMODEL':
                return vtm_formatOutput($item->$column_name);
            default:
                return print_r($item,true); 
        }
    }
	
	function column_sourcebook($item) {
		return vtm_formatOutput($item->bookname) . ", " . $item->PAGE_NUMBER;
	}

   function column_name($item){
        
        $actions = array(
            'edit'      => sprintf('<a href="?page=%s&amp;action=%s&amp;road=%s&amp;tab=%s">Edit</a>',$_REQUEST['page'],'edit',$item->ID, $this->type),
            'delete'    => sprintf('<a href="?page=%s&amp;action=%s&amp;road=%s&amp;tab=%s">Delete</a>',$_REQUEST['page'],'delete',$item->ID, $this->type),
       );
        
        
        return sprintf('%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
            vtm_formatOutput($item->NAME),
            $item->ID,
            $this->row_actions($actions)
        );
    }
   

    function get_columns(){
        $columns = array(
            'cb'         => '<input type="checkbox" />', 
            'NAME'       => 'Name',
            'DESCRIPTION' => 'Description',
            'STAT1'      => 'Stat1',
            'STAT2'      => 'Stat2',
            'SOURCEBOOK' => 'Source book',
            'COSTMODEL'  => 'Cost Model',
            'VISIBLE'    => 'Visible to Players',
         );
        return $columns;
		
    }
    
    function get_sortable_columns() {
        $sortable_columns = array(
            'NAME'     => array('NAME',true),
            'STAT1'    => array('STAT1',false),
            'STAT2'    => array('STAT2',false),
            'VISIBLE'  => array('VISIBLE',false)
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
        if( 'delete'===$this->current_action() && $_REQUEST['tab'] == $this->type && isset($_REQUEST['road'])) {
			if ('string' == gettype($_REQUEST['road'])) {
				$this->delete($_REQUEST['road']);
			} else {
				foreach ($_REQUEST['road'] as $road) {
					$this->delete($road);
				}
			}
        }
        		
     }

        
    function prepare_items() {
        global $wpdb; 
        
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
		
		$type = "enlighten";
        			
		$this->_column_headers = array($columns, $hidden, $sortable);
        
		$this->type = $type;
        
        $this->process_bulk_action();
		
		/* Get the data from the database */
		$sql = "SELECT paths.ID, paths.NAME, paths.DESCRIPTION, stats1.NAME as STAT1, stats2.NAME as STAT2,
					books.name as bookname, paths.PAGE_NUMBER, paths.VISIBLE,
					model.name as COSTMODEL
				FROM 
					" . VTM_TABLE_PREFIX . "ROAD_OR_PATH paths,
					" . VTM_TABLE_PREFIX . "STAT stats1,
					" . VTM_TABLE_PREFIX . "STAT stats2,
					" . VTM_TABLE_PREFIX . "SOURCE_BOOK books,
					" . VTM_TABLE_PREFIX . "COST_MODEL model
				WHERE 
					paths.STAT1_ID = stats1.ID
					AND paths.STAT2_ID = stats2.ID
					AND paths.SOURCE_BOOK_ID = books.ID
					AND paths.COST_MODEL_ID = model.ID";
				
		/* order the data according to sort columns */
		if (!empty($_REQUEST['orderby']) && !empty($_REQUEST['order']))
			$sql .= " ORDER BY {$_REQUEST['orderby']} {$_REQUEST['order']}";
		
		$sql .= ";";
		
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