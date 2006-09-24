<?php

require_once realpath(dirname(__FILE__) . '/../Adapter.php');

class Test_Solar_Log_Adapter_Multi extends Test_Solar_Log_Adapter {
    
    protected $_Test_Solar_Log_Adapter_Multi = array(
        'adapters' => array(
            array(
                'adapter' => 'Solar_Log_Adapter_File',
                'config'  => array(
                    'events'  => 'debug',
                    'file'    => '/tmp/test_solar_log_adapter_multi.debug.log',
                    'format' => '%e %m',
                ),
            ),
            array(
                'adapter' => 'Solar_Log_Adapter_File',
                'config'  => array(
                    'events'  => 'info, notice',
                    'file'    => '/tmp/test_solar_log_adapter_multi.other.log',
                    'format' => '%e %m',
                ),
            ),
        ),
    );
    
    public function setup()
    {
        parent::setup();
        @unlink($this->_config['adapters'][0]['config']['file']);
        @unlink($this->_config['adapters'][1]['config']['file']);
    }
    
    public function teardown()
    {
        @unlink($this->_config['adapters'][0]['config']['file']);
        @unlink($this->_config['adapters'][1]['config']['file']);
        parent::teardown();
    }
    
    public function testSave_recognized()
    {
        $class = get_class($this);
        $this->_log->save($class, 'info', 'some information');
        $this->_log->save($class, 'debug', 'a debug description');
        $this->_log->save($class, 'notice', 'note this message');
        
        // the debug log
        $actual = file_get_contents($this->_config['adapters'][0]['config']['file']);
        $expect = "debug a debug description\n";
        $this->assertSame($actual, $expect);
        
        // the other log
        $actual = file_get_contents($this->_config['adapters'][1]['config']['file']);
        $expect = "info some information\nnotice note this message\n";
        $this->assertSame($actual, $expect);
    }
    
    public function testSave_notRecognized()
    {
        $class = get_class($this);
        $this->_log->save($class, 'debug', 'recognized');
        $this->_log->save($class, 'info', 'recognized');
        $this->_log->save($class, 'qwert', 'not recognized');
        
        // the debug log
        $actual = file_get_contents($this->_config['adapters'][0]['config']['file']);
        $expect = "debug recognized\n";
        $this->assertSame($actual, $expect);
        
        // the other log
        $actual = file_get_contents($this->_config['adapters'][1]['config']['file']);
        $expect = "info recognized\n";
        $this->assertSame($actual, $expect);
    }
}
?>