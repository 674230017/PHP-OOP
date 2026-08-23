<?php

declare(strict_types=1);

namespace StudentApp\Payment;

use InvalidArgumentException;

/**
 * Strategy Interface สำหรับช่องทางการชำระเงินแบบต่าง ๆ
 */
interface PaymentMethod
{
    public function pay(float $amount): string;
}

/**
 * ตัวเลือกการชำระเงินผ่านบัตรเครดิต
 */
final readonly class CreditCardPayment implements PaymentMethod
{
    public function __construct(private string $cardLast4)
    {
    }

    public function pay(float $amount): string
    {
        // จำลองข้อความประมวลผลการตัดบัตรเครดิต
        return sprintf("ชำระเงิน %.2f บาท ผ่านบัตรเครดิต (ลงท้ายด้วย %s) สำเร็จ", $amount, $this->cardLast4);
    }
}

/**
 * ตัวเลือกการชำระเงินผ่าน PromptPay QR Code
 */
final readonly class PromptPayPayment implements PaymentMethod
{
    public function pay(float $amount): string
    {
        // จำลองข้อความประมวลผลการสร้าง QR Code และรับชำระ
        return sprintf("ชำระเงิน %.2f บาท ผ่าน PromptPay QR Code สำเร็จ", $amount);
    }
}

/**
 * ตัวประมวลผลการชำระเงิน (Context)
 * ใช้ Dependency Injection เพื่อรับ Strategy เข้ามาทำงาน
 */
final readonly class PaymentGateway
{
    /**
     * ฉีด PaymentMethod เข้ามาใน Constructor เพื่อรองรับช่องทางชำระเงินใหม่ ๆ ได้อย่างยืดหยุ่น
     */
    public function __construct(
        private PaymentMethod $method
    ) {
    }

    /**
     * ดำเนินการชำระเงินตามช่องทางที่ถูกกำหนดเข้ามา
     */
    public function checkout(float $amount): string
    {
        // ตรวจสอบว่ายอดที่ต้องจ่ายต้องไม่ติดลบหรือเท่ากับ 0
        if ($amount <= 0) {
            throw new InvalidArgumentException("ยอดชำระเงินต้องมากกว่า 0 บาท");
        }

        // ส่งต่องานไปยังคลาสลูกตามสัญญากลางของ Interface
        return $this->method->pay($amount);
    }
}