<?php include('db_connect.php');

if(in_array("32",$_SESSION['login_Permisions']))
{
	$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

	if($order_id > 0){
		
		$order = $conn->query("SELECT a.*,b.cust_name as custName FROM job_order as a INNER JOIN customers as b on a.customer_id = b.cust_id where a.jd_id =".$order_id);

		$row_cnt = $order->num_rows;
		if($row_cnt>0){
			foreach($order->fetch_array() as $k => $v){
				$$k= $v;
			}
			?>

			<style>
				label{font-size: 14px; font-weight: bold}
				.spanCss{font-size: 14px;color: blue}
			</style>

			<div class="container-fluid professional-view-page view-job-detail">

				<div class="col-lg-12">
					<div class="row">
						<!-- FORM Panel -->
						<div class="col-md-12">
							<form id="manage-job-order" method="POST" enctype="multipart/form-data">
								<div class="card professional-view-card">
									<div class="card-header">
										<span class="detail-header-icon"><i class="fa fa-briefcase"></i></span> Job Order #<?php echo $order_id ?>
									</div>
									<div class="card-body">
										<input type="hidden" name="jd_id">

										<div class="row">
											<div class="col-md-3 form-group">
												<label class="control-label">Customer: </label>
												<span class="spanCss"><?php echo  $custName ?></span>
											</div>

											<div class="col-md-3 form-group">
												<label class="control-label">Order Received Date: </label>
												<span class="spanCss"><?php echo $order_rec_date ?></span>
											</div>
											<div class="col-md-6 form-group">
												<label class="control-label">Job Name: </label>
												<span class="spanCss"><?php echo $job_name ?></span>
											</div>

											<div class="col-md-12 form-group">
												<label class="control-label">Job Description: </label>
												<span class="spanCss"><?php echo $job_description ?></span>
											</div>

										</div>


										<div class="row">
											<div class="col-md-12">
												<table class="table-bordered" style="width: 100%;">
													<tr>
														<th style="width: 40%;width: 14px;text-align: center;">Plate</th>
														<th style="width: 20%;width: 14px;text-align: center;">Quantity</th>
														<th style="width: 20%;width: 14px;text-align: center;">Price</th>
														<th style="width: 20%;width: 14px;text-align: center;">Amount</th>
													</tr>

													<?php
													$job_details = $conn->query("SELECT a.*,b.item_name FROM job_order_details as a inner join inventory_item as b on a.item_id = b.item_id where a.delete_status = 0 AND a.job_id = ".$order_id);
													$counter = 0;
													while($row_job_details = $job_details->fetch_assoc()){
														$total_amount = $row_job_details['total_amount'];
														$price = $row_job_details['price'];
														$quantity = $row_job_details['quantity'];
														$item_name = $row_job_details['item_name'];
														$counter++;
														?>
														<tr>
															<td><?= $item_name ?></td>
															<td><?= $quantity ?></td>
															<td><?= $price ?></td>
															<td><?= $total_amount ?></td>
														</tr>
													<?php } ?>
												</table>
											</div>
										</div>


										<div class="row">
											<div class="col-md-12 form-group">
												<label class="control-label">Attachment: </label>
												<table class="table-bordered" style="width: 40%">
													<tr>
														<th style="width: 20%;font-size: 14px;text-align: center;">SR#</th>
														<th style="width: 80%;font-size: 14px;text-align: center;">File</th>
													</tr>
													<?php

													$query_attachment = $conn->query("SELECT * FROM job_order_attachment WHERE job_id = ".$order_id);
													$counter=0;
													while($rowAttachment = $query_attachment->fetch_assoc()):
														$counter++;
														?>
														<tr>
															<td style="font-size: 14px;text-align: center;"><?php echo $counter ?></td>
															<td style="font-size: 14px;text-align: center;"><a href="job_Attachments/<?php echo $rowAttachment['attachment'] ?>" download><i class="fa fa-file"></i> File No <?= $counter ?></a></td>
														</tr>
														<?php
													endwhile;

													?>
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
			?>
			<div class="container-fluid">

				<div class="col-lg-12">
					<div class="row">
						<div class="col-md-12">
							<div class="card">
								<div class="card-header">
									<h2 class="text-danger">Invalid Link!</h2>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php
		}
	}else{
		include 'invalidLink.php';
	}

}else{
	include 'accessDenied.php';
}
?>
