<?php
class User {
    public int $id;
    public string $username;
    public string $role;

    public function __construct(int $id, string $username, string $role) {
        $this->id = $id;
        $this->username = $username;
        $this->role = $role;
    }

    public function isAdmin(): bool {
        return $this->role === 'ADMIN';
    }
}