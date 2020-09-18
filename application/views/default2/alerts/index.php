<?php 
	$error_messages = get_error_messages();
	$success_messages = get_success_messages();
?>

	<div class="m-section__content">
	<?php if(!empty($success_messages)): ?>
		<div class="alert alert-success" role="alert">
			<strong>
				Success!
			</strong>
			<?php
				foreach($success_messages as $success_message): 
				 echo $success_message.'<br />';
				endforeach; 
			?>
		</div>
	<?php endif; ?>
	<?php if(!empty($error_messages)): ?>
		<div class="alert alert-danger" role="alert">
			<strong>
				Oh snap!
			</strong>
			<?php foreach($error_messages as $error_message): 
				  echo $error_message.'<br/>';
			      endforeach; 
			?>
		</div>
	<?php endif; ?>
	</div>
