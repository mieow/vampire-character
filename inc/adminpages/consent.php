<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


function vtm_render_consent_page(){

    $testListTable['consent'] = new vtmclass_admin_consent_table();
	// $doaction = vtm_bgdata_input_validation();
	
 	// if ($doaction == "add-bgdata") {
	// 	$testListTable['bgdata']->add_background($_REQUEST['bgdata_name'], $_REQUEST['bgdata_desc'], $_REQUEST['bgdata_group'], 
	// 								$_REQUEST['bgdata_costmodel'], $_REQUEST['bgdata_visible'],
	// 								$_REQUEST['bgdata_hassector'], $_REQUEST['bgdata_question'],
	// 								$_REQUEST['bgdata_hasspec']);
	// }
	// if ($doaction == "save-bgdata") { 
	// 	$testListTable['bgdata']->edit_background($_REQUEST['bgdata_id'], $_REQUEST['bgdata_name'], $_REQUEST['bgdata_desc'], $_REQUEST['bgdata_group'], 
	// 								$_REQUEST['bgdata_costmodel'], $_REQUEST['bgdata_visible'],
	// 								$_REQUEST['bgdata_hassector'], $_REQUEST['bgdata_question'],
	// 								$_REQUEST['bgdata_hasspec']);
	// } 

	// vtm_render_bgdata_add_form($doaction);
	
	// $testListTable['bgdata']->prepare_items();
 	$current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
	$current_url = remove_query_arg( 'action', $current_url );
  ?>	

	<!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
	<form id="consent-filter" method="get" action='<?php print esc_url($current_url); ?>'>
		<input type="hidden" name="page" value="<?php print esc_html($_REQUEST['page']) ?>" />
		<input type="hidden" name="tab" value="consent" />
		<?php $testListTable['consent']->display() ?>
	</form>

    <?php
}


class vtmclass_admin_consent_table extends vtmclass_MultiPage_ListTable {

    function __construct(){
        global $status, $page;
                
        parent::__construct( array(
            'singular'  => 'item',     
            'plural'    => 'items',    
            'ajax'      => false        
        ) );
    }

    function add($name, $type, $status) {
		global $wpdb;
		
		$wpdb->show_errors();


    }

	function edit($id, $name, $type, $status) {
		global $wpdb;
		
		$wpdb->show_errors();

    }

    function column_default($item, $column_name){
        switch($column_name){
         default:
                return print_r($item,true); 
        }
    }

    function column_name($item){
        
        $actions = array(
            'edit'      => sprintf('<a href="?page=%s&amp;action=%s&amp;item=%s">Edit</a>',$_REQUEST['page'],'edit',$item->ID),
            'delete'    => sprintf('<a href="?page=%s&amp;action=%s&amp;item=%s">Delete</a>',$_REQUEST['page'],'delete',$item->ID),
       );
        
        
        return sprintf('%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
            esc_html($item->NAME),
            $item->ID,
            $this->row_actions($actions)
        );
    }

    function get_columns(){
        $columns = array(
            'cb'           => '<input type="checkbox" />', 
            'NAME'         => 'Name',
            'PLAYERTYPE'   => 'Player Type',
            'PLAYERSTATUS' => 'Player Status',
            'CHARACTERLIST' => 'Characters'
        );
        return $columns;
		
    }

    function prepare_items() {
        global $wpdb; 
 
    }
}
?>