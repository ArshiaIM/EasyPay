<?php
interface ArPaymentGatewayInterface
{
    /**
     * سازنده کلاس (بدون تعریف متغیرهای داخلی در اینترفیس)
     */
    public function __construct();

    /**
     * بررسی فعال بودن درگاه
     * @return bool
     */
    public function is_available(): bool;

    /**
     * تنظیمات فرم درگاه پرداخت در پنل ادمین
     */
    public function init_form_fields();

    // /**
    //  * انجام پردازش پرداخت برای یک سفارش
    //  * @param int $order_id شناسه سفارش
    //  * @return array نتیجه پرداخت
    //  */
    // public function process_payment(int $order_id): array;

    /**
     * ارسال درخواست پرداخت به سرور بانک
     * @param array $data داده‌های پرداخت
     * @return mixed پاسخ درگاه
     */
    public function send_request(array $data);
}
