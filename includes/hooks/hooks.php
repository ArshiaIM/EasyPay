<?php
add_action('woocommerce_thankyou', function ($order_id) {
    $order = wc_get_order($order_id);
    if (!$order) {
        return;
    }

    $payment_method = $order->get_payment_method();

    if ($payment_method === 'mellat' && isset($_GET['RefId'])) {
        update_post_meta($order_id, '_mellat_transaction_id', sanitize_text_field($_GET['RefId']));
        update_post_meta($order_id, '_mellat_status', 'success');
    }

    if ($payment_method === 'zarinpal' && isset($_GET['Authority'])) {
        update_post_meta($order_id, '_zarinpal_transaction_id', sanitize_text_field($_GET['Authority']));
        update_post_meta($order_id, '_zarinpal_status', 'success');
    }

    if ($payment_method === 'payir' && isset($_GET['transId'])) {
        update_post_meta($order_id, '_payir_transaction_id', sanitize_text_field($_GET['transId']));
        update_post_meta($order_id, '_payir_status', 'success');
    }
    if ($payment_method === 'pasargad' && isset($_GET['tref'])) {
        update_post_meta($order_id, '_pasargad_transaction_id', sanitize_text_field($_GET['tref']));
        update_post_meta($order_id, '_pasargad_status', 'success');
    }

    if ($payment_method === 'saman' && isset($_GET['RefNum'])) {
        update_post_meta($order_id, '_saman_transaction_id', sanitize_text_field($_GET['RefNum']));
        update_post_meta($order_id, '_saman_status', 'success');
    }
});
