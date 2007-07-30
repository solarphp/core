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
     * `P3P`
     * : Compact [Platform for Privacy Preferences][] policy. Default is
     *   'CP="CAO COR CURa ADMa DEVa TAIa OUR BUS IND UNI COM NAV INT STA"',
     *   which translates to:
     *   
     *   *CAO* _ACCESS Element_: the ability of the individual to view 
     *   identified data and address questions or concerns to the service 
     *   provider. CAO is short for 'contact-and-other', meaning _Identified 
     *   Contact Information and Other Identified Data: access is given to 
     *   identified online and physical contact information as well as to 
     *   certain other identified data._
     * 
     *   *COR* _REMEDIES Element_: Remedies in case a policy breach occurs.
     *   COR is short for 'correct', meaning _Errors or wrongful actions 
     *   arising in connection with the privacy policy will be remedied by 
     *   the service._
     * 
     *   *CURa*
     *   *ADMa*
     *   *DEVa*
     *   *TAIa* _PURPOSE Elements_: Purposes for data processing relevant to 
     *   the Web. The 'a' following each code indicates 'always', meaning 
     *   the site provides no opt-in/opt-out choices for the information 
     *   collected in the _CATEGORIES_ Elements.
     * 
     *   CUR is short for 'current', meaning _Completion and Support of 
     *   Activity For Which Data Was Provided: Information may be used by the 
     *   service provider to complete the activity for which it was provided, 
     *   whether a one-time activity such as returning the results from a Web 
     *   search, forwarding an email message, or placing an order; or a 
     *   recurring activity such as providing a subscription service, or 
     *   allowing access to an online address book or electronic wallet._
     * 
     *   ADM is short for 'admin', meaning _Web Site and System 
     *   Administration: Information may be used for the technical support of 
     *   the Web site and its computer system. This would include processing 
     *   computer account information, information used in the course of 
     *   securing and maintaining the site, and verification of Web site 
     *   activity by the site or its agents._
     * 
     *   DEV is short for 'develop', meaning _Research and Development: 
     *   Information may be used to enhance, evaluate, or otherwise review 
     *   the site, service, product, or market. This does not include personal 
     *   information used to tailor or modify the content to the specific 
     *   individual nor information used to evaluate, target, profile or 
     *   contact the individual._
     * 
     *   TAI is short for 'tailoring', meaning _One-time Tailoring: 
     *   Information may be used to tailor or modify content or design of the 
     *   site where the information is used only for a single visit to the 
     *   site and not used for any kind of future customization. For example, 
     *   an online store might suggest other items a visitor may wish to 
     *   purchase based on the items he has already placed in his shopping 
     *   basket._
     *
     *   *OUR* _RECIPIENT Element_: The legal entity, or domain, beyond the 
     *   service provider and its agents where data may be distributed.
     *   OUR is short for 'ourselves', meaning _Ourselves and/or entities 
     *   acting as our agents or entities for whom we are acting as an agent: 
     *   An agent in this instance is defined as a third party that processes 
     *   data only on behalf of the service provider for the completion of the 
     *   stated purposes. (e.g., the service provider and its printing bureau 
     *   which prints address labels and does nothing further with the 
     *   information.)_
     * 
     *   *BUS*
     *   *IND* _RETENTION Elements_: The type of retention policy in effect.
     *   BUS is short for 'business-practices', meaning _Determined by 
     *   service provider's business practice: Information is retained under 
     *   a service provider's stated business practices._
     * 
     *   IND is short for 'indefinitely', meaning _Information is retained
     *   for an indeterminate period of time._
     *   
     *   *UNI*
     *   *COM*
     *   *NAV*
     *   *INT*
     *   *STA* _CATEGORIES Elements_: Elements inside data elements that 
     *   provide hints to users and user agents as to the intended uses of 
     *   the data.
     * 
     *   UNI is short for 'uniqeid', meaning _Unique Identifiers: 
     *   Non-financial identifiers, excluding government-issued identifiers, 
     *   issued for purposes of consistently identifying or recognizing the 
     *   individual. These include identifiers issued by a Web site or 
     *   service._
     * 
     *   COM is short for 'computer', meaning _Computer Information: 
     *   Information about the computer system that the individual is using 
     *   to access the network -- such as the IP number, domain name, browser 
     *   type or operating system._
     * 
     *   NAV is short for 'navigation', meaning _Navigation and Click-stream 
     *   Data: Data passively generated by browsing the Web site -- such as 
     *   which pages are visited, and how long users stay on each page._
     * 
     *   INT is short for 'interactive', meaning _Interactive Data: Data 
     *   actively generated from or reflecting explicit interactions with a 
     *   service provider through its site -- such as queries to a search 
     *   engine, or logs of account activity._
     * 
     *   STA is short for 'state', meaning _State Management Mechanisms: 
     *   Mechanisms for maintaining a stateful session with a user or 
     *   automatically recognizing users who have visited a particular site 
     *   or accessed particular content previously -- such as HTTP cookies._
     *   
     *   Please refer to the W3C P3P specification for more information 
     *   on customizing this default policy. A compact policy delivered 
     *   in an HTTP header is only part of a complete P3P implementation.
     * 
     *   [Platform for Privacy Preferences]: http://www.w3.org/TR/P3P/
     * 
     * @var array
     * 
     */
    protected $_Solar_Session = array(
        'class' => 'Solar',
        'P3P'   => 'CP="CAO COR CURa ADMa DEVa TAIa OUR BUS IND UNI COM NAV INT STA"',
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
     * Starts the session if one does not exist, but only if we're not at the
     * command line.
     * 
     * Automatically sends a P3P header if one is defined (and it is, by 
     * default).
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
            if ($this->_config['P3P']) {
                header('P3P: ' . $this->_config['P3P']);
            }
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
     * @todo Mike Naberezny notes a possible issue with AJAX requests:
     * 
     *     // If this is an AJAX request, don't clear the flash.
     *     $headers = getallheaders();
     *     if (isset($headers['X-Requested-With']) &&
     *         stripos($headers['X-Requested-With'], 'xmlhttprequest') !== false) {
     *         // leave alone
     *         return;
     *     }
     * 
     * Would need to have Solar_Request access for this to work like the rest
     * of Solar does.
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
