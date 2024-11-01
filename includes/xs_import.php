<?php

defined( 'ABSPATH' ) || exit;

$table_id = $_GET['table_id'];
check_ajax_referer( 'xs-table'.$table_id, '_xsnonce' );
check_admin_referer( 'xs-table'.$table_id, '_xsnonce' );
$_xsnonce = wp_create_nonce('xs-import'.$table_id);
$column_number = xsdatatables_column::rowCount($table_id);
$column_import = xsdatatables_column::import($table_id);
?>
<body>
	<div class="container_xs">
		<h1 style="text-align:center;"><?php esc_html_e('Import from a CSV, XLS, XLSX or JSON File', 'xsdatatables'); ?></h1></p>
		<span id="message"></span></p>
		<div class="panel panel-default">
			<div class="panel-heading">
				<h5 class="panel-title"><b><?php esc_html_e(xsdatatables_table::name($table_id), 'xsdatatables'); ?></b></h5>
			</div>
			<div class="panel-body">
				<form id="sample_form" method="POST" enctype="multipart/form-data" class="form-horizontal">
					<div class="row">
  						<div class="col-6 col-md-3">
							<label><b><?php esc_html_e('Type', 'xsdatatables'); ?></b></label>
							<select name="import_type" id="import_type" class="form-select" required >
								<option value=""><?php esc_html_e('Select', 'xsdatatables'); ?></option>
								<option value="replace">Replace existing table</option>
								<option value="append">Append rows to existing table</option>
							</select>
  						</div>
							<div class="col-6 col-md-3">
  							<label><b><?php esc_html_e('Number of Columns', 'xsdatatables'); ?></b></label>
  							<input type="number" name="column_number" id="column_number" min="1" max="20" value="1" class="form-control" required disabled/>
							</div>
							<div class="col-6 col-md-3">
  							<label><b><?php esc_html_e('Source', 'xsdatatables'); ?></b></label>
							<select name="import_source" id="import_source" class="form-select" required disabled>
								<option value="file">File</option>
							</select>
							</div>
  						<div class="col-6 col-md-3">
  							<label><b><?php esc_html_e('Select', 'xsdatatables'); ?></b></label>
  							<input type="file" class="form-control" name="file" id="file" disabled/>
							<select name="select_table" id="select_table" class="form-select" style="display:none">
								<option value=""><?php esc_html_e('Select', 'xsdatatables'); ?></option>
								<?php echo wp_kses(xsdatatables_import::table(), array('option' => array('value' => array()))); ?>
							</select>
  						</div>
					</div>
					<div class="form-group" style="margin: 15px 0px;text-align:center;">
						<input type="hidden" name="table_id" value="<?php echo esc_attr($table_id); ?>" />
						<input type="hidden" name="hidden_field" id="hidden_field" />
						<input type="hidden" name="_xsnonce" id="_xsnonce" value="<?php echo esc_attr($_xsnonce); ?>"/>
						<input type="submit" name="upload_file" id="upload_file" class="btn btn-secondary btn-xs" value="Load" disabled/><img id="spin_upload_file" src="<?php echo esc_url(admin_url('images/spinner-2x.gif')); ?>" alt="..." style="vertical-align:bottom; max-height: 30px; display:none">
					</div>
				</form>
				<div class="form-group" id="process_xs" style="display:none;margin: 15px 0px;">
					<div class="progress" style="margin-bottom:unset;">
						<div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuemin="0" aria-valuemax="100">
							<span id="process_data">0</span> - <span id="total_data">0</span>
						</div>
					</div>
				</div>
				<div class="table-responsive" id="process_area"></div>
			</div>
		</div>
	</div>
</body>
</html>
<script>
jQuery(document).ready(function(){
	jQuery('#file').on("change", function(){
		if(jQuery("#file").val() == ''){
			jQuery('#upload_file').attr('disabled', 'disabled');
		}else{
			jQuery('#upload_file').attr('disabled', false);
		}
	})
	jQuery('#select_table').on("change", function(){
		if(jQuery("#select_table").val() == ''){
			jQuery('#upload_file').attr('disabled', 'disabled');
		}else{
			jQuery('#upload_file').attr('disabled', false);
		}
	})
	jQuery('#import_source').on("change", function(){
		import_source = jQuery("#import_source").val();
		if(import_source == 'file'){
			jQuery('#select_table').hide().attr('disabled', 'disabled');
			jQuery('#file').show().attr('disabled', false);
		}
	})
	jQuery('#sample_form').on('submit', function(event){
		event.preventDefault();
		var import_type = jQuery("#import_type").val();
		var column_number = jQuery('#column_number').val();
		var form_data = new FormData(this);
		form_data.append("action", "upload_xs");
		jQuery.ajax({
			url:"<?php echo esc_url(admin_url('admin-ajax.php')); ?>",
			method:"POST",
			data:form_data,
			dataType:'json',
			contentType:false,
			cache:false,
			processData:false,
			beforeSend:function(){
				jQuery('#message').hide();
				jQuery('#upload_file').hide();
				jQuery('#spin_upload_file').show();
			},
			success:function(data){
				if(data.error){
					jQuery('#message').html('<div class="alert alert-danger">'+data.error+'</div>').show();
					jQuery('#upload_file').show().attr('disabled', 'disabled');
				}else{
					jQuery('#total_data').text(data.total_line);
					jQuery('#hidden_field').text(data.available);
					jQuery('#process_area').html(data.output).css('display', 'block');
					jQuery("#column_number").attr({"max":data.column_number});
					jQuery('#sample_form').css('display', 'none');
					const entries = Object.entries(column_data);
					for(const [key, value] of entries){
						delete column_data[key];
					}
					if(data.column_number < column_number){
						jQuery('#message').html('<div class="alert alert-danger"><?php esc_html_e('The number of columns specified does not match the number of columns in the import file.', 'xsdatatables'); ?></div>').show();
					}
				}
				jQuery('#spin_upload_file').hide();
			}
		});
	});

	var total_selection = 0;
	var column_data = [];
	if(import_type == 'append'){
		<?php 
		foreach($column_import['name'] as $name){
			?> var <?php echo esc_attr($name); ?> = 0; <?php
		}
		?>
	}else if(import_type == 'replace'){
		<?php 
		if(isset($_SESSION['column_number'])){
			foreach($_SESSION['column_number'] as $name){
				?> var <?php echo esc_attr('column_'.$name); ?> = 0; <?php
			} 
		}
		?>
	}

	jQuery(document).on('change', '.set_column_data', function(){
		var column_name = jQuery(this).val();
		if(column_name != ''){
			jQuery('#set_column_data option[value='+column_name+']').hide();
		}
		var column_numbers = jQuery(this).data('column_numbers');
		if(column_name in column_data){
			alert('You have already define '+column_name+ ' column');
			jQuery(this).val('');
			return false;
		}
		if(column_name != ''){
			const entries = Object.entries(column_data);
			for(const [key, value] of entries){
				if(value == column_numbers){
					delete column_data[key];
					jQuery('#set_column_data option[value='+key+']').show();
				}
			}
			column_data[column_name] = column_numbers;
		}else{
			const entries = Object.entries(column_data);
			for(const [key, value] of entries){
				if(value == column_numbers){
					delete column_data[key];
					jQuery('#set_column_data option[value='+key+']').show();
				}
			}
		}
		total_selection = Object.keys(column_data).length;
		var column_number = jQuery('#column_number').val();
		if(import_type == 'append'){
			var column_number = <?php echo esc_attr($column_number); ?>;
		}
		if(total_selection == column_number){
			var import_type = jQuery("#import_type").val();
			jQuery('#import_file').attr('disabled', false);
			if(import_type == 'append'){
				<?php
				foreach($column_import['name'] as $name){
					echo esc_attr($name); ?> = column_data.<?php echo esc_attr($name); ?>;<?php
				}
				?>
			}else if(import_type == 'replace'){
				<?php
				foreach(xsdatatables_column::import_replace(20) as $name => $names){
					echo esc_attr('column_'.$name); ?> = column_data.<?php echo esc_attr('column_'.$name); ?>;<?php 
				}
				?>
			}
		}else{
			jQuery('#import_file').attr('disabled', 'disabled');
		}
	});

	jQuery('#import_type').change(function(){
		var import_type = jQuery('#import_type').val();
		jQuery('#process_area').css('display', 'none');
		if(import_type == 'replace'){
			jQuery('#column_number').attr('disabled', false).val(1);
			jQuery('#import_source').attr('disabled', false);
			jQuery('#file').attr('disabled', false);
			jQuery('#select_table').attr('disabled', false);
		}else if(import_type == 'append'){
			jQuery('#column_number').attr('disabled', true).val(<?php echo esc_attr($column_number); ?>);
			jQuery('#import_source').attr('disabled', false);
			jQuery('#file').attr('disabled', false);
			jQuery('#select_table').attr('disabled', false);
		}else{
			jQuery('#column_number').attr('disabled', true).val(1);
			jQuery('#import_source').attr('disabled', 'disabled');
			jQuery('#file').attr('disabled', 'disabled');
			jQuery('#select_table').attr('disabled', 'disabled');
		}
	});

	jQuery('#column_number').change(function(){
		var column_number = jQuery('#column_number').val();
		if(total_selection == column_number){
			jQuery('#import_file').attr('disabled', false);
		}else{
			jQuery('#import_file').attr('disabled', true);
		}
	});

	var clear_timer;
	jQuery(document).on('click', '#import_file', function(event){
		event.preventDefault();
		if(confirm("<?php esc_html_e('This action is not reversable. Click Ok to continue. Click Cancel to abort.', 'xsdatatables'); ?>")){
			jQuery.ajax({
				url:"<?php echo esc_url(admin_url('admin-ajax.php')); ?>",
				method:"POST",
				dataType:'json',
				data:{action:'confirm_xs',table_id:'<?php echo esc_attr($table_id); ?>',_xsnonce:"<?php echo esc_attr($_xsnonce); ?>"},
				beforeSend:function(){
					jQuery('#file').attr('disabled', 'disabled');
					jQuery('#upload_file').attr('disabled', 'disabled');
					jQuery('#column_number').attr('disabled', 'disabled');
					jQuery('#import_type').attr('disabled', 'disabled');
					jQuery('#sample_form').css('display', 'block');
					jQuery('#import_file').attr('disabled', 'disabled').text('Importing').hide();
					jQuery('#process_area').css('display', 'none');
					jQuery('#spin_upload_file').show();
				},
				success:function(data){
					if(data.success){
						start_import();
						clear_timer = setInterval(get_import_data, 1000);
					}else{
						jQuery('#message').html('<div class="alert alert-danger">error</div>').show();
						jQuery('#file').val('').attr('disabled', false);
						jQuery('#column_number').val(1);
						jQuery('#import_type').attr('disabled', false).val('');
					}
				}
			})
		}else{
			return false;
		}
	});

	function start_import(){
		var import_type = jQuery('#import_type').val();
		var column_number = jQuery('#column_number').val();
		jQuery('#process_xs').css('display', 'block');
		if(import_type == 'append'){
			var data = {action:'import_xs',import_type:import_type,column_number:column_number,table_id:'<?php echo esc_attr($table_id); ?>',<?php echo esc_attr($column_import['post']); ?>,_xsnonce:"<?php echo esc_attr($_xsnonce); ?>"};
		}else if(import_type == 'replace'){
			var data = {action:'import_xs',import_type:import_type,column_number:column_number,table_id:'<?php echo esc_attr($table_id); ?>',<?php echo esc_attr(xsdatatables_column::import_post(20)); ?>,_xsnonce:"<?php echo esc_attr($_xsnonce); ?>"};
		}else{
			return;
		}
		jQuery.ajax({
			url:"<?php echo esc_url(admin_url('admin-ajax.php')); ?>",
			method:"POST",
			dataType:'json',
			data:data,
			success:function(){}
		})
	}

	function get_import_data(){
		var import_type = jQuery('#import_type').val();
		var column_number = jQuery('#column_number').val();
		jQuery.ajax({
			url:"<?php echo esc_url(admin_url('admin-ajax.php')); ?>",
			method:"POST",
			data:{action:'process_xs', table_id:<?php echo esc_attr($table_id); ?>, import_type:import_type, column_number:column_number, _xsnonce:"<?php echo esc_attr($_xsnonce); ?>"},
			dataType:'JSON',
			success:function(data){
				var available = jQuery('#hidden_field').text();
				jQuery('#process_data').text(data - available);
				var total_data = jQuery('#total_data').text();
				var width = Math.round(((data - available)/total_data)*100);
				jQuery('.progress-bar').css('width', width + '%');
				if(width >= 100){
					clearInterval(clear_timer);
					jQuery('#process_xs').css('display', 'none');
					jQuery('#message').html('<div class="alert alert-success"><?php esc_html_e('Data Successfully Imported', 'xsdatatables'); ?></div>').show();
					jQuery('#file').val('').attr('disabled', false);
					jQuery('#upload_file').show();
					jQuery('#spin_upload_file').hide();
					jQuery('#column_number').val(1);
					jQuery('#import_type').attr('disabled', false).val('');
					jQuery('.progress-bar').css('width', 0 + '%');
				}
			}
		})
	}
});
</script>