<?php

function activityLog($log_description,$user_id,$conn){
	$queryLog = " INSERT INTO activity_log set log_desc = '".$log_description."' ";
	$queryLog .= ", user_id = ".$user_id;

	$query1 = $conn->query($queryLog);
	return $query1;
}

function resolve_account_no($account_ref,$conn){
	$account_ref = (int)$account_ref;
	if($account_ref <= 0){
		return 0;
	}

	$query_acc = "SELECT account_no FROM accounts WHERE account_no = ".$account_ref." AND del_status = 0 LIMIT 1";
	$result_acc = mysqli_query($conn,$query_acc);
	if($result_acc && mysqli_num_rows($result_acc) > 0){
		$data_acc = mysqli_fetch_array($result_acc);
		return (int)$data_acc['account_no'];
	}

	$query_acc = "SELECT account_no FROM accounts WHERE acc_id = ".$account_ref." AND del_status = 0 LIMIT 1";
	$result_acc = mysqli_query($conn,$query_acc);
	if($result_acc && mysqli_num_rows($result_acc) > 0){
		$data_acc = mysqli_fetch_array($result_acc);
		return (int)$data_acc['account_no'];
	}

	return 0;
}

function generate_voucher($v_type_id,$target_account,$sec_acc,$trans_dated,$amount,$ref_column,$ref_id,$narration,$created_by,$conn){

	$query_get_voucher_no = "SELECT max(voucher_no) as voucher_no FROM vouchers WHERE v_type_id = ".$v_type_id;
	$result_voucher = mysqli_query($conn,$query_get_voucher_no);
	$data_voucher = mysqli_fetch_array($result_voucher);
	$voucher_no = $data_voucher['voucher_no'];
	if($voucher_no == ""){
		$voucher_no = 10000;
	}
	$voucher_no++;

	if($ref_column == "customer_payment"){
		$query_get_acc = "SELECT acc_id from customers WHERE cust_id = ".$target_account;
		$result_get_acc = mysqli_query($conn,$query_get_acc);
		$data_get_acc = mysqli_fetch_array($result_get_acc);
		$acc_id = $data_get_acc['acc_id'];
	}
	if($ref_column == "supplier_payment" || $ref_column == "inventory_received"){
		$query_get_acc = "SELECT acc_id from suppliers WHERE supp_id = ".$target_account;
		$result_get_acc = mysqli_query($conn,$query_get_acc);
		$data_get_acc = mysqli_fetch_array($result_get_acc);
		$acc_id = $data_get_acc['acc_id'];
	}

	$acc_id = resolve_account_no($acc_id,$conn);
	$sec_acc = resolve_account_no($sec_acc,$conn);

	if($v_type_id == 1){
		$query_get_acc = "SELECT acc_id from accounts WHERE acc_name IN ('Purchase Account','Purchase','Purchases') AND del_status = 0 LIMIT 1";
		$result_get_acc = mysqli_query($conn,$query_get_acc);
		if(!$result_get_acc || mysqli_num_rows($result_get_acc) == 0){
			return "Purchase account not found in chart of accounts.";
		}
		$data_get_acc = mysqli_fetch_array($result_get_acc);
		$sec_acc = resolve_account_no($data_get_acc['acc_id'],$conn);

		// 	Purchase Account
		$debit_accoount = $sec_acc;
		$credit_account = $acc_id;
	}
	else if($v_type_id == 2){
		$debit_accoount = $acc_id;
		$credit_account = $sec_acc;
	}
	else if($v_type_id == 3){
		$query_get_acc = "SELECT acc_id from accounts WHERE acc_name IN ('Sale Account','Sales') AND del_status = 0 LIMIT 1";
		$result_get_acc = mysqli_query($conn,$query_get_acc);
		if(!$result_get_acc || mysqli_num_rows($result_get_acc) == 0){
			return "Sales account not found in chart of accounts.";
		}
		$data_get_acc = mysqli_fetch_array($result_get_acc);
		$sec_acc = resolve_account_no($data_get_acc['acc_id'],$conn);

		$debit_accoount = $acc_id;
		$credit_account = $sec_acc;
	}
	else if($v_type_id == 4){
		$debit_accoount = $sec_acc;
		$credit_account = $acc_id;
	}
	else if($v_type_id == 5){
		$debit_accoount = "";
		$credit_account = "";
	}

	if((int)$debit_accoount <= 0 || (int)$credit_account <= 0){
		return "Invalid account binding. Debit Account: ".$debit_accoount.", Credit Account: ".$credit_account;
	}


	$query_1 = "INSERT INTO vouchers SET ";
	$query_1 .= " voucher_no  = ".$voucher_no;
	$query_1 .= ", v_type_id  = ".$v_type_id;
	$query_1 .= ", account_id  = ".$debit_accoount;
	$query_1 .= ", trans_dated  = '".$trans_dated."'";
	$query_1 .= ", debit_amount  = ".$amount;
	$query_1 .= ", credit_amount  = 0";
	$query_1 .= ", ref_column  = '".$ref_column."'";
	$query_1 .= ", ref_id  = ".$ref_id;
	$query_1 .= ", narration  = '".$narration."'";
	$query_1 .= ", created_by  = ".$created_by;

	$query1 = $conn->query($query_1);


	$query_2 = "INSERT INTO vouchers SET ";
	$query_2 .= " voucher_no  = ".$voucher_no;
	$query_2 .= ", v_type_id  = ".$v_type_id;
	$query_2 .= ", account_id  = ".$credit_account;
	$query_2 .= ", trans_dated  = '".$trans_dated."'";
	$query_2 .= ", debit_amount  = 0";
	$query_2 .= ", credit_amount  = ".$amount;
	$query_2 .= ", ref_column  = '".$ref_column."'";
	$query_2 .= ", ref_id  = ".$ref_id;
	$query_2 .= ", narration  = '".$narration."'";
	$query_2 .= ", created_by  = ".$created_by;

	$query2 = $conn->query($query_2);

	if($query1 && $query2){
		return 1;
	}else{
		return $query_get_acc;
	}
}





function searchForDt($id, $array,$st,$empId) {
	for($j =0; $j<count($array); $j++){
		if($array[$j][2] == $id && $array[$j][5]==$st && $array[$j][1]==$empId){
			return $j;
		}
	}
	return null;
}
function get_policy($conn,$pol_key){
	$policy_value = "";
	$queryPol = "SELECT * FROM policies WHERE policy_key = '".$pol_key."'";
	$resultPol = mysqli_query($conn,$queryPol);
	if(mysqli_num_rows($resultPol)>0){
		$dataPol = mysqli_fetch_array($resultPol);
		$policy_value = $dataPol['policy_value'];
	}
	

	return $policy_value;
}



function get_salary_employees($from_dt,$to_dt,$ref,$conn){
	if($ref == 1){
		$output_date = array();

		
		$usr_mod_permisions=array();
		$queryAtt = "SELECT * FROM attendance WHERE dated >= '".$from_dt."' AND dated <= '".$to_dt."' AND del_status = 0 order by 1 DESC ";
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

		$queryRec = "SELECT a.*,b.des_name, c.shift_name, c.shift_start, c.shift_end, c.total_hours, c.grace_time,d.salary FROM employee as a INNER JOIN designations as b on a.emp_designation_id = b.des_id INNER JOIN employee_shifts as c on a.emp_shift_id = c.shift_id INNER JOIN emp_salary as d on a.emp_id = d.emp_id  WHERE a.emp_status = 0 AND a.sal_type_id = 1 and (a.emp_id != 1 AND a.emp_id != 10) order by a.emp_id ASC ";
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
			$salary = $dataRec['salary'];
			$emp_overtime_flag = $dataRec['emp_overtime_flag'];

			$total_working_hours = $total_hours;

			$diff = abs(strtotime($to_dt) - strtotime($from_dt));
			$total_days_of_month = floor($diff / (60*60*24)) +1;

			$new_date = $from_dt;


			$total_holidays = 0;
			$total_working_days = 0;
			$gazated_holiday_count = 0;

			$checkInMissingCount = 0;
			$checkOutMissingCount = 0;
			$OverTimeInMissingCount = 0;
			$OverTimeOutMissingCount = 0;

			$total_present_days = 0;
			$total_absent_days = 0;
			$total_late_arrival_days = 0;
			$total_half_day_count = 0;
			$total_early_dep_day_count = 0;
			$total_overtime_mints_working_day_count = 0;
			$total_non_working_day_mints = 0;
			$total_gazated_holiday_day_mints = 0;

			$late_early_deduction = 0;

			$lateHourSum = 0;
			$lateMinSum = 0;
			$earlyHourSum = 0;
			$earlyMinSum = 0;

			$total_nightShift_mints = 0;

			//  Polices		
			$NWDS = get_policy($conn,'NWDS');
			$LESS = get_policy($conn,'LESS');
			$ODS = get_policy($conn,'ODS');
			$OTHOURSAL = get_policy($conn,'OTHOURSAL');
			$NSPHS = get_policy($conn,'NSPHS');

			$LEDFW = get_policy($conn,'LEDFW');
			$LEDFS = get_policy($conn,'LEDFS');

			$ADFS = get_policy($conn,'ADFS');
			$ADFW = get_policy($conn,'ADFW');

			$ONAW = get_policy($conn,'ONAW');
			$ONAS = get_policy($conn,'ONAS');


			for($i=1; $i<=$total_days_of_month; $i++){
				$DayOfWeekNumber = date("w",strtotime($new_date));

				if($new_date>= date('Y-m-d',strtotime('2024-03-12')) && $new_date<= date('Y-m-d',strtotime('2024-04-09'))){
					$shift_start = '09:00';
					$shift_end = '5:00';
				}

				$this_gazated = 0;
				for($j =0; $j<count($gazated_holidays); $j++){
					if($gazated_holidays[$j][2] == $new_date){

						$this_gazated++;
						$gazated_holiday_count++;
						$total_working_hours=0;
					}
				}

				if($this_gazated == 0){
					if($DayOfWeekNumber == 0){
						$total_holidays++;
						$total_working_hours = 0;
					}else if($DayOfWeekNumber == 1){
						$total_working_days++;
					}else if($DayOfWeekNumber == 2){
						$total_working_days++;
					}else if($DayOfWeekNumber == 3){
						$total_working_days++;
					}else if($DayOfWeekNumber == 4){
						$total_working_days++;
					}else if($DayOfWeekNumber == 5){
						$total_working_days++;
					}else if($DayOfWeekNumber == 6){
						$total_working_days++;
					}
				}



				$st = "0";
				$checkInInd = searchForDt($new_date, $usr_mod_permisions,$st,$emp_id);
				$dateTimeChkIn = "";
				$checkInColor = "";
				$checkIn="";
				if($checkInInd != ""){
					$dateTimeChkIn = $usr_mod_permisions[$checkInInd][4];
					$checkIn = date('h:i A',strtotime($dateTimeChkIn));
				}else{
					$checkInMissingCount++;
				}


				$st = "1";
				$dateTimeChkOut ="";
				$checkOut ="";
				$checkOutInd = searchForDt($new_date, $usr_mod_permisions,$st,$emp_id);
				if($checkOutInd != ""){
					$dateTimeChkOut = $usr_mod_permisions[$checkOutInd][4];
					$checkOut = date('h:i A',strtotime($dateTimeChkOut));
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


				}

				// Total present on working days
				if(($checkOutInd != "" || $checkInInd != "") && $this_gazated == 0 && $DayOfWeekNumber != 0){
					$total_present_days++;
				}

				// Total absent on working days
				if($checkInInd == "" && $checkOutInd == "" && $this_gazated == 0 && $DayOfWeekNumber != 0){
					$total_absent_days++;
				}


				// Total Late Arrival
				if($checkInInd != "" && $this_gazated == 0 && $DayOfWeekNumber != 0){

					$shift_start_new = date('h:'.$grace_time,strtotime($shift_start));
					$late_in_mints = ((strtotime($checkIn) - strtotime($shift_start_new))/3600) * 60;

					$late_in_mints_half_day = ((strtotime($checkIn) - strtotime('12:00'))/3600) * 60;

					$shiftStDt = date('Y-m-d '.$shift_start_new, strtotime($new_date));
					$expiry_time1 = new DateTime($dateTimeChkIn);
					$current_date1 = new DateTime($shiftStDt);
					$diff1 = $expiry_time1->diff($current_date1);

					if($late_in_mints_half_day>0){
						$total_half_day_count++;
					}else{
						if($late_in_mints > 0){
							$late_arrival = $diff1->format('%Hhr %Imin');
							$lateHourSum += $diff1->format('%H');
							$lateMinSum += $diff1->format('%I');

							$total_late_arrival_days++;
						}
					}
				}

				// half day count if (checkin or checkout one is missing)
				if($checkInInd == "" && $checkOutInd != "" && $this_gazated == 0 && $DayOfWeekNumber != 0 ){
					$total_half_day_count++;
				}
				if($checkInInd != "" && $checkOutInd == "" && $this_gazated == 0 && $DayOfWeekNumber != 0 ){
					$total_half_day_count++;
				}
				////////////////////////////////////


				// Total Early Departure
				if($checkOutInd != "" && $this_gazated == 0 && $DayOfWeekNumber != 0){
					$shift_end_new = $shift_end;
					$shift_end_new = $shift_end_new.' PM';

					$early_dep_mints = ((strtotime($checkOut) - strtotime($shift_end_new))/3600) * 60;


					if($early_dep_mints < 0){
						$total_early_dep_day_count++;
					}

					else if($early_dep_mints>0){


						$shift_end_new_overtime = '0'.$ODS.':00 PM';
						$overtime_hour_mins = ((strtotime($checkOut) - strtotime($shift_end_new_overtime))/3600) * 60;

						if($overtime_hour_mins>0){
							$total_overtime_mints_working_day_count +=  $overtime_hour_mins;

						}
					}
				}




				if($this_gazated != 0){
					if($checkOutInd != "" || $checkInInd != ""){

						$this_gazated_mints = ((strtotime($checkOut) - strtotime($checkIn))/3600) * 60;
						$total_gazated_holiday_day_mints += $this_gazated_mints;
					}
				}
				else if($DayOfWeekNumber == 0){


					if($checkOutInd != "" && $checkInInd != ""){

						$this_holiday_mints =  ((strtotime($checkOut) - strtotime($checkIn))/3600) * 60;
						$total_non_working_day_mints += $this_holiday_mints;
					}
				}







				// Night Shift
				// NSPHS
				// Check if Allow
				$query_check = "SELECT * FROM night_shift_perm WHERE employee_id = ".$emp_id." AND dated = '".$new_date."' AND del_status = 0";
				$result_check = mysqli_query($conn,$query_check);
				if(mysqli_num_rows($result_check)>0){
					// echo 'he';
					$data_check = mysqli_fetch_array($result_check);
					$perm_id = $data_check['id'];

					// Overtime IN
					$st = "4";
					$OverTimeInInd = searchForDt($new_date, $usr_mod_permisions,$st,$emp_id);
					$dateTimeOverTimeIn = "";
					if($OverTimeInInd != ""){
						$dateTimeOverTimeIn = $usr_mod_permisions[$OverTimeInInd][4];
						$OverTimeIn = date('h:i A',strtotime($dateTimeOverTimeIn));
					}else{
						$OverTimeInMissingCount++;
					}


					// Overtime OUT
					$st = "5";
					$OverTimeOutInd = searchForDt($new_date, $usr_mod_permisions,$st,$emp_id);
					$dateTimeOverTimeOut = "";
					if($OverTimeOutInd != ""){
						$dateTimeOverTimeOut = $usr_mod_permisions[$OverTimeOutInd][4];
						$OverTimeOut = date('h:i A',strtotime($dateTimeOverTimeOut));
					}else{
						$OverTimeOutMissingCount++;
					}

					if($OverTimeInInd != "" && $OverTimeOutInd != ""){
						$night_shift_mints = ((strtotime($OverTimeOut) - strtotime($OverTimeIn))/3600) * 60;
						$hours_nightshifts = intdiv($night_shift_mints, 60);

						if($hours_nightshifts>=5){
							$total_nightShift_mints += $night_shift_mints;
						}else{
							$total_overtime_mints_working_day_count += $night_shift_mints;
						}
					}
				}else{
					$st = "4";
					$OverTimeInInd = searchForDt($new_date, $usr_mod_permisions,$st,$emp_id);
					$dateTimeOverTimeIn = "";
					if($OverTimeInInd != ""){
						$dateTimeOverTimeIn = $usr_mod_permisions[$OverTimeInInd][4];
						$OverTimeIn = date('h:i A',strtotime($dateTimeOverTimeIn));
					}else{
						$OverTimeInMissingCount++;
					}


					// Overtime OUT
					$st = "5";
					$OverTimeOutInd = searchForDt($new_date, $usr_mod_permisions,$st,$emp_id);
					$dateTimeOverTimeOut = "";
					if($OverTimeOutInd != ""){
						$dateTimeOverTimeOut = $usr_mod_permisions[$OverTimeOutInd][4];
						$OverTimeOut = date('h:i A',strtotime($dateTimeOverTimeOut));
					}else{
						$OverTimeOutMissingCount++;
					}

					if($OverTimeInInd != "" && $OverTimeOutInd != ""){
						$night_shift_mints = ((strtotime($OverTimeOut) - strtotime($OverTimeIn))/3600) * 60;
						$hours_nightshifts = intdiv($night_shift_mints, 60);

						$total_overtime_mints_working_day_count += $night_shift_mints;				
					}
				}


				$new_date = date('Y-m-d', strtotime($new_date. ' + 1 days'));

			}



			// per day & per hour salary
			$per_day_salary = $salary/$total_days_of_month;
			$per_hour_salary = $per_day_salary/8;

			$late_early_deduction_days = floor((($total_late_arrival_days+$total_early_dep_day_count)/$LESS));
			$half_day_salary = ($per_day_salary)/2;
			
			// late early deduction amt
			if(($emp_designation_id == 3 && $LEDFW=='Y') || (($emp_designation_id == 1 || $emp_designation_id == 2 || $emp_designation_id == 4) &&  $LEDFS=='Y') ) {
				
				$late_early_deduction = round($late_early_deduction_days * $half_day_salary);
			}
			else{
				$late_early_deduction = 0;
			}
			///////////////////////////


			// Overtime Amount
			$hours_new_exccess = intdiv($total_overtime_mints_working_day_count, 60);
			$total_hours_excess = $hours_new_exccess;
			$overtime_hours_amount = round(($total_hours_excess * $OTHOURSAL) * $per_hour_salary);


			// Night shift amount
			$hours_new_nightshift = intdiv($total_nightShift_mints, 60);
			$nightshift_hours_amount = round(($hours_new_nightshift * $NSPHS) * $per_hour_salary);


			// non working day
			$hours_new_exccess_holiday = intdiv($total_non_working_day_mints, 60);
			$total_overtime_non_workingday_mints = round($hours_new_exccess_holiday);

			// gazated holiday
			$hours_new_exccess_holiday_gazated = intdiv($total_gazated_holiday_day_mints, 60);
			$total_gazated_holidays_mints =  round($hours_new_exccess_holiday_gazated);


			$NWDA_amount = (round(($total_overtime_non_workingday_mints + $total_gazated_holidays_mints))*$per_hour_salary) * $NWDS;

			// half day deduction amount

			if(($emp_designation_id == 3 && $LEDFW=='Y' && $total_half_day_count>0) || (($emp_designation_id == 1 || $emp_designation_id == 2 || $emp_designation_id == 4) &&  $LEDFS=='Y' && $total_half_day_count>0) ){
				
				$half_day_deduction_amt =  round($total_half_day_count * $per_day_salary/2);
			}
			else{
				$half_day_deduction_amt = 0;
			}

			// if($total_half_day_count>0 ){

			// }else{
			// 	$half_day_deduction_amt = 0;
			// }

			if( ($ADFW == 'Y' && $total_absent_days>0) || (($emp_designation_id == 1 || $emp_designation_id == 2 || $emp_designation_id == 4) &&  $ADFS=='Y' && $total_absent_days>0 ) ){

				$total_absent_days_amt = $total_absent_days * $per_day_salary;

			}else{
				$total_absent_days_amt = 0;
			}



			if($emp_designation_id == 3){
				$allowAbsentDeduct = $ADFW;
				$allowHalfDayLateEarlyDeduct = $LEDFW;
				$allowOverTimeAllowance = $ONAW;
			}
			else if($emp_designation_id == 1 || $emp_designation_id == 2 || $emp_designation_id == 4){
				$allowAbsentDeduct = $ADFS;
				$allowHalfDayLateEarlyDeduct = $LEDFS;
				$allowOverTimeAllowance = $ONAS;
			}


			$sal = $salary;

			if($allowAbsentDeduct == 'Y'){
				$sal = $sal - $total_absent_days_amt;
			}else{
				$total_absent_days_amt = 0;
			}

			if($allowHalfDayLateEarlyDeduct == 'Y'){
				$sal = $sal - $half_day_deduction_amt - $late_early_deduction;
			}else{
				$half_day_deduction_amt = 0;
				$late_early_deduction = 0;
			}

			if($allowOverTimeAllowance == 'Y'){
				$sal = $sal + $overtime_hours_amount + $NWDA_amount + $nightshift_hours_amount;
			}else{
				$overtime_hours_amount = 0;
				$NWDA_amount = 0;
				$nightshift_hours_amount = 0;
			}

			$expected_salary = round($sal,2);

			// $expected_salary = round($salary - $total_absent_days_amt - $half_day_deduction_amt - $late_early_deduction + $overtime_hours_amount + $NWDA_amount + $nightshift_hours_amount,2);

			// 'Overtime Mints Working Day'=> $total_overtime_mints_working_day_count,

			// if($emp_designation_id==3){
			$test1 = array(
				'Employee ID' => $emp_id,
				'Employee Name' => $emp_name,
				'Total Month Days' => $total_days_of_month,
				'Per Day Salary' => $per_day_salary,
				'Per Hour Salary' => $per_hour_salary,

				'Present Days'=> $total_present_days,
				'Absent Days'=> $total_absent_days,
				'Late Arrival Days'=> $total_late_arrival_days,
				'Early Departure Days'=> $total_early_dep_day_count,
				'Half Days'=> $total_half_day_count,
				'Overtime Hours Working Day' => $total_hours_excess,
				'Overtime Hours Non Working Day'=> $total_overtime_non_workingday_mints,
				'Overtime Hours Gazated Holiday Day'=> $total_gazated_holidays_mints,

				'Late Early Salary Days Deduct' => $late_early_deduction_days,

				'Absent Days Deduction' => $total_absent_days_amt,
				'Late Early Deduction' => $late_early_deduction,
				'Half Days Deduction Amount' => $half_day_deduction_amt,
				'Overtime Amount Working Day' => $overtime_hours_amount,
				'NWDA Amount' => $NWDA_amount,

				'Night Shift Hours' => $hours_new_nightshift,
				'Night Shift Amount' => $nightshift_hours_amount,

				'Actual Salary' => $salary,
				'Expected Salary' => $expected_salary,
				'LESS' => $LESS,
				'ODS' => $ODS,
				'NWDS' => $NWDS,
				'OTHOURSAL' => $OTHOURSAL,
				'NSPHS' => $NSPHS,
				'AbsentDeduct' => $allowAbsentDeduct,
				'LEHD' => $allowHalfDayLateEarlyDeduct,
				'OtherAllownces' => $allowOverTimeAllowance
			);
			// }else{
			// 	$expected_salary = round($salary - $total_absent_days_amt - $half_day_deduction_amt - $late_early_deduction,2);
			// 	$test1 = array(
			// 		'Employee ID' => $emp_id,
			// 		'Employee Name' => $emp_name,
			// 		'Total Month Days' => $total_days_of_month,
			// 		'Per Day Salary' => $per_day_salary,
			// 		'Per Hour Salary' => $per_hour_salary,

			// 		'Present Days'=> $total_present_days,
			// 		'Absent Days'=> $total_absent_days,
			// 		'Late Arrival Days'=> $total_late_arrival_days,
			// 		'Early Departure Days'=> $total_early_dep_day_count,
			// 		'Half Days'=> $total_half_day_count,
			// 		'Overtime Hours Working Day' => $total_hours_excess,
			// 		'Overtime Hours Non Working Day'=> $total_overtime_non_workingday_mints,
			// 		'Overtime Hours Gazated Holiday Day'=> $total_gazated_holidays_mints,

			// 		'Late Early Salary Days Deduct' => $late_early_deduction_days,

			// 		'Absent Days Deduction' => $total_absent_days_amt,
			// 		'Late Early Deduction' => $late_early_deduction,
			// 		'Half Days Deduction Amount' => $half_day_deduction_amt,
			// 		'Overtime Amount Working Day' => 0,
			// 		'NWDA Amount' => 0,

			// 		'Night Shift Hours' => $hours_new_nightshift,
			// 		'Night Shift Amount' => $nightshift_hours_amount,

			// 		'Actual Salary' => $salary,
			// 		'Expected Salary' => $expected_salary,
			// 		'LESS' => $LESS,
			// 		'ODS' => $ODS,
			// 		'NWDS' => $NWDS,
			// 		'OTHOURSAL' => $OTHOURSAL,
			// 		'NSPHS'=>$NSPHS
			// 	);
			// }
			array_push($output_date,$test1);

		}
		return $output_date;
	}
}


function get_salary_employees_emp_wise($from_dt,$to_dt,$ref,$conn,$emp_id){
	if($ref == 1){
		$output_date = array();
		
		$usr_mod_permisions=array();
		$queryAtt = "SELECT * FROM attendance WHERE dated >= '".$from_dt."' AND dated <= '".$to_dt."' AND del_status = 0 ";
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

		$queryRec = "SELECT a.*,b.des_name, c.shift_name, c.shift_start, c.shift_end, c.total_hours, c.grace_time,d.salary FROM employee as a INNER JOIN designations as b on a.emp_designation_id = b.des_id INNER JOIN employee_shifts as c on a.emp_shift_id = c.shift_id INNER JOIN emp_salary as d on a.emp_id = d.emp_id  WHERE a.emp_status = 0 AND a.sal_type_id = 1 AND a.emp_id = ".$emp_id." order by a.emp_id ASC ";
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
			$salary = $dataRec['salary'];
			$emp_overtime_flag = $dataRec['emp_overtime_flag'];

			$total_working_hours = $total_hours;

			$diff = abs(strtotime($to_dt) - strtotime($from_dt));
			$total_days_of_month = floor($diff / (60*60*24)) +1;

			$new_date = $from_dt;


			$total_holidays = 0;
			$total_working_days = 0;
			$gazated_holiday_count = 0;

			$checkInMissingCount = 0;
			$checkOutMissingCount = 0;
			$OverTimeInMissingCount = 0;
			$OverTimeOutMissingCount = 0;

			$total_present_days = 0;
			$total_absent_days = 0;
			$total_late_arrival_days = 0;
			$total_half_day_count = 0;
			$total_early_dep_day_count = 0;
			$total_overtime_mints_working_day_count = 0;
			$total_non_working_day_mints = 0;
			$total_gazated_holiday_day_mints = 0;

			$late_early_deduction = 0;

			$lateHourSum = 0;
			$lateMinSum = 0;
			$earlyHourSum = 0;
			$earlyMinSum = 0;

			$total_nightShift_mints = 0;

			//  Polices		
			$NWDS = get_policy($conn,'NWDS');
			$LESS = get_policy($conn,'LESS');
			$ODS = get_policy($conn,'ODS');
			$OTHOURSAL = get_policy($conn,'OTHOURSAL');
			$NSPHS = get_policy($conn,'NSPHS');
			

			$LEDFW = get_policy($conn,'LEDFW');
			$LEDFS = get_policy($conn,'LEDFS');

			$ADFS = get_policy($conn,'ADFS');
			$ADFW = get_policy($conn,'ADFW');

			$ONAW = get_policy($conn,'ONAW');
			$ONAS = get_policy($conn,'ONAS');


			for($i=1; $i<=$total_days_of_month; $i++){
				$DayOfWeekNumber = date("w",strtotime($new_date));

				if($new_date>= date('Y-m-d',strtotime('2024-03-12')) && $new_date<= date('Y-m-d',strtotime('2024-04-09'))){
					$shift_start = '09:00';
					$shift_end = '5:00';
				}

				$this_gazated = 0;
				for($j =0; $j<count($gazated_holidays); $j++){
					if($gazated_holidays[$j][2] == $new_date){

						$this_gazated++;
						$gazated_holiday_count++;
						$total_working_hours=0;
					}
				}

				if($this_gazated == 0){
					if($DayOfWeekNumber == 0){
						$total_holidays++;
						$total_working_hours = 0;
					}else if($DayOfWeekNumber == 1){
						$total_working_days++;
					}else if($DayOfWeekNumber == 2){
						$total_working_days++;
					}else if($DayOfWeekNumber == 3){
						$total_working_days++;
					}else if($DayOfWeekNumber == 4){
						$total_working_days++;
					}else if($DayOfWeekNumber == 5){
						$total_working_days++;
					}else if($DayOfWeekNumber == 6){
						$total_working_days++;
					}
				}



				$st = "0";
				$checkInInd = searchForDt($new_date, $usr_mod_permisions,$st,$emp_id);
				$dateTimeChkIn = "";
				$checkInColor = "";
				$checkIn="";
				if($checkInInd != ""){
					$dateTimeChkIn = $usr_mod_permisions[$checkInInd][4];
					$checkIn = date('h:i A',strtotime($dateTimeChkIn));
				}else{
					$checkInMissingCount++;
				}


				$st = "1";
				$dateTimeChkOut ="";
				$checkOut ="";
				$checkOutInd = searchForDt($new_date, $usr_mod_permisions,$st,$emp_id);
				if($checkOutInd != ""){
					$dateTimeChkOut = $usr_mod_permisions[$checkOutInd][4];
					$checkOut = date('h:i A',strtotime($dateTimeChkOut));
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
				}

				// Total present on working days
				if(($checkOutInd != "" || $checkInInd != "") && $this_gazated == 0 && $DayOfWeekNumber != 0){
					$total_present_days++;
				}

				// Total absent on working days
				if($checkInInd == "" && $checkOutInd == "" && $this_gazated == 0 && $DayOfWeekNumber != 0){
					$total_absent_days++;
				}


				// Total Late Arrival
				if($checkInInd != "" && $this_gazated == 0 && $DayOfWeekNumber != 0){

					$shift_start_new = date('h:'.$grace_time,strtotime($shift_start));
					$late_in_mints = ((strtotime($checkIn) - strtotime($shift_start_new))/3600) * 60;

					$late_in_mints_half_day = ((strtotime($checkIn) - strtotime('12:00'))/3600) * 60;

					$shiftStDt = date('Y-m-d '.$shift_start_new, strtotime($new_date));
					$expiry_time1 = new DateTime($dateTimeChkIn);
					$current_date1 = new DateTime($shiftStDt);
					$diff1 = $expiry_time1->diff($current_date1);

					if($late_in_mints_half_day>0){
						$total_half_day_count++;
					}else{
						if($late_in_mints > 0){
							$late_arrival = $diff1->format('%Hhr %Imin');
							$lateHourSum += $diff1->format('%H');
							$lateMinSum += $diff1->format('%I');

							$total_late_arrival_days++;
						}
					}
				}


				// half day count if (checkin or checkout one is missing)
				if($checkInInd == "" && $checkOutInd != "" && $this_gazated == 0 && $DayOfWeekNumber != 0 ){
					$total_half_day_count++;
				}
				if($checkInInd != "" && $checkOutInd == "" && $this_gazated == 0 && $DayOfWeekNumber != 0 ){
					$total_half_day_count++;
				}
				////////////////////////////////////


				// Total Early Departure
				if($checkOutInd != "" && $this_gazated == 0 && $DayOfWeekNumber != 0){
					$shift_end_new = $shift_end;
					$shift_end_new = $shift_end_new.' PM';

					$early_dep_mints = ((strtotime($checkOut) - strtotime($shift_end_new))/3600) * 60;


					if($early_dep_mints < 0){
						$total_early_dep_day_count++;
					}

					else if($early_dep_mints>0){


						$shift_end_new_overtime = '0'.$ODS.':00 PM';
						$overtime_hour_mins = ((strtotime($checkOut) - strtotime($shift_end_new_overtime))/3600) * 60;

						if($overtime_hour_mins>0){
							$total_overtime_mints_working_day_count +=  $overtime_hour_mins;

						}
					}
				}




				if($this_gazated != 0){
					if($checkOutInd != "" || $checkInInd != ""){

						$this_gazated_mints = ((strtotime($checkOut) - strtotime($checkIn))/3600) * 60;
						$total_gazated_holiday_day_mints += $this_gazated_mints;
					}
				}
				else if($DayOfWeekNumber == 0){


					if($checkOutInd != "" && $checkInInd != ""){

						$this_holiday_mints =  ((strtotime($checkOut) - strtotime($checkIn))/3600) * 60;
						$total_non_working_day_mints += $this_holiday_mints;
					}
				}







				// Night Shift
				// NSPHS
				// Check if Allow
				$query_check = "SELECT * FROM night_shift_perm WHERE employee_id = ".$emp_id." AND dated = '".$new_date."' AND del_status = 0";
				$result_check = mysqli_query($conn,$query_check);
				if(mysqli_num_rows($result_check)>0){
					// echo 'he';
					$data_check = mysqli_fetch_array($result_check);
					$perm_id = $data_check['id'];

					// Overtime IN
					$st = "4";
					$OverTimeInInd = searchForDt($new_date, $usr_mod_permisions,$st,$emp_id);
					$dateTimeOverTimeIn = "";
					if($OverTimeInInd != ""){
						$dateTimeOverTimeIn = $usr_mod_permisions[$OverTimeInInd][4];
						$OverTimeIn = date('h:i A',strtotime($dateTimeOverTimeIn));
					}else{
						$OverTimeInMissingCount++;
					}


					// Overtime OUT
					$st = "5";
					$OverTimeOutInd = searchForDt($new_date, $usr_mod_permisions,$st,$emp_id);
					$dateTimeOverTimeOut = "";
					if($OverTimeOutInd != ""){
						$dateTimeOverTimeOut = $usr_mod_permisions[$OverTimeOutInd][4];
						$OverTimeOut = date('h:i A',strtotime($dateTimeOverTimeOut));
					}else{
						$OverTimeOutMissingCount++;
					}

					if($OverTimeInInd != "" && $OverTimeOutInd != ""){
						$night_shift_mints = ((strtotime($OverTimeOut) - strtotime($OverTimeIn))/3600) * 60;
						$hours_nightshifts = intdiv($night_shift_mints, 60);

						if($hours_nightshifts>=5){
							$total_nightShift_mints += $night_shift_mints;
						}else{
							$total_overtime_mints_working_day_count += $night_shift_mints;
						}
					}
				}



				$new_date = date('Y-m-d', strtotime($new_date. ' + 1 days'));

			}



			// per day & per hour salary
			$per_day_salary = $salary/$total_days_of_month;
			$per_hour_salary = $per_day_salary/8;

			// late early deduction amt
			$late_early_deduction_days = floor((($total_late_arrival_days+$total_early_dep_day_count)/$LESS));
			$half_day_salary = ($per_day_salary)/2;
			$late_early_deduction = round($late_early_deduction_days * $half_day_salary);


			// Overtime Amount
			$hours_new_exccess = intdiv($total_overtime_mints_working_day_count, 60);
			$total_hours_excess = $hours_new_exccess;
			$overtime_hours_amount = round(($total_hours_excess * $OTHOURSAL) * $per_hour_salary);


			// Night shift amount
			$hours_new_nightshift = intdiv($total_nightShift_mints, 60);
			$nightshift_hours_amount = round(($hours_new_nightshift * $NSPHS) * $per_hour_salary);


			// non working day
			$hours_new_exccess_holiday = intdiv($total_non_working_day_mints, 60);
			$total_overtime_non_workingday_mints = round($hours_new_exccess_holiday);

			// gazated holiday
			$hours_new_exccess_holiday_gazated = intdiv($total_gazated_holiday_day_mints, 60);
			$total_gazated_holidays_mints =  round($hours_new_exccess_holiday_gazated);


			$NWDA_amount = (round(($total_overtime_non_workingday_mints + $total_gazated_holidays_mints))*$per_hour_salary) * $NWDS;

			// half day deduction amount
			if($total_half_day_count>0){
				$half_day_deduction_amt =  round($total_half_day_count * $per_day_salary/2);
			}else{
				$half_day_deduction_amt = 0;
			}

			$total_absent_days_amt = $total_absent_days * $per_day_salary;



			if($emp_designation_id == 3){
				$allowAbsentDeduct = $ADFW;
				$allowHalfDayLateEarlyDeduct = $LEDFW;
				$allowOverTimeAllowance = $ONAW;
			}
			else if($emp_designation_id == 1 || $emp_designation_id == 2 || $emp_designation_id == 4){
				$allowAbsentDeduct = $ADFS;
				$allowHalfDayLateEarlyDeduct = $LEDFS;
				$allowOverTimeAllowance = $ONAS;
			}


			$sal = $salary;

			if($allowAbsentDeduct == 'Y'){
				$sal = $sal - $total_absent_days_amt;
			}else{
				$total_absent_days_amt = 0;
			}

			if($allowHalfDayLateEarlyDeduct == 'Y'){
				$sal = $sal - $half_day_deduction_amt - $late_early_deduction;
			}else{
				$half_day_deduction_amt = 0;
				$late_early_deduction = 0;
			}

			if($allowOverTimeAllowance == 'Y'){
				$sal = $sal + $overtime_hours_amount + $NWDA_amount + $nightshift_hours_amount;
			}else{
				$overtime_hours_amount = 0;
				$NWDA_amount = 0;
				$nightshift_hours_amount = 0;
			}


			$expected_salary = round($salary - $total_absent_days_amt - $half_day_deduction_amt - $late_early_deduction + $overtime_hours_amount + $NWDA_amount + $nightshift_hours_amount,2);

			// 'Overtime Mints Working Day'=> $total_overtime_mints_working_day_count,

			// if($emp_designation_id==3){
			$test1 = array(
				'Employee ID' => $emp_id,
				'Employee Name' => $emp_name,
				'Total Month Days' => $total_days_of_month,
				'Per Day Salary' => $per_day_salary,
				'Per Hour Salary' => $per_hour_salary,

				'Present Days'=> $total_present_days,
				'Absent Days'=> $total_absent_days,
				'Late Arrival Days'=> $total_late_arrival_days,
				'Early Departure Days'=> $total_early_dep_day_count,
				'Half Days'=> $total_half_day_count,
				'Overtime Hours Working Day' => $total_hours_excess,
				'Overtime Hours Non Working Day'=> $total_overtime_non_workingday_mints,
				'Overtime Hours Gazated Holiday Day'=> $total_gazated_holidays_mints,

				'Late Early Salary Days Deduct' => $late_early_deduction_days,

				'Absent Days Deduction' => $total_absent_days_amt,
				'Late Early Deduction' => $late_early_deduction,
				'Half Days Deduction Amount' => $half_day_deduction_amt,
				'Overtime Amount Working Day' => $overtime_hours_amount,
				'NWDA Amount' => $NWDA_amount,

				'Night Shift Hours' => $hours_new_nightshift,
				'Night Shift Amount' => $nightshift_hours_amount,

				'Actual Salary' => $salary,
				'Expected Salary' => $expected_salary,
				'LESS' => $LESS,
				'ODS' => $ODS,
				'NWDS' => $NWDS,
				'OTHOURSAL' => $OTHOURSAL,
				'NSPHS'=>$NSPHS,
				'AbsentDeduct' => $allowAbsentDeduct,
				'LEHD' => $allowHalfDayLateEarlyDeduct,
				'OtherAllownces' => $allowOverTimeAllowance
			);
			// }else{
			// 	$expected_salary = round($salary - $total_absent_days_amt - $half_day_deduction_amt - $late_early_deduction,2);
			// 	$test1 = array(
			// 		'Employee ID' => $emp_id,
			// 		'Employee Name' => $emp_name,
			// 		'Total Month Days' => $total_days_of_month,
			// 		'Per Day Salary' => $per_day_salary,
			// 		'Per Hour Salary' => $per_hour_salary,

			// 		'Present Days'=> $total_present_days,
			// 		'Absent Days'=> $total_absent_days,
			// 		'Late Arrival Days'=> $total_late_arrival_days,
			// 		'Early Departure Days'=> $total_early_dep_day_count,
			// 		'Half Days'=> $total_half_day_count,
			// 		'Overtime Hours Working Day' => $total_hours_excess,
			// 		'Overtime Hours Non Working Day'=> $total_overtime_non_workingday_mints,
			// 		'Overtime Hours Gazated Holiday Day'=> $total_gazated_holidays_mints,

			// 		'Late Early Salary Days Deduct' => $late_early_deduction_days,

			// 		'Absent Days Deduction' => $total_absent_days_amt,
			// 		'Late Early Deduction' => $late_early_deduction,
			// 		'Half Days Deduction Amount' => $half_day_deduction_amt,
			// 		'Overtime Amount Working Day' => 0,
			// 		'NWDA Amount' => 0,

			// 		'Night Shift Hours' => $hours_new_nightshift,
			// 		'Night Shift Amount' => 0,

			// 		'Actual Salary' => $salary,
			// 		'Expected Salary' => $expected_salary,
			// 		'LESS' => $LESS,
			// 		'ODS' => $ODS,
			// 		'NWDS' => $NWDS,
			// 		'OTHOURSAL' => $OTHOURSAL,
			// 		'NSPHS'=>$NSPHS
			// 	);
			// }
			array_push($output_date,$test1);

		}
		return $output_date;
	}
}
?>
