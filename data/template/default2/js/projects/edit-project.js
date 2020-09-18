jQuery(document).ready(function($){
	/* AJAX upload */
	
	// Upload image
	var upload_file_url = $("input#upload_url").val();
	$(document).on("click", ".file-uploader", function(e){
		$("#file").trigger('click');
		e.preventDefault();		
		return false;
	});
	
	$("#file").on("change", function(){
		$("#file").closest("form").ajaxForm({
			success: function(responseText, statusText){
				var data = $.parseJSON(responseText);
				if(data.is_image == true){
					var imgSrc = data.url;
					$("input.route-image").val(data.file_sub_path);
					$(".image-thumb").html('');
					$(".image-thumb").append('<span class="image-remove">X</span>');
					$(".image-thumb").append('<img src="'+imgSrc+'" />');
				}
				$("#file").val('');
			}
		});
		$("#file").closest("form").submit();
	});
	
	// Remove image
	$(document).on("click", ".image-thumb .image-remove", function(){
		var imagePath = $("input.route-image").val();
		$.ajax({
			url: $("#remove_url").val(),
			dataType: "JSON",
			method: "POST",
			data: {
				image_path: imagePath
			},
			success: function(data){
				if(data == "true"){
					$("input.route-image").val('');
					$(".image-thumb").html('');
				}
			}
		});
	});
	
	$("#tabs").tabs();
	
	$(".form-field input, .form-field select").bind('change', function(){
		updateDependencies();
	});
	
	$(".form-field .add-row").bind('click', function(){
		$(this).closest('table').find('.row-init').clone().appendTo($(this).closest('table').find('tbody')).removeClass('row-init').show();
	});
	
	function updateDependencies(){
		$(".form-field").each(function(){
			if($(this).data('dependencies') != '' && typeof($(this).data('dependencies')) != 'undefined'){
				var depends = $(this).data('dependencies');
				var field = this;
				var hideField = false;
				$.each(depends, function(name, value){
					if($("input[name="+name+"]").length > 0){
						if($("input[name="+name+"]").val() != value){
/*
							$(field).find('input, select').attr('disabled', true);
							$(field).hide();
*/
							hideField = true;
						} else {
/*
							$(field).find('input, select').attr('disabled', false);
							$(field).show();
*/
						}
					} else if($("select[name="+name+"]").length > 0){
						var split_val = value.split(',');
						console.log(split_val);
						for (var i = split_val.length - 1; i >= 0; i--) {
							if($("select[name="+name+"]").val() != split_val[i]){
								hideField = true;
							} else {
								hideField = false;
								break;
							}
						}
						//if($("select[name="+name+"]").val() != value){
/*
							$(field).find('input, select').attr('disabled', true);
							$(field).hide();
*/
						//	hideField = true;
						//} else {
/*
							$(field).find('input, select').attr('disabled', false);
							$(field).show();
*/
						//}
					} else if($(".form-field-"+name+" select").length > 0){
						if($(".form-field-"+name+" select").val() != value){
/*
							$(field).find('input, select').attr('disabled', true);
							$(field).hide();
*/
							hideField = true;
						} else {
/*
							$(field).find('input, select').attr('disabled', false);
							$(field).show();
*/
						}
					}
				});
				if(hideField){
					$(field).find('input, select').attr('disabled', true);
					$(field).hide();
				} else {
					$(field).find('input, select').attr('disabled', false);
					$(field).show();
				}
			}
		});
	}
	
});