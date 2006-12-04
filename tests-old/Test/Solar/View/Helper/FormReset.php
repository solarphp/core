<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . '/Helper.php';

class Test_Solar_View_Helper_FormReset extends Test_Solar_View_Helper {
    
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
?>