<?php
include '../db_connect.php';

// $url = "https://reqres.in/api/users";
// $url = "localhost:8080/ICON-CTP/Employees/add-att-emp.php";
$url = "https://icon.net.pk/ICON-CTP/Employees/add-att-emp.php";

$ch = curl_init();


$att_comp_array=array();

$queryAtt = "SELECT * FROM attendance WHERE live = 0";
$resultAtt = mysqli_query($conn,$queryAtt);
while($dataAtt = mysqli_fetch_array($resultAtt)){
	$emp_id = $dataAtt['emp_id'];
	$dated = $dataAtt['dated'];
	$time = $dataAtt['time'];
	$dateTime = $dataAtt['dateTime'];
	$status = $dataAtt['status'];

	$data_array = array(
		'emp_id' => $emp_id,
		'dated' => $dated,
		'time' => $time,
		'dateTime' => $dateTime,
		'status' => $status
	);
	array_push($att_comp_array,$data_array);
}



$json = json_encode($att_comp_array);

curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$resp = curl_exec($ch);

if($e = curl_error($ch)){
	echo $e;
}
else{
	$decoded = json_decode($resp);


	foreach ($decoded as $key => $value) {
		echo $key.'--'.$value.'<br>';
		if($key == "status"){
			if($value == true){
				$queryUp = "UPDATE attendance SET live = 1 ";
				$resUp = mysqli_query($conn,$queryUp);
			}
		}
	}
}

curl_close($ch);
?>