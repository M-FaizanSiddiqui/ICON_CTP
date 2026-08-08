<?php
include('../db_connect.php');

$json_array = array();
$json_array_2 = array();
$json_array_3 = array();

$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
$rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
$index = ($page-1)*$rows;

// $user_id = $_POST['user_id'];

$sort_order_column_value=' order by a.voucher_no DESC';
$filter_query=' WHERE 1 AND a.cancel_flag = 0 AND a.debit_amount > 0 AND a.v_type_id = 5 ';

$query82 = "SELECT count(a.id) as rows_total FROM vouchers as a INNER JOIN vouchers as b on (a.voucher_no=b.voucher_no and b.credit_amount>0) ".$filter_query." ".$sort_order_column_value;
$result82=mysqli_query($conn,$query82);
$data82=mysqli_fetch_array($result82);
$json_array['total']=$data82['rows_total'];

$fetchquery = "SELECT a.voucher_no,a.narration,a.v_type_id,a.account_id as deb_acc,b.account_id as cred_acc,a.trans_dated,a.debit_amount,b.credit_amount,d.acc_name  as deb_acc_name, e.acc_name as cred_acc_name FROM vouchers as a INNER JOIN vouchers as b on (a.voucher_no=b.voucher_no and b.credit_amount>0) LEFT JOIN accounts as d on a.account_id = d.account_no LEFT JOIN accounts as e on b.account_id = e.account_no  ".$filter_query." ".$sort_order_column_value." Limit ".$index.",".$rows;
$runQuery = mysqli_query($conn,$fetchquery);
$i = 0;
while($dataFetch = mysqli_fetch_array($runQuery)) 
{
	$i++;
	$voucher_no = $dataFetch['voucher_no'];
	$trans_dated = $dataFetch['trans_dated'];
	$debit_amount = $dataFetch['debit_amount'];
	$credit_amount = $dataFetch['credit_amount'];
	$deb_acc = $dataFetch['deb_acc'];
	$cred_acc = $dataFetch['cred_acc'];
	$v_type_id = $dataFetch['v_type_id'];
	$deb_acc_name = $dataFetch['deb_acc_name'];
	$cred_acc_name = $dataFetch['cred_acc_name'];
	$narration = $dataFetch['narration'];

	$action_btn = '-';

	$action_btn = '<a style="font-size:20px" action="_blank" target="_blank" href="Expense/jv.php?ref='.md5($voucher_no).'"><i class="fa fa-file"></i></a>';

	array_push($json_array_2,array(
		'voucher_no' => 'JV-'.$voucher_no,
		'trans_date' => date('d-M-Y',strtotime($trans_dated)),
		'narration' => $narration,
		'debit_acc' => $deb_acc.'-'.$deb_acc_name,
		'credit_Acc' => $cred_acc.'-'.$cred_acc_name,
		'amount' => number_format($debit_amount),
		'action' => $action_btn
	));

}

$json_array['rows']=$json_array_2;
echo json_encode($json_array);
?>