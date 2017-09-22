<?php

function prof_flag($str)
{
	global $prof_timing, $prof_names;
	$prof_timing[] = microtime(true);
	$prof_names[] = $str;
}

// Call this when you're done and want to see the results
function prof_print()
{
	global $prof_timing, $prof_names;
	$size = count($prof_timing);
	for($i=0;$i<$size - 1; $i++)
	{
		error_log(sprintf("%s --- %f\n", $prof_names[$i], $prof_timing[$i+1]-$prof_timing[$i]), 3, "php.trace");

		//echo "<b>{$prof_names[$i]}</b><br>";
		//echo sprintf("&nbsp;&nbsp;&nbsp;%f<br>", $prof_timing[$i+1]-$prof_timing[$i]);
	}
	//echo "<b>{$prof_names[$size-1]}</b><br>";
}

//sleep(5);
date_default_timezone_set('Asia/Shanghai');

//set_time_limit(15*60);

//sleep(31);

//echo "=====dump POST start";
//print_r($_POST);
//echo "=====dump POST end";

prof_flag("entry");

error_log(sprintf("!!!!!!!!! enter at %s !!!!!!!!!!!\n", date("H:i:s")), 3, "php.log");

error_log(print_r($_POST, true), 3, "php.log");

$tocken0 = "";
$tocken1 = "";
$data = "";
$error = 1;
$log = "";
$lastImageFileTime = 0;
$lastVideoFileTime = 0;
$lastLocalIdentifier = "";
$lastUploadFileType = "";
$lastUploadOffset = 0;
$lastUploadLength = 0;
$lastUploadTotalLength = 0;

$upload_dir = "uploads/";
if (!file_exists($upload_dir)) {
	@mkdir($upload_dir, 0777, true);
	@mkdir($upload_dir.'/video', 0777, true);
	@mkdir($upload_dir.'/image', 0777, true);
}

if (!file_exists($upload_dir)) {
	$log = (sprintf("%s not exist", $upload_dir));
	goto error_exit;
}

$file_context = sprintf('%s/save.json', $upload_dir);
if (file_exists($file_context)) {
	$context_restore = json_decode(file_get_contents($file_context), TRUE);
	if (isset($context_restore['lastLocalIdentifier']))
		$lastLocalIdentifier = $context_restore['lastLocalIdentifier'];
	if (isset($context_restore['lastUploadFileType']))
		$lastUploadFileType = $context_restore['lastUploadFileType'];
	if (isset($context_restore['lastUploadOffset']))
		$lastUploadOffset = $context_restore['lastUploadOffset'];
	if (isset($context_restore['lastUploadLength']))
		$lastUploadLength = $context_restore['lastUploadLength'];
	if (isset($context_restore['lastUploadTotalLength']))
		$lastUploadTotalLength = $context_restore['lastUploadTotalLength'];
	if (isset($context_restore['lastImageFileTime']))
		$lastImageFileTime = $context_restore['lastImageFileTime'];
	if (isset($context_restore['lastVideoFileTime']))
		$lastVideoFileTime = $context_restore['lastVideoFileTime'];

	error_log("load context\n\r", 3, "php.log");
	error_log(print_r($context_restore, true), 3, "php.log");
}

if (isset($_GET['transfer_id']))
	$transfer_id = $_GET['transfer_id'];
if (isset($_GET['command']))
	$command = $_GET['command'];
if (isset($_GET['tocken0']))
	$tocken0 = $_GET['tocken0'];
if (isset($_GET['tocken1']))
	$tocken1 = $_GET['tocken1'];
if (isset($_GET['lastLocalIdentifier']))
	$lastLocalIdentifier = $_GET['lastLocalIdentifier'];
if (isset($_GET['lastUploadFileType']))
	$lastUploadFileType = $_GET['lastUploadFileType'];
if (isset($_GET['lastUploadOffset']))
	$lastUploadOffset = $_GET['lastUploadOffset'];
if (isset($_GET['lastUploadLength']))
	$lastUploadLength = $_GET['lastUploadLength'];
if (isset($_GET['lastUploadTotalLength']))
	$lastUploadTotalLength = $_GET['lastUploadTotalLength'];


if (isset($_POST['transfer_id']))
	$transfer_id = $_POST['transfer_id'];
if (isset($_POST['command']))
	$command = $_POST['command'];
if (isset($_POST['tocken0']))
	$tocken0 = $_POST['tocken0'];
if (isset($_POST['tocken1']))
	$tocken1 = $_POST['tocken1'];
if (isset($_POST['lastLocalIdentifier']))
	$lastLocalIdentifier = $_POST['lastLocalIdentifier'];
if (isset($_POST['lastUploadFileType']))
	$lastUploadFileType = $_POST['lastUploadFileType'];
if (isset($_POST['lastUploadOffset']))
	$lastUploadOffset = $_POST['lastUploadOffset'];
if (isset($_POST['lastUploadLength']))
	$lastUploadLength = $_POST['lastUploadLength'];
if (isset($_POST['lastUploadTotalLength']))
	$lastUploadTotalLength = $_POST['lastUploadTotalLength'];

if (!isset($transfer_id)) {
	$transfer_id = "";
	$log = "please set transfer_id";
	goto error_exit;
}

if (!isset($command)) {
	$command = "";
	$log = "please set command";
	goto error_exit;
}

switch($command) {
	case "ShortBeat":
	case "LongBeat":
		sleep(181);
		$error = 0;
		break;
	case "heartbeat":
		sleep(15);
		$error = 0;
		break;
	case "handshakeWithToken":
		sleep(10);
		$error = 0;
		break;
	case "delay":
		sleep(3);
		$error = 0;
		break;
	case "upload_file":
		if (!isset($_POST['file_time'])) {
			$log = "please set file_time";
			goto error_exit;
		}

		if (!isset($_POST['file_name'])) {
			$log = "please set file_name";
			goto error_exit;
		}
		$file_name = $_POST['file_name'];

		if (!isset($_FILES['userfile']) || !isset($_FILES['userfile']['tmp_name'])) {
			$log = "please set userfile";
			goto error_exit;
		}

		prof_flag(sprintf("file: %s, file_size: %d\n", $file_name, $lastUploadLength));

		prof_flag("verify file size");
		if (@filesize($_FILES['userfile']['tmp_name']) != $lastUploadLength) {
			$log = "offset error";
			goto error_exit;
		}

		prof_flag("file_md5");
		$local_md5 = md5_file($_FILES['userfile']['tmp_name']);
		$remote_md5 = $_POST['file_md5'];

		if ($local_md5 != $remote_md5) {
			$log = "md5 error";
			goto error_exit;
		}

		if ($lastUploadFileType == "image") {
			$file_name = sprintf('%s/image/%s', $upload_dir, $file_name);
		}else if ($lastUploadFileType == "video") {
			$file_name = sprintf('%s/video/%s', $upload_dir, $file_name);
		}else {
			$log = "lastUploadFileType error";
			goto error_exit;
		}
		$file_tmp = sprintf('%s/tmp.bin', $upload_dir);

		if ($lastUploadOffset == 0) {
			prof_flag("file_move");
			if ($lastUploadLength == $lastUploadTotalLength) {
				// whole file upload
				if (!@move_uploaded_file($_FILES['userfile']['tmp_name'], $file_name)) {
					$log = "move to upload folder error";
					goto error_exit;
				}
				if ($lastUploadFileType == "image") {
					$lastImageFileTime = $_POST['file_time'];
				}
				if ($lastUploadFileType == "video") {
					$lastVideoFileTime = $_POST['file_time'];
				}
			}else {
				// first chunk upload
				if (!@move_uploaded_file($_FILES['userfile']['tmp_name'], $file_tmp)) {
					$log = "move to tmp.bin error";
					goto error_exit;
				}
			}
		}else {
			// next chunk upload
			prof_flag("file_size");
			if (@filesize($file_tmp) != $lastUploadOffset) {
				$log = "offset error";
				goto error_exit;
			}
			prof_flag("file_combine");
			if (@file_put_contents($file_tmp, @file_get_contents($_FILES['userfile']['tmp_name']), FILE_APPEND) != $lastUploadLength) {
				$log = "append to file error";
				goto error_exit;
			}
			if ($lastUploadOffset + $lastUploadLength > $lastUploadTotalLength) {
				$log = "file size > total size";
				goto error_exit;
			}
			if ($lastUploadOffset + $lastUploadLength == $lastUploadTotalLength) {
				prof_flag("file_rename");
				if (!@rename($file_tmp, $file_name)) {
					$log = "move tmp.bin to upload folder error";
					goto error_exit;
				}
				if ($lastUploadFileType == "image") {
					$lastImageFileTime = $_POST['file_time'];
				}
				if ($lastUploadFileType == "video") {
					$lastVideoFileTime = $_POST['file_time'];
				}
			}
		}
		$log ="upload file success: ".$file_name;
		$error = 0;
		break;
	case "query":
		$log = "query last info";
		$error = 0;
		break;
	default:
		$log = "unknown command";
		break;
}

error_exit:
if ($error == 0 && $command == "upload_file") {
	prof_flag("file upload success, context_save");
	error_log("save context\n\r", 3, "php.log");
	$context_save['lastLocalIdentifier'] = $lastLocalIdentifier;
	$context_save['lastUploadFileType'] = $lastUploadFileType;
	$context_save['lastUploadOffset'] = $lastUploadOffset;
	$context_save['lastUploadLength'] = $lastUploadLength;
	$context_save['lastUploadTotalLength'] = $lastUploadTotalLength;
	$context_save['lastImageFileTime'] = $lastImageFileTime;
	$context_save['lastVideoFileTime'] = $lastVideoFileTime;
	file_put_contents($file_context, json_encode($context_save));
}

if ($error != 0 && $command == "upload_file") {
	prof_flag("context_save");
	error_log("file upload error, reset context\n\r", 3, "php.log");
	$context_save['lastLocalIdentifier'] = "unknown";
	$context_save['lastUploadFileType'] = $lastUploadFileType;
	$context_save['lastUploadOffset'] = 0;
	$context_save['lastUploadLength'] = 0;
	$context_save['lastUploadTotalLength'] = 0;
	$context_save['lastImageFileTime'] = $lastImageFileTime;
	$context_save['lastVideoFileTime'] = $lastVideoFileTime;
	file_put_contents($file_context, json_encode($context_save));
}

$result_xml = <<<__EOF__
<return>
	<transfer_id>$transfer_id</transfer_id>
	<tocken0>$tocken0</tocken0>
	<tocken1>$tocken1</tocken1>
	<data>$data</data>
	<log>$log</log>
	<error>$error</error>
	<command>$command</command>
	<lastImageFileTime>$lastImageFileTime</lastImageFileTime>
	<lastVideoFileTime>$lastVideoFileTime</lastVideoFileTime>
	<lastLocalIdentifier>$lastLocalIdentifier</lastLocalIdentifier>
	<lastUploadFileType>$lastUploadFileType</lastUploadFileType>
	<lastUploadOffset>$lastUploadOffset</lastUploadOffset>
	<lastUploadLength>$lastUploadLength</lastUploadLength>
	<lastUploadTotalLength>$lastUploadTotalLength</lastUploadTotalLength>
</return>
__EOF__;

echo $result_xml;


error_log(print_r($result_xml, true), 3, "php.log");
error_log("\n\r", 3, "php.log");

error_log(sprintf("!!!!!!!!! exit at %s !!!!!!!!!!!\n", date("H:i:s")), 3, "php.log");

prof_flag("exit");
prof_print();

?>
