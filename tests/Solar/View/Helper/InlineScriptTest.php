<?php

require_once dirname(__FILE__) . '/../HelperTestCase.php';

class Solar_View_Helper_ScriptInlineTest extends Solar_View_HelperTestCase {
    
    public function testScript()
    {
        $actual = $this->_view->inlineScript('alert(\'Hello world!\');');
        $expect = '<script type="text/javascript">
//<![CDATA[
alert(\'Hello world!\');
//]]>
</script>';
        $this->assertSame(trim($actual), trim($expect));
    }
}
