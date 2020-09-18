<?php
$project_id = $project['id'];
$project = $this->db->get_where('projects', array('id' => $project_id))->row_array();
$erpSystem = $project['erp_system'];
?>

<div class="project-actions">

	<?php
	$permission = $this->Permissions_model->check_permission_user('project', $project['id']);
	if ($permission == 've') :
		?>
		<a href="<?php echo site_url('cronjob/?project=' . $project_id); ?>" class="action-icon">
			<span class="icon fa fa-sign-in"></span>
			<span class="icon-text"><?php echo translate('Run manual sync'); ?></span>
		</a>
	<?php endif; ?>
	<?php if ($erpSystem == 'visma') : ?>
		<a href="<?php echo site_url('visma/index/project/' . $project_id); ?>?reauthorize=1" class="action-icon">
			<span class="icon fa fa-sign-in"></span>
			<span class="icon-text"><?php echo translate('Visma authorization'); ?></span>
		</a>
	<?php endif; ?>
	<?php
	if ($erpSystem == 'exactonline') :
		$project_settings = $this->db->get_where('project_settings', array('project_id' => $project_id, 'code' => 'exact_authorizationcode'))->row_array();
		if (!$project_settings || $project_settings['value'] == '') :
			$this->db->where('project_id =', $project_id);
			$this->db->where('code =', 'cms');
			$this->db->or_where('code =', 'market_place');
			$project_settings = $this->db->get('project_settings')->row_array();
			if ($project_settings) :
				if ($project_settings['value'] == 'WooCommerce') :
					?>
					<a href="<?php echo site_url('woorest/index/?project_id=' . $project_id); ?>" class="action-icon">
						<span class="icon fa fa-sign-in"></span>
						<span class="icon-text"><?php echo translate('Exact authorization'); ?></span>
					</a>
				<?php endif;
				if ($project_settings['value'] == 'Amazon') :
					?>
					<a href="<?php echo site_url('amazonmws/index/?project_id=' . $project_id); ?>" class="action-icon">
						<span class="icon fa fa-sign-in"></span>
						<span class="icon-text"><?php echo translate('Exact authorization'); ?></span>
					</a>
				<?php endif;
				if ($project_settings['value'] == 'vtiger') :
					?>
					<a href="<?php echo site_url('vtigerapi/index/?project_id=' . $project_id); ?>" class="action-icon">
						<span class="icon fa fa-sign-in"></span>
						<span class="icon-text"><?php echo translate('Exact authorization'); ?></span>
					</a>
				<?php endif;
				if ($project_settings['value'] == 'shopify') :
					?>
					<a href="<?php echo site_url('exactonline/index/?project_id=' . $project_id); ?>" class="action-icon">
						<span class="icon fa fa-sign-in"></span>
						<span class="icon-text"><?php echo translate('Exact authorization'); ?></span>
					</a>
				<?php endif;
				if ($project_settings['value'] == 'cscart' || $project_settings['value'] == 'bol') :
					?>
					<a href="<?php echo site_url('exactonline/index/?project_id=' . $project_id); ?>" class="action-icon">
						<span class="icon fa fa-sign-in"></span>
						<span class="icon-text"><?php echo translate('Exact authorization'); ?></span>
					</a>
				<?php endif;
			endif;
		else :
			$exact_webhook_item = $this->db->get_where('project_settings', array('project_id' => $project_id, 'code' => 'exact_webhook_item'))->row();
			if (!$exact_webhook_item) :
				?>
				<!-- <a href="<?php echo site_url('projects/createexactwebhook/?project_id=' . $project_id); ?>" class="action-icon">
									<span class="icon fa fa-sign-in"></span>
									<span class="icon-text"><?php echo translate('Exact Item Webhook'); ?></span>
								</a> -->
			<?php
			endif;
		endif;

	endif; ?>
</div>
<script>
	jQuery(document).ready(function() {
		jQuery('.action-icon').on('click', function() {
			$runUrl = jQuery(this).attr('href');
			fetch($runUrl);
			document.location.reload(true);
			return false;
		});
	});
</script>