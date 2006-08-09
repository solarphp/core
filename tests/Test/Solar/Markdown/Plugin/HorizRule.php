<?php
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'Plugin.php';

class Test_Solar_Markdown_Plugin_HorizRule extends Test_Solar_Markdown_Plugin {
    
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
        $source[] = "-";
        $source[] = "--";
        $source[] = "---";
        $source[] = "----";
        $source[] = "- - -";
        $source[] = "baz dib";
        $source = implode("\n", $source);
        
        $expect = array();
        $expect[] = "foo bar";
        $expect[] = "-";
        $expect[] = "--";
        $expect[] = "\n" . $this->_token . "\n";
        $expect[] = "\n" . $this->_token . "\n";
        $expect[] = "\n" . $this->_token . "\n";
        $expect[] = "baz dib";
        $expect = implode("\n", $expect);
        
        $actual = $this->_rule->parse($source);
        $this->assertRegex($actual, "/$expect/");
    }
    
    public function testRender()
    {
        $source = array();
        $source[] = "foo bar";
        $source[] = "-";
        $source[] = "--";
        $source[] = "---";
        $source[] = "----";
        $source[] = "- - -";
        $source[] = "baz dib";
        $source = implode("\n", $source);
        
        $expect = array();
        $expect[] = "foo bar";
        $expect[] = "-";
        $expect[] = "--";
        $expect[] = "\n<hr />\n";
        $expect[] = "\n<hr />\n";
        $expect[] = "\n<hr />\n";
        $expect[] = "baz dib";
        $expect = implode("\n", $expect);
        
        $result = $this->_rule->parse($source);
        $actual = $this->_rule->render($result);
        
        $this->assertSame($actual, $expect);
    }
    
    public function testParse_starsUnderscores()
    {
        $source = array();
        $source[] = "foo bar";
        $source[] = "-";
        $source[] = "--";
        $source[] = "***";
        $source[] = "___";
        $source[] = "* * *";
        $source[] = "baz dib";
        $source = implode("\n", $source);
        
        $expect = array();
        $expect[] = "foo bar";
        $expect[] = "-";
        $expect[] = "--";
        $expect[] = "\n" . $this->_token . "\n";
        $expect[] = "\n" . $this->_token . "\n";
        $expect[] = "\n" . $this->_token . "\n";
        $expect[] = "baz dib";
        $expect = implode("\n", $expect);
        
        $actual = $this->_rule->parse($source);
        $this->assertRegex($actual, "/$expect/");
    }
    
    public function testRender_starsUnderscores()
    {
        $source = array();
        $source[] = "foo bar";
        $source[] = "-";
        $source[] = "--";
        $source[] = "***";
        $source[] = "___";
        $source[] = "* * *";
        $source[] = "baz dib";
        $source = implode("\n", $source);
        
        $expect = array();
        $expect[] = "foo bar";
        $expect[] = "-";
        $expect[] = "--";
        $expect[] = "\n<hr />\n";
        $expect[] = "\n<hr />\n";
        $expect[] = "\n<hr />\n";
        $expect[] = "baz dib";
        $expect = implode("\n", $expect);
        
        $result = $this->_rule->parse($source);
        $actual = $this->_rule->render($result);
        
        $this->assertSame($actual, $expect);
    }
}
?>