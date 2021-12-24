<?php
/**
 * found action that run after order updated
 */
function nls_vendor_order_update( $new_order_id, $order, $wcfm_orders_edit_form_data ){
    //test
    $test = $_POST;

    $order_items = $wcfm_orders_edit_form_data['wcfm_order_edit_input'];
	  		
    $products = array();
    
    foreach( $order_items as $order_edit_input_id => $order_edit_input ) {
        
        $order_item_id = absint( $order_edit_input['item'] );
    
        if( !$order_item_id ) continue;
        
        $line_item  = new WC_Order_Item_Product( $order_item_id );
        
        //temporary commented
        // $product    = $line_item->get_product();
        // $product_id = $line_item->get_product_id();
        
        //selected horses for particualr product/booking
        $selectedHorsesForBooking = $order_edit_input['horse_id'];
        //also get instructor id to allocate
        $selectedInstrutorForBooking = $order_edit_input['instructor_id'];
        /**
         * Get booking data if a line item is linked to a booking ID.
         */
        $booking_ids = WC_Booking_Data_Store::get_booking_ids_from_order_item_id( $order_item_id );
        if ( $booking_ids ) {
            foreach ( $booking_ids as $booking_id ) {
                $booking = new WC_Booking( $booking_id );
                $product  = $booking->get_product();
                // Get the product Id
                $product_id = $product->get_id();
                
                //if already horse not assigned then only assign and deduct hours
                $alreadyHorseAllocated = get_post_meta( $booking_id, 'allocated_horse_ids', true );
                if( empty($alreadyHorseAllocated) ){
                    $HorseIdsarr = array();                
                    //check all resouces selected for horse
                    foreach ($selectedHorsesForBooking as $resource_id) {
                        //get total time allocation by vendor
                        $totalHorseHours = getHorseRemainingHours($resource_id);
                        $totalHorseHours = convertToDecimal($totalHorseHours);
                        $totalHorseMinutes = hoursToMinutes($totalHorseHours);
                        
                        //assigned time to this resource for booking product by meta key: _wc_booking_duration
                        $assignedHoursForHorse = getBookingHorseAssignedHours($product_id);
                        $assignedHoursForHorse = convertToDecimal($assignedHoursForHorse);
                        $assignedHorseMinutes = hoursToMinutes($assignedHoursForHorse);
                        
                        //update the time we created in new 
                        $finalMinutes = $totalHorseMinutes - $assignedHorseMinutes;
                        //$finalHours = minutesToHours($finalMinutes);
                        update_post_meta( $resource_id, 'horse_hours_remaining', $finalMinutes );
                        $HorseIdsarr[]= $resource_id;
                    }
                    //allocate horse to booking
                    update_post_meta( $booking_id, 'allocated_horse_ids', $HorseIdsarr );
                }
                
                $alreadyInstrutorAllocated = get_post_meta( $booking_id, 'allocated_instrutor_ids', true );
                if( empty($alreadyInstrutorAllocated) ){
                    //allocate instructor to booking
                    update_post_meta( $booking_id, 'allocated_instrutor_ids', $selectedInstrutorForBooking );
                }
            }
        }
        
    }
}
add_action( 'after_wcfm_orders_edit', 'nls_vendor_order_update', 10, 3 );

/**
 * Create a table to store the weekly added hours by admin
 */
function nls_register_resouces_hours_table() {
    global $wpdb;

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

    //$wpdb->nls_weekly_resouces_hours = "{$wpdb->prefix}nls_weekly_resouces_hours";
    $table_created = get_option( 'nls_weekly_resouces_hours_tbl_created' );
    if( $table_created == 0 || $table_created == '' ){

        $nls_weekly_resouces_hours = $wpdb->prefix . 'nls_weekly_resouces_hours';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $nls_weekly_resouces_hours (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            resouce_id mediumint(9) NOT NULL,
            week_no mediumint(9) NOT NULL,
            week_start_date DATE NOT NULL,
            week_end_date DATE NOT NULL,
            hours tinytext NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        dbDelta( $sql );

        //set option to avoid loop
        update_option( 'nls_weekly_resouces_hours_tbl_created', 1 );
    }

    //when site initilalized then if today is Monday and it's next week exists then update for current week.
    $day = date('D');
    $resouce_limit = get_option( 'set_resouce_limit_next_week' );
    if( $day == 'Tue' && !empty($resouce_limit) ){
        update_option( 'resouce_limit', $resouce_limit );
        delete_option( 'set_resouce_limit_next_week' );
    }

}
add_action( 'init', 'nls_register_resouces_hours_table', 1 );



/**
 * When resouce limit stored
 */
function filter_redux_resouce_limit_sections( $value ) {
    if( isset($value['resouce-limit']) && !empty($value['resouce-limit']) ){
        //if today is Friday then value for next week
        $day = date('D');
        $resouce_limit = floatval($value['resouce-limit']);
        if( $day != 'Mon' ){
            update_option( 'set_resouce_limit_next_week', $resouce_limit );
        }else{
            update_option( 'resouce_limit', $resouce_limit );
        }
    }
    return $value;
    // make action magic happen here... 
}
add_action ('redux/options/nls_admin_settings/saved', 'filter_redux_resouce_limit_sections');