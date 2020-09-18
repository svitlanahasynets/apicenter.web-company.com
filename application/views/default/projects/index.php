<h1 class="content-title"><?php echo translate('All projects'); ?></h1>

<div class="projects-actions form-actions">
	<div class="project-action form-action table-columns-preferences"><span class="icon fa fa-columns"></span><span><?php echo translate('Columns'); ?></span></div>
	<?php if($this->Permissions_model->check_permission_user('create_project', 0) == 've'): ?>
	<div class="project-action form-action"><span class="icon fa fa-plus-square"></span><?php echo anchor(site_url('/projects/create'), translate('Create project')); ?></div>
	<?php endif; ?>
</div>

<input type="hidden" name="update_url" id="update_url" value="<?php echo site_url('/projects/search_projects'); ?>" />
<input type="hidden" name="number_of_pages" id="number_of_pages" value="1" />

<div class="form-columns-switcher"></div>
<input type="hidden" id="form-columns" value='<?php echo json_encode(array("project_id", "title", "store_url")); ?>' />
<input type="hidden" id="form-columns-preferences" value='<?php echo json_encode(get_user_preference('columns_projects_index')); ?>' />

<form id="filter-form">
<table class="data-table">
	<thead>
		<tr>
			<th data-column="project_id"><?php echo translate('Project ID');?></th>
			<th data-column="erp_system"><?php echo translate('Type');?></th>
			<th data-column="title"><?php echo translate('Project title');?></th>
			<th data-column="store_url"><?php echo translate('Store URL');?></th>
		</tr>
	</thead>
	<tbody>
		<tr class="filters">
			<td data-column="project_id"><?php echo form_input(array('name' => 'id', 'id' => 'id', 'class' => 'input-short')); ?></td>
			<td data-column="erp_system"><?php echo form_dropdown('erp_system', array('' => translate('All'), 'afas' => 'AFAS', 'exactonline' => 'Exact Online', 'visma' => 'Visma')); ?></td>
			<td data-column="title"><?php echo form_input(array('name' => 'title', 'id' => 'title', 'class' => 'input-long')); ?></td>
			<td data-column="store_url"><?php echo form_input(array('name' => 'store_url', 'id' => 'store_url', 'class' => 'input-long')); ?></td>
		</tr>
	</tbody>
	<tfoot>
		<tr>
			<td colspan="25" class="table-pager noselect">
				<span class="pager-button table-first-page"><<</span>
				<span class="pager-button table-previous-page"><</span>
				<span class="pager-button table-current-page">
					<?php echo translate("Page"); ?> <input type="number" name="current_page" id="current_page" min="1" class="input-short align-center" value="1" />
				</span>
				<span class="pager-button table-number-of-pages">of <span></span></span>
				<span class="pager-button table-next-page">></span>
				<span class="pager-button table-last-page">>></span>
			</td>
		</tr>
	</tfoot>
</table>
</form>