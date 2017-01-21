<?php
/**
 * StandardLogin/config/host.config.php
 *
 * This file will be executed whatever site is responding, in order to add the "/logout" virtual route
 *
 * PHP version 5
 *
 * @category PHP
 * @package  StandardLogin
 * @author   greg <greg@phpdevshell.org>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.phpdevshell.org
 *
 */


/**
 * This very simple implementation of deferred provides the "/logout" virtual URL
 *
 * @category PHP
 * @package  StandardLogin
 * @author   greg <greg@phpdevshell.org>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.phpdevshell.org
 */
class LOGIN_logoutDeferred extends PHPDS_dependant implements iPHPDS_deferred
{
    /**
     * Install the route
     *
     * @return void
     */
    public function construct()
    {
        $this->router->addRoute($this, '/logout');
    }

    /**
     * Actual logout and go back to the default page;
     *
     * @return int
     */
    public function reduce()
    {
        $this->factory('StandardLogin')->clearLogin();
        return 0;
    }

    /**
     * We do nothing here
     *
     * @param mixed $controller_result whatever was returned by the controller's run
     *
     * @return void
     */
    public function success($controller_result = null)
    {
    }

    /**
     * Part to execute if the action triggered has failed
     *
     * @return void
     */
    public function failure($something = null)
    {

    }
}

/* @var PHPDS $this */
$this->PHPDS_classFactory()->registerClass('#LOGIN_logoutDeferred', 'LOGIN_logoutDeferred', 'StandardLogin');