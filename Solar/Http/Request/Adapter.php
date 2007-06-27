<?php
/**
 * 
 * Abstract adapter to support various HTTP request backends.
 * 
 * @category Solar
 * 
 * @package Solar_Http
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
 * Abstract adapter to support various HTTP request backends.
 * 
 * Note that this class represents a standalone HTTP request, whereas
 * Solar_Request represents the PHP request environment superglobals (including
 * $_SERVER, $_ENV, $_POST, etc).
 * 
 * Here is an example request process:
 * 
 * {{code: php
 *     $request = Solar::factory('Solar_Http_Request');
 *     
 *     // fetch a response object for a GET request
 *     $request->setUri('http://example.com');
 *     $response = $request->fetch();
 *     
 *     // fetch a response object for a POST request with some data
 *     $request->setContent = array(...);
 *     $request->setMethod('post');
 *     $response = $request->fetch();
 *     
 *     // fetch the response as raw text instead of a response object
 *     $response = $request->fetchRaw();
 * }}
 * 
 * This is a fluent class; you can chain the set*() and fetch() methods like so:
 * 
 * {{code: php
 *     // fetch a response object for a POST request with some data
 *     $request  = Solar::factory('Solar_Http_Request');
 *     $response = $request->setUri('http://example.com')
 *                         ->setContent(array(...))
 *                         ->setMethod('post')
 *                         ->fetch();
 * }}
 * 
 * To see the request message that will be sent, use __toString():
 * 
 * {{code: php
 *     // fetch a response object for a POST request with some data
 *     $request  = Solar::factory('Solar_Http_Request');
 *     echo $request->setUri('http://example.com')
 *                  ->setContent(array(...))
 *                  ->setMethod('post')
 *                  ->__toString();
 * }}
 * 
 * @category Solar
 * 
 * @package Solar_Http
 * 
 * @todo Support multipart/form-data and file uploads (must be in conjunction).
 * <http://www.w3.org/TR/html4/interact/forms.html#h-17.13.4.2>  Probably need
 * a MIME part class for this, to be shared with the Mail_Message class.
 * 
 */
abstract class Solar_Http_Request_Adapter extends Solar_Base {
    
    /**
     * 
     * User-defined configuration values.
     * 
     * Config keys are ...
     * 
     * @var array
     * 
     */
    protected $_Solar_Http_Request_Adapter = array(
        'charset'         => 'utf-8',
        'content_type'    => null,
        'max_redirects'   => null,
        'proxy'           => null,
        'timeout'         => null,
        'user_agent'      => null,
        'version'         => '1.1',
        'ssl_cafile'      => null,
        'ssl_capath'      => null,
        'ssl_local_cert'  => null,
        'ssl_passphrase'  => null,
        'ssl_verify_peer' => null,
    );
    
    /**
     * 
     * Content to send along with the request.
     * 
     * If an array, will be encoded with http_build_query() at fetch() time.
     * 
     * @var string|array
     * 
     */
    public $content = null;
    
    /**
     * 
     * The URI for the request.
     * 
     * @var Solar_Uri
     * 
     */
    protected $_uri = null;
    
    /**
     * 
     * The User-Agent header value to send.
     * 
     * @var string
     * 
     */
    protected $_user_agent = null;
    
    /**
     * 
     * The content-type for the body content.
     * 
     * @var string
     * 
     */
    protected $_content_type = null;
    
    /**
     * 
     * The character-set for the body content.
     * 
     * @var string
     * 
     */
    protected $_charset = null;
    
    /**
     * 
     * Additional headers to send with the request.
     * 
     * @var array
     * 
     */
    protected $_headers = array();
    
    /**
     * 
     * Additional cookies to send with the request.
     * 
     * @var array
     * 
     */
    protected $_cookies = array();
    
    /**
     * 
     * The maximum number of redirects to allow.
     * 
     * @var int
     * 
     */
    protected $_max_redirects = null;
    
    /**
     * 
     * The HTTP method to use for the request (GET, POST, etc).
     * 
     * @var string
     * 
     */
    protected $_method = 'GET';
    
    /**
     * 
     * Pass all HTTP requests through this proxy.
     * 
     * @var string
     * 
     */
    protected $_proxy = null;
    
    /**
     * 
     * Let the request time out after this many seconds.
     * 
     * @var string
     * 
     */
    protected $_timeout = null;
    
    /**
     * 
     * The HTTP protocol version to use (1.0 or 1.1).
     * 
     * @var string
     * 
     */
    protected $_version = '1.1';
    
    /**
     * 
     * Require verification of SSL certificate used?
     * 
     * @var bool
     * 
     */
    protected $_ssl_verify_peer = false;
                
    /**
     * 
     * Location of Certificate Authority file on local filesystem which should
     * be used with the $_ssl_verify_peer  option to authenticate the identity
     * of the remote peer.              
     * 
     * @var string
     * 
     */
    protected $_ssl_cafile = null;
                
    /**
     * 
     * If $_ssl_cafile is not specified or if the certificate is not
     * found there, this directory path is searched for a suitable certificate.
     * 
     * The path must be a correctly hashed certificate directory.              
     * 
     * @var string
     * 
     */
    protected $_ssl_capath = null;
    
    /**
     * 
     * Path to local certificate file on filesystem. This must be a PEM encoded
     * file which contains your certificate and private key. It can optionally
     * contain the certificate chain of issuers.              
     * 
     * @var string
     * 
     */
    protected $_ssl_local_cert = null;
    
    /**
     * 
     * Passphrase with which the $_ssl_local_cert file was encoded.
     * 
     * @var string
     * 
     */
    protected $_ssl_passphrase = null;
    
    /**
     * 
     * Constructor.
     * 
     * @param array $config User-defined construction values.
     * 
     */
    public function __construct($config = null)
    {
        parent::__construct($config);
        
        // set all the basic http options
        $this->setCharset($this->_config['charset']);
        $this->setContentType($this->_config['content_type']);
        $this->setMaxRedirects($this->_config['max_redirects']);
        $this->setProxy($this->_config['proxy']);
        $this->setTimeout($this->_config['timeout']);
        $this->setUserAgent($this->_config['user_agent']);
        $this->setVersion($this->_config['version']);
        
        // set all the ssl/https options
        $this->setSslCafile($this->_config['ssl_cafile']);
        $this->setSslCapath($this->_config['ssl_capath']);
        $this->setSslLocalCert($this->_config['ssl_local_cert']);
        $this->setSslPassphrase($this->_config['ssl_passphrase']);
        $this->setSslVerifyPeer($this->_config['ssl_verify_peer']);
    }
    
    /**
     * 
     * Returns this object as a string; effectively, the request message to be
     * sent.
     * 
     * @return string
     * 
     */
    public function __toString()
    {
        // the headers and content
        list($uri, $headers, $content) = $this->_prepareRequest();
        
        // add the request line
        array_unshift($headers, "{$this->_method} $uri HTTP/{$this->_version}");
        
        // the request line, headers, and content
        return implode("\r\n", $headers)
             . "\r\n\r\n"
             . $content;
    }
    
    /**
     * 
     * Returns the body content.
     * 
     * @return array
     * 
     */
    public function getContent()
    {
        return $this->content;
    }
    
    /**
     * 
     * Returns all options as an array.
     * 
     * @return array
     * 
     */
    public function getOptions()
    {
        $list = array(
            'charset',
            'content_type',
            'cookies',
            'headers',
            'max_redirects',
            'method',
            'proxy',
            'timeout',
            'uri',
            'version',
            'ssl_cafile',
            'ssl_capath',
            'ssl_local_cert',
            'ssl_passphrase',
            'ssl_verify_peer',
        );
        
        $opts = array();
        foreach ($list as $item) {
            $var = "_$item";
            $opts[$item] = $this->$var;
        }
        
        return $opts;
    }
    
    /**
     * 
     * Sets "Basic" authorization credentials.
     * 
     * Note that username handles may not have ':' in them.
     * 
     * If both the handle and password are empty, turns off authorization.
     * 
     * @param string $handle The username or login name.
     * 
     * @param string $passwd The associated password for the handle.
     * 
     * @return Solar_Http_Adapter This adapter object.
     * 
     */
    public function setBasicAuth($handle, $passwd)
    {
        // turn off authorization?
        if (! $handle && ! $passwd) {
            unset($this->_header['Authorization']);
            return $this;
        }
        
        // is the handle allowed?
        if (strpos($handle, ':') !== false) {
            throw $this->_exception('ERR_INVALID_HANDLE', array(
                'handle' => $handle
            ));
        }
        
        // set authorization header
        $value = 'Basic ' . base64_encode("$handle:$passwd");
        $this->_headers['Authorization'] = $value;
        
        // done
        return $this;
    }
    
    /**
     * 
     * Sets the character set for the body content.
     * 
     * @param string $val The character set, e.g. "utf-8".
     * 
     * @return Solar_Http_Adapter This adapter object.
     * 
     */
    public function setCharset($val)
    {
        $this->_charset = $val;
        return $this;
    }
    
    /**
     * 
     * Sets the body content; technically you can use the public $content 
     * property, but this allows method-chaining.
     * 
     * If you pass an array, the _prepare() method will automatically call
     * http_build_query() on the array and set the content-type for you.
     * 
     * @param string|array $val The body content.
     * 
     * @return Solar_Http_Adapter This adapter object.
     * 
     */
    public function setContent($val)
    {
        $this->content = $val;
        return $this;
    }
    
    /**
     * 
     * Sets the content-type for the body content.
     * 
     * @param string $val The content-type, e.g. "text/plain".
     * 
     * @return Solar_Http_Adapter This adapter object.
     * 
     */
    public function setContentType($val)
    {
        $this->_content_type = $val;
        return $this;
    }
    
    /**
     * 
     * Sets a cookie value in $this->_cookies to add to the request.
     * 
     * @param string $key The name of the cookie.
     * 
     * @param string $val The value of the cookie; will be URL-encoded at
     * fetch() time.
     * 
     * @return Solar_Http_Adapter This adapter object.
     * 
     */
    public function setCookie($key, $val = '')
    {
        $key = str_replace(array("\r", "\n"), '', $key);
        $this->_cookies[$key] = $val;
        return $this;
    }
    
    /**
     * 
     * Sets a header value in $this->_headers for sending at fetch() time.
     * 
     * This method will not set certain headers; use the provided methods
     * instead.
     * 
     * | Header        | Method                                 |
     * | ------------- | -------------------------------------- |
     * | Authorization | [[setBasicAuth()]]                     |
     * | Content-Type  | [[setContentType()]], [[setCharset()]] |
     * | Cookie        | [[setCookie()]]                        |
     * | HTTP          | [[setVersion()]]                       |
     * | User-Agent    | [[setUserAgent()]]                     |
     * 
     * @param string $key The header label, such as "X-Foo-Bar".
     * 
     * @param string $val The value for the header.  When null or false,
     * deletes the header.
     * 
     * @param bool $replace This header value should replace any previous
     * values of the same key.  When false, the same header key is sent
     * multiple times with the different values.
     * 
     * @return Solar_Http_Adapter This adapter object.
     * 
     * @see [[php::header() | ]]
     * 
     */
    public function setHeader($key, $val, $replace = true)
    {
        // normalize the header key
        $key = Solar_Mime::headerLabel($key);
        
        // disallow certain headers
        $lower = strtolower($key);
        $notok = array('authorization', 'content-type', 'cookie', 'http', 'user-agent');
        if (in_array($lower, $notok)) {
            throw $this->_exception('ERR_USE_OTHER_METHOD', array(
                'Authorization' => 'setBasicAuth()',
                'Content-Type'  => 'setContentType()',
                'Cookie'        => 'setCookie()',
                'HTTP'          => 'setVersion()',
                'User-Agent'    => 'setUserAgent()',
            ));
        }
        
        // how to add the header?
        if ($val === null or $val === false) {
            // delete the key
            unset($this->_headers[$key]);
        } elseif ($replace || empty($this->_headers[$key])) {
            // replacement, or first instance of the key
            $this->_headers[$key] = $val;
        } else {
            // second or later instance of the key
            settype($this->_headers[$key], 'array');
            $this->_headers[$key][] = $val;
        }
        
        // done
        return $this;
    }
    
    /**
     * 
     * When making the request, allow no more than this many redirects.
     * 
     * @param int $max The max number of redirects to allow.
     * 
     * @return Solar_Http_Adapter This adapter object.
     * 
     */
    public function setMaxRedirects($max)
    {
        $this->_max_redirects = (int) $max;
        return $this;
    }
    
    /**
     * 
     * Sets the HTTP method for the request (GET, POST, etc).
     * 
     * Recgonized methods are 'OPTIONS', 'GET', 'HEAD', 'POST', 'PUT', 'DELETE',
     * 'TRACE', and 'CONNECT'.
     * 
     * @param string $method The method to use for the request.
     * 
     * @return Solar_Http_Adapter This adapter object.
     * 
     */
    public function setMethod($method)
    {
        $allowed = array('OPTIONS', 'GET', 'HEAD', 'POST', 'PUT', 'DELETE',
            'TRACE', 'CONNECT');
            
        $method = strtoupper($method);
        
        if (! in_array($method, $allowed)) {
            throw $this->_exception('ERR_UNKNOWN_METHOD');
        }
        
        $this->_method = $method;
        
        // done
        return $this;
    }
    
    /**
     * 
     * Send all requests through this proxy server.
     * 
     * @param string|Solar_Uri $spec The URI for the proxy server.
     * 
     * @return Solar_Http_Adapter This adapter object.
     * 
     */
    public function setProxy($spec)
    {
        if ($spec instanceof Solar_Uri) {
            $this->_proxy = $spec->fetch(true);
        } else {
            $this->_proxy = $spec;
        }
        
        // done
        return $this;
    }
    
    /**
     * 
     * Sets the request timeout in seconds.
     * 
     * @param float $time The timeout in seconds.
     * 
     * @return Solar_Http_Adapter This adapter object.
     * 
     */
    public function setTimeout($time)
    {
        $this->_timeout = (float) $time;
        return $this;
    }
    
    /**
     * 
     * Sets the URI for the request.
     * 
     * @param Solar_Uri|string $spec The URI for the request.
     * 
     * @return Solar_Http_Adapter This adapter object.
     * 
     */
    public function setUri($spec)
    {
        if ($spec instanceof Solar_Uri) {
            $this->_uri = $spec;
        } else {
            $this->_uri = Solar::factory('Solar_Uri', array('uri' => $spec));
        }
        
        return $this;
    }
    
    /**
     * 
     * Sets the User-Agent for the request.
     * 
     * @param string $val The User-Agent value.
     * 
     * @return Solar_Http_Adapter This adapter object.
     * 
     */
    public function setUserAgent($val)
    {
        $this->_user_agent = $val;
        return $this;
    }
    
    /**
     * 
     * Sets the HTTP protocol version for the request (1.0 or 1.1).
     * 
     * @param string $version The version number (1.0 or 1.1).
     * 
     * @return Solar_Http_Adapter This adapter object.
     * 
     */
    public function setVersion($version)
    {
        if ($version != '1.0' && $version != '1.1') {
            throw $this->_exception('ERR_UNKNOWN_VERSION');
        }
        $this->_version = $version;
        return $this;
    }
    
    /**
     * 
     * Require verification of SSL certificate used?
     * 
     * @param bool $flag True or false.
     * 
     * @return Solar_Http_Adapter This adapter object.
     * 
     */
    public function setSslVerifyPeer($flag)
    {
        $this->_ssl_verify_peer = (bool) $flag;
        return $this;
    }
    
    /**
     * 
     * Location of Certificate Authority file on local filesystem which should
     * be used with the $_ssl_verify_peer option to authenticate the identity
     * of the remote peer.              
     * 
     * @param string $val The CA file.
     * 
     * @return Solar_Http_Adapter This adapter object.
     * 
     */
    public function setSslCafile($val)
    {
        $this->_ssl_cafile = $val;
        return $this;
    }
    
    /**
     * 
     * If $_ssl_cafile is not specified or if the certificate is not
     * found there, this directory path is searched for a suitable certificate.
     * 
     * The path must be a correctly hashed certificate directory.              
     * 
     * @param string $val The CA path.
     * 
     * @return Solar_Http_Adapter This adapter object.
     * 
     */
    public function setSslCapath($val)
    {
        $this->_ssl_capath = $val;
        return $this;
    }
    
    /**
     * 
     * Path to local certificate file on filesystem. This must be a PEM encoded
     * file which contains your certificate and private key. It can optionally
     * contain the certificate chain of issuers.              
     * 
     * @param string $val The local certificate file path.
     * 
     * @return Solar_Http_Adapter This adapter object.
     * 
     */
    public function setSslLocalCert($val)
    {
        $this->_ssl_local_cert = $val;
        return $this;
    }
    
    /**
     * 
     * Passphrase with which the $_ssl_local_cert file was encoded.
     * 
     * @param string $val The passphrase.
     * 
     * @return Solar_Http_Adapter This adapter object.
     * 
     */
    public function setSslPassphrase($val)
    {
        $this->_ssl_passphrase = $val;
        return $this;
    }
    
    /**
     * 
     * Fetches from the specified URI and returns a Solar_Http_Response object
     * (or an array of objects if there was more than one response).
     * 
     * @param bool $as_array When false, returns only the last response from the
     * request (a Solar_Http_Response object).  When true, returns an array of
     * response objects representing all responses.  Useful when the request
     * has one or more redirections.
     * 
     * @return Solar_Http_Response|array
     * 
     * @todo Would it make more sense to have a stack in the response object
     * for holding on to the stack of responses?
     * 
     */
    public function fetch($as_array = false)
    {
        // get prepared headers and content for the request
        list($req_uri, $req_headers, $req_content) = $this->_prepareRequest();
        
        // fetch the headers and content from the response
        list($headers, $content) = $this->_fetch($req_uri, $req_headers,
            $req_content);
        
        // a stack of responses; this is because there may have been redirects,
        // etc.
        $response = array();
        
        // the count of responses; start at -1 because we increment in the
        // loop below.
        $i = -1;
        
        // add headers for each response
        foreach ($headers as $header) {
            
            // not an HTTP header, must be a "real" header for the current
            // response number.  split on the first colon.
            $pos = strpos($header, ':');
            $is_http = strtoupper(substr($header, 0, 5)) == 'HTTP/';
            
            // look for an HTTP header to start a new response object.
            if ($pos === false && $is_http) {
                
                // increment to the next response
                $i ++;
                $response[$i] = Solar::factory('Solar_Http_Response');

                // set the version, status code, and status text in the response
                preg_match('/HTTP\/(.+?) ([0-9]+)(.*)/i', $header, $matches);
                $response[$i]->setVersion($matches[1]);
                $response[$i]->setStatusCode($matches[2]);
                $response[$i]->setStatusText($matches[3]);
                
                // go to the next header line
                continue;
            }
            
            // the header label is before the colon
            $label = substr($header, 0, $pos);
            
            // the header value is the part after the colon,
            // less any leading spaces.
            $value = ltrim(substr($header, $pos+1));
            
            // is this a set-cookie header?
            if (strtolower($label) == 'set-cookie') {
                $cookie = $this->_parseCookie($value);
                $response[$i]->setCookie(
                    $cookie['name'],
                    $cookie['value'],
                    $cookie['expire'],
                    $cookie['path'],
                    $cookie['domain'],
                    $cookie['secure'],
                    $cookie['httponly']
                );
            } elseif ($label) {
                // set the header, allow multiples
                $response[$i]->setHeader($label, $value, false);
            }
        }
        
        // @todo -- what if we never get to $response[0]?
        
        // set the content on the last response
        $response[$i]->content = $content;
        
        // done!
        if ($as_array) {
            // return the whole stack
            return $response;
        } else {
            // only return the last response
            return $response[$i];
        }
    }
    
    /**
     * 
     * Fetches from the specified URI and returns the response message as a
     * string.
     * 
     * @return string
     * 
     */
    public function fetchRaw()
    {
        // get prepared headers and content for the request
        list($req_uri, $req_headers, $req_content) = $this->_prepareRequest();
        
        // fetch the headers and content from the response
        list($headers, $content) = $this->_fetch($req_uri, $req_headers,
            $req_content);
        
        // return the raw message
        return implode("\r\n", $headers)
             . "\r\n\r\n"
             . $content;
    }
    
    /**
     * 
     * Support method to make the request, then return headers and content.
     * 
     * @param string $uri The URI get a response from.
     * 
     * @param array $headers A sequential array of header lines for the request.
     * 
     * @param string $content A string of content for the request.
     * 
     * @return array A sequential array where element 0 is a sequential array of
     * header lines, and element 1 is the body content.
     * 
     */
    abstract protected function _fetch($uri, $headers, $content);
    
    /**
     * 
     * Prepares $this->_headers, $this->_cookies, and $this->content for the
     * request.
     * 
     * @return array A sequential array where element 0 is a string of headers
     * (including cookies), and element 1 is a string of content.
     * 
     * @todo Only generate $content on POST and PUT?
     * 
     */
    protected function _prepareRequest()
    {
        // get the URI
        if (! $this->_uri) {
            throw $this->_exception('ERR_NO_URI');
        } else {
            $uri = clone $this->_uri;
        }
        
        // what kind of request is this?
        $is_get  = ($this->_method == 'GET');
        $is_post = ($this->_method == 'POST');
        $is_put  = ($this->_method == 'PUT');
        
        // do we have any body content?
        if (is_array($this->content) && ($is_post || $is_put)) {
            
            // is a POST or PUT with a data array.
            // convert from array and force the content-type.
            $content      = http_build_query($this->content);
            $content_type = 'application/x-www-form-urlencoded';
            
        } elseif (is_array($this->content) && $is_get) {
            
            // is a GET with a data array.
            // merge the content array to the cloned uri query params.
            $uri->query = array_merge(
                $uri->query,
                $this->content
            );
            
            // now clear out the content
            $content      = null;
            $content_type = null;
            
        } elseif (is_string($this->content)) {
            
            // honor as set by the user
            $content      = $this->content;
            $content_type = $this->content_type;
            
        } else {
            
            // no recognizable content
            $content      = null;
            $content_type = null;
            
        }
        
        // get a list of the headers as they are now
        $list = $this->_headers;
        
        // force the content-type header if needed
        if ($content_type) {
            if ($this->_charset) {
                $content_type .= "; charset={$this->_charset}";
            }
            $list['Content-Type'] = $content_type;
        }
        
        // force the content-length
        if ($content) {
            $list['Content-Length'] = strlen($content);
        } else {
            unset($list['Content-Length']);
        }
        
        // force the user-agent header if needed
        if ($this->_user_agent) {
            $list['User-Agent'] = $this->_user_agent;
        }
        
        // convert the list of all header values to a sequential array
        $headers = array();
        foreach ($list as $key => $set) {
            settype($set, 'array');
            foreach ($set as $val) {
                $headers[] = Solar_Mime::headerLine($key, $val);
            }
        }
        
        // create additional cookies in the headers array
        $key = 'Cookie';
        foreach ($this->_cookies as $name => $data) {
            $val = "$name=" . urlencode($data);
            $headers[] = Solar_Mime::headerLine($key, $val);
        }
        
        // done!
        return array($uri->fetch(true), $headers, $content);
    }
    
    /**
     * 
     * Parses a "Set-Cookie" header value and returns it as an array.
     * 
     * @param string $text The Set-Cookie text string value.
     * 
     * @return array An array with keys for each element of the cookie: name,
     * value, expire, etc.
     * 
     * @todo This is probably a brain-dead algorithm; do some research to see if
     * it's truly viable.
     * 
     */
    protected function _parseCookie($text)
    {
        $cookie = array(
            'name'      => null,
            'value'     => null,
            'expire'    => null,
            'path'      => null,
            'domain'    => null,
            'secure'    => false,
            'httponly'  => false,
        );
        
        // get the list of elements
        $list = explode(';', $text);
        
        // get the name and value
        list($cookie['name'], $cookie['value']) = explode('=', array_shift($list));
        $cookie['value'] = urldecode($cookie['value']);
        
        foreach ($list as $item) {
            $data = explode('=', trim($item));
            switch ($data[0]) {
            // string-literal values
            case 'expire':
            case 'path':
            case 'domain':
                $cookie[$data[0]] = $data[1];
                break;
            // true/false values
            case 'secure':
            case 'httponly':
                $cookie[$data[0]] = true;
                break;
            }
        }
        
        return $cookie;
    }
}