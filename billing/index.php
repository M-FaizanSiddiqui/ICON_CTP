<!DOCTYPE html>
<html lang="en">

<?php session_start(); ?>
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title><?php echo isset($_SESSION['system']['name']) ? $_SESSION['system']['name'] : '' ?></title>
  

  <?php
  include('./header.php'); ?> 

</head>
<style>
	body{
    background: #f8f9fa;
    position: fixed;
    width: calc(100%);
    height: calc(100%);
    overflow: auto;
  }
  main#view-panel {
    height: calc(100% - 7em);
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
main#view-panel {
 margin-left: inherit; 
 width: calc(100%);}
 #page {
  display: none;
}
#loading {
  position: fixed;
  inset: 0;
  z-index: 99999;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(248,249,252,.86);
  backdrop-filter: blur(8px);
}
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

<body>
  <div id="page"></div>
  <div id="loading">
    <div class="app-loader-card" role="status" aria-live="polite">
      <div class="app-loader-logo"><img src="../assets/uploads/logo.png" alt="ICON"></div>
      <div class="app-loader-spinner"></div>
      <p class="app-loader-title">Loading module</p>
      <p class="app-loader-subtitle">Please wait a moment</p>
    </div>
  </div>
  <?php include 'topbar.php' ?>
  <div class="toast" id="alert_toast" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="toast-body text-white">
    </div>
  </div>
  
  <main id="view-panel" >
    <?php $page = isset($_GET['page']) ? $_GET['page'] :'home'; ?>
    <?php include $page.'.php' ?>
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
    <div class="modal-dialog modal-dialog-centered modal-md" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"></h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
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
</div>

</body>

<script>
 window.start_load = function(){
  if($('#preloader2').length){
    return;
  }
  $('body').prepend('<div id="preloader2"><div class="app-loader-card" role="status" aria-live="polite"><div class="app-loader-logo"><img src="../assets/uploads/logo.png" alt="ICON"></div><div class="app-loader-spinner"></div><p class="app-loader-title">Loading module</p><p class="app-loader-subtitle">Please wait a moment</p></div></div>')
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
window.uni_modal = function($title = '' , $url='',$size="",$params = {}){
  start_load()
  $.ajax({
    url:$url,
    method:'POST',
    data:$params,
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
          $('#uni_modal .modal-dialog').removeAttr("class").addClass("modal-dialog modal-dialog-centered modal-md")
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
  $(document).on('click', 'a[href^="index.php?page="], a[href^="../billing/index.php?page="], a[href^="billing/index.php?page="]', function(e){
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
<script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>


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
    document.getElementById(id).style.display = value ? 'block' : 'none';
  }

  onReady(function () {
    show('page', true);
    show('loading', false);
  });
</script>

</html>
