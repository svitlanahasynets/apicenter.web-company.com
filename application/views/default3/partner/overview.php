<partner-overview-component post-title="<?php echo translate('Partner overview') ?>"  inline-template>
	<component :is="layout">
		<page-title :heading=heading :icon=icon></page-title>
		<!-- <b-card class="main-card mb-4"> -->
			<div class="content">
				<button class="m-aside-left-close m-aside-left-close--skin-light" id="m_aside_left_close_btn">
					<i class="la la-close"></i>
				</button>
				<div class="m-grid__item m-grid__item--fluid m-wrapper">
					<input type="hidden" name="update_url" id="update_url" value="<?php echo site_url('/partner-overview'); ?>" />
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
										<div class="form-columns-switcher" style="display: none"></div>
										
										<form id="message_form" action="<?php echo site_url('/partner-overview'); ?>">
											<div class="m-section">
												<div class="m-section__content">
													<div class="row">
														<div class="col-md-12">
															<h3 class="mb-3"><?php echo translate('Message Box');?></h3>
															<table class="table b-table b-table-stacked-md" id="">
																<thead>
																	<tr>
																		<th><?php echo translate('Date');?></th>
																		<th><?php echo translate('From');?></th>
																		<th><?php echo translate('Subject');?></th>
																		<th></th>
																	</tr>
																</thead>
																<tbody>
																	<?php $tr_count = 0; ?>
																	<?php foreach($messages as $message): ?>
																		<?php
																			$tr_count++;
																			$tr_class = '';

																			if($tr_count % 2){
																				$tr_class = 'odd';
																			} else {
																				$tr_class = 'even';
																			}

																			$binary_str = strval(decbin($message['visibility']));
																			$third_index = intval(substr($binary_str, -3, 1));

																			$tr_bold_style = '';

																			if (!$third_index || strlen($binary_str) < 3) {
																				$tr_bold_style .= ' display: none;';
																			}

																			if(!$message['isRead']){
																				$tr_bold_style .= ' font-weight: bold;';
																			}
																		?>
																		<tr style="cursor: pointer; <?php echo $tr_bold_style;?>" class="<?php echo $tr_class;?>">
																			<td onclick="App.navigateTo('<?php echo site_url('/messages/view/id/'.$message['message_id']);?>');"><?php echo $message['date'];?></td>
																			<td onclick="App.navigateTo('<?php echo site_url('/messages/view/id/'.$message['message_id']);?>');"><?php echo $message['message_sender'];?></td>
																			<td onclick="App.navigateTo('<?php echo site_url('/messages/view/id/'.$message['message_id']);?>');"><?php echo $message['subject'];?></td>
																			<td><a href="<?php echo site_url('/messages/delete/id/'.$message['message_id']);?>" class="btn btn-warning delete-message" data-message-id="<?php echo $message['message_id'];?>"><?php echo translate('Delete');?> <i class="fa fa-trash"></i></a></td>
																		</tr>
																	<?php endforeach; ?>
																</tbody>
															</table>
														</div>
													</div>
													<div class="pagination-section">
														<p>Showing <?php echo $from;?> to <?php echo $to;?> of <?php echo $total;?> messages</p>
														<p class="pagination"><?php echo $links; ?></p>
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
		<!-- </b-card> -->
	</component>
</partner-overview-component>