<?php
/*
Plugin Name: xsDataTables - DataTables for WordPress
Plugin URI: https://xsdatatables.com/documentation/
Description: You can create tables of products, orders, posts, pages with searchable, filterable, responsive from the admin panel quickly, effectively, and easily without having to write code.
Version: 1.0.3
Author: xsdatatables.com
Author URI: https://xsdatatables.com/
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Text Domain: xsdatatables
Domain Path: /i18n/languages/
*/
defined( 'ABSPATH' ) || exit;
if ( ! defined( 'XSDATATABLES_ASSETS' ) ) define( 'XSDATATABLES_ASSETS', plugins_url( 'assets/', __FILE__ ) );
if ( ! defined( 'XSDATATABLES_INCLUDES' ) ) define( 'XSDATATABLES_INCLUDES', plugins_url( 'includes/', __FILE__ ) );
if ( ! defined( 'XSDATATABLES_VENDOR' ) ) define( 'XSDATATABLES_VENDOR', plugins_url( 'vendor/', __FILE__ ) );
if ( ! defined( 'XSDATATABLES_VERSION' ) ) define( 'XSDATATABLES_VERSION', '1.0.3' );
if ( ! defined( 'XSDATATABLES_SLUG' ) ) define( 'XSDATATABLES_SLUG', basename( plugin_dir_path( __FILE__ ) ) );
if ( ! defined( 'XSDATATABLES_MAIN' ) ) define( 'XSDATATABLES_MAIN', plugin_basename( __FILE__ ) );
ob_start();

if( !function_exists( 'wp_get_current_user' ) ) {
	include_once( ABSPATH. "wp-includes/pluggable.php" ); 
}
$XSDATATABLES_TABLES = array();
include_once 'includes/function.php';
include_once 'includes/ajax.php';
include_once 'includes/xsoption.php';

if ( ! class_exists( 'xsdatatables_main' ) ) {
	class xsdatatables_main {
		public function __construct(){
			global $pagenow;
			include_once 'includes/shortcode.php';
			if( is_admin() && current_user_can( 'manage_options' ) ) {
				register_activation_hook( __FILE__, array( $this, 'activation_hook' ) );
				add_action( 'admin_menu', array( $this, 'admin_menu' ) );
				add_action( 'admin_enqueue_scripts', array( $this, 'xs_enqueue_scripts' ) );
				if($pagenow == 'admin.php' && isset($_GET['page']) && in_array($_GET['page'], array('xsdatatables', 'xsoption'))){
					add_action('in_admin_header', array( $this, 'skip_notice'), 10000);
				}
			}
		}

		public function activation_hook() {
			xsdatatables_init::activation_hook();
		}

		public function admin_menu(){
			add_menu_page( 'xsDataTables', 'xsDataTables', 'manage_options', 'xsdatatables', '', 'dashicons-list-view', 6 );
			add_submenu_page( 'xsdatatables', 'xsDataTables', esc_html__('All Tables', 'xsdatatables'), 'manage_options', 'xsdatatables', array( $this, 'admin_page' ), 1 );
			add_submenu_page( 'xsdatatables', 'xsDocumentation', esc_html__('About', 'xsdatatables'), 'manage_options', 'https://xsdatatables.com/', '', 2 );
		}

		public function admin_page(){
			global $table_id, $tab_id, $_xsnonce, $table_type;
			if(isset($_GET['table_id'])){
				$table_id = (int) sanitize_text_field($_GET['table_id']);
				$_xsnonce = wp_create_nonce('xs-table'.$table_id);
				$table_type = xsdatatables_table::type($table_id);
				if(isset($_GET['tab'])){
					if(xsdatatables_init::check_tables(XSDATATABLES_PREFIX. $table_id)){
						$tab_id = "&table_id=$table_id&_xsnonce=".$_xsnonce;
						echo '<div class="wrap">';
							echo '<div class="nav-tab-xs">';
							$tab = sanitize_text_field($_GET['tab']);
							$this->tab($tab.$tab_id);
							echo '</div>';
						echo '</div>';
						switch ( $tab ) {
							case 'table' :
								echo '<div class="wrap">';
								include_once 'includes/details.php';
								echo '</div>';
							break;
							case 'column' :
								echo '<div class="wrap">';
								include_once 'includes/column.php';
								echo '</div>';
							break;
							case 'import' :
								if($table_type == 'default'){
									echo '<div class="wrap">';
									include_once 'includes/xs_import.php';
									echo '</div>';
								}else{
									wp_redirect('admin.php?page=xsdatatables');
									exit;
								}
							break;
						}
					}else{
						wp_redirect('admin.php?page=xsdatatables');
						exit;
					}
				}else{
					wp_redirect('admin.php?page=xsdatatables');
					exit;
				}
			}else{
				echo '<div class="wrap">';
				include_once 'includes/table.php';
				echo '</div>';
			}
		}

		public function tab( $current = 'table' ) {
			global $tab_id, $table_type;
			if(isset($tab_id)){
				$tabs = array( "table$tab_id" => 'Details', "column$tab_id" => 'Column' );
			}
			if(isset($table_type) && $table_type == 'default'){
				$tabs = array( "table$tab_id" => 'Details', "column$tab_id" => 'Column', "import$tab_id" => 'Import' );
			}
			echo '<h2 class="nav-tab-wrapper">';
			echo "<a class='nav-tab' href='?page=xsdatatables'><span class='dashicons dashicons-admin-home'></span></a>";
			if($tabs){
				foreach( $tabs as $tab => $name ) {
					$class = ( $tab == $current ) ? ' nav-tab-active' : '';
					echo "<a class='nav-tab$class' href='?page=xsdatatables&tab=$tab'>$name</a>";
				}
			}
			echo '</h2>';
		}

		public function xs_enqueue_scripts(){
			global $pagenow;
			if ( $pagenow == 'admin.php' && isset($_GET['page']) && in_array( $_GET['page'], array( 'xsdatatables', 'xsoption' ) ) ) {
				wp_enqueue_style( 'xsbootstrap', XSDATATABLES_VENDOR. 'bootstrap/css/bootstrap.min.css' );
				wp_enqueue_style( 'xsdatatables', XSDATATABLES_VENDOR. 'datatables/datatables.min.css' );
				wp_enqueue_style( 'xsstyle', XSDATATABLES_ASSETS. 'css/style.css' );
				wp_enqueue_style( 'xsadmin', XSDATATABLES_ASSETS. 'css/admin.css' );
				wp_enqueue_script( 'xsbootstrap', XSDATATABLES_VENDOR. 'bootstrap/js/bootstrap.bundle.min.js' );
				wp_enqueue_script( 'xsdatatables', XSDATATABLES_VENDOR. 'datatables/datatables.min.js' );
				wp_enqueue_script( 'xsaudio', XSDATATABLES_ASSETS. 'js/soundmanager2.js' );
			}
		}

		public function skip_notice(){
			global $wp_filter;
			if ( is_network_admin() && isset( $wp_filter["network_admin_notices"] ) ) {
				unset( $wp_filter['network_admin_notices'] ); 
			} elseif ( is_user_admin() && isset( $wp_filter["user_admin_notices"] ) ) {
				unset( $wp_filter['user_admin_notices'] ); 
			} else {
				if ( isset( $wp_filter["admin_notices"] ) ) {
					unset( $wp_filter['admin_notices'] ); 
				}
			}
			if ( isset( $wp_filter["all_admin_notices"] ) ) {
				unset( $wp_filter['all_admin_notices'] ); 
			}
		}
	}
	new xsdatatables_main();
}