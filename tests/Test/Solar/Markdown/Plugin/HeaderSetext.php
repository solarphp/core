<?php
require_once Solar::dirname(__FILE__, 1) . DIRECTORY_SEPARATOR . 'Plugin.php';

class Test_Solar_Markdown_Plugin_HeaderSetext extends Test_Solar_Markdown_Plugin {
    
    public function testIsBlock()
    {
        $this->assertTrue($this->_plugin->isBlock());
    }
    
    public function testIsSpan()
    {
        $this->assertFalse($this->_plugin->isSpan());
    }
    
    // should show no changes
    public function testPrepare()
    {
        $source = "foo bar baz";
        $expect = $source;
        $actual = $this->_plugin->prepare($source);
        $this->assertSame($actual, $expect);
    }
    
    // should show no changes
    public function testCleanup()
    {
        $source = "foo bar baz";
        $expect = $source;
        $actual = $this->_plugin->cleanup($source);
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
        
        $actual = $this->_plugin->parse($source);
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
        
        $result = $this->_plugin->parse($source);
        $actual = $this->_plugin->render($result);
        
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
        
        $actual = $this->_plugin->parse($source);
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
        
        $result = $this->_plugin->parse($source);
        $actual = $this->_plugin->render($result);
        
        $this->assertSame($actual, $expect);
    }
}
?>