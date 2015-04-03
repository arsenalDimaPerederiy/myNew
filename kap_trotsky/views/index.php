<h2>Управление капом</h2>

<h5>Для вставки на сайт добавьте следующий код</h5>
<pre>
    include 'lib/Model.php'; // путь изменить относительно положения скрипта на сайте
    $kap = new KapModel();

    echo $kap->getLinks(); // выведет ссылки

    // все настройки задаются в файле config.php
</pre>

<h5>Логи событий: </h5>
<ul id="log_list">
<?php $msgs = array(
    'login' => 'Пользователь залогинился',
    'kap_upload' => 'Загружен КАП',
    'delete_link' => 'Удалена привязка'
); ?>
<?php foreach($logs as $log): ?>
    <li>
        <?=$log[0]?> <b><?=$log[1]?></b> -
            <?php echo isset($msgs[trim($log[2])]) ? $msgs[trim($log[2])] : $log[2]; ?>
        <?php if(isset($log[3])): ?>
        <a href="#" class="show_pre">Подробнее...</a><pre style="display:none"><?php print_r(json_decode(trim($log[3]), 1))?></pre>
        <?php endif; ?>
    </li>
<?php endforeach; ?>
</ul>

