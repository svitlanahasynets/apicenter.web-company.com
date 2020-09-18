<admin-test-api-connection-component post-title="<?php echo translate('Test Api Connection') ?>"  inline-template>
	<component :is="layout">
		<page-title :heading=heading :icon=icon></page-title>
		<!-- <b-card class="main-card mb-4"> -->
			<div class="content">
				<button class="m-aside-left-close m-aside-left-close--skin-light" id="m_aside_left_close_btn">
					<i class="la la-close"></i>
				</button>
				<div class="m-grid__item m-grid__item--fluid m-wrapper">
					<input type="hidden" name="update_url" id="update_url" value="<?php echo site_url('/admin-test-api-connection'); ?>" />
					<div class="row" style="">
						<div class="col-xl-12 col-md-12">
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
									<form id="test_api_connection_form" action="<?php echo site_url('/admin-test-api-connection'); ?>">
										<div class="m-section">
											<div class="m-section__content">
												<div class="row">
													<div class="col-md-12">
														<fieldset id="api_request">
															<ul class="api-request-section-header pa-10">
																<li>
																	<label for="method">Method</label> 
																	<span class="select-wrapper">
																		<select id="api_request_method" class="html-element" name="api_request_method">
																			<option value="GET" <?php echo $api_request_method == "GET" ? "selected" : "" ?>>GET</option> 
																			<option value="HEAD" <?php echo $api_request_method == "HEAD" ? "selected" : "" ?>>HEAD</option> 
																			<option value="POST" <?php echo $api_request_method == "POST" ? "selected" : "" ?>>POST</option> 
																			<option value="PUT" <?php echo $api_request_method == "PUT" ? "selected" : "" ?>>PUT</option> 
																			<option value="DELETE" <?php echo $api_request_method == "DELETE" ? "selected" : "" ?>>DELETE</option> 
																			<option value="CONNECT" <?php echo $api_request_method == "CONNECT" ? "selected" : "" ?>>CONNECT</option> 
																			<option value="OPTIONS" <?php echo $api_request_method == "OPTIONS" ? "selected" : "" ?>>OPTIONS</option> 
																			<option value="TRACE" <?php echo $api_request_method == "TRACE" ? "selected" : "" ?>>TRACE</option> 
																			<option value="PATCH" <?php echo $api_request_method == "PATCH" ? "selected" : "" ?>>PATCH</option>
																		</select>
																	</span>
																</li> 
																<li style="width: 60%;">
																	<label for="api_request_url">URL</label> 
																	<input id="api_request_url" name="api_request_url" type="url" class="html-element" value="<?php echo $api_request_url ?>">
																</li>																	
																<li>
																	<label for="api_request_send" class="hide-on-small-screen">&nbsp;</label> 
																	<button id="api_request_send">
																		Send
																		<span><i class="material-icons">send</i></span>
																	</button>
																</li>
															</ul>


															<div label="API Request Body" class="api-request-section-body">
																
																<div class="row pa-10">
																	<div class="col-md-12">
																		<label class="switch">
																			<input type="checkbox" checked id="api_request_body_switch_button">
																			<span class="slider round"></span>
																		</label>
																		<span id="api_request_body_switch_desc">
																			<?php echo translate('Raw input Enabled'); ?>	
																		</span>
																	</div>
																</div>
																<div class="row pa-10">
																	<div class="col-md-12">
																		<div id="list_request_body">
																			<div for="reqParamList">
																				Parameter List
																			</div> 
																			<div class="list-param-section">
																				<ul class="list-param-ul">
																					<li class="list-param-li">
																						<input placeholder="key 1" name="bparam[]" autofocus="autofocus" class="bparam html-element">
																					</li> 
																					<li class="list-param-li">
																						<input placeholder="value 1" name="bvalue[]" class="bvalue html-element">
																					</li> 
																					<li class="list-param-li">
																						<button type="button" class="del-param" >
																							<i class="material-icons">delete</i>
																						</button>
																					</li>
																				</ul>
																			</div>
																			
																			<div class="pa-10">
																				<button type="button" name="addrequest" id="add_request_list">
																					<i class="material-icons">add</i> 
																					<span>Add new</span>
																				</button>
																			</div>
																		</div>
																		<div id="raw_request_body">
																			<label class="raw-body-label">Raw Request Body</label>
																			<textarea class="textarea-element" placeholder="(add at least one parameter)" rows="7" name="raw_request_body"><?php echo nl2br($raw_request_body) ?></textarea>
																		</div>
																	</div>
																</div>	

																<div class="row pa-10">
																	<div class="col-md-12">
																		<ul class="nav nav-tabs">
																			<li class="active">
																				<a href="#portlet_tab1" data-toggle="tab">
																				Authorization </a>
																			</li>
																			<li>
																				<a href="#portlet_tab2" data-toggle="tab">
																				Headers </a>
																			</li>
																			<li>
																				<a href="#portlet_tab3" data-toggle="tab">
																				Parameters </a>
																			</li>
																		</ul>
																		<div class="tab-content">
																			<div class="tab-pane active" id="portlet_tab1">
																				<div class="pa-10">
																					<select id="api_request_auth" name="api_request_auth" class="html-element">
																						<option value="" <?php echo $api_request_auth == "" ? "selected" : "" ?>>No Auth</option> 
																						<option value="api_key" <?php echo $api_request_auth == "api_key" ? "selected" : "" ?>>API key</option> 
																						<option value="basic_auth" <?php echo $api_request_auth == "basic_auth" ? "selected" : "" ?>>Basic Auth</option> 
																						<option value="bearer_token" <?php echo $api_request_auth == "bearer_token" ? "selected" : "" ?>>Bearer Token</option> 
																						<option value="oauth_2" <?php echo $api_request_auth == "oauth_2" ? "selected" : "" ?>>OAuth 2.0</option>
																					</select>
																				</div>								
																				<div class="api-auth_desc pa-10">
																					
																				</div>
																				<div class="api-request-auth-param-field pa-10">
																					
																				</div>
																			</div>
																			<div class="tab-pane" id="portlet_tab2">
																				<ul class="list-param-ul">
																					<li class="list-param-li">
																						<label>Key</label>
																						<p class="list-param-li mt-10">Content-Type</p>
																						<p class="list-param-li mt-10">Accept</p>
																					</li>
																					<li class="list-param-li">
																						<label>Value</label>
																						<input placeholder="" name="content_type" autofocus="autofocus" class="html-element mt-10" value="<?php echo $content_type ?>">
																						<input placeholder="" name="accept" autofocus="autofocus" class="html-element mt-10" value="<?php echo $accept ?>">
																					</li>
																				</ul>
																			</div>
																			<div class="tab-pane" id="portlet_tab3">
																				<div class="api-auth_desc">
																					Query Params
																				</div>
																				<ul class="list-param-ul mt-10">
																					<li class="list-param-li">
																						<label>Key</label>
																						<input placeholder="" name="param_key_1" autofocus="autofocus" class="html-element mt-10">
																						<input placeholder="" name="param_key_2" autofocus="autofocus" class="html-element mt-10">
																						<input placeholder="" name="param_key_3" autofocus="autofocus" class="html-element mt-10">
																					</li>
																					<li class="list-param-li">
																						<label>Value</label>
																						<input placeholder="" name="param_value_1" autofocus="autofocus" class="html-element mt-10">
																						<input placeholder="" name="param_value_2" autofocus="autofocus" class="html-element mt-10">
																						<input placeholder="" name="param_value_3" autofocus="autofocus" class="html-element mt-10">
																					</li>
																				</ul>
																			</div>
																		</div>
																	</div>
																</div>
															</div>
														</fieldset>
													</div>
												</div>													
											</div>
										</div>
									</form>
									<div class="response-section">
										<label class="raw-body-label">Raw Response Body</label>
										<textarea class="textarea-element" rows="<?php echo $response_rows_number; ?>" name="raw_response_body"><?php echo $response; ?></textarea>										
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		<!-- </b-card> -->
	</component>
</admin-test-api-connection-component>