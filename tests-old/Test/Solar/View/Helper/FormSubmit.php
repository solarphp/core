<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . '/Helper.php';

class Test_Solar_View_Helper_FormSubmit extends Test_Solar_View_Helper {
    
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
?>