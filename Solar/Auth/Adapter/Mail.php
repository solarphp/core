<?php
/**
 * 
 * Authenticate against an IMAP or POP3 mail server.
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
 * Authentication adapter class.
 */
Solar::loadClass('Solar_Auth_Adapter');

/**
 * 
 * Authenticate against an IMAP or POP3 mail server.
 * 
 * @category Solar
 * 
 * @package Solar_Auth
 * 
 */
class Solar_Auth_Adapter_Mail extends Solar_Auth_Adapter {
    
    /**
     * 
     * User-supplied configuration values.
     * 
     * Keys are ...
     * 
     * `mailbox`
     * : (string) An imap_open() mailbox string, e.g.
     *   "mail.example.com:143/imap" or "mail.example.com:110/pop3".
     * 
     * @var array
     * 
     */
    protected $_Solar_Auth_Adapter_Mail = array(
        'mailbox' => null,
    );
    
    /**
     * 
     * Constructor.
     * 
     * @param array $config User-supplied configuration.
     * 
     */
    public function __construct($config = null)
    {
        // make sure the IMAP extension is available
        if (! extension_loaded('imap')) {
            throw $this->_exception(
                'ERR_EXTENSION_NOT_LOADED',
                array('extension' => 'imap')
            );
        }
        
        // continue construction
        parent::__construct($config);
    }

    /**
     * 
     * Verifies a username handle and password.
     * 
     * @return mixed An array of verified user information, or boolean false
     * if verification failed.
     * 
     * @todo Check the server status with fsockopen().
     * 
     */
    protected function _processLogin()
    {
        $mailbox = '{' . $this->_config['mailbox'] . '}';
        $conn = @imap_open($mailbox, $this->_handle, $this->_passwd, OP_HALFOPEN);
        if (is_resource($conn)) {
            @imap_close($conn);
            return array('handle' => $this->_handle);
        } else {
            return false;
        }
    }
}
