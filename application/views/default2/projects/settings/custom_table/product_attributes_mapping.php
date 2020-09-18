<?php
$project = $this->db->get_where('projects', array('id' => $project_id))->row_array();
$erpSystem = $project['erp_system'];
$cms = $this->Projects_model->getValue('cms', $project_id);

$cmsAttributes = $this->Cms_model->getAttributesForMappingTable($project_id);
$values = array();
$value = $this->Projects_model->getValue($field['code'], $project_id);
if(!empty(json_decode($value, true))){
	$values = json_decode($value, true);
}
?>
<div class="form-field form-field-<?php echo $field['code']; ?> form-group m-form__group row" data-dependencies='<?php echo isset($field['depends_on']) ? json_encode($field['depends_on']) : ''; ?>'>
	<label for="<?php echo $field['code']; ?>" class="col-3 col-form-label"><span class="label"><?php echo translate($field['title']);?></span><span class="required">*</span></label>
	<span>
		<table>
			<thead>
				<tr>
					<th>CMS attribuut</th>
					<th>ERP attribuut</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach($values['cms_attribute'] as $index => $firstFieldValue): ?>
					<tr>
						<td>
							<select class="form-control m-input m-input--air" name="settings[<?php echo $field['code']; ?>][cms_attribute][]">
								<option value=""><?php echo translate('Choose one'); ?></option>
								<?php foreach($cmsAttributes as $attribute): ?>
									<?php
										$value = $values['cms_attribute'][$index];
										$selected = '';
										if($attribute['code'] == $value){
											$selected = 'selected="selected"';
										}
									?>
									<option value="<?php echo $attribute['code']; ?>" data-type="<?php echo $attribute['type']; ?>" <?php echo $selected; ?>><?php echo $attribute['label']; ?></option>
								<?php endforeach; ?>
							</select>
						</td>
						<td>
							<input class="form-control m-input m-input--air" type="text" name="settings[<?php echo $field['code']; ?>][erp_attribute][]" value="<?php echo $values['erp_attribute'][$index]; ?>" />
						</td>
					</tr>
				<?php endforeach; ?>
				<tr class="row-init" style="display:none;">
					<td>
						<select class="form-control m-input m-input--air" name="settings[<?php echo $field['code']; ?>][cms_attribute][]">
							<option value=""><?php echo translate('Choose one'); ?></option>
							<?php foreach($cmsAttributes as $attribute): ?>
								<option value="<?php echo $attribute['code']; ?>" data-type="<?php echo $attribute['type']; ?>"><?php echo $attribute['label']; ?></option>
							<?php endforeach; ?>
						</select>
					</td>
					<td>
						<input class="form-control m-input m-input--air" type="text" name="settings[<?php echo $field['code']; ?>][erp_attribute][]" value="" />
					</td>
				</tr>
			</tbody>
			<?php if($field['permission']=='ve'): ?>
			<tfoot>
				<tr>
					<td><button type="button" class="add-row btn btn-primary" style="    margin-top: 10px;">Nieuwe rij</button></td>
					<td><a href="<?php echo site_url('/projects/edit/id/'.$project_id).'?refresh_attribute_mapping=true'; ?>" class="btn btn-primary" style="margin-top: 10px;">Attributen opnieuw inladen (pagina wordt opnieuw geladen)</a></td>
				</tr>
			</tfoot>
			<?php endif; ?>
		</table>
	</span>
</div>