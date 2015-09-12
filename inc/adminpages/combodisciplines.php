<?php


function vtm_render_combo_page(){


    $testListTable["combo"] = new vtmclass_admin_combo_table();
	$doaction = vtm_combo_input_validation("combo");
	
	/* echo "<p>action: $doaction</p>"; */
	
	if ($doaction == "add-combo") {
		$testListTable["combo"]->add();		
	}
	if ($doaction == "save-combo") {
		$testListTable["combo"]->edit();				
	}

	vtm_render_combo_add_form("combo", $doaction);
	$testListTable["combo"]->prepare_items();
	$current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
	$current_url = remove_query_arg( 'action', $current_url );
	?>	

	<form id="combo-filter" method="get" action='<?php print htmlentities($current_url); ?>'>
		<input type="hidden" name="page" value="<?php print $_REQUEST['page'] ?>" />
		<input type="hidden" name="tab" value="combo" />
 		<?php $testListTable["combo"]->display() ?>
	</form>

    <?php 
}

function vtm_render_combo_add_form($type, $addaction) {
	global $wpdb;

	$id   = isset($_REQUEST['combo']) ? $_REQUEST['combo'] : '';
		
	if ('fix-' . $type == $addaction) {
		$name          = $_REQUEST[$type . "_name"];
		$desc          = $_REQUEST[$type . "_desc"];
		$sourcebook    = $_REQUEST[$type . "_sourcebook"];
		$pagenum       = $_REQUEST[$type . "_page_number"];
		$xpcost        = $_REQUEST[$type . "_xp_cost"];
		$prerequisites = isset($_REQUEST[$type . "_disc"]) ? $_REQUEST[$type . "_disc"] : array();
		$visible       = $_REQUEST[$type . "_visible"];
		
		$nextaction = $_REQUEST['action'];

	} elseif ('edit-' . $type == $addaction) {
		$sql = "SELECT * FROM " . VTM_TABLE_PREFIX . "COMBO_DISCIPLINE WHERE ID = %s";
		$sql = $wpdb->prepare($sql, $id);
		$data =$wpdb->get_row($sql);
		
		$name          = $data->NAME;
		$desc          = $data->DESCRIPTION;
		$sourcebook    = $data->SOURCE_BOOK_ID;
		$pagenum       = $data->PAGE_NUMBER;
		$xpcost        = $data->COST;
		$visible       = $data->VISIBLE;
		
		$sql = "SELECT  disc.ID, prereq.DISCIPLINE_LEVEL
				FROM 
					" . VTM_TABLE_PREFIX . "COMBO_DISCIPLINE_PREREQUISITE prereq,
					" . VTM_TABLE_PREFIX . "DISCIPLINE disc
				WHERE 
					COMBO_DISCIPLINE_ID = %d
					AND disc.ID = prereq.DISCIPLINE_ID";
		$sql = $wpdb->prepare($sql, $id);
		$prerequisites = $wpdb->get_results($sql, OBJECT_K);
		
		$nextaction = "save";

	} else {
	
		$name = "";
		$desc = "";
		$sourcebook    = "";
		$pagenum       = "";
		$xpcost = "";
		$prerequisites = array();
		$visible  = 'Y';
		
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
			<td>Sourcebook:</td>
			<td>
				<select name="<?php print $type; ?>_sourcebook">
						<?php
							foreach (vtm_get_booknames() as $book) {
								print "<option value='{$book->ID}' ";
								($book->ID == $sourcebook) ? print "selected" : print "";
								echo ">" . vtm_formatOutput($book->NAME) . "</option>";
							}
						?>
				</select>
			</td>
			<td>Page number:</td>
			<td><input type="number" name="<?php print $type; ?>_page_number" value="<?php print $pagenum; ?>" /></td>
		</tr><tr>
			<td>Experience Cost:</td>
			<td><input type="number" name="<?php print $type; ?>_xp_cost" value="<?php print $xpcost; ?>" /></td>
			<td>Visible to Players:</td>
			<td>
				<select name="<?php print $type; ?>_visible">
					<option value="N" <?php selected($visible, "N"); ?>>No</option>
					<option value="Y" <?php selected($visible, "Y"); ?>>Yes</option>
				</select>
			</td>
			<td>&nbsp;</td>
		<tr>
			<td>Description:  </td>
			<td colspan=5><input type="text" name="<?php print $type; ?>_desc" value="<?php print vtm_formatOutput($desc); ?>" size=90 /></td> 
		</tr>
		</tr><tr>
			<td colspan=6><strong>Discipline Pre-requisite Levels</strong></td>
		</tr><tr>
			<td colspan=6>
			<table>
			<?php
				$col = 1;
				$disciplines = vtm_get_disciplines();
				if (count($disciplines) > 0) {
					foreach(vtm_get_disciplines() as $disc) {

						$prereq = (isset($prerequisites[$disc->ID]->DISCIPLINE_LEVEL) && $prerequisites[$disc->ID]->DISCIPLINE_LEVEL) 
									? $prerequisites[$disc->ID]->DISCIPLINE_LEVEL 
									: "0";
					
						if ($col == 1) echo "<tr>\n";
						echo "<td>" . vtm_formatOutput($disc->NAME) . "</td>\n";
						echo "<td><input type=\"number\" name=\"{$type}_disc[{$disc->ID}]\" value=\"{$prereq}\" size=4 /></td>\n";
						
						if ($col == 4) {
							echo "</tr>\n";
							$col = 1;
						} else {
							$col++;
						}
					}
				} else {
					echo "<tr><td>Please enter disciplines into the database for pre-requisites</td></tr>";
				}
			?>
			</table>
			</td>
		</tr>
		</table>
		<input type="submit" name="save_<?php print $type; ?>" class="button-primary" value="Save" />
	</form>
	
	<?php

}

function vtm_combo_input_validation($type) {
	
	$doaction = '';
	
	if (!empty($_REQUEST['action']) && $_REQUEST['action'] == 'edit' && $_REQUEST['tab'] == $type)
		$doaction = "edit-$type";

	if (!empty($_REQUEST[$type . '_name'])){
	
		$doaction = $_REQUEST['action'] . "-" . $type;
		
		if (empty($_REQUEST[$type . '_desc']) || $_REQUEST[$type . '_desc'] == "") {
			$doaction = "fix-$type";
			echo "<p style='color:red'>ERROR: Description is missing</p>";
		}
		
		//Page number is a number
		if (empty($_REQUEST[$type . '_page_number']) || $_REQUEST[$type . '_page_number'] == "") {
			$doaction = "fix-$type";
			echo "<p style='color:red'>ERROR: sourcebook page number is missing</p>";
		} else if ($_REQUEST[$type . '_page_number'] <= 0) {
			$doaction = "fix-$type";
			echo "<p style='color:red'>ERROR: Invalid sourcebook page number</p>";
		} 
		
		// cost
		if (empty($_REQUEST[$type . '_xp_cost']) || $_REQUEST[$type . '_xp_cost'] == "") {
			$doaction = "fix-$type";
			echo "<p style='color:red'>ERROR: Experience point cost is missing</p>";
		} else if ($_REQUEST[$type . '_xp_cost'] <= 0) {
			$doaction = "fix-$type";
			echo "<p style='color:red'>ERROR: Invalid Experience point cost</p>";
		} 		
			
	}
	
	return $doaction;

}



class vtmclass_admin_combo_table extends vtmclass_MultiPage_ListTable {
   
    function __construct(){
        global $status, $page;
                
        parent::__construct( array(
            'singular'  => 'combo',     
            'plural'    => 'combos',    
            'ajax'      => false        
        ) );
    }
 	function add() {
		global $wpdb;
		
		$wpdb->show_errors();
		
		$dataarray = array(
						'NAME'           => $_REQUEST['combo_name'],
						'DESCRIPTION'    => $_REQUEST['combo_desc'],
						'COST'           => $_REQUEST['combo_xp_cost'],
						'SOURCE_BOOK_ID' => $_REQUEST['combo_sourcebook'],
						'PAGE_NUMBER'    => $_REQUEST['combo_page_number'],
						'VISIBLE'        => $_REQUEST['combo_visible']
					);
				
		$wpdb->insert(VTM_TABLE_PREFIX . "COMBO_DISCIPLINE",
					$dataarray,
					array ('%s', '%s', '%d', '%d', '%d','%s')
				);
		
		$id = $wpdb->insert_id;
		if ($id == 0) {
			echo "<p style='color:red'><b>Error:</b> " . stripslashes($_REQUEST['combo_name']) . " could not be inserted (";
			$wpdb->print_error();
			echo ")</p>";
		} else {
		
			$fail    = 0;
			
			// add the pre-requisites
			if (isset($_REQUEST['combo_disc']) && count($_REQUEST['combo_disc']) > 0) {
				foreach ($_REQUEST['combo_disc'] as $key => $value) {
					if ($value > 0) {
						$dataarray = array (
							'COMBO_DISCIPLINE_ID' => $id,
							'DISCIPLINE_ID'       => $key,
							'DISCIPLINE_LEVEL'    => $value
						);
						
						$wpdb->insert(VTM_TABLE_PREFIX . "COMBO_DISCIPLINE_PREREQUISITE",
							$dataarray, array ('%d', '%d', '%d')
						);
						
						if ($wpdb->insert_id == 0) {
							$fail++;
						} 
					}
			
				}
			}
			if ($fail) {
				echo "<p style='color:red'>Could not add Combination Discipline pre-requisites ({$_REQUEST['combo_name']})</p>";
			} 
			else {
				echo "<p style='color:green'>Added " . vtm_formatOutput($_REQUEST['combo_name']) . "' (ID: {$id})</p>";
			}
		
		}
	}

 	function edit() {
		global $wpdb;
		
		$wpdb->show_errors();
		
		$updates = 0;
		$fail    = 0;
		
		$dataarray = array(
						'NAME'           => $_REQUEST['combo_name'],
						'DESCRIPTION'    => $_REQUEST['combo_desc'],
						'COST'           => $_REQUEST['combo_xp_cost'],
						'SOURCE_BOOK_ID' => $_REQUEST['combo_sourcebook'],
						'PAGE_NUMBER'    => $_REQUEST['combo_page_number'],
						'VISIBLE'        => $_REQUEST['combo_visible']
					);
		
		$result = $wpdb->update(VTM_TABLE_PREFIX . "COMBO_DISCIPLINE",
					$dataarray,
					array (
						'ID' => $_REQUEST['combo']
					)
				);
		
		if ($result) 
			$updates++;
		else if ($result !== 0) {
			$fail = 1;
			echo "<p style='color:red'>Could not update Combination Discipline ({$_REQUEST['combo_name']})</p>";
		}
		
		if (!$fail) {
			// remove all current pre-requisites for this combo-discipline
			$sql = "DELETE FROM " . VTM_TABLE_PREFIX . "COMBO_DISCIPLINE_PREREQUISITE
					WHERE COMBO_DISCIPLINE_ID = %d";
			$sql = $wpdb->prepare($sql, $_REQUEST['combo']);
			$result = $wpdb->get_results($sql);
			
			// add the ones we want
			foreach ($_REQUEST['combo_disc'] as $key => $value) {
				if ($value > 0) {
					$dataarray = array (
						'COMBO_DISCIPLINE_ID' => $_REQUEST['combo'],
						'DISCIPLINE_ID'       => $key,
						'DISCIPLINE_LEVEL'    => $value
					);
					
					$wpdb->insert(VTM_TABLE_PREFIX . "COMBO_DISCIPLINE_PREREQUISITE",
						$dataarray, array ('%d', '%d', '%d')
					);
					
					if ($wpdb->insert_id == 0) {
						$fail++;
					} else {
						$updates++;
					}
				}
		
			}
			
			if ($fail) {
				echo "<p style='color:red'>Could not update Combination Discipline pre-requisites (" . vtm_formatOutput($_REQUEST['combo_name']) . ")</p>";
			} 
			elseif (!$updates) {
				echo "<p style='color:orange'>No updates made to Combination Discipline</p>";
			}
			else {
				echo "<p style='color:green'>Updated Combination Discipline " . vtm_formatOutput($_REQUEST['combo_name']) . "</p>";
			}
		}
	}
	
 	function delete($selectedID) {
		global $wpdb;
		
		
		$sql = "select characters.NAME
				from 
					" . VTM_TABLE_PREFIX . "CHARACTER characters,
					" . VTM_TABLE_PREFIX . "CHARACTER_COMBO_DISCIPLINE charcombos,
					" . VTM_TABLE_PREFIX . "COMBO_DISCIPLINE combos
				where 
					characters.ID = charcombos.CHARACTER_ID 
					and combos.ID = charcombos.COMBO_DISCIPLINE_ID
					and combos.ID = %d;";
					
		$isused = $wpdb->get_results($wpdb->prepare($sql, $selectedID));
		if ($isused) {
			echo "<p style='color:red'>Cannot delete as this combo discipline has been use for the following characters:";
			echo "<ul>";
			foreach ($isused as $item)
				echo "<li style='color:red'>" . vtm_formatOutput($item->NAME) . "</li>";
			echo "</ul></p>";
			return;
			
		} else {
		
			$sql = "delete from " . VTM_TABLE_PREFIX . "COMBO_DISCIPLINE_PREREQUISITE where COMBO_DISCIPLINE_ID = %d;";
			$result = $wpdb->get_results($wpdb->prepare($sql, $selectedID));
			$sql = "delete from " . VTM_TABLE_PREFIX . "COMBO_DISCIPLINE where ID = %d;";
			$result = $wpdb->get_results($wpdb->prepare($sql, $selectedID));
		
			echo "<p style='color:green'>Deleted combo $selectedID</p>";
		} 
	}
  
    function column_default($item, $column_name){
        switch($column_name){
            case 'DESCRIPTION':
                return vtm_formatOutput($item->$column_name);
            case 'COST':
                return $item->$column_name;
            default:
                return print_r($item,true); 
        }
    }
	
	function column_sourcebook($item) {
		return vtm_formatOutput($item->BOOKNAME) . ", p" . $item->PAGE_NUMBER;
	}
	function column_prerequisites($item) {
	
		$outarray = array();
		if (count($this->prerequisites) > 0) {
			$list = $this->prerequisites[$item->ID];
			
			if (count($list) > 0) {
				foreach ($list as $row) {
					array_push($outarray, $row->discipline . " " . $row->level);
				}
			}
			
			$out = count($outarray) > 0 ? implode(', ', $outarray) : "None";
		} else {
			$out = 'None';
		}
	
		return vtm_formatOutput($out);
	}
	
   function column_name($item){
        
        $actions = array(
            'edit'      => sprintf('<a href="?page=%s&amp;action=%s&amp;combo=%s&amp;tab=%s">Edit</a>',$_REQUEST['page'],'edit',$item->ID, $this->type),
            'delete'    => sprintf('<a href="?page=%s&amp;action=%s&amp;combo=%s&amp;tab=%s">Delete</a>',$_REQUEST['page'],'delete',$item->ID, $this->type),
       );
        
        
        return sprintf('%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
            vtm_formatOutput($item->NAME),
            $item->ID,
            $this->row_actions($actions)
        );
    }
   

    function get_columns(){
        $columns = array(
            'cb'            => '<input type="checkbox" />', 
            'NAME'          => 'Name',
            'DESCRIPTION'   => 'Description',
            'SOURCEBOOK'    => 'Source Book',
            'COST'          => 'Experience Cost',
            'PREREQUISITES' => 'Pre-Requisites',
            'VISIBLE'       => 'Visible to Players',
         );
        return $columns;
		
    }
    
    function get_sortable_columns() {
        $sortable_columns = array(
            'NAME'       => array('NAME',true),
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
        if( 'delete'===$this->current_action() && $_REQUEST['tab'] == $this->type && isset($_REQUEST['combo'])) {
			if ('string' == gettype($_REQUEST['combo'])) {
				$this->delete($_REQUEST['combo']);
			} else {
				foreach ($_REQUEST['combo'] as $combo) {
					$this->delete($combo);
				}
			}
        }
        		
     }
	 
	function reformat_info($data) {
	
		$arr = array();
		
		foreach ($data as $row) {
			if (array_key_exists($row->COMBO_DISCIPLINE_ID, $arr)) {
				array_push($arr[$row->COMBO_DISCIPLINE_ID],$row);
			} else {
				$arr[$row->COMBO_DISCIPLINE_ID] = array($row);
			}
		
		}		
		
		return $arr;
	}
        
    function prepare_items() {
        global $wpdb; 
        
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
		
		$type = "combo";
        			
		$this->_column_headers = array($columns, $hidden, $sortable);
        
		$this->type = $type;
        
        $this->process_bulk_action();
				
		/* Get the data from the database */
		$sql = "SELECT combo.*, books.name as BOOKNAME
				FROM
					" . VTM_TABLE_PREFIX . "COMBO_DISCIPLINE combo,
					" . VTM_TABLE_PREFIX . "SOURCE_BOOK books
				WHERE
					books.ID = combo.SOURCE_BOOK_ID";
							
		/* order the data according to sort columns */
		if (!empty($_REQUEST['orderby']) && !empty($_REQUEST['order']))
			$sql .= " ORDER BY {$_REQUEST['orderby']} {$_REQUEST['order']}";
		$data =$wpdb->get_results($sql);
        $this->items = $data;
		
		// Get the combo pre-requisite discipline data
		$sql = "SELECT prereq.COMBO_DISCIPLINE_ID , dis.NAME as discipline, prereq.DISCIPLINE_LEVEL as level 
				FROM 
					" . VTM_TABLE_PREFIX . "COMBO_DISCIPLINE_PREREQUISITE prereq,
					" . VTM_TABLE_PREFIX . "DISCIPLINE dis
				WHERE dis.ID = prereq.DISCIPLINE_ID";
		$pre = $wpdb->get_results($sql);
		$this->prerequisites = $this->reformat_info($pre);
		//print_r($this->prerequisites);
        
        $current_page = $this->get_pagenum();
        $total_items = count($data);
        
        
        $this->set_pagination_args( array(
            'total_items' => $total_items,                  
            'per_page'    => $total_items,                  
            'total_pages' => 1
        ) );
    }

}
?>