<?php
require_once 'Solar.php';
Solar::start();
Solar::run('Solar/App/Bugs/act.php');
Solar::stop();
?>