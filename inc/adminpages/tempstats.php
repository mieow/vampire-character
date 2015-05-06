<?php

function vtm_character_temp_stats() {
	global $wpdb;
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	
	$sql = "SELECT NAME FROM " . VTM_TABLE_PREFIX . "TEMPORARY_STAT ORDER BY ID";
	$tempstats = $wpdb->get_col($sql);
	
	?>
	<div class="wrap">
		<h2>Temporary Stat Changes</h2>
		<div class="gvadmin_nav">
			<ul>
			<?php
				foreach ($tempstats as $stat) {
					print "<li>" . vtm_get_tablink(esc_attr($stat), $stat) . "</li>";
				}
			?>
			</ul>
		</div>
		<div class="gvadmin_content">
		<?php
		
		$stat = isset($_REQUEST['tab']) ? $_REQUEST['tab'] : "Willpower";
		
		vtm_render_temp_stat_page($stat);
				
		?>
		</div>
	</div>
	
	<?php	
}

function vtm_render_temp_stat_page($stat) {
	global $wpdb;

	$current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );

	$sql = "SELECT ID FROM " . VTM_TABLE_PREFIX . "TEMPORARY_STAT WHERE NAME = %s";
	$statID = $wpdb->get_var($wpdb->prepare($sql, $stat));
	
	$sql = "SELECT ID FROM " . VTM_TABLE_PREFIX . "TEMPORARY_STAT_REASON WHERE NAME = %s";
	$default_bulk_reason = $wpdb->get_var($wpdb->prepare($sql, 'Game spend'));
 
 // List of stats
	$sql = "SELECT ID FROM " . VTM_TABLE_PREFIX . "STAT WHERE NAME = %s";
	$sql = $wpdb->prepare($sql, $stat);
	$result = $wpdb->get_results($sql);
	if (count($result) > 0) {
		$filterstat = ucfirst($stat);
		$maxcol = "MAXSTAT";
	} else {
		$filterstat = "Willpower";
		$maxcol = "MAX" . strtoupper($stat);
	}
	
	
	$reasons = vtm_listTemporaryStatReasons();

	// Setup extra tablenav default options
	if ( isset( $_REQUEST[$stat . '_reason_bulk'] ) && array_key_exists( $_REQUEST[$stat . '_reason_bulk'], $reasons ) ) {
		$reasonID = sanitize_key( $_REQUEST[$stat . '_reason_bulk'] );
	} else {
		$reasonID = $default_bulk_reason;
	}
	if ( isset( $_REQUEST[$stat . '_amount_bulk'] ) ) {
		$amount = (int) sanitize_key( $_REQUEST[$stat . '_amount_bulk'] );
	} else {
		$amount = 1;
	}
	
	// DO UPDATES
	$maximums   = isset($_REQUEST['max'])         ? $_REQUEST['max']         : array();
	$currents   = isset($_REQUEST['current'])     ? $_REQUEST['current']     : array();
	$names      = isset($_REQUEST['charname'])    ? $_REQUEST['charname']    : array();
	$amounts    = isset($_REQUEST['amount'])      ? $_REQUEST['amount']      : array();
	$comments   = isset($_REQUEST['comment'])     ? $_REQUEST['comment']     : array();
	$tmpreasons = isset($_REQUEST['temp_reason']) ? $_REQUEST['temp_reason'] : array();
	$ids        = isset($_REQUEST['charID'])      ? $_REQUEST['charID']      : array();
	
	$errstat = 0;
	if (isset($_REQUEST['apply2all'])) {
		$errstat = 2;
		for ($i=0;$i<count($ids);$i++) {
			if (!vtm_update_temp_stat($ids[$i], $amount, $stat, $statID, $reasonID, 
							$maximums[$i], $currents[$i], $names[$i], ''))
				$errstat = 1;
		}
	}
	elseif (isset($_REQUEST['applychanges'])) {
		$errstat = 2;
		for ($i=0;$i<count($ids);$i++) {
			if (!empty($amounts[$i]))
				if (!vtm_update_temp_stat($ids[$i], $amounts[$i], $stat, $statID, $tmpreasons[$i], 
							$maximums[$i], $currents[$i], $names[$i], $comments[$i]))
					$errstat = 1;
		}
	}
	if ($errstat == 2) {
		echo "<p style='color:green'>$stat updates completed successfully</p>";
	}

	//Get the data from the database
	$sql = "SELECT 
				chara.ID as ID,
				chara.NAME as CHARACTERNAME,
				SUM(char_temp_stat.AMOUNT) as CURRENTSTAT,
				cstat.LEVEL as MAXSTAT,
				gen.BLOODPOOL as MAXBLOOD
			FROM
				" . VTM_TABLE_PREFIX . "CHARACTER chara,
				" . VTM_TABLE_PREFIX . "CHARACTER_STAT cstat,
				" . VTM_TABLE_PREFIX . "STAT stat,
				" . VTM_TABLE_PREFIX . "PLAYER player,
				" . VTM_TABLE_PREFIX . "PLAYER_STATUS pstatus,
				" . VTM_TABLE_PREFIX . "CHARACTER_TYPE ctype,
				" . VTM_TABLE_PREFIX . "CHARACTER_STATUS cstatus,
				" . VTM_TABLE_PREFIX . "GENERATION gen,
				" . VTM_TABLE_PREFIX . "CHARACTER_TEMPORARY_STAT char_temp_stat,
				" . VTM_TABLE_PREFIX . "TEMPORARY_STAT temp_stat
			WHERE 
				chara.id = cstat.character_id
				AND cstat.stat_id = stat.id
				AND chara.player_id = player.id
				AND player.player_status_id = pstatus.id
				AND chara.character_type_id = ctype.id
				AND chara.generation_id = gen.id
				AND char_temp_stat.character_id = chara.id
				AND char_temp_stat.temporary_stat_id = temp_stat.id
				AND char_temp_stat.character_id = chara.id
				AND chara.character_status_id = cstatus.id
				AND pstatus.name = 'Active'
				AND cstatus.name != 'Dead'
				AND chara.DELETED != 'Y'
				AND chara.VISIBLE = 'Y'
				AND stat.name = %s
				AND temp_stat.name = %s
			GROUP BY chara.id
			ORDER BY chara.name";
	$sql = $wpdb->prepare($sql, $filterstat, $stat);
	$data = $wpdb->get_results($sql, OBJECT_K);	
	
   ?>
   <h2><?php print $stat; ?> Changes</h2>

	<!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
	<form id="<?php print $stat ?>-filter" method="post" action='<?php print htmlentities($current_url); ?>'>
		<input type="hidden" name="page" value="<?php print $_REQUEST['page'] ?>" />
		<input type="hidden" name="tab" value="<?php print $stat ?>" />
 		
		<div class='gvfilter'>
		<select name='<?php print $stat; ?>_reason_bulk'>
		<?php
			foreach ($reasons as $reason) {
				echo "<option value='{$reason->id}'";
				selected($reason->id, $reasonID);
				echo ">" . vtm_formatOutput($reason->name) . "</option>";
			}
		?>
		</select>
		<input type='text' name='<?php print $stat; ?>_amount_bulk' value='<?php print $amount; ?>' size=4 />
		<?php submit_button( 'Apply to all', 'primary', 'apply2all', false ); ?>
		</div>
		<table class="wp-list-table widefat">
		<tr><th class="manage-column">Character</th>
			<th class="manage-column">Current</th>
			<th class="manage-column">Maximum</th>
			<th class="manage-column">Reason</th>
			<th class="manage-column">Amount</th>
			<th class="manage-column">Comment</th>
		</tr>
		<?php
			foreach ($data as $item) {
				?>
				<tr>
					<?php 
					echo "<td><input type='hidden' name='charname[]' value='" . vtm_formatOutput($item->CHARACTERNAME) . "'/>
						<input type='hidden' name='charID[]' value='{$item->ID}'/>
						" . vtm_formatOutput($item->CHARACTERNAME) . "
						<span style='color:silver'>(ID:{$item->ID})</span></td>";
					echo "<td><input type='hidden' name='current[]' value='{$item->CURRENTSTAT}'/>
						{$item->CURRENTSTAT}</td>";
					echo "<td><input type='hidden' name='max[]' value='{$item->$maxcol}'/>
						{$item->$maxcol}</td>";
					echo "<td><select name='temp_reason[]'>\n";
					foreach ($reasons as $reason) {
						echo "<option value='{$reason->id}'>" . vtm_formatOutput($reason->name) . "</option>\n";
					}
					echo "</select></td>\n";
					echo "<td><input type='text' name='amount[]' value='' size=4 /></td>";
					echo "<td><input type='text' name='comment[]' value='' size=30 /></td>";
					?>
				</tr>
				<?php
			}
		?>
		</table>
		<?php submit_button( 'Apply Changes', 'primary', 'applychanges', false ); ?>
	</form>

    <?php
}

function vtm_update_temp_stat($selectedID, $amount, $stat, $statID, $reasonID, 
							$max, $current, $char, $comment) {
	global $wpdb;
	
	$wpdb->show_errors();
		
	$change = $amount;
	
	echo "<ul>";
	if ( $current + $amount > $max ) {
		$change = $max - $current;
		echo "<li><span style='color:orange'>Current $stat for $char is capped at the maximum</span></li>";
	}
	elseif ($current + $amount < 0) {
		$change = $current * -1;
		echo "<li><span style='color:orange'>Current $stat for $char is capped at the minimum of 0</span></li>";
	}
	
	if ($change == 0) {
		echo "<ul>";
		return 1;
	}
	
	$data = array (
		'CHARACTER_ID'      => $selectedID,
		'TEMPORARY_STAT_ID' => $statID,
		'TEMPORARY_STAT_REASON_ID' => $reasonID,
		'AWARDED'  => Date('Y-m-d'),
		'AMOUNT'   => $change,
		'COMMENT'  => $comment
	);
	$wpdb->insert(VTM_TABLE_PREFIX . "CHARACTER_TEMPORARY_STAT",
			$data,
			array (
				'%d',
				'%d',
				'%d',
				'%s',
				'%d',
				'%s'
			)
		);

	if ($wpdb->insert_id == 0) {
		echo "<li><span style='color:red'><b>Error:</b>$stat update for character $char failed</span></li>";
	} else {
		 vtm_touch_last_updated($selectedID);
	}
	echo "<ul>";
	
	return ($wpdb->insert_id != 0);
}


?>