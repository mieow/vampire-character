<?php

register_activation_hook(__FILE__, "vtm_character_install");
register_activation_hook( __FILE__, 'vtm_character_install_data' );

global $vtm_character_version;
global $vtm_character_db_version;
$vtm_character_version = "2.0"; 
$vtm_character_db_version = "51"; 

function vtm_update_db_check() {
    global $vtm_character_version;
    global $vtm_character_db_version;
	
    if (get_option( 'vtm_character_db_version' ) != $vtm_character_db_version ||
		get_option( 'vtm_character_version' ) != $vtm_character_version) {
		
		echo "<p>Updating from " . get_option( 'vtm_character_version' ) . "." . get_option( 'vtm_character_db_version' );
		echo " to  $vtm_character_version.$vtm_character_db_version</p>";
		
        $errors = vtm_character_update('before');
        vtm_character_install();
		vtm_character_install_data();
        $errors += vtm_character_update('after');
				
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
	
	//$wpdb->show_errors();
	
	$table_prefix = VTM_TABLE_PREFIX;
	$installed_version = get_site_option( "vtm_character_db_version" );
	
//	if( $installed_version != $vtm_character_db_version ) {
	
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	
		// LEVEL 1 TABLES - TABLES WITHOUT FOREIGN KEY CONSTRAINTS
	
		$current_table_name = $table_prefix . "PLAYER_TYPE";
		$sql = "CREATE TABLE " . $current_table_name . " (
					ID           MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
					NAME         VARCHAR(16)  NOT NULL,
					DESCRIPTION  TINYTEXT     NOT NULL,
					PRIMARY KEY  (ID)
					) ENGINE=INNODB;";
		dbDelta($sql);

		$current_table_name = $table_prefix . "PLAYER_STATUS";
		$sql = "CREATE TABLE " . $current_table_name . " (
					ID           MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
					NAME         VARCHAR(16)  NOT NULL,
					DESCRIPTION  TINYTEXT     NOT NULL,
					PRIMARY KEY  (ID)
					) ENGINE=INNODB;";
		dbDelta($sql);

		$current_table_name = $table_prefix . "ST_LINK";
		$sql = "CREATE TABLE " . $current_table_name . " (
					ID           MEDIUMINT(9) NOT NULL  AUTO_INCREMENT,
					VALUE        VARCHAR(32)  NOT NULL,
					DESCRIPTION  TINYTEXT     NOT NULL,
					LINK         TINYTEXT     NOT NULL,
					WP_PAGE_ID   MEDIUMINT(9) NOT NULL,
					ORDERING     SMALLINT(3)  NOT NULL,
					PRIMARY KEY  (ID)
					) ENGINE=INNODB;";
		dbDelta($sql);

		$current_table_name = $table_prefix . "OFFICE";
		$sql = "CREATE TABLE " . $current_table_name . " (
					ID           MEDIUMINT(9) NOT NULL  AUTO_INCREMENT,
					NAME         VARCHAR(32)  NOT NULL,
					DESCRIPTION  TINYTEXT     NOT NULL,
					ORDERING     SMALLINT(3)  NOT NULL,
					VISIBLE      VARCHAR(1)   NOT NULL,
					PRIMARY KEY  (ID)
					) ENGINE=INNODB;";
		dbDelta($sql);

		$current_table_name = $table_prefix . "XP_REASON";
		$sql = "CREATE TABLE " . $current_table_name . " (
					ID           MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
					NAME         VARCHAR(16)  NOT NULL,
					DESCRIPTION  TINYTEXT     NOT NULL,
					PRIMARY KEY  (ID)
					) ENGINE=INNODB;";
		dbDelta($sql);

		$current_table_name = $table_prefix . "PATH_REASON";
		$sql = "CREATE TABLE " . $current_table_name . " (
					ID           MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
					NAME         VARCHAR(24)  NOT NULL,
					DESCRIPTION  TINYTEXT     NOT NULL,
					PRIMARY KEY  (ID)
					) ENGINE=INNODB;";
		dbDelta($sql);

		$current_table_name = $table_prefix . "TEMPORARY_STAT_REASON";
		$sql = "CREATE TABLE " . $current_table_name . " (
					ID           MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
					NAME         VARCHAR(16)  NOT NULL,
					DESCRIPTION  TINYTEXT     NOT NULL,
					PRIMARY KEY  (ID)
					) ENGINE=INNODB;";
		dbDelta($sql);

		$current_table_name = $table_prefix . "CHARACTER_TYPE";
		$sql = "CREATE TABLE " . $current_table_name . " (
					ID           MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
					NAME         VARCHAR(16)  NOT NULL,
					DESCRIPTION  TINYTEXT     NOT NULL,
					PRIMARY KEY  (ID)
					) ENGINE=INNODB;";
		dbDelta($sql);

		$current_table_name = $table_prefix . "CHARACTER_STATUS";
		$sql = "CREATE TABLE " . $current_table_name . " (
					ID           MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
					NAME         VARCHAR(16)  NOT NULL,
					DESCRIPTION  TINYTEXT     NOT NULL,
					PRIMARY KEY  (ID)
					) ENGINE=INNODB;";
		dbDelta($sql);

		$current_table_name = $table_prefix . "COST_MODEL";
		$sql = "CREATE TABLE " . $current_table_name . " (
					ID           MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
					NAME         VARCHAR(16)  NOT NULL,
					DESCRIPTION  TINYTEXT     NOT NULL,
					PRIMARY KEY  (ID)
					) ENGINE=INNODB;";
		dbDelta($sql);

		$current_table_name = $table_prefix . "DOMAIN";
		$sql = "CREATE TABLE " . $current_table_name . " (
					ID           MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
					NAME         VARCHAR(16)  NOT NULL,
					DESCRIPTION  TINYTEXT     NOT NULL,
					VISIBLE      VARCHAR(1)   NOT NULL,
					PRIMARY KEY  (ID)
					) ENGINE=INNODB;";
		dbDelta($sql);

		$current_table_name = $table_prefix . "SECT";
		$sql = "CREATE TABLE " . $current_table_name . " (
					ID           MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
					NAME         VARCHAR(16)  NOT NULL,
					DESCRIPTION  TINYTEXT     NOT NULL,
					VISIBLE      VARCHAR(1)   NOT NULL,
					PRIMARY KEY  (ID)
					) ENGINE=INNODB;";
		dbDelta($sql);
		
		$current_table_name = $table_prefix . "SOURCE_BOOK";
		$sql = "CREATE TABLE " . $current_table_name . " (
					ID           MEDIUMINT(9)  NOT NULL   AUTO_INCREMENT,
					CODE         VARCHAR(16)   NOT NULL,
					NAME         VARCHAR(60)   NOT NULL,
					VISIBLE      VARCHAR(1)    NOT NULL,
					PRIMARY KEY  (ID)
					) ENGINE=INNODB;";
		dbDelta($sql);
		
		$current_table_name = $table_prefix . "GENERATION";
		$sql = "CREATE TABLE " . $current_table_name . " (
					ID              MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
					NAME            VARCHAR(16)  NOT NULL,
					BLOODPOOL       SMALLINT(3)  NOT NULL,
					BLOOD_PER_ROUND SMALLINT(2)  NOT NULL,
					MAX_RATING      SMALLINT(2)  NOT NULL,
					MAX_DISCIPLINE  SMALLINT(2)  NOT NULL,
					PRIMARY KEY  (ID)
					) ENGINE=INNODB;";
		dbDelta($sql);

		$current_table_name = $table_prefix . "NATURE";
		$sql = "CREATE TABLE " . $current_table_name . " (
					ID              MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
					NAME            VARCHAR(16)  NOT NULL,
					DESCRIPTION     TINYTEXT     NOT NULL,
					PRIMARY KEY  (ID)
					) ENGINE=INNODB;";
		dbDelta($sql);

		$current_table_name = $table_prefix . "TEMPORARY_STAT";
		$sql = "CREATE TABLE " . $current_table_name . " (
					ID              MEDIUMINT(9)  NOT NULL  AUTO_INCREMENT,
					NAME            VARCHAR(60)   NOT NULL,
					DESCRIPTION     TINYTEXT      NOT NULL,
					VISIBLE         VARCHAR(1)    NOT NULL,
					PRIMARY KEY  (ID)
					) ENGINE=INNODB;";
		dbDelta($sql);

		$current_table_name = $table_prefix . "SECTOR";
		$sql = "CREATE TABLE " . $current_table_name . " (
					ID              MEDIUMINT(9)  NOT NULL  AUTO_INCREMENT,
					NAME            VARCHAR(16)   NOT NULL,
					DESCRIPTION     TINYTEXT      NOT NULL,
					VISIBLE         VARCHAR(1)    NOT NULL,
					PRIMARY KEY  (ID)
					) ENGINE=INNODB;";
		dbDelta($sql);

		$current_table_name = $table_prefix . "EXTENDED_BACKGROUND";
		$sql = "CREATE TABLE " . $current_table_name . " (
					ID                    MEDIUMINT(9)  NOT NULL  AUTO_INCREMENT,
					ORDERING              SMALLINT(4)   NOT NULL,
					GROUPING              VARCHAR(90)   NOT NULL,
					TITLE                 VARCHAR(90)   NOT NULL,
					BACKGROUND_QUESTION   TEXT   		NOT NULL,
					VISIBLE				  VARCHAR(1)    NOT NULL,
					REQD_AT_CHARGEN		  VARCHAR(1)    NOT NULL,
					PRIMARY KEY  (ID)
					) ENGINE=INNODB;";
		dbDelta($sql);
		
		$current_table_name = $table_prefix . "PROFILE_DISPLAY";
		$sql = "CREATE TABLE " . $current_table_name . " (
					ID			MEDIUMINT(9)	NOT NULL  AUTO_INCREMENT,
					NAME		TEXT			NOT NULL,
					PRIMARY KEY  (ID)
					) ENGINE=INNODB;";
		dbDelta($sql);

		$current_table_name = $table_prefix . "MAPOWNER";
		$sql = "CREATE TABLE " . $current_table_name . " (
					ID              MEDIUMINT(9)	NOT NULL   AUTO_INCREMENT,
					NAME            VARCHAR(60)		NOT NULL,
					FILL_COLOUR     VARCHAR(7)		NOT NULL,
					VISIBLE         VARCHAR(1)		NOT NULL,
					PRIMARY KEY  (ID)
					) ENGINE=INNODB;";
		dbDelta($sql);
		
		$current_table_name = $table_prefix . "CHARGEN_TEMPLATE";
		$sql = "CREATE TABLE " . $current_table_name . " (
					ID              MEDIUMINT(9)	NOT NULL   AUTO_INCREMENT,
					NAME            VARCHAR(60)		NOT NULL,
					DESCRIPTION     TINYTEXT      	NOT NULL,
					VISIBLE         VARCHAR(1)		NOT NULL,
					PRIMARY KEY  (ID)
					) ENGINE=INNODB;";
		dbDelta($sql);
		
		$current_table_name = $table_prefix . "CHARGEN_STATUS";
		$sql = "CREATE TABLE " . $current_table_name . " (
					ID              MEDIUMINT(9)	NOT NULL   AUTO_INCREMENT,
					NAME            VARCHAR(60)		NOT NULL,
					DESCRIPTION     TINYTEXT      	NOT NULL,
					PRIMARY KEY  (ID)
					) ENGINE=INNODB;";
		dbDelta($sql);
		
		$current_table_name = $table_prefix . "SKILL_TYPE";
		$sql = "CREATE TABLE " . $current_table_name . " (
					ID              MEDIUMINT(9)	NOT NULL   AUTO_INCREMENT,
					NAME            VARCHAR(60)		NOT NULL,
					PARENT_ID       MEDIUMINT(9)	NOT NULL,
					DESCRIPTION     TINYTEXT      	NOT NULL,
					ORDERING        SMALLINT(4)     NOT NULL,
					PRIMARY KEY  (ID)
					) ENGINE=INNODB;";
		dbDelta($sql);
		
		$current_table_name = $table_prefix . "MAIL_STATUS";
		$sql = "CREATE TABLE " . $current_table_name . " (
					ID              MEDIUMINT(9)	NOT NULL   AUTO_INCREMENT,
					NAME            VARCHAR(60)		NOT NULL,
					DESCRIPTION     TINYTEXT      	NOT NULL,
					PRIMARY KEY  (ID)
					) ENGINE=INNODB;";
		dbDelta($sql);

		// LEVEL 2 TABLES - TABLES WITH A FOREIGN KEY CONSTRAINT TO A LEVEL 1 TABLE
		
		$current_table_name = $table_prefix . "CHARGEN_TEMPLATE_OPTIONS";
		$sql = "CREATE TABLE " . $current_table_name . " (
					ID              MEDIUMINT(9)	NOT NULL   AUTO_INCREMENT,
					NAME            VARCHAR(60)		NOT NULL,
					VALUE    		TINYTEXT      	NOT NULL,
					TEMPLATE_ID		MEDIUMINT(9)	NOT NULL,
					PRIMARY KEY  (ID),
					CONSTRAINT `" . $table_prefix . "template_constraint_1` FOREIGN KEY (TEMPLATE_ID)   REFERENCES " . $table_prefix . "CHARGEN_TEMPLATE(ID)
					) ENGINE=INNODB;";
		dbDelta($sql);

		$current_table_name = $table_prefix . "CHARGEN_TEMPLATE_DEFAULTS";
		$sql = "CREATE TABLE " . $current_table_name . " (
					ID              MEDIUMINT(9)	NOT NULL   AUTO_INCREMENT,
					TEMPLATE_ID		MEDIUMINT(9)	NOT NULL,
					CHARTABLE       TINYTEXT        NOT NULL,
					ITEMTABLE       TINYTEXT        NOT NULL,
					ITEMTABLE_ID    MEDIUMINT(9)    NOT NULL,
					SECTOR_ID       MEDIUMINT(9)    NOT NULL,
					SPECIALISATION  VARCHAR(64)	    NOT NULL,
					LEVEL  	        MEDIUMINT(9)    NOT NULL,
					MULTIPLE        VARCHAR(1)		NOT NULL,
					PRIMARY KEY  (ID),
					CONSTRAINT `" . $table_prefix . "template_default_constraint_1` FOREIGN KEY (TEMPLATE_ID)   REFERENCES " . $table_prefix . "CHARGEN_TEMPLATE(ID)
					) ENGINE=INNODB;";
		dbDelta($sql);

		$current_table_name = $table_prefix . "CHARGEN_TEMPLATE_MAXIMUM";
		$sql = "CREATE TABLE " . $current_table_name . " (
					ID              MEDIUMINT(9)	NOT NULL   AUTO_INCREMENT,
					TEMPLATE_ID		MEDIUMINT(9)	NOT NULL,
					ITEMTABLE       TINYTEXT        NOT NULL,
					ITEMTABLE_ID    MEDIUMINT(9)    NOT NULL,
					LEVEL  	        MEDIUMINT(9)    NOT NULL,
					PRIMARY KEY  (ID),
					CONSTRAINT `" . $table_prefix . "template_max_constraint_1` FOREIGN KEY (TEMPLATE_ID)   REFERENCES " . $table_prefix . "CHARGEN_TEMPLATE(ID)
					) ENGINE=INNODB;";
		dbDelta($sql);

		$current_table_name = $table_prefix . "PLAYER";
		$sql = "CREATE TABLE " . $current_table_name . " (
					ID                 MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
					NAME               VARCHAR(60)  NOT NULL,
					PLAYER_TYPE_ID     MEDIUMINT(9) NOT NULL,
					PLAYER_STATUS_ID   MEDIUMINT(9) NOT NULL,
					PRIMARY KEY  (ID),
					CONSTRAINT `" . $table_prefix . "player_constraint_1` FOREIGN KEY (PLAYER_TYPE_ID)   REFERENCES " . $table_prefix . "PLAYER_TYPE(ID),
					CONSTRAINT `" . $table_prefix . "player_constraint_2` FOREIGN KEY (PLAYER_STATUS_ID) REFERENCES " . $table_prefix . "PLAYER_STATUS(ID)
					) ENGINE=INNODB;";
		dbDelta($sql);

		$current_table_name = $table_prefix . "COST_MODEL_STEP";
		$sql = "CREATE TABLE " . $current_table_name . " (
					ID              MEDIUMINT(9) NOT NULL  AUTO_INCREMENT,
					COST_MODEL_ID   MEDIUMINT(9) NOT NULL,
					SEQUENCE        SMALLINT(3)  NOT NULL,
					CURRENT_VALUE   SMALLINT(3)  NOT NULL,
					NEXT_VALUE      SMALLINT(3)  NOT NULL,
					FREEBIE_COST    SMALLINT(3)  NOT NULL,
					XP_COST         SMALLINT(3)  NOT NULL,
					PRIMARY KEY  (ID),
					CONSTRAINT `" . $table_prefix . "cost_model_step_constraint_1` FOREIGN KEY (COST_MODEL_ID) REFERENCES " . $table_prefix . "COST_MODEL(ID)
					) ENGINE=INNODB;";
		dbDelta($sql);

		$current_table_name = $table_prefix . "CLAN";
		$sql = "CREATE TABLE " . $current_table_name . " (
					ID           	MEDIUMINT(9)  NOT NULL AUTO_INCREMENT,
					NAME         	VARCHAR(30)   NOT NULL,
					DESCRIPTION  	TINYTEXT      NOT NULL,
					ICON_LINK    	TINYTEXT      NOT NULL,
					CLAN_PAGE_LINK	TINYTEXT      NOT NULL,
					CLAN_FLAW    	TINYTEXT      NOT NULL,
					CLAN_COST_MODEL_ID      MEDIUMINT(9) NOT NULL,
					NONCLAN_COST_MODEL_ID   MEDIUMINT(9) NOT NULL,
					WORDPRESS_ROLE  TINYTEXT      NOT NULL,
					VISIBLE      	VARCHAR(1)    NOT NULL,
					PRIMARY KEY  (ID),
					CONSTRAINT `" . $table_prefix . "clan_constraint_1` FOREIGN KEY (CLAN_COST_MODEL_ID)    REFERENCES " . $table_prefix . "COST_MODEL(ID),
					CONSTRAINT `" . $table_prefix . "clan_constraint_2` FOREIGN KEY (NONCLAN_COST_MODEL_ID) REFERENCES " . $table_prefix . "COST_MODEL(ID)
					) ENGINE=INNODB;";
		dbDelta($sql);

		$current_table_name = $table_prefix . "STAT";
		$sql = "CREATE TABLE " . $current_table_name . " (
					ID              	MEDIUMINT(9)  NOT NULL  AUTO_INCREMENT,
					NAME            	VARCHAR(16)   NOT NULL,
					DESCRIPTION     	TINYTEXT      NOT NULL,
					GROUPING        	VARCHAR(30)   NOT NULL,
					ORDERING        	SMALLINT(3)   NOT NULL,
					COST_MODEL_ID   	MEDIUMINT(9)  NOT NULL,
					SPECIALISATION_AT	SMALLINT(2)	  NOT NULL,
					PRIMARY KEY  (ID),
					CONSTRAINT `" . $table_prefix . "stat_constraint_1` FOREIGN KEY (COST_MODEL_ID) REFERENCES " . $table_prefix . "COST_MODEL(ID)
					) ENGINE=INNODB;";
		dbDelta($sql);

		$current_table_name = $table_prefix . "SKILL";
		$sql = "CREATE TABLE " . $current_table_name . " (
					ID              	MEDIUMINT(9)  NOT NULL  AUTO_INCREMENT,
					NAME            	VARCHAR(30)   NOT NULL,
					DESCRIPTION     	TINYTEXT      NOT NULL,
					COST_MODEL_ID   	MEDIUMINT(9)  NOT NULL,
					SKILL_TYPE_ID   	MEDIUMINT(9)  NOT NULL,
					MULTIPLE			VARCHAR(1)	  NOT NULL,
					SPECIALISATION_AT	SMALLINT(2)	  NOT NULL,
					VISIBLE         	VARCHAR(1)    NOT NULL,
					PRIMARY KEY  (ID),
					CONSTRAINT `" . $table_prefix . "skill_constraint_1` FOREIGN KEY (COST_MODEL_ID) REFERENCES " . $table_prefix . "COST_MODEL(ID),
					CONSTRAINT `" . $table_prefix . "skill_constraint_2` FOREIGN KEY (SKILL_TYPE_ID) REFERENCES " . $table_prefix . "SKILL_TYPE(ID)
					) ENGINE=INNODB;";

		
		dbDelta($sql);

		$current_table_name = $table_prefix . "BACKGROUND";
		$sql = "CREATE TABLE " . $current_table_name . " (
					ID             		MEDIUMINT(9)  NOT NULL  AUTO_INCREMENT,
					NAME            	VARCHAR(30)   NOT NULL,
					DESCRIPTION     	TINYTEXT      NOT NULL,
					GROUPING        	VARCHAR(30)   NOT NULL,
					COST_MODEL_ID   	MEDIUMINT(9)  NOT NULL,
					HAS_SECTOR      	VARCHAR(1)    NOT NULL,
					HAS_SPECIALISATION  VARCHAR(1)    NOT NULL,
					VISIBLE         	VARCHAR(1)    NOT NULL,
					BACKGROUND_QUESTION TEXT,
					PRIMARY KEY  (ID),
					CONSTRAINT `" . $table_prefix . "background_constraint_1` FOREIGN KEY (COST_MODEL_ID) REFERENCES " . $table_prefix . "COST_MODEL(ID)
					) ENGINE=INNODB;";
		
		dbDelta($sql);
			
		$current_table_name = $table_prefix . "MERIT";
			$sql = "CREATE TABLE " . $current_table_name . " (
						ID                  MEDIUMINT(9)  NOT NULL  AUTO_INCREMENT,
						NAME                VARCHAR(32)   NOT NULL,
						DESCRIPTION         TINYTEXT      NOT NULL,
						VALUE               SMALLINT(3)   NOT NULL,
						GROUPING            VARCHAR(30)   NOT NULL,
						COST                SMALLINT(3)   NOT NULL,
						XP_COST             SMALLINT(3)   NOT NULL,
						MULTIPLE            VARCHAR(1)    NOT NULL,
						HAS_SPECIALISATION  VARCHAR(1)    NOT NULL,
						SOURCE_BOOK_ID      MEDIUMINT(9)  NOT NULL,
						PAGE_NUMBER         SMALLINT(4)   NOT NULL,
						VISIBLE             VARCHAR(1)    NOT NULL,
						BACKGROUND_QUESTION VARCHAR(255),
						PROFILE_DISPLAY_ID	MEDIUMINT(9)  NOT NULL,
						PRIMARY KEY  (ID),
						CONSTRAINT `" . $table_prefix . "merit_constraint_1` FOREIGN KEY (SOURCE_BOOK_ID) REFERENCES " . $table_prefix . "SOURCE_BOOK(ID)
						) ENGINE=INNODB;";			
			dbDelta($sql);

		$current_table_name = $table_prefix . "DISCIPLINE";
		$sql = "CREATE TABLE " . $current_table_name . " (
					ID              MEDIUMINT(9)  NOT NULL   AUTO_INCREMENT,
					NAME            VARCHAR(32)   NOT NULL,
					DESCRIPTION     TINYTEXT      NOT NULL,
					SOURCE_BOOK_ID  MEDIUMINT(9)  NOT NULL,
					PAGE_NUMBER     SMALLINT(4)   NOT NULL,
					VISIBLE         VARCHAR(1)    NOT NULL,
					PRIMARY KEY  (ID),
					CONSTRAINT `" . $table_prefix . "discipline_constraint_1` FOREIGN KEY (SOURCE_BOOK_ID) REFERENCES " . $table_prefix . "SOURCE_BOOK(ID)
					) ENGINE=INNODB;";
		dbDelta($sql);
		
		$current_table_name = $table_prefix . "COMBO_DISCIPLINE";
			$sql = "CREATE TABLE " . $current_table_name . " (
						ID              MEDIUMINT(9)  NOT NULL   AUTO_INCREMENT,
						NAME            VARCHAR(60)   NOT NULL,
						DESCRIPTION     TINYTEXT      NOT NULL,
						COST            SMALLINT(3)   NOT NULL,
						SOURCE_BOOK_ID  MEDIUMINT(9)  NOT NULL,
						PAGE_NUMBER     SMALLINT(4)   NOT NULL,
						VISIBLE         VARCHAR(1)    NOT NULL,
						PRIMARY KEY  (ID),
						CONSTRAINT `" . $table_prefix . "combo_disc_constraint_1` FOREIGN KEY (SOURCE_BOOK_ID) REFERENCES " . $table_prefix . "SOURCE_BOOK(ID)
						) ENGINE=INNODB;";
			dbDelta($sql);

		$current_table_name = $table_prefix . "CONFIG";
		$sql = "CREATE TABLE " . $current_table_name . " (
					ID                         MEDIUMINT(9)   NOT NULL  AUTO_INCREMENT,
					PLACEHOLDER_IMAGE          TINYTEXT       NOT NULL,
					ANDROID_LINK               TINYTEXT       NOT NULL,
					HOME_DOMAIN_ID             MEDIUMINT(9)   NOT NULL,
					HOME_SECT_ID               MEDIUMINT(9)   NOT NULL,
					DEFAULT_GENERATION_ID      MEDIUMINT(9)   NOT NULL,
					ASSIGN_XP_BY_PLAYER	       VARCHAR(1)     NOT NULL,
					USE_NATURE_DEMEANOUR       VARCHAR(1)     NOT NULL,
					DISPLAY_BACKGROUND_IN_PROFILE  MEDIUMINT(9)     NOT NULL,
					PRIMARY KEY  (ID),
					CONSTRAINT `" . $table_prefix . "config_constraint_1` FOREIGN KEY (HOME_DOMAIN_ID)  REFERENCES " . $table_prefix . "DOMAIN(ID),
					CONSTRAINT `" . $table_prefix . "config_constraint_2` FOREIGN KEY (HOME_SECT_ID)    REFERENCES " . $table_prefix . "SECT(ID)
					) ENGINE=INNODB;";
		dbDelta($sql);
		
		$current_table_name = $table_prefix . "MAPDOMAIN";
		$sql = "CREATE TABLE " . $current_table_name . " (
					ID              MEDIUMINT(9)  NOT NULL   AUTO_INCREMENT,
					NAME            VARCHAR(60)   NOT NULL,
					OWNER_ID  		MEDIUMINT(9)  NOT NULL,
					DESCRIPTION     TINYTEXT      NOT NULL,
					COORDINATES     LONGTEXT      NOT NULL,
					VISIBLE         VARCHAR(1)    NOT NULL,
					PRIMARY KEY  (ID),
					CONSTRAINT `" . $table_prefix . "mapdomain_constraint_1` FOREIGN KEY (OWNER_ID)  REFERENCES " . $table_prefix . "MAPOWNER(ID)
					) ENGINE=INNODB;";
		dbDelta($sql);
		
		// LEVEL 3 TABLES - TABLES WITH A FOREIGN KEY CONSTRAINT TO A LEVEL 2 TABLE
	
		$current_table_name = $table_prefix . "ROAD_OR_PATH";
		$sql = "CREATE TABLE " . $current_table_name . " (
					ID              MEDIUMINT(9)  NOT NULL  AUTO_INCREMENT,
					NAME            VARCHAR(32)   NOT NULL,
					DESCRIPTION     TINYTEXT      NOT NULL,
					STAT1_ID        MEDIUMINT(9)  NOT NULL,
					STAT2_ID        MEDIUMINT(9)  NOT NULL,
					SOURCE_BOOK_ID  MEDIUMINT(9)  NOT NULL,
					PAGE_NUMBER     SMALLINT(4)   NOT NULL,
					VISIBLE         VARCHAR(1)    NOT NULL,
					COST_MODEL_ID   MEDIUMINT(9)  NOT NULL,
					PRIMARY KEY  (ID),
					CONSTRAINT `" . $table_prefix . "road_constraint_1` FOREIGN KEY (STAT1_ID) REFERENCES " . $table_prefix . "STAT(ID),
					CONSTRAINT `" . $table_prefix . "road_constraint_2` FOREIGN KEY (STAT2_ID) REFERENCES " . $table_prefix . "STAT(ID),
					CONSTRAINT `" . $table_prefix . "road_constraint_3` FOREIGN KEY (SOURCE_BOOK_ID) REFERENCES " . $table_prefix . "SOURCE_BOOK(ID),
					CONSTRAINT `" . $table_prefix . "road_constraint_4` FOREIGN KEY (COST_MODEL_ID)  REFERENCES " . $table_prefix . "COST_MODEL(ID)
					) ENGINE=INNODB;";
		dbDelta($sql);
		
		$current_table_name = $table_prefix . "PATH";
			$sql = "CREATE TABLE " . $current_table_name . " (
						ID              MEDIUMINT(9)  NOT NULL  AUTO_INCREMENT,
						NAME            VARCHAR(63)   NOT NULL,
						DESCRIPTION     TINYTEXT      NOT NULL,
						DISCIPLINE_ID   MEDIUMINT(9)  NOT NULL,
						COST_MODEL_ID   MEDIUMINT(9)  NOT NULL,
						SOURCE_BOOK_ID  MEDIUMINT(9)   NOT NULL,
						PAGE_NUMBER     SMALLINT(4)   NOT NULL,
						VISIBLE         VARCHAR(1)    NOT NULL,
						PRIMARY KEY  (ID),
						CONSTRAINT `" . $table_prefix . "path_constraint_1` FOREIGN KEY (DISCIPLINE_ID)  REFERENCES " . $table_prefix . "DISCIPLINE(ID),
						CONSTRAINT `" . $table_prefix . "path_constraint_2` FOREIGN KEY (SOURCE_BOOK_ID) REFERENCES " . $table_prefix . "SOURCE_BOOK(ID),
						CONSTRAINT `" . $table_prefix . "path_constraint_3` FOREIGN KEY (COST_MODEL_ID)  REFERENCES " . $table_prefix . "COST_MODEL(ID)
						) ENGINE=INNODB;";

			
			dbDelta($sql);

		$current_table_name = $table_prefix . "DISCIPLINE_POWER";
			$sql = "CREATE TABLE " . $current_table_name . " (
						ID mediumint(9) NOT NULL AUTO_INCREMENT,
						NAME varchar(32) NOT NULL,
						DESCRIPTION TINYTEXT NOT NULL,
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

			
			dbDelta($sql);

		$current_table_name = $table_prefix . "RITUAL";
			$sql = "CREATE TABLE " . $current_table_name . " (
						ID              MEDIUMINT(9)  NOT NULL  AUTO_INCREMENT,
						NAME            VARCHAR(60)   NOT NULL,
						DESCRIPTION     TINYTEXT      NOT NULL,
						LEVEL           SMALLINT(2)   NOT NULL,
						DISCIPLINE_ID   MEDIUMINT(9)  NOT NULL,
						DICE_POOL       VARCHAR(60)   NOT NULL,
						DIFFICULTY      VARCHAR(60)   NOT NULL,
						COST            SMALLINT(3)   NOT NULL,
						SOURCE_BOOK_ID  MEDIUMINT(9)   NOT NULL,
						PAGE_NUMBER     SMALLINT(4)   NOT NULL,
						VISIBLE         VARCHAR(1)    NOT NULL,
						PRIMARY KEY  (ID),
						CONSTRAINT `" . $table_prefix . "ritual_constraint_1` FOREIGN KEY (DISCIPLINE_ID) REFERENCES " . $table_prefix . "DISCIPLINE(ID),
						CONSTRAINT `" . $table_prefix . "ritual_constraint_2` FOREIGN KEY (SOURCE_BOOK_ID) REFERENCES " . $table_prefix . "SOURCE_BOOK(ID)
						) ENGINE=INNODB;";
			dbDelta($sql);

		$current_table_name = $table_prefix . "CLAN_DISCIPLINE";
			$sql = "CREATE TABLE " . $current_table_name . " (
						ID             MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
						CLAN_ID        MEDIUMINT(9) NOT NULL,
						DISCIPLINE_ID  MEDIUMINT(9) NOT NULL,
						PRIMARY KEY  (ID),
						CONSTRAINT `" . $table_prefix . "clan_disc_constraint_1` FOREIGN KEY (CLAN_ID)       REFERENCES " . $table_prefix . "CLAN(ID),
						CONSTRAINT `" . $table_prefix . "clan_disc_constraint_2` FOREIGN KEY (DISCIPLINE_ID) REFERENCES " . $table_prefix . "DISCIPLINE(ID)
						) ENGINE=INNODB;";
			dbDelta($sql);
		//echo "<p>Clan Disc SQL: $sql</p>";

		$current_table_name = $table_prefix . "COMBO_DISCIPLINE_PREREQUISITE";
			$sql = "CREATE TABLE " . $current_table_name . " (
						ID                   MEDIUMINT(9)  NOT NULL AUTO_INCREMENT,
						COMBO_DISCIPLINE_ID  MEDIUMINT(9)  NOT NULL,
						DISCIPLINE_ID        MEDIUMINT(9)  NOT NULL,
						DISCIPLINE_LEVEL     SMALLINT(3)   NOT NULL,
						PRIMARY KEY  (ID),
						CONSTRAINT `" . $table_prefix . "char_combo_pre_constraint_1` FOREIGN KEY (COMBO_DISCIPLINE_ID) REFERENCES " . $table_prefix . "COMBO_DISCIPLINE(ID),
						CONSTRAINT `" . $table_prefix . "char_combo_pre_constraint_2` FOREIGN KEY (DISCIPLINE_ID)       REFERENCES " . $table_prefix . "DISCIPLINE(ID)
						) ENGINE=INNODB;";
			dbDelta($sql);

		// LEVEL 4 TABLES - TABLES WITH A FOREIGN KEY CONSTRAINT TO A LEVEL 3 TABLE

		$current_table_name = $table_prefix . "CHARACTER";
		//echo "<p>Setting up $current_table_name</p>";
		$sql = "CREATE TABLE " . $current_table_name . " (
					ID                        MEDIUMINT(9)  NOT NULL  AUTO_INCREMENT,
					NAME                      VARCHAR(60)   NOT NULL,
					PUBLIC_CLAN_ID            MEDIUMINT(9)  NOT NULL,
					PRIVATE_CLAN_ID           MEDIUMINT(9)  NOT NULL,
					GENERATION_ID             MEDIUMINT(9)  NOT NULL,
					DATE_OF_BIRTH             DATE          NOT NULL,
					DATE_OF_EMBRACE           DATE          NOT NULL,
					SIRE                      VARCHAR(60)   NOT NULL,
					PLAYER_ID                 MEDIUMINT(9)  NOT NULL,
					CHARACTER_TYPE_ID         MEDIUMINT(9)  NOT NULL,
					CHARACTER_STATUS_ID       MEDIUMINT(9)  NOT NULL,
					CHARACTER_STATUS_COMMENT  VARCHAR(120),
					ROAD_OR_PATH_ID           MEDIUMINT(9)  NOT NULL,
					ROAD_OR_PATH_RATING       SMALLINT(3)   NOT NULL,
					DOMAIN_ID                 MEDIUMINT(9)  NOT NULL,
					WORDPRESS_ID              VARCHAR(32)   NOT NULL,
					SECT_ID                   MEDIUMINT(9)  NOT NULL,
					NATURE_ID                 MEDIUMINT(9)  NOT NULL,
					DEMEANOUR_ID              MEDIUMINT(9)  NOT NULL,
					CHARGEN_STATUS_ID		  MEDIUMINT(9)  NOT NULL,
					CONCEPT					  TINYTEXT		NOT NULL,
					EMAIL					  VARCHAR(60)	NOT NULL,
					LAST_UPDATED              DATE          NOT NULL,
					GET_NEWSLETTER            VARCHAR(1)    NOT NULL,
					VISIBLE                   VARCHAR(1)    NOT NULL,
					DELETED                   VARCHAR(1)    NOT NULL,
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
		dbDelta($sql);

		$current_table_name = $table_prefix . "PATH_POWER";
			$sql = "CREATE TABLE " . $current_table_name . " (
						ID              MEDIUMINT(9)  NOT NULL  AUTO_INCREMENT,
						NAME            VARCHAR(32)   NOT NULL,
						DESCRIPTION     TINYTEXT      NOT NULL,
						LEVEL           SMALLINT(2)   NOT NULL,
						PATH_ID         MEDIUMINT(9)  NOT NULL,
						DICE_POOL       VARCHAR(60)   NOT NULL,
						DIFFICULTY      VARCHAR(60)   NOT NULL,
						COST            SMALLINT(3)   NOT NULL,
						SOURCE_BOOK_ID  MEDIUMINT(9)   NOT NULL,
						PAGE_NUMBER     SMALLINT(4)   NOT NULL,
						VISIBLE         VARCHAR(1)    NOT NULL,
						PRIMARY KEY  (ID),
						CONSTRAINT `" . $table_prefix . "path_power_constraint_1` FOREIGN KEY (PATH_ID) REFERENCES " . $table_prefix . "PATH(ID),
						CONSTRAINT `" . $table_prefix . "path_power_constraint_2` FOREIGN KEY (SOURCE_BOOK_ID) REFERENCES " . $table_prefix . "SOURCE_BOOK(ID)
						) ENGINE=INNODB;";

			
			dbDelta($sql);

		// LEVEL 5 TABLES - TABLES WITH A FOREIGN KEY CONSTRAINT TO A LEVEL 4 TABLE

		$current_table_name = $table_prefix . "CHARACTER_OFFICE";
		$sql = "CREATE TABLE " . $current_table_name . " (
					ID            MEDIUMINT(9) NOT NULL  AUTO_INCREMENT,
					OFFICE_ID     MEDIUMINT(9) NOT NULL,
					DOMAIN_ID     MEDIUMINT(9) NOT NULL,
					CHARACTER_ID  MEDIUMINT(9) NOT NULL,
					COMMENT       VARCHAR(60),
					PRIMARY KEY  (ID),
					CONSTRAINT `" . $table_prefix . "office_constraint_1` FOREIGN KEY (OFFICE_ID)    REFERENCES " . $table_prefix . "OFFICE(ID),
					CONSTRAINT `" . $table_prefix . "office_constraint_2` FOREIGN KEY (DOMAIN_ID)    REFERENCES " . $table_prefix . "DOMAIN(ID),
					CONSTRAINT `" . $table_prefix . "office_constraint_3` FOREIGN KEY (CHARACTER_ID) REFERENCES " . $table_prefix . "CHARACTER(ID)
					) ENGINE=INNODB;";
		dbDelta($sql);

		$current_table_name = $table_prefix . "PLAYER_XP";
		$sql = "CREATE TABLE " . $current_table_name . " (
					ID             MEDIUMINT(9)  NOT NULL  AUTO_INCREMENT,
					PLAYER_ID      MEDIUMINT(9)  NOT NULL,
					CHARACTER_ID   MEDIUMINT(9)  NOT NULL,
					XP_REASON_ID   MEDIUMINT(9)  NOT NULL,
					AWARDED        DATE          NOT NULL,
					AMOUNT         SMALLINT(3)   NOT NULL,
					COMMENT        VARCHAR(120)  NOT NULL,
					PRIMARY KEY  (ID),
					CONSTRAINT `" . $table_prefix . "player_xp_constraint_1` FOREIGN KEY (PLAYER_ID)    REFERENCES " . $table_prefix . "PLAYER(ID),
					CONSTRAINT `" . $table_prefix . "player_xp_constraint_2` FOREIGN KEY (CHARACTER_ID) REFERENCES " . $table_prefix . "CHARACTER(ID),
					CONSTRAINT `" . $table_prefix . "player_xp_constraint_3` FOREIGN KEY (XP_REASON_ID) REFERENCES " . $table_prefix . "XP_REASON(ID)
					) ENGINE=INNODB;";
		dbDelta($sql);

		/* 	CHARTABLE 		= Character table to update or add new row to
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
					ID             MEDIUMINT(9)  NOT NULL  AUTO_INCREMENT,
					PLAYER_ID      MEDIUMINT(9)  NOT NULL,
					CHARACTER_ID   MEDIUMINT(9)  NOT NULL,
					CHARTABLE      TINYTEXT      NOT NULL,
					CHARTABLE_ID   MEDIUMINT(9)  NOT NULL,
					CHARTABLE_LEVEL  TINYTEXT    NOT NULL,
					AWARDED        DATE          NOT NULL,
					AMOUNT         SMALLINT(3)   NOT NULL,
					COMMENT        VARCHAR(120)  NOT NULL,
					SPECIALISATION VARCHAR(64)	 NOT NULL,
					TRAINING_NOTE  VARCHAR(164)  NOT NULL,
					ITEMTABLE      TINYTEXT      NOT NULL,
					ITEMNAME       TINYTEXT      NOT NULL,
					ITEMTABLE_ID   MEDIUMINT(9)  NOT NULL,
					PRIMARY KEY  (ID),
					CONSTRAINT `" . $table_prefix . "pending_xp_constraint_1` FOREIGN KEY (PLAYER_ID)    REFERENCES " . $table_prefix . "PLAYER(ID),
					CONSTRAINT `" . $table_prefix . "pending_xp_constraint_2` FOREIGN KEY (CHARACTER_ID) REFERENCES " . $table_prefix . "CHARACTER(ID)
					) ENGINE=INNODB;";
		dbDelta($sql);

		$current_table_name = $table_prefix . "PENDING_FREEBIE_SPEND";
		$sql = "CREATE TABLE " . $current_table_name . " (
					ID             MEDIUMINT(9)  NOT NULL  AUTO_INCREMENT,
					CHARACTER_ID   MEDIUMINT(9)  NOT NULL,
					CHARTABLE      TINYTEXT      NOT NULL,
					CHARTABLE_ID   MEDIUMINT(9)  NOT NULL,
					LEVEL_FROM     MEDIUMINT(9)  NOT NULL,
					LEVEL_TO  	   MEDIUMINT(9)  NOT NULL,
					AMOUNT         SMALLINT(3)   NOT NULL,
					ITEMTABLE      TINYTEXT      NOT NULL,
					ITEMNAME       TINYTEXT      NOT NULL,
					ITEMTABLE_ID   MEDIUMINT(9)  NOT NULL,
					SPECIALISATION VARCHAR(64)	 NOT NULL,
					PENDING_DETAIL TEXT          NOT NULL,
					PRIMARY KEY  (ID),
					CONSTRAINT `" . $table_prefix . "pending_freebie_constraint_1` FOREIGN KEY (CHARACTER_ID) REFERENCES " . $table_prefix . "CHARACTER(ID)
					) ENGINE=INNODB;";
		dbDelta($sql);

		$current_table_name = $table_prefix . "CHARACTER_ROAD_OR_PATH";
		$sql = "CREATE TABLE " . $current_table_name . " (
					ID               MEDIUMINT(9)  NOT NULL  AUTO_INCREMENT,
					CHARACTER_ID     MEDIUMINT(9)  NOT NULL,
					PATH_REASON_ID   MEDIUMINT(9)  NOT NULL,
					AWARDED          DATE          NOT NULL,
					AMOUNT           SMALLINT(3)   NOT NULL,
					COMMENT          VARCHAR(120)  NOT NULL,
					PRIMARY KEY  (ID),
					CONSTRAINT `" . $table_prefix . "char_road_constraint_1` FOREIGN KEY (CHARACTER_ID) REFERENCES " . $table_prefix . "CHARACTER(ID),
					CONSTRAINT `" . $table_prefix . "char_road_constraint_2` FOREIGN KEY (PATH_REASON_ID) REFERENCES " . $table_prefix . "PATH_REASON(ID)
					) ENGINE=INNODB;";
		dbDelta($sql);

		$current_table_name = $table_prefix . "CHARACTER_TEMPORARY_STAT";
			$sql = "CREATE TABLE " . $current_table_name . " (
						ID                        MEDIUMINT(9)  NOT NULL  AUTO_INCREMENT,
						CHARACTER_ID              MEDIUMINT(9)  NOT NULL,
						TEMPORARY_STAT_ID         MEDIUMINT(9)  NOT NULL,
						TEMPORARY_STAT_REASON_ID  MEDIUMINT(9)  NOT NULL,
						AWARDED                   DATE          NOT NULL,
						AMOUNT                    SMALLINT(3)   NOT NULL,
						COMMENT                   VARCHAR(120)  NOT NULL,
						PRIMARY KEY  (ID),
						CONSTRAINT `" . $table_prefix . "char_temp_constraint_1` FOREIGN KEY (CHARACTER_ID) REFERENCES " . $table_prefix . "CHARACTER(ID),
						CONSTRAINT `" . $table_prefix . "char_temp_constraint_2` FOREIGN KEY (TEMPORARY_STAT_ID) REFERENCES " . $table_prefix . "TEMPORARY_STAT(ID),
						CONSTRAINT `" . $table_prefix . "char_temp_constraint_3` FOREIGN KEY (TEMPORARY_STAT_REASON_ID) REFERENCES " . $table_prefix . "TEMPORARY_STAT_REASON(ID)
						) ENGINE=INNODB;";

			
			dbDelta($sql);

		$current_table_name = $table_prefix . "CHARACTER_STAT";
			$sql = "CREATE TABLE " . $current_table_name . " (
						ID            MEDIUMINT(9)  NOT NULL  AUTO_INCREMENT,
						CHARACTER_ID  MEDIUMINT(9)  NOT NULL,
						STAT_ID       MEDIUMINT(9)  NOT NULL,
						LEVEL         SMALLINT(3)   NOT NULL,
						COMMENT       VARCHAR(60),
						PRIMARY KEY  (ID),
						CONSTRAINT `" . $table_prefix . "char_stat_constraint_1` FOREIGN KEY (CHARACTER_ID) REFERENCES " . $table_prefix . "CHARACTER(ID),
						CONSTRAINT `" . $table_prefix . "char_stat_constraint_2` FOREIGN KEY (STAT_ID)      REFERENCES " . $table_prefix . "STAT(ID)
						) ENGINE=INNODB;";
			dbDelta($sql);

		$current_table_name = $table_prefix . "CHARACTER_RITUAL";
			$sql = "CREATE TABLE " . $current_table_name . " (
						ID            MEDIUMINT(9)  NOT NULL  AUTO_INCREMENT,
						CHARACTER_ID  MEDIUMINT(9)  NOT NULL,
						RITUAL_ID     MEDIUMINT(9)  NOT NULL,
						LEVEL         SMALLINT(3)   NOT NULL,
						COMMENT       VARCHAR(60),
						PRIMARY KEY  (ID),
						CONSTRAINT `" . $table_prefix . "char_ritual_constraint_1` FOREIGN KEY (CHARACTER_ID) REFERENCES " . $table_prefix . "CHARACTER(ID),
						CONSTRAINT `" . $table_prefix . "char_ritual_constraint_2` FOREIGN KEY (RITUAL_ID)    REFERENCES " . $table_prefix . "RITUAL(ID)
						) ENGINE=INNODB;";
			dbDelta($sql);

		$current_table_name = $table_prefix . "CHARACTER_DISCIPLINE";
			$sql = "CREATE TABLE " . $current_table_name . " (
						ID             MEDIUMINT(9)  NOT NULL  AUTO_INCREMENT,
						CHARACTER_ID   MEDIUMINT(9)  NOT NULL,
						DISCIPLINE_ID  MEDIUMINT(9)  NOT NULL,
						LEVEL          SMALLINT(3)   NOT NULL,
						COMMENT        VARCHAR(60),
						PRIMARY KEY  (ID),
						CONSTRAINT `" . $table_prefix . "char_disc_constraint_1` FOREIGN KEY (CHARACTER_ID)  REFERENCES " . $table_prefix . "CHARACTER(ID),
						CONSTRAINT `" . $table_prefix . "char_disc_constraint_2` FOREIGN KEY (DISCIPLINE_ID) REFERENCES " . $table_prefix . "DISCIPLINE(ID)
						) ENGINE=INNODB;";
			dbDelta($sql);

		$current_table_name = $table_prefix . "CHARACTER_PATH";
			$sql = "CREATE TABLE " . $current_table_name . " (
						ID             MEDIUMINT(9)  NOT NULL  AUTO_INCREMENT,
						CHARACTER_ID   MEDIUMINT(9)  NOT NULL,
						PATH_ID        MEDIUMINT(9)  NOT NULL,
						LEVEL          SMALLINT(3)   NOT NULL,
						COMMENT        VARCHAR(60),
						PRIMARY KEY  (ID),
						CONSTRAINT `" . $table_prefix . "char_path_constraint_1` FOREIGN KEY (CHARACTER_ID)  REFERENCES " . $table_prefix . "CHARACTER(ID),
						CONSTRAINT `" . $table_prefix . "char_path_constraint_2` FOREIGN KEY (PATH_ID) REFERENCES " . $table_prefix . "PATH(ID)
						) ENGINE=INNODB;";
			dbDelta($sql);

		$current_table_name = $table_prefix . "CHARACTER_PATH_POWER";
			$sql = "CREATE TABLE " . $current_table_name . " (
						ID                      MEDIUMINT(9)  NOT NULL  AUTO_INCREMENT,
						CHARACTER_ID            MEDIUMINT(9)  NOT NULL,
						PATH_POWER_ID           MEDIUMINT(9)  NOT NULL,
						LEVEL                   SMALLINT(3)   NOT NULL,
						COMMENT                 VARCHAR(60),
						PRIMARY KEY  (ID),
						CONSTRAINT `" . $table_prefix . "char_path_power_constraint_1` FOREIGN KEY (CHARACTER_ID)    REFERENCES " . $table_prefix . "CHARACTER(ID),
						CONSTRAINT `" . $table_prefix . "char_path_power_constraint_2` FOREIGN KEY (PATH_POWER_ID)   REFERENCES " . $table_prefix . "PATH_POWER(ID)
						) ENGINE=INNODB;";
			dbDelta($sql);

		$current_table_name = $table_prefix . "CHARACTER_DISCIPLINE_POWER";
			$sql = "CREATE TABLE " . $current_table_name . " (
						ID                      MEDIUMINT(9)  NOT NULL  AUTO_INCREMENT,
						CHARACTER_ID            MEDIUMINT(9)  NOT NULL,
						DISCIPLINE_POWER_ID     MEDIUMINT(9)  NOT NULL,
						LEVEL                   SMALLINT(3)   NOT NULL,
						COMMENT                 VARCHAR(60),
						PRIMARY KEY  (ID),
						CONSTRAINT `" . $table_prefix . "char_disc_power_constraint_1` FOREIGN KEY (CHARACTER_ID)        REFERENCES " . $table_prefix . "CHARACTER(ID),
						CONSTRAINT `" . $table_prefix . "char_disc_power_constraint_2` FOREIGN KEY (DISCIPLINE_POWER_ID) REFERENCES " . $table_prefix . "DISCIPLINE_POWER(ID)
						) ENGINE=INNODB;";
			dbDelta($sql);

		$current_table_name = $table_prefix . "CHARACTER_MERIT";
			$sql = "CREATE TABLE " . $current_table_name . " (
						ID            MEDIUMINT(9)  NOT NULL  AUTO_INCREMENT,
						CHARACTER_ID  MEDIUMINT(9)  NOT NULL,
						MERIT_ID      MEDIUMINT(9)  NOT NULL,
						LEVEL         SMALLINT(3)   NOT NULL,
						COMMENT       VARCHAR(60),
						APPROVED_DETAIL TEXT,
						PENDING_DETAIL  TEXT,
						DENIED_DETAIL   TEXT,
						PRIMARY KEY  (ID),
						CONSTRAINT `" . $table_prefix . "char_merit_constraint_1` FOREIGN KEY (CHARACTER_ID) REFERENCES " . $table_prefix . "CHARACTER(ID),
						CONSTRAINT `" . $table_prefix . "char_merit_constraint_2` FOREIGN KEY (MERIT_ID)     REFERENCES " . $table_prefix . "MERIT(ID)
						) ENGINE=INNODB;";
			dbDelta($sql);

		$current_table_name = $table_prefix . "CHARACTER_SKILL";
			$sql = "CREATE TABLE " . $current_table_name . " (
						ID            MEDIUMINT(9)  NOT NULL  AUTO_INCREMENT,
						CHARACTER_ID  MEDIUMINT(9)  NOT NULL,
						SKILL_ID      MEDIUMINT(9)  NOT NULL,
						LEVEL         SMALLINT(3)   NOT NULL,
						COMMENT       VARCHAR(60),
						PRIMARY KEY  (ID),
						CONSTRAINT `" . $table_prefix . "char_skill_constraint_1` FOREIGN KEY (CHARACTER_ID) REFERENCES " . $table_prefix . "CHARACTER(ID),
						CONSTRAINT `" . $table_prefix . "char_skill_constraint_2` FOREIGN KEY (SKILL_ID)     REFERENCES " . $table_prefix . "SKILL(ID)
						) ENGINE=INNODB;";
			dbDelta($sql);

		$current_table_name = $table_prefix . "CHARACTER_BACKGROUND";
		$sql = "CREATE TABLE " . $current_table_name . " (
					ID                MEDIUMINT(9)  NOT NULL  AUTO_INCREMENT,
					CHARACTER_ID      MEDIUMINT(9)  NOT NULL,
					BACKGROUND_ID     MEDIUMINT(9)  NOT NULL,
					LEVEL             SMALLINT(3)   NOT NULL,
					SECTOR_ID		  MEDIUMINT(9)  NOT NULL,
					COMMENT           VARCHAR(60),
					APPROVED_DETAIL   TEXT,
					PENDING_DETAIL    TEXT,
					DENIED_DETAIL     TEXT,
					PRIMARY KEY  (ID),
					CONSTRAINT `" . $table_prefix . "char_bg_constraint_1` FOREIGN KEY (CHARACTER_ID)  REFERENCES " . $table_prefix . "CHARACTER(ID),
					CONSTRAINT `" . $table_prefix . "char_bg_constraint_2` FOREIGN KEY (BACKGROUND_ID) REFERENCES " . $table_prefix . "BACKGROUND(ID)
					) ENGINE=INNODB;";
		dbDelta($sql);
		
		$current_table_name = $table_prefix . "CHARACTER_COMBO_DISCIPLINE";
		
			$sql = "CREATE TABLE " . $current_table_name . " (
						ID                   MEDIUMINT(9)  NOT NULL AUTO_INCREMENT,
						CHARACTER_ID         MEDIUMINT(9)  NOT NULL,
						COMBO_DISCIPLINE_ID  MEDIUMINT(9)  NOT NULL,
						COMMENT              VARCHAR(60),
						PRIMARY KEY  (ID),
						CONSTRAINT `" . $table_prefix . "char_combo_constraint_1` FOREIGN KEY (CHARACTER_ID)        REFERENCES " . $table_prefix . "CHARACTER(ID),
						CONSTRAINT `" . $table_prefix . "char_combo_constraint_2` FOREIGN KEY (COMBO_DISCIPLINE_ID) REFERENCES " . $table_prefix . "COMBO_DISCIPLINE(ID)
						) ENGINE=INNODB;";
			dbDelta($sql);

		$current_table_name = $table_prefix . "CHARACTER_PROFILE";
			$sql = "CREATE TABLE " . $current_table_name . " (
						ID                MEDIUMINT(9)   NOT NULL  AUTO_INCREMENT,
						CHARACTER_ID      MEDIUMINT(9)   NOT NULL,
						QUOTE             TEXT			 NOT NULL,
						PORTRAIT          TINYTEXT       NOT NULL,
						PRIMARY KEY  (ID),
						CONSTRAINT `" . $table_prefix . "char_profile_constraint_1` FOREIGN KEY (CHARACTER_ID)  REFERENCES " . $table_prefix . "CHARACTER(ID)
						) ENGINE=INNODB;";
			dbDelta($sql);

		$current_table_name = $table_prefix . "CHARACTER_EXTENDED_BACKGROUND";
		$sql = "CREATE TABLE " . $current_table_name . " (
					ID                    MEDIUMINT(9)  NOT NULL  AUTO_INCREMENT,
					CHARACTER_ID          MEDIUMINT(9)  NOT NULL,
					QUESTION_ID			  MEDIUMINT(9)  NOT NULL,
					APPROVED_DETAIL       TEXT   		NOT NULL,
					PENDING_DETAIL        TEXT   		NOT NULL,
					DENIED_DETAIL         TEXT   		NOT NULL,
					PRIMARY KEY  (ID),
					CONSTRAINT `" . $table_prefix . "char_ext_bg_constraint_1` FOREIGN KEY (CHARACTER_ID)  REFERENCES " . $table_prefix . "CHARACTER(ID),
					CONSTRAINT `" . $table_prefix . "char_ext_bg_constraint_2` FOREIGN KEY (QUESTION_ID)  REFERENCES " . $table_prefix . "EXTENDED_BACKGROUND(ID)
					) ENGINE=INNODB;";
		dbDelta($sql);
		
		$current_table_name = $table_prefix . "CHARACTER_GENERATION";
		$sql = "CREATE TABLE " . $current_table_name . " (
					ID                    MEDIUMINT(9)  NOT NULL  AUTO_INCREMENT,
					CHARACTER_ID          MEDIUMINT(9)  NOT NULL,
					TEMPLATE_ID			  MEDIUMINT(9)  NOT NULL,
					NOTE_TO_ST       	  TEXT   		NOT NULL,
					NOTE_FROM_ST          TEXT   		NOT NULL,
					WORDPRESS_ID          VARCHAR(32)	NOT NULL,
					DATE_OF_APPROVAL	  DATE			NOT NULL,
					EMAIL_CONFIRMED	  	  VARCHAR(1)	NOT NULL,
					PRIMARY KEY  (ID),
					CONSTRAINT `" . $table_prefix . "char_gen_constraint_1` FOREIGN KEY (CHARACTER_ID)  REFERENCES " . $table_prefix . "CHARACTER(ID)
					) ENGINE=INNODB;";
		dbDelta($sql);

		$current_table_name = $table_prefix . "MAIL_QUEUE";
		$sql = "CREATE TABLE " . $current_table_name . " (
					ID                    MEDIUMINT(9)  NOT NULL  AUTO_INCREMENT,
					CHARACTER_ID          MEDIUMINT(9)  NOT NULL,
					MAIL_STATUS_ID        MEDIUMINT(9)  NOT NULL,
					WP_POST_ID        	  MEDIUMINT(9)  NOT NULL,
					PRIMARY KEY  (ID),
					CONSTRAINT `" . $table_prefix . "mail_status_constraint_1` FOREIGN KEY (CHARACTER_ID)  REFERENCES " . $table_prefix . "CHARACTER(ID),
					CONSTRAINT `" . $table_prefix . "mail_status_constraint_2` FOREIGN KEY (MAIL_STATUS_ID)  REFERENCES " . $table_prefix . "MAIL_STATUS(ID)
					) ENGINE=INNODB;";
		dbDelta($sql);
	
	//}
	
}

function vtm_character_install_data() {
	global $wpdb;
	
	$wpdb->show_errors();
	
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
	
	// LOAD UP THE INITIAL TABLE DATA
	$datalist = glob(VTM_CHARACTER_URL . "init/*.csv");
	foreach ($datalist as $datafile) {
		$temp = explode(".", basename($datafile));
		$tablename = $temp[1];
		
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
		}
	}
	
}

function vtm_character_update($beforeafter) {
	global $vtm_character_version;
	global $vtm_character_db_version;
	
	$errors = 0;

	$installed_version = get_site_option( "vtm_character_version", "1.9" );
	
	switch ($installed_version) {
		//--- FROM VERSION 1.9 -------------------------------------------------
		case "1.9":  $errors += vtm_character_update_1_9($beforeafter);
		case "1.10": $errors += vtm_character_update_1_10($beforeafter);
		case "1.11": $errors += vtm_character_update_1_11($beforeafter);
		case "1.12": $errors += vtm_character_update_1_11($beforeafter);
	}
	
	// Incremental database updates, during development
	$db_version = get_site_option( "vtm_character_db_version", "1" );
	if ($installed_version == $vtm_character_version && $db_version != $vtm_character_db_version) {
		switch ($installed_version) {
			case "1.10": $errors += vtm_character_update_1_9($beforeafter);
			case "1.11": $errors += vtm_character_update_1_10($beforeafter);
			case "1.12": $errors += vtm_character_update_1_11($beforeafter);
			case "2.0" : $errors += vtm_character_update_1_11($beforeafter);
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
			//GROUPING        VARCHAR(30)   NOT NULL,
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
add_action('activated_plugin','save_error');
function save_error(){
    update_option('vtm_plugin_error',  ob_get_contents());
}


?>