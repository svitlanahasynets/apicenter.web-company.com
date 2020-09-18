var App = {};
App.disableNavigation = false;

$(document).ready(function(){

	// Enable / disable full-width mode on click
	$("div.sidebar-toggle").click(function(){
		if(!$("div.sidebar").hasClass("folded")){
			$("div.sidebar").addClass("folded");
			var containerWidth = jQuery("div.container").width();
			$("div.content").css({
				width: containerWidth - 120
			});
			$("div.sidebar-toggle span").html('>');
			App.setSessionVariable('fullWidthMode', 'yes');
		} else {
			$("div.sidebar").removeClass("folded");
			var containerWidth = jQuery("div.container").width();
			$("div.content").css({
				width: containerWidth - $('.sidebar').width() - 95
			});
			$("div.sidebar-toggle span").html('<');
			App.setSessionVariable('fullWidthMode', 'no');
		}
	});
	
	// Resize content div after window resize when in full-width mode
	$(window).on('resize', function(){
		if($("div.sidebar").hasClass("folded")){
			var containerWidth = jQuery("div.container").width();
			$("div.content").css({
				width: containerWidth - 120
			});
		} else {
			var containerWidth = jQuery("div.container").width();
			$("div.content").css({
				width: containerWidth - $('.sidebar').width() - 95
			});
		}
		if($(window).width() <= 1024){
			$("div.sidebar").addClass("folded");
		}
	});
	$(window).trigger('resize');
	
	// Enable full width mode if set in session
	App.getSessionVariable('fullWidthMode', function(result){
		if(result != 'yes' && $(window).width() > 1024){
			$("div.sidebar-toggle").trigger('click');
		}
	});
	
	// Open menu in mobile devices
	$(".menu-toggle").bind("click", function(){
		$(".menu-container").toggle();
		$(".wrapper").toggleClass("menuActive");
	});
	
	// Load selectboxes with search view
	$(".form-fields select:visible, .data-table tbody tr:not(.filters) select:visible").select2();
	
	$(".add-table-row").bind("click", function(){
		setTimeout(function(){
			$(".form-fields select:visible, .data-table tbody tr:not(.filters) select:visible").select2();
		}, 100);
	});
	
	var canExitPage = false;
	$("form").bind("submit", function(){
		canExitPage = true;
	});
	$(".delete a").bind("click", function(){
		canExitPage = true;
	});
	
	// Display error message before closing page
	if($("input#enable-exit-page-message").val() == 'true'){
		// $(window).bind('beforeunload', function(e) {
		// 	if(!canExitPage){
		// 		e.preventDefault();
		// 		var message = $("input#exit-page-message").val();
		// 		return message;
		// 	}
		// 	canExitPage = false;
		// });
	}
	
	// Display login form if ajax login error
	$(document).ajaxError(function(event, jqxhr, settings, exception){
		if(exception == 'login'){
			$(".relogin").show();
		}
	});
	
	// Re-login
	$(".relogin form").submit(function(){
		$(this).ajaxSubmit({
			success: function(){
				$(".relogin").hide();
			},
			error: function(){
				$(".relogin .error").show();
				setTimeout(function(){
					$(".relogin .error").fadeOut(1500);
				}, 3000);
			}
		});
		return false;
	});
	
});

// Set session variable
App.setSessionVariable = function(name, value){
	var url = $("input#site-url").val()+'/usersession/setSessionVariable';
	$.ajax({
		url: url,
		type: "POST",
		data: {
			'name': name,
			'value': value
		}
	});
}

//Get session variable
App.getSessionVariable = function(name, callback){
	var url = $("input#site-url").val()+'/usersession/getSessionVariable';
	$.ajax({
		url: url,
		type: "POST",
		data: {
			'name': name
		},
		success: function(result){
			if(typeof(callback) == 'function'){
				callback(result);
			}
			return result;
		}
	});
}

// Format price
App.formatPrice = function(price){
	var defaultSymbol = $("#currency-symbol").val();
	return formatPrice(price, 2, ',', '.', defaultSymbol);
}

function formatPrice(price, c, d, t, symbol){
	var n = price, 
	c = isNaN(c = Math.abs(c)) ? 2 : c, 
	d = d == undefined ? "." : d, 
	t = t == undefined ? "," : t, 
	s = n < 0 ? "-" : "", 
	i = parseInt(n = Math.abs(+n || 0).toFixed(c)) + "", 
	j = (j = i.length) > 3 ? j % 3 : 0,
	symbol = symbol == undefined ? "$" : symbol;
	
	return symbol + '' + s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
 };
 
// Format price to number
App.priceToNumber = function(price){
	var defaultSymbol = $("#currency-symbol").val();
	return priceToNumber(price, defaultSymbol, ',', '.');
}

function priceToNumber(price, symbol, decimal, thousand){
	if(price == ''){
		return 0;
	}
	price = price.replace(symbol, '');
	price = price.replace(' ', '');
	price = price.replace(thousand, '');
	price = price.replace(decimal, '.');
	price = parseFloat(price);
	return price;
}
 
// Format number
App.formatNumber = function(number){
	return formatNumber(number, 2, ',', '.');
}

function formatNumber(price, c, d, t){
	var n = price, 
	c = isNaN(c = Math.abs(c)) ? 2 : c, 
	d = d == undefined ? "." : d, 
	t = t == undefined ? "," : t, 
	s = n < 0 ? "-" : "", 
	i = parseInt(n = Math.abs(+n || 0).toFixed(c)) + "", 
	j = (j = i.length) > 3 ? j % 3 : 0;
	
	return '' + s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
 };
 
// Navigate on click event
App.navigateTo = function(url){
	setTimeout(function(){
		if(!App.disableNavigation){
			document.location.href = url;
		}
		App.disableNavigation = false;
	}, 100);
}