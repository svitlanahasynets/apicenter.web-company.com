<messages-component post-title="<?php echo translate('Message center') ?>"  inline-template>
	<component :is="layout">
		<page-title :heading=heading :icon=icon></page-title>
		<!-- <b-card class="main-card mb-4"> -->
			<div class="content">
				<button class="m-aside-left-close m-aside-left-close--skin-light" id="m_aside_left_close_btn">
					<i class="la la-close"></i>
				</button>
				<div class="m-grid__item m-grid__item--fluid m-wrapper">
					<input type="hidden" name="update_url" id="update_url" value="<?php echo site_url('/messages/index'); ?>" />
					<div class="">
						<div class="row" style="">
							<div class="col-xl-12">
								<div class="m-portlet m-portlet--mobile card ">
									<?php $this->load->view(TEMPLATE . '/alerts/index'); ?>
									<div class="m-portlet__body" style="padding: 2.2rem 1.2rem;">
										<div class="row p-3">
											<div class="col-12 text-right">
												<a href="<?php echo site_url('/myaccount');?>"><i class="fa fa-cog" style="font-size: 35px;"></i></a>
											</div>
										</div>
										<form id="message_form" action="<?php echo site_url('/messages/index'); ?>">
											<div class="m-section">
												<div class="m-section__content">
													<div class="row">
														<div class="col-md-12">
															<h2>Message box</h2>
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
																			$first_index = intval(substr($binary_str, -1, 1));

																			$tr_bold_style = '';

																			if (!$first_index || strlen($binary_str) < 1) {
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
</messages-component>