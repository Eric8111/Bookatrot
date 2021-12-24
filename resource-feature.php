<?php
/**
 * Vendor can add hours at resources
 */
function wcfm_vendor_add_hours_to_resources( $fields = array() ){
    $test = $fields;
    if( is_user_logged_in() && wcfm_is_vendor()){
        $hrsField1 = array(
            "label"       => "Resouce Type",
            "type"        => "select",
            "class"       => "wcfm-select wcfm_ele",
            "label_class" => "wcfm_ele wcfm_title wcfm_hours",
            "value"       => '',
            "options" => array(
                'horse'      => 'Horse',
                'instructor' => 'Instructor'
            )
        );
        $fields['resouce_type'] = $hrsField1;

        //append start and end date to label
        $today_date = date('Y-m-d');
        $start_end_dates = _getWeekStartEndDatesByDate($today_date);
        $label_field2 = '<br/>('. $start_end_dates['start'] . ' to ' . $start_end_dates['end'] . ')';

        $hrsField2 = array(
            "label"       => "Hours (For example: 40)" . $label_field2,
            "type"        => "number",
            "class"       => "wcfm-text wcfm_ele",
            "label_class" => "wcfm_ele wcfm_title wcfm_hours",
            "value"       => 0
        );
        $fields['horse_hours'] = $hrsField2;

        //read only field
        $hrsField3 = array(
            "label"       => "Hours (Remaining) : ",
            "type"        => "title",
            "class"       => "wcfm-text wcfm_ele",
            "label_class" => "wcfm_ele wcfm_title wcfm_hours",
            "value"       => 0
        );
        $fields['remaining_hrs_readonly'] = $hrsField3;

        

        //remove qty
        if( isset($fields['qty']) ){
            unset($fields['qty']);
        }

        //set the predefined value
        if( isset($fields['resource_id']['value']) && $fields['resource_id']['value'] != 0 ){
            //get resource type
            $fields['resouce_type']['value'] = getResouceType($fields['resource_id']['value']);
            //get horse hours
            //$fields['horse_hours']['value']  = getHorseHours($fields['resource_id']['value']);
            //set 0 because when it's all used and still we show from db then it will display error
            $fields['horse_hours']['value']  = 0;

            //add remaining hours
            $fields['remaining_hrs_readonly']['label']  = $fields['remaining_hrs_readonly']['label'] . convertToHoursMins($fields['resource_id']['value']);
        }
    }
    return $fields;
}
add_filter('resource_manager_fields_general','wcfm_vendor_add_hours_to_resources', 10, 1 );

function getResouceCurrentWeekHours($resouce_id=0){
    if( $resouce_id == 0 ){ return; }
    
    global $wpdb;
    //now add entry in database new table
    $nls_weekly_resouces_hours = $wpdb->prefix . 'nls_weekly_resouces_hours';
    $total_hours = $wpdb->get_var( $wpdb->prepare( "SELECT SUM(hours) as total_hours from $nls_weekly_resouces_hours where resouce_id = %d", $resouce_id ) );
    return $total_hours;
}

/**
 * Storing the extra fields.
 */

function update_hours_for_resources(){
    global $WCFM, $WCFMu, $wpdb, $wcfm_resource_manager_form_data;
    $resouce_limit = get_option( 'resouce_limit' );
    $controller = '';
  	if( isset( $_POST['controller'] ) && $_POST['controller'] == 'wcfm-bookings-resources-manage' ) {
        $wcfm_resource_manager_form_data = array();
        parse_str($_POST['wcfm_resources_manage_form'], $wcfm_resource_manager_form_data);
        
        if(isset($wcfm_resource_manager_form_data['horse_hours']) ) {
            $resource_id = 0;

            //if it's instructor then don't update
            $hours = $wcfm_resource_manager_form_data['horse_hours'];
            if( $wcfm_resource_manager_form_data['resouce_type'] == 'instructor' ){
                $hours = 0;
            }
            
            $resouce_limit_used = getResouceCurrentWeekHours($wcfm_resource_manager_form_data['resource_id']);
            $resouce_limit_used = (int) $resouce_limit_used + (int) $wcfm_resource_manager_form_data['horse_hours'];
            //if assigned hours are more then admin set then 
            if( isset($resouce_limit) && $wcfm_resource_manager_form_data['resouce_type'] == 'horse' && (int) $resouce_limit < $resouce_limit_used ){
                echo '{"status": false, "message": " You can not assign more than ' . $resouce_limit . ' hours." }';
                die;
            }
            

            //update resouce hours by vendor
            $data = array();
            $data = $wcfm_resource_manager_form_data;
            $data['hours'] = $hours;
            updateResouceHoursByVendor($data);
            
            if(isset($wcfm_resource_manager_form_data['resource_id']) && $wcfm_resource_manager_form_data['resource_id'] == 0) {
				//$nresource = new WC_Product_Booking_Resource();
				// $nresource->set_name( $wcfm_resource_manager_form_data['horse_hours'] );
				// $resource_id = $nresource->save();
                updateResouceType($resource_id, $wcfm_resource_manager_form_data['resouce_type']);
                //updateHorseHours($resource_id, $hours);
			} else { // For Update
				$resource_id = $wcfm_resource_manager_form_data['resource_id'];
                updateResouceType($resource_id, $wcfm_resource_manager_form_data['resouce_type']);
                //updateHorseHours($resource_id, $hours);
				//$resource = new WC_Product_Booking_Resource( $resource_id );
				// $resource->set_name( $wcfm_resource_manager_form_data['horse_hours'] );
				// $resource->save();
			}
        }
    }
}
add_action( 'after_wcfm_ajax_controller', 'update_hours_for_resources' );

function _getWeekStartEndDatesByDate($current_date) {
	$week = date('W', strtotime($current_date));
	$year = date('Y', strtotime($current_date));
	return _getWeekStartEndDatesByWeekAndYear($week, $year);
}

function _getWeekStartEndDatesByWeekAndYear($week, $year) {
	$dto = new DateTime();
	$result['start'] = $dto->setISODate($year, $week, 1)->format('Y-m-d');
	$result['end'] = $dto->setISODate($year, $week, 7)->format('Y-m-d');
	$result['week'] = $week;
	return $result;
}

function updateResouceHoursByVendor($data){
    global $wpdb;
    //now add entry in database new table
    $nls_weekly_resouces_hours = $wpdb->prefix . 'nls_weekly_resouces_hours';

    //get current week number
    $today_date = date('Y-m-d');
    $today = date('Y-m-d H:i:s');
    $start_end_dates = _getWeekStartEndDatesByDate($today_date);

    //get already assign hours and compare if more then limit set then return with error
    
    $wpdb->insert( $nls_weekly_resouces_hours, array( 
        'resouce_id' => $data['resource_id'],
        'week_no' => $start_end_dates['week'],
        'week_start_date' => $start_end_dates['start'],
        'week_end_date' => $start_end_dates['end'],
        'hours' => $data['hours'],
        'created_at' => $today
    ), array( '%d', '%d', '%s', '%s', '%d', '%s') );

    //also main place where we are storing remaining hours
    $hours = getResouceCurrentWeekHours($data['resource_id']);
    $minutes = hoursToMinutes($hours);
    update_post_meta( $data['resource_id'], 'horse_hours', $minutes );
    //update total hours

    //get remaining hours and add then update remaining
    $resouce_minutes = get_post_meta( $data['resource_id'], 'horse_hours_remaining', true );
    $resouce_hours = minutesToHours($resouce_minutes);
    $resouce_hours = (int)$resouce_hours + (int)$data['hours'];
    $resouce_minutes = hoursToMinutes($resouce_hours);
    update_post_meta( $data['resource_id'], 'horse_hours_remaining', $resouce_minutes );

    // $final_hours = (int) $hours + (int) $data['hours'];
    // $final_minutes = hoursToMinutes($final_hours);
    // update_post_meta( $resource_id, 'horse_hours_remaining', $final_minutes );

}

function updateResouceType($resource_id=0,$type=''){
    update_post_meta( $resource_id, 'resouce_type', $type );
}

function getResouceType($resource_id=0){
    $type = get_post_meta( $resource_id, 'resouce_type', true );
    return $type;
}

function updateHorseHours($resource_id=0,$hours=0){
    $minutes = hoursToMinutes($hours);
    update_post_meta( $resource_id, 'horse_hours', $minutes );
    update_post_meta( $resource_id, 'horse_hours_remaining', $minutes );
}

function getHorseHours($resource_id=0){
    $minutes = get_post_meta( $resource_id, 'horse_hours', true );
    $hours = minutesToHours($minutes);
    return $hours;
}

function getHorseRemainingHours($resource_id=0){
    $minutes = get_post_meta( $resource_id, 'horse_hours_remaining', true );
    $hours = minutesToHours($minutes);
    return $hours;
}

function convertToHoursMins($resource_id, $format = '%02d hours %02d minutes') {
    // $totalHours = getResouceCurrentWeekHours($resource_id);
    // $resouce_limit = get_option( 'resouce_limit' );
    // $final_hours = (int)$resouce_limit - (int)$totalHours;
    $time = get_post_meta( $resource_id, 'horse_hours_remaining', true );
    $final_hours = minutesToHours($time);
    $time = hoursToMinutes($final_hours);

    // if ($time < 1) {
    //     return;
    // }
    $hours = floor($time / 60);
    $minutes = ($time % 60);
    return sprintf($format, $hours, $minutes);
}

// Transform hours like "1:45" into the total number of minutes, "105". 
function hoursToMinutes($hours) 
{ 
    $minutes = 0; 
    if (strpos($hours, ':') !== false) 
    { 
        // Split hours and minutes. 
        list($hours, $minutes) = explode(':', $hours); 
    } 
    return $hours * 60 + $minutes; 
} 

// Transform minutes like "105" into hours like "1:45". 
function minutesToHours($minutes) 
{ 
    $hours = (int)($minutes / 60); 
    $minutes -= $hours * 60; 
    return sprintf("%d.%02.0f", $hours, $minutes); 
}  


function getBookingHorseAssignedHours($resource_id=0){
    $assignedHours = get_post_meta( $resource_id, '_wc_booking_duration', true );
    return $assignedHours;
}

function convertToDecimal($number=0){
    $number = number_format($number, 2, '.', ',');
    return $number;
}

function getProductIdAssociatedByBookingId($bookingId=0){
    $productId = get_post_meta( $bookingId, '_booking_product_id', true );
    return $productId;
}

/**
 * This is temporary so we need to add textbox at order add/edit and then 
 */
//add_action('woocommerce_thankyou', 'update_remaining_horse_hours', 10, 1);
function update_remaining_horse_hours( $order_id ) {
    if ( ! $order_id )
        return;

    // Allow code execution only once 
    if( ! get_post_meta( $order_id, '_horse_hours_updated', true ) ) {

        // Get an instance of the WC_Order object
        $order = wc_get_order( $order_id );

        // Get the order key
        $order_key = $order->get_order_key();

        // Get the order number
        $order_key = $order->get_order_number();

        if($order->is_paid())
            $paid = __('yes');
        else
            $paid = __('no');

        // Loop through order items
        foreach ( $order->get_items() as $item_id => $item ) {

            // Get the product object
            $product = $item->get_product();

            // Get the product Id
            $product_id = $product->get_id();

            // Get the product name
            $product_id = $item->get_name();

            /**
             * Get booking data if a line item is linked to a booking ID.
             */
            $booking_ids = WC_Booking_Data_Store::get_booking_ids_from_order_item_id( $item_id );
            if ( $booking_ids ) {
                foreach ( $booking_ids as $booking_id ) {
                    $booking = new WC_Booking( $booking_id );
                    $product  = $booking->get_product();
                    $resource = $booking->get_resource();
                    if ( $resource ) :
                        $resource_id = $resource->get_id();
                        $name        = $resource->get_name();
                        //get total time allocation by vendor
                        $totalHorseHours = getHorseHours($resource_id);
                        $totalHorseHours = convertToDecimal($totalHorseHours);
                        
                        //assigned time to this resource for booking product by meta key: _wc_booking_duration
                        $assignedHoursForHorse = getBookingHorseAssignedHours($resource_id);
                        $assignedHoursForHorse = convertToDecimal($assignedHoursForHorse);
                        
                        //update the time we created in new 
                        $finalHours = $totalHorseHours - $assignedHoursForHorse;
                    endif;
                }
            }
        }

        // Output some data
        //echo '<p>Order ID: '. $order_id . ' — Order Status: ' . $order->get_status() . ' — Order is paid: ' . $paid . '</p>';

        // Flag the action as done (to avoid repetitions on reload for example)
        $order->update_meta_data( '_horse_hours_updated', true );
        $order->save();
    }
}

/**
 * Add custom resource field at order detail at vendor 
 */
// display the extra data in the order admin panel
function kia_display_order_data_in_admin( $order ){
    global $WCFM, $wpdb, $_POST, $WCFMu;
    $resources_horse = array();
    $resources_instructor = array();
    $args = array(
        'posts_per_page'   => -1,
        'offset'           => 0,
        'category'         => '',
        'category_name'    => '',
        'bookingby'          => 'date',
        'booking'            => 'DESC',
        'include'          => '',
        'exclude'          => '',
        // 'meta_key'         => 'resouce_type',
        // 'meta_value'       => 'horse',
        'post_type'        => 'bookable_resource',
        'post_mime_type'   => '',
        'post_parent'      => '',
        //'author'	   => get_current_user_id(),
        'post_status'      => 'any',
        
        
        //'suppress_filters' => 0 
    );

    
    $args = apply_filters( 'get_booking_resources_args', $args );
    $wcfm_bookings_resources_array = get_posts( $args );

    if(!empty($wcfm_bookings_resources_array)) {
        foreach($wcfm_bookings_resources_array as $wcfm_bookings_resources_single) {
            $type = get_post_meta( $wcfm_bookings_resources_single->ID, 'resouce_type', true );
            if( $type == 'horse' ){
                //if meta value is horse
                $resources_horse[$wcfm_bookings_resources_single->ID] =   $wcfm_bookings_resources_single->post_title;
            }

            if( $type == 'instructor' ){
                //if meta value is instructor
            $resources_instructor[$wcfm_bookings_resources_single->ID] =   $wcfm_bookings_resources_single->post_title;
            }
        }
    }

    // $resources = array(
    //     1 => 'Horse 1',
    //     2 => 'Horse 2',
    //     3 => 'Horse 3',
    //     4 => 'Horse 4'
    // );

    //if it's already allocatd then don't show all other instructor and horse
    //get idea by getting post meta 
    $order_id = isset($order->id) ? $order->id : '';
    $resources_allocated = get_post_meta( $order_id, '_horse_hours_updated', true );
    if( $resources_allocated == 1 ){
        //get horse allocated
        $allocted_horse_id = get_post_meta( $order_id, '_horse_allocated', true );
        //get instructor allocated
        $allocted_instructor_id = get_post_meta( $order_id, '_instructor_allocated', true );
    }
    ?>
    <div class="order_data_column">
        <h4><?php _e( 'Resource Details' ); ?></h4>
        <div id="wcfm_resource_hours_status_update_wrapper" class="wcfm_resource_hours_status_update_wrapper">
            <p class="form-field form-field-wide wc-order-status">
                <label for="horse_id"><?php _e( 'Horse:', 'wc-frontend-manager' ) ?></label>
                <?php
                    if( $resources_allocated != 1 ) {
                    $resource_dropdown = '<select id="wcfm_horse_id" name="horse_id" style="padding: 8px 10px;">';
                    } else {
                    $resource_dropdown = '<select id="wcfm_horse_id" name="horse_id" style="padding: 8px 10px;" disabled>';
                    }
                    foreach ( $resources_horse as $resource => $resource_name ) {
                        //if already allocated then remove other ids
                        if( $resources_allocated == 1 && $resource != (int)$allocted_horse_id ) { continue; } 
                        $resource_dropdown .= '<option value="' . esc_attr( $resource ) . '" ' . selected( $resource, 'wc-' . $current_order_status, false ) . '>' . esc_html( $resource_name ) . '</option>';
                    }
                    $resource_dropdown .= '</select>';
                    echo $resource_dropdown;
                ?>
            </p>
            <p class="form-field form-field-wide wc-order-status">
                <label for="instructor_id"><?php _e( 'Instructor:', 'wc-frontend-manager' ) ?></label>
                <?php
                    if( $resources_allocated != 1 ) {
                    $resource_dropdown = '<select id="wcfm_instructor_id" name="instructor_id" style="padding: 8px 10px;">';
                    } else {
                    $resource_dropdown = '<select id="wcfm_instructor_id" name="instructor_id" style="padding: 8px 10px;" disabled>';
                    }
                    foreach ( $resources_instructor as $resource => $resource_name ) {
                        //if already allocated then remove other ids
                        if( $resources_allocated == 1 && $resource != (int)$allocted_instructor_id ) { unset($resources_horse[$resource]); } 
                        $resource_dropdown .= '<option value="' . esc_attr( $resource ) . '" ' . selected( $resource, 'wc-' . $current_order_status, false ) . '>' . esc_html( $resource_name ) . '</option>';
                    }
                    $resource_dropdown .= '</select>';
                    echo $resource_dropdown;
                ?>
            </p>
            
            <?php //do_action( 'wcfm_after_resource_hours_edit_block', $order_id ); ?>
            
            <div class="wcfm-message" tabindex="-1"></div>
        </div>
        <?php
            //echo '<p><strong>' . __( 'Some field' ) . ':</strong>' . get_post_meta( $order->id, '_some_field', true ) . '</p>';
            //echo '<p><strong>' . __( 'Another field' ) . ':</strong>' . get_post_meta( $order->id, '_another_field', true ) . '</p>'; ?>
    </div>
<?php }
//now resouce assignment is at per booking so commented
//add_action( 'woocommerce_admin_order_data_after_order_details', 'kia_display_order_data_in_admin' );

// add_action( 'wcfm_order_status_updated', 'action_on_order_status_completed', 20, 2 );
// function action_on_order_status_completed( $order_id, $order ){
//     $test = $_POST;
//     $o = $order;
//     $password    = wp_generate_password( 16, true );
//     // $user_name   = $user_email = $order->get_billing_email();
// }

/**
 * Loading script so we can handle click event and pass data to ajax
 */
function load_scripts_nls( $end_point ) {
    global $WCFM, $WCFMmp;
    switch( $end_point ) {
        case 'wcfm-orders-details':
            wp_enqueue_script( 'nls_wcfm_orders_details_js', get_stylesheet_directory_uri() . '/assets/js/wcfm-script-orders-details.js', array('jquery'), '1.0', true );
        break;

        case 'wcfm-bookings-resources-manage':
            wp_enqueue_script( 'nls_wcfmu_bookings_resources_manage_js', get_stylesheet_directory_uri() . '/assets/js/wcfmu-script-wcbookings-resources-manage.js', array('jquery'), '1.0', true );
        break;
    }
}
add_action( 'before_wcfm_load_scripts', 'load_scripts_nls');


 /**
   * Handle Order Details Status Update
   */
function wcfm_update_horse_hours() {
    global $WCFM;
    
    // if ( ! check_ajax_referer( 'wcfm_ajax_nonce', 'wcfm_ajax_nonce', false ) ) {
    //     echo '{"status": false, "message": "' . __( 'Invalid nonce! Refresh your page and try again.', 'wc-frontend-manager' ) . '"}';
    //     wp_die();
    // }
    
    $order_id = absint( $_POST['order_id'] );
    $horse_id = wc_clean( $_POST['horse'] );
    $instructor_id = wc_clean( $_POST['instructor'] );
    $order_status = wc_clean( $_POST['order_status'] );
    
    do_action( 'before_wcfm_horse_hours_update', $order_id, $order_status );
    
    //update hours here.
    //print_r($horse_id);

    if ( ! $order_id )
        return;

    //if order is not completed as status then only allow
    if( $order_status != 'wc-completed' ){
        return;
    }
    // Allow code execution only once 
    if( ! get_post_meta( $order_id, '_horse_hours_updated', true ) ) {

        // Get an instance of the WC_Order object
        $order = wc_get_order( $order_id );

        // Loop through order items
        foreach ( $order->get_items() as $item_id => $item ) {

            // Get the product object
            $product = $item->get_product();

            // Get the product Id
            $product_id = $product->get_id();
            
            //step1 : get hours assigned to product
            //if it's hours unit then make it minutes
            $hours_unit = get_post_meta( $product_id, '_wc_booking_duration_unit', true );
            $hours_allocated = get_post_meta( $product_id, '_wc_booking_duration', true );
            $minutes_allocated = 0;
            if( $hours_unit == 'hour' ){
                $minutes_allocated = hoursToMinutes($hours_allocated);
            }
            //step2: get remaining hours for horse
            $resouce_remaining_minutes = get_post_meta( $horse_id, 'horse_hours_remaining', true );

            //step3: minus the hours and if pending hours are less than assigned hours then don't update and then send error message
            if( $resouce_remaining_minutes < $minutes_allocated ){
                //return error and don't allocate the horse
                echo '{"status": false, "message": "' . __( 'Resouce hours are less than the hours need to allocate.', 'wc-frontend-manager' ) . '"}';
            }else{
                //update
                $final_minutes = $resouce_remaining_minutes - $minutes_allocated;
                update_post_meta( $horse_id, 'horse_hours_remaining', $final_minutes );
                //allocate the horse
                $order->update_meta_data( '_horse_allocated', $horse_id );
                //allocate the instructor
                $order->update_meta_data( '_instructor_allocated', $instructor_id );
                echo '{"status": true, "message": "' . __( 'Order status updated.', 'wc-frontend-manager' ) . '"}';
            }
        }

        // Output some data
        //echo '<p>Order ID: '. $order_id . ' — Order Status: ' . $order->get_status() . ' — Order is paid: ' . $paid . '</p>';

        // Flag the action as done (to avoid repetitions on reload for example)
        $order->update_meta_data( '_horse_hours_updated', true );
        $order->save();
    }

    // if ( wc_is_order_status( $order_status ) && $order_id ) {
    //       $order = wc_get_order( $order_id );
    //       $order->update_status( str_replace('wc-', '', $order_status), '', true );
          
    //       // Add Order Note for Log
    //       $user_id = apply_filters( 'wcfm_current_vendor_id', get_current_user_id() );
    //       $shop_name =  get_user_by( 'ID', $user_id )->display_name;
    //       if( wcfm_is_vendor() ) {
    //           $shop_name =  wcfm_get_vendor_store( absint($user_id) );
    //       }
    //       $wcfm_messages = sprintf( __( 'Order status updated to <b>%s</b> by <b>%s</b>', 'wc-frontend-manager' ), wc_get_order_status_name( str_replace('wc-', '', $order_status) ), $shop_name );
    //       $is_customer_note = apply_filters( 'wcfm_is_allow_order_update_note_for_customer', '1' );
          
    //       if( wcfm_is_vendor() ) add_filter( 'woocommerce_new_order_note_data', array( $WCFM->wcfm_marketplace, 'wcfm_update_comment_vendor' ), 10, 2 );
    //       $comment_id = $order->add_order_note( $wcfm_messages, $is_customer_note);
    //       if( wcfm_is_vendor() ) { add_comment_meta( $comment_id, '_vendor_id', $user_id ); }
    //       if( wcfm_is_vendor() ) remove_filter( 'woocommerce_new_order_note_data', array( $WCFM->wcfm_marketplace, 'wcfm_update_comment_vendor' ), 10, 2 );
          
    //       $wcfm_messages = sprintf( __( '<b>%s</b> order status updated to <b>%s</b> by <b>%s</b>', 'wc-frontend-manager' ), '#<a target="_blank" class="wcfm_dashboard_item_title" href="' . get_wcfm_view_order_url($order_id) . '">' . $order->get_order_number() . '</a>', wc_get_order_status_name( str_replace('wc-', '', $order_status) ), $shop_name );
    //       $WCFM->wcfm_notification->wcfm_send_direct_message( -2, 0, 1, 0, $wcfm_messages, 'status-update' );
          
    //       do_action( 'woocommerce_order_edit_status', $order_id, str_replace('wc-', '', $order_status) );
    //       do_action( 'wcfm_horse_hours_updated', $order_id, str_replace('wc-', '', $order_status) );
          
    //       if( defined('WCFM_REST_API_CALL') ) {
    //           return '{"status": true, "message": "' . __( 'Order status updated.', 'wc-frontend-manager' ) . '"}';
    //       }
          
    //       echo '{"status": true, "message": "' . __( 'Order status updated.', 'wc-frontend-manager' ) . '"}';
    //   }
      die;
    
}
add_action( 'wp_ajax_wcfm_update_horse_hours','wcfm_update_horse_hours' );


/**
 * When order is removed either by admin or vendor then revert back hours if order status is not completed then only
 */
add_action('before_delete_post', function($order_id) {
    $post_type = get_post_type($id);

    if ($post_type !== 'shop_order') {
        return;
    }

    //$order = new WC_Order($id);
    //do some stuff with order meta data
    // Get an instance of the WC_Order object
    $order = wc_get_order( $order_id );

    //if order is compoleted then return
    if ( $order->has_status('completed') ) {
        return;
    }

    //get horse id by order id
    $allocted_horse_id = get_post_meta( $order_id, '_horse_allocated', true );

    // Loop through order items
    foreach ( $order->get_items() as $item_id => $item ) {

        // Get the product object
        $product = $item->get_product();

        // Get the product Id
        $product_id = $product->get_id();
        
        //step1 : get hours assigned to product
        //if it's hours unit then make it minutes
        $hours_unit = get_post_meta( $product_id, '_wc_booking_duration_unit', true );
        $hours_allocated = get_post_meta( $product_id, '_wc_booking_duration', true );
        $minutes_allocated = 0;
        if( $hours_unit == 'hour' ){
            $minutes_allocated = hoursToMinutes($hours_allocated);
        }
        //step2: get remaining hours for horse
        $resouce_remaining_minutes = get_post_meta( $allocted_horse_id, 'horse_hours_remaining', true );

        //step3: plus the hours
        $final_minutes = $resouce_remaining_minutes + $minutes_allocated;
        update_post_meta( $allocted_horse_id, 'horse_hours_remaining', $final_minutes );

        //now remove 3 things to remove data we created from order
        $ourData = array(
            '_horse_allocated',
            '_instructor_allocated',
            '_horse_hours_updated'
        );
        foreach($ourData as $key ){
            delete_post_meta($order_id, $key);
        }

    }

}, 10, 1);

/**
 * If post deleted by admin
 */
// disable delete entirely
function restrict_post_deletion($post_ID){
    $type = get_post_type($post_ID);
    if($type == 'shop_order'){
        echo "You are not authorized to delete this page.";
        exit;
    }
}
// add_action('wp_trash_post', 'restrict_post_deletion', 10, 1);
// add_action('before_delete_post', 'restrict_post_deletion', 10, 1);

/**
 * If at customer side booking listing if customer cancle booking then handle with thig action
 */
/**
 * Cancel a booking.
 */
function cancel_booking_nls() {

    if ( isset( $_GET['cancel_booking'] ) && isset( $_GET['booking_id'] ) ) {

        $booking_id         = absint( $_GET['booking_id'] );
        $booking            = get_wc_booking( $booking_id );
        $booking_can_cancel = $booking->has_status( get_wc_booking_statuses( 'cancel' ) );
        $booking_after_twentyfour_hours = false;
        $redirect           = $_GET['redirect'];
        $booking_date_timestamp = $booking->get_date_created('view');
        $booking_date_normal = date("d-m-Y h:i:s",$booking_date_timestamp);
        // $after_day_date = date('d-m-Y h:i:s', strtotime("+1 day", strtotime($booking_date_normal)));
        // $after_day_date_timestamp = strtotime($after_day_date);
        $after_day_date_timestamp = strtotime('+1 day', $booking_date_timestamp);

        //get current strtotime
        $current_timestamp = strtotime("now");
        //compare if current is greather or equal to after one day strtotime
        if ($current_timestamp > $after_day_date_timestamp){
            $booking_after_twentyfour_hours = true;
        }
        
        if ( $booking->has_status( 'cancelled' ) ) {
            // Already cancelled - take no action
        } elseif ( $booking_after_twentyfour_hours == false && $booking_can_cancel && $booking->get_id() == $booking_id && isset( $_GET['_wpnonce'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'woocommerce-bookings-cancel_booking' ) ) {
            
            // Cancel the booking
            //TODO::Temporary disabled
            // $booking->update_status( 'cancelled' );
            // WC_Cache_Helper::get_transient_version( 'bookings', true );

            // Message
            //wc_add_notice( apply_filters( 'woocommerce_booking_cancelled_notice', __( 'Your booking was cancelled.', 'woocommerce-bookings' ) ), apply_filters( 'woocommerce_booking_cancelled_notice_type', 'notice' ) );

            //do_action( 'woocommerce_bookings_cancelled_booking', $booking->get_id() );
            
            /**
             * Custom logic start
             */
            //step 1: get resouces allocated to booking
            $selectedHorsesForBooking = get_post_meta( $booking_id, 'allocated_horse_ids', true );
            if( !empty($selectedHorsesForBooking) ){
            
                //step 2: Get hourse for each resouces and revert back to resouces (horse)
                foreach ($selectedHorsesForBooking as $resource_id) {
                    //get total time allocation by vendor
                    $totalHorseHours = getHorseRemainingHours($resource_id);
                    $totalHorseHours = convertToDecimal($totalHorseHours);
                    $totalHorseMinutes = hoursToMinutes($totalHorseHours);
                    
                    //assigned time to this resource for booking product by meta key: _wc_booking_duration
                    $associatedProductId = getProductIdAssociatedByBookingId($booking_id);
                    $assignedHoursForHorse = getBookingHorseAssignedHours($associatedProductId);
                    $assignedHoursForHorse = convertToDecimal($assignedHoursForHorse);
                    $assignedHorseMinutes = hoursToMinutes($assignedHoursForHorse);
                    
                    //update the time we created in new 
                    $finalMinutes = $totalHorseMinutes + $assignedHorseMinutes;
                    update_post_meta( $resource_id, 'horse_hours_remaining', $finalMinutes );

                    //step 3: remove everything we added to booking
                    delete_post_meta($booking_id, 'allocated_horse_ids');
                    delete_post_meta($booking_id, 'allocated_instrutor_ids');
                                        
                }
                
            }            
             /**
              * Custom logic end
              */
            // Cancel the booking
            $booking->update_status( 'cancelled' );
            WC_Cache_Helper::get_transient_version( 'bookings', true );

            // Message
            wc_add_notice( apply_filters( 'woocommerce_booking_cancelled_notice', __( 'Your booking was cancelled.', 'woocommerce-bookings' ) ), apply_filters( 'woocommerce_booking_cancelled_notice_type', 'notice' ) );

            do_action( 'woocommerce_bookings_cancelled_booking', $booking->get_id() );
        } elseif ( ! $booking_can_cancel ) {
            wc_add_notice( __( 'Your booking can no longer be cancelled. Please contact us if you need assistance.', 'woocommerce-bookings' ), 'error' );
        } elseif ( booking_after_twentyfour_hours == true ) {
            wc_add_notice( __( 'Timeframe of 24 hours for the cancellation request has expired.', 'woocommerce-bookings' ), 'error' );
        } else {
            wc_add_notice( __( 'Invalid booking.', 'woocommerce-bookings' ), 'error' );
        }

        if ( $redirect ) {
            wp_safe_redirect( $redirect );
            exit;
        }
    }
}
add_action( 'init', 'cancel_booking_nls', 22 );

function allow_complete_status_tobe_cancled( $status = array() ){
    $status['complete'] = __( 'Complete', 'woocommerce-bookings' );
    return $status;
}
add_filter( 'woocommerce_valid_booking_statuses_for_cancel', 'allow_complete_status_tobe_cancled', 10, 1);