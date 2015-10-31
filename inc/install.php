<?php

// NO TABS IN TABLE DECLARATIONS

register_activation_hook(__FILE__, "vtm_character_install");
register_activation_hook( __FILE__, 'vtm_character_install_data' );

global $vtm_character_version;
global $vtm_character_db_version;
$vtm_character_version = "2.3"; 
$vtm_character_db_version = "61"; 

function vtm_update_db_check() {
    global $vtm_character_version;
    global $vtm_character_db_version;
	global $wpdb;
	
    if (get_option( 'vtm_character_db_version' ) != $vtm_character_db_version ||
		get_option( 'vtm_character_version' ) != $vtm_character_version) {

		$text =  "<p>Vampire Character Manager updated from version " . get_option( 'vtm_character_version' ) . "." . get_option( 'vtm_character_db_version' );
		$text .= " to $vtm_character_version.$vtm_character_db_version</p>";
		vtm_add_admin_notice($text);
  
        $errors = vtm_character_update('before');
        vtm_character_install();
		vtm_character_install_data(VTM_CHARACTER_URL . "init");
        $errors += vtm_character_update('after');
		
		$count = $wpdb->get_var("SELECT COUNT(ID) FROM " . VTM_TABLE_PREFIX . "CHARACTER");
		if ($count == 0) {
			$text = "<p>Go to the Vampire Character Manager Configuration page to 
					load advanced initial data into the plugin database tables
					from an external website.</p>";
			vtm_add_admin_notice($text);
		}
				
		if (!$errors) {
			update_option( "vtm_character_version", $vtm_character_version );
			update_option( "vtm_character_db_version", $vtm_character_db_version );
		}
   }
}
add_action( 'plugins_loaded', 'vtm_update_db_check' );

function vtm_character_install() {
	global $wpdb;
	global $vtm_character_db_version;
		
	$table_prefix = VTM_TABLE_PREFIX;
	$installed_version = get_site_option( "vtm_character_db_version" );
	$lasterror = "";
		
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

	// LEVEL 1 TABLES - TABLES WITHOUT FOREIGN KEY CONSTRAINTS

	$current_table_name = $table_prefix . "PLAYER_TYPE";
	$sql = "CREATE TABLE " . $current_table_name . " (
				ID mediumint(9) NOT NULL AUTO_INCREMENT,
				NAME varchar(16) NOT NULL,
				DESCRIPTION tinytext NOT NULL,
				PRIMARY KEY  (ID)
				) ENGINE=INNODB;";
	$for_update = dbDelta($sql);
	vtm_save_install_errors($current_table_name, $lasterror, $for_update);

	$current_table_name = $table_prefix . "PLAYER_STATUS";
	$sql = "CREATE TABLE " . $current_table_name . " (
				ID mediumint(9) NOT NULL AUTO_INCREMENT,
				NAME varchar(16)  NOT NULL,
				DESCRIPTION tinytext     NOT NULL,
				PRIMARY KEY  (ID)
				) ENGINE=INNODB;";
	$for_update = dbDelta($sql);
	$for_update = vtm_save_install_errors($current_table_name, $lasterror, $for_update);

	$current_table_name = $table_prefix . "ST_LINK";
	$sql = "CREATE TABLE " . $current_table_name . " (
				ID mediumint(9) NOT NULL  AUTO_INCREMENT,
				VALUE varchar(32)  NOT NULL,
				DESCRIPTION tinytext     NOT NULL,
				LINK tinytext     NOT NULL,
				WP_PAGE_ID mediumint(9) NOT NULL,
				ORDERING smallint(3)  NOT NULL,
				PRIMARY KEY  (ID)
				) ENGINE=INNODB;";
	$for_update = dbDelta($sql);
	$for_update = vtm_save_install_errors($current_table_name, $lasterror, $for_update);

	$current_table_name = $table_prefix . "OFFICE";
	$sql = "CREATE TABLE " . $current_table_name . " (
				ID mediumint(9) NOT NULL  AUTO_INCREMENT,
				NAME varchar(32)  NOT NULL,
				DESCRIPTION tinytext     NOT NULL,
				ORDERING smallint(3)  NOT NULL,
				VISIBLE varchar(1)   NOT NULL,
				PRIMARY KEY  (ID)
				) ENGINE=INNODB;";
	$for_update = dbDelta($sql);
	vtm_save_install_errors($current_table_name, $lasterror, $for_update);

	$current_table_name = $table_prefix . "XP_REASON";
	$sql = "CREATE TABLE " . $current_table_name . " (
				ID mediumint(9) NOT NULL AUTO_INCREMENT,
				NAME varchar(16)  NOT NULL,
				DESCRIPTION tinytext     NOT NULL,
				PRIMARY KEY  (ID)
				) ENGINE=INNODB;";
	$for_update = dbDelta($sql);
	vtm_save_install_errors($current_table_name, $lasterror, $for_update);

	$current_table_name = $table_prefix . "PATH_REASON";
	$sql = "CREATE TABLE " . $current_table_name . " (
				ID mediumint(9) NOT NULL AUTO_INCREMENT,
				NAME varchar(24)  NOT NULL,
				DESCRIPTION tinytext     NOT NULL,
				PRIMARY KEY  (ID)
				) ENGINE=INNODB;";
	$for_update = dbDelta($sql);
	vtm_save_install_errors($current_table_name, $lasterror, $for_update);

	$current_table_name = $table_prefix . "TEMPORARY_STAT_REASON";
	$sql = "CREATE TABLE " . $current_table_name . " (
				ID mediumint(9) NOT NULL AUTO_INCREMENT,
				NAME varchar(16)  NOT NULL,
				DESCRIPTION tinytext     NOT NULL,
				PRIMARY KEY  (ID)
				) ENGINE=INNODB;";
	$for_update = dbDelta($sql);
	vtm_save_install_errors($current_table_name, $lasterror, $for_update);

	$current_table_name = $table_prefix . "CHARACTER_TYPE";
	$sql = "CREATE TABLE " . $current_table_name . " (
				ID mediumint(9) NOT NULL AUTO_INCREMENT,
				NAME varchar(16)  NOT NULL,
				DESCRIPTION tinytext     NOT NULL,
				PRIMARY KEY  (ID)
				) ENGINE=INNODB;";
	$for_update = dbDelta($sql);
	vtm_save_install_errors($current_table_name, $lasterror, $for_update);

	$current_table_name = $table_prefix . "CHARACTER_STATUS";
	$sql = "CREATE TABLE " . $current_table_name . " (
				ID mediumint(9) NOT NULL AUTO_INCREMENT,
				NAME varchar(16)  NOT NULL,
				DESCRIPTION tinytext     NOT NULL,
				PRIMARY KEY  (ID)
				) ENGINE=INNODB;";
	$for_update = dbDelta($sql);
	vtm_save_install_errors($current_table_name, $lasterror, $for_update);

	$current_table_name = $table_prefix . "COST_MODEL";
	$sql = "CREATE TABLE " . $current_table_name . " (
				ID mediumint(9) NOT NULL AUTO_INCREMENT,
				NAME varchar(16)  NOT NULL,
				DESCRIPTION tinytext     NOT NULL,
				PRIMARY KEY  (ID)
				) ENGINE=INNODB;";
	$for_update = dbDelta($sql);
	vtm_save_install_errors($current_table_name, $lasterror, $for_update);

	$current_table_name = $table_prefix . "DOMAIN";
	$sql = "CREATE TABLE " . $current_table_name . " (
				ID mediumint(9) NOT NULL AUTO_INCREMENT,
				NAME varchar(16)  NOT NULL,
				DESCRIPTION tinytext     NOT NULL,
				VISIBLE varchar(1)   NOT NULL,
				PRIMARY KEY  (ID)
				) ENGINE=INNODB;";
	$for_update = dbDelta($sql);
	vtm_save_install_errors($current_table_name, $lasterror, $for_update);

	$current_table_name = $table_prefix . "SECT";
	$sql = "CREATE TABLE " . $current_table_name . " (
				ID mediumint(9) NOT NULL AUTO_INCREMENT,
				NAME varchar(16)  NOT NULL,
				DESCRIPTION tinytext     NOT NULL,
				VISIBLE varchar(1)   NOT NULL,
				PRIMARY KEY  (ID)
				) ENGINE=INNODB;";
	$for_update = dbDelta($sql);
	vtm_save_install_errors($current_table_name, $lasterror, $for_update);
	
	$current_table_name = $table_prefix . "SOURCE_BOOK";
	$sql = "CREATE TABLE " . $current_table_name . " (
				ID mediumint(9)  NOT NULL   AUTO_INCREMENT,
				CODE varchar(16)   NOT NULL,
				NAME varchar(60)   NOT NULL,
				VISIBLE varchar(1)    NOT NULL,
				PRIMARY KEY  (ID)
				) ENGINE=INNODB;";
	$for_update = dbDelta($sql);
	vtm_save_install_errors($current_table_name, $lasterror, $for_update);
	
	$current_table_name = $table_prefix . "GENERATION";
	$sql = "CREATE TABLE " . $current_table_name . " (
				ID mediumint(9) NOT NULL AUTO_INCREMENT,
				NAME varchar(16)  NOT NULL,
				BLOODPOOL smallint(3)  NOT NULL,
				BLOOD_PER_ROUND smallint(2)  NOT NULL,
				MAX_RATING smallint(2)  NOT NULL,
				MAX_DISCIPLINE smallint(2)  NOT NULL,
				PRIMARY KEY  (ID)
				) ENGINE=INNODB;";
	$for_update = dbDelta($sql);
	vtm_save_install_errors($current_table_name, $lasterror, $for_update);

	$current_table_name = $table_prefix . "NATURE";
	$sql = "CREATE TABLE " . $current_table_name . " (
				ID mediumint(9) NOT NULL AUTO_INCREMENT,
				NAME varchar(16)  NOT NULL,
				DESCRIPTION tinytext     NOT NULL,
				PRIMARY KEY  (ID)
				) ENGINE=INNODB;";
	$for_update = dbDelta($sql);
	vtm_save_install_errors($current_table_name, $lasterror, $for_update);

	$current_table_name = $table_prefix . "TEMPORARY_STAT";
	$sql = "CREATE TABLE " . $current_table_name . " (
				ID mediumint(9)  NOT NULL  AUTO_INCREMENT,
				NAME varchar(60)   NOT NULL,
				DESCRIPTION tinytext      NOT NULL,
				VISIBLE varchar(1)    NOT NULL,
				PRIMARY KEY  (ID)
				) ENGINE=INNODB;";
	$for_update = dbDelta($sql);
	vtm_save_install_errors($current_table_name, $lasterror, $for_update);

	$current_table_name = $table_prefix . "SECTOR";
	$sql = "CREATE TABLE " . $current_table_name . " (
				ID mediumint(9)  NOT NULL  AUTO_INCREMENT,
				NAME varchar(16)   NOT NULL,
				DESCRIPTION tinytext      NOT NULL,
				VISIBLE varchar(1)    NOT NULL,
				PRIMARY KEY  (ID)
				) ENGINE=INNODB;";
	$for_update = dbDelta($sql);
	vtm_save_install_errors($current_table_name, $lasterror, $for_update);

	$current_table_name = $table_prefix . "EXTENDED_BACKGROUND";
	$sql = "CREATE TABLE " . $current_table_name . " (
				ID mediumint(9)  NOT NULL  AUTO_INCREMENT,
				ORDERING smallint(4)   NOT NULL,
				GROUPING varchar(90)   NOT NULL,
				TITLE varchar(90)   NOT NULL,
				BACKGROUND_QUESTION text           NOT NULL,
				VISIBLE varchar(1)    NOT NULL,
				REQD_AT_CHARGEN varchar(1)    NOT NULL,
				PRIMARY KEY  (ID)
				) ENGINE=INNODB;";
	$for_update = dbDelta($sql);
	vtm_save_install_errors($current_table_name, $lasterror, $for_update);
	
	$current_table_name = $table_prefix . "PROFILE_DISPLAY";
	$sql = "CREATE TABLE " . $current_table_name . " (
				ID mediumint(9) NOT NULL  AUTO_INCREMENT,
				NAME text NOT NULL,
				PRIMARY KEY  (ID)
				) ENGINE=INNODB;";
	$for_update = dbDelta($sql);
	vtm_save_install_errors($current_table_name, $lasterror, $for_update);

	$current_table_name = $table_prefix . "MAPOWNER";
	$sql = "CREATE TABLE " . $current_table_name . " (
				ID mediumint(9) NOT NULL   AUTO_INCREMENT,
				NAME varchar(60) NOT NULL,
				FILL_COLOUR varchar(7) NOT NULL,
				VISIBLE varchar(1) NOT NULL,
				PRIMARY KEY  (ID)
				) ENGINE=INNODB;";
	$for_update = dbDelta($sql);
	vtm_save_install_errors($current_table_name, $lasterror, $for_update);
	
	$current_table_name = $table_prefix . "CHARGEN_TEMPLATE";
	$sql = "CREATE TABLE " . $current_table_name . " (
				ID mediumint(9) NOT NULL   AUTO_INCREMENT,
				NAME varchar(60) NOT NULL,
				DESCRIPTION tinytext NOT NULL,
				VISIBLE varchar(1) NOT NULL,
				PRIMARY KEY  (ID)
				) ENGINE=INNODB;";
	$for_update = dbDelta($sql);
	vtm_save_install_errors($current_table_name, $lasterror, $for_update);
	
	$current_table_name = $table_prefix . "CHARGEN_STATUS";
	$sql = "CREATE TABLE " . $current_table_name . " (
				ID mediumint(9) NOT NULL   AUTO_INCREMENT,
				NAME varchar(60) NOT NULL,
				DESCRIPTION tinytext NOT NULL,
				PRIMARY KEY  (ID)
				) ENGINE=INNODB;";
	$for_update = dbDelta($sql);
	vtm_save_install_errors($current_table_name, $lasterror, $for_update);
	
	$current_table_name = $table_prefix . "SKILL_TYPE";
	$sql = "CREATE TABLE " . $current_table_name . " (
				ID mediumint(9) NOT NULL   AUTO_INCREMENT,
				NAME varchar(60) NOT NULL,
				PARENT_ID mediumint(9) NOT NULL,
				DESCRIPTION tinytext NOT NULL,
				ORDERING smallint(4) NOT NULL,
				PRIMARY KEY  (ID)
				) ENGINE=INNODB;";
	$for_update = dbDelta($sql);
	vtm_save_install_errors($current_table_name, $lasterror, $for_update);
	
	$current_table_name = $table_prefix . "MAIL_STATUS";
	$sql = "CREATE TABLE " . $current_table_name . " (
				ID mediumint(9) NOT NULL   AUTO_INCREMENT,
				NAME varchar(60) NOT NULL,
				DESCRIPTION tinytext NOT NULL,
				PRIMARY KEY  (ID)
				) ENGINE=INNODB;";
	$for_update = dbDelta($sql);
	vtm_save_install_errors($current_table_name, $lasterror, $for_update);

	$current_table_name = $table_prefix . "PM_TYPE";
	$sql = "CREATE TABLE " . $current_table_name . " (
				ID mediumint(9) NOT NULL   AUTO_INCREMENT,
				NAME varchar(60) NOT NULL,
				DESCRIPTION tinytext NOT NULL,
				ISANONYMOUS varchar(1) NOT NULL,
				PRIMARY KEY  (ID)
				) ENGINE=INNODB;";
	$for_update = dbDelta($sql);
	vtm_save_install_errors($current_table_name, $lasterror, $for_update);

	// LEVEL 2 TABLES - TABLES WITH A FOREIGN KEY CONSTRAINT TO A LEVEL 1 TABLE
	
	$current_table_name = $table_prefix . "CHARGEN_TEMPLATE_OPTIONS";
	$sql = "CREATE TABLE " . $current_table_name . " (
				ID mediumint(9)	NOT NULL   AUTO_INCREMENT,
				NAME varchar(60) NOT NULL,
				VALUE tinytext NOT NULL,
				TEMPLATE_ID mediumint(9) NOT NULL,
				PRIMARY KEY  (ID),
				CONSTRAINT `" . $table_prefix . "template_constraint_1` FOREIGN KEY (TEMPLATE_ID)   REFERENCES " . $table_prefix . "CHARGEN_TEMPLATE(ID)
				) ENGINE=INNODB;";
	$for_update = dbDelta($sql);
	vtm_save_install_errors($current_table_name, $lasterror, $for_update);

	$current_table_name = $table_prefix . "CHARGEN_TEMPLATE_DEFAULTS";
	$sql = "CREATE TABLE " . $current_table_name . " (
				ID mediumint(9)	NOT NULL   AUTO_INCREMENT,
				TEMPLATE_ID mediumint(9)	NOT NULL,
				CHARTABLE tinytext        NOT NULL,
				ITEMTABLE tinytext        NOT NULL,
				ITEMTABLE_ID mediumint(9)    NOT NULL,
				SECTOR_ID mediumint(9)    NOT NULL,
				SPECIALISATION varchar(64)	    NOT NULL,
				LEVEL mediumint(9)    NOT NULL,
				MULTIPLE varchar(1)		NOT NULL,
				PRIMARY KEY  (ID),
				CONSTRAINT `" . $table_prefix . "template_default_constraint_1` FOREIGN KEY (TEMPLATE_ID)   REFERENCES " . $table_prefix . "CHARGEN_TEMPLATE(ID)
				) ENGINE=INNODB;";
	$for_update = dbDelta($sql);
	vtm_save_install_errors($current_table_name, $lasterror, $for_update);

	$current_table_name = $table_prefix . "CHARGEN_TEMPLATE_MAXIMUM";
	$sql = "CREATE TABLE " . $current_table_name . " (
				ID mediumint(9)	NOT NULL   AUTO_INCREMENT,
				TEMPLATE_ID mediumint(9)	NOT NULL,
				ITEMTABLE tinytext        NOT NULL,
				ITEMTABLE_ID mediumint(9)    NOT NULL,
				LEVEL mediumint(9)    NOT NULL,
				PRIMARY KEY  (ID),
				CONSTRAINT `" . $table_prefix . "template_max_constraint_1` FOREIGN KEY (TEMPLATE_ID)   REFERENCES " . $table_prefix . "CHARGEN_TEMPLATE(ID)
				) ENGINE=INNODB;";
	$for_update = dbDelta($sql);
	vtm_save_install_errors($current_table_name, $lasterror, $for_update);

	$current_table_name = $table_prefix . "PLAYER";
	$sql = "CREATE TABLE " . $current_table_name . " (
				ID mediumint(9) NOT NULL AUTO_INCREMENT,
				NAME varchar(60)  NOT NULL,
				PLAYER_TYPE_ID mediumint(9) NOT NULL,
				PLAYER_STATUS_ID mediumint(9) NOT NULL,
				PRIMARY KEY  (ID),
				CONSTRAINT `" . $table_prefix . "player_constraint_1` FOREIGN KEY (PLAYER_TYPE_ID)   REFERENCES " . $table_prefix . "PLAYER_TYPE(ID),
				CONSTRAINT `" . $table_prefix . "player_constraint_2` FOREIGN KEY (PLAYER_STATUS_ID) REFERENCES " . $table_prefix . "PLAYER_STATUS(ID)
				) ENGINE=INNODB;";
	$for_update = dbDelta($sql);
	vtm_save_install_errors($current_table_name, $lasterror, $for_update);

	$current_table_name = $table_prefix . "COST_MODEL_STEP";
	$sql = "CREATE TABLE " . $current_table_name . " (
				ID mediumint(9) NOT NULL  AUTO_INCREMENT,
				COST_MODEL_ID mediumint(9) NOT NULL,
				SEQUENCE smallint(3)  NOT NULL,
				CURRENT_VALUE smallint(3)  NOT NULL,
				NEXT_VALUE smallint(3)  NOT NULL,
				FREEBIE_COST smallint(3)  NOT NULL,
				XP_COST smallint(3)  NOT NULL,
				PRIMARY KEY  (ID),
				CONSTRAINT `" . $table_prefix . "cost_model_step_constraint_1` FOREIGN KEY (COST_MODEL_ID) REFERENCES " . $table_prefix . "COST_MODEL(ID)
				) ENGINE=INNODB;";
	$for_update = dbDelta($sql);
	vtm_save_install_errors($current_table_name, $lasterror, $for_update);

	$current_table_name = $table_prefix . "CLAN";
	$sql = "CREATE TABLE " . $current_table_name . " (
				ID mediumint(9)  NOT NULL AUTO_INCREMENT,
				NAME varchar(30)   NOT NULL,
				DESCRIPTION tinytext      NOT NULL,
				ICON_LINK tinytext      NOT NULL,
				CLAN_PAGE_LINK tinytext      NOT NULL,
				CLAN_FLAW tinytext      NOT NULL,
				CLAN_COST_MODEL_ID mediumint(9) NOT NULL,
				NONCLAN_COST_MODEL_ID mediumint(9) NOT NULL,
				WORDPRESS_ROLE tinytext      NOT NULL,
				VISIBLE varchar(1)    NOT NULL,
				PRIMARY KEY  (ID),
				CONSTRAINT `" . $table_prefix . "clan_constraint_1` FOREIGN KEY (CLAN_COST_MODEL_ID)    REFERENCES " . $table_prefix . "COST_MODEL(ID),
				CONSTRAINT `" . $table_prefix . "clan_constraint_2` FOREIGN KEY (NONCLAN_COST_MODEL_ID) REFERENCES " . $table_prefix . "COST_MODEL(ID)
				) ENGINE=INNODB;";
	$for_update = dbDelta($sql);
	vtm_save_install_errors($current_table_name, $lasterror, $for_update);

	$current_table_name = $table_prefix . "STAT";
	$sql = "CREATE TABLE " . $current_table_name . " (
				ID mediumint(9)  NOT NULL  AUTO_INCREMENT,
				NAME varchar(16)   NOT NULL,
				DESCRIPTION tinytext      NOT NULL,
				GROUPING varchar(30)   NOT NULL,
				ORDERING smallint(3)   NOT NULL,
				COST_MODEL_ID mediumint(9)  NOT NULL,
				SPECIALISATION_AT smallint(2)	  NOT NULL,
				PRIMARY KEY  (ID),
				CONSTRAINT `" . $table_prefix . "stat_constraint_1` FOREIGN KEY (COST_MODEL_ID) REFERENCES " . $table_prefix . "COST_MODEL(ID)
				) ENGINE=INNODB;";
	$for_update = dbDelta($sql);
	vtm_save_install_errors($current_table_name, $lasterror, $for_update);

	$current_table_name = $table_prefix . "SKILL";
	$sql = "CREATE TABLE " . $current_table_name . " (
				ID mediumint(9)  NOT NULL  AUTO_INCREMENT,
				NAME varchar(30)   NOT NULL,
				DESCRIPTION tinytext      NOT NULL,
				COST_MODEL_ID mediumint(9)  NOT NULL,
				SKILL_TYPE_ID mediumint(9)  NOT NULL,
				MULTIPLE varchar(1)	  NOT NULL,
				SPECIALISATION_AT smallint(2)	  NOT NULL,
				VISIBLE varchar(1)    NOT NULL,
				PRIMARY KEY  (ID),
				CONSTRAINT `" . $table_prefix . "skill_constraint_1` FOREIGN KEY (COST_MODEL_ID) REFERENCES " . $table_prefix . "COST_MODEL(ID),
				CONSTRAINT `" . $table_prefix . "skill_constraint_2` FOREIGN KEY (SKILL_TYPE_ID) REFERENCES " . $table_prefix . "SKILL_TYPE(ID)
				) ENGINE=INNODB;";

	
	$for_update = dbDelta($sql);
	vtm_save_install_errors($current_table_name, $lasterror, $for_update);

	$current_table_name = $table_prefix . "BACKGROUND";
	$sql = "CREATE TABLE " . $current_table_name . " (
				ID mediumint(9)  NOT NULL  AUTO_INCREMENT,
				NAME varchar(30)   NOT NULL,
				DESCRIPTION tinytext      NOT NULL,
				GROUPING varchar(30)   NOT NULL,
				COST_MODEL_ID mediumint(9)  NOT NULL,
				HAS_SECTOR varchar(1)    NOT NULL,
				HAS_SPECIALISATION varchar(1)    NOT NULL,
				VISIBLE varchar(1)    NOT NULL,
				BACKGROUND_QUESTION text,
				PRIMARY KEY  (ID),
				CONSTRAINT `" . $table_prefix . "background_constraint_1` FOREIGN KEY (COST_MODEL_ID) REFERENCES " . $table_prefix . "COST_MODEL(ID)
				) ENGINE=INNODB;";
	
	$for_update = dbDelta($sql);
	vtm_save_install_errors($current_table_name, $lasterror, $for_update);
		
	$current_table_name = $table_prefix . "MERIT";
		$sql = "CREATE TABLE " . $current_table_name . " (
					ID mediumint(9)  NOT NULL  AUTO_INCREMENT,
					NAME varchar(32)   NOT NULL,
					DESCRIPTION tinytext      NOT NULL,
					VALUE smallint(3)   NOT NULL,
					GROUPING varchar(30)   NOT NULL,
					COST smallint(3)   NOT NULL,
					XP_COST smallint(3)   NOT NULL,
					MULTIPLE varchar(1)    NOT NULL,
					HAS_SPECIALISATION varchar(1)    NOT NULL,
					SOURCE_BOOK_ID mediumint(9)  NOT NULL,
					PAGE_NUMBER smallint(4)   NOT NULL,
					VISIBLE varchar(1)    NOT NULL,
					BACKGROUND_QUESTION varchar(255),
					PROFILE_DISPLAY_ID mediumint(9)  NOT NULL,
					PRIMARY KEY  (ID),
					CONSTRAINT `" . $table_prefix . "merit_constraint_1` FOREIGN KEY (SOURCE_BOOK_ID) REFERENCES " . $table_prefix . "SOURCE_BOOK(ID)
					) ENGINE=INNODB;";			
		$for_update = dbDelta($sql);
	vtm_save_install_errors($current_table_name, $lasterror, $for_update);

	$current_table_name = $table_prefix . "DISCIPLINE";
	$sql = "CREATE TABLE " . $current_table_name . " (
				ID mediumint(9)  NOT NULL   AUTO_INCREMENT,
				NAME varchar(32)   NOT NULL,
				DESCRIPTION tinytext      NOT NULL,
				SOURCE_BOOK_ID mediumint(9)  NOT NULL,
				PAGE_NUMBER smallint(4)   NOT NULL,
				VISIBLE varchar(1)    NOT NULL,
				PRIMARY KEY  (ID),
				CONSTRAINT `" . $table_prefix . "discipline_constraint_1` FOREIGN KEY (SOURCE_BOOK_ID) REFERENCES " . $table_prefix . "SOURCE_BOOK(ID)
				) ENGINE=INNODB;";
	$for_update = dbDelta($sql);
	vtm_save_install_errors($current_table_name, $lasterror, $for_update);
	
	$current_table_name = $table_prefix . "COMBO_DISCIPLINE";
		$sql = "CREATE TABLE " . $current_table_name . " (
					ID mediumint(9)  NOT NULL   AUTO_INCREMENT,
					NAME varchar(60)   NOT NULL,
					DESCRIPTION tinytext      NOT NULL,
					COST smallint(3)   NOT NULL,
					SOURCE_BOOK_ID mediumint(9)  NOT NULL,
					PAGE_NUMBER smallint(4)   NOT NULL,
					VISIBLE varchar(1)    NOT NULL,
					PRIMARY KEY  (ID),
					CONSTRAINT `" . $table_prefix . "combo_disc_constraint_1` FOREIGN KEY (SOURCE_BOOK_ID) REFERENCES " . $table_prefix . "SOURCE_BOOK(ID)
					) ENGINE=INNODB;";
		$for_update = dbDelta($sql);
	vtm_save_install_errors($current_table_name, $lasterror, $for_update);

	$current_table_name = $table_prefix . "CONFIG";
	$sql = "CREATE TABLE " . $current_table_name . " (
				ID mediumint(9)   NOT NULL  AUTO_INCREMENT,
				PLACEHOLDER_IMAGE tinytext       NOT NULL,
				ANDROID_LINK tinytext       NOT NULL,
				HOME_DOMAIN_ID mediumint(9)   NOT NULL,
				HOME_SECT_ID mediumint(9)   NOT NULL,
				DEFAULT_GENERATION_ID mediumint(9)   NOT NULL,
				ASSIGN_XP_BY_PLAYER varchar(1)     NOT NULL,
				USE_NATURE_DEMEANOUR varchar(1)     NOT NULL,
				DISPLAY_BACKGROUND_IN_PROFILE mediumint(9)     NOT NULL,
				PRIMARY KEY  (ID),
				CONSTRAINT `" . $table_prefix . "config_constraint_1` FOREIGN KEY (HOME_DOMAIN_ID)  REFERENCES " . $table_prefix . "DOMAIN(ID),
				CONSTRAINT `" . $table_prefix . "config_constraint_2` FOREIGN KEY (HOME_SECT_ID)    REFERENCES " . $table_prefix . "SECT(ID)
				) ENGINE=INNODB;";
	$for_update = dbDelta($sql);
	vtm_save_install_errors($current_table_name, $lasterror, $for_update);
	
	$current_table_name = $table_prefix . "MAPDOMAIN";
	$sql = "CREATE TABLE " . $current_table_name . " (
				ID mediumint(9)  NOT NULL   AUTO_INCREMENT,
				NAME varchar(60)   NOT NULL,
				OWNER_ID mediumint(9)  NOT NULL,
				DESCRIPTION tinytext      NOT NULL,
				COORDINATES     LONGTEXT      NOT NULL,
				VISIBLE varchar(1)    NOT NULL,
				PRIMARY KEY  (ID),
				CONSTRAINT `" . $table_prefix . "mapdomain_constraint_1` FOREIGN KEY (OWNER_ID)  REFERENCES " . $table_prefix . "MAPOWNER(ID)
				) ENGINE=INNODB;";
	$for_update = dbDelta($sql);
	vtm_save_install_errors($current_table_name, $lasterror, $for_update);
	
	// LEVEL 3 TABLES - TABLES WITH A FOREIGN KEY CONSTRAINT TO A LEVEL 2 TABLE

	$current_table_name = $table_prefix . "ROAD_OR_PATH";
	$sql = "CREATE TABLE " . $current_table_name . " (
				ID mediumint(9)  NOT NULL  AUTO_INCREMENT,
				NAME varchar(32)   NOT NULL,
				DESCRIPTION tinytext      NOT NULL,
				STAT1_ID mediumint(9)  NOT NULL,
				STAT2_ID mediumint(9)  NOT NULL,
				SOURCE_BOOK_ID mediumint(9)  NOT NULL,
				PAGE_NUMBER smallint(4)   NOT NULL,
				VISIBLE varchar(1)    NOT NULL,
				COST_MODEL_ID mediumint(9)  NOT NULL,
				PRIMARY KEY  (ID),
				CONSTRAINT `" . $table_prefix . "road_constraint_1` FOREIGN KEY (STAT1_ID) REFERENCES " . $table_prefix . "STAT(ID),
				CONSTRAINT `" . $table_prefix . "road_constraint_2` FOREIGN KEY (STAT2_ID) REFERENCES " . $table_prefix . "STAT(ID),
				CONSTRAINT `" . $table_prefix . "road_constraint_3` FOREIGN KEY (SOURCE_BOOK_ID) REFERENCES " . $table_prefix . "SOURCE_BOOK(ID),
				CONSTRAINT `" . $table_prefix . "road_constraint_4` FOREIGN KEY (COST_MODEL_ID)  REFERENCES " . $table_prefix . "COST_MODEL(ID)
				) ENGINE=INNODB;";
	$for_update = dbDelta($sql);
	vtm_save_install_errors($current_table_name, $lasterror, $for_update);
	
	$current_table_name = $table_prefix . "PATH";
		$sql = "CREATE TABLE " . $current_table_name . " (
					ID mediumint(9)  NOT NULL  AUTO_INCREMENT,
					NAME varchar(63)   NOT NULL,
					DESCRIPTION tinytext      NOT NULL,
					DISCIPLINE_ID mediumint(9)  NOT NULL,
					COST_MODEL_ID mediumint(9)  NOT NULL,
					SOURCE_BOOK_ID mediumint(9)   NOT NULL,
					PAGE_NUMBER smallint(4)   NOT NULL,
					VISIBLE varchar(1)    NOT NULL,
					PRIMARY KEY  (ID),
					CONSTRAINT `" . $table_prefix . "path_constraint_1` FOREIGN KEY (DISCIPLINE_ID)  REFERENCES " . $table_prefix . "DISCIPLINE(ID),
					CONSTRAINT `" . $table_prefix . "path_constraint_2` FOREIGN KEY (SOURCE_BOOK_ID) REFERENCES " . $table_prefix . "SOURCE_BOOK(ID),
					CONSTRAINT `" . $table_prefix . "path_constraint_3` FOREIGN KEY (COST_MODEL_ID)  REFERENCES " . $table_prefix . "COST_MODEL(ID)
					) ENGINE=INNODB;";

		
	vtm_save_install_errors($current_table_name, $lasterror, $for_update);
		$for_update = dbDelta($sql);

	$current_table_name = $table_prefix . "DISCIPLINE_POWER";
		$sql = "CREATE TABLE " . $current_table_name . " (
					ID mediumint(9) NOT NULL AUTO_INCREMENT,
					NAME varchar(32) NOT NULL,
					DESCRIPTION tinytext NOT NULL,
					LEVEL smallint(2) NOT NULL,
					DISCIPLINE_ID mediumint(9) NOT NULL,
					DICE_POOL varchar(60) NOT NULL,
					DIFFICULTY varchar(60) NOT NULL,
					COST smallint(3) NOT NULL,
					SOURCE_BOOK_ID mediumint(9) NOT NULL,
					PAGE_NUMBER smallint(4) NOT NULL,
					VISIBLE varchar(1) NOT NULL,
					PRIMARY KEY  (ID),
					CONSTRAINT `" . $table_prefix . "disc_power_constraint_1` FOREIGN KEY (DISCIPLINE_ID) REFERENCES " . $table_prefix . "DISCIPLINE(ID),
					CONSTRAINT `" . $table_prefix . "disc_power_constraint_2` FOREIGN KEY (SOURCE_BOOK_ID) REFERENCES " . $table_prefix . "SOURCE_BOOK(ID)
					) ENGINE=INNODB;";

		
		$for_update = dbDelta($sql);
	vtm_save_install_errors($current_table_name, $lasterror, $for_update);

	$current_table_name = $table_prefix . "RITUAL";
		$sql = "CREATE TABLE " . $current_table_name . " (
					ID mediumint(9)  NOT NULL  AUTO_INCREMENT,
					NAME varchar(60)   NOT NULL,
					DESCRIPTION tinytext      NOT NULL,
					LEVEL smallint(2)   NOT NULL,
					DISCIPLINE_ID mediumint(9)  NOT NULL,
					DICE_POOL varchar(60)   NOT NULL,
					DIFFICULTY varchar(60)   NOT NULL,
					COST smallint(3)   NOT NULL,
					SOURCE_BOOK_ID mediumint(9)   NOT NULL,
					PAGE_NUMBER smallint(4)   NOT NULL,
					VISIBLE varchar(1)    NOT NULL,
					PRIMARY KEY  (ID),
					CONSTRAINT `" . $table_prefix . "ritual_constraint_1` FOREIGN KEY (DISCIPLINE_ID) REFERENCES " . $table_prefix . "DISCIPLINE(ID),
					CONSTRAINT `" . $table_prefix . "ritual_constraint_2` FOREIGN KEY (SOURCE_BOOK_ID) REFERENCES " . $table_prefix . "SOURCE_BOOK(ID)
					) ENGINE=INNODB;";
		$for_update = dbDelta($sql);
	vtm_save_install_errors($current_table_name, $lasterror, $for_update);

	$current_table_name = $table_prefix . "CLAN_DISCIPLINE";
		$sql = "CREATE TABLE " . $current_table_name . " (
					ID mediumint(9) NOT NULL AUTO_INCREMENT,
					CLAN_ID mediumint(9) NOT NULL,
					DISCIPLINE_ID mediumint(9) NOT NULL,
					PRIMARY KEY  (ID),
					CONSTRAINT `" . $table_prefix . "clan_disc_constraint_1` FOREIGN KEY (CLAN_ID)       REFERENCES " . $table_prefix . "CLAN(ID),
					CONSTRAINT `" . $table_prefix . "clan_disc_constraint_2` FOREIGN KEY (DISCIPLINE_ID) REFERENCES " . $table_prefix . "DISCIPLINE(ID)
					) ENGINE=INNODB;";
		$for_update = dbDelta($sql);
	vtm_save_install_errors($current_table_name, $lasterror, $for_update);
	//echo "<p>Clan Disc SQL: $sql</p>";

	$current_table_name = $table_prefix . "COMBO_DISCIPLINE_PREREQUISITE";
		$sql = "CREATE TABLE " . $current_table_name . " (
					ID mediumint(9)  NOT NULL AUTO_INCREMENT,
					COMBO_DISCIPLINE_ID mediumint(9)  NOT NULL,
					DISCIPLINE_ID mediumint(9)  NOT NULL,
					DISCIPLINE_LEVEL smallint(3)   NOT NULL,
					PRIMARY KEY  (ID),
					CONSTRAINT `" . $table_prefix . "char_combo_pre_constraint_1` FOREIGN KEY (COMBO_DISCIPLINE_ID) REFERENCES " . $table_prefix . "COMBO_DISCIPLINE(ID),
					CONSTRAINT `" . $table_prefix . "char_combo_pre_constraint_2` FOREIGN KEY (DISCIPLINE_ID)       REFERENCES " . $table_prefix . "DISCIPLINE(ID)
					) ENGINE=INNODB;";
		$for_update = dbDelta($sql);
	vtm_save_install_errors($current_table_name, $lasterror, $for_update);

	// LEVEL 4 TABLES - TABLES WITH A FOREIGN KEY CONSTRAINT TO A LEVEL 3 TABLE

	$current_table_name = $table_prefix . "CHARACTER";
	//echo "<p>Setting up $current_table_name</p>";
	$sql = "CREATE TABLE " . $current_table_name . " (
				ID mediumint(9)  NOT NULL  AUTO_INCREMENT,
				NAME varchar(60)   NOT NULL,
				PUBLIC_CLAN_ID mediumint(9)  NOT NULL,
				PRIVATE_CLAN_ID mediumint(9)  NOT NULL,
				GENERATION_ID mediumint(9)  NOT NULL,
				DATE_OF_BIRTH             DATE          NOT NULL,
				DATE_OF_EMBRACE           DATE          NOT NULL,
				SIRE varchar(60)   NOT NULL,
				PLAYER_ID mediumint(9)  NOT NULL,
				CHARACTER_TYPE_ID mediumint(9)  NOT NULL,
				CHARACTER_STATUS_ID mediumint(9)  NOT NULL,
				CHARACTER_STATUS_COMMENT varchar(120),
				ROAD_OR_PATH_ID mediumint(9)  NOT NULL,
				ROAD_OR_PATH_RATING smallint(3)   NOT NULL,
				DOMAIN_ID mediumint(9)  NOT NULL,
				WORDPRESS_ID varchar(32)   NOT NULL,
				SECT_ID mediumint(9)  NOT NULL,
				NATURE_ID mediumint(9)  NOT NULL,
				DEMEANOUR_ID mediumint(9)  NOT NULL,
				CHARGEN_STATUS_ID mediumint(9)  NOT NULL,
				CONCEPT tinytext		NOT NULL,
				EMAIL varchar(60)	NOT NULL,
				LAST_UPDATED              DATE          NOT NULL,
				GET_NEWSLETTER varchar(1)    NOT NULL,
				VISIBLE varchar(1)    NOT NULL,
				DELETED varchar(1)    NOT NULL,
				PRIMARY KEY  (ID),
				CONSTRAINT `" . $table_prefix . "char_constraint_1`  FOREIGN KEY  (PUBLIC_CLAN_ID)       REFERENCES " . $table_prefix . "CLAN(ID),
				CONSTRAINT `" . $table_prefix . "char_constraint_2`  FOREIGN KEY  (PRIVATE_CLAN_ID)      REFERENCES " . $table_prefix . "CLAN(ID),
				CONSTRAINT `" . $table_prefix . "char_constraint_3`  FOREIGN KEY  (GENERATION_ID)        REFERENCES " . $table_prefix . "GENERATION(ID),
				CONSTRAINT `" . $table_prefix . "char_constraint_4`  FOREIGN KEY  (PLAYER_ID)            REFERENCES " . $table_prefix . "PLAYER(ID),
				CONSTRAINT `" . $table_prefix . "char_constraint_5`  FOREIGN KEY  (CHARACTER_TYPE_ID)    REFERENCES " . $table_prefix . "CHARACTER_TYPE(ID),
				CONSTRAINT `" . $table_prefix . "char_constraint_6`  FOREIGN KEY  (CHARACTER_STATUS_ID)  REFERENCES " . $table_prefix . "CHARACTER_STATUS(ID),
				CONSTRAINT `" . $table_prefix . "char_constraint_7`  FOREIGN KEY  (ROAD_OR_PATH_ID)      REFERENCES " . $table_prefix . "ROAD_OR_PATH(ID),
				CONSTRAINT `" . $table_prefix . "char_constraint_8`  FOREIGN KEY  (DOMAIN_ID)            REFERENCES " . $table_prefix . "DOMAIN(ID),
				CONSTRAINT `" . $table_prefix . "char_constraint_9`  FOREIGN KEY  (SECT_ID)              REFERENCES " . $table_prefix . "SECT(ID),
				CONSTRAINT `" . $table_prefix . "char_constraint_10`  FOREIGN KEY (CHARGEN_STATUS_ID)    REFERENCES " . $table_prefix . "CHARGEN_STATUS(ID)
				) ENGINE=INNODB;";
	$for_update = dbDelta($sql);
	vtm_save_install_errors($current_table_name, $lasterror, $for_update);

	$current_table_name = $table_prefix . "PATH_POWER";
		$sql = "CREATE TABLE " . $current_table_name . " (
					ID mediumint(9)  NOT NULL  AUTO_INCREMENT,
					NAME varchar(32)   NOT NULL,
					DESCRIPTION tinytext      NOT NULL,
					LEVEL smallint(2)   NOT NULL,
					PATH_ID mediumint(9)  NOT NULL,
					DICE_POOL varchar(60)   NOT NULL,
					DIFFICULTY varchar(60)   NOT NULL,
					COST smallint(3)   NOT NULL,
					SOURCE_BOOK_ID mediumint(9)   NOT NULL,
					PAGE_NUMBER smallint(4)   NOT NULL,
					VISIBLE varchar(1)    NOT NULL,
					PRIMARY KEY  (ID),
					CONSTRAINT `" . $table_prefix . "path_power_constraint_1` FOREIGN KEY (PATH_ID) REFERENCES " . $table_prefix . "PATH(ID),
					CONSTRAINT `" . $table_prefix . "path_power_constraint_2` FOREIGN KEY (SOURCE_BOOK_ID) REFERENCES " . $table_prefix . "SOURCE_BOOK(ID)
					) ENGINE=INNODB;";

		
		$for_update = dbDelta($sql);
	vtm_save_install_errors($current_table_name, $lasterror, $for_update);

	// LEVEL 5 TABLES - TABLES WITH A FOREIGN KEY CONSTRAINT TO A LEVEL 4 TABLE

	$current_table_name = $table_prefix . "CHARACTER_OFFICE";
	$sql = "CREATE TABLE " . $current_table_name . " (
				ID mediumint(9) NOT NULL  AUTO_INCREMENT,
				OFFICE_ID mediumint(9) NOT NULL,
				DOMAIN_ID mediumint(9) NOT NULL,
				CHARACTER_ID mediumint(9) NOT NULL,
				COMMENT varchar(60),
				PRIMARY KEY  (ID),
				CONSTRAINT `" . $table_prefix . "office_constraint_1` FOREIGN KEY (OFFICE_ID)    REFERENCES " . $table_prefix . "OFFICE(ID),
				CONSTRAINT `" . $table_prefix . "office_constraint_2` FOREIGN KEY (DOMAIN_ID)    REFERENCES " . $table_prefix . "DOMAIN(ID),
				CONSTRAINT `" . $table_prefix . "office_constraint_3` FOREIGN KEY (CHARACTER_ID) REFERENCES " . $table_prefix . "CHARACTER(ID)
				) ENGINE=INNODB;";
	$for_update = dbDelta($sql);
	vtm_save_install_errors($current_table_name, $lasterror, $for_update);

	$current_table_name = $table_prefix . "PLAYER_XP";
	$sql = "CREATE TABLE " . $current_table_name . " (
				ID mediumint(9)  NOT NULL  AUTO_INCREMENT,
				PLAYER_ID mediumint(9)  NOT NULL,
				CHARACTER_ID mediumint(9)  NOT NULL,
				XP_REASON_ID mediumint(9)  NOT NULL,
				AWARDED        DATE          NOT NULL,
				AMOUNT smallint(3)   NOT NULL,
				COMMENT varchar(120)  NOT NULL,
				PRIMARY KEY  (ID),
				CONSTRAINT `" . $table_prefix . "player_xp_constraint_1` FOREIGN KEY (PLAYER_ID)    REFERENCES " . $table_prefix . "PLAYER(ID),
				CONSTRAINT `" . $table_prefix . "player_xp_constraint_2` FOREIGN KEY (CHARACTER_ID) REFERENCES " . $table_prefix . "CHARACTER(ID),
				CONSTRAINT `" . $table_prefix . "player_xp_constraint_3` FOREIGN KEY (XP_REASON_ID) REFERENCES " . $table_prefix . "XP_REASON(ID)
				) ENGINE=INNODB;";
	$for_update = dbDelta($sql);
	vtm_save_install_errors($current_table_name, $lasterror, $for_update);

	/*     CHARTABLE         = Character table to update or add new row to
		CHARTABLE_ID	= ID of row in character table to update (0 for new)
		CHARTABLE_LEVEL = LEVEL of item to add/update character table to
		SPECIALISATION  = COMMENT of item to add/update character table to
		COMMENT			= What gets displayed in spend table
		ITEMTABLE       = For new skills/stats/etc: what table they belong to
		ITEMNAME        = For new skills/stats/etc: what is the name of the column for the item
		ITEMTABLE_ID    = For new skills/stats/etc: what table ID they have
	*/
	$current_table_name = $table_prefix . "PENDING_XP_SPEND";
	$sql = "CREATE TABLE " . $current_table_name . " (
				ID mediumint(9)  NOT NULL  AUTO_INCREMENT,
				PLAYER_ID mediumint(9)  NOT NULL,
				CHARACTER_ID mediumint(9)  NOT NULL,
				CHARTABLE tinytext      NOT NULL,
				CHARTABLE_ID mediumint(9)  NOT NULL,
				CHARTABLE_LEVEL tinytext    NOT NULL,
				AWARDED        DATE          NOT NULL,
				AMOUNT smallint(3)   NOT NULL,
				COMMENT varchar(120)  NOT NULL,
				SPECIALISATION varchar(64)	 NOT NULL,
				TRAINING_NOTE varchar(164)  NOT NULL,
				ITEMTABLE tinytext      NOT NULL,
				ITEMNAME tinytext      NOT NULL,
				ITEMTABLE_ID mediumint(9)  NOT NULL,
				PRIMARY KEY  (ID),
				CONSTRAINT `" . $table_prefix . "pending_xp_constraint_1` FOREIGN KEY (PLAYER_ID)    REFERENCES " . $table_prefix . "PLAYER(ID),
				CONSTRAINT `" . $table_prefix . "pending_xp_constraint_2` FOREIGN KEY (CHARACTER_ID) REFERENCES " . $table_prefix . "CHARACTER(ID)
				) ENGINE=INNODB;";
	$for_update = dbDelta($sql);
	vtm_save_install_errors($current_table_name, $lasterror, $for_update);

	$current_table_name = $table_prefix . "PENDING_FREEBIE_SPEND";
	$sql = "CREATE TABLE " . $current_table_name . " (
				ID mediumint(9)  NOT NULL  AUTO_INCREMENT,
				CHARACTER_ID mediumint(9)  NOT NULL,
				CHARTABLE tinytext      NOT NULL,
				CHARTABLE_ID mediumint(9)  NOT NULL,
				LEVEL_FROM mediumint(9)  NOT NULL,
				LEVEL_TO mediumint(9)  NOT NULL,
				AMOUNT smallint(3)   NOT NULL,
				ITEMTABLE tinytext      NOT NULL,
				ITEMNAME tinytext      NOT NULL,
				ITEMTABLE_ID mediumint(9)  NOT NULL,
				SPECIALISATION varchar(64)	 NOT NULL,
				PENDING_DETAIL text          NOT NULL,
				PRIMARY KEY  (ID),
				CONSTRAINT `" . $table_prefix . "pending_freebie_constraint_1` FOREIGN KEY (CHARACTER_ID) REFERENCES " . $table_prefix . "CHARACTER(ID)
				) ENGINE=INNODB;";
	$for_update = dbDelta($sql);
	vtm_save_install_errors($current_table_name, $lasterror, $for_update);

	$current_table_name = $table_prefix . "CHARACTER_ROAD_OR_PATH";
	$sql = "CREATE TABLE " . $current_table_name . " (
				ID mediumint(9)  NOT NULL  AUTO_INCREMENT,
				CHARACTER_ID mediumint(9)  NOT NULL,
				PATH_REASON_ID mediumint(9)  NOT NULL,
				AWARDED          DATE          NOT NULL,
				AMOUNT smallint(3)   NOT NULL,
				COMMENT varchar(120)  NOT NULL,
				PRIMARY KEY  (ID),
				CONSTRAINT `" . $table_prefix . "char_road_constraint_1` FOREIGN KEY (CHARACTER_ID) REFERENCES " . $table_prefix . "CHARACTER(ID),
				CONSTRAINT `" . $table_prefix . "char_road_constraint_2` FOREIGN KEY (PATH_REASON_ID) REFERENCES " . $table_prefix . "PATH_REASON(ID)
				) ENGINE=INNODB;";
	$for_update = dbDelta($sql);
	vtm_save_install_errors($current_table_name, $lasterror, $for_update);

	$current_table_name = $table_prefix . "CHARACTER_TEMPORARY_STAT";
		$sql = "CREATE TABLE " . $current_table_name . " (
					ID mediumint(9)  NOT NULL  AUTO_INCREMENT,
					CHARACTER_ID mediumint(9)  NOT NULL,
					TEMPORARY_STAT_ID mediumint(9)  NOT NULL,
					TEMPORARY_STAT_REASON_ID mediumint(9)  NOT NULL,
					AWARDED                   DATE          NOT NULL,
					AMOUNT smallint(3)   NOT NULL,
					COMMENT varchar(120)  NOT NULL,
					PRIMARY KEY  (ID),
					CONSTRAINT `" . $table_prefix . "char_temp_constraint_1` FOREIGN KEY (CHARACTER_ID) REFERENCES " . $table_prefix . "CHARACTER(ID),
					CONSTRAINT `" . $table_prefix . "char_temp_constraint_2` FOREIGN KEY (TEMPORARY_STAT_ID) REFERENCES " . $table_prefix . "TEMPORARY_STAT(ID),
					CONSTRAINT `" . $table_prefix . "char_temp_constraint_3` FOREIGN KEY (TEMPORARY_STAT_REASON_ID) REFERENCES " . $table_prefix . "TEMPORARY_STAT_REASON(ID)
					) ENGINE=INNODB;";

		
		$for_update = dbDelta($sql);
	vtm_save_install_errors($current_table_name, $lasterror, $for_update);

	$current_table_name = $table_prefix . "CHARACTER_STAT";
		$sql = "CREATE TABLE " . $current_table_name . " (
					ID mediumint(9)  NOT NULL  AUTO_INCREMENT,
					CHARACTER_ID mediumint(9)  NOT NULL,
					STAT_ID mediumint(9)  NOT NULL,
					LEVEL smallint(3)   NOT NULL,
					COMMENT varchar(60),
					PRIMARY KEY  (ID),
					CONSTRAINT `" . $table_prefix . "char_stat_constraint_1` FOREIGN KEY (CHARACTER_ID) REFERENCES " . $table_prefix . "CHARACTER(ID),
					CONSTRAINT `" . $table_prefix . "char_stat_constraint_2` FOREIGN KEY (STAT_ID)      REFERENCES " . $table_prefix . "STAT(ID)
					) ENGINE=INNODB;";
		$for_update = dbDelta($sql);
	vtm_save_install_errors($current_table_name, $lasterror, $for_update);

	$current_table_name = $table_prefix . "CHARACTER_RITUAL";
		$sql = "CREATE TABLE " . $current_table_name . " (
					ID mediumint(9)  NOT NULL  AUTO_INCREMENT,
					CHARACTER_ID mediumint(9)  NOT NULL,
					RITUAL_ID mediumint(9)  NOT NULL,
					LEVEL smallint(3)   NOT NULL,
					COMMENT varchar(60),
					PRIMARY KEY  (ID),
					CONSTRAINT `" . $table_prefix . "char_ritual_constraint_1` FOREIGN KEY (CHARACTER_ID) REFERENCES " . $table_prefix . "CHARACTER(ID),
					CONSTRAINT `" . $table_prefix . "char_ritual_constraint_2` FOREIGN KEY (RITUAL_ID)    REFERENCES " . $table_prefix . "RITUAL(ID)
					) ENGINE=INNODB;";
		$for_update = dbDelta($sql);
	vtm_save_install_errors($current_table_name, $lasterror, $for_update);

	$current_table_name = $table_prefix . "CHARACTER_DISCIPLINE";
		$sql = "CREATE TABLE " . $current_table_name . " (
					ID mediumint(9)  NOT NULL  AUTO_INCREMENT,
					CHARACTER_ID mediumint(9)  NOT NULL,
					DISCIPLINE_ID mediumint(9)  NOT NULL,
					LEVEL smallint(3)   NOT NULL,
					COMMENT varchar(60),
					PRIMARY KEY  (ID),
					CONSTRAINT `" . $table_prefix . "char_disc_constraint_1` FOREIGN KEY (CHARACTER_ID)  REFERENCES " . $table_prefix . "CHARACTER(ID),
					CONSTRAINT `" . $table_prefix . "char_disc_constraint_2` FOREIGN KEY (DISCIPLINE_ID) REFERENCES " . $table_prefix . "DISCIPLINE(ID)
					) ENGINE=INNODB;";
		$for_update = dbDelta($sql);
	vtm_save_install_errors($current_table_name, $lasterror, $for_update);

	$current_table_name = $table_prefix . "CHARACTER_PATH";
		$sql = "CREATE TABLE " . $current_table_name . " (
					ID mediumint(9)  NOT NULL  AUTO_INCREMENT,
					CHARACTER_ID mediumint(9)  NOT NULL,
					PATH_ID mediumint(9)  NOT NULL,
					LEVEL smallint(3)   NOT NULL,
					COMMENT varchar(60),
					PRIMARY KEY  (ID),
					CONSTRAINT `" . $table_prefix . "char_path_constraint_1` FOREIGN KEY (CHARACTER_ID)  REFERENCES " . $table_prefix . "CHARACTER(ID),
					CONSTRAINT `" . $table_prefix . "char_path_constraint_2` FOREIGN KEY (PATH_ID) REFERENCES " . $table_prefix . "PATH(ID)
					) ENGINE=INNODB;";
		$for_update = dbDelta($sql);
	vtm_save_install_errors($current_table_name, $lasterror, $for_update);

	$current_table_name = $table_prefix . "CHARACTER_PATH_POWER";
		$sql = "CREATE TABLE " . $current_table_name . " (
					ID mediumint(9)  NOT NULL  AUTO_INCREMENT,
					CHARACTER_ID mediumint(9)  NOT NULL,
					PATH_POWER_ID mediumint(9)  NOT NULL,
					LEVEL smallint(3)   NOT NULL,
					COMMENT varchar(60),
					PRIMARY KEY  (ID),
					CONSTRAINT `" . $table_prefix . "char_path_power_constraint_1` FOREIGN KEY (CHARACTER_ID)    REFERENCES " . $table_prefix . "CHARACTER(ID),
					CONSTRAINT `" . $table_prefix . "char_path_power_constraint_2` FOREIGN KEY (PATH_POWER_ID)   REFERENCES " . $table_prefix . "PATH_POWER(ID)
					) ENGINE=INNODB;";
		$for_update = dbDelta($sql);
	vtm_save_install_errors($current_table_name, $lasterror, $for_update);

	$current_table_name = $table_prefix . "CHARACTER_DISCIPLINE_POWER";
		$sql = "CREATE TABLE " . $current_table_name . " (
					ID mediumint(9)  NOT NULL  AUTO_INCREMENT,
					CHARACTER_ID mediumint(9)  NOT NULL,
					DISCIPLINE_POWER_ID mediumint(9)  NOT NULL,
					LEVEL smallint(3)   NOT NULL,
					COMMENT varchar(60),
					PRIMARY KEY  (ID),
					CONSTRAINT `" . $table_prefix . "char_disc_power_constraint_1` FOREIGN KEY (CHARACTER_ID)        REFERENCES " . $table_prefix . "CHARACTER(ID),
					CONSTRAINT `" . $table_prefix . "char_disc_power_constraint_2` FOREIGN KEY (DISCIPLINE_POWER_ID) REFERENCES " . $table_prefix . "DISCIPLINE_POWER(ID)
					) ENGINE=INNODB;";
		$for_update = dbDelta($sql);
	vtm_save_install_errors($current_table_name, $lasterror, $for_update);

	$current_table_name = $table_prefix . "CHARACTER_MERIT";
		$sql = "CREATE TABLE " . $current_table_name . " (
					ID mediumint(9)  NOT NULL  AUTO_INCREMENT,
					CHARACTER_ID mediumint(9)  NOT NULL,
					MERIT_ID mediumint(9)  NOT NULL,
					LEVEL smallint(3)   NOT NULL,
					COMMENT varchar(60),
					APPROVED_DETAIL text,
					PENDING_DETAIL text,
					DENIED_DETAIL text,
					PRIMARY KEY  (ID),
					CONSTRAINT `" . $table_prefix . "char_merit_constraint_1` FOREIGN KEY (CHARACTER_ID) REFERENCES " . $table_prefix . "CHARACTER(ID),
					CONSTRAINT `" . $table_prefix . "char_merit_constraint_2` FOREIGN KEY (MERIT_ID)     REFERENCES " . $table_prefix . "MERIT(ID)
					) ENGINE=INNODB;";
		$for_update = dbDelta($sql);
	vtm_save_install_errors($current_table_name, $lasterror, $for_update);

	$current_table_name = $table_prefix . "CHARACTER_SKILL";
		$sql = "CREATE TABLE " . $current_table_name . " (
					ID mediumint(9)  NOT NULL  AUTO_INCREMENT,
					CHARACTER_ID mediumint(9)  NOT NULL,
					SKILL_ID mediumint(9)  NOT NULL,
					LEVEL smallint(3)   NOT NULL,
					COMMENT varchar(60),
					PRIMARY KEY  (ID),
					CONSTRAINT `" . $table_prefix . "char_skill_constraint_1` FOREIGN KEY (CHARACTER_ID) REFERENCES " . $table_prefix . "CHARACTER(ID),
					CONSTRAINT `" . $table_prefix . "char_skill_constraint_2` FOREIGN KEY (SKILL_ID)     REFERENCES " . $table_prefix . "SKILL(ID)
					) ENGINE=INNODB;";
		$for_update = dbDelta($sql);
	vtm_save_install_errors($current_table_name, $lasterror, $for_update);

	$current_table_name = $table_prefix . "CHARACTER_BACKGROUND";
	$sql = "CREATE TABLE " . $current_table_name . " (
				ID mediumint(9)  NOT NULL  AUTO_INCREMENT,
				CHARACTER_ID mediumint(9)  NOT NULL,
				BACKGROUND_ID mediumint(9)  NOT NULL,
				LEVEL smallint(3)   NOT NULL,
				SECTOR_ID mediumint(9)  NOT NULL,
				COMMENT varchar(60),
				APPROVED_DETAIL text,
				PENDING_DETAIL text,
				DENIED_DETAIL text,
				PRIMARY KEY  (ID),
				CONSTRAINT `" . $table_prefix . "char_bg_constraint_1` FOREIGN KEY (CHARACTER_ID)  REFERENCES " . $table_prefix . "CHARACTER(ID),
				CONSTRAINT `" . $table_prefix . "char_bg_constraint_2` FOREIGN KEY (BACKGROUND_ID) REFERENCES " . $table_prefix . "BACKGROUND(ID)
				) ENGINE=INNODB;";
	$for_update = dbDelta($sql);
	vtm_save_install_errors($current_table_name, $lasterror, $for_update);
	
	$current_table_name = $table_prefix . "CHARACTER_COMBO_DISCIPLINE";
	
		$sql = "CREATE TABLE " . $current_table_name . " (
					ID mediumint(9)  NOT NULL AUTO_INCREMENT,
					CHARACTER_ID mediumint(9)  NOT NULL,
					COMBO_DISCIPLINE_ID mediumint(9)  NOT NULL,
					COMMENT varchar(60),
					PRIMARY KEY  (ID),
					CONSTRAINT `" . $table_prefix . "char_combo_constraint_1` FOREIGN KEY (CHARACTER_ID)        REFERENCES " . $table_prefix . "CHARACTER(ID),
					CONSTRAINT `" . $table_prefix . "char_combo_constraint_2` FOREIGN KEY (COMBO_DISCIPLINE_ID) REFERENCES " . $table_prefix . "COMBO_DISCIPLINE(ID)
					) ENGINE=INNODB;";
		$for_update = dbDelta($sql);
	vtm_save_install_errors($current_table_name, $lasterror, $for_update);

	$current_table_name = $table_prefix . "CHARACTER_PROFILE";
		$sql = "CREATE TABLE " . $current_table_name . " (
					ID mediumint(9)   NOT NULL  AUTO_INCREMENT,
					CHARACTER_ID mediumint(9)   NOT NULL,
					QUOTE text			 NOT NULL,
					PORTRAIT tinytext       NOT NULL,
					PRIMARY KEY  (ID),
					CONSTRAINT `" . $table_prefix . "char_profile_constraint_1` FOREIGN KEY (CHARACTER_ID)  REFERENCES " . $table_prefix . "CHARACTER(ID)
					) ENGINE=INNODB;";
		$for_update = dbDelta($sql);
	vtm_save_install_errors($current_table_name, $lasterror, $for_update);

	$current_table_name = $table_prefix . "CHARACTER_EXTENDED_BACKGROUND";
	$sql = "CREATE TABLE " . $current_table_name . " (
				ID mediumint(9)  NOT NULL  AUTO_INCREMENT,
				CHARACTER_ID mediumint(9)  NOT NULL,
				QUESTION_ID mediumint(9)  NOT NULL,
				APPROVED_DETAIL text           NOT NULL,
				PENDING_DETAIL text           NOT NULL,
				DENIED_DETAIL text           NOT NULL,
				PRIMARY KEY  (ID),
				CONSTRAINT `" . $table_prefix . "char_ext_bg_constraint_1` FOREIGN KEY (CHARACTER_ID)  REFERENCES " . $table_prefix . "CHARACTER(ID),
				CONSTRAINT `" . $table_prefix . "char_ext_bg_constraint_2` FOREIGN KEY (QUESTION_ID)  REFERENCES " . $table_prefix . "EXTENDED_BACKGROUND(ID)
				) ENGINE=INNODB;";
	$for_update = dbDelta($sql);
	vtm_save_install_errors($current_table_name, $lasterror, $for_update);
	
	$current_table_name = $table_prefix . "CHARACTER_GENERATION";
	$sql = "CREATE TABLE " . $current_table_name . " (
				ID mediumint(9)  NOT NULL  AUTO_INCREMENT,
				CHARACTER_ID mediumint(9)  NOT NULL,
				TEMPLATE_ID mediumint(9)  NOT NULL,
				NOTE_TO_ST text           NOT NULL,
				NOTE_FROM_ST text           NOT NULL,
				WORDPRESS_ID varchar(32)	NOT NULL,
				DATE_OF_APPROVAL      DATE			NOT NULL,
				EMAIL_CONFIRMED varchar(1)	NOT NULL,
				PRIMARY KEY  (ID),
				CONSTRAINT `" . $table_prefix . "char_gen_constraint_1` FOREIGN KEY (CHARACTER_ID)  REFERENCES " . $table_prefix . "CHARACTER(ID)
				) ENGINE=INNODB;";
	$for_update = dbDelta($sql);
	vtm_save_install_errors($current_table_name, $lasterror, $for_update);

	$current_table_name = $table_prefix . "MAIL_QUEUE";
	$sql = "CREATE TABLE " . $current_table_name . " (
				ID mediumint(9)  NOT NULL  AUTO_INCREMENT,
				CHARACTER_ID mediumint(9)  NOT NULL,
				MAIL_STATUS_ID mediumint(9)  NOT NULL,
				WP_POST_ID mediumint(9)  NOT NULL,
				PRIMARY KEY  (ID),
				CONSTRAINT `" . $table_prefix . "mail_status_constraint_1` FOREIGN KEY (CHARACTER_ID)  REFERENCES " . $table_prefix . "CHARACTER(ID),
				CONSTRAINT `" . $table_prefix . "mail_status_constraint_2` FOREIGN KEY (MAIL_STATUS_ID)  REFERENCES " . $table_prefix . "MAIL_STATUS(ID)
				) ENGINE=INNODB;";
	$for_update = dbDelta($sql);
	vtm_save_install_errors($current_table_name, $lasterror, $for_update);

	$current_table_name = $table_prefix . "CHARACTER_PM_ADDRESS";
	$sql = "CREATE TABLE " . $current_table_name . " (
				ID mediumint(9)  NOT NULL  AUTO_INCREMENT,
				NAME varchar(120)  NOT NULL,
				CHARACTER_ID mediumint(9)  NOT NULL,
				PM_TYPE_ID mediumint(9)  NOT NULL,
				PM_CODE varchar(60)   NOT NULL,
				DESCRIPTION tinytext      NOT NULL,
				VISIBLE varchar(1)    NOT NULL,
				ISDEFAULT varchar(1)    NOT NULL,
				DELETED varchar(1)    NOT NULL,
				PRIMARY KEY  (ID),
				CONSTRAINT `" . $table_prefix . "pm_address_constraint_1` FOREIGN KEY (CHARACTER_ID)  REFERENCES " . $table_prefix . "CHARACTER(ID),
				CONSTRAINT `" . $table_prefix . "pm_address_constraint_2` FOREIGN KEY (PM_TYPE_ID)  REFERENCES " . $table_prefix . "PM_TYPE(ID)
				) ENGINE=INNODB;";
	$for_update = dbDelta($sql);
	vtm_save_install_errors($current_table_name, $lasterror, $for_update);

	$current_table_name = $table_prefix . "CHARACTER_PM_ADDRESSBOOK";
	$sql = "CREATE TABLE " . $current_table_name . " (
				ID mediumint(9)  NOT NULL  AUTO_INCREMENT,
				CHARACTER_ID mediumint(9)  NOT NULL,
				PM_CODE varchar(60)   NOT NULL,
				NAME varchar(120)  NOT NULL,
				DESCRIPTION tinytext      NOT NULL,
				PRIMARY KEY  (ID),
				CONSTRAINT `" . $table_prefix . "pm_addressbook_constraint_1` FOREIGN KEY (CHARACTER_ID)  REFERENCES " . $table_prefix . "CHARACTER(ID)
				) ENGINE=INNODB;";
	$for_update = dbDelta($sql);
	vtm_save_install_errors($current_table_name, $lasterror, $for_update);
	
	//vtm_add_admin_notice("SQl error: " . $wpdb->last_error);
}

function vtm_character_install_data($initdatapath) {
	global $wpdb;
	
	$wpdb->show_errors();
		
	// LOAD UP THE INITIAL TABLE DATA
	$datalist = glob("$initdatapath/*.csv");
	//print_r($datalist);
	foreach ($datalist as $datafile) {
		$temp = explode(".", basename($datafile));
		$tablename = $temp[1];
		
		//echo "<p>Table: $tablename</p>";
		
		$sql = "select ID from " . VTM_TABLE_PREFIX . $tablename;
		$rows = count($wpdb->get_results($sql));
		if (!$rows) {
			$filehandle = fopen($datafile,"r");
			
			$i=0;
			$data = array();
			while(! feof($filehandle)) {
				if ($i == 0) {
					$headings = fgetcsv($filehandle,0,",");
				} else {
					$line = fgetcsv($filehandle,0,",");
					if ($line > 0) {
						$j=0;
						foreach ($headings as $heading) {
							$data[$i-1][$heading] = $line[$j];
							$j++;
						}
					}
				}
				$i++;
			}
			fclose($filehandle);

			$rowsadded = 0;
			foreach ($data as $id => $entry) {
				$rowsadded += $wpdb->insert( VTM_TABLE_PREFIX . $tablename, $entry);
			}
			
			if ($rowsadded == 0 && $rows > 0) {
				echo "<p style='color:red'>No rows added for $tablename but $rows rows in source</p>";
			}
		} else {
				echo "<p style='color:red'>Target table $tablename is not empty</p>";
		}
	}

	// SET UP THE AVAILABLE PAGES
	$data = array (
		'editCharSheet' => array(	'VALUE' => 'editCharSheet',
									'DESCRIPTION' => 'New/Edit Character Sheet',
									'WP_PAGE_ID' => '',
									'ORDERING' => 1
							),
		'viewCharSheet' => array(	'VALUE' => 'viewCharSheet',
									'DESCRIPTION' => 'View Character Sheet',
									'WP_PAGE_ID' => '',
									'ORDERING' => 2
							),
		'printCharSheet' => array(	'VALUE' => 'printCharSheet',
									'DESCRIPTION' => 'View Printable Character Sheet',
									'WP_PAGE_ID' => '',
									'ORDERING' => 3
							),
		'viewCustom' => array(		'VALUE' => 'viewCustom',
									'DESCRIPTION' => 'View Custom Page as Character',
									'WP_PAGE_ID' => '',
									'ORDERING' => 4
							),
		'viewProfile ' => array(	'VALUE' => 'viewProfile',
									'DESCRIPTION' => 'View Character Profile',
									'WP_PAGE_ID' => '',
									'ORDERING' => 5
									),
		'viewXPSpend' => array(	'VALUE' => 'viewXPSpend',
								'DESCRIPTION' => 'View XP Spend Workspace',
								'WP_PAGE_ID' => '',
								'ORDERING' => 6,
						),
		'viewExtBackgrnd' => array(	'VALUE' => 'viewExtBackgrnd',
								'DESCRIPTION' => 'View Extended Background',
								'WP_PAGE_ID' => '',
								'ORDERING' => 7,
						),
		'viewCharGen' => array(	'VALUE' => 'viewCharGen',
								'DESCRIPTION' => 'Character Generation',
								'WP_PAGE_ID' => '',
								'ORDERING' => 8,
						),
		// 'viewPM' => array(	'VALUE' => 'viewPM',
							// 'DESCRIPTION' => 'View Private Messages',
							// 'WP_PAGE_ID' => '',
							// 'ORDERING' => 9,
						// ),
	);
	foreach ($data as $key => $entry) {
		$sql = "select VALUE from " . VTM_TABLE_PREFIX . "ST_LINK where VALUE = %s;";
		$exists = count($wpdb->get_results($wpdb->prepare($sql,$key)));
		if (!$exists) 
			$rowsadded = $wpdb->insert( VTM_TABLE_PREFIX . "ST_LINK", $entry);
	}
	$sql = "SELECT ID FROM  " . VTM_TABLE_PREFIX . "ST_LINK WHERE VALUE != %s";
	for ($i = 1;$i<count(array_keys($data));$i++)
		$sql .= ' AND VALUE != %s';
	$sql = $wpdb->prepare($sql,array_keys($data));
	//echo "<p>SQL: $sql</p>";
	$results = $wpdb->get_results($sql);
	//print_r($results);
	$sql = "DELETE FROM " . VTM_TABLE_PREFIX . "ST_LINK WHERE ID = %d";
	foreach ($results as $row) {
		$result = $wpdb->get_results($wpdb->prepare($sql, $row->ID));
	}
	
}

function vtm_character_update($beforeafter) {
	global $vtm_character_version;
	global $vtm_character_db_version;
	
	$errors = 0;

	$installed_version = get_site_option( "vtm_character_version", $vtm_character_version );
	
	switch ($installed_version) {
		//--- FROM VERSION 1.9 -------------------------------------------------
		case "1.9":  $errors += vtm_character_update_1_9($beforeafter);
		case "1.10": $errors += vtm_character_update_1_10($beforeafter);
		case "1.11": $errors += vtm_character_update_1_11($beforeafter);
		case "1.12": $errors += vtm_character_update_1_11($beforeafter);
		case "2.1":  $errors += vtm_character_update_2_0($beforeafter);
	}
	
	// Incremental database updates, during development
	$db_version = get_site_option( "vtm_character_db_version", "1" );
	if ($installed_version == $vtm_character_version && $db_version != $vtm_character_db_version) {
		switch ($installed_version) {
			case "1.10": $errors += vtm_character_update_1_9($beforeafter);
			case "1.11": $errors += vtm_character_update_1_10($beforeafter);
			case "1.12": $errors += vtm_character_update_1_11($beforeafter);
			case "2.0" : $errors += vtm_character_update_1_11($beforeafter);
			case "2.1" : $errors += vtm_character_update_2_0($beforeafter);
			case "2.2" : $errors += vtm_character_update_2_0($beforeafter);
			// 2.3: no updates to database
		}
	
	}
	return $errors;

}

function vtm_remove_columns($table, $columninfo) {
	global $wpdb;

	//SHOW CREATE TABLE gvpluginwp_VTM_CHARACTER
	// gvpluginwp_VTM_CHARACTER_ibfk_8
	
	$existing_keys = $wpdb->get_col("SHOW INDEX FROM $table WHERE Key_name != 'PRIMARY';",2);
	$existing_columns = $wpdb->get_col("DESC $table", 0);
	
	/* which constraints/foreign keys to remove */
	$remove_constraints = array_intersect(array_values($columninfo), $existing_keys);
	$sql = "ALTER TABLE $table DROP FOREIGN KEY ".implode(', DROP INDEX ',$remove_constraints).';';
	
	if( !empty($remove_constraints) ) $wpdb->query($sql);			

	/* which columns to remove */
	$remove_columns = array_intersect(array_keys($columninfo), $existing_columns);
	$sql = "ALTER TABLE $table DROP COLUMN ".implode(', DROP COLUMN ',$remove_columns).';';
	
	if( !empty($remove_columns) ) $wpdb->query($sql); 

}

function vtm_remove_constraint($table, $constraint) {
	global $wpdb;

	
	$existing_keys = $wpdb->get_col("SHOW INDEX FROM $table WHERE Key_name != 'PRIMARY';",2);
	
	/* which constraints/foreign keys to remove */
	$remove_constraints = array_intersect(array($constraint), $existing_keys);
	$sql = "ALTER TABLE $table DROP FOREIGN KEY ".implode(', DROP INDEX ',$remove_constraints).';';
	
	/* do remove */
	if( !empty($remove_constraints) ) $wpdb->query($sql);			


}

function vtm_add_constraint($table, $constraint, $foreignkey, $reference) {
	global $wpdb;

	$existing_keys = $wpdb->get_col("SHOW INDEX FROM $table WHERE Key_name != 'PRIMARY';",2);
	
	$constraint = VTM_TABLE_PREFIX . $constraint;
	$reference  = VTM_TABLE_PREFIX . $reference;
	
	/* which constraints/foreign keys to add */
	$check_constraints = array_intersect(array($constraint), $existing_keys);
	$sql = "ALTER TABLE $table ADD CONSTRAINT $constraint FOREIGN KEY ($foreignkey) REFERENCES $reference;";
	
	//echo "SQL: $sql<br />";
	
	/* do add */
	if( empty($check_constraints) ) $wpdb->query($sql);			


}

function vtm_table_exists($table, $prefix = VTM_TABLE_PREFIX) {
	global $wpdb;

	$sql = "SHOW TABLES LIKE '" . $prefix . $table . "'";
	$result = $wpdb->get_results($sql);
	$tableExists = count($result) > 0;
	
	//echo "<p>Table $table exists: $tableExists ($sql)</p>";
	
	return $tableExists;
}

function vtm_column_exists($table, $column) {
	global $wpdb;

	$sql = "DESC $table";
	$existing_columns = $wpdb->get_col($sql, 0);
	$match_columns = array_intersect(array($column), $existing_columns);
	
	//print_r($existing_columns);
	//print_r($column);
	//print_r($match_columns);
	//echo "<li>SQL: $sql -->" . count($match_columns) . "</li>";
	
	return count($match_columns);
}
function vtm_rename_column($columninfo) {
	global $wpdb;

	//print_r($columninfo);
	
	$table = $columninfo['table'];
	
	$sql = "SHOW INDEX FROM $table WHERE Key_name != 'PRIMARY';";
	//echo "<p>indexes: $sql</p>";
	$existing_keys = $wpdb->get_col("SHOW INDEX FROM $table WHERE Key_name != 'PRIMARY';",2);
	$existing_columns = $wpdb->get_col("DESC $table", 0);
	
	$remove_constraints = array_intersect(array($columninfo['from']), $existing_keys);
	$sql = "ALTER TABLE $table DROP FOREIGN KEY {$columninfo['constraint']};";
	//echo "<p>rem constraint: $sql</p>";
	if( !empty($remove_constraints) ) $wpdb->query($sql);	
	
	$rename_columns = array_intersect(array($columninfo['from']), $existing_columns);
	$sql = "ALTER TABLE $table CHANGE {$columninfo['from']} {$columninfo['to']} {$columninfo['definition']};";
	//echo "<p>rename col: $sql</p>";
	if (!empty($rename_columns)) $wpdb->query($sql);

	$sql = "ALTER TABLE $table ADD CONSTRAINT {$columninfo['constraint']} FOREIGN KEY ({$columninfo['to']}) REFERENCES {$columninfo['reference']};";
	//echo "<p>add constraint: $sql</p>";
	if( !empty($remove_constraints) ) $wpdb->query($sql);	
	

}

function vtm_rename_table($from, $to, $prefixfrom = VTM_TABLE_PREFIX, $prefixto = VTM_TABLE_PREFIX) {
	global $wpdb;

	$sql = "RENAME TABLE " . $prefixfrom . $from . " TO " . $prefixto . $to;
	//echo "<p>rename sql: $sql</p>";
	$result = $wpdb->get_results($sql);

}

function vtm_character_update_1_9($beforeafter) {
	global $wpdb;
	
	
	if ( $beforeafter == 'before') {
		//echo "<p>Setting up tables</p>";
	
		// Rename GVLARP_ tables to VTM_ tables
		$oldprefix = $wpdb->prefix . "GVLARP_";
		$sql = "SHOW TABLES LIKE %s";
		$sql = $wpdb->prepare($sql, $oldprefix . "%");
		$result = $wpdb->get_col($sql);
		if (count($result) > 0) {
			foreach ($result as $table) {
				$newtable = str_replace($oldprefix, VTM_TABLE_PREFIX, $table);
				
				$sql = "SHOW TABLES LIKE %s";
				$sql = $wpdb->prepare($sql, $newtable);
				$result = $wpdb->get_results($sql);
				//echo "<p>SQL: $sql</p>";
				
				if (count($result) == 0) {
					$sql = "RENAME TABLE $table TO $newtable";
					$result = $wpdb->query($sql);
					if (isset($result) && $result === false) {
						$errors++;
					}
				}
			}
			
		}
		
		// Remove some columns that may have been created while developing this version
		$remove = array (
			'CHARGEN_TEMPLATE_ID' => '',
			'CHARGEN_NOTE_TO_ST' => '',
			'CHARGEN_NOTE_FROM_ST' => ''
		);
		vtm_remove_columns(VTM_TABLE_PREFIX . "CHARACTER", $remove);

	} else {
	
		//echo "<p>Updating data</p>";
		$wpdb->show_errors();

		// Add Character Generation Status to all characters
		$sql = "SELECT ID FROM " . VTM_TABLE_PREFIX . "CHARGEN_STATUS WHERE NAME = 'Approved'";
		$approvedid = $wpdb->get_var($sql);
		$sql = "SELECT ID FROM " . VTM_TABLE_PREFIX . "CHARACTER WHERE ISNULL(CHARGEN_STATUS_ID) OR CHARGEN_STATUS_ID = 0";
		//echo "<p>SQL: $sql</p>";
		$result = $wpdb->get_col($sql);
		//print_r($result);
		if (count($result) > 0) {
			foreach ($result as $characterID) {
				$wpdb->update(VTM_TABLE_PREFIX . "CHARACTER",
					array('CHARGEN_STATUS_ID' => $approvedid),
					array('ID' => $characterID)
				);
				
			}
		}
		
		// Add subscriber as default wordpress role to clan table
		$sql = "SELECT ID FROM " . VTM_TABLE_PREFIX . "CLAN WHERE WORDPRESS_ROLE = ''";
		$result = $wpdb->get_col($sql);
		//echo "<li>SQL: $sql</li>";
		//print_r($result);
		if (count($result) > 0) {
			foreach ($result as $clanID) {
				$wpdb->update(VTM_TABLE_PREFIX . "CLAN",
					array('WORDPRESS_ROLE' => 'subscriber'),
					array('ID' => $clanID)
				);
			}
		}
		
		// Copy in initial values for the new CHARACTER EMAIL column
		$sql = "SELECT ch.ID, ch.WORDPRESS_ID, ch.NAME 
			FROM 
				" . VTM_TABLE_PREFIX . "CHARACTER ch,
				" . VTM_TABLE_PREFIX . "CHARACTER_STATUS cs
			WHERE
				ch.CHARACTER_STATUS_ID = cs.ID
				AND ch.EMAIL = ''
				AND cs.NAME = 'Alive'
				AND ch.VISIBLE = 'Y'
				AND ch.WORDPRESS_ID != ''";
		//echo "<p>SQL: $sql</p>";
		$result = $wpdb->get_results($sql);
		if (count($result) > 0) {
			foreach ($result as $row) {
				$userdata = get_user_by( 'login', $row->WORDPRESS_ID );
				if ($userdata) {
					//echo "<li>Email address of {$row->NAME} ({$row->WORDPRESS_ID}) is {$userdata->user_email}</li>";
					$wpdb->update(VTM_TABLE_PREFIX . "CHARACTER",
						array('EMAIL' => $userdata->user_email),
						array('ID' => $row->ID)
					);
				} //else {
				//	echo "<li>No account created for {$row->NAME} ({$row->WORDPRESS_ID})</li>";
				//}
			}
		}
	
		// Add new foreign key(s)
		vtm_add_constraint(VTM_TABLE_PREFIX . "CHARACTER", "char_constraint_10", "CHARGEN_STATUS_ID", "CHARGEN_STATUS(ID)");
	
	}

}

function vtm_character_update_1_10($beforeafter) {
	global $wpdb;
	
	if ( $beforeafter == 'before') {
		//echo "<p>Setting up tables</p>";

	} else {
	
		//echo "<p>Updating data</p>";
		$wpdb->show_errors();

		// Add Cost Model for Paths of Enlightenment
		$modelid = $wpdb->get_var("SELECT ID FROM " . VTM_TABLE_PREFIX . "COST_MODEL WHERE NAME = 'RoadOrPath'");
		if (!$modelid) {
			$wpdb->insert(VTM_TABLE_PREFIX . "COST_MODEL",
				array(
					'NAME' => 'RoadOrPath',
					'DESCRIPTION' => 'Paths of Enlightenment'
				),
				array('%s', '%s')
			);
			$modelid = $wpdb->insert_id;
			
			for ($i=0;$i<11;$i++) {
						
				$dataarray = array (
					'COST_MODEL_ID'   => $modelid,
					'SEQUENCE'        => $i+1,
					'CURRENT_VALUE'   => $i,
					'NEXT_VALUE'      => ($i == 10 ? 10 : $i + 1),
					'FREEBIE_COST'    => ($i == 10 ? 0 : 2),
					'XP_COST'         => ($i == 10 ? 0 : $i * 2)
				);
				
				$wpdb->insert(VTM_TABLE_PREFIX . "COST_MODEL_STEP",
					$dataarray,
					array (
						'%d',
						'%d',
						'%d',
						'%d',
						'%d',
						'%d'
					)
				);
			}
		}
		
		// Add Cost Model to Paths of Enlightenment
		$sql = "SELECT ID FROM " . VTM_TABLE_PREFIX . "ROAD_OR_PATH WHERE ISNULL(COST_MODEL_ID) OR (NOT(ISNULL(COST_MODEL_ID)) AND COST_MODEL_ID = 0)";
		//echo "<p>SQL: $sql</p>";
		$result = $wpdb->get_col($sql);
		//print_r($result);
		if (count($result) > 0) {
			foreach ($result as $roadid) {
				$wpdb->update(VTM_TABLE_PREFIX . "ROAD_OR_PATH",
					array('COST_MODEL_ID' => $modelid),
					array('ID' => $roadid)
				);
			}
		}
	
	
	}

}
function vtm_character_update_1_11($beforeafter) {
	global $wpdb;
	
	if ( $beforeafter == 'before') {
		//echo "<p>Setting up tables</p>";

	} else {
	
		// Go through SKILL table and work out what the skill type is
		// from the GROUPING. Use 'Other Traits' if no match
		
		// if grouping column exists
		if (vtm_column_exists(VTM_TABLE_PREFIX . "SKILL","GROUPING")) {
			$sql = "SELECT NAME, ID, PARENT_ID FROM " . VTM_TABLE_PREFIX . "SKILL_TYPE";
			$types = $wpdb->get_results($sql, OBJECT_K);
			$types = vtm_sanitize_array($types);
			//print_r($types);
			
			$sql = "SELECT ID, GROUPING, NAME FROM " . VTM_TABLE_PREFIX . "SKILL";
			$result = $wpdb->get_results($sql);
			//print_r($result);
			//echo "<li>Updating...</li>";
			if (count($result) > 0) {
				foreach ($result as $row) {
					$grp = sanitize_key($row->GROUPING);
					if (isset($types[$grp])) {
						$typeid = $types[$grp]->ID;
					}
					// remove 's' at the end of the line
					elseif (isset($types[chop($grp,'s')])) {
						$typeid = $types[chop($grp,'s')]->ID;
					}
					else {
						$typeid = $types["othertraits"]->ID;
					}
					//echo "<li>Update {$row->NAME}, ID: {$row->ID} with skill type {$grp}, type ID: $typeid</li>";
					$wpdb->update(VTM_TABLE_PREFIX . "SKILL",
						array('SKILL_TYPE_ID' => $typeid),
						array('ID' => $row->ID)
					);
				}
			}
		
			// Remove SKILL column
			//GROUPING varchar(30)   NOT NULL,
			$remove = array (
				'GROUPING' => '',
			);
			vtm_remove_columns(VTM_TABLE_PREFIX . "SKILL", $remove);
		}
		
		// Fill in ST_LINK Page IDs
		$result = vtm_column_exists(VTM_TABLE_PREFIX . "ST_LINK","LINK");
		if ($result > 0) {
			$sql = "SELECT VALUE, ID, LINK FROM " . VTM_TABLE_PREFIX . "ST_LINK";
			$links = $wpdb->get_results($sql, OBJECT_K);
			$args = array(
				'sort_order' => 'ASC',
				'sort_column' => 'post_title',
				'hierarchical' => 0,
				'exclude' => '',
				'include' => '',
				'meta_key' => '',
				'meta_value' => '',
				'authors' => '',
				'child_of' => 0,
				'parent' => -1,
				'exclude_tree' => '',
				'number' => '',
				'offset' => 0,
				'post_type' => 'page',
				'post_status' => 'publish'
			); 
			$pages = get_pages($args);
			$pageinfo = array();
			foreach ( $pages as $page ) {
				$pageinfo['/' . get_page_uri( $page->ID )] = $page->ID;
			}

			foreach ($links as $key => $info) {
					if (isset($pageinfo[$info->LINK])) {
						$wpdb->update(VTM_TABLE_PREFIX . "ST_LINK",
							array('WP_PAGE_ID' => $pageinfo[$info->LINK]),
							array('ID' => $info->ID)
						);
					}
			}
			$remove = array (
				'LINK' => ''
			);
			vtm_remove_columns(VTM_TABLE_PREFIX . "ST_LINK", $remove);

			
		}
		
		// Fill in default 'N' for new background column "HAS_SPECIALISATION"
		$sql = "SELECT ID FROM " . VTM_TABLE_PREFIX . "BACKGROUND WHERE HAS_SPECIALISATION = ''";
		$result = $wpdb->get_results($sql);
		foreach ($result as $bg) {
					$wpdb->update(VTM_TABLE_PREFIX . "BACKGROUND",
						array('HAS_SPECIALISATION' => 'N'),
						array('ID' => $bg->ID)
					);
		}

		// Fill in default 'Y' for new character column "GET_NEWSLETTER"
		$sql = "SELECT ID FROM " . VTM_TABLE_PREFIX . "CHARACTER WHERE GET_NEWSLETTER = ''";
		$result = $wpdb->get_results($sql);
		foreach ($result as $bg) {
					$wpdb->update(VTM_TABLE_PREFIX . "CHARACTER",
						array('GET_NEWSLETTER' => 'Y'),
						array('ID' => $bg->ID)
					);
		}

		
	}

}

function vtm_character_update_2_0($beforeafter) {
	global $wpdb;
	
	if ( $beforeafter == 'before') {

	} else {
		$remove = array (
			'PREFIX' => ''
		);
		vtm_remove_columns(VTM_TABLE_PREFIX . "PM_TYPE", $remove);
		
	}
}

add_action('activated_plugin','save_error');
function save_error(){
	$outputbuffer = ob_get_contents();
    update_option('vtm_plugin_error',  
		get_option('vtm_plugin_error') . $outputbuffer);
}

// Display notice with version update
function vtm_install_notice() {
	if (vtm_isST()) {
		
		// Display admin notices
		$notices = get_option('vtm_admin_notices');
		if (isset($notices) && $notices != "") {
			echo "<div class='updated'>$notices | <a href='?vtm_ignore_notice'>Dismiss</a></p></div>";
		}
		
		// Display plugin activation errors 
		$activation_output = get_option('vtm_plugin_error');
		if (isset($activation_output) && $activation_output != "") {
			echo "<div class='error'><p>$activation_output | <a href='?vtm_ignore_activation_output'>Dismiss</a></p></div>";
		}
		
	}
}
add_action('admin_notices', 'vtm_install_notice');


function vtm_install_notice_ignore() {
	
	if ( isset($_GET['vtm_ignore_notice']) ) {
		update_option('vtm_admin_notices',  '');
	}
	
	if ( isset($_GET['vtm_ignore_activation_output']) ) {
		update_option('vtm_plugin_error',  '');
	}

}
add_action('admin_init', 'vtm_install_notice_ignore');

function vtm_add_admin_notice($text) {
	update_option('vtm_admin_notices', 
		get_option('vtm_admin_notices') . $text);
}

function vtm_save_install_errors($table, $lasterror, $for_update = array()) {
	global $wpdb;
	
	$errtext1 = "You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near 'CONSTRAINT";
	$errtext2 = "FOREIGN KEY (";
	
	$error = $wpdb->last_error;
	//$outputbuffer = ob_get_contents();
	//vtm_add_admin_notice($outputbuffer);
	
	// Display what database updates were made by dbDelta
	if (empty($error) && count($for_update) > 0 ) {
		$erroutput = "";
		foreach ($for_update as $update) {
			if (!strstr($update, 'Created table')) {
				$erroutput .= $update . ".<br />";
			}
		}
		if (!empty($erroutput)) {
			$erroutput = get_option('vtm_plugin_error') . "$table:'$erroutput'<br />\n";
			update_option('vtm_plugin_error',  $erroutput);
		}
		return;
	}
	// Ignore the constraint/foreign key errors as dbDelta is 
	// wrongly forming the SQL for these
	elseif (strstr($error,$errtext1) && strstr($error,$errtext2))
		return;
	elseif (!empty($error)) {
		// save error
		$erroutput = get_option('vtm_plugin_error');
		$erroutput .= $table . ":" . $error . "<br />\n";
		update_option('vtm_plugin_error',  $erroutput);
		return;
	}
}

function vtm_define_tables() {
	
	// level 5 tables
	$tables[] = array(
		'CHARACTER_PM_ADDRESSBOOK',
		'CHARACTER_PM_ADDRESS',
		'MAIL_QUEUE',
		'CHARACTER_GENERATION',
		'CHARACTER_EXTENDED_BACKGROUND',
		'CHARACTER_PROFILE',
		'CHARACTER_COMBO_DISCIPLINE',
		'CHARACTER_BACKGROUND',
		'CHARACTER_SKILL',
		'CHARACTER_MERIT',
		'CHARACTER_DISCIPLINE_POWER',
		'CHARACTER_PATH_POWER',
		'CHARACTER_PATH',
		'CHARACTER_DISCIPLINE',
		'CHARACTER_RITUAL',
		'CHARACTER_STAT',
		'CHARACTER_TEMPORARY_STAT',
		'CHARACTER_ROAD_OR_PATH',
		'PENDING_FREEBIE_SPEND',
		'PENDING_XP_SPEND',
		'PLAYER_XP',
		'CHARACTER_OFFICE',
	);
	// level 4 tables
	$tables[] = array(
		'PATH_POWER',
		'CHARACTER',
	);
	// level 3 tables
	$tables[] = array(
		'COMBO_DISCIPLINE_PREREQUISITE',
		'CLAN_DISCIPLINE',
		'RITUAL',
		'DISCIPLINE_POWER',
		'PATH',
		'ROAD_OR_PATH',
	);
	// level 2 tables
	$tables[] = array(
		'MAPDOMAIN',
		'CONFIG',
		'COMBO_DISCIPLINE',
		'DISCIPLINE',
		'MERIT',
		'BACKGROUND',
		'SKILL',
		'STAT',
		'CLAN',
		'COST_MODEL_STEP',
		'PLAYER',
		'CHARGEN_TEMPLATE_MAXIMUM',
		'CHARGEN_TEMPLATE_DEFAULTS',
		'CHARGEN_TEMPLATE_OPTIONS',
	);
	// level 1 tables
	$tables[] = array(
		'PM_TYPE',
		'MAIL_STATUS',
		'SKILL_TYPE',
		'CHARGEN_STATUS',
		'CHARGEN_TEMPLATE',
		'MAPOWNER',
		'PROFILE_DISPLAY',
		'EXTENDED_BACKGROUND',
		'SECTOR',
		'TEMPORARY_STAT',
		'NATURE',
		'GENERATION',
		'SOURCE_BOOK',
		'SECT',
		'DOMAIN',
		'COST_MODEL',
		'CHARACTER_STATUS',
		'CHARACTER_TYPE',
		'TEMPORARY_STAT_REASON',
		'PATH_REASON',
		'XP_REASON',
		'OFFICE',
		'ST_LINK',
		'PLAYER_STATUS',
		'PLAYER_TYPE',
	);

	return $tables;
}

function vtm_factory_defaults() {
	global $wpdb;
	
	$tables = vtm_define_tables();
	
	foreach ($tables as $tablelist) {
		foreach ($tablelist as $id => $table) {
			$tablelist[$id] = VTM_TABLE_PREFIX . $table;
		}
		$list = implode(', ', $tablelist);
		$sql = "DROP TABLE $list";
		//echo "<p>SQL: $sql</p>";
		$wpdb->query($sql);
	}
	
    vtm_character_install();
	
	echo "<p>Databases reset to factory defaults</p>";
}

function vtm_export_data($filepath, $dirname) {
	global $wpdb;
	global $wp_filesystem;	
	
	$creds = request_filesystem_credentials(site_url() . '/wp-admin/', '', false, false, array());
	if ( ! WP_Filesystem($creds) ) {
		return false;
	}	
	
	$path = $filepath . "/$dirname";
	if(!$wp_filesystem->is_dir($path)) {
		$wp_filesystem->mkdir($path);
	}
	
	$tables = vtm_define_tables();
	
	for ($i = 0 ; $i < count($tables) ; $i++) {
		$lvl = count($tables) - $i;
		$tablelist = $tables[$i];
		for ($id = 0 ; $id < count($tablelist) ; $id++) {
			$table = $tablelist[$id];
			$filename = sprintf("%'02s-%'03s.%s.csv", $lvl, $id+1, $table);
			
			$sql = "SELECT * FROM " . VTM_TABLE_PREFIX . "$table ORDER BY ID";
			$contents = $wpdb->get_results($sql);
			
			$sql = "SHOW COLUMNS FROM " . VTM_TABLE_PREFIX . $table;
			$info = $wpdb->get_results($sql);
			foreach ($info as $index => $data) {
				$headings[] = $data->Field;
			}
			
			//echo "<li>$path/$filename</li>";
			//print_r($contents);
			//echo "</li>";
			// Open CSV file
			$file = fopen("$path/$filename","w");
			// output headings
			//print_r($headings);
			fputcsv($file, $headings);
			// output contents
			if (count($contents) > 0) {
				foreach ($contents as $data) {
					foreach ($headings as $heading) {
						$row[] = $data->$heading;
					}
					//echo "<li>row:";
					//print_r($row);
					//echo "<li>";
					fputcsv($file, $row);
					unset($row);
				}
			}
			
			// close file
			fclose($file);
			unset($headings);
			
		}
	}
	
	// zip up directory
	//create the archive
	$zipfilename = "$filepath/$dirname.zip";
	//echo "<p>Creating zip: $zipfilename</p>";
	$zip = new ZipArchive();
	$zip->open($zipfilename, ZipArchive::CREATE);
	for ($i = 0 ; $i < count($tables) ; $i++) {
		$lvl = count($tables) - $i;
		$tablelist = $tables[$i];
		for ($id = 0 ; $id < count($tablelist) ; $id++) {
			$table = $tablelist[$id];
			$filename = sprintf("%'02s-%'03s.%s.csv", $lvl, $id+1, $table);
			//echo "<li>Adding file: $path/$filename, $dirname/$filename</li>";
			$zip->addFile("$path/$filename", "$dirname/$filename");
		}
	}
	$zip->close();
	
	return "$dirname.zip";
}

function vtm_is_valid_import_version($version) {
	global $vtm_character_version;
	
	if ($version == "vtm-export-$vtm_character_version")
		return 1;

}

?>