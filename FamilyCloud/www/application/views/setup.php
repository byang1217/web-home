<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<?php $this->load->view('header');?>

<?php
echo <<<__EOF__
<script>
function toggle_server_url_auto() {
	server_url_input = document.getElementById("server_url_input")
	server_url_auto_check = document.getElementById("server_url_auto_check")
	if (document.getElementById("server_url_auto_check").checked) {
		document.getElementById("server_url_input").value = "$auto_server_url"
		document.getElementById("server_url_input").readOnly = true;
	}else {
		document.getElementById("server_url_input").readOnly = false;
	}
}
</script>
<div class="setup_page">
<form action='index.php' method="post">
<input type="hidden" name="action" value="update_setup" />
	<br>
	<div class="page_line_hl">Setup:</div>
	<div>Title:</div>
	<div><input style="width:100%;" type="text" name="page_title" value="$page_title" /></div>
	<div>Server URL: &nbsp;&nbsp;  Auto Detect<input id="server_url_auto_check" name='auto_detect' type="checkbox" value="y" onclick="toggle_server_url_auto()" /></div>
	<div><input id="server_url_input" style="width:100%;" type="text" name="server_url" value="$server_url" /></div>
	<div><input style="float:right;width:30%;" name="submit" type="submit" value="OK" /></div>
	<br>
</form>

__EOF__;
?>

<div class="page_separator"><img width="100%" height="50%" src="application/views/img/separator.jpg"></img></div>

<form action='index.php' method="post">
<input type="hidden" name="action" value="new_user" />
	<br>
	<div class="page_line_hl">Create New User</div>
	<div>Name: </div>
	<div><input style="width:100%" type="text" name="user_name" value="" /></div>
<?php
if ($is_first_setup) {
	echo <<<__EOF__
	<div> First Setup, User should be Admin <input name="is_admin" type="checkbox" value="y" checked onclick="return false;" onkeydown="e = e || window.event; if(e.keyCode !== 9) return false;"/></div>
__EOF__;
}else {
	echo <<<__EOF__
	<div> Admin? <input name="is_admin" type="checkbox" value="y" /></div>
__EOF__;
}
?>
	<div><input style="float:right;width:30%;" name="submit" type="submit" value="OK" /></div>
	<br>
</form>

<div class="page_separator"><img width="100%" height="50%" src="application/views/img/separator.jpg"></img></div>

<div class="page_line_hl">User List</div>
<?php
foreach ($this->My_user->all_users() as $user) {
	$user_id = $user->id;
	$user_name = $user->name;
	$user_is_admin = $user->is_admin == "y" ? "Admin User" : "Normal User";
	$user_otp = $this->My_user->gen_user_otp($user_id, 600);
	$qr_json = json_encode(array(
					'C' => 'b',
					'L' => $server_url,
					'O' => $user_otp));
	$qr_json_b64 = base64_encode($qr_json);
	echo <<<__EOF__
	<div><a href="javascript:void(0);" onclick="view_toggle($user_id)">$user_name ($user_is_admin): <== Click Me to bind new phone</a></div>
	<div id="$user_id" style="display:none;"></div>
	<script>
		jQuery(function(){
			jQuery('#$user_id').qrcode("$qr_json_b64");
		})
	</script>
	<br>

__EOF__;
}
?>

<?php $this->load->view('footer');?>
