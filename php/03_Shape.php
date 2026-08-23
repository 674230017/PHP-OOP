<?php

declare(strict_types=1);

namespace StudentApp\Media;

use InvalidArgumentException;

/**
 * Interface แม่แบบสำหรับรูปทรงสื่อสิ่งพิมพ์ประชาสัมพันธ์
 * เพื่อให้คลาสอื่นสามารถคำนวณพื้นที่ได้ผ่าน Polymorphism โดยไม่ต้องเช็คชนิดคลาส
 */
interface Shape
{
    /**
     * คำนวณพื้นที่ของรูปทรง
     */
    public function area(): float;
}

/**
 * สติกเกอร์หรือป้ายประชาสัมพันธ์ทรงกลม
 */
final readonly class Circle implements Shape
{
    /**
     * รับค่ารัศมีและป้องกันการแก้ไข (readonly)
     */
    public function __construct(private float $radius)
    {
        // ป้องกันการใส่ค่ารัศมีที่ติดลบหรือเท่ากับ 0
        if ($this->radius <= 0) {
            throw new InvalidArgumentException("รัศมีต้องมากกว่า 0");
        }
    }

    /**
     * คำนวณพื้นที่วงกลม: π * r^2
     */
    public function area(): float
    {
        return M_PI * ($this->radius ** 2);
    }
}

/**
 * ป้ายประชาสัมพันธ์รูปทรงสี่เหลี่ยม
 */
final readonly class Rectangle implements Shape
{
    /**
     * รับค่าความกว้างและความสูง
     */
    public function __construct(
        private float $width,
        private float $height
    ) {
        // ตรวจสอบความถูกต้องของมิติรูปทรง
        if ($this->width <= 0 || $this->height <= 0) {
            throw new InvalidArgumentException("ความกว้างและความสูงต้องมากกว่า 0");
        }
    }

    /**
     * คำนวณพื้นที่สี่เหลี่ยม: กว้าง * ยาว
     */
    public function area(): float
    {
        return $this->width * $this->height;
    }
}