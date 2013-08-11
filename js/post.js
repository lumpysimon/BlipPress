jQuery(function($){

	$(document).on('click','#blip-this',function(e){

		post_id = $(this).attr('data-post');

		$(this).hide().after('<span id="blip-this-info"><span id="blip-this-spinner" class="spinner"></span>Please wait...</span>');
		$('#blip-this-spinner').show();

		if ( post_id ) {

			// $.post( blipfoto.ajaxurl, {
			$.post( ajaxurl, {
				'action'  : 'send_post_to_blipfoto',
				'post_id' : post_id
			}, function(r){

				text = r.message;
				status = r.result
				text = '<div class="blip-status blip-'+status+'">'+text;
				if ( 'success' == r.result ) {
					text = text + ' <a href="http://blipfoto.com/entry/' + r.data.entry_id + '" target="_blank">View</a>';
				}
				text = text + '</div>';

				$('#blip-this-info').html( text );

			} );

		}

		e.stopPropagation();
		e.preventDefault();

	});

});