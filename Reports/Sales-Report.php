<?php require_once(__DIR__.'/../includes/pdf_runtime.php'); icon_pdf_session_start();
$report_permissions = isset($_SESSION['login_Permisions']) && is_array($_SESSION['login_Permisions']) ? $_SESSION['login_Permisions'] : array();
if(in_array("43",$report_permissions))
{
	require_once('../tcpdf/tcpdf.php');
	require_once('../includes/pdf_report_helper.php');
	include '../db_connect.php';

	if(isset($_POST['open_rpt'], $_POST['from_date'], $_POST['to_date'])){
		$from_dt = icon_date_value($_POST['from_date'] ?? '', date('Y-m-d'));
		$to_dt = icon_date_value($_POST['to_date'] ?? '', date('Y-m-d'));

		class MYPDF extends TCPDF 
		{
			public function Header() 
			{
				$pdf_report_heading = 'Sales Report';

				$this->SetFont('helvetica', 'B', 16);
				$image_file = K_PATH_IMAGES.'logo.jpg';
				$this->Image($image_file, 10, 10, 35, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);

				$this->SetFont('helvetica', 'R', 10);
				// $this->Cell(1, 7, 'Address: ICON Site Area near Naurus Chorangi Karachi,Pakistan.', 0, false, 'L', 0, '', 0, false, 'S', 'S');
				// $this->Cell(0, 18, 'PH: 0331-1114266 | 021-32564266', 0, false, 'L', 0, '', 0, false, 'S', 'S');
				
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

		$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor('Sales Report');
		$pdf->SetTitle('Sales Report');
		$pdf->SetSubject('Sales Report');
		$pdf->SetKeywords('Sales Report');
		$PDF_HEADER_LOGO_WIDTH = "20";
		$PDF_HEADER_TITLE = "This is my Title";
		$PDF_HEADER_STRING = "This is Header Part";
		$pdf->SetHeaderData(PDF_HEADER_LOGO, $PDF_HEADER_LOGO_WIDTH, $PDF_HEADER_TITLE, $PDF_HEADER_STRING); 
		$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

		$pdf->SetMargins(15, 14, 15);


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

		$pdf->AddPage('', 'A4');
		$pdf->SetMargins(15, 30, 15);
		$pdf->SetFont('helvetica', '', 8);


		$labels='<br><br><br>';
		$labels .= '<table border="0" cellpadding="1" width="100%" style="font-size:14px">';
		$labels .= '<tr style="text-align:right;font-weight:bold;">';
		$labels .= '<th style="text-align:right">Sales Report</th>';
		$labels .= '</tr>';
		$labels .= '<tr>';
		$labels .= '<th style="text-align:right;font-size:12px">(From: '.date('d-M-Y',strtotime($from_dt)).' To: '.date('d-M-Y',strtotime($to_dt)).')</th>';
		$labels .= '</tr>';
		$labels .= '</table>';


		$description_table = '';
		$description_table .= '<br><br><table border="1" cellpadding="4" width="100%" style="font-size:12px">';
		$description_table .= '<tr style="text-align:center;font-weight:bold;" bgcolor="#efefef" >';
		$description_table .= '<th style="width:10%">SR#</th>';
		$description_table .= '<th style="width:14%">Dated</th>';
		$description_table .= '<th style="width:56%">Job Name</th>';
		$description_table .= '<th style="width:20%">Amount</th>';
		$description_table .= '</tr>';


		$query_cust = "SELECT * FROM customers";
		$result_cust = mysqli_query($conn,$query_cust);
		$total_amt_all = 0;
		$total_jobs = 0;
		$customers_with_sales = 0;
		$professional_detail_rows = '';
		$professional_summary_rows = '';

		$cust_order_summary=array(array());
		$cust_counter = 0;
		while($data_cust = mysqli_fetch_array($result_cust)){
			$cust_id = $data_cust['cust_id'];
			$cust_name = $data_cust['cust_name'];
			$cust_job_total_amt = 0;
			$cust_counter++;

			$description_table .= '<tr>';
			$description_table .= '<td colspan="4" style="color:blue"><b>Customer: </b>'.$cust_name.'</td>';
			$description_table .= '</tr>';	

			$query_job = "SELECT * FROM job_order WHERE order_rec_date >= '".$from_dt."' AND order_rec_date <= '".$to_dt."' AND customer_id = ".$cust_id;
			$result_job = mysqli_query($conn,$query_job);
			$counter = 0;
			if(mysqli_num_rows($result_job)>0){
				$customers_with_sales++;
				$professional_detail_rows .= '<tr bgcolor="#fff1e9"><td colspan="4" style="color:#d95613;font-size:9px;"><b>CUSTOMER: '.htmlspecialchars($cust_name, ENT_QUOTES, 'UTF-8').'</b></td></tr>';
				while($data_job = mysqli_fetch_array($result_job)){
					$counter++;
					$total_jobs++;
					$job_name = $data_job['job_name'];
					$order_rec_date = $data_job['order_rec_date'];
					$total_job_amount = $data_job['total_job_amount'];

					$description_table .= '<tr>';
					$description_table .= '<td style="text-align:center;">'.$counter.'</td>';
					$description_table .= '<td style="text-align:center;">'.date('d-M-Y',strtotime($order_rec_date)).'</td>';
					$description_table .= '<td style="text-align:left;">'.$job_name.'</td>';
					$description_table .= '<td style="text-align:right;">'.number_format($total_job_amount,2).'</td>';
					$description_table .= '</tr>';
					$total_amt_all += $total_job_amount;

					$cust_job_total_amt += $total_job_amount;
					$row_bg = ($counter % 2 === 0) ? '#fafafa' : '#ffffff';
					$professional_detail_rows .= '<tr bgcolor="'.$row_bg.'"><td width="9%" align="center">'.$total_jobs.'</td><td width="18%" align="center">'.date('d M Y',strtotime($order_rec_date)).'</td><td width="51%">'.htmlspecialchars($job_name, ENT_QUOTES, 'UTF-8').'</td><td width="22%" align="right"><b>'.number_format((float)$total_job_amount,2).'</b></td></tr>';
				}
				$professional_detail_rows .= '<tr bgcolor="#f4f4f5"><td colspan="3" align="right" style="font-size:8px;color:#68696f;"><b>CUSTOMER SUBTOTAL</b></td><td align="right" style="color:#303033;"><b>'.number_format((float)$cust_job_total_amt,2).'</b></td></tr>';
			}else{
				$description_table .= '<tr>';
				$description_table .= '<td colspan="3" style="text-align:center;color:red">No Job</td>';
				$description_table .= '<td style="text-align:right;"></td>';

				$description_table .= '</tr>';	
			}

			;

			array_push($cust_order_summary,array($cust_counter,$cust_name,$cust_job_total_amt));

		}

		$description_table .= '<tr>';
		$description_table .= '<td colspan="3" style="text-align:center;color:blue"><b>SUM</b></td>';
		$description_table .= '<td style="text-align:right;">'.number_format($total_amt_all,2).'</td>';
		$description_table .= '</tr>';	

		$description_table .= '</table>';











		$description_table .= '<br><br><br>';
		$description_table .= '<span style="font-size:14px;font-weight:bold;" bgcolor="white">Summary:</span>';
		$description_table .= '<br><br><table border="1" cellpadding="4" width="100%" style="font-size:12px">';
		$description_table .= '<tr style="text-align:center;font-weight:bold;" bgcolor="#efefef" >';
		$description_table .= '<th style="width:10%">SR#</th>';
		$description_table .= '<th style="width:40%">Customer</th>';
		$description_table .= '<th style="width:20%">Amount</th>';
		$description_table .= '</tr>';


		$tota_amt = 0;
		$summary_counter = 0;
		for($i=1; $i<count($cust_order_summary); $i++){
			$description_table .= '<tr>';
			$description_table .= '<td style="text-align:center;">'.$cust_order_summary[$i][0].'</td>';
			$description_table .= '<td style="text-align:center;">'.$cust_order_summary[$i][1].'</td>';
			$description_table .= '<td style="text-align:right;">'.number_format($cust_order_summary[$i][2],2).'</td>';
			$description_table .= '</tr>';

			$tota_amt += $cust_order_summary[$i][2];
			if((float)$cust_order_summary[$i][2] != 0){
				$summary_counter++;
				$professional_summary_rows .= '<tr><td width="12%" align="center">'.$summary_counter.'</td><td width="58%">'.htmlspecialchars($cust_order_summary[$i][1], ENT_QUOTES, 'UTF-8').'</td><td width="30%" align="right"><b>'.number_format((float)$cust_order_summary[$i][2],2).'</b></td></tr>';
			}
		}


		$description_table .= '<tr>';
		$description_table .= '<td colspan="2" style="text-align:center;color:blue"><b>Total</b></td>';
		$description_table .= '<td style="text-align:right;">'.number_format($tota_amt,2).'</td>';
		$description_table .= '</tr>';	

		$description_table .= '</table>';

		$description_table.='<br><br><br><span style="color:blue;font-size:12px"><b>Note:</b></span> <span> This Report used only for Internal Purpose.</span>';


		$sign= '<br><br><br><br><br><br><br>
		<table>
		<tr>
		<td style="text-align:center;color:blue;"><b></b></td>
		<td></td>
		<td style="text-align:center;color:blue;"></td>
		</tr>

		<tr>
		<td style="border-top:1px solid black;text-align:center;"><b>Prepared By</b></td>
		<td></td>
		<td style="border-top:1px solid black;text-align:center;"><b>Checked By</b></td>
		</tr>

		</table>';

		$systemdate=date('Y-m-d H:i:s');
		$para='<br><br><br><br><br><span style="color:black;font-size:12px;text-align:center;">This Report is printed through System at <span style="color:blue;"><strong>'.$systemdate.'</strong></span></span>';


		if($professional_detail_rows === ''){
			$professional_detail_rows = '<tr><td colspan="4" align="center" style="padding:12px;color:#85868b;">No sales transactions were recorded for this period.</td></tr>';
		}
		if($professional_summary_rows === ''){
			$professional_summary_rows = '<tr><td colspan="3" align="center" style="color:#85868b;">No customer sales summary available.</td></tr>';
		}

		$from_display = date('d M Y', strtotime($from_dt));
		$to_display = date('d M Y', strtotime($to_dt));
		$average_job_value = $total_jobs > 0 ? ($total_amt_all / $total_jobs) : 0;
		$printed_at = date('d M Y, h:i A');

		$professional_report = '
		<table width="100%" cellpadding="0" cellspacing="0">
			<tr><td width="4%" bgcolor="#f36b21"></td><td width="58%" style="padding-left:8px;font-size:19px;color:#303033;"><b>SALES REPORT</b><br><span style="font-size:8px;color:#85868b;">Customer sales performance and job revenue</span></td><td width="38%" align="right" style="font-size:8px;color:#77787d;"><b>REPORTING PERIOD</b><br><span style="font-size:11px;color:#303033;"><b>'.$from_display.' - '.$to_display.'</b></span></td></tr>
		</table>
		<table width="100%" cellpadding="5" cellspacing="4" style="font-size:8px;">
			<tr>
				<td width="32%" bgcolor="#f6f6f7" style="border-left:3px solid #f36b21;"><span style="font-size:7px;color:#85868b;"><b>TOTAL SALES</b></span><br><span style="font-size:15px;color:#303033;"><b>PKR '.number_format((float)$total_amt_all,2).'</b></span></td>
				<td width="2%"></td><td width="21%" bgcolor="#f6f6f7" style="border-left:3px solid #f36b21;"><span style="font-size:7px;color:#85868b;"><b>JOBS</b></span><br><span style="font-size:15px;color:#303033;"><b>'.number_format((int)$total_jobs).'</b></span></td>
				<td width="2%"></td><td width="21%" bgcolor="#f6f6f7" style="border-left:3px solid #f36b21;"><span style="font-size:7px;color:#85868b;"><b>CUSTOMERS</b></span><br><span style="font-size:15px;color:#303033;"><b>'.number_format((int)$customers_with_sales).'</b></span></td>
				<td width="2%"></td><td width="20%" bgcolor="#f6f6f7" style="border-left:3px solid #f36b21;"><span style="font-size:7px;color:#85868b;"><b>AVG. JOB VALUE</b></span><br><span style="font-size:13px;color:#303033;"><b>'.number_format((float)$average_job_value,2).'</b></span></td>
			</tr>
		</table>
		<br><span style="font-size:10px;color:#303033;"><b>SALES TRANSACTIONS</b></span><br><br>
		<table width="100%" cellpadding="5" cellspacing="0" style="border:1px solid #dedee1;font-size:8px;">
			<thead><tr bgcolor="#303033" style="color:#ffffff;"><th width="9%" align="center"><b>#</b></th><th width="18%" align="center"><b>DATE</b></th><th width="51%"><b>JOB DESCRIPTION</b></th><th width="22%" align="right"><b>AMOUNT (PKR)</b></th></tr></thead>
			<tbody>'.$professional_detail_rows.'<tr bgcolor="#303033" style="color:#ffffff;"><td colspan="3" align="right"><b>GRAND TOTAL</b></td><td align="right"><b>'.number_format((float)$total_amt_all,2).'</b></td></tr></tbody>
		</table>
		<br><br><span style="font-size:10px;color:#303033;"><b>CUSTOMER SALES SUMMARY</b></span><br><br>
		<table width="100%" cellpadding="5" cellspacing="0" style="border:1px solid #dedee1;font-size:8px;">
			<thead><tr bgcolor="#f36b21" style="color:#ffffff;"><th width="12%" align="center"><b>#</b></th><th width="58%"><b>CUSTOMER</b></th><th width="30%" align="right"><b>SALES AMOUNT (PKR)</b></th></tr></thead>
			<tbody>'.$professional_summary_rows.'<tr bgcolor="#fff1e9"><td colspan="2" align="right"><b>TOTAL SALES</b></td><td align="right" style="color:#d95613;"><b>'.number_format((float)$tota_amt,2).'</b></td></tr></tbody>
		</table>
		<br><br><table width="100%" cellpadding="2" cellspacing="0" style="font-size:8px;color:#4b4c50;"><tr><td width="42%" height="28"></td><td width="16%"></td><td width="42%"></td></tr><tr><td style="border-top:1px solid #66676b;" align="center"><b>Prepared By</b></td><td></td><td style="border-top:1px solid #66676b;" align="center"><b>Checked By</b></td></tr></table>
		<br><table width="100%" cellpadding="4" cellspacing="0" style="font-size:7px;"><tr><td width="72%" bgcolor="#fff6f0" style="color:#7b5d4e;"><b>NOTE:</b> This report is intended for internal business use only.</td><td width="28%" align="right" style="color:#929398;">Generated '.$printed_at.'</td></tr></table>';

		$pdf->SetMargins(15, 32, 15);
		$pdf->SetY(32);
		$pdf->SetFont('helvetica', '', 8);
		$pdf->writeHTML($professional_report, true, false, true, false, '');


		$file_name = 'Sales Report.pdf';
		$pdf->Output($file_name, 'I');
	}else{
		?>
		<h2>Report filters are required.</h2>
		<?php
	}
}
else{
	?>
	<h3>Access denied.</h3>
	<?php
}

?>
