<h4><?php echo translate('Settings'); ?></h4>
<ul>
	<?php foreach($sections as $section): ?>
		<?php
			$class = '';
			if(strpos(current_url(), 'code/'.$section['code']) > -1){
				$class = 'class="bold"';
			}
		?>
		<li><a href="<?php echo site_url('settings/section/code/'.$section['code']);?>" <?php echo $class; ?>><?php echo translate($section['title']);?></a></li>
	<?php endforeach; ?>
</ul>