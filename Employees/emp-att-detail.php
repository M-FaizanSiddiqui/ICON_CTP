<?php require_once(__DIR__.'/../includes/pdf_runtime.php'); icon_pdf_session_start();

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

//$from_dt = date('2023-12-01');
//$to_dt = date('2023-12-t');

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

$pdf->SetMargins(10, 15, 10);

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



$usr_mod_permisions=array();

$queryAtt = "SELECT * FROM attendance WHERE dated >= '".$from_dt."' AND dated <= '".$to_dt."' AND del_status = 0 order by id DESC ";
$resultAtt = mysqli_query($conn,$queryAtt);
while($dataAtt = mysqli_fetch_array($resultAtt)){
	array_push($usr_mod_permisions,$dataAtt);
}

$gazated_holidays =array();
$queryHolidays = "SELECT * FROM holidays WHERE holiday_date >= '".$from_dt."' AND  holiday_date <= '".$to_dt."' AND effective = 0 ";
$resultHolidays = mysqli_query($conn,$queryHolidays);
while($dataHolidays = mysqli_fetch_array($resultHolidays)){
	array_push($gazated_holidays,$dataHolidays);
}


$description_table = '';

$queryRec = "SELECT a.*,b.des_name, c.shift_name, c.shift_start, c.shift_end, c.total_hours, c.grace_time FROM employee as a INNER JOIN designations as b on a.emp_designation_id = b.des_id INNER JOIN employee_shifts as c on a.emp_shift_id = c.shift_id WHERE a.emp_status = 0  AND emp_designation_id IN ('1','2','3','4')  ";
$resultRec = mysqli_query($conn,$queryRec);
while($dataRec = mysqli_fetch_array($resultRec)){

	$emp_id = $dataRec['emp_id'];
	$emp_name = $dataRec['emp_name'];
	$emp_ph_no = $dataRec['emp_ph_no'];
	$emp_email = $dataRec['emp_email'];
	$emp_designation_id = $dataRec['emp_designation_id'];
	$emp_shift_id = $dataRec['emp_shift_id'];
	$sal_type_id = $dataRec['sal_type_id'];
	$des_name = $dataRec['des_name'];
	$shift_name = $dataRec['shift_name'];
	$shift_start = $dataRec['shift_start'];
	$shift_end = $dataRec['shift_end'];
	$total_hours = $dataRec['total_hours'];
	$grace_time = $dataRec['grace_time'];

	$total_hours_working = $total_hours;

	$description_table ='<br>';





	$description_table .= '<table border="0" cellpadding="2" width="100%" style="font-size:11px">';
	
	$description_table .= '<tr style="text-align:right">';
	$description_table .= '<td style="font-size:14px;"><span style="color:red">Employee Monthly Attendance</span></td>';
	$description_table .= '</tr>';
	$description_table .= '<tr style="font-weight:bold;text-align:right">';
	$description_table .= '<td style="font-size:11px;">'.date('d-M-Y',strtotime($from_dt)).' To '.date('d-M-Y',strtotime($to_dt)).'</td>';
	$description_table .= '</tr>';

	$description_table .= '</table>';


	$description_table .= '<br><table border="0" cellpadding="1" width="100%" style="font-size:11px">';
	
	$description_table .= '<tr>';
	$description_table .= '<td>Emp Id: <span style="color:blue">'.$emp_id.'</span></td>';
	$description_table .= '<td>Designation: <span style="color:blue">'.$des_name.'</span></td>';
	$description_table .= '</tr>';

	$description_table .= '<tr>';
	$description_table .= '<td>Emp Name: <span style="color:blue">'.$emp_name.'</span></td>';
	$description_table .= '<td>Shift: <span style="color:blue">'.date('h:i A',strtotime($shift_start)).' To '.date('h:i A',strtotime($shift_end)).'</span></td>';
	$description_table .= '</tr>';

	$description_table .= '</table>';

	$description_table .= '<br><br>';
	$description_table .= '<table border="1" cellpadding="2">';

	$description_table .= '<tr style="background-color:lightgray;font-weight:bold;text-align:ceneter">';
	$description_table .= '<th style="width:11%">Dateed</th>';
	$description_table .= '<th style="width:10%">Time In</th>';
	$description_table .= '<th style="width:10%">Time Out</th>';
	$description_table .= '<th style="width:12%">Standard <br> Work Hours</th>';
	$description_table .= '<th style="width:12%">Actual <br> Work Hours</th>';
	$description_table .= '<th style="width:10%">Late Arrival</th>';
	$description_table .= '<th style="width:10%">Early Departure</th>';
	$description_table .= '<th style="width:10%">Excess Hours</th>';
	$description_table .= '<th style="width:15%">Remarks</th>';
	$description_table .= '</tr>';



	$diff = abs(strtotime($to_dt) - strtotime($from_dt));
	$daysDiff = floor($diff / (60*60*24)) +1;

	$new_date = $from_dt;
	$counter = 0;
	$checkInMissingCount = 0;
	$checkOutMissingCount = 0;
	$absentCount = 0;

	$emp_total_time = 0;
	$total_holidays = 0;
	$total_working_days = 0;

	$total_hours_sum = 0;
	$totalTime_sum = "";
	$actHourSum = 0;
	$actMinSum = 0;

	$lateTime_sum=0;
	$lateHourSum = 0;
	$lateMinSum = 0;

	$excessTime_sum=0;
	$excessHourSum = 0;
	$excessMinSum = 0;

	$earlyTime_sum=0;
	$earlyHourSum = 0;
	$earlyMinSum = 0;

	// summary varuables
	$SumWorkNonWorking = 0;
	$SumOnTime = 0;
	$SumPresentDay = 0;
	$SumOffDays = 0;
	$SumAbsentDays = 0;
	$SumEarlyDep = 0;
	$SumLateArrival = 0;
	$SumTotalDays = $daysDiff;

	$total_holidays=0;
	$total_working_days=0;

	$overtimeNightShift = 0;

	$remarks = "-";
	for($i=1; $i<=$daysDiff; $i++){

		$tHour = 0;
		$tMin = 0;
		$counter++;
		$DayOfWeekNumber = date("w",strtotime($new_date));

		$total_hours_working = $total_hours;

		$gazated_holiday_count = 0;
		$gazated_holiday_name = "";
		for($j =0; $j<count($gazated_holidays); $j++){
			if($gazated_holidays[$j][2] == $new_date){
				$gazated_holiday_name = $gazated_holidays[$j][1];
				$gazated_holiday_count++;
				$total_holidays++;
				$total_working_days--;
				$total_hours_working = 0;
			}
		}

		
		$dayName = "";
		
		if($DayOfWeekNumber == 0){
			$dayName = "Sunday";
			$total_holidays++;
			$total_hours_working = 0;
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


		$st = "0";
		$checkInInd = searchForDt($new_date, $usr_mod_permisions,$st,$emp_id);

		$remarks = "-";
		$remarksColor = "";
		$checkIn = "Missing";
		$dateTimeChkIn = "";
		$checkInColor = "";
		// $checkInColor = "#ff6c6c";
		if($checkInInd != ""){
			$dateTimeChkIn = $usr_mod_permisions[$checkInInd][4];
			$checkIn = date('h:i A',strtotime($dateTimeChkIn));
			// $checkInColor = "#7be77b";
		}else{
			if($gazated_holiday_name== "" && $DayOfWeekNumber != 0){
				$checkInMissingCount++;
				$remarks = 'Attendance Missing';
				$remarksColor = "#ff6c6c";
				$checkInColor = "#ff6c6c";
			}
		}


		$st = "1";
		$dateTimeChkOut ="";
		$checkOutInd = searchForDt($new_date, $usr_mod_permisions,$st,$emp_id);
		$checkOut = "Missing";
		$checkOutColor = "";
		// $checkOutColor = "#ff6c6c";
		if($checkOutInd != ""){
			$dateTimeChkOut = $usr_mod_permisions[$checkOutInd][4];
			$checkOut = date('h:i A',strtotime($dateTimeChkOut));
			// $checkOutColor = "#7be77b";
		}else{
			if($gazated_holiday_name== "" && $DayOfWeekNumber != 0){



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
					$remarks = 'Attendance Missing';
					$remarksColor = "#ff6c6c";
					$checkOutColor = "#ff6c6c";
				}




				
			}
		}

		$totalTime = "0";
		if($checkOutInd != "" && $checkInInd != ""){
			$expiry_time = new DateTime($dateTimeChkOut);
			$current_date = new DateTime($dateTimeChkIn);
			$diff = $expiry_time->diff($current_date);
			$totalTime = $diff->format('%Hhr %Imin');


			$tHour += $diff->format('%H');
			$tMin += $diff->format('%I'); 

			$actHourSum += $diff->format('%H');
			$actMinSum += $diff->format('%I'); 
		}


		if($checkOutInd != "" || $checkInInd != ""){
			$SumPresentDay++;
		}





		if($checkInInd == "" && $checkOutInd == "" && $gazated_holiday_name== "" && $DayOfWeekNumber != 0){
			$absentCount++;
		}

		$late_arrival = "-";
		$lateColor = "";
		if($checkIn != "Missing"){
			
			$shift_start_new = date('h:'.$grace_time,strtotime($shift_start));
			$late_in_mints = ((strtotime($checkIn) - strtotime($shift_start_new))/3600) * 60;
			$shiftStDt = date('Y-m-d '.$shift_start_new, strtotime($new_date));
			$expiry_time1 = new DateTime($dateTimeChkIn);
			$current_date1 = new DateTime($shiftStDt);
			$diff1 = $expiry_time1->diff($current_date1);


			if($late_in_mints > 0){
				$lateColor = "#ff6c6c";
				$late_arrival = $diff1->format('%Hhr %Imin'); 
				if($remarks == "-"){
					$remarks = 'Late Arrival';
				}
				$remarksColor = "#ff6c6c";

				$lateHourSum += $diff1->format('%H');
				$lateMinSum += $diff1->format('%I');

				$SumLateArrival++;
			}

			// $late_arrival = $late_in_mints;
			// else{
			// 	$lateColor = "#7be77b";
			// }
		}

		$shiftEndDt ="";
		$early_dep_mints = "";
		$earlyColor = "";
		$earyly_dep = "-";
		if($checkOut != "Missing"){
			$early_dep_mints = ((strtotime($shift_end) - strtotime($checkOut))/3600) * 60;


			$shiftEndDt = date('Y-m-d '.$shift_end, strtotime($new_date));
			$expiry_time2 = new DateTime($dateTimeChkOut);
			$current_date2 = new DateTime($shiftEndDt);
			$diff2 = $current_date2->diff($expiry_time2);

			// echo $early_dep_mints;
			// echo '<br>';


			$tHour1 = $diff->format('%H');


			$hours_new1 = intdiv($tHour1, 60);
			// $total_minutes1 = ($totalTime % 60);
			// $total_hoursActSum1 = $actHourSum+$hours_new1;
			// $totalTime_sum = $total_hoursActSum1.'hr '.$total_minutes1.'min';

			// if($tHour1 < 10){
			// 	// if($early_dep_mints > $grace_time){
			// 	$earyly_dep = $diff2->format('%Hhr %Imin'); 
			// 	$earlyColor = "#ff6c6c";

			// 	if($remarks == "-"){
			// 		$remarks = 'Early Departure';
			// 	}
			// 	$remarksColor = "#ff6c6c";

			// 	$earlyHourSum += $diff2->format('%H'); 
			// 	$earlyMinSum += $diff2->format('%I'); 

			// 	$SumEarlyDep++;
			// }
			// else{
			// 	$earlyColor = "#7be77b";
			// }
		}


		$excessColor = "";
		$excess_time = "-";
		if($checkOutInd != "" && $checkInInd != ""){
			$excess_hour = $tHour - $total_hours_working;
			$excess_min = $tMin;

			if($excess_hour>0){
				$excess_time = $excess_hour.'h '.$excess_min.'m';
				$excessColor = "#7be77b";

				if($remarks == "-"){
					$remarks = 'Overtime';
				}

				$remarksColor = "#7be77b";

				$excessHourSum += $excess_hour;
				$excessMinSum += $excess_min;
			}
			else if($excess_min>0 && $excess_hour == 0){
				$excess_time = $excess_min.'m';
				$excessColor = "#7be77b";

				if($remarks == "-"){
					$remarks = 'Overtime';
				}
				$remarksColor = "#7be77b";

				$excessMinSum += $excess_min;
			}else{
				$earyly_dep = $excess_hour.'h '.$excess_min.'m';

				$earlyHourSum += $excess_hour; 
				$earlyMinSum += $excess_min; 

				$SumEarlyDep++;
				$remarksColor = "#ff6c6c";

				if($remarks == "-"){
					$remarks = 'Early Departure';
				}
			}
		}

		 //// Overtime calc Night shift

		$st = "4";
		$OverTimeInInd = searchForDt($new_date, $usr_mod_permisions,$st,$emp_id);


		$OverTimeInColor = "#ff5151";
		if($OverTimeInInd != ""){
			$OverTimeInInd;
			$dateTimeOverTimeIn = $usr_mod_permisions[$OverTimeInInd][4];
			$OverTimeIn = date('h:i A',strtotime($dateTimeOverTimeIn));
			$OverTimeInColor = "#7be77b";
		}

		$st = 5;
		$OverTimeOutInd = searchForDt($new_date, $usr_mod_permisions,$st,$emp_id);

		$OverTimeInColor = "#ff5151";
		if($OverTimeOutInd != ""){
			$dateTimeOverTimeIn = $usr_mod_permisions[$OverTimeOutInd][4];
			$OverTimeOut = date('h:i A',strtotime($dateTimeOverTimeIn));
			$OverTimeInColor = "#7be77b";
		}

		$totalTimeNight = 0;
		if($OverTimeInInd != "" && $OverTimeOutInd != ""){
			$expiry_time = new DateTime($OverTimeOut);
			$current_date = new DateTime($OverTimeIn);
			$diff = $expiry_time->diff($current_date);
			$totalTimeNight = $diff->format('%Hhr %Imin'); 
	        // $totalTime = $diff->format('%H:%I:%S');

			$tHour += $diff->format('%H'); 
			$tMin += $diff->format('%I'); 

			$actHourSum += $diff->format('%H');
			$actMinSum += $diff->format('%I'); 
		}





		$total_hours_sum += $total_hours_working;

		$description_table .= '<tr>';



		if($DayOfWeekNumber != 0 && $gazated_holiday_name == ''){
			$description_table .= '<td style="text-align:center">'.date('d-M-Y',strtotime($new_date)).'</td>';
			$description_table .= '<td style="text-align:center;background-color:'.$checkInColor.'">'.$checkIn.'</td>';
			$description_table .= '<td style="text-align:center;background-color:'.$checkOutColor.'">'.$checkOut.'</td>';
			$description_table .= '<td style="text-align:center">'.$total_hours_working.'</td>';
			$description_table .= '<td style="text-align:center">'.$totalTime.'</td>';
			$description_table .= '<td style="text-align:center;background-color:'.$lateColor.'">'.$late_arrival.'</td>';
			$description_table .= '<td style="text-align:center;background-color:'.$earlyColor.'">'.$earyly_dep.'</td>';
			$description_table .= '<td style="text-align:center;background-color:'.$excessColor.'">'.$excess_time.'</td>';
			$description_table .= '<td style="text-align:center;background-color:'.$remarksColor.'">'.$remarks.'</td>';
			$description_table .= '</tr>';
		}else{
			$description_table .= '<td style="text-align:center;background-color:pink">'.date('d-M-Y',strtotime($new_date)).'</td>';
			if($checkIn != "Missing" || $checkOut != "Missing"){
				$SumWorkNonWorking++;
				$description_table .= '<td style="text-align:center;background-color:pink">'.$checkIn.'</td>';
				$description_table .= '<td style="text-align:center;background-color:pink">'.$checkOut.'</td>';

				if($gazated_holiday_name == ''){
					$description_table .= '<td style="text-align:center;background-color:pink">Holiday</td>';
				}else{
					$description_table .= '<td style="text-align:center;background-color:pink">'.$gazated_holiday_name.'</td>';
				}

				$description_table .= '<td style="text-align:center;background-color:pink">'.$totalTime.'</td>';
				$description_table .= '<td style="text-align:center;background-color:pink">'.$late_arrival.'</td>';
				$description_table .= '<td style="text-align:center;background-color:pink">'.$earyly_dep.'</td>';
				$description_table .= '<td style="text-align:center;background-color:pink">'.$excess_time.'</td>';
				$description_table .= '<td style="text-align:center;background-color:pink">Overtime</td>';
			}else{

				// $description_table .= '<td style="text-align:center;background-color:pink">Holiday</td>';
				// $description_table .= '<td style="text-align:center;background-color:pink">Holiday</td>';
				if($gazated_holiday_name == ''){
					$description_table .= '<td colspan="8" style="text-align:center;background-color:pink">WeekEnd Holiday</td>';
				}else{
					$description_table .= '<td colspan="8" style="text-align:center;background-color:pink">'.$gazated_holiday_name.'</td>';
				}


				// $description_table .= '<td style="text-align:center;background-color:pink">'.$totalTime.'</td>';
				// $description_table .= '<td style="text-align:center;background-color:pink">'.$late_arrival.'</td>';
				// $description_table .= '<td style="text-align:center;background-color:pink">'.$earyly_dep.'</td>';
				// $description_table .= '<td style="text-align:center;background-color:pink">'.$excess_time.'</td>';
				// $description_table .= '<td style="text-align:center;background-color:pink">Holiday</td>';
			}


			$description_table .= '</tr>';


			
		}


		if($OverTimeInInd != "" && $OverTimeOutInd != ""){

			$description_table .= '<tr>';
			$description_table .= '<td style="text-align:center;background-color:white">'.date('d-M-Y',strtotime($new_date)).'</td>';
			if($OverTimeIn != "Missing" || $OverTimeOut != "Missing"){
				$overtimeNightShift++;
				$description_table .= '<td style="text-align:center;background-color:white">'.$OverTimeIn.'</td>';
				$description_table .= '<td style="text-align:center;background-color:white">'.$OverTimeOut.'</td>';
				$description_table .= '<td style="text-align:center;background-color:white">-</td>';

				$description_table .= '<td style="text-align:center;background-color:white">'.$totalTimeNight.'</td>';
				$description_table .= '<td style="text-align:center;background-color:white">-</td>';
				$description_table .= '<td style="text-align:center;background-color:white">-</td>';
				$description_table .= '<td style="text-align:center;background-color:white">-</td>';
				$description_table .= '<td style="text-align:center;background-color:orange">Overtime Night Shift</td>';
				$description_table .= '</tr>';

			}
		}




		$new_date = date('Y-m-d', strtotime($new_date. ' + 1 days')); 

		// Actual Time Sum 
		$hours_new = intdiv($actMinSum, 60);
		$total_minutes = ($actMinSum % 60);
		$total_hoursActSum = $actHourSum + $hours_new;
		$totalTime_sum = $total_hoursActSum.'hr '.$total_minutes.'min';


		// Late Arrival Sum
		$hours_new_late = intdiv($lateMinSum, 60);
		$total_minutes_late = ($lateMinSum % 60);
		$total_hours_late = $lateHourSum+$hours_new_late;
		$lateTime_sum = $total_hours_late.'hr '.$total_minutes_late.'min';


		// Late Arrival Sum
		$hours_new_exccess = intdiv($excessMinSum, 60);
		$total_minutes_excess = ($excessMinSum % 60);
		$total_hours_excess = $excessHourSum+$hours_new_exccess;
		$excessTime_sum = $total_hours_excess.'hr '.$total_minutes_excess.'min';


		// Late Arrival Sum
		$hours_new_early = intdiv($earlyMinSum, 60);
		$total_minutes_early = ($earlyMinSum % 60);
		$total_hours_early = $earlyHourSum+$hours_new_early;
		$earlyTime_sum = $total_hours_early.'hr '.$total_minutes_early.'min';

	}



	$description_table .= '<tr>';
	$description_table .= '<td colspan="3" style="text-align:center"></td>';
	$description_table .= '<td style="text-align:center">'.$total_hours_sum.' h</td>';
	$description_table .= '<td style="text-align:center">'.$totalTime_sum.'</td>';
	$description_table .= '<td style="text-align:center">'.$lateTime_sum.'</td>';
	$description_table .= '<td style="text-align:center">'.$earlyTime_sum.'</td>';
	$description_table .= '<td style="text-align:center">'.$excessTime_sum.'</td>';
	$description_table .= '<td></td>';
	$description_table .= '</tr>';


	$description_table .= '</table>';



	$description_table .= '<br><br><br><table cellpadding="2">';

	$description_table .= '<tr>';
	$description_table .= '<td colspan="4"><b>Attendance Summary</b></td>';
	$description_table .= '</tr>';


	$description_table .= '<tr>';
	$description_table .= '<td><span>Total Days:</span> '.$SumTotalDays.'</td>';
	$description_table .= '<td><span>Working Days:</span> '.$total_working_days.'</td>';
	$description_table .= '<td><span>Off Days:</span> '.$total_holidays.'</td>';
	$description_table .= '<td></td>';
	$description_table .= '</tr>';


	$absentCounting = $absentCount;

	$description_table .= '<tr>';
	$description_table .= '<td><span>Present Days:</span> '.$SumPresentDay.'</td>';
	$description_table .= '<td><span>Absent Days:</span> '.$absentCounting.' </td>';
	$description_table .= '<td><span>Late Arrival:</span> '.$SumLateArrival.'</td>';
	$description_table .= '<td><span>Early Departure:</span> '.$SumEarlyDep.'</td>';
	$description_table .= '</tr>';

	$description_table .= '<tr>';
	$description_table .= '<td><span>Works on Non Working Day:</span> '.$SumWorkNonWorking.'</td>';
	$description_table .= '<td><span>Night Shift: </span>'.$overtimeNightShift.'</td>';
	$description_table .= '<td></td>';
	$description_table .= '<td></td>';
	$description_table .= '</tr>';


	$description_table .= '</table>';



	$pdf->AddPage('P', 'A4');
	if($counter == 1){		
		$pdf->SetMargins(10, 10, 10);
	}else{
		$pdf->SetMargins(10, 20, 10);
	}
	$pdf->SetFont('helvetica', '', 8);

	$pdf->writeHTML($description_table, true, 1, true, 1, '');

}
// $tota_rec = $dataRec['total_rec'];
// $pageCount = $tota_rec/5;



$systemdate=date('Y-m-d H:i:s');
$para='<br><br><br><br><br><span style="color:black;font-size:12px;text-align:center;">This Report is printed through System at <span style="color:blue;"><strong>'.$systemdate.'</strong></span></span>';



$file_name = 'Attendance List.pdf';

$pdf->Output($file_name, 'I');

