<?php include('db_connect.php');

if(in_array(55,$_SESSION['login_Permisions']))
{

  $month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');

  $fromDt = icon_date_value($_GET['fromDt'] ?? '', date('Y-m-d'));
  $toDt = icon_date_value($_GET['toDt'] ?? '', date('Y-m-d'));
  $customer_id = icon_get_int('customer_id');



  ?>
  <div class="container-fluid professional-ledger-page">
    <div class="col-lg-12">
      <div class="card professional-ledger-card">
		<div class="ledger-page-header"><span class="view-heading-icon"><i class="fa fa-chart-bar"></i></span><div><h2>Customer Ledger Summary</h2><p>Analyze month-wise customer balances and activity.</p></div></div>
        <div class="card_body">
          <div class="row p-4 ledger-filters">


            <div class="col-md-3">
              <label class="control-label"><b>Customer Name:</b></label>
              <select  name="customer_id" required id="customer_id" class="form-control">
                <option value="0">Select Customer</option>
                <?php
                $query_cust = "SELECT * FROM customers WHERE cust_status = 0";
                $result_cust = mysqli_query($conn,$query_cust);
                while($data_cust = mysqli_fetch_array($result_cust)){
                  $selecte_val = "";
                  if($customer_id == $data_cust['cust_id']){
                    $selecte_val = "Selected";
                  }
                  ?>
                  <option <?php echo $selecte_val ?> value="<?php echo $data_cust['cust_id'] ?>"><?php echo $data_cust["cust_name"] ?></option>
                  <?php
                }
                ?>
              </select>
            </div>

            <div class="col-md-3">
              <label class="control-label"><b>Start Month:</b></label>
              <input type="date" name="fromDt" id="fromDt" value="<?php echo $fromDt ?>" class="form-control">
            </div>

            <div class="col-md-3">
              <label class="control-label"><b>End Month:</b></label>            
              <input type="date" name="toDt" id="toDt" value="<?php echo $toDt ?>" class="form-control">
            </div>
            <div class="col-md-3 mt-4">
              <button type="button" class="btn btn-success" id="filterBtn">Get Ledger</button>
            </div>
          </div>

          <hr>

          <div class="col-md-12 ledger-table-wrap">
            <table class="table table-bordered" id='report-list'>
              <thead>
                <tr>
                  <th colspan="6" class="text-center" style="background-color: #c1f0ff;font-size: 16px"><b>Customer Month Wise Ledger</b></th>
                </tr>
                <tr>
                  <th class="text-center">#</th>
                  <th class="text-center">Month/Year</th>
                  <th class="text-center">Particulars</th>
                  <th class="text-center">Debit</th>
                  <th class="text-center">Credit</th>
                  <th class="text-center">Balance</th>
                </tr>
              </thead>
              <tbody>

                <tr>
                  <?php

                  $firstDtSt = Date('Y-m-01',strtotime($fromDt));
                  $firstDtEnd = Date('Y-m-t',strtotime($fromDt));

                  $lastDtSt = Date('Y-m-01',strtotime($toDt));
                  $lastDtEnd = Date('Y-m-01',strtotime($toDt));

                  $job_opening = $conn->query("SELECT sum(total_amount) as total_amt_jobs from job_order as a inner join job_order_details as b on a.jd_id = b.job_id WHERE a.order_rec_date < '".$firstDtSt."' AND a.customer_id = ".$customer_id." and b.delete_status = 0 ");
                  $row_opening = $job_opening->fetch_array();
                  $total_amt_jobs = $row_opening['total_amt_jobs'];

                  $cust_pay_openong = $conn->query("SELECT sum(amount) as total_amt_pay from customer_payment WHERE payment_date < '".$firstDtSt."' AND customer_id = ".$customer_id." AND pay_status = 0 ");
                  $row_opening_pay = $cust_pay_openong->fetch_array();
                  $total_amt_pay = $row_opening_pay['total_amt_pay'];

                  $opening_balance = $total_amt_pay - $total_amt_jobs;
                  ?>
                  <th colspan="5" class="text-right">Opening Balance</th>
                  <th class="text-right"><?php echo number_format($opening_balance,2) ?></th>
                </tr>


                <?php
                 $y1 = Date('Y',strtotime($fromDt));
                 $y2 = Date('Y',strtotime($toDt));
                 $yeatC = $y1;

                $m1 = Date('m',strtotime($fromDt));
                $m2 = Date('m',strtotime($toDt));
                
                if($y2 == $y1){
                    $monthDiff = $m2 - $m1 +1;
                }else{
                    $sa = $y2-$y1;
                    
                    if($sa == 1){
                        $as = 12 - $m1;
                        $ds = 12 - $m2;
                        $sd = 12 - $ds;
                        
                        $monthDiff =  $as + $sd + 1;
                    }else{
                        $as = 12 - $m1;
                        $ds = 12 - $m2;
                        $sd = 12 - $ds;
                        
                        $monthDiff =  $as + $sd + 13;
                    }
                    
                    
                }
                
                $monthC = $m1;
                $count = 0; 
                $balanceAmt = $opening_balance;
                for($i=0; $i<$monthDiff; $i++){


                  $dated = date('01-'.$monthC.'-'.$yeatC);

                  $stDate = date('Y-m-01',strtotime($dated));
                  $endDate = date('Y-m-t',strtotime($dated));

                  $SalesQ = "SELECT sum(b.total_amount) as saleAmt FROM job_order as a INNER JOIN job_order_details as b on a.jd_id = b.job_id where (a.order_rec_date >= '$stDate' AND a.order_rec_date <= '$endDate') AND customer_id = $customer_id and b.delete_status = 0 ";
                  $sales = $conn->query($SalesQ);

                  if($sales->num_rows > 0){
                    $rowSales = $sales->fetch_array();
                    $particuler = "BILL - Amount For ".date('M-Y',strtotime($dated));

                    $credit_amt = $rowSales['saleAmt'] * (-1);
                    $balanceAmt = $balanceAmt + $credit_amt;

                    if($credit_amt != 0){
                     $count++;
                     ?>
                     <tr>
                      <td class="text-center"><?= $count ?></td>
                      <td class="text-center"><?= date('M-Y',strtotime($dated)) ?></td>
                      <td><?= $particuler ?></td>
                      <td class="text-right"></td>
                      <td class="text-right" style="color:red"><b><?= number_format($credit_amt,2)?></b></td>
                      <td class="text-right"><?= number_format($balanceAmt ,2)?></td>
                    </tr>
                    <?php
                  }
                }


                $PayQ = 'SELECT sum(aa.amount) as payAmt from customer_payment as aa WHERE (aa.payment_date >= "'.$stDate.'" AND aa.payment_date <= "'.$endDate.'") AND aa.customer_id = '.$customer_id." AND pay_status = 0 ";

                $pay = $conn->query($PayQ);
                if($pay->num_rows > 0){
                  $rowPay = $pay->fetch_array();

                  $debit_amt = $rowPay['payAmt'];
                  $particuler = "Payment Received From Customer";

                  $balanceAmt = $balanceAmt + $debit_amt;

                  if($debit_amt != 0){
                   $count++;
                   ?>
                   <tr>
                    <td class="text-center"><?= $count ?></td>
                    <td class="text-center"><?= date('M-Y',strtotime($dated)) ?></td>
                    <td><?= $particuler ?></td>
                    <td class="text-right" style="color:green"><b><?= number_format($debit_amt ,2)?></b></td>
                    <td class="text-right"></td>
                    <td class="text-right"><?= number_format($balanceAmt ,2)?></td>
                  </tr>
                  <?php
                }
              }

             
              if($monthC == 12){
                $yeatC++;
                $monthC=1;
              }else{
                 $monthC++;
              }

            }
            ?>


          </tbody>
          <tfoot>
            <tr>
              <th colspan="5" class="text-right">Closing Balance</th>
              <th class="text-right"><?= number_format($balanceAmt,2) ?></th>
            </tr>
          </tfoot>
        </table>
        <hr>

      </div>
    </div>
  </div>
</div>
</div>
<noscript>
  <style>
    table#report-list{
      width:100%;
      border-collapse:collapse
    }
    table#report-list td,table#report-list th{
      border:1px solid
    }
    p{
      margin:unset;
    }
    .text-center{
     text-align:center
   }
   .text-right{
    text-align:right
  }
</style>
</noscript>
<script>
  $('#filterBtn').click(function(){
    var fromDt = $('#fromDt').val();
    var toDt = $('#toDt').val();
    var customer_id = $('#customer_id').val();
    location.replace('index.php?page=Customer/customer-summary-ledger&fromDt='+fromDt+'&toDt='+toDt+'&customer_id='+customer_id)
  })
</script>



<?php
}else{
  include 'accessDenied.php';
}
?>
