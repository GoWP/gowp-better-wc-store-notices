<?php
/**
 * GoWP Better WooCommerce Store Notices
 *
 * @wordpress-plugin
 * Plugin Name: GoWP Better WooCommerce Store Notices
 * Description: Enhances WC store notice functionality
 * Version:     1.0.0
 * Author:      GoWP
 * Author URI:  https://www.gowp.com
 * Text Domain: gowp-better-wc-store-notices
 */


/* Register post type */

	add_action( 'init', 'bwcsn_register_store_notices' );
	function bwcsn_register_store_notices() {
		register_post_type(
			'bwcsn_shop_notice',
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

/* Add setting/control to Customizer */

	add_action( 'customize_register', 'bwcsn_customize_register', 20 );
	function bwcsn_customize_register( $wp_customize ) {
		$wp_customize->add_setting(
			'bwcsn_select_notice',
			array(
				'default'           => '',
				'type'              => 'option',
				'capability'        => 'manage_woocommerce',
			)
		);
		$wp_customize->add_control(
			'bwcsn_select_notice',
			array(
				'label'    => __( 'GoWP Better Store Notices', 'gowp-better-wc-store-notices' ),
				'description' => __( 'Choose the desired active store notice from the list below. This selection will over-ride the default WC store notice above. Store notices can be managed <a href="' . admin_url( 'edit.php?post_type=bwcsn_shop_notice' ) . '">here</a>.', 'gowp-better-wc-store-notices' ),
				'section'     => 'woocommerce_store_notice',
				'settings'    => 'bwcsn_select_notice',
				'type'        => 'dropdown-pages',
			)
		);
	}

	// Filter the result of the dropdown-pages control (simpler than a custom control)

		add_filter( 'get_pages', 'bwcsn_dropdown_pages_filter', 10, 2 );
		function bwcsn_dropdown_pages_filter( $pages, $r ) {
			if ( isset( $r['name'] ) && ( '_customize-dropdown-pages-bwcsn_select_notice' == $r['name'] ) ) {
				$args = array( 'numberposts' => '-1', 'post_type' => 'bwcsn_shop_notice' );
				$pages = get_posts( $args );
			}
			return $pages;
		}

/* Display the store notice if appliable */

	add_filter( 'woocommerce_demo_store', 'bwcsn_display_notice' );
	function bwcsn_display_notice( $output ) {
		if ( $bwcsn_select_notice_id = get_option( 'bwcsn_select_notice' ) ) {
			$args = array(
				'post_type' => 'bwcsn_shop_notice',
				'post_status' => 'publish',
				'p' => $bwcsn_select_notice_id
			);
			$notices = new WP_Query( $args );
			if ( ! empty( $notices->posts[0] ) ) {
				$bwcsn_select_notice_post = $notices->posts[0];
				$bwcsn_select_notice = $bwcsn_select_notice_post->post_content;
				$output = '<p class="woocommerce-store-notice demo_store">' . wp_kses_post( $bwcsn_select_notice ) . ' <a href="#" class="woocommerce-store-notice__dismiss-link">' . esc_html__( 'Dismiss', 'woocommerce' ) . '</a></p>';
			}
		}
		return $output;
	}