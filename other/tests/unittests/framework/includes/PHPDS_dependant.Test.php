<?php


/**
 * @method void method()
 */
class TEST_dependant extends PHPDS_dependant
{
    public $A = 'b';
    protected $B = '2';
    protected $C = '1';


    protected $parent;
    protected $sub;

    public function construct($a = '', $b = '')
    {
        $this->A = $a;
        $this->B = $b;

        if ($b < 2) {
            $this->parent = new TEST_dependantSub;
        } else {
            $this->sub = new TEST_dependantSub;
            $this->parent = 'sub';
        }
    }

    public function getRoots()
    {
        return $this->parent;
    }

    public function C($value = null)
    {
        if (!is_null($value)) {
            $this->C = intval($value) * 10;
        }
        return '(' . $this->C . ')';
    }
}

class TEST_dependantSub
{
    public function method()
    {
        return "success";
    }
}


class PHPDS_dependantTest extends PHPUnit_Framework_TestCase
{
    protected $obj;


    protected function setUp()
    {

    }

    public function testFactory1()
    {
        /* @var PHPDS $instance */
        $instance = TEST_main::instance();
        /* @var TEST_dependant $obj */
        $obj = $instance->_factory('TEST_dependant', array('a', 1));

        $this->assertEquals('TEST_dependant', get_class($obj));

        $this->assertEquals('TEST_dependantSub', get_class($obj->getRoots()));

        $this->assertEquals('TEST_stubDB', get_class($obj->db));

        $this->assertEquals('success', $obj->method());
    }

    public function testFactory2()
    {
        /* @var PHPDS $instance */
        $instance = TEST_main::instance();
        /* @var TEST_dependant $obj */
        $obj = $instance->_factory('TEST_dependant', array('a', 2));

        $this->assertEquals('TEST_dependant', get_class($obj));

        $this->assertEquals('sub', $obj->getRoots());

        $this->assertEquals('TEST_stubDB', get_class($obj->db));

        $this->assertEquals('success', $obj->method());
    }

    public function testAccessors()
    {
        /* @var PHPDS $instance */
        $instance = TEST_main::instance();
        /* @var TEST_dependant $obj */
        $obj = $instance->_factory('TEST_dependant', array('a', 3));

        $this->assertEquals('a', $obj->A); // A is public

        $this->assertEquals(3, $obj->B); // B is protected

        // this feature has been disabled
        /*$obj->C = 5;
        $this->assertEquals('(50)', $obj->C); // B is accessed*/

        // this feature has been disabled too
        //$this->setExpectedException('Exception'); // B is protected
        $obj->B = 4;

    }
}