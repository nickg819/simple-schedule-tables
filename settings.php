<?php

	function sst24_plugin_path( $path = '' ) {
		return path_join( SST24_PLUGIN_DIR, trim( $path, '/' ) );
	}

	function sst24_plugin_url( $path = '' ) {
		return plugins_url( $path, SST24_PLUGIN_BASENAME );
	}

	function sst24_admin_url( $query = array() ) {
		global $plugin_page;

		if ( ! isset( $query['page'] ) )
			$query['page'] = $plugin_page;
	
		$path = 'admin.php';

		if ( $query = build_query( $query ) )
			$path .= '?' . $query;
	
		$url = admin_url( $path );

		return esc_url_raw( $url );
	}

	function sst24() {
		global $wpdb, $sst24;
	
		if ( is_object( $sst24 ) )
			return;

		$sst24 = (object) array(
			'processing_within' => '',
			'widget_count' => 0,
			'unit_count' => 0,
			'global_unit_count' => 0 );
	}

	sst24();

	//require_once SST24_PLUGIN_DIR . '/includes/functions.php';
	//require_once SST24_PLUGIN_DIR . '/includes/formatting.php';
	//require_once SST24_PLUGIN_DIR . '/includes/pipe.php';
	//require_once SST24_PLUGIN_DIR . '/includes/shortcodes.php';
	//require_once SST24_PLUGIN_DIR . '/includes/classes.php';
	//require_once SST24_PLUGIN_DIR . '/includes/taggenerator.php';

	/*if ( is_admin() )
		require_once SST24_PLUGIN_DIR . '/admin/admin.php';
	else
		require_once SST24_PLUGIN_DIR . '/includes/controller.php';*/

	add_action( 'plugins_loaded', 'sst24_set_request_uri', 9 );
	
	function sst24_set_request_uri() {
		global $sst24_request_uri;

		$sst24_request_uri = add_query_arg( array() );
	}

	function sst24_get_request_uri() {
		global $sst24_request_uri;

		return (string) $sst24_request_uri;
	}

	/* L10N */

	add_action( 'init', 'sst24_load_plugin_textdomain' );

	function sst24_load_plugin_textdomain() {
		load_plugin_textdomain( 'sst24', false, 'simple-schedule-tables/languages' );
	}

	/* Custom Post Type: Contact Form */

	add_action( 'init', 'sst24_register_post_types' );

	function sst24_register_post_types() {
		$args = array(
			'labels' => array(
				'name' => __( 'Contact Forms', 'sst24' ),
				'singular_name' => __( 'Contact Form', 'sst24' ) )
		);

		register_post_type( 'sst24_contact_form', $args );
	}

	/* Upgrading */

	add_action( 'init', 'sst24_upgrade' );

	function sst24_upgrade() {
		$opt = get_option( 'sst24' );

		if ( ! is_array( $opt ) )
			$opt = array();

		$old_ver = isset( $opt['version'] ) ? (string) $opt['version'] : '0';
		$new_ver = SST24_VERSION;

		if ( $old_ver == $new_ver )
			return;

		do_action( 'sst24_upgrade', $new_ver, $old_ver );

		$opt['version'] = $new_ver;

		update_option( 'sst24', $opt );

		if ( is_admin() && isset( $_GET['page'] ) && 'sst24' == $_GET['page'] ) {
			wp_redirect( sst24_admin_url( array( 'page' => 'sst24' ) ) );
			exit();
		}
	}

	add_action( 'sst24_upgrade', 'sst24_convert_to_cpt', 10, 2 );

	function sst24_convert_to_cpt( $new_ver, $old_ver ) {
		global $wpdb;

		if ( ! version_compare( $old_ver, '3.0-dev', '<' ) )
			return;

		$table_name = $wpdb->prefix . "contact_form_7";

		if ( ! $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) )
			return;

		$old_rows = $wpdb->get_results( "SELECT * FROM $table_name" );

		foreach ( $old_rows as $row ) {
			$q = "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_old_cf7_unit_id'"
				. $wpdb->prepare( " AND meta_value = %d", $row->cf7_unit_id );

			if ( $wpdb->get_var( $q ) )
				continue;

			$postarr = array(
				'post_type' => 'sst24_contact_form',
				'post_status' => 'publish',
				'post_title' => maybe_unserialize( $row->title ) );

			$post_id = wp_insert_post( $postarr );

			if ( $post_id ) {
				update_post_meta( $post_id, '_old_cf7_unit_id', $row->cf7_unit_id );
				update_post_meta( $post_id, 'form', maybe_unserialize( $row->form ) );
				update_post_meta( $post_id, 'mail', maybe_unserialize( $row->mail ) );
				update_post_meta( $post_id, 'mail_2', maybe_unserialize( $row->mail_2 ) );
				update_post_meta( $post_id, 'messages', maybe_unserialize( $row->messages ) );
				update_post_meta( $post_id, 'additional_settings',
					maybe_unserialize( $row->additional_settings ) );
			}
		}
	}

	/* Install and default settings */

	add_action( 'activate_' . SST24_PLUGIN_BASENAME, 'sst24_install' );

	function sst24_install() {
		if ( $opt = get_option( 'sst24' ) )
			return;

		sst24_load_plugin_textdomain();
		sst24_register_post_types();
		sst24_upgrade();

		if ( get_posts( array( 'post_type' => 'sst24_contact_form' ) ) )
			return;

		$contact_form = sst24_get_contact_form_default_pack(
			array( 'title' => sprintf( __( 'Contact form %d', 'sst24' ), 1 ) ) );

		$contact_form->save();
	}	

?>