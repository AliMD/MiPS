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
	public function Boolean($str){
		return !!$str;
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
		$device_id = update_device($arr);
	}else{
		$arr['user_id'] = reg_guest();
		$device_id = reg_device($arr);
	}

	return $device_id;	// return device ID [id column in PREFIX.devices table]
}

function reg_guest(){
	global $db;
	$date=get_date();
	db_query("INSERT INTO $db[prefix]users(name,reg_date) VALUES('guest','$date')");
	return mysql_insert_id();
}

function get_user_id($device_id){
	global $db;
	return get_field('user_id',db_get_rows($db['prefix'].'devices',"id='$device_id'"));
}

function get_date(){
	return date('Y-m-d H:i:s', time());
}

function check_in_table($table,$whr){	// returns 1 if $param with $var value exists.
	$check = db_query("SELECT id FROM $table WHERE $whr");
	return @mysql_num_rows($check)!=0;
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
	$device_arr = db_get_rows($db['prefix'].'devices',"uuid='$arr[uuid]'");
	
	$waived_arr = array('id','user_id','uuid','reg_date','last_update');
	// Non-Updateable Columns in PREFIX.devices table. 'last_update' column will be changed automatically by MySQL.
	
	$update_arr = get_update_arr($arr,$device_arr,$waived_arr);
	// comparing two array and returning their difference as a new array BUT WAIVE all the 'KEY's exist in '$waived_arr' as a value.

	if($update_arr)	update_table($db['prefix'].'devices',$update_arr,"uuid='$arr[uuid]'"); // check if $update_arr is an array, or FALSE
	
	return $device_arr[0]['id'];
}

// TODO: update db functions, get "DB Error #1064" when executing multiple queries; WHY ?????
function change_guest($gid,$uarr){
	global $db;
	db_query("DELETE FROM $db[prefix]users WHERE id='$gid';");
	db_query("UPDATE $db[prefix]devices SET user_id='$uarr[id]' WHERE user_id='$gid';");
	db_query("UPDATE $db[prefix]analytics SET user_id='$uarr[id]' WHERE user_id='$gid';");
	$_SESSION['user_id']=$uarr['id'];
	/*	
	*	remove guest from users database
	*	update user_id in devices table
	*	update user_id in analytics table
	*	update $_SESSION['user_id']
	*/
}

function reg_device($arr){
	global $db;
	$date = get_date();
	$arr = ready2insert($arr);
	db_query("INSERT INTO $db[prefix]devices($arr[keys],reg_date) VALUES($arr[vals],'$date')");
	return mysql_insert_id();
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
	return db_get_rows($db['prefix'].'users',"email='$arr[email]' AND password='$arr[password]'");
}

function meta_login($arr){	
	global $db;
	$probable_guest_id = $_SESSION['user_id'];	// or get user ID from PREFIX.devices table [probable guest ID]
	
	if($user_arr = user_exists($arr)){
		$user_id = $user_arr[0]['id'];
		($probable_guest_id != $user_id) and change_guest($probable_guest_id,$user_arr[0]);
	}
	check_login() or user_exists($arr) and login();
}

function logout(){
	unset($_SESSION['login']);
	setcookie(session_name(),'',-1);	// 1sec ago (past time to clear cookie)
//	session_destroy();
}

function meta_logout(){
	logout();
}

function str2array($str,$seperator,$inner_seperator=NULL){
		/* ex:	$str = "reply_id=12|title=in yek title ast|comment=blah blah|tags=1,3"
		 *		$seperator = "|";
		 *		$inner_seperator = "=";
		 */
	$content_arr = explode($seperator,$str);
		/*
		 *	Array (
		 *		[0] => reply_id=12
		 *		[1] => title=this is a title
		 *		[2] => comment=blah blah
		 *		[3] => tags=1,3
		 *	)
		 */
	if(isset($inner_seperator)) {
		foreach($content_arr as $val){
			$param = explode($inner_seperator,$val);	
			$arr[$param[0]] = $param[1];		
		}
		/*
		 *	Array (
		 *		[reply_id] => 12
		 *		[title] => this is a title
		 *		[comment] => blah blah
		 *		[tags] => 1,3
		 *	)
		 */
	}
	return isset($inner_seperator)?$arr:$content_arr;
}

function meta_comment($str){
	// insert comment in db ( meta_content = reply_id=12|title=this is a title|comment=blah blah|tags=1,3 )
	global $db;
	$arr = str2array($str,'|','=');
	$arr['user_id']		= $_SESSION['user_id'];
	$arr['device_id']	= $_SESSION['device_id'];
	$arr['app_id']		= $_SESSION['app_id'];
	$arr['client_ip']	= $_SESSION['client_ip'];
	
	$arr = ready2insert($arr);
		/*
		 *	Array (
		 *		[keys] => reply_id,title,comment,tags,user_id,device_id,app_id,client_ip
   		 *		[vals] => '12','this is a title','blah blah','1,3','7','12','454','185.188.56.2'
		 *	)
		 */
	db_query("INSERT INTO $db[prefix]comments($arr[keys]) VALUES($arr[vals])");
}

function meta_comment_rate($str){
	//	update comment table, increase/decrease rate by 1, ( meta_content = id=123|rate=-1 )
	global $db;
	$arr = str2array($str,'|','=');
	
	$row = db_get_rows($db['prefix'].'comments',"id='$arr[id]'");
	$voters_arr = explode(',',$row[0]['voters']);
	
	if(!in_array($_SESSION['device_id'],$voters_arr)){
	// check voters to block duplicate ratings.
	
		$arr['rate'] =	$arr['rate']>0	and 1	or
						$arr['rate']==0	and 0	or
						$arr['rate']<0	and	-1;
		// ex: if rate=-5 has been sent, set $arr['rate'] to -1
		
		$SP = strlen($row[0]['voters'])>0 and ',' or '';
		db_query("UPDATE $db[prefix]comments SET rate=rate+$arr[rate], voters=CONCAT(voters, '$SP$_SESSION[device_id]') WHERE id='$arr[id]'");
	}
}

function insert_analytics($arr){
	global $db;
	$arr = ready2insert($arr);
	db_query("INSERT INTO $db[prefix]analytics($arr[keys]) VALUES($arr[vals])");
}

function finalize(){
	db_close();
}