<?php
header('Content-Type: text/html; charset=utf-8');
include 'lib/Controller.php';
$conf = include 'config.php';

$app = new KapController($conf);
$app->run();

