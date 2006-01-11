<?php

/**
 * 
 * Exception class.
 * 
 * @category Solar
 * 
 * @package Solar
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license LGPL
 * 
 * @version $Id$
 * 
 */

/**
 * 
 * Exception class.
 * 
 * @category Solar
 * 
 * @package Solar
 * 
 * @todo Make this object observable?
 * 
 */

class Solar_Exception extends Exception {
    
    /**
     * 
     * User-defined information array.
     * 
     * @var array
     * 
     * @access protected
     * 
     */
    protected $_info = array();
    
    /**
     * 
     * User-defined information array.
     * 
     * @var array
     * 
     * @access protected
     * 
     */
    protected $_class;
    
    /**
     * 
     * Constructor.
     * 
     */
    public function __construct($config = null)
    {
        $default = array(
            'class' => '',
            'code'  => '',
            'text'  => '',
            'info'  => array();
        )
        $config = array_merge($default, (array) $config);
        extract($config);
        parent::__construct($text, $code);
        $this->_class = $class;
        $this->_info = (array) $info;
    }
    
    /**
     * 
     * Gets user-defined information.
     * 
     * @var array
     * 
     * @access protected
     * 
     */
    final public function getInfo()
    {
        return $this->_info;
    }
    
    /**
     * 
     * Gets the class that threw the exception.
     * 
     * @var array
     * 
     * @access protected
     * 
     */
    final public function getClass()
    {
        return $this->_class;
    }
}
?>