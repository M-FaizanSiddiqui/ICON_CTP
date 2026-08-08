<?php
session_start();
header('Content-Type: application/json');

include('../db_connect.php');

function attendance_whatsapp_response($status, $message, $rows = array())
{
	echo json_encode(array('status' => $status, 'message' => $message, 'rows' => $rows));
	exit;
}

function attendance_whatsapp_phone($phone)
{
	$digits = preg_replace('/\D+/', '', trim((string)$phone));

	if ($digits === '') {
		return '';
	}

	if (substr($digits, 0, 2) === '00') {
		$digits = substr($digits, 2);
	}

	if (substr($digits, 0, 1) === '0') {
		$digits = '92'.substr($digits, 1);
	} elseif (strlen($digits) === 10 && substr($digits, 0, 1) === '3') {
		$digits = '92'.$digits;
	}

	return $digits;
}

function attendance_find_entry($date, $rows, $status)
{
	foreach ($rows as $row) {
		if ($row['dated'] === $date && (string)$row['status'] === (string)$status) {
			return $row;
		}
	}
	return null;
}

if (!isset($_SESSION['login_id'])) {
	attendance_whatsapp_response('error', 'Session expired. Please login again.');
}

if (isset($_SESSION['login_Permisions']) && !in_array('62', $_SESSION['login_Permisions'])) {
	attendance_whatsapp_response('error', 'You do not have permission to share attendance.');
}

$fromDt = date('Y-m-d', strtotime('-6 days'));
$toDt = date('Y-m-d');
$periodLabel = date('d-M-Y', strtotime($fromDt)).' to '.date('d-M-Y', strtotime($toDt));

$holidayRows = array();
$holidayQuery = "SELECT * FROM holidays WHERE holiday_date >= '".$fromDt."' AND holiday_date <= '".$toDt."' AND effective = 0";
$holidayResult = mysqli_query($conn, $holidayQuery);
while ($holiday = mysqli_fetch_array($holidayResult)) {
	$holidayRows[$holiday[2]] = $holiday[1];
}

$employees = mysqli_query($conn, "SELECT emp_id, emp_name, emp_ph_no FROM employee WHERE emp_status = 0 ORDER BY emp_name ASC");
$rows = array();

while ($employee = mysqli_fetch_assoc($employees)) {
	$empId = (int)$employee['emp_id'];
	$phone = attendance_whatsapp_phone($employee['emp_ph_no']);

	if ($phone === '' || strlen($phone) < 11) {
		$rows[] = array(
			'emp_id' => $empId,
			'emp_name' => $employee['emp_name'],
			'phone' => $employee['emp_ph_no'],
			'status' => 'missing_phone',
			'url' => '',
			'message' => 'Phone number missing or invalid'
		);
		continue;
	}

	$attendanceRows = array();
	$attendanceQuery = "SELECT * FROM attendance WHERE emp_id = ".$empId." AND dated >= '".$fromDt."' AND dated <= '".$toDt."' AND del_status = 0 ORDER BY dated ASC, status ASC, dateTime ASC";
	$attendanceResult = mysqli_query($conn, $attendanceQuery);
	while ($attendance = mysqli_fetch_assoc($attendanceResult)) {
		$attendanceRows[] = $attendance;
	}

	$messageLines = array();
	$messageLines[] = 'Dear '.$employee['emp_name'].',';
	$messageLines[] = '';
	$messageLines[] = 'Your attendance summary for the last 7 days is below.';
	$messageLines[] = 'Period: '.$periodLabel;
	$messageLines[] = '';

	$presentDays = 0;
	$absentDays = 0;
	$missingDays = 0;
	$totalMinutes = 0;

	for ($date = $fromDt; $date <= $toDt; $date = date('Y-m-d', strtotime($date.' +1 day'))) {
		$dayName = date('D', strtotime($date));
		$dateLabel = date('d-M', strtotime($date));
		$holidayName = isset($holidayRows[$date]) ? $holidayRows[$date] : '';
		$checkIn = attendance_find_entry($date, $attendanceRows, 0);
		$checkOut = attendance_find_entry($date, $attendanceRows, 1);

		if ($holidayName !== '' && !$checkIn && !$checkOut) {
			$messageLines[] = $dayName.' '.$dateLabel.': Holiday - '.$holidayName;
			continue;
		}

		if ($checkIn && $checkOut) {
			$inTime = date('h:i A', strtotime($checkIn['dateTime']));
			$outTime = date('h:i A', strtotime($checkOut['dateTime']));
			$inObj = new DateTime($checkIn['dateTime']);
			$outObj = new DateTime($checkOut['dateTime']);
			$diff = $outObj->diff($inObj);
			$dayMinutes = ((int)$diff->format('%h') * 60) + (int)$diff->format('%i');
			$totalMinutes += $dayMinutes;
			$presentDays++;
			$messageLines[] = $dayName.' '.$dateLabel.': Check-in '.$inTime.' | Check-out '.$outTime.' | '.$diff->format('%Hhr %Imin');
		} elseif ($checkIn || $checkOut) {
			$missingDays++;
			$inTime = $checkIn ? date('h:i A', strtotime($checkIn['dateTime'])) : 'Missing';
			$outTime = $checkOut ? date('h:i A', strtotime($checkOut['dateTime'])) : 'Missing';
			$messageLines[] = $dayName.' '.$dateLabel.': Check-in '.$inTime.' | Check-out '.$outTime.' | Incomplete';
		} else {
			$absentDays++;
			$messageLines[] = $dayName.' '.$dateLabel.': Absent';
		}
	}

	$totalHours = intdiv($totalMinutes, 60);
	$remainingMinutes = $totalMinutes % 60;

	$messageLines[] = '';
	$messageLines[] = 'Summary:';
	$messageLines[] = 'Present: '.$presentDays.' day(s)';
	$messageLines[] = 'Absent: '.$absentDays.' day(s)';
	$messageLines[] = 'Incomplete: '.$missingDays.' day(s)';
	$messageLines[] = 'Total Working Time: '.$totalHours.'hr '.$remainingMinutes.'min';
	$messageLines[] = '';
	$messageLines[] = 'Regards,';
	$messageLines[] = 'ICON Design';

	$message = implode("\n", $messageLines);

	$rows[] = array(
		'emp_id' => $empId,
		'emp_name' => $employee['emp_name'],
		'phone' => $phone,
		'status' => 'ready',
		'url' => 'https://wa.me/'.$phone.'?text='.rawurlencode($message),
		'message' => $message
	);
}

attendance_whatsapp_response('success', 'Last week attendance messages prepared.', $rows);

?>
