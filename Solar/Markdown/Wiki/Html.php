<?php
Solar::loadClass('Solar_Markdown_Plugin');
class Solar_Markdown_Wiki_Html extends Solar_Markdown_Plugin {
    
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