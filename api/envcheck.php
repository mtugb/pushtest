<?php
require __DIR__ . '/../vendor/autoload.php';
// Dotenv::createImmutable(__DIR__ . '/../')->load(); // 例: Dotenvライブラリを使う場合
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();
var_dump($_ENV);