<?php
/**
 * 
 * Log adapter for Chromephp.
 * 
 * @category Solar
 * 
 * @package Solar_Log
 * 
 * @author Richard Thomas <richard@phpjack.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Chromephp.php 3995 2009-09-08 18:49:24Z pmjones $
 * 
 */
class Solar_Log_Adapter_Chromephp extends Solar_Log_Adapter {
    
    /**
     * 
     * Default configuration values.
     * 
     * @config string|array events The event types this instance
     *   should recognize; a comma-separated string of events, or
     *   a sequential array.  Default is all events ('*').
     * 
     * @config string format The line format for each saved event.
     *   Use '%t' for the timestamp, '%e' for the class name, '%e' for
     *   the event type, '%m' for the event description, and '%%' for a
     *   literal percent.  Default is '%t %c %e %m'.
     * 
     * @config string output Output mode.  Set to 'html' for HTML, or 'text' for plain 
     *   text.  Default autodetects by SAPI version.  Value is ignored by this
     *   adapter, since it encodes everything into JSON format.
     * 
     * @config dependency response A Solar_Http_Response dependency injection.
     * 
     * @config string cookie The ChromePHP cookie name.
     * 
     * @config string version The ChromePHP version number.
     * 
     * @var array
     * 
     */
    protected $_Solar_Log_Adapter_Chromephp = array(
        'events'   => '*',
        'format'   => '%t %c %e %m', // time, class, event, message
        'output'   => null,
        'response' => 'response',
        'cookie'   => 'chromephp_log',
        'version'  => '0.145',
    );
    
    /**
     * 
     * The Solar_Http_Response where headers will be set.
     * 
     * @var Solar_Http_Response
     * 
     */
    protected $_response;
    
    /**
     * 
     * A session object for this class.
     * 
     * @var Solar_Session
     * 
     */
    protected $_session;
    
    /**
     * 
     * A ChromePHP data object.
     * 
     * @var StdClass
     * 
     */
    protected $_obj;
    
    /**
     * 
     * Post-construction tasks to complete object construction.
     * 
     * @return void
     * 
     */
    protected function _postConstruct()
    {
        parent::_postConstruct();
        setcookie($this->_config['cookie'], null, 1);
        $this->_session = Solar::factory(
            'Solar_Session',
            array('class' => 'Solar_Log_Adapter_Chromephp')
        );
        if (Solar_Registry::exists('chromephp_data')) {
            $this->_obj = Solar_Registry::get('chromephp_data');
        } else {
            $this->_obj = new StdClass;
            $this->_obj->data      = array();
            $this->_obj->backtrace = array();
            $this->_obj->labels    = array();
            $this->_obj->version   = $this->_config['version'];
            Solar_Registry::set('chromephp_data', $this->_obj);
        }
    }
    
    /**
     * 
     * Sends the log message.
     * 
     * @param string $class The class name reporting the event.
     * 
     * @param string $event The event type (for example 'info' or 'debug').
     * 
     * @param string $descr A description of the event. 
     * 
     * @return mixed Boolean false if the event was not saved (usually
     * because it was not recognized), or a non-empty value if it was
     * saved.
     * 
     */
    protected function _save($class, $event, $descr)
    {
        $this->_obj = Solar_Registry::get('chromephp_data');
        $back = debug_backtrace(false);
        $this->_obj->data[]        = $this->_encode($event) . ':%20' . $this->_encode($descr);
        $this->_obj->labels[]      = $class;
        $this->_obj->backtrace[]   = $this->_encode($back[1]['file'] . ' : ' . $back[1]['line']);
        $this->_cookie();
        return true;
    }
    
    /**
     * 
     * Sets the ChromePHP cookie.
     * 
     * @return void
     * 
     */
    protected function _cookie()
    {
        $data = array(
            'data'      => $this->_obj->data,
            'backtrace' => $this->_obj->backtrace,
            'labels'    => $this->_obj->labels,
            'version'   => $this->_obj->version
        );
        setcookie($this->_config['cookie'], json_encode($data), time() + 30);
    }

    /**
     * 
     * Encodes values for ChromePHP.
     * 
     * @param string $value The value to be encoded.
     * 
     * @return void The encoded value.
     * 
     */
    protected function _encode($value)
    {
        if (!is_string($value)) {
            return $value;
        }
        return str_replace(' ', '%20', $value);
    }
    
    /**
     * 
     * Sets the log message in the response headers.
     * 
     * @param string $data The JSON data for the header.
     *
     * @param int $type 3 - normal, 2 - dump
     * 
     * @return void
     * 
     */
    protected function _setHeader($data, $type = 3)
    {
        $utime = explode(' ', microtime());
        $utime = substr($utime[1], 7) . substr($utime[0], 2);  
        $this->_response->setHeader(
            "X-FirePHP-Data-{$type}{$utime}",
            "{$data},"
        );
    }
}
