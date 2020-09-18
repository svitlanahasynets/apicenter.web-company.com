<?php
	$form_attributes = array('class' => 'm-form m-form--fit m-form--label-align-right admin_sendmessage_form', 'autocomplete' => 'off');

	$subject = array(
		'name' => 'subject',
		'id' => 'subject',
		'class' => 'required form-control m-input m-input--air'
	);

	$message_body = array(
		'name' => 'message_body',
		'id' => 'message_body',
		'class' => 'required form-control m-input m-input--air'
	);

	$file = array(
		'name' => 'file',
		'id' => 'file',
		'class' => 'form-control m-input m-input--air'
	);

	$submit = array(
	    'name'          => 'submit',
	    'id'            => 'submit',
	    'value'         => translate('Send Message'),
	    'class'       => 'btn btn-primary m-btn m-btn--air m-btn--custom'
	);
?>
<admin-sendmessage-component post-title="<?php echo translate('Send Message') ?>"  inline-template>
	<component :is="layout">
		<page-title :heading=heading :icon=icon></page-title>
		<!-- <b-card class="main-card mb-4"> -->
			<div class="content">
				<button class="m-aside-left-close m-aside-left-close--skin-light" id="m_aside_left_close_btn">
					<i class="la la-close"></i>
				</button>
				<div class="m-grid__item m-grid__item--fluid m-wrapper">
					<input type="hidden" name="update_url" id="update_url" value="<?php echo site_url('/admin-sendmessage'); ?>" />
					<div class="">
						<div class="row" style="">
							<div class="col-xl-12">
								<div class="m-portlet m-portlet--mobile card ">
									<?php $this->load->view(TEMPLATE . '/alerts/index'); ?>
									<div class="m-portlet__head">
										<div class="m-portlet__head-caption">
											<div class="m-portlet__head-title">
												<h3 class="m-portlet__head-text">
													<span style="font-family: Roboto,sans-serif;">
													</span>
												</h3>
											</div>
										</div>
										<div class="m-portlet__head-tools">
											
										</div>
									</div>
									<div class="m-portlet__body" style="padding: 2.2rem 1.2rem;">
										<div class="form-columns-switcher" style="display: none"></div>

										<?php
											$suffix = '';
											if ($current_project_id) {
												$suffix .= '?selected_project_id=' . $current_project_id;
											}
											$site_url = '/admin-sendmessage' . $suffix;
										?>
										<div class="m-section">
											<div class="m-section__content">
												<div class="container">
													<?php echo form_open_multipart($site_url, $form_attributes);?>
														<div class="form-field form-group m-form__group row">
															<label for="example-text-input" class="col-3 col-form-label">
																<?php echo translate('Select Customers');?> <span class="required"><font color="red">*</font></span>
															</label>
															<div class="col-8">
																<?php echo form_multiselect('selected_customers[]', $selectable_customers, '', 'class="form-control m-input m-input--air" size="' . $viewable_customers_count . '"'); ?>
															</div>
														</div>
														<div class="form-field form-group m-form__group row">
															<label for="example-text-input" class="col-3 col-form-label">
																<?php echo translate('Message Subject');?> <span class="required"><font color="red">*</font></span>
															</label>
															<div class="col-8">
																<?php echo form_input($subject);?>
															</div>
														</div>
														<div class="form-field form-group m-form__group row">
															<label for="example-text-input" class="col-3 col-form-label">
																<?php echo translate('Message Body');?> <span class="required"><font color="red">*</font></span>
															</label>
															<div class="col-8">
																<?php echo form_textarea($message_body);?>
															</div>
														</div>
														<div class="form-field form-group m-form__group row">
															<label for="example-text-input" class="col-3 col-form-label">
																<?php echo translate('Upload file');?> <span class="required"><font color="red"></font></span>
															</label>
															<div class="col-8">
																<?php echo form_upload($file);?>
															</div>
														</div>
														<div class="m-form__actions">
															<div class="row">
																<div class="col-11 text-right">
																	<?php echo form_submit($submit); ?>
																</div>
															</div>
														</div>
													<?php echo form_close(); ?>
												</div>										
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		<!-- </b-card> -->
	</component>
</admin-sendmessage-component>