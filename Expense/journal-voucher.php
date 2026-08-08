<?php include('db_connect.php');

if(in_array("66",$_SESSION['login_Permisions']))
{
	$from_date = isset($_GET['from']) && $_GET['from'] != '' ? $_GET['from'] : date('Y-m-01');
	$to_date = isset($_GET['to']) && $_GET['to'] != '' ? $_GET['to'] : date('Y-m-d');
	$from_date_safe = mysqli_real_escape_string($conn,$from_date);
	$to_date_safe = mysqli_real_escape_string($conn,$to_date);

	$receipt_sql = "SELECT d.voucher_no,d.trans_dated,d.narration,d.account_id AS deb_acc,d.debit_amount AS amount,
		da.acc_name AS deb_acc_name,c.account_id AS cred_acc,ca.acc_name AS cred_acc_name
		FROM vouchers d
		INNER JOIN vouchers c ON d.voucher_no = c.voucher_no AND d.v_type_id = c.v_type_id AND c.credit_amount > 0 AND c.cancel_flag = 0
		LEFT JOIN accounts da ON d.account_id = da.account_no
		LEFT JOIN accounts ca ON c.account_id = ca.account_no
		WHERE d.cancel_flag = 0 AND d.v_type_id = 5 AND d.debit_amount > 0
		AND d.trans_dated BETWEEN '".$from_date_safe."' AND '".$to_date_safe."'
		ORDER BY d.voucher_no DESC";
	$receipts = $conn->query($receipt_sql);

	$total_amount = 0;
	$total_count = 0;
	$summary_qry = $conn->query("SELECT COUNT(*) AS total_rows, COALESCE(SUM(debit_amount),0) AS total_amount FROM vouchers WHERE cancel_flag=0 AND v_type_id=5 AND debit_amount>0 AND trans_dated BETWEEN '".$from_date_safe."' AND '".$to_date_safe."'");
	if($summary_qry && $summary_qry->num_rows > 0){
		$summary = $summary_qry->fetch_assoc();
		$total_count = (int)$summary['total_rows'];
		$total_amount = (float)$summary['total_amount'];
	}
	?>
	<style>
		.receipt-page{padding:0 0 26px}.receipt-shell{max-width:1240px;margin:0 auto}.receipt-hero{position:relative;overflow:hidden;margin-bottom:18px;padding:22px 24px;border-radius:20px;background:linear-gradient(135deg,#171923 0%,#282b35 58%,#f36b21 160%);box-shadow:0 18px 45px rgba(20,23,34,.12);color:#fff}.receipt-hero-content{position:relative;z-index:1;display:flex;align-items:center;justify-content:space-between;gap:18px}.receipt-title-wrap{display:flex;align-items:center;gap:14px}.receipt-title-icon{display:grid;place-items:center;flex:0 0 48px;width:48px;height:48px;border-radius:15px;background:rgba(255,255,255,.12)}.receipt-title-copy h2{margin:0;font-size:22px;font-weight:700;color:#fff}.receipt-title-copy p{display:inline-block;margin:8px 0 0;padding:6px 11px;border-radius:999px;background:rgba(255,255,255,.14);box-shadow:inset 0 0 0 1px rgba(255,255,255,.18);font-size:13px;font-weight:600;color:#fff!important}.receipt-card{overflow:hidden;border:1px solid #e7e8ed;border-radius:18px;background:#fff;box-shadow:0 14px 38px rgba(28,31,42,.07)}.receipt-toolbar{display:grid;grid-template-columns:1fr auto;gap:18px;align-items:end;padding:18px 20px;border-bottom:1px solid #edf0f4;background:linear-gradient(180deg,#fff,#fbfbfc)}.receipt-filters{display:flex;gap:12px;flex-wrap:wrap;align-items:end}.receipt-filter label{display:block;margin:0 0 7px;font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#8b8d95}.receipt-filter input{height:43px!important;min-width:180px;border:1px solid #dfe3ea!important;border-radius:12px!important;padding:8px 12px;font-size:13px;color:#30323a;background:#fff}.receipt-btn{height:43px;border:0;border-radius:12px;padding:0 16px;background:#f36b21;color:#fff;font-weight:800;box-shadow:0 10px 22px rgba(243,107,33,.18)}.receipt-add{display:inline-flex;align-items:center;gap:7px;height:43px;padding:0 16px;border-radius:12px;background:#f36b21;color:#fff!important;font-weight:800;text-decoration:none;box-shadow:0 10px 22px rgba(243,107,33,.18)}.receipt-stats{display:flex;gap:10px;flex-wrap:wrap;justify-content:flex-end}.receipt-stat{min-width:140px;padding:11px 13px;border:1px solid #edf0f4;border-radius:14px;background:#fff}.receipt-stat span{display:block;font-size:10px;font-weight:800;letter-spacing:.07em;text-transform:uppercase;color:#9698a1}.receipt-stat strong{display:block;margin-top:5px;font-size:19px;line-height:1;color:#22242b}.receipt-table-wrap{padding:18px;background:#fff}.receipt-table-responsive{overflow:auto;border:1px solid #edf0f4;border-radius:15px}.receipt-table{width:100%;margin:0!important;border:0!important;border-collapse:separate;border-spacing:0;background:#fff}.receipt-table thead th{position:sticky;top:0;z-index:2;padding:13px 14px!important;border:0!important;border-bottom:1px solid #e8ebf1!important;background:#f7f8fa!important;color:#676a73;font-size:10px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;white-space:nowrap}.receipt-table tbody td{padding:13px 14px!important;border:0!important;border-bottom:1px solid #f0f1f4!important;vertical-align:middle!important;font-size:13px;color:#343741}.receipt-table tbody tr:hover td{background:#fff8f4}.receipt-code{font-family:Consolas,monospace;font-weight:800;color:#1e2535}.receipt-account{display:block;color:#747782;font-size:12px;margin-top:3px}.receipt-amount{text-align:right;font-weight:800;color:#159447;white-space:nowrap}.receipt-actions{display:flex;gap:7px;align-items:center}.receipt-action{display:inline-grid;place-items:center;width:34px;height:34px;border-radius:11px;background:#fff2ea;color:#f36b21;border:1px solid #ffd9c4;text-decoration:none}.receipt-action.view{background:#eef4ff;color:#2563eb;border-color:#cfe0ff}.receipt-empty{padding:30px;text-align:center;color:#858892}@media(max-width:768px){.receipt-hero-content,.receipt-toolbar{display:block}.receipt-title-wrap,.receipt-filters{margin-bottom:16px}.receipt-filter input{min-width:100%;width:100%}.receipt-stats{justify-content:flex-start;margin-top:15px}}
	</style>
	<div class="container-fluid receipt-page">
		<div class="receipt-shell">
			<div class="receipt-hero">
				<div class="receipt-hero-content">
					<div class="receipt-title-wrap">
						<div class="receipt-title-icon"><i class="fa fa-book"></i></div>
						<div class="receipt-title-copy"><h2>Journal Vouchers</h2><p>Review posted debit and credit transactions.</p></div>
					</div>
					<a class="receipt-add" href="index.php?page=Expense/make-journal-voucher"><i class="fa fa-plus"></i> New Voucher</a>
				</div>
			</div>
			<div class="receipt-card">
				<div class="receipt-toolbar">
					<form class="receipt-filters" method="get" action="index.php">
						<input type="hidden" name="page" value="Expense/journal-voucher">
						<div class="receipt-filter"><label>From Date</label><input type="date" name="from" value="<?php echo htmlspecialchars($from_date); ?>"></div>
						<div class="receipt-filter"><label>To Date</label><input type="date" name="to" value="<?php echo htmlspecialchars($to_date); ?>"></div>
						<button class="receipt-btn" type="submit"><i class="fa fa-filter"></i> Fetch</button>
					</form>
					<div class="receipt-stats">
						<div class="receipt-stat"><span>Vouchers</span><strong><?php echo number_format($total_count); ?></strong></div>
						<div class="receipt-stat"><span>Total Amount</span><strong><?php echo number_format($total_amount,2); ?></strong></div>
					</div>
				</div>
				<div class="receipt-table-wrap">
					<div class="receipt-table-responsive">
						<table class="table receipt-table">
							<thead><tr><th>Voucher No</th><th>Date</th><th>Narration</th><th>Debit Account</th><th>Credit Account</th><th style="text-align:right">Amount</th><th>Action</th></tr></thead>
							<tbody>
								<?php if($receipts && $receipts->num_rows > 0){ while($row = $receipts->fetch_assoc()){ ?>
									<tr>
										<td><span class="receipt-code">JV-<?php echo $row['voucher_no']; ?></span></td>
										<td><?php echo date('d-M-Y',strtotime($row['trans_dated'])); ?></td>
										<td><?php echo htmlspecialchars($row['narration']); ?></td>
										<td><?php echo htmlspecialchars($row['deb_acc_name']); ?><span class="receipt-account"><?php echo $row['deb_acc']; ?></span></td>
										<td><?php echo htmlspecialchars($row['cred_acc_name']); ?><span class="receipt-account"><?php echo $row['cred_acc']; ?></span></td>
										<td class="receipt-amount"><?php echo number_format($row['amount'],2); ?></td>
										<td><div class="receipt-actions"><a class="receipt-action view" title="View Voucher" href="index.php?page=Expense/journal-voucher-view&ref=<?php echo md5($row['voucher_no']); ?>"><i class="fa fa-eye"></i></a><a class="receipt-action" target="_blank" title="Print Voucher" href="Expense/jv.php?ref=<?php echo md5($row['voucher_no']); ?>"><i class="fa fa-print"></i></a></div></td>
									</tr>
								<?php }}else{ ?>
									<tr><td colspan="7" class="receipt-empty">No journal vouchers found for selected filters.</td></tr>
								<?php } ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
<?php } else { include 'accessDenied.php'; } ?>
