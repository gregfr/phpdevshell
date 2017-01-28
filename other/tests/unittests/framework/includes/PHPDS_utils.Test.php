<?php

    if (!defined('BASEPATH')) {
        die("\nDon't forget the bootstrap!!\n\n");
    }

    require_once BASEPATH.'/includes/PHPDS_utils.inc.php';


    class PHPDS_utilsTest extends PHPUnit_Framework_TestCase
    {
        protected function setUp()
        {
        }

        public function test_BuildGETArray()
        {
            $a = array('a' => true, 'B' => 10, 'c' =>'test');

            $this->assertEquals(array(), PU_BuildGETArray(array()));
            $this->assertEquals($a, PU_BuildGETArray($a));
            $this->assertEquals(array('a' => true, 'B' => 10, 'c' =>'test', 'D' => 'testing'), PU_BuildGETArray($a, array('D' => 'testing')));
            $this->assertEquals(array('a' => true, 'c' =>'test'), PU_BuildGETArray($a, null, array('B')));
            $this->assertEquals(array('a' => true, 'c' =>'test', 'D' => 'testing'), PU_BuildGETArray($a, array('D' => 'testing'), array('B')));

            $this->setExpectedException('PHPUnit_Framework_Error');
            PU_BuildGETArray('test');
        }

        public function test_BuildGETString()
        {
            $this->assertEquals('', PU_BuildGETString(array()));
            $this->assertEquals('', PU_BuildGETString(array(), '&'));

            $this->assertEquals('?a=1', PU_BuildGETString(array('a' => 1)));
            $this->assertEquals('?a=1', PU_BuildGETString(array('a' => 1), '&'));

            $this->assertEquals('?a=1&amp;b=test', PU_BuildGETString(array('a' => 1, 'b' => 'test')));
            $this->assertEquals('?a=1&b=test', PU_BuildGETString(array('a' => 1, 'b' => 'test'), '&'));

            $this->assertEquals('?a=%26%20%C3%A9&amp;b=test', PU_BuildGETString(array('a' => '& é', 'b' => 'test')));
            $this->assertEquals('?a=%26%20%C3%A9&b=test', PU_BuildGETString(array('a' => '& é', 'b' => 'test'), '&'));
        }

        public function test_BuildGET_Empty()
        {
            /**
             * function PU_BuildGET($includeInGet = null, $excludeFromGet = null, $glue = '&amp;')
             *
             * Build GET part of a url
             *
             * @param $includeInGet	(optional) array of pairs: parameters to add as GET in the url
             * @param $excludeFromGet (optional) array of strings: parameters to remove from GET in the url
             * @return string the whole parameter part of the url (including '?') ; maybe empty if there are no parameters
             */

            $_GET = array();

            $this->assertEquals('', PU_BuildGET(null,  null));
            $this->assertEquals('?a=1', PU_BuildGET(array('a' => 1),  null));
            $this->assertEquals('?a=1&amp;b=y%20s', PU_BuildGET(array('a' => 1, 'b' => 'y s'),  null));
            $this->assertEquals('?a=1', PU_BuildGET(array('a' => 1, 'b' => 'y s'),  'b'));

            $this->assertEquals('?a=y%20s', PU_BuildGET(array('a' => 'y s'),  null));
            $this->assertEquals('?a=1&b=y%20s', PU_BuildGET(array('a' => 1, 'b' => 'y s'), null, '&'));
            $this->assertEquals('?a=1', PU_BuildGET(array('a' => 1, 'b' => 'y s'),  'b', '&'));
        }

        public function test_BuildGET_NotEmpty()
        {
            /**
             * function PU_BuildGET($includeInGet = null, $excludeFromGet = null, $glue = '&amp;')
             *
             * Build GET part of a url
             *
             * @param $includeInGet	(optional) array of pairs: parameters to add as GET in the url
             * @param $excludeFromGet (optional) array of strings: parameters to remove from GET in the url
             * @return string the whole parameter part of the url (including '?') ; maybe empty if there are no parameters
             */

            $_GET = array('c' => 'no');

            $this->assertEquals('?c=no', PU_BuildGET(null,  null));
            $this->assertEquals('?c=no&amp;a=1', PU_BuildGET(array('a' => 1),  null));
            $this->assertEquals('?c=no&amp;a=1', PU_BuildGET(array('a' => 1),  null));
            $this->assertEquals('?c=no&amp;a=1', PU_BuildGET(array('a' => 1),  null));
            $this->assertEquals('?c=no&amp;a=1&amp;b=y%20s', PU_BuildGET(array('a' => 1, 'b' => 'y s'),  null));
            $this->assertEquals('?c=no&amp;a=1', PU_BuildGET(array('a' => 1, 'b' => 'y s'),  'b'));

            $this->assertEquals('?c=no&amp;a=y%20s', PU_BuildGET(array('a' => 'y s'),  null));
            $this->assertEquals('?c=no&a=1&b=y%20s', PU_BuildGET(array('a' => 1, 'b' => 'y s'),  null, '&'));
            $this->assertEquals('?c=no&a=1', PU_BuildGET(array('a' => 1, 'b' => 'y s'),  'b', '&'));
        }

        public function test_BuildURL()
        {
            //PU_BuildURL($target = null, $includeInGet = null, $excludeFromGet = null, $glue = '&amp;')
            $this->assertEquals('http://TEST/test.php', PU_BuildURL());
            $this->assertEquals('http://TEST/target.php', PU_BuildURL('/target.php'));
            $this->assertEquals('target.php', PU_BuildURL('target.php'));

            $this->assertEquals('target.php?a=z', PU_BuildURL('target.php', array('a' => 'z')));
            $this->assertEquals('target.php?a=1&amp;b=2', PU_BuildURL('target.php?a=1&b=2'));
            $this->assertEquals('target.php?a=z&amp;b=2', PU_BuildURL('target.php?a=1&b=2', array('a' => 'z')));
            $this->assertEquals('target.php?a=z&amp;b=2&amp;c=y', PU_BuildURL('target.php?a=1&b=2', array('a' => 'z', 'c' => 'y')));
            $this->assertEquals('target.php?a=1&amp;b=2&amp;c=y', PU_BuildURL('target.php?a=1&b=2', array('c' => 'y')));

            $this->assertEquals('target.php', PU_BuildURL('target.php', null, array('a')));
            $this->assertEquals('target.php?b=2', PU_BuildURL('target.php?a=1&b=2', null, array('a')));
            $this->assertEquals('target.php?b=2', PU_BuildURL('target.php?a=1&b=2', array('a' => 'z'), array('a')));
            $this->assertEquals('target.php?b=2&amp;c=y', PU_BuildURL('target.php?b=2', array('a' => 'z', 'c' => 'y'), array('a')));
            $this->assertEquals('target.php?c=y', PU_BuildURL('target.php?c=z', array('c' => 'y'), array('a')));
        }

        public function test_BuildAttrString()
        {
            $this->assertEquals('', PU_BuildAttrString(array()));
            $this->assertEquals(' a="1"', PU_BuildAttrString(array('a' => 1)));
            $this->assertEquals(' a="1" b="y s"', PU_BuildAttrString(array('a' => 1, 'b' => 'y s')));
        }

        public function test_CleanString()
        {
            $this->assertEquals('', PU_CleanString(''));
            $this->assertEquals('a', PU_CleanString('a'));
            $this->assertEquals('a', PU_CleanString('a"'));
            $this->assertEquals('ab', PU_CleanString('a"\'\\\\b"'));
        }

        public function test_IsAjax()
        {
            $data = array('test' => true);

            $_SERVER["HTTP_X_REQUESTED_WITH"] = '';
            $_SERVER["HTTP_X_REQUESTED_TYPE"] = '';

            $this->assertFalse(PU_isAJAX());
            $this->assertEquals('', PU_isJSON($data));


            $_SERVER["HTTP_X_REQUESTED_WITH"] = 'XMLHttpRequest';
            $_SERVER["HTTP_X_REQUESTED_TYPE"] = '';

            $this->assertTrue(PU_isAJAX());
            $this->assertEquals('', PU_isJSON($data));
        }

        public function test_IsJson()
        {
            $data = array('test' => true);

            $_SERVER["HTTP_X_REQUESTED_WITH"] = '';
            $_SERVER["HTTP_X_REQUESTED_TYPE"] = 'json';

            $this->assertFalse(PU_isAJAX());
            $this->assertEquals('', PU_isJSON($data));


            $_SERVER["HTTP_X_REQUESTED_WITH"] = 'XMLHttpRequest';
            $_SERVER["HTTP_X_REQUESTED_TYPE"] = 'json';

            $this->assertTrue(PU_isAJAX());
            $this->assertEquals('{"test":true}', PU_isJSON($data));

            // phpunit expects an open buffer after that
            ob_start();
        }

        public function test_arrayCompact()
        {
            /**
             * function PU_array_compact(array $a)
             *
             * Get rid of null values inside an array
             *
             * All values which are null in the array are remove, shortening the array
             *
             * @param array $a the array to compact
             */
            $this->assertEquals(array(), PU_arrayCompact(array()));
            $this->assertEquals(array('a' => 1), PU_arrayCompact(array('a' => 1)));
            $this->assertEquals(array('a' => 1), PU_arrayCompact(array('a' => 1, 'b' => null)));
            $this->assertEquals(array('a' => 1, 'c' => 'test'), PU_arrayCompact(array('a' => 1, 'b' => null, 'c' => 'test')));
        }

        public function test_sprintfn()
        {
            $this->assertEquals('', PU_sprintfn('', array()));
            $this->assertEquals('', PU_sprintfn('', array('a' => 'test')));

            $this->assertEquals('TEST', PU_sprintfn('TEST', array('a' => 'test')));
            $this->assertEquals('TEST test', PU_sprintfn('TEST %s', array('a' => 'test')));
            $this->assertEquals('TEST test', PU_sprintfn('TEST %1$s', array('a' => 'test')));
            $this->assertEquals('TEST test', PU_sprintfn('TEST %(a)s', array('a' => 'test')));

            $this->assertEquals('TEST right', PU_sprintfn('TEST %2$s', array('a' => 'wrong', 'b' => 'right')));
            $this->assertEquals('TEST right', PU_sprintfn('TEST %(b)s', array('a' => 'wrong', 'b' => 'right')));

        }

        public function testSprintfnException1()
        {
            $this->setExpectedException('PHPDS_sprintfnException');
            PU_sprintfn('TEST %2$s', array('a' => 'wrong'));
        }

        public function test_SprintfnException2()
        {
            $this->setExpectedException('PHPDS_sprintfnException');
            PU_sprintfn('TEST %(b)s', array('a' => 'wrong'));
        }

        public function  test_SprintfnArray()
        {
            $this->assertEquals('TEST ', PU_sprintfn('TEST %(a)s', array('a' => array())));
            $this->assertEquals('TEST one, two', PU_sprintfn('TEST %(a)s', array('a' => array('one', 'two'))));

        }

        public function test_BuildParsedURL()
        {
            $result = PU_buildParsedURL(array(
                'path' => '/where',
                'port' => 81,
                'fragment' => 'here'
            ));

            $this->assertEquals('http://TEST:81/where#here', $result);
        }

        public function test_BuildHREF()
        {
            /**
             * function PU_BuildHREF($label, $includeInGet = null, $excludeFromGet = null, $target = null, array $attrs = null)
             *
             * Build a html link (A+HREF html tag) with label and url and GET parameters
             *
             * @param $label string: the text of the link
             * @param $includeInGet (optional) array of pairs: parameters to add as GET in the url
             * @param $excludeFromGet (optional) array of strings: parameters to remove from GET in the url
             * @param $target (optional) string: the target script url (current script if missing)
             * @return string the complete html link
             */

            $this->assertEquals('<a href="popup.html?a=A&amp;c=C">link</a>', PU_BuildHREF('link', array('a' => 'A', 'b' => 'B', 'c' => 'C'), array('b'), 'popup.html'));
            $this->assertEquals('<a href="popup.html?a=A&amp;b=B&amp;c=C">link</a>', PU_BuildHREF('link', array('a' => 'A', 'b' => 'B', 'c' => 'C'), null, 'popup.html'));
            $this->assertEquals('<a href="http://TEST/test.php?a=A&amp;c=C">link</a>', PU_BuildHREF('link', array('a' => 'A', 'b' => 'B', 'c' => 'C'), array('b')));
            $this->assertEquals('<a href="popup.html">link</a>', PU_BuildHREF('link', null, array('b'), 'popup.html'));
            $this->assertEquals('<a href="popup.html?a=A&amp;b=B&amp;c=C">link</a>', PU_BuildHREF('link', array('a' => 'A', 'b' => 'B', 'c' => 'C'), array('d'), 'popup.html'));
            //$this->assertEquals('<a href="popup.html?a=A&amp;c=C">link</a>', PU_BuildHREF('link', array('a' => 'A', 'b' => 'B', 'c' => 'C'), array('b'), 'popup.html', array('id' => 'mylink')));
        }

        function test_MakeString()
        {
            /**
             * function PU_MakeString($string, $htmlize = false)
             *
             * Convert a string to UTF8 (default) or to HTML
             *
             * @param $string the string to convert
             * @param $htmlize if true the string is converted to HTML, if nul to UTF8; otherwise specified encoding
             *
             * @return string
             */

            $this->markTestIncomplete(
                'This test has not been implemented yet.'
            );
            /*$this->assertEquals(base64_decode('RcOpJiU='), PU_MakeString('Eé&%'));
            $this->assertEquals('E&eacute;&amp;%', PU_MakeString('Eé&%', true));*/
        }

        function test_ArraySearch()
        {
            /**
             * function PU_ArraySearch($needle, $haystack)
             * Search for array values inside array and returns key.
             *
             * @param array $needle
             * @param array $haystack
             * @return mixed
             */

            $a = array(
                'a' => array('A'),
                'b' => array('B')
            );
            $this->assertFalse(PU_ArraySearch(array(), $a));
            $this->assertFalse(PU_ArraySearch(array('C'), array()));
            $this->assertFalse(PU_ArraySearch(array('C'), null));

            $this->assertEquals('b', PU_ArraySearch(array('B'), $a));
            $this->assertFalse(PU_ArraySearch(array('C'), $a));
        }

        function test_numval()
        {
            $this->assertEquals(0, numval(0));
            $this->assertEquals(0, numval(''));
            $this->assertEquals(0, numval('0'));
            $this->assertEquals(10, numval(10));
            $this->assertEquals(10, numval('10'));
            $this->assertEquals('123456789', numval('123456789'));
            $this->assertEquals(0, numval(null));
            $this->assertEquals(0, numval(array('a' => 'A')));
        }

        function testPackEnv()
        {
            //$this->assertEquals('', PU_PackEnv()); TODO
        }

        function test_SafeSubpath()
        {
            $this->assertEquals(BASEPATH.'other/tests', PU_SafeSubpath(BASEPATH.'other/tests', BASEPATH));
            $this->assertEquals(BASEPATH.'other/tests', PU_SafeSubpath('other/tests', BASEPATH));

            $this->assertEquals(BASEPATH.'other/tests', PU_SafeSubpath(BASEPATH.'other/tests'));
            $this->assertEquals(BASEPATH.'other/tests', PU_SafeSubpath('other/tests'));

            $this->assertEquals(BASEPATH.'tests', PU_SafeSubpath(BASEPATH.'tests', BASEPATH, 'other'));
            $this->assertEquals(BASEPATH.'other/tests', PU_SafeSubpath('tests', BASEPATH, 'other'));

            $this->assertEquals(BASEPATH.'tests', PU_SafeSubpath(BASEPATH.'tests', '', 'other'));
            $this->assertEquals(BASEPATH.'other/tests', PU_SafeSubpath('tests', '', 'other'));

            $this->assertFalse(PU_SafeSubpath('/etc/passwd', BASEPATH));
            $this->assertFalse(PU_SafeSubpath(BASEPATH.'../../../../etc/passwd', BASEPATH));

            $this->assertFalse(PU_SafeSubpath('/this-dir-does-not-exists'));
            $this->assertFalse(PU_SafeSubpath('/this-dir-does-not-exists', BASEPATH));
            $this->assertFalse(PU_SafeSubpath('/this-dir-does-not-exists', ''));
        }

        function test_GetDBSettings()
        {
            $PHPDS = TEST_main::instance();
            /*$settings = array(
                'database_name' =>'PHPDS_test',
                'database_user_name' =>'PHPDS_test',
                'database_password' =>'PHPDS_test',
                'server_address' =>'localhost',
                'persistent_db_connection' =>false,
                'database_prefix' =>''
            );*/
            $settings = array(
                'dsn' => '',
                'database' => 'PHPDS_test',
                'host' => 'localhost',
                'username' => 'PHPDS_test',
                'password' => 'PHPDS_test',
                'prefix' => 'pds_',
                'persistent' => false,
                'charset' => 'utf8'
            );
            //$this->assertEquals($settings, PU_GetDBSettings($PHPDS->PHPDS_configuration())); TODO
        }

        function test_safeName()
        {
            $this->assertEquals('a-b-c-d-e-f-g-h', PU_safeName('A--B C/D"E%F\\G\'H'));
            $this->assertEquals('abcdefgh', PU_safeName('A--B C/D"E%F\\G\'H', ''));
            $this->assertEquals('a.b.c.d.e.f.g.h', PU_safeName('A--B C/D"E%F\\G\'H', '.'));
            $this->assertFalse(PU_safeName(''));
            $this->assertEquals('-', PU_safeName('--'));
        }

        function test_rightTrim()
        {
            $this->assertEquals('ab', PU_rightTrim('abcd', 'cd'));
            $this->assertEquals('abcd', PU_rightTrim('abcd ', 'cd'));
            $this->assertEquals('abcd', PU_rightTrim('abcd', 'c'));
            $this->assertEquals('abcd', PU_rightTrim('abcd', ''));
        }

        public function test_addIncludePath()
        {
            $old_path = get_include_path();
            $me = __FILE__;

            PU_addIncludePath($me); // must be an existing file
            $new_path = set_include_path($old_path);

            $this->assertEquals($old_path.PATH_SEPARATOR.$me, $new_path);
        }
    }