

$(document).ready(function(){

	$(document).on("change", "select.selected-project-id", function(e){
		$('form#log_content_form').submit();

	});

	$('input[name="daterange"]').daterangepicker({
		opens: 'center'
	}, function(start, end, label) {

		var s = start.format('YYYY/MM/DD'); 
        var e = end.format('YYYY/MM/DD'); 
        var str;

        if (s == e) {
          	str = s;
        } else {
          	str = s + ' - ' + e;
        }

        $('input#daterange').val(str);

        var checked = $("input#only_error_filter").is(':checked');

		if (checked) {
			$("input#only_error_filter").val('only_error_filter');
		} else {
			$("input#only_error_filter").val('');
		}

		$('form#log_content_form').submit();

	});

	$(document).on("change", "input#only_error_filter", function(e){
		var checked = $(this).is(':checked');

		if (checked) {
			$(this).val('only_error_filter');
		} else {
			$(this).val('');
		}

        $('form#log_content_form').submit();

	});

	$(document).on("keydown", "input#search_log", function(e){

		var checked = $("input#only_error_filter").is(':checked');

		if (checked) {
			$("input#only_error_filter").val('only_error_filter');
		} else {
			$("input#only_error_filter").val('');
		}

		if (e.keyCode == 13) {
			$('form#log_content_form').submit();
		}
	});

	$(document).on("change", "select.selected-date-sort", function(e){

		var checked = $("input#only_error_filter").is(':checked');

		if (checked) {
			$("input#only_error_filter").val('only_error_filter');
		} else {
			$("input#only_error_filter").val('');
		}

		$('select#name_sort').val('');
		$('select#error_sort').val('');
		$('form#log_content_form').submit();
	});

	$(document).on("change", "select#name_sort", function(e){

		var checked = $("input#only_error_filter").is(':checked');

		if (checked) {
			$("input#only_error_filter").val('only_error_filter');
		} else {
			$("input#only_error_filter").val('');
		}

		$('select.selected-date-sort').val('');
		$('select#error_sort').val('');
		$('form#log_content_form').submit();
	});

	$(document).on("change", "select#error_sort", function(e){

		var checked = $("input#only_error_filter").is(':checked');

		if (checked) {
			$("input#only_error_filter").val('only_error_filter');
		} else {
			$("input#only_error_filter").val('');
		}

		$('select.selected-date-sort').val('');
		$('select#name_sort').val('');
		$('form#log_content_form').submit();
	});
	
});