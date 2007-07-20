<?php
require_once dirname(__FILE__) . '/../PluginTestCase.php';

class Solar_Markdown_Plugin_HtmlTest extends Solar_Markdown_PluginTestCase {
    
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
        $this->assertRegExp("/$expect/", $actual);
    }
    
    public function testCleanup()
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
        $actual = $this->_plugin->cleanup($result);
        $this->assertSame($expect, $actual);
    }
}
