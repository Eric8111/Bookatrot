<?php

/**
 * Display Fields
 */
function wooc_extra_register_fields() {?>
    <p class="form-row form-row-first">
        <label for="reg_billing_first_name"><?php _e( 'First name', 'woocommerce' ); ?><span class="required">*</span></label>
        <input type="text" class="input-text woocommerce-Input woocommerce-Input--text input-text form-control" name="billing_first_name" id="reg_billing_first_name" value="<?php if ( ! empty( $_POST['billing_first_name'] ) ) esc_attr_e( $_POST['billing_first_name'] ); ?>" />
    </p>
    <p class="form-row form-row-last">
        <label for="reg_billing_last_name"><?php _e( 'Last name', 'woocommerce' ); ?><span class="required">*</span></label>
        <input type="text" class="input-text woocommerce-Input woocommerce-Input--text input-text form-control" name="billing_last_name" id="reg_billing_last_name" value="<?php if ( ! empty( $_POST['billing_last_name'] ) ) esc_attr_e( $_POST['billing_last_name'] ); ?>" />
    </p>
    <div class="clear"></div>
    <?php
}
add_action( 'woocommerce_register_form_start', 'wooc_extra_register_fields' );


function wooc_extra_register_fields2() {?>
    <p class="form-row form-row-full">
        <label for="reg_date_of_birth"><?php _e( 'Date of birth', 'woocommerce' ); ?></label>
        <input name="date_of_birth" id="reg_date_of_birth" type="text" class="input-text woocommerce-Input woocommerce-Input--text input-text form-control" value="<?php if ( ! empty( $_POST['date_of_birth'] ) ) esc_attr_e( $_POST['date_of_birth'] ); ?>" readonly/>
    </p>
    <p class="form-row form-row-full">
        <label for="reg_address"><?php _e( 'Address', 'woocommerce' ); ?></label>
        <textarea name="address" id="reg_address" class="input-text woocommerce-Input woocommerce-Input--text input-text form-control"><?php if ( ! empty( $_POST['address'] ) ) esc_attr_e( $_POST['address'] ); ?></textarea>
    </p>
    <div class="clear"></div>
    
    <p class="form-row form-row-first">
        <label for="reg_height"><?php _e( 'Height', 'woocommerce' ); ?></label>
        <input type="text" class="input-text woocommerce-Input woocommerce-Input--text input-text form-control" name="height" id="reg_height" value="<?php if ( ! empty( $_POST['height'] ) ) esc_attr_e( $_POST['height'] ); ?>" />
    </p>
    <p class="form-row form-row-last">
        <label for="reg_weight"><?php _e( 'Weight', 'woocommerce' ); ?></label>
        <input type="text" class="input-text woocommerce-Input woocommerce-Input--text input-text form-control" name="weight" id="reg_weight" value="<?php if ( ! empty( $_POST['weight'] ) ) esc_attr_e( $_POST['weight'] ); ?>" />
    </p>
    <div class="clear"></div>

    <p class="form-row form-row-full">
        <label for="emergency_contact"><?php _e( 'Emergency contact details', 'woocommerce' ); ?><span class="required">*</span></label>
        <textarea name="emergency_contact" id="emergency_contact" class="input-text woocommerce-Input woocommerce-Input--text input-text form-control"><?php if ( ! empty( $_POST['emergency_contact'] ) ) esc_attr_e( $_POST['emergency_contact'] ); ?></textarea>
    </p>
    <div class="clear"></div>

    <?php
}
add_action( 'woocommerce_register_form', 'wooc_extra_register_fields2' );

/**
* register fields Validating.
*/
function wooc_validate_extra_register_fields( $username, $email, $validation_errors ) {
    if ( isset( $_POST['billing_first_name'] ) && empty( $_POST['billing_first_name'] ) ) {
           $validation_errors->add( 'billing_first_name_error', __( '<strong>Error</strong>: First name is required!', 'woocommerce' ) );
    }
    if ( isset( $_POST['billing_last_name'] ) && empty( $_POST['billing_last_name'] ) ) {
           $validation_errors->add( 'billing_last_name_error', __( '<strong>Error</strong>: Last name is required!.', 'woocommerce' ) );
    }

    // if ( isset( $_POST['date_of_birth'] ) && empty( $_POST['date_of_birth'] ) ) {
    //        $validation_errors->add( 'date_of_birth_error', __( '<strong>Error</strong>: Date of birth is required!', 'woocommerce' ) );
    // }

    // if ( isset( $_POST['address'] ) && empty( $_POST['address'] ) ) {
    //     $validation_errors->add( 'address_error', __( '<strong>Error</strong>: Address is required!', 'woocommerce' ) );
    // }

    // if ( isset( $_POST['height'] ) && empty( $_POST['height'] ) ) {
    //     $validation_errors->add( 'height_error', __( '<strong>Error</strong>: Height is required!', 'woocommerce' ) );
    // }

    // if ( isset( $_POST['weight'] ) && empty( $_POST['weight'] ) ) {
    //     $validation_errors->add( 'weight_error', __( '<strong>Error</strong>: Weight is required!', 'woocommerce' ) );
    // }

    if ( isset( $_POST['emergency_contact'] ) && empty( $_POST['emergency_contact'] ) ) {
        $validation_errors->add( 'emergency_contact_error', __( '<strong>Error</strong>: Emergency contact is required!', 'woocommerce' ) );
    }

    
    
    return $validation_errors;
}
add_action( 'woocommerce_register_post', 'wooc_validate_extra_register_fields', 10, 3 );


/**
* Below code save extra fields.
*/
function wooc_save_extra_register_fields( $customer_id ) {
 
    
      if ( isset( $_POST['billing_first_name'] ) ) {
             //First name field which is by default
             update_user_meta( $customer_id, 'first_name', sanitize_text_field( $_POST['billing_first_name'] ) );
             // First name field which is used in WooCommerce
             update_user_meta( $customer_id, 'billing_first_name', sanitize_text_field( $_POST['billing_first_name'] ) );
      }
      if ( isset( $_POST['billing_last_name'] ) ) {
             // Last name field which is by default
             update_user_meta( $customer_id, 'last_name', sanitize_text_field( $_POST['billing_last_name'] ) );
             // Last name field which is used in WooCommerce
             update_user_meta( $customer_id, 'billing_last_name', sanitize_text_field( $_POST['billing_last_name'] ) );
      }

      if ( isset( $_POST['date_of_birth'] ) ) {
             //First name field which is by default
             update_user_meta( $customer_id, 'date_of_birth', sanitize_text_field( $_POST['date_of_birth'] ) );
      }

      if ( isset( $_POST['address'] ) ) {
             //First name field which is by default
             update_user_meta( $customer_id, 'address', sanitize_text_field( $_POST['address'] ) );
      }

      if ( isset( $_POST['height'] ) && isset( $_POST['weight'] ) ) {
             update_user_meta( $customer_id, 'height', sanitize_text_field( $_POST['height'] ) );
             update_user_meta( $customer_id, 'weight', sanitize_text_field( $_POST['weight'] ) );
      }

      if ( isset( $_POST['emergency_contact'] ) ) {
             //First name field which is by default
             update_user_meta( $customer_id, 'emergency_contact', sanitize_text_field( $_POST['emergency_contact'] ) );
      }
      /**
       * Save extra options
       */
}
add_action( 'woocommerce_created_customer', 'wooc_save_extra_register_fields' );

function nls_dob_datepicker_support( ){
    wp_enqueue_script( 'frontend_script',  get_stylesheet_directory_uri() . '/assets/js/frontend_script.js', array('jquery'), true );
    // wp_localize_script( 'frontend_script', 'frontend', 
    //     array( 
    //         'ajaxurl' => admin_url( 'admin-ajax.php' ),
    //         'site_url' => get_site_url(),
    //         'assets_url' => get_template_directory_uri()
    //     )
    // );
}
add_filter( 'wp_enqueue_scripts', 'nls_dob_datepicker_support', PHP_INT_MAX );

function nls_dob_datepicker_support_backend( ){
    wp_enqueue_script( 'backend_script',  get_stylesheet_directory_uri() . '/assets/js/backend_script.js', array('jquery'), true );
}
add_filter( 'admin_enqueue_scripts', 'nls_dob_datepicker_support_backend', PHP_INT_MAX );


/**
 * Show and update these fields at user listing 0
 */
function mk_custom_user_profile_fields( $user )
{
    echo '<h3 class="heading">Customer Extra Info:</h3>';
    ?>    
    <table class="form-table">
        <tr>
            <th><label for="date_of_birth">Date of birth</label></th>
            <td>
                <input name="date_of_birth" id="customer_date_of_birth" type="text" class="input-text form-control"
                value="<?php echo esc_attr( get_the_author_meta( 'date_of_birth', $user->ID ) ); ?>" readonly />
            </td>
        </tr>
        <tr>
            <th><label for="address">Address</label></th>
            <td>
                <textarea name="address" id="address" class="input-text form-control"><?php echo esc_attr( get_the_author_meta( 'address', $user->ID ) ); ?></textarea>
            </td>
        </tr>
        <tr>
            <th><label for="height">Height</label></th>
            <td>
                <input type="text" class="input-text form-control" name="height" id="height" value="<?php echo esc_attr( get_the_author_meta( 'height', $user->ID ) ); ?>" />
            </td>
        </tr>
        <tr>
            <th><label for="weight">Weight</label></th>
            <td>
                <input type="text" class="input-text form-control" name="weight" id="weight" value="<?php echo esc_attr( get_the_author_meta( 'weight', $user->ID ) ); ?>" />
            </td>
        </tr>
        <tr>
            <th><label for="weight">Emergency contact details</label></th>
            <td>
                <textarea name="emergency_contact" id="emergency_contact" class="input-text form-control"><?php echo esc_attr( get_the_author_meta( 'emergency_contact', $user->ID ) ); ?></textarea>
            </td>
        </tr>
    </table>
    <?php
}
add_action( 'edit_user_profile', 'mk_custom_user_profile_fields' );

/**
 * When user profile updated
 */
function save_mk_extra_author_fields( $user_id ) {

    // Check to see if user can edit this profile
    if ( ! current_user_can( 'edit_user', $user_id ) )
        return false;
        
    if( isset($_POST['date_of_birth']) && $_POST['date_of_birth'] != '' ){
        update_user_meta( $user_id, 'date_of_birth', $_POST['date_of_birth'] );
    }

    if( isset($_POST['address']) && $_POST['address'] != '' ){
        update_user_meta( $user_id, 'address', $_POST['address'] );
    }

    if( isset($_POST['height']) && $_POST['height'] != '' ){
        update_user_meta( $user_id, 'height', $_POST['height'] );
    }

    if( isset($_POST['weight']) && $_POST['weight'] != '' ){
        update_user_meta( $user_id, 'weight', $_POST['weight'] );
    }

    if( isset($_POST['emergency_contact']) && $_POST['emergency_contact'] != '' ){
        update_user_meta( $user_id, 'emergency_contact', $_POST['emergency_contact'] );
    }
    
}

add_action( 'personal_options_update', 'save_mk_extra_author_fields' );
add_action( 'edit_user_profile_update', 'save_mk_extra_author_fields' );