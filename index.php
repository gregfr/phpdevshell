<?php

/**
 * Ok this is what it is all about, start PHPDevShell engine and let the games begin.
 *
 * PHP version 5
 *
 * @category PHP
 * @package  PHPDevShel
 * @author   jason <titanking@phpdevshell.org>
 * @author   greg <greg@phpdevshell.org>
 * @license  LGPL
 * @version  Release: 3.5.0
 * @link     http://www.phpdevshell.org
 */

// Enable this only if you are debugging the early stages of PHPDS's initialization.
// Really only used for core PHPDevShell development.
$early_debug = false;

$start_time = microtime(true);

// this is stupid, but *required* by PHP :(   TODO: make it better! if possible...
date_default_timezone_set('America/Los_Angeles');

// Super high level exception if all else truly fails.
try {
    define('BASEPATH', realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
    $includes = array('/includes/', '/includes/legacy/', '/includes/local');
    foreach ($includes as $path) {
        ini_set('include_path', ini_get('include_path') . PATH_SEPARATOR . realpath(BASEPATH . $path));
    }

    if (file_exists('./index.local.php')) {
        /** @noinspection PhpIncludeInspection */
        include './index.local.php';
    } else {
        include 'includes/PHPDS.inc.php';
        $PHPDS = new PHPDS;
        $PHPDS->run();
    }
} catch (Exception $e) {
    if ($early_debug) {
        error_log('Uncaught exception!' . $e);
    }
    print <<<EOH
<h1>Uncaught exception!</h1>
<p>PHPDevShell encountered a serious error, please check all files and their permissions.
Some components could be missing.</p>
EOH;
    print '<div style="color: red">'.$e->getMessage()
        . ' in '.$e->getFile().' on line '.$e->getLine().'</div>';
    if (!empty($PHPDS) && (is_a($PHPDS, 'PHPDS'))) {
        $config = $PHPDS->PHPDS_configuration();
        if (!empty($config['error']['display'])) {
            print "<pre>$e</pre>";
        }
    }
    //todo: be smarter about the path
    print '<p>You might want to run the <a href="/other/service/install.php">installation script.</a></p>';
}