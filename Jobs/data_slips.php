<?php
include('../db_connect.php');

$json_array = array();
$json_array_2 = array();
$json_array_3 = array();

$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
$rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
$index = ($page-1)*$rows;

// $user_id = $_POST['user_id'];

$sort_order_column_value=' order by a.slip_id DESC';
$filter_query=' WHERE 1 ';

$query82="SELECT count(a.slip_id) as rows_total FROM receiving_slips as a INNER JOIN customers as b on a.customer_id = b.cust_id ".$filter_query." ".$sort_order_column_value;
$result82=mysqli_query($conn,$query82);
$data82=mysqli_fetch_array($result82);
$json_array['total']=$data82['rows_total'];

$fetchquery = "SELECT a.*,b.cust_name FROM receiving_slips as a INNER JOIN customers as b on a.customer_id = b.cust_id ".$filter_query." ".$sort_order_column_value." Limit ".$index.",".$rows;
$runQuery = mysqli_query($conn,$fetchquery);
$i = 0;
while($dataFetch = mysqli_fetch_array($runQuery)) 
{
	$i++;
	$slip_no = $dataFetch['slip_id'];
	$customer_id = $dataFetch['customer_id'];
	$cust_name = $dataFetch['cust_name'];
	$rec_date = $dataFetch['rec_date'];
	$created_by = $dataFetch['created_by'];
	$action_btn = '-';

	$job_ids = "";

	$query_job = "SELECT a.*,b.job_id FROM receiving_slip_details as a INNER JOIN job_order_details as b on a.job_order_detail_id = b.id  WHERE a.slip_id = ".$slip_no;
	$result_job = mysqli_query($conn,$query_job);
	while($data_job = mysqli_fetch_array($result_job)){
		$job_id = $data_job['job_id'];
		$job_ids .= "JB-".$job_id.", ";
	}

	$job_ids = trim($job_ids,', ');
	
	$action_btn = '<a style="font-size:20px" action="_blank" target="_blank" href="Jobs/rec-slip-rpt.php?ref='.md5($slip_no).'"><i class="fa fa-file"></i></a>';

	array_push($json_array_2,array(
		'slip_no' => $slip_no,
		'customer' => $cust_name,
		'rec_date' => date('d-M-Y',strtotime($rec_date)),
		'job_no' => $job_ids,
		'action' => $action_btn
	));
}

$json_array['rows']=$json_array_2;
echo json_encode($json_array);
?>