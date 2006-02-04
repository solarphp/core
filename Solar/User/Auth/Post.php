<?php
/**
 * 
 * Authenticate via simple HTTP POST request-and-reply.
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
 * Authenticate via simple HTTP POST request-and-reply.
 *
 * Based in part on php.net user comments:
 * http://us3.php.net/manual/en/function.fsockopen.php#57275
 * http://us3.php.net/manual/en/function.fopen.php#58099
 * 
 * @category Solar
 * 
 * @package Solar_User
 * 
 */
class Solar_User_Auth_Post extends Solar_Base {
    
    /**
     * 
     * User-supplied configuration values.
     * 
     * Keys are:
     * 
     * url => (string) URL to the HTTP service, e.g. "https://example.com/login.php".
     * 
     * username => (string) The username element name.
     * 
     * password => (string) The password element name.
     * 
     * headers => (array) Additional headers to use in the POST request.
     * 
     * replies => (array) Key-value pairs where the key is the server reply
     * string, and the value is a boolean indicating if it indicates success
     * or failure to authenticate.
     * 
     * @var array
     * 
     */
    protected $_config = array(
        'url'      => 'https://example.com/services/authenticate.php',
        'username' => 'username',
        'password' => 'password',
        'headers'  => null, // additional heaaders
        'replies'  => array('0' => false, '1' => true), // key-value array of replies
    );
    
    /**
     * 
     * Validate a username and password.
     * 
     * @param string $user Username to authenticate.
     * 
     * @param string $pass The plain-text password to use.
     * 
     * @return boolean True on success, false on failure.
     * 
     */
    public function valid($username, $password)
    {
        // parse out URL elements
        $url = parse_url($this->_config['url']);
        
        // build the content string for username and password
        $content = 
            urlencode($this->_config['username']) . '=' . urlencode($username) . '&' .
            urlencode($this->_config['password']) . '=' . urlencode($password);
        
        // set up the basic headers
        $tmp = array(
            'Host'            => $url['host'],
            'Connection'      => 'close',
            'Content-Type'    => 'application/x-www-form-urlencoded',
            'Content-Length'  => strlen($content),
        );
        
        // add user-defined headers
        $tmp = array_merge($tmp, (array) $this->_config['headers']);
        
        // build the header string itself
        $headers = "POST {$url['path']} HTTP/1.1\r\n";
        foreach ($tmp as $key => $val) {
            $headers .= "$key: $val\r\n";
        }
        
        // define the host string
        $host = $url['host'];
        if (strtolower($url['scheme']) == 'https') {
            // special hostname used for SSL connections,
            // see http://php.net/fsockopen
            $host = "ssl://$host";
        }
        
        // set the port number (needed for fsockopen)
        if (empty($url['port'])) {
            if (strtolower($url['scheme']) == 'https') {
                $url['port'] = 443;
            } else {
                $url['port'] = 80;
            }
        }
        
        // connect to the host
        $fp = fsockopen($host, $url['port'], $errno, $errstr);
        if (! $fp) {
            // build user-info about the error
            $info = array_merge(
                $this->_config,
                array('errno' => $errno, 'errstr' => $errstr)
            );
            throw $this->_exception(
                'ERR_CONNECTION_FAILED',
                $info
            );
        }
        
        // send the headers and content
        fputs($fp, $headers . "\r\n" . $content);
        
        // now get back the reply
        $reply = '';
        while (! feof($fp)) {
            // suppress errors, as SSL errors come through in a lot of cases.
            // http://forum.mamboserver.com/showthread.php?t=41446
            $reply .= @fgets($fp, 1024);
        }
        
        // close the connection
        fclose($fp);
        
        // remove anything before the first newline-pair (the headers)
        $reply = str_replace("\r\n", "\n", $reply); // be lax on newlines
        $pos = strpos($reply, "\n\n");
        $reply = substr($reply, $pos+2);
        
        // is the reply string a known reply?
        if (array_key_exists($reply, $this->_config['replies'])) {
            // get the true/false value of the reply
            return (bool) $this->_config['replies'][$reply];
        }
        
        // reply not listed, assume false
        return false;
    }
}
?>