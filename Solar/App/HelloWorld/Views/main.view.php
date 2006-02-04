<?php
/**
 * 
 * HTML view.
 * 
 * @category Solar
 * 
 * @package Solar_App
 * 
 * @subpackage Solar_App_HelloWorld
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license LGPL
 * 
 * @version $Id$
 * 
 */
?>
<html>
    <head>
        <title>Solar: Hello World</title>
    </head>
    <body>
        <p><?php $this->eprint($this->text) ?></p>
        <p><?php $this->eprint($this->code) ?></p>
        <ul>
            <?php foreach ($this->list as $code): ?>
            <li>
                <?php echo $this->actionLink("hello/main?code=$code", $code) ?>
                (<?php echo $this->actionLink("hello/rss?code=$code", 'RSS') ?>)
            </li>
            <?php endforeach ?>
        </ul>
    </body>
</html>