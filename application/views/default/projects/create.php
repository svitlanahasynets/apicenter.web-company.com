<h1 class="content-title"><?php echo translate('Create a project'); ?></h1>
<input type="hidden" id="enable-exit-page-message" value="true" />
<?php
$form_attributes = array('class' => 'projects_create_form');

// Open form tag
echo form_open_multipart('projects/createaction', $form_attributes);
$connect_to_webshop = isset($project['connect_to_webshop'])?$project['connect_to_webshop']:'';

// Define and display form fields
$project_title = array(
	'name' => 'project_title',
	'id' => 'project_title',
	'class' => 'required'
);
$project_description = array(
	'name' => 'project_desc',
	'id' => 'project_desc',
	'class' => 'required'
);
$erp_system = array(
	'name' => 'erp_system',
	'id' => 'erp_system',
	'class' => 'required',
	'values' => array(
		'afas' => 'AFAS',
		'exactonline' => 'Exact Online',
		'visma' => 'Visma'
	)
);
$connect_webshop = array(
	'name' => 'connect_to_webshop',
	'id'   => 'connect_to_webshop',
	'class'=> 'form-control m-input m-input--air',
	'values' => array(
		'' 	=> 'Select webshop',
		'WooCommerce' 	=> 'WooCommerce',
		'Amazon' 		=> 'Amazon'
	),
);
$store_url = array(
	'name' => 'store_url',
	'id' => 'store_url',
	'class' => 'required'
);
$db_users = $this->db->get('permissions_users')->result_array();
$contacts = array();
$contacts[] = translate('None');
foreach($db_users as $db_user){
	$user_id = $db_user['user_id'];
	$contacts[$user_id] = $db_user['firstname'].' '.$db_user['lastname'].' ('.$db_user['user_name'].')';
}
?>

<div class="form-fields">
	<div class="form-field">
		<label for="project_title"><span class="label"><?php echo translate('Project title');?></span><span class="required">*</span></label>
		<span><?php echo form_input($project_title);?></span>
	</div>
	<div class="form-field">
		<label for="project_desc"><span class="label"><?php echo translate('Project description');?></span><span class="required">*</span></label>
		<span><?php echo form_textarea($project_description);?></span>
	</div>
	<div class="form-field">
		<label for="erp_system"><span class="label"><?php echo translate('ERP system');?></span></label>
		<span><?php echo form_dropdown('erp_system', $erp_system['values']);?></span>
	</div>
	<div class="form-field">
		<label for="connect_to_webshop"><span class="label"><?php echo translate('Connect To Webshop');?></span></label>
		<span><?php echo form_dropdown('connect_to_webshop', $connect_webshop['values'],$connect_to_webshop,'id="testDrop"');?></span>
	</div>
	<div class="form-field">
		<label for="store_url"><span class="label"><?php echo translate('Store URL');?></span><span class="required">*</span></label>
		<span><?php echo form_input($store_url);?></span>
	</div>
	<div class="form-field">
		<label for="contact_person"><span class="label"><?php echo translate('Contact person');?></span></label>
		<span><?php echo form_dropdown('contact_person', $contacts);?></span>
	</div>
	
	<?php foreach($project_settings as $field): ?>
		<?php $value = isset($field['default']) ? $field['default'] : ''; ?>
		<?php if($field['type'] == 'text'): ?>
			<div class="form-field" data-dependencies='<?php echo isset($field['depends_on']) ? json_encode($field['depends_on']) : ''; ?>'>
				<label for="<?php echo $field['code']; ?>"><span class="label"><?php echo translate($field['title']);?></span><span class="required">*</span></label>
				<span>
					<?php echo form_input('settings['.$field['code'].']', $value);?>
				</span>
			</div>
		<?php elseif($field['type'] == 'select'): ?>
			<?php
				$values = array();
				foreach($field['values'] as $code => $fieldValue){
					$values[$code] = translate($fieldValue);
				}
			?>
			<div class="form-field" data-dependencies='<?php echo isset($field['depends_on']) ? json_encode($field['depends_on']) : ''; ?>'>
				<label for="<?php echo $field['code']; ?>"><span class="label"><?php echo translate($field['title']);?></span><span class="required">*</span></label>
				<span>
					<?php echo form_dropdown('settings['.$field['code'].']', $values, $value);?>
				</span>
			</div>
		<?php endif; ?>
	<?php endforeach; ?>
	
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