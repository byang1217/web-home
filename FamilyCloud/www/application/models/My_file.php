<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class My_file extends CI_Model {
	public $data = array();
	public $cur_dir = '.';

	public $img_empty = "application/views/img/empty.png";
	public $img_star_y = "application/views/img/star_y.png";
	public $img_star_n = "application/views/img/star_n.png";
	public $img_lock_y = "application/views/img/lock_y.png";
	public $img_lock_n = "application/views/img/lock_n.png";
	public $img_delete_y = "application/views/img/delete_y.png";
	public $img_delete_n = "application/views/img/delete_n.png";
	public $img_bookmark = "application/views/img/bookmark.png";
	public $img_back = "application/views/img/back.png";

        public function __construct()
        {
                parent::__construct();
        }
	
	public function update($file_id, $which, $value)
	{
		$this->db->from('photo');
		$this->db->where('id', $file_id);
		$this->db->update('photo',
				array(
					$which => $value,
				     ));
	}

	public function toggle($file_id, $which)
	{
		$is_which = sprintf("is_%s", $which);
		$query = $this->db->get_where('photo', array('id' => $file_id));
		if ($query->num_rows() != 1)
			return false;
		$is_val = $query->row(0, "array")[$is_which] == "y" ? "n" : "y";
		$this->update($file_id, $is_which, $is_val);
	}

	public function file_id_to_info($file_id)
	{
		$query = $this->db->get_where('photo', array('id' => $file_id));
		if ($query->num_rows() != 1)
			return false;

		$file_info = $query->row(0, "array");
		$file_info['img_star'] = $file_info['is_star'] == "y" ? $this->img_star_y : $this->img_star_n;
		$file_info['img_lock'] = $file_info['is_lock'] == "y" ? $this->img_lock_y : $this->img_lock_n;
		$file_info['img_delete'] = $file_info['is_delete'] == "y" ? $this->img_delete_y : $this->img_delete_n;

		$file_info['note'] = htmlspecialchars($file_info['note']);

		/* prev load need to calculate the height first. otherwise, the scroll will change abnormally */
		if ($file_info['type'] == 'image') {
			$file_path_fluent = sprintf("%s/%s", TOPCFG_PHOTOS_DIR_FLUENT, $file_info['path']);
			list($file_info['fluent_w'], $file_info['fluent_h']) = getimagesize($file_path_fluent);
		}else {
			$file_info['fluent_w'] = 320;
			$file_info['fluent_h'] = 480;
		}

		return $file_info;
	}

}

