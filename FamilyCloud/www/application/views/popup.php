<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>


<script>
</script>

<div id="popup_textarea" class="popup_window" style="display:none">
	<div class="black_overlay" onclick = 'document.body.style.overflow="auto";document.getElementById("popup_textarea").style.display="none"'></div>
	<div class="popup_overlay" >
		<textarea rows="5" class="popup_line" >kaljsdf</textarea>
		<br clear=left>
		<br clear=left>
		<button type="button" class="popup_button" onclick = 'document.body.style.overflow="auto";document.getElementById("popup_textarea").style.display="none"'>Cancel</button>
		<button type="button" class="popup_button" onclick='note_update("$file_id", document.getElementById("$textarea_note_edit_id").value, "$div_note_id", "$img_note_id")'>OK</button>
	</div>
</div>


