<?php include('db_connect.php');

if(in_array("36",$_SESSION['login_Permisions']))
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

	$total_accounts = 0;
	$parent_accounts = 0;
	$child_accounts = 0;
	$account_summary = $conn->query("SELECT COUNT(*) AS total_accounts, SUM(CASE WHEN parent_id = 0 THEN 1 ELSE 0 END) AS parent_accounts, SUM(CASE WHEN parent_id <> 0 THEN 1 ELSE 0 END) AS child_accounts FROM accounts WHERE company_id = ".$company_id_acc);
	if($account_summary && $account_summary->num_rows > 0){
		$summary_row = $account_summary->fetch_assoc();
		$total_accounts = $summary_row['total_accounts'] ?? 0;
		$parent_accounts = $summary_row['parent_accounts'] ?? 0;
		$child_accounts = $summary_row['child_accounts'] ?? 0;
	}
	?>
	<style>
		.coa-page{padding:0 0 26px}
		.coa-shell{max-width:1240px;margin:0 auto}
		.coa-hero{position:relative;overflow:hidden;margin-bottom:18px;padding:22px 24px;border:1px solid #ececf0;border-radius:20px;background:linear-gradient(135deg,#171923 0%,#282b35 58%,#f36b21 160%);box-shadow:0 18px 45px rgba(20,23,34,.12);color:#fff}
		.coa-hero:before{content:"";position:absolute;right:-70px;top:-80px;width:230px;height:230px;border-radius:50%;background:rgba(243,107,33,.28)}
		.coa-hero:after{content:"";position:absolute;right:120px;bottom:-120px;width:220px;height:220px;border-radius:50%;background:rgba(255,255,255,.06)}
		.coa-hero-content{position:relative;z-index:1;display:flex;align-items:center;justify-content:space-between;gap:18px}
		.coa-title-wrap{display:flex;align-items:center;gap:14px}
		.coa-title-icon{display:grid;place-items:center;flex:0 0 48px;width:48px;height:48px;border-radius:15px;background:rgba(255,255,255,.12);box-shadow:inset 0 0 0 1px rgba(255,255,255,.16)}
		.coa-title-icon i{font-size:20px;color:#fff}
		.coa-title-copy h2{margin:0;font-size:22px;font-weight:700;letter-spacing:-.02em;color:#fff}
		.coa-title-copy p{margin:5px 0 0;font-size:12px;color:#fff!important}
		.coa-action-btn{display:inline-flex;align-items:center;gap:8px;min-height:41px;padding:10px 15px;border:0;border-radius:13px;color:#fff!important;background:#f36b21;box-shadow:0 12px 24px rgba(243,107,33,.25);font-size:12px;font-weight:700;text-decoration:none!important;transition:.18s}
		.coa-action-btn:hover{transform:translateY(-1px);background:#df5913;color:#fff!important}
		.coa-card{overflow:hidden;border:1px solid #e7e8ed;border-radius:18px;background:#fff;box-shadow:0 14px 38px rgba(28,31,42,.07)}
		.coa-toolbar{display:grid;grid-template-columns:1fr auto;gap:18px;align-items:center;padding:18px 20px;border-bottom:1px solid #edf0f4;background:linear-gradient(180deg,#fff,#fbfbfc)}
		.coa-filter label{display:block;margin:0 0 7px;font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#8b8d95}
		.coa-filter select{height:43px!important;min-width:290px;border:1px solid #dfe3ea!important;border-radius:12px!important;padding:8px 12px;font-size:13px;color:#30323a;background:#fff;box-shadow:none!important}
		.coa-filter select:focus{border-color:#f36b21!important;box-shadow:0 0 0 3px rgba(243,107,33,.1)!important}
		.coa-stats{display:flex;gap:10px;flex-wrap:wrap;justify-content:flex-end}
		.coa-stat{min-width:118px;padding:11px 13px;border:1px solid #edf0f4;border-radius:14px;background:#fff}
		.coa-stat span{display:block;font-size:10px;font-weight:800;letter-spacing:.07em;text-transform:uppercase;color:#9698a1}
		.coa-stat strong{display:block;margin-top:5px;font-size:20px;line-height:1;color:#22242b}
		.coa-table-wrap{padding:18px;background:#fff}
		.coa-table-responsive{overflow:auto;border:1px solid #edf0f4;border-radius:15px}
		.coa-table{width:100%;margin:0!important;border:0!important;border-collapse:separate;border-spacing:0;background:#fff}
		.coa-table thead th{position:sticky;top:0;z-index:2;padding:13px 14px!important;border:0!important;border-bottom:1px solid #e8ebf1!important;background:#f7f8fa!important;color:#676a73;font-size:10px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;white-space:nowrap}
		.coa-table tbody td,.coa-table tbody th{padding:12px 14px!important;border:0!important;border-bottom:1px solid #f0f1f4!important;vertical-align:middle!important;font-size:13px;color:#343741}
		.coa-table tbody tr:hover td{background:#fff8f4}
		.coa-section-row th{padding:14px 16px!important;background:#f8f9fb!important;color:#1f2330!important;font-size:12px!important;font-weight:800!important;letter-spacing:.04em;text-transform:uppercase;border-bottom:1px solid #e9ecf1!important}
		.coa-section-row.is-child th{color:#f36b21!important;background:#fff6ef!important}
		.coa-account-row{color:#2f3545!important}
		.coa-account-row td:nth-child(3){font-family:Consolas,monospace;font-weight:700;color:#1e2535}
		.coa-account-name{font-weight:700;color:#252832}
		.coa-statement-badge{display:inline-flex;align-items:center;justify-content:center;min-height:26px;padding:6px 10px;border-radius:999px;background:#f4f5f7;color:#5d6069;font-size:11px;font-weight:700}
		.coa-toggle-cell{text-align:center}
		.coa-table img.plus_icon,.coa-table img.minus_icon,.coa-table img.loading_gif{width:24px!important;height:24px!important;padding:5px;border-radius:999px;background:#fff;box-shadow:0 0 0 1px #e4e7ec;transition:.18s}
		.coa-table img.plus_icon:hover,.coa-table img.minus_icon:hover{transform:scale(1.08);box-shadow:0 0 0 1px #f36b21,0 8px 18px rgba(243,107,33,.16)}
		.coa-table img.loading_gif{box-shadow:none;background:transparent}
		.coa-empty{padding:26px!important;text-align:center;color:#8b8d95!important}
		@media(max-width:768px){.coa-hero-content,.coa-toolbar{grid-template-columns:1fr;display:block}.coa-title-wrap{margin-bottom:16px}.coa-filter select{min-width:100%;width:100%}.coa-stats{justify-content:flex-start;margin-top:15px}.coa-action-btn{width:100%;justify-content:center}}
	</style>

	<div class="container-fluid coa-page">

		<div class="coa-shell">
			<div class="coa-hero">
				<div class="coa-hero-content">
					<div class="coa-title-wrap">
						<div class="coa-title-icon"><i class="fa fa-sitemap"></i></div>
						<div class="coa-title-copy">
							<h2>Chart of Accounts</h2>
							<p>Organized account hierarchy for <?php echo htmlspecialchars($company_name); ?>.</p>
						</div>
					</div>
					<a class="coa-action-btn" href="index.php?page=Accounting/add-new-account" id="new_order">
						<i class="fa fa-plus"></i> Add New Account
					</a>
				</div>
			</div>

			<div class="coa-card">
				<div class="coa-toolbar">
					<div class="coa-filter">
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
					<div class="coa-stats">
						<div class="coa-stat"><span>Total Accounts</span><strong><?php echo number_format($total_accounts); ?></strong></div>
						<div class="coa-stat"><span>Parent Heads</span><strong><?php echo number_format($parent_accounts); ?></strong></div>
						<div class="coa-stat"><span>Sub Accounts</span><strong><?php echo number_format($child_accounts); ?></strong></div>
					</div>
				</div>

				<div class="coa-table-wrap">
					<div class="coa-table-responsive">

							<table class="table table-bordered coa-table">
								<thead>
									<tr>
										<th style="width: 4%"></th>
										<th style="width: 5%">SR#</th>
										<th style="width: 10%">Account No</th>
										<th colspan="4">Account Name</th>
										<th style="width: 20%">Financial Statement</th>
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
										<tr class="coa-section-row <?php echo $category_list[$i]['Parent'] == 0 ? 'is-parent' : 'is-child'; ?>">
											<th colspan="87" style="color: <?php echo $colored ?>"><?= $category_list[$i]['TypeName'] ?></th>
										</tr>
										<?php

										$query_acc = "SELECT * FROM accounts as a INNER JOIN account_category as b on a.acc_cat = b.ac_id WHERE a.acc_type = ".$category_list[$i]['TypeId']." AND parent_id = 0 AND company_id = ".$company_id_acc;
										$result_acc = mysqli_query($conn,$query_acc);
										$counterthis = 0;
										while($data_acc = mysqli_fetch_array($result_acc)){
											$this_acc_no = $data_acc['account_no'];
											// if()
											?>
											<tr class="coa-account-row">
												<td class="coa-toggle-cell">
													<img  style="cursor:pointer;width:18px" class="image plus_icon" src="Accounting/plus.png" alt="plus">
													<img  style="cursor:pointer;width:18px" class="image minus_icon" src="Accounting/minus.png" alt="minus">
													<img  style="cursor:pointer;width:18px" class="image loading_gif" src="Accounting/load.gif" alt="minus">
												</td>
												<td class="text-center"><?php echo ++$counterthis; ?></td>
												<td><?php echo $data_acc['account_no'] ?>
												<input type="hidden" name="account_no_tb" value="<?php echo $data_acc['account_no']  ?>" class="account_no_tb">
											</td>
											<td colspan="4"><span class="coa-account-name"><?php echo $data_acc['acc_name'] ?></span></td>
											<td><span class="coa-statement-badge"><?php echo $fin_statement_list[$data_acc['fin_statement']] ?></span></td>
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


	<?php
}else{
	include 'accessDenied.php';
}
?>
