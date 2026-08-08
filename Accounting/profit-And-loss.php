<?php include('db_connect.php');

$company_id_acc = isset($_GET['comp']) ? (int)$_GET['comp'] : 1;
if($company_id_acc <= 0){
	$company_id_acc = 1;
}

$from_date = isset($_GET['fromDt']) && $_GET['fromDt'] != '' ? $_GET['fromDt'] : date('Y-m-01');
$to_date = isset($_GET['toDt']) && $_GET['toDt'] != '' ? $_GET['toDt'] : date('Y-m-d');
$from_date_safe = mysqli_real_escape_string($conn,$from_date);
$to_date_safe = mysqli_real_escape_string($conn,$to_date);

$company_name = 'Selected Company';
$company_qry = $conn->query("SELECT comp_name FROM companies WHERE comp_id = ".$company_id_acc." LIMIT 1");
if($company_qry && $company_qry->num_rows > 0){
	$company_row = $company_qry->fetch_assoc();
	$company_name = $company_row['comp_name'];
}

function profit_loss_account_numbers($conn,$account_no,$company_id){
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

	return $account_numbers;
}

function profit_loss_account_balance($conn,$account_no,$company_id,$from_date,$to_date){
	$account_list = implode(',',profit_loss_account_numbers($conn,$account_no,$company_id));
	$balance_qry = $conn->query("SELECT SUM(debit_amount) - SUM(credit_amount) AS balance_amount FROM vouchers WHERE account_id IN (".$account_list.") AND trans_dated >= '".$from_date."' AND trans_dated <= '".$to_date."'");
	if($balance_qry && $balance_qry->num_rows > 0){
		$row = $balance_qry->fetch_assoc();
		return (float)($row['balance_amount'] ?? 0);
	}

	return 0;
}

function profit_loss_account_direct_balance($conn,$account_no,$from_date,$to_date){
	$account_no = (int)$account_no;
	$balance_qry = $conn->query("SELECT SUM(debit_amount) - SUM(credit_amount) AS balance_amount FROM vouchers WHERE account_id = ".$account_no." AND trans_dated >= '".$from_date."' AND trans_dated <= '".$to_date."'");
	if($balance_qry && $balance_qry->num_rows > 0){
		$row = $balance_qry->fetch_assoc();
		return (float)($row['balance_amount'] ?? 0);
	}

	return 0;
}

function profit_loss_child_accounts($conn,$parent_no,$company_id,$from_date,$to_date,$level = 1){
	$children_data = array();
	$children_qry = $conn->query("SELECT account_no,acc_name FROM accounts WHERE parent_id = ".(int)$parent_no." AND company_id = ".(int)$company_id." AND del_status = 0 ORDER BY account_no ASC");

	while($children_qry && $child = $children_qry->fetch_assoc()){
		$balance = profit_loss_account_balance($conn,$child['account_no'],$company_id,$from_date,$to_date);
		$direct_balance = profit_loss_account_direct_balance($conn,$child['account_no'],$from_date,$to_date);
		$children_data[] = array(
			'account_no' => $child['account_no'],
			'acc_name' => $child['acc_name'],
			'balance' => $balance,
			'direct_balance' => $direct_balance,
			'debit_amount' => $balance >= 0 ? $balance : 0,
			'credit_amount' => $balance < 0 ? abs($balance) : 0,
			'level' => $level,
			'children' => profit_loss_child_accounts($conn,$child['account_no'],$company_id,$from_date,$to_date,$level + 1)
		);
	}

	return $children_data;
}

function profit_loss_render_account_rows($account){
	$indent = min((int)($account['level'] ?? 0),5);
	$is_child = $indent > 0;
	$row_class = $is_child ? 'pl-child-row level-'.$indent : 'pl-parent-row';
	$debit = $account['debit_amount'] != 0 ? number_format($account['debit_amount'],2) : '-';
	$credit = $account['credit_amount'] != 0 ? number_format($account['credit_amount'],2) : '-';
	$direct_balance = (float)($account['direct_balance'] ?? 0);
	$direct_note = $is_child && $direct_balance != 0 ? '<span class="pl-direct-note">Direct: '.number_format($direct_balance,2).'</span>' : '';

	echo '<tr class="'.$row_class.'">';
	echo '<td class="pl-account-no">'.htmlspecialchars($account['account_no']).'</td>';
	echo '<td><span class="pl-account-indent indent-'.$indent.'"><span class="pl-tree-mark"></span><span class="pl-account-name">'.htmlspecialchars($account['acc_name']).'</span>'.$direct_note.'</span></td>';
	echo '<td class="pl-amount pl-debit">'.$debit.'</td>';
	echo '<td class="pl-amount pl-credit">'.$credit.'</td>';
	echo '</tr>';

	if(isset($account['children']) && count($account['children']) > 0){
		foreach($account['children'] as $child_account){
			profit_loss_render_account_rows($child_account);
		}
	}
}

$profit_loss_accounts = array();
$total_debit_balance = 0;
$total_credit_balance = 0;

$type_qry = $conn->query("SELECT acc_type_id,type_name,type_parent_id FROM account_types WHERE del_status = 0 ORDER BY type_parent_id ASC, acc_type_id ASC");
while($type_qry && $type = $type_qry->fetch_assoc()){
	$accounts_qry = $conn->query("SELECT account_no,acc_name FROM accounts WHERE acc_type = ".$type['acc_type_id']." AND parent_id = 0 AND company_id = ".$company_id_acc." AND fin_statement = 2 AND del_status = 0 ORDER BY account_no ASC");
	$accounts = array();
	while($accounts_qry && $account = $accounts_qry->fetch_assoc()){
		$balance = profit_loss_account_balance($conn,$account['account_no'],$company_id_acc,$from_date_safe,$to_date_safe);
		$direct_balance = profit_loss_account_direct_balance($conn,$account['account_no'],$from_date_safe,$to_date_safe);
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
			'children' => profit_loss_child_accounts($conn,$account['account_no'],$company_id_acc,$from_date_safe,$to_date_safe,1)
		);
	}

	if(count($accounts) > 0){
		$profit_loss_accounts[] = array(
			'type_name' => $type['type_name'],
			'type_parent_id' => $type['type_parent_id'],
			'accounts' => $accounts
		);
	}
}

$net_profit_loss = $total_credit_balance - $total_debit_balance;
?>

<style>
	.pl-page{padding:0 0 26px}
	.pl-shell{max-width:1240px;margin:0 auto}
	.pl-hero{position:relative;overflow:hidden;margin-bottom:18px;padding:22px 24px;border-radius:20px;background:linear-gradient(135deg,#171923 0%,#282b35 58%,#f36b21 160%);box-shadow:0 18px 45px rgba(20,23,34,.12);color:#fff}
	.pl-hero:before{content:"";position:absolute;right:-70px;top:-80px;width:230px;height:230px;border-radius:50%;background:rgba(243,107,33,.28)}
	.pl-hero-content{position:relative;z-index:1;display:flex;align-items:center;justify-content:space-between;gap:18px}
	.pl-title-wrap{display:flex;align-items:center;gap:14px}
	.pl-title-icon{display:grid;place-items:center;flex:0 0 48px;width:48px;height:48px;border-radius:15px;background:rgba(255,255,255,.12)}
	.pl-title-copy h2{margin:0;font-size:22px;font-weight:650;color:#fff}
	.pl-title-copy p{display:inline-block;margin:8px 0 0;padding:6px 11px;border-radius:999px;background:rgba(0,0,0,.24);box-shadow:inset 0 0 0 1px rgba(255,255,255,.22),0 6px 18px rgba(0,0,0,.12);font-size:13px;font-weight:550;color:#fff!important;text-shadow:0 1px 2px rgba(0,0,0,.35)}
	.pl-card{overflow:hidden;border:1px solid #e7e8ed;border-radius:18px;background:#fff;box-shadow:0 14px 38px rgba(28,31,42,.07)}
	.pl-toolbar{display:grid;grid-template-columns:1fr auto;gap:18px;align-items:center;padding:18px 20px;border-bottom:1px solid #edf0f4;background:linear-gradient(180deg,#fff,#fbfbfc)}
	.pl-filters{display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end}
	.pl-filter label{display:block;margin:0 0 7px;font-size:11px;font-weight:650;letter-spacing:.08em;text-transform:uppercase;color:#8b8d95}
	.pl-filter select,.pl-filter input{height:43px!important;min-width:190px;border:1px solid #dfe3ea!important;border-radius:12px!important;padding:8px 12px;font-size:13px;color:#30323a;background:#fff}
	.pl-filter-btn{height:43px;padding:0 16px;border:0;border-radius:12px;background:#f36b21;color:#fff;font-size:12px;font-weight:650;box-shadow:0 10px 22px rgba(243,107,33,.2)}
	.pl-stats{display:flex;gap:10px;flex-wrap:wrap;justify-content:flex-end}
	.pl-stat{min-width:138px;padding:11px 13px;border:1px solid #edf0f4;border-radius:14px;background:#fff}
	.pl-stat span{display:block;font-size:10px;font-weight:650;letter-spacing:.07em;text-transform:uppercase;color:#9698a1}
	.pl-stat strong{display:block;margin-top:5px;font-size:19px;line-height:1;font-weight:650;color:#22242b}
	.pl-stat.net-profit strong{color:#159447}.pl-stat.net-loss strong{color:#dc3545}
	.pl-table-wrap{padding:18px;background:#fff}
	.pl-table-responsive{overflow:auto;border:1px solid #edf0f4;border-radius:15px}
	.pl-table{width:100%;margin:0!important;border:0!important;border-collapse:separate;border-spacing:0;background:#fff}
	.pl-table thead th{position:sticky;top:0;z-index:2;padding:13px 14px!important;border:0!important;border-bottom:1px solid #e8ebf1!important;background:#f7f8fa!important;color:#676a73;font-size:10px;font-weight:650;letter-spacing:.08em;text-transform:uppercase;white-space:nowrap}
	.pl-table tbody td,.pl-table tbody th{padding:12px 14px!important;border:0!important;border-bottom:1px solid #f0f1f4!important;vertical-align:middle!important;font-size:13px;color:#343741}
	.pl-table tbody tr:hover td{background:#fff8f4}
	.pl-section-row th{padding:14px 16px!important;background:#f8f9fb!important;color:#1f2330!important;font-size:12px!important;font-weight:650!important;letter-spacing:.04em;text-transform:uppercase;border-bottom:1px solid #e9ecf1!important}
	.pl-section-row.is-child th{color:#f36b21!important;background:#fff6ef!important}
	.pl-account-no{font-family:Consolas,monospace;font-weight:600;color:#1e2535}
	.pl-account-name{font-weight:550;color:#252832}
	.pl-parent-row .pl-account-name{font-weight:650}
	.pl-child-row td{background:#fcfcfd;color:#4d5059}
	.pl-child-row:hover td{background:#fff8f4!important}
	.pl-account-indent{display:flex;align-items:center;gap:8px}
	.pl-account-indent.indent-1{padding-left:24px}.pl-account-indent.indent-2{padding-left:48px}.pl-account-indent.indent-3{padding-left:72px}.pl-account-indent.indent-4{padding-left:96px}.pl-account-indent.indent-5{padding-left:120px}
	.pl-tree-mark{display:inline-block;width:14px;height:1px;background:#d7dbe2}.pl-parent-row .pl-tree-mark{display:none}
	.pl-direct-note{margin-left:8px;padding:3px 7px;border-radius:999px;background:#f2f4f7;color:#7d818b;font-size:10px;font-weight:500}
	.pl-amount{text-align:right;font-weight:650}.pl-debit{color:#dc3545}.pl-credit{color:#159447}
	.pl-total-row td{background:#fafafb!important;font-weight:700}
	.pl-net-row td{background:#fff8f4!important;font-weight:700}
	.pl-empty{padding:30px!important;text-align:center;color:#8b8d95!important}
	@media(max-width:900px){.pl-hero-content,.pl-toolbar{grid-template-columns:1fr;display:block}.pl-title-wrap{margin-bottom:16px}.pl-stats{justify-content:flex-start;margin-top:15px}.pl-filter,.pl-filter select,.pl-filter input,.pl-filter-btn{width:100%;min-width:100%}}
</style>

<div class="container-fluid pl-page">
	<div class="pl-shell">
		<div class="pl-hero">
			<div class="pl-hero-content">
				<div class="pl-title-wrap">
					<div class="pl-title-icon"><i class="fa fa-chart-line"></i></div>
					<div class="pl-title-copy">
						<h2>Profit &amp; Loss</h2>
						<p>Company-wise P&amp;L for <?php echo htmlspecialchars($company_name); ?> from <?php echo date('d M Y',strtotime($from_date)); ?> to <?php echo date('d M Y',strtotime($to_date)); ?>.</p>
					</div>
				</div>
			</div>
		</div>

		<div class="pl-card">
			<div class="pl-toolbar">
				<div class="pl-filters">
					<div class="pl-filter">
						<label for="company_id_acc">Company</label>
						<select class="form-control" name="company_id_acc" id="company_id_acc" required="true">
							<option value="">Select Company</option>
							<?php
							$query_comp = "SELECT * FROM companies";
							$result_comp = mysqli_query($conn,$query_comp);
							while($data_comp = mysqli_fetch_array($result_comp)){
								$selected_val = $company_id_acc == $data_comp['comp_id'] ? "selected" : "";
								?>
								<option <?= $selected_val ?> value="<?= $data_comp['comp_id'] ?>"><?= $data_comp['comp_name'] ?></option>
								<?php
							}
							?>
						</select>
					</div>
					<div class="pl-filter"><label for="profit_loss_fromDt">From Date</label><input type="date" class="form-control" id="profit_loss_fromDt" value="<?php echo htmlspecialchars($from_date); ?>"></div>
					<div class="pl-filter"><label for="profit_loss_toDt">To Date</label><input type="date" class="form-control" id="profit_loss_toDt" value="<?php echo htmlspecialchars($to_date); ?>"></div>
					<button type="button" class="pl-filter-btn" id="profit_loss_filter"><i class="fa fa-filter"></i> Apply</button>
				</div>
				<div class="pl-stats">
					<div class="pl-stat"><span>Total Debit</span><strong><?php echo number_format($total_debit_balance,2); ?></strong></div>
					<div class="pl-stat"><span>Total Credit</span><strong><?php echo number_format($total_credit_balance,2); ?></strong></div>
					<div class="pl-stat <?php echo $net_profit_loss >= 0 ? 'net-profit' : 'net-loss'; ?>"><span><?php echo $net_profit_loss >= 0 ? 'Net Profit' : 'Net Loss'; ?></span><strong><?php echo number_format(abs($net_profit_loss),2); ?></strong></div>
				</div>
			</div>

			<div class="pl-table-wrap">
				<div class="pl-table-responsive">
					<table class="table table-bordered pl-table">
						<thead><tr><th style="width:14%">Account No</th><th>Account Name</th><th style="width:18%" class="text-right">Debit</th><th style="width:18%" class="text-right">Credit</th></tr></thead>
						<tbody>
							<?php if(count($profit_loss_accounts) > 0): ?>
								<?php foreach($profit_loss_accounts as $section): ?>
									<tr class="pl-section-row <?php echo $section['type_parent_id'] == 0 ? 'is-parent' : 'is-child'; ?>"><th colspan="4"><?php echo htmlspecialchars($section['type_name']); ?></th></tr>
									<?php foreach($section['accounts'] as $account): ?>
										<?php profit_loss_render_account_rows($account); ?>
									<?php endforeach; ?>
								<?php endforeach; ?>
								<tr class="pl-total-row"><td colspan="2" class="text-right">Total</td><td class="pl-amount pl-debit"><?php echo number_format($total_debit_balance,2); ?></td><td class="pl-amount pl-credit"><?php echo number_format($total_credit_balance,2); ?></td></tr>
								<tr class="pl-net-row"><td colspan="2" class="text-right"><?php echo $net_profit_loss >= 0 ? 'Net Profit' : 'Net Loss'; ?></td><td colspan="2" class="pl-amount <?php echo $net_profit_loss >= 0 ? 'pl-credit' : 'pl-debit'; ?>"><?php echo number_format(abs($net_profit_loss),2); ?></td></tr>
							<?php else: ?>
								<tr><td colspan="4" class="pl-empty">No profit and loss accounts found for this company/date range.</td></tr>
							<?php endif; ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
	$(document).off('change.profitLossCompany','#company_id_acc').on('change.profitLossCompany','#company_id_acc',function(e){
		e.stopImmediatePropagation();
		$('#profit_loss_filter').trigger('click');
	});
	$(document).off('click.profitLossFilter','#profit_loss_filter').on('click.profitLossFilter','#profit_loss_filter',function(){
		var comp = $('#company_id_acc').val() || 1;
		var fromDt = $('#profit_loss_fromDt').val() || '<?php echo date('Y-m-01'); ?>';
		var toDt = $('#profit_loss_toDt').val() || '<?php echo date('Y-m-d'); ?>';
		window.open('index.php?page=Accounting/profit-And-loss&comp='+encodeURIComponent(comp)+'&fromDt='+encodeURIComponent(fromDt)+'&toDt='+encodeURIComponent(toDt),'_self');
	});
</script>
