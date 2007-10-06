<?php
require_once dirname(__FILE__) . '/../PluginTestCase.php';

class Solar_Markdown_Plugin_HeaderTest extends Solar_Markdown_PluginTestCase {
    
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
        $source[] = "Top-Level Header";
        $source[] = "================";
        $source[] = "baz dib";
        $source = implode("\n", $source);
        
        $expect[] = "foo bar";
        $expect[] = "<h1>Top-Level Header</h1>\n";
        $expect[] = "baz dib";
        $expect = implode("\n", $expect);
        
        $actual = $this->_markdown->transform($source);
        $this->assertSame($expect, $actual);
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
        
        $actual = $this->_markdown->transform($source);
        $this->assertSame($expect, $actual);
    }
    
    public function testRender_atx()
    {
        $source = array();
        $source[] = "foo bar";
        $source[] = "# 1";
        $source[] = "## 2";
        $source[] = "### 3";
        $source[] = "#### 4";
        $source[] = "##### 5";
        $source[] = "###### 6";
        $source[] = "####### 7";
        $source[] = "baz dib";
        $source = implode("\n", $source);
        
        $expect[] = "foo bar";
        $expect[] = "<h1>1</h1>\n";
        $expect[] = "<h2>2</h2>\n";
        $expect[] = "<h3>3</h3>\n";
        $expect[] = "<h4>4</h4>\n";
        $expect[] = "<h5>5</h5>\n";
        $expect[] = "<h6>6</h6>\n";
        $expect[] = "<h6># 7</h6>\n";
        $expect[] = "baz dib";
        $expect = implode("\n", $expect);
        
        $actual = $this->_markdown->transform($source);
        $this->assertSame($expect, $actual);
    }
    
    public function testRender_atxTrailingHashes()
    {
        $source = array();
        $source[] = "foo bar";
        $source[] = "# 1 #";
        $source[] = "# 2 ##";
        $source[] = "# 5 ###";
        $source[] = "baz dib";
        $source = implode("\n", $source);
        
        $expect[] = "foo bar";
        $expect[] = "<h1>1</h1>\n";
        $expect[] = "<h1>2</h1>\n";
        $expect[] = "<h1>5</h1>\n";
        $expect[] = "baz dib";
        $expect = implode("\n", $expect);
        
        $actual = $this->_markdown->transform($source);
        $this->assertSame($expect, $actual);
    }
}
