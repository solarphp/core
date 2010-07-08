<?php
/**
 * 
 * Session manager that does not continue between requests
 * 
 * @category Solar
 * 
 * @package Solar_Session
 * 
 * @author Antti Holvikari <anttih@gmail.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Native.php 3366 2008-08-26 01:36:49Z pmjones $
 * 
 */
class Solar_Session_Manager_Adapter_None extends Solar_Session_Manager_Adapter
{

    /**
     * 
     * Starts the session
     * 
     * @return void
     * 
     */
    public  function start()
    {
        throw $this->_exception('ERR_CANNOT_START_SESSION');
    }

    /**
     * 
     * Regenerates the session ID.
     * 
     * Use this every time there is a privilege change.
     * 
     * @return void
     * 
     * @see [[php::session_regenerate_id()]]
     * 
     */
    public function regenerateId()
    {
    }

    /**
     * 
     * Has a session been started yet?
     * 
     * @return bool
     * 
     */
    public function isStarted()
    {
        return false;
    }

    /**
     * 
     * Has the user requested a prior session?
     * 
     * @return bool
     * 
     */
    public function isContinuing()
    {
        return false;
    }

}