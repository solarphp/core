<?php

require_once dirname(__FILE__) . '/../HelperTestCase.php';

class Solar_View_Helper_FormResetTest extends Solar_View_HelperTestCase {
    
    public function testFormReset()
    {
        $info = array(
            'name'  => 'test',
        );
        
        $actual = $this->_view->formReset($info);
        $expect = '<input type="reset" name="test" />';
        $this->assertSame($actual, $expect);
        
        $info = array(
            'name'  => 'test',
            'value' => 'Push Me',
        );
        
        $actual = $this->_view->formReset($info);
        $expect = '<input type="reset" name="test" value="Push Me" />';
        $this->assertSame($actual, $expect);
    }
}
