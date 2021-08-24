<?php

add_action( 'wp_head', 'swashthy_GA_tag', 20 );
function swashthy_GA_tag() { ?>
    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-183104043-1"></script>
    <script>
       window.dataLayer = window.dataLayer || [];
       function gtag(){dataLayer.push(arguments);}
       gtag('js', new Date());

       gtag('config', 'UA-183104043-1');
    </script>

    <!-- Google Tag Manager -->
	<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
	new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
	j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
	'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
	})(window,document,'script','dataLayer','GTM-N7KBVXG');</script>
	<!-- End Google Tag Manager -->
<?php
}


add_action( ' get_header', 'swashthy_GA_tag_noscript', 20 );
function swashthy_GA_tag_noscript() { ?>
    <!-- Google Tag Manager (noscript) -->
	<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-N7KBVXG"
	height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
	<!-- End Google Tag Manager (noscript) -->
<?php
}

add_action('admin_head', 'my_custom_admin_css');

function my_custom_admin_css() {
  echo '<style>
    #content_ifr #tinymce a {
		color: #d1001c;
    }
  </style>';
}

/**
 * Exclude users from BuddyPress members list.
 *
 * @param array $args args.
 *
 * @return array
 */
function buddydev_exclude_users( $args ) {
    /*// do not exclude in admin.
    if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
        return $args;
    }*/

    $excluded = isset( $args['exclude'] ) ? $args['exclude'] : array();

    if ( ! is_array( $excluded ) ) {
        $excluded = explode( ',', $excluded );
    }

    // Change it with the actual numeric user ids.
    $user_ids = array( 44,16,17,25,2 ); // user ids to exclude.

    $excluded = array_merge( $excluded, $user_ids );

    $args['exclude'] = $excluded;

    return $args;
}

add_filter( 'bp_after_has_members_parse_args', 'buddydev_exclude_users' );



/**
 * Sets the extension and mime type for .webp files.
 *
 * @param array  $wp_check_filetype_and_ext File data array containing 'ext', 'type', and
 *                                          'proper_filename' keys.
 * @param string $file                      Full path to the file.
 * @param string $filename                  The name of the file (may differ from $file due to
 *                                          $file being in a tmp directory).
 * @param array  $mimes                     Key is the file extension with value as the mime type.
 */
add_filter( 'wp_check_filetype_and_ext', 'wpse_file_and_ext_webp', 10, 4 );
function wpse_file_and_ext_webp( $types, $file, $filename, $mimes ) {
    if ( false !== strpos( $filename, '.webp' ) ) {
        $types['ext'] = 'webp';
        $types['type'] = 'image/webp';
    }

    return $types;
}

/**
 * Adds webp filetype to allowed mimes
 *
 * @see https://codex.wordpress.org/Plugin_API/Filter_Reference/upload_mimes
 *
 * @param array $mimes Mime types keyed by the file extension regex corresponding to
 *                     those types. 'swf' and 'exe' removed from full list. 'htm|html' also
 *                     removed depending on '$user' capabilities.
 *
 * @return array
 */
add_filter( 'upload_mimes', 'wpse_mime_types_webp' );
function wpse_mime_types_webp( $mimes ) {
    $mimes['webp'] = 'image/webp';

  return $mimes;
}

/* DO something based on user type */

add_action( 'wp_enqueue_scripts', 'shop_manager_css' );
function shop_manager_css(){
    if( in_array( 'shop_manager', (array) wp_get_current_user()->roles ) ){
        echo '<style>
            #wcfm_menu .wcfm_menu_items.wcfm_menu_wcfm-media{
			display:none;
			}
			</style>';
    }
}

/* Hides other shipping methods when free shipping is available */
add_filter( 'woocommerce_package_rates', 'my_hide_shipping_when_free_is_available' );

function my_hide_shipping_when_free_is_available( $rates ) {
        $free = array();
        foreach( $rates as $rate_id => $rate ) {
          if( 'free_shipping' === $rate->method_id ) {
                $free[ $rate_id ] = $rate;
                break;
          }
        }

        return ! empty( $free ) ? $free : $rates;
}

/* Add a new product meta - exclude from google shipping */

add_action( 'woocommerce_product_options_advanced', 'misha_adv_product_options');
function misha_adv_product_options(){

	echo '<div class="options_group">';

	woocommerce_wp_checkbox( array(
		'id'      => 'exclude_google_shopping',
		'label'   => 'Exclude from Google Shopping',
		'desc_tip' => true,
		'description' => 'If ticked, the product will excluded from google shopping feed',
	) );

	echo '</div>';

}

add_action( 'woocommerce_process_product_meta', 'misha_save_fields', 10, 2 );
function misha_save_fields( $id, $post ){

		update_post_meta( $id, 'exclude_google_shopping', $_POST['exclude_google_shopping'] );

}

/**
 * WCFM + Woocommerce
 * adds a check out field notice which shows the 'shop more to avail free shipping
 */

add_action( 'woocommerce_before_cart', 'wc_add_notice_free_shipping' );

function wc_add_notice_free_shipping() {
	global $woocommerce;
	$swsthy_packages = WC()->shipping()->get_packages();

	foreach ( $swsthy_packages as $i => $swsthy_package ) {
		add_filter( 'wcfmmp_more_to_free_shipping_text_enable', false );
		$swsthy_package_name = apply_filters( 'woocommerce_shipping_package_name', ( ( $i + 1 ) > 1 ) ? sprintf( _x( 'Shipping %d', 'shipping packages', 'woocommerce' ), ( $i + 1 ) ) : _x( 'Shipping', 'shipping packages', 'woocommerce' ), $i, $swsthy_package );

		$swsthy_available_methods= $swsthy_package['rates'];
		$swshthy_vendor_id = array_search($swsthy_package,$swsthy_packages);



		$swsthy_package_zone = WC_Shipping_Zones::get_zone_matching_package( $swsthy_package );
		$swsthy_package_zone_name=$swsthy_package_zone->get_zone_name();
		$swsthy_package_zone_id = WC_Shipping_Zone_Data_Store::get_zone_id_from_package( $swsthy_package );
		$swsthy_package_methods = WC_Shipping_Zone_Data_Store::get_methods($swsthy_package_zone_id, true);
		$swsthy_package_vendor_methods = WCFMmp_Shipping_Zone::get_shipping_methods( $swsthy_package_zone_id, $swshthy_vendor_id );
		$notice=false;
		$vendor_method=!empty($swsthy_package_vendor_methods);
		if($vendor_method){
			foreach($swsthy_package_vendor_methods as $swsthy_package_vendor_method){
				if ( 'free_shipping' == $swsthy_package_vendor_method['id'] && 'yes' == $swsthy_package_vendor_method['enabled'] ) {
					$min_amount =$swsthy_package_vendor_method['settings']['min_amount'];
					$subtotal= $swsthy_package['contents_cost'];
					//var_dump($swsthy_package);

					$remaining = $min_amount - $subtotal;
					if($min_amount>$subtotal){
						$notice = $swsthy_package_name." (Shop for ₹".$min_amount." to get free shipping)";
						wc_print_notice( $notice , 'notice' );
						$notice=true;
					}
					break;
				}
			}
		}

		else{

			foreach ($swsthy_package_methods as $swsthy_package_method){

				if ('free_shipping'==$swsthy_package_method->method_id){
					$swsthy_package_free_shipping_instance_id =$swsthy_package_method->instance_id;
					$free_shipping_option ='woocommerce_free_shipping_'.$swsthy_package_free_shipping_instance_id.'_settings';
					$free_shipping = get_option( $free_shipping_option );
					$min_amount = $free_shipping['min_amount'];
					$subtotal= $swsthy_package['contents_cost'];
					$remaining = $min_amount - $subtotal;
					//var_dump($swsthy_package);
					if($min_amount>$subtotal){
						$notice = $swsthy_package_name." (Shop for ₹".$min_amount." to get free shipping)";
						wc_print_notice( $notice , 'notice' );
						$notice=true;
					};
					break;
				}
			}
		}
	}
}

/* Remove wordpress log out confirmation */

function sw_change_menu($items){
    foreach($items as $item){
      if( $item->title == "Logout"){
           $item->url = $item->url . "&_wpnonce=" . wp_create_nonce( 'log-out' );
      }
    }
    return $items;

  }
  add_filter('wp_nav_menu_objects', 'sw_change_menu');


/**
 * Check if product has attributes, dimensions or weight to override the call_user_func() expects parameter 1 to be a valid callback error when changing the additional tab
 */
add_filter( 'woocommerce_product_tabs', 'woo_rename_tabs', 98 );

function woo_rename_tabs( $tabs ) {

	global $product;

	if( $product->has_attributes() || $product->has_dimensions() || $product->has_weight() ) { // Check if product has attributes, dimensions or weight
		$tabs['additional_information']['title'] = __( 'Measurements' );	// Rename the additional information tab
	}

	return $tabs;

}

/* */

// Add a custom fee (fixed or based cart subtotal percentage) by payment
add_action( 'woocommerce_cart_calculate_fees', 'custom_handling_fee' );
function custom_handling_fee ( $cart ) {
    if ( ! defined( 'DOING_AJAX' ) ) return;

    $chosen_payment_id = WC()->session->get('chosen_payment_method');

    if ( empty( $chosen_payment_id ) ) return;

    $subtotal = $cart->subtotal;

    // SETTINGS: Here set in the array the (payment Id) / (fee cost) pairs
    $targeted_payment_ids = array(
        'cod' => 55, // Fixed fee
    );

    // Loop through defined payment Ids array
    foreach ( $targeted_payment_ids as $payment_id => $fee_cost ) {
        if ( $chosen_payment_id === $payment_id ) {
            $cart->add_fee( __('COD fee', 'woocommerce'), $fee_cost, true );
        }
    }
}

// jQuery - Update checkout on payment method change
add_action( 'woocommerce_checkout_init', 'payment_methods_refresh_checkout' );
function payment_methods_refresh_checkout() {
    wc_enqueue_js( "jQuery( function($){
        $('form.checkout').on('change', 'input[name=payment_method]', function(){
            $(document.body).trigger('update_checkout');
        });
    });");
}


add_filter( 'woocommerce_sale_flash', 'msr_add_percentage_to_sale_badge', 20, 3 );
function msr_add_percentage_to_sale_badge( $html, $post, $product ) {
    if( $product->is_type('variable')){
        $percentages = array();

        // Get all variation prices
        $prices = $product->get_variation_prices();

        // Loop through variation prices
        foreach( $prices['price'] as $key => $price ){
            // Only on sale variations
            if( $prices['regular_price'][$key] !== $price ){
                // Calculate and set in the array the percentage for each variation on sale
                $percentages[] = round(100 - ($prices['sale_price'][$key] / $prices['regular_price'][$key] * 100));
            }
        }
        $percentage = '- '.max($percentages) . '%';
    } else {
        $regular_price = (float) $product->get_regular_price();
        $sale_price    = (float) $product->get_sale_price();

        $percentage    = '- '.round(100 - ($sale_price / $regular_price * 100)) . '%';
    }
    return '<span class="onsale" style="background-color:#aa0017;">' . $percentage . '</span>';
}


/*post-in */

add_filter( 'woocommerce_shortcode_products_query', 'woocommerce_shortcode_products_orderby' );
function woocommerce_shortcode_products_orderby( $args ) {
    $standard_array = array('menu_order','title','date','rand','id');

    if( isset( $args['orderby'] ) && !in_array( $args['orderby'], $standard_array ) ) {
    $args['orderby']  = 'post__in';
    }

    return $args;
}


/* woocommerce product zoom */

function remove_product_zoom_support() {
    remove_theme_support( 'wc-product-gallery-zoom' );
}
add_action( 'wp', 'remove_product_zoom_support', 100 );

function sw_deactivate_plugins(){

	if(get_current_blog_id()==4){

		deactivate_plugins('bbpress/bbpress.php');
		deactivate_plugins('bp-remove-profile-links-master/loader.php');
		deactivate_plugins('bp-xprofile-custom-field-types/bp-xprofile-custom-field-types.php');
		deactivate_plugins('buddypress/bp-loader.php');
		deactivate_plugins('buddypress-cover-photo/buddypress-cover-photo.php');
		deactivate_plugins('buddypress-media/index.php');
		deactivate_plugins('buddypress-profile-completion/bp-profile-completion.php');
		deactivate_plugins('cf7-conditional-fields/contact-form-7-conditional-fields.php');
		deactivate_plugins('chaty/cht-icons.php');
		deactivate_plugins('contact-form-7/wp-contact-form-7.php');
		deactivate_plugins('contact-form-cfdb7/contact-form-cfdb-7.php');
		deactivate_plugins('cookie-notice/cookie-notice.php');
		deactivate_plugins('export-import-menus/main.php');
		deactivate_plugins('litespeed-cache/litespeed-cache.php');
		deactivate_plugins('ml-slider/ml-slider.php');
		deactivate_plugins('multisite-enhancements/multisite-enhancements.php');
		deactivate_plugins('nextend-facebook-connect/nextend-facebook-connect.php');
		deactivate_plugins('seo-by-rank-math/rank-math.php');
		deactivate_plugins('user-role-editor/user-role-editor.php');
		deactivate_plugins('wp-smushit/wp-smush.php');
	}
}

add_action( 'init', 'sw_deactivate_plugins' );

