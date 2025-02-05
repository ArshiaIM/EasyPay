<?php

require plugin_dir_path(__FILE__) . '../bases/AR_Payment_Gateway_Base.php';
class WC_Gateway_Mellat extends AR_Payment_Gateway_Base
{
    private string $merchant_code;
    private string $terminal_code;

    public function __construct()
    {
        parent::__construct();
        $this->id                 = 'mellat';
        $this->method_title       = 'بانک ملت';
        $this->method_description = 'پرداخت از طریق درگاه بانک ملت';
        $this->has_fields         = false;

        $this->init_form_fields();
        $this->init_settings();

        $this->enabled       = $this->get_option('enabled', 'no');
        $this->title         = $this->get_option('title');
        $this->merchant_code = $this->get_option('merchant_code');
        $this->terminal_code = $this->get_option('terminal_code');

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
                'label'   => 'فعال‌سازی درگاه بانک ملت',
                'default' => 'no'
            ),
            'title' => array(
                'title'       => 'عنوان درگاه',
                'type'        => 'text',
                'default'     => 'پرداخت از طریق بانک ملت'
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
            'TerminalId'    => $this->terminal_code,
            'MerchantId'    => $this->merchant_code,
            'Amount'        => intval($order->get_total()),
            'OrderId'       => $order_id,
            'CallbackURL'   => $this->callback_url,  // CallBack URL
        );

        // ارسال درخواست به بانک ملت با فرمت XML
        $response = $this->send_request($data);

        // پردازش پاسخ بانک
        $body = simplexml_load_string(wp_remote_retrieve_body($response));

        // بررسی نتیجه درخواست
        if ($body && isset($body->Result) && $body->Result == 'OK' && isset($body->Token)) {
            return array(
                'result'   => 'success',
                'redirect' => 'https://bpm.shaparak.ir/pgwchannel/startpay.mellat?' . http_build_query(array('token' => (string) $body->Token))
            );
        } else {
            wc_add_notice('خطا در دریافت توکن پرداخت از بانک ملت.', 'error');
            return array('result' => 'failure');
        }
    }
}
?>
