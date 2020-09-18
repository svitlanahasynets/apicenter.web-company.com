<input type="hidden" id="enable-exit-page-message" value="true" />
<?php

$form_attributes = array('class' => 'projects_edit_form m-form m-form--fit m-form--label-align-right', 'autocomplete' => 'off');

$project_id = array('project_id' => $project['id']);

$project_title = array(
	'name' => 'project_title',
	'id' => 'project_title',
	'class' => 'required form-control m-input m-input--air',
	'value' => $project['title']
);
$project_description = array(
	'name' => 'project_desc',
	'id' => 'project_desc',
	'class' => 'required form-control m-input m-input--air',
	'value' => $project['description']
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
		'afas' 			=> 'AFAS',
		'exactonline'	=> 'Exact Online',
		'visma' 		=> 'Visma',
		'twinfield' 	=> 'Twinfield',
		'accountview' => 'AccountView'
	)
);

$store_url = array(
	'name' => 'store_url',
	'id' => 'store_url',
	'class' => 'form-control m-input m-input--air',
	'value' => $project['store_url']
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
						<?php echo translate('Edit project'); ?> "<?php echo $project['title'];?>"
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
									<?php echo $project['title'];?>
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
						<?php $this->load->view(TEMPLATE.'/alerts/index'); ?>
						<div class="m-portlet__body">
							<div class="tab-content">
								<div class="tab-pane active" id="m_user_profile_tab_1">
								<?php echo form_open_multipart('projects/editaction', $form_attributes);?>
									<div class="m-portlet__body">
										<input type="hidden" id="upload_url" value="<?php echo site_url('/projects/upload_file'); ?>" />
										<input type="hidden" id="remove_url" value="<?php echo site_url('/projects/remove_file'); ?>" />
										<input type="hidden" id="disable_remove" value="" />
										<?php echo form_hidden($project_id);?>

										<div class="form-group">
										    <label for="contact_person" class="col-4 col-form-label">
										        <span class="label"> <b> <?php echo translate('Project Information');?> </b></span>
										    </label>
										    <hr size="5" color="#032f59" style="margin-top: 1rem; margin-left: 1rem; margin-right: 5rem;">
										</div>

										<?php if($permission!=''):?>
											<?php if($permission['project_title']!=''):?>
											<div class="form-field form-group m-form__group row">
												<label for="project_title" class="col-3 col-form-label">
													<span class="label">
														<?php echo translate('Project title');?> <span class="required"><font color="red">*</font></span>
													</span>
												</label>
												<div class="col-8">
													<?php
														if($permission['project_title']=='ve')
													 			echo form_input($project_title);
														else
														 echo $project['title'];
													?>

												</div>
											</div>
											<?php endif;?>
											<?php if($permission['project_desc']!=''): ?>
												<div class="form-field form-group m-form__group row">
													<label for="project_desc" class="col-3 col-form-label">
														<span class="label">
															<?php echo translate('Project description');?> <span class="required"><font color="red">*</font></span>
														</span>
													</label>
													<div class="col-8">
														<?php
																if($permission['project_desc']=='ve')
																		echo form_textarea($project_description);
																else
																 echo $project['description'];
														?>
													</div>
												</div>
										<?php endif;?>
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
											<?php
												echo form_dropdown('connection_type', $connection_type['values'],$project['connection_type'], 'class="form-control m-input m-input--air" id="connection_type" disabled="disabled"');
											?>
											</div>
										</div>
										<?php if($permission['erp_system']!=''): 
										?>
										<div class="form-field form-field-erp_system form-group m-form__group row" data-dependencies='{"connection_type":"2,1,5,6,7"}'>
												<label for="erp_system" class="col-3 col-form-label">
													<span class="label">
														<?php echo translate('ERP Software system');?>  <span class="required"><font color="red">*</font></span>
													</span>
												</label>
												<div class="col-8">
													<?php
															if($permission['erp_system']=='ve')
																	echo form_dropdown('erp_system', $erp_system['values'], $project['erp_system'],'class="form-control m-input m-input--air"');
															else
															 echo $project['erp_system'];
													?>
												</div>
											</div>
									<?php endif;?>
									<?php if($permission['store_url']!=''): ?>
										<div class="form-field form-field-webshop form-group m-form__group row" data-dependencies='{"connection_type":"3,1,4,5"}'>
												<label for="store_url" class="col-3 col-form-label">
													<span class="label">
														<?php echo translate('Webshop URL address');?> <span class="required"><font color="red">*</font></span>
													</span>
												</label>
												<div class="col-8">
													<?php
															if($permission['store_url']=='ve')
																	echo form_input($store_url);
															else
															 echo $project['store_url'];
													?>
												</div>
											</div>
										<?php endif;?>
									<?php if($permission['contact_person']!=''): ?>
										<div class="form-field form-group m-form__group row">
											<label for="contact_person" class="col-3 col-form-label">
												<span class="label">
													<?php echo translate('Contact person');?> <span class="required"><font color="red">*</font></span>
												</span>
											</label>
											<div class="col-8">
												<?php
														if($permission['contact_person']=='ve')
																echo form_dropdown('contact_person', $contacts, $project['contact_person'],'class="form-control m-input m-input--air"');
														else
														 echo $contacts[$project['contact_person']] ;
												?>
											</div>
										</div>
									<?php endif;?>
								<?php endif;?>

										<?php
										foreach($project_settings as $field):
										 	$value = $this->Projects_model->getValue($field['code'], $project['id']);
											if($field['permission']=='')
												continue;
											if($value == '' && isset($field['default'])) $value = $field['default'];
											if($field['headers']!=NULL):?>
												<div class="form-field form-group form-field-<?php echo $field['code']; ?> " data-dependencies='<?php echo isset($field['depends_on']) ? json_encode($field['depends_on']) : ''; ?>'>
													<span class="label"> <b> <?php echo $field['headers'];?> </b></span>
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
														if($field['permission']=='ve')
															if($field['code']=='exact_article_last_update_date' || $field['code']=='article_last_update_date' || $field['code']=='webshop_article_last_update_date'){
																if($value!='')
																	$value = date('Y-m-d H:i:00', strtotime($value));
																else if($field['code']=='nopcommerce_order_start_day' || $field['code']=='nopcommerce_order_start_day'){
																	$value = date('Y-m-d H:i:00', strtotime($value));
																} else
																	$value = date('Y-m-d H:i:00');
																echo form_input('settings['.$field['code'].']', $value,'class="form-control m-input m-input--air" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01]) (0[0-9]|1[0-9]|2[0123]):(0[0-9]|1[0-9]|2[0-9]|3[0-9]|4[0-9]|5[0-9]):(00)"');
															}
															else
																echo form_input('settings['.$field['code'].']', $value,'class="form-control m-input m-input--air"');
														else
														  echo $value;
														?>
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
											elseif($field['type'] == 'select'):
												$values = array();
												foreach($field['values'] as $code => $fieldValue){
													$values[$code] = translate($fieldValue);
												}
												?>
												<div class="form-field form-field-<?php echo $field['code']; ?> form-group m-form__group row" data-dependencies='<?php echo isset($field['depends_on']) ? json_encode($field['depends_on']) : ''; ?>'>
													<label for="<?php echo $field['code']; ?>" class="col-3 col-form-label">
															<span class="label">
																<?php echo translate($field['title']);?> <span class="required"><font color="red">*</font></span>
															</span>
														</label>
														<div class="col-8">
															<?php
															if($field['permission']=='ve')
																echo form_dropdown('settings['.$field['code'].']', $values, $value,'class="form-control m-input m-input--air" id='. $field['code']);
															else
																echo $value;
															?>
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
											<?php elseif($field['type'] == 'checkbox'): ?>
												<div class="form-field form-field-<?php echo $field['code']; ?> form-group m-form__group row" data-dependencies='<?php echo isset($field['depends_on']) ? json_encode($field['depends_on']) : ''; ?>'>
													<label for="<?php echo $field['code']; ?>" class="col-3 col-form-label">
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
                                            <?php elseif($field['type'] == 'button' && $field['code'] == 'update_opt_suppliers'): ;?>

                                                <div class="form-field form-field-<?php echo $field['code']; ?> form-group m-form__group row" data-dependencies='<?php echo isset($field['depends_on']) ? json_encode($field['depends_on']) : ''; ?>'>
                                                    <label for="<?php echo $field['code']; ?>" class="col-3 col-form-label">
                                                            <span class="label">
                                                                <?php echo translate($field['title']);?> <span class="required"><font color="red">*</font></span>
                                                            </span>
                                                    </label>
                                                    <div class="col-4">
                                                        <div style="display: none">
                                                            <input id="reset-optiply-sup" name="project_id" value="<?php echo $project_id["project_id"] ?>">
                                                        </div>
                                                        <button type="button" id="optiply-update-sup" class="add-row btn btn-primary">Reimport Suppliers</button>
                                                    </div>
                                                    <div class="col-4 ok-message" style="color: red; padding-top: 10px; display: none">
                                                        <span>Done</span>
                                                    </div>
                                                </div>
                                                <?php elseif($field['type'] == 'button' && $field['code'] == 'force_buy_orders'): ;?>

                                                <div class="form-field form-field-<?php echo $field['code']; ?> form-group m-form__group row" data-dependencies='<?php echo isset($field['depends_on']) ? json_encode($field['depends_on']) : ''; ?>'>
                                                    <label for="<?php echo $field['code']; ?>" class="col-3 col-form-label">
                                                            <span class="label">
                                                                <?php echo translate($field['title']);?> <span class="required"><font color="red">*</font></span>
                                                            </span>
                                                    </label>
                                                    <div class="col-4">
                                                        <div style="display: none">
                                                            <input name="project_id" value="<?php echo $project_id["project_id"] ?>">
                                                        </div>
                                                        <button type="button" id="force_buy_orders" class="add-row btn btn-primary">Check orders</button>
                                                    </div>
                                                    <div class="col-4 done-message" style="color: red; padding-top: 10px; display: none">
                                                        <span>In Progres</span>
                                                    </div>
                                                </div>
											<?php elseif($field['type'] == 'table'): ?>
												<?php
													$fields = array();
													foreach($field['fields'] as $code => $fieldField){
														$fields[$code] = translate($fieldField);
													}
													$values = array();
													if(!empty(json_decode($value, true))){
														$values = json_decode($value, true);
													}
												?>
												<div class="form-field form-field-<?php echo $field['code']; ?> form-group m-form__group row" data-dependencies='<?php echo isset($field['depends_on']) ? json_encode($field['depends_on']) : ''; ?>'>
													<label for="<?php echo $field['code']; ?>" class="col-3 col-form-label"><span class="label"><?php echo translate($field['title']);?></span><span class="required">*</span></label>
													<span>
														<table>
															<thead>
																<tr>
																	<?php foreach($fields as $code => $label): ?>
																		<th><?php echo translate($label); ?></th>
																	<?php endforeach; ?>
																</tr>
															</thead>
															<tbody>
																<?php reset($fields); $firstField = key($fields); ?>
																<?php if(isset($values[$firstField])): ?>
																	<?php foreach($values[$firstField] as $index => $firstFieldValue): ?>
																		<tr>
																			<?php foreach($fields as $code => $label): ?>
																				<?php $columnValue = isset($values[$code][$index]) ? $values[$code][$index] : ''; ?>
																				<?php if($field['permission']=='ve'){ ?>
																				<td><input class="form-control m-input m-input--air" type="text" name="settings[<?php echo $field['code']; ?>][<?php echo $code; ?>][]" value="<?php echo $columnValue; ?>" /></td>
																			<?php } else{ ?>
																				<td><input class="form-control m-input m-input--air" type="text" name="settings[<?php echo $field['code']; ?>][<?php echo $code; ?>][]" value="<?php echo $columnValue; ?>"  readonly="readonly" disabled="disabled"/></td>
																			<?php } ?>
																			<?php endforeach; ?>
																		</tr>
																	<?php endforeach; ?>
																<?php endif; ?>
																<tr class="row-init" style="display:none;">
																	<?php foreach($fields as $code => $label): ?>
																		<?php if($field['permission']=='ve'){ ?>
																		<td><input class="form-control m-input m-input--air" type="text" name="settings[<?php echo $field['code']; ?>][<?php echo $code; ?>][]" /></td>
																	<?php } else{ ?>
																		<td><input class="form-control m-input m-input--air" type="text" name="settings[<?php echo $field['code']; ?>][<?php echo $code; ?>][]" readonly="readonly" disabled="disabled"/></td>
																	<?php } ?>
																	<?php endforeach; ?>
																</tr>
															</tbody>
															<?php if($field['permission']=='ve'){ ?>
															<tfoot>
																<tr>
																	<td><button type="button" class="add-row btn btn-primary" style="    margin-top: 10px;">Nieuwe rij</button></td>
																</tr>
															</tfoot>
														<?php } ?>
														</table>
													</span>
												</div>
											<?php elseif($field['type'] == 'custom_table'): ?>
												<?php $this->load->view(TEMPLATE.'/projects/settings/custom_table/'.$field['code'], array('field' => $field, 'project_id' => $project['id'])); ?>
											<?php endif;
											 endforeach; ?>
									</div>
									<div class="m-portlet__foot m-portlet__foot--fit">
										<div class="m-form__actions">
											<div class="row">
												<div class="col-3"></div>
												<div class="col-4">
												<?php echo form_submit($submit); ?>
												</div>
												<div class="col-4 text-right">
													<span class="btn btn-danger"><a  href="<?php echo site_url('/projects/deleteaction/id/'.$project['id']);?>" onclick="return confirm('<?php echo translate('Are you sure you want to delete this project? This action can not be undone.');?>')" style="color:#fafafd"><?php echo translate('Delete project');?></a></span>
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
        	$('#connection_type').trigger('change');
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

	$('#optiply-update-sup').click(function () {

        var projectId = $('[name="project_id"]').val();

        $.ajax({
            url: '/index.php/projects/resetoptiplysuppliers',
            type: 'post',
            data: {'project_id': projectId}
        }).done(function (data) {
            if(data == 'ok')
                $('.ok-message').show();
        })
    });

    $('#force_buy_orders').click(function () {

        var projectId = $('[name="project_id"]').val();

        $.ajax({
            url: '/index.php/projects/reimportbuyorders',
            type: 'post',
            data: {'project_id': projectId}
        }).done(function (data) {
            if(data == 'ok')
                $('.done-message').show();
        })
    });
</script>
