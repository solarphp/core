<?php
/**
 * 
 * Authentication adapter for a Generic Facebook login
 * 
 * Requires that you have setup the Facebook.php class in your registry
 * Facebook.php is not Solar so doesn't enjoy Solars config loading so we have to
 * push the config to it through the registry_set feature
 *$config['Solar']['registry_set']['facebook'] = array('Facebook',
 *    array(
 *        'appId'     => '127186213963424',
 *        'secret'    => 'fe524a375e606b8aa245c79414656e67',
 *        'apiKey'    => 'e2954dfa4f7b13a26d41c1139870d949',
 *        'cookie'    => true,
 *    ),
 *); 
 * @category Solar
 * 
 * @package Solar_Auth
 * 
 * @author Richard Thomas <richard@phpjack.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 *   
 * @version $Id: Typekey.php 4405 2010-02-18 04:27:25Z pmjones $
 * 
 */
class Solar_Auth_Adapter_Facebook extends Solar_Auth_Adapter 
{
    /**
     * 
     * Default configuration values.
     * 
     * @config string facebook_instance The value to use for Solar::dependency
     *   
     * @var array
     * 
     */  
    protected $_Solar_Auth_Adapter_Facebook = array(
        'facebook_instance' => 'facebook',
    );
    
    /**
     * 
     * This is the Facebook.php object
     * 
     * @var string
     * 
     * 
     */   
     protected $_facebook;
    
    /**
     * 
     * We just need to setup the dependency to the facebook object
     * 
     * @return void
     * 
     */
    protected function _postConstruct()
    {
        $this->_facebook = Solar::dependency('Facebook', $this->_config['facebook_instance']);
        parent::_postConstruct();
    }
    


    /**
     * 
     * Verifies The facebook session.
     * 
     * @return mixed An array of verified user information, or boolean false
     * if verification failed.
     * 
     */
    protected function _processLogin()
    {
        // We have a possible session lets get our user data
        if ($this->_facebook->getSession()) {
            try {
                $fb_results = $this->_facebook->api('/me');
                return array(
                    'handle'  => $fb_results['id'],  // username
                    'email'   => $fb_results['email'], // email
                    'moniker' => $fb_results['name'],  // display name
                );
            } catch (FacebookApiException $e) {
                // Session is invalid, login failed
            }
        }
        return false;
    }

    /**
     * 
     * Is the current page-load a login request?
     * 
     * Facebook sets a specific cookie, if this cookie is set then they should
     * Be "logged in"
     * 
     * @return bool
     * 
     */
    public function isLoginRequest()
    {
        // check for a facebook session
        if ($this->_request->cookie('fbs_'.$this->_facebook->getAppId())) {
            return true;
        }
        return false;
    }

    /**
     * 
     * Adapter-specific logout processing.
     * 
     * @return string A status code string for reset().
     * 
     */
    protected function _processLogout()
    {
        setcookie('fbs_'.$this->_facebook->getAppId(), "", time() - 36000);
        return Solar_Auth::ANON;
    }

}