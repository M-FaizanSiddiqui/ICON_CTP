<?php include('db_connect.php');

if(in_array("24",$_SESSION['login_Permisions']))
{

  $month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');

  $fromDt = icon_date_value($_GET['fromDt'] ?? '', date('Y-m-d'));
  $toDt = icon_date_value($_GET['toDt'] ?? '', date('Y-m-d'));
  $item_id = icon_get_int('item_id');

  ?>
  <div class="container-fluid professional-ledger-page">
    <div class="col-lg-12">
      <div class="card professional-ledger-card">
        <div class="ledger-page-header"><span class="view-heading-icon"><i class="fa fa-cubes"></i></span><div><h2>Item Ledger</h2><p>Track inventory movement and running quantities.</p></div></div>
        <div class="card_body">
          <div class="row p-4 ledger-filters">


            <div class="col-md-4">
              <label class="control-label"><b>Item:</b></label>
              <select  name="item_id" id="item_id" class="form-control">
                <option value="">Please Select</option>
                <?php
                $query_items = "SELECT * FROM inventory_item";
                $result_items = mysqli_query($conn,$query_items);
                while($data_items = mysqli_fetch_array($result_items)){
                  $selecte_val = "";
                  if($item_id == $data_items['item_id']){
                    $selecte_val = "Selected";
                  }
                  ?>
                  <option <?php echo $selecte_val ?> value="<?php echo $data_items['item_id'] ?>"><?php echo $data_items["item_name"] ?></option>
                  <?php
                }
                ?>
              </select>
            </div>

            <div class="col-md-4">
              <label class="control-label"><b>From Date:</b></label>
              <input type="date" name="fromDt" id="fromDt" value="<?php echo $fromDt ?>" class="form-control">
            </div>

            <div class="col-md-4">
              <label class="control-label"><b>To Date:</b></label>            
              <input type="date" name="toDt" id="toDt" value="<?php echo $toDt ?>" class="form-control">
            </div>
            <div class="col-md-4 mt-2">
              <button type="button" class="btn btn-success" id="filterBtn">Get Ledger</button>
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
                  <th class="">In</th>
                  <th class="">Out</th>
                  <th class="">Balance</th>
                </tr>
              </thead>
              <tbody>

               <tr>
                <?php
                $job_opening = $conn->query("SELECT sum(quantity) as total_reams FROM inventory_audit WHERE item_id = ".$item_id." AND dated < '".$fromDt."'");
                $row_opening = $job_opening->fetch_array();
                $total_reams = $row_opening['total_reams'];

                $opening_balance = $total_reams;
                ?>
                <th colspan="5" class="text-right">Opening Balance</th>
                <th class="text-right"><?php echo number_format($opening_balance,2) ?></th>
              </tr>
              <?php
              $i = 1;
              $sales = $conn->query("SELECT * FROM inventory_audit WHERE item_id = ".$item_id." AND dated >= '".$fromDt."' AND dated <= '".$toDt."' ");

              $balance = $opening_balance;
              if($sales->num_rows > 0):
               while($row = $sales->fetch_array()):
                $particulars = "";
                $debit_amt = 0;
                $credit_amt = 0;

                if($row['quantity']>0){
                  $debit_amt += $row['quantity'];
                }
                else{
                  $credit_amt += $row['quantity'];
                }

                if($row['ref_column'] == "JOB"){
                  $job_details =  $conn->query("SELECT job_name,cust_name FROM job_order as a INNER JOIN customers as b on a.customer_id = b.cust_id WHERE a.jd_id =".$row['ref_id']);
                  $row_job = $job_details->fetch_array();
                  $job_name = $row_job['job_name'];
                  $cust_name = $row_job['cust_name'];

                  $particulars = "Job Order recevied from Customer: ".$cust_name.". Job Name: ".$job_name.".";
                }else if($row['ref_column'] == "SUPPLIER_RECEIVED_INV"){

                  $job_details =  $conn->query("SELECT supp_name FROM inventoty_received_details as a INNER JOIN suppliers as b on a.supplier_id = b.supp_id WHERE a.ird_id =".$row['ref_id']);
                  $row_job = $job_details->fetch_array();
                  $supp_name = $row_job['supp_name'];
                  $particulars = "Inventory Recevied from Supplier: ".$supp_name.".";
                }else{
                  // INVENTORY_WASTED
                  $job_details = $conn->query("SELECT * FROM waste_inventory WHERE w_id = ".$row['ref_id']);
                  $row_job = $job_details->fetch_array();
                  // $reamrks_d = $row_job['remarks'];
                  $particulars = "Inventory Wasted Remarks are: ";
                }
                

                $balance = $balance + $debit_amt + $credit_amt;

                ?>
                <tr>
                  <td class="text-center"><?php echo $i++ ?></td>
                  <td class="text-center"><?php echo date("d-M-Y", strtotime($row['dated'])); ?></td>
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
          <center>
            <button class="btn btn-success btn-sm col-sm-3" type="button" id="print"><i class="fa fa-print"></i> Print</button>
          </center>
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
    var item_id = $('#item_id').val();
    location.replace('index.php?page=Stocks/item-ledger&fromDt='+fromDt+'&toDt='+toDt+'&item_id='+item_id)
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
