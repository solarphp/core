<?php

/**
* 
* Class for checking user authentication credentials.
* 
* @category Solar
* 
* @package Solar_User
* 
* @subpackage Solar_User_Auth
* 
* @author Paul M. Jones <pmjones@solarphp.com>
* 
* @license LGPL
* 
* @version $Id: Auth.php,v 1.19 2005/02/08 01:42:27 pmjones Exp $
* 
*/

/**
* 
* Class for checking user authentication credentials.
* 
* Usage:
*
* <code type="php">
*
* $opts = array(
*     'class'  => 'Solar_User_Auth_File',
*     'options' => array('file' => '/conf/htpasswd')
* );
* 
* $auth = Solar::object('Solar_User_Auth', $opts);
* $auth->start();
* 
* Solar::dump($auth->statusCode);
* 
* </code type="php>
* 
* @category Solar
* 
* @package Solar_User
* 
* @subpackage Solar_User_Auth
* 
*/

class Solar_User_Auth extends Solar_Base {
	

	/**
	* 
	* User-supplied configuration values.
	* 
	* Keys are:
	* 
	* class => (string) The driver class, e.g. 'Solar_User_Auth_File'.
	* 
	* options => (array) Options for the authentication driver.
	* 
	* expire => (int) Authentication lifetime in seconds; zero is forever.
	* 
	* idle => (int) Maximum allowed idle time in seconds; zero is forever.
	* 
	* allow => (bool) Whether or not to allow automatic login/logout.
	* 
	* post_action => (string) Login/logout action key in $_POST array.
	* 
	* post_username => (string) Password key in $_POST array.
	* 
	* post_password => (string) Username key in $_POST array.
	* 
	* action_login => (string) The $_POST['action'] value to indicate a login attempt.
	* 
	* action_logout => (string) The $postAction value to indicate a logout attempt.
	* 
	* @access protected
	* 
	* @var array
	* 
	*/
	
	public $config = array(
		'locale'        => 'Solar/User/Locale/',
		'class'         => null,
		'options'       => null,
		'expire'        => 0,
		'idle'          => 0,
		'allow'         => true,
		'post_action'   => 'action',
		'post_password' => 'password',
		'post_username' => 'username',
		'action_login'  => 'login',
		'action_logout' => 'logout',
	);
	
	
	/**
	* 
	* A driver object instance.
	* 
	* @access protected
	* 
	* @var object
	* 
	*/
	
	protected $driver = null;
	
	
	/**
	* 
	* Whether or not to allow authentication actions (login/logout).
	* 
	* @access private
	* 
	* @var bool
	* 
	*/
	
	public $allow = true;
	
	
	/**
	* 
	* Convenience reference to $_SESSION['Solar_User_Auth']['lastActive'].
	* 
	* This is the Unix time at which the authenticated username was last
	* valid().
	* 
	* @access public
	* 
	* @var int
	* 
	* @see valid()
	* 
	*/
	
	public $lastActive;
	
	
	/**
	* 
	* Convenience reference to $_SESSION['Solar_User_Auth']['loginTime'].
	* 
	* This is the Unix time at which the username was authenticated.
	* 
	* @access public
	* 
	* @var int
	* 
	*/
	
	public $loginTime;
	
	
	/**
	* 
	* Convenience reference to $_SESSION['Solar_User_Auth']['statusCode'].
	* 
	* This is the status code of the current authentication; it maps to a
	* class constant ('VALID', 'IDLED', etc).
	* 
	* @access public
	* 
	* @var int
	* 
	*/
	
	public $statusCode;
	
	
	/**
	* 
	* Convenience reference to $_SESSION['Solar_User_Auth']['statusText'].
	* 
	* This is message text related to the status code of the current
	* authentication.
	* 
	* @access public
	* 
	* @var int
	* 
	*/
	
	public $statusText;
	
	
	/**
	* 
	* Convenience reference to $_SESSION['Solar_User_Auth']['username'].
	* 
	* This is the currently authenticated username.
	* 
	* @access public
	* 
	* @var string
	* 
	*/
	
	public $username;
	
	
	// ----------------------------------------------------------------
	// 
	// Public methods.
	// 
	// ----------------------------------------------------------------
	
	
	/**
	* 
	* Constructor to set up the storage driver.
	*
	* @access public
	* 
	* @param array $conf The config options.
	* 
	* @return object
	* 
	*/
	
	public function __construct($conf = null)
	{
		// basic config option settings
		parent::__construct($conf);
		
		// instantiate a driver object
		$this->driver = Solar::object(
			$this->config['class'],
			$this->config['options']
		);
	}
	
	
	/**
	* 
	* Start a session with authentication.
	* 
	* @access public
	* 
	* @return void
	* 
	*/
	
	public function start()
	{
		// Start the session; suppress errors if already started.
		@session_start();
		
		// initialize the session array if it does not exist
		if (! isset($_SESSION['Solar_User_Auth']) ||
			! is_array($_SESSION['Solar_User_Auth'])) {
			
			$_SESSION['Solar_User_Auth'] = array(
				'statusCode' => 'ANON',
				'statusText' => $this->locale('AUTH_ANON'),
				'username' => null,
				'loginTime' => null,
				'lastActive' => null
			);
		}
		
		// add convenience references to the session array keys
		$this->statusCode =& $_SESSION['Solar_User_Auth']['statusCode'];
		$this->statusText =& $_SESSION['Solar_User_Auth']['statusText'];
		$this->username   =& $_SESSION['Solar_User_Auth']['username'];
		$this->loginTime  =& $_SESSION['Solar_User_Auth']['loginTime'];
		$this->lastActive =& $_SESSION['Solar_User_Auth']['lastActive'];
		
		// update any current authentication (including idle and expire).
		$this->valid();
		
		// are we allowing authentication actions?
		if ($this->allow) {
		
			// get the action and credentials
			$action   = Solar::post($this->config['post_action']);
			$username = Solar::post($this->config['post_username']);
			$password = Solar::post($this->config['post_password']);
			
			// check for a login request.
			if ($action == $this->config['action_login']) {
				
				// check the storage driver to see if the username
				// and password credentials are valid.
				$result = $this->driver->valid($username, $password);
				
				// were the credentials valid? (check if exactly boolean
				// true, as it may have returned a Solar error).
				if ($result === true) {
					// login attempt succeeded.
					$this->statusCode = 'VALID';
					$this->statusText = $this->locale('AUTH_VALID');
					$this->username = $username;
					$this->loginTime = time();
					$this->lastActive = time();
				} else {
					// login attempt failed.
					$this->reset('WRONG');
				}
			}
			
			// check for a logout request.
			if ($action == $this->config['action_logout']) {
				// reset the authentication data
				$this->reset('LOGOUT');
			}
		}
	}
	
	
	/**
	* 
	* Revalidates any current authentication and updates idle time.
	* 
	* Note that if your script runs more than 1 second, it is possible
	* that multiple calls to valid() may result in the authentication
	* expiring in the middle of the script.  As such, if you only need
	* to check that the user is logged in, look at the value of
	* $this->statusCode.
	* 
	* @access public
	* 
	* @return boolean Whether or not authentication is still valid.
	* 
	*/
	
	public function valid()
	{
		// is the current user already authenticated?
		if ($this->statusCode == 'VALID') {
			
			// Check if session authentication has expired
			$tmp = $this->loginTime + $this->config['expire'];
			if ($this->config['expire'] > 0 && $tmp < time()) {
				// past the expiration time
				$this->reset('EXPIRED');
				return false;
			}
	
			// Check if session has been idle for too long
			$tmp = $this->lastActive + $this->config['idle'];
			if ($this->config['idle'] > 0 && $tmp < time()) {
				// past the idle time
				$this->reset('IDLED');
				return false;
			}
			
			// not expired, not idled, so update the lastActive time
			$this->lastActive = time();
			return true;
			
		} else {
			// current user is not already authenticated.
			return false;
		}
	}
	
	
	/**
	* 
	* Resets any authentication data in the session.
	* 
	* Typically used for idling, expiration, and logout.
	*
	* @access public
	* 
	* @param int $statusCode A Solar_User_Auth status constant; default is
	* 'ANON'.
	* 
	* @return void
	* 
	*/
	
	public function reset($statusCode = 'ANON')
	{
		$statusCode = strtoupper($statusCode);
		$this->statusCode = $statusCode;
		$this->statusText = $this->locale("AUTH_$statusCode");
		$this->username = null;
		$this->loginTime = null;
		$this->lastActive = null;
	}
}
?>