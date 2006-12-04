<?php
Solar::loadClass('Solar_Test_Bench');

class Bench_Solar_Markdown extends Solar_Test_Bench {
    
    protected $_markdown;
    
    protected $_source;
    
    public function setup()
    {
        include_once dirname(__FILE__) . '/Markdown/php-markdown.php';
        $this->_markdown = Solar::factory('Solar_Markdown');
        $this->_source = dirname(__FILE__) . '/Markdown/source.text';
    }
    
    public function benchSolarMarkdown()
    {
        $text = $this->_source;
        $html = $this->_markdown->transform($text);
    }
    
    public function benchPhpMarkdown()
    {
        $text = $this->_source;
        $html = Markdown($text);
    }
}
?>