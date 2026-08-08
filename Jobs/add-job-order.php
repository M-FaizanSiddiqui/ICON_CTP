<?php include('db_connect.php');
include 'functions.php';

if(in_array(30,$_SESSION['login_Permisions']))
{
	function job_order_amount_value($value)
	{
		$value = trim((string)$value);
		return is_numeric($value) ? $value : 0;
	}

	function job_order_status_history_table($conn)
	{
		$sql = "CREATE TABLE IF NOT EXISTS job_order_status_history (
			id INT AUTO_INCREMENT PRIMARY KEY,
			job_id INT NOT NULL,
			status INT NOT NULL,
			status_label VARCHAR(50) NOT NULL,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			INDEX(job_id),
			INDEX(status)
		)";
		mysqli_query($conn, $sql);
	}

	?>
	<style>
		.card-footer{margin-bottom: 26px;}
	</style>

	<style>
		:root{
			--primary: #0b1324;
			--accent: #ff6a00;
			--bg: #f4f6fb;
		}

		body{
			background: var(--bg);
		}

		.card{
			border-radius: 10px;
			border: none;
			box-shadow: 0 4px 12px rgba(0,0,0,0.05);
		}

		.card-header{
			background: var(--primary);
			color: #fff;
			padding: 12px 15px;
			border-left: 4px solid var(--accent);
		}

		.form-control{
			height: 36px;
			border-radius: 6px;
			font-size: 13px;
		}

		.simple-table{
			width: 100%;
			border-collapse: collapse;
			margin-top: 10px;
		}

		.simple-table th,
		.simple-table td{
			border: 1px solid #ddd;
			padding: 8px;
			font-size: 13px;
		}

		.simple-table th{
			background: #f1f1f1;
			text-align: center;
		}

		.btn-primary{
			background: var(--accent);
			border: none;
		}
	</style>
	<div class="container-fluid professional-form-page">

		<div class="col-lg-12">
			<div class="row">
				<!-- FORM Panel -->
				<div class="col-md-12">

					<?php 
					if(isset($_POST['Insert_job'])){
						mysqli_query($conn,"START TRANSACTION");
						$error = 0;
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

						// if(mysqli_real_escape_string($conn,$_FILES['attachment']['name'][0]) == ""){
						// 	$error++;
						// 	$error_message .= '<li>Attachment cannot be empty</li>';
						// }

						if(count($_POST['item_id']) == 0){
							$error++;
							$error_message .= '<li>Atleast add 1 Paper Item in your order.</li>';
						}else{
							$counter = 0;
							$amount_array = array();
							$paper_ids_array = array();
							$rate_array = array();
							$qty_array = array();
							$exposing_amt_array = array();
							$imposing_amt_array = array();
							$ovenbake_amt_array = array();



							for($i=0; $i<count($_POST['item_id']); $i++){

								$counter++;
								$item_id = mysqli_real_escape_string($conn,$_POST['item_id'][$i]);
								$quantity = mysqli_real_escape_string($conn,$_POST['quantity'][$i]);
								$rate = mysqli_real_escape_string($conn,$_POST['rate'][$i]);
								$amount = mysqli_real_escape_string($conn,$_POST['amount'][$i]);

								$exposing_amt = mysqli_real_escape_string($conn,$_POST['exposing_amt'][$i]);
								$imposing_amt = mysqli_real_escape_string($conn,$_POST['imposing_amt'][$i]);
								$ovenbake_amt = isset($_POST['ovenBake_amt'][$i]) ? mysqli_real_escape_string($conn,$_POST['ovenBake_amt'][$i]) : 0;


								array_push($amount_array,$amount);
								array_push($exposing_amt_array,$exposing_amt);
								array_push($imposing_amt_array,$imposing_amt);
								array_push($ovenbake_amt_array,$ovenbake_amt);


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
									$error_message .= '<li>rate cannot be empty at Index '.$counter.'</li>';
									array_push($rate_array,0);
								}else{
									array_push($rate_array,$rate);
								}


							}
						}


						if($error >0){
							?>
							<div class="col-md-12" style="border:1px solid red; margin-top: 20px;">
								<div>
									<h3 style="color:red">ERRORS!</h3>
									<ul>
										<?php echo $error_message; ?>
									</ul>
								</div>
							</div>
							<?php
						}else{




							$total_amt = 0;
							$data_act = "";
							for($i=0; $i<count($_POST['item_id']); $i++){
								$amount = mysqli_real_escape_string($conn,$_POST['amount'][$i]);
								$total_amt += $amount;
							}

							$queryJbOrder = " INSERT INTO job_order set job_name = '".$job_name."' ";
							$queryJbOrder .= ", job_description = '".$job_description."' ";
							$queryJbOrder .= ", customer_id = '".$customer_id."' ";
							$queryJbOrder .= ", order_rec_date = '".$order_rec_date."' ";
							if(isset($_POST['imposing_up_charges'])){
								$queryJbOrder .= ", imposing_up_charges = '1' ";
							}

							if(isset($_POST['effect_cust_inv'])){
								$queryJbOrder .= ", job_effect = 1 ";
							}
							$queryJbOrder .= ", order_rec_by = '".$_SESSION['login_id']."' ";
							$queryJbOrder .= ", total_job_amount = '".$total_amt."' ";

							$query_cust = "SELECT * FROM customers WHERE cust_id = ".$customer_id;
							$result_cust = mysqli_query($conn,$query_cust);
							$data_cust = mysqli_fetch_array($result_cust);
							$cust_name = $data_cust['cust_name'];

							$data_act .= "Job Name: ".$job_name.", Job Description: ".$job_description.", Customer: CUST-".$customer_id."-".$cust_name.", Order Rec Date: ".$order_rec_date.", Total Amount of Order: ".$total_amt;

							$query1 = mysqli_query($conn,$queryJbOrder);

							$job_id = $conn->insert_id;
							if($query1){
								job_order_status_history_table($conn);
								mysqli_query($conn,"INSERT INTO job_order_status_history SET job_id = ".$job_id.", status = 0, status_label = 'Pending', created_at = NOW()");
							}

							// $dir='job_Attachments/'.$job_id;
							// mkdir($dir);

							// for ($i=0; $i < count($_FILES['attachment']['name']); $i++) { 

							// 	$Img_name = mysqli_real_escape_string($conn,$_FILES['attachment']['name'][$i]);
							// 	$Img_type = mysqli_real_escape_string($conn,$_FILES['attachment']['type'][$i]);
							// 	$Img_size = mysqli_real_escape_string($conn,$_FILES['attachment']['size'][$i]);
							// 	$Img_tmp_name = mysqli_real_escape_string($conn,$_FILES['attachment']['tmp_name'][$i]);


							// 	$ImgFilePath = "job_Attachments/$job_id/".$Img_name;
							// 	copy($Img_tmp_name,$ImgFilePath);
							// 	$a = $job_id . '/' .$Img_name;
							// 	$query_img = "INSERT INTO job_order_attachment (job_id,attachment) VALUES ('$job_id','$a')";

							// 	$query2 = mysqli_query($conn,$query_img);
							// }


							$query3_up = 1;
							$query4_up = 1;
							$query5_up = 1;
							$query6_up = 1;
							for($i=0; $i<count($_POST['item_id']); $i++){

								$item_id = mysqli_real_escape_string($conn,$_POST['item_id'][$i]);
								$quantity = mysqli_real_escape_string($conn,$_POST['quantity'][$i]);
								$rate = mysqli_real_escape_string($conn,$_POST['rate'][$i]);
								$amount = mysqli_real_escape_string($conn,$_POST['amount'][$i]);
								$exposing_amt = mysqli_real_escape_string($conn,$_POST['exposing_amt'][$i]);
								$imposing_amt = mysqli_real_escape_string($conn,$_POST['imposing_amt'][$i]);
								$OvenBake_Charges = isset($_POST['ovenBake_amt'][$i]) ? mysqli_real_escape_string($conn,$_POST['ovenBake_amt'][$i]) : 0;

								$item_id = job_order_amount_value($item_id);
								$quantity = job_order_amount_value($quantity);
								$rate = job_order_amount_value($rate);
								$amount = job_order_amount_value($amount);
								$exposing_amt = job_order_amount_value($exposing_amt);
								$imposing_amt = job_order_amount_value($imposing_amt);
								$OvenBake_Charges = job_order_amount_value($OvenBake_Charges);


								$queryJbDetails = " INSERT INTO job_order_details SET job_id = ".$job_id;
								$queryJbDetails .= ", item_id = ".$item_id;
								$queryJbDetails .= ", price = ".$rate;
								$queryJbDetails .= ", quantity = ".$quantity;
								$queryJbDetails .= ", total_amount = ".$amount;
								$queryJbDetails .= ", exposing_amt = ".$exposing_amt;
								$queryJbDetails .= ", imposing_amt = ".$imposing_amt;
								$queryJbDetails .= ", OvenBake_Charges = ".$OvenBake_Charges;

								$query3 = mysqli_query($conn,$queryJbDetails);
								if(!$query3){$query3_up = 2;}

								if(isset($_POST['effect_cust_inv'])){
									$queryUpInventory = "UPDATE customer_inventory SET qty_booked = qty_booked + ".$quantity." WHERE plate_id = ".$item_id." AND cust_id = ".$customer_id;
									$query4 = mysqli_query($conn,$queryUpInventory);
									if(!$query4){$query4_up = 2;}
								}else{
									$queryUpInventory = "UPDATE inventory_item SET qty_booked = qty_booked + ".$quantity." WHERE item_id = ".$item_id;
									$query4 = mysqli_query($conn,$queryUpInventory);
									if(!$query4){$query4_up = 2;}


									// calculation work
								// 	$qty_for_calc = $quantity;
								// 	while($qty_for_calc>0){
								// 		$query212 = "SELECT * FROM plate_rate_calculations WHERE prc_plate_id = ".$item_id." AND del_status = 0 AND prc_qty >0 ORDER by prc_id ASC LIMIT 1";
								// 		$result212 = mysqli_query($conn,$query212);
								// 		if(mysqli_num_rows($result212)>0){
								// 		    $data212 = mysqli_fetch_array($result212);
    				// 						$prc_up_id = $data212['prc_id'];
    				// 						$prc_plate_rate = $data212['prc_plate_rate'];
    				// 						$prc_qty_old = $data212['prc_qty'];
    				// 						$total_stock_amt_old = $data212['total_stock_amt'];

    				// 						if($prc_qty_old > $qty_for_calc){
    				// 							$qty_to_update = $prc_qty_old - $qty_for_calc;
    				// 							$tot_price_to_update = $qty_to_update * $prc_plate_rate;
    				// 							$qty_for_calc = 0;
    				// 						}
    				// 						else if($prc_qty_old < $qty_for_calc){
    				// 							$qty_to_update = 0;
    				// 							$tot_price_to_update = 0;
    				// 							$qty_for_calc = $qty_for_calc - $prc_qty_old;
    				// 						}

    				// 						$queryUp212 = "UPDATE plate_rate_calculations SET prc_qty = ".$qty_to_update.", total_stock_amt = ".$tot_price_to_update." WHERE prc_plate_id = ".$item_id." AND prc_id = ".$prc_up_id;
    				// 						$query5 = mysqli_query($conn,$queryUp212);
    				// 						if(!$query5){$query5_up = 2;}    
								// 		}


								// 	}


								// 	// rate calculation inventory update
								// 	$rate_calc_query = "SELECT * FROM plate_rate_calculations WHERE prc_plate_id = ".$item_id." AND prc_qty != 0 ";
								// 	$query_212 = mysqli_query( $conn,$rate_calc_query);
								// 	$t_qty = 0;
								// 	$t_amount = 0;
								// 	while($data_212 = mysqli_fetch_array($query_212)){
								// 		$prc_inv_rec_id = $data_212['prc_inv_rec_id'];
								// 		$prc_plate_id = $data_212['prc_plate_id'];
								// 		$prc_plate_rate = $data_212['prc_plate_rate'];
								// 		$prc_qty = $data_212['prc_qty'];
								// 		$total_stock_amt = $data_212['total_stock_amt'];

								// 		$t_amount += $total_stock_amt;
								// 		$t_qty += $prc_qty;
								// 	}

        //                             if($t_amount != 0 && $t_qty != 0){
        //                                 $price_to_be = number_format($t_amount/$t_qty,2);
        //                             }else{
        //                                 $price_to_be = 0;
        //                             }



								// 	$save6 = $conn->query("UPDATE inventory_item set avg_rate = '".$price_to_be."' WHERE item_id = ".$item_id);
								// 	if(!$save6){$query6_up = 2;}

								}

							}



							$act_log = activityLog("New Job Order has been Placed. Order No JB-".$job_id.", Details are [".$data_act."] ",$_SESSION['login_id'],$conn);

						// $query2
							$query2 = 0;
							if($query1  && $query3_up && $query4_up && $query5_up && $query6_up && $act_log){
								mysqli_query($conn,"COMMIT");
								?>
								<script>
									alert("Order successfully added");
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
					}else{
						$job_name = "";
						$customer_id = "";
						$job_description = "";
						$order_rec_date = "";

						$paper_ids_array=array(0);
						$rate_array=array(0);
						$qty_array=array(0);
						$amount_array=array(0);
						$exposing_amt_array=array(0);
						$imposing_amt_array=array(0);
						$ovenbake_amt_array=array(0);
					}
					?>

					<div class="container-fluid p-0">

						<form id="manage-job-order" method="POST" enctype="multipart/form-data">

							<div class="card professional-form-card">

								<div class="card-header">
									<span class="form-title-icon"><i class="fa fa-briefcase"></i></span>
									<div class="form-title-copy"><h2>Add New Job</h2><p>Create a customer job, configure plate charges, and calculate totals.</p></div>
								</div>

								<div class="card-body">
									<input type="hidden" name="jd_id">
									<div class="row">

										<!-- Customer -->
										<div class="col-md-4 mb-3">
											<label>Customer</label>
											<select name="customer_id" class="form-control">
												<option value="">Select customer</option>
												<?php
												$result = mysqli_query($conn,"SELECT * FROM customers WHERE cust_status=0");
												while($r=mysqli_fetch_array($result)){
													$selected_val = "";
													if($customer_id ==  $r['cust_id']){
														$selected_val = "Selected";
													}
													?>
													<option <?= $selected_val ?> value="<?= $r['cust_id'] ?>"><?= $r['cust_name'] ?></option>
												<?php } ?>
											</select>
										</div>

										<!-- Job -->
										<div class="col-md-4 mb-3">
											<label>Job Name</label>
											<input type="text" value="<?= $job_name ?>" name="job_name" class="form-control" placeholder="Enter job name">
										</div>

										<!-- Date -->
										<div class="col-md-4 mb-3">
											<label>Date</label>
											<input type="date" value="<?= $order_rec_date ?>" name="order_rec_date" class="form-control">
										</div>

										<!-- Description -->
										<div class="col-md-6 mb-3">
											<label>Description</label>
											<textarea name="job_description" class="form-control" placeholder="Describe the job requirements"><?=trim($job_description)?></textarea>
										</div>

										<!-- Options -->
									<div class="col-md-6 mb-3 form-option-panel">
										<label>Options</label><br>
										<label class="form-check-line"><input type="checkbox" id="effect_cust_inv" name="effect_cust_inv" readonly="true"> Effect Inventory</label>
										<label class="form-check-line"><input type="checkbox" id="imposing_up_charges" name="imposing_up_charges" readonly="true"> Imposing Charges</label>
										<label class="form-check-line"><input type="checkbox" id="oven_bake_charges" name="oven_bake_charges" readonly="true"> Oven Bake</label>
										</div>

									</div>

									<!-- SIMPLE TABLE -->
									<div class="table-responsive">
										<table class="simple-table">

											<thead>
												<tr>
													<th>Plate</th>
													<th>Qty</th>
													<th>Rate</th>
													<th>Exposing</th>
													<th>Imposing</th>
													<th>Oven Bake</th>
													<th>Amount</th>
												</tr>
											</thead>

											<tbody>

												<?php 
												for($ii=0; $ii<count($paper_ids_array); $ii++){
													?>
													<tr>

														<td>
															<select name="item_id[]" class="form-control item_id">
																<option value="">Select</option>
																<?php
																$items = mysqli_query($conn,"SELECT * FROM inventory_item WHERE status=0");
																while($i=mysqli_fetch_array($items)){

																	?>
																	<option <?= ($paper_ids_array[$ii]==$i['item_id'])?'selected':'' ?>
																	value="<?= $i['item_id'] ?>">
																	<?= $i['item_name'] ?>
																</option>
															<?php } ?>
														</select>
													</td>

													<td><input type="number" class="form-control quantity" name="quantity[]" value="<?= $qty_array[$ii] ?>"></td>
													<td><input type="number" class="form-control rate" name="rate[]" value="<?= $rate_array[$ii] ?>"></td>
													<td><input type="number" class="form-control exposing_amt" name="exposing_amt[]" value="<?= $exposing_amt_array[$ii] ?>"></td>
													<td><input type="number" class="form-control imposing_amt" name="imposing_amt[]" value="<?= $imposing_amt_array[$ii] ?>"><input type="hidden"  value="0" class="form-control imposing_amt_hidden"></td>
													<td><input type="number" class="form-control ovenBake_amt" name="ovenBake_amt[]" value="<?= $ovenbake_amt_array[$ii] ?>"><input type="hidden"  value="0" class="form-control ovenbake_amt_hidden"></td>
													<td><input type="text" class="form-control amount" name="amount[]" value="<?= $amount_array[$ii] ?>"></td>

												</tr>
											<?php } ?>

										</tbody>
									</table>
								</div>

							</div>

							<!-- FOOTER -->
							<div class="card-footer text-right">
								<button class="btn btn-default" type="button" onclick="$('#manage-job-order').get(0).reset()"><i class="fa fa-undo"></i> Reset</button>
								<button class="btn btn-primary" type="submit" name="Insert_job" id="Insert_job"><i class="fa fa-save"></i> Save Job</button>
							</div>

						</div>

					</form>
				</div>










			</div>
		</div>
	</div>

	<?php
	$item_list = '<select  name="item_id[]" class="form-control item_id">';
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


			$(document).on("keyup",".exposing_amt",function() {
						// if (!$('#effect_cust_inv').is(":checked"))
						// {
				var exposing_amt = $(this).closest('tr').find('.exposing_amt').val();
				var imposing_amt = $(this).closest('tr').find('.imposing_amt').val();
				var ovenBake_amt = $(this).closest('tr').find('.ovenBake_amt').val();

				var rate = $(this).closest('tr').find('.rate').val();
				var quantity = $(this).closest('tr').find('.quantity').val();

				if(exposing_amt == ''){
					exposing_amt = 0;
				}
				if(imposing_amt == ''){
					imposing_amt = 0;
				}
				if(ovenBake_amt == ''){
					ovenBake_amt = 0;
				}
				if(rate == ''){
					rate = 0;
				}
				if(quantity == ''){
					quantity = 0;
				}

				var tot_rate = parseFloat(rate)+parseFloat(exposing_amt)+parseFloat(imposing_amt) +parseFloat(ovenBake_amt);
				var tot_amt = parseFloat(tot_rate) * parseFloat(quantity);

				$(this).closest('tr').find('.amount').val(tot_amt);	
						// }
			});


			$(document).on("keyup",".imposing_amt",function() {
						// if (!$('#effect_cust_inv').is(":checked"))
						// {
				var exposing_amt = $(this).closest('tr').find('.exposing_amt').val();
				var imposing_amt = $(this).closest('tr').find('.imposing_amt').val();
				var ovenBake_amt = $(this).closest('tr').find('.ovenBake_amt').val();

				var rate = $(this).closest('tr').find('.rate').val();
				var quantity = $(this).closest('tr').find('.quantity').val();


				if(exposing_amt == ''){
					exposing_amt = 0;
				}
				if(imposing_amt == ''){
					imposing_amt = 0;
				}
				if(rate == ''){
					rate = 0;
				}
				if(quantity == ''){
					quantity = 0;
				}
				if(ovenBake_amt == ''){
					ovenBake_amt = 0;
				}

				var tot_rate = parseFloat(rate)+parseFloat(exposing_amt)+parseFloat(imposing_amt)  +parseFloat(ovenBake_amt);
				var tot_amt = parseFloat(tot_rate) * parseFloat(quantity);

				$(this).closest('tr').find('.amount').val(tot_amt);	
						// }
			});




			$(document).on("change","#effect_cust_inv",function() {
				$('.item_id').val('');
				$('.quantity').val('0');
				$('.rate').val('0');
				$('.exposing_amt').val('0');
				$('.imposing_amt').val('0');
				$('.amount').val('0');
			});



			$(document).on("change","#oven_bake_charges",function() {

				var quantity = $('.quantity').val();
				var rate = $('.rate').val();
				var exposing_amt = $('.exposing_amt').val();
				var imposing_amt = $('.imposing_amt').val();
				var ovenBake_amt = $('.ovenBake_amt').val();

				var amount = $('.amount').val();


				if (!$('#imposing_up_charges').is(":checked"))
				{
					imposing_amt=0;
				}

				if ($('#oven_bake_charges').is(":checked"))
				{
					var ovenbake_amt_hidden = $('.ovenbake_amt_hidden').val();
					if(ovenbake_amt_hidden == ''){
						ovenbake_amt_hidden = 0;
					}
					$('.ovenBake_amt').val(ovenbake_amt_hidden);

					var total_rate = parseFloat(rate) + parseFloat(exposing_amt) + parseFloat(imposing_amt) + parseFloat(ovenbake_amt_hidden);
					var tot_amt = parseFloat(quantity) * parseFloat(total_rate);
				}else{
					var total_rate = parseFloat(rate) + parseFloat(exposing_amt) + parseFloat(imposing_amt);
					var tot_amt = parseFloat(quantity) * parseFloat(total_rate);
					$('.ovenBake_amt').val(0);
				}
				if(rate!= 0){
					$('.amount').val(tot_amt);	
				}



			});

					//




			$(document).on("change","#imposing_up_charges",function() {

				var quantity = $('.quantity').val();
				var rate = $('.rate').val();
				var exposing_amt = $('.exposing_amt').val();
				var imposing_amt = $('.imposing_amt').val();
				var ovenBake_amt = $('.ovenBake_amt').val();

				if (!$('#oven_bake_charges').is(":checked"))
				{
					ovenBake_amt=0;
				}

				var amount = $('.amount').val();
				if ($('#imposing_up_charges').is(":checked"))
				{
					var imposing_amt_hidden = $('.imposing_amt_hidden').val();
					if(imposing_amt_hidden == ''){
						imposing_amt_hidden = 0;
					}
					$('.imposing_amt').val(imposing_amt_hidden);

					var total_rate = parseFloat(rate) + parseFloat(exposing_amt) + parseFloat(imposing_amt_hidden) + parseFloat(ovenBake_amt);
					var tot_amt = parseFloat(quantity) * parseFloat(total_rate);
				}else{
					var total_rate = parseFloat(rate) + parseFloat(exposing_amt) + parseFloat(ovenBake_amt);
					var tot_amt = parseFloat(quantity) * parseFloat(total_rate);
					$('.imposing_amt').val(0);
				}

				if(rate!= 0){
					$('.amount').val(tot_amt);
				}

			});



			$(document).on("change",".item_id",function() {
				var item_id = $(this).closest('tr').find('.item_id').val();

				var quantity = $(this).closest('tr').find('.quantity').val();
				var imp = 0;
				if ($('#imposing_up_charges').is(":checked"))
				{
					var imp = 1;
				}

				var ovenBake = 0;
				if ($('#oven_bake_charges').is(":checked"))
				{
					var ovenBake = 1;
				}
						// alert(imp);

				var this_row = $(this).closest('tr');

				$.ajax({
					type: "POST",
					url : "ajax-req/ajax_request.php",
					data: {item_id: item_id, req_no:11}, 
					success: function(response){

						var ans = response.split("^^");
						this_row.find('.rate').val(ans[0]);
						this_row.find('.exposing_amt').val(ans[1]);

						this_row.find('.imposing_amt_hidden').val(ans[2]);

						this_row.find('.ovenbake_amt_hidden').val(ans[3]);


						if(imp==1){
							this_row.find('.imposing_amt').val(ans[2]);
						}
						if(ovenBake==1){
							this_row.find('.ovenBake_amt').val(ans[3]);
						}




						var amt = 0;
						if(parseFloat(quantity)>0){
							total_rate = parseFloat(ans[0]) + parseFloat(ans[1]);
							if(imp==1){
								total_rate = parseFloat(total_rate) + parseFloat(ans[2]);
							}
							if(ovenBake==1){
								total_rate = parseFloat(total_rate) + parseFloat(ans[3]);
							}
							amt = parseFloat(total_rate) * parseFloat(quantity);
						}
						this_row.find('.amount').val(amt);
					}
				});
			})




			$(document).on("keyup",".rate",function() {
				var qty = $(this).closest('tr').find('.quantity').val();
				var rate = $(this).closest('tr').find('.rate').val();
				var amt = 0;

				var exposing_amt = $(this).closest('tr').find('.exposing_amt').val();
				var imposing_amt = $(this).closest('tr').find('.imposing_amt').val();
				var ovenBake_amt = $(this).closest('tr').find('.ovenBake_amt').val();
				var total_rate = parseFloat(rate) + parseFloat(exposing_amt);


				var imp = 0;
				if ($('#imposing_up_charges').is(":checked"))
				{
					var imp = 1;
				}

				var isBake = 0;
				if ($('#oven_bake_charges').is(":checked"))
				{
					isBake = 1;
				}

				if(imp==1){
					total_rate = parseFloat(total_rate) + parseFloat(imposing_amt);
				}

				if(isBake==1){
					total_rate = parseFloat(total_rate) + parseFloat(ovenBake_amt);
				}

				amt = qty * total_rate;

				$(this).closest('tr').find('.amount').val(amt);	


			});



			$(document).on("keyup",".quantity",function() {
				var qty = $(this).closest('tr').find('.quantity').val();
				var rate = $(this).closest('tr').find('.rate').val();
				var amt = 0;

				var imp = 0;
				if ($('#imposing_up_charges').is(":checked"))
				{
					var imp = 1;
				}

				var ovenBake = 0;
				if ($('#oven_bake_charges').is(":checked"))
				{
					var ovenBake = 1;
				}

				if(qty != "" && rate != ""){

					var exposing_amt = $(this).closest('tr').find('.exposing_amt').val();
					var imposing_amt = $(this).closest('tr').find('.imposing_amt').val();
					var ovenBake_amt = $(this).closest('tr').find('.ovenBake_amt').val();
					var total_rate = parseFloat(rate) + parseFloat(exposing_amt);

					if(imp==1){
						total_rate = parseFloat(total_rate) + parseFloat(imposing_amt);
					}
					if(ovenBake==1){
						total_rate = parseFloat(total_rate) + parseFloat(ovenBake_amt);
					}



					amt = qty * total_rate;

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
				new_row += '<td><input type="number" class="form-control exposing_amt" name="exposing_amt[]" value="0"></td>';
				new_row += '<td><input type="number" class="form-control imposing_amt" name="imposing_amt[]" value="0"><input type="hidden" value="0" class="form-control imposing_amt_hidden"></td>';
				new_row += '<td><input type="number" class="form-control ovenBake_amt" name="ovenBake_amt[]" value="0"><input type="hidden" value="0" class="form-control ovenbake_amt_hidden"></td>';
				new_row += '<td><input type="text" readonly="true" class="form-control amount" name="amount[]"></td>';
				new_row += '<td class="text-center"><span class="del_row" style="color:red;cursor: pointer"><i class="fa fa-trash-alt"></i></span></td>';
				new_row += '</tr>';

				$('#job_item_table').append(new_row);
			});
		})

// $('table').dataTable()
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

<style>
	/* Chrome, Safari, Edge, Opera */
	input::-webkit-outer-spin-button,
	input::-webkit-inner-spin-button {
		-webkit-appearance: none;
		margin: 0;
	}

	/* Firefox */
	input[type=number] {
		-moz-appearance: textfield;
	}
</style>
