<?php
require_once __DIR__ . '/User.php';

class RepairService {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function login(string $username, string $password): ?User {
        $stmt = $this->db->prepare("SELECT user_id, username, role FROM users WHERE username = :username AND password = :password");
        $stmt->execute([':username' => $username, ':password' => $password]);
        $data = $stmt->fetch();

        if ($data) {
            return new User($data['user_id'], $data['username'], $data['role']);
        }
        return null;
    }

    public function createRequest(int $userId, string $title, string $description): bool {
        if (empty(trim($title)) || empty(trim($description))) {
            return false;
        }
        $stmt = $this->db->prepare("INSERT INTO repair_requests (user_id, title, description) VALUES (:user_id, :title, :description)");
        return $stmt->execute([
            ':user_id'     => $userId,
            ':title'       => trim($title),
            ':description' => trim($description)
        ]);
    }

    public function getRequestsByUser(int $userId): array {
        $stmt = $this->db->prepare("
            SELECT r.*, u.username 
            FROM repair_requests r 
            JOIN users u ON r.user_id = u.user_id 
            WHERE r.user_id = :user_id 
            ORDER BY r.created_at DESC
        ");
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function getAllRequests(): array {
        $stmt = $this->db->prepare("
            SELECT r.*, u.username 
            FROM repair_requests r 
            JOIN users u ON r.user_id = u.user_id 
            ORDER BY r.created_at DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function updateStatus(int $requestId, string $status): bool {
        $allowed = ['PENDING', 'IN_PROGRESS', 'COMPLETED', 'CANCELLED'];
        if (!in_array($status, $allowed, true)) {
            return false;
        }
        $stmt = $this->db->prepare("UPDATE repair_requests SET status = :status WHERE request_id = :request_id");
        return $stmt->execute([':status' => $status, ':request_id' => $requestId]);
    }
}