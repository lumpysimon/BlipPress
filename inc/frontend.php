<?php



class blippress_frontend {



	function __construct() {

		add_action( 'wp_enqueue_scripts', array( $this, 'styles' ) );
		add_filter( 'the_content', array( $this, 'content' ) );

	}



	function styles() {

		if ( blippress_option( 'css' ) ) {

			wp_register_style(
				blippress_prefix() . 'frontend',
				BLIPPRESS_PLUGIN_DIR . 'css/frontend.css',
				null,
				filemtime( BLIPPRESS_PLUGIN_PATH . 'css/frontend.css' )
				);

			wp_enqueue_style( blippress_prefix() . 'frontend' );

		}

	}



	function content( $content ) {

		global $blippress_shortcodes;

		if ( $entry_id = is_blipped() ) {
			$content = $blippress_shortcodes->single_id( array( 'id' => $entry_id ) ) . $content;
		}

		return $content;

	}



} // class



global $blippress_frontend;

$blippress_frontend = new blippress_frontend;
