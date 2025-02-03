<?php


if (!defined('ABSPATH')) {
    exit; // جلوگیری از دسترسی مستقیم
}

/**
 * بارگذاری خودکار همه درگاه‌ها
 */
add_action( 'wp_footer', 'check_is_page_function', 10, 0 );

function check_is_page_function() {
    if ( is_page( 'your-page-slug' ) ) {
        // کد مورد نظر شما در اینجا
    }
}
function load_custom_gateways()
{
   
    $gateway_files = glob(plugin_dir_path(__FILE__) . 'gateways/gateway-*.php');
    foreach ($gateway_files as $file) {
        require_once $file;
    }
}

add_action('plugins_loaded', 'load_custom_gateways');

/**
 * ثبت تمام درگاه‌های پرداخت موجود
 */
function add_custom_gateway_classes($methods)
{

    foreach (get_declared_classes() as $class) {
        if (is_subclass_of($class, 'WC_Payment_Gateway')) {
            error_log("Found payment gateway class: " . $class);
            $methods[] = $class;
        }
    }
    return $methods;
}

add_filter('woocommerce_payment_gateways', 'add_custom_gateway_classes');

require_once plugin_dir_path(__FILE__) . 'hooks.php';
