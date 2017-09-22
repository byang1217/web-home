<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>

<script language="javascript">
function year_view_toggle(id)
{
        var obj = document.getElementById(id);
        if (!obj) return false;

        if (obj.style.display == "none")
                obj.style.display = "block";
        else
                obj.style.display = "none";
}
</script>

<div>
<?php
	for ($y = date('Y'); $y > date('Y')-3; $y--) {
		echo <<<__EOF__
<span class="def_block"><a href="index.php?action=small_view&date=$y">$y</a></span>
__EOF__;
	}
?>
<span class="mid_block"><a href="javascript:void(0);" onclick="year_view_toggle('small_view_year_list')">更多...</a></span>

</div>
<div id="small_view_year_list" style="display:none;">
<?php
	for ($y = date('Y')-3; $y >= 2002; $y--) {
		echo <<<__EOF__
<span class="mid_block"><a href="index.php?action=small_view&date=$y">$y</a></span>
__EOF__;
	}
?>
<span class="mid_block"><a href="index.php?action=small_view&date=1900">老照片</a></span>
</div>
<div class="page_separator"><img width="100%" height="100%" src="application/views/img/separator.jpg"><div>

