<admin-projectslist-component post-title="<?php echo translate('Optiply Sellorder') ?>"  inline-template>
	<component :is="layout">
		<page-title :heading=heading :icon=icon></page-title>
		<!-- <b-card class="main-card mb-4"> -->
			<div class="">
				<button class="m-aside-left-close m-aside-left-close--skin-light" id="m_aside_left_close_btn">
					<i class="la la-close"></i>
				</button>
				<div class="m-grid__item m-grid__item--fluid m-wrapper">
					<input type="hidden" name="update_url" id="update_url" value="<?php echo site_url('/logs/getResponse'); ?>" />
					<input type="hidden" name="number_of_pages" id="number_of_pages" value="1" />
					<input type="hidden" id="form-columns" value='<?php echo json_encode(array("project_id", "title", "store_url")); ?>' />
					<input type="hidden" id="form-columns-preferences" value='<?php echo json_encode(get_user_preference('columns_projects_index')); ?>' />
					<div class="">
						<div class="row" style="">
							<div class="col-xl-12">
								<div class="m-portlet m-portlet--mobile card ">
									<?php $this->load->view(TEMPLATE . '/alerts/index'); ?>
									<div class="m-portlet__head">
										<div class="m-portlet__head-caption">
											<div class="m-portlet__head-title">
												<h3 class="m-portlet__head-text">
													<span style="font-family: Roboto,sans-serif;">Exact Sell Orders Logs ( Total Exact Sell Orders Logs <span style="color: green"><?php echo $total_count; ?></span> | Generated Errors <span style="color: red"><?php echo $generated_error_count; ?></span> )</span>
												</h3>
											</div>
										</div>
										<div class="m-portlet__head-tools">
											<?php
												$suffix = '';
												if ($selected_project_id) {
													$suffix .= '?selected_project_id=' . $selected_project_id;
												}
												$site_url = '/logs/index/optiply_sellorder' . $suffix;
												$all_logs_url = $site_url . '&all_logs=1';
											?>
											<a class="btn btn-success btn-lg" style="font-family: Roboto,sans-serif; margin-right: 15px;" href="<?php echo site_url($all_logs_url); ?>"><?php echo translate('All Logs'); ?></a>
											<a class="btn btn-danger btn-lg" style="font-family: Roboto,sans-serif;" href="<?php echo site_url($site_url); ?>"><?php echo translate('Reset Control Log'); ?></a>
										</div>
									</div>
									<div class="m-portlet__body" style="padding: 2.2rem 1.2rem;">
										<div class="form-columns-switcher" style="display: none"></div>
										
										<form id="log_content_form" action="<?php echo site_url('/logs/index/optiply_sellorder'); ?>">
											<input type="hidden" name="selected_project_id" value="<?php echo $selected_project_id; ?>">
											<div class="m-section">
												<div class="m-section__content">
													
													<div class="row">
														<div class="col-md-6 col-sm-6">
															<div class="log-filters ma-3 mb-3">
																<h3 class="ma-3 mb-3">
																	Filter Options
																</h3>
																<div class="ma-3 mb-3">
																	<label>DateRange Filter</label>
																	<input type="text" name="daterange" id="daterange" value="<?php echo $daterange; ?>" class="pa-1 pull-right input-option" />
																</div>
																<div class="ma-3 mb-3">
																	<label>View only Error Logs</label>
																	<label class="switch pull-right">
																		<input type="checkbox" id="only_error_filter" name="only_error_filter" value="" <?php echo $only_error_filter == 'only_error_filter' ? 'checked' : ''; ?>>
																		<span class="slider round"></span>
																	</label>
																</div>
																<div class="ma-3 mb-3">
																	<label>Search Logs</label>
																	<input type="text" name="search_log" id="search_log" value="<?php echo $search_log; ?>" class="pa-1 pull-right input-option" />
																</div>
																
															</div>
														</div>
														<!-- <div class="col-md-6 col-sm-6">
															<div class="log-sort ma-3 mb-3">
																<h3 class="ma-3 mb-3">
																	Sort Options
																</h3>
																<div class="ma-3 mb-3">
																	<label>Sort by Date</label>
																	<label class="switch pull-right">
																		<input type="checkbox" checked id="sort_by_date" name="sort_by_date">
																		<span class="slider round"></span>
																	</label>
																</div>
																<div class="ma-3 mb-3">
																	<label>Sort by Name</label>
																	<label class="switch pull-right">
																		<input type="checkbox" checked id="sort_by_name" name="sort_by_name">
																		<span class="slider round"></span>
																	</label>
																</div>
																<div class="ma-3 mb-3">
																	<label>Additional Sort</label>
																	<label class="switch pull-right">
																		<input type="checkbox" id="additional_sort" name="additional_sort">
																		<span class="slider round"></span>
																	</label>
																</div>
																
															</div>
														</div> -->
													</div>
														
													
													<div class="table_responsive" style="display: block; width: 100%; overflow-x: hidden; -webkit-overflow-scrolling: touch; -ms-overflow-style: -ms-autohiding-scrollbar;">
														<table class="table b-table b-table-stacked-md">
															<thead>
																<tr>
																	<th data-column="date" style="width: 25.22% !important; "><?php echo translate('Date'); ?></th>
																	<th data-column="error"><?php echo translate('Error'); ?></th>
																	<th data-column="description" style="width: 68.42%;"><?php echo translate('Description'); ?></th>
																</tr>
															</thead>
															<tbody>
																<?php
																	if (count($log_contents) > 0) {
																?>
		                                                            <tr class="filters">
																		<td data-column="date" scope="row">
																			<select name="date" class="form-control m-input m-input--air selected-date-sort">
																				<option value=""> --- </option>
																				<option value="new" 
																					<?php
																					 	if($selected_date_sort == 'new'){
																							echo 'selected';
																						} 
																					?>
																				>New
																				</option> 
																				<option value="old" 
																					<?php
																					 	if($selected_date_sort == 'old'){
																							echo 'selected';
																						} 
																					?>
																				>Old
																				</option>
																			</select>					
																		</td>
																		<td data-column="error" scope="row">
																			<select name="error_sort" class="form-control m-input m-input--air" id="error_sort">
																				<option value=""> --- </option>
																				<option value="yes" 
																					<?php
																					 	if($error_sort == 'yes'){
																							echo 'selected';
																						} 
																					?>
																				>Yes
																				</option> 
																				<option value="no" 
																					<?php
																					 	if($error_sort == 'no'){
																							echo 'selected';
																						} 
																					?>
																				>No
																				</option>
																			</select>					
																		</td>
																		<td data-column="description" scope="row">
																			<select name="name_sort" class="form-control m-input m-input--air" id="name_sort">
																				<option value=""> --- </option>
																				<option value="asc" 
																					<?php
																					 	if($name_sort == 'asc'){
																							echo 'selected';
																						} 
																					?>
																				>ASC
																				</option> 
																				<option value="desc" 
																					<?php
																					 	if($name_sort == 'desc'){
																							echo 'selected';
																						} 
																					?>
																				>DESC
																				</option>
																			</select>					
																		</td>
																	</tr>
																<?php
																	}
																?>
																<?php $tr_count = 0; ?>
																<?php foreach($log_contents as $log_content): ?>
																	<?php		
																		$tr_count++;
																		if($tr_count % 2){
																			$tr_class = 'odd';
																		} else {
																			$tr_class = 'even';
																		}
																	?>
																		<tr style="cursor: pointer;" class="<?php echo $tr_class;?>" onclick="">
																			<td data-column="date"><?php echo $log_content['timestamp'];?></td>
																			<td data-column="error" style="text-align: center;">
																				<?php
																				 	if($log_content['is_error'] == 1){
																				?>
																					<i class="fa fa-exclamation" style="color: red;"></i>
																				
																				<?php		
																					}
																				?>
																			</td>
																			<td title="log description>" data-column="description"><?php echo $log_content['message'];?></td>
																		</tr>
																<?php endforeach; ?>
															</tbody>
															
														</table>
													</div>
												</div>
												<div class="pagination-section">
													<p>Showing <?php echo $from;?> to <?php echo $to;?> of <?php echo $total;?> logs</p>
													<p class="pagination"><?php echo $links; ?></p>
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
		<!-- </b-card> -->
	</component>
</admin-projectslist-component>