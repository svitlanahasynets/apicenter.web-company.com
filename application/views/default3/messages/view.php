<messages-component post-title="<?php echo translate('Message View') ?>"  inline-template>
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
									<div class="m-portlet__head">
										
									</div>
									<div class="m-portlet__body" style="padding: 2.2rem 1.2rem;">
										<form id="message_form" action="<?php echo site_url('/messages/index'); ?>">
											<div class="m-section">
												<div class="m-section__content">
													<div class="row">
														<div class="col-md-12">
															<div class="form-field form-group m-form__group row mb-3">
																<div for="example-text-input" class="col-3 text-right">
																	<?php echo translate('Message ID');?> :
																</div>
																<div class="col-8">
																	<?php echo $message['message_id'];?>
																</div>
															</div>
															<div class="form-field form-group m-form__group row mb-3">
																<div for="example-text-input" class="col-3 text-right">
																	<?php echo translate('Sender');?> :
																</div>
																<div class="col-8">
																	<?php echo $message['message_sender'];?>
																</div>
															</div>
															<div class="form-field form-group m-form__group row mb-3">
																<div for="example-text-input" class="col-3 text-right">
																	<?php echo translate('Recipient');?> :
																</div>
																<div class="col-8">
																	<?php echo $message['message_receiver'];?>
																</div>
															</div>
															<div class="form-field form-group m-form__group row mb-3">
																<div for="example-text-input" class="col-3 text-right">
																	<?php echo translate('Type');?> :
																</div>
																<div class="col-8">
																	<?php
																		$type = array();
																		$type['E'] = 'Error messages';
																		$type['N'] = 'Notification';
																		$type['R'] = 'Report';
																		$type['U'] = 'Update messages';
																		$type['M'] = 'Maintenance';
																		$type['A'] = 'Administration messages';
																	?>
																	<?php echo $type[$message['type']];?>
																</div>
															</div>
															<div class="form-field form-group m-form__group row mb-3">
																<div for="example-text-input" class="col-3 text-right">
																	<?php echo translate('Subject');?> :
																</div>
																<div class="col-8">
																	<?php echo $message['subject'];?>
																</div>
															</div>
															<div class="form-field form-group m-form__group row mb-3">
																<div for="example-text-input" class="col-3 text-right">
																	<?php echo translate('Message Body');?> :
																</div>
																<div class="col-8">

																	<?php	

																		if(isset($message['file_type']) && in_array($message['file_type'], ['image/jpeg', 'image/png'])){

																	?>
																		<img style="max-height: 100px; max-width: 100px;" src="<?php echo site_url('/messages/imgView'); ?>?message_id=<?php echo $message['message_id']; ?>" class="rounded-circle" /><br/>
																	<?php
																			
																		} else {
																			echo nl2br($message['message_body']);
																		}
																	?>
																	
																</div>
															</div>
															<div class="form-field form-group m-form__group row mb-3">
																<div for="example-text-input" class="col-3 text-right">
																	<?php echo translate('Url');?> :
																</div>
																<div class="col-8">
																	<?php echo $message['url'];?>
																</div>
															</div>
															<div class="form-field form-group m-form__group row mb-3">
																<div for="example-text-input" class="col-3 text-right">
																	<?php echo translate('Date');?> :
																</div>
																<div class="col-8">
																	<?php echo $message['date'];?>
																</div>
															</div>
															<div class="form-field form-group m-form__group row mb-3">
																<div for="example-text-input" class="col-3 text-right">
																	<?php echo translate('isRead');?> :
																</div>
																<div class="col-8">
																	<?php echo $message['isRead'];?>
																</div>
															</div>
															<div class="form-field form-group m-form__group row mb-3">
																<div for="example-text-input" class="col-3 text-right">
																	<?php echo translate('Project Id');?> :
																</div>
																<div class="col-8">
																	<?php echo $message['project_id'];?>
																</div>
															</div>
															<div class="form-field form-group m-form__group row mb-3">
																<div for="example-text-input" class="col-3 text-right">
																	<?php echo translate('Visibility');?> :
																</div>
																<div class="col-8">
																	<?php echo $message['visibility'];?>
																</div>
															</div>
															<div class="form-field form-group m-form__group row mb-3">
																<div for="example-text-input" class="col-3 text-right">
																	<?php echo translate('Generated By');?> :
																</div>
																<div class="col-8">
																	<?php echo $message['generated_by'];?>
																</div>
															</div>
														</div>
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
