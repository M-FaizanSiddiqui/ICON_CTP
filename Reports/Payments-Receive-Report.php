<?php session_start();

if(in_array("44",$_SESSION['login_Permisions']))
{
	require_once('../tcpdf/tcpdf.php');
	require_once('../includes/pdf_report_helper.php');
	include '../db_connect.php';


	if(isset($_POST['open_rpt'])){
		$from_dt = icon_date_value($_POST['from_date'] ?? '', date('Y-m-d'));
		$to_dt = icon_date_value($_POST['to_date'] ?? '', date('Y-m-d'));

		class MYPDF extends TCPDF
		{
			public function Header() 
			{
				$pdf_report_heading = 'Payment Received Report';

				$this->SetFont('helvetica', 'B', 16);
				$image_file = K_PATH_IMAGES.'logo.jpg';
				$this->Image($image_file, 10, 10, 35, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);

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
 // set document information

		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor('Payment Received Report');
		$pdf->SetTitle('Payment Received Report');
		$pdf->SetSubject('Payment Received Report');
		$pdf->SetKeywords('Payment Received Report');
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
		$labels .= '<th style="text-align:right">Payment Received Report</th>';
		$labels .= '</tr>';
		$labels .= '<tr>';
		$labels .= '<th style="text-align:right;font-size:12px">(From: '.date('d-M-Y',strtotime($from_dt)).' To: '.date('d-M-Y',strtotime($to_dt)).')</th>';
		$labels .= '</tr>';

		$labels .= '</table>';


		$description_table = '';
		$description_table .= '<br><br><table border="1" cellpadding="4" width="100%" style="font-size:11px">';
		$description_table .= '<tr style="text-align:center;font-weight:bold;" bgcolor="#efefef" >';
		$description_table .= '<th style="width:5%">SR#</th>';
		$description_table .= '<th style="width:15%">Payment Date</th>';
		$description_table .= '<th style="width:10%">Payment Mode</th>';
		$description_table .= '<th style="width:15%">Cheque No</th>';
		$description_table .= '<th style="width:15%">Cheque Date</th>';
		$description_table .= '<th style="width:15%">Amount</th>';
		$description_table .= '<th style="width:25%">Remarks</th>';
		$description_table .= '</tr>';

		$query_cust = "SELECT * FROM customers";
		$result_cust = mysqli_query($conn,$query_cust);
		$total_amt_all = 0;

		$cust_order_summary=array(array());
		$cust_counter = 0;
		while($data_cust = mysqli_fetch_array($result_cust)){
			$cust_id = $data_cust['cust_id'];
			$cust_name = $data_cust['cust_name'];
			$cust_job_total_amt = 0;
			$cust_counter++;

			

			$query_job = "SELECT * FROM customer_payment WHERE payment_date >= '".$from_dt."' AND payment_date <= '".$to_dt."' AND customer_id = ".$cust_id." AND pay_status = 0";
			$result_job = mysqli_query($conn,$query_job);
			$counter = 0;
			if(mysqli_num_rows($result_job)>0){
				$description_table .= '<tr>';
				$description_table .= '<td colspan="7" style="color:blue"><b>Customer: </b>'.$cust_name.'</td>';
				$description_table .= '</tr>';

				while($data_job = mysqli_fetch_array($result_job)){
					$counter++;
					$amount = $data_job['amount'];
					$reference = $data_job['reference'];
					$payment_mode = $data_job['payment_mode'];
					$cheque_no = $data_job['cheque_no'];
					$cheque_date = $data_job['cheque_date'];
					$consignee_name = $data_job['consignee_name'];
					$payment_date = $data_job['payment_date'];
					$remarks = $data_job['remarks'];

					if($payment_mode == 1){
						$pay_mode = 'Cash';
						$cheque_no = '-';
						$cheque_date = '-';
					}else{
						$pay_mode = 'Cheque';
						$cheque_date = date('d-M-Y',strtotime($cheque_date));
					}

					$description_table .= '<tr>';
					$description_table .= '<td style="text-align:center;">'.$counter.'</td>';
					$description_table .= '<td style="text-align:center;">'.date('d-M-Y',strtotime($payment_date)).'</td>';
					$description_table .= '<td style="text-align:center;">'.$pay_mode.'</td>';
					$description_table .= '<td style="text-align:center;">'.$cheque_no.'</td>';
					$description_table .= '<td style="text-align:center;">'.$cheque_date.'</td>';
					$description_table .= '<td style="text-align:right;">'.number_format($amount,2).'</td>';
					$description_table .= '<td style="text-align:left;">'.$remarks.'</td>';
					$description_table .= '</tr>';

					$total_amt_all += $amount;
				}
			}
			// else{
			// 	$description_table .= '<tr>';
			// 	$description_table .= '<td colspan="7" style="text-align:center;color:red">No Payment</td>';
			// 	$description_table .= '</tr>';	
			// }

		}



		$description_table .= '<tr>';
		$description_table .= '<td colspan="5" style="text-align:center;color:blue"><b>Total</b></td>';
		$description_table .= '<td style="text-align:right;">'.number_format($total_amt_all,2).'</td>';
		$description_table .= '<td style="text-align:right;"></td>';
		$description_table .= '</tr>';	

		$description_table .= '</table>';

		$description_table.='<br><br><br><span style="color:blue;font-size:12px"><b>Note:</b></span> <span> This Report used only for Internal Purpose.</span>';




		$systemdate=date('Y-m-d H:i:s');
		$para='<br><br><br><br><br><span style="color:black;font-size:12px;text-align:center;">This Report is printed through System at <span style="color:blue;"><strong>'.$systemdate.'</strong></span></span>';


		$pdf->writeHTML($labels, true, 1, true, 1, '');
		$pdf->writeHTML($description_table, true, 1, true, 1, '');
		$pdf->writeHTML($para, true, 1, true, 1, '');

		$file_name = 'Payment Received Report.pdf';
		$pdf->Output($file_name, 'I');
	}
	else{
		?>
		<h3>Invalid Link</h3>
		<?php
	}
}
else{
	?>
	<h3>Invalid Link</h3>
	<?php
}

?>
