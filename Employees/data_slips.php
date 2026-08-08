<?php
include('../db_connect.php');

header('Content-Type: application/json; charset=utf-8');

$json_array = array();
$json_array_2 = array();
$json_array_3 = array();

$page = max(1, isset($_POST['page']) ? intval($_POST['page']) : 1);
$rows = max(1, isset($_POST['rows']) ? intval($_POST['rows']) : 10);
$index = ($page-1)*$rows;

// $user_id = $_POST['user_id'];

$sort_order_column_value=' order by sp_id DESC';
$filter_query=' WHERE 1 ';

$query82="SELECT count(sp_id) as rows_total FROM salary_slip ".$filter_query." ".$sort_order_column_value;
$result82=mysqli_query($conn,$query82);
$data82=mysqli_fetch_assoc($result82);
$json_array['total']=(int)$data82['rows_total'];

$fetchquery = "SELECT * FROM salary_slip ".$filter_query." ".$sort_order_column_value." Limit ".$index.",".$rows;
$runQuery = mysqli_query($conn,$fetchquery);
$i = 0;
while($dataFetch = mysqli_fetch_array($runQuery)) 
{
	$i++;
	$sp_type_id = $dataFetch['sp_type_id'];
	$sp_id = $dataFetch['sp_id'];
	$sp_month_year = $dataFetch['sp_month_year'];
	$sp_week_st = $dataFetch['sp_week_st'];
	$sp_week_end = $dataFetch['sp_week_end'];
	$action_btn = '-';
	$type_name = 'Other';

	$periodName = "";
	if($sp_type_id == 1){
		$type_name = "Monthly";
		$periodName = date('M-Y',strtotime($sp_month_year));
	}
	else if($sp_type_id == 2){
		$type_name = "Hourly";
		$periodName = date('d-M-Y',strtotime($sp_week_st)).' '.date('d-M-Y',strtotime($sp_week_end));
	}
	else if($sp_type_id == 3){
		$type_name = "Per Impression";
		$periodName = date('M-Y',strtotime($sp_month_year));
	}

	$action_btn = '<a style="font-size:20px" action="_blank" target="_blank" href="index.php?page=Employees/salary-slip-details&ref='.md5($sp_id).'"><i class="fa fa-eye"></i></a>';

	array_push($json_array_2,array(
		'processed_no' => $sp_id,
		'salary_type' => $type_name,
		'period' => $periodName,
		'action' => $action_btn
	));
}

$json_array['rows']=$json_array_2;
echo json_encode($json_array);
?>
