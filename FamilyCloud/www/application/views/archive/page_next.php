<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>

<div>
	<table align="center" class="page_line">
	<tr>
		<th style="width:20%" >
		<form action='index.php' method="get">
			<input type="hidden" name="action" value="page_jump" />
			<?php
			$prev_page_num = $page_num > 1 ? $page_num - 1 : 1;
			echo <<<__EOF__
			<input type="hidden" name="page_action" value="$page_action" />
			<input type="hidden" name="page_data" value="$page_data" />
			<input type="hidden" name="page_data2" value="$page_data2" />
			<input type="hidden" name="page_num" value="$prev_page_num" />
__EOF__
?>
			<input style="width:100%" type="submit" value="上页" />
		</form>
		</th>
		<th style="width:60%" >
		<form action='index.php' method="get">
			<input type="hidden" name="action" value="page_jump" />
			<?php echo <<<__EOF__
			<input type="hidden" name="page_action" value="$page_action" />
			<input type="hidden" name="page_data" value="$page_data" />
			<input type="hidden" name="page_data2" value="$page_data2" />
			<input name="page_num" style="width:20%" type="text" value="$page_num" />
__EOF__
?>
			<input style="width:30%" type="submit" value="Go" />
		</form>
		</th>
		<th style="width:20%" >
		<form action='index.php' method="get">
			<input type="hidden" name="action" value="page_jump" />
			<?php
			$next_page_num = $page_num + 1;
			echo <<<__EOF__
			<input type="hidden" name="page_action" value="$page_action" />
			<input type="hidden" name="page_data" value="$page_data" />
			<input type="hidden" name="page_data2" value="$page_data2" />
			<input type="hidden" name="page_num" value="$next_page_num" />
__EOF__
?>
			<input style="width:100%" type="submit" value="下页" />
		</form>
		</th>
	</tr>
	</table>
</div>
