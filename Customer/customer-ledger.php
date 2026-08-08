<?php include('db_connect.php');

if(in_array(14,$_SESSION['login_Permisions']))
{
  if(!function_exists('customer_ledger_job_status_label')){
    function customer_ledger_job_status_label($status){
      $labels = [
        0 => 'Pending',
        3 => 'Plate Setting',
        1 => 'On Machine',
        4 => 'Plate Washing',
        5 => 'Oven Baking',
        2 => 'Completed'
      ];

      return $labels[(int)$status] ?? 'Pending';
    }
  }

  $month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');

  $fromDt = icon_date_value($_GET['fromDt'] ?? '', date('Y-m-d'));
  $toDt = icon_date_value($_GET['toDt'] ?? '', date('Y-m-d'));
  $customer_id = icon_get_int('customer_id');

  ?>
  <div class="container-fluid professional-ledger-page">
    <div class="col-lg-12">
      <div class="card professional-ledger-card">
        <div class="ledger-page-header"><span class="view-heading-icon"><i class="fa fa-book"></i></span><div><h2>Customer Ledger</h2><p>Review customer debits, credits, and running balance.</p></div></div>
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
              <label class="control-label"><b>From Date:</b></label>
              <input type="date" name="fromDt" id="fromDt" value="<?php echo $fromDt ?>" class="form-control">
            </div>

            <div class="col-md-3">
              <label class="control-label"><b>To Date:</b></label>            
              <input type="date" name="toDt" id="toDt" value="<?php echo $toDt ?>" class="form-control">
            </div>
            <div class="col-md-3 mt-4">
              <button type="button" class="btn btn-success btn-sm" id="filterBtn">Get Ledger</button>
              <button class="btn btn-primary btn-sm float-right" type="button" id="print"><i class="fa fa-print"></i> Print</button>
            </div>
          </div>

          <hr>

          <div class="col-md-12 ledger-table-wrap">
            <table class="table table-bordered" id='report-list'>
              <thead>
                <tr>
                  <th class="text-center">#</th>
                  <th class="">Date</th>
                  <th class="">Particulars</th>
                  <th class="">Debit</th>
                  <th class="">Credit</th>
                  <th class="">Balance</th>
                </tr>
              </thead>
              <tbody>

               <tr>
                <?php
                $job_opening = $conn->query("SELECT sum(total_amount) as total_amt_jobs from job_order as a inner join job_order_details as b on a.jd_id = b.job_id WHERE a.order_rec_date < '".$fromDt."' AND a.customer_id = ".$customer_id." and b.delete_status = 0 ");
                $row_opening = $job_opening->fetch_array();
                $total_amt_jobs = $row_opening['total_amt_jobs'];

                $cust_pay_openong = $conn->query("SELECT sum(amount) as total_amt_pay from customer_payment WHERE payment_date < '".$fromDt."' AND customer_id = ".$customer_id." AND pay_status = 0 ");
                $row_opening_pay = $cust_pay_openong->fetch_array();
                $total_amt_pay = $row_opening_pay['total_amt_pay'];

                $opening_balance = $total_amt_pay - $total_amt_jobs;
                ?>
                <th colspan="5" class="text-right">Opening Balance</th>
                <th class="text-right"><?php echo number_format($opening_balance,2) ?></th>
              </tr>
              <?php
             //
              $i = 1;
              $total = 0;
              $sales = $conn->query("SELECT a.jd_id as jp_id,a.job_name,a.order_rec_date as jp_date,a.order_status,b.item_id,b.total_amount,'Job_Card' as ref, '' as cheque_no, '' as cheque_date,'' as payment_mode FROM job_order as a INNER JOIN job_order_details as b on a.jd_id = b.job_id where (a.order_rec_date >= '$fromDt' AND a.order_rec_date <= '$toDt') AND customer_id = $customer_id and b.delete_status = 0 UNION ALL SELECT aa.pay_id as jp_id,aa.reference as job_name,aa.payment_date as jp_date,'' as order_status, '' as item_id, aa.amount as total_amount, 'Payment' as ref, cheque_no, cheque_date,payment_mode from customer_payment as aa WHERE (aa.payment_date >= '$fromDt' AND aa.payment_date <= '$toDt') AND aa.customer_id = $customer_id  AND aa.pay_status = 0 order by unix_timestamp(jp_date) asc ");
              $balance = $opening_balance;
              if($sales->num_rows > 0):
               while($row = $sales->fetch_array()):
                $total += $row['total_amount'];
                $particulars = "";
                $debit_amt = 0;
                $credit_amt = 0;


                if($row['ref'] == "Job_Card"){
                  $credit_amt = $row['total_amount'];

                  $job_order_status = customer_ledger_job_status_label($row['order_status']);

                  if($row['item_id'] == 0){
                    $particulars = "This Entry is made to balance the previous amount.";
                  }else{
                    $particulars = "JOB Card #: Jd-".$row['jp_id']." (".$row['job_name']."). Stock Id: IT-".$row['item_id']." [".$job_order_status."] ";
                  }
                  

                  $credit_amt = $credit_amt * (-1);
                  $balance = $balance + $credit_amt;

                }else{
                  $debit_amt = $row['total_amount'];

                  $balance = $balance + $debit_amt;
                  if($row['payment_mode'] == 1){
                    $paymentMode = 'Cash';
                    $chequeDetails = "";
                  }else{
                    $paymentMode = 'Cheque';
                    $chequeDetails = ",Cheque Date: ".$row['cheque_date'].", Cheque No:".$row['cheque_no'];
                  }
                  $particulars = "Payment Received. Details[Payment Mode:".$paymentMode." ".$chequeDetails.", Refernece: ".$row['job_name']."].";
                }
                ?>
                <tr>
                  <td class="text-center"><?php echo $i++ ?></td>
                  <td class="text-center"><?php echo date("d-M-Y", strtotime($row['jp_date'])); ?></td>
                  <td class="text-left"><?= $particulars ?></td>
                  <td class="text-right text-success"><b><?= number_format($debit_amt,2)?></b></td>
                  <td class="text-right text-danger"><b><?= number_format($credit_amt,2) ?></b></td>                  
                  <td>
                    <p class="text-right"> <b><?php echo number_format($balance,2) ?></b></p>
                  </td>
                </tr>
                <?php 
              endwhile;
            else:
              ?>
              <tr>
                <th class="text-center" colspan="6">No Data.</th>
              </tr>
              <?php 
            endif;
            ?>
          </tbody>
          <tfoot>
            <tr>
              <th colspan="5" class="text-right">Closing Balance</th>
              <th class="text-right"><?php echo number_format($balance,2) ?></th>
            </tr>
          </tfoot>
        </table>
        <hr>
        <div class="col-md-12 mb-4">

        </div>
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
    location.replace('index.php?page=Customer/customer-ledger&fromDt='+fromDt+'&toDt='+toDt+'&customer_id='+customer_id)
  })

  $('#print').click(function(){
    var _c = $('#report-list').clone();
    var ns = $('noscript').clone();
    ns.append(_c)
    var nw = window.open('','_blank','width=900,height=600')
    nw.document.write('<p class="text-center"><b>Customer Ledger as of <?php echo date("F, Y",strtotime($month)) ?></b></p>')
    nw.document.write(ns.html())
    nw.document.close()
    nw.print()
    setTimeout(() => {
     nw.close()
   }, 500);
  })
</script>



<?php
}else{
  include 'accessDenied.php';
}
?>
