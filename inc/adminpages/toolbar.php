<?php

function vtm_count_XP4approval() {
	global $wpdb;
	
	$sql = "SELECT COUNT(pxp.ID) as count
			FROM 
				" . VTM_TABLE_PREFIX . "PENDING_XP_SPEND pxp,
				" . VTM_TABLE_PREFIX . "CHARACTER ch,
				" . VTM_TABLE_PREFIX . "CHARGEN_STATUS cs
			WHERE
				pxp.CHARACTER_ID = ch.ID
				AND ch.CHARGEN_STATUS_ID = cs.ID
				AND cs.NAME = 'Approved'";
	$result = $wpdb->get_results($sql);
	
	return (count($result) > 0 ? $result[0]->count : 0);
}
function vtm_count_BG4approval() {
	global $wpdb;
	
	$count = 0;	
	$sql = "SELECT COUNT(cb.ID) as count
			FROM 
				" . VTM_TABLE_PREFIX . "CHARACTER_BACKGROUND cb,
				" . VTM_TABLE_PREFIX . "CHARACTER ch,
				" . VTM_TABLE_PREFIX . "CHARGEN_STATUS cs
			WHERE NOT(cb.PENDING_DETAIL = '') AND cb.DENIED_DETAIL = ''
				AND ch.CHARGEN_STATUS_ID = cs.ID
				AND ch.ID = cb.CHARACTER_ID
				AND cs.NAME = 'Approved'";
	$result = $wpdb->get_results($sql);
	$count += count($result) > 0 ? $result[0]->count : 0;
	//echo "<p>SQL: $sql</p>";
	//print_r($result);
	
	$sql = "SELECT COUNT(cm.ID) as count
			FROM 
				" . VTM_TABLE_PREFIX . "CHARACTER_MERIT cm,
				" . VTM_TABLE_PREFIX . "CHARACTER ch,
				" . VTM_TABLE_PREFIX . "CHARGEN_STATUS cs
			WHERE NOT(cm.PENDING_DETAIL = '') AND cm.DENIED_DETAIL = ''
				AND ch.ID = cm.CHARACTER_ID
				AND ch.CHARGEN_STATUS_ID = cs.ID
				AND cs.NAME = 'Approved'";
	$result = $wpdb->get_results($sql);
	$count += count($result) > 0 ? $result[0]->count : 0;
	
	$sql = "SELECT COUNT(cxb.ID) as count
			FROM 
				" . VTM_TABLE_PREFIX . "CHARACTER_EXTENDED_BACKGROUND cxb,
				" . VTM_TABLE_PREFIX . "CHARACTER ch,
				" . VTM_TABLE_PREFIX . "CHARGEN_STATUS cs
			WHERE NOT(cxb.PENDING_DETAIL = '') AND cxb.DENIED_DETAIL = ''
				AND ch.ID = cxb.CHARACTER_ID
				AND ch.CHARGEN_STATUS_ID = cs.ID
				AND cs.NAME = 'Approved'";
	$result = $wpdb->get_results($sql);
	$count += count($result) > 0 ? $result[0]->count : 0;
	
	return $count;
}
function vtm_count_CharGen4approval() {
	global $wpdb;
	
	$sql = "SELECT COUNT(ch.ID)
			FROM 
				" . VTM_TABLE_PREFIX . "CHARACTER ch,
				" . VTM_TABLE_PREFIX . "CHARGEN_STATUS cgs
			WHERE 
				cgs.NAME = 'Submitted'
				AND ch.DELETED = 'N'
				AND ch.CHARGEN_STATUS_ID = cgs.ID";
	//echo "<p>SQL: $sql</p>";
	return $wpdb->get_var($sql);
}

/* WORDPRESS TOOLBAR 
----------------------------------------------------------------- */
function vtm_toolbar_link_admin( $wp_admin_bar ) {

	if ( current_user_can( 'manage_options' ) )  {
		$args = array(
			'id'    => 'vtmcharacters',
			'title' => 'Characters',
			'href'  => admin_url('admin.php?page=character-plugin'),
			'meta'  => array( 'class' => 'vtm-toolbar-page' )
		);
		$wp_admin_bar->add_node( $args );
		
		$args = array(
			'id'    => 'vtmcharacters2',
			'title' => 'Character Admin',
			'href'  => admin_url('admin.php?page=character-plugin'),
			'parent' => 'vtmcharacters',
			'meta'  => array( 'class' => 'vtm-toolbar-page' )
		); 
		$wp_admin_bar->add_node( $args );
		
		$args = array(
			'id'    => 'vtmplayers',
			'title' => 'Player Admin',
			'href'  => admin_url('admin.php?page=vtmcharacter-player'),
			'parent' => 'vtmcharacters',
			'meta'  => array( 'class' => 'vtm-toolbar-page' )
		); 
		$wp_admin_bar->add_node( $args );
		
		$args = array(
			'id'    => 'vtmbg',
			'title' => 'Approve Backgrounds (' . vtm_count_BG4approval() . ')',
			'href'  => admin_url('admin.php?page=vtmcharacter-bg'),
			'parent' => 'vtmcharacters',
			'meta'  => array( 'class' => 'vtm-toolbar-page' )
		); 
		$wp_admin_bar->add_node( $args );
		
		$args = array(
			'id'    => 'vtmchargen',
			'title' => 'Approve Characters (' . vtm_count_CharGen4approval() . ')',
			'href'  => admin_url('admin.php?page=vtmcharacter-chargen'),
			'parent' => 'vtmcharacters',
			'meta'  => array( 'class' => 'vtm-toolbar-page' )
		); 
		$wp_admin_bar->add_node( $args );
		
		$args = array(
			'id'    => 'vtmspendxp',
			'title' => 'Approve Spends (' . vtm_count_XP4approval() . ')',
			'href'  => admin_url('admin.php?page=vtmcharacter-xp'),
			'parent' => 'vtmcharacters',
			'meta'  => array( 'class' => 'vtm-toolbar-page' )
		); 
		$wp_admin_bar->add_node( $args );
		
		$args = array(
			'id'    => 'vtmassignxp',
			'title' => 'Assign Experience',
			'href'  => admin_url('admin.php?page=vtmcharacter-xpassign'),
			'parent' => 'vtmcharacters',
			'meta'  => array( 'class' => 'vtm-toolbar-page' )
		); 
		$wp_admin_bar->add_node( $args );

		$args = array(
			'id'    => 'vtmdata',
			'title' => 'Data Tables',
			'href'  => admin_url('admin.php?page=vtmcharacter-data'),
			'parent' => 'vtmcharacters',
			'meta'  => array( 'class' => 'vtm-toolbar-page' )
		); 
		$wp_admin_bar->add_node( $args );

		if ( get_option( 'vtm_feature_reports', '0' ) == 1) {
			$args = array(
				'id'    => 'vtmreport',
				'title' => 'Reports',
				'href'  => admin_url('admin.php?page=vtmcharacter-report'),
				'parent' => 'vtmcharacters',
				'meta'  => array( 'class' => 'vtm-toolbar-page' )
			);
			$wp_admin_bar->add_node( $args );
		}
		
		$args = array(
			'id'    => 'vtmconfig',
			'title' => 'Configuration',
			'href'  => admin_url('admin.php?page=vtmcharacter-config'),
			'parent' => 'vtmcharacters',
			'meta'  => array( 'class' => 'vtm-toolbar-page' )
		);
		$wp_admin_bar->add_node( $args );
	}
}
add_action( 'admin_bar_menu', 'vtm_toolbar_link_admin', 999 );


?>