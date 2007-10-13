<?php
/**
 * 
 * Partial layout template for the <head>.
 * 
 * @category Solar
 * 
 * @package Solar_App
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id$
 * 
 */
?>
<head>
<?php
    // add meta tags
    foreach ((array) $this->layout_head['meta'] as $val) {
        $this->head()->addMeta($val);
    }
    
    // set the title
    $this->head()->setTitle($this->layout_head['title']);
    
    // set the uri base
    $this->head()->setBase($this->layout_head['base']);
    
    // add links
    foreach ((array) $this->layout_head['link'] as $val) {
        $this->head()->addLink($val);
    }
    
    // add baseline styles
    $this->head()->addStyleBase("Solar/styles/cssfw/tools.css")
                 ->addStyleBase("Solar/styles/cssfw/typo.css")
                 ->addStyleBase("Solar/styles/cssfw/forms.css")
                 ->addStyleBase("Solar/styles/cssfw/layout-{$this->layout}.css")
                 ->addStyleBase("Solar/styles/typo.css")
                 ->addStyleBase("Solar/styles/forms.css")
                 ->addStyleBase("Solar/styles/app/{$this->controller}.css");
    
    // additional baseline styles
    foreach ((array) $this->layout_head['style'] as $val) {
        $this->head()->addStyleBase($val);
    }
    
    // additional baseline scripts
    foreach ((array) $this->layout_head['script'] as $val) {
        $this->head()->addScriptBase($val);
    }
    
    // done!
    echo $this->head()->fetch();
?>
</head>
