<?php

require_once dirname(__FILE__) . '/../HelperTestCase.php';

class Solar_View_Helper_FormSubmitTest extends Solar_View_HelperTestCase {
    
    public function testFormSubmit()
    {
        $info = array(
            'name'  => 'test',
        );
        
        $actual = $this->_view->formSubmit($info);
        $expect = '<input type="submit" name="test" />';
        $this->assertSame($actual, $expect);
        
        $info = array(
            'name'  => 'test',
            'value' => 'Push Me',
        );
        
        $actual = $this->_view->formSubmit($info);
        $expect = '<input type="submit" name="test" value="Push Me" />';
        $this->assertSame($actual, $expect);
    }
}
