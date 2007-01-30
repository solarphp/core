<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . '/Helper.php';

class Test_Solar_View_Helper_GetTextRaw extends Test_Solar_View_Helper {
    
    public function testGetTextRaw()
    {
        $actual = $this->_view->getTextRaw('ACTION_BROWSE');
        $expect = 'Browse';
        $this->assertSame($actual, $expect);
    }
    
    public function testGetTextRaw_otherClass()
    {
        $example = Solar::factory('Solar_Test_Example');
        $actual = $this->_view->getTextRaw('Solar_Test_Example::HELLO_WORLD');
        $expect = 'hello world';
        $this->assertSame($actual, $expect);
    }
    
    public function testGetTextRaw_resetClass()
    {
        $example = Solar::factory('Solar_Test_Example');
        $this->_view->getTextRaw('Solar_Test_Example::');
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