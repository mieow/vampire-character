<?php
require_once VTM_CHARACTER_URL . 'inc/classes.php';

class vtmclass_report_flaws extends vtmclass_Report_ListTable {

    function column_default($item, $column_name){
        switch($column_name){
            case 'PLAYERNAME':
                return vtm_formatOutput($item->$column_name);
            case 'CHARACTERNAME':
                return vtm_formatOutput($item->$column_name);
            case 'MERIT':
                return vtm_formatOutput($item->$column_name);
            case 'LEVEL':
                return $item->$column_name;
            case 'COMMENT':
                return vtm_formatOutput($item->$column_name);
            case 'DETAIL':
                return wpautop(vtm_formatOutput($item->$column_name));
            case 'SOURCEBOOK':
                return vtm_formatOutput($item->$column_name);
            case 'PAGE_NUMBER':
                return $item->$column_name;
           default:
                return print_r($item,true); 
        }
    }

    function get_columns(){
        $columns = array(
            'CHARACTERNAME'  => 'Character',
            'PLAYERNAME'   => 'Player',
            'MERIT'        => 'Merit or Flaw',
            'LEVEL'        => 'Level',
			'COMMENT'      => 'Comment',
			'DETAIL'       => 'Background Detail',
            'SOURCEBOOK'   => 'Source Book',
        );
        return $columns;
	}
		
   function get_sortable_columns() {
        $sortable_columns = array(
            'CHARACTERNAME' => array('CHARACTERNAME',true),
            'PLAYERNAME'    => array('PLAYERNAME',false),
            'MERIT'  	=> array('MERIT',false),
            'LEVEL'     => array('LEVEL',false)
        );
        return $sortable_columns;
    }

	function extra_tablenav($which) {
		if ($which == 'top')  {
			echo "<div class='gvfilter'>";
			
			echo "<span>Merit/Flaw: </span>";
			echo "<select name='merit_or_flaw'>";
			echo '<option value="all" ';
			selected( $this->active_filter_merit_or_flaw, 'all' );
			echo '>All</option>';
			echo '<option value="merit" ';
			selected( $this->active_filter_merit_or_flaw, 'merit');
			echo '>Merits</option>';
			echo '<option value="flaw" ';
			selected( $this->active_filter_merit_or_flaw, 'flaw' );
			echo '>Flaws</option>';
			echo '</select>';
			
			$this->filter_tablenav();
		
			echo "</div>";
		}
	}
	function set_column_alignment ($columns = "") {
	
		$colwidths = array(
            'CHARACTERNAME' => 'L',
            'PLAYERNAME'   => 'L',
            'MERIT'        => 'L',
            'LEVEL'        => 'C',
			'COMMENT'      => 'L',
			'DETAIL'       => 'L',
            'SOURCEBOOK'   => 'C',
			
		);

		return $colwidths;
	}
	function set_column_widths($columns = "") {
	
		/* total 297-10, avg width 42 */
		$colwidths = array(
            'CHARACTERNAME' => 33,
            'PLAYERNAME'   => 33,
            'MERIT'        => 50,
            'LEVEL'        => 10,
			'COMMENT'      => 45,
			'DETAIL'       => 80,
            'SOURCEBOOK'   => 35,
			
		);

		return $colwidths;
	}
	function prepare_items() {
        global $wpdb; 
        
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();

		/* filters */
		$this->load_filters();
		/* Merit or Flaw */
		if ( isset( $_REQUEST['merit_or_flaw'] )) {
			$this->active_filter_merit_or_flaw = sanitize_key( $_REQUEST['merit_or_flaw'] );
		} else {
			$this->active_filter_merit_or_flaw = 'all';
		}
		
		
		/* Character visibility */
		
		$sql = "SELECT players.NAME as PLAYERNAME, characters.NAME as CHARACTERNAME, merits.NAME as MERIT, charmerit.LEVEL, charmerit.COMMENT,
					charmerit.APPROVED_DETAIL as DETAIL, CONCAT(sourcebooks.NAME, \", p\" , merits.PAGE_NUMBER) as SOURCEBOOK
				FROM
					" . VTM_TABLE_PREFIX. "PLAYER players,
					" . VTM_TABLE_PREFIX. "CHARACTER characters,
					" . VTM_TABLE_PREFIX. "MERIT merits,
					" . VTM_TABLE_PREFIX. "CHARACTER_MERIT charmerit,
					" . VTM_TABLE_PREFIX. "SOURCE_BOOK sourcebooks
				WHERE
					players.ID = characters.PLAYER_ID
					AND characters.ID = charmerit.CHARACTER_ID
					AND merits.ID = charmerit.MERIT_ID
					AND sourcebooks.ID = merits.SOURCE_BOOK_ID
					AND characters.DELETED = 'N'";
		
		switch ($this->active_filter_merit_or_flaw) {
			case "merit":
				$sql .= " AND charmerit.LEVEL >= 0";
				break;
			case "flaw":
				$sql .= " AND charmerit.LEVEL < 0";
				break;
		}
		
		$filterinfo = $this->get_filter_sql();
		$sql .= $filterinfo[0];
		
		if (!empty($_REQUEST['orderby']) && !empty($_REQUEST['order']))
			$sql .= " ORDER BY {$_REQUEST['orderby']} {$_REQUEST['order']}";

			$this->_column_headers = array($columns, $hidden, $sortable);
        $this->process_bulk_action();
		
		/* run query */
		
		/* echo "<p>SQL: $sql</p>"; */
		$data =$wpdb->get_results($wpdb->prepare($sql,$filterinfo[1]));
 		
        $current_page = $this->get_pagenum();
        $total_items = count($data);

        
        $this->items = $data;
		
		//$this->output_report("Merits and Flaws Report");
		//$this->output_csv();
        
        $this->set_pagination_args( array(
            'total_items' => $total_items,                  
            'per_page'    => $total_items,                  
            'total_pages' => 1
        ) );
	}
}

class vtmclass_report_quotes extends vtmclass_Report_ListTable {

    function column_default($item, $column_name){
        switch($column_name){
            case 'PLAYERNAME':
                return vtm_formatOutput($item->$column_name);
            case 'CHARACTERNAME':
                return vtm_formatOutput($item->$column_name);
            case 'CLAN':
                return vtm_formatOutput($item->$column_name);
            case 'QUOTE':
                return wpautop(vtm_formatOutput($item->$column_name, 1));
            default:
                return print_r($item,true); 
        }
    }

    function get_columns(){
        $columns = array(
            'CHARACTERNAME' => 'Character',
            'PLAYERNAME'    => 'Player',
            'CLAN'          => 'Public Clan',
            'QUOTE'         => 'Profile Quote',
        );
        return $columns;
	}
		
   function get_sortable_columns() {
        $sortable_columns = array(
            'CHARACTERNAME' => array('CHARACTERNAME',true),
            'PLAYERNAME'    => array('PLAYERNAME',false),
        );
        return $sortable_columns;
    }

	function extra_tablenav($which) {
		if ($which == 'top')  {
			echo "<div class='gvfilter'>";
			
			$this->filter_tablenav();
		
			echo "</div>";
		}
	}
	function set_column_widths($columns = "") {
	
		/* total 297-10, avg width 42 */
		$colwidths = array(
            'CHARACTERNAME' => 33,
            'PLAYERNAME'    => 33,
            'CLAN'          => 20,
            'QUOTE'         => 200,
			
		);

		return $colwidths;
	}
	function prepare_items() {
        global $wpdb; 
        
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();

		/* filters */
		$this->load_filters();
				
		$sql = "SELECT players.NAME as PLAYERNAME, characters.NAME as CHARACTERNAME, profiles.QUOTE, clans.NAME as CLAN
				FROM
					" . VTM_TABLE_PREFIX. "PLAYER players,
					" . VTM_TABLE_PREFIX. "CHARACTER characters,
					" . VTM_TABLE_PREFIX. "CHARACTER_PROFILE profiles,
					" . VTM_TABLE_PREFIX. "CLAN clans
				WHERE
					players.ID = characters.PLAYER_ID
					AND characters.ID = profiles.CHARACTER_ID
					AND clans.ID = characters.PUBLIC_CLAN_ID
					AND characters.DELETED = 'N'";
		
		$filterinfo = $this->get_filter_sql();
		$sql .= $filterinfo[0];
		
		if (!empty($_REQUEST['orderby']) && !empty($_REQUEST['order']))
			$sql .= " ORDER BY {$_REQUEST['orderby']} {$_REQUEST['order']}";

			$this->_column_headers = array($columns, $hidden, $sortable);
        $this->process_bulk_action();
		
		/* run query */
		
		/* echo "<p>SQL: $sql</p>"; */
		$data =$wpdb->get_results($wpdb->prepare($sql,$filterinfo[1]));
 		
        $current_page = $this->get_pagenum();
        $total_items = count($data);

        
        $this->items = $data;
		
		//$this->output_report("Profile Quotes Report");
		//$this->output_csv();
        
        $this->set_pagination_args( array(
            'total_items' => $total_items,                  
            'per_page'    => $total_items,                  
            'total_pages' => 1
        ) );
	}
}


class vtmclass_report_prestige extends vtmclass_Report_ListTable {

    function column_default($item, $column_name){
        switch($column_name){
            case 'PLAYERNAME':
                return vtm_formatOutput($item->$column_name);
            case 'CHARACTERNAME':
                return vtm_formatOutput($item->$column_name);
            case 'PUBLIC_CLAN':
                return vtm_formatOutput($item->$column_name);
            case 'PRIVATE_CLAN':
                return vtm_formatOutput($item->$column_name);
            case 'COMMENT':
                return vtm_formatOutput($item->$column_name);
            case 'LEVEL':
                return $item->$column_name;
            default:
                return print_r($item,true); 
        }
    }

    function get_columns(){
        $columns = array(
            'CHARACTERNAME' => 'Character',
            'PLAYERNAME'    => 'Player',
            'PUBLIC_CLAN'   => 'Public Clan',
            'PRIVATE_CLAN'  => 'Actual Clan',
            'COMMENT'       => 'Prestige is for',
            'LEVEL'         => 'Clan Prestige Level',
        );
        return $columns;
	}
		
   function get_sortable_columns() {
        $sortable_columns = array(
            'CHARACTERNAME' => array('CHARACTERNAME',true),
            'PLAYERNAME'    => array('PLAYERNAME',false),
            'PUBLIC_CLAN'   => array('CLAN',false),
            'PRIVATE_CLAN'  => array('CLAN',false),
            'COMMENT'       => array('COMMENT',false),
        );
        return $sortable_columns;
    }

	function extra_tablenav($which) {	
		if ($which == 'top')  {
			echo "<div class='gvfilter'>";
			
			echo "<span>Clan: </span>";
			echo "<select name='clanfilter'>\n";
			echo '<option value="all">All</option>';
			foreach (vtm_get_clans() as $clan) {
				echo '<option value="' . $clan->ID . '" ';
				selected( $this->active_filter_clan, $clan->ID );
				echo '>' . $clan->NAME , '</option>';
			}
			echo '</select>';

			$this->filter_tablenav();
		
			echo "</div>";
		}
	}
	function prepare_items() {
        global $wpdb; 
        
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();

		/* filters */
		$this->load_filters();
		/* Clan */
		if ( isset( $_REQUEST['clanfilter'] )) {
			$this->active_filter_clan = sanitize_key( $_REQUEST['clanfilter'] );
		} else {
			$this->active_filter_clan = 'all';
		}
		
		$filterinfo = $this->get_filter_sql();
		$args = array();
		if (isset($this->active_filter_clan) && $this->active_filter_clan != 'all') {
			$clanfilter = " AND (characters.PUBLIC_CLAN_ID = %s OR characters.PRIVATE_CLAN_ID = %s)";
			$args = array($this->active_filter_clan, $this->active_filter_clan);
		} else {
			$clanfilter = "";
		}
		$args = array_merge($args, $filterinfo[1]);
		
		$subtable = "SELECT charbgs.CHARACTER_ID as CHARACTER_ID, charbgs.LEVEL as LEVEL, charbgs.COMMENT as COMMENT
						FROM
							" . VTM_TABLE_PREFIX. "PLAYER players, 
							" . VTM_TABLE_PREFIX. "CHARACTER_BACKGROUND charbgs,
							" . VTM_TABLE_PREFIX. "BACKGROUND backgrounds,
							" . VTM_TABLE_PREFIX. "CLAN pubclans, 
							" . VTM_TABLE_PREFIX. "CLAN privclans, 
							" . VTM_TABLE_PREFIX. "CHARGEN_STATUS cgstatus,
							" . VTM_TABLE_PREFIX. "CHARACTER characters
						WHERE
							charbgs.BACKGROUND_ID = backgrounds.ID
							AND players.ID = characters.PLAYER_ID
							AND characters.ID = charbgs.CHARACTER_ID
							AND characters.PUBLIC_CLAN_ID = pubclans.ID
							AND characters.PRIVATE_CLAN_ID = privclans.ID
							AND characters.CHARGEN_STATUS_ID = cgstatus.ID
							AND cgstatus.NAME = 'Approved'
							AND backgrounds.NAME = 'Clan Prestige'
							AND characters.DELETED = 'N'";
		$subtable .= $clanfilter;
		$subtable .= $filterinfo[0];
				
		$sql = "SELECT characters.NAME as CHARACTERNAME, players.NAME as PLAYERNAME, 
					pubclans.NAME as PUBLIC_CLAN, privclans.NAME as PRIVATE_CLAN, 
					TBGRND.LEVEL, TBGRND.COMMENT
				FROM 
					" . VTM_TABLE_PREFIX. "PLAYER players, 
					" . VTM_TABLE_PREFIX. "CLAN pubclans, 
					" . VTM_TABLE_PREFIX. "CLAN privclans, 
					" . VTM_TABLE_PREFIX. "CHARGEN_STATUS cgstatus,
					" . VTM_TABLE_PREFIX. "CHARACTER characters
					LEFT JOIN 
						($subtable) as TBGRND 
					ON 
						TBGRND.CHARACTER_ID = characters.ID 
				WHERE 
					players.ID = characters.PLAYER_ID
					AND characters.PUBLIC_CLAN_ID = pubclans.ID
					AND characters.PRIVATE_CLAN_ID = privclans.ID
					AND characters.CHARGEN_STATUS_ID = cgstatus.ID
					AND cgstatus.NAME = 'Approved'
					AND characters.DELETED = 'N'";
		$sql .= $clanfilter;
		$sql .= $filterinfo[0];
		

		if (!empty($_REQUEST['orderby']) && !empty($_REQUEST['order']))
			$sql .= " ORDER BY {$_REQUEST['orderby']} {$_REQUEST['order']}";

		$this->_column_headers = array($columns, $hidden, $sortable);
        $this->process_bulk_action();
		
		/* run query */

		$args = array_merge($args, $args);
		$sql = $wpdb->prepare($sql,$args);
		//echo "<pre>SQL: $sql (>";
		//print_r($args);
		//echo ")</pre>";
		$data =$wpdb->get_results($sql);
 		
        $current_page = $this->get_pagenum();
        $total_items = count($data);

        
        $this->items = $data;
		
		//$this->output_report("Clan Prestige Report");
		//$this->output_csv();
        
        $this->set_pagination_args( array(
            'total_items' => $total_items,                  
            'per_page'    => $total_items,                  
            'total_pages' => 1
        ) );
	}
}

class vtmclass_report_signin extends vtmclass_Report_ListTable {

    function column_default($item, $column_name){
        switch($column_name){
            case 'PLAYERNAME':
                return vtm_formatOutput($item->$column_name);
            case 'CHARACTERNAME':
                return vtm_formatOutput($item->$column_name);
            case 'BACKGROUND':
                return vtm_formatOutput($item->$column_name);
            case 'SIGNATURE':
                return $item->$column_name;
            default:
                return ''; 
        }
    }

    function get_columns(){
        $columns = array(
            'PLAYERNAME'    => 'Player',
            'CHARACTERNAME' => 'Character',
			'BACKGROUND'    => 'Background',
            'SIGNATURE'     => 'Signature',
        );
		$customcolstr = get_option('vtm_signin_columns','');
		if ($customcolstr != "") {
			$customcols = explode(",",$customcolstr);
			foreach ($customcols as $col) {
				$val = trim($col);
				if ($val != '') {
					$key = sanitize_key($col);
					$columns[$key] = $val;
				}
			}
			//print_r($columns);
		}
        return $columns;
	}
	
	function set_column_widths($columns = "") {
	
		$totalwidth = 200;
		$numcols = count($columns) + 1; // sig is double-width
		$width = $totalwidth / $numcols;
	
		/* total 297-10, avg width 42 */
		$colwidths = array();
		
		foreach ($columns as $key => $name) {
			if ($key == 'SIGNATURE')
				$colwidths[$key] = $width * 2;
			else
				$colwidths[$key] = $width;
		}

		//print_r($colwidths);
		return $colwidths;
	}		
	function set_column_alignment($columns = "") {
	
		foreach ($columns as $key => $name) {
			if ($key == 'BACKGROUND')
				$colwidths[$key] = 'C';
			else
				$colwidths[$key] = 'L';
		}

		return $colwidths;
	}		
   function get_sortable_columns() {
        $sortable_columns = array(
            'PLAYERNAME'    => array('PLAYERNAME',true),
            'CHARACTERNAME' => array('CHARACTERNAME',false)
        );
        return $sortable_columns;
    }
	
	/* function vtm_set_line_height() {
		return 10;
	} */

	function prepare_items() {
        global $wpdb; 
        
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();

		/* filters */
		$this->load_filters();
		
		$filterinfo = $this->get_filter_sql();
		
		$sql = "SELECT COUNT(ID) as total2do FROM " . VTM_TABLE_PREFIX . "EXTENDED_BACKGROUND WHERE VISIBLE = 'Y'";
		$this->totalqs = $wpdb->get_var($sql);
		
		$sql = "SELECT characters.NAME as CHARACTERNAME, players.NAME as PLAYERNAME, \"\" as SIGNATURE,
					CONCAT(FORMAT((IFNULL(SUM(bginfo.TOTALDONE),0) + IFNULL(SUM(mfinfo.TOTALDONE),0) + IFNULL(SUM(qinfo.TOTALDONE),0)) * 100 /
					(IFNULL(SUM(bginfo.TOTAL2DO),0) + IFNULL(SUM(mfinfo.TOTAL2DO),0) + IFNULL(SUM(totalqs.TOTAL2DO),0)),0),'%%') as BACKGROUND 
				FROM 
					" . VTM_TABLE_PREFIX. "PLAYER players, 
					" . VTM_TABLE_PREFIX. "CHARGEN_STATUS cgstatus,
					" . VTM_TABLE_PREFIX. "CHARACTER characters
					LEFT JOIN (
						SELECT charbgs.CHARACTER_ID, 
							COUNT(backgrounds.BACKGROUND_QUESTION) AS TOTAL2DO, 
							COUNT(charbgs.APPROVED_DETAIL) AS TOTALDONE
						FROM
							" . VTM_TABLE_PREFIX . "BACKGROUND backgrounds,
							" . VTM_TABLE_PREFIX . "CHARACTER_BACKGROUND charbgs
						WHERE
							backgrounds.ID = charbgs.BACKGROUND_ID
							and	(backgrounds.BACKGROUND_QUESTION != '' OR charbgs.SECTOR_ID > 0)
						GROUP BY charbgs.CHARACTER_ID
					) as bginfo
					ON
						characters.ID = bginfo.CHARACTER_ID
					LEFT JOIN (
						SELECT charmerits.CHARACTER_ID,
							COUNT(charmerits.APPROVED_DETAIL) as TOTALDONE, 
							COUNT(merits.BACKGROUND_QUESTION) as TOTAL2DO
						FROM
							" . VTM_TABLE_PREFIX . "MERIT merits,
							" . VTM_TABLE_PREFIX . "CHARACTER_MERIT charmerits
						WHERE
							merits.ID = charmerits.MERIT_ID
							AND	merits.BACKGROUND_QUESTION != ''
						GROUP BY charmerits.CHARACTER_ID
					) as mfinfo
					ON
						characters.ID = mfinfo.CHARACTER_ID
					LEFT JOIN (
						SELECT charquest.CHARACTER_ID,
							COUNT(questions.ID) AS TOTALDONE
						FROM
							" . VTM_TABLE_PREFIX . "CHARACTER_EXTENDED_BACKGROUND as charquest,
							" . VTM_TABLE_PREFIX . "EXTENDED_BACKGROUND as questions
						WHERE
							charquest.QUESTION_ID = questions.ID
							AND questions.VISIBLE = 'Y'
							AND charquest.APPROVED_DETAIL != ''
						GROUP BY charquest.CHARACTER_ID
					) as qinfo
					ON
						characters.ID = qinfo.CHARACTER_ID,
					(SELECT COUNT(ID) as total2do FROM " . VTM_TABLE_PREFIX . "EXTENDED_BACKGROUND WHERE VISIBLE = 'Y')
					as totalqs
				WHERE 
					players.ID = characters.PLAYER_ID
					AND cgstatus.ID = characters.CHARGEN_STATUS_ID
					AND characters.DELETED = 'N'
					AND cgstatus.NAME = 'Approved'";
		$sql .= $filterinfo[0];
		$sql .= " GROUP BY characters.ID";
		
		if (!empty($_REQUEST['orderby']) && !empty($_REQUEST['order']))
			$sql .= " ORDER BY {$_REQUEST['orderby']} {$_REQUEST['order']}";
		else
			$sql .= " ORDER BY PLAYERNAME, CHARACTERNAME";
		
		$this->_column_headers = array($columns, $hidden, $sortable);
        $this->process_bulk_action();
		
		/* run query */
		$sql = $wpdb->prepare($sql,$filterinfo[1]);
		//echo "<p>SQL: $sql (";
		//print_r($filterinfo[1]);
		//echo ")</p>";
		$data = $wpdb->get_results($sql);
		//print_r($data);
 		
        $current_page = $this->get_pagenum();
        $total_items = count($data);

        $this->items = $data;
		
		$this->lineheight = 10;
		//$this->output_report("Signin Sheet " . Date('F Y'), 'P');
		//$this->output_csv();
        
        $this->set_pagination_args( array(
            'total_items' => $total_items,                  
            'per_page'    => $total_items,                  
            'total_pages' => 1
        ) );
	}
}


class vtmclass_report_sect extends vtmclass_Report_ListTable {

    function column_default($item, $column_name){
        switch($column_name){
            case 'PLAYERNAME':
                return vtm_formatOutput($item->$column_name);
            case 'CHARACTERNAME':
                return vtm_formatOutput($item->$column_name);
            case 'SECT':
                return vtm_formatOutput($item->$column_name);
            default:
                return print_r($item,true); 
        }
    }

    function get_columns(){
        $columns = array(
            'PLAYERNAME'    => 'Player',
            'CHARACTERNAME' => 'Character',
            'SECT'     => 'Sect',
        );
        return $columns;
	}
		
   function get_sortable_columns() {
        $sortable_columns = array(
            'CHARACTERNAME' => array('CHARACTERNAME',true),
            'PLAYERNAME'    => array('PLAYERNAME',false),
            'SECT'          => array('SECT',false)
        );
        return $sortable_columns;
    }

	function extra_tablenav($which) {
		if ($which == 'top')  {
			echo "<div class='gvfilter'>";
			
			echo "<span>Sect: </span>";
			echo "<select name='selectsect'>";
			echo '<option value="all" ';
			selected( $this->active_filter_selectsect, 'all' );
			echo '>All</option>';
			foreach (vtm_get_sects() as $sect) {
				echo '<option value="' . $sect->ID . '" ';
				echo selected( $this->active_filter_selectsect, $sect->ID );
				echo '>' . vtm_formatOutput($sect->NAME) . '</option>';
			}
			echo '</select>';
			
			$this->filter_tablenav();
		
			echo "</div>";
		}
	}

	function prepare_items() {
        global $wpdb; 
        
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();

		/* filters */
		$this->load_filters();
		/* SEct */
		if ( isset( $_REQUEST['selectsect'] )) {
			$this->active_filter_selectsect = sanitize_key( $_REQUEST['selectsect'] );
		} else {
			$this->active_filter_selectsect = 'all';
		}
		
		$sql = "SELECT characters.NAME as CHARACTERNAME, players.NAME as PLAYERNAME, sects.NAME as SECT
				FROM 
					" . VTM_TABLE_PREFIX. "PLAYER players, 
					" . VTM_TABLE_PREFIX. "CHARACTER characters,
					" . VTM_TABLE_PREFIX. "SECT sects,
					" . VTM_TABLE_PREFIX. "CHARGEN_STATUS cgstatus
				WHERE 
					players.ID = characters.PLAYER_ID
					AND sects.ID = characters.SECT_ID
					AND cgstatus.ID = characters.CHARGEN_STATUS_ID
					AND characters.DELETED = 'N'
					AND cgstatus.NAME = 'Approved'";
					
		$filterinfo = $this->get_filter_sql();
		$args = array();
		if (isset($this->active_filter_selectsect) && $this->active_filter_selectsect != 'all') {
			$sectfilter = " AND sects.id = %d";
			$args = array($this->active_filter_selectsect);
		} else {
			$sectfilter = "";
		}
		$args = array_merge($args, $filterinfo[1]);
		
					
		$sql .= $sectfilter;
		$sql .= $filterinfo[0];
		
		if (!empty($_REQUEST['orderby']) && !empty($_REQUEST['order']))
			$sql .= " ORDER BY {$_REQUEST['orderby']} {$_REQUEST['order']}";

		$this->_column_headers = array($columns, $hidden, $sortable);
        $this->process_bulk_action();
		
		/* run query */
		$sql = $wpdb->prepare($sql,$args);
		/* echo "<p>SQL: $sql (";
		print_r($filterinfo[1]);
		echo ")</p>"; */
		$data =$wpdb->get_results($sql);
 		
        $current_page = $this->get_pagenum();
        $total_items = count($data);

        $this->items = $data;
		
		//$this->output_report("Character Sects List", 'P');
		//$this->output_csv();
        
        $this->set_pagination_args( array(
            'total_items' => $total_items,                  
            'per_page'    => $total_items,                  
            'total_pages' => 1
        ) );
	}
}

class vtmclass_report_activity extends vtmclass_Report_ListTable {

    function column_default($item, $column_name){
        switch($column_name){
            case 'PLAYERNAME':
                return vtm_formatOutput($item->$column_name);
            case 'CHARACTERNAME':
                return vtm_formatOutput($item->$column_name);
            case 'LAST_UPDATED':
                return $item->$column_name;
            default:
                return print_r($item,true); 
        }
    }

    function get_columns(){
        $columns = array(
            'PLAYERNAME'    => 'Player',
            'CHARACTERNAME' => 'Character',
            'LAST_UPDATED'  => 'Last character activity',
        );
        return $columns;
	}
		
   function get_sortable_columns() {
        $sortable_columns = array(
            'CHARACTERNAME' => array('CHARACTERNAME',false),
            'PLAYERNAME'    => array('PLAYERNAME',false),
            'LAST_UPDATED'  => array('LAST_UPDATED',true)
        );
        return $sortable_columns;
    }

	function prepare_items() {
        global $wpdb; 
        
        $columns  = $this->get_columns();
        $hidden   = array();
        $sortable = $this->get_sortable_columns();

		/* filters */
		$this->load_filters();

		
		$sql = "SELECT players.NAME as PLAYERNAME, 
					characters.LAST_UPDATED,
					IF(cgstatus.NAME = 'Approved', characters.NAME, CONCAT(characters.NAME, ' (being generated)')) as CHARACTERNAME
				FROM 
					" . VTM_TABLE_PREFIX. "PLAYER players, 
					" . VTM_TABLE_PREFIX. "CHARACTER characters,
					" . VTM_TABLE_PREFIX. "CHARGEN_STATUS cgstatus
				WHERE 
					players.ID = characters.PLAYER_ID
					AND cgstatus.ID = characters.CHARGEN_STATUS_ID
					AND characters.DELETED = 'N'";
					
		$filterinfo = $this->get_filter_sql();
		$args = array();
		$args = array_merge($args, $filterinfo[1]);
		
		$sql .= $filterinfo[0];
		
		if (!empty($_REQUEST['orderby']) && !empty($_REQUEST['order']))
			$sql .= " ORDER BY {$_REQUEST['orderby']} {$_REQUEST['order']}";
		else
			$sql .= " ORDER BY LAST_UPDATED ASC";

		$this->_column_headers = array($columns, $hidden, $sortable);
        $this->process_bulk_action();
		
		/* run query */
		$sql = $wpdb->prepare($sql,$args);
		//echo "<p>SQL: $sql (";
		//print_r($filterinfo[1]);
		//echo ")</p>";
		$data = $wpdb->get_results($sql);
 		
        $current_page = $this->get_pagenum();
        $total_items = count($data);

        $this->items = $data;
		
		//$this->output_report("Character Activity", 'P');
		//$this->output_csv();
        
        $this->set_pagination_args( array(
            'total_items' => $total_items,                  
            'per_page'    => $total_items,                  
            'total_pages' => 1
        ) );
	}
}

class vtmclass_report_sector extends vtmclass_Report_ListTable {

    function column_default($item, $column_name){
        switch($column_name){
            case 'PLAYERNAME':
                return vtm_formatOutput($item->$column_name);
            case 'CHARACTERNAME':
                return vtm_formatOutput($item->$column_name);
            case 'BACKGROUND':
                return vtm_formatOutput($item->$column_name);
            case 'SECTOR':
                return vtm_formatOutput($item->$column_name);
            case 'COMMENT':
                return vtm_formatOutput($item->$column_name);
            case 'LEVEL':
                return $item->$column_name;
           default:
                return print_r($item,true); 
        }
    }

    function get_columns(){
        $columns = array(
            'PLAYERNAME'    => 'Player',
            'CHARACTERNAME' => 'Character',
            'BACKGROUND'  => 'Background',
            'SECTOR'      => 'Sector',
            'COMMENT'     => 'Comment',
            'LEVEL'       => 'Level',
        );
        return $columns;
	}
		
   function get_sortable_columns() {
        $sortable_columns = array(
            'CHARACTERNAME' => array('CHARACTERNAME',true),
            'PLAYERNAME'    => array('PLAYERNAME',false),
            'BACKGROUND'  => array('BACKGROUND',false),
            'SECTOR'  => array('SECTOR',false)
        );
        return $sortable_columns;
    }
	function extra_tablenav($which) {	
		if ($which == 'top')  {
			echo "<div class='gvfilter'>";
			
			echo "<span>Background: </span>";
			echo "<select name='bgfilter'>\n";
			echo '<option value="all">All</option>';
			foreach (vtm_get_backgrounds() as $background) {
				echo '<option value="' . $background->ID . '" ';
				selected( $this->active_filter_background, $background->ID );
				echo '>' . vtm_formatOutput($background->NAME) , '</option>';
			}
			echo '</select>';

			$this->filter_tablenav();
		
			echo "</div>";
		}
	}

	function prepare_items() {
        global $wpdb; 
        
        $columns  = $this->get_columns();
        $hidden   = array();
        $sortable = $this->get_sortable_columns();

		/* filters */
		$this->load_filters();
		if ( isset( $_REQUEST['bgfilter'] )) {
			$this->active_filter_background = sanitize_key( $_REQUEST['bgfilter'] );
		} else {
			$this->active_filter_background = 'all';
		}

		$filterinfo = $this->get_filter_sql();
		$args = array();
		if (isset($this->active_filter_background) && $this->active_filter_background != 'all') {
			$bgfilter = " AND cbg.BACKGROUND_ID = %s";
			$args = array($this->active_filter_background);
		} else {
			$bgfilter = "";
		}
		$args = array_merge($args, $filterinfo[1]);
		
		$sql = "SELECT characters.NAME as CHARACTERNAME, players.NAME as PLAYERNAME,
					bg.NAME as BACKGROUND, sectors.NAME as SECTOR,
					cbg.COMMENT as COMMENT, cbg.LEVEL as LEVEL
				FROM
					" . VTM_TABLE_PREFIX . "CHARACTER characters,
					" . VTM_TABLE_PREFIX . "PLAYER players,
					" . VTM_TABLE_PREFIX . "CHARACTER_BACKGROUND cbg
					LEFT JOIN (
						SELECT ID, NAME
						FROM " . VTM_TABLE_PREFIX . "SECTOR
					) sectors
					ON sectors.ID = cbg.SECTOR_ID
					,
					" . VTM_TABLE_PREFIX . "BACKGROUND bg
				WHERE
					characters.PLAYER_ID = players.ID
					AND cbg.CHARACTER_ID = characters.ID
					AND cbg.BACKGROUND_ID = bg.ID
					$bgfilter ";
					
		$sql .= $filterinfo[0];
		
		if (!empty($_REQUEST['orderby']) && !empty($_REQUEST['order']))
			$sql .= " ORDER BY {$_REQUEST['orderby']} {$_REQUEST['order']}";
		else
			$sql .= " ORDER BY CHARACTERNAME ASC";

		$this->_column_headers = array($columns, $hidden, $sortable);
        $this->process_bulk_action();
		
		/* run query */
		$sql = $wpdb->prepare($sql,$args);
		//echo "<p>SQL: $sql (";
		//print_r($filterinfo[1]);
		//echo ")</p>";
		$data = $wpdb->get_results($sql);
 		
        $current_page = $this->get_pagenum();
        $total_items = count($data);

        $this->items = $data;
		
		//$this->output_report("Sector and Background", 'P');
		//$this->output_csv();
        
        $this->set_pagination_args( array(
            'total_items' => $total_items,                  
            'per_page'    => $total_items,                  
            'total_pages' => 1
        ) );
	}
}

?>