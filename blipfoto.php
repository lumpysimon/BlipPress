<?php
/*
Plugin Name: Blipfoto for WordPress
Plugin URI:  http://wpblip.org
Description: All the Blipfoto things but for WordPress
Version:     0.1
Author:      Simon Blackbourn @ Lumpy Lmeon
Author URI:  https://twitter.com/lumpysimon



	-------
	Credits
	-------

	This plugin is not an official Blipfoto product.
	I have written it to combine my two main interests in life:
	photography (in which Blipfoto plays a significant role) and WordPress development.

	The Blipfoto website is at http://blipfoto.com
	My own Blipfoto journal can be viewed at http://blipfoto.com/lumpysimon



	------------
	What it does
	------------

	@TODO@



	-------
	License
	-------

	Copyright (c) Simon Blackbourn. All rights reserved.

	Released under the GPL license:
	http://www.opensource.org/licenses/gpl-license.php

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.



*/



defined( 'ABSPATH' ) or die();



if ( ! defined( 'BLIPFOTO_PLUGIN_PATH' ) )
	define( 'BLIPFOTO_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

if ( ! defined( 'BLIPFOTO_PLUGIN_DIR' ) )
	define( 'BLIPFOTO_PLUGIN_DIR', plugin_dir_url( __FILE__ ) );



require 'inc/blipfoto-wordpress-api.php';
require 'inc/frontend.php';
require 'inc/settings.php';
require 'inc/dashboard.php';
require 'inc/authentication.php';
// require 'inc/create.php';
require 'inc/functions.php';
require 'inc/shortcodes.php';
require 'inc/post.php';



class blipfoto {



	var $plugin_page    = 'http://wordpress.org/extend/plugins/blipfoto-for-wordpress';
	var $me             = 'lumpysimon';
	var $permissions_id = '139459';
	var $key            = '46a9df14f768a45619a5c0eb312d51a3';
	var $secret         = 'd96e00ecb17c1fd33e37b73a0c483fef';
	var $default_num    = 16;



	public function __construct() {

		register_activation_hook( __FILE__, array( $this, 'hello' ) );

	}



	function hello() {

		global $blipfoto_settings;

		$blipfoto_settings->get();

	}



}



$blipfoto = new blipfoto;
