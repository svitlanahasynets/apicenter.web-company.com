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
								 	?>
									<li id="<?php echo $value['id']; ?>" style="padding: 7px; cursor: pointer;" > 
										<div class="m-accordion__item" style="padding: 7px;">
											<div class="row">
												<div class="col-1"> Header : </div>
												<div class="col-6">
													<input type="text" name="header_before_id[]" value="<?php echo $value['headers']; ?>" id="header_before_id_<?php echo $value['id']; ?>_id" class="input-long form-control m-input m-input--air">
												</div>
												<div class="col-5">
													<span class="m-badge m-badge--metal m-badge--wide" style="margin: 5px;"><b><?php echo $value['title'];?></b></span> 
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