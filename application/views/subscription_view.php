<!DOCTYPE html>
<html>
<head>
    <!-- TABLES CSS CODE -->
    <?php include "comman/code_css_datatable.php"; ?>
</head>

<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
    <!-- Left side column. contains the logo and sidebar -->
    <?php include "sidebar.php"; ?>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1>
                <?=$page_title;?>
                <small>View/Search Subscriptions</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="<?php echo $base_url; ?>dashboard"><i class="fa fa-dashboard"></i> Home</a></li>
                <li class="active"><?=$page_title;?></li>
            </ol>
        </section>

        <!-- Main content -->
        <?= form_open('#', array('class' => '', 'id' => 'table_form')); ?>
        <input type="hidden" id="base_url" value="<?=$base_url;?>">
        <section class="content">
            <div class="row">
                <!-- ********** ALERT MESSAGE START******* -->
                <?php include "comman/code_flashdata.php"; ?>
                <!-- ********** ALERT MESSAGE END******* -->
                <div class="col-xs-12">
                    <div class="box">
                        <div class="box-header with-border">
                            <h3 class="box-title"><?=$page_title;?></h3>
                            <?php if($CI->permissions('subscription_add')) { ?>
                            
                            <?php } ?>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">
                            <table id="subscription_table" class="table table-bordered table-striped" width="100%">
                                <thead class="bg-primary">
                                    <tr>
                                        
                                        <th>Transaction ID</th>
                                        <th>Company Name</th>
                                        <th>Subscription Status</th>
                                        <th>Created At</th>
                                    </tr>
                                </thead>
                                <tbody>
                <?php if (!empty($subscriptions)): ?>
                    <?php foreach ($subscriptions as $subscription): ?>
                        <tr>
                                                        <td><?php echo $subscription['transaction_id']; ?></td>

                            <td><?php echo $subscription['company_name']; ?></td>
                            <td><?php echo $subscription['subscription_status']; ?></td>
                            <td><?php echo $subscription['created_at']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center">No subscription data found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
                            </table>
                        </div>
                        <!-- /.box-body -->
                    </div>
                    <!-- /.box -->
                </div>
                <!-- /.col -->
            </div>
            <!-- /.row -->
        </section>
        <!-- /.content -->
        <?= form_close(); ?>
    </div>
    <!-- /.content-wrapper -->
    <?php include "footer.php"; ?>
    <div class="control-sidebar-bg"></div>
</div>
<!-- ./wrapper -->

<!-- SOUND CODE -->
<?php include "comman/code_js_sound.php"; ?>
<!-- TABLES CODE -->
<?php include "comman/code_js_datatable.php"; ?>



<script src="<?php echo $theme_link; ?>js/subscription.js"></script>
<!-- Make sidebar menu highlighter/selector -->
<script>$(".<?php echo basename(__FILE__, '.php'); ?>-active-li").addClass("active");</script>
</body>
</html>
