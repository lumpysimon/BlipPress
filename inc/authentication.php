<?php



// @@TODO@@
// encrypt token before storing?



class blippress_authentication {



	var $slug   = 'blippress-authentication';
	var $option = 'blippress-authentication';
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
			'blippress',
			'BlipPress Authentication',
			'Authentication',
			'manage_options',
			$this->slug,
			array( $this, 'render_page' )
			);

	}



	function is_authentication_page() {

		$screen = get_current_screen();

		return ( 'blippress_page_' . $this->slug == $screen->id );

	}



	function page_url() {

		return admin_url( 'admin.php?page=' . $this->slug );

	}



	function check() {

		global $blippress, $blippress_cache;

		if ( ! current_user_can( 'manage_options' ) )
			return;

		if ( ! is_admin() )
			return;

		if ( !$this->is_authentication_page() and !blippress_check_permission() )  {
			$this->notice['type']    = 'error';
			$this->notice['message'] = '<p><strong>Blipfoto needs some attention</strong>: Please <a href="' . $this->page_url() . '">authenticate your Blipfoto account</a></p>';
		}

		if ( ! $this->is_authentication_page() )
			return;

		$opts = get_option( $this->option );

		if ( isset( $_POST['revoke-permission'] ) and 'go' == $_POST['revoke-permission'] ) {
			delete_option( $this->option );
			$blippress_cache->clear();
		}

		if ( isset( $_POST['request-permission'] ) and 'go' == $_POST['request-permission'] ) {

			$blip = new blipWP( $blippress->key );
			$blip->get_temp_token( $blippress->permissions_id, $this->page_url() );

		}

		if ( isset( $_GET['temp_token'] ) and $temp_token = self::alphanumeric( $_GET['temp_token'] ) ) {
			$blip = new blipWP( $blippress->key, $blippress->secret );
			if ( $data = $blip->get_user_token( $temp_token ) ) {
				$opts = array(
					'username' => $data->display_name,
					'token'    => $data->token,
					'secret'   => $data->secret
					);
				update_option( $this->option, $opts );
				$blippress_cache->clear();
			}
		}

		// if ( isset( $_GET['error'] ) ) {
		// 	if ( 0 === $_GET['error'] ) {
		// 		} else {
		// 			if ( isset( $_GET['token'] ) and $token = self::lowercase_alphanumeric( $_GET['token'] ) and $username = self::lowercase_alphanumeric( $_GET['display_name'] ) ) {
		// 				$opts['token'] = $token;
		// 				$opts['username'] = $username;
		// 				update_option( 'blippress', $opts );
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
			echo '<div class="' . $this->notice['type'] . '" id="blippress-notice">' . $this->notice['message'] . '</div>';
		}

	}



	function render_page() {

		global $blippress_settings;

		?>

		<div class="wrap">

			<h2>Blipfoto Authentication</h2>

			<h3>Permission</h3>

			<?php if ( ! blippress_check_permission() ) { ?>

				<p>You need to grant permission for your website to access your Blipfoto account.</p>
				<form method="post">
					<p>
						<input type="hidden" name="request-permission" value="go">
						<input class="button-primary" name="submit" type="submit" value="Grant permission">
					</p>
				</form>

				<h4>What does this mean?</h4>

				<p>In order to prevent anyone accessing your Blipfoto account without your permission, the BlipPress app will connect to your account on the blipfoto.com website, so you can then verify that it is yours. You only need to do this once.</p>
				<p>You will then be able to display blips here on your website and create entries on Blipfoto from the post edit screen.</p>
				<p>BlipPress does not have the ability to create entries by itself, only you can manually do this. The creators of BlipPress have no way of accessing any of your account credentials or other personal details (the necessary access token and key are only stored here in your website's database).</p>
				<p>If you have any questions about this, please use the <a href="#" target="_blank">support forums</a> [@TODO@ link].</p>

			<?php } else { ?>

				<p>Your website has permission to access the following Blipfoto account: <a href="<?php blippress_user_url( blippress_auth_option( 'username' ) ); ?>"><?php blippress_user_url( blippress_auth_option( 'username' ), '' ); ?></a></p>

			<?php } ?>

			<?php if ( blippress_check_permission() ) { ?>

				<h3>Revoke</h3>

				<p>You can revoke permission by clicking the button below (e.g. if you wish to use a different account).</p>
				<p><strong>Important!</strong> Revoking permission will prevent any blips being displayed on your website and you will not be able to post to Blipfoto until you grant permission again.</p>
				<form method="post">
					<p>
						<input type="hidden" name="revoke-permission" value="go">
						<input class="button-primary" name="submit" type="submit" value="Revoke permission" onClick="return confirm('Are you sure you want to revoke permission?')">
					</p>
				</form>

				<h3>Other settings</h3>

				<p>Please visit the <a href="<?php echo admin_url( 'options-general.php?page=' . $blippress_settings->slug ); ?>">settings page</a> to configure optional BlipPress settings.</p>

			<?php } ?>

		</div>

		<?php

	}



} // class



global $blippress_authentication;

$blippress_authentication = new blippress_authentication;
