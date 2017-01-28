<?php

if (!defined('BASEPATH')) die("\nDon't forget the bootstrap!!\n\n");

require_once BASEPATH.'/includes/PHPDS_query.class.php';

/**
 * NOTE: this test file focuses on utilities which do not require an actual/mock query. 
 * See PHPDS_stubQueryTest.php for the other tests
 */


class TEST_query extends PHPDS_query
{
    /* make these fields available to test methods */
    public $fields;
    public $tables;
    public $where;
    public $autoProtect;
    public $autoQuote;
}

/**
 * Test class for PHPDS_query.
 */
class PHPDS_queryTest extends PHPUnit_Framework_TestCase
{
    /* @var TEST_query $object */
    protected $object;

    protected function setUp()
    {
        /* @var PHPDS $PHPDS */
        $PHPDS = TEST_main::instance();
        /* @var TEST_query $object */
        $object = $PHPDS->_factory('TEST_query');

        $object->construct();

        $this->object = $object;
    }

    function test_protectString()
    {
        $this->assertEquals('a', $this->object->protectString('a'));
        $this->assertEquals('\\\'a', $this->object->protectString("'a"));
        $this->assertEquals('\\"a', $this->object->protectString('"a'));
        $this->assertEquals('\\na', $this->object->protectString("\na"));

    }

    function test_build()
    {
        $this->object->fields = array(
          'field1' => true, 'field2' => true
        );
        $this->object->tables = 'table';
        $this->object->where = 'is_null(field1)';
        $this->object->preBuild();
        $this->object->addWhere('not is_null(field2)', 'OR');

        $expected_select = 'SELECT field1, field2 FROM table';
        $expected_extra = ' WHERE is_null(field1) OR not is_null(field2)  ';

        $this->assertEquals($expected_select, $this->object->sql());
        $this->assertEquals($expected_extra, $this->object->extraBuild());
        $this->assertEquals($expected_select.$expected_extra, $this->object->build());
    }

    public function test_protectBuild()
    {
        $base_sql = 'SELECT * FROM table WHERE field1 = ';
        $this->object->sql($base_sql.'%(f1)s');

        $this->assertEquals($base_sql.'test', $this->object->build(array('f1' => 'test')));

        $this->object->autoProtect = true;

        $this->assertEquals($base_sql . 'test', $this->object->build(array('f1' => 'test')));
        $this->object->autoQuote = "'";
        $this->assertEquals($base_sql . "'test'", $this->object->build(array('f1' => 'test')));
        $this->object->autoQuote = '"';
        $this->assertEquals($base_sql . '"test"', $this->object->build(array('f1' => 'test')));

        $this->object->autoQuote = null;
        $this->assertEquals($base_sql . "test\' OR \'a\' = \'a", $this->object->build(array('f1' => "test' OR 'a' = 'a")));
        $this->object->autoQuote = "'";
        $this->assertEquals($base_sql . "'test\' OR \'a\' = \'a'", $this->object->build(array('f1' => "test' OR 'a' = 'a")));
        $this->object->autoQuote = '"';
        $this->assertEquals($base_sql . '"test\\\' OR \\\'a\\\' = \\\'a"', $this->object->build(array('f1' => "test' OR 'a' = 'a")));
    }

    /**
     * @dataProvider provider_arrayToListSQL
     */
    function test_arrayToListSQL($input, $output, $protect = null)
    {
        $this->assertEquals($output, $this->object->arrayToListSQL($input, $protect));
    }

    /**
     * Test data provider for test_arrayToListSQL()
     * @return array
     */
    function provider_arrayToListSQL()
    {
        return array(
            array(array(), ''),
            array(array('a'), "(a)"),
            array(array('1'), "(1)"),
            array(array(null), "(NULL)"),

            array(array('a'), "(a)", false),
            array(array('1'), "(1)", false),
            array(array(null), "(NULL)", false),
            array(array('a', 'b'), "(a), (b)", false),
            array(array('1', '2'), "(1), (2)", false),
            array(array('a', null, '2'), "(a), (NULL), (2)", false),
            array(array(array('a', 'b'), array('c', 'd')), "(a, b), (c, d)", false),
            array(array(array('a', null, '2'), array('3', 'd', null)), "(a, NULL, 2), (3, d, NULL)", false),

            array(array('a'), "('a')", true),
            array(array('1'), "(1)", true),
            array(array(null), "(NULL)", true),
            array(array('a', 'b'), "('a'), ('b')", true),
            array(array('1', '2'), "(1), (2)", true),
            array(array('a', null, '2'), "('a'), (NULL), (2)", true),
            array(array(array('a', 'b'), array('c', 'd')), "('a', 'b'), ('c', 'd')", true),
            array(array(array('a', null, '2'), array('3', 'd', null)), "('a', NULL, 2), (3, 'd', NULL)", true),
        );
    }
}