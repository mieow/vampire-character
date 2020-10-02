<?php


function vtm_render_meritflaw_page($type){

	$current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
    $testListTable[$type] = new vtmclass_admin_meritsflaws_table();
	$doaction = vtm_merit_input_validation($type);
	/* echo "<p>Merit action: $doaction</p>"; */
	
	if ($doaction == "add-$type") {
		$testListTable[$type]->add_merit($_REQUEST[$type . '_name'], $_REQUEST[$type . '_group'], $_REQUEST[$type . '_sourcebook'], 
									$_REQUEST[$type . '_page_number'], $_REQUEST[$type . '_cost'], $_REQUEST[$type . '_xp_cost'], 
									$_REQUEST[$type . '_multiple'], $_REQUEST[$type . '_visible'], $_REQUEST[$type . '_desc'],
									$_REQUEST[$type . '_question'], $_REQUEST[$type . '_has_specialisation'],
									$_REQUEST[$type . '_profile']);
	}
	if ($doaction == "save-$type") { 
		$testListTable[$type]->edit_merit($_REQUEST[$type . '_id'], $_REQUEST[$type . '_name'], $_REQUEST[$type . '_group'], 
									$_REQUEST[$type . '_sourcebook'], $_REQUEST[$type . '_page_number'], $_REQUEST[$type . '_cost'], 
									$_REQUEST[$type . '_xp_cost'], $_REQUEST[$type . '_multiple'], $_REQUEST[$type . '_visible'],
									$_REQUEST[$type . '_desc'], $_REQUEST[$type . '_question'], $_REQUEST[$type . '_has_specialisation'],
									$_REQUEST[$type . '_profile']);
	} 
	
	vtm_render_meritflaw_add_form($type, $doaction);
	
    $testListTable[$type]->prepare_items_bytype($type);
	$current_url = remove_query_arg( 'action', $current_url );

   ?>	

	<!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
	<form id="<?php print $type ?>-filter" method="get" action='<?php print htmlentities($current_url); ?>'>
		<input type="hidden" name="page" value="<?php print $_REQUEST['page'] ?>" />
		<input type="hidden" name="tab" value="<?php print $type ?>" />
 		<?php $testListTable[$type]->display() ?>
	</form>

    <?php
}

function vtm_render_rituals_page(){

    $testListTable["rituals"] = new vtmclass_admin_rituals_table();
	$doaction = vtm_ritual_input_validation();
	
	if ($doaction == "add-ritual") {
		$testListTable["rituals"]->add_ritual($_REQUEST['ritual_name'], $_REQUEST['ritual_desc'], 
			$_REQUEST['ritual_level'], $_REQUEST['ritual_disc'], $_REQUEST['ritual_dicepool'], 
			$_REQUEST['ritual_difficulty'], $_REQUEST['ritual_cost'], $_REQUEST['ritual_sourcebook'], 
			$_REQUEST['ritual_page_number'], $_REQUEST['ritual_visible']);
									
	}
	if ($doaction == "save-ritual") {
		$testListTable["rituals"]->edit_ritual($_REQUEST['ritual_id'], $_REQUEST['ritual_name'], $_REQUEST['ritual_desc'], 
			$_REQUEST['ritual_level'], $_REQUEST['ritual_disc'], $_REQUEST['ritual_dicepool'], 
			$_REQUEST['ritual_difficulty'], $_REQUEST['ritual_cost'], $_REQUEST['ritual_sourcebook'], 
			$_REQUEST['ritual_page_number'], $_REQUEST['ritual_visible']);
									
	}

	vtm_render_ritual_add_form($doaction);
	$testListTable["rituals"]->prepare_items();
	$current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
	$current_url = remove_query_arg( 'action', $current_url );
	?>	

	<form id="rituals-filter" method="get" action='<?php print htmlentities($current_url); ?>'>
		<input type="hidden" name="page" value="<?php print $_REQUEST['page'] ?>" />
		<input type="hidden" name="tab" value="ritual" />
 		<?php $testListTable["rituals"]->display() ?>
	</form>

    <?php
}
function vtm_render_sourcebook_page(){

    $testListTable["books"] = new vtmclass_admin_books_table();
	$doaction = vtm_book_input_validation();
	
	if ($doaction == "add-book") {
		$testListTable["books"]->add_book($_REQUEST['book_name'], $_REQUEST['book_code'], $_REQUEST['book_visible']);
									
	}
	if ($doaction == "save-book") {
		$testListTable["books"]->edit_book($_REQUEST['book_id'], $_REQUEST['book_name'], $_REQUEST['book_code'], $_REQUEST['book_visible']);
									
	}

	vtm_render_book_add_form($doaction);
	$testListTable["books"]->prepare_items();
	$current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
	$current_url = remove_query_arg( 'action', $current_url );
	?>	

	<form id="books-filter" method="get" action='<?php print htmlentities($current_url); ?>'>
		<input type="hidden" name="page" value="<?php print $_REQUEST['page'] ?>" />
		<input type="hidden" name="tab" value="book" />
 		<?php $testListTable["books"]->display() ?>
	</form>

    <?php
}

function vtm_render_meritflaw_add_form($type, $addaction) {

	global $wpdb;
	
	/* echo "<p>Creating $type form based on action $addaction</p>"; */

	if ('fix-' . $type == $addaction) {
		$id = isset($_REQUEST['merit']) ? $_REQUEST['merit'] : 0;
		$name = $_REQUEST[$type . '_name'];
		$group = $_REQUEST[$type . '_group'];
		$bookid = $_REQUEST[$type . '_sourcebook'];
		$pagenum = $_REQUEST[$type . '_page_number'];
		$cost = $_REQUEST[$type . '_cost'];
		$xpcost = $_REQUEST[$type . '_xp_cost'];
		$multiple = $_REQUEST[$type . '_multiple'];
		$visible = $_REQUEST[$type . '_visible'];
		$desc = $_REQUEST[$type . '_desc'];
		$question = $_REQUEST[$type . '_question'];
		$spec = $_REQUEST[$type . '_has_specialisation'];
		$profile = $_REQUEST[$type . '_profile'];
		
		$nextaction = $_REQUEST['action'];
		
	} else if ('edit-' . $type == $addaction) {
		/* Get values from database */
		$id   = $_REQUEST['merit'];
		
		$sql = "select merit.ID, merit.NAME as NAME, merit.DESCRIPTION as DESCRIPTION, merit.GROUPING as GROUPING,
						merit.COST as COST, merit.XP_COST as XP_COST, merit.MULTIPLE as MULTIPLE,
						books.ID as SOURCEBOOK, merit.PAGE_NUMBER as PAGE_NUMBER,
						merit.VISIBLE as VISIBLE, merit.BACKGROUND_QUESTION, merit.HAS_SPECIALISATION,
						merit.PROFILE_DISPLAY_ID
						from " . VTM_TABLE_PREFIX . "MERIT merit, " . VTM_TABLE_PREFIX . "SOURCE_BOOK books 
						where merit.ID = %d and books.ID = merit.SOURCE_BOOK_ID;";
		
		/* echo "<p>$sql</p>"; */
		
		$data =$wpdb->get_results($wpdb->prepare($sql, $id));
		
		/* print_r($data); */
		
		$name = $data[0]->NAME;
		$group = $data[0]->GROUPING;
		$bookid = $data[0]->SOURCEBOOK;
		$pagenum = $data[0]->PAGE_NUMBER;
		$cost = $data[0]->COST;
		$xpcost = $data[0]->XP_COST;
		$multiple = $data[0]->MULTIPLE;
		$visible = $data[0]->VISIBLE;
		$desc = $data[0]->DESCRIPTION;
		$question = $data[0]->BACKGROUND_QUESTION;
		$spec = $data[0]->HAS_SPECIALISATION;
		$profile = $data[0]->PROFILE_DISPLAY_ID;
		
		$nextaction = "save";
		
	} else {
	
		/* defaults */
		$id = 1;
		$name = "";
		$group = "";
		$bookid = 1;
		$pagenum = 1;
		$cost = 0;
		$xpcost = 0;
		$multiple = "N";
		$visible = "Y";
		$desc = "";
		$question = "";
		$spec = "N";
		$profile = 0;
		
		$nextaction = "add";
	}

	$booklist = vtm_get_booknames();
	$current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
	$current_url = remove_query_arg( 'action', $current_url );

	?>
	<form id="new-<?php print $type; ?>" method="post" action='<?php print htmlentities($current_url); ?>'>
		<input type="hidden" name="<?php print $type; ?>_id" value="<?php print $id; ?>"/>
		<input type="hidden" name="tab" value="<?php print $type; ?>" />
		<input type="hidden" name="action" value="<?php print $nextaction; ?>" />
		<table>
		<tr>
			<td><?php print ucfirst($type); ?> Name:  </td>
			<td><input type="text" name="<?php print $type; ?>_name" value="<?php print vtm_formatOutput($name); ?>" size=20 /></td> <!-- check sizes -->
			<td>Grouping:   </td>
			<td><input type="text" name="<?php print $type; ?>_group" value="<?php print vtm_formatOutput($group); ?>" size=20 /></td>
			<td>Visible to Players: </td>
			<td>
				<select name="<?php print $type; ?>_visible">
					<option value="N" <?php selected($visible, "N"); ?>>No</option>
					<option value="Y" <?php selected($visible, "Y"); ?>>Yes</option>
				</select>
			</td>
		</tr>
		<tr>
			<td>Sourcebook: </td>
			<td>
				<select name="<?php print $type; ?>_sourcebook">
					<?php
						foreach ($booklist as $book) {
							print "<option value='{$book->ID}' ";
							($book->ID == $bookid) ? print "selected" : print "";
							echo ">" . vtm_formatOutput($book->NAME) . "</option>";
						}
					?>
				</select>
			</td>
			<td>Page Number: </td>
			<td><input type="number" name="<?php print $type; ?>_page_number" value="<?php print $pagenum; ?>" /></td>
			<td>Multiple?: </td>
			<td>
				<select name="<?php print $type; ?>_multiple">
					<option value="N" <?php selected($multiple, "N"); ?>>No</option>
					<option value="Y" <?php selected($multiple, "Y"); ?>>Yes</option>
				</select>
			</td>
		</tr>
		<tr>
			<td>Freebie Point Cost: </td>
			<td><input type="number" name="<?php print $type; ?>_cost" value="<?php print $cost; ?>" /></td>
			<td>Experience Cost: </td>
			<td><input type="number" name="<?php print $type; ?>_xp_cost" value="<?php print $xpcost; ?>" /></td>
			<td>Has Specialisation?: </td><td>
				<select name="<?php print $type; ?>_has_specialisation">
					<option value="N" <?php selected($spec, "N"); ?>>No</option>
					<option value="Y" <?php selected($spec, "Y"); ?>>Yes</option>
				</select>
			</td>
		</tr>
		<tr>
			<td colspan=4></td>
			<td>Display in public profile: </td><td>
				<select name="<?php print $type; ?>_profile">
					<option value="0" <?php selected($profile, "0"); ?>>Not displayed</option>
					<?php
						foreach (vtm_get_profile_display() as $opt) {
							print "<option value='{$opt->ID}' ";
							($opt->ID == $profile) ? print "selected" : print "";
							echo ">" . vtm_formatOutput($opt->NAME) . "</option>";
						}
					?>
				</select>
			</td>
		</tr>
		<tr>
			<td>Description: </td>
			<td colspan=2><input type="text" name="<?php print $type; ?>_desc" value="<?php print vtm_formatOutput($desc); ?>" size=50 /></td>
			<td>Extended Background Question: </td>
			<td colspan=2><input type="text" name="<?php print $type; ?>_question" value="<?php print vtm_formatOutput($question); ?>" size=50 /></td>
		</tr>
		</table>
		<input type="submit" name="do_add_<?php print $type; ?>" class="button-primary" value="Save <?php print ucfirst($type); ?>" />
	</form>
	
	<?php
}
function vtm_render_ritual_add_form($addaction) {

	global $wpdb;
	
	$type = "ritual";
	
	/* echo "<p>Creating ritual form based on action $addaction</p>"; */

	if ('fix-' . $type == $addaction) {
		$id = isset($_REQUEST['ritual']) ? $_REQUEST['ritual'] : 0;
		$name = $_REQUEST[$type . '_name'];

		$bookid = $_REQUEST[$type . '_sourcebook'];
		$pagenum = $_REQUEST[$type . '_page_number'];
		$cost = $_REQUEST[$type . '_cost'];
		$visible = $_REQUEST[$type . '_visible'];
		$desc = $_REQUEST[$type . '_desc'];
	
		$level = $_REQUEST[$type . '_level'];
		$disciplineid = isset($_REQUEST[$type . '_disc']) ? $_REQUEST[$type . '_disc'] : 0;
		$dicepool = $_REQUEST[$type . '_dicepool'];
		$diff = $_REQUEST[$type . '_difficulty'];
		
		$nextaction = $_REQUEST['action'];
		
	} else if ('edit-' . $type == $addaction) {
		/* Get values from database */
		$id   = $_REQUEST['ritual'];
		
		$sql = "select ritual.ID, ritual.NAME, ritual.LEVEL, ritual.DISCIPLINE_ID as DISCIPLINE, ritual.DICE_POOL,
					ritual.COST, ritual.DIFFICULTY, ritual.SOURCE_BOOK_ID as SOURCEBOOK, ritual.PAGE_NUMBER, 
					ritual.VISIBLE, ritual.DESCRIPTION
				from " . VTM_TABLE_PREFIX . "RITUAL as ritual, 
					" . VTM_TABLE_PREFIX . "SOURCE_BOOK books,
					" . VTM_TABLE_PREFIX . "DISCIPLINE as discipline
				where ritual.DISCIPLINE_ID = discipline.ID and
					ritual.SOURCE_BOOK_ID = books.ID and
					ritual.ID = %d;";
		
		/* echo "<p>$sql</p>"; */
		
		$data =$wpdb->get_results($wpdb->prepare($sql, $id));
		
		/* print_r($data); */
		
		$name = $data[0]->NAME;
		$desc = $data[0]->DESCRIPTION;
		$level = $data[0]->LEVEL;
		$disciplineid = $data[0]->DISCIPLINE;
		$dicepool = $data[0]->DICE_POOL;
		$cost = $data[0]->COST;
		$diff = $data[0]->DIFFICULTY;
		$bookid = $data[0]->SOURCEBOOK;
		$pagenum = $data[0]->PAGE_NUMBER;
		$visible = $data[0]->VISIBLE;
		
		$nextaction = "save";
		
	} else {
	
		/* defaults */
		$name = "";
		$desc = "";
		$level = 1;
		$disciplineid = 1;
		$dicepool = "Intelligence + Occult";
		$cost = 1;
		$diff = 4;
		$bookid = 1;
		$pagenum = "";
		$visible = "Y";
		
		$nextaction = "add";
	}

	$booklist = vtm_get_booknames();
	$current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
	$current_url = remove_query_arg( 'action', $current_url );

	?>
	<form id="new-<?php print $type; ?>" method="post" action='<?php print htmlentities($current_url); ?>'>
		<input type="hidden" name="<?php print $type; ?>_id" value="<?php print $id; ?>"/>
		<input type="hidden" name="tab" value="<?php print $type; ?>" />
		<input type="hidden" name="action" value="<?php print $nextaction; ?>" />
		<table>
		<tr>
			<td><?php print ucfirst($type); ?> Name:  </td>
			<td colspan=3><input type="text" name="<?php print $type; ?>_name" value="<?php print vtm_formatOutput($name); ?>" size=60 /></td> <!-- check sizes -->
			<td>Visible to Players: </td>
			<td>
				<select name="<?php print $type; ?>_visible">
					<option value="N" <?php selected($visible, "N"); ?>>No</option>
					<option value="Y" <?php selected($visible, "Y"); ?>>Yes</option>
				</select></td>

		</tr>
		<tr>
			<td>Discipline:  </td>
			<td>
				<?php $disciplines = vtm_get_disciplines();
				if (count($disciplines) > 0) { ?>
				<select name="<?php print $type; ?>_disc">
					<?php
						foreach ($disciplines as $disc) {
							print "<option value='{$disc->ID}' ";
							($disc->ID == $disciplineid) ? print "selected" : print "";
							echo ">" . vtm_formatOutput($disc->NAME) . "</option>";
						}
					?>
				</select>
				<?php } else {
					echo "Please add disciplines to the database";
				} ?>
			</td> 
			<td>Level:  </td>
			<td><input type="text" name="<?php print $type; ?>_level" value="<?php print $level; ?>" size=3 /></td> <!-- check sizes -->
			<td>Experience Cost: </td>
			<td><input type="number" name="<?php print $type; ?>_cost" value="<?php print $cost; ?>" /></td>
		</tr>
		<tr>
			<td>Sourcebook: </td>
			<td>
				<select name="<?php print $type; ?>_sourcebook">
					<?php
						foreach ($booklist as $book) {
							print "<option value='{$book->ID}' ";
							($book->ID == $bookid) ? print "selected" : print "";
							echo ">" . vtm_formatOutput($book->NAME) . "</option>";
						}
					?>
				</select>
			</td>
			
			<td>Page Number: </td>
			<td><input type="number" name="<?php print $type; ?>_page_number" value="<?php print $pagenum; ?>" /></td>
			<td colspan=2>&nbsp;</td>
		</tr>
		<tr>
			<td>Dicepool: </td><td><input type="text" name="<?php print $type; ?>_dicepool" value="<?php print $dicepool; ?>" /></td>
			<td>Difficulty: </td><td><input type="number" name="<?php print $type; ?>_difficulty" value="<?php print $diff; ?>" /></td>
			<td colspan=2>&nbsp;</td>
		</tr>
		<tr>
			<td>Description: </td><td colspan=5><input type="text" name="<?php print $type; ?>_desc" value="<?php print vtm_formatOutput($desc); ?>" size=120 /></td>
		</tr>
		</table>
		<input type="submit" name="do_add_<?php print $type; ?>" class="button-primary" value="Save <?php print ucfirst($type); ?>" />
	</form>
	
	<?php
}
function vtm_render_book_add_form($addaction) {

	global $wpdb;
	
	$type = "book";
	
	/* echo "<p>Creating book form based on action $addaction</p>"; */

	if ('fix-' . $type == $addaction) {
		$id = $_REQUEST['book'];
		$name = $_REQUEST[$type . '_name'];

		$visible = $_REQUEST[$type . '_visible'];
		$code = $_REQUEST[$type . '_code'];
		
		$nextaction = $_REQUEST['action'];
		
	} else if ('edit-' . $type == $addaction) {
		/* Get values from database */
		$id   = $_REQUEST['book'];
		
		$sql = "select books.ID, books.NAME, books.CODE, books.VISIBLE
				from " . VTM_TABLE_PREFIX . "SOURCE_BOOK books
				where books.ID = %d;";
		
		/* echo "<p>$sql</p>"; */
		
		$data =$wpdb->get_results($wpdb->prepare($sql, $id));
		
		/* print_r($data); */
		
		$name = $data[0]->NAME;
		$code = $data[0]->CODE;
		$visible = $data[0]->VISIBLE;
		
		$nextaction = "save";
		
	} else {
	
		/* defaults */
		$name = "";
		$code = "";
		$visible = "Y";
		
		$nextaction = "add";
	}

	$current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
	$current_url = remove_query_arg( 'action', $current_url );
	?>
	<form id="new-<?php print $type; ?>" method="post" action='<?php print htmlentities($current_url); ?>'>
		<input type="hidden" name="<?php print $type; ?>_id" value="<?php print $id; ?>"/>
		<input type="hidden" name="tab" value="<?php print $type; ?>" />
		<input type="hidden" name="action" value="<?php print $nextaction; ?>" />
		<table style='width:500px'>
		<tr>
			<td><?php print ucfirst($type); ?> Code:  </td>
			<td><input type="text" name="<?php print $type; ?>_code" value="<?php print vtm_formatOutput($code); ?>" size=16 /></td> <!-- check sizes -->
		</tr>
		<tr>
			<td><?php print ucfirst($type); ?> Name:  </td>
			<td><input type="text" name="<?php print $type; ?>_name" value="<?php print vtm_formatOutput($name); ?>" size=60 /></td> <!-- check sizes -->
		</tr>
		<tr>
			<td>Visible to Players: </td><td>
				<select name="<?php print $type; ?>_visible">
					<option value="N" <?php selected($visible, "N"); ?>>No</option>
					<option value="Y" <?php selected($visible, "Y"); ?>>Yes</option>
				</select></td>

		</tr>
		</table>
		<input type="submit" name="do_add_<?php print $type; ?>" class="button-primary" value="Save <?php print ucfirst($type); ?>" />
	</form>
	
	<?php
}


function vtm_merit_input_validation($type) {
	global $wpdb;

	$doaction = '';

	if (!empty($_REQUEST['action']) && $_REQUEST['action'] == 'edit' && $_REQUEST['tab'] == $type)
		$doaction = "edit-$type"; 
		
	/* echo "<p>Requested action: " . $_REQUEST['action'] . ", " . $type . "_name: " . $_REQUEST[$type . '_name']; */
	
	
	if (!empty($_REQUEST[$type . '_name'])){
	
		if ($_REQUEST['action'] == 'add') {
			$sql = $wpdb->prepare("SELECT ID FROM " . VTM_TABLE_PREFIX. "MERIT
				WHERE NAME = %s", $_REQUEST[$type . '_name']);
			$match = $wpdb->get_var($sql);
			//echo "<p>Result: $match, SQL: $sql</p>";
			if (isset($match) && $match > 0) {
				$doaction = "fix-$type";
				echo "<p style='color:red'>ERROR: {$_REQUEST[$type . '_name']} already exists</p>";
			}
		}

		$doaction = $_REQUEST['action'] . "-" . $type;
		
		/* Input Validation */
		if (empty($_REQUEST[$type . '_group']) || $_REQUEST[$type . '_group'] == "") {
			$doaction = "fix-$type";
			echo "<p style='color:red'>ERROR: Name of group is missing</p>";
		}
		if (empty($_REQUEST[$type . '_desc']) || $_REQUEST[$type . '_desc'] == "") {
			$doaction = "fix-$type";
			echo "<p style='color:red'>ERROR: Description is missing</p>";
		}
		/* Page number is a number */
		if (empty($_REQUEST[$type . '_page_number']) || $_REQUEST[$type . '_page_number'] == "") {
			$doaction = "fix-$type";
			echo "<p style='color:red'>ERROR: sourcebook page number is missing</p>";
		} else if ($_REQUEST[$type . '_page_number'] <= 0) {
			$doaction = "fix-$type";
			echo "<p style='color:red'>ERROR: Invalid sourcebook page number</p>";
		} 
		
		/* Freebies is greater than 0 */
		if (empty($_REQUEST[$type . '_cost']) || $_REQUEST[$type . '_cost'] == "") {
			echo "<p style='color:orange'>Warning: Freebie point cost is missing. Will save cost as 0.</p>";
		} else if ($_REQUEST[$type . '_cost'] == 0 && $type == "flaw") {
			echo "<p style='color:orange'>Warning: Freebie point cost is 0 and will be saved as a Merit</p>";
		} else if ($type == "merit" && $_REQUEST[$type . '_cost'] < 0) {
			$doaction = "fix-$type";
			echo "<p style='color:red'>ERROR: Freebie point cost for merits should greater than or equal to 0</p>";
		} else if ($type == "flaw" && $_REQUEST[$type . '_cost'] > 0) {
			$doaction = "fix-$type";
			echo "<p style='color:red'>ERROR: Freebie point cost for flaws should less than or equal to 0</p>";
		}

		
		/* XP is 0 or greater */
		if ($_REQUEST[$type . '_xp_cost'] < 0) {
			$doaction = "fix-$type";
			echo "<p style='color:red'>ERROR: Experience point cost should greater than or equal to 0</p>";
		}
		
	} 
	
	/* echo "action: $doaction</p>"; */

	return $doaction;
}

function vtm_ritual_input_validation() {

	$type = "ritual";
	$doaction = '';

	if (!empty($_REQUEST['action']) && $_REQUEST['action'] == 'edit' && $_REQUEST['tab'] == $type)
		$doaction = "edit-$type"; 
		
	/* echo "<p>Requested action: " . $_REQUEST['action'] . ", " . $type . "_name: " . $_REQUEST[$type . '_name']; */
	
	if (!empty($_REQUEST[$type . '_name'])){
			
		$doaction = $_REQUEST['action'] . "-" . $type;
		
		/* Input Validation */
		if (empty($_REQUEST[$type . '_desc']) || $_REQUEST[$type . '_desc'] == "") {
			$doaction = "fix-$type";
			echo "<p style='color:red'>ERROR: Description is missing</p>";
		}
		
		/* Level is a number greater than 0 */
		if (empty($_REQUEST[$type . '_level']) || $_REQUEST[$type . '_level'] == "") {
			$doaction = "fix-$type";
			echo "<p style='color:red'>ERROR: Level is missing</p>";
		} else if ($_REQUEST[$type . '_level'] <= 0) {
			$doaction = "fix-$type";
			echo "<p style='color:red'>ERROR: Level should be a number greater than 0</p>";
		} 
		/* Dice pool is not empty */
		if (empty($_REQUEST[$type . '_dicepool']) || $_REQUEST[$type . '_dicepool'] == "") {
			$doaction = "fix-$type";
			echo "<p style='color:red'>ERROR: Dicepool is missing</p>";
		}
		
		/* Page number is a number */
		if (empty($_REQUEST[$type . '_page_number']) || $_REQUEST[$type . '_page_number'] == "") {
			$doaction = "fix-$type";
			echo "<p style='color:red'>ERROR: sourcebook page number is missing</p>";
		} else if ($_REQUEST[$type . '_page_number'] <= 0) {
			$doaction = "fix-$type";
			echo "<p style='color:red'>ERROR: Invalid sourcebook page number</p>";
		} 
		
		/* XP is 0 or greater */
		if ($_REQUEST[$type . '_cost'] < 0) {
			$doaction = "fix-$type";
			echo "<p style='color:red'>ERROR: Experience point cost should greater than or equal to 0</p>";
		}
		
		// No disciplines (indicates empty database)
		if (!isset($_REQUEST[$type . '_disc'])) {
			$doaction = "fix-$type";
			echo "<p style='color:red'>ERROR: Associated discipline must be added to the database</p>";
		}
		
	}
	
	/* echo "action: $doaction</p>"; */

	return $doaction;
}

function vtm_book_input_validation() {

	$type = "book";
	$doaction = '';

	if (!empty($_REQUEST['action']) && $_REQUEST['action'] == 'edit' && $_REQUEST['tab'] == $type)
		$doaction = "edit-$type";
		
	/* echo "<p>Requested action: " . $_REQUEST['action'] . ", " . $type . "_name: " . $_REQUEST[$type . '_name']; */
	
	
	if (!empty($_REQUEST[$type . '_name'])){
		$doaction = $_REQUEST['action'] . "-" . $type;
			
		/* Input Validation */
		if (empty($_REQUEST[$type . '_code']) || $_REQUEST[$type . '_code'] == "") {
			$doaction = "fix-$type";
			echo "<p style='color:red'>ERROR: Book code is missing</p>";
		}
				
	}
	
	/* echo "action: $doaction</p>"; */

	return $doaction;
}


/* 
-----------------------------------------------
MERITS AND FLAWS TABLE
------------------------------------------------ */


class vtmclass_admin_meritsflaws_table extends vtmclass_MultiPage_ListTable {
   
    function __construct(){
        global $status, $page;
                
        parent::__construct( array(
            'singular'  => 'merit',     
            'plural'    => 'merits',    
            'ajax'      => false        
        ) );
        
    }
	
	function delete_merit($selectedID) {
		global $wpdb;
		
		/* Check if merit id in use */
		$sql = "select characters.NAME 
			from " . VTM_TABLE_PREFIX . "CHARACTER_MERIT charmerits , " . VTM_TABLE_PREFIX . "CHARACTER characters
			where charmerits.MERIT_ID = %d and charmerits.CHARACTER_ID = characters.ID;";
		$isused = $wpdb->get_results($wpdb->prepare($sql, $selectedID));
		
		if ($isused) {
			echo "<p style='color:red'>Cannot delete as this {$this->type} is being used in the following characters:";
			echo "<ul>";
			foreach ($isused as $character)
				echo "<li style='color:red'>" . stripslashes($character->NAME) . "</li>";
			echo "</ul></p>";
		} else {
			$sql = "delete from " . VTM_TABLE_PREFIX . "MERIT where ID = %d;";
			
			$result = $wpdb->get_results($wpdb->prepare($sql, $selectedID));
		
			/* print_r($result); */
			echo "<p style='color:green'>Deleted item $selectedID</p>";
		}
	}
 	function showhide_merit($selectedID, $showhide) {
		global $wpdb;
		
		//echo "id: $selectedID, setting: $showhide";
		
		$wpdb->show_errors();
		
		$visiblity = $showhide == 'hide' ? 'N' : 'Y';
		
		$result = $wpdb->update( VTM_TABLE_PREFIX . "MERIT", 
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
	
 	function add_merit($meritname, $meritgroup, $sourcebookid, $pagenum,
						$cost, $xp_cost, $multiple, $visible, $description, $question, 
						$hasspec, $profile) {
		global $wpdb;
		
		$wpdb->show_errors();
		
		$dataarray = array(
						'NAME' => $meritname,
						'DESCRIPTION' => $description,
						'GROUPING' => $meritgroup,
						'SOURCE_BOOK_ID' => $sourcebookid,
						'PAGE_NUMBER' => $pagenum,
						'COST'  => $cost,
						'VALUE' => $cost,
						'XP_COST' => $xp_cost,
						'MULTIPLE' => $multiple,
						'VISIBLE' => $visible,
						'BACKGROUND_QUESTION' => $question,
						'HAS_SPECIALISATION' => $hasspec,
						'PROFILE_DISPLAY_ID' => $profile
					);
		
		/* print_r($dataarray); */
		
		$wpdb->insert(VTM_TABLE_PREFIX . "MERIT",
					$dataarray,
					array (
						'%s',
						'%s',
						'%s',
						'%d',
						'%d',
						'%d',
						'%d',
						'%d',
						'%s',
						'%s',
						'%s',
						'%s',
						'%d'
					)
				);
		
		if ($wpdb->insert_id == 0) {
			echo "<p style='color:red'><b>Error:</b> " . vtm_formatOutput($meritname) . " could not be inserted (";
			$wpdb->print_error();
			echo ")</p>";
		} else {
			echo "<p style='color:green'>Added " . vtm_formatOutput($meritgroup) . " merit/flaw '" . stripslashes($meritname) . "' (ID: {$wpdb->insert_id})</p>";
		}
	}
 	function edit_merit($meritid, $meritname, $meritgroup, $sourcebookid, $pagenum,
						$cost, $xp_cost, $multiple, $visible, $description, $question, 
						$hasspec, $profile) {
		global $wpdb;
		
		$wpdb->show_errors();
		
		$dataarray = array(
						'NAME' => $meritname,
						'DESCRIPTION' => $description,
						'GROUPING' => $meritgroup,
						'SOURCE_BOOK_ID' => $sourcebookid,
						'PAGE_NUMBER' => $pagenum,
						'COST'  => $cost,
						'VALUE' => $cost,
						'XP_COST' => $xp_cost,
						'MULTIPLE' => $multiple,
						'VISIBLE' => $visible,
						'BACKGROUND_QUESTION' => $question,
						'HAS_SPECIALISATION' => $hasspec,
						'PROFILE_DISPLAY_ID' => $profile
					);
		
		/* print_r($dataarray); */
		
		$result = $wpdb->update(VTM_TABLE_PREFIX . "MERIT",
					$dataarray,
					array (
						'ID' => $meritid
					)
				);
		
		if ($result) 
			echo "<p style='color:green'>Updated " . vtm_formatOutput($meritname) . "</p>";
		else if ($result === 0) 
			echo "<p style='color:orange'>No updates made to " . vtm_formatOutput($meritname) . "</p>";
		else {
			$wpdb->print_error();
			echo "<p style='color:red'>Could not update " . vtm_formatOutput($meritname) . " ($meritid)</p>";
		}
	}
   
    function column_default($item, $column_name){
        switch($column_name){
            case 'DESCRIPTION':
                return vtm_formatOutput($item->$column_name);
            case 'GROUPING':
                return vtm_formatOutput($item->$column_name);
            case 'COST':
                return $item->$column_name;
             case 'XP_COST':
                return $item->$column_name;
            case 'PAGE_NUMBER':
                return $item->$column_name;
            case 'SOURCEBOOK':
                return vtm_formatOutput($item->$column_name);
           default:
                return print_r($item,true); 
        }
    }
 
    function column_name($item){
	
		$act = ($item->VISIBLE === 'Y') ? 'hide' : 'show';
        
        $actions = array(
            'edit'      => sprintf('<a href="?page=%s&amp;action=%s&amp;merit=%s&amp;tab=%s">Edit</a>',$_REQUEST['page'],'edit',$item->ID, $this->type),
            'delete'    => sprintf('<a href="?page=%s&amp;action=%s&amp;merit=%s&amp;tab=%s">Delete</a>',$_REQUEST['page'],'delete',$item->ID, $this->type),
            $act        => sprintf('<a href="?page=%s&amp;action=%s&amp;merit=%s&amp;tab=%s">%s</a>',$_REQUEST['page'],$act,$item->ID, $this->type, ucfirst($act)),
        );
        
        
        return sprintf('%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
            vtm_formatOutput($item->NAME),
            $item->ID,
            $this->row_actions($actions)
        );
    }
   
	function column_multiple($item){
		return ($item->MULTIPLE == "Y") ? "Yes" : "No";
    }
	function column_has_specialisation($item){
		return ($item->HAS_SPECIALISATION == "Y") ? "Yes" : "No";
    }
	function column_profile_display($item){
		return $item->PROFILE_DISPLAY_ID ? "Yes" : "No";
    }
	
	function column_has_bg_q($item) {
		if ($item->BACKGROUND_QUESTION == "")
			return "No";
		else
			return "Yes";
			
	}
   

    function get_columns(){
        $columns = array(
            'cb'           => '<input type="checkbox" />', 
            'NAME'         => 'Name',
            'DESCRIPTION'  => 'Description',
            'GROUPING'     => 'Grouping / Type',
            'COST'         => 'Freebie Cost',
            'XP_COST'      => 'Experience Cost',
            'MULTIPLE'     => 'Can be bought multiple times?',
            'HAS_SPECIALISATION' => 'Need specialisation?',
			'SOURCEBOOK'   => 'Source Book',
            'PAGE_NUMBER'  => 'Source Page',
            'VISIBLE'      => 'Visible to Players',
            'HAS_BG_Q'     => 'Extended Background',
            'PROFILE_DISPLAY' => 'Display in Profile'
        );
        return $columns;
		
    }
    
    function get_sortable_columns() {
        $sortable_columns = array(
            'NAME'        => array('NAME',false),
            'GROUPING'    => array('GROUPING',false),
            'COST'  	  => array('COST',false),
            'XP_COST'     => array('XP_COST',false)
        );
        return $sortable_columns;
    }
	
	
	
    
    function get_bulk_actions() {
        $actions = array(
            'delete'    => 'Delete',
			'hide'      => 'Hide',
 			'show'      => 'Show'
       );
        return $actions;
    }
    
    function process_bulk_action() {
        
		/* echo "<p>Bulk action " . $this->current_action() . ", currently on tab {$_REQUEST['tab']} and will do action if {$this->type}.</p>"; */
		
        if( 'delete'===$this->current_action() && $_REQUEST['tab'] == $this->type && isset($_REQUEST['merit'])) {
			if ('string' == gettype($_REQUEST['merit'])) {
				$this->delete_merit($_REQUEST['merit']);
			} else {
				foreach ($_REQUEST['merit'] as $merit) {
					$this->delete_merit($merit);
				}
			}
        }
		
        if( 'hide'===$this->current_action() && $_REQUEST['tab'] == $this->type && isset($_REQUEST['merit']) ) {
			if ('string' == gettype($_REQUEST['merit'])) {
				$this->showhide_merit($_REQUEST['merit'], "hide");
			} else {
				foreach ($_REQUEST['merit'] as $merit) {
					$this->showhide_merit($merit, "hide");
				}
			}
        }
        if( 'show'===$this->current_action() && $_REQUEST['tab'] == $this->type && isset($_REQUEST['merit']) ) {
			if ('string' == gettype($_REQUEST['merit'])) {
				$this->showhide_merit($_REQUEST['merit'], "show");
			} else {
				foreach ($_REQUEST['merit'] as $merit) {
					$this->showhide_merit($merit, "show");
				}
			}
        }
    }
	
	function extra_tablenav($which) {
		if ($which == 'top') {

			echo "<div class='gvfilter'>";
			/* Select if visible */
			echo "<span>Visiblity to Players: </span>";
			if ( !empty( $this->filter_visible ) ) {
				echo "<select name='{$this->type}_filter'>";
				foreach( $this->filter_visible as $key => $value ) {
					echo '<option value="' . esc_attr( $key ) . '" ';
					selected( $this->active_filter_visible, $key );
					echo '>' . vtm_formatOutput( $value ) . '</option>';
				}
				echo '</select>';
			}
			
			/* Select Grouping */
			echo "<span>Group: </span>";
			if ( !empty( $this->filter_group ) ) {
				echo "<select name='{$this->type}_group'>";
				foreach( $this->filter_group as $key => $value ) {
					echo '<option value="' . esc_attr( $key ) . '" ';
					selected( $this->active_filter_group, $key );
					echo '>' . vtm_formatOutput( $value ) . '</option>';
				}
				echo '</select>';
			}
			
			/* Select if multiple */
			echo "<span>Multiple: </span>";
			if ( !empty( $this->filter_multiple ) ) {
				echo "<select name='{$this->type}_multiple'>";
				foreach( $this->filter_multiple as $key => $value ) {
					echo '<option value="' . esc_attr( $key ) . '" ';
					selected( $this->active_filter_multiple, $key );
					echo '>' . vtm_formatOutput( $value ) . '</option>';
				}
				echo '</select>';
			}
			
			submit_button( 'Filter', 'secondary', 'do_filter_merits', false );
			echo "</div>";
		}
	}
    
    
    function prepare_items_bytype($type) {
        global $wpdb; 

        /* $per_page = 20; */
        
        $columns = $this->get_columns();
        $hidden = array('VALUE');
        $sortable = $this->get_sortable_columns();
        
		/* setup filters here */
		$this->filter_visible = array(
				'all' => 'Both',
				'y'   => 'Visible',
				'n'   => 'Not Visible',
			);
		$this->filter_multiple = array(
				'all' => 'All',
				'y' => 'Yes',
				'n'  => 'No',
			);
			
		$sql = "SELECT DISTINCT GROUPING FROM " . VTM_TABLE_PREFIX . "MERIT merit;";
		$groups =$wpdb->get_results($sql);
		$this->filter_group = vtm_make_filter($groups);
			
		if ( isset( $_REQUEST[$type . '_filter'] ) && array_key_exists( $_REQUEST[$type . '_filter'], $this->filter_visible ) ) {
			$this->active_filter_visible = sanitize_key( $_REQUEST[$type . '_filter'] );
		} else {
			$this->active_filter_visible = 'all';
		}
		if ( isset( $_REQUEST[$type . '_group'] ) && array_key_exists( $_REQUEST[$type . '_group'], $this->filter_group ) ) {
			$this->active_filter_group = sanitize_key( $_REQUEST[$type . '_group'] );
		} else {
			$this->active_filter_group = 'all';
		}
		if ( isset( $_REQUEST[$type . '_multiple'] ) && array_key_exists( $_REQUEST[$type . '_multiple'], $this->filter_multiple ) ) {
			$this->active_filter_multiple = sanitize_key( $_REQUEST[$type . '_multiple'] );
		} else {
			$this->active_filter_multiple = 'all';
		}
			
		$this->_column_headers = array($columns, $hidden, $sortable);
        
		$this->type = $type;
        
        $this->process_bulk_action();
		
		
		/* Get the data from the database */
		$sql = "select merit.ID, merit.NAME as NAME, merit.DESCRIPTION as DESCRIPTION, merit.GROUPING as GROUPING,
						merit.COST as COST, merit.XP_COST as XP_COST, merit.MULTIPLE as MULTIPLE,
						books.NAME as SOURCEBOOK, merit.PAGE_NUMBER as 	PAGE_NUMBER,
						merit.VISIBLE as VISIBLE, merit.BACKGROUND_QUESTION, merit.HAS_SPECIALISATION,
						merit.PROFILE_DISPLAY_ID
						from " . VTM_TABLE_PREFIX. "MERIT merit, " . VTM_TABLE_PREFIX . "SOURCE_BOOK books where ";
		if ($type == "merit") {
			$sql .= "merit.value >= 0";
		} else {
			$sql .= "merit.value < 0";
		}
		
		if ( "all" !== $this->active_filter_visible)
			$sql .= " AND merit.visible = '" . $this->active_filter_visible . "'";
		if ( "all" !== $this->active_filter_multiple)			
			$sql .= " AND merit.multiple = '" . $this->active_filter_multiple . "'";
		if ( "all" !== $this->active_filter_group )			
			$sql .= " AND merit.grouping = '" . $this->active_filter_group . "'";
		$sql .= " AND books.ID = merit.SOURCE_BOOK_ID";
		
		if (!empty($_REQUEST['orderby']) && !empty($_REQUEST['order']) && $type == $_REQUEST['tab'])
			$sql .= " ORDER BY merit.{$_REQUEST['orderby']} {$_REQUEST['order']}";
		
		$sql .= ";";
		/* echo "<p>SQL: " . $sql . "</p>"; */
		
		$data =$wpdb->get_results($sql);
        
        $current_page = $this->get_pagenum();
        $total_items = count($data);
        
        /* $data = array_slice($data,(($current_page-1)*$per_page),$per_page); */
        
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
/* 
-----------------------------------------------
RITUALS TABLE
------------------------------------------------ */


class vtmclass_admin_rituals_table extends vtmclass_MultiPage_ListTable {
   
    function __construct(){
        global $status, $page;
                
        parent::__construct( array(
            'singular'  => 'ritual',     
            'plural'    => 'rituals',    
            'ajax'      => false        
        ) );
    }
	
	function delete_ritual($selectedID) {
		global $wpdb;
		
		/* Check if ritual id in use */
		$sql = "select characters.NAME
					from " . VTM_TABLE_PREFIX . "CHARACTER_RITUAL charrituals, " . VTM_TABLE_PREFIX . "CHARACTER characters
					where charrituals.RITUAL_ID = %d and charrituals.CHARACTER_ID = characters.ID;";
		$isused = $wpdb->get_results($wpdb->prepare($sql, $selectedID));
		
		if ($isused) {
			echo "<p style='color:red'>Cannot delete as this ritual is being used in the following characters:";
			echo "<ul>";
			foreach ($isused as $character)
				echo "<li style='color:red'>" . vtm_formatOutput($character->NAME) . "</li>";
			echo "</ul></p>";
		} else {
		
			$sql = "delete from " . VTM_TABLE_PREFIX . "RITUAL where ID = %d;";
			
			$result = $wpdb->get_results($wpdb->prepare($sql, $selectedID));
		
			/* print_r($result); */
			echo "<p style='color:green'>Deleted ritual $selectedID</p>";
		}
	}
	
 	function add_ritual($ritualname, $description, $level, $disciplineid, $dicepool,
						$difficulty, $xp_cost, $sourcebookid, $pagenum, $visible) {
		global $wpdb;
		
		$wpdb->show_errors();
		
		$dataarray = array(
						'NAME' => $ritualname,
						'DESCRIPTION' => $description,
						'LEVEL' => $level,
						'DISCIPLINE_ID' => $disciplineid,
						'DICE_POOL' => $dicepool,
						'DIFFICULTY' => $difficulty,
						'COST' => $xp_cost,
						'SOURCE_BOOK_ID' => $sourcebookid,
						'PAGE_NUMBER' => $pagenum,
						'VISIBLE' => $visible
					);
		
		/* print_r($dataarray); */
		
		$wpdb->insert(VTM_TABLE_PREFIX . "RITUAL",
					$dataarray,
					array (
						'%s',
						'%s',
						'%d',
						'%d',
						'%s',
						'%s',
						'%d',
						'%d',
						'%d',
						'%s'
					)
				);
		
		if ($wpdb->insert_id == 0) {
			echo "<p style='color:red'><b>Error:</b> " . vtm_formatOutput($ritualname) . " could not be inserted (";
			$wpdb->print_error();
			echo ")</p>";
		} else {
			echo "<p style='color:green'>Added ritual '" . vtm_formatOutput($ritualname) . "' (ID: {$wpdb->insert_id})</p>";
		}
	}
 	function edit_ritual($ritualid, $ritualname, $description, $level, $disciplineid, $dicepool,
						$difficulty, $xp_cost, $sourcebookid, $pagenum, $visible) {
		global $wpdb;
		
		$wpdb->show_errors();
		
		$dataarray = array(
						'NAME' => $ritualname,
						'DESCRIPTION' => $description,
						'LEVEL' => $level,
						'DISCIPLINE_ID' => $disciplineid,
						'DICE_POOL' => $dicepool,
						'DIFFICULTY' => $difficulty,
						'COST' => $xp_cost,
						'SOURCE_BOOK_ID' => $sourcebookid,
						'PAGE_NUMBER' => $pagenum,
						'VISIBLE' => $visible
					);
		
		/* print_r($dataarray); */
		
		$result = $wpdb->update(VTM_TABLE_PREFIX . "RITUAL",
					$dataarray,
					array (
						'ID' => $ritualid
					)
				);
		
		if ($result) 
			echo "<p style='color:green'>Updated " . vtm_formatOutput($ritualname) . "</p>";
		else if ($result === 0) 
			echo "<p style='color:orange'>No updates made to " . vtm_formatOutput($ritualname) . "</p>";
		else {
			$wpdb->print_error();
			echo "<p style='color:red'>Could not update " . vtm_formatOutput($ritualname) . " ($ritualid)</p>";
		}
	}
   
    function column_default($item, $column_name){
        switch($column_name){
            case 'DESCRIPTION':
                return vtm_formatOutput($item->$column_name);
            case 'LEVEL':
                return $item->$column_name;
            case 'DISCIPLINE':
                return vtm_formatOutput($item->$column_name);
            case 'DICE_POOL':
                return $item->$column_name;
            case 'DIFFICULTY':
                return $item->$column_name;
             case 'COST':
                return $item->$column_name;
            case 'PAGE_NUMBER':
                return $item->$column_name;
            case 'SOURCEBOOK':
                return vtm_formatOutput($item->$column_name);
           default:
                return print_r($item,true); 
        }
    }
 
    function column_name($item){
        
        $actions = array(
            'edit'      => sprintf('<a href="?page=%s&amp;action=%s&amp;ritual=%s&amp;tab=%s">Edit</a>',$_REQUEST['page'],'edit',$item->ID, $this->type),
            'delete'    => sprintf('<a href="?page=%s&amp;action=%s&amp;ritual=%s&amp;tab=%s">Delete</a>',$_REQUEST['page'],'delete',$item->ID, $this->type),
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
            'LEVEL'        => 'Ritual Level',
            'DISCIPLINE'   => 'Associated Discipline',
            'DICE_POOL'    => 'Dice Pool',
            'DIFFICULTY'   => 'Difficulty of roll',
            'COST'         => 'Experience Cost',
            'SOURCEBOOK'   => 'Source Book',
            'PAGE_NUMBER'  => 'Source Page',
            'VISIBLE'      => 'Visible to Players'
        );
        return $columns;
		
    }
    
    function get_sortable_columns() {
        $sortable_columns = array(
            'NAME'        => array('NAME',false),
            'LEVEL'       => array('LEVEL',false),
            'COST'  	  => array('COST',false),
            'SOURCEBOOK'  => array('SOURCEBOOK',false)
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
        		
        if( 'delete'===$this->current_action() && $_REQUEST['tab'] == $this->type && isset($_REQUEST['ritual'])) {
			if ('string' == gettype($_REQUEST['ritual'])) {
				$this->delete_ritual($_REQUEST['ritual']);
			} else {
				foreach ($_REQUEST['ritual'] as $ritual) {
					$this->delete_ritual($ritual);
				}
			}
        }
     }
	
	function extra_tablenav($which) {
		if ($which == 'top') {

			echo "<div class='gvfilter'>";
			echo "<span>Discipline: </span>";
			if ( !empty( $this->filter_discipline ) ) {
				echo "<select name='{$this->type}_discipline'>";
				foreach( $this->filter_discipline as $key => $value ) {
					echo '<option value="' . esc_attr( $key ) . '" ';
					selected( $this->active_filter_discipline, $key );
					echo '>' . esc_attr( $value ) . '</option>';
				}
				echo '</select>';
			}
			
			echo "<span>Level: </span>";
			if ( !empty( $this->filter_level ) ) {
				echo "<select name='{$this->type}_flevel'>";
				foreach( $this->filter_level as $key => $value ) {
					echo '<option value="' . esc_attr( $key ) . '" ';
					selected( $this->active_filter_level, $key );
					echo '>' . esc_attr( $value ) . '</option>';
				}
				echo '</select>';
			}
			
			echo "<span>Sourcebook: </span>";
			if ( !empty( $this->filter_book ) ) {
				echo "<select name='{$this->type}_book'>";
				foreach( $this->filter_book as $key => $value ) {
					echo '<option value="' . esc_attr( $key ) . '" ';
					selected( $this->active_filter_book, $key );
					echo '>' . esc_attr( $value ) . '</option>';
				}
				echo '</select>';
			}
			
			submit_button( 'Filter', 'secondary', 'do_filter_rituals', false );
			echo "</div>";
		}
	}
        
    function prepare_items() {
        global $wpdb; 
        
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
		
		$type = "ritual";
        
		/* setup filters here */
		$sql = "SELECT DISTINCT disciplines.ID as ID, disciplines.NAME as NAME
				FROM " . VTM_TABLE_PREFIX . "RITUAL rituals, " . VTM_TABLE_PREFIX . "DISCIPLINE disciplines
				WHERE disciplines.ID = rituals.DISCIPLINE_ID;";
		$disciplines = $wpdb->get_results($sql);
		$this->filter_discipline = vtm_make_filter($disciplines);
		
		/* Ritual Level filter */
		$sql = "SELECT DISTINCT LEVEL FROM " . VTM_TABLE_PREFIX . "RITUAL;";
		$levels = $wpdb->get_results($sql);
		$this->filter_level = vtm_make_filter($levels);
			
		/* Book filter */
		$sql = "SELECT DISTINCT books.ID, books.NAME 
				FROM " . VTM_TABLE_PREFIX . "RITUAL rituals, " . VTM_TABLE_PREFIX . "SOURCE_BOOK books
				WHERE rituals.SOURCE_BOOK_ID = books.ID;";
		$books = $wpdb->get_results($sql);
		$this->filter_book = vtm_make_filter($books);
						
		if ( isset( $_REQUEST[$type . '_discipline'] ) && array_key_exists( $_REQUEST[$type . '_discipline'], $this->filter_discipline ) ) {
			$this->active_filter_discipline = sanitize_key( $_REQUEST[$type . '_discipline'] );
		} else {
			$this->active_filter_discipline = 'all';
		}
		if ( isset( $_REQUEST[$type . '_flevel'] ) && array_key_exists( $_REQUEST[$type . '_flevel'], $this->filter_level ) ) {
			$this->active_filter_level = sanitize_key( $_REQUEST[$type . '_flevel'] );
		} else {
			$this->active_filter_level = 'all';
		}
		if ( isset( $_REQUEST[$type . '_book'] ) && array_key_exists( $_REQUEST[$type . '_book'], $this->filter_book ) ) {
			$this->active_filter_book = sanitize_key( $_REQUEST[$type . '_book'] );
		} else {
			$this->active_filter_book = 'all';
		}
			
		$this->_column_headers = array($columns, $hidden, $sortable);
        
		$this->type = $type;
        
        $this->process_bulk_action();
		
		/* Get the data from the database */
		$sql = "select rituals.ID, rituals.NAME as NAME, rituals.DESCRIPTION as DESCRIPTION, rituals.LEVEL as LEVEL,
					disciplines.NAME as DISCIPLINE, rituals.DICE_POOL as DICE_POOL,
					rituals.COST as COST, rituals.DIFFICULTY as DIFFICULTY,
						books.NAME as SOURCEBOOK, rituals.PAGE_NUMBER as PAGE_NUMBER,
						rituals.VISIBLE as VISIBLE
				from " . VTM_TABLE_PREFIX. "RITUAL rituals, " . VTM_TABLE_PREFIX . "SOURCE_BOOK books, "
						. VTM_TABLE_PREFIX. "DISCIPLINE disciplines 
				where disciplines.ID = rituals.DISCIPLINE_ID and books.ID = rituals.SOURCE_BOOK_ID";
		
		/* limit data according to the filters */
		if ( "all" !== $this->active_filter_discipline)
			$sql .= " AND disciplines.ID = '" . $this->active_filter_discipline . "'";
		if ( "all" !== $this->active_filter_level)			
			$sql .= " AND rituals.LEVEL = '" . $this->active_filter_level . "'";
		if ( "all" !== $this->active_filter_book )			
			$sql .= " AND books.ID = '" . $this->active_filter_book . "'";
		
		/* order the data according to sort columns */
		if (!empty($_REQUEST['orderby']) && !empty($_REQUEST['order']))
			$sql .= " ORDER BY rituals.{$_REQUEST['orderby']} {$_REQUEST['order']}";
		
		$sql .= ";";
		
		/* echo "<p>SQL: $sql</p>"; */
		
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
/* 
-----------------------------------------------
SOURCEBOOKS TABLE
------------------------------------------------ */


class vtmclass_admin_books_table extends vtmclass_MultiPage_ListTable {
   
    function __construct(){
        global $status, $page;
                
        parent::__construct( array(
            'singular'  => 'book',     
            'plural'    => 'books',    
            'ajax'      => false        
        ) );
    }
	
	function delete_book($selectedID) {
		global $wpdb;
		
		/* Check if book in use in MERITS and FLAWS */
		$sql = "select merits.NAME
				from " . VTM_TABLE_PREFIX . "MERIT merits, " . VTM_TABLE_PREFIX . "SOURCE_BOOK books
				where books.ID = merits.SOURCE_BOOK_ID and merits.SOURCE_BOOK_ID = %d;";
		$isused = $wpdb->get_results($wpdb->prepare($sql, $selectedID));
		if ($isused) {
			echo "<p style='color:red'>Cannot delete as this book is being used in the Merits and Flaws list:";
			echo "<ul>";
			foreach ($isused as $item)
				echo "<li style='color:red'>" . vtm_formatOutput($item->NAME) . "</li>";
			echo "</ul></p>";
			return;
		}
		
		/* Check if book in use in RITUALS */
		$sql = "select rituals.NAME
				from " . VTM_TABLE_PREFIX . "RITUAL rituals, " . VTM_TABLE_PREFIX . "SOURCE_BOOK books
				where books.ID = rituals.SOURCE_BOOK_ID and rituals.SOURCE_BOOK_ID = %d;";
		$isused = $wpdb->get_results($wpdb->prepare($sql, $selectedID));
		if ($isused) {
			echo "<p style='color:red'>Cannot delete as this book is being used in the Rituals list:";
			echo "<ul>";
			foreach ($isused as $item)
				echo "<li style='color:red'>" . vtm_formatOutput($item->NAME) . "</li>";
			echo "</ul></p>";
			return;
		}

		/* Check if book in use in COMBO DISCIPLINE */
		
		/* Check if book in use in DISCIPLINE */
		
		/* Check if book in use in MAJIK PATH */
		
		/* Check if book in use in ROAD OR PATH */
		
		if ($isused) {
			echo "<p style='color:red'>Cannot delete as this book is being used in the following places:";
			echo "<ul>";
			foreach ($isused as $character)
				echo "<li style='color:red'></li>";
			echo "</ul></p>";
		} else {
		
			$sql = "delete from " . VTM_TABLE_PREFIX . "SOURCE_BOOK where ID = %d;";
			
			$result = $wpdb->get_results($wpdb->prepare($sql, $selectedID));
		
			/* print_r($result); */
			echo "<p style='color:green'>Deleted book $selectedID</p>";
		}
	}
	
 	function add_book($bookname, $bookcode, $visible) {
		global $wpdb;
		
		$wpdb->show_errors();
		
		$dataarray = array(
						'NAME' => $bookname,
						'CODE' => $bookcode,
						'VISIBLE' => $visible
					);
		
		/* print_r($dataarray); */
		
		$wpdb->insert(VTM_TABLE_PREFIX . "SOURCE_BOOK",
					$dataarray,
					array (
						'%s',
						'%s',
						'%s'
					)
				);
		
		$bookname = vtm_formatOutput($bookname);
		if ($wpdb->insert_id == 0) {
			echo "<p style='color:red'><b>Error:</b> $bookname could not be inserted (";
			$wpdb->print_error();
			echo ")</p>";
		} else {
			echo "<p style='color:green'>Added book '$bookname' (ID: {$wpdb->insert_id})</p>";
		}
	}
 	function edit_book($bookid, $bookname, $bookcode, $visible) {
		global $wpdb;
		
		$wpdb->show_errors();
		
		$dataarray = array(
						'NAME' => $bookname,
						'CODE' => $bookcode,
						'VISIBLE' => $visible
					);
		
		/* print_r($dataarray); */
		
		$result = $wpdb->update(VTM_TABLE_PREFIX . "SOURCE_BOOK",
					$dataarray,
					array (
						'ID' => $bookid
					)
				);
		
		$bookname = vtm_formatOutput($bookname);
		if ($result) 
			echo "<p style='color:green'>Updated $bookname</p>";
		else if ($result === 0) 
			echo "<p style='color:orange'>No updates made to $bookname</p>";
		else {
			$wpdb->print_error();
			echo "<p style='color:red'>Could not update $bookname ($bookid)</p>";
		}
	}
   
    function column_default($item, $column_name){
        switch($column_name){
            case 'CODE':
                return vtm_formatOutput($item->$column_name);
           default:
                return print_r($item,true); 
        }
    }
 
    function column_name($item){
        
        $actions = array(
            'edit'      => sprintf('<a href="?page=%s&amp;action=%s&amp;book=%s&amp;tab=%s">Edit</a>',$_REQUEST['page'],'edit',$item->ID, $this->type),
            'delete'    => sprintf('<a href="?page=%s&amp;action=%s&amp;book=%s&amp;tab=%s">Delete</a>',$_REQUEST['page'],'delete',$item->ID, $this->type),
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
            'CODE'         => 'Book Code',
            'VISIBLE'      => 'Visible to Players'
        );
        return $columns;
		
    }
    
    function get_sortable_columns() {
        $sortable_columns = array(
            'NAME'        => array('NAME',true)
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
        		
        if( 'delete'===$this->current_action() && $_REQUEST['tab'] == $this->type && isset($_REQUEST['book'])) {
			if ('string' == gettype($_REQUEST['book'])) {
				$this->delete_book($_REQUEST['book']);
			} else {
				foreach ($_REQUEST['book'] as $book) {
					$this->delete_book($book);
				}
			}
        }
     }

        
    function prepare_items() {
        global $wpdb; 
        
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
		
		$type = "book";
        			
		$this->_column_headers = array($columns, $hidden, $sortable);
        
		$this->type = $type;
        
        $this->process_bulk_action();
		
		/* Get the data from the database */
		$sql = "select books.ID, books.NAME, books.CODE, books.VISIBLE
				from " . VTM_TABLE_PREFIX . "SOURCE_BOOK books";
				
		/* order the data according to sort columns */
		if (!empty($_REQUEST['orderby']) && !empty($_REQUEST['order']))
			$sql .= " ORDER BY books.{$_REQUEST['orderby']} {$_REQUEST['order']}";
		
		$sql .= ";";
		
		/* echo "<p>SQL: $sql</p>"; */
		
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