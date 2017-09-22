<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>

<datalist id="bookmark_list">
  <option value="Boston">
  <option value="Cambridge">
</datalist>

<?php
$js_scroll_dis = 'document.body.style.overflow="hidden"; ';
$js_scroll_en = 'document.body.style.overflow="auto"; ';
//$js_scroll_dis = '';
//$js_scroll_en = '';

$img_note_edit = "application/views/img/note.png";
$img_bookmark = "application/views/img/bookmark.png";
$img_back = "application/views/img/back.png";
$img_empty = "application/views/img/empty.png";
$img_loading = "application/views/img/loading.gif";

$list_view_mode = $form_params['list_view_mode'];
if ($list_view_mode == "thumb") {
	$list_class = 'class="thumb_block"';
	$list_separator = "";
	$toolbar_display = true;
	$note_display = "none";
	$back_display = "none";
	$star_display = "none";
	$bookmark_display = "none";
	$list_br = "";
	$icon_class = "thumb_icon_block";

}elseif ($list_view_mode == "big") {
	$list_class = "";
	$list_separator = '<div class="page_separator"><img width="100%" height="100%" src="application/views/img/separator.jpg"></img></div>';
	$toolbar_display = true;
	$note_display = "block";
	$back_display = "block";
	$bookmark_display = "block";
	$star_display = "block";
	$list_br = "<br clear=left>";
	$icon_class = "icon_block";
}elseif ($list_view_mode == "list") {
	$list_class = "";
	$list_separator = '<div class="page_separator"><img width="100%" height="100%" src="application/views/img/separator.jpg"></img></div>';
	$toolbar_display = false;
	$list_br = "<br clear=left>";
	$icon_class = "icon_block";
}else {
}

$seq = 0;
foreach ($file_list as $file_id) {
	$offset = $search_offset + $seq;
	$seq ++;
	$file_info = $this->My_file->file_id_to_info($file_id);
	if ($file_info == false)
		continue;
	$file_seq_id = $file_info['seq_id'];
	$name = my_path_to_base($file_info['path']);
	$www_hd = sprintf("%s/%s/%s", "photos", "HD", $file_info['path']);
	$www_fluent = sprintf("%s/%s/%s", "photos", "Fluent", $file_info['path']);
	$www_thumb = sprintf("%s/%s/%s", "photos", "Thumb", $file_info['path']);
	$www_hd = sprintf("%s/%s/%s", "photos", "HD", $file_info['path']);
	$file_type = $file_info['type'];
	$file_note = $file_info['note'];
	$file_note_div = nl2br($file_note);
	$file_date = $file_info['unix_time'] == '0' ? "" : date('Y-m-d', $file_info['unix_time']);

	$img_star = $file_info['img_star'];
	$img_lock = $file_info['img_lock'];
	$img_delete = $file_info['img_delete'];

	$div_bookmark_id = sprintf("div_bookmark_%d", $file_id);

	$img_note_id = sprintf("img_note_%d", $file_id);
	$div_note_id = sprintf("div_note_%d", $file_id);
	$div_note_popup_window_id = sprintf("div_note_popup_window_%d", $file_id);
	$textarea_note_edit_id = sprintf("textarea_note_edit_%d", $file_id);
	$js_note_popup_show = 'document.getElementById("'.$div_note_popup_window_id.'").style.display="block"';
	$js_note_popup_hide = 'document.getElementById("'.$div_note_popup_window_id.'").style.display="none"';

	if ($window_h && $window_w) {
		$fluent_h = $file_info['fluent_h'];
		$fluent_w = $file_info['fluent_w'];
		if ($window_w >= $fluent_w) {
			$div_fluent_height = $fluent_h;
		}else {
			$div_fluent_height = floor(($window_w / $fluent_w) * $fluent_h);
		}
		pr_debug(sprintf("div_h:%d, fluent_h: %d, fluent_w:%d, win_h:%d, win_w:%d\n",
			$div_fluent_height, $fluent_h, $fluent_w, $window_h, $window_w));
		$img_fluent_height_attr = sprintf('height="%dpx"', $div_fluent_height);
		
	}else {
		$img_fluent_height_attr = "";
	}

	if ($debug_mode) {
		$www_thumb = sprintf("%s?t=%d", $www_thumb, time());
		$www_fluent = sprintf("%s?t=%d", $www_fluent, time());
		echo "<!-- start of photo block -->\n";
	}
	$js_fluent_full_show = $js_scroll_dis . 'document.getElementById("full_screen_img").src="'.$www_fluent.'"; document.getElementById("popup_full_img").style.display="block"';
	$js_hd_full_show = $js_scroll_dis . 'document.getElementById("full_screen_img").src="'.$www_hd.'"; document.getElementById("popup_full_img").style.display="block"';


	if ($list_view_mode == "thumb")
		echo '<span class="thumb_block">';
	else
		echo '<div>';

	echo <<<__EOF__
		<a name="$file_id"></a>
		$list_separator
__EOF__;
	if ($toolbar_display) {
		echo <<<__EOF__
		<div id="$div_note_id" style="display:$note_display">
			$file_note_div
		</div>
		<div id="$div_note_popup_window_id" class="popup_window" style="display:none">
			<div class="black_overlay" onclick = '$js_scroll_en;$js_note_popup_hide;'></div>
			<div class="popup_overlay" >
				<div class="icon_block"><img style="max-height:100%;" src="$img_note_edit"></div>
				<textarea id="$textarea_note_edit_id" rows="5" class="popup_text" >$file_note</textarea>
				<button type="button" class="popup_button" onclick = '$js_scroll_en;$js_note_popup_hide;'>Cancel</button>
				<button type="button" class="popup_button" onclick='note_update("$file_id", document.getElementById("$textarea_note_edit_id").value, "$div_note_id", "$img_note_id"); $js_scroll_en;$js_note_popup_hide;'>OK</button>
			</div>
		</div>
		$list_br

		<div id="photo_icon_bar" search_offset="$offset" >
			<span  class="$icon_class" style="float:left;margin-left:0px;">
				<input style="width:100%;height:100%" class="file_id_checkbox" type="checkbox" value="$file_id" />&nbsp
			</span>
			<span  class="$icon_class" style="display:$note_display">
				<a href="javascript:void(0);" onclick='$js_scroll_dis;$js_note_popup_show;'><img id="$img_note_id" style="max-width:100%;" src="$img_note_edit"></a>
			</span>
			<span  class="$icon_class" style="display:$bookmark_display">
				<a href="javascript:void(0);" onclick='view_toggle("$div_bookmark_id")'><img style="max-width:100%;" src="$img_bookmark"></a>
				<div id="$div_bookmark_id" style="display:none;">
					<input type="text" name="bookmark" list="bookmark_list">
				</div>
			</span>
			<span  class="$icon_class" style="display:$star_display">
				<a href="javascript:void(0);" onclick='toggle_click("star", this, "$file_id");' ><img style="max-width:100%;" src="$img_star"></a>
			</span>
			<span  class="$icon_class">
				<a href="javascript:void(0);" onclick='toggle_click("lock", this, "$file_id");' ><img style="max-width:100%;" src="$img_lock"></a>
			</span>
			<span  class="$icon_class">
				<a href="javascript:void(0);" onclick='toggle_click("delete", this, "$file_id");' ><img style="max-width:100%;" src="$img_delete"></a>
			</span>
			<span  style="float:right;display:$back_display" class="$icon_class">
				<a href="javascript:void(0);" onclick='ajax_form_submit("show_list", "thumb", "$file_id", "")'><img style="max-width:100%;" src="$img_back"></a>
			</span>
		</div>
		$list_br
__EOF__;
	}

	if ($list_view_mode == "thumb") {
		if ($file_type == 'image' || $file_type == 'video') {
			echo <<<__EOF__
				<a href="javascript:void(0);" onclick='ajax_form_submit("show_list", "big", "$file_id", "")'><img style="width:100%;" src="$img_loading"  data-echo="$www_thumb"></a>
__EOF__;
		}else {
			echo <<<__EOF__
				unknown file type: $file_id
__EOF__;
		}
	}elseif ($list_view_mode == "big") {
		echo <<<__EOF__
		<div style="font-size:50%;">
			$file_date
		</div>
__EOF__;
		if ($file_type == 'image') {
			echo <<<__EOF__
				<div>
				<a href="javascript:void(0);" onclick='$js_hd_full_show'><img style="max-width:100%;max-height:100%" $img_fluent_height_attr src="$img_loading"  data-echo="$www_fluent"></a>
				</div>
__EOF__;
		}elseif ($file_info['type'] == 'video') {
			echo <<<__EOF__
				<video style="max-width:100%;" controls >
					<source src="$www_fluent" type="video/mp4" preload="none" />
				</video>
__EOF__;
		}else {
			echo <<<__EOF__
				unknown file type: $file_id
__EOF__;
		}
	}elseif ($list_view_mode == "list") {
		echo <<<__EOF__
		<div style="float:right;">
			$file_date
		</div>
		<br clear=left>
__EOF__;
		if ($file_type == 'image' || $file_type == 'video') {
			echo <<<__EOF__
				<div class="list_line">
				<span ><a href="javascript:void(0);" onclick='ajax_form_submit("show_list", "big", "$file_id", "")'><img height="100%"  src="$img_loading"  data-echo="$www_thumb"></a></span>
				<span><textarea style="width: 60%;height:100%;background:transparent; border-style:none;resize:none; overflow:hidden; " readonly="readonly" disabled="disabled"  >$file_note</textarea></span>
				</div>
__EOF__;
		}else {
			echo <<<__EOF__
				unknown file type: $file_id
__EOF__;
		}
	}else{
	}

	if (0 && $debug_mode) {
		echo <<<__EOF__
		<div>file_id= $file_id</div>
		<div>seq_id = $file_seq_id</div>
__EOF__;
		
/*
		foreach ($file_info as $key => $value) {
		echo <<<__EOF__
			<div>file_info[$key] = $value</div>
__EOF__;
		}
*/
	}

	if ($list_view_mode == "thumb")
		echo '</span>';
	else
		echo '</div>';

}
?>

