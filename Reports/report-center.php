<?php include('db_connect.php');

$module_qry = $conn->query("SELECT m_id FROM modules_1 WHERE m_url = 'Reports/report-center' LIMIT 1");
$report_center_module_id = ($module_qry && $module_qry->num_rows > 0) ? (string)$module_qry->fetch_assoc()['m_id'] : "0";

if($report_center_module_id == "0" || in_array($report_center_module_id,$_SESSION['login_Permisions']))
{
	$reports = array(
		array('title'=>'Sales Report','desc'=>'Customer-wise job sales for selected period.','icon'=>'fa-chart-line','url'=>'Reports/Sales-Report.php','permission'=>'43','fields'=>array('date')),
		array('title'=>'Payments Receive Report','desc'=>'Customer collections received in selected period.','icon'=>'fa-money-bill-wave','url'=>'Reports/Payments-Receive-Report.php','permission'=>'44','fields'=>array('date')),
		array('title'=>'Payable Summary','desc'=>'Supplier payable summary.','icon'=>'fa-truck','url'=>'Reports/Payable-Summary.php','permission'=>'45','fields'=>array()),
		array('title'=>'Receivable Summary','desc'=>'Customer receivable summary.','icon'=>'fa-hand-holding-dollar','url'=>'Reports/Receivable-Summary.php','permission'=>'46','fields'=>array()),
		array('title'=>'Monthly Bill','desc'=>'Customer monthly billing detail.','icon'=>'fa-file-invoice','url'=>'Reports/Monthly-Bill.php','permission'=>'48','fields'=>array('date','customer')),
		array('title'=>'Inventory Purchase Report','desc'=>'Inventory purchases by supplier and item.','icon'=>'fa-boxes-stacked','url'=>'Reports/Inv-Purchases-Rpt.php','permission'=>'74','fields'=>array('date'))
	);
	function report_center_safe($value){ return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
	?>
	<style>
		.report-center-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(310px,1fr));gap:16px}.report-tile{border:1px solid #eceef3;border-radius:16px;background:#fff;box-shadow:0 10px 28px rgba(28,31,42,.055);overflow:hidden}.report-tile-head{display:flex;gap:12px;padding:17px;border-bottom:1px solid #f0f1f4}.report-tile-icon{display:grid;place-items:center;width:44px;height:44px;border-radius:13px;color:#fff;background:linear-gradient(145deg,#f36b21,#df5913)}.report-tile h3{margin:0;font-size:16px;color:#30323a}.report-tile p{margin:4px 0 0;font-size:12px;color:#858891}.report-tile-body{padding:16px}.report-fields{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:12px}.report-fields .full{grid-column:1/-1}@media(max-width:575px){.report-fields{grid-template-columns:1fr}}
	</style>
	<div class="container-fluid icon-page-fluid">
		<div class="icon-hero"><div class="icon-hero-row"><div class="icon-title"><span class="icon-title-icon"><i class="fa fa-chart-pie"></i></span><div><h1>Report Center</h1><p>Generate PDF reports from one professional dashboard.</p></div></div></div></div>
		<div class="report-center-grid">
			<?php foreach($reports as $report): if(isset($_SESSION['login_Permisions']) && !in_array($report['permission'], $_SESSION['login_Permisions'])){ continue; } ?>
				<div class="report-tile">
					<div class="report-tile-head"><span class="report-tile-icon"><i class="fa <?php echo $report['icon']; ?>"></i></span><div><h3><?php echo report_center_safe($report['title']); ?></h3><p><?php echo report_center_safe($report['desc']); ?></p></div></div>
					<div class="report-tile-body">
						<form method="post" target="_blank" action="<?php echo report_center_safe($report['url']); ?>">
							<input type="hidden" name="open_rpt" value="1">
							<?php if(in_array('date',$report['fields'])): ?>
								<div class="report-fields"><input class="icon-input" type="date" name="from_date" value="<?php echo date('Y-m-01'); ?>"><input class="icon-input" type="date" name="to_date" value="<?php echo date('Y-m-d'); ?>"></div>
							<?php endif; ?>
							<?php if(in_array('customer',$report['fields'])): ?>
								<div class="report-fields"><select class="icon-select full" name="customer_id"><option value="">All Customers</option><?php $customers=$conn->query("SELECT cust_id,cust_name FROM customers WHERE cust_status=0 ORDER BY cust_name ASC"); while($c=$customers->fetch_assoc()): ?><option value="<?php echo (int)$c['cust_id']; ?>"><?php echo report_center_safe($c['cust_name']); ?></option><?php endwhile; ?></select></div>
							<?php endif; ?>
							<button class="icon-btn icon-btn-primary" type="submit"><i class="fa fa-file-pdf"></i> Generate PDF</button>
						</form>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
	<?php
}else{
	include 'accessDenied.php';
}
?>
