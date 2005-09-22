<?php
require_once 'Solar.php';
Solar::start();

$app = Solar::object('Helloworld');
echo $app->output();

Solar::stop();
?>