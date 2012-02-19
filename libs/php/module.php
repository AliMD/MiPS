<?php
defined('_CORE') or die('Access Restricted.');

require_once('db.php');

function initialize(){
	db_connect();
	session_start();
}

class validate {	//TODO: complete and test!
	public function name($str){
		return preg_replace("/[\\\[\]<>~`\!\@\#\$%\^&\*()_=\+\|}{\"\'\:;\/\?\.0-9,-]*/",'',trim($str));
	}
	public function password($str){	// ???
		return $str;
	}
	public function tel($str){
		return preg_replace("/[\\\[\]~`!\@\#\$%\^&\*_=\|}{\"\'\:;\/\?\.\\,a-wyz ]*/",'',trim($str));
	}
	public function test($str){
		return $str;
	}
}

function get_inputs($method,$arr){
	return escape_arr(filter_input_array($method,$arr));	
}

function escape_arr($arr){
	foreach($arr as $key=>$val){
		$arr[$key] = mysql_real_escape_string($val);
	}
	return $arr;
}

function get_field($field,$arr=NULL){
	if(is_array($field) && $arr==NULL){	// calling get_field() without assigning $field parameter, will return 'ID'-index of input array
		$arr = $field;
		$field = 'id';
	}
	foreach($arr as $val){
		$data ="$val[$field]";	// $data = value of the last index
	}							// $arr[0]['id'] for single-indexed array
	return $data;
}

function get_app_id($track_code){
	global $db;
	return get_field(db_get_rows($db['prefix'].'applications',"track_code='$track_code'"));	// default field: ID
}

function get_device_id($arr){
	global $db;
	if(check_in_table($db['prefix'].'devices',"uuid='$arr[uuid]'")){
		update_device($arr);
	}else{
		reg_guest($arr['uuid']);
		$arr['user_id'] = get_field(db_get_rows($db['prefix'].'users',"name REGEXP '$arr[uuid]'"));	// default field: ID
		reg_device($arr);
	}
	return get_field(db_get_rows($db['prefix'].'devices',"uuid='$arr[uuid]'"));	// return device ID [id column in PREFIX.devices table]
}

function get_user_id($device_id){
	return get_field('user_id',db_get_rows($db['prefix'].'devices',"id='$device_id'"));
}

function get_date(){
	return date('Y-m-d H:i:s', time());;
}

function check_in_table($table,$whr){	// returns 1 if $param with $var value exists.
	$check = db_query("SELECT id FROM $table WHERE $whr");
	return @mysql_num_rows($check)!=0;
}

function reg_guest($uuid){
	global $db;
	$date=get_date();
	db_query("INSERT INTO $db[prefix]users(name,reg_date) VALUES('guest_$uuid','$date')");
}

function update_user($arr){
	global $db;
	//$arr['user_id']=get_field(db_get_rows($db['prefix'].'users',"name REGEXP '$arr[uuid]'"));
	$arr['password']=md5($arr['password']);
	
	$waived_arr = array('id','reg_date','last_update');
	// Non-Updateable Columns in PREFIX.users table. 'last_update' column will be changed automatically by MySQL.
		
	$update_arr = get_update_arr($arr,db_get_rows($db['prefix'].'users',"id='$_SESSION[user_id]'"),$waived_arr);
	// comparing two array and returning their difference as a new array BUT WAIVE all the 'KEY's exist in '$waived_arr' as a value.
		
	if($update_arr)	update_table($db['prefix'].'users',$update_arr,"id='$_SESSION[user_id]'"); // check if $update_arr is an array, or FALSE
}

function update_device($arr){
	global $db;
	$waived_arr = array('id','user_id','uuid','reg_date','last_update');
	// Non-Updateable Columns in PREFIX.devices table. 'last_update' column will be changed automatically by MySQL.
	
	$update_arr = get_update_arr($arr,db_get_rows($db['prefix'].'devices',"uuid='$arr[uuid]'"),$waived_arr);
	// comparing two array and returning their difference as a new array BUT WAIVE all the 'KEY's exist in '$waived_arr' as a value.
	
	if($update_arr)	update_table($db['prefix'].'devices',$update_arr,"uuid='$arr[uuid]'"); // check if $update_arr is an array, or FALSE
}

function update_guest($gid,$uarr){
	$waived_arr = array('id','reg_date','last_update');
	// Non-Updateable Columns in PREFIX.users table. 'last_update' column will be changed automatically by MySQL.
	
	$update_arr = get_update_arr($uarr,db_get_rows($db['prefix'].'users',"id='$gid'"),$waived_arr);
	// comparing two array and returning their difference as a new array BUT WAIVE all the 'KEY's exist in '$waived_arr' as a value.
	
	update_table($db['prefix'].'users',$update_arr,"id='$gid'"); // check if $update_arr is an array, or FALSE
}

function reg_device($arr){
	global $db;
	$date = get_date();
	$arr = ready2insert($arr);
	db_query("INSERT INTO $db[prefix]devices($arr[keys],reg_date) VALUES($arr[vals],'$date')");
}

function get_update_arr($input_arr,$avail_arr,$waived_arr){
	$res=array();
	foreach($avail_arr[0] as $key=>$val){	// $avail_arr[0] = array('id'=>'','user_id'=>'', ... , 'reg_time'=>'') [db_get_rows() single-indexed output array]
		if(in_array($key,$waived_arr)) continue;	// waive all the 'key's exist in '$waive_arr' as a value
		if($val != $input_arr[$key]) $res[$key]=$input_arr[$key];			 			
	}
	return (count($res)>0)?$res:false;	
}

function get_client_ip(){
	return isset($_SERVER["HTTP_X_FORWARDED_FOR"])?$_SERVER["HTTP_X_FORWARDED_FOR"]:$_SERVER['REMOTE_ADDR'];
}

function update_table($table,$arr,$whr){
	$text='';
	foreach($arr as $key=>$val){
		if($text!='') $text.=',';
		$text.="$key='$val'";
	}
	db_query("UPDATE $table SET $text WHERE $whr");
}

function ready2insert($arr){
	$key_str=$val_str='';
	foreach($arr as $key=>$val){
		if($key_str!=''){
			$key_str.=',';
			$val_str.=',';
		}
		$key_str.="$key";
		$val_str.="'$val'";
	}
	return array('keys'=>$key_str,'vals'=>$val_str);
}

function meta_register($arr){
	update_user($arr);	// update if needed automatically.
	check_login() or user_exists($arr) and login();	
}

function login(){
	$_SESSION['login']=1;
}

function check_login(){
	return $_SESSION['login']==1;
}

function user_exists($arr){
	global $db;
	$arr['password']=md5($arr['password']);
	return db_get_rows($db['prefix'].'users',"email='$arr[email]' AND password='$arr[password]'") or false;
}

function meta_login($arr){	
	global $db;
	$probable_guest_id = $_SESSION['user_id'];	// or get user ID from PREFIX.devices table [probable guest ID]
	
	if($user_arr = user_exists($arr)){
		$user_id = $user_arr[0]['id'];
		if($probable_guest_id != $user_id) update_guest($probable_guest_id,$user_arr);
	}
	check_login() or user_exists($arr) and login();
}

function logout(){
	unset($_SESSION['login']);
	setcookie(session_name(),'',-1);	// 1sec ago (past time to clear cookie)
	//session_destroy();				// ???
}

function meta_logout(){
	logout();
}

function insert_analytics($arr){
	global $db;
	$arr = ready2insert($arr);
	db_query("INSERT INTO $db[prefix]analytics($arr[keys]) VALUES($arr[vals])");
}

function finalize(){
	db_close();
}