<?php


function vtm_render_skill_types_page(){


    $testListTable["skill_type"] = new vtmclass_admin_skill_type_table();
	$doaction = vtm_skill_type_input_validation("skill_type");
	
	/* echo "<p>action: $doaction</p>"; */
	
	if ($doaction == "add-skill_type") {
		$testListTable["skill_type"]->add();		
	}
	if ($doaction == "save-skill_type") {
		$testListTable["skill_type"]->edit();				
	}

	vtm_render_skill_type_add_form("skill_type", $doaction);
	$testListTable["skill_type"]->prepare_items();
	$current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
	$current_url = remove_query_arg( 'action', $current_url );
	?>	

	<form id="skill_type-filter" method="get" action='<?php print htmlentities($current_url); ?>'>
		<input type="hidden" name="page" value="<?php print $_REQUEST['page'] ?>" />
		<input type="hidden" name="tab" value="skill_type" />
 		<?php $testListTable["skill_type"]->display() ?>
	</form>

    <?php 
}

function vtm_render_skill_type_add_form($type, $addaction) {
	global $wpdb;

	$id   = isset($_REQUEST['skill_type']) ? $_REQUEST['skill_type'] : '';
		
	if ('fix-' . $type == $addaction) {
		$name          = $_REQUEST[$type . "_name"];
		$desc          = $_REQUEST[$type . "_desc"];
		$order         = $_REQUEST[$type . "_ordering"];
		$parentid      = $_REQUEST[$type . "_parentid"];
		
		$nextaction = $_REQUEST['action'];

	} elseif ('edit-' . $type == $addaction) {
		$sql = "SELECT * FROM " . VTM_TABLE_PREFIX . "SKILL_TYPE WHERE ID = %s";
		$sql = $wpdb->prepare($sql, $id);
		$data =$wpdb->get_row($sql);
		/* echo "<p>SQL: $sql</p>";
		print_r($data); */
		
		$name          = $data->NAME;
		$desc          = $data->DESCRIPTION;
		$order         = $data->ORDERING;
		$parentid      = $data->PARENT_ID;
		
		$nextaction = "save";

	} else {
	
		$name = "";
		$desc = "";
		$order    = "";
		$parentid = 0;
		
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
			<td>Parent Category:</td>
			<td>
				<select name="<?php print $type; ?>_parentid">
					<option value="0" <?php selected($parentid, 0); ?>>None</option>
					<?php
						foreach (vtm_get_skilltypes() as $sktype) {
							print "<option value='{$sktype->ID}' ";
							selected($sktype->ID, $parentid);
							echo ">" . vtm_formatOutput($sktype->NAME) . "</option>";
						}
					?>
				</select>
			</td>
			<td>List Order:</td>
			<td><input type="number" name="<?php print $type; ?>_ordering" value="<?php print vtm_formatOutput($order); ?>" size=30 /></td>
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

function vtm_skill_type_input_validation($type) {
	
	$doaction = '';
	
	if (!empty($_REQUEST['action']) && $_REQUEST['action'] == 'edit' && $_REQUEST['tab'] == $type)
		$doaction = "edit-$type";

	if (!empty($_REQUEST[$type . '_name'])){
	
		$doaction = $_REQUEST['action'] . "-" . $type;
		
		if (empty($_REQUEST[$type . '_desc']) || $_REQUEST[$type . '_desc'] == "") {
			$doaction = "fix-$type";
			echo "<p style='color:red'>ERROR: Description is missing</p>";
		}
			
	}
	
	return $doaction;

}


/* 
-----------------------------------------------
SECT TABLE
------------------------------------------------ */


class vtmclass_admin_skill_type_table extends vtmclass_MultiPage_ListTable {
   
    function __construct(){
        global $status, $page;
                
        parent::__construct( array(
            'singular'  => 'skill_type',     
            'plural'    => 'skill_types',    
            'ajax'      => false        
        ) );
    }
 	function add() {
		global $wpdb;
		
		$wpdb->show_errors();
		
		$dataarray = array(
						'NAME'        => $_REQUEST['skill_type_name'],
						'DESCRIPTION' => $_REQUEST['skill_type_desc'],
						'PARENT_ID'   => $_REQUEST['skill_type_parentid'],
						'ORDERING'    => $_REQUEST['skill_type_ordering'],
					);
		
		/* print_r($dataarray); */
		
		$wpdb->insert(VTM_TABLE_PREFIX . "SKILL_TYPE",
					$dataarray,
					array (
						'%s',
						'%s',
						'%d',
						'%d'
					)
				);
		
		if ($wpdb->insert_id == 0) {
			echo "<p style='color:red'><b>Error:</b> " . vtm_formatOutput($_REQUEST['skill_type_name']) . " could not be inserted (";
			$wpdb->print_error();
			echo ")</p>";
		} else {
			echo "<p style='color:green'>Added " . vtm_formatOutput($_REQUEST['skill_type_name']) . "' (ID: {$wpdb->insert_id})</p>";
		}
	}

 	function edit() {
		global $wpdb;
		
		$wpdb->show_errors();
		
		$dataarray = array(
						'NAME'           => $_REQUEST['skill_type_name'],
						'DESCRIPTION'    => $_REQUEST['skill_type_desc'],
						'PARENT_ID'   => $_REQUEST['skill_type_parentid'],
						'ORDERING'    => $_REQUEST['skill_type_ordering'],
					);
		
		$result = $wpdb->update(VTM_TABLE_PREFIX . "SKILL_TYPE",
					$dataarray,
					array (
						'ID' => $_REQUEST['skill_type']
					)
				);
		
		if ($result) 
			echo "<p style='color:green'>Updated Ability Category</p>";
		else if ($result === 0) 
			echo "<p style='color:orange'>No updates made</p>";
		else {
			$wpdb->print_error();
			echo "<p style='color:red'>Could not update Ability Category ({$_REQUEST['skill_type']})</p>";
		}
		 
	}
	
 	function delete($selectedID) {
		global $wpdb;
		
		/* Check if in use */
		$sql = "select skills.NAME
				from 
					" . VTM_TABLE_PREFIX . "SKILL skills,
					" . VTM_TABLE_PREFIX . "SKILL_TYPE types
				where 
					skills.SKILL_TYPE_ID = types.ID 
					and types.ID = %d;";
					
		$isused = $wpdb->get_results($wpdb->prepare($sql, $selectedID));
		if ($isused) {
			echo "<p style='color:red'>Cannot delete as this category has been used for the following abilities:";
			echo "<ul>";
			foreach ($isused as $item)
				echo "<li style='color:red'>" . vtm_formatOutput($item->NAME) . "</li>";
			echo "</ul></p>";
			return;
			
		} else {
		
			$sql = "delete from " . VTM_TABLE_PREFIX . "SKILL_TYPE where ID = %d;";
			
			$result = $wpdb->get_results($wpdb->prepare($sql, $selectedID));
		
			echo "<p style='color:green'>Deleted Ability Category $selectedID</p>";
		}
	}
  
    function column_default($item, $column_name){
        switch($column_name){
            case 'DESCRIPTION':
                return vtm_formatOutput($item->$column_name);
            case 'ORDERING':
                return vtm_formatOutput($item->$column_name);
            case 'PARENT':
                return vtm_formatOutput($item->$column_name);
            default:
                return print_r($item,true); 
        }
    }
	

   function column_name($item){
        
        $actions = array(
            'edit'      => sprintf('<a href="?page=%s&amp;action=%s&amp;skill_type=%s&amp;tab=%s">Edit</a>',$_REQUEST['page'],'edit',$item->ID, $this->type),
            'delete'    => sprintf('<a href="?page=%s&amp;action=%s&amp;skill_type=%s&amp;tab=%s">Delete</a>',$_REQUEST['page'],'delete',$item->ID, $this->type),
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
            'PARENT'      => 'Parent Category',
            'ORDERING'    => 'List Order',
         );
        return $columns;
		
    }
    
    function get_sortable_columns() {
        $sortable_columns = array(
            'NAME'       => array('NAME',true),
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
        if( 'delete'===$this->current_action() && $_REQUEST['tab'] == $this->type && isset($_REQUEST['skill_type'])) {
			if ('string' == gettype($_REQUEST['skill_type'])) {
				$this->delete($_REQUEST['skill_type']);
			} else {
				foreach ($_REQUEST['skill_type'] as $skill_type) {
					$this->delete($skill_type);
				}
			}
        }
        		
     }
	 
        
    function prepare_items() {
        global $wpdb; 
        
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
		
		$type = "skill_type";
        			
		$this->_column_headers = array($columns, $hidden, $sortable);
        
		$this->type = $type;
        
        $this->process_bulk_action();
		
		
		/* Get the data from the database */
		$sql = "SELECT types.ID, types.NAME, types.DESCRIPTION, 
					parenttypes.NAME as PARENT, types.ORDERING
				FROM 
					" . VTM_TABLE_PREFIX . "SKILL_TYPE types
					LEFT JOIN (
						SELECT ID, NAME
						FROM " . VTM_TABLE_PREFIX . "SKILL_TYPE
					) parenttypes
					ON parenttypes.ID = types.PARENT_ID";
							
		/* order the data according to sort columns */
		if (!empty($_REQUEST['orderby']) && !empty($_REQUEST['order']))
			$sql .= " ORDER BY {$_REQUEST['orderby']} {$_REQUEST['order']}";
				
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