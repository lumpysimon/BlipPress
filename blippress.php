<?php
/*
Plugin Name: Blipfoto for WordPress
Plugin URI:  http://wpblipfoto.org
Description: All the Blipfoto things but for WordPress
Version:     0.1
Author:      Simon Blackbourn @ Lumpy Lmeon
Author URI:  https://twitter.com/lumpysimon



-----
To Do

singularisation



-----------
Description

@TODO@


---------
Changelog

@TODO@



-------
License

This is a plugin for WordPress (http://wordpress.org).

Copyright (c) Simon Blackbourn. All rights reserved.

Released under the GPL license:
http://www.opensource.org/licenses/gpl-license.php

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.

See the GNU General Public License for more details.
*/



require 'inc/blip-php.php';
require 'inc/functions.php';
require 'inc/options.php';
require 'inc/shortcodes.php';
require 'inc/post.php';
require 'inc/create.php';
require 'inc/admin-pages.php';



class blipfoto {



	var $plugin_page = 'http://wordpress.org/extend/plugins/blipfoto';
	var $api_version = 2;
	var $me          = 'lumpysimon';
	var $default_num = 12;
	var $key         = '60fe44de7bdc715a972a578e3c7eb7e5';
	var $exif        = array(
						'aperture' => 'Aperture',
						'exposure' => 'Exposure',
						'focal'    => 'Focal length',
						'iso'      => 'ISO',
						'model'    => 'Camera'
						);



	public function __construct() {

		register_activation_hook(   __FILE__, array( $this, 'hello'   ) );
		// register_deactivation_hook( __FILE__, array( $this, 'goodbye' ) );

	}



	function hello() {

		// if the options are not set, then set the defaults

		$opts = get_option( 'blippress' );

		$defaults = array(
						'access-code' => '3f3102407026a35b7f1b1bcefb924c0e',
						'num'        => $this->default_num,
						'post-types' => array( 'post' ) // here in case of future ability to choose post type
						);

		if ( false === $opts ) {
			$new = $defaults;
		} else {
			$new = array_merge( $defaults, $opts );
		}

		update_option( 'blippress', $new );

	}



	function goodbye() {
	}



}



$blipfoto = new blipfoto;
