<?php include('db_connect.php');

$module_qry = $conn->query("SELECT m_id FROM modules_1 WHERE m_url = 'Accounting/accounting-audit' LIMIT 1");
$module_id = ($module_qry && $module_qry->num_rows > 0) ? (string)$module_qry->fetch_assoc()['m_id'] : "0";
if($module_id == "0" || in_array($module_id,$_SESSION['login_Permisions']))
{
	function aa_safe($value){ return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
	function aa_count($conn,$sql){ $r=$conn->query($sql); return ($r && $r->num_rows>0) ? (int)$r->fetch_assoc()['c'] : 0; }
	function aa_rows($conn,$sql,$limit=100){ $rows=array(); $r=$conn->query($sql." LIMIT ".(int)$limit); while($r && $row=$r->fetch_assoc()){ $rows[]=$row; } return $rows; }

	$unbalanced_count = aa_count($conn,"SELECT COUNT(*) c FROM (SELECT voucher_no,v_type_id FROM vouchers WHERE cancel_flag=0 GROUP BY voucher_no,v_type_id HAVING ROUND(SUM(debit_amount-credit_amount),2)<>0) x");
	$orphan_count = aa_count($conn,"SELECT COUNT(*) c FROM vouchers v LEFT JOIN accounts a ON a.account_no=v.account_id WHERE v.cancel_flag=0 AND a.account_no IS NULL");
	$customer_missing = aa_count($conn,"SELECT COUNT(*) c FROM customers c LEFT JOIN accounts a ON a.account_no=c.acc_id WHERE c.acc_id=0 OR a.account_no IS NULL");
	$supplier_missing = aa_count($conn,"SELECT COUNT(*) c FROM suppliers s LEFT JOIN accounts a ON a.account_no=s.acc_id WHERE s.acc_id=0 OR a.account_no IS NULL");
	$duplicate_accounts = aa_count($conn,"SELECT COUNT(*) c FROM (SELECT account_no FROM accounts WHERE del_status=0 GROUP BY account_no HAVING COUNT(*)>1) x");

	$unbalanced = aa_rows($conn,"SELECT voucher_no,v_type_id,MIN(trans_dated) AS trans_dated,COUNT(*) AS rows_count,SUM(debit_amount) AS debit,SUM(credit_amount) AS credit,ROUND(SUM(debit_amount-credit_amount),2) AS difference,MAX(ref_column) AS ref_column,MAX(ref_id) AS ref_id FROM vouchers WHERE cancel_flag=0 GROUP BY voucher_no,v_type_id HAVING ROUND(SUM(debit_amount-credit_amount),2)<>0 ORDER BY voucher_no DESC");
	$orphans = aa_rows($conn,"SELECT v.id,v.voucher_no,v.v_type_id,v.trans_dated,v.account_id,v.debit_amount,v.credit_amount,v.ref_column,v.ref_id FROM vouchers v LEFT JOIN accounts a ON a.account_no=v.account_id WHERE v.cancel_flag=0 AND a.account_no IS NULL ORDER BY v.voucher_no DESC");
	$customers = aa_rows($conn,"SELECT c.cust_id,c.cust_name,c.acc_id FROM customers c LEFT JOIN accounts a ON a.account_no=c.acc_id WHERE c.acc_id=0 OR a.account_no IS NULL ORDER BY c.cust_name");
	$suppliers = aa_rows($conn,"SELECT s.supp_id,s.supp_name,s.acc_id FROM suppliers s LEFT JOIN accounts a ON a.account_no=s.acc_id WHERE s.acc_id=0 OR a.account_no IS NULL ORDER BY s.supp_name");
	?>
	<div class="container-fluid icon-page-fluid">
		<div class="icon-hero"><div class="icon-hero-row"><div class="icon-title"><span class="icon-title-icon"><i class="fa fa-balance-scale"></i></span><div><h1>Accounting Audit</h1><p>Detailed review for voucher balance and account binding issues.</p></div></div></div></div>
		<div class="row view-summary-grid">
			<div class="col-md-3"><div class="view-summary-card <?php echo $unbalanced_count>0?'is-inactive':'is-active'; ?>"><span class="view-summary-icon"><i class="fa fa-balance-scale"></i></span><div class="view-summary-copy"><h6>Unbalanced Vouchers</h6><h3><?php echo number_format($unbalanced_count); ?></h3></div></div></div>
			<div class="col-md-3"><div class="view-summary-card <?php echo $orphan_count>0?'is-inactive':'is-active'; ?>"><span class="view-summary-icon"><i class="fa fa-unlink"></i></span><div class="view-summary-copy"><h6>Voucher Orphans</h6><h3><?php echo number_format($orphan_count); ?></h3></div></div></div>
			<div class="col-md-3"><div class="view-summary-card <?php echo ($customer_missing+$supplier_missing)>0?'is-inactive':'is-active'; ?>"><span class="view-summary-icon"><i class="fa fa-users"></i></span><div class="view-summary-copy"><h6>Missing Party Accounts</h6><h3><?php echo number_format($customer_missing+$supplier_missing); ?></h3></div></div></div>
			<div class="col-md-3"><div class="view-summary-card <?php echo $duplicate_accounts>0?'is-inactive':'is-active'; ?>"><span class="view-summary-icon"><i class="fa fa-copy"></i></span><div class="view-summary-copy"><h6>Duplicate Accounts</h6><h3><?php echo number_format($duplicate_accounts); ?></h3></div></div></div>
		</div>

		<div class="icon-card">
			<div class="icon-card-header"><div class="icon-card-title"><span><i class="fa fa-exclamation-triangle"></i></span><div><h3>Unbalanced Vouchers</h3><p>Debit and credit totals should always match.</p></div></div></div>
			<div class="icon-card-body"><div class="icon-table-wrap"><table class="icon-table table audit-table"><thead><tr><th>Voucher</th><th>Type</th><th>Date</th><th>Rows</th><th>Debit</th><th>Credit</th><th>Diff</th><th>Reference</th></tr></thead><tbody>
				<?php foreach($unbalanced as $row): ?><tr><td><?php echo (int)$row['voucher_no']; ?></td><td><?php echo (int)$row['v_type_id']; ?></td><td><?php echo aa_safe($row['trans_dated']); ?></td><td><?php echo (int)$row['rows_count']; ?></td><td><?php echo number_format($row['debit']); ?></td><td><?php echo number_format($row['credit']); ?></td><td><?php echo number_format($row['difference']); ?></td><td><?php echo aa_safe($row['ref_column'].' #'.$row['ref_id']); ?></td></tr><?php endforeach; ?>
				<?php if(count($unbalanced)==0): ?><tr><td colspan="8" class="text-center text-muted">No unbalanced voucher found.</td></tr><?php endif; ?>
			</tbody></table></div></div>
		</div>

		<div class="icon-card">
			<div class="icon-card-header"><div class="icon-card-title"><span><i class="fa fa-unlink"></i></span><div><h3>Voucher Rows With Missing Account</h3><p>These voucher rows point to account numbers not present in chart of accounts.</p></div></div></div>
			<div class="icon-card-body"><div class="icon-table-wrap"><table class="icon-table table audit-table"><thead><tr><th>Voucher</th><th>Type</th><th>Date</th><th>Account</th><th>Debit</th><th>Credit</th><th>Reference</th></tr></thead><tbody>
				<?php foreach($orphans as $row): ?><tr><td><?php echo (int)$row['voucher_no']; ?></td><td><?php echo (int)$row['v_type_id']; ?></td><td><?php echo aa_safe($row['trans_dated']); ?></td><td><?php echo (int)$row['account_id']; ?></td><td><?php echo number_format($row['debit_amount']); ?></td><td><?php echo number_format($row['credit_amount']); ?></td><td><?php echo aa_safe($row['ref_column'].' #'.$row['ref_id']); ?></td></tr><?php endforeach; ?>
				<?php if(count($orphans)==0): ?><tr><td colspan="7" class="text-center text-muted">No orphan voucher account found.</td></tr><?php endif; ?>
			</tbody></table></div></div>
		</div>

		<div class="row">
			<div class="col-md-6"><div class="icon-card"><div class="icon-card-header"><div class="icon-card-title"><span><i class="fa fa-user"></i></span><div><h3>Customers Without Valid Account</h3><p>Customer account binding missing or invalid.</p></div></div></div><div class="icon-card-body"><div class="icon-table-wrap"><table class="icon-table table audit-table"><thead><tr><th>ID</th><th>Customer</th><th>Account</th></tr></thead><tbody><?php foreach($customers as $row): ?><tr><td><?php echo (int)$row['cust_id']; ?></td><td><?php echo aa_safe($row['cust_name']); ?></td><td><?php echo (int)$row['acc_id']; ?></td></tr><?php endforeach; ?><?php if(count($customers)==0): ?><tr><td colspan="3" class="text-center text-muted">All customers have valid accounts.</td></tr><?php endif; ?></tbody></table></div></div></div></div>
			<div class="col-md-6"><div class="icon-card"><div class="icon-card-header"><div class="icon-card-title"><span><i class="fa fa-truck"></i></span><div><h3>Suppliers Without Valid Account</h3><p>Supplier account binding missing or invalid.</p></div></div></div><div class="icon-card-body"><div class="icon-table-wrap"><table class="icon-table table audit-table"><thead><tr><th>ID</th><th>Supplier</th><th>Account</th></tr></thead><tbody><?php foreach($suppliers as $row): ?><tr><td><?php echo (int)$row['supp_id']; ?></td><td><?php echo aa_safe($row['supp_name']); ?></td><td><?php echo (int)$row['acc_id']; ?></td></tr><?php endforeach; ?><?php if(count($suppliers)==0): ?><tr><td colspan="3" class="text-center text-muted">All suppliers have valid accounts.</td></tr><?php endif; ?></tbody></table></div></div></div></div>
		</div>
	</div>
	<script>$(function(){ $('.audit-table').DataTable({pageLength:25,order:[]}); });</script>
	<?php
}else{ include 'accessDenied.php'; }
?>
