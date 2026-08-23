<?php

declare(strict_types=1);

namespace StudentApp\Account;

/**
 * User Entity แทนตัวตนบัญชีของนักศึกษา
 */
final class User
{
    public function __construct(
        public readonly int $id,       // รหัสนักศึกษา / ID
        public readonly string $email   // อีเมลนักศึกษา
    ) {
    }
}

/**
 * Repository Interface เป็นสัญญากลางสำหรับการเข้าถึงและจัดเก็บข้อมูล User
 */
interface UserRepository
{
    public function save(User $user): void;
    public function findById(int $id): ?User;
    /** @return list<User> */
    public function all(): array;
}

/**
 * Concrete Repository ที่จำลองการบันทึกข้อมูลไว้ในหน่วยความจำ RAM (In-Memory)
 */
final class InMemoryUserRepository implements UserRepository
{
    /**
     * เก็บ Array ของ User โดยใช้ id เป็น Key
     * @var array<int, User>
     */
    private array $users = [];

    /**
     * บันทึกหรืออัปเดตข้อมูลผู้ใช้ลงใน Array
     */
    public function save(User $user): void
    {
        $this->users[$user->id] = $user;
    }

    /**
     * ค้นหาผู้ใช้จาก ID หากไม่พบจะคืนค่าเป็น null
     */
    public function findById(int $id): ?User
    {
        return $this->users[$id] ?? null;
    }

    /**
     * ดึงข้อมูลผู้ใช้ทั้งหมด
     * ใช้ array_values() เพื่อคืนค่า Array สำเนาชุดใหม่ ป้องกันไม่ให้ภายนอกแอบแก้ไข Key ภายใน
     */
    public function all(): array
    {
        return array_values($this->users);
    }
}