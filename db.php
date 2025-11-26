<?php
// db.php — подключение к базе данных

$host = 'localhost';   // На Beget будет другим (например: localhost)
$db   = 'couteruly';   // Название твоей БД
$user = 'root';        // Имя пользователя (на OpenServer/XAMPP — root)
$pass = '';            // Пароль (по умолчанию пустой в XAMPP)
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}
?>