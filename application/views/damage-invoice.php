<!DOCTYPE html>
<html>

<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 13px;
        }

        .box {
            border: 1px solid #ddd;
            padding: 15px;
            margin: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
        }

        .text-right {
            text-align: right;
        }
    </style>
</head>

<body>
    <div class="box">
        <h2>Damage Invoice</h2>
        <p><strong>Damage Code:</strong> <?php echo $damage->damage_code; ?></p>
        <p><strong>Date:</strong> <?php echo $damage->damage_date; ?></p>
        <p><strong>Status:</strong> <?php echo ucfirst($damage->status); ?></p>
        <p><strong>Reason:</strong> <?php echo $damage->reason; ?></p>

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Item</th>
                    <th>Qty</th>
                    <th>Unit Cost</th>
                    <th>Value</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($damage_items as $i => $row) { ?>
                    <tr>
                        <td><?php echo $i + 1; ?></td>
                        <td><?php echo $row->item_code . ' - ' . $row->item_name; ?></td>
                        <td><?php echo $row->damage_qty; ?></td>
                        <td class="text-right"><?php echo $row->unit_cost; ?></td>
                        <td class="text-right"><?php echo $row->total_value; ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</body>

</html>