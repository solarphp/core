<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . '/Helper.php';

class Test_Solar_View_Helper_Image extends Test_Solar_View_Helper {
    
    public function testImage()
    {
        $src = '/images/example.gif';
        $actual = $this->_view->image($src);
        $expect = '<img src="/public/images/example.gif" alt="example.gif" />';
        $this->assertSame($actual, $expect);
    }
}
?>