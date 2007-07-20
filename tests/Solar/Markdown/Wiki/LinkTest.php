<?php
require_once dirname(__FILE__) . '/../PluginTestCase.php';

class Solar_Markdown_Wiki_LinkTest extends Solar_Markdown_PluginTestCase {
    
    protected $_encode = "\x1BSolar_Markdown_Wiki_Link:.*?\x1B";
    
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
        $this->assertSame($expect, $actual);
    }
    
    // should show no changes
    public function testCleanup()
    {
        $source = "foo bar baz";
        $expect = $source;
        $actual = $this->_plugin->prepare($source);
        $this->assertSame($expect, $actual);
    }
    
    public function testParse()
    {
        $source = 'foo [[page name]] bar';
        $actual = $this->_plugin->parse($source);
        $expect = "foo {$this->_encode} bar";
        $this->assertRegExp("@$expect@", $actual);
    }
    
    public function testParse_frag()
    {
        $source = 'foo [[page name#frag]] bar';
        $actual = $this->_plugin->parse($source);
        $expect = "foo {$this->_encode} bar";
        $this->assertRegExp("@$expect@", $actual);
    }
    
    public function testParse_text()
    {
        $source = 'foo [[page name | text]] bar';
        $actual = $this->_plugin->parse($source);
        $expect = "foo {$this->_encode} bar";
        $this->assertRegExp("@$expect@", $actual);
    }
    
    public function testParse_atch()
    {
        $source = 'foo [[page name atch]]es bar';
        $actual = $this->_plugin->parse($source);
        $expect = "foo {$this->_encode} bar";
        $this->assertRegExp("@$expect@", $actual);
    }
    
    public function testParse_combo()
    {
        $source = 'foo [[page name#frag | display]]s bar';
        $actual = $this->_plugin->parse($source);
        $expect = "foo {$this->_encode} bar";
        $this->assertRegExp("@$expect@", $actual);
    }
    
    public function testRender()
    {
        $source = 'foo [[page name]] bar';
        $actual = $this->_spanTransform($source);
        $expect = 'foo <a href="/wiki/read/Page_name">page name</a> bar';
        $this->assertSame(trim($actual), trim($expect));
    }
    
    public function testRender_frag()
    {
        $source = 'foo [[page name#frag]] bar';
        $actual = $this->_spanTransform($source);
        $expect = 'foo <a href="/wiki/read/Page_name#frag">page name#frag</a> bar';
        $this->assertSame(trim($actual), trim($expect));
    }
    
    public function testRender_text()
    {
        $source = 'foo [[page name | text]] bar';
        $actual = $this->_spanTransform($source);
        $expect = 'foo <a href="/wiki/read/Page_name">text</a> bar';
        $this->assertSame(trim($actual), trim($expect));
    }
    
    public function testRender_atch()
    {
        $source = 'foo [[page name atch]]es bar';
        $actual = $this->_spanTransform($source);
        $expect = 'foo <a href="/wiki/read/Page_name_atch">page name atches</a> bar';
        $this->assertSame(trim($actual), trim($expect));
    }
    
    public function testRender_combo()
    {
        $source = 'foo [[page name#frag | display]]s bar';
        $actual = $this->_spanTransform($source);
        $expect = 'foo <a href="/wiki/read/Page_name#frag">displays</a> bar';
        $this->assertSame(trim($actual), trim($expect));
    }
    
    public function testRender_comboCollapse()
    {
        $source = 'foo [[page name#frag | ]]s bar';
        $actual = $this->_spanTransform($source);
        $expect = 'foo <a href="/wiki/read/Page_name#frag">page names</a> bar';
        $this->assertSame(trim($actual), trim($expect));
    }
    
    public function testRender_interwiki()
    {
        $source = 'foo [[php::print()]] bar';
        $actual = $this->_spanTransform($source);
        $expect = 'foo <a href="http://php.net/print()">php::print()</a> bar';
        $this->assertSame($expect, $actual);
    }
    
    public function testRender_interwikiFrag()
    {
        $source = 'foo [[php::print() #anchor]] bar';
        $actual = $this->_spanTransform($source);
        $expect = 'foo <a href="http://php.net/print()#anchor">php::print()#anchor</a> bar';
        $this->assertSame($expect, $actual);
    }
    
    public function testRender_interwikiText()
    {
        $source = 'foo [[php::print() | other]] bar';
        $actual = $this->_spanTransform($source);
        $expect = 'foo <a href="http://php.net/print()">other</a> bar';
        $this->assertSame($expect, $actual);
    }
    
    public function testRender_interwikiAtch()
    {
        $source = 'foo [[php::print]]ers bar';
        $actual = $this->_spanTransform($source);
        $expect = 'foo <a href="http://php.net/print">php::printers</a> bar';
        $this->assertSame($expect, $actual);
    }
    
    public function testRender_interwikiCombo()
    {
        $source = 'foo [[php::print()#anchor | print]]ers bar';
        $actual = $this->_spanTransform($source);
        $expect = 'foo <a href="http://php.net/print()#anchor">printers</a> bar';
        $this->assertSame($expect, $actual);
    }
    
    public function testRender_interwikiComboCollapse()
    {
        $source = 'foo [[php::print#anchor | ]]ers bar';
        $actual = $this->_spanTransform($source);
        $expect = 'foo <a href="http://php.net/print#anchor">printers</a> bar';
        $this->assertSame($expect, $actual);
    }
    
    public function testRender_manyPerLine()
    {
        $source = 'foo [[page one]] '
                . 'bar [[page two]] '
                . 'baz [[page three]] '
                . 'dib';
        
        $expect = 'foo <a href="/wiki/read/Page_one">page one</a> '
                . 'bar <a href="/wiki/read/Page_two">page two</a> '
                . 'baz <a href="/wiki/read/Page_three">page three</a> '
                . 'dib';
        
        $actual = $this->_spanTransform($source);
        $this->assertSame($expect, $actual);
    }
    
    public function testRender_interwikiManyPerLine()
    {
        $source = 'foo [[php::print()]] '
                . 'bar [[php::echo | ]] '
                . 'baz [[php::phpinfo()]] '
                . 'dib';
        
        $expect = 'foo <a href="http://php.net/print()">php::print()</a> '
                . 'bar <a href="http://php.net/echo">echo</a> '
                . 'baz <a href="http://php.net/phpinfo()">php::phpinfo()</a> '
                . 'dib';
        
        $actual = $this->_spanTransform($source);
        $this->assertSame($expect, $actual);
    }
    
    public function testRender_mixed()
    {
        $source = 'foo [[page one]] '
                . 'bar [[php::print()]] '
                . 'baz [[page two]] '
                . 'dib [[php::echo | ]] '
                . 'zim [[page three]] '
                . 'gir [[php::phpinfo()]] '
                . 'irk';
        
        $expect = 'foo <a href="/wiki/read/Page_one">page one</a> '
                . 'bar <a href="http://php.net/print()">php::print()</a> '
                . 'baz <a href="/wiki/read/Page_two">page two</a> '
                . 'dib <a href="http://php.net/echo">echo</a> '
                . 'zim <a href="/wiki/read/Page_three">page three</a> '
                . 'gir <a href="http://php.net/phpinfo()">php::phpinfo()</a> '
                . 'irk';
                
        $actual = $this->_spanTransform($source);
        $this->assertSame($expect, $actual);
    }
}
