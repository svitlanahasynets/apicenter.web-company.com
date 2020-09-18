
<div class="m-grid__item m-grid__item--fluid m-grid m-grid--hor m-login m-login--signin m-login--2 m-login-2--skin-2" id="m_login" style="background-image: url(<?php echo $this->pmurl->get_jscss('media/img/bg/bg-2.jpg'); ?>);">
	<div class="m-grid__item m-grid__item--fluid	m-login__wrapper">
		<div class="m-login__container">
			<div class="m-login__logo">
				<a href="#">
					<img src="<?php echo $this->pmurl->get_template_image('logo.png'); ?>" class="logo" />
				</a>
			</div>
			<div class="m-login__signin">
				<div class="m-login__head">
					<h3 class="m-login__title"><?php echo translate('Login').' APIcenter'; ?><!-- <font color="red"> BETA </font></h3> -->
				</div>
				<?php
					$form_attributes = array('class' => 'm-login__form m-form');
					// Open form tag
					echo form_open('login/loginaction', $form_attributes);
					// Define and display form fields
					$field_username = array(
						'name' => 'username',
						'id' => 'username',
						'class'   => 'form-control m-input',
						'autocomplete' => 'off',
						'placeholder'  => translate('Username'),
					);
					$field_password = array(
						'name' => 'password',
						'id' => 'password',
						'class'   => 'form-control m-input',
						'autocomplete' => 'off',
						'placeholder'  => translate('Password'),
					);
					$data = array(
					        'name'          => 'submit',
					        'id'            => 'submit',
					        'value'         => translate('Login'),
					        'class'       => 'btn btn-focus m-btn m-btn--pill m-btn--custom m-login__btn m-login__btn--primary'
					);
					?>
					<div class="form-group m-form__group">
						<?php echo form_input($field_username, '', 'autofocus');?>
					</div>
					<div class="form-group m-form__group">
						<?php echo form_password($field_password);?>
					</div>
					<div class="m-login__form-action">
						<?php echo form_submit($data); ?>
					</div>
					<?php echo form_close(); ?>
			</div>
		</div>
