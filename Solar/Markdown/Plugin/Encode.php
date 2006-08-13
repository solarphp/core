<?php
Solar::loadClass('Solar_Markdown_Plugin');
class Solar_Markdown_Plugin_Encode extends Solar_Markdown_Plugin {
    
    protected $_is_span = true;
    
    public function parse($text)
    {
        // encode backslash-escaped characters
        return $this->_encode($text, true);
    }
}
?>