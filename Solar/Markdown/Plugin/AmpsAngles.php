<?php
Solar::loadClass('Solar_Markdown_Plugin');
class Solar_Markdown_Plugin_AmpsAngles extends Solar_Markdown_Plugin {
    
    protected $_is_span = true;
    
    // Smart processing for ampersands and angle brackets that need to be encoded.
    public function parse($text)
    {
        // Ampersand-encoding based entirely on Nat Irons's Amputator MT plugin:
        //   http://bumppo.net/projects/amputator/
        $text = preg_replace(
            '/&(?!#?[xX]?(?:[0-9a-fA-F]+|\w+);)/', 
            '&amp;',
            $text
        );

        // Encode naked <'s
        $text = preg_replace('{<(?![a-z/?\$!])}i', '&lt;', $text);

        return $text;
    }
}
?>