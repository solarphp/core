<?php
// prepend for all controllers
include $this->helper('prepend');

// list only open bugs
$tpl->list = $bugs->fetchOpen();

// display
return $tpl->fetch('list.php');
?>