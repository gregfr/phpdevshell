<?php
	$configuration['debug']['enable'] = true;
	$configuration['debug']['level'] = 4; // 	DEBUG = 4;INFO = 3;WARN = 2;ERROR = 1;LOG = 0;
	$configuration['debug']['firePHP'] = true;
	$configuration['debug']['serverlog'] = true;
	//$configuration['debug']['serverlog'] = false;
	//$configuration['debug']['domains'] = array('authlib', 'test', 'user', 'db', 'security', 'skel', 'core', '!');
	$configuration['debug']['domains'] = array('authlib', 'test', 'user', 'security');

	$configuration['error']['display'] = false;
	$configuration['error']['firePHP'] = true;
	$configuration['error']['ignore_notices'] = false;
	$configuration['error']['ignore_warnings'] = false;
	//$configuration['error']['file'] = '/tmp/phpdevshell.'.date('Y-m-d').'.log';
	//$configuration['error']['mail']= 'root@vecteurm.com';

	$configuration['production'] = false;
	
	$configuration['gzip'] = false;

	error_reporting(E_ALL);