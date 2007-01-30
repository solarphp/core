<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . '/Helper.php';

class Test_Solar_View_Helper_InlineScript extends Test_Solar_View_Helper {

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
?>