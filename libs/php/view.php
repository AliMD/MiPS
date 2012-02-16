<?php
defined('_CORE') or die('Access Restricted.');

/*
function gen_msg($data,$id=NULL){	// return a div element with a variable content
	return "<div id='$id'>$data</div>";
}

function send_msg($data,$id=NULL){
	echo gen_msg($data,$id);
}

function doDie($data=NULL,$id=NULL){
	if(isset($data)){
		$id=isset($id)?$id:'err';	// default ID for error Division: #err
		send_msg($data,$id);
	}
	die();
}
*/

function send_msg($data){
	echo $data;
}

function doDie($data=NULL){
	if(isset($data)) send_msg($data);
	die();
}

function get_field($arr,$field){
	$field = $field or 'id';
	foreach($arr as $val){
		$data ="$val[$field]";	// $data = value of the last index
	}						// $arr[0]['id'] for single-indexed array
	return $data;
}
?>