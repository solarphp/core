<?php
Solar::loadClass('Solar_Markdown_Plugin');
class Solar_Markdown_Plugin_Break extends Solar_Markdown_Plugin {
    
    protected $_is_span = true;
    
    public function parse($text)
    {
        return preg_replace('/ {2,}\n/', "<br />\n", $text);
    }
}
?>