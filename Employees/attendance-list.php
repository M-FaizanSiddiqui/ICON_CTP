<?php session_start();

// if(in_array("45",$_SESSION['login_Permisions']))
// {
require_once('../tcpdf/tcpdf.php');
include '../db_connect.php';

class MYPDF extends TCPDF 
{
	public function Header() 
	{
		$this->SetFont('helvetica', 'H', 16);
		$image_file = K_PATH_IMAGES.'logo.jpg';
		$this->Image($image_file, 10, 10, 35, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);

		$labels= '';
		$labels .= '<table border="0" cellpadding="1" width="100%" style="font-size:10px">';
		$labels .= '<tr>';
		$labels .= '<th><b>ICON Design:</b> Suite # 8, Plot # D-20/A, MOIN AKHTER ROAD,</th>';
		$labels .= '</tr>';

		$labels .= '<tr>';
		$labels .= '<th>S.I.T.E., Karachi-75700. (Pakistan).</th>';
		$labels .= '</tr>';

		$labels .= '<tr>';
		$labels .= '<th>PH: (021) 3256 4266 | (0331) 111 4266</th>';
		$labels .= '</tr>';
		$labels .= '</table>';

		$this->writeHTML($labels, true, 1, true, 1, '');
	}
}

$from_dt = $_POST['from_date'];
$to_dt = $_POST['to_date'];

	// $from_dt = date('Y-m-01');
	// $to_dt = date('Y-m-t');

$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		// set document information

$pdf->SetCreator(PDF_CREATOR);





$PDF_HEADER_LOGO_WIDTH = "20";
$PDF_HEADER_TITLE = "This is my Title";
$PDF_HEADER_STRING = "This is Header Part";
$pdf->SetHeaderData(PDF_HEADER_LOGO, $PDF_HEADER_LOGO_WIDTH, $PDF_HEADER_TITLE, $PDF_HEADER_STRING); 
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

$pdf->SetMargins(5, 14, 5);

$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);


$bMargin = $pdf->getBreakMargin();
$auto_page_break = $pdf->getAutoPageBreak();
$pdf->SetAutoPageBreak($auto_page_break, $bMargin);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
if (@file_exists(dirname(__FILE__).'/lang/eng.php')) 
{
	require_once(dirname(__FILE__).'/lang/eng.php');
	$pdf->setLanguageArray($l);
}




function searchForDt($id, $array,$st,$empId) {
	for($j =0; $j<count($array); $j++){
		if($array[$j][2] == $id && $array[$j][5]==$st && $array[$j][1]==$empId){
			return $j;
		}
	}
	return null;
}

$queryRec = "SELECT count(emp_id) as total_rec FROM employee WHERE emp_status = 0  AND emp_designation_id IN ('1','2','3','4')";
$resultRec = mysqli_query($conn,$queryRec);
$dataRec = mysqli_fetch_array($resultRec);
$tota_rec = $dataRec['total_rec'];
$pageCount = $tota_rec/5;


$limit=5;
for($kj=0; $kj<$pageCount; $kj++){

	$offset = $kj*5;

	$description_table = '<br><br><br><br><br>';
	$description_table .= '<table border="1" cellpadding="2">';
	$description_table .= '<tr style="background-color:lightgray;font-weight:bold;text-align:ceneter">';
	$description_table .= '<th style="width:68px" rowspan="2">Dateed</th>';
	$description_table .= '<th rowspan="2">Day</th>';


	$usr_mod_permisions=array();
	$queryCheck = "SELECT * FROM employee WHERE emp_status = 0  AND emp_designation_id IN ('1','2','3','4') order by emp_id asc limit ".$limit." OFFSET ".$offset." ";
	$resultCheck = mysqli_query($conn,$queryCheck);
	$this_row_count = mysqli_num_rows($resultCheck);
	while($dataEmp = mysqli_fetch_array($resultCheck)){
		$emp_id = $dataEmp['emp_id'];
		$emp_name = $dataEmp['emp_name'];
		$description_table .= '<th colspan="3">'.$emp_name.'</th>';

		$queryAtt = "SELECT * FROM attendance WHERE emp_id = ".$emp_id." AND dated >= '".$from_dt."' AND dated <= '".$to_dt."' AND del_status = 0 order by id DESC ";
		$resultAtt = mysqli_query($conn,$queryAtt);
		while($dataAtt = mysqli_fetch_array($resultAtt)){
			array_push($usr_mod_permisions,$dataAtt);
		}
	}
	if($this_row_count==4){
		$description_table .= '<th colspan="3">-</th>';
	}
	if($this_row_count==3){
		$description_table .= '<th colspan="3">-</th>';
		$description_table .= '<th colspan="3">-</th>';
	}
	if($this_row_count==2){
		$description_table .= '<th colspan="3">-</th>';
		$description_table .= '<th colspan="3">-</th>';
		$description_table .= '<th colspan="3">-</th>';
	}
	if($this_row_count==1){
		$description_table .= '<th colspan="3">-</th>';
		$description_table .= '<th colspan="3">-</th>';
		$description_table .= '<th colspan="3">-</th>';
		$description_table .= '<th colspan="3">-</th>';
	}

	$new_date = $from_dt;

	$checkInMissingCount = 0;
	$checkOutMissingCount = 0;
	$absentCount = 0;

	$tHour = 0;
	$tMin = 0;
	$emp_total_time = 0;
	$total_holidays = 0;
	$total_working_days = 0;



	$description_table .= '</tr>';
	$description_table .= '<tr style="background-color:lightgray;font-weight:bold;text-align:ceneter">';
	$description_table .= '<td>Check In</td>';
	$description_table .= '<td>Check Out</td>';
	$description_table .= '<td>Time</td>';
	$description_table .= '<td>Check In</td>';
	$description_table .= '<td>Check Out</td>';
	$description_table .= '<td>Time</td>';
	$description_table .= '<td>Check In</td>';
	$description_table .= '<td>Check Out</td>';
	$description_table .= '<td>Time</td>';
	$description_table .= '<td>Check In</td>';
	$description_table .= '<td>Check Out</td>';
	$description_table .= '<td>Time</td>';
	$description_table .= '<td>Check In</td>';
	$description_table .= '<td>Check Out</td>';
	$description_table .= '<td>Time</td>';

	$description_table .= '</tr>';

	$diff = abs(strtotime($to_dt) - strtotime($from_dt));
	$daysDiff = floor($diff / (60*60*24)) +1;

	$new_date = $from_dt;


	$gazated_holidays =array();
	$queryHolidays = "SELECT * FROM holidays WHERE holiday_date >= '".$from_dt."' AND  holiday_date <= '".$to_dt."' AND effective = 0 ";
	$resultHolidays = mysqli_query($conn,$queryHolidays);
	while($dataHolidays = mysqli_fetch_array($resultHolidays)){
		array_push($gazated_holidays,$dataHolidays);
	}

	for($i=1; $i<=$daysDiff; $i++){

		$DayOfWeekNumber = date("w",strtotime($new_date));
		$total_holidays=0;
		$total_working_days=0;

		$gazated_holiday_count = 0;
		$gazated_holiday_name = "";
		for($j =0; $j<count($gazated_holidays); $j++){
			if($gazated_holidays[$j][2] == $new_date){
				$gazated_holiday_name = $gazated_holidays[$j][1];
				$gazated_holiday_count++;
				$total_holidays++;
				$total_working_days--;
			}
		}

		
		$dayName = "";
		if($DayOfWeekNumber == 0){
			$dayName = "Sunday";
			$total_holidays++;
		}else if($DayOfWeekNumber == 1){
			$dayName = "Monday";
			$total_working_days++;
		}else if($DayOfWeekNumber == 2){
			$dayName = "Tuesday";
			$total_working_days++;
		}else if($DayOfWeekNumber == 3){
			$dayName = "Wednesday";
			$total_working_days++;
		}else if($DayOfWeekNumber == 4){
			$dayName = "Thursday";
			$total_working_days++;
		}else if($DayOfWeekNumber == 5){
			$dayName = "Friday";
			$total_working_days++;
		}else if($DayOfWeekNumber == 6){
			$dayName = "Satuday";
			$total_working_days++;
		}


		$description_table .= '<tr style="text-align:ceneter">';
		$description_table .= '<td style="width:68px">'.date('d-M-Y',strtotime($new_date)).'</td>';
		$description_table .= '<td>'.$dayName.'</td>';



		$queryCheck = "SELECT * FROM employee WHERE emp_status = 0  AND emp_designation_id IN ('1','2','3','4') order by emp_id asc limit ".$limit." OFFSET ".$offset." ";
		$resultCheck = mysqli_query($conn,$queryCheck);
		$this_row_count = mysqli_num_rows($resultCheck);
		while($dataEmp = mysqli_fetch_array($resultCheck)){
			$emp_id = $dataEmp['emp_id'];

			$st = "0";
			$checkInInd = searchForDt($new_date, $usr_mod_permisions,$st,$emp_id);

			$checkIn = "Missing";
			$dateTimeChkIn = "";
			$checkInColor = "#ff5151";
			if($checkInInd != ""){
				$dateTimeChkIn = $usr_mod_permisions[$checkInInd][4];
				$checkIn = date('h:i A',strtotime($dateTimeChkIn));
				$checkInColor = "#7be77b";
			}else{
				$checkInMissingCount++;
			}


			$st = "1";
			$dateTimeChkOut ="";
			$checkOutInd = searchForDt($new_date, $usr_mod_permisions,$st,$emp_id);
			$checkOut = "Missing";
			$checkOutColor = "#ff5151";
			if($checkOutInd != ""){
				$dateTimeChkOut = $usr_mod_permisions[$checkOutInd][4];
				$checkOut = date('h:i A',strtotime($dateTimeChkOut));
				$checkOutColor = "#7be77b";
			}else{


				$foundCheckOut = 0;

				$mythisDate = date('Y-m-d', strtotime($new_date. ' + 1 days')); 

				$q2 = "SELECT * FROM attendance WHERE emp_id = ".$emp_id." AND dated >= '".$mythisDate."' AND dated <= '".$mythisDate."' AND del_status = 0";
				$r1Att = mysqli_query($conn,$q2);
				while($d1Att = mysqli_fetch_array($r1Att)){

					$thisDtss = $d1Att['dateTime'];

					if( date('H:i A',strtotime($thisDtss)) <=  date('H:i A',strtotime($mythisDate . '05:00:00'))){

						$dateTimeChkOut = $thisDtss;
						$checkOut = date('h:i A',strtotime($thisDtss));
						$checkOutColor = "#7ee9d3";
						$checkOutInd = 1;

						$foundCheckOut++;

					}

				}


				if($foundCheckOut==0){
					$checkOutMissingCount++;
				}
				// $checkOutMissingCount++;
			}

			$totalTime = "0";
			if($checkOutInd != "" && $checkInInd != ""){
				$expiry_time = new DateTime($dateTimeChkOut);
				$current_date = new DateTime($dateTimeChkIn);
				$diff = $expiry_time->diff($current_date);
				$totalTime = $diff->format('%Hhr %Imin'); 

				$tHour += $diff->format('%H'); 
				$tMin += $diff->format('%I'); 
			}

			if($checkInInd == "" && $checkOutInd == ""){
				$absentCount++;
			}


			if($DayOfWeekNumber == 0){
				if($checkIn == "Missing" && $checkOut == "Missing"){
					$description_table .= '<td colspan="3"style="background-color:lightpink;border:1px solid gray"><b>Holiday</b></td>';
				}else{
					$description_table .= '<td style="color:black;background-color:lightpink;border:1px solid gray">'.$checkIn.'</td>';
					$description_table .= '<td style="color:black;background-color:lightpink;border:1px solid gray">'.$checkOut.'</td>';
					$description_table .= '<td style="border:1px solid gray;background-color:lightpink"  class="text-center">'.$totalTime.'</td>';
				}
			}
			else if($gazated_holiday_name != ""){
				if($checkIn == "Missing" && $checkOut == "Missing"){
					$description_table .= '<td colspan="3"style="background-color:lightpink;border:1px solid gray"><b>'.$gazated_holiday_name.'</b></td>';
				}else{
					$description_table .= '<td style="color:black;background-color:lightpink;border:1px solid gray">'.$checkIn.'</td>';
					$description_table .= '<td style="color:black;background-color:lightpink;border:1px solid gray">'.$checkOut.'</td>';
					$description_table .= '<td style="border:1px solid gray;background-color:lightpink"  class="text-center">'.$totalTime.'</td>';
				}
			}
			else{
				$description_table .= '<td style="color:black;background-color:'.$checkInColor.';border:1px solid gray">'.$checkIn.'</td>';
				$description_table .= '<td style="color:black;background-color:'.$checkOutColor.';border:1px solid gray">'.$checkOut.'</td>';
				$description_table .= '<td style="border:1px solid gray"  class="text-center">'.$totalTime.'</td>';
			}			
		}
		
		if($this_row_count==4){
			$description_table .= '<td style="color:black;border:1px solid gray"></td>';
			$description_table .= '<td style="color:black;border:1px solid gray"></td>';
			$description_table .= '<td style="border:1px solid gray" class="text-center">-</td>';
		}
		if($this_row_count==3){
			$description_table .= '<td style="color:black;border:1px solid gray"></td>';
			$description_table .= '<td style="color:black;border:1px solid gray"></td>';
			$description_table .= '<td style="border:1px solid gray" class="text-center">-</td>';
			$description_table .= '<td style="color:black;border:1px solid gray"></td>';
			$description_table .= '<td style="color:black;border:1px solid gray"></td>';
			$description_table .= '<td style="border:1px solid gray" class="text-center">-</td>';
		}
		if($this_row_count==2){
			$description_table .= '<td style="color:black;border:1px solid gray"></td>';
			$description_table .= '<td style="color:black;border:1px solid gray"></td>';
			$description_table .= '<td style="border:1px solid gray" class="text-center">-</td>';
			$description_table .= '<td style="color:black;border:1px solid gray"></td>';
			$description_table .= '<td style="color:black;border:1px solid gray"></td>';
			$description_table .= '<td style="border:1px solid gray" class="text-center">-</td>';
			$description_table .= '<td style="color:black;border:1px solid gray"></td>';
			$description_table .= '<td style="color:black;border:1px solid gray"></td>';
			$description_table .= '<td style="border:1px solid gray" class="text-center">-</td>';
		}
		if($this_row_count==1){
			$description_table .= '<td style="color:black;border:1px solid gray"></td>';
			$description_table .= '<td style="color:black;border:1px solid gray"></td>';
			$description_table .= '<td style="border:1px solid gray" class="text-center">-</td>';
			$description_table .= '<td style="color:black;border:1px solid gray"></td>';
			$description_table .= '<td style="color:black;border:1px solid gray"></td>';
			$description_table .= '<td style="border:1px solid gray" class="text-center">-</td>';
			$description_table .= '<td style="color:black;border:1px solid gray"></td>';
			$description_table .= '<td style="color:black;border:1px solid gray"></td>';
			$description_table .= '<td style="border:1px solid gray" class="text-center">-</td>';
			$description_table .= '<td style="color:black;border:1px solid gray"></td>';
			$description_table .= '<td style="color:black;border:1px solid gray"></td>';
			$description_table .= '<td style="border:1px solid gray" class="text-center">-</td>';
		}


		$description_table .= '</tr>';

		$new_date = date('Y-m-d', strtotime($new_date. ' + 1 days')); 
	}

	$description_table .= '</table>';
	$pdf->AddPage('L', 'A4');
	$pdf->SetMargins(5, 15, 5);
	$pdf->SetFont('helvetica', '', 8);

	$pdf->writeHTML($description_table, true, 1, true, 1, '');
}



	// $description_table.='<br><br><br><span style="color:blue;font-size:12px"><b>Note:</b></span> <span> This Report used only for Internal Purpose.</span>';




$systemdate=date('Y-m-d H:i:s');
$para='<br><br><br><br><br><span style="color:black;font-size:12px;text-align:center;">This Report is printed through System at <span style="color:blue;"><strong>'.$systemdate.'</strong></span></span>';



	// $pdf->writeHTML($para, true, 1, true, 1, '');

$file_name = 'Attendance List.pdf';

$pdf->Output($file_name, 'I');

