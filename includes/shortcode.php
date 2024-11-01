<?php

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'xsdatatables_shortcode' ) ) {

	class xsdatatables_shortcode {

		protected $shown_tables = array();

		public function __construct(){
			add_action( 'wp_enqueue_scripts', array( $this, 'xs_enqueue_style' ), 99 );
			if( !shortcode_exists( 'xsdatatable' ) ) {
				add_shortcode( 'xsdatatable', array( $this, 'shortcode' ) );
				add_shortcode( 'xsdatatable_audio', array( $this, 'shortcode_audio' ) );
			}
			add_action( "wp_ajax_nopriv_row_getdatas_xs", array( $this, 'row_getdatas_xs' ));
			add_action( "wp_ajax_nopriv_row_search_xs", array( $this, 'row_getdatas_xs' ));
		}

		public function row_getdatas_xs(){
			global $wpdb;
			if(isset($_POST['xs_type'])){
				if($_POST['xs_type'] == 'default'){
					include_once 'details_action.php';
				}elseif($_POST['xs_type'] == 'product'){
					include_once 'woo_action.php';
				}elseif(in_array($_POST['xs_type'], array('post', 'page'))){
					include_once 'post_action.php';
				}
			}
			wp_die();
		}

		function xs_enqueue_style(){
			if(!is_admin()){
				wp_enqueue_style( 'xsdatatables', XSDATATABLES_VENDOR. 'datatables/datatables.min.css' );
				wp_enqueue_style( 'xs-style', XSDATATABLES_ASSETS. 'css/style.css' );
				wp_enqueue_style( 'xs-admin', XSDATATABLES_ASSETS. 'css/front.css' );
				wp_enqueue_script( 'jquery' );
				wp_enqueue_script( 'xsdatatables', XSDATATABLES_VENDOR. 'datatables/datatables.min.js' );
				wp_enqueue_script( 'xs-audio', XSDATATABLES_ASSETS. 'js/soundmanager2.js' );
			}
		}

		function html_id($table_id){
			if ( ! isset( $this->shown_tables[ $table_id ] ) ) {
				$this->shown_tables[ $table_id ] = array(
					'count'		=> 0,
					'instances' => array(),
				);
			}
			$this->shown_tables[ $table_id ]['count']++;
			$count = $this->shown_tables[ $table_id ]['count'];
			$html_id = "xsdatatable_$table_id";
			if ( $count > 1 ) {
				$html_id .= "-no-{$count}";
			}
			return $html_id;
		}

		function table_background($table_id, $column_color, $column_colors, $headerbackground, $headercolor, $bodybackground, $bodycolor){
			$output = '';
			if($headerbackground == 'custom' && !empty($headercolor)){
				$output .= '.xsdatatable_'.$table_id.'_thead{background-color:'.$headercolor.' !important;}';
				$output .= '.xsdatatable_'.$table_id.'_tfoot{background-color:'.$headercolor.' !important;}';
			}
			if($bodybackground == 'custom' && !empty($bodycolor)){
				$output .= '.xsdatatable_'.$table_id.'_tbody td{background-color:'.$bodycolor.' !important;}';
			}
			foreach($column_color as $column){
				if(isset($column_colors[$column])){
					$output .= '.xsdatatable_'.$table_id.'_tbody .xsdatatable_'.$table_id.'_column_'.$column.'{background-color:'.$column_colors[$column].' !important;}';
				}
			}
			echo wp_kses_post($output);
		}

	    function print_inline($table_id, $column_color, $column_colors, $headerbackground, $headercolor, $bodybackground, $bodycolor){
	        echo '<style id="xscss">';
	        self::table_background($table_id, $column_color, $column_colors, $headerbackground, $headercolor, $bodybackground, $bodycolor);
	        echo '</style>';
	    }

		function shortcode_audio($atts, $content = null) {
		    extract(shortcode_atts(array(
		        'fileurl' => '',
		        'volume' => '',
		        'class' => '',
		        'loops' => '',
		      	), $atts));
		    if (empty($fileurl)) {
		        return '';
		    }
		    if (empty($volume)) {
		        $volume = '100';
		    }
		    if (empty($class)) {
		        $class = "xs_player_container";
		    }
		    if (empty($loops)) {
		        $loops = "false";
		    }
		    $ids = uniqid('', true);
		    $player_cont = '<div class="' . esc_attr($class) . '">';
		    $player_cont .= '<input type="button" id="xsplay_' . $ids . '" class="myButton_play" onClick="play_mp3(\'play\',\'' . $ids . '\',\'' . ltrim(strrev(base64_encode(esc_url($fileurl))), "=") . '\',\'' . esc_attr($volume) . '\',\'' . esc_attr($loops) . '\');show_hide(\'play\',\'' . $ids . '\');" />';
		    $player_cont .= '<input type="button"  id="xsstop_' . $ids . '" style="display:none" class="myButton_stop" onClick="play_mp3(\'stop\',\'' . $ids . '\',\'\',\'' . $volume . '\',\'' . $loops . '\');show_hide(\'stop\',\'' . $ids . '\');" />';
		    $player_cont .= '<div id="sm2-container"></div>';
		    $player_cont .= '</div>';
		    return $player_cont;
		}

		function shortcode($atts){
			if(is_admin() || empty($atts)){
				return;
			}
			extract(shortcode_atts(array('id' => ''), $atts));
			$table_id = (int)$id;
			$output = '';
			if(xsdatatables_table::status($table_id)){
				$_xsnonce = wp_create_nonce('xs-table'.$table_id.'_front');
				include 'init.php';
				self::print_inline($table_id, $column_color, $column_colors, $headerbackground, $headercolor, $bodybackground, $bodycolor);
				if($table_type == 'default'){
					$new_action = XSDATATABLES_INCLUDES. 'details_action.php';
				}elseif($table_type == 'product'){
					if(!function_exists('wc_get_products')){
						return;
					}
					$new_action = XSDATATABLES_INCLUDES. 'woo_action.php';
				}elseif(in_array($table_type, array('post', 'page'))){
					$new_action = XSDATATABLES_INCLUDES. 'post_action.php';
				}elseif($table_type == 'order'){
					return;
				}else{
					return;
				}
				$html_id = $this->html_id($table_id);
				if(empty($table_dom)){
					$table_dom = 'lcBfrtip';
				}
				$table_doms = str_replace(["l","c"], '', $table_dom);
				$output .= '<div class="container_xs">';
				$output .= '<div class="'.$html_id.'">';
				$output .= '<div class="panel panel-default">';
				$output .= '<table class="stripe" id="'.$html_id.'" style="width:100%;border-spacing:0px;">';
					$output .= '<thead class="'.$html_id.'_thead"><tr class="'.$html_id.'_head">';
						$i = 0;
						$output .= '<th class="'.$html_id.'_head_'.$i.'">'.esc_html__('ID', 'xsdatatables').'</th>';
						foreach($column_names as $names){
							$i = $i + 1;
							$output .= '<th class="'.$html_id.'_head_'.$i.'">'.$names.'</th>';
						}
					$output .= '</tr></thead>';
					$output .= '<tbody class="'.$html_id.'_tbody"></tbody>';
					if($table_footer == 'yes'){
						$i = 0;
						$output .= '<tfoot class="'.$html_id.'_tfoot"><tr class="'.$html_id.'_foot">';
							$output .= '<th class="'.$html_id.'_foot_'.$i.'">ID</th>';
							foreach($column_names as $names){
								$i = $i + 1;
								$output .= '<th class="'.$html_id.'_foot_'.$i.'">'.$names.'</th>';
							}
						$output .= '</tr></tfoot>';
					}
				$output .= '</table>';
				$output .= '</div>';
				$output .= '</div>';
				$output .= '</div>';
				?>
				<script>
				jQuery(document).ready(function(){
					var table = <?php echo esc_attr($table_id); ?>;
					var dataRecord = jQuery('#<?php echo esc_attr($html_id); ?>').DataTable({
						"processing": true,
						"pagingType": '<?php echo esc_attr($table_pagination); ?>',
						"serverSide": <?php echo esc_attr($serverSide); ?>,
						"deferRender": true,
						<?php if($table_responsive == 'yes'){ ?>
						"responsive": true,
						<?php }else{ ?>
						"scrollX": true,
						<?php } ?>
						<?php if(strpos($table_dom, "lc" ) !== false){ ?>
						"dom": 'l<"#<?php echo esc_attr($html_id.'_select.dataTables_length'); ?>"><?php echo esc_attr($table_doms); ?>',
						<?php }elseif(strpos($table_dom, "l" ) !== false){ ?>
						"dom": 'l<?php echo esc_attr($table_doms); ?>',
						<?php }elseif(strpos($table_dom, "c" ) !== false){ ?>
						"dom": '<"#<?php echo esc_attr($html_id.'_select.dataTables_length'); ?>"><?php echo esc_attr($table_doms); ?>',
						<?php }else{ ?>
						"dom": '<?php echo esc_attr($table_doms); ?>',
						<?php } ?>
						"buttons": [
							<?php if($table_button == 'yes'){ ?>
				            {
				                extend: 'collection',
				                text: '<?php esc_html_e('Export', 'xsdatatables'); ?>',
				                className: 'custom-html-collection',
				                buttons: [
									{
										text: 'JSON',
										action: function ( e, dt, button, config ) {
											var data = dt.buttons.exportData();
											jQuery.fn.dataTable.fileSave(
												new Blob( [ JSON.stringify( data ) ] ),
												'Export.json'
											);
										}
									},
									{
										extend: 'excelHtml5',
										footer: true,
										exportOptions: {
											columns: ':visible'
										},
										title: ''
									},
									{
										extend: 'csvHtml5',
										footer: true,
										exportOptions: {
											columns: ':visible'
										},
										title: ''
									},
									{
										extend: 'print',
										footer: true,
										exportOptions: {
											stripHtml : false,
											columns: ':visible'
										},
										title: ''
									},
				                ]
				            },
							<?php } ?>
							{
								text: '<?php esc_html_e('Reset', 'xsdatatables'); ?>',
								header: true,
								action: function ( e, dt, node, config ) {
									jQuery("input[type='text']").each(function () { 
										jQuery(this).val(''); 
									})
									<?php 
						        	foreach(json_decode($column_filters) as $column){ ?>
										jQuery("#<?php echo esc_attr($html_id.'_select_'.$column); ?>").val('');
						        	<?php } ?>
									dt.columns().every( function () {
										var column = this;
										column
									    	.search( '' )
									    	.draw();
									} );
									dt.search('').draw();
									dt.searchBuilder.rebuild();
									dt.searchPanes.rebuildPane();
									dt.page.len(<?php echo esc_attr($table_length); ?>).draw();
									dt.order([<?php echo wp_kses_post($table_sort); ?>]).draw();
									dt.columns( <?php echo esc_attr($column_hidden); ?> ).visible( false );
									dt.columns( <?php echo esc_attr($column_show); ?> ).visible( true );
									dt.columns.adjust().draw();
									dt.rows().deselect();
									dt.columns().deselect();
									dt.cells().deselect();
								}
							},
						],
						"destroy": true,
						"ajax": {
							"url": "<?php echo esc_url(admin_url('admin-ajax.php')); ?>",
							"type": "POST",
							"data" : {action: "row_getdatas_xs", table_id: "<?php echo esc_attr($table_id); ?>", xs_type: "<?php echo esc_attr($table_type); ?>", _xsnonce:"<?php echo esc_attr($_xsnonce); ?>"},
						},
				        initComplete: function () {
				        	<?php
				        	foreach(json_decode($column_default) as $column){ ?>
					            this.api().columns(<?php echo esc_attr($column); ?>).every( function () {
					                var column = this;
					                var select = jQuery('<select id="<?php echo esc_attr($html_id.'_select_'.$column); ?>" style="width: auto;"><option value=""><?php esc_html_e('Select', 'xsdatatables'); ?></option></select>')
										.appendTo("#<?php echo esc_attr($html_id); ?>_select")
					                    .on( 'change', function () {
					                        var val = jQuery.fn.dataTable.util.escapeRegex(
					                            jQuery(this).val()
					                        );
					                        <?php if(!empty($column_customfilter[$column])){ ?>
					                        column.search( val ? val : '', true, false ).draw();
					                        <?php }else{ ?>
					                        column.search( val ? '^'+val+'$' : '', true, false ).draw();
					                        <?php } ?>
					                    } );
					                <?php if(!empty($column_customfilter[$column])){ ?>
					                	select.append( '<?php echo wp_kses($column_customfilter[$column], $allowed_select); ?>' );
					                <?php }else{ ?>
					                column.data().unique().sort().each( function ( d, j ) {
					                	var val = jQuery('<div/>').html(d).text();
					                	select.append( '<option value="'+val+'">'+val.substr(0,100)+'</option>' );
					                } );
					            	<?php } ?>
					            } );
				        	<?php }
				        	foreach(json_decode($column_footer) as $column){ ?>
					            this.api().columns(<?php echo esc_attr($column); ?>).every( function () {
					                var column = this;
									var select = jQuery('<select id="<?php echo esc_attr($html_id.'_select_'.$column); ?>"><option value=""><?php esc_html_e('Select', 'xsdatatables'); ?></option></select>')
										.appendTo( jQuery(column.footer()).empty() )
					                    .on( 'change', function () {
					                        var val = jQuery.fn.dataTable.util.escapeRegex(
					                            jQuery(this).val()
					                        );
					                        <?php if(!empty($column_customfilter[$column])){ ?>
					                        column.search( val ? val : '', true, false ).draw();
					                        <?php }else{ ?>
					                        column.search( val ? '^'+val+'$' : '', true, false ).draw();
					                        <?php } ?>
					                    } );
					                <?php if(!empty($column_customfilter[$column])){ ?>
					                	select.append( '<?php echo wp_kses($column_customfilter[$column], $allowed_select); ?>' );
					                <?php }else{ ?>
					                column.data().unique().sort().each( function ( d, j ) {
					                	var val = jQuery('<div/>').html(d).text();
					                	select.append( '<option value="'+val+'">'+val.substr(0,100)+'</option>' );
					                } );
					            	<?php } ?>
					            } );
				        	<?php } ?>
				        },
						"lengthMenu": <?php echo esc_attr($pagelength); ?>,
						"pageLength": <?php echo esc_attr($table_length); ?>,
						"language": {
							"decimal": ',',
							"thousands": '.',
							"sEmptyTable":     "<?php esc_html_e('No data available in table', 'xsdatatables'); ?>",
							"sInfo":           "<?php esc_html_e('Showing _START_ to _END_ of _TOTAL_ entries', 'xsdatatables'); ?>",
							"sInfoEmpty":      "<?php esc_html_e('Showing 0 to 0 of 0 entries', 'xsdatatables'); ?>",
							"sInfoFiltered":   "<?php esc_html_e('(filtered from _MAX_ total entries)', 'xsdatatables'); ?>",
							"sInfoPostFix":    "",
							"sInfoThousands":  ",",
							"sLengthMenu":     "<?php esc_html_e('_MENU_', 'xsdatatables'); ?>",
							"sLoadingRecords": "<?php esc_html_e('Loading...', 'xsdatatables'); ?>",
							"sSearch":         "<?php esc_html_e('', 'xsdatatables'); ?>",
							"searchPlaceholder": "<?php esc_html_e('Search...', 'xsdatatables'); ?>",
							"sZeroRecords":    "<?php esc_html_e('No matching records found', 'xsdatatables'); ?>",
							"oPaginate": {
								"sFirst":    "<?php esc_html_e('First', 'xsdatatables'); ?>",
								"sLast":     "<?php esc_html_e('Last', 'xsdatatables'); ?>",
								"sNext":     "<?php esc_html_e('Next', 'xsdatatables'); ?>",
								"sPrevious": "<?php esc_html_e('Previous', 'xsdatatables'); ?>"
							},
							"oAria": {
								"sSortAscending":  "<?php esc_html_e(': activate to sort column ascending', 'xsdatatables'); ?>",
								"sSortDescending": "<?php esc_html_e(': activate to sort column descending', 'xsdatatables'); ?>"
							},
							searchBuilder: {title: {0: '', _: 'Filters (%d)'}},
							searchPanes: {collapse: {0: '<?php esc_html_e('Search Panes', 'xsdatatables'); ?>', _: '<?php esc_html_e('Search Panes', 'xsdatatables'); ?> (%d)'}},
							buttons: {colvis: '<?php esc_html_e('Column', 'xsdatatables'); ?>'}
						},
						"columns": [<?php echo wp_kses_post($column_width); ?>],
						"columnDefs":[
							{"targets":<?php echo esc_attr($column_filters); ?>,"searchPanes": {"show": true}},
							{"targets": 0, "className": 'noVis text-center'},
							{
								"targets":<?php echo esc_attr($column_hidden); ?>,
								"visible": false,
								"orderable":true,
								"searchable": true,
								"className": 'noVis text-center'
							},
							<?php for ($i = 0; $i <= $column_count; $i++){
								if(in_array($i, json_decode($align_left))){ ?>
									{"targets":<?php echo esc_attr($i); ?>,"className": "<?php echo esc_attr($html_id.'_column_'.$i); ?> text-left",},
								<?php }elseif(in_array($i, json_decode($align_center))){ ?>
									{"targets":<?php echo esc_attr($i); ?>,"className": "<?php echo esc_attr($html_id.'_column_'.$i); ?> text-center",},
								<?php }elseif(in_array($i, json_decode($align_right))){ ?>
									{"targets":<?php echo esc_attr($i); ?>,"className": "<?php echo esc_attr($html_id.'_column_'.$i); ?> text-right",},
								<?php } ?>
							<?php } ?>
					        {"targets":<?php echo esc_attr($column_orderable); ?>,"orderable": false,},
					        {"targets":<?php echo esc_attr($column_searchable); ?>,"searchable": false,},
						],
						"footerCallback": function ( row, data, start, end, display ) {
						    var api = this.api();
						    var intVal = function ( i ) {
						        return typeof i === 'string' ?
						            i.replace(/[\$,]/g, '')*1 :
						            typeof i === 'number' ?
						                i : 0;
						    };
							<?php foreach($column_total as $row){ ?>
						    total = api
						        .column( <?php echo esc_attr($row); ?> )
						        .data()
						        .reduce( function (a, b) {
						            return intVal(a) + intVal(b);
						        }, 0 );
						    pageTotal = api
						        .column( <?php echo esc_attr($row); ?>, { page: 'current'} )
						        .data()
						        .reduce( function (a, b) {
						            return intVal(a) + intVal(b);
						        }, 0 );
						    jQuery( api.column( <?php echo esc_attr($row); ?> ).footer() ).html(
						        Math.round(pageTotal * 100)/100 +'/'+ Math.round(total * 100)/100
						    );
							<?php } ?>
							jQuery( api.columns(0).footer() ).html('ID');
						},
						"order" : [<?php echo wp_kses_post($table_sort); ?>],
					});
					<?php if(!empty($column_index)){ ?>
					dataRecord.on('order.dt search.dt', function () {
						<?php foreach($column_index as $index){ ?>
					    var i = 1;
					    dataRecord.cells(null, <?php echo esc_attr($index) ?>, { search: 'applied', order: 'applied' }).every(function (cell) {
					        this.data(i++);
					    });
						<?php } ?>
					});
					<?php } ?>
					jQuery('.dataTables_filter input').off().on('keyup change clear input', function() {
						jQuery('#<?php echo esc_attr($html_id); ?>').DataTable().search(this.value.trim(), true, false).draw();
					});
				});
				</script>
				<?php
			}
			return $output;
		}
	}
	new xsdatatables_shortcode();
}