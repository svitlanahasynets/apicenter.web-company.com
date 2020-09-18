<h1 class="content-title"><?php echo translate('Project'); ?> "<?php echo $project['title'];?>"</h1>

<div> <?php    
		if($this->session->userdata('webhook_order_update')):
			echo 'We are getting some error while creating webhook in woocommerce webshop please check credentiails and update project settings.';
			$this->session->unset_userdata('webhook_order_update',false);
		endif;
 	?></div>

<div class="dashboard-block project-actions">
	<?php $this->load->view(TEMPLATE.'/projects/view/actions', array('project' => $project)); ?>
</div>

<div class="dashboard-block project-settings">
	<?php $this->load->view(TEMPLATE.'/projects/view/settings', array('project' => $project)); ?>
</div>

<div class="dashboard-block project-log">
	<?php $this->load->view(TEMPLATE.'/projects/view/log', array('project' => $project)); ?>
</div>