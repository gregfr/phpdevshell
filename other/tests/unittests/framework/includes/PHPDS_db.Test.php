<?php

if (!defined('BASEPATH')) die("\nDon't forget the bootstrap!!\n\n");

/** @noinspection PhpIncludeInspection */
require_once BASEPATH.'/includes/PHPDS_db.class.php';
	
/**
 * NOTE: this test file focuses on utilities which do not require an actual/mock db connection. 
 */


/**
 * Test class for PHPDS_db.
 */
class PHPDS_dbTest extends PHPUnit_Framework_TestCase
{
    /** @var  PHPDS_db */
    protected $object;

	protected function setUp()
	{
		$PHPDS = TEST_main::instance();
		$this->object = $PHPDS->_factory('PHPDS_db');

		$this->object->construct();
	}

    /**
     * @dataProvider providerprotectArray
     */
	function testprotectArray($input, $output)
	{
		//	public function protectArray(array $a, $quote = '')
		$this->assertEquals($output, $this->object->protectArray($input));
	}

	function providerprotectArray()
	{
		return array(
			array(array(), array()),
			array(array('a'), array('a')),
			array(array('"a"'), array('\"a\"')),
			array(array('"b\''), array('\"b\\\'')),
			array(array('\"c\''), array('\\\\\"c\\\'')),
			array(array('"d\'', '"e'."\r"), array('\"d\\\'', '\"e\\r'))
		);
	}
}