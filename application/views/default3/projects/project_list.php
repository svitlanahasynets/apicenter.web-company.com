<admin-projectslist-component post-title="<?php echo translate('Project List') ?>"  inline-template>
	<component :is="layout">
		<page-title :heading=heading :icon=icon></page-title>
		<!-- <b-card class="main-card mb-4"> -->
			<div class="">
				<button class="m-aside-left-close m-aside-left-close--skin-light" id="m_aside_left_close_btn">
					<i class="la la-close"></i>
				</button>
				<div class="m-grid__item m-grid__item--fluid m-wrapper">
					<input type="hidden" name="update_url" id="update_url" value="<?php echo site_url('/projects/projectList'); ?>" />
					<input type="hidden" name="number_of_pages" id="number_of_pages" value="1" />
					<input type="hidden" id="form-columns" value='<?php echo json_encode(array("project_id", "title", "store_url")); ?>' />
					<input type="hidden" id="form-columns-preferences" value='<?php echo json_encode(get_user_preference('columns_projects_index')); ?>' />
					<div class="">
						<div class="row" style="">
							<div class="col-xl-12">
								<div class="m-portlet m-portlet--mobile card ">
									<?php $this->load->view(TEMPLATE . '/alerts/index'); ?>
									<div class="m-portlet__head">
										<?php if ($this->Permissions_model->check_permission_user('create_project', 0) == 've') : ?>
											<div class="m-portlet__head-tools">
												<ul class="m-portlet__nav">
													<li class="m-portlet__nav-item">
														<div class="m-dropdown m-dropdown--inline m-dropdown--arrow m-dropdown--align-right m-dropdown--align-push" data-dropdown-toggle="hover" aria-expanded="true">
															<a href="#" class="m-portlet__nav-link btn btn-lg btn-secondary  m-btn m-btn--icon m-btn--icon-only m-btn--pill  m-dropdown__toggle">
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
																						<?php echo translate('Manage projects') ?>
																					</span>
																				</li>

																				<li class="m-nav__item">
																					<span class="m-nav__link-text">
																						<div class="project-action form-action table-columns-preferences"><span class="icon fa fa-columns"></span><span> <?php echo translate('Columns'); ?></span></div>
																					</span>
																				</li>
																				<?php if ($this->Permissions_model->check_permission_user('create_project', 0) == 've') : ?>
																					<li class="m-nav__item">
																						<span class="m-nav__link-text"><span class="icon fa fa-plus-square"></span>
																							<?php echo anchor(site_url('/projects/create'), translate('Create project')); ?>
																						</span>
																					</li>
																					<li class="m-nav__item">
																						<span class="m-nav__link-text"><span class="icon fa fa-check"></span>
																							<?php echo anchor(site_url('/projects/manageFormFieldsOrders'), translate('Arrange Fields Order')); ?>
																						</span>
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
									<div class="m-portlet__body" style="padding: 2.2rem 1.2rem;">
										<div class="form-columns-switcher" style="display: none"></div>
										<form id="filter-form" action="<?php echo site_url('/projects/projectList'); ?>">
											<div class="m-section">
												<div class="m-section__content">
													<div class="table_responsive" style="display: block; width: 100%; overflow-x: hidden; -webkit-overflow-scrolling: touch; -ms-overflow-style: -ms-autohiding-scrollbar;">
														<table class="table b-table b-table-stacked-md">
															<thead>
																<tr>
																	<th data-column="id" style="width: 5.22% !important; "><?php echo translate('Project ID'); ?></th>
																	<th data-column="erp_system" style="width: 8.42%;"><?php echo translate('Type'); ?></th>
																	<th data-column="web_shop" style="width: 8.42%;"><?php echo translate('Webshop'); ?></th>
																	<th data-column="web_shop" style="width: 8.42%;"><?php echo translate('Market'); ?></th>
																	<th data-column="title" style="width: 8.42%;"><?php echo translate('Project title'); ?></th>
																	<th data-column="store_url" style="width: 8.42%;"><?php echo translate('Webshop URL address'); ?></th>
																	<th data-column="store_url" style="width: 5.22%;"><?php echo translate('Active'); ?></th>
																</tr>
															</thead>
															<tbody>
                                                            <tr class="filters">
																	<td data-column="id" scope="row">
																		<input type="text" name="id" value="<?php echo $selected_id; ?>" id="id" width="120px;" class="input-short form-control m-input m-input--air" style="width: 70px;">
																	</td>

																	<td data-column="erp_system">
																		<select name="erp_system" class="form-control m-input m-input--air">
																			<option value="" <?php echo $selected_erp_system == '' ? 'selected' : ''; ?>>Alle</option> 
																			<option value="afas" <?php echo $selected_erp_system == 'afas' ? 'selected' : ''; ?>>AFAS</option> 
																			<option value="exactonline" <?php echo $selected_erp_system == 'exactonline' ? 'selected' : ''; ?>>Exact Online</option> 
																			<option value="twinfield" <?php echo $selected_erp_system == 'twinfield' ? 'selected' : ''; ?>>Twinfield</option>
																		</select>
																	</td>

																	<td data-column="web_shop">
																		<select name="web_shop" class="form-control m-input m-input--air">
																			<option value="" <?php echo $selected_web_shop == '' ? 'selected' : ''; ?>>Alle</option> 
																			<option value="magento2" <?php echo $selected_web_shop == 'magento2' ? 'selected' : ''; ?>>Magento 2</option> 
																			<option value="WooCommerce" <?php echo $selected_web_shop == 'WooCommerce' ? 'selected' : ''; ?>>WooCommerce</option> 
																			<option value="mailchimp" <?php echo $selected_web_shop == 'mailchimp' ? 'selected' : ''; ?>>Mailchimp</option> 
																			<option value="vtiger" <?php echo $selected_web_shop == 'vtiger' ? 'selected' : ''; ?>>Vtiger</option> 
																			<option value="shopify" <?php echo $selected_web_shop == 'shopify' ? 'selected' : ''; ?>>Shopify</option> 
																			<option value="lightspeed" <?php echo $selected_web_shop == 'lightspeed' ? 'selected' : ''; ?>>Lightspeed</option> 
																			<option value="cscart" <?php echo $selected_web_shop == 'cscart' ? 'selected' : ''; ?>>CS-Cart</option>
																		</select>
																	</td>

																	<td data-column="market">
																		<select name="market" class="form-control m-input m-input--air">
																			<option value="" <?php echo $selected_market == '' ? 'selected' : ''; ?>>Alle</option> 
																			<option value="bol" <?php echo $selected_market == 'bol' ? 'selected' : ''; ?>>Bol.com</option> 
																			<option value="Amazon" <?php echo $selected_market == 'Amazon' ? 'selected' : ''; ?>>Amazon</option>
																		</select>
																	</td>

																	<td data-column="title">
																		<input type="text" name="title" value="<?php echo $selected_title; ?>" id="title" class="input-long form-control m-input m-input--air">
																	</td>

																	<td data-column="store_url">
																		<input type="text" name="store_url" value="<?php echo $selected_store_url; ?>" id="store_url" class="input-long form-control m-input m-input--air">
																	</td>

																	<td data-column="active">
																		<select name="active" class="form-control m-input m-input--air">
																			<option value="" selected="selected">Alle</option> 
																			<option value="1">Ja</option> 
																			<option value="0">Nee</option>
																		</select>
																	</td>
																</tr>

																<?php $tr_count = 0; ?>
																<?php foreach($projects as $project): ?>
																	<?php		
																		$tr_count++;
																		if($tr_count % 2){
																			$tr_class = 'odd';
																		} else {
																			$tr_class = 'even';
																		}
																	?>
																		<tr style="cursor: pointer;" class="<?php echo $tr_class;?>" onclick="App.navigateTo('<?php echo site_url('/projects/edit/id/'.$project['id']);?>');">
																			<td data-column="project_id"><?php echo $project['id'];?></td>
																			<td title="<?php echo get_erp_system_label($project['erp_system']); ?>" data-column="erp_system"><?php echo get_erp_system_label($project['erp_system']);?></td>
																			<td title="<?php echo $controller->webshopaname($project['id'],'webs_shop'); ?>" data-column="title"><?php echo $controller->webshopaname($project['id'],'webs_shop'); ?></td>
																			<td title="<?php echo $controller->webshopaname($project['id'],'market'); ?>" data-column="title"><?php echo $controller->webshopaname($project['id'],'market'); ?></td>
																			<td title="<?php echo $project['title']; ?>" data-column="title"><?php echo $project['title'];?></td>
																			<td title="<?php echo $project['store_url']; ?>" data-column="store_url"><?php echo $project['store_url'];?></td>
																			<td title="<?php echo $controller->connectionStatus($project['id']); ?>" data-column="title"><?php echo $controller->connectionStatus($project['id']); ?></td>
																		</tr>
																<?php endforeach; ?>
															</tbody>															
														</table>
													</div>
												</div>
												<p class="pagination"><?php echo $links; ?></p>
											</div>
										</form>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		<!-- </b-card> -->
	</component>
</admin-projectslist-component>