<?php
/**
 * 
 * 2-column (narrow left) Solar_View layout template for a Solar site.
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
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
        "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <title><?php echo $this->head['title'] ?></title>
        <?php
            echo $this->stylesheet('layouts/default.css') . "\n        ";
            echo $this->stylesheet('layouts/twoColLeft.css') . "\n";
            if (! empty($this->head['link'])) {
                foreach ((array) $this->head['link'] as $key => $val) {
                    echo "        " . $this->link($val) . "\n";
                }
            }
        ?>
    </head>
    <body>
        <div id="container">
            <div id="top"><?php if (! empty($this->body['title'])): ?>
            
                <h1><?php echo $this->escape($this->body['title']) ?></h1>
                <?php endif; ?>
            </div>
            
            <div id="left">
                <?php
                    include $this->template('auth.part.php')
                ?>
            </div>
            
            <div id="content">
                <?php echo $this->solar_app_content ?>
            </div>
            
            <div id="bottom">
            </div>
        </div>
    </body>
</html>