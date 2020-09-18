<div class="m-grid__item m-grid__item--fluid  m-grid m-grid--ver-desktop m-grid--desktop 	m-container m-container--responsive m-container--xxl m-page__container m-body">
	<button class="m-aside-left-close m-aside-left-close--skin-light" id="m_aside_left_close_btn">
		<i class="la la-close"></i>
	</button>
	<div class="m-grid__item m-grid__item--fluid m-wrapper">
		<div class="m-subheader ">
			<div class="d-flex align-items-center">
				<div class="mr-auto">
					<h3 class="m-subheader__title m-subheader__title--separator">
						<?php echo translate('All projects') ?>
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
			<div class="row" style="margin-right: -42px; margin-left: -42px;">
				<div class="col-xl-12">
					<div class="m-portlet m-portlet--mobile ">
						<?php $this->load->view(TEMPLATE.'/alerts/index'); ?>
						<div class="m-portlet__head">
							<div class="m-portlet__head-caption">
								<div class="m-portlet__head-title">
									<h3 class="m-portlet__head-text">
										<?php echo translate('All projects') ?>
									</h3>
								</div>
							</div>
							<?php if($this->Permissions_model->check_permission_user('create_project', 0) == 've'): ?>
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
																	<?php if($this->Permissions_model->check_permission_user('create_project', 0) == 've'): ?>
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
							<form id="filter-form">
								<div class="m-section">
									<div class="m-section__content">
										<div class="table_responsive" style="display: block; width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; -ms-overflow-style: -ms-autohiding-scrollbar;">
										<table class="table m-table m-table--head-bg-brand data-table">
											<thead>
												<tr>
													<th data-column="project_id" style="width: 5.22% !important; "><?php echo translate('Project ID');?></th>
													<th data-column="erp_system" style="width: 8.42%;"><?php echo translate('Type');?></th>
													<th data-column="web_shop" style="width: 8.42%;"><?php echo translate('Webshop');?></th>
													<!--<th data-column="web_shop" style="width: 8.42%;"><?php echo translate('Market');?></th>-->
													<!--<th data-column="web_shop" style="width: 8.42%;"><?php echo translate('Point Of Sale');?></th>-->
													<!--<th data-column="web_shop" style="width: 8.42%;"><?php echo translate('PIM System');?></th>-->
													<th data-column="title" style="width: 8.42%;"><?php echo translate('Project title');?></th>
													<th data-column="store_url" style="width: 8.42%;"><?php echo translate('Webshop URL address');?></th>
													<th data-column="store_url" style="width: 5.22%;"><?php echo translate('Active');?></th>
												</tr>
											</thead>
											<tbody>
												<tr class="filters">
													<td data-column="project_id" scope="row"><?php echo form_input(array('name' => 'id', 'id' => 'id', 'class' => 'input-short form-control m-input m-input--air','width'=>'120px;', 'style'=>'width:70px;')); ?></td>

													<td data-column="erp_system"><?php echo form_dropdown('erp_system', array('' => translate('All'), 'afas' => 'AFAS', 'exactonline' => 'Exact Online','twinfield'=> 'Twinfield'),'','class="form-control m-input m-input--air"'); ?></td>

													<td data-column="web_shop"><?php echo form_dropdown('web_shop', array('' => translate('All'), 'magento2' => 'Magento 2', 'WooCommerce' => 'WooCommerce','mailchimp'=> 'Mailchimp','vtiger'=> 'Vtiger', 'shopify'=> 'Shopify', 'lightspeed' => 'Lightspeed', 'cscart' => 'CS-Cart', 'optiply' => 'Optiply', 'moodle' => 'Moodle'),'','class="form-control m-input m-input--air"'); ?></td>
													
													<!--<td data-column="market"><?php echo form_dropdown('market', array('' => translate('All'), 'bol'=>'Bol.com','Amazon'=>'Amazon'),'','class="form-control m-input m-input--air"'); ?></td>-->

													<!--<td data-column="pos"><?php echo form_dropdown('pos', array('' => translate('All'), 'mplus' => 'MPlus'),'','class="form-control m-input m-input--air"'); ?></td>-->
													
													<!--<td data-column="pim"><?php echo form_dropdown('pim', array('' => translate('All'), 'akeneo'=>'Akeneo'),'','class="form-control m-input m-input--air"'); ?></td>-->

													<td data-column="title"><?php echo form_input(array('name' => 'title', 'id' => 'title', 'class' => 'input-long form-control m-input m-input--air')); ?></td>
													
													<td data-column="store_url"><?php echo form_input(array('name' => 'store_url', 'id' => 'store_url', 'class' => 'input-long form-control m-input m-input--air')); ?></td>

													<td data-column="active"><?php echo form_dropdown('active', array('' => translate('All'), '1' => 'Ja', '0' => 'Nee'),'','class="form-control m-input m-input--air"'); ?></td>

												</tr>
											</tbody>
											<tfoot>
												<tr>
													<td colspan="25" scope="row" align="center" style="background-color: #033058; color: #fff;">
														<span class="pager-button table-first-page"><<</span>
														<span class="pager-button table-previous-page"><</span>
														<span class="pager-button table-current-page">
															<?php echo translate("Page"); ?> <input type="number" name="current_page" id="current_page" min="1" class="input-short align-center" value="1" />
														</span>
														<span class="pager-button table-number-of-pages">of <span></span></span>
														<span class="pager-button table-next-page">></span>
														<span class="pager-button table-last-page">>></span>
													</td>
												</tr>
											</tfoot>
										</table>
									    </div>
									</div>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>