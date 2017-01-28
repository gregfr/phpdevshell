<?php

require_once 'PHPDS_db.class.php';
require_once 'PHPDS_query.class.php';


/**
 * This is a fake database connector, used to test feature *using* a connector without actually connecting to a db server
 */
class TEST_mock_connector extends PHPDS_dependant implements iPHPDS_dbConnector
{
    private $link;
    private $result;

    public $data = null;

    public $stubborn = false; // use to block certain behavior (free)

    protected $testData = null;

    private $pointer = 0;
    private $lastid = 0;

    /**
     * {@inheritDoc}
     */
    public function free()
    {
        if ($this->stubborn) {
            return false;
        }

        $this->data = array();
        $this->pointer = 0;
        $this->lastid = 0;

        return true;
    }

    /**
     * Inject data into the connector so it will be retrieved later as if it was sent by the database
     *
     * @param array $data
     */
    public function stubdata(array $data)
    {
        $this->free();
        $this->testData = $data;
    }

    /**
     * {@inheritDoc}
     */
    public function connect($db_config = null)
    {
        $this->link = true;
    }

    /**
     * {@inheritDoc}
     */
    public function disconnect()
    {
        $this->link = null;
    }

    /**
     * {@inheritDoc}
     */
    public function query($sql, $parameters = null)
    {
        if (is_null($this->data)) {
            return false;
        }
        if (empty($this->link)) {
            $this->connect();
        }
        $this->pointer = 0;
        $this->lastid++;
        $this->data = $this->testData;

        return 1; // should not be true or false so... 1 is ok
    }

    /**
     * {@inheritDoc}
     */
    public function protect($param)
    {
        return mysql_real_escape_string($param);
    }

    /**
     * {@inheritDoc}
     */
    public function fetchAssoc()
    {
        if ($this->pointer >= count($this->data)) return false;
        return $this->data[$this->pointer++];
    }

    /**
     * {@inheritDoc}
     */
    public function seek($row_number)
    {
        $this->pointer = $row_number;
        if ($this->pointer < 0) $this->pointer = 0;
        if ($this->pointer > count($this->data)) $this->pointer = count($this->data) - 1;
    }

    /**
     * {@inheritDoc}
     */
    public function numrows()
    {
        return count($this->data);
    }

    /**
     * {@inheritDoc}
     */
    public function affectedRows()
    {
        return -1;
    }

    public function returnSqlError($query)
    {
        return '';
    }

    /**
     * {@inheritDoc}
     */
    public function debugInstance($domain = null)
    {
        return parent::debugInstance('db');
    }

    /**
     * {@inheritDoc}
     */
    public function lastId($reset = false)
    {
        if ($reset) $this->lastid = 0;
        return $this->lastid;
    }

    /**
     * {@inheritDoc}
     */
    public function rowResults($row = 0)
    {
        return $this->data[$row];
    }

    /**
     * {@inheritDoc}
     */
    public function startTransaction()
    {
        // do nothing
    }

    /**
     * {@inheritDoc}
     */
    public function endTransaction($commit = true)
    {
        // do nothing
    }

    /**
     * Returns as much information as possible on the server
     *
     * @since 3.5
     * @date  20130609 (1.0) (greg) added
     *
     * @return array
     *
     **/
    public function serverInfo()
    {
        // dummy stub
    }
}

/**
 * This is a fake query, it can used to test a connector or a feature using query-related functions
 */
class TEST_stubQuery extends PHPDS_query
{
    protected $connector = 'TEST_mock_connector';
    public $sql = '';
    public $returnId;

    public $stubborn = false; // use to block certain behavior (checKParamters)

    /**
     * Inject data into the connector so it will be retrieved later as if it was sent by the database
     *
     * @param array $data
     */
    public function stubdata(array $data)
    {
        $this->connector->stubdata($data);
    }

    /**
     * {@inheritDoc}
     */
    public function checkParameters(&$parameters = null)
    {
        return $this->stubborn ? false : parent::checkParameters($parameters);
    }

    // allow easy access from the test scripts to the fields (make them public)
    public $singleRow;
    public $singleValue;
    public $typecast;
    public $keyField;
    public $focus;
    //public $getLastID;
}

/**
 * This is fake "db" object of the PHPDevShell backbone
 *
 * Defined here only to use a fake connector as a default
 */
class TEST_stubDB extends PHPDS_db
{
    public function construct()
    {
        $this->connector = $this->factory('TEST_mock_connector');
    }
}

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


/**
 * This base class is used to test the various connectors
 *
 * All connectors implementations should comply to this
 *
 */
class TEST_dbConnector extends PHPUnit_Framework_TestCase
{
    /* @var TEST_stubQuery $query */
    protected $query;

    /* todo: add more on this */
    public $sql_protection_dataset = array(
        array('a', 'a'),
        array('\\\'a', "'a"),
        array('\\"a', '"a'),
        array('\\na', "\na")
    );


    public function testConnectorBasics()
    {
        $data = array(
            array('col1' => 1, 'col2' => 'one', 'col3' => 0),
            array('col1' => 2, 'col2' => 'two', 'col3' => 1),
        );
        /* @var TEST_mock_connector $stub */
        $stub = $this->query->connector();
        $stub->connect();
        $stub->startTransaction();
        $stub->query('SELECT * FROM _db_legacyQuery_1');

        $this->assertEquals($data[0], $stub->fetchAssoc());
        $this->assertEquals($data[1], $stub->fetchAssoc());

        $stub->endTransaction(); // TODO: test rollback
    }

    /**
     * @dataProvider providerProtectBasics
     */
    public function testProtectBasics($output, $input)
    {
        /* @var TEST_mock_connector $stub */
        $stub = $this->query->connector();
        $this->assertEquals($output, $stub->protect($input));
    }

    /**
     * Test data provider for testProtectBasics()
     * @return array
     */
    public function providerProtectBasics()
    {
        return $this->sql_protection_dataset;
    }

    public function testQueryBasics1()
    {
        /*$data_in = array(
            array('col1' => 1, 'col2' => 'one', 'col3' => false, 'col4' => 'abc'),
            array('col1' => 2, 'col2' => 'two', 'col3' => true, 'col4' => '15'),
            array('col1' => 20, 'col2' => 'twenty', 'col3' => null, 'col4' => null),
        );*/
        $data_expected = array(
            1 => array('col1' => 1, 'col2' => 'one', 'col3' => false, 'col4' => 0),
            2 => array('col1' => 2, 'col2' => 'two', 'col3' => true, 'col4' => 15),
            20 => array('col1' => 20, 'col2' => 'twenty', 'col3' => false, 'col4' => 0),
        );
        $this->query->sql('SELECT * FROM _db_legacyQuery_2');
        $this->query->invoke();

        $this->query->typecast = array('col3' => 'boolean', 'col4' => 'int');

        $result = $this->query->invoke();
        $this->assertEquals($data_expected, $result, 'Testing as_array');
        $this->assertEquals(3, $this->query->count()); // result rows
        $this->assertEquals(3, $this->query->total()); // affected rows

        $this->query->singleRow = true;
        $result = $this->query->invoke();
        $this->assertEquals($data_expected[1], $result, 'Testing single_row');
        $this->assertEquals(1, $this->query->count()); // result rows
        $this->assertEquals(3, $this->query->total()); // affected rows

        $this->query->singleValue = true;
        $result = $this->query->invoke();
        $this->assertEquals($data_expected[1]['col1'], $result, 'Testing single_value');
        $this->assertEquals(1, $this->query->count()); // result rows
        $this->assertEquals(3, $this->query->total()); // affected rows

    }

    public function testQueryBasics2()
    {
        try {
            $this->query->sql('DELETE FROM _db_legacyQuery_1 WHERE col3 = 2');
            $result = $this->query->invoke();
        } catch (PHPDS_exception $e) {
            // row already exists...
        }

        $this->query->sql('INSERT INTO _db_legacyQuery_1 SET col1 = 3, col2 = "three", col3 = 2');
        $result = $this->query->invoke();

        $this->assertEquals(true, $result);
        $this->assertEquals(-1, $this->query->count()); // result rows
        $this->assertEquals(1, $this->query->total()); // affected rows

        $this->query->returnId = true;
        $this->query->sql('INSERT INTO _db_legacyQuery_1 SET col1 = 4, col2 = "three", col3 = 2');
        $result = $this->query->invoke();
        $this->assertEquals(4, $result);
        $this->assertEquals(-1, $this->query->count()); // result rows
        $this->assertEquals(1, $this->query->total()); // affected rows


        $this->query->returnId = false;
        $this->query->sql('DELETE FROM _db_legacyQuery_1 WHERE col3 = 2');
        $result = $this->query->invoke();
        $this->assertEquals(true, $result);
        $this->assertEquals(-1, $this->query->count()); // result rows
        $this->assertEquals(2, $this->query->total()); // affected rows

    }
    public function testQueryBasics3()
    {
        $this->query->sql = '';
        $this->query->sql('');
        $this->setExpectedException('PHPDS_exception');
        $this->assertFalse($this->query->invoke());
    }

    public function testExceptionBasics1()
    {
        $this->setExpectedException('PHPDS_databaseException');
        $this->query->sql('THIS IS NOT SQL');
        $result = $this->query->invoke();
    }

    public function testTransactionBasics1()
    {
        /* @var TEST_mock_connector $stub */
        $stub = $this->query->connector();
        $stub->connect();

        $query = $this->query;

        try {
            $query->sql('DELETE FROM _db_legacyQuery_1 WHERE col3 = -1');
            $result = $this->query->invoke();
        } catch (PHPDS_exception $e) {
            // row already exists...
        }


        // test rollback
        $stub->startTransaction();
        $query->sql('INSERT INTO _db_legacyQuery_1 SET col1 = 5, col2 = "TEST", col3 = -1');
        $query->invoke();
        $stub->endTransaction(false);

        $query->sql('SELECT col3 FROM _db_legacyQuery_1 WHERE col3 = -1');
        $result = $query->invoke();
            $this->assertEquals(array(), $result);

        // test commit
        $stub->startTransaction();
        $query->sql('INSERT INTO _db_legacyQuery_1 SET col1 = 5, col2 = "TEST", col3 = -1');
        $query->invoke();
        $stub->endTransaction(true);

        $query->sql('SELECT col3 FROM _db_legacyQuery_1 WHERE col3 = -1');
        $result = $query->invoke();
        $this->assertEquals(array('col3' => -1), $result[0]);

    }

    /**
     * TODO: we used to provide a workaround for row counts, is it still necessary?
     */
    public function _testCounts()
    {
        $this->query->sql('SELECT * FROM _db_legacyQuery_2'); // as such, this should give 3 rows

        // first, with default value of the count flag

        $this->query->countRows = false;

        // single row
        $this->query->singleRow = true;
        $this->query->singleValue = false;
        $result = $this->query->invoke();
        $this->assertEquals(-1, $this->query->selectedRows, 'Testing result row count of a single row result (non-MySQL flaw)');
        $this->assertEquals(0, $this->query->affectedRows, 'Testing total row count of a single row result (non-MySQL flaw)');

        // single value
        $this->query->singleRow = false;
        $this->query->singleValue = true;
        $result = $this->query->invoke();
        $this->assertEquals(-1, $this->query->selectedRows, 'Testing result row count of a single value result (non-MySQL flaw)');
        $this->assertEquals(0, $this->query->affectedRows, 'Testing total row count of a single value result (non-MySQL flaw)');

        // default, multi rows
        $this->query->singleRow = false;
        $this->query->singleValue = false;
        $result = $this->query->invoke();
        $this->assertEquals(3, $this->query->selectedRows, 'Testing result row count of a multi row result (non-MySQL flaw)');
        $this->assertEquals(0, $this->query->affectedRows, 'Testing total row count of a multi row result (non-MySQL flaw)');

        // second, with our workaround

        $this->query->countRows = true;

        // single row
        $this->query->singleRow = true;
        $this->query->singleValue = false;
        $result = $this->query->invoke();
        $this->assertEquals(1, $this->query->selectedRows, 'Testing result row count of a single row result (workaround)');
        $this->assertEquals(0, $this->query->affectedRows, 'Testing total row count of a single row result (workaround)');

        // single value
        $this->query->singleRow = false;
        $this->query->singleValue = true;
        $result = $this->query->invoke();
        $this->assertEquals(1, $this->query->selectedRows, 'Testing result row count of a single value result (workaround)');
        $this->assertEquals(0, $this->query->affectedRows, 'Testing total row count of a single value result (workaround)');

        // default, multi rows
        $this->query->singleRow = false;
        $this->query->singleValue = false;
        $result = $this->query->invoke();
        $this->assertEquals(3, $this->query->selectedRows, 'Testing result row count of a multi row result (workaround)');
        $this->assertEquals(0, $this->query->affectedRows, 'Testing total row count of a multi row result (workaround)');
    }

}