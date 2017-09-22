<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<div>
	<form action='index.php' enctype= "multipart/form-data" method="post">
		<input type="hidden" name="action" value="upload" />
		<div class="page_line">
		<input style="width:75%;" name="userfile" type="file"/>
		<input style="float:right;width:20%;" type="submit" value="上传"/>
	</form>

</div>
<div class="page_separator"><img width="100%" height="100%" src="application/views/img/separator.jpg"><div>

