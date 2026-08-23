<?php

declare(strict_types=1);

namespace StudentApp\Shop;

use InvalidArgumentException;

/**
 * รายการสินค้าแต่ละชิ้นในใบสั่งซื้อ (Item Line)
 */
final readonly class OrderItem
{
    public function __construct(
        public string $name,        // ชื่อสินค้า
        public int $quantity,       // จำนวนที่ซื้อ
        public float $unitPrice     // ราคาต่อหน่วย
    ) {
        // ตรวจสอบจำนวนและราคาไม่ให้ติดลบ
        if ($this->quantity <= 0) {
            throw new InvalidArgumentException("จำนวนสินค้าต้องมากกว่า 0 ชิ้น");
        }
        if ($this->unitPrice < 0) {
            throw new InvalidArgumentException("ราคาสินค้าต้องไม่ติดลบ");
        }
    }

    /**
     * คำนวณยอดรวมของรายการสินค้านี้ (จำนวน * ราคาต่อหน่วย)
     */
    public function total(): float
    {
        return $this->quantity * $this->unitPrice;
    }
}

/**
 * Strategy Interface สำหรับการคำนวณส่วนลด
 */
interface DiscountPolicy
{
    public function discount(float $subtotal): float;
}

/**
 * นโยบายส่วนลดแบบเปอร์เซ็นต์สำหรับนักศึกษา
 */
final readonly class PercentageDiscount implements DiscountPolicy
{
    public function __construct(private float $percent)
    {
        if ($this->percent < 0 || $this->percent > 100) {
            throw new InvalidArgumentException("เปอร์เซ็นต์ส่วนลดต้องอยู่ระหว่าง 0 ถึง 100");
        }
    }

    /**
     * คำนวณจำนวนเงินส่วนลดที่จะหักออก
     */
    public function discount(float $subtotal): float
    {
        return $subtotal * ($this->percent / 100.0);
    }
}

/**
 * Order ใบสั่งซื้อของร้านสวัสดิการ
 */
final class Order
{
    /**
     * รับรายการสินค้าทั้งหมด และนโยบายส่วนลดผ่าน Dependency Injection
     * @param list<OrderItem> $items
     */
    public function __construct(
        private array $items,
        private readonly DiscountPolicy $discountPolicy
    ) {
    }

    /**
     * คำนวณยอดรวมก่อนหักส่วนลด (Subtotal)
     */
    public function subtotal(): float
    {
        // ใช้ array_reduce รวมยอดเงินของสินค้าทุกชิ้นในคำสั่งซื้อ
        return array_reduce(
            $this->items,
            static fn(float $sum, OrderItem $item): float => $sum + $item->total(),
            0.0
        );
    }

    /**
     * คำนวณยอดสุทธิหลังหักส่วนลด (Net Total)
     */
    public function total(): float
    {
        $subtotal = $this->subtotal();
        // ขอส่วนลดจาก DiscountPolicy
        $discount = $this->discountPolicy->discount($subtotal);

        // ป้องกันกรณียอดติดลบด้วยการใช้ max() ให้ต่ำสุดคือ 0.0 บาท
        return max(0.0, $subtotal - $discount);
    }
}