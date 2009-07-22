<?php
/**
 * 
 * Helper for building list of invalid messages for a form element.
 * 
 * @category Solar
 * 
 * @package Solar_View_Helper_Form
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: FormText.php 3153 2008-05-05 23:14:16Z pmjones $
 * 
 */
class Solar_View_Helper_FormInvalid extends Solar_View_Helper_FormElement
{
    protected $_Solar_View_Helper_FormInvalid = array(
        'css_class_list' => 'invalid',
        'css_class_item' => 'invalid',
    );
    
    protected $_indent = 0;
    
    /**
     * 
     * Helper for building list of invalid messages for a form element.
     * 
     * @param array $info An array of element information.
     * 
     * @return string The element XHTML.
     * 
     */
    public function formInvalid($info)
    {
        $this->_prepare($info);
        
        if (! $this->_invalid) {
            return;
        }
        
        $html = array();
        
        $attribs = array(
            'class' => $this->_config['css_class_list']
        );
        
        $text = '<ul'
              . $this->_view->attribs($attribs)
              . '>';
              
        $html[] = $this->_indent(0, $text);
        
        $attribs = array(
            'class' => $this->_config['css_class_item']
        );
        
        foreach ((array) $this->_invalid as $item) {
            $text = '<li'
                    . $this->_view->attribs($attribs)
                    . '>'
                    . $this->_view->escape($item)
                    . '</li>';
            
            $html[] = $this->_indent(1, $text);
        }
        
        $html[] = $this->_indent(0, '</ul>');
        
        return implode("\n", $html);
    }
    
    public function setIndent($indent)
    {
        $this->_indent = (int) $indent;
    }
    
    protected function _indent($num, $text)
    {
        $num += $this->_indent;
        return str_pad('', $num * 4) . $text;
    }
}
