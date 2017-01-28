<?php

	if (!defined('BASEPATH')) die("\nDon't forget the bootstrap!!\n\n");

	/*require_once BASEPATH.'/includes/PHPDS_query.class.php';
	require_once BASEPATH.'/includes/PHPDS_tagger.class.php';
	require_once BASEPATH.'/includes/PHPDS_tagger.query.php';*/

	class PHPDS_tagTest extends PHPUnit_Framework_TestCase
	{
			protected $tagger;


			protected function setUp()
			{
				$PHPDS = TEST_main::instance();
				$this->tagger = $PHPDS->_factory('PHPDS_tagger'); // this will be $core->PHPDS_tagger();
			}


			/**
			 * @dataProvider tagValueProvider
			 * @group tagging
			 */
			public function testGenericTagCallWithValues($object, $name, $target, $value)
			{
				$this->tagger->tag($object, $name, $target, $value);
				$test_value = $this->tagger->tag($object, $name, $target);
				$this->assertEquals($test_value, $value);
			}

			/**
			 * @dataProvider tagValueProvider
			 * @group tagging
			 */
			public function testUserTagCallWithValues($object, $name, $target, $value)
			{
				$this->tagger->tagUser($name, $target, $value);
				$test_value = $this->tagger->tagUser($name, $target);
				$this->assertEquals($test_value, $value);
			}

			/**
			 * @dataProvider tagValueProvider
			 * @group tagging
			 */
			public function testRoleTagCallWithValues($object, $name, $target, $value)
			{
				$this->tagger->tagRole($name, $target, $value);
				$test_value = $this->tagger->tagRole($name, $target);
				$this->assertEquals($test_value, $value);

				$test_value = $this->tagger->tag(PHPDS_tagger::tag_role, $name, $target);
				$this->assertEquals($test_value, $value);
			}

			/**
			 * @dataProvider tagValueProvider
			 * @group tagging
			 */
			public function testGroupTagCallWithValues($object, $name, $target, $value)
			{
				$this->tagger->tagGroup($name, $target, $value);

				$test_value = $this->tagger->tagGroup($name, $target);
				$this->assertEquals($test_value, $value);

				$test_value = $this->tagger->tag(PHPDS_tagger::tag_group, $name, $target);
				$this->assertEquals($test_value, $value);
			}

			public function tagValueProvider()
			{
				return array(
					array('__testobject__', '__testname__', '__testtarget__', '__testvalue1__'),
					array('__testobject__', '__testname__', '__testtarget__', '__testvalue2__'),
					array('__testobject2__', '__testname__', '__testtarget__', '__testvalue2__'),
					array('__testobject__', '__testname2__', '__testtarget__', '__testvalue2__'),
					array('__testobject__', '__testname__', '__testtarget2__', '__testvalue2__')
				);
			}

			/**
			 * @depends testGroupTagCallWithValues
			 */
			public function testGroupTagList()
			{
				$list = $this->tagger->tagList('__testname__', PHPDS_tagger::tag_group);
				$tag = array_shift($list);
				$this->assertEquals(array('tagID' => $tag['tagID'], 'tagObject' => 'group', 'tagName' => '__testname__', 'tagTarget' => '__testtarget2__', 'tagValue' => '__testvalue2__'), $tag);

				$list = $this->tagger->tagList('__testname__', PHPDS_tagger::tag_group, '__testtarget2__');
				$this->assertType(PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY, $list);
				$tag = array_shift($list);
				$this->assertEquals(array('tagID' => $tag['tagID'], 'tagObject' => 'group', 'tagName' => '__testname__', 'tagTarget' => '__testtarget2__', 'tagValue' => '__testvalue2__'), $tag);
			}


	}