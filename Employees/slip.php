<?php
require_once('../tcpdf/tcpdf.php');
include '../db_connect.php';

if(isset($_GET['ref'])){

	$slip_d_id = $_GET['ref'];

	class MYPDF extends TCPDF 
	{
		public function Header() 
		{
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

			$this->SetFont('helvetica', 'H', 16);
			$image_file = K_PATH_IMAGES.'logo.jpg';


			$this->Image($image_file, 10, 10, 35, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
			$this->writeHTML($labels, true, 1, true, 1, '');

			$this->Image($image_file, 150, 10, 35, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
			$this->writeHTML($labels, true, 1, true, 1, '');
		}
	}



	$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
 	// set document information

	$pdf->SetCreator(PDF_CREATOR);
	$pdf->SetAuthor('Salary Slip');
	$pdf->SetTitle('Salary Slip');
	$pdf->SetSubject('Salary Slip');
	$pdf->SetKeywords('Salary Slip');
	$PDF_HEADER_LOGO_WIDTH = "20";
	$PDF_HEADER_TITLE = "Salary Slip";
	$PDF_HEADER_STRING = "Salary Slip";
	$pdf->SetHeaderData(PDF_HEADER_LOGO, $PDF_HEADER_LOGO_WIDTH, $PDF_HEADER_TITLE, $PDF_HEADER_STRING); 
	$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
	$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
	$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

	$pdf->SetMargins(5, 24, 5);

	$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
	$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);


	$bMargin = $pdf->getBreakMargin();
	$auto_page_break = $pdf->getAutoPageBreak();
	$pdf->SetAutoPageBreak($auto_page_break, $bMargin);
	$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
	if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
		require_once(dirname(__FILE__).'/lang/eng.php');
		$pdf->setLanguageArray($l);
	}

	$pdf->AddPage('L', 'A4');
	$pdf->SetFont('helvetica', '', 8);

	
	$adjustment_amount = 0;

	$query_slip = "SELECT a.*,b.emp_name,b.emp_designation_id, c.st_type_name,des.des_name FROM salary_slip_info as a INNER JOIN employee as b on a.emp_id = b.emp_id INNER JOIN salary_type as c on a.sal_type_id = c.st_id INNER JOIN salary_slip as d on a.slip_id = d.sp_id INNER JOIN designations as des on b.emp_designation_id = des.des_id WHERE a.status = 0 AND a.slip_info_id = ".$slip_d_id." order by a.slip_info_id ASC";

	$result_slip = mysqli_query($conn,$query_slip);
	if(mysqli_num_rows($result_slip)>0){
		$row = mysqli_fetch_array($result_slip);
		$slip_info_id = $row['slip_info_id'];
		$slip_id = $row['slip_id'];
		$emp_id = $row['emp_id'];
		$emp_name = $row['emp_name'];
		$sal_type_id = $row['sal_type_id'];
		$month_year = $row['month_year'];
		$emp_salary = $row['emp_salary'];
		$total_month_days = $row['total_month_days'];
		$per_day_salary = $row['per_day_salary'];
		$per_hour_salary = $row['per_hour_salary'];
		$present_days = $row['present_days'];
		$absent_days = $row['absent_days'];
		$late_arrival_days = $row['late_arrival_days'];
		$early_departure_days = $row['early_departure_days'];
		$half_days = $row['half_days'];
		$emp_designation_id = $row['emp_designation_id'];
		$des_name = $row['des_name'];

		$overtime_hours_working_day = $row['overtime_hours_working_day'];
		$overtime_hours_non_working_day = $row['overtime_hours_non_working_day'];
		$overtime_hours_gazated_holiday = $row['overtime_hours_gazated_holiday'];
		
		$late_early_salary_days_deduct = $row['late_early_salary_days_deduct'];
		$late_early_deduction = $row['late_early_deduction'];
		$half_days_deduction_amt = $row['half_days_deduction_amt'];
		$overtime_amt_working_days = $row['overtime_amt_working_days'];
		$LESS = $row['LESS'];
		$ODS = $row['ODS'];
		$NWDS = $row['NWDS'];;
		$OTHOURSAL = $row['OTHOURSAL'];
		$NSPHS = $row['NSPHS'];
		$night_shift_hours = $row['night_shift_hours'];
		$night_shift_amt = $row['night_shift_amt'];

		$expected_salary = $row['expected_salary'];
		$incentive_amt = $row['incentive_amt'];
		$month_gross_amt = $row['month_gross_amt'];
		$st_type_name = $row['st_type_name'];
		$NWDA_amt = $row['NWDA_amt'];
		$absent_days_deduction = $row['absent_days_deduction'];

		$week_start = $row['week_start'];
		$week_end = $row['week_end'];
		$no_of_hours = $row['no_of_hours'];
		$no_of_impressions = $row['no_of_impressions'];



		// $adjustment_amount = 0;

		// $adjustment_amount = $month_gross_amt - (($overtime_amt_working_days + $NWDA_amt + $incentive_amt) - $half_days_deduction_amt - $late_early_deduction);

		$periodName = "";
		if($sal_type_id == 1){
			$periodName = date('M-Y',strtotime($month_year));
		}
		else if($sal_type_id == 2){
			$periodName = date('d-M-Y',strtotime($week_start)).' '.date('d-M-Y',strtotime($week_end));
		}
		else if($sal_type_id == 3){
			$periodName = date('M-Y',strtotime($month_year));
		}


		// $labels='<br><br>';

		$labels='<table  width="100%">';
		$labels.= '<tr>
		<td style="width:50%">
		<label style="text-align:center">
		<span style="font-size:16px;"><strong>Salary Slip</strong></span>
		<span>(Office Copy)</span>
		</label>
		</td>
		<td style="width:50%">
		<label style="text-align:center">
		<span style="font-size:16px;"><strong>Salary Slip</strong></span>
		<span>(Employee Copy)</span>
		</label>

		</td> 
		</tr>
		</table>';

		$labels.='<br><table>';
		$labels.='<tr>';

		$labels.='<td style="width:48%">';
		$labels.='<table cellpadding="1" width="100%">';
		$labels.= '<tr>



		<td style="width:50%">
		<label>
		<span style="font-size:10px;"><strong>Slip No:</strong></span>
		</label>
		<span style="color:blue;font-size:10px;">'.$slip_info_id.'</span>
		</td>
		<td style="width:50%">
		<label style="text-align:right">
		<span style="font-size:9px;"><strong>Salary Type:</strong> </span>
		<span style="color:blue;font-size:9px;">'.$st_type_name.'</span>
		</label>
		</td></tr>

		<tr>
		<td style="width:35%">
		<label>
		<span style="font-size:10px;"><strong>Employee Name:</strong></span>
		</label>
		<span style="color:blue;font-size:10px;">'.$emp_name.'</span>
		</td>

		<td style="width:30%;text-align:center">
		<label>
		<span style="font-size:10px;"><strong>Designation:</strong></span>
		</label>
		<span style="color:blue;font-size:10px;">'.$des_name.'</span>
		</td>

		<td style="width:35%">
		<label style="text-align:right">
		<span style="font-size:9px;"><strong>Period:</strong> </span>
		<span style="color:blue;font-size:9px;">'.$periodName.'</span>
		</label>
		</td>
		</tr>';

		// if($sal_type_id == 2){
		// 	$labels.='<tr>';
		// 	$labels.='<td colspan="2">
		// 	<label>
		// 	<span style="font-size:10px;"><strong>Designation:</strong></span>
		// 	</label>
		// 	<span style="color:blue;font-size:10px;">'.$des_name.'</span>

		// 	</td>

		// 	<td style="border:1px solid black"><span style="font-size:12px;">&nbsp;&nbsp;&nbsp;&nbsp;<strong>Hourly Rate:</strong></span><span style="color:green;font-size:12px;border:1px solid black">&nbsp;<b>@'.number_format($emp_salary).'/Hour</b></span></td>';
		// 	$labels.='</tr>';
		// }
		$labels.='</table>';
		$labels.='</td>';




		$labels.='<td style="width:4%;text-align:center"> |<br> |<br> |</td>';

		$labels.='<td style="width:48%">';
		$labels.='<table cellpadding="1" width="100%">';
		$labels.= '<tr>
		<td style="width:50%">
		<label>
		<span style="font-size:10px;"><strong>Slip No:</strong></span>
		</label>
		<span style="color:blue;font-size:10px;">'.$slip_info_id.'</span>
		</td>
		<td style="width:50%">
		<label style="text-align:right">
		<span style="font-size:9px;"><strong>Salary Type:</strong> </span>
		<span style="color:blue;font-size:9px;">'.$st_type_name.'</span>
		</label>
		</td></tr>
		<tr>
		<td style="width:35%">
		<label>
		<span style="font-size:10px;"><strong>Employee Name:</strong></span>
		</label>
		<span style="color:blue;font-size:10px;">'.$emp_name.'</span>
		</td>


		<td style="width:30%;text-align:center">
		<label>
		<span style="font-size:10px;"><strong>Designation:</strong></span>
		</label>
		<span style="color:blue;font-size:10px;">'.$des_name.'</span>
		</td>

		<td style="width:35%">
		<label style="text-align:right">
		<span style="font-size:9px;"><strong>Period:</strong> </span>
		<span style="color:blue;font-size:9px;">'.$periodName.'</span>
		</label>
		</td>
		</tr>';

		// if($sal_type_id == 2){
		// 	$labels.='<tr>';
		// 	$labels.='<td colspan="2">
		// 	<label>
		// 	<span style="font-size:10px;"><strong>Designation:</strong></span>
		// 	</label>
		// 	<span style="color:blue;font-size:10px;">'.$des_name.'</span>

		// 	</td>

		// 	<td style="border:1px solid black"><span style="font-size:12px;">&nbsp;&nbsp;&nbsp;&nbsp;<strong>Hourly Rate:</strong></span><span style="color:green;font-size:12px;border:1px solid black">&nbsp;<b>@'.number_format($emp_salary).'/Hour</b></span></td>';
		// 	$labels.='</tr>';
		// }
		$labels.='</table>';
		$labels.='</td>';


		// if($sal_type_id == 2){


			// $data_details = '<tr>';
			// $data_details .= '<td style="font-size:11px" colspan="3"><b>Hourly Rate</b>
			// <span style="float:right;color:green">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>@'.number_format($emp_salary).'/Hour</b></span> </td>';
			// // $data_details .= '<td style="text-align:right;color:green;font-size:11px"></td>';


		$labels.='</tr>';
		$labels.='</table>';

		$data_details = '';

		// $data_details = '<tr>';
		// $data_details .= '<td colspan="3"></td>';
		// $data_details .= '</tr>';

		if($sal_type_id == 1){

			$data_details = '<tr>';
			$data_details .= '<td style="width:70%"><b>Earnings:</b></td>';
			$data_details .= '<td style="width:10%"></td>';
			$data_details .= '<td style="width:20%"></td>';
			$data_details .= '</tr>';

			$data_details .= '<tr>';
			$data_details .= '<td colspan="2">Basic Salary</td>';
			$data_details .= '<td style="text-align:right;color:green">'.number_format($emp_salary).'</td>';
			$data_details .= '</tr>';


			if($emp_designation_id == 3){
				$data_details .= '<tr>';
				$data_details .= '<td>Overtime Hours</td>';
				$data_details .= '<td style="text-align:center">'.$overtime_hours_working_day.'</td>';
				$data_details .= '<td style="text-align:right;color:green">'.number_format($overtime_amt_working_days).'</td>';
				$data_details .= '</tr>';


				$gaz_nwd = $overtime_hours_non_working_day + $overtime_hours_gazated_holiday;
				$data_details .= '<tr>';
				$data_details .= '<td>Non Working Days Hours</td>';
				$data_details .= '<td style="text-align:center">'.$gaz_nwd.'</td>';
				$data_details .= '<td style="text-align:right;color:green">'.number_format($NWDA_amt).'</td>';
				$data_details .= '</tr>';
				$data_details .= '<tr>';
				$data_details .= '<td>Night Shifts Hours</td>';
				$data_details .= '<td style="text-align:center">'.$night_shift_hours.'</td>';
				$data_details .= '<td style="text-align:right;color:green">'.number_format($night_shift_amt).'</td>';
				$data_details .= '</tr>';				
			}
			if($incentive_amt>0){
				$data_details .= '<tr>';
				$data_details .= '<td>Incentive Amount</td>';
				$data_details .= '<td style="text-align:center">-</td>';
				$data_details .= '<td style="text-align:right;color:green">'.number_format($incentive_amt).'</td>';
				$data_details .= '</tr>';
			}



			$data_details .= '<tr>';
			$data_details .= '<td colspan="3"></td>';
			$data_details .= '</tr>';


			$data_details .= '<tr>';
			$data_details .= '<td><b>Deductions:</b></td>';
			$data_details .= '<td style="text-align:right"></td>';
			$data_details .= '</tr>';
			$data_details .= '<tr>';
			$data_details .= '<td>Absent Days</td>';
			$data_details .= '<td style="text-align:center">'.$absent_days.'</td>';
			$data_details .= '<td style="text-align:right;color:red">('.number_format($absent_days_deduction).')</td>';
			$data_details .= '</tr>';
			$data_details .= '<tr>';
			$data_details .= '<td>Half Days</td>';
			$data_details .= '<td style="text-align:center">'.$half_days.'</td>';
			$data_details .= '<td style="text-align:right;color:red">('.number_format($half_days_deduction_amt).')</td>';
			$data_details .= '</tr>';

			$late_dep =	$late_arrival_days + $early_departure_days;
			$data_details .= '<tr>';
			$data_details .= '<td>Late Arrival / Early Departure</td>';
			$data_details .= '<td style="text-align:center">'.$late_dep.'</td>';
			$data_details .= '<td style="text-align:right;color:red">('.number_format($late_early_deduction).')</td>';
			$data_details .= '</tr>';


			$data_details .= '<tr>';
			$data_details .= '<td colspan="3"></td>';
			$data_details .= '</tr>';

			$data_details .= '<tr>';
			$data_details .= '<td colspan="2"><b>Gross Salary</b></td>';
			$data_details .= '<td style="text-align:right;color:green"><b>Rs. '.number_format($month_gross_amt).'/=</b></td>';
			$data_details .= '</tr>';
		}
		else if($sal_type_id == 2){


			$data_details = '<tr>';
			$data_details .= '<td style="font-size:11px" colspan="3"><b>Hourly Rate:</b>
			<span style="float:right;color:green">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>@'.number_format($emp_salary).'/Hour</b></span> </td>';
			// $data_details .= '<td style="text-align:right;color:green;font-size:11px"></td>';
			$data_details .= '</tr>';

			$data_details .= '<tr>';
			$data_details .= '<td colspan="3"></td>';
			$data_details .= '</tr>';

			$data_details .= '<tr>';
			$data_details .= '<td colspan="3"><b>Earnings:</b></td>';
			$data_details .= '</tr>';

			$data_details .= '<tr>';
			$data_details .= '<td style="width:70%">Total Worked Hours</td>';
			$data_details .= '<td  style="text-align:center;width:10%">'.$no_of_hours.'</td>';
			$data_details .= '<td style="text-align:right;color:green;width:20%">'.number_format($expected_salary).'</td>';
			$data_details .= '</tr>';
			$data_details .= '<tr>';
			$data_details .= '<td>Incentive Amount</td>';
			$data_details .= '<td style="text-align:center">-</td>';
			$data_details .= '<td style="text-align:right;color:green">'.number_format($incentive_amt).'</td>';
			$data_details .= '</tr>';


			$data_details .= '<tr>';
			$data_details .= '<td colspan="3"></td>';
			$data_details .= '</tr>';


			$data_details .= '<tr>';
			$data_details .= '<td colspan="2"><b>Gross Salary</b></td>';
			$data_details .= '<td style="text-align:right;color:green"><b>Rs. '.number_format($month_gross_amt).'/=</b></td>';
			$data_details .= '</tr>';
		}
		else if($sal_type_id == 3){
			$data_details = '<tr>';
			$data_details .= '<td style="width:70%"><b>Earnings:</b></td>';
			$data_details .= '<td style="width:10%"></td>';
			$data_details .= '<td style="width:20%"></td>';
			$data_details .= '</tr>';

			$data_details .= '<tr>';
			$data_details .= '<td colspan="2">Per Impression Rate</td>';
			$data_details .= '<td style="text-align:right;color:green">'.number_format($emp_salary).'</td>';
			$data_details .= '</tr>';

			$data_details .= '<tr>';
			$data_details .= '<td>Total No of Impressionss</td>';
			$data_details .= '<td style="text-align:center">'.$no_of_impressions.'</td>';
			$data_details .= '<td style="text-align:right;color:green">'.number_format($expected_salary).'</td>';
			$data_details .= '</tr>';
			$data_details .= '<tr>';
			$data_details .= '<td>Incentive Amount</td>';
			$data_details .= '<td style="text-align:center">-</td>';
			$data_details .= '<td style="text-align:right;color:green">'.number_format($incentive_amt).'</td>';
			$data_details .= '</tr>';


			$data_details .= '<tr>';
			$data_details .= '<td colspan="3"></td>';
			$data_details .= '</tr>';


			$data_details .= '<tr>';
			$data_details .= '<td colspan="2"><b>Gross Salary</b></td>';
			$data_details .= '<td style="text-align:right;color:green"><b>Rs. '.number_format($month_gross_amt).'/=</b></td>';
			$data_details .= '</tr>';
		}




		$description_table_1 = '';
		$description_table_1 .= '<table>';
		$description_table_1 .= '<tr>';

		$description_table_1 .= '<td style="width:48%">';

		$description_table_1 .= '<br><table border="1" cellpadding="2" width="100%" style="font-size:10px">';

		$description_table_1 .= '<tr style="text-align:center;font-weight:bold;" bgcolor="#efefef" >';
		$description_table_1 .= '<th colspan="2" style="width:80%">Particuler</th>';
		$description_table_1 .= '<th style="width:20%">Amount</th>';
		$description_table_1 .= '</tr>';

		$description_table_1 .= $data_details;

		$description_table_1 .= '</table>';
		$description_table_1 .= '</td>';



		if($sal_type_id == 1){
			if($emp_designation_id == 3){
				$description_table_1 .= '<td style="width:4%;text-align:center">|<br>|<br>|<br>|<br>|<br>|<br>|<br>|<br>|<br>|<br>|<br>|<br>|<br>|<br>|<br>|<br>|<br>|</td>';
			}else{
				$description_table_1 .= '<td style="width:4%;text-align:center">|<br>|<br>|<br>|<br>|<br>|<br>|<br>|<br>|<br>|<br>|<br>|<br>|<br>|</td>';
			}
		}
		else if($sal_type_id == 2){
			$description_table_1 .= '<td style="width:4%;text-align:center">|<br>|<br>|<br>|<br>|<br>|<br>|<br>|<br>|<br>|<br>|</td>';
		}
		else if($sal_type_id == 3){
			$description_table_1 .= '<td style="width:4%;text-align:center">|<br>|<br>|<br>|<br>|<br>|<br>|<br>|<br>|<br>|<br>|</td>';
		}


		$description_table_1 .= '<td style="width:48%">';
		$description_table_1 .= '<br><table border="1" cellpadding="2" width="100%" style="font-size:10px">';

		$description_table_1 .= '<tr style="text-align:center;font-weight:bold;" bgcolor="#efefef" >';
		$description_table_1 .= '<th colspan="2" style="width:80%">Particuler</th>';
		$description_table_1 .= '<th style="width:20%">Amount</th>';
		$description_table_1 .= '</tr>';

		$description_table_1 .= $data_details;

		$description_table_1 .= '</table>';
		$description_table_1 .= '</td>';

		$description_table_1 .= '</tr>';




		// Attendance
		function searchForDt($id, $array,$st,$empId) {
			for($j =0; $j<count($array); $j++){
				if($array[$j][2] == $id && $array[$j][5]==$st && $array[$j][1]==$empId){
					return $j;
				}
			}
			return null;
		}


		$usr_mod_permisions=array();

		if($sal_type_id == 2){

		   // $periodName = date('d-M-Y',strtotime($week_start)).' '.date('d-M-Y',strtotime($week_end));
		    //$aa = 
			$from_dt = date('Y-m-d',strtotime($week_start));
			$to_dt = date('Y-m-d',strtotime($week_end));
		}else{
			$from_dt = date('Y-m-01',strtotime($month_year));
			$to_dt = date('Y-m-t',strtotime($month_year));
		}


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


		$queryRec = "SELECT a.*,b.des_name, c.shift_name, c.shift_start, c.shift_end, c.total_hours, c.grace_time FROM employee as a INNER JOIN designations as b on a.emp_designation_id = b.des_id INNER JOIN employee_shifts as c on a.emp_shift_id = c.shift_id WHERE a.emp_status = 0 and a.emp_id = ".$emp_id;
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




			if($sal_type_id != 2){
				$att1 = '<table border="1" cellpadding="1" width="100%" style="font-size:9px">';
				$att1 .= '<tr>';
				$att1 .= '<td style="text-align:center;width:15%"><b>Day</b></td>';
				$att1 .= '<td style="text-align:center;width:25%"><b>In</b></td>';
				$att1 .= '<td style="text-align:center;width:25%"><b>Out</b></td>';
				$att1 .= '<td style="text-align:center;width:35%"><b>Time</b></td>';
				$att1 .= '</tr>';
			}else{
				$att1 = '<table border="1" cellpadding="1" width="100%" style="font-size:9px">';
				$att1 .= '<tr>';
				$att1 .= '<td style="text-align:center;"><b>Date</b></td>';
				$att1 .= '<td style="text-align:center;"><b>Check In</b></td>';
				$att1 .= '<td style="text-align:center;"><b>CheckOut</b></td>';
				$att1 .= '<td style="text-align:center;"><b>Total Time</b></td>';
				$att1 .= '</tr>';
			}




			$att2 = '<table border="1" cellpadding="1" width="100%" style="font-size:9px">';
			$att2 .= '<tr>';
			$att2 .= '<td style="text-align:center;width:15%"><b>Day</b></td>';
			$att2 .= '<td style="text-align:center;width:25%"><b>In</b></td>';
			$att2 .= '<td style="text-align:center;width:25%"><b>Out</b></td>';
			$att2 .= '<td style="text-align:center;width:35%"><b>Time</b></td>';
			$att2 .= '</tr>';

			$att3 = '<table border="1" cellpadding="1" width="100%" style="font-size:9px">';
			$att3 .= '<tr>';
			$att3 .= '<td style="text-align:center;width:15%"><b>Day</b></td>';
			$att3 .= '<td style="text-align:center;width:25%"><b>In</b></td>';
			$att3 .= '<td style="text-align:center;width:25%"><b>Out</b></td>';
			$att3 .= '<td style="text-align:center;width:35%"><b>Time</b></td>';
			$att3 .= '</tr>';



			$diff = abs(strtotime($to_dt) - strtotime($from_dt));
			$daysDiff = floor($diff / (60*60*24)) +1;

			$new_date = $from_dt;
			$counter = 0;
			$checkInMissingCount = 0;
			$checkOutMissingCount = 0;
			$total_holidays = 0;
			$total_working_days = 0;

			$val_this = 10;
			if($sal_type_id == 2){
				$val_this = 7;
			}

			for($i=1; $i<=$val_this; $i++){

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
					$foundCheckOut = 0;
					if($gazated_holiday_name== "" && $DayOfWeekNumber != 0){

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
						$remarks = 'Attendance Missing';
						$remarksColor = "#ff6c6c";
						$checkOutColor = "#ff6c6c";
					}
				}


				$totalTime = "0";
				if($checkOutInd != "" && $checkInInd != ""){
					$expiry_time = new DateTime($dateTimeChkOut);
					$current_date = new DateTime($dateTimeChkIn);
					$diff = $expiry_time->diff($current_date);
					$totalTime = $diff->format('%Hhr %Imin'); 
                     // $totalTime = $diff->format('%H:%I:%S');

					$tHour += $diff->format('%H'); 
					$tMin += $diff->format('%I'); 
				}

				$att1 .= '<tr style="font-size:8px;text-align:center">';
				if($sal_type_id != 2){
					if($DayOfWeekNumber != 0 && $gazated_holiday_name == ''){


						$att1 .= '<td>'.date('d',strtotime($new_date)).'</td>';
						$att1 .= '<td style="background-color:'.$checkInColor.'">'.$checkIn.'</td>';
						$att1 .= '<td style="background-color:'.$checkOutColor.'">'.$checkOut.'</td>';
						$att1 .= '<td style="text-align:center;">'.$totalTime.'</td>';
					}
					else{
						$att1 .= '<td style="background-color:pink">'.date('d',strtotime($new_date)).'</td>';
						if($checkIn != "Missing" || $checkOut != "Missing"){
							$att1 .= '<td style="text-align:center;background-color:pink">'.$checkIn.'</td>';
							$att1 .= '<td style="text-align:center;background-color:pink">'.$checkOut.'</td>';
							$att1 .= '<td style="text-align:center;background-color:pink">'.$totalTime.'</td>';

						}else{
							if($gazated_holiday_name == ''){
								$att1 .= '<td colspan="3" style="text-align:center;background-color:pink">WeekEnd Holiday</td>';
							}else{
								$att1 .= '<td colspan="3" style="text-align:center;background-color:pink">'.$gazated_holiday_name.'</td>';
							}
						}

					}
				}else{
					if($DayOfWeekNumber != 0 && $gazated_holiday_name == ''){


						$att1 .= '<td>'.date('d-M-Y',strtotime($new_date)).'</td>';
						$att1 .= '<td style="background-color:'.$checkInColor.'">'.$checkIn.'</td>';
						$att1 .= '<td style="background-color:'.$checkOutColor.'">'.$checkOut.'</td>';
						$att1 .= '<td style="text-align:center;">'.$totalTime.'</td>';

					}
					else{
						$att1 .= '<td style="background-color:pink">'.date('d-M-Y',strtotime($new_date)).'</td>';
						if($checkIn != "Missing" || $checkOut != "Missing"){
							$att1 .= '<td style="text-align:center;background-color:pink">'.$checkIn.'</td>';
							$att1 .= '<td style="text-align:center;background-color:pink">'.$checkOut.'</td>';
							$att1 .= '<td style="text-align:center;background-color:pink">'.$totalTime.'</td>';
						}else{
							if($gazated_holiday_name == ''){
								$att1 .= '<td colspan="3" style="text-align:center;background-color:pink">WeekEnd Holiday</td>';
							}else{
								$att1 .= '<td colspan="3" style="text-align:center;background-color:pink">'.$gazated_holiday_name.'</td>';
							}
						}

					}
				}
				$att1 .= '</tr>';


				$new_date = date('Y-m-d', strtotime($new_date. ' + 1 days'));
			}

			for($i=11; $i<=20; $i++){

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
					$foundCheckOut = 0;
					if($gazated_holiday_name== "" && $DayOfWeekNumber != 0){

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
						$remarks = 'Attendance Missing';
						$remarksColor = "#ff6c6c";
						$checkOutColor = "#ff6c6c";
					}
				}

				$totalTime = "0";
				if($checkOutInd != "" && $checkInInd != ""){
					$expiry_time = new DateTime($dateTimeChkOut);
					$current_date = new DateTime($dateTimeChkIn);
					$diff = $expiry_time->diff($current_date);
					$totalTime = $diff->format('%Hhr %Imin'); 
                     // $totalTime = $diff->format('%H:%I:%S');

					$tHour += $diff->format('%H'); 
					$tMin += $diff->format('%I'); 
				}

				$att2 .= '<tr style="font-size:8px;text-align:center">';
				if($DayOfWeekNumber != 0 && $gazated_holiday_name == ''){


					$att2 .= '<td>'.date('d',strtotime($new_date)).'</td>';
					$att2 .= '<td style="background-color:'.$checkInColor.'">'.$checkIn.'</td>';
					$att2 .= '<td style="background-color:'.$checkOutColor.'">'.$checkOut.'</td>';
					$att2 .= '<td style="text-align:center;">'.$totalTime.'</td>';
				}
				else{
					$att2 .= '<td style="background-color:pink">'.date('d',strtotime($new_date)).'</td>';
					if($checkIn != "Missing" || $checkOut != "Missing"){
						$att2 .= '<td style="text-align:center;background-color:pink">'.$checkIn.'</td>';
						$att2 .= '<td style="text-align:center;background-color:pink">'.$checkOut.'</td>';
						$att2 .= '<td style="text-align:center;background-color:pink">'.$totalTime.'</td>';

					}else{
						if($gazated_holiday_name == ''){
							$att2 .= '<td colspan="3" style="text-align:center;background-color:pink">WeekEnd Holiday</td>';
						}else{
							$att2 .= '<td colspan="3" style="text-align:center;background-color:pink">'.$gazated_holiday_name.'</td>';
						}
					}

				}
				$att2 .= '</tr>';


				$new_date = date('Y-m-d', strtotime($new_date. ' + 1 days'));
			}

			for($i=21; $i<=$daysDiff; $i++){

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
					$foundCheckOut = 0;
					if($gazated_holiday_name== "" && $DayOfWeekNumber != 0){

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
							$checkInMissingCount++;
						}
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
						$checkOutMissingCount++;
						$remarks = 'Attendance Missing';
						$remarksColor = "#ff6c6c";
						$checkOutColor = "#ff6c6c";
					}
				}

				$totalTime = "0";
				if($checkOutInd != "" && $checkInInd != ""){
					$expiry_time = new DateTime($dateTimeChkOut);
					$current_date = new DateTime($dateTimeChkIn);
					$diff = $expiry_time->diff($current_date);
					$totalTime = $diff->format('%Hhr %Imin'); 
                     // $totalTime = $diff->format('%H:%I:%S');

					$tHour += $diff->format('%H'); 
					$tMin += $diff->format('%I'); 
				}

				$att3 .= '<tr style="font-size:8px;text-align:center">';
				if($DayOfWeekNumber != 0 && $gazated_holiday_name == ''){


					$att3 .= '<td>'.date('d',strtotime($new_date)).'</td>';
					$att3 .= '<td style="background-color:'.$checkInColor.'">'.$checkIn.'</td>';
					$att3 .= '<td style="background-color:'.$checkOutColor.'">'.$checkOut.'</td>';
					$att3 .= '<td style="text-align:center;">'.$totalTime.'</td>';
				}
				else{
					$att3 .= '<td style="background-color:pink">'.date('d',strtotime($new_date)).'</td>';
					if($checkIn != "Missing" || $checkOut != "Missing"){
						$att3 .= '<td style="text-align:center;background-color:pink">'.$checkIn.'</td>';
						$att3 .= '<td style="text-align:center;background-color:pink">'.$checkOut.'</td>';
						$att3 .= '<td style="text-align:center;background-color:pink">'.$totalTime.'</td>';

					}else{
						if($gazated_holiday_name == ''){
							$att3 .= '<td colspan="3" style="text-align:center;background-color:pink">WeekEnd Holiday</td>';
						}else{
							$att3 .= '<td colspan="3" style="text-align:center;background-color:pink">'.$gazated_holiday_name.'</td>';
						}
					}

				}
				$att3 .= '</tr>';


				$new_date = date('Y-m-d', strtotime($new_date. ' + 1 days'));
			}

			$att1 .= '</table>';
			$att2 .= '</table>';
			$att3 .= '</table>';
		}



		if($sal_type_id != 2){
			$att = '<table border="0" cellpadding="1" width="100%" style="font-size:9px">';
			$att .= '<tr>';
			$att .= '<td colspan="9" style="text-align:center;font-size:11px;background-color:lightgray;border:1px solid black"><b>Attendance ('.date('M-Y',strtotime($from_dt)).')</b></td>';
			$att .= '</tr>';
			$att .= '<tr>';
			$att .= '<td colspan="3">'.$att1.'</td>';
			$att .= '<td colspan="3">'.$att2.'</td>';
			$att .= '<td colspan="3">'.$att3.'</td>';
			$att .= '</tr>';

			$att .= '</table>';
		}else{
			$att = '<table border="0" cellpadding="1" width="70%" style="font-size:9px">';
			$att .= '<tr>';
			$att .= '<td colspan="3" style="text-align:center;font-size:11px;background-color:lightgray;border:1px solid black"><b>Attendance ('.date('M-Y',strtotime($from_dt)).')</b></td>';
			$att .= '</tr>';
			$att .= '<tr>';
			$att .= '<td colspan="3">'.$att1.'</td>';
			$att .= '</tr>';
			$att .= '</table>';
		}






		// end attendance

		$notes = '<table style="font-size:9px">';
		if($sal_type_id == 1){

			$notes .= '<tr>';
			$notes .= '<td style="color:blue;"><b>Notes:</b></td>';
			$notes .= '<td></td>';
			$notes .= '<td style="text-align:center;color:blue;"></td>';
			$notes .= '</tr>';
			$notes .= '<tr>';
			$notes .= '<td colspan="3"><b>Absent Days:</b> 1 day salary will be deducted.</td>';
			$notes .= '</tr>';
			$notes .= '<tr>';
			$notes .= '<td colspan="3"><b>Late Count:</b> If you arrived After 15 minutes of your reporting time. that will be count as 1 late.</td>';
			$notes .= '</tr>';
			$notes .= '<tr>';
			$notes .= '<td colspan="3"><b>Late/Early:</b> Half day salary will be deducted after '.$LESS.' Late Arrival / Early Departure.</td>';
			$notes .= '</tr>';

			if($emp_designation_id == 3){
				$notes .= '<tr>';
				$notes .= '<td colspan="3"><b>Overtime Hours:</b> '.$OTHOURSAL.' hour salary will be given for Overtime on Workign day which will be calculated after [0'.$ODS.':00 PM].</td>';
				$notes .= '</tr>';
				$notes .= '<tr>';
				$notes .= '<td colspan="3"><b>Non Working Days:</b> '.$NWDS.' Hours Salary will be given for any Non-Working Day Hours.</td>';
				$notes .= '</tr>';
				$notes .= '<tr>';
				$notes .= '<td colspan="3"><b>Night Shift:</b> '.$NSPHS.' Hours Salary will be given for any Night Shift Hours (12 - 6). It also need to be approved by admin.</td>';
				$notes .= '</tr>';
			}
		}


		$notes .= '</table>';

		if($sal_type_id != 2){

			$description_table_1 .= '<tr>';
			$description_table_1 .= '<td>';
			$description_table_1 .= $att;
			$description_table_1 .= '</td>';

			$description_table_1 .= '<td style="text-align:center">|<br>|<br>|<br>|<br>|<br>|<br>|<br>|<br>|<br>|<br>|<br>|</td>';

			$description_table_1 .= '<td>';
			$description_table_1 .= $att;
			$description_table_1 .= '</td>';
			$description_table_1 .= '</tr>';
		}else{

			$description_table_1 .= '<tr>';
			$description_table_1 .= '<td>';
			$description_table_1 .= $att;
			$description_table_1 .= '</td>';

			$description_table_1 .= '<td style="text-align:center">|<br>|<br>|<br>|<br>|<br>|<br>|<br>|<br>|<br>|<br>|<br>|<br>|<br>|<br>|<br>|<br>|</td>';

			$description_table_1 .= '<td>';
			$description_table_1 .= $att;
			$description_table_1 .= '</td>';
			$description_table_1 .= '</tr>';
		}






		$description_table_1 .= '<tr>';
		$description_table_1 .= '<td>';
		$description_table_1 .= $notes;
		$description_table_1 .= '</td>';
		if($sal_type_id != 2){
			$description_table_1 .= '<td style="text-align:center"><br>|<br>|<br>|<br>|<br>|<br>|<br>|<br>|</td>';
		}else{

			$description_table_1 .= '<td style="text-align:center"><br>|<br>|<br>|<br>|<br>|<br>|<br>|</td>';
		}


		$description_table_1 .= '<td>';
		$description_table_1 .= $notes;
		$description_table_1 .= '</td>';
		$description_table_1 .= '</tr>';



		if($sal_type_id == 1 && $emp_designation_id == 3){
			$sign = '<br><table>';
		}else{
			$sign = '<br><br><br><br><br><table>';
		}

		$sign .= '<tr>';
		$sign .= '<td style="text-align:center;color:blue;"><b></b></td>';
		$sign .= '<td></td>';
		$sign .= '<td style="text-align:center;color:blue;"></td>';
		$sign .= '</tr>';
		$sign .= '<tr>';
		$sign .= '<td style="border-top:1px solid black;text-align:center;"><b>Employee Sign</b></td>';
		$sign .= '<td></td>';
		$sign .= '<td style="border-top:1px solid black;text-align:center;"><b>Checked By</b></td>';
		$sign .= '</tr>';
		$sign .= '</table>';

		$description_table_1 .= '<tr>';
		$description_table_1 .= '<td>';
		$description_table_1 .= $sign;
		$description_table_1 .= '</td>';

		$description_table_1 .= '<td style="text-align:center">|<br>|</td>';

		$description_table_1 .= '<td>';
		$description_table_1 .= $sign;
		$description_table_1 .= '</td>';

		$description_table_1 .= '</tr>';

		$description_table_1 .= '</table>';


		$pdf->writeHTML($labels, true, 1, true, 1, '');


		$pdf->writeHTML($description_table_1, true, 1, true, 1, '');


		$file_name = 'Salary Slip.pdf';
		$pdf->Output($file_name, 'I');

	}
	else{
		include '../invalidLink.php';
	}

}
else{
	include '../invalidLink.php';
}