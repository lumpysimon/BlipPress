<?php



class blippress_upgrade {



	function __construct() {

		add_action( 'admin_init', array( $this, 'maybe_upgrade' ) );

	}



	function maybe_upgrade() {

		global $blippress_settings;

		$v = blippress_prefix() . 'version';

		// v0.1 doesn't have the 'show' option
		if ( get_option( $v ) < 0.2 ) {
			$opts = blippress_options();
			$opts['show'] = 'above';
			$blippress_settings->update( $opts );
			update_option( $v, blippress_version() );
		}

	}



} // class



global $blippress_upgrade;

$blippress_upgrade = new blippress_upgrade;
