<?php include('db_connect.php');



$month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');

$fromDt = isset($_GET['fromDt']) ? $_GET['fromDt'] : date('Y-m-01');
$toDt = isset($_GET['toDt']) ? $_GET['toDt'] : date('Y-m-t');
$employee_id = isset($_GET['employee_id']) ? $_GET['employee_id'] : "0";

?>
<style>
	.night-overtime-header{display:flex!important;align-items:center;justify-content:space-between;gap:16px}
	.night-overtime-actions{display:flex;align-items:center;justify-content:flex-end;gap:7px;flex-wrap:wrap}
	.night-overtime-actions .btn{display:inline-flex;align-items:center;gap:6px;white-space:nowrap}
	#loading_sync{display:none;align-items:center;gap:7px;min-height:35px;padding:7px 11px;border:1px solid #ffd9c3;border-radius:8px;color:#d95c18;background:#fff5ee;font-size:10px;font-weight:600}
	.night-overtime-filters{margin:16px 18px 18px!important;padding:15px 14px 14px;border:1px solid #e6e7ea;border-radius:11px;background:#f8f8f9}
	.night-overtime-filters label{display:block;margin:0 0 7px;color:#56575d;font-size:10px;font-weight:700}
	.night-overtime-filters label i{width:15px;margin-right:4px;color:#f36b21;text-align:center}
	.night-overtime-filter-action{display:flex;align-items:flex-end}
	.night-overtime-filter-action .btn{width:100%;height:40px}
	.night-overtime-divider{display:none}
	.night-overtime-table-row{margin:0 18px 18px!important}
	.night-overtime-table-wrap{overflow:auto;border:1px solid #e3e4e7;border-radius:11px;background:#fff}
	#report-list{margin:0!important;border:0!important;border-collapse:separate!important;border-spacing:0}
	#report-list thead th{padding:11px 9px!important;border-top:0!important;border-right:0!important;border-bottom:1px solid #dedfe2!important;border-left:0!important;white-space:nowrap;color:#62636a!important;background:#f4f5f6!important}
	#report-list tbody td,#report-list tbody th{padding:9px!important;border-right:0!important;border-bottom:1px solid #ececef!important;border-left:0!important}
	#report-list tbody tr:last-child td,#report-list tbody tr:last-child th{border-bottom:0!important}
	@media(max-width:767px){.night-overtime-header{align-items:flex-start;flex-direction:column}.night-overtime-actions{width:100%;justify-content:flex-start}.night-overtime-filters{margin:12px!important}.night-overtime-filters>[class*="col-"]{margin-bottom:11px}.night-overtime-table-row{margin:0 12px 14px!important}}
</style>
<div class="container-fluid professional-payroll-page">
	<div class="col-lg-12">
		<div class="card payroll-card">
			<div class="payroll-header night-overtime-header">
				<div class="payroll-title"><span class="payroll-title-icon"><i class="fas fa-business-time"></i></span><div><h2>Night Shift Overtime</h2><p>Review shift attendance and calculated overtime.</p></div></div>
				<div class="night-overtime-actions">
					<span id="loading_sync"><i class="fas fa-circle-notch fa-spin"></i> Syncing attendance...</span>
					<button type="button" class="btn btn-outline-secondary btn-sm" id="sync_attendance"><i class="fas fa-sync-alt"></i> Sync</button>
					<button type="button" class="btn btn-outline-success btn-sm exportToExcel"><i class="fas fa-file-excel"></i> Export</button>
					<button type="button" class="btn btn-primary btn-sm" id="print"><i class="fas fa-print"></i> Print</button>
				</div>
			</div>



			<div class="card_body">


				<?php
				if(isset($_POST['change_status'])){
					mysqli_query($conn,"START TRANSACTION");

					$checkInTime = mysqli_real_escape_string($conn,$_POST['checkInTime']);
					$att_id_check_in = mysqli_real_escape_string($conn,$_POST['att_id_check_in']);

					$emp_id_TB = mysqli_real_escape_string($conn,$_POST['emp_id_TB']);

					$checkOutTime = mysqli_real_escape_string($conn,$_POST['checkOutTime']);
					$reasonEdit = mysqli_real_escape_string($conn,$_POST['reasonEdit']);
					$att_id_check_out = mysqli_real_escape_string($conn,$_POST['att_id_check_out']);
					$att_date = date('Y-m-d',strtotime(mysqli_real_escape_string($conn,$_POST['att_date'])));

					$result1 = 1;
					$result3 = 1;
					$result2 = 1;
					$result4 = 1;
					$query21 = "SELECT * from attendance WHERE emp_id = ".$emp_id_TB." AND dated = '".$att_date."' AND status = 4 AND del_status = 0 ";
					$result21 = mysqli_query($conn,$query21);
					if(mysqli_num_rows($result21)>0){
						$data21 = mysqli_fetch_array($result21);
						$CItime_pre = date('H:i',strtotime($data21['time']));
						$CIdated_pre = $data21['dated'];

						if($CItime_pre != $checkInTime){
							$upQuery1 = " UPDATE attendance SET ";
							$upQuery1 .= " del_status = '1' ";
							$upQuery1 .= ", del_reason = 'Employee Edited'";
							$upQuery1 .= ", remarks = '".$reasonEdit."'";


							$upQuery1 .= " WHERE emp_id = ".$emp_id_TB;
							$upQuery1 .= " AND dated ='".$att_date."' ";
							$upQuery1 .= " AND status ='4' ";
							$upQuery1 .= " AND del_status ='0' ";
							$result1 = mysqli_query($conn, $upQuery1);

							$dateTime = $att_date.' '.$checkInTime;
							$studentQuery1 = "INSERT INTO attendance SET ";
							$studentQuery1 .= " emp_id = ".$emp_id_TB;
							$studentQuery1 .= ", dated ='".$att_date."' ";
							$studentQuery1 .= ", time ='".$checkInTime."' ";
							$studentQuery1 .= ", dateTime ='".$dateTime."' ";
							$studentQuery1 .= ", status ='4' ";
							$studentQuery1 .= ", del_reason = '".$reasonEdit."' ";
							$studentQuery1 .= ", remarks ='edited entry' ";

							$result3 = mysqli_query($conn, $studentQuery1);
						}

					}else{
						$dateTime = $att_date.' '.$checkInTime;
						$studentQuery1 = "INSERT INTO attendance SET ";
						$studentQuery1 .= " emp_id = ".$emp_id_TB;
						$studentQuery1 .= ", dated ='".$att_date."' ";
						$studentQuery1 .= ", time ='".$checkInTime."' ";
						$studentQuery1 .= ", dateTime ='".$dateTime."' ";
						$studentQuery1 .= ", status ='4' ";
						$studentQuery1 .= ", remarks ='edited entry' ";
						$studentQuery1 .= ", del_reason = '".$reasonEdit."' ";
						$result3 = mysqli_query($conn, $studentQuery1);
					}


					$query31 = "SELECT * from attendance WHERE emp_id = ".$emp_id_TB." AND dated = '".$att_date."' AND status = 5 AND del_status = 0 ";
					$result31 = mysqli_query($conn,$query31);
					if(mysqli_num_rows($result31)>0){
						$data31 = mysqli_fetch_array($result31);
						$COtime_pre = $data31['time'];
						$COdated_pre = $data31['dated'];

						if($COtime_pre != $checkOutTime){

							$upQuery2 = " UPDATE attendance SET ";
							$upQuery2 .= " del_status = '1' ";
							$upQuery2 .= ", del_reason = 'Employee Edited: ".$reasonEdit."' ";

							$upQuery2 .= " WHERE emp_id = ".$emp_id_TB;
							$upQuery2 .= " AND dated ='".$att_date."' ";
							$upQuery2 .= " AND status ='5' ";
							$upQuery2 .= " AND del_status ='0' ";
							$result2 = mysqli_query($conn, $upQuery2);

							$dateTime = $att_date.' '.$checkOutTime;
							$studentQuery2 = "INSERT INTO attendance SET ";
							$studentQuery2 .= " emp_id = ".$emp_id_TB;
							$studentQuery2 .= ", dated ='".$att_date."' ";
							$studentQuery2 .= ", time ='".$checkOutTime."' ";
							$studentQuery2 .= ", dateTime ='".$dateTime."' ";
							$studentQuery2 .= ", status ='5' ";
							$studentQuery2 .= ", del_reason = '".$reasonEdit."' ";
							$studentQuery2 .= ", remarks ='edited entry' ";
							$result4 = mysqli_query($conn, $studentQuery2);
						}
					}else{
						$dateTime = $att_date.' '.$checkOutTime;
						$studentQuery2 = "INSERT INTO attendance SET ";
						$studentQuery2 .= " emp_id = ".$emp_id_TB;
						$studentQuery2 .= ", dated ='".$att_date."' ";
						$studentQuery2 .= ", time ='".$checkOutTime."' ";
						$studentQuery2 .= ", dateTime ='".$dateTime."' ";
						$studentQuery2 .= ", status ='5' ";
						$studentQuery2 .= ", remarks ='edited entry' ";
						$studentQuery2 .= ", del_reason = '".$reasonEdit."' ";
						$result4 = mysqli_query($conn, $studentQuery2);
					}

					if($result1 && $result2 && $result3 && $result4 ){
						mysqli_query($conn,"COMMIT");
						?>
						<script>
							alert("Attendance Updated Successfully");
							window.open('index.php?page=Employees/night-shift-overtime&fromDt=<?php echo $att_date ?>&toDt=<?php echo $att_date ?>&employee_id=<?php echo $emp_id_TB ?>','_self');
						</script>
						<?php
					}else{

						mysqli_query($conn,"ROLLBACK");     
						?>
						<script>
							alert_toast("Error",'danger');
						</script>
						<?php
					}
				}

				?>


				<div class="row night-overtime-filters">
					<div class="col-md-3">
						<label class="control-label"><i class="fas fa-user"></i>Employee Name</label>
						<select  name="employee_id" required="true" id="employee_id" class="form-control">
							<option value="0">Select Employee</option>
							<?php

							$selected = "";
							if($employee_id == "all"){
								$selected = "SELECTED";
							}
							?>
							<option <?= $selected ?> value="all">All</option>
							<?php
							$query_emp = "SELECT * FROM employee WHERE emp_designation_id = 3 AND sal_type_id=1 AND emp_status = 0";
							$result_emp = mysqli_query($conn,$query_emp);
							while($data_emp = mysqli_fetch_array($result_emp)){
								$selecte_val = "";
								if($employee_id == $data_emp['emp_id']){
									$selecte_val = "Selected";
								}
								?>
								<option <?php echo $selecte_val ?> value="<?php echo $data_emp['emp_id'] ?>"><?php echo $data_emp["emp_name"] ?></option>
								<?php
							}
							?>
						</select>
					</div>

					<div class="col-md-3">
						<label class="control-label"><i class="fas fa-calendar-alt"></i>From Date</label>
						<input type="date" name="fromDt" required="true" id="fromDt" value="<?php echo $fromDt ?>" class="form-control">
					</div>

					<div class="col-md-3">
						<label class="control-label"><i class="fas fa-calendar-check"></i>To Date</label>            
						<input type="date" name="toDt" required="true" id="toDt" value="<?php echo $toDt ?>" class="form-control">
					</div>
					<div class="col-md-3 night-overtime-filter-action">
						<button type="button" class="btn btn-primary btn-sm" id="filterBtn"><i class="fas fa-filter"></i> Get Attendance</button>
					</div>
				</div>

				<hr class="night-overtime-divider">

				<div class="row night-overtime-table-row">

					<?php
					if($employee_id != 0 || $employee_id =='all'){
						?>
						<div class="col-md-12 p-0 night-overtime-table-wrap">
							<table class="table table-bordered" id='report-list'  data-cols-width="10,20,20,20,20,20">
								<thead>
									<tr style="background-color: #e5e5e5">
										<th data-f-bold="true" data-a-h="center" data-b-a-s="true" class="text-center" style="width:10px;border:1px solid gray">SR#</th>
										<th data-f-bold="true" data-a-h="center" data-b-a-s="true" class="text-center" style="border:1px solid gray">Date</th>
										<th data-f-bold="true" data-a-h="center" data-b-a-s="true" class="text-center" style="border:1px solid gray">Day</th>
										<th data-f-bold="true" data-a-h="center" data-b-a-s="true" class="text-center" style="border:1px solid gray">Overtime In</th>
										<th data-f-bold="true" data-a-h="center" data-b-a-s="true" class="text-center" style="border:1px solid gray">Overime Out</th>
										<th data-f-bold="true" data-a-h="center" data-b-a-s="true" class="text-center" style="border:1px solid gray">Total Hours</th>
										<th data-f-bold="true" data-a-h="center" data-b-a-s="true" class="text-center" style="border:1px solid gray">Permission</th>
										<th data-exclude="true" class="text-center" style="border:1px solid gray">Action</th>
									</tr>
								</thead>
								<tbody>

									<?php

									function searchForDt($id, $array,$st) {
										for($j =0; $j<count($array); $j++){
											if($array[$j][2] ==$id && $array[$j][5]==$st){
												return $j;
											}
										}
										return 111111;
									}

									$diff = abs(strtotime($toDt) - strtotime($fromDt));
									$daysDiff = floor($diff / (60*60*24)) +1;

									if($employee_id == "all"){
										$queryCheck = "SELECT * FROM employee WHERE emp_status = 0";

									}else{
										$queryCheck = "SELECT * FROM employee WHERE emp_id = ".$employee_id;
									}

									$resultCheck = mysqli_query($conn,$queryCheck);
									while($dataEmp = mysqli_fetch_array($resultCheck)){
										$emp_id = $dataEmp['emp_id'];
										$emp_name = $dataEmp['emp_name'];

										$tHour = 0;
										$tMin = 0;
										$emp_total_time = 0;
										$total_holidays = 0;
										$total_working_days = 0;
										?>
										<tr>
											<td data-f-bold="true" style="border:1px solid gray" colspan="7"><b>EMP-<?= $emp_id ?>: <?= $emp_name ?></b></td>
										</tr>
										<?php
										$usr_mod_permisions=array();


										$queryAtt = "SELECT * FROM attendance WHERE emp_id = ".$emp_id." AND dated >= '".$fromDt."' AND dated <= '".$toDt."' AND del_status = 0 AND status IN (4,5) ";
										$resultAtt = mysqli_query($conn,$queryAtt);
										while($dataAtt = mysqli_fetch_array($resultAtt)){
											array_push($usr_mod_permisions,$dataAtt);
										}

										$new_date = $fromDt;

										$checkInMissingCount = 0;
										$checkOutMissingCount = 0;
										$absentCount = 0;


										$gazated_holidays =array();
										$queryHolidays = "SELECT * FROM holidays WHERE holiday_date >= '".$fromDt."' AND  holiday_date <= '".$toDt."' AND effective = 0 ";
										$resultHolidays = mysqli_query($conn,$queryHolidays);
										while($dataHolidays = mysqli_fetch_array($resultHolidays)){
											array_push($gazated_holidays,$dataHolidays);
										}

										for($i=1; $i<=$daysDiff; $i++){

											$currentDt = Date('Y-m-d');

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

											$DayOfWeekNumber = date("w",strtotime($new_date));

											if($new_date <= $currentDt){
												$st = 4;
												$checkInInd = searchForDt($new_date, $usr_mod_permisions,$st);

												$checkIn = "Missing";
												$dateTimeChkIn = "";
												$checkInColor = "#ff5151";
												if($checkInInd != 111111){
													$dateTimeChkIn = $usr_mod_permisions[$checkInInd][4];
													$checkIn = date('h:i A',strtotime($dateTimeChkIn));
													$checkInColor = "#7be77b";
												}else{
													if($gazated_holiday_name== "" && $DayOfWeekNumber != 0){
														$checkInMissingCount++;
													}
												}

												$st = "5";
												$dateTimeChkOut ="";
												$checkOutInd = searchForDt($new_date, $usr_mod_permisions,$st);
												$checkOut = "Missing";
												$checkOutColor = "#ff5151";
												if($checkOutInd != 111111 ){
													$dateTimeChkOut = $usr_mod_permisions[$checkOutInd][4];
													$checkOut = date('h:i A',strtotime($dateTimeChkOut));
													$checkOutColor = "#7be77b";
												}else{
													if($gazated_holiday_name== "" && $DayOfWeekNumber != 0){
														$checkOutMissingCount++;
													}
												}

												$totalTime = "0";
												if($checkOutInd != 111111 && $checkInInd != 111111){
													$expiry_time = new DateTime($dateTimeChkOut);
													$current_date = new DateTime($dateTimeChkIn);
													$diff = $expiry_time->diff($current_date);
													$totalTime = $diff->format('%Hhr %Imin');

													$tHour += $diff->format('%H'); 
													$tMin += $diff->format('%I'); 
												}




												if($checkInInd == 111111 && $checkOutInd == 111111 && $gazated_holiday_name== "" && $DayOfWeekNumber != 0){
													$absentCount++;
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

												$allow_night_sym = "Not Allowed";
												$night_all_color = "red";
												$allow_night = false;
												$query_check = "SELECT * FROM night_shift_perm WHERE employee_id = ".$emp_id." AND dated = '".$new_date."' AND del_status = 0";
												$result_check = mysqli_query($conn,$query_check);
												if(mysqli_num_rows($result_check)>0){
													$allow_night = true;
													$night_all_color = "green";
													$allow_night_sym = "Allowed";
												}


												if($checkIn!= "Missing" || $checkOut != "Missing" || $allow_night){
													?>
													<tr>
														<td data-a-h="center" data-b-a-s="true" data-t="n" style="border:1px solid gray" class="text-center"><?= $i ?></td>
														<td data-a-h="center" data-b-a-s="true" style="border:1px solid gray" class="text-center"><?= date('d-M-Y',strtotime($new_date)) ?></td>
														<td data-a-h="center" data-b-a-s="true" style="border:1px solid gray" class="text-center"><?= $dayName ?></td>
														<?php

														if($DayOfWeekNumber == 0){
															if($checkIn!= "Missing" && $checkOut != "Missing"){
																?>
																<td data-a-h="center" data-b-a-s="true" data-fill-color="<?= $checkInColor ?>" class="text-center" style="color:black;background-color:lightpink;border:1px solid gray"><?= $checkIn ?></td>
																<td data-a-h="center" data-b-a-s="true" data-fill-color="<?= $checkInColor ?>" class="text-center" style="color:black;background-color:lightpink;border:1px solid gray"><?= $checkOut ?></td>
																<td data-a-h="center" data-b-a-s="true" style="border:1px solid gray"  class="text-center"><?= $totalTime ?></td>
																<?php

																$edtCheckOut = "";
																$edtCheckIn = "";
																if($dateTimeChkOut != ""){
																	$edtCheckOut =date('H:i',strtotime($dateTimeChkOut));
																}
																if($dateTimeChkIn != ""){
																	$edtCheckIn =date('H:i',strtotime($dateTimeChkIn));
																}
																?>
																<td data-a-h="center" data-b-a-s="true" style="color:<?= $night_all_color ?>;text-align:center;border:1px solid gray"><b><?= $allow_night_sym ?></b></td>



																<td data-exclude="true" style="text-align: center;padding: 0px;padding-top: 4px;border:1px solid gray">
																	<button class="btn btn-warning btn-sm" data-value="1^2^<?php echo $edtCheckIn ?>^<?php echo $edtCheckOut ?>^<?php echo date('d-M-Y',strtotime($new_date))?>^<?php echo $emp_id?>^<?php echo $emp_name?>" data-toggle="modal" id="edt_btn" data-target="#att_edt_model" style="margin: 1px;padding: 4px !important;"><i class="fa fa-edit"></i></button>
																</td>

																<?php

															}else{
																?>
																<td data-a-h="center" data-b-a-s="true" data-fill-color="" colspan="5" class="text-center" style="background-color:lightpink;border:1px solid gray"><b>Weekend</b></td>
																<?php
															}



														}
														else if($gazated_holiday_name != ""){
															if($checkIn!= "Missing" && $checkOut != "Missing"){
																?>
																<td data-a-h="center" data-b-a-s="true" data-fill-color="<?= $checkInColor ?>" class="text-center" style="color:black;background-color:lightpink;border:1px solid gray"><?= $checkIn ?></td>
																<td data-a-h="center" data-b-a-s="true" data-fill-color="<?= $checkInColor ?>" class="text-center" style="color:black;background-color:lightpink;border:1px solid gray"><?= $checkOut ?></td>
																<td data-a-h="center" data-b-a-s="true" style="border:1px solid gray"  class="text-center"><?= $totalTime ?></td>
																<?php

																$edtCheckOut = "";
																$edtCheckIn = "";
																if($dateTimeChkOut != ""){
																	$edtCheckOut =date('H:i',strtotime($dateTimeChkOut));
																}
																if($dateTimeChkIn != ""){
																	$edtCheckIn =date('H:i',strtotime($dateTimeChkIn));
																}
																?>
																<td data-a-h="center" data-b-a-s="true" style="color:<?= $night_all_color ?>;text-align:center;border:1px solid gray"><b><?= $allow_night_sym ?></b></td>
																<td data-exclude="true" style="text-align: center;padding: 0px;padding-top: 4px;border:1px solid gray">
																	<button class="btn btn-warning btn-sm" data-value="1^2^<?php echo $edtCheckIn ?>^<?php echo $edtCheckOut ?>^<?php echo date('d-M-Y',strtotime($new_date))?>^<?php echo $emp_id?>^<?php echo $emp_name?>" data-toggle="modal" id="edt_btn" data-target="#att_edt_model" style="margin: 1px;padding: 4px !important;"><i class="fa fa-edit"></i></button>
																</td>
																<?php
															}


															else{
																?>
																<td data-a-h="center" data-b-a-s="true" data-fill-color="" colspan="5" class="text-center" style="background-color:lightpink;border:1px solid gray"><b><?= $gazated_holiday_name?></b></td>
																<?php
															}

														}
														else{
															?>
															<td data-a-h="center" data-b-a-s="true" data-fill-color="<?= $checkInColor ?>" class="text-center" style="color:black;background-color:<?= $checkInColor ?>;border:1px solid gray"><?= $checkIn ?></td>
															<td data-a-h="center" data-b-a-s="true" data-fill-color="<?= $checkInColor ?>" class="text-center" style="color:black;background-color:<?= $checkOutColor ?>;border:1px solid gray"><?= $checkOut ?></td>
															<td data-a-h="center" data-b-a-s="true" style="border:1px solid gray"  class="text-center"><?= $totalTime ?></td>
															<?php

															$edtCheckOut = "";
															$edtCheckIn = "";
															if($dateTimeChkOut != ""){
																$edtCheckOut =date('H:i',strtotime($dateTimeChkOut));
															}
															if($dateTimeChkIn != ""){
																$edtCheckIn =date('H:i',strtotime($dateTimeChkIn));
															}
															?>
															<td data-a-h="center" data-b-a-s="true" style="color:<?= $night_all_color ?>;text-align:center;border:1px solid gray"><b><?= $allow_night_sym ?></b></td>
															<td data-exclude="true" style="text-align: center;padding: 0px;padding-top: 4px;border:1px solid gray">
																<button class="btn btn-warning btn-sm" data-value="1^2^<?php echo $edtCheckIn ?>^<?php echo $edtCheckOut ?>^<?php echo date('d-M-Y',strtotime($new_date))?>^<?php echo $emp_id?>^<?php echo $emp_name?>" data-toggle="modal" id="edt_btn" data-target="#att_edt_model" style="margin: 1px;padding: 4px !important;"><i class="fa fa-edit"></i></button>
															</td>
															<?php
														}
														?>

													</tr>
													<?php
												}
												

											}
											$new_date = date('Y-m-d', strtotime($new_date. ' + 1 days')); 
										}


										$hours_new = intdiv($tMin, 60);
										$total_minutes = ($tMin % 60);

										$total_hours = $tHour+$hours_new;
										$emp_total_time = $total_hours.'hr '.$total_minutes.'min';

										?>
										<tr>
											<th data-f-bold="true" data-a-h="center" data-b-a-s="true" style="border:1px solid gray;font-size: 14px" colspan="5" class="text-right">Total Time</th>
											<th data-f-bold="true" data-a-h="center" data-b-a-s="true" style="border:1px solid gray;font-size: 14px" class="text-center"><?= $emp_total_time ?></th>
											<th style="border:1px solid gray"></th>
											<th style="border:1px solid gray"></th>
										</tr>
										<?php
									}
									?>



								</tbody>
							</table>
						</div>
						<?php
					}
					?>




					

				</div>





			</div>
		</div>
	</div>
</div>

<style>
	.modal-header .close {
		padding: 1rem 2rem !important;
	}
	.bg-warning {
		background-color: #265d50 !important;
	}
</style>

<div class="modal fade" id="att_edt_model" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<form method="post">
				<div class="modal-header bg-warning" style="padding:5px">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-window-close" aria-hidden="true" style="margin: -9px;color:white"></i>
					</button>
					<h4 class="modal-title"><strong style="color: white;padding-left: 11px;">Attendance Edit</strong></h4>
				</div>
				<div class="modal-body">
					<div class="row">
						<div class="col-md-12">
							<label><b>Employee: </b><span id="edt_emp_span"></span></label>
						</div>
						<div class="col-md-12">
							<label><b>Attendance Date: </b><span id="edt_att_dt"></span></label>
						</div>
						<br>
						<br>
						<div class="col-md-6">
							<span><b>Check In:</b></span>
							<input type="time" name="checkInTime" id="checkInTime">
						</div>
						<div class="col-md-6">
							<span><b>Check Out:</b></span>
							<input type="time" name="checkOutTime" id="checkOutTime">
						</div>

						<div class="col-md-12 mt-2">
							<span><b>Reason for edit:</b></span>
							<textarea name="reasonEdit" id="reasonEdit" class="form-control" style="resize:none"></textarea>
						</div>
					</div>


					<input type="hidden" name="att_date" id="att_date">
					<input type="hidden" name="att_id_check_in" id="att_id_check_in">
					<input type="hidden" name="emp_id_TB" id="emp_id_TB">
					<input type="hidden" name="att_id_check_out" id="att_id_check_out">

				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-primary btn-embossed" id="change_status" name="change_status">Update</button>
					<button type="button" class="btn btn-default btn-embossed" data-dismiss="modal">Close</button>
				</div>
			</form>
		</div>
	</div>
</div>





<noscript>
	<style>
		table#report-list{
			width:100%;
			border-collapse:collapse
		}
		table#report-list td,table#report-list th{
			border:1px solid
		}
		p{
			margin:unset;
		}
		.text-center{
			text-align:center
		}
		.text-right{
			text-align:right
		}
	</style>
</noscript>


<script>
	$(document).on("click","#edt_btn",function() {
		var data=$(this).attr('data-value'); 
		var ans=data.split('^');

		$("#att_id_check_in").val(ans[0]);
		$("#att_id_check_out").val(ans[1]);
		$("#checkInTime").val(ans[2]);
		$("#checkOutTime").val(ans[3]);
		$("#edt_att_dt").html('<span style="color:blue"><b>'+ans[4]+'</b></span>');
		$("#emp_id_TB").val(ans[5]);
		$("#att_date").val(ans[4]);
		$("#edt_emp_span").html('<span style="color:blue"><b>EMP-'+ans[5]+" "+ans[6]+'</b></span>');
	});

	$('#loading_sync').hide();
	$('#sync_attendance').show();

	$('#sync_attendance').click(function(event) {

		$('#loading_sync').show();
		$('#sync_attendance').hide();
		$.ajax({
			url:'ajax.php?action=sync_attendance',
			cache: false,
			contentType: false,
			processData: false,
			method: 'POST',
			type: 'POST',
			success:function(resp){
				if(resp==1){
					alert_toast("Attendance Successfully Synced",'success')
					setTimeout(function(){
						window.open('index.php?page=Employees/night-shift-overtime','_self');
					},1500)

				}else{
					alert(resp)
					alert_toast("Error Occured, Machine Connection Failed.",'danger');
					$('#loading_sync').hide();
					$('#sync_attendance').show();
				}
			}
		})
	});

	$('#filterBtn').click(function(){
		var fromDt = $('#fromDt').val();
		var toDt = $('#toDt').val();
		var employee_id = $('#employee_id').val();
		location.replace('index.php?page=Employees/night-shift-overtime&fromDt='+fromDt+'&toDt='+toDt+'&employee_id='+employee_id)
	})

	$('#print').click(function(){
		var _c = $('#report-list').clone();
		var ns = $('noscript').clone();
		ns.append(_c)
		var nw = window.open('','_blank','width=900,height=600')
		nw.document.write('<p class="text-center"><b>Employee Attendance as of <?php echo date("F, Y",strtotime($month)) ?></b></p>')
		nw.document.write(ns.html())
		nw.document.close()
		nw.print()
		setTimeout(() => {
			nw.close()
		}, 500);
	})
</script>


