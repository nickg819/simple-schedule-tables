<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit();

function sst24_delete_plugin() {
	global $wpdb;

	delete_option( 'sst24' );

	$posts = get_posts( array(
		'numberposts' => -1,
		'post_type' => 'sst24_contact_form',
		'post_status' => 'any' ) );

	foreach ( $posts as $post )
		wp_delete_post( $post->ID, true );

	$table_name = $wpdb->prefix . "simple-schedule-tables";

	$wpdb->query( "DROP TABLE IF EXISTS $table_name" );
}

sst24_delete_plugin();

?>