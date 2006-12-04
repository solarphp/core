<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . '/Helper.php';

class Test_Solar_View_Helper_FormText extends Test_Solar_View_Helper {
    
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
?>