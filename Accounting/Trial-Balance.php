<?php include('db_connect.php');

if(in_array("77",$_SESSION['login_Permisions']))
{
	$category_list = array();

	$category = $conn->query("SELECT * FROM account_types WHERE type_parent_id = 0 AND del_status = 0");
	$counter = 0;
	while($row=$category->fetch_assoc()){
		$category_list[$counter] =
		array(
			'TypeName' => $row['type_name'],
			'TypeId' => $row['acc_type_id'],
			'Position' => $counter,
			'Parent' => 0,
		);

		$category_sub = $conn->query("SELECT * FROM account_types WHERE type_parent_id = ".$row['acc_type_id']."  AND del_status = 0");
		while($row_sub=$category_sub->fetch_assoc()){
			$counter++;
			$category_list[$counter] =
			array(
				'TypeName' => $row_sub['type_name'],
				'TypeId' => $row_sub['acc_type_id'],
				'Position' => $counter,
				'Parent' => 1,
			);
		}

		$counter++;
	}

	if(isset($_GET['comp'])){
		$company_id_acc = (int)$_GET['comp'];
		if($company_id_acc == ""){
			$company_id_acc = 1;
		}
	}else{
		$company_id_acc = 1;
	}

	$company_name = 'Selected Company';
	$company_qry = $conn->query("SELECT comp_name FROM companies WHERE comp_id = ".$company_id_acc." LIMIT 1");
	if($company_qry && $company_qry->num_rows > 0){
		$company_row = $company_qry->fetch_assoc();
		$company_name = $company_row['comp_name'];
	}

	$total_debit = 0;
	$total_credit = 0;
	$company_totals = $conn->query("SELECT SUM(v.debit_amount) AS debit_total, SUM(v.credit_amount) AS credit_total FROM vouchers v INNER JOIN accounts a ON v.account_id = a.account_no WHERE a.company_id = ".$company_id_acc);
	if($company_totals && $company_totals->num_rows > 0){
		$totals_row = $company_totals->fetch_assoc();
		$total_debit = $totals_row['debit_total'] ?? 0;
		$total_credit = $totals_row['credit_total'] ?? 0;
	}
	?>
	<style>
		.trial-page{padding:0 0 26px}
		.trial-shell{max-width:1240px;margin:0 auto}
		.trial-hero{position:relative;overflow:hidden;margin-bottom:18px;padding:22px 24px;border-radius:20px;background:linear-gradient(135deg,#171923 0%,#282b35 58%,#f36b21 160%);box-shadow:0 18px 45px rgba(20,23,34,.12);color:#fff}
		.trial-hero:before{content:"";position:absolute;right:-70px;top:-80px;width:230px;height:230px;border-radius:50%;background:rgba(243,107,33,.28)}
		.trial-hero-content{position:relative;z-index:1;display:flex;align-items:center;justify-content:space-between;gap:18px}
		.trial-title-wrap{display:flex;align-items:center;gap:14px}
		.trial-title-icon{display:grid;place-items:center;flex:0 0 48px;width:48px;height:48px;border-radius:15px;background:rgba(255,255,255,.12)}
		.trial-title-copy h2{margin:0;font-size:22px;font-weight:700;color:#fff}
		.trial-title-copy p{display:inline-block;margin:8px 0 0;padding:6px 11px;border-radius:999px;background:rgba(255,255,255,.14);box-shadow:inset 0 0 0 1px rgba(255,255,255,.18);font-size:13px;font-weight:600;color:#fff}
		.trial-card{overflow:hidden;border:1px solid #e7e8ed;border-radius:18px;background:#fff;box-shadow:0 14px 38px rgba(28,31,42,.07)}
		.trial-toolbar{display:grid;grid-template-columns:1fr auto;gap:18px;align-items:center;padding:18px 20px;border-bottom:1px solid #edf0f4;background:linear-gradient(180deg,#fff,#fbfbfc)}
		.trial-filter label{display:block;margin:0 0 7px;font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#8b8d95}
		.trial-filter select{height:43px!important;min-width:290px;border:1px solid #dfe3ea!important;border-radius:12px!important;padding:8px 12px;font-size:13px;color:#30323a;background:#fff}
		.trial-stats{display:flex;gap:10px;flex-wrap:wrap;justify-content:flex-end}
		.trial-stat{min-width:140px;padding:11px 13px;border:1px solid #edf0f4;border-radius:14px;background:#fff}
		.trial-stat span{display:block;font-size:10px;font-weight:800;letter-spacing:.07em;text-transform:uppercase;color:#9698a1}
		.trial-stat strong{display:block;margin-top:5px;font-size:19px;line-height:1;color:#22242b}
		.trial-table-wrap{padding:18px;background:#fff}
		.trial-table-responsive{overflow:auto;border:1px solid #edf0f4;border-radius:15px}
		.trial-table{width:100%;margin:0!important;border:0!important;border-collapse:separate;border-spacing:0;background:#fff}
		.trial-table thead th{position:sticky;top:0;z-index:2;padding:13px 14px!important;border:0!important;border-bottom:1px solid #e8ebf1!important;background:#f7f8fa!important;color:#676a73;font-size:10px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;white-space:nowrap}
		.trial-table tbody td,.trial-table tbody th{padding:12px 14px!important;border:0!important;border-bottom:1px solid #f0f1f4!important;vertical-align:middle!important;font-size:13px;color:#343741}
		.trial-table tbody tr:hover td{background:#fff8f4}
		.trial-section-row th{padding:14px 16px!important;background:#f8f9fb!important;color:#1f2330!important;font-size:12px!important;font-weight:800!important;letter-spacing:.04em;text-transform:uppercase;border-bottom:1px solid #e9ecf1!important}
		.trial-section-row.is-child th{color:#f36b21!important;background:#fff6ef!important}
		.trial-account-row td:nth-child(3){font-family:Consolas,monospace;font-weight:700;color:#1e2535}
		.trial-account-name{font-weight:700;color:#252832}
		.trial-balance{display:block;text-align:right;font-weight:800;color:#22242b}
		.trial-balance.is-credit{color:#dc3545}
		.trial-balance.is-debit{color:#159447}
		.trial-table img.plus_icon,.trial-table img.minus_icon,.trial-table img.loading_gif{width:24px!important;height:24px!important;padding:5px;border-radius:999px;background:#fff;box-shadow:0 0 0 1px #e4e7ec;transition:.18s}
		.trial-table img.plus_icon:hover,.trial-table img.minus_icon:hover{transform:scale(1.08);box-shadow:0 0 0 1px #f36b21,0 8px 18px rgba(243,107,33,.16)}
		.trial-table img.loading_gif{box-shadow:none;background:transparent}
		@media(max-width:768px){.trial-hero-content,.trial-toolbar{grid-template-columns:1fr;display:block}.trial-title-wrap{margin-bottom:16px}.trial-filter select{min-width:100%;width:100%}.trial-stats{justify-content:flex-start;margin-top:15px}}
	</style>

	<div class="container-fluid trial-page">
		<div class="trial-shell">
			<div class="trial-hero">
				<div class="trial-hero-content">
					<div class="trial-title-wrap">
						<div class="trial-title-icon"><i class="fa fa-balance-scale"></i></div>
						<div class="trial-title-copy">
							<h2>Trial Balance</h2>
							<p>Company-wise account balances for <?php echo htmlspecialchars($company_name); ?>.</p>
						</div>
					</div>
				</div>
			</div>

			<div class="trial-card">
				<div class="trial-toolbar">
					<div class="trial-filter">
						<label for="company_id_acc">Company</label>
						<select class="form-control" name="company_id_acc" id="company_id_acc" required="true">
							<option value="">Select Company</option>
							<?php
							$query_comp = "SELECT * FROM companies";
							$result_comp = mysqli_query($conn,$query_comp);
							while($data_comp = mysqli_fetch_array($result_comp)){
								$selected_val = "";
								if($company_id_acc == $data_comp['comp_id']){
									$selected_val = "selected";
								}
								?>
								<option <?= $selected_val ?> value="<?= $data_comp['comp_id'] ?>"><?= $data_comp['comp_name'] ?></option>
								<?php
							}
							?>
						</select>
					</div>
					<div class="trial-stats">
						<div class="trial-stat"><span>Total Debit</span><strong><?php echo number_format($total_debit,2); ?></strong></div>
						<div class="trial-stat"><span>Total Credit</span><strong><?php echo number_format($total_credit,2); ?></strong></div>
						<div class="trial-stat"><span>Difference</span><strong><?php echo number_format($total_debit - $total_credit,2); ?></strong></div>
					</div>
				</div>
				<div class="trial-table-wrap">
					<div class="trial-table-responsive">
							<table class="table table-bordered trial-table">
								<thead>
									<tr>
										<th style="width: 4%"></th>
										<th style="width: 5%">SR#</th>
										<th style="width: 10%">Account No</th>
										<th colspan="4">Account Name</th>
										<th style="width: 20%">Balance</th>
									</tr>
								</thead>
								<tbody>
									
									<?php
									$fin_statement_list = array('','Balance Sheet','Profit & Loss'); 
									for($i=0;$i<count($category_list);$i++){
										if($category_list[$i]['Parent'] == 0){
											$colored = "Black";
										}else{
											$colored = "Blue";
										}
										?>
										<tr class="trial-section-row <?php echo $category_list[$i]['Parent'] == 0 ? 'is-parent' : 'is-child'; ?>">
											<th colspan="87" style="color: <?php echo $colored ?>"><?= $category_list[$i]['TypeName'] ?></th>
										</tr>
										<?php

										$query_acc = "SELECT * FROM accounts as a INNER JOIN account_category as b on a.acc_cat = b.ac_id WHERE a.acc_type = ".$category_list[$i]['TypeId']." AND parent_id = 0 AND company_id = ".$company_id_acc;
										$result_acc = mysqli_query($conn,$query_acc);
										$counterthis = 0;
										while($data_acc = mysqli_fetch_array($result_acc)){
											$Balance = 0;
											$this_acc_no = $data_acc['account_no'];
											
											$queryVouchersDB1 = "SELECT SUM(v.debit_amount) - SUM(v.credit_amount) as sumAmt FROM vouchers v INNER JOIN accounts a ON v.account_id = a.account_no WHERE v.account_id IN ($this_acc_no) AND a.company_id = ".$company_id_acc;
											$resultVouchersDB1 = mysqli_query($conn,$queryVouchersDB1);
											$dataVouchersDB1 = mysqli_fetch_array($resultVouchersDB1);
											$Balance += $dataVouchersDB1['sumAmt'] ?? 0;


											$queryVouchersDB2 = "SELECT SUM(v.debit_amount) - SUM(v.credit_amount) as sumAmt FROM vouchers v INNER JOIN accounts a ON v.account_id = a.account_no WHERE v.account_id IN (SELECT account_no from accounts where parent_id = $this_acc_no AND company_id = ".$company_id_acc.") AND a.company_id = ".$company_id_acc;
											$resultVouchersDB2 = mysqli_query($conn,$queryVouchersDB2);
											$dataVouchersDB2 = mysqli_fetch_array($resultVouchersDB2);
											$Balance += $dataVouchersDB2['sumAmt'] ?? 0;



											$queryVouchersDB3 = "SELECT SUM(v.debit_amount) - SUM(v.credit_amount) as sumAmt FROM vouchers v INNER JOIN accounts a ON v.account_id = a.account_no WHERE v.account_id IN (SELECT account_no FROM accounts where company_id = ".$company_id_acc." AND parent_id IN (SELECT account_no from accounts where parent_id = $this_acc_no AND company_id = ".$company_id_acc.")) AND a.company_id = ".$company_id_acc;
											$resultVouchersDB3 = mysqli_query($conn,$queryVouchersDB3);
											$dataVouchersDB3 = mysqli_fetch_array($resultVouchersDB3);
											$Balance += $dataVouchersDB3['sumAmt'] ?? 0;


											?>
											<tr class="trial-account-row">
												<td class="text-center">
													<img  style="cursor:pointer;width:18px" class="image plus_icon" src="Accounting/plus.png" alt="plus">
													<img  style="cursor:pointer;width:18px" class="image minus_icon" src="Accounting/minus.png" alt="minus">
													<img  style="cursor:pointer;width:18px" class="image loading_gif" src="Accounting/load.gif" alt="minus">
												</td>
												<td class="text-center"><?php echo ++$counterthis; ?></td>
												<td><?php echo $data_acc['account_no'] ?>
												<input type="hidden" name="account_no_tb" value="<?php echo $data_acc['account_no']  ?>" class="account_no_tb">
											</td>
											<td colspan="4"><span class="trial-account-name"><?php echo $data_acc['acc_name'] ?></span></td>
											<td><span class="trial-balance <?php echo $Balance < 0 ? 'is-credit' : 'is-debit'; ?>"><?= number_format($Balance,2) ?></span></td>
										</tr>

										<?php											

									}
								}
								?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>	







	<script>

		$('.delete_acc_type').click(function(){
			_conf("Are you sure to delete this Account Types?","delete_acc_type",[$(this).attr('data-id')])
		})
		function delete_acc_type($id){
			start_load()
			$.ajax({
				url:'ajax.php?action=delete_acc_type',
				method:'POST',
				data:{acc_type_id:$id},
				success:function(resp){
					if(resp==1){
						alert_toast("Data successfully deleted",'success')
						setTimeout(function(){
							location.reload()
						},1500)

					}else{
						alert(resp);
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
