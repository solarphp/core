<?php
/**
 * 
 * Helper for a <label> tag on a form element.
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
class Solar_View_Helper_FormLabel extends Solar_View_Helper_FormElement
{
    protected $_Solar_View_Helper_FormLabel = array(
        'css_class_require' => 'require',
        'css_class_invalid' => 'invalid',
    );
    
    /**
     * 
     * Generates a <label> tag for a form element.
     * 
     * @param array $info An array of element information.
     * 
     * @return string The element XHTML.
     * 
     */
    public function formLabel($info)
    {
        $this->_prepare($info);
        
        if (! $this->_label) {
            return;
        }
        
        // only use certain attribs
        $attribs = $this->_getAttribs();
        
        // build the label, and done
        return '<label' . $this->_view->attribs($attribs) . '>'
             . $this->_view->getText($this->_label)
             . '</label>';
    }
    
    protected function _getAttribs()
    {
        return array(
            'for'   => $this->_getFor(),
            'class' => $this->_getClass(),
        );
    }
    
    protected function _getFor()
    {
        if (! empty($this->_attribs['for'])) {
            return $this->_attribs['for'];
        }
        
        if (! empty($this->_attribs['id'])) {
            return $this->_attribs['id'];
        }
    }
    
    protected function _getClass()
    {
        if (! empty($this->_attribs['class'])) {
            return (array) $this->_attribs['class'];
        }
        
        $list = array();
        
        if ($this->_require) {
            $list[] = $this->_config['css_class_require'];
        }
        
        if ($this->_invalid) {
            $list[] = $this->_config['css_class_invalid'];
        }
        
        return $list;
    }
}
