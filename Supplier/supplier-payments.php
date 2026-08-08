<?php include('db_connect.php');

if(in_array("6",$_SESSION['login_Permisions']))
{
	?>
	<div class="container-fluid px-3 mt-3">
		<div class="col-lg-12">			
			<div class="row">
				<div class="col-md-12">

					<div class="card border-0 shadow-sm mb-3">
						<div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
							<div>
								<h5 class="mb-0 fw-semibold" style="color: var(--accent);">List of Supplier Payments</h5>
								<small class="text-muted">All the payments related to Suppliers are as Under..</small>
							</div>
							<a href="index.php?page=Supplier/add-supplier-payment" class="btn btn-primary btn-sm px-3">
								<i class="fa fa-plus"></i> New
							</a>
						</div>
					</div>
					<div class="card">
						
						<div class="card-body">
							<table class="table table-hover">
								<thead>
									<tr>
										<th>Pay ID</th>
										<th>Supplier</th>
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
									$order = $conn->query("SELECT a.*,b.supp_name, c.name as receiverName FROM supplier_payment as a INNER JOIN suppliers as b on a.supplier_id = b.supp_id INNER JOIN users as c on a.user_id = c.id WHERE a.pay_status = 0 order by a.pay_id desc ");
									while($row=$order->fetch_assoc()):
										?>
										<tr>
											<td class="text-center">PAY-<?php echo $row['pay_id'] ?></td>
											<td class="text-center">
												<p><?php echo $row['supp_name'] ?></p>
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
												<p>
													<?php echo ($row['payment_mode']==1) ? "-" : $row['cheque_no']?>

												</p>
											</td>
											<td class="text-center">
												<p>
													<?php echo ($row['payment_mode']==1) ? "-" : $row['cheque_date']?>
												</p>
											</td>
											<td class="text-center">
												<p><?php echo number_format($row['amount']) ?></p>
											</td>
											<td class="text-center">
												<p><?php echo $row['receiverName'] ?></p>
											</td>

											<td class="text-center">
												<a target="_blank" class="btn btn-sm btn-info" href="Supplier/view_supplier_payment_print.php?id=<?php echo $row['pay_id'] ?>"><i class="fa fa-file"></i></a>
												
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
			$('table').dataTable({
				order: [[0, 'desc']]
			});
		});


		$('.view_supplier_payment').click(function(){
			alert('s');
			uni_modal("Supplier Payment Details","Supplier/view_supplier_payment_print.php?id="+$(this).attr('data-id'),"mid-large")

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