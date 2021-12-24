<?php
/**
 * The template for displaying product content within loops
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 3.6.0
 */

defined( 'ABSPATH' ) || exit;

global $product, $opt_name;

if( isset( $opt_name['enable_lazyloader'] ) ):
	$is_lazyloader = $opt_name['enable_lazyloader'];
else:
	$is_lazyloader = true;
endif;

if( $is_lazyloader == true ):
	$lazy_class = 'smartify';
	$lazy_attr = 'sm-';
else:
	$lazy_class = '';
	$lazy_attr = '';
endif;

// Ensure visibility.
if ( empty( $product ) || ! $product->is_visible() ) {
	return;
}

/**
 * Control product visiblity based on grade system.
 */
//get rid of virtual products
if( $product-> is_virtual('yes') ){
    return;
}

if( is_user_logged_in() && ! is_admin() ){

    //if no orders then show only assestment product
    global $product, $WCFM;
        $id = $product->get_id();
        $vendor_id = $WCFM->wcfm_vendor_support->wcfm_get_vendor_id_from_product( $id );
    $customer_orders = getLoggedInCustomerOrders( $vendor_id );
    if( empty($customer_orders) ){
        global $product, $WCFM;
        $id = $product->get_id();
        echo $isAssesmentProduct = getAssestmentProductByDependency( $product );
        if( $isAssesmentProduct == false ){
            return;
        }
        //this is changed now as per call on 28102021
        // $vendor_id = $WCFM->wcfm_vendor_support->wcfm_get_vendor_id_from_product( $id );
        // $assesmentProducts = getAssestmentProductByVendorId( $vendor_id );
        // if( !in_array($id,$assesmentProducts)){
        //  return;
        // }
    }else{

        
        //else show based on grade level
        //extra get meta
        //get product meta and return based on meta and user grade level
        $dependent_ids = get_post_meta( $product->id, '_tied_products', true );
        if ( WC_PD_Core_Compatibility::is_wc_version_gte_2_7() ) {
            $dependent_ids = $product->get_meta( '_tied_products', true );
        } else {
            $dependent_ids = (array) get_post_meta( $product->id, '_tied_products', true );
        }

        $dependent_ids = empty( $dependent_ids ) ? array() : array_unique( $dependent_ids );
        // print_r($product->get_id());
        $customerOrders = getLoggedInCustomerOrders( $vendor_id );
        if( empty($dependent_ids) ){
            return;
        }
        //print_r($dependent_ids);

        $customerGrades = getCustomerGradeBasedOnOrders( $vendor_id );
        
        $result = array_intersect($customerGrades, $dependent_ids);
        if( empty($result) ){
            return;
        }
    }

}
/**
 * End
 */
?>
<li <?php wc_product_class( '', $product ); ?> >
    <div class="single-products">
        <?php
        /**
         * Hook: woocommerce_before_shop_loop_item.
         *
         * @hooked woocommerce_template_loop_product_link_open - 10
         */
        do_action( 'woocommerce_before_shop_loop_item' );

        /**
         * Hook: woocommerce_before_shop_loop_item_title.
         *
         * @hooked woocommerce_show_product_loop_sale_flash - 10
         * @hooked woocommerce_template_loop_product_thumbnail - 10
         */

        remove_action('woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10);
        remove_action('woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10);
        remove_action('woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10);
        remove_action('woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5);
        remove_action('woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10);
        remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10);
        ?>
        

        <div class="products-image">
            <?php //woocommerce_template_loop_product_thumbnail(); ?>
            <img class="<?php echo esc_attr($lazy_class); ?>" <?php echo esc_attr($lazy_attr); ?>src="<?php the_post_thumbnail_url('thumbnail_image_width') ?>" alt="<?php echo esc_attr__('product','jasy'); ?>">
            <ul class="product-btn-hover">
                <li class="product-link">
                    <a href="#" data-toggle="modal" data-target="#productsModalCenter<?php echo esc_attr(get_the_ID(),'jasy');?>">
                        <i class="fa fa-eye"></i>
                    </a>
                </li>
                <li><a href="<?php the_permalink(); ?>"><i class="fa fa-link" aria-hidden="true"></i></a></li>
            </ul>
        </div>

        <div class="products-content">
            <h3><a href="<?php the_permalink() ?>"><?php the_title() ?></a></h3>
            <?php woocommerce_template_loop_price(); ?>
            <?php 
            $rating_count = $product->get_rating_count();
            if($product->get_rating_count() == 0){
            ?>
            <ul>
                <li><i class="fa fa-star-o"></i></li>
                <li><i class="fa fa-star-o"></i></li>
                <li><i class="fa fa-star-o"></i></li>
                <li><i class="fa fa-star-o"></i></li>
                <li><i class="fa fa-star-o"></i></li>
            </ul>
            <?php
            }else{
                woocommerce_template_loop_rating(); 
            }
            ?>
            <?php woocommerce_template_loop_add_to_cart(); ?>
        </div>
        <?php woocommerce_show_product_sale_flash(); ?>
        <?php
        do_action( 'woocommerce_before_shop_loop_item_title' );

        /**
         * Hook: woocommerce_shop_loop_item_title.
         *
         * @hooked woocommerce_template_loop_product_title - 10
         */
        

        do_action( 'woocommerce_shop_loop_item_title' );

        /**
         * Hook: woocommerce_after_shop_loop_item_title.
         *
         * @hooked woocommerce_template_loop_rating - 5
         * @hooked woocommerce_template_loop_price - 10
         */
        do_action( 'woocommerce_after_shop_loop_item_title' );

        /**
         * Hook: woocommerce_after_shop_loop_item.
         *
         * @hooked woocommerce_template_loop_product_link_close - 5
         * @hooked woocommerce_template_loop_add_to_cart - 10
         */

        do_action( 'woocommerce_after_shop_loop_item' );
        ?>
    </div>
</li>


