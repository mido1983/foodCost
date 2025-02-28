<?php
$current_page = 'calculator';
$page_title = 'View Cost Calculation';
require_once 'includes/config.php';

// Проверка наличия ID расчета
if (!isset($_GET['id']) || empty($_GET['id'])) {
    Session::setFlash('error', 'Calculation ID not specified');
    header('Location: ' . SITE_URL . '/calculator.php');
    exit;
}

$recipeId = intval($_GET['id']);

// Получение данных расчета
$calculator = new Calculator();
$recipe = $calculator->getRecipeById($recipeId);

if (!$recipe) {
    Session::setFlash('error', 'Calculation not found');
    header('Location: ' . SITE_URL . '/calculator.php');
    exit;
}

// Проверка прав доступа (только владелец или администратор)
if (!Session::isAdmin() && $recipe['user_id'] != Session::get('user_id')) {
    Session::setFlash('error', 'You do not have access to this calculation');
    header('Location: ' . SITE_URL . '/calculator.php');
    exit;
}

// Получение дополнительных данных
$ingredients = $calculator->getRecipeIngredients($recipeId);
$expenses = $calculator->getAdditionalExpenses($recipeId);
$costCalculation = $calculator->getCostCalculation($recipeId);

// Получение информации о единицах измерения
$batchUnit = $calculator->getUnitById($recipe['batch_unit_id']);

require_once 'includes/header.php';
?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= htmlspecialchars($recipe['name']) ?></h1>
        <div>
            <a href="calculator_pdf.php?id=<?= $recipeId ?>" class="btn btn-outline-primary">
                <i class="fas fa-file-pdf"></i> Export to PDF
            </a>
            <a href="calculator.php" class="btn btn-secondary ms-2">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>
    
    <div class="row">
        <div class="col-lg-8">
            <!-- Основная информация -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Основные параметры продукта</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <h6>Размер партии:</h6>
                            <p><?= $recipe['batch_size'] ?> <?= $batchUnit['short_name'] ?></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h6>Изменение веса при готовке:</h6>
                            <?php if ($recipe['weight_change_type'] === 'unchanged'): ?>
                                <p>Вес не меняется</p>
                            <?php elseif ($recipe['weight_change_type'] === 'loss'): ?>
                                <p>Усушка <?= $recipe['weight_change_percentage'] ?>%</p>
                            <?php else: ?>
                                <p>Увеличение веса на <?= $recipe['weight_change_percentage'] ?>%</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Выход готового продукта:</h6>
                            <p><?= $recipe['final_weight'] ?> <?= $batchUnit['short_name'] ?></p>
                        </div>
                        <div class="col-md-6">
                            <h6>Валюта:</h6>
                            <p><?= $recipe['currency'] ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Ингредиенты -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Ингредиенты</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Название</th>
                                    <th>Количество</th>
                                    <th class="text-end">Стоимость</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ingredients as $ingredient): ?>
                                <tr>
                                    <td><?= htmlspecialchars($ingredient['ingredient_name']) ?></td>
                                    <td><?= $ingredient['quantity'] ?> <?= $ingredient['unit_name'] ?></td>
                                    <td class="text-end"><?= number_format($ingredient['price'], 2) ?> <?= $recipe['currency'] ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="2">Итого по ингредиентам:</th>
                                    <th class="text-end"><?= number_format($costCalculation['ingredients_cost'], 2) ?> <?= $recipe['currency'] ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Дополнительные расходы -->
            <?php if (!empty($expenses)): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Дополнительные расходы</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Название</th>
                                    <th>Тип</th>
                                    <th class="text-end">Сумма</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($expenses as $expense): ?>
                                <tr>
                                    <td><?= htmlspecialchars($expense['name']) ?></td>
                                    <td><?= ucfirst($expense['expense_type']) ?></td>
                                    <td class="text-end"><?= number_format($expense['amount'], 2) ?> <?= $recipe['currency'] ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="2">Итого по дополнительным расходам:</th>
                                    <th class="text-end"><?= number_format($costCalculation['additional_expenses'], 2) ?> <?= $recipe['currency'] ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($recipe['notes'])): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Примечания</h5>
                </div>
                <div class="card-body">
                    <p><?= nl2br(htmlspecialchars($recipe['notes'])) ?></p>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="col-lg-4">
            <!-- Итоговый расчет -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Итоговый расчет себестоимости</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6>Себестоимость всей партии:</h6>
                        <h5><?= number_format($costCalculation['total_cost'], 2) ?> <?= $recipe['currency'] ?></h5>
                    </div>
                    
                    <div class="mb-3">
                        <h6>Себестоимость за единицу:</h6>
                        <h5><?= number_format($costCalculation['cost_per_unit'], 2) ?> <?= $recipe['currency'] ?>/<?= $batchUnit['short_name'] ?></h5>
                    </div>
                    
                    <div class="mb-3">
                        <h6>Наценка:</h6>
                        <p><?= $costCalculation['markup_percentage'] ?>%</p>
                    </div>
                    
                    <div>
                        <h6>Рекомендуемая цена продажи:</h6>
                        <h4 class="text-success"><?= number_format($costCalculation['suggested_price'], 2) ?> <?= $recipe['currency'] ?>/<?= $batchUnit['short_name'] ?></h4>
                    </div>
                </div>
                <div class="card-footer text-muted">
                    <small>Расчет создан: <?= date('d.m.Y H:i', strtotime($costCalculation['calculation_date'])) ?></small>
                </div>
            </div>
            
            <!-- Генерация этикетки -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Действия</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="label_generator.php?recipe_id=<?= $recipeId ?>" class="btn btn-success">
                            <i class="fas fa-tag"></i> Создать этикетку
                        </a>
                        <a href="calculator_edit.php?id=<?= $recipeId ?>" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Редактировать расчет
                        </a>
                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteCalculationModal">
                            <i class="fas fa-trash"></i> Удалить расчет
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно для подтверждения удаления -->
<div class="modal fade" id="deleteCalculationModal" tabindex="-1" aria-labelledby="deleteCalculationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteCalculationModalLabel">Подтверждение удаления</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Вы уверены, что хотите удалить этот расчет?</p>
                <p class="text-danger"><strong>Внимание:</strong> Это действие нельзя отменить.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <a href="calculator_delete.php?id=<?= $recipeId ?>" class="btn btn-danger">Удалить</a>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 