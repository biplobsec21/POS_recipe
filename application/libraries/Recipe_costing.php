<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Recipe_costing
{
    /**
     * Calculate total production cost for a batch from recipe ingredients and overheads.
     *
     * @param object $recipe
     * @param array $recipe_items
     * @param float|int $batch_quantity
     * @return array
     */
    public function calculate_batch_cost($recipe, $recipe_items, $batch_quantity)
    {
        $batch_quantity = (float) $batch_quantity;
        $ingredient_cost = 0.0;

        foreach ((array) $recipe_items as $item) {
            $quantity = isset($item->quantity) ? (float) $item->quantity : 0;
            $purchase_price = isset($item->purchase_price) ? (float) $item->purchase_price : 0;
            $ingredient_cost += $quantity * $purchase_price * $batch_quantity;
        }

        $overhead_cost = 0.0;
        $overhead_cost_type = isset($recipe->overhead_cost_type) ? $recipe->overhead_cost_type : 'fixed';
        $recipe_overhead = isset($recipe->overhead_cost) ? (float) $recipe->overhead_cost : 0;

        if ($overhead_cost_type === 'per_unit') {
            $overhead_cost = $recipe_overhead * $batch_quantity;
        } else {
            $overhead_cost = $recipe_overhead;
        }

        $total_cost = $ingredient_cost + $overhead_cost;
        $yield_quantity = isset($recipe->yield_quantity) ? (float) $recipe->yield_quantity : 0;
        $total_output_qty = $yield_quantity * $batch_quantity;
        $cost_per_unit = ($total_output_qty > 0) ? ($total_cost / $total_output_qty) : 0;

        return array(
            'ingredient_cost' => round($ingredient_cost, 4),
            'overhead_cost' => round($overhead_cost, 4),
            'total_cost' => round($total_cost, 4),
            'cost_per_unit' => round($cost_per_unit, 4),
            'total_output_qty' => round($total_output_qty, 4),
        );
    }
}
