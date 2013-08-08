<?php



// @@TODO@@
// encrypt token before storing?



class blipfoto_options {



	// @@TODO@@
	// should this be protected rather than private? i really should learn the difference...
	private static $permissions_url = 'http://www.blipfoto.com/getpermission/139459';

	var $notice = array();



	function __construct() {

		add_action( 'admin_init',   array( $this, 'check'  ) );
		add_action( 'admin_footer', array( $this, 'notice' ) );

	}



	function check() {

		global $blipfoto;

		if ( ! current_user_can( 'manage_options' ) )
			return;

		$opts = get_option( 'blipfoto' );

		if ( isset( $_POST['request-permission'] ) and 'go' == $_POST['request-permission'] ) {
			$url = self::$permissions_url . '?version=' . $blipfoto->api_version . '&callback_url=' . admin_url( 'admin.php?page=blipfoto-options' );
			wp_redirect( $url );
			exit;
		}

		if ( isset( $_GET['error'] ) ) {
			if ( '0' === $_GET['error'] ) {
				if ( isset( $_GET['token'] ) and $token = self::lowercase_alphanumeric( $_GET['token'] ) and $username = self::lowercase_alphanumeric( $_GET['display_name'] ) ) {
					$opts['token'] = $token;
					$opts['username'] = $username;
					update_option( 'blipfoto', $opts );
					$this->notice['type'] = 'updated';
					$this->notice['message'] = '<p>Permission successfully granted for <em>' . $username . '</em></p>';
				} else {
					$this->notice['type'] = 'error';
					$this->notice['message'] = '<p>Sorry, there was an error while granting permission, please try again</p>';
				}
			} else {
				$this->notice['type'] = 'error';
				$this->notice['message'] = '<p>Sorry, there was an error communicating with Blipfoto, please try again</p>';
			}
			return;
		}

		if ( !check_blip_permission() or !check_blip_options() ) {

			if ( !isset( $_GET['page'] ) or ( isset( $_GET['page'] ) and 'blipfoto-options' != $_GET['page'] ) ) {
				$this->notice['type'] = 'error';
				$this->notice['message'] = '<p><strong>Blipfoto needs some attention</strong>: Please <a href="' . admin_url( 'admin.php?page=blipfoto' ) . '">configure Blipfoto</a></p>';
			}

		}

	}



	function lowercase_alphanumeric( $str ) {

		return strtolower( ereg_replace( '[^A-Za-z0-9]', '', $str ) );

	}



	function notice() {

		if ( !current_user_can( 'manage_options' ) or !isset( $this->notice ) or empty( $this->notice ) )
			return;

		echo '<div class="' . $this->notice['type'] . '" id="blipfoto-notice">' . $this->notice['message'] . '</div>';

	}



	function page() {

		global $blipfoto;

		$opts = get_option( 'blipfoto' );

		?>

		<div class="wrap">

			<h2>Blipfoto Configuration</h2>

			<div class="postbox-container" style="width:65%;">

				<h3>Permission</h3>

				<?php if ( ! check_blip_permission() ) { ?>
					<p>You need to grant permission for Blipfoto to access your account.</p>
					<form method="post">
						<p class="submit">
							<input type="hidden" name="request-permission" value="go">
							<input class="button-primary" name="submit" type="submit" value="Grant Permission">
						</p>
					</form>
				<?php } else { ?>
					<p>Looking good... Blipfoto has permission to access your account</p>
				<?php } ?>

				<h3>Settings</h3>

				<?php if ( ! check_blip_options() ) { ?>
					<p>Please check your settings.</p>
					<form method="post">
						<p class="submit">
							<input class="button-primary" name="submit-settings" type="submit" value="Update">
						</p>
					</form>
				<?php } else { ?>
					<p>Looking good... All settings are ok</p>
				<?php } ?>

			</div>

			<div class="postbox-container" style="width:20%;">

				<div class="metabox-holder">

					<div class="meta-box-sortables" style="min-height:0;">
						<div class="postbox blipfoto-info" id="blipfoto-support">
							<h3 class="hndle"><span>Need Help?</span></h3>
							<div class="inside">
								<p>If something's not working, the first step is to read the <a href="<?php echo $blipfoto->plugin_page; ?>/faq/">[!!LINK!!] FAQ</a>.</p>
								<p>If your question is not answered there, please check the official <a href="http://wordpress.org/tags/blipfoto?forum_id=10">[!!LINK!!] support forum</a>.</p>
							</div>
						</div>
					</div>

					<div class="meta-box-sortables" style="min-height:0;">
						<div class="postbox blipfoto-info" id="blipfoto-suggest">
							<h3 class="hndle"><span>Like this plugin?</span></h3>
							<div class="inside">
								<p>If this plugin has helped you showcase your photography skills, please consider supporting it:</p>
								<ul>
									<li><a href="<?php echo $blipfoto->plugin_page; ?>">[!!LINK!!] Rate it and let other people know it works</a>.</li>
									<li>Link to or share <a href="<?php echo $blipfoto->plugin_page; ?>" target="_blank">the plugin page</a> on Twitter or Facebook.</li>
									<li>Write a review on your website or blog.</li>
									<li>Make a <a href="http://lumpylemon.co.uk/donate/">donation</a>.</li>
									<li><a href="http://lumpylemon.co.uk/">Commission me</a> for WordPress development, plugin or design work (or photography if you're feeling brave!).</li>
								</ul>
							</div>
						</div>
					</div>

					<div class="meta-box-sortables" style="min-height:0;">
						<div class="postbox blipfoto-info" id="blipfoto-lumpysimon">
							<h3 class="hndle"><span><?php echo $blipfoto->me; ?> on Blipfoto</span></h3>
							<div class="inside">
								<p>[!!Show my latest blips here!!]</p>
							</div>
						</div>
					</div>

				</div>

			</div>

		</div>

		<?php

	}



} // class



// let's go!
$blipfoto_options = new blipfoto_options;
