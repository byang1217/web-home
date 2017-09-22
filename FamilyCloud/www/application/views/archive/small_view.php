<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<?php $this->load->view('myhome/header');?>
<?php $this->load->view('myhome/year_list');?>

<?php 
	$cur_date="unknown";
	$cur_titile="unknown";
	$cur_month="unknown";
	$link_list = array();
	foreach ($file_list as $file_id) {
		$file_info = $this->myhome->file_id_to_info($file_id, true);
		$file_name = $this->myhome->path_to_base($file_info['real_path']);
		$www_thumb_file = sprintf("%s/%s/%s", $file_info['www'], "thumbnail", $file_info['path']);
		if ($this->myhome->is_video_file($file_name))
			$www_thumb_file .= ".gif";
		$file_title = $file_info['title'];
		$file_date = $file_info['date'];
		if ($file_date == "1900-01-01")
			$file_date_show = "老照片";
		else
			$file_date_show = $file_date;

		if ($cur_date != $file_date || $cur_title != $file_title) {
			$month=preg_split("/-/", $file_date)[1];
			if ($month != $cur_month) {
				$cur_month = $month;
				array_push($link_list, $month);
			echo <<<__EOF__
<a name="$cur_month"></a>
__EOF__;
			}
			echo <<<__EOF__
<br>
<div class="page_line_hl">$file_date_show &nbsp $file_title</div>
__EOF__;
			$cur_date = $file_date;
			$cur_title = $file_title;
		}
			echo <<<__EOF__
<span class="mid_block"><a href="index.php?action=show_day&date=$file_date#$file_id" ><img width="100%" src="application/views/img/loading.gif" data-echo="$www_thumb_file"></img></a></span>
__EOF__;
	}
/*
	echo <<<__EOF__
<div class="top2">
__EOF__;
	foreach ($link_list as $lk) {
		echo <<<__EOF__
		<a href="#$lk">$lk </a>
__EOF__;
	}
	echo <<<__EOF__
</div>
__EOF__;
*/
?>

<?php $this->load->view('myhome/footer');?>
