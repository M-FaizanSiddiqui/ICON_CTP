<?php include('db_connect.php');

if(in_array("10",$_SESSION['login_Permisions']))
{
	?>
	<div class="container-fluid professional-view-page view-customers">
		<div class="row view-summary-grid">
			<div class="col-md-4">
				<div class="view-summary-card"><span class="view-summary-icon"><i class="fa fa-users"></i></span><div class="view-summary-copy"><h6>Total Customers</h6><h3><?php echo $conn->query("SELECT COUNT(*) AS c FROM customers")->fetch_assoc()['c']; ?></h3></div></div>
			</div>
			<div class="col-md-4">
				<div class="view-summary-card is-active"><span class="view-summary-icon"><i class="fa fa-check-circle"></i></span><div class="view-summary-copy"><h6>Active Customers</h6><h3><?php echo $conn->query("SELECT COUNT(*) AS c FROM customers WHERE cust_status=0")->fetch_assoc()['c']; ?></h3></div></div>
			</div>
			<div class="col-md-4">
				<div class="view-summary-card is-inactive"><span class="view-summary-icon"><i class="fa fa-pause-circle"></i></span><div class="view-summary-copy"><h6>Inactive Customers</h6><h3><?php echo $conn->query("SELECT COUNT(*) AS c FROM customers WHERE cust_status=1")->fetch_assoc()['c']; ?></h3></div></div>
			</div>
		</div>

		<div class="col-lg-12">
			<div class="row">
				<div class="col-md-12">
					<div class="card professional-view-card">
						<div class="row" style="background-color:#f9f9f9">
							<div class="col-lg-6">
								<div class="view-heading"><span class="view-heading-icon"><i class="fa fa-users"></i></span><div><h2>Customer Directory</h2><p>Registered customers and contact information.</p></div></div>
							</div>
							<div class="col-lg-6">
								<a class="btn btn-primary nav-add-customer btn-sm float-right m-3" href="index.php?page=Customer/add-customer" id="new_order">
									<i class="fa fa-plus"></i> Add Customer
								</a>
							</div>
						</div>
						<hr>
						
						<div class="card-body table-responsive">
							<table class="table table-hover">
								<thead>
									<tr>
										<th>Customer Id</th>
										<th>Name</th>
										<th>Email</th>
										<th>Phone No</th>
										<th>Location</th>
										<th>Status</th>
									</tr>
								</thead>
								<tbody>
									<?php
									$category = $conn->query("SELECT * FROM customers order by cust_id asc");
									while($row=$category->fetch_assoc()):
										?>
										<tr>
											<td class="text-center">CUST-<?php echo $row['cust_id'] ?></td>
											<td class=""><?php echo $row['cust_name'] ?></td>
											<td><?php echo $row['cust_email'] ?></td>
											<td><?php echo $row['cust_ph_no'] ?></td>
											<td><?php echo $row['cust_address'] ?></td>
											<td><?php
											if($row['cust_status'] == 0){
												?><span style="color:green"><b>Active</b></span><?php
											}else{
												?>
												<span style="color: red"><b>In-Active</b></span>
												<?php
											}
											?></td>

											<!-- <td class="text-center">
												<button class="btn btn-sm btn-danger delete_customer" type="button" data-id="<?php echo $row['cust_id'] ?>"><i class="fa fa-trash-alt"></i></button>
											</td> -->
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

		<script>
			$(document).ready(function(){
				$('table').dataTable( {
					order: [[0, 'desc']]
				});
			})

			$('.edit_customer').click(function(){
				start_load()
				var cat = $('#manage-customer')
				cat.get(0).reset()
				cat.find("[name='cust_id']").val($(this).attr('data-id'))
				cat.find("[name='cust_name']").val($(this).attr('data-name'))
				end_load()
			})
			$('.delete_customer').click(function(){
				_conf("Are you sure to delete this customer?","delete_customer",[$(this).attr('data-id')])
			})
			function delete_customer($id){
				start_load()
				$.ajax({
					url:'ajax.php?action=delete_customer',
					method:'POST',
					data:{cust_id:$id},
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
