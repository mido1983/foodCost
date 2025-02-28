<?php
require_once __DIR__ . '/../includes/config.php';

// Проверяем, авторизован ли пользователь
if (!Session::isLoggedIn()) {
    Session::setFlash('error', 'You must be logged in to access the admin area');
    header('Location: ' . SITE_URL . '/login.php');
    exit;
}

// Проверяем, имеет ли пользователь права администратора
$user = new User();
if (!$user->isAdmin()) {
    Session::setFlash('error', 'You do not have permission to access the admin area');
    header('Location: ' . SITE_URL);
    exit;
}

// Функция для логирования действий администратора
function logAdminAction($action, $entityType = null, $entityId = null, $details = null) {
    $adminStats = new AdminStats();
    $adminStats->logAdminAction(Session::get('user_id'), $action, $entityType, $entityId, $details);
} 