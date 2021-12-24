<?php
   /*
   Template Name: Hire A Carriage Template
   */
  defined( 'ABSPATH' ) || exit;
  get_header( 'shop' );

  global $opt_name;

  remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );
  do_action( 'woocommerce_before_main_content' );

  if ( isset( $opt_name['product_bg_image'] ) ): 
    $bg_img             = $opt_name['product_bg_image']['url'];
  else:
    $bg_img             = '';
  endif;

  $jasy_product_layout = !empty($opt_name['jasy_product_layout']) ? $opt_name['jasy_product_layout'] : 'container';
if ( !empty($_GET['jasy_product_layout']) ) {
    $jasy_product_layout = $_GET['jasy_product_layout'];
}

if( isset($opt_name['page_title_tag']) ):
    $tag = $opt_name['page_title_tag'];
else:
    $tag = 'h2';
endif;

?>


<!-- Start Page Title Area -->
<div class="page-title-area bg1 jarallax" data-jarallax='{"speed": 0.2}' style="background-image:url( <?php echo esc_url( $bg_img ); ?> );">
    <div class="container">
        <div class="page-title-content">
            <<?php echo esc_attr( $tag ); ?>><?php woocommerce_page_title(); ?></<?php echo esc_attr( $tag ); ?>>
            <?php if ( function_exists('yoast_breadcrumb') ) {
                yoast_breadcrumb( '<p class="jasy-seo-breadcrumbs" id="breadcrumbs">','</p>' );
            } else { ?>
                <?php woocommerce_breadcrumb(); ?>
            <?php } ?>
        </div>
    </div>
</div>
<!-- End Page Title Area -->

<div class="products-area ptb-80">
    <div class="<?php echo esc_attr( $jasy_product_layout ); ?>">
        <div class="row">
            <div class="col-lg-12 col-md-12">
            <?php
            if ( woocommerce_product_loop() ) {
             ?>
            <?php   
                woocommerce_product_loop_start();

                $args = array(
                    'post_type' => 'product',
                    'posts_per_page' => 999
                    );
                if( isset($_GET['type']) && $_GET['type'] !='' ){
                    $args['tax_query'] = array(
                        array(
                            'taxonomy' => 'product_cat',
                            'field' => 'slug',
                            'terms' => $_GET['type']
                        )
                    );
                }
                    
                    
                $loop = new WP_Query( $args );
                if ( $loop->have_posts() ) {
                    while ( $loop->have_posts() ) : $loop->the_post();

                        /**
                     * Hook: woocommerce_shop_loop.
                     *
                     * @hooked WC_Structured_Data::generate_product_data() - 10
                     */
                    do_action( 'woocommerce_shop_loop' );
                        wc_get_template_part( 'content', 'product' );
                    endwhile;
                } else {
                    echo __( 'No products found' );
                }
                wp_reset_postdata();
                

                woocommerce_product_loop_end();

                do_action( 'woocommerce_after_shop_loop' );
                
            } else {
                /**
                 * Hook: woocommerce_no_products_found.
                 *
                 * @hooked wc_no_products_found - 10
                 */
                do_action( 'woocommerce_no_products_found' );
            } ?>
            </div>
        </div>
    </div>
</div>    
 


   <?php
get_footer();
