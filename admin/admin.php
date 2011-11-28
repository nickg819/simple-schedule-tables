<?php

function sst24_admin_has_edit_cap() {
	return current_user_can( SST24_ADMIN_READ_WRITE_CAPABILITY );
}

add_action( 'admin_init', 'sst24_admin_init' );

function sst24_admin_init() {
	if ( ! sst24_admin_has_edit_cap() )
		return;

	if ( isset( $_POST['sst24-save'] ) ) {
		$id = $_POST['post_ID'];
		check_admin_referer( 'sst24-save_' . $id );

		if ( ! $contact_form = sst24_contact_form( $id ) ) {
			$contact_form = new SST24_ContactForm();
			$contact_form->initial = true;
		}

		$contact_form->title = trim( $_POST['sst24-title'] );

		$form = trim( $_POST['sst24-form'] );

		$mail = array(
			'subject' => trim( $_POST['sst24-mail-subject'] ),
			'sender' => trim( $_POST['sst24-mail-sender'] ),
			'body' => trim( $_POST['sst24-mail-body'] ),
			'recipient' => trim( $_POST['sst24-mail-recipient'] ),
			'additional_headers' => trim( $_POST['sst24-mail-additional-headers'] ),
			'attachments' => trim( $_POST['sst24-mail-attachments'] ),
			'use_html' =>
				isset( $_POST['sst24-mail-use-html'] ) && 1 == $_POST['sst24-mail-use-html']
		);

		$mail_2 = array(
			'active' =>
				isset( $_POST['sst24-mail-2-active'] ) && 1 == $_POST['sst24-mail-2-active'],
			'subject' => trim( $_POST['sst24-mail-2-subject'] ),
			'sender' => trim( $_POST['sst24-mail-2-sender'] ),
			'body' => trim( $_POST['sst24-mail-2-body'] ),
			'recipient' => trim( $_POST['sst24-mail-2-recipient'] ),
			'additional_headers' => trim( $_POST['sst24-mail-2-additional-headers'] ),
			'attachments' => trim( $_POST['sst24-mail-2-attachments'] ),
			'use_html' =>
				isset( $_POST['sst24-mail-2-use-html'] ) && 1 == $_POST['sst24-mail-2-use-html']
		);

		$messages = isset( $contact_form->messages ) ? $contact_form->messages : array();

		foreach ( sst24_messages() as $key => $arr ) {
			$field_name = 'sst24-message-' . strtr( $key, '_', '-' );
			if ( isset( $_POST[$field_name] ) )
				$messages[$key] = trim( $_POST[$field_name] );
		}

		$additional_settings = trim( $_POST['sst24-additional-settings'] );

		$props = apply_filters( 'sst24_contact_form_admin_posted_properties',
			compact( 'form', 'mail', 'mail_2', 'messages', 'additional_settings' ) );

		foreach ( (array) $props as $key => $prop )
			$contact_form->{$key} = $prop;

		$query = array();
		$query['message'] = ( $contact_form->initial ) ? 'created' : 'saved';

		$contact_form->save();

		$query['contactform'] = $contact_form->id;
		$redirect_to = sst24_admin_url( $query );
		wp_redirect( $redirect_to );
		exit();
	}

	if ( isset( $_POST['sst24-copy'] ) ) {
		$id = $_POST['post_ID'];
		check_admin_referer( 'sst24-copy_' . $id );

		$query = array();

		if ( $contact_form = sst24_contact_form( $id ) ) {
			$new_contact_form = $contact_form->copy();
			$new_contact_form->save();

			$query['contactform'] = $new_contact_form->id;
			$query['message'] = 'created';
		} else {
			$query['contactform'] = $contact_form->id;
		}

		$redirect_to = sst24_admin_url( $query );
		wp_redirect( $redirect_to );
		exit();
	}

	if ( isset( $_POST['sst24-delete'] ) ) {
		$id = $_POST['post_ID'];
		check_admin_referer( 'sst24-delete_' . $id );

		if ( $contact_form = sst24_contact_form( $id ) )
			$contact_form->delete();

		$redirect_to = sst24_admin_url( array( 'message' => 'deleted' ) );
		wp_redirect( $redirect_to );
		exit();
	}
}

add_action( 'admin_menu', 'sst24_admin_menu', 9 );

function sst24_admin_menu() {
	add_menu_page( __( 'Contact Form 7', 'sst24' ), __( 'Contact', 'sst24' ),
		SST24_ADMIN_READ_CAPABILITY, 'sst24', 'sst24_admin_management_page' );

	add_submenu_page( 'sst24', __( 'Edit Contact Forms', 'sst24' ), __( 'Edit', 'sst24' ),
		SST24_ADMIN_READ_CAPABILITY, 'sst24', 'sst24_admin_management_page' );
}

add_action( 'admin_print_styles', 'sst24_admin_enqueue_styles' );

function sst24_admin_enqueue_styles() {
	global $plugin_page;

	if ( ! isset( $plugin_page ) || 'sst24' != $plugin_page )
		return;

	wp_enqueue_style( 'thickbox' );

	wp_enqueue_style( 'contact-form-7-admin', sst24_plugin_url( 'admin/styles.css' ),
		array(), SST24_VERSION, 'all' );

	if ( is_rtl() ) {
		wp_enqueue_style( 'contact-form-7-admin-rtl',
			sst24_plugin_url( 'admin/styles-rtl.css' ), array(), SST24_VERSION, 'all' );
	}
}

add_action( 'admin_enqueue_scripts', 'sst24_admin_enqueue_scripts' );

function sst24_admin_enqueue_scripts() {
	global $plugin_page;

	if ( ! isset( $plugin_page ) || 'sst24' != $plugin_page )
		return;

	wp_enqueue_script( 'thickbox' );
	wp_enqueue_script( 'postbox' );

	wp_enqueue_script( 'sst24-admin-taggenerator', sst24_plugin_url( 'admin/taggenerator.js' ),
		array( 'jquery' ), SST24_VERSION, true );

	wp_enqueue_script( 'sst24-admin', sst24_plugin_url( 'admin/scripts.js' ),
		array( 'jquery', 'sst24-admin-taggenerator' ), SST24_VERSION, true );
	wp_localize_script( 'sst24-admin', '_sst24L10n', array(
		'generateTag' => __( 'Generate Tag', 'sst24' ) ) );
}

add_action( 'admin_footer', 'sst24_admin_footer' );

function sst24_admin_footer() {
	global $plugin_page;

	if ( ! isset( $plugin_page ) || 'sst24' != $plugin_page )
		return;

?>
<script type="text/javascript">
/* <![CDATA[ */
var _sst24 = {
	pluginUrl: '<?php echo sst24_plugin_url(); ?>',
	tagGenerators: {
<?php sst24_print_tag_generators(); ?>
	}
};
/* ]]> */
</script>
<?php
}

function sst24_admin_management_page() {
	$contact_forms = get_posts( array(
		'numberposts' => -1,
		'orderby' => 'ID',
		'order' => 'ASC',
		'post_type' => 'sst24_contact_form' ) );

	$cf = null;
	$unsaved = false;

	if ( ! isset( $_GET['contactform'] ) )
		$_GET['contactform'] = '';

	if ( 'new' == $_GET['contactform'] && sst24_admin_has_edit_cap() ) {
		$unsaved = true;
		$current = -1;
		$cf = sst24_get_contact_form_default_pack(
			array( 'locale' => ( isset( $_GET['locale'] ) ? $_GET['locale'] : '' ) ) );
	} elseif ( $cf = sst24_contact_form( $_GET['contactform'] ) ) {
		$current = (int) $_GET['contactform'];
	} else {
		$first = reset( $contact_forms ); // Returns first item

		if ( $first ) {
			$current = $first->ID;
			$cf = sst24_contact_form( $current );
		}
	}

	require_once SST24_PLUGIN_DIR . '/admin/includes/meta-boxes.php';
	require_once SST24_PLUGIN_DIR . '/admin/edit.php';
}

/* Misc */

add_filter( 'plugin_action_links', 'sst24_plugin_action_links', 10, 2 );

function sst24_plugin_action_links( $links, $file ) {
	if ( $file != SST24_PLUGIN_BASENAME )
		return $links;

	$url = sst24_admin_url( array( 'page' => 'sst24' ) );

	$settings_link = '<a href="' . esc_attr( $url ) . '">'
		. esc_html( __( 'Settings', 'sst24' ) ) . '</a>';

	array_unshift( $links, $settings_link );

	return $links;
}

add_action( 'sst24_admin_before_subsubsub', 'sst24_cf7com_links', 9 );

function sst24_cf7com_links( &$contact_form ) {
	$links = '<div class="cf7com-links">'
		. '<a href="' . esc_url_raw( __( 'http://contactform7.com/', 'sst24' ) ) . '" target="_blank">'
		. esc_html( __( 'Contactform7.com', 'sst24' ) ) . '</a>&ensp;'
		. '<a href="' . esc_url_raw( __( 'http://contactform7.com/docs/', 'sst24' ) ) . '" target="_blank">'
		. esc_html( __( 'Docs', 'sst24' ) ) . '</a> - '
		. '<a href="' . esc_url_raw( __( 'http://contactform7.com/faq/', 'sst24' ) ) . '" target="_blank">'
		. esc_html( __( 'FAQ', 'sst24' ) ) . '</a> - '
		. '<a href="' . esc_url_raw( __( 'http://contactform7.com/support/', 'sst24' ) ) . '" target="_blank">'
		. esc_html( __( 'Support', 'sst24' ) ) . '</a>'
		. '</div>';

	echo apply_filters( 'sst24_cf7com_links', $links );
}

add_action( 'sst24_admin_before_subsubsub', 'sst24_updated_message' );

function sst24_updated_message( &$contact_form ) {
	if ( ! isset( $_GET['message'] ) )
		return;

	switch ( $_GET['message'] ) {
		case 'created':
			$updated_message = __( "Contact form created.", 'sst24' );
			break;
		case 'saved':
			$updated_message = __( "Contact form saved.", 'sst24' );
			break;
		case 'deleted':
			$updated_message = __( "Contact form deleted.", 'sst24' );
			break;
	}

	if ( ! $updated_message )
		return;

?>
<div id="message" class="updated"><p><?php echo esc_html( $updated_message ); ?></p></div>
<?php
}

add_action( 'sst24_admin_before_subsubsub', 'sst24_donation_link' );

function sst24_donation_link( &$contact_form ) {
	if ( ! SST24_SHOW_DONATION_LINK )
		return;

	if ( 'new' == $_GET['contactform'] || ! empty($_GET['message']) )
		return;

	$show_link = true;

	$num = mt_rand( 0, 99 );

	if ( $num >= 20 )
		$show_link = false;

	$show_link = apply_filters( 'sst24_show_donation_link', $show_link );

	if ( ! $show_link )
		return;

	$texts = array(
		__( "Contact Form 7 needs your support. Please donate today.", 'sst24' ),
		__( "Your contribution is needed for making this plugin better.", 'sst24' ) );

	$text = $texts[array_rand( $texts )];

?>
<div class="donation">
<p><a href="<?php echo esc_url_raw( __( 'http://contactform7.com/donate/', 'sst24' ) ); ?>"><?php echo esc_html( $text ); ?></a> <a href="<?php echo esc_url_raw( __( 'http://contactform7.com/donate/', 'sst24' ) ); ?>" class="button"><?php echo esc_html( __( "Donate", 'sst24' ) ); ?></a></p>
</div>
<?php
}

?>