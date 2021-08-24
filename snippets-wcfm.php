<?php

add_filter( 'wcfm_is_allow_repair_order_item', '__return_true' );

add_filter( 'wcfm_is_allow_wc_geolocate', '__return_false' );


/* DATA issue */

function wcfm_is_dashboard_home() {
    global $wp, $WCFM_Query;
    if( is_wcfm_page() ) {
    $wcfm_endpoints = $WCFM_Query->get_query_vars();
    $is_endpoint = false;
    foreach ( $wcfm_endpoints as $key => $value ) {
    if ( isset( $wp->query_vars[ $key ] ) ) {
    $is_endpoint = true;
    break;
    }
    }
    if( !$is_endpoint ) {
    return true;
    }
    }
    return false;
    }
    function wcfm_is_dashboard_reports() {
    global $wp;
    return !empty( $wp->query_vars['wcfm-reports-sales-by-date'] );
    }
    add_filter('wcfm_marketplace_active_withdrwal_order_status_in_comma', function($statuses) {
    if(wcfm_is_vendor() && (wcfm_is_dashboard_home() || wcfm_is_dashboard_reports())) {
    return "'processing', 'completed'";
    }
    return $statuses;
    });


add_filter( 'wcfmmp_store_tabs', 'new_wcfmmp_store_tabs',90,2);
function new_wcfmmp_store_tabs($store_tabs, $vendor_id) {
    unset($store_tabs['products']);
    return $store_tabs;
}
add_filter('wcfmp_store_default_query_vars',function($tab,$vendor_id){
   return 'about';
},20,2);