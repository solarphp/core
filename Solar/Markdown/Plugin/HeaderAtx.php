<?php
/**
 * 
 * Block plugin to turn ATX-style headers into XHTML header tags.
 * 
 * @category Solar
 * 
 * @package Solar_Markdown
 * 
 * @author John Gruber <http://daringfireball.net/projects/markdown/>
 * 
 * @author Michel Fortin <http://www.michelf.com/projects/php-markdown/>
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id$
 * 
 */

/**
 * Abstract plugin class.
 */
Solar::loadClass('Solar_Markdown_Plugin');

/**
 * 
 * Block plugin to turn ATX-style headers into XHTML header tags.
 * 
 * For example, this code ...
 * 
 *     # Header 1
 * 
 *     ## Header 2
 * 
 *     ##### Header 5
 * 
 * ... would become:
 * 
 *     <h1>Header 1</h1>
 * 
 *     <h2>Header 1</h2>
 * 
 *     <h5>Header 1</h5>
 * 
 * @category Solar
 * 
 * @package Solar_Markdown
 * 
 */
class Solar_Markdown_Plugin_HeaderAtx extends Solar_Markdown_Plugin {
    
    /**
     * 
     * This is a block plugin.
     * 
     * @var bool
     * 
     */
    protected $_is_block = true;
    
    /**
     * 
     * These should be encoded as special Markdown characters.
     * 
     * @var string
     * 
     */
    protected $_chars = '#';
    
    /**
     * 
     * Turns ATX-style headers into XHTML header tags.
     * 
     * @param string $text Portion of the Markdown source text.
     * 
     * @return string The transformed XHTML.
     * 
     */
    public function parse($text)
    {
        $text = preg_replace_callback(
            "{
                ^(\\#{1,6}) # $1 = string of #'s
                [ \\t]*
                (.+?)       # $2 = Header text
                [ \\t]*
                \\#*        # optional closing #'s (not counted)
                \\n+
            }xm",
            array($this, '_parse'),
            $text
        );
        
        return $text;
    }

    /**
     * 
     * Support callback.
     * 
     * @param array $matches Matches from preg_replace_callback().
     * 
     * @return string The replacement text.
     * 
     */
    protected function _parse($matches)
    {
        $tag = 'h' . strlen($matches[1]); // h1, h2, h5, etc
        return "<$tag>"
             . $this->_processSpans($matches[2])
             . "</$tag>"
             . "\n\n";
    }
}
?>