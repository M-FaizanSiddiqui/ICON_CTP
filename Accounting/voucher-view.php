<?php include('db_connect.php');

$type = isset($_GET['type']) ? $_GET['type'] : '';
$ref = isset($_GET['ref']) ? mysqli_real_escape_string($conn,$_GET['ref']) : '';

$config = [
	'sales_receipt' => [
		'title' => 'Sales Receipt',
		'prefix' => 'SR',
		'icon' => 'fa fa-receipt',
		'v_type_id' => 4,
		'debit_label' => 'Received In',
		'credit_label' => 'Customer Account',
		'print' => 'Accounting/sales-receipt-print.php',
		'extra_sql' => "LEFT JOIN customer_payment cp ON v.ref_column='customer_payment' AND v.ref_id=cp.pay_id LEFT JOIN customers party ON cp.customer_id=party.cust_id",
		'party_field' => 'party.cust_name',
		'ref_field' => 'cp.reference'
	],
	'sales_invoice' => [
		'title' => 'Sales Invoice',
		'prefix' => 'SI',
		'icon' => 'fa fa-file-invoice-dollar',
		'v_type_id' => 3,
		'debit_label' => 'Customer Account',
		'credit_label' => 'Sales Account',
		'print' => 'Accounting/sales-invoice-print.php',
		'extra_sql' => "LEFT JOIN job_order jo ON v.ref_column='job_order' AND v.ref_id=jo.jd_id LEFT JOIN customers party ON jo.customer_id=party.cust_id",
		'party_field' => 'party.cust_name',
		'ref_field' => 'jo.job_name'
	],
	'purchase_receipt' => [
		'title' => 'Purchase Receipt',
		'prefix' => 'PR',
		'icon' => 'fa fa-file-invoice',
		'v_type_id' => 1,
		'debit_label' => 'Purchase Account',
		'credit_label' => 'Supplier Account',
		'print' => 'Accounting/purchase-receipt-print.php',
		'extra_sql' => "LEFT JOIN inventory_received ir ON v.ref_column='inventory_received' AND v.ref_id=ir.ir_id LEFT JOIN suppliers party ON ir.supplier_id=party.supp_id",
		'party_field' => 'party.supp_name',
		'ref_field' => 'ir.doc_no'
	],
	'supplier_payment' => [
		'title' => 'Supplier Payment Receipt',
		'prefix' => 'SPR',
		'icon' => 'fa fa-money',
		'v_type_id' => 2,
		'debit_label' => 'Supplier Account',
		'credit_label' => 'Paid From',
		'print' => 'Accounting/supplier-payment-receipt-print.php',
		'extra_sql' => "LEFT JOIN supplier_payment sp ON v.ref_column='supplier_payment' AND v.ref_id=sp.pay_id LEFT JOIN suppliers party ON sp.supplier_id=party.supp_id",
		'party_field' => 'party.supp_name',
		'ref_field' => 'sp.reference'
	]
];

if(!isset($config[$type]) || $ref == ''){
	echo '<div class="alert alert-danger">Invalid voucher request.</div>';
	return;
}

$cfg = $config[$type];
$head_sql = "SELECT v.voucher_no,v.trans_dated,v.ref_id,v.ref_column,v.narration,v.debit_amount AS amount,
	".$cfg['party_field']." AS party_name, ".$cfg['ref_field']." AS reference_text
	FROM vouchers v
	".$cfg['extra_sql']."
	WHERE v.cancel_flag=0 AND v.v_type_id=".(int)$cfg['v_type_id']." AND v.debit_amount>0 AND md5(v.voucher_no)='".$ref."' LIMIT 1";
$head_qry = $conn->query($head_sql);
if(!$head_qry || $head_qry->num_rows == 0){
	echo '<div class="alert alert-warning">Voucher not found.</div>';
	return;
}
$voucher = $head_qry->fetch_assoc();
$rows = [];
$detail_qry = $conn->query("SELECT v.account_id,v.debit_amount,v.credit_amount,a.acc_name FROM vouchers v LEFT JOIN accounts a ON v.account_id=a.account_no WHERE v.cancel_flag=0 AND v.v_type_id=".(int)$cfg['v_type_id']." AND md5(v.voucher_no)='".$ref."' ORDER BY v.debit_amount DESC");
while($detail_qry && $row = $detail_qry->fetch_assoc()){
	$rows[] = $row;
}
$debit_total = 0;
$credit_total = 0;
?>
<style>
	.voucher-view-page{max-width:1060px;margin:0 auto;padding:0 0 28px}.voucher-view-hero{display:flex;align-items:center;justify-content:space-between;gap:18px;margin-bottom:18px;padding:22px 24px;border-radius:20px;background:linear-gradient(135deg,#171923 0%,#282b35 58%,#f36b21 160%);color:#fff;box-shadow:0 18px 45px rgba(20,23,34,.12)}.voucher-view-title{display:flex;align-items:center;gap:14px}.voucher-view-title span{display:grid;place-items:center;width:48px;height:48px;border-radius:15px;background:rgba(255,255,255,.14)}.voucher-view-title h2{margin:0;font-size:22px;color:#fff}.voucher-view-title p{margin:6px 0 0;color:#fff;font-size:12px}.voucher-view-actions{display:flex;gap:9px}.voucher-view-actions a{display:inline-flex;align-items:center;gap:7px;min-height:39px;padding:9px 14px;border-radius:10px;font-size:12px;font-weight:700;text-decoration:none}.voucher-back{background:rgba(255,255,255,.12);color:#fff;border:1px solid rgba(255,255,255,.2)}.voucher-print{background:#f36b21;color:#fff}.voucher-card{overflow:hidden;border:1px solid #e7e8ed;border-radius:18px;background:#fff;box-shadow:0 14px 38px rgba(28,31,42,.07)}.voucher-meta{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;padding:18px;border-bottom:1px solid #edf0f4;background:#fbfcfd}.voucher-meta-box{padding:13px;border:1px solid #edf0f4;border-radius:14px;background:#fff}.voucher-meta-box span{display:block;margin-bottom:6px;font-size:10px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#8b8d95}.voucher-meta-box strong{font-size:14px;color:#252832}.voucher-body{padding:18px}.voucher-table-wrap{overflow:auto;border:1px solid #edf0f4;border-radius:15px}.voucher-table{width:100%;margin:0!important;border-collapse:separate;border-spacing:0}.voucher-table th{padding:13px 14px!important;background:#f7f8fa!important;border:0!important;border-bottom:1px solid #e8ebf1!important;color:#676a73;font-size:10px;font-weight:800;letter-spacing:.08em;text-transform:uppercase}.voucher-table td{padding:13px 14px!important;border:0!important;border-bottom:1px solid #f0f1f4!important;font-size:13px;color:#343741}.voucher-table .amount{text-align:right;font-weight:800}.voucher-total td{background:#fff8f4!important;font-weight:800}.voucher-narration{margin-top:16px;padding:14px;border:1px solid #edf0f4;border-radius:14px;background:#fbfcfd;color:#555b67}@media(max-width:768px){.voucher-view-hero{display:block}.voucher-view-actions{margin-top:14px}.voucher-meta{grid-template-columns:1fr}}
</style>
<div class="voucher-view-page">
	<div class="voucher-view-hero">
		<div class="voucher-view-title"><span><i class="<?php echo $cfg['icon']; ?>"></i></span><div><h2><?php echo $cfg['title']; ?></h2><p>Detailed voucher entry inside the system panel.</p></div></div>
		<div class="voucher-view-actions"><a class="voucher-back" href="javascript:history.back()"><i class="fa fa-arrow-left"></i> Back</a><a class="voucher-print" target="_blank" href="<?php echo $cfg['print']; ?>?ref=<?php echo md5($voucher['voucher_no']); ?>"><i class="fa fa-print"></i> Print</a></div>
	</div>
	<div class="voucher-card">
		<div class="voucher-meta">
			<div class="voucher-meta-box"><span>Voucher No</span><strong><?php echo $cfg['prefix'].'-'.$voucher['voucher_no']; ?></strong></div>
			<div class="voucher-meta-box"><span>Date</span><strong><?php echo date('d-M-Y',strtotime($voucher['trans_dated'])); ?></strong></div>
			<div class="voucher-meta-box"><span>Party</span><strong><?php echo htmlspecialchars($voucher['party_name'] ?: '-'); ?></strong></div>
			<div class="voucher-meta-box"><span>Amount</span><strong><?php echo number_format($voucher['amount'],2); ?></strong></div>
			<div class="voucher-meta-box" style="grid-column:span 4"><span>Reference</span><strong><?php echo htmlspecialchars($voucher['reference_text'] ?: ($voucher['ref_column'].' #'.$voucher['ref_id'])); ?></strong></div>
		</div>
		<div class="voucher-body">
			<div class="voucher-table-wrap">
				<table class="voucher-table">
					<thead><tr><th>Account No</th><th>Account Name</th><th class="amount">Debit</th><th class="amount">Credit</th></tr></thead>
					<tbody>
						<?php foreach($rows as $row){ $debit_total += $row['debit_amount']; $credit_total += $row['credit_amount']; ?>
							<tr><td><?php echo $row['account_id']; ?></td><td><?php echo htmlspecialchars($row['acc_name']); ?></td><td class="amount"><?php echo number_format($row['debit_amount'],2); ?></td><td class="amount"><?php echo number_format($row['credit_amount'],2); ?></td></tr>
						<?php } ?>
						<tr class="voucher-total"><td colspan="2" class="amount">Total</td><td class="amount"><?php echo number_format($debit_total,2); ?></td><td class="amount"><?php echo number_format($credit_total,2); ?></td></tr>
					</tbody>
				</table>
			</div>
			<div class="voucher-narration"><strong>Narration:</strong> <?php echo htmlspecialchars($voucher['narration']); ?></div>
		</div>
	</div>
</div>
