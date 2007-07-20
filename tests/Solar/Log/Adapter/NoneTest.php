<?php

require_once dirname(__FILE__) . '/../AdapterTestCase.php';

class Solar_Log_Adapter_NoneTest extends Solar_Log_AdapterTestCase {
    
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
