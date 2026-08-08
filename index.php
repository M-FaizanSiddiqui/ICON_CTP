<?php include_once 'secure_session.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="robots" content="noindex">

  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <title><?php echo isset($_SESSION['system']['name']) ? $_SESSION['system']['name'] : '' ?></title>
  

  <?php
  if(!isset($_SESSION['login_id'])){
    ?>
    <script>
     window.open('login.php','_self');
   </script>
   <?php

 }else{
  include('./header.php'); 
   // include('./auth.php'); 
}


?>
?>

</head>
<style>
  .row{margin-right: 0px !important;; margin-left: 0px !important;}
  hr{margin-top: 0rem !important;}
  body{
    background: #f8f9fa;
  }
  .modal-dialog.large {
    width: 80% !important;
    max-width: unset;
  }
  .modal-dialog.mid-large {
    width: 50% !important;
    max-width: unset;
  }
  #viewer_modal .btn-close {
    position: absolute;
    z-index: 999999;
    /*right: -4.5em;*/
    background: unset;
    color: white;
    border: unset;
    font-size: 27px;
    top: 0;
  }
  #viewer_modal .modal-dialog {
    width: 80%;
    max-width: unset;
    height: calc(90%);
    max-height: unset;
  }
  #viewer_modal .modal-content {
   background: black;
   border: unset;
   height: calc(100%);
   display: flex;
   align-items: center;
   justify-content: center;
 }
 #viewer_modal img,#viewer_modal video{
  max-height: calc(100%);
  max-width: calc(100%);
}
#page {
  display: none;
}
#loading,
#preloader2 {
  position: fixed;
  inset: 0;
  z-index: 99999;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(248,249,252,.86);
  backdrop-filter: blur(8px);
}
#loading .app-loader-card,
#preloader2 .app-loader-card {
  width: min(300px, calc(100vw - 40px));
  padding: 24px 26px;
  border: 1px solid rgba(243,107,33,.16);
  border-radius: 24px;
  background: rgba(255,255,255,.96);
  box-shadow: 0 24px 70px rgba(21,26,39,.13);
  text-align: center;
}
#loading .app-loader-logo,
#preloader2 .app-loader-logo {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 128px;
  height: 62px;
  margin-bottom: 18px;
  border-radius: 18px;
  background: linear-gradient(135deg,rgba(243,107,33,.08),rgba(35,35,38,.04));
}
#loading .app-loader-logo img,
#preloader2 .app-loader-logo img {
  width: 104px;
  max-height: 42px;
  object-fit: contain;
}
#loading .app-loader-spinner,
#preloader2 .app-loader-spinner {
  width: 34px;
  height: 34px;
  margin: 0 auto 13px;
  border: 3px solid rgba(243,107,33,.17);
  border-top-color: #f36b21;
  border-radius: 50%;
  animation: iconLoaderSpin .8s linear infinite;
}
#loading .app-loader-title,
#preloader2 .app-loader-title {
  margin: 0;
  font-size: 13px;
  font-weight: 700;
  letter-spacing: .03em;
  color: #25262b;
}
#loading .app-loader-subtitle,
#preloader2 .app-loader-subtitle {
  margin: 4px 0 0;
  font-size: 11px;
  color: #8a8c93;
}
@keyframes iconLoaderSpin {
  to { transform: rotate(360deg); }
}

/* Shared professional form layout */
main#view-panel{margin-top:0 !important;padding:0 !important}
#content{padding-top:88px !important}
.professional-form-page{width:100%;max-width:1180px;margin:0 auto;padding:0 0 24px}
.professional-form-page.form-page-narrow{max-width:860px}
.professional-form-page .row{margin-right:-8px !important;margin-left:-8px !important}
.professional-form-page [class*="col-"]{padding-right:8px;padding-left:8px}
.professional-form-page .professional-form-card{overflow:visible;margin:0;border:1px solid #e8e9ec !important;border-radius:15px !important;background:#fff;box-shadow:0 10px 34px rgba(43,43,47,.07) !important}
.professional-form-page .professional-form-card>.card-header{display:flex;align-items:center;gap:13px;min-height:72px;padding:14px 20px !important;border:0 !important;border-bottom:1px solid #ececef !important;border-left:4px solid #f36b21 !important;border-radius:15px 15px 0 0 !important;color:#303033 !important;background:#fff !important}
.professional-form-page .form-title-icon{display:grid;place-items:center;flex:0 0 40px;width:40px;height:40px;border-radius:11px;color:#fff;background:linear-gradient(145deg,#f36b21,#df5812);box-shadow:0 7px 16px rgba(243,107,33,.2)}
.professional-form-page .form-title-copy{min-width:0}
.professional-form-page .form-title-copy h2{margin:0;font-size:17px;line-height:1.3;font-weight:600;color:#303033}
.professional-form-page .form-title-copy p{margin:3px 0 0;font-size:11px;line-height:1.4;font-weight:400;color:#8b8c92}
.professional-form-page .professional-form-card>.card-body{padding:22px !important}
.professional-form-page .master-form-fields{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:0 18px}
.professional-form-page .master-form-fields>input[type="hidden"]{display:none}
.professional-form-page .form-group{margin-bottom:17px}
.professional-form-page .form-group label{display:block;margin-bottom:7px;font-size:12px;font-weight:600;color:#4d4e53}
.professional-form-page .form-control{width:100%;height:43px !important;padding:9px 12px;border:1px solid #dfe1e5 !important;border-radius:9px !important;font-size:12px;color:#333439;background:#fff;transition:border-color .18s,box-shadow .18s}
.professional-form-page textarea.form-control{height:88px !important;resize:vertical}
.professional-form-page .form-control:focus{border-color:#f36b21 !important;box-shadow:0 0 0 3px rgba(243,107,33,.11) !important}
.professional-form-page .form-control::placeholder{color:#aaabb0}
.professional-form-page .professional-form-card .card-footer,.professional-form-page .form-actions{display:flex;align-items:center;justify-content:flex-end;gap:9px;margin:0 !important;padding:15px 22px !important;border:0;border-top:1px solid #ececef !important;border-radius:0 0 15px 15px;background:#fafafb !important}
.professional-form-page .card-footer>.row{width:100%}.professional-form-page .card-footer>.row>[class*="col-"]{display:flex;justify-content:flex-end;gap:9px}
.professional-form-page .btn{min-height:39px;padding:9px 16px;border-radius:9px !important;font-size:12px;font-weight:600;box-shadow:none;transition:transform .18s,box-shadow .18s,background .18s}
.professional-form-page .btn-primary,.professional-form-page .btn-success{border-color:#f36b21 !important;color:#fff !important;background:#f36b21 !important}
.professional-form-page .btn-primary:hover,.professional-form-page .btn-success:hover{transform:translateY(-1px);background:#df5913 !important;box-shadow:0 7px 16px rgba(243,107,33,.2)}
.professional-form-page .btn-default{border:1px solid #dddfe3 !important;color:#5e5f64;background:#fff !important}
.professional-form-page .btn-default:hover{background:#f2f3f4 !important}
.professional-form-page .table-responsive{overflow-x:auto;border:1px solid #e7e8eb;border-radius:11px}
.professional-form-page table{width:100%;margin:0!important;border:0!important;background:#fff}
.professional-form-page table th{padding:11px 12px!important;border-color:#e7e8eb!important;font-size:10px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:#67686e;background:#f5f5f6}
.professional-form-page table td{padding:9px 10px!important;border-color:#e7e8eb!important;vertical-align:middle!important}
.professional-form-page table .form-control{min-width:130px}
.professional-form-page .delBtn,.professional-form-page .del_row{color:#dc4f4f!important}
.professional-form-page .form-option-panel{padding:12px 15px!important;border:1px solid #e6e7ea;border-radius:10px;background:#fafafb}
.professional-form-page .form-option-panel label{margin-bottom:8px}
.professional-form-page .form-option-panel .form-check-line{display:inline-flex;align-items:center;gap:7px;margin:4px 18px 4px 0;font-size:12px;color:#55565b}
.professional-form-page .form-option-panel input[type="checkbox"]{width:16px;height:16px;accent-color:#f36b21}
.professional-form-page .simple-table input,.professional-form-page .simple-table select{min-width:92px}
.professional-form-page .simple-table .amount{background:#f7f7f8}
@media(max-width:768px){#content{padding-top:76px!important}.professional-form-page .master-form-fields{grid-template-columns:1fr}.professional-form-page .professional-form-card>.card-header{min-height:66px;padding:12px 15px!important}.professional-form-page .professional-form-card>.card-body{padding:17px!important}.professional-form-page .professional-form-card>.card-footer,.professional-form-page .form-actions{padding:13px 17px!important}}

/* Shared professional view and directory pages */
.professional-view-page{width:100%;max-width:1280px;margin:0 auto!important;padding:0 0 28px!important}
.professional-view-page>.col-lg-12{margin-top:0!important;padding:0!important}
.professional-view-page .professional-view-card{overflow:hidden;margin:0 0 18px;border:1px solid #e7e8eb!important;border-radius:15px!important;background:#fff;box-shadow:0 10px 34px rgba(43,43,47,.07)!important}
.professional-view-page .professional-view-card>.row:first-child{display:flex;align-items:center;min-height:70px;margin:0!important;padding:12px 16px;border-bottom:1px solid #ececef;border-left:4px solid #f36b21;background:#fff!important}
.professional-view-page .professional-view-card>.row:first-child h4,.professional-view-page .professional-view-card>.row:first-child h5{margin:0;font-size:17px;font-weight:600;color:#303033}
.professional-view-page .view-heading{display:flex;align-items:center;gap:12px}.professional-view-page .view-heading-icon{display:grid;place-items:center;flex:0 0 40px;width:40px;height:40px;border-radius:11px;color:#fff;background:linear-gradient(145deg,#f36b21,#df5812);box-shadow:0 7px 16px rgba(243,107,33,.2)}.professional-view-page .view-heading h2{margin:0;font-size:16px;font-weight:600;color:#303033}.professional-view-page .view-heading p{margin:3px 0 0;font-size:10px;line-height:1.4;color:#898a90}
.professional-view-page .professional-view-card>.card-header{display:flex;align-items:center;min-height:70px;padding:15px 20px!important;border:0!important;border-bottom:1px solid #ececef!important;border-left:4px solid #f36b21!important;border-radius:15px 15px 0 0!important;font-size:16px;font-weight:600;color:#303033!important;background:#fff!important}
.professional-view-page .professional-view-card>.card-body{overflow-x:auto;padding:18px!important}
.professional-view-page .detail-header-icon{display:grid;place-items:center;flex:0 0 38px;width:38px;height:38px;margin-right:11px;border-radius:10px;color:#fff;background:linear-gradient(145deg,#f36b21,#df5812)}
.professional-view-page .professional-view-card .btn-primary{border-color:#f36b21!important;color:#fff!important;background:#f36b21!important}
.professional-view-page .professional-view-card .btn{display:inline-flex;align-items:center;justify-content:center;gap:6px;min-height:34px;padding:7px 11px;border-radius:8px!important;font-size:10px;font-weight:600;box-shadow:none!important}
.professional-view-page .professional-view-card .btn-success{border-color:#2e9667!important;background:#2e9667!important}.professional-view-page .professional-view-card .btn-warning{border-color:#eba53a!important;color:#fff!important;background:#eba53a!important}.professional-view-page .professional-view-card .btn-danger{border-color:#d95757!important;background:#d95757!important}
.professional-view-page .professional-view-card .table-responsive{border:1px solid #e6e7ea;border-radius:10px}
.professional-view-page table{width:100%!important;margin:0!important;border-collapse:separate!important;border-spacing:0!important}
.professional-view-page table thead th,.professional-view-page table tr:first-child th{padding:11px 12px!important;border-color:#e3e4e7!important;font-size:9px!important;font-weight:700!important;letter-spacing:.07em;text-align:left;text-transform:uppercase;color:#6c6d73!important;background:#f5f5f6!important}
.professional-view-page table tbody td{padding:11px 12px!important;border-color:#ececef!important;font-size:11px;color:#515258;vertical-align:middle!important;background:#fff}
.professional-view-page table tbody tr:nth-child(even) td{background:#fcfcfd}.professional-view-page table tbody tr:hover td{background:#fff8f4}
.professional-view-page table p{margin:0!important;font-size:inherit;line-height:1.5;color:inherit}
.professional-view-page .dataTables_wrapper{padding:0}.professional-view-page .dataTables_length,.professional-view-page .dataTables_filter{margin-bottom:14px;font-size:10px;color:#77787e}.professional-view-page .dataTables_filter label,.professional-view-page .dataTables_length label{display:flex;align-items:center;gap:7px;font-weight:500}
.professional-view-page .dataTables_filter input,.professional-view-page .dataTables_length select{height:35px!important;margin:0!important;padding:6px 9px;border:1px solid #dfe1e5!important;border-radius:8px!important;outline:0;background:#fff}.professional-view-page .dataTables_filter input{width:210px}.professional-view-page .dataTables_filter input:focus{border-color:#f36b21!important;box-shadow:0 0 0 3px rgba(243,107,33,.1)}
.professional-view-page .dataTables_info{padding-top:14px!important;font-size:10px;color:#85868b}.professional-view-page .dataTables_paginate{padding-top:9px!important}.professional-view-page .paginate_button{min-width:31px!important;height:31px;padding:6px 9px!important;border:0!important;border-radius:7px!important;font-size:10px}.professional-view-page .paginate_button.current{color:#fff!important;background:#f36b21!important}
.professional-view-page .view-code{display:inline-flex;padding:4px 8px;border-radius:7px;font-size:10px;font-weight:600;color:#df5913;background:#fff0e8}
.professional-view-page .view-summary-grid{margin:0 -7px 18px!important}.professional-view-page .view-summary-grid>[class*="col-"]{padding:0 7px}.professional-view-page .view-summary-card{display:flex;align-items:center;gap:14px;min-height:90px;padding:15px 17px;border:1px solid #e7e8eb;border-radius:13px;background:#fff;box-shadow:0 7px 24px rgba(45,45,49,.055)}
.professional-view-page .view-summary-icon{display:grid;place-items:center;flex:0 0 43px;width:43px;height:43px;border-radius:12px;font-size:16px;color:#f36b21;background:#fff0e8}.professional-view-page .view-summary-card.is-active .view-summary-icon{color:#27865a;background:#eaf8f1}.professional-view-page .view-summary-card.is-inactive .view-summary-icon{color:#c95050;background:#fff0f0}
.professional-view-page .view-summary-copy h6{margin:0 0 4px;font-size:9px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#898a90}.professional-view-page .view-summary-copy h3{margin:0;font-size:23px;font-weight:650;color:#303033}
.professional-view-page .professional-view-card>.row:first-child .btn{width:auto!important;max-width:none!important;white-space:nowrap}
.view-customers .professional-view-card,.view-employees .professional-view-card,.view-modules .professional-view-card,.view-requisitions .professional-view-card,.view-items .professional-view-card,.view-waste-inventory .professional-view-card{background:#f7f7f8}
.view-customers .professional-view-card>.row:first-child,.view-employees .professional-view-card>.row:first-child,.view-modules .professional-view-card>.row:first-child,.view-requisitions .professional-view-card>.row:first-child,.view-items .professional-view-card>.row:first-child,.view-waste-inventory .professional-view-card>.row:first-child{background:linear-gradient(135deg,#fff,#fafafa)!important}
.view-customers .professional-view-card>.card-body,.view-employees .professional-view-card>.card-body,.view-modules .professional-view-card>.card-body,.view-requisitions .professional-view-card>.card-body,.view-items .professional-view-card>.card-body,.view-waste-inventory .professional-view-card>.card-body{margin:12px;border:1px solid #e6e7ea;border-radius:11px;background:#fff}
@media(max-width:767px){.professional-view-page{padding-bottom:20px!important}.professional-view-page .view-summary-grid>[class*="col-"]{margin-bottom:10px}.professional-view-page .professional-view-card>.card-body{padding:12px!important}.professional-view-page .professional-view-card>.row:first-child{align-items:flex-start;flex-direction:column;gap:8px}.professional-view-page .professional-view-card>.row:first-child>[class*="col-"]{width:100%;max-width:100%;padding:0}.professional-view-page .professional-view-card>.row:first-child .btn{float:none!important;width:auto!important;margin:0!important}.professional-view-page .dataTables_filter{float:none;text-align:left}.professional-view-page .dataTables_filter label{align-items:flex-start;flex-direction:column}.professional-view-page .dataTables_filter input{width:100%}}

.professional-ledger-page{width:100%;max-width:1280px;margin:0 auto;padding:0 0 28px}.professional-ledger-page>.col-lg-12{margin-top:0!important;padding:0}.professional-ledger-card{overflow:hidden;border:1px solid #e7e8eb!important;border-radius:15px!important;background:#fff;box-shadow:0 10px 34px rgba(43,43,47,.07)!important}
.ledger-page-header{display:flex;align-items:center;gap:12px;padding:15px 19px;border-bottom:1px solid #ececef;border-left:4px solid #f36b21;background:linear-gradient(135deg,#fff,#fafafa)}.ledger-page-header .view-heading-icon{display:grid;place-items:center;flex:0 0 40px;width:40px;height:40px;border-radius:11px;color:#fff;background:linear-gradient(145deg,#f36b21,#df5812)}.ledger-page-header h2{margin:0;font-size:16px;font-weight:600;color:#303033}.ledger-page-header p{margin:3px 0 0;font-size:10px;color:#898a90}
.professional-ledger-page .card_body{padding:0}.professional-ledger-page .ledger-filters{margin:0!important;padding:18px!important;background:#f8f8f9}.professional-ledger-page .ledger-filters label{font-size:11px;font-weight:600;color:#55565b}.professional-ledger-page .form-control{height:40px!important;border:1px solid #dfe1e5!important;border-radius:9px!important;font-size:11px}.professional-ledger-page .form-control:focus{border-color:#f36b21!important;box-shadow:0 0 0 3px rgba(243,107,33,.1)!important}.professional-ledger-page .btn{min-height:38px;padding:8px 13px;border-radius:8px!important;font-size:10px;font-weight:600}.professional-ledger-page .btn-success,.professional-ledger-page .btn-primary{border-color:#f36b21!important;background:#f36b21!important}.professional-ledger-page hr{display:none}.professional-ledger-page .ledger-table-wrap{overflow-x:auto;margin:14px 18px 18px!important;padding:0!important;border:1px solid #e6e7ea;border-radius:10px}.professional-ledger-page table{width:100%;margin:0!important}.professional-ledger-page table th{padding:11px 12px!important;border-color:#e3e4e7!important;font-size:9px!important;font-weight:700;text-transform:uppercase;color:#68696f;background:#f5f5f6!important}.professional-ledger-page table td{padding:10px 12px!important;border-color:#ececef!important;font-size:11px;color:#505156}.professional-ledger-page table tbody tr:nth-child(even) td{background:#fcfcfd}.professional-ledger-page table tbody tr:hover td{background:#fff8f4}
@media(max-width:767px){.professional-ledger-page .ledger-filters>[class*="col-"]{margin-bottom:12px}.professional-ledger-page .ledger-table-wrap{margin:10px 12px 14px!important}}
.professional-ledger-page .ledger-table-wrap{display:block!important;width:auto!important;max-width:none!important;overflow-x:auto!important}
.professional-ledger-page .ledger-table-wrap table{width:100%!important;min-width:880px}
.professional-ledger-page .ledger-table-wrap th:nth-last-child(-n+3),.professional-ledger-page .ledger-table-wrap td:nth-last-child(-n+3){min-width:86px;white-space:nowrap;text-align:right}
.professional-ledger-page .ledger-table-wrap th:last-child,.professional-ledger-page .ledger-table-wrap td:last-child{min-width:116px;padding-right:20px!important}
.view-customer-payments .professional-view-card,.view-customer-inventory .professional-view-card,.view-account-types .professional-view-card,.view-journal-vouchers .professional-view-card{background:#f7f7f8}.view-customer-payments .professional-view-card>.card-body,.view-customer-inventory .professional-view-card>.card-body,.view-account-types .professional-view-card>.card-body,.view-journal-vouchers .professional-view-card>.card-body{margin:12px;border:1px solid #e6e7ea;border-radius:11px;background:#fff}.view-journal-vouchers .datagrid{border:0!important}.view-journal-vouchers .datagrid-header{background:#f5f5f6!important}.view-journal-vouchers .datagrid-header td{font-size:10px!important;font-weight:700!important;color:#68696f!important}.view-journal-vouchers .datagrid-row-over,.view-journal-vouchers .datagrid-row-selected{background:#fff4ec!important;color:#333!important}
.professional-payroll-page{width:100%;max-width:1380px;margin:0 auto!important;padding:0 0 28px!important}.professional-payroll-page>.col-lg-12{margin-top:0!important;padding:0!important}.professional-payroll-page .payroll-card{overflow:hidden;border:1px solid #e7e8eb!important;border-radius:15px!important;background:#fff;box-shadow:0 10px 34px rgba(43,43,47,.07)!important}.professional-payroll-page .payroll-header{display:flex;align-items:center;justify-content:space-between;gap:15px;min-height:70px;margin:0!important;padding:13px 18px!important;border-bottom:1px solid #ececef;border-left:4px solid #f36b21;background:linear-gradient(135deg,#fff,#fafafa)!important}.professional-payroll-page .payroll-title{display:flex;align-items:center;gap:12px}.professional-payroll-page .payroll-title-icon{display:grid;place-items:center;width:40px;height:40px;border-radius:11px;color:#fff;background:linear-gradient(145deg,#f36b21,#df5812)}.professional-payroll-page .payroll-title h2{margin:0;font-size:16px;font-weight:600;color:#303033}.professional-payroll-page .payroll-title p{margin:3px 0 0;font-size:10px;color:#898a90}.professional-payroll-page .card_body,.professional-payroll-page .card-body{padding:18px!important}.professional-payroll-page .form-control{height:40px!important;border:1px solid #dfe1e5!important;border-radius:8px!important;font-size:11px}.professional-payroll-page .form-control:focus{border-color:#f36b21!important;box-shadow:0 0 0 3px rgba(243,107,33,.1)!important}.professional-payroll-page .btn{min-height:35px;padding:7px 11px;border-radius:8px!important;font-size:10px;font-weight:600}.professional-payroll-page .btn-primary,.professional-payroll-page .btn-success{border-color:#f36b21!important;background:#f36b21!important}.professional-payroll-page .btn-warning{border-color:#eaa13a!important;color:#fff!important;background:#eaa13a!important}.professional-payroll-page table{width:100%!important;margin:0!important}.professional-payroll-page table th{padding:10px!important;border-color:#e3e4e7!important;font-size:9px!important;font-weight:700!important;text-transform:uppercase;color:#68696f!important;background:#f5f5f6!important}.professional-payroll-page table td{padding:9px 10px!important;border-color:#ececef!important;font-size:10px;color:#505156;vertical-align:middle!important}.professional-payroll-page table tbody tr:nth-child(even) td{background:#fcfcfd}.professional-payroll-page table tbody tr:hover td{background:#fff8f4}.professional-payroll-page .imgClick{max-height:34px!important;padding:5px;border:1px solid #e2e3e6;border-radius:8px;background:#fff;cursor:pointer}.professional-payroll-page .datagrid{border:0!important}.professional-payroll-page .datagrid-header{background:#f5f5f6!important}.professional-payroll-page .datagrid-row-over,.professional-payroll-page .datagrid-row-selected{background:#fff4ec!important;color:#333!important}
.form-control{
  border: 1px solid #d7d7d7 !important;
  border-radius: 0 !important;
  height: 38px !important;
}
.card-header:first-child{
  background: white;
  font-size: 15px;
  font-weight: bold
}
.card-footer:last-child{
  background: #ffb178;
}
h5{
  color:orange;
}
.color-gray{
  color: grey;
}



:root{
  --primary:#0b1324;
  --accent:#ff6a00;
  --bg:#f4f7fc;
  --text:#2b2b2b;
  --border:#e4e7ee;
}


body{
  background:var(--bg);
  font-family:'Segoe UI',sans-serif;
  color:var(--text);
}

.card{
  border:none;
  border-radius:14px;
  box-shadow:0 10px 30px rgba(0,0,0,0.07);
  overflow:hidden;
}

.card-header{
  background:linear-gradient(135deg,#0b1324,#1c2541);
  color:#fff;
  padding:16px 20px;
  font-size:16px;
  font-weight:600;
  letter-spacing:.5px;
}

.card-body{
  padding:25px;
}

.card-footer{
  background:#fff;
  border-top:1px solid var(--border);
  padding:18px;
}

label{
  font-size:13px;
  font-weight:600;
  margin-bottom:6px;
}

.form-control{
  height:42px;
  border-radius:10px;
  border:1px solid var(--border);
  font-size:13px;
  transition:.2s;
}

.form-control:focus{
  border-color:var(--accent);
  box-shadow:0 0 0 3px rgba(255,106,0,0.15);
}

textarea.form-control{
  height:90px;
}

.btn-primary{
  background: var(--accent);
  border: none;
  border-radius: 10px;
  font-weight: 600;
}

.btn-primary:hover{
  background: #e85f00;
  transform: scale(1.02);
}

.col-lg-12{margin-top:20px !important;}
.card-header{color:black !important;}
.card-footer:last-child {background: #fdd180 !important;}
</style>

<body>
  <div id="page"></div>
  <div id="loading">
    <div class="app-loader-card" role="status" aria-live="polite">
      <div class="app-loader-logo"><img src="assets/uploads/logo.png" alt="ICON"></div>
      <div class="app-loader-spinner"></div>
      <p class="app-loader-title">Loading module</p>
      <p class="app-loader-subtitle">Please wait a moment</p>
    </div>
  </div>
  <?php include 'topbar.php' ?>
  <?php include 'navbar.php' ?>


  <div class="toast" id="alert_toast" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="toast-body text-white">
    </div>
  </div>
  
  <main id="view-panel" >

    <!-- Page Content  -->
    <div id="content" class="app-content">


      <?php
      $page = isset($_GET['page']) ? trim((string)$_GET['page']) :'home';
      if($page === ''){
        $page = 'home';
      }
      if(!preg_match('/^[A-Za-z0-9_\\-\\/]+$/', $page) || strpos($page, '..') !== false || strpos($page, '//') !== false){
        include 'invalidLink.php';
        exit;
      }
      $page_safe = mysqli_real_escape_string($conn, $page);
      $quer = "SELECT * from modules_1 where m_url ='".$page_safe."'";
      $order = $conn->query($quer);
      $row_cnt = $order->num_rows;
      if($row_cnt>0){

        $row=$order->fetch_assoc();
        $m_id = $row['m_id'];
        $m_parent_id = $row['m_parent_id'];

        $resolved_page = realpath($page.'.php');
        $root_path = realpath(__DIR__);
        if($resolved_page && strpos($resolved_page, $root_path) === 0 && file_exists($page.'.php') == 1){
          $pageArray = explode("/",$page);
          $folder = $pageArray[0];
          include $page.'.php';
        }else{

          include 'invalidLink.php';
        }
      }    
      else{
        include 'invalidLink.php';
      }
      ?>

    </div>
  </div>
</div>

</main>


<a href="#" class="back-to-top"><i class="icofont-simple-up"></i></a>

<div class="modal fade" id="confirm_modal" role='dialog'>
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Confirmation</h5>
      </div>
      <div class="modal-body">
        <div id="delete_content"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" id='confirm' onclick="">Continue</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="uni_modal" role='dialog'>
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"></h5>
      </div>
      <div class="modal-body">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" id='submit' onclick="$('#uni_modal form').submit()">Save</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="viewer_modal" role='dialog'>
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
      <button type="button" class="btn-close" data-dismiss="modal"><span class="fa fa-times"></span></button>
      <img src="" alt="">
    </div>
  </div>
</div>

</body>

<script>








  $(document).ready(function(){
   $('.minus_icon').hide();
   $('.loading_gif').hide();

   $(document).on("click",".plus_icon",function() 
   {
     var acco_no = $(this).closest('tr').find('.account_no_tb').val();
     var company_id_acc = $('#company_id_acc').val();

     var this_row = $(this).closest('tr');

     this_row.find('.loading_gif').show();
     this_row.find('.plus_icon').hide();

     $.ajax({
      type: "POST",
      url : "ajax-req/ajax_request.php",
      data: {acco_no: acco_no, company_id:company_id_acc, req_no:9}, 
      success: function(response){

        $(response).insertAfter(this_row);
        this_row.find('.plus_icon').hide();
        this_row.find('.minus_icon').fadeIn();
        if(response == '')
        {
          this_row.find('.loading_gif').hide();
        }
      },
      complete: function(){
        this_row.find('.loading_gif').hide();
      }
    });

   });

   $(document).on("click",".minus_icon",function() {
    var user_id = $(this).closest('tr').find('.account_no_tb').val();
    var this_row = $(this).closest('tr');
    this_row.find('.plus_icon').fadeIn();
    this_row.find('.minus_icon').hide();
    $('.new_tr_'+user_id).hide();
  });




   var id = "<?php echo $folder ?>";
   var m_id = "<?php echo $m_id ?>";
   var m_parent_id = "<?php echo $m_parent_id ?>";

   $("#"+id).removeClass("collapse"); 
   $(".nav-"+m_id).addClass("active");
   $("#"+id).addClass("show"); 
   $(".nav-"+m_parent_id).removeClass("collapsed");
   $(".nav-"+m_parent_id).attr('aria-expanded', true);




 });

  window.start_load = function(){
    if($('#preloader2').length){
      return;
    }
    $('body').prepend('<div id="preloader2"><div class="app-loader-card" role="status" aria-live="polite"><div class="app-loader-logo"><img src="assets/uploads/logo.png" alt="ICON"></div><div class="app-loader-spinner"></div><p class="app-loader-title">Loading module</p><p class="app-loader-subtitle">Please wait a moment</p></div></div>')
  }
  window.end_load = function(){
    $('#preloader2').fadeOut('fast', function() {
      $(this).remove();
    })
  }
  window.viewer_modal = function($src = ''){
    start_load()
    var t = $src.split('.')
    t = t[1]
    if(t =='mp4'){
      var view = $("<video src='"+$src+"' controls autoplay></video>")
    }else{
      var view = $("<img src='"+$src+"' />")
    }
    $('#viewer_modal .modal-content video,#viewer_modal .modal-content img').remove()
    $('#viewer_modal .modal-content').append(view)
    $('#viewer_modal').modal({
      show:true,
      backdrop:'static',
      keyboard:false,
      focus:true
    })
    end_load()  

  }
  window.uni_modal = function($title = '' , $url='',$size=""){
    start_load()
    $.ajax({
      url:$url,
      error:err=>{
        console.log()
        alert("An error occured")
      },
      success:function(resp){
        if(resp){
          $('#uni_modal .modal-title').html($title)
          $('#uni_modal .modal-body').html(resp)
          if($size != ''){
            $('#uni_modal .modal-dialog').addClass($size)
          }else{
            $('#uni_modal .modal-dialog').removeAttr("class").addClass("modal-dialog modal-md")
          }
          $('#uni_modal').modal({
            show:true,
            backdrop:'static',
            keyboard:false,
            focus:true
          })
          end_load()
        }
      }
    })
  }
  window._conf = function($msg='',$func='',$params = []){
    $('#confirm_modal #confirm').attr('onclick',$func+"("+$params.join(',')+")")
    $('#confirm_modal .modal-body').html($msg)
    $('#confirm_modal').modal('show')
  }
  window.alert_toast= function($msg = 'TEST',$bg = 'success'){
    $('#alert_toast').removeClass('bg-success')
    $('#alert_toast').removeClass('bg-danger')
    $('#alert_toast').removeClass('bg-info')
    $('#alert_toast').removeClass('bg-warning')

    if($bg == 'success')
      $('#alert_toast').addClass('bg-success')
    if($bg == 'danger')
      $('#alert_toast').addClass('bg-danger')
    if($bg == 'info')
      $('#alert_toast').addClass('bg-info')
    if($bg == 'warning')
      $('#alert_toast').addClass('bg-warning')
    $('#alert_toast .toast-body').html($msg)
    $('#alert_toast').toast({delay:3000}).toast('show');
  }
  $(document).ready(function(){
    $('#loading').fadeOut(180, function() {
      $(this).remove();
    })
    $('#preloader').fadeOut('fast', function() {
      $(this).remove();
    })
    $(document).on('click', '#sidebar a.nav-item, .sidebar-brand, a[href^="index.php?page="]', function(e){
      var target = $(this).attr('target');
      var href = $(this).attr('href') || '';
      if(target === '_blank' || href === '' || href === '#' || href.indexOf('javascript:') === 0){
        return;
      }
      start_load();
    });
  })
  $('.datetimepicker').datetimepicker({
    format:'Y/m/d H:i',
    startDate: '+3d'
  })
  $('.select2').select2({
    placeholder:"Please select here",
    width: "100%"
  })

</script>	

<script type="text/javascript">

  function googleTranslateElementInit() {
    new google.translate.TranslateElement({pageLanguage: 'en'}, 'google_translate_element');
  }
  function onReady(callback) {
    var intervalID = window.setInterval(checkReady, 1000);
    function checkReady() {
      if (document.getElementsByTagName('body')[0] !== undefined) {
        window.clearInterval(intervalID);
        callback.call(this);
      }
    }
  }

  function show(id, value) {
    var el = document.getElementById(id);
    if(el){
      el.style.display = value ? 'flex' : 'none';
    }
  }

  onReady(function () {
    show('page', true);
    show('loading', false);
  });
</script>
<style>
  .goog-logo-link {
    display:none !important;
  } 

  .goog-te-gadget{
    color: transparent !important;
  }
  .goog-te-gadget .goog-te-combo {
    margin: 0px 0;
    padding: 8px;
    color: #000;
    background: #eeee;
  }
  #google_translate_element{
    padding-top: 1px;
    position: absolute;
    top: 8px;
    right: 140px;
  }
</style>


<link rel="stylesheet" type="text/css" href="easy_ui/themes/default/easyui.css">
<link rel="stylesheet" type="text/css" href="easy_ui/themes/icon.css">
<script type="text/javascript" src="easy_ui/easy_ui_jquery.js"></script>
<script type="text/javascript" src="easy_ui/datagrid_export.js"></script>
<script type="text/javascript" src="easy_ui/data_grid_filters.js"></script>


<script type="text/javascript" src="assets/js/tableToExcel.js"></script>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>


<script type="text/javascript">

  // In your Javascript (external .js resource or <script> tag)
  $(document).ready(function() {
    $('.my_select2').select2();
  });


  $(document).ready(function() {

    var dg = $('#dg');
    dg.datagrid({
      toolbar: '#tb',
    });

    $('#export_datagrid').click(function(){
      $('#dg').datagrid('toExcel','receivable_report.xls');
    });



    $('.exportToExcel').click(function()
    { 
      TableToExcel.convert(document.getElementById("report-list"), {
        name: "Attendance.xlsx",
        sheet: {
          name: "Enquries"
        }
      });
    });

    $('#company_id_acc').change(function(){
      var comp =$('#company_id_acc').val();

      const urlParams = new URLSearchParams(window.location.search);
      const myParam = urlParams.get('page');

      setTimeout(function(){
        window.open('index.php?page='+myParam+'&comp='+comp,'_self');
      },500)
    })
  });
</script>





</html>
