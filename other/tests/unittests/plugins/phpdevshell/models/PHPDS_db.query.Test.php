<?php


// WORK IN EARLY PROGRESS DO NOT USE


if (!defined('BASEPATH')) die("\nDon't forget the bootstrap!!\n\n");


class DB_countRowsQueryTest extends \PHPUnit_Framework_TestCase
{

    protected function setUp()
    {
        /* @var TEST_main $PHPDS */
        $PHPDS = TEST_main::instance();
        /* @var TEST_stubQuery $query */
        $query = $PHPDS->_factory('DB_countRowsQuery');


        $data_in = array(
            array('col1' => 1, 'col2' => 'one', 'col3' => false, 'col4' => 'abc'),
            array('col1' => 2, 'col2' => 'two', 'col3' => true, 'col4' => '15'),
            array('col1' => 20, 'col2' => 'twenty', 'col3' => null, 'col4' => null),
        );
        $data_expected = array(
            1  => array('col1' => 1, 'col2' => 'one', 'col3' => false, 'col4' => 0),
            2  => array('col1' => 2, 'col2' => 'two', 'col3' => true, 'col4' => 15),
            20 => array('col1' => 20, 'col2' => 'twenty', 'col3' => false, 'col4' => 0),
        );


        $this->data_in = $data_in;
        $this->data_expected = $data_expected;

        $query->stubdata($data_in);

        $this->query = $query;
    }

}