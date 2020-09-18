<div class="m-portlet m-portlet--bordered m-portlet--unair">
	<div class="m-portlet__head">
		<div class="m-portlet__head-caption">
			<div class="m-portlet__head-title">
				<h3 class="m-portlet__head-text">
					<?php echo translate('Logs'); $project_id = $project['id']; ?>
				</h3>
			</div>
		</div>
	</div>
	
	<?php if(file_exists(DATA_DIRECTORY.'/log_files/'.$project['id'].'/importInvoices.log')): ?>
		<h5 class="m-portlet__head-text" style="padding: 10px;"> Imported Invoice  ( Total Imported Invoices <font color="green"> <?php echo $log_details['invoice_success'];?> </font>   |  Generated errors <font color="red"> <?php echo $log_details['invoice_error'];?> </font> ) <button class="btn btn-danger" onclick="deleteLog('<?php echo  $project_id ;?>','importInvoices')" style="float: right;"> Reset Invoice Log </button> </h5>
		<div class="m-portlet__body">
			<div class="col-lg-12 col-md-12 col-sm-12">
				<textarea class="form-control log" id="m_clipboard_3" rows="8">
			<?php echo file_get_contents(DATA_DIRECTORY.'/log_files/'.$project['id'].'/importInvoices.log'); ?></textarea>
			</div>
		</div>
	<?php endif; ?>

	<?php if(file_exists(DATA_DIRECTORY.'/log_files/'.$project['id'].'/afas_setup_error.log')): ?>
		<h5 class="m-portlet__head-text" style="padding: 10px;"> Afas Setup errors <font color="red"> <?php echo $log_details['afas_setup_error'];?> </font> <button class="btn btn-danger" onclick="deleteLog('<?php echo  $project_id ;?>','afas_setup_error')" style="float: right;" > Reset AFAS Setup Error Log </button> </h5>
		<div class="m-portlet__body">
			<div class="col-lg-12 col-md-12 col-sm-12">
				<textarea class="form-control log" id="m_clipboard_3" rows="8">
			<?php echo file_get_contents(DATA_DIRECTORY.'/log_files/'.$project['id'].'/afas_setup_error.log'); ?></textarea>
			</div>
		</div>
	<?php endif; ?>

	<?php if(file_exists(DATA_DIRECTORY.'/log_files/'.$project['id'].'/importSalesEntry.log')): ?>
		<h5 class="m-portlet__head-text" style="padding: 10px;"> Imported SalesEntry  ( Total Imported SalesEntry <font color="green"> <?php echo $log_details['sales_entry_success'];?> </font>   |  Generated errors <font color="red"> <?php echo $log_details['sales_entry_error'];?> </font> ) <button class="btn btn-danger" onclick="deleteLog('<?php echo  $project_id ;?>','importSalesEntry')" style="float: right;"> Reset Sales Entry Log </button> </h5>
		<div class="m-portlet__body">
			<div class="col-lg-12 col-md-12 col-sm-12">
				<textarea class="form-control log" id="m_clipboard_3" rows="8">
			<?php echo file_get_contents(DATA_DIRECTORY.'/log_files/'.$project['id'].'/importSalesEntry.log'); ?></textarea>
			</div>
		</div>
	<?php endif; ?>

	<?php if(file_exists(DATA_DIRECTORY.'/log_files/'.$project['id'].'/importcustomers.log')): ?>
		<h5 class="m-portlet__head-text" style="padding: 10px;"> Imported customers  ( Total Imported customers <font color="green"> <?php echo $log_details['customer_success'];?> </font>   |  Generated errors <font color="red"> <?php echo $log_details['customer_error'];?> </font> ) <button class="btn btn-danger" onclick="deleteLog('<?php echo  $project_id ;?>','importcustomers')" style="float: right;"> Reset products Log </button> </h5>
		<div class="m-portlet__body">
			<div class="col-lg-12 col-md-12 col-sm-12">
				<textarea class="form-control log" id="m_clipboard_3" rows="8">
			<?php echo file_get_contents(DATA_DIRECTORY.'/log_files/'.$project['id'].'/importcustomers.log'); ?></textarea>
			</div>
		</div>
	<?php endif; ?>

	<?php if(file_exists(DATA_DIRECTORY.'/log_files/'.$project['id'].'/importarticles.log')): ?>
		<h5 class="m-portlet__head-text" style="padding: 10px;"> Imported articles ( Total Imported products <font color="green"> <?php echo $log_details['article_success'];?> </font> | Generated errors <font color="red"> <?php echo $log_details['article_error'];?> </font> ) <button class="btn btn-danger" onclick="deleteLog('<?php echo  $project_id ;?>','importarticles')" style="float: right;"> Reset products Log </button> </h5>
		<div class="m-portlet__body">
			<div class="col-lg-12 col-md-12 col-sm-12">
				<textarea class="form-control log" id="m_clipboard_3" rows="6">
			<?php echo file_get_contents(DATA_DIRECTORY.'/log_files/'.$project['id'].'/importarticles.log'); ?></textarea>
			</div>
		</div>
	<?php endif; ?>

	<?php if(file_exists(DATA_DIRECTORY.'/log_files/'.$project['id'].'/exportorders.log')): ?>
		<h5 class="m-portlet__head-text" style="padding: 10px;"> Import orders  ( Total Imported orders <font color="green"> <?php echo $log_details['orders_success'];?> </font>  |  Generated errors <font color="red"> <?php echo $log_details['orders_error'];?> </font> ) <button class="btn btn-danger" onclick="deleteLog('<?php echo  $project_id ;?>','exportorders')" style="float: right;"> Reset orders Log </button> </h5>
		<div class="m-portlet__body">
			<div class="col-lg-12 col-md-12 col-sm-12">
				<textarea class="form-control log" id="m_clipboard_3" rows="6">
			<?php echo file_get_contents(DATA_DIRECTORY.'/log_files/'.$project['id'].'/exportorders.log'); ?></textarea>
			</div>
		</div>
	<?php endif; ?>

	<?php if(file_exists(DATA_DIRECTORY.'/log_files/'.$project['id'].'/exact_setup.log')): ?>
		<h5 class="m-portlet__head-text" style="padding: 10px;"> Exact setup  ( Total successful execution <font color="green"> <?php echo $log_details['exact_success'];?> </font>  |  Generated errors <font color="red"> <?php echo $log_details['exact_error'];?> </font> ) <button class="btn btn-danger" onclick="deleteLog('<?php echo  $project_id ;?>','exact_setup')" style="float: right;"> Reset Exact Setup Log </button>  </h5>
		<div class="m-portlet__body">
			<div class="col-lg-12 col-md-12 col-sm-12">
				<textarea class="form-control log" id="m_clipboard_3" rows="6">
			<?php echo file_get_contents(DATA_DIRECTORY.'/log_files/'.$project['id'].'/exact_setup.log'); ?></textarea>
			</div>
		</div>
	<?php endif; ?>
	
	<?php if(file_exists(DATA_DIRECTORY.'/log_files/'.$project['id'].'/custom_cronjob.log')): ?>
		<h5 class="m-portlet__head-text" style="padding: 10px;"> Custom cronjob   <button class="btn btn-danger" onclick="deleteLog('<?php echo  $project_id ;?>','custom_cronjob')" style="float: right;"> Reset Custom cronjob Log </button> </h5>
		<div class="m-portlet__body">
			<div class="col-lg-12 col-md-12 col-sm-12">
				<textarea class="form-control log" id="m_clipboard_3" rows="6">
			<?php echo file_get_contents(DATA_DIRECTORY.'/log_files/'.$project['id'].'/custom_cronjob.log'); ?></textarea>
			</div>
		</div>
	<?php endif; ?>
</div>
<script type="text/javascript">
	function deleteLog(id, type){
		$.ajax(
			{
				url: "<?php echo site_url('projects/deleteLog');?>", 
				method: "POST",
				data: { project_id: id, log_type : type} ,
				success: function(result){
	            	if(result == 'true'){
	            		alert(type+' Reset successful.');
	            		location.reload();
	            	}
	            	else
	            		alert(result);
	        	}
        	}
        );
	}
</script>