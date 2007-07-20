<?php

require_once dirname(__FILE__) . '/../HelperTestCase.php';

class Solar_View_Helper_BaseTest extends Solar_View_HelperTestCase {
    
    public function testBase()
    {
        $actual = $this->_view->base('http://example.com/');
        $expect = '<base href="http://example.com/" />';
        $this->assertSame($actual, $expect);
    }
}
