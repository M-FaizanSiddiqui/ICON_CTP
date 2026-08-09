<?php include('db_connect.php');

$module_qry = $conn->query("SELECT m_id FROM modules_1 WHERE m_url = 'SystemUser/data-health' LIMIT 1");
$module_id = ($module_qry && $module_qry->num_rows > 0) ? (string)$module_qry->fetch_assoc()['m_id'] : "0";
if($module_id == "0" || in_array($module_id,$_SESSION['login_Permisions']))
{
	function dh_count($conn,$sql){ $r=$conn->query($sql); return ($r && $r->num_rows>0) ? (int)$r->fetch_assoc()['c'] : 0; }
	function dh_safe($value){ return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
	function dh_rows($conn,$sql,$limit=25){
		$rows = array();
		$r = $conn->query($sql." LIMIT ".(int)$limit);
		while($r && $row=$r->fetch_assoc()){ $rows[] = $row; }
		return $rows;
	}

	$own_mismatch_sql = "SELECT COUNT(*) c FROM inventory_item i LEFT JOIN (SELECT d.item_id, SUM(d.quantity) AS booked_qty FROM job_order_details d INNER JOIN job_order j ON j.jd_id=d.job_id WHERE d.delete_status=0 AND j.del_status=0 AND j.order_status<>2 AND j.job_effect<>1 GROUP BY d.item_id) b ON b.item_id=i.item_id WHERE i.qty_booked<>COALESCE(b.booked_qty,0)";
	$cust_mismatch_sql = "SELECT COUNT(*) c FROM customer_inventory ci LEFT JOIN (SELECT j.customer_id, d.item_id, SUM(d.quantity) AS booked_qty FROM job_order_details d INNER JOIN job_order j ON j.jd_id=d.job_id WHERE d.delete_status=0 AND j.del_status=0 AND j.order_status<>2 AND j.job_effect=1 GROUP BY j.customer_id,d.item_id) b ON b.customer_id=ci.cust_id AND b.item_id=ci.plate_id WHERE ci.del_status=0 AND ci.qty_booked<>COALESCE(b.booked_qty,0)";
	$checks = array(
		array('Own Inventory Booked Mismatch','qty_booked differs from active non-completed jobs',$own_mismatch_sql,'danger','own-booked'),
		array('Customer Inventory Booked Mismatch','customer qty_booked differs from active non-completed customer jobs',$cust_mismatch_sql,'danger','cust-booked'),
		array('Inventory Negative Booked','Booked quantity below zero','SELECT COUNT(*) c FROM inventory_item WHERE qty_booked < 0','danger','negative-own'),
		array('Customer Inventory Negative Booked','Customer booked quantity below zero','SELECT COUNT(*) c FROM customer_inventory WHERE qty_booked < 0','danger','negative-cust'),
		array('Unbalanced Vouchers','Voucher debit/credit mismatch','SELECT COUNT(*) c FROM (SELECT voucher_no FROM vouchers WHERE cancel_flag=0 GROUP BY voucher_no HAVING ROUND(SUM(debit_amount-credit_amount),2)<>0) x','danger','vouchers'),
		array('Customers Without Account','Customer account binding missing','SELECT COUNT(*) c FROM customers WHERE acc_id=0 OR acc_id NOT IN (SELECT account_no FROM accounts)','warning','customers'),
		array('Suppliers Without Account','Supplier account binding missing','SELECT COUNT(*) c FROM suppliers WHERE acc_id=0 OR acc_id NOT IN (SELECT account_no FROM accounts)','warning','suppliers'),
		array('Jobs Without Details','Job cards with no active detail rows','SELECT COUNT(*) c FROM (SELECT j.jd_id FROM job_order j LEFT JOIN job_order_details d ON j.jd_id=d.job_id AND d.delete_status=0 WHERE j.del_status=0 GROUP BY j.jd_id HAVING COUNT(d.id)=0) x','warning','jobs')
	);
	$own_rows = dh_rows($conn,"SELECT i.item_id,i.item_name,i.qty_booked,COALESCE(b.booked_qty,0) AS calculated_booked,(i.qty_booked-COALESCE(b.booked_qty,0)) AS difference FROM inventory_item i LEFT JOIN (SELECT d.item_id, SUM(d.quantity) AS booked_qty FROM job_order_details d INNER JOIN job_order j ON j.jd_id=d.job_id WHERE d.delete_status=0 AND j.del_status=0 AND j.order_status<>2 AND j.job_effect<>1 GROUP BY d.item_id) b ON b.item_id=i.item_id WHERE i.qty_booked<>COALESCE(b.booked_qty,0) ORDER BY ABS(i.qty_booked-COALESCE(b.booked_qty,0)) DESC");
	$cust_rows = dh_rows($conn,"SELECT ci.ci_id,c.cust_name,i.item_name,ci.qty_booked,COALESCE(b.booked_qty,0) AS calculated_booked,(ci.qty_booked-COALESCE(b.booked_qty,0)) AS difference FROM customer_inventory ci LEFT JOIN customers c ON c.cust_id=ci.cust_id LEFT JOIN inventory_item i ON i.item_id=ci.plate_id LEFT JOIN (SELECT j.customer_id, d.item_id, SUM(d.quantity) AS booked_qty FROM job_order_details d INNER JOIN job_order j ON j.jd_id=d.job_id WHERE d.delete_status=0 AND j.del_status=0 AND j.order_status<>2 AND j.job_effect=1 GROUP BY j.customer_id,d.item_id) b ON b.customer_id=ci.cust_id AND b.item_id=ci.plate_id WHERE ci.del_status=0 AND ci.qty_booked<>COALESCE(b.booked_qty,0) ORDER BY ABS(ci.qty_booked-COALESCE(b.booked_qty,0)) DESC");
	$voucher_rows = dh_rows($conn,"SELECT voucher_no,v_type_id,MIN(trans_dated) AS trans_dated,SUM(debit_amount) AS debit,SUM(credit_amount) AS credit,ROUND(SUM(debit_amount-credit_amount),2) AS difference FROM vouchers WHERE cancel_flag=0 GROUP BY voucher_no,v_type_id HAVING ROUND(SUM(debit_amount-credit_amount),2)<>0 ORDER BY voucher_no DESC");
	?>
	<div class="container-fluid icon-page-fluid">
		<div class="icon-hero"><div class="icon-hero-row"><div class="icon-title"><span class="icon-title-icon"><i class="fa fa-heartbeat"></i></span><div><h1>Data Health Center</h1><p>Audit and safely repair inventory booking mismatches.</p></div></div></div></div>
		<div class="icon-card">
			<div class="icon-card-header"><div class="icon-card-title"><span><i class="fa fa-stethoscope"></i></span><div><h3>Health Checks</h3><p>Counts above zero need review. Repair buttons only recalculate booked quantity from active jobs.</p></div></div></div>
			<div class="icon-card-body">
				<div class="icon-table-wrap"><table class="icon-table table" id="health-table"><thead><tr><th>Check</th><th>Description</th><th>Status</th><th>Count</th></tr></thead><tbody>
					<?php foreach($checks as $check): $count = dh_count($conn,$check[2]); $ok=$count===0; ?>
						<tr><td><?php echo dh_safe($check[0]); ?></td><td><?php echo dh_safe($check[1]); ?></td><td><span class="icon-badge <?php echo $ok?'success':''; ?>"><?php echo $ok?'OK':'Review'; ?></span></td><td><b><?php echo number_format($count); ?></b></td></tr>
					<?php endforeach; ?>
				</tbody></table></div>
			</div>
		</div>

		<div class="row">
			<div class="col-md-6">
				<div class="icon-card">
					<div class="icon-card-header"><div class="icon-card-title"><span><i class="fa fa-boxes"></i></span><div><h3>Own Inventory Booked Repair</h3><p>Preview top mismatched own stock rows.</p></div></div><button class="icon-btn icon-btn-primary repair-health" data-action="repair_inventory_booked"><i class="fa fa-wrench"></i> Recalculate</button></div>
					<div class="icon-card-body"><div class="icon-table-wrap"><table class="icon-table table health-detail"><thead><tr><th>Item</th><th>Current</th><th>Calculated</th><th>Diff</th></tr></thead><tbody>
						<?php foreach($own_rows as $row): ?><tr><td><?php echo dh_safe($row['item_name']); ?></td><td><?php echo number_format($row['qty_booked']); ?></td><td><?php echo number_format($row['calculated_booked']); ?></td><td><?php echo number_format($row['difference']); ?></td></tr><?php endforeach; ?>
						<?php if(count($own_rows)==0): ?><tr><td colspan="4" class="text-center text-muted">No mismatch found.</td></tr><?php endif; ?>
					</tbody></table></div></div>
				</div>
			</div>
			<div class="col-md-6">
				<div class="icon-card">
					<div class="icon-card-header"><div class="icon-card-title"><span><i class="fa fa-user-tag"></i></span><div><h3>Customer Inventory Booked Repair</h3><p>Preview top mismatched customer stock rows.</p></div></div><button class="icon-btn icon-btn-primary repair-health" data-action="repair_customer_inventory_booked"><i class="fa fa-wrench"></i> Recalculate</button></div>
					<div class="icon-card-body"><div class="icon-table-wrap"><table class="icon-table table health-detail"><thead><tr><th>Customer</th><th>Item</th><th>Current</th><th>Calculated</th><th>Diff</th></tr></thead><tbody>
						<?php foreach($cust_rows as $row): ?><tr><td><?php echo dh_safe($row['cust_name']); ?></td><td><?php echo dh_safe($row['item_name']); ?></td><td><?php echo number_format($row['qty_booked']); ?></td><td><?php echo number_format($row['calculated_booked']); ?></td><td><?php echo number_format($row['difference']); ?></td></tr><?php endforeach; ?>
						<?php if(count($cust_rows)==0): ?><tr><td colspan="5" class="text-center text-muted">No mismatch found.</td></tr><?php endif; ?>
					</tbody></table></div></div>
				</div>
			</div>
		</div>

		<div class="icon-card">
			<div class="icon-card-header"><div class="icon-card-title"><span><i class="fa fa-balance-scale"></i></span><div><h3>Unbalanced Voucher Preview</h3><p>Accounting differences are shown here for review. Use Accounting Audit for full details.</p></div></div><a class="icon-btn icon-btn-soft" href="index.php?page=Accounting/accounting-audit"><i class="fa fa-search"></i> Open Audit</a></div>
			<div class="icon-card-body"><div class="icon-table-wrap"><table class="icon-table table health-detail"><thead><tr><th>Voucher</th><th>Type</th><th>Date</th><th>Debit</th><th>Credit</th><th>Diff</th></tr></thead><tbody>
				<?php foreach($voucher_rows as $row): ?><tr><td><?php echo (int)$row['voucher_no']; ?></td><td><?php echo (int)$row['v_type_id']; ?></td><td><?php echo dh_safe($row['trans_dated']); ?></td><td><?php echo number_format($row['debit']); ?></td><td><?php echo number_format($row['credit']); ?></td><td><?php echo number_format($row['difference']); ?></td></tr><?php endforeach; ?>
				<?php if(count($voucher_rows)==0): ?><tr><td colspan="6" class="text-center text-muted">No unbalanced vouchers found.</td></tr><?php endif; ?>
			</tbody></table></div></div>
		</div>
	</div>
	<script>
	$(function(){
		$('#health-table').DataTable({paging:false,searching:false,info:false});
		$('.health-detail').DataTable({pageLength:10,order:[]});
		$('.repair-health').click(function(){
			var action = $(this).data('action');
			if(!confirm('Recalculate booked quantity from active non-completed jobs?')) return;
			start_load();
			$.post('ajax.php?action='+action,{},function(resp){
				var parts = String(resp).split('|');
				if(parts[0] === '1'){
					alert_toast('Repair complete. Rows corrected: '+(parts[1] || '0'),'success');
					setTimeout(function(){ location.reload(); },900);
				}else{
					end_load();
					alert_toast(resp,'danger');
				}
			});
		});
	});
	</script>
	<?php
}else{ include 'accessDenied.php'; }
?>
