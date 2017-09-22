<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<?php $this->load->view('myhome/header');?>

<?php 
if (isset($file_browser_quick_mode) && $file_browser_quick_mode) {
	$this->load->view('myhome/folder_browse');
	$this->load->view('myhome/quick_file_browse');
}else {
	if ($page_action != "none") $this->load->view('myhome/page_next');
	$this->load->view('myhome/folder_browse');
	$this->load->view('myhome/file_browse');
	if ($page_action != "none") $this->load->view('myhome/page_next');
	$this->load->view('myhome/file_browse_js');
}

?>

<?php $this->load->view('myhome/footer');?>
