<?php



class blipfoto_admin {



	function __construct() {

		add_action( 'admin_menu', array( $this, 'add' ) );

	}



	function add() {

		add_submenu_page(
			'blippress',
			'BlipPress Options',
			'Settings',
			'manage_options',
			'blippress-options',
			array( 'blipPressOptions', 'page' )
			);

	}



} // class



// let's go!
$blipfoto_admin = new blipfoto_admin;
