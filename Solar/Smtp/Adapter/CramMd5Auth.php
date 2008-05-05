<?php
/**
 * 
 * SMTP adapter with "cram-md5" authentication at connection time.
 * 
 * @category Solar
 * 
 * @package Solar_Smtp
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id$
 * 
 */
class Solar_Smtp_Adapter_CramMd5Auth extends Solar_Smtp_Adapter_PlainAuth
{
    /**
     * 
     * Performs AUTH CRAM-MD5 with username, password, and server challenge.
     * 
     * @return bool
     * 
     */
    public function auth()
    {
        if (! $this->_auth) {
            
            // issue AUTH CRAM-MD5 and get the server challenge
            $this->_send('AUTH CRAM-MD5');
            $challenge = $this->_expect(334);
            $challenge = base64_decode($challenge);
            
            // send the password hashed with the server challenge
            $hash = hash_hmac('md5', $this->_password, $challenge); 
            $this->_send(base64_encode($this->_username . ' ' . $hash));
            $this->_expect(235);
            
            // guess it worked
            $this->_auth = true;
        }
        
        return $this->_auth;
    }
}
