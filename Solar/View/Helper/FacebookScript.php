<?php
/**
 * 
 * Generates the script block required by Facebook.
 * 
 * It's not enough to call this helper; you also need to call
 * `$this->foot()->fetch()` to render the scripts just before
 * the HTML `</body>` closing tag.
 * 
 * @category Solar
 * 
 * @package Solar_View_Helper
 * 
 * @author Richard Thomas <richard@phpjack.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: TypekeyLink.php 4285 2009-12-31 02:18:15Z pmjones $
 * 
 */
class Solar_View_Helper_FacebookScript extends Solar_View_Helper
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
    protected $_Solar_View_Helper_FacebookScript = array(
        'facebook' => 'facebook',
    );
    
    /**
     * 
     * A Facebook library instance.
     * 
     * @var Facebook
     * 
     */   
     protected $_facebook;
    
    /**
     * 
     * Generates the script block required by Facebook.
     * 
     * @return void
     * 
     */
    protected function _postConstruct()
    {
        parent::_postConstruct();
        
        // retain the facebook dependency
        $this->_facebook = Solar::dependency(
            'Facebook',
            $this->_config['facebook']
        );
        
        // add the FB script to the foot helper
        $href = "http://connect.facebook.net/en_US/all.js";
        $this->_view->foot()->addScript($href);
        
        // initialize the application and set up login event subscription,
        // also done via the foot helper
        $appid  = $this->_facebook->getAppId();
        $inline = <<<INLINE
FB.init({appId: '$appid', xfbml: true, cookie: true});
FB.Event.subscribe('auth.login', function(response) {
  window.location.reload();
});
INLINE;

        $this->_view->foot()->addScriptInline($inline);
    }
    
    /**
     * 
     * Technically, this does nothing at all; the necessary pieces have
     * been added to the foot() helper by _postConstruct().
     * 
     * @return string
     * 
     */
    public function facebookScript()
    {
        // do nothing; at this point, the scripts have already been added
    }
}
