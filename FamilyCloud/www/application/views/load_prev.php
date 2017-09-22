<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>

<?php
echo <<<__EOF__
<script>
var load_pages = 1;
var delay = 250;
var poll = null;
var load_prev_stop = false;
var load_next_stop = false;
var load_more_prev_offset = $load_more_prev_offset;
var load_more_next_offset = $load_more_next_offset;
</script>
__EOF__;
?>

<script>
var debounceOrThrottle = function () {
	if (load_prev_stop && load_next_stop) {
		if (document.removeEventListener) {
			document.removeEventListener('scroll', debounceOrThrottle);
		} else {
			document.detachEvent('onscroll', debounceOrThrottle);
		}
	}

	if(!!poll) {
		return;
	}
	clearTimeout(poll);
	poll = setTimeout(function(){
			var ScrollHight = $(document).height();
			var ScrollTop = $(window).scrollTop();
			var WindowHeight = $(window).height();
			if(!load_next_stop && ScrollTop + WindowHeight > ScrollHight - (WindowHeight * load_pages)) {
				load_more(true);
			}else if(!load_prev_stop && ScrollTop < (WindowHeight * load_pages)) {
				load_more(false);
			}else {
				poll = null;
			}
		}, delay);
};

$(document).ready(function (){
		if (document.addEventListener) {
			document.addEventListener('scroll', debounceOrThrottle, false);
		} else {
			document.attachEvent('onscroll', debounceOrThrottle);
		}
});

function load_more(is_load_next) {
	var action = "ajax";
	var sub_action = "ajax_load_prev"
	var org_offset = load_more_prev_offset;
	if (is_load_next) {
		sub_action = "ajax_load_next";
		org_offset = load_more_next_offset;
	}
	var success_callback = function (data) {
		var new_offset = data.offset;
		if (is_load_next) {
			load_more_next_offset = new_offset;
		}else {
			load_more_prev_offset = new_offset;
		}
		if (is_load_next) {
			document.getElementById("file_list_div").innerHTML += data.html;
			if (new_offset == org_offset) {
				load_next_stop = true;
				var ScrollHight = $(document).height();
				document.getElementById("div_load_next").style.display="none";
			}
		}else {
			var ScrollHight = $(document).height();
			var ScrollTop = $(window).scrollTop();
			var WindowHeight = $(window).height();
			if (new_offset == org_offset) {
				load_prev_stop = true;
				document.getElementById("div_load_prev").style.display="none";
			}else {
				document.getElementById("file_list_div").innerHTML = data.html + document.getElementById("file_list_div").innerHTML;
			}
			var newScrollHight = $(document).height();
			$(window).scrollTop(ScrollTop + newScrollHight - ScrollHight);
		}
		poll = null;
		debounceOrThrottle(); //call again to make sure no action is pending
	};

	var error_callback = function (status) {
		alert('load more error: ' + status);
	};

	ajax_form_xfer(action, sub_action, "", org_offset, success_callback, error_callback);
}

function load_next() {
	document.getElementById("file_list_div").innerHTML += "<div>hello</div>";
}
</script>

<div id="div_load_prev" style="height:40px;text-align:center" ><a href="javascript:void(0);" onclick='load_more(false)'><img style="max-width:100%;max-height:100%" src="application/views/img/loading.gif" onload='load_more(false)'></img></a></div>
<br clear=left>

<div id="file_list_div">

