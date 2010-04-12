<?php
/**
 * 
 * Replaces class page links in source text with XHTML anchors.
 * 
 * Class page links are in this format ...
 * 
 *     [[Class]]
 *     [[Class]]es
 *     [[Class | display this instead]]
 *     [[Class::Page]]
 *     [[Class::$property]]
 *     [[Class::method()]]
 *     [[Class::CONSTANT]]
 * 
 * @category Solar
 * 
 * @package Solar_Markdown_Apidoc
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Link.php 3988 2009-09-04 13:51:51Z pmjones $
 * 
 */
class Solar_Markdown_Apidoc_ClassPage extends Solar_Markdown_Plugin
{
    /**
     * 
     * Default configuration values.
     * 
     * @config string constant A string template for the xml:id for 
     * "Constants" pages.
     * 
     * @config string overview A string template for the xml:id for 
     * "Overview" pages.
     * 
     * @config string method A string template for the xml:id for 
     * individual method pages.
     * 
     * @config string other A string template for the xml:id for 
     * all other types of pages.
     * 
     * @config string property A string template for the xml:id for 
     * "Properties" pages.
     * 
     * @var array
     * 
     */
    protected $_Solar_Markdown_Apidoc_ClassPage = array(
        'constant'  => 'class.{:class}.Constants.{:page}.html',
        'overview'  => 'class.{:class}.Overview.html',
        'method'    => 'class.{:class}.{:page}.html',
        'other'     => 'class.{:class}.{:page}.html',
        'property'  => 'class.{:class}.Properties.html#{:page}',
    );
    
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
     * Parses the source text for Class::Page links.
     * 
     * @param string $text The source text.
     * 
     * @return string The parsed text.
     * 
     */
    public function parse($text)
    {
        $regex = '/\[\[(.*?)(\|.*?)?\]\](\w*)?/';
        return preg_replace_callback(
            $regex,
            array($this, '_parse'),
            $text
        );
    }
    
    /**
     * 
     * Support callback for parsing class page links.
     * 
     * @param array $matches Matches from preg_replace_callback().
     * 
     * @return string The replacement text.
     * 
     */
    protected function _parse($matches)
    {
        $spec = $matches[1];
        $text = empty($matches[2]) ? $spec : trim($matches[2], "| \t");
        $atch = empty($matches[3]) ? null  : trim($matches[3]);
        
        if (strtolower(substr($spec, 0, 5)) == 'php::') {
            $link = $this->_getPhpLink($spec, $text, $atch);
        } else {
            $link = $this->_getClassPageLink($spec, $text, $atch);
        }
        
        return $this->_toHtmlToken($link);
    }
    
    /**
     * 
     * Builds a link for php.net pages.
     * 
     * @param string $spec The link specification.
     * 
     * @param string $text The displayed text for the link.
     * 
     * @param string $atch Additional non-linked text.
     * 
     * @return string The replacement text.
     * 
     */
    protected function _getPhpLink($spec, $text, $atch)
    {
        $pos  = strpos($spec, '::');
        $page = trim(substr($spec, $pos + 2));
        if (substr($page, -2) == '()') {
            $page = substr($page, 0, -2);
        }
        
        $href = "http://php.net/$page";
        
        return '<link xlink:href="' . $this->_escape($href) . '">'
             . $this->_escape($text . $atch)
             . '</link>';
    }
    
    /**
     * 
     * Builds a link for class API documentation pages.
     * 
     * @param string $spec The link specification.
     * 
     * @param string $text The displayed text for the link.
     * 
     * @param string $atch Additional non-linked text.
     * 
     * @return string The replacement text.
     * 
     */
    protected function _getClassPageLink($spec, $text, $atch)
    {
        $pos = strpos($spec, '::');
        if ($pos === false) {
            $class = $spec;
            $page  = null;
        } else {
            $class = trim(substr($spec, 0, $pos));
            $page  = trim(substr($spec, $pos + 2));
        }
        
        // what kind of link to build?
        if (! $page) {
            // no page specified
            $link = $this->_config['overview'];
        } elseif (substr($page, 0, 1) == '$') {
            // $property
            $link = $this->_config['property'];
            $page = substr($page, 1);
        } elseif (substr($page, -2) == '()') {
            // method()
            $link = $this->_config['method'];
            $page = substr($page, 0, -2);
        } elseif (strtoupper($page) === $page) {
            // CONSTANT
            $link = $this->_config['constant'];
        } else {
            // other
            $link = $this->_config['other'];
        }
        
        // interpolate values into link template placeholders
        $keys = array('{:class}', '{:page}');
        $vals = array($class, $page);
        $href = str_replace($keys, $vals, $link);
        
        return '<link xlink:href="' . $this->_escape($href) . '">'
             . $this->_escape($text . $atch)
             . '</link>';
    }
}
