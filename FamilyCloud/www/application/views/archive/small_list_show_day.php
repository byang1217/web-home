<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<?php $this->load->view('header');?>

<?php 
	echo <<<__EOF__
	<div class="page_separator"><img width="100%" height="50%" src="application/views/img/separator.jpg"></img></div>
	<div class="page_line_hl">时间轴</div>
	<form action="index.php" method="get">
		<input type="hidden" name="action" value="show_day" />
		<input style="width:60%;" name="date" type="month" value="$show_day_year-$show_day_month"/>
		<input style="float:right;width:30%;" type="submit" value="确定"/>
		
	</form>
	<div class="page_separator"><img width="100%" height="50%" src="application/views/img/separator.jpg"></img></div>
	<div class="page_separator"><img width="100%" height="50%" src="application/views/img/separator.jpg"></img></div>
__EOF__;

	foreach ($file_list as $file_id) {
		$file_info = $this->myhome->file_id_to_info($file_id);
		$file_name = $this->myhome->path_to_base($file_info['real_path']);
		$www_thumb_file = sprintf("%s/%s/%s", $file_info['www'], "thumbnail", $file_info['path']);
		if ($this->myhome->is_video_file($file_name))
			$www_thumb_file .= ".gif";
		$file_date = $file_info['date'];
		if ($file_date == "1900-01-01")
			$file_date = "老照片";
		$file_note = $file_info['note'];
		$file_note = htmlspecialchars($file_note);
		$file_note = nl2br($file_note);

		echo <<<__EOF__
		<div class="page_line_hl"><a href="index.php?action=show_day&date=$file_date" >$file_date</a></div>
__EOF__;
		echo <<<__EOF__
		<div class="list_line">
			<span class="list_img"><a href="index.php?action=show_day&date=$file_date" ><img height="100%" src="$www_thumb_file"></img></a></span>
			<span class="list_text"><br>$file_note</span>
		</div>
		<div class="page_separator"><img width="100%" height="50%" src="application/views/img/separator.jpg"></img></div>
__EOF__;
	}


	echo <<<__EOF__
	<form action="index.php" method="get">
		<input type="hidden" name="action" value="show_day" />
		<input style="width:60%;" name="date" type="month" value="$show_day_year-$show_day_month"/>
		<input style="float:right;width:30%;" type="submit" value="确定"/>
	</form>
__EOF__;
?>

<?php $this->load->view('footer');?>
