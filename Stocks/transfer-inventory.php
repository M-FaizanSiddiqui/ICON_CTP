<?php include('db_connect.php');

if(in_array(25,$_SESSION['login_Permisions']))
{
	?>
	<div class="container-fluid professional-form-page">

		<div class="col-lg-12">
			<div class="row">
				<!-- FORM Panel -->
				<div class="col-md-12">
					<form action="" id="transfer_inventory">
						<div class="card professional-form-card">
							<div class="card-header">
								<span class="form-title-icon"><i class="fas fa-exchange-alt"></i></span>
								<div class="form-title-copy"><h2>Transfer Inventory</h2><p>Move plate stock between ICON and customer inventory.</p></div>
							</div>
							<div class="card-body">
								<input type="hidden" name="transfer_id">
								<div class="form-group col-md-3">
									<label><b>Receive In:</b></label>
									<select class="form-control" id="inventory_in_out" name="inventory_in_out">
										<option value="">Select destination</option>
										<option value="1">ICON Inventory</option>
										<option value="2">Customer Inventory</option>
									</select>
								</div>

								<div id="ICON_Form">
									<div class="form-group col-md-12">
										<label class="control-label" id="cust_label"><b>From Customer:</b></label>
										<select class="form-control" id="customer_id" name="customer_id">
											<option value="">Select customer</option>
											<?php
											$query_supp = "SELECT * FROM customers WHERE cust_status = 0";
											$result_supp = mysqli_query($conn,$query_supp);
											while($data_supp = mysqli_fetch_array($result_supp)){
												?>
												<option value="<?php echo $data_supp['cust_id'] ?>"><?php echo $data_supp["cust_name"] ?></option>
												<?php
											}
											?>
										</select>
									</div>
									<div class="row">
										<div class="col-md-6">
											<label class="control-label"><b>Plate:</b></label>
											<select  name="plate_id" id="plate_id" class="form-control">
												<option value="">Select plate</option>
												<?php
												$query_supp = "SELECT * FROM inventory_item WHERE status = 0";
												$result_supp = mysqli_query($conn,$query_supp);
												while($data_supp = mysqli_fetch_array($result_supp)){
													?>
													<option value="<?php echo $data_supp['item_id'] ?>"><?php echo $data_supp["item_name"] ?></option>
													<?php
												}
												?>
											</select>
										</div>
										<div class="col-md-6">
											<label class="control-label"><b>Quantity Available:</b></label>
											<input type="text" readonly="true" name="qty_available" class="form-control" id="qty_available">
										</div>
										<div class="form-group col-md-6">
											<label class="control-label"><b>Quantity</b></label>
											<input type="text" class="form-control" name="quantity" id="quantity" placeholder="Enter quantity to transfer">
										</div>

										<div class="form-group col-md-6">
											<label class="control-label"><b>Transfer Date:</b></label>
											<input type="date" value="" class="form-control" name="transfer_date" id="transfer_date">
										</div>

										<div class="form-group col-md-12">
											<label class="control-label"><b>Remarks:</b></label>
											<input type="text" value="" class="form-control" name="remarks" id="remarks" placeholder="Add transfer remarks">
										</div>
									</div>


								</div>



							</div>

							<div class="card-footer">
								<div class="row">
									<div class="col-md-12">
										<button class="btn btn-default" type="button" onclick="$('#transfer_inventory').get(0).reset()"><i class="fa fa-undo"></i> Reset</button>
										<button class="btn btn-primary"><i class="fas fa-exchange-alt"></i> Transfer Inventory</button>
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

		$(document).ready(function() {

			$('#ICON_Form').hide();

			$('#inventory_in_out').change(function(event) {
				let val = $('#inventory_in_out').val();
				if(val==1){
					$('#cust_label').html('<b>From Customer:</b>');
					$('#ICON_Form').show();
					$('#qty_available').val(0);
					$('#plate_id').val('');
					$('#customer_id').val('');
				}
				else if(val==2){
					$('#cust_label').html('<b>To Customer:</b>');
					$('#ICON_Form').show();
					$('#qty_available').val(0);
					$('#plate_id').val('');
					$('#customer_id').val('');
				}else{
					$('#ICON_Form').hide();
					$('#qty_available').val(0);
					$('#plate_id').val('');
					$('#customer_id').val('');
				}

			});

			$('#customer_id').change(function(event) {
				var plate_id = $('#plate_id').val();
				var inventory_in_out = $('#inventory_in_out').val();
				var cust_id = $('#customer_id').val();
				if(inventory_in_out==1){
					if(plate_id!='' && cust_id!=''){
						$.ajax({
							url : "ajax-req/ajax_request.php",
							method : "POST",
							data : {plate_id : plate_id,inventory_in_out:inventory_in_out,cust_id:cust_id,req_no: 4},
							dataType : "text",
							success : function(data){
								$('#qty_available').val(data);
								$('#quantity').attr('max',data);
							}
						});
					}else{
						$('#qty_available').val(0);
						$('#quantity').attr('max',0);
					}
				}
			});

			$('#plate_id').change(function(event) {
				var plate_id = $('#plate_id').val();
				var inventory_in_out = $('#inventory_in_out').val();
				var cust_id = $('#customer_id').val();

				if(inventory_in_out==1){
					if(plate_id!='' && cust_id!=''){
						$.ajax({
							url : "ajax-req/ajax_request.php",
							method : "POST",
							data : {plate_id : plate_id,inventory_in_out:inventory_in_out,cust_id:cust_id,req_no: 4},
							dataType : "text",
							success : function(data){
								$('#qty_available').val(data);
								$('#quantity').attr('max',data);
							}
						});
					}else{
						$('#qty_available').val(0);
						$('#quantity').attr('max',0);
					}
				}else if(inventory_in_out==2){
					if(plate_id != ''){
						cust_id = 0;
						$.ajax({
							url : "ajax-req/ajax_request.php",
							method : "POST",
							data : {plate_id : plate_id,inventory_in_out:inventory_in_out,cust_id:cust_id,req_no: 4},
							dataType : "text",
							success : function(data){
								$('#qty_available').val(data);
								$('#quantity').attr('max',data);
							}
						});
					}else{
						$('#qty_available').val(0);
						$('#quantity').attr('max',0);
					}
				}else{
					$('#qty_available').val(0);
					$('#quantity').attr('max',0);
				}


			});

		});



		$('#transfer_inventory').on('reset',function(){
			$('input:hidden').val('')
		})

		$('#transfer_inventory').submit(function(e){
			e.preventDefault();
			start_load();

			var inventory_in_out = $('#inventory_in_out').val();
			var customer_id = $('#customer_id').val();
			var plate_id = $('#plate_id').val();
			var qty_available = $('#qty_available').val();
			var quantity = $('#quantity').val();
			var go_post = '';
			if(inventory_in_out==''){
				go_post = 1;
				alert_toast("Receive In cannot be empty.",'warning');
			}
			else if(customer_id==''){
				go_post = 1;
				alert_toast("Customer cannot be empty.",'warning');
			}
			else if(plate_id==''){
				go_post = 1;
				alert_toast("Plate cannot be empty.",'warning');
			}
			else if(quantity==''){
				go_post = 1;
				alert_toast("Quantity cannot be empty.",'warning');
			}
			else if(quantity > qty_available){
				go_post = 1;
				alert_toast("Quantity cannot be more then Available Quantity.",'warning');
			}

			if(go_post == ''){
				$.ajax({
					url:'ajax.php?action=save_transfer_inventory',
					data: new FormData($(this)[0]),
					cache: false,
					contentType: false,
					processData: false,
					method: 'POST',
					type: 'POST',
					success:function(resp){
						if(resp==1){
							alert_toast("Data successfully Saved",'success')
							setTimeout(function(){
								window.open('index.php?page=Stocks/view-items','_self');
							},1500);
						}
						else{
							alert_toast(resp,'danger');
						}
					}
				})
			}

		})

		$('table').dataTable()
	</script>



	<?php
}else{
	include 'accessDenied.php';
}
?>
