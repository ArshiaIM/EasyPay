<?php
add_action('admin_menu', function() {
    add_menu_page(
        'گزارش تراکنش‌ها',
        'گزارش پرداخت‌ها',
        'manage_options',
        'custom_payment_reports',
        'custom_payment_reports_page',
        '',     // آیکون منو (اختیاری)
    26   
    );
});

function custom_payment_reports_page() {
    global $wpdb;

    echo '<div class="wrap"><h2>لیست تراکنش‌ها</h2>';
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead><tr><th>شماره سفارش</th><th>درگاه</th><th>شماره تراکنش</th></tr></thead>';
    echo '<tbody>';

    $gateways = [
        'mellat'    => '_mellat_transaction_id',
        'zarinpal'  => '_zarinpal_transaction_id',
        'payir'     => '_payir_transaction_id'
    ];

    foreach ($gateways as $gateway_name => $meta_key) {
        $results = $wpdb->get_results("SELECT post_id, meta_value AS transaction_id FROM {$wpdb->postmeta} WHERE meta_key = '{$meta_key}'");
        foreach ($results as $row) {
            echo "<tr><td>{$row->post_id}</td><td>{$gateway_name}</td><td>{$row->transaction_id}</td></tr>";
        }
    }

    echo '</tbody></table></div>';
}
