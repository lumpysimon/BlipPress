<?php



class blipfoto_post {



	var $postmeta = 'blipfoto-entry';
	var $nonce    = 'blip-this-nonce';
	var $notice   = array();



	public function __construct() {

		add_action( 'admin_enqueue_scripts', array( $this, 'script' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'style' ) );
		add_action( 'wp_ajax_send_post_to_blipfoto', array( $this, 'ajax_send_post_to_blipfoto' ) );
		// add_action( 'add_meta_boxes', array( $this, 'check' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		// add_action( 'save_post',      array( $this, 'save_date' ), 25, 2 );
		// add_action( 'admin_footer',   array( $this, 'notice' ) );

	}



	function script() {

		wp_register_script(
			'blipfoto-post',
			BLIPFOTO_PLUGIN_DIR . 'js/post.js',
			array( 'jquery' ),
			filemtime( BLIPFOTO_PLUGIN_PATH . 'js/post.js' )
			);

		wp_enqueue_script( 'blipfoto-post' );

		// wp_localize_script(
		// 	'blipfoto-post',
		// 	'blipfoto',
		// 	array(
		// 		'ajaxurl' => admin_url( 'admin-ajax.php' )
		// 	)
		// );

	}



	function style() {

		wp_register_style(
			'blipfoto-post',
			BLIPFOTO_PLUGIN_DIR . 'css/post.css',
			null,
			filemtime( BLIPFOTO_PLUGIN_PATH . 'css/post.css' )
			);

		wp_enqueue_style( 'blipfoto-post' );

	}



	function ajax_send_post_to_blipfoto() {

		// if ( ! wp_verify_nonce( $_REQUEST['nonce'], $this->nonce ) )
		// 	return;

		global $blipfoto;

		if ( ! check_blip_permission() )
			return;

		$post_id = absint( $_POST['post_id'] );

		if ( ! has_post_thumbnail( $post_id ) ) {

			$response = array(
				'result'  => 'error',
				'message' => 'No featured image - please set the featured image, save the post, then try again'
			);

		} else {

			$blip = new blip( $blipfoto->key, blip_auth_option( 'secret' ) );

			$thumb_id   = get_post_thumbnail_id( $post_id );
			$thumb_src  = wp_get_attachment_image_src( $thumb_id, 'full' );
			$url        = $thumb_src[0];
			$attachment = get_post( $thumb_id );

			if ( 1 != 1 ) {
			// @TODO@
			// if ( ! $blip->validate_date( '2013-08-06' ) ) {

				$response = array(
					'result'  => 'error',
					'message' => 'You\'ve already blipped on this date'
				);

			} else {

				$postdata = array(
					'image_url'   => $url,
					'title'       => $attachment->post_title,
					'description' => $attachment->post_content
					);

				$json = $blip->post_entry( $postdata );

				$response = array(
					'result'  => 'error',
					'message' => 'log time'
				);

			}

		}

		header('Content-type: application/json');
		die( json_encode( $response ) );

	}



	function check() {

		global $post;

		if ( !check_blip_permission() )
			return;

		if ( is_blipped() or !is_blip_post_type() or !isset( $_GET['post'] ) or !isset( $_GET['action'] ) or 'edit' != $_GET['action'] )
			return;

		if ( ! has_post_thumbnail( $post->ID ) ) {

			$this->notice['type'] = 'updated';
			$this->notice['message'] = '<p>Please add a featured image if you\'d like to create a Blip from this post</a></p>';

		} else {

			$this->notice['type'] = 'updated';
			$this->notice['message'] = '<p><a href="' . wp_nonce_url( admin_url( '?page=blipfoto&action=blip&post_id=' . $post->ID ), 'blipfoto-create-nonce' ) . '">Create a Blip from this post</a></p>';

		}

	}



	function add_meta_box() {

		if ( ! check_blip_permission() )
			return;

		if ( ! is_blip_post_type() )
			return;

		if ( ! $types = blip_post_types() )
			return;

		foreach ( $types as $type ) {

			add_meta_box(
				'blipfoto',
				'Blipfoto',
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
				echo '<p><a id="blip-this" class="button" data-post="' . $post->ID . '" href="#">Blip this post</a><span id="' . $this->nonce . '" class="hidden">' . wp_create_nonce( $this->nonce ) . '</span></p>';
			} else {
				echo '<p>You must set a featured image before you can blip this post</p>';
			}
		}

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
					update_post_meta( $post_id, 'blipfoto-image-date', absint( $image_date ) );
				}
			}
		}

	}



	function notice() {

		if ( !current_user_can( 'edit_posts' ) or !isset( $this->notice ) or empty( $this->notice ) )
			return;

		echo '<div class="' . $this->notice['type'] . '" id="blipfoto-notice">' . $this->notice['message'] . '</div>';

	}



}



$blipfoto_post = new blipfoto_post;
