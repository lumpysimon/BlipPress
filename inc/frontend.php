<?php



class blippress_frontend {



	function __construct() {

		add_action( 'wp_enqueue_scripts', array( $this, 'styles' ) );

	}



	function styles() {

		if ( blip_option( 'css' ) ) {

			wp_register_style(
				'blippress-frontend',
				BLIPPRESS_PLUGIN_DIR . 'css/frontend.css',
				null,
				filemtime( BLIPPRESS_PLUGIN_PATH . 'css/frontend.css' )
				);

			wp_enqueue_style( 'blippress-frontend' );

		}

	}



} // class



global $blippress_frontend;

$blippress_frontend = new blippress_frontend;
