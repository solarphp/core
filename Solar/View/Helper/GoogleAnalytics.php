<?php
/**
 * 
 * Generates the script block required by Google Analytics.
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
class Solar_View_Helper_GoogleAnalytics extends Solar_View_Helper
{
    /**
     * 
     * Default configuration values.
     * 
     * @config string google analytics account id
     *
     * @config string domain_name used to define base domain name if your using same code across
     *  multiple subdomains, for example '.test.com' or 'none' if being used on multiple top-level domains
     *
     * @config bool allow_linker set to 'true' if you want to use across multiple top-level domains
     * @var array
     * 
     */  
    protected $_Solar_View_Helper_GoogleAnalytics = array(
        'google_ua'     => 'UA-XXXXX-X',
        'domain_name'   => null,
        'allow_linker' => false,
    );
    
    /**
     * 
     * Generates the script block required by Google Analytics
     * 
     * @return void
     * 
     */
    protected function _postConstruct()
    {
        parent::_postConstruct();
        $setDomainName = '';
        if ($this->_config['domain_name']) {
            $setDomainName = "\n  _gaq.push(['_setDomainName', '{$this->_config['domain_name']}']);";
        }
        $setAllowLinker = '';
        if ($this->_config['allow_linker']) {
            $setAllowLinker = "\n  _gaq.push(['_setAllowLinker', true]);";
        }
        $inline = <<<INLINE
var _gaq = _gaq || [];
  _gaq.push(['_setAccount', '{$this->_config['google_ua']}']);$setDomainName$setAllowLinker
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
})();
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
    public function GoogleAnalytics()
    {
        // do nothing; at this point, the scripts have already been added
    }
}
