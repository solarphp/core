<?php

require_once dirname(__FILE__) . '/../AdapterTestCase.php';

class Solar_Log_Adapter_FileTest extends Solar_Log_AdapterTestCase 
{
    
    protected $_config = array(
        'file' => null, // set in constructor
        'format' => '%e %m',
        'events' => array('info', 'debug', 'notice'),
    );
    
    public function setup()
    {
        $this->_config['file'] = Solar_File::tmp('test_solar_log_adapter_file.log');
        parent::setup();
        @unlink($this->_config['file']);
    }
    
    public function teardown()
    {
        @unlink($this->_config['file']);
        parent::teardown();
    }
    
    public function testSave_recognized()
    {
        $class = get_class($this);
        $this->_log->save($class, 'info', 'some information');
        $this->_log->save($class, 'debug', 'a debug description');
        $this->_log->save($class, 'notice', 'note this message');
        $actual = file_get_contents($this->_config['file']);
        $expect = "info some information\ndebug a debug description\nnotice note this message\n";
        $this->assertSame($actual, $expect);
    }
    
    public function testSave_notRecognized()
    {
        $class = get_class($this);
        $this->_log->save($class, 'info', 'recognized');
        $this->_log->save($class, 'qwert', 'not recognized');
        $actual = file_get_contents($this->_config['file']);
        $expect = "info recognized\n";
        $this->assertEquals($actual, $expect);
    }
}
