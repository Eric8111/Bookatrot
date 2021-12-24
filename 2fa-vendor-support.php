<?php
if(!defined('ABSPATH')) exit; // Exit if accessed directly

if(!class_exists('WCFM')) return; // Exit if WCFM not installed

/**
 * WCFM - Custom Menus Query Var
 */
function wcfm_nls_query_vars( $query_vars ) {
	$wcfm_modified_endpoints = (array) get_option( 'wcfm_endpoints' );
	
	$query_custom_menus_vars = array(
		'wcfm-2fa'         => ! empty( $wcfm_modified_endpoints['wcfm-2fa'] ) ? $wcfm_modified_endpoints['wcfm-2fa'] : '2fa',
	);
	
	$query_vars = array_merge( $query_vars, $query_custom_menus_vars );
	
	return $query_vars;
}
add_filter( 'wcfm_query_vars', 'wcfm_nls_query_vars', 50 );


/**
 * WCFM - Custom Menus End Point Title
 */
function wcfm_nls_endpoint_title( $title, $endpoint ) {
	global $wp;
	switch ( $endpoint ) {
		case 'wcfm-2fa' :
			$title = __( '2FA Authentication', 'wcfm-custom-menus' );
		break;
	}
	
	return $title;
}
add_filter( 'wcfm_endpoint_title', 'wcfm_nls_endpoint_title', 50, 2 );


/**
 * WCFM - Custom Menus Endpoint Intialize
 */
function wcfm_nls_init() {
	global $WCFM_Query;

	// Intialize WCFM End points
	$WCFM_Query->init_query_vars();
	$WCFM_Query->add_endpoints();
	
	if( !get_option( 'wcfm_updated_end_point_cms' ) ) {
		// Flush rules after endpoint update
		flush_rewrite_rules();
		update_option( 'wcfm_updated_end_point_cms', 1 );
	}
}
add_action( 'init', 'wcfm_nls_init', 50 );


/**
 * WCFM - Custom Menus Endpoiint Edit
 */
function wcfm_nls_endpoints_slug( $endpoints ) {
	
	$custom_menus_nls_endpoints = array(
        'wcfm-2fa'        => '2fa',
    );
	
	$endpoints = array_merge( $endpoints, $custom_menus_nls_endpoints );
	
	return $endpoints;
}
add_filter( 'wcfm_endpoints_slug', 'wcfm_nls_endpoints_slug' );

if(!function_exists('get_wcfm_custom_menus_url')) {
	function get_wcfm_custom_menus_url( $endpoint ) {
		global $WCFM;
		$wcfm_page = get_wcfm_page();
		$wcfm_custom_menus_url = wcfm_get_endpoint_url( $endpoint, '', $wcfm_page );
		return $wcfm_custom_menus_url;
	}
}

/**
 * WCFM - Custom Menus
 */
function wcfmcsm_wcfm_menus( $menus ) {
	global $WCFM;
	
	$custom_menus_nls = array( 
        'wcfm-2fa' => array(   
            'label'  => __( '2FA Authentication', 'wcfm-custom-menus'),
            'url'       => get_wcfm_custom_menus_url( 'wcfm-2fa' ),
            'icon'      => 'cogs',
            'priority'  => 12
        )
    );
    $menus = array_merge( $menus, $custom_menus_nls );		
	return $menus;
}
add_filter( 'wcfm_menus', 'wcfmcsm_wcfm_menus', 20 );

/**
 *  WCFM - Custom Menus Views
 */
function wcfm_csm_load_views( $end_point ) {
	global $WCFM, $WCFMu;
	$plugin_path = trailingslashit( dirname( __FILE__  ) );
	
	switch( $end_point ) {
		case 'wcfm-2fa':
			require_once( get_stylesheet_directory() . '/wcfm-2fa-view.php' );
		break;
	}
}
add_action( 'wcfm_load_views', 'wcfm_csm_load_views', 50 );
add_action( 'before_wcfm_load_views', 'wcfm_csm_load_views', 50 );