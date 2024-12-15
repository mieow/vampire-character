<?php


function vtm_render_nature_page(){


    $testListTable["nature"] = new vtmclass_admin_nature_table();
	$doaction = vtm_nature_input_validation("nature");
	
	/* echo "<p>action: $doaction</p>"; */
	
	if ($doaction == "add-nature") {
		$testListTable["nature"]->add();		
	}
	if ($doaction == "save-nature") {
		$testListTable["nature"]->edit();				
	}

	vtm_render_nature_add_form("nature", $doaction);
	$testListTable["nature"]->prepare_items();
	$current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
	$current_url = remove_query_arg( 'action', $current_url );
	?>	

	<form id="nature-filter" method="get" action='<?php print esc_url($current_url); ?>'>
		<input type="hidden" name="page" value="<?php print esc_html($_REQUEST['page']) ?>" />
		<input type="hidden" name="tab" value="nature" />
 		<?php $testListTable["nature"]->display() ?>
	</form>

    <?php 
}

function vtm_render_nature_add_form($type, $addaction) {
	global $wpdb;

	$id   = isset($_REQUEST['nature']) ? $_REQUEST['nature'] : '';
		
	if ('fix-' . $type == $addaction) {
		$name          = $_REQUEST[$type . "_name"];
		$desc          = $_REQUEST[$type . "_desc"];
		
		$nextaction = $_REQUEST['action'];

	} elseif ('edit-' . $type == $addaction) {
		$sql = "SELECT * FROM " . VTM_TABLE_PREFIX . "NATURE WHERE ID = %s";
		$data =$wpdb->get_row($wpdb->prepare("$sql", $id));
		/* echo "<p>SQL: $sql</p>";
		print_r($data); */
		
		$name          = $data->NAME;
		$desc          = $data->DESCRIPTION;
		
		$nextaction = "save";

	} else {
	
		$name = "";
		$desc = "";
		
		$nextaction = "add";
		
	}
		
	$current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
	$current_url = remove_query_arg( 'action', $current_url );
	?>
	<form id="new-<?php print esc_html($type); ?>" method="post" action='<?php print esc_url($current_url); ?>'>
		<input type="hidden" name="<?php print esc_html($type); ?>_id" value="<?php print esc_html($id); ?>"/>
		<input type="hidden" name="tab" value="<?php print esc_html($type); ?>" />
		<input type="hidden" name="action" value="<?php print esc_html($nextaction); ?>" />
		<table>
		<tr>
			<td>Name:</td>
			<td><input type="text" name="<?php print esc_html($type); ?>_name" value="<?php print esc_html($name); ?>" size=30 /></td>
		</tr>
		<tr>
			<td>Description:  </td>
			<td><input type="text" name="<?php print esc_html($type); ?>_desc" value="<?php print esc_html($desc); ?>" size=90 /></td> 
		</tr>
		</table>
		<input type="submit" name="save_<?php print esc_html($type); ?>" class="button-primary" value="Save" />
	</form>
	
	<?php

}

function vtm_nature_input_validation($type) {
	
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
ROAD/PATHS TABLE
------------------------------------------------ */


class vtmclass_admin_nature_table extends vtmclass_MultiPage_ListTable {
   
    function __construct(){
        global $status, $page;
                
        parent::__construct( array(
            'singular'  => 'nature',     
            'plural'    => 'natures',    
            'ajax'      => false        
        ) );
    }
 	function add() {
		global $wpdb;
		
		$wpdb->show_errors();
		
		$dataarray = array(
						'NAME'           => $_REQUEST['nature_name'],
						'DESCRIPTION'    => $_REQUEST['nature_desc'],
					);
		
		/* print_r($dataarray); */
		
		$wpdb->insert(VTM_TABLE_PREFIX . "NATURE",
					$dataarray,
					array (
						'%s',
						'%s',
					)
				);
		
		if ($wpdb->insert_id == 0) {
			echo "<p style='color:red'><b>Error:</b> " . esc_html($_REQUEST['nature_name']) . " could not be inserted (";
			$wpdb->print_error();
			echo ")</p>";
		} else {
			echo "<p style='color:green'>Added " . esc_html($_REQUEST['nature_name']) . "' (ID: " . esc_html($wpdb->insert_id) . ")</p>";
		}
	}

 	function edit() {
		global $wpdb;
		
		$wpdb->show_errors();
		
		$dataarray = array(
						'NAME'           => $_REQUEST['nature_name'],
						'DESCRIPTION'    => $_REQUEST['nature_desc'],
					);
		
		$result = $wpdb->update(VTM_TABLE_PREFIX . "NATURE",
					$dataarray,
					array (
						'ID' => $_REQUEST['nature']
					)
				);
		
		if ($result) 
			echo "<p style='color:green'>Updated Nature/Demeanour</p>";
		else if ($result === 0) 
			echo "<p style='color:orange'>No updates made</p>";
		else {
			$wpdb->print_error();
			echo "<p style='color:red'>Could not update Nature/Demeanour (" . esc_html($_REQUEST['nature']) . ")</p>";
		}
		 
	}
	
 	function delete($selectedID) {
		global $wpdb;
		
		/* Check if question in use */
		$sql = "select characters.NAME
				from 
					" . VTM_TABLE_PREFIX . "CHARACTER characters,
					" . VTM_TABLE_PREFIX . "NATURE natures,
					" . VTM_TABLE_PREFIX . "NATURE demeanours
				where 
					characters.NATURE_ID = natures.ID 
					and characters.DEMEANOUR_ID = demeanours.ID 
					and (natures.ID = %d OR demeanours.ID %d)";
					
		$isused = $wpdb->get_results($wpdb->prepare("$sql", $selectedID, $selectedID));
		if ($isused) {
			echo "<p style='color:red'>Cannot delete as this nature or demeanour has been use for the following characters:";
			echo "<ul>";
			foreach ($isused as $item)
				echo "<li style='color:red'>" . esc_html($item->NAME) . "</li>";
			echo "</ul></p>";
			return;
			
		} else {
		
			$sql = "delete from " . VTM_TABLE_PREFIX . "NATURE where ID = %d;";
			
			$result = $wpdb->get_results($wpdb->prepare("$sql", $selectedID));
		
			echo "<p style='color:green'>Deleted nature/demeanour " . esc_html($selectedID) . "</p>";
		}
	}
  
    function column_default($item, $column_name){
        switch($column_name){
            case 'DESCRIPTION':
                return esc_html($item->$column_name);
            default:
                return print_r($item,true); 
        }
    }
	

   function column_name($item){
        
        $actions = array(
            'edit'      => sprintf('<a href="?page=%s&amp;action=%s&amp;nature=%s&amp;tab=%s">Edit</a>',$_REQUEST['page'],'edit',$item->ID, $this->type),
            'delete'    => sprintf('<a href="?page=%s&amp;action=%s&amp;nature=%s&amp;tab=%s">Delete</a>',$_REQUEST['page'],'delete',$item->ID, $this->type),
       );
        
        
        return sprintf('%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
            esc_html($item->NAME),
            $item->ID,
            $this->row_actions($actions)
        );
    }
   

    function get_columns(){
        $columns = array(
            'cb'          => '<input type="checkbox" />', 
            'NAME'        => 'Name',
            'DESCRIPTION' => 'Description',
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
        if( 'delete'===$this->current_action() && $_REQUEST['tab'] == $this->type && isset($_REQUEST['nature'])) {
			if ('string' == gettype($_REQUEST['nature'])) {
				$this->delete($_REQUEST['nature']);
			} else {
				foreach ($_REQUEST['nature'] as $nature) {
					$this->delete($nature);
				}
			}
        }
        		
     }

        
    function prepare_items() {
        global $wpdb; 
        
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
		
		$type = "nature";
        			
		$this->_column_headers = array($columns, $hidden, $sortable);
        
		$this->type = $type;
        
        $this->process_bulk_action();
		
		
		/* Get the data from the database */
		$sql = "SELECT
					natures.ID,
					natures.NAME,
					natures.DESCRIPTION
				FROM
					" . VTM_TABLE_PREFIX . "NATURE natures";
				
		/* order the data according to sort columns */
		if (!empty($_REQUEST['orderby']) && !empty($_REQUEST['order']))
			$sql .= " ORDER BY {$_REQUEST['orderby']} {$_REQUEST['order']}";
				
		//echo "<p>SQL: $sql</p>";
		
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
?>