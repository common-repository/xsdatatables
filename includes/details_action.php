<?php

defined( 'ABSPATH' ) || exit;

if(isset($_POST["action"]) && isset($_POST['table_id'])){
	$action = sanitize_text_field($_POST["action"]);
	$table_id = (int) sanitize_text_field($_POST['table_id']);
	$table = XSDATATABLES_PREFIX.$table_id;
	if(isset($_POST['row_id'])){
		if(is_array($_POST['row_id'])){
			$row_id = array_map('sanitize_text_field', $_POST['row_id']);
		}else{
			$row_id = (int) sanitize_text_field($_POST['row_id']);
		}
	}
	if(in_array($action, array('row_getdata_xs', 'row_add_xs', 'row_multi_active_xs', 'row_multi_inactive_xs', 'row_multi_duplicate_xs', 'row_multi_delete_xs'))){
		check_ajax_referer( 'xs-table'.$table_id, '_xsnonce' );
		check_admin_referer( 'xs-table'.$table_id, '_xsnonce' );
	}elseif(isset($row_id)){
		check_ajax_referer( 'xs-table'.$table_id.'xs-row'.$row_id, '_xsnonce' );
		check_admin_referer( 'xs-table'.$table_id.'xs-row'.$row_id, '_xsnonce' );
	}else{
		check_ajax_referer( 'xs-table'.$table_id.'_front', '_xsnonce' );
		check_admin_referer( 'xs-table'.$table_id.'_front', '_xsnonce' );
	}
	if($action == 'row_getdata_xs'){
		global $wpdb;
		$data = [];
		$limit = xsdatatables_table::limit($table_id);
		$order_name_type = xsdatatables_column::order_name_type($table_id);
		$order_names = $order_name_type['names'];
		$order_types = $order_name_type['types'];
		$table_total = xsdatatables_table::get_var($table_id, 'table_total');
		$table_serverside = xsdatatables_table::get_var($table_id, 'table_serverside');;
		$recordsTotal = xsdatatables_row::rowCount($table_id);
		if($recordsTotal != $table_total){
			xsdatatables_table::update($table_id, 'table_total', $recordsTotal);
		}
		if($table_serverside != 'yes'){
			$query = "SELECT * FROM `{$table}` ORDER BY column_id ASC";
			if($limit >= 0){
				$query .= " LIMIT {$limit}";
			}
			$result = $wpdb->get_results($query);
		}else{
			$query = "SELECT * FROM `{$table}` WHERE ";
			$order_name = $order_name_type['name'];
			$column_total = xsdatatables_column::rowCount($table_id, 'active');
			for($x = 1; $x <= $column_total + 1; $x++){
				if(isset($_POST["columns"][$x]["search"]["value"]) && !empty($_POST["columns"][$x]["search"]["value"])){
					$value = str_replace(array('^', '\\', '$'), '', $_POST["columns"][$x]["search"]["value"]);
					if($x <= $column_total){
						$query .= $order_names[$x].' LIKE "%'.$value.'%" AND ';
					}
					if($x > $column_total){
						$query .= 'column_status = "'.$value.'" AND ';
					}
				}
			}
			if(isset($_POST["search"]["value"])){
				$query .= '(column_id LIKE "%'.$_POST["search"]["value"].'%" ';
				for ($x = 1; $x <= $column_total; $x++) {
					$value = str_replace(array('^', '$'), '', $_POST["search"]["value"]);
					$query .= 'OR '.$order_name[$x].' LIKE "%'.$value.'%" ';
				}
				$query .= ') ';
			}
			if(isset($_POST["order"])){
				$query .= 'ORDER BY '.$order_name[$_POST['order']['0']['column']].' '.$_POST['order']['0']['dir'].' ';
			}
			$query1 = '';
			if(isset($_POST["length"]) && $_POST["length"] != -1){
				$query1 .= 'LIMIT ' . $_POST['start'] . ', ' . $_POST['length'];
			}
			$result = $wpdb->get_results($query. $query1);
		}
		$output = array();
		foreach($result as $row){
			$row = (array)$row;
			$xsnonce = wp_create_nonce('xs-table'.$table_id.'xs-row'.$row['column_id']);
			$sub_array = array();
			$sub_array[] = '<input type="checkbox" name="column_id[]" id="'.$row['column_id'].'" value="'.$row['column_id'].'" />';
			$sub_array['DT_RowClass'] = 'xsdatatable_'.$table_id.'_row_'.$row['column_id'];
			for ($x = 1; $x <= count($row) - 2; $x++){
				if(isset($order_names[$x])){
					$column_name = $order_names[$x];
					$value = str_replace('”', '"', $row[$column_name]);
					if($order_types[$x] == 'url'){
						$sub_array[] .= make_clickable($value);
					}elseif($order_types[$x] == 'shortcode'){
						$shortcode = explode(' ', ltrim($value))[0];
						if(strpos($shortcode, "[") === 0 && shortcode_exists(trim($shortcode, "["))){
							$sub_array[] .= do_shortcode($value);
						}else{
							$sub_array[] .= $value;
						}
					}elseif($order_types[$x] == 'html'){
						$sub_array[] .= htmlentities($value);
					}else{
						$sub_array[] .= $value;
					}
				}
			}
			if($row['column_status'] == 'active'){
				$status = '<button type="button" name="row_edit_status_xs" class="btn btn-success btn-xs row_edit_status_xs" style="min-width: 70.25px;" id="'.$row['column_id'].'" data-status="'.$row['column_status'].'" data-xsnonce="'.$xsnonce.'">Active</button>';
			}else{
				$status = '<button type="button" name="row_edit_status_xs" class="btn btn-danger btn-xs row_edit_status_xs" id="'.$row['column_id'].'" data-status="'.$row['column_status'].'" data-xsnonce="'.$xsnonce.'">Inactive</button>';
			}
			$sub_array[] = $status;
			$sub_array[] = '<button type="button" name="row_edit_xs" class="btn btn-warning btn-xs row_edit_xs" data-id="'.$row['column_id'].'" data-xsnonce="'.$xsnonce.'"><span class="dashicons dashicons-edit"></span></button>';
			$sub_array[] = '<button type="button" name="row_delete_xs" class="btn btn-danger btn-xs row_delete_xs" data-id="'.$row['column_id'].'" data-xsnonce="'.$xsnonce.'"><span class="dashicons dashicons-no"></span></button>';
			$data[] = $sub_array;
		}
		if($table_serverside != 'yes'){
			$output = array("data"	=>	$data);
		}else{
			$wpdb->get_results($query);
			$recordsFiltered = $wpdb->num_rows;
			$output = array(
				"draw"    			=>	intval($_POST["draw"]),
				"recordsTotal"  	=>	$recordsTotal,
				"recordsFiltered"	=>	$recordsFiltered,
				"data"    			=>	$data
			);
		}
		echo json_encode($output);
	}
	if($action == 'row_getdatas_xs'){
		global $wpdb;
		$data = [];
		$limit = xsdatatables_table::limit($table_id);
		$order_name_type = xsdatatables_column::order_name_type($table_id);
		$order_names = $order_name_type['names'];
		$order_types = $order_name_type['types'];
		$table_serverside = xsdatatables_table::get_var($table_id, 'table_serverside');;
		$recordsTotal = xsdatatables_row::rowCount($table_id, 'active');
		if($table_serverside != 'yes'){
			$query = "SELECT * FROM `{$table}` WHERE column_status = 'active' ORDER BY column_id ASC";
			if($limit >= 0){
				$query .= " LIMIT {$limit}";
			}
			$result = $wpdb->get_results($query);
		}else{
			$query = "SELECT * FROM `{$table}` WHERE column_status = 'active' AND ";
			$order_name = $order_name_type['name'];
			$column_total = xsdatatables_column::rowCount($table_id, 'active');
			for($x = 1; $x <= $column_total; $x++){
				if(isset($_POST["columns"][$x]["search"]["value"]) && !empty($_POST["columns"][$x]["search"]["value"])){
					$value = str_replace(array('^', '\\', '$'), '', $_POST["columns"][$x]["search"]["value"]);
					$query .= $order_names[$x].' LIKE "%'.$value.'%" AND ';
				}
			}
			if(isset($_POST["search"]["value"])){
				$query .= '(column_id LIKE "%'.$_POST["search"]["value"].'%" ';
				for ($x = 1; $x <= $column_total; $x++) {
					$value = str_replace(array('^', '$'), '', $_POST["search"]["value"]);
					$query .= 'OR '.$order_name[$x].' LIKE "%'.$value.'%" ';
				}
				$query .= ') ';
			}
			if(isset($_POST["order"])){
				$query .= 'ORDER BY '.$order_name[$_POST['order']['0']['column']].' '.$_POST['order']['0']['dir'].' ';
			}
			$query1 = '';
			if(isset($_POST["length"]) && $_POST["length"] != -1){
				$query1 .= 'LIMIT ' . $_POST['start'] . ', ' . $_POST['length'];
			}
			$result = $wpdb->get_results($query. $query1);
		}
		$output = array();
		foreach($result as $row){
			$row = (array)$row;
			$xsnonce = wp_create_nonce('xs-table'.$table_id.'xs-row'.$row['column_id']);
			$sub_array = array();
			$sub_array[] = $row['column_id'];
			$sub_array['DT_RowClass'] = 'xsdatatable_'.$table_id.'_row_'.$row['column_id'];
			for ($x = 1; $x <= count($row) - 2; $x++){
				if(isset($order_names[$x])){
					$column_name = $order_names[$x];
					$value = str_replace('”', '"', $row[$column_name]);
					if($order_types[$x] == 'url'){
						$sub_array[] .= make_clickable($value);
					}elseif($order_types[$x] == 'shortcode'){
						$shortcode = explode(' ', ltrim($value))[0];
						if(strpos($shortcode, "[") === 0 && shortcode_exists(trim($shortcode, "["))){
							$sub_array[] .= do_shortcode($value);
						}else{
							$sub_array[] .= $value;
						}
					}elseif($order_types[$x] == 'html'){
						$sub_array[] .= htmlentities($value);
					}else{
						$sub_array[] .= $value;
					}
				}
			}
			$data[] = $sub_array;
		}
		if($table_serverside != 'yes'){
			$output = array("data"	=>	$data);
		}else{
			$wpdb->get_results($query);
			$recordsFiltered = $wpdb->num_rows;
			$output = array(
				"draw"    			=>	intval($_POST["draw"]),
				"recordsTotal"  	=>	$recordsTotal,
				"recordsFiltered"	=>	$recordsFiltered,
				"data"    			=>	$data
			);
		}
		echo json_encode($output);
	}
	if($action == 'row_search_xs'){
		global $wpdb;
		$data = [];
		$limit = xsdatatables_table::limit($table_id);
		$order_name_type = xsdatatables_column::order_name_type($table_id);
		$order_name = $order_name_type['names'];
		$order_type = $order_name_type['types'];
		$xs_column_data = xsdatatables_column::data($table_id, 'active');
		$column_order = xsdatatables_column::order_name($table_id);
		$column_filters = $xs_column_data['column_filters'];
		$query = "SELECT * FROM `{$table}` WHERE column_status = %s";
		foreach(json_decode($column_filters) as $column){
			if(isset($_POST["$column_order[$column]"])){
				$column_value = sanitize_text_field(trim($_POST[$column_order[$column]]));
				$query .= " AND `{$column_order[$column]}` = '$column_value'";
			}
		}
		if(!isset($column_value)){
			$output = array("data"	=>	$data);
			echo json_encode($output);
			return;
		}
		$query .= " ORDER BY column_id ASC";
		if($limit >= 0){
			$query .= " LIMIT {$limit}";
		}
		$result = $wpdb->get_results($wpdb->prepare($query, 'active'));
		$output = array();
		foreach($result as $row){
			$row = (array)$row;
			$xsnonce = wp_create_nonce('xs-table'.$table_id.'xs-row'.$row['column_id']);
			$sub_array = array();
			$sub_array[] = $row['column_id'];
			$sub_array['DT_RowClass'] = 'xsdatatable_'.$table_id.'_row_'.$row['column_id'];
			for ($x = 1; $x <= count($row) - 2; $x++){
				if(isset($order_name[$x])){
					$column_name = $order_name[$x];
					$value = str_replace('”', '"', $row[$column_name]);
					if($order_type[$x] == 'url'){
						$sub_array[] .= make_clickable($value);
					}elseif($order_type[$x] == 'shortcode'){
						$shortcode = explode(' ', ltrim($value))[0];
						if(strpos($shortcode, "[") === 0 && shortcode_exists(trim($shortcode, "["))){
							$sub_array[] .= do_shortcode($value);
						}else{
							$sub_array[] .= $value;
						}
					}elseif($order_type[$x] == 'html'){
						$sub_array[] .= htmlentities($value);
					}else{
						$sub_array[] .= $value;
					}
				}
			}
			$data[] = $sub_array;
		}
		$output = array("data"	=>	$data);
		echo json_encode($output);
	}
	if($action == 'row_add_xs'){
		$data = xsdatatables_row::column($table_id);
		$rows = array();
		foreach($data as $row){
			$rows[$row] = stripslashes(wp_kses_post(trim($_POST[$row])));
		}
		$result = $wpdb->insert($table, $rows);
		if($result){
			echo wp_kses_post('<div class="alert alert-success">'.esc_html__('Row Added', 'xsdatatables').'</div>');
		}
	}
	if(isset($row_id)){
		if($action == 'row_single_xs'){
			$query = "SELECT * FROM `{$table}` WHERE column_id = %d";
			$result = $wpdb->get_results($wpdb->prepare($query, $row_id));
			$data = array();
			foreach($result as $row){
				foreach((array)$row as $keys => $rows){
					if(str_replace('column_', '', $keys) != $keys){
						$data[$keys] = $rows;
					}
				}
			}
			echo json_encode($data);
		}
		if($action == 'row_edit_xs'){
			$data = xsdatatables_row::update($table, $row_id);
			$rows = array();
			foreach($data as $key => $row){
				$rows[$key] = stripslashes(wp_kses_post(trim($_POST[$key])));
			}
			if(!empty($row_id)){
				$result = $wpdb->update($table, $rows, array('column_id' => $row_id));
			}
			if($result){
				echo wp_kses_post('<div class="alert alert-success">'.esc_html__('Row Edited', 'xsdatatables').'</div>');
			}
		}
		if($action == 'row_edit_status_xs'){
			$status = 'active';
			if($_POST['status'] == 'active'){
				$status = 'inactive';	
			}
			$data = array(
				'column_status'	=>	$status
			);
			$result = $wpdb->update($table, $data, array('column_id' => $row_id));
			if($result){
				echo wp_kses_post('<div class="alert alert-success">'.esc_html__('Row status change to ', 'xsdatatables').$status.'</div>');
			}
		}
		if($action == 'row_delete_xs'){
			$result = $wpdb->delete($table, array('column_id' => $row_id));
			if($result){
				echo wp_kses_post('<div class="alert alert-success">'.esc_html__('Row has been Deleted', 'xsdatatables').'</div>');
			}
		}
		if($action == 'row_multi_active_xs'){
			$status = 'active';
			$data = array(
				'column_status'	=>	$status
			);
			$ids = array_unique($row_id);
			foreach($ids as $id){
				if(!xsdatatables_row::check_id($table, (int)$id)){
					$result = $wpdb->insert($table, array('column_id' => (int)$id, 'column_status' => $status));
				}else{
					$result = $wpdb->update($table, $data, array('column_id' => (int)$id));
				}
			}
			if($result){
				echo wp_kses_post('<div class="alert alert-success">'.esc_html__('Row status change to ', 'xsdatatables').$status.'</div>');
			}
		}
		if($action == 'row_multi_inactive_xs'){
			$status = 'inactive';
			$data = array(
				'column_status'	=>	$status
			);
			$ids = array_unique($row_id);
			foreach($ids as $id){
				if(!xsdatatables_row::check_id($table, (int)$id)){
					$result = $wpdb->insert($table, array('column_id' => (int)$id, 'column_status' => $status));
				}else{
					$result = $wpdb->update($table, $data, array('column_id' => (int)$id));
				}
			}
			if($result){
				echo wp_kses_post('<div class="alert alert-success">'.esc_html__('Row status change to ', 'xsdatatables').$status.'</div>');
			}
		}
		if($action == 'row_multi_duplicate_xs'){
			$ids = array_unique($row_id);
			foreach($ids as $id){
				$query = "SELECT * FROM `{$table}` WHERE column_id = %d";
				$result = $wpdb->get_results($wpdb->prepare($query, (int)$id));
				if($result){
					foreach($result as $row){
						$row = (array)$row;
						unset($row['column_id']);
					}
				}
				$result = $wpdb->insert($table, $row);
			}
			if($result){
				echo wp_kses_post('<div class="alert alert-success">'.esc_html__('Row Copied', 'xsdatatables').'</div>');
			}
		}
		if($action == 'row_multi_delete_xs'){
			$ids = array_unique($row_id);
			foreach($ids as $id){
				$result = $wpdb->delete($table, array('column_id' => (int)$id));
			}
			if($result){
				echo wp_kses_post('<div class="alert alert-success">'.esc_html__('Row has been Deleted', 'xsdatatables').'</div>');
			}
		}
	}
}
?>