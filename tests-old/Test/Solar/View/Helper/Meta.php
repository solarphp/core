<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . '/Helper.php';

class Test_Solar_View_Helper_Meta extends Test_Solar_View_Helper {
    
    public function testMeta()
    {
        $attribs = array('foo' => 'bar');
        $actual = $this->_view->meta($attribs);
        $expect = '<meta foo="bar" />';
        $this->assertSame($actual, $expect);
    }
}
?>