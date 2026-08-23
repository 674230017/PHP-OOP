<?php

declare(strict_types=1);

namespace StudentApp\Shared;

use InvalidArgumentException;
use Stringable;

/**
 * Value Object สำหรับอีเมล (Immutable: สร้างแล้วแก้ไขไม่ได้)
 */
final readonly class Email implements Stringable
{
    public string $value;

    /**
     * ซ่อน Constructor ให้เป็น private เพื่อบังคับให้สร้างผ่าน Static Factory Method
     */
    private function __construct(string $value)
    {
        // ตัดช่องว่างหน้าหลัง
        $trimmed = trim($value);

        // ตรวจสอบรูปแบบ Email ตามมาตรฐาน RFC
        if (!filter_var($trimmed, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("รูปแบบอีเมลไม่ถูกต้อง: {$trimmed}");
        }

        // ปรับเป็นตัวพิมพ์เล็กทั้งหมดเพื่อมาตรฐานเดียวกัน
        $this->value = strtolower($trimmed);
    }

    /**
     * Static Factory Method สำหรับสร้าง Email Instance หลังผ่านการตรวจสอบ
     */
    public static function fromString(string $value): self
    {
        return new self($value);
    }

    /**
     * เปรียบเทียบความเท่ากันด้วยค่าข้างใน (Value Equality)
     */
    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    /**
     * แปลงค่ากลับเป็น string
     */
    public function toString(): string
    {
        return $this->value;
    }

    /**
     * Magic Method ให้พิมพ์ Object ออกมาเป็น String ได้ทันที
     */
    public function __toString(): string
    {
        return $this->value;
    }
}