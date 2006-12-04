<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . '/Helper.php';

class Test_Solar_View_Helper_MetaName extends Test_Solar_View_Helper {
    
    public function testMetaName()
    {
        $attribs = array('foo' => 'bar');
        $actual = $this->_view->metaName('foo', 'bar');
        $expect = '<meta name="foo" content="bar" />';
        $this->assertSame($actual, $expect);
    }
}
?>