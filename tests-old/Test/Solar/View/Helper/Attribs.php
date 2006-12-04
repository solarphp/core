<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . '/Helper.php';

class Test_Solar_View_Helper_Attribs extends Test_Solar_View_Helper {
    
    public function testAttribs()
    {
        $attr = array(
            'foo' => 'bar',
            'baz' => '"dib"',
            'zim' => array('irk', 'gir'),
        );
        
        $actual = $this->_view->attribs($attr);
        $expect = ' foo="bar" baz="&quot;dib&quot;" zim="irk gir"';
        $this->assertSame($actual, $expect);
    }
}
?>