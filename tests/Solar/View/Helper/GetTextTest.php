<?php

require_once dirname(__FILE__) . '/../HelperTestCase.php';

class Solar_View_Helper_GetTextTest extends Solar_View_HelperTestCase {
    
    public function testGetText()
    {
        $actual = $this->_view->getText('ACTION_BROWSE');
        $expect = 'Browse';
        $this->assertSame($actual, $expect);
    }
    
    public function testGetText_otherClass()
    {
        $example = Solar::factory('Solar_Test_Example');
        $actual = $this->_view->getText('Solar_Test_Example::HELLO_WORLD');
        $expect = 'hello world';
        $this->assertSame($actual, $expect);
    }
    
    public function testGetText_resetClass()
    {
        $example = Solar::factory('Solar_Test_Example');
        $this->_view->getText('Solar_Test_Example::');
        $actual = $this->_view->getText('HELLO_WORLD');
        $expect = 'hello world';
        $this->assertSame($actual, $expect);
    }
    
    public function testGetText_badLocaleKey()
    {
        $actual = $this->_view->getText('no such "locale" key');
        $expect = 'no such &quot;locale&quot; key';
        $this->assertSame($actual, $expect);
    }
}
?>