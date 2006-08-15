<?php
/**
 * 
 * Span class to convert ampersands and less-than angle brackets to 
 * their HTML entity equivalents.
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
 * Span class to convert ampersands and less-than angle brackets to 
 * their HTML entity equivalents.
 * 
 * @category Solar
 * 
 * @package Solar_Markdown
 * 
 */
class Solar_Markdown_Plugin_AmpsAngles extends Solar_Markdown_Plugin {
    
    /**
     * 
     * This is a span plugin.
     * 
     * @var bool
     * 
     */
    protected $_is_span = true;
    
    /**
     * 
     * Smart processing for encoding ampersands and left-angle brackets.
     * 
     * Ampersand-encoding based entirely on Nat Irons's [Amputator MT][]
     * plugin.
     * 
     * [Amputator MT]: http://bumppo.net/projects/amputator/
     * 
     * @param string $text The source text to be parsed.
     * 
     * @return string The transformed XHTML.
     * 
     */
    public function parse($text)
    {
        // encode ampersands
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