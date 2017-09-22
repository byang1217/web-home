<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Pages extends CI_Controller {
	public $data = array();

	public function index()
	{
		$this->data['form_params'] = array();

//		sleep(2);
		pr_debug("dump POST content: ".print_r($_POST, true));

		$save_url = false;;
		pr_debug(print_r($_SERVER, true));
		pr_debug("url: ".$_SERVER["QUERY_STRING"]);
		$action = getpost_var('action');
		if (!$action)
			$action = 'default';
		pr_debug("action: ".$action);

		if ($action == 'otp')
			return $this->otp_handle();

		if ($action == 'token')
			return $this->token_handle();

               if (TOPCFG_RELEASE_VERSION) {
			$this->data['debug_mode'] = false;
                       error_reporting(0);
               }else {
			$this->data['debug_mode'] = true;
			if ($action == "token"
				|| $action == "otp"
				|| $action == "ajax") {
				error_reporting(0);
			}else {
			       error_reporting(E_ALL);
//			       $this->output->enable_profiler(TRUE);
			}

			if ($action == "debug_db_reset") {
				$this->db->query("TRUNCATE TABLE `albums`");
				$this->db->query("TRUNCATE TABLE `photo`");
				$this->db->query("TRUNCATE TABLE `photo_and_album`");
				return;
			}

               }

		session_start();
		if ($action == 'auth_self') {
			$this->My_user->auth_self();
		}
		$this->My_user->login_auth();

		if (TOPCFG_RELEASE_VERSION) {
			if ($this->My_user->is_first_setup() && $action != 'update_setup' && $action != 'new_user') {
				$action = "setup";
				pr_info("first setup");
			}
		}

		/* list non-admin privilege */
/*
		switch ($action) {
			case "default":
			case "logout":
				break;
			default:
				if (!$this->My_user->has_admin_access() && !$this->My_user->is_first_setup()) {
					pr_err("no privilege, jump to login");
					$_SESSION['pending_alert_msg'] = "您没有权限这么做";
					$this->load_view('login');
					goto error_exit;
				}
				break;
		}
*/

		/* list non-normal privilege */
		switch ($action) {
			case "logout":
				break;
			default:
				if (!$this->My_user->has_login() && !$this->My_user->is_first_setup()) {
					$_SESSION['pending_alert_msg'] = "您没有权限这么做";
					$this->load_view('login');
					goto error_exit;
				}
				break;
		}

		pr_debug('handle action: '.$action);
		switch ($action) {
			case "ajax":
				$sub_action = getpost_var('sub_action');
				pr_debug("sub_action: ".$sub_action);
				if ($sub_action == "ajax_load_prev" || $sub_action == "ajax_load_next") {
					$this->ajax_load_more($sub_action);
				}elseif ($sub_action == "toggle") {
					$ajax_ret = array();
					$file_id = getpost_var('file_id');
					$which = getpost_var('which');
					$this->My_file->toggle($file_id, $which);
					$file_info = $this->My_file->file_id_to_info($file_id);
//					pr_debug("dump file_info:".print_r($file_info, true));
					$img_which = sprintf("img_%s", $which);
					if (isset($file_info[$img_which])) {
						$ajax_ret['img'] = $file_info[$img_which];
						echo json_encode($ajax_ret);
					}
				}elseif ($sub_action == "note_update") {
					$ajax_ret = array();
					$file_id = getpost_var('file_id');
					$note = getpost_var('note');
					$this->My_file->update($file_id, 'note', $note);
					$file_info = $this->My_file->file_id_to_info($file_id);
					//pr_debug("dump file_info:".print_r($file_info, true));
					if (isset($file_info['note'])) {
						$ajax_ret['note'] = nl2br($file_info['note']);
						echo json_encode($ajax_ret);
					}
				}elseif ($sub_action == "select_post") {
					$ajax_ret = array();
					$file_id_list = getpost_var('file_id_list');
					$ajax_ret['ret'] = getpost_var('command').print_r($file_id_list, true)."done";
					echo json_encode($ajax_ret);
				}
				break;

			case "default":
				$save_url = true;
				$file_list = $this->get_list_by_time(LOAD_NUM_ONCE_SMALL);
				$this->show_list($file_list);
				//$this->show_list($file_list, "big");
				break;

			case "show_list":
				$save_url = true;
				$form_params = getpost_var('form_params');
				if ($form_params) {
					extract($form_params);
				}else {
					pr_err("invalid form params");
					return;
				}
				$sub_action = getpost_var('sub_action');
				if ($sub_action == "big")
					$num = LOAD_NUM_ONCE;
				elseif ($sub_action == "big")
					$num = LOAD_NUM_ONCE_SMALL;
				else
					$num = LOAD_NUM_ONCE_SMALL;
				$this->form_params_add('num', $num);
				$file_id = getpost_var('file_id_list');
				$file_info = $this->My_file->file_id_to_info($file_id);
				if (!isset($file_info['seq_id']))
					return;
				$file_seq_id = $file_info['seq_id'];
				$offset = $this->My_search->get_offset($form_params, $file_seq_id);
				$this->data['load_more_next_offset'] = $this->data['load_more_prev_offset'] = $offset;
				$search_params = array_merge($form_params, array('offset' => $offset));
				$file_list = $this->My_search->search($search_params);
				pr_debug(sprintf("file list: %s\n", print_r($file_list, true)));
				$this->show_list($file_list, $sub_action);
				break;

			case "search":
				$save_url = true;
				$keyw = getpost_var('keyw');
				$star_only = getpost_var('is_star') ? 'y' : 'n';
				$delete_only = getpost_var('is_delete') ? 'y' : 'n';
				$lock_only = getpost_var('is_lock') ? 'y' : 'n';
				$order_by_access = getpost_var('is_order_by_access') ? 'y' : 'n';
				if ($delete_only == 'y')
					$order_by_access = 'y';

				$num = LOAD_NUM_ONCE_SMALL;
				$offset = 0;
				$this->data['load_more_next_offset'] = $this->data['load_more_prev_offset'] = $offset;

				$file_list = array();
				$form_params = array('search_type' => 'note', 'order' => 'desc', 'num' => $num,
							'keyw' => $keyw, 'order_by_access' => $order_by_access,
							'star_only' => $star_only, 'delete_only' => $delete_only, 'lock_only' => $lock_only
							);
				$this->form_params_merge($form_params);

				$search_params = array_merge($form_params, array('offset' => $offset));
				$file_list = $this->My_search->search($search_params);

				pr_debug(sprintf("file list: %s\n", print_r($file_list, true)));
				$this->show_list($file_list, "list");
				break;

			case "setup":
				$save_url = true;
				$this->load_view('setup');
				break;
			case "update_setup":
				$this->My_setup->update_setup();
				$this->My_utils->jump_prev_page();
				break;
			case "new_user":
				$this->My_user->new_user();
				$this->My_utils->jump_prev_page();
				break;


/* OLD */

			case "ip":
				echo $_SERVER["REMOTE_ADDR"];
				break;
			case "list_dir":
				if (isset($_GET['ch_dir'])) {
					$this->My_file->ch_dir($_GET['ch_dir']);
				}
				$this->list_dir(0);
				break;
			case "logout":
				$_SESSION['privilege'] = 0;
				break;
			default:
				break;
		}
		if ($save_url)
			$_SESSION['REQUEST_URI'] = $_SERVER['REQUEST_URI'];

		return;
error_exit:
		pr_err("goto error exit");
/*
		if (!$this->My_user->has_login())
			session_destroy();
*/
	}

	function load_view($v, $ret=false)
	{
		if (isset($_SESSION['pending_alert_msg'])) {
			$this->data['alert_msg'] = $_SESSION['pending_alert_msg'];
			unset($_SESSION['pending_alert_msg']);
		}

		$this->data['window_h'] = getpost_var('window_h');
		$this->data['window_w'] = getpost_var('window_w');
		$this->data['network_timeout'] = NETWORK_TIMEOUT;
		$this->data = array_merge($this->My_setup->data, $this->My_file->data, $this->My_user->data, $this->My_search->data, $this->data);
		return $this->load->view($v, $this->data, $ret);
	}

	function alert($msg)
	{
		$_SESSION['pending_alert_msg'] = $msg;
	}

	function otp_handle()
	{
		$ret = array();
		$ret["errString"] = "";
		$ret['error'] = "y";

		$sub_action = getpost_var('sub_action');
		pr_debug("sub_action: ".$sub_action);
		$otp = getpost_var('otp');
		$user_id = my_decode($otp);
		$user_info = $this->My_user->get_userinfo($user_id);
		if (!$user_info) {
			$ret["errString"] = "invalid user";
			goto error_exit;
		}
		if ($sub_action == 'bindDevice') {
			$dev_uuid = getpost_var('dev_uuid');
			$dev_name = getpost_var('dev_name');

			$query = $this->db->get_where('devices', array('uuid' => $dev_uuid));
			if ($query->num_rows() == 0) {
				$this->db->insert('devices', array(
							'uuid' => $dev_uuid,
							'name' => $dev_name,
							'user_id' => $user_id,
							));
				$query = $this->db->get_where('devices', array('uuid' => $dev_uuid));
			}else {
				$this->db->from('devices');
				$this->db->where('uuid', $dev_uuid);
				$this->db->update('devices', array(
							'name' => $dev_name,
							'user_id' => $user_id,
							));
			}
			if ($query->num_rows() == 0) {
				$ret["errString"] = "insert device error";
			}else {
				$ret = array_merge($ret, $user_info);
				$ret["dev_id"] = $query->row()->id;
				$ret["error"] = "n";
			}
		}
error_exit:
			echo json_encode($ret);
//			echo array_to_xml($user_info);
	}

	function token_handle()
	{
		$ret = array();
		$ret["errString"] = "";
		$ret['error'] = "y";
		$user_id = getpost_var("user_id");
		$clientTokenTime = getpost_var("tokenTime");
		$clientToken = getpost_var("token");

		$user_info = $this->My_user->get_userinfo($user_id);
		if (!isset($user_info['id']) || $user_info['id'] != $user_id) {
			$ret["errString"] = "user id error";
			goto out;
		}

		$dev_id = getpost_var("dev_id");
		$query = $this->db->get_where('devices', array('id' => $dev_id));
		if ($query->num_rows() == 0 || $query->row()->user_id != $user_id) {
			$ret["errString"] = "device id error";
			goto out;
		}

		$user_token = $user_info["token"];
		$localToken = md5(sprintf("%s%s", $user_token, $clientTokenTime));
//		pr_debug(sprintf("localToken:%s, clientToken:%s\n", $localToken, $clientToken));
		if ($localToken != $clientToken) {
			$ret["errString"] = "token error";
			goto out;
		}

		$sub_action = getpost_var('sub_action');
		pr_debug("subaction: ".$sub_action);

		if ($sub_action == 'query') {
			$ret['error'] = "n";
			goto out;
		}

		$serverTime = time();
		$clientTime = intval($clientTokenTime);
		if (abs($serverTime - $clientTime) > (5*60)) {
			$ret["errString"] = sprintf("tokenTime timeout, serverTime: %d, clientTime: %d, delta: %d", $serverTime, $clientTime, abs($serverTime - $clientTime));
			goto out;
		}

		if ($sub_action == 'albumsUpload') {
			$result = $this->My_upload->upload_albums($user_id, $dev_id);
			$ret = array_merge($ret, $result);
			goto out;
			
		}else if ($sub_action == 'albumsClean') {
			$localIdentifier = getpost_var('localIdentifier');
			$result = $this->My_upload->upload_albums_clean($user_id, $dev_id, $localIdentifier);
			$ret = array_merge($ret, $result);
			goto out;
		}else if ($sub_action == 'albumsTodo') {
			$max_num = getpost_var('max_num');
			$result = $this->My_upload->upload_albums_todo($user_id, $dev_id, $max_num);
			$ret = array_merge($ret, $result);
			goto out;
		}else if ($sub_action == 'uploadQuery') {
			$result = $this->My_upload->upload_query($user_id, $dev_id);
			$ret = array_merge($ret, $result);
			goto out;
		}else if ($sub_action == 'uploadChunk') {
			$result = $this->My_upload->upload_chunk($user_id, $dev_id);
			$ret = array_merge($ret, $result);
			goto out;
		}else {
			$ret["errString"] = "unknown action";
		}

out:
		pr_debug("errString: ".$ret["errString"]);
		$serverTime = time();
		$localToken = md5(sprintf("%s%d", $user_token, $serverTime));
		$ret['serverTime'] = $serverTime;
		$ret['tokenTime'] = $serverTime;
		$ret['token'] = $localToken;
		pr_debug("dump ret: ".print_r($ret, true));
		echo json_encode($ret);
	}

	function ajax_load_more($sub_action)
	{
		$ajax_ret = array();
		$form_params = getpost_var('form_params');
		pr_debug(print_r($form_params, true));
		extract($form_params);
		$offset_org = $offset = getpost_var('param');

		if (!isset($num))
			$num = LOAD_NUM_ONCE;

		pr_debug(sprintf("offset_org: %s", $offset_org)); 
		if ($offset === false || !isset($load_more_view)) { 
			pr_err("invalid offset or load view");
			return;
		}

		if ($sub_action == "ajax_load_prev") {
			if ($offset < $num) {
				$num = $offset;
				$offset = 0;
			}elseif ($offset >= $num) {
				$offset -= $num;
			}else {
				if ($offset != 0) {
					pr_err("invalid offset or num");
					return;
				}
			}
		}elseif ($sub_action == "ajax_load_next") {
			$offset += $num;
		}else {
			pr_err("invalid sub_action");
			return;
		}

		if ($offset != $offset_org) {
			$search_params = array_merge($form_params, array('offset' => $offset, 'num' => $num));

			$file_list = $this->My_search->search($search_params);
			$this->data['file_list'] = $file_list;
			$this->form_params_add('list_view_mode', $list_view_mode);
			$a_start = "start_".time();
			$a_end = "end_".time();
			$ajax_ret['a_start'] = $a_start;
			$ajax_ret['a_end'] = $a_end;
			$content = sprintf("<a name=%s></a>", $a_start);
			$content .= $this->load_view($load_more_view, true);
			$content .= sprintf("<a name=%s></a>", $a_end);

			pr_debug("ajax: dump file_list: " . print_r($file_list, true));

			if ($sub_action == "ajax_load_next") {
				$offset = $offset_org + count($file_list);
			}
		}else {
			$content = "";
			pr_debug("hit end");
		}
		$ajax_ret['offset'] = $offset;
		$ajax_ret['html'] = $content;
		pr_debug(sprintf("return offset: %d", $offset));
		echo json_encode($ajax_ret);
		return;
	}

	function form_params_add($key, $value)
	{
		if (!isset($this->data['form_params']))
			$this->data['form_params'] = array();
		$this->data['form_params'][$key] = $value;
	}

	function form_params_merge($params)
	{
		if (!isset($this->data['form_params']))
			$this->data['form_params'] = array();
		$this->data['form_params'] = array_merge($this->data['form_params'], $params);
	}

	//todo: refine it with get_offset
	function get_list_by_time($num = LOAD_NUM_ONCE, $from_seq_id = "9999999999999999")
	{
		$file_list = array();

		$total_count = $this->My_search->search(array('search_type' => 'time', 'seq_id' => $from_seq_id, 'order'=>'asc'), true);
		$offset = $total_count > $num ? $total_count - $num : 0;
		pr_debug(sprintf("total_count: %d, offset: %s", $total_count, $offset));

		$this->data['load_more_next_offset'] = $this->data['load_more_prev_offset'] = $offset;

		$form_params = array('search_type' => 'time', 'order' => 'asc', 'num' => $num);
		$this->form_params_merge($form_params);

		$search_params = array_merge($form_params, array('offset' => $offset));
		$file_list = $this->My_search->search($search_params);

		pr_debug("dump file_list: " . print_r($file_list, true));

		return $file_list;

	}

	function show_list($file_list, $list_view_mode = "thumb")
	{
		$this->form_params_add('list_view_mode', $list_view_mode );
		$this->form_params_add('load_more_view', 'file_list');
		$this->data['file_list'] = $file_list;
		$this->load_view('file_browse');
	}

/* Old */
	function list_dir($page_num = 0)
	{
		if (!$this->My_user->has_admin_access()) {
			$this->alert("您没有权限这么做");
			$this->load_view('login');
			return;
		}

		$file_list = $this->My_file->list_dir($this->myhome->cur_dir, $page_num * num_per_page, num_per_page );
		$this->show_file_list($file_list, $page_num, "list_dir", $this->My_file->cur_dir);
		$this->load_view('myhome/list_dir');
	}

	function default_page()
	{
		if (!$this->My_user->has_login()) {
			$this->load_view('login');
			return;
		}

		$this->data['is_default_page'] = true;
		$this->load_view('default_page');
	}
}

