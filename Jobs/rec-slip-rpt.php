<?php
require_once(__DIR__.'/../includes/pdf_runtime.php');
require_once('../tcpdf/tcpdf.php');
require_once('../includes/pdf_report_helper.php');
include '../db_connect.php';

if(isset($_GET['ref'])){

	$slip_ref = preg_match('/^[a-f0-9]{32}$/i', $_GET['ref']) ? $_GET['ref'] : '';

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
			
			$this->SetFont('helvetica', 'R', 16);
			$image_file = K_PATH_IMAGES.'logo.jpg';
			$this->Image($image_file, 155, 10, 35, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);

		    $this->writeHTML($labels, true, 1, true, 1, '');

		}
	}



	$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
 	// set document information

	$pdf->SetCreator(PDF_CREATOR);
	$pdf->SetAuthor('Receiving Slip');
	$pdf->SetTitle('Receiving Slip');
	$pdf->SetSubject('Receiving Slip');
	$pdf->SetKeywords('Receiving Slip');
	$PDF_HEADER_LOGO_WIDTH = "20";
	$PDF_HEADER_TITLE = "Receiving Slip";
	$PDF_HEADER_STRING = "Receiving Slip";
	$pdf->SetHeaderData(PDF_HEADER_LOGO, $PDF_HEADER_LOGO_WIDTH, $PDF_HEADER_TITLE, $PDF_HEADER_STRING); 
	$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
	$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
	$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

	$pdf->SetMargins(5, 26, 5);

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

	$query_slip = "SELECT a.*,b.cust_name FROM receiving_slips as a INNER JOIN customers as b on a.customer_id = b.cust_id WHERE md5(a.slip_id) = '".mysqli_real_escape_string($conn, $slip_ref)."'";
	$result_slip = mysqli_query($conn,$query_slip);
	if(mysqli_num_rows($result_slip)>0){
		$data_slip = mysqli_fetch_array($result_slip);
		$slip_id = $data_slip['slip_id'];
		$cust_name = $data_slip['cust_name'];
		$customer_id = $data_slip['customer_id'];
		$rec_date = $data_slip['rec_date'];
		$remarks = $data_slip['remarks'];
		$created_by = $data_slip['created_by'];
		$create_date_time = $data_slip['create_date_time'];

		$labels='<br>';

		$labels.='<table  width="100%">';
		$labels.= '<tr>
		<td style="width:50%">
		<label style="text-align:center">
		<span style="font-size:25px;"><strong>Receiving Slip</strong></span>
		<span>(Office Copy)</span>
		</label>
		</td>
		<td style="width:50%">
		<label style="text-align:center">
		<span style="font-size:25px;"><strong>Receiving Slip</strong></span>
		<span>(Customer Copy)</span>
		</label>

		</td> 
		</tr>
		</table>';

		$labels.='<br><br><table>';
		$labels.='<tr>';

		$labels.='<td style="width:48%">';
		$labels.='<table cellpadding="3" width="100%">';
		$labels.= '<tr>
		<td style="width:50%">
		<label>
		<span style="font-size:12px;"><strong>Slip No:</strong></span>
		</label>
		<span style="color:blue;font-size:12px;">'.$slip_id.'</span>
		</td>
		<td style="width:50%">
		<label style="text-align:right">
		<span style="font-size:12px;"><strong>Dated:</strong> '.date("d-M-Y", strtotime($rec_date)).'</span>
		<span style="color:blue;font-size:12px;"></span>
		</label>
		</td></tr>

		<tr>
		<td style="width:100%">
		<label>
		<span style="font-size:12px;"><strong>Customer Name:</strong></span>
		</label>
		<span style="color:blue;font-size:12px;">'.$cust_name.'</span>
		</td></tr>
		</table>';
		$labels.='</td>';



		$labels.='<td style="width:4%;text-align:center">|<br>|<br>|<br>|</td>';

		$labels.='<td style="width:48%">';
		$labels.='<table cellpadding="3" width="100%">';
		$labels.= '<tr>
		<td style="width:50%">
		<label>
		<span style="font-size:12px;"><strong>Slip No:</strong></span>
		</label>
		<span style="color:blue;font-size:12px;">'.$slip_id.'</span>
		</td>
		<td style="width:50%">
		<label style="text-align:right">
		<span style="font-size:12px;"><strong>Dated:</strong> '.date("d-M-Y", strtotime($rec_date)).'</span>
		<span style="color:blue;font-size:12px;"></span>
		</label>
		</td></tr>
		<tr>
		<td style="width:100%">
		<label>
		<span style="font-size:12px;"><strong>Customer Name:</strong></span>
		</label>
		<span style="color:blue;font-size:12px;">'.$cust_name.'</span>
		</td></tr>
		</table>';
		$labels.='</td>';


		$labels.='</tr>';
		$labels.='</table>';



	

		$description_table = '';
		$description_table .= '';
		$description_table .= '<table>';
		$description_table .= '<tr>';

		$description_table .= '<td style="width:48%">';
	// $description_table .= '<span style="font-size:15px;font-weight:bold;">Plates Details:</span>';
		$description_table .= '<br><table border="1" cellpadding="4" width="100%" style="font-size:10px">';
		$description_table .= '<tr style="text-align:center;font-weight:bold;" bgcolor="#efefef" >';
		$description_table .= '<th style="width:6%">SR#</th>';
		$description_table .= '<th style="width:10%">Job No</th>';
		$description_table .= '<th style="width:55%">Job Name</th>';
		$description_table .= '<th style="width:23%">Item Name</th>';
		$description_table .= '<th style="width:6%">Qty</th>';
		$description_table .= '</tr>';

		$counter = 0;
		$totalQty = 0;
		$data_details = "";
		$professional_rows = "";
		$query_slip_d = "SELECT a.*,b.job_id,b.item_id,b.quantity,c.job_name,d.item_name,d.size_in_mm FROM receiving_slip_details as a INNER JOIN job_order_details as b on a.job_order_detail_id = b.id INNER JOIN job_order as c on b.job_id = c.jd_id INNER JOIN inventory_item as d on b.item_id = d.item_id WHERE a.slip_id = ".$slip_id;
		$result_slip_d = mysqli_query($conn,$query_slip_d);
		if(mysqli_num_rows($result_slip_d)>0){
			while($data_slip_d = mysqli_fetch_array($result_slip_d)){
				$job_id = $data_slip_d['job_id'];
				$item_id = $data_slip_d['item_id'];
				$quantity = $data_slip_d['quantity'];
				$job_name = $data_slip_d['job_name'];
				$item_name = $data_slip_d['item_name'];
				$size_in_mm = $data_slip_d['size_in_mm'];

			 $counter++;
				$totalQty += $quantity;
				$row_background = ($counter % 2 === 0) ? '#fafafa' : '#ffffff';
				$professional_rows .= '<tr style="background-color:'.$row_background.';">';
				$professional_rows .= '<td width="7%" align="center">'.$counter.'</td>';
				$professional_rows .= '<td width="14%" align="center"><b>JB-'.(int)$job_id.'</b></td>';
				$professional_rows .= '<td width="47%">'.htmlspecialchars($job_name, ENT_QUOTES, 'UTF-8').'</td>';
				$professional_rows .= '<td width="24%">'.htmlspecialchars($item_name, ENT_QUOTES, 'UTF-8').' <span style="color:#85868b;">('.htmlspecialchars($size_in_mm, ENT_QUOTES, 'UTF-8').')</span></td>';
				$professional_rows .= '<td width="8%" align="center"><b>'.number_format((float)$quantity, 0).'</b></td>';
				$professional_rows .= '</tr>';
				   
				    $data_details .= '<tr>';
    				$data_details .= '<td style="text-align:center;">'.$counter.'</td>';
    				$data_details .= '<td style="text-align:center;">JB-'.$job_id.'</td>';
    				$data_details .= '<td style="text-align:left;">'.$job_name.'</td>';
    				$data_details .= '<td style="text-align:center;">'.$item_name.' ('.$size_in_mm.')</td>';
    				$data_details .= '<td style="text-align:center;">'.$quantity.'</td>';
    				$data_details .= '</tr>';
							
			}

			$data_details .= '<tr>';
			$data_details .= '<td colspan="4" style="text-align:left;"><b>Total Plates</b></td>';
			$data_details .= '<td style="text-align:center;"><b>'.$totalQty.'</b></td>';
			$data_details .= '</tr>';			

		}
		
		 $sign = '<br><br><br><table>';
// 		if($counter<=9){
// 		    $sign = '<br><br><br><table>';
// 		}else if($counter>=10 && $counter<=14 ){
// 		    $sign = '<br><br><br><br><br><br><br><br><br><table>';
// 		}else{
// 		     $sign = '<br><br><br><table>';
// 		}
		
		$sign .= '<tr>';
		$sign .= '<td style="text-align:center;color:blue;"><b></b></td>';
		$sign .= '<td></td>';
		$sign .= '<td style="text-align:center;color:blue;"></td>';
		$sign .= '</tr>';
		$sign .= '<tr>';
		$sign .= '<td style="border-top:1px solid black;text-align:center;"><b>Signature</b></td>';
		$sign .= '<td></td>';
		$sign .= '<td style="border-top:1px solid black;text-align:center;"><b>Receiver Sign</b></td>';
		$sign .= '</tr>';
		$sign .= '</table>';




		$description_table .= $data_details;


		$description_table .= '</table>';
		$description_table .= '</td>';

		$description_table .= '<td style="width:4%;text-align:center">|<br>|<br>|<br>|<br>|<br>|<br>|<br>|<br>|<br>|<br>|<br>|<br>|<br>|<br>|<br>|<br>|<br>|<br>|<br>|<br>|<br>|<br>|<br>|<br>|<br>|<br>|<br>|<br>|</td>';

		$description_table .= '<td style="width:48%">';
	// $description_table .= '<span style="font-size:15px;font-weight:bold;">Plates Details:</span>';
		$description_table .= '<br><table border="1" cellpadding="4" width="100%" style="font-size:10px">';
		$description_table .= '<tr style="text-align:center;font-weight:bold;" bgcolor="#efefef" >';
		$description_table .= '<th style="width:6%">SR#</th>';
		$description_table .= '<th style="width:10%">Job No</th>';
		$description_table .= '<th style="width:55%">Job Name</th>';
		$description_table .= '<th style="width:23%">Item Name</th>';
		$description_table .= '<th style="width:6%">Qty</th>';
		$description_table .= '</tr>';
		
		

		$description_table .= $data_details;

		$description_table .= '</table>';
		$description_table .= '</td>';

		$description_table .= '</tr>';


		$description_table .= '<tr>';
		$description_table .= '<td>';
		$description_table .= $sign;
		$description_table .= '</td>';

		$description_table .= '<td style="text-align:center">|<br>|<br>|</td>';

		$description_table .= '<td>';
		$description_table .= $sign;
		$description_table .= '</td>';

		$description_table .= '</tr>';






		$description_table .= '</table>';



		$systemdate=date('d-M-Y');
		$paraThis='<span style="color:black;font-size:12px;text-align:center;">This Slip is printed through System at <span style="color:blue;"><strong>'.$systemdate.'</strong></span></span>';

		$para='<br><br><br><table  width="100%">';
		$para.= '<tr>
		<td style="width:50%">
		<label style="text-align:center">
		<span style="font-size:12px;">'.$paraThis.'</strong>
		</label>
		</td>
		<td style="width:50%">
		<label style="text-align:center">
		<span style="font-size:12px;">'.$paraThis.'</span>
		</label>

		</td> 
		</tr>
		</table>';

		$safe_slip_id = htmlspecialchars((string)$slip_id, ENT_QUOTES, 'UTF-8');
		$safe_customer = htmlspecialchars((string)$cust_name, ENT_QUOTES, 'UTF-8');
		$safe_customer_id = htmlspecialchars((string)$customer_id, ENT_QUOTES, 'UTF-8');
		$safe_date = date('d M Y', strtotime($rec_date));
		$safe_remarks = trim((string)$remarks) !== '' ? htmlspecialchars((string)$remarks, ENT_QUOTES, 'UTF-8') : 'No remarks provided.';
		$printed_at = date('d M Y, h:i A');
		$signature_space = ($counter >= 9) ? 10 : (($counter >= 7) ? 22 : 38);
		$printed_note = ($counter >= 9) ? '' : '<tr><td height="7"></td></tr><tr><td align="center" style="font-size:6px;color:#929398;border-top:1px solid #e3e3e5;">System generated on '.$printed_at.'</td></tr>';
		if ($professional_rows === '') {
			$professional_rows = '<tr><td colspan="5" align="center" style="color:#77787d;">No plate details available.</td></tr>';
		}

		$build_copy = function ($copy_label) use ($safe_slip_id, $safe_customer, $safe_customer_id, $safe_date, $safe_remarks, $professional_rows, $totalQty, $signature_space, $printed_note) {
			return '<table width="100%" cellpadding="0" cellspacing="0">
			<tr><td><table width="100%" cellpadding="5" cellspacing="0" style="border-bottom:2px solid #f36b21;"><tr>
			<td width="72%" style="font-size:18px;color:#303033;"><b>RECEIVING SLIP</b><br><span style="font-size:8px;color:#77787d;">PLATE RECEIPT CONFIRMATION</span></td>
			<td width="28%" align="center" bgcolor="#fff4ed" style="border:1px solid #f36b21;font-size:9px;color:#d9530f;"><br><b>'.$copy_label.'</b><br></td>
			</tr></table></td></tr>
			<tr><td height="5"></td></tr>
			<tr><td><table width="100%" cellpadding="6" cellspacing="3" style="font-size:9px;">
			<tr><td width="49%" bgcolor="#f6f6f7" style="border-left:3px solid #f36b21;color:#303033;"><span style="font-size:7px;color:#77787d;"><b>SLIP NUMBER</b></span><br><span style="font-size:11px;"><b>RS-'.$safe_slip_id.'</b></span></td><td width="2%"></td><td width="49%" bgcolor="#f6f6f7" style="border-left:3px solid #f36b21;color:#303033;"><span style="font-size:7px;color:#77787d;"><b>RECEIVING DATE</b></span><br><span style="font-size:11px;"><b>'.$safe_date.'</b></span></td></tr>
			<tr><td colspan="3" bgcolor="#fafafa" style="border:1px solid #e1e1e3;color:#303033;"><span style="font-size:7px;color:#77787d;"><b>CUSTOMER</b></span><br><span style="font-size:10px;"><b>'.$safe_customer.'</b></span> <span style="color:#85868b;">Customer ID: '.$safe_customer_id.'</span></td></tr>
			</table></td></tr>
			<tr><td height="7"></td></tr><tr><td style="font-size:10px;color:#303033;"><b>PLATE DETAILS</b></td></tr><tr><td height="3"></td></tr>
			<tr><td><table width="100%" cellpadding="5" cellspacing="0" style="border:1px solid #dedee1;font-size:8px;">
			<tr bgcolor="#303033" style="color:#ffffff;"><th width="7%" align="center"><b>#</b></th><th width="14%" align="center"><b>JOB</b></th><th width="47%"><b>JOB DESCRIPTION</b></th><th width="24%"><b>ITEM / SIZE</b></th><th width="8%" align="center"><b>QTY</b></th></tr>
			'.$professional_rows.'<tr bgcolor="#fff1e9"><td colspan="4" align="right"><b>TOTAL PLATES</b></td><td align="center" style="color:#f36b21;"><b>'.number_format((float)$totalQty, 0).'</b></td></tr>
			</table></td></tr>
			<tr><td height="6"></td></tr>
			<tr><td><table width="100%" cellpadding="5" cellspacing="0" style="border:1px solid #dedee1;font-size:8px;"><tr><td width="18%" bgcolor="#f4f4f5" style="color:#5f6065;"><b>REMARKS</b></td><td width="82%">'.$safe_remarks.'</td></tr></table></td></tr>
			<tr><td height="'.$signature_space.'"></td></tr>
			<tr><td><table width="100%" cellpadding="3" cellspacing="0" style="font-size:8px;color:#4b4c50;"><tr><td width="44%" style="border-top:1px solid #66676b;" align="center"><b>Prepared By / Signature</b></td><td width="12%"></td><td width="44%" style="border-top:1px solid #66676b;" align="center"><b>Receiver Name / Signature</b></td></tr></table></td></tr>
			'.$printed_note.'
			</table>';
		};

		$separator = '<table width="100%" cellpadding="0" cellspacing="0"><tr><td height="10"></td></tr><tr><td width="48%" height="300"></td><td width="4%" height="300" style="border-left:1px dashed #b8b9bd;"></td><td width="48%" height="300"></td></tr></table>';
		$professional_content = '<table width="100%" cellpadding="0" cellspacing="0"><tr><td width="48.5%" valign="top">'.$build_copy('OFFICE COPY').'</td><td width="3%" valign="top">'.$separator.'</td><td width="48.5%" valign="top">'.$build_copy('CUSTOMER COPY').'</td></tr></table>';
		$pdf->SetY(28);
		$pdf->writeHTML($professional_content, true, false, true, false, '');

		$file_name = 'Receiving Slip.pdf';
		$pdf->Output($file_name, 'I');
	}
	else{
		include '../invalidLink.php';
	}
}
else{
	include '../invalidLink.php';
}
