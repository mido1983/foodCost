<?php
class User {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function register($username, $email, $password, $first_name = null, $last_name = null) {
        // Check if user already exists
        $user = $this->findByUsername($username);
        if ($user) {
            return ['success' => false, 'message' => 'Username already exists'];
        }

        $user = $this->findByEmail($email);
        if ($user) {
            return ['success' => false, 'message' => 'Email already exists'];
        }

        // Hash password
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // Insert new user
        $data = [
            'username' => $username,
            'email' => $email,
            'password' => $password_hash,
            'first_name' => $first_name,
            'last_name' => $last_name
        ];

        $userId = $this->db->insert('users', $data);

        if ($userId) {
            return ['success' => true, 'user_id' => $userId];
        } else {
            return ['success' => false, 'message' => 'Registration failed'];
        }
    }

    public function login($username, $password) {
        $user = $this->findByUsername($username);

        if (!$user) {
            return ['success' => false, 'message' => 'User not found'];
        }

        if (password_verify($password, $user['password'])) {
            Session::set('user_id', $user['id']);
            Session::set('username', $user['username']);
            Session::set('account_status', $user['account_status']);
            
            return ['success' => true, 'user' => $user];
        } else {
            return ['success' => false, 'message' => 'Invalid password'];
        }
    }

    public function logout() {
        Session::destroy();
    }

    public function findByUsername($username) {
        return $this->db->selectOne('SELECT * FROM users WHERE username = ?', [$username]);
    }

    public function findByEmail($email) {
        return $this->db->selectOne('SELECT * FROM users WHERE email = ?', [$email]);
    }

    public function findById($id) {
        return $this->db->selectOne('SELECT * FROM users WHERE id = ?', [$id]);
    }

    public function updateProfile($id, $data) {
        return $this->db->update('users', $data, ['id' => $id]);
    }

    public function upgradeAccount($userId, $accountType = 'premium') {
        return $this->db->update('users', ['account_status' => $accountType], ['id' => $userId]);
    }
} 