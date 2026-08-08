<?php
session_start();
include '../db_connect.php';

if(!isset($_SESSION['login_Permisions'])){
	die('Access denied');
}

$ref = isset($_GET['ref']) ? mysqli_real_escape_string($conn,$_GET['ref']) : '';
$voucher = null;
$rows = [];

if($ref != ''){
	$head_sql = "SELECT a.voucher_no,a.trans_dated,a.ref_id,a.narration,a.debit_amount,
		d.acc_name AS deb_acc_name,e.acc_name AS cred_acc_name,a.account_id AS deb_acc,b.account_id AS cred_acc,
		cp.reference,cp.payment_mode,cp.cheque_no,cp.cheque_date,cust.cust_name,co.comp_name
		FROM vouchers a
		INNER JOIN vouchers b ON a.voucher_no=b.voucher_no AND a.v_type_id=b.v_type_id AND b.credit_amount>0 AND b.cancel_flag=0
		LEFT JOIN accounts d ON a.account_id=d.account_no
		LEFT JOIN accounts e ON b.account_id=e.account_no
		LEFT JOIN companies co ON d.company_id=co.comp_id
		LEFT JOIN customer_payment cp ON a.ref_column='customer_payment' AND a.ref_id=cp.pay_id
		LEFT JOIN customers cust ON cp.customer_id=cust.cust_id
		WHERE a.cancel_flag=0 AND a.v_type_id=4 AND a.debit_amount>0 AND md5(a.voucher_no)='".$ref."' LIMIT 1";
	$head_qry = $conn->query($head_sql);
	if($head_qry && $head_qry->num_rows > 0){
		$voucher = $head_qry->fetch_assoc();
		$detail_qry = $conn->query("SELECT v.account_id,v.debit_amount,v.credit_amount,a.acc_name FROM vouchers v LEFT JOIN accounts a ON v.account_id=a.account_no WHERE v.cancel_flag=0 AND v.v_type_id=4 AND md5(v.voucher_no)='".$ref."' ORDER BY v.debit_amount DESC");
		while($detail_qry && $row = $detail_qry->fetch_assoc()){
			$rows[] = $row;
		}
	}
}

if(!$voucher){ die('Sales receipt not found.'); }

$debit_total = 0;
$credit_total = 0;
?>
<!doctype html>
<html>
<head>
	<meta charset="utf-8">
	<title>Sales Receipt SR-<?php echo $voucher['voucher_no']; ?></title>
	<style>
		body{margin:0;background:#f4f5f8;color:#20232b;font-family:Arial,Helvetica,sans-serif;font-size:13px}
		.receipt{width:820px;margin:24px auto;background:#fff;border:1px solid #e3e6ec;border-radius:18px;box-shadow:0 18px 45px rgba(28,31,42,.12);overflow:hidden}
		.top{display:flex;justify-content:space-between;gap:20px;align-items:flex-start;padding:24px 28px;border-bottom:1px solid #eceff4;background:linear-gradient(135deg,#fff 0%,#fff7f1 100%)}
		.logo{width:190px;height:auto}
		.company{margin-top:10px;color:#5f6572;line-height:1.55;font-size:12px}
		.title{text-align:right}
		.title h1{margin:0;color:#f36b21;font-size:28px;letter-spacing:.03em}
		.badge{display:inline-block;margin-top:10px;padding:8px 12px;border-radius:999px;background:#22242b;color:#fff;font-weight:700}
		.meta{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;padding:18px 28px;border-bottom:1px solid #eceff4}
		.box{border:1px solid #edf0f4;border-radius:14px;padding:12px;background:#fbfcfd}
		.box span{display:block;font-size:10px;text-transform:uppercase;letter-spacing:.08em;color:#8b909b;font-weight:800;margin-bottom:7px}
		.box strong{font-size:14px;color:#262a33}
		.content{padding:24px 28px}
		table{width:100%;border-collapse:separate;border-spacing:0;border:1px solid #e8ebf1;border-radius:14px;overflow:hidden}
		th{background:#f7f8fa;color:#676a73;font-size:10px;text-transform:uppercase;letter-spacing:.08em;text-align:left;padding:12px}
		td{padding:12px;border-top:1px solid #eef1f5;color:#343741}
		td.amount,th.amount{text-align:right}
		tfoot td{font-weight:800;background:#fff8f4}
		.narration{margin-top:18px;border:1px solid #edf0f4;border-radius:14px;padding:14px;background:#fbfcfd;color:#555b67}
		.signs{display:grid;grid-template-columns:1fr 1fr;gap:34px;margin-top:54px}
		.sign{border-top:1px solid #9ca3af;padding-top:9px;text-align:center;color:#676a73;font-size:12px}
		.printbar{width:820px;margin:18px auto 0;text-align:right}
		.printbtn{border:0;border-radius:12px;background:#f36b21;color:white;padding:11px 16px;font-weight:800;cursor:pointer}
		@media print{body{background:#fff}.printbar{display:none}.receipt{margin:0 auto;box-shadow:none;border-radius:0}}
	</style>
</head>
<body>
	<div class="printbar"><button class="printbtn" onclick="window.print()">Print Receipt</button></div>
	<div class="receipt">
		<div class="top">
			<div>
				<img class="logo" src="../assets/uploads/logo.png" alt="ICON">
				<div class="company">Suite # 8, Plot # D-20/A, Moin Akhter Road, S.I.T.E., Karachi-75700<br>PH: (021) 3256 4266 | (0331) 111 4266</div>
			</div>
			<div class="title">
				<h1>Sales Receipt</h1>
				<div class="badge">SR-<?php echo $voucher['voucher_no']; ?></div>
			</div>
		</div>
		<div class="meta">
			<div class="box"><span>Date</span><strong><?php echo date('d-M-Y',strtotime($voucher['trans_dated'])); ?></strong></div>
			<div class="box"><span>Company</span><strong><?php echo htmlspecialchars($voucher['comp_name']); ?></strong></div>
			<div class="box"><span>Amount</span><strong><?php echo number_format($voucher['debit_amount'],2); ?></strong></div>
			<div class="box"><span>Customer</span><strong><?php echo htmlspecialchars($voucher['cust_name'] ?: 'Customer'); ?></strong></div>
			<div class="box" style="grid-column:span 2"><span>Reference</span><strong><?php echo htmlspecialchars($voucher['reference'] ?: ('Payment #'.$voucher['ref_id'])); ?></strong></div>
		</div>
		<div class="content">
			<table>
				<thead><tr><th>Account No</th><th>Account Name</th><th class="amount">Debit</th><th class="amount">Credit</th></tr></thead>
				<tbody>
					<?php foreach($rows as $row){ $debit_total += $row['debit_amount']; $credit_total += $row['credit_amount']; ?>
						<tr><td><?php echo $row['account_id']; ?></td><td><?php echo htmlspecialchars($row['acc_name']); ?></td><td class="amount"><?php echo number_format($row['debit_amount'],2); ?></td><td class="amount"><?php echo number_format($row['credit_amount'],2); ?></td></tr>
					<?php } ?>
				</tbody>
				<tfoot><tr><td colspan="2" class="amount">Total</td><td class="amount"><?php echo number_format($debit_total,2); ?></td><td class="amount"><?php echo number_format($credit_total,2); ?></td></tr></tfoot>
			</table>
			<div class="narration"><strong>Narration:</strong> <?php echo htmlspecialchars($voucher['narration']); ?></div>
			<div class="signs"><div class="sign">Prepared By</div><div class="sign">Authorized Signature</div></div>
		</div>
	</div>
</body>
</html>
