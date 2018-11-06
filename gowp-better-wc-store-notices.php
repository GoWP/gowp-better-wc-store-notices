<?php
/**
 * GoWP Better WooCommerce Store Notices
 *
 * @wordpress-plugin
 * Plugin Name: GoWP Better WooCommerce Store Notices
 * Description: Enhances WC store notice functionality
 * Author:      GoWP
 * Author URI:  https://www.gowp.com
 * Text Domain: gowp-better-wc-store-notices
 */

add_action( 'init', 'bsn_register_post_type' );
function bsn_register_post_type() {
	register_post_type(
		'bsn_shop_notice',
		array(
			'labels'                => array(
				'name'                  => _x( 'Store Notices', 'Post Type General Name', 'gowp-better-wc-store-notices' ),
				'singular_name'         => _x( 'Store Notice', 'Post Type Singular Name', 'gowp-better-wc-store-notices' ),
				'menu_name'             => __( 'Store Notices', 'gowp-better-wc-store-notices' ),
				'name_admin_bar'        => __( 'Store Notice', 'gowp-better-wc-store-notices' ),
			),
			'description'           => __( 'Better WooCommerce Store Notices', 'gowp-better-wc-store-notices' ),
			'public'                => false,
			'show_ui'               => true,
			'capability_type'       => 'post',
			'publicly_queryable'    => false,
			'exclude_from_search'   => true,
			'show_in_menu'          => current_user_can( 'manage_woocommerce' ) ? 'woocommerce' : true,
			'hierarchical'          => false,
			'rewrite'               => false,
			'query_var'             => false,
			'supports'              => array( 'title', 'editor', 'revisions', 'page-attributes' ),
			'show_in_nav_menus'     => false,
			'show_in_admin_bar'     => true,
		)
	);
}

add_action( 'customize_register', 'bsn_customize_register', 20 );
function bsn_customize_register( $wp_customize ) {
	//$wp_customize->remove_control( 'woocommerce_demo_store_notice' );
	$wp_customize->add_setting(
		'bsn_select_notice',
		array(
			'default'           => '',
			'type'              => 'option',
			'capability'        => 'manage_woocommerce',
		)
	);
	$wp_customize->add_control(
		'bsn_select_notice',
		array(
			'label'    => __( 'GoWP Better Store Notices', 'gowp-better-wc-store-notices' ),
			'description' => __( 'Choose the desired active store notice from the list below. This selection will over-ride the default WC store notice above. Store notices can be managed <a href="' . admin_url( 'edit.php?post_type=bsn_shop_notice' ) . '">here</a>.', 'gowp-better-wc-store-notices' ),
			'section'     => 'woocommerce_store_notice',
			'settings'    => 'bsn_select_notice',
			'type'        => 'dropdown-pages',
		)
	);
}

add_filter( 'get_pages', 'bsn_dropdown_pages_filter', 10, 2 );
function bsn_dropdown_pages_filter( $pages, $r ) {
	if ( '_customize-dropdown-pages-bsn_select_notice' == $r['name'] ) {
		$args = array( 'numberposts' => '-1', 'post_type' => 'bsn_shop_notice' );
		$pages = get_posts( $args );
	}
	return $pages;
}

add_filter( 'woocommerce_demo_store', 'bsn_display_notice' );
function bsn_display_notice( $output ) {
	if ( $bsn_select_notice_id = get_option( 'bsn_select_notice' ) ) {


		$bsn_select_notice_post = get_post( $bsn_select_notice_id );
		$bsn_select_notice = $bsn_select_notice_post->post_content;
		$output = '<p class="woocommerce-store-notice demo_store">' . wp_kses_post( $bsn_select_notice ) . ' <a href="#" class="woocommerce-store-notice__dismiss-link">' . esc_html__( 'Dismiss', 'woocommerce' ) . '</a></p>';
	}
	return $output;
}