<?php
/**
 * 
 * 3-column Solar_View layout template for a Solar site.
 * 
 * @category Solar
 * 
 * @package Solar_App
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
        <?php
            array_unshift($this->layout_style, 'Solar/Layouts/default.css');
            include $this->template('head.part.php');
        ?>
    </head>
    
    <body>
        <div id="top">
            <h1>Solar</h1>
            <?php foreach ((array) $this->layout_top as $val) {
                echo $val;
            } ?>
        </div>
    
        <table id="container" cellspacing="0">
            <tr>
                <td id="left">
                    <?php foreach ((array) $this->layout_left as $val) {
                        echo $val;
                    } ?>
                </td>
                <td id="content">
                    <?php echo $this->layout_content ?>
                </td>
                <td id="right">
                    <?php
                        include $this->template('auth.part.php');
                        foreach ((array) $this->layout_right as $val) {
                            echo $val;
                        }
                    ?>
                </td>
            </tr>
        </table>
    
        <div id="bottom">
            <?php foreach ((array) $this->layout_bottom as $val) {
                echo $val;
            } ?>
        </div>
    </body>

</html>