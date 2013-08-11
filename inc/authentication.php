<?php



// @@TODO@@
// encrypt token before storing?



class blipfoto_authentication {



	var $slug   = 'blipfoto-authentication';
	var $option = 'blipfoto-authentication';
	var $notice = array();



	function __construct() {

		add_action( 'admin_menu',   array( $this, 'add_page' ) );
		add_action( 'admin_enqueue_scripts',   array( $this, 'check' ) );
		add_action( 'admin_footer', array( $this, 'notice' ) );

	}



	function get_option() {

		return get_option( $this->option );

	}



	function add_page() {

		add_submenu_page(
			'blipfoto',
			'Blipfoto Authentication',
			'Authentication',
			'manage_options',
			$this->slug,
			array( $this, 'render_page' )
			);

	}



	function is_authentication_page() {

		$screen = get_current_screen();

		return ( 'blipfoto_page_' . $this->slug == $screen->id );

	}



	function page_url() {

		return admin_url( 'admin.php?page=' . $this->slug );

	}



	function check() {

		global $blipfoto;

		if ( ! current_user_can( 'manage_options' ) )
			return;

		if ( ! is_admin() )
			return;

		if ( !$this->is_authentication_page() and !check_blip_permission() )  {
			$this->notice['type']    = 'error';
			$this->notice['message'] = '<p><strong>Blipfoto needs some attention</strong>: Please <a href="' . $this->page_url() . '">authenticate your Blipfoto account</a></p>';
		}

		if ( ! $this->is_authentication_page() )
			return;

		$opts = get_option( $this->option );

		if ( isset( $_POST['request-permission'] ) and 'go' == $_POST['request-permission'] ) {

			$blip = new blip( $blipfoto->key );
			$blip->get_temp_token( $blipfoto->permissions_id, $this->page_url() );

		}

		if ( isset( $_GET['temp_token'] ) and $temp_token = self::alphanumeric( $_GET['temp_token'] ) ) {
			$blip = new blip( $blipfoto->key, $blipfoto->secret );
			if ( $data = $blip->get_user_token( $temp_token ) ) {
				$opts = array(
					'username' => $data->display_name,
					'token'    => $data->token,
					'secret'   => $data->secret
					);
				update_option( $this->option, $opts );
			}
		}

		// if ( isset( $_GET['error'] ) ) {
		// 	if ( 0 === $_GET['error'] ) {
		// 		} else {
		// 			if ( isset( $_GET['token'] ) and $token = self::lowercase_alphanumeric( $_GET['token'] ) and $username = self::lowercase_alphanumeric( $_GET['display_name'] ) ) {
		// 				$opts['token'] = $token;
		// 				$opts['username'] = $username;
		// 				update_option( 'blipfoto', $opts );
		// 				$this->notice['type'] = 'updated';
		// 				$this->notice['message'] = '<p>Permission successfully granted for <em>' . $username . '</em></p>';
		// 			} else {
		// 				$this->notice['type'] = 'error';
		// 				$this->notice['message'] = '<p>Sorry, there was an error while granting permission, please try again</p>';
		// 			}
		// 		}
		// 	} else {
		// 		$this->notice['type'] = 'error';
		// 		$this->notice['message'] = '<p>Sorry, there was an error communicating with Blipfoto, please try again</p>';
		// 	}
		// 	return;
		// }

	}



	function alphanumeric( $str ) {

		return ereg_replace( '[^A-Za-z0-9]', '', $str );

	}



	function lowercase_alphanumeric( $str ) {

		return strtolower( self::alphanumeric( $str ) );

	}



	function notice() {

		if ( current_user_can( 'manage_options' ) and isset( $this->notice ) and !empty( $this->notice ) ) {
			echo '<div class="' . $this->notice['type'] . '" id="blipfoto-notice">' . $this->notice['message'] . '</div>';
		}

	}



	function render_page() {

		$opts = get_option( $this->option );

		?>

		<div class="wrap">

			<h2>Blipfoto Authentication</h2>

			<div class="postbox-container">

				<h3>Permission</h3>

				<?php if ( ! check_blip_permission() ) { ?>
					<p>You need to grant permission for Blipfoto to access your account.</p>
					<form method="post">
						<p>
							<input type="hidden" name="request-permission" value="go">
							<input class="button-primary" name="submit" type="submit" value="Grant Permission">
						</p>
					</form>
				<?php } else { ?>
					<p>Looking good... Your website has permission to access the following Blipfoto account: <strong><?php echo $opts['username']; ?></strong></p>
				<?php } ?>

			</div>

		</div>

		<?php

	}



} // class



$blipfoto_authentication = new blipfoto_authentication;
