<?php




if (!defined('BASEPATH')) die("\nDon't forget the bootstrap!!\n\n");

require_once BASEPATH.'/includes/models/PHPDS_user.query.php';

$user_roles = array(
    1 => array(1,2,3,4,5),
    2 => array(5),
    3 => array(3)
);

$user_groups = array(
    1 => array(1, 2, 3, 4, 5),
    2 => array(5),
    3 => array(3)
);


/**
 * @outputBuffering disabled
 */
class TEST_userQueryTest extends PHPUnit_Framework_TestCase
{
    /* @var TEST_mock_connector $connector */
    protected $connector;

    /* @var USER_getRolesQuery $object */
    protected $object;

    protected function setUp()
    {
        /* @var TEST_main $PHPDS */
        $PHPDS = TEST_main::instance();
        $PHPDS->PHPDS_db()->connectCacheServer();
        $configuration = $PHPDS->PHPDS_configuration();

        $configuration['root_role'] = 1;

        $classes = $PHPDS->PHPDS_classFactory();

        $classes->registerClass('TEST_USER_getExtraRolesQuery', 'USER_getExtraRolesQuery', 'TEST');
        $classes->registerClass('TEST_USER_getRolesQuery', 'USER_getRolesQuery', 'TEST');

        $classes->registerClass('TEST_USER_getExtraGroupsQuery', 'USER_getExtraGroupsQuery', 'TEST');
        $classes->registerClass('TEST_USER_getGroupsQuery', 'USER_getGroupsQuery', 'TEST');
    }

    public function testRolesForUser1()
    {
        /* @var PHPDS $PHPDS */
        $PHPDS = TEST_main::instance();
        $configuration = $PHPDS->PHPDS_configuration();

        /* @var USER_getRolesQuery $object */
        $object = $PHPDS->_factory('USER_getRolesQuery');


        $configuration['user_id'] = 1;
        $configuration['user_role'] = 1;

        $this->assertEquals('1,2,3,4,5', $object->invoke());
        $this->assertEquals('1,2,3,4,5', $object->invoke(1));

        $this->assertEquals('5,2', $object->invoke(2));
        $this->assertEquals('3', $object->invoke(3));

        // do the same call twice to check the cache behavior
        $this->assertEquals('1,2,3,4,5', $object->invoke());
        $this->assertEquals('1,2,3,4,5', $object->invoke(1));

        $this->assertEquals('5,2', $object->invoke(2));
        $this->assertEquals('3', $object->invoke(3));

        // todo: check exception handling
    }

    public function testRolesForUser2()
    {
        /* @var PHPDS $PHPDS */
        $PHPDS = TEST_main::instance();
        $configuration = $PHPDS->PHPDS_configuration();

        /* @var USER_getRolesQuery $object */
        $object = $PHPDS->_factory('USER_getRolesQuery');


        $configuration['user_id'] = 2;
        $configuration['user_role'] = 2;

        $this->assertEquals('1,2,3,4,5', $object->invoke(1));

        $this->assertEquals('5,2', $object->invoke());
        $this->assertEquals('5,2', $object->invoke(2));

        $this->assertEquals('3', $object->invoke(3));

        // do the same call twice to check the cache behavior
        $this->assertEquals('1,2,3,4,5', $object->invoke(1));

        $this->assertEquals('5,2', $object->invoke());
        $this->assertEquals('5,2', $object->invoke(2));

        $this->assertEquals('3', $object->invoke(3));
    }


    public function _testGroupsForUser1()
    {
        /* @var PHPDS $PHPDS */
        $PHPDS = TEST_main::instance();
        $configuration = $PHPDS->PHPDS_configuration();

        /* @var USER_getRolesQuery $object */
        $object = $PHPDS->_factory('USER_getGroupsQuery');


        $configuration['user_id'] = 1;
        $configuration['user_role'] = 1;
        $configuration['user_group'] = 1;

        $this->assertEquals('1,2,3,4,5', $object->invoke());
        $this->assertEquals('1,2,3,4,5', $object->invoke(1));

        $this->assertEquals('5,2', $object->invoke(2));
        $this->assertEquals('3', $object->invoke(3));

        // do the same call twice to check the cache behavior
        $this->assertEquals('1,2,3,4,5', $object->invoke());
        $this->assertEquals('1,2,3,4,5', $object->invoke(1));

        $this->assertEquals('5,2', $object->invoke(2));
        $this->assertEquals('3', $object->invoke(3));
    }

    public function __testGroupsForUser2()
    {
        /* @var PHPDS $PHPDS */
        $PHPDS = TEST_main::instance();
        $configuration = $PHPDS->PHPDS_configuration();

        /* @var USER_getRolesQuery $object */
        $object = $PHPDS->_factory('USER_getGroupsQuery');


        $configuration['user_id'] = 2;
        $configuration['user_role'] = 2;
        $configuration['user_group'] = 2;

        $this->assertEquals('1,2,3,4,5', $object->invoke(1));

        $this->assertEquals('5,2', $object->invoke());
        $this->assertEquals('5,2', $object->invoke(2));

        $this->assertEquals('3', $object->invoke(3));

        // do the same call twice to check the cache behavior
        $this->assertEquals('1,2,3,4,5', $object->invoke(1));

        $this->assertEquals('5,2', $object->invoke());
        $this->assertEquals('5,2', $object->invoke(2));

        $this->assertEquals('3', $object->invoke(3));
    }
}




























/**
 * This is a stub for USER_getExtraRolesQuery which doesn't actually call the DB
 */
class TEST_USER_getExtraRolesQuery extends USER_getExtraRolesQuery
{
    protected $data = array();

    /**
     * @param string $sql
     * @return array the fake user roles
     */
    public function querySQL($sql)
    {
        global $user_roles;

        $this->data = array();
        $matches = array();
        if (preg_match('/WHERE\s+user_id = (\d+)/', $sql, $matches)) {
            $user_id = $matches[1];
            $this->data = $user_roles[$user_id];
        }

        return $this->data;
    }

    /**
     * @return array
     */
    public function getResults()
    {
        return $this->data;
    }

}



/**
 * This is a stub for TEST_USER_getRolesQuery which doesn't actually call the DB
 */
class TEST_USER_getRolesQuery extends USER_getRolesQuery
{
    protected $data = 0;

    /**
     * @param string $sql
     * @return integer the fake user role
     */
    public function querySQL($sql)
    {
        $this->data = 0;
        $matches = array();
        if (preg_match('/WHERE\s+user_id = (\d+)/', $sql, $matches)) {
            $user_id = $matches[1];
            $this->data = $user_id;
        }

        return $this->data;
    }

    /**
     * @return null
     */
    public function getResults()
    {
        return $this->result;
    }
}





/**
 * This is a stub for USER_getExtraGroupsQuery which doesn't actually call the DB
 */
class TEST_USER_getExtraGroupsQuery extends USER_getExtraGroupsQuery
{
    protected $data = array();

    /**
     * @param string $sql
     * @return array the fake user groups
     */
    public function querySQL($sql)
    {
        global $user_groups;

        $this->data = array();
        $matches = array();
        if (preg_match('/WHERE\s+user_id = (\d+)/', $sql, $matches)) {
            $user_id = $matches[1];
            $this->data = $user_groups[$user_id];
        }

        return $this->data;
    }

    /**
     * @return array
     */
    public function getResults()
    {
        return $this->data;
    }

}

/**
 * This is a stub for USER_getGroupsQuery which doesn't actually call the DB
 */
class TEST_USER_getGroupsQuery extends USER_getGroupsQuery
{
    protected $data = 0;

    /**
     * @param string $sql
     * @return integer the fake user group
     */
    public function querySQL($sql)
    {
        $this->data = 0;
        $matches = array();
        if (preg_match('/WHERE\s+user_id = (\d+)/', $sql, $matches)) {
            $user_id = $matches[1];
            $this->data = $user_id;
        }

        return $this->data;
    }

    /**
     * @return integer the fake user role
     */
    public function getResults()
    {
        return $this->data;
    }
}

class TEST_USER_getGroupsChildrenQuery extends PHPDS_query
{
    protected $sql = "
        SELECT
            user_group_id, parent_group_id
        FROM
            _db_core_user_groups
    ";
}