<?php include('db_connect.php');

$module_qry = $conn->query("SELECT m_id FROM modules_1 WHERE m_url = 'Reports/weekly-summary' LIMIT 1");
$module_id = ($module_qry && $module_qry->num_rows > 0) ? (string)$module_qry->fetch_assoc()['m_id'] : "0";
if($module_id == "0" || in_array($module_id,$_SESSION['login_Permisions']))
{
	function ws_date_value($value, $fallback){
		$value = trim((string)$value);
		if($value !== ''){
			$dt = DateTime::createFromFormat('Y-m-d', $value);
			if($dt && $dt->format('Y-m-d') === $value){
				return $value;
			}
		}
		return $fallback;
	}
	$from = ws_date_value($_GET['from'] ?? '', date('Y-m-d', strtotime('-6 days')));
	$to = ws_date_value($_GET['to'] ?? '', date('Y-m-d'));
	function ws_sum($conn,$sql){ $r=$conn->query($sql); return ($r && $r->num_rows>0) ? (float)$r->fetch_assoc()['v'] : 0; }
	$sales = ws_sum($conn,"SELECT COALESCE(SUM(total_job_amount),0) v FROM job_order WHERE del_status=0 AND order_rec_date >= '".$from."' AND order_rec_date <= '".$to."'");
	$purchases = ws_sum($conn,"SELECT COALESCE(SUM(amount),0) v FROM inventoty_received_details WHERE status=0 AND DATE(received_date) >= '".$from."' AND DATE(received_date) <= '".$to."'");
	$customer_payments = ws_sum($conn,"SELECT COALESCE(SUM(amount),0) v FROM customer_payment WHERE pay_status=0 AND payment_date >= '".$from."' AND payment_date <= '".$to."'");
	$supplier_payments = ws_sum($conn,"SELECT COALESCE(SUM(amount),0) v FROM supplier_payment WHERE pay_status=0 AND payment_date >= '".$from."' AND payment_date <= '".$to."'");
	$jobs = ws_sum($conn,"SELECT COUNT(*) v FROM job_order WHERE del_status=0 AND order_rec_date >= '".$from."' AND order_rec_date <= '".$to."'");
	$completed = ws_sum($conn,"SELECT COUNT(*) v FROM job_order WHERE del_status=0 AND order_status=2 AND order_rec_date >= '".$from."' AND order_rec_date <= '".$to."'");
	$message = "ICON CTP Weekly Summary\nPeriod: ".date('d-M-Y',strtotime($from))." to ".date('d-M-Y',strtotime($to))."\n\nSales: Rs. ".number_format($sales,2)."\nPurchases: Rs. ".number_format($purchases,2)."\nCustomer Payments: Rs. ".number_format($customer_payments,2)."\nSupplier Payments: Rs. ".number_format($supplier_payments,2)."\nJobs Received: ".number_format($jobs)."\nCompleted Jobs: ".number_format($completed)."\n\nRegards,\nICON CTP";
	$wa_url = 'https://wa.me/?text='.rawurlencode($message);
	?>
	<div class="container-fluid icon-page-fluid">
		<div class="icon-hero"><div class="icon-hero-row"><div class="icon-title"><span class="icon-title-icon"><i class="fa fa-share-alt"></i></span><div><h1>Weekly Business Summary</h1><p>Sales, purchases, payments and jobs summary for WhatsApp sharing.</p></div></div><a class="icon-btn icon-btn-soft" target="_blank" href="<?php echo htmlspecialchars($wa_url); ?>"><i class="fab fa-whatsapp"></i> Send WhatsApp</a></div></div>
		<div class="icon-card"><form class="icon-toolbar" method="get"><input type="hidden" name="page" value="Reports/weekly-summary"><div class="icon-toolbar-group"><input class="icon-input" type="date" name="from" value="<?php echo htmlspecialchars($from); ?>"><input class="icon-input" type="date" name="to" value="<?php echo htmlspecialchars($to); ?>"><button class="icon-btn icon-btn-primary"><i class="fa fa-filter"></i> Filter</button></div></form><div class="icon-card-body">
			<div class="row view-summary-grid"><div class="col-md-3"><div class="view-summary-card"><span class="view-summary-icon"><i class="fa fa-chart-line"></i></span><div class="view-summary-copy"><h6>Sales</h6><h3><?php echo number_format($sales,0); ?></h3></div></div></div><div class="col-md-3"><div class="view-summary-card"><span class="view-summary-icon"><i class="fa fa-shopping-cart"></i></span><div class="view-summary-copy"><h6>Purchases</h6><h3><?php echo number_format($purchases,0); ?></h3></div></div></div><div class="col-md-3"><div class="view-summary-card is-active"><span class="view-summary-icon"><i class="fa fa-money-bill"></i></span><div class="view-summary-copy"><h6>Collections</h6><h3><?php echo number_format($customer_payments,0); ?></h3></div></div></div><div class="col-md-3"><div class="view-summary-card is-inactive"><span class="view-summary-icon"><i class="fa fa-wallet"></i></span><div class="view-summary-copy"><h6>Supplier Paid</h6><h3><?php echo number_format($supplier_payments,0); ?></h3></div></div></div></div>
			<label><b>WhatsApp Message</b></label><textarea class="form-control" rows="10" readonly><?php echo htmlspecialchars($message); ?></textarea>
		</div></div>
	</div>
	<?php
}else{ include 'accessDenied.php'; }
?>
