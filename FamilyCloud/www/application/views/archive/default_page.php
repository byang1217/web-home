<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<?php $this->load->view('myhome/header');?>

<?php 
echo <<<__EOF__
    <script type="text/javascript">
	var count_stop = 50;
	var count = count_stop;
	var response = true;
	var timeout = 99;
	var pending_timeout = 0;
	var update_pending = false;
	var preload_img_obj;
	var preload = "";
	var preload_file_id;
	var preload_file_date;
	var play_view_start = false;
	var load_timeout = 99;

	function play_view_toggle(id)
	{
		var obj = document.getElementById(id);
		if (!obj) return false;

		if (obj.style.display == "none") {
			document.getElementById("start_play").value = "停止("+count+")";
			var obj_ctrl = document.getElementById('play_view_ctrl');
			obj_ctrl.style.display = "none";
			play_view_start = true;
			obj.style.display = "block";
		}else {
			obj.style.display = "none";
			document.getElementById("start_play").value = "自动播放";
			play_view_start = false;
		}
	}

	function start_stop() {
		if (play_view_start) {
			document.getElementById("start_play").value = "自动播放";
			play_view_start = false;
		}else {
			count = count_stop;
			document.getElementById("start_play").value = "停止("+count+")";
			play_view_start = true;
		}
	}

	function play_img_loaded() {
		update_pending = false;
	}
	function request(){
		if(response == false){
			return;
		}

		if (load_timeout <= 5) {
			if (!document.getElementById("play_img").complete) {
				load_timeout ++;
				return;
			}
		}
		timeout ++;
/*
		if (!update_pending) {
			timeout ++;
		}else {
			pending_timeout ++;
		}
*/

		if (!play_view_start)
			return;

		if (count <= 0) {
			document.getElementById("start_play").value = "自动播放";
			play_view_start = false;
			return;
		}

		if(timeout >= 3 || pending_timeout >= 16){
			count --;
			if (preload != "") {
				document.getElementById("start_play").value = "停止(" + count + ")";
				document.getElementById("play_view").innerHTML = '<a href="index.php?action=show_day&date='+preload_file_date+'#'+preload_file_id+'"><img id="play_img" width=100% src="'+preload+'"></img></a>';
				load_timeout = 0;
			}
		}

		if(timeout >= 3 || pending_timeout >= 16){
			// This makes it unable to send a new request 
			// unless you get response from last request
			response = false;
			var req = $.ajax({
			type:"post",
			url: 'index.php',
			data:{action:"play_view",type:"1"},
			dataType: 'json',

			success: function (data) {
				pending_timeout = 0;
				timeout = 0;
				update_pending = true;
				response = true;
				preload_file_id = data.file_id;
				preload_file_date = data.file_date;
				preload_img_obj = new Image();
				preload_img_obj.src = data.img_url;
				preload_img_obj.onload = play_img_loaded;

				if (preload == "") {
					preload = data.img_url;
					document.getElementById("play_view").innerHTML = '<a href="index.php?action=show_day&date='+preload_file_date+'#'+preload_file_id+'"><img id="play_img"  width=100% src="'+preload+'"></img></a>';
					load_timeout = 0;
				}
				preload = data.img_url;


				//alert(data.msg);
			},

			 error: function (xhr, status) {
			      switch (status) {
				 case 404:
				     //alert('File not found');
				     break;
				 case 500:
				     //alert('Server error');
				     break;
				 case 0:
				     //alert('Request aborted');
				     break;
				 default:
				     //alert('Unknown error ' + status);
				      break;
			     } 
			      pending_timeout = 0;
			      timeout = 0;
			      update_pending = true;
			      response = true;
			 }
			});

		}
	}

	$( document ).ready(function() {
		request();
		var myVar=setInterval(function(){request()},1000);
	});
    </script>

    <div id="play_view_ctrl"><a href="javascript:void(0);" onclick="play_view_toggle('play_view_content')">随机播放图片 <img src="application/views/img/play.png" height=32px></img></a></div>

	<div id="play_view_content" style="display:none;">
	<div>
	随机播放 
	<input id="start_play" type="button" value="" onclick="start_stop()" ></input>
	<br>
	<br>
	</div>
	<div id="play_view" style="background-repeat:no-repeat;background-position: center center; background-image: url(application/views/img/loading.gif);">
	</div>
	</div>
	<div class="page_separator"><img width="100%" height="100%" src="application/views/img/separator.jpg"><div>
__EOF__;
?>

<?php $this->load->view('myhome/year_list');?>
<?php $this->load->view('myhome/search');?>

<?php $this->load->view('myhome/footer');?>
