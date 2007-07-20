<?php

require_once dirname(__FILE__) . '/../HelperTestCase.php';

class Solar_View_Helper_AttribsTest extends Solar_View_HelperTestCase {
    
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
