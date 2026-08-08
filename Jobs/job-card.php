<?php
require_once(__DIR__.'/../includes/pdf_runtime.php');
require_once('../tcpdf/tcpdf.php');
require_once('../includes/pdf_report_helper.php');

include '../db_connect.php';

if(isset($_GET['ref'])){

	$job_id = (int)$_GET['ref'];

	class MYPDF extends TCPDF 
	{
		public function Header() 
		{
			$pdf_report_heading = 'Job Card';

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
		}
	}



	$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
 // set document information

	$pdf->SetCreator(PDF_CREATOR);
	$pdf->SetAuthor('Job Card');
	$pdf->SetTitle('Job Card');
	$pdf->SetSubject('Job Card');
	$pdf->SetKeywords('Job Card');
	$PDF_HEADER_LOGO_WIDTH = "20";
	$PDF_HEADER_TITLE = "This is my Title";
	$PDF_HEADER_STRING = "This is Header Part";
	$pdf->SetHeaderData(PDF_HEADER_LOGO, $PDF_HEADER_LOGO_WIDTH, $PDF_HEADER_TITLE, $PDF_HEADER_STRING); 
	$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
	$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
	$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

	$pdf->SetMargins(15, 32, 15);

	$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
	$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);


	$bMargin = $pdf->getBreakMargin();
	$auto_page_break = $pdf->getAutoPageBreak();
	$pdf->SetAutoPageBreak(true, 12);
	$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
	if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
		require_once(dirname(__FILE__).'/lang/eng.php');
		$pdf->setLanguageArray($l);
	}

	$pdf->AddPage('L', 'A5');
	$pdf->SetFont('helvetica', '', 8);

	$query_job = "SELECT a.*,b.cust_name FROM job_order as a INNER JOIN customers as b on a.customer_id = b.cust_id where a.jd_id = ".$job_id;
	$result_job = mysqli_query($conn,$query_job);
	$data_job = mysqli_fetch_array($result_job);
	$job_name = $data_job['job_name'];
	$job_description = $data_job['job_description'];
	$order_rec_date = $data_job['order_rec_date'];
	$total_job_amount = $data_job['total_job_amount'];
	$cust_name = $data_job['cust_name'];
	$customer_id = $data_job['customer_id'];


	$labels='<br>';
	$labels.='<table cellpadding="3" width="100%">';
	$labels.= '<tr>
	<td>
	<label>
	<span style="font-size:12px;"><strong>Job Card #:</strong></span>
	</label>
	<span style="color:blue;font-size:12px;">JB - '.$job_id.'</span>
	</td>
	<td>
	<label style="text-align:right">
	<span style="font-size:12px;"><strong>Dated:</strong> '.date("d-M-Y", strtotime($order_rec_date)).'</span>
	<span style="color:blue;font-size:12px;"></span>
	</label>

	</td> 
	</tr>
	</table>';


	$description_table = '<br>';
	$description_table .= '<span style="font-size:13px;font-weight:bold;">Job Details:</span>';
	$description_table .= '<br><div border="1">';
	$description_table .= '<table cellpadding="3" width="100%" style="font-size:11px">';

	$description_table .= '<tr>';
	$description_table .= '<td colspan="2"><b>Job Name:</b> <span style="color:blue">'.$job_name.'</span> </td>';
	$description_table .= '</tr>';


	$description_table .= '<tr>';
	$description_table .= '<td colspan="2"><b>Job Description:</b> <span style="color:blue">'.$job_description.'</span> </td>';
	$description_table .= '</tr>';

	$description_table .= '<tr>';
	$description_table .= '<td><b>Customer Code:</b> <span style="color:blue">CT - '.$customer_id.'</span> </td>';
	$description_table .= '<td><b>Customer Name:</b> <span style="color:blue">'.$cust_name.'</span> </td>';
	$description_table .= '</tr>';

	$description_table .= '<tr>';
	$description_table .= '<td><b>Order Date:</b> <span style="color:blue">'.date('d-M-Y',strtotime($order_rec_date)).'</span> </td>';
	$description_table .= '<td><b>Total Amount:</b> <span style="color:blue">Rs. '.number_format($total_job_amount).'/=</span> </td>';
	$description_table .= '</tr>';

	$description_table .= '</table>';
	$description_table .= '</div>';


	$description_table .= '<br>';
	$description_table .= '<span style="font-size:13px;font-weight:bold;">Plates Details:</span>';
	$description_table .= '<br><br><table border="1" cellpadding="4" width="100%" style="font-size:11px">';
	$description_table .= '<tr style="text-align:center;font-weight:bold;" bgcolor="#efefef" >';
	$description_table .= '<th style="width:7%">SR#</th>';
	$description_table .= '<th style="width:10%">Item Code</th>';
	$description_table .= '<th style="width:25%">Item Name</th>';
	$description_table .= '<th style="width:15%">Rate</th>';
	$description_table .= '<th style="width:13%">Quantity</th>';
	$description_table .= '<th style="width:30%">Total Amount</th>';
	$description_table .= '</tr>';

	$query_job_details = "SELECT a.*,b.item_name FROM job_order_details as a INNER JOIN inventory_item as b on a.item_id = b.item_id where a.job_id = ".$job_id;
	$result_job_details = mysqli_query($conn,$query_job_details);
	$counter = 0;
	$sum_amount = 0;
	$bar_code_data = "";
	$plate_rows = "";
	while($data_job_details = mysqli_fetch_array($result_job_details))
	{
		$item_id = $data_job_details['item_id'];
		$price = $data_job_details['price'];
		$quantity = $data_job_details['quantity'];
		$total_amount = $data_job_details['total_amount'];
		$item_name = $data_job_details['item_name'];

		$counter++;
		$row_background = ($counter % 2 === 0) ? '#fafafa' : '#ffffff';
		$plate_rows .= '<tr style="background-color:'.$row_background.';">';
		$plate_rows .= '<td width="7%" align="center">'.$counter.'</td>';
		$plate_rows .= '<td width="13%" align="center">IT-'.(int)$item_id.'</td>';
		$plate_rows .= '<td width="32%">'.htmlspecialchars($item_name, ENT_QUOTES, 'UTF-8').'</td>';
		$plate_rows .= '<td width="15%" align="right">'.number_format((float)$price, 2).'</td>';
		$plate_rows .= '<td width="13%" align="center">'.number_format((float)$quantity, 0).'</td>';
		$plate_rows .= '<td width="20%" align="right"><b>'.number_format((float)$total_amount, 2).'</b></td>';
		$plate_rows .= '</tr>';
		$description_table .= '<tr>';
		$description_table .= '<td style="text-align:center;">'.$counter.'</td>';
		$description_table .= '<td style="text-align:center;">IT-'.$item_id.'</td>';
		$description_table .= '<td style="text-align:center;">'.$item_name.'</td>';
		$description_table .= '<td style="text-align:center;">'.$price.'</td>';
		$description_table .= '<td style="text-align:center;">'.$quantity.'</td>';
		$description_table .= '<td style="text-align:right;">'.number_format($total_amount,2).'</td>';
		$description_table .= '<td></td>';
		$description_table .= '</tr>';

		$bar_code_data .= $item_id.":".$quantity.'|';

		$sum_amount += $total_amount;
	}
	$bar_code_data = trim($bar_code_data,'|');

	$description_table .= '<tr>';
	$description_table .= '<td colspan="5" style="text-align:center;color:blue"><b>Total</b></td>';
	$description_table .= '<td style="text-align:right;color:blue">'.number_format($sum_amount,2).'</td>';
	$description_table .= '<td></td>';
	$description_table .= '</tr>';	


	$description_table .= '</table>';

// $description_table.='<br><br><br><span style="color:blue;font-size:12px"><b>Note:</b></span> <span> This Job Card used only for Internal Purpose.</span>';




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
	$para='<br><br><br><br><br><span style="color:black;font-size:12px;text-align:center;">This Job Card is printed through System at <span style="color:blue;"><strong>'.$systemdate.'</strong></span></span>';


	$style = array(
		'position' => 'R',
		'border' => 1,
		'vpadding' => 'auto',
		'hpadding' => 'auto',
		'fgcolor' => array(0,0,0),
		'bgcolor' => false,
		'module_width' => 1,
		'module_height' => 1
	);
	$bar_code_details = "JB-".$job_id.' - ['.$bar_code_data.']';
	$pdf->SetDrawColor(0, 0, 0);
	$pdf->write2DBarcode($bar_code_details, 'QRCODE,H', 20, 10, 20, 20, $style, 'N');

	$safe_job_name = htmlspecialchars($job_name, ENT_QUOTES, 'UTF-8');
	$safe_job_description = htmlspecialchars($job_description, ENT_QUOTES, 'UTF-8');
	$safe_customer_name = htmlspecialchars($cust_name, ENT_QUOTES, 'UTF-8');
	$printed_at = date('d-M-Y h:i A');

	$professional_content = '
	<table cellpadding="0" cellspacing="0" width="100%">
		<tr>
			<td width="72%" style="font-size:16px;font-weight:bold;color:#303033;">JOB CARD <span style="color:#f36b21;"># JB-'.$job_id.'</span></td>
			<td width="28%" align="right" style="font-size:9px;color:#77777c;"><b>ORDER DATE</b><br><span style="font-size:12px;color:#303033;">'.date('d-M-Y', strtotime($order_rec_date)).'</span></td>
		</tr>
	</table>
	<table cellpadding="0" cellspacing="0" width="100%"><tr><td height="4" style="border-bottom:2px solid #f36b21;"></td></tr></table>
	<br>
	<table cellpadding="4" cellspacing="0" width="100%" style="border:1px solid #dddddf;font-size:8.5px;">
		<tr style="background-color:#f5f5f6;">
			<td width="50%"><span style="color:#77777c;font-size:7px;"><b>JOB NAME</b></span><br><span style="color:#303033;font-size:10px;"><b>'.$safe_job_name.'</b></span></td>
			<td width="25%"><span style="color:#77777c;font-size:7px;"><b>CUSTOMER CODE</b></span><br><span style="color:#303033;font-size:9px;">CT-'.(int)$customer_id.'</span></td>
			<td width="25%"><span style="color:#77777c;font-size:7px;"><b>JOB VALUE</b></span><br><span style="color:#f36b21;font-size:10px;"><b>Rs. '.number_format((float)$total_job_amount, 2).'</b></span></td>
		</tr>
		<tr>
			<td width="50%"><span style="color:#77777c;font-size:7px;"><b>DESCRIPTION</b></span><br><span style="color:#303033;">'.$safe_job_description.'</span></td>
			<td width="50%" colspan="2"><span style="color:#77777c;font-size:7px;"><b>CUSTOMER</b></span><br><span style="color:#303033;font-size:9px;"><b>'.$safe_customer_name.'</b></span></td>
		</tr>
	</table>
	<br>
	<table cellpadding="2" cellspacing="0" width="100%">
		<tr><td style="font-size:10px;font-weight:bold;color:#303033;">PLATE DETAILS <span style="font-size:7px;color:#929399;font-weight:normal;">('.$counter.' line items)</span></td></tr>
	</table>
	<table border="1" cellpadding="3" cellspacing="0" width="100%" style="border-color:#dddddf;font-size:8px;">
		<thead>
			<tr style="background-color:#303033;color:#ffffff;font-weight:bold;">
				<th width="7%" align="center">SR#</th>
				<th width="13%" align="center">ITEM CODE</th>
				<th width="32%">ITEM / PLATE</th>
				<th width="15%" align="right">RATE</th>
				<th width="13%" align="center">QTY</th>
				<th width="20%" align="right">AMOUNT</th>
			</tr>
		</thead>
		<tbody>'.$plate_rows.'</tbody>
		<tr style="background-color:#fff1e8;color:#303033;font-size:9px;">
			<td colspan="5" width="80%" align="right"><b>PLATE TOTAL</b></td>
			<td width="20%" align="right" style="color:#e55d17;"><b>Rs. '.number_format((float)$sum_amount, 2).'</b></td>
		</tr>
	</table>
	<br>
	<table cellpadding="2" cellspacing="0" width="100%" style="font-size:7px;color:#8b8c91;">
		<tr>
			<td width="68%">Internal production document - verify plate specifications before processing.</td>
			<td width="32%" align="right">Printed '.$printed_at.'</td>
		</tr>
	</table>';

	$pdf->SetY(31);
	$pdf->SetFont('helvetica', '', 8);
	$pdf->writeHTML($professional_content, true, false, true, false, '');

	$file_name = 'Job Card.pdf';
	$pdf->Output($file_name, 'I');
	
}
else{
	include '../invalidLink.php';
}
