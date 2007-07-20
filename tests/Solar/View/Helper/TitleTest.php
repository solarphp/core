<?php

require_once dirname(__FILE__) . '/../HelperTestCase.php';

class Solar_View_Helper_TitleTest extends Solar_View_HelperTestCase {
    
    public function testTitle()
    {
        $actual = $this->_view->title('foo & bar');
        $expect = '<title>foo &amp; bar</title>';
        $this->assertSame($actual, $expect);
    }
}
