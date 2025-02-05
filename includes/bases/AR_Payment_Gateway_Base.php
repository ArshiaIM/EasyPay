<?php

require plugin_dir_path(__FILE__).'ArPaymentGatewayInterface.php';
abstract class AR_Payment_Gateway_Base extends WC_Payment_Gateway implements ArPaymentGatewayInterface
{
    protected string $callback_url;
    protected string $request_url;
    protected string $request_format = 'json'; // فرمت پیش‌فرض XML

    public function __construct()
    {
        
        $this->callback_url = add_query_arg('wc-api', 'wc_gateway_' . $this->id, home_url('/'));
        
        // ثبت متد پردازش بازگشت از بانک
        add_action('woocommerce_api_wc_gateway_' . $this->id, array($this, 'process_callback'));
    }

    public function is_available(): bool
    {
        return $this->enabled === 'yes';
    }

    

    public function init_form_fields(){}

    /**
     * پردازش پاسخ بازگشتی از بانک (Callback)
     */
    public function process_callback()
    {
        if (!isset($_REQUEST['order_id'])) {
            wp_die('درخواست نامعتبر است', 'خطا', array('response' => 400));
        }

        $order_id = sanitize_text_field($_REQUEST['order_id']);
        $status = isset($_REQUEST['status']) ? sanitize_text_field($_REQUEST['status']) : 'failed';
        $transaction_id = isset($_REQUEST['transaction_id']) ? sanitize_text_field($_REQUEST['transaction_id']) : '';

        $order = wc_get_order($order_id);

        if (!$order) {
            wp_die('سفارش یافت نشد', 'خطا', array('response' => 404));
        }

        if ($status === 'success') {
            $order->payment_complete($transaction_id);
            $order->add_order_note("پرداخت موفق. شماره تراکنش: {$transaction_id}");
            wc_reduce_stock_levels($order_id);
        } else {
            $order->add_order_note('پرداخت ناموفق.');
            wc_add_notice('پرداخت ناموفق بود.', 'error');
        }

        wp_redirect($this->get_return_url($order));
        exit;
    }

    /**
     * متد ارسال درخواست پرداخت
     */
    public function send_request(array $data)
    {
        $headers = array('Content-Type' => $this->request_format === 'json' ? 'application/json' : 'application/xml');
        $body = $this->request_format === 'json' ? json_encode($data) : $this->array_to_xml($data);

        $response = wp_remote_post($this->request_url, array(
            'body'    => $body,
            'headers' => $headers,
            'timeout' => 15,
        ));

        if (is_wp_error($response)) {
            wc_add_notice('خطا در اتصال به درگاه: ' . $response->get_error_message(), 'error');
            return array('result' => 'failure');
        }

        return $response;
    }

    /**
     * متد کمکی برای تبدیل آرایه به XML
     */
    private function array_to_xml($data, $root_element = '<Request/>')
    {
        $xml = new SimpleXMLElement($root_element);
        $this->array_to_xml_recursive($data, $xml);
        return $xml->asXML();
    }

    private function array_to_xml_recursive($data, &$xml)
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $subnode = $xml->addChild($key);
                $this->array_to_xml_recursive($value, $subnode);
            } else {
                $xml->addChild($key, htmlspecialchars($value));
            }
        }
    }

}
