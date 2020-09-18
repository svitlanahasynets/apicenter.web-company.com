$(document).ready(function(){

	$(document).on("change", "input#switch_button", function(e){
		var checked = $(this).is(':checked');

        if (checked) {
        	$("#message_type_select").show(600);
        } else {
        	$("#message_type_select").hide(600);
        }

	});
	
});