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

				if ( 'error' == r.result ) {
					text = r.message;
				} else if ( 'success' == r.result ) {
					text = '<div id="cont-book-cover"><img src="' + r.data.img + '"></div><div id="cont-book-title-and-author">&quot;' + r.data.title + '&quot; by ' + r.data.author + '</div>';
					$('#cont-title').val( r.data.title );
					$('#cont-authors').val( r.data.authors.join(',') );
					$('#cont-cover').val( r.data.img );
					$('#cont-book-details').html( text ).show();
				} else {
					text = 'major shit going down.';
				}

				$('#blip-this-info').html( text );

			} );

		}

		e.stopPropagation();
		e.preventDefault();

	});

});