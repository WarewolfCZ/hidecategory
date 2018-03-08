<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
/*
Plugin Name: HideCategory
Plugin URI: http://www.warewolf.cz/hidecategory/
Description: Hide categories from menu
Version: 1.0.0
Author: WarewolfCZ
Author URI: http://www.warewolf.cz
License: GPLv2
*/

/* 
Copyright (C) 2018 WarewolfCZ

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
*/

define( 'HCAT_VERSION', '1.0.0' );
define( 'HCAT_MINIMUM_WP_VERSION', '4.2' );
define( 'HCAT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

require_once( HCAT_PLUGIN_DIR . '/includes/HideCategory.class.php' );
$hideCategory = new HideCategory();

register_activation_hook( __FILE__, array( $hideCategory, 'plugin_activation' ) );
register_deactivation_hook( __FILE__, array( $hideCategory, 'plugin_deactivation' ) );


add_action( 'init', array( $hideCategory, 'init' ) );

if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
	require_once( HCAT_PLUGIN_DIR . '/admin/HideCategoryAdmin.class.php' );
	$hideCategoryAdmin = new HideCategoryAdmin();
	add_action( 'init', array( $hideCategoryAdmin, 'init' ) );
}
