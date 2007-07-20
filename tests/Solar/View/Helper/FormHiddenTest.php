<?php

require_once dirname(__FILE__) . '/../HelperTestCase.php';

class Solar_View_Helper_FormHiddenTest extends Solar_View_HelperTestCase {
    
    public function testFormHidden()
    {
        $info = array(
            'name'  => 'test',
            'value' => '"something\'s quoted"',
        );
        
        $actual = $this->_view->formHidden($info);
        $expect = '<input type="hidden" name="test" value="&quot;something\'s quoted&quot;" />';
        $this->assertSame($actual, $expect);
    }
}
