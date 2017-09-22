<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" 
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title><?php echo $page_title;?></title>

		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<script src="external/jquery-1.12.4.min.js"></script>
		<link rel="stylesheet" href="external/jquery-ui-1.12.1.light/jquery-ui.min.css">
		<script src="external/jquery-ui-1.12.1.light/jquery-ui.min.js"></script>

		<link rel="stylesheet" type="text/css" media="screen and (min-device-width: 720px)" href="application/views/css/myhome_big.css?v=<?php echo filemtime("application/views/css/myhome_big.css");?>" />
		<link rel="stylesheet" type="text/css" media="screen and (max-device-width:720px)" href="application/views/css/myhome_small.css?v=<?php echo filemtime("application/views/css/myhome_small.css");?>" />
		<link rel="stylesheet" type="text/css" href="application/views/css/myhome.css?v=<?php echo filemtime("application/views/css/myhome.css");?>" />
		<script language="javascript" src="application/views/jquery.qrcode.min.js" type="text/javascript"></script>
		<script language="javascript" src="application/views/func.js?v=<?php echo filemtime("application/views/func.js");?>" type="text/javascript"></script>
		<script language="javascript" src="application/views/echo.js?v=<?php echo filemtime("application/views/echo.js");?>" type="text/javascript"></script>

		<script>

<?php
echo <<<__EOF__
		$(document).ready(function(){
			$.ajaxSetup({
				timeout: $network_timeout
			});
			echo.init({
				offset: 800,
				throttle: 250,
				unload: false,
			});
		});
__EOF__;
?>
		</script>

</head>
<?php
	if (isset($alert_msg)) {
		echo '<script type="text/javascript">';
		echo sprintf("alert(\"%s\");", $alert_msg);
		echo '</script>';
	}
?>
<body>

<div class="top">
<?php
	$img_star_y = "application/views/img/star_y.png";
	$img_star_n = "application/views/img/star_n.png";
	$img_lock_y = "application/views/img/lock_y.png";
	$img_lock_n = "application/views/img/lock_n.png";
	$img_delete_y = "application/views/img/delete_y.png";
	$img_delete_n = "application/views/img/delete_n.png";
	$img_order_by_access_y = "application/views/img/order_by_access_y.png";
	$img_order_by_access_n = "application/views/img/order_by_access_n.png";

	$js_scroll_dis = 'document.body.style.overflow="hidden"; ';
	$js_scroll_en = 'document.body.style.overflow="auto"; ';

	$div_search_popup_window_id = "div_search_popup_window";
	$js_search_popup_show = $js_scroll_dis . 'document.getElementById("'.$div_search_popup_window_id.'").style.display="block"';
	$js_search_popup_hide = $js_scroll_en . 'document.getElementById("'.$div_search_popup_window_id.'").style.display="none"';



	if ($has_login) {
	echo <<<__EOF__
		<span class="icon_block">
		<a href="javascript:void(0);" onclick='$js_search_popup_show;'><img style="max-height:100%;" src="application/views/img/search_white.png"></a>
		</span>

__EOF__;

	echo <<<__EOF__
		<span class="icon_block">
		<a href="javascript:void(0);" onclick='select_post("gen_share", "", "img_gen_share_link")'><img id="img_gen_share_link" style="max-width:100%;" src="application/views/img/share.png"></a>
		</span>

__EOF__;
	}
	if ($has_admin_access) {
	echo <<<__EOF__
		<span class="icon_block">
		<a href="index.php?action=setup"><img style="max-width:100%;" src="application/views/img/setup.png" ></a>
		</span>
__EOF__;

	echo <<<__EOF__
		<span class="icon_block" style="float:right">
		<a href="javascript:void(0);" onclick='view_toggle("top_popup_menu")'><img style="max-width:100%;" src="application/views/img/menu.png"></a>
		</span>

__EOF__;
	}
?>
</div>

<div>
<?php
// search
echo <<<__EOF__
	<div id="$div_search_popup_window_id" class="popup_window" style="display:none">
		<div class="black_overlay" onclick = '$js_search_popup_hide;'></div>
		<div class="popup_overlay" >
		    <form action='index.php' method="post">
			<input type="hidden" name="action" value="search" />
			<div class="icon_block"><img style="max-height:100%;" src="application/views/img/search_y.png" /></div>
			<input class="popup_text" type="text" name="keyw" />
			<br clear=left>
			<div class="popup_line">
				<span>
					<a href="javascript:void(0);" onclick='toggle_checkbox_with_img("checkbox_is_star_id", "img_is_star_id", "$img_star_y", "$img_star_n")'>
					<img id="img_is_star_id" class="icon_block" style="max-height:100%;" src="$img_star_n" />
					<input id="checkbox_is_star_id" style="width:0px; height:0px;" type="checkbox" name="is_star" value="y" />
					</a>
				</span>
				<span>
					<a href="javascript:void(0);" onclick='toggle_checkbox_with_img("checkbox_is_lock_id", "img_is_lock_id", "$img_lock_y", "$img_lock_n")'>
					<img id="img_is_lock_id" class="icon_block" style="max-height:100%;" src="$img_lock_n" />
					<input id="checkbox_is_lock_id" style="width:0px; height:0px;" type="checkbox" name="is_lock" value="y" />
					</a>
				</span>
				<span>
					<a href="javascript:void(0);" onclick='toggle_checkbox_with_img("checkbox_is_delete_id", "img_is_delete_id", "$img_delete_y", "$img_delete_n")'>
					<img id="img_is_delete_id" class="icon_block" style="max-height:100%;" src="$img_delete_n" />
					<input id="checkbox_is_delete_id" style="width:0px; height:0px;" type="checkbox" name="is_delete" value="y" />
					</a>
				</span>
				<span style="float:right">
					<a href="javascript:void(0);" onclick='toggle_checkbox_with_img("checkbox_is_order_by_access_id", "img_is_order_by_access_id", "$img_order_by_access_y", "$img_order_by_access_n")'>
					<img id="img_is_order_by_access_id" class="icon_block" style="max-height:100%;" src="$img_order_by_access_n" />
					<input id="checkbox_is_order_by_access_id" style="width:0px; height:0px;" type="checkbox" name="is_order_by_access" value="y" />
					</a>
				</span>
			</div>

			<br clear=left>

			<button type="button" class="popup_button" onclick = '$js_search_popup_hide;'>Cancel</button>
			<button type="submit" class="popup_button" >OK</button>
		    </form>
		</div>
	</div>
__EOF__;
?>
<div>



<div id="popup_full_img" class="full_screen_window" style="display:none">
	<div class="full_screen_overlay">
		<img style="position:fixed; top:0px; left: 0px; height: 64px;" class="icon_topright" style="max-height:100%;" src="application/views/img/close.png" onclick = 'document.getElementById("popup_full_img").style.display="none"; document.body.style.overflow="auto"; '  />
		<img id="full_screen_img" src="" />
	</div>
</div>


<form id="ajax_form" action='index.php' method="post">
	<input type="hidden" name="action" value="unknown" />
	<input type="hidden" name="sub_action" value="unknown" />
	<input type="hidden" name="window_w" value="0" />
	<input type="hidden" name="window_h" value="0" />
	<input type="hidden" name="file_id_list" value="unknown" />
	<input type="hidden" name="param" value="unknown" />
<?php
foreach ($form_params as $key => $value) {
echo <<<__EOF__
	<input type="hidden" name="form_params[$key]" value="$value" />

__EOF__;
}
?>
</form>
<script>  
function ajax_form_xfer(action, sub_action, file_id_list, param, success_callback, error_callback) {
    var ajax_form=document.forms['ajax_form'];  
    ajax_form.action.value = action;  
    ajax_form.sub_action.value = sub_action;  
    ajax_form.file_id_list.value = file_id_list;  
    ajax_form.param.value = param;  
    ajax_form.window_h.value = $(window).height();;  
    ajax_form.window_w.value = $(window).width();;  

    $.ajax({
	type: 'POST',
//		async: false,
	url: 'index.php',
	data: $('#ajax_form').serialize(),
	dataType: 'json',
	success: function (data) {
		if (success_callback)
			success_callback(data);
	},
	error: function (xhr, status) {
		if (error_callback) {
			error_callback(status);
		}else {
		switch (status) {
		case 404:
			alert('File not found');
			break;
		case 500:
			alert('Server error');
			break;
		case 0:
			alert('Request aborted');
			break;
		default:
			alert('Unknown error ' + status);
		} 
	}
}
});
}

function ajax_form_submit(action, sub_action, file_id_list, param) {
    var ajax_form=document.forms['ajax_form'];  
    ajax_form.action.value = action;  
    ajax_form.sub_action.value = sub_action;  
    ajax_form.file_id_list.value = file_id_list;  
    ajax_form.param.value = param;  
    ajax_form.submit();  
}
</script>  

<div id="top_popup_menu" class="top_popup_menu" style="display:none">
	<a href="javascript:void(0);" onclick='ajax_form_submit("action_test", "sub_action_test", "1 2 3 4 5", "param_test")'><img style="max-width:250%;" src="application/views/img/share.png"></a>
	<div>aaaa</div>
	<div>aaaa</div>
	<div>aaaa</div>
	<div>aaaa</div>
	<div>aaaa</div>
	<div>aaaa</div>
</div>

<div id="page_container" class="page_container">


