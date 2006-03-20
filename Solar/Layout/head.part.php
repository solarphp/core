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
 * @license LGPL
 * 
 * @version $Id$
 * 
 */
 
// title
if (! empty($this->layout_title)) {
    echo "<title>" . $this->escape($this->layout_title) . "</title>\n";
}

/** @todo meta */

/** @todo base */

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

/** @todo object */
?>