<?php

defined( 'ABSPATH' ) || exit;

global $XSDATATABLES_ADDONS, $XSDATATABLES_TABLES;

if(isset($table_id)){
	$xs_table_value = xsdatatables_table::get_row($table_id);
	$table_total = $xs_table_value['table_total'];
	$table_type = $xs_table_value['table_type'];
	$table_pagination = $xs_table_value['table_pagination'];
	if(empty($table_pagination)){
		$table_pagination = 'simple_numbers';
	}
	$table_serverside = $xs_table_value['table_serverside'];
	if($table_serverside == 'yes' && $table_type == 'default'){
		$serverSide = "true";
	}else{
		$serverSide = "false";
	}
	$table_footer = $xs_table_value['table_footer'];
	$table_button = $xs_table_value['table_button'];
	if(!empty($XSDATATABLES_ADDONS)){
		foreach($XSDATATABLES_ADDONS as $table){
			$$table = 'no';
		}
	}
	$table_length = (int) $xs_table_value['table_length'];
	$pagelength = xsdatatables_row::pagelength($table_id, $table_length);
	$table_responsive = $xs_table_value['table_responsive'];
	$headerbackground = $xs_table_value['table_headerbackground'];
	$headercolor = $xs_table_value['table_headercolor'];
	$bodybackground = $xs_table_value['table_bodybackground'];
	$bodycolor = $xs_table_value['table_bodycolor'];
	$table_order = $xs_table_value['table_order'];
	$table_dom = str_replace(",", '', $xs_table_value['table_dom']);
	$column_align = xsdatatables_column::align($table_id);
	if($table_order > $column_align['count']){
		$table_order = 0;
	}
	$table_sort = '['. $table_order. ', "' . $xs_table_value['table_sort'] .'"]';
	$xs_column_data = xsdatatables_column::data($table_id, 'active');
	$column_filters = $xs_column_data['column_filters'];
	$column_hidden = $xs_column_data['column_hidden'];
	$column_show = $xs_column_data['column_show'];
	$column_hiddens = $xs_column_data['column_hiddens'];
	$column_orderable = $xs_column_data['column_orderable'];
	$column_searchable = $xs_column_data['column_searchable'];
	$column_default = $xs_column_data['column_default'];
	$column_footer = $xs_column_data['column_footer'];
	$column_total = $xs_column_data['column_total'];
	$column_customfilter = $xs_column_data['column_customfilter'];
	$column_customtype = $xs_column_data['column_customtype'];
	$column_order = $xs_column_data['column_order'];
	$column_count = $xs_column_data['column_count'];
	$align_left = $xs_column_data['column_left'];
	$align_center = $xs_column_data['column_center'];
	$align_right = $xs_column_data['column_right'];
	$column_names = $xs_column_data['column_names'];
	$column_width = $xs_column_data['column_width'];
	$column_color = $xs_column_data['column_color'];
	$column_colors = $xs_column_data['column_colors'];
	$column_index = $xs_column_data['column_index'];
	$table_name = $xs_table_value['table_name'];
	$allowed_select = array('option' => array('value' => array()));
}

?>