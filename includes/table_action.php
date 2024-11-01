<?php

defined( 'ABSPATH' ) || exit;

if(isset($_POST['action'])){
	$action = sanitize_text_field($_POST['action']);
	$XSDATATABLES_TABLE = XSDATATABLES_TABLE;
	$XSDATATABLES_COLUMN = XSDATATABLES_COLUMN;
	if(isset($_POST['table_id'])){
		if(is_array($_POST['table_id'])){
			$table_id = array_map('sanitize_text_field', $_POST['table_id']);
		}else{
			$table_id = (int) sanitize_text_field($_POST['table_id']);
		}
	}
	if(in_array($action, array('table_getdata_xs', 'table_add_xs', 'table_multi_active_xs', 'table_multi_inactive_xs', 'table_multi_delete_xs'))){
		check_ajax_referer( XSDATATABLES_TABLE, '_xsnonce' );
		check_admin_referer( XSDATATABLES_TABLE, '_xsnonce' );
	}elseif(isset($table_id)){
		check_ajax_referer( 'xs-table'.$table_id, '_xsnonce' );
		check_admin_referer( 'xs-table'.$table_id, '_xsnonce' );
	}
	$tables = xsdatatables_table::column();
	if($action == 'table_getdata_xs'){
		xsdatatables_init::rename_column($XSDATATABLES_COLUMN, 'column_search', 'column_filters', "enum('no','yes')");
		xsdatatables_init::rename_column($XSDATATABLES_COLUMN, 'column_custom_type', 'column_customtype', "text");
		xsdatatables_init::rename_column($XSDATATABLES_COLUMN, 'column_dynamic', 'column_customfilter', "text");
		$query = "SELECT * FROM `{$XSDATATABLES_TABLE}` ";
		$result = $wpdb->get_results($query);
		$filtered_rows = $wpdb->num_rows;
		$output = array();
		$data = array();
		foreach($result as $row){
			$table_id = $row->table_id;
			$xsnonce = wp_create_nonce('xs-table'.$table_id);
			$shortcode = '[xsdatatable id="'.$table_id.'"]';
			$sub_array = array();
			$sub_array[] = '<input type="checkbox" name="table_id[]" id="'.$table_id.'" value="'.$table_id.'" />';
			$sub_array[] = $row->table_name;
			$sub_array[] = $shortcode;
			$sub_array[] = $row->table_type;
			$sub_array[] = $row->table_total;
			$sub_array[] = get_user_by('id', $row->table_author)->display_name;
			if($row->table_status == 'active'){
				$status = '<button type="button" name="table_edit_status_xs" class="btn btn-success btn-xs table_edit_status_xs" style="min-width: 70.25px;" id="'.$table_id.'" data-status="'.$row->table_status.'" data-xsnonce="'.$xsnonce.'">Active</button>';
			}else{
				$status = '<button type="button" name="table_edit_status_xs" class="btn btn-danger btn-xs table_edit_status_xs" id="'.$table_id.'" data-status="'.$row->table_status.'" data-xsnonce="'.$xsnonce.'">Inactive</button>';
			}
			$sub_array[] = $status;
			$sub_array[] = '<a href="admin.php?page=xsdatatables&tab=table&table_id='.$table_id.'&_xsnonce='.$xsnonce.'"><span class="btn btn-info btn-xs update"><span class="dashicons dashicons-visibility"></span></span></a>';
			$sub_array[] = '<button type="button" name="table_edit_xs" class="btn btn-warning btn-xs table_edit_xs" id="'.$table_id.'" data-xsnonce="'.$xsnonce.'"><span class="dashicons dashicons-edit"></span></button>';
			$disabled = "";
			$sub_array[] = '<button type="button" name="table_delete_xs" class="btn btn-danger btn-xs table_delete_xs" data-id="'.$table_id.'" data-xsnonce="'.$xsnonce.'"><span class="dashicons dashicons-no"></span></button>';
			$data[] = $sub_array;
		}
		$output = array("data"	=>	$data);
		echo json_encode($output);
	}
	if($action == 'table_add_xs'){
		if(xsdatatables_init::check_names($XSDATATABLES_TABLE, 'table_name', sanitize_text_field($_POST["table_name"]))){
			echo wp_kses_post('<div class="alert alert-danger">'.esc_html__('The name has already been taken or invalid', 'xsdatatables').'</div>');
			return;
		}
		foreach($tables as $table){
			if(isset($_POST["$table"])){
				$data[$table] = sanitize_text_field($_POST["$table"]);
			}
		}
		$data['table_dom'] = 'l,c,B,f,r,t,i,p';
		$data['table_author'] = get_current_user_id();
		$result = $wpdb->insert($XSDATATABLES_TABLE, $data);
		if($result){
			$id = $wpdb->insert_id;
			$table_new = XSDATATABLES_PREFIX.$id;
			$charset_collate = $wpdb->get_charset_collate();
			$sql = "CREATE TABLE IF NOT EXISTS $table_new (
				column_id mediumint(11) NOT NULL AUTO_INCREMENT,
				column_status enum('active','inactive') NOT NULL,
				PRIMARY KEY  (column_id)
			) $charset_collate;";
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );
			$query = 'ADD `column_1` longtext';
			$querys = "($id, 'column_1', 1, 'column_1')";
			for($x = 2; $x <= (int) sanitize_text_field($_POST["column_number"]); $x++){
				$query .= ", ADD `column_$x` longtext";
				$querys .= ", ($id, 'column_$x', $x, 'column_$x')";
			}
			if(isset($_POST['table_type']) && sanitize_text_field($_POST['table_type']) == 'default'){
				$wpdb->query("ALTER TABLE `{$table_new}` $query");
			}
			$result = $wpdb->query("INSERT INTO `{$XSDATATABLES_COLUMN}` (table_id, column_names, column_order, column_name) VALUES $querys");
			echo wp_kses_post('<div class="alert alert-success">'.esc_html__('The table was added successfully.', 'xsdatatables').'</div>');
		}
	}
	if(isset($table_id)){
		if($action == 'table_single_xs'){
			$query = "SELECT * FROM `{$XSDATATABLES_TABLE}` WHERE table_id = %d";
			$result = $wpdb->get_results($wpdb->prepare($query, $table_id));
			$output = array();
			foreach($result as $row){
				foreach($tables as $table){
					$output["$table"] = $row->$table;
				}
			}
			unset($output['table_dom']);
			$table_dom = xsdatatables_table::get_var($table_id, 'table_dom');
			if(empty($table_dom)){
				$table_dom = 'l,c,B,f,r,t,i,p';
			}
			foreach(explode(',', $table_dom) as $dom){
				if(!empty($dom)){
					$output['table_dom_'.trim($dom)] = trim($dom);
				}
			}
			echo json_encode($output);
		}
		if($action == 'table_edit_xs'){
			if(xsdatatables_init::check_names($XSDATATABLES_TABLE, 'table_name', sanitize_text_field($_POST["table_name"]), $table_id)){
				echo wp_kses_post('<div class="alert alert-danger">'.esc_html__('The name has already been taken or invalid', 'xsdatatables').'</div>');
				return;
			}
			unset($tables[1]);
			foreach($tables as $table){
				if(isset($_POST["$table"])){
					$data[$table] = sanitize_text_field($_POST["$table"]);
				}
			}
			$table_dom = '';
			if(isset($_POST["xs_dom"])){
				$xs_dom = array_map('sanitize_text_field', $_POST["xs_dom"]);
				foreach($xs_dom as $dom){
					$table_dom .= $dom.",";
				}
			}else{
				$table_dom .= "r,t,";
			}
			$data['table_dom'] = substr($table_dom, 0, -1);
			$result = $wpdb->update($XSDATATABLES_TABLE, $data, array('table_id' => $table_id));
			if($result){
				echo wp_kses_post('<div class="alert alert-success">'.esc_html__('The table was saved successfully.', 'xsdatatables').'</div>');
			}
		}
		if($action == 'table_edit_status_xs'){
			$status = 'active';
			if($_POST['status'] == 'active'){
				$status = 'inactive';	
			}
			$data = array(
				'table_status'	=>	$status
			);
			$result = $wpdb->update($XSDATATABLES_TABLE, $data, array('table_id' => $table_id));
			if($result){
				echo wp_kses_post('<div class="alert alert-success">'.esc_html__('Table status change to ', 'xsdatatables').$status.'</div>');
			}
		}
		if($action == 'table_delete_xs'){
			$table = XSDATATABLES_PREFIX.$table_id;
			$result = $wpdb->delete($XSDATATABLES_TABLE, array('table_id' => $table_id));
			$result = $wpdb->delete($XSDATATABLES_COLUMN, array('table_id' => $table_id));
			if($result){
				$wpdb->query("DROP TABLE IF EXISTS `{$table}`");
				echo wp_kses_post('<div class="alert alert-success">'.esc_html__('Table Name Deleted', 'xsdatatables').'</div>');
			}
		}
		if($action == 'table_multi_active_xs'){
			$status = 'active';
			$data = array(
				'table_status'	=>	$status
			);
			$ids = array_unique($table_id);
			foreach($ids as $id){
				$result = $wpdb->update($XSDATATABLES_TABLE, $data, array('table_id' => (int)$id));
			}
			if($result){
				echo wp_kses_post('<div class="alert alert-success">'.esc_html__('Table status change to ', 'xsdatatables').$status.'</div>');
			}
		}
		if($action == 'table_multi_inactive_xs'){
			$status = 'inactive';
			$data = array(
				'table_status'	=>	$status
			);
			$ids = array_unique($table_id);
			foreach($ids as $id){
				$result = $wpdb->update($XSDATATABLES_TABLE, $data, array('table_id' => (int)$id));
			}
			if($result){
				echo wp_kses_post('<div class="alert alert-success">'.esc_html__('Table status change to ', 'xsdatatables').$status.'</div>');
			}
		}
		if($action == 'table_multi_delete_xs'){
			$ids = array_unique($table_id);
			foreach($ids as $id){
				$id = (int)$id;
				$table = XSDATATABLES_PREFIX.$id;
				$result = $wpdb->delete($XSDATATABLES_TABLE, array('table_id' => $id));
				$result = $wpdb->delete($XSDATATABLES_COLUMN, array('table_id' => $id));
				$wpdb->query("DROP TABLE IF EXISTS `{$table}`");
			}
			if($result){
				echo wp_kses_post('<div class="alert alert-success">'.esc_html__('Table Name Deleted', 'xsdatatables').'</div>');
			}
		}
	}
}
?>