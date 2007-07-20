<?php

require_once dirname(__FILE__) . '/../HelperTestCase.php';

class Solar_View_Helper_MetaTest extends Solar_View_HelperTestCase {
    
    public function testMeta()
    {
        $attribs = array('foo' => 'bar');
        $actual = $this->_view->meta($attribs);
        $expect = '<meta foo="bar" />';
        $this->assertSame($actual, $expect);
    }
}
