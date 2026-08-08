<?php include('db_connect.php');

if(in_array("4",$_SESSION['login_Permisions']))
{
	$order_id = icon_get_int('id');
	if($order_id > 0){
		$order = $conn->query("SELECT a.*,b.supp_name FROM inventory_received as a INNER JOIN suppliers as b on a.supplier_id = b.supp_id where a.ir_id =".$order_id);

		$row_cnt = $order->num_rows;
		if($row_cnt>0){

			foreach($order->fetch_array() as $k => $v){
				$$k= $v;
			}

			$items = $conn->query("SELECT a.*,b.item_name FROM inventoty_received_details as a inner join inventory_item as b on a.item_id = b.item_id where a.ir_id = ".$order_id);

			?>

			<style>
				label{font-size: 14px; font-weight: bold}
				.spanCss{font-size: 14px;color: blue}
			</style>

			<div class="container-fluid professional-view-page view-receive-detail">

				<div class="col-lg-12">
					<div class="row">
						<!-- FORM Panel -->
						<div class="col-md-12">
							<form id="manage-job-order" method="POST" enctype="multipart/form-data">
								<div class="card professional-view-card">
									<div class="card-header">
										<span class="detail-header-icon"><i class="fa fa-truck"></i></span> Supplier Order #<?php echo $order_id ?>
									</div>
									<div class="card-body">
										<input type="hidden" name="jd_id">

										<div class="row">
											<div class="col-md-3 form-group">
												<label class="control-label">Supplier: </label>
												<span class="spanCss"><?php echo  $supp_name ?></span>
											</div>

											<div class="col-md-3 form-group">
												<label class="control-label">Order Received Date: </label>
												<span class="spanCss"><?php echo $received_date ?></span>
											</div>

											<div class="col-md-3 form-group">
												<label class="control-label">Document No: </label>
												<span class="spanCss"><?php echo $doc_no ?></span>
											</div>

										</div>


										<div class="row">
											<div class="col-md-12">
												<table class="table-bordered" style="width: 100%;">
													<tr>
														<th colspan="5">Order Details</th>
													</tr>
													<tr>
														<th style="width: 8%;text-align: center;">Item Id</th>
														<th style="width: 15%;text-align: center;">Item Name</th>
														<th style="width: 15%;text-align: center;">Qty</th>
														<th style="width: 15%;text-align: center;">Rate</th>
														<th style="width: 15%;text-align: center;">Amount</th>
													</tr>

													<?php
													$total_amount = 0;
													while($row = $items->fetch_assoc()):
														$supplier_id = $row['supplier_id'];
														$item_id = $row['item_id'];
														$quantity = $row['quantity'];
														$received_date = $row['received_date'];
														$status = $row['status'];
														$user_id = $row['user_id'];
														$sup_order_id = $row['sup_order_id'];
														$item_name = $row['item_name'];
														$rate = $row['rate'];
														$amount = $row['amount'];
														$total_amount += $amount;
														?>
														<tr>
															<td style="text-align: center;">IT-<?= $item_id ?></td>
															<td><?= $item_name ?></td>
															<td style="text-align: center;"><?= $quantity ?></td>
															<td style="text-align: center;"><?= $rate ?></td>
															<td style="text-align: center;text-align: right;"><?= number_format($amount,2) ?></td>
														</tr>
														<?php
													endwhile;											
													?>

													<tr>
														<td colspan="4" style="text-align: right;color: blue"><b>Total Amount</b></td>
														<td style="text-align: right;color: blue"><b><?=  number_format($total_amount,2) ?></b></td>
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
		}
		else{
			include 'invalidLink.php';
		}
	}else{		
		include 'invalidLink.php';
	}
}else{
	include 'accessDenied.php';
}
?>
