<?php

defined( 'ABSPATH' ) || exit;

if(isset($_POST['action']) && isset($_POST['table_id'])){
	$action = sanitize_text_field($_POST['action']);
	$table_id = (int) sanitize_text_field($_POST['table_id']);
	$table = XSDATATABLES_PREFIX.$table_id;
	if(isset($_POST['row_id'])){
		if(is_array($_POST['row_id'])){
			$row_id = array_map('sanitize_text_field', $_POST['row_id']);
		}else{
			$row_id = (int) sanitize_text_field($_POST['row_id']);
		}
	}
	if(in_array($action, array('row_getdata_xs', 'row_multi_active_xs', 'row_multi_inactive_xs'))){
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
		$order_name = xsdatatables_column::order_name($table_id);
		$order_id = xsdatatables_column::order_id($table_id);
		$category_id = xsdatatables_table::category($table_id);
		$output = array();
		$data = array();
		$column_total = xsdatatables_column::rowCount($table_id);
		$limit = xsdatatables_table::limit($table_id);
		foreach(xsdatatables_woocommerce::order($category_id, $limit) as $row){
			$row = array_values($row);
			$xsnonce = wp_create_nonce('xs-table'.$table_id.'xs-row'.$row[0]);
			$sub_array = array();
			$sub_array[] = '<input type="checkbox" name="column_id[]" id="'.$row[0].'" value="'.$row[0].'" />';
			$sub_array['DT_RowClass'] = 'xsdatatable_'.$table_id.'_row_'.$row[0];
			for ($x = 1; $x <= $column_total; $x++){
				if(xsdatatables_column::status($table_id, $order_id[$x])){
					$value = str_replace('column_', '', $order_name[$x]);
					$sub_array[] .= $row[$value];
				}
			}
			if(xsdatatables_row::check_id($table, $row[0]) && !xsdatatables_row::status($table, $row[0])){
				$status = '<button type="button" name="row_edit_status_xs" class="btn btn-danger btn-xs row_edit_status_xs" id="'.$row[0].'" data-status="inactive" data-xsnonce="'.$xsnonce.'">Inactive</button>';
			}else{
				$status = '<button type="button" name="row_edit_status_xs" class="btn btn-success btn-xs row_edit_status_xs" style="min-width: 70.25px;" id="'.$row[0].'" data-status="active" data-xsnonce="'.$xsnonce.'">Active</button>';
			}
			$sub_array[] = $status;
			$data[] = $sub_array;
		}
		$table_total = xsdatatables_table::get_var($table_id, 'table_total');
		$recordsTotal = count($data);
		if($recordsTotal != $table_total){
			xsdatatables_table::update($table_id, 'table_total', $recordsTotal);
		}
		$output = array("data"	=>	$data);
		echo json_encode($output);
	}
	if(isset($row_id)){
		if($action == 'row_edit_status_xs'){
			$status = 'active';
			if(sanitize_text_field($_POST['status']) == 'active'){
				$status = 'inactive';	
			}
			$data = array(
				'column_status'	=>	$status
			);
			if(!xsdatatables_row::check_id($table, $row_id)){
				$result = $wpdb->insert($table, array('column_id' => $row_id, 'column_status' => $status));
			}else{
				$result = $wpdb->update($table, $data, array('column_id' => $row_id));
			}
			if($result){
				echo wp_kses_post('<div class="alert alert-success">'.esc_html__('Row status change to ', 'xsdatatables').$status.'</div>');
			}
		}
		if($action == 'row_multi_active_xs'){
			$status = 'active';
			$data = array(
				'column_status'	=>	$status
			);
			$ids = array_unique($row_id);
			foreach($ids as $id){
				if(!xsdatatables_row::check_id($table, $id)){
					$result = $wpdb->insert($table, array('column_id' => $id, 'column_status' => $status));
				}else{
					$result = $wpdb->update($table, $data, array('column_id' => $id));
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
				if(!xsdatatables_row::check_id($table, $id)){
					$result = $wpdb->insert($table, array('column_id' => $id, 'column_status' => $status));
				}else{
					$result = $wpdb->update($table, $data, array('column_id' => $id));
				}
			}
			if($result){
				echo wp_kses_post('<div class="alert alert-success">'.esc_html__('Row status change to ', 'xsdatatables').$status.'</div>');
			}
		}
	}
}

?>