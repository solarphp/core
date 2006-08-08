<?php
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'Rule.php';

class Test_Solar_Markdown_Rule_HeaderSetext extends Test_Solar_Markdown_Rule {
    
    public function testFilter()
    {
        $text = "foo bar baz";
        $expect = $text;
        $actual = $this->_rule->filter($text);
        $this->assertSame($actual, $expect);
    }
    
    public function testParse()
    {
        $text = array();
        $text[] = "some lines";
        $text[] = "Top-Level Header";
        $text[] = "================";
        $this->todo();
    }
    
    public function testRender()
    {
        $this->todo();
    }
}
?>