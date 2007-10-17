<?php

require_once dirname(__FILE__) . '/../../SolarUnitTest.config.php';

abstract class Solar_View_HelperTestCase extends PHPUnit_Framework_TestCase
{
    
    protected $_view;
    
    protected $_name;
    
    protected $_request;
    
    public function setup()
    {
        parent::setup();
        $this->_view = Solar::factory('Solar_View');
        $this->_name = substr(get_class($this), 18, -4);
        $this->_name[0] = strtolower($this->_name[0]);
        
        // register the request environment so all classes have access to it,
        // not just the helpers.
        if (! Solar_Registry::exists('request')) {
            Solar_Registry::set('request', 'Solar_Request');
        }
        
        // retain and reset the request environment
        $this->_request = Solar_Registry::get('request');
        $this->_request->reset();
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
