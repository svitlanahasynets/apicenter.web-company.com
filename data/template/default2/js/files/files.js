jQuery(document).ready(function($){

	// Render tree html
	function getTree(data, output){
		$(data).each(function(index, item){
			var itemHtml = $("<li />")
				.html('<span>'+item.title+'</span>')
				.attr('folder_type', item.type)
				.attr('folder_id', item.id);
			$(output).append(itemHtml);
		});
		return output;
	}

	// Initialize tree
	$.ajax({
		url: $("#site-url").val()+'/files/get_tree/',
		type: 'POST',
		dataType: 'JSON',
		success: function(tree){
			var html = $("<div />").addClass('tree');
			$(".tree-container").html(html);
			$(".tree-container .tree").append('<ul class="open" />');
			var html = $(".tree-container .tree ul").first();
			var treeHtml = getTree(tree, html);
			$(".tree-container .tree ul").first().append(treeHtml);
			$(".tree ul li").first().trigger('click');
			loadFileActions();
		}
	});
	
	// Load sub tree and files on click
	$(document).on('click', '.tree ul li', function(e){
		var li = $(e.currentTarget);
		var collapse = true;
		if($(li).find('li.active').length > 0){
			collapse = false;
		}
		$(".tree ul li").removeClass('active');
		$(li).addClass('active');
		$("#file-uploader-id").val($(li).attr('folder_id'));
		$("#file-uploader-type").val($(li).attr('folder_type'));
		$(".tree ul").removeClass('active');
		$(li).parents('ul').addClass('open');
		$(".tree ul li").removeClass('activechild');
		$(li).parents('li').addClass('activechild');
		loadFileActions();
		
		// Load files
		$.ajax({
			url: $("#site-url").val()+'/files/get_files_by_group/',
			type: 'POST',
			data: {
				id: $(li).attr('folder_id'),
				type: $(li).attr('folder_type')
			},
			success: function(files){
				$(".file-tool-files-inner").html(files);
			}
		});
		
		if(collapse == true){
			$(li).children('ul').toggleClass('open');
		}
		$(li).children('ul').each(function(index, ul){
			if(!$(ul).hasClass('open')){
				$(ul).parent('li').removeClass('activechild');
			} else {
				$(ul).parent('li').addClass('activechild');
				//$(li).find('li').addClass('activechild');
			}
		});
		
		if($(li).hasClass('loaded') || collapse == false){
			e.stopPropagation();
			return;
		}
		$(li).addClass('loaded');

		// Update tree
		$.ajax({
			url: $("#site-url").val()+'/files/get_tree/',
			type: 'POST',
			dataType: 'JSON',
			data: {
				id: $(li).attr('folder_id'),
				type: $(li).attr('folder_type')
			},
			success: function(tree){
				if(tree.length > 0){
					var html = $("<ul class='open' />");
					var treeHtml = getTree(tree, html);
					$(li).append(treeHtml);
					$(li).children('ul').each(function(index, ul){
						if($(ul).hasClass('open')){
							$(ul).parent('li').addClass('activechild');
						}
					});
					
					if($("input#load-folder-on-pageload").length > 0 && $("input#load-folder-on-pageload").val() != ''){
						var loadFolder = $("input#load-folder-on-pageload").val();
						loadFolder = loadFolder.split('-');
						var loadType = loadFolder[0];
						var loadId = loadFolder[1];
						$(".tree ul li[folder_type="+loadType+"][folder_id="+loadId+"]").trigger('click');
						// Prevent second load
						$("input#load-folder-on-pageload").val('');
					}
				}
			},
			failure: function(){
				$(li).removeClass('loaded');
			}
		});
		
		e.stopPropagation();
	});
	
	// Focus file on click
	$(document).on('click', ".list-file, .list-folder", function(e){
		$(".list-file, .list-folder").removeClass('focused');
		$(this).toggleClass('focused');
		loadFileActions();
	});
	// Unfocus file on container click
	$(".file-tool-files-inner").click(function(){
		$(".list-file, .list-folder").removeClass('focused');
		loadFileActions();
	});
	
	// Download file on double click
	$(document).on('doubletap', ".list-file", function(e){
		var file_url = $(this).attr('file_url');
		//window.location = file_url;return;
		window.open(file_url);
	});
	
	// Open folder on double click
	$(document).on('doubletap', ".list-folder", function(e){
		// Go to parent folder
		if($(this).hasClass('goToParentFolder')){
			$(".tree ul li.active").parent('ul').parent('li').trigger('click');
			return;
		}
		
		// Or open subfolder
		var folder_id = $(this).attr('folder_id');
		$(".tree ul li").removeClass('active');
		$(".tree ul li[folder_id="+folder_id+"]").trigger('click').addClass('active');
		$(".tree ul li[folder_id="+folder_id+"]").parent('ul').addClass('open');
		loadFileActions();
	});
	
	// Delete file
	$(".files-actions-files .delete_file").not('.disabled').click(function(){
		var focusedFile = $(".file-tool-files-inner .list-file.focused").first();
		var confirmMessage = $("#file-delete-confirm-message").val();
		if(focusedFile.length > 0 && confirm(confirmMessage)){
			// Remove from database
			$.ajax({
				url: $("#site-url").val()+'/files/remove_file/',
				type: 'POST',
				data: {
					file_id: $(focusedFile).attr('file_id')
				},
				success: function(){
					$(focusedFile).remove();
				}
			});
		}
	});
	
	// Delete folder
	$(".files-actions-files .delete_folder").not('.disabled').click(function(){
		var focusedFolder = $(".file-tool-files-inner .list-folder.focused").first();
		var treeFolder = $(".tree ul li[folder_id="+$(focusedFolder).attr('folder_id')+"][folder_type=folder]");
		var confirmMessage = $("#folder-delete-confirm-message").val();
		if(focusedFolder.length > 0 && confirm(confirmMessage)){
			// Remove from database
			$.ajax({
				url: $("#site-url").val()+'/files/remove_folder/',
				type: 'POST',
				data: {
					folder_id: $(focusedFolder).attr('folder_id')
				},
				success: function(data){
					$(focusedFolder).remove();
					$(treeFolder).remove();
				}
			});
		}
	});
	
	// Disable / enable perticular functions based on selected file
	function loadFileActions(){
		var focusedFile = $(".file-tool-files-inner .list-file.focused").first();
		var focusedFolder = $(".file-tool-files-inner .list-folder.focused").first();
		var currentFolder = $(".tree ul li.active").first();
		
		var options = {
			add_file: false,
			delete_file: false,
			add_folder: false,
			delete_folder: false
		};
		if(currentFolder.length > 0 && ($(currentFolder).attr('folder_type') == 'folder' || $(currentFolder).attr('folder_type') == 'purchaseinvoice-folder' || $(currentFolder).attr('folder_type') == 'salesinvoice-folder')){
			options.add_file = true;
		}
		if(focusedFile.length > 0){
			options.delete_file = true;
		}
		if(currentFolder.length > 0){
			options.add_folder = true;
		}
		if(focusedFolder.length > 0){
			options.delete_folder = true;
		}
		
		// Disable / enable buttons
		$.each(options, function(actionName, value){
			if(value == false){
				$(".files-actions-files ."+actionName).addClass('disabled');
			} else {
				$(".files-actions-files ."+actionName).removeClass('disabled');
			}
		});
	}
	
	// Stop action if action is disabled
	$(".files-actions-files .icon").click(function(e){
		if($(this).hasClass('disabled')){
			e.preventDefault();
			e.stopPropagation();
		}
	});
	
	// Open file upload dialog on click
	function open_file_upload(){
		$(".add-file-popup-container").show();
	}
	$(".files-actions-files .add_file").click(function(){
		if(!$(this).hasClass('disabled')){
			open_file_upload();
		}
	});
	
	// Close dialog on click or escape
	$(document).on('click', '.close-dialog', function(e){
		$(this).closest('.dialog-container').hide();
	});
	$(document).bind('keyup', function(e){
		if(e.keyCode == 27){
			$(".dialog-container:visible").hide();
		}
	});
	
	// Upload files in popup
	var upload_files_url = $("input#site-url").val()+'/files/filetool_upload_files';
	$(".file-uploader").uploadFile({
		url: upload_files_url,
		multiple: true,
		dragDrop: true,
		fileName: "files",
		maxFileCount: 100,
		showFileCounter: false,
		maxFileSize: parseFloat($("#file-uploader-maxfilesize").val()) * 1000,
		showAbort: true,
		showFileCounter: true,
		showProgress: true,
		abortStr: '<span><b>'+$("#file-uploader-aborttitle").val()+'</b></span>',
		dragDropStr: '<span><b>'+$("#file-uploader-dragdroptitle").val()+'</b></span>',
		doneStr: '<span><b>'+$("#file-uploader-donetitle").val()+'</b></span>',
		dynamicFormData: function(){
			var data = {
				folder_id: $("#file-uploader-id").val()
			}
			return data;
		},
		onSuccess: function(files, data, xhr){
			setTimeout(function(){
				$(".ajax-file-upload-statusbar").html('').hide();
			}, 200);
			// Load files
			$.ajax({
				url: $("#site-url").val()+'/files/get_files_by_group/',
				type: 'POST',
				data: {
					id: $(".tree ul li.active").attr('folder_id'),
					type: $(".tree ul li.active").attr('folder_type')
				},
				success: function(files){
					$(".dialog-container").hide();
					$(".file-tool-files-inner").html(files);
				}
			});
		}
	});
	var dragOver = false;
	$(".file-tool-files-inner").bind('dragover', function(){
		dragOver = true;
		$(".file-tool-files .ajax-upload-dragdrop").addClass('state-hover');
	});
	$(".file-tool-files-inner").bind('dragleave', function(){
		if(dragOver == false){
			$(".file-tool-files .ajax-upload-dragdrop").removeClass('state-hover');
		}
	});
	// Upload files by drag/drop in file list area
	$(".file-tool-files-uploader").uploadFile({
		url: upload_files_url,
		multiple: true,
		dragDrop: true,
		fileName: "files",
		maxFileCount: 100,
		showFileCounter: false,
		maxFileSize: parseFloat($("#file-uploader-maxfilesize").val()) * 1000,
		showAbort: true,
		showFileCounter: true,
		showProgress: true,
		abortStr: '<span><b>'+$("#file-uploader-aborttitle").val()+'</b></span>',
		dragDropStr: '<span><b>'+$("#file-uploader-dragdroptitle").val()+'</b></span>',
		doneStr: '<span><b>'+$("#file-uploader-donetitle").val()+'</b></span>',
		dynamicFormData: function(){
			var data = {
				folder_id: $("#file-uploader-id").val()
			}
			return data;
		},
		onSelect:function(files)
		{
			if($("span.add_file").hasClass('disabled')){
				return false;
			}
			return true;
		},
		onSuccess: function(files, data, xhr){
			setTimeout(function(){
				$(".ajax-file-upload-statusbar").html('').hide();
			}, 200);
			// Load files
			$.ajax({
				url: $("#site-url").val()+'/files/get_files_by_group/',
				type: 'POST',
				data: {
					id: $(".tree ul li.active").attr('folder_id'),
					type: $(".tree ul li.active").attr('folder_type')
				},
				success: function(files){
					$(".dialog-container").hide();
					$(".file-tool-files-inner").html(files);
				}
			});
		}
	});
	
	// Open add folder dialog on click
	function open_folder_add_dialog(folder_id, type){
		if(folder_id > 0 && type != ''){
			$("#add-folder-parent-id").val(folder_id);
			$("#add-folder-type").val(type);
			$("#add-folder-name").val('');
			$(".add-folder-popup-container").show();
		}
	}
	$(".files-actions-files .add_folder").click(function(){
		if(!$(this).hasClass('disabled')){
			var folder_id = $(".tree li.active").attr('folder_id');
			var type = $(".tree li.active").attr('folder_type');
			open_folder_add_dialog(folder_id, type);
		}
	});
	$(".add-folder-popup input[type=submit]").click(function(){
		var parent_id = $("#add-folder-parent-id").val();
		var folder_name = $("#add-folder-name").val();
		var type = $("#add-folder-type").val();
		if(parent_id > 0 && folder_name != '' && type != ''){
			// Add folder
			$.ajax({
				url: $("#site-url").val()+'/files/add_folder/',
				type: 'POST',
				dataType: 'JSON',
				data: {
					parent_id: parent_id,
					folder_name: folder_name,
					type: type
				},
				success: function(tree){
					var type = tree.type;
					var parent_type = tree.parent_type;
					var parent_id = tree.parent;
					var name = tree.name;
					var folder_id = tree.folder_id;
					var parent = $(".tree ul li[folder_type="+parent_type+"][folder_id="+parent_id+"]");
					var item = $("<li />").attr({
						folder_type: 'folder',
						folder_id: folder_id
					}).html('<span>'+name+'<span>');
					$(parent).trigger('click');
					if($(parent).children('ul').length == 0){
						var ul = $("<ul />").addClass('open');
						$(parent).append(ul);
					}
					$(parent).children('ul').first().addClass('open').append(item);
					$(".dialog-container").hide();
				},
				failure: function(){
					$(li).removeClass('loaded');
				}
			});
		}
	});
});