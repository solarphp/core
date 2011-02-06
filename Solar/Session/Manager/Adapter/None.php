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
        return true;
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

    /**
     * 
     * Remove this session
     * 
     * @return bool
     * 
     */
    public function stop()
    {
    }

    /**
     * 
     * Allow session segments to register with the mothership.
     * 
     * @param Solar_Session $session The session object to add.
     * 
     * @return void
     * 
     */
    public function addSession(Solar_Session $session)
    {
    }

    /**
     * 
     * Close this session for use in this request, writing the results
     * to storage for the next request
     * 
     * @return void
     * 
     */
    public function close()
    {
    }

}