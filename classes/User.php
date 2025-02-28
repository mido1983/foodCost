<?php
class User {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function register($username, $email, $password, $first_name = null, $last_name = null) {
        // Проверка на запрещенные имена пользователей
        if ($this->isUsernameProhibited($username)) {
            return ['success' => false, 'message' => 'This username is not allowed. Please choose a different username.'];
        }
        
        // Check if user already exists
        $user = $this->findByUsername($username);
        if ($user) {
            return ['success' => false, 'message' => 'Username already exists'];
        }

        $user = $this->findByEmail($email);
        if ($user) {
            return ['success' => false, 'message' => 'Email already exists'];
        }

        // Set admin role for special email
        $role = ($email === 'michael.doroshenko1@gmail.com') ? 'admin' : 'user';

        // Hash password
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // Insert new user
        $data = [
            'username' => $username,
            'email' => $email,
            'password' => $password_hash,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'role' => $role
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
        $query = "SELECT * FROM users WHERE id = ?";
        return $this->db->selectOne($query, [$id]);
    }

    public function updateProfile($id, $data) {
        return $this->db->update('users', $data, ['id' => $id]);
    }

    public function upgradeAccount($userId, $accountType = 'premium') {
        return $this->db->update('users', ['account_status' => $accountType], ['id' => $userId]);
    }

    public function isAdmin() {
        return $this->findById(Session::get('user_id'))['role'] === 'admin';
    }

    public function getRole() {
        return $this->findById(Session::get('user_id'))['role'];
    }

    public function setRole($userId, $role) {
        if (!in_array($role, ['user', 'admin', 'editor', 'moderator'])) {
            return false;
        }
        return $this->db->update('users', ['role' => $role], ['id' => $userId]);
    }

    // Метод проверки запрещенных имен пользователей
    public function isUsernameProhibited($username) {
        $prohibited_terms = [
            'admin', 'administrator', 'moderator', 'mod', 'support',
            'helpdesk', 'staff', 'official', 'foodcost', 'системный',
            'админ', 'администратор', 'модератор', 'поддержка'
        ];
        
        $username = strtolower($username);
        
        foreach ($prohibited_terms as $term) {
            if (strpos($username, $term) !== false) {
                return true;
            }
        }
        
        return false;
    }

    public function getAllUsers($limit = null, $offset = null) {
        $sql = 'SELECT * FROM users ORDER BY created_at DESC';
        $params = [];
        
        if ($limit !== null) {
            $sql .= ' LIMIT ?';
            $params[] = $limit;
            
            if ($offset !== null) {
                $sql .= ' OFFSET ?';
                $params[] = $offset;
            }
        }
        
        return $this->db->select($sql, $params);
    }

    public function countTotalUsers() {
        $result = $this->db->selectOne('SELECT COUNT(*) as count FROM users');
        return $result['count'];
    }

    public function deleteUser($id) {
        return $this->db->delete('users', ['id' => $id]);
    }
} 