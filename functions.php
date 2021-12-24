<?php
function jasyenqueue_style() {
    wp_enqueue_style("parent-style",get_parent_theme_file_uri("/style.css"));
    //this is for organization page
    //define( 'ORGANIZATION_PAGE_ID' , 'value'); 
    $organizationPageId = 729;
    if ( defined('ORGANIZATION_PAGE_ID') ){
        $organizationPageId = ORGANIZATION_PAGE_ID;
    }
    if( is_page($organizationPageId)  ){
        wp_enqueue_script( 'custom', get_stylesheet_directory_uri() . '/assets/js/custom.js', array ( 'jquery' ), true);
    }
}
add_action( 'wp_enqueue_scripts', 'jasyenqueue_style' );
add_action('after_setup_theme', 'remove_admin_bar');
function remove_admin_bar() {
    if (!current_user_can('administrator') && !is_admin()) {
        show_admin_bar(false);
    }
}

function wcfm_custom_2209_translate_text( $translated ) {
    $translated = str_ireplace( 'Store Name', 'Name of establishment', $translated );
    //$translated = str_ireplace( 'Store Phone', 'Your Market Phone', $translated );
    return $translated;
}
add_filter('gettext', 'wcfm_custom_2209_translate_text');
add_filter('ngettext', 'wcfm_custom_2209_translate_text');



function enqueuing_admin_scripts(){
 
    wp_enqueue_style('admin-style', get_stylesheet_directory_uri().'/admin-style.css');
    //wp_enqueue_script('admin-your-js-file-handle-name', get_stylesheet_directory_uri().'/your-js-file.js');
 
}
 
add_action( 'wp_enqueue_scripts', 'enqueuing_admin_scripts' );

/*add_filter( 'woocommerce_product_query_meta_query', 'filter_products_with_custom_field', 10, 2 );
function filter_products_with_custom_field( $meta_query, $query ) {
    
    //alway exlude virtual products
    $meta_query[] = array(
        'key'     => '_virtual',
        'value'   => 'yes',
        'compare' => '!=',
    );
    
    $meta_key = 'grading_level'; // <= Here define the meta key
    if( is_user_logged_in() && ! is_admin() ){
        // $meta_query[] = array(
        //     'key'     => 'grading_level',
        //     'compare' => 'EXISTS'
        // );

        $meta_value = 0;
        $customer_orders = getLoggedInCustomerOrders();
        //if no orders then show only first assesment product (product with customer field defined 0)
        //TODO:Temporary disabled
        if( empty($customer_orders) ){
            $meta_query[] = array(
                'key'   => $meta_key,
                'value' => esc_attr($meta_value),
             );
        }

        //check if orders then start getting virtual products from orders
        $breakNow = false;
        if( !empty($customer_orders) ){

            //if there is an order so it means assesment is bought so we will exlude it

            
            foreach ($customer_orders as $key => $value) {
                $order_id = $value->ID;
                $order = wc_get_order( $order_id );
                $items = $order->get_items(); 
                foreach ($items as $key => $item) {
                    $product_id = $item->get_product_id();
                    // $product_id = 736; //for testing purpose
                    $currentGradeLevel = get_post_meta( $product_id, 'grading_level', true );
                    // Check if the custom field has a value.
                    if ( $currentGradeLevel != '' ) {
                        $vendor_id = wcfm_get_vendor_id_by_post( $product_id );
                        $vendorGrades = getVendorGrades( $vendor_id );
                        $currentGradeLevel = (int)$currentGradeLevel;
                        //now get grade upto next level from current
                        foreach ($vendorGrades as $pId => $level) {
                            $nextLevel[] = $level;
                            $nextLevelProductIds[] = $pId;
                            if( $breakNow == true ){
                                break;
                            }
                            if( $currentGradeLevel == $level){
                                $breakNow = true;
                            }
                        }
                    }
                }
            }
            // $meta_query[] = array(
            //     'key' => '_tied_products',
            //     'value' => array(742),
            //     'compare' => 'LIKE'
            // );

        }
        
    }

    return $meta_query;
} */

//get all the orders for loggin customer
function getLoggedInCustomerOrders( $vendorId = 0 ){
    //get orders so we can set which products needs to set
    $customer_orders = get_posts( array(
        'numberposts' => -1,
        'meta_key'    => '_customer_user',
        'meta_value'  => get_current_user_id(),
        'post_type'   => wc_get_order_types(),
        'post_status' => array_keys( wc_get_order_statuses() ),
    ) );

    if( $vendorId != 0 ){
        if( !empty($customer_orders) ){
            foreach ($customer_orders as $key => $value) {
                $order_id = $value->ID;
                $order = wc_get_order( $order_id );
                $items = $order->get_items(); 
                    foreach ($items as $key2 => $item) {
                        $product_id = $item->get_product_id();
                        $productVendorId = get_post_meta( $product_id, '_wcfm_product_author', true );
                        if( $productVendorId != $vendorId ){
                            unset($customer_orders[$key]);
                        }
                    }
            }
        }
    }
    return $customer_orders;

}
function so_20990199_product_query( $q ){ 
    if( ! is_user_logged_in() && (!is_admin() || !wcfm_is_vendor()) ){
        $no_products = array(''); 
        $q->set( 'post__in', (array) $no_products ); 
    }

}
add_action( 'woocommerce_product_query', 'so_20990199_product_query' );

function getVendorGrades( $vendorId = 0 ){
    if( $vendorId == 0 ) { return; }

    global $WCMp;

    $vendorGradesResult = array();
    $default = array(
        'post_type' => 'product',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'author' => $vendorId, //change your vendor id here
        'meta_query' => array( 
            array( 
             'key' => 'grading_level', 
             'value' => '',
             'compare' => '!='
            ), 
          ), 
    );

    $args = wp_parse_args($args, $default);

    $products = new WP_Query( $args );
    
    if ($products->have_posts()) :
        ?>

        <?php //woocommerce_product_loop_start(); ?>

        <?php while ($products->have_posts()) : $products->the_post(); ?>

            <?php //wc_get_template_part('content', 'product'); ?>
            <?php 
                $gradeLevel = get_post_meta( get_the_ID(), 'grading_level', true );
                $productId = get_the_ID();
                // Check if the custom field has a value.
                if ( $gradeLevel != '' ) {
                    $vendorGradesResult[$productId] = (int)$gradeLevel;
                }
            ?>

        <?php endwhile; // end of the loop.  ?>

        <?php //woocommerce_product_loop_end(); ?>

        <?php
        asort($vendorGradesResult);
    endif;

    //woocommerce_reset_loop();
    wp_reset_postdata();

    // Remove ordering query arguments
    //WC()->query->remove_ordering_args();

    return $vendorGradesResult;
    
}

function getVendorGradesNew( $vendorId = 0 ){
    if( $vendorId == 0 ) { return; }

    global $WCMp;

    $vendorGradesResult = array();
    $default = array(
        'post_type' => 'product',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'author' => $vendorId, //change your vendor id here
        'meta_query' => array( 
            array( 
             'key' => 'grading_level', 
             'value' => '',
             'compare' => '!='
            ), 
          ), 
    );

    $args = wp_parse_args($args, $default);

    $products = new WP_Query( $args );
    
    if ($products->have_posts()) :
        ?>

        <?php woocommerce_product_loop_start(); ?>

        <?php while ($products->have_posts()) : $products->the_post(); ?>

            <?php //wc_get_template_part('content', 'product'); ?>
            <?php 
                $gradeLevel = get_post_meta( get_the_ID(), 'grading_level', true );
                $productId = get_the_ID();
                // Check if the custom field has a value.
                if ( $gradeLevel != '' ) {
                    $gradeLevel = (int)$gradeLevel;
                    $vendorGradesResult[$gradeLevel] = $productId;
                }
            ?>

        <?php endwhile; // end of the loop.  ?>

        <?php woocommerce_product_loop_end(); ?>

        <?php
        asort($vendorGradesResult);
    endif;

    woocommerce_reset_loop();
    wp_reset_postdata();

    // Remove ordering query arguments
    WC()->query->remove_ordering_args();

    return $vendorGradesResult;
    
}

function getCustomerGradeBasedOnOrders( $vendor_id = 0 ){
    $customer_orders = getLoggedInCustomerOrders($vendor_id);
    $nextLevelProductIds = array();
    //check if orders then start getting virtual products from orders
    $breakNow = false;
    if( !empty($customer_orders) ){

        //if there is an order so it means assesment is bought so we will exlude it
        foreach ($customer_orders as $key => $value) {
            $order_id = $value->ID;
            $order = wc_get_order( $order_id );
            $items = $order->get_items(); 
            foreach ($items as $key => $item) {
                $product_id = $item->get_product_id();
                // $product_id = 736; //for testing purpose
                $currentGradeLevel = get_post_meta( $product_id, 'grading_level', true );
                // Check if the custom field has a value.
                if ( $currentGradeLevel != '' ) {
                    $vendor_id = wcfm_get_vendor_id_by_post( $product_id );
                    $vendorGrades = getVendorGrades( $vendor_id );
                    $currentGradeLevel = (int)$currentGradeLevel;
                    //now get grade upto next level from current
                    foreach ($vendorGrades as $pId => $level) {
                        if( $breakNow == true ){
                            break;
                        }
                        $nextLevel[] = $level;
                        $nextLevelProductIds[] = $pId;
                        if( $currentGradeLevel == $level){
                            $breakNow = true;
                        }
                    }
                }
            }
        }
        // $meta_query[] = array(
        //     'key' => '_tied_products',
        //     'value' => array(742),
        //     'compare' => 'LIKE'
        // );

    }
    return $nextLevelProductIds;
}

//add_filter( 'woocommerce_product_query_meta_query', 'filter_products_with_custom_field_new', 10, 2 );
function filter_products_with_custom_field_new( $meta_query, $query ) {

    $meta_key = 'grading_level'; // <= Here define the meta key

    if( is_user_logged_in() && ! is_admin() ){
        //alway exlude virtual products
        $meta_query[] = array(
            'key'     => '_virtual',
            'value'   => 'yes',
            'compare' => '!=',
        );
    }
}
/**
 * If user is logged in then we will show only product based on orders having virtual product hight level (begginer and complete begginer then begginer will be taken)
 */

///add_filter( 'woocommerce_product_query_tax_query', 'exclude_products_fom_unlogged_users', 10, 2 );
function exclude_products_fom_unlogged_users( $tax_query, $query ) {
    // On frontend for unlogged users
    if( ! is_user_logged_in() ){
        $tax_query[] = array(
            'taxonomy'  => 'product_cat',
            'field'     => 'slug',
            'terms'     => array('t-shirts'), // <=== HERE the product category slug
            'operator'  => 'NOT IN'
        );
    }
    return $tax_query;
}

function getAssestmentProductByVendorId( $vendor_id = 0 ){
    $args = array(
        'post_type'     => 'product',
        'post_status'   => 'publish',
        'posts_per_page'=> -1,
        'order'         => 'DESC',
        'meta_query'    => array(
            'relation'  => 'AND',
            array(
                'key'       => '_wcfm_product_author',
                'value'     => $vendor_id,
                'compare'   => '='
            ),
            array(
                'key'       => 'grading_level',
                'value'     => '0',
                'compare'   => '=' //'>='
            )
        )
    );
    $query = new WP_Query( $args );
    $post_ids = wp_list_pluck( $query->posts, 'ID' );
    return $post_ids;
}

/**
 * Hide all product if user is not logged in and also not admin or vendor
 */
add_action( 'woocommerce_no_products_found', function(){
    if( ! is_user_logged_in() || !is_admin()  || !wcfm_is_vendor() ){
        remove_action( 'woocommerce_no_products_found', 'wc_no_products_found', 10 );

        // HERE change your message below
        $message = __( 'Sorry, but you need to login to check lessons.', 'woocommerce' );

        echo '<p class="woocommerce-info">' . $message .'</p>';
    }
}, 9 );

function vendorExistInOrder( $vendorId = 0 ){
    if( $vendorId == 0 ){ return; }
    $customer_orders = getLoggedInCustomerOrders();
    if( !empty($customer_orders) ){
        foreach ($customer_orders as $key => $value) {
            $order_id = $value->ID;
            $order = wc_get_order( $order_id );
            $items = $order->get_items(); 
                foreach ($items as $key => $item) {
                    $product_id = $item->get_product_id();
                    
                }
        }
    }
    
    
}

function getAssestmentProductByDependency( $product ){
    if( !$product ){ return; }
    //get product meta and return based on meta and user grade level
    $dependent_ids = get_post_meta( $product->id, '_tied_products', true );
    if ( WC_PD_Core_Compatibility::is_wc_version_gte_2_7() ) {
        $dependent_ids = $product->get_meta( '_tied_products', true );
    } else {
        $dependent_ids = (array) get_post_meta( $product->id, '_tied_products', true );
    }

    $dependent_ids = empty( $dependent_ids ) ? array() : array_unique( $dependent_ids );

    foreach ($dependent_ids as $product_id) {
        $currentGradeLevel = get_post_meta( $product_id, 'grading_level', true );
        if( $currentGradeLevel == 0 ){
            return true;
        }
    }
    return false;
}

add_filter( 'woocommerce_account_menu_items', 'misha_remove_my_account_dashboard' );
function misha_remove_my_account_dashboard( $menu_links ){
	unset( $menu_links['dashboard'] );
	return $menu_links;	
}


//• Instead of become a vendor, there should be text “Sign Up your organization”
add_filter('wcfm_become_vendor_label',function($text){
    $text = __( 'Sign Up your organization', 'wc-multivendor-marketplace' ); //replace the "Become a Vendor" text as your requirement
    return $text;
});

//remove map from homepage shortcode
function wcfm_stores_args_modify( $defaults ) {
    $defaults['map'] = 'no';
    $defaults['has_map'] = 'no';
    return $defaults;
}
add_filter( 'wcfmmp_stores_default_args', 'wcfm_stores_args_modify', 10, 1 );

//removeing some of fields from vendor panel
add_action( 'after_setup_theme', 'wpdocs_i_am_a_function' );
function wpdocs_i_am_a_function() {
    if( is_user_logged_in() && wcfm_is_vendor()){
        //hide menus at bottom of add order 
        add_filter('wcfm_orders_manage_payment',false);
        add_filter('wcfm_orders_manage_shipping',false);
        add_filter('wcfm_orders_manage_address',false);
        add_filter('wcfm_orders_manage_discount',false);
        add_filter('wcfm_orders_manage_note',false);

        //hide fields at report menu
        add_filter('wcfm_reports_menus','remove_reports_menu', 11, 1 );
    }
}
function remove_reports_menu( $menus ){
    if( isset($menus['coupons-by-date']) ){ unset($menus['coupons-by-date']); }
    if( isset($menus['low-in-stock']) ){ unset($menus['low-in-stock']); }
    if( isset($menus['out-of-stock']) ){ unset($menus['out-of-stock']); }
    return $menus;
}

/**
 * This method will added custom so we modified plugin.
 * We added one line apply_filter in plugins file number 544 in function get_tied_product_ids
 * $dependent_ids = apply_filters( 'nls_override_dependent_ids', $product, $dependent_ids );
 */
function change_dependent_ids_cb( $product, $dependent_ids ){
    $p = $product;
    $d = $dependent_ids;
    if( is_user_logged_in() && ! is_admin() ){

        //if no orders then show only assestment product
        global $WCFM; //$product
        $id = $product->get_id();
        $vendor_id = $WCFM->wcfm_vendor_support->wcfm_get_vendor_id_from_product( $id );
        $customer_orders = getLoggedInCustomerOrders( $vendor_id );
        if( empty($customer_orders) ){
            global $WCFM; //get_id
            //$id = $product->get_id();
            $vendor_id = $WCFM->wcfm_vendor_support->wcfm_get_vendor_id_from_product( $id );
            $assesmentProducts = getAssestmentProductByVendorId( $vendor_id );
            if( !in_array($id,$assesmentProducts)){
                return;
            }
        }else{
    
            
            //else show based on grade level
            //extra get meta
            //get product meta and return based on meta and user grade level
            $dependent_ids = get_post_meta( $product->id, '_tied_products', true );
            if ( WC_PD_Core_Compatibility::is_wc_version_gte_2_7() ) {
                $dependent_ids = $product->get_meta( '_tied_products', true );
            } else {
                $dependent_ids = (array) get_post_meta( $product->id, '_tied_products', true );
            }
    
            $dependent_ids = empty( $dependent_ids ) ? array() : array_unique( $dependent_ids );
            // print_r($product->get_id());
            $customerOrders = getLoggedInCustomerOrders( $vendor_id );
            if( empty($dependent_ids) ){
                return;
            }
            //print_r($dependent_ids);
    
            $customerGrades = getCustomerGradeBasedOnOrders( $vendor_id );
            
            $result = array_intersect($customerGrades, $dependent_ids);
            if( !empty($result) ){
                $dependent_ids = array_merge($dependent_ids, $customerGrades);
                $dependent_ids = array_unique($dependent_ids);
            }
        }
    
    }
    return $dependent_ids;
}
add_filter('nls_override_dependent_ids','change_dependent_ids_cb', 10, 2 );
/* Menu modification */
add_filter( 'wp_nav_menu_items', 'wti_loginout_menu_link', 9, 2 );

function wti_loginout_menu_link( $items, $args ) {

   if ($args->theme_location == 'header') {
    if( is_user_logged_in()){
      	$submenu = '<ul class="sub-menu">
      				<li class="right"><a href="'. wp_logout_url( get_permalink( woocommerce_get_page_id( 'myaccount' ) ) ) .'">'. __("Log Out") .'</a></li>
      				</ul>';
        $items .= '<li class="right menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children menu-item-2415 nav-item my-account"><a href="'.get_site_url().'/my-account">'. __("My Account") .'</a></li> <li class="right logout-btn"><a href="'. wp_logout_url( get_permalink( woocommerce_get_page_id( 'myaccount' ) ) ) .'"><i class="fas fa-sign-out-alt"></i></a></li>';
      } else {
        $items .= '<li class="right login-icon" ><a href="'. site_url().'/my-account" ><i class="fas fa-user"></i></a></li>';
        // $items .= '<li class="right"><a href="'.get_site_url().'/registration/">'. __("Sign Up!") .'</a></li>';
      }
   }
   return $items;
}



/* remove admin strip */
add_filter('show_admin_bar', '__return_false');

require_once 'resource-feature.php';
require_once 'resource-feature-order-items.php';

//include third party files
require_once 'admin-settings-resouces-limit.php';

/**
 * Enable 2FA settings for customer and vendor
 */
require_once '2fa-vendor-support.php';
require_once '2fa-customer-support.php';

/**
 * Weight and height 
 */
require_once 'extra-checkout-fields-for-resouces.php';

/**
 * Some helper functions
 */
require_once 'helper-functions.php';
require_once 'predefined-grades.php';