<?php
/**
 * Care Appliances - Astra child theme.
 *
 * Pages are built visually with Elementor. This file handles
 * asset loading, Astra/Elementor integration and any custom PHP
 * (shortcodes, post types, hooks) added later.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CARE_APPLIANCES_CHILD_VERSION', '1.3.0' );

/**
 * Load the child stylesheet after Astra's own styles so
 * child overrides always win. Bump the Version: header in
 * style.css (not just this constant) when CSS changes.
 */
function care_appliances_child_enqueue_styles() {
	wp_enqueue_style(
		'care-appliances-child',
		get_stylesheet_uri(),
		array( 'astra-theme-css' ),
		CARE_APPLIANCES_CHILD_VERSION
	);
}
add_action( 'wp_enqueue_scripts', 'care_appliances_child_enqueue_styles' );

/**
 * Register Elementor locations so Elementor Pro (if ever added)
 * can replace the Astra header/footer/-single templates.
 * Harmless with Elementor free.
 */
function care_appliances_child_register_elementor_locations( $elementor_theme_manager ) {
	$elementor_theme_manager->register_all_core_location();
}
add_action( 'elementor/theme/register_locations', 'care_appliances_child_register_elementor_locations' );

/**
 * Elementor editor: use the child theme's typography defaults
 * as the initial values for new Elementor pages.
 */
add_filter( 'elementor/kit/default_container_fluid', '__return_true' );
