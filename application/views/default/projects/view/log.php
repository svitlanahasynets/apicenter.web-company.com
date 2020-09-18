<h2 class="block-title"><?php echo translate('Logs'); ?></h2>

<?php if(file_exists(DATA_DIRECTORY.'/log_files/'.$project['id'].'/importcustomers.log')): ?>
	<h4>Import customers</h4>
	<textarea readonly="readonly" class="log-viewer"><?php echo file_get_contents(DATA_DIRECTORY.'/log_files/'.$project['id'].'/importcustomers.log'); ?></textarea>
<?php endif; ?>

<?php if(file_exists(DATA_DIRECTORY.'/log_files/'.$project['id'].'/importarticles.log')): ?>
	<h4>Import articles</h4>
	<textarea readonly="readonly" class="log-viewer"><?php echo file_get_contents(DATA_DIRECTORY.'/log_files/'.$project['id'].'/importarticles.log'); ?></textarea>
<?php endif; ?>

<?php if(file_exists(DATA_DIRECTORY.'/log_files/'.$project['id'].'/exportorders.log')): ?>
	<h4>Export orders</h4>
	<textarea readonly="readonly" class="log-viewer"><?php echo file_get_contents(DATA_DIRECTORY.'/log_files/'.$project['id'].'/exportorders.log'); ?></textarea>
<?php endif; ?>

<?php if(file_exists(DATA_DIRECTORY.'/log_files/'.$project['id'].'/exact_setup.log')): ?>
	<h4>Exact setup</h4>
	<textarea readonly="readonly" class="log-viewer"><?php echo file_get_contents(DATA_DIRECTORY.'/log_files/'.$project['id'].'/exact_setup.log'); ?></textarea>
<?php endif; ?>