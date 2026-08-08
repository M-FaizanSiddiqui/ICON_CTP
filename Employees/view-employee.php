<?php include('db_connect.php');

if(in_array("60",$_SESSION['login_Permisions']))
{
	?>
	<div class="container-fluid professional-view-page view-employees">
		<div class="row view-summary-grid">
			<div class="col-md-4"><div class="view-summary-card"><span class="view-summary-icon"><i class="fa fa-id-card"></i></span><div class="view-summary-copy"><h6>Total Employees</h6><h3><?php echo $conn->query("SELECT COUNT(*) AS c FROM employee")->fetch_assoc()['c']; ?></h3></div></div></div>
			<div class="col-md-4"><div class="view-summary-card is-active"><span class="view-summary-icon"><i class="fa fa-check-circle"></i></span><div class="view-summary-copy"><h6>Active Employees</h6><h3><?php echo $conn->query("SELECT COUNT(*) AS c FROM employee WHERE emp_status=0")->fetch_assoc()['c']; ?></h3></div></div></div>
			<div class="col-md-4"><div class="view-summary-card is-inactive"><span class="view-summary-icon"><i class="fa fa-pause-circle"></i></span><div class="view-summary-copy"><h6>Inactive Employees</h6><h3><?php echo $conn->query("SELECT COUNT(*) AS c FROM employee WHERE emp_status=1")->fetch_assoc()['c']; ?></h3></div></div></div>
		</div>

		<div class="col-lg-12">
			<div class="row">
				<div class="col-md-12">
					<div class="card professional-view-card">
						<div class="row" style="background-color:#f9f9f9">
							<div class="col-lg-6">
								<div class="view-heading"><span class="view-heading-icon"><i class="fa fa-id-card"></i></span><div><h2>Employee Directory</h2><p>Employee profiles, designations, and status.</p></div></div>
							</div>
							<div class="col-lg-6">
								<a class="btn btn-primary nav-add-employee btn-sm float-right m-3" href="index.php?page=Employees/add-employee" id="new_order">
									<i class="fa fa-plus"></i> Add Employee
								</a>
							</div>
						</div>
						<hr>
						
						<div class="card-body table-responsive">
							<table class="table table-hover">
								<thead>
									<tr>
										<th>Employee Id</th>
										<th>Name</th>
										<th>Email</th>
										<th>Phone No</th>
										<th>Designation</th>
										<th>Status</th>
										<th>Action</th>
									</tr>
								</thead>
								<tbody>
									<?php
									$category = $conn->query("SELECT a.*,b.des_name FROM employee as a INNER JOIN designations as b on a.emp_designation_id = b.des_id order by a.emp_id asc");
									while($row=$category->fetch_assoc()):
										?>
										<tr>
											<td class="text-center">EMP-<?php echo $row['emp_id'] ?></td>
											<td class=""><?php echo $row['emp_name'] ?></td>
											<td><?php echo $row['emp_email'] ?></td>
											<td><?php echo $row['emp_ph_no'] ?></td>
											<td><?php echo $row['des_name'] ?></td>
											<td><?php
											if($row['emp_status'] == 0){
												?><span style="color:green"><b>Active</b></span><?php
											}else{
												?>
												<span style="color: red"><b>In-Active</b></span>
												<?php
											}
											?></td>
											<td class="text-center">
												<a class="icon-action edit" title="Edit Employee" href="index.php?page=Employees/edit-employee&id=<?php echo $row['emp_id'] ?>"><i class="fa fa-edit"></i></a>
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
