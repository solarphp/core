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
<p id="hello"><?php echo $this->escape($this->text) ?></p>
<?php $this->jsScriptaculous()->highlight('#hello', array('duration' => 1.0));?>
<?php $this->jsScriptaculous()->inPlaceEditor('#hello', 'index.php', array(
    'rows' => 15,
    'cols' => 40,
    'ajaxOptions' => array(
        'method' => 'post',
        'postBody' => 'thisvar=true',
        'onSuccess' => 'function(t) { alert(t.responseText); }',
        'on404' => 'function(t) { alert(\'Error 404: location not found\'); }'
    )
));?>
<p><?php echo $this->escape($this->code) ?></p>
<ul>
    <?php foreach ($this->list as $code): ?>
    <li>
        <?php echo $this->action("hello/main/$code", $code) ?>
        (<?php echo $this->action("hello/rss/$code", 'RSS') ?>)
    </li>
    <?php endforeach ?>
</ul>