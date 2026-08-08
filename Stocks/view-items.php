<?php include('db_connect.php');

if(in_array("22",$_SESSION['login_Permisions']))
{
	?>
	<div class="container-fluid professional-view-page view-items">
		<div class="row view-summary-grid">
			<div class="col-md-4"><div class="view-summary-card"><span class="view-summary-icon"><i class="fa fa-cubes"></i></span><div class="view-summary-copy"><h6>Total Plates</h6><h3><?php echo $conn->query("SELECT COUNT(*) AS c FROM inventory_item")->fetch_assoc()['c']; ?></h3></div></div></div>
			<div class="col-md-4"><div class="view-summary-card is-active"><span class="view-summary-icon"><i class="fa fa-check-circle"></i></span><div class="view-summary-copy"><h6>Active Plates</h6><h3><?php echo $conn->query("SELECT COUNT(*) AS c FROM inventory_item WHERE status=0")->fetch_assoc()['c']; ?></h3></div></div></div>
			<div class="col-md-4"><div class="view-summary-card is-inactive"><span class="view-summary-icon"><i class="fa fa-pause-circle"></i></span><div class="view-summary-copy"><h6>Inactive Plates</h6><h3><?php echo $conn->query("SELECT COUNT(*) AS c FROM inventory_item WHERE status=1")->fetch_assoc()['c']; ?></h3></div></div></div>
		</div>

		<div class="col-lg-12">

			<div class="row">
				<div class="col-md-12">
					<div class="card professional-view-card">
						<div class="row" style="background-color:#f9f9f9">
							<div class="col-lg-6">
								<div class="view-heading"><span class="view-heading-icon"><i class="fa fa-cubes"></i></span><div><h2>Plate Inventory</h2><p>Available plates, booked quantities, and rates.</p></div></div>
							</div>
							<div class="col-lg-6">
								<a class="btn btn-primary btn-sm float-right m-3" href="index.php?page=Stocks/add-items" id="new_order">
									<i class="fa fa-plus"></i> Add Plate
								</a>
							</div>
						</div>
						<hr>
						<div class="card-body table-responsive">
							<table class="table table-hover">
								<thead>
									<tr>
										<th>Item ID</th>
										<th>Name</th>
										<th>Size</th>
										<th>HL Inches</th>
										<th>Quantity</th>
										<th>Quantity Booked</th>
										<th>Avg Rate</th>
										<th>Imposing Price</th>
										<th>OvenBake Item</th>
										<th>Status</th>
									</tr>
								</thead>
								<tbody>
									<?php
									$category = $conn->query("SELECT * FROM inventory_item order by item_id asc");
									while($row=$category->fetch_assoc()):
										?>
										<tr>
											<td class="text-center">IT-<?php echo $row['item_id'] ?></td>
											<td class=""><?php echo $row['item_name'] ?></td>
											<td><?php echo $row['size_in_mm'] ?></td>
											<td><?php echo $row['hl_inches'] ?></td>
											<td><?php echo $row['quantity'] ?></td>
											<td><?php echo $row['qty_booked'] ?></td>
											<td><?php echo $row['avg_rate'] ?></td>
											<td><?php echo $row['imposition_charges'] ?></td>
											<td><?php echo $row['OvenBake_Charges'] ?></td>
											<td><?php
											if($row['status'] == 0){
												?><span style="color:green"><b>Active</b></span><?php
											}else{
												?>
												<span style="color: red"><b>In-Active</b></span>
												<?php
											}
										?></td>
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
				order: [[0, 'asc']],
				"pageLength": 50,
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
