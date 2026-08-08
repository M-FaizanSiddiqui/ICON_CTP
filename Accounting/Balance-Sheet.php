<?php include('db_connect.php');

$company_id_acc = isset($_GET['comp']) ? (int)$_GET['comp'] : 1;
if($company_id_acc <= 0){
	$company_id_acc = 1;
}

$as_of_date = isset($_GET['toDt']) && $_GET['toDt'] != '' ? $_GET['toDt'] : date('Y-m-d');
$as_of_date_safe = mysqli_real_escape_string($conn,$as_of_date);

$company_name = 'Selected Company';
$company_qry = $conn->query("SELECT comp_name FROM companies WHERE comp_id = ".$company_id_acc." LIMIT 1");
if($company_qry && $company_qry->num_rows > 0){
	$company_row = $company_qry->fetch_assoc();
	$company_name = $company_row['comp_name'];
}

function balance_sheet_account_balance($conn,$account_no,$company_id,$as_of_date){
	$account_no = (int)$account_no;
	$company_id = (int)$company_id;
	$account_numbers = array($account_no);
	$queue = array($account_no);

	while(count($queue) > 0){
		$current = array_shift($queue);
		$children = $conn->query("SELECT account_no FROM accounts WHERE parent_id = ".(int)$current." AND company_id = ".$company_id." AND del_status = 0");
		if($children){
			while($child = $children->fetch_assoc()){
				$child_no = (int)$child['account_no'];
				if(!in_array($child_no,$account_numbers)){
					$account_numbers[] = $child_no;
					$queue[] = $child_no;
				}
			}
		}
	}

	$account_list = implode(',',$account_numbers);
	$balance_qry = $conn->query("SELECT SUM(debit_amount) - SUM(credit_amount) AS balance_amount FROM vouchers WHERE account_id IN (".$account_list.") AND trans_dated <= '".$as_of_date."'");
	if($balance_qry && $balance_qry->num_rows > 0){
		$row = $balance_qry->fetch_assoc();
		return (float)($row['balance_amount'] ?? 0);
	}

	return 0;
}

function balance_sheet_account_direct_balance($conn,$account_no,$as_of_date){
	$account_no = (int)$account_no;
	$balance_qry = $conn->query("SELECT SUM(debit_amount) - SUM(credit_amount) AS balance_amount FROM vouchers WHERE account_id = ".$account_no." AND trans_dated <= '".$as_of_date."'");
	if($balance_qry && $balance_qry->num_rows > 0){
		$row = $balance_qry->fetch_assoc();
		return (float)($row['balance_amount'] ?? 0);
	}

	return 0;
}

function balance_sheet_child_accounts($conn,$parent_no,$company_id,$as_of_date,$level = 1){
	$children_data = array();
	$children_qry = $conn->query("SELECT account_no,acc_name FROM accounts WHERE parent_id = ".(int)$parent_no." AND company_id = ".(int)$company_id." AND del_status = 0 ORDER BY account_no ASC");

	while($children_qry && $child = $children_qry->fetch_assoc()){
		$balance = balance_sheet_account_balance($conn,$child['account_no'],$company_id,$as_of_date);
		$direct_balance = balance_sheet_account_direct_balance($conn,$child['account_no'],$as_of_date);
		$children_data[] = array(
			'account_no' => $child['account_no'],
			'acc_name' => $child['acc_name'],
			'balance' => $balance,
			'direct_balance' => $direct_balance,
			'debit_amount' => $balance >= 0 ? $balance : 0,
			'credit_amount' => $balance < 0 ? abs($balance) : 0,
			'level' => $level,
			'children' => balance_sheet_child_accounts($conn,$child['account_no'],$company_id,$as_of_date,$level + 1)
		);
	}

	return $children_data;
}

function balance_sheet_render_account_rows($account){
	$indent = min((int)($account['level'] ?? 0),5);
	$is_child = $indent > 0;
	$row_class = $is_child ? 'bs-child-row level-'.$indent : 'bs-parent-row';
	$debit = $account['debit_amount'] != 0 ? number_format($account['debit_amount'],2) : '-';
	$credit = $account['credit_amount'] != 0 ? number_format($account['credit_amount'],2) : '-';
	$direct_balance = (float)($account['direct_balance'] ?? 0);
	$direct_note = $is_child && $direct_balance != 0 ? '<span class="bs-direct-note">Direct: '.number_format($direct_balance,2).'</span>' : '';

	echo '<tr class="'.$row_class.'">';
	echo '<td class="bs-account-no">'.htmlspecialchars($account['account_no']).'</td>';
	echo '<td><span class="bs-account-indent indent-'.$indent.'"><span class="bs-tree-mark"></span><span class="bs-account-name">'.htmlspecialchars($account['acc_name']).'</span>'.$direct_note.'</span></td>';
	echo '<td class="bs-amount bs-debit">'.$debit.'</td>';
	echo '<td class="bs-amount bs-credit">'.$credit.'</td>';
	echo '</tr>';

	if(isset($account['children']) && count($account['children']) > 0){
		foreach($account['children'] as $child_account){
			balance_sheet_render_account_rows($child_account);
		}
	}
}

$balance_sheet_accounts = array();
$total_debit_balance = 0;
$total_credit_balance = 0;

$type_qry = $conn->query("SELECT acc_type_id,type_name,type_parent_id FROM account_types WHERE del_status = 0 ORDER BY type_parent_id ASC, acc_type_id ASC");
while($type_qry && $type = $type_qry->fetch_assoc()){
	$accounts_qry = $conn->query("SELECT account_no,acc_name FROM accounts WHERE acc_type = ".$type['acc_type_id']." AND parent_id = 0 AND company_id = ".$company_id_acc." AND fin_statement = 1 AND del_status = 0 ORDER BY account_no ASC");
	$accounts = array();
	while($accounts_qry && $account = $accounts_qry->fetch_assoc()){
		$balance = balance_sheet_account_balance($conn,$account['account_no'],$company_id_acc,$as_of_date_safe);
		$direct_balance = balance_sheet_account_direct_balance($conn,$account['account_no'],$as_of_date_safe);
		$debit_amount = $balance >= 0 ? $balance : 0;
		$credit_amount = $balance < 0 ? abs($balance) : 0;
		$total_debit_balance += $debit_amount;
		$total_credit_balance += $credit_amount;
		$accounts[] = array(
			'account_no' => $account['account_no'],
			'acc_name' => $account['acc_name'],
			'balance' => $balance,
			'direct_balance' => $direct_balance,
			'debit_amount' => $debit_amount,
			'credit_amount' => $credit_amount,
			'level' => 0,
			'children' => balance_sheet_child_accounts($conn,$account['account_no'],$company_id_acc,$as_of_date_safe,1)
		);
	}

	if(count($accounts) > 0){
		$balance_sheet_accounts[] = array(
			'type_name' => $type['type_name'],
			'type_parent_id' => $type['type_parent_id'],
			'accounts' => $accounts
		);
	}
}
?>

<style>
	.bs-page{padding:0 0 26px}
	.bs-shell{max-width:1240px;margin:0 auto}
	.bs-hero{position:relative;overflow:hidden;margin-bottom:18px;padding:22px 24px;border-radius:20px;background:linear-gradient(135deg,#171923 0%,#282b35 58%,#f36b21 160%);box-shadow:0 18px 45px rgba(20,23,34,.12);color:#fff}
	.bs-hero:before{content:"";position:absolute;right:-70px;top:-80px;width:230px;height:230px;border-radius:50%;background:rgba(243,107,33,.28)}
	.bs-hero-content{position:relative;z-index:1;display:flex;align-items:center;justify-content:space-between;gap:18px}
	.bs-title-wrap{display:flex;align-items:center;gap:14px}
	.bs-title-icon{display:grid;place-items:center;flex:0 0 48px;width:48px;height:48px;border-radius:15px;background:rgba(255,255,255,.12)}
	.bs-title-copy h2{margin:0;font-size:22px;font-weight:650;color:#fff}
	.bs-title-copy p{display:inline-block;margin:8px 0 0;padding:6px 11px;border-radius:999px;background:rgba(255,255,255,.14);box-shadow:inset 0 0 0 1px rgba(255,255,255,.18);font-size:13px;font-weight:500;color:#fff}
	.bs-card{overflow:hidden;border:1px solid #e7e8ed;border-radius:18px;background:#fff;box-shadow:0 14px 38px rgba(28,31,42,.07)}
	.bs-toolbar{display:grid;grid-template-columns:1fr auto;gap:18px;align-items:center;padding:18px 20px;border-bottom:1px solid #edf0f4;background:linear-gradient(180deg,#fff,#fbfbfc)}
	.bs-filters{display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end}
	.bs-filter label{display:block;margin:0 0 7px;font-size:11px;font-weight:650;letter-spacing:.08em;text-transform:uppercase;color:#8b8d95}
	.bs-filter select,.bs-filter input{height:43px!important;min-width:210px;border:1px solid #dfe3ea!important;border-radius:12px!important;padding:8px 12px;font-size:13px;color:#30323a;background:#fff}
	.bs-filter-btn{height:43px;padding:0 16px;border:0;border-radius:12px;background:#f36b21;color:#fff;font-size:12px;font-weight:650;box-shadow:0 10px 22px rgba(243,107,33,.2)}
	.bs-stats{display:flex;gap:10px;flex-wrap:wrap;justify-content:flex-end}
	.bs-stat{min-width:140px;padding:11px 13px;border:1px solid #edf0f4;border-radius:14px;background:#fff}
	.bs-stat span{display:block;font-size:10px;font-weight:650;letter-spacing:.07em;text-transform:uppercase;color:#9698a1}
	.bs-stat strong{display:block;margin-top:5px;font-size:19px;line-height:1;font-weight:650;color:#22242b}
	.bs-table-wrap{padding:18px;background:#fff}
	.bs-table-responsive{overflow:auto;border:1px solid #edf0f4;border-radius:15px}
	.bs-table{width:100%;margin:0!important;border:0!important;border-collapse:separate;border-spacing:0;background:#fff}
	.bs-table thead th{position:sticky;top:0;z-index:2;padding:13px 14px!important;border:0!important;border-bottom:1px solid #e8ebf1!important;background:#f7f8fa!important;color:#676a73;font-size:10px;font-weight:650;letter-spacing:.08em;text-transform:uppercase;white-space:nowrap}
	.bs-table tbody td,.bs-table tbody th{padding:12px 14px!important;border:0!important;border-bottom:1px solid #f0f1f4!important;vertical-align:middle!important;font-size:13px;color:#343741}
	.bs-table tbody tr:hover td{background:#fff8f4}
	.bs-section-row th{padding:14px 16px!important;background:#f8f9fb!important;color:#1f2330!important;font-size:12px!important;font-weight:650!important;letter-spacing:.04em;text-transform:uppercase;border-bottom:1px solid #e9ecf1!important}
	.bs-section-row.is-child th{color:#f36b21!important;background:#fff6ef!important}
	.bs-account-no{font-family:Consolas,monospace;font-weight:600;color:#1e2535}
	.bs-account-name{font-weight:550;color:#252832}
	.bs-parent-row .bs-account-name{font-weight:650}
	.bs-child-row td{background:#fcfcfd;color:#4d5059}
	.bs-child-row:hover td{background:#fff8f4!important}
	.bs-account-indent{display:flex;align-items:center;gap:8px}
	.bs-account-indent.indent-1{padding-left:24px}
	.bs-account-indent.indent-2{padding-left:48px}
	.bs-account-indent.indent-3{padding-left:72px}
	.bs-account-indent.indent-4{padding-left:96px}
	.bs-account-indent.indent-5{padding-left:120px}
	.bs-tree-mark{display:inline-block;width:14px;height:1px;background:#d7dbe2}
	.bs-parent-row .bs-tree-mark{display:none}
	.bs-direct-note{margin-left:8px;padding:3px 7px;border-radius:999px;background:#f2f4f7;color:#7d818b;font-size:10px;font-weight:500}
	.bs-amount{text-align:right;font-weight:650}
	.bs-debit{color:#159447}
	.bs-credit{color:#dc3545}
	.bs-total-row td{background:#fafafb!important;font-weight:700}
	.bs-empty{padding:30px!important;text-align:center;color:#8b8d95!important}
	@media(max-width:900px){.bs-hero-content,.bs-toolbar{grid-template-columns:1fr;display:block}.bs-title-wrap{margin-bottom:16px}.bs-stats{justify-content:flex-start;margin-top:15px}.bs-filter,.bs-filter select,.bs-filter input,.bs-filter-btn{width:100%;min-width:100%}}
</style>

<div class="container-fluid bs-page">
	<div class="bs-shell">
		<div class="bs-hero">
			<div class="bs-hero-content">
				<div class="bs-title-wrap">
					<div class="bs-title-icon"><i class="fa fa-file-invoice-dollar"></i></div>
					<div class="bs-title-copy">
						<h2>Balance Sheet</h2>
						<p>Company-wise balance sheet for <?php echo htmlspecialchars($company_name); ?> as of <?php echo date('d M Y',strtotime($as_of_date)); ?>.</p>
					</div>
				</div>
			</div>
		</div>

		<div class="bs-card">
			<div class="bs-toolbar">
				<div class="bs-filters">
					<div class="bs-filter">
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
					<div class="bs-filter">
						<label for="balance_sheet_toDt">As of Date</label>
						<input type="date" class="form-control" id="balance_sheet_toDt" value="<?php echo htmlspecialchars($as_of_date); ?>">
					</div>
					<button type="button" class="bs-filter-btn" id="balance_sheet_filter"><i class="fa fa-filter"></i> Apply</button>
				</div>
				<div class="bs-stats">
					<div class="bs-stat"><span>Debit Balance</span><strong><?php echo number_format($total_debit_balance,2); ?></strong></div>
					<div class="bs-stat"><span>Credit Balance</span><strong><?php echo number_format($total_credit_balance,2); ?></strong></div>
					<div class="bs-stat"><span>Difference</span><strong><?php echo number_format($total_debit_balance - $total_credit_balance,2); ?></strong></div>
				</div>
			</div>

			<div class="bs-table-wrap">
				<div class="bs-table-responsive">
					<table class="table table-bordered bs-table">
						<thead>
							<tr>
								<th style="width:14%">Account No</th>
								<th>Account Name</th>
								<th style="width:18%" class="text-right">Debit</th>
								<th style="width:18%" class="text-right">Credit</th>
							</tr>
						</thead>
						<tbody>
							<?php if(count($balance_sheet_accounts) > 0): ?>
								<?php foreach($balance_sheet_accounts as $section): ?>
									<tr class="bs-section-row <?php echo $section['type_parent_id'] == 0 ? 'is-parent' : 'is-child'; ?>">
										<th colspan="4"><?php echo htmlspecialchars($section['type_name']); ?></th>
									</tr>
									<?php foreach($section['accounts'] as $account): ?>
										<?php balance_sheet_render_account_rows($account); ?>
									<?php endforeach; ?>
								<?php endforeach; ?>
								<tr class="bs-total-row">
									<td colspan="2" class="text-right">Total</td>
									<td class="bs-amount bs-debit"><?php echo number_format($total_debit_balance,2); ?></td>
									<td class="bs-amount bs-credit"><?php echo number_format($total_credit_balance,2); ?></td>
								</tr>
							<?php else: ?>
								<tr><td colspan="4" class="bs-empty">No balance sheet accounts found for this company.</td></tr>
							<?php endif; ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
	$(document).off('change.balanceSheetCompany','#company_id_acc').on('change.balanceSheetCompany','#company_id_acc',function(e){
		e.stopImmediatePropagation();
		$('#balance_sheet_filter').trigger('click');
	});

	$(document).off('click.balanceSheetFilter','#balance_sheet_filter').on('click.balanceSheetFilter','#balance_sheet_filter',function(){
		var comp = $('#company_id_acc').val() || 1;
		var toDt = $('#balance_sheet_toDt').val() || '<?php echo date('Y-m-d'); ?>';
		window.open('index.php?page=Accounting/Balance-Sheet&comp='+encodeURIComponent(comp)+'&toDt='+encodeURIComponent(toDt),'_self');
	});
</script>
