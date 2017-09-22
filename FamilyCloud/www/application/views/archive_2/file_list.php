<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>

<datalist id="bookmark_list">
  <option value="Boston">
  <option value="Cambridge">
</datalist>

<?php
$img_note_edit = "application/views/img/note.png";
$img_bookmark = "application/views/img/bookmark.png";
$img_back = "application/views/img/back.png";

foreach ($file_list as $file_id) {
	$file_info = $this->My_file->file_id_to_info($file_id);
	if ($file_info == false)
		continue;
	$name = my_path_to_base($file_info['path']);
	$www_hd = sprintf("%s/%s/%s", "photos", "HD", $file_info['path']);
	$www_fluent = sprintf("%s/%s/%s", "photos", "Fluent", $file_info['path']);
	$www_thumb = sprintf("%s/%s/%s", "photos", "Thumb", $file_info['path']);
	$file_type = $file_info['type'];
	$file_note = $file_info['note'];
	$file_note_div = nl2br($file_note);

	$img_star_id = sprintf("img_star_%d", $file_id);
	$img_star = $file_info['img_star'];
	$img_lock_id = sprintf("img_lock_%d", $file_id);
	$img_lock = $file_info['img_lock'];
	$img_delete_id = sprintf("img_delete_%d", $file_id);
	$img_delete = $file_info['img_delete'];

	$div_bookmark_id = sprintf("div_bookmark_%d", $file_id);

	$img_note_id = sprintf("img_note_%d", $file_id);
	$div_note_id = sprintf("div_note_%d", $file_id);
	$div_note_edit_id = sprintf("div_note_edit_%d", $file_id);
	$textarea_note_edit_id = sprintf("textarea_note_edit_%d", $file_id);

	echo <<<__EOF__
		<a name="$file_id"></a>
		<div class="page_separator"><img width="100%" height="100%" src="application/views/img/separator.jpg"></img></div>
			<div id="$div_note_id" >
				$file_note_div
			</div>
			<br clear=left>

			<div id="$div_note_edit_id"  style="display:none">
				<textarea id="$textarea_note_edit_id" rows="5" style="width:100%;border:2px orange dashed;" >$file_note</textarea>
				<button type="button" style="width:20%" onclick='note_update("$file_id", document.getElementById("$textarea_note_edit_id").value, "$div_note_id", "$img_note_id")'>OK</button>
			</div>
			<br clear=left>

			<div>
				<span  class="icon_block">
					<a href="javascript:void(0);" onclick='document.body.style.overflow="hidden";view_toggle("popup_textarea")'><img id="$img_note_id" style="max-width:100%;" src="$img_note_edit"></a>
				</span>
				<span  class="icon_block">
					<a href="javascript:void(0);" onclick='view_toggle("$div_note_edit_id")'><img id="$img_note_id" style="max-width:100%;" src="$img_note_edit"></a>
				</span>
				<span  class="icon_block">
					<a href="javascript:void(0);" onclick='view_toggle("$div_bookmark_id")'><img style="max-width:100%;" src="$img_bookmark"></a>
					<div id="$div_bookmark_id" style="display:none;">
						<input type="text" name="bookmark" list="bookmark_list">
					</div>
				</span>
				<span  class="icon_block">
					<a href="javascript:void(0);" onclick='toggle_click("star", "$file_id", "$img_star_id")'><img id="$img_star_id" style="max-width:100%;" src="$img_star"></a>
				</span>
				<span  class="icon_block">
					<a href="javascript:void(0);" onclick='toggle_click("lock", "$file_id", "$img_lock_id")'><img id="$img_lock_id" style="max-width:100%;" src="$img_lock"></a>
				</span>
				<span  class="icon_block">
					<a href="javascript:void(0);" onclick='toggle_click("delete", "$file_id", "$img_delete_id")'><img id="$img_delete_id" style="max-width:100%;" src="$img_delete"></a>
				</span>
				<span  style="float:right" class="icon_block">
					<a href="javascript:void(0);" onclick='back_to_small("$file_id")'><img style="max-width:100%;" src="$img_back"></a>
				</span>
			</div>
			<br clear=left>

			<div>
				<input class="file_id_checkbox" type="checkbox" value="$file_id" />
			</div>
			<br clear=left>
			
__EOF__;

	if ($file_type == 'image') {
		echo <<<__EOF__
			<div>
				<img style="max-width:100%;" src="$www_fluent">
			</div>
__EOF__;
	}elseif ($file_info['type'] == 'video') {
		echo <<<__EOF__
			<div>
				<video style="max-width:100%;" controls >
					<source src="$www_fluent" type="video/mp4" preload="none" />
				</video>
			</div>
__EOF__;
	}else {
		echo <<<__EOF__
			<div>
				unknown file type: $file_id
			</div>
__EOF__;
	}
}
?>

