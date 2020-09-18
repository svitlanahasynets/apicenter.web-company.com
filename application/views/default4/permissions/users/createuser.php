<?php
	$form_attributes = array('class' => 'm-form m-form--fit m-form--label-align-right permissions_createuser_form', 'autocomplete' => 'off');
	$user_name = array(
		'name' => 'user_name',
		'id' => 'user_name',
		'class' => 'required form-control m-input m-input--air'
	);
	$firstname = array(
		'name' => 'firstname',
		'id' => 'firstname',
		'class' => 'required form-control m-input m-input--air'
	);
	$lastname = array(
		'name' => 'lastname',
		'id' => 'lastname',
		'class' => 'required form-control m-input m-input--air'
	);
	$password = array(
		'name' => 'password',
		'id' => 'password',
		'class' => 'required form-control m-input m-input--air'
	);
	$user_phone = array(
		'name' => 'user_phone',
		'id' => 'user_phone',
		'class' => 'form-control m-input m-input--air'
	);
	$user_email = array(
		'name' => 'user_email',
		'id' => 'user_email',
		'class' => 'required form-control m-input m-input--air'
	);
	$submit = array(
        'name'          => 'submit',
        'id'            => 'submit',
        'value'         => translate('Create'),
        'class'       => 'btn btn-primary m-btn m-btn--air m-btn--custom'
	);
?>
<input type="hidden" id="enable-exit-page-message" value="true" />
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
						<?php echo $page_title; ?>
					</h3>
					<ul class="m-subheader__breadcrumbs m-nav m-nav--inline">
						<li class="m-nav__item m-nav__item--home">
							<a href="#" class="m-nav__link m-nav__link--icon">
								<i class="m-nav__link-icon la la-home"></i>
							</a>
						</li>
						<li class="m-nav__separator">
							-
						</li>
						<li class="m-nav__item">
							<a href="<?php echo $go_back_url; ?>" class="m-nav__link">
								<span class="m-nav__link-text">
								<?php echo $go_back_title ;?>
								</span>
							</a>
						</li>
						<li class="m-nav__separator">
							-
						</li>
						<li class="m-nav__item">
							<a href="javascript:void(0)" class="m-nav__link m-nav__link--active">
								<span class="m-nav__link-text">
									<?php echo translate('Create user'); ?>
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
									<?php echo form_open_multipart('permissions/createuseraction', $form_attributes);?>
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