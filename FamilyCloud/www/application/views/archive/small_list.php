<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<?php $this->load->view('header');?>
<?php if (isset($is_default_page) && $is_default_page) $this->load->view('year_list');?>
<?php if ($page_action != "none") $this->load->view('page_next');?>

<?php 
	foreach ($file_list as $file_id) {
		$file_info = $this->myhome->file_id_to_info($file_id);
		$file_name = $this->myhome->path_to_base($file_info['real_path']);
		$www_thumb_file = sprintf("%s/%s/%s", $file_info['www'], "thumbnail", $file_info['path']);
		if ($this->myhome->is_video_file($file_name))
			$www_thumb_file .= ".gif";
		$file_title = $file_info['title'];
		$file_date = $file_info['date'];
		if ($file_date == "1900-01-01")
			$file_date = "";
		$file_note = $file_info['note'];
		$file_note = htmlspecialchars($file_note);
		$file_note = nl2br($file_note);
		$page_num = $file_info['page_num'] + 1;
		$title_enc = $this->str_encdec->encode($file_title);
		$title_base64 = base64_encode(base64_encode($file_title));

		echo <<<__EOF__
		<div class="page_line_hl"><a href="index.php?action=page_jump&page_action=show_title&page_data=$title_base64&page_data2=none&page_num=$page_num#$file_id" >$file_title</a></div>
__EOF__;
		if (false && $admin_mode) {
			echo <<<__EOF__
		<div style="float:right;font-size:50%;">
			$file_date
			&nbsp
			<a href="index.php?action=page_jump&page_action=share_title&page_data=$title_enc&page_data2=none&page_num=$page_num#$file_id" >分享</a>
			&nbsp
		</div>
__EOF__;
		}else {
			echo <<<__EOF__
		<div style="float:right;"><a href="index.php?action=show_day&date=$file_date#$file_id">$file_date</a></div>
__EOF__;
		}
		echo <<<__EOF__
		<div class="list_line">
			<span class="list_img"><a href="index.php?action=page_jump&page_action=show_title&page_data=$title_base64&page_data2=none&page_num=$page_num#$file_id" ><img height="100%" src="application/views/img/loading.gif" data-echo="$www_thumb_file"></img></a></span>
			<span class="list_text"><br>$file_note</span>
		</div>
		<div class="page_separator"><img width="100%" height="50%" src="application/views/img/separator.jpg"></img></div>
__EOF__;
	}
?>

<?php if ($page_action != "none") $this->load->view('page_next');?>

<?php $this->load->view('footer');?>
