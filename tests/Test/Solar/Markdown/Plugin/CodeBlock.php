<?php
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'Plugin.php';

class Test_Solar_Markdown_Plugin_CodeBlock extends Test_Solar_Markdown_Plugin {
    
    // should show no changes
    public function testFilter()
    {
        $source = "foo bar baz";
        $expect = $source;
        $actual = $this->_plugin->filter($source);
        $this->assertSame($actual, $expect);
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
        
        $expect[] = "foo bar". "\n";
        $expect[] = $this->_token . "\n";
        $expect[] = "baz dib";
        $expect = implode("\n", $expect);
        
        $actual = $this->_plugin->parse($source);
        $this->assertRegex($actual, "/$expect/");
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
        
        $expect[] = "foo bar". "\n";
        $expect[] = "<pre><code>code line 1";
        $expect[] = "code line 2";
        $expect[] = "code line 3</code></pre>\n";
        $expect[] = "baz dib";
        $expect = implode("\n", $expect);
        
        $result = $this->_plugin->parse($source);
        $actual = $this->_plugin->render($result);
        $this->assertRegex($actual, "@$expect@");
    }
}
?>