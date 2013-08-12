jQuery(function($){

	$(document).on('click','#blippress-this',function(e){

		post_id = $(this).attr('data-post');

		$(this).hide().after('<span id="blippress-this-info"><span id="blippress-this-spinner" class="spinner"></span>Please wait...</span>');
		$('#blippress-this-spinner').show();

		if ( post_id ) {

			$.post( ajaxurl, {
				'action'  : 'post_to_blipfoto',
				'post_id' : post_id
			}, function(r){

				text = r.message;
				status = r.result
				text = '<div class="blippress-status blippress-'+status+'">'+text;
				if ( 'success' == r.result ) {
					text = text + ' <a href="http://blipfoto.com/entry/' + r.data.entry_id + '" target="_blank">View</a>';
				}
				text = text + '</div>';

				$('#blippress-this-info').html( text );

			} );

		}

		e.stopPropagation();
		e.preventDefault();

	});

});