<?php 
	$form_attributes = array('class' => 'projects_create_form m-form m-form--fit m-form--label-align-right', 'autocomplete' => 'off');
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
	<div class="m-grid__item m-grid__item--fluid m-wrapper">
		<div class="m-subheader ">
			<div class="d-flex align-items-center">
				<div class="mr-auto">
					<h3 class="m-subheader__title m-subheader__title--separator">
						<?php echo translate('Arrange Fields Orders') ?>
					</h3>
					<ul class="m-subheader__breadcrumbs m-nav m-nav--inline">
						<li class="m-nav__item m-nav__item--home">
							<a href="#" class="m-nav__link m-nav__link--icon">
								<i class="m-nav__link-icon la la-home"></i>
								<?php echo translate('All projects') ?>
							</a>
						</li>
					</ul>
				</div>
			</div>
		</div>

		<input type="hidden" name="update_url" id="update_url" value="<?php echo site_url('/projects/getResponse'); ?>" />
		<input type="hidden" name="number_of_pages" id="number_of_pages" value="1" />
		<input type="hidden" id="form-columns" value='<?php echo json_encode(array("project_id", "title", "store_url")); ?>' />
		<input type="hidden" id="form-columns-preferences" value='<?php echo json_encode(get_user_preference('columns_projects_index')); ?>' />
		<div class="m-content">
			<div class="row">
				<div class="col-xl-12">
					<div class="m-portlet m-portlet--mobile ">
						<?php $this->load->view(TEMPLATE.'/alerts/index'); ?>
						<div class="m-portlet__head">
							<div class="m-portlet__head-caption">
								<div class="m-portlet__head-title">
									<h3 class="m-portlet__head-text">
										<?php echo translate('Arrange Fields Orders') ?>
									</h3>
								</div>
							</div>						
						</div>
							<?php echo form_open_multipart('projects/saveArrangedOrders', $form_attributes);?>
						<div class="m-portlet__body">
							<div class="m-accordion m-accordion--default m-accordion--solid" id="m_accordion_3" role="tablist">

							<ol class='example'>
								<?php $order_val = '';
								 foreach ($project_from_settings as $key => $value) { 
								 	$order_val == '' ? ($order_val = $value['id']): ($order_val = $order_val.','.$value['id']);
								 	$help_option = ($value['help_option']!=null || $value['help_option']!='')? $value['help_option']:0;
								 	?>
									<li id="<?php echo $value['id']; ?>" style="padding: 6px; cursor: pointer;" > 
										<div class="m-accordion__item" style="padding: 6px;">
											<div class="m-accordion__item-head collapsed"  role="tab" id="m_accordion_3_item_<?php echo $value['id']; ?>_head" data-toggle="collapse" href="#m_accordion_3_item_<?php echo $value['id']; ?>_body" aria-expanded="false">
												<span class="m-accordion__item-title">
													<?php echo $value['title'];?>
												</span>
												<span class="m-accordion__item-mode"></span>
											</div>
											<div class="m-accordion__item-body collapse" id="m_accordion_3_item_<?php echo $value['id']; ?>_body" class=" " role="tabpanel" aria-labelledby="m_accordion_3_item_<?php echo $value['id']; ?>_head" data-parent="#m_accordion_3">
												<div class="m-accordion__item-content">
													<div class="row" style="padding-bottom: 10px;">
														<div class="col-1"> Header : </div>
														<div class="col-5">
															<input type="text" name="header_before_id[]" value="<?php echo $value['headers']; ?>" id="header_before_id_<?php echo $value['id']; ?>_id" class="input-long form-control m-input m-input--air">
														</div>
														<div class="col-1"> Label : </div>
														<div class="col-5">
															<input type="text" name="form_label_id[]" value="<?php echo $value['title']; ?>" id="form_label_id_<?php echo $value['id']; ?>_id" class="input-long form-control m-input m-input--air">
														</div>
													</div>
													<div class="row" style="padding-bottom: 10px;">
														<div class="col-2"> Help Option : </div>
														<div class="col-9">
															<div class="m-radio-inline">
																<label class="m-radio">
																	<input type="radio" name="help_option<?php echo $value['id']; ?>" value="0" id="help_option<?php echo $value['id']; ?>_id_0" <?php echo ($help_option==0)?'checked="checked"':'';?>>
																	None
																	<span></span>
																</label>
																<label class="m-radio">
																	<input type="radio" name="help_option<?php echo $value['id']; ?>" value="1" id="help_option<?php echo $value['id']; ?>_id_1" <?php echo ($help_option==1)?'checked="checked"':'';?>>
																	Text Message
																	<span></span>
																</label>
																<label class="m-radio">
																	<input type="radio" name="help_option<?php echo $value['id']; ?>" value="2" id="help_option<?php echo $value['id']; ?>_id_2" <?php echo ($help_option==2)?'checked="checked"':'';?>>
																	Url Link 
																	<span></span>
																</label>
																<label class="m-radio">
																	<input type="radio" name="help_option<?php echo $value['id']; ?>" value="3" id="help_option<?php echo $value['id']; ?>_id_3" <?php echo ($help_option==3)?'checked="checked"':'';?>>
																	Both
																	<span></span>
																</label>
															</div>
														</div>
													</div>

													<div class="row" id="tet<?=$value['id'];?>" style="padding-bottom: 10px;">
														<div class="col-1"> Message : </div>
														<div class="col-5">
															<textarea class="form-control m-input" id="help_message_id<?php echo $value['id']; ?>" rows="3" name="help_message<?php echo $value['id']; ?>"><?php echo $value['help_message']; ?> </textarea>
														</div>
														<div class="col-1"> Link : </div>
														<div class="col-5">
															<input type="text" name="help_url<?php echo $value['id']; ?>" value="<?php echo $value['help_url']; ?>" id="help_url_id<?php echo $value['id']; ?>_id" class="input-long form-control m-input m-input--air">
														</div>
													</div>
												</div>
											</div>
										</div>
									</li>
									<?php 
								} ?>
							</ol>
							</div>
						</div>
							<input type="hidden" name="fields_orders" id="fields_orders" value="<?php echo $order_val;?>">
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
				</div>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
$(function  () {
  $("ol.example").sortable({
  	stop : function(event, ui){
  		$('#fields_orders').val($(this).sortable('toArray'));
        }}
    );
});
</script>