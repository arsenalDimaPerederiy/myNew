<?php


$_SERVER['SERVER_PORT'] = 80;
$_SERVER['HTTP_HOST'] = 'trotsky';
$_SERVER['REQUEST_URI'] = $argv[1];

include 'lib/Model.php'; // путь изменить относительно положения скрипта на сайте
$kap = new KapModel();
echo $kap->getLinks(); // выведет ссылки
