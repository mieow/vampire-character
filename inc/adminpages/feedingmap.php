<?php

function vtm_render_owner_data(){
	global $wpdb;

    $ownerTable = new vtmclass_owner_table();
	
	if (isset($_REQUEST['do_add_owner'])) {
		if ($_REQUEST['owner']) 
			$ownerTable->edit();
		else
			$ownerTable->add();
	}

	vtm_render_owner_add_form();
	
	$ownerTable->prepare_items();

	$current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
	$current_url = remove_query_arg( 'action', $current_url );

   ?>	

	<!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
	<form id="feedingmap_owner_section" method="get" action='<?php print htmlentities($current_url); ?>'>
		<input type="hidden" name="page" value="<?php print $_REQUEST['page'] ?>" />
		<input type="hidden" name="tab" value="mapowner" />
 		<?php $ownerTable->display() ?>
	</form>

    <?php
}
function vtm_render_domain_data(){
	global $wpdb;

    $domainTable = new vtmclass_domain_table();
	$defaultCoord = "lat,long";

	// Validation
		
	$inputsok = 1;
	if (isset($_REQUEST['do_add_domain']) && !empty($_REQUEST['domain_name'])) {
		if (empty($_REQUEST['domain_coordinates']) || $_REQUEST['domain_coordinates'] == ""  || $_REQUEST['domain_coordinates'] == $defaultCoord) {
			$inputsok = 0;
			echo "<p style='color:red'>ERROR: Enter coordinates</p>";
		}
		
		if ($inputsok) {
			if ($_REQUEST['mapdomain']) 
				$domainTable->edit();
			else
				$domainTable->add();
		}
	}
	vtm_render_feedingdomain_add_form($inputsok);
	
	$domainTable->prepare_items();

	$current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
	$current_url = remove_query_arg( 'action', $current_url );

   ?>	

	<!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
	<form id="feedingmap_domain_section" method="get" action='<?php print htmlentities($current_url); ?>'>
		<input type="hidden" name="page" value="<?php print $_REQUEST['page'] ?>" />
		<input type="hidden" name="tab" value="mapdomain" />
 		<?php $domainTable->display() ?>
	</form>

    <?php
}

function vtm_render_owner_add_form() {
	global $wpdb;

	$thisaction = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
	
	switch ($thisaction) {
	case "edit":
		$id          = $_REQUEST['owner'];
		
		$sql = "SELECT * FROM " . VTM_TABLE_PREFIX . "MAPOWNER WHERE ID = %d";
		$data =$wpdb->get_row($wpdb->prepare($sql, $id));
		
		$name        = $data->NAME;
		$visible     = $data->VISIBLE;
		$fillcolour  = $data->FILL_COLOUR;
		
		break;
	default:
		$id = "";
		$name = "";
		$visible = 'Y';
		$fillcolour = "#FFFFFF";
		
	}
	
	$current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
	$current_url = remove_query_arg( 'action', $current_url );
	?>
	<form id="new-owner" method="post" action='<?php print htmlentities($current_url); ?>'>
		<input type="hidden" name="owner" value="<?php print $id; ?>"/>
		<input type="hidden" name="tab" value="mapowner" />
		<input type="hidden" name="action" value="save" />
		<table style='width:500px'>
		<tr>
			<td>Name:  </td>
			<td><input type="text" name="owner_name" value="<?php print vtm_formatOutput($name); ?>" /></td>
			<td>Fill Colour:  </td>
			<td><input type="color" name="owner_fill" value="<?php print $fillcolour; ?>" /></td>
			<td>Visible:  </td>
			<td>
				<select name="owner_visible">
					<option value="N" <?php selected($visible, "N"); ?>>No</option>
					<option value="Y" <?php selected($visible, "Y"); ?>>Yes</option>
				</select>
			</td>
		</tr>
		</table>
		
		
		
		</table>
		<input type="submit" name="do_add_owner" class="button-primary" value="Save Owner" />
	</form>
	
	<?php

}
function vtm_render_feedingdomain_add_form($inputsok) {
	global $wpdb;

	$defaultCoord = "lat,long";
	
	//echo "<p>Inputs: $inputsok, action: {$_REQUEST['action']}, table: </p>";
	// if (!$inputsok) {
			// $id          = $_REQUEST['mapdomain'];
			// $name        = $_REQUEST['domain_name'];
			// $visible     = $_REQUEST['domain_visible'];
			// $description = $_REQUEST['domain_desc'];
			// $coordinates = $_REQUEST['domain_coordinates'];
			// $ownerid     = $_REQUEST['domain_owner'];
	// } 
	// else
	if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'edit' && $_REQUEST['tab'] == 'mapdomain') {
			$id          = $_REQUEST['mapdomain'];
			
			$sql = "SELECT * FROM " . VTM_TABLE_PREFIX . "MAPDOMAIN WHERE ID = %d";
			$data =$wpdb->get_row($wpdb->prepare($sql, $id));
			
			$name        = $data->NAME;
			$visible     = $data->VISIBLE;
			$description = $data->DESCRIPTION;
			$coordinates = $data->COORDINATES;
			$ownerid     = $data->OWNER_ID;
	} else {
			$id = "";
			$name = "";
			$visible = 'Y';
			$description = "";
			$coordinates = $defaultCoord;
			$ownerid = 0;
	}
		
	$current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
	$current_url = remove_query_arg( 'action', $current_url );
	?>
	<form id="new-mapdomain" method="post" action='<?php print htmlentities($current_url); ?>'>
		<input type="hidden" name="mapdomain" value="<?php print $id; ?>"/>
		<input type="hidden" name="tab" value="mapdomain" />
		<input type="hidden" name="action" value="save" />
		<table style='width:500px'>
		<tr><td style='vertical-align:top;'>
			<table>
				<tr>
					<td>Name:  </td>
					<td colspan=3><input type="text" name="domain_name" value="<?php print vtm_formatOutput($name); ?>" /></td>
				</tr>
				<tr>
					<td>Owner:  </td>
					<td>
						<select name="domain_owner">
						<?php
							foreach (vtm_get_owners() as $id => $info) {
								echo "<option value=\"$id\" ";
								selected($ownerid, $id);
								echo ">" . vtm_formatOutput($info->NAME) . "</option>\n";
							}
						?>
						</select>
					</td>
					<td>Visible to Players: </td>
					<td>
						<select name="domain_visible">
							<option value="N" <?php selected($visible, "N"); ?>>No</option>
							<option value="Y" <?php selected($visible, "Y"); ?>>Yes</option>
						</select>
					</td>
				</tr>
				<tr>
					<td>Description:  </td>
					<td colspan=3>
						<textarea name="domain_desc" rows=6 cols=40><?php print vtm_formatOutput($description); ?></textarea></td>
				</tr>
			</table>
		</td>
		<td style='vertical-align:top;'>Coordinates List:<i><br>lat,long<br>lat,long<br>...</i></td>
		<td>
			<textarea name="domain_coordinates" rows=10 cols=20><?php print $coordinates; ?></textarea>
		</td>
		</tr>
		</table>
		
		
		
		</table>
		<input type="submit" name="do_add_domain" class="button-primary" value="Save Location" />
	</form>
	
	<?php
}

function vtm_get_owners() {
	global $wpdb;

	$sql = "SELECT ID, NAME FROM " . VTM_TABLE_PREFIX . "MAPOWNER;";
	$list = $wpdb->get_results($sql,OBJECT_K);
	
	return $list;
}

class vtmclass_owner_table extends WP_List_Table {
   
    function __construct(){
        global $status, $page;
                
        parent::__construct( array(
            'singular'  => 'owner',     
            'plural'    => 'owners',    
            'ajax'      => false        
        ) );
    }
	
	function add() {
		global $wpdb;
		$wpdb->show_errors();
		
		$dataarray = array(
						'NAME' => $_REQUEST['owner_name'],
						'FILL_COLOUR' => $_REQUEST['owner_fill'],
						'VISIBLE' => $_REQUEST['owner_visible']
					);
	
		$wpdb->insert(VTM_TABLE_PREFIX . "MAPOWNER",
					$dataarray,
					array (
						'%s',
						'%s',
						'%s'
					)
				);
		
		$owner = vtm_formatOutput($_REQUEST['owner_name']);
		if ($wpdb->insert_id == 0) {
			echo "<p style='color:red'><b>Error:</b> $owner could not be inserted</p>";
		} else {
			echo "<p style='color:green'>Added owner '$owner' (ID: {$wpdb->insert_id})</p>";
		}
	}
 	function edit() {
		global $wpdb;
		$wpdb->show_errors();
		
		$dataarray = array(
						'NAME' => $_REQUEST['owner_name'],
						'FILL_COLOUR' => $_REQUEST['owner_fill'],
						'VISIBLE' => $_REQUEST['owner_visible']
					);
	
		$result = $wpdb->update(VTM_TABLE_PREFIX . "MAPOWNER",
					$dataarray,
					array (
						'ID' => $_REQUEST['owner']
					)
				);
		
		$owner = vtm_formatOutput($_REQUEST['owner_name']);
		if ($result) 
			echo "<p style='color:green'>Updated $owner</p>";
		else if ($result === 0) 
			echo "<p style='color:orange'>No updates made to $owner</p>";
		else {
			echo "<p style='color:red'>Could not update $owner ({$_REQUEST['owner']})</p>";
		}
	}
	function delete($selectedID) {
		global $wpdb;
		
		$sql = "select domains.NAME 
			from 
				" . VTM_TABLE_PREFIX . "MAPOWNER owners , 
				" . VTM_TABLE_PREFIX . "MAPDOMAIN domains
			where owners.ID = %d and domains.OWNER_ID = owners.ID;";
		$isused = $wpdb->get_results($wpdb->prepare($sql, $selectedID));
		
		if ($isused) {
			echo "<p style='color:red'>Cannot delete as owner is assigned to the following locations:";
			echo "<ul>";
			foreach ($isused as $mapdomain)
				echo "<li style='color:red'>" . vtm_formatOutput($mapdomain->NAME) . "</li>";
			echo "</ul></p>";
		} else {
			$sql = "delete from " . VTM_TABLE_PREFIX . "MAPOWNER where ID = %d;";
			
			$result = $wpdb->get_results($wpdb->prepare($sql, $selectedID));
		
			/* print_r($result); */
			echo "<p style='color:green'>Deleted item $selectedID</p>";
		}
	}
 	function showhide($selectedID, $showhide) {
		global $wpdb;
		
		//echo "id: $selectedID, setting: $showhide";
		if (empty($selectedID)) return;
		
		$wpdb->show_errors();
		
		$visiblity = $showhide == 'hide' ? 'N' : 'Y';
		
		$result = $wpdb->update( VTM_TABLE_PREFIX . "MAPOWNER", 
			array (
				'VISIBLE' => $visiblity
			), 
			array (
				'ID' => $selectedID
			)
		);
		
		if ($result) 
			echo "<p style='color:green'>" . ucfirst($showhide) . " item $selectedID successful</p>";
		else if ($result === 0)
			echo "<p style='color:orange'>Item $selectedID has not been changed</p>";
		else {
			$wpdb->print_error();
			echo "<p style='color:red'>Item $selectedID could not be updated</p>";
		}
	}
  
    function column_default($item, $column_name){
        switch($column_name){
             default:
                return print_r($item,true); 
        }
    }
	
    function column_visible($item){
		return ($item->VISIBLE == "Y") ? "Yes" : "No";
    }
    function column_fill_colour($item){
		return "<span style='background-color:" . $item->FILL_COLOUR . ";'>" . $item->FILL_COLOUR . "</span>";
    }
 
    function column_name($item){
		$act = ($item->VISIBLE === 'Y') ? 'hide' : 'show';
        
        $actions = array(
            $act     => sprintf('<a href="?page=%s&amp;action=%s&amp;owner=%s&amp;tab=%s">%s</a>',$_REQUEST['page'],$act,$item->ID, 'mapowner', ucfirst($act)),
            'edit'   => sprintf('<a href="?page=%s&amp;action=%s&amp;owner=%s&amp;tab=%s">Edit</a>',$_REQUEST['page'],'edit',$item->ID, 'mapowner'),
            'delete' => sprintf('<a href="?page=%s&amp;action=%s&amp;owner=%s&amp;tab=%s">Delete</a>',$_REQUEST['page'],'delete',$item->ID, 'mapowner'),
        );
        
        
        return sprintf('%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
            vtm_formatOutput($item->NAME),
            $item->ID,
            $this->row_actions($actions)
        );
    }
   
    function column_cb($item){
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            $this->_args['singular'],  
            $item->ID
        );
    }

    function get_columns(){
        $columns = array(
            'cb'           => '<input type="checkbox" />', 
            'NAME'         => 'Name',
            'FILL_COLOUR'  => 'Fill Colour',
            'VISIBLE'      => 'Visible',
        );
        return $columns;
		
    }
    
/*     function get_sortable_columns() {
        $sortable_columns = array(
            'NAME'        => array('NAME',true)
        );
        return $sortable_columns;
    }
 */	
    
    function get_bulk_actions() {
        $actions = array(
            'show'   => 'Show',
            'hide'   => 'Hide',
            'delete' => 'Delete',
       );
        return $actions;
    }
    
    function process_bulk_action() {
        		
 		if( 'delete'===$this->current_action() && $_REQUEST['tab'] == 'mapowner' && isset($_REQUEST['owner'])) {

			if ('string' == gettype($_REQUEST['owner'])) {
				$this->delete($_REQUEST['owner']);
			} else {
				foreach ($_REQUEST['owner'] as $owner) {
					$this->delete($owner);
				}
			}
        }
       if( 'hide'===$this->current_action() && $_REQUEST['tab'] == 'mapowner' && isset($_REQUEST['owner']) ) {
			if ('string' == gettype($_REQUEST['owner'])) {
				$this->showhide($_REQUEST['owner'], "hide");
			} else {
				foreach ($_REQUEST['owner'] as $owner) {
					$this->showhide($owner, "hide");
				}
			}
        }
        if( 'show'===$this->current_action() && $_REQUEST['tab'] == 'mapowner' && isset($_REQUEST['owner']) ) {
			if ('string' == gettype($_REQUEST['owner'])) {
				$this->showhide($_REQUEST['owner'], "show");
			} else {
				foreach ($_REQUEST['owner'] as $owner) {
					$this->showhide($owner, "show");
				}
			}
        }

     }
        
    function prepare_items() {
		global $wpdb;
        
        $columns  = $this->get_columns();
        $hidden   = array();
        $sortable = array(); //$this->get_sortable_columns();
		        			
		$this->_column_headers = array($columns, $hidden, $sortable);
        		
        $this->process_bulk_action();
		
		$sql  = "SELECT * FROM " . VTM_TABLE_PREFIX . "MAPOWNER ORDER BY NAME";
		$data = $wpdb->get_results($sql);

		$this->items = $data;

        $current_page = $this->get_pagenum();
        $total_items = count($data);
                
        $this->set_pagination_args( array(
            'total_items' => $total_items,                  
            'per_page'    => $total_items,                  
            'total_pages' => 1
        ) );
    }

}

class vtmclass_domain_table extends WP_List_Table {
   
    function __construct(){
        global $status, $page;
                
        parent::__construct( array(
            'singular'  => 'mapdomain',     
            'plural'    => 'mapdomains',    
            'ajax'      => false        
        ) );
    }
	
	function add() {
		global $wpdb;
		$wpdb->show_errors();
		
		$dataarray = array(
						'NAME' => $_REQUEST['domain_name'],
						'OWNER_ID' => $_REQUEST['domain_owner'],
						'DESCRIPTION' => $_REQUEST['domain_desc'],
						'COORDINATES' => $_REQUEST['domain_coordinates'],
						'VISIBLE' => $_REQUEST['domain_visible']
					);
	
		$wpdb->insert(VTM_TABLE_PREFIX . "MAPDOMAIN",
					$dataarray,
					array (
						'%s',
						'%d',
						'%s',
						'%s',
						'%s'
					)
				);
		
		if ($wpdb->insert_id == 0) {
			echo "<p style='color:red'><b>Error:</b> " . vtm_formatOutput($_REQUEST['domain_name']) . " could not be inserted</p>";
		} else {
			echo "<p style='color:green'>Added location '" . vtm_formatOutput($_REQUEST['domain_name']) . "' (ID: {$wpdb->insert_id})</p>";
		}
	}
 	function edit() {
		global $wpdb;
		$wpdb->show_errors();
		
		$dataarray = array(
						'NAME' => $_REQUEST['domain_name'],
						'OWNER_ID' => $_REQUEST['domain_owner'],
						'DESCRIPTION' => $_REQUEST['domain_desc'],
						'COORDINATES' => $_REQUEST['domain_coordinates'],
						'VISIBLE' => $_REQUEST['domain_visible']
					);
	
		$result = $wpdb->update(VTM_TABLE_PREFIX . "MAPDOMAIN",
					$dataarray,
					array (
						'ID' => $_REQUEST['mapdomain']
					)
				);
		
		if ($result) 
			echo "<p style='color:green'>Updated " . vtm_formatOutput($_REQUEST['domain_name']) . "</p>";
		else if ($result === 0) 
			echo "<p style='color:orange'>No updates made to " . vtm_formatOutput($_REQUEST['domain_name']) . "</p>";
		else {
			echo "<p style='color:red'>Could not update " . vtm_formatOutput($_REQUEST['domain_name']) . " ({$_REQUEST['mapdomain']})</p>";
		}
	}
	function assign($selectedID) {
		global $wpdb;
		$wpdb->show_errors();
		
		$dataarray = array(
						'OWNER_ID' => $this->ownerselect
					);
	
		$result = $wpdb->update(VTM_TABLE_PREFIX . "MAPDOMAIN",
					$dataarray,
					array (
						'ID' => $selectedID
					)
				);
		
		if ($result) 
			echo "<p style='color:green'>Updated map location $selectedID</p>";
		else if ($result === 0) 
			echo "<p style='color:orange'>No updates made to $selectedID</p>";
		else {
			echo "<p style='color:red'>Could not update $selectedID</p>";
		}
	}
	
	function delete($selectedID) {
		global $wpdb;
		
		$sql = "delete from " . VTM_TABLE_PREFIX . "MAPDOMAIN where ID = %d;";
		$wpdb->get_results($wpdb->prepare($sql, $selectedID));
		echo "<p style='color:green'>Deleted mapdomain {$selectedID}</p>";
	}
	
 	function showhide($selectedID, $showhide) {
		global $wpdb;
		
		//echo "id: $selectedID, setting: $showhide";
		if (empty($selectedID)) return;
		
		$wpdb->show_errors();
		
		$visiblity = $showhide == 'hide' ? 'N' : 'Y';
		
		$result = $wpdb->update( VTM_TABLE_PREFIX . "MAPDOMAIN", 
			array (
				'VISIBLE' => $visiblity
			), 
			array (
				'ID' => $selectedID
			)
		);
		
		if ($result) 
			echo "<p style='color:green'>" . ucfirst($showhide) . " item $selectedID successful</p>";
		else if ($result === 0)
			echo "<p style='color:orange'>Item $selectedID has not been changed</p>";
		else {
			$wpdb->print_error();
			echo "<p style='color:red'>Item $selectedID could not be updated</p>";
		}
	}

    function column_default($item, $column_name){
        switch($column_name){
            case 'DESCRIPTION':
                return vtm_formatOutput($item->$column_name);
            case 'OWNER':
                return vtm_formatOutput($item->$column_name);
             default:
                return print_r($item,true); 
        }
    }
	
    function column_visible($item){
		return ($item->VISIBLE == "Y") ? "Yes" : "No";
    }
    function column_fill_colour($item){
		return "<span style='background-color:" . $item->FILL_COLOUR . ";'>" . $item->FILL_COLOUR . "</span>";
    }
 
    function column_name($item){
		$act = ($item->VISIBLE === 'Y') ? 'hide' : 'show';
        
        $actions = array(
            $act     => sprintf('<a href="?page=%s&amp;action=%s&amp;mapdomain=%s&amp;tab=%s">%s</a>',$_REQUEST['page'],$act,$item->ID, 'mapdomain', ucfirst($act)),
            'edit'   => sprintf('<a href="?page=%s&amp;action=%s&amp;mapdomain=%s&amp;tab=%s">Edit</a>',$_REQUEST['page'],'edit',$item->ID, 'mapdomain'),
            'delete' => sprintf('<a href="?page=%s&amp;action=%s&amp;mapdomain=%s&amp;tab=%s">Delete</a>',$_REQUEST['page'],'delete',$item->ID, 'mapdomain'),
        );
        
        
        return sprintf('%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
            vtm_formatOutput($item->NAME),
            $item->ID,
            $this->row_actions($actions)
        );
    }
   
    function column_cb($item){
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            $this->_args['singular'],  
            $item->ID
        );
    }

    function get_columns(){
        $columns = array(
            'cb'           => '<input type="checkbox" />', 
            'NAME'         => 'Name',
            'OWNER'        => 'Owner',
            'DESCRIPTION'  => 'Description',
            'VISIBLE'      => 'Visible',
        );
        return $columns;
		
    }
    
    function get_sortable_columns() {
        $sortable_columns = array(
            'NAME'        => array('NAME',true),
            'OWNER'       => array('OWNER',false),
            'VISIBLE'       => array('VISIBLE',false)
      );
        return $sortable_columns;
    }
	
	
	
    //assign owner, remove owner, show/hide, delete
    function get_bulk_actions() {
        $actions = array(
            'show'     => 'Show',
            'hide'     => 'Hide',
            'assign'   => 'Assign',
            'delete'   => 'Delete',
      );
        return $actions;
    }
    
    function process_bulk_action() {
        		
		if( 'delete'===$this->current_action() && $_REQUEST['tab'] == 'mapdomain' && isset($_REQUEST['mapdomain'])) {

			if ('string' == gettype($_REQUEST['mapdomain'])) {
				$this->delete($_REQUEST['mapdomain']);
			} else {
				foreach ($_REQUEST['mapdomain'] as $mapdomain) {
					$this->delete($mapdomain);
				}
			}
        }
        if( 'hide'===$this->current_action() && $_REQUEST['tab'] == 'mapdomain' && isset($_REQUEST['mapdomain']) ) {
			if ('string' == gettype($_REQUEST['mapdomain'])) {
				$this->showhide($_REQUEST['mapdomain'], "hide");
			} else {
				foreach ($_REQUEST['mapdomain'] as $mapdomain) {
					$this->showhide($mapdomain, "hide");
				}
			}
        }
        if( 'show'===$this->current_action() && $_REQUEST['tab'] == 'mapdomain' && isset($_REQUEST['mapdomain']) ) {
			if ('string' == gettype($_REQUEST['mapdomain'])) {
				$this->showhide($_REQUEST['mapdomain'], "show");
			} else {
				foreach ($_REQUEST['mapdomain'] as $mapdomain) {
					$this->showhide($mapdomain, "show");
				}
			}
        }
        if( 'assign'===$this->current_action() && $_REQUEST['tab'] == 'mapdomain' && isset($_REQUEST['mapdomain']) ) {
			if ('string' == gettype($_REQUEST['mapdomain'])) {
				$this->assign($_REQUEST['mapdomain']);
			} else {
				foreach ($_REQUEST['mapdomain'] as $mapdomain) {
					$this->assign($mapdomain);
				}
			}
        }

     }
 	function extra_tablenav($which) {
		if ($which == 'top') {

			echo "<div class='selectowner'>";
			echo "<label>Select New Owner: </label>";
			if ( !empty( $this->ownerlist ) ) {
				echo "<select name='ownerselect'>";
				foreach( $this->ownerlist as $key => $object ) {
					echo '<option value="' . esc_attr( $object->ID ) . '" ';
					selected( $this->ownerselect, $object->ID );
					echo '>' . vtm_formatOutput($object->NAME) . '</option>';
				}
				echo '</select>';
			}
			echo "</div>";
		}
	}
       
    function prepare_items() {
		global $wpdb;
        
        $columns  = $this->get_columns();
        $hidden   = array();
        $sortable = $this->get_sortable_columns();
		
		// Assign to Owner
		$sql = "SELECT ID, NAME FROM " . VTM_TABLE_PREFIX . "MAPOWNER ORDER BY NAME";
		$this->ownerlist = $wpdb->get_results($sql);
		$this->ownerselect = isset($_REQUEST['ownerselect']) ? sanitize_key($_REQUEST['ownerselect']) : '';
		        			
		$this->_column_headers = array($columns, $hidden, $sortable);
		
        $this->process_bulk_action();
        		
		$sql  = "SELECT 
					domains.ID, domains.NAME, owners.NAME as OWNER, domains.DESCRIPTION, domains.VISIBLE
				FROM 
					" . VTM_TABLE_PREFIX . "MAPOWNER owners,
					" . VTM_TABLE_PREFIX . "MAPDOMAIN domains
				WHERE
					domains.OWNER_ID = owners.ID";
		if (!empty($_REQUEST['orderby']) && !empty($_REQUEST['order']) )
			$sql .= " ORDER BY {$_REQUEST['orderby']} {$_REQUEST['order']}";
		//echo "<p>SQL: $sql</p>";
		$data = $wpdb->get_results($sql);

		$this->items = $data;

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