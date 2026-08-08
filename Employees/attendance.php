<?php include('db_connect.php');

if(in_array(62,$_SESSION['login_Permisions']))
{

  $month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');

  $fromDt = icon_date_value($_GET['fromDt'] ?? '', date('Y-m-01'));
  $toDt = icon_date_value($_GET['toDt'] ?? '', date('Y-m-t'));
  $employee_id = icon_get_int('employee_id');

  ?>
  <style>
    .attendance-header{display:flex!important;align-items:center;justify-content:space-between;gap:16px}
    .attendance-actions{display:flex;align-items:center;justify-content:flex-end;gap:8px;flex-wrap:wrap}
    .attendance-actions .btn{display:inline-flex;align-items:center;gap:6px;min-height:36px;padding:8px 12px;border-radius:9px;font-size:11px;font-weight:600;white-space:nowrap}
    .attendance-actions .attendance-whatsapp-btn{border-color:#cceadd;color:#128c7e;background:#e8f7f1}
    .attendance-actions .attendance-whatsapp-btn:hover{color:#fff;background:#128c7e;border-color:#128c7e}
    #loading_sync{display:none;align-items:center;gap:7px;min-height:36px;padding:8px 12px;border:1px solid #ffd9c3;border-radius:9px;color:#d95c18;background:#fff5ee;font-size:11px;font-weight:600}
    .attendance-filters{margin:16px 18px 18px!important;padding:15px 14px 14px;border:1px solid #e6e7ea;border-radius:11px;background:#f8f8f9}
    .attendance-filters label{display:block;margin:0 0 7px;color:#56575d;font-size:10px;font-weight:700;letter-spacing:.03em;text-transform:uppercase}
    .attendance-filters label i{width:15px;margin-right:4px;color:#f36b21;text-align:center}
    .attendance-filters .form-control{height:40px!important;border:1px solid #dfe1e5!important;border-radius:9px!important;font-size:12px;box-shadow:none}
    .attendance-filters .form-control:focus{border-color:#f36b21!important;box-shadow:0 0 0 3px rgba(243,107,33,.1)!important}
    .attendance-filter-action{display:flex;align-items:flex-end}
    .attendance-filter-action .btn{width:100%;height:40px;border:0;border-radius:9px;font-size:11px;font-weight:600;background:#f36b21!important}
    .attendance-divider{display:none}
    .attendance-table-row{margin:0 18px 18px!important}
    .attendance-table-wrap{overflow:auto;border:1px solid #e3e4e7;border-radius:11px;background:#fff}
    #report-list{min-width:840px;margin:0!important;border:0!important;border-collapse:separate!important;border-spacing:0}
    #report-list thead th{padding:11px 9px!important;border-top:0!important;border-right:0!important;border-bottom:1px solid #dedfe2!important;border-left:0!important;white-space:nowrap;color:#62636a!important;background:#f4f5f6!important;font-size:10px;text-transform:uppercase}
    #report-list tbody td,#report-list tbody th{padding:9px!important;border-top:0!important;border-right:0!important;border-bottom:1px solid #ececef!important;border-left:0!important;vertical-align:middle!important;font-size:12px}
    #report-list tbody tr:last-child td,#report-list tbody tr:last-child th{border-bottom:0!important}
    #report-list tbody tr:hover td{background-color:#fffaf7}
    #report-list tbody td[style*="background-color:#7be77b"],#report-list tbody td[style*="background-color:#7ee9d3"]{color:#176b43!important;background:#e9f8ef!important;font-weight:700}
    #report-list tbody td[style*="background-color:#ff5151"]{color:#bd2626!important;background:#fff0f0!important;font-weight:700}
    #report-list tbody td[style*="background-color:lightpink"]{color:#8a3d16!important;background:#fff4e9!important;font-weight:700}
    #report-list tbody td[style*="background-color:#ffd800"]{color:#8a5a00!important;background:#fff7cf!important;font-weight:700}
    #report-list .btn-warning{display:inline-grid;place-items:center;width:32px;height:32px;margin:0!important;padding:0!important;border:1px solid #ffd3bd!important;border-radius:8px;color:#df5913!important;background:#fff4ed!important}
    #report-list .btn-warning:hover{color:#fff!important;background:#f36b21!important}
    .attendance-summary-card{margin:0 18px 22px;padding:0;border:1px solid #e3e4e7;border-radius:11px;overflow:hidden;background:#fff}
    .attendance-summary-card table{margin:0!important;border:0!important}
    .attendance-summary-card th{padding:12px!important;border:0!important;color:#303033;background:#f4f5f6!important;font-size:12px;text-transform:uppercase}
    .attendance-summary-card td{padding:10px 12px!important;border:0!important;border-bottom:1px solid #ececef!important;font-size:12px}
    .attendance-summary-card tr:last-child td{border-bottom:0!important}
    #att_edt_model .modal-dialog{max-width:560px;margin:7vh auto}
    #att_edt_model .modal-content{overflow:hidden;border:0;border-radius:15px;box-shadow:0 24px 70px rgba(28,29,32,.24)}
    #att_edt_model .modal-header{display:flex!important;align-items:center;justify-content:space-between;min-height:68px;padding:15px 18px!important;border:0;border-bottom:1px solid #ececef;border-left:4px solid #f36b21;background:#fff!important}
    .attendance-modal-heading{display:flex;align-items:center;gap:12px}
    .attendance-modal-icon{display:grid;place-items:center;width:40px;height:40px;border-radius:11px;color:#fff;background:linear-gradient(145deg,#f36b21,#df5812)}
    .attendance-modal-heading h4{margin:0;font-size:16px;font-weight:650;color:#303033}
    .attendance-modal-heading p{margin:3px 0 0;font-size:10px;color:#898a90}
    .attendance-modal-close{display:grid;place-items:center;width:32px;height:32px;padding:0;border:0;border-radius:8px;color:#6f7075;background:#f4f4f5}
    #att_edt_model .modal-body{padding:20px;background:#f7f7f8}
    .attendance-modal-context{margin-bottom:14px;padding:12px 13px;border:1px solid #e4e5e8;border-radius:9px;background:#fff}
    .attendance-modal-context label{display:block;margin:0 0 6px;font-size:11px;color:#626369}
    .attendance-modal-context label:last-child{margin-bottom:0}
    .attendance-modal-field label{display:block;margin-bottom:7px;font-size:10px;font-weight:700;color:#626369;text-transform:uppercase}
    .attendance-modal-field input,.attendance-modal-field textarea{width:100%;border:1px solid #dfe1e5!important;border-radius:9px!important;font-size:12px;box-shadow:none}
    .attendance-modal-field input{height:40px;padding:8px 10px}
    .attendance-modal-field textarea{min-height:92px;padding:9px 11px;resize:none}
    #att_edt_model .modal-footer{display:flex;justify-content:flex-end;gap:8px;padding:13px 18px;border:0;border-top:1px solid #e7e8eb;background:#fff}
    .attendance-modal-action{display:inline-flex;align-items:center;gap:7px;min-height:38px;margin:0!important;padding:8px 14px;border-radius:9px;font-size:11px;font-weight:600}
    .attendance-modal-action.cancel{border:1px solid #dadce0;color:#5f6065;background:#fff}
    .attendance-modal-action.save{border:1px solid #f36b21;color:#fff;background:#f36b21;box-shadow:0 7px 16px rgba(243,107,33,.18)}
    #attendance_whatsapp_modal .modal-dialog{max-width:760px;margin:6vh auto}
    #attendance_whatsapp_modal .modal-content{overflow:hidden;border:0;border-radius:15px;box-shadow:0 24px 70px rgba(28,29,32,.24)}
    #attendance_whatsapp_modal .modal-header{display:flex!important;align-items:center;justify-content:space-between;min-height:68px;padding:15px 18px!important;border:0;border-bottom:1px solid #ececef;border-left:4px solid #128c7e;background:#fff!important}
    .attendance-wa-heading{display:flex;align-items:center;gap:12px}
    .attendance-wa-icon{display:grid;place-items:center;width:40px;height:40px;border-radius:11px;color:#fff;background:#128c7e}
    .attendance-wa-heading h4{margin:0;font-size:16px;font-weight:650;color:#303033}
    .attendance-wa-heading p{margin:3px 0 0;font-size:10px;color:#898a90}
    .attendance-wa-close{display:grid;place-items:center;width:32px;height:32px;padding:0;border:0;border-radius:8px;color:#6f7075;background:#f4f4f5}
    #attendance_whatsapp_modal .modal-body{padding:18px;background:#f7f7f8}
    .attendance-wa-note{margin:0 0 13px;padding:10px 12px;border:1px solid #d8eee4;border-radius:9px;color:#42675b;background:#effaf5;font-size:11px}
    .attendance-wa-list{overflow:auto;max-height:420px;border:1px solid #e3e4e7;border-radius:11px;background:#fff}
    .attendance-wa-table{width:100%;margin:0;border-collapse:separate;border-spacing:0}
    .attendance-wa-table th{padding:11px 10px;border-bottom:1px solid #e3e4e7;color:#62636a;background:#f4f5f6;font-size:10px;text-transform:uppercase}
    .attendance-wa-table td{padding:10px;border-bottom:1px solid #eeeeef;font-size:12px;vertical-align:middle}
    .attendance-wa-table tr:last-child td{border-bottom:0}
    .attendance-wa-status{display:inline-flex;align-items:center;gap:6px;padding:5px 8px;border-radius:20px;font-size:10px;font-weight:700}
    .attendance-wa-status.ready{color:#128c7e;background:#e8f7f1}
    .attendance-wa-status.missing{color:#ba4b21;background:#fff1e8}
    .attendance-wa-send{display:inline-flex;align-items:center;gap:6px;min-height:32px;padding:7px 10px;border:0;border-radius:8px;color:#fff;background:#128c7e;font-size:11px;font-weight:600}
    .attendance-wa-send[disabled]{cursor:not-allowed;opacity:.55}
    #attendance_whatsapp_modal .modal-footer{display:flex;justify-content:flex-end;gap:8px;padding:13px 18px;border:0;border-top:1px solid #e7e8eb;background:#fff}
    @media(max-width:767px){.attendance-header{align-items:flex-start;flex-direction:column}.attendance-actions{width:100%;justify-content:flex-start}.attendance-filters{margin:12px!important}.attendance-filters>[class*="col-"]{margin-bottom:11px}.attendance-table-row{margin:0 12px 14px!important}.attendance-summary-card{margin:0 12px 16px}}
  </style>
  <div class="container-fluid professional-payroll-page">
    <div class="col-lg-12">
      <div class="card payroll-card">
        <div class="payroll-header attendance-header">
          <div class="payroll-title"><span class="payroll-title-icon"><i class="fas fa-calendar-check"></i></span><div><h2>Attendance</h2><p>Review, synchronize, export, and correct attendance records.</p></div></div>
          <div class="attendance-actions">
            <span id="loading_sync"><i class="fas fa-circle-notch fa-spin"></i> Syncing attendance...</span>
            <button type="button" class="btn btn-outline-secondary btn-sm" id="sync_attendance"><i class="fas fa-sync-alt"></i> Sync</button>
            <button type="button" class="btn btn-outline-success btn-sm exportToExcel"><i class="fas fa-file-excel"></i> Export</button>
            <button type="button" class="btn btn-outline-success btn-sm attendance-whatsapp-btn" id="attendance_whatsapp_week"><i class="fab fa-whatsapp"></i> WhatsApp Last 7 Days</button>
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
            $query21 = "SELECT * from attendance WHERE emp_id = ".$emp_id_TB." AND dated = '".$att_date."' AND status = 0 AND del_status = 0 ";
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
                $upQuery1 .= " AND status ='0' ";
                $upQuery1 .= " AND del_status ='0' ";
                $result1 = mysqli_query($conn, $upQuery1);

                $dateTime = $att_date.' '.$checkInTime;
                $studentQuery1 = "INSERT INTO attendance SET ";
                $studentQuery1 .= " emp_id = ".$emp_id_TB;
                $studentQuery1 .= ", dated ='".$att_date."' ";
                $studentQuery1 .= ", time ='".$checkInTime."' ";
                $studentQuery1 .= ", dateTime ='".$dateTime."' ";
                $studentQuery1 .= ", status ='0' ";
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
              $studentQuery1 .= ", status ='0' ";
              $studentQuery1 .= ", remarks ='edited entry' ";
              $studentQuery1 .= ", del_reason = '".$reasonEdit."' ";
              $result3 = mysqli_query($conn, $studentQuery1);
            }


            $query31 = "SELECT * from attendance WHERE emp_id = ".$emp_id_TB." AND dated = '".$att_date."' AND status = 1 AND del_status = 0 ";
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
                $upQuery2 .= " AND status ='1' ";
                $upQuery2 .= " AND del_status ='0' ";
                $result2 = mysqli_query($conn, $upQuery2);

                $dateTime = $att_date.' '.$checkOutTime;
                $studentQuery2 = "INSERT INTO attendance SET ";
                $studentQuery2 .= " emp_id = ".$emp_id_TB;
                $studentQuery2 .= ", dated ='".$att_date."' ";
                $studentQuery2 .= ", time ='".$checkOutTime."' ";
                $studentQuery2 .= ", dateTime ='".$dateTime."' ";
                $studentQuery2 .= ", status ='1' ";
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
              $studentQuery2 .= ", status ='1' ";
              $studentQuery2 .= ", remarks ='edited entry' ";
              $studentQuery2 .= ", del_reason = '".$reasonEdit."' ";
              $result4 = mysqli_query($conn, $studentQuery2);
            }

            if($result1 && $result2 && $result3 && $result4 ){
              mysqli_query($conn,"COMMIT");
              ?>
              <script>
                alert("Attendance Updated Successfully");
                window.open('index.php?page=Employees/attendance&fromDt=<?php echo $att_date ?>&toDt=<?php echo $att_date ?>&employee_id=<?php echo $emp_id_TB ?>','_self');
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


          <div class="row attendance-filters">
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
                $query_emp = "SELECT * FROM employee WHERE emp_status = 0 AND emp_designation_id IN ('1','2','3','4') ";
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
            <div class="col-md-3 attendance-filter-action">
              <button type="button" class="btn btn-success btn-sm" id="filterBtn"><i class="fas fa-filter"></i> Get Attendance</button>
            </div>
          </div>

          <hr class="attendance-divider">

          <div class="row attendance-table-row">

            <?php
            if($employee_id != 0 || $employee_id =='all'){
              ?>
              <div class="col-md-12 p-0 attendance-table-wrap">
               <table class="table table-bordered" id='report-list'  data-cols-width="10,20,20,20,20,20">
                 <thead>

                   <tr style="background-color: #e5e5e5">
                    <th data-f-bold="true" data-a-h="center" data-b-a-s="true" class="text-center" style="width:10px;border:1px solid gray">SR#</th>
                    <th data-f-bold="true" data-a-h="center" data-b-a-s="true" class="text-center" style="border:1px solid gray">Date</th>
                    <th data-f-bold="true" data-a-h="center" data-b-a-s="true" class="text-center" style="border:1px solid gray">Day</th>
                    <th data-f-bold="true" data-a-h="center" data-b-a-s="true" class="text-center" style="border:1px solid gray">Check In</th>
                    <th data-f-bold="true" data-a-h="center" data-b-a-s="true" class="text-center" style="border:1px solid gray">Check Out</th>
                    <th data-f-bold="true" data-a-h="center" data-b-a-s="true" class="text-center" style="border:1px solid gray">Total Hours</th>
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


                    $queryAtt = "SELECT * FROM attendance WHERE emp_id = ".$emp_id." AND dated >= '".$fromDt."' AND dated <= '".$toDt."' AND del_status = 0 order by id DESC ";
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
                        $st = 0;
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

                       $st = "1";
                       $dateTimeChkOut ="";
                       $checkOutInd = searchForDt($new_date, $usr_mod_permisions,$st);
                       $checkOut = "Missing";
                       $checkOutColor = "#ff5151";

                       if($checkOutInd != 111111 ){
                        $dateTimeChkOut = $usr_mod_permisions[$checkOutInd][4];
                        $checkOut = date('h:i A',strtotime($dateTimeChkOut));
                        $checkOutColor = "#7be77b";
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

                        }
                      }

                      $totalTime = "0";
                      if($checkOutInd != 111111 && $checkInInd != 111111){
                        $expiry_time = new DateTime($dateTimeChkOut);
                        $current_date = new DateTime($dateTimeChkIn);
                        $diff = $expiry_time->diff($current_date);
                        $totalTime = $diff->format('%Hhr %Imin'); 
                        // $totalTime = $diff->format('%H:%I:%S');

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









                      //// Overtime calc Night shift

                      $st = 4;
                      $OverTimeInInd = searchForDt($new_date, $usr_mod_permisions,$st);
                      $overtimeFound = 0;
                      $OverTimeIn = '';
                      $OverTimeInColor = "#ff5151";
                      if($OverTimeInInd != 111111){
                        $dateTimeOverTimeIn = $usr_mod_permisions[$OverTimeInInd][4];
                        $OverTimeIn = date('h:i A',strtotime($dateTimeOverTimeIn));
                        $OverTimeInColor = "#7be77b";
                        $overtimeFound++;
                      }

                      $st = 5;
                      $OverTimeOutInd = searchForDt($new_date, $usr_mod_permisions,$st);

                      $OverTimeInColor = "#ff5151";
                      $OverTimeOut  = '';
                      if($OverTimeOutInd != 111111){
                        $dateTimeOverTimeIn = $usr_mod_permisions[$OverTimeOutInd][4];
                        $OverTimeOut = date('h:i A',strtotime($dateTimeOverTimeIn));
                        $OverTimeInColor = "#7be77b";
                        $overtimeFound++;
                      }

                      $totalTimeNight = "0";
                      if($OverTimeInInd != 111111 && $OverTimeOutInd != 111111){
                        $expiry_time = new DateTime($OverTimeOut);
                        $current_date = new DateTime($OverTimeIn);
                        $diff = $expiry_time->diff($current_date);
                        $totalTimeNight = $diff->format('%Hhr %Imin'); 
                        // $totalTime = $diff->format('%H:%I:%S');

                        $tHour += $diff->format('%H'); 
                        $tMin += $diff->format('%I'); 
                      }

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
                            <td data-exclude="true" style="text-align: center;padding: 0px;padding-top: 4px;border:1px solid gray">
                              <button class="btn btn-warning btn-sm" data-value="1^2^<?php echo $edtCheckIn ?>^<?php echo $edtCheckOut ?>^<?php echo date('d-M-Y',strtotime($new_date))?>^<?php echo $emp_id?>^<?php echo $emp_name?>" data-toggle="modal" id="edt_btn" data-target="#att_edt_model" style="margin: 1px;padding: 4px !important;"><i class="fa fa-edit"></i></button>
                            </td>
                            <?php

                          }else{
                            ?>
                            <td data-a-h="center" data-b-a-s="true" data-fill-color="" colspan="4" class="text-center" style="background-color:lightpink;border:1px solid gray"><b>Weekend</b></td>
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
                            <td data-exclude="true" style="text-align: center;padding: 0px;padding-top: 4px;border:1px solid gray">
                              <button class="btn btn-warning btn-sm" data-value="1^2^<?php echo $edtCheckIn ?>^<?php echo $edtCheckOut ?>^<?php echo date('d-M-Y',strtotime($new_date))?>^<?php echo $emp_id?>^<?php echo $emp_name?>" data-toggle="modal" id="edt_btn" data-target="#att_edt_model" style="margin: 1px;padding: 4px !important;"><i class="fa fa-edit"></i></button>
                            </td>
                            <?php
                          }


                          else{
                            ?>
                            <td data-a-h="center" data-b-a-s="true" data-fill-color="" colspan="4" class="text-center" style="background-color:lightpink;border:1px solid gray"><b><?= $gazated_holiday_name?></b></td>
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
                          <td data-exclude="true" style="text-align: center;padding: 0px;padding-top: 4px;border:1px solid gray">
                            <button class="btn btn-warning btn-sm" data-value="1^2^<?php echo $edtCheckIn ?>^<?php echo $edtCheckOut ?>^<?php echo date('d-M-Y',strtotime($new_date))?>^<?php echo $emp_id?>^<?php echo $emp_name?>" data-toggle="modal" id="edt_btn" data-target="#att_edt_model" style="margin: 1px;padding: 4px !important;"><i class="fa fa-edit"></i></button>
                          </td>
                          <?php
                        }
                        ?>


                      </tr>


                      <?php

                      if($overtimeFound>0){
                        ?>
                        <tr>
                          <td data-a-h="center" data-b-a-s="true" data-t="n" style="border:1px solid gray" class="text-center"><?= $i ?></td>
                          <td data-a-h="center" data-b-a-s="true" style="border:1px solid gray" class="text-center"><?= date('d-M-Y',strtotime($new_date)) ?></td>
                          <td data-a-h="center" data-b-a-s="true" style="border:1px solid gray" class="text-center"><?= $dayName ?> (Night Shift)</td>
                          <?php

                          ?>
                          <td data-a-h="center" data-b-a-s="true" data-fill-color="<?= $checkInColor ?>" class="text-center" style="color:black;background-color:#ffd800;border:1px solid gray"><?= $OverTimeIn ?></td>
                          <td data-a-h="center" data-b-a-s="true" data-fill-color="<?= $checkInColor ?>" class="text-center" style="color:black;background-color:#ffd800;border:1px solid gray"><?= $OverTimeOut ?></td>
                          <td data-a-h="center" data-b-a-s="true" style="border:1px solid gray"  class="text-center"><?= $totalTimeNight ?></td>
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
                          <td data-exclude="true" style="text-align: center;padding: 0px;padding-top: 4px;border:1px solid gray">
                            -
                          </td>
                          <?php



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

                  // $totalTime212 = $emp_total_time->format('%Hhr %Imin'); 
                  ?>
                  <tr>
                    <th data-f-bold="true" data-a-h="center" data-b-a-s="true" style="border:1px solid gray;font-size: 14px" colspan="5" class="text-right">Total Time</th>
                    <th data-f-bold="true" data-a-h="center" data-b-a-s="true" style="border:1px solid gray;font-size: 14px" class="text-center"><?= $emp_total_time ?></th>
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




<!-- <div class="col-md-1"> -->
  <!-- </div> -->
  <div class="col-md-4 attendance-summary-card">
    <?php
    if($employee_id != "all" && $employee_id != 0){

      $totalChecknInMiss_this = $checkInMissingCount - $absentCount;
      $totalChecknOutMiss_this = $checkOutMissingCount - $absentCount;
                // $total_absent_miss_this = $absentCount;
      ?>
      <table class="table table-bordered">
        <thead>
          <tr class="text-center">
            <th style="border:1px solid gray;background-color: #e5e5e5" colspan="2">Summary</th>
          </tr>
          <tr>
            <td style="border:1px solid gray">Total Working Days</td>
            <td style="border:1px solid gray" class="text-center"><?= $total_working_days ?></td>
          </tr>
          <tr>
            <td style="border:1px solid gray">Total Holidays</td>
            <td style="border:1px solid gray" class="text-center"><?= $total_holidays ?></td>
          </tr>

          <tr>
            <td style="border:1px solid gray">Total Checkin Missing</td>
            <td style="border:1px solid gray" class="text-center"><?= $totalChecknInMiss_this?></td>
          </tr>
          <tr>
            <td style="border:1px solid gray">Total CheckOut Missing</td>
            <td style="border:1px solid gray" class="text-center"><?= $totalChecknOutMiss_this ?></td>
          </tr>
          <tr>
            <td style="border:1px solid gray">Total Absents</td>
            <td style="border:1px solid gray" class="text-center"><?= $absentCount ?></td>
          </tr>
          <tr>
            <td style="border:1px solid gray">Total Working Hours</td>
            <td style="border:1px solid gray" class="text-center"><?= $emp_total_time ?></td>
          </tr>

          <?php
          $tMin = ($total_hours*60) + $total_minutes;
          $aa = $tMin/$total_working_days;


          $hours_new_this = intdiv($aa, 60);
          $total_minutes_this = ($aa % 60);

          $avg_time = $hours_new_this.'hr '.$total_minutes_this.'min';
          ?>
          <tr>
            <td style="border:1px solid gray">Average Time Per Day</td>
            <td style="border:1px solid gray" class="text-center"><?= $avg_time ?></td>

          </tr>
        </thead>
        <tbody>
        </tbody>
      </table>
      <?php
    }
    ?>

  </div>

</div>





</div>
</div>
</div>
</div>

<div class="modal fade" id="att_edt_model" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post">
        <div class="modal-header">
          <div class="attendance-modal-heading"><span class="attendance-modal-icon"><i class="fas fa-user-clock"></i></span><div><h4>Attendance Edit</h4><p>Correct employee check-in and check-out time.</p></div></div>
          <button type="button" class="attendance-modal-close" data-dismiss="modal" aria-hidden="true" aria-label="Close"><i class="fa fa-times"></i></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-12 attendance-modal-context">
              <label><b>Employee: </b><span id="edt_emp_span"></span></label>
              <label><b>Attendance Date: </b><span id="edt_att_dt"></span></label>
            </div>
            <div class="col-md-6 attendance-modal-field">
             <label for="checkInTime">Check In</label>
             <input type="time" name="checkInTime" id="checkInTime" class="form-control">
           </div>
           <div class="col-md-6 attendance-modal-field">
            <label for="checkOutTime">Check Out</label>
            <input type="time" name="checkOutTime" id="checkOutTime" class="form-control">
          </div>

          <div class="col-md-12 mt-3 attendance-modal-field">
            <label for="reasonEdit">Reason for edit</label>
            <textarea name="reasonEdit" id="reasonEdit" class="form-control" placeholder="Enter reason for attendance correction"></textarea>
          </div>
        </div>


        <input type="hidden" name="att_date" id="att_date">
        <input type="hidden" name="att_id_check_in" id="att_id_check_in">
        <input type="hidden" name="emp_id_TB" id="emp_id_TB">
        <input type="hidden" name="att_id_check_out" id="att_id_check_out">

      </div>
      <div class="modal-footer">
        <button type="button" class="btn attendance-modal-action cancel" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
        <button type="submit" class="btn attendance-modal-action save" id="change_status" name="change_status"><i class="fa fa-check"></i> Update</button>
      </div>
    </form>
  </div>
</div>
</div>

<div class="modal fade" id="attendance_whatsapp_modal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <div class="attendance-wa-heading"><span class="attendance-wa-icon"><i class="fab fa-whatsapp"></i></span><div><h4>Last 7 Days Attendance</h4><p>Prepared WhatsApp messages for active employees.</p></div></div>
        <button type="button" class="attendance-wa-close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
      </div>
      <div class="modal-body">
        <div class="attendance-wa-note">WhatsApp Web will open with the message filled. Please press Send in WhatsApp for each employee.</div>
        <div class="attendance-wa-list">
          <table class="attendance-wa-table">
            <thead>
              <tr>
                <th style="width:70px">Emp</th>
                <th>Employee</th>
                <th style="width:140px">Phone</th>
                <th style="width:120px">Status</th>
                <th style="width:90px">Action</th>
              </tr>
            </thead>
            <tbody id="attendance_whatsapp_rows">
              <tr><td colspan="5" class="text-center">No messages prepared yet.</td></tr>
            </tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn attendance-modal-action cancel" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
        <button type="button" class="btn attendance-modal-action save" id="attendance_whatsapp_open_all"><i class="fab fa-whatsapp"></i> Open Ready Messages</button>
      </div>
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
            window.open('index.php?page=Employees/attendance','_self');
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
    location.replace('index.php?page=Employees/attendance&fromDt='+fromDt+'&toDt='+toDt+'&employee_id='+employee_id)
  })

  var attendanceWhatsappRows = [];

  function attendanceWhatsappEscape(text){
    return $('<div>').text(text == null ? '' : text).html();
  }

  function attendanceWhatsappRender(rows){
    attendanceWhatsappRows = rows || [];
    var html = '';

    if(attendanceWhatsappRows.length === 0){
      html = '<tr><td colspan="5" class="text-center">No active employees found.</td></tr>';
    }

    attendanceWhatsappRows.forEach(function(row, index){
      var ready = row.status === 'ready' && row.url;
      var statusClass = ready ? 'ready' : 'missing';
      var statusText = ready ? 'Ready' : 'No Phone';
      var action = ready
        ? '<button type="button" class="attendance-wa-send" data-index="'+index+'"><i class="fab fa-whatsapp"></i> Send</button>'
        : '<button type="button" class="attendance-wa-send" disabled><i class="fa fa-ban"></i> Skip</button>';

      html += '<tr>';
      html += '<td>EMP-'+attendanceWhatsappEscape(row.emp_id)+'</td>';
      html += '<td>'+attendanceWhatsappEscape(row.emp_name)+'</td>';
      html += '<td>'+attendanceWhatsappEscape(row.phone || '-')+'</td>';
      html += '<td><span class="attendance-wa-status '+statusClass+'">'+statusText+'</span></td>';
      html += '<td>'+action+'</td>';
      html += '</tr>';
    });

    $('#attendance_whatsapp_rows').html(html);
  }

  $('#attendance_whatsapp_week').click(function(){
    var btn = $(this);
    var oldHtml = btn.html();
    btn.prop('disabled',true).html('<i class="fa fa-spinner fa-spin"></i> Preparing...');

    $.ajax({
      url:'Employees/attendance-whatsapp-week.php',
      method:'POST',
      dataType:'json',
      success:function(resp){
        if(resp && resp.status === 'success'){
          attendanceWhatsappRender(resp.rows || []);
          $('#attendance_whatsapp_modal').modal('show');
        }else{
          alert_toast((resp && resp.message) ? resp.message : 'Unable to prepare WhatsApp messages.','danger');
        }
      },
      error:function(){
        alert_toast('Unable to prepare WhatsApp messages.','danger');
      },
      complete:function(){
        btn.prop('disabled',false).html(oldHtml);
      }
    });
  })

  $(document).on('click','.attendance-wa-send',function(){
    var index = $(this).attr('data-index');
    if(attendanceWhatsappRows[index] && attendanceWhatsappRows[index].url){
      window.open(attendanceWhatsappRows[index].url,'_blank');
    }
  })

  $('#attendance_whatsapp_open_all').click(function(){
    var readyRows = attendanceWhatsappRows.filter(function(row){
      return row.status === 'ready' && row.url;
    });

    if(readyRows.length === 0){
      alert_toast('No WhatsApp-ready employee messages found.','danger');
      return;
    }

    readyRows.forEach(function(row, index){
      setTimeout(function(){
        window.open(row.url,'_blank');
      }, index * 700);
    });

    alert_toast('Opening ready WhatsApp messages. If browser blocks tabs, use Send one by one.','success');
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



<?php
}else{
  include 'accessDenied.php';
}
?>
