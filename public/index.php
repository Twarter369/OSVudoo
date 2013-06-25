<?php	

define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname(dirname(__FILE__)));
//Define ethe first URL to call if just the main site is called 
if(!isset($_GET['url'])){
	$_GET['url'] = 'homes/login';
}

$url = $_GET['url'];

require_once (ROOT . DS . 'library' . DS . 'bootstrap.php');
