<?php
/**
 *
 * Generates an anchor linking to the TypeKey login site.
 *
 * @category Solar
 *
 * @package Solar_View
 *
 * @author Paul M. Jones <pmjones@solarphp.com>
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 *
 * @version $Id$
 *
 */

/**
 * Parent class
 */
Solar::loadClass('Solar_View_Helper');

/**
 *
 * Generates a anchor linking to the TypeKey login site.
 * 
 * Uses the same TypeKey token as Solar_Auth_Adapter_TypeKey
 * @category Solar
 *
 * @package Solar_View
 *
 */
class Solar_View_Helper_TypekeyLink extends Solar_View_Helper {
    
    /**
     *
     * User-defined configuration values.
     * 
     * Keys are ...
     * 
     * `token`
     * : (string) The TypeKey site identifier token string. If empty,
     *   the helper will use the value from the Solar config file under
     *   the $config['Solar_Auth_Adapter_Typekey']['token'] key.
     * 
     * `href`
     * : (string) The HREF of the TypeKey login service. Default is
     *   "https://www.typekey.com:443/t/typekey/login".
     * 
     * `need_email`
     * : (bool) Whether or not to get the TypeKey user's email address.
     *   Default false.
     * 
     * @var array
     *
     */
    protected $_Solar_View_Helper_TypekeyLink = array(
        'token'      => null,
        'href'       => "https://www.typekey.com:443/t/typekey/login",
        'need_email' => false,
        'process_key' => 'process',
    );
    
    /**
     *
     * Generates a link to the TypeKey login site.
     *
     * @param string $text The text to display for the link.
     * 
     * @return string
     *
     */
    public function typekeyLink($text = null)
    {
        // get a URI processor; defaults to the current URI.
        $uri = Solar::factory('Solar_Uri');
        
        // do not retain the GET 'process' value on the current URI.
        // this prevents double-processing of actions submitted via GET.
        $key = $this->_config['process_key'];
        if (! empty($uri->query[$key])) {
            unset($uri->query[$key]);
        }
        
        // save the current URI as the return location after typekey.
        $return = $uri->fetch(true);
        
        // now reset the URI to point to the typekey service
        $uri->set($this->_config['href']);
        
        // add the typekey token
        if (empty($this->_config['token'])) {
            $uri->query['t'] = Solar::config('Solar_Auth_Adapter_Typekey', 'token');
        } else {
            $uri->query['t'] = $this->_config['token'];
        }
        
        // convert need_email from true/false to 1/0 and add
        $uri->query['need_email'] = (int) $this->_config['need_email'];
        
        // add the return location
        $uri->query['_return'] = $return;
        
        // done!
        return $this->_view->anchor($uri->fetch(true), $text);
    }
}
