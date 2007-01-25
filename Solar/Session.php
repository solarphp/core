<?php
/**
 * 
 * Class for working with the $_SESSION array, including read-once
 * flashes.
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
 * Class for working with the $_SESSION array, including read-once
 * flashes.
 * 
 * On instantiation, starts a session if one has not yet been started.
 * 
 * Instantiate this once for each class that wants access to $_SESSION
 * values.  It automatically segments $_SESSION by class name, so be 
 * sure to use setClass() (or the 'class' config key) to identify the
 * segment properly.
 * 
 * A "flash" is a session value that propagates only until it is read,
 * at which time it is removed from the session.  Taken from ideas 
 * popularized by Ruby on Rails, this is useful for forwarding
 * information and messages between page loads without using GET vars
 * or cookies.
 * 
 * @category Solar
 * 
 * @package Solar
 * 
 */
class Solar_Session extends Solar_Base {
    
    /**
     * 
     * User-defined configuration values.
     * 
     * Keys are ...
     * 
     * `class`
     * : Store values in this top-level key in $_SESSION.  Default is
     *   'Solar'.
     * 
     * @var array
     * 
     */
    protected $_Solar_Session = array(
        'class' => 'Solar',
    );
    
    /**
     * 
     * The top-level $_SESSION class key for segmenting values.
     * 
     * @var array
     * 
     */
    protected $_class = 'Solar';
    
    /**
     * 
     * Array of read-once "flash" keys and values.
     * 
     * Convenience reference to $_SESSION['Solar_Session']['flash'][$this->_class].
     * 
     * @var array
     * 
     */
    public $flash;
    
    /**
     * 
     * Array of "normal" session keys and values.
     * 
     * Convenience reference to $_SESSION[$this->_class].
     * 
     * @var array
     * 
     */
    public $store;
    
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
        
        // start a session if one does not exist, but not if we're at
        // the command line.
        if (session_id() === '' && PHP_SAPI != 'cli') {
            session_start();
        }
        
        $this->_class = trim($this->_config['class']);
        if ($this->_class == '') {
            $this->_class = 'Solar';
        }
        
        $this->setClass($this->_class);
    }
    
    /**
     * 
     * Sets the class segment for $_SESSION.
     * 
     * @param string $class The class name to segment by.
     * 
     * @return void
     * 
     */
    public function setClass($class)
    {
        $this->_class = $class;
        
        // set up the value store
        if (empty($_SESSION[$this->_class])) {
            $_SESSION[$this->_class] = array();
        }
        $this->store =& $_SESSION[$this->_class];
        
        // set up the flash store
        if (empty($_SESSION['Solar_Session']['flash'][$this->_class])) {
            $_SESSION['Solar_Session']['flash'][$this->_class] = array();
        }
        $this->flash =& $_SESSION['Solar_Session']['flash'][$this->_class];
    }
    
    /**
     * 
     * Sets a normal value by key.
     * 
     * @param string $key The data key.
     * 
     * @param mixed $val The value for the key; previous values will
     * be overwritten.
     * 
     * @return void
     * 
     */
    public function set($key, $val)
    {
        $this->store[$key] = $val;
    }
    
    /**
     * 
     * Appends a normal value to a key.
     * 
     * @param string $key The data key.
     * 
     * @param mixed $val The value to add to the key; this will
     * result in the value becoming an array.
     * 
     * @return void
     * 
     */
    public function add($key, $val)
    {
        if (! isset($this->store[$key])) {
            $this->store[$key] = array();
        }
        
        if (! is_array($this->store[$key])) {
            settype($this->store[$key], 'array');
        }
        
        $this->store[$key][] = $val;
    }
    
    /**
     * 
     * Gets a normal value by key, or an alternative default value if
     * the key does not exist.
     * 
     * @param string $key The data key.
     * 
     * @param mixed $val If key does not exist, returns this value
     * instead.  Default null.
     * 
     * @return mixed The value.
     * 
     */
    public function get($key, $val = null)
    {
        if (array_key_exists($key, $this->store)) {
            $val = $this->store[$key];
        }
        return $val;
    }
    
    /**
     * 
     * Resets (clears) all normal keys and values.
     * 
     * @return void
     * 
     */
    public function reset()
    {
        $this->store = array();
    }
    
    /**
     * 
     * Sets a flash value by key.
     * 
     * @param string $key The flash key.
     * 
     * @param mixed $val The value for the key; previous values will
     * be overwritten.
     * 
     * @return void
     * 
     */
    public function setFlash($key, $val)
    {
        $this->flash[$key] = $val;
    }
    
    /**
     * 
     * Appends a flash value to a key.
     * 
     * @param string $key The flash key.
     * 
     * @param mixed $val The flash value to add to the key; this will
     * result in the flash becoming an array.
     * 
     * @return void
     * 
     */
    public function addFlash($key, $val)
    {
        if (! isset($this->flash[$key])) {
            $this->flash[$key] = array();
        }
        
        if (! is_array($this->flash[$key])) {
            settype($this->flash[$key], 'array');
        }
        
        $this->flash[$key][] = $val;
    }
    
    /**
     * 
     * Gets a flash value by key, thereby removing the value.
     * 
     * @param string $key The flash key.
     * 
     * @param mixed $val If key does not exist, returns this value
     * instead.  Default null.
     * 
     * @return mixed The flash value.
     * 
     */
    public function getFlash($key, $val = null)
    {
        if (array_key_exists($key, $this->flash)) {
            $val = $this->flash[$key];
            unset($this->flash[$key]);
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
    public function resetFlash()
    {
        $this->flash = array();
    }
    
    /**
     * 
     * Resets both "normal" and "flash" values.
     * 
     * @return void
     * 
     */
    public function resetAll()
    {
        $this->reset();
        $this->resetFlash();
    }
    
    /**
     * 
     * Regenerates the session ID and deletes the previous session store.
     * 
     * Use this every time there is a privilege change.
     * 
     * @return void
     * 
     * @see [[php::session_regenerate_id()]]
     * 
     */
    public function regenerateId()
    {
        if (! headers_sent()) {
            session_regenerate_id(true);
        }
    }
}
