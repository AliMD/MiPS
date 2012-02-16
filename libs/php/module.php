	<?php
defined('_CORE') or die('Access Restricted.');

require_once('db.php');

function initialize(){
	db_connect();
}

class validate {
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

function get_date(){
	return date('Y-m-d H:i:s', time());;
}

function escape_arr($arr){
	foreach($arr as $key=>$val){
		$arr[$key] = mysql_real_escape_string($val);
	}
	return $arr;
}

function check_in_table($table,$param,$var){	// returns 1 if $param with $var value exists.
	$check = db_query("SELECT id FROM $table WHERE $param='$var'");
	return @mysql_num_rows($check)!=0;
}

function reg_user($arr){
	$arr['password']=md5($arr['password']);
	db_query("INSERT INTO mips_users VALUES(NULL,'$arr[device_id]','$arr[fname]','$arr[lname]','$arr[username]','$arr[password]','$arr[email]','$arr[tel]',NULL)");
}

function update_user($arr){
	$arr['password']=md5($arr['password']);
	db_query("UPDATE mips_users SET name='$arr[name]',nickname='$arr[nickname]',password='$arr[password]',email='$arr[email]',cellphone='$arr[cellphone]' WHERE id='$arr[user_id]' AND name='guest'");
}

function reg_guest($arr){
	db_query("INSERT INTO mips_users(device_id,username) VALUES('$arr[device_id]','guest')");
}

function reg_device($arr){
	db_query("INSERT INTO mips_devices VALUES(NULL,'$arr[user_id]','$arr[device_id]','$arr[device_name]','$arr[platform_name]','$arr[platform_version]','$arr[screen_width]','$arr[screen_height]','$arr[avail_width]','$arr[avail_height]','$arr[color_depth]','$arr[user_agent]','$arr[language]',NULL)");
}

/*
function device_update_needed($input_arr,$avail_arr){
	$ret=0;
	foreach($avail_arr[0] as $key=>$val){	// $avail_arr[0] = array('id'=>'','user_id'=>'', ... , 'reg_time'=>'') [db_get_rows() single-indexed output array]
		if($key=='id' || $key=='reg_time') continue;	// maybe for user_id or device_id (?)
		if($val != $input_arr[$key]) $ret=1;			 			
	}
	return $ret;	
}

function update_device($arr){
	db_query("UPDATE devices SET device_id='$arr[device_id]',device_name='$arr[device_name]',platform_name='$arr[platform_name]',platform_version='$arr[platform_version]',screen_width='$arr[screen_width]',screen_height='$arr[screen_height]',avail_width='$arr[avail_width]',avail_height='$arr[avail_height]',color_depth='$arr[color_depth]',user_agent='$arr[user_agent]',language='$arr[language]' WHERE user_id='$arr[user_id]'");
}
*/

function get_update_arr($input_arr,$avail_arr){
	$res=array();
	foreach($avail_arr[0] as $key=>$val){	// $avail_arr[0] = array('id'=>'','user_id'=>'', ... , 'reg_time'=>'') [db_get_rows() single-indexed output array]
		if($key=='id' || $key=='reg_time') continue;	// maybe for user_id or device_id (?)
		if($val != $input_arr[$key]) $res[$key]=$input_arr[$key];			 			
	}
	return (count($res)>0)?$res:false;	
}

function update_device($arr,$whr){
	$text='';
	foreach($arr as $key=>$val){
		if($text!='') $text.=',';
		$text.="$key='$val'";
	}
	db_query("UPDATE mips_devices SET $text WHERE $whr");
}

function get_client_ip(){
	return isset($_SERVER["HTTP_X_FORWARDED_FOR"])?$_SERVER["HTTP_X_FORWARDED_FOR"]:$_SERVER['REMOTE_ADDR'];
}

function reg_stats($arr){
	db_query("INSERT INTO mips_analytics VALUES(NULL,'$arr[user_id]','$arr[device_id]','$arr[app_id]','$arr[client_ip]','$arr[meta_name]','$arr[meta_content]',NULL)");
}

function finalize(){
	db_close();
}
?>