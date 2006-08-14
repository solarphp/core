<?php
Solar::loadClass('Solar_Markdown_Plugin');
class Solar_Markdown_Plugin_Uri extends Solar_Markdown_Plugin {
    
    protected $_is_span = true;
    
    protected $_schemes = array('http', 'https', 'ftp');
    
    protected $_chars = '<>';
    
    public function parse($text)
    {
        
        $list = implode('|', $this->_schemes);
        $text = preg_replace_callback(
            "!<(($list):[^'\">\\s]+)>!", 
            array($this, '_parse'),
            $text
        );

        # Email addresses: <address@domain.foo>
        $text = preg_replace_callback('{
                <
                    (?:mailto:)?
                    (
                        [-.\w]+
                        \@
                        [-a-z0-9]+(\.[-a-z0-9]+)*\.[a-z]+
                    )
                >
            }xi',
            array($this, '_parseEmail'),
            $text
        );

        return $text;
    }

    protected function _parse($matches)
    {
        $href = $this->_escape($matches[1]);
        return "<a href=\"$href\">$href</a>";
    }
    
    /**
     *
     *    Input: an email address, e.g. "foo@example.com"
     *
     *    Output: the email address as a mailto link, with each character
     *        of the address encoded as either a decimal or hex entity, in
     *        the hopes of foiling most address harvesting spam bots. E.g.:
     *
     *      <a href="&#x6D;&#97;&#105;&#108;&#x74;&#111;:&#102;&#111;&#111;&#64;&#101;
     *        x&#x61;&#109;&#x70;&#108;&#x65;&#x2E;&#99;&#111;&#109;">&#102;&#111;&#111;
     *        &#64;&#101;x&#x61;&#109;&#x70;&#108;&#x65;&#x2E;&#99;&#111;&#109;</a>
     *
     *    Based by a filter by Matthew Wickline, posted to the BBEdit-Talk
     *    mailing list: <http://tinyurl.com/yu7ue>
     *
     */
    protected function _parseEmail($matches)
    {
        $addr = $matches[1];
        // _UnescapeSpecialChars(_UnslashQuotes('\\1'))
        
        $addr = "mailto:" . $addr;
        $length = strlen($addr);

        # leave ':' alone (to spot mailto: later)
        $addr = preg_replace_callback(
            '/([^\:])/',
            array($this, '_obfuscateEmail'),
            $addr
        );

        $addr = "<a href=\"$addr\">$addr</a>";
        # strip the mailto: from the visible part
        $addr = preg_replace('/">.+?:/', '">', $addr);

        return $addr;
    }
    
    protected function _obfuscateEmail($matches) {
        $char = $matches[1];
        $r = rand(0, 100);
        # roughly 10% raw, 45% hex, 45% dec
        # '@' *must* be encoded. I insist.
        if ($r > 90 && $char != '@') return $char;
        if ($r < 45) return '&#x'.dechex(ord($char)).';';
        return '&#'.ord($char).';';
    }

}
?>