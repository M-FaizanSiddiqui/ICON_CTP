<?php include('db_connect.php');

if(in_array("35",$_SESSION['login_Permisions']))
{
	?>
	<div class="container-fluid professional-view-page view-account-types">
		<div class="row view-summary-grid">
			<div class="col-md-4"><div class="view-summary-card"><span class="view-summary-icon"><i class="fa fa-list"></i></span><div class="view-summary-copy"><h6>Total Account Types</h6><h3><?php echo $conn->query("SELECT COUNT(*) AS c FROM account_types WHERE del_status=0")->fetch_assoc()['c']; ?></h3></div></div></div>
			<div class="col-md-4"><div class="view-summary-card is-active"><span class="view-summary-icon"><i class="fa fa-folder"></i></span><div class="view-summary-copy"><h6>Parent Types</h6><h3><?php echo $conn->query("SELECT COUNT(*) AS c FROM account_types WHERE del_status=0 AND type_parent_id=0")->fetch_assoc()['c']; ?></h3></div></div></div>
			<div class="col-md-4"><div class="view-summary-card"><span class="view-summary-icon"><i class="fa fa-sitemap"></i></span><div class="view-summary-copy"><h6>Sub Types</h6><h3><?php echo $conn->query("SELECT COUNT(*) AS c FROM account_types WHERE del_status=0 AND type_parent_id<>0")->fetch_assoc()['c']; ?></h3></div></div></div>
		</div>

		<div class="col-lg-12">
			<div class="row">
				<div class="col-md-12">
					<div class="card professional-view-card">
						<div class="row" style="background-color:#f9f9f9">
							<div class="col-lg-6">
								<div class="view-heading"><span class="view-heading-icon"><i class="fa fa-sitemap"></i></span><div><h2>Account Types</h2><p>Manage the accounting classification hierarchy.</p></div></div>
								<!-- <img src="assets/module_img/supplier.png" class="mt-1" style="width:250px"> -->
							</div>
							<div class="col-lg-6">
								<a class="btn btn-primary nav-add-supplier btn-sm float-right m-3" href="index.php?page=Accounting/add-acc-type" id="new_order">
									<i class="fa fa-plus"></i> Add Account Type
								</a>
							</div>
						</div>
						<hr>
						<div class="card-body table-responsive">
							<table class="table table-bordered">
								<thead>
									<tr>
										<th style="width: 5%">SR#</th>
										<th style="width: 10%">Code</th>
										<th colspan="3">Name</th>
									</tr>
								</thead>
								<tbody>
									<?php
									$counter = 0;
									$category = $conn->query("SELECT * FROM account_types WHERE type_parent_id = 0 order by acc_type_id asc");
									while($row=$category->fetch_assoc()):
										$counter++;
										?>
										<tr>
											<td class="text-center" style="color:blue"><?php echo $counter ?></td>
											<td class="text-center" style="color:blue">CD-000<?php echo $row['acc_type_id'] ?></td>
											<td colspan="3" style="color:blue"><?php echo $row['type_name'] ?></td>

										</tr>
										<?php
										$category_de = $conn->query("SELECT * FROM account_types WHERE type_parent_id = ".$row['acc_type_id']." AND del_status = 0 order by acc_type_id asc");
										if(mysqli_num_rows($category_de)>0){
											?>
											<tr>
												<th class="text-center"></th>
												<th class="text-center"></th>
												<th class="text-center" style="width: 15%">Code</th>
												<th class="text-center">Name</th>
												<th class="text-center" style="width: 10%">Action</th>
											</tr>
											<?php
											while($row_de=$category_de->fetch_assoc()):
												?>
												<tr>
													<td></td>
													<td></td>
													<td class="text-center">CD-000<?php echo $row_de['acc_type_id'] ?></td>
													<td><?php echo $row_de['type_name'] ?></td>
													<td class="text-center">
														<button class="btn btn-sm btn-danger delete_acc_type" type="button" data-id="<?php echo $row_de['acc_type_id'] ?>">
													<i class="fa fa-trash"></i>
														</button>
													</td>
												</tr>
												<?php
											endwhile;
										}
										?>
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

			$('.delete_acc_type').click(function(){
				_conf("Are you sure to delete this Account Types?","delete_acc_type",[$(this).attr('data-id')])
			})
			function delete_acc_type($id){
				start_load()
				$.ajax({
					url:'ajax.php?action=delete_acc_type',
					method:'POST',
					data:{acc_type_id:$id},
					success:function(resp){
						if(resp==1){
							alert_toast("Data successfully deleted",'success')
							setTimeout(function(){
								location.reload()
							},1500)

						}else{
							alert(resp);
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
