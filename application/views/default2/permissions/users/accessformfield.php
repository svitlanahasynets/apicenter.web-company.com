<?php
	$form_attributes = array('class' => 'm-form m-form--fit m-form--label-align-right permissions_edituser_form', 'autocomplete' => 'off');
	$project_id = array('project_id' => $project['id']);
	$submit = array(
	    'name'          => 'submit',
	    'id'            => 'submit',
	    'value'         => translate('Save'),
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
									<?php echo translate('Authorise form fileds'); ?>
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
						<div class="m-portlet__head">
							<div class="m-portlet__head-caption">
								<div class="m-portlet__head-title">
									<h3 class="m-portlet__head-text">
										<?php echo translate('Authorise form fileds of projects'); ?> "<?php echo $project['title'];?>"
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
								<?php echo form_open_multipart('permissions/saveuseractionformauth', $form_attributes);?>
								<?php echo form_hidden('user_id', $user['user_id']);?>
								<?php echo form_hidden('project_id', $project_id['project_id']);?>
									<div class="m-portlet__body">
										<div class="form-field form-group m-form__group row">
											<label for="project_title" class="col-4 col-form-label">
												<span class="label">
													<?php echo translate('Field Name');?>
												</span>
											</label>
											<div class="col-8">
												<div class="m-checkbox-inline">
													<label class="m-checkbox">
														<?php echo form_checkbox(['name' => 'all[v]', 'id' => 'viewCheckAll', 'value' => '1', 'checked' => FALSE]); ?>
														View
														<span></span>
													</label>
													<label class="m-checkbox">
														<?php echo form_checkbox(['name' => 'all[ve]', 'id' => 'updateCheckAll', 'value' => '1', 'checked' => FALSE]); ?>
														Update
														<span></span>
													</label>
													<!-- <label class="m-checkbox">
														<?php // echo form_checkbox(['name' => 'all[cve]', 'id' => 'createCheckAll', 'value' => '1', 'checked' => FALSE]); ?>
														Create
														<span></span>
													</label> -->
												</div>
											</div>
										</div>
										<div class="form-group">
										    <hr size="5" color="#032f59" style="margin-top: 1rem; margin-left: 1rem; margin-right: 5rem;">
										</div>
										<div class="form-field form-group m-form__group row">
											<label for="project_title" class="col-4 col-form-label">
												<span class="label">
													<?php echo translate('Project title');?>
												</span>
											</label>
											<div class="col-8">
												<div class="m-checkbox-inline">
													<label class="m-checkbox">
														<?php
														echo form_checkbox(['name' => 'permission[project_title][v]', 'id' => 'project_title_v', 'class' => 'viewcheckBoxClass', 'value' => '1', 'checked' => $permission['project_title_v']]); ?>
														<span></span>
													</label>
													<label class="m-checkbox">
														<?php echo form_checkbox(['name' => 'permission[project_title][ve]', 'id' => 'project_title_ve', 'class' => 'updatecheckBoxClass', 'value' => '1', 'checked' => $permission['project_title_ve']]); ?>
														<span style="margin-left: 35px;"></span>
													</label>
													<!-- <label class="m-checkbox">
														<?php //echo form_checkbox(['name' => 'permission[project_title][cve]', 'id' => 'project_title_cve', 'class' => 'createcheckBoxClass', 'value' => '1', 'checked' => $permission['project_title_cve']]); ?>
														<span></span>
													</label> -->
												</div>
											</div>
										</div>
										<div class="form-field form-group m-form__group row">
											<label for="project_desc" class="col-4 col-form-label">
												<span class="label">
													<?php echo translate('Project description');?>
												</span>
											</label>
											<div class="col-8">
												<div class="m-checkbox-inline">
													<label class="m-checkbox">
														<?php echo form_checkbox(['name' => 'permission[project_desc][v]', 'id' => 'project_desc_v', 'class' => 'viewcheckBoxClass', 'value' => '1', 'checked' => $permission['project_desc_v']]); ?>
														<span></span>
													</label>
													<label class="m-checkbox">
														<?php echo form_checkbox(['name' => 'permission[project_desc][ve]', 'id' => 'project_desc_ve', 'class' => 'updatecheckBoxClass', 'value' => '1', 'checked' => $permission['project_desc_ve']]); ?>
															<span style="margin-left: 35px;"></span>
													</label>
													<!-- <label class="m-checkbox">
														<?php //echo form_checkbox(['name' => 'permission[project_desc][cve]', 'id' => 'project_desc_cve', 'class' => 'createcheckBoxClass', 'value' => '1', 'checked' => $permission['project_desc_cve']]); ?>
														<span></span>
													</label> -->
												</div>
											</div>
										</div>
										<div class="form-field form-group m-form__group row">
											<label for="erp_system" class="col-4 col-form-label">
												<span class="label">
													<?php echo translate('ERP Software system');?>
												</span>
											</label>
											<div class="col-8">
												<div class="m-checkbox-inline">
													<label class="m-checkbox">
														<?php echo form_checkbox(['name' => 'permission[erp_system][v]', 'id' => 'erp_system_v', 'class' => 'viewcheckBoxClass', 'value' => '1', 'checked' => $permission['erp_system_v']]); ?>
														<span></span>
													</label>
													<label class="m-checkbox">
														<?php echo form_checkbox(['name' => 'permission[erp_system][ve]', 'id' => 'erp_system_ve', 'class' => 'updatecheckBoxClass', 'value' => '1', 'checked' => $permission['erp_system_ve']]); ?>
														<span style="margin-left: 35px;"></span>
													</label>
													<!-- <label class="m-checkbox">
														<?php // echo form_checkbox(['name' => 'permission[erp_system][cve]', 'id' => 'erp_system_cve', 'class' => 'createcheckBoxClass', 'value' => '1', 'checked' => $permission['erp_system_cve']]); ?>
														<span></span>
													</label> -->
												</div>
											</div>
										</div>
										<div class="form-field form-field-enable_store_url webshop form-group m-form__group row">
											<label for="store_url" class="col-4 col-form-label">
												<span class="label">
													<?php echo translate('Webshop URL address');?>
												</span>
											</label>
											<div class="col-8">
												<div class="m-checkbox-inline">
													<label class="m-checkbox">
														<?php echo form_checkbox(['name' => 'permission[store_url][v]', 'id' => 'store_url_v', 'class' => 'viewcheckBoxClass', 'value' => '1', 'checked' => $permission['store_url_v']]); ?>
														<span></span>
													</label>
													<label class="m-checkbox">
														<?php echo form_checkbox(['name' => 'permission[store_url][ve]', 'id' => 'store_url_ve', 'class' => 'updatecheckBoxClass', 'value' => '1', 'checked' => $permission['store_url_ve']]); ?>
														<span style="margin-left: 35px;"></span>
													</label>
													<!-- <label class="m-checkbox">
														<?php // echo form_checkbox(['name' => 'permission[store_url][cve]', 'id' => 'store_url_cve', 'class' => 'createcheckBoxClass', 'value' => '1', 'checked' => $permission['store_url_cve']]); ?>
														<span></span>
													</label> -->
												</div>
											</div>
										</div>
										<div class="form-field form-group m-form__group row">
											<label for="contact_person" class="col-4 col-form-label">
												<span class="label">
													<?php echo translate('Contact person');?>
												</span>
											</label>
											<div class="col-8">
												<div class="m-checkbox-inline">
													<label class="m-checkbox">
														<?php echo form_checkbox(['name' => 'permission[contact_person][v]', 'id' => 'contact_person_v', 'class' => 'viewcheckBoxClass', 'value' => '1', 'checked' => $permission['contact_person_v']]); ?>
														<span></span>
													</label>
													<label class="m-checkbox">
														<?php echo form_checkbox(['name' => 'permission[contact_person][ve]', 'id' => 'contact_person_ve', 'class' => 'updatecheckBoxClass', 'value' => '1', 'checked' => $permission['contact_person_ve']]); ?>
														<span style="margin-left: 35px;"></span>
													</label>
													<!-- <label class="m-checkbox">
														<?php // echo form_checkbox(['name' => 'permission[contact_person][cve]', 'id' => 'contact_person_cve', 'class' => 'createcheckBoxClass', 'value' => '1', 'checked' => $permission['contact_person_cve']]); ?>
														<span></span>
													</label> -->
												</div>
											</div>
										</div>
										<?php
										$att = array('class'=>'form-control m-input m-input--air');
										foreach($project_settings as $field):
											$continue = false;
											$value = $this->Projects_model->getValue($field['code'], $project['id']);
										 	if($value == '' && isset($field['default'])) $value = $field['default'];
											if(isset($field['depends_on'])){
												foreach ($field['depends_on'] as $dep_key => $dep_value) {
													$value1 = $this->Projects_model->getValue($dep_key, $project['id']);
													if($dep_key=='erp_system'){
														$value1 = $project['erp_system'];
													} else if($dep_key=='connection_type'){
														$value1 = $project['connection_type'];
													}
													$value1_arr = explode(',', $dep_value);
													foreach ($value1_arr as $arr_key => $arr_value) {
														if($arr_value!=$value1){
															$continue = true;
														} else{
															$continue = false;
															break;
														}
													}
												}
											}
											if($continue)
												continue;
											$view 		= $field['code'].'_v';
											$update  	= $field['code'].'_ve';
											$create 	= $field['code'].'_cve';
											$$view = $$update = $$create = FALSE;

											$permission_f = $field['permission'];
											if($permission_f=='v'){
													$$view 	= TRUE;
												} else if($permission_f=='ve'){
													$$update 	= $$view = TRUE;
												} else if($permission_f=='cve'){
													$$create 	= $$update = $$view = TRUE;
												}
												?>
												<div class="form-field form-field-<?php echo $field['code']; ?> form-group m-form__group row">
													<label for="<?php echo $field['code']; ?>" class="col-4 col-form-label">
														<span class="label">
															<?php echo translate($field['title']);?>
														</span>
													</label>
													<div class="col-8">
														<div class="m-checkbox-inline">
															<label class="m-checkbox">
																<?php echo form_checkbox(['name' => 'permission['.$field['code'].'][v]', 'id' => $field['code'].'_v', 'class' => 'viewcheckBoxClass', 'value' => '1', 'checked' => $$view]); ?>
																<span></span>
															</label>
															<label class="m-checkbox">
																<?php echo form_checkbox(['name' => 'permission['.$field['code'].'][ve]', 'id' => $field['code'].'_ve', 'class' => 'updatecheckBoxClass', 'value' => '1', 'checked' => $$update]); ?>
																<span style="margin-left: 35px;"></span>
															</label>
															<!-- <label class="m-checkbox">
																<?php //echo form_checkbox(['name' => 'permission['.$field['code'].'][cve]', 'id' => $field['code'].'_cve', 'class' => 'createcheckBoxClass', 'value' => '1', 'checked' => $$create]); ?>
																<span></span>
															</label> -->
														</div>
													</div>
												</div>
										<?php endforeach; ?>
									</div>
									<div class="m-portlet__foot m-portlet__foot--fit">
										<div class="m-form__actions">
											<div class="row">
												<div class="col-3"></div>
												<div class="col-4">
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
<script type="text/javascript">

$(document).ready(function(){
	checkUncheckAll();
});
$("#viewCheckAll").click(function () {
    $(".viewcheckBoxClass").prop('checked', $(this).prop('checked'));
});
$("#updateCheckAll").click(function () {
		if(!$("#viewCheckAll").prop('checked') && $(this).prop('checked'))
			$("#viewCheckAll").trigger('click');
    $(".updatecheckBoxClass").prop('checked', $(this).prop('checked'));
});
// $("#createCheckAll").click(function () {
//     $(".createcheckBoxClass").prop('checked', $(this).prop('checked'));
// });
$(".viewcheckBoxClass").click(function(){
		var id = this.id;
		if(!$("#"+id).prop('checked') && $("#"+id+'e').prop('checked'))
			$("#"+id+'e').prop('checked', $("#"+id).prop('checked'));
		//$("#"+id.replace(/v+$/g, '')+'cve').prop('checked', $("#"+id).prop('checked'));
		checkUncheckAll();
});
$(".updatecheckBoxClass").click(function(){
		var id = this.id;
		if($("#"+id).prop('checked'))
			$("#"+id.replace(/e+$/g, '')).prop('checked', $("#"+id).prop('checked'));
		checkUncheckAll();
});
// $(".createcheckBoxClass").click(function(){
// 		var id = this.id;
// 		$("#"+id.replace(/cve+$/g, '')+'v').prop('checked', $("#"+id).prop('checked'));
// 		checkUncheckAll();
// });

function checkUncheckAll(){
	// var checked = true;
	// $(".createcheckBoxClass").each(function(i,n){
	// 	if (!$('#'+this.id).is(':checked')) {
	// 		checked = false;
	// 		return false;
	// 	}
	// });
	// $("#createCheckAll").prop('checked',checked);

	var checked = true;
	$(".updatecheckBoxClass").each(function(i,n){
		if (!$('#'+this.id).is(':checked')) {
			checked = false;
			return false;
		}
	});
	$("#updateCheckAll").prop('checked',checked);

	var checked = true;
	$(".viewcheckBoxClass").each(function(i,n){
		if (!$('#'+this.id).is(':checked')) {
			checked = false;
			return false;
		}
	});
	$("#viewCheckAll").prop('checked',checked);

}

</script>
