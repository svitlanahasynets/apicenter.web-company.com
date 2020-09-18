<h1 class="content-title"><?php echo translate($section['title']); ?></h1>
<input type="hidden" id="enable-exit-page-message" value="true" />
<?php
$form_attributes = array('class' => 'settings_edit_form');

// Open form tag
echo form_open_multipart('settings/saveaction', $form_attributes);

// Define and display form fields
$section = array(
	'section' => $section['code']
);
$returnUrl = array(
	'returnUrl' => current_url()
);
?>

<div class="form-fields">
	<?php echo form_hidden($section);?>
	<?php echo form_hidden($returnUrl);?>
	<?php foreach($fields as $field): ?>
		<?php
			$value = $this->Settings_model->getValue($field['section'], $field['code']);
		?>
		<?php if($field['type'] == 'file'): ?>
			<div class="form-field">
				<label for="<?php echo $section['section'].'-'.$field['code']; ?>"><span class="label"><?php echo translate($field['title']);?></span><span class="required">*</span></label>
				<span>
					<?php echo form_upload($section['section'].'-'.$field['code']);?>
					<?php if($this->Settings_model->getFileType($value) == 'image'): ?>
						<span class="settings-preview-image">
							<img src="<?php echo $this->Settings_model->getImageUrl($value); ?>" />
						</span>
					<?php else : ?>
						<span class="settings-download-link">
							<a href="<?php echo $this->Settings_model->getDownloadLink($value); ?>" target="_blank"><?php echo translate('Current file'); ?></a>
						</span>
					<?php endif; ?>
				</span>
			</div>
		<?php elseif($field['type'] == 'text'): ?>
			<div class="form-field">
				<label for="<?php echo $section['section'].'-'.$field['code']; ?>"><span class="label"><?php echo translate($field['title']);?></span><span class="required">*</span></label>
				<span>
					<?php echo form_input($section['section'].'-'.$field['code'], $value);?>
				</span>
			</div>
		<?php elseif($field['type'] == 'select'): ?>
			<?php
				$values = array();
				foreach($field['values'] as $code => $fieldValue){
					$values[$code] = translate($fieldValue);
				}
			?>
			<div class="form-field">
				<label for="<?php echo $section['section'].'-'.$field['code']; ?>"><span class="label"><?php echo translate($field['title']);?></span><span class="required">*</span></label>
				<span>
					<?php echo form_dropdown($section['section'].'-'.$field['code'], $values, $value);?>
				</span>
			</div>
		<?php endif; ?>
	<?php endforeach; ?>
	<div class="form-field">
		<span>
			<?php
			// Display submit button
			echo form_submit('submit', translate('Save'));
			?>
		</span>
	</div>
</div>

<?php
// Close form tag
echo form_close();