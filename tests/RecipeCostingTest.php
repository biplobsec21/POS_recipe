<?php
if (!defined('BASEPATH')) {
    define('BASEPATH', '');
}

if (!class_exists('MY_Controller')) {
    class MY_Controller {}
}

require_once __DIR__ . '/../application/libraries/Recipe_costing.php';
require_once __DIR__ . '/../application/controllers/Production.php';

function assertScenario($label, $recipe, $recipeItems, $batchQuantity, $expected)
{
    $calculator = new Recipe_costing();
    $result = $calculator->calculate_batch_cost($recipe, $recipeItems, $batchQuantity);

    foreach ($expected as $key => $value) {
        if ($result[$key] != $value) {
            fwrite(STDERR, "FAIL [$label] $key expected $value but got {$result[$key]}\n");
            exit(1);
        }
    }

    echo "PASS [$label] " . json_encode($result) . PHP_EOL;
}

$fixedRecipe = (object) [
    'yield_quantity' => 2,
    'overhead_cost' => 10,
    'overhead_cost_type' => 'fixed',
];

$fixedRecipeItems = [
    (object) [
        'quantity' => 1,
        'purchase_price' => 50,
    ],
];

assertScenario('fixed overhead', $fixedRecipe, $fixedRecipeItems, 1, [
    'ingredient_cost' => 50.0,
    'overhead_cost' => 10.0,
    'total_cost' => 60.0,
    'cost_per_unit' => 30.0,
    'total_output_qty' => 2.0,
]);

$perUnitRecipe = (object) [
    'yield_quantity' => 2,
    'overhead_cost' => 3,
    'overhead_cost_type' => 'per_unit',
];

assertScenario('per-unit overhead', $perUnitRecipe, $fixedRecipeItems, 2, [
    'ingredient_cost' => 100.0,
    'overhead_cost' => 6.0,
    'total_cost' => 106.0,
    'cost_per_unit' => 26.5,
    'total_output_qty' => 4.0,
]);

$controller = new class extends Production {
    public function __construct()
    {
    }

    public function exposeBuildCostBreakdown($recipe, $recipeItems, $batchQuantity)
    {
        return $this->_build_cost_breakdown($recipe, $recipeItems, $batchQuantity);
    }

    public function exposeBuildCostBreakdownForBatch($recipe, $recipeItems, $productionBatch)
    {
        return $this->_build_cost_breakdown_for_batch($recipe, $recipeItems, $productionBatch);
    }

    public function exposeShouldBlockCostIncrease($currentCost, $newCost, $thresholdPercent)
    {
        return $this->_should_block_cost_increase($currentCost, $newCost, $thresholdPercent);
    }
};

$controllerResult = $controller->exposeBuildCostBreakdown($fixedRecipe, $fixedRecipeItems, 1);
if ($controllerResult['total_cost'] != 60.0) {
    fwrite(STDERR, "FAIL [controller fixed overhead] expected total cost 60 but got {$controllerResult['total_cost']}\n");
    exit(1);
}

echo "PASS [controller fixed overhead] " . json_encode($controllerResult) . PHP_EOL;

$controllerPerUnitResult = $controller->exposeBuildCostBreakdown($perUnitRecipe, $fixedRecipeItems, 2);
if ($controllerPerUnitResult['cost_per_unit'] != 26.5) {
    fwrite(STDERR, "FAIL [controller per-unit overhead] expected cost per unit 26.5 but got {$controllerPerUnitResult['cost_per_unit']}\n");
    exit(1);
}

echo "PASS [controller per-unit overhead] " . json_encode($controllerPerUnitResult) . PHP_EOL;

$batchObject = (object) ['batch_quantity' => 3];
$batchResult = $controller->exposeBuildCostBreakdownForBatch($fixedRecipe, $fixedRecipeItems, $batchObject);
if ($batchResult['total_output_qty'] != 6.0) {
    fwrite(STDERR, "FAIL [controller batch quantity] expected total output qty 6 but got {$batchResult['total_output_qty']}\n");
    exit(1);
}

echo "PASS [controller batch quantity] " . json_encode($batchResult) . PHP_EOL;

$thresholdBlock = $controller->exposeShouldBlockCostIncrease(20, 25, 10);
if ($thresholdBlock !== true) {
    fwrite(STDERR, "FAIL [cost threshold] expected block for 25% increase over 20 with 10% threshold\n");
    exit(1);
}

echo "PASS [cost threshold] block triggered" . PHP_EOL;
