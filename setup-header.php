<?php
/**
 * One-shot: configure the Astra header for Care Appliance Aircon Trading.
 *
 * Sets up: nav menu with section anchors (Services / Why Us / About /
 * Contact), header builder layout (logo | menu | FB icon + Call button),
 * and the mobile header (logo | Call button | hamburger).
 *
 * Run inside the container:
 *   docker compose exec wordpress php setup-header.php
 * Idempotent - safe to re-run. Keep out of wp-content when not running.
 */

define( 'WP_USE_THEMES', false );
require '/var/www/html/wp-load.php';

wp_set_current_user( 1 );

$FB  = 'https://www.facebook.com/profile.php?id=100064011216452';
$TEL = 'tel:+639640862665';

/* ---------- nav menu (anchor links into the landing page) ---------- */

$menu_name = 'Main Menu';
$existing  = wp_get_nav_menu_object( $menu_name );
if ( $existing ) {
	$menu_id = $existing->term_id;
	foreach ( wp_get_nav_menu_items( $menu_id ) as $item ) {
		wp_delete_post( $item->ID, true );
	}
	echo "Menu exists: #$menu_id (items reset)\n";
} else {
	$menu_id = wp_create_nav_menu( $menu_name );
	echo "Menu created: #$menu_id\n";
}

$items = [
	[ 'Services', '/#services' ],
	[ 'Why Us',   '/#why-us' ],
	[ 'About',    '/#about' ],
	[ 'Contact',  '/#contact' ],
];
foreach ( $items as [ $label, $url ] ) {
	wp_update_nav_menu_item( $menu_id, 0, [
		'menu-item-title'  => $label,
		'menu-item-url'    => home_url( $url ),
		'menu-item-type'   => 'custom',
		'menu-item-status' => 'publish',
	] );
}
$locations = get_theme_mod( 'nav_menu_locations', [] );
$locations['primary'] = $menu_id;
set_theme_mod( 'nav_menu_locations', $locations );
echo "Menu assigned to 'primary' location\n";

/* ---------- Astra header builder layout ---------- */

$fb_svg = '<svg viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path fill="currentColor" d="M504 256C504 119 393 8 256 8S8 119 8 256c0 123.78 90.69 226.38 209.25 245V327.69h-63V256h63v-54.64c0-62.15 37-96.48 93.67-96.48 27.14 0 55.52 4.84 55.52 4.84v61h-31.28c-30.8 0-40.41 19.12-40.41 38.73V256h68.78l-11 71.69h-57.78V501C413.31 482.38 504 379.78 504 256z"/></svg>';

// Astra 4.8.9+ stores its settings in the WP option 'astra-settings'
// (NOT the theme mod). Read-modify-write must go through
// astra_get_raw_options() so filtered/translated strings are not baked in.
$settings = astra_get_raw_options( [] );
if ( ! is_array( $settings ) ) {
	$settings = [];
}

// Desktop: logo left, menu center, FB icon + Call button right.
$settings['header-desktop-items'] = [
	'popup' => [ 'popup_content' => [ 'mobile-menu' ] ],
	'above' => [],
	'primary' => [
		'primary_left'         => [ 'logo' ],
		'primary_left_center'  => [],
		'primary_center'       => [ 'menu-1' ],
		'primary_right_center' => [],
		'primary_right'        => [ 'html-1', 'button-1' ],
	],
	'below' => [],
];

// Mobile: logo left, Call button + hamburger right; menu slides in off-canvas.
$settings['header-mobile-items'] = [
	'popup' => [ 'popup_content' => [] ],
	'above' => [],
	'primary' => [
		'primary_left'   => [ 'logo' ],
		'primary_center' => [],
		'primary_right'  => [ 'button-1', 'mobile-trigger' ],
	],
	'below' => [],
];
$settings['header-mobile-popup-items'] = [
	'popup' => [ 'popup_content' => [ 'mobile-menu' ] ],
];

// Call button (brand orange, white text, pill shape).
$settings['header-button1-text']             = 'Call Now';
$settings['header-button1-link']             = $TEL;
$settings['header-button1-new-tab']          = false;
$settings['header-button1-text-color']       = [ 'desktop' => '#ffffff', 'tablet' => '#ffffff', 'mobile' => '#ffffff' ];
$settings['header-button1-back-color']       = [ 'desktop' => '#ff8a00', 'tablet' => '#ff8a00', 'mobile' => '#ff8a00' ];
$settings['header-button1-text-hover-color'] = '#ffffff';
$settings['header-button1-back-hover-color'] = '#e67a00';
$settings['header-button1-border-radius']    = '40';

// HTML 1 block: Facebook icon linking to their page.
$settings['header-html-1'] = '<a class="ca-header-fb" href="' . $FB . '" target="_blank" rel="noopener" aria-label="Care Appliance Aircon Trading on Facebook">' . $fb_svg . '</a>';

update_option( 'astra-settings', $settings );
Astra_Theme_Options::refresh();
echo "Astra header builder configured (desktop + mobile)\n";
