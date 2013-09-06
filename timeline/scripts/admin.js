jQuery(document).ready(function($) {

	$('.hide-button').click(function() {

		var timeline_params = {}, 
			hidden = $(this).data('hidden');
		timeline_params["id"] = $(this).data('id');

		if (! hidden) {					
			
			var data = {
				action: 'get_response',
				timeline_action: 'hide_post',
				timeline_params: timeline_params
			}

			$(this).closest('.timeline-item').animate({
				marginLeft: '48px',
				opacity: 0.5
			}, '500');
		
			$(this).data('hidden', true);
		
		} else if (hidden) {
			
			var data = {
				action: 'get_response',
				timeline_action: 'unhide_post',
				timeline_params: timeline_params
			}

			$(this).closest('.timeline-item').animate({
				marginLeft: '0',
				opacity: 1
			}, '500');

			$(this).data('hidden', false);
		
		}

		if ($(this).data('hidden')) {
			$(this).text('unhide');
		} else {
			$(this).text('hide');
		}


		$.post('/wp-admin/admin-ajax.php', data, function(response) {
			var response_obj = $.parseJSON(response);
			console.log(response_obj);
		});

	});

});