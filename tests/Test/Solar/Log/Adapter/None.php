<?php

require_once realpath(dirname(__FILE__) . '/../Adapter.php');

class Test_Solar_Log_Adapter_None extends Test_Solar_Log_Adapter {
    
    protected $_config = array(
        'format' => '%e %m',
        'events' => array('info', 'debug', 'notice'),
    );
    
    public function testSave_recognized()
    {
        $class = get_class($this);
        
        $actual = $this->_log->save($class, 'info', 'some information');
        $this->assertTrue($actual);
        
        $actual = $this->_log->save($class, 'debug', 'a debug description');
        $this->assertTrue($actual);
        
        $actual = $this->_log->save($class, 'notice', 'note this message');
        $this->assertTrue($actual);
    }
    
    public function testSave_notRecognized()
    {
        $class = get_class($this);
        $actual = $this->_log->save($class, 'qwert', 'not recognized');
        $this->assertFalse($actual);
    }
}
?>