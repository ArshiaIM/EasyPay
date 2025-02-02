<?php


if (!defined('ABSPATH')) {
    exit; // جلوگیری از دسترسی مستقیم
}

/**
 * بارگذاری خودکار همه درگاه‌ها
 */
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
            $methods[] = $class;
        }
    }
    return $methods;
}

add_filter('woocommerce_payment_gateways', 'add_custom_gateway_classes');
require_once plugin_dir_path(__FILE__) . 'includes/hooks.php';
