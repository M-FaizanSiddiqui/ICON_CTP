<?php include('db_connect.php');

$module_qry = $conn->query("SELECT m_id FROM modules_1 WHERE m_url = 'SystemUser/data-health' LIMIT 1");
$module_id = ($module_qry && $module_qry->num_rows > 0) ? (string)$module_qry->fetch_assoc()['m_id'] : "0";
if($module_id == "0" || in_array($module_id,$_SESSION['login_Permisions']))
{
	function dh_count($conn,$sql){ $r=$conn->query($sql); return ($r && $r->num_rows>0) ? (int)$r->fetch_assoc()['c'] : 0; }
	$checks = array(
		array('Inventory Negative Booked','Booked quantity below zero','SELECT COUNT(*) c FROM inventory_item WHERE qty_booked < 0','danger'),
		array('Customer Inventory Negative Booked','Customer booked quantity below zero','SELECT COUNT(*) c FROM customer_inventory WHERE qty_booked < 0','danger'),
		array('Unbalanced Vouchers','Voucher debit/credit mismatch','SELECT COUNT(*) c FROM (SELECT voucher_no FROM vouchers WHERE cancel_flag=0 GROUP BY voucher_no HAVING ROUND(SUM(debit_amount-credit_amount),2)<>0) x','danger'),
		array('Customers Without Account','Customer account binding missing','SELECT COUNT(*) c FROM customers WHERE acc_id=0 OR acc_id NOT IN (SELECT account_no FROM accounts)','warning'),
		array('Suppliers Without Account','Supplier account binding missing','SELECT COUNT(*) c FROM suppliers WHERE acc_id=0 OR acc_id NOT IN (SELECT account_no FROM accounts)','warning'),
		array('Jobs Without Details','Job cards with no active detail rows','SELECT COUNT(*) c FROM (SELECT j.jd_id FROM job_order j LEFT JOIN job_order_details d ON j.jd_id=d.job_id AND d.delete_status=0 WHERE j.del_status=0 GROUP BY j.jd_id HAVING COUNT(d.id)=0) x','warning'),
		array('Missing Module Files','Menu/module URLs with missing PHP file','MANUAL','warning')
	);
	$missing_files = 0;
	$mods = $conn->query("SELECT m_url FROM modules_1 WHERE m_url<>''");
	while($mods && $m=$mods->fetch_assoc()){
		$url = $m['m_url'];
		$path = __DIR__.'/../'.$url.(substr($url,-4)==='.php'?'':'.php');
		if(!file_exists($path)){ $missing_files++; }
	}
	?>
	<div class="container-fluid icon-page-fluid">
		<div class="icon-hero"><div class="icon-hero-row"><div class="icon-title"><span class="icon-title-icon"><i class="fa fa-heartbeat"></i></span><div><h1>Data Health Center</h1><p>Quick checks for accounting, inventory, accounts and module integrity.</p></div></div></div></div>
		<div class="icon-card"><div class="icon-card-header"><div class="icon-card-title"><span><i class="fa fa-stethoscope"></i></span><div><h3>Health Checks</h3><p>Counts above zero need review.</p></div></div></div><div class="icon-card-body"><div class="icon-table-wrap"><table class="icon-table table" id="health-table"><thead><tr><th>Check</th><th>Description</th><th>Status</th><th>Count</th></tr></thead><tbody>
			<?php foreach($checks as $check): $count = $check[2]==='MANUAL' ? $missing_files : dh_count($conn,$check[2]); $ok=$count===0; ?>
				<tr><td><?php echo htmlspecialchars($check[0]); ?></td><td><?php echo htmlspecialchars($check[1]); ?></td><td><span class="icon-badge <?php echo $ok?'success':''; ?>"><?php echo $ok?'OK':'Review'; ?></span></td><td><b><?php echo number_format($count); ?></b></td></tr>
			<?php endforeach; ?>
		</tbody></table></div></div></div>
	</div>
	<script>$(function(){ $('#health-table').DataTable({paging:false,searching:false,info:false}); });</script>
	<?php
}else{ include 'accessDenied.php'; }
?>
