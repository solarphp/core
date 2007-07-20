<?php

require_once dirname(__FILE__) . '/../AdapterTestCase.php';

class Solar_Log_Adapter_EchoTest extends Solar_Log_AdapterTestCase
{
    
    protected $_config = array(
        'output' => 'text',
        'format' => '%e %m',
        'events' => array('info', 'debug', 'notice'),
    );
    
    public function testSave_recognized()
    {
        ob_start();
        $class = get_class($this);
        $this->_log->save($class, 'info', 'some information');
        $this->_log->save($class, 'debug', 'a debug description');
        $this->_log->save($class, 'notice', 'note this message');
        $actual = ob_get_clean();
        
        $expect = "info some information" . PHP_EOL
                . "debug a debug description" . PHP_EOL
                . "notice note this message" . PHP_EOL;
                
        $this->assertSame($actual, $expect);
    }
    
    public function testSave_notRecognized()
    {
        ob_start();
        $class = get_class($this);
        $this->_log->save($class, 'qwert', 'not recognized');
        $actual = ob_get_clean();
        $expect = '';
        $this->assertEquals($actual, $expect);
    }
}
