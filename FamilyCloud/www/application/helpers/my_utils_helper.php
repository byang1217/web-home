<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

function my_path_to_dir($path)
{
	$path = preg_replace('/[\\\\\\/]$/', '', $path);
	return preg_replace('/[\\\\\\/][^\\\\\\/]+$/', '', $path);
}

function my_path_to_base($path)
{
	$path = preg_replace('/[\\\\\\/]$/', '', $path);
	return preg_replace('/^.+[\\\\\\/]/', '', $path);
}

function getpost_var($name) {
	$var = false;
	if (isset($_POST[$name]))
		$var = $_POST[$name];
	if (isset($_GET[$name]))
		$var = $_GET[$name];
	return $var;
}

function array_to_xml($array, $xml = false){
    if($xml === false){
        $xml = new SimpleXMLElement('<root/>');
    }
    foreach($array as $key => $value){
        if(is_array($value)){
            array2xml($value, $xml->addChild($key));
        }else{
            $xml->addChild($key, $value);
        }
    }
    return $xml->asXML();
}

