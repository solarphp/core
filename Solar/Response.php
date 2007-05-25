<?php
/**
 * 
 * Generic HTTP response object for sending headers, cookies, and body.
 * 
 * @category Solar
 * 
 * @package Solar
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
 * Generic HTTP response object for sending headers, cookies, and body.
 * 
 * @category Solar
 * 
 * @package Solar
 * 
 */
class Solar_Response extends Solar_Base {
    
    /**
     * 
     * All headers to send at send() time.
     * 
     * @var array
     * 
     */
    public $headers = array();
    
    /**
     * 
     * All cookies to send at send() time.
     * 
     * @var array
     * 
     */
    public $cookies = array();
    
    /**
     * 
     * The response body.
     * 
     * @var string
     * 
     */
    public $body = null;
    
    /**
     * 
     * Whether or not cookies should default being sent by HTTP only.
     * 
     * @var bool
     * 
     */
    protected $_cookies_httponly = true;
    
    /**
     * 
     * The HTTP version to send as.
     * 
     * @var string
     * 
     */
    protected $_version = '1.1';
    
    /**
     * 
     * The HTTP response status code.
     * 
     * @var int
     * 
     */
    protected $_status = 200;
    
    /**
     * 
     * An array of response status code keys and text message values.
     * 
     * @var array
     * 
     */
    protected $_status_codes = array(
        100 => 'Continue',
        101 => 'Switching Protocols',
        
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request Uri Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
    );
    
    /**
     * 
     * Sends all headers and cookies, then returns the body.
     * 
     * @return string
     * 
     */
    public function __toString()
    {
        $this->_sendHeaders();
        return $this->body;
    }
    
    /**
     * 
     * Sets the HTTP version to '1.0' or '1.1'.
     * 
     * @param string $version The HTTP version to use for this response.
     * 
     * @return void
     * 
     * @throws Solar_Response_Exception_HttpVersion when the version number
     * is not '1.0' or '1.1'.
     * 
     */
    public function setVersion($version)
    {
        $version = trim($version);
        if ($version != '1.0' && $version != '1.1') {
            throw $this->_exception('ERR_HTTP_VERSION', array(
                'version' => $version
            ));
        } else {
            $this->_version = $version;
        }
    }
    
    /**
     * 
     * Sets the HTTP response status code.
     * 
     * @param int $code A valid HTTP status code, such as 200, 302, 404, etc.
     * 
     * @return void
     * 
     */
    public function setStatus($code)
    {
        $code = (int) $code;
        if (empty($this->_status_codes[$code])) {
            throw $this->_exception('ERR_STATUS_CODE', array(
                'code' => $code
            ));
        } else {
            $this->_status = $code;
        }
    }
    
    /**
     * 
     * By default, should cookies be sent by HTTP only?
     * 
     * @param bool $flag True to send by HTTP only, false to send by any
     * method.
     * 
     * @return void
     * 
     */
    public function setCookiesHttponly($flag)
    {
        $this->_cookies_httponly = (bool) $flag;
    }
    
    /**
     * 
     * Sets a header value in $this->headers; will be sent to the client at
     * send() time.
     * 
     * This method will not set 'HTTP' headers for response status codes; use
     * the [[setStatus()]] method instead.
     * 
     * @param string $key The header label, such as "Content-Type".
     * 
     * @param string $val The value for the header.
     * 
     * @param bool $replace This header value should replace any previous
     * values of the same key.  When false, the same header key is sent
     * multiple times with the different values.
     * 
     * @return void
     * 
     * @throws Solar_Response_Exception_HeadersSent when headers have already
     * been sent.
     * 
     * @see [[php::header() | ]]
     * 
     */
    public function setHeader($key, $val, $replace = true)
    {
        // have headers been sent already?
        $this->_checkHeadersSent();
        
        // normalize the header key
        $key = preg_replace('/[^a-zA-Z-]/', '', $key);
        $key = ucwords(strtolower(str_replace('-', ' ', $key)));
        $key = str_replace(' ', '-', $key);
        
        // disallow HTTP headers
        if (strtolower(substr($key, 0, 4)) == 'http') {
            return;
        }
        
        // add the header to the list
        if ($replace) {
            $this->headers[$key] = $val;
        } else {
            $this->headers[$key][] = $val;
        }
    }
    
    /**
     * 
     * Sets a cookie value in $this->cookies; will be sent to the client at
     * send() time.
     * 
     * @param string $name The name of the cookie.
     * 
     * @param string $value The value of the cookie.
     * 
     * @param int $expire The Unix timestamp after which the cookie expires.
     * 
     * @param string $path The path on the server in which the cookie will be
     * available on.
     * 
     * @param string $domain The domain that the cookie is available on.
     * 
     * @param bool $secure Indicates that the cookie should only be
     * transmitted over a secure HTTPS connection.
     * 
     * @param bool $httponly When true, the cookie will be made accessible
     * only through the HTTP protocol. This means that the cookie won't be
     * accessible by scripting languages, such as JavaScript.
     * 
     * @return void
     * 
     * @see [[php::setcookie() | ]]
     * 
     * @throws Solar_Response_Exception_HeadersSent when headers have already
     * been sent.
     * 
     */
    public function setCookie($name, $value = '', $expire = 0,
        $path = '', $domain = '', $secure = false, $httponly = null)
    {
        // have headers been sent already?
        $this->_checkHeadersSent();
        
        // store the cookie value
        $this->cookies[$name] = array(
            'value'     => $value,
            'expire'    => $expire,
            'path'      => $path,
            'domain'    => $domain,
            'secure'    => $secure,
            'httponly'  => $httponly,
        );
    }
    
    /**
     * 
     * Sends all headers and cookies, and the prints the body of the response.
     * 
     * @return void
     * 
     */
    public function send()
    {
        echo $this;
    }
    
    /**
     * 
     * Sends all headers and cookies.
     * 
     * @return void
     * 
     * @throws Solar_Response_Exception_HeadersSent when headers have already
     * been sent.
     * 
     */
    protected function _sendHeaders()
    {
        // have headers been sent already?
        $this->_checkHeadersSent();
        
        // build the response status code and text string
        $status = "HTTP/{$this->_version} {$this->_status} "
                . $this->_status_codes[$this->_status];
        
        // send the status header
        header($status, true, $this->_status);
        
        // send each of the remaining headers
        foreach ($this->headers as $key => $list) {
            settype($list, 'array');
            foreach ($list as $val) {
                header("$key: $val");
            }
        }
        
        // send each of the cookies
        foreach ($this->cookies as $key => $val) {
            
            // was httponly set for this cookie?  if not,
            // use the default.
            $httponly = ($val['httponly'] === null)
                ? $this->_cookies_httponly
                : (bool) $val['httponly'];
            
            setcookie(
                $key,
                $val['value'],
                (int) $val['expire'],
                $val['path'],
                $val['domain'],
                (bool) $val['secure'],
                (bool) $httponly
            );
        }
    }
    
    /**
     * 
     * Checks to see if headers have been sent, throws an exception if they
     * have.
     * 
     * @return void
     * 
     */
    protected function _checkHeadersSent()
    {
        if (headers_sent($file, $line)) {
            throw $this->_exception('ERR_HEADERS_SENT', array(
                'file' => $file,
                'line' => $line,
            ));
        }
    }
}