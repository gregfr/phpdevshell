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

require_once BASEPATH . '/includes/databases/PHPDS_pdoConnector.class.php';



/**
 * Connector info to connect to a real MySQL server
 */
class TEST_pdoMysqlConnector extends PHPDS_pdoConnector
{
    public $dbSettings = array(
        'dsn' => 'mysql:host=localhost;dbname=PHPDS_test',
        'username' => 'PHPDS_test',
        'password' => 'PHPDS_test',
        'prefix' => 'TEST_'
    );

    public $link;
}

/**
 * Query to test against the real MySQL server
 */
class TEST_pdoMysqlQuery extends TEST_stubQuery
{
    protected $connector = 'TEST_pdoMysqlConnector';
    public $sql = '';

    public $keyField = 'col1';
}


/**
 * @outputBuffering disabled
 */
class TEST_pdoMysqlConnectorTest extends TEST_dbConnector
{
    /* @var TEST_pdoMysqlQuery $query */
    protected $query;
    /* @var TEST_pdoMysqlConnector $stub */
    protected $stub;

    public $test_db_settings = array(
        'dsn' => 'mysql:host=localhost;dbname=PHPDS_test',
        'username' => 'PHPDS_test',
        'password' => 'PHPDS_test',
        'prefix' => 'TEST_'
    );

    protected function setUp()
    {
        /* @var TEST_main $PHPDS */
        $PHPDS = TEST_main::instance();
        /* @var TEST_pdoMysqlQuery $query */
        $query = $PHPDS->_factory('TEST_pdoMysqlQuery');
        $this->query = $query;
        /* @var TEST_pdoMysqlConnector $stub */
        $stub = $this->query->connector();

        $stub->applyConfig($this->test_db_settings);
        $this->assertEquals('mysql:host=localhost;dbname=PHPDS_test', $stub->dsn);
    }


    public function testDSNConfig()
    {
        /* @var TEST_main $PHPDS */
        $PHPDS = TEST_main::instance();
        $configuration = $PHPDS->PHPDS_configuration();


        $configuration['databases']['PDO_test'] = $this->test_db_settings;

        $configuration['database_name'] = null; // disable legacy

        $configuration['databases']['PDO_fake'] = array(
            'driver' => 'fake6',
            'host' => 'localhost6',
            'database' => 'PHPDS_test6'
        );

        /* @var TEST_pdoMysqlConnector $stub */
        $stub = $this->query->connector();

        $this->assertEquals('mysql:host=localhost;dbname=PHPDS_test', $stub->dsn);

        $stub->applyConfig(array('dsn' => 'mysql:host=localhost2;dbname=PHPDS_test2'));
        $this->assertEquals('mysql:host=localhost2;dbname=PHPDS_test2', $stub->dsn);

        $stub->applyConfig(array());
        $this->assertEquals('mysql:host=localhost2;dbname=PHPDS_test2', $stub->dsn);

        $stub->applyConfig(array('database' => 'PHPDS_test4'));
        $this->assertEquals('mysql:host=localhost;dbname=PHPDS_test4', $stub->dsn);

        $stub->applyConfig(array('host' => 'localhost5', 'database' => 'PHPDS_test5'));
        $this->assertEquals('mysql:host=localhost5;dbname=PHPDS_test5', $stub->dsn);

        $stub->applyConfig(array('driver' => 'fake', 'host' => 'localhost5', 'database' => 'PHPDS_test5'));
        $this->assertEquals('fake:host=localhost5;dbname=PHPDS_test5', $stub->dsn);

        $stub->applyConfig(array('driver' => 'fake2', 'host' => 'localhost6', 'database' => 'PHPDS_test6', 'charset' => 'utf8'));
        $this->assertEquals('fake2:host=localhost6;dbname=PHPDS_test6;charset=utf8', $stub->dsn);

        $stub->applyConfig('PDO_fake');
        $this->assertEquals('fake6:host=localhost6;dbname=PHPDS_test6', $stub->dsn);

        $stub->dbSettings = null;
        $stub->applyConfig();
        $this->assertEquals('mysql:host=localhost;dbname=phpdev', $stub->dsn);

    }

    public function testMisc()
    {
        /* @var TEST_pdoMysqlConnector $stub */
        $stub = $this->query->connector();

        $settings = $this->test_db_settings;
        $settings['persistent'] = true;
        $stub->connect($settings);
        $this->assertEquals('mysql:host=localhost;dbname=PHPDS_test', $stub->dsn);

        $stub->link = true;

        $stub->applyConfig();
        $this->assertNull($stub->link);

        $stub->dbSettings = null;
        $stub->connect($this->test_db_settings);
        $this->assertEquals('mysql:host=localhost;dbname=PHPDS_test', $stub->dsn);

        $this->assertEquals('00000:  [] <br />SELECT * FROM legacyquery_1', $stub->returnSqlError('SELECT * FROM legacyquery_1'));
    }

    public function testConfigException()
    {
        /* @var TEST_pdoMysqlConnector $stub */
        $stub = $this->query->connector();

        $this->setExpectedException('PHPDS_exception');
        $stub->applyConfig($stub);
    }

    public function testConnectException()
    {
        /* @var TEST_pdoMysqlConnector $stub */
        $stub = $this->query->connector();

        $this->setExpectedException('PHPDS_databaseException');
        $stub->connect(array('host' => 'localhost5', 'database' => 'PHPDS_test5', 'persistent' => true));
    }


    public function testUninmplemented1()
    {
        $stub = $this->query->connector();
        /* @var TEST_pdoMysqlConnector $stub */

        $this->setExpectedException('PHPDS_exception');
        $stub->rowResults();
    }


    public function testUninmplemented2()
    {
        $stub = $this->query->connector();
        /* @var TEST_pdoMysqlConnector $stub */

        $this->setExpectedException('PHPDS_exception');
        $stub->seek(0);
    }


    /**
     * @group database
     */
    public function _testExtendedBuild()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet because they need a DB connection.'
        );
    }


}


