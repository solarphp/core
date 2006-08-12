<?php
Solar::loadClass('Solar_Markdown_Plugin');
class Solar_Markdown_Plugin_CodeSpan extends Solar_Markdown_Plugin {
    
    protected $_is_span = true;
    
    protected $_chars = '`';
    
    #
    #     *    Backtick quotes are used for <code></code> spans.
    #
    #     *    You can use multiple backticks as the delimiters if you want to
    #         include literal backticks in the code span. So, this input:
    #
    #          Just type ``foo `bar` baz`` at the prompt.
    #
    #          Will translate to:
    #
    #          <p>Just type <code>foo `bar` baz</code> at the prompt.</p>
    #
    #        There's no arbitrary limit to the number of backticks you
    #        can use as delimters. If you need three consecutive backticks
    #        in your code, use four for delimiters, etc.
    #
    #    *    You can use spaces to get literal backticks at the edges:
    #
    #          ... type `` `bar` `` ...
    #
    #          Turns to:
    #
    #          ... type <code>`bar`</code> ...
    #
    public function parse($text)
    {
        $text = preg_replace_callback('@
                (?<!\\\) # Character before opening ` cannot be a backslash
                (`+)     # $1 = Opening run of `
                (.+?)    # $2 = The code block
                (?<!`)   
                \1       # Matching closer
                (?!`)
            @xs',
            array($this, '_parse'),
            $text
        );

        return $text;
    }
    
    protected function _parse($matches)
    {
        $c = $matches[2];
        $c = preg_replace('/^[ \t]*/', '', $c); # leading whitespace
        $c = preg_replace('/[ \t]*$/', '', $c); # trailing whitespace
        $c = $this->_escapeHtml($c);
        $c = $this->_escapeChars($c);
        return "<code>$c</code>";
    }
}
?>