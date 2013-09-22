<?php
/*
Plugin Name: BlipPress
Plugin URI:  http://blippress.com
Description: Display entries from your Blipfoto journal and post to Blipfoto from your WordPress website
Version:     0.1
Author:      Simon Blackbourn
Author URI:  https://twitter.com/lumpysimon
License:     GPL2



	------------
	What it does
	------------

	Blipfoto (http://blipfoto.com) is an online daily photo journal. Each day you can upload one photo and add some words. It is also a very friendly community where you can comment on and rate each other's photos.

	BlipPress lets you easily integrate your Blipfoto journal into your WordPress website. You can display single or multiple entries from your or other people's journals in your posts and pages or in a widget, as well as posting to your journal directly from within WordPress.



	-------
	Credits
	-------

	This plugin is not an official Blipfoto product.
	I have written it to combine my two main interests in life:
	photography (in which Blipfoto plays a significant role) and WordPress development.

	The Blipfoto website is at http://blipfoto.com
	My Blipfoto journal can be viewed at http://blipfoto.com/lumpysimon
	Detailed instructions, news and other random bits n bobs connected with BlipPress at http://blippress.com
	If you're so inclined you can follow me on Twitter at https://twitter.com/lumpysimon



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



// define constants, if not defined already
if ( ! defined( 'BLIPPRESS_PLUGIN_PATH' ) )
	define( 'BLIPPRESS_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

if ( ! defined( 'BLIPPRESS_PLUGIN_DIR' ) )
	define( 'BLIPPRESS_PLUGIN_DIR', plugin_dir_url( __FILE__ ) );



// include all the component files from the 'inc' folder
foreach ( glob( dirname( __FILE__ ) . '/inc/*.php' ) as $component ) {
	require $component;
}



/**
 * The main BlipPress class.
 * Doesnt'actually do much apart from define some variables and run the activation function.
 */
class blippress {



	var $plugin_page    = 'http://wordpress.org/extend/plugins/blippress';
	var $website        = 'blippress.com';
	var $me             = 'lumpysimon';
	var $permissions_id = '139459';
	var $key            = '46a9df14f768a45619a5c0eb312d51a3';
	var $secret         = 'd96e00ecb17c1fd33e37b73a0c483fef';
	var $default_num    = 16;
	var $prefix         = 'blippress-';



	public function __construct() {

		register_activation_hook( __FILE__, array( $this, 'hello' ) );

	}



	function hello() {

		$opts = blippress_options();

	}



}



global $blippress;

$blippress = new blippress;
