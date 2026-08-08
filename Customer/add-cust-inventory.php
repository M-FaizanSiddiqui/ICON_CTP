<?php include('db_connect.php');

if(in_array(16,$_SESSION['login_Permisions']))
{
	?>
	<div class="container-fluid professional-form-page">
		
		<div class="col-lg-12">
			<div class="row">
				<!-- FORM Panel -->
				<div class="col-md-12">
					<form action="" id="manage_inventory_item">
						<div class="card professional-form-card">
							<div class="card-header">
								<span class="form-title-icon"><i class="fas fa-boxes"></i></span>
								<div class="form-title-copy"><h2>Receive Customer Inventory</h2><p>Record plate quantities received for a customer.</p></div>
							</div>
							<div class="card-body">
								<input type="hidden" name="ird_id">

								<div class="row">
									<div class="col-md-3">
										<div class="form-group">
											<label class="control-label"><b>Customre:</b></label>
											<select name="cust_id" id="cust_id" class="form-control" required="true">
												<option value="">Select customer</option>
												<?php
												$query_cust = "SELECT * FROM customers";
												$result_cust = mysqli_query($conn,$query_cust);
												while($data_cust = mysqli_fetch_array($result_cust)){
													?>
													<option value="<?php echo $data_cust['cust_id'] ?>"><?php echo $data_cust["cust_name"] ?></option>
													<?php
												}
												?>
											</select>
										</div>
									</div>
									
								</div>
								


								<div class="table-responsive">
									<table class="table table-bordered" id="customer_receive_detail_table">
										<tr>
											<th style="width: 20%" class="text-center">Plate</th>
											<th>Qty</th>
										</tr>
										<?php 
										$query_item = "SELECT * FROM inventory_item";
										$result_item = mysqli_query($conn,$query_item);
										while($data_item = mysqli_fetch_array($result_item)){
											?>
											<tr>
												<td class="text-center">
													<?php echo $data_item['item_id'] ?> - <?php echo $data_item['item_name'] ?> 
													<input type="hidden" value="<?php echo $data_item['item_id'] ?>" name="item_id[]">
												</td>
												<td><input type="text" placeholder="Quantity" name="quantity[]" class="form-control" value="0"></td>
											</tr>
											<?php
										}
										?>
										
									</table>
								</div>
							</div>

							<div class="card-footer">
								<div class="row">
									<div class="col-md-12">
										<button class="btn btn-default" type="button" onclick="$('#manage_inventory_item').get(0).reset()"><i class="fa fa-undo"></i> Reset</button>
										<button class="btn btn-primary"><i class="fa fa-save"></i> Receive Inventory</button>
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
		$('#manage_inventory_item').on('reset',function(){
			$('input:hidden').val('')
		})

		
		$('#manage_inventory_item').submit(function(e){
			e.preventDefault()
			start_load()
			$.ajax({
				url:'ajax.php?action=save_receive_inventory_cust',
				data: new FormData($(this)[0]),
				cache: false,
				contentType: false,
				processData: false,
				method: 'POST',
				type: 'POST',
				success:function(resp){
					if(resp==1){
						alert_toast("Data successfully added",'success')
						setTimeout(function(){
							window.open('index.php?page=Customer/customer-inventory','_self');
						},1500)

					}
					else{
						alert_toast(resp,'danger')
					}
				}
			})
		})
		
		$('table').dataTable()
	</script>



	<?php
}else{
	include 'accessDenied.php';
}
?>
