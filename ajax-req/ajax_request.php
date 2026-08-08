<?php
include('../db_connect.php');
include('../functions.php');

$req_no = $_POST['req_no'];
$output_date = '<option value="">Please Select</option>';

if($req_no == 1){
	$supplier_id = $_POST['supplier_id'];
	$query_supp_order = "SELECT * FROM paper_requisition WHERE req_status != 2 AND supp_id = ".$supplier_id." AND del_status = 0";
	$row_supp_order = mysqli_query($conn,$query_supp_order);
	while($data_supp_order = mysqli_fetch_array($row_supp_order)){
		$output_date .= '<option value="'.$data_supp_order['id'].'">ORD - '.$data_supp_order['id'].'</option>';
	}
}
else if($req_no == 2){
	$requisition_id = $_POST['requisition_id'];

	$output_date = '';
	
	$counter = 0;
	$query_supp_ord = "SELECT a.*,b.item_name FROM requisition_details as a INNER JOIN inventory_item as b on a.item_id = b.item_id WHERE a.req_id = ".$requisition_id;
	$result_supp_ord = mysqli_query($conn,$query_supp_ord);
	while($data_supp_ord = mysqli_fetch_array($result_supp_ord)){
		$counter++;
		$qtyRemain = $data_supp_ord['qty'] - $data_supp_ord['qty_rec'];
		$output_date .= '<tr class="my_tr">';

		$output_date .= '<td>';
		$output_date.='<input type="text" class="form-control" readonly="true" style="display:none" value="'.$data_supp_ord['item_id'].'" name="item_id[]">';
		$output_date.='<span>IT-'.$data_supp_ord['item_id'].' ('.$data_supp_ord['item_name'].')</span>';
		$output_date .= '</td>';

		$output_date .= '<td>';
		$output_date.='<span>'.$data_supp_ord['qty'].'</span>';
		$output_date .= '</td>';

		$output_date .= '<td>';
		$output_date.='<span>'.$data_supp_ord['qty_rec'].'</span>';
		$output_date .= '</td>';

		$output_date .= '<td>';
		$output_date.='<span><input type="hidden" class="form-control" name="quantity_remain[]" value="'.$qtyRemain.'">
		'.$qtyRemain.'</span>';
		$output_date .= '</td>';

		$output_date .= '<td>';
		$output_date.='<input type="text" class="form-control quantity " max = "'.$qtyRemain.'" name="quantity[]" value="0">';
		$output_date .= '</td>';

		$output_date .= '<td>';
		$output_date.='<input type="text" class="form-control rate" name="rate[]" value="0">';
		$output_date .= '</td>';

		$output_date .= '<td>';
		$output_date.='<input type="text" class="form-control amount" name="amount[]" value="0">';
		$output_date .= '</td>';

		$output_date .= '</tr>';
	}
}



else if($req_no == 3){
	$cust_id = $_POST['cust_id'];

	$output_date = '';
	
	$counter = 0;
	$query_cust_inv = "SELECT a.*,b.item_name,b.size_in_mm,b.hl_inches FROM customer_inventory as a INNER JOIN inventory_item as b on a.plate_id = b.item_id WHERE a.cust_id = ".$cust_id;
	$result_cust_inv = mysqli_query($conn,$query_cust_inv);
	if(mysqli_num_rows($result_cust_inv)>0){
		while($data_cust_inv = mysqli_fetch_array($result_cust_inv)){
			$counter++;
			$output_date .= '<tr class="my_tr">';

			$output_date .= '<td class="text-center">';
			$output_date.='<span>IT-'.$data_cust_inv['plate_id'].'</span>';
			$output_date .= '</td>';

			$output_date .= '<td>';
			$output_date.='<span>'.$data_cust_inv['item_name'].'</span>';
			$output_date .= '</td>';

			$output_date .= '<td>';
			$output_date.='<span>'.$data_cust_inv['size_in_mm'].'</span>';
			$output_date .= '</td>';

			$output_date .= '<td>';
			$output_date.='<span>'.$data_cust_inv['hl_inches'].'</span>';
			$output_date .= '</td>';

			$output_date .= '<td>';
			$output_date.='<span>'.$data_cust_inv['quantity'].'</span>';
			$output_date .= '</td>';

			$output_date .= '<td>';
			$output_date.='<span>'.$data_cust_inv['qty_booked'].'</span>';
			$output_date .= '</td>';

			$output_date .= '</tr>';
		}	
	}else{
		
		$output_date .= '<tr class="my_tr">';
		$output_date .= '<td class="text-center" colspan="6">';
		$output_date.='<span style="color:red">No Record Found</span>';
		$output_date .= '</td>';
		$output_date .= '</tr>';
	}
	
}

else if($req_no == 4){
	$plate_id = $_POST['plate_id'];
	$inventory_in_out = $_POST['inventory_in_out'];
	$cust_id = $_POST['cust_id'];


	$qty = 0;
	if($inventory_in_out == 1){
		$query_qty = "SELECT quantity,qty_booked FROM customer_inventory WHERE plate_id = ".$plate_id." AND cust_id = ".$cust_id;
	}else{
		$query_qty = "SELECT quantity,qty_booked FROM inventory_item WHERE item_id = ".$plate_id;
	}
	$result_qty = mysqli_query($conn,$query_qty);
	if(mysqli_num_rows($result_qty)>0){
		$data_qty = mysqli_fetch_array($result_qty);
		$quantity = $data_qty['quantity'];
		$qty_booked = $data_qty['qty_booked'];

		$qty = $quantity - $qty_booked;
	}
	
	
	$output_date = $qty;
}

else if($req_no == 5){
	$customer_id = $_POST['customer_id'];
	$output_date = '';

	$query1 = "SELECT a.jd_id,a.job_name,b.quantity,b.id as job_detail_id FROM job_order as a INNER JOIN job_order_details as b on a.jd_id = b.job_id WHERE b.jd_slip_id = 0 AND a.customer_id = ".$customer_id;
	$result1 = mysqli_query($conn,$query1);
	while($data1 = mysqli_fetch_array($result1)){

		$output_date .= '<tr class="this_row">';
		$output_date .= '<td class="text-center this_rows">'.$data1['jd_id'].'</td>';
		$output_date .= '<td>'.$data1['job_name'].'</td>';
		$output_date .= '<td class="text-center">'.$data1['quantity'].'</td>';
		$output_date .= '<td class="text-center slip_selecttd"><input class="form-control" name="slip_select[]" class ="slip_select" type="checkbox" value="'.$data1['job_detail_id'].'" style="width:15px;display:flex;"></td>';
		$output_date .= '</tr>';
	}
}


else if($req_no == 6){
	$output_date="";
	$salary_week = $_POST['salary_week'];
	$week_start_dt = date('Y-m-d',strtotime(trim(explode("**",$salary_week)[0])));
	$week_end_dt =  date('Y-m-d',strtotime(trim(explode("**",$salary_week)[1])));

	$usr_mod_permisions=array();


	$queryAtt = "SELECT a.* FROM attendance as a INNER JOIN employee as b on a.emp_id = b.emp_id WHERE a.dated >= '".$week_start_dt."' AND a.dated <= '".$week_end_dt."' AND a.del_status = 0 AND b.sal_type_id = 2 and b.emp_status = 0";
	$resultAtt = mysqli_query($conn,$queryAtt);
	while($dataAtt = mysqli_fetch_array($resultAtt)){
		array_push($usr_mod_permisions,$dataAtt);
	}

	
	$diff = abs(strtotime($week_end_dt) - strtotime($week_start_dt));
	$daysDiff = floor($diff / (60*60*24)) +1;

	// $queryAttDe = "SELECT * FROM  employee WHERE sal_type_id = 2";
	$queryAttDe = "SELECT a.*,b.salary FROM employee as a INNER JOIN emp_salary as b on a.emp_id = b.emp_id WHERE a.sal_type_id = 2 and a.emp_status = 0";
	$counter=0;
	$resultAttDe = mysqli_query($conn,$queryAttDe);
	while($dataAttDe = mysqli_fetch_array($resultAttDe)){
		$counter++;
		$actHourSum = 0;
		$actMinSum = 0;
		$emp_id = $dataAttDe['emp_id'];
		$emp_name = $dataAttDe['emp_name'];
		$salary = $dataAttDe['salary'];
		$new_date = $week_start_dt;
		for($i=1; $i<=$daysDiff; $i++){

			$st = "0";
			$checkInInd = searchForDt($new_date, $usr_mod_permisions,$st,$emp_id);

			if($new_date>= date('Y-m-d',strtotime('2024-03-12')) && $new_date<= date('Y-m-d',strtotime('2024-04-10'))){
				$shift_start = '09:00';
				$shift_end = '5:00';
			}

			$remarks = "-";
			$remarksColor = "";
			$checkIn = "Missing";
			$dateTimeChkIn = "";
			$checkInColor = "";

			$hasCheckIn = $checkInInd !== null && isset($usr_mod_permisions[$checkInInd][4]);
			if($hasCheckIn){
				$dateTimeChkIn = $usr_mod_permisions[$checkInInd][4];
				$checkIn = date('h:i A',strtotime($dateTimeChkIn));
			}


			$st = "1";
			$dateTimeChkOut ="";
			$checkOutInd = searchForDt($new_date, $usr_mod_permisions,$st,$emp_id);
			$checkOut = "Missing";
			$checkOutColor = "";

			$hasCheckOut = $checkOutInd !== null && isset($usr_mod_permisions[$checkOutInd][4]);
			if($hasCheckOut){
				$dateTimeChkOut = $usr_mod_permisions[$checkOutInd][4];
				$checkOut = date('h:i A',strtotime($dateTimeChkOut));
			}

			$totalTime = "0";
			if($hasCheckOut && $hasCheckIn){
				$expiry_time = new DateTime($dateTimeChkOut);
				$current_date = new DateTime($dateTimeChkIn);
				$diff = $expiry_time->diff($current_date);
				$totalTime = $diff->format('%Hhr %Imin');

				$actHourSum += $diff->format('%H');
				$actMinSum += $diff->format('%I'); 
			}
			$new_date = date('Y-m-d', strtotime($new_date. ' + 1 days')); 
		}

		// Actual Time Sum 
		$hours_new = intdiv($actMinSum, 60);
		$total_minutes = ($actMinSum % 60);
		$total_hoursActSum = $actHourSum+$hours_new;
		$totalTime_sum = $total_hoursActSum.'hr '.$total_minutes.'min';

		// $output_date .= $totalTime_sum.'****';
		if($total_minutes>30){
			$total_hoursActSum += 1;
		}

		$salaryAmt = $total_hoursActSum * $salary;

		$output_date .='<tr class="my_tr">';
		$output_date .='<td class="text-center">'.$counter.'</td>';
		$output_date .='<td class="text-center"><input name="emp_id_week[]" value="'.$emp_id.'" type="hidden">'.$emp_id.'</td>';
		$output_date .='<td>'.$emp_name.'</td>';
		$output_date .='<td class="text-center"><input class="form-control WeekSalary" name="WeekSalary[]" value="'.$salary.'" placeholder="No of Hours" type="text" readonly="true"></td>';
		$output_date .='<td><input class="form-control WeekNoOfHours" name="WeekNoOfHours[]" value="'.$total_hoursActSum.'" placeholder="No of Hours" type="text" readonly="true"></td>';
		$output_date .='<td><input class="form-control WeekExpectedSalary" name="WeekExpectedSalary[]" value="'.$salaryAmt.'" placeholder="Expected Salary" type="number"></td>';
		$output_date .='<td><input class="form-control WeekIncentiveAmt" name="WeekIncentiveAmt[]" placeholder="Incentive Amt" type="number"></td>';
		$output_date .='<td><input class="form-control WeekGrossAmt" name="WeekGrossAmt[]" value="'.$salaryAmt.'" placeholder="Gross Amt" type="number"></td>';
		$output_date .='</tr>';
	}
}


else if($req_no == 7){
	$salary_impression_month = $_POST['salary_impression_month'];
	$output_date = "";
	$queryAttDe = "SELECT a.*,b.salary FROM employee as a INNER JOIN emp_salary as b on a.emp_id = b.emp_id WHERE a.sal_type_id = 3 and a.emp_status = 0 ";
	$counter=0;
	$resultAttDe = mysqli_query($conn,$queryAttDe);
	while($dataAttDe = mysqli_fetch_array($resultAttDe)){
		$counter++;
		$actHourSum = 0;
		$actMinSum = 0;
		$emp_id = $dataAttDe['emp_id'];
		$emp_name = $dataAttDe['emp_name'];
		$salary = $dataAttDe['salary'];

		$output_date .='<tr class="my_tr">';
		$output_date .='<td class="text-center">'.$counter.'</td>';
		$output_date .='<td class="text-center"><input name="emp_id_impression[]" value="'.$emp_id.'" type="hidden">'.$emp_id.'</td>';
		$output_date .='<td>'.$emp_name.'</td>';
		$output_date .='<td class="text-center"><input class="form-control impressSalary" name="impressSalary[]" placeholder="Per Impression" value="'.$salary .'" type="text"></td>';
		$output_date .='<td><input class="form-control noOfImpressions" name="noOfImpressions[]" value="" placeholder="No of Impressions" type="text"></td>';
		$output_date .='<td><input class="form-control ImpExpectedSalary" name="ImpExpectedSalary[]" value="" placeholder="Expected Salary" type="number"></td>';
		$output_date .='<td><input class="form-control ImpIncentiveAmt" name="ImpIncentiveAmt[]" placeholder="Incentive Amt" type="number"></td>';
		$output_date .='<td><input class="form-control ImpGrossAmt" value="'.$salary.'" name="ImpGrossAmt[]" placeholder="Gross Amt" type="number"></td>';
		$output_date .='</tr>';
	}
}



else if($req_no == 8){
	$salary_month = $_POST['salary_month'];
	$output_date = "";

	$from_dt = date('Y-m-01',strtotime($salary_month));
	$to_dt = date('Y-m-t',strtotime($salary_month));

	
	$new_array = get_salary_employees($from_dt,$to_dt,1,$conn);

	for($ii=0; $ii<count($new_array); $ii++){

		$emp_id = $new_array[$ii]['Employee ID'];
		$emp_name = $new_array[$ii]['Employee Name'];
		$total_month_days = $new_array[$ii]['Total Month Days'];
		$per_day_salary = $new_array[$ii]['Per Day Salary'];
		$per_hour_salary = $new_array[$ii]['Per Hour Salary'];
		$present_days = $new_array[$ii]['Present Days'];
		$absent_days = $new_array[$ii]['Absent Days'];
		$late_arrival_days = $new_array[$ii]['Late Arrival Days'];
		$early_departure_days = $new_array[$ii]['Early Departure Days'];
		$half_days = $new_array[$ii]['Half Days'];
		$overtime_hours_working_day = $new_array[$ii]['Overtime Hours Working Day'];
		$overtime_hours_non_working_day = $new_array[$ii]['Overtime Hours Non Working Day'];
		$overtime_hours_gazated_holiday_days = $new_array[$ii]['Overtime Hours Gazated Holiday Day'];
		$late_early_salary_days_deduct = $new_array[$ii]['Late Early Salary Days Deduct'];
		$late_early_deduction = $new_array[$ii]['Late Early Deduction'];
		$half_days_deduction_amt = $new_array[$ii]['Half Days Deduction Amount'];
		$overtime_amt_working_days = $new_array[$ii]['Overtime Amount Working Day'];
		$NWDA_amt = $new_array[$ii]['NWDA Amount'];
		$actual_salary = $new_array[$ii]['Actual Salary'];
		$expected_salary = $new_array[$ii]['Expected Salary'];
		$LESS = $new_array[$ii]['LESS'];
		$ODS = $new_array[$ii]['ODS'];
		$NWDS = $new_array[$ii]['NWDS'];
		$OTHOURSAL = $new_array[$ii]['OTHOURSAL'];
		$NSPHS = $new_array[$ii]['NSPHS'];
		$night_shift_hours = $new_array[$ii]['Night Shift Hours'];
		$night_shift_amt = $new_array[$ii]['Night Shift Amount'];

		$AbsentDeduct = $new_array[$ii]['AbsentDeduct'];
		$LEHD = $new_array[$ii]['LEHD'];
		$OtherAllownces = $new_array[$ii]['OtherAllownces'];

		$tot_late_early = $late_arrival_days + $early_departure_days;
		$tot_overtime_NWD = $overtime_hours_non_working_day + $overtime_hours_gazated_holiday_days;

		$output_date .='<tr class="my_tr" style="font-size: 12px">';
		$output_date .='<td class="text-center"><input name="emp_id_month[]" value="'.$emp_id.'" type="hidden">00'.$emp_id.'</td>';
		$output_date .='<td>'.$emp_name.'</td>';
		$output_date .='<td class="text-center">'.$actual_salary.'</td>';
		$output_date .='<td class="text-center">'.$present_days.'</td>';
		$output_date .='<td class="text-center">'.$absent_days.'</td>';
		$output_date .='<td class="text-center">'.$tot_late_early.'</td>';
		$output_date .='<td class="text-center">'.$overtime_hours_working_day.'</td>';
		$output_date .='<td class="text-center">'.$tot_overtime_NWD.'</td>';
		$output_date .='<td class="text-center">'.$night_shift_hours.'</td>';

		$output_date .='<td>'.$expected_salary.'<input type="hidden" style="width:80px" readonly="true" class="form-control expectedSalary" name="MonthExpectedSalary[]" value="'.$expected_salary.'"></td>';
		$output_date .='<td><input style="width:80px" class="form-control incentiveAmt" name="MonthIcentiveAmt[]" value="0"></td>';
		$output_date .='<td><input style="width:80px" class="form-control grossAmt" name="MonthGrossAmt[]" value="'.$expected_salary.'">
		</td>';

		$output_date .='</tr>';
	}
}



else if ($req_no == 9){
	$output_date = '';
	$acco_no = $_POST['acco_no'];

	$fin_statement_list = array('','Balance Sheet','Profit & Loss');

	$colorRow = '';
	if($acco_no != 100000 && $acco_no != 200000 && $acco_no != 300000 && $acco_no != 400000 && $acco_no != 500000 && $acco_no != 600000 && $acco_no != 700000 && $acco_no != 800000){
		$colorRow = '#f2fcff';
	}
	
	$query3121 = "SELECT parent_id FROM accounts WHERE account_no = ".$acco_no;
	$result3121 = mysqli_query($conn,$query3121);
	$data3121 = mysqli_fetch_array($result3121);
	$pre_parent_id = $data3121['parent_id']; 

	
	$query_acc2 = "SELECT * FROM accounts WHERE parent_id = ".$acco_no;
	$result_acc2 = mysqli_query($conn,$query_acc2);
	while($data_acc2 = mysqli_fetch_array($result_acc2)){

		$entry_exist=0;
		$query31210 = "SELECT * FROM accounts WHERE parent_id = ".$data_acc2['account_no'];
		$result31210 = mysqli_query($conn,$query31210);
		if(mysqli_num_rows($result31210)>0){
			$entry_exist++;
		}

		$output_date .= '<tr style="background:'.$colorRow.'" class="new_tr_'.$acco_no.' new_tr_'.$pre_parent_id.'">';
		$output_date .= '<td></td>';

		if($entry_exist>0){
			$output_date .= '<td>
			<img  style="cursor:pointer;width:18px" class="image plus_icon" src="Accounting/plus.png" alt="plus">
			<img  style="cursor:pointer;width:18px;display:none" class="image minus_icon" src="Accounting/minus.png" alt="minus">
			<img  style="cursor:pointer;width:18px;display:none" class="image loading_gif" src="Accounting/load.gif" alt="minus">
			</td>';
		}
		else{
			$output_date .= '<td>-</td>';
		}
		$output_date .= '<td>'.$data_acc2['account_no'].'
		<input type="hidden" name="account_no_tb" value="'.$data_acc2['account_no'].'" class="account_no_tb">
		</td>';
		$output_date .= '<td colspan="4">'.$data_acc2['acc_name'].'</td>';
		$output_date .= '<td>'.$fin_statement_list[$data_acc2['fin_statement']].'</td>';
		$output_date .= '</tr>';
	}
}













else if($req_no == 10){
	$salary_month = $_POST['salary_hour_month'];
	$output_date = "";

	$from_dt = date('Y-m-01',strtotime($salary_month));
	$to_dt = date('Y-m-t',strtotime($salary_month));

	
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


	$queryRec = "SELECT a.*,b.des_name, c.shift_name, c.shift_start, c.shift_end, c.total_hours, c.grace_time,d.salary FROM employee as a INNER JOIN designations as b on a.emp_designation_id = b.des_id INNER JOIN employee_shifts as c on a.emp_shift_id = c.shift_id INNER JOIN emp_salary as d on a.emp_id = d.emp_id  WHERE a.emp_status = 0 AND a.sal_type_id = 4 AND a.emp_id != 1 order by a.emp_id ASC ";
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





		$total_hours_working = $total_hours;

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

		$total_nightShift_mints = 0;

		$remarks = "-";
		for($i=1; $i<=$daysDiff; $i++){

			$tHour = 0;
			$tMin = 0;
			$counter++;
			$DayOfWeekNumber = date("w",strtotime($new_date));

			if($new_date>= date('Y-m-d',strtotime('2024-03-12')) && $new_date<= date('Y-m-d',strtotime('2024-04-10'))){
				$shift_start = '09:00';
				$shift_end = '5:00';
			}

			$total_hours_working = $total_hours;

			$gazated_holiday_count = 0;
			$gazated_holiday_name = "";
			for($j =0; $j<count($gazated_holidays); $j++){
				if($gazated_holidays[$j][2] == $new_date){
					$gazated_holiday_name = $gazated_holidays[$j][1];
					$gazated_holiday_count++;
					$total_holidays++;
					$total_working_days--;
					$total_hours_working=0;
				}
			}

			$dayName = "";
			$total_hours_working = $total_hours;
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
			if($checkInInd !== null && isset($usr_mod_permisions[$checkInInd][4])){
				$dateTimeChkIn = $usr_mod_permisions[$checkInInd][4];
				$checkIn = date('h:i A',strtotime($dateTimeChkIn));
			// $checkInColor = "#7be77b";
			}else{
				$checkInMissingCount++;
				$remarks = 'Attendance Missing';
				$remarksColor = "#ff6c6c";
				$checkInColor = "#ff6c6c";
			}


			$st = "1";
			$dateTimeChkOut ="";
			$checkOutInd = searchForDt($new_date, $usr_mod_permisions,$st,$emp_id);
			$checkOut = "Missing";
			$checkOutColor = "";
			// $checkOutColor = "#ff6c6c";
			if($checkOutInd !== null && isset($usr_mod_permisions[$checkOutInd][4])){
				$dateTimeChkOut = $usr_mod_permisions[$checkOutInd][4];
				$checkOut = date('h:i A',strtotime($dateTimeChkOut));
			// $checkOutColor = "#7be77b";
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
					$remarks = 'Attendance Missing';
					$remarksColor = "#ff6c6c";
					$checkOutColor = "#ff6c6c";
				}

				
			}

			$totalTime = "0";
			if($checkOutInd !== null && $checkInInd !== null){
				$expiry_time = new DateTime($dateTimeChkOut);
				$current_date = new DateTime($dateTimeChkIn);
				$diff = $expiry_time->diff($current_date);
				$totalTime = $diff->format('%Hhr %Imin');


				$tHour += $diff->format('%H');
				$tMin += $diff->format('%I'); 

				$actHourSum += $diff->format('%H');
				$actMinSum += $diff->format('%I'); 
			}


			if($checkOutInd !== null || $checkInInd !== null){
				$SumPresentDay++;
			}



			if($checkInInd === null && $checkOutInd === null && $gazated_holiday_name== "" && $DayOfWeekNumber != 0){
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


				if($early_dep_mints > $grace_time){
					$earyly_dep = $diff2->format('%Hhr %Imin'); 
					$earlyColor = "#ff6c6c";

					if($remarks == "-"){
						$remarks = 'Early Departure';
					}
					$remarksColor = "#ff6c6c";

					$earlyHourSum += $diff2->format('%H'); 
					$earlyMinSum += $diff2->format('%I'); 

					$SumEarlyDep++;
				}
			// else{
			// 	$earlyColor = "#7be77b";
			// }
			}


			$excessColor = "";
			$excess_time = "-";
			if($checkOutInd !== null && $checkInInd !== null){
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
				}
			}



			// Night Shift
			// NSPHS
			// Check if Allow
			$query_check = "SELECT * FROM night_shift_perm WHERE employee_id = ".$emp_id." AND dated = '".$new_date."' AND del_status = 0";
			$result_check = mysqli_query($conn,$query_check);
			if(mysqli_num_rows($result_check)>0){

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

					if($hours_nightshifts>=4){
						$total_nightShift_mints += $night_shift_mints;
					}else{
						$total_overtime_mints_working_day_count += $night_shift_mints;
					}
				}
			}








			$total_hours_sum += $total_hours_working;





			$new_date = date('Y-m-d', strtotime($new_date. ' + 1 days')); 

			// Actual Time Sum 
			$hours_new = intdiv($actMinSum, 60);
			$total_minutes = ($actMinSum % 60);
			$total_hoursActSum = $actHourSum+$hours_new;
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

		$absentCounting = $absentCount;


		// $p_days = $SumPresentDay + $total_holidays;
		$output_date .='<tr class="my_tr">';
		$output_date .='<td class="text-center"><input name="emp_id_month[]" value="'.$emp_id.'" type="hidden">00'.$emp_id.'</td>';
		$output_date .='<td>'.$emp_name.'</td>';
		$output_date .='<td  style="width:80px" class="text-center"><input class="form-control perMonthSalary" name="perMonthSalary[]" placeholder="Per Month" readonly="true" value="'.$salary .'" type="text"></td>';
		$output_date .='<td><input style="width:60px" readonly="true" class="form-control presentDays" name="presentDays[]" value="'.$SumPresentDay.'" type="text"></td>';

		$totalLateEarly = $SumLateArrival + $SumEarlyDep;
		
		//  Polices		
		$NWDS = get_policy($conn,'NWDS');
		$LESS = get_policy($conn,'LESS');
		$ODS = get_policy($conn,'ODS');

		$per_day_salary = $salary/$SumTotalDays;


		// Overtime Hours Salary Count
		$totalExcessum = $total_hours_excess;
		if($total_minutes_excess >30){
			$totalExcessum = $total_hours_excess + 1; 
		}
		$b=0;
		$ExcessHoursSalary=0;
		for($j=$totalExcessum; $j>=1; $j--){
			$b++;
			if($b == $ODS){
				$b = 0;
				$ExcessHoursSalary++;
			}
		}
		
		// Late Early Salary Count
		$a=0;
		$lateEarlySalary=0;
		$halfDay = 0;
		for($j=$totalLateEarly; $j>=1; $j--){
			$a++;
			if($a == $LESS){
				$a = 0;
				$lateEarlySalary += 0.5;
				// $halfDay = 0.6
			}
		}
		
		//  Non Working Day Salary Count
		$nonWorkingDaySalary = 0;
		if($SumWorkNonWorking>0){
			$nonWorkingDaySalary =+ $NWDS;
		}


		$total_day_seleceted = $SumPresentDay + $total_holidays;
		$total_day_seleceted -= $lateEarlySalary;
		$total_day_seleceted += $nonWorkingDaySalary;
		$total_day_seleceted += $ExcessHoursSalary;

		//$gross_salary = ceil($total_day_seleceted * $per_day_salary);

		
		$present_days_amt = $per_day_salary * ($SumPresentDay + $total_holidays);
		$absent_day_amt = $per_day_salary * $absentCounting;
		$late_early_amt = $per_day_salary * $lateEarlySalary; 
		$overtime_amt = $per_day_salary * $ExcessHoursSalary;
		$non_working_day_amt = $per_day_salary * $nonWorkingDaySalary;


		$per_hour_month_salary = $per_day_salary/8;
		$per_hour_month_salary_half = $per_day_salary/16;
		$new_ov = $per_hour_month_salary + $per_hour_month_salary_half;

		$overtime_amt_new = $totalExcessum * $new_ov;



		// Night shift amount
		$hours_new_nightshift = intdiv($total_nightShift_mints, 60);
		$nightshift_hours_amount = round(($hours_new_nightshift * $NSPHS) * $per_hour_month_salary);
		

		$gross_salary = ceil($salary - $absent_day_amt - $late_early_amt + $overtime_amt_new + $non_working_day_amt);

		$a = ($gross_salary%100);
		$b = 100-$a;
		$gross_salary = $gross_salary + $b;

		
		$output_date .='<td>
		<input type="hidden" class="NWDS" name="NWDS[]" value="'.$NWDS.'">
		<input type="hidden" class="LESS" name="LESS[]" value="'.$LESS.'">
		<input type="hidden" class="ODS" name="ODS[]" value="'.$ODS.'">
		<input type="hidden" class="present_days_amt" name="present_days_amt[]" value="'.$present_days_amt.'">
		<input type="hidden" class="absent_day_amt" name="absent_day_amt[]" value="'.$absent_day_amt.'">
		<input type="hidden" class="late_early_amt" name="late_early_amt[]" value="'.$late_early_amt.'">
		<input type="hidden" class="overtime_amt" name="overtime_amt[]" value="'.$overtime_amt_new.'">
		<input type="hidden" class="non_working_day_amt" name="non_working_day_amt[]" value="'.$non_working_day_amt.'">

		<input style="width:50px" readonly="true" class="form-control absentDays" name="absentDays[]" value="'.$absentCounting.'"></td>';
		$output_date .='<td><input style="width:50px" readonly="true" class="form-control lateEarly" name="lateEarly[]" value="'.$totalLateEarly.'"></td>';
		$output_date .='<td><input style="width:50px" readonly="true" class="form-control overtimeHours" name="overtimeHours[]" value="'.$totalExcessum.'"></td>';
		$output_date .='<td><input style="width:50px" readonly="true" class="form-control nonWorkingDays" name="nonWorkingDays[]" value="'.$SumWorkNonWorking.'"></td>';
		$output_date .='<td><input style="width:80px" readonly="true" class="form-control expectedSalary" name="MonthExpectedSalary[]" value="'.$gross_salary.'"></td>';
		$output_date .='<td><input style="width:80px" class="form-control incentiveAmt" name="MonthIcentiveAmt[]" value="0"></td>';
		$output_date .='<td><input style="width:80px" class="form-control grossAmt" name="MonthGrossAmt[]" value="'.$gross_salary.'"></td>';
		$output_date .='</tr>';

	}

}












else if($req_no == 11){

	$item_id = $_POST['item_id'];

	$query1_item = "SELECT * FROM inventory_item WHERE item_id =".$item_id;
	$result1 = mysqli_query($conn,$query1_item);
	while($data1 = mysqli_fetch_array($result1))
	{
		$avg_rate = $data1['avg_rate'];
		$exposing_price = $data1['exposing_price'];
		$imposition_charges = $data1['imposition_charges'];
		$OvenBake_Charges = $data1['OvenBake_Charges'];

		$output_date = $avg_rate.'^^'.$exposing_price.'^^'.$imposition_charges.'^^'.$OvenBake_Charges;
	}

}
echo $output_date;
?>
