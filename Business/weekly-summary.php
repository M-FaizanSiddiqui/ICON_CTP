<?php include('db_connect.php');

$module_qry = $conn->query("SELECT m_id FROM modules_1 WHERE m_url = 'Business/weekly-summary' LIMIT 1");
$module_id = ($module_qry && $module_qry->num_rows > 0) ? (string)$module_qry->fetch_assoc()['m_id'] : "0";
if($module_id == "0" || in_array($module_id,$_SESSION['login_Permisions']))
{
	function bs_date_value($value, $fallback){
		$value = trim((string)$value);
		if($value !== ''){
			$dt = DateTime::createFromFormat('Y-m-d', $value);
			if($dt && $dt->format('Y-m-d') === $value){
				return $value;
			}
		}
		return $fallback;
	}
	function bs_sum($conn,$sql){
		$r=$conn->query($sql);
		return ($r && $r->num_rows>0) ? (float)$r->fetch_assoc()['v'] : 0;
	}
	function bs_money($value){ return 'Rs. '.number_format((float)$value, 0); }

	$from = bs_date_value($_GET['from'] ?? '', date('Y-m-d', strtotime('-6 days')));
	$to = bs_date_value($_GET['to'] ?? '', date('Y-m-d'));
	if(strtotime($from) > strtotime($to)){
		$tmp = $from; $from = $to; $to = $tmp;
	}

	$sales = bs_sum($conn,"SELECT COALESCE(SUM(total_job_amount),0) v FROM job_order WHERE del_status=0 AND order_rec_date >= '".$from."' AND order_rec_date <= '".$to."'");
	$purchases = bs_sum($conn,"SELECT COALESCE(SUM(amount),0) v FROM inventoty_received_details WHERE status=0 AND DATE(received_date) >= '".$from."' AND DATE(received_date) <= '".$to."'");
	$customer_payments = bs_sum($conn,"SELECT COALESCE(SUM(amount),0) v FROM customer_payment WHERE pay_status=0 AND payment_date >= '".$from."' AND payment_date <= '".$to."'");
	$supplier_payments = bs_sum($conn,"SELECT COALESCE(SUM(amount),0) v FROM supplier_payment WHERE pay_status=0 AND payment_date >= '".$from."' AND payment_date <= '".$to."'");
	$jobs = bs_sum($conn,"SELECT COUNT(*) v FROM job_order WHERE del_status=0 AND order_rec_date >= '".$from."' AND order_rec_date <= '".$to."'");
	$completed = bs_sum($conn,"SELECT COUNT(*) v FROM job_order WHERE del_status=0 AND order_status=2 AND order_rec_date >= '".$from."' AND order_rec_date <= '".$to."'");
	$net_collection = $customer_payments - $supplier_payments;

	$message = "ICON CTP Business Summary\n";
	$message .= "Period: ".date('d-M-Y',strtotime($from))." to ".date('d-M-Y',strtotime($to))."\n\n";
	$message .= "Sales: ".bs_money($sales)."\n";
	$message .= "Purchases: ".bs_money($purchases)."\n";
	$message .= "Customer Collection: ".bs_money($customer_payments)."\n";
	$message .= "Supplier Payments: ".bs_money($supplier_payments)."\n";
	$message .= "Net Cash Movement: ".bs_money($net_collection)."\n";
	$message .= "Jobs Received: ".number_format($jobs)."\n";
	$message .= "Jobs Completed: ".number_format($completed)."\n\n";
	$message .= "Regards,\nICON CTP";
	$wa_url = 'https://wa.me/?text='.rawurlencode($message);
	?>
	<style>
		.business-summary-wrap{max-width:1120px;margin:0 auto}
		.business-summary-card{border:1px solid rgba(15,23,42,.08);border-radius:22px;background:#fff;box-shadow:0 18px 45px rgba(15,23,42,.07);overflow:hidden}
		.business-summary-top{background:linear-gradient(135deg,#222 0%,#111827 56%,#f97316 140%);padding:24px 26px;color:#fff;display:flex;align-items:center;justify-content:space-between;gap:16px}
		.business-summary-top h1{font-size:24px;margin:0 0 6px;font-weight:700}
		.business-summary-top p{margin:0;color:rgba(255,255,255,.78);font-size:13px}
		.business-summary-wa{width:54px;height:54px;border-radius:18px;border:0;background:#25D366;color:#fff;display:inline-flex;align-items:center;justify-content:center;font-size:26px;box-shadow:0 14px 28px rgba(37,211,102,.28)}
		.business-summary-body{padding:22px 24px}
		.business-summary-filter{display:flex;gap:10px;flex-wrap:wrap;align-items:center;justify-content:space-between;margin-bottom:18px}
		.business-summary-filter .dates{display:flex;gap:10px;flex-wrap:wrap}
		.business-summary-filter input{height:42px;border:1px solid #e5e7eb;border-radius:12px;padding:0 14px;color:#111827}
		.business-summary-filter button{height:42px;border:0;border-radius:12px;padding:0 18px;background:#f97316;color:#fff;font-weight:700}
		.business-summary-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px}
		.business-metric{border:1px solid #eef2f7;border-radius:18px;padding:16px;background:#fbfdff}
		.business-metric span{font-size:11px;text-transform:uppercase;letter-spacing:.08em;color:#64748b;font-weight:700}
		.business-metric strong{display:block;margin-top:8px;font-size:22px;color:#0f172a;font-weight:700}
		.business-metric i{color:#f97316;margin-right:7px}
		.business-message{margin-top:18px;border-radius:18px;background:#f8fafc;border:1px dashed #cbd5e1;padding:16px}
		.business-message textarea{width:100%;border:0;background:transparent;resize:none;outline:0;font-size:13px;color:#334155;line-height:1.6}
		@media(max-width:991px){.business-summary-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
		@media(max-width:575px){.business-summary-top{align-items:flex-start}.business-summary-grid{grid-template-columns:1fr}.business-summary-filter{display:block}.business-summary-filter .dates{margin-bottom:10px}.business-summary-filter input,.business-summary-filter button{width:100%;margin-bottom:8px}}
	</style>
	<div class="container-fluid icon-page-fluid">
		<div class="business-summary-wrap">
			<div class="business-summary-card">
				<div class="business-summary-top">
					<div>
						<h1>Business Summary</h1>
						<p>Short snapshot for sales, purchases, payments and jobs.</p>
					</div>
					<a class="business-summary-wa" target="_blank" href="<?php echo htmlspecialchars($wa_url); ?>" title="Send on WhatsApp">
						<i class="fab fa-whatsapp"></i>
					</a>
				</div>
				<div class="business-summary-body">
					<form class="business-summary-filter" method="get">
						<input type="hidden" name="page" value="Business/weekly-summary">
						<div class="dates">
							<input type="date" name="from" value="<?php echo htmlspecialchars($from); ?>">
							<input type="date" name="to" value="<?php echo htmlspecialchars($to); ?>">
						</div>
						<button type="submit"><i class="fa fa-sync-alt"></i> Update</button>
					</form>
					<div class="business-summary-grid">
						<div class="business-metric"><span><i class="fa fa-chart-line"></i> Sales</span><strong><?php echo bs_money($sales); ?></strong></div>
						<div class="business-metric"><span><i class="fa fa-shopping-cart"></i> Purchases</span><strong><?php echo bs_money($purchases); ?></strong></div>
						<div class="business-metric"><span><i class="fa fa-hand-holding-usd"></i> Collection</span><strong><?php echo bs_money($customer_payments); ?></strong></div>
						<div class="business-metric"><span><i class="fa fa-wallet"></i> Supplier Paid</span><strong><?php echo bs_money($supplier_payments); ?></strong></div>
						<div class="business-metric"><span><i class="fa fa-exchange-alt"></i> Net Movement</span><strong><?php echo bs_money($net_collection); ?></strong></div>
						<div class="business-metric"><span><i class="fa fa-briefcase"></i> Jobs Received</span><strong><?php echo number_format($jobs); ?></strong></div>
						<div class="business-metric"><span><i class="fa fa-check-circle"></i> Jobs Completed</span><strong><?php echo number_format($completed); ?></strong></div>
						<div class="business-metric"><span><i class="fa fa-calendar-alt"></i> Period</span><strong><?php echo date('d M',strtotime($from)); ?> - <?php echo date('d M',strtotime($to)); ?></strong></div>
					</div>
					<div class="business-message">
						<textarea rows="9" readonly><?php echo htmlspecialchars($message); ?></textarea>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php
}else{ include 'accessDenied.php'; }
?>
