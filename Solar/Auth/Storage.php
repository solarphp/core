<?php
/**
 * 
 * Abstract Authentication Storage.
 * 
 * @category Solar
 * 
 * @package Solar_Auth
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Adapter.php 4533 2010-04-23 16:35:15Z pmjones $
 * 
 */
abstract class Solar_Auth_Storage extends Solar_Base {

    /**
     * 
     * The user-provided plaintext handle, if any.
     * 
     * @var string
     * 
     */
    protected $_handle;
    
    /**
     * 
     * The user-provided plaintext password, if any.
     * 
     * @var string
     * 
     */
    protected $_passwd;

    /**
     *
     */
    public function validateCredentials($credentials) {
        $this->_handle = $credentials['handle'];
        $this->_passwd = $credentials['passwd'];

        return $this->_processLogin();
    }
    
}