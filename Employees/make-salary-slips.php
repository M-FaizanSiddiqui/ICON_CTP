<?php include('db_connect.php');

if(in_array(71,$_SESSION['login_Permisions']))
{
	?>
	<div class="container-fluid professional-form-page professional-payroll-page">
		
		<div class="col-lg-12">
			<div class="row">
				<!-- FORM Panel -->
				<div class="col-md-12">
					<form action="" id="manage-employee">
						<div class="card professional-form-card payroll-card">
							<div class="card-header">
								<span class="form-title-icon"><i class="fa fa-calculator"></i></span><div class="form-title-copy"><h2>Make Salary Slips</h2><p>Select a payroll type and process employee salaries.</p></div>
							</div>
							<div class="salary-table-loader" id="salary-table-loader" aria-live="polite" aria-hidden="true">
								<div class="salary-loader-content"><span class="salary-loader-spinner"></span><strong>Loading employees</strong><small>Please wait while the salary table is prepared.</small></div>
							</div>
							<div class="card-body">
								<div class="row">
									<div class="col-md-3">
										<div class="form-group">
											<label class="control-label"><b>Salary Type:</b></label>
											<select class="form-control" name="sal_type" id="sal_type">
												<option>Please Select</option>
												<?php 
												$query1 = "SELECT * FROM salary_type";
												$result1 = mysqli_query($conn,$query1);
												while($data1 = mysqli_fetch_array($result1)){
													$st_id = $data1['st_id'];
													$st_type_name = $data1['st_type_name'];
													?>
													<option value="<?= $st_id ?>"><?= $st_type_name ?></option>
													<?php
												}
												?>
											</select>
										</div>
									</div>
								</div>


								<div class="row monthDiv">
									<div class="col-md-3">
										<div class="form-group">
											<label class="control-label"><b>Salary Month:</b></label>
											<select class="form-control" name="salary_month" id="salary_month">
												<option>Please Select</option>
												<?php

											$curDate = strtotime(date('Y-m-01')); // First day of current month

                                            for($i = -3; $i <= 3; $i++){
                                                $thisDate = date("Y-m-01", strtotime($i . " month", $curDate));
                                                ?>
                                                <option value="<?= date('M-Y', strtotime($thisDate)) ?>">
                                                    <?= date('M-Y', strtotime($thisDate)) ?>
                                                </option>
                                                <?php
                                            }
												?>
											</select>
										</div>
									</div>

									<div class="col-md-12">
										<table class="table table-bordered" id="tableMonthlyEmp">
											<tr class="text-center" style="font-size: 10px">
												<th>Emp Code</th>
												<th>Emp Name</th>
												<th>Per Month</th>
												<th>Present Days</th>
												<th>Absent Days</th>
												<th>Late/Early</th>
												<th>OverTime Hours</th>
												<th>NWD Hours</th>
												<th>Night Shift Hours</th>
												<th>Exp Salary</th>
												<th>Incentive Amt</th>
												<th>Gross Amt</th>
											</tr>

										</table>
									</div>

								</div>



								<div class="row weekDiv">
									<div class="col-md-3">
										<div class="form-group">
											<label class="control-label"><b>Salary Week:</b></label>
											<select class="form-control" name="salary_week" id="salary_week">
												<option>Please Select</option>
												<?php

												// $curDate = date('2025-12-20');
												// for($i=1; $i<36; $i++){
												// 	$pre_date = $curDate;
												// 	$post_date =  date('Y-m-d', strtotime($pre_date. ' + 6 days'));
												// 	?>

												<!--// 	<option value="<?= date('d-M-Y',strtotime($pre_date)).' ** '. date('d-M-Y',strtotime($post_date)) ?>"><?= date('d-M-Y',strtotime($pre_date)).' - '.date('d-M-Y',strtotime($post_date)) ?></option>-->
												 	<?php
												// 	$curDate = date('Y-m-d', strtotime($post_date. ' + 1 days'));
												// }
												
												
												// Current date
                                                $currentDate = date('Y-m-d');
                                                
                                                // Find Saturday of current week
                                                $dayOfWeek = date('w', strtotime($currentDate)); // 0=Sun, 6=Sat
                                                $daysToSaturday = ($dayOfWeek == 6) ? 0 : $dayOfWeek + 1;
                                                $currentSaturday = date('Y-m-d', strtotime("-$daysToSaturday days", strtotime($currentDate)));
                                                
                                                // Define range (2 months back, 2 months forward)
                                                $startDate = date('Y-m-01', strtotime('-1 months'));
                                                $endDate   = date('Y-m-t', strtotime('+1 months'));
                                                
                                                // Move startDate to first Saturday before/within range
                                                $startSaturday = date('Y-m-d', strtotime('last saturday', strtotime($startDate . ' +1 day')));
                                                
                                                $loopDate = $startSaturday;
                                                
                                                while ($loopDate <= $endDate) {
                                                
                                                    $weekStart = $loopDate; // Saturday
                                                    $weekEnd   = date('Y-m-d', strtotime($weekStart . ' +6 days')); // Friday
                                                    ?>
                                                
                                                    <option value="<?= date('d-M-Y', strtotime($weekStart)) . ' ** ' . date('d-M-Y', strtotime($weekEnd)) ?>">
                                                        <?= date('d-M-Y', strtotime($weekStart)) . ' - ' . date('d-M-Y', strtotime($weekEnd)) ?>
                                                    </option>
                                                
                                                    <?php
                                                    // Move to next Saturday
                                                    $loopDate = date('Y-m-d', strtotime($weekStart . ' +7 days'));
                                                }
												
												
												
												
												?>
											</select>
										</div>
									</div>

									<div class="col-md-12">
										<table class="table table-bordered" id="tableWeekEmp">
											<tr class="text-center">
												<th>SR#</th>
												<th>Emp Code</th>
												<th>Emp Name</th>
												<th>Per Hour</th>
												<th>No of Hours</th>
												<th>Expecetd Salary</th>
												<th>Incentive Amt</th>
												<th>Gross Amt</th>
											</tr>

										</table>
									</div>

								</div>



								<div class="row impressionkDiv">
									<div class="col-md-3">
										<div class="form-group">
											<label class="control-label"><b>Salary Week:</b></label>
											<select class="form-control" name="salary_impression_month" id="salary_impression_month">
												<option>Please Select</option>
												<?php
												$curDate = strtotime(date('Y-m-01')); // First day of current month

                                            for($i = -3; $i <= 3; $i++){
                                                $thisDate = date("Y-m-01", strtotime($i . " month", $curDate));
                                                ?>
                                                <option value="<?= date('M-Y', strtotime($thisDate)) ?>">
                                                    <?= date('M-Y', strtotime($thisDate)) ?>
                                                </option>
                                                <?php
                                            }
												?>
											</select>
										</div>
									</div>

									<div class="col-md-12">
										<table class="table table-bordered" id="tableImpressionEmp">
											<tr class="text-center">
												<th>SR#</th>
												<th>Emp Code</th>
												<th>Emp Name</th>
												<th>Per Impression</th>
												<th>No of Impressions</th>
												<th>Expecetd Salary</th>
												<th>Incentive Amt</th>
												<th>Gross Amt</th>
											</tr>

										</table>
									</div>

								</div>



								<div class="row monthHourDiv">
									<div class="col-md-3">
										<div class="form-group">
											<label class="control-label"><b>Salary Month:</b></label>
											<select class="form-control" name="salary_hour_month" id="salary_hour_month">
												<option>Please Select</option>
												<?php

												$curDate = strtotime( date('2023-11-01'));
												for($i=0; $i<24; $i++){
													$thisDate = date("Y-m-01", strtotime("+".$i." month", $curDate));
													?>
													<option value="<?= date('M-Y',strtotime($thisDate)) ?>"><?= date('M-Y',strtotime($thisDate)) ?></option>
													<?php
												}
												?>
											</select>
										</div>
									</div>

									<div class="col-md-12">
										<table class="table table-bordered" id="tableMonthlyHourEmp">
											<tr class="text-center">
												<th>Emp Code</th>
												<th>Emp Name</th>
												<th>Per Month</th>
												<th>Present Days</th>
												<th>Absent Days</th>
												<th>Late/Early</th>
												<th>OverTime Hours</th>
												<th>Non Working Days</th>
												<th>Exp Salary</th>
												<th>Incentive Amt</th>
												<th>Gross Amt</th>
											</tr>

										</table>
									</div>

								</div>

							</div>

							<div class="card-footer">
								<div class="row">
									<div class="col-md-12">
										<button class="btn btn-default" type="button" onclick="$('#manage-employee').get(0).reset()"><i class="fa fa-undo"></i> Reset</button>
										<button class="btn btn-primary"><i class="fa fa-save"></i> Process Salary</button>
									</div>
								</div>
							</div>
						</div>
					</form>
				</div>
				<!-- FORM Panel -->
			</div>
		</div>	

	</div>
	<style>

		td{
			vertical-align: middle !important;
		}
		td p {
			margin:unset;
		}
	</style>
	<style>
		#manage-employee .payroll-card{position:relative}
		.salary-table-loader{position:absolute;inset:0;z-index:20;display:flex;align-items:center;justify-content:center;padding:20px;border-radius:15px;background:rgba(255,255,255,.82);backdrop-filter:blur(3px);opacity:0;visibility:hidden;pointer-events:none;transition:opacity .18s,visibility .18s}
		.salary-table-loader.is-visible{opacity:1;visibility:visible;pointer-events:all}
		.salary-loader-content{display:flex;align-items:center;flex-direction:column;min-width:220px;padding:22px 26px;border:1px solid #e8e9ec;border-radius:14px;text-align:center;background:#fff;box-shadow:0 16px 42px rgba(43,43,47,.14)}
		.salary-loader-content strong{margin-top:12px;font-size:13px;font-weight:600;color:#303033}.salary-loader-content small{margin-top:4px;font-size:10px;color:#8d8e94}
		.salary-loader-spinner{width:34px;height:34px;border:3px solid #ffe0cf;border-top-color:#f36b21;border-radius:50%;animation:salaryLoaderSpin .72s linear infinite}
		@keyframes salaryLoaderSpin{to{transform:rotate(360deg)}}
	</style>
	<script>
		function showSalaryTableLoader(){
			$('#salary-table-loader').addClass('is-visible').attr('aria-hidden','false');
			$('#manage-employee select').prop('disabled',true);
		}
		function hideSalaryTableLoader(){
			$('#salary-table-loader').removeClass('is-visible').attr('aria-hidden','true');
			$('#manage-employee select').prop('disabled',false);
		}
		$('#manage-employee').on('reset',function(){
			$('input:hidden').val('')
		})

		$('.weekDiv').hide();
		$('.impressionkDiv').hide();
		$('.monthDiv').hide();
		$('.monthHourDiv').hide();

		$('#sal_type').change(function(event) {
			var salTypee = $(this).val();

			$('.weekDiv').hide();
			$('.impressionkDiv').hide();
			$('.monthDiv').hide();
			$('.monthHourDiv').hide();

			if(salTypee == "1"){
				$('.monthDiv').show();
			}else if(salTypee == "2"){
				$('.weekDiv').show();
			}else if(salTypee == "3"){
				$('.impressionkDiv').show();
			}
			else if(salTypee == "4"){
				$('.monthHourDiv').show();
			}
		});



		$('#salary_week').change(function(event) {
			var salary_week = $(this).val();

			$.ajax({
				url : "ajax-req/ajax_request.php",
				method : "POST",
				data : {salary_week : salary_week,req_no: 6},
				dataType : "text",
				beforeSend : showSalaryTableLoader,
				complete : hideSalaryTableLoader,
				success : function(data){
					$('.my_tr').remove();
					$('#tableWeekEmp').append(data);
				}
			});
		});

		$('#salary_impression_month').change(function(event) {
			var salary_impression_month = $(this).val();

			$.ajax({
				url : "ajax-req/ajax_request.php",
				method : "POST",
				data : {salary_impression_month : salary_impression_month,req_no: 7},
				dataType : "text",
				beforeSend : showSalaryTableLoader,
				complete : hideSalaryTableLoader,
				success : function(data){
					$('.my_tr').remove();
					$('#tableImpressionEmp').append(data);
				}
			});
		});



		$('#salary_month').change(function(event) {
			var salary_month = $(this).val();

			$.ajax({
				url : "ajax-req/ajax_request.php",
				method : "POST",
				data : {salary_month : salary_month,req_no: 8},
				dataType : "text",
				beforeSend : showSalaryTableLoader,
				complete : hideSalaryTableLoader,
				success : function(data){
					$('.my_tr').remove();
					$('#tableMonthlyEmp').append(data);
				}
			});
		});



		$('#salary_hour_month').change(function(event) {
			var salary_hour_month = $(this).val();

			$.ajax({
				url : "ajax-req/ajax_request.php",
				method : "POST",
				data : {salary_hour_month : salary_hour_month,req_no: 10},
				dataType : "text",
				beforeSend : showSalaryTableLoader,
				complete : hideSalaryTableLoader,
				success : function(data){
					$('.my_tr').remove();
					$('#tableMonthlyHourEmp').append(data);
				}
			});
		});


		




		
		// expectedSalary
		$(document).on("keyup",".noOfImpressions",function() {
			var noOfImp = $(this).val();
			var amt = $(this).closest('tr').find('.impressSalary').val();
			var expSal = parseFloat(noOfImp) * parseFloat(amt);
			$(this).closest('tr').find('.ImpExpectedSalary').val(expSal);
			$(this).closest('tr').find('.ImpGrossAmt').val(expSal);
		});


		$(document).on("keyup",".incentiveAmt",function() {
			var expectedSalary = $(this).closest('tr').find('.expectedSalary').val();
			var incentiveAmt = $(this).closest('tr').find('.incentiveAmt').val();

			var newAmt = parseFloat(expectedSalary) + parseFloat(incentiveAmt);
			$(this).closest('tr').find('.grossAmt').val(newAmt);
		});


		$(document).on("keyup",".WeekIncentiveAmt",function() {
			var WeekExpectedSalary = $(this).closest('tr').find('.WeekExpectedSalary').val();
			var WeekIncentiveAmt = $(this).closest('tr').find('.WeekIncentiveAmt').val();

			var newAmt = parseFloat(WeekExpectedSalary) + parseFloat(WeekIncentiveAmt);
			$(this).closest('tr').find('.WeekGrossAmt').val(newAmt);
		});


		$(document).on("keyup",".ImpIncentiveAmt",function() {
			var ImpExpectedSalary = $(this).closest('tr').find('.ImpExpectedSalary').val();
			var ImpIncentiveAmt = $(this).closest('tr').find('.ImpIncentiveAmt').val();

			var newAmt = parseFloat(ImpExpectedSalary) + parseFloat(ImpIncentiveAmt);
			$(this).closest('tr').find('.ImpGrossAmt').val(newAmt);
		});
		
		

		


		$('#manage-employee').submit(function(e){
			e.preventDefault()
			start_load()
			$.ajax({
				url:'ajax.php?action=process_salary',
				data: new FormData($(this)[0]),
				cache: false,
				contentType: false,
				processData: false,
				method: 'POST',
				type: 'POST',
				success:function(resp){
					if(resp==1){
						alert_toast("Data successfully added",'success')
						setTimeout(function(){
							window.open('index.php?page=Employees/salary-slips','_self');
						},1500)

					}else{
						alert_toast("Error Occured"+resp,'danger');
					}
				}
			})
		})

		$('table').dataTable()
	</script>


	<?php
}else{
	include 'accessDenied.php';
}
?>
