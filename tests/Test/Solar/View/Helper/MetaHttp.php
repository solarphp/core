<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . '/Helper.php';

class Test_Solar_View_Helper_MetaHttp extends Test_Solar_View_Helper {
    
    public function testMetaHttp()
    {
        $attribs = array('foo' => 'bar');
        $actual = $this->_view->metaHttp('foo', 'bar');
        $expect = '<meta http-equiv="foo" content="bar" />';
        $this->assertSame($actual, $expect);
    }
}
?>