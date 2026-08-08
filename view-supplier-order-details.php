<?php include('db_connect.php');

if(in_array("20",$_SESSION['login_Permisions']))
{
	
	$order_id = $_GET['id'];

	$order = $conn->query("SELECT a.*,b.supp_name,c.name as userName FROM supplier_order as a INNER JOIN suppliers as b on a.supp_id = b.supp_id INNER JOIN users as c on a.user_id = c.id where a.id =".$order_id);
	foreach($order->fetch_array() as $k => $v){
		$$k= $v;
	}

	$items = $conn->query("SELECT a.*,b.item_name FROM job_order_details as a inner join inventory_item as b on a.item_id = b.item_id where a.job_id = {$_GET['id']} ");
	while($row = $items->fetch_assoc()):
		$total_amount = $row['total_amount'];
		$price = $row['price'];
		$quantity = $row['quantity'];
		$item_id = $row['item_id'];
		$item_name = $row['item_name'];
	endwhile;
	?>

	<style>
		label{font-size: 14px; font-weight: bold}
		.spanCss{font-size: 14px;color: blue}
	</style>

	<div class="container-fluid">

		<div class="col-lg-12">
			<div class="row">
				<!-- FORM Panel -->
				<div class="col-md-12">
					<form id="manage-job-order" method="POST" enctype="multipart/form-data">
						<div class="card">
							<div class="card-header">
								<b>Supplier Order: <?php echo $order_id ?></b>
							</div>
							<div class="card-body">
								<input type="hidden" name="jd_id">

								<div class="row">
									<div class="col-md-3 form-group">
										<label class="control-label">Customer: </label>
										<span class="spanCss"><?php echo  $supp_name ?></span>
									</div>

									<div class="col-md-3 form-group">
										<label class="control-label">Order Received Date: </label>
										<span class="spanCss"><?php echo $dated ?></span>
									</div>
									<div class="col-md-3 form-group">
										<label class="control-label">Receiver Name: </label>
										<span class="spanCss"><?php echo $userName ?></span>
									</div>

									<div class="col-md-3 form-group">
										<label class="control-label">Status: </label>
										<?php
										if($req_status == 0){
											?><span style="color:red"><b>Pending</b></span><?php
										}elseif($req_status == 1){
											?><span style="color:blue"><b>Partial Received</b></span><?php
										}else{
											?>
											<span style="color: green"><b>Received</b></span>
											<?php
										}
										?>
									</div>

								</div>


								<div class="row">
									<div class="col-md-12">
										<table class="table-bordered" style="width: 100%;">
											<tr>
												<th colspan="7">Order Details</th>
											</tr>
											<tr>
												<th style="width: 2%;text-align: center;">SR#</th>
												<th style="width: 20%;text-align: center;">Plate</th>
												<th style="width: 20%;text-align: center;">Qty Ordered</th>
												<th style="width: 20%;text-align: center;">Qty Received</th>
												<th style="width: 20%;text-align: center;">Qty Remaining</th>
												<th style="width: 20%;text-align: center;">Rate</th>
												<th style="width: 20%;text-align: center;">Amount</th>
											</tr>

											<?php
											$job_details = $conn->query("SELECT a.*,b.item_name FROM supplier_order_details as a inner join inventory_item as b on a.item_id = b.item_id where a.sr_id = {$_GET['id']} ");
											$counter = 0;
											$total_amount =0;
											while($row_job_details = $job_details->fetch_assoc()){
												$item_id = $row_job_details['item_id'];
												$qty_ordered = $row_job_details['qty'];
												$item_name = $row_job_details['item_name'];
												$rate = $row_job_details['rate'];
												$amount = $row_job_details['amount'];
												$counter++;

												$job_details_rec = $conn->query("SELECT sum(quantity) as total_received FROM inventoty_received_details as a where a.sup_order_id = {$_GET['id']}  AND item_id = ".$item_id." ");
												$row_job_details_rec = $job_details_rec->fetch_assoc();
												$total_received = $row_job_details_rec['total_received'];
												if($total_received == ""){
													$total_received = 0;
												}
												$remainingQty = $qty_ordered - $total_received;

												$total_amount += $amount;
												?>
												<tr>
													<td style="text-align: center;"><?= $counter ?></td>
													<td><?= $item_name ?></td>
													<td style="text-align: center;"><?= number_format($qty_ordered) ?></td>
													<td style="text-align: center;"><?= number_format($total_received) ?></td>
													<td style="text-align: center;"><?= number_format($remainingQty) ?></td>
													<td style="text-align: right;"><?= number_format($rate,2) ?></td>
													<td style="text-align: right;"><?= number_format($amount,2) ?></td>
												</tr>
											<?php } ?>

											<tr>
												<td style="text-align: right;color: blue" colspan="6"><b>Total Amount</b></td>
												<td style="text-align: right;color: blue"><b><?= number_format($total_amount,2) ?></b></td>
											</tr>
										</table>
									</div>
								</div>


							</div>


						</div>
					</form>
				</div>
				<!-- FORM Panel -->
			</div>
		</div>


	</div>
	<style>

		td{
			vertical-align: middle !important;
		}
		td p {
			margin:unset;
		}
	</style>
	<script>

		$('#manage-job-order').on('reset',function(){
			$('input:hidden').val('')
		})

		$('#manage-job-order').on('reset',function(){
			$('input:hidden').val('')
		})


		$('table').dataTable()
	</script>


	<?php
}else{
	include 'accessDenied.php';
}
?>