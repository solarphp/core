<?php
/**
 * 
 * Abstract helper for form elements.
 * 
 * @category Solar
 * 
 * @package Solar_View
 * 
 * @author Paul M. Jones <pmjones@ciaweb.net>
 * 
 * @license http://www.gnu.org/copyleft/lesser.html LGPL
 * 
 * @version $Id: Solar_View_Helper_FormElement.php 654 2006-01-11 17:10:06Z pmjones $
 * 
 */

/**
 * 
 * Abstract helper for form elements.
 * 
 * @category Solar
 * 
 * @package Solar_View
 * 
 * @author Paul M. Jones <pmjones@ciaweb.net>
 * 
 */
abstract class Solar_View_Helper_FormElement extends Solar_View_Helper {
    protected $_info = array(
        'type'    => '',
        'name'    => '',
        'value'   => '',
        'label'   => '',
        'attribs' => '',
        'options' => array(),
        'require' => false,
        'disable' => false,
        'feedback' => array(),
    );
    
    protected $_keys = array(
        'type',
        'name',
        'value',
        'label',
        'attribs',
        'options',
        'require',
        'disable',
        'feedback',
    );
    
    protected function _info($info)
    {
        $info = array_merge($this->_info, $info);
        
        settype($info['type'], 'string');
        settype($info['name'], 'string');
        settype($info['label'], 'string');
        settype($info['attribs'], 'array');
        settype($info['options'], 'array');
        settype($info['require'], 'bool');
        settype($info['disable'], 'bool');
        settype($info['feedback'], 'array');
        
        
        foreach ($this->_keys as $key) {
            unset($info['attribs'][$key]);
        }
        
        return $info;
    }
}
?>