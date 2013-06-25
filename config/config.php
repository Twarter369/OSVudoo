<?php

/** Configuration Variables **/
session_start();

//Someuseful Sys vars
define ('DEVELOPMENT_ENVIRONMENT',true);
define('REQUIRE_LOGIN',true);
define('ADMIN_ROLL_ID',1);//

//DB connection stuff
define('DB_NAME', '#####');
define('DB_USER', '#####');
define('DB_PASSWORD', '####');
define('DB_HOST', '####');

//Pagination
define('PAGINATE_LIMIT',10);

define('SITE_ROOT','http://localhost/');
define('LOGGED_IN_ROOT',SITE_ROOT.'projects/viewall');
define('NOT_LOGGED_IN_ROOT',SITE_ROOT.'homes/login');
define('IMAGE_ROOT',SITE_ROOT.'public/img');
define('CALENDAR_ROOT',SITE_ROOT.'vendor/calendarview-1.2/');

define('DEFAULT_STYLE_TAG',"<link href='/public/css/style.css' rel='stylesheet' type='text/css' />");