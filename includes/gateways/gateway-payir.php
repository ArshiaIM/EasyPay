<?php

if (!defined('ABSPATH')) {
    exit;
}

class WC_Gateway_PayIr extends AR_Payment_Gateway_Base
{

    private string $api_key;
    protected string $callback_url;

    public function __construct()
    {
        parent::__construct();
        $this->id = 'payir';
        $this->method_title = 'درگاه Pay.ir';
        $this->method_description = 'پرداخت آنلاین با استفاده از Pay.ir';
        $this->has_fields = false;

        // تنظیمات درگاه
        $this->init_form_fields();
        $this->init_settings();

        $this->title = $this->get_option('title');
        $this->description = $this->get_option('description');
        $this->api_key = $this->get_option('api_key');
        // $this->callback_url = get_site_url() . '/?wc-api=wc_gateway_payir';

        $this->request_url = 'https://api.pay.ir/v1.1/payment/send';
        $this->request_format = 'json';

        add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
        add_action('woocommerce_api_wc_gateway_payir', [$this, 'handle_callback']);
    }
   

    /**
     * تنظیمات صفحه مدیریت ووکامرس
     */
    public function init_form_fields()
    {
        $this->form_fields = [
            'enabled' => [
                'title' => 'فعال‌سازی',
                'type' => 'checkbox',
                'label' => 'فعال کردن درگاه Pay.ir',
                'default' => 'no',
            ],
            'title' => [
                'title' => 'عنوان درگاه',
                'type' => 'text',
                'default' => 'پرداخت آنلاین با Pay.ir',
            ],
            'description' => [
                'title' => 'توضیحات',
                'type' => 'textarea',
                'default' => 'پرداخت امن از طریق درگاه Pay.ir',
            ],
            'api_key' => [
                'title' => 'API Key',
                'type' => 'text',
            ],
        ];
    }

    /**
     * ارسال درخواست پرداخت به Pay.ir
     */
    public function process_payment($order_id)
    {
        $order = wc_get_order($order_id);
        $data = [
            'api' => $this->api_key,
            'amount' => $order->get_total(),
            'redirect' => $this->callback_url,
            'factorNumber' => $order_id,
            'description' => 'پرداخت سفارش شماره ' . $order_id,
        ];

        $response = $this->send_request($data);
        if ($response && isset($response->status) && $response->status == 1) {
            return [
                'result'   => 'success',
                'redirect' => "https://pay.ir/pg/{$response->token}",
            ];
        } else {
            wc_add_notice('خطا در اتصال به Pay.ir: ' . $response->message, 'error');
            return ['result' => 'failure'];
        }
    }

}
