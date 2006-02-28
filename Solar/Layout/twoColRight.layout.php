<?php
/**
 * 
 * 2-column (narrow right) Solar_View_Xhtml layout template for a Solar site.
 * 
 * @category Solar
 * 
 * @package Solar_Layout
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license LGPL
 * 
 * @version $Id: default.layout.php 795 2006-02-19 22:03:33Z pmjones $
 * 
 */
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
        "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

    <head>
        <title><?php echo $this->head['title'] ?></title>
        <?php
            echo $this->stylesheet('layout/default.css') . "\n        ";
            echo $this->stylesheet('layout/twoColRight.css') . "\n";
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
            
            <div id="right">
                <?php include $this->template('auth.part.php') ?>
            </div>
            
            <div id="content">
                <?php echo $this->solar_app_content ?>
            </div>
            
            <div id="bottom">
            </div>
        </div>
    </body>
</html>