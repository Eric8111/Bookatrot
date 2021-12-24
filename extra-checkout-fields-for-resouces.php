<?php
/**
 * @snippet       Add Custom Field @ WooCommerce Checkout Page
 */
function nls_add_custom_checkout_field( $checkout ) {
    echo '<h3>' . __('Horse Details') . '</h3>';
    foreach ( WC()->cart->get_cart() as $cart_item ) {
        $product = $cart_item['data'];
        $booking_persons = isset($cart_item['booking']['Persons']) ? $cart_item['booking']['Persons'] : 1;
        if(!empty($product)){
            // $image = wp_get_attachment_image_src( get_post_thumbnail_id( $product->ID ), 'single-post-thumbnail' );
            //echo $product->get_image();
            //echo $product->get_id();
            // echo "<strong>";
            // echo $product->get_title();
            // echo "</strong>";
            // echo "<br>";
            $default_val = '';
            for ($i=0; $i < $booking_persons; $i++) { 
                if( (int) $booking_persons > 1){ 
                    $num = $i + 1;
                    echo '<h4>' . __('Person #') . $num . '</h4>'; 
                }
                woocommerce_form_field( 'horse['.$product->get_id().']['.$i.'][weight]', array(        
                    'type' => 'text',        
                    'class' => array( 'form-row-first' ),        
                    'label' => 'Weight',        
                    'placeholder' => 'e.g. 80kg',
                    'required' => true,        
                    'default' => $default_val,        
                 ) ); 
    
                 woocommerce_form_field( 'horse['.$product->get_id().']['.$i.'][height]', array(        
                    'type' => 'text',        
                    'class' => array( 'form-row-last' ),        
                    'label' => 'Height',        
                    'placeholder' => 'e.g. 170cm',
                    'required' => true,        
                    'default' => $default_val,        
                 ) ); 
            }

            // to display only the first product image uncomment the line below
            // break;
        }
    }


//    $current_user = wp_get_current_user();
//    $saved_license_no = $current_user->license_no;
//    woocommerce_form_field( 'license_no', array(        
//       'type' => 'text',        
//       'class' => array( 'form-row-wide' ),        
//       'label' => 'License Number',        
//       'placeholder' => 'CA12345678',        
//       'required' => true,        
//       'default' => $saved_license_no,        
//    ), $checkout->get_value( 'license_no' ) ); 
}
add_action( 'woocommerce_before_order_notes', 'nls_add_custom_checkout_field' );

/**
 * @snippet       Validate Custom Field @ WooCommerce Checkout Page
 */
function nls_validate_new_checkout_field() {
    //for validation get total fields
    foreach ( WC()->cart->get_cart() as $cart_item ) {
        $product = $cart_item['data'];
        $booking_persons = isset($cart_item['booking']['Persons']) ? $cart_item['booking']['Persons'] : 1;
        for ($i=0; $i < $booking_persons; $i++) {
            //validating weight
            $horse_number = $i + 1;
            if ( ! $_POST['horse'][$product->get_id()][$i]['weight'] ) {
                wc_add_notice( 'Please enter weight for ' . $product->get_title() .' horse '.$horse_number, 'error' );
             }
            //validating height
            if ( ! $_POST['horse'][$product->get_id()][$i]['height'] ) {
                wc_add_notice( 'Please enter height ' . $product->get_title() .' horse '.$horse_number, 'error' );
             }
        }
    }

    
    // if ( ! $_POST['license_no'] ) {
    //    wc_add_notice( 'Please enter your Licence Number.', 'error' );
    // }
}
add_action( 'woocommerce_checkout_process', 'nls_validate_new_checkout_field' );

/**
 * @snippet       Save & Display Custom Field @ WooCommerce Order
 */
function nls_save_new_checkout_field( $order_id ) { 
    //for storing fields
    foreach ( WC()->cart->get_cart() as $cart_item ) {
        $product = $cart_item['data'];
        $booking_id = $cart_item['booking']['_booking_id'];
        $booking_persons = isset($cart_item['booking']['Persons']) ? $cart_item['booking']['Persons'] : 1;
        $horse = [];
        for ($i=0; $i < $booking_persons; $i++) {
            $horse_number = $i + 1;
            if ( $_POST['horse'][$product->get_id()][$i]['weight'] && $_POST['horse'][$product->get_id()][$i]['height'] ) {
                $horse[$i]['weight'] = $_POST['horse'][$product->get_id()][$i]['weight'];
                $horse[$i]['height'] = $_POST['horse'][$product->get_id()][$i]['height'];
            }            
        }
        //update for specific booking
        if( !empty($horse) ){ 
            $horse = json_encode($horse);
            update_post_meta( $booking_id, '_horse_details_by_customer', $horse );
        }
    }
    //if ( $_POST['license_no'] ) update_post_meta( $order_id, '_license_no', esc_attr( $_POST['license_no'] ) );
}
add_action( 'woocommerce_checkout_update_order_meta', 'nls_save_new_checkout_field' );  

/*
function nls_show_new_checkout_field_order( $order ) {    
   $order_id = $order->get_id();
   if ( get_post_meta( $order_id, '_license_no', true ) ) echo '<p><strong>License Number:</strong> ' . get_post_meta( $order_id, '_license_no', true ) . '</p>';
}
add_action( 'woocommerce_admin_order_data_after_billing_address', 'nls_show_new_checkout_field_order', 10, 1 );   

function nls_show_new_checkout_field_emails( $order, $sent_to_admin, $plain_text, $email ) {
    if ( get_post_meta( $order->get_id(), '_license_no', true ) ) echo '<p><strong>License Number:</strong> ' . get_post_meta( $order->get_id(), '_license_no', true ) . '</p>';
}
add_action( 'woocommerce_email_after_order_table', 'nls_show_new_checkout_field_emails', 20, 4 );
*/