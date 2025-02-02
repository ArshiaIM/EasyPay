<?php
/**
 * Plugin Name: درگاه پرداخت سفارشی
 * Description: افزونه پرداخت با زرین پال، بانک ملت و Pay.ir برای ووکامرس
 * Version: 1.0
 * Author: Arshia Rahmani
 * License: GPL2
 */

if (!defined('ABSPATH')) {
    exit; // جلوگیری از دسترسی مستقیم
}

// بررسی اینکه ووکامرس فعال است یا نه
function check_woocommerce_active() {
    if (!class_exists('WooCommerce')) {
        add_action('admin_notices', function() {
            echo '<div class="error"><p>افزونه ووکامرس باید فعال باشد!</p></div>';
        });
        return false;
    }
    return true;
}

// اجرای تنظیمات افزونه
if (check_woocommerce_active()) {
    require_once plugin_dir_path(__FILE__) . 'includes/init.php';
}
