<?php



class blipfoto_frontend {



	function __construct() {

		add_action( 'wp_enqueue_scripts', array( $this, 'styles' ) );

	}



	function styles() {

		if ( blip_option( 'css' ) ) {

			wp_register_style(
				'blipfoto-frontend',
				BLIPFOTO_PLUGIN_DIR . 'css/frontend.css',
				null,
				filemtime( BLIPFOTO_PLUGIN_PATH . 'css/frontend.css' )
				);

			wp_enqueue_style( 'blipfoto-frontend' );

		}

	}



} // class



$blipfoto_frontend = new blipfoto_frontend;
