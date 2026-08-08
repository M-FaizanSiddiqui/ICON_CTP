<?php include('db_connect.php');

if(in_array("50",$_SESSION['login_Permisions']))
{
	?>
	<div class="container-fluid professional-view-page view-waste-inventory">
		<div class="row view-summary-grid">
			<div class="col-md-4"><div class="view-summary-card"><span class="view-summary-icon"><i class="fa fa-list"></i></span><div class="view-summary-copy"><h6>Waste Entries</h6><h3><?php echo $conn->query("SELECT COUNT(*) AS c FROM waste_inventory")->fetch_assoc()['c']; ?></h3></div></div></div>
			<div class="col-md-4"><div class="view-summary-card is-inactive"><span class="view-summary-icon"><i class="fa fa-recycle"></i></span><div class="view-summary-copy"><h6>Total Waste Quantity</h6><h3><?php echo $conn->query("SELECT COALESCE(SUM(qty),0) AS c FROM waste_inventory")->fetch_assoc()['c']; ?></h3></div></div></div>
			<div class="col-md-4"><div class="view-summary-card is-active"><span class="view-summary-icon"><i class="fa fa-briefcase"></i></span><div class="view-summary-copy"><h6>Tagged Jobs</h6><h3><?php echo $conn->query("SELECT COUNT(DISTINCT job_id) AS c FROM waste_inventory WHERE job_id<>''")->fetch_assoc()['c']; ?></h3></div></div></div>
		</div>

		<div class="col-lg-12">

			<div class="row">
				<div class="col-md-12">
					<div class="card professional-view-card">
						<div class="row" style="background-color:#f9f9f9">
							<div class="col-lg-6">
								<div class="view-heading"><span class="view-heading-icon"><i class="fa fa-recycle"></i></span><div><h2>Waste Inventory</h2><p>Recorded plate waste and related job details.</p></div></div>
							</div>
							<div class="col-lg-6">
								<a class="btn btn-primary btn-sm float-right m-3" href="index.php?page=Stocks/waste-item" id="new_order">
									<i class="fa fa-plus"></i> Add Waste
								</a>
							</div>
						</div>
						<hr>
						<div class="card-body table-responsive">
							<table class="table table-hover">
								<thead>
									<tr>
										<th class="text-center" style="width: 60px">Waste No</th>
										<th class="text-center" style="width: 60px">Item ID</th>
										<th class="text-center" style="width: 100px">Item Name</th>
										<th class="text-center" style="width: 80px">Quantity</th>
										<th class="text-center" style="width: 80px">Dated</th>
										<th class="text-center">Remarks</th>
									</tr>
								</thead>
								<tbody>
									<?php
									$category = $conn->query("SELECT a.*,b.item_name FROM waste_inventory as a INNER JOIN inventory_item as b on a.item_id = b.item_id order by a.w_id desc");
									while($row=$category->fetch_assoc()):
										?>
										<tr>
											<td class="text-center">W-<?php echo $row['w_id'] ?></td>
											<td class="text-center"><?php echo $row['item_id'] ?></td>
											<td class="text-center"><?php echo $row['item_name'] ?></td>
											<td class="text-center"><?php echo $row['qty'] ?></td>
											<td class="text-center"><?php echo date('d-M-Y',strtotime($row['dated'])) ?></td>
											<td><?php echo $row['remarks']?></td>
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
			$(document).ready( function() {
				$('table').dataTable( {
					order: [[0, 'desc']]
				});
			});

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
			$('.delete_items').click(function(){
				_conf("Are you sure to delete this supplier?","delete_items",[$(this).attr('data-id')])
			})
			function delete_items($id){
				start_load()
				$.ajax({
					url:'ajax.php?action=delete_items',
					method:'POST',
					data:{item_id:$id},
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
