<?php

defined( 'ABSPATH' ) || exit;

if(isset($_POST["action"]) && isset($_POST['table_id'])){
	$action = sanitize_text_field($_POST["action"]);
	$table_id = (int) sanitize_text_field($_POST['table_id']);
	$table = XSDATATABLES_PREFIX.$table_id;
	$table_type = xsdatatables_table::type($table_id);
	$tables = xsdatatables_column::column();
	$XSDATATABLES_COLUMN = XSDATATABLES_COLUMN;
	if(isset($_POST['column_id'])){
		if(is_array($_POST['column_id'])){
			$column_id = array_map('sanitize_text_field', $_POST['column_id']);
		}else{
			$column_id = (int) sanitize_text_field($_POST['column_id']);
		}
	}
	if(in_array($action, array('column_getdata_xs', 'column_add_xs', 'column_multi_active_xs', 'column_multi_inactive_xs', 'column_multi_duplicate_xs', 'column_multi_delete_xs'))){
		check_ajax_referer( 'xs-table'.$table_id, '_xsnonce' );
		check_admin_referer( 'xs-table'.$table_id, '_xsnonce' );
	}elseif(isset($column_id)){
		check_ajax_referer( 'xs-table'.$table_id.'xs-column'.$column_id, '_xsnonce' );
		check_admin_referer( 'xs-table'.$table_id.'xs-column'.$column_id, '_xsnonce' );
	}

	if($action == 'column_getdata_xs'){
		$data = array();
		if($table_type == 'product' && !function_exists('wc_get_products')){
			echo json_encode($data);
			return;
		}elseif(in_array($table_type, array('post', 'page'))){
			$table_types = xsdatatables_post::type($table_type);
		}elseif($table_type == 'product'){
			$table_types = xsdatatables_woocommerce::product_types();
		}elseif($table_type == 'order'){
			$table_types = xsdatatables_woocommerce::order_types();
		}
		$query = "SELECT * FROM `{$XSDATATABLES_COLUMN}` WHERE table_id = %d ORDER BY column_order ASC";
		$result = $wpdb->get_results($wpdb->prepare($query, $table_id));
		foreach($result as $rows){
			$sub_array = array();
			foreach($rows as $column => $row){
				$sub_array[$column] = $row;
				if($table_type == 'default' && $column == 'column_type' && empty($row)){
					$sub_array[$column] = 'textarea';
				}
				if(isset($table_types) && $column == 'column_name'){
					$sub_array['column_type'] = $table_types[$row];
				}
			}
			$sub_array['xsnonce'] = wp_create_nonce('xs-table'.$table_id.'xs-column'.$rows->id);
			$data[] = $sub_array;
		}
		echo json_encode($data);
	}

	if($action == 'column_update_order_xs' && isset($_POST["table_id_array"])){
		$table_id_array = array_map('sanitize_text_field', $_POST["table_id_array"]);
		for($count = 0;  $count < count($table_id_array); $count++){
			$data = array(
				'column_order'	=>	$count + 1
			);
			$id = (int) $table_id_array[$count];
			$wpdb->update($XSDATATABLES_COLUMN, $data, array('id' => $id));
		}
	}

	if($action == 'column_add_xs'){
		$column_names = sanitize_text_field($_POST["column_names"]);
		if(xsdatatables_init::check_name($XSDATATABLES_COLUMN, $table_id, 'column_names', $column_names)){
			echo wp_kses_post('<div class="alert alert-danger">'.esc_html__('The name has already been taken or invalid', 'xsdatatables').'</div>');
			return;
		}
		$query = "SELECT MAX(column_order) as column_order FROM `{$XSDATATABLES_COLUMN}` WHERE table_id = %d";
		$result = $wpdb->get_results($wpdb->prepare($query, $table_id));
		foreach($result as $row){
			$max_order = $row->column_order;
		}
		$column_name = 'column_'. (xsdatatables_row::column_name($table) + 1);
		if(isset($_POST["column_name"])){
			$column_name = sanitize_text_field($_POST["column_name"]);
		}
		foreach($tables as $_table){
			if(isset($_POST["$_table"])){
				$data[$_table] = sanitize_text_field($_POST["$_table"]);
			}
		}
		$column_customfilter = implode(";", array_filter(array_unique(array_map('sanitize_text_field', $_POST["column_customfilter"])), 'strlen'));
		$column_customtype = implode(";", array_filter(array_unique(array_map('sanitize_text_field', $_POST["column_customtype"])), 'strlen'));
		$data['column_customfilter'] = $column_customfilter;
		$data['column_customtype'] = $column_customtype;
		$data['table_id'] = $table_id;
		$data['column_order'] = $max_order + 1;
		$data['column_name'] = $column_name;
		$result = $wpdb->insert($XSDATATABLES_COLUMN, $data);
		if($result){
			if($table_type == 'default'){
				$wpdb->query("ALTER TABLE `{$table}` ADD `{$column_name}` LONGTEXT");
			}
			echo wp_kses_post('<div class="alert alert-success">'.esc_html__('Column ', 'xsdatatables').$column_names.esc_html__(' Added', 'xsdatatables').'</div>');
		}
	}

	if(isset($column_id)){
		if($action == 'column_single_xs'){
			$query = "SELECT * FROM `{$XSDATATABLES_COLUMN}` WHERE id = %d";
			$result = $wpdb->get_results($wpdb->prepare($query, $column_id));
			$output = array();
			foreach($result as $row){
				foreach($tables as $table){
					$output["$table"] = $row->$table;
					$count = 1;
					$column_customfilter = '';
					if($table_type == 'default' && empty($row->column_customfilter)){
						$dynamic_array = xsdatatables_column::custom_filter($table_id, $column_id);
						if(empty($dynamic_array)){
							$dynamic_array = array('');
						}
					}else{
						$dynamic_array = explode(";", $row->column_customfilter);
					}
					foreach($dynamic_array as $dynamic){
						$button = '';
						if($count > 1){
							$button = '<button type="button" name="remove" id="'.$count.'" class="btn btn-danger btn-xs remove">x</button>';
						}else{
							$button = '<button type="button" name="add_more" id="add_more" class="btn btn-success btn-xs">+</button>';
						}
						$column_customfilter .= '
							<tr id="row'.$count.'">
								<td style="padding:5px"><input type="text" name="column_customfilter[]" placeholder="option" class="form-control name_list" value="'.$dynamic.'" /></td>
								<td align="center" style="width:45px">'.$button.'</td>
							</tr>
						';
						$count++;
					}
					$output["column_customfilter"] = $column_customfilter;
					$count = 1;
					$column_customtype = '';
					$dynamic_array = explode(";", $row->column_customtype);
					foreach($dynamic_array as $dynamic){
						$button = '';
						if($count > 1){
							$button = '<button type="button" name="remove_option" id="'.$count.'" class="btn btn-danger btn-xs remove_option">x</button>';
						}else{
							$button = '<button type="button" name="add_option" id="add_option" class="btn btn-success btn-xs">+</button>';
						}
						$column_customtype .= '
							<tr id="rows'.$count.'">
								<td style="padding:5px"><input type="text" name="column_customtype[]" placeholder="option" class="form-control name_list" value="'.$dynamic.'" /></td>
								<td align="center" style="width:45px">'.$button.'</td>
							</tr>
						';
						$count++;
					}
					$output["column_customtype"] = $column_customtype;
				}
			}
			echo json_encode($output);
		}

		if($action == 'column_edit_status_xs'){
			$status = 'active';
			if($_POST['status'] == 'active'){
				$status = 'inactive';	
			}
			$data = array(
				'column_status'	=>	$status
			);
			$result = $wpdb->update($XSDATATABLES_COLUMN, $data, array('id' => $column_id));
			if($result){
				echo wp_kses_post('<div class="alert alert-success">'.esc_html__('Column status change to ', 'xsdatatables').$status.'</div>');
			}
		}

		if($action == 'column_edit_xs'){
			$column_names = sanitize_text_field($_POST["column_names"]);
			if(xsdatatables_init::check_name($XSDATATABLES_COLUMN, $table_id, 'column_names', $column_names, $column_id)){
				echo wp_kses_post('<div class="alert alert-danger">'.esc_html__('The name has already been taken or invalid', 'xsdatatables').'</div>');
				return;
			}
			foreach($tables as $table){
				if(isset($_POST["$table"])){
					$data[$table] = sanitize_text_field($_POST["$table"]);
				}
			}
			if(isset($_POST["column_customfilter"])){
				$column_customfilter = implode(";", array_filter(array_unique(array_map('sanitize_text_field', $_POST["column_customfilter"])), 'strlen'));
				$data['column_customfilter'] = $column_customfilter;
			}
			if(isset($_POST["column_customtype"])){
				$column_customtype = implode(";", array_filter(array_unique(array_map('sanitize_text_field', $_POST["column_customtype"])), 'strlen'));
				$data['column_customtype'] = $column_customtype;
			}
			$result = $wpdb->update($XSDATATABLES_COLUMN, $data, array('id' => $column_id));
			if($result){
				echo wp_kses_post('<div class="alert alert-success">'.esc_html__('Column ', 'xsdatatables').$column_names.esc_html__(' Edited', 'xsdatatables').'</div>');
			}
		}

		if($action == 'column_delete_xs'){
			$column_name = xsdatatables_column::id_name($table_id)[$column_id];
			if($table_type == 'default'){
				$wpdb->query("ALTER TABLE `{$table}` DROP `{$column_name}`");
			}
			$result = $wpdb->delete($XSDATATABLES_COLUMN, array('id' => $column_id));
			if($result){
				xsdatatables_column::update($table_id);
				echo wp_kses_post('<div class="alert alert-success">'.esc_html__('Column has been Deleted', 'xsdatatables').'</div>');
			}
		}

		if($action == 'column_multi_active_xs'){
			$ids = array_unique($column_id);
			$status = 'active';
			$data = array(
				'column_status'	=>	$status
			);
			foreach($ids as $id){
				$result = $wpdb->update($XSDATATABLES_COLUMN, $data, array('id' => (int)$id));
			}
			if($result){
				echo wp_kses_post('<div class="alert alert-success">'.esc_html__('Column status change to ', 'xsdatatables').$status.'</div>');
			}
		}

		if($action == 'column_multi_inactive_xs'){
			$ids = array_unique($column_id);
			$status = 'inactive';
			$data = array(
				'column_status'	=>	$status
			);
			foreach($ids as $id){
				$result = $wpdb->update($XSDATATABLES_COLUMN, $data, array('id' => (int)$id));
			}
			if($result){
				echo wp_kses_post('<div class="alert alert-success">'.esc_html__('Column status change to ', 'xsdatatables').$status.'</div>');
			}
		}

		if($action == 'column_multi_duplicate_xs'){
			$ids = array_unique($column_id);
			foreach($ids as $id){
				$query = "SELECT * FROM `{$XSDATATABLES_COLUMN}` WHERE id = %d";
				$result = $wpdb->get_results($wpdb->prepare($query, (int)$id));
				if($result){
					$column_name = 'column_'. (xsdatatables_row::column_name($table) + 1);
					foreach($result as $row){
						$row = (array)$row;
						$row['column_names'] = 'Copy of '.$row['column_names'];
						$row['column_order'] = xsdatatables_row::column_name($table) + 1;
						if($table_type == 'default'){
							$row['column_name'] = $column_name;
						}
						unset($row['id']);
					}
					$result = $wpdb->insert($XSDATATABLES_COLUMN, $row);
					if($result && $table_type == 'default'){
						$wpdb->query("ALTER TABLE `{$table}` ADD `{$column_name}` LONGTEXT");
					}
				}
			}
			if($result){
				xsdatatables_column::update($table_id);
				echo wp_kses_post('<div class="alert alert-success">'.esc_html__('Column Copied', 'xsdatatables').'</div>');
			}
		}

		if($action == 'column_multi_delete_xs'){
			$ids = array_unique($column_id);
			foreach($ids as $id){
				$column_name = xsdatatables_column::id_name($table_id)[(int)$id];
				if($table_type == 'default'){
					$wpdb->query("ALTER TABLE `{$table}` DROP `{$column_name}`");
				}
				$result = $wpdb->delete($XSDATATABLES_COLUMN, array('id' => (int)$id));
			}
			if($result){
				xsdatatables_column::update($table_id);
				echo wp_kses_post('<div class="alert alert-success">'.esc_html__('Column has been Deleted', 'xsdatatables').'</div>');
			}
		}
	}
}
?>