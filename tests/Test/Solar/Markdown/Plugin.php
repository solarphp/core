<?php
abstract class Test_Solar_Markdown_Plugin extends Solar_Test {
    
    protected $_markdown;
    
    protected $_class;
    
    protected $_plugin;
    
    protected $_text;
    
    protected $_token = "\x0E.*?\x0F";
    
    protected $_encode = "\x1B.*?\x1B";
    
    // we need a special transform to process **spans** instead of
    // blocks, for span testing.
    protected function _spanTransform($text)
    {
        $text = $this->_markdown->prepare($text);
        $text = $this->_markdown->processSpans($text);
        $text = $this->_markdown->cleanup($text);
        $text = $this->_markdown->render($text);
        return $text;
    }
    
    public function __construct($config = null)
    {
        parent::__construct($config);
        $this->_class = substr(get_class($this), 5);
    }
    
    public function setup()
    {
        // limit Markdown to the one plugin we're testing
        $config['plugins'] = array($this->_class);
        $this->_markdown = Solar::factory('Solar_Markdown', $config);
        
        // build the plugin
        $config['markdown'] = $this->_markdown;
        $this->_plugin = Solar::factory($this->_class, $config);
        
    }
    
    public function test__construct()
    {
        $this->assertInstance($this->_plugin, $this->_class);
    }
    
    public function testIsBlock()
    {
        $this->todo('needs an isBlock() test');
    }
    
    public function testIsSpan()
    {
        $this->todo('needs an isSpan() test');
    }
    
    public function testPrepare()
    {
        $this->todo('needs a prepare() test');
    }
    
    public function testParse()
    {
        $this->todo('needs a parse() test');
    }
    
    public function testCleanup()
    {
        $this->todo('needs a cleanup() test');
    }
    
    protected function _tag($tag)
    {
        return "\s*<$tag>\s*";
    }
}
?>