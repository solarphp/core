<?php
class Test_Solar_Markdown_Wiki extends Solar_Test {
    
    protected $_markdown;
    
    /**
     * 
     * Tidy extension must be loaded.  This allows us to ignore
     * tag-level whitepspace differences.
     * 
     */
    public function __construct($config = null)
    {
        parent::__construct($config);
        // if (! extension_loaded('tidy')) {
        //     $this->skip("Tidy extension not loaded");
        // }
    }
    
    public function setup()
    {
        $this->_markdown = Solar::factory('Solar_Markdown_Wiki');
    }
    
    protected function _output($method)
    {
        $dir = dirname(__FILE__) . '/Wiki/output/';
        $base = substr($method, 5);
        $text_file = $dir . $base . '.text';
        $html_file = $dir . $base . '.html';
        $source = file_get_contents($text_file);
        
        $expect = $this->_tidy(file_get_contents($html_file));
        $actual = $this->_tidy($this->_markdown->transform($source));
        
        $this->assertSame($actual, $expect);
    }
    
    protected function _tidy($text)
    {
        // tidy up the text
        $tidy = new tidy;
        $tidy->parseString($text, array(), 'utf8');
        $tidy->cleanRepair();
        
        // get only the body portion
        $body = tidy_get_body($tidy);
        return $body->value;
    }
    
    public function test_basic()
    {
        $this->_output(__FUNCTION__);
    }
}
?>