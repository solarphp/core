<?php

require_once dirname(__FILE__) . '/../HelperTestCase.php';

class Solar_View_Helper_FormButtonTest extends Solar_View_HelperTestCase {
    
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
