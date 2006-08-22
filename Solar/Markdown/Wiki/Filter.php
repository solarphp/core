<?php
Solar::loadClass('Solar_Markdown_Plugin_Prefilter');
class Solar_Markdown_Wiki_Filter extends Solar_Markdown_Plugin_Prefilter {
    
    protected $_is_cleanup = true;
    
    public function cleanup($text)
    {
        // all HTML remaining in the text should be escaped
        $text = $this->_escape($text);
        
        // render all html
        return $this->_unHtmlToken($text);
    }
}
?>