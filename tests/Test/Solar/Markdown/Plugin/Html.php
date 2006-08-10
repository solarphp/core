<?php
require_once Solar::dirname(__FILE__, 1) . DIRECTORY_SEPARATOR . 'Plugin.php';

class Test_Solar_Markdown_Plugin_Html extends Test_Solar_Markdown_Plugin {
    
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
        $source = <<<EOT
foo bar

<div onclick="return alert('xss');">
    <p>zim gir</p>
</div>

baz dib
EOT;
        
        $expect = "foo bar\n\s+" . $this->_token . "\n\s+baz dib";
        $actual = $this->_plugin->parse($source);
        $this->assertRegex($actual, "/$expect/");
    }
    
    public function testRender()
    {
        $source = <<<EOT
foo bar

<div onclick="return alert('xss');">
    <p>zim gir</p>
</div>

baz dib
EOT;
        $expect = <<<EOT
foo bar



<div onclick="return alert('xss');">
    <p>zim gir</p>
</div>



baz dib
EOT;
        $result = $this->_plugin->parse($source);
        $actual = $this->_plugin->render($result);
        $this->assertSame($actual, $expect);
    }
}
?>