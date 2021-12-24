jQuery(document).ready(function($) {
    $('.wcfm-select').select2();
    // jQuery('#wcfm_modify_order_status').click(function(event) {
	// 	event.preventDefault();
    //     setTimeout(function() {
    //         modifyWCFMOrderStatusNLS();
    //       }, 2000);
	// 	return false;
	// });
});

function modifyWCFMOrderStatusNLS() {
    // jQuery('#orders_details_general_expander').block({
    //     message: null,
    //     overlayCSS: {
    //         background: '#fff',
    //         opacity: 0.6
    //     }
    // });
    var data = {
        action       : 'wcfm_update_horse_hours',
        horse : jQuery('#wcfm_horse_id').val(),
        instructor : jQuery('#wcfm_instructor_id').val(),
        order_id     : jQuery('#wcfm_modify_order_status').data('orderid'),
        order_status     : jQuery('#wcfm_order_status').val(),
        wcfm_ajax_nonce : wcfm_params.wcfm_ajax_nonce
    }	
    jQuery.ajax({
        type:		'POST',
        url: wcfm_params.ajax_url,
        data: data,
        success:	function(response) {
            debugger;
            $response_json = jQuery.parseJSON(response);
            jQuery('.wcfm-message').html('').removeClass('wcfm-error').removeClass('wcfm-success').slideUp();
            if($response_json.status) {
                // wcfm_notification_sound.play();
                // jQuery('#wcfm_order_status_update_wrapper .wcfm-message').html('<span class="wcicon-status-completed"></span>' + $response_json.message).addClass('wcfm-success').slideDown( "slow" );
            } else {
                //wcfm_notification_sound.play();
                setTimeout(function() {
                    jQuery('#wcfm_order_status_update_wrapper .wcfm-message').html('').html('<span class="wcicon-status-cancelled"></span>' + $response_json.message).addClass('wcfm-error').slideDown( "slow" );
                  }, 2000);
            }
            jQuery('#orders_details_general_expander').unblock();
        }
    });
}