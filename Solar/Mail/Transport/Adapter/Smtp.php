<?php
/**
 * 
 * Mail-transport adapter using an SMTP connection.
 * 
 * @category Solar
 * 
 * @package Solar_Mail
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
 * Mail-transport adapter using an SMTP connection.
 * 
 * @category Solar
 * 
 * @package Solar_Mail
 * 
 */
class Solar_Mail_Transport_Adapter_Smtp extends Solar_Mail_Transport_Adapter {
    
    /**
     * 
     * User-defined configuration values.
     * 
     * Keys are:
     * 
     * `smtp`
     * : (dependency) A Solar_Smtp dependency object.  Default is 'smtp',
     *   which means to use the registered object named 'smtp'.
     * 
     */
    protected $_Solar_Mail_Transport_Adapter_Smtp = array(
        'smtp' => 'smtp',
    );
    
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
        $this->_smtp = Solar::dependency(
            'Solar_Smtp',
            $this->_config['smtp']
        );
    }
    
    /**
     * 
     * Destructor; makes sure the SMTP connection is closed.
     * 
     * @return void
     * 
     */
    public function __destruct()
    {
        $this->_smtp->disconnect();
    }
    
    /**
     * 
     * Sends the Solar_Mail_Message through an SMTP server.
     *
     * @return bool True on success, false on failure.
     * 
     */
    public function _send()
    {
        // get the headers for the message
        $headers = $this->_mail->fetchHeaders();
        
        // who are we sending from?
        $from = null;
        foreach ($headers as $header) {
            if ($header[0] == 'Return-Path') {
                $from = trim($header[1], '<>');
                break;
            }
        }
        if (! $from) {
            throw $this->_exception('ERR_NO_RETURN_PATH');
        }
        
        // who are we sending to?
        $rcpt = $this->_mail->getRcpt();
        
        // get the content
        $content = $this->_mail->fetchContent();
        
        // change headers from array to string
        $headers = $this->_headersToString($headers);
        
        // prepare the message data
        $crlf = $this->_mail->getCrlf();
        $data = $headers . $crlf . $content;
        
        // make sure we're connected to the server
        if (! $this->_smtp->isConnected()) {
            $this->_smtp->connect();
            $this->_smtp->helo();
        }
        
        // reset previous connections
        $this->_smtp->rset();
        
        // tell who this is MAIL FROM
        $this->_smtp->mail($from);

        // tell who this is RCPT TO (each to, cc, and bcc)
        foreach ($rcpt as $addr) {
            $this->_smtp->rcpt($addr);
        }
        
        // send the message
        $this->_smtp->data($data, $crlf);
        
        // done!
        return true;
    }
}
