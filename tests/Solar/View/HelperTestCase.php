<?php

require_once dirname(__FILE__) . '/../../SolarUnitTest.config.php';

abstract class Solar_View_HelperTestCase extends PHPUnit_Framework_TestCase
{
    
    protected $_view;
    
    protected $_name;
    public function setup()
    {
        parent::setup();
        $this->_view = Solar::factory('Solar_View');
        $this->_name = substr(get_class($this), 18, -4);
        $this->_name[0] = strtolower($this->_name[0]);
    }
    
    public function teardown()
    {
        parent::teardown();
    }
    
    public function test__construct()
    {
        $actual = $this->_view->getHelper($this->_name);
        $expect = 'Solar_View_Helper_' . ucfirst($this->_name);
        $this->assertType($expect, $actual);
    }
}
