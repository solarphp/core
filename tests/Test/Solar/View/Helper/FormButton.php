<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . '/Helper.php';

class Test_Solar_View_Helper_FormButton extends Test_Solar_View_Helper {
    
    public function testFormButton()
    {
        $info = array(
            'name'  => 'test',
            'value' => 'Push Me',
        );
        
        $actual = $this->_view->formButton($info);
        $expect = '<input type="button" name="test" value="Push Me" />';
        $this->assertSame($actual, $expect);
    }
}
?>