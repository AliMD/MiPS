<?php
defined('_CORE') or die('Access Restricted.');


require_once('view.php');
require_once('module.php');

initialize();

$act = $_GET['act'] or doDie('Wrong action.');

$track_validate = array(
	'name' => array( 'filter' => FILTER_CALLBACK, 'options' => 'validate::name' ),
	'nickname' => array( 'filter' => FILTER_CALLBACK, 'options' => 'validate::password' ),
	'email' => array( 'filter' => FILTER_SANITIZE_EMAIL ),
	'password' => array( 'filter' => FILTER_SANITIZE_STRING ), //TODO: research
	'cellphone' => array( 'filter' => FILTER_CALLBACK, 'options' => 'validate::tel' ),
	'uuid' => array( 'filter' => FILTER_CALLBACK, 'options' => 'validate::test' ),
	'device_name' => array( 'filter' => FILTER_CALLBACK, 'options' => 'validate::test' ),
	'platform_name' => array( 'filter' => FILTER_CALLBACK, 'options' => 'validate::test' ),
	'platform_version' => array( 'filter' => FILTER_CALLBACK, 'options' => 'validate::test' ),
	'screen_width' => array( 'filter' => FILTER_CALLBACK, 'options' => 'validate::test' ),
	'screen_height' => array( 'filter' => FILTER_CALLBACK, 'options' => 'validate::test' ),
	'avail_width' => array( 'filter' => FILTER_CALLBACK, 'options' => 'validate::test' ),
	'avail_height' => array( 'filter' => FILTER_CALLBACK, 'options' => 'validate::test' ),
	'color_depth' => array( 'filter' => FILTER_CALLBACK, 'options' => 'validate::test' ),
	'user_agent' => array( 'filter' => FILTER_CALLBACK, 'options' => 'validate::test' ),
	'language' => array( 'filter' => FILTER_CALLBACK, 'options' => 'validate::test' ),
	'track_code' => array( 'filter' => FILTER_CALLBACK, 'options' => 'validate::test' ),
	'meta_name' => array( 'filter' => FILTER_CALLBACK, 'options' => 'validate::test' ),
	'meta_content' => array( 'filter' => FILTER_CALLBACK, 'options' => 'validate::test' ) //TODO: test!
);

if($act=='track'){
	
	$track_arr = get_inputs('INPUT_GET',$track_validate);	// sanitize the params which have been sent by a METHOD
	// can NOT: "check inputs for err and doDie" because of being variable params in array. these params should NOT be sent all together!
	
	$app_id = ( $_SESSION['app_id'] or get_app_id($track_arr['track_code']) ) or doDie('App ID not found');
	if(!isset($_SESSION['app_id'])) $_SESSION['app_id'] = $app_id;			// set session for later using.
	
	$device_id = ( $_SESSION['device_id'] or get_device_id($track_arr) ) or doDie('Device ID not found'); // insert device and user, update device if needed.
	if(!isset($_SESSION['device_id'])) $_SESSION['device_id'] = $device_id;	// set session for later using.
	
	$user_id = ( $_SESSION['user_id'] or get_user_id($device_id) ) or doDie('User ID not found');
	if(!isset($_SESSION['user_id'])) $_SESSION['user_id'] = $user_id;		// set session for later using.
	
	$client_ip = get_client_ip();
	
	if($track_arr['meta_name']=='register')	meta_register($track_arr);		// update guest (registered befor) as a user, and login.
	if($track_arr['meta_name']=='login')	meta_login($track_arr);			// check users table for update probable guest, and login.
	if($track_arr['meta_name']=='logout')	meta_logout();					// logout user.
		
	insert_analytics($db['prefix'].'analytics',array(
		'user_id'		=>	$_SESSION['user_id'],
		'device_id'		=>	$_SESSION['device_id'],
		'app_id'		=>	$_SESSION['app_id'],
		'client_ip'		=>	$client_ip,
		'meta_name'		=>	$track_arr['meta_name'],
		'meta_content'	=>	$track_arr['meta_content'],
	));
	
	/*
	TODO:
	target: add2analytic
	neeeds: user_id, app_id, device_id, client_ip
	
	check session if not:
	app_id -> sellect track_code in apps table
		if not fount then doDie
	store in app_id session
	
	session!
	device_id -> sellect uuid in device table
		f: if not found -> insert user as guest and assign user_id, register device and return id 
		t: if found -> update and fetch id and fetch user_id
	
	session!
	user_id -> is ok !
	
	client_ip -> get from $_SERVER
		check for proxy
	
	if meta_name=register -> update guest as user , login
	if meta_name=login :
		get probable guest_user_id from device_id
		get user_id from db_get_raws() where email=$arr[email] and password=$arr[password]
		guest_user_id == user_id?
		if false: update guest_user_id with user_id parameters in users table!
		login() anyway
		
		//check if guest -> update usertable for change this quest's to real user (mullti device for 1 user) , login
	
	insert into analytic !:D
	
	*/

}else if($act=='add_app'){}

finalize();