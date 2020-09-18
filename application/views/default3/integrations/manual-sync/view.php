<integration-pages-component post-title="<?php echo translate($page_title) ?>" inline-template>
<component :is="layout">
	<page-title :heading=heading :icon=icon></page-title>
		<div>
			<button class="m-aside-left-close m-aside-left-close--skin-light" id="m_aside_left_close_btn">
				<i class="la la-close"></i>
			</button>
			<?php //echo $menu_html;?>
			<div class="m-grid__item m-grid__item--fluid m-wrapper">
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
								</div>
								<div class="m-portlet__body">
									<div class="m-portlet m-portlet--bordered m-portlet--unair">
										<div class="m-portlet__body">
											<?php $this->load->view(TEMPLATE.'/integrations/manual-sync/view/actions', array('project' => $project)); ?>

											<?php $this->load->view(TEMPLATE.'/integrations/manual-sync/view/settings', array('project' => $project)); ?>
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
</component>
</integration-pages-component>
