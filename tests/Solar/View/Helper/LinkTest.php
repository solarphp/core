<?php

require_once dirname(__FILE__) . '/../HelperTestCase.php';

class Solar_View_Helper_LinkTest extends Solar_View_HelperTestCase {
    
    public function testLink()
    {
        $attribs = array('foo' => 'bar');
        $actual = $this->_view->link($attribs);
        $expect = '<link foo="bar" />';
        $this->assertSame($actual, $expect);
    }
}
