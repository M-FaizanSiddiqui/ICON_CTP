<?php
include '../db_connect.php';

$payment_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$order = $conn->query("SELECT a.*, b.cust_name AS custName, u.name AS receiverName FROM customer_payment AS a INNER JOIN customers AS b ON a.customer_id = b.cust_id LEFT JOIN users AS u ON a.user_id = u.id WHERE a.pay_id = {$payment_id} LIMIT 1");

if(!$order || !$order->num_rows){
	echo '<div class="payment-empty">Payment record not found.</div>';
	return;
}

$payment = $order->fetch_assoc();
$pay_mode = ((int)$payment['payment_mode'] === 1) ? 'Cash' : 'Cheque';

function payment_value($value, $fallback = '-'){
	$value = trim((string)$value);
	return htmlspecialchars($value !== '' ? $value : $fallback, ENT_QUOTES, 'UTF-8');
}

$pay_code = 'PAY-'.str_pad((int)$payment['pay_id'], 6, '0', STR_PAD_LEFT);
$payment_date_text = date('d M Y', strtotime($payment['payment_date']));
$cheque_date_text = !empty($payment['cheque_date']) && $payment['cheque_date'] !== '0000-00-00' ? date('d M Y', strtotime($payment['cheque_date'])) : '-';
?>

<style>
	.payment-receipt{font-family:Arial,sans-serif;color:#303033;background:#fff}.payment-receipt *{box-sizing:border-box}
	.payment-receipt-header{display:flex;align-items:center;justify-content:space-between;gap:20px;padding:4px 0 16px;border-bottom:2px solid #f36b21}.payment-title{display:flex;align-items:center;gap:12px}.payment-title-icon{display:grid;place-items:center;width:42px;height:42px;border-radius:11px;color:#fff;background:linear-gradient(145deg,#f36b21,#df5812)}.payment-title h2{margin:0;font-size:19px;font-weight:650}.payment-title p{margin:4px 0 0;font-size:11px;color:#85868c}.payment-status{padding:7px 11px;border:1px solid #bde4cf;border-radius:20px;font-size:10px;font-weight:700;color:#267b53;background:#edf9f2}
	.payment-overview{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;margin:16px 0}.payment-info-card{padding:12px 14px;border:1px solid #e5e6e9;border-left:3px solid #f36b21;border-radius:9px;background:#fafafa}.payment-info-card span{display:block;margin-bottom:5px;font-size:9px;font-weight:700;letter-spacing:.07em;color:#85868c;text-transform:uppercase}.payment-info-card strong{font-size:13px;font-weight:650;color:#303033}
	.payment-section-title{display:flex;align-items:center;gap:7px;margin:18px 0 8px;font-size:11px;font-weight:700;letter-spacing:.04em;color:#55565b;text-transform:uppercase}.payment-section-title:before{content:'';width:4px;height:15px;border-radius:4px;background:#f36b21}
	.payment-details{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));border:1px solid #e5e6e9;border-radius:10px;overflow:hidden}.payment-detail{min-height:58px;padding:11px 13px;border-bottom:1px solid #eeeeef}.payment-detail:nth-child(odd){border-right:1px solid #eeeeef}.payment-detail:nth-last-child(-n+2){border-bottom:0}.payment-detail label{display:block;margin-bottom:5px;font-size:9px;font-weight:700;color:#8b8c91;text-transform:uppercase}.payment-detail div{font-size:12px;font-weight:550;color:#3d3e42}.payment-detail .muted{color:#77787d}
	.payment-total{display:flex;align-items:center;justify-content:space-between;gap:20px;margin-top:16px;padding:15px 17px;border:1px solid #ffd7c1;border-radius:10px;background:#fff6f0}.payment-total span{font-size:11px;font-weight:700;color:#6f7075;text-transform:uppercase}.payment-total strong{font-size:22px;font-weight:700;color:#e25d18}.payment-total small{margin-right:5px;font-size:10px;color:#9a6f59}
	.payment-meta{display:flex;justify-content:space-between;gap:15px;margin-top:14px;padding-top:12px;border-top:1px solid #ececef;font-size:9px;color:#909196}.payment-empty{padding:30px;text-align:center;color:#777}
	@media(max-width:600px){.payment-overview,.payment-details{grid-template-columns:1fr}.payment-detail:nth-child(odd){border-right:0}.payment-detail:nth-last-child(2){border-bottom:1px solid #eeeeef}.payment-receipt-header{align-items:flex-start;flex-direction:column}}
	@media print{body{margin:12mm}.payment-receipt{max-width:900px;margin:0 auto}.payment-title-icon{color:#fff!important;-webkit-print-color-adjust:exact;print-color-adjust:exact}.payment-total,.payment-info-card,.payment-status{-webkit-print-color-adjust:exact;print-color-adjust:exact}}
</style>

<div class="payment-receipt">
	<div class="payment-receipt-header">
		<div class="payment-title"><span class="payment-title-icon"><i class="fa fa-money-bill"></i></span><div><h2>Customer Payment Receipt</h2><p>Payment transaction and receiving details</p></div></div>
		<span class="payment-status">Payment Received</span>
	</div>

	<div class="payment-overview">
		<div class="payment-info-card"><span>Payment Number</span><strong><?php echo $pay_code; ?></strong></div>
		<div class="payment-info-card"><span>Payment Date</span><strong><?php echo htmlspecialchars($payment_date_text); ?></strong></div>
		<div class="payment-info-card"><span>Customer</span><strong><?php echo payment_value($payment['custName']); ?></strong></div>
		<div class="payment-info-card"><span>Payment Method</span><strong><?php echo htmlspecialchars($pay_mode); ?></strong></div>
	</div>

	<div class="payment-section-title">Transaction Details</div>
	<div class="payment-details">
		<div class="payment-detail"><label>Reference</label><div><?php echo payment_value($payment['reference']); ?></div></div>
		<div class="payment-detail"><label>Consignee Name</label><div><?php echo payment_value($payment['consignee_name']); ?></div></div>
		<div class="payment-detail"><label>Cheque Number</label><div class="muted"><?php echo payment_value($payment['cheque_no']); ?></div></div>
		<div class="payment-detail"><label>Cheque Date</label><div class="muted"><?php echo htmlspecialchars($cheque_date_text); ?></div></div>
		<div class="payment-detail"><label>Received By</label><div><?php echo payment_value($payment['receiverName'], 'System User'); ?></div></div>
		<div class="payment-detail"><label>Payment Mode</label><div><?php echo htmlspecialchars($pay_mode); ?></div></div>
	</div>

	<div class="payment-total"><span>Total Amount Received</span><strong><small>PKR</small><?php echo number_format((float)$payment['amount'], 2); ?></strong></div>
	<div class="payment-meta"><span>Transaction: <?php echo $pay_code; ?></span><span>System generated payment receipt</span></div>
</div>
