<?php

declare(strict_types=1);

namespace StudentApp\Notification;

use InvalidArgumentException;

/**
 * Interface สำหรับช่องทางการส่งข้อความ
 */
interface NotificationChannel
{
    public function send(string $recipient, string $message): void;
}

/**
 * ช่องทางการส่งอีเมล
 */
final readonly class EmailChannel implements NotificationChannel
{
    public function send(string $recipient, string $message): void
    {
        echo "[Email ส่งถึง {$recipient}]: {$message}\n";
    }
}

/**
 * ช่องทางการส่ง SMS
 */
final readonly class SmsChannel implements NotificationChannel
{
    public function send(string $recipient, string $message): void
    {
        echo "[SMS ส่งถึง {$recipient}]: {$message}\n";
    }
}

/**
 * ศูนย์กลางการส่งการแจ้งเตือนไปยังหลาย ๆ ช่องทาง
 */
final readonly class Notifier
{
    /**
     * รับช่องทางการส่งแบบ iterable (Array หรือ Generator ก็ได้)
     * @param iterable<NotificationChannel> $channels
     */
    public function __construct(
        private iterable $channels
    ) {
    }

    /**
     * กระจายข้อความไปยังทุกช่องทางที่ลงทะเบียนไว้
     */
    public function notify(string $recipient, string $message): void
    {
        // ตรวจสอบว่าผู้รับและข้อความต้องไม่เป็นค่าว่าง
        if (trim($recipient) === '' || trim($message) === '') {
            throw new InvalidArgumentException("ผู้รับและข้อความต้องไม่เป็นค่าว่าง");
        }

        // วนลูปส่งข้อความออกไปยังทุก Channel โดยไม่ต้องเช็ค instanceof
        foreach ($this->channels as $channel) {
            $channel->send($recipient, $message);
        }
    }
}