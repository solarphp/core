<?php
/**
 * 
 * Partial template for layouts to generate the <head> section.
 * 
 * @category Solar
 * 
 * @package Solar_Layout
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id$
 * 
 */
 
// meta elements
if (! empty($this->layout_meta)) {
    foreach ((array) $this->layout_meta as $val) {
        echo "    " . $this->meta($val) . "\n";
    }
}

// title
if (! empty($this->layout_title)) {
    echo "    " . $this->title($this->layout_title) . "\n";
}

// base url
if (! empty($this->layout_base)) {
    echo "    " . $this->base($this->layout_base) . "\n";
}

// links
if (! empty($this->layout_link)) {
    foreach ((array) $this->layout_link as $val) {
        echo "    " . $this->link($val) . "\n";
    }
}

// styles
if (! empty($this->layout_style)) {
    foreach ((array) $this->layout_style as $val) {
        settype($val, 'array');
        if (empty($val[0])) $val[0] = null;
        if (empty($val[1])) $val[1] = array();
        echo "    " . $this->style($val[0], $val[1]) . "\n";
    }
}

// scripts
if (! empty($this->layout_script)) {
    foreach ((array) $this->layout_script as $val) {
        settype($val, 'array');
        if (empty($val[0])) $val[0] = null;
        if (empty($val[1])) $val[1] = array();
        echo "    " . $this->script($val[0], $val[1]) . "\n";
    }
}

// Helper-required scripts
echo $this->js()->fetch();

/** @todo object */
?>