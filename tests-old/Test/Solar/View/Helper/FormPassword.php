<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . '/Helper.php';

class Test_Solar_View_Helper_FormPassword extends Test_Solar_View_Helper {
    
    public function testFormPassword()
    {
        $info = array(
            'name'  => 'test',
        );
        
        $actual = $this->_view->formPassword($info);
        $expect = '<input type="password" name="test" value="" />';
        $this->assertSame($actual, $expect);
    }
}
?>