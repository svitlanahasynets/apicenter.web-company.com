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
									<?php echo translate('Project'); ?> "<?php echo $project['title'];?>"
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
										<?php echo translate('Project'); ?> "<?php echo $project['title'];?>"
									</h3>
								</div>
							</div>

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
																	<?php echo translate('Projects'); ?>
																	</span>
																</li>
																<li class="m-nav__item">
																	<a href="<?php echo site_url('projects/index');?>" class="m-nav__link">
																		<i class="m-nav__link-icon flaticon-add"></i>
																		<span class="m-nav__link-text">
																		<?php echo translate('All projects');?>
																		</span>
																	</a>
																</li>
																<?php if($this->Permissions_model->check_permission_user('create_project', 0) == 've'): ?>
																<li class="m-nav__item">
																	<a href="<?php echo site_url('projects/create');?>" class="m-nav__link"><i class="m-nav__link-icon flaticon-add"></i>
																		<span class="m-nav__link-text">
																		<?php echo translate('Create project');?>
																		</span>
																	</a>
																</li>
															<?php endif;?>
															</ul>
														</div>
													</div>
												</div>
											</div>
										</div>
									</li>
								</ul>
							</div>
						</div>
						<div class="m-portlet__body">
							<div class="m-portlet m-portlet--bordered m-portlet--unair">
								<div class="m-portlet__body">
									<?php $this->load->view(TEMPLATE.'/projects/view/actions', array('project' => $project)); ?>

									<?php $this->load->view(TEMPLATE.'/projects/view/settings', array('project' => $project)); ?>
								</div>
								<div class="col-lg-12 col-md-12 col-sm-12">
									<div class="col-lg-12 col-md-12 col-sm-12">
										<?php $this->load->view(TEMPLATE.'/projects/view/log', array('project' => $project)); ?>
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
<script>
  $('textarea').highlightTextarea({
    words: ['Failed']
  });
</script>
<script type="text/javascript">


	// $("div:contains('Failed')").each( function( i, element ) {
 //      	var content = $(element).html();
 //      	content = content.replace( 'Failed', '<font color="red">Failed</font>' );
 //      	$(element).html(content);
 // 	});
</script>