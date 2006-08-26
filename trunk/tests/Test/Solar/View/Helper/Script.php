<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . '/Helper.php';

class Test_Solar_View_Helper_Script extends Test_Solar_View_Helper {
    
    public function testScript()
    {
        $actual = $this->_view->script('clientside.js');
        $expect = '<script src="/public/clientside.js" type="text/javascript"></script>';
        $this->assertSame($actual, $expect);
    }
}
?>