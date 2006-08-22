<?php
/**
 * 
 * Block plugin to convert wiki-fied headers into XHTML headers.
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
 * Block plugin to convert wiki-fied headers into XHTML headers.
 * 
 * This code ...
 * 
 *     =======
 *      Title
 *     =======
 *     
 *     ---------------
 *      Super-Section
 *     ---------------
 *     
 *     Section
 *     =======
 *     
 *     Sub Section
 *     -----------
 *     
 * ... would become:
 * 
 *     <h2>Title</h2>
 *     
 *     <h3>Super-Section</h3>
 *     
 *     <h4>Section</h4>
 *     
 *     <h5>Sub-Section</h5>
 * 
 * @category Solar
 * 
 * @package Solar_Markdown
 * 
 */
class Solar_Markdown_Wiki_Header extends Solar_Markdown_Plugin {
    
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
        // h2
        $text = preg_replace_callback(
            '{ ^=+[ \t]*\n(.+)[ \t]*\n=+[ \t]*\n+ }mx',
            array($this, '_parseTitle'),
            $text
        );
        
        // h3
        $text = preg_replace_callback(
            '{ ^-+[ \t]*\n(.+)[ \t]*\n-+[ \t]*\n+ }mx',
            array($this, '_parseSuperSection'),
            $text
        );
        
        // h4
        $text = preg_replace_callback(
            '{ ^(.+)[ \t]*\n=+[ \t]*\n+ }mx',
            array($this, '_parseSection'),
            $text
        );
        
        // h5
        $text = preg_replace_callback(
            '{ ^(.+)[ \t]*\n-+[ \t]*\n+ }mx',
            array($this, '_parseSubSection'),
            $text
        );
        
        return $text;
    }

    /**
     * 
     * Support callback for H2 headers.
     * 
     * @param array $matches Matches from preg_replace_callback().
     * 
     * @return string The replacement text.
     * 
     */
    protected function _parseTitle($matches)
    {
        return $this->_header('h1', $matches[1]);
    }

    /**
     * 
     * Support callback for H3 headers.
     * 
     * @param array $matches Matches from preg_replace_callback().
     * 
     * @return string The replacement text.
     * 
     */
    protected function _parseSuperSection($matches)
    {
        return $this->_header('h2', $matches[1]);
    }

    /**
     * 
     * Support callback for H4 headers.
     * 
     * @param array $matches Matches from preg_replace_callback().
     * 
     * @return string The replacement text.
     * 
     */
    protected function _parseSection($matches)
    {
        return $this->_header('h3', $matches[1]);
    }

    /**
     * 
     * Support callback for H5 headers.
     * 
     * @param array $matches Matches from preg_replace_callback().
     * 
     * @return string The replacement text.
     * 
     */
    protected function _parseSubSection($matches)
    {
        return $this->_header('h4', $matches[1]);
    }
    
    /**
     * 
     * Support callback for all headers.
     * 
     * @param string $tag The header tag ('h1', 'h5', etc).
     * 
     * @param string $text The header text.
     * 
     * @return string The replacement header HTML token.
     * 
     */
    protected function _header($tag, $text)
    {
        $html = "<$tag>"
              . $this->_processSpans($text)
              . "</$tag>";
        
        return $this->_toHtmlToken($html) . "\n\n";
    }
}
?>