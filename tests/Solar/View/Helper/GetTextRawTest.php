<?php

require_once dirname(__FILE__) . '/../HelperTestCase.php';

class Solar_View_Helper_GetTextRawTest extends Solar_View_HelperTestCase {
    
    public function testGetTextRaw()
    {
        $actual = $this->_view->getTextRaw('ACTION_BROWSE');
        $expect = 'Browse';
        $this->assertSame($actual, $expect);
    }
    
    public function testGetTextRaw_resetClass()
    {
        $example = Solar::factory('Solar_Test_Example');
        
        $helper = $this->_view->getHelper('getTextRaw');
        $helper->setClass('Solar_Test_Example');
        
        $actual = $this->_view->getTextRaw('HELLO_WORLD');
        $expect = 'hello world';
        $this->assertSame($actual, $expect);
    }
    
    public function testGetTextRaw_badLocaleKey()
    {
        $actual = $this->_view->getTextRaw('no such "locale" key');
        $expect = 'no such "locale" key';
        $this->assertSame($actual, $expect);
    }
}
?>