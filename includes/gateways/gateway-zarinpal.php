<?php

if (!defined('ABSPATH')) {
    exit; // جلوگیری از دسترسی مستقیم
}

// Test
class WC_Gateway_Zarinpal extends AR_Payment_Gateway_Base
{
    private string $merchant_id;
    public function __construct()
    {
        parent::__construct();
        $this->id                 = 'zarinpal';
        $this->method_title       = 'زرین پال';
        $this->method_description = 'پرداخت از طریق زرین پال';
        $this->has_fields         = false;

        $this->init_form_fields();
        $this->init_settings();

        $this->enabled   = $this->get_option('enabled', 'no');
        $this->title       = $this->get_option('title');
        $this->merchant_id = $this->get_option('merchant_id');


        // تنظیمات اختصاصی این درگاه
        $this->request_url    = 'https://bpm.shaparak.ir/pgwchannel/startpay.mellat'; // URL بانک ملت
        $this->request_format = 'xml'; // این درگاه از XML پشتیبانی می‌کند

        
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
    }

    public function init_form_fields()
    {
        $this->form_fields = array(
            'enabled' => array(
                'title'   => 'فعال‌سازی',
                'type'    => 'checkbox',
                'label'   => 'فعال‌سازی زرین پال',
                'default' => 'no'
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

    public function process_payment($order_id)
    {
        $order = wc_get_order($order_id);

        $data = array(
            'merchant_id' => $this->merchant_id,
            'amount'      => $order->get_total(),
            'callback_url' => $this->callback_url,
            'description' => 'پرداخت سفارش شماره ' . $order->get_id()
        );

        $response = $this->send_request($data);

        if (is_wp_error($response)) {
            wc_add_notice('خطا در ارتباط با زرین پال.', 'error');
            return;
        }

        $result = json_decode(wp_remote_retrieve_body($response));

        if ($result->data->code == 100) {
            return array(
                'result'   => 'success',
                'redirect' => 'https://www.sandbox.zarinpal.com/pg/StartPay/' . $result->data->authority
            );
        } else {
            wc_add_notice('خطا در پرداخت: ' . $result->errors->message, 'error');
            return;
        }
    }
}
