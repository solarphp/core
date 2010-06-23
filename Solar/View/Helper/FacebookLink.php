<?php
/**
 * 
 * Generates the Link for a facebook login
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
class Solar_View_Helper_FacebookLink extends Solar_View_Helper
{
    protected $_Solar_View_Helper_FacebookScript = array(
        'perms'             => 'email',
        'size'              => 'medium',
        'autologoutlink'    => 'false',
    );

    /**
     * 
     * Generates the link required by facebook
     * 
     * @return string
     * 
     */
    public function FacebookLink()
    {
        return '<fb:login-button autologoutlink="'.$this->_config['autologoutlink'].
                '" size="'.$this->_config['size'].
                '" perm="'.$this->_config['perms'].
                '"></fb:login-button>';
    }

}
