<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class My_video extends CI_Model {
        public function __construct()
        {
                parent::__construct();
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
}

