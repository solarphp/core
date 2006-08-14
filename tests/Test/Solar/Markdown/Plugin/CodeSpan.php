<?php
require_once Solar::dirname(__FILE__, 1) . DIRECTORY_SEPARATOR . 'Plugin.php';

class Test_Solar_Markdown_Plugin_CodeSpan extends Test_Solar_Markdown_Plugin {
    
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
        $source[] = "`code`";
        $source[] = "``code``";
        $source[] = "`` `code` ``";
        $source[] = "plain `code` plain `code`";
        $source = implode("\n", $source);
        
        $expect[] = $this->_token;
        $expect[] = $this->_token;
        $expect[] = $this->_token;
        $expect[] = "plain $this->_token plain $this->_token";
        $expect = implode("\n", $expect);
        
        $actual = $this->_plugin->parse($source);
        $this->assertRegex($actual, "@$expect@");
    }
}
?>