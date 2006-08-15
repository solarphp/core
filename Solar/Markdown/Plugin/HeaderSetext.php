<?php
/**
 * 
 * Block plugin to convert Setext-style headers into XHTML header tags.
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
 * _____
 * 
 * @category Solar
 * 
 * @package Solar_Markdown
 * 
 */
class Solar_Markdown_Plugin_HeaderSetext extends Solar_Markdown_Plugin {
    
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
    protected $_chars = '-=';
    
    /**
     * 
     * Turns setext-style headers into XHTML header tags.
     * 
     * @param string $text Portion of the Markdown source text.
     * 
     * @return string The transformed XHTML.
     * 
     */
    public function parse($text)
    {
        $text = preg_replace_callback(
            '{ ^(.+)[ \t]*\n=+[ \t]*\n+ }mx',
            array($this, '_parseTop'),
            $text
        );
        
        $text = preg_replace_callback(
            '{ ^(.+)[ \t]*\n-+[ \t]*\n+ }mx',
            array($this, '_parseSub'),
            $text
        );
        
        return $text;
    }

    /**
     * 
     * Support callback for top-level setext headers ("h1").
     * 
     * @param array $matches Matches from preg_replace_callback().
     * 
     * @return string The replacement text.
     * 
     */
    protected function _parseTop($matches)
    {
        return "<h1>"
             . $this->_processSpans($matches[1])
             . "</h1>"
             . "\n\n";
    }
    
    /**
     * 
     * Support callback for sub-level setext headers ("h2").
     * 
     * @param array $matches Matches from preg_replace_callback().
     * 
     * @return string The replacement text.
     * 
     */
    protected function _parseSub($matches)
    {
        return "<h2>"
             . $this->_processSpans($matches[1])
             . "</h2>"
             . "\n\n";
    }
}
?>