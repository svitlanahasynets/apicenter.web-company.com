<h2 class="block-title"><?php echo translate('Project settings'); ?></h2>
<!-- data-table property-value no-edit projects-settings-block
 --><table class=" table m-table m-table--head-separator-danger" style="width: 60%">
	<tr class="even">
		<td><?php echo translate('Project title');?></td>
		<td><?php echo $project['title'];?></td>
	</tr>
	<tr class="odd">
		<td><?php echo translate('Project description');?></td>
		<td><?php echo $project['description'];?></td>
	</tr>
	<tr class="even">
		<td><?php echo translate('Contact person');?></td>
		<td><?php echo $this->pmprojects->get_contact_person_name($project['contact_person']);?></td>
	</tr>
</table>

<?php
$permission = $this->Permissions_model->check_permission_user('project', $project['id']);
if($permission == 've'):
?>
<!--
	<div class="actions">
		<a href="<?php echo site_url('/projects/edit/id/'.$project['id']);?>" class="action"><?php echo translate('Edit project');?></a>
	</div>
-->
<?php endif; ?>