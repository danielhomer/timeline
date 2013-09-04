jQuery(document).ready(function($) {

	$('.hide-button').click(function() {

		var timeline_params = {};
		timeline_params["id"] = $(this).attr('id').split('-')[1];

		if ($(this).text() == 'hide') {		
			
			var data = {
				action: 'get_response',
				timeline_action: 'hide_post',
				timeline_params: timeline_params
			}

			$(this).closest('.timeline-item').animate({
				marginLeft: '48px',
				opacity: 0.5
			}, '500');
			$(this).text('unhide');
		
		} else {

			var data = {
				action: 'get_response',
				timeline_action: 'unhide_post',
				timeline_params: timeline_params
			}

			$(this).closest('.timeline-item').animate({
				marginLeft: '0',
				opacity: 1
			}, '500');
			$(this).text('hide');
		}
		
		$.post('/wp-admin/admin-ajax.php', data, function(response) {
			var response_obj = $.parseJSON(response);
			console.log(response_obj);
		})
	});

});