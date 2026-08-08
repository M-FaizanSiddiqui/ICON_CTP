<?php require_once(__DIR__.'/../includes/pdf_runtime.php'); icon_pdf_session_start();

// if(in_array("44",$_SESSION['login_Permisions']))
// {
require_once('../tcpdf/tcpdf.php');
require_once('../includes/pdf_report_helper.php');
include '../db_connect.php';


// if(isset($_POST['open_rpt'])){
// $from_dt = $_POST['from_date'];
// $to_dt = $_POST['to_date'];
$from_dt = '2023-06-01';
$to_dt = '2024-03-11';

class MYPDF extends TCPDF
{
	public function Header() 
	{
		$pdf_report_heading = 'Inventory Purchase Report';

		$this->SetFont('helvetica', '', 16);
		$image_file = K_PATH_IMAGES.'logo.jpg';
		$this->Image($image_file, 10, 10, 35, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);

		$labels = '<table border="0" cellpadding="1" width="100%" style="font-size:10px">';
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
$pdf->SetAuthor('Inventory Purchase Report');
$pdf->SetTitle('Inventory Purchase Report');
$pdf->SetSubject('Inventory Purchase Report');
$pdf->SetKeywords('Inventory Purchase Report');
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
$labels .= '<th style="text-align:right">Inventory Purchase Report</th>';
$labels .= '</tr>';
$labels .= '<tr>';
$labels .= '<th style="text-align:right;font-size:12px">(From: '.date('d-M-Y',strtotime($from_dt)).' To: '.date('d-M-Y',strtotime($to_dt)).')</th>';
$labels .= '</tr>';

$labels .= '</table>';


$description_table = '';
$description_table .= '<br><br><table border="1" cellpadding="4" width="100%" style="font-size:11px">';
$description_table .= '<tr style="text-align:center;font-weight:bold;" bgcolor="#efefef" >';
$description_table .= '<th style="width:5%">SR#</th>';
$description_table .= '<th style="width:15%">Purchase Date</th>';
$description_table .= '<th style="width:33%">Supplier</th>';
$description_table .= '<th style="width:15%">Quantity</th>';
$description_table .= '<th style="width:15%">Rate</th>';
$description_table .= '<th style="width:15%">Amount</th>';
$description_table .= '</tr>';

$query_item = "SELECT * FROM inventory_item";
$result_item = mysqli_query($conn,$query_item);
$total_amt_all = 0;

$cust_counter = 0;
while($data_item = mysqli_fetch_array($result_item)){
	$item_id = $data_item['item_id'];
	$item_name = $data_item['item_name'];
	$size_in_mm = $data_item['size_in_mm'];
	$cust_job_total_amt = 0;
	$cust_counter++;



	$query_job = "SELECT a.*,b.supp_name FROM inventory_received as ad INNER JOIN inventoty_received_details as a ON ad.ir_id = a.ir_id INNER JOIN suppliers as b on a.supplier_id = b.supp_id WHERE a.received_date >= '".$from_dt."' AND a.received_date <= '".$to_dt."' AND a.item_id = ".$item_id." AND a.status = 0 order by a.received_date";
	$result_job = mysqli_query($conn,$query_job);
	$counter = 0;
	if(mysqli_num_rows($result_job)>0){
		$description_table .= '<tr>';
		$description_table .= '<td colspan="7" style="color:blue;font-size:16px;"><b>Plate: </b>'.$item_id.' - '.$item_name.' ('.$size_in_mm.')</td>';
		$description_table .= '</tr>';

		$tot_amt_this = 0;
		$tot_qty_this = 0;
		while($data_job = mysqli_fetch_array($result_job)){
			$counter++;
			$supp_name = $data_job['supp_name'];
			$received_date = $data_job['received_date'];
			$quantity = $data_job['quantity'];
			$rate = $data_job['rate'];
			$amount = $data_job['amount'];
			

			$description_table .= '<tr>';
			$description_table .= '<td style="text-align:center;">'.$counter.'</td>';
			$description_table .= '<td style="text-align:center;">'.date('d-M-Y',strtotime($received_date)).'</td>';
			$description_table .= '<td style="text-align:left;">'.$supp_name.'</td>';
			$description_table .= '<td style="text-align:center;">'.$quantity.'</td>';
			$description_table .= '<td style="text-align:right;">'.number_format($rate).'</td>';
			$description_table .= '<td style="text-align:right;">'.number_format($amount).'</td>';
			$description_table .= '</tr>';

			$total_amt_all += $amount;
			$tot_amt_this += $amount;
			$tot_qty_this += $quantity;
		}
		$description_table .= '<tr>';
		$description_table .= '<td colspan="3" style="text-align:center;color:blue"><b>Total</b></td>';
		$description_table .= '<td style="text-align:center;">'.$tot_qty_this.'</td>';
		$description_table .= '<td style="text-align:right;"></td>';
		$description_table .= '<td style="text-align:right;"><b>'.number_format($tot_amt_this,2).'</b></td>';
		$description_table .= '</tr>';	
	}
}



$description_table .= '<tr>';
$description_table .= '<td colspan="5" style="text-align:center;color:blue"><b>Overall Total</b></td>';
$description_table .= '<td style="text-align:right;"><b>'.number_format($total_amt_all,2).'</b></td>';
$description_table .= '</tr>';	

$description_table .= '</table>';

// $description_table.='<br><br><br><span style="color:blue;font-size:12px"><b>Note:</b></span> <span> This Report used only for Internal Purpose.</span>';












// $description_table = '';
$description_table .= '<br><br><br><br><table border="1" cellpadding="4" width="70%" style="font-size:11px">';
$description_table .= '<tr style="text-align:center;font-weight:bold;" bgcolor="#efefef" >';
$description_table .= '<th colspan="5">Summary</th>';
$description_table .= '</tr>';

$description_table .= '<tr style="text-align:center;font-weight:bold;" bgcolor="#efefef" >';
$description_table .= '<th style="width:10%">SR#</th>';
$description_table .= '<th style="width:15%">Plate Code</th>';
$description_table .= '<th style="width:40%">Plate</th>';
$description_table .= '<th style="width:15%">Quantity</th>';
$description_table .= '<th style="width:20%">Amount</th>';
$description_table .= '</tr>';

$query_item = "SELECT * FROM inventory_item";
$result_item = mysqli_query($conn,$query_item);
$total_amt_all = 0;

$cust_counter = 0;


$query_job = "SELECT a.item_id,b.item_name,b.size_in_mm,sum(a.quantity) as tot_qty ,sum(a.amount) as tot_amt FROM inventory_received as ad INNER JOIN inventoty_received_details as a ON ad.ir_id = a.ir_id INNER JOIN inventory_item as b on a.item_id = b.item_id WHERE a.received_date >= '".$from_dt."' AND a.received_date <= '".$to_dt."' AND a.status = 0 group by a.item_id";
$result_job = mysqli_query($conn,$query_job);
$counter = 0;
if(mysqli_num_rows($result_job)>0){
	$tot_amt_this = 0;
	$tot_qty_this = 0;
	while($data_job = mysqli_fetch_array($result_job)){
		$counter++;
		$item_id = $data_job['item_id'];
		$tot_qty = $data_job['tot_qty'];
		$item_name = $data_job['item_name'];
		$tot_amt = $data_job['tot_amt'];
		$size_in_mm = $data_job['size_in_mm'];


		$description_table .= '<tr>';
		$description_table .= '<td style="text-align:center;">'.$counter.'</td>';
		$description_table .= '<td style="text-align:left;">'.$item_id.'</td>';
		$description_table .= '<td style="text-align:center;">'.$item_name.' ('.$size_in_mm.')</td>';
		$description_table .= '<td style="text-align:right;">'.number_format($tot_qty).'</td>';
		$description_table .= '<td style="text-align:right;">'.number_format($tot_amt).'</td>';
		$description_table .= '</tr>';

		$total_amt_all += $tot_amt;
		$tot_qty_this += $tot_qty;
	}
}


$description_table .= '<tr>';
$description_table .= '<td colspan="4" style="text-align:center;color:blue"><b>Total</b></td>';
$description_table .= '<td style="text-align:right;"><b>'.number_format($total_amt_all,2).'</b></td>';
$description_table .= '</tr>';	

$description_table .= '</table>';




$systemdate=date('Y-m-d H:i:s');
$para='<br><br><br><br><br><span style="color:black;font-size:12px;text-align:center;">This Report is printed through System at <span style="color:blue;"><strong>'.$systemdate.'</strong></span></span>';


$pdf->writeHTML($labels, true, 1, true, 1, '');
$pdf->writeHTML($description_table, true, 1, true, 1, '');
$pdf->writeHTML($para, true, 1, true, 1, '');

$file_name = 'Inventory Purchase Report.pdf';
$pdf->Output($file_name, 'I');
// }
// else{
?>
<!-- <h3>Invalid Link</h3> -->
<?php
// }
// }
// else{
?>
<!-- <h3>Invalid Link</h3> -->
<?php
// }

?>
