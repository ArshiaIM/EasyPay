<?php

if (!defined('ABSPATH')) {
    exit;
}

// درگاه پرداخت سامان
class WC_Gateway_Saman extends AR_Payment_Gateway_Base
{
    private string $terminal;
    private string $username;
    private string $password;

    public function __construct()
    {
        parent::__construct();
        $this->id                 = 'saman';
        $this->method_title       = 'بانک سامان';
        $this->method_description = 'پرداخت از طریق درگاه بانک سامان';
        $this->has_fields         = false;

        $this->init_form_fields();
        $this->init_settings();

        $this->enabled   = $this->get_option('enabled', 'no');
        $this->title     = $this->get_option('title');
        $this->terminal  = $this->get_option('terminal_id');
        $this->username  = $this->get_option('username');
        $this->password  = $this->get_option('password');

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
                'label'   => 'فعال‌سازی درگاه بانک سامان',
                'default' => 'no'
            ),
            'title' => array(
                'title'       => 'عنوان درگاه',
                'type'        => 'text',
                'default'     => 'پرداخت از طریق بانک سامان'
            ),
            'terminal_id' => array(
                'title' => 'شماره ترمینال',
                'type'  => 'text'
            ),
            'username' => array(
                'title' => 'نام کاربری',
                'type'  => 'text'
            ),
            'password' => array(
                'title' => 'رمز عبور',
                'type'  => 'password'
            )
        );
    }

    public function process_payment($order_id)
    {
        $order = wc_get_order($order_id);

        // داده‌های پرداخت
        $data = array(
            'TerminalId'    => $this->terminal,
            'UserName'      => $this->username,
            'UserPassword'  => $this->password,
            'OrderId'       => $order->get_order_number(),
            'Amount'        => $order->get_total(),
            'LocalDateTime' => date('YmdHis'),
            'ReturnUrl'     => $this->callback_url,
            'AdditionalData'=> '',
            'SignData'      => '',
            'Sign'          => ''
        );

        // ارسال درخواست به بانک
        $response = wp_remote_post($this->request_url, array(
            'body' => $data,
            'timeout' => 60,
            'sslverify' => false
        ));

        if (is_wp_error($response)) {
            wc_add_notice('خطا در ارتباط با بانک: ' . $response->get_error_message(), 'error');
            return;
        }

        $body = wp_remote_retrieve_body($response);
        $xml = simplexml_load_string($body);

        if ($xml->ResCode != 0) {
            wc_add_notice('خطا در پرداخت: ' . $xml->Description, 'error');
            return;
        }

        // انتقال به درگاه بانک
        return array(
            'result'   => 'success',
            'redirect' => $xml->Token
        );
    }
}


