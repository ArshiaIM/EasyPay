<?php

if (!defined('ABSPATH')) {
    exit;
}

class WC_Gateway_Mellat extends WC_Payment_Gateway {
    public function __construct() {
        $this->id                 = 'mellat';
        $this->method_title       = 'بانک ملت';
        $this->method_description = 'پرداخت از طریق درگاه بانک ملت';
        $this->has_fields         = false;

        $this->init_form_fields();
        $this->init_settings();

        $this->enabled   = $this->get_option('enabled');
        $this->title     = $this->get_option('title');
        $this->terminal  = $this->get_option('terminal_id');
        $this->username  = $this->get_option('username');
        $this->password  = $this->get_option('password');

        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
    }

    public function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title'   => 'فعال‌سازی',
                'type'    => 'checkbox',
                'label'   => 'فعال‌سازی درگاه بانک ملت',
                'default' => 'yes'
            ),
            'title' => array(
                'title'       => 'عنوان درگاه',
                'type'        => 'text',
                'default'     => 'پرداخت از طریق بانک ملت'
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

    public function process_payment($order_id) {
        $order = wc_get_order($order_id);
        $amount = intval($order->get_total()) * 10;
        $callback_url = add_query_arg('wc-api', $this->id, home_url('/'));

        $data = array(
            'TerminalId' => $this->terminal,
            'UserName'   => $this->username,
            'Password'   => $this->password,
            'OrderId'    => $order_id,
            'Amount'     => $amount,
            'LocalDate'  => date('Ymd'),
            'LocalTime'  => date('His'),
            'CallBackUrl'=> $callback_url
        );

        $response = wp_remote_post('https://bpm.shaparak.ir/pgwchannel/services/pgw?wsdl', array(
            'body'    => json_encode($data),
            'headers' => array('Content-Type' => 'application/json')
        ));

        if (is_wp_error($response)) {
            wc_add_notice('خطا در ارتباط با درگاه.', 'error');
            return;
        }

        $result = json_decode(wp_remote_retrieve_body($response));

        if ($result->ResCode == '0') {
            return array(
                'result'   => 'success',
                'redirect' => 'https://bpm.shaparak.ir/pgwchannel/startpay.mellat?RefId=' . $result->RefId
            );

        } else {
            wc_add_notice('خطا در پرداخت: ' . $result->Description, 'error');
            return;
        }
    }

}

