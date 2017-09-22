<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>

<script>

function select_post(command, param, img_id) {
	if (img_id != false) {
		src_save = document.getElementById(img_id).src;
		document.getElementById(img_id).src = "application/views/img/loading.gif";
	}

	var file_id_list = [];
	$('input.file_id_checkbox:checkbox:checked').each(function () {
	    file_id_list.push($(this).val());
	});

	$.ajax({
		type: 'POST',
//		async: false,
		url: 'index.php',
		data: {"action":"ajax", "sub_action":"select_post", "sub_command":command, "file_id_list":file_id_list},
		dataType: 'json',
		success: function (data) {
			if (img_id != false) {
				document.getElementById(img_id).src = src_save;
			}
			alert(data.ret);
		},
		 error: function (xhr, status) {
			 if (img_id != false) {
			 	document.getElementById(img_id).src = src_save;
			 }
		      switch (status) {
			 case 404:
			     alert('File not found');
			     break;
			 case 500:
			     alert('Server error');
			     break;
			 case 0:
			     alert('Request aborted');
			     break;
			 default:
			     alert('Unknown error ' + status);
		     } 
		 }
	});
}

function note_update(file_id, note, div_note_id, img_id) {
	src_save = document.getElementById(img_id).src;
	document.getElementById(img_id).src = "application/views/img/loading.gif";
	$.ajax({
		type: 'POST',
//		async: false,
		url: 'index.php',
		data: {"action":"ajax", "sub_action":"note_update", "note":note, "file_id":file_id},
		dataType: 'json',
		success: function (data) {
			document.getElementById(img_id).src = src_save;
			document.getElementById(div_note_id).innerHTML = data.note;
		},
		 error: function (xhr, status) {
			document.getElementById(img_id).src = src_save;
		      switch (status) {
			 case 404:
			     alert('File not found');
			     break;
			 case 500:
			     alert('Server error');
			     break;
			 case 0:
			     alert('Request aborted');
			     break;
			 default:
			     alert('Unknown error ' + status);
		     } 
		 }
	});
}

function toggle_click(which, elem, file_id) {
	img_elem = elem.getElementsByTagName("img")[0];
	src_save = img_elem.src;
	img_elem.src = "application/views/img/loading.gif";
	$.ajax({
		type: 'POST',
//		async: false,
		url: 'index.php',
		data: {"action":"ajax", "sub_action":"toggle", "which":which, "file_id":file_id},
		dataType: 'json',
		success: function (data) {
			img_elem.src = data.img;
		},
		 error: function (xhr, status) {
			img_elem.src = src_save;
		      switch (status) {
			 case 404:
			     alert('File not found');
			     break;
			 case 500:
			     alert('Server error');
			     break;
			 case 0:
			     alert('Request aborted');
			     break;
			 default:
			     alert('Unknown error ' + status);
		     } 
		 }
	});
}



$('.info_update').submit(function(event) {
	event.preventDefault();
	$.ajax({
		type: 'POST',
		url: 'index.php',
		data: $(this).serialize(),
		dataType: 'json',
		success: function (data) {
			alert(data.msg);
		},
		 error: function (xhr, status) {
		      switch (status) {
			 case 404:
			     alert('File not found');
			     break;
			 case 500:
			     alert('Server error');
			     break;
			 case 0:
			     alert('Request aborted');
			     break;
			 default:
			     alert('Unknown error ' + status);
		     } 
		 }
	});

});
</script>


