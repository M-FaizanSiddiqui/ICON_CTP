<div class="customer-payment-modal-content">
	<?php include 'receipt_customer_payment.php'; ?>
</div>
<div class="modal-footer customer-payment-modal-footer display">
	<button class="btn payment-modal-close" type="button" data-dismiss="modal"><i class="fa fa-times"></i><span>Close</span></button>
	<button class="btn payment-modal-print" type="button" id="print"><i class="fa fa-print"></i><span>Print Receipt</span></button>
</div>

<style>
	#uni_modal .modal-content{overflow:hidden;border:0;border-radius:15px;box-shadow:0 24px 70px rgba(28,29,32,.22)}
	#uni_modal .modal-header{align-items:center;min-height:62px;padding:15px 20px;border:0;border-bottom:1px solid #ececef;border-left:4px solid #f36b21;background:#fff}
	#uni_modal .modal-title{font-size:16px;font-weight:650;color:#303033}#uni_modal .modal-header .close{display:grid;place-items:center;width:32px;height:32px;margin:-4px -4px -4px auto;padding:0;border-radius:8px;color:#77787d;background:#f4f4f5;opacity:1}
	#uni_modal .modal-body{padding:0;background:#f5f6f8}.customer-payment-modal-content{margin:14px;padding:18px 20px;border:1px solid #e5e6e9;border-radius:12px;background:#fff;box-shadow:0 7px 22px rgba(45,45,49,.05)}
	#uni_modal .modal-footer{display:none}#uni_modal .customer-payment-modal-footer.display{display:flex!important;align-items:center;justify-content:flex-end;gap:9px;padding:13px 18px;border:0;border-top:1px solid #e7e8eb;background:#fff}
	.customer-payment-modal-footer .btn{display:inline-flex;align-items:center;justify-content:center;gap:7px;min-height:38px;margin:0!important;padding:8px 15px;border-radius:9px;font-size:11px;font-weight:600;box-shadow:none}.payment-modal-close{border:1px solid #dadce0;color:#5e5f64;background:#fff}.payment-modal-close:hover{color:#303033;background:#f5f5f6}.payment-modal-print{border:1px solid #f36b21;color:#fff;background:#f36b21;box-shadow:0 7px 16px rgba(243,107,33,.18)!important}.payment-modal-print:hover{color:#fff;background:#df5913}
	@media(max-width:600px){.customer-payment-modal-content{margin:8px;padding:14px}.customer-payment-modal-footer .btn{flex:1}}
</style>

<script>
	$('#print').off('click.customerPayment').on('click.customerPayment', function(){
		start_load();
		var printWindow = window.open('Customer/receipt_customer_payment.php?id=<?php echo isset($_GET['id']) ? (int)$_GET['id'] : 0; ?>', '_blank', 'width=980,height=700');
		if(!printWindow){ end_load(); alert_toast('Please allow pop-ups to print the receipt.', 'warning'); return; }
		setTimeout(function(){
			printWindow.print();
			setTimeout(function(){ printWindow.close(); end_load(); }, 500);
		}, 600);
	});
</script>
