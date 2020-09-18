<h1 class="content-title"><?php echo translate('Create user'); ?></h1>
<input type="hidden" id="enable-exit-page-message" value="true" />
<?php
$form_attributes = array('class' => 'permissions_createuser_form', 'autocomplete' => 'off');

// Open form tag
echo form_open_multipart('permissions/createuseraction', $form_attributes);

// Define and display form fields
$user_name = array(
	'name' => 'user_name',
	'id' => 'user_name',
	'class' => 'required'
);
$firstname = array(
	'name' => 'firstname',
	'id' => 'firstname',
	'class' => 'required'
);
$lastname = array(
	'name' => 'lastname',
	'id' => 'lastname',
	'class' => 'required'
);
$password = array(
	'name' => 'password',
	'id' => 'password',
	'class' => 'required'
);
$user_phone = array(
	'name' => 'user_phone',
	'id' => 'user_phone',
	'class' => ''
);
$user_email = array(
	'name' => 'user_email',
	'id' => 'user_email',
	'class' => 'required'
);
?>

<div class="form-fields">
	<div class="form-field">
		<label for="user_name"><span class="label"><?php echo translate('Username');?></span><span class="required">*</span></label>
		<span><?php echo form_input($user_name);?></span>
	</div>
	<div class="form-field">
		<label for="firstname"><span class="label"><?php echo translate('First name');?></span><span class="required">*</span></label>
		<span><?php echo form_input($firstname);?></span>
	</div>
	<div class="form-field">
		<label for="lastname"><span class="label"><?php echo translate('Last name');?></span><span class="required">*</span></label>
		<span><?php echo form_input($lastname);?></span>
	</div>
	<div class="form-field">
		<label for="password"><span class="label"><?php echo translate('Password');?></span><span class="required">*</span></label>
		<span><?php echo form_password($password);?></span>
	</div>
	<div class="form-field">
		<label for="user_email"><span class="label"><?php echo translate('E-mail address');?></span><span class="required">*</span></label>
		<span><?php echo form_input($user_email);?></span>
	</div>
	<div class="form-field">
		<label for="user_phone"><span class="label"><?php echo translate('Phone number');?></span></label>
		<span><?php echo form_input($user_phone);?></span>
	</div>
	<div class="form-field">
		<span>
			<?php
			// Display submit button
			echo form_submit('submit', translate('Create'));
			?>
		</span>
	</div>
</div>

<?php
// Close form tag
echo form_close();