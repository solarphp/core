<?php

require_once realpath(dirname(__FILE__) . '/../Adapter.php');

class Test_Solar_Log_Adapter_Echo extends Test_Solar_Log_Adapter {
    
    protected $_config = array(
        'output' => 'text',
        'format' => '%e %m',
        'events' => array('info', 'debug', 'notice'),
    );
    
    public function testSave_recognized()
    {
        ob_start();
        $this->_log->save('info', 'some information');
        $this->_log->save('debug', 'a debug description');
        $this->_log->save('notice', 'note this message');
        $actual = ob_get_clean();
        $expect = "info some information\ndebug a debug description\nnotice note this message\n";
        $this->assertSame($actual, $expect);
    }
    
    public function testSave_notRecognized()
    {
        ob_start();
        $this->_log->save('qwert', 'not recognized');
        $actual = ob_get_clean();
        $expect = '';
        $this->assertEquals($actual, $expect);
    }
}
?>