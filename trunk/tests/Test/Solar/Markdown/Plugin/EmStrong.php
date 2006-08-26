<?php
require_once Solar::dirname(__FILE__, 1) . DIRECTORY_SEPARATOR . 'Plugin.php';

class Test_Solar_Markdown_Plugin_EmStrong extends Test_Solar_Markdown_Plugin {
    
    public function testIsBlock()
    {
        $this->assertFalse($this->_plugin->isBlock());
    }
    
    public function testIsSpan()
    {
        $this->assertTrue($this->_plugin->isSpan());
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
        $source[] = "*em*";
        $source[] = "**strong**";
        $source[] = "***strong-em***";
        $source[] = "plain *em* plain **strong** plain ***strong-em*** plain";
        $source = implode(" ", $source);
        
        $expect[] = "<em>em</em>";
        $expect[] = "<strong>strong</strong>";
        $expect[] = "<strong><em>strong-em</em></strong>";
        $expect[] = "plain <em>em</em> plain <strong>strong</strong> plain <strong><em>strong-em</em></strong> plain";
        $expect = implode(" ", $expect);
        
        $actual = $this->_plugin->parse($source);
        $this->assertSame($actual, $expect);
    }
    
    public function testParse_underscores()
    {
        $source = array();
        $source[] = "_em_";
        $source[] = "__strong__";
        $source[] = "___strong-em___";
        $source[] = "plain _em_ plain __strong__ plain ___strong-em___ plain";
        $source = implode(" ", $source);
        
        $expect[] = "<em>em</em>";
        $expect[] = "<strong>strong</strong>";
        $expect[] = "<strong><em>strong-em</em></strong>";
        $expect[] = "plain <em>em</em> plain <strong>strong</strong> plain <strong><em>strong-em</em></strong> plain";
        $expect = implode(" ", $expect);
        
        $actual = $this->_plugin->parse($source);
        $this->assertSame($actual, $expect);
    }
    
    public function testParse_mixed()
    {
        $source = array();
        $source[] = "_em_";
        $source[] = "**strong**";
        $source[] = "**_strong-em_**";
        $source[] = "plain _em_ plain __strong__ plain __*strong-em*__ plain";
        $source = implode(" ", $source);
        
        $expect[] = "<em>em</em>";
        $expect[] = "<strong>strong</strong>";
        $expect[] = "<strong><em>strong-em</em></strong>";
        $expect[] = "plain <em>em</em> plain <strong>strong</strong> plain <strong><em>strong-em</em></strong> plain";
        $expect = implode(" ", $expect);
        
        $actual = $this->_plugin->parse($source);
        $this->assertSame($actual, $expect);
    }
}
?>