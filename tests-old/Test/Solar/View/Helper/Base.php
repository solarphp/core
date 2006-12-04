<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . '/Helper.php';

class Test_Solar_View_Helper_Base extends Test_Solar_View_Helper {
    
    public function testBase()
    {
        $actual = $this->_view->base('http://example.com/');
        $expect = '<base href="http://example.com/" />';
        $this->assertSame($actual, $expect);
    }
}
?>