<?php
/**
 * 
 * Generic exception class.
 * 
 * @category Solar
 * 
 * @package Solar
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
 * Generic exception class.
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
     */
    protected $_info = array();
    
    /**
     * 
     * Class where the exception originated.
     * 
     * @var array
     * 
     */
    protected $_class;
    
    /**
     * 
     * Constructor.
     * 
     * @param array $config User-defined configuration with keys
     * for 'class', 'code', 'text', and 'info'.
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
        
        parent::__construct($config['text']);
        $this->code = $config['code'];
        $this->_class = $config['class'];
        $this->_info = (array) $config['info'];
    }
    
    /**
     * 
     * Returnes the exception as a string.
     * 
     * @return void
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
     * Returns user-defined information.
     * 
     * @param string $key A particular info key to return; if empty, returns
     * all info.
     * 
     * @return array
     * 
     */
    final public function getInfo($key = null)
    {
        if (empty($key)) {
            return $this->_info;
        } else {
            return $this->_info[$key];
        }
    }
    
    /**
     * 
     * Returns the class name that threw the exception.
     * 
     * @return string
     * 
     */
    final public function getClass()
    {
        return $this->_class;
    }
    
    /**
     * 
     * Returns the class name and code together.
     * 
     * @return string
     * 
     */
    final public function getClassCode()
    {
        return $this->_class . '::' . $this->code;
    }
}
