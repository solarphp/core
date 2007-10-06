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
    // meta elements
    if (! empty($this->layout_head['meta'])) {
        foreach ((array) $this->layout_head['meta'] as $val) {
            echo "    " . $this->meta($val) . "\n";
        }
    }
    
    // title
    if (! empty($this->layout_head['title'])) {
        echo "    " . $this->title($this->layout_head['title']) . "\n";
    }
    
    // base url
    if (! empty($this->layout_head['base'])) {
        echo "    " . $this->base($this->layout_head['base']) . "\n";
    }
    
    // links
    if (! empty($this->layout_head['link'])) {
        foreach ((array) $this->layout_head['link'] as $val) {
            echo "    " . $this->link($val) . "\n";
        }
    }
    
    // javascript helper styles before app styles, so that app styles may
    // override bundled style files
    echo $this->js()->fetchStyles();
    
    // styles
    if (! empty($this->layout_head['style'])) {
        foreach ((array) $this->layout_head['style'] as $val) {
            settype($val, 'array');
            if (empty($val[0])) $val[0] = null;
            if (empty($val[1])) $val[1] = array();
            echo "    " . $this->style($val[0], $val[1]) . "\n";
        }
    }
    
    // load helper-required scripts before app scripts, so that app scripts
    // may rely on bundled script files
    echo $this->js()->fetchFiles();
    
    // *now* the app script overrides
    if (! empty($this->layout_head['script'])) {
        foreach ((array) $this->layout_head['script'] as $val) {
            settype($val, 'array');
            if (empty($val[0])) $val[0] = null;
            if (empty($val[1])) $val[1] = array();
            echo "    " . $this->script($val[0], $val[1]) . "\n";
        }
    }
    
    // finally, any inline scripts
    echo $this->js()->fetchInline();
    
    /** @todo object */
?>
</head>

