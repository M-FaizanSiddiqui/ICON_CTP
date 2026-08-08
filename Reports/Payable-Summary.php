<?php session_start();

if(in_array("45",$_SESSION['login_Permisions']))
{
	require_once('../tcpdf/tcpdf.php');
	require_once('../includes/pdf_report_helper.php');

	include '../db_connect.php';

	if(isset($_POST['open_rpt'])){

		class MYPDF extends TCPDF 
		{
			public function Header() 
			{
				$pdf_report_heading = 'Payable Summary';

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
 // set document information

		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor('Payable Summary');
		$pdf->SetTitle('Payable Summary');
		$pdf->SetSubject('Payable Summary');
		$pdf->SetKeywords('Payable Summary');
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
		$labels .= '<th style="text-align:right">Payment Payable Summary</th>';
		$labels .= '</tr>';
		$labels .= '<tr>';
		$labels .= '<th style="text-align:right;font-size:12px"></th>';
		$labels .= '</tr>';

		$labels .= '</table>';


		$description_table = '';
		$description_table .= '<table border="1" cellpadding="4" width="100%" style="font-size:12px">';
		$description_table .= '<tr style="text-align:center;font-weight:bold;" bgcolor="#efefef" >';
		$description_table .= '<th style="width:10%">SR#</th>';
		$description_table .= '<th style="width:15%">Supplier Code</th>';
		$description_table .= '<th style="width:55%">Supplier Name</th>';
		$description_table .= '<th style="width:20%">Amount Payable</th>';
		$description_table .= '</tr>';

		$query_supp = "SELECT * FROM suppliers";
		$result_supp = mysqli_query($conn,$query_supp);
		$total_amt_all = 0;

		$cust_order_summary=array(array());
		$supp_counter = 0;
		$tota_amount = 0;
		while($data_supp = mysqli_fetch_array($result_supp)){
			$supp_id = $data_supp['supp_id'];
			$supp_name = $data_supp['supp_name'];
			$supp_counter++;
			$payable_amt = 0;

			$query_sum_amount = "SELECT SUM(amount) as total_amount_ordered FROM inventoty_received_details WHERE supplier_id =".$supp_id;
			$result_sum_amount = mysqli_query($conn,$query_sum_amount);
			if(mysqli_num_rows($result_sum_amount)>0){
				$data_sum_amount = mysqli_fetch_array($result_sum_amount);
				$total_amount_ordered = $data_sum_amount['total_amount_ordered'];
			}else{
				$total_amount_ordered = 0;
			}
			


			$query_amt_p = "SELECT SUM(amount) as total_amount_paid FROM supplier_payment WHERE supplier_id =".$supp_id;
			$result_amt_p = mysqli_query($conn,$query_amt_p);
			if(mysqli_num_rows($result_amt_p)>0){
				$data_amt_p = mysqli_fetch_array($result_amt_p);
				$total_amount_paid = $data_amt_p['total_amount_paid'];
			}else{
				$total_amount_paid = 0;
			}
			

			$payable_amt = $total_amount_ordered - $total_amount_paid;

            if($payable_amt != 0){
            	$description_table .= '<tr>';
    			$description_table .= '<td style="text-align:center;">'.$supp_counter.'</td>';
    			$description_table .= '<td style="text-align:center;">SP - '.$supp_id.'</td>';
    			$description_table .= '<td>'.$supp_name.'</td>';
    			$description_table .= '<td style="text-align:right;">'.number_format($payable_amt).'</td>';
    			$description_table .= '</tr>';	
    
    			$tota_amount += $payable_amt; 
            }
        }


		$description_table .= '<tr>';
		$description_table .= '<td colspan="3" style="text-align:center;color:blue"><b>Total</b></td>';
		$description_table .= '<td style="text-align:right;">'.number_format($tota_amount,2).'</td>';
		$description_table .= '</tr>';	

		$description_table .= '</table>';

		$description_table.='<br><br><br><span style="color:blue;font-size:12px"><b>Note:</b></span> <span> This Report used only for Internal Purpose.</span>';


		$systemdate=date('Y-m-d H:i:s');
		$para='<br><br><br><br><br><span style="color:black;font-size:12px;text-align:center;">This Report is printed through System at <span style="color:blue;"><strong>'.$systemdate.'</strong></span></span>';


		$pdf->writeHTML($labels, true, 1, true, 1, '');
		$pdf->writeHTML($description_table, true, 1, true, 1, '');
		$pdf->writeHTML($para, true, 1, true, 1, '');

		$file_name = 'Payable Summary.pdf';
		$pdf->Output($file_name, 'I');

	}else{
		?>
		<h2>Invalid Link</h2>
		<?php
	}
}else{
	?>
	<h2>Invalid Link</h2>
	<?php
}
?>
