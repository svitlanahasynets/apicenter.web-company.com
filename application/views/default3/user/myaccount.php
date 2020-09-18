<?php
	$form_attributes = array('class' => 'm-form m-form--fit m-form--label-align-right permissions_edituser_form', 'autocomplete' => 'off');

	$user_name = array(
		'name' => 'user_name',
		'id' => 'user_name',
		'class' => 'required form-control m-input m-input--air',
		'readonly' => 'readonly',
		'value' => $user['user_name']
	);
	$firstname = array(
		'name' => 'firstname',
		'id' => 'firstname',
		'class' => 'required form-control m-input m-input--air',
		'value' => $user['firstname']
	);
	$lastname = array(
		'name' => 'lastname',
		'id' => 'lastname',
		'class' => 'required form-control m-input m-input--air',
		'value' => $user['lastname']
	);

	$password = array(
		'name' => 'password',
		'id' => 'password',
		'class' => 'required form-control m-input m-input--air',
		'value' => ''
	);
	$user_phone = array(
		'name' => 'user_phone',
		'id' => 'user_phone',
		'class' => 'form-control m-input m-input--air',
		'value' => $user['user_phone']
	);
	$user_email = array(
		'name' => 'user_email',
		'id' => 'user_email',
		'class' => 'required form-control m-input m-input--air',
		'value' => $user['user_email']
	);
	$profile_picture = array(
		'name' => 'profile_picture',
		'id' => 'profile_picture',
		'class' => 'form-control m-input m-input--air',
		'value' => $user['profile_picture']
	);

	$submit = array(
	    'name'          => 'submit',
	    'id'            => 'submit',
	    'value'         => translate('Save'),
	    'class'       => 'btn btn-primary m-btn m-btn--air m-btn--custom'
	);
?>
<myaccount-component post-title="<?php echo translate('My account') ?>" inline-template>
	<component :is="layout">
		<page-title :heading=heading :icon=icon></page-title>
		<input type="hidden" id="enable-exit-page-message" value="true" />
		<div>
				<button class="m-aside-left-close m-aside-left-close--skin-light" id="m_aside_left_close_btn">
				<i class="la la-close"></i>
			</button>
			<div class="m-grid__item m-grid__item--fluid m-wrapper">
				<div class="m-content">
					<div class="row">
						<div class="col-xl-12">
							<div class="m-portlet m-portlet--mobile ">
								<?php $this->load->view(TEMPLATE.'/alerts/index'); ?>
								<div class="tab-content">
									<div class="tab-pane active" id="m_user_profile_tab_1">
										<?php echo form_open_multipart('permissions/savemyaccount', $form_attributes);?>
											<input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>" id="user_id"/>											
											<div class="m-portlet__body">
												<div class="form-field form-group m-form__group row">
													<label for="example-text-input" class="col-3 col-form-label">
														<?php echo translate('Username');?> <span class="required"><font color="red">*</font></span>
													</label>
													<div class="col-8">
														<?php echo form_input($user_name);?>
													</div>
												</div>
												<div class="form-field form-group m-form__group row">
													<label for="example-text-input" class="col-3 col-form-label">
														<?php echo translate('First name');?> <span class="required"><font color="red">*</font></span>
													</label>
													<div class="col-8">
														<?php echo form_input($firstname);?>
													</div>
												</div>
												<div class="form-field form-group m-form__group row">
													<label for="example-text-input" class="col-3 col-form-label">
														<?php echo translate('Last name');?> <span class="required"><font color="red">*</font></span>
													</label>
													<div class="col-8">
														<?php echo form_input($lastname);?>
													</div>
												</div>
												<div class="form-field form-group m-form__group row">
													<label for="example-text-input" class="col-3 col-form-label">
														<?php echo translate('Password');?> <span class="required"><font color="red">*</font></span>
													</label>
													<div class="col-8">
														<?php echo form_password($password);?>
														<span class="input-description"><?php echo translate('Enter a new password if you want to change it'); ?></span>
													</div>
												</div>
												<div class="form-field form-group m-form__group row">
													<label for="example-text-input" class="col-3 col-form-label">
														<?php echo translate('E-mail address');?> <span class="required"><font color="red">*</font></span>
													</label>
													<div class="col-8">
														<?php echo form_input($user_email);?>
													</div>
												</div>
												<div class="form-field form-group m-form__group row">
													<label for="example-text-input" class="col-3 col-form-label">
														<?php echo translate('Phone number');?> <span class="required"><font color="red"></font></span>
													</label>
													<div class="col-8">
														<?php echo form_input($user_phone);?>
													</div>
												</div>
												<div class="form-field form-group m-form__group row mb-3">
													<label for="example-text-input" class="col-3 col-form-label">
														<?php echo translate('Profile picture');?> <span class="required"><font color="red"></font></span>
													</label>
													<div class="col-8">
														<?php echo form_upload($profile_picture);?>
													</div>
												</div>
												<div class="form-field form-group m-form__group row">
													<div class="col-6">
														<div class="email-visible-toggle mb-3 row">
															<label for="example-text-input" class="col-7 col-form-label text-right">
																Do you want to receive an email when an error occurs?
															</label>
															<div class="col-3">
																<label class="switch">
																	<input type="checkbox" checked id="switch_button">
																	<span class="slider round"></span>
																</label>
															</div>
														</div>
													</div>
													<div class="col-6">
														<div class="row mb-3">
															<div class="col-md-12">
																<div id="message_type_select">
																	<div class="widget-content p-3">
																		<div class="widget-content-wrapper">
																			<div class="widget-content-left mr-2">
																				<div class="custom-checkbox custom-control"><input type="checkbox" id="message_instant" class="custom-control-input"><label class="custom-control-label" for="message_instant">&nbsp;</label>
																				</div>
																			</div>
																			<div class="widget-content-left">
																				<div class="widget-heading">Instant
																				</div>
																			</div>
																		</div>
																	</div>
																	<div class="widget-content p-3">
																		<div class="widget-content-wrapper">
																			<div class="widget-content-left mr-2">
																				<div class="custom-checkbox custom-control"><input type="checkbox" id="message_day" class="custom-control-input"><label class="custom-control-label" for="message_day">&nbsp;</label>
																				</div>
																			</div>
																			<div class="widget-content-left">
																				<div class="widget-heading">Once a day with a summary of errors
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
											<div class="m-portlet__foot m-portlet__foot--fit">
												<div class="m-form__actions">
													<div class="row">
														<div class="col-11 text-right">
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
					<!--End::Section-->
				</div>
			</div>
		</div>
	</component>
</myaccount-component>
