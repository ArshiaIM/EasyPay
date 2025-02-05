# Installation and Setup Guide for the Plugin

## Installation Steps

1. **Download and Install the Plugin**  
   First, download the plugin and place it in the `wp-content/plugins` folder of your WordPress installation.  
   Make sure that **WooCommerce** is already installed and activated on your WordPress site before using this plugin.

2. **Activate the Plugin**  
   Go to your WordPress dashboard and navigate to `Plugins` → `Installed Plugins`. Find your newly uploaded plugin and click "Activate."

## Configure Gateways

To configure the payment gateways, follow these steps:

1. Go to the **WooCommerce settings**:
   - Navigate to `WooCommerce > Settings > Payments`.

2. **Add a Gateway**  
   To add a new payment gateway, use the `AR_Gateway_Payment` class provided by the plugin.  
   For instance, the file is located at:
   - `plugin_path.'/includes/bases/AR_Gateway_Payment.php'`

### Example Code to Add a New Gateway

Here’s an example of how you can configure a new gateway:

```php
class WC_Gateway_Mellat extends AR_Gateway_Payment {

    public function __construct() {
        $this->id = 'mellat';
        $this->method_title = 'بانک ملت';
        $this->method_description = 'پرداخت از طریق بانک ملت';
        $this->has_fields = false;

        $this->init_form_fields();
        $this->init_settings();

        // Get plugin settings
        $this->enabled = $this->get_option('enabled', 'no');
        $this->title = $this->get_option('title');
        $this->merchant_code = $this->get_option('merchant_code');
        $this->terminal_code = $this->get_option('terminal_code');

        // Additional configurations for the gateway
        $this->request_url = 'https://bpm.shaparak.ir/pgwchannel/startpay.mellat';
        $this->request_format = 'xml';
        
        // Register action to process admin settings update
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
    }

    public function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title'   => 'Enable Gateway',
                'type'    => 'checkbox',
                'label'   => 'Enable Mellat Payment Gateway',
                'default' => 'no'
            ),
            'title' => array(
                'title'       => 'Gateway Title',
                'type'        => 'text',
                'default'     => 'پرداخت از طریق بانک ملت'
            ),
            'merchant_code' => array(
                'title' => 'Merchant Code',
                'type'  => 'text'
            ),
            'terminal_code' => array(
                'title' => 'Terminal Code',
                'type'  => 'text'
            )
        );
    }

    public function process_payment($order_id) {
        $order = wc_get_order($order_id);

        // Payment data to be sent to the bank
        $data = array(
            'TerminalId'    => $this->terminal_code,
            'MerchantId'    => $this->merchant_code,
            'Amount'        => intval($order->get_total()),
            'OrderId'       => $order_id,
            'CallbackURL'   => $this->callback_url,
        );

        // Send request to the bank and process response
        $response = $this->send_request($data);

        $body = simplexml_load_string(wp_remote_retrieve_body($response));

        if ($body && isset($body->Result) && $body->Result == 'OK' && isset($body->Token)) {
            return array(
                'result'   => 'success',
                'redirect' => 'https://bpm.shaparak.ir/pgwchannel/startpay.mellat?' . http_build_query(array('token' => (string) $body->Token))
            );
        } else {
            wc_add_notice('Payment error from Mellat gateway.', 'error');
            return array('result' => 'failure');
        }
    }
}
