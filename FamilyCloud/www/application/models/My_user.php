<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

define("USER_ADMIN", 99);
define("USER_DEF", 1);
define("USER_INVALID", 0);

class My_user extends CI_Model {
	public $data = array();
	public $is_valid = false;
	public $id = -1;
	public $name = "";
	public $full_name = "";
	public $error = "";

        public function __construct()
        {
                parent::__construct();
        }

	public function is_first_setup()
	{
		return file_exists(TOPCFG_IS_FIST_SETUP_FILE);
	}

	public function has_login()
	{
		return isset($_SESSION['user_privilege']) && $_SESSION['user_privilege'] >= USER_DEF;
	}

	public function has_admin_access()
	{
		if ($this->is_first_setup())
			return true;
		return isset($_SESSION['user_privilege']) && $_SESSION['user_privilege'] >= USER_ADMIN;
	}

	public function auth_token()
	{
	}

	public function auth_self()
	{
	}

	public function auth_other()
	{
	}

	public function login_auth()
	{
		if ($this->is_first_setup()) {
			$_SESSION['user_privilege'] = USER_ADMIN;
			$_SESSION['user_id'] = 1;
		}

		if (!isset($_SESSION['user_privilege'])) {
			$_SESSION['user_privilege'] = USER_INVALID;
			$_SESSION['user_id'] = -1;
		}

		$this->data['user_id'] = $_SESSION['user_id'];
		$this->data['user_privilege'] = $_SESSION['user_privilege'];
		$this->data['has_login'] = $this->has_login();
		$this->data['has_admin_access'] = $this->has_admin_access();
		$this->data['is_first_setup'] = $this->is_first_setup();
	}

	public function get_userinfo($user_id)
	{
		$user_info = array();
		$query = $this->db->get_where('user', array('id' => $user_id));
		if ($query->num_rows() == 1) {
			$user_info['id'] = $query->row()->id;
			$user_info['name'] = $query->row()->name;
			$user_info['token'] = $query->row()->token;
			$user_info['is_admin'] = $query->row()->is_admin;
		}
		pr_debug("userinfo: ".print_r($user_info, true));
		return $user_info;
	}

	public function gen_user_otp($user_id, $timeout)
	{
		return my_encode($user_id, $timeout);
	}

	public function user_id_from_otp($otp)
	{
		$user_id = my_decode($otp);
		if ($user_id == '')
			return false;
		else
			return $user_id;
	}

	public function all_users()
	{
		$users = array();
		$query = $this->db->get_where('user');
		return $query->result();
	}

	public function new_user()
	{
		if (!isset($_POST['user_name']) || mb_strlen($_POST['user_name'], 'UTF8') < 2) {
                        $_SESSION['pending_alert_msg'] = '用户名错误，至少2个字';
			return false;
                }
		if (isset($_POST['is_admin']))
			$privilege = USER_ADMIN;
		else
			$privilege = USER_DEF;

		$query = $this->db->get_where('user', array('name' => $_POST['user_name']));
		if ($query->num_rows() != 0) {
                        $_SESSION['pending_alert_msg'] = '用户名已经存在';
			return false;
		}
		$this->db->insert('user', array(
					'name' => $_POST['user_name'],
					'privilege' => $privilege,
					'is_admin' => $privilege >= USER_ADMIN ? "y" : "n",
					'token' => md5(sprintf("%s%d", $_POST['user_name'], floor(time()))),
					));
		$_SESSION['pending_alert_msg'] = '创建用户成功';

		return true;
	}
}
