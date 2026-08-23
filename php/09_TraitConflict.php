<?php

declare(strict_types=1);

namespace StudentApp\Registrar;

/**
 * Trait สำหรับการดึงเวลาประทับทั่วไป
 */
trait Timestampable
{
    public function timestamp(): string
    {
        return date('Y-m-d H:i:s'); // รูปแบบ: 2026-08-16 22:00:00
    }
}

/**
 * Trait สำหรับงานประวัติการตรวจสอบ (Audit)
 */
trait Auditable
{
    public function timestamp(): string
    {
        return date('c'); // รูปแบบ ISO 8601 สำหรับ Audit
    }

    public function auditMessage(): string
    {
        return "ตรวจสอบและอนุมัติเอกสารทะเบียนแล้ว";
    }
}

/**
 * เอกสารคำร้องของนักศึกษา
 */
final class Document
{
    /**
     * แก้ไขปัญหา Trait Conflict (เมื่อทั้ง 2 trait มีเมธอดชื่อ timestamp() เหมือนกัน):
     * 1. ใช้ 'insteadof' เพื่อบอกว่าให้เมธอด timestamp() หลักมาจาก Timestampable
     * 2. ใช้ 'as' เพื่อตั้งชื่อเล่น (alias) ให้เมธอด timestamp() ของ Auditable เป็น auditTimestamp()
     */
    use Timestampable, Auditable {
        Timestampable::timestamp insteadof Auditable;
        Auditable::timestamp as auditTimestamp;
    }

    /**
     * เวลาที่สร้างเอกสาร
     */
    public function createdAt(): string
    {
        return $this->timestamp();
    }

    /**
     * แสดงประวัติการ Audit พร้อมเวลาที่แน่นอน
     */
    public function auditLog(): string
    {
        return sprintf("[%s] สถานะ: %s", $this->auditTimestamp(), $this->auditMessage());
    }
}