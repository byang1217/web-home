<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>

<script>
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


