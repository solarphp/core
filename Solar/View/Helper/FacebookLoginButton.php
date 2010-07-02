<?php
/**
 * 
 * Generates a Facebook login button.
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
class Solar_View_Helper_FacebookLoginButton extends Solar_View_Helper
{
    /**
     * 
     * Default configuration values. See the original documentation at
     * <http://wiki.developers.facebook.com/index.php/Fb:login-button>.
     * 
     * @config string condition Indicates whether the button is visible or 
     * hidden. (See the wiki for more information.)
     * 
     * @config string size Specifies the size of the button. Specify 'icon' to 
     * display a favicon only, or 'small', 'medium', 'large', or 'xlarge'.
     * 
     * @config string autologoutlink If 'true' and the user is already 
     * connected and has a session, then the button image changes to 
     * indicate the user can log out. Clicking the button logs the user 
     * out of Facebook and all connected sessions. Note that 'true'/'false'
     * values must be strings, not PHP booleans.
     * 
     * @config string background Specifies the button image to use that is 
     * anti-aliased to match the background of your site -- whether it's 
     * pure white, light, or dark. Specify 'white', 'dark', or 'light'. 
     * Note: You don't specify this attribute if you are using v="2".  
     * 
     * @config string length Specifies which text label to use on a button 
     * with size specified as 'small', 'medium', 'large', or 'xlarge'. 
     * Specify 'short' for the text label **Connect** only or 'long' for 
     * the text label **Connect with Facebook**. If you are rendering the 
     * login button text by including it within the fb:login-button tags, 
     * you don't specify a length at all.
     * 
     * @config string onlogin JavaScript code to execute when the user gains 
     * a Facebook session (that is, after logging into Facebook and 
     * authorizing the site).
     * 
     * @config string v Specify "2" to use the latest Facebook Connect 
     * login buttons (examples available in the Facebook Connect wizard). 
     * Don't use the attribute if you need to use the original Facebook 
     * Connect login buttons.
     * 
     * * @var array
     * 
     */
    protected $_Solar_View_Helper_FacebookLoginButton = array(
        'condition'         => null,
        'size'              => null,
        'autologoutlink'    => null,
        'background'        => null,
        'length'            => null,
        'onlogin'           => null,
        'perm'              => 'email',
    );

    /**
     * 
     * Generates a Facebook login button.
     * 
     * @return string
     * 
     */
    public function facebookLoginButton()
    {
        $attribs = $this->_config;
        return '<fb:login-button'
             . $this->_view->attribs($attribs)
             . '></fb:login-button>';
    }
}
