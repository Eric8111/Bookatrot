jQuery(document).ready(function($) {
    changeType();

    $("body").on('change', '#resouce_type', function(){    // 2nd (B)
        changeType();
    });
})

function changeType(){
    let selectedVal = jQuery('#resouce_type').val();
    if( selectedVal == 'horse' ){
        jQuery('#horse_hours').show();
    }else{
        jQuery('#horse_hours').hide();
    }
}