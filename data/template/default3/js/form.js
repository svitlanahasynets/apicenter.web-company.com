var form = {};
form.focusedSelect2 = null;

$(document).ready(function(){

	// Load date pickers
	form.loadDatePickers = function(){
		if($('.datepicker').not(':hidden').length > 0){
			$('.datepicker').not(':hidden').Zebra_DatePicker({
				format: 'd-m-Y',
				readonly_element: false,
				show_week_number: 'Week',
				days: ['Zondag', 'Maandag', 'Dinsdag', 'Woensdag', 'Donderdag', 'Vrijdag', 'Zaterdag'],
				days_abbr: ['zo', 'ma', 'di', 'wo', 'do', 'vr', 'za'],
				months: ['Januari', 'Februari', 'Maart', 'April', 'Mei', 'Juni', 'Juli', 'Augustus', 'September', 'Oktober', 'November', 'December'],
				months_abbr: ['Jan', 'Feb', 'Maa', 'Apr', 'Mei', 'Jun', 'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dec'],
				show_select_today: "Vandaag",
				onSelect: function(){
					$(this).trigger('change');
				}
			});
		}
	}
	form.loadDatePickers();
	
	// Validate form entries
	$("form").on('submit', function(){
		var check = true;
		var scrool_id = '';
		var fields = $(this).find('input, textarea, select');
		$(fields).each(function(index, field){
			if($(field).closest(".hidden").length > 0){
				return;
			}
			if($(field).hasClass('required') !== false || $(field).closest('.form-field').find('input.required, textarea.required, select.required').length > 0){
				field = $(field).closest('.form-field').find('input.required, textarea.required, select.required').first();
				if(field.length == 0){
					return;
				}
				// Check if field is empty
// 				if($(field).val() == '' || $(field).val() == null || ($(field).is('select') && $(field).val() == 0)){
				if( (typeof($(field).val()) != 'object') && ($(field).val() == '' || $(field).val() == null)
					|| (typeof($(field).val()) == 'object' && ($(field).val() == null || $(field).val().length == 0)) ){
					$(field).parent().append("<span class='required required-notice'><font color='red'>Please fill in the field above</font></span>");
					if(scrool_id=='')
						scrool_id = field.get(0).id;
					setTimeout(function(){
						$("span.required-notice").fadeOut(2000, function(){
							$(this).remove();
						});
					}, 3000);
					check = false;
				}
				
				// Check if date field has correct entry
				
				// Check if email field has correct entry
				
			}
		});
		if(check == false){
			if(scrool_id!=''){
				$('html, body').animate({
			        scrollTop: $("#"+scrool_id).offset().top - 80
			    }, 1000)
			}
			return false;
		}
	});
	
	if($(".chosen").length > 0){
		$(".chosen").multipleSelect({
			filter: true,
// 			multiple: true,
		});
		$(document).on("change", ".chosen input", function(){
			$(this).closest('td').find('select').trigger('change');
		});
	}
	
	if($.isFunction($.fn.stickyTableHeaders)){
		var fixedOffset = 0;
		if($(window).width() <= 1400){
			fixedOffset = $(".header").height();
		}
		$(".data-table").stickyTableHeaders({
			fixedOffset: fixedOffset
		});
		$('.content-inner').on('scroll', function(){
			$(window).trigger('scroll');
		});
		$(window).on('resize orientationchange', function(){
			var fixedOffset = 0;
			if($(window).width() <= 1400){
				fixedOffset = $(".header").height();
			}
			$(".data-table").stickyTableHeaders({
				fixedOffset: fixedOffset
			});
		});
	}
	
	$(".filters select, .filters input[type=text], .filters input[type=checkbox]:not(.select-all)").on('change', function(){
		form.filterResults();
	});
	$(document).on("click", ".dp_clear", function(){
		form.filterResults();
	});
	
	$(".load-more-results").click(function(){
		form.loadResults();
	});
	
	form.loadResults = function(){
		var updateUrl = $("input#update_url").val();
		if(updateUrl && updateUrl != ''){
			var options = {
				url: updateUrl,
				type: 'POST',
				dataType: "json",
				success: function(data){
					$("form#filter-form tbody span").not('.filters, td span').remove();
					$("form#filter-form tbody").append(data.html);
					$(".table-number-of-pages span").html(data.pages);
					$("input#number_of_pages").val(data.pages);
					$("input#current_page").attr('max', data.pages);
					$("input.select-all").prop('checked', false);
					$("input#all_selected").val(false);
					$("input#selected_ids").val('');
					toggleBulkActionButtons();
					form.loadDatePickers();
					form.resizableColumnns();
					form.getColumnPreferences();
					$(".textarea-oneline").trigger('keyup');
				}
			};
	
			$('#filter-form').ajaxSubmit(options);
		}
	}
	form.loadResults();
	
	form.filterResults = function(){
		var updateUrl = $("input#update_url").val();
		if(updateUrl && updateUrl != ''){
			var options = {
				url: updateUrl,
				type: 'POST',
				dataType: "json",
				success: function(data){
					$("form#filter-form tbody tr, form#filter-form tbody span").not('.filters, td span').remove();
					$("form#filter-form tbody").append(data.html);
					$(".table-number-of-pages span").html(data.pages);
					$("input#number_of_pages").val(data.pages);
					$("input#current_page").attr('max', data.pages);
					$("input.select-all").prop('checked', false);
					$("input#all_selected").val(false);
					$("input#selected_ids").val('');
					toggleBulkActionButtons();
					form.loadDatePickers();
					form.resizableColumnns();
					form.getColumnPreferences();
					$(".textarea-oneline").trigger('keyup');
				}
			};
			$('#filter-form').ajaxSubmit(options);
		}
		
		// Set cookie to save filter settings
		var options = $("form#filter-form").serialize();
		$.ajax({
			url: $("input#site-url").val()+'/form/set_filter_data',
			type: "post",
			data: {
				fields: options,
				module: $("input#current_module").val(),
				action: $("input#current_action").val(),
			}
		});
	}
	
	form.initializeFilters = function(){
		$.ajax({
			url: $("input#site-url").val()+'/form/get_filter_data',
			type: "post",
			dataType: "json",
			data: {
				module: $("input#current_module").val(),
				action: $("input#current_action").val(),
			},
			success: function(data){
				// Set multiselect boxes
				$.each(data, function(name, value){
					if(typeof(data[name]) == 'object'){
						$("tr.filters select#"+name).val(value);
						$("tr.filters select#"+name).multipleSelect("refresh");
					}
				});
				
				// Set input text boxes
				$("tr.filters input[type=text]").each(function(){
					var name = $(this).attr('name');
					if(typeof(data[name]) != 'undefined'){
						$(this).val(data[name]);
					}
				});
				
				// Set checkboxes
				$("tr.filters input[type=checkbox]").each(function(){
					var name = $(this).attr('name');
					if(typeof(data[name]) != 'undefined' && data[name] == 'true'){
						$(this).prop('checked', true);
					}
				});
				
				// Reload results
				form.filterResults();
				
				// Triggers
				$("select, input").trigger("initialize_complete");
			}
		});
	}
	form.initializeFilters();
	
	// Select / deselect all
	$(document).on("change", "input.select-all", function(e){
		if($(this).is(':checked')){
			$(this).closest('.content').find('table tr:not(.filters) input[type=checkbox]:not(.select-all)').prop('checked', true);
			$("input#all_selected").val(true);
		} else {
			$(this).closest('.content').find('table tr:not(.filters) input[type=checkbox]:not(.select-all)').prop('checked', false);
			$("input#all_selected").val(false);
		}
		
		// Reset IDs array
		var ids = [];
		$(this).closest('.content').find('table tr:not(.filters) input[type=checkbox]:not(.select-all):checked').each(function(){
			var id = $(this).attr('name').replace('select-item-', '');
			if(jQuery.isNumeric(id)){
				ids.push(id);
			}
		});
		if($("input#selected_ids").length > 0){
			ids = ids.join(',');
			$("input#selected_ids").val(ids);
		}
		
		toggleBulkActionButtons();
	});
	
	$(document).on("change", "input[type=checkbox]:not(.select-all)", function(e){
		// Set select-all input field to not-checked
		if($(this).closest('.content').find('table tr:not(.filters) input[type=checkbox]:not(.select-all):not(:checked)').length > 0){
			$("input.select-all").prop('checked', false);
			$("input#all_selected").val(false);
		}
		
		// Reset IDs array
		var ids = [];
		$(this).closest('.content').find('table tr:not(.filters) input[type=checkbox]:not(.select-all):checked').each(function(){
			var id = $(this).attr('name').replace('select-item-', '');
			if(jQuery.isNumeric(id)){
				ids.push(id);
			}
		});
		if($("input#selected_ids").length > 0){
			ids = ids.join(',');
			$("input#selected_ids").val(ids);
		}
		
		toggleBulkActionButtons();
	});
	
	// Prevent navigation on click checkbox
	$(document).on("click", ".data-table tr", function(e){
		e = window.event || e;
		if($(e.target).is(':checkbox') || $(e.target).is('a')){
			App.disableNavigation = true;
		}
	});
	
	// Export to CSV
	$(".export-to-csv").bind("click", function(){
		var url = $("input#export_csv_url").val();
		var formData = $("form#filter-form").serialize();
		var win = window.open(url+'?'+formData, '_blank');
		win.focus();
	});
	
	// Export to PDF
	$(".export-to-pdf").bind("click", function(){
		var url = $("input#export_pdf_url").val();
		var formData = $("form#filter-form").serialize();
		var win = window.open(url+'?'+formData, '_blank');
		win.focus();
	});
	
	// Enable / disable bulk action buttons
	function toggleBulkActionButtons(){
		if($("input#selected_ids").val() != ''){
			$(".form-bulk-action").addClass('active');
		} else {
			$(".form-bulk-action").removeClass('active');
		}
	}
	
	// Pagination
	$("input#current_page").bind("change", function(){
		form.filterResults();
	});
	$(".table-previous-page").bind("click", function(){
		if((parseInt($("input#current_page").val()) - 1) > 0){
			$("input#current_page").val(parseInt($("input#current_page").val()) - 1);
			form.filterResults();
		}
	});
	$(".table-next-page").bind("click", function(){
		if((parseInt($("input#current_page").val()) + 1) <= parseInt($("input#number_of_pages").val())){
			$("input#current_page").val(parseInt($("input#current_page").val()) + 1);
			form.filterResults();
		}
	});
	$(".table-first-page").bind("click", function(){
		$("input#current_page").val(1);
		form.filterResults();
	});
	$(".table-last-page").bind("click", function(){
		$("input#current_page").val($("input#number_of_pages").val());
		form.filterResults();
	});
	
	// Quick view
	$(document).on("click", ".data-table .quick-view:not(.active)", function(){
		App.disableNavigation = true;
		$(".quick-view-row").remove();
		$(".quick-view").removeClass('active');
		$(this).addClass('active');
		var quickViewRow = $(this).closest("tr");
		
		$.ajax({
			url: $(this).data('url'),
			type: "post",
			success: function(data){
				var newRow = $("<tr />").addClass('quick-view-row');
				$(newRow).insertAfter(quickViewRow);
				$(newRow).html('<td colspan="15"></td>');
				$(newRow).find('td').html(data);
			}
		});
	});
	$(document).on("click", ".data-table .quick-view.active", function(){
		App.disableNavigation = true;
		$(".quick-view-row").remove();
		$(".quick-view").removeClass('active');
	});
	
	form.resizableColumnns = function(){
		// https://github.com/dobtco/jquery-resizable-columns
		$("table.data-table").resizableColumns({
			store: window.store
		});
	}
	
	// Transfer 'Enter' keypress to 'Tab' keypress if focused in on input or select
	$(document).on("keydown", "input, select", function(e){
		if(e.keyCode == 13){
			var currentElement = $(this);
			
			var isSelect2 = false;
			if($(":focus").first().is(".select2-selection")){
				isSelect2 = true;
				currentElement = form.focusedSelect2;
			}
			
			if(e.shiftKey){
				$('input[type!=hidden]:enabled:not([readonly]),select:enabled:not([readonly]),textarea:enabled:not([readonly])')[$('input[type!=hidden]:enabled:not([readonly]),select:enabled:not([readonly]),textarea:enabled:not([readonly])').index(currentElement)-1].focus();
			} else {
				$('input[type!=hidden]:enabled:not([readonly]),select:enabled:not([readonly]),textarea:enabled:not([readonly])')[$('input[type!=hidden]:enabled:not([readonly]),select:enabled:not([readonly]),textarea:enabled:not([readonly])').index(currentElement)+1].focus();
			}
			
			if($(':focus').first().is('input:text')){
				$(':focus').first().select();
			}
			if($(':focus').first().is('select')){
				$(':focus').first().select2("open");
			}
			e.preventDefault();
			return false;
		}
	});
	
	$(document).on("select2:open", "select", function(e){
		form.focusedSelect2 = e.target;
	});
	
	// Show column switchers
	if($("input#form-columns").length > 0){
		var columns = JSON.parse($("input#form-columns").val());
		$.each(columns, function(index, column){
			var div = $("<div />").addClass('column-switcher');
			var columnName = $(".data-table thead th[data-column="+column+"]").first().text();
			$(div).append('<input type="checkbox" class="column-switch-checkbox" id="column-switch-'+column+'" data-column="'+column+'" checked="checked" /><label for="column-switch-'+column+'">'+columnName+'</label>');
			$(div).appendTo(".form-columns-switcher");
		});
	}
	
	// Toggle columns
	$(document).on('change', ".form-columns-switcher .column-switch-checkbox", function(){
		var column = $(this).data('column');
		if($(this).is(':checked')){
			$(".data-table thead th[data-column="+column+"], .data-table tbody td[data-column="+column+"]").show();
		} else {
			$(".data-table thead th[data-column="+column+"], .data-table tbody td[data-column="+column+"]").hide();
		}
		var columnData = {};
		$(".form-columns-switcher .column-switch-checkbox").each(function(index, option){
			var columnName = $(option).data('column');
			if($(option).is(':checked')){
				columnData[columnName] = true;
			} else {
				columnData[columnName] = false;
			}
		});
		$.ajax({
			url: $("input#site-url").val()+'/form/update_user_preference',
			type: "post",
			data: {
				'preference': 'columns_'+ $("input#current_module").val() +'_'+ $("input#current_action").val(),
				'value': columnData,
			}
		});
	});
	
	// Get column preferences
	form.getColumnPreferences = function(){
		if($("input#form-columns-preferences").length > 0){
			var columnPreferences = JSON.parse($("input#form-columns-preferences").val());
			$.each(columnPreferences, function(columnName, enabled){
				if(!enabled || enabled == 'false'){
					$(".form-columns-switcher .column-switch-checkbox[data-column="+columnName+"]").prop('checked', false).trigger('change');
				}
			});
		}
	}
	form.getColumnPreferences();
	
	// Show/hide columns panel
	$(".form-action.table-columns-preferences").bind("click", function(){
		$(".form-columns-switcher").toggle();
	});
	
	// Update textarea height
	$(document).on('keyup', ".textarea-oneline", function(){
	    var rows = $(this).val().split("\n");
	    var rowsAmount = rows.length;
	    if(rowsAmount > 4){
		    rowsAmount = 4;
	    }
	    $(this).prop('rows', rowsAmount);
	});
	$(".textarea-oneline").trigger('keyup');
	
});