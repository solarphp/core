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
 * @version $Id$
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
        'options',
        'require',
        'disable',
        'feedback',
        'attribs', // last, so that all attribs are unset properly
    );
    
    protected $_type;
    
    protected $_name;
    
    protected $_value;
    
    protected $_label;
    
    protected $_attribs;
    
    protected $_options;
    
    protected $_require;
    
    protected $_disable;
    
    protected $_feedback;
    
    protected function _prepare($info)
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
            $prop = "_$key";
            $this->$prop = $info[$key];
        }
    }
}
?>