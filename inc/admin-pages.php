<?php



class blipfoto_admin {



	function __construct() {

		add_action( 'admin_menu', array( $this, 'add' ) );

	}



	function add() {

		add_submenu_page(
			'blipfoto',
			'Blipfoto Options',
			'Settings',
			'manage_options',
			'blipfoto-options',
			array( 'blipfoto_options', 'page' )
			);

	}



} // class



$blipfoto_admin = new blipfoto_admin;
