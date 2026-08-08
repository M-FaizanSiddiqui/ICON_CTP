<?php include('db_connect.php');
include 'functions.php';

if(in_array(19,$_SESSION['login_Permisions']))
{
	?>
	<style>
		.card-footer{margin-bottom: 26px;}
	</style>
	<div class="container-fluid professional-form-page">

		<div class="col-lg-12">
			<div class="row">
				<!-- FORM Panel -->
				<div class="col-md-12">

					<?php 
					if(isset($_POST['add_new_requisition'])){
						mysqli_query($conn,"START TRANSACTION");
						$error = 0;
						$error_message = "";

						$total_amount = 0;
						$required_date = mysqli_real_escape_string($conn,$_POST['required_date']);
						
						
						if(trim($required_date," ") == ""){
							$error++;
							$error_message .= '<li>Received Date cannot be empty</li>';
						}						
						
						if(count($_POST['item_id']) == 0){
							$error++;
							$error_message .= '<li>Atleast add 1 Paper Item in your order.</li>';
						}else{
							$counter = 0;
							$paper_ids_array = array();
							$qty_array = array();
							for($i=0; $i<count($_POST['item_id']); $i++){

								$counter++;
								$item_id = mysqli_real_escape_string($conn,$_POST['item_id'][$i]);
								$quantity = mysqli_real_escape_string($conn,$_POST['quantity'][$i]);
								

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

							$act_data = "";
							$queryJbOrder = " INSERT INTO paper_requisition set supp_id = '' ";
							$queryJbOrder .= ", required_date = '".$required_date."' ";
							$queryJbOrder .= ", user_id = '".$_SESSION['login_id']."' ";

							$query1 = mysqli_query($conn,$queryJbOrder);

							$requisition_id = $conn->insert_id;

							$act_data .= "Requisition No: REQ-".$requisition_id.", Required Date: ".$required_date;

							$query3_up = 1;
							for($i=0; $i<count($_POST['item_id']); $i++){

								$item_id = mysqli_real_escape_string($conn,$_POST['item_id'][$i]);
								$quantity = mysqli_real_escape_string($conn,$_POST['quantity'][$i]);

								$queryJbDetails = " INSERT INTO requisition_details SET req_id = ".$requisition_id;
								$queryJbDetails .= ", item_id = ".$item_id;
								$queryJbDetails .= ", qty = ".$quantity;

								$query3 = mysqli_query($conn,$queryJbDetails);
								if(!$query3){$query3_up = 2;}

								$query_item = "SELECT * FROM inventory_item WHERE item_id = ".$item_id;
								$result_item = mysqli_query($conn,$query_item);
								$data_item = mysqli_fetch_array($result_item);
								$item_name = $data_item['item_name'];

								$act_data .= " Paper Item: ".$item_id."- ".$item_name.", Quantity: ".$quantity;
							}

							$act_log = activityLog("New Requisition Added. Details are [".$act_data."] ",$_SESSION['login_id'],$conn);

							if($query1 && $query3_up && $act_log){
								mysqli_query($conn,"COMMIT");
								?>
								<script>
									alert("Requisition Successfully Added");

									window.open('index.php?page=Requisition/view-requisition-details&id=<?php echo $requisition_id ?>','_self');
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
						$required_date = "";

						$paper_ids_array=array(0);
						$qty_array=array(0);
					}
					?>

					<form id="manage-job-order" method="POST" enctype="multipart/form-data">
						<div class="card professional-form-card">
							<div class="card-header">
								<span class="form-title-icon"><i class="fa fa-clipboard-list"></i></span>
								<div class="form-title-copy"><h2>Add Requisition</h2><p>Choose required plates, quantities, and fulfillment date.</p></div>
							</div>
							<div class="card-body">
								<input type="hidden" name="jd_id">

								<div class="row">
									<div class="col-md-4 form-group">
										<label class="control-label"><b>Required Date:</b></label>
										<input type="date" class="form-control" value="<?php echo $required_date ?>" name="required_date" aria-label="Required date">
									</div>

								<div class="col-md-12">
									<button type="button" id="add_new_row" class="btn btn-primary mb-2"><i class="fa fa-plus"></i> Add Plate</button>
									<div class="table-responsive"><table class="table-bordered" id="job_item_table">
											<tr>
												<th>Plate</th>
												<th>Quantity</th>
												<th>Action</th>
											</tr>
											<?php 
											for($ii=0; $ii<count($paper_ids_array); $ii++){
												?>
												<tr>
													<td>
														<select  name="item_id[]" class="form-control" >
																	<option value="">Select plate</option>
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
																	<input type="number" value="<?php echo $qty_array[$ii] ?>" class="form-control quantity" name="quantity[]" placeholder="Enter quantity">
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

									</table></div>
									</div>


								</div>

								<div class="card-footer">
									<div class="row">
										<div class="col-md-12">
											<button class="btn btn-default" type="button" onclick="$('#manage-job-order').get(0).reset()"><i class="fa fa-undo"></i> Reset</button>
											<button class="btn btn-success" type="submit" name="add_new_requisition" id="add_new_requisition"><i class="fa fa-save"></i> Save Requisition</button>
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
			$item_list .= '<option value="">Select plate</option>';
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
					
					$(document).on("click",".del_row",function() {
						this.closest('tr').remove();
					});

					$('#add_new_row').click(function(event) {
						var new_row = '';
						new_row += '<tr>';
						new_row += '<td><?php echo $item_list ?></td>';
					new_row += '<td><input type="number" class="form-control quantity" name="quantity[]" placeholder="Enter quantity"></td>';
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
