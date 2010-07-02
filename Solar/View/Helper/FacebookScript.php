<?php
/**
 * 
 * Generates the script block required by Facebook.
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
     * Generates the scriptblock required by facebook
     * 
     * @param string $text The text to display for the link.
     * 
     * @param array $attribs Attributes for the anchor.
     * 
     * @return string
     * 
     */
    public function FacebookScript()
    {
        return
        $this->_view->script("http://connect.facebook.net/en_US/all.js") .
        $this->_view->scriptInline("FB.init({appId: '".$this->_facebook->getAppId()."', xfbml: true, cookie: true});
FB.Event.subscribe('auth.login', function(response) {
  window.location.reload();
});");
    }

}