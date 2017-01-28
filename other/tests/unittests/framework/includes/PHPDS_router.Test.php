n <?php

if (!defined('BASEPATH')) die("\nDon't forget the bootstrap!!\n\n");

/** @noinspection PhpIncludeInspection */
require_once BASEPATH . '/includes/PHPDS_controller.class.php';
/**
 * Testing the URL routing system
 */
class PHPDS_routerTest extends PHPUnit_Framework_TestCase
{
    /* @var PHPDS_router $obj */
    protected $obj;


    protected function setUp()
    {
        /* @var TEST_main $instance */
        $instance = TEST_main::instance();
        $this->obj = $instance->_factory('PHPDS_router');

    }

    public function testRoute1()
    {
        $this->obj->addRoute(123, '/');

        $this->assertEquals(123, $this->obj->matchRoute('/'));

        $this->obj->addRoute(124, '/test1');
        $this->assertEquals(123, $this->obj->matchRoute('/'));
        $this->assertEquals(124, $this->obj->matchRoute('/test1'));

        $this->obj->addRoute(125, '/test2', 'mod1');
        $this->assertEquals(123, $this->obj->matchRoute('/'));
        $this->assertEquals(124, $this->obj->matchRoute('/test1'));
        $this->assertEquals(125, $this->obj->matchRoute('/test2'));
        $this->assertEquals(125, $this->obj->matchRoute('/mod1/test2'));

        $this->obj->addRoute(126, '/test2', 'mod2');
        $this->assertEquals(123, $this->obj->matchRoute('/'));
        $this->assertEquals(124, $this->obj->matchRoute('/test1'));
        $this->assertEquals(126, $this->obj->matchRoute('/test2'));
        $this->assertEquals(125, $this->obj->matchRoute('/mod1/test2'));
        $this->assertEquals(126, $this->obj->matchRoute('/mod2/test2'));
    }

    public function testRoute2()
    {
        $this->obj->addRoute(133, '/test/test1');
        $this->obj->addRoute(133, '/test/test2');
        $this->obj->addRoute(134, '/test/test3');
        $this->assertFalse($this->obj->matchRoute('/test'));
        $this->assertEquals(133, $this->obj->matchRoute('/test/test1'));
        $this->assertEquals(133, $this->obj->matchRoute('/test/test2'));
        $this->assertEquals(134, $this->obj->matchRoute('/test/test3'));

    }

    public function testRoute3()
    {
        $this->obj->addRoute(135, '/test5/(:var)');
        $this->obj->addRoute(136, '/test5/(#var)');
        $this->assertEquals(array(), $this->obj->parameters());

        $this->assertEquals(136, $this->obj->matchRoute('/test5/5'));
        $this->assertEquals(array('var' => '5'), $this->obj->parameters());

        $this->assertEquals(135, $this->obj->matchRoute('/test5/edit'));
        $this->assertEquals(array('var' => 'edit'), $this->obj->parameters());

        $this->assertEquals(135, $this->obj->matchRoute('/test5/n5'));
        $this->assertEquals(array('var' => 'n5'), $this->obj->parameters());

        $this->assertEquals(135, $this->obj->matchRoute('/test5'));
        $this->assertEquals(array(), $this->obj->parameters());

        $this->obj->addRoute(137, '/test5/edit');


        $this->assertEquals(136, $this->obj->matchRoute('/test5/5'));
        $this->assertEquals(array('var' => '5'), $this->obj->parameters());

        $this->assertEquals(137, $this->obj->matchRoute('/test5/edit'));
        $this->assertEquals(array(), $this->obj->parameters());

        $this->assertEquals(135, $this->obj->matchRoute('/test5'));
        $this->assertEquals(array(), $this->obj->parameters());
    }

    public function testRouteIncorrect()
    {
        $this->assertFalse($this->obj->addRoute('', ''));
    }
}