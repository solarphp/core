<?php

abstract class Test_Solar_Log_Adapter extends Solar_Test {
    
    protected $_adapter;
    
    protected $_log;
    
    protected $_config = array();
    
    public function __construct($config = null)
    {
        parent::__construct($config);
        $this->_adapter = 'Solar_Log_Adapter_' . substr(get_class($this), 23);
    }
    
    public function _destruct()
    {
        parent::__destruct();
    }
    
    public function setup()
    {
        $this->_log = Solar::factory($this->_adapter, $this->_config);
    }
    
    public function teardown()
    {
        parent::teardown();
    }
    
    public function test__construct()
    {
        $this->assertInstance($this->_log, $this->_adapter);
    }
    
    public function testGetEvents()
    {
        $log = Solar::factory($this->_adapter);
        
        // default is always *
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
?>