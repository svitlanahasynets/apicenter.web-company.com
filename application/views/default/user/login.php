<h1 class="content-title"><?php echo translate('Login'); ?></h1>
<?php
$form_attributes = array('class' => 'login_form');

// Open form tag
echo form_open('login/loginaction', $form_attributes);

// Define and display form fields
$field_username = array(
	'name' => 'username',
	'id' => 'username'
);
$field_password = array(
	'name' => 'password',
	'id' => 'password'
);
?>

<div class="form-fields">
	<div class="form-field">
		<label for="username"><?php echo translate('Username');?></label>
		<span><?php echo form_input($field_username, '', 'autofocus');?></span>
	</div>
	<div class="form-field">
		<label for="password"><?php echo translate('Password');?></label>
		<span><?php echo form_password($field_password);?></span>
	</div>
	<div class="form-field">
		<span>
			<?php
			// Display submit button
			echo form_submit('submit', translate('Login'));
			?>
		</span>
	</div>
</div>

<?php

// Close form tag
echo form_close();