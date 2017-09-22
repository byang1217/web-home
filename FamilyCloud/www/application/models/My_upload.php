<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class My_upload extends CI_Model {
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

	public function image_get_gps($exif_data)
	{
		if (!isset($exif_data['GPSLongitude'])
				||!isset($exif_data['GPSLatitude'])
				||!isset($exif_data['GPSLongitudeRef'])
				||!isset($exif_data['GPSLatitudeRef'])) {
			return array(0, 0);
		}

		$egeoLong = $exif_data['GPSLongitude'];
		$egeoLat = $exif_data['GPSLatitude'];
		$egeoLongR = $exif_data['GPSLongitudeRef'];
		$egeoLatR = $exif_data['GPSLatitudeRef'];

		$geoLong = $this->gpsDecimal($egeoLong[0], $egeoLong[1], $egeoLong[2], $egeoLongR);
		$geoLat = $this->gpsDecimal($egeoLat[0], $egeoLat[1], $egeoLat[2], $egeoLatR);

		return array($geoLat, $geoLong);
	}

	public function image_create_thumb($src, $new)
	{
		$thumb_size = THUMBNAIL_SIZE;

		list($src_w, $src_h) = getimagesize($src);
		pr_debug(sprintf("src_w %d, src_h %d, thumb size %d\n", $src_w, $src_h, $thumb_size));
		pr_debug("copy src to thumb\n");
		if (!copy($src, $new)) {
			pr_debug("copy error\n");
			return false;
		}
		pr_debug("resize src to thumb\n");
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

		pr_debug("crop middle\n");
		$config['maintain_ratio'] = false;
		$config['width']	= $thumb_size;
		$config['height']	= $thumb_size;
		if ($src_w >= $src_h) {
			$config['x_axis'] = floor(($thumb_size * $src_w / $src_h - $thumb_size)/2);
			$config['y_axis'] = 0;
			pr_debug("x_axis = ".$config['x_axis']);
		}else {
			$config['x_axis'] = 0;
			$config['y_axis'] = floor(($thumb_size * $src_h / $src_w - $thumb_size)/2);
			pr_debug("y_axis = ".$config['y_axis']);
		}
		$this->image_lib->clear();
		$this->image_lib->initialize($config);
		$this->image_lib->crop();

		return true;
	}

	public function image_create_fluent($src, $new)
	{
		$small_width = FLUENT_SIZE;

		list($src_w, $src_h) = getimagesize($src);
		pr_debug(sprintf("src_w %d, small_w %d\n", $src_w, $small_width));
		pr_debug("copy src to new\n");
		if (!copy($src, $new)) {
			pr_debug("copy error\n");
			return false;
		}
		if ($src_w > $small_width) {
			pr_debug("resize src to fluent\n");
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

	public function upload_convert_file($file_id)
	{
		$ret = array();
		$ret['error'] = "y";

		$query = $this->db->get_where('photo', array(
					'id' => $file_id,
					'is_upload' => "y",
					'is_convert' => "n",
					));
		if ($query->num_rows() == 0) {
			$ret['errString'] = "convert file error";
			goto error_exit;
		}
		$file_path = $query->row()->path;
		$file_name = my_path_to_base($file_path);
		$file_dir = my_path_to_dir($file_path);
		$file_type = $query->row()->type;
		$file_time = $query->row()->unix_time;
		$file_seq_id = ($file_time * 1000000) + $file_id;

		$file_path_original = sprintf("%s/%s", TOPCFG_PHOTOS_DIR_ORIGINAL, $file_path);
		$file_path_hd = sprintf("%s/%s", TOPCFG_PHOTOS_DIR_HD, $file_path);
		$file_path_fluent = sprintf("%s/%s", TOPCFG_PHOTOS_DIR_FLUENT, $file_path);
		$file_path_thumb = sprintf("%s/%s", TOPCFG_PHOTOS_DIR_THUMB, $file_path);

		@mkdir(my_path_to_dir($file_path_hd) , 0777, true);
		@mkdir(my_path_to_dir($file_path_fluent) , 0777, true);
		@mkdir(my_path_to_dir($file_path_thumb) , 0777, true);
		
		if ($file_type == "image") {
			if (!@rename($file_path_original, $file_path_hd)) {
				$ret['errString'] = "move to upload HD folder error";
				goto error_exit;
			}
			if (!$this->image_create_fluent($file_path_hd, $file_path_fluent)) {
				$ret['errString'] = "create fluent error";
				goto error_exit;
			}
			if (!$this->image_create_thumb($file_path_hd, $file_path_thumb)) {
				$ret['errString'] = "create fluent error";
				goto error_exit;
			}

			$geoLat = 0;
			$geoLong = 0;
			$exif = @exif_read_data($file_path_hd);
			if ($exif != false) {
				list($geoLat, $geoLong) = $this->image_get_gps($exif);
			}

			$this->db->from('photo');
			$this->db->where('id', $file_id);
			$this->db->update('photo',
					array(
						'seq_id' => $file_seq_id,
						'gps_geoLat' => $geoLat,
						'gps_geoLong' => $geoLong,
						'is_convert' => "y",
					     ));
		}

		$ret['error'] = "n";
error_exit:
		return $ret;
	}

	public function upload_albums_todo($user_id, $dev_id, $max_num)
	{
		$ret = array();
		$ret['error'] = "y";

		$localIdentifierArray = array();

		$this->db->where('dev_id', $dev_id);
		$this->db->where('is_upload', "n");
		$this->db->order_by('unix_time', 'asc');
		$query = $this->db->get('photo', $max_num, 0);
		
		foreach ($query->result() as $photo) {
			array_push($localIdentifierArray, $photo->localIdentifier);
		}
		$ret['localIdentifierArray'] = $localIdentifierArray;
		$ret['total_num'] = count($localIdentifierArray);
		$ret['error'] = "n";
		return $ret;
	}

	public function upload_albums_clean($user_id, $dev_id, $localIdentifier)
	{
		$ret = array();
		$ret['error'] = "y";

		// clean current transfer
		$this->db->from('devices');
		$this->db->where('id', $dev_id);
		$this->db->update('devices',
				array(
					'lastLocalIdentifier' => "",
					'lastUploadOffset' => 0,
					'lastUploadLength' => 0,
					'lastUploadTotalLength' => 0,
				     ));

		if ($localIdentifier == "all") {
			$query = $this->db->get_where('photo', array(
						'dev_id' => $dev_id,
						'is_upload' => "n",
						));
		}else {
			$query = $this->db->get_where('photo', array(
						'dev_id' => $dev_id,
						'is_upload' => "n",
						'localIdentifier' => $localIdentifier,
						));
		}
		foreach ($query->result() as $photo) {
			$this->db->delete('photo_and_album', array('photo_id' => $photo->id)); 
		}
		$this->db->delete('photo', array(
					'dev_id' => $dev_id,
					'is_upload' => "n",
					));

		$ret['error'] = "n";
		return $ret;
	}

	public function upload_albums($user_id, $dev_id)
	{
		$ret = array();
		$ret['error'] = "y";

		$albumsInfoJson = getpost_var("albumsInfoJson");
		$albumsInfoArray = json_decode($albumsInfoJson, true);
		pr_debug("albumsInfoArray: ".print_r($albumsInfoArray, true));
		foreach ($albumsInfoArray as $photo) {
			$query = $this->db->get_where('photo', array('uuid' => $photo['uuid']));
			if ($query->num_rows() == 0) {
				$this->db->insert('photo', array(
							'uuid' => $photo['uuid'],
							'localIdentifier' => $photo['localIdentifier'],
							'is_upload' => "n",
							'unix_time' => $photo['createTime'],
							'user_id' => $user_id,
							'dev_id' => $dev_id,
							));
				$query = $this->db->get_where('photo', array('uuid' => $photo['uuid']));
				if ($query->num_rows() == 0) {
					pr_err("insert photo error");
					$ret["errString"] = "insert photo error";
					return $ret;
				}
			}
			$photo_id = $query->row()->id;
			
			foreach ($photo['albums'] as $album_name) {
				$query = $this->db->get_where('albums', array('name' => $album_name));
				if ($query->num_rows() == 0) {
					$this->db->insert('albums', array(
								'name' => $album_name,
								'is_from_sync' => "y",
								));
					$query = $this->db->get_where('albums', array('name' => $album_name));
					if ($query->num_rows() == 0) {
						pr_err("insert album error");
						$ret["errString"] = "insert album error";
						return $ret;
					}
				}
				$album_id = $query->row()->id;

				$query = $this->db->get_where('photo_and_album', array(
							'photo_id' => $photo_id,
							'album_id' => $album_id,
							));
				if ($query->num_rows() == 0) {
					$this->db->insert('photo_and_album', array(
							'photo_id' => $photo_id,
							'album_id' => $album_id,
							));
					$query = $this->db->get_where('photo_and_album', array(
								'photo_id' => $photo_id,
								'album_id' => $album_id,
								));
				}
				if ($query->num_rows() == 0) {
					pr_err("insert photo_to_album error");
					$ret["errString"] = "insert photo_to_album error";
					return $ret;
				}
			}
		}
		$ret['error'] = "n";
		return $ret;
	}

	public function upload_query($user_id, $dev_id)
	{
		$ret = array();
		$ret['error'] = "y";


		$query = $this->db->get_where('devices', array('id' => $dev_id));
		if ($query->num_rows() == 0) {
			$ret["errString"] = "device id error";
			goto error_exit;
		}

		$ret['lastLocalIdentifier'] = $query->row()->lastLocalIdentifier;
		$ret['lastUploadOffset'] = $query->row()->lastUploadOffset;
		$ret['lastUploadLength'] = $query->row()->lastUploadLength;
		$ret['lastUploadTotalLength'] = $query->row()->lastUploadTotalLength;
		$ret['error'] = "n";
error_exit:
		return $ret;
	}



	public function upload_chunk($user_id, $dev_id)
	{
		$ret = array();
		//$ret['error'] = "y";
		$ret['error'] = "fatal";

		$file_type = getpost_var('file_type');
		$file_offset = getpost_var('file_offset');
		$file_length = getpost_var('file_length');
		$chunk_length = getpost_var('chunk_length');
		$localIdentifier = getpost_var('localIdentifier');
		$file_name = getpost_var('file_name');

		$query = $this->db->get_where('photo', array(
					'dev_id' => $dev_id,
					'is_upload' => "n",
					'localIdentifier' => $localIdentifier,
					));
		if ($query->num_rows() == 0) {
			$ret["errString"] = "localIdentifier error";
			goto error_exit;
		}
		$file_id = $query->row()->id;
		$file_time = intval($query->row()->unix_time);
		$file_date_YYmm = date('Y-m', $file_time);

		if (!isset($_FILES['userfile']) || !isset($_FILES['userfile']['tmp_name'])) {
			$ret['errString'] = "please set userfile";
			goto error_exit;
		}

		if (@filesize($_FILES['userfile']['tmp_name']) != $chunk_length) {
			$ret['errString'] = "offset error";
			goto error_exit;
		}

		$local_md5 = md5_file($_FILES['userfile']['tmp_name']);
		$remote_md5 = $_POST['file_md5'];

		if ($local_md5 != $remote_md5) {
			$ret['error'] = "y";
			$ret['errString'] = "md5 error";
			goto error_exit;
		}

		$file_path_original = sprintf("%s/upload/%s/%s", TOPCFG_PHOTOS_DIR_ORIGINAL, $file_date_YYmm, $file_name);
		$file_tmp = sprintf("%s/dev_%d_upload.bin", TOPCFG_PHOTOS_DIR_ORIGINAL, $dev_id);
		@mkdir(sprintf("%s/upload/%s", TOPCFG_PHOTOS_DIR_ORIGINAL, $file_date_YYmm), 0777, true);
		@mkdir(sprintf("%s", TOPCFG_PHOTOS_DIR_ORIGINAL), 0777, true);

		if ($file_offset == 0) {
			if ($chunk_length == $file_length) {
				// whole file upload
				if (!@move_uploaded_file($_FILES['userfile']['tmp_name'], $file_path_original)) {
					$ret['errString'] = "move to upload folder error";
					$ret['error'] = "stop";
					goto error_exit;
				}
			}else {
				// first chunk upload
				if (!@move_uploaded_file($_FILES['userfile']['tmp_name'], $file_tmp)) {
					$ret['errString'] = "move to upload folder error";
					$ret['error'] = "stop";
					goto error_exit;
				}
			}
		}else {
			// next chunk upload
			if (@filesize($file_tmp) != $file_offset) {
				$ret['errString'] = "offset error";
				goto error_exit;
			}
			if (@file_put_contents($file_tmp, @file_get_contents($_FILES['userfile']['tmp_name']), FILE_APPEND) != $chunk_length) {
				$ret['errString'] = "append to file error";
				$ret['error'] = "stop";
				goto error_exit;
			}
			if ($file_offset + $chunk_length > $file_length) {
				$ret['errString'] = "file size > total size";
				goto error_exit;
			}
			if ($file_offset + $chunk_length == $file_length) {
				if (!@rename($file_tmp, $file_path_original)) {
					$ret['errString'] = "move to upload original folder error";
					$ret['error'] = "stop";
					goto error_exit;
				}
			}
		}
		$ret['errString'] = "upload chunk success: ".$file_name;

		$ret['lastLocalIdentifier'] = $localIdentifier;
		$ret['lastUploadOffset'] = $file_offset;
		$ret['lastUploadLength'] = $chunk_length;
		$ret['lastUploadTotalLength'] = $file_length;

		$this->db->from('devices');
		$this->db->where('id', $dev_id);
		$this->db->update('devices',
				array(
					'lastLocalIdentifier' => $ret['lastLocalIdentifier'],
					'lastUploadOffset' => $ret['lastUploadOffset'],
					'lastUploadLength' => $ret['lastUploadLength'],
					'lastUploadTotalLength' => $ret['lastUploadTotalLength'],
				     ));

		if ($file_offset + $chunk_length == $file_length) {
			$file_path = sprintf("upload/%s/%s", $file_date_YYmm, $file_name);

			$this->db->from('photo');
			$this->db->where('id', $file_id);
			$this->db->update('photo',
					array(
						'path' => $file_path,
						'type' => $file_type,
						'is_upload' => "y",
					     ));

			$convert_result = $this->upload_convert_file($file_id);
			if ($convert_result['error'] != "n") {
				$ret = array_merge($ret, $convert_result);
				goto error_exit;
			}
			$ret['errString'] = "upload whole file success: ".$file_name;
		}

		$ret['error'] = "n";

error_exit:
		if ($ret['error'] != "n") {
			$this->db->from('devices');
			$this->db->where('id', $dev_id);
			$this->db->update('devices',
					array(
						'lastLocalIdentifier' => "",
						'lastUploadOffset' => 0,
						'lastUploadLength' => 0,
						'lastUploadTotalLength' => 0,
					     ));
		}
		return $ret;
	}
}
