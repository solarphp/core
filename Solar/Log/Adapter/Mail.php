<?php
/**
 * 
 * Log adapter for sending logs via mail
 * 
 * @category Solar
 * 
 * @package Solar_Log
 * 
 * @author Bahtiar `kalkin` Gadimov <bahtiar@gadimov.de>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id$
 * 
 */
class Solar_Log_Adapter_Mail extends Solar_Log_Adapter {

    /**
     * 
     * Default configuration values.
     * 
     * @config string|array events The event types this instance
     *   should recognize; a comma-separated string of events, or
     *   a sequential array.  Default is all events ('*').
     * 
     * @config string file The file where events should be logged;
     *   for example '/www/username/logs/solar.log'.
     * 
     * @config string format The line format for each saved event.
     *   Use '%t' for the timestamp, '%c' for the class name, '%e' for
     *   the event type, '%m' for the event description, and '%%' for a
     *   literal percent.  Default is '%t %c %e %m'.
     *
     * @config array from email address and name
     *
     * @config array to email address and name
     * 
     * @var array
     * 
     */
    protected $_Solar_Log_Adapter_Mail = array(
        'events' => '*',
        'file'   => '',
        'format' => '%t %c %e %m',
        'from'   => null,
        'to'     => null,
    );

    /**
     * Solar_Mail_Transport object
     * 
     * @var Solar_Mail_Transport
     * @access protected
     */
    protected $_transport = null;

    protected function _postConstruct()
    {
        parent::_postConstruct();
        $this->_transport = 
        Solar::factory('Solar_Mail_Transport', array(
                    'smtp' => Solar::factory('Solar_Smtp')));
    }

    protected function _save($class, $event, $descr)
    {
        $descr = mb_ereg_replace('\n', "\r\n", $descr);
        $text = str_replace(
            array('%t', '%c', '%e', '%m', '%%'),
            array($this->_getTime(), $class, $event, $descr, '%'),
            $this->_config['format']
        ) . "\n";
        $mail = Solar::factory('Solar_Mail_Message', array('transport' =>
                    $this->_transport));

        $toName = null;
        if(isset($this->_config['to'][1]))
        {
            $toName = $this->_config['to'][1];
        }

        $fromName = null;
        if(isset($this->_config['from'][1]))
        {
            $fromName = $this->_config['from'][1];
        }
        $mail->setTo($this->_config['to'][0], $toName )
             ->setFrom($this->_config['from'][0], $fromName )
             ->setSubject("[$event] $class")
             ->setText($text)
             ->send();
    }
    
}
