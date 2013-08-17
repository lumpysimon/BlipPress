<?php



class blippress_widgets {



	function __construct() {

		add_action( 'widgets_init', array( $this, 'register' ) );
	}



	function register() {

		if ( blippress_check_permission() ) {
			register_widget( 'blippress_multi_widget' );
		}

	}



} // class



$blippress_widgets = new blippress_widgets;
