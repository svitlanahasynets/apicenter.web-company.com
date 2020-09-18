jQuery(document).ready(function($){
	/* AJAX upload */
		
	$(".form-field input, .form-field select").bind('change', function(){
		updateDependencies();
		updateWebshopDependencies();

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
							hideField = true;
						} 
					} else if($("select[name="+name+"]").length > 0){
						if($("select[name="+name+"]").val() != value){
							hideField = true;
						} 
					} else if($(".form-field-"+name+" select").length > 0){
						if($(".form-field-"+name+" select").val() != value){
							hideField = true;
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

	$(".form-field select[name='connect_to_webshop']").bind('change',function(){
		updateWebshopDependencies();
	});

	function updateWebshopDependencies(){
		if($("select[name='connect_to_webshop']").prop('disabled')){
			$("select[name='connect_to_webshop']").val('Api2cart');
		}
		$(".webshop").each(function(){
			if($(this).data('dependencies') != '' && typeof($(this).data('dependencies')) != 'undefined'){
				var depends = $(this).data('dependencies');
				var field = this;
				var hideField = false;
				$.each(depends, function(name, value){
					if($("select[name='connect_to_webshop']").length > 0){
						if($("select[name='connect_to_webshop']").val() != value){
							hideField = true;
						} 
					}
				});
				if(hideField){
					$(field).find('input, select').attr('disabled', true);
					$(field).find('input, select').removeClass("required");
					$(field).hide();
				} else {
					$(field).find('input, select').attr('disabled', false);
					$(field).find('input, select').addClass("required");
					$(field).show();
				}
			}
		});
	}
	
});