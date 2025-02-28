<?php
/**
 * Логирование действий администратора
 * 
 * @param string $action Тип действия (create, update, delete)
 * @param string $entity_type Тип сущности (user, post, category)
 * @param int $entity_id ID сущности
 * @param array $details Дополнительные детали (опционально)
 * @return bool Успешно ли залогировано действие
 */
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
            // created_at заполняется автоматически в базе данных
        ];
        
        return $db->insert('admin_logs', $data);
    } catch (Exception $e) {
        // В случае ошибки просто записываем в лог и продолжаем
        error_log('Ошибка логирования действия администратора: ' . $e->getMessage());
        return false;
    }
}

/**
 * Получить логи действий администратора
 * 
 * @param int $limit Ограничение количества записей
 * @param int $offset Смещение для пагинации
 * @return array Массив логов
 */
function getAdminLogs($limit = 100, $offset = 0) {
    $db = Database::getInstance();
    $sql = "SELECT al.*, u.username 
            FROM admin_logs al
            LEFT JOIN users u ON al.admin_id = u.id  /* Используем admin_id вместо user_id */
            ORDER BY al.created_at DESC
            LIMIT ? OFFSET ?";
    
    return $db->select($sql, [$limit, $offset]);
}

/**
 * Получить количество действий админа за указанный период
 * 
 * @param string $start_date Начальная дата в формате Y-m-d
 * @param string $end_date Конечная дата в формате Y-m-d
 * @return int Количество действий
 */
function getAdminActionsCount($start_date = null, $end_date = null) {
    $db = Database::getInstance();
    $params = [];
    $where = "";
    
    if ($start_date && $end_date) {
        $where = " WHERE DATE(created_at) BETWEEN ? AND ?";
        $params = [$start_date, $end_date];
    }
    
    $sql = "SELECT COUNT(*) as total FROM admin_logs" . $where;
    $result = $db->selectOne($sql, $params);
    
    return $result ? $result['total'] : 0;
} 