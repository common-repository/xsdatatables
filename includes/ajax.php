<?php

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'xsdatatables_ajax' ) ) {
	class xsdatatables_ajax {
		public function __construct(){
			if( is_admin() && current_user_can( 'manage_options' ) ) {
				$actions = array(
					'_getdata_xs',
					'_getdatas_xs',
					'_multi_active_xs',
					'_multi_inactive_xs',
					'_multi_duplicate_xs',
					'_multi_delete_xs',
					'_single_xs',
					'_edit_status_xs',
					'_update_order_xs',
					'_permission_single_xs',
					'_edit_permission_xs',
					'_add_xs',
					'_edit_xs',
					'_delete_xs',
					'_search_xs'
				);
				$types = array('table', 'row', 'column');
				foreach($types as $type){
					foreach($actions as $action){
						add_action( "wp_ajax_$type$action", array( $this, $type ));
					}
				}
				$imports = array('confirm_xs', 'import_xs', 'process_xs', 'upload_xs');
				foreach($imports as $import){
					add_action( "wp_ajax_$import", array( $this, $import ));
				}
			}
		}

		public function table(){
			global $wpdb;
			include_once 'table_action.php';
			wp_die();
		}

		public function row(){
			global $wpdb;
			if(isset($_POST['xs_type'])){
				if($_POST['xs_type'] == 'default'){
					include_once 'details_action.php';
				}elseif($_POST['xs_type'] == 'product'){
					include_once 'woo_action.php';
				}elseif(in_array($_POST['xs_type'], array('post', 'page'))){
					include_once 'post_action.php';
				}elseif($_POST['xs_type'] == 'order'){
					include_once 'woo_order.php';
				}
			}else{
				include_once 'details_action.php';
			}
			wp_die();
		}

		public function column(){
			global $wpdb;
			include_once 'column_action.php';
			wp_die();
		}

		public function confirm_xs(){
			global $wpdb;
			include_once 'xs_import_action.php';
			wp_die();
		}

		public function import_xs(){
			global $wpdb;
			include_once 'xs_import_action.php';
			wp_die();
		}

		public function process_xs(){
			global $wpdb;
			include_once 'process_action.php';
			wp_die();
		}

		public function upload_xs(){
			global $wpdb;
			include_once 'upload_action.php';
			wp_die();
		}
	}
	new xsdatatables_ajax();
}