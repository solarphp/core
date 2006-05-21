<?php

require_once realpath(dirname(__FILE__) . '/../Adapter.php');

class Test_Solar_Log_Adapter_File extends Test_Solar_Log_Adapter {
    
    protected $_config = array(
        'file' => '/tmp/test_solar_log_adapter_file.log',
        'format' => '%e %m',
        'events' => array('info', 'debug', 'notice'),
    );
    
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
        $this->_log->save('info', 'some information');
        $this->_log->save('debug', 'a debug description');
        $this->_log->save('notice', 'note this message');
        $actual = file_get_contents($this->_config['file']);
        $expect = "info some information\ndebug a debug description\nnotice note this message\n";
        $this->assertSame($actual, $expect);
    }
    
    public function testSave_notRecognized()
    {
        $this->_log->save('info', 'recognized');
        $this->_log->save('qwert', 'not recognized');
        $actual = file_get_contents($this->_config['file']);
        $expect = "info recognized\n";
        $this->assertEquals($actual, $expect);
    }
}
?>