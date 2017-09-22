<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class My_search extends CI_Model {
	public $data = array();

        public function __construct()
        {
                parent::__construct();
        }

	public function get_offset($params = array(), $seq_id)
	{
		$search_params = array_merge($params, array('seq_id' => $seq_id));
		$num = $search_params['num'];
		$total_count = $this->My_search->search($search_params, true);
		return $total_count;
	}

	public function search($params = array(), $get_count = false)
	{
		$search_type = isset($params['search_type']) ? $params['search_type'] : "time";

		$this->data['search_offset'] = isset($params['offset']) ? $params['offset'] : 0;
		if ($search_type == "time") {
			return $this->search_time($params, $get_count);
/*
		}elseif ($search_type = "album") {
			return $this->search_album($params, $get_count);
		}elseif ($search_type = "path") {
			return $this->search_path($params, $get_count);
		}elseif ($search_type = "delete") {
			return $this->search_delete($params, $get_count);
*/
		}elseif ($search_type = "note") {
			return $this->search_note($params, $get_count);
		}else {
			pr_err("invalid search type");
			return false;
		}
	}

	public function search_time($params = array(), $get_count = false)
	{
		$default_params = array(
				'seq_id' => "9999999999999999",
				'offset' => 0,
				'num' => LOAD_NUM_ONCE,
				'order' => 'asc', /*desc*/
				'star_only' => 'n'
			       );
		$params = array_merge($default_params, $params);
		extract($params);

		$file_list = array();

		$this->db->where('is_convert', "y");
		if ($star_only == 'y')
			$this->db->like('is_star', "y");

		if ($order == 'desc')
			$this->db->where('seq_id >', $seq_id);
		else
			$this->db->where('seq_id <', $seq_id);
		$this->db->order_by('seq_id', $order);

		if ($get_count) {
			$this->db->select("count('id') as search_count");
			$query = $this->db->get('photo');
			return $query->row()->search_count;
		}

		$query = $this->db->get('photo', $num, $offset);
		foreach ($query->result() as $row) {
			array_push($file_list, $row->id);
		}
		return $file_list;
	}

	public function search_note($params = array(), $get_count = false)
	{
		$default_params = array(
				'keyw' => '',
				'offset' => 0,
				'num' => LOAD_NUM_ONCE,
				'order' => 'asc', /*desc*/
				'order_by_access' => 'n',
				'star_only' => 'n',
				'delete_only' => 'n',
				'lock_only' => 'n',
			       );
		$params = array_merge($default_params, $params);
		extract($params);

		$file_list = array();
		$keys = preg_split("/[\s,]+/", $keyw);

		$this->db->where('is_convert', "y");
		foreach ($keys as $key)
			$this->db->like('note', $key);
		if ($star_only == 'y')
			$this->db->like('is_star', "y");
		$this->db->order_by('seq_id', $order);

		if ($get_count) {
			$this->db->select("count('id') as search_count");
			$query = $this->db->get('photo');
			return $query->row()->search_count;
		}

		$query = $this->db->get('photo', $num, $offset);
		foreach ($query->result() as $row) {
			array_push($file_list, $row->id);
		}
		return $file_list;
	}
}

