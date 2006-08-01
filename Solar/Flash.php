<?php
/**
 * 
 * Class for working with read-once flashes.
 * 
 * @category Solar
 * 
 * @package Solar_Flash
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
 * Class for working with read-once flashes.
 * 
 * Taken from ideas popularized by Ruby on Rails, a "flash" is a session
 * value that propagates only until it is read, at which time it
 * is removed from the session.  This is useful for forwarding
 * information and messages between page loads without using get-vars
 * or cookies.
 * 
 * @category Solar
 * 
 * @package Solar_Access
 * 
 */
class Solar_Flash extends Solar_Base {
    
    /**
     * 
     * User-defined configuration values.
     * 
     * Keys are:
     * 
     * : \\class\\ : Flash messages are for this class.  Default is
     *   'Solar'.
     * 
     * @var array
     * 
     */
    protected $_Solar_Flash = array(
        'class' => 'Solar',
    );
    
    /**
     * 
     * The class for flashes.
     * 
     * @var array
     * 
     */
    protected $_class = 'Solar';
    
    /**
     * 
     * Convenience reference to $_SESSION['Solar_Flash'][$this->_class].
     * 
     * @var array
     * 
     */
    public $list;
    
    /**
     * 
     * Constructor.
     * 
     * @param array $config User-defined configuration values.
     * 
     */
    public function __construct($config = null)
    {
        parent::__construct($config);
        
        if (session_id() === '') {
            session_start();
        }
        
        $this->_class = trim($this->_config['class']);
        if ($this->_class == '') {
            $this->_class = 'Solar';
        }
        
        if (! isset($_SESSION['Solar_Flash'][$this->_class])) {
            $_SESSION['Solar_Flash'][$this->_class] = array();
        }
        
        $this->list =& $_SESSION['Solar_Flash'][$this->_class];
    }
    
    /**
     * 
     * Sets a "read-once" session value for a class and key.
     * 
     * @param string $key The specific type of information for the class.
     * 
     * @param mixed $val The value for the key; previous values will
     * be overwritten.
     * 
     * @return void
     * 
     */
    public function set($key, $val)
    {
        $this->list[$key] = $val;
    }
    
    /**
     * 
     * Appends a "read-once" session value to a class and key.
     * 
     * @param string $key The specific type of information for the class.
     * 
     * @param mixed $val The flash value to add to the key; this will
     * result in the flash becoming an array.
     * 
     * @return void
     * 
     */
    public function add($key, $val)
    {
        if (! isset($this->list[$key])) {
            $this->list[$key] = array();
        }
        
        if (! is_array($this->list[$key])) {
            settype($this->list[$key], 'array');
        }
        
        $this->list[$key][] = $val;
    }
    
    /**
     * 
     * Retrieves a "read-once" session value, thereby removing the value.
     * 
     * @param string $key The specific type of information for the class.
     * 
     * @param mixed $val If the class and key do not exist, return
     * this value.  Default null.
     * 
     * @return mixed The "read-once" value.
     * 
     */
    public function get($key, $val = null)
    {
        if (isset($this->list[$key])) {
            $val = $this->list[$key];
            unset($this->list[$key]);
        }
        return $val;
    }
    
    /**
     * 
     * Resets (clears) all flash keys and values.
     * 
     * @return void
     * 
     */
    public function reset()
    {
        $this->list = array();
    }
}
?>