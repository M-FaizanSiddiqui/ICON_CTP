<?php 
include 'db_connect.php';
$receipt_job_id = icon_get_int('id');
$order = $conn->query("SELECT a.*,b.cust_name as custName FROM job_order as a INNER JOIN customers as b on a.customer_id = b.cust_id where a.jd_id = ".$receipt_job_id);
foreach($order->fetch_array() as $k => $v){
	$$k= $v;
}
$items = $conn->query("SELECT a.*,b.item_name FROM job_order_details as a inner join inventory_item as b on a.item_id = b.item_id where a.delete_status = 0 AND a.job_id = ".$receipt_job_id." ");

function receipt_job_status_label($status){
	$labels = [
		0 => 'Pending',
		3 => 'Plate Setting',
		1 => 'On Machine',
		4 => 'Plate Washing',
		5 => 'Oven Baking',
		2 => 'Completed'
	];

	return $labels[(int)$status] ?? 'Pending';
}

$job_status = receipt_job_status_label($order_status);
?>

<style>
	.flex{
		display: inline-flex;
		width: 100%;
	}
	.w-50{
		width: 50%;
	}
	.text-center{
		text-align:center;
	}
	.text-right{
		text-align:right;
	}
	table.wborder{
		width: 100%;
		border-collapse: collapse;
	}
	table.wborder>tbody>tr, table.wborder>tbody>tr>td{
		border:1px solid;
	}
	p{
		margin:unset;
	}

</style>
<div class="container-fluid">
	<p class="text-center"><b>Bill</b></p>
	<hr>
	<div class="flex">
		<div class="w-100">
			<div class="row">
				<div class="col-md-6">
					<p>Order Number: <b>ORD-<?php echo $jd_id  ?></b></p>
					<p>Date: <b><?php echo date("M d, Y",strtotime($order_rec_date)) ?></b></p>
				</div>
				<div class="col-md-6">
					<p>Customer: <b><?php echo $custName ?></b></p>
					<p>Status: <b><?php echo $job_status ?></b></p>
				</div>
			</div>
			
			
		</div>
	</div>
	<hr>
	<p><b>Order List</b></p>
	<table width="100%">
		<thead>
			<tr>
				<td><b>Item</b></td>
				<td><b>Price</b></td>
				<td><b>Quantity</b></td>
				<td class="text-right"><b>Amount</b></td>
			</tr>
		</thead>
		<tbody>
			<?php 
			$total_amt = 0;
			while($row = $items->fetch_assoc()):
				$total_amt += $row['total_amount'];
				?>
				<tr>
					<td><?php echo $row['item_name'] ?></td>
					<td><?php echo $row['price'] ?></td>
					<td><?php echo $row['quantity'] ?></td>
					<td class="text-right"><?php echo number_format($row['total_amount'],2) ?></td>
				</tr>
			<?php endwhile; ?>
		</tbody>
	</table>
	<hr>
	<table width="100%">
		<tbody>
			<tr>
				<td><b>Total Amount</b></td>
				<td class="text-right"><b><?php echo number_format($total_amt,2) ?></b></td>
			</tr>
			
		</tbody>
	</table>
	<hr>
	<p class="text-center"><b>Order No.</b></p>
	<h4 class="text-center"><b>ORD-<?php echo $jd_id ?></b></h4>
</div>
