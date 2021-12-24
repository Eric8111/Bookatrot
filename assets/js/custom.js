jQuery(document).on('ready', function () {
    setTimeout(function(){

        //change dropdown
        //var hash = new URL(document.URL).hash;
        let long_name = new URL(document.URL).hash;
        long_name = decodeURI(long_name);
        long_name = long_name.replace( /#/, "" );
        if( long_name != ''){
            let $element = jQuery('#wcfmsc_store_categories');
            let val = $element.find("option:contains('"+long_name+"')").val()
            $element.val(val).trigger('change');    

            // document.URL refers to the current url
            //var hash = new URL(document.URL).hash;
            //console.log(hash);
            var url_ob = new URL(document.URL);
            //url_ob.hash = '#'+long_name;
            url_ob.hash = '#';
            var new_url = url_ob.href;
            document.location.href = new_url;
        }

    }, 1000);
    
});