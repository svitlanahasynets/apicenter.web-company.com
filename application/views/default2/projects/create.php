<input type="hidden" id="enable-exit-page-message" value="true" />
<?php

$form_attributes = array('class' => 'projects_create_form m-form m-form--fit m-form--label-align-right', 'autocomplete' => 'off');
$project_title = array(
	'name' => 'project_title',
	'id' => 'project_title',
	'class' => 'required form-control m-input m-input--air',
);
$project_description = array(
	'name' => 'project_desc',
	'id' => 'project_desc',
	'class' => 'required form-control m-input m-input--air',
);

$connection_type = array(
	'name' => 'connection_type',
	'id' => 'connection_type',
	'class' => 'required form-control m-input m-input--air',
	'values' => array(
		'1' => 'ERP systeem & Webshop',
		'2' => 'ERP systeem & Marketplace',
		'3' => 'Marketplace & Webshop',
		'4' => 'Webshop (CMS) & Point of Sale (POS)',
	    '5' => 'Webshop & WMS system',
		'6' => 'ERP systeem & WMS system',
		'7' => 'ERP systeem & PIM systeem',
		'8' => 'Marketing system & Management system'
	)
);

$erp_system = array(
	'name' => 'erp_system',
	'id' => 'erp_system',
	'class' => 'required form-control m-input m-input--air',
	'values' => array(
		'' => 'Select Erp system',
		'afas' => 'AFAS',
		'exactonline' => 'Exact Online',
		'visma' => 'Visma',
		'twinfield' => 'Twinfield',
		'accountview' => 'AccountView'
	)
);

$store_url = array(
	'name' => 'store_url',
	'id' => 'store_url',
	'class' => 'form-control m-input m-input--air',
);

$db_users = $this->db->get('permissions_users')->result_array();
$contacts = array();
$contacts[] = translate('None');
foreach($db_users as $db_user){
	$user_id = $db_user['user_id'];
	$contacts[$user_id] = $db_user['firstname'].' '.$db_user['lastname'].' ('.$db_user['user_name'].')';
}
$submit = array(
    'name'          => 'submit',
    'id'            => 'submit',
    'value'         => translate('Save'),
    'class'       => 'btn btn-primary m-btn m-btn--air m-btn--custom'
);
?>
<div class="m-grid__item m-grid__item--fluid  m-grid m-grid--ver-desktop m-grid--desktop 	m-container m-container--responsive m-container--xxl m-page__container m-body">
	<button class="m-aside-left-close m-aside-left-close--skin-light" id="m_aside_left_close_btn">
		<i class="la la-close"></i>
	</button>
	<?php echo $menu_html;?>
	
	<div class="m-grid__item m-grid__item--fluid m-wrapper">
		<div class="m-subheader ">
			<div class="d-flex align-items-center">
				<div class="mr-auto">
					<h3 class="m-subheader__title m-subheader__title--separator">
						<?php echo translate('Create a project'); ?>
					</h3>
					<ul class="m-subheader__breadcrumbs m-nav m-nav--inline">
						<li class="m-nav__item m-nav__item--home">
							<a href="<?php echo site_url('projects/index');?>" class="m-nav__link m-nav__link--icon">
								<i class="m-nav__link-icon la la-home"></i><?php echo translate('All projects') ?>
							</a>
						</li>
						<li class="m-nav__separator">
							-
						</li>
						<li class="m-nav__item">
							<a href="javascript:void(0)" class="m-nav__link">
								<span class="m-nav__link-text">
									<?php echo translate('Create a project'); ?>
								</span>
							</a>
						</li>
					</ul>
				</div>
			</div>
		</div>
		<div class="m-content">
			<div class="row">
				<div class="col-xl-11 col-lg-12">
					<div class="m-portlet m-portlet--full-height   m-portlet--rounded">
						<div class="m-portlet__body">
						<?php $this->load->view(TEMPLATE.'/alerts/index'); ?>
							<div class="tab-content">
								<div class="tab-pane active" id="m_user_profile_tab_1">
								<?php echo form_open_multipart('projects/createaction', $form_attributes);?>
									<div class="m-portlet__body">
										<div class="form-group">
										    <label for="contact_person" class="col-4 col-form-label">
										        <span class="label"> <b> <?php echo translate('Project Information');?> </b></span>
										    </label>
										    <hr size="5" color="#032f59" style="margin-top: 1rem; margin-left: 1rem; margin-right: 5rem;">
										</div>

										<div class="form-field form-group m-form__group row">
											<label for="project_title" class="col-3 col-form-label">
												<span class="label"> 
													<?php echo translate('Project title');?> <span class="required"><font color="red">*</font></span>
												</span>
											</label>
											<div class="col-8">
												<?php echo form_input($project_title);?>
											</div>
										</div>
										<div class="form-field form-group m-form__group row">
											<label for="project_desc" class="col-3 col-form-label">
												<span class="label"> 
													<?php echo translate('Project description');?> <span class="required"><font color="red">*</font></span>
												</span>
											</label>
											<div class="col-8">
												<?php echo form_textarea($project_description);?>
											</div>
										</div>

										<div class="form-group">
										    <label for="contact_person" class="col-4 col-form-label">
										        <span class="label"> <b> <?php echo translate('Settings Link');?>  </b></span>
										    </label>
										    <hr size="5" color="#032f59" style="margin-top: 1rem; margin-left: 1rem; margin-right: 5rem;">
										</div>

										<div class="form-field form-group m-form__group row">
											<label for="erp_system" class="col-3 col-form-label">
												<span class="label"> 
													<?php echo translate('Connection Type');?> <span class="required"><font color="red">*</font></span>
												</span>
											</label>
											<div class="col-8">
												<?php echo form_dropdown('connection_type', $connection_type['values'],'','class="required form-control m-input m-input--air" id="connection_type"');?>
											</div>
										</div>
										<div class="form-field form-field-erp_system form-group m-form__group row" data-dependencies='{"connection_type":"2,1,5,6,7"}'>
											<label for="erp_system" class="col-3 col-form-label">
												<span class="label"> 
													<?php echo translate('ERP Software system');?> <span class="required"><font color="red">*</font></span>
												</span>
											</label>
											<div class="col-8">
												<?php echo form_dropdown('erp_system', $erp_system['values'],'','class="form-control m-input m-input--air" id="erp_system"');?>
											</div>
										</div>
										<div class="form-field form-field-webshop form-group m-form__group row" data-dependencies='{"connection_type":"3,1,4,5"}'>
											<label for="store_url" class="col-3 col-form-label">
												<span class="label"> 
													<?php echo translate('Webshop URL address');?> <span class="required"><font color="red">*</font></span>
												</span>
											</label>
											<div class="col-8">
												<?php echo form_input($store_url);?>
											</div>
										</div>									
										<div class="form-field form-group m-form__group row">
											<label for="contact_person" class="col-3 col-form-label">
												<span class="label"> 
													<?php echo translate('Contact person');?> <span class="required"><font color="red">*</font></span>
												</span>
											</label>
											<div class="col-8">
												<?php echo form_dropdown('contact_person', $contacts,'','class="form-control m-input m-input--air"');?>
											</div>
										</div>

										<?php
										$att = array('class'=>'form-control m-input m-input--air');
										foreach($project_settings as $field): 
											$value = isset($field['default']) ? $field['default'] : ''; 
											if($field['headers']!=NULL):?>
												<div class="form-field form-group form-field-<?php echo $field['code']; ?> " data-dependencies='<?php echo isset($field['depends_on']) ? json_encode($field['depends_on']) : ''; ?>'>
													<span class="label"> <b> <?php echo $field['headers'];?>  </b></span>
													<hr size="5" color="#032f59" style="margin-top: 1rem; margin-left: 1rem; margin-right: 5rem;">
												</div>
											<?php endif;
											if($field['type'] == 'text'): ?>
												<div class="form-field form-field-<?php echo $field['code']; ?> form-group m-form__group row" data-dependencies='<?php echo isset($field['depends_on']) ? json_encode($field['depends_on']) : ''; ?>'>
													<label for="<?php echo $field['code']; ?>" class="col-3 col-form-label">
														<span class="label"> 
															<?php echo translate($field['title']);?> <span class="required"><font color="red">*</font></span>
														</span>
													</label>
													<div class="col-8">
														<?php 
														if($field['code']=='exact_article_last_update_date' || $field['code']=='article_last_update_date'){
															$value = date('Y-m-d H:i:s');
															echo form_input('settings['.$field['code'].']', $value,'class="form-control m-input m-input--air" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01]) (0[0-9]|1[0-9]|2[0123]):(0[0-9]|1[0-9]|2[0-9]|3[0-9]|4[0-9]|5[0-9]):(00)"');
														} else if($field['code']=='nopcommerce_order_start_day' || $field['code']=='nopcommerce_order_start_day'){
															$value = date('Y-m-d H:i:s');
															echo form_input('settings['.$field['code'].']', $value,'class="form-control m-input m-input--air" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01]) (0[0-9]|1[0-9]|2[0123]):(0[0-9]|1[0-9]|2[0-9]|3[0-9]|4[0-9]|5[0-9]):(00)"');
														} else
															echo form_input('settings['.$field['code'].']', $value, 'class="form-control m-input m-input--air" id='. $field['code']);?>
													</div>
													<?php if($field['help_option']==1) : ?>
														<div class="col-1">
															<button class="btn btn-link" type="button" data-html="true" data-trigger="focus" data-toggle="m-popover" title="" data-content="<?php echo $field['help_message'];?>" data-original-title="Waar dient dit voor ?">
																<i class="fa fa-question-circle"></i>
															</button>
														</div>
													<?php endif;?>
													<?php if($field['help_option']==2) : ?>
														<div class="col-1">
															<button class="btn btn-link" type="button" data-html="true" data-trigger="focus" data-toggle="m-popover" title="" data-content="<p><a href='<?php echo $field['help_url'];?>' target='_blank'> Learn More... </a></p>" data-original-title="Waar dient dit voor ?">
																<i class="fa fa-question-circle"></i>
															</button>
														</div>
													<?php endif;?>
													<?php if($field['help_option']==3) : ?>
														<div class="col-1">
															<button class="btn btn-link" type="button" data-html="true" data-trigger="focus" data-toggle="m-popover" title="" data-content="<?php echo $field['help_message'];?> <br/> <p><a href='<?php echo $field['help_url'];?>' target='_blank'> Learn More... </a></p>" data-original-title="Waar dient dit voor ?">
																<i class="fa fa-question-circle"></i>
															</button>
														</div>
													<?php endif;?>
													<?php if($field['code']=='product_condition_code') : ?>
														<div class="col-1" id="idFormConditionCodeDiv" style="display: none">
															<button  class="btn btn-primary m-btn m-btn--air m-btn--custom" id="idFormConditionCode" title="Create Condition Field on Magento!"> ✓ </button>
														</div>
													<?php endif;?>
													<?php if($field['code']=='product_delivery_code') : ?>
														<div class="col-1" id="idFormDeliveryCodeDiv" style="display: none">
															<button  class="btn btn-primary m-btn m-btn--air m-btn--custom" id="idFormDeliveryCode" title="Create Delivery Code Field on Magento!"> ✓ </button>
														</div>
													<?php endif;?>
												</div>
											<?php 
												elseif($field['type'] == 'select'): 
													$values = array();
													foreach($field['values'] as $code => $fieldValue){
														$values[$code] = translate($fieldValue);
													} ?>
													<div class="form-field form-field-<?php echo $field['code']; ?> form-group m-form__group row" data-dependencies='<?php echo isset($field['depends_on']) ? json_encode($field['depends_on']) : ''; ?>'>
														<label for="<?php echo $field['code']; ?>" class="col-3 col-form-label">
															<span class="label"> 
																<?php echo translate($field['title']);?> <span class="required"><font color="red">*</font></span>
															</span>
														</label>
														<div class="col-8">
															<?php echo form_dropdown('settings['.$field['code'].']', $values, $value,'class="form-control m-input m-input--air" id='. $field['code']);?>
														</div>
														<?php if($field['help_option']==1) : ?>
														<div class="col-1">
															<button class="btn btn-link" type="button" data-html="true" data-trigger="focus" data-toggle="m-popover" title="" data-content="<?php echo $field['help_message'];?>" data-original-title="Waar dient dit voor ?">
																<i class="fa fa-question-circle"></i>
															</button>
														</div>
													<?php endif;?>
													<?php if($field['help_option']==2) : ?>
														<div class="col-1">
															<button class="btn btn-link" type="button" data-html="true" data-trigger="focus" data-toggle="m-popover" title="" data-content="<p><a href='<?php echo $field['help_url'];?>' target='_blank'> Learn More... </a></p>" data-original-title="Waar dient dit voor ?">
																<i class="fa fa-question-circle"></i>
															</button>
														</div>
													<?php endif;?>
													<?php if($field['help_option']==3) : ?>
														<div class="col-1">
															<button class="btn btn-link" type="button" data-html="true" data-trigger="focus" data-toggle="m-popover" title="" data-content="<?php echo $field['help_message'];?> <br/> <p><a href='<?php echo $field['help_url'];?>' target='_blank'> Learn More... </a></p>" data-original-title="Waar dient dit voor ?">
																<i class="fa fa-question-circle"></i>
															</button>
														</div>
													<?php endif;?>
													</div>
											<?php 
												elseif($field['type'] == 'checkbox'):
												?>
													<div class="form-field form-field-<?php echo $field['code']; ?> form-group m-form__group row" data-dependencies='<?php echo isset($field['depends_on']) ? json_encode($field['depends_on']) : ''; ?>'>
														<label for="checkbox_<?php echo $field['code']; ?>" class="col-3 col-form-label">
															<?php echo translate($field['title']);?><span class="required"><font color="red">*</font></span>
														</label>
														<div class="col-8">
															<?php echo form_checkbox([
																'id'	   => 'checkbox_'.$field['code'],
																'data-id'  => $field['code'],
																'data-val' => $field['values'][$field['code']],
																'checked'  => FALSE,
															]); ?>
															<input type="hidden" id="<?php echo $field['code']?>" value="" name=<?php echo 'settings['.$field['code'].']'?> disabled>
														</div>
													</div>
											<?php endif; ?>
										<?php endforeach; ?>
									</div>
									<div class="m-portlet__foot m-portlet__foot--fit">
										<div class="m-form__actions">
											<div class="row">
												<div class="col-3"></div>
												<div class="col-8">
												<?php echo form_submit($submit); ?>
												</div>
											</div>
										</div>
									</div>
									<?php echo form_close(); ?>
								</div>
								<div class="tab-pane " id="m_user_profile_tab_2"></div>
								<div class="tab-pane " id="m_user_profile_tab_3"></div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div> 
	</div>
</div>
<script type="text/javascript">
	$( document ).ready(function() {
        setTimeout(function(){
        	$('#cms').trigger('change');
        },1000);

		$('.form-field input[type=checkbox]').on('change', function(){
			if ($(this).prop('checked')) {
				var id = $(this).attr('data-id');
				$('#'+id).prop('disabled', false);
				$('#'+id).val($(this).attr('data-val'));
			} else {
				var id = $(this).attr('data-id');
				$('#'+id).prop('disabled', true);
				$('#'+id).val($(this).attr(''));
			}
		});
    });
    $('#connection_type').change(function(){
    	if($('#connection_type').val()==1)
    		$('#market_place').val('');
    	else if($('#connection_type').val()==2)
    		$('#cms').val('');
    	else if($('#connection_type').val()==3)
    		$('#erp_system').val('');
    });

    $('#cms').change(function(){
    	if($('#cms').val()=='magento2' && $('#market_place').val()=='bol'){
    		$('#idFormConditionCodeDiv').css('display','block');
    		$('#idFormDeliveryCodeDiv').css('display','block');
    	} else{
    		$('#idFormConditionCodeDiv').css('display','none');
    		$('#idFormDeliveryCodeDiv').css('display','none');
    	}
    });

    $('#market_place').change(function(){
    	if($('#cms').val()=='magento2' && $('#market_place').val()=='bol'){
    		$('#idFormConditionCodeDiv').css('display','block');
    		$('#idFormDeliveryCodeDiv').css('display','block');
    	} else{
    		$('#idFormConditionCodeDiv').css('display','none');
    		$('#idFormDeliveryCodeDiv').css('display','none');
    	}
    });
    
    // this is the id of the form
	$("#idFormConditionCode").click(function(e) {
	    var url 			= "creatConditionCode"; // the script where you handle the form input.
	    var magento_user 	= $('#user').val();
	    var magento_pass 	= $('#password').val();
	    var store_url 		= $('#store_url').val();
	    if(magento_user != '' && magento_pass != '' && store_url != ''){
	    	$.ajax({
		       	type: "POST",
		       	url: url,
		    	data: { magento_user: magento_user, magento_pass : magento_pass, store_url:store_url} ,
		       	success: function(data){
					var obj = JSON.parse(data);
		            if(obj.status==1){
    					$('#idFormConditionCodeDiv').css('display','none');
		           		$('#product_condition_code').val(obj.result);
		            }
		           	else
		           		alert(obj.result);
		       	}
		    });
	    } else{
	    	alert('Please provide magento user name and user password');
	    }
	    e.preventDefault(); // avoid to execute the actual submit of the form.
	});
	$("#idFormDeliveryCode").click(function(e) {
	    var url 			= "creatDeliveryCode"; // the script where you handle the form input.
	    var magento_user 	= $('#user').val();
	    var magento_pass 	= $('#password').val();
	    var store_url 		= $('#store_url').val();
	    if(magento_user != '' && magento_pass != '' && store_url != ''){
	    	$.ajax({
		       	type: "POST",
		       	url: url,
		    	data: { magento_user: magento_user, magento_pass : magento_pass, store_url:store_url} ,
		       	success: function(data){
					var obj = JSON.parse(data);
		            if(obj.status==1){
    					$('#idFormDeliveryCodeDiv').css('display','none');
		           		$('#product_delivery_code').val(obj.result);
		            }
		           	else
		           		alert(obj.result);
		       	}
		    });
	    } else{
	    	alert('Please provide magento user name and user password');
	    }
	    e.preventDefault(); // avoid to execute the actual submit of the form.
	});
</script>