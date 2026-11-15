<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require_once VTM_CHARACTER_URL . 'inc/classes.php';

class vtmclass_report_flaws extends vtmclass_Report_ListTable {
	protected $filter_player_status;
	protected $active_filter_player_status;
	protected $active_filter_merit_or_flaw;
	protected $filter_character_status;
	protected $active_filter_character_status;
	protected $active_filter_character_type;
	protected $active_filter_character_visible;
	protected $filter_character_type;

    function column_default($item, $column_name){
        switch($column_name){
            case 'PLAYERNAME':
                return esc_html($item->$column_name);
            case 'CHARACTERNAME':
                return esc_html($item->$column_name);
            case 'MERIT':
                return esc_html($item->$column_name);
            case 'LEVEL':
                return $item->$column_name;
            case 'COMMENT':
                return esc_html($item->$column_name);
            case 'DETAIL':
                return wpautop(esc_html($item->$column_name));
            case 'SOURCEBOOK':
                return esc_html($item->$column_name);
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

		$this->_column_headers = array($columns, $hidden, $sortable);
        $this->process_bulk_action();
		
		/* run query */
		
		/* echo "<p>SQL: $sql</p>"; */
		$data =$wpdb->get_results($wpdb->prepare("
				SELECT players.NAME as PLAYERNAME, 
					characters.NAME as CHARACTERNAME, 
					players.PLAYER_STATUS_ID,
					characters.CHARACTER_TYPE_ID,
					characters.CHARACTER_STATUS_ID,
					characters.VISIBLE,
					merits.NAME as MERIT, 
					charmerit.LEVEL, 
					charmerit.COMMENT,
					charmerit.APPROVED_DETAIL as DETAIL, 
					IF(charmerit.LEVEL >= 0,'merit','flaw') as MERITFLAW,
					CONCAT(sourcebooks.NAME, \", p\" , merits.PAGE_NUMBER) as SOURCEBOOK
				FROM
					%i players,
					%i characters,
					%i merits,
					%i charmerit,
					%i sourcebooks
				WHERE
					players.ID = characters.PLAYER_ID
					AND characters.ID = charmerit.CHARACTER_ID
					AND merits.ID = charmerit.MERIT_ID
					AND sourcebooks.ID = merits.SOURCE_BOOK_ID
					AND characters.DELETED = 'N'",
						VTM_TABLE_PREFIX . 'PLAYER',
						VTM_TABLE_PREFIX . 'CHARACTER',
						VTM_TABLE_PREFIX . 'MERIT',
						VTM_TABLE_PREFIX . 'CHARACTER_MERIT',
						VTM_TABLE_PREFIX . 'SOURCE_BOOK'));

		/* Merit or Flaw */
		if ( isset( $_REQUEST['merit_or_flaw'] )) {
			$this->active_filter_merit_or_flaw = sanitize_key(wp_unslash($_REQUEST['merit_or_flaw']) );
		} else {
			$this->active_filter_merit_or_flaw = 'all';
		}
		
		if ($this->active_filter_merit_or_flaw != 'all')
			$data = wp_list_filter($data, array("MERITFLAW" => $this->active_filter_merit_or_flaw));
					
		$data = $this->apply_filters($data);
		if (!empty($_REQUEST['orderby']) && !empty($_REQUEST['order']))
			$data = wp_list_sort($data, $_REQUEST['orderby'], $_REQUEST['order']);

		$total_items = count($data);
		$perPage = isset($_REQUEST["format"]) ? $total_items: $this::PER_PAGE;
        $currentPage = $this->get_pagenum();

		$this->set_pagination_args(array(
			'total_items' => $total_items,
			'per_page' => $perPage
		));
		$data = array_slice($data,(($currentPage-1)*$perPage),$perPage);

        $this->items = $data;
		
	}
}

class vtmclass_report_quotes extends vtmclass_Report_ListTable {
	protected $filter_player_status;
	protected $filter_character_type;
	protected $filter_character_status;
	protected $active_filter_player_status;
	protected $active_filter_character_type;
	protected $active_filter_character_status;
	protected $active_filter_character_visible;

    function column_default($item, $column_name){
        switch($column_name){
            case 'PLAYERNAME':
                return esc_html($item->$column_name);
            case 'CHARACTERNAME':
                return esc_html($item->$column_name);
            case 'CLAN':
                return esc_html($item->$column_name);
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

		$this->_column_headers = array($columns, $hidden, $sortable);
        $this->process_bulk_action();
		
		/* run query */
		$data =$wpdb->get_results($wpdb->prepare("SELECT players.NAME as PLAYERNAME, 
					characters.NAME as CHARACTERNAME, 
					profiles.QUOTE, 
					clans.NAME as CLAN,
					players.PLAYER_STATUS_ID,
					characters.CHARACTER_TYPE_ID,
					characters.CHARACTER_STATUS_ID,
					characters.VISIBLE
				FROM
					%i players,
					%i characters,
					%i profiles,
					%i clans
				WHERE
					players.ID = characters.PLAYER_ID
					AND characters.ID = profiles.CHARACTER_ID
					AND clans.ID = characters.PUBLIC_CLAN_ID
					AND characters.DELETED = 'N'",
				VTM_TABLE_PREFIX . 'PLAYER',
			VTM_TABLE_PREFIX . 'CHARACTER',
			VTM_TABLE_PREFIX . 'CHARACTER_PROFILE',
			VTM_TABLE_PREFIX . 'CLAN'));
 		
 		$data = $this->apply_filters($data);
		if (!empty($_REQUEST['orderby']) && !empty($_REQUEST['order']))
			$data = wp_list_sort($data, $_REQUEST['orderby'], $_REQUEST['order']);

		$total_items = count($data);
		$perPage = isset($_REQUEST["format"]) ? $total_items: $this::PER_PAGE;
        $currentPage = $this->get_pagenum();

		$this->set_pagination_args(array(
			'total_items' => $total_items,
			'per_page' => $perPage
		));
		$data = array_slice($data,(($currentPage-1)*$perPage),$perPage);

        $this->items = $data;
	}
}


class vtmclass_report_prestige extends vtmclass_Report_ListTable {
	protected $filter_player_status;
	protected $filter_character_type;
	protected $filter_character_status;
	protected $active_filter_player_status;
	protected $active_filter_character_type;
	protected $active_filter_character_status;
	protected $active_filter_character_visible;
	protected $active_filter_clan;

    function column_default($item, $column_name){
        switch($column_name){
            case 'PLAYERNAME':
                return esc_html($item->$column_name);
            case 'CHARACTERNAME':
                return esc_html($item->$column_name);
            case 'PUBLIC_CLAN':
                return esc_html($item->$column_name);
            case 'PRIVATE_CLAN':
                return esc_html($item->$column_name);
            case 'COMMENT':
                return esc_html($item->$column_name);
            case 'LEVEL':
                return isset($item->$column_name) ? $item->$column_name : '';
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
				echo '<option value="' . esc_html($clan->ID) . '" ';
				selected( $this->active_filter_clan, $clan->ID );
				echo '>' . esc_html($clan->NAME) , '</option>';
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

		$this->_column_headers = array($columns, $hidden, $sortable);
        $this->process_bulk_action();
		
		/* run query */
		$data =$wpdb->get_results($wpdb->prepare("SELECT characters.NAME as CHARACTERNAME, 
					players.NAME as PLAYERNAME, 
					pubclans.NAME as PUBLIC_CLAN, 
					privclans.NAME as PRIVATE_CLAN, 
					TBGRND.LEVEL, TBGRND.COMMENT,
					characters.PUBLIC_CLAN_ID,
					characters.PRIVATE_CLAN_ID,
					players.PLAYER_STATUS_ID,
					characters.CHARACTER_TYPE_ID,
					characters.CHARACTER_STATUS_ID,
					characters.VISIBLE
				FROM 
					%i players, 
					%i pubclans, 
					%i privclans, 
					%i cgstatus,
					%i characters
					LEFT JOIN 
						(SELECT charbgs.CHARACTER_ID as CHARACTER_ID, charbgs.LEVEL as LEVEL, charbgs.COMMENT as COMMENT
						FROM
							%i players, 
							%i charbgs,
							%i backgrounds,
							%i pubclans, 
							%i privclans, 
							%i cgstatus,
							%i characters
						WHERE
							charbgs.BACKGROUND_ID = backgrounds.ID
							AND players.ID = characters.PLAYER_ID
							AND characters.ID = charbgs.CHARACTER_ID
							AND characters.PUBLIC_CLAN_ID = pubclans.ID
							AND characters.PRIVATE_CLAN_ID = privclans.ID
							AND characters.CHARGEN_STATUS_ID = cgstatus.ID
							AND cgstatus.NAME = 'Approved'
							AND backgrounds.NAME = 'Clan Prestige'
							AND characters.DELETED = 'N') as TBGRND 
					ON 
						TBGRND.CHARACTER_ID = characters.ID 
				WHERE 
					players.ID = characters.PLAYER_ID
					AND characters.PUBLIC_CLAN_ID = pubclans.ID
					AND characters.PRIVATE_CLAN_ID = privclans.ID
					AND characters.CHARGEN_STATUS_ID = cgstatus.ID
					AND cgstatus.NAME = 'Approved'
					AND characters.DELETED = 'N'",
			VTM_TABLE_PREFIX . 'PLAYER',
			VTM_TABLE_PREFIX . 'CLAN',
			VTM_TABLE_PREFIX . 'CLAN',
			VTM_TABLE_PREFIX . 'CHARGEN_STATUS',
			VTM_TABLE_PREFIX . 'CHARACTER',
			VTM_TABLE_PREFIX . 'PLAYER',
			VTM_TABLE_PREFIX . 'CHARACTER_BACKGROUND',
			VTM_TABLE_PREFIX . 'BACKGROUND',
			VTM_TABLE_PREFIX . 'CLAN',
			VTM_TABLE_PREFIX . 'CLAN',
			VTM_TABLE_PREFIX . 'CHARGEN_STATUS',
			VTM_TABLE_PREFIX . 'CHARACTER'
			));

		$data = $this->apply_filters($data);

 		/* Clan */
		if ( isset( $_REQUEST['clanfilter'] )) {
			$this->active_filter_clan = sanitize_key(wp_unslash($_REQUEST['clanfilter']) );
		} else {
			$this->active_filter_clan = 'all';
		}
		if (isset($this->active_filter_clan) && $this->active_filter_clan != 'all')
			$data = wp_list_filter($data, array("PUBLIC_CLAN_ID" => $this->active_filter_clan, "PRIVATE_CLAN_ID" => $this->active_filter_clan), "OR");

        $current_page = $this->get_pagenum();
        $total_items = count($data);

 		$data = $this->apply_filters($data);
		if (!empty($_REQUEST['orderby']) && !empty($_REQUEST['order']))
			$data = wp_list_sort($data, $_REQUEST['orderby'], $_REQUEST['order']);
      
		$total_items = count($data);
		$perPage = isset($_REQUEST["format"]) ? $total_items: $this::PER_PAGE;
        $currentPage = $this->get_pagenum();

		$this->set_pagination_args(array(
			'total_items' => $total_items,
			'per_page' => $perPage
		));
		$data = array_slice($data,(($currentPage-1)*$perPage),$perPage);

		$this->items = $data;

	}
}


class vtmclass_report_signin extends vtmclass_Report_ListTable {
	protected $totalqs;

    function column_default($item, $column_name){
        switch($column_name){
            case 'PLAYERNAME':
                return esc_html($item->$column_name);
            case 'CHARACTERNAME':
                return esc_html($item->$column_name);
            case 'BACKGROUND':
                return esc_html($item->$column_name);
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
				
		$this->totalqs = $wpdb->get_var($wpdb->prepare("SELECT COUNT(ID) as total2do FROM %i WHERE VISIBLE = 'Y'", VTM_TABLE_PREFIX . "EXTENDED_BACKGROUND"));

		$this->_column_headers = array($columns, $hidden, $sortable);
        $this->process_bulk_action();
		
		/* run query */
		$data = $wpdb->get_results($wpdb->prepare("SELECT characters.NAME as CHARACTERNAME, 
					players.NAME as PLAYERNAME, \"\" as SIGNATURE,
					CONCAT(FORMAT((IFNULL(SUM(bginfo.TOTALDONE),0) + IFNULL(SUM(mfinfo.TOTALDONE),0) + IFNULL(SUM(qinfo.TOTALDONE),0)) * 100 /
					(IFNULL(SUM(bginfo.TOTAL2DO),0) + IFNULL(SUM(mfinfo.TOTAL2DO),0) + IFNULL(SUM(totalqs.TOTAL2DO),0)),0),'%%') as BACKGROUND,
					players.PLAYER_STATUS_ID,
					characters.CHARACTER_TYPE_ID,
					characters.CHARACTER_STATUS_ID,
					characters.VISIBLE
				FROM 
					%i players, 
					%i cgstatus,
					%i characters
					LEFT JOIN (
						SELECT charbgs.CHARACTER_ID, 
							COUNT(backgrounds.BACKGROUND_QUESTION) AS TOTAL2DO, 
							COUNT(charbgs.APPROVED_DETAIL) AS TOTALDONE
						FROM
							%i backgrounds,
							%i charbgs
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
							%i merits,
							%i charmerits
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
							%i as charquest,
							%i as questions
						WHERE
							charquest.QUESTION_ID = questions.ID
							AND questions.VISIBLE = 'Y'
							AND charquest.APPROVED_DETAIL != ''
						GROUP BY charquest.CHARACTER_ID
					) as qinfo
					ON
						characters.ID = qinfo.CHARACTER_ID,
					(SELECT COUNT(ID) as total2do FROM %i WHERE VISIBLE = 'Y')
					as totalqs
				WHERE 
					players.ID = characters.PLAYER_ID
					AND cgstatus.ID = characters.CHARGEN_STATUS_ID
					AND characters.DELETED = 'N'
					AND cgstatus.NAME = 'Approved'
				 GROUP BY characters.ID",
						VTM_TABLE_PREFIX . 'PLAYER',
			VTM_TABLE_PREFIX . 'CHARGEN_STATUS',
			VTM_TABLE_PREFIX . 'CHARACTER',
			VTM_TABLE_PREFIX . 'BACKGROUND',
			VTM_TABLE_PREFIX . 'CHARACTER_BACKGROUND',
			VTM_TABLE_PREFIX . 'MERIT',
			VTM_TABLE_PREFIX . 'CHARACTER_MERIT',
			VTM_TABLE_PREFIX . 'CHARACTER_EXTENDED_BACKGROUND',
			VTM_TABLE_PREFIX . 'EXTENDED_BACKGROUND',
			VTM_TABLE_PREFIX . 'EXTENDED_BACKGROUND',
			));
		//print_r($data);
 		$data = $this->apply_filters($data);
		
        $current_page = $this->get_pagenum();
        $total_items = count($data);

		if (!empty($_REQUEST['orderby']) && !empty($_REQUEST['order']))
			$data = wp_list_sort($data, $_REQUEST['orderby'], $_REQUEST['order']);
		else
			$data = wp_list_sort($data, array("PLAYERNAME" => "ASC", "CHARACTERNAME" => "ASC"));

		$this->lineheight = 10;

		$total_items = count($data);
		$perPage = isset($_REQUEST["format"]) ? $total_items: $this::PER_PAGE;
        $currentPage = $this->get_pagenum();

		$this->set_pagination_args(array(
			'total_items' => $total_items,
			'per_page' => $perPage
		));
		$data = array_slice($data,(($currentPage-1)*$perPage),$perPage);

        $this->items = $data;

	}
}


class vtmclass_report_sect extends vtmclass_Report_ListTable {
	protected $filter_player_status;
	protected $filter_character_type;
	protected $filter_character_status;
	protected $active_filter_player_status;
	protected $active_filter_character_type;
	protected $active_filter_character_status;
	protected $active_filter_character_visible;
	protected $active_filter_selectsect;

    function column_default($item, $column_name){
        switch($column_name){
            case 'PLAYERNAME':
                return esc_html($item->$column_name);
            case 'CHARACTERNAME':
                return esc_html($item->$column_name);
            case 'SECT':
                return esc_html($item->$column_name);
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
				echo '<option value="' . esc_html($sect->ID) . '" ';
				echo selected( $this->active_filter_selectsect, $sect->ID );
				echo '>' . esc_html($sect->NAME) . '</option>';
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

		$this->_column_headers = array($columns, $hidden, $sortable);
        $this->process_bulk_action();
		
		/* run query */
		$data =$wpdb->get_results($wpdb->prepare("SELECT characters.NAME as CHARACTERNAME, 
					players.NAME as PLAYERNAME, 
					players.PLAYER_STATUS_ID,
					characters.CHARACTER_TYPE_ID,
					characters.CHARACTER_STATUS_ID,
					characters.VISIBLE,
					sects.NAME as SECT,
					characters.SECT_ID
				FROM 
					%i players, 
					%i characters,
					%i sects,
					%i cgstatus
				WHERE 
					players.ID = characters.PLAYER_ID
					AND sects.ID = characters.SECT_ID
					AND cgstatus.ID = characters.CHARGEN_STATUS_ID
					AND characters.DELETED = 'N'
					AND cgstatus.NAME = 'Approved'",
			VTM_TABLE_PREFIX . 'PLAYER',
			VTM_TABLE_PREFIX . 'CHARACTER',
			VTM_TABLE_PREFIX . 'SECT',
			VTM_TABLE_PREFIX . 'CHARGEN_STATUS',
					));

		/* Sect */
		if ( isset( $_REQUEST['selectsect'] )) {
			$this->active_filter_selectsect = sanitize_key(wp_unslash($_REQUEST['selectsect']) );
		} else {
			$this->active_filter_selectsect = 'all';
		}
		if (isset($this->active_filter_selectsect) && $this->active_filter_selectsect != 'all')
			$data = wp_list_filter($data, array("SECT_ID" => $this->active_filter_selectsect));
		$data = $this->apply_filters($data);
 		
		$total_items = count($data);
		$perPage = $this::PER_PAGE;
        $currentPage = $this->get_pagenum();

		$this->set_pagination_args(array(
			'total_items' => $total_items,
			'per_page' => $perPage
		));
		$data = array_slice($data,(($currentPage-1)*$perPage),$perPage);

        $this->items = $data;
	}
}

class vtmclass_report_activity extends vtmclass_Report_ListTable {
	protected $filter_player_status;
	protected $filter_character_type;
	protected $filter_character_status;
	protected $active_filter_player_status;
	protected $active_filter_character_type;
	protected $active_filter_character_status;
	protected $active_filter_character_visible;

    function column_default($item, $column_name){
        switch($column_name){
            case 'PLAYERNAME':
                return esc_html(stripslashes($item->$column_name));
            case 'CHARACTERNAME':
                return esc_html(stripslashes($item->$column_name));
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
		
		$this->_column_headers = array($columns, $hidden, $sortable);
        $this->process_bulk_action();
		
		/* run query */
		$data = $wpdb->get_results($wpdb->prepare("
				SELECT players.NAME as PLAYERNAME,
					players.PLAYER_STATUS_ID,
					characters.CHARACTER_TYPE_ID,
					characters.CHARACTER_STATUS_ID,
					characters.VISIBLE,
					characters.LAST_UPDATED,
					IF(cgstatus.NAME = 'Approved', characters.NAME, CONCAT(characters.NAME, ' (being generated)')) as CHARACTERNAME
				FROM 
					%i players, 
					%i characters,
					%i cgstatus
				WHERE 
					players.ID = characters.PLAYER_ID
					AND cgstatus.ID = characters.CHARGEN_STATUS_ID
					AND characters.DELETED = 'N'",
			VTM_TABLE_PREFIX . 'PLAYER',
			VTM_TABLE_PREFIX . 'CHARACTER',
			VTM_TABLE_PREFIX . 'CHARGEN_STATUS',
					));

		$data = $this->apply_filters($data);

		if (!empty($_REQUEST['orderby']) && !empty($_REQUEST['order']))
			$data = wp_list_sort($data, $_REQUEST['orderby'], $_REQUEST['order']);
		else
			$data = wp_list_sort($data, "LAST_UPDATED", "DESC");

		$total_items = count($data);
		$perPage = isset($_REQUEST["format"]) ? $total_items: $this::PER_PAGE;
        $currentPage = $this->get_pagenum();

		$this->set_pagination_args(array(
			'total_items' => $total_items,
			'per_page' => $perPage
		));
		$data = array_slice($data,(($currentPage-1)*$perPage),$perPage);

        $this->items = $data;
	}
}

class vtmclass_report_sector extends vtmclass_Report_ListTable {

    function column_default($item, $column_name){
        switch($column_name){
            case 'PLAYERNAME':
                return esc_html($item->$column_name);
            case 'CHARACTERNAME':
                return esc_html($item->$column_name);
            case 'BACKGROUND':
                return esc_html($item->$column_name);
            case 'SECTOR':
                return esc_html($item->$column_name);
            case 'COMMENT':
                return esc_html($item->$column_name);
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
				echo '<option value="' . esc_html($background->ID) . '" ';
				selected( $this->active_filter_background, $background->ID );
				echo '>' . esc_html($background->NAME) , '</option>';
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

		$this->_column_headers = array($columns, $hidden, $sortable);
        $this->process_bulk_action();
		
		/* run query */
		$data = $wpdb->get_results($wpdb->prepare("SELECT characters.NAME as CHARACTERNAME, players.NAME as PLAYERNAME,
					bg.NAME as BACKGROUND, sectors.NAME as SECTOR,
					cbg.COMMENT as COMMENT, 
					cbg.LEVEL as LEVEL,
					players.PLAYER_STATUS_ID,
					characters.CHARACTER_TYPE_ID,
					characters.CHARACTER_STATUS_ID,
					characters.VISIBLE,
					cbg.BACKGROUND_ID
				FROM
					%i characters,
					%i players,
					%i cbg
					LEFT JOIN (
						SELECT ID, NAME
						FROM %i
					) sectors
					ON sectors.ID = cbg.SECTOR_ID
					,
					%i bg
				WHERE
					characters.PLAYER_ID = players.ID
					AND cbg.CHARACTER_ID = characters.ID
					AND cbg.BACKGROUND_ID = bg.ID;",
			VTM_TABLE_PREFIX . 'CHARACTER',
			VTM_TABLE_PREFIX . 'PLAYER',
			VTM_TABLE_PREFIX . 'CHARACTER_BACKGROUND',
			VTM_TABLE_PREFIX . 'SECTOR',
			VTM_TABLE_PREFIX . 'BACKGROUND',
					));

		$data = $this->apply_filters($data);

		if ( isset( $_REQUEST['bgfilter'] )) {
			$this->active_filter_background = sanitize_key(wp_unslash($_REQUEST['bgfilter']) );
		} else {
			$this->active_filter_background = 'all';
		}
		if (isset($this->active_filter_background) && $this->active_filter_background != 'all')
			$data = wp_list_filter($data, array("BACKGROUND_ID" => $this->active_filter_background));

		if (!empty($_REQUEST['orderby']) && !empty($_REQUEST['order']))
			$data = wp_list_sort($data, $_REQUEST['orderby'], $_REQUEST['order']);
		else
			$data = wp_list_sort($data, "CHARACTERNAME", "ASC");

 		
		$total_items = count($data);
		$perPage = isset($_REQUEST["format"]) ? $total_items: $this::PER_PAGE;
        $currentPage = $this->get_pagenum();

		$this->set_pagination_args(array(
			'total_items' => $total_items,
			'per_page' => $perPage
		));
		$data = array_slice($data,(($currentPage-1)*$perPage),$perPage);

        $this->items = $data;

	}
}


// SELECT c.NAME, pxp.`AWARDED`, pxp.`XP_REASON_ID`, xpr.NAME 
// FROM `wp_vtm_PLAYER_XP`AS pxp, wp_vtm_CHARACTER as c, wp_vtm_XP_REASON as xpr 
// WHERE pxp.`CHARACTER_ID` = c.ID and xpr.ID = pxp.XP_REASON_id 
// 	and pxp.AWARDED >= DATE_SUB(NOW(),INTERVAL 1 YEAR) order by AWARDED DESC

class vtmclass_report_xp extends vtmclass_Report_ListTable {
	protected $filter_player_status;
	protected $filter_character_type;
	protected $filter_character_status;
	protected $active_filter_player_status;
	protected $active_filter_character_type;
	protected $active_filter_character_status;
	protected $active_filter_character_visible;

    function column_default($item, $column_name){
        switch($column_name){
            default:
                return esc_html($item->$column_name); 
        }
    }

    function get_columns(){
        $columns = array(
            'PLAYERNAME'    => 'Player',
            'CHARACTERNAME' => 'Character',
            'XP_REASON'     => 'XP Change Reason',
			'AWARDED'       => 'Date Awarded',
			'AMOUNT'        => 'Amount Awarded'
        );
        return $columns;
	}
		
   function get_sortable_columns() {
        $sortable_columns = array(
            'CHARACTERNAME' => array('CHARACTERNAME',false),
            'PLAYERNAME'    => array('PLAYERNAME',false),
            'XP_REASON'  => array('XP_REASON',false),
            'AWARDED'  => array('AWARDED',true)
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

		$this->_column_headers = array($columns, $hidden, $sortable);
        $this->process_bulk_action();
		
		/* run query */
		$data = $wpdb->get_results($wpdb->prepare("SELECT players.NAME as PLAYERNAME,
					IF(cgstatus.NAME = 'Approved', characters.NAME, CONCAT(characters.NAME, ' (being generated)')) as CHARACTERNAME,
					xpreason.NAME as XP_REASON,
					pxp.AWARDED as AWARDED,
					pxp.AMOUNT as AMOUNT,
					players.PLAYER_STATUS_ID,
					characters.CHARACTER_TYPE_ID,
					characters.CHARACTER_STATUS_ID,
					characters.VISIBLE
				FROM 
					%i players, 
					%i characters,
					%i cgstatus,
					%i pxp,
					%i xpreason
				WHERE 
					players.ID = characters.PLAYER_ID
					AND cgstatus.ID = characters.CHARGEN_STATUS_ID
					AND characters.DELETED = 'N' 
					AND pxp.CHARACTER_ID = characters.ID
					AND xpreason.ID = pxp.XP_REASON_ID
					AND pxp.AWARDED >= DATE_SUB(NOW(),INTERVAL 1 YEAR)",
			VTM_TABLE_PREFIX. "PLAYER",
			VTM_TABLE_PREFIX. "CHARACTER",
			VTM_TABLE_PREFIX. "CHARGEN_STATUS",
			VTM_TABLE_PREFIX. "PLAYER_XP",
			VTM_TABLE_PREFIX. "XP_REASON"
					));
 		
		$data = $this->apply_filters($data);

		if (!empty($_REQUEST['orderby']) && !empty($_REQUEST['order']))
			$data = wp_list_sort($data, $_REQUEST['orderby'], $_REQUEST['order']);
		else
			$data = wp_list_sort($data, "AWARDED", "ASC");

		$total_items = count($data);
		$perPage = isset($_REQUEST["format"]) ? $total_items: $this::PER_PAGE;
        $currentPage = $this->get_pagenum();

		$this->set_pagination_args(array(
			'total_items' => $total_items,
			'per_page' => $perPage
		));
		$data = array_slice($data,(($currentPage-1)*$perPage),$perPage);

        $this->items = $data;

	}
}

class vtmclass_report_consent extends vtmclass_Report_ListTable {
	protected $filter_player_status;
	protected $filter_character_type;
	protected $filter_character_status;
	protected $active_filter_player_status;
	protected $active_filter_character_type;
	protected $active_filter_character_status;
	protected $active_filter_character_visible;
	function column_default($item, $column_name) {
		$value = $item->$column_name ?? '';

		if (in_array($column_name, array('PVPOK', 'PVPMOREINFO', 'THEMEOK', 'CONSENTOK', 'BOUNDOK'), true)) {
			if ($value === 'Y') {
				return 'Yes';
			}
			if ($value === 'N') {
				return 'No';
			}
		}

		return wpautop(esc_html($value));
	}

	function get_columns() {
		return array(
			'PLAYERNAME'  => 'Player',
			'PVPOK'       => 'Player Character Conflict',
			'PVPMOREINFO' => 'Discuss Restrictions',
			'THEMEOK'     => 'Game Themes',
			'CONSENTOK'   => 'In-character Consequences',
			'BOUNDOK'     => 'Boundaries Acknowledged',
			'REDFLAG'     => 'Do Not Include',
			'ORANGEFLAG'  => 'Handle Carefully',
		);
	}

	function get_sortable_columns() {
		return array(
			'PLAYERNAME' => array('PLAYERNAME', false),
		);
	}

	function filter_tablenav() {
		echo "<label>Player Status: </label>";
		if (!empty($this->filter_player_status)) {
			echo "<select name='player_status'>";
			foreach ($this->filter_player_status as $key => $value) {
				echo '<option value="' . esc_attr($key) . '" ';
				selected($this->active_filter_player_status, $key);
				echo '>' . esc_html($value) . '</option>';
			}
			echo '</select>';
		}

		submit_button('Filter', 'secondary', 'do_filter_tablenav', false);
		echo "<label>Download: </label>";
		$current_url = set_url_scheme('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
		$current_url = add_query_arg('report', $_REQUEST['report'], $current_url);
		$pdf_url = wp_nonce_url(add_query_arg('format', 'pdf', $current_url), 'vtm_run_report');
		$csv_url = wp_nonce_url(add_query_arg('format', 'csv', $current_url), 'vtm_run_report');
		echo "<a class='button-primary' href='" . esc_url($pdf_url) . "'>PDF</a>";
		echo "<a class='button-primary' href='" . esc_url($csv_url) . "'>CSV</a>";
	}

	function prepare_items() {
		global $wpdb;

		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();

		$this->load_filters();

		$this->_column_headers = array($columns, $hidden, $sortable);
		$this->process_bulk_action();
		$data = $wpdb->get_results($wpdb->prepare("
				SELECT players.NAME AS PLAYERNAME,
					consent.PVPOK,
					consent.PVPMOREINFO,
					consent.THEMEOK,
					consent.CONSENTOK,
					consent.BOUNDOK,
					consent.REDFLAG,
					consent.ORANGEFLAG,
					players.PLAYER_STATUS_ID
				FROM %i consent
				INNER JOIN %i players ON players.ID = consent.PLAYER_ID", 
			VTM_TABLE_PREFIX . 'PLAYER_CONSENT',
			VTM_TABLE_PREFIX . 'PLAYER',
				));

		if ($this->active_filter_player_status != 'all')
			$data = wp_list_filter( $data, array("PLAYER_STATUS_ID" => $this->active_filter_player_status));

		if (!empty($_REQUEST['orderby']) && !empty($_REQUEST['order']))
			$data = wp_list_sort($data, $_REQUEST['orderby'], $_REQUEST['order']);
		else
			$data = wp_list_sort($data, "PLAYERNAME", "ASC");

		$total_items = count($data);
		$perPage = isset($_REQUEST["format"]) ? $total_items: $this::PER_PAGE;
        $currentPage = $this->get_pagenum();

		$this->set_pagination_args(array(
			'total_items' => $total_items,
			'per_page' => $perPage
		));
		$data = array_slice($data,(($currentPage-1)*$perPage),$perPage);

		$this->items = $data;

	}
}
?>
