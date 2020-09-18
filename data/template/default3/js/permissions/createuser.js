$(function(){	

	$(document).on('change', "select#user_role", function(){
		if($(this).val() == 'partner'){
			$('#logo_section').show();
		} else {
			$('#logo_section').hide();
		}
	});

});
