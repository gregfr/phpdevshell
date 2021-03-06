<?php

if (!defined('BASEPATH')) die("\nDon't forget the bootstrap!!\n\n");

require_once BASEPATH.'/includes/PHPDS_core.class.php';




/**
 * Test class for PHPDS_navigation.
 * Generated by PHPUnit on 2011-08-09 at 01:06:29.
 */
class PHPDS_navigationTest extends PHPUnit_Framework_TestCase
{
    /* @var PHPDS_navigation $object */
    protected $object;

    protected function setUp()
    {
        /* @var TEST_main $PHPDS */
        $PHPDS = TEST_main::instance();
        $this->object = $PHPDS->_factory('PHPDS_navigation');

        $this->object->construct();
    }

    public function test_determineMenuName()
    {
        $this->assertEquals('replace', $this->object->determineMenuName('replace','link', 123, 'plugin'));
        $this->assertEquals('link', $this->object->determineMenuName('','link', 123, 'plugin'));
    }

    public function test_showMenu()
    {
        $this->assertTrue($this->object->showMenu(4, 123, 123));
        $this->assertTrue($this->object->showMenu(0, 123, 456));
        $this->assertTrue($this->object->showMenu(2, 123, 456));
        $this->assertFalse($this->object->showMenu(3, 123, 456));
    }

    public function test_createMenuId()
    {
        $this->assertEquals(3632233996, $this->object->createMenuId('test'));
        $this->assertEquals(4008350648, $this->object->createMenuId('TEST'));
    }
}