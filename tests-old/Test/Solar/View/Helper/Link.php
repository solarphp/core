<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . '/Helper.php';

class Test_Solar_View_Helper_Link extends Test_Solar_View_Helper {
    
    public function testLink()
    {
        $attribs = array('foo' => 'bar');
        $actual = $this->_view->link($attribs);
        $expect = '<link foo="bar" />';
        $this->assertSame($actual, $expect);
    }
}
?>