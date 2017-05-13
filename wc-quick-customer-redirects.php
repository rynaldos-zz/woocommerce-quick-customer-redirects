<?php

/*
 Plugin Name: WC Quick Customer Redirects
 Plugin URI: https://profiles.wordpress.org/rynald0s
 Description: This plugin lets you set custom page redirects for customers after registration, login, logout actions.
 Author: Rynaldo Stoltz
 Author URI: https://github.com/rynaldos
 Version: 1.0
 License: GPLv3 or later License
 URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

if ( ! defined( 'ABSPATH' ) ) { 
    exit; // Exit if accessed directly
}

/**
 * Check if WooCommerce is active
 **/

if ( !in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    add_action( 'admin_notices', 'wc_quick_customer_redirects_admin_notice' );
    
} else {

/**
 * Add settings
 */

add_filter( 'woocommerce_get_settings_account','wc_quick_customer_redirect_settings', 10 );
function wc_quick_customer_redirect_settings( $settings ) {
    $settings[] = array( 'title' => __( 'WC Quick Customer Redirects', 'woocommerce' ), 'type' => 'title', 'id' => 'wc_quick_customer_redirects' );
    $settings[] = array(
                'title'    => __( 'Registration redirect', 'woocommerce' ),
                'desc' => __( 'Redirect customers to this page after successfull registration. If no redirect page is set, customer will be redirect to the shop page', 'woocommerce' ),
                'id'       => 'customer_reg_redirect',
                'type'     => 'single_select_page',
                'default'  => '',
                'class'    => 'wc-enhanced-select',
                'css'      => 'min-width:300px;',
                'desc_tip' => true,
            );

        $settings[] = array(
                'title'    => __( 'Login redirect', 'woocommerce' ),
                'desc' => __( 'Redirect customers to this page after successfull login. If no redirect page is set, customer will be redirect to the my-account page', 'woocommerce' ),
                'id'       => 'customer_login_redirect',
                'type'     => 'single_select_page',
                'default'  => '',
                'class'    => 'wc-enhanced-select',
                'css'      => 'min-width:300px;',
                'desc_tip' => true,
            );

        $settings[] = array(
                'title'    => __( 'Logout redirect', 'woocommerce' ),
                'desc' => __( 'Redirect customers to this page after successfull logout. If no redirect page is set, customer will be redirect to the my-account page', 'woocommerce' ),
                'id'       => 'customer_logout_redirect',
                'type'     => 'single_select_page',
                'default'  => '',
                'class'    => 'wc-enhanced-select',
                'css'      => 'min-width:300px;',
                'desc_tip' => true,
            );

    $settings[]=array( 'type' => 'sectionend', 'id' => 'wc_quick_customer_redirects' );
    return $settings;
    }
}

/**
 * Customer registration redirects
 **/

function wc_quick_customer_redirect_after_register( $redirect ) {
     $shop_url = wc_get_page_permalink('shop');
     $customer_redirect = get_permalink( get_option( 'customer_reg_redirect') );
     // return user to shop page when no redirect page is set (default)
     $redirect = (isset($customer_redirect) && ''!=$customer_redirect)?$customer_redirect:$shop_url;
     return $redirect;
}

add_filter('woocommerce_registration_redirect', 'wc_quick_customer_redirect_after_register');

/**
 * Customer login redirects
 **/

function wc_quick_customer_redirect_after_login( $redirect, $user ) {
    
    $role = $user->roles[0];
    $dashboard = admin_url();
    $myaccount = get_permalink( wc_get_page_id( 'myaccount' ) );

    if ( $role == 'administrator' || $role == 'shop-manager' ) {
        $redirect = $dashboard;
    }

    elseif ( $role == 'customer' || $role == 'subscriber' ) {
        $customer_redirect = get_permalink( get_option( 'customer_login_redirect') );
        // return user to my-account page when no redirect page is set (default)
        $redirect = (isset($customer_redirect) && ''!=$customer_redirect)?$customer_redirect:$myaccount;

    } 

    else {
        // redirect any other role to the previous visited page or if not available, to the homepage
        $redirect = wp_get_referer() ? wp_get_referer() : home_url();
    }
        return $redirect;
}

add_filter( 'woocommerce_login_redirect', 'wc_quick_customer_redirect_after_login', 10, 2 );

/**
 * Customer logout redirects
 **/ 

function wc_quick_customer_redirect_after_logout(){
    $myaccount = get_permalink( wc_get_page_id( 'myaccount' ) );
    $customer_redirect = get_permalink( get_option( 'customer_logout_redirect') );
    // return user to my-account page when no redirect page is set (default)
    $redirect = (isset($customer_redirect) && ''!=$customer_redirect)?$customer_redirect:$myaccount;
  wp_redirect($redirect);
  exit();
}

add_action( 'wp_logout', 'wc_quick_customer_redirect_after_logout');

function wc_quick_customer_redirects_admin_notice() {
    $class = 'notice notice-error';
    $message = __( 'WC Skip cart requires WooCommerce plugin to be activated!', 'woocommerce' );
    
    printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) ); 
}
