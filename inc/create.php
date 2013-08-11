<?php



class blipfoto_create {



	var $notice = array();
	var $ok     = true;
	var $error  = false;
	var $slug   = 'blipfoto-create';



	public function __construct() {

		add_action( 'admin_init',   array( $this, 'check' ) );
		add_action( 'admin_footer', array( $this, 'notice' ) );
		add_action( 'admin_menu',   array( $this, 'add_page' ) );

	}



	function check() {

		if ( 'blippress' != $_GET['page'] )
			return;

		if ( !check_blip_permission() ) {
			$this->error = 'You cannot create a Blip until you have configured BlipPress';
			return;
		}

		if ( isset( $_GET['post_id'] ) and isset( $_GET['action'] ) and 'blip' == $_GET['action'] ) {
			check_admin_referer( 'blippress-create-nonce' );
			$this->notice['type'] = 'updated';
			$this->notice['message'] = '<p>Checking &amp; processing your Blip, please be patient...</p>';
			$this->create( absint( $_GET['post_id'] ) );
			return;
		}

	}



	function create( $post_id ) {

		global $blippress;

		if ( !check_blip_permission() ) {
			$this->error = 'You cannot create a Blip until you have authenticated your Blipfoto account.';
		}

		$post = get_post( $post_id );

		if ( ! $post ) {
			$this->error = 'There is no post with ID ' . $post_id;
			return;
		}

		if ( is_blipped( $post_id ) ) {
			$this->error = sprintf(
				'Already blipped! <a href="%s" target="_blank">%s</a>.',
				get_blip_url( get_blip_id() )
				);
			return;
		}

		if ( ! has_post_thumbnail( $post_id ) ) {
			$this->error = sprintf(
				'Post %s does not have a featured image. Please <a href="%s">edit the post</a> and set one, then try again.',
				$post_id,
				get_edit_post_link( $post_id )
				);
			return;
		}

		$id = get_post_thumbnail_id( $post_id );

		if ( ! $meta = wp_get_attachment_metadata( $id ) ) {
			$this->error = sprintf(
				'The featured image does not have any EXIF data. Please <a href="%s">edit the post</a> and try a different image.',
				$post_id,
				get_edit_post_link( $post_id )
				);
			return;
		}

		if ( ! isset( $meta['image_meta']['created_timestamp'] ) ) {
			$this->error = sprintf(
				'The featured image does not have the date in its EXIF data. Please <a href="%s">edit the post</a> and try a different image.',
				$post_id,
				get_edit_post_link( $post_id )
				);
			return;
		}

		$opts = get_option( 'blippress' );

		// everything looks ok so far, so let's get started...
		$blip = new blip(
			$blippress->key,
			$opts['access-code'],
			array( 'id_token' => $opts['token'],
				'test' => true
				)
			);

		$postdata = array();

		// create the entry date from the image exif data
		$postdata['date'] = date( 'Y-m-d', $meta['image_meta']['created_timestamp'] );

		// check if the user is allowed to post on that date
		$response = $blip->get_date_validation( $postdata['date'] );

		$postdata['title']       = get_the_title( $post_id );
		$postdata['description'] = $this->prepare_content( $post->post_content );
		$postdata['image_url']   = wp_get_attachment_url( $id );

		if ( $tags = wp_get_post_terms( $post_id, 'post_tag', array( 'fields' => 'names' ) ) ) {
			$postdata['tags'] = implode( ',', $tags );
		}

		// $response = $blip->post_entry( $postdata );

	}



	function prepare_content( $content ) {

		$content = str_replace( '<strong>', '[b]', $content );
		$content = str_replace( '</strong>', '[/b]', $content );

		$content = str_replace( '<em>', '[i]', $content );
		$content = str_replace( '</em>', '[/i]', $content );

		$content = strip_tags( $content );
		$content = strip_shortcodes( $content );

		return $content;

	}



	function notice() {

		if ( !current_user_can( 'edit_posts' ) or !isset( $this->notice ) or empty( $this->notice ) )
			return;

		echo '<div class="' . $this->notice['type'] . '" id="blippress-notice">' . $this->notice['message'] . '</div>';

	}



	function add_page() {

		add_submenu_page(
			'blipfoto',
			'Create a blip',
			'Create a blip',
			'edit_posts',
			$this->slug,
			array( $this, 'render_page' )
			);

	}



	function render_page() {

		?>

		<div class="wrap">

			<h2>Create a blip</h2>

			<?php if ( check_blip_permission() ) { ?>

				<h3>Something</h3>

			<?php } else {
				echo blipfoto_authenticate_message( ' to create a blip.' );
			} ?>

			<?php if ( $this->error ) {
				printf( '<p>ERROR! %s</p>', $this->error );
			} ?>

		</div>

		<?php

	}



}



$blipfoto_create = new blipfoto_create;
