<?php
//print "Performing maintenance, please try back in 3 or 4 min";
//exit();
/*
*
*
*	config.php
*	Defines site settings and database information
*
*/
#ini_set('error_reporting', E_ALL & ~E_DEPRECATED & ~E_NOTICE);
define('BASE_PATH', $_SERVER['DOCUMENT_ROOT']);
define('BASE_URL', 'https://'.$_SERVER['HTTP_HOST']);
define('DOMAIN', 'client.voztechnologies.com');
define('ADMIN_DOMAIN', 'client.voztechnologies.com');
define('SITE_NAME', 'Voz Admin');
define('SITE_ID', '0');
define('INCLUDE_PATH', BASE_PATH.'includes');
	define('INCLUDES', INCLUDE_PATH);
	define('INCLUDE', INCLUDE_PATH);
define('DOWNLOADED_IMAGES_PATH', '/var/www/shared_images/html/em/cities');
define('DOWNLOADED_IMAGES_URL', 'https://www.'.DOMAIN.'/images');
define('AD_IMAGES_URL', 'https://www.'.DOMAIN.'/images/adthumbs');
define('GET_PASS','nocont');

# Admin User/Pass
$users =
	array(
		'gbrent' => array('username'=>'NONE',
					'password'=>'NONE',
					'email'=>'NONE',
					'name'=>'Brent',
					'type'=>1,
					'graffiti'=>1,
					'first_page'=>'stats',
					'pages_allowed'=>array('removals','records','reviews','stats','sites','settings','campaigns','blog','proxies','tweets','lcmc','sidebar'))
	);


/*
	Connect to memcache
*/
global $memcache;
define('MEMCACHED_HOST', '127.0.0.1');
define('MEMCACHED_PORT', '11211');
$memcache = new Memcache;
$cacheAvailable = $memcache->connect(MEMCACHED_HOST, MEMCACHED_PORT);


# Database
$connect = mysqli_connect('NONE', 'NONE', 'NONE', 'NONE');

?>
