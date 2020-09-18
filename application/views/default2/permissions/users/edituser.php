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

	$submit = array(
	    'name'          => 'submit',
	    'id'            => 'submit',
	    'value'         => translate('Save'),
	    'class'       => 'btn btn-primary m-btn m-btn--air m-btn--custom'
	);
?>

<script type="text/javascript">
	var permissionData = [];

	var projects_array = [];
	<?php if(in_array("projects", $this->config->item('enabled_modules'))): ?>
	<?php $permission = $this->Permissions_model->check_permission_user('access_projects_section', 0, $user['user_id']); ?>
	projects_array.push({ title: '<?php echo cut(translate('Access projects section'), 80); ?>', key: 'projects-access_projects_section', id: '0', type: 'access_projects_section', typeTitle: '<?php echo translate("Projects"); ?>', permission: '<?php echo $permission; ?>'});
	<?php $permission = $this->Permissions_model->check_permission_user('create_project', 0, $user['user_id']); ?>
	projects_array.push({ title: '<?php echo cut(translate('Create project'), 80); ?>', key: 'projects-create_project', id: '0', type: 'create_project', typeTitle: '<?php echo translate("Projects"); ?>', permission: '<?php echo $permission; ?>'});
	<?php $permission = $this->Permissions_model->check_permission_user('edit_all_projects', 0, $user['user_id']); ?>
	projects_array.push({ title: '<?php echo cut(translate('Edit all projects'), 80); ?>', key: 'projects-edit_all_projects', id: '0', type: 'edit_all_projects', typeTitle: '<?php echo translate("Projects"); ?>', permission: '<?php echo $permission; ?>'});
	<?php $permission = $this->Permissions_model->check_permission_user('all_projects_actions', 0, $user['user_id']); ?>
	permissionData.push({ title: '<?php echo cut(translate('Projects'), 80); ?>', key: 'all_projects_actions-0', id: '0', type: 'all_projects_actions', typeTitle: '<?php echo translate("Projects"); ?>', permission: '<?php echo $permission; ?>', children: projects_array});
	<?php endif; ?>


	var permissions_array = [];
	<?php $permission = $this->Permissions_model->check_permission_user('access_permissions_section', 0, $user['user_id']); ?>
	permissions_array.push({ title: '<?php echo cut(translate('Access permissions section'), 80); ?>', key: 'permissions-access_permissions_section', id: '0', type: 'access_permissions_section', typeTitle: '<?php echo translate("Permissions"); ?>', permission: '<?php echo $permission; ?>'});
	<?php $permission = $this->Permissions_model->check_permission_user('list_users', 0, $user['user_id']); ?>
	permissions_array.push({ title: '<?php echo cut(translate('List users'), 80); ?>', key: 'permissions-list_users', id: '0', type: 'list_users', typeTitle: '<?php echo translate("Users"); ?>', permission: '<?php echo $permission; ?>'});
	<?php $permission = $this->Permissions_model->check_permission_user('create_user', 0, $user['user_id']); ?>
	permissions_array.push({ title: '<?php echo cut(translate('Create user'), 80); ?>', key: 'permissions-create_user', id: '0', type: 'create_user', typeTitle: '<?php echo translate("Users"); ?>', permission: '<?php echo $permission; ?>'});
	<?php $permission = $this->Permissions_model->check_permission_user('create_user_own_company', 0, $user['user_id']); ?>
	<?php $permission = $this->Permissions_model->check_permission_user('manage_all_permissions', 0, $user['user_id']); ?>
	// 				permissions_array.push({ title: '<?php echo cut(translate('Manage permissions (master account)'), 80); ?>', key: 'permissions-manage_all_permissions', id: '0', type: 'manage_all_permissions', typeTitle: '<?php echo translate("Permissions"); ?>', permission: '<?php echo $permission; ?>'});
	<?php $permission = $this->Permissions_model->check_permission_user('all_permission_actions', 0, $user['user_id']); ?>
	permissionData.push({ title: '<?php echo cut(translate('Permissions'), 80); ?>', key: 'all_permission_actions-0', id: '0', type: 'all_permission_actions', typeTitle: '<?php echo translate("Permissions"); ?>', permission: '<?php echo $permission; ?>', children: permissions_array});


	var settings_array = [];
	<?php if(in_array("settings", $this->config->item('enabled_modules'))): ?>
	<?php $permission = $this->Permissions_model->check_permission_user('access_settings_section', 0, $user['user_id']); ?>
	settings_array.push({ title: '<?php echo cut(translate('Access settings section'), 80); ?>', key: 'settings-access_settings_section', id: '0', type: 'access_settings_section', typeTitle: '<?php echo translate("Settings"); ?>', permission: '<?php echo $permission; ?>'});
	<?php $permission = $this->Permissions_model->check_permission_user('all_settings_actions', 0, $user['user_id']); ?>
	permissionData.push({ title: '<?php echo cut(translate('Settings'), 80); ?>', key: 'all_settings_actions-0', id: '0', type: 'all_settings_actions', typeTitle: '<?php echo translate("Settings"); ?>', permission: '<?php echo $permission; ?>', children: settings_array});
	<?php endif; ?>


	<?php foreach($projects as $project): ?>
		var project_children = [];
		<?php $permission = $this->Permissions_model->check_permission_user('project', $project['id'], $user['user_id']); ?>
		permissionData.push({ title: '<?php echo cut(str_replace("'", "\'", $project["title"]), 80); ?>', key: 'project-<?php echo $project['id']; ?>', id: '<?php echo $project['id']; ?>', type: 'project', typeTitle: '<?php echo translate("Project"); ?>', permission: '<?php echo $permission; ?>', children: project_children, folder: true });
	<?php endforeach; ?>
</script>
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
									<?php echo translate('Edit user'); ?>
								</span>
							</a>
						</li>
					</ul>
				</div>
			</div>
		</div>
		<div class="m-content">
			<div class="row">
				<div class="col-xl-12">
					<div class="m-portlet m-portlet--mobile ">
						<?php $this->load->view(TEMPLATE.'/alerts/index'); ?>
						<div class="m-portlet__head">
							<div class="m-portlet__head-caption">
								<div class="m-portlet__head-title">
									<h3 class="m-portlet__head-text">
										<?php echo translate('Edit user'); ?> "<?php echo $user['user_name'];?>"
									</h3>
								</div>
							</div>

						<?php if(strpos($this->Permissions_model->check_permission_user('list_users', '', $this->session->userdata('username')), 'v') > -1
								|| $this->Permissions_model->check_permission_user('create_user', '', $this->session->userdata('username')) == 've'): ?>
								<div class="m-portlet__head-tools">
									<ul class="m-portlet__nav">
										<li class="m-portlet__nav-item">
											<div class="m-dropdown m-dropdown--inline m-dropdown--arrow m-dropdown--align-right m-dropdown--align-push" data-dropdown-toggle="hover" aria-expanded="true">
												<a href="javascript:void(0)" class="m-portlet__nav-link btn btn-lg btn-secondary  m-btn m-btn--icon m-btn--icon-only m-btn--pill  m-dropdown__toggle">
													<i class="la la-ellipsis-h m--font-brand"></i>
												</a>
												<div class="m-dropdown__wrapper">
													<span class="m-dropdown__arrow m-dropdown__arrow--right m-dropdown__arrow--adjust"></span>
													<div class="m-dropdown__inner">
														<div class="m-dropdown__body">
															<div class="m-dropdown__content">
																<ul class="m-nav">
																	<li class="m-nav__section m-nav__section--first">
																		<span class="m-nav__section-text">
																		<?php echo translate('Users'); ?>
																		</span>
																	</li>
																	<?php if(strpos($this->Permissions_model->check_permission_user('list_users', '', $this->session->userdata('username')), 'v') > -1): ?>
																	<li class="m-nav__item">
																		<a href="<?php echo site_url('permissions/index');?>" class="m-nav__link">
																			<i class="m-nav__link-icon flaticon-users"></i>
																			<span class="m-nav__link-text">
																			<?php echo translate('All user');?>
																			</span>
																		</a>
																	</li>
																	<?php endif;
																	 if($this->Permissions_model->check_permission_user('create_user', '', $this->session->userdata('username')) == 've'): ?>
																	<li class="m-nav__item">
																		<a href="<?php echo site_url('permissions/createuser');?>" class="m-nav__link">
																			<i class="m-nav__link-icon flaticon-add"></i>
																			<span class="m-nav__link-text">
																			<?php echo translate('Create user');?>
																			</span>
																		</a>
																	</li>
																	<?php endif; ?>

																</ul>
															</div>
														</div>
													</div>
												</div>
											</div>
										</li>
									</ul>
								</div>
						<?php endif; ?>
						</div>

						<div class="tab-content">
							<div class="tab-pane active" id="m_user_profile_tab_1">
								<?php echo form_open_multipart('permissions/saveuseraction', $form_attributes);?>
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

										<div class="form-field form-group m-form__group row">
											<label for="example-text-input" class="col-3 col-form-label">
												<?php echo translate('User specific permissions');?> <span class="required"><font color="red"></font></span>
											</label>
											<div class="col-8">
												<table id="permissions-table" class="data-table">
													<thead>
														<tr>
															<th><?php echo translate('Name'); ?></th>
															<th><?php echo translate('Type'); ?></th>
															<th><?php echo translate('View'); ?></th>
															<th><?php echo translate('Edit'); ?></th>
															<th><?php echo translate('Accreditate Fields'); ?></th>
														</tr>
													</thead>
													<tbody>
														<tr>
															<td></td>
															<td></td>
															<td></td>
															<td></td>
															<td></td>
														</tr>
													</tbody>
												</table>
											</div>
										</div>
									</div>
									<div class="m-portlet__foot m-portlet__foot--fit">
										<div class="m-form__actions">
											<div class="row">
												<div class="col-3"></div>
												<div class="col-4">
												<?php echo form_submit($submit); ?>
												</div>
												<div class="col-4 text-right">
													<span class="btn btn-danger"><a href="<?php echo site_url('/permissions/deleteuser/id/'.$user['user_id']);?>" onclick="return confirm('<?php echo translate('Are you sure you want to delete this user?');?>')" style="color:#fafafd"><?php echo translate('Delete user');?></a></span>
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
