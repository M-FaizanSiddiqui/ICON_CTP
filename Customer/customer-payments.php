<?php include('db_connect.php');

if(in_array("12",$_SESSION['login_Permisions']))
{
	?>
	<div class="container-fluid professional-view-page view-customer-payments">
		<div class="row view-summary-grid">
			<div class="col-md-4"><div class="view-summary-card"><span class="view-summary-icon"><i class="fa fa-file"></i></span><div class="view-summary-copy"><h6>Total Payments</h6><h3><?php echo $conn->query("SELECT COUNT(*) AS c FROM customer_payment")->fetch_assoc()['c']; ?></h3></div></div></div>
			<div class="col-md-4"><div class="view-summary-card is-active"><span class="view-summary-icon"><i class="fa fa-money-bill"></i></span><div class="view-summary-copy"><h6>Active Amount</h6><h3><?php echo number_format($conn->query("SELECT COALESCE(SUM(amount),0) AS c FROM customer_payment WHERE pay_status=0")->fetch_assoc()['c'],0); ?></h3></div></div></div>
			<div class="col-md-4"><div class="view-summary-card is-inactive"><span class="view-summary-icon"><i class="fa fa-pause-circle"></i></span><div class="view-summary-copy"><h6>Inactive Payments</h6><h3><?php echo $conn->query("SELECT COUNT(*) AS c FROM customer_payment WHERE pay_status<>0")->fetch_assoc()['c']; ?></h3></div></div></div>
		</div>
		<div class="col-lg-12">			
			<div class="row">
				<div class="col-md-12">
					<div class="card professional-view-card">
						<div class="card-header">
							<span class="detail-header-icon"><i class="fa fa-money-bill"></i></span> Customer Payments

							<span class="float:right">
								<a class="btn btn-primary btn-sm float-right" href="index.php?page=Customer/add-customer-payment" id="new_order">
									<i class="fa fa-plus"></i> New 
								</a>
							</span>

						</div>
						<div class="card-body table-responsive">
							<table class="table table-hover">
								<thead>
									<tr>
										<th>Pay ID</th>
										<th>Customer</th>
										<th>Payment Date</th>
										<th>Pay Mode</th>
										<th>Cheque No</th>
										<th>Cheque Date</th>
										<th>Amount</th>
										<th>Received By</th>
										<th>Action</th>
									</tr>
								</thead>
								<tbody>
									<?php 
									$i = 1;
									$order = $conn->query("SELECT a.*,b.cust_name, c.name as receiverName FROM customer_payment as a INNER JOIN customers as b on a.customer_id = b.cust_id INNER JOIN users as c on a.user_id = c.id WHERE a.pay_status = 0 order by a.pay_id desc ");
									while($row=$order->fetch_assoc()):
										?>
										<tr>
											<td class="text-center">PAY-000<?php echo $row['pay_id'] ?></td>
											<td class="text-center">
												<p><?php echo $row['cust_name'] ?></p>
											</td>
											<td class="text-center">
												<p> <?php echo date("M d,Y",strtotime($row['payment_date'])) ?></p>
											</td>
											<td class="text-center">
												<p>
													<?php 
													if($row['payment_mode'] == 1){
														echo 'Cash';
													}else{
														echo 'Cheque';
													}
													?>
												</p>
											</td>
											<td class="text-center">
												<p><?php echo $row['cheque_no'] ?></p>
											</td>
											<td class="text-center">
												<p><?php echo $row['cheque_date'] ?></p>
											</td>
											<td class="text-center">
												<p><?php echo number_format($row['amount']) ?></p>
											</td>
											<td class="text-center">
												<p><?php echo $row['receiverName'] ?></p>
											</td>

											<td class="text-center">

												<!-- <span class="float:right">
													<a class="btn btn-primary btn-sm " href="index.php?page=edit-customer-payment&id=<?php echo $row['pay_id'] ?>" id="edit_order">
														<i class="fa fa-edit"></i> 
													</a>
												</span> -->

												<button class="btn btn-sm btn-info view_customer_payment" type="button" data-id="<?php echo $row['pay_id'] ?>">
													<i class="fa fa-file"></i>
												</button>

												<button class="btn btn-sm btn-danger delete_customer_payment" type="button" data-id="<?php echo $row['pay_id'] ?>">
											<i class="fa fa-trash"></i>
												</button>
											</td>
										</tr>
									<?php endwhile; ?>
								</tbody>
							</table>
						</div>
					</div>
				</div>
				<!-- Table Panel -->
			</div>
		</div>

	</div>
	<style>

		td{
			vertical-align: middle !important;
		}
		td p{
			margin: unset
		}
		img{
			max-width:100px;
			max-height: :150px;
		}
	</style>



	<script>
		$(document).ready(function(){
			$('table').dataTable()
		})


		$('.view_customer_payment').click(function(){
			uni_modal("Customer Payment Details","Customer/view_customer_payment_print.php?id="+$(this).attr('data-id'),"mid-large")

		})
		$('.delete_customer_payment').click(function(){
			_conf("Are you sure to delete this order ?","delete_customer_payment",[$(this).attr('data-id')])
		})
		function delete_customer_payment($id){
			start_load()
			$.ajax({
				url:'ajax.php?action=delete_customer_payment',
				method:'POST',
				data:{pay_id:$id},
				success:function(resp){
					if(resp==1){
						alert_toast("Data successfully deleted",'success')
						setTimeout(function(){
							location.reload()
						},1500)

					}
				}
			})
		}
	</script>


	<?php
}else{
	include 'accessDenied.php';
}
?>
