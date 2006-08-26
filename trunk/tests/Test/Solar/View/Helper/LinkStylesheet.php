<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . '/Helper.php';

class Test_Solar_View_Helper_LinkStylesheet extends Test_Solar_View_Helper {
    
    public function testLinkStylesheet()
    {
        $actual = $this->_view->linkStylesheet('styles.css');
        $expect = '<link rel="stylesheet" type="text/css" media="screen" href="/public/styles.css" />';
        $this->assertSame($actual, $expect);
    }
}
?>