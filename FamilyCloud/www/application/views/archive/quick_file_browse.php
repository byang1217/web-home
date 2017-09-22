<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>

<div>
<form action='index.php' method="post">
	<input type="hidden" name="action" value="batch_info_update" />

<?php
	$my_http_url = 'http://yangjingxuan.tk/myhome';
	$loop_index = 0;
	foreach ($file_list as $file_id) {
		$file_info = $this->myhome->file_id_to_info($file_id);
		$file_name = $this->myhome->path_to_base($file_info['real_path']);
		$www_file = sprintf("%s/%s/%s", $file_info['www'], "small", $file_info['path']);
		$www_big_file = sprintf("%s/%s/%s", $file_info['www'], "src", $file_info['path']);
		$file_title = $file_info['title'];
		$file_date = $file_info['date'];
		$file_note = $file_info['note'];
		$file_star_checked = $file_info['star'] == "y" ? "checked" : "";
		$title_enc = $this->str_encdec->encode($file_title);
		$file_path_enc = $this->str_encdec->encode($file_info['path']);
		$page_num = $file_info['page_num'] + 1;
		$title_base64 = base64_encode(base64_encode($file_title));
		$rotate_id = sprintf("ro_%d", $file_id);
		$img_orientation = $file_info['Orientation'];
		$img_ro_scale = $file_info['ro_scale'];
		$file_addr = $file_info['address'];

		if (isset($file_browse_edit_mode) && $file_browse_edit_mode)
			$loading_img = "$www_file";
		else
			$loading_img = "application/views/img/loading.gif";
			


		if (isset($show_mode) && $show_mode=="src")
			$www_file = $www_big_file;
		if (isset($show_mode) && $show_mode=="org")
			$www_file = sprintf("%s/%s", "myhome/original/", $file_info['real_path']);

		echo <<<__EOF__
		<span class="huge_block">
		<a name="$file_id"></a>
		<input type="hidden" name="file_id_array[]" value="$file_id" />
__EOF__;

		if ($file_info['type'] == 'img') {
			if ($file_addr != "none" && $file_addr != "") {
				echo <<<__EOF__
					<a href="$my_http_url/index.php?action=baidu_map&file_id=$file_id"> 照片GPS地址: $file_addr </a>
__EOF__;
			}
			if ($img_orientation == 6) {
				echo <<<__EOF__
					<img id="$rotate_id" style="max-width:100%;-webkit-transform:rotate(90deg) scale($img_ro_scale,$img_ro_scale);-moz-transform:rotate(90deg) scale($img_ro_scale,$img_ro_scale);-ms-transform:rotate(90deg) scale($img_ro_scale,$img_ro_scale);-o-transform:rotate(90deg) scale($img_ro_scale,$img_ro_scale);transform:rotate(90deg) scale($img_ro_scale,$img_ro_scale);" src="$loading_img" data-echo="$www_file">
__EOF__;
			}elseif ($img_orientation == 3) {
				echo <<<__EOF__
					<img id="$rotate_id" style="max-width:100%;-webkit-transform:rotate(180deg) scale($img_ro_scale,$img_ro_scale);-moz-transform:rotate(90deg) scale($img_ro_scale,$img_ro_scale);-ms-transform:rotate(90deg) scale($img_ro_scale,$img_ro_scale);-o-transform:rotate(90deg) scale($img_ro_scale,$img_ro_scale);transform:rotate(90deg) scale($img_ro_scale,$img_ro_scale);" src="$loading_img" data-echo="$www_file">
__EOF__;
			}elseif ($img_orientation == 8) {
				echo <<<__EOF__
					<img id="$rotate_id" style="max-width:100%;-webkit-transform:rotate(270deg) scale($img_ro_scale,$img_ro_scale);-moz-transform:rotate(90deg) scale($img_ro_scale,$img_ro_scale);-ms-transform:rotate(90deg) scale($img_ro_scale,$img_ro_scale);-o-transform:rotate(90deg) scale($img_ro_scale,$img_ro_scale);transform:rotate(90deg) scale($img_ro_scale,$img_ro_scale);" src="$loading_img" data-echo="$www_file">
__EOF__;
			}else {
				echo <<<__EOF__
					<img id="$rotate_id" style="max-width:100%;" src="$loading_img" data-echo="$www_file" >
__EOF__;
			}
		}elseif ($file_info['type'] == 'video') {
		echo <<<__EOF__
				<video id="$rotate_id"  style="max-width:100%;" controls >
					<source src="$www_file" type="video/mp4" preload="none" />
				</video>
__EOF__;
		}else {
		echo <<<__EOF__
				<a href="$www_file" >$file_name </a>
__EOF__;
		}
		echo <<<__EOF__
			<p  align="left" style="font-size:50%;">$file_id: $file_name</p>
			<p  align="left">相册
			<input id="title_$loop_index" name="title_array[]" type="text" value="$file_title" onchange="quick_title_update($loop_index, this.value)">
			</p>
			<p align="left" >时间
			<input name="date_array[]" type="date" value="$file_date">
			</p>
			<p align="left">
			<input name="star_array[]" type="checkbox" value="$file_id" $file_star_checked>精华, 收藏
			<p align="left">留言
			<textarea style="width:100%" name="note_array[]" rows=3>$file_note</textarea>
			</p>
			<p align="center">
			<input name="del_file_array[]" type="checkbox" value="$file_id" >删除
			</p>
		</span>
__EOF__;
		$loop_index ++;
	}

echo <<<__EOF__
<script>
function quick_title_update(idx, val) {
	for (var i = idx+1; i < $loop_index; i ++) {
		document.getElementById("title_"+i).value = val;
	}
}
</script>
__EOF__;

?>

	<div class="page_separator"><img width="100%" height="100%" src="application/views/img/separator.jpg"><div>
	<div class="page_line">
	<input style="float:right;width:30%;" name="submit" type="submit" value="更新" />
	</div>
	</form>
	</div>



