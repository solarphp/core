<?php
/**
 * 
 * Replaces wiki links in source text with XHTML anchors.
 * 
 * @category Solar
 * 
 * @package Solar_Markdown
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Plugin.php 1668 2006-08-17 21:04:58Z pmjones $
 * 
 */

/**
 * Abstract plugin class.
 */
Solar::loadClass('Solar_Markdown_Plugin');

/**
 * 
 * Replaces wiki links in source text with XHTML anchors.
 * 
 * @category Solar
 * 
 * @package Solar_Markdown
 * 
 */
class Solar_Markdown_Wiki_PageLink extends Solar_Markdown_Plugin {
    
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
     * Runs during the cleanup() phase.
     * 
     * @var bool
     * 
     */
    protected $_is_cleanup = true;
    
    /**
     * 
     * Array of which pages exist and which don't.
     * 
     * Format is page name => true/false.
     * 
     * @var array
     * 
     */
    protected $_pages;
    
    /**
     * 
     * Array of information for each link found in the source text.
     * 
     * Each element is an array with these keys:
     * 
     * `norm`:
     * The normalized form of the page name.
     * 
     * `page`:
     * The page name as entered in the source text.
     * 
     * `frag`:
     * A fragment anchor for the target page (e.g., "#example").
     * 
     * `text`:
     * The text to display in place of the page name.
     * 
     * `atch`:
     * Attached suffix text to go on the end of the displayed text.
     * 
     * @var array
     * 
     */
    protected $_links;
    
    /**
     * 
     * Running count of $this->_links, so we don't have to call count()
     * on it all the time.
     * 
     * @var int
     * 
     */
    protected $_count = 0;
    
    /**
     * 
     * The name of this class, for identifying encoded keys in the
     * source text.
     * 
     * @var string
     * 
     */
    protected $_class;
    
    /**
     * 
     * Attribs for 'read' and 'add' style links.
     * 
     * Note that 'href' is special, in that it is an sprintf() format 
     * string.
     * 
     * @var array
     * 
     */
    protected $_attribs = array(
        'read' => array(
            'href' => '/wiki/read/%s'
        ),
        'add' => array(
            'href' => '/wiki/add/%s'
        ),
    );
    
    /**
     * 
     * Callback to check if pages linked from the source text exist or 
     * not.
     * 
     * @var callback
     * 
     */
    protected $_check_pages = false;
    
    /**
     * 
     * Constructor.
     * 
     * @param array $config Array of user-defined configuariont values.
     * 
     */
    public function __construct($config)
    {
        parent::__construct($config);
        $this->_class = get_class($this);
    }
    
    /**
     * 
     * Sets the callback to check if pages exist.
     * 
     * The callback has to take exactly one paramter, an array keyed
     * on page names, with the value being true or false.  It should
     * return a similar array, saying whether or not each page in the
     * array exists.
     * 
     * If left empty, the plugin will assume all links exist.
     * 
     * @param callback $callback The callback to check if pages exist.
     * 
     * @return array An array of which pages exist and which don't.
     * 
     */
    public function setCheckPagesCallback($callback)
    {
        $this->_check_pages = $callback;
    }
    
    /**
     * 
     * Sets one anchor attribute.
     * 
     * @param string $type The anchor type, generally 'read' or 'add'.
     * 
     * @param string $key The attribute key, e.g. 'href' or 'class'.
     * 
     * @param string $val The attribute value.
     * 
     * @return void
     * 
     */
    public function setAttrib($type, $key, $val)
    {
        $this->_attribs[$type][$key] = $val;
    }
    
    /**
     * 
     * Sets all attributes for one anchor type.
     * 
     * @param string $type The anchor type, generally 'read' or 'add'.
     * 
     * @param array $list The attributes to set in key => value format.
     * 
     * @return void
     * 
     */
    public function setAttribs($type, $list)
    {
        $this->_attribs[$type] = $list;
    }
    
    /**
     * 
     * Gets the list of pages found in the source text.
     * 
     * @return array
     * 
     */
    public function getPages()
    {
        return array_keys($this->_pages);
    }
    
    /**
     * 
     * Resets this plugin for a new transformation.
     * 
     * @return void
     * 
     */
    public function reset()
    {
        parent::reset();
        $this->_links = array();
        $this->_pages = array();
        $this->_count = 0;
    }
    
    /**
     * 
     * Parses the source text for wiki links.
     * 
     * Wiki links are in this format:
     * 
     *     [[wiki page]]
     *     [[wiki page #anchor]]
     *     [[wiki page]]s
     *     [[wiki page | display this instead]]
     * 
     * Links are replaced with encoded placeholders.  At cleanup() time,
     * the placeholders are transformed into XHTML anchors.
     * 
     * @param string $text The source text.
     * 
     * @return string The parsed text.
     * 
     */
    public function parse($text)
    {
        $regex = '/\[\[(.*?)(\#.*?)?(\|.*?)?\]\](\S*)?/';
        return preg_replace_callback(
            $regex,
            array($this, '_parse'),
            $text
        );
    }
    
    /**
     * 
     * Support callback for parsing wiki links.
     * 
     * @param array $matches Matches from preg_replace_callback().
     * 
     * @return string The replacement text.
     * 
     */
    protected function _parse($matches)
    {
        $page = $matches[1];
        $frag = empty($matches[2]) ? null  : trim($matches[2], "# \t");
        $text = empty($matches[3]) ? $page : trim($matches[3], "| \t");
        $atch = empty($matches[4]) ? null  : trim($matches[4]);
        
        // normalize the page name
        $norm = $this->_normalize($page);
        
        // assume the page exists
        $this->_pages[$norm] = true;
        
        // save the link
        $this->_links[$this->_count] = array(
            'norm' => $norm,
            'page' => $page,
            'frag' => $frag,
            'text' => $text,
            'atch' => $atch,
        );
        
        // generate an escaped WikiLink token to be replaced at
        // cleanup() time with real HTML.
        $key = $this->_class . ':' . $this->_count ++;
        return "\x1B$key\x1B";
    }
    
    /**
     * 
     * Normalizes a wiki page name.
     * 
     * @param string $page The page name from the source text.
     * 
     * @return string The normalized page name.
     * 
     */
    protected function _normalize($page)
    {
        // trim, force only the first letter to upper-case (leaving all
        // other characters alone), and then replace all whitespace
        // runs with a single underscore.
        return preg_replace('/\s+/', '_', ucfirst(trim($page)));
    }
    
    /**
     * 
     * Cleans up text to replace encoded placeholders with anchors.
     * 
     * @param string $page The page name from the source text.
     * 
     * @return string The normalized page name.
     * 
     */
    public function cleanup($text)
    {
        // first, update $this->_pages against the data store to see
        // which pages exist and which do not.
        if ($this->_check_pages) {
            $this->_pages = call_user_func($_check_pages, $this->_pages);
        }
        
        // now go through and replace tokens
        $regex = "/\x1B{$this->_class}:(.*?)\x1B/";
        return preg_replace_callback(
            $regex,
            array($this, '_cleanup'),
            $text
        );
    }
    
    /**
     * 
     * Support callback for replacing placeholder with anchors.
     * 
     * @param array $matches Matches from preg_replace_callback().
     * 
     * @return string The replacement text.
     * 
     */
    protected function _cleanup($matches)
    {
        $key = $matches[1];
        $tmp = $this->_links[$key];
        
        // normalized page name
        $norm = $tmp['norm'];
        
        // page name as entered
        $page = $tmp['page'];
        
        // anchor "#fragment"
        $frag = $tmp['frag'];
        if ($frag) {
            $frag = "#$frag";
        }
        
        // optional display text
        $text = $tmp['text'];
        
        // optional attached text outside the link
        $atch = $tmp['atch'];
        
        // make sure the page is listed; the check-pages callback
        // may not have populated it back.
        if (empty($this->_pages[$norm])) {
            $this->_pages[$norm] = false;
        }
        
        // use "read" or "add" attribs?
        if ($this->_pages[$norm]) {
            // page exists
            $attribs = $this->_attribs['read'];
        } else {
            // page does not exist
            $attribs = $this->_attribs['add'];
        }
        
        // make sure we have an href attrib
        if (empty($attribs['href'])) {
            $attribs['href'] = '%s';
        }
        
        // build the opening <a href="" portion of the tag.
        $html = '<a href="'
              . $this->_escape(sprintf($attribs['href'], $norm . $frag))
              . '"';
              
        // add attributes and close the opening tag
        unset($attribs['href']);
        foreach ($attribs as $key => $val) {
            $key = $this->_escape($key);
            $val = $this->_escape($val);
            $html .= " $key=\"$val\"";
        }
        $html .= ">";
        
        // add the escaped the display text and close the tag
        $html .= $this->_escape($text . $atch) . "</a>";
        
        // done!
        return $html;
    }
}
?>