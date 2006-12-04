<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . '/Helper.php';

class Test_Solar_View_Helper_Title extends Test_Solar_View_Helper {
    
    public function testTitle()
    {
        $actual = $this->_view->title('foo & bar');
        $expect = '<title>foo &amp; bar</title>';
        $this->assertSame($actual, $expect);
    }
}
?>