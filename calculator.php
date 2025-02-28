<?php
$current_page = 'calculator';
$page_title = 'Cost Calculator';
require_once 'includes/config.php';

// Получение списка единиц измерения и ингредиентов
$calculator = new Calculator();
$weightUnits = $calculator->getUnitsByType('weight');
$volumeUnits = $calculator->getUnitsByType('volume');
$quantityUnits = $calculator->getUnitsByType('quantity');
$ingredients = $calculator->getAllIngredients();

// Получение сохраненных рецептов пользователя
$userRecipes = [];
if (Session::isLoggedIn()) {
    $userRecipes = $calculator->getUserRecipes(Session::get('user_id'));
}

require_once 'includes/header.php';
?>

<div class="container py-5">
    <h1 class="mb-4">Cost Calculator</h1>
    
    <div class="row">
        <div class="col-lg-3">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Saved Calculations</h5>
                </div>
                <div class="card-body">
                    <?php if (Session::isLoggedIn()): ?>
                        <?php if (empty($userRecipes)): ?>
                            <p class="text-muted">You don't have any saved calculations yet.</p>
                        <?php else: ?>
                            <div class="list-group">
                                <?php foreach ($userRecipes as $recipe): ?>
                                    <a href="calculator_view.php?id=<?= $recipe['id'] ?>" class="list-group-item list-group-item-action">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1"><?= htmlspecialchars($recipe['name']) ?></h6>
                                            <small><?= date('m/d/Y', strtotime($recipe['created_at'])) ?></small>
                                        </div>
                                        <small class="text-muted">
                                            Cost: <?= number_format($recipe['total_cost'], 2) ?> <?= $recipe['currency'] ?>
                                        </small>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <p class="text-muted">Sign in to see your saved calculations.</p>
                        <a href="login.php" class="btn btn-outline-primary btn-sm">Sign In</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-lg-9">
            <form id="calculatorForm" method="post" action="calculator_result.php">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Product Basic Parameters</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="recipe_name" class="form-label">Product/Recipe Name</label>
                                <input type="text" class="form-control" id="recipe_name" name="recipe_name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="currency" class="form-label">Currency</label>
                                <select class="form-select" id="currency" name="currency">
                                    <option value="USD">USD ($)</option>
                                    <option value="EUR">EUR (€)</option>
                                    <option value="RUB">RUB (₽)</option>
                                    <option value="UAH">UAH (₴)</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="batch_size" class="form-label">Batch Size</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" min="0.01" class="form-control" id="batch_size" name="batch_size" value="1" required>
                                    <select class="form-select" id="batch_unit" name="batch_unit_id" style="max-width: 120px;">
                                        <optgroup label="Weight">
                                            <?php foreach ($weightUnits as $unit): ?>
                                                <option value="<?= $unit['id'] ?>" <?= $unit['short_name'] === 'kg' ? 'selected' : '' ?>>
                                                    <?= $unit['name'] ?> (<?= $unit['short_name'] ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </optgroup>
                                        <optgroup label="Volume">
                                            <?php foreach ($volumeUnits as $unit): ?>
                                                <option value="<?= $unit['id'] ?>">
                                                    <?= $unit['name'] ?> (<?= $unit['short_name'] ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </optgroup>
                                        <optgroup label="Quantity">
                                            <?php foreach ($quantityUnits as $unit): ?>
                                                <option value="<?= $unit['id'] ?>">
                                                    <?= $unit['name'] ?> (<?= $unit['short_name'] ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </optgroup>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="weight_change_type" class="form-label">Weight Change Type</label>
                                <select class="form-select" id="weight_change_type" name="weight_change_type">
                                    <option value="unchanged" selected>Weight doesn't change</option>
                                    <option value="loss">Weight Loss</option>
                                    <option value="gain">Weight Gain</option>
                                </select>
                                <div id="weight-change-details" class="mt-2" style="display: none;">
                                    <div class="input-group">
                                        <input type="number" step="0.1" min="0" max="100" class="form-control" id="weight_change_percentage" name="weight_change_percentage" value="0">
                                        <span class="input-group-text">%</span>
                                    </div>
                                    <div class="form-text" id="weight-change-help"></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <label class="form-label">Final Product Output</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="final_weight" name="final_weight" value="1.00" readonly>
                                    <span class="input-group-text" id="final-weight-unit">kg</span>
                                </div>
                                <div class="form-text">
                                    After weight loss/gain, output will be <span id="final-output">1.00</span> <span id="final-output-unit">kg</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Ingredients</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <div class="d-flex justify-content-between mb-2">
                                <h6>Add Ingredient</h6>
                                <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#addIngredientModal">
                                    <i class="fas fa-plus"></i> New Ingredient
                                </button>
                            </div>
                            <div class="row g-2">
                                <div class="col-md-5">
                                    <select class="form-select" id="ingredient-select">
                                        <option value="">Select an ingredient...</option>
                                        <?php foreach ($ingredients as $ingredient): ?>
                                            <option value="<?= $ingredient['id'] ?>"
                                                    data-price="<?= $ingredient['price_per_unit'] ?>"
                                                    data-unit-id="<?= $ingredient['default_unit_id'] ?>"
                                                    data-unit-name="<?= $ingredient['unit_name'] ?>">
                                                <?= htmlspecialchars($ingredient['name']) ?> (<?= number_format($ingredient['price_per_unit'], 2) ?> per <?= $ingredient['unit_name'] ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <div class="input-group">
                                        <input type="number" step="0.01" min="0" class="form-control" id="ingredient-quantity" placeholder="Quantity">
                                        <span class="input-group-text" id="ingredient-unit">-</span>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="ingredient-price" placeholder="Price" readonly>
                                        <span class="input-group-text currency-symbol">$</span>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <button type="button" id="add-ingredient-btn" class="btn btn-primary w-100">Add</button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-bordered" id="ingredients-table">
                                <thead class="table-light">
                                    <tr>
                                        <th>Ingredient</th>
                                        <th>Quantity</th>
                                        <th>Price</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="ingredients-list">
                                    <!-- Ingredients will be added via JavaScript -->
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <th colspan="2">Total Ingredients Cost:</th>
                                        <th class="text-end"><span id="total-ingredients-cost">0.00</span> <span class="currency-symbol">$</span></th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                            <input type="hidden" id="ingredients_cost_input" name="ingredients_cost" value="0">
                        </div>
                    </div>
                </div>
                
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Additional Expenses</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6 col-lg-4">
                                <label for="packaging_cost" class="form-label">Packaging</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" min="0" class="form-control expense-input" id="packaging_cost" name="expenses[packaging]" value="0">
                                    <span class="input-group-text currency-symbol">$</span>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <label for="electricity_cost" class="form-label">Electricity</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" min="0" class="form-control expense-input" id="electricity_cost" name="expenses[electricity]" value="0">
                                    <span class="input-group-text currency-symbol">$</span>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <label for="water_cost" class="form-label">Water</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" min="0" class="form-control expense-input" id="water_cost" name="expenses[water]" value="0">
                                    <span class="input-group-text currency-symbol">$</span>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <label for="gas_cost" class="form-label">Gas</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" min="0" class="form-control expense-input" id="gas_cost" name="expenses[gas]" value="0">
                                    <span class="input-group-text currency-symbol">$</span>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <label for="labor_cost" class="form-label">Labor Cost</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" min="0" class="form-control expense-input" id="labor_cost" name="expenses[labor]" value="0">
                                    <span class="input-group-text currency-symbol">$</span>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <label for="rent_cost" class="form-label">Rent</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" min="0" class="form-control expense-input" id="rent_cost" name="expenses[rent]" value="0">
                                    <span class="input-group-text currency-symbol">$</span>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <label for="other_cost" class="form-label">Other Expenses</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" min="0" class="form-control expense-input" id="other_cost" name="expenses[other]" value="0">
                                    <span class="input-group-text currency-symbol">$</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer" id="additional-expenses-footer" style="display: none;">
                        <div class="d-flex justify-content-between">
                            <strong>Total Additional Expenses:</strong>
                            <span><span id="total-expenses-cost">0.00</span> <span class="currency-symbol">$</span></span>
                        </div>
                        <input type="hidden" id="additional_expenses_cost_input" name="additional_expenses_cost" value="0">
                    </div>
                </div>
                
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Final Calculation</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6>Total Cost of Entire Batch:</h6>
                                <h4><span id="total-cost">0.00</span> <span class="currency-symbol">$</span></h4>
                                <input type="hidden" id="total_cost_input" name="total_cost" value="0">
                            </div>
                            <div class="col-md-6">
                                <h6>Cost per Unit:</h6>
                                <h4><span id="cost-per-unit">0.00</span> <span class="currency-symbol">$</span>/<span id="unit-label">kg</span></h4>
                                <input type="hidden" id="cost_per_unit_input" name="cost_per_unit" value="0">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <label for="markup_percentage" class="form-label">Markup:</label>
                                <div class="input-group mb-3">
                                    <input type="number" step="1" min="0" max="1000" class="form-control" id="markup_percentage" name="markup_percentage" value="30">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6>Suggested Selling Price:</h6>
                                <h3 class="text-success"><span id="suggested-price">0.00</span> <span class="currency-symbol">$</span>/<span id="price-unit-label">kg</span></h3>
                                <input type="hidden" id="suggested_price_input" name="suggested_price" value="0">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes:</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                        </div>
                        
                        <div class="d-flex justify-content-end">
                            <button type="reset" class="btn btn-secondary me-2">Reset</button>
                            <button type="submit" class="btn btn-success">Save Calculation</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal for adding a new ingredient -->
<div class="modal fade" id="addIngredientModal" tabindex="-1" aria-labelledby="addIngredientModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addIngredientModalLabel">Add New Ingredient</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="newIngredientForm">
                    <div class="mb-3">
                        <label for="new_ingredient_name" class="form-label">Ingredient Name</label>
                        <input type="text" class="form-control" id="new_ingredient_name" required>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="new_ingredient_price" class="form-label">Price per Unit</label>
                            <div class="input-group">
                                <input type="number" step="0.01" min="0" class="form-control" id="new_ingredient_price" value="0">
                                <span class="input-group-text currency-symbol">$</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="new_ingredient_unit" class="form-label">Unit of Measurement</label>
                            <select class="form-select" id="new_ingredient_unit">
                                <optgroup label="Weight">
                                    <?php foreach ($weightUnits as $unit): ?>
                                        <option value="<?= $unit['id'] ?>" <?= $unit['short_name'] === 'kg' ? 'selected' : '' ?>>
                                            <?= $unit['short_name'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </optgroup>
                                <optgroup label="Volume">
                                    <?php foreach ($volumeUnits as $unit): ?>
                                        <option value="<?= $unit['id'] ?>">
                                            <?= $unit['short_name'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </optgroup>
                                <optgroup label="Quantity">
                                    <?php foreach ($quantityUnits as $unit): ?>
                                        <option value="<?= $unit['id'] ?>">
                                            <?= $unit['short_name'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </optgroup>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="saveNewIngredient" class="btn btn-primary">Save</button>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

<script src="<?= SITE_URL ?>/assets/js/calculator.js"></script> 