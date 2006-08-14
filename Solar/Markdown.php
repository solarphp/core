<?php
/**
 * 
 * Pluggable text-to-XHTML converter based on Markdown.
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
 * 
 * Pluggable text-to-XHTML converter based on Markdown.
 * 
 * This package is ported from John Gruber's [Markdown][] script in Perl,
 * with many thanks to Michel Fortin for his [PHP Markdown][] port to
 * PHP 4.
 * 
 * [Markdown]: http://daringfireball.net/projects/markdown/
 * [PHP Markdown]: http://www.michelf.com/projects/php-markdown/
 * 
 * Unlike Markdown and PHP Markdown, Solar_Markdown is plugin-aware.
 * Every processing rule is a separate class, and classes can be strung
 * together in a manner largely independent of each other (although the
 * order of processing still matters a great deal).
 * 
 * This plugin awareness is based on my work from [Text_Wiki][].  The
 * Text_Wiki project is capable of converting to any rendering format,
 * whereas Solar_Markdown is targeted only at XHTML.  If you need to
 * output to something other than XHTML, I suggest a two-step output
 * process:  Markdown to XHTML, then XHTML to your final format.
 * 
 * @category Solar
 * 
 * @package Solar_Markdown
 * 
 */
class Solar_Markdown extends Solar_Base {
    
    /**
     * 
     * Default configuration for the class.
     * 
     * Keys are:
     * 
     * : tab_width : (int) Number of spaces per tab.  Default 4.
     * 
     * : tidy : (bool|array) If false, do not use Tidy to post-process
     *   the transformed output.  If true or an array, is a set of
     *   config options to pass to Tidy when rendering output.
     *   See also <http://php.net/tidy>.  Default true.
     * 
     * : plugins : (array) An array of plugins for the parser to use, in
     *   order.
     * 
     * @var array
     * 
     */
    protected $_Solar_Markdown = array(
        
        'tab_width' => 4,
        
        'tidy' => true,
        
        'plugins' => array(
            // pre-processing on the source as a whole
            'Solar_Markdown_Plugin_Prefilter',
            'Solar_Markdown_Plugin_StripLinkDefs',
            
            // blocks
            'Solar_Markdown_Plugin_HeaderSetext',
            'Solar_Markdown_Plugin_HeaderAtx',
            'Solar_Markdown_Plugin_HorizRule',
            'Solar_Markdown_Plugin_List',
            'Solar_Markdown_Plugin_CodeBlock',
            'Solar_Markdown_Plugin_BlockQuote',
            'Solar_Markdown_Plugin_Html',
            'Solar_Markdown_Plugin_Paragraph',
            
            // spans
            'Solar_Markdown_Plugin_CodeSpan',
            'Solar_Markdown_Plugin_Image',
            'Solar_Markdown_Plugin_Link',
            'Solar_Markdown_Plugin_Uri',
            'Solar_Markdown_Plugin_Encode',
            'Solar_Markdown_Plugin_AmpsAngles',
            'Solar_Markdown_Plugin_EmStrong',
            'Solar_Markdown_Plugin_Break',
        ),
    );
    
    /**
     * 
     * Left-delimiter for HTML tokens.
     * 
     * @var string
     * 
     */
    protected $_html_ldelim = "\x0E";
    
    /**
     * 
     * Right-delimiter for HTML tokens.
     * 
     * @var string
     * 
     */
    protected $_html_rdelim = "\x0F";
    
    /**
     * 
     * Delimiter for encoded Markdown characters.
     * 
     * @var string
     * 
     */
    protected $_char_delim = "\x1B";
    
    /**
     * 
     * Array of HTML blocks represented by delimited token numbers.
     * 
     * Format is token => html.
     * 
     * @var array
     * 
     */
    protected $_html = array();
    
    /**
     * 
     * Running count of $this->_html elements so we don't have to 
     * count($this->_html) each time we add an HTML token.
     * 
     * @var int
     * 
     */
    protected $_count = 0;
    
    /**
     * 
     * List of defined link references keyed on the link name.
     * 
     * Format is "link-name" => array('href' => ..., 'title' => ...).
     * 
     * Generally populated via the StripLinkDefs plugin.
     * 
     * @var array
     * 
     */
    protected $_link = array();
    
    /**
     * 
     * Escape table for special Markdown characters.
     * 
     * Format is "$char" => "\x1B$char\x1B".
     * 
     * @var array
     * 
     */
    protected $_esc = array();
    
    /**
     * 
     * Escape table for backslashed special Markdown characters.
     * 
     * Format is "\\$char" => "\x1B$char\x1B".
     * 
     * Note that the backslash escape table and the normal escape table
     * map to identical escape sequences.
     * 
     * @var array
     * 
     */
    protected $_bs_esc = array();
    
    // plugin class => object
    protected $_plugin = array();
    
    // list of block-type plugins
    protected $_block_class = array();
    
    // list of span-type plugins
    protected $_span_class = array();
    
    protected $_chars = '.{}\\';
        
    /**
     * 
     * Constructor.
     * 
     * Loads the plugins and builds a list of characters to encode.
     * 
     * @param array $config User-defined configuration values.
     * 
     */
    public function __construct($config = null)
    {
        parent::__construct($config);
        
        if ($this->_config['tidy'] !== false &&
            ! extension_loaded('tidy')) {
            // tidy requsted but not loaded
            throw $this->_exception('ERR_TIDY_NOT_LOADED');
        }
        
        $config = array('markdown' => $this);
        
        foreach ($this->_config['plugins'] as $class) {
            // save the plugin object
            $this->_plugin[$class] = Solar::factory($class, $config);
            
            // is it a block plugin?
            if ($this->_plugin[$class]->isBlock()) {
                $this->_block_class[] = $class;
            }
            
            // is it a span plugin?
            if ($this->_plugin[$class]->isSpan()) {
                $this->_span_class[] = $class;
            }
            
            // assemble character list
            $this->_chars .= $this->_plugin[$class]->getChars();
        }
        
        // build the character escape tables
        $k = strlen($this->_chars);
        for ($i = 0; $i < $k; ++ $i) {
            $char = $this->_chars[$i];
            $delim = $this->_char_delim . $i. $this->_char_delim;
            $this->_esc[$char] = $delim;
            $this->_bs_esc["\\$char"] = $delim;
            
        }
    }
    
    /**
     * 
     * One-step transformation of source text using plugins.
     * 
     * @param string $text The source text.
     * 
     * @return string The transformed text.
     * 
     */
    public function transform($text)
    {
        $text = $this->prepare($text);
        $text = $this->processBlocks($text);
        $text = $this->cleanup($text);
        return $this->render($text);
    }
    
    /**
     * 
     * Prepares the text for processing by running the prepare() 
     * method of each plugin, in order, on the source text.
     * 
     * Also resets 
     * @param string $text The source text.
     * 
     * @return string The source text after processing.
     * 
     */
    public function prepare($text)
    {
        // reset from previous transformations
        $this->_count = 0;
        $this->_html = array();
        $this->_link = array();
        
        // let each plugin prepare the source text for parsing
        foreach ($this->_plugin as $plugin) {
            $plugin->reset();
            $text = $plugin->prepare($text);
        }
        
        return $text;
    }
    
    public function cleanup($text)
    {
        // let each plugin clean up the rendered source
        foreach ($this->_plugin as $plugin) {
            $text = $plugin->cleanup($text);
        }
        return $text;
    }
    
    public function render($text)
    {
        // replace any remaining HTML tokens
        $text = $this->unHtmlToken($text);
        
        // replace all special chars in the text.
        $text = $this->unEncode($text);
        
        if ($this->_config['tidy'] === false) {
            // tidy explicitly disabled
            return $text;
        }
        
        // tidy up the text
        $tidy = new tidy;
        $opts = (array) $this->_config['tidy'];
        $tidy->parseString($text, $opts, 'utf8');
        $tidy->cleanRepair();
        
        // get only the body portion
        $body = trim(tidy_get_body($tidy)->value);
        
        // remove <body> and </body>
        return substr($body, 6, -7);
    }
    
    /**
     * 
     * Processes text through all block-type plugins.
     * 
     * @param string $text The source text.
     * 
     * @return string The processed text.
     * 
     */
    public function processBlocks($text)
    {
        foreach ($this->_block_class as $class) {
            $text = $this->_plugin[$class]->parse($text);
        }
        return $text;
    }
    
    /**
     * 
     * Processes text through all span-type plugins.
     * 
     * @param string $text The source text.
     * 
     * @return string The processed text.
     * 
     */
    public function processSpans($text)
    {
        foreach ($this->_span_class as $class) {
            $text = $this->_plugin[$class]->parse($text);
        }
        return $text;
    }
    
    /**
     * 
     * Saves a pieces of text as HTML and returns a delimited token.
     * 
     * When you convert a piece of text an HTML token, that HTML will
     * no longer be processed by remaining plugins.
     * 
     * @param string $text The text to retain as HTML.
     * 
     * @return An HTML token.
     * 
     */
    public function toHtmlToken($text)
    {
        $key = $this->_html_ldelim
             . $this->_count
             . $this->_html_rdelim;
        
        $this->_html[$this->_count ++] = $text;
        return $key;
    }
    
    /**
     * 
     * Is a piece of text a delimited HTML token?
     * 
     * @param string $text The text to check.
     * 
     * @return bool True if a token, false if not.
     * 
     */
    public function isHtmlToken($text)
    {
        return preg_match(
            "/^{$this->_html_ldelim}.*?{$this->_html_rdelim}$/",
            $text
        );
    }
    
    /**
     * 
     * Replaces all HTML tokens in source text with saved HTML.
     * 
     * @param string $text The text to do replacements in.
     * 
     * @return string The source text with HTML in place of tokens.
     * 
     */
    public function unHtmlToken($text, $token = null)
    {
        if ($token) {
            // replace one token
            $find = "{$this->_html_ldelim}$token{$this->_html_rdelim}";
            $repl = $this->_html[$token];
            $text = str_replace($find, $repl, $text);
        } else {
            // replace all tokens
            $regex = "/{$this->_html_ldelim}(.*?){$this->_html_rdelim}/";
            while (preg_match_all($regex, $text, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $val) {
                    $text = str_replace(
                        $val[0],
                        $this->_html[$val[1]],
                        $text
                    );
                }
            }
        }
        
        return $text;
    }
    
    /**
     * 
     * Escapes HTML in source text.
     * 
     * Uses htmlspecialchars() with UTF-8.
     * 
     * @param string $text Source text.
     * 
     * @param int $quotes How to escape quotes; default ENT_COMPAT.
     * 
     * @return string The escaped text.
     * 
     */
    public function escape($text, $quotes = ENT_COMPAT)
    {
        return htmlspecialchars($text, $quotes, 'UTF-8');
    }
    
    /**
     * 
     * Encodes special Markdown characters so they are not recognized
     * when parsing.
     * 
     * @param string $text The source text.
     * 
     * @return string The encoded text.
     * 
     */
    public function encode($text)
    {
        $list = $this->_explodeTags($text);
        
        // reset text and rebuild from the list
        $text = '';
        
        foreach ($list as $item) {
            if ($item[0] == 'tag') {
                $text .= $this->_encode($item[1], true);
            } else {
                $text .= $this->_encode($item[1]);
            }
        }
        
        return $text;
    }
    
    /**
     * 
     * Support method for encode().
     * 
     * @param string $text The source text.
     * 
     * @param bool $in_tag Escaping inside a tag?
     * 
     * @return string The encoded text.
     * 
     */
    public function _encode($text, $in_tag = false)
    {
        if ($in_tag) {
            // inside a tag
            $chars = $this->_esc;
        } else {
            // outside a tag, or between tags
            $chars = $this->_bs_esc;
        }
        
        return str_replace(
            array_keys($chars),
            array_values($chars),
            $text
        );
    }
    
    /**
     * 
     * Un-encodes special Markdown characters.
     * 
     * @param string $text The text with encocded characters.
     * 
     * @return string The un-encoded text.
     * 
     */
    public function unEncode($text)
    {
        // because the bs_esc table uses the same values (just
        // different keys), this will catch both regular and
        // backslashes chars.
        $chars = array_flip($this->_esc);
        $text = str_replace(
            array_keys($chars),
            array_values($chars),
            $text
        );
        
        return $text;
    }
    
    /**
     * 
     * Explodes source text into tags and text
     * 
     * Parameter:  String containing HTML markup.
     * Returns:    An array of the tokens comprising the input
     *             string. Each token is either a tag (possibly with nested,
     *             tags contained therein, such as <a href="<MTFoo>">, or a
     *             run of text between tags. Each element of the array is a
     *             two-element array; the first is either 'tag' or 'text';
     *             the second is the actual value.
     * 
     * 
     * Regular expression derived from the _tokenize() subroutine in 
     * Brad Choate's MTRegex plugin.
     * <http://www.bradchoate.com/past/mtregex.php>
     * 
     */
    protected function _explodeTags($str)
    {
        $index = 0;
        $list = array();

        $match = '(?s:<!(?:--.*?--\s*)+>)|'.    # comment
                 '(?s:<\?.*?\?>)|'.             # processing instruction
                                                # regular tags
                 '(?:<[/!$]?[-a-zA-Z0-9:]+\b(?>[^"\'>]+|"[^"]*"|\'[^\']*\')*>)'; 
                 
        $parts = preg_split("{($match)}", $str, -1, PREG_SPLIT_DELIM_CAPTURE);
        
        foreach ($parts as $part) {
            if (++$index % 2 && $part != '') {
                $list[] = array('text', $part);
            } else {
                $list[] = array('tag', $part);
            }
        }

        return $list;
    }
    
    /**
     * 
     * Returns the number of spaces per tab.
     * 
     * @return int
     * 
     */
    public function getTabWidth()
    {
        return (int) $this->_config['tab_width'];
    }
    
    public function setLink($name, $href, $title = null)
    {
        $this->_link[$name] = array(
            'href'  => $href,
            'title' => $title,
        );
    }
    
    public function getLink($name)
    {
        if (! empty($this->_link[$name])) {
            return $this->_link[$name];
        } else {
            return false;
        }
    }
    
    public function getLinks()
    {
        return $this->_link;
    }
}
?>