<?php
require_once dirname(__FILE__) . '/../PluginTestCase.php';

class Solar_Markdown_Plugin_HorizRuleTest extends Solar_Markdown_PluginTestCase {
    
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
        $source[] = "---";
        $source[] = "baz dib";
        $source = implode("\n", $source);
        
        $expect = array();
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
        $this->assertSame($expect, $actual);
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
        $this->assertSame($expect, $actual);
    }
}
