<?php


if (!defined('BASEPATH')) die("\nDon't forget the bootstrap!!\n\n");



/**
 * @outputBuffering disabled
 */
class TEST_stubQueryTest extends PHPUnit_Framework_TestCase
{
    public $data_in;
    public $data_expected;
    public $data_expected1;
    /* @var TEST_stubQuery $query */
    protected $query;
    protected $stub;

    protected function setUp()
    {
        /* @var TEST_main $PHPDS */
        $PHPDS = TEST_main::instance();
        /* @var TEST_stubQuery $query */
        $query = $PHPDS->_factory('TEST_stubQuery');


        $data_in = array(
            array('col1' => 1, 'col2' => 'one', 'col3' => false, 'col4' => 'abc'),
            array('col1' => 2, 'col2' => 'two', 'col3' => true, 'col4' => '15'),
            array('col1' => 20, 'col2' => 'twenty', 'col3' => null, 'col4' => null),
        );
        $data_expected = array(
            1 => array('col1' => 1, 'col2' => 'one', 'col3' => false, 'col4' => 0),
            2 => array('col1' => 2, 'col2' => 'two', 'col3' => true, 'col4' => 15),
            20 => array('col1' => 20, 'col2' => 'twenty', 'col3' => false, 'col4' => 0),
        );


        $this->data_in = $data_in;
        $this->data_expected = $data_expected;

        $query->stubdata($data_in);

        $this->query = $query;
    }

    public function testConnector()
    {
        $data = array(
            array('col1' => 1, 'col2' => 'one', 'col3' => false),
            array('col1' => 2, 'col2' => 'two', 'col3' => true),
        );
        /*
        // todo: check exceptions
        $this->setExpectedException('PHPDS_DatabaseException');
        $result = $this->query->invoke();*/

        /* @var TEST_mock_connector $stub */
        $stub = $this->query->connector();
        $stub->stubdata($data);
        $stub->query('');

        $this->assertEquals($data[0], $stub->fetchAssoc());
        $this->assertEquals($data[1], $stub->fetchAssoc());
    }

    public function testQuery1()
    {
        $this->query->run();
        $this->assertEquals($this->data_in[0]['col1'], $this->query->asOne());
        $this->assertEquals($this->data_in[1]['col1'], $this->query->asOne());
        $this->assertEquals($this->data_in[2]['col1'], $this->query->asOne());
        $this->assertEquals(null, $this->query->asOne());

        $this->query->run();
        $this->assertEquals($this->data_in[0], $this->query->asLine());
        $this->assertEquals($this->data_in[1], $this->query->asLine());
        $this->assertEquals($this->data_in[2], $this->query->asLine());
        $this->assertEquals(null, $this->query->asLine());

        $this->query->run();
        $this->assertEquals(array($this->data_in[0]['col1'], $this->data_in[1]['col1'], $this->data_in[2]['col1']), $this->query->asArray('col1'));
        $this->assertEquals(array(), $this->query->asArray('col2'));

        $this->query->run();
        $this->assertEquals(array($this->data_in[0]['col2'], $this->data_in[1]['col2'], $this->data_in[2]['col2']), $this->query->asArray('col2'));
        $this->assertEquals(array(), $this->query->asArray('col2'));
    }

    public function testQuery2()
    {
        $this->query->keyField = 'col1';

        $this->query->typecast = array('col3' => 'boolean', 'col4' => 'int');

        $result = $this->query->invoke();
        $this->assertEquals($this->data_expected, $result, 'Testing as_array');
        $this->assertEquals(3, $this->query->count());
        $this->assertEquals(3, $this->query->total());

        $this->query->singleRow = true;
        $this->query->singleValue = false;
        $result = $this->query->invoke();
        $this->assertEquals($this->data_expected[1], $result, 'Testing singleRow / !singleValue');
        $this->assertEquals(1, $this->query->count());
        $this->assertEquals(3, $this->query->total());

        $this->query->singleRow = false;
        $this->query->singleValue = true;
        $result = $this->query->invoke();
        $this->assertEquals($this->data_expected[1]['col1'], $result, 'Testing singleValue');
        $this->assertEquals(1, $this->query->count());
        $this->assertEquals(3, $this->query->total());


        $this->query->focus = 'col2';

        $this->query->singleRow = true;
        $result = $this->query->invoke();
        $this->assertEquals($this->data_expected[1]['col2'], $result, 'Testing focused single_row');
        $this->assertEquals(1, $this->query->count());
        $this->assertEquals(3, $this->query->total());

        $this->query->singleRow = false;
        $result = $this->query->invoke();
        $this->assertEquals($this->data_expected[1]['col2'], $result, 'Testing focused !single_row');
        $this->assertEquals(1, $this->query->count());
        $this->assertEquals(3, $this->query->total());


    }

    public function testStubborn()
    {
        /* @var TEST_mock_connector $stub */
        $stub = $this->query->connector();
        $stub->stubborn = true;

        $this->setExpectedException('PHPDS_databaseException');
        $this->query->query();

        $this->query->stubborn= true;
        $this->setExpectedException('PHPDS_exception');
        $this->query->query();
    }


    /**
     * @dataProvider castScalarValueProvider
     * @group database
     */
    public function testTypeCastScalar($in, $type, $expected)
    {
        $this->query->typecast = $type;
        $out = $this->query->typecast($in);
        $this->assertEquals($out, $expected);

        if ('double' == $type) $type = 'float';
        $this->assertInternalType($type, $expected);
    }

    /**
     * @return array
     */
    public function castScalarValueProvider()
    {
        return array(
            array(0, 'int', 0), // dataset 0
            array(0, 'integer', 0),
            array(0, 'string', '0'),
            array(0, 'float', 0.0),
            array(0, 'double', 0.0),
            array(0, 'bool', false), // dataset 5
            array(0, 'boolean', false),

            array(-123, 'int', -123),
            array(-123, 'integer', -123),
            array(-123, 'string', '-123'),
            array(-123, 'float', -123.0), // dataset 10
            array(-123, 'double', -123.0),
            array(-123, 'bool', true),
            array(-123, 'boolean', true),

            array(1.5, 'int', 1),
            array(1.5, 'integer', 1), // dataset 15
            //array(1.5, 'string', '1,5'), // careful this could be locale-dependant TODO
            array(1.5, 'float', 1.5),
            array(1.5, 'double', 1.5),
            array(1.5, 'bool', true),
            array(1.5, 'boolean', true),

            array('a', 'int', 0),
            array('a', 'integer', 0),
            array('a', 'string', 'a'),
            array('a', 'float', 0.0),
            array('a', 'double', 0.0),
            array('a', 'bool', true),
            array('a', 'boolean', true),

            array('', 'int', 0),
            array('', 'integer', 0),
            array('', 'string', ''),
            array('', 'float', 0.0),
            array('', 'double', 0.0),
            array('', 'bool', false),
            array('', 'boolean', false),
        );
    }


    /**
     * @dataProvider castArrayValueProvider
     * @group database
     */
    public function testTypeCastArray($in_array, $type_array, $expected_array)
    {
        $this->query->typecast = $type_array;
        $out = $this->query->typecast($in_array);
        $this->assertEquals($out, $expected_array);

        foreach ($out as $key => $value) {
            $type = $type_array[$key];
            $this->assertNotNull($type);
            if ('double' == $type) $type = 'float';
            $this->assertInternalType($type, $value);
        }
    }

    /**
     * @return array
     */
    public function castArrayValueProvider()
    {
        return array(
            array(
                array('first' => 0, 'second' => 0, 'third' => 0), array('first' => 'int', 'second' => 'string', 'third' => 'bool'), array('first' => 0, 'second' => '0', 'third' => false),
                array('first' => -1.5, 'second' => 'abc', 'third' => true), array('first' => 'int', 'second' => 'string', 'third' => 'bool'), array('first' => 1, 'second' => 'abc', 'third' => true),
                array('first' => '-1.5', 'second' => '', 'third' => 3.5), array('first' => 'int', 'second' => 'string', 'third' => 'bool'), array('first' => -1, 'second' => '', 'third' => true),
                array('first' => '0', 'second' => '0', 'third' => 'hello'), array('first' => 'int', 'second' => 'string', 'third' => 'bool'), array('first' => 0, 'second' => '0', 'third' => true),
            )
        );
    }


    public function testForceScalar()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }


}

/*class usernames extends PHPDS_query
{
        protected $sql = 'SELECT names FROM users';
}


class PHPDS_queryTest_wikidoc extends PHPUnit_Framework_TestCase
{
    protected $query;
    protected $stub;

    protected function setUp()
    {
        $db = PHPDSlib::instance()->PHPDS_db();
        $this->query = $db->factory('TEST_stubQuery');
        $this->stub = $db->factory('TEST_mock_connector');
        $this->query->connector($this->stub);
    }

    public function testWikiDoc()
    {

        $data_in = array(
            array('id' => 1, 'name' => 'root', 'age' => 100),
            array('id' => 2, 'name' => 'Jason', 'age' => 35),
            array('id' => 3, 'name' => 'Greg', 'age' => 37),
        );
        $this->stub->data = $data_in;
    }

}

*/
