jQuery(document).on('ready', function () {
    if( jQuery( "#reg_date_of_birth" ).length == 1 ){
        jQuery( "#reg_date_of_birth" ).datepicker();    
    }

    jQuery(".semental-mobile-nav li.login-icon a").html("Sing Up");
    jQuery(".semental-mobile-nav li.logout-btn a").html("Logout");
    
});