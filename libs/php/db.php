<?php

function db_err(){
	$err_no = mysql_errno();
	$err_msg = mysql_error();
	doDie("DB Error #$err_no: $err_msg");
}

function db_connect(){
	global $db;
	if($db['con']) return false;
	
	$db['con'] = @mysql_connect($db['server'],$db['user'],$db['pass']) or db_err();
	
	db_query("
			SET character_set_results = 'utf8',
				character_set_client = 'utf8',
				character_set_connection = 'utf8',
				character_set_database = 'utf8',
				character_set_server = 'utf8'
			");

	@mysql_select_db($db['name'],$db['con']) or db_err();
}

function db_query($q){
	global $db;
	$res = @mysql_query($q,$db['con']) or db_err();
	return $res;
}

function db_fetch_array($query){
	if(!@mysql_num_rows($query)) return false;
	$rows = array();
	while($arr = mysql_fetch_array($query,MYSQL_ASSOC)){
		$rows[]=$arr;
	}
	return $rows;
}

function db_get_rows($table_name,$where=1){
	$query = db_query("SELECT * FROM $table_name WHERE $where");	
	return db_fetch_array($query);
}

function db_close(){
	global $db;
	if($db['con']) @mysql_close($db['con']) or db_err();
}