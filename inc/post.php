<?php



class blippress_post {



	var $postmeta = 'blippress-entry';
	var $nonce    = 'blippress-this-nonce';
	var $notice   = array();



	public function __construct() {

		add_action( 'admin_enqueue_scripts', array( $this, 'script' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'style' ) );
		add_action( 'wp_ajax_post_to_blipfoto', array( $this, 'ajax_post_to_blipfoto' ) );
		// add_action( 'add_meta_boxes', array( $this, 'check' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		// add_action( 'save_post',      array( $this, 'save_date' ), 25, 2 );
		// add_action( 'admin_footer',   array( $this, 'notice' ) );

	}



	function script() {

		wp_register_script(
			'blippress-post',
			BLIPPRESS_PLUGIN_DIR . 'js/post.js',
			array( 'jquery' ),
			filemtime( BLIPPRESS_PLUGIN_PATH . 'js/post.js' )
			);

		wp_enqueue_script( 'blippress-post' );

		// wp_localize_script(
		// 	'blippress-post',
		// 	'blippress',
		// 	array(
		// 		'ajaxurl' => admin_url( 'admin-ajax.php' )
		// 	)
		// );

	}



	function style() {

		wp_register_style(
			'blippress-post',
			BLIPPRESS_PLUGIN_DIR . 'css/post.css',
			null,
			filemtime( BLIPPRESS_PLUGIN_PATH . 'css/post.css' )
			);

		wp_enqueue_style( 'blippress-post' );

	}



	function ajax_post_to_blipfoto() {

		// if ( ! wp_verify_nonce( $_REQUEST['nonce'], $this->nonce ) )
		// 	return;

		global $blippress;

		if ( ! blip_check_permission() )
			return;

		$post_id = absint( $_POST['post_id'] );

		if ( ! has_post_thumbnail( $post_id ) ) {

			$response = array(
				'result'  => 'error',
				'message' => 'No featured image - please set the featured image, save the post, then try again'
			);

		} else {

			$blip = new blipWP( $blippress->key, blip_auth_option( 'secret' ), array( 'token' => blip_auth_option( 'token' ) ) );

			$meta = $this->metadata( array( 'post_id' => get_post_thumbnail_id( $post_id ), 'image_meta' => false ) );

			if ( 1 != 1 ) {
			// @TODO@
			// if ( ! $blip->validate_date( '2013-08-06' ) ) {

				$response = array(
					'result'  => 'error',
					'message' => 'You\'ve already blipped on this date'
				);

			} else {

				$postdata = array(
					'image_url'   => $meta['url'],
					'title'       => $meta['title'],
					'description' => $meta['description']
					);

				$json = $blip->post_entry( $postdata );

				if ( isset( $json->data ) ) {

					// error_log(print_r($json->data,true));

					$response = array(
						'result'  => 'success',
						'message' => 'Success! The entry has been published',
						'data'    => array( 'entry_id' => $json->data->entry_id )
					);

					update_post_meta( $post_id, $this->postmeta, $json->data->entry_id );

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



	function check() {

		global $post;

		if ( !blip_check_permission() )
			return;

		if ( is_blipped() or !is_blip_post_type() or !isset( $_GET['post'] ) or !isset( $_GET['action'] ) or 'edit' != $_GET['action'] )
			return;

		if ( ! has_post_thumbnail( $post->ID ) ) {

			$this->notice['type'] = 'updated';
			$this->notice['message'] = '<p>Please add a featured image if you\'d like to create a Blip from this post</a></p>';

		} else {

			$this->notice['type'] = 'updated';
			$this->notice['message'] = '<p><a href="' . wp_nonce_url( admin_url( '?page=blippress&action=blip&post_id=' . $post->ID ), 'blippress-create-nonce' ) . '">Create a Blip from this post</a></p>';

		}

	}



	function add_meta_box() {

		if ( ! blip_check_permission() )
			return;

		if ( ! is_blip_post_type() )
			return;

		if ( ! $types = blip_post_types() )
			return;

		foreach ( $types as $type ) {

			add_meta_box(
				'blippress',
				'BlipPress',
				array( $this, 'meta_box' ),
				$type,
				'normal'
			);

		}

	}



	function meta_box( $post ) {

		if ( is_blipped() ) {
			echo sprintf(
					'<p>This post is blipped - <a href="%s" target="_blank">view</a></p>',
					get_blip_url( get_blip_id() )
					);
		} else {
			if ( has_post_thumbnail() ) {
				echo $this->details();
				echo '<p><a id="blippress-this" class="button" data-post="' . $post->ID . '" href="#">Blip this post</a><span id="' . $this->nonce . '" class="hidden">' . wp_create_nonce( $this->nonce ) . '</span></p>';
			} else {
				echo '<p>You must set a featured image before you can blip this post</p>';
			}
		}

	}



	function thumb() {

		the_post_thumbnail( array( 124, 124 ) );

	}



	function details() {

		global $post;

		$thumb    = get_the_post_thumbnail( $post->ID, array( 100, 100 ) );
		$metadata = $this->metadata();

		echo '<div class="blippress-this-details">';
		echo '<div class="blippress-this-thumb">';
		echo $thumb;
		echo '</div>';
		echo '<div class="blippress-this-meta">';
		echo '<ul>';
		foreach ( $metadata as $k => $v ) {
			echo '<li>' . $k . ': ' . $v . '</li>';
		}
		echo '</ul>';
		echo '</div>';
		echo '</div>';

	}



	function metadata( $args = array() ) {

		global $post;

		$defaults = array(
			'post_id'    => null,
			'image_meta' => true
			);

		$args = wp_parse_args( $args, $defaults );

		extract( $args, EXTR_SKIP );

		if ( ! $post_id ) {
			$post_id = get_post_thumbnail_id( $post->ID );
		}

		if ( ! $post_id )
			return;

		$thumb_src       = wp_get_attachment_image_src( $post_id, 'full' );
		$url             = $thumb_src[0];
		$attachment      = get_post( $post_id );
		$attachment_meta = wp_get_attachment_metadata( $post_id );

		$image_fields = blip_exif_fields( true );

		$meta                = array();
		// $meta['url']         = $url;
		$meta['url']         = 'http://lumpysimon.net/bliptest/2013-08-09-meldon-rocks-again.jpg';
		$meta['title']       = $attachment->post_title;
		$meta['description'] = $attachment->post_content;

		if ( $image_meta and isset( $attachment_meta['image_meta'] ) and is_array( $attachment_meta['image_meta'] ) ) {
			foreach ( $image_fields as $field ) {
				if ( isset( $attachment_meta['image_meta'][$field] ) ) {
					$meta[$field] = $attachment_meta['image_meta'][$field];
				}
			}
		}

		return $meta;

	}



	function save_date( $post_id, $post ) {

		if (
			!has_post_thumbnail( $post_id )
			or !current_user_can( 'edit_post', $post_id )
			or ( defined( 'DOING_AJAX' ) and DOING_AJAX )
			or wp_is_post_revision( $post_id )
			or wp_is_post_autosave( $post_id )
			or 'auto-draft' == $post->post_status
			or 'trash' == $post->post_status
			) {
			return;
		}

		if ( $id = get_post_thumbnail_id( $post_id ) ) {
			if ( $meta = wp_get_attachment_metadata( $id ) ) {
				if ( $image_date = $meta['image_meta']['created_timestamp'] ) {
					update_post_meta( $post_id, 'blippress-image-date', absint( $image_date ) );
				}
			}
		}

	}



	function notice() {

		if ( !current_user_can( 'edit_posts' ) or !isset( $this->notice ) or empty( $this->notice ) )
			return;

		echo '<div class="' . $this->notice['type'] . '" id="blippress-notice">' . $this->notice['message'] . '</div>';

	}



}



global $blippress_post;

$blippress_post = new blippress_post;
