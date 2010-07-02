<?php
/**
 * 
 * Generates the scriptblock required by facebook
 * 
 * @category Solar
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
    protected $_Solar_View_Helper_FacebookScript = array(
        'facebook_instance' => 'facebook',
    );

    protected $_facebook;

    protected function _postConstruct()
    {
        $this->_facebook = Solar::dependency('Facebook', $this->_config['facebook_instance']);
        parent::_postConstruct();
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
        $this->_view->Script("http://connect.facebook.net/en_US/all.js") .
        $this->_view->ScriptInline("FB.init({appId: '".$this->_facebook->getAppId()."', xfbml: true, cookie: true});
FB.Event.subscribe('auth.login', function(response) {
  window.location.reload();
});");
    }

}