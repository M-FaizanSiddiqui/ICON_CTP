<?php include('db_connect.php');

$module_for_active =16;
if(in_array(16,$_SESSION['login_Permisions']))
{
	?>
	<style>
		.card-footer{margin-bottom: 26px;}
	</style>
	<div class="container-fluid">

		<div class="col-lg-12">
			<div class="row">
				<!-- FORM Panel -->
				<div class="col-md-12">

					<?php 
					if(isset($_POST['Insert_job'])){
						mysqli_query($conn,"START TRANSACTION");
						$error = 0;
						$error_message = "";

						$total_amount = 0;
						$supplier_id = mysqli_real_escape_string($conn,$_POST['supplier_id']);
						$order_rec_date = mysqli_real_escape_string($conn,$_POST['order_rec_date']);
						
						if(trim($supplier_id," ") == ""){
							$error++;
							$error_message .= '<li>Supplier cannot be empty</li>';
						}
						if(trim($order_rec_date," ") == ""){
							$error++;
							$error_message .= '<li>Received Date cannot be empty</li>';
						}
						
						
						if(count($_POST['item_id']) == 0){
							$error++;
							$error_message .= '<li>Atleast add 1 Paper Item in your order.</li>';
						}else{
							$counter = 0;
							$paper_ids_array = array();
							$rate_array = array();
							$amount_array = array();
							$qty_array = array();
							for($i=0; $i<count($_POST['item_id']); $i++){

								$counter++;
								$item_id = mysqli_real_escape_string($conn,$_POST['item_id'][$i]);
								$quantity = mysqli_real_escape_string($conn,$_POST['quantity'][$i]);
								$rate = mysqli_real_escape_string($conn,$_POST['rate'][$i]);
								$amount = mysqli_real_escape_string($conn,$_POST['amount'][$i]);
								

								if($item_id == ""){
									$error++;
									$error_message .= '<li>Paper Item cannot be empty at Index '.$counter.'</li>';
									array_push($paper_ids_array,0);
								}else{
									if(in_array($item_id, $paper_ids_array)){
										$error++;
										$error_message .= '<li>Duplicated Paper Item at Index '.$counter.'</li>';
										array_push($paper_ids_array,$item_id);
									}else{
										array_push($paper_ids_array,$item_id);
									}
								}

								if($quantity==0 || $quantity == ""){
									$error++;
									$error_message .= '<li>Quantity cannot be empty at Index '.$counter.'</li>';
									array_push($qty_array,0);
								}else{
									array_push($qty_array,$quantity);
								}

								if($rate==0 || $rate == ""){
									$error++;
									$error_message .= '<li>Rate cannot be empty at Index '.$counter.'</li>';
									array_push($qty_array,0);
								}else{
									array_push($qty_array,$rate);
								}

								if($amount==0 || $amount == ""){
									$error++;
									$error_message .= '<li>Amount cannot be empty at Index '.$counter.'</li>';
									
									array_push($qty_array,0);
								}else{
									$total_amount += $amount;
									array_push($qty_array,$amount);
								}
							}
						}


						if($error >0){
							?>
							<div class="col-md-12" style="border:1px solid red">
								<div>
									<h3 style="color:red">ERRORS!</h3>
									<ul>
										<?php echo $error_message; ?>
									</ul>
								</div>
							</div>
							<?php
						}else{


							$queryJbOrder = " INSERT INTO supplier_order set supp_id = '".$supplier_id."' ";
							$queryJbOrder .= ", dated = '".$order_rec_date."' ";
							$queryJbOrder .= ", user_id = '".$_SESSION['login_id']."' ";
							$queryJbOrder .= ", total_amount = '".$total_amount."' ";

							$query1 = mysqli_query($conn,$queryJbOrder);

							$sup_order_id = $conn->insert_id;

							

							$query3_up = 1;
							for($i=0; $i<count($_POST['item_id']); $i++){

								$item_id = mysqli_real_escape_string($conn,$_POST['item_id'][$i]);
								$quantity = mysqli_real_escape_string($conn,$_POST['quantity'][$i]);
								$rate = mysqli_real_escape_string($conn,$_POST['rate'][$i]);
								$amount = mysqli_real_escape_string($conn,$_POST['amount'][$i]);

								$queryJbDetails = " INSERT INTO supplier_order_details SET sr_id = ".$sup_order_id;
								$queryJbDetails .= ", item_id = ".$item_id;
								$queryJbDetails .= ", qty = ".$quantity;
								$queryJbDetails .= ", rate = ".$rate;
								$queryJbDetails .= ", amount = ".$amount;

								$query3 = mysqli_query($conn,$queryJbDetails);
								if(!$query3){$query3_up = 2;}
							}

							if($query1 && $query3_up){
								mysqli_query($conn,"COMMIT");
								?>
								<script>
									alert("Supplier Order successfully added")
								</script>
								<?php
							}else{
								mysqli_query($conn,"ROLLBACK");
								?>
								<script>
									alert_toast("Error",'danger');
								</script>
								<?php
							}
						}
					}else{
						$supplier_id = "";
						$order_rec_date = "";

						$paper_ids_array=array(0);
						$qty_array=array(0);
					}
					?>

					<form id="manage-job-order" method="POST" enctype="multipart/form-data">
						<div class="card">
							<div class="card-header">
								<b>Supplier Order</b>
							</div>
							<div class="card-body">
								<input type="hidden" name="jd_id">

								<div class="row">
									<div class="col-md-4 form-group">
										<label class="control-label">Supplier</label>
										<select  name="supplier_id" class="form-control" >
											<option value="">Please Select</option>
											<?php
											$query_supp = "SELECT * FROM suppliers WHERE supp_status = 0";
											$result_supp = mysqli_query($conn,$query_supp);
											while($data_supp = mysqli_fetch_array($result_supp)){
												$selected_item = "";
												if($supplier_id == $data_supp['supp_id']){
													$selected_item = "Selected";
												}
												?>
												<option <?php echo $selected_item ?> value="<?php echo $data_supp['supp_id'] ?>"><?php echo $data_supp["supp_name"] ?></option>
												<?php
											}
											?>
										</select>
									</div>

									

									<div class="col-md-4 form-group">
										<label class="control-label">Received Date</label>
										<input type="date" class="form-control" value="<?php echo $order_rec_date ?>" name="order_rec_date">
									</div>

									
								</div>

								<div class="col-md-12">
									<button type="button" id="add_new_row" class="btn btn-primary">Add New Row</button>
									<table class="table-bordered" id="job_item_table">
										<tr>
											<th>Plate</th>
											<th>Quantity</th>
											<th>Rate</th>
											<th>Amount</th>
											<th>Action</th>
										</tr>
										<?php 
										for($ii=0; $ii<count($paper_ids_array); $ii++){
											?>
											<tr>
												<td>
													<select  name="item_id[]" class="form-control" >
														<option value="">Please Select</option>
														<?php
														$query_supp = "SELECT * FROM inventory_item WHERE status = 0";
														$result_supp = mysqli_query($conn,$query_supp);
														while($data_supp = mysqli_fetch_array($result_supp)){
															$selected_item = "";
															if($paper_ids_array[$ii] == $data_supp['item_id']){
																$selected_item = "Selected";
															}
															?>
															<option <?php echo $selected_item ?> value="<?php echo $data_supp['item_id'] ?>"><?php echo $data_supp["item_name"] ?></option>
															<?php
														}
														?>
													</select>
												</td>
												<td>
													<input type="number" value="<?php echo $qty_array[$ii] ?>" class="form-control quantity" name="quantity[]">
												</td>

												<td>
													<input type="number" value="<?php echo $qty_array[$ii] ?>" class="form-control rate" name="rate[]">
												</td>

												<td>
													<input type="number" value="<?php echo $qty_array[$ii] ?>" class="form-control amount" name="amount[]">
												</td>
												
												<td class="text-center">
													<?php 
													if($ii == 0){
														?>
														<span>-</span>
														<?php 
													}else{
														?>
														<span class="del_row" style="color:red;cursor: pointer"><i class="fa fa-trash-alt"></i></span>
														<?php
													}
													?>
													
												</td>
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
										<button class="btn btn-success" type="submit" name="Insert_job" id="Insert_job"> Save</button>
										<button class="btn btn-default" type="button" onclick="$('#manage-job-order').get(0).reset()"> Cancel</button>
									</div>
								</div>
							</div>
						</div>
					</form>
				</div>
				<!-- FORM Panel -->
			</div>
		</div>

		<?php
		$item_list = '<select  name="item_id[]" class="form-control">';
		$item_list .= '<option value="">Please Select</option>';
		$query_supp = "SELECT * FROM inventory_item WHERE status = 0";
		$result_supp = mysqli_query($conn,$query_supp);
		while($data_supp = mysqli_fetch_array($result_supp)){
			$item_list .= '<option value="'.$data_supp['item_id'].'">'.$data_supp["item_name"].'</option>';			
		}
		$item_list .= '</select>';


		?>

		<script>
			$('#manage-job-order').on('reset',function(){
				$('input:hidden').val('')
			})

			$(document).ready(function(){
				$(document).on("keyup",".rate",function() {
					var qty = $(this).closest('tr').find('.quantity').val();
					var rate = $(this).closest('tr').find('.rate').val();
					var amt = 0;
					if(qty != "" && rate != ""){
						amt = qty * rate;
						$(this).closest('tr').find('.amount').val(amt);
					}
				})
				$(document).on("keyup",".quantity",function() {
					var qty = $(this).closest('tr').find('.quantity').val();
					var rate = $(this).closest('tr').find('.rate').val();
					var amt = 0;
					if(qty != "" && rate != ""){
						amt = qty * rate;
						$(this).closest('tr').find('.amount').val(amt);
					}
				})
				$(document).on("click",".del_row",function() {
					this.closest('tr').remove();
				});

				$('#add_new_row').click(function(event) {
					var new_row = '';
					new_row += '<tr>';
					new_row += '<td><?php echo $item_list ?></td>';
					new_row += '<td><input type="number" class="form-control quantity" name="quantity[]"></td>';
					new_row += '<td><input type="number" class="form-control rate" name="rate[]"></td>';
					new_row += '<td><input type="number" class="form-control amount" name="amount[]"></td>';

					new_row += '<td class="text-center"><span class="del_row" style="color:red;cursor: pointer"><i class="fa fa-trash-alt"></i></span></td>';
					new_row += '</tr>';

					$('#job_item_table').append(new_row);
				});
			})

			$('table').dataTable()

		</script>

	</div>
	<style>

		td{
			vertical-align: middle !important;
		}
		td p {
			margin:unset;
		}
	</style>




	<?php
}else{
	include 'accessDenied.php';
}
?>