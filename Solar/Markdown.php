<?php
/**
 * 
 * Pluggable text-to-XHTML converter based on Mardown rules.
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
 * Pluggable text-to-XHTML converter based on Mardown rules.
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
     * : rules : (array) An array of rules to process, in order.
     * 
     * @var array
     * 
     */
    protected $_Solar_Markdown = array(
        'rules'   => array(
            
            // pre-filters
            'Solar_Markdown_Plugin_Prefilter',
            
            // blocks
            'Solar_Markdown_Plugin_HeaderSetext',
            'Solar_Markdown_Plugin_HeaderAtx',
            'Solar_Markdown_Plugin_HorizRule',
            // 'Solar_Markdown_Plugin_List',
            // 'Solar_Markdown_Plugin_CodeBlock',
            // 'Solar_Markdown_Plugin_Blockquote',
            // 'Solar_Markdown_Plugin_Html',
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
    
    protected $_rules = array();
    
    public function __construct($config = null)
    {
        parent::__construct($config);
        foreach ($this->_config['rules'] as $class)
            if (! empty($this->_config['setup'][$class]) {
                $config = $this->_config['setup'][$class];
            } else {
                $config = null;
            }
            $this->_rules[$class] = Solar::factory($class, $config);
        }
    }
    
    public function transform($text)
    {
        foreach ($this->_rules as $rule) {
            $text = $rule->filter($text);
        }
        foreach ($this->_rules as $rule) {
            $text = $rule->parse($text);
        }
        
        foreach ($this->_rules as $rule) {
            $text = $rule->render($text);
        }
        
        return $text;
    }
}
?>