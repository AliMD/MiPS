<?php
defined('_CORE') or die('Access Restricted.');

require_once('view.php');
require_once('module.php');

initialize();

$act = $_GET['act'] or doDie('Wrong action.');

$track_validate = array(
	'name'				=> array( 'filter' => FILTER_CALLBACK, 'options' => 'validate::name' ),
	'nickname'			=> array( 'filter' => FILTER_CALLBACK, 'options' => 'validate::name' ),
	'email'				=> array( 'filter' => FILTER_SANITIZE_EMAIL ),
	'password'			=> array( 'filter' => FILTER_SANITIZE_STRING ), //TODO: research
	'cellphone'			=> array( 'filter' => FILTER_CALLBACK, 'options' => 'validate::tel' ),
	'uuid'				=> array( 'filter' => FILTER_CALLBACK, 'options' => 'validate::test' ),
	'device_name'		=> array( 'filter' => FILTER_CALLBACK, 'options' => 'validate::test' ),
	'platform_name'		=> array( 'filter' => FILTER_CALLBACK, 'options' => 'validate::test' ),
	'platform_version'	=> array( 'filter' => FILTER_CALLBACK, 'options' => 'validate::test' ),
	'screen_width'		=> array( 'filter' => FILTER_CALLBACK, 'options' => 'validate::test' ),
	'screen_height'		=> array( 'filter' => FILTER_CALLBACK, 'options' => 'validate::test' ),
	'avail_width'		=> array( 'filter' => FILTER_CALLBACK, 'options' => 'validate::test' ),
	'avail_height'		=> array( 'filter' => FILTER_CALLBACK, 'options' => 'validate::test' ),
	'color_depth'		=> array( 'filter' => FILTER_CALLBACK, 'options' => 'validate::test' ),
	'user_agent'		=> array( 'filter' => FILTER_CALLBACK, 'options' => 'validate::test' ),
	'language'			=> array( 'filter' => FILTER_CALLBACK, 'options' => 'validate::test' ),
	'track_code'		=> array( 'filter' => FILTER_CALLBACK, 'options' => 'validate::test' ),
	'skip_analytic'		=> array( 'filter' => FILTER_CALLBACK, 'options' => 'validate::Boolean' ),
	'meta_name'			=> array( 'filter' => FILTER_CALLBACK, 'options' => 'validate::test' ),
	'meta_content'		=> array( 'filter' => FILTER_CALLBACK, 'options' => 'validate::test' ) //TODO: test!
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
	// can NOT: "check inputs for err and doDie" because of being variable params in array. these params may NOT be sent all together!
	
	$app_id = isset($_SESSION['app_id']) and $_SESSION['app_id'] or ($_SESSION['app_id']=get_app_id($track_arr['track_code']));
	// set session once, for lateral using.
	
	$device_id = isset($_SESSION['device_id']) and $_SESSION['device_id'] or ($_SESSION['device_id']=get_device_id(array(
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
	)));
	// insert device and user, update device if needed.
	
	$user_id = isset($_SESSION['user_id']) and $_SESSION['user_id'] or ($_SESSION['user_id']=get_user_id($device_id));
	// set session for lateral using.
	
	$client_ip = isset($_SESSION['client_ip']) and $_SESSION['client_ip'] or ($_SESSION['client_ip']=get_client_ip());
	// set session for lateral using.
	
	$track_arr['meta_name']=='register' and meta_register(array(
		'name'			=>	$track_arr['name'],
		'nickname'		=>	$track_arr['nickname'],
		'email'			=>	$track_arr['email'],
		'password'		=>	$track_arr['password'],
		'cellphone'		=>	$track_arr['cellphone']
	));
	// update guest (registered before) as a user, and login.
		
		/*
		 *	Ali.MD: why use meta ? user_register or user_login maybe better ?!
		 *	#em.Ql:	What are you talking about? need to charge ??? :D
		 *		 	seriously: in order to remember that these functions are called by meta_name param.
		 */
	
	$track_arr['meta_name']=='login' and meta_login(array(
		'email'			=>	$track_arr['email'],
		'password'		=>	$track_arr['password']
	));	
	
		/*
		*	check email and password in users table,
		*	if user exists, remove guest from users table, update user_id in devices an analytics table, change user_id SESSION.
		*	do login anyway.
		*/
	
	$track_arr['meta_name']=='logout' and meta_logout();
	// logout user.
	
	$track_arr['meta_name']=='comment' and meta_comment($track_arr['meta_content']);
	// insert comment in db ( meta_content = reply_id=12|title=this is a title|comment=blah blah|tags=1,3 )
	
	$track_arr['meta_name']=='comment_rate' and meta_comment_rate($track_arr['meta_content']);
	// update comment table, add rate +1 or -1 to comment ( meta_content = id=123|rate=-1 )
	// check voters to block duplicate ratings.
	
	// client can insert_analytics log by skip_analytic=1 (for example for login and logout or comment)
	$track_arr['skip_analytic'] or insert_analytics(array(
		'user_id'		=>	$_SESSION['user_id'],
		'device_id'		=>	$_SESSION['device_id'],
		'app_id'		=>	$_SESSION['app_id'],
		'client_ip'		=>	$_SESSION['client_ip'],
		'meta_name'		=>	$track_arr['meta_name'],
		'meta_content'	=>	$track_arr['meta_content']
	));
	
	//echo json data to app

}else if($act=='read_db'){ 
	
	$read_db_validate = array(
		'from'		=> array( 'filter' => FILTER_CALLBACK, 'options' => 'validate::test' ),
		'alt_where'	=> array( 'filter' => FILTER_CALLBACK, 'options' => 'validate::test' ), // && with sql where.
		'skip'		=> array( 'filter' => FILTER_CALLBACK, 'options' => 'validate::test' ),
		'limit'		=> array( 'filter' => FILTER_CALLBACK, 'options' => 'validate::test' )
	);
	
	$read_db_arr = get_inputs(INPUT_GET,$read_db_validate);
	
	if($read_db_arr['from']=='comment'){
		
	}
	// echo json comment list to app
	
}

finalize();