<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != 6) {
    header('Location: ../login.php');
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Админ-панель</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body style="padding: 20px; font-family: Arial, sans-serif;">
    <h2>Админ-панель</h2>
    <div style="text-align: left; margin: 20px 0;"><div style="text-align: left; margin: 20px 0;">
    <div style="text-align: left; margin: 20px 0;">
    <a href="../index.php" style="color: #000; text-decoration: underline; font-size: 16px;">
        ← В главное меню
    </a>
</div>
</div>
    <nav style="margin: 20px 0; padding: 15px; background: #f5f5f5; border-radius: 8px;">
    <a href="products.php" style="margin-right: 20px; text-decoration: none; color: #000; font-weight: 500;">Товары</a>
    <a href="users.php" style="margin-right: 20px; text-decoration: none; color: #000; font-weight: 500;">Пользователи</a>
    <a href="orders.php" style="margin-right: 20px; text-decoration: none; color: #000; font-weight: 500;">Заказы</a>
    <a href="admin_reviews.php" style="text-decoration: none; color: #000; font-weight: 500;">Отзывы</a>
</nav>

</body>
</html>