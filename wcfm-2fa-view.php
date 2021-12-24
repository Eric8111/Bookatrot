<?php
global $WCFM, $wp_query;

?>
<style>
    #wcfm_2fa_listing_expander h2 { width: 100%; }
    #wcfm_2fa_listing_expander p { width: 100%; padding: 15px 0; }
    #tfa_advanced_box { display: block; float: left; margin-top: 0px !important;}
</style>
<div class="collapse wcfm-collapse" id="wcfm_2fa_listing">
	
	<div class="wcfm-page-headig">
		<span class="fa fa-cubes"></span>
		<span class="wcfm-page-heading-text"><?php _e( '2FA Verification', 'wcfm-custom-menus' ); ?></span>
		<?php do_action( 'wcfm_page_heading' ); ?>
	</div>
	<div class="wcfm-collapse-content">
		<div id="wcfm_page_load"></div>
		<?php do_action( 'before_wcfm_2fa' ); ?>
		
		<div class="wcfm-container wcfm-top-element-container">
			<h2><?php _e('2FA Verification', 'wcfm-custom-menus' ); ?></h2>
			<div class="wcfm-clearfix"></div>
	  </div>
	  <div class="wcfm-clearfix"></div><br />
		

		<div class="wcfm-container">
			<div id="wcfm_2fa_listing_expander" class="wcfm-content">
			
				<!---- Add Content Here ----->
                <?php echo do_shortcode( '[twofactor_user_settings]' ); ?>
			
				<div class="wcfm-clearfix"></div>
			</div>
			<div class="wcfm-clearfix"></div>
		</div>
	
		<div class="wcfm-clearfix"></div>
		<?php
		do_action( 'after_wcfm_2fa' );
		?>
	</div>
</div>