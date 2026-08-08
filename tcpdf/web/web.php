 

<?php 
$db = mysql_connect("localhost", "root", "Saladied@786!");  
mysql_select_db("gcs-new", $db);
?>
<form action="#" method="post">

 <textarea cols="50" rows="10" name="hdnimage" id="hdnimage"></textarea><br>
 <button class="form-control" name="submitimage" id="submitimage">Submit Image</button>
</form>


<video id="video" width="480" height="320" autoplay></video>

<button id="snap">Snap Photo</button>
<canvas id="canvas" width="480" height="320"></canvas>
 
<script type="text/javascript">




  var dataUrl;
  var video = document.getElementById('video');

// Get access to the camera!
if(navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
    // Not adding `{ audio: true }` since we only want video now
    navigator.mediaDevices.getUserMedia({ video: true }).then(function(stream) {
        //video.src = window.URL.createObjectURL(stream);
        video.srcObject = stream;
        video.play();
      });
  }

  var canvas = document.getElementById('canvas');
  var context = canvas.getContext('2d');
  var video = document.getElementById('video');

// Trigger photo take
document.getElementById("snap").addEventListener("click", function() {
 context.drawImage(video, 0, 0, 640, 480);
 dataUrl=canvas.toDataURL();
 alert(dataUrl);
   //saveImage();
   document.getElementById("hdnimage").value=dataUrl;

 });

document.getElementById("submitimage").addEventListener("click", function() {


  function saveImage() {

    var canvasData = canvas.toDataURL("image/png");

    var ajax = new XMLHttpRequest();

    ajax.open("POST", "ajax.php", false);
    ajax.onreadystatechange = function() {
   // alert(ajax.responseText);
 }
 ajax.setRequestHeader("Content-Type", "application/upload");
 ajax.send("imgData=" + canvasData);
}
saveImage();
});
</script>

<?php 
if(isset($_POST['submitimage'])){
  $imageData=$_POST['hdnimage'];
  $query="INSERT INTO camera set image='".$imageData."' ";
  $result=mysql_query($query);
}

?>