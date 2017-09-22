<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>

<div class="page_separator"><img width="100%" height="100%" src="application/views/img/separator.jpg"><div>
<?php
	$show_new_only_checked = isset($_SESSION['file_browser_show_new_only']) && $_SESSION['file_browser_show_new_only'] ? "checked" : "";
	$show_order_reverse_checked = isset($_SESSION['file_browser_show_order_reverse']) && $_SESSION['file_browser_show_order_reverse'] ? "checked" : "";
	$quick_mode_checked = isset($_SESSION['file_browser_quick_mode']) && $_SESSION['file_browser_quick_mode'] ? "checked" : "";
	echo <<<__EOF__
		<form action='index.php' method="get">
			<input type="hidden" name="action" value="file_browser_set" />
			<input type="checkbox" name="show_new_only" value="y" $show_new_only_checked /> 新文件
			<input type="checkbox" name="show_order_reverse" value="y" $show_order_reverse_checked /> 倒序
			<input type="checkbox" name="quick_mode" value="y" $quick_mode_checked /> 快速模式
			<input type="submit" value="确定" />
		</form>
__EOF__;
?>

<div class="page_separator"><img width="100%" height="100%" src="application/views/img/separator.jpg"><div>
<div>
	<div class="page_line_hl" style="word-break:break-all">目录: <?php echo $cur_dir; ?></div>
	<div>
		<?php
			foreach ($dir_list as $folder) {
				echo <<<__EOF__
					<span class="def_block">
						<a href="index.php?action=list_dir&ch_dir=$cur_dir/$folder">
							<img style="width:100%;" src="application/views/img/dir.jpg" >
						</a>
						<a href="index.php?action=list_dir&ch_dir=$cur_dir/$folder">$folder</a>
					</span>
__EOF__;
			}
		?>
	</div>
</div>
<div class="page_separator"><img width="100%" height="100%" src="application/views/img/separator.jpg"><div>
