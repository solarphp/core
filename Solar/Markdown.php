<?php
/**
 * 
 * Pluggable text-to-XHTML converter based on Markdown.
 * 
 * @category Solar
 * 
 * @package Solar_Markdown
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
 * @todo add (c) notes for Gruber and Fortin
 * 
 * @category Solar
 * 
 * @package Solar_Markdown
 * 
 */
class Solar_Markdown extends Solar_Base {
    
    protected $_count = 0;
    
    protected $_esc = array();
    
    protected $_bs_esc = array();
    
    
    /**
     * 
     * Default configuration for the class.
     * 
     * Keys are:
     * 
     * : plugins : (array) An array of plugins to process, in order.
     * 
     * @var array
     * 
     */
    protected $_Solar_Markdown = array(
        
        'plugins' => array(
            // pre-processing to the source as a whole
            'Solar_Markdown_Plugin_Prefilter',
            
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
            // 'Solar_Markdown_Plugin_EscapeSpecialChars',
            // 'Solar_Markdown_Plugin_Image',
            // 'Solar_Markdown_Plugin_LinkDefined',
            // 'Solar_Markdown_Plugin_LinkInline',
            // 'Solar_Markdown_Plugin_Uri',
            // 'Solar_Markdown_Plugin_EncodeAmpsAndAngles',
            // 'Solar_Markdown_Plugin_ItalicsAndBold',
            // 'Solar_Markdown_Plugin_Break',
        ),
    );
    
    // plugin class => object
    protected $_plugins = array();
    
    // list of block-type plugins
    protected $_blocks = array();
    
    // list of span-type plugins
    protected $_spans = array();
    
    public function __construct($config = null)
    {
        parent::__construct($config);
        $chars = '';
        $config = array('_markdown' => $this);
        foreach ($this->_config['plugins'] as $class) {
            // save the plugin object
            $this->_plugins[$class] = Solar::factory($class, $config);
            
            // is it a block plugin?
            if ($this->_plugins[$class]->isBlock()) {
                $this->_blocks[] = $class;
            }
            
            // is it a span plugin?
            if ($this->_plugins[$class]->isSpan()) {
                $this->_spans[] = $class;
            }
            
            // assemble character list
            $chars .= $this->_plugins[$class]->getChars();
        }
        
        // build the escape tables
        $k = strlen($chars);
        for ($i = 0; $i < $k; ++ $i) {
            $char = $chars[$i];
            // \x1B is ESC
            $this->_esc[$char] = "\x1B$char\x1B";
            $this->_bs_esc["\\$char"] = md5("\x1B\\$char\x1B");
        }
    }
    
    public function transform($text)
    {
        // let each plugin prepare the source text for parsing
        foreach ($this->_plugins as $plugin) {
            $plugin->reset();
            $text = $plugin->prepare($text);
        }
        
        // run the block parsing plugins; these should process spans
        // as needed.
        $text = $this->processBlocks($text);
        
        // let each plugin clean up the rendered source
        foreach ($this->_plugins as $plugin) {
            $text = $plugin->cleanup($text);
        }
        
        // finally, unescape special chars
        return $this->unescapeChars($text);
    }
    
    public function processBlocks($text)
    {
        foreach ($this->_blocks as $class) {
            $text = $this->_plugins[$class]->parse($text);
        }
        return $text;
    }
    
    public function processSpans($text)
    {
        foreach ($this->_spans as $class) {
            $text = $this->_plugins[$class]->parse($text);
        }
        return $text;
    }
    
    public function escapeChars($text)
    {
        $list = $this->explodeTags($text);
        
        $text = '';
        
        foreach ($list as $item) {
            if ($item[0] == 'tag') {
                $text .= $this->_escapeChars($item[1]);
            } else {
                $text .= $this->_escapeChars($item[1], true);
            }
        }
        
        return $text;
    }
    
    public function _escapeChars($text, $backslash = true)
    {
        if ($backslash) {
            $chars = $this->_bs_esc;
        } else {
            $chars = $this->_esc;
        }
        
        return str_replace(
            array_keys($chars),
            array_values($chars),
            $text
        );
    }
    
    public function unescapeChars($text)
    {
        $chars = array_flip($this->_esc);
        $text = str_replace(
            array_keys($chars),
            array_values($chars),
            $text
        );
        
        $chars = array_flip($this->_bs_esc);
        $text = str_replace(
            array_keys($chars),
            array_values($chars),
            $text
        );
        
        return $text;
    }
    
    #
    #   Parameter:  String containing HTML markup.
    #   Returns:    An array of the tokens comprising the input
    #               string. Each token is either a tag (possibly with nested,
    #               tags contained therein, such as <a href="<MTFoo>">, or a
    #               run of text between tags. Each element of the array is a
    #               two-element array; the first is either 'tag' or 'text';
    #               the second is the actual value.
    #
    #
    #   Regular expression derived from the _tokenize() subroutine in 
    #   Brad Choate's MTRegex plugin.
    #   <http://www.bradchoate.com/past/mtregex.php>
    // explodes source text into tags and text
    protected function explodeTags($str)
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
}
?>