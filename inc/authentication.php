<?php



/**
 * The authentication class.
 * Handles everything to do with granting permission for BlipPress
 * to access your Blipfoto account.
 */
class blippress_authentication {



	var $slug   = 'authentication';
	var $option = 'authentication';
	var $notice = array();



	function __construct() {

		add_action( 'admin_menu',            array( $this, 'add_page' ), 110 );
		add_action( 'admin_enqueue_scripts', array( $this, 'check' ) );
		add_action( 'admin_footer',          array( $this, 'notice' ) );

	}



	function option() {

		return blippress_prefix() . $this->option;

	}



	function slug() {

		return blippress_prefix() . $this->slug;

	}



	function add_page() {

		global $blippress_dashboard;

		add_submenu_page(
			$blippress_dashboard->slug,
			'BlipPress Authentication',
			'Authentication',
			'manage_options',
			$this->slug(),
			array( $this, 'render_page' )
			);

	}



	function is_authentication_page() {

		$screen = get_current_screen();

		return ( 'blippress_page_' . $this->slug() == $screen->id );

	}



	function page_url() {

		return admin_url( 'admin.php?page=' . $this->slug() );

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

		$opts = get_option( $this->option() );

		if ( isset( $_POST['revoke-permission'] ) and 'go' == $_POST['revoke-permission'] ) {
			delete_option( $this->option() );
			$blippress_cache->clear();
		}

		if ( isset( $_POST['request-permission'] ) and 'go' == $_POST['request-permission'] ) {

			$blip = new blipWP( $blippress->key );
			$blip->get_temp_token( $blippress->permissions_id, $this->page_url() );

		}

		if ( isset( $_GET['temp_token'] ) and $temp_token = blippress_alphanumeric( $_GET['temp_token'] ) ) {
			$blip = new blipWP( $blippress->key, $blippress->secret );
			if ( $data = $blip->get_user_token( $temp_token ) ) {
				$opts = array(
					'username' => $data->display_name,
					'token'    => $data->token,
					'secret'   => $data->secret
					);
				update_option( $this->option(), $opts );
				$blippress_cache->clear();
			}
		}

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

				<p>BlipPress needs your permission in order to access your Blipfoto account, and to prevent anyone accessing your account without your permission. When you click the button above, you will be redirected tothe 'apps' page on blipfoto.com where you can verify that you are happy to allow it. You only need to do this once.</p>
				<p>You will then be able to display blips here on your website and create entries on your Blipfoto journal from the post edit screen.</p>
				<p>BlipPress does not have the ability to create entries by itself, nor does it allow anyone else to, only you can manually do this. The creators of BlipPress have no way of accessing any of your account credentials or other personal details (the necessary access token and key are only stored here in your website's database).</p>
				<p>If you have any questions about this, please use the support forums at the <a href="<?php echo blippress_plugin_page(); ?>">plugin page</a>.</p>

			<?php } else { ?>

				<p>BlipPress has permission to access the following Blipfoto account: <a href="<?php blippress_user_url( blippress_auth_option( 'username' ) ); ?>"><?php blippress_user_url( blippress_auth_option( 'username' ), '' ); ?></a></p>

			<?php } ?>

			<?php if ( blippress_check_permission() ) { ?>

				<h3>Revoke</h3>

				<p>You can revoke permission by clicking the button below.</p>
				<p><strong>Important!</strong> Revoking permission will prevent any blips being displayed on your website and you will not be able to post to Blipfoto until you grant permission again. If you change to a different account, some blips displayed on your site may change.</p>
				<form method="post">
					<p>
						<input type="hidden" name="revoke-permission" value="go">
						<input class="button-primary" name="submit" type="submit" value="Revoke permission" onClick="return confirm('Are you sure you want to revoke permission?')">
					</p>
				</form>

				<h3>Other settings</h3>

				<p>Please visit the <a href="<?php echo admin_url( 'options-general.php?page=' . $blippress_settings->slug() ); ?>">settings page</a> to configure optional BlipPress settings.</p>

			<?php } ?>

		</div>

		<?php

	}



} // class



global $blippress_authentication;

$blippress_authentication = new blippress_authentication;
