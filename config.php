<?php
defined('_CORE') or die('Access Restricted.');

define('PS',PATH_SEPARATOR);
define('PATH_LIBS','libs/');
define('PATH_CORE',PATH_LIBS.'php/');
define('PATH_TMPL',PATH_LIBS.'tmpl/');

set_include_path(get_include_path()
						. PS . PATH_CORE
						. PS . PATH_TMPL
);

$db = array(
	'con'	=>	0,
	'server' => '127.0.0.1',
	'name'	=>	'mips_db',
	'user'	=>	'root',
	'pass'	=>	''
);
?>