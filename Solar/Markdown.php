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
        
        // pre-processing to the source as a whole
        'plugins' => array(
            'Solar_Markdown_Plugin_Prefilter',
            'Solar_Markdown_Plugin_Html',
            'Solar_Markdown_Plugin_HeaderSetext',
            'Solar_Markdown_Plugin_HeaderAtx',
            'Solar_Markdown_Plugin_HorizRule',
            'Solar_Markdown_Plugin_List',
            'Solar_Markdown_Plugin_CodeBlock',
            'Solar_Markdown_Plugin_BlockQuote',
            // 'Solar_Markdown_Plugin_Paragraphs',
            
            // spans
            // 'Solar_Markdown_Plugin_CodeSpan',
            // 'Solar_Markdown_Plugin_EscapeSpecialChars',
            // 'Solar_Markdown_Plugin_Image',
            // 'Solar_Markdown_Plugin_LinkDefined',
            // 'Solar_Markdown_Plugin_LinkInline',
            // 'Solar_Markdown_Plugin_Uri',
            // 'Solar_Markdown_Plugin_EncodeAmpsAndAngles',
            // 'Solar_Markdown_Plugin_ItalicsAndBold',
            // 'Solar_Markdown_Plugin_Break',
            
            // post-filters
            // 'Solar_Markdown_Plugin_Postfilter',
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
        }
    }
    
    public function transform($text)
    {
        // let each plugin prepare the source text for parsing
        foreach ($this->_plugins as $plugin) {
            $text = $plugin->prepare($text);
        }
        
        // run the block parsing plugins; these should process spans
        // as needed.
        $text = $this->processBlocks($text);
        
        // let each plugin clean up the source after parsing
        foreach ($this->_plugins as $plugin) {
            $text = $plugin->cleanup($text);
        }
        
        // render the tokens from each plugin back into the text
        foreach ($this->_plugins as $plugin) {
            $text = $plugin->render($text);
        }
        
        return $text;
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
}
?>