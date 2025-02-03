<?php
class WC_Gateway_Pasargad extends WC_Payment_Gateway {
    private string $merchant_code;
    private string $terminal_code;

    public function __construct() {
        $this->id                 = 'pasargad';
        $this->method_title       = 'بانک پاسارگاد';
        $this->method_description = 'پرداخت از طریق درگاه بانک پاسارگاد';
        $this->has_fields         = false;

        $this->init_form_fields();
        $this->init_settings();

        $this->enabled       = $this->get_option('enabled');
        $this->title         = $this->get_option('title');
        $this->merchant_code = $this->get_option('merchant_code');
        $this->terminal_code = $this->get_option('terminal_code');

        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
    }

    public function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title'   => 'فعال‌سازی',
                'type'    => 'checkbox',
                'label'   => 'فعال‌سازی درگاه بانک پاسارگاد',
                'default' => 'yes'
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
}