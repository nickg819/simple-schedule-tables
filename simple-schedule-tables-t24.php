<?php
/*
Plugin Name: Simple Schedule Table 24
Plugin URI: http://www.colossalhippo.com/plugins/simplescheduletable
Description: Plugin for building an easy to manage schedule table.
Author: Nick Gassmann
Version: 0.1
Author URI: http://www.colossalhippo.com
License: GPL2
*/
	
	
/*  Copyright 2011  Nick Gassmann  (email : nick@colossalhippo.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

	define( 'SST24_VERSION', '0.1' );
	
	if ( ! defined( 'SST24_PLUGIN_BASENAME' ) )
	define( 'SST24_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

	if ( ! defined( 'SST24_PLUGIN_NAME' ) )
		define( 'SST24_PLUGIN_NAME', trim( dirname( SST24_PLUGIN_BASENAME ), '/' ) );

	if ( ! defined( 'SST24_PLUGIN_DIR' ) )
		define( 'SST24_PLUGIN_DIR', WP_PLUGIN_DIR . '/' . SST24_PLUGIN_NAME );

	if ( ! defined( 'SST24_PLUGIN_URL' ) )
		define( 'SST24_PLUGIN_URL', WP_PLUGIN_URL . '/' . SST24_PLUGIN_NAME );
		
	if ( ! defined( 'SST24_LOAD_JS' ) )
		define( 'SST24_LOAD_JS', true );

	if ( ! defined( 'SST24_LOAD_CSS' ) )
		define( 'SST24_LOAD_CSS', true );

	/* If you or your client hate to see about donation, set this value false. */
	if ( ! defined( 'SST24_SHOW_DONATION_LINK' ) )
		define( 'SST24_SHOW_DONATION_LINK', true );
	
	if ( ! defined( 'SST24_ADMIN_READ_CAPABILITY' ) )
		define( 'SST24_ADMIN_READ_CAPABILITY', 'edit_posts' );

	if ( ! defined( 'SST24_ADMIN_READ_WRITE_CAPABILITY' ) )
		define( 'SST24_ADMIN_READ_WRITE_CAPABILITY', 'publish_pages' );

	require_once SST24_PLUGIN_DIR . '/settings.php';

?>

