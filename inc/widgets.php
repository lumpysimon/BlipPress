<?php



class blippress_widgets {



	function __construct() {

		add_action( 'widgets_init', array( $this, 'register' ) );
	}



	function register() {

		if ( blippress_check_permission() ) {
			register_widget( 'blippress_multi_widget' );
			register_widget( 'blippress_single_widget' );
			register_widget( 'blippress_latest_widget' );
		}

	}



} // class



$blippress_widgets = new blippress_widgets;
