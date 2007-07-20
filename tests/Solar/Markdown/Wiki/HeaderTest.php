<?php
require_once dirname(__FILE__) . '/../PluginTestCase.php';

class Solar_Markdown_Wiki_HeaderTest extends Solar_Markdown_PluginTestCase {
    
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
        $source[] = "Top-Level Header";
        $source[] = "================";
        $source[] = "baz dib";
        $source = implode("\n", $source);
        
        $expect[] = "foo bar";
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
        $source[] = "=====";
        $source[] = "Title";
        $source[] = "=====";
        $source[] = "baz dib";
        $source = implode("\n", $source);
        
        $expect[] = "foo bar";
        $expect[] = "<h1>Title</h1>\n";
        $expect[] = "baz dib";
        $expect = implode("\n", $expect);
        
        $actual = $this->_markdown->transform($source);
        $this->assertSame($expect, $actual);
    }
    
    public function testRender_superSection()
    {
        $source = array();
        $source[] = "foo bar";
        $source[] = "-------------";
        $source[] = "Super-Section";
        $source[] = "-------------";
        $source[] = "baz dib";
        $source = implode("\n", $source);
        
        $expect[] = "foo bar";
        $expect[] = "<h2>Super-Section</h2>\n";
        $expect[] = "baz dib";
        $expect = implode("\n", $expect);
        
        $actual = $this->_markdown->transform($source);
        $this->assertSame($expect, $actual);
    }
    
    public function testRender_section()
    {
        $source = array();
        $source[] = "foo bar";
        $source[] = "Section";
        $source[] = "=======";
        $source[] = "baz dib";
        $source = implode("\n", $source);
        
        $expect[] = "foo bar";
        $expect[] = "<h3>Section</h3>\n";
        $expect[] = "baz dib";
        $expect = implode("\n", $expect);
        
        $actual = $this->_markdown->transform($source);
        $this->assertSame($expect, $actual);
    }
    
    public function testRender_subSection()
    {
        $source = array();
        $source[] = "foo bar";
        $source[] = "Sub-Section";
        $source[] = "-----------";
        $source[] = "baz dib";
        $source = implode("\n", $source);
        
        $expect[] = "foo bar";
        $expect[] = "<h4>Sub-Section</h4>\n";
        $expect[] = "baz dib";
        $expect = implode("\n", $expect);
        
        $actual = $this->_markdown->transform($source);
        $this->assertSame($expect, $actual);
    }
    
    public function testRender_Atx()
    {
        $source = array();
        $source[] = "foo bar";
        $source[] = "# Title";
        $source[] = "## Super-Section";
        $source[] = "### Section";
        $source[] = "#### Sub-Section";
        $source[] = "baz dib";
        $source = implode("\n", $source);
        
        $expect = array();
        $expect[] = "foo bar";
        $expect[] = "<h1>Title</h1>\n";
        $expect[] = "<h2>Super-Section</h2>\n";
        $expect[] = "<h3>Section</h3>\n";
        $expect[] = "<h4>Sub-Section</h4>\n";
        $expect[] = "baz dib";
        $expect = implode("\n", $expect);
        
        $actual = $this->_markdown->transform($source);
        $this->assertSame($expect, $actual);
    }
}
