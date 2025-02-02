<?php

if (!defined('ABSPATH')) {
    exit; // جلوگیری از دسترسی مستقیم
}

class WC_Gateway_Zarinpal extends WC_Payment_Gateway {
    public function __construct() {
        $this->id                 = 'zarinpal';
        $this->method_title       = 'زرین پال';
        $this->method_description = 'پرداخت از طریق زرین پال';
        $this->has_fields         = false;

        $this->init_form_fields();
        $this->init_settings();

        $this->enabled     = $this->get_option('enabled');
        $this->title       = $this->get_option('title');
        $this->merchant_id = $this->get_option('merchant_id');

        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
    }

    public function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title'   => 'فعال‌سازی',
                'type'    => 'checkbox',
                'label'   => 'فعال‌سازی زرین پال',
                'default' => 'yes'
            ),
            'title' => array(
                'title'       => 'عنوان درگاه',
                'type'        => 'text',
                'default'     => 'پرداخت آنلاین با زرین پال',
                'description' => 'این عنوان در صفحه پرداخت نمایش داده می‌شود.'
            ),
            'merchant_id' => array(
                'title'       => 'مرچنت کد',
                'type'        => 'text',
                'default'     => '',
                'description' => 'مرچنت کد دریافتی از زرین پال'
            )
        );
    }

    public function process_payment($order_id) {
        $order = wc_get_order($order_id);
        $amount = intval($order->get_total()) * 10; // تبدیل تومان به ریال
        $callback_url = add_query_arg('wc-api', $this->id, home_url('/'));

        $data = array(
            'merchant_id' => $this->merchant_id,
            'amount'      => $amount,
            'callback_url'=> $callback_url,
            'description' => 'پرداخت سفارش شماره ' . $order->get_id()
        );

        $response = wp_remote_post('https://api.zarinpal.com/pg/v4/payment/request.json', array(
            'body'    => json_encode($data),
            'headers' => array('Content-Type' => 'application/json')
        ));

        if (is_wp_error($response)) {
            wc_add_notice('خطا در ارتباط با زرین پال.', 'error');
            return;
        }

        $result = json_decode(wp_remote_retrieve_body($response));

        if ($result->data->code == 100) {
            return array(
                'result'   => 'success',
                'redirect' => 'https://www.zarinpal.com/pg/StartPay/' . $result->data->authority
            );
        } else {
            wc_add_notice('خطا در پرداخت: ' . $result->errors->message, 'error');
            return;
        }
    }
}
