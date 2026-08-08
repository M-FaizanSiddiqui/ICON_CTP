<?php

include 'db_connect.php';
print_r(get_salary_employees(1,$conn));

function get_policy($conn,$pol_key){
	$queryPol = "SELECT * FROM policies WHERE policy_key = '".$pol_key."'";
	$resultPol = mysqli_query($conn,$queryPol);
	$dataPol = mysqli_fetch_array($resultPol);
	$policy_value = $dataPol['policy_value'];

	return $policy_value;
}

function get_salary_employees($ref,$conn){
	if($ref == 1){
		$output_date = array();
		$from_dt = date('Y-m-01',strtotime('2024-01-01'));
		$to_dt = date('Y-m-t',strtotime('2024-01-01'));

		function searchForDt($id, $array,$st,$empId) {
			for($j =0; $j<count($array); $j++){
				if($array[$j][2] == $id && $array[$j][5]==$st && $array[$j][1]==$empId){
					return $j;
				}
			}
			return null;
		}


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

		$queryRec = "SELECT a.*,b.des_name, c.shift_name, c.shift_start, c.shift_end, c.total_hours, c.grace_time,d.salary FROM employee as a INNER JOIN designations as b on a.emp_designation_id = b.des_id INNER JOIN employee_shifts as c on a.emp_shift_id = c.shift_id INNER JOIN emp_salary as d on a.emp_id = d.emp_id  WHERE a.emp_status = 0 AND a.sal_type_id = 1 order by a.emp_id ASC ";
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


			$total_working_hours = $total_hours;

			$diff = abs(strtotime($to_dt) - strtotime($from_dt));
			$total_days_of_month = floor($diff / (60*60*24)) +1;

			$new_date = $from_dt;


			$total_holidays = 0;
			$total_working_days = 0;
			$gazated_holiday_count = 0;

			$checkInMissingCount = 0;
			$checkOutMissingCount = 0;

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

			//  Polices		
			$NWDS = get_policy($conn,'NWDS');
			$LESS = get_policy($conn,'LESS');
			$ODS = get_policy($conn,'ODS');
			$OTHOURSAL = get_policy($conn,'OTHOURSAL');

			for($i=1; $i<=$total_days_of_month; $i++){
				$DayOfWeekNumber = date("w",strtotime($new_date));

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
					$checkOutMissingCount++;
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
					$checkOut = '08:00 PM';
					$checkOutInd = 1;
					if($checkOutInd != "" && $checkInInd != ""){

						$this_holiday_mints =  ((strtotime($checkOut) - strtotime($checkIn))/3600) * 60;
						$total_non_working_day_mints += $this_holiday_mints;
					}
				}


				$new_date = date('Y-m-d', strtotime($new_date. ' + 1 days'));

				$per_day_salary = $salary/$total_days_of_month;
				$per_hour_salary = $per_day_salary/8;

				$late_early_deduction_days = floor((($total_late_arrival_days+$total_early_dep_day_count)/$LESS));
				$late_early_deduction = round($late_early_deduction_days * $per_day_salary);



				// Overtime Amount
				$hours_new_exccess = intdiv($total_overtime_mints_working_day_count, 60);
				$total_hours_excess = $hours_new_exccess;
				$overtime_hours_amount = round(($total_hours_excess * $OTHOURSAL) * $per_hour_salary);


			}


			$hours_new_exccess_holiday = intdiv($total_non_working_day_mints, 60);
			$total_overtime_non_workingday_mints = round($hours_new_exccess_holiday);


			$hours_new_exccess_holiday_gazated = intdiv($total_gazated_holiday_day_mints, 60);
			$total_gazated_holidays_mints =  round($hours_new_exccess_holiday_gazated);


			$NWDA_amount = (round(($total_overtime_non_workingday_mints + $total_gazated_holiday_day_mints))*$per_hour_salary) * $NWDS;

			// half day deduction amount
			if($total_half_day_count>0){
				$half_day_deduction_amt =  round($total_half_day_count * $per_day_salary/2);
			}else{
				$half_day_deduction_amt = 0;
			}


			$expected_salary = $salary - $half_day_deduction_amt - $late_early_deduction + $overtime_hours_amount + $NWDA_amount;



			// 'Overtime Mints Working Day'=> $total_overtime_mints_working_day_count,
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
				'Overtime Hours Gazated Holiday Day'=> $total_gazated_holiday_day_mints,

				'Late Early Salary Days Deduct' => $late_early_deduction_days,

				'Late Early Deduction' => $late_early_deduction,
				'Half Days Deduction Amount' => $half_day_deduction_amt,
				'Overtime Amount Working Day' => $overtime_hours_amount,
				'NWDA Amount' => $NWDA_amount,

				'Actual Salary' => $salary,
				'Expeted Salary' => $expected_salary

			);

			// print_r($test1);

			array_push($output_date,$test1);
		}

		return $output_date;
	}
}
