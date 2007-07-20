<?php

require_once dirname(__FILE__) . '/../HelperTestCase.php';

class Solar_View_Helper_MetaHttpTest extends Solar_View_HelperTestCase {
    
    public function testMetaHttp()
    {
        $attribs = array('foo' => 'bar');
        $actual = $this->_view->metaHttp('foo', 'bar');
        $expect = '<meta http-equiv="foo" content="bar" />';
        $this->assertSame($actual, $expect);
    }
}
