<?php
require_once '../includes/config.php';

// Проверка метода запроса
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    exit(json_encode(['success' => false, 'message' => 'Method Not Allowed']));
}

// Проверка авторизации
if (!Session::isLoggedIn()) {
    header('HTTP/1.1 401 Unauthorized');
    exit(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

// Получение данных
$name = trim($_POST['name'] ?? '');
$price = floatval($_POST['price'] ?? 0);
$unitId = intval($_POST['unit_id'] ?? 0);

// Валидация
if (empty($name)) {
    exit(json_encode(['success' => false, 'message' => 'Название ингредиента не может быть пустым']));
}

if ($unitId <= 0) {
    exit(json_encode(['success' => false, 'message' => 'Необходимо выбрать единицу измерения']));
}

// Сохранение ингредиента
try {
    $calculator = new Calculator();
    
    $ingredientData = [
        'name' => $name,
        'price_per_unit' => $price,
        'default_unit_id' => $unitId
    ];
    
    $ingredientId = $calculator->addIngredient($ingredientData);
    
    // Получение информации о единице измерения
    $unit = $calculator->getUnitById($unitId);
    
    // Возвращаем результат
    $result = [
        'success' => true,
        'ingredient' => [
            'id' => $ingredientId,
            'name' => $name,
            'price_per_unit' => $price,
            'default_unit_id' => $unitId,
            'unit_name' => $unit['short_name']
        ]
    ];
    
    exit(json_encode($result));
    
} catch (Exception $e) {
    exit(json_encode(['success' => false, 'message' => $e->getMessage()]));
} 