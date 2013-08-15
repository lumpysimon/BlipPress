var BlipPress;

// @TODO@ CREDIT THIS!!

/**
 * Media control frame popup.
 */
jQuery(function($) {
	var Attachment = wp.media.model.Attachment,
		$control, $controlTarget, mediaControl;

	mediaControl = {
		// Initialize a new media manager or return an existing frame.
		// @see wp.media.featuredImage.frame()
		frame: function() {
			if ( this._frame )
				return this._frame;

			this._frame = wp.media({
				title: $control.data('title') || BlipPress.frameTitle,
				library: {
					type: $control.data('media-type') || 'image'
				},
				button: {
					text: $control.data('update-text') || BlipPress.frameUpdateText
				},
				multiple: $control.data( 'select-multiple' ) || false
			});

			this._frame.on( 'open', this.updateLibrarySelection ).state('library').on( 'select', this.select );

			return this._frame;
		},

		// Update the control when an image is selected from the media library.
		select: function() {
			var selection = this.get('selection'),
				returnProperty = $control.data('return-property') || 'id';

			// Insert the selected attachment ids into the target element.
			if ( $controlTarget.length ) {
				// $controlTarget.val( selection.pluck( returnProperty ) ).trigger('change');
				$controlTarget.attr( 'data-image', selection.pluck( returnProperty ) );
			}

			// Trigger an event on the control to allow custom updates.
			$control.trigger( 'selectionChange.blippress', [ selection ] );
		},

		// Update the selected image in the media library based on the image in the control.
		updateLibrarySelection: function() {
			var selection = this.get('library').get('selection'),
				attachment, selectedIds;

			if ( $controlTarget.length ) {
				selectedIds = $controlTarget.attr('data-image');
				if ( selectedIds && '' !== selectedIds && -1 !== selectedIds && '0' !== selectedIds ) {
					attachment = Attachment.get( selectedIds );
					attachment.fetch();
				}
			}

			selection.reset( attachment ? [ attachment ] : [] );
		},

		init: function() {
			$('#wpbody').on('click', '.blippress-image-control-choose', function(e) {
				var targetSelector;

				e.preventDefault();

				$control = $(this).closest('.blippress-image-control');

				targetSelector = $control.data('target') || '.blippress-image-control-target';
				if ( 0 === targetSelector.indexOf('#') ) {
					// Context doesn't matter if the selector is an ID.
					$controlTarget = $( targetSelector );
				} else {
					// Search for other selectors within the context of the control.
					$controlTarget = $control.find( targetSelector );
				}

				mediaControl.frame().open();
			});
		}
	};

	mediaControl.init();



	$('#wpbody').on('selectionChange.blippress', '.blippress-image-control', function( e, selection ) {

		image_id=$('#blippress-action').attr('data-image');
		$('#blippress-image-id').val(image_id);
		$('#blippress-status').removeClass('updated error').html('').hide();

		var $control = $( e.target ),
			model = selection.first(),
			sizes = model.get('sizes'),
			size, image;

		if ( sizes ) {
			// The image size to display in the widget.
			size = sizes['post-thumbnail'] || sizes.medium;
		}

		size = size || model.toJSON();

		image = $( '<img />', { src: size.url, width: size.width } );

		$control.find('img').remove().end()
			.prepend( image )
			.addClass('has-image')
			.find('a.blippress-image-control-choose').removeClass('button-hero');
	});



	$(document).on('click','#blippress-action',function(e){

		post_id  = $(this).attr('data-post');
		image_id = $(this).attr('data-image');

		if ( image_id && "0" != image_id && post_id ) {

			if ( confirm('You are about to create an entry on Blipfoto. Proceed?' ) ) {

				$('#blippress-waiting').show();
				$('#blippress-status').removeClass('updated error').html('').hide();

				$.post( ajaxurl, {
					'action'  : 'post_to_blipfoto',
					'post_id' : post_id,
					'image_id' : image_id
				}, function(r){

					text = r.message;
					status = r.result
					$('#blippress-status').addClass(status);
					if ( 'updated' == r.result ) {
						text = text+' <a href="http://blipfoto.com/entry/' + r.data.entry_id + '" target="_blank">View on Blipfoto</a>';
					}

					$('#blippress-waiting').hide();
					$('#blippress-status').html('<p>'+text+'</p>').show();

				} );

			}

		} else {
			alert( 'Please choose an image' );
		}

		e.stopPropagation();
		e.preventDefault();

	});

});