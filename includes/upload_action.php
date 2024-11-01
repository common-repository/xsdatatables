<?php

defined( 'ABSPATH' ) || exit;

include plugin_dir_path( __DIR__ ).'/vendor/phpspreadsheet/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;

if(isset($_POST['table_id']) && isset($_POST['import_type']) && isset($_POST['import_source'])){
	$table_id = (int) sanitize_text_field($_POST['table_id']);
	$import_type = sanitize_text_field($_POST['import_type']);
	$import_source = sanitize_text_field($_POST['import_source']);
	check_ajax_referer( 'xs-import'.$table_id, '_xsnonce' );
	check_admin_referer( 'xs-import'.$table_id, '_xsnonce' );
	session_start();
	$error = '';
	$temp_data = array();
	$column_number = array();
	$header = array();
	$total_line = 0;
	$available = 0;
	if($import_source == 'file'){
		if($_FILES['file']['name'] != ''){
			$allowed_extension = array('csv', 'xls', 'xlsx', 'json');
			$file_array = explode(".", $_FILES['file']['name']);
			$extension = end($file_array);
			if(in_array($extension, $allowed_extension)){
				$phpversion = 8.0;
				if(in_array($extension, array('csv', 'json'))){
					$phpversion = 7.4;
				}
				if(phpversion() < $phpversion){
					$error = esc_html__('Require a PHP version >= '.$phpversion, 'xsdatatables');
				}else{
					//if(!defined('ALLOW_UNFILTERED_UPLOADS')) define('ALLOW_UNFILTERED_UPLOADS', true);
					$file_return = wp_handle_upload($_FILES['file'], array('test_form' => false));
					if(isset($file_return['file'])){
						$tmp_name = $file_return['file'];
						if(in_array($extension, array('csv', 'xls', 'xlsx'))){
							if(in_array($extension, array('xls', 'xlsx'))){
								$reader = IOFactory::createReader(ucwords($extension));
								$spreadsheet = $reader->load($tmp_name);
								$writer = IOFactory::createWriter($spreadsheet, 'Csv');
								$upload_dir = wp_upload_dir();
								$tmp_name = $upload_dir['path'] . rand() . '.csv';
								$writer->save($tmp_name);
							}
							$data = fopen($tmp_name, 'r');
							$header = fgetcsv($data);
							$limit = 0;
							$htmls = '';
							while(($row = fgetcsv($data)) !== FALSE){
								$limit++;
								if($limit <= 5){
									$htmls .= '<tr>';
									for($count = 0; $count < count($row); $count++){
										$htmls .= '<td>'.$row[$count].'</td>';
									}
									$htmls .= '</tr>';
								}
								$temp_data[] = $row;
							}
							$total_line = count($temp_data);
							wp_delete_file($tmp_name);
						}elseif($extension == 'json'){
							$data = json_decode(file_get_contents($tmp_name), true);
							if(isset($data['header']) && isset($data['body'])){
								$header = $data['header'];
								$total_line = count($data['body']);
							}elseif(isset($data[0])){
								$header = array_keys($data[0]);
								$total_line = count($data);
							}else{
								$error = esc_html__('Incorrect JSON format', 'xsdatatables');
							}
						}
						if($total_line > 0 && count($header) > 0){
							$preview = $total_line > 5 ? 5 : $total_line;
							$html = 'Preview the '.$preview.' first result entries (filtered from '.$total_line.' total entries)<p>';
							$html .= '<table class="table table-bordered"><tr>';
							for($count = 0; $count < count($header); $count++){
								$head = $header[$count];
								if(empty($header[$count]) && $header[$count] !== 0){
									$head = 'column_'.($count + 1);
								}
								$html .= '<th style="min-width: 100px;">'.$head.'<select name="set_column_data" id="set_column_data" class="form-control set_column_data" data-column_numbers="'.$count.'">';
								$html .= '<option value="">Select</option>';
								if($import_type == 'append'){
									$html .= xsdatatables_column::names($table_id);
								}else{
									$html .= '<option value="column_'.$count.'">'.$head.'</option>';
								}
								$html .= '</select></th>';
								$column_number[] = 'column_'.$count;
								$keys[] = $header[$count];
							}
							$html .= '</tr>';
							if(in_array($extension, array('csv', 'xls', 'xlsx'))){
								$html .= $htmls;
							}elseif($extension == 'json'){
								$limit = 0;
								$body = isset($data['body']) ? $data['body'] : $data;
								foreach($body as $row){
									$limit++;
									if($limit <= 5){
										$html .= '<tr>';
										foreach($keys as $key => $value){
											if(isset($row[$key])){
												$html .= '<td>'.$row[$key].'</td>';
											}else{
												$html .= '<td></td>';
											}
										}
										$html .= '</tr>';
									}
									foreach($keys as $key => $value){
										if(isset($row[$key])){
											$temp[$key] = $row[$key];
										}else{
											$temp[$key] = '';
										}
									}
									if(isset($temp)){
										$temp_data[] = array_values($temp);
									}
								}
							}
							$_SESSION['temp_data'] = $temp_data;
							$_SESSION['column_number'] = $column_number;
							if(empty($temp_data)){
								$error = esc_html__('empty', 'xsdatatables');
							}
							$html .= '</table>';
							$html .= '<div align="center" style="margin-top: 15px;">';
							$html .= '<button type="button" name="import_file" id="import_file" class="btn btn-secondary btn-xs" disabled>Import</button>';
							$html .= '</div>';
						}
					}else{
						$error = esc_html__('Incorrect format', 'xsdatatables');
					}
				}
			}else{
				$error = esc_html__('Only CSV, XLS, XLSX or JSON file format is allowed', 'xsdatatables');
			}
		}else{
			$error = esc_html__('Please Select File', 'xsdatatables');
		}
	}
	if($error != ''){
		$output = array('error'	=>	$error);
	}else{
		if($import_type == 'append'){
			$available = xsdatatables_row::rowCount($table_id);
		}
		$output = array(
			'column_number'	=>	count($header),
			'available'		=>	$available,
			'total_line'	=>	$total_line,
			'output'		=>	$html
		);
	}
	echo json_encode($output);
}
?>