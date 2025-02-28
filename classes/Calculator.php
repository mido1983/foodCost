<?php
class Calculator {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Получение всех единиц измерения
     */
    public function getAllUnits() {
        return $this->db->select("SELECT * FROM measurement_units ORDER BY type, is_metric DESC, conversion_to_base");
    }
    
    /**
     * Получение единиц измерения по типу
     */
    public function getUnitsByType($type) {
        return $this->db->select(
            "SELECT * FROM measurement_units WHERE type = ? ORDER BY is_metric DESC, conversion_to_base", 
            [$type]
        );
    }
    
    /**
     * Получение списка ингредиентов
     */
    public function getAllIngredients() {
        return $this->db->select(
            "SELECT i.*, u.short_name as unit_name 
             FROM ingredients i 
             LEFT JOIN measurement_units u ON i.default_unit_id = u.id 
             ORDER BY i.name"
        );
    }
    
    /**
     * Добавление нового ингредиента
     */
    public function addIngredient($data) {
        return $this->db->insert('ingredients', $data);
    }
    
    /**
     * Сохранение рецепта
     */
    public function saveRecipe($recipeData, $ingredients, $expenses) {
        $this->db->beginTransaction();
        
        try {
            // Сохраняем основные данные рецепта
            $recipeId = $this->db->insert('recipes', $recipeData);
            
            // Сохраняем ингредиенты
            foreach ($ingredients as $ingredient) {
                $ingredient['recipe_id'] = $recipeId;
                $this->db->insert('recipe_ingredients', $ingredient);
            }
            
            // Сохраняем дополнительные расходы
            if (!empty($expenses)) {
                foreach ($expenses as $expense) {
                    $expense['recipe_id'] = $recipeId;
                    $this->db->insert('additional_expenses', $expense);
                }
            }
            
            // Сохраняем расчет себестоимости
            $calculationData = [
                'recipe_id' => $recipeId,
                'user_id' => $recipeData['user_id'],
                'total_cost' => $recipeData['total_cost'],
                'ingredients_cost' => $recipeData['ingredients_cost'],
                'additional_expenses' => $recipeData['additional_expenses_cost'] ?? 0,
                'cost_per_unit' => $recipeData['cost_per_unit'],
                'suggested_price' => $recipeData['suggested_price'],
                'markup_percentage' => $recipeData['markup_percentage'] ?? 30.00,
                'notes' => $recipeData['notes'] ?? null
            ];
            
            $this->db->insert('cost_calculations', $calculationData);
            
            $this->db->commit();
            return $recipeId;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    /**
     * Получение рецепта по ID
     */
    public function getRecipeById($id) {
        return $this->db->selectOne("SELECT * FROM recipes WHERE id = ?", [$id]);
    }
    
    /**
     * Получение ингредиентов для рецепта
     */
    public function getRecipeIngredients($recipeId) {
        return $this->db->select(
            "SELECT ri.*, i.name as ingredient_name, mu.short_name as unit_name 
             FROM recipe_ingredients ri 
             JOIN ingredients i ON ri.ingredient_id = i.id 
             LEFT JOIN measurement_units mu ON ri.unit_id = mu.id 
             WHERE ri.recipe_id = ?",
            [$recipeId]
        );
    }
    
    /**
     * Получение дополнительных расходов для рецепта
     */
    public function getAdditionalExpenses($recipeId) {
        return $this->db->select(
            "SELECT * FROM additional_expenses WHERE recipe_id = ?",
            [$recipeId]
        );
    }
    
    /**
     * Получение расчета себестоимости для рецепта
     */
    public function getCostCalculation($recipeId) {
        return $this->db->selectOne(
            "SELECT * FROM cost_calculations WHERE recipe_id = ? ORDER BY calculation_date DESC LIMIT 1",
            [$recipeId]
        );
    }
    
    /**
     * Получение всех рецептов пользователя
     */
    public function getUserRecipes($userId) {
        return $this->db->select(
            "SELECT r.*, cc.total_cost, cc.suggested_price 
             FROM recipes r 
             LEFT JOIN cost_calculations cc ON r.id = cc.recipe_id 
             WHERE r.user_id = ? 
             GROUP BY r.id 
             ORDER BY r.created_at DESC",
            [$userId]
        );
    }
    
    /**
     * Конвертация измерения между единицами
     */
    public function convertMeasurement($value, $fromUnitId, $toUnitId) {
        $fromUnit = $this->db->selectOne("SELECT * FROM measurement_units WHERE id = ?", [$fromUnitId]);
        $toUnit = $this->db->selectOne("SELECT * FROM measurement_units WHERE id = ?", [$toUnitId]);
        
        if (!$fromUnit || !$toUnit || $fromUnit['type'] !== $toUnit['type']) {
            throw new Exception("Невозможно конвертировать между несовместимыми единицами измерения");
        }
        
        // Конвертируем в базовую единицу, затем в целевую
        $valueInBaseUnit = $value * $fromUnit['conversion_to_base'];
        return $valueInBaseUnit / $toUnit['conversion_to_base'];
    }
    
    /**
     * Получение единицы измерения по ID
     */
    public function getUnitById($id) {
        return $this->db->selectOne("SELECT * FROM measurement_units WHERE id = ?", [$id]);
    }
} 