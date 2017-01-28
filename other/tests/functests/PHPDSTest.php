<?php

require_once 'WebDriver-PHP/WebDriver.php';
require_once 'WebDriver-PHP/WebDriver/Driver.php';
require_once 'WebDriver-PHP/WebDriver/MockDriver.php';
require_once 'WebDriver-PHP/WebDriver/WebElement.php';
require_once 'WebDriver-PHP/WebDriver/MockElement.php';
require_once 'WebDriver-PHP/WebDriver/FirefoxProfile.php';

class PHPDSFunctionalTest extends PHPUnit_Framework_TestCase
{
    protected $driver;

    public function setUp()
    {
        // If you want to set preferences in your Firefox profile
        $fp = new WebDriver_FirefoxProfile();
        $fp->set_preference("capability.policy.default.HTMLDocument.compatMode", "allAccess");

        // Choose one of the following

        // For tests running at Sauce Labs
//     $this->driver = WebDriver_Driver::InitAtSauce(
//       "my-sauce-username",
//       "my-sauce-api-key",
//       "WINDOWS",
//       "firefox",
//       "10",
//       array(
//         'firefox_profile' => $fp->get_profile()
//       ));
//     $sauce_job_name = get_class($this);
//     $this->driver->set_sauce_context("name", $sauce_job_name);

        // For a mock driver (for debugging)
//     $this->driver = new WebDriver_MockDriver();
//     define('kFestDebug', true);

        // For a local driver
        $this->driver = WebDriver_Driver::InitAtLocal("4444", "firefox");
    }

    // Forward calls to main driver
    public function __call($name, $arguments)
    {
        if (method_exists($this->driver, $name)) {
            return call_user_func_array(array($this->driver, $name), $arguments);
        } else {
            throw new Exception("Tried to call nonexistent method $name with arguments:\n" . print_r($arguments, true));
        }
    }

    public function subTestPage($nodeid, $headingText)
    {
        /* @var WebDriver_Driver $driver */
        $driver = $this->driver;

        $driver->load('http://localhost/pds-stable/index.php?m='.$nodeid);
        $driver->assert_element_present('id=heading');
        $driver->get_element('id=heading')->assert_text($headingText);

        $driver->assert_element_present('class=home');
        $driver->assert_element_present('class=jump');
    }

    public function testGuestView()
    {
        /* @var WebDriver_Driver $driver */
        $driver = $this->driver;

        $driver->load('http://localhost/pds-stable/index.php');
        $driver->assert_string_not_present('An error occured');
        $driver->assert_title('Readme - My Cool Site');
        $driver->get_element("css=h1")->assert_text('PHPDevShell V 3.2.0-Stable-DB-3140');

        $driver->assert_element_present('id=menu_2266433229'); // readme
        $driver->assert_element_present('id=menu_3727066128'); // register account
        $driver->assert_element_present('id=menu_1901799184'); // lost password

        $driver->assert_element_present('id=logged-out');

        $this->subTestPage('3727066128', 'Register Private Account');
        $this->subTestPage('2266433229', 'Starting with PHPDevShell');
        $this->subTestPage('1901799184', 'Recover Lost Password');
    }

    public function doLogin($name = 'root', $passwd = 'root')
    {
//    $this->set_implicit_wait(5000);
        /* @var WebDriver_Driver $driver */
        $driver = $this->driver;
        $driver->set_implicit_wait(5000);

        $driver->load('http://localhost/pds-stable/index.php');
        $driver->assert_string_not_present('An error occured');

        $driver->assert_element_present('id=logged-out');

        $driver->get_element('id=logged-out')->click();

        $driver->get_element('name=user_name')->send_keys($name);
        $driver->get_element('name=user_password')->send_keys($passwd);

        $driver->get_element('name=login')->click();

        $driver->assert_element_present('id=logged-in');
        $driver->get_element('id=logged-in')->assert_text('Root User');

    }

    public function testRootView()
    {
        $this->doLogin();
//    $this->set_implicit_wait(5000);
        /* @var WebDriver_Driver $driver */
        $driver = $this->driver;

        $driver->assert_element_not_present('id=menu_3727066128'); // register account
        $driver->assert_element_not_present('id=menu_1901799184'); // lost password


//    $this->get_element("id=q")->send_keys("webdriver");
//    $this->get_element("id=submit")->click();
//
//    $first_result = $this->get_element("css=a.gs-title")->get_text();
    }

    public function tearDown()
    {
        if ($this->driver) {
            if ($this->hasFailed()) {
                $this->driver->set_sauce_context("passed", false);
            } else {
                $this->driver->set_sauce_context("passed", true);
            }
            $this->driver->quit();
        }
        parent::tearDown();
    }
}