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
 * @license http://opensource.org/licenses/bsd-license.php BSD
 *
 * @version $Id$
 *
 */
?>
<?php

$this->JsPrototype()->event
                          ->observe('#top', 'click', 'function() { location.href = "http://solarphp.com"; }')
                          ->observe('#top', 'mouseover', 'function() { this.style.cursor = "pointer"; }')
                          ->observe('#top', 'mouseout', 'function() { this.style.cursor = "auto"; }');

// sample onclick
$this->JsPrototype()->event->observe('#ahello', 'click', 'function(evt) { alert("hello!"); Event.stop(evt); }');


?>

<p id="hello"><?php echo $this->escape($this->text) ?></p>
<?php $this->JsScriptaculous()->effect->highlight('#hello', array('duration' => 1.0));?>
<?php $this->JsScriptaculous()->control->inPlaceEditor('#hello', 'index.php', array(
    'rows' => 15,
    'cols' => 40,
    'ajaxOptions' => array(
        'method' => 'post',
        'postBody' => 'thisvar=true',
        'onSuccess' => 'function(t) { alert(t.responseText); }',
        'on404' => 'function(t) { alert(\'Error 404: location not found\'); }'
    )
));?>

<p><a id="ahello" href="index.php">hello!</a> ... an example of an unobtrusive "onclick" hyperlink.</a></p>


<p><?php echo $this->escape($this->code) ?></p>
<ul>
    <?php foreach ($this->list as $code): ?>
    <li>
        <?php echo $this->action("helloajax/main/$code", $code) ?>
        (<?php echo $this->action("helloajax/rss/$code", 'RSS') ?>)
    </li>
    <?php endforeach ?>
</ul>
