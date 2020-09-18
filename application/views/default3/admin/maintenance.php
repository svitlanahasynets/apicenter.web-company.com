<admin-maintenance-component post-title="<?php echo translate('Maintenance') ?>"  inline-template>
	<component :is="layout">
		<page-title :heading=heading :icon=icon></page-title>
		<!-- <b-card class="main-card mb-4"> -->
			<div class="content">
				<div class="m-grid__item m-grid__item--fluid m-wrapper">
					<input type="hidden" name="update_url" id="update_url" value="<?php echo site_url('/admin-maintenance'); ?>" />
					<div class="">
						<div class="row" style="">
							<div class="col-xl-12">
								<div class="m-portlet m-portlet--mobile card ">
									<?php $this->load->view(TEMPLATE . '/alerts/index'); ?>
									<div class="m-portlet__head">
										<div class="m-portlet__head-caption">
											<div class="m-portlet__head-title">
												<h3 class="m-portlet__head-text">
													<span style="font-family: Roboto,sans-serif;">
													</span>
												</h3>
											</div>
										</div>
										<div class="m-portlet__head-tools">
											
										</div>
									</div>
									<div class="m-portlet__body" style="padding: 2.2rem 1.2rem;">
										<input type="hidden" name="remove_files" id="remove_files" value="<?php echo site_url('/admin-remove-tmp-files'); ?>">
										<div class="m-section">
											<div class="m-section__content">
												<div role="alert" class="alert alert-success" style="display: none;">
												</div>
												<div class="row">
													<div class="col-md-12">
														<div id="project_files_remove_section">
															<table class="table b-table b-table-stacked-md">
																<thead>
																	<tr>
																		<th data-column="id" style="width: 5.22% !important; "><?php echo translate('Project ID'); ?></th>
																		<th data-column="title" style="width: 8.42%;"><?php echo translate('Project title'); ?></th>
																		<th data-column="erp_system" style="width: 8.42%;"><?php echo translate('Type'); ?></th>
																		<th data-column="store_url" style="width: 8.42%;"><?php echo translate('Webshop URL address'); ?></th>
																		<th data-column="action" style="width: 5.22%;"><?php echo translate('Action'); ?></th>
																	</tr>
																</thead>
																<tbody>

																	<?php $tr_count = 0; ?>
																	<?php foreach($projects as $project): ?>
																		<?php		
																			$tr_count++;
																			if($tr_count % 2){
																				$tr_class = 'odd';
																			} else {
																				$tr_class = 'even';
																			}

																			$folder = DATA_DIRECTORY . '/tmp_files/' . $project['id'];
         
																	        //Get a list of all of the file names in the folder.
																	        $files = glob($folder . '/*');
																	        $files_count = count($files);
																		?>
																		<tr class="<?php echo $tr_class;?>">
																			<td data-column="project_id"><?php echo $project['id'];?></td>
																			<td title="<?php echo $project['title']; ?>" data-column="title"><?php echo $project['title'];?></td>
																			<td title="<?php echo get_erp_system_label($project['erp_system']); ?>" data-column="erp_system"><?php echo get_erp_system_label($project['erp_system']);?></td>					
																			<td title="<?php echo $project['store_url']; ?>" data-column="store_url"><?php echo $project['store_url'];?></td>
																			<td>
																				<button type="button" data-project-id="<?php echo $project['id'];?>" data-project-name="<?php echo $project['title'];?>" class="btn btn-warning remove-files" <?php echo ($files_count > 0 ? '' : 'disabled'); ?>>Remove Files</button>
																			</td>
																		</tr>
																	<?php endforeach; ?>
																</tbody>															
															</table>
																
															


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
				</div>
			</div>
		<!-- </b-card> -->
	</component>
</admin-maintenance-component>