<?php
Solar::loadClass('Solar_Markdown_Plugin');
class Solar_Markdown_Wiki_Escape extends Solar_Markdown_Plugin {
    protected $_is_span = true;
    public function parse($text)
    {
        return $this->_escape($text);
    }
}
?>