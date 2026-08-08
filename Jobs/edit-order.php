<?php include('db_connect.php');

if(in_array("31",$_SESSION['login_Permisions']))
{
	
	$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
	if($order_id > 0){
		$myq = "SELECT a.*,b.cust_name as custName FROM job_order as a INNER JOIN customers as b on a.customer_id = b.cust_id where a.jd_id =".$order_id;
		$order = $conn->query($myq);

		$row_cnt = $order->num_rows;
		
		if($row_cnt>0){
			foreach($order->fetch_array() as $k => $v){
				$$k= $v;
			}
			$old_customer_id = (int)$customer_id;
			$old_job_effect = (int)$job_effect;

			$items = $conn->query("SELECT a.*,b.item_name FROM job_order_details as a inner join inventory_item as b on a.item_id = b.item_id where a.delete_status = 0 AND a.job_id = ".$order_id);

			if($order_status != 2){
				?>
				<div class="container-fluid professional-edit-job-page">

					<div class="col-lg-12">
						<?php
						if(isset($_POST['update_job'])){

							$error = 1;
							$error_message = "";

							$job_name = mysqli_real_escape_string($conn,$_POST['job_name']);
							$customer_id = mysqli_real_escape_string($conn,$_POST['customer_id']);
							$job_description = mysqli_real_escape_string($conn,$_POST['job_description']);
							$order_rec_date = mysqli_real_escape_string($conn,$_POST['order_rec_date']);
							if(trim($job_name," ") == ""){
								$error++;
								$error_message .= '<li>Job Name cannot be empty</li>';
							}
							if(trim($customer_id," ") == ""){
								$error++;
								$error_message .= '<li>Customer cannot be empty</li>';
							}
							if(trim($job_description," ") == ""){
								$error++;
								$error_message .= '<li>Job Description cannot be empty</li>';
							}
							if(trim($order_rec_date," ") == ""){
								$error++;
								$error_message .= '<li>Order Received Date cannot be empty</li>';
							}
						// echo count($_FILES['attachment']['name']);

						// $Img_name = mysqli_real_escape_string($conn,$_FILES['attachment']['name'][$i]);
							if(mysqli_real_escape_string($conn,$_FILES['attachment']['name'][0]) == ""){
								$error++;
								$error_message .= '<li>Attachment cannot be empty</li>';
							}

							if(count($_POST['item_id']) == 0){
								$error++;
								$error_message .= '<li>Atleast add 1 Paper Item in your order.</li>';
							}else{
								$counter = 0;
								$amount_array = array();
								$paper_ids_array = array();
								$price_array = array();
								$qty_array = array();
								for($i=0; $i<count($_POST['item_id']); $i++){

									$counter++;
									$item_id = mysqli_real_escape_string($conn,$_POST['item_id'][$i]);
									$quantity = mysqli_real_escape_string($conn,$_POST['quantity'][$i]);
									$price = mysqli_real_escape_string($conn,$_POST['price'][$i]);
									$amount = mysqli_real_escape_string($conn,$_POST['amount'][$i]);


									array_push($amount_array,$amount);


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
										array_push($price_array,0);
									}else{
										array_push($price_array,$quantity);
									}

									if($price==0 || $price == ""){
										$error++;
										$error_message .= '<li>Price cannot be empty at Index '.$counter.'</li>';
										array_push($qty_array,0);
									}else{
										array_push($qty_array,$price);
									}


								}
							}


							if($error >0){

								mysqli_query($conn,"START TRANSACTION");
								$query1 = "";
								$query2 = "";
								$query3 = "";
								$query4 = "";
								$query5 = "";
								$query6 = "";

								$job_name = mysqli_real_escape_string($conn,$_POST['job_name']);
								$customer_id = mysqli_real_escape_string($conn,$_POST['customer_id']);
								$job_description = mysqli_real_escape_string($conn,$_POST['job_description']);
								$order_rec_date = mysqli_real_escape_string($conn,$_POST['order_rec_date']);

								$total_amt = 0;
								for($i=0; $i<count($_POST['item_id']); $i++){
									$amount = mysqli_real_escape_string($conn,$_POST['amount'][$i]);
									$total_amt += $amount;
								}

								$query_21 = "SELECT * FROM job_order_details WHERE job_id = ".$order_id." AND delete_status = 0";
								$result_21 = mysqli_query($conn,$query_21);
								$query5_up = 1;
								while($data_21 = mysqli_fetch_array($result_21)){

									$quantity_previous = $data_21['quantity'];
									$item_id_previous = $data_21['item_id'];

									if($old_job_effect == 1){
										$queryUpInventory = "UPDATE customer_inventory SET qty_booked = qty_booked - ".$quantity_previous." WHERE plate_id = ".$item_id_previous." AND cust_id = ".$old_customer_id;
									}else{
										$queryUpInventory = "UPDATE inventory_item SET qty_booked = qty_booked - ".$quantity_previous." WHERE item_id = ".$item_id_previous;
									}
									$query5 = mysqli_query($conn,$queryUpInventory);
									if(!$query5){$query5_up = 2;}

								}


								$queryJbOrder = " UPDATE job_order set job_name = '".$job_name."' ";
								$queryJbOrder .= ", job_description = '".$job_description."' ";
								$queryJbOrder .= ", customer_id = '".$customer_id."' ";
								$queryJbOrder .= ", order_rec_date = '".$order_rec_date."' ";
								$queryJbOrder .= ", total_job_amount = '".$total_amt."' ";
								$queryJbOrder .= " WHERE jd_id = ".$order_id;

								$query1 = mysqli_query($conn,$queryJbOrder);

								$job_id = $order_id;

								if($_FILES['attachment']['name'] != ""){
									for ($i=0; $i < count($_FILES['attachment']['name']); $i++) { 

										$Img_name = mysqli_real_escape_string($conn,$_FILES['attachment']['name'][$i]);
										$Img_type = mysqli_real_escape_string($conn,$_FILES['attachment']['type'][$i]);
										$Img_size = mysqli_real_escape_string($conn,$_FILES['attachment']['size'][$i]);
										$Img_tmp_name = mysqli_real_escape_string($conn,$_FILES['attachment']['tmp_name'][$i]);

										if($Img_name != ""){
											$ImgFilePath = "job_Attachments/$job_id/".$Img_name;
											copy($Img_tmp_name,$ImgFilePath);
											$a = $job_id . '/' .$Img_name;
											$query_img = "INSERT INTO job_order_attachment (job_id,attachment) VALUES ('$job_id','$a')";

											$query2 = mysqli_query($conn,$query_img);
										}else{
											$query2 = 1;
										}

									}
								}else{
									$query2 = 1;
								}

								$del_query = "UPDATE job_order_details set delete_status = 1 WHERE job_id = ".$job_id;
								$query6 = mysqli_query($conn,$del_query);

								$query3_up = 1;
								$query4_up = 1;
								for($i=0; $i<count($_POST['item_id']); $i++){

									$item_id = mysqli_real_escape_string($conn,$_POST['item_id'][$i]);
									$quantity = mysqli_real_escape_string($conn,$_POST['quantity'][$i]);
									$price = mysqli_real_escape_string($conn,$_POST['price'][$i]);
									$amount = mysqli_real_escape_string($conn,$_POST['amount'][$i]);

									$queryJbDetails = " INSERT INTO job_order_details SET job_id = ".$job_id;
									$queryJbDetails .= ", item_id = ".$item_id;
									$queryJbDetails .= ", price = ".$price;
									$queryJbDetails .= ", quantity = ".$quantity;
									$queryJbDetails .= ", total_amount = ".$amount;

									$query3 = mysqli_query($conn,$queryJbDetails);
									if(!$query3){$query3_up = 2;}


									if($old_job_effect == 1){
										$queryUpInventory = "UPDATE customer_inventory SET qty_booked = qty_booked + ".$quantity." WHERE plate_id = ".$item_id." AND cust_id = ".$customer_id;
									}else{
										$queryUpInventory = "UPDATE inventory_item SET qty_booked = qty_booked + ".$quantity." WHERE item_id = ".$item_id;
									}
									$query4 = mysqli_query($conn,$queryUpInventory);
									if(!$query4){$query4_up = 2;}



								}

								if($query1 && $query2 && $query3_up && $query4_up && $query5_up && $query6){
									mysqli_query($conn,"COMMIT");
									?>
									<script>
										alert("Order successfully updated");
										window.open('index.php?page=Jobs/orders','_self');
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
						}
						?>

						<style>
							.professional-edit-job-page{max-width:1280px;margin:0 auto;padding:0 0 30px!important}.professional-edit-job-page>.col-lg-12{padding:0}.edit-job-card{overflow:hidden;border:1px solid #e5e6e9!important;border-radius:15px!important;background:#fff;box-shadow:0 10px 34px rgba(43,43,47,.07)!important}.edit-job-header{display:flex;align-items:center;justify-content:space-between;gap:18px;min-height:76px;padding:15px 19px!important;border:0!important;border-bottom:1px solid #ececef!important;border-left:4px solid #f36b21!important;color:#303033!important;background:#fff!important}.edit-job-title{display:flex;align-items:center;gap:12px}.edit-job-title-icon{display:grid;place-items:center;width:42px;height:42px;border-radius:11px;color:#fff;background:linear-gradient(145deg,#f36b21,#df5812);box-shadow:0 7px 16px rgba(243,107,33,.2)}.edit-job-title h2{margin:0;font-size:17px;font-weight:650}.edit-job-title p{margin:4px 0 0;font-size:10px;color:#898a90}.edit-job-code{padding:7px 11px;border:1px solid #ffd2ba;border-radius:8px;font-size:10px;font-weight:700;color:#d85411;background:#fff4ed}.edit-job-card>.card-body{padding:20px!important}.edit-job-section-title{display:flex;align-items:center;gap:8px;margin:0 0 15px;font-size:11px;font-weight:700;letter-spacing:.06em;color:#5f6065;text-transform:uppercase}.edit-job-section-title:before{content:'';width:4px;height:17px;border-radius:3px;background:#f36b21}.edit-job-card label{margin-bottom:6px;font-size:10px;font-weight:700;letter-spacing:.035em;color:#626369;text-transform:uppercase}.edit-job-card .form-control{min-height:42px;padding:8px 11px;border:1px solid #dfe1e5;border-radius:9px;font-size:11px;color:#3e3f44;background:#fbfbfc;box-shadow:none}.edit-job-card textarea.form-control{min-height:92px;resize:vertical}.edit-job-card .form-control:focus{border-color:#f36b21;box-shadow:0 0 0 3px rgba(243,107,33,.1)}.edit-job-card input[type=file]{padding:8px;background:#fff}.edit-job-meta{margin-bottom:7px}.edit-job-items-head{display:flex;align-items:center;justify-content:space-between;gap:15px;margin:6px 0 11px}.edit-job-items-head .edit-job-section-title{margin:0}.edit-add-row{display:inline-flex;align-items:center;gap:7px;min-height:36px;padding:7px 12px!important;border:0!important;border-radius:8px!important;font-size:10px;font-weight:600;color:#fff!important;background:#f36b21!important;box-shadow:0 7px 16px rgba(243,107,33,.16)}.edit-job-table-wrap{overflow-x:auto;border:1px solid #e4e5e8;border-radius:11px}.edit-job-table{width:100%;min-width:720px;margin:0!important;border:0!important;border-collapse:separate;border-spacing:0}.edit-job-table th{padding:11px 10px;border:0!important;border-bottom:1px solid #e3e4e7!important;font-size:9px;font-weight:700;letter-spacing:.06em;color:#73747a;text-transform:uppercase;background:#f5f5f6}.edit-job-table td{padding:9px 8px;border:0!important;border-bottom:1px solid #eeeeef!important;vertical-align:middle!important;background:#fff}.edit-job-table tr:last-child td{border-bottom:0!important}.edit-job-table th:nth-child(1){width:40%}.edit-job-table th:nth-child(2),.edit-job-table th:nth-child(3),.edit-job-table th:nth-child(4){width:17%}.edit-job-table th:last-child{width:9%;text-align:center}.edit-job-table .form-control{min-height:38px;height:38px}.edit-delete-row{display:inline-grid;place-items:center;width:32px;height:32px;border:1px solid #ffd0d0;border-radius:8px;color:#c84d4d;background:#fff2f2;cursor:pointer;transition:.15s}.edit-delete-row:hover{color:#fff;background:#d95757}.edit-job-total-bar{display:flex;align-items:center;justify-content:flex-end;gap:12px;margin-top:12px;padding:12px 14px;border:1px solid #ffd7c1;border-radius:9px;background:#fff6f0}.edit-job-total-bar span{font-size:10px;font-weight:700;color:#77787d;text-transform:uppercase}.edit-job-total-bar strong{font-size:18px;color:#df5913}.edit-job-footer{display:flex;align-items:center;justify-content:flex-end;gap:9px;padding:14px 19px!important;border:0!important;border-top:1px solid #ececef!important;background:#fafafa!important}.edit-job-footer .btn{display:inline-flex;align-items:center;gap:7px;min-height:39px;margin:0;padding:8px 15px;border-radius:9px;font-size:11px;font-weight:600}.edit-job-footer .btn-default{border:1px solid #dadce0;color:#5f6065;background:#fff}.edit-job-footer .btn-primary{border-color:#f36b21;background:#f36b21;box-shadow:0 7px 16px rgba(243,107,33,.18)}@media(max-width:767px){.edit-job-header{align-items:flex-start;flex-direction:column}.edit-job-card>.card-body{padding:15px!important}.edit-job-items-head{align-items:flex-start;flex-direction:column}.edit-add-row{width:100%;justify-content:center}.edit-job-footer .btn{flex:1;justify-content:center}}
						</style>
						<div class="row edit-job-meta">
							<!-- FORM Panel -->
							<div class="col-md-12">
								<form id="manage-job-order" method="POST" enctype="multipart/form-data">
									<div class="card edit-job-card">
										<div class="card-header edit-job-header">
											<div class="edit-job-title"><span class="edit-job-title-icon"><i class="fa fa-edit"></i></span><div><h2>Edit Job Order</h2><p>Update job information, plate quantities, pricing, and attachments.</p></div></div><span class="edit-job-code">JOB-<?php echo (int)$order_id ?></span>
										</div>
										<div class="card-body">
											<input type="hidden" name="jd_id">

											<div class="edit-job-section-title">Order Information</div><div class="row">
												<div class="col-md-4 form-group">
													<label class="control-label"><i class="fa fa-users"></i> Customer</label>
													<select  name="customer_id"  class="form-control" >
														<option value="">Select customer</option>
														<?php
														$query_supp = "SELECT * FROM customers WHERE cust_status = 0";
														$result_supp = mysqli_query($conn,$query_supp);
														while($data_supp = mysqli_fetch_array($result_supp)){
															$selected_val ="";
															if($data_supp['cust_id'] == $customer_id){
																$selected_val = "selected";
															}
															?>
															<option <?php echo $selected_val ?> value="<?php echo $data_supp['cust_id'] ?>"><?php echo $data_supp["cust_name"] ?></option>
															<?php
														}
														?>
													</select>
												</div>

												<div class="col-md-4 form-group">
													<label class="control-label"><i class="fa fa-briefcase"></i> Job Name</label>
													<input type="text" class="form-control" value="<?php echo htmlspecialchars($job_name, ENT_QUOTES, 'UTF-8') ?>" name="job_name" placeholder="Enter job name">
												</div>
												<div class="col-md-4 form-group">
													<label class="control-label"><i class="fa fa-calendar"></i> Order Received Date</label>
													<input type="date" class="form-control" value="<?php echo $order_rec_date ?>" name="order_rec_date">
												</div>
												<div class="col-md-8 form-group">
													<label class="control-label"><i class="fa fa-align-left"></i> Job Description</label>
													<textarea class="form-control" name="job_description" placeholder="Describe the job requirements"><?php echo htmlspecialchars($job_description, ENT_QUOTES, 'UTF-8') ?></textarea>
												</div>


												<div class="col-md-4 form-group">
													<label class="control-label"><i class="fa fa-paperclip"></i> Add Attachment</label>
													<input type="file" id="attachment" multiple="true" readonly="true" class="form-control"  name="attachment[]">
												</div>




												<div class="col-md-12"><div class="edit-job-items-head"><div class="edit-job-section-title">Plate & Pricing Details</div><button type="button" id="add_new_row" class="btn btn-primary edit-add-row"><i class="fa fa-plus"></i> Add Plate Row</button></div><div class="edit-job-table-wrap">
													<table class="table-bordered edit-job-table" id="job_item_table">
														<tr>
															<th>Plate</th>
															<th>Quantity</th>
															<th>Price</th>
															<th>Amount</th>
															<th>Action</th>
														</tr>

														<?php
														$job_details = $conn->query("SELECT a.*,b.item_name FROM job_order_details as a inner join inventory_item as b on a.item_id = b.item_id where a.delete_status = 0 AND a.job_id = ".$order_id);
														$counter = 0;
														while($row_job_details = $job_details->fetch_assoc()){
															$total_amount = $row_job_details['total_amount'];
															$price = $row_job_details['price'];
															$quantity = $row_job_details['quantity'];
															$item_id = $row_job_details['item_id'];
															$counter++;
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
																			if($item_id == $data_supp['item_id']){
																				$selected_item = "selected";
																			}
																			?>
																			<option <?php echo $selected_item ?> value="<?php echo $data_supp['item_id'] ?>"><?php echo $data_supp["item_name"] ?></option>
																			<?php
																		}
																		?>
																	</select>
																</td>
																<td>
																	<input type="number" value="<?php echo $quantity ?>" class="form-control quantity" name="quantity[]">
																</td>
																<td>
																	<input type="number" value="<?php echo $price ?>" class="form-control price" name="price[]">
																</td>
																<td>
																	<input type="text" value="<?php echo $total_amount ?>" readonly="true" class="form-control amount" name="amount[]">
																</td>
																<td class="text-center">
																	<?php 
																	if($counter != 1){
																		?>
																		<span class="del_row edit-delete-row" title="Remove row"><i class="fa fa-trash-alt"></i></span>
																		<?php
																	}else{
																		?>
																		<span>-</span>
																		<?php
																	}
																	?>

																</td>
															</tr>
														<?php } ?>

													</table></div><div class="edit-job-total-bar"><span>Estimated Total</span><strong>PKR <span id="edit_job_total">0.00</span></strong></div>
												</div>
											</div>


										</div>

										<div class="card-footer edit-job-footer">
											<button class="btn btn-default" type="button" onclick="$('#manage-job-order').get(0).reset()"><i class="fa fa-undo"></i> Reset</button>
											<button class="btn btn-primary" type="submit" name="update_job" id="update_job"><i class="fa fa-save"></i> Update Job</button>
										</div>
									</div>
								</form>
							</div>
							<!-- FORM Panel -->
						</div>
					</div>



				</div>
				<?php
			}else{
				?>
				<div class="container-fluid">

					<div class="col-lg-12">
						<div class="row">
							<div class="col-md-12">
								<div class="card">
									<div class="card-header">
										<h3 class="text-danger">Access Denied, Order is Marked as Completed!</h3>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<?php
			}
			?>



			<style>

				td{
					vertical-align: middle !important;
				}
				td p {
					margin:unset;
				}
			</style>

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
					function updateEditJobTotal(){
						var total = 0;
						$('#job_item_table .amount').each(function(){ total += parseFloat($(this).val()) || 0; });
						$('#edit_job_total').text(total.toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2}));
					}
					updateEditJobTotal();
					$(document).on("keyup",".price",function() {
						var qty = $(this).closest('tr').find('.quantity').val();
						var price = $(this).closest('tr').find('.price').val();
						var amt = 0;
						if(qty != "" && price != ""){
							amt = qty * price;
							$(this).closest('tr').find('.amount').val(amt);
						}
						updateEditJobTotal();
					})
					$(document).on("keyup",".quantity",function() {
						var qty = $(this).closest('tr').find('.quantity').val();
						var price = $(this).closest('tr').find('.price').val();
						var amt = 0;
						if(qty != "" && price != ""){
							amt = qty * price;
							$(this).closest('tr').find('.amount').val(amt);
						}
						updateEditJobTotal();
					})
					$(document).on("click",".del_row",function() {
						this.closest('tr').remove();
						updateEditJobTotal();
					});

					$('#add_new_row').click(function(event) {
						var new_row = '';
						new_row += '<tr>';
						new_row += '<td><?php echo $item_list ?></td>';
						new_row += '<td><input type="number" class="form-control quantity" name="quantity[]"></td>';
						new_row += '<td><input type="number" class="form-control price" name="price[]"></td>';
						new_row += '<td><input type="text" readonly="true" class="form-control amount" name="amount[]"></td>';
						new_row += '<td class="text-center"><span class="del_row edit-delete-row" title="Remove row"><i class="fa fa-trash-alt"></i></span></td>';
						new_row += '</tr>';

						$('#job_item_table').append(new_row);
						updateEditJobTotal();
					});
				})

				$('#manage-job-order').on('reset',function(){
					$('input:hidden').val('')
				})


			</script>

			<?php
		}else{
			include 'invalidLink.php';
		}
	}else{
		include 'invalidLink.php';
	}

}else{
	include 'accessDenied.php';
}
?>
