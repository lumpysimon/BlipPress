<?php



class blippress_frontend {



	function __construct() {

		add_action( 'wp_enqueue_scripts', array( $this, 'styles' ) );

	}



	function styles() {

		global $blippress;

		if ( blippress_option( 'css' ) ) {

			wp_register_style(
				$blippress->prefix . 'frontend',
				BLIPPRESS_PLUGIN_DIR . 'css/frontend.css',
				null,
				filemtime( BLIPPRESS_PLUGIN_PATH . 'css/frontend.css' )
				);

			wp_enqueue_style( $blippress->prefix . 'frontend' );

		}

	}



} // class



global $blippress_frontend;

$blippress_frontend = new blippress_frontend;
