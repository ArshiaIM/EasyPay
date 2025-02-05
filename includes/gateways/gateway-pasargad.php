<?php

class WC_Gateway_Pasargad extends AR_Payment_Gateway_Base
{
    private string $merchant_code;
    private string $terminal_code;

    public function __construct()
    {
        parent::__construct();
        $this->id                 = 'pasargad';
        $this->method_title       = 'بانک پاسارگاد';
        $this->method_description = 'پرداخت از طریق درگاه بانک پاسارگاد';
        $this->has_fields         = false;

        $this->init_form_fields();
        $this->init_settings();

        $this->enabled       = $this->get_option('enabled', 'no');
        $this->title         = $this->get_option('title');
        $this->merchant_code = $this->get_option('merchant_code');
        $this->terminal_code = $this->get_option('terminal_code');

        // تنظیمات اختصاصی این درگاه
        $this->request_url    = 'https://pep.shaparak.ir/Api/v1/PaymentRequest';
        $this->request_format = 'json'; // این درگاه از JSON پشتیبانی می‌کند

        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
    }

    public function init_form_fields()
    {
        $this->form_fields = array(
            'enabled' => array(
                'title'   => 'فعال‌سازی',
                'type'    => 'checkbox',
                'label'   => 'فعال‌سازی درگاه بانک پاسارگاد',
                'default' => 'no'
            ),
            'title' => array(
                'title'       => 'عنوان درگاه',
                'type'        => 'text',
                'default'     => 'پرداخت از طریق بانک پاسارگاد'
            ),
            'merchant_code' => array(
                'title' => 'کد پذیرنده',
                'type'  => 'text'
            ),
            'terminal_code' => array(
                'title' => 'کد ترمینال',
                'type'  => 'text'
            )
        );
    }

    public function process_payment($order_id)
    {
        $order = wc_get_order($order_id);

        // داده‌های پرداخت
        $data = array(
            'merchantCode'   => $this->merchant_code,
            'terminalCode'   => $this->terminal_code,
            'amount'         => intval($order->get_total()),
            'redirectAddress'=> $this->callback_url,
            'invoiceNumber'  => $order->get_order_number(),
        );

        // ارسال درخواست به بانک پاسارگاد
        $response = $this->send_request($data);

        // پردازش پاسخ بانک
        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (isset($body['success']) && $body['success'] == true && !empty($body['result']['token'])) {
            return array(
                'result'   => 'success',
                'redirect' => 'https://pep.shaparak.ir/payment.aspx?n=' . $body['result']['token']
            );
        } else {
            wc_add_notice('خطا در دریافت توکن پرداخت از بانک پاسارگاد.', 'error');
            return array('result' => 'failure');
        }
    }
}
