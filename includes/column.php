<?php

defined( 'ABSPATH' ) || exit;

global $table_id, $table_type, $_xsnonce;

check_ajax_referer( 'xs-table'.$table_id, '_xsnonce' );
check_admin_referer( 'xs-table'.$table_id, '_xsnonce' );
wp_enqueue_script('jquery-ui-sortable');

?>
<body>
<div class="container_xs">
	<span id="alert_column"></span>
	<div class="panel panel-default">
		<div class="panel-heading">
			<div class="row">
				<div class="panel_heading_left">
					<h5 class="panel-title"><b><?php echo esc_attr(xsdatatables_table::name($table_id)); ?></b></h5>
				</div>
				<div class="panel_heading_right" style="text-align:right;">
				<button type="button" name="add" id="column_add_xs" data-toggle="modal" data-target="#columnModal" class="btn btn-success btn-xs"><span class="dashicons dashicons-plus"></span></button>
				<button type="button" name="column_multi_delete_xs" id="column_multi_delete_xs" class="btn btn-danger btn-xs"><span class="dashicons dashicons-no"></span></button>
				</div>
			</div>
		</div>
		<div style="overflow-x:auto;padding: 10px;">
			<table class="table table-bordered table-striped" style="margin-bottom: 0px;" id="xsdatatable_column">
				<thead style="cursor:auto;height:42px;">
					<tr>
						<th style="text-align:center;width:54px;"><input name="xs_select_all" id="xs-select-all" type="checkbox" /></th>
						<th style="width:1%;"><?php esc_html_e('#', 'xsdatatables'); ?></th>
						<th><?php esc_html_e('Name', 'xsdatatables'); ?></th>
						<th style="width:10%;"><?php esc_html_e('Filter', 'xsdatatables'); ?></th>
						<th style="width:10%;"><?php esc_html_e('Hidden', 'xsdatatables'); ?></th>
						<th style="width:10%;"><?php esc_html_e('Width(%)', 'xsdatatables'); ?></th>
						<th style="width:10%;"><?php esc_html_e('Align', 'xsdatatables'); ?></th>
						<th style="width:10%;"><?php esc_html_e('Type', 'xsdatatables'); ?></th>
						<th style="text-align:center;width:1%;"><?php esc_html_e('Status', 'xsdatatables'); ?></th>
						<th style="text-align:center;width:1%;"><?php esc_html_e('Edit', 'xsdatatables'); ?></th>
						<th style="text-align:center;width:1%;"><?php esc_html_e('Delete', 'xsdatatables'); ?></th>
					</tr>
				</thead>
				<tbody style="cursor: all-scroll;" id="xscolumn_body"></tbody>
			</table>
			<div id="loader" class="xs-dual-ring hidden overlay"></div>
		</div>
		<div class="panel-footer" style="text-align:center;">
			<button type="button" name="column_multi_active_xs" id="column_multi_active_xs" class="btn btn-success btn-xs"><?php esc_html_e('Active', 'xsdatatables'); ?></button>
			<button type="button" name="column_multi_inactive_xs" id="column_multi_inactive_xs" class="btn btn-danger btn-xs"><?php esc_html_e('Inactive', 'xsdatatables'); ?></button>
			<button type="button" name="column_multi_duplicate_xs" id="column_multi_duplicate_xs" class="btn btn-info btn-xs"><?php esc_html_e('Duplicate', 'xsdatatables'); ?></button>
		</div>
	</div>
</div>
<div class="modal-xs">
	<div id="columnModal" class="modal fade">
		<div class="modal-dialog modal-dialog-scrollable">
			<form method="post" id="column_form">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title"><b><?php esc_html_e('Add Column', 'xsdatatables'); ?></b></h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class="modal-body">
						<div class="form-group">
							<label><?php esc_html_e('Name', 'xsdatatables'); ?></label>
							<input type="text" name="column_names" id="column_names" class="form-control" required />
						</div>
						<div class="row">
							<div class="col-md-6">
								<label><?php esc_html_e('Filter', 'xsdatatables'); ?></label>
								<select name="column_filters" id="column_filters" class="form-control" required >
									<option value="no">No</option>
									<option value="yes">Yes</option>
								</select>
							</div>
							<div class="col-md-6">
								<label><?php esc_html_e('Position', 'xsdatatables'); ?></label>
								<select name="column_position" id="column_position" class="form-control" required >
									<option value="default">Default</option>
									<option value="footer">Footer</option>
									<option value="hidden">Hidden</option>
								</select>
							</div>
						</div>
						<div class="row">
							<div class="col-md-6">
								<label><?php esc_html_e('Hidden', 'xsdatatables'); ?></label>
								<select name="column_hidden" id="column_hidden" class="form-control" required >
									<option value="no">No</option>
									<option value="yes">Yes</option>
								</select>
							</div>
							<div class="col-md-6">
								<label><?php esc_html_e('Total for column', 'xsdatatables'); ?></label>
								<select name="column_total" id="column_total" class="form-control" required >
									<option value="no">No</option>
									<option value="yes">Yes</option>
								</select>
							</div>
						</div>
						<div class="row">
							<div class="col-md-6">
								<label><?php esc_html_e('Width (%)', 'xsdatatables'); ?></label>
								<input type="number" name="column_width" id="column_width" min="0" max="99" value ="0" class="form-control" required/>
							</div>
							<div class="col-md-6">
								<label><?php esc_html_e('Align', 'xsdatatables'); ?></label>
								<select name="column_align" id="column_align" class="form-control" required >
									<option value="left">Left</option>
									<option value="center">Center</option>
									<option value="right">Right</option>
								</select>
							</div>
						</div>
						<div class="row">
							<div class="col-md-6">
								<label><?php esc_html_e('Orderable', 'xsdatatables'); ?></label>
								<select name="column_orderable" id="column_orderable" class="form-control" required >
									<option value="no">No</option>
									<option value="yes" selected>Yes</option>
								</select>
							</div>
							<div class="col-md-6">
								<label><?php esc_html_e('Searchable', 'xsdatatables'); ?></label>
								<select name="column_searchable" id="column_searchable" class="form-control" required >
									<option value="no">No</option>
									<option value="yes" selected>Yes</option>
								</select>
							</div>
						</div>
						<div class="row">
							<?php if($table_type == 'product'){ ?>
							<div class="col-md-6">
								<label><?php esc_html_e('Column Type', 'xsdatatables'); ?></label>
								<select name="column_name" id="column_name" class="form-control" required >
								<?php echo wp_kses(xsdatatables_woocommerce::product_type(), array('option' => array('value' => array()))); ?>
								</select>
							</div>
							<?php }elseif($table_type == 'order'){ ?>
							<div class="col-md-6">
								<label><?php esc_html_e('Column Type', 'xsdatatables'); ?></label>
								<select name="column_name" id="column_name" class="form-control" required >
								<?php echo wp_kses(xsdatatables_woocommerce::order_type(), array('option' => array('value' => array()))); ?>
								</select>
							</div>
							<?php }elseif($table_type == 'post'){ ?>
							<div class="col-md-6">
								<label><?php esc_html_e('Column Type', 'xsdatatables'); ?></label>
								<select name="column_name" id="column_name" class="form-control" required >
								<?php echo wp_kses(xsdatatables_post::poss('post'), array('option' => array('value' => array()))); ?>
								</select>
							</div>
							<?php }elseif($table_type == 'page'){ ?>
							<div class="col-md-6">
								<label><?php esc_html_e('Column Type', 'xsdatatables'); ?></label>
								<select name="column_name" id="column_name" class="form-control" required >
								<?php echo wp_kses(xsdatatables_post::poss('page'), array('option' => array('value' => array()))); ?>
								</select>
							</div>
							<?php }else{ ?>
							<div class="col-md-6">
								<label><?php esc_html_e('Column Type', 'xsdatatables'); ?></label>
								<select name="column_type" id="column_type" class="form-control" >
									<option value="">textarea</option>
									<option value="text">text</option>
									<option value="url">url</option>
									<option value="date">date</option>
									<option value="datetime-local">datetime-local</option>
									<option value="month">month</option>
									<option value="week">week</option>
									<option value="time">time</option>
									<option value="number">number</option>
									<option value="color">color</option>
									<option value="tel">tel</option>
									<option value="select">select</option>
									<option value="shortcode">shortcode</option>
									<option value="index">index</option>
									<option value="html">html</option>
								</select>
							</div>
							<?php } ?>
							<div class="col-md-6">
								<label><?php esc_html_e('Custom Filter', 'xsdatatables'); ?></label>
								<select name="column_filter" id="column_filter" class="form-control" required >
									<option value="no" selected>No</option>
									<option value="yes">Yes</option>
								</select>
							</div>
						</div>
						<div class="row">
							<div class="col-md-6">
								<label><?php esc_html_e('Column Background', 'xsdatatables'); ?></label>
								<select name="column_background" id="column_background" class="form-control" required >
									<option value="default" selected>Default</option>
									<option value="custom">Custom</option>
								</select>
							</div>
							<div class="col-md-6">
								<label><?php esc_html_e('Column Color', 'xsdatatables'); ?></label>
				      			<input type="color" name="column_color" id="column_color" class="form-control" value="#ffffff" />
							</div>
						</div>
						<div class="form-group" id="custom_filter" style="text-align:center;">
							<label><?php esc_html_e('Dynamic Field', 'xsdatatables'); ?></label>
			      			<table style="width:100%;" id="dynamic_filter"></table>
						</div>
						<div class="form-group" id="custom_type" style="text-align:center; display: none;">
							<label><?php esc_html_e('Create a drop-down list', 'xsdatatables'); ?></label>
			      			<table style="width:100%;" id="dynamic_type"></table>
						</div>
					</div>
					<div class="modal-footer">
						<input type="hidden" name="table_id" id="table_id" value="<?php echo esc_attr($table_id); ?>"/>
						<input type="hidden" name="column_id" id="column_id"/>
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
	var table_id = <?php echo esc_attr($table_id); ?>;
	if(table_id != ''){
		load_data(table_id);
		jQuery('#xscolumn_body').sortable({
			placeholder : "ui-state-highlight",
			update : function(event, ui){
				var table_id_array = new Array();
				jQuery('#xsdatatable_column tbody tr').each(function(){
					table_id_array.push(jQuery(this).attr('id'));
				});
				jQuery.ajax({
					url:"<?php echo esc_url(admin_url('admin-ajax.php')); ?>",
					method:"POST",
					data:{table_id_array:table_id_array, action:'column_update_order_xs', table_id:table_id, _xsnonce:"<?php echo esc_attr($_xsnonce); ?>"},
					success:function(){
						load_data(table_id);
					}
				})
			}
		});

		jQuery(document).on('submit','#column_form', function(event){
			event.preventDefault();
			jQuery('#submit_button').attr('disabled','disabled');
			var form_data = jQuery(this).serialize();
			jQuery.ajax({
				url:"<?php echo esc_url(admin_url('admin-ajax.php')); ?>",
				method:"POST",
				data:form_data,
				success:function(data){
					jQuery('#column_form')[0].reset();
					jQuery('#columnModal').modal('hide');
					jQuery('#alert_column').fadeIn().html(data);
					jQuery('#submit_button').attr('disabled', false);
					load_data(table_id);
				}
			})
		});

		jQuery('#column_add_xs').click(function(){
			jQuery('#columnModal').modal('show');
			jQuery('#column_form')[0].reset();
			jQuery('#column_position').attr('disabled', true);
			jQuery('#column_filter').attr('disabled', true);
			jQuery('#column_color').attr('disabled', true);
			jQuery('#custom_filter').hide();
			jQuery('#dynamic_filter').html('');
			jQuery('#custom_type').hide();
			jQuery('#dynamic_type').html('');
			add_dynamic_filter(1);
			add_dynamic_type(1);
			jQuery('.modal-title').html("<b>Add Column</b>");
			jQuery('#submit_button').val('Add');
			jQuery('#action').val('column_add_xs');
			jQuery('#_xsnonce').val('<?php echo esc_attr($_xsnonce); ?>');
		});

		jQuery(document).on('click', '.column_edit_status_xs', function(){
			var id = jQuery(this).attr('id');
			var status = jQuery(this).data("status");
			var xsnonce = jQuery(this).data("xsnonce");
			var action = 'column_edit_status_xs';
			if(confirm("<?php esc_html_e('Are you sure you want to change the status of this column?', 'xsdatatables'); ?>")){
				jQuery.ajax({
					url:"<?php echo esc_url(admin_url('admin-ajax.php')); ?>",
					method:"POST",
					data:{column_id:id, status:status, action:action, table_id:table_id, _xsnonce:xsnonce},
					success:function(data){
						jQuery('#alert_column').fadeIn().html(data);
						load_data(table_id);
					}
				})
			}else{
				return false;
			}
		});

		jQuery(document).on('click', '.column_edit_xs', function(){
			var id = jQuery(this).attr("id");
			var xsnonce = jQuery(this).data("xsnonce");
			var action = 'column_single_xs';
			jQuery.ajax({
				url:"<?php echo esc_url(admin_url('admin-ajax.php')); ?>",
				method:"POST",
				data:{column_id:id, action:action, table_id:table_id, _xsnonce:xsnonce},
				dataType:"json",
				success:function(data){
					jQuery('#columnModal').modal('show');
					jQuery('#column_names').val(data.column_names);
					jQuery('#column_filters').val(data.column_filters);
					jQuery('#column_position').val(data.column_position);
					jQuery('#column_hidden').val(data.column_hidden);
					jQuery('#column_total').val(data.column_total);
					jQuery('#column_width').val(data.column_width);
					jQuery('#column_align').val(data.column_align);
					jQuery('#column_orderable').val(data.column_orderable);
					jQuery('#column_searchable').val(data.column_searchable);
					jQuery('#column_type').val(data.column_type);
					jQuery('#column_name').val(data.column_name);
					jQuery('#column_filter').val(data.column_filter);
					jQuery('#column_background').val(data.column_background);
					jQuery('#column_color').val(data.column_color);
					jQuery('#dynamic_filter').html(data.column_customfilter);
					jQuery('#dynamic_type').html(data.column_customtype);
					if(data.column_filters == 'no'){
						jQuery('#column_position').attr('disabled', true);
						jQuery('#column_filter').attr('disabled', true);
						jQuery('#custom_filter').hide();
					}else{
						jQuery('#column_position').attr('disabled', false);
						jQuery('#column_filter').attr('disabled', false);
						if(data.column_filter == 'no'){
							jQuery('#custom_filter').hide();
						}else{
							jQuery('#custom_filter').show();
						}
					}
					if(data.column_type == 'select'){
						jQuery('#custom_type').show();
					}else{
						jQuery('#custom_type').hide();
					}
					if(data.column_background == 'default'){
						jQuery('#column_color').attr('disabled', true).val('#ffffff');
					}else{
						jQuery('#column_color').attr('disabled', false);
					}
					jQuery('.modal-title').html("<b><?php esc_html_e('Edit Column', 'xsdatatables'); ?></b>");
					jQuery('#column_id').val(id);
					jQuery('#_xsnonce').val(xsnonce);
					jQuery('#action').val("column_edit_xs");
					jQuery('#submit_button').val("Edit");
				}
			})
		});

		jQuery(document).on('click', '.column_delete_xs', function(){
			var id = jQuery(this).data('id');
			var xsnonce = jQuery(this).data("xsnonce");
			if(confirm("<?php esc_html_e('Are you sure you want to delete this column?', 'xsdatatables'); ?>")){
				jQuery.ajax({
					url:"<?php echo esc_url(admin_url('admin-ajax.php')); ?>",
					method:"POST",
					data:{column_id:id, action:'column_delete_xs', table_id:table_id, _xsnonce:xsnonce},
					success:function(data){
						jQuery('#alert_column').fadeIn().html(data);
						load_data(table_id);
					}
				});
			}
		});

		jQuery('#xs-select-all').click(function() {
			var isChecked = jQuery(this).prop("checked");
			jQuery('#xsdatatable_column tr:has(td)').find('input[type="checkbox"]').prop('checked', isChecked);
		});

		jQuery('#xsdatatable_column tbody').on('change', 'input[type="checkbox"]', function(){
			var isChecked = jQuery(this).prop("checked");
			var isHeaderChecked = jQuery("#xs-select-all").prop("checked");
			if (isChecked == false && isHeaderChecked)
				jQuery("#xs-select-all").prop('checked', isChecked);
			else {
				jQuery('#xsdatatable_column tr:has(td)').find('input[type="checkbox"]').each(function() {
					if (jQuery(this).prop("checked") == false)
					isChecked = false;
				});
				jQuery("#xs-select-all").prop('checked', isChecked);
			}
		});

		jQuery('#column_multi_active_xs').click(function(){
		    if(confirm("<?php esc_html_e('Are you sure you want to change the status of this column?', 'xsdatatables'); ?>")){
		        var id = [];
		        var action = "column_multi_active_xs";
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
		                data:{column_id:id, action:action, table_id:table_id, _xsnonce:"<?php echo esc_attr($_xsnonce); ?>"},
		                success:function(data){
		                    jQuery('#alert_column').fadeIn().html(data);
		                    jQuery("#xs-select-all").prop('checked', false);
		                    load_data(table_id);
		                }
		            });
		        }
		    }else{
		        return false;
		    }
		});

		jQuery('#column_multi_inactive_xs').click(function(){
		    if(confirm("<?php esc_html_e('Are you sure you want to change the status of this column?', 'xsdatatables'); ?>")){
		        var id = [];
		        var action = "column_multi_inactive_xs";
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
		                data:{column_id:id, action:action, table_id:table_id, _xsnonce:"<?php echo esc_attr($_xsnonce); ?>"},
		                success:function(data){
		                    jQuery('#alert_column').fadeIn().html(data);
		                    jQuery("#xs-select-all").prop('checked', false);
		                    load_data(table_id);
		                }
		            });
		        }
		    }else{
		        return false;
		    }
		});

		jQuery('#column_multi_duplicate_xs').click(function(){
		    if(confirm("<?php esc_html_e('Are you sure you want to duplicate this column?', 'xsdatatables'); ?>")){
		        var id = [];
		        var action = "column_multi_duplicate_xs";
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
		                data:{column_id:id, action:action, table_id:table_id, _xsnonce:"<?php echo esc_attr($_xsnonce); ?>"},
		                success:function(data){
		                    jQuery('#alert_column').fadeIn().html(data);
		                    jQuery("#xs-select-all").prop('checked', false);
		                    load_data(table_id);
		                }
		            });
		        }
		    }else{
		        return false;
		    }
		});

		jQuery('#column_multi_delete_xs').click(function(){
		    if(confirm("<?php esc_html_e('Are you sure you want to delete this column?', 'xsdatatables'); ?>")){
		        var id = [];
		        var action = "column_multi_delete_xs";
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
		                data:{column_id:id, action:action, table_id:table_id, _xsnonce:"<?php echo esc_attr($_xsnonce); ?>"},
		                success:function(data){
		                    jQuery('#alert_column').fadeIn().html(data);
		                    jQuery("#xs-select-all").prop('checked', false);
		                    load_data(table_id);
		                }
		            });
		        }
		    }else{
		        return false;
		    }
		});

		jQuery('#column_filters').change(function(){
			var column_filters = jQuery('#column_filters').val();
			if(column_filters == 'no'){
				jQuery('#column_position').attr('disabled', true);
				jQuery('#column_filter').attr('disabled', true);
				jQuery('#custom_filter').hide();
			}else{
				jQuery('#column_position').attr('disabled', false);
				jQuery('#column_filter').attr('disabled', false);
				var column_filter = jQuery('#column_filter').val();
				if(column_filter == 'yes'){
					jQuery('#custom_filter').show();
				}
			}
			
		});

		jQuery('#column_type').change(function(){
			var column_type = jQuery('#column_type').val();
			if(column_type == 'select'){
				jQuery('#custom_type').show();
			}else{
				jQuery('#custom_type').hide();
			}
		});

		jQuery('#column_background').change(function(){
			var column_background = jQuery('#column_background').val();
			if(column_background == 'default'){
				jQuery('#column_color').attr('disabled', true);
			}else{
				jQuery('#column_color').attr('disabled', false);
			}
			
		});

		jQuery('#column_filter').change(function(){
			var column_filter = jQuery('#column_filter').val();
			if(column_filter == 'no'){
				jQuery('#custom_filter').hide();
			}else{
				jQuery('#custom_filter').show();
			}
		});
	}
	var count = 1;
	function add_dynamic_filter(count){
		var button = '';
		if(count > 1){
			button = '<button type="button" name="remove" id="'+count+'" class="btn btn-danger btn-xs remove">x</button>';
		}else{
			button = '<button type="button" name="add_more" id="add_more" class="btn btn-success btn-xs">+</button>';
		}
		output = '<tr id="row'+count+'">';
		output += '<td style="padding:5px"><input type="text" name="column_customfilter[]" placeholder="option" class="form-control name_list" /></td>';
		output += '<td style="width:45px;text-align:center;">'+button+'</td></tr>';
		jQuery('#dynamic_filter').append(output);
	}
	function add_dynamic_type(count){
		var button = '';
		if(count > 1){
			button = '<button type="button" name="remove_option" id="'+count+'" class="btn btn-danger btn-xs remove_option">x</button>';
		}else{
			button = '<button type="button" name="add_option" id="add_option" class="btn btn-success btn-xs">+</button>';
		}
		output = '<tr id="rows'+count+'">';
		output += '<td style="padding:5px"><input type="text" name="column_customtype[]" placeholder="option" class="form-control name_list" /></td>';
		output += '<td style="width:45px;text-align:center;">'+button+'</td></tr>';
		jQuery('#dynamic_type').append(output);
	}
	jQuery(document).on('click', '#add_more', function(){
		count = count + 1;
		add_dynamic_filter(count);
	});

	jQuery(document).on('click', '.remove', function(){
		var row_id = jQuery(this).attr("id");
		jQuery('#row'+row_id).remove();
	});

	jQuery(document).on('click', '#add_option', function(){
		count = count + 1;
		add_dynamic_type(count);
	});

	jQuery(document).on('click', '.remove_option', function(){
		var row_id = jQuery(this).attr("id");
		jQuery('#rows'+row_id).remove();
	});
	function load_data(table_id){
		jQuery.ajax({
			url:"<?php echo esc_url(admin_url('admin-ajax.php')); ?>",
			method:"POST",
			data:{action:'column_getdata_xs', table_id:table_id, _xsnonce:"<?php echo esc_attr($_xsnonce); ?>"},
			dataType:'json',
			beforeSend:function(){
				jQuery('#xscolumn_body').hide();
				jQuery('#loader').removeClass('hidden');
			},
			success:function(data){
				var html = '';
				for(var count = 0; count < data.length; count++){
					if(data[count].column_status == 'active'){
						status = '<button type="button" name="column_edit_status_xs" class="btn btn-success btn-xs column_edit_status_xs" style="min-width: 70.25px;" id="'+data[count].id+'" data-status="'+data[count].column_status+'" data-xsnonce="'+data[count].xsnonce+'">Active</button>';
					}else{
						status = '<button type="button" name="column_edit_status_xs" class="btn btn-danger btn-xs column_edit_status_xs" id="'+data[count].id+'" data-status="'+data[count].column_status+'" data-xsnonce="'+data[count].xsnonce+'">Inactive</button>';
					}
					html += '<tr id="'+data[count].id+'">';
					html += '<td style="text-align:center">'+'<input type="checkbox" name="column_id[]" id="'+data[count].id+'" value="'+data[count].id+'">'+'</td>';
					html += '<td>'+data[count].column_order+'</td>';
					html += '<td>'+data[count].column_names+'</td>';
					html += '<td>'+data[count].column_filters+'</td>';
					html += '<td>'+data[count].column_hidden+'</td>';
					html += '<td>'+data[count].column_width+'%'+'</td>';
					html += '<td>'+data[count].column_align+'</td>';
					html += '<td>'+data[count].column_type+'</td>';
					html += '<td style="text-align:center">'+status+'</td>';
					html += '<td style="text-align:center"><button type="button" name="column_edit_xs" class="btn btn-warning btn-xs column_edit_xs" id="'+data[count].id+'" data-xsnonce="'+data[count].xsnonce+'"><span class="dashicons dashicons-edit"></span></button></td>';
					html += '<td style="text-align:center"><button type="button" name="column_delete_xs" class="btn btn-danger btn-xs column_delete_xs" data-id="'+data[count].id+'" data-xsnonce="'+data[count].xsnonce+'"><span class="dashicons dashicons-no"></span></button></td>';
					html += '</tr>';
				}
				jQuery("#xs-select-all").prop('checked', false);
				jQuery('#loader').addClass('hidden');
				jQuery('#xscolumn_body').show().html(html);
			}
		})
	}
});
</script>