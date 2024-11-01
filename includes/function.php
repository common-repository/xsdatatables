<?php

defined( 'ABSPATH' ) || exit;

global $wpdb;

if ( ! defined( 'XSDATATABLES_TABLE' ) ) define( 'XSDATATABLES_TABLE', $wpdb->prefix . 'xsdatatables_table' );
if ( ! defined( 'XSDATATABLES_PREFIX' ) ) define( 'XSDATATABLES_PREFIX', $wpdb->prefix . 'xsdatatables_table_' );
if ( ! defined( 'XSDATATABLES_COLUMN' ) ) define( 'XSDATATABLES_COLUMN', $wpdb->prefix . 'xsdatatables_column' );

$XSDATATABLES_ADDONS = array();

if ( ! class_exists( 'xsdatatables_init' ) ) {
	class xsdatatables_init {
		public function __construct(){
			if( is_admin() && current_user_can( 'manage_options' ) ) {
				add_action( 'admin_post_uninstall_xsdatatables', array( $this, 'uninstall' ) );
				add_action( 'admin_post_export_xsdatatables', array( $this, 'export' ) );
				add_filter( 'upload_mimes', array( $this, 'json_mime_types' ) );
			}
		}
		public static function json_mime_types( $mimes ) {
			$mimes['json'] = 'text/plain';
			return $mimes;
		}
		public static function check_name($table, $table_id, $key, $value, $id = null){
			global $wpdb;
			if(empty(trim($value))){
				return true;
			}
			$query = "SELECT `{$key}` FROM `{$table}` WHERE table_id = %d AND $key = %s";
			if(!empty($id)){
				$query .= " AND NOT id = %d";
				$result = $wpdb->get_var($wpdb->prepare($query, $table_id, $value, $id));
			}else{
				$result = $wpdb->get_var($wpdb->prepare($query, $table_id, $value));
			}
			if(!empty($result)){
				return true;
			}else{
				return false;
			}
		}
		public static function check_names($table, $key, $value, $table_id = null){
			global $wpdb;
			if(empty(trim($value))){
				return true;
			}
			$query = "SELECT `{$key}` FROM `{$table}` WHERE $key = %s";
			if(!empty($table_id)){
				$query .= " AND NOT table_id = %d";
				$result = $wpdb->get_var($wpdb->prepare($query, $value, $table_id));
			}else{
				$result = $wpdb->get_var($wpdb->prepare($query, $value));
			}
			if(!empty($result)){
				return true;
			}else{
				return false;
			}
		}
		public static function activation_hook() {
			global $wpdb;
			$XSDATATABLES_TABLE = XSDATATABLES_TABLE;
			$XSDATATABLES_COLUMN = XSDATATABLES_COLUMN;
			$charset_collate = $wpdb->get_charset_collate();
			$sql = "CREATE TABLE IF NOT EXISTS $XSDATATABLES_TABLE (
				table_id bigint(20) NOT NULL AUTO_INCREMENT,
				table_name varchar(250) NOT NULL,
				table_type enum('default','product','order','post','page') NOT NULL,
				table_responsive enum('no','yes') NOT NULL,
				table_length int(11) NOT NULL,
				table_footer enum('no','yes') NOT NULL,
				table_button enum('no','yes') NOT NULL,
				table_order int(2) NOT NULL,
				table_sort enum('asc','desc') NOT NULL,
				table_category varchar(250) NOT NULL,
				table_pagination varchar(250) NOT NULL,
				table_serverside enum('no','yes') NOT NULL,
				table_limit int(11) NOT NULL,
				table_headerbackground enum('default','custom') NOT NULL,
				table_headercolor varchar(250) NOT NULL,
				table_bodybackground enum('default','custom') NOT NULL,
				table_bodycolor	 varchar(250) NOT NULL,
				table_dom text NOT NULL,
				table_note text NOT NULL,
				table_status enum('active','inactive') NOT NULL,
				table_author int(11) NOT NULL,
				table_total int(11) NOT NULL,
				PRIMARY KEY (table_id)
			) $charset_collate;";
			$sqls = "CREATE TABLE IF NOT EXISTS $XSDATATABLES_COLUMN (
				id bigint(20) NOT NULL AUTO_INCREMENT,
				table_id bigint(20) NOT NULL,
				column_names varchar(250) NOT NULL,
				column_order int(2) NOT NULL,
				column_filters enum('no','yes') NOT NULL,
				column_position enum('default','footer','hidden') NOT NULL,
				column_filter enum('no','yes') NOT NULL,
				column_hidden enum('no','yes') NOT NULL,
				column_total enum('no','yes') NOT NULL,
				column_orderable enum('yes','no') NOT NULL,
				column_searchable enum('yes','no') NOT NULL,
				column_type varchar(250) NOT NULL,
				column_width int(2) NOT NULL,
				column_align enum('left','center','right') NOT NULL,
				column_status enum('active','inactive') NOT NULL,
				column_name varchar(250) NOT NULL,
				column_background enum('default','custom') NOT NULL,
				column_color varchar(250) NOT NULL,
				column_customfilter text NOT NULL,
				column_customtype text NOT NULL,
				PRIMARY KEY (id)
			) $charset_collate;";
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );
			dbDelta( $sqls );
		}
		public static function url_uninstall(){
			$args = array('action' => 'uninstall_xsdatatables');
			$url = wp_nonce_url(add_query_arg($args, admin_url('admin-post.php')), 'uninstall_xsdatatables');
			return $url;
		}
		public static function url_export($table_id, $xs_type){
			$args = array('action' => 'export_xsdatatables', 'table_id' => $table_id, 'xs_type' => $xs_type );
			$url = wp_nonce_url(add_query_arg($args, admin_url('admin-post.php')), 'export_xsdatatables');
			return $url;
		}
		public static function uninstall(){
			global $wpdb;
			if(current_user_can('deactivate_plugin', XSDATATABLES_MAIN) && wp_verify_nonce($_GET['_wpnonce'], 'uninstall_xsdatatables')){
				$query = "SELECT table_id FROM ".XSDATATABLES_TABLE;
				$result = $wpdb->get_results($query);
				foreach($result as $row){
					$table = XSDATATABLES_PREFIX.$row->table_id;
					$wpdb->query("DROP TABLE IF EXISTS $table");
				}
				$wpdb->query("DROP TABLE IF EXISTS ".XSDATATABLES_COLUMN);
				$wpdb->query("DROP TABLE IF EXISTS ".XSDATATABLES_TABLE);
				@delete_option( 'xs_custom_css' );
				wp_redirect('plugins.php');
				@deactivate_plugins(XSDATATABLES_MAIN);
			}else{
				wp_die( __( 'Sorry, you are not allowed to access this page.', 'default' ), 403 );
			}
		}
		public static function export(){
			if(wp_verify_nonce($_GET['_wpnonce'], 'export_xsdatatables')){
				if(isset($_GET['table_id']) && isset($_GET['xs_type'])){
					$table_id = (int)sanitize_text_field($_GET['table_id']);
					$xs_type = sanitize_text_field($_GET['xs_type']);
					$download_data = xsdatatables_column::data_export($table_id, $xs_type);
					$table_name = 'xsdatatables_table_id_'.$table_id.'.csv';
					// Send download headers for export file.
					header( 'Content-Description: File Transfer' );
					header( 'Content-Type: application/octet-stream' );
					header( "Content-Disposition: attachment; filename=\"{$table_name}\"" );
					header( 'Content-Transfer-Encoding: binary' );
					header( 'Expires: 0' );
					header( 'Cache-Control: must-revalidate' );
					header( 'Pragma: public' );
					header( 'Content-Length: ' . strlen( $download_data ) );
					@ob_end_clean();
					flush();
					echo $download_data;
					exit;
				}
			}else{
				wp_die( __( 'Sorry, you are not allowed to access this page.', 'default' ), 403 );
			}
		}
		public static function check_tables($table_name){
			global $wpdb;
		    $query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table_name ) );
		    if ( $wpdb->get_var( $query ) === $table_name ) {
		        return true;
		    }
		    return false;
		}
		public static function check_column($table_name, $column_name){
			global $wpdb;
			if(self::check_tables($table_name)){
			    foreach ( $wpdb->get_col( "DESC $table_name", 0 ) as $column ) {
			        if ( $column === $column_name ) {
			            return true;
			        }
			    }
			}
		    return false;
		}
		public static function add_column($table_name, $column_name, $type){
			global $wpdb;
			foreach ( $wpdb->get_col( "DESC $table_name", 0 ) as $column ) {
				if ( $column === $column_name ) {
					return true;
				}
			}
			$wpdb->query( "ALTER TABLE `{$table_name}` ADD `{$column_name}` $type NOT NULL" );
			foreach ( $wpdb->get_col( "DESC $table_name", 0 ) as $column ) {
				if ( $column === $column_name ) {
					return true;
				}
			}
			return false;
		}
		public static function drop_column($table_name, $column_name){
			global $wpdb;
			foreach ( $wpdb->get_col( "DESC $table_name", 0 ) as $column ) {
				if ( $column === $column_name ) {
					$wpdb->query( "ALTER TABLE `{$table_name}` DROP `{$column_name}`" );
					foreach ( $wpdb->get_col( "DESC $table_name", 0 ) as $column ) {
						if ( $column === $column_name ) {
							return false;
						}
					}
				}
			}
			return true;
		}
		public static function rename_column($table_name, $column_old, $column_new, $type){
			global $wpdb;
			if(self::check_column($table_name, $column_old) && !self::check_column($table_name, $column_new)){
				$wpdb->query("ALTER TABLE `{$table_name}` CHANGE `{$column_old}` `{$column_new}` $type NOT NULL");
			}
		}
		public static function rename_table($table_old, $table_new){
			global $wpdb;
			if(self::check_tables($table_old)){
				$wpdb->query("RENAME TABLE `{$table_old}` TO `{$table_new}`");
			}
		}
	}
	new xsdatatables_init();
}

if ( ! class_exists( 'xsdatatables_table' ) ) {
	class xsdatatables_table {
		public static function get_var($table_id, $column){
			global $wpdb;
			$query = "SELECT `{$column}` FROM ".XSDATATABLES_TABLE." WHERE table_id = %d";
			$output = $wpdb->get_var($wpdb->prepare($query, $table_id));
			return $output;
		}
		public static function get_row($table_id){
			global $wpdb;
			$query = "SELECT * FROM ".XSDATATABLES_TABLE." WHERE table_id = %d";
			$output = $wpdb->get_row($wpdb->prepare($query, $table_id), ARRAY_A);
			return $output;
		}
		public static function name($table_id){
			global $wpdb;
			$query = "SELECT table_name FROM ".XSDATATABLES_TABLE." WHERE table_id = %d";
			$output = $wpdb->get_var($wpdb->prepare($query, $table_id));
			return $output;
		}
		public static function type($table_id){
			global $wpdb;
			$query = "SELECT table_type FROM ".XSDATATABLES_TABLE." WHERE table_id = %d";
			$output = $wpdb->get_var($wpdb->prepare($query, $table_id));
			return $output;
		}
		public static function all_type(){
			global $wpdb;
			$query = "SELECT table_type FROM ".XSDATATABLES_TABLE;
			$output = $wpdb->get_results($query);
			$select = '';
			if(!empty($output)){
				foreach($output as $column){
					$types[] = $column->table_type;
				}
			}
			if(isset($types)){
				foreach(array_unique($types) as $type){
					$select .= "<option value=$type >$type</option>";
				}
			}
			return $select;
		}
		public static function limit($table_id){
			global $wpdb;
			$query = "SELECT table_limit FROM ".XSDATATABLES_TABLE." WHERE table_id = %d";
			$output = $wpdb->get_var($wpdb->prepare($query, $table_id));
			return $output;
		}
		public static function category($table_id){
			global $wpdb;
			$output = array();
			$query = "SELECT table_category FROM ".XSDATATABLES_TABLE." WHERE table_id = %d";
			$result = $wpdb->get_var($wpdb->prepare($query, $table_id));
			if(empty($result)){
				return $output;
			}else{
				$output = explode(',', $result);
			}
			if(!empty($output)){
				$outputs = array();
				foreach($output as $id){
					if(is_numeric($id)){
						$outputs[] = $id;
					}
				}
				return $outputs;
			}
			return $output;
		}
		public static function status($table_id){
			global $wpdb;
			$query = "SELECT table_status FROM ".XSDATATABLES_TABLE." WHERE table_id = %d";
			$result = $wpdb->get_var($wpdb->prepare($query, $table_id));
			if($result == 'active'){
				return true;
			}else{
				return false;
			}
		}
		public static function column(){
			global $wpdb;
			$query = "SELECT * FROM information_schema.columns WHERE table_schema = %s AND table_name = %s";
			$result = $wpdb->get_results($wpdb->prepare($query, DB_NAME, XSDATATABLES_TABLE));
			$column_name = array();
			foreach($result as $row){
				if(!in_array($row->COLUMN_NAME, array('table_id', 'table_status', 'table_author'))){
					$column_name[] = $row->COLUMN_NAME;
				}
			}
			return $column_name;
		}
		public static function update($table_id, $column, $value){
			global $wpdb;
			$data = array(
				$column => $value
			);
			$wpdb->update(XSDATATABLES_TABLE, $data, array('table_id' => $table_id));
		}
	}
	new xsdatatables_table();
}

if ( ! class_exists( 'xsdatatables_column' ) ) {
	class xsdatatables_column {
		public static function update($table_id){
			global $wpdb;
			$query = "SELECT * FROM ".XSDATATABLES_COLUMN." WHERE table_id = %d ORDER BY column_order ASC";
			$result = $wpdb->get_results($wpdb->prepare($query, $table_id));
			$output = array();
			foreach($result as $row){
				$output[] = $row->id;
			}
			for($count = 0;  $count < count($output); $count++){
				$data = array(
					'column_order'	=>	$count + 1
				);
				$wpdb->update(XSDATATABLES_COLUMN, $data, array('id' => $output[$count]));
			}
		}
		public static function order_id($table_id){
			global $wpdb;
			$query = "SELECT * FROM ".XSDATATABLES_COLUMN." WHERE table_id = %d";
			$result = $wpdb->get_results($wpdb->prepare($query, $table_id));
			$output = array();
			foreach($result as $row){
				$output[$row->column_order] = $row->id;
			}
			return $output;
		}
		public static function id_name($table_id){
			global $wpdb;
			$query = "SELECT * FROM ".XSDATATABLES_COLUMN." WHERE table_id = %d";
			$result = $wpdb->get_results($wpdb->prepare($query, $table_id));
			$output = array();
			if(!empty($result)){
				foreach($result as $row){
					$output[$row->id] = $row->column_name;
				}
			}
			return $output;
		}
		public static function order_name($table_id){
			global $wpdb;
			$query = "SELECT * FROM ".XSDATATABLES_COLUMN." WHERE table_id = %d";
			$result = $wpdb->get_results($wpdb->prepare($query, $table_id));
			$output = array();
			foreach($result as $row){
				$output[$row->column_order] = $row->column_name;
			}
			return $output;
		}
		public static function order_name_type($table_id){
			global $wpdb;
			$query = "SELECT * FROM ".XSDATATABLES_COLUMN." WHERE table_id = %d ORDER BY column_order ASC";
			$result = $wpdb->get_results($wpdb->prepare($query, $table_id));
			$output = array();
			$output1 = array('column_id');
			$output2 = array();
			$output3 = array();
			$output4 = array();
			$output5 = array();
			$i = 0;
			$j = 0;
			foreach($result as $row){
				$i = $i + 1;
				$output1[$i] = $row->column_name;
				$output2[$i] = $row->column_type;
				if($row->column_status == 'active'){
					$j = $j + 1;
					$output3[$j] = $row->column_name;
					$output4[$j] = $row->column_order;
					$output5[$j] = $row->column_type;
				}
			}
			$output['name'] = $output1;
			$output['type'] = $output2;
			$output['names'] = $output3;
			$output['order'] = $output4;
			$output['types'] = $output5;
			return $output;
		}
		public static function status($table_id, $id){
			global $wpdb;
			$query = "SELECT column_status FROM ".XSDATATABLES_COLUMN." WHERE table_id = %d AND id = %d";
			$result = $wpdb->get_var($wpdb->prepare($query, $table_id, $id));
			if($result == 'active'){
				return true;
			}else{
				return false;
			}
		}
		public static function name($table_id){
			global $wpdb;
			$query = "SELECT * FROM ".XSDATATABLES_COLUMN." WHERE table_id = %d ORDER BY column_order ASC";
			$result = $wpdb->get_results($wpdb->prepare($query, $table_id));
			$output = array();
			if(!empty($result)){
				foreach($result as $row){
					$output[$row->column_name] = $row->column_names;
				}
			}
			return $output;
		}
		public static function rowCount($table_id, $status = null){
			global $wpdb;
			$query = "SELECT * FROM ".XSDATATABLES_COLUMN." WHERE table_id = %d";
			if(!empty($status)){
				$query .= " AND column_status = 'active'";
			}
			$wpdb->get_results($wpdb->prepare($query, $table_id));
			$rowCount = $wpdb->num_rows;
			return $rowCount;
		}
		public static function data($table_id, $status = null){
			global $wpdb;
			$query = "SELECT * FROM ".XSDATATABLES_COLUMN." WHERE table_id = %d";
			if(!empty($status)){
				$query .= " AND column_status = 'active'";
			}
			$query .= " ORDER BY column_order ASC";
			$result = $wpdb->get_results($wpdb->prepare($query, $table_id));
			$rowCount = $wpdb->num_rows;
			$output = array();
			for($i = 1; $i <= 20; $i++){
				$variable = 'output'.$i;
				$$variable = array();
			}
			$output15 = 'null,';
			$j = 0;
			foreach($result as $row){
				$output[] = $row->column_order;
				if($row->column_filters == 'yes'){
					$output1[] = $row->column_order;
				}
				if($row->column_hidden == 'yes'){
					$output2[] = $row->column_order;
				}else{
					$output13[] = $row->column_order;
				}
				if($row->column_orderable == 'no'){
					$output3[] = $row->column_order;
				}
				if($row->column_searchable == 'no'){
					$output4[] = $row->column_order;
				}
				if($row->column_filters == 'yes' && $row->column_position == 'default'){
					$output5[] = $row->column_order;
				}
				if($row->column_filters == 'yes' && $row->column_position == 'footer'){
					$output6[] = $row->column_order;
				}
				if($row->column_total == 'yes'){
					$output7[] = $row->column_order;
				}
				if($row->column_filters == 'yes' && $row->column_filter == 'yes' && !empty($row->column_customfilter)){
					$output9[$row->column_order] = $row->column_customfilter;
				}
				if(isset($row->column_align)){
					if($row->column_align == 'left'){
						$output10[] = $row->column_order;
					}elseif($row->column_align == 'center'){
						$output11[] = $row->column_order;
					}elseif($row->column_align == 'right'){
						$output12[] = $row->column_order;
					}
				}
				if($row->column_status == 'active'){
					$output14[] = $row->column_names;
				}
				if($row->column_status == 'active'){
					if($row->column_width > 0){
						$output15 .= '{ "width": "'.$row->column_width.'%" },';
					}else{
						$output15 .= 'null,';
					}
				}
				if($row->column_background == 'custom'){
					$output16[] = $row->column_order;
				}
				if($row->column_background == 'custom'){
					$output17[$row->column_color] = $row->column_order;
				}
				if($row->column_type == 'select' && !empty($row->column_customtype)){
					$output18[$row->column_order] = $row->column_customtype;
				}
				if($row->column_type == 'index'){
					$output19[] = $row->column_order;
				}
				$j = $j + 1;
				$output20[$j] = $row->column_name;
			}
			$outputs = array();
			for($i = 1; $i <= 20; $i++){
				$variables = 'outputs'.$i;
				$$variables = array();
			}
			$outputs2 = array(0);
			foreach($output as $key => $value){
				foreach($output1 as $value1){
					if($value == $value1){
						$outputs1[] = $key + 1;
					}
				}
				foreach($output2 as $value2){
					if($value == $value2){
						$outputs2[] = $key + 1;
						$outputs8[] = $key + 1;
					}
				}
				foreach($output3 as $value3){
					if($value == $value3){
						$outputs3[] = $key + 1;
					}
				}
				foreach($output4 as $value4){
					if($value == $value4){
						$outputs4[] = $key + 1;
					}
				}
				foreach($output5 as $value5){
					if($value == $value5){
						$outputs5[] = $key + 1;
					}
				}
				foreach($output6 as $value6){
					if($value == $value6){
						$outputs6[] = $key + 1;
					}
				}
				foreach($output7 as $value7){
					if($value == $value7){
						$outputs7[] = $key + 1;
					}
				}
				foreach($output9 as $key9 => $value9){
					if($value == $key9){
						if(!empty($value9)){
							$select = '';
							foreach(explode(";", $value9) as $rows){
								$select .= '<option value="'.$rows.'">'.$rows.'</option>';
							}
							$outputs9[$key + 1] = $select;
						}
					}
				}
				foreach($output10 as $value10){
					if($value == $value10){
						$outputs10[] = $key + 1;
					}
				}
				foreach($output11 as $value11){
					if($value == $value11){
						$outputs11[] = $key + 1;
					}
				}
				foreach($output12 as $value12){
					if($value == $value12){
						$outputs12[] = $key + 1;
					}
				}
				foreach($output13 as $value13){
					if($value == $value13){
						$outputs13[] = $key + 1;
					}
				}
				foreach($output16 as $value16){
					if($value == $value16){
						$outputs16[] = $key + 1;
					}
				}
				foreach($output17 as $key17 => $value17){
					if($value == $value17){
						$outputs17[$key + 1] = $key17;
					}
				}
				foreach($output18 as $key18 => $value18){
					if($value == $key18){
						if(!empty($value18)){
							$select = '';
							foreach(explode(";", $value18) as $rows){
								$select .= "<option value=$rows >$rows</option>";
							}
							$outputs18[$key + 1] = $select;
						}
					}
				}
				foreach($output19 as $value19){
					if($value == $value19){
						$outputs19[] = $key + 1;
					}
				}
			}
			$outputs['column_filters'] = json_encode($outputs1);
			$outputs['column_hidden'] = json_encode($outputs2);
			$outputs['column_show'] = json_encode($outputs13);
			$outputs['column_hiddens'] = json_encode($outputs8);
			$outputs['column_orderable'] = json_encode($outputs3);
			$outputs['column_searchable'] = json_encode($outputs4);
			$outputs['column_default'] = json_encode($outputs5);
			$outputs['column_footer'] = json_encode($outputs6);
			$outputs['column_total'] = $outputs7;
			$outputs['column_customfilter'] = $outputs9;
			$outputs['column_left'] = json_encode($outputs10);
			$outputs['column_center'] = json_encode($outputs11);
			$outputs['column_right'] = json_encode($outputs12);
			$outputs['column_count'] = json_encode($rowCount);
			$outputs['column_names'] = $output14;
			$outputs['column_width'] = $output15;
			$outputs['column_color'] = $outputs16;
			$outputs['column_colors'] = $outputs17;
			$outputs['column_customtype'] = $outputs18;
			$outputs['column_index'] = $outputs19;
			$outputs['column_order'] = $output20;
			return $outputs;
		}
		public static function align($table_id){
			global $wpdb;
			$query = "SELECT * FROM ".XSDATATABLES_COLUMN." WHERE table_id = %d AND column_status = %s";
			$result = $wpdb->get_results($wpdb->prepare($query, $table_id, 'active'));
			$output = array('column_id');
			$outputs = array();
			foreach($result as $row){
				$output[] = $row->column_order;
			}
			$output1 = array(0, count($output));
			$output2 = array(0, count($output), count($output) + 1, count($output) + 2);
			$outputs['status'] = json_encode($output1);
			$outputs['all'] = json_encode($output2);
			$outputs['count'] = count($output) - 1;
			return $outputs;
		}
		public static function column(){
			global $wpdb;
			$query = "SELECT * FROM information_schema.columns WHERE table_schema = %s AND table_name = %s";
			$result = $wpdb->get_results($wpdb->prepare($query, DB_NAME, XSDATATABLES_COLUMN));
			$column_name = array();
			if(!empty($result)){
				foreach($result as $row){
					if(!in_array($row->COLUMN_NAME, array('id', 'table_id', 'column_status'))){
						$column_name[] = $row->COLUMN_NAME;
					}
				}
			}
			return $column_name;
		}
		public static function names($table_id){
			global $wpdb;
			$query = "SELECT column_name, column_names FROM ".XSDATATABLES_COLUMN." WHERE table_id = %d";
			$result = $wpdb->get_results($wpdb->prepare($query, $table_id));
			$select = '';
			foreach($result as $column){
				$select .= "<option value={$column->column_name} >{$column->column_names}</option>";
			}
			return $select;
		}
		public static function import($table_id){
			$output = array();
			$output1 = array();
			$output2 = '';
			$output3 = '';
			foreach(self::name($table_id) as $name => $names){
				$output1[] = $name;
				$output2 .= $name.':'.$name.',';
				$output3 .= $name.',';
			}
			$output['name'] = $output1;
			$output['post'] = rtrim($output2, ",");
			$output['query'] = rtrim($output3, ",");
			return $output;
		}
		public static function import_replace($total){
			$output = array();
			for($x = 0; $x <= $total - 1; $x++){
				$output[$x] = $x;
			}
			return $output;
		}
		public static function import_post($total){
			$output = 'column_0:column_0';
			for($x = 1; $x <= $total - 1; $x++){
				$output .= ', column_'.$x.':column_'.$x;
			}
			return $output;
		}
		public static function all_column($table){
			global $wpdb;
			$output = array();
			$columns = array();
			$rows = array();
			$results = $wpdb->get_results( "DESC `{$table}`" );
			foreach ( $results as $column ) {
				$columns[] = $column->Field;
			}
			$query = "SELECT * FROM $table";
			$result = $wpdb->get_results($query);
			foreach ( $result as $row ) {
				$rows[] = array_values((array)$row);
			}
			$output['column'] = $columns;
			$output['row'] = (array)$rows;
			return $output;
		}
		public static function all_columns($table, $table_id, $xs_type){
			$order_name = xsdatatables_column::order_name($table_id);
			$name = xsdatatables_column::name($table_id);
			$order_id = xsdatatables_column::order_id($table_id);
			$category_id = xsdatatables_table::category($table_id);
			$output = array();
			$data = array();
			$column = array();
			$column_total = xsdatatables_column::rowCount($table_id);
			$limit = xsdatatables_table::limit($table_id);
			if(in_array($xs_type, array('post', 'page'))){
				$array = xsdatatables_post::post($xs_type, $category_id, $limit);
			}elseif($xs_type == 'product'){
				$array = xsdatatables_woocommerce::woocommerce($category_id, $limit);
			}elseif($xs_type == 'order'){
				$array = xsdatatables_woocommerce::order($category_id, $limit);
			}
			foreach($array as $row){
				$row = array_values($row);
				if(xsdatatables_row::status($table, $row[0])){
					$sub_array = array();
					$column = array();
					for ($x = 1; $x <= $column_total; $x++){
						if(xsdatatables_column::status($table_id, $order_id[$x])){
							$value = str_replace('column_', '', $order_name[$x]);
							if(isset($row[$value])){
								$sub_array[] .= $row[$value];
							}else{
								$sub_array[] .= '';
							}
							$column[] = $name[$order_name[$x]];
						}
					}
					$data[] = $sub_array;
				}
			}
			$output['column'] = array_values($column);
			$output['row'] = (array)$data;
			return $output;
		}
		public static function data_export($table_id, $xs_type){
			global $wpdb;
			$table = XSDATATABLES_PREFIX.$table_id;
			$order_name = xsdatatables_column::order_name($table_id);
			$name = xsdatatables_column::name($table_id);
			$order_id = xsdatatables_column::order_id($table_id);
			$category_id = xsdatatables_table::category($table_id);
			$data = 'column_1';
			$column_total = xsdatatables_column::rowCount($table_id);
			for ($x = 2; $x <= $column_total; $x++){
				$data .= ',column_'.$x;
			}
			$data .= "\n";
			$limit = xsdatatables_table::limit($table_id);
			if(in_array($xs_type, array('post', 'page', 'product'))){
				if(in_array($xs_type, array('post', 'page'))){
					$array = xsdatatables_post::post($xs_type, $category_id, $limit);
				}elseif($xs_type == 'product'){
					$array = xsdatatables_woocommerce::woocommerce($category_id, $limit);
				}
				foreach($array as $row){
					$row = array_values($row);
					if(xsdatatables_row::status($table, $row[0])){
						$sub_array = array();
						for ($x = 1; $x <= $column_total; $x++){
							if(xsdatatables_column::status($table_id, $order_id[$x])){
								$value = str_replace('column_', '', $order_name[$x]);
								if(isset($row[$value])){
									$sub_array[] .= self::csv_wrap( $row[$value], ',' );
								}else{
									$sub_array[] .= '';
								}
							}
						}
						$data .= implode( ',', $sub_array )."\n";
					}
				}
			}elseif($xs_type == 'default'){
				$query = "SELECT * FROM `{$table}` ORDER BY column_id ASC";
				if($limit >= 0){
					$query .= " LIMIT {$limit}";
				}
				$result = $wpdb->get_results($query);
				foreach($result as $row){
					$row = (array)$row;
					$sub_array = array();
					for ($x = 1; $x <= count($row) - 2; $x++){
						if(isset($order_name[$x])){
							$column_name = $order_name[$x];
							$value = str_replace('”', '"', $row[$column_name]);
							$sub_array[] .= self::csv_wrap( $value, ',' );
						}
					}
					$data .= implode( ',', $sub_array )."\n";
				}
			}
			return $data;
		}
		public static function csv_wrap( $string, $delimiter ) {
			$delimiter = preg_quote( $delimiter, '#' );
			if ( 1 === preg_match( '#' . $delimiter . '|"|\n|\r#i', $string ) || ' ' === substr( $string, 0, 1 ) || ' ' === substr( $string, -1 ) ) {
				$string = str_replace( '"', '""', $string );
				$string = '"' . $string . '"';
			}
			return $string;
		}
		public static function type($table_id, $column_name){
			global $wpdb;
			$query = "SELECT column_type FROM ".XSDATATABLES_COLUMN." WHERE table_id = %d AND column_name = %s";
			$output = $wpdb->get_var($wpdb->prepare($query, $table_id, $column_name));
			return $output;
		}
		public static function column_filter($table_id, $column_name){
			global $wpdb;
			$query = "SELECT column_filter FROM ".XSDATATABLES_COLUMN." WHERE table_id = %d AND column_name = %s";
			$output = $wpdb->get_var($wpdb->prepare($query, $table_id, $column_name));
			return $output;
		}
		public static function custom_filter($table_id, $column_id){
			global $wpdb;
			$output = array();
			$table = XSDATATABLES_PREFIX.$table_id;
			$column_name = self::id_name($table_id)[$column_id];
			$query = "SELECT $column_name FROM `{$table}`";
			$result = $wpdb->get_results($query);
			if(!empty($result)){
				$i = 0;
				foreach($result as $column){
					$i = $i + 1;
					if($i <= 20){
						$output[] = $column->$column_name;
					}
				}
			}
			return array_unique($output);
		}
	}
	new xsdatatables_column();
}

if ( ! class_exists( 'xsdatatables_row' ) ) {
	class xsdatatables_row {
		public static function pagelength($table_id, $table_length){
			$pagelength = array(10, 25, 50, 100);
			if($table_length > 0 && !in_array($table_length, $pagelength)){
				array_push($pagelength, $table_length);
			}
			sort($pagelength);
			$output = '['. json_encode($pagelength). ', ' . json_encode($pagelength) .']';
			return $output;
		}
		public static function update($table, $column_id){
			global $wpdb;
			$query = "SELECT * FROM `{$table}` WHERE column_id = %d";
			$result = $wpdb->get_results($wpdb->prepare($query, $column_id));
			$data = array();
			if(!empty($result)){
				foreach($result as $row){
					foreach((array)$row as $keys => $rows){
						if(str_replace('column_', '', $keys) != $keys && $keys != 'column_id' && $keys != 'column_status'){
							$data[$keys] = $rows;
						}
					}
				}
			}
			return $data;
		}
		public static function column_name($column){
			global $wpdb;
			$database = DB_NAME;
			$query = "SELECT * FROM information_schema.columns WHERE table_schema = %s AND table_name = %s";
			$result = $wpdb->get_results($wpdb->prepare($query, DB_NAME, $column));
			$column_name = array();
			foreach($result as $row){
				if(!in_array($row->COLUMN_NAME, array('column_id', 'column_status'))){
					$column_name[] = str_replace('column_', '', $row->COLUMN_NAME);
				}
			}
			if(empty($column_name)){
				return 0;
			}
			return max($column_name);
		}
		public static function check_id($table, $column_id){
			global $wpdb;
			$query = "SELECT column_id FROM `{$table}` WHERE column_id = %d";
			$result = $wpdb->get_var($wpdb->prepare($query, $column_id));
			if(!empty($result)){
				return true;
			}else{
				return false;
			}
		}
		public static function status($table, $column_id){
			global $wpdb;
			$query = "SELECT column_status FROM `{$table}` WHERE column_id = %d";
			$result = $wpdb->get_var($wpdb->prepare($query, $column_id));
			if(empty($result) || $result == 'active'){
				return true;
			}else{
				return false;
			}
		}
		public static function rowCount($table_id, $status = null){
			global $wpdb;
			$rowCount = 0;
			$table = XSDATATABLES_PREFIX.$table_id;
			$query = "SELECT * FROM `{$table}`";
			if(!empty($status)){
				$query .= " WHERE column_status = 'active'";
			}
			$result = $wpdb->get_results($query);
			if($result){
				$rowCount = $wpdb->num_rows;
			}
			return $rowCount;
		}
		public static function column($table_id){
			global $wpdb;
			$database = DB_NAME;
			$table = XSDATATABLES_PREFIX.$table_id;
			$query = "SELECT * FROM information_schema.columns WHERE table_schema = %s AND table_name = %s";
			$result = $wpdb->get_results($wpdb->prepare($query, DB_NAME, $table));
			$column_name = array();
			foreach($result as $row){
				if(!in_array($row->COLUMN_NAME, array('column_id', 'column_status'))){
					$column_name[] = $row->COLUMN_NAME;
				}
			}
			return $column_name;
		}
	}
	new xsdatatables_row();
}

if ( ! class_exists( 'xsdatatables_woocommerce' ) ) {
	class xsdatatables_woocommerce {
		public static function woocommerce($category_id = array(), $limit = 1000){
			$output = array();
			if(!function_exists('wc_get_products')){
				return $output;
			}
			if($limit == 0) return $output;
			$product_ids = self::product_id($category_id);
			$products = wc_get_products(array('status' => 'publish', 'limit' => $limit));
			foreach ($products as $product){ 
				$product_id = $product->get_id();
				if(in_array($product_id, $product_ids)){
					$output[] = array(
						'id' => $product_id,
						'title' => '<a href="'.get_permalink($product_id).'" target="_self">'.$product->get_title().'</a>',
						'image' => '<a href="'.get_permalink($product_id).'" target="_self"><img src="'.wp_get_attachment_image_url($product->get_image_id(), 'full').'" class="img-thumbnail" width="70" /></a>',
						'categories' => wc_get_product_category_list($product_id),
						'short_description' => $product->get_short_description(),
						'price' => $product->get_price_html(),
						'rating' => '<div class="star-rating">'.wc_get_rating_html($product->get_average_rating(), $product->get_rating_count()).'</div>',
						'buy' => do_shortcode('[add_to_cart id="'.$product_id.'" show_price="false" style = "margin-bottom: 0!important;"]'),
						'type' => $product->get_type(),
						'sku' => $product->get_sku(),
						'tags' => wc_get_product_tag_list($product_id, ', '),
						'stock_status' => $product->get_stock_status(),
						'stock_quantity' => $product->get_stock_quantity(),
					);
				}
			}
			return $output;
		}
		public static function product_id($category_id){
			$product_id = array();
			if(empty($category_id)){
				$products = wc_get_products(array('status' => 'publish', 'limit' => -1));
				foreach ($products as $product){ 
					$product_id[] = $product->get_id();
				}
			}else{
				$all_ids = get_posts(array(
					'post_type' => 'product',
					'numberposts' => -1,
					'post_status' => 'publish',
					'fields' => 'ids',
					'tax_query' => array(
						array(
							'taxonomy' => 'product_cat',
							'field' => 'term_id',
							'terms' => $category_id,
							'operator' => 'IN',
						)
					),
				));
				foreach ( $all_ids as $id ) {
					$product_id[] = $id;
				}
			}
			return $product_id;
		}
		public static function category(){
			$category = array();
			$woocommerce_category_id = get_queried_object_id();
			$args = array(
				'parent' => $woocommerce_category_id
			);
			$terms = get_terms( 'product_cat', $args );
			if ( $terms ) {
				foreach ( $terms as $term ) {
					$category[] = $term->name;
				}
			}
			return $category;
		}
		public static function categories($table_id){
			$category_id = xsdatatables_table::category($table_id);
			$categories = '';
			$args = array(
				'taxonomy'     => 'product_cat',
				'orderby'      => 'name',
				'show_count'   => true,
				'pad_counts'   => false,
				'hierarchical' => true,
				'title_li'     => '',
				'hide_empty'   => true
			);
			if(!empty(get_categories( $args ))){
				foreach(get_categories( $args ) as $row){
					if($row->category_parent == 0) {
						$name = $row->name;
						if(empty($category_id)){
							$categories .= "<option value=$name >$name</option>";
						}elseif(in_array($row->term_id, $category_id)){
							$categories .= "<option value=$name >$name</option>";
						}
					}
				}
			}
			return $categories;
		}
		public static function all_categories(){
			$categories = '';
			$args = array(
				'taxonomy'     => 'product_cat',
				'orderby'      => 'name',
				'show_count'   => true,
				'pad_counts'   => false,
				'hierarchical' => true,
				'title_li'     => '',
				'hide_empty'   => true
			);
			if(!empty(get_categories( $args ))){
				foreach(get_categories( $args ) as $row){
					if($row->category_parent == 0) {
						$name = $row->name;
						$id = $row->term_id;
						$categories .= "<option value=$id >$name</option>";
					}
				}
			}
			return $categories;
		}
		public static function product_type(){
			$output = '';
			foreach (self::woocommerce(array(), 10) as $value){ 
				$i = -1;
				$output = '<option value="">Select</option>';
				foreach ($value as $keys => $values){ 
					$i= $i + 1;
					$output .= '<option value="'.'column_'.$i.'" >'.$keys.'</option>';
				}
			}
			return $output;
		}
		public static function product_types(){
			$output = array();
			foreach (self::woocommerce(array(), 10) as $value){ 
				$i = -1;
				foreach ($value as $keys => $values){ 
					$i= $i + 1;
					$output['column_'.$i] = $keys;
				}
			}
			return $output;
		}
		public static function download_products($product_id){
			$downloads = array();
			$user_id = get_current_user_id();
			$downloads = wc_get_customer_available_downloads($user_id);
			$output = '';
			if (!empty($downloads)) {
				foreach ($downloads as $download) {
					if($download['product_id'] == $product_id){
						$output = '<a class="button" href="'.$download['download_url'].'">Download</a>';
					}
				}
			}
			return $output;
		}
		public static function order($category_id = array(), $limit = 1000){
			$output = array();
			if(!function_exists('wc_get_products')){
				return $output;
			}
			if($limit == 0) return $output;
			$item_sales = get_posts(array(
				'numberposts' => $limit,
				'post_type'   => 'shop_order',
				'post_status' => array('wc-completed', 'wc-on-hold', 'wc-pending', 'wc-failed', 'wc-cancelled', 'wc-refunded'),
			));
			if($item_sales) {
				$product_ids = self::product_id($category_id);
				foreach( $item_sales as $sale ) {
					$order = wc_get_order( $sale->ID );
					$data = $order->get_data();
					foreach( $order->get_items() as $item ){
					   $product_id = $item->get_product_id();
					   $product_name = $item->get_name();
					}
					if(in_array($product_id, $product_ids)){
						if(isset($data['date_completed'])){
							$date_completed = $data['date_completed']->date('Y-m-d H:i:s');
						}else{
							$date_completed = '';
						}
						if(isset($data['date_paid'])){
							$date_paid = $data['date_paid']->date('Y-m-d H:i:s');
						}else{
							$date_paid = '';
						}
						$billing_phone = !empty($data['billing']['phone']) ? ' ('.$data['billing']['phone'].')' : '';
						$billing_first_name = !empty($data['billing']['first_name']) ? $data['billing']['first_name'] : '';
						$billing_last_name = !empty($data['billing']['last_name']) ? ' '.$data['billing']['last_name'] : '';
						$billing_address_1 = !empty($data['billing']['address_1']) ? ', '.$data['billing']['address_1'] : '';
						$billing_city = !empty($data['billing']['city']) ? ', '.$data['billing']['city'] : '';
						$billing_country = !empty($data['billing']['country']) ? ', '.$data['billing']['country'] : '';
						$shipping_phone = !empty($data['shipping']['phone']) ? ' ('.$data['shipping']['phone'].')' : '';
						$shipping_first_name = !empty($data['shipping']['first_name']) ? $data['shipping']['first_name'] : '';
						$shipping_last_name = !empty($data['shipping']['last_name']) ? ' '.$data['shipping']['last_name'] : '';
						$shipping_address_1 = !empty($data['shipping']['address_1']) ? ', '.$data['shipping']['address_1'] : '';
						$shipping_city = !empty($data['shipping']['city']) ? ', '.$data['shipping']['city'] : '';
						$shipping_country = !empty($data['shipping']['country']) ? ', '.$data['shipping']['country'] : '';
						$output[] = array(
							'id' => $data['id'],
							'order' => '<a href="'.admin_url( 'post.php' ).'?post='.$data['id'].'&action=edit" target="_blank">'.$data['id'].'</a>',
							'date_created' => $data['date_created']->date('Y-m-d'),
							'status' => $data['status'],
							'product_name' => '<a href="'.get_permalink($product_id).'" target="_self">'.$product_name.'</a>',
							'total' => $data['total'],
							'payment_method_title' => $data['payment_method_title'],
							'billing.email' => $data['billing']['email'],
							'billing' => $billing_first_name.$billing_last_name.$billing_phone.$billing_address_1.$billing_city.$billing_country,
							'shipping' => $shipping_first_name.$shipping_last_name.$shipping_phone.$shipping_address_1.$shipping_city.$shipping_country,
							'customer_note' => $data['customer_note'],
							'customer_id' => '<a href="'.get_admin_url().'user-edit.php?user_id='.$data['customer_id'].'" target="_blank">'.$data['customer_id'].'</a>',
							'currency' => $data['currency'],
							'discount_total' => $data['discount_total'],
							'discount_tax' => $data['discount_tax'],
							'shipping_total' => $data['shipping_total'],
							'shipping_tax' => $data['shipping_tax'],
							'cart_tax' => $data['cart_tax'],
							'total_tax' => $data['total_tax'],
							'date_modified' => $data['date_modified']->date('Y-m-d'),
							'billing.first_name' => $data['billing']['first_name'],
							'billing.last_name' => $data['billing']['last_name'],
							'billing.company' => $data['billing']['company'],
							'billing.address_1' => $data['billing']['address_1'],
							'billing.address_2' => $data['billing']['address_2'],
							'billing.city' => $data['billing']['city'],
							'billing.state' => $data['billing']['state'],
							'billing.postcode' => $data['billing']['postcode'],
							'billing.country' => $data['billing']['country'],
							'billing.phone' => $data['billing']['phone'],
							'shipping.first_name' => $data['shipping']['first_name'],
							'shipping.last_name' => $data['shipping']['last_name'],
							'shipping.company' => $data['shipping']['company'],
							'shipping.address_1' => $data['shipping']['address_1'],
							'shipping.address_2' => $data['shipping']['address_2'],
							'shipping.city' => $data['shipping']['city'],
							'shipping.state' => $data['shipping']['state'],
							'shipping.postcode' => $data['shipping']['postcode'],
							'shipping.country' => $data['shipping']['country'],
							'shipping.phone' => $data['shipping']['phone'],
							'date_completed' => $date_completed,
							'date_paid' => $date_paid,
							'product_id' => $product_id,
							'order_subscriptions' => self::subscriptions_for_order($data['id']),
						);
					}
				}
			}
			return $output;
		}
		public static function subscriptions_for_order($order_id){
			if(!function_exists('wcs_get_subscriptions_for_order')) return '';
			foreach (wcs_get_subscriptions_for_order($order_id) as $subscription_id => $subscription_obj){
				return $subscription_id;
			}
		}
		public static function order_type(){
			$output = '';
			foreach (self::order(array(), 10) as $value){ 
				$i = -1;
				$output = '<option value="">Select</option>';
				foreach ($value as $keys => $values){ 
					$i= $i + 1;
					$output .= '<option value="'.'column_'.$i.'" >'.$keys.'</option>';
				}
			}
			return $output;
		}
		public static function order_types(){
			$output = array();
			foreach (self::order(array(), 10) as $value){ 
				$i = -1;
				foreach ($value as $keys => $values){ 
					$i= $i + 1;
					$output['column_'.$i] = $keys;
				}
			}
			return $output;
		}
	}
	new xsdatatables_woocommerce();
}

if ( ! class_exists( 'xsdatatables_post' ) ) {
	class xsdatatables_post {
		public static function category($id){
			$category = '';
			if(!empty(get_the_category($id))){
				foreach(get_the_category($id) as $row){
					$category .= '<a href="'.esc_url( get_category_link($row->term_id)).'">'.esc_html( $row->name ).'</a>'.', ';
				}
			}
			return rtrim($category, ", ");
		}
		public static function categories($table_id){
			$category_id = xsdatatables_table::category($table_id);
			$categories = '';
			if(!empty(get_categories())){
				foreach(get_categories() as $row){
					$name = $row->name;
					if(empty($category_id)){
						$categories .= "<option value=$name >$name</option>";
					}elseif(in_array($row->term_id, $category_id)){
						$categories .= "<option value=$name >$name</option>";
					}
				}
			}
			return $categories;
		}
		public static function tags($id){
			$tags = '';
			if(!empty(get_the_tags($id))){
				foreach(get_the_tags($id) as $row){
					$tags .= '<a href="'.esc_url( get_tag_link($row->term_id)).'">'.esc_html( $row->name ).'</a>'.', ';
				}
			}
			return rtrim($tags, ", ");
		}
		public static function author($author){
			$user_info = get_userdata($author);
			$user_name = $user_info->display_name;
			return $user_name;
		}
		public static function post($type, $category_id = 0, $limit = 1000){
			if(empty($category_id)){
				$category_id = 0;
			}
			$output = array();
			if($limit == 0) return $output;
			if($type == 'post'){
				$posts = get_posts(array('category'  => $category_id, 'numberposts' => $limit));
			}elseif($type == 'page'){
				if($limit < 0) $limit = 0;
				$posts = get_pages(array('number' => $limit));
			}
			foreach($posts as $row){
				if($row->post_status == 'publish'){
					$output[] = array(
						"post_id" => $row->ID,
						"post_title" => '<a href="'.get_permalink($row->ID).'" target="_self">'.$row->post_title.'</a>',
						"post_thumbnail" => '<img src="'.get_the_post_thumbnail_url($row->ID).'" class="img-thumbnail" width="100" />',
						"category" => self::category($row->ID),
						"post_tags" => self::tags($row->ID),
						"post_date" => date("Y-m-d", strtotime($row->post_date)),
						"post_author" => self::author($row->post_author),
						"post_url" => get_permalink($row->ID),
						"post_type" => $row->post_type,
						"post_modified" => date("Y-m-d", strtotime($row->post_modified)),
						"post_name" => $row->post_name,
						"post_guid" => $row->guid,
						"comment_status" => $row->comment_status,
						"comment_count" => $row->comment_count
					);
				}
			}
			return $output;
		}
		public static function poss($type){
			$output = '<option value="">Select</option>';
			foreach (self::post($type, 0, 10) as $value){ 
				$i = -1;
				$output = '<option value="">Select</option>';
				foreach ($value as $keys => $values){ 
					$i= $i + 1;
					$output .= '<option value="'.'column_'.$i.'" >'.$keys.'</option>';
				}
			}
			return $output;
		}
		public static function type($type){
			$output = array();
			foreach (self::post($type, 0, 10) as $value){
				$i = -1;
				foreach ($value as $keys => $values){
					$i= $i + 1;
					$output['column_'.$i] = $keys;
				}
			}
			return $output;
		}
	}
	new xsdatatables_post();
}

if ( ! class_exists( 'xsdatatables_import' ) ) {
	class xsdatatables_import {
		public static function import($table_new){
			global $wpdb;
			$wpdb->query("DROP TABLE IF EXISTS `{$table_new}`");
			$charset_collate = $wpdb->get_charset_collate();
			$sql = "CREATE TABLE IF NOT EXISTS $table_new (
				column_id bigint(20) NOT NULL AUTO_INCREMENT,
				column_status enum('active','inactive') NOT NULL,
				PRIMARY KEY  (column_id)
			) $charset_collate;";
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );
		}
		public static function column_name($table, $table_id){
			global $wpdb;
			$query = "SELECT * FROM `{$table}` WHERE table_id = %d ORDER BY column_order ASC";
			$result = $wpdb->get_results($wpdb->prepare($query, $table_id));
			$output = '';
			if(!empty($result)){
				foreach($result as $row){
					$output .= $row->column_name.', ';
				}
			}
			return substr($output, 0, -2);
		}
		public static function table(){
			global $wpdb;
			$output = '';
			$query = "SELECT table_name FROM information_schema.tables WHERE table_schema = %s ORDER BY table_name ASC";
			$result = $wpdb->get_results($wpdb->prepare($query, DB_NAME));
			if($result){
				foreach($result as $column){
					$output .= '<option value="'.$column->table_name.'" >'.$column->table_name.'</option>';
				}
			}
			return $output;
		}
	}
	new xsdatatables_import();
}
?>