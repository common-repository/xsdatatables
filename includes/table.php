<?php

defined( 'ABSPATH' ) || exit;

global $XSDATATABLES_TABLES;
$_xsnonce = wp_create_nonce(XSDATATABLES_TABLE);

?>
<body>
<div class="container_xs">
	<span id="alert_table"></span>
	<div class="panel panel-default">
		<div class="panel-heading">
			<div class="row">
				<div class="panel_heading_left">
					<h5 class="panel-title"><b><?php esc_html_e('All Tables', 'xsdatatables'); ?></b></h5>
				</div>
				<div class="panel_heading_right" style="text-align:right;">
					<button type="button" name="add" id="table_add_xs" class="btn btn-success btn-xs"><span class="dashicons dashicons-plus"></span></button>
					<button type="button" name="table_multi_delete_xs" id="table_multi_delete_xs" class="btn btn-danger btn-xs"><span class="dashicons dashicons-no"></span></button>
				</div>
			</div>
		</div>
		<table style="width:100%" class="table table-bordered table-striped" id="xs_table">
			<thead style="text-align:left;">
				<tr>
					<th style="width:1%"><input name="xs_select_all" id="xs-select-all" type="checkbox" /></th>
					<th style="width:30%"><?php esc_html_e('Name', 'xsdatatables'); ?></th>
					<th style="width:11%"><?php esc_html_e('Shortcode', 'xsdatatables'); ?></th>
					<th style="width:5%"><?php esc_html_e('Type', 'xsdatatables'); ?></th>
					<th style="width:1%"><?php esc_html_e('Records', 'xsdatatables'); ?></th>
					<th style="width:18%"><?php esc_html_e('Author', 'xsdatatables'); ?></th>
					<th style="width:1%"><?php esc_html_e('Status', 'xsdatatables'); ?></th>
					<th style="width:1%"><?php esc_html_e('Details', 'xsdatatables'); ?></th>
					<th style="width:1%"><?php esc_html_e('Edit', 'xsdatatables'); ?></th>
					<th style="width:1%"><?php esc_html_e('Delete', 'xsdatatables'); ?></th>
				</tr>
			</thead>
		</table>
		<div class="panel-footer" style="text-align:center;">
			<button type="button" name="table_multi_active_xs" id="table_multi_active_xs" class="btn btn-success btn-xs"><?php esc_html_e('Active', 'xsdatatables'); ?></button>
			<button type="button" name="table_multi_inactive_xs" id="table_multi_inactive_xs" class="btn btn-danger btn-xs"><?php esc_html_e('Inactive', 'xsdatatables'); ?></button>
		</div>
	</div>
	<p>
</div>
<div class="modal-xs">
	<div id="tableModal" class="modal fade">
		<div class="modal-dialog">
			<form method="post" id="tableForm">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title"><b><?php esc_html_e('Edit Table', 'xsdatatables'); ?></b></h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class="modal-body">
						<div class="form-group">
							<label><?php esc_html_e('Name', 'xsdatatables'); ?></label>
							<input type="text" class="form-control" id="table_name" name="table_name" placeholder="Name" required>
						</div>
						<div class="row" id="new_field">
	  						<div class="col-md-6">
	  							<label><?php esc_html_e('Column Count', 'xsdatatables'); ?></label>
	  							<input type="number" name="column_number" id="column_number" value="1" min="1" max="10" class="form-control" required/>
	  						</div>
	  						<div class="col-md-6">
								<label><?php esc_html_e('Type', 'xsdatatables'); ?></label>
								<select name="table_type" id="table_type" class="form-control" required >
									<option value=""><?php esc_html_e('Select', 'xsdatatables'); ?></option>
									<option value="default">Default</option>
									<?php if(function_exists('wc_get_products')){ ?>
									<option value="product">Product</option>
									<option value="order">order</option>
									<?php } ?>
									<option value="post">Post</option>
									<option value="page">Page</option>
								</select>
	  						</div>
						</div>
						<div class="row">
							<div class="col-md-6">
								<label><?php esc_html_e('Responsive', 'xsdatatables'); ?></label>
								<select name="table_responsive" id="table_responsive" class="form-control" required >
									<option value="no">No</option>
									<option value="yes" selected>Yes</option>
								</select>
							</div>
							<div class="col-md-6">
	  							<label><?php esc_html_e('Page Length', 'xsdatatables'); ?></label>
	  							<input type="number" name="table_length" id="table_length" value="10" min="1" class="form-control" required/>
							</div>
						</div>
						<div class="row">
							<div class="col-md-6">
								<label><?php esc_html_e('Show Footer', 'xsdatatables'); ?></label>
								<select name="table_footer" id="table_footer" class="form-control" required >
									<option value="no">No</option>
									<option value="yes">Yes</option>
								</select>
							</div>
	  						<div class="col-md-6">
								<label><?php esc_html_e('Show Export', 'xsdatatables'); ?></label>
								<select name="table_button" id="table_button" class="form-control" required >
									<option value="no">No</option>
									<option value="yes">Yes</option>
								</select>
	  						</div>
						</div>
						<div class="row">
							<div class="col-md-6">
								<label><?php esc_html_e('Sorting Column', 'xsdatatables'); ?></label>
								<input type="number" name="table_order" id="table_order" min="0" value ="0" class="form-control" required/>
							</div>
							<div class="col-md-6">
								<label><?php esc_html_e('Sorting Type', 'xsdatatables'); ?></label>
								<select name="table_sort" id="table_sort" class="form-control" required >
									<option value="asc">ASC</option>
									<option value="desc">DESC</option>
								</select>
							</div>
						</div>
						<div class="row">
							<div class="col-md-6">
								<label><?php esc_html_e('Categories ID', 'xsdatatables'); ?></label>
								<input type="text" name="table_category" id="table_category" class="form-control" placeholder="1,2,3" disabled/>
							</div>
							<div class="col-md-6">
								<label><?php esc_html_e('Pagination', 'xsdatatables'); ?></label>
								<select name="table_pagination" id="table_pagination" class="form-control" required >
									<option value="full">full</option>
									<option value="simple">simple</option>
									<option value="numbers">numbers</option>
									<option value="full_numbers">full_numbers</option>
									<option value="simple_numbers" selected>simple_numbers</option>
									<option value="first_last_numbers">first_last_numbers</option>
								</select>
							</div>
						</div>
						<div class="row">
							<div class="col-md-6">
								<label><?php esc_html_e('Server-side', 'xsdatatables'); ?></label>
								<select name="table_serverside" id="table_serverside" class="form-control" disabled>
									<option value="no">No</option>
									<option value="yes">Yes</option>
								</select>
							</div>
							<div class="col-md-6">
								<label><?php esc_html_e('Maximum number of rows', 'xsdatatables'); ?></label>
								<input type="number" name="table_limit" id="table_limit" min="-1" value ="-1" class="form-control" required/>
							</div>
						</div>
						<div class="row">
							<div class="col-md-6">
								<label><?php esc_html_e('Header Background', 'xsdatatables'); ?></label>
								<select name="table_headerbackground" id="table_headerbackground" class="form-control" required >
									<option value="default" selected>Default</option>
									<option value="custom">Custom</option>
								</select>
							</div>
							<div class="col-md-6">
								<label><?php esc_html_e('Header Background Color', 'xsdatatables'); ?></label>
				      			<input type="color" name="table_headercolor" id="table_headercolor" class="form-control" value="#ffffff" />
							</div>
						</div>
						<div class="row">
							<div class="col-md-6">
								<label><?php esc_html_e('Body Background', 'xsdatatables'); ?></label>
								<select name="table_bodybackground" id="table_bodybackground" class="form-control" required >
									<option value="default" selected>Default</option>
									<option value="custom">Custom</option>
								</select>
							</div>
							<div class="col-md-6">
								<label><?php esc_html_e('Body Color', 'xsdatatables'); ?></label>
				      			<input type="color" name="table_bodycolor" id="table_bodycolor" class="form-control" value="#ffffff" />
							</div>
						</div>
						<div class="form-group" id="xs_dom" style="display: none;">
							<label>Dom</label><br/>
				            <div class="table-responsive">
				              <table class="table table-bordered" style="margin-bottom: 0;">
								<thead>
								<tr>
									<th style="text-align: center;">Length</th>
									<th style="text-align: center;">Select inputs</th>
									<th style="text-align: center;">Buttons</th>
									<th style="text-align: center;">Filtering</th>
									<th style="text-align: center;display: none;">pRocessing</th>
									<th style="text-align: center;display: none;">Table</th>
									<th style="text-align: center;">Information</th>
									<th style="text-align: center;">Pagination</th>
								</tr>
								</thead>
								<tr>
									<td style="text-align:center;"><input type="checkbox" name="xs_dom[]" id="l" value="l" /></td>
									<td style="text-align:center;"><input type="checkbox" name="xs_dom[]" id="c" value="c" /></td>
									<td style="text-align:center;"><input type="checkbox" name="xs_dom[]" id="B" value="B" /></td>
									<td style="text-align:center;"><input type="checkbox" name="xs_dom[]" id="f" value="f" /></td>
									<td style="text-align:center;display: none;"><input type="checkbox" name="xs_dom[]" id="r" value="r" checked/></td>
									<td style="text-align:center;display: none;"><input type="checkbox" name="xs_dom[]" id="t" value="t" checked/></td>
									<td style="text-align:center;"><input type="checkbox" name="xs_dom[]" id="i" value="i" /></td>
									<td style="text-align:center;"><input type="checkbox" name="xs_dom[]" id="p" value="p" /></td>
				                </tr>
				              </table>
				            </div>
			            </div>
						<div class="form-group">
							<label><?php esc_html_e('Note', 'xsdatatables'); ?></label>
							<textarea class="form-control" rows="1" id="table_note" name="table_note" style="min-height: 34px;"></textarea>
						</div>
					</div>
					<div class="modal-footer">
						<input type="hidden" name="table_id" id="table_id"/>
						<input type="hidden" name="action" id="action"/>
						<input type="hidden" name="_xsnonce" id="_xsnonce" value="<?php echo esc_attr($_xsnonce); ?>"/>
						<input type="submit" name="submit" id="submit_button" class="btn btn-success btn-xs"/>
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
	var dataRecords = jQuery('#xs_table').DataTable({
		"processing": true,
		"ajax": {
			"url": "<?php echo esc_url(admin_url('admin-ajax.php')); ?>",
			"type": "POST",
			"data" : {action: "table_getdata_xs", _xsnonce:"<?php echo esc_attr($_xsnonce); ?>"},
		},
		"dom": 'l<"#xs_select.dataTables_length">Bfrtip',
		"buttons": [
			{
				extend: 'colvis',
				columns: ':not(.noVis)'
			},
			{
				text: '<?php esc_html_e('Reset', 'xsdatatables'); ?>',
				header: true,
				action: function ( e, dt, node, config ) {
					jQuery("input[type='text']").each(function () { 
						jQuery(this).val(''); 
					})
					jQuery("#select_type").val('');
					jQuery("#select_status").val('');
					dt.columns().every( function () {
						var column = this;
						column
					    	.search( '' )
					    	.draw();
					} );
					dt.search('').draw();
					dt.searchBuilder.rebuild();
					dt.page.len(10).draw();
					dt.order([[2, 'asc']]).draw();
					dt.columns([5]).visible( false );
					dt.columns([1, 2, 3, 4, 6, 7, 8, 9]).visible( true );
					dt.columns.adjust().draw();
					dt.rows().deselect();
					dt.columns().deselect();
					dt.cells().deselect();
				}
			},
		],
		"columnDefs":[
			{
				"targets":[0, 6, 7, 8, 9],
				"className": "text-center",
				"orderable":false
			},
			{
				"targets":[4],
				"className": "text-right",
			},
			{
				"targets":[5],
				"visible": false,
				"orderable":true,
				"searchable": true,
			},
			{"targets": [0], "className": 'noVis text-center'},
		],
        initComplete: function () {
            this.api().columns([3]).every( function () {
                var column = this;
                var select = jQuery('<select id="select_type"><option value=""><?php esc_html_e('Select', 'xsdatatables'); ?></option></select>')
					.appendTo("#xs_select.dataTables_length")
					.on( 'change', function () {
                        var val = jQuery.fn.dataTable.util.escapeRegex(
                            jQuery(this).val()
                        );
                        column
                            .search( val ? '^'+val+'$' : '', true, false )
                            .draw();
                    } );
				select.append( '<?php echo wp_kses(xsdatatables_table::all_type(), array('option' => array('value' => array()))); ?>' );
            } );
	        this.api().columns([6]).every( function () {
	            var column = this;
	            var select = jQuery('<select id="select_status"><option value=""><?php esc_html_e('Select', 'xsdatatables'); ?></option></select>')
					.appendTo("#xs_select.dataTables_length")
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
		"deferRender": true,
		"responsive": true,
        "language": {
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
			buttons: {colvis: '<?php esc_html_e('Column', 'xsdatatables'); ?>'}
        },
		"paging":   true,
		"searching": true,
		"order":[],
	});

	jQuery('#table_add_xs').click(function(){
		jQuery('#new_field').show();
		jQuery('#xs_dom').hide();
		jQuery('#table_category').attr('disabled', true);
		jQuery('#table_headercolor').attr('disabled', true);
		jQuery('#table_bodycolor').attr('disabled', true);
		jQuery('#tableModal').modal('show');
		jQuery('#tableForm')[0].reset();
		jQuery('.modal-title').html("<b><?php esc_html_e('Add Table', 'xsdatatables'); ?></b>");
		jQuery('#action').val('table_add_xs');
		jQuery('#_xsnonce').val('<?php echo esc_attr($_xsnonce); ?>');
		jQuery('#submit_button').val('Add');
	});

	jQuery(document).on('click', '.table_edit_status_xs', function(){
		var id = jQuery(this).attr('id');
		var status = jQuery(this).data("status");
		var xsnonce = jQuery(this).data("xsnonce");
		var action = 'table_edit_status_xs';
		if(confirm("<?php esc_html_e('Are you sure you want to change the status of this table?', 'xsdatatables'); ?>")){
			jQuery.ajax({
				url:"<?php echo esc_url(admin_url('admin-ajax.php')); ?>",
				method:"POST",
				data:{table_id:id, status:status, action:action, _xsnonce:xsnonce},
				success:function(data){
					jQuery('#alert_table').fadeIn().html(data);
					jQuery("#xs-select-all").prop('checked', false);
					dataRecords.ajax.reload(null, false);
				}
			})
		}else{
			return false;
		}
	});

	jQuery("#xs_table").on('click', '.table_edit_xs', function(){
		jQuery('#new_field').hide();
		jQuery('#xs_dom').show();
		jQuery('#column_number').attr('required', false);
		jQuery('#column_type').attr('required', false);
		var id = jQuery(this).attr("id");
		var xsnonce = jQuery(this).data("xsnonce");
		var action = 'table_single_xs';
		jQuery.ajax({
			url:"<?php echo esc_url(admin_url('admin-ajax.php')); ?>",
			method:"POST",
			data:{table_id:id, action:action, _xsnonce:xsnonce},
			dataType:"json",
			success:function(data){
				jQuery('#tableModal').modal('show');
				jQuery('#table_name').val(data.table_name);
				jQuery('#table_type').val(data.table_type);
				jQuery('#table_footer').val(data.table_footer);
				jQuery('#table_button').val(data.table_button);
				jQuery('#table_length').val(data.table_length);
				jQuery('#table_order').val(data.table_order);
				jQuery('#table_sort').val(data.table_sort);
				jQuery('#table_pagination').val(data.table_pagination);
				jQuery('#table_responsive').val(data.table_responsive);
				jQuery('#table_headerbackground').val(data.table_headerbackground);
				jQuery('#table_headercolor').val(data.table_headercolor);
				jQuery('#table_bodybackground').val(data.table_bodybackground);
				jQuery('#table_bodycolor').val(data.table_bodycolor);
				if(data.table_type == 'default' || data.table_type == 'page'){
					jQuery('#table_category').val('').attr('disabled', true);
				}else{
					jQuery('#table_category').val(data.table_category).attr('disabled', false);
				}
				if(data.table_type == 'default'){
					jQuery('#table_serverside').val(data.table_serverside).attr('disabled', false);
				}else{
					jQuery('#table_serverside').val('no').attr('disabled', true);
				}
				if(data.table_headerbackground == 'default'){
					jQuery('#table_headercolor').attr('disabled', true).val('#ffffff');
				}else{
					jQuery('#table_headercolor').attr('disabled', false);
				}
				if(data.table_bodybackground == 'default'){
					jQuery('#table_bodycolor').attr('disabled', true).val('#ffffff');
				}else{
					jQuery('#table_bodycolor').attr('disabled', false);
				}
				<?php
				foreach(array('l','c','B','f','r','t','i','p') as $dome){
					?>
					if(data.<?php echo esc_attr('table_dom_'.$dome); ?> == '<?php echo esc_attr($dome); ?>'){
						jQuery('#<?php echo esc_attr($dome); ?>').prop("checked", true);
					}else{
						jQuery('#<?php echo esc_attr($dome); ?>').prop("checked", false);
					}
					<?php
				}
				?>
				jQuery('#table_limit').val(data.table_limit);
				jQuery('#table_note').val(data.table_note);
				jQuery('#table_id').val(id);
				jQuery('#_xsnonce').val(xsnonce);
				jQuery('.modal-title').html("<b><?php esc_html_e('Edit Table', 'xsdatatables'); ?></b>");
				jQuery('#action').val('table_edit_xs');
				jQuery('#submit_button').val('Edit');
			}
		})
	});

	jQuery("#xs_table").on('click', '.table_delete_xs', function(){
		var id = jQuery(this).data('id');
		var xsnonce = jQuery(this).data("xsnonce");
		var action = "table_delete_xs";
		if(confirm("<?php esc_html_e('Are you sure you want to delete this table?', 'xsdatatables'); ?>")) {
			jQuery.ajax({
				url:"<?php echo esc_url(admin_url('admin-ajax.php')); ?>",
				method:"POST",
				data:{table_id:id, action:action, _xsnonce:xsnonce},
				success:function(data){
					jQuery('#alert_table').fadeIn().html(data);
					jQuery("#xs-select-all").prop('checked', false);
					dataRecords.ajax.reload(null, false);
				}
			})
		} else {
			return false;
		}
	});	

	jQuery('#xs-select-all').on('click', function(){
		var rows = dataRecords.rows({ 'search': 'applied' }).nodes();
		jQuery('input[type="checkbox"]', rows).prop('checked', this.checked);
	});

	jQuery('#xs_table tbody').on('change', 'input[type="checkbox"]', function(){
		var isChecked = jQuery(this).prop("checked");
		var isHeaderChecked = jQuery("#xs-select-all").prop("checked");
		if (isChecked == false && isHeaderChecked)
			jQuery("#xs-select-all").prop('checked', isChecked);
		else {
			jQuery('#xs_table tr:has(td)').find('input[type="checkbox"]').each(function() {
				if (jQuery(this).prop("checked") == false)
				isChecked = false;
			});
			jQuery("#xs-select-all").prop('checked', isChecked);
		}
	});

	jQuery('#table_multi_active_xs').click(function(){
	    if(confirm("<?php esc_html_e('Are you sure you want to change the status of this table?', 'xsdatatables'); ?>")){
	        var id = [];
	        var action = "table_multi_active_xs";
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
	                data:{table_id:id, action:action, _xsnonce:"<?php echo esc_attr($_xsnonce); ?>"},
	                success:function(data){
	                    jQuery('#alert_table').fadeIn().html(data);
	                    jQuery("#xs-select-all").prop('checked', false);
	                    dataRecords.ajax.reload(null, false);
	                }
	            });
	        }
	    }else{
	        return false;
	    }
	});

	jQuery('#table_multi_inactive_xs').click(function(){
	    if(confirm("<?php esc_html_e('Are you sure you want to change the status of this table?', 'xsdatatables'); ?>")){
	        var id = [];
	        var action = "table_multi_inactive_xs";
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
	                data:{table_id:id, action:action, _xsnonce:"<?php echo esc_attr($_xsnonce); ?>"},
	                success:function(data){
	                    jQuery('#alert_table').fadeIn().html(data);
	                    jQuery("#xs-select-all").prop('checked', false);
	                    dataRecords.ajax.reload(null, false);
	                }
	            });
	        }
	    }else{
	        return false;
	    }
	});

	jQuery('#table_multi_delete_xs').click(function(){
	    if(confirm("<?php esc_html_e('Are you sure you want to delete this table?', 'xsdatatables'); ?>")){
	        var id = [];
	        var action = "table_multi_delete_xs";
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
	                data:{table_id:id, action:action, _xsnonce:"<?php echo esc_attr($_xsnonce); ?>"},
	                success:function(data){
	                    jQuery('#alert_table').fadeIn().html(data);
	                    jQuery("#xs-select-all").prop('checked', false);
	                    dataRecords.ajax.reload(null, false);
	                }
	            });
	        }
	    }else{
	        return false;
	    }
	});

	jQuery("#tableModal").on('submit','#tableForm', function(event){
		event.preventDefault();
		jQuery('#submit_button').attr('disabled','disabled');
		var formData = jQuery(this).serialize();
		jQuery.ajax({
			url:"<?php echo esc_url(admin_url('admin-ajax.php')); ?>",
			method:"POST",
			data:formData,
			success:function(data){				
				jQuery('#tableForm')[0].reset();
				jQuery('#tableModal').modal('hide');				
				jQuery('#submit_button').attr('disabled', false);
				jQuery('#alert_table').fadeIn().html(data);
				jQuery("#xs-select-all").prop('checked', false);
				dataRecords.ajax.reload(null, false);
			}
		})
	});

	jQuery('#table_type').change(function(){
		var table_type = jQuery('#table_type').val();
		if(table_type == '' || table_type == 'default' || table_type == 'page'){
			jQuery('#table_category').attr('disabled', true);
		}else{
			jQuery('#table_category').attr('disabled', false);
		}
		if(table_type == 'default'){
			jQuery('#table_serverside').attr('disabled', false);
		}else{
			jQuery('#table_serverside').attr('disabled', true).val('no');
		}
	});

	jQuery('#table_serverside').change(function(){
		var table_serverside = jQuery('#table_serverside').val();
		if(table_serverside == 'yes'){
			jQuery('#table_limit').val('-1');
		}
	});

	jQuery('#table_limit').change(function(){
		var table_limit = jQuery('#table_limit').val();
		if(table_limit >= 0){
			jQuery('#table_serverside').val('no');
		}
	});

	jQuery('#table_headerbackground').change(function(){
		var table_headerbackground = jQuery('#table_headerbackground').val();
		if(table_headerbackground == 'default'){
			jQuery('#table_headercolor').attr('disabled', true);
		}else{
			jQuery('#table_headercolor').attr('disabled', false);
		}
		
	});

	jQuery('#table_bodybackground').change(function(){
		var table_bodybackground = jQuery('#table_bodybackground').val();
		if(table_bodybackground == 'default'){
			jQuery('#table_bodycolor').attr('disabled', true);
		}else{
			jQuery('#table_bodycolor').attr('disabled', false);
		}
		
	});
});
</script>