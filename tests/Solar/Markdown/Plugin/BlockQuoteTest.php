<?php
require_once dirname(__FILE__) . '/../PluginTestCase.php';

class Solar_Markdown_Plugin_BlockQuoteTest extends Solar_Markdown_PluginTestCase
{
    
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
        $this->assertSame($expect, $actual);
    }
    
    // should show no changes
    public function testCleanup()
    {
        $source = "foo bar baz";
        $expect = $source;
        $actual = $this->_plugin->cleanup($source);
        $this->assertSame($expect, $actual);
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
        
        $expect[] = "foo bar\n";
        $expect[] = $this->_token . "\n";
        $expect[] = "baz dib";
        $expect = implode("\n", $expect);
        
        $actual = $this->_plugin->parse($source);
        $this->assertRegExp("@$expect@", $actual);
    }
    
    public function testRender()
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
        
        $expect[] = "foo bar" . "\n";
        $expect[] = $this->_tag('blockquote');
        $expect[] = "  line 1";
        $expect[] = "  line 2";
        $expect[] = "  ";
        $expect[] = "  line 3";
        $expect[] = "  line 4";
        $expect[] = $this->_tag('/blockquote');
        $expect[] = "";
        $expect[] = "baz dib";
        $expect = implode("\n", $expect);
        
        $actual = $this->_markdown->transform($source);
        $this->assertRegExp("@$expect@", $actual);
    }
    
    
    public function testRender_nested()
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
        
        $actual = $this->_markdown->transform($source);
        $this->assertRegExp("@$expect@", $actual);
    }
}
