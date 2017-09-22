<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class My_utils extends CI_Model {
        public function __construct()
        {
                parent::__construct();
        }

	public function is_https()
	{
		return isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on';
	}

	public function use_browser_cache($timeout)
	{
		if ($this->is_https())
			return false;
		pr_debug(print_r($_SERVER, true));
		if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
			//			$if_modified_since = preg_replace('/;.*$/', '',   $_SERVER['HTTP_IF_MODIFIED_SINCE']);
			$if_modified_since = strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']);
			pr_debug(sprintf("small_view: if_modified_since: %d, time: %d", $if_modified_since, time() ));
			if (time() - $timeout > strtotime($if_modified_since)) {
				pr_debug("send 304");
				header("HTTP/1.0 304 Not Modified");
				return true;
			}else {
				pr_debug("need refresh");
			}
		}

		$mtime = time();
		$gmdate_mod = gmdate('D, d M Y H:i:s', $mtime) . ' GMT';
		header('Cache-Control: public, max-age='.$timeout);
		header("Last-Modified: $gmdate_mod");
		header('Expires: '.gmdate('D, d M Y H:i:s', $mtime+$timeout) . ' GMT');
		header_remove('Pragma');
		//header('ETag: "'.time().'"');
		header('ETag: "12345"');
		pr_debug(sprintf("small_view: set timeout: timeout %d, time: %d", $timeout, time() ));
		return false;
	}

	public function jump_prev_page()
	{
		if (!isset($_SESSION['REQUEST_URI']))
			$_SESSION['REQUEST_URI'] = "";
			
		echo '<html><head><meta http-equiv="refresh" content="0;url=./';
		echo str_replace('/familycloud/', '', $_SESSION['REQUEST_URI']);
		echo '"></head></html>';
	}

	public function jump_cur_page()
	{
		if (!isset($_SESSION['REQUEST_URI']))
			$_SESSION['REQUEST_URI'] = "";

		echo '<html><head><meta http-equiv="refresh" content="0;url=/';
		echo str_replace('/familycloud/', '', $_SERVER['REQUEST_URI']);
		echo '/"></head></html>';
	}
}
