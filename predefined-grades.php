<?php
/*
* Creating a function to create our CPT
*/
 
function nls_grades_custom_post_type() { 
// Set UI labels for Custom Post Type
    $labels = array(
        'name'                => _x( 'Grades', 'Post Type General Name', 'twentytwenty' ),
        'singular_name'       => _x( 'Grade', 'Post Type Singular Name', 'twentytwenty' ),
        'menu_name'           => __( 'Grades', 'twentytwenty' ),
        'parent_item_colon'   => __( 'Parent Grade', 'twentytwenty' ),
        'all_items'           => __( 'All Grades', 'twentytwenty' ),
        'view_item'           => __( 'View Grade', 'twentytwenty' ),
        'add_new_item'        => __( 'Add New Grade', 'twentytwenty' ),
        'add_new'             => __( 'Add New', 'twentytwenty' ),
        'edit_item'           => __( 'Edit Grade', 'twentytwenty' ),
        'update_item'         => __( 'Update Grade', 'twentytwenty' ),
        'search_items'        => __( 'Search Grade', 'twentytwenty' ),
        'not_found'           => __( 'Not Found', 'twentytwenty' ),
        'not_found_in_trash'  => __( 'Not found in Trash', 'twentytwenty' ),
    );
        
// Set other options for Custom Post Type
        
    $args = array(
        'label'               => __( 'grades', 'twentytwenty' ),
        'description'         => __( 'Grade', 'twentytwenty' ),
        'labels'              => $labels,
        // Features this CPT supports in Post Editor
        'supports'            => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'custom-fields' ),
        // You can associate this CPT with a taxonomy or custom taxonomy. 
        //'taxonomies'          => array( 'genres' ),
        /* A hierarchical CPT is like Pages and can have
        * Parent and child items. A non-hierarchical CPT
        * is like Posts.
        */ 
        'hierarchical'        => false,
        'public'              => false,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'show_in_nav_menus'   => false,
        'show_in_admin_bar'   => true,
        'menu_position'       => 56,
        'can_export'          => true,
        'has_archive'         => false,
        'exclude_from_search' => true,
        'publicly_queryable'  => false,
        'capability_type'     => 'post',
        'show_in_rest' => false,
        'menu_icon' => 'dashicons-welcome-learn-more',
    
    );
        
    // Registering your Custom Post Type
    register_post_type( 'grades', $args );
    
}
    
/* Hook into the 'init' action so that the function
* Containing our post type registration is not 
* unnecessarily executed. 
*/
    
add_action( 'init', 'nls_grades_custom_post_type', 0 );

/**
 * Meta box functionality
 */
add_action( 'add_meta_boxes_grades', 'meta_box_for_grades' );
function meta_box_for_grades( $post ){
    add_meta_box(
        'grade_level', // $id
        'Grade Level', // $title
        'grade_level_callback', // $callback
        'grades', // $screen
        'normal', // $context
        'high' // $priority
    );
}
function grade_level_callback() {
    global $post;
    $level_grade = get_post_meta( $post->ID, 'level_for_grade', true ); ?>
        <input type="hidden" name="grade_level_nonce" value="<?php echo wp_create_nonce( basename(__FILE__) ); ?>">

        <!-- All fields will go here -->
        <p>
            <!-- <label for="your_fields[text]">Define Level</label>
            <br> -->
            <input type="text" name="level_for_grade" id="level_for_grade" class="regular-text1" value="<?php echo $level_grade; ?>">
        </p>
        
<?php }

function save_grade_level_cb( $post_id ) {
    // verify nonce
    if ( !wp_verify_nonce( $_POST['grade_level_nonce'], basename(__FILE__) ) ) {
        return $post_id;
    }
    // check autosave
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return $post_id;
    }
    // check permissions
    if ( 'grades' === $_POST['post_type'] ) {
        if ( !current_user_can( 'edit_page', $post_id ) ) {
            return $post_id;
        } elseif ( !current_user_can( 'edit_post', $post_id ) ) {
            return $post_id;
        }
    }

    $old = get_post_meta( $post_id, 'level_for_grade', true );
    $new = $_POST['level_for_grade'];
    
    update_post_meta( $post_id, 'level_for_grade', $new );

    // if ( $new && $new !== $old ) {
    //     update_post_meta( $post_id, 'level_for_grade', $new );
    // } elseif ( '' === $new && $old ) {
    //     delete_post_meta( $post_id, 'level_for_grade', $old );
    // }
}
add_action( 'save_post', 'save_grade_level_cb' );



/**
 * Showing data in column
 */
function grades_cpt_columns($cols) {
	//Remove default columns
	unset(
		$cols['date']
	);
	
	//Rename existing column
	//$cols['author'] = __('Writer', 'text_domain');
	
	//Add custom columns
	$new_columns = array(
		'level' => __('Level'),
		'date' => __('Date'),
	);
    //$cols['author'] = __('Writer', 'text_domain');
	
    return array_merge($cols, $new_columns);
}
add_filter('manage_grades_posts_columns' , 'grades_cpt_columns');

function level_admin_columns_values ( $column, $post_id ) {
    switch ( $column ) {
      case 'level':
        echo get_post_meta ( $post_id, 'level_for_grade', true );
        break;	 
    }
  }
  add_action ( 'manage_grades_posts_custom_column', 'level_admin_columns_values', 10, 2 );
 //10 is priority and 2 is number of argument passed.


 /**
  * When vendor approved then add product related to vendor
  */
  function add_predefined_grades_cb( $member_id, $wcfmmp_settings ){
    //get all grades added by Admin
    $args = array(  
        'post_type' => 'grades',
        'post_status' => 'publish',
        'posts_per_page' => -1, 
        'orderby' => 'title', 
        'order' => 'ASC', 
    );
    $loop = new WP_Query( $args );     
    while ( $loop->have_posts() ) : $loop->the_post(); 
        //print the_title(); 
        //the_excerpt(); 
        
        //assign each grade with level to new vendor
        $product_title = get_the_title();
        $product_status = 'publish';
        $product_excerpt = get_the_excerpt();
        $product_description = get_the_content();
        $product_vendor = $member_id;
        $grading_level = get_post_meta( get_the_ID(), 'level_for_grade', true );
        $new_product = apply_filters( 'wcfm_product_content_before_save', 
                array(
                    'post_title'   => wc_clean( $product_title ),
                    'post_status'  => $product_status,
                    'post_type'    => 'product',
                    'post_excerpt' => sanitize_option( 'wcfm_editor_content', apply_filters( 'wcfm_editor_content_before_save', stripslashes( html_entity_decode( $product_excerpt, ENT_QUOTES, 'UTF-8' ) ) ) ),
                    'post_content' => sanitize_option( 'wcfm_editor_content', apply_filters( 'wcfm_editor_content_before_save', stripslashes( html_entity_decode( $product_description, ENT_QUOTES, 'UTF-8' ) ) ) ),
                    'post_author'  => $product_vendor,
                    'post_name'    => sanitize_title($product_title)
                ), $product_title 
            );
            
            $new_product_id = wp_insert_post( $new_product, true );

            // Product Real Author
            update_post_meta( $new_product_id, '_wcfm_product_author', $product_vendor );
            
            if(!is_wp_error($new_product_id)) {
                $wcfm_products_manage_form_data['sku'] = '';
                wp_set_object_terms( $new_product_id, 'simple', 'product_type' );

                $product      = new WC_Product_Simple( $new_product_id );

                $wcfm_product_data_factory = apply_filters( 'wcfm_product_data_factory', array(
                    'virtual'            => true,
                    'sku'                => null,
                    'tax_status'         => null,
                    'tax_class'          => null,
                    'weight'             => null,
                    'length'             => null,
                    'width'              => null,
                    'height'             => null,
                    'shipping_class_id'  => null,
                    'sold_individually'  => '',
                    'upsell_ids'         => array(),
                    'cross_sell_ids'     => array(),
                    'regular_price'      => '',
                    'sale_price'         => '',
                    'date_on_sale_from'  => '',
                    'date_on_sale_to'    => '',
                    'manage_stock'       => '',
                    'backorders'         => '',
                    'stock_status'       => '',
                    'stock_quantity'     => '',
                    'product_url'        => '',
                    'button_text'        => '',
                    'children'           => null,
                    'downloadable'       => '',
                    'download_limit'     => '',
                    'download_expiry'    => '',
                    'downloads'          => '',
                    'attributes'         => '',
                    'default_attributes' => '',
                    'reviews_allowed'    => true,
                ), $new_product_id, $product, $wcfm_products_manage_form_data );

                $errors       = $product->set_props( $wcfm_product_data_factory );
                // if ( is_wp_error( $errors ) ) {
                //     $has_error = true;
                // }
                $product->save();

                wp_delete_object_term_relationships( $new_product_id, 'product_cat' );
                wp_delete_object_term_relationships( $new_product_id, 'product_tag' );
                update_post_meta( $new_product_id, '_product_image_gallery', '' );

                //update grading level
                update_post_meta( $new_product_id, 'grading_level', $grading_level );
                
                $wcfm_policy_product_options = array();
                $wcfm_policy_product_options[0] = '';
                $wcfm_policy_product_options['refund_policy'] = '';
                $wcfm_policy_product_options['cancellation_policy'] = '';
                update_post_meta( $new_product_id, 'wcfm_policy_product_options', $wcfm_policy_product_options );

                // Clear cache and transients
                wc_delete_product_transients( $new_product_id );

        }

    endwhile;

    wp_reset_postdata();        
  }
  add_action( 'wcfmmp_new_store_created', 'add_predefined_grades_cb', 10, 2 );
