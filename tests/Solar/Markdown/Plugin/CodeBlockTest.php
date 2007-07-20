<?php
require_once dirname(__FILE__) . '/../PluginTestCase.php';

class Solar_Markdown_Plugin_CodeBlockTest extends Solar_Markdown_PluginTestCase {
    
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
        $source[] = "\tcode line 1";
        $source[] = "\tcode line 2";
        $source[] = "\tcode line 3";
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
        $source[] = "\tcode line 1";
        $source[] = "\tcode line 2";
        $source[] = "\tcode line 3";
        $source[] = "";
        $source[] = "baz dib";
        $source = implode("\n", $source);
        
        $expect[] = "foo bar";
        $expect[] = "";
        $expect[] = "<pre><code>code line 1";
        $expect[] = "code line 2";
        $expect[] = "code line 3";
        $expect[] = "</code></pre>";
        $expect[] = "";
        $expect[] = "baz dib";
        $expect = implode("\n", $expect);
        
        $actual = $this->_markdown->transform($source);
        $this->assertSame($expect, $actual);
    }
}
