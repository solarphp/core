<?php

require_once dirname(__FILE__) . '/../HelperTestCase.php';

class Solar_View_Helper_ScriptTest extends Solar_View_HelperTestCase {
    
    public function testScript()
    {
        $actual = $this->_view->script('clientside.js');
        $expect = '<script src="/public/clientside.js" type="text/javascript"></script>';
        $this->assertSame($actual, $expect);
    }
}
