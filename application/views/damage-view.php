<!DOCTYPE html>
<html>

<head>
    <?php include "comman/code_css_form.php"; ?>
</head>

<body class="hold-transition skin-blue sidebar-mini">
    <div class="wrapper">
        <?php include "sidebar.php"; ?>
        <div class="content-wrapper">
            <section class="content-header">
                <h1>Damage Details</h1>
                <ol class="breadcrumb">
                    <li><a href="<?php echo $base_url; ?>dashboard"><i class="fa fa-dashboard"></i> Home</a></li>
                    <li><a href="<?php echo $base_url; ?>damage">Damage</a></li>
                    <li class="active">View</li>
                </ol>
            </section>

            <section class="content">
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title">Damage #<?php echo $damage->damage_code; ?></h3>
                        <div class="pull-right">
                            <a href="<?php echo $base_url; ?>damage/invoice/<?php echo $damage->id; ?>" class="btn btn-primary">Invoice</a>
                            <a href="<?php echo $base_url; ?>damage" class="btn btn-default">Back</a>
                        </div>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Date:</strong> <?php echo $damage->damage_date; ?></p>
                                <p><strong>Status:</strong> <?php echo ucfirst($damage->status); ?></p>
                                <p><strong>Warehouse:</strong> <?php echo isset($damage->warehouse_name) ? $damage->warehouse_name : '-'; ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Reason:</strong> <?php echo $damage->reason; ?></p>
                                <p><strong>Note:</strong> <?php echo $damage->note; ?></p>
                                <p><strong>Created By:</strong> <?php echo isset($damage->created_by_name) ? $damage->created_by_name : '-'; ?></p>
                            </div>
                        </div>
                        <table class="table table-bordered table-striped">
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
                                        <td><?php echo $row->unit_cost; ?></td>
                                        <td><?php echo $row->total_value; ?></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
        <?php include "footer.php"; ?>
    </div>
</body>

</html>