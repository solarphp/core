--TEST--
output-scrubbing plugin
--FILE--
<?php
require_once '00_prepend.php';

// configure and instantiate
$conf = array(
	'template_path' => 'templates',
	'resource_path' => 'resources'
);

$tpl = new Savant3($conf);
$tpl->setTemplate('04_plugins_scrub.tpl.php');

$tpl->setPluginConf(
	'scrub',
	array(
		'default' => 'stripslashes htmlspecialchars'
	)
);

$text = <<<EOF
<html>
    <body>
        This\'s special & so\'s that.
    </body>
</html>
EOF;

$tpl->text = $text;
echo $tpl;

?>
--EXPECT--
&lt;html&gt;
    &lt;body&gt;
        This's special &amp; so's that.
    &lt;/body&gt;
&lt;/html&gt;