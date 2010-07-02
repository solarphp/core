<?php
/**
 * 
 * Authentication adapter for a generic Facebook login.
 * 
 * You will need the Facebook SDK library for this to work; it is available
 * at <http://github.com/facebook/php-sdk>.  Place the library in your
 * Solar system at `(system)/source/facebook`, then change directories to 
 * `include` and create a symlink to the source so the autoloader can find it:
 * 
 *     (system)/include$ ln -s ../source/facebook/src/facebook.php Facebook.php
 * 
 * Alternatively, you can copy the `facebook.php` class into your include 
 * directory as `Facebook.php`.
 * 
 * The Facebook class uses a universal constructor, but not Solar_Base, so 
 * one has to push the config to it directly when registering it:
 * 
 * {{code: php
 *      $config['Solar']['registry_set']['facebook'] = array('Facebook',
 *          array(
 *              'appId'     => '127186213963424',
 *              'secret'    => 'fe524a375e606b8aa245c79414656e67',
 *              'apiKey'    => 'e2954dfa4f7b13a26d41c1139870d949',
 *              'cookie'    => true,
 *          ),
 *      ); 
 * }} 
 * 
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
     * @config dependency facebook A dependency on a Facebook instance; 
     * default is a Solar_Registry entry named 'facebook'.
     *   
     * @var array
     * 
     */  
    protected $_Solar_Auth_Adapter_Facebook = array(
        'facebook' => 'facebook',
    );
    
    /**
     * 
     * A Facebook library instance.
     * 
     * @var Facebook
     * 
     * 
     */   
     protected $_facebook;
    
    /**
     * 
     * Set up the dependency to the Facebook object.
     * 
     * @return void
     * 
     */
    protected function _postConstruct()
    {
        parent::_postConstruct();
        
        $this->_facebook = Solar::dependency(
            'Facebook',
            $this->_config['facebook']
        );
    }

    /**
     * 
     * Is the current page-load a login request?
     * 
     * Facebook sets a specific cookie; if this cookie is set then the user
     * is attempting to log in.
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
     * Verifies the Facebook session.
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