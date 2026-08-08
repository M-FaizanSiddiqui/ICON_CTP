<?php session_start();

if(in_array("45",$_SESSION['login_Permisions']))
{
	require_once('../tcpdf/tcpdf.php');
	include '../db_connect.php';

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


	$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
	// set document information
	$pdf->SetCreator(PDF_CREATOR);


	$pdf->SetAuthor('Bill');
	$pdf->SetTitle('Bill');
	$pdf->SetSubject('Bill');
	$pdf->SetKeywords('Bill');
	$PDF_HEADER_LOGO_WIDTH = "20";
	$PDF_HEADER_TITLE = "This is my Title";
	$PDF_HEADER_STRING = "This is Header Part";
	$pdf->SetHeaderData(PDF_HEADER_LOGO, $PDF_HEADER_LOGO_WIDTH, $PDF_HEADER_TITLE, $PDF_HEADER_STRING); 
	$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
	$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
	$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

	$pdf->SetMargins(8, 14, 8);

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

	$pdf->AddPage('L', 'A5');
	$pdf->SetMargins(8, 25, 8);
	$pdf->SetFont('helvetica', '', 8);


	if(isset($_GET['ref'])){
		$ref = icon_md5_ref($_GET['ref']);
		
		$fetchquery = "SELECT a.voucher_no,a.v_type_id,a.account_id as deb_acc,b.account_id as cred_acc,a.trans_dated,a.debit_amount,b.credit_amount,d.acc_name  as deb_acc_name, e.acc_name as cred_acc_name,a.narration FROM vouchers as a INNER JOIN vouchers as b on (a.voucher_no=b.voucher_no and b.credit_amount>0 AND a.v_type_id = b.v_type_id) LEFT JOIN accounts as d on a.account_id = d.account_no LEFT JOIN accounts as e on b.account_id = e.account_no WHERE a.cancel_flag = 0 AND a.v_type_id = 5 AND md5(a.voucher_no) = '".$ref."'";
		$runQuery = mysqli_query($conn,$fetchquery);
		$dataFetch = mysqli_fetch_array($runQuery);
		$voucher_no = $dataFetch['voucher_no'];
		$trans_dated = $dataFetch['trans_dated'];
		$debit_amount = $dataFetch['debit_amount'];
		$credit_amount = $dataFetch['credit_amount'];
		$deb_acc = $dataFetch['deb_acc'];
		$cred_acc = $dataFetch['cred_acc'];
		$v_type_id = $dataFetch['v_type_id'];
		$deb_acc_name = $dataFetch['deb_acc_name'];
		$cred_acc_name = $dataFetch['cred_acc_name'];
		$narration = $dataFetch['narration'];
	}

	$description_table = '<br><br><br>';
	$description_table .= '<table border="0" cellpadding="1" width="100%" style="font-size:14px">';

	$description_table .= '<tr style="font-weight:bold;">';
	$description_table .= '<th style="width:73%"></th>';
	$description_table .= '<th style="width:27%;text-align:right"><b>Journal Voucher</b></th>';
	$description_table .= '</tr>';

	$description_table .= '</table>';

	$labels = '<table border="0" cellpadding="1" width="100%" style="font-size:12px">';
	$labels .= '<tr>';
	$labels .= '<th><span style="font-weight:bold;">Voucher No:</span> <span>JV-'.$voucher_no.'</span></th>';
	$labels .= '</tr>';
	$labels .= '<tr>';
	$labels .= '<th><span style="font-weight:bold;">Trans Date:</span> <span>'.date('d-M-Y',strtotime($trans_dated)).'</span></th>';
	$labels .= '</tr>';

	$labels .= '</table>';

	$pdf->writeHTML($description_table, true, 1, true, 1, '');
	$pdf->writeHTML($labels, true, 1, true, 1, '');

	$des_tab = '<br><br><table border="1" cellpadding="2" width="100%" style="font-size:10px">';

	$des_tab .= '<tr style="text-align:center;font-weight:bold;" bgcolor="#ff9f51">';
	$des_tab .= '<th colspan="5" style="width:100%" bgcolor="#ff9f51">Voucher Details</th>';
	$des_tab .= '</tr>';

	$des_tab .= '<tr style="text-align:center;font-weight:bold;" bgcolor="#efefef">';
	$des_tab .= '<th style="width:5%;text-align:center;">SR#</th>';
	$des_tab .= '<th style="width:15%;text-align:center;">Account No</th>';
	$des_tab .= '<th style="width:56%;text-align:center;">Account Name</th>';
	$des_tab .= '<th style="width:12%;text-align:center;">Debit</th>';
	$des_tab .= '<th style="width:12%;text-align:center;">Credit</th>';
	$des_tab .= '</tr>';


	$deb_amt_total = 0;
	$cred_amt_total = 0;
	$s_no = 0;
	$fetchquery = "SELECT a.voucher_no,a.v_type_id,a.account_id, a.trans_dated,a.debit_amount,a.credit_amount, d.acc_name FROM vouchers as a LEFT JOIN accounts as d on a.account_id = d.account_no WHERE a.cancel_flag = 0 AND md5(a.voucher_no) = '".$ref."' AND a.v_type_id = 5";
	$runQuery = mysqli_query($conn,$fetchquery);
	while($dataFetch = mysqli_fetch_array($runQuery)){
		$voucher_no = $dataFetch['voucher_no'];
		$trans_dated = $dataFetch['trans_dated'];
		$debit_amount = $dataFetch['debit_amount'];
		$credit_amount = $dataFetch['credit_amount'];
		$account_id = $dataFetch['account_id'];
		$v_type_id = $dataFetch['v_type_id'];
		$acc_name = $dataFetch['acc_name'];

		$s_no++;
		$des_tab .= '<tr nobr="true">';
		$des_tab .= '<td style="text-align:center;">'.$s_no.'</td>';
		$des_tab .= '<td style="text-align:center;">'.$account_id.'</td>';
		$des_tab .= '<td style="text-align:left;">'.$acc_name.'</td>';
		$des_tab .= '<td style="text-align:right;">'.number_format($debit_amount).'</td>';
		$des_tab .= '<td style="text-align:right;">'.number_format($credit_amount).'</td>';
		$des_tab .= '</tr>';

		$deb_amt_total += $debit_amount;
		$cred_amt_total += $credit_amount;
	}

	$des_tab .= '<tr nobr="true">';
	$des_tab .= '<td colspan="3" style="text-align:right;"><b>Total</b></td>';
	$des_tab .= '<td style="text-align:right;"><b>'.number_format($deb_amt_total).'</b></td>';
	$des_tab .= '<td style="text-align:right;"><b>'.number_format($cred_amt_total).'</b></td>';
	$des_tab .= '</tr>';



	$des_tab .= '</table>';
	$pdf->writeHTML($des_tab, true, 1, true, 1, '');


	$description_table='<br><br><br><span style="color:blue;font-size:12px"><b>Narration:</b></span> <span> '.$narration.'</span>';

	// $description_table.='.<br><br><br><span style="color:blue;font-size:12px"><b>Note:</b></span> <span> This Report used only for Internal Purpose.</span>';

	$pdf->writeHTML($description_table, true, 1, true, 1, '');


	$systemdate=date('d-M-Y');
	$para='<br><br><br><br><br><br><br><br><br><br><span style="color:black;font-size:12px;text-align:center;">This Voucher is printed through System at <span style="color:blue;"><strong>'.$systemdate.'</strong></span></span>';



	$pdf->writeHTML($para, true, 1, true, 1, '');



	$file_name = 'All Bills.pdf';
	$pdf->Output($file_name, 'I');

	
}else{
	?>
	<h2>Invalid Link</h2>
	<?php
}
?>
