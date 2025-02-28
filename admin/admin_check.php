<?php
require_once __DIR__ . '/../includes/config.php';

// Проверяем, авторизован ли пользователь
if (!Session::isLoggedIn()) {
    Session::setFlash('error', 'You must log in to access the admin area');
    header('Location: ' . SITE_URL . '/login.php');
    exit;
}

// Проверяем, имеет ли пользователь права администратора
$user = new User();
$currentUser = $user->findById(Session::get('user_id'));

if (!$currentUser || $currentUser['role'] !== 'admin') {
    Session::setFlash('error', 'You do not have permission to access the admin area');
    header('Location: ' . SITE_URL . '/index.php');
    exit;
}

// Объявляем функцию только если она еще не существует
if (!function_exists('logAdminAction')) {
    function logAdminAction($action, $entity_type, $entity_id, $details = []) {
        try {
            $db = Database::getInstance();
            
            $data = [
                'admin_id' => Session::get('user_id'),
                'action' => $action,
                'entity_type' => $entity_type,
                'entity_id' => $entity_id,
                'details' => !empty($details) ? json_encode($details) : null,
                'ip_address' => $_SERVER['REMOTE_ADDR']
            ];
            
            return $db->insert('admin_logs', $data);
        } catch (Exception $e) {
            error_log('Ошибка логирования действия администратора: ' . $e->getMessage());
            return false;
        }
    }
} 