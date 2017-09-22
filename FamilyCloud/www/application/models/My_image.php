<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class My_image extends CI_Model {
        public function __construct()
        {
                parent::__construct();
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
}

