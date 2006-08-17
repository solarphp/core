<?php
Solar::loadClass('Solar_Markdown_Plugin_HeaderSetext');
class Solar_Markdown_Extra_HeaderSetext extends Solar_Markdown_Plugin_HeaderSetext {
    
    protected $_chars = '-={}#';
    
    public function parse($text)
    {
        $text = preg_replace_callback(
            '{ (^.+?) (?:[ ]+\{\#([-_:a-zA-Z0-9]+)\})? [ \t]*\n=+[ \t]*\n+ }mx',
            array($this, '_parseTop'),
            $text
        );
    
        $text = preg_replace_callback(
            '{ (^.+?) (?:[ ]+\{\#([-_:a-zA-Z0-9]+)\})? [ \t]*\n-+[ \t]*\n+ }mx',
            array($this, '_parseSub'),
            $text
        );
        
        return $text;
    }
    
    protected function _parseTop($matches)
    {
        if (! empty($matches[2])) {
            $id = ' id="' . $this->_escape($matches[2]) . '"';
        } else {
            $id = '';
        }
        
        return "<h1$id>"
             . $this->_processSpans($matches[1])
             . "</h1>"
             . "\n\n";
    }
    
    protected function _parseSub($matches)
    {
        if (! empty($matches[2])) {
            $id = ' id="' . $this->_escape($matches[2]) . '"';
        } else {
            $id = '';
        }
        
        return "<h2$id>"
             . $this->_processSpans($matches[1])
             . "</h2>"
             . "\n\n";
    }
}
?>