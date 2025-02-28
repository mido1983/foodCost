// Инициализация при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    // Инициализация выбора ингредиентов
    if (document.getElementById('ingredient-select')) {
        document.getElementById('ingredient-select').addEventListener('change', calculateIngredientPrice);
    }
    
    if (document.getElementById('ingredient-quantity')) {
        document.getElementById('ingredient-quantity').addEventListener('input', calculateIngredientPrice);
    }
    
    // Добавление ингредиента в список
    if (document.getElementById('add-ingredient-btn')) {
        document.getElementById('add-ingredient-btn').addEventListener('click', function() {
            const ingredientSelect = document.getElementById('ingredient-select');
            const ingredientQuantity = document.getElementById('ingredient-quantity');
            const ingredientPrice = document.getElementById('ingredient-price');
            
            if (!ingredientSelect.value || !ingredientQuantity.value) {
                alert('Please select an ingredient and specify quantity');
                return;
            }
            
            const selectedOption = ingredientSelect.options[ingredientSelect.selectedIndex];
            const ingredientId = ingredientSelect.value;
            const ingredientName = selectedOption.text;
            const quantity = parseFloat(ingredientQuantity.value);
            const price = parseFloat(ingredientPrice.value);
            const unitId = selectedOption.dataset.unitId;
            const unitName = selectedOption.dataset.unitName;
            
            addIngredientToList(ingredientId, ingredientName, quantity, price, unitId, unitName);
            
            // Сброс формы после добавления
            ingredientSelect.selectedIndex = 0;
            ingredientQuantity.value = '';
            ingredientPrice.value = '';
        });
    }
    
    // Обработка изменения типа изменения веса
    if (document.getElementById('weight_change_type')) {
        document.getElementById('weight_change_type').addEventListener('change', function() {
            const weightChangeDetails = document.getElementById('weight-change-details');
            const weightChangeHelp = document.getElementById('weight-change-help');
            
            if (this.value === 'unchanged') {
                weightChangeDetails.style.display = 'none';
            } else {
                weightChangeDetails.style.display = 'flex';
                
                if (this.value === 'loss') {
                    weightChangeHelp.textContent = 'Specify the percentage of weight loss';
                } else {
                    weightChangeHelp.textContent = 'Specify the percentage of weight gain';
                }
            }
            
            calculateFinalWeight();
        });
    }
    
    // Обработка изменения процента изменения веса
    if (document.getElementById('weight_change_percentage')) {
        document.getElementById('weight_change_percentage').addEventListener('input', calculateFinalWeight);
    }
    
    // Обработка изменения размера партии
    if (document.getElementById('batch_size')) {
        document.getElementById('batch_size').addEventListener('input', function() {
            calculateFinalWeight();
            calculateTotal();
        });
    }
    
    // Обработка изменения единицы измерения партии
    document.getElementById('batch_unit').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const unitShortName = selectedOption.textContent.match(/\(([^)]+)\)/)[1];
        
        document.getElementById('final-weight-unit').textContent = unitShortName;
        document.getElementById('unit-label').textContent = unitShortName;
        document.getElementById('price-unit-label').textContent = unitShortName;
        document.getElementById('final-output-unit').textContent = unitShortName;
        
        calculateTotal();
    });
    
    // Обработка изменения наценки
    document.getElementById('markup_percentage').addEventListener('input', calculateSuggestedPrice);
    
    // Обработка изменения валюты
    document.getElementById('currency').addEventListener('change', function() {
        const currencySymbols = {
            'USD': '$',
            'EUR': '€',
            'RUB': '₽',
            'UAH': '₴'
        };
        
        const symbol = currencySymbols[this.value] || this.value;
        
        document.querySelectorAll('.currency-symbol').forEach(el => {
            el.textContent = symbol;
        });
    });
    
    // Обработка изменения дополнительных расходов
    document.querySelectorAll('.expense-input').forEach(input => {
        input.addEventListener('input', calculateTotal);
    });
    
    // Инициализация модального окна для добавления нового ингредиента
    document.getElementById('add-new-ingredient-btn').addEventListener('click', function() {
        const modal = new bootstrap.Modal(document.getElementById('addIngredientModal'));
        modal.show();
    });
    
    // Обработка отправки формы нового ингредиента
    if (document.getElementById('saveNewIngredient')) {
        document.getElementById('saveNewIngredient').addEventListener('click', function() {
            const name = document.getElementById('new_ingredient_name').value;
            const price = document.getElementById('new_ingredient_price').value;
            const unitId = document.getElementById('new_ingredient_unit').value;
            
            if (!name) {
                alert('Ingredient name cannot be empty');
                return;
            }
            
            // AJAX запрос для сохранения ингредиента
            const formData = new FormData();
            formData.append('name', name);
            formData.append('price', price);
            formData.append('unit_id', unitId);
            
            fetch('ajax/add_ingredient.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const ingredient = data.ingredient;
                    const option = document.createElement('option');
                    option.value = ingredient.id;
                    option.text = ingredient.name;
                    option.dataset.unitId = ingredient.default_unit_id;
                    option.dataset.unitName = ingredient.unit_name;
                    option.dataset.price = ingredient.price_per_unit;
                    
                    document.getElementById('ingredient-select').appendChild(option);
                    
                    // Закрытие модального окна
                    const modal = bootstrap.Modal.getInstance(document.getElementById('addIngredientModal'));
                    modal.hide();
                    
                    // Очистка формы
                    document.getElementById('new_ingredient_name').value = '';
                    document.getElementById('new_ingredient_price').value = '0';
                    
                    // Выбор нового ингредиента
                    document.getElementById('ingredient-select').value = ingredient.id;
                    document.getElementById('ingredient-select').dispatchEvent(new Event('change'));
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while saving the ingredient');
            });
        });
    }
});

// Функция добавления ингредиента в список
function addIngredientToList(ingredientId, ingredientName, quantity, price, unitId, unitName) {
    const container = document.getElementById('ingredients-container');
    const rowId = 'ingredient-row-' + Date.now();
    
    const ingredientRow = document.createElement('div');
    ingredientRow.id = rowId;
    ingredientRow.className = 'row mb-2 ingredient-row';
    ingredientRow.innerHTML = `
        <input type="hidden" name="ingredients[${rowId}][id]" value="${ingredientId}">
        <input type="hidden" name="ingredients[${rowId}][unit_id]" value="${unitId}">
        <input type="hidden" class="ingredient-price-input" name="ingredients[${rowId}][price]" value="${price.toFixed(2)}">
        
        <div class="col-md-4">
            <input type="text" class="form-control" value="${ingredientName}" readonly>
        </div>
        <div class="col-md-3">
            <div class="input-group">
                <input type="number" step="0.001" min="0.001" class="form-control ingredient-quantity" 
                       name="ingredients[${rowId}][quantity]" value="${quantity}" data-row="${rowId}">
                <span class="input-group-text">${unitName}</span>
            </div>
        </div>
        <div class="col-md-3">
            <div class="input-group">
                <input type="text" class="form-control ingredient-cost" value="${price.toFixed(2)}" readonly>
                <span class="input-group-text currency-symbol">$</span>
            </div>
        </div>
        <div class="col-md-2">
            <button type="button" class="btn btn-danger btn-sm w-100 remove-ingredient" data-row="${rowId}">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    `;
    
    container.appendChild(ingredientRow);
    
    // Отображаем футер ингредиентов
    document.getElementById('ingredients-footer').style.display = 'block';
    
    // Добавляем обработчики событий
    const quantityInput = ingredientRow.querySelector('.ingredient-quantity');
    quantityInput.addEventListener('input', function() {
        updateIngredientPrice(this.dataset.row);
    });
    
    const removeButton = ingredientRow.querySelector('.remove-ingredient');
    removeButton.addEventListener('click', function() {
        document.getElementById(this.dataset.row).remove();
        calculateTotal();
        
        // Скрываем футер, если нет ингредиентов
        if (document.querySelectorAll('.ingredient-row').length === 0) {
            document.getElementById('ingredients-footer').style.display = 'none';
        }
    });
    
    // Обновляем общую стоимость
    calculateTotal();
}

// Функция расчета стоимости выбранного ингредиента
function calculateIngredientPrice() {
    const ingredientSelect = document.getElementById('ingredient-select');
    const ingredientQuantity = document.getElementById('ingredient-quantity');
    const ingredientPrice = document.getElementById('ingredient-price');
    
    if (!ingredientSelect.value || !ingredientQuantity.value) {
        ingredientPrice.value = '';
        return;
    }
    
    const selectedOption = ingredientSelect.options[ingredientSelect.selectedIndex];
    const pricePerUnit = parseFloat(selectedOption.dataset.price);
    const quantity = parseFloat(ingredientQuantity.value);
    
    const totalPrice = pricePerUnit * quantity;
    ingredientPrice.value = totalPrice.toFixed(2);
}

// Функция обновления стоимости ингредиента в списке
function updateIngredientPrice(rowId) {
    const row = document.getElementById(rowId);
    const quantityInput = row.querySelector('.ingredient-quantity');
    const costInput = row.querySelector('.ingredient-cost');
    const priceInput = row.querySelector('.ingredient-price-input');
    
    // Получаем текущую цену и количество
    const quantity = parseFloat(quantityInput.value) || 0;
    const basePrice = parseFloat(priceInput.value) / parseFloat(quantityInput.defaultValue);
    
    // Рассчитываем новую стоимость
    const newPrice = basePrice * quantity;
    costInput.value = newPrice.toFixed(2);
    priceInput.value = newPrice.toFixed(2);
    
    // Обновляем общую стоимость
    calculateTotal();
}

// Функция расчета общей стоимости
function calculateTotal() {
    let totalIngredientsPrice = 0;
    let totalExpensesPrice = 0;
    
    // Подсчет стоимости ингредиентов
    document.querySelectorAll('.ingredient-price-input').forEach(input => {
        totalIngredientsPrice += parseFloat(input.value) || 0;
    });
    
    // Отображение стоимости ингредиентов
    document.getElementById('total-ingredients-cost').textContent = totalIngredientsPrice.toFixed(2);
    document.getElementById('ingredients_cost_input').value = totalIngredientsPrice.toFixed(2);
    
    // Подсчет дополнительных расходов
    document.querySelectorAll('.expense-input').forEach(input => {
        totalExpensesPrice += parseFloat(input.value) || 0;
    });
    
    // Отображение дополнительных расходов
    document.getElementById('total-expenses-cost').textContent = totalExpensesPrice.toFixed(2);
    document.getElementById('additional_expenses_cost_input').value = totalExpensesPrice.toFixed(2);
    
    // Скрываем или показываем футер дополнительных расходов
    document.getElementById('additional-expenses-footer').style.display = 
        totalExpensesPrice > 0 ? 'block' : 'none';
    
    // Общая себестоимость
    const totalCost = totalIngredientsPrice + totalExpensesPrice;
    document.getElementById('total-cost').textContent = totalCost.toFixed(2);
    document.getElementById('total_cost_input').value = totalCost.toFixed(2);
    
    // Расчет себестоимости за единицу
    calculateUnitCost(totalCost);
    
    // Расчет рекомендуемой цены продажи
    calculateSuggestedPrice();
}

// Функция расчета итогового веса
function calculateFinalWeight() {
    const batchSize = parseFloat(document.getElementById('batch_size').value) || 0;
    const weightChangeType = document.getElementById('weight_change_type').value;
    const weightChangePercentage = parseFloat(document.getElementById('weight_change_percentage').value) || 0;
    const finalWeightInput = document.getElementById('final_weight');
    const finalOutputSpan = document.getElementById('final-output');
    
    let finalWeight = batchSize;
    
    if (weightChangeType === 'loss') {
        finalWeight = batchSize * (1 - weightChangePercentage / 100);
    } else if (weightChangeType === 'gain') {
        finalWeight = batchSize * (1 + weightChangePercentage / 100);
    }
    
    finalWeightInput.value = finalWeight.toFixed(2);
    finalOutputSpan.textContent = finalWeight.toFixed(2);
    
    // Пересчет себестоимости за единицу при изменении веса
    calculateTotal();
}

// Функция расчета себестоимости за единицу
function calculateUnitCost(totalCost) {
    const finalWeight = parseFloat(document.getElementById('final_weight').value) || 0;
    
    if (finalWeight <= 0) {
        document.getElementById('cost-per-unit').textContent = '0.00';
        document.getElementById('cost_per_unit_input').value = '0.00';
        return;
    }
    
    const costPerUnit = totalCost / finalWeight;
    document.getElementById('cost-per-unit').textContent = costPerUnit.toFixed(2);
    document.getElementById('cost_per_unit_input').value = costPerUnit.toFixed(2);
}

// Функция расчета рекомендуемой цены продажи
function calculateSuggestedPrice() {
    const costPerUnit = parseFloat(document.getElementById('cost_per_unit_input').value) || 0;
    const markupPercentage = parseFloat(document.getElementById('markup_percentage').value) || 0;
    
    const suggestedPrice = costPerUnit * (1 + markupPercentage / 100);
    
    document.getElementById('suggested-price').textContent = suggestedPrice.toFixed(2);
    document.getElementById('suggested_price_input').value = suggestedPrice.toFixed(2);
} 