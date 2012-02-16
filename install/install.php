<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>RegExp Examlpe 1</title>
<style type="text/css">
body {
	direction:rtl;
	font-family:Arial, Helvetica, sans-serif;
	font-size:18px;
	line-height:170%;
}
fieldset {
	margin:10px auto;
	width:95%;
	padding:10px;
	border-radius: 10px;
	-moz-border-radius: 10px;
	border:#999 5px double;
}
input[type="text"] {
	direction:ltr;
	height:25px;
	width:200px;
	border-radius: 5px;
	-moz-border-radius: 5px;
	border:#999 1px solid;
	font-size:18px;
	padding:3px;
	box-shadow:#999 0 0 3px;
	-moz-box-shadow:#999 0 0 3px;
}
input[type="submit"] {
	width:100px;
	border-radius: 5px;
	-moz-border-radius: 5px;
	border:#999 1px solid;
	font-size:18px;
	padding:3px;
	margin:3px;
	box-shadow:#999 0 0 3px;
	-moz-box-shadow:#999 0 0 3px;
	background-color:#AABC2C;
	color:#FFF;
	cursor: pointer;	
}
</style>
</head>

<body>
<?php 
if(isset($_POST['send'])){ 
	define('_CORE',1);
	require_once('config.php');
	require_once('view.php');
	require_once('db.php');
	
	$db['name']=$_POST['dbname'];
	db_connect();
	
	db_query("
		CREATE TABLE users(
			id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
			device_id VARCHAR(32),
			fname VARCHAR(32),
			lname VARCHAR(32),
			username VARCHAR(16),
			password VARCHAR(32),
			email VARCHAR(100),
			tel VARCHAR(20),
			reg_time TIMESTAMP NOT NULL DEFAULT NOW()
		) ENGINE = MYISAM;
		
		CREATE TABLE devices(
			id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
			user_id INT,
			device_id VARCHAR(32),
			device_name VARCHAR(22),
			platform_name VARCHAR(12),
			platform_version VARCHAR(12),
			screen_width INT,
			screen_height INT,
			avail_width INT,
			avail_height INT,
			color_depth VARCHAR(16),
			user_agent VARCHAR(100),
			language VARCHAR(6),
			reg_time TIMESTAMP NOT NULL DEFAULT NOW()
		) ENGINE = MYISAM;
		
		CREATE TABLE applications(
			id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
			track_code INT,
			app_name VARCHAR(16),
			app_version VARCHAR(16),
			authors VARCHAR(100),
			icon VARCHAR(32),
			screenshots VARCHAR(150),
			description VARCHAR(300),
			programming_lang VARCHAR(16),
			core_framework_name VARCHAR(16),
			core_framework_version VARCHAR(16),
			ui_framework_name VARCHAR(16),
			ui_framework_version VARCHAR(16),
			reg_time TIMESTAMP NOT NULL DEFAULT NOW()
		) ENGINE = MYISAM;
		
		CREATE TABLE analytics(
			id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
			user_id INT,
			device_id VARCHAR(32),
			app_id INT,
			client_ip VARCHAR(15),
			meta_name TEXT(100),
			meta_content VARCHAR(100),
			reg_time TIMESTAMP NOT NULL DEFAULT NOW()
		) ENGINE = MYISAM;
	");

	db_close();
?>
<div class="content">
<fieldset>جداول با موفقیت ایجاد شد.</fieldset>
</div>
<?php }else{ ?>
<div class="content">
<form action="" method="post">
	<fieldset>
		<legend>نام دیتابیس را وارد نمایید</legend>
		<input type="text" name="dbname" /> 
		<input type="submit" value="ارسال" name="send" />
	</fieldset>
</form>
</div>
<?php } ?>
</body>
</html>