<!DOCTYPE html>
<html>

<head>
    <!-- TABLES CSS CODE -->
    <?php include"comman/code_css_form.php"; ?>
    <!-- </copy> -->
</head>

<body class="hold-transition skin-blue sidebar-mini">
    <div class="wrapper">

        <?php include"sidebar.php"; ?>

        <?php

	if(!isset($customer_name)){
    $customer_name=$mobile=$phone=$email=$country_id=$state_id=$city=
    $postcode=$address=$supplier_code=$gstin=$tax_number=
    $state_code=$customer_code=$company_name=$company_mobile=$opening_balance='';
	}
 ?>


        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <section class="content-header">
                <h1>
                    <?=$page_title;?>
                    <small>Customer Payments</small>
                </h1>
                <ol class="breadcrumb">
                    <li><a href="<?php echo $base_url; ?>dashboard"><i class="fa fa-dashboard"></i> Home</a></li>
                    <li><a href="<?php echo $base_url; ?>customers"><?= $this->lang->line('customers_list'); ?></a></li>
                    <li class="active"><?=$page_title;?></li>
                </ol>
            </section>

            <!-- Main content -->
            <section class="content">
                <div class="row">
                    <!-- ********** ALERT MESSAGE START******* -->
                    <?php include"comman/code_flashdata.php"; ?>
                    <!-- ********** ALERT MESSAGE END******* -->
                    <!-- right column -->

                    <!--/.col (right) -->


                    <div class="col-md-12">

                        <div class="box">
                            <div class="box-header">
                                <h3 class="box-title text-blue">Customer Payments</h3>
                            </div>
                            <!-- /.box-header -->
                            <div class="box-body table-responsive no-padding">

                                <table class="table table-bordered table-hover " id="report-data">
                                    <thead>
                                        <tr class="bg-gray">
                                            <th style="">#</th>
                                            <th style=""><?= $this->lang->line('payment_date'); ?></th>
                                            <th style=""><?= $this->lang->line('payment'); ?></th>
                                            <th style=""><?= $this->lang->line('payment_type'); ?></th>
                                            <th style=""><?= $this->lang->line('payment_note'); ?></th>
                                            <th style=""><?= $this->lang->line('action'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                if(isset($q_id)){
                                  $q3 = $this->db->query("select * from db_customer_payments where customer_id=$q_id");
                                  if($q3->num_rows()>0){
                                    $i=1;
                                    $total_paid = 0;
                                    foreach ($q3->result() as $res3) {
                                      $total_paid +=$res3->payment;
                                      echo "<td>".$i."</td>";
                                      echo "<td>".show_date($res3->payment_date)."</td>";
                                      echo "<td class='text-right'>".$CI->currency($res3->payment)."</td>";
                                      echo "<td>".$res3->payment_type."</td>";
                                      echo "<td>".$res3->payment_note."</td>";
                                    //   echo '<td><i class="fa fa-trash text-red pointer" onclick="delete_opening_balance_entry('.$res3->id.')"> Delete</i></td>';
                                        echo '<td></td>';

                                        echo "</tr>";
                                      $i++;
                                    }
                                    echo "<tr class='text-bold'>
                                            <td colspan=2 class='text-right '>Total</td>
                                            <td class='text-right'>".$CI->currency($total_paid)."</td>
                                            <td colspan=3></td>
                                          </tr>";
                                  }
                                  else{
                                    echo "<tr><td colspan='6' class='text-center text-bold'>No Previous Payments Found!!</td></tr>";
                                  }
                                }
                                else{
                                  echo "<tr><td colspan='6' class='text-center text-bold'>No Previous Stock Entry Found!!</td></tr>";
                                }
                              ?>
                                    </tbody>
                                </table>


                            </div>
                            <!-- /.box-body -->
                        </div>
                        <!-- /.box -->
                    </div>
                </div>
                <!-- /.row -->

            </section>
            <!-- /.content -->
        </div>
        <!-- /.content-wrapper -->

        <?php include"footer.php"; ?>


        <!-- Add the sidebar's background. This div must be placed
       immediately after the control sidebar -->
        <div class="control-sidebar-bg"></div>
    </div>
    <!-- ./wrapper -->

    <!-- SOUND CODE -->
    <?php include"comman/code_js_sound.php"; ?>
    <!-- TABLES CODE -->
    <?php include"comman/code_js_form.php"; ?>

    <script src="<?php echo $theme_link; ?>js/customers.js"></script>
    <!-- Make sidebar menu hughlighter/selector -->
    <script>
        $(".<?php echo basename(__FILE__,'.php');?>-active-li").addClass("active");
    </script>
</body>

</html>