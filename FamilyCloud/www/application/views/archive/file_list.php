<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<?php $this->load->view('header');?>

<?php if ($page_action != "none") $this->load->view('page_next');?>
<?php $this->load->view('file_browse');?>
<?php if ($page_action != "none") $this->load->view('page_next');?>
<?php $this->load->view('file_browse_js');?>

<?php $this->load->view('footer');?>
