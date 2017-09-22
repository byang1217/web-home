<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class My_setup extends CI_Model {
	public $data = array();

        public function __construct()
        {
                parent::__construct();
		$this->data['page_title'] = "Family Cloud";
		$this->data['server_ip'] = getHostByName(getHostName());
		$this->data['server_port'] = $_SERVER['SERVER_PORT'];
		$this->data['client_ip'] = $_SERVER['REMOTE_ADDR'];
		$this->data['auto_server_url'] = sprintf("http://%s:%s%s", getHostByName(getHostName()), $_SERVER['SERVER_PORT'], $_SERVER['SCRIPT_NAME']);
		$this->data['server_url'] = $this->data['auto_server_url'];

		$query = $this->db->get_where('setup');
		foreach ($query->result() as $item) {
			if ($item->config == 'server_url')
				$this->data['server_url'] = $item->value;
			if ($item->config == 'page_title')
				$this->data['page_title'] = $item->value;
		}
        }

	public function update_setup()
	{
		if (!isset($_POST['page_title']) || mb_strlen($_POST['page_title'], 'UTF8') < 2) {
                        $_SESSION['pending_alert_msg'] = 'title至少2个字';
			return false;
                }
		if (!isset($_POST['server_url'])) {
                        $_SESSION['pending_alert_msg'] = 'server url 错误';
			return false;
                }

		$this->db->delete('setup', array('config' => 'server_url'));
		$this->db->insert('setup', array('config' => 'server_url', 'value' => $_POST['server_url']));
		$this->db->delete('setup', array('config' => 'page_title'));
		$this->db->insert('setup', array('config' => 'page_title', 'value' => $_POST['page_title']));
	}
}
