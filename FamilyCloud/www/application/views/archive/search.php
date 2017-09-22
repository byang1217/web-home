<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>

<div class="login_page">
	<br>
	<form action='index.php' method="get">
		<input type="hidden" name="action" value="search_file" />
		<div>
			<input style="width:40%;" type="text" name="keyw" />
			<input style="width:20%;" type="submit" value="搜索" />
			<br>
			<input type="checkbox" name="star" value="y" /> 只显示收藏，精华
		</div>
	</form>
	<br>
</div>

<?php
	if ($admin_mode) {
		$hot_search_list = file_get_contents("hot_search_list");
		echo <<<__EOF__
		<form action='index.php' method="post">
			<input type="hidden" name="action" value="set_hot_search_list" />
			<div>
				<textarea style="width:100%" name="hot_search_list" rows=8>$hot_search_list</textarea>
			</div>
			<input style="width:20%;" type="submit" value="Submit" />
		</form>
		<br>
__EOF__;
	}
?>

	<div class="page_separator"><img width="100%" height="50%" src="application/views/img/separator.jpg"></img></div>
	<div class="list_line">
	<span class="list_img"><a href="index.php?action=search_file&keyw=&star=y" ><img height="100%" src="myhome/files/thumbnail/0000009250_9250_095218be1079f526478789d3e6f758b2.jpg"></img></a></span>
	<span class="list_text" style="font-size:150%;" ><a href="index.php?action=search_file&keyw=&star=y"<br><br>收藏</a></span>
	</div>

<?php 
	
	$search_lines = file("hot_search_list", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
	foreach ($search_lines as $line) {
		$p = preg_split("/[\s,]+/", $line);
		$file_id = $p[0];
		$keyw=$p[1];
		$note=$p[1];
		$file_info = $this->myhome->file_id_to_info($file_id);
		$file_name = $this->myhome->path_to_base($file_info['real_path']);
		$www_thumb_file = sprintf("%s/%s/%s", $file_info['www'], "thumbnail", $file_info['path']);
		if ($this->myhome->is_video_file($file_name))
			$www_thumb_file .= ".gif";
		echo <<<__EOF__
	<div class="page_separator"><img width="100%" height="50%" src="application/views/img/separator.jpg"></img></div>
	<div class="list_line">
	<span class="list_img"><a href="index.php?action=search_file&keyw=@$keyw" ><img height="100%" src="$www_thumb_file"></img></a></span>
	<span class="list_text" style="font-size:150%;" ><a href="index.php?action=search_file&keyw=@$keyw"<br><br>$note</a></span>
	</div>
__EOF__;
	}
?>

