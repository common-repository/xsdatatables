<?php

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'xsdatatables_option' ) ) :

	class xsdatatables_option {

		private static $_instance = null;

		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
				self::$_instance->init_actions();
			}
			return self::$_instance;
		}
		public function init_actions() {
			add_action( 'wp_head', array( $this, 'xscss_print_inline' ) );
			if( is_admin() && current_user_can( 'manage_options' ) ) {
				add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		        add_action( 'admin_enqueue_scripts', array( $this, 'xs_enqueue_style' ));
		        add_action( 'admin_init', array( $this, 'custom_css' ) );
	    	}
		}
		public function custom_css() {
			if ( isset( $_POST['xs_update_css'] ) && isset( $_POST['xs_custom_css'] ) ) {
				$result = update_option( 'xs_custom_css', $_POST['xs_custom_css'] );
				if($result){
					$message = esc_html__('Custom CSS updated successfully.', 'xsdatatables');
					add_action( 'admin_notices', function() use ($message) { echo wp_kses_post('<div class="notice notice-success is-dismissible"><p>'.$message.'</p></div>'); } );
				}
			}
		}
		public function xs_enqueue_style() {
	        wp_enqueue_style( 'xs-custom', XSDATATABLES_ASSETS. 'css/custom.css', array(), '20220306' );
	    	wp_enqueue_code_editor( array( 'type' => 'text/html' ) );
	        wp_enqueue_script( 'xs-custom', XSDATATABLES_ASSETS. 'js/custom.js', array( 'jquery' ), '20220306', true );
	    }
	    public function xscss_the_css() {
	        $options     = get_option( 'xs_custom_css' );
	        $raw_content = isset( $options['xscss-content'] ) ? $options['xscss-content'] : '';
	        $content     = stripslashes( $raw_content );
	        $content     = str_replace( '&gt;', '>', $content );
	        echo wp_kses_post(strip_tags( $content ));
	    }
	    public function xscss_print_inline() {
	        echo '<style id="xscss">';
			self::xscss_the_css();
	        echo '</style>';
	    }
		public function admin_menu(){
			add_submenu_page( 'xsdatatables', 'xsOption', esc_html__('Options', 'xsdatatables'), 'manage_options', 'xsoption', array( $this, 'option_page' ), 1 );
		}
	    public function option_page(){
	    	$options = get_option( 'xs_custom_css' );
	    	$content = isset( $options['xscss-content'] ) && ! empty( $options['xscss-content'] ) ? $options['xscss-content'] : '';
			?>
	    	<div class="wrap">
	    		<div class="container_xs">
	    		<h3 style="margin-bottom: 1em;"><?php esc_html_e( 'Custom CSS', 'xsdatatables' ); ?></h3>
	    		<form method="post" enctype="multipart/form-data">
	    			<div id="xs_template">
	    				<div>
	    					<textarea cols="70" rows="10" name="xs_custom_css[xscss-content]" class="xscss-content" id="xs_custom_css[xscss-content]"><?php echo esc_html( stripslashes( $content ) ); ?></textarea>
	    				</div></p>
	    				<button type="submit" id="xs_update_css" class="button button-primary" name="xs_update_css" value="xs_update_css">Update CSS</button>
	    			</div>
	    		</form>
	    		<hr>
	    		<div class="row">
		    		<div class="col-md-6">
						<h3 style="margin-top:0px;"><?php esc_html_e( 'Uninstall', 'xsdatatables' ); ?></h3>
						<?php
						if(current_user_can('delete_plugins')){
						echo __( '- Uninstalling <strong>will permanently delete</strong> all tables and options from the database.', 'xsdatatables' ) . '<br />'
							. __( '- You will manually need to remove the plugin&#8217;s files from the plugin folder afterwards.', 'xsdatatables' ) . '<br />'
							. __( '- Be very careful with this and only click the button if you know what you are doing!', 'xsdatatables' );
						?>
						</p>
						<p><a style="color: #fff;" href="<?php echo esc_url(xsdatatables_init::url_uninstall()); ?>" id="uninstall-xsdatatables" class="btn btn-danger btn-xs" onclick="return confirm('<?php esc_html_e('Do you really want to delete all data?', 'xsdatatables'); ?>')"><?php esc_html_e( 'Uninstall', 'xsdatatables' ); ?></a></p>
						<?php } ?>
					</div>
				</div>
				</div>
	    	</div>
	    	<?php
	    }
	}
	if ( ! function_exists( 'xsdatatables_option' ) ) :
		function xsdatatables_option() {
			return xsdatatables_option::instance();
		}
	endif;
	add_action( 'after_setup_theme', 'xsdatatables_option', 99 );

endif;
