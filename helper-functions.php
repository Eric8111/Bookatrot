<?php
/**
 * Currently support ticket feature needs at booking listing for customer my account so we are enabling the js as well for that end point
 */
/**
 * WCFM Support JS
 */
function nls_support_scripts() {
    global $WCFM, $WCFMu, $wp, $WCFM_Query;
    
    if( is_account_page() ) {
        if( is_user_logged_in() ) {
            $test = $wp->query_vars['bookings'];
            if( isset( $wp->query_vars['bookings'] ) ) {
                $WCFM->library->load_blockui_lib();
                wp_enqueue_script( 'wcfmu_support_popup_js', $WCFMu->library->js_lib_url . 'support/wcfmu-script-support-popup.js', array('jquery' ), $WCFMu->version, true );
                // Localized Script
                $wcfm_messages = get_wcfm_support_manage_messages();
                wp_localize_script( 'wcfmu_support_popup_js', 'wcfm_support_manage_messages', $wcfm_messages );
            }
        }
    }
}
add_action('wp_enqueue_scripts', 'nls_support_scripts');

function nls_override_vendor_bookableprod_pricing_fields( $fields ){
    $test = $fields;
    if( isset($fields['_wc_booking_block_cost'])){ unset($fields['_wc_booking_block_cost']); }
    if( isset($fields['_wc_display_cost'])){ unset($fields['_wc_display_cost']); }    
    return $fields;
}
add_filter('wcfm_wcbokings_cost_fields', 'nls_override_vendor_bookableprod_pricing_fields', 10, 1);

/**
 * When order made 10% off on commission price if vendor of that product is hippovibe subscriber
 */
function nls_hippovibe_commisssion_cb( $commission_id, $order_id, $order, $vendor_id, $product_id, $order_item_id, $grosse_total, $total_commission, $is_auto_withdrawal, $commission_rule ){
    global $wpdb;
    //get vendor is hippovibe subscriber then rest of process
    $vendor_hippovibe_subscriber = false;
    $wcfmvm_custom_infos = (array) get_user_meta( $vendor_id, 'wcfmvm_custom_infos', true );
    if( array_key_exists("hippovibe-subscriber",$wcfmvm_custom_infos) ){
        foreach ($wcfmvm_custom_infos as $key => $value) {
            if( $key === "hippovibe-subscriber" && $value == "yes" ){ $vendor_hippovibe_subscriber = true; continue; }
        }
        if( $vendor_hippovibe_subscriber == true ){
            //get product amount - total comission so we will get price of 5 %
            // Purchase Price
            $product = wc_get_product( $product_id );
            $purchase_price = get_post_meta( $product_id, '_purchase_price', true );
            if( !$purchase_price ) $purchase_price = $product->get_price();
            $five_percent_price = $purchase_price - $total_commission;

            //make 10% and add to total comission
            $after_ten_percent = ((float)$five_percent_price * 10)/100;

            //update that 10% off on comission amount in db
            $final_comission_amount = $total_commission + $after_ten_percent;
            $wpdb->update("{$wpdb->prefix}wcfm_marketplace_orders", array('total_commission' => $final_comission_amount), array('ID' => $commission_id), array('%s'), array('%d') );
            //$wpdb->query("UPDATE {$wpdb->prefix}wcfm_marketplace_orders SET total_commission = 'shipped', total_commission = 'shipped' WHERE order_id = $order_id and vendor_id = $user_id and item_id = $order_item_id");


        }
    }
}
add_action('wcfmmp_order_item_processed','nls_hippovibe_commisssion_cb', 10, 10);

/**
 * Plugin have issue at file 
 * when only one checkbox for vendor registration and it's unchecked so older data remains same it should removed
 */
/**
 * Vendor Profile Additional Info Update
 */
function wcfmmp_profile_additional_info_update_nls_fix( $vendor_id, $wcfm_profile_form ){
    global $WCFM, $WCFMmp, $wpdb;
    if( !isset( $wcfm_profile_form['wcfmmp_additional_infos'] ) ) {
        delete_user_meta( $vendor_id, 'wcfmvm_custom_infos');
    }
}
add_action( 'wcfm_profile_update', 'wcfmmp_profile_additional_info_update_nls_fix' , 76, 2 );
add_action( 'wcfm_vendor_manage_profile_update', 'wcfmmp_profile_additional_info_update_nls_fix' , 76, 2 );

/**
 * Modify privacy policy label at add product
 */
function modify_privacy_policy_field_cb( $fields, $product_id ){
    $t = $fields;
    if( isset($fields['wcfm_shipping_policy'])){
        unset($fields['wcfm_shipping_policy']);
    }
    return $fields;
}
add_filter('wcfm_product_manage_fields_policies','modify_privacy_policy_field_cb', 10, 2);

/**
 * Remove pagiation at shop page
 */
add_action( 'wcfmmp_store_ppp', 'custom_pre_get_posts' );
function custom_pre_get_posts($post_per_page) {
    return 9999;
}

/**
 * Remove product count from top and bottom
 */
add_action( 'after_setup_theme', 'my_remove_product_result_count', 99 );
function my_remove_product_result_count() { 
    remove_action( 'woocommerce_before_shop_loop' , 'woocommerce_result_count', 20 );
    remove_action( 'woocommerce_after_shop_loop' , 'woocommerce_result_count', 20 );
}

/**
 * Generate unique code of Hippovibe when vendor signup
 * Send mail as well
 */
function nls_generate_unique_code_cb( $member_id, $wcfm_membership_registration_form_data ){
    global $current_user;
    $current_user = get_user_by( 'id', $member_id );
    //generate the unique code
    $hippovibeCode = 'bookatrot';
    $hippovibeCode = $hippovibeCode . $member_id;
    //str_pad($value, 8, '0', STR_PAD_LEFT);
    
    //send mail
    $mail_to = $wcfm_membership_registration_form_data['user_email'];
    if( $mail_to ){
        $new_account_mail_subject = "{site_name}: Hippovibe copuon code";
        $new_account_mail_body = 
            __( 'Dear', 'wc-frontend-membership_registration-vendor-membership' ) . ' {first_name}' .',<br/><br/>' . 
            __( 'Thank you for registering with {site_name}. You got 6 months free Hippovibe coupon code:', 'wc-frontend-membership_registration-vendor-membership' ) .'<br/><br/>' . 
            __( 'Copuon Code: ', 'wc-frontend-membership_registration-vendor-membership' ) . '{hippovibe_code}' . '<br/>
            Thank You';
        
        $subject = str_replace( '{site_name}', get_bloginfo( 'name' ), $new_account_mail_subject );
        $message = str_replace( '{site_name}', get_bloginfo( 'name' ), $new_account_mail_body );
        $message = str_replace( '{first_name}', $wcfm_membership_registration_form_data['first_name'], $message );
        $message = str_replace( '{hippovibe_code}', $hippovibeCode, $message );
        
        wp_mail( $mail_to, $subject, $message );
    }
    
}
add_action('wcfm_membership_registration','nls_generate_unique_code_cb', 10, 2);

/**
 * Change product wording at add order for vendor panel
 */
function nls_change_product_to_grade_cb( $fields ){
    $test = $fields;
    if( isset($fields) ){
        $fields['associate_products']['options']['product']['label'] = 'Select grade';
    }
    return $fields;
}
add_filter('wcfm_orders_manage_fields_product','nls_change_product_to_grade_cb');

function change_frontend_products_title($store_tabs, $id){
    if( isset( $store_tabs['products'] ) ){ $store_tabs['products'] = 'Sessions'; };
    return $store_tabs;
}
add_filter('wcfmmp_store_tabs','change_frontend_products_title', 10, 2 );
/**
 * Remove Fixed block of from booking duration at vendor side add product
 */
function remove_fixed_block_vendor_add_product( $fields ){
    if( isset($fields['_wc_booking_duration_type']) && isset($fields['_wc_booking_duration_type']['options']) && isset($fields['_wc_booking_duration_type']['options']['fixed']) ){
        unset($fields['_wc_booking_duration_type']['options']['fixed']);
    }
    $test = $fields;
    return $fields;
}
add_filter('wcfm_wcbokings_general_fields','remove_fixed_block_vendor_add_product');

/**
 * Order Notes change placeholder
 */
add_filter('woocommerce_checkout_fields', 'nls_custom_woocommerce_checkout_fields');
function nls_custom_woocommerce_checkout_fields( $fields ) {
     $fields['order']['order_comments']['placeholder'] = 'Notes about your order, e.g. special notes for organization.';
     return $fields;
}

add_filter( 'body_class', 'custom_body_class' );
/**
 * Add custom field body class(es) to the body classes.
 *
 * It accepts values from a per-page custom field, and only outputs when viewing a singular static Page.
 *
 * @param array $classes Existing body classes.
 * @return array Amended body classes.
 */
function custom_body_class( array $classes ) {
	$new_class = is_page_template( 'page-template/hire-a-carraige-template.php' ) ? 'post-type-archive-product woocommerce-shop woocommerce woocommerce-page page-template ' : null;

	if ( $new_class ) {
		$classes[] = $new_class;
	}

	return $classes;
}

/**
 * When product added to cart check if enough resources available
 */
function so_validate_add_cart_item( $passed, $product_id, $quantity, $variation_id = '', $variations= '' ) {
    if( has_term( 'carriage', 'product_cat', $product_id ) ) {
        $test = $product_id;
        //1. get total horses for vendor
        global $WCFM, $wpdb, $_POST, $WCFMu;
        $args = array(
            'posts_per_page'   => -1,
            'offset'           => 0,
            'category'         => '',
            'category_name'    => '',
            'bookingby'          => 'date',
            'booking'            => 'DESC',
            'include'          => '',
            'exclude'          => '',
            'meta_key'         => '',
            'meta_value'       => '',
            'post_type'        => 'bookable_resource',
            'post_mime_type'   => '',
            'post_parent'      => '',
            //'author'	   => get_current_user_id(),
            'post_status'      => 'any',
            //'suppress_filters' => 0 
        );
        $args['meta_query'] = array(
            array(
                'key'       => 'resouce_type',
                'value'     => 'horse',
            )
        );
        
        //get for product vendor
        $vendor_id = $WCFM->wcfm_vendor_support->wcfm_get_vendor_id_from_product( $product_id );
        $args['author'] = wc_clean($vendor_id);
        $wcfm_bookings_resources_array = array();
        $args = apply_filters( 'get_booking_resources_args', $args );
        $wcfm_bookings_resources_array = get_posts( $args );
        $booking_resources_count = count($wcfm_bookings_resources_array);

        

        $month = $_POST['wc_bookings_field_start_date_month'];
        $day = $_POST['wc_bookings_field_start_date_day'];
        $year = $_POST['wc_bookings_field_start_date_year'];
        //2. get for product vendor
        $selected_booking_date = '';
        $time = strtotime($year."/".$month."/".$day);
        $selected_booking_date = date('Y-m-d',$time);
        
        //3. check pair of horses available against number of bookings
        $sql = 'SELECT *, GROUP_CONCAT(ID) as commission_ids, GROUP_CONCAT(item_id) order_item_ids, GROUP_CONCAT(product_id) product_id, SUM( commission.quantity ) AS order_item_count, COALESCE( SUM( commission.item_total ), 0 ) AS item_total, COALESCE( SUM( commission.item_sub_total ), 0 ) AS item_sub_total, COALESCE( SUM( commission.shipping ), 0 ) AS shipping, COALESCE( SUM( commission.tax ), 0 ) AS tax, COALESCE( SUM( commission.shipping_tax_amount ), 0 ) AS shipping_tax_amount, COALESCE( SUM( commission.total_commission ), 0 ) AS total_commission, COALESCE( SUM( commission.discount_amount ), 0 ) AS discount_amount, COALESCE( SUM( commission.refunded_amount ), 0 ) AS refunded_amount, GROUP_CONCAT(is_refunded) is_refundeds, GROUP_CONCAT(refund_status) refund_statuses FROM ' . $wpdb->prefix . 'wcfm_marketplace_orders AS commission';
        $sql .= ' WHERE 1=1';
        $sql .= " AND `is_trashed` = 0 AND `vendor_id` = {$vendor_id}";
        $sql = apply_filters( 'wcfmmp_order_query', $sql );
        $sql .= " GROUP BY commission.order_id";
        $sql .= " ORDER BY `order_id` DESC";
        // $sql .= " LIMIT 25";
        // $sql .= " OFFSET 0";
        $data = $wpdb->get_results( $sql );
        $order_summary = $data;
        $order_horse_allocated_count = 0;
        if ( !empty( $order_summary ) ) {
            foreach ( $order_summary as $order ) {
                //test start
                // Get an instance of the WC_Order object
                $order = wc_get_order( $order->order_id );
                
                //we need to check for that particular order date
                //$order_date = $order->order_date;

                // Loop through order items
                foreach ( $order->get_items() as $item_id => $item ) {

                    // Get the product object
                    $product = $item->get_product();

                    // Get the product Id
                    $product_id = $product->get_id();

                    // Get the product name
                    $product_name = $item->get_name();

                    /**
                     * Get booking data if a line item is linked to a booking ID.
                     */
                    $horse_allocated = array();
                    $booking_ids = WC_Booking_Data_Store::get_booking_ids_from_order_item_id( $item_id );
                    if ( $booking_ids ) {
                        foreach ( $booking_ids as $booking_id ) {
                            $booking = new WC_Booking( $booking_id );
                            $all_horse_allocated = array();
                            //get booking date and if not same as customer selected then go away
                            $get_local_time = wc_should_convert_timezone( $booking );
                            if ( strtotime( 'midnight', $booking->get_start() ) === strtotime( 'midnight', $booking->get_end() ) ) {
                                $booking_date = sprintf( '%1$s', $booking->get_start_date( 'Y-m-d', null, $get_local_time ) );
                            } else {
                                $booking_date = sprintf( '%1$s - %2$s', $booking->get_start_date( 'Y-m-d', null, $get_local_time ), $booking->get_end_date( null, null, $get_local_time ) );
                            }
                            
                            //if customer selected date and booking date different then go away
                            if( $selected_booking_date != $booking_date ){
                                continue;
                            }

                            $horse_allocated = get_post_meta( $booking_id, 'allocated_horse_ids', true );
                            if( $horse_allocated ){
                                $all_horse_allocated[] = $horse_allocated;
                            }
                            $order_horse_allocated_count = $order_horse_allocated_count + count($all_horse_allocated);
                        }
                    }
                }
                //test end
            }
        }
        //check here test
        $total_available_horse_on_date = (int)$booking_resources_count - (int)$order_horse_allocated_count;
        $test = $order_horse_allocated_count;

        
        //4. if not show error

        // do your validation, if not met switch $passed to false
        if ( $booking_resources_count != 0 && $total_available_horse_on_date < 2 ){
            $passed = false;
            wc_add_notice( __( 'There are no horses available on this date.', 'jasy-child' ), 'error' );
        }
    }
    return $passed;

}
add_filter( 'woocommerce_add_to_cart_validation', 'so_validate_add_cart_item', 10, 5 );