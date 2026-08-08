<?php include('db_connect.php');

if(in_array("26",$_SESSION['login_Permisions']))
{
	function rl_safe($value){ return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
	$rate_rows = $conn->query("SELECT i.item_id,i.item_name,i.size_in_mm,i.hl_inches,i.quantity,i.qty_booked,i.avg_rate,i.imposition_charges,i.OvenBake_Charges,
		COALESCE(SUM(CASE WHEN p.del_status = 0 THEN p.prc_qty ELSE 0 END),0) AS batch_qty,
		COALESCE(SUM(CASE WHEN p.del_status = 0 THEN p.total_stock_amt ELSE 0 END),0) AS batch_value,
		COUNT(CASE WHEN p.del_status = 0 THEN p.prc_id END) AS batches
		FROM inventory_item i
		LEFT JOIN plate_rate_calculations p ON p.prc_plate_id = i.item_id
		GROUP BY i.item_id
		ORDER BY i.item_name ASC");
	$total_items = $conn->query("SELECT COUNT(*) AS c FROM inventory_item WHERE status = 0")->fetch_assoc()['c'];
	$stock_value = $conn->query("SELECT COALESCE(SUM(quantity * avg_rate),0) AS v FROM inventory_item WHERE status = 0")->fetch_assoc()['v'];
	?>
	<div class="container-fluid icon-page-fluid">
		<div class="icon-hero">
			<div class="icon-hero-row">
				<div class="icon-title">
					<span class="icon-title-icon"><i class="fa fa-tags"></i></span>
					<div>
						<h1>Plate Rate List</h1>
						<p>Current plate rates, stock value, and active rate batches.</p>
					</div>
				</div>
				<div class="icon-stat-row">
					<div class="icon-stat"><span>Active Items</span><strong><?php echo number_format($total_items); ?></strong></div>
					<div class="icon-stat"><span>Stock Value</span><strong><?php echo number_format($stock_value,0); ?></strong></div>
				</div>
			</div>
		</div>
		<div class="icon-card">
			<div class="icon-card-header">
				<div class="icon-card-title">
					<span><i class="fa fa-list-alt"></i></span>
					<div><h3>Rate Directory</h3><p>Professional view for plate pricing and stock costing.</p></div>
				</div>
				<a href="index.php?page=Stocks/add-items" class="icon-btn icon-btn-primary"><i class="fa fa-plus"></i> Add Plate</a>
			</div>
			<div class="icon-card-body">
				<div class="icon-table-wrap">
					<table class="icon-table table" id="rate-list-table">
						<thead>
							<tr>
								<th>Code</th>
								<th>Plate</th>
								<th>Size</th>
								<th>HL Inches</th>
								<th class="text-right">Stock</th>
								<th class="text-right">Booked</th>
								<th class="text-right">Avg Rate</th>
								<th class="text-right">Batch Qty</th>
								<th class="text-right">Batch Value</th>
								<th class="text-right">Imposing</th>
								<th class="text-right">Oven</th>
							</tr>
						</thead>
						<tbody>
							<?php while($row = $rate_rows->fetch_assoc()):
								$available = (float)$row['quantity'] - (float)$row['qty_booked'];
							?>
								<tr>
									<td><span class="icon-badge">IT-<?php echo str_pad($row['item_id'],4,'0',STR_PAD_LEFT); ?></span></td>
									<td><?php echo rl_safe($row['item_name']); ?><br><small class="text-muted"><?php echo (int)$row['batches']; ?> rate batches</small></td>
									<td><?php echo rl_safe($row['size_in_mm']); ?></td>
									<td><?php echo rl_safe($row['hl_inches']); ?></td>
									<td class="text-right"><?php echo number_format($row['quantity']); ?><br><small class="text-muted">Available <?php echo number_format($available); ?></small></td>
									<td class="text-right"><?php echo number_format($row['qty_booked']); ?></td>
									<td class="text-right"><?php echo number_format($row['avg_rate'],2); ?></td>
									<td class="text-right"><?php echo number_format($row['batch_qty']); ?></td>
									<td class="text-right"><?php echo number_format($row['batch_value'],2); ?></td>
									<td class="text-right"><?php echo number_format($row['imposition_charges'],2); ?></td>
									<td class="text-right"><?php echo number_format($row['OvenBake_Charges'],2); ?></td>
								</tr>
							<?php endwhile; ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
	<script>
		$(document).ready(function(){
			$('#rate-list-table').DataTable({ pageLength:50, order:[[1,'asc']] });
		});
	</script>
	<?php
}else{
	include 'accessDenied.php';
}
?>
