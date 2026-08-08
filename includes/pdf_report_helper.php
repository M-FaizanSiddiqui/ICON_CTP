<?php
if(!function_exists('icon_pdf_company_header_html')){
	function icon_pdf_company_header_html(){
		return '<table border="0" cellpadding="1" width="100%" style="font-size:10px;color:#303033;">
			<tr><th><b>ICON Design:</b> Suite # 8, Plot # D-20/A, MOIN AKHTER ROAD,</th></tr>
			<tr><th>S.I.T.E., Karachi-75700. (Pakistan).</th></tr>
			<tr><th>PH: (021) 3256 4266 | (0331) 111 4266</th></tr>
		</table>';
	}
}

if(!function_exists('icon_pdf_apply_defaults')){
	function icon_pdf_apply_defaults($pdf, $title, $left = 15, $top = 30, $right = 15){
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor($title);
		$pdf->SetTitle($title);
		$pdf->SetSubject($title);
		$pdf->SetKeywords($title.', ICON CTP');
		$pdf->SetHeaderData(PDF_HEADER_LOGO, 20, $title, 'ICON CTP');
		$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		$pdf->SetMargins($left, $top, $right);
		$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
		$pdf->SetAutoPageBreak(true, 14);
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
	}
}

if(!function_exists('icon_pdf_title_block')){
	function icon_pdf_title_block($title, $subtitle = ''){
		$subtitle_html = $subtitle !== '' ? '<br><span style="font-size:8px;color:#d9d9dc;">'.$subtitle.'</span>' : '';
		return '<table border="0" cellpadding="0" cellspacing="0" width="100%">
			<tr>
				<td style="width:1%;background-color:#f36b21;"></td>
				<td style="width:99%;background-color:#303033;color:#ffffff;padding:6px;">
					<span style="font-size:17px;font-weight:bold;"> '.strtoupper($title).'</span>'.$subtitle_html.'
				</td>
			</tr>
		</table>';
	}
}

if(!function_exists('icon_pdf_note')){
	function icon_pdf_note($text = 'This report is intended for internal business use only.'){
		return '<br><table border="0" cellpadding="7" width="100%">
			<tr><td style="background-color:#fff6f0;color:#6f625b;font-size:8px;"><span style="color:#f36b21;font-weight:bold;">NOTE</span><br>'.$text.'</td></tr>
		</table>';
	}
}
?>
