<?php

if (!defined('ABSPATH')) {
    exit;
}

class WC_Gateway_PayIr extends WC_Payment_Gateway
{

    private string $api_key;
    private string $callback_url;

    public function __construct()
    {
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
        $this->callback_url = get_site_url() . '/?wc-api=wc_gateway_payir';

        add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
        add_action('woocommerce_api_wc_gateway_payir', [$this, 'handle_callback']);
    }
    public function is_available()
    {
        if (!$this->api_key || !$this->callback_url) {
            if (is_admin()) {
                add_action('admin_notices', function () {
                    echo '<div class="error"><p>درگاه پرداخت Pay.ir فعال نیست! لطفاً اطلاعات درگاه را در تنظیمات بررسی کنید.</p></div>';
                });
            }
            return false;
        }
        return true;
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
                'default' => 'yes',
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

        $amount = $order->get_total();
        $data = [
            'api' => $this->api_key,
            'amount' => $amount,
            'redirect' => $this->callback_url,
            'factorNumber' => $order_id,
            'description' => 'پرداخت سفارش شماره ' . $order_id,
        ];

        $response = $this->send_request('https://api.pay.ir/v1.1/payment/send', $data);

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

    /**
     * بررسی وضعیت پرداخت و تایید سفارش
     */
    public function handle_callback()
    {
        if (!isset($_GET['token'])) {
            wp_die('توکن پرداخت یافت نشد.');
        }

        $token = sanitize_text_field($_GET['token']);
        $data = [
            'api' => $this->api_key,
            'token' => $token,
        ];

        $response = $this->send_request('https://api.pay.ir/v1.1/payment/verify', $data);

        if ($response && isset($response->status) && $response->status == 1) {
            $order_id = $response->factorNumber;
            $order = wc_get_order($order_id);

            if ($order) {
                $order->payment_complete($response->transId);
                $order->add_order_note('پرداخت موفق از طریق Pay.ir - شماره تراکنش: ' . $response->transId);
                wp_redirect($this->get_return_url($order));
                exit;
            }
        }

        wc_add_notice('پرداخت انجام نشد یا نامعتبر است.', 'error');
        wp_redirect(wc_get_checkout_url());
        exit;
    }

    /**
     * ارسال درخواست به API
     */
    private function send_request($url, $data)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response);
    }
}
