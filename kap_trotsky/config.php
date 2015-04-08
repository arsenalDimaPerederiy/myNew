<?php

return array(
    'data_dir'  => realpath(dirname(__FILE__)).'/tmp/', // директория с правом на запись для хранения sqlite базы и логов
    'crypt_key' => '12345', // ключ  для шифрованию логов
    'db' => array(
        // если вместо sqlite необходимо использовать mysql нужно установить эти значения
        'dsn' => 'mysql:host=localhost;dbname=dev_ashop',
        'user' => 'dev_ashop',
        'pass' => 'admin123',
        'table_prefix' => 'zKDcYMV2KPepL7LT',
    ),
);
