<?php
defined('_CORE') or die('Access Restricted.');

require_once('view.php');
require_once('module.php');

initialize();

$act = $_GET['act'] or doDie('Wrong action.');

$track_validate = array(
	'name' => array( 'filter' => FILTER_CALLBACK, 'options' => 'validate::name' ),
	'nickname' => array( 'filter' => FILTER_CALLBACK, 'options' => 'validate::name' ),
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
	
	/*
	 *	IMPORTANT:
	 *		we have used $_SESSION in order to prevent sending repeatedly parameters for each section.
	 *		base on plan, it can be changed!
	 */
	
	$track_arr = get_inputs(INPUT_GET,$track_validate);	// sanitize the params which have been sent by a METHOD
	// can NOT: "check inputs for err and doDie" because of being variable params in array. these params should NOT be sent all together!
	
	if(!isset($_SESSION['track_code']))	$_SESSION['track_code']=$track_arr['track_code'];	// set session once, for later using.
	$app_id = get_app_id($_SESSION['track_code']) or doDie('App ID not found');
	if(!isset($_SESSION['app_id']))		$_SESSION['app_id'] = $app_id;						// set session once, for later using.	
	
	$device_id = get_device_id(array(
		'uuid'				=>	isset($_SESSION['uuid'])?				$_SESSION['uuid']:($_SESSION['uuid']=$track_arr['uuid']),
		'device_name'		=>	isset($_SESSION['device_name'])?		$_SESSION['device_name']:($_SESSION['device_name']=$track_arr['device_name']),
		'platform_name'		=>	isset($_SESSION['platform_name'])?		$_SESSION['platform_name']:($_SESSION['platform_name']=$track_arr['platform_name']),
		'platform_version'	=>	isset($_SESSION['platform_version'])?	$_SESSION['platform_version']:($_SESSION['platform_version']=$track_arr['platform_version']),
		'screen_width'		=>	isset($_SESSION['screen_width'])?		$_SESSION['screen_width']:($_SESSION['screen_width']=$track_arr['screen_width']),
		'screen_height'		=>	isset($_SESSION['screen_height'])?		$_SESSION['screen_height']:($_SESSION['screen_height']=$track_arr['screen_height']),
		'avail_width'		=>	isset($_SESSION['avail_width'])?		$_SESSION['avail_width']:($_SESSION['avail_width']=$track_arr['avail_width']),
		'avail_height'		=>	isset($_SESSION['avail_height'])?		$_SESSION['avail_height']:($_SESSION['avail_height']=$track_arr['avail_height']),
		'color_depth'		=>	isset($_SESSION['color_depth'])?		$_SESSION['color_depth']:($_SESSION['color_depth']=$track_arr['color_depth']),
		'user_agent'		=>	isset($_SESSION['user_agent'])?			$_SESSION['user_agent']:($_SESSION['user_agent']=$track_arr['user_agent']),
		'language'			=>	isset($_SESSION['language'])?			$_SESSION['language']:($_SESSION['language']=$track_arr['language']),
	)) or doDie('Device ID not found'); // insert device and user, update device if needed.
	if(!isset($_SESSION['device_id']))	$_SESSION['device_id'] = $device_id;	// set session for later using.
	
	$user_id = get_user_id($device_id) or doDie('User ID not found');
	if(!isset($_SESSION['user_id']))	$_SESSION['user_id'] = $user_id;		// set session for later using.
	
	$client_ip = get_client_ip();
	
	if($track_arr['meta_name']=='register')	meta_register(array(
		'name'			=>	$track_arr['name'],
		'nickname'		=>	$track_arr['nickname'],
		'email'			=>	$track_arr['email'],
		'password'		=>	$track_arr['password'],
		'cellphone'		=>	$track_arr['cellphone']
	));	// update guest (registered before) as a user, and login.
	
	if($track_arr['meta_name']=='login')	meta_login(array(
		'email'			=>	$track_arr['email'],
		'password'		=>	$track_arr['password']
	));	// check users table for update probable guest, and login.
	
	if($track_arr['meta_name']=='logout')	meta_logout();	// logout user.
		
	insert_analytics(array(
		'user_id'		=>	$_SESSION['user_id'],
		'device_id'		=>	$_SESSION['device_id'],
		'app_id'		=>	$_SESSION['app_id'],
		'client_ip'		=>	$client_ip,
		'meta_name'		=>	$track_arr['meta_name'],
		'meta_content'	=>	$track_arr['meta_content']
	));

}else if($act=='add_app'){
	// TODO: ...	
}

finalize();