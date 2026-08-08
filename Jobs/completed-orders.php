<?php include('db_connect.php');
include_once('functions.php');

if(in_array("29",$_SESSION['login_Permisions']))
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

	$completed_total = 0;
	$completed_amount = 0;
	$completed_today = 0;
	$completed_month = 0;
	$summary_qry = mysqli_query($conn,"SELECT COUNT(*) AS total_orders, COALESCE(SUM(total_job_amount),0) AS total_amount FROM job_order WHERE order_status = 2 AND del_status = 0");
	if($summary_qry && mysqli_num_rows($summary_qry) > 0){
		$summary_row = mysqli_fetch_assoc($summary_qry);
		$completed_total = (int)$summary_row['total_orders'];
		$completed_amount = (float)$summary_row['total_amount'];
	}
	$today_qry = mysqli_query($conn,"SELECT COUNT(*) AS total_orders FROM job_order WHERE order_status = 2 AND del_status = 0 AND order_rec_date = CURDATE()");
	if($today_qry && mysqli_num_rows($today_qry) > 0){
		$completed_today = (int)mysqli_fetch_assoc($today_qry)['total_orders'];
	}
	$month_qry = mysqli_query($conn,"SELECT COUNT(*) AS total_orders FROM job_order WHERE order_status = 2 AND del_status = 0 AND order_rec_date BETWEEN DATE_FORMAT(CURDATE(),'%Y-%m-01') AND CURDATE()");
	if($month_qry && mysqli_num_rows($month_qry) > 0){
		$completed_month = (int)mysqli_fetch_assoc($month_qry)['total_orders'];
	}

	?>
	<style>
		.completed-orders-page{padding:0 0 18px}
		.completed-orders-shell{max-width:100%;margin:0 auto}
		.completed-orders-hero{position:relative;overflow:hidden;margin-bottom:10px;padding:12px 16px;border-radius:14px;background:linear-gradient(135deg,#1d2029 0%,#282b35 62%,#f36b21 160%);box-shadow:0 10px 28px rgba(20,23,34,.10);color:#fff}
		.completed-orders-hero:before{content:"";position:absolute;right:-58px;top:-82px;width:180px;height:180px;border-radius:50%;background:rgba(243,107,33,.22)}
		.completed-orders-hero-inner{position:relative;z-index:1;display:flex;align-items:center;justify-content:space-between;gap:12px}
		.completed-orders-title{display:flex;align-items:center;gap:10px;min-width:260px}
		.completed-orders-title-icon{display:grid;place-items:center;flex:0 0 38px;width:38px;height:38px;border-radius:11px;background:rgba(255,255,255,.13);box-shadow:inset 0 0 0 1px rgba(255,255,255,.14)}
		.completed-orders-title h2{margin:0;font-size:18px;font-weight:750;color:#fff}
		.completed-orders-title p{margin:3px 0 0;font-size:11px;font-weight:500;color:rgba(255,255,255,.74)!important}
		.completed-orders-stats{display:flex;align-items:center;justify-content:flex-end;gap:8px;flex-wrap:wrap;min-width:0}
		.completed-orders-stat{display:flex;align-items:center;gap:7px;min-height:34px;padding:6px 9px;border:1px solid rgba(255,255,255,.14);border-radius:999px;background:rgba(255,255,255,.10)}
		.completed-orders-stat span{display:block;margin:0;color:rgba(255,255,255,.68);font-size:9px;font-weight:800;letter-spacing:.06em;text-transform:uppercase}
		.completed-orders-stat strong{display:block;color:#fff;font-size:13px;line-height:1}
		.completed-orders-card{overflow:hidden;border:1px solid #e7e8ed;border-radius:14px;background:#fff;box-shadow:0 10px 26px rgba(28,31,42,.06)}
		.completed-orders-toolbar{display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;padding:10px 12px;border-bottom:1px solid #edf0f4;background:#fbfbfc}
		.completed-orders-toolbar-left{display:flex;align-items:center;gap:9px;flex-wrap:wrap;min-width:0}
		.completed-orders-search{position:relative}
		.completed-orders-search i{position:absolute;left:13px;top:50%;transform:translateY(-50%);color:#999ea8;font-size:12px}
		.completed-orders-search input{width:300px;max-width:70vw;height:36px;border:1px solid #dfe3ea;border-radius:11px;padding:7px 12px 7px 35px;font-size:12px;color:#30323a;background:#fff;outline:none}
		.completed-orders-search input:focus{border-color:#f36b21;box-shadow:0 0 0 3px rgba(243,107,33,.10)}
		.completed-orders-toolbar-note{color:#747782;font-size:11px}
		.completed-orders-refresh,.completed-orders-export{display:inline-flex;align-items:center;gap:7px;height:36px;border:1px solid #ffd4bd;border-radius:10px;padding:0 12px;background:#fff2ea;color:#e35e18;font-size:11px;font-weight:800;text-decoration:none;cursor:pointer}
		.completed-orders-refresh:hover,.completed-orders-export:hover{background:#f36b21;color:#fff;border-color:#f36b21;text-decoration:none}
		.completed-grid-wrap{padding:10px;background:#fff}
		.completed-grid-wrap .datagrid{border:1px solid #edf0f4!important;border-radius:12px;overflow:hidden}
		.completed-grid-wrap .datagrid-header{background:#f7f8fa!important;border-color:#e8ebf1!important}
		.completed-grid-wrap .datagrid-header td,.completed-grid-wrap .datagrid-header-row{height:36px!important}
		.completed-grid-wrap .datagrid-header .datagrid-cell span{color:#676a73!important;font-size:9px!important;font-weight:800!important;letter-spacing:.07em;text-transform:uppercase}
		.completed-grid-wrap .datagrid-row{height:46px!important}
		.completed-grid-wrap .datagrid-row td{border-color:#f0f1f4!important}
		.completed-grid-wrap .datagrid-row-over td{background:#fff8f4!important}
		.completed-grid-wrap .datagrid-cell{font-size:12px;color:#343741;line-height:1.35}
		.completed-grid-wrap .datagrid-cell-c1-job_name{max-height:38px;overflow:hidden}
		.completed-grid-wrap .pagination{background:#fbfbfc!important;border-color:#edf0f4!important;padding:5px 8px!important}
		.completed-order-code{display:inline-flex;align-items:center;justify-content:center;min-width:70px;padding:5px 8px;border-radius:999px;background:#fff2ea;color:#e45f18;font-family:Consolas,monospace;font-size:11px;font-weight:800}
		.completed-status-badge{display:inline-flex;align-items:center;justify-content:center;padding:5px 9px;border-radius:999px;background:#eaf8f0;color:#147946;font-size:10px;font-weight:800}
		.completed-action-group{display:flex;align-items:center;justify-content:center;gap:4px;flex-wrap:nowrap;white-space:nowrap}
		.completed-action-btn{display:inline-grid;place-items:center;width:28px;height:28px;border:1px solid transparent;border-radius:8px;text-decoration:none!important;cursor:pointer;font-size:11px;flex:0 0 28px}
		.completed-action-btn.view{background:#eef4ff;color:#2563eb;border-color:#cfe0ff}.completed-action-btn.edit{background:#f4f1ff;color:#6d4bd6;border-color:#ded5ff}.completed-action-btn.details{background:#eef8fb;color:#16809b;border-color:#ccecf4}.completed-action-btn.pdf{background:#fff0f0;color:#d64141;border-color:#ffd1d1}.completed-action-btn.whatsapp{background:#e8f7f1;color:#128c7e;border-color:#d4f0e6}
		.completed-action-btn:hover{filter:brightness(.96)}
		#status_modal .modal-content{overflow:hidden;border:0;border-radius:16px;box-shadow:0 24px 70px rgba(28,29,32,.24)}
		#status_modal .modal-header{display:flex!important;align-items:center;justify-content:space-between;min-height:66px;padding:15px 18px!important;border:0;border-bottom:1px solid #ececef;border-left:4px solid #f36b21;background:#fff!important}
		#status_modal .modal-title{margin:0;color:#252832;font-size:17px}
		#status_modal .modal-title strong{color:#252832!important}
		#status_modal .close{opacity:1;color:#f36b21;text-shadow:none}
		#status_modal .modal-body{padding:18px;background:#f7f7f8}
		#status_modal label,#status_modal span b{font-size:11px;font-weight:800;letter-spacing:.07em;text-transform:uppercase;color:#747782}
		#status_modal .form-control{height:43px;border:1px solid #dfe3ea;border-radius:12px;padding:8px 12px;background:#fff}
		#status_modal .modal-footer{display:flex;justify-content:flex-end;gap:8px;padding:13px 18px;border:0;border-top:1px solid #e7e8eb;background:#fff}
		#status_modal .btn-primary{border-color:#f36b21;background:#f36b21}
		@media(max-width:992px){.completed-orders-hero-inner{display:block}.completed-orders-stats{justify-content:flex-start;margin-top:10px}.completed-orders-search input{width:100%}.completed-orders-search{width:100%}.completed-orders-toolbar-left{width:100%}.completed-orders-toolbar-note{display:none}}
	</style>
	<div class="container-fluid completed-orders-page">

		<div class="completed-orders-shell">
			
			<div class="completed-orders-hero">
				<div class="completed-orders-hero-inner">
					<div class="completed-orders-title">
						<div class="completed-orders-title-icon"><i class="fa fa-check-circle"></i></div>
						<div>
							<h2>Completed Orders</h2>
							<p>Finished job cards with dynamic pagination and quick actions.</p>
						</div>
					</div>
					<div class="completed-orders-stats">
						<div class="completed-orders-stat"><span>Total Orders</span><strong><?php echo number_format($completed_total); ?></strong></div>
						<div class="completed-orders-stat"><span>Total Value</span><strong><?php echo number_format($completed_amount,0); ?></strong></div>
						<div class="completed-orders-stat"><span>This Month</span><strong><?php echo number_format($completed_month); ?></strong></div>
						<div class="completed-orders-stat"><span>Today</span><strong><?php echo number_format($completed_today); ?></strong></div>
					</div>
				</div>
			</div>

			<div class="completed-orders-card">


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
									$query_job_dt = "SELECT * FROM job_order_details WHERE job_id = ".$job_id_TB_status;
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

							<div class="completed-orders-toolbar" id="tb">
								<div class="completed-orders-toolbar-left">
									<div class="completed-orders-search">
										<i class="fa fa-search"></i>
										<input type="text" id="completed_order_search" placeholder="Search order, job, customer or user...">
									</div>
									<span class="completed-orders-toolbar-note">Showing completed jobs only. Results are loaded page-by-page.</span>
								</div>
								<div>
									<button type="button" class="completed-orders-refresh" id="completed_order_refresh"><i class="fa fa-sync"></i> Refresh</button>
									<a id="export_datagrid" class="completed-orders-export"><i class="fa fa-file-excel"></i> Excel</a>
								</div>
							</div>

							<?php $user_id = "1"; ?>

							<div class="completed-grid-wrap">
							<table id="dg" title="" style="width: 100%;height:calc(100vh - 245px);min-height:430px;margin: auto;" data-options="singleSelect:true,fitColumns:true,rownumbers:false,remoteSort:true,remoteFilter:true,clientPaging:false,nowrap:false,autoRowHeight:false,method:'POST',url:'Jobs/data_comp_jobs.php',toolbar:'#tb'" pagination="true" pageSize="30" pageList="[10,20,30,40,50,100,200]">

								<thead>
									<tr>
										<th style="width: 78px" data-options="field:'order_code',align:'center'"><b>Order Code</b></th>
										<th style="width: 76px" data-options="field:'dated',align:'center'"><b>Dated</b></th>
										<th style="width: 285px" data-options="field:'job_name',align:'left'"><b>Job Name</b></th>
										<th style="width: 145px" data-options="field:'customer',align:'left'"><b>Customer</b></th>
										<th style="width: 110px" data-options="field:'order_rec_by',align:'left'"><b>Received By</b></th>
										<th style="width: 82px" data-options="field:'status',align:'center'"><b>Status</b></th>
										<th style="width: 155px" data-options="field:'action',align:'center'"><b>Action</b></th>
									</tr>
								</thead>
							</table>
							</div>
			</div>
		</div>

		<div class="modal fade" id="status_modal" tabindex="-1" role="dialog" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<form method="post">
						<div class="modal-header bg-warning">
							<button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-window-close" aria-hidden="true"></i>
							</button>
							<h4 class="modal-title"><strong style="color: white">Status</strong></h4>
						</div>
						<div class="modal-body">
							<span><b>Status:</b></span>
							<select class="form-control" name="job_status_TB" id="job_status_TB">
								<option value="">Please Select</option>
								<option value="0">Pending</option>
								<option value="3">Plate Setting</option>
								<option value="1">On Machine</option>
								<option value="4">Plate Washing</option>
								<option value="5">Oven Baking</option>
								<option value="2">Completed</option>
							</select>
							<input type="hidden" name="job_id_TB_status" id="job_id_TB_status">
						</div>
						<div class="modal-footer">
							<button type="submit" class="btn btn-primary btn-embossed" id="change_status" name="change_status">Yes</button>
							<button type="button" class="btn btn-default btn-embossed" data-dismiss="modal">No</button>
						</div>
					</form>
				</div>
			</div>
		</div>

	</div>


	<script>
		// $(document).ready(function(){
		// 	$('table').dataTable( {
		// 		order: [[0, 'desc']]
		// 	});
		// })
		$(function(){
			var completedSearchTimer = null;
			function reloadCompletedOrders(){
				$('#dg').datagrid('load',{
					search: $('#completed_order_search').val()
				});
			}
			$('#completed_order_search').on('keyup',function(){
				clearTimeout(completedSearchTimer);
				completedSearchTimer = setTimeout(reloadCompletedOrders,350);
			});
			$('#completed_order_refresh').on('click',function(){
				$('#completed_order_search').val('');
				reloadCompletedOrders();
			});
			$('#export_datagrid').on('click',function(e){
				e.preventDefault();
				if($.fn.datagrid && $.fn.datagrid.methods && $.fn.datagrid.methods.toExcel){
					$('#dg').datagrid('toExcel','completed_orders.xls');
				}else{
					alert_toast('Excel export plugin is not loaded on this page.','warning');
				}
			});
		});
		
		$(document).on("click","#status_btn",function() {
			var data=$(this).attr('data-value'); 
			var ans=data.split('^');

			$("#job_id_TB_status").val(ans[0]);
			$("#job_status_TB").val(ans[1]);
		});


		$(document).on("click",".view_order",function() {
			uni_modal("Order Details","view_order.php?id="+$(this).attr('data-id'),"mid-large")
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
