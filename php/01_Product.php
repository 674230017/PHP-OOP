<?php

declare(strict_types=1);

namespace StudentApp\Shop;

use InvalidArgumentException;

/**
 * คลาส Product สำหรับจัดการสินค้าของร้านสหกรณ์นักศึกษา
 * ใช้ Asymmetric Visibility (PHP 8.4) เพื่อให้อ่านราคาได้ แต่ห้ามแก้ไขโดยตรงจากภายนอก
 */
final class Product
{
    /**
     * กำหนด Constructor Property Promotion
     * - id และ name: เป็น readonly เพื่อป้องกันไม่ให้ถูกแก้ไขค่าหลังจากสร้าง Object แล้ว
     * - price: กำหนดเป็น public private(set) ทำให้อ่านได้แบบ public แต่แก้ค่าได้เฉพาะ method ภายในคลาส
     */
    public function __construct(
        public readonly int $id,           // รหัสสินค้า
        public readonly string $name,       // ชื่อสินค้า
        public private(set) float $price    // ราคาสินค้า (จำกัดสิทธิ์แก้ไขเฉพาะภายใน)
    ) {
        // ตรวจสอบและกำหนดราคาเริ่มต้นผ่านเมธอด validatePrice
        $this->price = $this->validatePrice($price);
    }

    /**
     * คำนวณและปรับลดราคาสินค้าตามเปอร์เซ็นต์ส่วนลด
     *
     * @param float $percent เปอร์เซ็นต์ส่วนลด เช่น 10.0 คือลด 10%
     * @throws InvalidArgumentException หากเปอร์เซ็นต์ส่วนลดไม่อยู่ในช่วง 0 - 100
     */
    public function applyDiscount(float $percent): void
    {
        // ตรวจสอบเงื่อนไขเปอร์เซ็นต์ส่วนลดว่าถูกต้องตามหลักคณิตศาสตร์หรือไม่
        if ($percent < 0 || $percent > 100) {
            throw new InvalidArgumentException("เปอร์เซ็นต์ส่วนลดต้องอยู่ระหว่าง 0 ถึง 100");
        }

        // คำนวณจำนวนเงินส่วนลด
        $discountAmount = $this->price * ($percent / 100.0);

        // นำราคาเดิมลบด้วยส่วนลด และตรวจสอบความถูกต้องของราคาก่อนเซ็ตค่าใหม่
        $this->price = $this->validatePrice($this->price - $discountAmount);
    }

    /**
     * คืนค่าราคาในรูปแบบข้อความพร้อมหน่วยเงินและทศนิยม 2 ตำแหน่ง
     */
    public function displayPrice(): string
    {
        return number_format($this->price, 2) . " THB";
    }

    /**
     * เมธอดภายในสำหรับตรวจสอบว่าราคาต้องไม่ติดลบ (Data Invariant)
     */
    private function validatePrice(float $price): float
    {
        if ($price < 0) {
            throw new InvalidArgumentException("ราคาสินค้าต้องไม่ติดลบ");
        }
        return $price;
    }
}