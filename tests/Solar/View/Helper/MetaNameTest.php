<?php

require_once dirname(__FILE__) . '/../HelperTestCase.php';

class Solar_View_Helper_MetaNameTest extends Solar_View_HelperTestCase {
    
    public function testMetaName()
    {
        $attribs = array('foo' => 'bar');
        $actual = $this->_view->metaName('foo', 'bar');
        $expect = '<meta name="foo" content="bar" />';
        $this->assertSame($actual, $expect);
    }
}
