<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>

<?php

foreach ($file_list as $file_id) {
	$file_info = $this->My_file->file_id_to_info($file_id);
	if ($file_info == false)
		continue;
	$name = my_path_to_base($file_info['path']);
	$www_hd = sprintf("%s/%s/%s", "photos", "HD", $file_info['path']);
	$www_fluent = sprintf("%s/%s/%s", "photos", "Fluent", $file_info['path']);
	$www_thumb = sprintf("%s/%s/%s", "photos", "Thumb", $file_info['path']);
	$file_type = $file_info['type'];

	echo <<<__EOF__
		<span class="thumb_block">
		<a name="$file_id"></a>
		<form class="info_update">
			<input type="hidden" name="action" value="info_update" />
			<input type="hidden" name="file_id" value="$file_id" />
__EOF__;

	if ($file_type == 'image' || $file_info['type'] == 'video') {
		echo <<<__EOF__
			<img style="width:100%;" src="$www_thumb">
__EOF__;
	}else {
		echo <<<__EOF__
			unknown file type: $file_id
__EOF__;
	}

	echo <<<__EOF__
		</form>
		</span>
__EOF__;
}
?>

