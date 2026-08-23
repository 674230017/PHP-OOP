<?php

declare(strict_types=1);

namespace StudentApp\Auth;

use InvalidArgumentException;
use StudentApp\Shared\Email; // นำ Email Value Object จากข้อ 6 มาใช้งาน

/**
 * Entity ผู้ใช้งานนักศึกษาพร้อม Password Hash
 */
final class User
{
    public function __construct(
        public readonly int $id,
        public readonly string $email,
        public readonly string $passwordHash
    ) {
    }
}

/**
 * Interface จัดเก็บข้อมูลผู้ใช้
 */
interface UserRepository
{
    public function save(User $user): void;
    public function findById(int $id): ?User;
    /** @return list<User> */
    public function all(): array;
}

final class InMemoryUserRepository implements UserRepository
{
    private array $users = [];

    public function save(User $user): void
    {
        $this->users[$user->id] = $user;
    }

    public function findById(int $id): ?User
    {
        return $this->users[$id] ?? null;
    }

    public function all(): array
    {
        return array_values($this->users);
    }
}

/**
 * Interface สำหรับเข้ารหัสรหัสผ่าน
 */
interface PasswordHasher
{
    public function hash(string $plainText): string;
}

/**
 * การแฮชรหัสผ่านด้วยอัลกอริทึมความปลอดภัยสูง Argon2id
 */
final readonly class ArgonPasswordHasher implements PasswordHasher
{
    public function hash(string $plainText): string
    {
        // ตรวจสอบความยาวรหัสผ่านเบื้องต้น
        if (strlen($plainText) < 6) {
            throw new InvalidArgumentException("รหัสผ่านต้องมีความยาวอย่างน้อย 6 ตัวอักษร");
        }

        // แปลงข้อความ Plaintext เป็น Password Hash ด้วย PASSWORD_ARGON2ID
        return password_hash($plainText, PASSWORD_ARGON2ID);
    }
}

/**
 * Use Case / Service สำหรับจัดการกระบวนการสมัครสมาชิกนักศึกษา
 */
final readonly class RegisterUserService
{
    /**
     * รับ Repository และ PasswordHasher ผ่าน Constructor Injection
     */
    public function __construct(
        private UserRepository $users,
        private PasswordHasher $hasher
    ) {
    }

    /**
     * ฟังก์ชันหลักในการลงทะเบียนผู้ใช้ใหม่
     */
    public function register(int $id, string $rawEmail, string $rawPassword): User
    {
        // ขั้นตอนที่ 1: ตรวจสอบและทำความสะอาด Email ด้วย Value Object
        $email = Email::fromString($rawEmail);

        // ขั้นตอนที่ 2: นำรหัสผ่านดิบไปแฮชเพื่อความปลอดภัย ไม่เก็บแบบ Plaintext
        $passwordHash = $this->hasher->hash($rawPassword);

        // ขั้นตอนที่ 3: ประกอบร่างเป็น User Entity
        $user = new User($id, $email->toString(), $passwordHash);

        // ขั้นตอนที่ 4: บันทึกข้อมูลลงในระบบผ่าน Repository
        $this->users->save($user);

        // คืนค่า User Entity ที่สร้างเสร็จสมบูรณ์
        return $user;
    }
}