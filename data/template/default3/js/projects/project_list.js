

$(document).ready(function(){

	$(document).on("change", "select.selected-project-id", function(e){
		$('form#log_content_form').submit();

	});

	$(document).on("keydown", ".filters input[type=text], .filters input[type=checkbox]:not(.select-all)", function(e){

		if (e.keyCode == 13) {
			$('form#filter-form').submit();
		}
	});

	$(document).on("change", ".filters select", function(e){

		$('form#filter-form').submit();
	});
	
});