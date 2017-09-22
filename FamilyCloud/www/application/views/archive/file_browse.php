<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>

<div>
<?php
	$my_http_url = 'http://yangjingxuan.tk/myhome';
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
		<a name="$file_id"></a>
		<div class="page_separator"><img width="100%" height="100%" src="application/views/img/separator.jpg"></img></div>
		<form class="info_update">
			<input type="hidden" name="action" value="info_update" />
			<input type="hidden" name="file_id" value="$file_id" />
__EOF__;
		if (!isset($file_browse_edit_mode) || !$file_browse_edit_mode) {
			$file_note = htmlspecialchars($file_note);
			$file_note = nl2br($file_note);
			if ($file_date != "1900-01-01") {
				echo <<<__EOF__
					<div><a href="index.php?action=show_day&date=$file_date#$file_id">$file_date</a></div>
__EOF__;
			}
			if (isset($show_title_in_file_browser) && $show_title_in_file_browser) {
				echo <<<__EOF__
					<div class="page_line_hl"><a href="index.php?action=page_jump&page_action=show_title&page_data=$title_base64&page_data2=none&page_num=$page_num#$file_id" >【 $file_title 】</a></div>
__EOF__;
			}
			echo <<<__EOF__
				<div>
					$file_note
				</div>
__EOF__;
			if ($support_admin_mode) {
				echo <<<__EOF__
					<div>
					<a href="index.php?action=show_file&show_mode=src&file_path=$file_path_enc" >【高清】</a>
					<a href="index.php?action=show_file&show_mode=org&file_path=$file_path_enc" >【超清】</a>
					<a style="float:right" href="$my_http_url/index.php?action=show_file&file_path=$file_path_enc" >【分享单个】</a>
					&nbsp
					&nbsp
					&nbsp
					<a style="float:right" href="$my_http_url/index.php?action=page_jump&page_action=share_title&page_data=$title_enc&page_data2=none&page_num=$page_num#$file_id" >【分享所有】</a>
					</div>
__EOF__;
			}else {
				echo <<<__EOF__
					<div>
					<a style="float:right" href="$my_http_url/index.php?action=show_file&file_path=$file_path_enc" >【点击生成分享链接】</a>
					</div>
__EOF__;
			}
		}

		if ($file_info['type'] == 'img') {
			if ($support_admin_mode) {
				echo <<<__EOF__
					<div style="font-size:50%;"> $file_id </div>
__EOF__;
			}
			if ($file_addr != "none" && $file_addr != "") {
				echo <<<__EOF__
					<div style="font-size:75%;"><a href="$my_http_url/index.php?action=baidu_map&file_id=$file_id"> 照片GPS地址: $file_addr </a></div>
__EOF__;
			}
			if ($img_orientation == 6) {
				echo <<<__EOF__
				<div>
					<img id="$rotate_id" style="max-width:100%;-webkit-transform:rotate(90deg) scale($img_ro_scale,$img_ro_scale);-moz-transform:rotate(90deg) scale($img_ro_scale,$img_ro_scale);-ms-transform:rotate(90deg) scale($img_ro_scale,$img_ro_scale);-o-transform:rotate(90deg) scale($img_ro_scale,$img_ro_scale);transform:rotate(90deg) scale($img_ro_scale,$img_ro_scale);" src="$loading_img" data-echo="$www_file">
				</div>
__EOF__;
			}elseif ($img_orientation == 3) {
				echo <<<__EOF__
				<div>
					<img id="$rotate_id" style="max-width:100%;-webkit-transform:rotate(180deg) scale($img_ro_scale,$img_ro_scale);-moz-transform:rotate(90deg) scale($img_ro_scale,$img_ro_scale);-ms-transform:rotate(90deg) scale($img_ro_scale,$img_ro_scale);-o-transform:rotate(90deg) scale($img_ro_scale,$img_ro_scale);transform:rotate(90deg) scale($img_ro_scale,$img_ro_scale);" src="$loading_img" data-echo="$www_file">
				</div>
__EOF__;
			}elseif ($img_orientation == 8) {
				echo <<<__EOF__
				<div>
					<img id="$rotate_id" style="max-width:100%;-webkit-transform:rotate(270deg) scale($img_ro_scale,$img_ro_scale);-moz-transform:rotate(90deg) scale($img_ro_scale,$img_ro_scale);-ms-transform:rotate(90deg) scale($img_ro_scale,$img_ro_scale);-o-transform:rotate(90deg) scale($img_ro_scale,$img_ro_scale);transform:rotate(90deg) scale($img_ro_scale,$img_ro_scale);" src="$loading_img" data-echo="$www_file">
				</div>
__EOF__;
			}else {
				echo <<<__EOF__
				<div>
					<img id="$rotate_id" style="max-width:100%;" src="$loading_img" data-echo="$www_file" >
				</div>
__EOF__;
			}
		echo <<<__EOF__
			<div>
				<a href="#$file_id" onclick="img_rotate('$rotate_id','right')">旋转图片</a>
			</div>
__EOF__;
		}elseif ($file_info['type'] == 'video') {
		echo <<<__EOF__
			<div style="font-size:50%;">如果视频播放有问题，可以尝试用UC浏览器或者用安卓手机。或者点击上面的高清链接下载播放</div>
			<div>
				<video id="$rotate_id"  style="max-width:100%;" controls >
					<source src="$www_file" type="video/mp4" preload="none" />
				</video>
			</div>
__EOF__;
		}else {
		echo <<<__EOF__
			<div>
				<a href="$www_file" >$file_name </a>
			</div>
__EOF__;
		}
		if (isset($file_browse_edit_mode) && $file_browse_edit_mode) {
			echo <<<__EOF__
				<div class="page_line">$file_name</div>
				<br>
				<div class="page_line">相册</div>
				<div class="page_line">
					<input style="width:100%;" name="title" type="text" value="$file_title">
				</div>
				<div class="page_line" >时间</div>
				<div class="page_line">
					<input style="width:100%;" name="date" type="date" value="$file_date">
				</div>
				<div class="page_line">
					<input name="star" type="checkbox" value="y" $file_star_checked>精华, 收藏
				</div>
				<div class="page_line">留言</div>
				<div>
					<textarea style="width:100%" name="note" rows=3>$file_note</textarea>
				</div>
				<div class="page_line">
					<input name="del_file" type="checkbox" value="y" >删除
					<input style="float:right;width:30%;" name="submit" type="submit" value="更新" />
				</div>
			</form>
__EOF__;
		}
	}
?>
	</div>



