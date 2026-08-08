<?php include('db_connect.php');
include_once('functions.php');

if(in_array("28",$_SESSION['login_Permisions']))
{
	if(!function_exists('create_job_sales_voucher')){
		function create_job_sales_voucher($conn,$job_id,$created_by){
			$job_id = (int)$job_id;
			$created_by = (int)$created_by;
			if($job_id <= 0){
				return "Invalid job id for sales invoice.";
			}

			$existing = mysqli_query($conn,"SELECT id FROM vouchers WHERE ref_column = 'job_order' AND ref_id = ".$job_id." AND v_type_id = 3 LIMIT 1");
			if($existing && mysqli_num_rows($existing) > 0){
				return 1;
			}

			$order_qry = mysqli_query($conn,"SELECT a.jd_id,a.customer_id,a.order_rec_date,b.acc_id AS customer_account FROM job_order a INNER JOIN customers b ON a.customer_id = b.cust_id WHERE a.jd_id = ".$job_id." LIMIT 1");
			if(!$order_qry || mysqli_num_rows($order_qry) == 0){
				return "Job order not found for sales invoice.";
			}
			$order = mysqli_fetch_assoc($order_qry);
			$customer_account = resolve_account_no($order['customer_account'],$conn);
			if($customer_account <= 0){
				return "Customer account is not properly bound for this job.";
			}

			$company_id = 0;
			$company_qry = mysqli_query($conn,"SELECT company_id FROM accounts WHERE account_no = ".$customer_account." LIMIT 1");
			if($company_qry && mysqli_num_rows($company_qry) > 0){
				$company_row = mysqli_fetch_assoc($company_qry);
				$company_id = (int)$company_row['company_id'];
			}

			$sale_qry_sql = "SELECT account_no FROM accounts WHERE acc_name IN ('Sale Account','Sales') AND del_status = 0";
			if($company_id > 0){
				$sale_qry_sql .= " AND company_id = ".$company_id;
			}
			$sale_qry_sql .= " LIMIT 1";
			$sale_qry = mysqli_query($conn,$sale_qry_sql);
			if(!$sale_qry || mysqli_num_rows($sale_qry) == 0){
				$sale_qry = mysqli_query($conn,"SELECT account_no FROM accounts WHERE acc_name IN ('Sale Account','Sales') AND del_status = 0 LIMIT 1");
			}
			if(!$sale_qry || mysqli_num_rows($sale_qry) == 0){
				return "Sales account not found in chart of accounts.";
			}
			$sale_account = (int)mysqli_fetch_assoc($sale_qry)['account_no'];

			$amount_qry = mysqli_query($conn,"SELECT COALESCE(SUM(total_amount),0) AS job_total FROM job_order_details WHERE job_id = ".$job_id." AND delete_status = 0");
			$amount = $amount_qry ? (float)mysqli_fetch_assoc($amount_qry)['job_total'] : 0;
			if($amount <= 0){
				return "Job total amount is zero; sales invoice was not created.";
			}

			$voucher_qry = mysqli_query($conn,"SELECT MAX(voucher_no) AS voucher_no FROM vouchers WHERE v_type_id = 3");
			$voucher_no = 10000;
			if($voucher_qry && mysqli_num_rows($voucher_qry) > 0){
				$voucher_row = mysqli_fetch_assoc($voucher_qry);
				if($voucher_row['voucher_no'] != ""){
					$voucher_no = (int)$voucher_row['voucher_no'];
				}
			}
			$voucher_no++;
			$trans_date = date("Y-m-d");
			$narration = mysqli_real_escape_string($conn,"Sales invoice generated on job completion for JOB-".$job_id);

			$debit = mysqli_query($conn,"INSERT INTO vouchers SET voucher_no = ".$voucher_no.", v_type_id = 3, account_id = ".$customer_account.", trans_dated = '".$trans_date."', debit_amount = ".$amount.", credit_amount = 0, ref_column = 'job_order', ref_id = ".$job_id.", narration = '".$narration."', created_by = ".$created_by);
			$credit = mysqli_query($conn,"INSERT INTO vouchers SET voucher_no = ".$voucher_no.", v_type_id = 3, account_id = ".$sale_account.", trans_dated = '".$trans_date."', debit_amount = 0, credit_amount = ".$amount.", ref_column = 'job_order', ref_id = ".$job_id.", narration = '".$narration."', created_by = ".$created_by);

			return ($debit && $credit) ? 1 : "Sales invoice creation failed.";
		}
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

	function job_order_status_label($status)
	{
		if((int)$status === 3){
			return 'Plate Setting';
		}
		if((int)$status === 1){
			return 'On Machine';
		}
		if((int)$status === 4){
			return 'Plate Washing';
		}
		if((int)$status === 5){
			return 'Oven Baking';
		}
		if((int)$status === 2){
			return 'Completed';
		}
		return 'Pending';
	}

	function job_order_status_class($status)
	{
		if((int)$status === 0){
			return 'status-pending';
		}
		if((int)$status === 2){
			return 'status-done';
		}
		return 'status-running';
	}

	?>
	<style>
		input[type=checkbox]
		{			
			-ms-transform: scale(1.3); 
			-moz-transform: scale(1.3); 
			-webkit-transform: scale(1.3); 
			-o-transform: scale(1.3); 
			transform: scale(1.3);
			padding: 10px;
			cursor:pointer;
		}
		:root{
			--primary: #0b1324;
			--accent: #ff6a00;
			--bg: #f4f6fb;
			--muted: #6b7280;
		}

		body{
			background: var(--bg);
		}
		.card{
			border: none;
			border-radius: 16px;
			box-shadow: 0 6px 18px rgba(0,0,0,0.06);
		}
		.card-header{
			background: var(--primary);
			color: #fff;
			font-weight: 600;
			border-left: 5px solid var(--accent);
		}
		.pro-table{
			width: 100%;
			border-collapse: separate;
			border-spacing: 0 10px;
		}
		.pro-table thead th{
			background: #fff;
			color: var(--muted);
			font-size: 12px;
			text-transform: uppercase;
			border: none;
			padding: 12px;
		}
		.pro-table tbody tr{
			background: #fff;
			border-radius: 12px;
			box-shadow: 0 2px 10px rgba(0,0,0,0.04);
			transition: 0.2s;
		}
		.pro-table tbody tr:hover{
			transform: translateY(-2px);
			border-left: 4px solid var(--accent);
		}
		.pro-table td{
			padding: 12px;
			vertical-align: middle;
		}
		.code-badge{
			background: rgba(255,106,0,0.12);
			color: var(--accent);
			padding: 4px 10px;
			border-radius: 20px;
			font-size: 12px;
			font-weight: 600;
		}
		.status-pending{
			color: #f59e0b;
			font-weight: 600;
		}
		.status-running{
			color: #16a34a;
			font-weight: 600;
		}
		.btn-primary{
			background: var(--accent);
			border: none;
			border-radius: 10px;
			font-weight: 600;
		}

		.btn-primary:hover{
			background: #e85f00;
		}

		.action-btns{
			display: flex;
			gap: 6px;
		}

		.action-btns .btn{
			background: #f3f4f6;
			border-radius: 8px;
		}

		.action-btns .btn:hover{
			background: var(--accent);
			color: #fff;
		}
		.action-btns .btn.whatsapp-job-card{
			color: #128c7e;
			background: #e8f7f1;
		}
		.action-btns .btn.whatsapp-job-card:hover{
			color: #fff;
			background: #128c7e;
		}
	</style>

	<div class="container-fluid">
		<div class="col-lg-12">			
			<div class="row">
				<div class="container-fluid px-3 mt-3">
					<div class="card mb-3">
						<div class="card-header d-flex justify-content-between align-items-center">
							<div>
								<h5 class="mb-0">Job Orders</h5>
								<small class="color-gray">All job orders with current status</small>
							</div>

							<a href="index.php?page=Jobs/add-job-order" class="btn btn-primary btn-sm">
								<i class="fa fa-plus"></i> New Order
							</a>
						</div>
					</div>


					<div class="table-responsive">

						<?php
						if(isset($_POST['change_status'])){
							mysqli_query($conn,"START TRANSACTION");

							$job_id_TB_status = mysqli_real_escape_string($conn,$_POST['job_id_TB_status']);
							$job_status_TB = mysqli_real_escape_string($conn,$_POST['job_status_TB']);
							$previous_status = -1;
							$previous_status_qry = mysqli_query($conn,"SELECT order_status FROM job_order WHERE jd_id = ".$job_id_TB_status." LIMIT 1");
							if($previous_status_qry && mysqli_num_rows($previous_status_qry) > 0){
								$previous_status = (int)mysqli_fetch_assoc($previous_status_qry)['order_status'];
							}
							$is_new_completion = ((int)$job_status_TB === 2 && $previous_status !== 2);

							$query4_up = 1;
							$query5_up = 1;
							$quantity_avaiable = true;
							$qty_error = "";
							if($is_new_completion){
								$query_job_dt = "SELECT * FROM job_order_details WHERE job_id = ".$job_id_TB_status." AND delete_status = 0";
								$result_job_dt = mysqli_query($conn,$query_job_dt);
								while($data_job_dt = mysqli_fetch_array($result_job_dt)){

									$item_id = $data_job_dt['item_id'];
									$quantity = $data_job_dt['quantity'];
									$dated = date("Y-m-d");
									$query_job = "SELECT job_effect,customer_id FROM job_order WHERE jd_id = ".$job_id_TB_status;
									$result_job = mysqli_query($conn,$query_job);
									$data_job = mysqli_fetch_array($result_job);
									$job_effect = $data_job['job_effect'];
									$customer_id = $data_job['customer_id'];

									$inventory_Block = "";

									if($job_effect == 0){
										$query_inv = "SELECT * FROM inventory_item WHERE item_id = ".$item_id;
										$result_inv = mysqli_query($conn,$query_inv);
										while($data_inv = mysqli_fetch_array($result_inv)){
											$quantity_in_inv = $data_inv['quantity'];
											$item_name_in_inv = $data_inv['item_name'];
										}
										$inventory_Block = "ICON";
									}
									else
									{
										$query_inv = "SELECT a.*,b.item_name FROM customer_inventory as a INNER JOIN inventory_item as b on a.plate_id = b.item_id WHERE a.plate_id = ".$item_id." AND cust_id = ".$customer_id;
										$result_inv = mysqli_query($conn,$query_inv);
										while($data_inv = mysqli_fetch_array($result_inv)){
											$quantity_in_inv = $data_inv['quantity'];
											$item_name_in_inv = $data_inv['item_name'];
										}
										$inventory_Block = "CUSTOMER";
									}


									if($quantity_in_inv>=$quantity){

										if($job_effect == 0){
											$queryUpInventory = "UPDATE inventory_item SET qty_booked = qty_booked - ".$quantity.", quantity = quantity - ".$quantity." WHERE item_id = ".$item_id;
											$query4 = mysqli_query($conn,$queryUpInventory);

											$queryAudit = "INSERT INTO inventory_audit set item_id = ".$item_id;
											$queryAudit .= " , quantity = -".$quantity." ";
											$queryAudit .= " , remarks = 'Inventory Out' ";
											$queryAudit .= " , ref_column = 'JOB' ";
											$queryAudit .= " , ref_id = ".$job_id_TB_status;
											$queryAudit .= " , user_id = '".$_SESSION['login_id']."' ";
											$queryAudit .= " , dated = '".$dated."' ";

											$query5 = mysqli_query($conn,$queryAudit);
										}else{
											$queryUpInventory = "UPDATE customer_inventory SET qty_booked = qty_booked - ".$quantity.", quantity = quantity - ".$quantity." WHERE cust_id = ".$customer_id." AND plate_id = ".$item_id;
											$query4 = mysqli_query($conn,$queryUpInventory);

											$queryAudit = "INSERT INTO external_inv_audit set item_id = ".$item_id;
											$queryAudit .= " , quantity = -".$quantity." ";
											$queryAudit .= " , remarks = 'Inventory Out' ";
											$queryAudit .= " , ref_column = 'JOB' ";
											$queryAudit .= " , ref_id = ".$job_id_TB_status;
											$queryAudit .= " , user_id = '".$_SESSION['login_id']."' ";
											$queryAudit .= " , creation_date = '".$dated."' ";
											$queryAudit .= " , cust_id = '".$customer_id."' ";

											$query5 = mysqli_query($conn,$queryAudit);
										}
										if(!$query4){$query4_up = 2;}
										if(!$query5){$query5_up = 2;}
									}else{
										$quantity_avaiable = false;
										$qty_error .= '<li>Paper Item: '.$item_name_in_inv.' is short by '.$quantity - $quantity_in_inv.' Sheets in '.$inventory_Block.' inventory to complete this order. </li>';
									}
								}
							}	
							if($qty_error != ""){

								?>
								<div class="col-md-12" style="border:1px solid red">
									<div>
										<h3 style="color:red">ERRORS!</h3>
										<ul>
											<?php echo $qty_error; ?>
										</ul>
									</div>
								</div>
								<?php
							}else{

								$queryUpStatus = "UPDATE job_order SET order_status = ".$job_status_TB." WHERE jd_id = ".$job_id_TB_status;
								$resultUpStatus = mysqli_query($conn,$queryUpStatus);
								$sales_voucher = 1;
								if($is_new_completion){
									$sales_voucher = create_job_sales_voucher($conn,$job_id_TB_status,$_SESSION['login_id']);
								}

								if($resultUpStatus && $query4_up && $query5_up && $sales_voucher === 1){
									job_order_status_history_table($conn);
									$status_label = job_order_status_label($job_status_TB);
									$historyCheck = mysqli_query($conn,"SELECT id FROM job_order_status_history WHERE job_id = ".$job_id_TB_status." AND status = ".$job_status_TB." LIMIT 1");
									if(!$historyCheck || mysqli_num_rows($historyCheck) == 0){
										mysqli_query($conn,"INSERT INTO job_order_status_history SET job_id = ".$job_id_TB_status.", status = ".$job_status_TB.", status_label = '".$status_label."', created_at = NOW()");
									}
									mysqli_query($conn,"COMMIT");
									?>
									<script>
										alert("Order Status Successfully updated");
										window.open('index.php?page=Jobs/orders','_self');
									</script>
									<?php
								}else{

									mysqli_query($conn,"ROLLBACK");			
									?>
									<script>
										alert_toast("<?php echo is_string($sales_voucher) ? addslashes($sales_voucher) : 'Error'; ?>",'danger');
									</script>
									<?php
								}
							}


						}
						?>


						<table class="table pro-table mb-0">

							<thead>
								<tr>

									<th>Job</th>
									<th>Customer</th>
									<th>Received By</th>
									<th>Date</th>
									<th>Status</th>
									<th>Actions</th>
								</tr>
							</thead>

							<tbody>

								<?php 
								$order = $conn->query("SELECT a.*,b.cust_name, c.name as userName 
									FROM job_order as a 
									INNER JOIN customers as b on a.customer_id = b.cust_id 
									INNER JOIN users as c on a.order_rec_by = c.id 
									WHERE order_status != 2 AND del_status !=1
									ORDER BY a.jd_id DESC");

								while($row=$order->fetch_assoc()):
									?>
									<tr>									
										<td>
											<div><?php echo $row['job_name'] ?></div>
											<small class="text-muted">ORD: <?php echo $row['jd_id'] ?></small>
										</td>									
										<td><?php echo $row['cust_name'] ?></td>								
										<td><?php echo $row['userName'] ?></td>									
										<td>
											<small class="text-muted">
												<?php echo date("d M Y",strtotime($row['order_rec_date'])) ?>
											</small>
										</td>									
										<td>
											<span class="<?php echo job_order_status_class($row['order_status']); ?>"><?php echo job_order_status_label($row['order_status']); ?></span>
										</td>									
										<td>
											<div class="action-btns">
												<a class="btn btn-sm" href="index.php?page=Jobs/view-job-order&id=<?php echo $row['jd_id'] ?>"> <i class="fa fa-eye"></i>
												</a>

												<a class="btn btn-sm" href="index.php?page=Jobs/edit-order&id=<?php echo $row['jd_id'] ?>"> <i class="fa fa-edit"></i>
												</a>

												<button class="btn btn-sm status_btn" data-value="<?php echo $row['jd_id'] ?>^<?php echo $row['order_status'] ?>" data-code="JOB-<?php echo $row['jd_id'] ?>" data-toggle="modal" data-target="#status_modal" title="Change job status" aria-label="Change job status">
													<i class="fa fa-sync"></i>
												</button>

												<?php if(in_array("41",$_SESSION['login_Permisions'])){ ?>
													<a class="btn btn-sm" target="_blank" href="Jobs/job-card.php?ref=<?= $row['jd_id']?>"> <i class="fas fa-file-pdf"></i>
													</a>
													<button class="btn btn-sm whatsapp-job-card" type="button" data-id="<?= $row['jd_id']?>" title="Send job card on WhatsApp" aria-label="Send job card on WhatsApp">
														<i class="fab fa-whatsapp"></i>
													</button>
												<?php } ?>

											</div>
										</td>

									</tr>
								<?php endwhile; ?>

							</tbody>
						</table>
					</div>

				</div>
			</div>
		</div>
	</div>

	<style>
		#status_modal .modal-dialog{max-width:500px;margin:8vh auto}#status_modal .modal-content{overflow:hidden;border:0;border-radius:15px;box-shadow:0 24px 70px rgba(28,29,32,.24)}#status_modal .modal-header{display:flex!important;align-items:center;justify-content:space-between;min-height:68px;padding:15px 18px;border:0;border-bottom:1px solid #ececef;border-left:4px solid #f36b21;background:#fff!important}.job-status-heading{display:flex;align-items:center;gap:12px}.job-status-icon{display:grid;place-items:center;width:40px;height:40px;border-radius:11px;color:#fff;background:linear-gradient(145deg,#f36b21,#df5812)}.job-status-heading h4{margin:0;font-size:16px;font-weight:650;color:#303033}.job-status-heading p{margin:3px 0 0;font-size:10px;color:#898a90}.job-status-close{display:grid;place-items:center;width:32px;height:32px;padding:0;border:0;border-radius:8px;color:#6f7075;background:#f4f4f5}
		#status_modal .modal-body{padding:20px;background:#f7f7f8}.job-status-context{display:flex;justify-content:space-between;margin-bottom:16px;padding:11px 13px;border:1px solid #e4e5e8;border-radius:9px;background:#fff}.job-status-context span{font-size:10px;color:#85868c}.job-status-context strong{font-size:11px}.job-status-field label{display:block;margin-bottom:7px;font-size:10px;font-weight:700;color:#626369;text-transform:uppercase}.job-status-field select{height:44px!important;padding:8px 11px!important;border:1px solid #dfe1e5!important;border-radius:9px!important;font-size:11px}.job-status-field select:focus{border-color:#f36b21!important;box-shadow:0 0 0 3px rgba(243,107,33,.1)!important}
		#status_modal .modal-footer{display:flex;justify-content:flex-end;gap:8px;padding:13px 18px;border:0;border-top:1px solid #e7e8eb;background:#fff}.job-status-action{display:inline-flex;align-items:center;gap:7px;min-height:38px;margin:0!important;padding:8px 14px;border-radius:9px;font-size:11px;font-weight:600}.job-status-action.cancel{border:1px solid #dadce0;color:#5f6065;background:#fff}.job-status-action.save{border:1px solid #f36b21;color:#fff;background:#f36b21;box-shadow:0 7px 16px rgba(243,107,33,.18)}
	</style>


	<div class="modal fade" id="status_modal" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<form method="post">
					<div class="modal-header">
						<div class="job-status-heading"><span class="job-status-icon"><i class="fa fa-sync"></i></span><div><h4>Update Job Status</h4><p>Move this job to the appropriate production stage.</p></div></div>
						<button type="button" class="job-status-close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
					</div>
					<div class="modal-body">
						<div class="job-status-context"><span>Selected job</span><strong id="job_status_code">Job</strong></div><div class="job-status-field"><label for="job_status_TB"><i class="fa fa-tasks"></i> New Status</label>
							<select class="form-control" name="job_status_TB" id="job_status_TB" required>
								<option value="">Choose job status</option>
								<option value="0">Pending</option>
								<option value="3">Plate Setting</option>
								<option value="1">On Machine</option>
								<option value="4">Plate Washing</option>
								<option value="5">Oven Baking</option>
								<option value="2">Completed</option>							
							</select></div>
							<input type="hidden" name="job_id_TB_status" id="job_id_TB_status">
						</div>
						<div class="modal-footer">
							<button type="button" class="btn job-status-action cancel" data-dismiss="modal"><i class="fa fa-times"></i> Cancel</button>
							<button type="submit" class="btn job-status-action save" id="change_status" name="change_status"><i class="fa fa-check"></i> Update Status</button>
						</div>
					</form>
				</div>
			</div>
		</div>


	</div>
	<style>

		td{
			vertical-align: middle !important;
		}
		td p{
			margin: unset
		}
		img{
			max-width:100px;
			max-height: :150px;
		}
	</style>





	<script>
		$(document).ready(function(){
			$('table').dataTable( {
				order: [[0, 'desc']]
			});
		})
	// $('#new_order').click(function(){
	// 	uni_modal("New order ","billing/index.php","mid-large")

	// })




		$(document).on("click",".status_btn",function() {
			var data=$(this).attr('data-value'); 
			var ans=data.split('^');

			$("#job_id_TB_status").val(ans[0]);
			$("#job_status_TB").val(ans[1]);
			$("#job_status_code").text($(this).attr('data-code'));
		});


		$('.view_order').click(function(){
			uni_modal("Order  Details","view_order.php?id="+$(this).attr('data-id'),"mid-large")

		})
		$(document).on('click','.whatsapp-job-card',function(){
			var btn = $(this);
			var jobId = btn.attr('data-id');
			var oldHtml = btn.html();
			btn.prop('disabled',true).html('<i class="fa fa-spinner fa-spin"></i>');
			start_load();
			$.ajax({
				url:'Jobs/job-card-whatsapp-link.php',
				method:'POST',
				dataType:'json',
				data:{job_id:jobId},
				success:function(resp){
					if(resp && resp.status === 'success' && resp.url){
						window.open(resp.url,'_blank');
						alert_toast('WhatsApp message is ready. Please press Send in WhatsApp.','success');
					}else{
						alert_toast((resp && resp.message) ? resp.message : 'Unable to prepare WhatsApp message.','danger');
					}
				},
				error:function(){
					alert_toast('Unable to prepare WhatsApp message.','danger');
				},
				complete:function(){
					btn.prop('disabled',false).html(oldHtml);
					end_load();
				}
			});
		})
		$('.delete_order').click(function(){
			_conf("Are you sure to delete this order ?","delete_order",[$(this).attr('data-id')])
		})
		function delete_order($id){
			start_load()
			$.ajax({
				url:'ajax.php?action=delete_order',
				method:'POST',
				data:{id:$id},
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
