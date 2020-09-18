

$(document).ready(function(){

	$(document).on("change", "input#api_request_body_switch_button", function(e){
		var checked = $(this).is(':checked');

        if (checked) {
        	$("#raw_request_body").show(600);
        	$("#list_request_body").hide(600);

        	$("#api_request_body_switch_desc").text("Raw input Enabled");
        } else {
        	$("#raw_request_body").hide(600);
        	$("#list_request_body").show(600);

        	$("#api_request_body_switch_desc").text("Raw input Disabled");
        }

	});

	$(document).on("click", "#add_request_list", function(e){
		var ul_count = $(".list-param-section").find("ul.list-param-ul").length;
		var next_ul_index = ul_count + 1;
		var html = '<ul class="list-param-ul"><li class="list-param-li"><input placeholder="key' + next_ul_index + ' " name="bparam[]" autofocus="autofocus" class="bparam html-element"></li><li class="list-param-li"><input placeholder="value ' + next_ul_index + '" name="bvalue[]" class="bvalue html-element"></li><li class="list-param-li"><button type="button" class="del-param"><i class="material-icons">delete</i></button></li></ul>';

		$(".list-param-section").append(html);

	});

	$(document).on("click", "button.del-param", function(e){
		$(e.target).closest(".list-param-ul").remove();
	});

	$(document).on("click", ".nav-tabs li a", function(e){
		$(".nav-tabs").find(".active").removeClass("active");
		$(e.target).closest("li").addClass("active");
	});

	$(document).on("change", "select#api_request_auth", function(e){
		var value = $(this).val();
		
        if (value == "") {
        	$(".api-request-auth-param-field").html("");
        } else if (value == "api_key") {
        	var html = '<ul class="list-param-ul"><li class="list-param-li"><input placeholder="key 1" name="api_key_param_key" autofocus="autofocus" class="html-element"></li> <li class="list-param-li"><input placeholder="value 1" name="api_key_param_value" class="html-element"></li> </ul>';
        	$(".api-request-auth-param-field").html(html);
        } else if (value == "basic_auth") {
        	var html = '<ul class="list-param-ul"><li class="list-param-li">username</li> <li class="list-param-li">password</li> </ul>';
        	html += '<ul class="list-param-ul"><li class="list-param-li"><input placeholder="username" name="basic_auth_username" autofocus="autofocus" class="html-element"></li> <li class="list-param-li"><input placeholder="password" name="basic_auth_password" class="html-element" type="password"></li> </ul>';
        	$(".api-request-auth-param-field").html(html);
        } else if (value == "bearer_token") {
        	var html = '<ul class="list-param-ul"><li class="list-param-li">Token</li> <li class="list-param-li"><textarea class="textarea-element" name="bearer_token_key" rows="5"></textarea></li> </ul>';
        	$(".api-request-auth-param-field").html(html);
        } else if (value == "oauth_2") {
        	var html = '<ul class="list-param-ul"><li class="list-param-li">Access Token</li> <li class="list-param-li"><textarea class="textarea-element" name="oauth_2_key" rows="5"></textarea></li> </ul>';
        	$(".api-request-auth-param-field").html(html);
        }

	});

	$(document).on("click", "button#api_request_send", function(e){
		console.log('ssssssssss');
		$("form#test_api_connection_form").submit();
	});
	
});