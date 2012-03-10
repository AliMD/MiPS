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


if($act=='add_app'){
	// TODO: add app to db from a form template page .
}else if($act=='track'){
	
	/*
	 *	IMPORTANT:
	 *		we have used $_SESSION in order to prevent sending repeatedly parameters for each section.
	 *		base on plan, it can be changed!
	 */
	
	$track_arr = get_inputs(INPUT_GET,$track_validate);	// sanitize the params which have been sent by a METHOD
	// can NOT: "check inputs for err and doDie" because of being variable params in array. these params should NOT be sent all together!
	
	$app_id = isset($_SESSION['app_id'])?$_SESSION['app_id']:($_SESSION['app_id']=get_app_id($track_arr['track_code']));
	// set session once, for lateral using.
	
	$device_id = isset($_SESSION['device_id'])?$_SESSION['device_id']:($_SESSION['device_id']=get_device_id(array(
	// set session once, for lateral using.
		'uuid'				=>	$track_arr['uuid'],
		'device_name'		=>	$track_arr['device_name'],
		'platform_name'		=>	$track_arr['platform_name'],
		'platform_version'	=>	$track_arr['platform_version'],
		'screen_width'		=>	$track_arr['screen_width'],
		'screen_height'		=>	$track_arr['screen_height'],
		'avail_width'		=>	$track_arr['avail_width'],
		'avail_height'		=>	$track_arr['avail_height'],
		'color_depth'		=>	$track_arr['color_depth'],
		'user_agent'		=>	$track_arr['user_agent'],
		'language'			=>	$track_arr['language'],
	))); // insert device and user, update device if needed.
	// tanx for dont masmalizing :D, remove after read.
	
	$user_id = isset($_SESSION['user_id'])?$_SESSION['user_id']:($_SESSION['user_id']=get_user_id($device_id));
	// set session for lateral using.
	
	$client_ip = get_client_ip();
	
	$track_arr['meta_name']=='register' && meta_register(array(
		'name'			=>	$track_arr['name'],
		'nickname'		=>	$track_arr['nickname'],
		'email'			=>	$track_arr['email'],
		'password'		=>	$track_arr['password'],
		'cellphone'		=>	$track_arr['cellphone']
	));	// update guest (registered before) as a user, and login.
	//why use meta ? user_register or user_login maybe better ?!
	
	$track_arr['meta_name']=='login' && meta_login(array(
		'email'			=>	$track_arr['email'],
		'password'		=>	$track_arr['password']
	));	
	/*
	*	check email and password in users table,
	*	if user exists, remove guest from users table, update user_id in devices an analytics table, change user_id SESSION.
	*	do login anyway.
	*/
	
	if($track_arr['meta_name']=='logout')	meta_logout();	// logout user.
	
	//TODO: dont insert everything !
	insert_analytics(array(
		'user_id'		=>	$_SESSION['user_id'],
		'device_id'		=>	$_SESSION['device_id'],
		'app_id'		=>	$_SESSION['app_id'],
		'client_ip'		=>	$client_ip,
		'meta_name'		=>	$track_arr['meta_name'],
		'meta_content'	=>	$track_arr['meta_content']
	));

}

finalize();