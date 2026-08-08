<?php
include '../db_connect.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$data = json_decode(file_get_contents("php://input"),true);

$res = 0;
$res_result = 1;
for($ii=0; $ii<count($data); $ii++){
	$emp_id = $data[$ii]['emp_id'];
	$dated = $data[$ii]['dated'];
	$time = $data[$ii]['time'];
	$dateTime = $data[$ii]['dateTime'];
	$status = $data[$ii]['status'];

	$queryIns = "INSERT INTO attendance SET ";
	$queryIns .= " emp_id = '".$emp_id."' ";
	$queryIns .= ", dated = '".$dated."' ";
	$queryIns .= ", time = '".$time."' ";
	$queryIns .= ", dateTime = '".$dateTime."' ";
	$queryIns .= ", status = '".$status."' ";

	$res = mysqli_query($conn,$queryIns);
	if(!$res){
		$res_result = 0;
	}
}


if($res_result){
	echo json_encode(array('message' => 'Attendance Inserted.','status' => true));
}else{
	echo json_encode(array('message' => 'No Record Found.','status' => false));
}

?>