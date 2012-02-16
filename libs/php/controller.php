<?php
defined('_CORE') or die('Access Restricted.');


require_once('view.php');
require_once('module.php');

initialize();

$act = $_GET['act'] or doDie('Wrong action.');

if($act=='track'){
	
	$track_validate = array(
		'name' => array( 'filter' => FILTER_CALLBACK, 'options' => 'validate::name' ),
		'nickname' => array( 'filter' => FILTER_CALLBACK, 'options' => 'validate::password' ),
		'email' => array( 'filter' => FILTER_SANITIZE_EMAIL ),
		'cellphone' => array( 'filter' => FILTER_CALLBACK, 'options' => 'validate::tel' ),
		//'user_id' => array( 'filter' => FILTER_CALLBACK, 'options' => 'validate::test' ),	// user_id=0 by default bayad tavasote device send shavad!
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
		'meta_content' => array( 'filter' => FILTER_CALLBACK, 'options' => 'validate::test' ),
	);
	
	$track_arr = escape_arr(filter_input_array(INPUT_GET,$track_validate));
	
	if(	strlen($track_arr['uuid'])		>0	&&
		strlen($track_arr['name'])		>0	&&
		strlen($track_arr['password'])	>0	&&
		strlen($track_arr['email'])		>0	&&
		strlen($track_arr['tel'])		>0		// Is this Required?
	){
		if(	check_in_table('mips_users','email',$track_arr['email']) ){ 
			send_msg('ERR: Duplicate User!');	// send error msg (for developing)
		}else{
			$track_arr['user_id'] = get_field(db_get_rows('mips_devices',"uuid='$track_arr[uuid]'"),'user_id'); 
			// get user unique id from mips_devices table
			if(check_in_table('mips_users','id',$track_arr['user_id'])){
				update_user($track_arr);
			}else{
				reg_user($track_arr);				// insert in database	
				$track_arr['user_id'] = get_id(db_get_rows('mips_users',"username='$track_arr[username]'"));
				send_msg($track_arr['user_id']);	// send user_id to device [if reg_user() occurs]
			}
		}
		
	}else if( 
		strlen($track_arr['device_id'])			>0	&&
		strlen($track_arr['device_name'])		>0	&&
		strlen($track_arr['platform_name'])		>0	&&
		strlen($track_arr['platform_version'])	>0	&&
		strlen($track_arr['screen_width'])		>0	&&
		strlen($track_arr['screen_height'])		>0	&&
		strlen($track_arr['avail_width'])		>0	&&
		strlen($track_arr['avail_height'])		>0	&&
		strlen($track_arr['color_depth'])		>0	&&
		strlen($track_arr['user_agent'])		>0	&&
		strlen($track_arr['language'])			>0
	){
		if(check_in_table('users','id',$track_arr['user_id'])){							// if exists in database
			if(check_in_table('devices','user_id',$track_arr['user_id'])){
				// comparing two array and returning their difference as a new array
				$device_update_arr = get_update_arr($track_arr,db_get_rows('devices',"user_id='$track_arr[user_id]'"));
				if($device_update_arr){	
					update_device($device_update_arr,"user_id='$track_arr[user_id]'");	// Under Special Circumstances
				}
			}else{
				reg_device($track_arr);
			}
		}else{
			if(!check_in_table('users','device_id',$track_arr['device_id'])){
				reg_guest($track_arr);
				$track_arr['user_id'] = get_id(db_get_rows('users',"device_id='$track_arr[device_id]'"));
				reg_device($track_arr);
				send_msg($track_arr['user_id']);					// send user_id to device [if reg_guest() occurs]
			}
		}
	}
	if(	strlen($track_arr['user_id'])		>0	&&		// Ensure user_id has been sent by device
		$track_arr['user_id']				>0 	&&		// Ensure user_id has a true value
		strlen($track_arr['device_id'])		>0	&&
		strlen($track_arr['track_code'])	>0	&&		// THESE parameters MUST BE SENT "in EACH request", by device
		strlen($track_arr['meta_name'])		>0	&&
		strlen($track_arr['meta_content'])	>0			// $track_arr['meta_content'] is NOT necessary
	){
		$track_arr['client_ip']	= get_client_ip();
		$track_arr['app_id']	= get_id(db_get_rows('applications',"track_code='$track_arr[track_code]'"));
		reg_stats($track_arr);
	}

}else if($act=='add_app'){
	
}

finalize();
?>