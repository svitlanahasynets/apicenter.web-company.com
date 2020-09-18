<admin-permissions-component post-title="<?php echo translate('All users') ?>"  inline-template>
	<component :is="layout">
		<page-title :heading=heading :icon=icon></page-title>
			<div>
				<button class="m-aside-left-close m-aside-left-close--skin-light" id="m_aside_left_close_btn">
					<i class="la la-close"></i>
				</button>

				<div class="m-grid__item m-grid__item--fluid m-wrapper">
					<!-- <div class="m-subheader ">
						<div class="d-flex align-items-center">
							<div class="mr-auto">
								<h3 class="m-subheader__title m-subheader__title--separator">
									<?php echo translate('All users') ?>
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
										<a href="javascript:void(0);" class="m-nav__link">
											<span class="m-nav__link-text">
											<?php echo translate('All users') ?>
											</span>
										</a>
									</li>
								</ul>
							</div>
						</div>
					</div> -->
					<div class="m-content">
						<div class="row">
							<div class="col-xl-12">
								<div class="m-portlet m-portlet--mobile ">
									<div class="m-portlet__head">
										<div class="m-portlet__head-caption">
											<div class="m-portlet__head-title">
												<h3 class="m-portlet__head-text">
													<?php echo translate('All users'); ?>
												</h3>
											</div>
										</div>
										<?php
										if($this->Permissions_model->check_permission_user('create_user', '', $this->session->userdata('username')) == 've'): ?>
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
																			
																				<li class="m-nav__item">
																					<a href="<?php echo site_url('permissions/createuser');?>" class="m-nav__link"> 
																						<i class="m-nav__link-icon flaticon-add"></i>
																						<span class="m-nav__link-text">
																						<?php echo translate('Create user');?>
																						</span>
																					</a>
																				</li>
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
									<div class="m-portlet__body">
										<!--begin: Datatable -->
										<?php $this->load->view(TEMPLATE.'/alerts/index'); ?>
										<table class="table b-table b-table-stacked-md" id="">
											<thead>
												<tr>
													<th><?php echo translate('User ID');?></th>
													<th><?php echo translate('Username');?></th>
													<th><?php echo translate('First name');?></th>
													<th><?php echo translate('Last name');?></th>
													<th><?php echo translate('Job description');?></th>
													<th><?php echo translate('E-mail address');?></th>
													<th><?php echo translate('Phone number');?></th>
													<th><?php echo translate('Edit user');?></th>
													<th><?php echo translate('User information');?></th>
												</tr>
											</thead>
											<tbody>
											<?php $tr_count = 0; ?>
											<?php foreach($users as $user): ?>
												<?php
													$tr_count++;
													if($tr_count % 2){
														$tr_class = 'odd';
													} else {
														$tr_class = 'even';
													}
												?>
												<tr style="cursor: pointer;" class="<?php echo $tr_class;?>" onclick="App.navigateTo('<?php echo site_url('/permissions/viewuser/id/'.$user['user_id']);?>');">
													<td><?php echo $user['user_id'];?></td>
													<td><?php echo $user['user_name'];?></td>
													<td><?php echo $user['firstname'];?></td>
													<td><?php echo $user['lastname'];?></td>
													<td><?php echo $user['user_function'];?></td>
													<td><?php echo $user['user_email'];?></td>
													<td><?php echo $user['user_phone'];?></td>
													<td><b><a href="<?php echo site_url('/permissions/edituser/id/'.$user['user_id']);?>"><?php echo translate('Edit');?></a></b></td>
													<td><b><a href="<?php echo site_url('/permissions/viewuser/id/'.$user['user_id']);?>"><?php echo translate('View');?></a></b></td>
												</tr>
											<?php endforeach; ?>
										</tbody>
										</table>
										<!--end: Datatable -->
									</div>
								</div>
							</div>
						</div>
					</div> 
				</div>
			</div>
	</component>
</admin-permissions-component>
