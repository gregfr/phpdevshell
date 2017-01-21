<?php

/**
 *
 * Allows multiple sites to be installed from one core directory, select the domain and the config file to load.
 * Setup as $configuration['host'][[host/domain]] = [config file prefix [default]].config.php]; (Multiple)

 */
//$configuration['host'][$_SERVER['SERVER_NAME']]		= 'single-site'; // Will use single.config.php as default
//$configuration['host']['127.0.0.1']					= 'some-site';
//$configuration['host']['localhost']					= 'some-site'; // Will use some-site.config.php
//$configuration['host']['phpdevshell.org']				= 'phpdevshell.org'; // Will use phpdevshell.org.config.php


// $configuration['host']['pds-install']				= 'pds-install'; 