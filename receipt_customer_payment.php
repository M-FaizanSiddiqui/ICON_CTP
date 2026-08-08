<?php 
include 'db_connect.php';
$order = $conn->query("SELECT a.*,b.cust_name as custName FROM customer_payment as a INNER JOIN customers as b on a.customer_id = b.cust_id where a.pay_id = {$_GET['id']}");
foreach($order->fetch_array() as $k => $v){
	$$k= $v;
}

if($payment_mode == 1){
	$pay_mode = 'Cash';
}
else{
	$pay_mode = 'Cheque';
}
if($reference == ''){$reference = '-';}
if($consignee_name == ''){$consignee_name = '-';}
if($cheque_no == ''){$cheque_no = '-';}
if($cheque_date == ''){$cheque_date = '-';}
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
	<p class="text-center"><b>Payment Slip</b></p>
	<hr>
	<div class="flex">
		<div class="w-100">
			<div class="row">
				<div class="col-md-6">
					<p>Pay ID: <b>PAY-000<?php echo $pay_id  ?></b></p>
					<p>Payment Date: <b><?php echo date("M d, Y",strtotime($payment_date)) ?></b></p>
				</div>
				<div class="col-md-6">
					<p>Customer: <b><?php echo $custName ?></b></p>
					<p>Pay Mode: <b><?php echo $pay_mode ?></b></p>
				</div>
			</div>
			
			
		</div>
	</div>
	<hr>
	<table width="100%">
		<thead>
			<tr>
				<td class="text-center"><b>Reference</b></td>
				<td class="text-center"><b>Consignee Name</b></td>
				<td class="text-center"><b>Cheque No</b></td>
				<td class="text-center"><b>Cheque Date</b></td>
				<td class="text-center"><b>Amount</b></td>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td class="text-center"><?php echo $reference ?></td>
				<td class="text-center"><?php echo $consignee_name ?></td>
				<td class="text-center"><?php echo $cheque_no ?></td>
				<td class="text-center"><?php echo $cheque_date ?></td>
				<td class="text-center"><?php echo number_format($amount,2) ?></td>
			</tr>
		</tbody>
	</table>
	<hr>
	<table width="100%">
		<tbody>
			<tr>
				<td><b>Total Amount</b></td>
				<td class="text-right"><b><?php echo number_format($amount,2) ?></b></td>
			</tr>
			
		</tbody>
	</table>
	<hr>
	<p class="text-center"><b>Order No.</b></p>
	<h4 class="text-center"><b>PAY-000<?php echo $pay_id ?></b></h4>
</div>