<?php



class blipfoto_dashboard {



	var $slug   = 'blipfoto';



	function __construct() {

		add_action( 'admin_enqueue_scripts', array( $this, 'styles' ) );
		add_action( 'admin_menu',            array( $this, 'add_page' ) );
		add_action( 'admin_menu',            array( $this, 'rename_submenu' ), 1000 );

	}



	function styles() {

		if ( 'mp6' == get_user_option( 'admin_color' ) ) {

			wp_register_style(
				'blipfoto-icon',
				BLIPFOTO_PLUGIN_DIR . 'css/icon.css',
				null,
				filemtime( BLIPFOTO_PLUGIN_PATH . 'css/icon.css' )
				);

			wp_enqueue_style( 'blipfoto-icon' );

		}

		$screen = get_current_screen();

		if ( 'toplevel_page_blipfoto' == $screen->id ) {

			wp_register_style(
				'blipfoto-dashboard',
				BLIPFOTO_PLUGIN_DIR . 'css/dashboard.css',
				null,
				filemtime( BLIPFOTO_PLUGIN_PATH . 'css/dashboard.css' )
				);

			wp_enqueue_style( 'blipfoto-dashboard' );

		}

	}



	function add_page() {

		add_menu_page(
			'Blipfoto',
			'Blipfoto',
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

		$opts = get_option( 'blipfoto' );

		?>

		<div class="wrap">

			<h2>Blipfoto Dashboard</h2>

			<div class="postbox-container" style="width:65%;">

				<h3>Your recent blips</h3>

				<?php echo blipfoto_bliplatest( array( 'num' => 30, 'css' => false ) ); ?>

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



$blipfoto_dashboard = new blipfoto_dashboard;
