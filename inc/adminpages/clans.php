<?php


function vtm_render_clan_page(){

    $testListTable["clans"] = new vtmclass_admin_clans_table();
	$doaction = vtm_clan_input_validation();
	
	if ($doaction == "add-clan") {
		$testListTable["clans"]->add_clan($_REQUEST['clan_name'], $_REQUEST['clan_description'], 
			$_REQUEST['clan_iconlink'], $_REQUEST['clan_clanpage'], $_REQUEST['clan_flaw'], 
			$_REQUEST['clan_visible'], $_REQUEST['clan_costmodel'],
			$_REQUEST['clan_costmodel_nonclan'], array($_REQUEST['clan_clan_disc1'], 
			$_REQUEST['clan_clan_disc2'], $_REQUEST['clan_clan_disc3'], $_REQUEST['clan_clan_disc4']),
			$_REQUEST['clan_role']);
									
	}
	if ($doaction == "save-clan") {
		$testListTable["clans"]->edit_clan($_REQUEST['clan_id'], $_REQUEST['clan_name'], $_REQUEST['clan_description'], $_REQUEST['clan_iconlink'], 
			$_REQUEST['clan_clanpage'], $_REQUEST['clan_flaw'], $_REQUEST['clan_visible'], $_REQUEST['clan_costmodel'],
			$_REQUEST['clan_costmodel_nonclan'], array($_REQUEST['clan_clan_disc1'], $_REQUEST['clan_clan_disc2'], $_REQUEST['clan_clan_disc3'], 
			$_REQUEST['clan_clan_disc4']), $_REQUEST['clan_role']);
									
	}

	vtm_render_clan_add_form($doaction);
	$testListTable["clans"]->prepare_items();
	$current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
	$current_url = remove_query_arg( 'action', $current_url );
	?>	

	<form id="clans-filter" method="get" action='<?php print htmlentities($current_url); ?>'>
		<input type="hidden" name="page" value="<?php print $_REQUEST['page'] ?>" />
		<input type="hidden" name="tab" value="clans" />
 		<?php $testListTable["clans"]->display() ?>
	</form>

    <?php
}

function vtm_render_clan_add_form($addaction) {

	global $wpdb;
	
	$type = "clan";
	
	//echo "<p>Creating clan form based on action $addaction</p>";

	if ('fix-' . $type == $addaction) {
		$id = isset($_REQUEST['clan']) ? $_REQUEST['clan'] : '';
		$name = $_REQUEST[$type . '_name'];

		$description = $_REQUEST[$type . '_description'];
		$iconlink = $_REQUEST[$type . '_iconlink'];
		$clanpage = $_REQUEST[$type . '_clanpage'];
		$clanflaw = $_REQUEST[$type . '_flaw'];
		$visible = $_REQUEST[$type . '_visible'];
		$costmodel_id = $_REQUEST[$type . '_costmodel'];
		$nonclan_costmodel_id = $_REQUEST[$type . '_costmodel_nonclan'];
		$role = $_REQUEST[$type . '_role'];

		$clan_discipline1_id = isset($_REQUEST[$type . '_clan_disc1']) ? $_REQUEST[$type . '_clan_disc1'] : 0;
		$clan_discipline2_id = isset($_REQUEST[$type . '_clan_disc2']) ? $_REQUEST[$type . '_clan_disc2'] : 0;
		$clan_discipline3_id = isset($_REQUEST[$type . '_clan_disc3']) ? $_REQUEST[$type . '_clan_disc3'] : 0;
		$clan_discipline4_id = isset($_REQUEST[$type . '_clan_disc4']) ? $_REQUEST[$type . '_clan_disc4'] : 0;
		
		$nextaction = $_REQUEST['action'];
		
	} else if ('edit-' . $type == $addaction) {
		/* Get values from database */
		$id   = $_REQUEST['clan'];
		
		$sql = "select *
				from " . VTM_TABLE_PREFIX . "CLAN clan
				where clan.ID = %d;";
		
		//echo "<p>$sql</p>"; 
		
		$data =$wpdb->get_results($wpdb->prepare($sql, $id));
		
		//print_r($data); 
		
		$name = $data[0]->NAME;
		$description = $data[0]->DESCRIPTION;
		$iconlink = $data[0]->ICON_LINK;
		$clanpage = $data[0]->CLAN_PAGE_LINK;
		$clanflaw = $data[0]->CLAN_FLAW;
		$visible = $data[0]->VISIBLE;
		$costmodel_id = $data[0]->CLAN_COST_MODEL_ID;
		$nonclan_costmodel_id = $data[0]->NONCLAN_COST_MODEL_ID;
		$role = $data[0]->WORDPRESS_ROLE;
		
		$sql = "select disciplines.ID, disciplines.NAME
				from " . VTM_TABLE_PREFIX . "CLAN_DISCIPLINE clandisc,
					" . VTM_TABLE_PREFIX . "DISCIPLINE disciplines
				where 
					disciplines.ID = clandisc.DISCIPLINE_ID
					AND clandisc.CLAN_ID = %d;";
		$data =$wpdb->get_results($wpdb->prepare($sql, $id));
		
		//print_r($data);
		
		$clan_discipline1_id = isset($data[0]->ID) ? $data[0]->ID : 0;
		$clan_discipline2_id = isset($data[1]->ID) ? $data[1]->ID : 0;
		$clan_discipline3_id = isset($data[2]->ID) ? $data[2]->ID : 0;	
		$clan_discipline4_id = isset($data[3]->ID) ? $data[3]->ID : 0;	
		
		$nextaction = "save";
		
	} else {
	
		/* defaults */
		$id = "";
		$name = "";
		$description = "";
		$iconlink = "";
		$clanpage = "";
		$clanflaw = "";
		$visible  = "Y";
		$costmodel_id = "";
		$nonclan_costmodel_id = "";
		$role = 'subscriber';
		
		$clan_discipline1_id = 0;
		$clan_discipline2_id = 0;
		$clan_discipline3_id = 0;	
		$clan_discipline4_id = 0;	
		
		$nextaction = "add";
	}
	$current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
	$current_url = remove_query_arg( 'action', $current_url );

	$disciplines = vtm_get_disciplines();
	?>
	<form id="new-<?php print $type; ?>" method="post" action='<?php print htmlentities($current_url); ?>'>
		<input type="hidden" name="<?php print $type; ?>_id" value="<?php print $id; ?>"/>
		<input type="hidden" name="tab" value="clans" />
		<input type="hidden" name="action" value="<?php print $nextaction; ?>" />
		<table>
		<tr>
			<td>Clan Name:  </td>
			<td><input type="text" name="<?php print $type; ?>_name" value="<?php print vtm_formatOutput($name); ?>" size=30 /></td>
			<td>Visible to Players: </td>
			<td>
				<select name="<?php print $type; ?>_visible">
					<option value="N" <?php selected($visible, "N"); ?>>No</option>
					<option value="Y" <?php selected($visible, "Y"); ?>>Yes</option>
				</select>
			</td>
			<td>Cost Model for Clan Disciplines: </td>
			<td>
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
			<td>Link to clan icon:  </td>
			<td><input type="text" name="<?php print $type; ?>_iconlink" value="<?php print $iconlink; ?>" size=30 /></td>
			<td>Wordpress Role: </td>
			<td>
				<select name="<?php print $type; ?>_role">
					<?php wp_dropdown_roles($role); ?>
				</select>
			</td>
			<td>Cost Model for Non-Clan Disciplines: </td>
			<td>
				<select name="<?php print $type; ?>_costmodel_nonclan">
					<?php
						foreach (vtm_get_costmodels() as $costmodel) {
							print "<option value='{$costmodel->ID}' ";
							selected($costmodel->ID, $nonclan_costmodel_id);
							echo ">" . vtm_formatOutput($costmodel->NAME) . "</option>";
						}
					?>
				</select>
			</td>
		</tr>
		<tr>
			<td>Link to clan webpage:  </td>
			<td colspan=3><input type="text" name="<?php print $type; ?>_clanpage" value="<?php print $clanpage; ?>" size=60 /></td>
			<td>Clan Discipline 1: </td>
			<td>
				<?php if (count($disciplines) > 0) { ?>
				<select name="<?php print $type; ?>_clan_disc1">
					<option value='0'>[Select]</option>
					<?php
						foreach ($disciplines as $discipline) {
							print "<option value='{$discipline->ID}' ";
							selected($discipline->ID, $clan_discipline1_id);
							echo ">" . vtm_formatOutput($discipline->NAME) . "</option>";
						}
					?>
				</select>
				<?php } else {
					echo "Please add disciplines to the database";
				} ?>
			</td>
		</tr>
		<tr>
			<td>Clan Flaw:  </td>
			<td colspan=3><input type="text" name="<?php print $type; ?>_flaw" value="<?php print vtm_formatOutput($clanflaw) ?>" size=60 /></td>
			<td>Clan Discipline 2: </td>
			<td>
				<?php if (count($disciplines) > 0) { ?>
				<select name="<?php print $type; ?>_clan_disc2">
					<option value='0'>[Select]</option>
					<?php
						foreach ($disciplines as $discipline) {
							print "<option value='{$discipline->ID}' ";
							selected($discipline->ID, $clan_discipline2_id);
							echo ">" . vtm_formatOutput($discipline->NAME) . "</option>";
						}
					?>
				</select>
				<?php } else {
					echo "Please add disciplines to the database";
				} ?>
			</td>
		</tr>
		<tr>
			<td>Description:  </td>
			<td colspan=3><input type="text" name="<?php print $type; ?>_description" value="<?php print vtm_formatOutput($description); ?>" size=60 /></td>
			<td>Clan Discipline 3: </td>
			<td>
				<?php if (count($disciplines) > 0) { ?>
				<select name="<?php print $type; ?>_clan_disc3">
					<option value='0'>[Select]</option>
					<?php
						foreach ($disciplines as $discipline) {
							print "<option value='{$discipline->ID}' ";
							selected($discipline->ID, $clan_discipline3_id);
							echo ">" . vtm_formatOutput($discipline->NAME) . "</option>";
						}
					?>
				</select>
				<?php } else {
					echo "Please add disciplines to the database";
				} ?>
			</td>
		</tr>
		<tr>
			<td colspan=4>&nbsp;</td>
			<td>Clan Discipline 4: </td>
			<td>
				<?php if (count($disciplines) > 0) { ?>
				<select name="<?php print $type; ?>_clan_disc4">
					<option value='0'>[Optional]</option>
					<?php
						foreach ($disciplines as $discipline) {
							print "<option value='{$discipline->ID}' ";
							selected($discipline->ID, $clan_discipline4_id);
							echo ">" . vtm_formatOutput($discipline->NAME) . "</option>";
						}
					?>
				</select>
				<?php } else {
					echo "Please add disciplines to the database";
				} ?>
			</td>
		</tr>
		</table>
		<input type="submit" name="do_add_<?php print $type; ?>" class="button-primary" value="Save <?php print ucfirst($type); ?>" />
	</form>
	
	<?php
}

function vtm_clan_input_validation() {

	$type = "clan";
	$doaction = '';

	if (!empty($_REQUEST['action']) && $_REQUEST['action'] == 'edit' && $_REQUEST['tab'] == 'clans')
		$doaction = "edit-$type"; 
		
	//echo "<p>Requested action: " . $_REQUEST['action'] . ", " . $type . "_name: " . $_REQUEST[$type . '_name'];
	
	//print_r($_REQUEST);
	
	if (!empty($_REQUEST[$type . '_name'])){
			
		$doaction = $_REQUEST['action'] . "-" . $type;
		
		/* Input Validation */
		if (empty($_REQUEST[$type . '_description']) || $_REQUEST[$type . '_description'] == "") {
			$doaction = "fix-$type";
			echo "<p style='color:red'>ERROR: Description is missing</p>";
		} 
		//if (empty($_REQUEST[$type . '_iconlink']) || $_REQUEST[$type . '_iconlink'] == "") {
		//	$doaction = "fix-$type";
		//	echo "<p style='color:red'>ERROR: Icon Link is missing</p>";
		//} 
		if (empty($_REQUEST[$type . '_flaw']) || $_REQUEST[$type . '_flaw'] == "") {
			$doaction = "fix-$type";
			echo "<p style='color:red'>ERROR: Clan Flaw is missing</p>";
		} 
		if (!isset($_REQUEST[$type . '_clan_disc1'])) {
			$doaction = "fix-$type";
			echo "<p style='color:red'>ERROR: Please add disciplines to the database before saving a clan</p>";
		} else {
			if (
				($_REQUEST[$type . '_clan_disc1'] == $_REQUEST[$type . '_clan_disc2'] && $_REQUEST[$type . '_clan_disc1'] != 0) ||
				($_REQUEST[$type . '_clan_disc1'] == $_REQUEST[$type . '_clan_disc3'] && $_REQUEST[$type . '_clan_disc1'] != 0) ||
				($_REQUEST[$type . '_clan_disc2'] == $_REQUEST[$type . '_clan_disc3'] && $_REQUEST[$type . '_clan_disc2'] != 0)
			) {
				$doaction = "fix-$type";
				echo "<p style='color:red'>ERROR: Cannot choose the same clan discipline more than once</p>";
			}
			
		}
				
	}
	
	//echo " action: $doaction</p>";

	return $doaction;
}


/* 
-----------------------------------------------
CLANS
------------------------------------------------ */
class vtmclass_admin_clans_table extends vtmclass_MultiPage_ListTable {
   
    function __construct(){
        global $status, $page;
                
        parent::__construct( array(
            'singular'  => 'clan',     
            'plural'    => 'clans',    
            'ajax'      => false        
        ) );
    }
	
	function delete_clan($selectedID) {
		global $wpdb;
		
		/* Check if clan id in use in a character */
		$sql = "select characters.NAME 
			from " . VTM_TABLE_PREFIX . "CHARACTER characters
			where characters.PUBLIC_CLAN_ID = %d or characters.PRIVATE_CLAN_ID = %d;";
		$isused = $wpdb->get_results($wpdb->prepare($sql, $selectedID, $selectedID));
		
		if ($isused) {
			echo "<p style='color:red'>Cannot delete as this {$this->type} is being used in the following characters:";
			echo "<ul>";
			foreach ($isused as $character)
				echo "<li style='color:red'>" . vtm_formatOutput($character->NAME) . "</li>";
			echo "</ul></p>";
		} else {
		
			/* delete clan disciplines */
			$sql = "delete from " . VTM_TABLE_PREFIX . "CLAN_DISCIPLINE 
					where CLAN_ID = %d;";
			$result = $wpdb->get_results($wpdb->prepare($sql, $selectedID));
		
			$sql = "delete from " . VTM_TABLE_PREFIX . "CLAN where ID = %d;";
			$result = $wpdb->get_results($wpdb->prepare($sql, $selectedID));
		
			/* print_r($result); */
			echo "<p style='color:green'>Deleted item $selectedID</p>";
		}
	}
	
 	function add_clan($name, $description, $iconlink, $clanpage,
						$clanflaw, $visible, $costmodel, $nonclanmodel, 
						$discids, $role) {
		global $wpdb;
		
		$wpdb->show_errors();
		
		/* add clan */
		$dataarray = array(
						'NAME' => $name,
						'DESCRIPTION' => $description,
						'ICON_LINK' => $iconlink,
						'CLAN_PAGE_LINK' => $clanpage,
						'CLAN_FLAW' => $clanflaw,
						'VISIBLE' => $visible,
						'CLAN_COST_MODEL_ID' => $costmodel,
						'NONCLAN_COST_MODEL_ID' => $nonclanmodel,
						'WORDPRESS_ROLE' => $role
					);
		
		/* print_r($dataarray); */
		
		$wpdb->insert(VTM_TABLE_PREFIX . "CLAN",
					$dataarray,
					array (
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
						'%d',
						'%d',
						'%s'
					)
				);
				
		$clanid = $wpdb->insert_id;
		
		if ($clanid == 0) {
			echo "<p style='color:red'><b>Error:</b> " . vtm_formatOutput($name) . " could not be inserted (";
			$wpdb->print_error();
			echo ")</p>";
		} else {
			
		
			/* add clan disciplines */
			foreach ($discids as $disc) {
				if ($disc > 0) {
					$dataarray = array(
							'CLAN_ID'       => $clanid,
							'DISCIPLINE_ID' => $disc
					);
					
					$wpdb->insert(VTM_TABLE_PREFIX . "CLAN_DISCIPLINE",
						$dataarray,
						array ( '%d', '%d')
					);
					
					if ($wpdb->insert_id == 0) {
						echo "<p style='color:green'>Failed to add clan discipline " . vtm_formatOutput($disc) . " for clan $name</p>";
					}
				}
			}
			
			echo "<p style='color:green'>Added clan '" . vtm_formatOutput($name) . "' (ID: {$clanid})</p>";
		}
	}
 	function edit_clan($clanid, $name, $description, $iconlink, $clanpage,
						$clanflaw, $visible, $costmodel, $nonclanmodel, 
						$discids, $role) {
		global $wpdb;
		
		$wpdb->show_errors();
		
		$dataarray = array(
						'NAME' => $name,
						'DESCRIPTION' => $description,
						'ICON_LINK' => $iconlink,
						'CLAN_PAGE_LINK' => $clanpage,
						'CLAN_FLAW' => $clanflaw,
						'VISIBLE' => $visible,
						'CLAN_COST_MODEL_ID' => $costmodel,
						'NONCLAN_COST_MODEL_ID' => $nonclanmodel,
						'WORDPRESS_ROLE' => $role
					);
		
		/* print_r($dataarray); */
		
		$result = $wpdb->update(VTM_TABLE_PREFIX . "CLAN",
					$dataarray,
					array (
						'ID' => $clanid
					)
				);
		$ok = 0;
		if ($result) {
			$ok = 1;
			echo "<p style='color:green'>Updated " . vtm_formatOutput($name) . "</p>";
		} else if ($result === 0) {
			$ok = 1;
		}
		
		if ($ok) {
			/* clan disciplines - remove old then re-add */
			$sql = "delete from " . VTM_TABLE_PREFIX . "CLAN_DISCIPLINE
					where CLAN_ID = %d";
			$result = $wpdb->get_results($wpdb->prepare($sql, $clanid));
			
			foreach ($discids as $disc) {
				if ($disc > 0) {
					$dataarray = array(
								'CLAN_ID' => $clanid,
								'DISCIPLINE_ID' => $disc
					);
					$wpdb->insert(VTM_TABLE_PREFIX . "CLAN_DISCIPLINE",
						$dataarray,
						array ( '%d', '%d')
					);
					
					if ($wpdb->insert_id == 0) {
						$ok = 0;
						echo "<p style='color:green'>Failed to update clan discipline " . vtm_formatOutput($disc) . " for clan " . vtm_formatOutput($name) . "</p>";
					}
				}
			}
				
		}
		else {
			echo "<p style='color:red'>Could not update $name ($clanid)</p>";
		}
	}
   
    function column_default($item, $column_name){
        switch($column_name){
            case 'DESCRIPTION':
                return vtm_formatOutput($item->$column_name);
            case 'CLAN_PAGE_LINK':
                return $item->$column_name;
             case 'ICON_LINK':
                return $item->$column_name;
           case 'CLAN_FLAW':
                return vtm_formatOutput($item->$column_name);
           case 'COST_MODEL':
                return vtm_formatOutput($item->$column_name);
           case 'NONCLAN_MODEL':
                return vtm_formatOutput($item->$column_name);
           case 'WORDPRESS_ROLE':
                return vtm_formatOutput($item->$column_name);
            default:
                return print_r($item,true); 
        }
    }
 
    function column_name($item){
	
		$act = ($item->VISIBLE === 'Y') ? 'hide' : 'show';
        
        $actions = array(
            'edit'      => sprintf('<a href="?page=%s&amp;action=%s&amp;clan=%s&amp;tab=%s">Edit</a>',$_REQUEST['page'],'edit',$item->ID, $this->type),
            'delete'    => sprintf('<a href="?page=%s&amp;action=%s&amp;clan=%s&amp;tab=%s">Delete</a>',$_REQUEST['page'],'delete',$item->ID, $this->type),
        );
        
        
        return sprintf('%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
            vtm_formatOutput($item->NAME),
            $item->ID,
            $this->row_actions($actions)
        );
    }
      

    function get_columns(){
        $columns = array(
            'cb'           => '<input type="checkbox" />', 
            'NAME'         => 'Name',
            'DESCRIPTION'  => 'Description',
            'ICON_LINK'    => 'Link to clan icon',
            'CLAN_PAGE_LINK' => 'Link to clan page',
            'CLAN_FLAW'    => 'Clan Flaw',
            'VISIBLE'      => 'Visible to Players',
            'COST_MODEL'   => 'Clan Cost Model',
            'NONCLAN_MODEL'  => 'Non-Clan Cost Model',
            'WORDPRESS_ROLE' => 'Wordpress Role Mapping',
        );
        return $columns;
		
    }
    
    function get_sortable_columns() {
        $sortable_columns = array(
            'NAME'        => array('NAME',true),
            'VISIBLE'    => array('GROUPING',false),
        );
        return $sortable_columns;
    }
	
    
    function get_bulk_actions() {
        $actions = array(
            'delete'    => 'Delete',
       );
        return $actions;
    }
    
    function process_bulk_action() {
        
		/* echo "<p>Bulk action " . $this->current_action() . ", currently on tab {$_REQUEST['tab']} and will do action if {$this->type}.</p>"; */
		
        if( 'delete'===$this->current_action() && $_REQUEST['tab'] == $this->type && isset($_REQUEST['clan'])) {
			if ('string' == gettype($_REQUEST['clan'])) {
				$this->delete_clan($_REQUEST['clan']);
			} else {
				foreach ($_REQUEST['clan'] as $clan) {
					$this->delete_clan($clan);
				}
			}
        }
		
    } 
    
    function prepare_items() {
        global $wpdb; 

        /* $per_page = 20; */
        
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        			
		$this->_column_headers = array($columns, $hidden, $sortable);
        
		$this->type = "clans";
        
        $this->process_bulk_action();
		
		/* Get the data from the database */
		$sql = "select clan.ID, clan.NAME, clan.DESCRIPTION, clan.ICON_LINK, clan.CLAN_PAGE_LINK, 
					clan.CLAN_FLAW, clan.VISIBLE, clan.WORDPRESS_ROLE,
					clancosts.NAME as COST_MODEL, nonclancosts.NAME as NONCLAN_MODEL
				from " . VTM_TABLE_PREFIX. "CLAN clan,
					" . VTM_TABLE_PREFIX. "COST_MODEL clancosts,
					" . VTM_TABLE_PREFIX. "COST_MODEL nonclancosts
				where
					clancosts.ID = clan.CLAN_COST_MODEL_ID
					AND nonclancosts.ID = clan.NONCLAN_COST_MODEL_ID";
		
		if (!empty($_REQUEST['orderby']) && !empty($_REQUEST['order']) && $this->type == $_REQUEST['tab'])
			$sql .= " ORDER BY clan.{$_REQUEST['orderby']} {$_REQUEST['order']}";
		
		$sql .= ";";
		/* echo "<p>SQL: " . $sql . "</p>"; */
		
		$data =$wpdb->get_results($sql);
        
        $current_page = $this->get_pagenum();
        $total_items = count($data);
                
        $this->items = $data;
        
        /* $this->set_pagination_args( array(
            'total_items' => $total_items,                  
            'per_page'    => $per_page,                  
            'total_pages' => ceil($total_items/$per_page)
        ) ); */
        $this->set_pagination_args( array(
            'total_items' => $total_items,                  
            'per_page'    => $total_items,                  
            'total_pages' => 1
        ) );
    }

}

/* ----------------------------
	DISCIPLINES
------------------------------- */

function vtm_render_discipline_page(){

    $testListTable["disc"] = new vtmclass_admin_disciplines_table();
	$doaction = vtm_discipline_input_validation();
	
	if ($doaction == "add-disc") {
		$testListTable["disc"]->add_discipline();							
	}
	if ($doaction == "save-disc") {
		$testListTable["disc"]->edit_discipline();						
	}

	vtm_render_discipline_add_form($doaction);
	$testListTable["disc"]->prepare_items();
	$current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
	$current_url = remove_query_arg( 'action', $current_url );
	?>	

	<form id="disc-filter" method="get" action='<?php print htmlentities($current_url); ?>'>
		<input type="hidden" name="page" value="<?php print $_REQUEST['page'] ?>" />
		<input type="hidden" name="tab" value="disc" />
 		<?php $testListTable["disc"]->display() ?>
	</form>

    <?php
}

function vtm_render_discipline_add_form($addaction) {

	global $wpdb;
	
	$type = "disc";
	
	/* echo "<p>Creating form based on action $addaction</p>"; */

	if ('fix-' . $type == $addaction) {
		$id = isset($_REQUEST['discipline']) ? $_REQUEST['discipline'] : "";
		$name = $_REQUEST[$type . '_name'];

		$description = $_REQUEST[$type . '_description'];
		$visible = $_REQUEST[$type . '_visible'];
		$sourcebook_id = isset($_REQUEST[$type . '_sourcebook_id']) ? $_REQUEST[$type . '_sourcebook_id'] : "";
		$pagenum = $_REQUEST[$type . '_pagenum'];
		
		$nextaction = $_REQUEST['action'];
		
	} 
	elseif ('edit-' . $type == $addaction) {
		/* Get values from database */
		$id   = $_REQUEST['discipline'];
		
		$sql = "select *
				from " . VTM_TABLE_PREFIX . "DISCIPLINE disciplines
				where disciplines.ID = %d;";
		
		/* echo "<p>$sql</p>"; */
		
		$data =$wpdb->get_results($wpdb->prepare($sql, $id));
		
		/* print_r($data); */
		
		$name = $data[0]->NAME;
		$description = $data[0]->DESCRIPTION;
		$visible = $data[0]->VISIBLE;
		$sourcebook_id = $data[0]->SOURCE_BOOK_ID;
		$pagenum = $data[0]->PAGE_NUMBER;
		
		$nextaction = "save";
		
	} else {
	
		/* defaults */
		$name = "";
		$description = "";
		$visible  = "Y";
		$sourcebook_id = "";
		$pagenum = "";
		
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
			<td>Name:  </td>
			<td><input type="text" name="<?php print $type; ?>_name" value="<?php print vtm_formatOutput($name); ?>" size=30 /></td>
			<td>Sourcebook: </td>
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
			<td>Page Number: </td>
			<td><input type="number" name="<?php print $type; ?>_pagenum" value="<?php print $pagenum; ?>" size=4 /></td>
		</tr>
		<tr>
			<td>Description:  </td>
			<td colspan=3><input type="text" name="<?php print $type; ?>_description" value="<?php print vtm_formatOutput($description); ?>" size=60 /></td>
			<td>Visible to Players: </td>
			<td>
				<select name="<?php print $type; ?>_visible">
					<option value="N" <?php selected($visible, "N"); ?>>No</option>
					<option value="Y" <?php selected($visible, "Y"); ?>>Yes</option>
				</select>
			</td>
		</tr>
		</table>
		<input type="submit" name="do_add_<?php print $type; ?>" class="button-primary" value="Save" />
	</form>
	
	<?php
}

function vtm_discipline_input_validation() {

	$type = "disc";
	$doaction = '';

	if (!empty($_REQUEST['action']) && $_REQUEST['action'] == 'edit' && $_REQUEST['tab'] == $type)
		$doaction = "edit-$type"; 
		
	/* echo "<p>Requested action: " . $_REQUEST['action'] . ", " . $type . "_name: " . $_REQUEST[$type . '_name'];  */
	
	if (!empty($_REQUEST[$type . '_name'])){
			
		$doaction = $_REQUEST['action'] . "-" . $type;
		
		/* Input Validation */
		if (empty($_REQUEST[$type . '_description']) || $_REQUEST[$type . '_description'] == "") {
			$doaction = "fix-$type";
			echo "<p style='color:red'>ERROR: Description is missing</p>";
		} 
		if (empty($_REQUEST[$type . '_pagenum']) || $_REQUEST[$type . '_pagenum'] == "") {
			$doaction = "fix-$type";
			echo "<p style='color:red'>ERROR: Page Number is missing</p>";
		} 
				
	}
	
	/* echo " action: $doaction</p>"; */

	return $doaction;
}


/* 
-----------------------------------------------
CLANS
------------------------------------------------ */
class vtmclass_admin_disciplines_table extends vtmclass_MultiPage_ListTable {
   
    function __construct(){
        global $status, $page;
                
        parent::__construct( array(
            'singular'  => 'discipline',     
            'plural'    => 'disciplines',    
            'ajax'      => false        
        ) );
    }
	
	function delete_discipline($selectedID) {
		global $wpdb;
		
		/* Check if discipline id in use in a character */
		$sql = "select characters.NAME 
				from 
					" . VTM_TABLE_PREFIX . "CHARACTER characters,
					" . VTM_TABLE_PREFIX . "CHARACTER_DISCIPLINE chardisc
				where 
					characters.ID = chardisc.CHARACTER_ID
					AND chardisc.DISCIPLINE_ID = %d;";
		$isused = $wpdb->get_results($wpdb->prepare($sql, $selectedID));
		
		if ($isused) {
			echo "<p style='color:red'>Cannot delete as this discipline is being used in the following characters:";
			echo "<ul>";
			foreach ($isused as $character)
				echo "<li style='color:red'>" . vtm_formatOutput($character->NAME) . "</li>";
			echo "</ul></p>";
		} else {
		
			/* Check if discipline id in use in a clan discipline */
			$sql = "select clans.NAME 
				from " . VTM_TABLE_PREFIX . "CLAN_DISCIPLINE clandisc, 
					 " . VTM_TABLE_PREFIX . "CLAN clans
				where 
					clans.ID = clandisc.CLAN_ID
					and clandisc.DISCIPLINE_ID = %d;";
			$isused = $wpdb->get_results($wpdb->prepare($sql, $selectedID));

			if ($isused) {
				echo "<p style='color:red'>Cannot delete as this is a clan discipline for the following clans:";
				echo "<ul>";
				foreach ($isused as $clan)
					echo "<li style='color:red'>" . vtm_formatOutput($clan->NAME) . "</li>";
				echo "</ul></p>";
			
			} else {
				$sql = "delete from " . VTM_TABLE_PREFIX . "DISCIPLINE where ID = %d;";
				
				$result = $wpdb->get_results($wpdb->prepare($sql, $selectedID));
			
				/* print_r($result); */
				echo "<p style='color:green'>Deleted item $selectedID</p>";
			}
		}
	}
	
 	function add_discipline() {
		global $wpdb;
		
		$wpdb->show_errors();
		
		$dataarray = array(
						'NAME' => $_REQUEST['disc_name'],
						'DESCRIPTION' => $_REQUEST['disc_description'],
						'VISIBLE' => $_REQUEST['disc_visible'],
						'SOURCE_BOOK_ID' => $_REQUEST['disc_sourcebook'],
						'PAGE_NUMBER' => $_REQUEST['disc_pagenum']
					);
		
		/* print_r($dataarray); */
		
		$wpdb->insert(VTM_TABLE_PREFIX . "DISCIPLINE",
					$dataarray,
					array (
						'%s',
						'%s',
						'%s',
						'%d',
						'%d'
					)
				);
		
		if ($wpdb->insert_id == 0) {
			echo "<p style='color:red'><b>Error:</b> " . vtm_formatOutput($_REQUEST['disc_name']) . " could not be inserted (";
			$wpdb->print_error();
			echo ")</p>";
		} else {
			echo "<p style='color:green'>Added discipline '" . vtm_formatOutput($_REQUEST['disc_name']) . "' (ID: {$wpdb->insert_id})</p>";
		}
	}
 	function edit_discipline() {
		global $wpdb;
		
		$wpdb->show_errors();
		
		$dataarray = array(
						'NAME' => $_REQUEST['disc_name'],
						'DESCRIPTION' => $_REQUEST['disc_description'],
						'VISIBLE' => $_REQUEST['disc_visible'],
						'SOURCE_BOOK_ID' => $_REQUEST['disc_sourcebook'],
						'PAGE_NUMBER' => $_REQUEST['disc_pagenum']
					);
		
		/* print_r($dataarray); */
		
		$result = $wpdb->update(VTM_TABLE_PREFIX . "DISCIPLINE",
					$dataarray,
					array (
						'ID' => $_REQUEST['disc_id']
					)
				);
		
		if ($result) 
			echo "<p style='color:green'>Updated " . vtm_formatOutput($_REQUEST['disc_name']) . "</p>";
		else if ($result === 0) 
			echo "<p style='color:orange'>No updates made to " . vtm_formatOutput($_REQUEST['disc_name']) . "</p>";
		else {
			$wpdb->print_error();
			echo "<p style='color:red'>Could not update " . vtm_formatOutput($_REQUEST['disc_name']) . " ({$_REQUEST['disc_id']})</p>";
		}
	}
   
    function column_default($item, $column_name){
        switch($column_name){
            case 'DESCRIPTION':
                return vtm_formatOutput($item->$column_name);
            case 'PAGE_NUMBER':
                return $item->$column_name;
             case 'SOURCE_BOOK':
                return vtm_formatOutput($item->$column_name);
            default:
                return print_r($item,true); 
        }
    }
 
    function column_name($item){
	        
        $actions = array(
            'edit'      => sprintf('<a href="?page=%s&amp;action=%s&amp;discipline=%s&amp;tab=%s">Edit</a>',$_REQUEST['page'],'edit',$item->ID, $this->type),
            'delete'    => sprintf('<a href="?page=%s&amp;action=%s&amp;discipline=%s&amp;tab=%s">Delete</a>',$_REQUEST['page'],'delete',$item->ID, $this->type),
        );
        
        
        return sprintf('%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
            vtm_formatOutput($item->NAME),
            $item->ID,
            $this->row_actions($actions)
        );
    }
      

    function get_columns(){
        $columns = array(
            'cb'           => '<input type="checkbox" />', 
            'NAME'         => 'Name',
            'DESCRIPTION'  => 'Description',
            'SOURCE_BOOK'  => 'Source Book',
            'PAGE_NUMBER'  => 'Page Number',
            'VISIBLE'      => 'Visible to Players',
        );
        return $columns;
		
    }
    
    function get_sortable_columns() {
        $sortable_columns = array(
            'NAME'        => array('NAME',true),
            'VISIBLE'    => array('GROUPING',false),
        );
        return $sortable_columns;
    }
	
    
    function get_bulk_actions() {
        $actions = array(
            'delete'    => 'Delete',
       );
        return $actions;
    }
    
    function process_bulk_action() {
        
		/* echo "<p>Bulk action " . $this->current_action() . ", currently on tab {$_REQUEST['tab']} and will do action if {$this->type}.</p>"; */
		
        if( 'delete'===$this->current_action() && $_REQUEST['tab'] == $this->type && isset($_REQUEST['discipline'])) {
			if ('string' == gettype($_REQUEST['discipline'])) {
				$this->delete_discipline($_REQUEST['discipline']);
			} else {
				foreach ($_REQUEST['discipline'] as $disc) {
					$this->delete_discipline($disc);
				}
			}
        }
		
    } 
    
    function prepare_items() {
        global $wpdb; 

        /* $per_page = 20; */
        
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        			
		$this->_column_headers = array($columns, $hidden, $sortable);
        
		$this->type = "disc";
        
        $this->process_bulk_action();
		
		/* Get the data from the database */
		$sql = "select disciplines.ID, disciplines.NAME, disciplines.DESCRIPTION, 
					disciplines.VISIBLE, disciplines.PAGE_NUMBER, books.NAME as SOURCE_BOOK
				from 
					" . VTM_TABLE_PREFIX. "DISCIPLINE disciplines,
					" . VTM_TABLE_PREFIX. "SOURCE_BOOK books
				where
					books.ID = disciplines.SOURCE_BOOK_ID";
		
		if (!empty($_REQUEST['orderby']) && !empty($_REQUEST['order']) && $this->type == $_REQUEST['tab'])
			$sql .= " ORDER BY disciplines.{$_REQUEST['orderby']} {$_REQUEST['order']}";
		
		$sql .= ";";
		//echo "<p>SQL: " . $sql . "</p>";
		
		$data =$wpdb->get_results($sql);
        
        $current_page = $this->get_pagenum();
        $total_items = count($data);
                
        $this->items = $data;
        
        /* $this->set_pagination_args( array(
            'total_items' => $total_items,                  
            'per_page'    => $per_page,                  
            'total_pages' => ceil($total_items/$per_page)
        ) ); */
        $this->set_pagination_args( array(
            'total_items' => $total_items,                  
            'per_page'    => $total_items,                  
            'total_pages' => 1
        ) );
    }

}
?>