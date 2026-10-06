<?php
/**
 * One-shot builder: fills the Home page (id 5) with an Elementor
 * landing page for Care Appliance Aircon Trading, using data from
 * their public Facebook page + cover banner.
 *
 * Run inside the container:
 *   docker compose exec wordpress php wp-content/themes/astra-child/build-page.php
 * Delete this file after running.
 */

define( 'WP_USE_THEMES', false );
require '/var/www/html/wp-load.php';

/* Elementor kit sync on update_option() needs a capable user in CLI context */
wp_set_current_user( 1 );

if ( ! defined( 'ELEMENTOR_VERSION' ) ) {
	wp_die( 'Elementor not active' );
}
require_once ABSPATH . 'wp-admin/includes/image.php';

/* ---------- helpers ---------- */

function ca_id() {
	return substr( bin2hex( random_bytes( 8 ) ), 0, 7 );
}
function ca_widget( $type, array $settings = [] ) {
	return [ 'id' => ca_id(), 'elType' => 'widget', 'widgetType' => $type,
		'settings' => $settings, 'elements' => [], 'isInner' => false ];
}
function ca_container( array $settings = [], array $elements = [], $inner = false ) {
	return [ 'id' => ca_id(), 'elType' => 'container', 'settings' => $settings,
		'elements' => $elements, 'isInner' => $inner ];
}
function ca_gap( $px ) {
	return [ 'unit' => 'px', 'size' => $px, 'column' => (string) $px, 'row' => (string) $px, 'isLinked' => true ];
}
function ca_pad( $t, $r, $b, $l ) {
	return [ 'unit' => 'px', 'top' => (string) $t, 'right' => (string) $r, 'bottom' => (string) $b, 'left' => (string) $l, 'isLinked' => false ];
}
function ca_radius( $px ) {
	return [ 'unit' => 'px', 'top' => (string) $px, 'right' => (string) $px, 'bottom' => (string) $px, 'left' => (string) $px, 'isLinked' => true ];
}

const CA_BLUE  = '#189ae0';
const CA_NAVY  = '#0d3a5c';
const CA_ACCENT = '#ff8a00';
const CA_MUTED = '#5a7184';
const CA_FB    = 'https://www.facebook.com/profile.php?id=100064011216452';

/* ---------- site identity ---------- */

update_option( 'blogname', 'Care Appliance Aircon Trading' );
update_option( 'blogdescription', 'Direct Aircon Dealer, Installer & Service Center in Oroquieta City' );

/* footer credit */
$astra = (array) get_theme_mod( 'astra-settings', [] );
$astra['footer-credit'] = 'Copyright © [current_year] Care Appliance Aircon Trading';
set_theme_mod( 'astra-settings', $astra );

/* ---------- import the brand banner ---------- */

$src  = '/var/www/html/wp-content/themes/astra-child/assets/img/brand-banner.jpg';
$existing = get_posts( [
	'post_type' => 'attachment', 'post_status' => 'inherit', 'numberposts' => 1,
	'fields' => 'ids', 'title' => 'Care Appliance Aircon Trading - brand banner',
] );
if ( $existing ) {
	$att_id     = $existing[0];
	$banner_url = wp_get_attachment_url( $att_id );
	echo "Banner already imported: attachment #$att_id\n";
} else {
	$bits = wp_upload_bits( 'care-appliance-banner.jpg', null, file_get_contents( $src ) );
	if ( ! empty( $bits['error'] ) ) {
		wp_die( 'Upload failed: ' . $bits['error'] );
	}
	$att_id = wp_insert_attachment( [
		'post_mime_type' => 'image/jpeg',
		'post_title'     => 'Care Appliance Aircon Trading - brand banner',
		'post_status'    => 'inherit',
	], $bits['file'] );
	wp_update_attachment_metadata( $att_id, wp_generate_attachment_metadata( $att_id, $bits['file'] ) );
	$banner_url = wp_get_attachment_url( $att_id );
	echo "Banner imported: attachment #$att_id $banner_url\n";
}

/* ---------- import the custom phone SVG (svgrepo phone-incoming-02) ---------- */

$svg_src = '/var/www/html/wp-content/themes/astra-child/assets/img/phone-incoming-02.svg';
$existing_svg = get_posts( [
	'post_type' => 'attachment', 'post_status' => 'inherit', 'numberposts' => 1,
	'fields' => 'ids', 'title' => 'Phone incoming icon (SVG)',
] );
if ( $existing_svg ) {
	$svg_id = $existing_svg[0];
	echo "Phone SVG already imported: attachment #$svg_id\n";
} else {
	add_filter( 'upload_mimes', function ( $mimes ) {
		$mimes['svg'] = 'image/svg+xml';
		return $mimes;
	} );
	$svg_bits = wp_upload_bits( 'phone-incoming-02.svg', null, file_get_contents( $svg_src ) );
	if ( ! empty( $svg_bits['error'] ) ) {
		wp_die( 'SVG upload failed: ' . $svg_bits['error'] );
	}
	$svg_id = wp_insert_attachment( [
		'post_mime_type' => 'image/svg+xml',
		'post_title'     => 'Phone incoming icon (SVG)',
		'post_status'    => 'inherit',
	], $svg_bits['file'] );
	echo "Phone SVG imported: attachment #$svg_id\n";
}

$phone_icon = [ 'value' => [ 'id' => $svg_id ], 'library' => 'svg' ];

/* ---------- page building blocks ---------- */

$h1 = ca_widget( 'heading', [
	'title' => 'Branded Aircons at Direct Bodega Prices',
	'header_size' => 'h1', 'align' => 'center', 'title_color' => '#ffffff',
	'typography_typography' => 'custom',
	'typography_font_size' => [ 'unit' => 'px', 'size' => 54 ],
	'typography_font_size_mobile' => [ 'unit' => 'px', 'size' => 32 ],
	'typography_font_weight' => '800',
	'typography_line_height' => [ 'unit' => 'em', 'size' => 1.15 ],
	'_element_width' => 'custom',
	'_element_custom_width' => [ 'unit' => 'px', 'size' => 820 ],
	'_element_custom_width_mobile' => [ 'unit' => '%', 'size' => 100 ],
] );

/* ---------- 1. hero ---------- */

$hero = ca_container( [
	'content_width' => 'boxed', 'width' => [ 'unit' => 'px', 'size' => 1140 ],
	'min_height' => [ 'unit' => 'px', 'size' => 680 ],
	'min_height_mobile' => [ 'unit' => 'px', 'size' => 580 ],
	'flex_direction' => 'column', 'flex_align_items' => 'center', 'flex_justify_content' => 'center',
	'flex_gap' => ca_gap( 18 ),
	'padding' => ca_pad( 110, 24, 110, 24 ), 'padding_mobile' => ca_pad( 72, 20, 72, 20 ),
	'background_background' => 'gradient',
	'background_color' => CA_NAVY, 'background_color_stop' => [ 'unit' => '%', 'size' => 0 ],
	'background_color_b' => CA_BLUE, 'background_color_b_stop' => [ 'unit' => '%', 'size' => 100 ],
	'background_gradient_type' => 'linear',
	'background_gradient_angle' => [ 'unit' => 'deg', 'size' => 165 ],
], [
	ca_widget( 'heading', [
		'title' => 'DIRECT DEALER  •  INSTALLER  •  SERVICE CENTER',
		'header_size' => 'p', 'align' => 'center', 'title_color' => '#aee0f8',
		'typography_typography' => 'custom',
		'typography_font_size' => [ 'unit' => 'px', 'size' => 14 ],
		'typography_font_weight' => '700',
		'typography_letter_spacing' => [ 'unit' => 'px', 'size' => 3 ],
	] ),
	$h1,
	ca_widget( 'text-editor', [
		'editor' => '<p>Care Appliance Aircon Trading supplies brand-new, branded window and split-type aircons — Daikin, Carrier, Panasonic, TCL and more — with <strong>free installation labor &amp; materials</strong> here in Oroquieta City.</p>',
		'align' => 'center', 'text_color' => 'rgba(255,255,255,0.88)',
		'typography_typography' => 'custom',
		'typography_font_size' => [ 'unit' => 'px', 'size' => 18 ],
		'typography_line_height' => [ 'unit' => 'em', 'size' => 1.7 ],
		'_element_width' => 'custom',
		'_element_custom_width' => [ 'unit' => 'px', 'size' => 680 ],
		'_element_custom_width_mobile' => [ 'unit' => '%', 'size' => 100 ],
	] ),
	ca_container( [
		'flex_direction' => 'row', 'flex_direction_mobile' => 'column',
		'flex_align_items' => 'center', 'flex_justify_content' => 'center',
		'flex_gap' => ca_gap( 16 ),
	], [
		ca_widget( 'button', [
			'text' => 'Call 0964-086-2665',
			'selected_icon' => $phone_icon,
			'icon_align' => 'left',
			'icon_indent' => [ 'unit' => 'px', 'size' => 10 ],
			'link' => [ 'url' => 'tel:+639640862665', 'is_external' => '', 'nofollow' => '' ],
			'align' => 'center', 'button_text_color' => '#ffffff',
			'background_color' => CA_ACCENT,
			'hover_color' => '#ffffff', 'button_background_hover_color' => '#e67a00',
			'border_radius' => ca_radius( 40 ), 'text_padding' => ca_pad( 18, 36, 18, 36 ),
			'typography_typography' => 'custom',
			'typography_font_size' => [ 'unit' => 'px', 'size' => 16 ],
			'typography_font_weight' => '700',
		] ),
		ca_widget( 'button', [
			'text' => 'Message Us on Facebook',
			'selected_icon' => [ 'value' => 'fab fa-facebook-messenger', 'library' => 'fa-brands' ],
			'icon_align' => 'left',
			'icon_indent' => [ 'unit' => 'px', 'size' => 10 ],
			'link' => [ 'url' => CA_FB, 'is_external' => 'true', 'nofollow' => '' ],
			'align' => 'center', 'button_text_color' => '#ffffff',
			'background_color' => 'rgba(255,255,255,0)',
			'hover_color' => CA_NAVY, 'button_background_hover_color' => '#ffffff',
			'border_border' => 'solid', 'border_color' => 'rgba(255,255,255,0.7)',
			'border_width' => [ 'unit' => 'px', 'top' => '2', 'right' => '2', 'bottom' => '2', 'left' => '2', 'isLinked' => true ],
			'border_radius' => ca_radius( 40 ), 'text_padding' => ca_pad( 16, 34, 16, 34 ),
			'typography_typography' => 'custom',
			'typography_font_size' => [ 'unit' => 'px', 'size' => 16 ],
			'typography_font_weight' => '700',
		] ),
	], true ),
	ca_widget( 'text-editor', [
		'editor' => '<p>Free installation labor &amp; materials &nbsp;•&nbsp; Delivery available &nbsp;•&nbsp; On-site service center</p>',
		'align' => 'center', 'text_color' => 'rgba(255,255,255,0.75)',
		'typography_typography' => 'custom',
		'typography_font_size' => [ 'unit' => 'px', 'size' => 14 ],
	] ),
] );

/* ---------- 2. brands strip ---------- */

$brands = ca_container( [
	'content_width' => 'boxed', 'width' => [ 'unit' => 'px', 'size' => 1140 ],
	'flex_direction' => 'column', 'flex_align_items' => 'center', 'flex_gap' => ca_gap( 14 ),
	'padding' => ca_pad( 60, 24, 60, 24 ),
	'background_background' => 'classic', 'background_color' => '#ffffff',
], [
	ca_widget( 'heading', [
		'title' => 'We carry the brands you trust',
		'header_size' => 'h2', 'align' => 'center', 'title_color' => CA_NAVY,
		'typography_typography' => 'custom',
		'typography_font_size' => [ 'unit' => 'px', 'size' => 26 ],
		'typography_font_weight' => '700',
	] ),
	ca_widget( 'text-editor', [
		'editor' => '<p><strong>Daikin</strong> · <strong>Midea</strong> · <strong>TCL</strong> · <strong>Samsung</strong> · <strong>Carrier</strong> · <strong>Condura</strong> · <strong>General Royal</strong> · <strong>ChiQ</strong> · <strong>Fujidenzo</strong> · <strong>Koppel</strong> · <strong>Panasonic</strong> · <strong>G.E.</strong></p>',
		'align' => 'center', 'text_color' => CA_MUTED,
		'typography_typography' => 'custom',
		'typography_font_size' => [ 'unit' => 'px', 'size' => 16 ],
		'typography_line_height' => [ 'unit' => 'em', 'size' => 1.9 ],
	] ),
] );

/* ---------- 3. services ---------- */

$service_card = function ( $icon, $title, $desc ) {
	return ca_container( [
		'content_width' => 'full',
		'flex_direction' => 'column', 'flex_align_items' => 'center', 'flex_gap' => ca_gap( 10 ),
		'width' => [ 'unit' => '%', 'size' => 31.8 ],
		'width_mobile' => [ 'unit' => '%', 'size' => 100 ],
		'background_background' => 'classic', 'background_color' => '#ffffff',
		'padding' => ca_pad( 38, 28, 38, 28 ),
		'border_radius' => ca_radius( 14 ),
		'box_shadow_box_shadow' => [ 'horizontal' => 0, 'vertical' => 10, 'blur' => 34, 'spread' => 0, 'color' => 'rgba(13,58,92,0.08)' ],
	], [
		ca_widget( 'icon-box', [
			'selected_icon' => [ 'value' => $icon, 'library' => 'fa-solid' ],
			'title_text' => $title, 'description_text' => $desc,
			'position' => 'top', 'text_align' => 'center',
			'primary_color' => CA_BLUE, 'icon_size' => [ 'unit' => 'px', 'size' => 42 ],
			'title_color' => CA_NAVY,
			'title_typography_typography' => 'custom',
			'title_typography_font_size' => [ 'unit' => 'px', 'size' => 20 ],
			'title_typography_font_weight' => '700',
			'description_color' => CA_MUTED,
			'description_typography_typography' => 'custom',
			'description_typography_font_size' => [ 'unit' => 'px', 'size' => 15 ],
			'description_typography_line_height' => [ 'unit' => 'em', 'size' => 1.7 ],
		] ),
	], true );
};

$services = ca_container( [
	'content_width' => 'boxed', 'width' => [ 'unit' => 'px', 'size' => 1140 ],
	'flex_direction' => 'column', 'flex_align_items' => 'center', 'flex_gap' => ca_gap( 44 ),
	'padding' => ca_pad( 88, 24, 88, 24 ),
	'background_background' => 'classic', 'background_color' => '#f4f9fd',
], [
	ca_widget( 'heading', [
		'title' => 'What we do',
		'header_size' => 'h2', 'align' => 'center', 'title_color' => CA_NAVY,
		'typography_typography' => 'custom',
		'typography_font_size' => [ 'unit' => 'px', 'size' => 34 ],
		'typography_font_weight' => '800',
	] ),
	ca_widget( 'text-editor', [
		'editor' => '<p>Sales, installation, parts and after-sales service — everything for your cooling needs, from one shop.</p>',
		'align' => 'center', 'text_color' => CA_MUTED,
		'typography_typography' => 'custom',
		'typography_font_size' => [ 'unit' => 'px', 'size' => 17 ],
		'_element_width' => 'custom',
		'_element_custom_width' => [ 'unit' => 'px', 'size' => 620 ],
		'_element_custom_width_mobile' => [ 'unit' => '%', 'size' => 100 ],
	] ),
	ca_container( [
		'flex_direction' => 'row', 'flex_direction_mobile' => 'column',
		'flex_wrap' => 'wrap', 'flex_align_items' => 'stretch',
		'flex_gap' => ca_gap( 20 ),
	], [
		$service_card( 'fas fa-snowflake', 'Aircon Sales',
			'Brand-new, sealed window and split-type units from Daikin, Carrier, Panasonic and other trusted brands — at direct bodega prices.' ),
		$service_card( 'fas fa-screwdriver-wrench', 'Installation & Service',
			'Professional installation by trained technicians — free labor & materials included — plus maintenance and after-sales support from our own service center.' ),
		$service_card( 'fas fa-boxes-stacked', 'Spare Parts & Materials',
			'Genuine aircon spare parts and installation materials available over the counter for technicians and DIY installers.' ),
	], true ),
] );

/* ---------- 4. why us + call card ---------- */

$why_list = ca_container( [
	'content_width' => 'full',
	'flex_direction' => 'column', 'flex_align_items' => 'flex-start', 'flex_gap' => ca_gap( 0 ),
	'width' => [ 'unit' => '%', 'size' => 55 ], 'width_mobile' => [ 'unit' => '%', 'size' => 100 ],
], [
	ca_widget( 'heading', [
		'title' => 'Why Oroquieta buys from Care Appliance',
		'header_size' => 'h2', 'align' => 'left', 'title_color' => '#ffffff',
		'typography_typography' => 'custom',
		'typography_font_size' => [ 'unit' => 'px', 'size' => 34 ],
		'typography_font_weight' => '800',
		'typography_line_height' => [ 'unit' => 'em', 'size' => 1.25 ],
	] ),
	ca_widget( 'icon-list', [
		'view' => 'traditional',
		'icon_list' => [
			[ '_id' => ca_id(), 'text' => 'Direct supplier prices — no middleman markups', 'selected_icon' => [ 'value' => 'fas fa-check', 'library' => 'fa-solid' ] ],
			[ '_id' => ca_id(), 'text' => 'Free installation labor & materials with every unit', 'selected_icon' => [ 'value' => 'fas fa-check', 'library' => 'fa-solid' ] ],
			[ '_id' => ca_id(), 'text' => 'Brand-new, sealed units from trusted brands', 'selected_icon' => [ 'value' => 'fas fa-check', 'library' => 'fa-solid' ] ],
			[ '_id' => ca_id(), 'text' => 'Delivery available in Oroquieta City & nearby towns', 'selected_icon' => [ 'value' => 'fas fa-check', 'library' => 'fa-solid' ] ],
			[ '_id' => ca_id(), 'text' => 'On-site service center for maintenance & support', 'selected_icon' => [ 'value' => 'fas fa-check', 'library' => 'fa-solid' ] ],
			[ '_id' => ca_id(), 'text' => 'Trusted by 11,000+ followers on Facebook', 'selected_icon' => [ 'value' => 'fas fa-check', 'library' => 'fa-solid' ] ],
		],
		'icon_color' => '#58c1f0', 'text_color' => 'rgba(255,255,255,0.9)',
		'space_between' => [ 'unit' => 'px', 'size' => 14 ],
		'icon_size' => [ 'unit' => 'px', 'size' => 16 ],
		'icon_typography_typography' => 'custom',
		'icon_typography_font_size' => [ 'unit' => 'px', 'size' => 16 ],
		'icon_typography_line_height' => [ 'unit' => 'em', 'size' => 1.9 ],
	] ),
], true );

$call_card = ca_container( [
	'content_width' => 'full',
	'flex_direction' => 'column', 'flex_align_items' => 'center',
	'flex_justify_content' => 'center', 'flex_gap' => ca_gap( 14 ),
	'width' => [ 'unit' => '%', 'size' => 45 ], 'width_mobile' => [ 'unit' => '%', 'size' => 100 ],
	'background_background' => 'classic', 'background_color' => CA_BLUE,
	'padding' => ca_pad( 48, 32, 48, 32 ),
	'border_radius' => ca_radius( 18 ),
	'box_shadow_box_shadow' => [ 'horizontal' => 0, 'vertical' => 16, 'blur' => 44, 'spread' => 0, 'color' => 'rgba(0,0,0,0.25)' ],
], [
	ca_widget( 'heading', [
		'title' => 'Ready to cool down?',
		'header_size' => 'h3', 'align' => 'center', 'title_color' => '#ffffff',
		'typography_typography' => 'custom',
		'typography_font_size' => [ 'unit' => 'px', 'size' => 26 ],
		'typography_font_weight' => '800',
	] ),
	ca_widget( 'text-editor', [
		'editor' => '<p>Send us a message or give us a call — we’ll quote you the same day.</p>',
		'align' => 'center', 'text_color' => 'rgba(255,255,255,0.9)',
		'typography_typography' => 'custom',
		'typography_font_size' => [ 'unit' => 'px', 'size' => 16 ],
	] ),
	ca_widget( 'button', [
		'text' => 'Call 0964-086-2665',
		'selected_icon' => $phone_icon,
		'icon_align' => 'left',
		'icon_indent' => [ 'unit' => 'px', 'size' => 10 ],
		'link' => [ 'url' => 'tel:+639640862665', 'is_external' => '', 'nofollow' => '' ],
		'align' => 'center', 'button_text_color' => CA_NAVY,
		'background_color' => '#ffffff',
		'hover_color' => '#ffffff', 'button_background_hover_color' => CA_ACCENT,
		'border_radius' => ca_radius( 40 ), 'text_padding' => ca_pad( 16, 34, 16, 34 ),
		'typography_typography' => 'custom',
		'typography_font_size' => [ 'unit' => 'px', 'size' => 16 ],
		'typography_font_weight' => '700',
	] ),
], true );

$whyus = ca_container( [
	'content_width' => 'boxed', 'width' => [ 'unit' => 'px', 'size' => 1140 ],
	'flex_direction' => 'column', 'flex_align_items' => 'stretch', 'flex_gap' => ca_gap( 0 ),
	'padding' => ca_pad( 88, 24, 88, 24 ),
	'background_background' => 'classic', 'background_color' => CA_NAVY,
], [
	ca_container( [
		'flex_direction' => 'row', 'flex_direction_mobile' => 'column',
		'flex_align_items' => 'center', 'flex_gap' => ca_gap( 48 ),
	], [ $why_list, $call_card ], true ),
] );

/* ---------- 5. about + brand banner ---------- */

$about = ca_container( [
	'content_width' => 'boxed', 'width' => [ 'unit' => 'px', 'size' => 1140 ],
	'flex_direction' => 'column', 'flex_align_items' => 'stretch',
	'padding' => ca_pad( 88, 24, 88, 24 ),
	'background_background' => 'classic', 'background_color' => '#ffffff',
], [
	ca_container( [
		'flex_direction' => 'row', 'flex_direction_mobile' => 'column',
		'flex_align_items' => 'center', 'flex_gap' => ca_gap( 56 ),
	], [
		ca_container( [
			'content_width' => 'full',
			'flex_direction' => 'column', 'flex_align_items' => 'flex-start', 'flex_gap' => ca_gap( 18 ),
			'width' => [ 'unit' => '%', 'size' => 55 ], 'width_mobile' => [ 'unit' => '%', 'size' => 100 ],
		], [
			ca_widget( 'heading', [
				'title' => 'Your direct dealer, installer & service center',
				'header_size' => 'h2', 'align' => 'left', 'title_color' => CA_NAVY,
				'typography_typography' => 'custom',
				'typography_font_size' => [ 'unit' => 'px', 'size' => 34 ],
				'typography_font_weight' => '800',
				'typography_line_height' => [ 'unit' => 'em', 'size' => 1.25 ],
			] ),
			ca_widget( 'text-editor', [
				'editor' => '<p>Straight from the supplier — no showroom markups. We stock branded air conditioners, spare parts and installation materials at our Upper Loboc store, and our own technicians handle everything from sizing and installation to maintenance. Catch our latest promos on our Facebook page, where 11,000+ followers keep up with our newest arrivals.</p>',
				'align' => 'left', 'text_color' => CA_MUTED,
				'typography_typography' => 'custom',
				'typography_font_size' => [ 'unit' => 'px', 'size' => 16 ],
				'typography_line_height' => [ 'unit' => 'em', 'size' => 1.8 ],
			] ),
			ca_widget( 'button', [
				'text' => 'See our latest units on Facebook',
				'selected_icon' => [ 'value' => 'fab fa-facebook', 'library' => 'fa-brands' ],
				'icon_align' => 'left',
				'icon_indent' => [ 'unit' => 'px', 'size' => 10 ],
				'link' => [ 'url' => CA_FB, 'is_external' => 'true', 'nofollow' => '' ],
				'align' => 'left', 'button_text_color' => '#ffffff',
				'background_color' => CA_BLUE,
				'hover_color' => '#ffffff', 'button_background_hover_color' => CA_NAVY,
				'border_radius' => ca_radius( 40 ), 'text_padding' => ca_pad( 16, 32, 16, 32 ),
				'typography_typography' => 'custom',
				'typography_font_size' => [ 'unit' => 'px', 'size' => 15 ],
				'typography_font_weight' => '700',
			] ),
		], true ),
		ca_container( [
			'content_width' => 'full',
			'flex_direction' => 'column', 'flex_align_items' => 'center',
			'width' => [ 'unit' => '%', 'size' => 45 ], 'width_mobile' => [ 'unit' => '%', 'size' => 100 ],
		], [
			ca_widget( 'image', [
				'image' => [ 'url' => $banner_url, 'id' => $att_id, 'size' => '', 'alt' => 'Care Appliance Aircon Trading - direct dealer, installer and service center', 'source' => 'library' ],
				'image_size' => 'full', 'align' => 'center',
				'_css_classes' => 'ca-img-rounded',
			] ),
		], true ),
	], true ),
] );

/* ---------- 6. facebook ---------- */

$fb_card = ca_container( [
	'content_width' => 'full',
	'flex_direction' => 'column', 'flex_align_items' => 'center',
	'flex_justify_content' => 'center', 'flex_gap' => ca_gap( 14 ),
	'width' => [ 'unit' => 'px', 'size' => 620 ], 'width_mobile' => [ 'unit' => '%', 'size' => 100 ],
	'background_background' => 'gradient',
	'background_color' => CA_BLUE, 'background_color_stop' => [ 'unit' => '%', 'size' => 0 ],
	'background_color_b' => CA_NAVY, 'background_color_b_stop' => [ 'unit' => '%', 'size' => 100 ],
	'background_gradient_type' => 'linear',
	'background_gradient_angle' => [ 'unit' => 'deg', 'size' => 160 ],
	'padding' => ca_pad( 44, 32, 44, 32 ), 'padding_mobile' => ca_pad( 36, 20, 36, 20 ),
	'border_radius' => ca_radius( 18 ),
	'box_shadow_box_shadow' => [ 'horizontal' => 0, 'vertical' => 12, 'blur' => 36, 'spread' => 0, 'color' => 'rgba(13,58,92,0.22)' ],
], [
	ca_widget( 'icon', [
		'selected_icon' => [ 'value' => 'fab fa-facebook', 'library' => 'fa-brands' ],
		'align' => 'center',
		'primary_color' => '#ffffff',
		'size' => [ 'unit' => 'px', 'size' => 56 ],
	] ),
	ca_widget( 'heading', [
		'title' => 'Care Appliance Aircon Trading',
		'header_size' => 'h3', 'align' => 'center', 'title_color' => '#ffffff',
		'typography_typography' => 'custom',
		'typography_font_size' => [ 'unit' => 'px', 'size' => 24 ],
		'typography_font_weight' => '800',
	] ),
	ca_widget( 'text-editor', [
		'editor' => '<p>11,000+ followers — daily price drops, promos and photos of our latest installations.</p>',
		'align' => 'center', 'text_color' => 'rgba(255,255,255,0.9)',
		'typography_typography' => 'custom',
		'typography_font_size' => [ 'unit' => 'px', 'size' => 15 ],
	] ),
	ca_widget( 'button', [
		'text' => 'Follow our Facebook Page',
		'selected_icon' => [ 'value' => 'fab fa-facebook', 'library' => 'fa-brands' ],
		'icon_align' => 'left',
		'icon_indent' => [ 'unit' => 'px', 'size' => 10 ],
		'link' => [ 'url' => CA_FB, 'is_external' => 'true', 'nofollow' => '' ],
		'align' => 'center', 'button_text_color' => CA_NAVY,
		'background_color' => '#ffffff',
		'hover_color' => '#ffffff', 'button_background_hover_color' => CA_ACCENT,
		'border_radius' => ca_radius( 40 ), 'text_padding' => ca_pad( 16, 34, 16, 34 ),
		'typography_typography' => 'custom',
		'typography_font_size' => [ 'unit' => 'px', 'size' => 15 ],
		'typography_font_weight' => '700',
	] ),
], true );

$facebook = ca_container( [
	'content_width' => 'boxed', 'width' => [ 'unit' => 'px', 'size' => 1140 ],
	'flex_direction' => 'column', 'flex_align_items' => 'center', 'flex_gap' => ca_gap( 24 ),
	'padding' => ca_pad( 88, 24, 88, 24 ),
	'background_background' => 'classic', 'background_color' => '#f4f9fd',
], [
	ca_widget( 'heading', [
		'title' => 'Follow us for promos & new arrivals',
		'header_size' => 'h2', 'align' => 'center', 'title_color' => CA_NAVY,
		'typography_typography' => 'custom',
		'typography_font_size' => [ 'unit' => 'px', 'size' => 34 ],
		'typography_font_weight' => '800',
	] ),
	ca_widget( 'text-editor', [
		'editor' => '<p>We post our latest prices, promos and completed installations on our Facebook page.</p>',
		'align' => 'center', 'text_color' => CA_MUTED,
		'typography_typography' => 'custom',
		'typography_font_size' => [ 'unit' => 'px', 'size' => 16 ],
	] ),
	$fb_card,
] );

/* ---------- 7. contact + map ---------- */

$contact = ca_container( [
	'content_width' => 'boxed', 'width' => [ 'unit' => 'px', 'size' => 1140 ],
	'flex_direction' => 'column', 'flex_align_items' => 'center', 'flex_gap' => ca_gap( 44 ),
	'padding' => ca_pad( 88, 24, 88, 24 ),
	'background_background' => 'classic', 'background_color' => CA_NAVY,
], [
	ca_widget( 'heading', [
		'title' => 'Visit our store',
		'header_size' => 'h2', 'align' => 'center', 'title_color' => '#ffffff',
		'typography_typography' => 'custom',
		'typography_font_size' => [ 'unit' => 'px', 'size' => 34 ],
		'typography_font_weight' => '800',
	] ),
	ca_container( [
		'flex_direction' => 'row', 'flex_direction_mobile' => 'column',
		'flex_align_items' => 'center', 'flex_gap' => ca_gap( 48 ),
	], [
		ca_container( [
			'content_width' => 'full',
			'flex_direction' => 'column', 'flex_align_items' => 'flex-start', 'flex_gap' => ca_gap( 0 ),
			'width' => [ 'unit' => '%', 'size' => 45 ], 'width_mobile' => [ 'unit' => '%', 'size' => 100 ],
		], [
			ca_widget( 'icon-list', [
				'view' => 'traditional',
				'icon_list' => [
					[ '_id' => ca_id(), 'text' => 'Gen. Deloso St., Purok 2, Upper Loboc, Oroquieta City', 'selected_icon' => [ 'value' => 'fas fa-location-dot', 'library' => 'fa-solid' ] ],
					[ '_id' => ca_id(), 'text' => '0964-086-2665', 'selected_icon' => $phone_icon, 'link' => [ 'url' => 'tel:+639640862665', 'is_external' => '', 'nofollow' => '' ] ],
					[ '_id' => ca_id(), 'text' => '0910-003-4325', 'selected_icon' => $phone_icon, 'link' => [ 'url' => 'tel:+639100034325', 'is_external' => '', 'nofollow' => '' ] ],
					[ '_id' => ca_id(), 'text' => 'Care Appliance Aircon Trading on Facebook', 'selected_icon' => [ 'value' => 'fab fa-facebook', 'library' => 'fa-brands' ], 'link' => [ 'url' => CA_FB, 'is_external' => 'true', 'nofollow' => '' ] ],
				],
				'icon_color' => '#58c1f0', 'text_color' => 'rgba(255,255,255,0.9)',
				'space_between' => [ 'unit' => 'px', 'size' => 18 ],
				'icon_size' => [ 'unit' => 'px', 'size' => 18 ],
				'icon_typography_typography' => 'custom',
				'icon_typography_font_size' => [ 'unit' => 'px', 'size' => 16 ],
				'icon_typography_line_height' => [ 'unit' => 'em', 'size' => 1.8 ],
			] ),
		], true ),
		ca_container( [
			'content_width' => 'full',
			'flex_direction' => 'column', 'flex_align_items' => 'center',
			'width' => [ 'unit' => '%', 'size' => 55 ], 'width_mobile' => [ 'unit' => '%', 'size' => 100 ],
		], [
			ca_widget( 'html', [
				'html' => '<div class="ca-embed"><iframe loading="lazy" src="https://www.google.com/maps?q=Upper%20Loboc%2C%20Oroquieta%20City%2C%20Misamis%20Occidental&amp;output=embed" width="100%" height="340" style="border:0" allowfullscreen="" referrerpolicy="no-referrer-when-downgrade"></iframe></div>',
			] ),
		], true ),
	], true ),
] );

/* ---------- 8. final CTA ---------- */

$cta = ca_container( [
	'content_width' => 'boxed', 'width' => [ 'unit' => 'px', 'size' => 1140 ],
	'flex_direction' => 'column', 'flex_align_items' => 'center', 'flex_gap' => ca_gap( 16 ),
	'padding' => ca_pad( 72, 24, 72, 24 ),
	'background_background' => 'gradient',
	'background_color' => CA_BLUE, 'background_color_stop' => [ 'unit' => '%', 'size' => 0 ],
	'background_color_b' => CA_NAVY, 'background_color_b_stop' => [ 'unit' => '%', 'size' => 100 ],
	'background_gradient_type' => 'linear',
	'background_gradient_angle' => [ 'unit' => 'deg', 'size' => 180 ],
], [
	ca_widget( 'heading', [
		'title' => 'Need a new aircon today?',
		'header_size' => 'h2', 'align' => 'center', 'title_color' => '#ffffff',
		'typography_typography' => 'custom',
		'typography_font_size' => [ 'unit' => 'px', 'size' => 38 ],
		'typography_font_weight' => '800',
	] ),
	ca_widget( 'text-editor', [
		'editor' => '<p>Call or message us now — same-day quotes, free installation with every unit.</p>',
		'align' => 'center', 'text_color' => 'rgba(255,255,255,0.85)',
		'typography_typography' => 'custom',
		'typography_font_size' => [ 'unit' => 'px', 'size' => 17 ],
	] ),
	ca_widget( 'button', [
		'text' => 'Call 0964-086-2665',
		'selected_icon' => $phone_icon,
		'icon_align' => 'left',
		'icon_indent' => [ 'unit' => 'px', 'size' => 10 ],
		'link' => [ 'url' => 'tel:+639640862665', 'is_external' => '', 'nofollow' => '' ],
		'align' => 'center', 'button_text_color' => CA_NAVY,
		'background_color' => '#ffffff',
		'hover_color' => '#ffffff', 'button_background_hover_color' => CA_ACCENT,
		'border_radius' => ca_radius( 40 ), 'text_padding' => ca_pad( 18, 38, 18, 38 ),
		'typography_typography' => 'custom',
		'typography_font_size' => [ 'unit' => 'px', 'size' => 16 ],
		'typography_font_weight' => '700',
	] ),
] );

/* ---------- write to the Home page ---------- */

$page_id = 5;
$elements = [ $hero, $brands, $services, $whyus, $about, $facebook, $contact, $cta ];

update_post_meta( $page_id, '_elementor_edit_mode', 'builder' );
update_post_meta( $page_id, '_elementor_template_type', 'wp-page' );
update_post_meta( $page_id, '_elementor_version', ELEMENTOR_VERSION );
update_post_meta( $page_id, '_wp_page_template', 'elementor_header_footer' );
update_post_meta( $page_id, '_elementor_page_settings', [ 'hide_title' => 'yes' ] );
update_post_meta( $page_id, '_elementor_data', wp_slash( wp_json_encode( $elements ) ) );

\Elementor\Plugin::$instance->files_manager->clear_cache();

echo "Home page #$page_id built with " . count( $elements ) . " sections.\n";
echo "Site title: " . get_option( 'blogname' ) . "\n";
