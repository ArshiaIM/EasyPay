<?php

if (!defined('ABSPATH')) {
    exit;
}

// درگاه پرداخت سامان
class WC_Gateway_Saman extends WC_Payment_Gateway
{
    private string $terminal;
    private string $username;
    private string $password;

    public function __construct()
    {
        $this->id                 = 'saman';
        $this->method_title       = 'بانک سامان';
        $this->method_description = 'پرداخت از طریق درگاه بانک سامان';
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
    public function is_available()
    {
        if (!$this->terminal || !$this->username || !$this->password) {
            if (is_admin()) {
                add_action('admin_notices', function () {
                    echo '<div class="error"><p>درگاه پرداخت Saman فعال نیست! لطفاً اطلاعات درگاه را در تنظیمات بررسی کنید.</p></div>';
                });
            }
            return false;
        }
        return true;
    }


    public function init_form_fields()
    {
        $this->form_fields = array(
            'enabled' => array(
                'title'   => 'فعال‌سازی',
                'type'    => 'checkbox',
                'label'   => 'فعال‌سازی درگاه بانک سامان',
                'default' => 'yes'
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
}

// درگاه پرداخت پارسیان
class WC_Gateway_Parsian extends WC_Payment_Gateway
{
    private string $merchant_id;

    public function __construct()
    {
        $this->id                 = 'parsian';
        $this->method_title       = 'بانک پارسیان';
        $this->method_description = 'پرداخت از طریق درگاه بانک پارسیان';
        $this->has_fields         = false;

        $this->init_form_fields();
        $this->init_settings();

        $this->enabled    = $this->get_option('enabled');
        $this->title      = $this->get_option('title');
        $this->merchant_id = $this->get_option('merchant_id');

        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
    }

    public function init_form_fields()
    {
        $this->form_fields = array(
            'enabled' => array(
                'title'   => 'فعال‌سازی',
                'type'    => 'checkbox',
                'label'   => 'فعال‌سازی درگاه بانک پارسیان',
                'default' => 'yes'
            ),
            'title' => array(
                'title'       => 'عنوان درگاه',
                'type'        => 'text',
                'default'     => 'پرداخت از طریق بانک پارسیان'
            ),
            'merchant_id' => array(
                'title' => 'کد پذیرنده',
                'type'  => 'text'
            )
        );
    }
}
