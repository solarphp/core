<?php

require_once dirname(__FILE__) . '/../HelperTestCase.php';

class Solar_View_Helper_GetTextTest extends Solar_View_HelperTestCase {
    
    public function testGetText()
    {
        Solar::start(false);
        $actual = $this->_view->getText('ACTION_BROWSE');
        $expect = 'Browse';
        $this->assertSame($actual, $expect);
    }
    
    public function testGetText_setClass()
    {
        Solar::start(false);
        $example = Solar::factory('Solar_Example');
        
        $helper = $this->_view->getHelper('getText');
        $helper->setClass('Solar_Example');
        
        $actual = $this->_view->getTextRaw('HELLO_WORLD');
        $expect = 'hello world';
        $this->assertSame($actual, $expect);
    }
    
    public function testGetText_badLocaleKey()
    {
        Solar::start(false);
        $actual = $this->_view->getText('no such "locale" key');
        $expect = 'no such &quot;locale&quot; key';
        $this->assertSame($actual, $expect);
    }
}
