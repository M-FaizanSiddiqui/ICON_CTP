<?php
include('../db_connect.php');

$json_array = array();
$json_array_2 = array();
$json_array_3 = array();

$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
$rows = isset($_POST['rows']) ? intval($_POST['rows']) : 30;
$index = ($page-1)*$rows;
$search = isset($_POST['search']) ? trim((string)$_POST['search']) : '';
$search_safe = mysqli_real_escape_string($conn,$search);

// $user_id = $_POST['user_id'];

function job_order_status_label($status)
{
	if((int)$status === 3){
		return 'Plate Setting';
	}
	if((int)$status === 1){
		return 'On Machine';
	}
	if((int)$status === 4){
		return 'Plate Washing';
	}
	if((int)$status === 5){
		return 'Oven Baking';
	}
	if((int)$status === 2){
		return 'Completed';
	}
	return 'Pending';
}

$sort_order_column_value=' order by a.jd_id desc';
$filter_query=' WHERE 1 and a.order_status = 2  ';
if($search_safe !== ''){
	$filter_query .= " AND (
		a.jd_id LIKE '%".$search_safe."%'
		OR a.job_name LIKE '%".$search_safe."%'
		OR b.cust_name LIKE '%".$search_safe."%'
		OR c.name LIKE '%".$search_safe."%'
	) ";
}

$query82="SELECT count(a.jd_id) as rows_total  FROM job_order as a INNER JOIN customers as b on a.customer_id = b.cust_id INNER JOIN users as c on a.order_rec_by = c.id ".$filter_query." ".$sort_order_column_value;
$result82=mysqli_query($conn,$query82);
$data82=mysqli_fetch_array($result82);
$json_array['total']=$data82['rows_total'];


 
$fetchquery = "SELECT a.*,b.cust_name, c.name as userName FROM job_order as a INNER JOIN customers as b on a.customer_id = b.cust_id INNER JOIN users as c on a.order_rec_by = c.id ".$filter_query." ".$sort_order_column_value." Limit ".$index.",".$rows;
$runQuery = mysqli_query($conn,$fetchquery);
$i = 0;
while($dataFetch = mysqli_fetch_array($runQuery)) 
{
	$i++;
	$jd_id = $dataFetch['jd_id'];
	$order_rec_date = $dataFetch['order_rec_date'];
	$job_name = $dataFetch['job_name'];
	$cust_name = $dataFetch['cust_name'];
	$userName = $dataFetch['userName'];
	$order_status = $dataFetch['order_status'];
	$userName = $dataFetch['userName'];
	$action_btn = '-';

	$status = '<span class="completed-status-badge">'.job_order_status_label($order_status).'</span>';

	$action_btn = '<div class="completed-action-group">';
	$action_btn .= '<a class="completed-action-btn view" href="index.php?page=Jobs/view-job-order&id='.$jd_id.'" title="View Order"><i class="fa fa-eye"></i></a>';
	$action_btn .= '<button class="completed-action-btn details view_order" type="button" data-id="'.$jd_id.'" title="Quick Details"><i class="fa fa-file"></i></button>';
	$action_btn .= '<a class="completed-action-btn pdf" target="_blank" href="Jobs/job-card.php?ref='.$jd_id.'" title="Job Card PDF"><i class="fas fa-file-pdf"></i></a>';
	$action_btn .= '<button class="completed-action-btn whatsapp whatsapp-job-card" type="button" data-id="'.$jd_id.'" title="Send job card on WhatsApp" aria-label="Send job card on WhatsApp"><i class="fab fa-whatsapp"></i></button>';
	$action_btn .= '</div>';

	array_push($json_array_2,array(
		'order_code' => '<span class="completed-order-code">JD-'.$jd_id.'</span>',
		'dated' => date('d-M-Y',strtotime($order_rec_date)),
		'job_name' => $job_name,
		'customer' => $cust_name,
		'order_rec_by' => $userName,
		'status' => $status,
		'action' => $action_btn
	));
}




$json_array['rows']=$json_array_2;
echo json_encode($json_array);
?>
