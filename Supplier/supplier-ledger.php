<?php include('db_connect.php');

if(in_array("8",$_SESSION['login_Permisions']))
{
	function sl_safe($value){ return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
	$supplier_id = isset($_GET['supplier_id']) ? (int)$_GET['supplier_id'] : 0;
	$fromDt = icon_date_value($_GET['fromDt'] ?? '', date('Y-m-01'));
	$toDt = icon_date_value($_GET['toDt'] ?? '', date('Y-m-d'));
	$from_safe = mysqli_real_escape_string($conn,$fromDt);
	$to_safe = mysqli_real_escape_string($conn,$toDt);

	$supplier_name = 'All Suppliers';
	if($supplier_id > 0){
		$supp_qry = $conn->query("SELECT supp_name FROM suppliers WHERE supp_id = ".$supplier_id." LIMIT 1");
		if($supp_qry && $supp_qry->num_rows > 0){
			$supplier_name = $supp_qry->fetch_assoc()['supp_name'];
		}
	}
	$supplier_filter = $supplier_id > 0 ? " AND supplier_id = ".$supplier_id : "";
	$opening_purchase_qry = $conn->query("SELECT COALESCE(SUM(amount),0) AS total FROM inventoty_received_details WHERE status = 0 ".$supplier_filter." AND DATE(received_date) < '".$from_safe."'");
	$opening_payment_qry = $conn->query("SELECT COALESCE(SUM(amount),0) AS total FROM supplier_payment WHERE pay_status = 0 ".$supplier_filter." AND payment_date < '".$from_safe."'");
	$opening_balance = (float)$opening_purchase_qry->fetch_assoc()['total'] - (float)$opening_payment_qry->fetch_assoc()['total'];

	$ledger_sql = "SELECT supplier_id, supp_name, trans_date, particular, ref_no, debit, credit FROM (
		SELECT d.supplier_id, s.supp_name, DATE(d.received_date) AS trans_date,
			CONCAT('Purchase / Inventory Received - ', i.item_name, ' (Qty ', d.quantity, ' @ ', d.rate, ')') AS particular,
			CONCAT('IR-', d.ir_id) AS ref_no, 0 AS debit, d.amount AS credit
		FROM inventoty_received_details d
		INNER JOIN suppliers s ON s.supp_id = d.supplier_id
		LEFT JOIN inventory_item i ON i.item_id = d.item_id
		WHERE d.status = 0 ".$supplier_filter." AND DATE(d.received_date) >= '".$from_safe."' AND DATE(d.received_date) <= '".$to_safe."'
		UNION ALL
		SELECT p.supplier_id, s.supp_name, p.payment_date AS trans_date,
			CONCAT('Supplier Payment', CASE WHEN p.reference <> '' THEN CONCAT(' - ', p.reference) ELSE '' END) AS particular,
			CONCAT('PAY-', p.pay_id) AS ref_no, p.amount AS debit, 0 AS credit
		FROM supplier_payment p
		INNER JOIN suppliers s ON s.supp_id = p.supplier_id
		WHERE p.pay_status = 0 ".$supplier_filter." AND p.payment_date >= '".$from_safe."' AND p.payment_date <= '".$to_safe."'
	) ledger ORDER BY trans_date ASC, ref_no ASC";
	$ledger_rows = $conn->query($ledger_sql);
	$period_debit = 0;
	$period_credit = 0;
	?>
	<div class="container-fluid icon-page-fluid">
		<div class="icon-hero">
			<div class="icon-hero-row">
				<div class="icon-title">
					<span class="icon-title-icon"><i class="fa fa-book"></i></span>
					<div>
						<h1>Supplier Ledger</h1>
						<p>Purchases, payments, and running payable balance for <?php echo sl_safe($supplier_name); ?>.</p>
					</div>
				</div>
				<div class="icon-stat-row">
					<div class="icon-stat"><span>Opening</span><strong><?php echo number_format($opening_balance,2); ?></strong></div>
					<div class="icon-stat"><span>Period</span><strong><?php echo sl_safe($fromDt); ?> → <?php echo sl_safe($toDt); ?></strong></div>
				</div>
			</div>
		</div>
		<div class="icon-card">
			<div class="icon-card-header">
				<div class="icon-card-title">
					<span><i class="fa fa-filter"></i></span>
					<div><h3>Ledger Filters</h3><p>Supplier payable increases with purchases and decreases with payments.</p></div>
				</div>
			</div>
			<div class="icon-toolbar">
				<div class="icon-toolbar-group">
					<select id="supplier_id" class="icon-select" style="min-width:260px">
						<option value="0">All Suppliers</option>
						<?php
						$suppliers = $conn->query("SELECT supp_id,supp_name FROM suppliers WHERE supp_status = 0 ORDER BY supp_name ASC");
						while($supp = $suppliers->fetch_assoc()):
							$selected = ((int)$supplier_id === (int)$supp['supp_id']) ? 'selected' : '';
						?>
							<option value="<?php echo (int)$supp['supp_id']; ?>" <?php echo $selected; ?>><?php echo sl_safe($supp['supp_name']); ?></option>
						<?php endwhile; ?>
					</select>
					<input type="date" id="fromDt" class="icon-input" value="<?php echo sl_safe($fromDt); ?>">
					<input type="date" id="toDt" class="icon-input" value="<?php echo sl_safe($toDt); ?>">
					<button type="button" id="filterBtn" class="icon-btn icon-btn-primary"><i class="fa fa-search"></i> Get Ledger</button>
				</div>
				<button type="button" id="print" class="icon-btn icon-btn-soft"><i class="fa fa-print"></i> Print</button>
			</div>
			<div class="icon-card-body">
				<div class="icon-table-wrap">
					<table class="icon-table table" id="supplier-ledger-table">
						<thead>
							<tr>
								<th>#</th>
								<th>Date</th>
								<th>Supplier</th>
								<th>Particulars</th>
								<th>Reference</th>
								<th class="text-right">Debit</th>
								<th class="text-right">Credit</th>
								<th class="text-right">Balance</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td colspan="7" class="text-right"><b>Opening Balance</b></td>
								<td class="text-right"><b><?php echo number_format($opening_balance,2); ?></b></td>
							</tr>
							<?php
							$i = 1;
							$balance = $opening_balance;
							if($ledger_rows && $ledger_rows->num_rows > 0):
								while($row = $ledger_rows->fetch_assoc()):
									$debit = (float)$row['debit'];
									$credit = (float)$row['credit'];
									$period_debit += $debit;
									$period_credit += $credit;
									$balance = $balance + $credit - $debit;
							?>
								<tr>
									<td><?php echo $i++; ?></td>
									<td><?php echo date('d-M-Y', strtotime($row['trans_date'])); ?></td>
									<td><?php echo sl_safe($row['supp_name']); ?></td>
									<td><?php echo sl_safe($row['particular']); ?></td>
									<td><span class="icon-badge"><?php echo sl_safe($row['ref_no']); ?></span></td>
									<td class="text-right text-success"><?php echo $debit > 0 ? number_format($debit,2) : '-'; ?></td>
									<td class="text-right text-danger"><?php echo $credit > 0 ? number_format($credit,2) : '-'; ?></td>
									<td class="text-right"><b><?php echo number_format($balance,2); ?></b></td>
								</tr>
							<?php endwhile; else: ?>
								<tr><td colspan="8" class="text-center text-muted">No ledger entries found for selected filters.</td></tr>
							<?php endif; ?>
						</tbody>
						<tfoot>
							<tr>
								<th colspan="5" class="text-right">Period Total</th>
								<th class="text-right"><?php echo number_format($period_debit,2); ?></th>
								<th class="text-right"><?php echo number_format($period_credit,2); ?></th>
								<th class="text-right"><?php echo number_format($balance,2); ?></th>
							</tr>
						</tfoot>
					</table>
				</div>
			</div>
		</div>
	</div>
	<noscript>
		<style>table{width:100%;border-collapse:collapse}td,th{border:1px solid #ddd;padding:7px}.text-right{text-align:right}.text-center{text-align:center}</style>
	</noscript>
	<script>
		$('#filterBtn').click(function(){
			var supplier_id = $('#supplier_id').val();
			var fromDt = $('#fromDt').val();
			var toDt = $('#toDt').val();
			window.open('index.php?page=Supplier/supplier-ledger&supplier_id='+encodeURIComponent(supplier_id)+'&fromDt='+encodeURIComponent(fromDt)+'&toDt='+encodeURIComponent(toDt),'_self');
		});
		$('#print').click(function(){
			var cloned = $('#supplier-ledger-table').clone();
			var ns = $('noscript').clone();
			ns.append(cloned);
			var nw = window.open('','_blank','width=1000,height=700');
			nw.document.write('<h3 style="text-align:center">Supplier Ledger - <?php echo sl_safe($supplier_name); ?></h3>');
			nw.document.write(ns.html());
			nw.document.close();
			nw.print();
			setTimeout(function(){ nw.close(); },500);
		});
	</script>
	<?php
}else{
	include 'accessDenied.php';
}
?>
