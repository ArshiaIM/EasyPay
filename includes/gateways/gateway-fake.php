<?php
class FakeMellatGateway {

    public $sandbox_mode;
    public function __construct() {
        $this->sandbox_mode = true; // تغییر به false برای غیرفعال‌سازی فیک
    }

    /**
     * ارسال درخواست پرداخت
     */
    public function requestPayment($data) {
        if (!$this->sandbox_mode) return false;

        return [
            'status' => 'success',
            'refId' => 'FAKE-REF-' . rand(100000, 999999),
            'message' => 'تراکنش موفق'
        ];
    }

    /**
     * بررسی وضعیت تراکنش
     */
    public function verifyPayment($data) {
        if (!$this->sandbox_mode) return false;

        return [
            'status' => 'verified',
            'transactionId' => 'FAKE-TRAN-' . rand(100000, 999999),
            'message' => 'پرداخت تایید شد'
        ];
    }

    /**
     * بازگشت از درگاه و بررسی نهایی
     */
    public function callback($data) {
        if (!$this->sandbox_mode) return false;

        return [
            'status' => 'completed',
            'trackingCode' => 'FAKE-TRACK-' . rand(100000, 999999),
            'message' => 'پرداخت با موفقیت انجام شد'
        ];
    }
}
