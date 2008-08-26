<?php
/**
 * 
 * Pseudo-transport that writes the message headers and content to a file.
 * 
 * The files are saved in a configurable directory location, and are named
 * "solar_email_{date('Y-m-d_H-i-s.u')}" by default.
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
class Solar_Mail_Transport_Adapter_File extends Solar_Mail_Transport_Adapter
{
    /**
     * 
     * User-defined configuration values.
     * 
     * Keys are ...
     * 
     * `dir`
     * : (string) The directory where email files should be saved.  Default
     *   is the system temp directory.
     * 
     * `prefix`
     * : (string) Prefix file names with this value; default is 'solar_email_'.
     * 
     * @var array
     * 
     */
    protected $_Solar_Mail_Transport_Adapter_File = array(
        'dir'    => null,
        'prefix' => 'solar_email_',
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
        $this->_Solar_Mail_Transport_Adapter_File['dir'] = Solar_Dir::tmp();
        parent::__construct($config);
    }
    
    /**
     * 
     * Writes the Solar_Mail_Message headers and content to a file.
     * 
     * @return bool True on success, false on failure.
     * 
     */
    protected function _send()
    {
        $file = Solar_Dir::fix($this->_config['dir'])
              . $this->_config['prefix']
              . date('Y-m-d_H-i-s')
              . '.' . substr(microtime(), 2, 6);
        
        $text = $this->_headersToString($this->_mail->fetchHeaders())
              . $this->_mail->getCrlf()
              . $this->_mail->fetchContent();
        
        $result = file_put_contents($file, $text);
        
        if ($result === false) {
            return false;
        } else {
            return true;
        }
    }
}