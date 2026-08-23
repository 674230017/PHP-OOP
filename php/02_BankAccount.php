<?php

declare(strict_types=1);

namespace StudentApp\Wallet;

use InvalidArgumentException;
use RuntimeException;

/**
 * Domain Exception เฉพาะเจาะจงสำหรับกรณียอดเงินในบัญชีไม่เพียงพอ
 * สืบทอดจาก RuntimeException เพื่อใช้บอกข้อผิดพลาดทางธุรกิจ (Business Rule)
 */
final class InsufficientFunds extends RuntimeException
{
}

/**
 * คลาส BankAccount กระเป๋าเงินอิเล็กทรอนิกส์ของนักศึกษา
 */
final class BankAccount
{
    /**
     * ยอดเงินคงเหลือในบัญชี
     * ภายนอกอ่านค่าได้ (public) แต่จะแก้ไขค่าได้เฉพาะผ่านเมธอด deposit/withdraw ภายในคลาสเท่านั้น
     */
    public private(set) float $balance = 0.0;

    /**
     * สร้างบัญชีใหม่พร้อมระบุเลขที่บัญชีที่ไม่สามารถแก้ไขได้
     */
    public function __construct(
        public readonly string $accountNumber
    ) {
    }

    /**
     * ฝากเงินเข้ากระเป๋าเงิน
     *
     * @param float $amount จำนวนเงินที่ต้องการฝาก
     */
    public function deposit(float $amount): void
    {
        // ตรวจสอบว่ายอดเงินที่ฝากต้องเป็นค่าบวกเสมอ
        $this->assertPositive($amount);

        // เพิ่มยอดเงินเข้าไปในยอดคงเหลือ
        $this->balance += $amount;
    }

    /**
     * ถอนเงินออกจากกระเป๋าเงิน
     *
     * @param float $amount จำนวนเงินที่ต้องการถอน
     * @throws InsufficientFunds ถ้ายอดเงินคงเหลือมีไม่พอถอน
     */
    public function withdraw(float $amount): void
    {
        // ตรวจสอบว่ายอดเงินที่ถอนต้องเป็นค่าบวกเสมอ
        $this->assertPositive($amount);

        // ตรวจสอบว่ายอดเงินในบัญชีมีเพียงพอหรือไม่
        if ($amount > $this->balance) {
            throw new InsufficientFunds("ยอดเงินในบัญชี {$this->accountNumber} ไม่เพียงพอสำหรับการทำรายการ");
        }

        // หักเงินออกจากยอดคงเหลือ
        $this->balance -= $amount;
    }

    /**
     * โอนเงินไปยังบัญชีกระเป๋าเงินของนักศึกษาคนอื่น
     *
     * @param self $target บัญชีปลายทางที่ต้องการโอนไป
     * @param float $amount จำนวนเงินที่ต้องการโอน
     */
    public function transferTo(self $target, float $amount): void
    {
        // ทำการถอนเงินจากบัญชีตัวเองก่อน (หากเงินไม่พอ จะ Throw Exception และหยุดทำงานทันที)
        $this->withdraw($amount);

        // ฝากเงินเข้าบัญชีปลายทาง
        $target->deposit($amount);
    }

    /**
     * เมธอดช่วยตรวจสอบว่าจำนวนเงินต้องมากกว่า 0 บาท
     */
    private function assertPositive(float $amount): void
    {
        if ($amount <= 0) {
            throw new InvalidArgumentException("จำนวนเงินต้องมากกว่า 0 บาท");
        }
    }
}