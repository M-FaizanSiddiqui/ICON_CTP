<?php session_start();

if(in_array("45", isset($_SESSION['login_Permisions']) ? $_SESSION['login_Permisions'] : array()))
{
	require_once('../tcpdf/tcpdf.php');
	require_once('../includes/pdf_report_helper.php');
	include '../db_connect.php';
	if(isset($_POST['open_rpt'])){

		class MYPDF extends TCPDF 
		{
			public function Header() 
			{
				$this->SetFont('helvetica', 'B', 16);
				$image_file = K_PATH_IMAGES.'logo.jpg';
				$this->Image($image_file, 10, 10, 35, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);

				$this->SetFont('helvetica', 'R', 9);

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

		$from_dt = $_POST['from_date'];
		$to_dt = $_POST['to_date'];
		
		$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		 // set document information

		$pdf->SetCreator(PDF_CREATOR);

		if(isset($_POST['customer_id'])){
			$customer_id = $_POST['customer_id'];

			$queryCust = "SELECT * FROM customers where cust_id = ".$customer_id;
			$resultCust = mysqli_query($conn,$queryCust);
			$dataCust = mysqli_fetch_array($resultCust);
			$customerName = $dataCust['cust_name'];

			$pdf->SetAuthor('Bill - '.$customerName);
			$pdf->SetTitle('Bill -'.$customerName);
			$pdf->SetSubject('Bill - '.$customerName);
			$pdf->SetKeywords('Bill - '.$customerName);
		}else{
			$customer_id = 0;

			$pdf->SetAuthor('Bill');
			$pdf->SetTitle('Bill');
			$pdf->SetSubject('Bill');
			$pdf->SetKeywords('Bill');
		}

		
		
		$PDF_HEADER_LOGO_WIDTH = "20";
		$PDF_HEADER_TITLE = "This is my Title";
		$PDF_HEADER_STRING = "This is Header Part";
		$pdf->SetHeaderData(PDF_HEADER_LOGO, $PDF_HEADER_LOGO_WIDTH, $PDF_HEADER_TITLE, $PDF_HEADER_STRING); 
		$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

		$pdf->SetMargins(10, 20, 10);

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
		$pdf->SetMargins(10, 30, 10);
		$pdf->SetFont('helvetica', '', 8.5);


		if($customer_id != 0){
			$fromLabel = date('d M Y', strtotime($from_dt));
			$toLabel = date('d M Y', strtotime($to_dt));
			$description_table = '<br><br><br>';
			$description_table .= '<table border="0" cellpadding="0" cellspacing="0" width="100%">';
			$description_table .= '<tr><td style="width:1%;background-color:#f36b21;"></td><td style="width:99%;background-color:#303033;color:#ffffff;padding:5px;">';
			$description_table .= '<span style="font-size:17px;font-weight:bold;"> MONTHLY BILL</span><br><span style="font-size:9px;color:#d9d9dc;">  Customer billing statement and job activity</span></td></tr></table>';
			$description_table .= '<table border="0" cellpadding="7" cellspacing="0" width="100%">';
			$description_table .= '<tr><td style="width:62%;background-color:#f6f6f7;"><span style="font-size:8px;color:#74747b;font-weight:bold;">BILLED TO</span><br><span style="font-size:13px;color:#202024;font-weight:bold;">'.htmlspecialchars($customerName, ENT_QUOTES, 'UTF-8').'</span></td>';
			$description_table .= '<td style="width:38%;background-color:#fff1e9;text-align:right;"><span style="font-size:8px;color:#a74a18;font-weight:bold;">BILLING PERIOD</span><br><span style="font-size:10px;color:#303033;font-weight:bold;">'.$fromLabel.' - '.$toLabel.'</span></td></tr></table><br>';

			$description_table .= '<table border="0" cellpadding="5" cellspacing="0" width="100%" style="font-size:8px">';
			$description_table .= '<tr style="background-color:#303033;color:#ffffff;font-weight:bold;">';
			$description_table .= '<th style="width:4%;text-align:center;">#</th>';
			$description_table .= '<th style="width:9%;text-align:center;">DATE</th>';
			$description_table .= '<th style="width:8%;text-align:center;">TICKET</th>';
			$description_table .= '<th style="width:31%;">JOB DESCRIPTION</th>';
			$description_table .= '<th style="width:10%;">PRODUCT</th>';
			$description_table .= '<th style="width:9%;text-align:center;">SIZE</th>';
			$description_table .= '<th style="width:7%;text-align:center;">OVEN</th>';
			$description_table .= '<th style="width:5%;text-align:center;">QTY</th>';
			$description_table .= '<th style="width:7%;text-align:right;">RATE</th>';
			$description_table .= '<th style="width:10%;text-align:right;">AMOUNT</th>';
			$description_table .= '</tr>';

			$query_job = "SELECT a.*,b.*,c.item_name,c.size_in_mm FROM job_order as a INNER JOIN job_order_details as b on a.jd_id = b.job_id INNER JOIN inventory_item as c on b.item_id = c.item_id where a.order_rec_date >= '".$from_dt."' AND a.order_rec_date <= '".$to_dt."' AND a.customer_id = ".$customer_id." AND b.delete_status = 0  order by a.order_rec_date ASC ";

			$result_job = mysqli_query($conn,$query_job);

			$total_overall_amount = 0;
			$serial_no = 0;
			$pre_item = 0;

			$cust_prod = array();

			

			$query_inv = "SELECT * FROM inventory_item";
			$result_inv = mysqli_query($conn,$query_inv);
			while($data_inv = mysqli_fetch_array($result_inv)){
				$inv_id = $data_inv['item_id'];
				$inv_name = $data_inv['item_name'];
				$size_in_mm = $data_inv['size_in_mm'];
				array_push($cust_prod,array($inv_id,$inv_name,$size_in_mm,0,0,0));
			}





			while($data_job = mysqli_fetch_array($result_job)){

				$serial_no++;
				$order_rec_date = $data_job['order_rec_date'];
				$job_name = $data_job['job_name'];
				$jd_id = $data_job['jd_id'];
				$item_id = $data_job['item_id'];
				$price = $data_job['price'];
				$quantity = $data_job['quantity'];
				$total_amount = $data_job['total_amount'];
				$item_name = $data_job['item_name'];
				$size_in_mm = $data_job['size_in_mm'];
				$OvenBake_Charges = $data_job['OvenBake_Charges'];

				$OvenBaked = 'No';

				if($OvenBake_Charges>0){
					$OvenBaked = 'Yes';
				}


				$rowColor = ($serial_no % 2 === 0) ? '#f7f7f8' : '#ffffff';
				$description_table .= '<tr nobr="true" style="background-color:'.$rowColor.';color:#303033;">';
				$description_table .= '<td style="text-align:center;">'.$serial_no.'</td>';
				$description_table .= '<td style="text-align:center;">'.date('d M y',strtotime($order_rec_date)).'</td>';
				$description_table .= '<td style="text-align:center;color:#f36b21;font-weight:bold;">JD-'.$jd_id.'</td>';
				$description_table .= '<td>'.htmlspecialchars($job_name, ENT_QUOTES, 'UTF-8').'</td>';
				$description_table .= '<td><b>'.htmlspecialchars($item_name, ENT_QUOTES, 'UTF-8').'</b></td>';
				$description_table .= '<td style="text-align:center">'.$size_in_mm.'</td>';
				$description_table .= '<td style="text-align:center;">'.$OvenBaked.'</td>';
				$description_table .= '<td style="text-align:center;">'.$quantity.'</td>';
				$description_table .= '<td style="text-align:right;">'.number_format($price, 2).'</td>';
				$description_table .= '<td style="text-align:right;"><b>'.number_format($total_amount, 2).'</b></td>';
				$description_table .= '</tr>';

				$total_overall_amount += $total_amount;

				
				$cost=0;
				$OvenBakes = 0;
				$NonOvenBakes = 0;
				for($jv=0; $jv<count($cust_prod); $jv++){
					if($cust_prod[$jv][0] == $item_id){
						$cost = $cust_prod[$jv][3]+$quantity;
						$cust_prod[$jv][3] = $cost;

						if($OvenBake_Charges>0){
							$OvenBakes = $cust_prod[$jv][4] + $quantity;
							$cust_prod[$jv][4] = $OvenBakes;
						}else{
							$NonOvenBakes = $cust_prod[$jv][5] + $quantity; 
							$cust_prod[$jv][5] = $NonOvenBakes;
						}
					}
				}
			}

			// $cust_order_summary=array(array());

			if($total_overall_amount !=0){
				$description_table .= '<tr style="background-color:#fff1e9;color:#303033;">';
				$description_table .= '<td colspan="9" style="text-align:right;font-size:9px;"><b>TOTAL BILL AMOUNT</b></td>';
				$description_table .= '<td style="text-align:right;color:#f36b21;font-size:10px;"><b>'.number_format($total_overall_amount, 2).'</b></td>';
				$description_table .= '</tr>';
				$description_table .= '</table>';
			}else{
				$description_table .= '<tr>';
				$description_table .= '<td colspan="10" style="text-align:center;background-color:#f6f6f7;color:#74747b;padding:16px;"><b>No billing records found for this period.</b><br>Please select another date range.</td>';
				$description_table .= '</tr>';
				$description_table .= '</table>';
			}


			$pdf->writeHTML($description_table, true, 1, true, 1, '');




			$des_tab = '<br><br><table border="0" cellpadding="5" cellspacing="0" width="72%" style="font-size:8px">';
			$des_tab .= '<tr style="background-color:#f36b21;color:#ffffff;font-weight:bold;">';
			$des_tab .= '<th colspan="6" style="font-size:10px;">PRODUCT SUMMARY</th>';
			$des_tab .= '</tr>';
			$des_tab .= '<tr style="background-color:#303033;color:#ffffff;font-weight:bold;">';
			$des_tab .= '<th style="width:8%;text-align:center;">#</th>';
			$des_tab .= '<th style="width:34%;">PRODUCT</th>';
			$des_tab .= '<th style="width:18%;text-align:center;">SIZE</th>';
			$des_tab .= '<th style="width:12%;text-align:center;">QTY</th>';
			$des_tab .= '<th style="width:14%;text-align:center;">OVEN</th>';
			$des_tab .= '<th style="width:14%;text-align:center;">STANDARD</th>';
			$des_tab .= '</tr>';

			$s_no = 0;
			for($jv=0; $jv<count($cust_prod); $jv++){
				if($cust_prod[$jv][3]>0){
					$s_no++;
					$rowColor = ($s_no % 2 === 0) ? '#f7f7f8' : '#ffffff';
					$des_tab .= '<tr nobr="true" style="background-color:'.$rowColor.';">';
					$des_tab .= '<td style="text-align:center;">'.$s_no.'</td>';
					$des_tab .= '<td>'.$cust_prod[$jv][1].'</td>';
					$des_tab .= '<td style="text-align:center;">'.$cust_prod[$jv][2].'</td>';
					$des_tab .= '<td style="text-align:center;">'.$cust_prod[$jv][3].'</td>';
					$des_tab .= '<td style="text-align:center;">'.$cust_prod[$jv][4].'</td>';
					$des_tab .= '<td style="text-align:center;">'.$cust_prod[$jv][5].'</td>';
					$des_tab .= '</tr>';
				}
			}
			if($s_no === 0){
				$des_tab .= '<tr><td colspan="6" style="text-align:center;background-color:#f6f6f7;color:#74747b;">No product activity</td></tr>';
			}
			
			$des_tab .= '</table>';
			$pdf->writeHTML($des_tab, true, 1, true, 1, '');
			
		}		

		$description_table='<br><br><table border="0" cellpadding="7" width="100%"><tr><td style="background-color:#f6f6f7;color:#66666d;font-size:8px;"><span style="color:#f36b21;font-weight:bold;">NOTE</span><br>This report is intended for internal use only.</td></tr></table>';

		$pdf->writeHTML($description_table, true, 1, true, 1, '');


		$systemdate=date('Y-m-d H:i:s');
		$para='<br><br><div style="color:#8a8a90;font-size:8px;text-align:center;">System generated on <strong style="color:#303033;">'.$systemdate.'</strong></div>';


		
		$pdf->writeHTML($para, true, 1, true, 1, '');

		if($customer_id != 0){
			$file_name = 'Billing ('.$customerName.').pdf';
		}else{
			$file_name = 'All Bills.pdf';
		}
		
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
