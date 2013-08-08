<?php



class blipPressPost {



	var $notice = array();



	public function __construct() {

		add_action( 'add_meta_boxes', array( $this, 'check' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action( 'save_post',      array( $this, 'save_date' ), 25, 2 );
		add_action( 'admin_footer',   array( $this, 'notice' ) );

	}



	function check() {

		global $post;

		if ( !check_blip_permission() or !check_blip_options() )
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



	function add_meta_box( $post_type ) {

		if ( !is_blipped() or !is_blip_post_type() )
			return;

		add_meta_box(
			'blippress',
			'Blipped!',
			array( $this, 'meta_box' ),
			$post_type,
			'normal'
		);

	}



	function meta_box( $post ) {

		if ( is_blipped() ) {
			echo sprintf(
					'<p><a href="%s" target="_blank">%s</a></p>',
					get_blip_url( get_blip_id() )
					);
		} else {

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
					update_post_meta( $post_id, 'blippress-image-date', absint( $image_date ) );
				}
			}
		}

	}



	function notice() {

		if ( !current_user_can( 'edit_posts' ) or !check_blip_options() or !isset( $this->notice ) or empty( $this->notice ) )
			return;

		echo '<div class="' . $this->notice['type'] . '" id="blippress-notice">' . $this->notice['message'] . '</div>';

	}



}



$blippress_post = new blipPressPost;



?>