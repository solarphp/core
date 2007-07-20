<?php

require_once dirname(__FILE__) . '/../HelperTestCase.php';

class Solar_View_Helper_FormTextTest extends Solar_View_HelperTestCase {
    
    public function testFormText()
    {
        $info = array(
            'name'  => 'test',
            'value' => '"quoted\'s"',
        );
        
        $actual = $this->_view->formText($info);
        $expect = '<input type="text" name="test" value="&quot;quoted\'s&quot;" />';
        $this->assertSame($actual, $expect);
    }
}
