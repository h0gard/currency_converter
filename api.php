<?php
require 'vendor/autoload.php';
//header('Content-Type: application/json');

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// Создаем логгер с именем "app"
$log = new Logger('app');

// Добавляем обработчик (записывать в app.log)
$log->pushHandler(new StreamHandler('app.log', Logger::WARNING));

// ---------------------------------------------------------------------------------------------------------------


// API KEY - 1a27a8ffb793cdd19c47fde18911148b  -  exchangeratesapi.io

use GuzzleHttp\Client;

$cacheFile = 'cache.json'; // Файл для кэша
$cacheTime = 43200; // 1 день (86400 секунд)

if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTime) {
    // Читаем данные из кэша
    $data = json_decode(file_get_contents($cacheFile), true);
} else {
    // Делаем запрос к API
    $client = new Client();
    $apiKey = '1a27a8ffb793cdd19c47fde18911148b';

    $response = $client->request('GET', 'https://api.exchangeratesapi.io/v1/latest', [
        'query' => ['access_key' => $apiKey]
    ]);

    $data = json_decode($response->getBody(), true);

    // Записываем ответ API в JSON-файл
    file_put_contents($cacheFile, json_encode($data, JSON_PRETTY_PRINT));
}

// Вывод данных
//print_r($data);
?>