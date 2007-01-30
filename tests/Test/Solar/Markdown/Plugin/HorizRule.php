<?php
require_once Solar::dirname(__FILE__, 1) . DIRECTORY_SEPARATOR . 'Plugin.php';

class Test_Solar_Markdown_Plugin_HorizRule extends Test_Solar_Markdown_Plugin {
    
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
        $source[] = "---";
        $source[] = "baz dib";
        $source = implode("\n", $source);
        
        $expect = array();
        $expect[] = "foo bar\n";
        $expect[] = $this->_token . "\n";
        $expect[] = "baz dib";
        $expect = implode("\n", $expect);
        
        $actual = $this->_plugin->parse($source);
        $this->assertRegex($actual, "@$expect@");
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
        
        $actual = $this->_markdown->transform($source);
        $this->assertSame($actual, $expect);
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
        
        $actual = $this->_markdown->transform($source);
        $this->assertSame($actual, $expect);
    }
}
?>