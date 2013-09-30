jQuery(document).ready(function($) {

	$('#timeline-more').click( function() {
		console.log('click');
		var post_count = 0,
			timeline_params = {};
			timeline_params["start"] = post_count;
		var	data = {
				action: 'get_response',
				timeline_action: 'get_posts',
				timeline_params: timeline_params
			}

		$.post('/wp-admin/admin-ajax.php', data, function(response) {
			var response_obj = $.parseJSON(response);
			console.log(response_obj);
			$.each(response_obj['results'], function() {
				var i = 0;
				$.each(this, function() {
					if (i == 0) $(".timeline-item.more").remove();
					if (this['service'] == 'end') {
						$('#timeline').append('<li class="timeline-item '+ this['service'].toLowerCase() +'"><div class="left-margin"></div></li>');
					} else if (this['service'] == 'more') {
						$('#timeline').append('<li class="timeline-item '+ this['service'].toLowerCase() +'"><div class="left-margin"><img src="/images/'+ this['service'].toLowerCase() +'-32.png" alt="'+ this['service'] +' logo" /></div><div class="right-margin"><p class="content"><a id="timeline-more">Load more posts</a></p></div></li>')
					} else {
						$('#timeline').append('<li class="timeline-item '+ this['service'].toLowerCase() +'"><div class="left-margin"><img src="/images/'+ this['service'].toLowerCase() +'-32.png" alt="'+ this['service'].toLowerCase() +' logo" /></div><div class="right-margin"><p class="content">'+ this['content'].toLowerCase() +'</p><p class="byline"><span id="datetime">'+ this['time'] +'</span> via <a href="#" class="vialink">'+ this['service'] +'</a></p></div></li>');
					}
				});
			});
		});

	});

});