<?php


/**
 *
 *

CREATE TABLE IF NOT EXISTS `TEST_legacyQuery_1` (
`col1` int(11) NOT NULL,
`col2` text NOT NULL,
`col3` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `TEST_legacyQuery_1` (`col1`, `col2`, `col3`) VALUES
(1, 'one', 0),
(2, 'two', 1);


CREATE TABLE IF NOT EXISTS `TEST_legacyQuery_2` (
`col1` int(11) NOT NULL,
`col2` text NOT NULL,
`col3` tinyint(1) NOT NULL,
`col4` int(11) NOT NULL,
PRIMARY KEY (`col1`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


INSERT INTO `TEST_legacyQuery_2` (`col1`, `col2`, `col3`, `col4`) VALUES
(1, 'one', 0, 0),
(2, 'two', 1, 15),
(20, 'twenty', 0, 0);
 *
 *
 */


if (!defined('BASEPATH')) die("\nDon't forget the bootstrap!!\n\n");

require_once BASEPATH.'/includes/databases/PHPDS_legacyConnector.class.php';


/**
 * Connector info to connect to a real MySQL server
 */
class TEST_mysqlConnector extends PHPDS_legacyConnector
{
    public $dbSettings = array(
//        'dsn' => 'mysql:host=localhost;dbname=PHPDS_test',
        'host' => 'localhost',
        'database' => 'PHPDS_test',
        'username' => 'PHPDS_test',
        'password' => 'PHPDS_test',
        'prefix' => 'TEST_'
    );
}

/**
 * Test query
 */
class TEST_legacyQuery extends TEST_stubQuery
{
    protected $connector = 'TEST_mysqlConnector';
    public $sql = '';

    public $keyField = 'col1';
}


/**
 * @outputBuffering disabled
 */
class TEST_legacyConnectorTest extends TEST_dbConnector
{

    protected function setUp()
    {
        /* @var TEST_main $PHPDS */
        $PHPDS = TEST_main::instance();
        $configuration = $PHPDS->PHPDS_configuration();
        
        $this->query = $PHPDS->_factory('TEST_legacyQuery');

        $configuration['database_name'] = 'PHPDS_test';
        $configuration['database_user_name'] = 'PHPDS_test';
        $configuration['database_password'] = 'PHPDS_test';
        $configuration['server_address'] = 'localhost';
        $configuration['persistent_db_connection'] = false;
        $configuration['database_prefix'] = 'TEST_';

    }


}


