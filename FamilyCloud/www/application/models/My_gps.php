<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class My_gps extends CI_Model {
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

