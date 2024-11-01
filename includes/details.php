<?php

defined( 'ABSPATH' ) || exit;

global $table_id, $table_type, $_xsnonce;
check_ajax_referer( 'xs-table'.$table_id, '_xsnonce' );
check_admin_referer( 'xs-table'.$table_id, '_xsnonce' );

include_once 'init.php';
$html_id = "xsdatatable_$table_id";
if(empty($table_dom)){
	$table_dom = 'lcBfrtip';
}
$table_doms = str_replace(["l","c"], '', $table_dom);

if($table_type == 'default'){
	?>
	<body>
	<div class="container_xs">
		<div class="<?php echo esc_attr($html_id); ?>">
			<span id="alert_row"></span>
			<div class="panel panel-default">
				<div class="panel-heading">
					<div class="row">
						<div class="panel_heading_left">
							<h5 class="panel-title"><b><?php echo esc_attr($table_name); ?></b></h5>
						</div>
						<div class="panel_heading_right" style="text-align:right;">
							<button type="button" name="row_add_xs" id="row_add_xs" class="btn btn-success btn-xs"><span class="dashicons dashicons-plus"></span></button>
							<button type="button" name="row_multi_delete_xs" id="row_multi_delete_xs" class="btn btn-danger btn-xs"><span class="dashicons dashicons-no"></span></button>
						</div>
					</div>
				</div>
				<table class="table table-bordered table-striped" id="<?php echo esc_attr($html_id); ?>" style="width:100%;border-spacing:0px;">
					<thead class="<?php echo esc_attr($html_id.'_thead'); ?>">
						<tr class="<?php echo esc_attr($html_id.'_head'); ?>">
							<?php $i = 0; ?>
							<th style="width:1%;" class="<?php echo esc_attr($html_id.'_head_'.$i); ?>"><input name="xs_select_all" id="xs-select-all" type="checkbox" /></th>
							<?php
							foreach($column_names as $names){
								$i = $i + 1;
								echo wp_kses_post('<th class="'.esc_attr($html_id.'_head_'.$i).'">'.$names.'</th>');
							}?>
							<th style="width:1%;"><?php esc_html_e('Status', 'xsdatatables'); ?></th>
							<th style="width:1%;"><?php esc_html_e('Edit', 'xsdatatables'); ?></th>
							<th style="width:1%;"><?php esc_html_e('Delete', 'xsdatatables'); ?></th>
						</tr>
					</thead>
					<?php if($table_footer == 'yes'){ ?>
					<tfoot class="<?php echo esc_attr($html_id.'_tfoot'); ?>">
						<tr class="<?php echo esc_attr($html_id.'_foot'); ?>">
							<?php 
							$i = 0;
							echo wp_kses_post('<th class="'.esc_attr($html_id.'_foot_'.$i).'"></th>');
							foreach($column_names as $names){
								$i = $i + 1;
								echo wp_kses_post('<th class="'.esc_attr($html_id.'_foot_'.$i).'">'.$names.'</th>');
							}?>
							<th><?php esc_html_e('Status', 'xsdatatables'); ?></th>
							<th><?php esc_html_e('Edit', 'xsdatatables'); ?></th>
							<th><?php esc_html_e('Delete', 'xsdatatables'); ?></th>
						</tr>
					</tfoot>
					<?php } ?>
				</table>
				<div class="panel-footer" style="text-align:center;">
					<button type="button" name="row_multi_active_xs" id="row_multi_active_xs" class="btn btn-success btn-xs"><?php esc_html_e('Active', 'xsdatatables'); ?></button>
					<button type="button" name="row_multi_inactive_xs" id="row_multi_inactive_xs" class="btn btn-danger btn-xs"><?php esc_html_e('Inactive', 'xsdatatables'); ?></button>
					<button type="button" name="row_multi_duplicate_xs" id="row_multi_duplicate_xs" class="btn btn-info btn-xs"><?php esc_html_e('Duplicate', 'xsdatatables'); ?></button>
					<a style="color: #fff;" href="<?php echo esc_url(xsdatatables_init::url_export($table_id, $table_type)); ?>" id="export-xsdatatables" class="btn btn-secondary btn-xs" onclick="return confirm('<?php esc_html_e('Are you sure you want to export this table?', 'xsdatatables'); ?>')"><?php esc_html_e( 'Export', 'xsdatatables' ); ?></a>
				</div>
			</div>
		</div>
	</div>
	<div class="modal-xs">
		<div id="rowModal" class="modal fade">
		  	<div class="modal-dialog modal-dialog-scrollable">
		    	<form method="post" id="row_form" enctype="multipart/form-data">
		      		<div class="modal-content">
		        		<div class="modal-header">
		          			<h5 class="modal-title" id="modal_title"><b><?php esc_html_e('Add Row', 'xsdatatables'); ?></b></h5>
		          			<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
		        		</div>
		        		<div class="modal-body">
		        			<span id="form_message"></span>
							<?php foreach(xsdatatables_column::name($table_id) as $key => $row){ ?>
		                    <div class="form-group">
		                        <label><?php echo esc_attr($row); ?></label>
	                        	<?php
	                        	$column_type = xsdatatables_column::type($table_id, $key);
	                        	if(in_array($column_type, array('text', 'url', 'date','datetime-local', 'month', 'week', 'time', 'email', 'number', 'color', 'tel'))){
	                        		?><input type="<?php echo esc_attr($column_type); ?>" id="<?php echo esc_attr($key); ?>" class="form-control" name="<?php echo esc_attr($key); ?>"><?php
	                        	}elseif($column_type == 'select' && !empty($column_customtype[array_flip($column_order)[$key]])){ ?>
									<select name="<?php echo esc_attr($key); ?>" id="<?php echo esc_attr($key); ?>">
										<option value="">Select</option>
										<?php echo wp_kses($column_customtype[array_flip($column_order)[$key]], array('option' => array('value' => array()))); ?>
									</select> <?php
	                        	}else{
	                        	?><textarea name="<?php echo esc_attr($key); ?>" id="<?php echo esc_attr($key); ?>" class="form-control" rows="1" style="min-height: 34px;"></textarea> <?php
	                        	}
	                        	?>
		                    </div>
							<?php } ?>
		        		</div>
		        		<div class="modal-footer">
							<input type="hidden" name="row_id" id="row_id"/>
							<input type="hidden" name="table_id" id="table_id" value="<?php echo esc_attr($table_id); ?>"/>
							<input type="hidden" name="_xsnonce" id="_xsnonce" value="<?php echo esc_attr($_xsnonce); ?>"/>
		          			<input type="hidden" name="action" id="action" value="" />
		          			<input type="submit" name="submit" id="submit_button" class="btn btn-success btn-xs" value="" />
		          			<button type="button" class="btn btn-secondary btn-xs" data-bs-dismiss="modal">Close</button>
		        		</div>
		      		</div>
		    	</form>
		  	</div>
		</div>
	</div>
	</body>
	</html>
	<script>
	jQuery(document).ready(function(){
		var table_id = <?php echo esc_attr($table_id); ?>;
		var dataRecord = jQuery('#<?php echo esc_attr($html_id); ?>').DataTable({
			"processing": true,
			"serverSide": <?php echo esc_attr($serverSide); ?>,
			"pagingType": '<?php echo esc_attr($table_pagination); ?>',
			"lengthMenu": <?php echo esc_attr($pagelength); ?>,
			"pageLength": <?php echo esc_attr($table_length); ?>,
			"deferRender": true,
			<?php if($table_responsive == 'yes'){ ?>
			"responsive": true,
			<?php }else{ ?>
			"scrollX": true,
			<?php } ?>
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
						jQuery("select").val('');
						dt.columns().every( function () {
							var column = this;
							column.search( '' ).draw();
						} );
						dt.search('').draw();
						dt.searchBuilder.rebuild();
						dt.searchPanes.rebuildPane();
						dt.page.len(<?php echo esc_attr($table_length); ?>).draw();
						dt.order([<?php echo wp_kses_post($table_sort); ?>]).draw();
						dt.columns( <?php echo esc_attr($column_hiddens); ?> ).visible( false );
						dt.columns( <?php echo esc_attr($column_show); ?> ).visible( true );
						dt.columns.adjust().draw();
						dt.rows().deselect();
						dt.columns().deselect();
						dt.cells().deselect();
					}
				},
			],
			"columns": [<?php echo wp_kses_post($column_width); ?> null, null, null],
			"ajax" : {
				url:"<?php echo esc_url(admin_url('admin-ajax.php')); ?>",
				type:"POST",
				data:{action:'row_getdata_xs', table_id:table_id, xs_type: "<?php echo esc_attr($table_type); ?>", _xsnonce:"<?php echo esc_attr($_xsnonce); ?>"}
			},
			"columnDefs":[
				{"targets":<?php echo esc_attr($column_filters); ?>,"searchPanes": {"show": true}},
				{"targets": [0,-1,-2,-3], "className": 'noVis text-center'},
				{
					"targets":<?php echo esc_attr($column_align['all']); ?>,
					"className": "text-center",
					"orderable":false
				},
				{
					"targets":<?php echo esc_attr($column_hiddens); ?>,
					"visible": false,
					"orderable":false,
					"searchable": true,
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
	        initComplete: function () {
	        	<?php foreach(json_decode($column_default) as $order){ ?>
	            this.api().columns([<?php echo esc_attr($order); ?>]).every( function () {
	                var column = this;
	                var select = jQuery('<select style="width: auto;"><option value=""><?php esc_html_e('Select', 'xsdatatables'); ?></option></select>')
						.appendTo("#<?php echo esc_attr($html_id); ?>_select")
						.on( 'change', function () {
	                        var val = jQuery.fn.dataTable.util.escapeRegex(
	                            jQuery(this).val()
	                        );
	                        <?php if(!empty($column_customfilter[$order])){ ?>
	                        column.search( val ? val : '', true, false ).draw();
	                        <?php }else{ ?>
	                        column.search( val ? '^'+val+'$' : '', true, false ).draw();
	                        <?php } ?>
	                    } );
	                <?php if(!empty($column_customfilter[$order])){ ?>
	                	select.append( '<?php echo wp_kses($column_customfilter[$order], $allowed_select); ?>' );
	                <?php }else{ ?>
	                column.data().unique().sort().each( function ( d, j ) {
	                	var val = jQuery('<div/>').html(d).text();
	                	select.append( '<option value="'+val+'">'+val.substr(0,100)+'</option>' );
	                } );
	            	<?php } ?>
	            } );
				<?php } ?>
				<?php foreach(json_decode($column_footer) as $order){ ?>
	            this.api().columns([<?php echo esc_attr($order); ?>]).every( function () {
	                var column = this;
	                var select = jQuery('<select><option value=""><?php esc_html_e('Select', 'xsdatatables'); ?></option></select>')
						.appendTo( jQuery(column.footer()).empty() )
						.on( 'change', function () {
	                        var val = jQuery.fn.dataTable.util.escapeRegex(
	                            jQuery(this).val()
	                        );
	                        <?php if(!empty($column_customfilter[$order])){ ?>
	                        column.search( val ? val : '', true, false ).draw();
	                        <?php }else{ ?>
	                        column.search( val ? '^'+val+'$' : '', true, false ).draw();
	                        <?php } ?>
	                    } );
	                <?php if(!empty($column_customfilter[$order])){ ?>
	                	select.append( '<?php echo wp_kses($column_customfilter[$order], $allowed_select); ?>' );
	                <?php }else{ ?>
	                column.data().unique().sort().each( function ( d, j ) {
	                	var val = jQuery('<div/>').html(d).text();
	                	select.append( '<option value="'+val+'">'+val.substr(0,100)+'</option>' );
	                } );
	            	<?php } ?>
	            } );
	            <?php } ?>
		        this.api().columns([-3]).every( function () {
		            var column = this;
		            var select = jQuery('<select id="select_status"><option value=""><?php esc_html_e('Select', 'xsdatatables'); ?></option></select>')
						.appendTo("#<?php echo esc_attr($html_id); ?>_select")
						.on( 'change', function () {
		                    var val = jQuery.fn.dataTable.util.escapeRegex(
		                        jQuery(this).val()
		                    );
		                    column
		                        .search( val ? '^'+val+'$' : '', true, false )
		                        .draw();
		                } );
					select.append( '<option value="Active">Active</option><option value="Inactive">Inactive</option>' );
		        } );
	        },
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
				jQuery( api.columns([0]).footer() ).html('');
				jQuery( api.columns([-1]).footer() ).html('<?php esc_html_e('Status', 'xsdatatables'); ?>');
				jQuery( api.columns([-2]).footer() ).html('<?php esc_html_e('Edit', 'xsdatatables'); ?>');
				jQuery( api.columns([-3]).footer() ).html('<?php esc_html_e('Delete', 'xsdatatables'); ?>');
			},
        	<?php if( $table_order > 0 ){ ?>
        		"order" : [<?php echo wp_kses_post($table_sort); ?>],
        	<?php }else{ ?>
        		"order" : [],
        	<?php } ?>
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

		jQuery('#row_add_xs').click(function(){
			jQuery('#row_form')[0].reset();
	    	jQuery('#modal_title').html("<b><?php esc_html_e('Add Row', 'xsdatatables'); ?></b>");
	    	jQuery('#action').val('row_add_xs');
	    	jQuery('#_xsnonce').val('<?php echo esc_attr($_xsnonce); ?>');
	    	jQuery('#submit_button').val('Add');
	    	jQuery('#rowModal').modal('show');
	    	jQuery('#form_message').html('');
		});

		jQuery('#row_form').on('submit', function(event){
			event.preventDefault();
			var form_data = jQuery(this).serialize();		
			jQuery.ajax({
				url:"<?php echo esc_url(admin_url('admin-ajax.php')); ?>",
				method:"POST",
				data:form_data,
				success:function(data){
					jQuery('#row_form')[0].reset();
					jQuery('#submit_button').attr('disabled', false);
					jQuery('#rowModal').modal('hide');
					jQuery('#alert_row').html(data);
					jQuery("#xs-select-all").prop('checked', false);
					dataRecord.ajax.reload(null, false);
				}
			})
		});

		jQuery(document).on('click', '.row_edit_xs', function(){
			var id = jQuery(this).data('id');
			var xsnonce = jQuery(this).data("xsnonce");
			jQuery('#form_message').html('');
			jQuery.ajax({
		      	url:"<?php echo esc_url(admin_url('admin-ajax.php')); ?>",
		      	method:"POST",
		      	data:{row_id:id, action:'row_single_xs', table_id:table_id, _xsnonce:xsnonce},
		      	dataType:'JSON',
		      	success:function(data){
					<?php foreach($column_order as $key => $row){ ?>
		        	jQuery('#<?php echo esc_attr('column_'.$key); ?>').val(data.<?php echo esc_attr('column_'.$key); ?>);
					<?php } ?>
		        	jQuery('#modal_title').html("<b><?php esc_html_e('Edit Row', 'xsdatatables'); ?></b>");
		        	jQuery('#action').val('row_edit_xs');
		        	jQuery('#submit_button').val('Edit');
		        	jQuery('#rowModal').modal('show');
		        	jQuery('#row_id').val(id);
		        	jQuery('#_xsnonce').val(xsnonce);
		      	}
		    })
		});

		jQuery(document).on('click', '.row_edit_status_xs', function(){
			var id = jQuery(this).attr('id');
			var status = jQuery(this).data("status");
			var xsnonce = jQuery(this).data("xsnonce");
			var action = 'row_edit_status_xs';
			if(confirm("<?php esc_html_e('Are you sure you want to change the status of this row?', 'xsdatatables'); ?>")){
				jQuery.ajax({
					url:"<?php echo esc_url(admin_url('admin-ajax.php')); ?>",
					method:"POST",
					data:{row_id:id, status:status, action:action, table_id:table_id, _xsnonce:xsnonce},
					success:function(data){
						jQuery('#alert_row').fadeIn().html(data);
						jQuery("#xs-select-all").prop('checked', false);
						dataRecord.ajax.reload(null, false);
					}
				})
			}else{
				return false;
			}
		});

		jQuery(document).on('click', '.row_delete_xs', function(){
	    	var id = jQuery(this).data('id');
	    	var xsnonce = jQuery(this).data("xsnonce");
	        if(confirm("<?php esc_html_e('Are you sure you want to delete this row?', 'xsdatatables'); ?>")){
	            jQuery.ajax({
	                url:"<?php echo esc_url(admin_url('admin-ajax.php')); ?>",
	                method:"POST",
	                data:{row_id:id, action:'row_delete_xs',table_id:table_id, _xsnonce:xsnonce},
	                success:function(data){
	                    jQuery('#alert_row').html(data);
	                    jQuery("#xs-select-all").prop('checked', false);
	                    dataRecord.ajax.reload(null, false);
	                }
	            });
	        }
	  	});

		jQuery('#xs-select-all').on('click', function(){
			var rows = dataRecord.rows({ 'search': 'applied' }).nodes();
			jQuery('input[type="checkbox"]', rows).prop('checked', this.checked);
		});

		jQuery('#<?php echo esc_attr($html_id); ?> tbody').on('change', 'input[type="checkbox"]', function(){
			var isChecked = jQuery(this).prop("checked");
			var isHeaderChecked = jQuery("#xs-select-all").prop("checked");
			if (isChecked == false && isHeaderChecked)
				jQuery("#xs-select-all").prop('checked', isChecked);
			else {
				jQuery('#<?php echo esc_attr($html_id); ?> tr:has(td)').find('input[type="checkbox"]').each(function() {
					if (jQuery(this).prop("checked") == false)
					isChecked = false;
				});
				jQuery("#xs-select-all").prop('checked', isChecked);
			}
		});

		jQuery('#row_multi_active_xs').click(function(){
		    if(confirm("<?php esc_html_e('Are you sure you want to change the status of this row?', 'xsdatatables'); ?>")){
		        var id = [];
		        var action = "row_multi_active_xs";
		        jQuery(':checkbox:checked').each(function(i){
		        	if(!isNaN(jQuery(this).val())){
		            	id[i] = jQuery(this).val();
		        	}
		        });
		        if(id.length === 0){
		            alert("<?php esc_html_e('Please Select at least one checkbox!', 'xsdatatables'); ?>");
		        }else{
		        	id = id.filter(item => item);
		            jQuery.ajax({
		                url:"<?php echo esc_url(admin_url('admin-ajax.php')); ?>",
						method:"POST",
		                data:{row_id:id, action:action, table_id:table_id, _xsnonce:"<?php echo esc_attr($_xsnonce); ?>"},
		                success:function(data){
		                    jQuery('#alert_row').fadeIn().html(data);
		                    jQuery("#xs-select-all").prop('checked', false);
		                    dataRecord.ajax.reload(null, false);
		                }
		            });
		        }
		    }else{
		        return false;
		    }
		});

		jQuery('#row_multi_inactive_xs').click(function(){
		    if(confirm("<?php esc_html_e('Are you sure you want to change the status of this row?', 'xsdatatables'); ?>")){
		        var id = [];
		        var action = "row_multi_inactive_xs";
		        jQuery(':checkbox:checked').each(function(i){
		        	if(!isNaN(jQuery(this).val())){
		            	id[i] = jQuery(this).val();
		        	}
		        });
		        if(id.length === 0){
		            alert("<?php esc_html_e('Please Select at least one checkbox!', 'xsdatatables'); ?>");
		        }else{
		        	id = id.filter(item => item);
		            jQuery.ajax({
		                url:"<?php echo esc_url(admin_url('admin-ajax.php')); ?>",
						method:"POST",
		                data:{row_id:id, action:action, table_id:table_id, _xsnonce:"<?php echo esc_attr($_xsnonce); ?>"},
		                success:function(data){
		                    jQuery('#alert_row').fadeIn().html(data);
		                    jQuery("#xs-select-all").prop('checked', false);
		                    dataRecord.ajax.reload(null, false);
		                }
		            });
		        }
		    }else{
		        return false;
		    }
		});

		jQuery('#row_multi_duplicate_xs').click(function(){
		    if(confirm("<?php esc_html_e('Are you sure you want to duplicate this row?', 'xsdatatables'); ?>")){
		        var id = [];
		        var action = "row_multi_duplicate_xs";
		        jQuery(':checkbox:checked').each(function(i){
		        	if(!isNaN(jQuery(this).val())){
		            	id[i] = jQuery(this).val();
		        	}
		        });
		        if(id.length === 0){
		            alert("<?php esc_html_e('Please Select at least one checkbox!', 'xsdatatables'); ?>");
		        }else{
		        	id = id.filter(item => item);
		            jQuery.ajax({
		                url:"<?php echo esc_url(admin_url('admin-ajax.php')); ?>",
						method:"POST",
		                data:{row_id:id, action:action, table_id:table_id, _xsnonce:"<?php echo esc_attr($_xsnonce); ?>"},
		                success:function(data){
		                    jQuery('#alert_row').fadeIn().html(data);
		                    jQuery("#xs-select-all").prop('checked', false);
		                    dataRecord.ajax.reload(null, false);
		                }
		            });
		        }
		    }else{
		        return false;
		    }
		});

		jQuery('#row_multi_delete_xs').click(function(){
		    if(confirm("<?php esc_html_e('Are you sure you want to delete this row?', 'xsdatatables'); ?>")){
		        var id = [];
		        var action = "row_multi_delete_xs";
		        jQuery(':checkbox:checked').each(function(i){
		        	if(!isNaN(jQuery(this).val())){
		            	id[i] = jQuery(this).val();
		        	}
		        });
		        if(id.length === 0){
		            alert("<?php esc_html_e('Please Select at least one checkbox!', 'xsdatatables'); ?>");
		        }else{
		        	id = id.filter(item => item);
		            jQuery.ajax({
		                url:"<?php echo esc_url(admin_url('admin-ajax.php')); ?>",
						method:"POST",
		                data:{row_id:id, action:action, table_id:table_id, _xsnonce:"<?php echo esc_attr($_xsnonce); ?>"},
		                success:function(data){
		                    jQuery('#alert_row').fadeIn().html(data);
		                    jQuery("#xs-select-all").prop('checked', false);
		                    dataRecord.ajax.reload(null, false);
		                }
		            });
		        }
		    }else{
		        return false;
		    }
		});
	});
	</script>
	<?php
}elseif(in_array($table_type, array('product', 'order', 'post', 'page'))){
	?>
	<body>
		<div class="container_xs">
			<div class="<?php echo esc_attr($html_id); ?>">
				<span id="alert_row"></span>
				<div class="panel panel-default">
					<div class="panel-heading">
						<div class="row">
							<div class="panel_heading_left">
								<h5 class="panel-title"><b><?php echo esc_attr($table_name); ?></b></h5>
							</div>
							<div class="panel_heading_right" style="text-align:right;"></div>
						</div>
					</div>
					<table class="table table-bordered table-striped" style="width:100%;" id="<?php echo esc_attr($html_id); ?>">
						<thead class="<?php echo esc_attr($html_id.'_thead'); ?>">
							<tr class="<?php echo esc_attr($html_id.'_head'); ?>">
								<?php $i = 0; ?>
								<th style="width:1%;" class="<?php echo esc_attr($html_id.'_head_'.$i); ?>"><input name="xs_select_all" id="xs-select-all" type="checkbox" /></th>
								<?php
								foreach($column_names as $names){
									$i = $i + 1;
									echo wp_kses_post('<th class="'.esc_attr($html_id.'_head_'.$i).'">'.$names.'</th>');
								}?>
								<th style="width:1%;"><?php esc_html_e('Status', 'xsdatatables'); ?></th>
							</tr>
						</thead>
						<?php if($table_footer == 'yes'){ ?>
						<tfoot class="<?php echo esc_attr($html_id.'_tfoot'); ?>">
							<tr class="<?php echo esc_attr($html_id.'_foot'); ?>">
								<?php 
								$i = 0;
								echo wp_kses_post('<th class="'.esc_attr($html_id.'_foot_'.$i).'"></th>');
								foreach($column_names as $names){
									$i = $i + 1;
									echo wp_kses_post('<th class="'.esc_attr($html_id.'_foot_'.$i).'">'.$names.'</th>');
								}?>
								<th><?php esc_html_e('Status', 'xsdatatables'); ?></th>
							</tr>
						</tfoot>
						<?php } ?>
					</table>
					<div class="panel-footer" style="text-align:center;">
						<?php if($table_type != 'order'){ ?>
						<button type="button" name="row_multi_active_xs" id="row_multi_active_xs" class="btn btn-success btn-xs"><?php esc_html_e('Active', 'xsdatatables'); ?></button>
						<button type="button" name="row_multi_inactive_xs" id="row_multi_inactive_xs" class="btn btn-danger btn-xs"><?php esc_html_e('Inactive', 'xsdatatables'); ?></button>
						<a style="color: #fff;" href="<?php echo esc_url(xsdatatables_init::url_export($table_id, $table_type)); ?>" id="export-xsdatatables" class="btn btn-secondary btn-xs" onclick="return confirm('<?php esc_html_e('Are you sure you want to export this table?', 'xsdatatables'); ?>')"><?php esc_html_e( 'Export', 'xsdatatables' ); ?></a>
						<?php } ?>
					</div>
				</div>
			</div>
		</div>
	</body>
	</html>
	<script>
	jQuery(document).ready(function(){
		var table_id = <?php echo esc_attr($table_id); ?>;
		var dataRecords = jQuery('#<?php echo esc_attr($html_id); ?>').DataTable({
			"processing": true,
			"ajax": {
				"url": "<?php echo esc_url(admin_url('admin-ajax.php')); ?>",
				"type": "POST",
				"data" : {action: "row_getdata_xs", table_id:table_id, xs_type: "<?php echo esc_attr($table_type); ?>", _xsnonce:"<?php echo esc_attr($_xsnonce); ?>"},
			},
			"columnDefs":[
				{"targets":<?php echo esc_attr($column_filters); ?>,"searchPanes": {"show": true}},
				{"targets": [0,-1], "className": 'noVis text-center'},
				{
					"targets":<?php echo esc_attr($column_align['status']); ?>,
					"className": "text-center",
					"orderable":false
				},
				{
					"targets":<?php echo esc_attr($column_hiddens); ?>,
					"visible": false,
					"orderable":false,
					"searchable": true,
				},
				<?php if($table_type == 'order'){ ?>
				{
					"targets":[0,<?php echo esc_attr($column_count + 1); ?>],
					"visible": false,
					"orderable":false,
					"searchable": true,
				},
				<?php } ?>
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
			"lengthMenu": <?php echo esc_attr($pagelength); ?>,
			"pageLength": <?php echo esc_attr($table_length); ?>,
			"deferRender": true,
			<?php if($table_responsive == 'yes'){ ?>
			"responsive": true,
			<?php }else{ ?>
			"scrollX": true,
			<?php } ?>
			"language": {
				"decimal": ',',
				"thousands": '.',
				"sEmptyTable":     "<?php esc_html_e('No data available in table', 'xsdatatables'); ?>",
				"sInfo":           "<?php esc_html_e('Showing _START_ to _END_ of _TOTAL_ entries', 'xsdatatables'); ?>",
				"sInfoEmpty":      "<?php esc_html_e('Showing 0 to 0 of 0 entries', 'xsdatatables'); ?>",
				"sInfoFiltered":   "<?php esc_html_e('(filtered from _MAX_ total entries)', 'xsdatatables'); ?>",
				"sInfoPostFix":    "",
				"sInfoThousands":  ",",
				"sLengthMenu":     "<?php esc_html_e('Show _MENU_', 'xsdatatables'); ?>",
				"sLoadingRecords": "<?php esc_html_e('Loading...', 'xsdatatables'); ?>",
				"sSearch":         "<?php esc_html_e('Search:', 'xsdatatables'); ?>",
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
			"paging":   true,
			"searching": true,
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
						jQuery("select").val('');
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
						dt.columns( <?php echo esc_attr($column_hiddens); ?> ).visible( false );
						dt.columns( <?php echo esc_attr($column_show); ?> ).visible( true );
						dt.columns.adjust().draw();
						dt.rows().deselect();
						dt.columns().deselect();
						dt.cells().deselect();
					}
				},
			],
			"columns": [<?php echo wp_kses_post($column_width); ?>],
	        initComplete: function () {
	            <?php foreach(json_decode($column_default) as $order){ ?>
	            this.api().columns([<?php echo esc_attr($order); ?>]).every( function () {
	                var column = this;
	                var select = jQuery('<select style="width: auto;"><option value=""><?php esc_html_e('Select', 'xsdatatables'); ?></option></select>')
						.appendTo("#<?php echo esc_attr($html_id); ?>_select")
	                    .on( 'change', function () {
	                        var val = jQuery.fn.dataTable.util.escapeRegex(
	                            jQuery(this).val()
	                        );
	                        <?php if(!empty($column_customfilter[$order])){ ?>
	                        column.search( val ? val : '', true, false ).draw();
	                        <?php }else{ ?>
	                        column.search( val ? '^'+val+'$' : '', true, false ).draw();
	                        <?php } ?>
	                    } );
	                <?php if(!empty($column_customfilter[$order])){ ?>
	                	select.append( '<?php echo wp_kses($column_customfilter[$order], $allowed_select); ?>' );
	                <?php }else{ ?>
	                column.data().unique().sort().each( function ( d, j ) {
	                	var val = jQuery('<div/>').html(d).text();
	                	select.append( '<option value="'+val+'">'+val.substr(0,100)+'</option>' );
	                } );
	            	<?php } ?>
	            } );
	            <?php } ?>
	            <?php foreach(json_decode($column_footer) as $order){ ?>
	            this.api().columns([<?php echo esc_attr($order); ?>]).every( function () {
	                var column = this;
	                var select = jQuery('<select><option value=""><?php esc_html_e('Select', 'xsdatatables'); ?></option></select>')
						.appendTo( jQuery(column.footer()).empty() )
	                    .on( 'change', function () {
	                        var val = jQuery.fn.dataTable.util.escapeRegex(
	                            jQuery(this).val()
	                        );
	                        <?php if(!empty($column_customfilter[$order])){ ?>
	                        column.search( val ? val : '', true, false ).draw();
	                        <?php }else{ ?>
	                        column.search( val ? '^'+val+'$' : '', true, false ).draw();
	                        <?php } ?>
	                    } );
	                <?php if(!empty($column_customfilter[$order])){ ?>
	                	select.append( '<?php echo wp_kses($column_customfilter[$order], $allowed_select); ?>' );
	                <?php }else{ ?>
	                column.data().unique().sort().each( function ( d, j ) {
	                	var val = jQuery('<div/>').html(d).text();
	                	select.append( '<option value="'+val+'">'+val.substr(0,100)+'</option>' );
	                } );
	            	<?php } ?>
	            } );
	            <?php } ?>
		        this.api().columns([-1]).every( function () {
		            var column = this;
		            var select = jQuery('<select id="select_status"><option value=""><?php esc_html_e('Select', 'xsdatatables'); ?></option></select>')
						.appendTo("#<?php echo esc_attr($html_id); ?>_select")
						.on( 'change', function () {
		                    var val = jQuery.fn.dataTable.util.escapeRegex(
		                        jQuery(this).val()
		                    );
		                    column
		                        .search( val ? '^'+val+'$' : '', true, false )
		                        .draw();
		                } );
					select.append( '<option value="Active">Active</option><option value="Inactive">Inactive</option>' );
		        } );
	        },
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
				jQuery( api.columns(0).footer() ).html('');
				jQuery( api.columns(<?php echo esc_attr($column_align['count'] + 1); ?>).footer() ).html('Status');
			},
        	<?php if( $table_order > 0 ){ ?>
        		"order" : [<?php echo wp_kses_post($table_sort); ?>],
        	<?php }else{ ?>
        		"order" : [],
        	<?php } ?>
		});

		dataRecords.on('order.dt search.dt', function () {
			<?php foreach($column_index as $index){ ?>
		    var i = 1;
		    dataRecord.cells(null, <?php echo esc_attr($index) ?>, { search: 'applied', order: 'applied' }).every(function (cell) {
		        this.data(i++);
		    });
			<?php } ?>
		}).draw();

		jQuery('.dataTables_filter input').off().on('keyup change clear input', function() {
			jQuery('#<?php echo esc_attr($html_id); ?>').DataTable().search(this.value.trim(), true, false).draw();
		});

		jQuery('#xs-select-all').on('click', function(){
			var rows = dataRecords.rows({ 'search': 'applied' }).nodes();
			jQuery('input[type="checkbox"]', rows).prop('checked', this.checked);
		});

		jQuery('#<?php echo esc_attr($html_id); ?> tbody').on('change', 'input[type="checkbox"]', function(){
			var isChecked = jQuery(this).prop("checked");
			var isHeaderChecked = jQuery("#xs-select-all").prop("checked");
			if (isChecked == false && isHeaderChecked)
				jQuery("#xs-select-all").prop('checked', isChecked);
			else {
				jQuery('#<?php echo esc_attr($html_id); ?> tr:has(td)').find('input[type="checkbox"]').each(function() {
					if (jQuery(this).prop("checked") == false)
					isChecked = false;
				});
				jQuery("#xs-select-all").prop('checked', isChecked);
			}
		});

		jQuery(document).on('click', '.row_edit_status_xs', function(){
			var id = jQuery(this).attr('id');
			var status = jQuery(this).data("status");
			var xsnonce = jQuery(this).data("xsnonce");
			var action = 'row_edit_status_xs';
			if(confirm("<?php esc_html_e('Are you sure you want to change the status of this row?', 'xsdatatables'); ?>")){
				jQuery.ajax({
					url:"<?php echo esc_url(admin_url('admin-ajax.php')); ?>",
					method:"POST",
					data:{row_id:id, status:status, action:action, table_id:table_id, xs_type: "<?php echo esc_attr($table_type); ?>", _xsnonce:xsnonce},
					success:function(data){
						jQuery('#alert_row').fadeIn().html(data);
						jQuery("#xs-select-all").prop('checked', false);
						dataRecords.ajax.reload(null, false);
					}
				})
			}else{
				return false;
			}
		});

		jQuery('#row_multi_active_xs').click(function(){
		    if(confirm("<?php esc_html_e('Are you sure you want to change the status of this row?', 'xsdatatables'); ?>")){
		        var id = [];
		        var action = "row_multi_active_xs";
		        jQuery(':checkbox:checked').each(function(i){
		        	if(!isNaN(jQuery(this).val())){
		            	id[i] = jQuery(this).val();
		        	}
		        });
		        if(id.length === 0){
		            alert("<?php esc_html_e('Please Select at least one checkbox!', 'xsdatatables'); ?>");
		        }else{
		        	id = id.filter(item => item);
		            jQuery.ajax({
		                url:"<?php echo esc_url(admin_url('admin-ajax.php')); ?>",
						method:"POST",
		                data:{row_id:id, action:action, table_id:table_id, xs_type: "<?php echo esc_attr($table_type); ?>", _xsnonce:"<?php echo esc_attr($_xsnonce); ?>"},
		                success:function(data){
		                    jQuery('#alert_row').fadeIn().html(data);
		                    jQuery("#xs-select-all").prop('checked', false);
		                    dataRecords.ajax.reload(null, false);
		                }
		            });
		        }
		    }else{
		        return false;
		    }
		});

		jQuery('#row_multi_inactive_xs').click(function(){
		    if(confirm("<?php esc_html_e('Are you sure you want to change the status of this row?', 'xsdatatables'); ?>")){
		        var id = [];
		        var action = "row_multi_inactive_xs";
		        jQuery(':checkbox:checked').each(function(i){
		        	if(!isNaN(jQuery(this).val())){
		            	id[i] = jQuery(this).val();
		        	}
		        });
		        if(id.length === 0){
		            alert("<?php esc_html_e('Please Select at least one checkbox!', 'xsdatatables'); ?>");
		        }else{
		        	id = id.filter(item => item);
		            jQuery.ajax({
		                url:"<?php echo esc_url(admin_url('admin-ajax.php')); ?>",
						method:"POST",
		                data:{row_id:id, action:action, table_id:table_id, xs_type: "<?php echo esc_attr($table_type); ?>", _xsnonce:"<?php echo esc_attr($_xsnonce); ?>"},
		                success:function(data){
		                    jQuery('#alert_row').fadeIn().html(data);
		                    jQuery("#xs-select-all").prop('checked', false);
		                    dataRecords.ajax.reload(null, false);
		                }
		            });
		        }
		    }else{
		        return false;
		    }
		});
	});
	</script>
	<?php
}