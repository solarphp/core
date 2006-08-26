<?php
/**
 * 
 * Abstract authentication adapter.
 * 
 * @category Solar
 * 
 * @package Solar_Auth
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
 * Abstract authentication adapter.
 * 
 * @category Solar
 * 
 * @package Solar_Auth
 * 
 */
abstract class Solar_Auth_Adapter extends Solar_Base {
    
    /**
     * 
     * Information for "common" handle + passwd authentication adapters.
     * 
     * Keys are ...
     * 
     * `source`:
     * (string) The source for auth credentials, 'get'
     * (for [[Solar::get()]] method) or 'post' (for [[Solar::post()]] method).
     * Default is 'post'.
     * 
     * `source_handle`:
     * (string) Username key in the credential array source,
     * default 'handle'.
     * 
     * `source_passwd`:
     * (string) Password key in the credential array source,
     * default 'passwd'.
     * 
     * `source_submit`:
     * (string) Submission key in the credential array source,
     * default 'submit'.
     * 
     * `submit_login`:
     * (string) The submission-key value to indicate a
     * login attempt; default is the 'SUBMIT_LOGIN' locale key value.
     * 
     * `submit_logout`:
     * (string) The submission-key value to indicate a
     * login attempt; default is the 'SUBMIT_LOGOUT' locale key value.
     * 
     * @var array
     * 
     * @see Solar_Auth_Adapter::setCommon()
     * 
     */
    protected $_common = array(
        'source'        => 'post',
        'source_handle' => 'handle',
        'source_passwd' => 'passwd',
        'source_submit' => 'submit',
        'submit_login'  => null,
        'submit_logout' => null,
    );
    
    /**
     * 
     * The unique user handle as derived from the authentication source.
     * 
     * @var string
     * 
     */
    protected $_handle;
    
    /**
     * 
     * The user password.
     * 
     * @var string
     * 
     */
    protected $_passwd;
    
    /**
     * 
     * The user "display name" or "full name" as derived from the
     * authentication source.
     * 
     * @var string
     * 
     */
    protected $_moniker;
    
    /**
     * 
     * The user email address as derived from the authentication source.
     * 
     * @var string
     * 
     */
    protected $_email;
    
    /**
     * 
     * The user URI as derived from the authentication source.
     * 
     * @var string
     * 
     */
    protected $_uri;
    
    /**
     * 
     * The most-recent error code.
     * 
     * @var string
     * 
     */
    protected $_err = null;
    
    /**
     * 
     * Constructor.
     * 
     * @param array $config User-defined configuration values.
     * 
     */
    public function __construct($config = null)
    {
        $this->_common['submit_login']  = $this->locale('SUBMIT_LOGIN');
        $this->_common['submit_logout'] = $this->locale('SUBMIT_LOGOUT');
        parent::__construct($config);
    }
    
    /**
     * 
     * Verifies user credentials for the adapter.
     * 
     * Typical credentials are $this->_handle and $this->_passwd, but
     * single sign-on systems may use different credential sources.
     * 
     * Adapters should set $this->_handle, $this->_email, and
     * $this->_moniker if verfication is successful.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    protected function _verify()
    {
        return false;
    }
    
    /**
     * 
     * Sets information for "common" handle + passwd authentication
     * systems.
     * 
     * @param array $common The common adapter information for source, 
     * source_handle, etc.
     * 
     * @see Solar_Auth_Adapter::$_common
     * 
     */
    public function setCommon($common)
    {
        $base = array(
            'source'        => 'post',
            'source_handle' => 'handle',
            'source_passwd' => 'passwd',
            'source_submit' => 'submit',
            'submit_login'  => $this->locale('SUBMIT_LOGIN'),
            'submit_logout' => $this->locale('SUBMIT_LOGOUT'),
        );
        
        $this->_common = array_merge($base, $common);
        
        // make sure the source is either 'get' or 'post'.
        if ($this->_common['source'] != 'get' && $this->_common['source'] != 'post') {
            // default to post
            $this->_common['source'] = 'post';
        }
    }
    
    /**
     * 
     * Tells if the current page load appears to be the result of
     * an attempt to log in.
     * 
     * @return bool
     * 
     */
    public function isLoginRequest()
    {
        $method = strtolower($this->_common['source']);
        $submit = Solar::$method($this->_common['source_submit']);
        return $submit == $this->_common['submit_login'];
    }
    
    /**
     * 
     * Tells if the current page load appears to be the result of
     * an attempt to log out.
     * 
     * @return bool
     * 
     */
    public function isLogoutRequest()
    {
        $method = strtolower($this->_common['source']);
        $submit = Solar::$method($this->_common['source_submit']);
        return $submit == $this->_common['submit_logout'];
    }
    
    /**
     * 
     * Checks to see if login credentials are valid for the adapter.
     * 
     * @return bool
     * 
     */
    public function isLoginValid()
    {
        $this->_err = null;
        $method = strtolower($this->_common['source']);
        $submit = Solar::$method($this->_common['source_submit']);
        $this->reset();
        $this->_handle = Solar::$method($this->_common['source_handle']);
        $this->_passwd = Solar::$method($this->_common['source_passwd']);
        $result = (bool) $this->_verify();
        if ($result !== true) {
            // not verified, clear out all user data
            $this->reset();
        }
        return $result;
    }
    
    /**
     * 
     * Clears handle, passwd, email, moniker, and uri properties.
     * 
     * @return void
     * 
     */
    public function reset()
    {
        $this->_err     = null;
        $this->_handle  = null;
        $this->_passwd  = null;
        $this->_email   = null;
        $this->_moniker = null;
        $this->_uri     = null;
    }
    
    /**
     * 
     * Returns the most recent error code.
     * 
     * @return string
     * 
     */
    public function getErrCode()
    {
        return $this->_err;
    }
    
    /**
     * 
     * Returns the current user handle.
     * 
     * @return string
     * 
     */
    public function getHandle()
    {
        return $this->_handle;
    }
    
    /**
     * 
     * Returns the current user email address.
     * 
     * @return string
     * 
     */
    public function getEmail()
    {
        return $this->_email;
    }
    
    /**
     * 
     * Returns the current user "full name" or "display name".
     * 
     * @return string
     * 
     */
    public function getMoniker()
    {
        return $this->_moniker;
    }
    
    /**
     * 
     * Returns the current user URI.
     * 
     * @return string
     * 
     */
    public function getUri()
    {
        return $this->_uri;
    }
}
?>