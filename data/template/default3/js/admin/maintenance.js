
$(document).ready(function(){

	$(".remove-files").on("click", function(e){

		var $this = $(this);

		$.ajax({
			url: $("#remove_files").val(),
			method: 'POST',
			dataType: 'json',
			data: {
				project_id: $this.data('project-id'),
				project_name: $this.data('project-name')
			},
			success: function(data){

				if (data.success) {
					var alert_html = "<strong>Success!</strong>  All files(<b>" + data.project_name + "</b>) have been removed.";
					$(".alert-success").html(alert_html);
					$(".alert-success").show();

					$("#project_files_remove_section").find('.remove-files').each(function() {
						if ($(this).data('project-id') == data.project_id) {
							$(this).prop('disabled', true);
						}
					});
				}
			}
		});

	});
	
});