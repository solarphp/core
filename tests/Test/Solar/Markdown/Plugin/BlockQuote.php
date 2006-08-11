<?php
require_once Solar::dirname(__FILE__, 1) . DIRECTORY_SEPARATOR . 'Plugin.php';

class Test_Solar_Markdown_Plugin_BlockQuote extends Test_Solar_Markdown_Plugin {
    
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
        $source[] = "";
        $source[] = "> line 1";
        $source[] = "> line 2";
        $source[] = "> ";
        $source[] = "> line 3";
        $source[] = "> line 4";
        $source[] = "";
        $source[] = "baz dib";
        $source = implode("\n", $source);
        
        $expect[] = "foo bar". "\n";
        $expect[] = $this->_tag('blockquote');
        $expect[] = "  line 1";
        $expect[] = "  line 2";
        $expect[] = "  ";
        $expect[] = "  line 3";
        $expect[] = "  line 4";
        $expect[] = "  ";
        $expect[] = "  " . $this->_tag('/blockquote') . "\n";
        $expect[] = "baz dib";
        $expect = implode("\n", $expect);
        
        $actual = $this->_plugin->parse($source);
        $this->assertRegex($actual, "@$expect@");
    }
    
    
    public function testParse_nested()
    {
        $source = array();
        $source[] = "foo bar";
        $source[] = "";
        $source[] = "> line 1";
        $source[] = "> line 2";
        $source[] = "> ";
        $source[] = "> > line 3";
        $source[] = "> > line 4";
        $source[] = "> ";
        $source[] = "> line 5";
        $source[] = "> line 6";
        $source[] = "";
        $source[] = "baz dib";
        $source = implode("\n", $source);
        
        $expect = array();
        $expect[] = "foo bar";
        $expect[] = $this->_tag('blockquote');
        $expect[] = "line 1";
        $expect[] = "line 2";
        $expect[] = $this->_tag('blockquote');
        $expect[] = "line 3";
        $expect[] = "line 4";
        $expect[] = $this->_tag('/blockquote');
        $expect[] = "line 5";
        $expect[] = "line 6";
        $expect[] = $this->_tag('/blockquote');
        $expect[] = "baz dib";
        $expect = implode("\s+", $expect);
        
        $actual = $this->_plugin->parse($source);
        $this->assertRegex($actual, "@$expect@");
    }
}
?>