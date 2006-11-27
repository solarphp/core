<?php
/**
 * 
 * Class for checking user authentication credentials.
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
 * Class for checking user authentication credentials.
 * 
 * @category Solar
 * 
 * @package Solar_Auth
 * 
 */
class Solar_Auth extends Solar_Base {
    
    /**
     * 
     * User-supplied configuration values.
     * 
     * Keys are ...
     * 
     * `adapter`
     * : (string) The adapter class, e.g. 'Solar_Auth_Adapter_File'.
     * 
     * `config`
     * : (array) Construction-time config keys to pass to the adapter
     *   to override Solar.config.php values.  Default is null.
     * 
     * `expire`
     * : (int) Authentication lifetime in seconds; zero is
     *   forever.  Default is 14400 (4 hours).
     * 
     * `idle`
     * : (int) Maximum allowed idle time in seconds; zero is
     *   forever.  Default is 1800 (30 minutes).
     * 
     * `allow`
     * : (bool) Whether or not to allow login/logout attempts.
     * 
     * `source`
     * : (string) The source for auth credentials, 'get' (via the
     *   for GET request vars) or 'post' (via the POST request vars).
     *   Default is 'post'.
     * 
     * `source_handle`
     * : (string) Username key in the credential array source,
     *   default 'handle'.
     * 
     * `source_passwd`
     * : (string) Password key in the credential array source,
     *   default 'passwd'.
     * 
     * `source_submit`
     * : (string) Submission key in the credential array source,
     *   default 'submit'.
     * 
     * `submit_login`
     * : (string) The submission-key value to indicate a
     *   login attempt; default is the 'SUBMIT_LOGIN' locale key value.
     * 
     * `submit_logout`
     * : (string) The submission-key value to indicate a
     *   login attempt; default is the 'SUBMIT_LOGOUT' locale key value.
     * 
     * @var array
     * 
     */
    protected $_Solar_Auth = array(
        'adapter'       => 'Solar_Auth_Adapter_None',
        'expire'        => 14400,
        'idle'          => 1800,
        'allow'         => true,
        'source'        => 'post',
        'source_handle' => 'handle',
        'source_passwd' => 'passwd',
        'source_submit' => 'submit',
        'submit_login'  => null,
        'submit_logout' => null,
    );
    
    /**
     * 
     * Factory method for returning adapters.
     * 
     */
    public function factory()
    {
        // bring in the config and get the adapter class.
        $config = $this->_config;
        $class = $config['adapter'];
        unset($config['adapter']);
        
        // deprecated: support a 'config' key for the adapter configs.
        // this was needed for facades, but is not needed for factories.
        if (isset($config['config'])) {
            $tmp = $config['config'];
            unset($config['config']);
            $config = array_merge($config, (array) $tmp);
        }
        
        return Solar::factory($class, $config);
    }
}
?>