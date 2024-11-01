<?php

defined( 'ABSPATH' ) || exit;

header('Content-type: text/html; charset=utf-8');
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");
set_time_limit(300);
ob_implicit_flush(1);
session_start();

if(isset($_POST["action"]) && isset($_POST["table_id"])){
	$action = sanitize_text_field($_POST["action"]);
	$table_id = (int) sanitize_text_field($_POST["table_id"]);
	check_ajax_referer( 'xs-import'.$table_id, '_xsnonce' );
	check_admin_referer( 'xs-import'.$table_id, '_xsnonce' );
	if($action == 'confirm_xs'){
		$output = array(
			'success'	=>	true,
		);
		echo json_encode($output);
	}elseif($action == 'import_xs' && isset($_POST["import_type"]) && isset($_SESSION['temp_data']) && isset($_SESSION['column_number'])){
		$import_type = sanitize_text_field($_POST["import_type"]);
		$temp_data = (array)$_SESSION['temp_data'];
		unset($_SESSION['temp_data']);
		$table = XSDATATABLES_PREFIX.$table_id;
		$XSDATATABLES_COLUMN = XSDATATABLES_COLUMN;
		if($import_type == 'append'){
			$import = xsdatatables_column::import($table_id);
			$query1 = $import['query'];
			$column_names = $import['name'];
		}else{
			$column_names = array_map('sanitize_text_field', (array)$_SESSION['column_number']);
			unset($_SESSION['column_number']);
			$total_column = (int) sanitize_text_field($_POST['column_number']);
			$query1 = 'column_1';
			$query2 = 'ADD `column_1` longtext';
			$query3 = "($table_id, 'column_1', 1, 'column_1')";
			for($x = 2; $x <= $total_column; $x++){
				$query1 .= ", column_$x";
				$query2 .= ", ADD `column_$x` longtext";
				$query3 .= ", ($table_id, 'column_$x', $x, 'column_$x')";
			}
			xsdatatables_import::import($table);
			$wpdb->query("ALTER TABLE `{$table}` $query2");
			$wpdb->delete($XSDATATABLES_COLUMN, array('table_id' => $table_id));
			$wpdb->query("INSERT INTO `{$XSDATATABLES_COLUMN}` (table_id, column_names, column_order, column_name) VALUES $query3");
		}
		foreach($temp_data as $row){
			$row = array_map('wp_kses_post', $row);
			$value = '';
			foreach($column_names as $name){
				if(isset($_POST[$name])){
					$value .= '"'.str_replace('"', '”', rtrim(ltrim($row[sanitize_text_field($_POST[$name])], '"'), '"')).'", ';
				}
			}
			$value = rtrim($value, ", ");
			$wpdb->query("INSERT INTO `{$table}` ($query1) VALUES ($value)");
			if(ob_get_level() > 0){
				ob_end_flush();
			}
		}
	}
}
?>