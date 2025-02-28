<?php
require_once 'includes/config.php';

// Проверка авторизации для сохранения расчетов
if (!Session::isLoggedIn()) {
    Session::setFlash('error', 'Для сохранения расчетов необходимо войти в систему');
    header('Location: ' . SITE_URL . '/calculator.php');
    exit;
}

// Проверка метода запроса
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . SITE_URL . '/calculator.php');
    exit;
}

// Получаем данные формы
$recipeName = trim($_POST['recipe_name'] ?? '');
$batchSize = floatval($_POST['batch_size'] ?? 0);
$batchUnitId = intval($_POST['batch_unit_id'] ?? 0);
$weightChangeType = $_POST['weight_change_type'] ?? 'unchanged';
$weightChangePercentage = floatval($_POST['weight_change_percentage'] ?? 0);
$finalWeight = floatval($_POST['final_weight'] ?? 0);
$currency = $_POST['currency'] ?? 'USD';
$markupPercentage = floatval($_POST['markup_percentage'] ?? 30);
$notes = trim($_POST['notes'] ?? '');

$totalCost = floatval($_POST['total_cost'] ?? 0);
$ingredientsCost = floatval($_POST['ingredients_cost'] ?? 0);
$additionalExpensesCost = floatval($_POST['additional_expenses_cost'] ?? 0);
$costPerUnit = floatval($_POST['cost_per_unit'] ?? 0);
$suggestedPrice = floatval($_POST['suggested_price'] ?? 0);

// Ингредиенты
$ingredientsRaw = $_POST['ingredients'] ?? [];
$ingredients = [];

foreach ($ingredientsRaw as $key => $ingredient) {
    $ingredients[] = [
        'ingredient_id' => intval($ingredient['id']),
        'quantity' => floatval($ingredient['quantity']),
        'unit_id' => intval($ingredient['unit_id']),
        'price' => floatval($ingredient['price'])
    ];
}

// Дополнительные расходы
$expensesRaw = $_POST['expenses'] ?? [];
$expenses = [];

foreach ($expensesRaw as $type => $amount) {
    if (floatval($amount) > 0) {
        $expenses[] = [
            'name' => ucfirst(str_replace('_', ' ', $type)),
            'amount' => floatval($amount),
            'expense_type' => $type
        ];
    }
}

// Подготовка данных для сохранения
$recipeData = [
    'name' => $recipeName,
    'user_id' => Session::get('user_id'),
    'batch_size' => $batchSize,
    'batch_unit_id' => $batchUnitId,
    'final_weight' => $finalWeight,
    'weight_change_type' => $weightChangeType,
    'weight_change_percentage' => $weightChangePercentage,
    'currency' => $currency,
    'total_cost' => $totalCost,
    'ingredients_cost' => $ingredientsCost,
    'additional_expenses_cost' => $additionalExpensesCost,
    'cost_per_unit' => $costPerUnit,
    'suggested_price' => $suggestedPrice,
    'markup_percentage' => $markupPercentage,
    'notes' => $notes
];

// Сохранение расчета
try {
    $calculator = new Calculator();
    $recipeId = $calculator->saveRecipe($recipeData, $ingredients, $expenses);
    
    Session::setFlash('success', 'Расчет себестоимости успешно сохранен');
    header('Location: ' . SITE_URL . '/calculator_view.php?id=' . $recipeId);
    exit;
} catch (Exception $e) {
    Session::setFlash('error', 'Ошибка при сохранении расчета: ' . $e->getMessage());
    header('Location: ' . SITE_URL . '/calculator.php');
    exit;
} 