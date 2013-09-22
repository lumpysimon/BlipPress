<?php



class blippress_dashboard {



	var $slug = 'blippress';



	function __construct() {

		add_action( 'admin_enqueue_scripts', array( $this, 'styles' ) );
		add_action( 'admin_menu',            array( $this, 'add_page' ), 100 );
		add_action( 'admin_menu',            array( $this, 'rename_submenu' ), 120 );

	}



	function styles() {

		if ( 'mp6' == get_user_option( 'admin_color' ) ) {

			wp_register_style(
				blippress_prefix() . 'icon',
				blippress_style( 'icon', 'dir' ),
				null,
				filemtime( blippress_style( 'icon', 'path' ) )
				);

			wp_enqueue_style( blippress_prefix() . 'icon' );

		}

		$screen = get_current_screen();

		if ( 'toplevel_page_blippress' == $screen->id ) {

			wp_register_style(
				blippress_prefix() . 'dashboard',
				blippress_style( 'dashboard', 'dir' ),
				null,
				filemtime( blippress_style( 'dashboard', 'path' ) )
				);

			wp_enqueue_style( blippress_prefix() . 'dashboard' );

		}

	}



	function add_page() {

		add_menu_page(
			'BlipPress',
			'BlipPress',
			'manage_options',
			$this->slug,
			array( $this, 'render_page' )
			);

	}



	function rename_submenu() {

		global $submenu;

		if ( isset( $submenu[$this->slug] ) ) {
			$submenu[$this->slug][0][0] = 'Dashboard';
		}

	}



	function render_page() {

		global $blippress, $blippress_settings, $blippress_shortcodes;

		?>

		<div class="wrap">

			<h2>BlipPress Dashboard</h2>

			<div class="postbox-container" style="width:60%;">

				<?php if ( blippress_check_permission() ) { ?>

					<h3>Your Blipfoto journal</h3>

					<p>These blips are displayed using your default settings. You can change them at the <a href="<?php echo admin_url( 'options-general.php?page=' . $blippress_settings->slug() ); ?>">settings page</a>.</p>

					<?php echo $blippress_shortcodes->single_latest( array( 'user' => blippress_auth_option( 'username' ) ) ); ?>

					<?php echo blippress_latest(); ?>

				<?php } else {
					echo blippress_authenticate_message( ' to show this page.' );
				} ?>

			</div>

			<div class="postbox-container" style="width:20%;">

				<div class="metabox-holder">

					<div class="meta-box-sortables" style="min-height:0;">
						<div class="postbox blippress-info-box" id="blippress-support">
							<h3 class="hndle"><span>Need Help?</span></h3>
							<div class="inside">
								<p>If something's not working, the first step is to read the <a href="<?php echo blippress_plugin_page(); ?>">description</a> and <a href="<?php echo blippress_plugin_page(); ?>/faq/">FAQ</a> pages.</p>
								<p>If your question is not answered there, please check the official <a href="http://wordpress.org/support/plugin/blippress">support forum</a> and create a new topic if required.</p>
							</div>
						</div>
					</div>

					<div class="meta-box-sortables" style="min-height:0;">
						<div class="postbox blippress-info-box" id="blippress-suggest">
							<h3 class="hndle"><span>Like this plugin?</span></h3>
							<div class="inside">
								<p>If this plugin has helped you showcase your photography skills, please consider supporting it:</p>
								<ul>
									<li><a href="<?php echo blippress_plugin_page(); ?>">Rate it and let other people know it works</a>.</li>
									<li>Link to or share <a href="<?php echo blippress_plugin_page(); ?>" target="_blank">the plugin page</a> on Twitter or Facebook.</li>
									<li>Tell people about it on your Blipfoto journal.</li>
									<li>Write a review on your website or blog.</li>
									<li>Make a <a href="<?php echo blippress_website(); ?>/donate/">donation</a>.</li>
									<li><a href="http://lumpylemon.co.uk/">Commission me</a> for WordPress development, plugin or design work (or photography if you're feeling brave!).</li>
								</ul>
							</div>
						</div>
					</div>

					<div class="meta-box-sortables" style="min-height:0;">
						<div class="postbox blippress-info-box" id="blippress-lumpysimon">
							<h3 class="hndle"><span><a href="<?php blippress_user_url( $blippress->me ); ?>"><?php echo $blippress->me; ?></a> on Blipfoto</span></h3>
							<div class="inside">
								<?php echo blippress_latest( array( 'user' => $blippress->me, 'num' => 12, 'size' => 'small' ) ); ?>
							</div>
						</div>
					</div>

				</div>

			</div>

		</div>

		<?php

	}



} // class



global $blippress_dashboard;

$blippress_dashboard = new blippress_dashboard;
