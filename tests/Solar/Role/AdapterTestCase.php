<?php

require_once dirname(__FILE__) . '/../../SolarUnitTest.config.php';

abstract class Solar_Role_AdapterTestCase extends PHPUnit_Framework_TestCase
{
    protected $_role;
    
    protected $_class;
    
    protected $_config = array();
    
    public function __construct($config = null)
    {
        parent::__construct($config);
        
        // convert from Solar_Role_Adapter_WhateverTest
        // to Solar_Role_Adapter_Whatever
        $this->_class = substr(get_class($this), 0, -4);
    }
    
    public function setup()
    {
        // get a new adapter
        $this->_role = Solar::factory($this->_class, $this->_config);
    }
    
    public function teardown()
    {
        parent::teardown();
    }
    
    public function test__construct()
    {
        $this->assertType($this->_class, $this->_role);
    }
    
    public function testFetch()
    {
        $expect = array('admin', 'root');
        $actual = $this->_role->fetch('pmjones');
        $this->assertEquals($actual, $expect);
    }
    
    public function testLoad()
    {
        $this->_role->load('pmjones');
        $expect = array('admin', 'root');
        $actual = $this->_role->list;
        $this->assertEquals($actual, $expect);
    }
    
    public function testLoad_refresh()
    {
        // load the first time
        $this->_role->load('pmjones');
        $expect = array('admin', 'root');
        $actual = $this->_role->list;
        $this->assertEquals($actual, $expect);
        
        // foribly refresh
        $this->_role->load('boshag', true);
        $expect = array('admin');
        $actual = $this->_role->list;
        $this->assertEquals($actual, $expect);
    }
    
    public function testReset()
    {
        // load the first time
        $this->_role->load('pmjones');
        $expect = array('admin', 'root');
        $actual = $this->_role->list;
        $this->assertEquals($actual, $expect);
        
        // reset to empty
        $this->_role->reset();
        $expect = array();
        $actual = $this->_role->list;
        $this->assertEquals($actual, $expect);
    }
    
    public function testIs()
    {
        $this->_role->load('pmjones');
        $actual = $this->_role->is('admin');
        $this->assertTrue($actual);
    }
    
    public function testIsNot()
    {
        $this->_role->load('pmjones');
        $actual = $this->_role->is('no-such-role');
        $this->assertFalse($actual);
    }
    
    public function testIsAny()
    {
        $this->_role->load('pmjones');
        $actual = $this->_role->isAny(array('no-such-role', 'root'));
        $this->assertTrue($actual);
    }
    
    public function testIsNotAny()
    {
        $this->_role->load('pmjones');
        $actual = $this->_role->isAny(array('no-such-role', 'no-other-role'));
        $this->assertFalse($actual);
    }
    
    public function testIsAll()
    {
        $this->_role->load('pmjones');
        $actual = $this->_role->isAll(array('admin', 'root'));
        $this->assertTrue($actual);
    }
    
    public function testIsNotAll()
    {
        $this->_role->load('pmjones');
        $actual = $this->_role->isAll(array('admin', 'root', 'no-such-role'));
        $this->assertFalse($actual);
    }
}
