<?php


    require_once 'mock_connector.php';

    require_once BASEPATH.'/includes/PHPDS_utils.inc.php';
    require_once BASEPATH.'/includes/legacy/user.class.php';
    require_once BASEPATH.'/includes/PHPDS_user.class.php';


    /*class TEST_userStubQuery  extends PHPDS_FindUserQuery
    {
    }*/

    class TEST_userStub extends PHPDS_user
    {

        protected function lookupQuery()
        {
            if (empty($this->lookupQuery)) {
                $this->lookupQuery = $this->db->makeQuery('TEST_userStubQuery');
                /* @var TEST_mock_connector $mockConnector */
                $mockConnector = $this->db->factory('TEST_mock_connector');

                $data = array(
                    array('user_id' => 1, 'user_display_name' => 'Root User', 'username' => 'root', 'user_password' => md5('pAsSw0rD'), 'user_email' => 'test@phpdevshell.org',
                        'user_group' => 1, 'user_role' => 1, 'date_registered' => 0, 'language' => 'en', 'timezone' => 'UTC', 'region' => ''),
                    array('user_id' => 2, 'user_display_name' => 'First Test User', 'username' => 'test1', 'user_password' => md5('pAsSw0rD'), 'user_email' => 'test1@phpdevshell.org',
                        'user_group' => 2, 'user_role' => 2, 'date_registered' => 0, 'language' => 'en', 'timezone' => 'UTC', 'region' => ''),
                );
                $mockConnector->stubdata($data);
                $this->lookupQuery->connector($mockConnector);
            }
            return $this->lookupQuery;
        }
    }

    /**
     * @outputBuffering disabled
     */
    class PHPDS_userTest extends PHPUnit_Framework_TestCase
    {
        /*protected $query;
        protected $stub;*/


        protected function setUp()
        {
            /*$db = PHPDSlib::instance()->PHPDS_db();
            $this->query = $db->factory('TEST_userStubQuery');
            $this->stub = $db->factory('TEST_mock_connector');
            $this->query->connector($this->stub);


            $data = array(
                array('userID' => 1, 'user_display_name' => 'Root User', 'username' => 'root', 'user_password' => '', 'user_email' => 'test@phpdevshell.org',
                    'user_group' => 1, 'user_role' => 1, 'date_registered' => 0, 'language' => 'en', 'timezone' => 'UTC', 'region' => ''),
                array('userID' => 2, 'user_display_name' => 'First Test User', 'username' => 'test1', 'user_password' => '', 'user_email' => 'test1@phpdevshell.org',
                    'user_group' => 2, 'user_role' => 2, 'date_registered' => 0, 'language' => 'en', 'timezone' => 'UTC', 'region' => ''),
            );
            $this->stub->data = $data;*/
        }

        public function testUser()
        {
            $this->markTestIncomplete(
                            'This test has not been implemented yet.'
            );
            /*$db = PHPDSlib::instance()->PHPDS_db();
            $u = $db->factory('TEST_userStub');
            $this->assertEquals(0, $u->user_id);
            $this->assertEquals(1, $u->load('root'));
            $this->assertEquals(1, $u->user_id);

            $this->assertEquals(0, $u->load('csv_imported'));
            $this->assertEquals(0, $u->user_id);
            $csv_line = "10\tCSV Imported\tcsv_imported\tpAsSw0rD\ttest_import@phpdevshell.org\t2\t2\t0\ten\\t";
            $fields = array('user_id', 'user_display_name', 'user_name', 'user_group', 'user_role', 'date_registered', 'language', 'timezone', 'region');
            $this->assertTrue($u->import_csv_line($csv_line, $fields));
            $this->assertEquals(10, $u->load('csv_imported'));
            $this->assertEquals(10, $u->user_id);*/
        }

    }