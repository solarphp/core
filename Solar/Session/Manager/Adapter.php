<?php
/**
 * 
 * Abstract class for session manager adapters.
 * 
 * @category Solar
 * 
 * @package Solar_Session
 * 
 * @author Antti Holvikari <anttih@gmail.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Adapter.php 3988 2009-09-04 13:51:51Z pmjones $
 * 
 */
abstract class Solar_Session_Manager_Adapter extends Solar_Base {


    /**
     * 
     * Starts the session
     * 
     * @return void
     * 
     */
    abstract function start();

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
    abstract function regenerateId();

    /**
     * 
     * Has a session been started yet?
     * 
     * @return bool
     * 
     */
    abstract function isStarted();

    /**
     * 
     * Has the user requested a prior session?
     * 
     * @return bool
     * 
     */
    abstract function isContinuing();

    /**
     * 
     * Remove this session
     * 
     * @return bool
     * 
     */
    abstract function stop();

    /**
     * 
     * Allow session segments to register with the mothership
     * 
     * @return void
     * 
     */
    abstract function addSession(Solar_Session $session);

}
