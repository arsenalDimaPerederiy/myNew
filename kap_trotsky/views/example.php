<h3>Пример вывода на сайте</h3>
<?php
$t1 = microtime(true);

echo $kap->getLinks();

$t = microtime(true)-$t1;

?>
<div>
    Время генерации: <?=round($t, 5)?>
</div>

