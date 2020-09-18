$(function(){
	$("#permissions-table").fancytree({
		extensions: ["table"],
		checkbox: false,
		table: {
			indentation: 20,      // indent 20px per node level
			nodeColumnIdx: 0,     // render the node title into the 2nd column
			checkboxColumnIdx: 2  // render the checkboxes into the 1st column
		},
		selectMode: 3,
		source: permissionData,
		renderColumns: function(event, data) {
			var node = data.node,
			$tdList = $(node.tr).find(">td");
			$tdList.eq(1).text(node.data.typeTitle);

			var checkedView = '';
			var checkedEdit = '';
			if(node.data.permission == 've'){
				checkedView = "checked";
				checkedEdit = "checked";
			} else if(node.data.permission == 'v'){
				checkedView = "checked";
			}
			
			$tdList.eq(2).html("<input type='checkbox' name='permissions[projects]["+node.data.type+"]["+node.data.id+"][view]' class='view-permission' element_id='"+node.key+"' permission_type='"+node.data.type+"' permission_type_id='"+node.data.id+"' value='1' "+checkedView+">");
			$tdList.eq(3).html("<input type='checkbox' name='permissions[projects]["+node.data.type+"]["+node.data.id+"][edit]' class='edit-permission' element_id='"+node.key+"' permission_type='"+node.data.type+"' permission_type_id='"+node.data.id+"' value='1' "+checkedEdit+">");
		}
	});
	
	var tree = $("#permissions-table").fancytree("getTree");
	
	// Expand all tree nodes
	tree.visit(function(node){
		node.setExpanded(true);
	});
	tree.visit(function(node){
		var types = [];
		if(types.indexOf(node.data.type) !== -1){
			node.setExpanded(true);
		} else {
			node.setExpanded(false);
		}
	});
	
	// Set even/odd colors
	$("#permissions-table tr").each(function(index, e){
		if(index % 2 == 0){
			$(e).addClass('even');
		}
		if(Math.abs(index) % 2 == 1){
			$(e).addClass('odd');
		}
	});
	
	$("#permissions-table input").bind('change togglepermission', function(el){
		var node = $("#permissions-table").fancytree("getTree").getNodeByKey($(el.currentTarget).attr('element_id'));
		var children = [];
		children = getChildren(children, node);
		var parents = [];
		parents = getParents(parents, node);

		// Set view permission checked if edit permission is checked
		if($(el.currentTarget).hasClass('edit-permission') && $(el.currentTarget).is(':checked')){
			$(el.currentTarget).closest('tr').find('.view-permission').prop('checked', true).trigger('change');
		}
		// Set edit permission unchecked if view permission is unchecked
		if($(el.currentTarget).hasClass('view-permission') && !$(el.currentTarget).is(':checked')){
			$(el.currentTarget).closest('tr').find('.edit-permission').prop('checked', false).trigger('change');
		}
		// Collapse when not collapsed
		if(!$(el.currentTarget).closest('tr').hasClass('fancytree-expanded')){
			$(el.currentTarget).closest('tr').find('.fancytree-expander').trigger('click');
		}
		
		// Set permissions for children
		$(children).each(function(index, child){
			if($(el.currentTarget).hasClass('view-permission') && $(el.currentTarget).is(':checked')){
				$(child.tr).find('.view-permission').prop('checked', true).trigger('change');
			}
			if($(el.currentTarget).hasClass('edit-permission') && $(el.currentTarget).is(':checked')){
				$(child.tr).find('.edit-permission').prop('checked', true).trigger('change');
				$(child.tr).find('.view-permission').prop('checked', true).trigger('change');
			}
			if($(el.currentTarget).hasClass('view-permission') && !$(el.currentTarget).is(':checked')){
				$(child.tr).find('.view-permission').prop('checked', false).trigger('change');
				$(child.tr).find('.edit-permission').prop('checked', false).trigger('change');
			}
			if($(el.currentTarget).hasClass('edit-permission') && !$(el.currentTarget).is(':checked')){
				//$(child.tr).find('.edit-permission').prop('checked', false);
			}
		});
		console.log(children);
		return;
		// Set permissions for parents
		$(parents).each(function(index, parent){
			if($(el.currentTarget).hasClass('view-permission') && $(el.currentTarget).is(':checked')){
				$(parent.tr).find('.view-permission').prop('checked', true).trigger('change');
			}
			if($(el.currentTarget).hasClass('edit-permission') && $(el.currentTarget).is(':checked')){
				//$(parent.tr).find('.edit-permission').prop('checked', true);
				$(parent.tr).find('.view-permission').prop('checked', true).trigger('change');
			}
		});
	});
	
	$(document).on('change', "select.user_groups", function(){
		$.ajax({
			url: $("input#site-url").val()+'/permissions/get_group_permissions',
			type: "post",
			dataType: "json",
			data: {
				user_groups: $("select.user_groups").val(),
			},
			success: function(data){
				console.log(data);
				$('.view-permission, .edit-permission').prop('checked', false);
				$('.view-permission').each(function(index, item){
					var elementId = $(item).attr('element_id');
					elementId = elementId.split('-');
					var type = $(item).attr('permission_type');
					var typeId = $(item).attr('permission_type_id');
					if(typeof(data[type]) != 'undefined' && typeof(data[type][typeId]) != 'undefined'){
						if(data[type][typeId] == 've'){
							$(item).prop('checked', true);
							$(item).closest('tr').find('.edit-permission').prop('checked', true);
						} else if(data[type][typeId] == 'v') {
							$(item).prop('checked', true);
						}
					}
				});
				
				if(typeof(data['edit_all_projects']) != 'undefined' && typeof(data['edit_all_projects'][0]) != 'undefined'){
					if(data['edit_all_projects'][0] == 've'){
						$("input[permission_type=project].edit-permission, input[permission_type=project].view-permission").prop('checked', true);
					} else if(data['edit_all_projects'][0] == 'v'){
						$("input[permission_type=project].view-permission").prop('checked', true);
					}
				}
			}
		});
	});
	
	$(document).on('change', "input[element_id=projects-edit_all_projects].edit-permission", function(){
		if($(this).is(':checked')){
			$("input[permission_type=project].edit-permission, input[permission_type=project].view-permission").prop('checked', true);
		}
	});
	
	$(document).on('change', "input[element_id=projects-edit_all_projects].view-permission", function(){
		if($(this).is(':checked')){
			$("input[permission_type=project].view-permission").prop('checked', true);
		}
	});
	
	$(document).on('change', "input[permission_type=project].view-permission", function(){
		if(!$(this).is(':checked')){
			$("input[element_id=projects-edit_all_projects].view-permission").prop('checked', false);
		}
	});
	
	$(document).on('change', "input[permission_type=project].edit-permission", function(){
		if(!$(this).is(':checked')){
			$("input[element_id=projects-edit_all_projects].view-permission, input[element_id=projects-edit_all_projects].edit-permission").prop('checked', false);
		}
	});
	
	function getChildren(children, node){
		if(typeof(node.children) != 'undefined' && node.children != null){
			if(node.children.length > 0){
				$(node.children).each(function(index, child){
					children.push(child);
					getChildren(children, child);
				});
			}
		}
		return children;
	}
	
	function getParents(parents, node){
		if(typeof(node.parent) != 'undefined' && node.parent != null){
			parents.push(node.parent);
			getParents(parents, node.parent);
		}
		return parents;
	}
	
});