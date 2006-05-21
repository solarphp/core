<?php

require_once realpath(dirname(__FILE__) . '/../Adapter.php');

class Test_Solar_Log_Adapter_None extends Test_Solar_Log_Adapter {
    
    protected $_config = array(
        'format' => '%e %m',
        'events' => array('info', 'debug', 'notice'),
    );
    
    public function testSave_recognized()
    {
        $actual = $this->_log->save('info', 'some information');
        $this->assertTrue($actual);
        
        $actual = $this->_log->save('debug', 'a debug description');
        $this->assertTrue($actual);
        
        $actual = $this->_log->save('notice', 'note this message');
        $this->assertTrue($actual);
    }
    
    public function testSave_notRecognized()
    {
        $actual = $this->_log->save('qwert', 'not recognized');
        $this->assertFalse($actual);
    }
}
?>