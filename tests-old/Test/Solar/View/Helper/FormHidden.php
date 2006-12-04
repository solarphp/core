<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . '/Helper.php';

class Test_Solar_View_Helper_FormHidden extends Test_Solar_View_Helper {
    
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
?>