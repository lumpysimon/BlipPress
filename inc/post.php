<?php



class blippress_post {



	var $entry_post_meta = 'entry';
	var $image_post_meta = 'image-id';
	var $nonce           = 'action-nonce';



	public function __construct() {

		add_action( 'admin_enqueue_scripts',    array( $this, 'script' ) );
		add_action( 'admin_enqueue_scripts',    array( $this, 'style' ) );
		add_action( 'wp_ajax_post_to_blipfoto', array( $this, 'ajax_post_to_blipfoto' ) );
		add_action( 'add_meta_boxes',           array( $this, 'add_meta_box' ) );
		add_action( 'save_post',                array( $this, 'save_image_meta' ), 10, 2 );

	}



	function nonce() {

		global $blippress;

		return $blippress->prefix . $this->nonce;

	}



	function script() {

		global $blippress;

		wp_register_script(
			$blippress->prefix . 'post',
			BLIPPRESS_PLUGIN_DIR . 'js/post.js',
			array( 'jquery', 'media-upload', 'media-views' ),
			filemtime( BLIPPRESS_PLUGIN_PATH . 'js/post.js' )
			);

		wp_enqueue_script( $blippress->prefix . 'post' );

		wp_localize_script(
			$blippress->prefix . 'post',
			'BlipPress',
			array(
				'frameTitle'      => 'Choose an image',
				'frameUpdateText' => 'Update image',
				'fullSizeLabel'   => 'Full Size'
				)
			);

	}



	function style() {

		global $blippress;

		wp_register_style(
			$blippress->prefix . 'post',
			BLIPPRESS_PLUGIN_DIR . 'css/post.css',
			null,
			filemtime( BLIPPRESS_PLUGIN_PATH . 'css/post.css' )
			);

		wp_enqueue_style( $blippress->prefix . 'post' );

	}



	function ajax_post_to_blipfoto() {

		global $blippress, $blippress_cache;

		if ( ! blippress_check_permission() )
			return;

		$ok = true;

		$post_id = absint( $_POST['post_id'] );

		$args = array(
			'p'           => $post_id,
			'post_status' => 'publish,pending,draft,future,private'
			);

		$posts = get_posts( $args );

		if ( $posts and !is_wp_error( $posts  ) ) {

			$post = array_shift( $posts );

			if ( ! $post->post_title ) {
				$ok = false;
				$response = array(
					'result'  => 'error',
					'message' => 'The post title is missing, please save your post and try again'
				);
			}

			if ( ! $post->post_content ) {
				$ok = false;
				$response = array(
					'result'  => 'error',
					'message' => 'The post content is missing, please save your post and try again'
				);
			}

		} else {
			$ok = false;
			$response = array(
				'result'  => 'error',
				'message' => 'Couldn\'t access post details, please save your post and try again'
			);
		}

		if ( isset( $_POST['image_id'] ) ) {

			$image_id = absint( $_POST['image_id'] );

		} else {
			$ok = false;
			$response = array(
				'result'  => 'error',
				'message' => 'No image chosen - please choose an image then try again'
			);
		}

		if ( $ok ) {

			// set this here as we want to remember the chosen image even if blipping fails
			update_blippress_meta( $this->image_post_meta, $image_id, $post_id );

			$blip = new blipWP( $blippress->key, blippress_auth_option( 'secret' ), array( 'token' => blippress_auth_option( 'token' ) ) );

			$meta = $this->metadata( $image_id );

			if ( ! $blip->validate_date( $meta['created_timestamp'] ) ) {

				$response = array(
					'result'  => 'error',
					'message' => 'Cannot create an entry for ' . date( get_option( 'date_format' ), strtotime( $meta['created_timestamp'] ) ) . ' (most likely is that you have already blipped on that date)'
				);

			} else {

				$postdata = array(
					'image_url'   => $meta['url'],
					'title'       => $post->post_title,
					'description' => $post->post_content,
					'date'        => $meta['created_timestamp']
					);

				$json = $blip->post_entry( $postdata );

				if ( isset( $json->data ) ) {

					$entry_id = $json->data->entry_id;

					$response = array(
						'result'  => 'updated',
						'message' => 'Success! The entry has been published to ' . blippress_auth_option( 'username' ) . '\'s journal',
						'data'    => array( 'entry_id' => $entry_id )
					);

					update_blippress_meta( $this->entry_post_meta, $entry_id, $post_id );
					$blippress_cache->clear();

				} else {

					$response = array(
						'result'  => 'error',
						'message' => $json->error->message
					);

				}

			}

		}

		header('Content-type: application/json');
		die( json_encode( $response ) );

	}



	function add_meta_box() {

		if ( ! blippress_check_permission() )
			return;

		if ( ! is_blippress_post_type() )
			return;

		if ( ! $types = blippress_post_types() )
			return;

		foreach ( $types as $type ) {

			add_meta_box(
				'blippress',
				'BlipPress',
				array( $this, 'render_meta_box' ),
				$type,
				'normal'
			);

		}

	}



	function render_meta_box( $post ) {

		global $blippress;

		$button_classes = array( 'button', 'button-hero', 'blippress-image-control-choose' );

		$image_id = absint( get_blippress_meta( $this->image_post_meta ) );

		echo meta_handler_nonce_field( $post->ID, $blippress->prefix . 'image' );
		echo '<input type="hidden" name="blippress-image-id" id="blippress-image-id" value="' . esc_attr( $image_id ) . '">';

		if ( is_blipped() ) {
			echo sprintf(
					'<p>This post is blipped. <a href="%s" target="_blank">View on Blipfoto</a></p>',
					get_blippress_url( get_blippress_id() )
					);
			if ( $image_id ) {
				echo wp_get_attachment_image( $image_id, 'medium', false );
			}
		} else { ?>

			<p>You can create a Blipfoto journal entry for <strong><?php echo blippress_auth_option( 'username' ); ?></strong> from this post. Just upload or choose any photograph from your media library, the entry date will be set to the date the photograph was taken and the post title and content will be used.</p>

			<p class="blippress-image-control<?php echo ( $image_id ) ? ' has-image' : ''; ?>"
				data-title="<?php esc_attr( 'Choose an image' ); ?>"
				data-update-text="<?php esc_attr( 'Change Image' ); ?>"
				data-target="#blippress-action">
				<?php
				if ( $image_id ) {
					echo wp_get_attachment_image( $image_id, 'medium', false );
					unset( $button_classes[ array_search( 'button-hero', $button_classes ) ] );
				}
				?>
				<a href="#" class="<?php echo join( ' ', $button_classes ); ?>">Choose an image</a>
			</p>
			<div id="blippress-button"><a id="blippress-action" class="button image-id" data-post="<?php echo $post->ID; ?>" data-image="<?php echo $image_id; ?>" href="#">Blip it!</a><span id="<?php echo $this->nonce(); ?>" class="hidden"><?php echo wp_create_nonce( $this->nonce() ); ?></span> <span id="blippress-waiting">Please wait...</span></div>

			<div id="blippress-status"></div>

		<?php }

	}



	function metadata( $image_id, $args = array() ) {

		global $post;

		$defaults = array(
			'image_meta' => true
			);

		$args = wp_parse_args( $args, $defaults );

		extract( $args, EXTR_SKIP );

		$thumb_src       = wp_get_attachment_image_src( $image_id, 'full' );
		$url             = $thumb_src[0];
		$attachment      = get_post( $image_id );
		$attachment_meta = wp_get_attachment_metadata( $image_id );

		$image_fields = blippress_exif_fields( true );

		$meta                = array();
		$meta['url']         = $url;
		$meta['title']       = $attachment->post_title;
		$meta['description'] = $attachment->post_content;

		if ( $image_meta and isset( $attachment_meta['image_meta'] ) and is_array( $attachment_meta['image_meta'] ) ) {
			foreach ( $image_fields as $field ) {
				if ( isset( $attachment_meta['image_meta'][$field] ) ) {
					$meta[$field] = $attachment_meta['image_meta'][$field];
					if ( 'created_timestamp' == $field ) {
						if ( isset( $attachment_meta['image_meta']['created_timestamp'] ) and $attachment_meta['image_meta']['created_timestamp'] ) {
							$meta['created_timestamp'] = date( 'Y-m-d', $attachment_meta['image_meta']['created_timestamp'] );
						} else {
							$meta['created_timestamp'] = date( 'Y-m-d' );
						}
					}
				}
			}
		}

		return $meta;

	}



	function save_image_meta( $post_id, $post ) {

		global $blippress;

		$meta = array();

		if ( verify_meta_handler_nonce( $post_id, $blippress->prefix . 'image' ) ) {
			$meta[] = 'image-id';
		}

		if ( $meta ) {

			foreach ( $meta as $field ) {
				delete_post_meta( $post_id, $blippress->prefix . $field );
				if ( isset( $_POST[$blippress->prefix . $field] ) and '' != trim( $_POST[$blippress->prefix . $field] ) ) {
					$data = wp_kses( trim( $_POST[$blippress->prefix . $field] ) );
					update_post_meta( $post_id, $blippress->prefix . $field, $data );
				}
			}

		}

	}



}



global $blippress_post;

$blippress_post = new blippress_post;
