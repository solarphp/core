<?php

require_once dirname(__FILE__) . '/../HelperTestCase.php';

class Solar_View_Helper_FormPasswordTest extends Solar_View_HelperTestCase {
    
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
