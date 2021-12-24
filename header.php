<?php global $opt_name;
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Jasy
 */

?>
<!doctype html>
<?php if( jasy_rtl() == true ): ?><html dir="rtl" <?php language_attributes(); ?>>
<?php else: ?><html <?php language_attributes(); ?>><?php endif; ?>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">

	<?php wp_head(); ?>
	<script>
		jQuery(document).ready(function () {
			if( jQuery('ul.products li').length == 0 ){
				jQuery('form.woocommerce-ordering').hide();
			}	
		});
	</script>
</head>

<?php 
	if( isset($opt_name['enable_light'])){
		if($opt_name['enable_light'] == true ){
			$jasy_version = 'light-version';
		}else {
			$jasy_version = 'dark-version';
		}
	}else {
		$jasy_version = 'dark-version';
	}

	if ( isset( $_GET['version'] ) ) {
		$jasy_version = $_GET['version'];
	}
?>

<?php
if( isset($opt_name['welcome_text']) ):
	$call_number           = $opt_name['call_number'];
	$call_number_link      = $opt_name['call_number_link'];
	$address               = $opt_name['address'];
	$email                 = $opt_name['email'];
	$email_link            = $opt_name['email_link'];
	$welcome_text          = $opt_name['welcome_text'];
else:
	$call_number           = '';
	$call_number_link      = '';
	$address               = '';
	$welcome_text          = '';
	$email                 = '';
	$email_link            = '';
endif;
?>

<body <?php body_class($jasy_version); ?>>
<?php wp_body_open(); ?>

	<!-- Start Preloader Area -->
	<?php jasy_preloader(); ?>
	<!-- End Preloader Area -->

	<!-- Start Navbar Area -->
	<?php if ( !is_404()){ ?>
	<div class="navbar-area <?php if ( is_user_logged_in() ) {echo esc_attr('hide-wp-nav');}?>">
		<!-- Start Top Header Area -->
		<?php if( isset( $opt_name['hide_top_header'] ) && $opt_name['hide_top_header'] != 1 ){
			if( $email != '' || $call_number != '' || $address != '' || $welcome_text != '' ) { ?>
				<div class="top-header">
					<div class="container">
						<div class="row align-items-center">
							<div class="col-lg-3">
								<div class="header-left-content">
									<p><?php echo esc_html( $welcome_text ); ?></p>
								</div>
							</div>
							<div class="col-lg-9">
								<div class="header-right-content">
									<ul>
										<?php if( $call_number != '' ) { ?>
											<li>
												<a href="<?php echo esc_url( $call_number_link ); ?>">
													<i class='fa fa-phone'></i>
													<?php echo esc_html( $call_number ); ?>
												</a>
											</li>
										<?php } ?>
										<?php if( $address != '' ) { ?>
											<li>
												<i class='fa fa-location-arrow'></i>
												<?php echo esc_html( $address ); ?>
											</li>
										<?php } ?>

										<?php if( $email != '' ) { ?>
											<li>
												<a href="<?php echo esc_url( $email_link ); ?>">
													<i class='fa fa-envelope'></i>
													<?php echo esc_html( $email ); ?>
												</a>
											</li>
										<?php } ?>
									</ul>
								</div>
							</div>
						</div>
					</div>
				</div> <?php
			}  
		} ?>

		<div class="semental-mobile-nav">
			<div class="container">
				<div class="semental-responsive-menu">
					<div class="logo">
						<a href="<?php echo esc_url( home_url( '/' ) ); ?>">
							<?php
								$custom_logo_id = get_theme_mod( 'custom_logo' );
								$logo = wp_get_attachment_image_src( $custom_logo_id , 'full' );
								if ( !$logo == '' ) { ?>
									<img src="<?php echo esc_url($logo[0], 'jasy') ?>" alt="<?php bloginfo( 'title' ); ?>"><?php
								}else{ ?>
									<h2><?php bloginfo( 'name' ); ?></h2>
							<?php } ?>
						</a>
					</div>
				</div>
			</div>
		</div>

		<div class="semental-nav">
			<div class="container">
				<nav class="navbar navbar-expand-md navbar-light">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="navbar-brand">
						<?php
							$custom_logo_id = get_theme_mod( 'custom_logo' );
							$logo = wp_get_attachment_image_src( $custom_logo_id , 'full' );
							if ( has_custom_logo() ) { ?>
								<img src="<?php echo esc_url( $logo[0] ); ?>" alt="<?php bloginfo( 'title' ); ?>"><?php
							}else{ ?>
								<h2><?php bloginfo( 'name' ); ?></h2>
						<?php } ?>
					</a>

					<div class="collapse navbar-collapse mean-menu ml-auto" id="navbarSupportedContent">
						<!-- Nav -->
						<?php 
						if(has_nav_menu('header')){
							wp_nav_menu( array(
								'theme_location'  => 'header',
								'depth'           => 3,
								'container'       => 'div',
								'menu_class'      => 'navbar-nav',
								'fallback_cb'     => 'WP_Bootstrap_Navwalker::fallback',
								'walker'          => new Jasy_Bootstrap_Navwalker()
							) );
						} ?>

						<?php 
						// Nav Button
						if( (isset($opt_name['enable_cart_btn']) && $opt_name['enable_cart_btn'] != 0) || (isset($opt_name['enable_modal']) && $opt_name['enable_modal'] != 0)  ){ ?>

							<div class="others-options"><?php
							if($opt_name['enable_cart_btn'] == true) { ?>
								<?php
									if ( class_exists( 'WooCommerce' ) ) {
										if( is_user_logged_in()){ ?>
									<a href="<?php echo esc_url(wc_get_cart_url()) ?>" class="nav-link shop-cart">
										<i class="flaticon-shopping-cart"></i>
										<span class="mini-cart-count"></span>
									</a> <?php } } ?>
							<?php } 

							if($opt_name['enable_modal'] == true) { ?>
								<a href="#" class="nav-link" data-toggle="modal" data-target="#rightSidebar">
									<i class="flaticon-menu"></i>
								</a>
							<?php } ?>
							</div>
						<?php } ?>
					</div>
				</nav>
			</div>
		</div>
	</div>
	<?php } ?>
	<!-- End Navbar Area -->
 
	<!-- Modal -->
	<div class="modal right fade" id="rightSidebar" tabindex="-1" role="dialog">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				</div>

				<div class="modal-body">
					<?php if( isset( $opt_name['modal_menu_title'] ) ): ?>
						<h3 class="title"><?php echo esc_html( $opt_name['modal_menu_title'] );  ?></h3>
					<?php endif; ?>

					<div class="modal-menu">
						<?php 
						if(has_nav_menu('sidebar-menu')){
							wp_nav_menu( array(
								'theme_location'  => 'sidebar-menu',
								'depth'           => 1,
							) );
						} ?>
					</div>

					<div class="contact_info">
						<?php if( isset( $opt_name['modal_info_title'] ) ): ?>
							<h3 class="title"><?php echo esc_html( $opt_name['modal_info_title'] );  ?></h3>
						<?php endif; ?>

						<ul class="modal-contact-info">
							<?php if(isset($opt_name['modal_add']) && $opt_name['modal_add'] !=''){ ?>
								<li>
									<i class="icofont-google-map"></i>
									<?php echo wp_kses_post($opt_name['modal_add'],'jasy'); ?>
								</li><?php 
							} 
							if(isset($opt_name['modal_contact']) && $opt_name['modal_contact'] !=''){ ?>
								<li>
									<i class="icofont-ui-call"></i>
									<?php echo wp_kses_post($opt_name['modal_contact'],'jasy'); ?>
								</li><?php 
							}
							if(isset($opt_name['modal_gmail']) && $opt_name['modal_gmail'] !=''){ ?>
								<li>
									<i class="icofont-envelope"></i>
									<?php echo wp_kses_post($opt_name['modal_gmail'],'jasy'); ?>
								</li><?php 
							} ?>
						</ul>
					</div>

					<ul class="social">
						<?php 
							if(isset($opt_name['facebook-url']) && $opt_name['facebook-url'] !='') { ?>
							<li><a href="<?php echo esc_url($opt_name['facebook-url'], 'jasy'); ?>"><i class="fa fa-facebook"></i> </a>
							</li>
						<?php } ?>
							
						<?php 
							if(isset($opt_name['twitter-url']) && $opt_name['twitter-url'] !='') { ?>
							<li><a href="<?php echo esc_url($opt_name['twitter-url'], 'jasy'); ?>"><i class="fa fa-twitter"></i> </a>
							</li>
						<?php } ?>
						
						<?php 
							if(isset($opt_name['instagram-url']) && $opt_name['instagram-url'] !='') { ?>
							<li><a href="<?php echo esc_url($opt_name['instagram-url'], 'jasy'); ?>"><i class="fa fa-instagram"></i> </a>
							</li>
						<?php } ?>
						
						<?php 
							if(isset($opt_name['youtube-url']) && $opt_name['youtube-url'] !='') { ?>
							<li><a href="<?php echo esc_url($opt_name['youtube-url'], 'jasy'); ?>"><i class="fa fa-youtube"></i> </a>
							</li>
						<?php } ?>
						
						<?php 
							if(isset($opt_name['linkedin-url']) && $opt_name['linkedin-url'] !='') { ?>
							<li><a href="<?php echo esc_url($opt_name['linkedin-url'], 'jasy'); ?>"><i class="fa fa-linkedin"></i> </a>
							</li>
						<?php } ?>
						
						<?php 
							if(isset($opt_name['pinterest-url']) && $opt_name['pinterest-url'] !='') { ?>
							<li><a href="<?php echo esc_url($opt_name['pinterest-url'], 'jasy'); ?>"><i class="fa fa-pinterest"></i> </a>
							</li>
						<?php } ?>
						
						<?php 
							if(isset($opt_name['google-plus-url']) && $opt_name['google-plus-url'] !='') { ?>
							<li><a href="<?php echo esc_url($opt_name['google-plus-url'], 'jasy'); ?>"><i class="fa fa-google-plus"></i> </a>
							</li>
						<?php } ?>
					</ul>
				</div>
			</div><!-- modal-content -->
		</div><!-- modal-dialog -->
	</div><!-- modal -->
 