<?php
/**
 * 
 * Authenticate against nothing; defaults all authentication to "failed."
 * 
 * @category Solar
 * 
 * @package Solar_User
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
 * Authenticate against nothing; defaults all authentication to "failed."
 * 
 * @category Solar
 * 
 * @package Solar_User
 * 
 */
class Solar_User_Auth_None extends Solar_Base {
    
    /**
     * 
     * Validate a username and password.  Always fails.
     *
     * @param string $user Username to authenticate.
     * 
     * @param string $pass The plain-text password to use.
     * 
     * @return boolean True on success, false on failure.
     * 
     */
    public function valid($handle, $passwd)
    {
        return false;
    }
}
?>