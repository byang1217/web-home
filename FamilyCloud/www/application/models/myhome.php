<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class myhome extends CI_Model {
	public $cur_dir = '.';
	public $year;
	public $month;
	public $day;
	public $share_mode = false;
	public $num_per_page;
	public $mirror_enable;
	public $local_mirror_num;
	public $local_mirror_enable;

	public $data = array();

        public function __construct()
        {
                parent::__construct();
        }

	public function log($msg)
	{
		log_message('info', "[myhome] ".$msg);
	}

	public function debug()
	{
		echo "hello, it is debug function";
	}

	public function redirect_default()
	{
		$this->data['redirection'] = sprintf("<meta http-equiv=\"refresh\" content=\"0;url=%s\">", site_url());
	}


	public function load_view($v)
	{
		if (isset($_GET['show_mode']))
			$this->data['show_mode'] = $_GET['show_mode'];
		$this->data['share_mode'] = $this->share_mode;
		if ($this->has_admin_access() || file_exists("debug_admin"))
			$this->data['support_admin_mode'] = true;
		else
			$this->data['support_admin_mode'] = false;
		$this->data['admin_mode'] = $_SESSION['admin_mode'];
		if (isset($_SESSION['pending_alert_msg'])) {
			$this->data['alert_msg'] = $_SESSION['pending_alert_msg'];
			unset($_SESSION['pending_alert_msg']);
		}
		if (isset($_SESSION['file_browser_quick_mode']))
			$this->data['file_browser_quick_mode'] = $_SESSION['file_browser_quick_mode'];
		else
			$this->data['file_browser_quick_mode'] = false;

		$this->data['cur_dir'] = $this->cur_dir;
		$this->load->view($v, $this->data);
	}

	public function login_auth($answer)
	{
		if (preg_match("/kasdkfjesa12313asldfkjs/i", $answer)) {
			$_SESSION['login'] = true;
			$_SESSION['user_access'] = 1;
		}
	}

	public function has_login()
	{
		return $_SESSION['login'];
	}

	public function has_admin_access()
	{
		return $_SESSION['login'] && $_SESSION['user_access'] > 3;
	}

	public function alert($msg)
	{
		$_SESSION['pending_alert_msg'] = $msg;
	}

	public function update_all($num_per_page)
	{
//		error_reporting(0);
//open it for debug		
		error_reporting(E_ALL);
		$this->output->enable_profiler(TRUE);

//		$myhome_session_dir = "session/";
//		$timeout = 31*24*3600;

//		ini_set('session.cookie_lifetime', $timeout);
//		ini_set('session.gc_maxlifetime', $timeout);
//		ini_set('session.save_path', $myhome_session_dir);
//		date_default_timezone_set('Asia/Shanghai');

		session_start();
		if (!isset($_SESSION['login'])) {
			$_SESSION['admin_mode'] = false;
			$_SESSION['login'] = false;
			$_SESSION['user_access'] = -1;
			$_SESSION['user_id'] = -1;
			$_SESSION['user_name'] = "未知";
			$_SESSION['user_display_name'] = "未知";
			$_SESSION['user_group'] = "unknown";

			
		}

		if (!isset($_SESSION['admin_mode']))
			$_SESSION['admin_mode'] = false;
		$this->data['page_title'] = "Family Cloud";
		$this->data['login_name'] = $_SESSION['user_name'];

		$this->num_per_page = $num_per_page;

		$this->mirror_enable = false;
		$this->local_mirror_enable = false;
		$this->local_mirror_num = file_get_contents("local_mirror_num");
	}

	public function mode_switch()
	{
		if ($_SESSION['admin_mode']) {
			$_SESSION['admin_mode'] = false;
			return;
		}

		if ($this->has_admin_access()) {
			$_SESSION['admin_mode'] = true;
		}else {
			$this->alert("您没有权限这么做");
		}
	}

	public function is_admin_mode()
	{
		if ($this->has_admin_access() && isset($_SESSION['admin_mode']))
			return $_SESSION['admin_mode'];
		return false;
	}

	public function show_title_in_file_browser()
	{
		$this->data['show_title_in_file_browser'] = true;
	}

	public function file_browse_edit_mode()
	{
		$this->data['file_browse_edit_mode'] = true;
	}

	public function path_to_dir($path)
	{
		$path = preg_replace('/[\\\\\\/]$/', '', $path);
		return preg_replace('/[\\\\\\/][^\\\\\\/]+$/', '', $path);
	}

	public function path_to_base($path)
	{
		$path = preg_replace('/[\\\\\\/]$/', '', $path);
		return preg_replace('/^.+[\\\\\\/]/', '', $path);
	}

	public function ch_dir($path)
	{
		$dir = $this->path_to_dir($path);
		$base = $this->path_to_base($path);

		$this->log(sprintf("ch_dir: dir=%s, base=%s\n", $dir, $base));
		if (preg_match("/\.\./", $dir)) {
			$this->log("invalid dir name ".$dir);
			return;
		}
		if ($base == '..') {
			$this->cur_dir = preg_replace('/[\\\\\\/][^\\\\\\/]+$/', '', $dir);
		} else {
			$this->cur_dir = sprintf("%s/%s", $dir, $base);
		}
		$this->log(sprintf("cur_dir: %s\n", $this->cur_dir));
	}

	public function file_ext($path)
	{
		$ext = end(explode(".", $path));
		
		if (preg_match("/\.jpg$/i", $path))
			return 'jpg';
		elseif (preg_match("/\.png$/i", $path))
			return 'png';
		elseif (preg_match("/\.bmp$/i", $path))
			return 'bmp';
		elseif (preg_match("/\.gif$/i", $path))
			return 'gif';
		elseif (preg_match("/\.mp4$/i", $path))
			return 'mp4';
		return $ext;
	}

	public function is_img_file($file)
	{
		if (preg_match("/\.jpg$/i", $file)
			|| preg_match("/\.png$/i", $file)
			|| preg_match("/\.bmp$/i", $file)
			|| preg_match("/\.gif$/i", $file)) {
			return true;
		}
		return false;
	}

	public function is_video_file($file)
	{
		if (preg_match("/\.mp4$/i", $file))
			return true;
		return false;
	}

	public function get_file_page_and_title($file_no)
	{
		$page_num = 0;
		$order = 'asc';
		$index = 0;
		$title = "";
		$query = $this->db->get_where('file', array('id' => $file_no));
		$date = $query->row()->date;
		if ($query->num_rows() == 1) {
			$title = $query->row()->title;
			$this->db->select("count('id') as count");
			$this->db->where('title', $title);
			$this->db->where('is_new', "n");
			$this->db->where('date < ', $date);
			$this->db->order_by('date', $order);
			$query = $this->db->get('file');
			//$index = $query->num_rows();
			$index = $query->row()->count;

			$this->db->where('title', $title);
			$this->db->where('is_new', "n");
			$this->db->where('date', $date);
			$this->db->order_by('date', $order);
			$query = $this->db->get('file');
			foreach ($query->result() as $row) {
				$index ++;
				if ($row->id == $file_no)
					break;
			}
		}
		$page_num = floor($index / $this->num_per_page);

		return array($page_num, $title);
	}

	public function is_title_pub($title)
	{
		$query = $this->db->get_where('group', array('title' => $title, 'pub' => "y"));
		return $query->num_rows() == 1;
	}

	public function get_title_file_num($title)
	{
		$query = $this->db->get_where('file', array('title' => $title, 'is_new' => "n"));
		return $query->num_rows();
	}

	public function get_title_files($title, $num, $offset, $order)
	{
		$file_list = array();

		$num = $num + 2;
		if ($offset > 0)
			$offset = $offset - 1;
		$this->db->like('title', $title);
		$this->db->where('is_new', "n");
		$this->db->order_by('date', $order);
		$query = $this->db->get('file', $num, $offset);
		$this->log(sprintf("get_title_files: title=%s, num=%d, offset=%d, rows=%d\n",
				$title, $num, $offset, $query->num_rows()));
		foreach ($query->result() as $row) {
			if ($num <= 0)
				break;
			array_push($file_list, $row->id);
			$num --;
			$offset ++;
		}

		$this->log(sprintf("get_title_files: res=%s\n",
				print_r($file_list, true)));
		return $file_list;
	}


	public function search_file_month($year, $month, $order)
	{
		$file_list = array();

		for ($day=1; $day<=31; $day++) {
			$this->db->like('date', sprintf("%04s-%02s-%02d", $year, $month, $day));
			$this->db->where_not_in('note', '');
			$this->db->where('is_new', "n");
			$this->db->order_by('date', $order);
			$query = $this->db->get('file', 1);
			if ($query->num_rows() == 1) {
				$row = $query->result()[0];
				array_push($file_list, $row->id);
				continue;
			}
			$this->db->like('date', sprintf("%04s-%02s-%02d", $year, $month, $day));
			$this->db->where('is_new', "n");
			$this->db->order_by('date', $order);
			$query = $this->db->get('file', 1);
			if ($query->num_rows() == 1) {
				$row = $query->result()[0];
				array_push($file_list, $row->id);
			}
		}
		return $file_list;
	}

	public function search_file_date($date, $order)
	{
		$file_list = array();

		$this->db->like('date', $date);
		$this->db->where('is_new', "n");
		$this->db->order_by('date', $order);
		$this->db->order_by('title', $order);
		$query = $this->db->get('file');
		foreach ($query->result() as $row) {
			array_push($file_list, $row->id);
		}
		return $file_list;
	}

	public function search_file_info($keyw, $num, $offset, $order, $star)
	{
		$file_list = array();
		$this->log(sprintf("search: keyw = %s, num = %d, offset = %d, order = %s, star = %s\n", $keyw, $num, $offset, $order, $star));
		$keys = preg_split("/[\s,]+/", $keyw);

		$num ++;

		if ($star != "y") {
			$this->log(sprintf("before search in title, offset = %d, num = %d\n", $offset, $num));
			foreach ($keys as $key)
				$this->db->like('title', $key);
			$this->db->order_by('id', $order);
			$query = $this->db->get('group', $num, $offset);
			foreach ($query->result() as $row) {
				if ($num <= 0)
					return $file_list;
				array_push($file_list, $row->file_id);
				$num --;
				$offset = 0;
			}
			if ($offset != 0) {
				$this->db->select("count('id') as count");
				foreach ($keys as $key)
					$this->db->like('title', $key);
				$query = $this->db->get('group');
				$offset -= $query->row()->count;
				$this->log(sprintf("offset=%d, title count=%d\n", $offset, $query->row()->count));
				if ($offset < 0)
					$offset = 0;
			}
			$this->log(sprintf("after search in title, offset = %d, num = %d\n", $offset, $num));
		}


		if (preg_match("/[0-9,-]+/", $keyw, $res) && ($res[0] == $keyw)) {
			$this->log(sprintf("before search in date, offset = %d, num = %d\n", $offset, $num));
			$this->db->like('date', $res[0]);
			$this->db->like('star', $star);
			$this->db->where('is_new', "n");
			$this->db->order_by('date', $order);
			$query = $this->db->get('file', $num, $offset);
			foreach ($query->result() as $row) {
				if ($num <= 0)
					return $file_list;
				array_push($file_list, $row->id);
				$num --;
				$offset = 0;
			}
			if ($offset != 0) {
				$this->db->select("count('id') as count");
				$this->db->like('date', $res[0]);
				$this->db->like('star', $star);
				$this->db->where('is_new', "n");
				$query = $this->db->get('file');
				$offset -= $query->row()->count;
				$this->log(sprintf("offset=%d, date count=%d\n", $offset, $query->row()->count));
				if ($offset < 0)
					$offset = 0;
			}
			$this->log(sprintf("after search in date, offset = %d, num = %d\n", $offset, $num));
		}
		
		foreach (array('note', 'path') as $field) {
			$this->log(sprintf("before search in %s, offset = %d, num = %d\n", $field, $offset, $num));
			foreach ($keys as $key)
				$this->db->like($field, $key);
			$this->db->like('star', $star);
			$this->db->where('is_new', "n");
			$this->db->order_by('date', $order);
			$query = $this->db->get('file', $num, $offset);
			foreach ($query->result() as $row) {
				if ($num <= 0)
					return $file_list;
				array_push($file_list, $row->id);
				$num --;
				$offset = 0;
			}
			if ($offset != 0) {
				$this->db->select("count('id') as count");
				foreach ($keys as $key)
					$this->db->like($field, $key);
				$this->db->like('star', $star);
				$this->db->where('is_new', "n");
				$query = $this->db->get('file');
				$offset -= $query->row()->count;
				$this->log(sprintf("offset=%d, date count=%d\n", $offset, $query->row()->count));
				if ($offset < 0)
					$offset = 0;
			}
			$this->log(sprintf("after search in %s, offset = %d, num = %d\n", $field, $offset, $num));
		}
//		$this->myhome->log(print_r($file_list, true));
		return $file_list;
	}

	public function show_file_list($file_list, $page_num, $page_action, $page_data, $page_data2 = "")
	{
		$this->data['file_list'] = $file_list;
		if ($page_num == 0 && $this->num_per_page > count($file_list)) {
			$this->data['page_action'] = "none";
		}else {
			$this->data['page_action'] = $page_action;
			$this->data['page_data'] = $page_data;
			$this->data['page_data2'] = $page_data2;
			$this->data['page_num'] = $page_num + 1;
		}
	}

	public function list_dir($path, $offset, $num)
	{
		$this->log("enter list_dir\n");
		$path = preg_replace('/^\.[\\\\\\/]/', '', $path);
		
		$this->data['dir_list'] = array("..",);
//		$this->data['file_list'] = array();
		$file_list = array();
		$return_file_list = array();

		$myhome_dir = TOPCFG_PHOTOS_DIR;
		$local_path = sprintf("%s/%s/%s", $myhome_dir, 'src', $path);
		$this->log("local_path=".$local_path);

		if (!is_dir($local_path)) {
			$this->log($local_path." is not a dir\n");
			return false;
		}

		if (($dir = opendir($local_path)) == false) {
			$this->log("open dir error");
			return false;
		}

		while (($file = readdir($dir)) !== false){
			$local_file = sprintf("%s/%s", $local_path, $file);
			$this->log("check file: local_file=".$local_file);
			if (preg_match("/^\./", $file)) {
				$this->log("ignore ".$file);
				continue;
			}
			if (is_dir($local_file))
				array_push($this->data['dir_list'], $file);
			else
				array_push($file_list, $file);
		}
		closedir($dir);

/*sort and process all files first*/
		sort($file_list);
		for ($i = 0; $i < count($file_list); $i++) {
			$file = $file_list[$i];
			
/*
			$file_path = preg_replace('/^\.[\\\\\\/]/', '', $file);
			$query = $this->db->get_where('file', array('path' => $file_path));
			if ($query->num_rows() == 1)
				continue;
*/
			
			if ($this->is_img_file($file)) {
				$this->log(sprintf("%s is image file, prepare small and thumb", $file));
				$this->img_save_and_resize(sprintf("%s/%s", $path, $file));
			} elseif ($this->is_video_file($file)) {
				$this->log(sprintf("%s is video file", $file));
				$this->video_save_and_resize(sprintf("%s/%s", $path, $file));
			} else {
				$this->log(sprintf("%s is unknown file, ignore it", $file));
			}

		}

/**/

		if ($_SESSION['file_browser_show_order_reverse']) {
			rsort($file_list);
			rsort($this->data['dir_list']);
		}else {
			sort($file_list);
			rsort($this->data['dir_list']);
		}

		for ($i = 0; $i < count($file_list); $i++) {
			if (count($return_file_list) >= $num)
				break;
			$file = $file_list[$i];
			
/*
			if ($this->is_img_file($file)) {
				$this->log(sprintf("%s is image file, prepare small and thumb", $file));
				$this->img_save_and_resize(sprintf("%s/%s", $path, $file));
			} elseif ($this->is_video_file($file)) {
				$this->log(sprintf("%s is video file", $file));
				$this->video_save_and_resize(sprintf("%s/%s", $path, $file));
			} else {
				$this->log(sprintf("%s is unknown file, ignore it", $file));
			}
*/

			if (isset($_SESSION['file_browser_show_new_only']) && isset($_SESSION['admin_mode'])
				&& $_SESSION['file_browser_show_new_only'] && $_SESSION['admin_mode']) {
				$query = $this->db->get_where('file', array('path' => sprintf("%s/%s", $path, $file), 'is_new' => 'y'));
			}else {
				$query = $this->db->get_where('file', array('path' => sprintf("%s/%s", $path, $file)));
			}

			$this->log("find it in db, ret ".$query->num_rows());
			if ($query->num_rows() == 1) {
				$offset --;
				if ($offset <= 0) {
					$this->log("push file_list, id = ".$query->row()->id);
					array_push($return_file_list, $query->row()->id);
				}
			}
		}
		return $return_file_list;
	}

	public function img_create_thumb($src, $new)
	{
		$thumb_size = $this->config->item('myhome_thumbnail_size');

		list($src_w, $src_h) = getimagesize($src);
		$this->log(sprintf("src_w %d, src_h %d, thumb size %d\n", $src_w, $src_h, $thumb_size));
		$this->log("copy src to thumb\n");
		if (!copy($src, $new)) {
			$this->log("copy error\n");
			return false;
		}
		$this->log("resize src to thumb\n");
		$config['image_library']	= 'gd2';
		$config['source_image']		= $new;
		if ($src_w >= $src_h) {
			$config['width']	= floor($thumb_size * $src_w / $src_h);
			$config['height']	= $thumb_size;
		}else {
			$config['width']	= $thumb_size;
			$config['height']	= floor($thumb_size * $src_h / $src_w);
		}
		$this->image_lib->clear();
		$this->image_lib->initialize($config);
		$this->image_lib->resize();

//		list($tmp_w, $tmp_h) = getimagesize($new);
//		$this->log(sprintf("tmp_w %d, tmp_h %dn", $tmp_w, $tmp_h));

		$this->log("crop middle\n");
		$config['maintain_ratio'] = false;
		$config['width']	= $thumb_size;
		$config['height']	= $thumb_size;
		if ($src_w >= $src_h) {
			$config['x_axis'] = floor(($thumb_size * $src_w / $src_h - $thumb_size)/2);
			$config['y_axis'] = 0;
			$this->log("x_axis = ".$config['x_axis']);
		}else {
			$config['x_axis'] = 0;
			$config['y_axis'] = floor(($thumb_size * $src_h / $src_w - $thumb_size)/2);
			$this->log("y_axis = ".$config['y_axis']);
		}
		$this->image_lib->clear();
		$this->image_lib->initialize($config);
		$this->image_lib->crop();

//		list($tmp_w, $tmp_h) = getimagesize($new);
//		$this->log(sprintf("tmp_w %d, tmp_h %dn", $tmp_w, $tmp_h));

		return true;
	}

	public function img_create_small($src, $new)
	{
		$small_width = $this->config->item('myhome_small_width');

		list($src_w, $src_h) = getimagesize($src);
		$this->log(sprintf("src_w %d, small_w %d\n", $src_w, $small_width));
		$this->log("copy src to small\n");
		if (!copy($src, $new)) {
			$this->log("copy error\n");
			return false;
		}
		if ($src_w > $small_width) {
			$this->log("resize src to small\n");
			$config['image_library']	= 'gd2';
			$config['source_image']		= $new;
			$config['master_dim']		= 'width';
			$config['width']		= $small_width;
			$config['height']		= floor($small_width * $src_h / $src_w);
			$this->image_lib->clear();
			$this->image_lib->initialize($config);
			$this->image_lib->resize();
		}
		return true;
	}

	public function file_path_to_id($path)
	{
		$query = $this->db->get_where('file', array('path' => $path, 'is_new' => "n"));
		if ($query->num_rows() != 1)
			return false;
		return $query->row()->id;
	}

	public function file_id_to_info($file_id, $simple_info_only=false)
	{
		$query = $this->db->get_where('file', array('id' => $file_id));
		if ($query->num_rows() != 1)
			return false;
		$this->log(sprintf("file_id_to_info: id = %d, path = %s\n", $file_id, $query->row()->path));
		if (!(strpos($query->row()->title, '不公开') === false) && !$this->has_admin_access()) {
			$query = $this->db->get_where('file', array('id' => 17483));
			if ($query->num_rows() != 1)
				return false;
		}

		$file_info['star']  = $query->row()->star;
		$file_info['path']  = $query->row()->path;
		$file_info['real_path']  = $query->row()->path;
		$file_info['title']  = $query->row()->title;
		$file_info['note']  = $query->row()->note;
		$file_info['type']  = $query->row()->type;
		$file_info['pub']  = $query->row()->pub;
		$file_info['gps_data']  = $query->row()->gps_data;
		$file_info['country']  = $query->row()->country;
		$file_info['province']  = $query->row()->province;
		$file_info['city']  = $query->row()->city;
		$file_info['district']  = $query->row()->district;
		$file_info['street']  = $query->row()->street;
		$file_info['address']  = $query->row()->address;
		$file_info['file_md5']  = $query->row()->file_md5;
		
		if (!$simple_info_only) {
			$src_file_path = sprintf("myhome/src/%s", $file_info['real_path']);
			$file_info['Orientation'] = 1;
			$file_info['ro_scale'] = 1;
			if ($this->is_img_file($src_file_path)) {
				$exif = @exif_read_data($src_file_path);
				if (isset($exif['Orientation']))
					$file_info['Orientation'] = $exif['Orientation'];
				list($file_info['img_h'], $file_info['img_w']) = getimagesize($src_file_path);
				$file_info['ro_scale'] = $file_info['img_w']/$file_info['img_h'];
	#			$this->log(sprintf("byang_debug 3: w: %d, h: %d, s: %d", $file_info['img_w'], $file_info['img_h'], $file_info['ro_scale']));

	// GPS address
				$this->log("get gps address for".$file_id."\n");
				if ($file_info['address'] == "") {
					$this->log("get address for ".$file_id."\n");
					$gps_info = $this->get_address_from_exif($exif);
					if ($gps_info == false) {
	//					print_r("get address false: ".$file_id."\n");
						$file_info['address'] = "";
					}else if ($gps_info == "none") {
						$file_info['address'] = "none";
					}else {
						$file_info['address'] = $gps_info['address'];
						$file_info['gps_data']  = $gps_info['gps_data'];
						$file_info['country']  =  $gps_info['country'];
						$file_info['province']  = $gps_info['province'];
						$file_info['city']  =  $gps_info['city'];
						$file_info['district']  =  $gps_info['district'];
						$file_info['street']  = $gps_info['street'];
						if ($file_info['country'] == "") {
							$file_info['address'] = "国外";
						}
					}
					if ($file_info['address'] != "") {
	//					print_r("update db: ".$file_id."\n");
						$this->db->from('file');
						$this->db->where('id', $file_id);
						$this->db->update('file',
						array(
							'address'	=> $file_info['address'],
							'gps_data'	=> $file_info['gps_data'],
							'country'	=> $file_info['country'],
							'province'	=> $file_info['province'],
							'city'		=> $file_info['city'],
							'district'	=> $file_info['district'],
							'street'	=> $file_info['street'],
						     )
						);
					}
				}
			}
		}

		if ($query->row()->is_new == "n") {
			$file_info['date']  = $query->row()->date;
			list($page_num, $title) = $this->get_file_page_and_title($file_id);
			$file_info['page_num'] = $page_num;
		}else {
			$file_info['date'] = $this->guess_file_date($file_info['path']);
			$file_info['page_num'] = 0;
		}

		
		$file_md5 = $file_info['file_md5'];
		if ($file_md5 == "") {
			$ext = $this->file_ext($file_info['real_path']);
			$file_md5 = sprintf("%010d_%d_%s.%s",$file_id, $file_id, md5($file_info['path']), $ext);
			$file_path = sprintf("../../%s/%s", "src", $file_info['path']);
			$file_link = sprintf("myhome/files/%s/%s", "src", $file_md5);
			if (!file_exists($file_link)) {
				$ret = exec(sprintf("ln -s \"%s\" \"%s\"", $file_path, $file_link));
			}
			$file_path = sprintf("../../%s/%s", "small", $file_info['path']);
			$file_link = sprintf("myhome/files/%s/%s", "small", $file_md5);
			if (!file_exists($file_link)) {
				$ret = exec(sprintf("ln -s \"%s\" \"%s\"", $file_path, $file_link));
			}
			$file_path = sprintf("../../%s/%s", "thumbnail", $file_info['path']);
			$file_link = sprintf("myhome/files/%s/%s", "thumbnail", $file_md5);
			if ($this->myhome->is_video_file($file_info['path'])) {
				$file_path .= ".gif";
				$file_link .= ".gif";
			}
			if (!file_exists($file_link)) {
				$ret = exec(sprintf("ln -s \"%s\" \"%s\"", $file_path, $file_link));
			}

			$this->db->from('file');
			$this->db->where('id', $file_id);
			$this->db->update('file',
					array(
						'file_md5'	=> $file_md5,
					     )
					);
		}

		$file_info['path']  = $file_md5;
// mirror setup
		$file_info['www']  = "myhome/files";
		if ($this->mirror_enable) {
			$file_info['www']  = $mirror_url."myhome/files";
			if ($this->local_mirror_enable) {
				if ($this->local_mirror_num >= $file_id) {
					$file_info['www']  = $local_mirror_url."myhome/files";
				}
			}
		}

		return $file_info;
	}

	public function guess_file_date($path)
	{
                $myhome_dir = $this->config->item('myhome_dir');
                $path = preg_replace('/^\.[\\\\\\/]/', '', $path);
                $file = sprintf("%s/%s/%s", $myhome_dir, "src", $path);

		$this->log("guess_file_date: ".$file);
		if ($this->is_img_file($file)) {
			$exif = @exif_read_data($file);
//			$this->log(print_r($exif, true));
			if (isset($exif['DateTimeOriginal'])) {
				$num = preg_match("/[1-2][0,1,9][0-9][0-9]:[0-1][0-9]:[0-3][0-9]/", $exif['DateTimeOriginal'], $res1);
				if ($num > 0) {
					$this->log("data in exif: ".$res1[0]);
					return str_replace(":", "-", $res1[0]);
				}
			}
			if (isset($exif['DateTime'])) {
				$num = preg_match("/[1-2][0,1,9][0-9][0-9]:[0-1][0-9]:[0-3][0-9]/", $exif['DateTime'], $res1);
				if ($num > 0) {
					$this->log("data in exif: ".$res1[0]);
					return str_replace(":", "-", $res1[0]);
				}
			}
		}


		$num = preg_match_all("/[1-2][0,1,9][0-9][0-9]-[0-1][0-9]-[0-3][0-9]/", $file, $res);
//		$this->log(print_r($res, true));
		if ($num > 0)
			return $res[0][$num - 1];

		$num = preg_match_all("/[1-2][0,1,9][0-9][0-9][0-1][0-9][0-3][0-9]/", $file, $res);
//		$this->log(print_r($res, true));
		if ($num > 0) {
			$ymd = $res[0][$num - 1];
			$y = substr($ymd, 0, 4);
			$m = substr($ymd, 4, 2);
			$d = substr($ymd, 6, 2);
			return sprintf("%s-%s-%s", $y, $m, $d);
		}

		return "1900-01-01";
//		return date('Y-m-d');
	}

	public function img_save_and_resize($path)
	{
		$img_type = '.gif|.jpeg|.png|.bmp';
		$myhome_dir = $this->config->item('myhome_dir');

		$path = preg_replace('/^\.[\\\\\\/]/', '', $path);
		$local_src_path = sprintf("%s/%s/%s", $myhome_dir, "src", $path);
		$local_thumbnail_path = sprintf("%s/%s/%s", $myhome_dir, "thumbnail", $path);
		$local_small_path = sprintf("%s/%s/%s", $myhome_dir, "small", $path);

		$query = $this->db->get_where('file', array('path' => $path));
		$this->log("num_rows: ".$query->num_rows());
		if ($query->num_rows() > 1) {
			$this->log("more than one records in db, error\n");
			return false;
		}
		if ($query->num_rows() == 1 && file_exists($local_src_path) && file_exists($local_thumbnail_path) && file_exists($local_small_path)) {
			$this->log("img_save_and_resize done already, ignore ".$path);
			return true;
		}

		if (!file_exists($this->path_to_dir($local_thumbnail_path))) {
			$this->log("mkdir: ".$this->path_to_dir($local_thumbnail_path));
			mkdir($this->path_to_dir($local_thumbnail_path), 0777, true);
		}
		if (!file_exists($this->path_to_dir($local_small_path))) {
			$this->log("mkdir: ".$this->path_to_dir($local_small_path));
			mkdir($this->path_to_dir($local_small_path), 0777, true);
		}

		if (!file_exists($local_src_path)) {
			$this->log("no src file");
			return false;
		}
		list($w, $h, $ext) = getimagesize($local_src_path);
		$type = image_type_to_extension($ext);
		$this->log("file type: ".$type);
		if (!stripos($img_type, $type)) {
			$this->log("not img file");
			return false;
		}

		if (!$this->img_create_small($local_src_path, $local_small_path))
			return false;

		if (!$this->img_create_thumb($local_src_path, $local_thumbnail_path))
			return false;

		if ($query->num_rows() == 0) {
			if (!$this->db->insert('file', array('path' => $path, 'type' => 'img')))
				return false;
		}
		$query = $this->db->get_where('file', array('path' => $path));
		$this->file_id_to_info($query->row()->id);

		return true;
	}

	public function video_save_and_resize($path)
	{
		$myhome_dir = $this->config->item('myhome_dir');

		$path = preg_replace('/^\.[\\\\\\/]/', '', $path);
		$local_src_path = sprintf("%s/%s/%s", $myhome_dir, "src", $path);
		$local_thumbnail_path = sprintf("%s/%s/%s.gif", $myhome_dir, "thumbnail", $path);
		$local_small_path = sprintf("%s/%s/%s", $myhome_dir, "small", $path);

		if (!$this->is_video_file($local_src_path))
			return false;

		$this->log("img_save_and_resize ".$path);
		if (!(file_exists($local_src_path))) {
			$this->log("src file missed, ignore ".$path);
			return false;
		}
		if (!(file_exists($local_thumbnail_path))) {
			exec(sprintf("./myhome/ffmpeg -i \"%s\" -y -vf scale=160*160 -t 10 -r 1 \"%s\"",
				$local_src_path, $local_thumbnail_path));
		}
		if (!(file_exists($local_small_path))) {
			exec(sprintf("./myhome/ffmpeg -i \"%s\" -y -vcodec libx264 -acodec libvo_aacenc \"%s\"",
				$local_src_path, $local_small_path));
//			$this->log(sprintf("ln -s \"%s\" \"%s\"", $local_src_path, $local_small_path));
//			exec(sprintf("ln -s \"%s\" \"%s\"", $local_src_path, $local_small_path));
		}
		if (!(file_exists($local_src_path) && file_exists($local_thumbnail_path) && file_exists($local_small_path))) {
			$this->log("file missed, ignore ".$path);
			return false;
		}

		$query = $this->db->get_where('file', array('path' => $path));
		$this->log("num_rows: ".$query->num_rows());
		if ($query->num_rows() > 1) {
			$this->log("more than one records in db, error\n");
			return false;
		}

		if ($query->num_rows() == 1) {
			$this->log("done already, ignore ".$path);
			return true;
		}

		$this->log("insert to db\n");
		if (!$this->db->insert('file', array('path' => $path, 'type' => 'video')))
			return false;

		$query = $this->db->get_where('file', array('path' => $path));
		$this->file_id_to_info($query->row()->id);

		return true;
	}

	public function update_title($title)
	{
		$this->log(sprintf("try to find newest date of title %s in file\n", $title));
		$this->db->where('title', $title);
		$this->db->order_by('date', 'desc');
		$query = $this->db->get('file', 1, 0);
		$this->log("ret ".$query->num_rows());
		$this->log("delete title in group first\n");
		$this->db->delete('group', array('title' => $title)); 
		if ($query->num_rows() == 1) {
			$this->log("insert new title in group, date ".$query->row()->date);
			$this->db->insert('group',
					array(
						'title' => $title,
						'date' => $query->row()->date,
						'file_id' => $query->row()->id,
					     )); 
		}
	}

	public function delete_file($file_id)
	{
		$this->log(sprintf("del file: %d", $file_id));
		$query = $this->db->get_where('file', array('id' => $file_id));
		if ($query->num_rows() != 1) {
			$this->log("cannot find it in db");
			return false;
		}
		if ($query->row()->is_new != "y") {
			$this->log("cannot del old file");
			return false;
		}

		$file_info = $this->file_id_to_info($file_id);
		$this->db->delete('file', array('id' => $file_id));

		$real_path = $file_info['real_path'];
		$path = $file_info['path'];

		$this->log(sprintf("del file: %s", $path));
		if (file_exists("myhome/files/src/".$path))
			unlink("myhome/files/src/".$path);
		if (file_exists("myhome/files/small/".$path))
			unlink("myhome/files/small/".$path);
		if (file_exists("myhome/files/thumbnail/".$path.".gif"))
			unlink("myhome/files/thumbnail/".$path.".gif");
		if (file_exists("myhome/files/thumbnail/".$path))
			unlink("myhome/files/thumbnail/".$path);

		$this->log(sprintf("del file: %s", $real_path));
		if (file_exists("myhome/src/".$real_path))
			unlink("myhome/src/".$real_path);
		if (file_exists("myhome/small/".$real_path))
			unlink("myhome/small/".$real_path);
		if (file_exists("myhome/thumbnail/".$real_path.".gif"))
			unlink("myhome/thumbnail/".$real_path.".gif");
		if (file_exists("myhome/thumbnail/".$real_path))
			unlink("myhome/thumbnail/".$real_path);

		return true;
	}

	public function update_file($file_id, $date, $title, $pub, $star, $note)
	{
		if ($pub != "y")
			$pub = "n";
		if ($star != "y")
			$star = "n";
		$this->log(sprintf("update, file_id:%d, date:%s, title:%s, pub:%s, star:%s, note:%s\n",
					$file_id, $date, $title, $pub, $star, $note));

		$query = $this->db->get_where('file', array('id' => $file_id));
		$this->log("num_rows: ".$query->num_rows());
		if ($query->num_rows() != 1) {
			$this->log("cannot find file in db, update file error\n");
			return false;
		}
		if ($query->row()->is_new == "n")
			$old_title = $query->row()->title;

		$this->db->from('file');
		$this->db->where('id', $file_id);
		$this->db->update('file',
			array(
				'date' => $date,
				'title' => $title,
				'pub' => $pub,
				'star' => $star,
				'note' => $note,
				'is_new' => "n"
			     )
			);

		if (isset($old_title) && $old_title != $title)
			$this->update_title($old_title);
		$this->update_title($title);

		return true;
	}

	function gpsDiv($a)
	{
		// evaluate the string fraction and return a float //	
		$e = explode('/', $a);
		// prevent division by zero //
		if (!$e[0] || !$e[1]) {
			return 0;
		}else{
			return $e[0] / $e[1];
		}
	}

	function gpsDecimal($_deg, $_min, $_sec, $hem) 
	{
		$deg = $this->gpsDiv($_deg);
		$min = $this->gpsDiv($_min);
		$sec = $this->gpsDiv($_sec);
		$d = $deg + ((($min/60) + ($sec/3600)));
		return ($hem=='S' || $hem=='W') ? $d*=-1 : $d;
	}

	function gpsGetData($exif_data)
	{
		if (!isset($exif_data['GPSLongitude']))
			return "";

		$egeoLong = $exif_data['GPSLongitude'];
		$egeoLat = $exif_data['GPSLatitude'];
		$egeoLongR = $exif_data['GPSLongitudeRef'];
		$egeoLatR = $exif_data['GPSLatitudeRef'];

		$geoLong = $this->gpsDecimal($egeoLong[0], $egeoLong[1], $egeoLong[2], $egeoLongR);
		$geoLat = $this->gpsDecimal($egeoLat[0], $egeoLat[1], $egeoLat[2], $egeoLatR);
		if ($geoLong == 0 || $geoLat == 0)
			return false;
		return $geoLat.",".$geoLong;
	}

	function get_address_from_exif($exif_data)
	{
		$ctx = stream_context_create(array( 
		    'http' => array( 
			'timeout' => 3 
			) 
		    ) 
		); 

		if (!isset($exif_data['GPSLongitude']))
			return "none";
		$gps_data = $this->gpsGetData($exif_data);
		if ($gps_data == false)
			return "none";

//		print_r("<br>gps: ".$gps_data);
/* Google map
		$ret = file_get_contents("http://maps.googleapis.com/maps/api/geocode/json?latlng=".$geoLat.",".$geoLong."&sensor=false&language=zh-cn");
		if ($ret == false)
			return false;
		$ret_json = json_decode($ret);
		if (!isset($ret_json->results[1])) {
			return false;
		}
		return $ret_json->results[0]->formatted_address;
*/
		$baidu_gps_convert_url = "http://api.map.baidu.com/geoconv/v1/?from=3&to=5&ak=FC69imYsWGM1yUMHdHQWF7Ia&coords=";
		$baidu_gps_get_addr_url = "http://api.map.baidu.com/geocoder/v2/?ak=FC69imYsWGM1yUMHdHQWF7Ia&output=json&pois=0&location=";
//		print_r($baidu_gps_convert_url.$gps_data);
		$ret = @file_get_contents($baidu_gps_convert_url.$gps_data, 0, $ctx);
		if ($ret == false) {
			return false;
		}
		$baidu_gps_json = json_decode($ret);
//		print_r($baidu_gps_json);
		if (!isset($baidu_gps_json->status) || $baidu_gps_json->status != 0) {
			return "none";
		}
//		print_r($baidu_gps_get_addr_url.$baidu_gps_json->result[0]->x.",".$baidu_gps_json->result[0]->y);
		$ret = @file_get_contents($baidu_gps_get_addr_url.$baidu_gps_json->result[0]->x.",".$baidu_gps_json->result[0]->y, 0, $ctx);
		if ($ret == false) {
			return false;
		}
		$baidu_addr_json = json_decode($ret);
//		print_r($baidu_addr_json);
		if (!isset($baidu_addr_json->status) || $baidu_addr_json->status != 0) {
			return false;
		}

		$info['gps_data']  = $gps_data;
		$info['country']  = $baidu_addr_json->result->addressComponent->country;
		$info['province']  = $baidu_addr_json->result->addressComponent->province;
		$info['city']  = $baidu_addr_json->result->addressComponent->city;
		$info['district']  = $baidu_addr_json->result->addressComponent->district;
		$info['street']  = $baidu_addr_json->result->addressComponent->street;
		$info['address']  = "";
		if ($info['country'] != "中国")
			$info['address'] = $info['address'].$info['country'];
		$info['address'] = $info['address'].$info['province'];
		if ($info['province'] != $info['city'])
			$info['address'] = $info['address'].$info['city'];
		$info['address'] = $info['address'].$info['district'];
		$info['address'] = $info['address'].$info['street'];
		return $info;
	}
}

