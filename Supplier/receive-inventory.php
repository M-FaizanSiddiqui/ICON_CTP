<?php include('db_connect.php');

if(in_array("4",$_SESSION['login_Permisions']))
{
	?>
	<div class="container-fluid px-3 mt-3">

		<div class="col-lg-12">

			<div class="row">
				<div class="col-md-12">

					<div class="card border-0 shadow-sm mb-3">
						<div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
							<div>
								<h5 class="mb-0 fw-semibold" style="color: var(--accent);">Receive Inventory</h5>
								<small class="text-muted">All Inventory Details which are recevied!</small>
							</div>

							<a href="index.php?page=Supplier/add-receive-inventory" class="btn btn-primary btn-sm px-3">
								<i class="fa fa-plus"></i> New
							</a>
						</div>
					</div>

					<div class="card">
						
						<div class="card-body">
							<table class="table table-hover">
								<thead>
									<tr>
										<th>ID</th>
										<th>Supplier Name</th>
										<th>Order No</th>
										<th>Received Date</th>
										<th>Document No</th>
										<th>User</th>
										<th>Action</th>
									</tr>
								</thead>
								<tbody>
									<?php
									$category = $conn->query("SELECT a.*,b.supp_name,c.name as user_name FROM inventory_received as a INNER JOIN suppliers as b on a.supplier_id = b.supp_id INNER JOIN users as c on a.user_id = c.id  order by a.ir_id asc");
									while($row=$category->fetch_assoc()):
										?>
										<tr>
											<td class="text-center">REC-00<?php echo $row['ir_id'] ?></td>
											<td class=""><?php echo $row['supp_name'] ?></td>
											<td class="">ORD-<?php echo $row['supp_order_id']?>
											<td><?php echo $row['received_date'] ?></td>
											<td><?php echo $row['doc_no'] ?></td>
											<td><?php echo $row['user_name'] ?></td>

											<td>
												<a class="btn btn-success btn-sm " href="index.php?page=Supplier/view-receive-inventory-details&id=<?php echo $row['ir_id'] ?>" id="view_order">
													<i class="fa fa-eye"></i> 
												</a>
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
				$('table').dataTable()
			})

			$('.edit_supplier').click(function(){
				start_load()
				var cat = $('#manage-supplier')
				cat.get(0).reset()
				cat.find("[name='supp_id']").val($(this).attr('data-id'))
				cat.find("[name='supp_name']").val($(this).attr('data-name'))
				cat.find("[name='supp_ph_no']").val($(this).attr('data-ph-no'))
				cat.find("[name='supp_email']").val($(this).attr('data-email'))
				cat.find("[name='supp_address']").val($(this).attr('data-address'))
				end_load()
			})
			$('.delete_receive_inventory').click(function(){
				_conf("Are you sure to delete this supplier?","delete_receive_inventory",[$(this).attr('data-id')])
			})
			function delete_receive_inventory($id){
				start_load()
				$.ajax({
					url:'ajax.php?action=delete_receive_inventory',
					method:'POST',
					data:{ird_id:$id},
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