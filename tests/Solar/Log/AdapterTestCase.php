<?php
require_once dirname(__FILE__) . '/../../SolarUnitTest.config.php';

abstract class Solar_Log_AdapterTestCase extends PHPUnit_Framework_TestCase {
    
    protected $_adapter;
    
    protected $_log;
    
    public function setup()
    {
        $this->_adapter = 'Solar_Log_Adapter_' . substr(get_class($this), 18, -4);
        $this->_log = Solar::factory($this->_adapter, $this->_config);
    }
    
    public function teardown()
    {
        parent::teardown();
    }
    
    public function test__construct()
    {
        $this->assertType($this->_adapter, $this->_log);
    }
    
    public function testGetEvents()
    {
        $log = Solar::factory($this->_adapter);
        
        // default is always "*"
        $actual = $log->getEvents();
        $expect = array('*');
        $this->assertSame($actual, $expect);
        
        // set some new ones
        $expect = array('debug', 'info', 'notice');
        $log->setEvents($expect);
        $actual = $log->getEvents();
        $this->assertSame($actual, $expect);
    }
    
    public function testSetEvents_array()
    {
        $log = Solar::factory($this->_adapter);
        $expect = array('debug', 'info', 'notice');
        $log->setEvents($expect);
        $actual = $log->getEvents();
        $this->assertSame($actual, $expect);
    }
    
    public function testSetEvents_string()
    {
        $log = Solar::factory($this->_adapter);
        $expect = array('debug', 'info', 'notice');
        $string = implode(', ', $expect);
        $log->setEvents($string);
        $actual = $log->getEvents();
        $this->assertSame($actual, $expect);
    }
    
    public function testSave_recognized()
    {
        $this->todo('incomplete');
    }
    
    public function testSave_notRecognized()
    {
        $this->todo('incomplete');
    }
}
