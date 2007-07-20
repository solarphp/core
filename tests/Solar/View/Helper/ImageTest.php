<?php

require_once dirname(__FILE__) . '/../HelperTestCase.php';

class Solar_View_Helper_ImageTest extends Solar_View_HelperTestCase {
    
    public function testImage()
    {
        $src = '/images/example.gif';
        $actual = $this->_view->image($src);
        $expect = '<img src="/public/images/example.gif" alt="example.gif" />';
        $this->assertSame($actual, $expect);
    }
}
