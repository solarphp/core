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
     * Class where the exception originated.
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
            'info'  => array(),
        );
        $config = array_merge($default, (array) $config);
        extract($config);
        parent::__construct($text);
        $this->code = $code;
        $this->_class = $class;
        $this->_info = (array) $info;
    }
    
    /**
     * 
     * Custom string output.
     * 
     */
    public function __toString()
    {
        return "exception '" . get_class($this) . "'\n"
             . "class::code '" . $this->_class . "::" . $this->code . "' \n"
             . "with message '" . $this->message . "' \n"
             . "information " . var_export($this->_info, true) . " \n"
             . "Stack trace:\n"
             . "  " . str_replace("\n", "\n  ", $this->getTraceAsString());
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
    
    /**
     * 
     * Gets the class and code together.
     * 
     * @var array
     * 
     * @access protected
     * 
     */
    final public function getClassCode()
    {
        return $this->_class . '::' . $this->code;
    }
    
    
}
?>