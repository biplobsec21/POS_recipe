<!DOCTYPE html>
<html>
<title><?= $page_title; ?> - Receipt</title>

<head>
    <link rel='shortcut icon' href='<?php echo $theme_link; ?>images/favicon.ico' />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

    <style>
        table,
        th,
        td {
            border: 1px solid black;
            border-collapse: collapse;
            font-family: 'Open Sans', 'Martel Sans', sans-serif;
        }

        th,
        td {
            padding: 5px;
            text-align: left;
            vertical-align: top
        }

        body {
            word-wrap: break-word;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }
    </style>
</head>

<body onload="window.print();"><!--  -->

    <?php

    $q1 = $this->db->query("select * from db_company where id=1 and status=1");
    $res1 = $q1->row();
    $company_name = $res1->company_name;
    $company_mobile = $res1->mobile;
    $company_phone = $res1->phone;
    $company_email = $res1->email;
    $company_country = $res1->country;
    $company_state = $res1->state;
    $company_city = $res1->city;
    $company_address = $res1->address;
    $company_gst_no = $res1->gst_no;
    $company_vat_no = $res1->vat_no;

    $recipe_id = (int) $recipe_id;

    $recipe = $this->db->query("
    SELECT r.*, i.item_name AS output_product_name
    FROM recipes r
    LEFT JOIN db_items i ON i.id = r.output_product_id
    WHERE r.id = " . $recipe_id . "
  ")->row();

    if (!$recipe) {
        echo "Recipe not found.";
        exit;
    }

    $recipe_items = $this->db->query("
    SELECT ri.*, i.item_name, i.purchase_price, i.stock,
           (SELECT unit_name FROM db_units WHERE id = i.unit_id) AS unit_name
    FROM recipe_items ri
    LEFT JOIN db_items i ON i.id = ri.item_id
    WHERE ri.recipe_id = " . $recipe_id . "
    ORDER BY ri.id ASC
  ")->result();

    $ingredient_cost = 0;
    ?>

    <table align="center" width="100%" height='100%'>
        <thead>
            <tr>
        <th colspan="6" style="padding-left: 15px;">
          <b><?php echo $company_name; ?></b><br />
          <?php echo $this->lang->line('address') . " : " . $company_address; ?>,
          <?php echo $company_city . " " . $company_state . " " . $company_country; ?><br />
          <?php echo $this->lang->line('mobile') . ":" . $company_mobile; ?><br />
          <?php echo (!empty(trim($company_email))) ? $this->lang->line('email') . ": " . $company_email . "<br>" : ''; ?>
          <?php echo (!empty(trim($company_gst_no))) ? $this->lang->line('gst_number') . ": " . $company_gst_no . "<br>" : ''; ?>
          <?php echo (!empty(trim($company_vat_no))) ? $this->lang->line('vat_number') . ": " . $company_vat_no . "<br>" : ''; ?>
        </th>
      </tr>
      <tr>
        <th colspan="6"><?= $this->lang->line('date'); ?> : <?= show_date(date('Y-m-d')); ?></th>

            <tr style="background-color: #f2f2f2;">
                <td colspan="6">
                    <table width="100%" style="border: none;">
                        <tr>
                            <td width="50%" style="border: none;"><b><?= $this->lang->line('recipe_name'); ?> :</b> <?= $recipe->recipe_name; ?></td>
                            <td width="50%" style="border: none;"><b><?= $this->lang->line('output_product'); ?> :</b> <?= $recipe->output_product_name; ?></td>
                        </tr>
                        <tr>
                            <td style="border: none;"><b><?= $this->lang->line('yield_quantity'); ?> :</b> <?= $recipe->yield_quantity; ?></td>
                            <td style="border: none;"><b><?= $this->lang->line('created_at'); ?> :</b> <?= show_date($recipe->created_at); ?></td>
                        </tr>
                        <?php if (!empty(trim($recipe->notes))) { ?>
                            <tr>
                                <td colspan="2" style="border: none;"><b><?= $this->lang->line('notes'); ?> :</b> <?= nl2br(htmlspecialchars($recipe->notes)); ?></td>
                            </tr>
                        <?php } ?>
                    </table>
                </td>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="6" style="padding: 0;">
                    <table width="100%" style="border: none;">
                        <tr style="background-color: #e8e8e8;">
                            <th width="5%" class="text-center">#</th>
                            <th width="35%"><?= $this->lang->line('item_name'); ?></th>
                            <th width="15%" class="text-right"><?= $this->lang->line('quantity'); ?></th>
                            <th width="10%"><?= $this->lang->line('unit'); ?></th>
                            <th width="15%" class="text-right"><?= $this->lang->line('unit_cost'); ?></th>
                            <th width="20%" class="text-right"><?= $this->lang->line('total_cost'); ?></th>
                        </tr>
                        <?php
                        $i = 1;
                        foreach ($recipe_items as $item) {
                            $line_total = (float) $item->quantity * (float) $item->purchase_price;
                            $ingredient_cost += $line_total;
                        ?>
                            <tr>
                                <td class="text-center"><?= $i++; ?></td>
                                <td><?= $item->item_name; ?></td>
                                <td class="text-right"><?= number_format($item->quantity, 2); ?></td>
                                <td><?= $item->unit_name; ?></td>
                                <td class="text-right"><?= $CURRENCY . number_format($item->purchase_price, 2); ?></td>
                                <td class="text-right"><?= $CURRENCY . number_format($line_total, 2); ?></td>
                            </tr>
                        <?php } ?>
                    </table>
                </td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6" style="padding: 0;">
                    <table width="100%" style="border: none;">
                        <?php
                        $overhead_cost = (float) $recipe->overhead_cost;
                        $overhead_type = $recipe->overhead_cost_type === 'per_unit' ? 'per_unit' : 'fixed';
                        if ($overhead_type === 'per_unit') {
                            $overhead_total = $overhead_cost * (float) $recipe->yield_quantity;
                        } else {
                            $overhead_total = $overhead_cost;
                        }
                        $total_cost = $ingredient_cost + $overhead_total;
                        $cost_per_unit = ((float) $recipe->yield_quantity > 0) ? ($total_cost / (float) $recipe->yield_quantity) : 0;
                        ?>
                        <tr>
                            <td width="60%" style="border: none;" class="text-right"><b><?= $this->lang->line('total_ingredients_cost'); ?> :</b></td>
                            <td width="40%" style="border: none;" class="text-right"><b><?= $CURRENCY . number_format($ingredient_cost, 2); ?></b></td>
                        </tr>
                        <tr>
                            <td style="border: none;" class="text-right"><b><?= $this->lang->line('overhead_cost'); ?> (<?= ucfirst($overhead_type); ?>) :</b></td>
                            <td style="border: none;" class="text-right"><b><?= $CURRENCY . number_format($overhead_total, 2); ?></b></td>
                        </tr>
                        <tr style="background-color: #f2f2f2;">
                            <td style="border: none;" class="text-right"><b><?= $this->lang->line('total_cost'); ?> :</b></td>
                            <td style="border: none;" class="text-right"><b><?= $CURRENCY . number_format($total_cost, 2); ?></b></td>
                        </tr>
                        <tr>
                            <td style="border: none;" class="text-right"><b><?= $this->lang->line('cost_per_unit'); ?> :</b></td>
                            <td style="border: none;" class="text-right"><b><?= $CURRENCY . number_format($cost_per_unit, 2); ?></b></td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td colspan="3" style="height:80px;">
                    <b><?= $this->lang->line('prepared_by'); ?></b><br />&nbsp;<br />&nbsp;<br />&nbsp;<br />
                </td>
                <td colspan="3">
                    <b><?= $this->lang->line('authorised_signature'); ?></b><br /><br /><br /><br />
                </td>
            </tr>
        </tfoot>
    </table>

</body>

</html>