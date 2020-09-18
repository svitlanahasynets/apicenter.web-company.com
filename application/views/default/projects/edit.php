<h1 class="content-title"><?php echo translate('Edit project'); ?> "<?php echo $project['title'];?>"</h1>
<input type="hidden" id="enable-exit-page-message" value="true" />
<?php
$form_attributes = array('class' => 'projects_edit_form');

// Open form tag
echo form_open_multipart('projects/editaction', $form_attributes);
$connect_to_webshop = isset($project['connect_to_webshop'])?$project['connect_to_webshop']:'';

// Define and display form fields
$project_id = array(
	'project_id' => $project['id']
);
$project_title = array(
	'name' => 'project_title',
	'id' => 'project_title',
	'class' => 'required',
	'value' => $project['title']
);
$project_description = array(
	'name' => 'project_desc',
	'id' => 'project_desc',
	'class' => 'required',
	'value' => $project['description']
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
	'class' => 'required',
	'value' => $project['store_url']
);

$db_users = $this->db->get('permissions_users')->result_array();
$contacts = array();
$contacts[] = translate('None');
foreach($db_users as $db_user){
	$user_id = $db_user['user_id'];
	$contacts[$user_id] = $db_user['firstname'].' '.$db_user['lastname'].' ('.$db_user['user_name'].')';
}
?>

<input type="hidden" id="upload_url" value="<?php echo site_url('/projects/upload_file'); ?>" />
<input type="hidden" id="remove_url" value="<?php echo site_url('/projects/remove_file'); ?>" />
<input type="hidden" id="disable_remove" value="" />

<div class="form-fields">
	<?php echo form_hidden($project_id);?>
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
		<span><?php echo form_dropdown('erp_system', $erp_system['values'], $project['erp_system']);?></span>
	</div>
	<div class="form-field">
		<label for="store_url"><span class="label"><?php echo translate('Store URL');?></span><span class="required">*</span></label>
		<span><?php echo form_input($store_url);?></span>
	</div>
	<div class="form-field">
		<label for="connect_to_webshop"><span class="label"><?php echo translate('Connect To Webshop');?></span></label>
		<span><?php echo form_dropdown('connect_to_webshop', $connect_webshop['values'],$connect_to_webshop,'id="testDrop"');?></span>
	</div>
	<div class="form-field">
		<label for="contact_person"><span class="label"><?php echo translate('Contact person');?></span></label>
		<span><?php echo form_dropdown('contact_person', $contacts, $project['contact_person']);?></span>
	</div>
	
	<?php foreach($project_settings as $field): ?>
		<?php $value = $this->Projects_model->getValue($field['code'], $project['id']); ?>
		<?php
		if($value == '' && isset($field['default'])){
			$value = $field['default'];
		}
		?>
		<?php if($field['type'] == 'text'): ?>
			<div class="form-field form-field-<?php echo $field['code']; ?>" data-dependencies='<?php echo isset($field['depends_on']) ? json_encode($field['depends_on']) : ''; ?>'>
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
			<div class="form-field form-field-<?php echo $field['code']; ?>" data-dependencies='<?php echo isset($field['depends_on']) ? json_encode($field['depends_on']) : ''; ?>'>
				<label for="<?php echo $field['code']; ?>"><span class="label"><?php echo translate($field['title']);?></span><span class="required">*</span></label>
				<span>
					<?php echo form_dropdown('settings['.$field['code'].']', $values, $value);?>
				</span>
			</div>
			
		<?php elseif($field['type'] == 'table'): ?>
			<?php
				$fields = array();
				foreach($field['fields'] as $code => $fieldField){
					$fields[$code] = translate($fieldField);
				}
				$values = array();
				if(!empty(json_decode($value, true))){
					$values = json_decode($value, true);
				}
			?>
			<div class="form-field form-field-<?php echo $field['code']; ?>" data-dependencies='<?php echo isset($field['depends_on']) ? json_encode($field['depends_on']) : ''; ?>'>
				<label for="<?php echo $field['code']; ?>"><span class="label"><?php echo translate($field['title']);?></span><span class="required">*</span></label>
				<span>
					<table>
						<thead>
							<tr>
								<?php foreach($fields as $code => $label): ?>
									<th><?php echo translate($label); ?></th>
								<?php endforeach; ?>
							</tr>
						</thead>
						<tbody>
							<?php reset($fields); $firstField = key($fields); ?>
							<?php if(isset($values[$firstField])): ?>
								<?php foreach($values[$firstField] as $index => $firstFieldValue): ?>
									<tr>
										<?php foreach($fields as $code => $label): ?>
											<?php $columnValue = isset($values[$code][$index]) ? $values[$code][$index] : ''; ?>
											<td><input type="text" name="settings[<?php echo $field['code']; ?>][<?php echo $code; ?>][]" value="<?php echo $columnValue; ?>" /></td>
										<?php endforeach; ?>
									</tr>
								<?php endforeach; ?>
							<?php endif; ?>
							<tr class="row-init" style="display:none;">
								<?php foreach($fields as $code => $label): ?>
									<td><input type="text" name="settings[<?php echo $field['code']; ?>][<?php echo $code; ?>][]" /></td>
								<?php endforeach; ?>
							</tr>
						</tbody>
						<tfoot>
							<tr>
								<td><button type="button" class="add-row">Nieuwe rij</button></td>
							</tr>
						</tfoot>
					</table>
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
	<div class="form-field">
		<span class="delete"><a href="<?php echo site_url('/projects/deleteaction/id/'.$project['id']);?>" onclick="return confirm('<?php echo translate('Are you sure you want to delete this project? This action can not be undone.');?>')"><?php echo translate('Delete project');?></a></span>
	</div>
</div>