<?php
$file = "./test.txt";
 
header("X-Sendfile: $file");
header("Content-type: application/octet-stream");
header('Content-Disposition: attachment; filename="' . basename($file) . '"');

?>

