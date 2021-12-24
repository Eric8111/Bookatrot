<?php
// 1. Register new endpoint (URL) for My Account page
// Note: Re-save Permalinks or it will give 404 error
  
function nls_add_2fa_verification_endpoint() {
    add_rewrite_endpoint( '2fa-verification', EP_ROOT | EP_PAGES );
}
  
add_action( 'init', 'nls_add_2fa_verification_endpoint' );
  
// ------------------
// 2. Add new query var
  
function nls_2fa_verification_query_vars( $vars ) {
    $vars[] = '2fa-verification';
    return $vars;
}
  
add_filter( 'query_vars', 'nls_2fa_verification_query_vars', 0 );
  
// ------------------
// 3. Insert the new endpoint into the My Account menu
  
function nls_add_2fa_verification_link_my_account( $items ) {
    $items['2fa-verification'] = '2FA Verification';
    return $items;
}
  
add_filter( 'woocommerce_account_menu_items', 'nls_add_2fa_verification_link_my_account' );
  
// ------------------
// 4. Add content to the new tab
  
function nls_2fa_verification_content() {
   echo '<h3>2FA Verification Settings</h3><p></p>';
   echo do_shortcode( '[twofactor_user_settings]' );
}
  
add_action( 'woocommerce_account_2fa-verification_endpoint', 'nls_2fa_verification_content' );
// Note: add_action must follow 'woocommerce_account_{your-endpoint-slug}_endpoint' format