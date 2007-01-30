<?php

require_once realpath(dirname(__FILE__) . '/../Adapter.php');

class Test_Solar_Log_Adapter_File extends Test_Solar_Log_Adapter {
    
    protected $_Test_Solar_Log_Adapter_File = array(
        'file' => null, // set in constructor
        'format' => '%e %m',
        'events' => array('info', 'debug', 'notice'),
    );
    
    public function __construct($config = null)
    {
        $this->_Test_Solar_Log_Adapter_File['file'] = Solar::temp('test_solar_log_adapter_file.log');
        parent::__construct($config);
    }
    
    public function setup()
    {
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
?>