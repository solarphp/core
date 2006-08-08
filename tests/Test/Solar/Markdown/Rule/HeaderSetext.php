<?php
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'Rule.php';

class Test_Solar_Markdown_Rule_HeaderSetext extends Test_Solar_Markdown_Rule {
    
    // should show no changes
    public function testFilter()
    {
        $source = "foo bar baz";
        $expect = $source;
        $actual = $this->_rule->filter($source);
        $this->assertSame($actual, $expect);
    }
    
    public function testParse()
    {
        $source = array();
        $source[] = "foo bar";
        $source[] = "Top-Level Header";
        $source[] = "================";
        $source[] = "baz dib";
        $source = implode("\n", $source);
        
        $expect[] = "foo bar";
        $expect[] = $this->_token . "Top-Level Header" . $this->_token . "\n";
        $expect[] = "baz dib";
        $expect = implode("\n", $expect);
        
        $actual = $this->_rule->parse($source);
        $this->assertRegex($actual, "/$expect/");
    }
    
    public function testRender()
    {
        $source = array();
        $source[] = "foo bar";
        $source[] = "Top-Level Header";
        $source[] = "================";
        $source[] = "baz dib";
        $source = implode("\n", $source);
        
        $expect[] = "foo bar";
        $expect[] = "<h1>Top-Level Header</h1>\n";
        $expect[] = "baz dib";
        $expect = implode("\n", $expect);
        
        $result = $this->_rule->parse($source);
        $actual = $this->_rule->render($result);
        
        $this->assertSame($actual, $expect);
    }
    
    public function testParse_sub()
    {
        $source = array();
        $source[] = "foo bar";
        $source[] = "Sub-Level Header";
        $source[] = "----------------";
        $source[] = "baz dib";
        $source = implode("\n", $source);
        
        $expect[] = "foo bar";
        $expect[] = $this->_token . "Sub-Level Header" . $this->_token . "\n";
        $expect[] = "baz dib";
        $expect = implode("\n", $expect);
        
        $actual = $this->_rule->parse($source);
        $this->assertRegex($actual, "/$expect/");
    }
    
    public function testRender_sub()
    {
        $source = array();
        $source[] = "foo bar";
        $source[] = "Sub-Level Header";
        $source[] = "----------------";
        $source[] = "baz dib";
        $source = implode("\n", $source);
        
        $expect[] = "foo bar";
        $expect[] = "<h2>Sub-Level Header</h2>\n";
        $expect[] = "baz dib";
        $expect = implode("\n", $expect);
        
        $result = $this->_rule->parse($source);
        $actual = $this->_rule->render($result);
        
        $this->assertSame($actual, $expect);
    }
}
?>