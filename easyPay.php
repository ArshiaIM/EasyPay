<?php

/**
 * Plugin Name: درگاه پرداخت سفارشی
 * Description: افزونه پرداخت با زرین پال، بانک ملت و Pay.ir برای ووکامرس
 * Version: 1.0
 * Author: Arshia Rahmani
 * License: GPL2
 */
//var_dump((plugin_dir_path(__FILE__) . 'includes/gateways/gateway-*.php'));
if (!defined('ABSPATH')) {
    exit; // جلوگیری از دسترسی مستقیم
}
if (!function_exists('is_plugin_active')) {
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
}



// بررسی اینکه ووکامرس فعال است یا نه
function check_woocommerce_active():bool
{
    
        if (!is_plugin_active('woocommerce/woocommerce.php')) {
            add_action('admin_notices', function () {
                echo '<div class="error"><p>افزونه ووکامرس باید فعال باشد!</p></div>';
            });
            deactivate_plugins( deactivate_plugins(plugin_basename(__FILE__)));
            return false;
        }
        return true;
    
}
// add_action('plugins_loaded', check_woocommerce_active());




// اجرای تنظیمات افزونه
if (check_woocommerce_active()) {
    
    add_action('wp_footer', function () {
        echo '<div style="text-align:center; padding:20px; background:#000; color:#fff;">این یک متن تستی در فوتر است.</div>';
    });
    
    require_once plugin_dir_path(__FILE__) . 'includes/main.php';
    require_once plugin_dir_path(__FILE__) . 'includes/hooks/hooks.php';
    if(is_admin()){
        require_once plugin_dir_path(__FILE__).'admin/admin.php';
    }
}else{
    return;
}
