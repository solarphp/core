<?php

require_once dirname(__FILE__) . '/../HelperTestCase.php';

class Solar_View_Helper_LinkStylesheetTest extends Solar_View_HelperTestCase {
    
    public function testLinkStylesheet()
    {
        $actual = $this->_view->linkStylesheet('styles.css');
        $expect = '<link rel="stylesheet" type="text/css" media="screen" href="/public/styles.css" />';
        $this->assertSame($actual, $expect);
    }
}
